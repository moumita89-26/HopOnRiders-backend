@extends('admin::layouts.admin_template')
@section('content')
    <p><a title="Main Module" href="{{ route('getManageUser') }}"><i class="fa fa-chevron-circle-left "></i> &nbsp; Back To
            List Data Manage Customer</a></p>

    <div class="row">
        <div class="col-md-6 offset-md-3">
            <div class="card">
                <div class="card-header card-primary align-items-center d-flex">
                    <h4 class="card-title mb-0 flex-grow-1">{{ $page_title }}</h4>
                    <div class="flex-shrink-0">
                    </div>
                </div>

                <div class="card-body">
                    <form action="{{ route('postUpdateRide', $row->id) }}" method="post" enctype="multipart/form-data"
                        autocomplete="off">
                        @csrf
                        <input type="hidden" name="return_url" value="{{ route('getManageUser') }}">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="mb-3 ">
                                    <label for="name" class="form-label"> Driver Name <span class="text-danger"
                                            title="This field is required">*</span></label>
                                    <input type="text" title="Name" class="form-control" name="driver_id"
                                        id="name" value="{{ $row->driverDetails->name }}"
                                        placeholder="Enter Driver Name" disabled>
                                    @error('driver_id')
                                        <div class="text-danger mt-1" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </div>
                                    @enderror
                                    <p class="text-muted"></p>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="mb-3 ">
                                    <label for="name" class="form-label"> Origin <span class="text-danger"
                                            title="This field is required">*</span></label>
                                    <input type="text" title="origin" class="form-control" name="origin" id="origin"
                                        value="{{ old('origin', $row->origin) }}" placeholder="Enter Origin" required>
                                    @error('origin')
                                        <div class="text-danger mt-1" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </div>
                                    @enderror
                                    <p class="text-muted"></p>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="mb-3 ">
                                    <label for="name" class="form-label"> Destination <span class="text-danger"
                                            title="This field is required">*</span></label>
                                    <input type="text" title="destination" class="form-control" name="destination"
                                        id="origin" value="{{ old('destination', $row->destination) }}"
                                        placeholder="Enter Destination" required>
                                    @error('destination')
                                        <div class="text-danger mt-1" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </div>
                                    @enderror
                                    <p class="text-muted"></p>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="mb-3 ">
                                    <label for="phone" class="form-label">Available Seats <span class="text-danger"
                                            title="This field is required">*</span></label>
                                    <input type="text" title="available_seats" class="form-control onlyNumberKey"
                                        name="available_seats" id="available_seats" maxlength="10"
                                        value="{{ old('available_seats', $row->available_seats) }}"
                                        placeholder="Enter Available Seats" required>
                                    @error('available_seats')
                                        <div class="text-danger mt-1" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </div>
                                    @enderror
                                    <p class="text-muted"></p>
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="mb-3 ">
                                    <label for="phone" class="form-label">Departure Time <span class="text-danger"
                                            title="This field is required">*</span></label>
                                    <input type="datetime-local" title="departure_time" class="form-control onlyNumberKey"
                                        name="departure_time" id="departure_time" maxlength="10"
                                        value="{{ old('departure_time', $row->departure_time) }}" required>
                                    @error('departure_time')
                                        <div class="text-danger mt-1" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </div>
                                    @enderror
                                    <p class="text-muted"></p>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="mb-3 ">
                                    <label for="phone" class="form-label">Fare Per Seat <span class="text-danger"
                                            title="This field is required">*</span></label>
                                    <input type="text" title="fare_per_seat" class="form-control onlyNumberKey"
                                        name="fare_per_seat" id="fare_per_seat" maxlength="10"
                                        value="{{ old('fare_per_seat', $row->fare_per_seat) }}" placeholder="00.00"
                                        required>
                                    @error('fare_per_seat')
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
                                    <select name="status" class="form-control" required>
                                        <option {{ old('status', $row->status) == 1 ? 'selected' : '' }} value="1">
                                            Pending</option>
                                        <option {{ old('status', $row->status) == 2 ? 'selected' : '' }} value="2">
                                            Confirmed</option>
                                        <option {{ old('status', $row->status) == 3 ? 'selected' : '' }} value="3">
                                            Completed</option>
                                        <option {{ old('status', $row->status) == 4 ? 'selected' : '' }} value="4">
                                            Cancel</option>
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
                                    <a href="{{ route('getManageUser') }}" class="btn btn-default"><i
                                            class="fa fa-chevron-circle-left"></i> Back</a>
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
        var city = $("input[name='selected_city']").val();
        var company = $("input[name='selected_company']").val();
        $(document).ready(function() {
            country_id = $("select[name='country_id'] :selected").val();
            getCities(country_id);

            $("select[name='country_id']").on('change', function() {
                country_id = $(this).val();
                getCities(country_id);
            })

            $("select[name='city_id']").on('change', function() {
                city_id = $(this).val();
                getCompanies(country_id, city_id);
            })
        })

        function getCities(country_id) {
            if (country_id > 0) {
                var options = '<option value="">Loading...</option>';
                $("select[name='city_id']").html(options);
                $.get("{{ url('ajax/get-cities/') }}/" + country_id, function(resp) {
                    options = '<option value="">Select City</option>';
                    if (resp.length > 0) {
                        $.each(resp, function(index, item) {
                            options += `<option value="${item.id}">${item.city_name}</option>`;
                        });
                    }

                    $("select[name='city_id']").html(options);
                    if (city > 0) {
                        $("select[name='city_id']").val(city).change();
                    }
                })
            } else {
                $("select[name='city_id']").html('<option value="">Select City</option>');
            }
        }

        function getCompanies(country_id, city_id) {
            if (city_id > 0) {
                var options = '<option value="">Loading...</option>';
                $("select[name='company_id']").html(options);
                $.get("{{ url('ajax/get-companies/') }}/" + country_id + '/' + city_id, function(resp) {
                    options = '<option value="">Select Company</option>';
                    if (resp.length > 0) {
                        $.each(resp, function(index, item) {
                            options += `<option value="${item.id}">${item.company_name}</option>`;
                        });
                    }

                    $("select[name='company_id']").html(options);
                    if (company > 0) {
                        $("select[name='company_id']").val(company).change();
                    }
                })
            } else {
                $("select[name='company_id']").html('<option value="">Select Company</option>');
            }

        }
    </script>
@endpush
