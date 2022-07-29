<link href="/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
<link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css" rel="stylesheet" type="text/css" />
<link href="/css/bootstrapValidator.css" rel="stylesheet" type="text/css" />

          {{ Form::open(array('url' => 'entities/store','id'=>'createEntity')) }}
          {{ Form::hidden('_method', 'POST') }}      
          <input type="hidden" id="entity_type_id" name="entity_type_id" value="{{$entity_type_id}}" />
          <input type="hidden" id="parent_entity_id" name="parent_entity_id" value="{{$parent_entity_id}}" />
          <input type="hidden" name="org_id" id="org_id" value="{{ $org_id }}" class="form-control">
          <input type="hidden" name="ware_id" id="ware_id" value="{{ $ware_id }}" class="form-control">
          <input type="hidden" id="warelength" name="warelength" value="{{ $warelength}}" />
          <input type="hidden" id="warewidth" name="warewidth" value="{{ $warewidth}}" />
          <input type="hidden" id="wareheight" name="wareheight" value="{{ $wareheight}}" />
          <!--warearea-->
          <input type="hidden" id="warearea" name="warearea" value="{{$warearea}}" /> 
          <!--warearea -->  
          <!--warearea utilised--> 
          <input type="hidden" id="floorsumarea" name="floorsumarea" value="{{$floorsumarea}}" />
          <!--warearea utilised-->        
          <input type="hidden" id="floorsumlength" name="floorsumlength" value="{{ $floorsumlength}}" />
          <input type="hidden" id="floorsumwidth" name="floorsumwidth" value="{{ $floorsumwidth}}" />
          <input type="hidden" id="getcapacityparent" name="getcapacityparent" value="{{ $parent_capacity}}" />
          <input type="hidden" id="getcapacitychild" name="getcapacitychild" value="{{ $child_capacity}}" />
        
         <h4><strong>Entity Details</strong></h4>
            <div class="row">
             <div class="">
            	@if($entity_type_id==6003)
            	<div class="form-group col-sm-6">
                    <label class="control-label" for="exampleInputEmail">Select Entity Type</label>
                     <div class="input-group ">
                        <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
                        <select name="zone_type_id" id="zone_type_id" class="form-control">
                          <option value="">Select Entity </option>
                          <option value="1">Zone</option>
                          <option value="2">Open Type Zone</option>
                          <option value="3">Put Away Zone</option>
                          <option value="4">Dock</option>
                        </select>
                    </div>
                </div>
                 @endif
                <div class="form-group col-sm-6">
                    <label class="control-label" for="exampleInputEmail">{{$entity_name}} Name*</label>
                     <div class="input-group ">
                        <span class="input-group-addon addon-red"><i class="fa fa-newspaper-o"></i></span>
                        <input type="text" name="entity_name" id="entity_name" value="" class="form-control">
                    </div>
                </div>
<!--             	@if($entity_type_id == 6005)
                <div class="form-group col-sm-6">
                    <label class="control-label" for="exampleInputEmail">No of Pallets*</label>
                     <div class="input-group ">
                        <span class="input-group-addon addon-red"><i class="fa fa-slack"></i></span>
                        <input type="number" step="any" min="0" id="pallet_capacity" name="pallet_capacity" value="" class="form-control">
                    </div>
                </div>
                @endif  -->
                  <div class="form-group col-sm-6">
                    <label class="control-label" for="exampleInputEmail">Entity Location*</label>
                    <div class="input-group">
                        <span class="input-group-addon addon-red"><i class="fa fa-map-marker"></i></span>
                        <input type="text" id="entity_location" name="entity_location" value="" class="form-control">
                    </div>                        
                </div>
              </div>              
                <div class="form-group col-sm-6">
                    <label class="control-label" for="exampleInputEmail">Capacity*</label>
                    <div class="input-group">
                        <span class="input-group-addon addon-red"><i class="ion-filing"></i></span>
                        <input type="number" step="any" min="0" id="capacity" name="capacity" value="" onblur="getCapacity();" class="form-control">
                    </div>                        
                </div>
                <div class="form-group col-sm-6">
                    <label class="control-label" for="exampleInputEmail">Capacity UOM*</label>
                     <div class="input-group ">
                        <span class="input-group-addon addon-red"><i class="ion-cube"></i></span>
                        <select name="capacity_uom_id" id="capacity_uom_id" class="form-control">
                        @foreach($capacity_uom as $key => $value)
                          <option value="{{ $key}}">{{ $value}}</option>
                         @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <h4><strong>Dimension</strong></h4>           
            <div class="row">
            @if($entity_type_id==6001 || $entity_type_id==6002 || $entity_type_id==6004 || $entity_type_id==6005)
                <div class="form-group col-sm-6">
                    <label class="control-label" for="exampleInputEmail">Length*</label>
                    <div class="input-group ">
                        <span class="input-group-addon addon-red"><i class="fa fa-arrows-h"></i></span>
                        <input type="text"  maxlength="8" name="length" id="length" value="" class="form-control" onblur="getAreaVal();">
                    </div>
                </div>
                <div class="form-group col-sm-6">
                    <label class="control-label" for="exampleInputEmail">Width*</label>
                    <div class="input-group ">
                        <span class="input-group-addon addon-red"><i class="fa fa-codepen"></i></span>
                        <input type="text"  maxlength="8" name="width" id="width" value="" class="form-control" onblur="getAreaVal();">
                    </div>
                </div>
                </div>
                <div class="row">
               	<div class="form-group col-sm-6">
                    <label class="control-label" for="exampleInputEmail">Height*</label>
                    <div class="input-group ">
                        <span class="input-group-addon addon-red"><i class="fa fa-arrows-v"></i></span>
                        <input type="text"  maxlength="8" name="height" id="height" value="" class="form-control" onblur="getAreaVal();">
                    </div>
                </div>
                 @endif
                 @if($entity_type_id==6003 || $entity_type_id==6007 || $entity_type_id==6006)
                 <div class="form-group col-sm-6">
                    <label class="control-label" for="exampleInputEmail">Length*</label>
                    <div class="input-group ">
                        <span class="input-group-addon addon-red"><i class="fa fa-arrows-h"></i></span>
                        <input type="text"  maxlength="8" name="length" id="length" value="" class="form-control" onblur="getAreaVal();">
                    </div>
                </div>
                </div>
                <div class="row">
                <div class="form-group col-sm-6">
                    <label class="control-label" for="exampleInputEmail">Width*</label>
                    <div class="input-group ">
                        <span class="input-group-addon addon-red"><i class="fa fa-codepen"></i></span>
                        <input type="text"  maxlength="8" name="width" id="width" value="" class="form-control" onblur="getAreaVal();">
                    </div>
                </div>
                @endif
                <div class="form-group col-sm-6">
                    <label class="control-label" for="exampleInputEmail">UOM*</label>
                    <div class="input-group ">
                        <span class="input-group-addon addon-red"><i class="fa fa-puzzle-piece"></i></span>
                        <select name="uom_id" id="uom_id" class="form-control">
                          @foreach($dimension_uom as $key => $value)
                          <option value="{{ $key }}">{{ $value }}</option>
                         @endforeach
                        </select>
                    </div>
                </div>
                </div>
                <div class="row">
                <div class="form-group col-sm-6">
                    <label class="control-label" for="exampleInputEmail">Area</label>
                    <div class="input-group ">
                        <span class="input-group-addon addon-red"><i class="fa fa-square-o"></i></span>
                        <input type="text" name="area" id="area" value="" readonly class="form-control">
                    </div>
                </div>
            </div>

         <h4><strong>Co-ordinates</strong></h4>
       <div class="row">
         <div class="form-group col-sm-6">
            <label class="control-label" for="exampleInputEmail">X co-ordinate*</label>
            <div class="input-group ">
                <span class="input-group-addon addon-red"><i class="fa fa-arrows-h"></i></span>
                <input type="number" step="any" min="0" name="xco" id="xco" value="" class="form-control">
            </div>
         </div>
         <div class="form-group col-sm-6">
            <label class="control-label" for="exampleInputEmail">Y co-ordinate*</label>
            <div class="input-group ">
                <span class="input-group-addon addon-red"><i class="fa fa-arrows-v"></i></span>
                <input type="number" step="any" min="0" name="yco" id="yco" value="" class="form-control">
            </div>
         </div>
         </div>
         <div class="row">
         <div class="form-group col-sm-6">
            <label class="control-label" for="exampleInputEmail">Z co-ordinate*</label>
            <div class="input-group ">
                <span class="input-group-addon addon-red"><i class="ion-arrow-resize"></i></span>
                <input type="number" step="any" min="0" name="zco" id="zco" value="" class="form-control">
            </div>
         </div>
	  </div>

	 <h4><strong>Address</strong></h4>
    <div class="row">
   	<div class="form-group col-sm-6">
            <label class="control-label" for="exampleInputEmail">Location*</label>
            <div class="input-group ">
                <span class="input-group-addon addon-red"><i class="fa fa-building-o"></i></span>
                <select name="location_id" id="location_id" class="form-control">
                      @foreach($locations as $key => $value)
                      <option value="{{ $key }}">{{ $value }}</option>
                      @endforeach
                    </select>
            </div>
         </div>
    </div>
           
          <div>
          @if($entity_type_id==6003)
           <a class="btn btn-primary" href=" {{ URL::to('entities/copy/' . $entity_type_id.'/'.$parent_entity_id ) }} " id="copyzone">CREATE A COPY OF ZONE</a>
          @endif
          @if($entity_type_id==6004)
           <a class="btn btn-primary" href=" {{ URL::to('entities/rackuse/' . $entity_type_id.'/'.$parent_entity_id.'/'.$org_id ) }} ">USE RACKTYPES</a>
          @endif
          {{ Form::submit('SAVE', array('class' => 'btn btn-primary')) }}
          {{ Form::close() }}

          
@section('style')
    {{HTML::style('css/style.css')}}
@stop
@section('script')
{{HTML::script('scripts/jquery-1.10.2.min.js')}}
{{HTML::script('js/bootstrap.min.js')}}
{{HTML::script('js/bootstrapValidator.js')}}
@stop
<!--  <script src="/scripts/jquery-1.10.2.min.js"></script>
 <script src="/js/bootstrap.min.js" type="text/javascript"></script>
 <script src="/js/bootstrapValidator.js"></script> -->
<script type="text/javascript">
$('#uom_id').change(function(){
  var uom_id = $('#uom_id').val();
  if(uom_id != 0)
  {
    getAreaVal();
  }
});
function getAreaVal()
{
  var length=1; 
  var width=1;
  var depth=1;
  var height=1;
  var area=1;
  var entity_type_id = document.getElementById('entity_type_id').value;
  var UomType = document.getElementById('uom_id').value;
  var warelength = document.getElementById('warelength').value;
  warelength = parseFloat(warelength);
  var warewidth = document.getElementById('warewidth').value;
  warewidth = parseFloat(warewidth);
  var warearea = document.getElementById('warearea').value;
  warearea = parseFloat(warearea);
  var wareheight = document.getElementById('wareheight').value;
  wareheight = parseFloat(wareheight);
  
  var floorsumlength = document.getElementById('floorsumlength').value;
  var floorsumwidth = document.getElementById('floorsumwidth').value;
  var floorsumarea = document.getElementById('floorsumarea').value;
  
  var length_remaining = parseFloat(warelength)-parseFloat(floorsumlength);
  var width_remaining = parseFloat(warewidth)-parseFloat(floorsumwidth);
  var area_remaining = parseFloat(warearea)-parseFloat(floorsumarea);
  
  if(entity_type_id == 6001 || entity_type_id==6004 || entity_type_id==6002 || entity_type_id==6005)
  { 
      //alert('here');
      if(UomType == 12002){
        if(document.getElementById('length').value!='undefined')
          length = document.getElementById('length').value;
        if(document.getElementById('width').value!='undefined')
          width = document.getElementById('width').value;  
        if(document.getElementById('height').value!='undefined')
          height = document.getElementById('height').value;
      }else if(UomType == 12001){//meter to feet conversion
        //alert('yard');
        if(document.getElementById('length').value!='undefined')
          length = Math.round((document.getElementById('length').value / 0.3048),2);
          length = document.getElementById('length').value / 0.3048;
          //console.log('12001===='+length);
        if(document.getElementById('width').value!='undefined')
          width = Math.round((document.getElementById('width').value / 0.3048),2);  
          //console.log('12001===='+width);
        if(document.getElementById('height').value!='undefined')
          height = Math.round((document.getElementById('height').value / 0.3048),2);
          //console.log('12001===='+height);
      }else if(UomType == 12003){//yard to feet conversion
        if(document.getElementById('length').value!='undefined')
          length = Math.round((document.getElementById('length').value /  0.33333),2);
          //console.log('12003===='+length);
        if(document.getElementById('width').value!='undefined')
          width = Math.round((document.getElementById('width').value /  0.33333),2);
          //console.log('12003===='+width);
        if(document.getElementById('height').value!='undefined')
          height = Math.round((document.getElementById('height').value /  0.33333),2);
          //console.log('12003===='+height);
      }  
  }
  if(entity_type_id==6003 || entity_type_id==6007 || entity_type_id==6006)
  { 
/*    if(document.getElementById('width').value!='undefined')
      width = document.getElementById('width').value;
    if(document.getElementById('length').value!='undefined')
      length = document.getElementById('length').value;*/
    //alert('6003 here');
      if(UomType == 12002){
        if(document.getElementById('length').value!='undefined')
          length = document.getElementById('length').value;
        if(document.getElementById('width').value!='undefined')
          width = document.getElementById('width').value;  
      }else if(UomType == 12003){//yard to feet conversion
        if(document.getElementById('length').value!='undefined')
          length = Math.round((document.getElementById('length').value / 0.3048),2);
          //console.log('12003===='+length);
        if(document.getElementById('width').value!='undefined')
          width = Math.round((document.getElementById('width').value / 0.3048),2);  
          //console.log('12003===='+width);
      }else if(UomType == 12001){//meter to feet conversion
        if(document.getElementById('length').value!='undefined')
          length = Math.round((document.getElementById('length').value /  0.33333),2);
        if(document.getElementById('width').value!='undefined')
          width = Math.round((document.getElementById('width').value /  0.33333),2);
      }    
  }
  if(entity_type_id == 6001)
  {
      if(length !='' && width !='' && height !='' ){
        //area = length*width;

        //alert('length ='+ document.getElementById('length').value+'width ='+document.getElementById('width').value);
        area = document.getElementById('length').value * document.getElementById('width').value;
        document.getElementById('area').value = area;  
      }
  }
  else{
    if(length !='' && width !='' && height !='')
    {
      if(UomType == 0)
      {
        return false;
      }else{
        //alert('expected');
        if(length <= warelength && length != 1)
        { //alert('length='+length+'& warelength='+warelength);
          if(width <= warewidth && width != 1)
          {
            if(height <= wareheight)
            {  
              area = parseFloat(document.getElementById('length').value * document.getElementById('width').value);
              areaDIm = parseFloat(length* width);
              document.getElementById('area').value = area; 
              if(area <= warearea && areaDIm <= area_remaining)
              {
                area = parseFloat(document.getElementById('length').value * document.getElementById('width').value);
                document.getElementById('area').value = area;              
              }else{
                //alert('area = '+area+' && warearea = '+warearea+' && area_remaining = '+area_remaining);
                area = parseFloat(document.getElementById('length').value * document.getElementById('width').value);
                document.getElementById('area').value = area;              
                alert('You donot have sufficient area to create this entity.');
                return false;
              }
            }else{
              //alert('height = '+height+' && wareheight = '+wareheight);
              area = parseFloat(document.getElementById('length').value * document.getElementById('width').value);
              document.getElementById('area').value = area;
              alert('The Quantity is exceeding given Height.');
              return false;
            }
          }else{
            area = parseFloat(document.getElementById('length').value * document.getElementById('width').value);
            document.getElementById('area').value = area;
            alert('width='+width+'& warewidth='+warewidth);
            alert('The Quantity is exceeding given Width.');
            return false;
          }
        }else{
          //alert('length='+length+'& warelength='+warelength);
          area = parseFloat(document.getElementById('length').value * document.getElementById('width').value);
          document.getElementById('area').value = area;
          alert('The Quantity is exceeding given Length.');
          return false;
        }   
      }     
    }
  }    
  return true;
}

function getCapacity()
{
  var parent_capacity = document.getElementById('getcapacityparent').value;
  var child_capacity = document.getElementById('getcapacitychild').value;
  //alert('parent_capacity ='+parent_capacity+'&child_capacity ='+child_capacity);
  var capacity_remaining = parseFloat(parent_capacity)-parseFloat(child_capacity);
  var given_capacity = document.getElementById('capacity').value;
  var entity_type_id = document.getElementById('entity_type_id').value;
  var capacity_uom_id = document.getElementById('capacity_uom_id').value;
  given_capacity = parseFloat(given_capacity);
  
  if(entity_type_id == 6002 || entity_type_id==6004 || entity_type_id==6003 || entity_type_id==6005 || entity_type_id==6006 || entity_type_id==6007 || entity_type_id==6008)
  {
      if(capacity_uom_id == 13001){//litre to kg converter
          given_capacity = document.getElementById('capacity').value * 1;
      }else if(capacity_uom_id == 13002){//kg to kg conversion
          given_capacity = document.getElementById('capacity').value;
      }else if(capacity_uom_id == 13003){//grams to kg conversion
          given_capacity = document.getElementById('capacity').value / 1000;
      }else if(capacity_uom_id == 13004){//metric tones to kg conversion
          given_capacity = document.getElementById('capacity').value * 1000;
      }    
    if(given_capacity > capacity_remaining)
    {
      //alert('given_capacity = '+given_capacity+'& capacity_remaining'+capacity_remaining);
      alert('Given capacity exceeds total capacity.');
      return false;
    }
  }
  return true;
}

$(document).ready(function() {
    $('#createEntity').bootstrapValidator({
//        live: 'disabled',
        message: 'This value is not valid',
        feedbackIcons: {
            valid: 'glyphicon glyphicon-ok',
            invalid: 'glyphicon glyphicon-remove',
            validating: 'glyphicon glyphicon-refresh'
        },
        fields: {
          
            entity_name: {
                    validators: {
                      remote: {
                          message : 'Name already exists.Please enter a new name',
                          url: 'entities/validateEntity',
                          type: 'GET',
                          data: {entity_type_id: $('#entity_type_id').val(), parent_entity_id: $('#parent_entity_id').val(), org_id: $('#org_id').val(), ware_id: $('#ware_id').val()},
                          delay: 2000     // Send Ajax request every 2 seconds
                  },
                      regexp: {
                        regexp: /^[a-zA-Z0-9\s]+$/i,
                            message: 'Name can consist of alphabetical characters and spaces only'
                        },
                        notEmpty: {
                            message: 'Name is required.'
                        }
                    }
                },
            capacity_uom_id: {
                    validators: {
                    callback: {
                        message: 'Please choose Capacity UOM',
                        callback: function(value, validator, $field) {
                            var options = $('[id="capacity_uom_id"]').val();
                            return (options != 0);
                        }
                    },                      
                        notEmpty: {
                            message: 'Capacity UOM is required.'
                        }
                    }
                },
              entity_location: {
                    validators: {
                        notEmpty: {
                            message: 'Entity Location is required.'
                        }
                    }
                },
            capacity: {
                    validators: {
                        notEmpty: {
                            message: 'Capacity is required.'
                        }
                    }
                },
            length: {
                    validators: {
                        notEmpty: {
                            message: 'Length is required.'
                        },
                         between: {
                          min: 0.01,
                          max:10000000,
                          message: 'Enter valid Length'
                      }
                    }
                },
                width: {
                    validators: {
                        notEmpty: {
                          message: 'Width is required.'
                        },
                      between: {
                          min: 0.01,
                          max:10000000,
                          message: 'Enter valid width'
                      }
                    }
                },

            height: {
                    validators: {
                        notEmpty: {

                            message: 'Height is required.'
                        },
                         between: {
                          min: 0.01,
                          max:10000000,
                          message: 'Enter valid height'
                    }
                  }
                },
            uom_id: {
                    validators: {
                    callback: {
                        message: 'Please choose UOM',
                        callback: function(value, validator, $field) {
                            var options = $('[id="uom_id"]').val();
                            return (options != 0);
                        }
                    },                       
                        notEmpty: {
                            message: 'UOM is required.'
                        }
                    }
                },
            zone_type_id: {
                    validators: {
                    callback: {
                        message: 'Please choose Entity Type',
                        callback: function(value, validator, $field) {
                            var options = $('[id="zone_type_id"]').val();
                            return (options != "");
                        }
                    }
                    }
                },                
            area: {
                    validators: {
                        notEmpty: {
                            message: 'Area value should be generated.'
                        }
                    }
                },    
            xco: {
                    validators: {
                        notEmpty: {
                            message: 'X co-ordinate is required.'
                        }
                    }
                }, 
            yco: {
                    validators: {
                        notEmpty: {
                            message: 'Y co-ordinate is required.'
                        }
                    }
                },
            zco: {
                    validators: {
                        notEmpty: {
                            message: 'Z co-ordinate is required.'
                        }
                    }
                },
            location_id: {
                    validators: {
                    callback: {
                        message: 'Please choose Location',
                        callback: function(value, validator, $field) {
                            var options = $('[id="location_id"]').val();
                            return (options != 0);
                        }
                    }
                    }
                }                                                                                                                                                                         
        }
    }).on('success.form.bv', function(event) {
        //event.preventDefault();
        var stat1 = getCapacity();
        var stat2 = getAreaVal();
        if(stat1==true && stat2== true)
        {
          return true;
        }else{
          return false;
        }
        
    });
});
</script>


<!-- @section('script')
  <script type="text/javascript">
    $(function(){
      $('form').validate({
        rules:{
          zone_type_id:{
            requiredDropdown:true
          },entity_name:{
            required:true
          },entity_code:{
            required:true
          },capacity:{
            required:true
          },capacity_uom_id:{
            requiredDropdown:true
          },length:{
            required:true
          },width:{
            required:true
          },height:{
            required:true
          },depth:{
            required:true
          },area:{
            required:true
          },uom_id:{
            requiredDropdown:true
          },location_id:{
            requiredDropdown:true
          }
        },messages:{
          zone_type_id:{
            requiredDropdown:'Zone Type is required'
          }, entity_name:{
            required:'Warehouse Name is required'
          },entity_code:{
            required:'Entity Code is required'
          },capacity:{
            required:'Capacity is required'
          },capacity_uom_id:{
            requiredDropdown:'Capacity UOM is required'
          },length:{
            required:'Length is required'
          },width:{
            required:'Width is required'
          },height:{
            required:'Height is required'
          },depth:{
            required:'Depth is required'
          },area:{
            required:'Area is required'
          },uom_id:{
            requiredDropdown:'Dimensions UOM is required'
          },location_id:{
            requiredDropdown:'Location is required'
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
@stop -->
<style type="text/css">
   .has-error .help-block{ position: absolute !impotant; top: 10px !impotant; font-size: 11px !impotant; color:#000 !impotant;}
</style>
@extends('layouts.footer')