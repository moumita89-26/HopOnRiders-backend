@extends('admin::layouts.admin_template')
@section('content')
    <div class="list-grid-nav hstack gap-1 mb-3">


    </div>

    <ul class="nav nav-pills mb-3" id="pills-tab" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="pills-home-tab" data-bs-toggle="pill" data-bs-target="#pills-home"
                type="button" role="tab" aria-controls="pills-home" aria-selected="true">Ride</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="pills-profile-tab" data-bs-toggle="pill" data-bs-target="#pills-profile"
                type="button" role="tab" aria-controls="pills-profile" aria-selected="false">Trip</button>
        </li>
    </ul>
    <div class="tab-content" id="pills-tabContent">
        <div class="tab-pane fade show active" id="pills-home" role="tabpanel" aria-labelledby="pills-home-tab">
            <div class="card">
                <div class="card-header align-items-center d-flex">
                    <h4 class="card-title mb-0 flex-grow-1">{{ $page_title }}</h4>
                    <div class="box-tools pull-right" style="position: relative;margin-top: -5px;margin-right: -10px">

                        <form method="get" style="display:inline-block;width: 350px;"
                            action="{{ route('getManageReport') }}">
                            <div class="input-group">
                                <input type="date" name="from" value="{{ request()->get('from') }}"
                                    class="form-control rounded-0 pull-right" placeholder="Search">
                                <input type="date" name="to" value="{{ request()->get('to') }}"
                                    class="form-control rounded-0 pull-right" placeholder="Search">
                                <div class="input-group-btn">
                                    @if (!empty(request()->get('q')))
                                        <button type="button" onclick="location.href='{{ route('getManageReport') }}'"
                                            title="Reset" class="btn rounded-0 btn-warning"><i
                                                class="fa fa-ban"></i></button>
                                    @endif
                                    <button type="submit" class="btn rounded-0 btn-primary me-2"><i
                                            class="fa fa-search"></i></button>
                                </div>
                            </div>
                        </form>


                        <form method="get" id="form-limit-paging" style="display:inline-block"
                            action="{{ route('getManageReport') }}">
                            @php $limis =[5,10,20,25,50,100,200]; @endphp
                            <div class="input-group">
                                <select onchange="$('#form-limit-paging').submit()" name="limit" style="width: 56px;"
                                    class="form-control input-sm">
                                    @foreach ($limis as $lmt)
                                        <option value="{{ $lmt }}" {{ $lmt == $limit ? 'selected' : '' }}>
                                            {{ $lmt }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </form>
                    </div>
                    <br style="clear:both">
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <form id="form-table" method="post" action="{{ route('getManageReport') }}/action-selected">
                            <input type='hidden' name='button_name' value='' />
                            @csrf
                            <table id="table_dashboard" class="table align-middle table-nowrap table-hover mb-0">
                                <thead class="table-blue">
                                    <tr class="active">
                                        <th width="3%"><input type="checkbox" id="checkall"></th>
                                        <th width="auto"><a href="{{ route('getManageReport') }}"
                                                title="Click to sort">Ride Id
                                        </th>
                                        <th width="auto"><a href="{{ route('getManageReport') }}"
                                                title="Click to sort">Driver Name
                                        </th>
                                        <th width="auto"><a href="{{ route('getManageReport') }}"
                                                title="Click to sort">Driver
                                                Mobile
                                        </th>
                                        <th width="auto"><a href="{{ route('getManageReport') }}"
                                                title="Click to sort">Passenger Name
                                        </th>
                                        <th width="auto"><a href="{{ route('getManageReport') }}"
                                                title="Click to sort">Passenger Mobile
                                        </th>
                                        <th width="auto"><a href="{{ route('getManageReport') }}"
                                                title="Click to sort">Booked sheet
                                        </th>
                                        <th width="auto"><a href="{{ route('getManageReport') }}"
                                                title="Click to sort">Total Fare
                                        </th>
                                        <th width="auto"><a href="{{ route('getManageReport') }}"
                                                title="Click to sort">Admin Share
                                        </th>
                                        <th width="auto"><a href="{{ route('getManageReport') }}"
                                                title="Click to sort">Driver
                                                Share
                                        </th>
                                        <th width="auto"><a href="{{ route('getManageReport') }}"
                                                title="Click to sort">Ride
                                                Date
                                        </th>
                                        <th width="auto"><a
                                                href="{{ route('getManageReport') }}?filter_column=status&sorting={{ request()->get('filter_column') == 'status' && request()->get('sorting') == 'asc' ? 'desc' : 'asc' }}"
                                                title="Click to sort">Status &nbsp; <i class="fa fa-sort"></i></a></th>
                                        <th width="auto"><a href="{{ route('getManageReport') }}"
                                                title="Click to sort">Action
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if (!empty($rides) && count($rides))
                                        @php
                                            $ChargePre = \DB::table('admin_settings')->first();
                                        @endphp
                                        @foreach ($rides as $data)
                                            <tr>
                                                <td><input type="checkbox" class="checkbox" name="checkbox[]"
                                                        value="{{ $data->id }}"></td>
                                                <td>#{{ $data->trip->id }}</td>
                                                <td>{{ $data->trip->driverDetails->name }}</td>
                                                <td>{{ $data->trip->driverDetails->phone }}</td>
                                                <td>{{ $data->user->name }}</td>
                                                <td>{{ $data->user->phone }}</td>
                                                <td>{{ (int) $data->total_seats }}</td>
                                                <td>{{ $data->total_fare }}</td>
                                                <td>{{ $data->booking_fee }}</td>
                                                <td>{{ $data->total_fare - $data->booking_fee }}
                                                </td>
                                                <td>{{ date('m-d-Y', strtotime($data->trip->departure_time)) }}</td>
                                                <td>
                                                    @if ($data->status == 1)
                                                        <span class="badge bg-warning">Pending</span>
                                                    @elseif($data->status == 2)
                                                        <span class="badge bg-success">Confirmed</span>
                                                    @elseif($data->status == 4)
                                                        <span class="badge bg-warning">Cancelled</span>
                                                    @else
                                                        <span class="badge bg-success">Finished</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if (!$data->trip->payout_status)
                                                        <a class="btn btn-sm btn-warning btn-delete" title="Delete"
                                                            href="javascript:;"
                                                            onclick="Swal.fire({
                                                            title: 'Are you sure ?',   
                                                            text: 'You will not be able to recover this record data!',  
                                                            icon: 'warning',
                                                            showCancelButton: !0,
                                                            confirmButtonText: 'Yes, Payout!',
                                                            cancelButtonText: 'No, cancel!',
                                                            confirmButtonClass: 'btn btn-primary w-xs me-2 mt-2',
                                                            cancelButtonClass: 'btn btn-danger w-xs mt-2',
                                                            buttonsStyling: !1,
                                                            showCloseButton: !0,
                                                        }).then(function (t) {
                                                            t.isConfirmed?location.href='{{ AdminHelper::adminpath() }}/update-payout-ride/{{ $data->trip->id }}':'' });">
                                                            Payout
                                                        </a>
                                                    @else
                                                        <span class="btn btn-sm btn-success btn-delete">Paid out</span>
                                                    @endif
                                                    @if (!$data->trip->refund_status)
                                                        <a class="btn btn-sm btn-danger btn-delete" title="Delete"
                                                            href="javascript:;"
                                                            onclick="Swal.fire({
                                                            title: 'Are you sure ?',   
                                                            text: 'You will not be able to recover this record data!',  
                                                            icon: 'warning',
                                                            showCancelButton: !0,
                                                            confirmButtonText: 'Yes, Refund!',
                                                            cancelButtonText: 'No, cancel!',
                                                            confirmButtonClass: 'btn btn-primary w-xs me-2 mt-2',
                                                            cancelButtonClass: 'btn btn-danger w-xs mt-2',
                                                            buttonsStyling: !1,
                                                            showCloseButton: !0,
                                                        }).then(function (t) {
                                                            t.isConfirmed?location.href='{{ AdminHelper::adminpath() }}/update-refunds-ride/{{ $data->trip->id }}':'' });">
                                                            Refund
                                                        </a>
                                                    @else
                                                        <span class="btn btn-sm btn-success btn-delete">Refunded</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr>
                                            <td colspan="8" style="text-align:center"><i class="fa fa-search"></i> No
                                                Data
                                                Avaliable</td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </form>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-4">
                            <span>Total rows : {{ $rides->total() }}</span>
                        </div>
                        <div class="col-md-8">
                            <div class="pull-right">{!! $rides->withQueryString()->links('pagination::bootstrap-4') !!} </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
        <div class="tab-pane fade" id="pills-profile" role="tabpanel" aria-labelledby="pills-profile-tab">
            <div class="card">
                <div class="card-header align-items-center d-flex">
                    <h4 class="card-title mb-0 flex-grow-1">{{ $page_title }}</h4>
                    <div class="box-tools pull-right" style="position: relative;margin-top: -5px;margin-right: -10px">

                        <form method="get" style="display:inline-block;width: 350px;"
                            action="{{ route('getManageReport') }}">
                            <div class="input-group">
                                <input type="date" name="from" value="{{ request()->get('from') }}"
                                    class="form-control rounded-0 pull-right" placeholder="Search">
                                <input type="date" name="to" value="{{ request()->get('to') }}"
                                    class="form-control rounded-0 pull-right" placeholder="Search">
                                <div class="input-group-btn">
                                    @if (!empty(request()->get('q')))
                                        <button type="button" onclick="location.href='{{ route('getManageReport') }}'"
                                            title="Reset" class="btn rounded-0 btn-warning"><i
                                                class="fa fa-ban"></i></button>
                                    @endif
                                    <button type="submit" class="btn rounded-0 btn-primary me-2"><i
                                            class="fa fa-search"></i></button>
                                </div>
                            </div>
                        </form>


                        <form method="get" id="form-limit-paging" style="display:inline-block"
                            action="{{ route('getManageReport') }}">
                            @php $limis =[5,10,20,25,50,100,200]; @endphp
                            <div class="input-group">
                                <select onchange="$('#form-limit-paging').submit()" name="limit" style="width: 56px;"
                                    class="form-control input-sm">
                                    @foreach ($limis as $lmt)
                                        <option value="{{ $lmt }}" {{ $lmt == $limit ? 'selected' : '' }}>
                                            {{ $lmt }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </form>

                    </div>

                    <br style="clear:both">

                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <form id="form-table" method="post" action="{{ route('getManageReport') }}/action-selected">
                            <input type='hidden' name='button_name' value='' />
                            @csrf
                            <table id="table_dashboard" class="table align-middle table-nowrap table-hover mb-0">
                                <thead class="table-blue">
                                    <tr class="active">
                                        <th width="3%"><input type="checkbox" id="checkall"></th>
                                        <th width="auto"><a href="{{ route('getManageReport') }}"
                                                title="Click to sort">Trip Id
                                        </th>
                                        <th width="auto"><a href="{{ route('getManageReport') }}"
                                                title="Click to sort">Driver Name
                                        </th>
                                        <th width="auto"><a href="{{ route('getManageReport') }}"
                                                title="Click to sort">Driver
                                                Mobile
                                        </th>
                                        <th width="auto"><a href="{{ route('getManageReport') }}"
                                                title="Click to sort">Passenger Name
                                        </th>
                                        <th width="auto"><a href="{{ route('getManageReport') }}"
                                                title="Click to sort">Passenger Mobile
                                        </th>
                                        <th width="auto"><a href="{{ route('getManageReport') }}"
                                                title="Click to sort">Booked sheet
                                        </th>
                                        <th width="auto"><a href="{{ route('getManageReport') }}"
                                                title="Click to sort">Total Fare
                                        </th>
                                        <th width="auto"><a href="{{ route('getManageReport') }}"
                                                title="Click to sort">Admin Share
                                        </th>
                                        <th width="auto"><a href="{{ route('getManageReport') }}"
                                                title="Click to sort">Driver
                                                Share
                                        </th>
                                        <th width="auto"><a href="{{ route('getManageReport') }}"
                                                title="Click to sort">Trip
                                                Date
                                        </th>
                                        <th width="auto"><a
                                                href="{{ route('getManageReport') }}?filter_column=status&sorting={{ request()->get('filter_column') == 'status' && request()->get('sorting') == 'asc' ? 'desc' : 'asc' }}"
                                                title="Click to sort">Status &nbsp; <i class="fa fa-sort"></i></a></th>
                                        <th width="auto"><a href="{{ route('getManageReport') }}"
                                                title="Click to sort">Action
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $ChargePre = \DB::table('admin_settings')->first();
                                    @endphp
                                    @if (!empty($trips) && count($trips))
                                        @foreach ($trips as $data)
                                            <tr>
                                                <td><input type="checkbox" class="checkbox" name="checkbox[]"
                                                        value="{{ $data->id }}"></td>
                                                <td>#{{ $data->id }}</td>
                                                @if (count($data->bid) > 0)
                                                    <td>{{ $data->bid[0]->driverDetails->name }}</td>
                                                    <td>{{ $data->bid[0]->driverDetails->phone }}</td>
                                                @else
                                                    <td>NA</td>
                                                    <td>NA</td>
                                                @endif
                                                <td>{{ $data->userDetails->name }}</td>
                                                <td>{{ $data->userDetails->phone }}</td>
                                                <td>{{ $data->seats_required }}</td>
                                                <td>
                                                    @if (count($data->bid) > 0)
                                                        {{ $data->bid[0]->proposed_fare }}
                                                    @else
                                                        0
                                                    @endif
                                                </td>
                                                <td>
                                                    @if (count($data->bid) > 0)
                                                        {{ ($data->bid[0]->proposed_fare * $ChargePre->trip_booking_fee) / 100 }}
                                                    @else
                                                        0
                                                    @endif
                                                </td>
                                                <td>
                                                    @if (count($data->bid) > 0)
                                                        {{ $data->bid[0]->proposed_fare - ($data->bid[0]->proposed_fare * $ChargePre->trip_booking_fee) / 100 }}
                                                    @else
                                                        0
                                                    @endif
                                                </td>
                                                <td>{{ date('m-d-Y', strtotime($data->created_at)) }}</td>
                                                <td>
                                                    @if ($data->status == 1)
                                                        <span class="badge bg-warning">Pending</span>
                                                    @elseif($data->status == 2)
                                                        <span class="badge bg-success">Confirmed</span>
                                                    @else
                                                        <span class="badge bg-success">Finished</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    {{-- {{ dd($data) }} --}}
                                                    @if (!$data->payout_status)
                                                        <a class="btn btn-sm btn-warning btn-delete" title="Payout"
                                                            href="javascript:;"
                                                            onclick="Swal.fire({
                                                            title: 'Are you sure ?',   
                                                            text: 'You will not be able to recover this record data!',  
                                                            icon: 'warning',
                                                            showCancelButton: !0,
                                                            confirmButtonText: 'Yes, Payout!',
                                                            cancelButtonText: 'No, cancel!',
                                                            confirmButtonClass: 'btn btn-primary w-xs me-2 mt-2',
                                                            cancelButtonClass: 'btn btn-danger w-xs mt-2',
                                                            buttonsStyling: !1,
                                                            showCloseButton: !0,
                                                        }).then(function (t) {
                                                            t.isConfirmed?location.href='{{ AdminHelper::adminpath() }}/update-payout-trip/{{ $data->id }}':'' });">
                                                            Payout
                                                        </a>
                                                    @else
                                                        <span class="btn btn-sm btn-success btn-delete">Paid out</span>
                                                    @endif

                                                    @if (!$data->refund_status)
                                                        <a class="btn btn-sm btn-danger btn-delete" title="Refund"
                                                            href="javascript:;"
                                                            onclick="Swal.fire({
                                                            title: 'Are you sure ?',   
                                                            text: 'You will not be able to recover this record data!',  
                                                            icon: 'warning',
                                                            showCancelButton: !0,
                                                            confirmButtonText: 'Yes, Refund!',
                                                            cancelButtonText: 'No, cancel!',
                                                            confirmButtonClass: 'btn btn-primary w-xs me-2 mt-2',
                                                            cancelButtonClass: 'btn btn-danger w-xs mt-2',
                                                            buttonsStyling: !1,
                                                            showCloseButton: !0,
                                                        }).then(function (t) {
                                                            t.isConfirmed?location.href='{{ AdminHelper::adminpath() }}/update-refunds-trip/{{ $data->id }}':'' });">
                                                            Refund
                                                        </a>
                                                    @else
                                                        <span class="btn btn-sm btn-success btn-delete">Refunded</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr>
                                            <td colspan="8" style="text-align:center"><i class="fa fa-search"></i> No
                                                Data
                                                Avaliable</td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </form>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-4">
                            <span>Total rows : {{ $trips->total() }}</span>
                        </div>
                        <div class="col-md-8">
                            <div class="pull-right">{!! $trips->withQueryString()->links('pagination::bootstrap-4') !!} </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
