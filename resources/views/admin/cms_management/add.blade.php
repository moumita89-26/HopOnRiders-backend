@extends('admin::layouts.admin_template')
@section('content')
    <!-- <script type="text/javascript" src="https://cdn.ckeditor.com/ckeditor5/17.0.0/classic/ckeditor.js"></script> -->
    <script type="text/javascript" src="https://cdn.ckeditor.com/4.19.1/standard/ckeditor.js"></script>

    <p><a title="Main Module" href="{{ AdminHelper::adminpath() }}/manage-cms"><i class="fa fa-chevron-circle-left "></i>
            &nbsp; Back To List Data Manage CMS</a></p>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header card-primary align-items-center d-flex">
                    <h4 class="card-title mb-0 flex-grow-1">{{ $page_title }}</h4>
                    <div class="flex-shrink-0">
                    </div>
                </div>

                <div class="card-body">
                    <form action="{{ route('postAddCms') }}" method="post" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="return_url" value="{{ route('getManageCMS') }}">
                        <div class="row">




                            <div class="col-md-6">
                                <div class="mb-3 ">
                                    <label class="form-label">Page Title<span class="text-danger"
                                            title="This field is required">*</span></label>
                                    <input type="text" title="Page Title" class="form-control" name="page_title"
                                        value="{{ old('page_title') }}" placeholder="Name" required maxlength="50">
                                    @error('page_title')
                                        <div class="text-danger mt-1" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </div>
                                    @enderror
                                    <p class="text-muted"></p>
                                </div>
                            </div>




                            <div class="col-md-6">
                                <div class="mb-3 ">
                                    <label class="form-label">Featured Image</label>
                                    <input type="file" class="form-control" name="image" accept="image/*">
                                    @error('image')
                                        <div class="text-danger mt-1" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </div>
                                    @enderror
                                    <p class="text-muted">The image should be JPG/JPEG/PNG/GIF/SVG type and the image size
                                        should not above 5MB.</p>
                                </div>
                            </div>




                            <div class="col-md-12">
                                <div class="mb-3 ">
                                    <label class="form-label">Page Content</label>
                                    <textarea name="content" class="form-control" id="description">{{ old('content') }}</textarea>
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
                                    <label class="form-label">Meta Title <span class="text-danger"
                                            title="This field is required">*</span></label>
                                    <input type="text" title="Meta Title" class="form-control" name="meta_title"
                                        value="{{ old('meta_title') }}" placeholder="Meta Title" required maxlength="60">
                                    @error('meta_title')
                                        <div class="text-danger mt-1" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </div>
                                    @enderror
                                    <p class="text-muted"></p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3 ">
                                    <label class="form-label">Meta Keywords</label>
                                    <input type="text" title="Meta Keywords" class="form-control" name="meta_keywords"
                                        value="{{ old('meta_keywords') }}" maxlength="60">
                                    @error('meta_keywords')
                                        <div class="text-danger mt-1" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </div>
                                    @enderror
                                    <p class="text-muted"></p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3 ">
                                    <label class="form-label">Meta Description</label>
                                    <textarea name="meta_description" class="form-control" rows="3">{{ old('meta_description') }}</textarea maxlength="200">
         @error('meta_description')
        <div class="text-danger mt-1" role="alert">
          <strong>{{ $message }}</strong>
          </div>
    @enderror
         <p class="text-muted"></p>
         </div>
                </div>
                <div class="col-md-6">
                <div class="mb-3 ">
         <label class="form-label">Status</label>
         <select name="status" class="form-control" required>
         <option value="1" {{ old('status') == '1' ? 'selected' : '' }}>Active</option>
         <option value="0" {{ old('status') == '0' ? 'selected' : '' }}>Inactive</option>
         </select>
         @error('status')
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


            CKEDITOR.replace('description', {
                allowedContent: true,
                versionCheck: false
            });


        });
    </script>
@endpush
