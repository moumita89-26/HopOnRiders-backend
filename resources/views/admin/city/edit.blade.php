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
            	<form action="{{ route('postUpdateCity', $row->id) }}" method="post" enctype="multipart/form-data">
            	@csrf
            	<input type="hidden" name="return_url" value="{{ route('getManageCity') }}">
            	<div class="row">

                    <div class="col-md-12">
                        <div class="mb-3 ">
                            <label for="title" class="form-label">City Name(English) <span class="text-danger" title="This field is required">*</span></label>
                            <input type="text" title="Title" class="form-control" name="city_name_en" id="city_name_en" value="{{ (!empty(old('city_name_en'))?old('city_name_en'):$row->city_name_en) }}" placeholder="Enter City Name" required>                          
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
                            <input type="text" title="Title" class="form-control" name="city_name_fr" id="city_name_fr" value="{{ (!empty(old('city_name_fr'))?old('city_name_fr'):$row->city_name_fr) }}" placeholder="Enter City Name" required>                          
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
                            <input type="text" title="Title" class="form-control" name="city_name_de" id="city_name_de" value="{{ (!empty(old('city_name_de'))?old('city_name_de'):$row->city_name_de) }}" placeholder="Enter City Name" required>                          
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
                            <input type="text" title="Title" class="form-control" name="city_name_ae" id="city_name_ae" value="{{ (!empty(old('city_name_ae'))?old('city_name_ae'):$row->city_name_ae) }}" placeholder="Enter City Name" required>                          
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
                            <input type="text" title="Title" class="form-control" name="city_name_ka" id="city_name_ka" value="{{ (!empty(old('city_name_ka'))?old('city_name_ka'):$row->city_name_ka) }}" placeholder="Enter City Name" required>                          
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
                                <option value="{{ $country->id }}" {{ ($row->country_id==$country->id)?'selected':'' }}>{{ $country->country_name }}</option>
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
					        	<option value="1" @if($row->city_status==1) selected @endif >Active</option>
					        	<option value="0" @if($row->city_status==0) selected @endif >Inactive</option>
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
     


     $("#city_id").change(function(){
    var city_val = $("#city_id").val();
    var url_val = '{{ url('/')}}'+'/admin/states-by-city';
    var myKeyVals = $(this).val();
    var getData = $.ajax({
        type: 'POST',
        url: url_val,
        data: { city_id:city_val,"_token": "{{ csrf_token() }}"},
        dataType: "html",
        success: function(resultData) { $('#state_id').html(resultData); }
  });
    //getData.error(function() { alert("Something went wrong"); });
  });

  $("#state_id").change(function(){
    var state_val = $("#state_id").val();
    var url_val = '{{ url('/')}}'+'/admin/city-by-states';
    var myKeyVals = $(this).val();
    var getData = $.ajax({
        type: 'POST',
        url: url_val,
        data: { state_id:state_val,"_token": "{{ csrf_token() }}"},
        dataType: "html",
        success: function(resultData) { $('#city_id').html(resultData); }
  });
    //getData.error(function() { alert("Something went wrong"); });
  });


    });
</script>
@endpush