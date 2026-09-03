@extends('admin::layouts.admin_template')
@section('content')
<div class="row">
	<div class="col-md-6">
		<div class="card">
			<div class="card-header card-primary align-items-center d-flex">
                <h4 class="card-title mb-0 flex-grow-1">Menus</h4>
                <div class="flex-shrink-0">
                </div>
            </div> 

            <div class="card-body">
            	@if(!empty($rows))
            	<ul class='draggable-menu draggable-menu-active'>
            	  @foreach($rows as $menu)            	 
				  <li data-id='{{$menu->id}}' data-name='{{$menu->name}}'>
					  <div @if($menu->is_active==0) class="alert-danger" @endif>
                            <i class='{{$menu->icon}}'></i> {{$menu->name}} 
                            <span class='pull-right'>
                            	<a class='fa fa-pencil' title='Edit' href='{{ AdminHelper::adminpath("menu-management/edit/".$menu->id) }}'></a>
                            	<a title='Delete' class='fa fa-trash' onclick='return confirm("Are you sure to delete?")' href='{{ AdminHelper::adminpath("menu-management/delete/".$menu->id) }}'></a>
                            </span>                            
                        </div>                        
                        	@if(!empty($menu->children))
                        	<ul>
	                        	@foreach($menu->children as $child)
	                        	<li data-id='{{$child->id}}' data-name='{{$child->name}}'>
                                    <div @if($child->is_active==0) class="alert-danger" @endif><i class='{{$child->icon}}'></i> {{$child->name}}
                                        <span class='pull-right'>
                                        	<a class='fa fa-pencil' title='Edit' href='{{ AdminHelper::adminpath("menu-management/edit/".$child->id) }}'></a>
                                        	<a title="Delete" class='fa fa-trash' onclick='return confirm("Are you sure to delete?")' href='{{ AdminHelper::adminpath("menu-management/delete/".$child->id) }}'></a>
                                        </span>                                        
                                    </div>
                                </li>
	                        	@endforeach
                        	</ul>
                        	@endif                        
				  </li>					  
				  @endforeach				  
				</ul>
				@endif
            </div>
		</div>
	</div>
	<div class="col-sm-6">
        @if(!empty($menu_item))
        <div class="card">
            <div class="card-header card-primary align-items-center d-flex">
                <h4 class="card-title mb-0 flex-grow-1">Edit Menu</h4>
                <div class="flex-shrink-0">
                </div>
            </div> 

            <div class="card-body">
             <form action="{{ route('postUpdateMenu', $menu_item->id) }}" method="post"> 
                @csrf
                <input type="hidden" name="return_url" value="{{ AdminHelper::adminpath() }}/menu-management">
                <div class="row">
                    <!-- <div class="col-md-12">
                        <div class="mb-3 ">
                            <label for="privileges" class="form-label">Privileges <span class="text-danger" title="This field is required">*</span></label>
                            <select name="privileges[]" class="form-control" id="privileges" multiple required>
                                @if(!empty($admin_privileges))
                                    @foreach($admin_privileges as $privilege)
                                        <option value="{{$privilege->id}}">{{$privilege->name}}</option>
                                    @endforeach
                                @endif
                            </select>
                            @error('privileges')
                                <div class="text-danger mt-1" role="alert">
                                    <strong>{{ $message }}</strong>
                                </div>
                            @enderror
                            <p class="text-muted"></p>
                        </div>
                    </div> -->
                    <!-- <div class="col-md-12">
                        <div class="mb-3 ">
                            <label for="privileges_config" class="form-label">Privileges Configuration <span class="text-danger" title="This field is required">*</span></label>
                            <select name="privileges_config[]" class="form-control" id="privileges_config" multiple required>
                                <option value="is_visible">View</option>
                                <option value="is_create">Create</option>
                                <option value="is_read">Read</option>
                                <option value="is_edit">Update</option>
                                <option value="is_delete">Delete</option>
                            </select>
                            @error('privileges_config')
                                <div class="text-danger mt-1" role="alert">
                                    <strong>{{ $message }}</strong>
                                </div>
                            @enderror
                            <p class="text-muted"></p>
                        </div>
                    </div> -->
                    <div class="col-md-12">
                        <div class="mb-3 ">
                            <label for="privileges_config" class="form-label">Parent Menu</label>
                            <select name="parent_id" class="form-control">
                                <option value="">Select Menu</option>
                                @if(!empty($parent_menus))
                                @foreach($parent_menus as $pmenu)
                                    <option value="{{ $pmenu->id }}" {{ ($menu_item->parent_id==$pmenu->id)?'selected':'' }}>{{ $pmenu->name }}</option>
                                @endforeach
                                @endif
                            </select>
                            @error('parent_id')
                                <div class="text-danger mt-1" role="alert">
                                    <strong>{{ $message }}</strong>
                                </div>
                            @enderror
                            <p class="text-muted"></p>
                        </div>
                    </div>

                    
                    <div class="col-md-12">
                        <div class="mb-3 ">
                            <label for="menu_name" class="form-label">Menu Name <span class="text-danger" title="This field is required">*</span></label>
                            <input type="text" title="Menu Name" class="form-control" name="menu_name" id="menu_name" value="{{ (old('menu_name'))?old('menu_name'):$menu_item->name }}"required>                     
                            @error('menu_name')
                                <div class="text-danger mt-1" role="alert">
                                    <strong>{{ $message }}</strong>
                                </div>
                            @enderror
                            <p class="text-muted"></p>
                        </div>                      
                    </div>

                    <div class="col-md-12">
                        <div class="mb-3 ">
                            <label for="type" class="form-label">Type <span class="text-danger" title="This field is required">*</span></label>
                            <select name="type" class="form-control" id="type" required>
                                <option value="URL" {{ ($menu_item->type=='URL')?'selected':'' }}>URL</option>
                                <option value="Route" {{ ($menu_item->type=='Route')?'selected':'' }}>Route</option>
                            </select>
                            @error('type')
                                <div class="text-danger mt-1" role="alert">
                                    <strong>{{ $message }}</strong>
                                </div>
                            @enderror
                            <p class="text-muted"></p>
                        </div>
                    </div>

                    <div class="col-md-12">
                        <div class="mb-3 ">
                            <label for="menu_path" class="form-label">Menu Path <span class="text-danger" title="This field is required">*</span></label>
                           <input type="text" title="Menu Path" class="form-control" name="menu_path" id="menu_path" value="{{ (old('menu_path'))?old('menu_path'):$menu_item->path }}" required>
                            @error('menu_path')
                                <div class="text-danger mt-1" role="alert">
                                    <strong>{{ $message }}</strong>
                                </div>
                            @enderror
                            <p class="text-muted"></p>
                        </div>
                    </div>

                    <div class="col-md-12">
                        <div class="mb-3 ">
                            <label for="icon" class="form-label">Icon </label>
                            <input type="text" title="Icon" class="form-control" name="icon" id="icon" value="{{ (old('icon'))?old('icon'):$menu_item->icon }}">
                            @error('icon')
                                <div class="text-danger mt-1" role="alert">
                                    <strong>{{ $message }}</strong>
                                </div>
                            @enderror
                            <p class="text-muted"></p>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="mb-3 ">
                            <label for="sorting" class="form-label">Sorting <span class="text-danger" title="This field is required">*</span></label>
                            <input type="number" title="Sorting" class="form-control" name="sorting" id="sorting" value="{{ $menu_item->sorting }}" required>
                            @error('sorting')
                                <div class="text-danger mt-1" role="alert">
                                    <strong>{{ $message }}</strong>
                                </div>
                            @enderror
                            <p class="text-muted"></p>
                        </div>
                    </div>

                    <div class="col-md-12">
                        <div class="mb-3 ">
                            <label for="sql_query" class="form-label">Count (SQL QUERY)</label>
                            <input type="text" title="Database Table Name" class="form-control" name="sql_query" id="sql_query" placeholder="Ex: SELECT count(id) as total FROM table_name" value="{{ $menu_item->sql_query }}">
                            @error('sql_query')
                                <div class="text-danger mt-1" role="alert">
                                    <strong>{{ $message }}</strong>
                                </div>
                            @enderror
                            <p class="text-muted"></p>
                        </div>
                    </div>

                    <div class="col-md-12">
                        <div class="mb-3 ">
                            <label for="status" class="form-label">Status <span class="text-danger" title="This field is required">*</span></label>
                            <select name="status" class="form-control">
                                <option value="1" {{ ($menu_item->is_active==1)?'selected':'' }}>Active</option>
                                <option value="0" {{ ($menu_item->is_active==0)?'selected':'' }}>Inctive</option>
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
                        <div class="col-sm-10">                             
                            <input type="submit" name="submit" value="Save" class="btn btn-primary">
                        </div>
                    </div>
                </div>
                </form>
            </div>
        </div>
        @else
		<div class="card">
			<div class="card-header card-primary align-items-center d-flex">
                <h4 class="card-title mb-0 flex-grow-1">Add Menu</h4>
                <div class="flex-shrink-0">
                </div>
            </div> 

            <div class="card-body">
             <form action="{{ route('postAddMenu') }}" method="post"> 
            	@csrf
            	<input type="hidden" name="return_url" value="{{ AdminHelper::adminpath() }}/menu-management">
            	<div class="row">
                    <!-- <div class="col-md-12">
                        <div class="mb-3 ">
                            <label for="privileges" class="form-label">Privileges <span class="text-danger" title="This field is required">*</span></label>
                            <select name="privileges[]" class="form-control" id="privileges" multiple required>
                            	@if(!empty($admin_privileges))
                            		@foreach($admin_privileges as $privilege)
                            			<option value="{{$privilege->id}}">{{$privilege->name}}</option>
                            		@endforeach
                            	@endif
                            </select>
                            @error('privileges')
                                <div class="text-danger mt-1" role="alert">
                                    <strong>{{ $message }}</strong>
                                </div>
                            @enderror
                            <p class="text-muted"></p>
                        </div>
                    </div> -->
                    <!-- <div class="col-md-12">
                        <div class="mb-3 ">
                            <label for="privileges_config" class="form-label">Privileges Configuration <span class="text-danger" title="This field is required">*</span></label>
                            <select name="privileges_config[]" class="form-control" id="privileges_config" multiple required>
                            	<option value="is_visible">View</option>
                            	<option value="is_create">Create</option>
                            	<option value="is_read">Read</option>
                            	<option value="is_edit">Update</option>
                            	<option value="is_delete">Delete</option>
                            </select>
                            @error('privileges_config')
                                <div class="text-danger mt-1" role="alert">
                                    <strong>{{ $message }}</strong>
                                </div>
                            @enderror
                            <p class="text-muted"></p>
                        </div>
                    </div> -->
                    <div class="col-md-12">
                        <div class="mb-3 ">
                            <label for="privileges_config" class="form-label">Parent Menu</label>
                            <select name="parent_id" class="form-control">
                                <option value="">Select Menu</option>
                                @if(!empty($parent_menus))
                                @foreach($parent_menus as $pmenu)
                                    <option value="{{ $pmenu->id }}">{{ $pmenu->name }}</option>
                                @endforeach
                                @endif
                            </select>
                            @error('parent_id')
                                <div class="text-danger mt-1" role="alert">
                                    <strong>{{ $message }}</strong>
                                </div>
                            @enderror
                            <p class="text-muted"></p>
                        </div>
                    </div>

                    
                    <div class="col-md-12">
                        <div class="mb-3 ">
                            <label for="menu_name" class="form-label">Menu Name <span class="text-danger" title="This field is required">*</span></label>
                            <input type="text" title="Menu Name" class="form-control" name="menu_name" id="menu_name" value="{{ old('menu_name') }}"required>                     
                            @error('menu_name')
                                <div class="text-danger mt-1" role="alert">
                                    <strong>{{ $message }}</strong>
                                </div>
                            @enderror
                            <p class="text-muted"></p>
                        </div>                      
                    </div>

                    <div class="col-md-12">
                        <div class="mb-3 ">
                            <label for="type" class="form-label">Type <span class="text-danger" title="This field is required">*</span></label>
                            <select name="type" class="form-control" id="type" required>
                            	<option value="URL">URL</option>
                            	<option value="Route">Route</option>
                            </select>
                            @error('type')
                                <div class="text-danger mt-1" role="alert">
                                    <strong>{{ $message }}</strong>
                                </div>
                            @enderror
                            <p class="text-muted"></p>
                        </div>
                    </div>

                    <div class="col-md-12">
                        <div class="mb-3 ">
                            <label for="menu_path" class="form-label">Menu Path <span class="text-danger" title="This field is required">*</span></label>
                           <input type="text" title="Menu Path" class="form-control" name="menu_path" id="menu_path" value="{{ old('menu_path') }}" required>
                            @error('menu_path')
                                <div class="text-danger mt-1" role="alert">
                                    <strong>{{ $message }}</strong>
                                </div>
                            @enderror
                            <p class="text-muted"></p>
                        </div>
                    </div>

                    <div class="col-md-12">
                        <div class="mb-3 ">
                            <label for="icon" class="form-label">Icon </label>
                            <input type="text" title="Icon" class="form-control" name="icon" id="icon" value="{{ old('icon') }}">
                            @error('icon')
                                <div class="text-danger mt-1" role="alert">
                                    <strong>{{ $message }}</strong>
                                </div>
                            @enderror
                            <p class="text-muted"></p>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="mb-3 ">
                            <label for="sorting" class="form-label">Sorting <span class="text-danger" title="This field is required">*</span></label>
                            <input type="number" title="Sorting" class="form-control" name="sorting" id="sorting" value="{{ $last_sort+1 }}" required>
                            @error('sorting')
                                <div class="text-danger mt-1" role="alert">
                                    <strong>{{ $message }}</strong>
                                </div>
                            @enderror
                            <p class="text-muted"></p>
                        </div>
                    </div>

                    <div class="col-md-12">
                        <div class="mb-3 ">
                            <label for="sql_query" class="form-label">Count (SQL QUERY)</label>
                            <input type="text" title="Database Table Name" class="form-control" name="sql_query" id="sql_query" placeholder="Ex: SELECT count(id) as total FROM table_name" value="{{ old('sql_query') }}">
                            @error('sql_query')
                                <div class="text-danger mt-1" role="alert">
                                    <strong>{{ $message }}</strong>
                                </div>
                            @enderror
                            <p class="text-muted"></p>
                        </div>
                    </div>

                    <div class="col-md-12">
                        <div class="mb-3 ">
                            <label for="status" class="form-label">Status <span class="text-danger" title="This field is required">*</span></label>
                            <select name="status" class="form-control">
                            	<option value="1">Active</option>
                            	<option value="0">Inctive</option>
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
                        <div class="col-sm-10">                            	
                        	<input type="submit" name="submit" value="Save" class="btn btn-primary">
                        </div>
                    </div>
                </div>
            	</form>
            </div>
		</div>
        @endif
	</div>
</div>
@endsection

@push('bottom')
<script type="text/javascript">
   $(document).ready( function(){
   		$("#privileges").select2();
   		$("#privileges_config").select2();
   })
</script>
@endpush