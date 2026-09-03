@extends('admin::layouts.admin_template')
@section('content')

<div class="row">
	<div class="col-md-6 offset-md-3">
		<div class="card">
            <div class="card-header card-primary">
                <i class="fa fa-cog"></i> {{ $page_title }}
            </div>
            <div class="card-body">
                <form method="post" id="form" enctype="multipart/form-data" action="{{ route('postSaveEmailSettings') }}">
                    @csrf
                    <div class="box-body">
                        <div class="form-group mb-3">
                            <label class="label-setting">Email Sender<span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="email_sender" value="{{ (old('email_sender')?old('email_sender'):(!empty($row)?$row->email_sender:'')) }}" required>
                            @error('email_sender')
		                        <div class="text-danger mt-1" role="alert">
		                            <strong>{{ $message }}</strong>
		                        </div>
		                    @enderror
                        </div>                                               
                        <div class="form-group mb-3">
                            <label class="label-setting">Mail Driver<span class="text-danger">*</span></label>
                            <select name="mail_driver" class="form-control" required>
                                <option value="smtp" {{ (old('mail_driver')=='smtp')?'selected':(((!empty($row) && $row->mail_driver=='smtp'))?'selected':'') }}>smtp</option>
                                <option value="mail" {{ (old('mail_driver')=='mail')?'selected':(((!empty($row) && $row->mail_driver=='mail'))?'selected':'') }}>mail</option>
                                <option value="sendmail" {{ (old('mail_driver')=='sendmail')?'selected':(((!empty($row) && $row->mail_driver=='sendmail'))?'selected':'') }}>sendmail</option>
                            </select>
                            <div class="text-muted"></div>
                            @error('mail_driver')
		                        <div class="text-danger mt-1" role="alert">
		                            <strong>{{ $message }}</strong>
		                        </div>
		                    @enderror
                        </div>
                        <div class="form-group mb-3">
                            <label class="label-setting">SMTP Host<span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="smtp_host" value="{{ (old('smtp_host')?old('smtp_host'):(!empty($row)?$row->smtp_host:'')) }}" required>
                            <div class="text-muted"></div>
                            @error('smtp_host')
		                        <div class="text-danger mt-1" role="alert">
		                            <strong>{{ $message }}</strong>
		                        </div>
		                    @enderror
                        </div>
                        <div class="form-group mb-3">
                            <label class="label-setting">SMTP Port</label>
                            <input type="text" class="form-control" name="smtp_port" value="{{ (old('smtp_port')?old('smtp_port'):(!empty($row)?$row->smtp_port:'')) }}">
                            <div class="text-muted">default 25</div>
                            @error('smtp_port')
		                        <div class="text-danger mt-1" role="alert">
		                            <strong>{{ $message }}</strong>
		                        </div>
		                    @enderror
                        </div>
                        <div class="form-group mb-3">
                            <label class="label-setting">SMTP Username<span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="smtp_username" value="{{ (old('smtp_username')?old('smtp_username'):(!empty($row)?$row->smtp_username:'')) }}" required>
                            <div class="text-muted"></div>
                            @error('smtp_username')
                                <div class="text-danger mt-1" role="alert">
                                    <strong>{{ $message }}</strong>
                                </div>
                            @enderror
                        </div>
                        <div class="form-group mb-3">
                            <label class="label-setting">SMTP Password<span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="smtp_password" value="{{ (old('smtp_password')?old('smtp_password'):(!empty($row)?$row->smtp_password:'')) }}" required>
                            <div class="text-muted"></div>
                            @error('smtp_password')
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