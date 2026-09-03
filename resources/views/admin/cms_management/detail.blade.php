@extends('admin::layouts.admin_template')
@section('content')
<p><a title="Main Module" href="{{ url()->previous() }}"><i class="fa fa-chevron-circle-left "></i> &nbsp; Back To List Data Manage CMS</a></p>

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
                                        <td>Page</td>
                                        <td>{{ $row->page_title }}</td>
                                    </tr>
                                    
                                    <tr>
                                        <td>Featured Image</td>
                                        <td>
                                            @if(!empty($row->featured_image) )
                                            <div class="prev-img-thumb"><img src="{{ asset('/public/'.$row->featured_image) }}"></div>
                                            @endif
                                        </td>
                                    </tr>
                                  
                                    <tr>
                                        <td>Page Content</td>
                                        <td>{!! $row->page_content !!}</td>
                                    </tr>
                                    <tr>
                                        <td>Meta Title</td>
                                        <td>{{ $row->meta_title }}</td>
                                    </tr>
                                    <tr>
                                        <td>Meta Keywords</td>
                                        <td>{{ $row->meta_keywords }}</td>
                                    </tr>
                                    <tr>
                                        <td>Meta Description</td>
                                        <td>{{ $row->meta_keywords }}</td>
                                    </tr>                                    
                                    <!-- <tr>
                                        <td>Status</td>
                                        <td>@if($row->status==1) <span class="badge bg-success">Active</span>@else<span class="badge bg-danger">@endif</td>
                                    </tr>     -->                                
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