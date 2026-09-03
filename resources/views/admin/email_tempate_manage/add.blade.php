@extends('admin::layouts.admin_template')
@section('content')

<!-- <script type="text/javascript" src="https://cdn.ckeditor.com/ckeditor5/17.0.0/classic/ckeditor.js"></script> -->
<script type="text/javascript" src="https://cdn.ckeditor.com/4.19.1/standard/ckeditor.js"></script>

<p><a title="Main Module" href="{{ AdminHelper::adminpath() }}/manage-cms"><i class="fa fa-chevron-circle-left "></i> &nbsp; Back To List Data Manage CMS</a></p>

<div class="row">
	<div class="col-md-12">
		<div class="card">
			<div class="card-header card-primary align-items-center d-flex">
                <h4 class="card-title mb-0 flex-grow-1">{{ $page_title }}</h4>
                <div class="flex-shrink-0">
                </div>
            </div> 

            <div class="card-body">
            	<form action="{{ route('postAddEmailTemplate') }}" method="post" enctype="multipart/form-data">
            	@csrf
            	<input type="hidden" name="return_url" value="{{ route('getIndexEmailTemplate') }}">
            	<div class="row">
            		<div class="col-md-6">
            			<div class="mb-3 ">
					        <label class="form-label">Name <span class="text-danger" title="This field is required">*</span></label>
					        <input type="text" title="Page Title" class="form-control" name="name" value="{{ old('name')}}" placeholder="Name" required >					        
					        @error('name')
		                        <div class="text-danger mt-1" role="alert">
		                            <strong>{{ $message }}</strong>
		                        </div>
		                    @enderror
					        <p class="text-muted"></p>
					    </div>            			
            		</div>
            	<div class="col-md-6">
            			<div class="mb-3 ">
					        <label class="form-label">Slug <span class="text-danger" title="This field is required">*</span></label>
					        <input type="text" title="Page Title" class="form-control" name="slug" value="{{ old('slug') }}" placeholder="Template Slug" required >					        
					        @error('slug')
		                        <div class="text-danger mt-1" role="alert">
		                            <strong>{{ $message }}</strong>
		                        </div>
		                    @enderror
					        <p class="text-muted"></p>
					    </div>            			
            		</div>
            		<div class="col-md-6">
            			<div class="mb-3 ">
					        <label class="form-label">Subject <span class="text-danger" title="This field is required">*</span></label>
					        <input type="text" title="Page Title" class="form-control" name="subject" value="{{ old('subject') }}" placeholder="Subject" required >					        
					        @error('subject')
		                        <div class="text-danger mt-1" role="alert">
		                            <strong>{{ $message }}</strong>
		                        </div>
		                    @enderror
					        <p class="text-muted"></p>
					    </div>            			
            		</div>
            	
            		<div class="col-md-12">
            			<div class="mb-3 ">
					        <label class="form-label">Content</label>
					        <textarea name="content" class="form-control" id="description">{!! old('content') !!}</textarea>				        
					        @error('content')
		                        <div class="text-danger mt-1" role="alert">
		                            <strong>{{ $message }}</strong>
		                        </div>
		                    @enderror
					        <p class="text-muted"></p>
					    </div>            			
            		</div>

            			<div class="col-md-6">
            			<div class="mb-3 ">
					        <label class="form-label">Description <span class="text-danger" title="This field is required">*</span></label>
					        <input type="text" title="Description" class="form-control" name="description" value="{{ old('description') }}" placeholder="Description" required >					        
					        @error('description')
		                        <div class="text-danger mt-1" role="alert">
		                            <strong>{{ $message }}</strong>
		                        </div>
		                    @enderror
					        <p class="text-muted"></p>
					    </div>            			
            		</div>
            		
            		<div class="col-md-6">
            			<div class="mb-3 ">
					        <label class="form-label">From Name <span class="text-danger" title="This field is required">*</span></label>
					        <input type="text" title="From Name " class="form-control" name="from_name" value="{{ old('from_name') }}" placeholder="From Name" required>					        
					        @error('from_name')
		                        <div class="text-danger mt-1" role="alert">
		                            <strong>{{ $message }}</strong>
		                        </div>
		                    @enderror
					        <p class="text-muted"></p>
					    </div>            			
            		</div>
            		<div class="col-md-6">
            			<div class="mb-3 ">
					        <label class="form-label">From Email<span class="text-danger" title="This field is required">*</span></label>
					        <input type="email" title="From Email" class="form-control" name="from_email" value="{{ old('from_email') }}">						        
					        @error('from_email')
		                        <div class="text-danger mt-1" role="alert">
		                            <strong>{{ $message }}</strong>
		                        </div>
		                    @enderror
					        <p class="text-muted"></p>
					    </div>            			
            		</div>
            		<div class="col-md-6">
            			<div class="mb-3 ">
					        <label class="form-label">CC Email</label>
					        <input type="email" title="From Email" class="form-control" name="cc_email" value="{{ old('cc_email')}}">						        
					        @error('cc_email')
		                        <div class="text-danger mt-1" role="alert">
		                            <strong>{{ $message }}</strong>
		                        </div>
		                    @enderror
					        <p class="text-muted"></p>
					    </div>            			
            		</div>
            		
            	</div>
            	<div class="row g-3">
                        <div class="form-group">
                            <label class="control-label col-sm-2"></label>
                            <div class="col-sm-10">
                            	<a href="{{ route('getManageCMS') }}" class="btn btn-default"><i class="fa fa-chevron-circle-left"></i> Back</a>
                            	<!-- <input type="submit" name="submit" value="Save & Add More" class="btn btn-primary"> -->
                            	<input type="submit" name="submit" value="Save" class="btn btn-primary">
                            </div>
                        </div>
                    </div>
            	</form>
            </div>
		</div>
	</div>
</div>
@endsection
@push('bottom')
<script type="text/javascript">
	$(document).ready(function() {
	  /*$('#description').summernote({
	  	height: 300,
	  	placeholder: 'Type here...'
	  });*/

	  CKEDITOR.replace( 'description',{
	  	allowedContent : true,
	  	versionCheck: false
	  });

	  /*ClassicEditor
		.create( document.querySelector( '#description' ) )
	.catch( error => {
		console.error( error );
	} );*/

	});
</script>
@endpush