@extends('admin::layouts.admin_template')
@section('content')
<p><a title="Main Module" href="{{ url()->previous() }}"><i class="fa fa-chevron-circle-left "></i> &nbsp; Back To List Data Email Templates</a></p>

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
                                        <td>template Slug</td>
                                        <td>
                                            {{ $row->slug }}
                                        </td>
                                    </tr>
                                     <tr>
                                        <td>Subject</td>
                                        <td>{{ $row->subject }}</td>
                                    </tr>
                                    <tr>
                                        <td>Content</td>
                                        <td>{!! $row->content !!}</td>
                                    </tr>
                                    <tr>
                                        <td>Description</td>
                                        <td>{{ $row->description }}</td>
                                    </tr>
                                    <tr>
                                        <td>From Email</td>
                                        <td>{{ $row->from_email }}</td>
                                    </tr>
                                     <tr>
                                        <td>From Name</td>
                                        <td>{{ $row->from_name }}</td>
                                    </tr>
                                    <tr>
                                        <td>CC Email</td>
                                        <td>{{ $row->cc_email }}</td>
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