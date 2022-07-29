<link href="/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
<link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css" rel="stylesheet" type="text/css" />
<link href="/css/bootstrapValidator.css" rel="stylesheet" type="text/css" />
{{ Form::open(array('url' => 'entities/update/'.$entities->id, 'method'=>'POST','id'=>'form-editEntity')) }}
{{ Form::hidden('_method', 'PUT') }}
<input type="hidden" id="entity_type_id" name="entity_type_id" value="{{$entity_type_id}}" />
<input type="hidden" id="entity_id" name="entity_id" value="{{$entities->id}}" />
<input type="hidden" id="parent_entity_id" name="parent_entity_id" value="{{$entities->parent_entity_id}}" />
<input type="hidden" name="org_id" id="org_id" value="{{ $entities->org_id }}" class="form-control">
<input type="hidden" name="ware_id" id="ware_id" value="{{ $ware_id }}" class="form-control">
<input type="hidden" id="warelength" name="warelength" value="{{ $warelength}}" />
<input type="hidden" id="warewidth" name="warewidth" value="{{ $warewidth}}" />
<input type="hidden" id="wareheight" name="wareheight" value="{{ $wareheight}}" />
<!--warearea-->
<input type="hidden" id="warearea" name="warearea" value="{{$warearea}}" /> 
<!--warearea-->  
<!--warearea utilised--> 
<input type="hidden" id="floorsumarea" name="floorsumarea" value="{{$floorsumarea}}" />
<!--warearea utilised-->  
<input type="hidden" id="floorsumlength" name="floorsumlength" value="{{ $floorsumlength}}" />
<input type="hidden" id="floorsumwidth" name="floorsumwidth" value="{{ $floorsumwidth}}" />
<input type="hidden" id="getcapacityparent" name="getcapacityparent" value="{{ $parent_capacity}}" />
<input type="hidden" id="getcapacitychild" name="getcapacitychild" value="{{ $child_capacity}}" />

<input type="hidden" id="childcap" name="childcap" value="{{ $childCap}}" />

           
<h4><strong>Entity Details</strong></h4>
 <div class="row">
  @if($entity_type_id==6003)
     <div class="form-group col-sm-6">
        <label class="control-label" for="exampleInputEmail">Select Zone Type</label>
         <div class="input-group ">
            <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
            <select name="zone_type_id" id="zone_type_id" class="form-control">
              <option value="">Select Zone </option>
            @if($entity_type_id==6003)
                <option value="1" selected="selected">Zone</option>
              @else
                <option value="1">Zone</option>
              @endif
              @if($entity_type_id==6006)
                <option value="2" selected="selected">Open Type Zone</option>
              @else
                <option value="2">Open Type Zone</option>
              @endif
              @if($entity_type_id==6007)
                <option value="3" selected="selected">Put Away Zone</option>
              @else
                <option value="3">Put Away Zone</option>
              @endif
            </select>
        </div>
    </div>
     @endif
    <div class="form-group col-sm-6">
        <label class="control-label" for="exampleInputEmail">{{$entity_name}}*</label>
        <div class="input-group">
            <span class="input-group-addon addon-red"><i class="fa fa-newspaper-o"></i></span>
            <input type="text" name="entity_name" id="entity_name" value="{{$entities->entity_name}}"  class="form-control" >
        </div>                        
    </div>
    <div class="form-group col-sm-6">
        <label class="control-label" for="exampleInputEmail">{{$entity_code}}*</label>
        <div class="input-group">
            <span class="input-group-addon addon-red"><i class="ion-ios-barcode-outline"></i></span>
            <input type="text" name="entity_code" id="entity_code" value="{{$entity_code_val}}" class="form-control" disabled="disabled">
        </div>                        
    </div>
     <div class="form-group col-sm-6">
            <label class="control-label" for="exampleInputEmail">Entity Location*</label>
            <div class="input-group">
                <span class="input-group-addon addon-red"><i class="fa fa-map-marker"></i></span>
                <input type="text" id="entity_location" name="entity_location" value="{{$entities->entity_location}}" class="form-control">
            </div>                        
        </div> 
    <div style="clear:both"></div>
    <div class="form-group col-sm-6">
        <label class="control-label" for="exampleInputEmail">Capacity*</label>
        <div class="input-group">
            <span class="input-group-addon addon-red"><i class="ion-filing"></i></span>
            <input type="number" step="any" min="0" id="capacity" name="capacity" value="{{$entities->capacity}}" onblur="getCapacity();" class="form-control">
        </div>                        
    </div>
    <div class="form-group col-sm-6">
        <label class="control-label" for="exampleInputEmail">Capacity UOM*</label>
        <div class="input-group">
            <span class="input-group-addon addon-red"><i class="ion-cube"></i></span>
            <select name="capacity_uom_id" id="capacity_uom_id" class="form-control">
          @foreach($capacity_uom as $key => $value)
            @if($key==$entities->capacity_uom_id)
                <option value="{{ $key}}" selected="selected">{{ $value}}</option>
            @else
                <option value="{{ $key}}">{{ $value}}</option>
            @endif
           @endforeach
          </select>
        </div>                        
    </div>
  </div>
<h4><strong>Dimensions</strong></h4>
 <div class="row">
  @if($entity_type_id==6001 || $entity_type_id==6004 || $entity_type_id==6005 || $entity_type_id==6002)
 	<div class="form-group col-sm-6">
        <label class="control-label" for="exampleInputEmail">Length*</label>
        <div class="input-group">
            <span class="input-group-addon addon-red"><i class="fa fa-arrows-h"></i></span>
            <input type="text" maxlength="8" name="length" id="length" value="{{$dimensions['length']}}" class="form-control" onblur="getAreaVal();">
        </div>                        
    </div>
    <div class="form-group col-sm-6">
        <label class="control-label" for="exampleInputEmail">Width*</label>
        <div class="input-group">
            <span class="input-group-addon addon-red"><i class="fa fa-codepen"></i></span>
            <input type="text" maxlength="8" name="width" id="width" value="{{$dimensions['width']}}" class="form-control" onblur="getAreaVal();">
        </div>                        
    </div>
    <div class="form-group col-sm-6">
        <label class="control-label" for="exampleInputEmail">Height*</label>
        <div class="input-group">
            <span class="input-group-addon addon-red"><i class="fa fa-arrows-v"></i></span>
            <input type="text"  maxlength="8" name="height" id="height" value="{{$dimensions['height']}}" class="form-control" onblur="getAreaVal();">
        </div>                        
    </div>
    </div>
    <disv class="row">
    @endif
    @if($entity_type_id==6003 || $entity_type_id==6007 || $entity_type_id==6006)
    <div class="form-group col-sm-6">
        <label class="control-label" for="exampleInputEmail">Length*</label>
        <div class="input-group">
            <span class="input-group-addon addon-red"><i class="fa fa-arrows-h"></i></span>
            <input type="text" maxlength="8" name="length" id="length" value="{{$dimensions['length']}}" class="form-control" onblur="getAreaVal();">
        </div>                        
    </div>
    <div class="form-group col-sm-6">
        <label class="control-label" for="exampleInputEmail">Width*</label>
        <div class="input-group">
            <span class="input-group-addon addon-red"><i class="fa fa-codepen"></i></span>
            <input type="text" maxlength="8" name="width" id="width" value="{{$dimensions['width']}}" class="form-control" onblur="getAreaVal();">
        </div>                        
    </div>      
    @endif
    <div class="form-group col-sm-6">
        <label class="control-label" for="exampleInputEmail">UOM*</label>
        <div class="input-group">
            <span class="input-group-addon addon-red"><i class="fa fa-puzzle-piece"></i></span>
            <select name="uom_id" id="uom_id" class="form-control">
            @foreach($dimension_uom as $key => $value)
            @if($key==$dimensions['uom_id'])
                <option value="{{ $key}}" selected="selected">{{ $value}}</option>
            @else
                <option value="{{ $key}}">{{ $value}}</option>
            @endif        
           @endforeach
          </select>
        </div>                        
    </div>
    
    <div class="form-group col-sm-6">
        <label class="control-label" for="exampleInputEmail">Area</label>
        <div class="input-group">
            <span class="input-group-addon addon-red"><i class="fa fa-square-o"></i></span>
            <input type="text" name="area" id="area" value="{{$dimensions['area']}}" readonly class="form-control">
        </div>                        
    </div>  
 </div>

       
<h4><strong>Co-ordinates</strong></h4>
<div class="row">

	<div class="form-group col-sm-6">
        <label class="control-label" for="exampleInputEmail">X Co-ordinate*</label>
        <div class="input-group">
            <span class="input-group-addon addon-red"><i class="fa fa-arrows-h"></i></span>
            <input type="number" step="any" min="0" name="xco" id="xco" value="{{$entities->xco}}" class="form-control">
        </div>                        
    </div>
    <div class="form-group col-sm-6">
        <label class="control-label" for="exampleInputEmail">Y Co-ordinate*</label>
        <div class="input-group">
            <span class="input-group-addon addon-red"><i class="fa fa-arrows-v"></i></span>
            <input type="number" step="any" min="0" name="yco" id="yco" value="{{$entities->yco}}" class="form-control">
        </div>                        
    </div>
    </div>
    <div class="row">            
     <div class="form-group col-sm-6">
        <label class="control-label" for="exampleInputEmail">Z Co-ordinate*</label>
        <div class="input-group">
            <span class="input-group-addon addon-red"><i class="ion-arrow-resize"></i></span>
            <input type="number" step="any" min="0" name="zco" id="zco" value="{{$entities->zco}}" class="form-control" >
        </div>                        
    </div>

</div>
<h4><strong>Address</strong></h4>
<div class="row">
	<div class="form-group col-sm-6">
        <label class="control-label" for="exampleInputEmail">Location*</label>
        <div class="input-group">
            <span class="input-group-addon addon-red"><i class="fa fa-building-o"></i></span>
            <select name="location_id" id="location_id" class="form-control">
            @foreach($locations as $key => $value)
            @if($key==$entities->location_id)
                <option value="{{ $key}}" selected="selected">{{ $value}}</option>
            @else
                <option value="{{ $key}}">{{ $value}}</option>
            @endif
            @endforeach
          </select>
        </div>                        
    </div>
</div>
{{ Form::submit('UPDATE', array('class' => 'btn btn-primary', 'id' => 'entity_update_button')) }}
{{ Form::close() }}

@section('style')
    {{HTML::style('css/style.css')}}
@stop
@section('script')
{{HTML::script('scripts/jquery-1.10.2.min.js')}}
{{HTML::script('js/bootstrap.min.js')}}
{{HTML::script('js/bootstrapValidator.js')}}
@stop
<!-- <script src="/scripts/jquery-1.10.2.min.js"></script>
<script src="/js/bootstrap.min.js" type="text/javascript"></script>
<script src="/js/bootstrapValidator.js" type="text/javascript"></script>  -->          
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
  var wareheight = document.getElementById('wareheight').value;
  wareheight = parseFloat(wareheight);
  var warearea = document.getElementById('warearea').value;
  warearea = parseFloat(warearea);  
  
  var floorsumlength = document.getElementById('floorsumlength').value;
  var floorsumwidth = document.getElementById('floorsumwidth').value;
  var floorsumarea = document.getElementById('floorsumarea').value;
  
  var length_remaining = parseFloat(warelength)-parseFloat(floorsumlength);
  var width_remaining = parseFloat(warewidth)-parseFloat(floorsumwidth);
  var area_remaining = parseFloat(warearea)-parseFloat(floorsumarea);

  if(entity_type_id==6001 || entity_type_id==6004 || entity_type_id==6002 || entity_type_id==6005)
  { 
/*    if(document.getElementById('length').value!='undefined')
      length = document.getElementById('length').value;
    if(document.getElementById('width').value!='undefined')
      width = document.getElementById('width').value;  
      if(document.getElementById('height').value!='undefined')
      height = document.getElementById('height').value;  */
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
        //area = Math.round(parseFloat(document.getElementById('length').value * document.getElementById('width').value),2);
        area = (parseFloat(document.getElementById('length').value * document.getElementById('width').value)).toFixed(2);
        document.getElementById('area').value = area;  
      }
  }else{
    if(length !='' && width !='' && height !='' )
    {
      if(UomType == 0)
      {
        return false;
      }else{      
        if(length <= warelength && length != 1)
        {
          if(width <= warewidth && width != 1)
          {
            if(height <= wareheight)
            {  
              //area = length*width;
              area = (parseFloat(document.getElementById('length').value * document.getElementById('width').value)).toFixed(2);
              //document.getElementById('area').value = area;
                areaDIm = parseFloat((parseFloat(length)*parseFloat(width)).toFixed(2));
                document.getElementById('area').value = area; 
                if(area <= warearea && areaDIm <= area_remaining)
                {
                  //area = Math.round(parseFloat(document.getElementById('length').value * document.getElementById('width').value),2);
                  area = (parseFloat(document.getElementById('length').value * document.getElementById('width').value)).toFixed(2);
                  document.getElementById('area').value = area;              
                }else{
                  //alert('area = '+area+' && warearea = '+warearea+' && area_remaining = '+area_remaining+' && areaDIm = '+areaDIm);
                  //area = Math.round(parseFloat(document.getElementById('length').value * document.getElementById('width').value),2);
                  area = (parseFloat(document.getElementById('length').value * document.getElementById('width').value)).toFixed(2);
                  document.getElementById('area').value = area;              
                  alert('You donot have sufficient area to create this entity.');
                  return false;
                }            
            }else{
              alert('The Quantity is exceeding given Height.');
              return false;
            }
          }else{
            alert('The Quantity is exceeding given Width.');
            return false;
          }
        }else{
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
  var capacity_remaining = parseFloat(parent_capacity)-parseFloat(child_capacity);
  var given_capacity = document.getElementById('capacity').value;
/*    alert('parent_capacity ======= '+parent_capacity+' && child_capacity ==== '+child_capacity+' && given_capacity ==== '+given_capacity);
    n_capacity = document.getElementById('capacity').value;
    console.log('parent_capacity ======= '+parent_capacity+' && child_capacity ==== '+child_capacity+' && given_capacity ==== '+given_capacity+' && capacity_remaining ==== '+capacity_remaining);
    return false;*/
  var entity_type_id = document.getElementById('entity_type_id').value;
  var childCap = parseFloat(document.getElementById('childcap').value);
  var capacity_uom_id = document.getElementById('capacity_uom_id').value;
  given_capacity = parseFloat(given_capacity);
/*  alert('given_capacity ===== '+given_capacity+' && childCap === '+childCap);*/
  if(given_capacity < childCap){
/*      alert('given_capacity ===== '+given_capacity+' && childCap === '+childCap);*/
      alert('Given Capacity is less than Child Capacities.');
      //$('#form-editEntity').data('bootstrapValidator').resetForm();
      return false;
    }
/*  return false;*/
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
      alert('Given capacity exceeds total capacity.');
      //$('#form-editEntity').data('bootstrapValidator').resetForm();
      return false;
    }
  }
  return true;//result
}

</script>


<script type="text/javascript">
$(document).ready(function() {
    $('#form-editEntity').bootstrapValidator({
        //live: 'disabled',
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
                      data: {id: $('#entity_id').val(), entity_type_id: $('#entity_type_id').val(), parent_entity_id: $('#parent_entity_id').val(), org_id: $('#org_id').val(), ware_id: $('#ware_id').val()},
                      delay: 2000     // Send Ajax request every 2 seconds
                  },
                      regexp: {
                        regexp: /^[a-zA-Z0-9\s]+$/i,
                            message: 'Entity Name can consist of alphabetical characters and spaces only'
                        },
                        notEmpty: {
                            message: 'Entity Name is required.'
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
