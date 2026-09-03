<?php

namespace App\Services;

use App\Models\DriverSettlement;
use App\Models\DriverSettlementAllocation;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DriverSettlementService
{
    public function ledger(?int $driverId = null, ?string $from = null, ?string $to = null): Collection
    {
        $bookings = DB::table('bookings as bookings')
            ->join('rides as rides', 'rides.id', '=', 'bookings.trip_id')
            ->join('users as drivers', 'drivers.id', '=', 'rides.driver_id')
            ->leftJoin('users as passengers', 'passengers.id', '=', 'bookings.passenger_id')
            ->whereIn('bookings.status', [3, 4])
            ->when($driverId, fn ($query) => $query->where('rides.driver_id', $driverId))
            ->when($from, fn ($query) => $query->whereDate('bookings.updated_at', '>=', $from))
            ->when($to, fn ($query) => $query->whereDate('bookings.updated_at', '<=', $to))
            ->select([
                'bookings.id as source_id',
                'bookings.id as booking_id',
                'bookings.trip_id as journey_id',
                'rides.driver_id',
                'drivers.name as driver_name',
                'passengers.name as passenger_name',
                'bookings.status',
                'bookings.total_fare',
                'bookings.booking_fee',
                'bookings.driver_compensation',
                'bookings.updated_at as eligible_at',
            ])
            ->get()
            ->map(fn ($row) => $this->normalizeEntry($row, 'booking'));

        $tripRequests = DB::table('trip_bids as bids')
            ->join('trip_requests as requests', 'requests.id', '=', 'bids.trip_id')
            ->join('users as drivers', 'drivers.id', '=', 'bids.driver_id')
            ->leftJoin('users as passengers', 'passengers.id', '=', 'requests.passenger_id')
            ->whereColumn('bids.driver_id', 'requests.driver_id')
            ->whereIn('requests.status', [3, 4])
            ->whereIn('bids.status', [1, 2, 3, 4])
            ->when($driverId, fn ($query) => $query->where('bids.driver_id', $driverId))
            ->when($from, fn ($query) => $query->whereDate('bids.updated_at', '>=', $from))
            ->when($to, fn ($query) => $query->whereDate('bids.updated_at', '<=', $to))
            ->select([
                'bids.id as source_id',
                'requests.id as booking_id',
                'requests.id as journey_id',
                'bids.driver_id',
                'drivers.name as driver_name',
                'passengers.name as passenger_name',
                'requests.status',
                'bids.total_fare',
                'bids.booking_fee',
                'bids.driver_compensation',
                'bids.updated_at as eligible_at',
            ])
            ->get()
            ->map(fn ($row) => $this->normalizeEntry($row, 'trip_request'));

        $entries = $bookings->concat($tripRequests);
        $paidBySource = DB::table('driver_settlement_allocations as allocations')
            ->join('driver_settlements as settlements', 'settlements.id', '=', 'allocations.driver_settlement_id')
            ->select(
                'allocations.source_type',
                'allocations.source_id',
                DB::raw("SUM(CASE WHEN settlements.payment_source = 'customer_direct' THEN allocations.amount ELSE 0 END) as customer_paid_amount"),
                DB::raw("SUM(CASE WHEN settlements.payment_source IN ('admin_manual', 'paygo_settlement') THEN allocations.amount ELSE 0 END) as admin_paid_amount"),
            )
            ->groupBy('source_type', 'source_id')
            ->get()
            ->keyBy(fn ($row) => $row->source_type.':'.$row->source_id);

        return $entries->map(function (array $entry) use ($paidBySource) {
            $payments = $paidBySource->get($entry['source_type'].':'.$entry['source_id']);
            $customerPaid = (float) ($payments->customer_paid_amount ?? 0);
            $adminPaid = (float) ($payments->admin_paid_amount ?? 0);
            $paid = $customerPaid + $adminPaid;
            $entry['customer_paid_amount'] = round($customerPaid, 2);
            $entry['admin_paid_amount'] = round($adminPaid, 2);
            $entry['paid_amount'] = round($paid, 2);
            $entry['outstanding_amount'] = round(max(0, $entry['earning_amount'] - $customerPaid - $adminPaid), 2);
            $entry['settlement_status'] = match (true) {
                $entry['outstanding_amount'] <= 0 => 'settled',
                $paid > 0 => 'partially_paid',
                default => 'pending',
            };

            return $entry;
        })->sortBy('eligible_at')->values();
    }

    public function driverSummaries(?string $from = null, ?string $to = null): Collection
    {
        return $this->ledger(null, $from, $to)
            ->groupBy('driver_id')
            ->map(function (Collection $entries) {
                return [
                    'driver_id' => $entries->first()['driver_id'],
                    'driver_name' => $entries->first()['driver_name'],
                    'latest_booking_at' => $entries->max('eligible_at'),
                    'total_earnings' => round($entries->sum('earning_amount'), 2),
                    'customer_paid' => round($entries->sum('customer_paid_amount'), 2),
                    'admin_paid' => round($entries->sum('admin_paid_amount'), 2),
                    'outstanding_amount' => round($entries->sum('outstanding_amount'), 2),
                    'booking_count' => $entries->count(),
                    'bookings_by_date' => $entries->sortByDesc('eligible_at')->groupBy(fn ($entry) => substr($entry['eligible_at'], 0, 10)),
                ];
            })
            ->sortByDesc('latest_booking_at')
            ->values();
    }

    public function recordManualPayment(int $driverId, float $amount, string $reason, string $date, ?string $reference, ?int $adminId, string $paymentSource = 'admin_manual', ?string $from = null, ?string $to = null): DriverSettlement
    {
        return DB::transaction(function () use ($driverId, $amount, $reason, $date, $reference, $adminId, $paymentSource, $from, $to) {
            DB::table('users')->where('id', $driverId)->lockForUpdate()->first();

            $eligible = $this->ledger($driverId, $from, $to)->filter(fn ($entry) => $entry['outstanding_amount'] > 0);
            $outstandingCents = $eligible->sum(fn ($entry) => $this->toCents($entry['outstanding_amount']));
            $paymentCents = $this->toCents($amount);

            if ($paymentCents <= 0 || $paymentCents > $outstandingCents) {
                throw ValidationException::withMessages([
                    'amount' => 'Payment must be greater than zero and cannot exceed the outstanding driver balance.',
                ]);
            }

            $settlement = DriverSettlement::create([
                'driver_id' => $driverId,
                'amount' => $paymentCents / 100,
                'payment_source' => $paymentSource,
                'reason' => $reason,
                'settlement_reference' => $reference,
                'settlement_date' => $date,
                'paid_by' => $adminId,
            ]);

            $remainingCents = $paymentCents;
            foreach ($eligible as $entry) {
                if ($remainingCents === 0) {
                    break;
                }

                $allocatedCents = min($remainingCents, $this->toCents($entry['outstanding_amount']));
                DriverSettlementAllocation::create([
                    'driver_settlement_id' => $settlement->id,
                    'source_type' => $entry['source_type'],
                    'source_id' => $entry['source_id'],
                    'amount' => $allocatedCents / 100,
                ]);
                $remainingCents -= $allocatedCents;
            }

            return $settlement->load('allocations');
        });
    }

    public function recordCustomerDirectPayment(string $sourceType, int $sourceId, float $amount, string $date, string $paymentMethod, ?string $reference = null): DriverSettlement
    {
        return DB::transaction(function () use ($sourceType, $sourceId, $amount, $date, $paymentMethod, $reference) {
            $source = $sourceType === 'booking'
                ? DB::table('bookings as source')->join('rides', 'rides.id', '=', 'source.trip_id')->where('source.id', $sourceId)
                    ->select('rides.driver_id', 'source.status', 'source.total_fare', 'source.booking_fee', 'source.driver_compensation')->first()
                : DB::table('trip_bids as source')->where('source.id', $sourceId)
                    ->select('source.driver_id', 'source.status', 'source.total_fare', 'source.proposed_fare', 'source.booking_fee', 'source.driver_compensation')->first();

            $paymentCents = $this->toCents($amount);
            if (! $source) {
                throw ValidationException::withMessages([
                    'amount' => 'The booking for this direct payment was not found.',
                ]);
            }

            DB::table('users')->where('id', $source->driver_id)->lockForUpdate()->first();
            $alreadyPaid = (float) DB::table('driver_settlement_allocations')
                ->where('source_type', $sourceType)
                ->where('source_id', $sourceId)
                ->sum('amount');
            $earning = (int) $source->status === 4
                ? (float) ($source->driver_compensation ?? 0)
                : max(0, (float) ($source->total_fare ?? $source->proposed_fare ?? 0) - (float) ($source->booking_fee ?? 0));

            if ($paymentCents <= 0 || $paymentCents > $this->toCents(max(0, $earning - $alreadyPaid))) {
                throw ValidationException::withMessages([
                    'amount' => 'Direct payment cannot exceed the remaining driver earning for this booking.',
                ]);
            }

            $settlement = DriverSettlement::create([
                'driver_id' => $source->driver_id,
                'amount' => $paymentCents / 100,
                'payment_source' => 'customer_direct',
                'customer_payment_method' => $paymentMethod,
                'reason' => 'Customer paid driver directly',
                'settlement_reference' => $reference,
                'settlement_date' => $date,
                'paid_by' => null,
            ]);

            DriverSettlementAllocation::create([
                'driver_settlement_id' => $settlement->id,
                'source_type' => $sourceType,
                'source_id' => $sourceId,
                'amount' => $paymentCents / 100,
            ]);

            return $settlement;
        });
    }

    private function normalizeEntry(object $row, string $sourceType): array
    {
        $status = (int) $row->status;
        $gross = (float) ($row->total_fare ?? 0);
        $fee = (float) ($row->booking_fee ?? 0);
        $earning = $status === 4
            ? (float) ($row->driver_compensation ?? 0)
            : max(0, $gross - $fee);

        return [
            'source_type' => $sourceType,
            'source_id' => (int) $row->source_id,
            'booking_id' => (int) $row->booking_id,
            'journey_id' => (int) $row->journey_id,
            'driver_id' => (int) $row->driver_id,
            'driver_name' => $row->driver_name,
            'passenger_name' => $row->passenger_name,
            'booking_status' => $status === 3 ? 'Completed' : 'Cancelled',
            'gross_amount' => round($gross, 2),
            'hopon_fee' => round($fee, 2),
            'earning_amount' => round($earning, 2),
            'eligible_at' => (string) $row->eligible_at,
        ];
    }

    private function toCents(float $amount): int
    {
        return (int) round($amount * 100);
    }
}
