@extends('admin::layouts.admin_template')

@section('content')
    <div class="card mb-4">
        <div class="card-header">
            <h4 class="card-title mb-3">Driver Settlement</h4>
            <form method="get" action="{{ route('admin.settlements.index') }}" class="row g-2">
                <div class="col-md-4"><input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="Driver name or ID"></div>
                <div class="col-md-2"><input type="date" name="from" value="{{ request('from') }}" class="form-control" title="Booking date from"></div>
                <div class="col-md-2"><input type="date" name="to" value="{{ request('to') }}" class="form-control" title="Booking date to"></div>
                <div class="col-md-4"><button class="btn btn-primary" type="submit">Filter</button> <a class="btn btn-light" href="{{ route('admin.settlements.index') }}">Reset</a></div>
            </form>
        </div>
    </div>

    @if ($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif

    <div class="card mb-4">
        <div class="card-header"><h4 class="card-title mb-0">Drivers</h4></div>
        <div class="card-body table-responsive">
            <table class="table table-bordered table-hover align-middle mb-0">
                <thead class="table-blue">
                    <tr><th>Latest date</th><th>Driver</th><th>Eligible bookings</th><th>Total earnings</th><th>Admin paid</th><th>Pending payout</th><th>Status</th><th>Action</th></tr>
                </thead>
                <tbody>
                    @forelse ($drivers as $driver)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($driver['latest_booking_at'])->format('d M Y') }}</td>
                            <td><strong>{{ $driver['driver_name'] ?: 'Driver #'.$driver['driver_id'] }}</strong><br><small>ID: {{ $driver['driver_id'] }}</small></td>
                            <td>{{ $driver['booking_count'] }}</td>
                            <td><strong>K{{ number_format($driver['total_earnings'], 2) }}</strong></td>
                            <td class="text-success">K{{ number_format($driver['admin_paid'], 2) }}</td>
                            <td class="text-warning"><strong>K{{ number_format($driver['outstanding_amount'], 2) }}</strong></td>
                            <td>@if ($driver['outstanding_amount'] > 0)<span class="badge bg-warning">Pending</span>@else<span class="badge bg-success">Settled</span>@endif</td>
                            <td>
                                <div class="d-flex gap-1 flex-wrap">
                                    <button class="btn btn-sm btn-primary collapsed" type="button" data-bs-toggle="collapse"
                                        data-bs-target="#driver-bookings-{{ $driver['driver_id'] }}" aria-expanded="false"
                                        aria-controls="driver-bookings-{{ $driver['driver_id'] }}">Bookings</button>
                                    @if ($driver['outstanding_amount'] > 0)
                                        <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#pay-driver-{{ $driver['driver_id'] }}">Record payout</button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        <tr class="bg-light">
                            <td colspan="8" class="p-0 border-0">
                                <div class="collapse p-3" id="driver-bookings-{{ $driver['driver_id'] }}">
                                <strong>Completed / Cancelled Bookings</strong>
                                @foreach ($driver['bookings_by_date'] as $date => $bookings)
                                    <div class="mt-2 mb-1"><strong>{{ \Carbon\Carbon::parse($date)->format('d M Y') }}</strong></div>
                                    <div class="table-responsive">
                                        <table class="table table-sm table-bordered bg-white mb-2">
                                            <thead>
                                                <tr><th>Type</th><th>Booking</th><th>Journey</th><th>Passenger</th><th>Status</th><th>Driver earning</th><th>Admin paid</th><th>Pending</th></tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($bookings as $booking)
                                                    <tr>
                                                        <td>{{ $booking['source_type'] === 'booking' ? 'Ride' : 'Trip Request' }}</td>
                                                        <td>#{{ $booking['booking_id'] }}</td>
                                                        <td>#{{ $booking['journey_id'] }}</td>
                                                        <td>{{ $booking['passenger_name'] ?: '—' }}</td>
                                                        <td><span class="badge {{ $booking['booking_status'] === 'Completed' ? 'bg-success' : 'bg-danger' }}">{{ $booking['booking_status'] }}</span></td>
                                                        <td>K{{ number_format($booking['earning_amount'], 2) }}</td>
                                                        <td>K{{ number_format($booking['admin_paid_amount'], 2) }}</td>
                                                        <td><strong>K{{ number_format($booking['outstanding_amount'], 2) }}</strong></td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @endforeach
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center py-4">No drivers with completed or cancelled bookings found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @foreach ($drivers as $driver)
        @if ($driver['outstanding_amount'] > 0)
            <div class="modal fade" id="pay-driver-{{ $driver['driver_id'] }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog"><div class="modal-content">
                    <form method="post" action="{{ route('admin.settlements.pay-driver', $driver['driver_id']) }}">
                        @csrf
                        <input type="hidden" name="from" value="{{ request('from') }}"><input type="hidden" name="to" value="{{ request('to') }}">
                        <div class="modal-header"><h5 class="modal-title">Payout: {{ $driver['driver_name'] ?: 'Driver #'.$driver['driver_id'] }}</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                        <div class="modal-body">
                            <p>Outstanding amount: <strong>K{{ number_format($driver['outstanding_amount'], 2) }}</strong></p>
                            <div class="mb-3"><label class="form-label">Payout amount</label><input type="number" name="amount" class="form-control" required min="0.01" step="0.01" max="{{ number_format($driver['outstanding_amount'], 2, '.', '') }}"></div>
                            <div class="mb-3"><label class="form-label">Payment date</label><input type="date" name="settlement_date" class="form-control" required max="{{ now()->toDateString() }}" value="{{ now()->toDateString() }}"></div>
                            <div class="mb-3"><label class="form-label">Reference</label><input type="text" name="settlement_reference" class="form-control" maxlength="255"></div>
                            <div class="mb-3"><label class="form-label">Reason (optional)</label><textarea name="reason" class="form-control" maxlength="1000" rows="3"></textarea></div>
                        </div>
                        <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-success" onclick="return confirm('Record this manual driver payout?')">Save payout</button></div>
                    </form>
                </div></div>
            </div>
        @endif
    @endforeach

    <div class="card mt-4">
        <div class="card-header"><h4 class="card-title mb-0">Driver Payment History</h4></div>
        <div class="card-body table-responsive">
            <table class="table table-bordered table-hover align-middle">
                <thead class="table-blue"><tr><th>Date</th><th>Driver</th><th>Amount</th><th>Reference</th><th>Reason</th><th>Paid by</th></tr></thead>
                <tbody>
                    @forelse ($settlementHistory as $settlement)
                        <tr><td>{{ $settlement->settlement_date?->format('d M Y') }}</td><td>{{ $settlement->driver?->name ?? 'Driver #'.$settlement->driver_id }}</td><td><strong>K{{ number_format((float) $settlement->amount, 2) }}</strong></td><td>{{ $settlement->settlement_reference ?: '—' }}</td><td>{{ $settlement->reason ?: '—' }}</td><td>{{ $settlement->paidBy?->name ?? '—' }}</td></tr>
                    @empty<tr><td colspan="6" class="text-center">No payout transactions recorded.</td></tr>@endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
