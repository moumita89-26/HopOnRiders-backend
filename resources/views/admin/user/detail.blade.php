@extends('admin::layouts.admin_template')
@section('content')
    <p><a title="Main Module" href="{{ route('getManageUser') }}"><i class="fa fa-chevron-circle-left "></i> &nbsp; Back To
            List Data Manage Customer</a></p>
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
                                        <td>Name</td>
                                        <td>{{ $row->name }}</td>
                                    </tr>
                                    <tr>
                                        <td>Unique ID</td>
                                        <td>{{ $row->unique_id }}</td>
                                    </tr>
                                    <tr>
                                        <td>Email</td>
                                        <td><a href="mailto:{{ $row->email }}">{{ $row->email }}</a></td>
                                    </tr>
                                    <tr>
                                        <td>Phone</td>
                                        <td>
                                            <a href="tel:{{ $row->phone }}">{{ $row->phone }}</a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Date of Birth</td>
                                        <td>
                                            <p>{{ $row->dob }}</p>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Role</td>
                                        <td>
                                            @if ($row->role == 1)
                                                <span class="badge bg-success">Driver</span>
                                            @else
                                                <span class="badge bg-warning">Passenger</span>
                                            @endif

                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Vehicle Make</td>
                                        <td>
                                            <p>{{ $row->vehicle_make }}</p>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Vehicle Model</td>
                                        <td>
                                            <p>{{ $row->vehicle_model }}</p>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Vehicle Color</td>
                                        <td>
                                            <p>{{ $row->vehicle_color }}</p>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Registration Number</td>
                                        <td>
                                            <p>{{ $row->registration_number }}</p>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Number Of Seat</td>
                                        <td>
                                            <p>{{ $row->number_of_seat }}</p>
                                        </td>
                                    </tr>
                                    {{-- <tr>
                                        <td>Fuel Cost Per Km</td>
                                        <td>
                                            <p>{{ $row->fuel_cost_per_km }}</p>
                                        </td>
                                    </tr> --}}
                                    <tr>
                                        <td>NRC No</td>
                                        <td>
                                            <p>{{ $row->nrc_no }}</p>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>NRC No</td>
                                        <td>
                                            <p>{{ $row->nrc_no }}</p>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>License No</td>
                                        <td>
                                            <p>{{ $row->license_no }}</p>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Driver Experience</td>
                                        <td>
                                            <p>{{ $row->driver_experience }}</p>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Travel Preferences</td>
                                        <td>
                                            <p>{{ $row->travel_preferences }}</p>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Verified</td>
                                        <td>
                                            @if ($row->is_verified == 1)
                                                <span class="badge bg-success">Approved</span>
                                            @else
                                                <span class="badge bg-warning">Pending</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Document Verified</td>
                                        <td>
                                            @if ($row->is_document_verify == 1)
                                                <span class="badge bg-success">Approved</span>
                                            @else
                                                <span class="badge bg-warning">Pending</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Created At</td>
                                        <td>{{ $row->created_at }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="row gap-2">
                        <div class="col-2 mr-2 text-center">
                            <h6>NRC Front</h6>
                            <img style="width: 200px" src="{{ asset($row->nrc_front) }}" alt="">
                        </div>
                        <div class="col-2 mr-2 text-center">
                            <h6>NRC Back</h6>
                            <img style="width: 200px" src="{{ asset($row->nrc_back) }}" alt="">
                        </div>
                        <div class="col-2 mr-2 text-center">
                            <h6>License Front</h6>
                            <img style="width: 200px" src="{{ asset($row->license_front) }}" alt="">
                        </div>
                        <div class="col-2 mr-2 text-center">
                            <h6>License Back</h6>
                            <img style="width: 200px" src="{{ asset($row->license_back) }}" alt="">
                        </div>

                        <div class="col-2 mr-2 text-center">
                            <h6>Car Image</h6>
                            <img style="width: 200px" src="{{ asset($row->car_image) }}" alt="">
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!--END AUTO MARGIN-->

    </div>
@endsection
