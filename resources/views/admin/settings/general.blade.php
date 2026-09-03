@extends('admin::layouts.admin_template')
@section('content')
    <div class="row">
        <div class="col-md-6 offset-md-3">
            <div class="card">
                <div class="card-header card-primary">
                    <i class="fa fa-cog"></i> {{ $page_title }}
                </div>
                <div class="card-body">
                    <form method="post" id="form" enctype="multipart/form-data"
                        action="{{ route('postSaveGeneralSettings') }}">
                        @csrf
                        <div class="box-body">
                            <div class="form-group mb-3">
                                <label class="label-setting">Site Title<span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="site_title"
                                    value="{{ old('site_title') ? old('site_title') : (!empty($row) ? $row->appname : '') }}"
                                    required>
                                @error('site_title')
                                    <div class="text-danger mt-1" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </div>
                                @enderror
                            </div>
                            <div class="form-group mb-3">
                                <label class="label-setting">Logo</label>
                                <input type="file" name="logo" accept="image/*" class="form-control">
                                <div class="text-muted">File support only jpg,png,gif, Max 2 MB</div>
                                @error('logo')
                                    <div class="text-danger mt-1" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </div>
                                @enderror
                                @if (!empty($row->logo) && (Storage::exists($row->logo) || file_exists(public_path($row->logo))))
                                    <div class="prev-img-thumb"><img src="{{ asset($row->logo) }}"></div>
                                    <!-- <p class="text-muted"><em>* If you want to upload other image, please first delete the image.</em></p> -->
                                    <p><a class="btn btn-danger btn-primary btn-sm"
                                            href="{{ AdminHelper::adminpath() }}/download-file?image={{ $row->logo }}"><i
                                                class="fa fa-download"></i> Download </a>
                                        <!-- <a class="btn btn-danger btn-delete btn-sm" onclick="if(!confirm('Are you sure ?')) return false" href="{{ AdminHelper::adminpath() }}/delete-image?image={{ $row->logo }}&&id={{ $row->id }}&&column=logo&table=admin_settings"><i class="fa fa-ban"></i> Delete </a> -->
                                    </p>
                                @endif
                            </div>
                            <div class="form-group mb-3">
                                <label class="label-setting">Favicon</label>
                                <input type="file" name="favicon" accept="image/*" class="form-control">
                                <div class="text-muted">File support only jpg,png,gif, Max 2 MB</div>
                                @error('favicon')
                                    <div class="text-danger mt-1" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </div>
                                @enderror
                                @if (!empty($row->favicon) && (Storage::exists($row->favicon) || file_exists(public_path($row->favicon))))
                                    <div class="prev-img-thumb" style="width:30px;height: auto;"><img style="width: 200px;"
                                            src="{{ asset($row->favicon) }}"></div>
                                    <p><a class="btn btn-danger btn-primary btn-sm"
                                            href="{{ AdminHelper::adminpath() }}/download-file?image={{ $row->favicon }}"><i
                                                class="fa fa-download"></i> Download </a>
                                    </p>
                                @endif
                            </div>
                            <div class="form-group mb-3">
                                <label class="label-setting">Footer Logo</label>
                                <input type="file" name="footer_logo" accept="image/*" class="form-control">
                                <div class="text-muted">File support only jpg,png,gif, Max 2 MB</div>
                                @error('footer_logo')
                                    <div class="text-danger mt-1" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </div>
                                @enderror
                                @if (!empty($row->footer_logo) && (Storage::exists($row->footer_logo) || file_exists(public_path($row->footer_logo))))
                                    <div class="prev-img-thumb"><img src="{{ asset($row->footer_logo) }}"></div>

                                    <p><a class="btn btn-danger btn-primary btn-sm"
                                            href="{{ AdminHelper::adminpath() }}/download-file?image={{ $row->footer_logo }}"><i
                                                class="fa fa-download"></i> Download </a>

                                    </p>
                                @endif
                            </div>
                            <div class="form-group mb-3">
                                <label class="label-setting">Email</label>
                                <input type="email" class="form-control" name="site_email"
                                    value="{{ old('site_email') ? old('site_email') : (!empty($row) ? $row->site_email : '') }}">
                                <div class="text-muted"></div>
                                @error('site_email')
                                    <div class="text-danger mt-1" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </div>
                                @enderror
                            </div>
                            <div class="form-group mb-3">
                                <label class="label-setting">Contact Number</label>
                                <input type="text" class="form-control" name="site_phone_number"
                                    value="{{ old('site_phone_number') ? old('site_phone_number') : (!empty($row) ? $row->site_phone_number : '') }}">
                                <div class="text-muted"></div>
                                @error('site_phone_number')
                                    <div class="text-danger mt-1" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </div>
                                @enderror
                            </div>

                            <div class="form-group mb-3">
                                <label class="label-setting">Address</label>
                                <textarea class="form-control" name="site_address" rows="3">{{ old('site_address') ? old('site_address') : (!empty($row) ? $row->site_address : '') }}</textarea>
                                <div class="text-muted"></div>
                                @error('site_address')
                                    <div class="text-danger mt-1" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </div>
                                @enderror
                            </div>
                            <div class="form-group mb-3">
                                <label class="label-setting">Ride Booking fee(In %)</label>
                                <input type="number" name="booking_fee" value="{{ $row->booking_fee }}" min="0"
                                    placeholder="50" class="form-control">
                                @error('booking_fee')
                                    <div class="text-danger mt-1" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </div>
                                @enderror
                            </div>
                            <div class="form-group mb-3">
                                <label class="label-setting">Trip Booking fee(In %)</label>
                                <input type="number" name="trip_booking_fee" value="{{ $row->trip_booking_fee }}"
                                    min="0" placeholder="50" class="form-control">
                                @error('trip_booking_fee')
                                    <div class="text-danger mt-1" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </div>
                                @enderror
                            </div>
                            <div class="form-group mb-3">
                                <label class="label-setting">Driver Payout Fee(In %)</label>
                                <input type="number" name="driver_payout_fee" value="{{ $row->driver_payout_fee }}"
                                    min="0" placeholder="10" class="form-control">
                                @error('driver_payout_fee')
                                    <div class="text-danger mt-1" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </div>
                                @enderror
                            </div>
                            <div class="form-group mb-3">
                                <label class="label-setting">Facebook</label>
                                <input type="text" class="form-control" name="facebook_link"
                                    value="{{ old('facebook_link') ? old('facebook_link') : (!empty($row) ? $row->facebook_link : '') }}">
                                <div class="text-muted"></div>
                                @error('facebook_link')
                                    <div class="text-danger mt-1" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </div>
                                @enderror
                            </div>
                            <div class="form-group mb-3">
                                <label class="label-setting">Instagram</label>
                                <input type="text" class="form-control" name="instagram_link"
                                    value="{{ old('instagram_link') ? old('instagram_link') : (!empty($row) ? $row->instagram_link : '') }}">
                                <div class="text-muted"></div>
                                @error('instagram_link')
                                    <div class="text-danger mt-1" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </div>
                                @enderror
                            </div>
                            <div class="form-group mb-3">
                                <label class="label-setting">Twitter</label>
                                <input type="text" class="form-control" name="twitter_link"
                                    value="{{ old('twitter_link') ? old('twitter_link') : (!empty($row) ? $row->twitter_link : '') }}">
                                <div class="text-muted"></div>
                                @error('twitter_link')
                                    <div class="text-danger mt-1" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </div>
                                @enderror
                            </div>
                            <!-- <div class="form-group mb-3">
                                                                                                                <label class="label-setting">Linkedin</label>
                                                                                                                <input type="text" class="form-control" name="linkedin_link" value="{{ old('linkedin_link') ? old('linkedin_link') : (!empty($row) ? $row->linkedin_link : '') }}">
                                                                                                                <div class="text-muted"></div>
                                                                                                                @error('linkedin_link')
        <div class="text-danger mt-1" role="alert">
                                                                                                                                                                                                            <strong>{{ $message }}</strong>
                                                                                                                                                                                                        </div>
    @enderror
                                                                                                            </div>                       -->
                            <div class="form-group mb-3">
                                <label class="label-setting">Youtube</label>
                                <input type="text" class="form-control" name="youtube_link"
                                    value="{{ old('youtube_link') ? old('youtube_link') : (!empty($row) ? $row->youtube_link : '') }}">
                                <div class="text-muted"></div>
                                @error('youtube_link')
                                    <div class="text-danger mt-1" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </div>
                                @enderror
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
@push('bottom')
    <script type="text/javascript" src="https://cdn.ckeditor.com/4.19.1/standard/ckeditor.js"></script>
    <script type="text/javascript">
        $(document).ready(function() {
            CKEDITOR.replace('opening_hours', {
                allowedContent: true,
                versionCheck: false
            });
        })
    </script>
@endpush
