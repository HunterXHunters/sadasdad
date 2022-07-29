<link href="/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
<link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css" rel="stylesheet" type="text/css" />
<link href="/css/bootstrapValidator.css" rel="stylesheet" type="text/css" />

{{ Form::open(array('url' => 'pallets/store','id'=>'createPallet')) }}
{{ Form::hidden('_method', 'POST') }}

    <div class="row">
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
     <div class="form-group col-sm-6">
      <label for="exampleInputEmail">Pallet Type*</label>
      <div class="input-group ">
        <span class="input-group-addon addon-red"><i class="fa fa-puzzle-piece"></i></span>
       <select name="pallet_type_id" id="pallet_type_id" class="form-control">
        @foreach($pallettype as $key => $value)
        <option value="{{ $key}}">{{ $value}}</option>
         @endforeach
        </select>
      </div>
    </div>
  </div>
  <div class="row">
     <div class="form-group col-sm-6">
      <label for="exampleInputEmail">Weight*</label>
      <div class="input-group ">
        <span class="input-group-addon addon-red"><i class="fa fa-cubes"></i></span>
        <input type="text"  maxlength="8" id="weight" name="weight"   placeholder="weight" class="form-control">
      </div>
    </div>  
     <div class="form-group col-sm-6">
      <label for="exampleInputEmail">Weight UOM*</label>
      <div class="input-group ">
        <span class="input-group-addon addon-red"><i class="ion-cube"></i></span>
        <select name="weightUOMId" id="weightUOMId" class="form-control">
        @foreach($weights as $key => $value)
        <option value="{{ $key}}">{{ $value}}</option>
         @endforeach
        </select>
      </div>
    </div>
  </div>

   <div class="row">
     <div class="form-group col-sm-6">
      <label for="exampleInputEmail">Dimension UOM*</label>
      <div class="input-group ">
        <span class="input-group-addon addon-red"><i class="ion-arrow-expand"></i></span>
        <select name="dimensionUOMId" id="dimensionUOMId" class="form-control">
        @foreach($dimensions as $key => $value)
        <option value="{{ $key}}">{{ $value}}</option>
         @endforeach
        </select>
      </div>
    </div>  
    <div class="form-group col-sm-6">
      <label for="exampleInputEmail">Height*</label>
      <div class="input-group ">
        <span class="input-group-addon addon-red"><i class="fa fa-arrows-v"></i></span>
        <input type="text"  maxlength="8" id="height" name="height"   placeholder="height" class="form-control"> 
      </div>
    </div>  
  </div>
  
     
   <div class="row">   
     <div class="form-group col-sm-6">
      <label for="exampleInputEmail">Length*</label>
      <div class="input-group ">
        <span class="input-group-addon addon-red"><i class="fa fa-arrows-h"></i></span>
        <input type="text"  maxlength="8" id="length" name="length"   placeholder="length" class="form-control">
      </div>
    </div>
     <div class="form-group col-sm-6">
      <label for="exampleInputEmail">Width*</label>
      <div class="input-group ">
        <span class="input-group-addon addon-red"><i class="fa fa-codepen"></i></span>
        <input type="text"  maxlength="8" id="width" name="width"   placeholder="width" class="form-control"> 
      </div>
    </div>  
  </div> 
  <div class="row">
  <div class="form-group col-sm-6">
                    <label class="control-label" for="exampleInputEmail">Capacity*</label>
                    <div class="input-group">
                        <span class="input-group-addon addon-red"><i class="ion-filing"></i></span>
                        <input type="number" step="any" min="0" id="capacity" name="capacity" value="" class="form-control">
                    </div>                        
                </div>
                
                <div class="form-group col-sm-6">
                    <label class="control-label" for="exampleInputEmail">Capacity UOM*</label>
                     <div class="input-group ">
                        <span class="input-group-addon addon-red"><i class="ion-cube"></i></span>
                        <select name="capacityUOMId" id="capacityUOMId" class="form-control">
                        @foreach($capacity_uom as $key => $value)
                          <option value="{{ $key}}">{{ $value}}</option>
                         @endforeach
                        </select>
                    </div>
                </div>
            </div>
  <div class="row">
     <div class="form-group col-sm-6">
      <label for="exampleInputEmail">No of pallets*</label>
      <div class="input-group ">
        <span class="input-group-addon addon-red"><i class="fa fa-newspaper-o"></i></span>
        <input type="text"  maxlength="8" id="no_of_pallets" name="no_of_pallets"   placeholder="No of pallets" class="form-control"> 
      </div>
    </div>
  </div> 


    {{ Form::submit('Create Pallet!', array('class' => 'btn btn-primary')) }}

{{ Form::close() }}



@section('style')
    {{HTML::style('css/style.css')}}
@stop

<!-- <script src="/scripts/jquery-1.10.2.min.js"></script>
 <script src="/js/bootstrap.min.js" type="text/javascript"></script>
 <script src="/js/bootstrapValidator.js"></script> -->

@section('script')
{{HTML::script('scripts/jquery-1.10.2.min.js')}}
{{HTML::script('js/bootstrap.min.js')}}
{{HTML::script('js/bootstrapValidator.js')}}
@stop
 <script type="text/javascript">
$('#ware_id').change(function(){
var ware_id = $('#ware_id').val();
var url = '/pallets/getOrg/'+ware_id;
var posting =$.get( url);
posting.done(function( data ) {
//console.log(data);
    $('#org_id').val(data);    
  }); 
});
 $(document).ready(function() {
    $('#createPallet').bootstrapValidator({
//        live: 'disabled',
        message: 'This value is not valid',
        feedbackIcons: {
            valid: 'glyphicon glyphicon-ok',
            invalid: 'glyphicon glyphicon-remove',
            validating: 'glyphicon glyphicon-refresh'
        },
        fields: {
          
/*            pallet_name: {
                    validators: {
                     remote: {
                      message : 'Name already exists.Please enter a new name',
                      url: '/pallets/validatepallet',
                      type: 'GET',
                        data: function(validator, $field, value) {
                              return {
                              'ware_id': validator.getFieldElements('ware_id').val(),
                              };
                          },
                      delay: 2000     // Send Ajax request every 2 seconds
                  },
                        notEmpty: {
                            message: 'Pallet Name is required.'
                        }
                    }
                },*/
            no_of_pallets: {
                    validators: {
                        notEmpty: {
                            message: 'No of pallets is required.'
                        },
                         between: {
                          min: 1,
                          max:10000000,
                          message: 'Enter valid Number'
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
            pallet_type_id: {
                    validators: {
                    callback: {
                        message: 'Please choose pallet type',
                        callback: function(value, validator, $field) {
                            var options = $('[id="pallet_type_id"]').val();
                            return (options != 0);
                        }
                    },                      
                        notEmpty: {
                            message: 'Pallet Type is required.'
                        }
                    }
                },
                weightUOMId: {
                    validators: {
                    callback: {
                        message: 'Please choose weight UOM',
                        callback: function(value, validator, $field) {
                            var options = $('[id="weightUOMId"]').val();
                            return (options != 0);
                        }
                    },                      
                        notEmpty: {
                            message: 'Weight UOM is required.'
                        }
                    }
                },

                
            weight: {
                    validators: {
                        notEmpty: {
                            message: 'Weight is required.'
                        },
                         between: {
                          min: 0.01,
                          max:10000000,
                          message: 'Enter valid weight'
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
            capacity: {
                    validators: {
                        notEmpty: {
                            message: 'Capacity is required.'
                        }
                   }
                },
            capacityUOMId: {
                    validators: {
                    callback: {
                        message: 'Please choose Capacity UOM',
                        callback: function(value, validator, $field) {
                            var options = $('[id="capacityUOMId"]').val();
                            return (options != 0);
                        }
                    },                      
                        notEmpty: {
                            message: 'Capacity UOM is required.'
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
            dimensionUOMId: {
                    validators: {
                    callback: {
                        message: 'Please choose UOM',
                        callback: function(value, validator, $field) {
                            var options = $('[id="dimensionUOMId"]').val();
                            return (options != 0);
                        }
                    },                       
                        notEmpty: {
                            message: 'Dimension UOM is required.'
                        }
                    }
                }                                                                                                                                                                                    
        }
    })
});
/*$('#ware_id').change(function(){
$('#createPallet').data('bootstrapValidator').updateStatus('pallet_name', 'NOT_VALIDATED').validateField('pallet_name');
});*/
</script>

@extends('layouts.footer')



