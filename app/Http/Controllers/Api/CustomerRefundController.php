<?php

namespace App\Http\Controllers\Api;

use App\Helpers\CustomHelper;
use App\Http\Controllers\Controller;
use App\Models\CustomerRefund;
use App\Models\User;
use App\Services\CustomerRefundService;
use Illuminate\Http\Request;

class CustomerRefundController extends Controller
{
    public function index(Request $request, CustomerRefundService $service)
    {
        $data = $request->validate([
            'customerId' => 'required|integer|min:1',
            'from' => 'nullable|date_format:Y-m-d',
            'to' => 'nullable|date_format:Y-m-d|after_or_equal:from',
            'historyPage' => 'nullable|integer|min:1',
            'historyLimit' => 'nullable|integer|min:1|max:100',
        ]);
        $customerId = (int) $data['customerId'];
        $customer = User::find($customerId);
        if (! $customer) {
            return CustomHelper::ErrorResponse('Customer not found.')->setStatusCode(404);
        }

        $summary = $service->summaries([
            'customer_id' => $customerId, 'from' => $data['from'] ?? null, 'to' => $data['to'] ?? null,
        ], false)->first();
        $entries = $summary['entries'] ?? collect();
        $history = CustomerRefund::with('allocations')->where('customer_id', $customerId)
            ->latest('refund_date')->latest('id')
            ->paginate($data['historyLimit'] ?? 20, ['*'], 'historyPage', $data['historyPage'] ?? 1);
        $status = $this->status($summary['pending_cents'] ?? 0, ($summary['review_count'] ?? 0) > 0);

        return CustomHelper::SuccessResponse('Customer refunds fetched successfully.', [
            'currency' => 'ZMW',
            'customer' => ['id' => $customerId, 'name' => $customer->name, 'phone' => $customer->phone],
            'summary' => [
                'latestCancellation' => $summary['latest_at'] ?? null,
                'bookingCount' => $entries->count(),
                'policyRefund' => $this->money($summary['entitlement_cents'] ?? 0),
                'alreadyRefunded' => $this->money($summary['paid_cents'] ?? 0),
                'availableWallet' => $this->money($summary['pending_cents'] ?? 0),
                'status' => $status,
                'reviewCount' => $summary['review_count'] ?? 0,
            ],
            'bookings' => $entries->map(fn ($entry) => [
                'type' => $entry['source_type'],
                'typeLabel' => $entry['source_type'] === 'booking' ? 'Ride booking' : 'Trip bid',
                'bookingId' => $entry['source_id'],
                'journeyId' => $entry['journey_id'],
                'cancellationDate' => $entry['eligible_at'],
                'policy' => $entry['policy'],
                'policyRefund' => $this->money($entry['entitlement_cents']),
                'alreadyRefunded' => $this->money($entry['paid_cents']),
                'available' => $this->money($entry['pending_cents']),
                'needsReview' => $entry['review'],
            ])->values(),
            'refundHistory' => $history->getCollection()->map(fn ($refund) => [
                'id' => $refund->id,
                'date' => $refund->refund_date->toDateString(),
                'amount' => $refund->amount,
                'reference' => $refund->reference,
                'allocations' => $refund->allocations->map(fn ($allocation) => [
                    'type' => $allocation->source_type,
                    'bookingId' => $allocation->source_id,
                    'amount' => $allocation->amount,
                ])->values(),
            ])->values(),
            'historyPagination' => [
                'currentPage' => $history->currentPage(), 'perPage' => $history->perPage(),
                'total' => $history->total(), 'lastPage' => $history->lastPage(),
            ],
        ]);
    }

    private function money(int $cents): string
    {
        return number_format($cents / 100, 2, '.', '');
    }

    private function status(int $pending, bool $review): string
    {
        return match (true) {
            $pending > 0 && $review => 'pending_review',
            $review => 'needs_review',
            $pending > 0 => 'pending',
            default => 'no_refund_due',
        };
    }
}
