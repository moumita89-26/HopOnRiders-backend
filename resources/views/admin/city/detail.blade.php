@extends('admin::layouts.admin_template')
@section('content')
<p><a title="Main Module" href="{{ route('getManageCity') }}"><i class="fa fa-chevron-circle-left "></i> &nbsp; Back To List Data Manage City</a></p>

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
                                        <td>City Name(English)</td>
                                        <td>{{ $row->city_name_en }}</td>
                                    </tr>
                                    <tr>
                                        <td>City Name(French)</td>
                                        <td>{{ $row->city_name_fr }}</td>
                                    </tr>
                                    <tr>
                                        <td>City Name(Arabic)</td>
                                        <td>{{ $row->city_name_ae }}</td>
                                    </tr>
                                    <tr>
                                        <td>Country</td>
                                        <td>{{ (!empty($row->country)?$row->country->country_name:'Not Found') }}</td>
                                    </tr>
                                     <tr>
                                        <td>Status </td>
                                        <td>@if($row->city_status==1) <span class="badge bg-success">Active</span> @else <span class="badge bg-warning">Inactive</span>  @endif</td>
                                    </tr>
                                    <tr>
                                        <td>Created At</td>
                                        <td>{{ date('d-m-Y', strtotime($row->created_at)) }}</td>
                                    </tr>
                                    
                                                                       
                                </tbody>
                            </table>
                        </div>
                    </div><!-- /.box-body -->                  

                </form>

            </div>
        </div>
    </div>
    <!--END AUTO MARGIN-->

</div>
@endsection