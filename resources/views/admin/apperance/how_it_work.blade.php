@extends('admin::layouts.admin_template')
@section('content')
<div class="card">
    <div class="card-header align-items-center d-flex">
        <h4 class="card-title mb-0 flex-grow-1">{{ $page_title }}</h4>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <form id="form-table" method="post" action="{{ route('getManageHowitworks') }}/action-selected">
                <input type='hidden' name='button_name' value=''/>
                @csrf                
                <table id="table_dashboard" class="table align-middle table-nowrap table-hover mb-0">
                    <thead class="table-blue">
                        <tr class="active">                            
                            <th>Title</th>
                            <th>Image</th>                            
                            <th width="auto" style="text-align:right">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if(!empty($row))                        
                        <tr>
                            <td>{{ $row->title }}</td>                           
                            <td>@if($row->image!='')<img src="{{ asset($row->image) }}" width="50px" height="50px"> @else N\A  @endif</td>
                            <td>
                                <div class="button_action" style="text-align:right">
                                <a class="btn btn-sm btn-info btn-edit" title="Steps" href="{{ route('getStepHowitwork', $row->id) }}">Steps</a>
                                <a class="btn btn-sm btn-success btn-edit" title="Edit Data" href="{{ route('getEditHowitwork', $row->id) }}?return_url={{ route('getManageHowitworks') }}"><i class="fa fa-pencil"></i></a>
                                </div>
                            </td>
                        </tr>                        
                        @else
                        <tr>
                            <td colspan="4" style="text-align:center"><i class="fa fa-search"></i> No Data Avaliable</td>
                        </tr>
                        @endif                        
                    </tbody>
                </table>

            </form>
            <!--END FORM TABLE-->           
            <!-- <div class="col-md-4"><span class="pull-right">Total rows
                    : 1 to 3 of 3</span></div> -->

        </div>
    </div>


</div>

@endsection