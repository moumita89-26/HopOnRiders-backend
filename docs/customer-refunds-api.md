# Customer refund API

`POST /api/customerRefunds` returns the customer's refund wallet, booking breakdown,
and refund history. It uses the same calculations as the admin Customer Refund tab.
No action fields, payment actions, separate legacy column, admin notes, or admin
identities are returned. Monetary values are decimal strings in ZMW.

This endpoint follows the existing ID-based mobile API convention, as requested.
It scopes results to `customerId` but does not authenticate ownership of that ID.
No login or token changes are required.

## Request

```http
POST /api/customerRefunds
Content-Type: application/json
Accept: application/json

{
  "customerId": 82
}
```

Optional fields:

| Field | Meaning |
| --- | --- |
| `from` | Cancellation date from, inclusive, `YYYY-MM-DD` |
| `to` | Cancellation date to, inclusive, `YYYY-MM-DD`; cannot precede `from` |
| `historyPage` | Refund history page, starting at 1 |
| `historyLimit` | Refund history page size, default 20, maximum 100 |

Date filters apply to the wallet and booking breakdown. Refund history includes
all dates for that customer, independently paginated, matching the admin page.

## Example response

Illustrative data for one booking:

```json
{
  "responseCode": 1,
  "responseText": "Customer refunds fetched successfully.",
  "responseData": {
    "currency": "ZMW",
    "customer": {"id": 82, "name": "Example Customer", "phone": "customer phone"},
    "summary": {
      "latestCancellation": "2026-08-09 10:00:00",
      "bookingCount": 1,
      "policyRefund": "260.00",
      "alreadyRefunded": "150.00",
      "availableWallet": "110.00",
      "status": "pending",
      "reviewCount": 0
    },
    "bookings": [{
      "type": "trip_bid",
      "typeLabel": "Trip bid",
      "bookingId": 4,
      "journeyId": 3,
      "cancellationDate": "2026-08-09 10:00:00",
      "policy": "Driver cancellation — fare and fee",
      "policyRefund": "260.00",
      "alreadyRefunded": "150.00",
      "available": "110.00",
      "needsReview": false
    }],
    "refundHistory": [{
      "id": 1,
      "date": "2026-08-10",
      "amount": "150.00",
      "reference": "EXAMPLE-REF-1",
      "allocations": [{"type": "trip_bid", "bookingId": 4, "amount": "150.00"}]
    }],
    "historyPagination": {"currentPage": 1, "perPage": 20, "total": 1, "lastPage": 1}
  }
}
```

Summary statuses: `pending`, `pending_review` (payable balance plus cases needing
review), `needs_review`, and `no_refund_due`. A customer without cancellations gets
zero totals, `latestCancellation: null`, and an empty booking list. Old refunded
markers remain part of `alreadyRefunded`; only newly recorded refunds have payment
history rows. Customer reads do not create legacy snapshots or refund records.

Errors: 404 when the customer does not exist; 422 for a missing/invalid customer ID,
invalid dates, or invalid pagination values.

## Deployment

The customer refund ledger migration must already be applied. No additional schema
migration or login change is introduced by this API.
