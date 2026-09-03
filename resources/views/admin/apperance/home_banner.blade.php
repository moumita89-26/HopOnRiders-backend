@extends('admin::layouts.admin_template')
@section('content')

<div class="row">
	<div class="col-md-6 offset-md-3">
		<div class="card">
            <div class="card-header card-primary">
                <i class="fa fa-cog"></i> {{ $page_title }}
            </div>
            <div class="card-body">
                <form method="post" id="form" enctype="multipart/form-data" action="{{ route('postAddSaveHomebanner') }}">
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
                            <label class="label-setting">Sub Title<span class="text-danger">*</span></label>
                            <textarea name="sub_title" class="form-control" rows="3" required>{{ (old('sub_title')?old('sub_title'):(!empty($row)?$row->sub_title:'')) }}</textarea>
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
                            <div class="prev-img-thumb"><img src="{{ asset($row->image) }}"></div>
                            <!-- <p class="text-muted"><em>* If you want to upload other image, please first delete the image.</em></p> -->
                            <p><a class="btn btn-danger btn-primary btn-sm" href="{{AdminHelper::adminpath()}}/download-file?image={{$row->image}}"><i class="fa fa-download"></i> Download </a>
                            <!-- <a class="btn btn-danger btn-delete btn-sm" onclick="if(!confirm('Are you sure ?')) return false" href="{{AdminHelper::adminpath()}}/delete-image?image={{$row->logo}}&&id={{$row->id}}&&column=logo&table=admin_settings"><i class="fa fa-ban"></i> Delete </a> -->
                            </p>
                            @endif                   
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