@extends('admin::layouts.admin_template')
@section('content')
<!-- <script type="text/javascript" src="https://cdn.ckeditor.com/ckeditor5/17.0.0/classic/ckeditor.js"></script> -->
<script type="text/javascript" src="https://cdn.ckeditor.com/4.19.1/standard/ckeditor.js"></script>

<p><a title="Main Module" href="{{ route('getStaticBlock') }}"><i class="fa fa-chevron-circle-left "></i> &nbsp; {{__('Back To List Data')}} {{__('Static Block')}}</a></p>

<div class="row">
	<div class="col-md-12">
		<div class="card">
			<div class="card-header card-primary align-items-center d-flex">
                <h4 class="card-title mb-0 flex-grow-1">{{ $page_title }}</h4>
                <div class="flex-shrink-0">
                </div>
            </div> 

            <div class="card-body">
            	<form action="{{ route('postUpdateStaticBlock', $row->id) }}" method="post" enctype="multipart/form-data">
            	@csrf
            	<input type="hidden" name="return_url" value="{{ route('getStaticBlock') }}">
            	<div class="row">
            		<div class="col-md-4">
            			<div class="mb-3 ">
					        <label class="form-label">Title(English) <span class="text-danger" title="This field is required">*</span></label>
					        <input type="text" title="Title" class="form-control" name="title_en" value="{{ (old('title_en'))?old('title_en'):$row->title_en }}" placeholder="Title" required>					        
					        @error('title_en')
		                        <div class="text-danger mt-1" role="alert">
		                            <strong>{{ $message }}</strong>
		                        </div>
		                    @enderror
					        <p class="text-muted"></p>
					    </div>            			
            		</div>
            		<div class="col-md-4">
            			<div class="mb-3 ">
					        <label class="form-label">Title(French) <span class="text-danger" title="This field is required">*</span></label>
					        <input type="text" title="Title" class="form-control" name="title_fr" value="{{ (old('title_fr'))?old('title_fr'):$row->title_fr }}" placeholder="Title" required>					        
					        @error('title_en')
		                        <div class="text-danger mt-1" role="alert">
		                            <strong>{{ $message }}</strong>
		                        </div>
		                    @enderror
					        <p class="text-muted"></p>
					    </div>            			
            		</div>
            		<div class="col-md-4">
            			<div class="mb-3 ">
					        <label class="form-label">Title(German) <span class="text-danger" title="This field is required">*</span></label>
					        <input type="text" title="Title" class="form-control" name="title_de" value="{{ (old('title_de'))?old('title_de'):$row->title_de }}" placeholder="Title" required>					        
					        @error('title_de')
		                        <div class="text-danger mt-1" role="alert">
		                            <strong>{{ $message }}</strong>
		                        </div>
		                    @enderror
					        <p class="text-muted"></p>
					    </div>            			
            		</div>
            		<div class="col-md-4">
            			<div class="mb-3 ">
					        <label class="form-label">Title(Arabic) <span class="text-danger" title="This field is required">*</span></label>
					        <input type="text" title="Title" class="form-control" name="title_ae" value="{{ (old('title_ae'))?old('title_ae'):$row->title_ae }}" placeholder="Title" required>					        
					        @error('title_ae')
		                        <div class="text-danger mt-1" role="alert">
		                            <strong>{{ $message }}</strong>
		                        </div>
		                    @enderror
					        <p class="text-muted"></p>
					    </div>            			
            		</div>
            		<div class="col-md-4">
            			<div class="mb-3 ">
					        <label class="form-label">Title(Georgian) <span class="text-danger" title="This field is required">*</span></label>
					        <input type="text" title="Title" class="form-control" name="title_ka" value="{{ (old('title_ka'))?old('title_ka'):$row->title_ka }}" placeholder="Title" required>					        
					        @error('title_ka')
		                        <div class="text-danger mt-1" role="alert">
		                            <strong>{{ $message }}</strong>
		                        </div>
		                    @enderror
					        <p class="text-muted"></p>
					    </div>            			
            		</div>
            		@if($row->has_image==1)
            		<div class="col-md-4">
            			<!-- <div class="mb-3 ">
					        <label class="form-label">Featured Image</label>
					        <input type="file" class="form-control" name="image" accept="image/*">					        
					        @error('image')
		                        <div class="text-danger mt-1" role="alert">
		                            <strong>{{ $message }}</strong>
		                        </div>
		                    @enderror
					        <p class="text-muted">The image should be JPG/JPEG/PNG/GIF/SVG type and the image size should not above 5MB.</p>
					    </div> -->  
					     <div class="form-group mb-3">
                            <label class="label-setting">{{__('Image')}}</label>
                            <input type="file" name="image" id="image" accept="image/*" class="form-control" >
                            <div class="text-muted">The image should be JPG/JPEG/PNG/GIF/SVG type and the image size should not above 2MB.</div>  
                            @error('image')
                                <div class="text-danger mt-1" role="alert">
                                    <strong>{{ $message }}</strong>
                                </div>
                            @enderror 
                            @if(!empty($row->image) && (Storage::exists($row->image) || file_exists(public_path($row->image))))
                            <div class="prev-img-thumb"><img src="{{ asset($row->image) }}"></div>
                            <!-- <p class="text-muted"><em>* If you want to upload other image, please first delete the image.</em></p> -->
                            <p><a class="btn btn-danger btn-primary btn-sm" href="{{AdminHelper::adminpath()}}/download-file?image={{$row->image}}"><i class="fa fa-download"></i> Download </a>
                            <!-- <a class="btn btn-danger btn-delete btn-sm" onclick="if(!confirm('Are you sure ?')) return false" href="{{AdminHelper::adminpath()}}/delete-image?image={{$row->featured_image}}&&id={{$row->id}}&&column=featured_image&table=cms_pages"><i class="fa fa-ban"></i> Delete </a> -->
                        	</p>
                            @endif                   
                        </div>            			
            		</div>
            		@endif
            		@if(in_array($row->id, [3]))
            		<div class="col-md-4">
					     <div class="form-group mb-3">
                            <label class="label-setting">{{__('Video')}}</label>
                            <input type="file" name="video" id="video" accept="video/mp4,video/x-m4v" class="form-control" >                            
                            @error('video')
                                <div class="text-danger mt-1" role="alert">
                                    <strong>{{ $message }}</strong>
                                </div>
                            @enderror 
                            @if(!empty($row->video))
                            <br>
                            <p><a class="btn btn-danger btn-primary btn-sm" href="{{AdminHelper::adminpath()}}/download-file?image={{$row->video}}"><i class="fa fa-download"></i> Download </a>
                        	</p>
                            @endif                   
                        </div>            			
            		</div>
            		@endif
            		@if(in_array($row->id, [2,3]))
            		<!-- <div class="col-md-4">
            			<div class="mb-3 ">
					        <label class="form-label">{{__('Background Color')}}</label>
					        <input type="text" title="Title" class="form-control" name="bg_color" value="{{ (old('bg_color'))?old('bg_color'):$row->bg_color }}" placeholder="#000">					        
					        @error('bg_color')
		                        <div class="text-danger mt-1" role="alert">
		                            <strong>{{ $message }}</strong>
		                        </div>
		                    @enderror
					        <p class="text-muted"></p>
					    </div>            			
            		</div> -->
            		@endif
            		<div class="col-md-12">
            			<div class="mb-3 ">
					        <label class="form-label">Content(English)</label>
					        <textarea name="content_en" class="form-control" id="description_en">{!! (old('content_en'))?old('content_en'):$row->content_en !!}</textarea>				        
					        @error('content_en')
		                        <div class="text-danger mt-1" role="alert">
		                            <strong>{{ $message }}</strong>
		                        </div>
		                    @enderror
					        <p class="text-muted"></p>
					    </div>            			
            		</div>
            		<div class="col-md-12">
            			<div class="mb-3 ">
					        <label class="form-label">Content(French)</label>
					        <textarea name="content_fr" class="form-control" id="description_fr">{!! (old('content_fr'))?old('content_fr'):$row->content_fr !!}</textarea>				        
					        @error('content_fr')
		                        <div class="text-danger mt-1" role="alert">
		                            <strong>{{ $message }}</strong>
		                        </div>
		                    @enderror
					        <p class="text-muted"></p>
					    </div>            			
            		</div>
            		<div class="col-md-12">
            			<div class="mb-3 ">
					        <label class="form-label">Content(German)</label>
					        <textarea name="content_de" class="form-control" id="description_de">{!! (old('content_de'))?old('content_de'):$row->content_de !!}</textarea>				        
					        @error('content_de')
		                        <div class="text-danger mt-1" role="alert">
		                            <strong>{{ $message }}</strong>
		                        </div>
		                    @enderror
					        <p class="text-muted"></p>
					    </div>            			
            		</div>
            		<div class="col-md-12">
            			<div class="mb-3 ">
					        <label class="form-label">Content(Arabic)</label>
					        <textarea name="content_ae" class="form-control" id="description_ae">{!! (old('content_ae'))?old('content_ae'):$row->content_ae !!}</textarea>				        
					        @error('content_ae')
		                        <div class="text-danger mt-1" role="alert">
		                            <strong>{{ $message }}</strong>
		                        </div>
		                    @enderror
					        <p class="text-muted"></p>
					    </div>            			
            		</div>
            		<div class="col-md-12">
            			<div class="mb-3 ">
					        <label class="form-label">Content(Georgian)</label>
					        <textarea name="content_ka" class="form-control" id="description_ka">{!! (old('content_ka'))?old('content_ka'):$row->content_ka !!}</textarea>				        
					        @error('content_ka')
		                        <div class="text-danger mt-1" role="alert">
		                            <strong>{{ $message }}</strong>
		                        </div>
		                    @enderror
					        <p class="text-muted"></p>
					    </div>            			
            		</div>
            		<input type="hidden" name="status" value="1">
            		
            	</div>
            	<div class="row g-3">
                        <div class="form-group">
                            <label class="control-label col-sm-2"></label>
                            <div class="col-sm-10">
                            	<a href="{{ route('getStaticBlock') }}" class="btn btn-default"><i class="fa fa-chevron-circle-left"></i> {{__('Back')}}</a>
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

	  CKEDITOR.replace( 'description_en',{
	  	allowedContent : true,
	  	versionCheck: false,
	  	alignment: {
            options: [ 'left', 'right' ]
        }        
	  });
	  CKEDITOR.replace( 'description_fr',{
	  	allowedContent : true,
	  	versionCheck: false,
	  	alignment: {
            options: [ 'left', 'right' ]
        }        
	  });
	  CKEDITOR.replace( 'description_de',{
	  	allowedContent : true,
	  	versionCheck: false,
	  	alignment: {
            options: [ 'left', 'right' ]
        }        
	  });
	  CKEDITOR.replace( 'description_ae',{
	  	allowedContent : true,
	  	versionCheck: false,
	  	alignment: {
            options: [ 'left', 'right' ]
        }        
	  });
	  CKEDITOR.replace( 'description_ka',{
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