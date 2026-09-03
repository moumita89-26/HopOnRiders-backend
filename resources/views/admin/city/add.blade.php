@extends('admin::layouts.admin_template')
@section('content')
<p><a title="Main Module" href="{{ route('getManageCity') }}"><i class="fa fa-chevron-circle-left "></i> &nbsp; Back To List Data Manage City</a></p>

<div class="row">
	<div class="col-md-6 offset-md-3">
		<div class="card">
			<div class="card-header card-primary align-items-center d-flex">
                <h4 class="card-title mb-0 flex-grow-1">{{ $page_title }}</h4>
                <div class="flex-shrink-0">
                </div>
            </div> 

            <div class="card-body">
            <form action="{{ route('postAddCity') }}" method="post" enctype="multipart/form-data"> 
            	@csrf
            	<input type="hidden" name="return_url" value="{{ route('getManageCity') }}">
            	<div class="row">

                    <div class="col-md-12">
                        <div class="mb-3 ">
                            <label for="title" class="form-label">City Name(English) <span class="text-danger" title="This field is required">*</span></label>
                            <input type="text" title="Title" class="form-control" name="city_name_en" id="city_name_en" value="{{ old('city_name_en') }}" required>                          
                            @error('city_name_en')
                                <div class="text-danger mt-1" role="alert">
                                    <strong>{{ $message }}</strong>
                                </div>
                            @enderror
                            <p class="text-muted"></p>
                        </div>                      
                    </div>
                    <div class="col-md-12">
                        <div class="mb-3 ">
                            <label for="title" class="form-label">City Name(French) <span class="text-danger" title="This field is required">*</span></label>
                            <input type="text" title="Title" class="form-control" name="city_name_fr" id="city_name_fr" value="{{ old('city_name_fr') }}" required>                          
                            @error('city_name_fr')
                                <div class="text-danger mt-1" role="alert">
                                    <strong>{{ $message }}</strong>
                                </div>
                            @enderror
                            <p class="text-muted"></p>
                        </div>                      
                    </div>
                    <div class="col-md-12">
                        <div class="mb-3 ">
                            <label for="title" class="form-label">City Name(German) <span class="text-danger" title="This field is required">*</span></label>
                            <input type="text" title="Title" class="form-control" name="city_name_de" id="city_name_de" value="{{ old('city_name_de') }}" required>                          
                            @error('city_name_de')
                                <div class="text-danger mt-1" role="alert">
                                    <strong>{{ $message }}</strong>
                                </div>
                            @enderror
                            <p class="text-muted"></p>
                        </div>                      
                    </div>
                    <div class="col-md-12">
                        <div class="mb-3 ">
                            <label for="title" class="form-label">City Name(Arabic) <span class="text-danger" title="This field is required">*</span></label>
                            <input type="text" title="Title" class="form-control" name="city_name_ae" id="city_name_ae" value="{{ old('city_name_ae') }}" required>                          
                            @error('city_name_ae')
                                <div class="text-danger mt-1" role="alert">
                                    <strong>{{ $message }}</strong>
                                </div>
                            @enderror
                            <p class="text-muted"></p>
                        </div>                      
                    </div>
                    <div class="col-md-12">
                        <div class="mb-3 ">
                            <label for="title" class="form-label">City Name(Georgian) <span class="text-danger" title="This field is required">*</span></label>
                            <input type="text" title="Title" class="form-control" name="city_name_ka" id="city_name_ka" value="{{ old('city_name_ka') }}" required>                          
                            @error('city_name_ka')
                                <div class="text-danger mt-1" role="alert">
                                    <strong>{{ $message }}</strong>
                                </div>
                            @enderror
                            <p class="text-muted"></p>
                        </div>                      
                    </div>
                    <div class="col-md-12">
                        <div class="mb-3 ">
                            <label for="title" class="form-label">Country <span class="text-danger" title="This field is required">*</span></label>
                            <select name="country_id" class="form-control" required>
                                <option value="">Select Country</option>
                                @if(!empty($countries))
                                @foreach($countries as $country)
                                <option value="{{ $country->id }}">{{ $country->country_name }}</option>
                                @endforeach
                                @endif
                            </select>                          
                            @error('country_id')
                                <div class="text-danger mt-1" role="alert">
                                    <strong>{{ $message }}</strong>
                                </div>
                            @enderror
                            <p class="text-muted"></p>
                        </div>                      
                    </div>

                    <div class="col-md-12">
                        <div class="mb-3 ">
                            <label for="Nameinput" class="form-label">Status<span class="text-danger" title="This field is required">*</span></label>
                            <select class="form-control" name="status" required>
                                <option value="">Select Status</option>
                                <option value="1" {{ (old('status') && old('status')==1)?'selected':'' }}>Active</option>
                                <option value="0" {{ (old('status') && old('status')==0)?'selected':'' }}>Inactive</option>
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
                            	<a href="{{ route('getManageCity') }}" class="btn btn-default"><i class="fa fa-chevron-circle-left"></i> Back</a>
                                <input type="submit" name="submit" value="Save & Add More" class="btn btn-primary">
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