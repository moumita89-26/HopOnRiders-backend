@extends('admin::layouts.admin_template')
@section('content')

<div class="list-grid-nav hstack gap-1 mb-3">
    @if(AdminHelper::isCreate())
    <!-- <a href="{{ route('getAddStaticBlock') }}?return_url={{ route('getStaticBlock') }}"
        id="btn_add_new_data" class="btn btn-primary" title="Add Data">
        <i class="fa fa-plus-circle"></i> {{__('Add Data')}}
    </a> -->
    @endif
</div>

<div class="card">
    <div class="card-header align-items-center d-flex">
        <h4 class="card-title mb-0 flex-grow-1">{{ $page_title }}</h4>
        <div class="box-tools pull-right" style="position: relative;margin-top: -5px;margin-right: -10px">   

            <form method="get" style="display:inline-block;width: 290px;"
                action="{{ route('getStaticBlock') }}">
                <div class="input-group">
                    <input type="text" name="q" value="{{ request()->get('q') }}" class="form-control rounded-0 pull-right" placeholder="{{__('Search')}}">

                    <div class="input-group-btn">
                        @if(!empty(request()->get('q')))
                        <button type="button" onclick="location.href='{{ route('getStaticBlock') }}'" title="Reset" class="btn rounded-0 btn-warning"><i class="fa fa-ban"></i></button>
                        @endif
                        <button type="submit" class="btn rounded-0 btn-primary me-2"><i class="fa fa-search"></i></button>
                    </div>
                </div>
            </form>


            <form method="get" id="form-limit-paging" style="display:inline-block"
                action="{{ route('getStaticBlock') }}">
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
            <form id="form-table" method="post" action="{{ route('getStaticBlock') }}/action-selected">
                <input type='hidden' name='button_name' value=''/>
                @csrf                
                <table id="table_dashboard" class="table align-middle table-nowrap table-hover mb-0">
                    <thead class="table-blue">
                        <tr class="active">                            
                            <th width="auto"><a href="{{ route('getStaticBlock') }}?filter_column=title&sorting={{(request()->get('filter_column')=='title' && request()->get('sorting')=='asc')?'desc':'asc'}}" title="Click to sort">{{__('Title')}} &nbsp; <i class="fa fa-sort"></i></a></th>
                            <th width="auto">{{__('Image')}}</th>
                            <th width="auto"><a href="{{ route('getStaticBlock') }}?filter_column=created_at&sorting={{(request()->get('filter_column')=='created_at' && request()->get('sorting')=='asc')?'desc':'asc'}}" title="Click to sort">{{__('Created At')}} &nbsp; <i class="fa fa-sort"></i></a></th>
                            <th width="auto" style="text-align:right">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if(!empty($rows) && count($rows))
                        @foreach($rows as $data)
                        <tr>
                            <td>{{ $data->title_en }}</td>                            
                            <td>@if($data->image!='')<img src="{{ asset($data->image) }}" width="50px" height="50px"> @else N\A  @endif</td>
                            <td>{{ date('Y-m-d', strtotime($data->created_at)) }}</td>
                            <td>
                                <div class="button_action" style="text-align:right">                                
                                @if(AdminHelper::isRead())
                                <a class="btn btn-sm btn-primary btn-detail" title="Detail Data" href="{{ route('getDetailStaticBlock', $data->id) }}?return_url={{ route('getStaticBlock') }}"><i class="fa fa-eye"></i></a>
                                @endif
                                @if(AdminHelper::isUpdate())
                                <a class="btn btn-sm btn-success btn-edit" title="Edit Data" href="{{ route('getEditStaticBlock', $data->id) }}?return_url={{ route('getStaticBlock') }}"><i class="fa fa-pencil"></i></a>
                                @endif

                                </div>
                            </td>
                        </tr>                        
                        @endforeach
                        @else
                        <tr>
                            <td colspan="5" style="text-align:center"><i class="fa fa-search"></i> {{__('No Data Available')}}</td>
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
                <span>{{__('Total rows')}} : {{ $rows->total() }}</span>
            </div>          
            <div class="col-md-8"> <div class="pull-right">{!! $rows->withQueryString()->links('pagination::bootstrap-4') !!} </div></div>         
        </div>
    </div>


</div>
@endsection