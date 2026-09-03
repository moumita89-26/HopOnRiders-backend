@extends('admin::layouts.admin_template')
@section('content')
    <div class="card">
        <div class="card-header align-items-center d-flex">
            <h4 class="card-title mb-0 flex-grow-1">{{ $page_title }}</h4>
            <div class="box-tools pull-right" style="position: relative;margin-top: -5px;margin-right: -10px">

                <form method="get" style="display:inline-block;width: 290px;" action="{{ route('getManageTrip') }}">
                    <div class="input-group">
                        <input type="text" name="q" value="{{ request()->get('q') }}"
                            class="form-control rounded-0 pull-right" placeholder="Search">

                        <div class="input-group-btn">
                            @if (!empty(request()->get('q')))
                                <button type="button" onclick="location.href='{{ route('getManageTrip') }}'" title="Reset"
                                    class="btn rounded-0 btn-warning"><i class="fa fa-ban"></i></button>
                            @endif
                            <button type="submit" class="btn rounded-0 btn-primary me-2"><i
                                    class="fa fa-search"></i></button>
                        </div>
                    </div>
                </form>
            </div>

            <br style="clear:both">

        </div>
        <div class="card-body">
            <div class="table-responsive">
                <form id="form-table" method="post" action="{{ route('getManageTrip') }}/action-selected">
                    <input type='hidden' name='button_name' value='' />
                    @csrf
                    <table id="table_dashboard" class="table align-middle table-nowrap table-hover mb-0">
                        <thead class="table-blue">
                            <tr class="active">
                                <th width="3%"><input type="checkbox" id="checkall"></th>
                                <th width="auto"><a href="{{ route('getManageTrip') }}">Passenger
                                        Name
                                </th>
                                <th width="auto"><a href="{{ route('getManageTrip') }}">Driver Name

                                </th>
                                <th width="auto"><a href="{{ route('getManageTrip') }}">Proposed Fare &nbsp; <i
                                            class="fa fa-sort"></i></a></th>
                                <th width="auto"><a href="{{ route('getManageTrip') }}">Status &nbsp; <i
                                            class="fa fa-sort"></i></a></th>
                                <th width="auto"><a
                                        href="{{ route('getManageTrip') }}?filter_column=created_at&sorting={{ request()->get('filter_column') == 'created_at' && request()->get('sorting') == 'asc' ? 'desc' : 'asc' }}"
                                        title="Click to sort">Created At &nbsp; <i class="fa fa-sort"></i></a></th>
                            </tr>
                        </thead>
                        <tbody>
                            @if (!empty($rows) && count($rows))
                                @foreach ($rows as $data)
                                    <tr>
                                        <td><input type="checkbox" class="checkbox" name="checkbox[]"
                                                value="{{ $data->id }}"></td>
                                        <td>
                                            {{ $data->trip->userDetails->name }}
                                        </td>
                                        <td>{{ $data->driverDetails->name }}</td>
                                        <td>{{ $data->proposed_fare }}</td>
                                        <td>
                                            @if ($data->status == 1)
                                                <span class="badge bg-warning">Pending</span>
                                            @elseif($data->status == 2)
                                                <span class="badge bg-success">Confirmed</span>
                                            @else
                                                <span class="badge bg-success">Finished</span>
                                            @endif
                                        </td>
                                        <td>{{ date('m-d-Y', strtotime($data->created_at)) }}</td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="8" style="text-align:center"><i class="fa fa-search"></i> No Data
                                        Avaliable</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>

                </form>
            </div>
            <div class="row mt-3">
                <div class="col-md-4">
                    <span>Total rows : {{ $rows->total() }}</span>
                </div>
                <div class="col-md-8">
                    <div class="pull-right">{!! $rows->withQueryString()->links('pagination::bootstrap-4') !!} </div>
                </div>
            </div>
        </div>


    </div>
@endsection
