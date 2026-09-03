@extends('admin::layouts.admin_template')
@section('content')

<div class="list-grid-nav hstack gap-1 mb-3">
    <div class="selected-action" style="display:inline-block;position:relative;">
        <button class="btn btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false"><i
                class="fa fa-check-square-o"></i> Bulk Actions</button>
        <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="javascript:void(0)" data-name="active" title="Active Selected"><i
                        class="fa fa-check"></i> Active Selected</a></li>
            <li><a class="dropdown-item" href="javascript:void(0)" data-name="inactive" title="Inactive Selected"><i
                        class="fa fa-times"></i> Inactive Selected</a></li>
           <!--  <li><a class="dropdown-item text-danger" href="javascript:void(0)" data-name="delete" title="Delete Selected"><i
                        class="fa fa-trash"></i> Delete Selected</a></li> -->
        </ul>
    </div>
    <a href="{{ route('getAddCity') }}?return_url={{ route('getManageCity') }}"
        id="btn_add_new_data" class="btn btn-primary" title="Add Data">
        <i class="fa fa-plus-circle"></i> Add Data
    </a>    
</div>



<div class="card">
    <div class="card-header align-items-center d-flex">
        <h4 class="card-title mb-0 flex-grow-1">{{ $page_title }}</h4>
        <div class="box-tools pull-right" style="position: relative;margin-top: -5px;margin-right: -10px">   

            <form method="get" style="display:inline-block;width: 290px;"
                action="{{ route('getManageCity') }}">
                <div class="input-group">
                    <input type="text" name="q" value="{{ request()->get('q') }}" class="form-control rounded-0 pull-right" placeholder="Search">

                    <div class="input-group-btn">
                        @if(!empty(request()->get('q')))
                        <button type="button" onclick="location.href='{{ route('getManageCity') }}'" title="Reset" class="btn rounded-0 btn-warning"><i class="fa fa-ban"></i></button>
                        @endif
                        <button type="submit" class="btn rounded-0 btn-primary me-2"><i class="fa fa-search"></i></button>
                    </div>
                </div>
            </form>


            <form method="get" id="form-limit-paging" style="display:inline-block"
                action="{{ route('getManageCity') }}">
                @php $limis =[5,10,20,25,50,100,200]; @endphp
                <div class="input-group">
                    <select onchange="$('#form-limit-paging').submit()" name="limit" style="width: 56px;"
                        class="form-control input-sm">
                        @foreach($limis as $lmt)
                            <option value="{{ $lmt }}" {{ ($lmt==$limit)?'selected':'' }}>{{$lmt}}</option>
                        @endforeach
                    </select>
                </div>
            </form>

        </div>

        <br style="clear:both">

    </div>
    <div class="card-body">
        <div class="table-responsive">
            <form id="form-table" method="post" action="{{ route('getManageCity') }}/action-selected">
                <input type='hidden' name='button_name' value=''/>
                @csrf                
                <table id="table_dashboard" class="table align-middle table-nowrap table-hover mb-0">
                    <thead class="table-blue">
                        <tr class="active">
                            <th width="3%"><input type="checkbox" id="checkall"></th>
                            <th width="auto"><a href="{{ route('getManageCity') }}?filter_column=city_name&sorting={{(request()->get('filter_column')=='city_name_en' && request()->get('sorting')=='asc')?'desc':'asc'}}" title="Click to sort ascending">City Name(EN) &nbsp; <i class="fa fa-sort"></i></a></th>
                            <th width="auto"><a href="{{ route('getManageCity') }}?filter_column=city_name&sorting={{(request()->get('filter_column')=='city_name_fr' && request()->get('sorting')=='asc')?'desc':'asc'}}" title="Click to sort ascending">City Name(FR) &nbsp; <i class="fa fa-sort"></i></a></th>
                            <th width="auto"><a href="{{ route('getManageCity') }}?filter_column=city_name&sorting={{(request()->get('filter_column')=='city_name_ae' && request()->get('sorting')=='asc')?'desc':'asc'}}" title="Click to sort ascending">City Name(AR) &nbsp; <i class="fa fa-sort"></i></a></th>
                            <th width="auto"><a href="{{ route('getManageCity') }}?filter_column=city_name&sorting={{(request()->get('filter_column')=='city_name_de' && request()->get('sorting')=='asc')?'desc':'asc'}}" title="Click to sort ascending">City Name(DE) &nbsp; <i class="fa fa-sort"></i></a></th>
                            <th width="auto"><a href="{{ route('getManageCity') }}?filter_column=city_name&sorting={{(request()->get('filter_column')=='city_name_de' && request()->get('sorting')=='asc')?'desc':'asc'}}" title="Click to sort ascending">City Name(KA) &nbsp; <i class="fa fa-sort"></i></a></th>
                            <th width="auto">Country</th>
                            <th width="auto">Status</th>
                            <th width="auto" style="text-align:right">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if(!empty($rows) && count($rows))
                        @foreach($rows as $data)
                        <tr>
                            <td><input type="checkbox" class="checkbox" name="checkbox[]" value="{{$data->id}}"></td>
                            <td>{{ $data->city_name_en }}</td>
                            <td>{{ $data->city_name_fr }}</td>
                            <td>{{ $data->city_name_ae }}</td>
                            <td>{{ $data->city_name_de }}</td>
                            <td>{{ $data->city_name_ka }}</td>
                            <td>{{ (!empty($data->country))?$data->country->country_name:'Not Found' }}</td>
                             <td>@if($data->city_status==1) <span class="badge bg-success">Active</span> @else <span class="badge bg-warning">Inactive</span>  @endif</td>
                           
                            <td>
                                <div class="button_action" style="text-align:right">          
                                <a class="btn btn-sm btn-primary btn-detail" title="Detail Data" href="{{ route('getDetailCity', $data->id) }}?return_url={{ route('getManageCity') }}"><i class="fa fa-eye"></i></a>
                                <a class="btn btn-sm btn-success btn-edit" title="Edit Data" href="{{ route('getEditCity', $data->id) }}?return_url={{ route('getManageCity') }}"><i class="fa fa-pencil"></i></a>
                                <a class="btn btn-sm btn-warning btn-delete" title="Delete" href="javascript:;" onclick="Swal.fire({
                                    title: 'Are you sure ?',   
                                    text: 'You will not be able to recover this record data!',  
                                    icon: 'warning',
                                    showCancelButton: !0,
                                    confirmButtonText: 'Yes, delete it!',
                                    cancelButtonText: 'No, cancel!',
                                    confirmButtonClass: 'btn btn-primary w-xs me-2 mt-2',
                                    cancelButtonClass: 'btn btn-danger w-xs mt-2',
                                    buttonsStyling: !1,
                                    showCloseButton: !0,
                                }).then(function (t) {
                                    t.isConfirmed?location.href='{{ route('deleteCity', $data->id) }}':'' });">
                                    <i class="fa fa-trash"></i>
                                </a>                                

                                </div>
                            </td>
                        </tr>
                        @endforeach
                        @else
                        <tr>
                            <td colspan="5" style="text-align:center"><i class="fa fa-search"></i> No Data Available</td>
                        </tr>
                        @endif                        
                    </tbody>
                </table>

            </form>
            <!--END FORM TABLE-->           
            <!-- <div class="col-md-4"><span class="pull-right">Total rows
                    : 1 to 3 of 3</span></div> -->

        </div>
        <div class="row mt-3">
            <div class="col-md-4">
                <span>Total rows : {{ $rows->total() }}</span>
            </div>          
            <div class="col-md-8"> <div class="pull-right">{!! $rows->withQueryString()->links('pagination::bootstrap-4') !!} </div></div>         
        </div>
    </div>


</div>

@endsection