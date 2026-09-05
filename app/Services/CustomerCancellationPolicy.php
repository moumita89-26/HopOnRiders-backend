<?php

namespace App\Services;

class CustomerCancellationPolicy
{
    /** Amounts are integer ngwee; total_fare includes the booking fee for all seats. */
    public function calculate(object $source): array
    {
        $total = (int) round((float) $source->total_fare * 100);
        $fee = (int) round((float) $source->booking_fee * 100);
        $status = (int) $source->status;
        $type = (int) ($source->cancel_type ?? 0);
        if ($total < 0 || $fee < 0 || $fee > $total) {
            return ['entitlement_cents' => 0, 'policy' => 'Invalid fare — review required', 'review' => true];
        }
        $fare = $total - $fee;
        if (! in_array($status, [4, 6, 7, 8], true)) {
            return ['entitlement_cents' => 0, 'policy' => 'Not cancelled', 'review' => false];
        }
        if ($status === 7 || $type === 7) {
            return ['entitlement_cents' => 0, 'policy' => 'No show — no refund', 'review' => false];
        }
        if ($type === 6) {
            return ['entitlement_cents' => $total, 'policy' => 'Support-approved full refund', 'review' => false];
        }
        if ($type === 4 || (int) ($source->is_late_cancellation ?? 0) === 1) {
            $driverCancelledLater = $type === 5 || (int) ($source->remaining_refund_paid ?? 0) === 1;

            return [
                'entitlement_cents' => $driverCancelledLater ? $fare : (int) round($fare / 2),
                'policy' => $driverCancelledLater ? 'Late cancellation, then driver cancellation — full fare, no fee' : 'Late cancellation — 50% fare, no fee',
                'review' => false,
            ];
        }

        return match (true) {
            $status === 8 || $type === 1 => ['entitlement_cents' => $total, 'policy' => 'Withdrawn request — fare and fee', 'review' => false],
            $status === 6 || $type === 2 => ['entitlement_cents' => $total, 'policy' => 'Expired request — fare and fee', 'review' => false],
            $type === 3 => ['entitlement_cents' => $fare, 'policy' => 'Early cancellation — full fare, no fee', 'review' => false],
            $type === 5 => ['entitlement_cents' => $total, 'policy' => 'Driver cancellation — fare and fee', 'review' => false],
            default => ['entitlement_cents' => 0, 'policy' => 'Cancellation reason missing — review required', 'review' => true],
        };
    }
}
