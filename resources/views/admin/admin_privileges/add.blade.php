@extends('admin::layouts.admin_template')
@section('content')
<p><a title="Main Module" href="{{ route('getPrivilege') }}"><i class="fa fa-chevron-circle-left "></i> &nbsp; Back To List Data</a></p>

<div class="row">
	<div class="col-md-6 offset-md-3">
		<div class="card">
			<div class="card-header card-primary align-items-center d-flex">
                <h4 class="card-title mb-0 flex-grow-1">{{ $page_title }}</h4>
                <div class="flex-shrink-0">
                </div>
            </div> 

            <div class="card-body">
            <form action="{{ route('postAddPrivilege') }}" method="post" enctype="multipart/form-data"> 
        	@csrf
        	<input type="hidden" name="return_url" value="{{ route('getPrivilege') }}">
        	<div class="mb-3 ">
                <label for="name" class="form-label">Privilege Name <span class="text-danger" title="This field is required">*</span></label>
                <input type="text" title="name" class="form-control" name="name" id="name" value="{{ old('name') }}" placeholder="Enter Name" required>                          
                @error('name')
                    <div class="text-danger mt-1" role="alert">
                        <strong>{{ $message }}</strong>
                    </div>
                @enderror
                <p class="text-muted"></p>
            </div>
            <div id="privileges_configuration" class="mb-3">
                <label class="form-label">Privileges Configuration</label>
                <table class="table table-striped table-hover table-bordered">
                    <thead>
                        <tr class="active">                            
                            <th width="60%">Module's Name</th>
                            <th>All</th>
                            <th>View</th>
                            <th>Create</th>
                            <th>Read</th>
                            <th>Update</th>
                            <th>Delete</th>
                        </tr>
                        <tr class="active">                            
                            <th>&nbsp;</th>
                            <th>&nbsp;</th>
                            <td align="center"><input title="Check all vertical" type="checkbox" id="is_visible"></td>
                            <td align="center"><input title="Check all vertical" type="checkbox" id="is_create"></td>
                            <td align="center"><input title="Check all vertical" type="checkbox" id="is_read"></td>
                            <td align="center"><input title="Check all vertical" type="checkbox" id="is_edit"></td>
                            <td align="center"><input title="Check all vertical" type="checkbox" id="is_delete"></td>
                        </tr>
                    </thead>
                    <tbody>
                        @if(!empty($menus) && count($menus))
                        @foreach($menus as $menu)
                        <tr>                            
                            <td>{{ $menu->name }}</td>
                            <td class="active" align="center">
                                <input type="checkbox" title="Check All Horizontal" class="select_horizontal">
                            </td>
                            <td align="center">
                                <input type="checkbox" class="is_visible" name="privileges[{{$menu->id}}][is_visible]" value="1">
                            </td>
                            <td class="warning" align="center">
                                <input type="checkbox" class="is_create" name="privileges[{{$menu->id}}][is_create]" value="1">
                            </td>
                            <td class="info" align="center">
                                <input type="checkbox" class="is_read" name="privileges[{{$menu->id}}][is_read]" value="1">
                            </td>
                            <td class="success" align="center">
                                <input type="checkbox" class="is_edit" name="privileges[{{$menu->id}}][is_edit]" value="1">
                            </td>
                            <td class="danger" align="center">
                                <input type="checkbox" class="is_delete" name="privileges[{{$menu->id}}][is_delete]" value="1">
                            </td>
                        </tr>
                        @endforeach
                        @else
                        <tr>
                            <td colspan="7" class="text-center">No records found!</td>
                        </tr>
                        @endif
                    </tbody>
                </table>

            </div>
        	<div class="row g-3">
                    <div class="form-group">
                        <label class="control-label col-sm-2"></label>
                        <div class="col-sm-10">
                        	<a href="{{ route('getPrivilege') }}" class="btn btn-default"><i class="fa fa-chevron-circle-left"></i> Back</a>
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
<script>
    $(function () {
        $("#is_visible").click(function () {
            var is_ch = $(this).prop('checked');
            console.log('is checked create ' + is_ch);
            $(".is_visible").prop("checked", is_ch);
            console.log('Create all');
        })
        $("#is_create").click(function () {
            var is_ch = $(this).prop('checked');
            console.log('is checked create ' + is_ch);
            $(".is_create").prop("checked", is_ch);
            console.log('Create all');
        })
        $("#is_read").click(function () {
            var is_ch = $(this).is(':checked');
            $(".is_read").prop("checked", is_ch);
        })
        $("#is_edit").click(function () {
            var is_ch = $(this).is(':checked');
            $(".is_edit").prop("checked", is_ch);
        })
        $("#is_delete").click(function () {
            var is_ch = $(this).is(':checked');
            $(".is_delete").prop("checked", is_ch);
        })
        $(".select_horizontal").click(function () {
            var p = $(this).parents('tr');
            var is_ch = $(this).is(':checked');
            p.find("input[type=checkbox]").prop("checked", is_ch);
        })
    })
</script>
@endpush