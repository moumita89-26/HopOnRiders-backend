<?php

namespace App\Http\Controllers\Api;

use App\Helpers\CustomHelper;
use App\Http\Controllers\Controller;
use App\Services\DriverSettlementService;
use Illuminate\Http\Request;

class CustomerDirectPaymentController extends Controller
{
    public function store(Request $request, DriverSettlementService $service)
    {
        $validated = $request->validate([
            'bookingId' => 'nullable|required_without:tripBidId|integer',
            'tripBidId' => 'nullable|required_without:bookingId|integer',
            'amount' => 'required|numeric|min:0.01',
            'paymentDate' => 'required|date|before_or_equal:today',
            'paymentMethod' => 'required|in:cash,mobile_money,bank_transfer,other',
            'reference' => 'nullable|string|max:255',
        ]);

        $sourceType = ! empty($validated['bookingId']) ? 'booking' : 'trip_request';
        $sourceId = (int) ($validated['bookingId'] ?? $validated['tripBidId']);
        $payment = $service->recordCustomerDirectPayment(
            $sourceType,
            $sourceId,
            (float) $validated['amount'],
            $validated['paymentDate'],
            $validated['paymentMethod'],
            $validated['reference'] ?? null,
        );

        return CustomHelper::SuccessResponse('Direct driver payment recorded successfully', [
            'transactionId' => $payment->id,
            'driverId' => $payment->driver_id,
            'amount' => $payment->amount,
            'paymentSource' => $payment->payment_source,
        ]);
    }
}
