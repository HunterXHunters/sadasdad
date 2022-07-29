<link href="/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
<link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css" rel="stylesheet" type="text/css" />
<link href="/css/bootstrapValidator.css" rel="stylesheet" type="text/css" />
{{Form::open(array('url' => 'rack/racktypestore','id'=>'saveRacks'))}}
{{ Form::hidden('_method', 'POST') }}

<h4><strong>Racktype details</strong></h4>
<div class="row">
@if(empty($manufacturerId))
 <div class="form-group col-sm-6">
  <label for="exampleInputEmail">Manufacturer*</label>
  <div class="input-group ">
    <span class="input-group-addon addon-red"><i class="fa fa-puzzle-piece"></i></span>
   <select name="mfg_id" id="mfg_id" class="form-control">
    <option value="0">Please select..</option>
    @foreach($mfgDetails as $key=>$value)
    <option value="{{$value->org_id}}">{{$value->brand_name}}</option>
     @endforeach
    </select>
  </div>
</div>
@endif
 <div class="form-group col-sm-6">
  <label for="exampleInputEmail">Warehouse*</label>
  <div class="input-group ">
    <span class="input-group-addon addon-red"><i class="fa fa-puzzle-piece"></i></span>
   <select name="ware_id" id="ware_id" class="form-control">
    <option value="0">Please select..</option>
    @foreach($WHDetails as $key=>$value)
    <option value="{{$key}}">{{$value}}</option>
     @endforeach
    </select>
    <input type="hidden" name="org_id" id="org_id" value="">
  </div>
</div>
</div>
<div class="row">
	<div class="form-group col-sm-6">
        <label class="control-label" for="exampleInputEmail">Racktype Name*</label>
         <div class="input-group ">
            <span class="input-group-addon addon-red"><i class="fa fa-newspaper-o"></i></span>
            <input type='text'  name='rack_type_name' id='rack_type_name' value='' class='form-control '>
        </div>
    </div>
    <div class="form-group col-sm-6">
        <label class="control-label" for="exampleInputEmail">Racktype Code</label>
         <div class="input-group ">
            <span class="input-group-addon addon-red"><i class="ion-ios-barcode-outline"></i></span>
            <input type='text' name='rack_type_code' id='rack_type_code' value='' class='form-control' disabled='disabled'>
        </div>
    </div>
    <div class="form-group col-sm-6">
        <label class="control-label" for="exampleInputEmail">Height*</label>
         <div class="input-group ">
            <span class="input-group-addon addon-red"><i class="fa fa-arrows-v"></i></span>
            <input type='text' name='rack_height' id='rack_height' value='' class='form-control'>
        </div>
    </div>
    <div class="form-group col-sm-6">
        <label class="control-label" for="exampleInputEmail">Width*</label>
         <div class="input-group ">
            <span class="input-group-addon addon-red"><i class="fa fa-arrows-h"></i></span>
            <input type='text' name='rack_width' id='rack_width' value='' class='form-control'>
        </div>
    </div>
    <div class="form-group col-sm-6">
        <label class="control-label" for="exampleInputEmail">Depth*</label>
         <div class="input-group ">
            <span class="input-group-addon addon-red"><i class="fa fa-long-arrow-down"></i></span>
            <input type='text' name='rack_depth' id='rack_depth' value='' class='form-control'>
        </div>
    </div>
    <div class="form-group col-sm-6">
        <label class="control-label" for="exampleInputEmail">Dimension UOM*</label>
         <div class="input-group ">
            <span class="input-group-addon addon-red"><i class="ion-arrow-expand"></i></span>
            <select name="rack_dimension_id" id="rack_dimension_id" class="form-control">
              @foreach($dimension_uom as $key => $value)
                <option value="{{ $key}}">{{$value}}</option>
              @endforeach
           </select>
        </div>
    </div>
    <div class="form-group col-sm-6">
        <label class="control-label" for="exampleInputEmail">Rack Capacity*</label>
         <div class="input-group ">
            <span class="input-group-addon addon-red"><i class="fa fa-bars"></i></span>
           <input type='text' name='rack_capacity' id='rack_capacity' value='' class='form-control'>
        </div>
    </div>
    <div class="form-group col-sm-6">
        <label class="control-label" for="exampleInputEmail">Capacity UOM*</label>
         <div class="input-group ">
            <span class="input-group-addon addon-red"><i class="ion-cube"></i></span>
          <select name="rack_capacity_uom_id" id="rack_capacity_uom_id" class="form-control">
              @foreach($capacity_uom as $key => $value)
                <option value="{{ $key}}">{{$value}}</option>
              @endforeach
         </select>
        </div>
    </div>
    
</div>


<h4><strong>Bin details</strong></h4>
<div class="row">
	<div class="form-group col-sm-6">
        <label class="control-label" for="exampleInputEmail">Height*</label>
         <div class="input-group ">
            <span class="input-group-addon addon-red"><i class="fa fa-arrows-v"></i></span>
           <input type='text' name='bin_height' id='bin_height' value='' class='form-control'>
        </div>
    </div>
    <div class="form-group col-sm-6">
        <label class="control-label" for="exampleInputEmail">Width*</label>
         <div class="input-group ">
            <span class="input-group-addon addon-red"><i class="fa fa-arrows-h"></i></span>
           <input type='text' name='bin_width' id='bin_width' value='' class='form-control'>
        </div>
    </div>
    <div class="form-group col-sm-6">
        <label class="control-label" for="exampleInputEmail">Depth*</label>
         <div class="input-group ">
            <span class="input-group-addon addon-red"><i class="fa fa-long-arrow-down"></i></span>
           <input type='text' name='bin_depth' id='bin_depth' value='' class='form-control'>
        </div>
    </div>
    <div class="form-group col-sm-6">
        <label class="control-label" for="exampleInputEmail">Dimension UOM*</label>
         <div class="input-group ">
            <span class="input-group-addon addon-red"><i class="ion-arrow-expand"></i></span>
           <select name="bin_dimension_id" id="bin_dimension_id" class="form-control">
              @foreach($dimension_uom as $key => $value)
                <option value="{{ $key}}">{{$value}}</option>
              @endforeach
          </select>
        </div>
    </div>
    <div class="form-group col-sm-6">
        <label class="control-label" for="exampleInputEmail">Bin Capacity*</label>
         <div class="input-group ">
            <span class="input-group-addon addon-red"><i class="fa fa-table"></i></span>
           <input type='text' name='bin_capacity' id='bin_capacity' value='' class='form-control'>
        </div>
    </div>
    <div class="form-group col-sm-6">
        <label class="control-label" for="exampleInputEmail">Capacity UOM*</label>
         <div class="input-group ">
            <span class="input-group-addon addon-red"><i class="ion-cube"></i></span>
           <select name="bin_capacity_uom_id" id="bin_capacity_uom_id" class="form-control">
              @foreach($capacity_uom as $key => $value)
                <option value="{{ $key}}">{{$value}}</option>
              @endforeach
         </select>
        </div>
    </div>
    <div class="form-group col-sm-6">
        <label class="control-label" for="exampleInputEmail">No of Bins*</label>
         <div class="input-group ">
            <span class="input-group-addon addon-red"><i class="fa fa-th"></i></span>
           <input type='text' name='no_of_bins' id='no_of_bins' value='' class='form-control'>
        </div>
    </div>
</div>




{{ Form::submit('CREATE', array('class' => 'btn btn-primary')) }}
{{ Form::close() }}

<!--  <script src="/scripts/jquery-1.10.2.min.js"></script>
 <script src="/js/bootstrap.min.js" type="text/javascript"></script>
 <script src="/js/bootstrapValidator.js"></script> -->
@section('script')
{{HTML::script('scripts/jquery-1.10.2.min.js')}}
{{HTML::script('js/bootstrap.min.js')}}
{{HTML::script('js/bootstrapValidator.js')}}
@stop 

@section('style')
    {{HTML::style('css/style.css')}}
@stop

  <script type="text/javascript">
/*    $(function(){
      $('form').validate({
        rules:{
          rack_type_name:{
            required:true
          },rack_height:{
            required:true
          },rack_width:{
            required:true
            
          },rack_depth:{
            required:true
          },rack_dimension_id:{
            requiredDropdown:true
          },rack_capacity:{
            required:true
          },rack_capacity_uom_id:{
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
          rack_type_name:{
            required: 'Rack type  Name is required'
          },rack_height:{
            required: 'Rack height is required'
          },rack_width:{
            required: 'Rack width is required'
            
          },rack_depth:{
             required: 'Rack depth is required'
          },rack_dimension_id:{
            requiredDropdown: 'Rack dimension is required'
          },rack_capacity:{
            required: 'Rack capacity is required'
          },rack_capacity_uom_id:{
            requiredDropdown: 'capacity dimension  is required'
            
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
    });*/
$('#ware_id').change(function(){
var ware_id = $('#ware_id').val();
var url = '/pallets/getOrg/'+ware_id;
var posting =$.get( url);
posting.done(function( data ) {
//console.log(data);
    $('#org_id').val(data);    
  }); 
});
$('#mfg_id').change(function(){
var mfg_id = $('#mfg_id').val();
var url = '/rack/getwarehouses/'+mfg_id;
$('[id="ware_id"]').empty();
var posting =$.get( url);
posting.done(function( data ) {
//console.log(data);
    $('[id="ware_id"]').append('<option value="0" selected="selected">Please select... </option>');
    $.each(data, function(key, value){
        $('[id="ware_id"]').append('<option value="' + key + '">' + value + '</option>');
    });   
  }); 
});
$(document).ready(function() {
    $('#saveRacks').bootstrapValidator({
//        live: 'disabled',
        message: 'This value is not valid',
        feedbackIcons: {
            valid: 'glyphicon glyphicon-ok',
            invalid: 'glyphicon glyphicon-remove',
            validating: 'glyphicon glyphicon-refresh'
        },
        fields: {
          
            rack_type_name: {
                    validators: {
                      remote: {
                      message : 'Name already exists.Please enter a new name',
                      url: '/rack/validaterack',
                      type: 'GET',
                      //data: ['name': $('#name').val()];
                      delay: 2000     // Send Ajax request every 2 seconds
                  },
                        notEmpty: {
                            message: 'Rack Type Name is required.'
                        }
                    }
                },
            rack_dimension_id: {
                    validators: {
                    callback: {
                        message: 'Please choose Dimension UOM',
                        callback: function(value, validator, $field) {
                            var options = $('[id="rack_dimension_id"]').val();
                            return (options != 0);
                        }
                    },                      
                        notEmpty: {
                            message: 'Dimension UOM is required.'
                        }
                    }
                },
            ware_id: {
                    validators: {
                    callback: {
                        message: 'Please choose Warehouse',
                        callback: function(value, validator, $field) {
                            var options = $('[id="ware_id"]').val();
                            return (options != 0);
                        }
                    },                      
                        notEmpty: {
                            message: 'Warehouse is required.'
                        }
                    }
                },
            mfg_id: {
                    validators: {
                    callback: {
                        message: 'Please choose Manufacturer',
                        callback: function(value, validator, $field) {
                            var options = $('[id="mfg_id"]').val();
                            return (options != 0);
                        }
                    },                      
                        notEmpty: {
                            message: 'Manufacturer is required.'
                        }
                    }
                },                                
            rack_capacity: {
                    validators: {
                        notEmpty: {
                            message: 'Rack Capacity is required.'
                        },
                         between: {
                          min: 0.01,
                          max:10000000,
                          message: 'Enter valid Rack Capacity.'
                      }                        
                    }
                },
            rack_depth: {
                    validators: {
                        notEmpty: {
                            message: 'Rack depth is required.'
                        },
                         between: {
                          min: 0.01,
                          max:10000000,
                          message: 'Enter valid Rack depth.'
                      }                         
                    }
                },
            rack_width: {
                    validators: {
                        notEmpty: {
                            message: 'Rack width is required.'
                        },
                         between: {
                          min: 0.01,
                          max:10000000,
                          message: 'Enter valid Rack width.'
                      }                        
                    }
                },
            rack_height: {
                    validators: {
                        notEmpty: {
                            message: 'Rack height is required.'
                        },
                         between: {
                          min: 0.01,
                          max:10000000,
                          message: 'Enter valid Rack height.'
                      }                        
                    }
                },
            rack_capacity_uom_id: {
                    validators: {
                    callback: {
                        message: 'Please choose Capacity UOM',
                        callback: function(value, validator, $field) {
                            var options = $('[id="rack_capacity_uom_id"]').val();
                            return (options != 0);
                        }
                    },                       
                        notEmpty: {
                            message: 'Capacity UOM is required.'
                        }
                    }
                },
            bin_height: {
                    validators: {
                    callback: {
                        message: 'Bin height should be less than Rack Height.',
                        callback: function(value, validator, $field) {
                            var options = parseFloat($('[id="rack_height"]').val(),10);
                            var bin = parseFloat($('[id="bin_height"]').val(),10);
                            //return (options >= bin);
                            if(bin > 0){
                              return (options >= bin);
                            }else{
                              return true;
                            }                            
                        }
                    },                       
                        notEmpty: {
                            message: 'Bin height is required.'
                        },
                         between: {
                          min: 0.1,
                          max:10000000,
                          message: 'Enter valid Bin height.'
                      }
                    }
                }, 
            bin_width: {
                    validators: {
                    callback: {
                        message: 'Bin width should be less than Rack width.',
                        callback: function(value, validator, $field) {
                            var options = parseFloat($('[id="rack_width"]').val(),10);
                            var bin = parseFloat($('[id="bin_width"]').val(),10);
                            /*return (options >= bin);*/
                            if(bin > 0){
                              return (options >= bin);
                            }else{
                              return true;
                            }                            
                        }
                    },                       
                        notEmpty: {
                            message: 'Bin width is required.'
                        },
                         between: {
                          min: 0.1,
                          max:10000000,
                          message: 'Enter valid Bin width.'
                      }
                    }
                },
            bin_depth: {
                    validators: {
                    callback: {
                        message: 'Bin depth should be less than Rack depth.',
                        callback: function(value, validator, $field) {
                            var options = parseFloat($('[id="rack_depth"]').val(),10);
                            var bin = parseFloat($('[id="bin_depth"]').val(),10);
                            if(bin > 0){
                              return (options >= bin);
                            }else{
                              return true;
                            }
                            
                        }
                    },                       
                        notEmpty: {
                            message: 'Bin depth is required.'
                        },
                         between: {
                          min: 0.1,
                          max:10000000,
                          message: 'Enter valid Bin depth.'
                      }
                    }
                },
            bin_capacity: {
                    validators: {
                    callback: {
                        message: 'Bin capacity should be less than Rack capacity.',
                        callback: function(value, validator, $field) {
                            var options = parseFloat($('[id="rack_capacity"]').val(),10);
                            var bin = parseFloat($('[id="bin_capacity"]').val(),10);
                            if(bin > 0){
                              return (options >= bin);
                            }else{
                              return true;
                            }
                        }
                    },                       
                        notEmpty: {
                            message: 'Bin capacity is required.'
                        },
                         between: {
                          min: 0.1,
                          max:10000000,
                          message: 'Enter valid Bin capacity.'
                      }
                    }
                },
            no_of_bins: {
                    validators: {
                        notEmpty: {
                            message: 'No of Bins is required.'
                        },
                         between: {
                          min: 1,
                          max:10000000,
                          message: 'Enter valid Rack height.'
                      }
                    }
                },                
            bin_dimension_id: {
                    validators: {
                    callback: {
                        message: 'Please choose Dimension UOM',
                        callback: function(value, validator, $field) {
                            var options = $('[id="bin_dimension_id"]').val();
                            return (options != 0);
                        }
                    }
                    }
                },
            bin_capacity_uom_id: {
                    validators: {
                    callback: {
                        message: 'Please choose Capacity UOM',
                        callback: function(value, validator, $field) {
                            var options = $('[id="bin_capacity_uom_id"]').val();
                            return (options != 0);
                        }
                    }
                    }
                }                                                                                                                                                                                       
        }
    }).on('success.form.bv', function(event) {
        //event.preventDefault();
        return true;
    })/*.validate({
        submitHandler: function (form) {
            return false;
        }
    })*/;
});
  </script>



      
