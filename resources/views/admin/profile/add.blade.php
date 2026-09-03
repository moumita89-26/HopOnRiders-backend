@extends('admin::layouts.admin_template')
@section('content')

<div class="row">
	<div class="col-md-6 offset-md-3">
		<div class="card">
            <div class="card-header card-primary">
                <i class="fa fa-cog"></i> {{ $page_title }}
            </div>
            <div class="card-body">
                <form method="post" id="form" enctype="multipart/form-data" action="{{ route('postSaveProfile') }}">
                    @csrf
                    <div class="box-body">
                        <div class="form-group mb-3">
                            <label class="label-setting">Name<span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="name" value="{{ (old('name')?old('name'):(!empty($row)?$row->name:'')) }}" required>
                            @error('name')
		                        <div class="text-danger mt-1" role="alert">
		                            <strong>{{ $message }}</strong>
		                        </div>
		                    @enderror
                        </div>                                               
                        <div class="form-group mb-3">
                            <label class="label-setting">Email<span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="email" value="{{ (old('email')?old('email'):(!empty($row)?$row->email:'')) }}" required>
                            @error('email')
                                <div class="text-danger mt-1" role="alert">
                                    <strong>{{ $message }}</strong>
                                </div>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label for="passwordInput" class="form-label">Password </label>
                            <input type="password" title="Password" class="form-control" name="password" id="passwordInput" value="{{ old('password') }}" placeholder="Password">
                            <p class="text-muted"><em>* Leave blank if do not want to change password.</em></p>
                            @error('password')
                                <div class="text-danger mt-1" role="alert">
                                    <strong>{{ $message }}</strong>
                                </div>
                            @enderror
                            <p class="text-muted"></p>
                        </div>  

                        <div class="form-group mb-3">
                            <label class="label-setting">Photo</label>                           
                            @if(!empty($row->photo) && (Storage::exists($row->photo) || file_exists(public_path($row->photo))))
                            <div class="prev-img-thumb"><img src="{{ asset($row->photo) }}"></div>
                            <p class="text-muted"><em>* If you want to upload other image, please first delete the image.</em></p>
                            <p><a class="btn btn-primary btn-sm" href="{{AdminHelper::adminpath()}}/download-file?image={{$row->photo}}"><i class="fa fa-download"></i> Download </a>
                            <a class="btn btn-danger btn-delete btn-sm" onclick="if(!confirm('Are you sure ?')) return false" href="{{AdminHelper::adminpath()}}/delete-image?image={{$row->photo}}&&id={{$row->id}}&&column=photo&table=admin_users"><i class="fa fa-ban"></i> Delete </a></p>
                            @else
                            <input type="file" name="photo" accept="image/*" class="form-control">
                            <div class="text-muted">File support only jpg,png,gif, Max 2 MB</div>  
                            @error('photo')
                                <div class="text-danger mt-1" role="alert">
                                    <strong>{{ $message }}</strong>
                                </div>
                            @enderror       
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