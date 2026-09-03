@extends('admin::layouts.admin_template')
@section('content')
    <p><a title="Main Module" href="{{ route('getManageTrip') }}"><i class="fa fa-chevron-circle-left "></i> &nbsp; Back To
            List Data</a></p>
    <style type="text/css">
        .img-thumb {
            height: 240px;
            width: 240px;
            border: 1px solid grey;
            padding: 10px;
            margin: 10px;
        }
    </style>
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header card-primary align-items-center d-flex">
                    <h4 class="card-title mb-0 flex-grow-1">{{ $page_title }}</h4>
                    <div class="flex-shrink-0">
                    </div>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="table-responsive">
                            <table id="table-detail" class="table table-striped">
                                <tbody>
                                    <tr>
                                        <td>Passenger Name</td>
                                        <td>{{ $row->userDetails ? $row->userDetails->name : 'NA' }}</td>
                                    </tr>
                                    <tr>
                                        <td>Pickup Point</td>
                                        <td>
                                            <p>{{ $row->pickup_point }}</p>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Drop Point</td>
                                        <td>
                                            <p>{{ $row->dropoff_point }}</p>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Seats Required</td>
                                        <td>
                                            <p>{{ $row->seats_required }}</p>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Requested Date</td>
                                        <td>
                                            <p>{{ \Carbon\Carbon::parse($row->requested_date)->format('d-m-Y H:s') }}</p>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Luggage Count</td>
                                        <td>
                                            <p>{{ $row->luggage_count }}</p>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Cart Type</td>
                                        <td>
                                            <p>{{ $row->carDetails->type_name }}</p>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Status</td>
                                        <td>
                                            @if ($row->status == 1)
                                                <span class="badge bg-success">Approved</span>
                                            @else
                                                <span class="badge bg-warning">Pending</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Created At</td>
                                        <td>{{ \Carbon\Carbon::parse($row->created_at)->format('d-m-Y H:s') }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!--END AUTO MARGIN-->

    </div>
@endsection
