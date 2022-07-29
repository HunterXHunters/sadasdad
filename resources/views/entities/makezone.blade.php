<!--<h1>Edit {{ $entities->entity_name }}</h1>-->

<!-- if there are creation errors, they will show here -->
{{ HTML::ul($errors->all()) }}

{{ Form::open(array('url' => 'entities/store','id' => 'makezonecopy')) }}
{{ Form::hidden('_method', 'POST') }}
<input type="hidden" id="entity_type_id" name="entity_type_id" value="{{$entities->entity_type_id}}" />
<input type="hidden" id="parent_entity_id" name="parent_entity_id" value="{{$parent_entity_id}}" />
<input type="hidden" name="org_id" id="org_id" value="{{ $entities->org_id }}" class="input1">
<input type="hidden" name="children_parent_entity_id" id="children_parent_entity_id" value="{{$children_parent_entity_id}}" class="input1">
<input type="hidden" id="ware_id" name="ware_id" value="{{$entities->ware_id}}" />
<input type="hidden" id="getcapacityparent" name="getcapacityparent" value="{{ $parent_capacity}}" />
<input type="hidden" id="getcapacitychild" name="getcapacitychild" value="{{ $child_capacity}}" />
<input type="hidden" id="warelength" name="warelength" value="{{ $warelength}}" />
<input type="hidden" id="warewidth" name="warewidth" value="{{ $warewidth}}" />
<input type="hidden" id="zonesumlength" name="zonesumlength" value="{{ $zonesumlength}}" />
<input type="hidden" id="zonesumwidth" name="zonesumwidth" value="{{ $zonesumwidth}}" />
<h4><strong>Zone Details</strong></h4>
<div class="row">
    <div class="form-group col-sm-6">
        <label for="exampleInputEmail">{{$entity_name}}* </label>
        <div class="input-group ">
            <span class="input-group-addon addon-red"><i class="fa fa-cube"></i></span>
            <input type="text" name="entity_name" id="entity_name" value="" class="form-control">
        </div>
    </div>  
    <!-- <div class="form-group col-sm-6">
        <label for="exampleInputEmail">{{$entity_code}}</label>
        <div class="input-group ">
            <span class="input-group-addon addon-red"><i class="fa fa-keyboard-o"></i></span>
            <input type="text" name="entity_code" id="entity_code" value="{{$entity_code_val}}" class="form-control" readonly>
        </div>
    </div> -->
     <div class="form-group col-sm-6">
      <label class="control-label" for="exampleInputEmail">Entity Location*</label>
      <div class="input-group">
      <span class="input-group-addon addon-red"><i class="fa fa-map-marker"></i></span>
      <input type="text" id="entity_location" name="entity_location" value="" class="form-control">
      </div>                        
    </div> 
    <div class="form-group col-sm-6">
        <label for="exampleInputEmail">Capacity*</label>
        <div class="input-group ">
            <span class="input-group-addon addon-red"><i class="fa fa-keyboard-o"></i></span>
            <input type="number" step="any" id="capacity" min="0" name="capacity" value="{{$entities->capacity}}" onblur="getCapacity();" class="form-control">
        </div>
    </div> 
    <div class="form-group col-sm-6">
        <label for="exampleInputEmail">Capacity UOM*</label>
        <div class="input-group ">
            <span class="input-group-addon addon-red"><i class="fa fa-keyboard-o"></i></span>
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
	<div class="form-group col-sm-6">
        <label for="exampleInputEmail">Length*</label>
        <div class="input-group ">
            <span class="input-group-addon addon-red"><i class="fa fa-keyboard-o"></i></span>
            <input type="text" name="length" id="length" value="{{$dimensions->length}}" class="form-control" onblur="getAreaVal();">
        </div>
    </div>
    <div class="form-group col-sm-6">
        <label for="exampleInputEmail">Width*</label>
        <div class="input-group ">
            <span class="input-group-addon addon-red"><i class="fa fa-keyboard-o"></i></span>
            <input type="text" name="width" id="width" value="{{$dimensions->width}}" class="form-control" onblur="getAreaVal();">
        </div>
    </div> 
    <div class="form-group col-sm-6">
        <label for="exampleInputEmail">UOM*</label>
        <div class="input-group ">
            <span class="input-group-addon addon-red"><i class="fa fa-keyboard-o"></i></span>
            <select name="uom_id" id="uom_id" class="form-control">
            @foreach($dimension_uom as $key => $value)
            @if($key==$dimensions->uom_id)
                <option value="{{ $key}}" selected="selected">{{ $value}}</option>
            @else
                <option value="{{ $key}}">{{ $value}}</option>
            @endif        
           @endforeach
          </select>
        </div>
    </div> 
    <div class="form-group col-sm-6">
        <label for="exampleInputEmail">Area*</label>
        <div class="input-group ">
            <span class="input-group-addon addon-red"><i class="fa fa-keyboard-o"></i></span>
            <input type="text" name="area" id="area" value="{{$dimensions->area}}" readonly class="form-control">
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
        <label for="exampleInputEmail">Location*</label>
        <div class="input-group ">
            <span class="input-group-addon addon-red"><i class="fa fa-keyboard-o"></i></span>
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
<div>
{{ Form::submit('SAVE', array('class' => 'btn btn-primary')) }}
{{ Form::close() }}
</div>

<script type="text/javascript">
  $(document).ready(function() {
    $('#makezonecopy').bootstrapValidator({
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
                      url: '/entities/validateEntity',
                      type: 'GET',
                      data: {entity_type_id: $('#entity_type_id').val(), parent_entity_id: $('#parent_entity_id').val(), org_id: $('#org_id').val()},
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
                    validators:{
                      callback:{
                          message: 'Please choose Capacity UOM',
                          callback: function(value, validator, $field) {
                              var options = $('[id="capacity_uom_id"]').val();
                              return (options != 0);
                          }
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
            uom_id: {
                validators: {
                  callback: {
                      message: 'Please choose UOM',
                      callback: function(value, validator, $field) {
                          var options = $('[id="uom_id"]').val();
                          return (options != 0);
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
        if(stat1 == true && stat2 == true)
        {
          return true;
        }else{
          return false;
        }
    });
});
</script>
<script type="text/javascript">
  function getCapacity()
  {
    var parent_capacity = document.getElementById('getcapacityparent').value;
    var child_capacity = document.getElementById('getcapacitychild').value;
    var capacity_remaining = parseInt(parent_capacity)-parseInt(child_capacity);
    var given_capacity = document.getElementById('capacity').value;
    given_capacity = parseInt(given_capacity);
    
    if(given_capacity > capacity_remaining)
      {
        alert('Given capacity exceeds total capacity.');
        return false;
      }
    return true;
  }
</script>
<script type="text/javascript">
function getAreaVal()
{
    var length=1; 
    var width=1;
    var area=1;
    
    var entity_type_id = document.getElementById('entity_type_id').value;

    var warelength = document.getElementById('warelength').value;
    var warewidth = document.getElementById('warewidth').value;
    
    var zonesumlength = document.getElementById('zonesumlength').value;
    var zonesumwidth = document.getElementById('zonesumwidth').value;
    
    var length_remaining = parseInt(warelength)-parseInt(zonesumlength);
    var width_remaining = parseInt(warewidth)-parseInt(zonesumwidth);
    
    if(entity_type_id==6003)
    { 
      if(document.getElementById('length').value!='undefined')
        length = document.getElementById('length').value;
      if(document.getElementById('width').value!='undefined')
        width = document.getElementById('width').value;    
    }

    if(length !='' && width !='')
    {
      if(length <= length_remaining)
      {
        if(width <= width_remaining)
        {
            area = length*width;
            document.getElementById('area').value = area;
        }else{
          area = length*width;
          document.getElementById('area').value = area;
          alert('The Quantity is exceeding given Width.');
          return false;
        }
      }else{
        area = length*width;
        document.getElementById('area').value = area;
        alert('The Quantity is exceeding given Length.');
        return false;
      }
    }
    return true;
}

</script>
