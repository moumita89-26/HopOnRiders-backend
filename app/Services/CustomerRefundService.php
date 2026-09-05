<?php

namespace App\Services;

use App\Models\CustomerRefund;
use App\Models\CustomerRefundAllocation;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CustomerRefundService
{
    public function __construct(private CustomerCancellationPolicy $policy) {}

    public function ledger(array $filters = [], bool $lock = false, bool $snapshotLegacy = true): Collection
    {
        $sources = collect();
        $legacyFallback = collect();
        foreach (['booking' => 'bookings', 'trip_bid' => 'trip_bids'] as $type => $table) {
            if (! empty($filters['source_type']) && $filters['source_type'] !== $type) {
                continue;
            }
            $journeys = $type === 'booking' ? 'rides' : 'trip_requests';
            $customerColumn = $type === 'booking' ? 's.passenger_id' : 'j.passenger_id';
            $query = DB::table($table.' as s')->join($journeys.' as j', 'j.id', '=', 's.trip_id')
                ->join('users as u', 'u.id', '=', $customerColumn)
                ->whereIn('s.status', [4, 6, 7, 8])
                ->when(! empty($filters['customer_id']), fn ($q) => $q->where($customerColumn, $filters['customer_id']))
                ->when(! empty($filters['journey_id']), fn ($q) => $q->where('s.trip_id', $filters['journey_id']))
                ->when(! empty($filters['from']), fn ($q) => $q->whereDate(DB::raw('COALESCE(s.cancelled_at, s.updated_at)'), '>=', $filters['from']))
                ->when(! empty($filters['to']), fn ($q) => $q->whereDate(DB::raw('COALESCE(s.cancelled_at, s.updated_at)'), '<=', $filters['to']))
                ->select('s.*', $customerColumn.' as customer_id', 'u.name as customer_name', 'u.phone as customer_phone', 'j.refund_status as legacy_refunded');
            if ($lock) {
                $query->lockForUpdate();
            }
            // Do not require the current assigned driver: a cancelled bid may
            // belong to a driver who was removed or replaced on the trip request.
            foreach ($query->get() as $row) {
                $calculation = $this->policy->calculate($row);
                if ((int) $row->legacy_refunded === 1) {
                    $snapshot = [
                        'source_type' => $type, 'source_id' => $row->id,
                        'amount' => $calculation['entitlement_cents'] / 100,
                        'needs_review' => $calculation['review'],
                        'created_at' => now(), 'updated_at' => now(),
                    ];
                    if ($snapshotLegacy) {
                        DB::table('customer_refund_legacy')->insertOrIgnore($snapshot);
                    } else {
                        // Customer reads calculate unsnapshotted markers without
                        // creating or changing any financial records.
                        $legacyFallback->put($type.':'.$row->id, (object) $snapshot);
                    }
                }
                $sources->push([
                    'source_type' => $type, 'source_id' => (int) $row->id,
                    'journey_id' => (int) $row->trip_id,
                    'customer_id' => (int) $row->customer_id,
                    'customer_name' => $row->customer_name, 'customer_phone' => $row->customer_phone,
                    'eligible_at' => (string) ($row->cancelled_at ?? $row->updated_at),
                    'total_cents' => (int) round((float) $row->total_fare * 100),
                ] + $calculation);
            }
        }
        $paid = DB::table('customer_refund_allocations')->select('source_type', 'source_id', DB::raw('SUM(amount) as amount'))
            ->groupBy('source_type', 'source_id')->get()->keyBy(fn ($r) => $r->source_type.':'.$r->source_id);
        $legacy = $legacyFallback->merge(DB::table('customer_refund_legacy')->get()->keyBy(fn ($r) => $r->source_type.':'.$r->source_id));

        return $sources->map(function ($entry) use ($paid, $legacy) {
            $key = $entry['source_type'].':'.$entry['source_id'];
            $entry['recorded_cents'] = (int) round((float) ($paid->get($key)->amount ?? 0) * 100);
            $entry['legacy_cents'] = (int) round((float) ($legacy->get($key)->amount ?? 0) * 100);
            $entry['paid_cents'] = $entry['recorded_cents'] + $entry['legacy_cents'];
            $entry['review'] = $entry['review'] || (bool) ($legacy->get($key)->needs_review ?? false)
                || $entry['paid_cents'] > $entry['entitlement_cents'];
            $entry['pending_cents'] = $entry['review'] ? 0 : max(0, $entry['entitlement_cents'] - $entry['paid_cents']);

            return $entry;
        })->sortBy(fn ($e) => $e['eligible_at'].':'.$e['source_type'].':'.sprintf('%020d', $e['source_id']))->values();
    }

    public function summaries(array $filters = [], bool $snapshotLegacy = true): Collection
    {
        return $this->ledger($filters, false, $snapshotLegacy)->groupBy('customer_id')->map(function (Collection $entries) {
            return [
                'customer_id' => $entries->first()['customer_id'],
                'customer_name' => $entries->first()['customer_name'],
                'customer_phone' => $entries->first()['customer_phone'],
                'latest_at' => $entries->max('eligible_at'),
                'entitlement_cents' => $entries->sum('entitlement_cents'),
                'paid_cents' => $entries->sum('paid_cents'),
                'pending_cents' => $entries->sum('pending_cents'),
                'review_count' => $entries->where('review', true)->count(),
                'entries' => $entries,
            ];
        })->sortByDesc('latest_at')->values();
    }

    public function record(int $customerId, string $amount, string $date, string $reference, ?string $reason, int $adminId, string $requestKey, array $filters = []): CustomerRefund
    {
        // Reject fractional ngwee and scientific notation, including direct service calls.
        if (! preg_match('/^\d{1,10}(?:\.\d{1,2})?$/D', $amount)) {
            throw ValidationException::withMessages(['amount' => 'Enter an amount with at most two decimal places.']);
        }
        $parts = explode('.', $amount);
        $cents = ((int) $parts[0] * 100) + (int) str_pad($parts[1] ?? '', 2, '0');

        return DB::transaction(function () use ($customerId, $cents, $date, $reference, $reason, $adminId, $requestKey, $filters) {
            if (! DB::table('users')->where('id', $customerId)->lockForUpdate()->first()) {
                throw ValidationException::withMessages(['customer' => 'Customer not found.']);
            }
            $existing = CustomerRefund::where('request_key', $requestKey)
                ->orWhere(fn ($q) => $q->where('customer_id', $customerId)->where('reference', $reference))->first();
            if ($existing) {
                if ((int) $existing->customer_id !== $customerId || (int) round((float) $existing->amount * 100) !== $cents
                    || $existing->reference !== $reference || $existing->refund_date->toDateString() !== $date) {
                    throw ValidationException::withMessages(['amount' => 'This refund request was already used. Refresh the page.']);
                }

                return $existing;
            }
            $eligible = $this->ledger(['customer_id' => $customerId] + $filters, true)->where('pending_cents', '>', 0);
            if ($cents <= 0 || $cents > $eligible->sum('pending_cents')) {
                throw ValidationException::withMessages(['amount' => 'Refund must be positive and cannot exceed the current available customer balance.']);
            }
            $refund = CustomerRefund::create([
                'customer_id' => $customerId, 'amount' => $cents / 100,
                'refund_date' => $date, 'reference' => $reference, 'reason' => $reason,
                'paid_by' => $adminId, 'request_key' => $requestKey,
            ]);
            foreach ($eligible as $entry) {
                if ($cents === 0) {
                    break;
                }
                $allocated = min($cents, $entry['pending_cents']);
                CustomerRefundAllocation::create([
                    'customer_refund_id' => $refund->id,
                    'source_type' => $entry['source_type'], 'source_id' => $entry['source_id'],
                    'amount' => $allocated / 100,
                ]);
                $cents -= $allocated;
            }

            return $refund;
        });
    }
}
