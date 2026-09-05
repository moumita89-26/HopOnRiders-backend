@extends('admin::layouts.admin_template')

@section('content')
    @include('admin.settlements.tabs', ['activeTab' => 'customers'])
    <div class="card mb-4">
        <div class="card-header">
            <h4 class="card-title mb-2">Customer Refund</h4>
            <p class="text-muted">Refund wallet calculated from cancellation policy, less refunds already recorded. Record a refund after paying the customer manually.</p>
            <form method="get" action="{{ route('admin.settlements.index') }}" class="row g-2">
                <input type="hidden" name="tab" value="customers">
                @foreach(['customer_id', 'source_type', 'journey_id'] as $filter)
                    @if(request()->filled($filter))<input type="hidden" name="{{ $filter }}" value="{{ request($filter) }}">@endif
                @endforeach
                <div class="col-md-4"><input name="q" value="{{ request('q') }}" class="form-control" placeholder="Customer name, phone or ID" aria-label="Customer name, phone or ID"></div>
                <div class="col-md-2"><input type="date" name="from" value="{{ request('from') }}" class="form-control" aria-label="Cancellation date from"></div>
                <div class="col-md-2"><input type="date" name="to" value="{{ request('to') }}" class="form-control" aria-label="Cancellation date to"></div>
                <div class="col-md-4"><button class="btn btn-primary">Filter</button> <a class="btn btn-light" href="{{ route('admin.settlements.index', ['tab' => 'customers']) }}">Reset</a></div>
            </form>
            @if(request()->filled('journey_id'))<p class="mt-2 mb-0">Showing {{ request('source_type') === 'booking' ? 'ride' : 'trip request' }} #{{ request('journey_id') }} only. Reset to see the full customer wallet.</p>@endif
            @if(request()->filled('from') || request()->filled('to'))<p class="mt-2 mb-0">Balances and refunds below apply only to cancellations within the selected dates.</p>@endif
        </div>
    </div>
    @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
    <div class="card mb-4">
        <div class="card-header"><h4 class="card-title mb-0">Customer Wallets</h4></div>
        <div class="card-body table-responsive">
            <table class="table table-bordered table-hover align-middle">
                <thead class="table-blue"><tr><th>Latest cancellation</th><th>Customer</th><th>Bookings</th><th>Policy refund</th><th>Already refunded</th><th>Available wallet</th><th>Status</th><th>Action</th></tr></thead>
                <tbody>
                @forelse($customers as $customer)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($customer['latest_at'])->format('d M Y') }}</td>
                        <td><strong>{{ $customer['customer_name'] }}</strong><br><small>ID: {{ $customer['customer_id'] }} · {{ $customer['customer_phone'] }}</small></td>
                        <td>{{ $customer['entries']->count() }}</td>
                        <td>K{{ number_format($customer['entitlement_cents'] / 100, 2) }}</td>
                        <td class="text-success">K{{ number_format($customer['paid_cents'] / 100, 2) }}</td>
                        <td><strong>K{{ number_format($customer['pending_cents'] / 100, 2) }}</strong></td>
                        <td>
                            @if($customer['review_count'])<span class="badge bg-danger">{{ $customer['review_count'] }} need review</span>@endif
                            @if($customer['pending_cents'] > 0)<span class="badge bg-warning">Pending</span>@elseif(!$customer['review_count'])<span class="badge bg-success">No refund due</span>@endif
                        </td>
                        <td><div class="d-flex gap-1 flex-wrap">
                            <button class="btn btn-sm btn-primary" data-bs-toggle="collapse" data-bs-target="#customer-bookings-{{ $customer['customer_id'] }}" aria-expanded="false" aria-controls="customer-bookings-{{ $customer['customer_id'] }}">Bookings</button>
                            @if($customer['pending_cents'] > 0)<button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#refund-customer-{{ $customer['customer_id'] }}">Record refund</button>@endif
                        </div></td>
                    </tr>
                    <tr><td colspan="8" class="p-0"><div class="collapse p-3" id="customer-bookings-{{ $customer['customer_id'] }}">
                        <table class="table table-sm table-bordered">
                            <thead><tr><th>Type / Booking</th><th>Journey</th><th>Policy</th><th>Policy refund</th><th>Already refunded</th><th>Available</th></tr></thead>
                            <tbody>@foreach($customer['entries'] as $entry)
                                <tr><td>{{ $entry['source_type'] === 'booking' ? 'Ride booking' : 'Trip bid' }} #{{ $entry['source_id'] }}</td><td>#{{ $entry['journey_id'] }}</td>
                                    <td>{{ $entry['policy'] }} @if($entry['review'])<br><span class="text-danger">Excluded from available wallet pending review.</span>@endif</td>
                                    <td>K{{ number_format($entry['entitlement_cents'] / 100, 2) }}</td><td>K{{ number_format($entry['paid_cents'] / 100, 2) }}</td><td>K{{ number_format($entry['pending_cents'] / 100, 2) }}</td>
                                </tr>
                            @endforeach</tbody>
                        </table>
                    </div></td></tr>
                @empty<tr><td colspan="8" class="text-center py-4">No customer cancellations found for these filters.</td></tr>@endforelse
                </tbody>
            </table>
            <p class="text-muted mb-0">Already refunded includes previous refund markers and refunds recorded here. Cases with missing cancellation details are held for review.</p>
        </div>
    </div>
    @foreach($customers as $customer)
        @if($customer['pending_cents'] > 0)
        <div class="modal fade" id="refund-customer-{{ $customer['customer_id'] }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog"><div class="modal-content">
                <form method="post" action="{{ route('admin.settlements.refund-customer', $customer['customer_id']) }}">
                    @csrf
                    <input type="hidden" name="request_key" value="{{ \Illuminate\Support\Str::uuid() }}">
                    @foreach(['from', 'to', 'source_type', 'journey_id'] as $filter)<input type="hidden" name="{{ $filter }}" value="{{ request($filter) }}">@endforeach
                    <div class="modal-header"><h5 class="modal-title">Refund: {{ $customer['customer_name'] }}</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                    <div class="modal-body">
                        <p>Available wallet: <strong>K{{ number_format($customer['pending_cents'] / 100, 2) }}</strong></p>
                        <div class="mb-3"><label class="form-label">Refund amount</label><input class="form-control" type="number" name="amount" min="0.01" step="0.01" max="{{ number_format($customer['pending_cents'] / 100, 2, '.', '') }}" required></div>
                        <div class="mb-3"><label class="form-label">Refund date</label><input class="form-control" type="date" name="refund_date" value="{{ now()->toDateString() }}" max="{{ now()->toDateString() }}" required></div>
                        <div class="mb-3"><label class="form-label">Payment reference</label><input class="form-control" name="reference" maxlength="255" required></div>
                        <div class="mb-3"><label class="form-label">Reason (optional)</label><textarea class="form-control" name="reason" maxlength="1000" rows="2"></textarea></div>
                        <label><input type="checkbox" name="confirmed" value="1" required> I verified the original customer payment and have paid this refund manually.</label>
                    </div>
                    <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button><button class="btn btn-success" type="submit">Save refund</button></div>
                </form>
            </div></div>
        </div>
        @endif
    @endforeach
    <div class="card">
        <div class="card-header"><h4 class="card-title mb-0">Customer Refund History</h4><small>Latest 100 refund records matching the customer search, across all dates and journeys.</small></div>
        <div class="card-body table-responsive"><table class="table table-bordered">
            <thead class="table-blue"><tr><th>Date</th><th>Customer</th><th>Amount</th><th>Reference</th><th>Bookings</th><th>Reason</th><th>Recorded by</th></tr></thead>
            <tbody>@forelse($refundHistory as $refund)
                <tr><td>{{ $refund->refund_date->format('d M Y') }}</td><td>{{ $refund->customer?->name ?? 'Customer #'.$refund->customer_id }}</td><td>K{{ number_format((float) $refund->amount, 2) }}</td><td>{{ $refund->reference }}</td>
                    <td>@foreach($refund->allocations as $allocation)<div>{{ $allocation->source_type === 'booking' ? 'Ride booking' : 'Trip bid' }} #{{ $allocation->source_id }}: K{{ number_format((float) $allocation->amount, 2) }}</div>@endforeach</td>
                    <td>{{ $refund->reason ?: '—' }}</td><td>{{ $refund->paidBy?->name ?? '—' }}</td></tr>
            @empty<tr><td colspan="7" class="text-center">No customer refunds recorded.</td></tr>@endforelse</tbody>
        </table></div>
    </div>
@endsection
