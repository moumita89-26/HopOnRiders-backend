<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\AdminHelper;
use App\Models\DriverPayout;
use App\Models\DriverSettlement;
use App\Models\User;
use App\Services\DriverSettlementService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;

class SettlementController extends Controller
{
    public function index(Request $request, DriverSettlementService $service)
    {
        $request->validate([
            'from' => 'nullable|date',
            'to' => 'nullable|date|after_or_equal:from',
            'q' => 'nullable|string|max:100',
        ]);

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
