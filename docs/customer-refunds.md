# Customer refunds

The Settlement / Reconciliation page now has Driver Settlement and Customer Refund
tabs. Customer refunds are manual payment records, like driver settlement payments;
saving a record does not send money through a payment provider.

## Deploy

Deploy the application files and run `php artisan migrate --force` before opening
the customer tab. The migration creates `customer_refunds`,
`customer_refund_allocations`, and `customer_refund_legacy`. It does not alter
booking, trip, customer, or driver settlement records. A rollback is refused once
refund or legacy history exists, to protect the audit trail.

## Policy calculations

Based on the supplied *Cancellation Policy HopOn.pdf*:

| Cancellation | Refund entitlement |
| --- | --- |
| Request withdrawn or expired | Full fare including booking fee |
| Early cancellation | Fare excluding booking fee |
| Late cancellation | 50% of fare excluding booking fee |
| Driver cancels after a passenger's late cancellation | Remaining 50% of fare; booking fee stays excluded |
| Driver cancellation of approved booking | Full fare including booking fee |
| Recorded support override | Full fare including booking fee |
| No show | No refund |

The fare portion is `total_fare - booking_fee`, for the whole booking, and is not
multiplied by seat count again. Amounts are allocated using integer ngwee (two
decimal places). Existing `cancel_type`, `status`, `is_late_cancellation`, and
`remaining_refund_paid` fields identify the policy event. The wallet does not
recalculate early/late using today's time or change the existing cancellation API.
Cancelled records with an unknown reason or invalid fare are excluded from the
available wallet pending review. Incorrect historical cancellation reasons must be
reconciled before recording payment; this screen cannot infer who cancelled from
missing metadata. Support review decisions remain outside this screen.

## Prior refund markers

Revenue Report sets a ride/trip-level `refund_status` flag without recording
an actual amount or customer-level payment. On first reading a marked cancellation,
the new ledger snapshots its policy-calculated entitlement as already refunded.
This is an assumption about old records, not independently verified payment data.
The booking detail includes this amount in **Already refunded**. Review old flagged
journeys before live use, especially multi-passenger rides; the flag covered the
entire journey. Unknown legacy cancellation reasons are held for review.

New manual-refund records are also included in **Already refunded**; their payment
details appear in Customer Refund History.
Available wallet = policy entitlement - legacy refunded - recorded refunds.
A later increase in entitlement leaves only the additional amount available.

## Recording a refund

1. Search by customer name, phone, or ID and optionally filter cancellation dates.
2. Open Bookings to inspect the policy calculation and previously paid amounts.
3. Verify the original customer payment and pay the refund using the usual manual
   process. The calculated wallet is not proof that payment was originally collected.
4. Select Record refund and enter the amount, payment date, a unique payment
   reference for that customer, and an optional reason. Confirm manual payment.

The server locks the customer and source records, recalculates the balance, rejects
excess payments, and allocates partial refunds to oldest eligible cancellations
first. Duplicate form requests and repeated customer payment references return the
existing record rather than recording a second payment. Records retain the admin,
date, reference, and per-booking allocations. Filters also restrict which bookings
receive allocations; reset filters to use the full wallet.

Revenue Report retains its original Refund/Refunded buttons and refund URLs.
Those actions mark the entire ride/trip refunded and return to Revenue Report;
they do not create customer-level refund payment records. The Customer Refund tab
and API remain available separately. Do not record the same payment in both flows:
journey-level markers do not identify individual payment amounts. Existing driver
settlement behavior is unchanged.

## Verify

Test early, late, withdrawn, expired, driver-cancelled, support-approved, and no-show
records. Check partial payments, retries, over-refund rejection, and the additional
50% after a late cancellation followed by driver cancellation. Verify that a refund
against one passenger does not reduce another passenger's wallet, that changed or
removed trip drivers do not hide cancelled bids. Check the Driver Settlement tab
and its payout flow. Revenue Report should retain its original refund behavior.

Automated tests:

```sh
vendor/bin/phpunit tests/Unit/CustomerCancellationPolicyTest.php
vendor/bin/phpunit tests/Feature/CustomerRefundServiceTest.php
```

The feature test uses an in-memory SQLite database when available. To exercise
MySQL, set `HOPON_REFUND_TEST_MYSQL_SOCKET` to an isolated test-server socket with an
empty `hopon_refund_test` database. Never point it at a production server: the
feature test recreates its test tables.
