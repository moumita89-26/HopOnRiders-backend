<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\AdminHelper;
use App\Models\CustomerRefund;
use App\Models\DriverPayout;
use App\Models\DriverSettlement;
use App\Models\User;
use App\Services\CustomerRefundService;
use App\Services\DriverSettlementService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;

class SettlementController extends Controller
{
    public function index(Request $request, DriverSettlementService $service, CustomerRefundService $refundService)
    {
        $request->validate([
            'from' => 'nullable|date',
            'to' => 'nullable|date|after_or_equal:from',
            'q' => 'nullable|string|max:100',
            'tab' => 'nullable|in:drivers,customers',
            'customer_id' => 'nullable|integer|min:1',
            'source_type' => 'nullable|in:booking,trip_bid',
            'journey_id' => 'nullable|integer|min:1|required_with:source_type',
        ]);

        if ($request->input('tab') === 'customers') {
            $filters = $request->only('from', 'to', 'customer_id', 'source_type', 'journey_id');
            $customers = $refundService->summaries($filters);
            if ($request->filled('q')) {
                $term = strtolower($request->input('q'));
                $customers = $customers->filter(fn ($c) => str_contains(strtolower($c['customer_name'] ?? ''), $term)
                    || (string) $c['customer_id'] === $term || str_contains($c['customer_phone'] ?? '', $term))->values();
            }

            return view('admin.settlements.customers', [
                'page_title' => 'Settlement / Reconciliation', 'customers' => $customers,
                'refundHistory' => CustomerRefund::with(['customer', 'paidBy', 'allocations'])
                    ->when($request->filled('customer_id'), fn ($q) => $q->where('customer_id', $request->input('customer_id')))
                    ->when($request->filled('q'), fn ($q) => $q->whereHas('customer', function ($q) use ($request) {
                        $term = $request->input('q');
                        $q->where('name', 'like', '%'.$term.'%')->orWhere('phone', 'like', '%'.$term.'%');
                        if (ctype_digit($term)) {
                            $q->orWhere('id', $term);
                        }
                    }))
                    ->latest('refund_date')->latest('id')->limit(100)->get(),
            ]);
        }

        $drivers = $service->driverSummaries($request->input('from'), $request->input('to'));
        if ($request->filled('q')) {
            $term = strtolower($request->input('q'));
            $drivers = $drivers->filter(fn ($driver) => str_contains(strtolower($driver['driver_name'] ?? ''), $term)
                || (string) $driver['driver_id'] === $term
            )->values();
        }

        return view('admin.settlements.index', [
            'page_title' => 'Settlement / Reconciliation',
            'drivers' => $drivers,
            'settlementHistory' => DriverSettlement::with(['driver', 'paidBy'])
                ->latest('settlement_date')
                ->latest('id')
                ->limit(100)
                ->get(),
        ]);
    }

    public function refundCustomer(Request $request, User $customer, CustomerRefundService $service)
    {
        $data = $request->validate([
            'amount' => ['required', 'regex:/^\d{1,10}(?:\.\d{1,2})?$/D'],
            'refund_date' => 'required|date|before_or_equal:today',
            'reference' => 'required|string|max:255',
            'reason' => 'nullable|string|max:1000',
            'request_key' => 'required|uuid',
            'confirmed' => 'accepted',
            'from' => 'nullable|date', 'to' => 'nullable|date|after_or_equal:from',
            'source_type' => 'nullable|in:booking,trip_bid',
            'journey_id' => 'nullable|integer|min:1|required_with:source_type',
        ]);
        $service->record($customer->id, $data['amount'], $data['refund_date'], $data['reference'],
            $data['reason'] ?? null, AdminHelper::myId(), $data['request_key'],
            $request->only('from', 'to', 'source_type', 'journey_id'));

        return redirect()->route('admin.settlements.index', ['tab' => 'customers'])
            ->withSuccess('Customer refund recorded and allocated to the eligible bookings.');
    }

    public function payDriver(Request $request, User $driver, DriverSettlementService $service)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'reason' => 'nullable|string|max:1000',
            'settlement_reference' => 'nullable|string|max:255',
            'settlement_date' => 'required|date|before_or_equal:today',
            'from' => 'nullable|date',
            'to' => 'nullable|date|after_or_equal:from',
        ]);

        $service->recordManualPayment(
            $driver->id,
            (float) $validated['amount'],
            $validated['reason'] ?? '',
            $validated['settlement_date'],
            $validated['settlement_reference'] ?? null,
            AdminHelper::myId(),
            'admin_manual',
            $validated['from'] ?? null,
            $validated['to'] ?? null,
        );

        return redirect()->back()->withSuccess('Manual driver payout recorded and allocated to eligible bookings.');
    }

    public function settle(Request $request, DriverPayout $driverPayout)
    {
        $validated = $request->validate([
            'settlement_reference' => 'required|string|max:255',
            'settlement_date' => 'required|date|before_or_equal:today',
        ]);

        DB::transaction(function () use ($driverPayout, $validated) {
            $payout = DriverPayout::whereKey($driverPayout->id)->lockForUpdate()->firstOrFail();

            if ($payout->payout_status === DriverPayout::STATUS_SETTLED) {
                return;
            }

            $payout->update([
                'payout_status' => DriverPayout::STATUS_SETTLED,
                'settlement_reference' => $validated['settlement_reference'],
                'settlement_date' => $validated['settlement_date'],
                'settled_by' => AdminHelper::myId(),
                'settled_at' => now(),
            ]);
        });

        return redirect()->back()->withSuccess('Driver payout marked as Paid/Settled.');
    }
}
