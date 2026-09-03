@extends('admin::layouts.admin_template')
@section('content')
    <p><a title="Main Module" href="{{ route('getManageUser') }}"><i class="fa fa-chevron-circle-left "></i> &nbsp; Back To
            List Data Manage Customers</a></p>

    <div class="row">
        <div class="col-md-6 offset-md-3">
            <div class="card">
                <div class="card-header card-primary align-items-center d-flex">
                    <h4 class="card-title mb-0 flex-grow-1">{{ $page_title }}</h4>
                    <div class="flex-shrink-0">
                    </div>
                </div>
                <div class="card-body">
                    <form action="{{ route('postAddUser') }}" method="post" enctype="multipart/form-data"
                        autocomplete="off">
                        @csrf
                        <input type="hidden" name="return_url" value="{{ route('getManageUser') }}">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="mb-3 ">
                                    <label for="name" class="form-label"> Name <span class="text-danger"
                                            title="This field is required">*</span></label>
                                    <input type="text" title="Name" class="form-control" name="name" id="name"
                                        value="{{ old('name') }}" placeholder="Enter Name" required>
                                    @error('name')
                                        <div class="text-danger mt-1" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </div>
                                    @enderror
                                    <p class="text-muted"></p>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="mb-3 ">
                                    <label for="email" class="form-label">Email <span class="text-danger"
                                            title="This field is required">*</span></label>
                                    <input type="email" title="Email" class="form-control" name="email" id="email"
                                        value="{{ old('email') }}" placeholder="Enter Email" required>
                                    @error('email')
                                        <div class="text-danger mt-1" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </div>
                                    @enderror
                                    <p class="text-muted"></p>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="mb-3 ">
                                    <label for="phone" class="form-label">Phone Number <span class="text-danger"
                                            title="This field is required">*</span></label>
                                    <input type="text" title="Phone Number" class="form-control onlyNumberKey"
                                        name="phone" id="phone_number" maxlength="10" value="{{ old('phone') }}"
                                        placeholder="Enter Phone Number" required>
                                    @error('phone')
                                        <div class="text-danger mt-1" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </div>
                                    @enderror
                                    <p class="text-muted"></p>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="mb-6 ">
                                    <label class="form-label">Select Role</label>
                                    <select name="role" class="form-control" required>
                                        <option value="1">Driver</option>
                                        <option value="2">Passanger</option>
                                    </select>
                                    @error('role')
                                        <div class="text-danger mt-1" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </div>
                                    @enderror
                                    <p class="text-muted"></p>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="mb-3 ">
                                    <label for="vehicle_make" class="form-label">Vehicle Make <span class="text-danger"
                                            title="This field is required">*</span></label>
                                    <input type="text" title="Vehicle Make" class="form-control" name="vehicle_make"
                                        id="vehicle_make" value="{{ old('vehicle_make') }}"
                                        placeholder="Enter Vehicle Make" required>
                                    @error('vehicle_make')
                                        <div class="text-danger mt-1" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </div>
                                    @enderror
                                    <p class="text-muted"></p>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="mb-3 ">
                                    <label for="vehicle_model" class="form-label">Vehicle Model <span class="text-danger"
                                            title="This field is required">*</span></label>
                                    <input type="text" title="vehicle model" class="form-control" name="vehicle_model"
                                        id="vehicle_model" value="{{ old('vehicle_model') }}"
                                        placeholder="Enter Vehicle Model" required>
                                    @error('vehicle_model')
                                        <div class="text-danger mt-1" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </div>
                                    @enderror
                                    <p class="text-muted"></p>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="mb-3 ">
                                    <label for="vehicle_color" class="form-label">Vehicle Color <span class="text-danger"
                                            title="This field is required">*</span></label>
                                    <input type="text" title="vehicle_color" class="form-control"
                                        name="vehicle_color" id="vehicle_color" value="{{ old('vehicle_color') }}"
                                        placeholder="Enter Vehicle Color" required>
                                    @error('vehicle_color')
                                        <div class="text-danger mt-1" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </div>
                                    @enderror
                                    <p class="text-muted"></p>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="mb-3 ">
                                    <label for="registration_number" class="form-label">Registration Number <span
                                            class="text-danger" title="This field is required">*</span></label>
                                    <input type="text" title="registration number" class="form-control"
                                        name="registration_number" id="registration_number"
                                        value="{{ old('registration_number') }}" placeholder="Enter Registration Number"
                                        required>
                                    @error('registration_number')
                                        <div class="text-danger mt-1" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </div>
                                    @enderror
                                    <p class="text-muted"></p>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="mb-3 ">
                                    <label for="fuel_cost_per_km" class="form-label">Fuel Cost 1/KM <span
                                            class="text-danger" title="This field is required">*</span></label>
                                    <input type="text" title="Fuel Cost 1/KM" class="form-control"
                                        name="fuel_cost_per_km" id="fuel_cost_per_km"
                                        value="{{ old('fuel_cost_per_km') }}" placeholder="Enter Fuel Cost 1/KM"
                                        required>
                                    @error('fuel_cost_per_km')
                                        <div class="text-danger mt-1" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </div>
                                    @enderror
                                    <p class="text-muted"></p>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="mb-3 ">
                                    <label for="is_online" class="form-label">is_online <span class="text-danger"
                                            title="This field is required">*</span></label>
                                    <input type="text" title="is_online" class="form-control" name="is_online"
                                        id="is_online" value="{{ old('is_online') }}" placeholder="Enter is_online"
                                        required>
                                    @error('is_online')
                                        <div class="text-danger mt-1" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </div>
                                    @enderror
                                    <p class="text-muted"></p>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="mb-3 ">
                                    <label for="documents" class="form-label">Documents <span class="text-danger"
                                            title="This field is required">*</span></label>
                                    <input type="text" title="documents" class="form-control" name="documents"
                                        id="documents" value="{{ old('documents') }}" placeholder="Enter Documents"
                                        required>
                                    @error('documents')
                                        <div class="text-danger mt-1" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </div>
                                    @enderror
                                    <p class="text-muted"></p>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="mb-3 ">
                                    <label for="travel_preferences" class="form-label">travel_preferences <span
                                            class="text-danger" title="This field is required">*</span></label>
                                    <input type="text" title="travel_preferences" class="form-control"
                                        name="travel_preferences" id="travel_preferences"
                                        value="{{ old('travel_preferences') }}" placeholder="Enter travel_preferences"
                                        required>
                                    @error('travel_preferences')
                                        <div class="text-danger mt-1" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </div>
                                    @enderror
                                    <p class="text-muted"></p>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="mb-3 ">
                                    <label for="password" class="form-label"> Password <span class="text-danger"
                                            title="This field is required"></span></label>
                                    <input type="password" title="Password" class="form-control" name="password"
                                        id="password" maxlength="13" value="" placeholder="Enter password">
                                    @error('password')
                                        <div class="text-danger mt-1" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </div>
                                    @enderror
                                    <p class="text-muted"></p>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="mb-6 ">
                                    <label class="form-label">Status</label>
                                    <select name="is_verified" class="form-control" required>
                                        <option value="1">Active</option>
                                        <option value="0">Inactive</option>
                                    </select>
                                    @error('is_verified')
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
                                    <a href="{{ route('getManageUser') }}" class="btn btn-default"><i
                                            class="fa fa-chevron-circle-left"></i> Back</a>
                                    <input type="submit" name="submit" value="Save & Add More"
                                        class="btn btn-primary">
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
        var country_id = '';
        $(document).ready(function() {
            $("select[name='country_id']").on('change', function() {
                country_id = $(this).val();
                if (country_id > 0) {
                    var options = '<option value="">Loading...</option>';
                    $("select[name='city_id']").html(options);
                    $.get("{{ url('ajax/get-cities/') }}/" + country_id, function(resp) {
                        options = '<option value="">Select City</option>';
                        if (resp.length > 0) {
                            $.each(resp, function(index, item) {
                                options +=
                                    `<option value="${item.id}">${item.city_name}</option>`;
                            });
                        }

                        $("select[name='city_id']").html(options);
                    })
                } else {
                    $("select[name='city_id']").html('<option value="">Select City</option>');
                }
            })

            $("select[name='city_id']").on('change', function() {
                var city_id = $(this).val();
                if (city_id > 0) {
                    var options = '<option value="">Loading...</option>';
                    $("select[name='company_id']").html(options);
                    $.get("{{ url('ajax/get-companies/') }}/" + country_id + '/' + city_id, function(
                        resp) {
                        options = '<option value="">Select Company</option>';
                        if (resp.length > 0) {
                            $.each(resp, function(index, item) {
                                options +=
                                    `<option value="${item.id}">${item.company_name}</option>`;
                            });
                        }

                        $("select[name='company_id']").html(options);
                    })
                } else {
                    $("select[name='company_id']").html('<option value="">Select Company</option>');
                }
            })
        })
    </script>
@endpush
