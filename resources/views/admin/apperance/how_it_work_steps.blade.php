@extends('admin::layouts.admin_template')
@section('content')
<p><a title="Main Module" href="{{ route('getManageHowitworks') }}"><i class="fa fa-chevron-circle-left "></i> &nbsp; Back To List Data</a></p>
<div class="list-grid-nav hstack gap-1 mb-3">
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addDataModal">
      <i class="fa fa-plus-circle"></i> Add Data
    </button>
    <!-- Modal -->
    <div class="modal fade" id="addDataModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="staticBackdropLabel">Add Steps</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <form action="{{ route('postAddStepHow', $id) }}" method="post" enctype="multipart/form-data">
                @csrf
                <div class="form-group mb-3">
                    <label class="label-setting">Title<span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="title" value="{{ old('title') }}" required>
                    @error('title')
                        <div class="text-danger mt-1" role="alert">
                            <strong>{{ $message }}</strong>
                        </div>
                    @enderror
                </div> 
                <div class="form-group mb-3">
                    <label class="label-setting">Sub Title<span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="sub_title" value="{{ old('sub_title') }}" required>
                    @error('sub_title')
                        <div class="text-danger mt-1" role="alert">
                            <strong>{{ $message }}</strong>
                        </div>
                    @enderror
                </div>                       
                <div class="form-group mb-3">
                    <label class="label-setting">Image</label>
                    <input type="file" name="image" accept="image/*" class="form-control">
                    <div class="text-muted">File support only jpg,png,gif, Max 2 MB</div>  
                    @error('image')
                        <div class="text-danger mt-1" role="alert">
                            <strong>{{ $message }}</strong>
                        </div>
                    @enderror
                </div>
                <div class="form-group">                    
                    <div class="col-sm-10">                        
                        <input type="submit" name="submit" value="Save" class="btn btn-success">
                    </div>
                </div>
            </form>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>            
          </div>
        </div>
      </div>
    </div>
</div>
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
                            <th>Sub Title</th>
                            <th>Image</th>                            
                            <th width="auto" style="text-align:right">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if(!empty($rows) && count($rows))                        
                        @foreach($rows as $row)
                        <tr>
                            <td>{{ $row->title }}</td>                           
                            <td>{{ $row->sub_title }}</td>                           
                            <td>@if($row->image!='')<img src="{{ asset($row->image) }}" width="50px" height="50px" style="background-color: #b20d8d;"> @else N\A  @endif</td>
                            <td>
                                <div class="button_action" style="text-align:right">
                                    <button type="button" class="btn btn-sm btn-success btn-edit" data-bs-toggle="modal" data-bs-target="#editDataModal{{$row->id}}">
                                      <i class="fa fa-pencil"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>  
                        <!-- Modal -->
                        <div class="modal fade" id="editDataModal{{$row->id}}" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                          <div class="modal-dialog">
                            <div class="modal-content">
                              <div class="modal-header">
                                <h5 class="modal-title" id="staticBackdropLabel">Edit Steps</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                              </div>
                              <div class="modal-body">
                                <form action="{{ route('postUpdateStepHow', $row->id) }}" method="post" enctype="multipart/form-data">
                                    @csrf
                                    <div class="form-group mb-3">
                                        <label class="label-setting">Title<span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" name="title" value="{{ $row->title }}" required>
                                        @error('title')
                                            <div class="text-danger mt-1" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </div>
                                        @enderror
                                    </div> 
                                    <div class="form-group mb-3">
                                        <label class="label-setting">Sub Title<span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" name="sub_title" value="{{ $row->sub_title }}" required>
                                        @error('sub_title')
                                            <div class="text-danger mt-1" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </div>
                                        @enderror
                                    </div>                       
                                    <div class="form-group mb-3">
                                        <label class="label-setting">Image</label>
                                        <input type="file" name="image" accept="image/*" class="form-control">
                                        <div class="text-muted">File support only jpg,png,gif, Max 2 MB</div>  
                                        @error('image')
                                            <div class="text-danger mt-1" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </div>
                                        @enderror
                                        @if(!empty($row->image) && (Storage::exists($row->image) || file_exists(public_path($row->image))))
                                            <div class="prev-img-thumb"><img src="{{ asset($row->image) }}" style="background-color: #b20d8d;"></div>
                                            <p><a class="btn btn-danger btn-primary btn-sm" href="{{AdminHelper::adminpath()}}/download-file?image={{$row->image}}"><i class="fa fa-download"></i> Download </a>
                                            </p>
                                        @endif 
                                    </div>
                                    <div class="form-group">                    
                                        <div class="col-sm-10">                        
                                            <input type="submit" name="submit" value="Save" class="btn btn-success">
                                        </div>
                                    </div>
                                </form>
                              </div>
                              <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>            
                              </div>
                            </div>
                          </div>
                        </div>   
                        @endforeach                   
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