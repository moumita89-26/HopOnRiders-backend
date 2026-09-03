@extends('admin::layouts.admin_template')
@section('content')
<p><a title="Main Module" href="{{ route('getStaticBlock') }}"><i class="fa fa-chevron-circle-left "></i> &nbsp; {{__('Back To List Data')}} {{__('Static Block')}}</a></p>

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
                                        <td>Title(English)</td>
                                        <td>{{ $row->title_en }}</td>
                                    </tr>
                                    <tr>
                                        <td>Title(French)</td>
                                        <td>{{ $row->title_fr }}</td>
                                    </tr>
                                    <tr>
                                        <td>Title(German)</td>
                                        <td>{{ $row->title_de }}</td>
                                    </tr>
                                    <tr>
                                        <td>Title(Arabic)</td>
                                        <td>{{ $row->title_ae }}</td>
                                    </tr>
                                    @if($row->has_image==1)                               
                                    <tr>
                                        <td>{{ __('Image') }}</td>
                                        <td>
                                            @if(!empty($row->image) )
                                            <div class="prev-img-thumb"><img src="{{ asset($row->image) }}"></div>
                                            @endif
                                        </td>
                                    </tr> 
                                    @endif                               
                                    <tr>
                                        <td>Content(English)</td>
                                        <td>{!! $row->content_en !!}</td>
                                    </tr>
                                    <tr>
                                        <td>Content(French)</td>
                                        <td>{!! $row->content_fr !!}</td>
                                    </tr>
                                    <tr>
                                        <td>Content(German)</td>
                                        <td>{!! $row->content_de !!}</td>
                                    </tr>
                                    <tr>
                                        <td>Content(Arabic)</td>
                                        <td>{!! $row->content_ae !!}</td>
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