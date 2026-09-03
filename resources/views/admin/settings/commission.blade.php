@extends('admin::layouts.admin_template')
@section('content')

<div class="row">
	<div class="col-md-6 offset-md-3">
		<div class="card">
            <div class="card-header card-primary">
                <i class="fa fa-cog"></i> {{ $page_title }}
            </div>
            <div class="card-body">
                <form method="post" id="form" enctype="multipart/form-data" action="{{ route('postSaveComissionSettings') }}">
                    @csrf
                    <div class="box-body">
                        <div class="form-group mb-3">
                            <label class="label-setting">Site commission for sellers who are selling own product<span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="commission_own_product" value="{{ (old('commission_own_product')?old('commission_own_product'):(!empty($row)?$row->commission_own_product:'')) }}" required>
                            @error('commission_own_product')
		                        <div class="text-danger mt-1" role="alert">
		                            <strong>{{ $message }}</strong>
		                        </div>
		                    @enderror
                        </div>   

                         <div class="form-group mb-3">
                            <label class="label-setting">Site commission for sellers who are selling iMart product<span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="commission_imart_product" value="{{ (old('commission_imart_product')?old('commission_imart_product'):(!empty($row)?$row->commission_imart_product:'')) }}" required>
                            @error('site_title')
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