@extends('admin::layouts.admin_template')
@section('content')

<div class="row">
	<div class="col-md-6 offset-md-3">
		<div class="card">
            <div class="card-header card-primary">
                <i class="fa fa-cog"></i> {{ $page_title }}
            </div>
            <div class="card-body">
                <form method="post" id="form" enctype="multipart/form-data" action="{{ route('postAddSaveBattleroom') }}">
                    @csrf
                    <div class="box-body">
                        <div class="form-group mb-3">
                            <label class="label-setting">Title<span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="title" value="{{ (old('title')?old('title'):(!empty($row)?$row->title:'')) }}" required>
                            @error('title')
		                        <div class="text-danger mt-1" role="alert">
		                            <strong>{{ $message }}</strong>
		                        </div>
		                    @enderror
                        </div> 
                        <div class="form-group mb-3">
                            <label class="label-setting">Short Description<span class="text-danger">*</span></label>
                            <textarea name="short_description" class="form-control" rows="3" required>{{ (old('short_description')?old('short_description'):(!empty($row)?$row->short_description:'')) }}</textarea>
                            @error('short_description')
		                        <div class="text-danger mt-1" role="alert">
		                            <strong>{{ $message }}</strong>
		                        </div>
		                    @enderror
                        </div>                       
                        <div class="form-group mb-3">
                            <label class="label-setting">Image 1</label>
                            <input type="file" name="image1" accept="image/*" class="form-control">
                            <div class="text-muted">File support only jpg,png,gif, Max 2 MB</div>  
                            @error('image1')
                                <div class="text-danger mt-1" role="alert">
                                    <strong>{{ $message }}</strong>
                                </div>
                            @enderror
                            @if(!empty($row->image1) && (Storage::exists($row->image1) || file_exists(public_path($row->image1))))
                            <div class="prev-img-thumb"><img src="{{ asset($row->image1) }}"></div>
                            <p><a class="btn btn-danger btn-primary btn-sm" href="{{AdminHelper::adminpath()}}/download-file?image1={{$row->image1}}"><i class="fa fa-download"></i> Download </a>
                            </p>
                            @endif                   
                        </div>
                        <div class="form-group mb-3">
                            <label class="label-setting">Image 2</label>
                            <input type="file" name="image2" accept="image/*" class="form-control">
                            <div class="text-muted">File support only jpg,png,gif, Max 2 MB</div>  
                            @error('image2')
                                <div class="text-danger mt-1" role="alert">
                                    <strong>{{ $message }}</strong>
                                </div>
                            @enderror
                            @if(!empty($row->image2) && (Storage::exists($row->image2) || file_exists(public_path($row->image2))))
                            <div class="prev-img-thumb"><img src="{{ asset($row->image2) }}"></div>
                            <p><a class="btn btn-danger btn-primary btn-sm" href="{{AdminHelper::adminpath()}}/download-file?image2={{$row->image2}}"><i class="fa fa-download"></i> Download </a>
                            </p>
                            @endif                   
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="label-setting">List 1</label>
                                    <input type="text" class="form-control" name="list1" value="{{ (old('list1')?old('list1'):(!empty($row)?$row->list1:'')) }}">
                                    @error('list1')
                                        <div class="text-danger mt-1" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </div>
                                    @enderror
                                </div> 
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="label-setting">List 2</label>
                                    <input type="text" class="form-control" name="list2" value="{{ (old('list2')?old('list2'):(!empty($row)?$row->list2:'')) }}">
                                    @error('list2')
                                        <div class="text-danger mt-1" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="label-setting">List 3</label>
                                    <input type="text" class="form-control" name="list3" value="{{ (old('list3')?old('list3'):(!empty($row)?$row->list3:'')) }}">
                                    @error('list3')
                                        <div class="text-danger mt-1" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </div>
                                    @enderror
                                </div>
                            </div>                        
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="label-setting">List 4</label>
                                    <input type="text" class="form-control" name="list4" value="{{ (old('list4')?old('list4'):(!empty($row)?$row->list4:'')) }}">
                                    @error('list4')
                                        <div class="text-danger mt-1" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div><!-- /.box-body -->
                    <div class="card-footer">
                        <div class="pull-right">
                            <input type="submit" name="submit" value="Save" class="btn btn-success">
                        </div>
                    </div><!-- /.box-footer-->
                </form>
            </div>
        </div>
	</div>
</div>
@endsection