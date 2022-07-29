<!-- <div class="box"> -->

<!-- <div class="box-header with-border">
	<h3 class="box-title"><strong>Rack Types </strong>Apply</h3>
</div> -->

<!--   <div class="col-sm-12">
    <div class="tile-body nopadding">  -->

    {{ Form::open(array('url' => 'entities/store')) }}
    {{ Form::hidden('_method', 'POST') }}
    {{ HTML::ul($errors->all()) }}
    <!-- <h1 style="color:Brown">Edit RackType {{ $racktypes->rack_type_name }}</h1> -->
    <input type="hidden" id="entity_type_id" name="entity_type_id" value="{{$entity_type_id}}" />
    <input type="hidden" id="parent_entity_id" name="parent_entity_id" value="{{$parent_entity_id}}" />
    <input type="hidden" name="org_id" id="org_id" value="{{ $org_id }}" class="form-control">
    <input type="hidden" name="location_id" id="location_id" value="{{ $location_id }}" class="form-control">
    <h4><strong>Racktype details</strong></h4>
        <div class="row">
        	  <div class="form-group col-sm-6">
                <label for="exampleInputEmail">Racktype Name</label>
                <div class="input-group ">
                    <span class="input-group-addon addon-red"><i class="fa fa-keyboard-o"></i></span>
                    <input type='text'  name='entity_name' id='entity_name' value='{{$racktypes->rack_type_name}}' class='form-control'>
                </div>
            </div>
            <div class="form-group col-sm-6">
                <label for="exampleInputEmail">Racktype Code</label>
                <div class="input-group ">
                    <span class="input-group-addon addon-red"><i class="fa fa-keyboard-o"></i></span>
                    <input type='text' name='entity_code' id='entity_code' value='{{$racktypes->rack_type_code}}' readonly class='form-control'>
                </div>
            </div>
              <div class="form-group col-sm-6">
                    <label class="control-label" for="exampleInputEmail">Entity Location</label>
                    <div class="input-group">
                        <span class="input-group-addon addon-red"><i class="fa fa-map-marker"></i></span>
                        <input type="text" id="entity_location" name="entity_location" value="" class="form-control">
                    </div>                        
                </div> 
            <div class="form-group col-sm-6">
                <label for="exampleInputEmail">Height</label>
                <div class="input-group ">
                    <span class="input-group-addon addon-red"><i class="fa fa-keyboard-o"></i></span>
                    <input type='text'  name='height' id='height' value='{{$racktypes->rack_height}}' class='form-control'>
                </div>
            </div>
            <div class="form-group col-sm-6">
                <label for="exampleInputEmail">Width</label>
                <div class="input-group ">
                    <span class="input-group-addon addon-red"><i class="fa fa-keyboard-o"></i></span>
                    <input type='text'  name='width' id='width' value='{{$racktypes->rack_width}}' class='form-control'>
                </div>
            </div>
            <div class="form-group col-sm-6">
                <label for="exampleInputEmail">Depth</label>
                <div class="input-group ">
                    <span class="input-group-addon addon-red"><i class="fa fa-keyboard-o"></i></span>
                    <input type='text'  name='depth' id='depth' value='{{$racktypes->rack_depth}}' class='form-control'>
                </div>
            </div>
            <div class="form-group col-sm-6">
                <label for="exampleInputEmail">Dimension UOM</label>
                <div class="input-group ">
                    <span class="input-group-addon addon-red"><i class="fa fa-keyboard-o"></i></span>
                    <select name="dimension_id" id="dimension_id" class="form-control select1">
                          @foreach($dimension_uom as $key => $value)
                           @if($key== $racktypes->rack_dimension_id)
                            <option value="{{ $key}}" selected="selected">{{$value}}</option>
                            @else
                            <option value="{{ $key}}">{{$value}}</option>               
                           @endif
                          @endforeach
                     </select>
                </div>
            </div>
            <div class="form-group col-sm-6">
                <label for="exampleInputEmail">Rack Capacity</label>
                <div class="input-group ">
                    <span class="input-group-addon addon-red"><i class="fa fa-keyboard-o"></i></span>
                    <input type='text'  name='capacity' id='capacity' value='{{$racktypes->rack_capacity}}' class='form-control'>
                </div>
            </div>
            <div class="form-group col-sm-6">
                <label for="exampleInputEmail">Capacity UOM</label>
                <div class="input-group ">
                    <span class="input-group-addon addon-red"><i class="fa fa-keyboard-o"></i></span>
                    <select name="capacity_uom_id" id="capacity_uom_id" class="form-control select1">
                          @foreach($capacity_uom as $key => $value)
                            @if($key== $racktypes->rack_capacity_uom_id)
                            <option value="{{ $key}}" selected="selected">{{$value}}</option>
                            @else
                            <option value="{{ $key}}">{{$value}}</option>  
                           @endif
                          @endforeach
                     </select>
                </div>
            </div>
        </div>
       
      <h4><strong>Bin details</strong></h4>
        <div class="row">
            <div class="form-group col-sm-6">
                <label for="exampleInputEmail">Bin Name</label>
                <div class="input-group ">
                    <span class="input-group-addon addon-red"><i class="fa fa-keyboard-o"></i></span>
                    <input type='text'  name='bin_name' id='bin_name' value='' class='form-control'>
                </div>
            </div>
            <div class="form-group col-sm-6">
                <label for="exampleInputEmail">Height</label>
                <div class="input-group ">
                    <span class="input-group-addon addon-red"><i class="fa fa-keyboard-o"></i></span>
                    <input type='text'  name='bin_height' id='bin_height' value='{{$racktypes->bin_height}}' class='form-control'>
                </div>
            </div>
            <div class="form-group col-sm-6">
                <label for="exampleInputEmail">Width</label>
                <div class="input-group ">
                    <span class="input-group-addon addon-red"><i class="fa fa-keyboard-o"></i></span>
                    <input type='text'  name='bin_width' id='bin_width' value='{{$racktypes->bin_width}}' class='form-control'>
                </div>
            </div>
            <div class="form-group col-sm-6">
                <label for="exampleInputEmail">Depth</label>
                <div class="input-group ">
                    <span class="input-group-addon addon-red"><i class="fa fa-keyboard-o"></i></span>
                    <input type='text'  name='bin_depth' id='bin_depth' value='{{$racktypes->bin_depth}}' class='form-control'>
                </div>
            </div>
            <div class="form-group col-sm-6">
                <label for="exampleInputEmail">Dimension UOM</label>
                <div class="input-group ">
                    <span class="input-group-addon addon-red"><i class="fa fa-keyboard-o"></i></span>
                    <select name="bin_dimension_id" id="bin_dimension_id" class="form-control select1">
                          @foreach($dimension_uom as $key => $value)
                            @if($key== $racktypes->bin_dimension_id)
                            <option value="{{ $key}}" selected="selected">{{$value}}</option>
                            @else
                            <option value="{{ $key}}">{{$value}}</option>               
                           @endif
                          @endforeach
                     </select>
                </div>
            </div>
            <div class="form-group col-sm-6">
                <label for="exampleInputEmail">Bin Capacity</label>
                <div class="input-group ">
                    <span class="input-group-addon addon-red"><i class="fa fa-keyboard-o"></i></span>
                    <input type='text'  name='bin_capacity' id='bin_capacity' value='{{$racktypes->bin_capacity}}' class='form-control'>
                </div>
            </div>
            <div class="form-group col-sm-6">
                <label for="exampleInputEmail">Capacity UOM</label>
                <div class="input-group ">
                    <span class="input-group-addon addon-red"><i class="fa fa-keyboard-o"></i></span>
                    <select name="bin_capacity_uom_id" id="bin_capacity_uom_id" class="form-control select1">
                          @foreach($capacity_uom as $key => $value)
                            @if($key== $racktypes->bin_capacity_uom_id)
                            <option value="{{ $key}}" selected="selected">{{$value}}</option>
                            @else
                            <option value="{{ $key}}">{{$value}}</option>  
                           @endif
                          @endforeach
                     </select>
                </div>
            </div>
            <div class="form-group col-sm-6">
                <label for="exampleInputEmail">No of Bins</label>
                <div class="input-group ">
                    <span class="input-group-addon addon-red"><i class="fa fa-keyboard-o"></i></span>
                    <input type='text'  name='no_of_bins' id='no_of_bins' value='{{$racktypes->no_of_bins}}' class='form-control'>
                </div>
            </div>
        </div>
        <div> {{ Form::submit('SAVE', array('class' => 'btn btn-primary margin')) }}
            {{ Form::close() }}
            </div>
<!--     </div>
  </div> -->
<!-- </div> -->

@section('style')
    {{HTML::style('css/style.css')}}
@stop

@section('script')
  <script type="text/javascript">
    $(function(){
      $('form').validate({
        rules:{
          entity_name:{
            required:true
          },entity_code:{
            required:true
          },height:{
            required:true
          },width:{
            required:true
          },depth:{
            required:true
          },dimension_id:{
            requiredDropdown:true
          },capacity:{
            required:true
          },capacity_uom_id:{
            requiredDropdown:true
          },bin_name:{
            requiredDropdown:true
          },bin_height:{
            required:true
          },bin_width:{
            required:true
          },bin_depth:{
            required:true
          },bin_dimension_id:{
            requiredDropdown:true
          },bin_capacity:{
            required:true
          },bin_capacity_uom_id:{
            requiredDropdown:true
          },no_of_bins:{
            required:true
          }
        },messages:{
          entity_name:{
            required: 'Rack Type Name is required'
          },entity_code:{
            required: 'Rack Code is required'
          },height:{
            required: 'Rack height is required'
          },width:{
            required: 'Rack width is required'
            
          },depth:{
             required: 'Rack depth is required'
          },dimension_id:{
            requiredDropdown: 'Rack dimension is required'
          },capacity:{
            required: 'Rack capacity is required'
          },capacity_uom_id:{
            requiredDropdown: 'capacity dimension  is required'
            
          },bin_name:{
           required: 'Bin Name is required'
          },bin_height:{
           required: 'Bin height is required'
          },bin_width:{
           required: 'Bin width is required'
          },bin_depth:{
            required: 'Bin depth is required'
          },bin_dimension_id:{
            requiredDropdown: 'Bin dimension is required'
          },bin_capacity:{
            required: 'Bin capacity is required'
          },bin_capacity_uom_id:{
           requiredDropdown: 'Bin capacity dimension is required'
          },no_of_bins:{
            required: ' bins are required'
          }
        },submitHandler:function(form){
          form.submit();
        },errorPlacement: function(error, element) {
          element.closest('td').append(error);
        },unhighlight: function (element, errorClass, validClass) {
          if ($(element).hasClass('optional') && $(element).val() == '') {
            $(element).removeClass('error valid');
          }else{
            $(element).removeClass('error').addClass('valid');
          }
        }
      });
    });
  </script>
@stop

