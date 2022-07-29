@extends('layouts.default')
@extends('layouts.header')
@extends('layouts.sideview')
@section('content')
<!-- <link href="/css/bootstrap-datetimepicker.min.css" rel="stylesheet" media="screen"> -->
<style type="text/css">
.form-horizontal .form-group {
    margin-left: -0px !important;
    margin-right: -0px !important;
}
.checkbox input[type="checkbox"], .checkbox-inline input[type="checkbox"], .radio input[type="radio"], .radio-inline input[type="radio"]
{margin-left: 0px !important;}
</style>

   
<div class="box">
  <div class="box-header">
    <h3 class="box-title"><strong>Edit </strong> eSeal Plan</h3>    
  </div>
   
   <div class="col-sm-12">
     <div class="tile-body nopadding">          
                <!-- tile -->
<section class="tile">

{{Form::open(array('url'=>'pricemaster/update/'.$compt->id,'method'=>'put','id'=>'form-pricemaster'))}}

                  <!-- tile body -->
  <div class="tile-body form-horizontal">
    <form id="basicvalidations" parsley-validate="" role="form" class="form-horizontal">

     <div class="row">
        <div class="form-group col-sm-6">
          <label for="exampleInputEmail">Customer Type*</label>
          <div class="input-group ">
            <span class="input-group-addon addon-red"><i class="ion-ios-person"></i></span>
            <select name="custtypeid" id="custtypeid"  required class="form-control">
            @foreach($custtype as $custtypes)  
              <option value="{{$custtypes->cust_type_id}}" @if($custtypes->cust_type_id==$compt->customer_type_lookup_id) selected="selected" @endif>{{$custtypes->cust_type}}</option>
            @endforeach
           </select>
          </div>
        </div>


        <div class="form-group col-sm-6">
          <label for="exampleInputEmail">Product Type*</label>
          <div class="input-group ">
            <span class="input-group-addon addon-red"><i class="ion-bag"></i></span>
            <select name="prodid" id="prodid"  required class="form-control">
                <option value=""> select </option>
                @foreach($custtypeprod as $custtypeprods) 
                <option value="{{$custtypeprods->product_lookup_id}}" data-mId="{{$custtypeprods->customer_type_lookup_id}}" @if($custtypeprods->product_lookup_id==$compt->product_lookup_id) selected="selected" @endif>{{$custtypeprods->prodtype}}</option>
                @endforeach
           </select>
          </div>
        </div>
    </div> 

     <div class="row">
        <div class="form-group col-sm-6">
          <label for="exampleInputEmail">Component Type*</label>
          <div class="input-group ">
            <span class="input-group-addon addon-red"><i class="ion-more"></i></span>
            <select name="comptypeid" id="comptypeid"  required class="form-control">
                @foreach($comptype as $comptypes) 
                <option value="{{$comptypes->comp_type_id}}" @if($comptypes->comp_type_id==$compt->component_type_lookup_id) selected="selected" @endif>{{$comptypes->comp_type}}</option> 
                 @endforeach
           </select>
          </div>
        </div>


        <div class="form-group col-sm-6">
          <label for="exampleInputEmail">Name*</label>
          <div class="input-group ">
            <span class="input-group-addon addon-red"><i class="ion-ios-person"></i> </span>
            <input type="text"  id="pname" name="pname" value="{{$compt->name}}"  class="form-control" required>
          </div>
        </div>
    </div>

    <div class="row">
        <div class="form-group col-sm-6">
          <label for="exampleInputEmail">Description*</label>
          <div class="input-group ">
            <span class="input-group-addon addon-red"><i class="ion-compose"></i></span>
            <input type="text"  id="description" name="description" value="{{$compt->description}}" class="form-control" required>
          </div>
        </div>


        <div class="form-group col-sm-6">
          <label for="exampleInputEmail">Price*</label>
          <div class="input-group">
            <span class="input-group-btn">
        <select name="currency_id" id="currency_id"  required class="btn">
                @foreach($curr as $curr)  
                <option value="{{$curr->currency_id}}" @if($curr->currency_id==$compt->currency_id) selected="selected" @endif>{{$curr->code}}</option>
                 @endforeach
        </select>
      </span>
    <input class="form-control" type="number" min=0 step="any" id="price" name="price" value="{{$compt->price}}">
        </div>
    </div>
  </div>





    <div class="row">
        <div class="form-group col-sm-6">
          <label for="exampleInputEmail">Subscription Mode*</label>
          <div class="input-group ">
            <span class="input-group-addon addon-red"><i class="ion-calendar"></i></span>
            <select name="subscription_mode" id="subscription_mode" selected="selected" required class="form-control"> 
               <option value="Yearly"@if('Yearly'==$compt->subscription_mode) selected="selected" @endif>Yearly</option>
               <option value="Half Yearly"@if('Half Yearly'==$compt->subscription_mode) selected="selected" @endif>Half Yearly</option>
               <option value="Quarterly"@if('Quarterly'==$compt->subscription_mode) selected="selected" @endif>Quarterly</option>
               <option value="Monthly"@if('Monthly'==$compt->subscription_mode) selected="selected" @endif >Monthly</option>
               <option value="Daily"@if('Daily'==$compt->subscription_mode) selected="selected" @endif>Daily</option>
               <option value="Per Item"@if('Per Item'==$compt->subscription_mode) selected="selected" @endif>Per Item</option>
            </select>
          </div>
        </div>


        <div class="form-group col-sm-6">
          <label for="exampleInputEmail">Min. Subscription*</label>
          <div class="input-group ">
            <span class="input-group-addon addon-red"><i class="ion-ios-calendar-outline"></i></span>
            <input type="number" min=0  id="min_subscription" name="min_subscription"   value="{{$compt->min_subscription}}" class="form-control" required>
          </div>
        </div>
    </div>   

    <div class="row">
      <div class="form-group col-sm-6">
          <label for="Agreed Date From">Valid From*</label>
            <div class="input-group input-append date" id="dateRangePickerFrom">
                <span class="input-group-addon addon-red"><span class="glyphicon glyphicon-calendar"></span></span>
                <input type="text" class="form-control" name="dtp_input1" id="dtp_input1" readonly="true" value="{{$compt->valid_from}}" />
            </div>
      </div>  
      <div class="form-group col-sm-6">
          <label for="Agreed Date To">Valid To*</label>
            <div class="input-group input-append date" id="dateRangePickerTo">
              <span class="input-group-addon addon-red"><span class="glyphicon glyphicon-calendar"></span></span>
              <input type="text" class="form-control" name="dtp_input2" id="dtp_input2" readonly="true" value="{{$compt->valid_upto}}" /> 
            </div>
      </div>  
    </div>

   
    
    <div class="row">
        <div class="form-group col-sm-6">
          <label for="exampleInputEmail">Tax Class*</label>
          <div class="input-group ">
            <span class="input-group-addon addon-red"><i class="ion-cash"></i></span>
            <select name="tax_class_id" id="tax_class_id"  required class="form-control">
                @foreach($taxclass as $taxclass)  
                <option value="{{$taxclass->taxclass_id}}" @if($taxclass->taxclass_id==$compt->tax_class_id) selected="selected" @endif>{{$taxclass->taxclass}}</option>
                 @endforeach
           </select>

        </div>
      </div>
      <div class="form-group col-sm-6">
            <label for="exampleInputEmail">Status*</label>
            <div class="input-group">
              <div class="checkbox">
                  <input type="checkbox" id="opt01" id="is_active" name="is_active" value="{{$compt->is_active}}" @if($compt->is_active==1) checked="checked" @endif>
                <label for="opt01">Is Active</label>
              </div>
            </div>                        
        </div>      
    </div>
                      


    
  <br>            
  <div class="box-header">
    <h3 class="box-title"><strong>Modules* </strong></h3>    
  </div>  

<div class="form-group table-responsive">
<table id="mytable" class="table table-bordred table-striped" data-click-to-select="true">
    <thead>
        <th></th>
        <!-- <th>ModuleId</th> -->
        <th>Module Name</th>
        <th>Users</th>  
    </thead>
   <tbody>
    @foreach ($modules as $index=>$mod)
    <tr>  
    <td> 
      <input type="checkbox"  class="checkthis module_id" id="module_id[<?=$index?>]" name="module_id[<?=$index?>]" value="{{$mod->module_id}}" 
      @foreach ($modu as $modus)
      @if($mod->module_id==$modus->module_id)
      checked="checked"
      @endif
      @endforeach/>
      </td>
<!--       <td>{{ $mod->module_id }}</td> -->
      <td>{{ $mod->name }}</td>
      <td><input type="number"  min=0 id="users[<?=$index?>]" name="users[<?=$index?>]"  class="form-control"@foreach ($modu as $modus)
      @if($mod->module_id==$modus->module_id)
      value="{{$modus->users}}"
      @endif
      @endforeach/></td>
    </tr>
    @endforeach
    </tbody>
</table>
</div>

    <div class="row">
     <div class="form-group form-footer">
       <div class="col-sm-offset-3 col-sm-8">
        <button class="btn btn-primary" type="submit">Submit</button>
        <button class="btn btn-primary" type="cancel" onclick="cancel()">Cancel</button>
       </div>
     </div>
    </div>
      {{Form::close()}}     
  </form>
</div>
                  <!-- /tile body -->               
</section>
</div> 
</div> 
</div>

@section('style')
    {{HTML::style('jqwidgets/styles/jqx.base.css')}}
    {{HTML::style('css/datepicker.min.css')}} 
@stop

@section('script')
    {{HTML::script('js/plugins/bootstrap-select//bootstrap-datepicker.min.js')}}
    {{HTML::script('scripts/demos.js')}}     
@stop

<script type="text/javascript" src="/js/bootstrap-datetimepicker.js" charset="UTF-8"></script>
<script type="text/javascript">
  
    var mId = $('#custtypeid').val();
    $('#prodid option').hide();
    $('#prodid option[data-mId="'+mId+'"]').show();
    $('#prodid option[value=""]').show();
    $('#custtypeid').change(function(e){
    var mId = $(this).val();
    $('#prodid option').hide();
    $('#prodid option[data-mId="'+mId+'"], #prodid option[value=""]').show();
    $('#prodid option[value=""]').attr('selected','true');
  });

$(document).ready(function()
{

  datePicket();
$('#mytable .checkall').on('click',function(e)
 {
  if($(this).is(':checked'))
  {
   $('.checkthis').prop('checked',true);//.prop('disabled',false);
  } 
  else 
  {
   $('.checkthis').prop('checked',false);//.prop('disabled',false);
  }
 });
//added for single check
$('.checkthis').on('click',function(e)
  {
    if($(".checkthis").length==$(".checkthis:checked").length)
    {
      $(".checkall").prop("checked",true);
    }
    else
    {
    $(".checkall").prop("checked",false);
    }
  });
//added for single check
});
function cancel()
{
  location.href = '/pricemaster';
}
function datePicket()
    {
        var today = new Date();
        var dd = today.getDate();
        var ddd = today.getDate()+1;
        var mm = today.getMonth()+1; //January is 0!

        var yyyy = today.getFullYear();
        var yyyyy = today.getFullYear()+20;
        if(dd<10){
            dd='0'+dd
        } 
        if(mm<10){
            mm='0'+mm
        } 
        var today = yyyy+'-'+mm+'-'+dd;
        var tomorrow = yyyy+'-'+mm+'-'+ddd;
        
        $('#dateRangePickerFrom')
            .datepicker({
                format: 'yyyy-mm-dd',
                startDate: today,
                endDate: yyyyy+'-12-30'
            })
            .on('changeDate', function(e) {
                //$('#dtp_input2').val('');
                //$('#dateRangePickerTo').datepicker('remove');
                changeData($("#dtp_input1").val(), yyyyy);
                $('.datepicker.datepicker-dropdown').hide();
                $('#form-pricemaster').bootstrapValidator('revalidateField', 'dtp_input1');
            });

        $('#dateRangePickerTo')
            .datepicker({
                format: 'yyyy-mm-dd',
                startDate: tomorrow,
                endDate: yyyyy+'-12-30',
                //Default: false
            })
            .on('changeDate', function(e) {
                $('.datepicker.datepicker-dropdown').hide();
                $('#form-pricemaster').bootstrapValidator('revalidateField', 'dtp_input2');
            });
    }

    function changeData(tomorrow, yyyyy)
    {
      $('#dateRangePickerTo')
            .datepicker({
                format: 'yyyy-mm-dd',
                startDate: tomorrow,
                endDate: yyyyy+'-12-30',
                Default: false
            })
            .on('changeDate', function(e) {
                $('.datepicker.datepicker-dropdown').hide();
                $('#form-pricemaster').bootstrapValidator('revalidateField', 'dtp_input2');
            });
        
    }

    $('#form-pricemaster').validate({
        submitHandler: function (form) {
            if(form.validate())
            {
                //form.submit();
            }
        }
    });

</script>   
<style type="text/css">
  .input-group-btn select {
    border-color: #ccc;
    margin-top: 0px;
    margin-bottom: 0px;
    padding-top: 7px;
    padding-bottom: 7px;
}
</style>  
<script type="text/javascript">
$(document).ready(function() {
    $('#form-pricemaster').bootstrapValidator({
//        live: 'disabled',
        message: 'This value is not valid',
        feedbackIcons: {
            valid: 'glyphicon glyphicon-ok',
            invalid: 'glyphicon glyphicon-remove',
            validating: 'glyphicon glyphicon-refresh'
        },
        fields: {
          
            custtypeid: {
                    validators: {
                        notEmpty: {
                            message: 'Please select Customer type.'
                        }
                    }
                },
                 prodid: {
                    validators: {
                        notEmpty: {
                            message: 'Please select Product type.'
                        }
                    }
                },
                 comptypeid: {
                    validators: {
                        notEmpty: {
                            message: 'Please select Component type.'
                        }
                    }
                },
            pname: {
                validators: {
                  notEmpty: {
                        message: 'Name is required'
                    }
                }
            },
            description: {
                validators: {
                  notEmpty: {
                        message: 'Description is required'
                    }
                }
            },
            currency_id: {
                validators: {
                  notEmpty: {
                        message: 'Please select Currency type.'
                    }
                }
            },
            price: {
                validators: {
                  notEmpty: {
                        message: 'Please enter price.'
                    }
                }
            },
            subscription_mode:{
                validators: {
                  notEmpty: {
                        message: 'Please select Subscription mode.'
                    }
                }
            },

            min_subscription: {
                validators: {
                  notEmpty: {
                        message: 'Minimum subscription is required'
                    }
                }
            },
            dtp_input1: {
                validators: {
                  notEmpty: {
                        message: 'From Date is required'
                    }
                }
            },
              dtp_input2: {
                validators: {
                  notEmpty: {
                        message: 'From Date is required'
                    }
                }
            },
            tax_class_id:{
                validators: {
                  notEmpty: {
                        message: 'Please select Tax class'
                    }
                }
            },
            'module_id[]': {
              selector: '.module_id',
                validators: {
                     choice: {
                        min: 1,
                        message: 'Please choose any module and Enter no.of users against selected module'
                      }
                  }
                }

            
        }
    });

});
</script>

@stop

