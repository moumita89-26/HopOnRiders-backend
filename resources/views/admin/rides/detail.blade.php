@extends('admin::layouts.admin_template')
@section('content')
    <p><a title="Main Module" href="{{ route('getManageRide') }}"><i class="fa fa-chevron-circle-left "></i> &nbsp; Back To
            List Data </a></p>
    <style type="text/css">
        .img-thumb {
            height: 240px;
            width: 240px;
            border: 1px solid grey;
            padding: 10px;
            margin: 10px;
        }

        .circle {
            width: 12px;
            height: 12px;
            border: 2px solid #000;
            border-radius: 50%;
            background-color: white;
        }

        .dotted-line {
            border-left: 2px dotted #999;
            flex-grow: 1;
            margin: 0 auto;
        }

        .timeline-col {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-right: 15px;
        }

        .container {
            display: flex;
            align-items: flex-start;
            font-family: Arial, sans-serif;
            padding: 20px;
            gap: 10px;
        }

        .left {
            display: flex;
            flex-direction: column;
            align-items: center;
            position: relative;
            margin-right: 20px;
        }

        .label {
            font-weight: bold;
            margin-bottom: 8px;
        }

        .timeline {
            position: relative;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .timeline::before {
            content: "";
            position: absolute;
            top: 6px;
            bottom: 6px;
            left: 50%;
            transform: translateX(-50%);
            width: 2px;
            background-image: linear-gradient(to bottom,
                    #999 33%,
                    rgba(255, 255, 255, 0) 0%);
            background-position: right;
            background-size: 2px 8px;
            background-repeat: repeat-y;
            z-index: 0;
        }

        .circle {
            width: 12px;
            height: 12px;
            background-color: #ffffff;
            border-radius: 50%;
            z-index: 1;
            margin: 18px 0;
        }

        .right {
            flex: 1;
        }

        .timestamp {
            text-align: right;
            font-size: 14px;
            color: #290546;
            font-weight: 500;
            margin-bottom: 10px;
        }

        .locations>div {
            margin: 18px 0;
            color: #290546;
            font-size: 16px;
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
                        <div class="col-7 table-responsive">
                            <table id="table-detail" class="table table-striped">
                                <tbody>
                                    <tr>
                                        <td>Driver Name</td>
                                        <td>{{ $row->driverDetails ? $row->driverDetails->name : 'NA' }}</td>
                                    </tr>
                                    <tr>
                                        <td>origin</td>
                                        <td>
                                            <p>{{ $row->origin }}</p>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Destination</td>
                                        <td>
                                            <p>{{ $row->destination }}</p>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Available Seats</td>
                                        <td>
                                            <p>{{ $row->available_seats }}</p>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Departure Time</td>
                                        <td>
                                            <p>{{ \Carbon\Carbon::parse($row->departure_time)->format('d-m-Y H:s') }}</p>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Fare Per Seat</td>
                                        <td>
                                            <p>{{ $row->fare_per_seat }}</p>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Status</td>
                                        <td>
                                            @if ($row->status == 1)
                                                <span class="badge bg-warning">Pending</span>
                                            @elseif($row->status == 2)
                                                <span class="badge bg-success">Approved</span>
                                            @else
                                                <span class="badge bg-success">Completed</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Created At</td>
                                        <td>{{ \Carbon\Carbon::parse($row->created_at)->format('d M, Y H:s') }}</td>
                                    </tr>
                                </tbody>
                            </table>
                            <div class="container mt-4">
                                <div class="left">
                                    <div class="label">Start</div>
                                    <div class="timeline" id="timeline"></div>
                                </div>
                                <div class="right">
                                    <div class="timestamp" id="timestamp"></div>
                                    <div class="locations" id="locations"></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-5">
                            <h5 class="text-center"><b>Booking Details</b></h5>

                            <div class="table-responsive">

                                <table id="table_dashboard" class="table align-middle table-nowrap table-hover mb-0">
                                    <thead class="table-blue">
                                        <tr class="active">
                                            <th>Passenger Name </th>
                                            <th>Seats Booked </th>
                                            <th>Total Fare </th>
                                        </tr>

                                    </thead>
                                    <tbody>
                                        @foreach ($row->booking as $data)
                                            <tr>
                                                <td>{{ $data->userData ? $data->userData->name : 'NA' }}</td>
                                                <td>
                                                    {{ $data->seats_booked }}
                                                </td>
                                                <td>{{ $data->total_fare }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                        </div>
                    </div>


                </div>
            </div>
        </div>
        <!--END AUTO MARGIN-->

    </div>
@endsection
@push('bottom')
    <script>
        const stops = @json($row->wayPoint);
        const mainData = @json($row);
        // stops.unshift({
        //     destination: mainData.origin
        // })
        // stops.push({
        //     destination: mainData.destination
        // })
        const timestamp = mainData.departure_time;

        const timelineContainer = document.getElementById("timeline");
        const locationsContainer = document.getElementById("locations");
        const timestampContainer = document.getElementById("timestamp");

        // Populate timestamp
        timestampContainer.textContent = timestamp;

        // Dynamically render circles and locations
        stops.forEach((location) => {
            const circle = document.createElement("div");
            circle.className = "circle";
            timelineContainer.appendChild(circle);

            const loc = document.createElement("div");
            loc.textContent = location.destination;
            loc.className = "text-small-muted mb-4";
            locationsContainer.appendChild(loc);
        });
    </script>
@endpush
