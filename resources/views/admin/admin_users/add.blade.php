@extends('admin::layouts.admin_template')
@section('content')
    <p><a title="Main Module" href="{{ route('getAdminUsers') }}"><i class="fa fa-chevron-circle-left "></i> &nbsp; Back To
            List Data</a></p>

    <div class="row">
        <div class="col-md-6 offset-md-3">
            <div class="card">
                <div class="card-header card-primary align-items-center d-flex">
                    <h4 class="card-title mb-0 flex-grow-1">{{ $page_title }}</h4>
                    <div class="flex-shrink-0">
                    </div>
                </div>

                <div class="card-body">
                    <form action="{{ route('postAddSaveAdminUser') }}" method="post" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="return_url" value="{{ route('getAdminUsers') }}">
                        <div class="mb-3 ">
                            <label for="name" class="form-label">Name <span class="text-danger"
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
                        <div class="mb-3 ">
                            <label for="password" class="form-label">Password <span class="text-danger"
                                    title="This field is required">*</span></label>
                            <input type="password" title="Password" class="form-control" name="password" id="password"
                                value="{{ old('email') }}" placeholder="Enter Password" required>
                            @error('password')
                                <div class="text-danger mt-1" role="alert">
                                    <strong>{{ $message }}</strong>
                                </div>
                            @enderror
                            <p class="text-muted"></p>
                        </div>
                        <div class="mb-3 ">
                            <label for="privilege" class="form-label">Privilege <span class="text-danger"
                                    title="This field is required">*</span></label>
                            <select name="privilege" id="privilege" class="form-control" required>
                                <option value="">Select a Privilege</option>
                                @if (!empty($privileges))
                                    @foreach ($privileges as $privilege)
                                        <option value="{{ $privilege->id }}">{{ $privilege->name }}</option>
                                    @endforeach
                                @endif
                            </select>
                            @error('privilege')
                                <div class="text-danger mt-1" role="alert">
                                    <strong>{{ $message }}</strong>
                                </div>
                            @enderror
                            <p class="text-muted"></p>
                        </div>
                        <div class="mb-3 ">
                            <label for="status" class="form-label">Status <span class="text-danger"
                                    title="This field is required">*</span></label>
                            <select name="status" class="form-control" required>
                                <option value="">Select a Status</option>
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                            @error('status')
                                <div class="text-danger mt-1" role="alert">
                                    <strong>{{ $message }}</strong>
                                </div>
                            @enderror
                            <p class="text-muted"></p>
                        </div>
                        <div class="row g-3">
                            <div class="form-group">
                                <label class="control-label col-sm-2"></label>
                                <div class="col-sm-10">
                                    <a href="{{ route('getAdminUsers') }}" class="btn btn-default"><i
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
