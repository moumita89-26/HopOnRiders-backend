<?php

namespace App\Http\Controllers\Api;

use App\Helpers\CustomHelper;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\DriverSettlementService;
use Illuminate\Http\Request;

class DriverSettlementController extends Controller
{
    public function index(Request $request, DriverSettlementService $service)
    {
        $request->validate([
            'userId' => 'required|integer',
            'status' => 'nullable|in:pending_settlement,partially_paid,settled,all',
            'isPaid' => 'nullable|in:0,1',
        ]);

        if (! User::whereKey($request->input('userId'))->exists()) {
            return CustomHelper::ErrorResponse('User not found');
        }

        $status = $request->input('status');

        if (! $status && $request->filled('isPaid')) {
            $status = (string) $request->input('isPaid') === '1'
                ? 'settled'
                : 'pending_settlement';
        }

        $allRecords = $service->ledger((int) $request->input('userId'));
        $records = $allRecords;
        if ($status && $status !== 'all') {
            $wantedStatus = $status === 'pending_settlement' ? 'pending' : $status;
            $records = $records->where('settlement_status', $wantedStatus)->values();
        }

        return CustomHelper::SuccessResponse('Driver settlements fetched successfully', [
            'summary' => [
                'totalEarnings' => number_format((float) $allRecords->sum('earning_amount'), 2, '.', ''),
                'totalPaid' => number_format((float) $allRecords->sum('paid_amount'), 2, '.', ''),
                'customerPaid' => number_format((float) $allRecords->sum('customer_paid_amount'), 2, '.', ''),
                'adminPaygoPaid' => number_format((float) $allRecords->sum('admin_paid_amount'), 2, '.', ''),
                'pendingSettlement' => number_format((float) $allRecords->sum('outstanding_amount'), 2, '.', ''),
            ],
            'payouts' => $records,
        ]);
    }
}
