@extends('layouts.default')
@extends('layouts.header')
@extends('layouts.sideview')

@section('content')
  <style>
  table td{
    vertical-align:middle !important;
  }
  </style>

<div class="box">
              <div class="box-header with-border" style="margin-bottom:15px">
                <h3 class="box-title"><strong>Place</strong> Order</h3>
                <div class="pull-right">
                  <a href='/orders/checkOut/{{$ima_id}}/{{$id}}/{{$temp_cust_id}}' class="refresh"><i class="fa fa-arrow-circle-left"></i></a>                  
                </div>
      </div>
               <div class="col-sm-12">
                 <div class="tile-body nopadding">


  {{Form::open(array('url' => 'orders/placeOrder/'.$ima_id.'/'.$id, 'id' => 'form'))}}
   {{ Form::hidden('_method', 'POST') }}

  <section class="tile">
    <!-- /tile header -->
    <!-- tile body -->
    <div class="tile-body">
      <section class="tile">
        <div class="panel-group accordion" id="accordion">
         <!-- Start Review related Tab -->
          <div class="panel panel-default active">
          
            <div class="panel-heading panel-heading" style="height:39px;">
              <h4 class="panel-title">
                <a data-toggle="collapse" data-parent="#accordion" href="#collapseOne" >
                  <span class="col-md-5" style="padding-left:0px;">Step 1: Order Review 
                  <!-- <span id="step1success" style="display:none"><i class="fa fa-check-square-o"></i></span> --></span>
                
                 <span class="col-md-3 " id="available_quantity">Available Quantity : {{$finaldata[0]->review_qty}}</span> <span class="pull-right">Total Quantity: {{$finaldata[0]->qty}}</span>
                  </a>
              </h4>
            </div>
           
            <div id="collapseOne" class="panel-collapse collapse in">
              <div class="">  
                <section class="tile">
                  <!-- tile body -->
                  <div class="tile-body nopadding table_padding">
                    <table class="table table-datatable table-bordered dataTable" >
                      <thead>
                        <tr>
                          <th>Product Image</th>                          
                          <th>Product </th>
                          <th>Price</th>
                          <th>Quantity</th>
                          <th>SubTotal</th>
                          <th>Tax %</th>
                          <th>Tax</th>
                          <th>Total</th>
                        </tr>
                      </thead>   
          <tbody>
          
        @foreach($finaldata as $key=>$final)
        <tr>
          <td><img src="{{$final->image_url}}" width="50" /></td>
                   <td>{{$final->name}}
                      @if(empty($cust_id))
                    <td>{{$final->price}}</td>
                    <td>{{$final[0]->qty}}</td>
                    <td>{{$final->price*$final->qty}}
                      <input type="hidden" id="subtotColVal{{$final->pid}}" class="subtotCol"  
                      value="{{$final->price*$final->qty}}"  />
                    </td>
                    <td>{{$final->tax}}</td>
                    <td>{{($final->price*$final->qty)*($final->tax/100)}}</td>
                    <td>{{($final->price*$final->review_qty)+(($final->price*$final->qty)*($final->tax/100))}}</td>
                      @else
                      <td>{{$final->agreed_price}}</td>
                     <td>{{$final->qty}}</td>
                    <td>{{$final->agreed_price*$final->qty}}
                      <input type="hidden" id="subtotColVal{{$final->pid}}" class="subtotCol"  
                      value="{{$final->agreed_price*$final->qty}}"  />
                    </td>
                    <td>{{$final->tax}}</td>
                    <td>{{($final->agreed_price*$final->qty)*($final->tax/100)}}</td>
                    <td>{{($final->agreed_price*$final->qty)+(($final->agreed_price*$final->qty)*($final->tax/100))}}</td>
                    @endif
      </tr>  
      @endforeach
       
     <tr>
              <td colspan="4" align="right">Sub Total :</td><td colspan="2" id="finalTot">{{$total_sum}}</td>
              <td  id="finalTax">{{$total_tax}}</td>
             <tr> 
            <tr>
                <td colspan="4" align="right"><b>Sum Total :</b></td><td colspan="3"></td><td  id="incTax">{{$inc_tax}}</td>
            </tr>
           
            </tbody>
          </table>

        </div>
        <!-- #####/tile body -->
      </section>    
         @if($id!=9)
         <a class="btn btn-primary"  data-toggle="collapse" data-parent="#accordion" href="#collapsenine" id="#two" style="margin:0px 0px 15px 15px;">Continue</a>                            
           @else
             <a class="btn btn-primary" style="margin: 0px 0px 15px 15px" data-toggle="collapse" data-parent="#accordion" href="" id="#two" onclick="validateMe('Order Review', 'collapseOne', 'collapseten')" >
                  Continue
                </a>  
              @endif  
              </div>
            </div>
          </div>
          
          <!-- End Review related Tab -->
          <!-- Start Shipment related Tab -->
          @if($id!=9)
          <div class="panel panel-default">
            <div class="panel-heading" style="height:39px;">
              <h4 class="panel-title">
                  <a data-toggle="collapse" data-parent="#accordion" href="" id="#three">
                  <span class="col-md-5" style="padding-left:0px;">Step 2: Delivery (Ship To)</span>  <!-- <span class="col-md-3 " id="available_quantity">Available Quantity : {{$finaldata[0]->review_qty}}</span> <span class="pull-right">Total Quantity: {{$finaldata[0]->qty}}</span> -->
                </a>
              </h4>
            </div>
            <div id="collapsenine" class="panel-collapse collapse">
              <div class="panel-body"> 
                <div class="row">
                        <section class="tile">
                            <div class="">
                              <div id="successid" class="callout" align="center"></div>
                              <div id="failureid" class="callout" align="center"></div>
                            <!--<div class="panel panel-default">-->
                              <table class="table table-datatable table-padding dataTable" id="package_data">
                                     <thead>
                                        <tr>
                                            <!-- <td><th colspan="4" id="available_quantity">Available Codes : {{$finaldata[0]->review_qty}}</th></td> -->
                                            <input type="hidden" id="quantity_present" name="quantity_present" value="{{$finaldata[0]->review_qty}}">
                                        </tr>
                                    </thead>
                                    <thead>
                                        <tr>
                                            <th>Delivery Mode</th>
                                            <th>Delivery To</th>
                                            <th>Quantity</th>
                                            <th>Action  </th>
                                        </tr>
                                    </thead>
                                    <tbody id="final_append">
                                    <tr>
                                      <td> <select id="deliverymode" name="deliverymode" class='form-control requiredDropdown' style='display:block;' onchange='vendordropdown(this.id)'>
                                      <option value="" selected="selected">Please Select..</option>
                                      @foreach($array_circle as $value)
                                      <option  value="{{$value['id']. '_'. $value['name']  }}">{{$value['name']}}</option>
                                      @endforeach
                                      </select></td>
                                      <td>
                                      <span id="replacecode"><b>Not Applicable</b></span>
                                        <select id="vendorid" name="vendorid" onchange='getVendorAddress(this.value)' class='form-control requiredDropdown' style='display:block;'>
                                        <option value="" selected="selected">Please Select..</option>
                                        @if(!empty($array_circle1))
                                        @foreach($array_circle1 as $vendor)

                                        <option  value="{{$vendor['id']}}">{{$vendor['name']}}</option>
                                        @endforeach
                                        @endif
                                        </select>
                                        
                                      </td>
                                      
                                      <td>
                                        <input type='text' class="form-control"  id="quantityplaced" name="quantityplaced" style='display:block;' value="{{$finaldata[0]->review_qty}}" onblur='editchange(this.id)' placeholder='IoT Codes' >
                                      </td>
                                      <td>
                                        <input type='button' class='btn btn-primary' id='' name='' style='display:block;' onclick="submitvendor();" value='submit'>
                                        <input type='hidden' name="custid" id="custid" value="{{$cust_id}}">
                                      </td>
                                      
                                    </tr>
                                    </tbody>
                                </table>
                            </div>
                        </section>
                    </div>
                                 
              <div>
             </div>
             <div id="showiotgrid">
              </div> 
               <a class="btn btn-primary" data-toggle="collapse" data-parent="#accordion" href="" onclick="validateMe('Delivery', 'collapsenine', 'collapseten')" id="#two">
                  Continue
                </a>
              </div>
            </div>
          </div>
          @endif
          <div class="panel panel-default">
            <div class="panel-heading">
              <h4 class="panel-title">
                <a data-toggle="collapse" data-parent="#accordion" href="#collapseTwo" id="#two">
                  Step 3: Billing
                </a>
              </h4>
            </div>
            
            <div id="collapseten" class="panel-collapse collapse">
              <div class="panel-body">
                
                 <div class="row">
                  <div class="form-group col-sm-6">
                    <!--<label for="exampleInputEmail"><font size="5">Billing Details</font></label>-->
                    <h4>Billing Details</h4>
                  </div>
                </div>
                
                <div class="row">
                  <input type="hidden" id="custom_id" name="custom_id" value="{{$temp_cust_id}}">
                  <div class="form-group col-sm-6">
                    <input type="hidden" id="check" name="check" 
                    onchange="testing(this.checked)">
                  </div>
                 
                </div>
             
               
                <div class="row">
                  <div class="form-group col-sm-6">
                    <label for="exampleInputEmail">First Name*</label>
                    <div class="input-group ">
                      <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
                      @if(!empty($customer_address[0]->firstname))
                      <input type="text" class="form-control required" placeholder="First Name" name="bill_first_name" id="bill_first_name" value="{{$customer_address[0]->firstname}}" >
                      @else
                      <input type="text" class="form-control required" placeholder="First Name" name="bill_first_name" id="bill_first_name"  >
                      @endif
                    </div>
                  </div>
                 
                  <div class="form-group col-sm-6">
                    <label for="exampleInputEmail">Last Name*</label>
                    <div class="input-group ">
                      <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
                      @if(!empty($customer_address[0]->lastname))
                      <input type="text" class="form-control " placeholder="Last Name" name="bill_last_name" id="bill_last_name" value="{{$customer_address[0]->lastname}}">
                      @else
                      <input type="text" class="form-control " placeholder="Last Name" name="bill_last_name" id="bill_last_name" >
                      @endif
                    </div>
                  </div>
                </div>
                <div class="row">
                 
                </div>
                <div class="row">
                <div class="form-group col-sm-6">
                  <label for="exampleInputEmail">Address*</label>
                  <div class="input-group">
                    <span class="input-group-addon addon-red"><i class="fa fa-newspaper-o"></i></span>
                    @if(!empty($customer_address[0]->address_1))
                    <textarea class="form-control required" id="bill_address" name="bill_address" rows="3">{{$customer_address[0]->address_1}}</textarea>
                    @else
                    <textarea class="form-control required" id="bill_address" name="bill_address" rows="3"></textarea>
                    @endif
                  </div>                        
                </div>
          

                <div class="form-group col-sm-6">
              <label for="exampleInputEmail">City*</label>
              <div class="input-group ">
                <span class="input-group-addon addon-red"><i class="fa fa-building-o"></i></span>
                @if(!empty($customer_address[0]->city))
                <input type="text" class="form-control required" placeholder="City" name="bill_city" id="bill_city" value="{{$customer_address[0]->city}}">
                @else
                <input type="text" class="form-control required" placeholder="City" name="bill_city" id="bill_city" >
                @endif
              </div>
            </div>

                </div>
                <div class="row">
               
              </div>
               <div class="row">
               <div class="form-group col-sm-6">
                <label for="exampleInputEmail">Country*</label>
                <div class="input-group ">
                  <span class="input-group-addon addon-red"><i class="fa fa-flag-checkered"></i></span>
                  <div id="selectbox">
                   <select class="form-control requiredDropdown" id="bill_country_id" name="bill_country_id"   onchange="test()" 
                    parsley-trigger="change" parsley-required="true" parsley-error-container="#selectbox" >
                        <option  value="" selected="selected">Please Select..</option>
                        @foreach($countries as $count)
                        @if(!empty($customer_address[0]->country_id))
                        @if($count->country_id == $customer_address[0]->country_id) 
                        <option  value="{{$count->country_id}}">{{$count->name}}</option>
                        @else
                        <option  value="{{$count->country_id}}">{{$count->name}}</option>
                        @endif
                        @else
                        <option  value="{{$count->country_id}}">{{$count->name}}</option>
                        @endif
                        @endforeach
                      </select>
                    </div>
                </div>
              </div>
              
               <div class="form-group col-sm-6">
                <label for="exampleInputEmail">Zone*</label>
                <div class="input-group ">
                  <span class="input-group-addon addon-red"><i class="fa fa-flag"></i></span>
                  <div id="selectbox1">
                      <select class="form-control requiredDropdown" id="bill_zone_id" name="bill_zone_id"  parsley-trigger="change" parsley-required="true" parsley-error-container="#selectbox" >
                        <option  value="" selected="selected">Please Select..</option> 
<!--                         @foreach($zones as $key=>$result)
                        @if(!empty($customer_address[0]->country_id))
                        @if($result->zone_id==$customer_address[0]->zone_id)
                        <option  value="{{$key}}">{{$result->name}}</option>
                        @else
                        <option  value="{{$key}}">{{$result->name}}</option>
                        @endif
                        @else
                        <option  value="{{$key}}">{{$result->name}}</option>
                        @endif
                        @endforeach -->
                      </select>
                    </div>
                </div>
              </div>
              </div>
             <div class="row">
                   
              </div>
              <div class="row">
                  <div class="form-group col-sm-6">
                <label for="exampleInputEmail">Phone*</label>
                <div class="input-group ">
                  <span class="input-group-addon addon-red"><i class="fa fa-mobile"></i></span>
                  @if(!empty($customer_address[0]->mobile_no))
                  <input type="text" class="form-control" placeholder="Phone Number" name="bill_phone_no" id="bill_phone_no"  value="{{$customer_address[0]->mobile_no}}">
                  @else
                  <input type="text" class="form-control" placeholder="Phone Number" name="bill_phone_no" id="bill_phone_no" >
                  @endif
                </div>
              </div>              
                </div>                                   
              <div>
             </div> 
                  <a class="btn btn-primary" data-toggle="collapse" data-parent="#accordion" href="" id="#two" onclick="validateMe('billing', 'collapseten', 'collapseThree')">
                  Continue
                </a>
              </div>
            </div>
            
          </div>
          <!-- End Shipment related Tab -->
          <!-- Start Payment related Tab -->
          <div class="panel panel-default">
            <div class="panel-heading">
              <h4 class="panel-title">
                <a data-toggle="collapse" data-parent="#accordion" href="" id="#three">
                  Step 4:Payment
                </a>
              </h4>
            </div>
            <div id="collapseThree" class="panel-collapse collapse">
              <div class="panel-body"> 
<div id="parentVerticalTab">
            <ul class="resp-tabs-list hor_1">
                <li><i class="fa fa-gift"></i>RTGS/NEFT</li>
                <li><i class="fa fa-credit-card"></i>Cheque</li>
                <li><i class="fa fa-desktop"></i>DD</li>
                <li><i class="fa fa-gift"></i>COD</li>
                <li><i class="fa fa-credit-card"></i>Credit Card</li>
                <li><i class="fa fa-credit-card"></i>Debit Card</li>
                <li><i class="fa fa-inr"></i>Net Banking</li>
                <li><i class="fa fa-gift"></i>Wallet</li>
            </ul>
            <div class="resp-tabs-container hor_1">
                <div>
                   <div class="row">
                            <div class="form-group col-sm-6">
                              <label for="exampleInputEmail">Reference Number*</label>
                              <div class="input-group ">
                                <span class="input-group-addon addon-red"><i class="ion-pound"></i></span>
                                <input type="text" class="form-control required" placeholder="Reference Number" name="trans_reference_no"  id="ref_number">
                              </div>
                            </div>
                            <div class="form-group col-sm-6">
                              <label for="exampleInputEmail">Payee Bank*</label>
                              <div class="input-group ">
                                <span class="input-group-addon addon-red"><i class="fa fa-university"></i></span>
                                <input type="text" class="form-control required" placeholder="Payee Bank Name" name="payee_bank"  id="payee_bank_name">
                              </div>
                            </div>
                          </div>
                          <div class="row">
                            <div class="form-group col-sm-6">
                              <label for="exampleInputEmail">IFSC CODE*</label>
                              <div class="input-group ">
                                <span class="input-group-addon addon-red"><i class="ion-ios-locked-outline"></i></span>
                                <input type="text" class="form-control required" placeholder="IFSC Code" name="ifsc_code"  id="ifsc_code">
                              </div>
                            </div>
                            <div class="form-group col-sm-6">
                              <label for="exampleInputEmail">Payment Date </label>
                              <div class="input-group ">
                                <span class="input-group-addon addon-red"><i class="ion-ios-calendar-outline"></i></span>
                                <input type="text" class="form-control" placeholder="Payment Date" value="<?php echo date('Y-m-d'); ?>" name="payment_date"  readonly="true" id="payment_date">
                              </div>
                            </div>
                          </div>
                          <div class="row">
                            <div class="form-group col-sm-6">
                              <label for="exampleInputEmail">Amount*</label>
                              <div class="input-group ">
                                <span class="input-group-addon addon-red"><i class="fa fa-money"></i></span>
                                <input type="number" min=0 step="any" class="form-control required" placeholder="Amount" name="amount"  id="amount">
                              </div>
                            </div>
                            <div class="form-group col-sm-6">
                              <label for="exampleInputEmail"> </label>
                              <div class="input-group ">
                                
                                <input type="hidden" class="form-control" placeholder="Payment Type" name="payment_type"  id="payment_type" value="RTGS/NEFT">
                              </div>
                            </div>
                          </div>  

                </div>
                
            </div>
        </div>
                 <!--Vertical Tab start-->
              <div>{{ Form::submit('Proceed', array('class' => 'btn btn-primary')) }}
              {{ Form::close() }}
              </div>
            </div>
          </div>
          <!-- End Payment related Tab -->
        
        </diquantityv>
      </section>
    </div>
    <!-- /tile body -->    
  </div>
  <!--Basic form end-->

    <!--Edit IOT Code-->
  <div class="modal fade" id="basicvalCodeModal" tabindex="-1" role="dialog" aria-labelledby="basicvalCode" aria-hidden="true">
        <div class="modal-dialog wide">
          <div class="modal-content">
            <div class="modal-header">
              <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
              <h4 class="modal-title" id="basicvalCode">Edit IoT Code</h4>
            </div>
            <div class="modal-body">

            {{ Form::open(array('url' => '','data-url' => '/orders/updateiotcodes/','id'=>'editiotform')) }}
            {{ Form::hidden('_method', 'PUT') }}
            <!-- <form name="editiotform" id="editiotform" method="POST" action="#"> -->
            <div class="row">
            <div id="successid1" class="callout" align="center"></div>
            <div id="failureid1" class="callout" align="center"></div>            
              <div class="form-group col-sm-6">
                <label class=" control-label" for="input-fields">Delivery Mode</label>
                 
                  <div class="input-group ">

                    <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
                    <select id="delivery_mode_id" name="delivery_mode_id" class='form-control requiredDropdown' style='display:block;' onchange='vendordropdown(this.id)'>                 
                    @foreach($array_circle as $value)
                    <option  value="{{$value['id']}}">{{$value['name']}}</option>
                    @endforeach
                    </select>
                  </div>
                  
              </div>
              <div class="form-group col-sm-6">
              <span style="margin-top:40px;"id="replacecodeEdit"><b>Not Applicable</b></span>
              <span id="vendor_id_edit">
                <label class=" control-label" for="input-fields">Vendor</label>
                 
                  <div class="input-group ">  
                      <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
                      
                      <select id="vendor_id" name="vendor_id" onchange='getVendorAddress(this.value)' class='form-control' style='display:block;'>
                      @if(!empty($array_circle1))
                      @foreach($array_circle1 as $vendor)
                      <option  value="{{$vendor['id']}}">{{$vendor['name']}}</option>
                      @endforeach
                      @endif
                      </select>
                      
                  </div>
               
                  </span>
              </div>
            </div>
            <div class="row">
              <div class="form-group col-sm-6">
                <label class=" control-label" for="input-fields">Quantity</label>
                 
                  <div class="input-group ">  
                    <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
                    <input type="text" class="form-control"  id="quantity" name="quantity" style='display:block;'>                   
                  </div>
                  
              </div>
              <input type="hidden" id="id" name="id" value="" />
              <input type="hidden" id="vendorpopup" name="vendorpopup" value="" />
              <input type="hidden" id="quantitypopup" name="quantitypopup" value="" />
              
            </div>
            <input type="button" class="btn btn-primary" value="Update Iot Order" onclick="submitvendorEdit()" />
            <!-- {{ Form::submit('Update Iot Order', array('class' => 'btn btn-primary')) }} -->
            {{ Form::close() }}

              </div>
            </div><!-- /.modal-content -->
          </div><!-- /.modal-dialog -->
        </div><!-- /.modal -->    
      <!--Edit IOT Code-->
@stop
  @section('style')
    {{HTML::style('jqwidgets/styles/jqx.base.css')}}
    {{HTML::style('css/easy-responsive-tabs.css')}}
  @stop

  @section('script')
    {{HTML::script('jqwidgets/jqxcore.js')}}
    {{HTML::script('jqwidgets/jqxbuttons.js')}}
    {{HTML::script('jqwidgets/jqxscrollbar.js')}}
    {{HTML::script('jqwidgets/jqxmenu.js')}}
    {{HTML::script('jqwidgets/jqxgrid.js')}}
    {{HTML::script('jqwidgets/jqxgrid.selection.js')}}
    {{HTML::script('jqwidgets/jqxgrid.columnsresize.js')}}
    {{HTML::script('jqwidgets/jqxdata.js')}}
    {{HTML::script('scripts/demos.js')}}
    {{HTML::script('jqwidgets/jqxlistbox.js')}}
    {{HTML::script('jqwidgets/jqxdropdownlist.js')}}
    {{HTML::script('jqwidgets/jqxgrid.pager.js')}}
    {{HTML::script('jqwidgets/jqxgrid.sort.js')}}
    {{HTML::script('jqwidgets/jqxgrid.filter.js')}}
    {{HTML::script('jqwidgets/jqxgrid.storage.js')}}
    {{HTML::script('jqwidgets/jqxgrid.columnsreorder.js')}}
    {{HTML::script('jqwidgets/jqxpanel.js')}}
    {{HTML::script('jqwidgets/jqxcheckbox.js')}}
    {{HTML::script('js/easyResponsiveTabs.js')}}

  <script type="text/javascript">
    $(document).ready(function() {
      document.getElementById('replacecodeEdit').style.display='none';
      document.getElementById('replacecode').style.display='none';
      document.getElementById('successid').style.display='none';
      document.getElementById('failureid').style.display='none';
      document.getElementById('successid1').style.display='none';
      document.getElementById('failureid1').style.display='none';    
      if ($('#quantityplaced').val() == 0){
        $('#quantityplaced').prop('readonly', true);
      }
      $('#quantityplaced').keypress(function(event) {
    if (event.which == 13) {
        event.preventDefault();
    }
});
      
        $('#form').bootstrapValidator({
    //        live: 'disabled',
            message: 'This value is not valid',
            feedbackIcons: {
                valid: 'glyphicon glyphicon-ok',
                invalid: 'glyphicon glyphicon-remove',
                validating: 'glyphicon glyphicon-refresh'
            },
            fields: {
                bill_first_name: {
                    validators: {
                        notEmpty: {
                            message: 'The bill first name is required and can\'t be empty'
                        }  
                    }
                },
                bill_last_name: {
                    validators: {
                        notEmpty: {
                            message: 'The bill last name is required and can\'t be empty'
                        }                        
                    }
                },
                bill_address: {
                    validators: {
                        notEmpty: {
                            message: 'The bill address is required and can\'t be empty'
                        }  
                    }
                },
                bill_city: {
                    validators: {
                        notEmpty: {
                            message: 'The bill city is required and can\'t be empty'
                        }                        
                    }
                },
                bill_country_id: {
                    validators: {
                        callback: {
                            message: 'Please choose country',
                            callback: function(value, validator, $field) {
                                return (value != 0);
                            }
                        }/*,
                        notEmpty: {
                            message: 'The bill country is required and can\'t be empty'
                        }  */                      
                    }
                },
                bill_zone_id: {
                    validators: {
                        callback: {
                            message: 'Please choose zone',
                            callback: function(value, validator, $field) {
                              var options = $('[id="bill_zone_id"]').val();
                                return (options != "");
                                //return (value != 0);
                            }
                        }/*,
                        notEmpty: {
                            message: 'The bill zone is required and can\'t be empty'
                        } */                       
                    }
                },
                bill_phone_no: {
                    validators: {
                        notEmpty: {
                            message: 'The bill phone number is required and can\'t be empty'
                        },                         
                        numeric: {
                            message: 'The value is not a number'
                        },
                        stringLength: {
                            min: 10,
                            max: 10,
                            message: 'Please enter 10 digit number'

                        }                        
                    }
                },                
                trans_reference_no: {
                    validators: {
                        regexp: {
                            regexp: '^[a-zA-Z0-9]+$',
                            message: 'Please enter only alpha-numeric'
                        },                      
                        notEmpty: {
                            message: 'The reference number is required and can\'t be empty'
                        }                        
                    }
                },
                payee_bank: {
                    validators: {
                        regexp: {
                            regexp: '^[a-zA-Z0-9. ]+$',
                            message: 'Please enter only alpha-numeric'
                        },                      
                        notEmpty: {
                            message: 'The payee bank is required and can\'t be empty'
                        }
                    }
                },
                ifsc_code: {
                    validators: {
                        regexp: {
                            regexp: '^[a-zA-Z0-9]+$',
                            message: 'Please enter only alpha-numeric'
                        },                      
                        notEmpty: {
                            message: 'The ifsc code is required and can\'t be empty'
                        }
                    }
                },
                payment_date: {
                    validators: {
                        notEmpty: {
                            message: 'The date is required and can\'t be empty'
                        }
                    }
                },
                amount: {
                    validators: {
                        notEmpty: {
                            message: 'The amount is required and can\'t be empty'
                        }
                    }
                }
            }
        });
        showIotCodes();
         //Vertical Tab
        $('#parentVerticalTab').easyResponsiveTabs({
            type: 'vertical', //Types: default, vertical, accordion
            width: 'auto', //auto or any width like 600px
            fit: true, // 100% fit in a container
            closed: 'accordion', // Start closed if in accordion view
            tabidentify: 'hor_1', // The tab groups identifier
            activate: function(event) { // Callback function if tab is switched
                var $tab = $(this);
                var $info = $('#nested-tabInfo2');
                var $name = $('span', $info);
                $name.text($tab.text());
                $info.show();
            }
        });
    });
</script>
<script type="text/javascript">
    $('#basicvalCodeModal').on('show.bs.modal', function () 
    {             
      makePopupEditAjaxProceed($('#basicvalCodeModal'));
 
  });
     
   
</script>
  <script type="text/javascript">
  $('#bill_country_id').on('change', function() {
    var zone_value=$('#bill_country_id').val();
    $('[id="bill_zone_id"]').empty();
    var url = '/orders/getZones';
    var posting = $.get(url, {type: zone_value});
    posting.done(function (data) {
       $('#bill_zone_id').append('<option value="">' +'Please Select..'+ '</option>');
       var result = data;
       $.each(result, function (key, value) {
            $('#bill_zone_id').append('<option value="' + value['id'] + '">' + value['name'] + '</option>');
        });
    });    
   });
/*  function test()
  {
    $(document).ready(function() {
    $('#bill_country_id').on('change', function() {
      //$('#bill_zone_id').html('');
      var zone_value=this.value;
       $.ajax
      (
      {
        url: "/orders/getZones", 
        type: "GET", 
        data: "type=" +zone_value,
        success: function(response)
        {          
          console.log(response);
          $('#selectbox1').html(response);
        },
        error:function()
        {
          
        }   
      }
    );  
    });
    });    
  }*/

  function testing()
  {
    $('[name="ship_first_name"]').val($('[name="bill_first_name"]').val());
    $('[name="ship_last_name"]').val($('[name="bill_last_name"]').val());
    $('[name="ship_address"]').text($('[name="bill_address"]').text());
    $('[name="ship_city"]').val($('[name="bill_city"]').val());
    $('[name="ship_phone_no"]').val($('[name="bill_phone_no"]').val());
    $('[name="ship_country_id"]').val($('[name="bill_country_id"]').val());
    $('[name="ship_zone_id"]').val($('[name="bill_zone_id"]').val());
  }
   
   var my_array=new Array();
   var vendor_array=new Array();

    var circles_array=<?php echo $circles; ?>;
    var vendors_array=<?php if(!empty($vendors_array)) echo $vendors_array; else echo 0; ?>;
    //alert(vendors_array);
  // alert(circles_array);
    var circles_count=circles_array.length;
    var vendors_count=vendors_array.length;
    //alert(vendors_count);



  function statusmessage()
  {
   $('#successid').delay(3000).fadeOut(1500);
   $('#failureid').delay(3000).fadeOut(1500);
   $('#successid1').delay(3000).fadeOut(1500);
   $('#failureid1').delay(3000).fadeOut(1500);
  }
  
  function submitvendor(cust_id){
    
    document.getElementById('failureid').innerHTML='';
    document.getElementById('successid').innerHTML='';
    var cust_id = $('#custid').val();
    var delivery_mode = $('#deliverymode').val();
    var delivery_id = delivery_mode.substr( 0, delivery_mode.indexOf('_') );
    var delivery_name = delivery_mode.substr( delivery_mode.indexOf('_') + 1 );
    var vendor_id = $('#vendorid').val();
    var quantity = parseInt($('#quantityplaced').val());
    var available_quantity = parseInt($('#quantity_present').val());
    var greater=(quantity <= available_quantity);
    //alert(quantity+'---'+available_quantity+'----'+greater);

    if(!greater){ 
       //alert('Available quantity is less than Total quantity.');
        document.getElementById('successid').style.display='none';
        document.getElementById('failureid').style.display='block';
        document.getElementById('failureid').innerHTML = 'Available quantity is less than Total quantity';
        $("#failureid").addClass("callout callout-danger");
        $("#successid").removeClass("callout-success");
        $("#failureid").removeClass("callout-info");
        $("#failureid").removeClass("callout-warning");
        statusmessage();
        

    }
    else{
    var available_codes = available_quantity - quantity;
    if(delivery_name == 'Vendor-Direct')
    {
      if(delivery_name == '' || vendor_id == '' || quantity == '')
        {
         //alert('Please fill all fields');
          document.getElementById('successid').style.display='none';
          document.getElementById('failureid').style.display='block';
          document.getElementById('failureid').innerHTML = 'Please fill all fields';
          $("#failureid").addClass("callout callout-warning");
          $("#successid").removeClass("callout-success");
          $("#failureid").removeClass("callout-info");
          $("#failureid").removeClass("callout-danger");
          statusmessage();
          return false;
          
        }
        else{
          $.ajax({
              url: '/orders/addiotcodes',
              data: {
                  'customer_id' : cust_id, 
                  'deliveryid' : delivery_id,
                  'deliveryname' : delivery_name,
                  'vendorid' : vendor_id,
                  'total_codes' : quantity
              },
              type:'POST',
              success: function(result)
              {
                  result = result.trim();
                  if(result.length>1)
                    {
                      var finalresult = result.substr( 0, result.indexOf('-') );
                      var count = result.substr(result.indexOf('-')+1);
                    }
                    else
                    {
                      var finalresult = result;
                    }
                  if(finalresult == 2)
                  {   
                      document.getElementById('available_quantity').innerHTML='Available Codes : '+count;
                      document.getElementById('quantity_present').value=count;
                      document.getElementById('quantityplaced').value=count;
                      //added to disable the quantityplaced field
                      if ($('#quantityplaced').val() == 0){
                        $('#quantityplaced').prop('readonly', true);
                      }
                      //added to disable the quantityplaced field  
                    document.getElementById('successid').style.display='block';
                    document.getElementById('failureid').style.display='none';
                    document.getElementById('successid').innerHTML='Succesfully added .';
                    $("#successid").addClass("callout callout-success");
                    $("#failureid").removeClass("callout-danger");
                    $("#failureid").removeClass("callout-info");
                    $("#failureid").removeClass("callout-warning");
                    showIotCodes();
                    statusmessage();

                  }
                  else if(finalresult == 1)
                  {
                    //alert('Already added for this vendor, please update the quantity.');
                    document.getElementById('successid').style.display='none';
                    document.getElementById('failureid').style.display='block';
                    document.getElementById('failureid').innerHTML='Already added for this vendor, please update the quantity.';
                    $("#failureid").addClass("callout callout-info");
                    $("#failureid").removeClass("callout-danger");
                    $("#successid").removeClass("callout-success");
                    $("#failureid").removeClass("callout-warning");
                    statusmessage();

                  }
                  else
                  {
                    //alert('IOT Codes are not added.');
                    document.getElementById('successid').style.display='block';
                    document.getElementById('failureid').style.display='none';
                    document.getElementById('successid').innerHTML='Succesfully added .';
                    $("#successid").addClass("callout callout-success");
                    $("#failureid").removeClass("callout-danger");
                    $("#failureid").removeClass("callout-info");
                    $("#failureid").removeClass("callout-warning");
                    statusmessage();
                  }
              }
          });
        }
      }else{
        var vendor_id = document.getElementById('vendorid').value=0;
        if(delivery_mode == '' || quantity == '')
        {
         // alert('Please fill all fields');
         document.getElementById('successid').style.display='none';
         document.getElementById('failureid').style.display='block';
         document.getElementById('failureid').innerHTML='Please fill all fields';
          $("#failureid").addClass("callout callout-warning");
          $("#successid").removeClass("callout-success");
          $("#failureid").removeClass("callout-info");
          $("#failureid").removeClass("callout-danger");
          statusmessage();
          return false;
          
        }
        else{
          $.ajax({
              url: '/orders/addiotcodes',
              data: {
                  'customer_id' : cust_id, 
                  'deliveryid' : delivery_id,
                  'deliveryname' : delivery_name,
                  'vendorid' : vendor_id,
                  'total_codes' : quantity
              },
              type:'POST',
              success: function(result)
              {
                  if(result.length>1)
                  {
                    var finalresult = result.substr( 0, result.indexOf('-') );
                    var count = result.substr(result.indexOf('-')+1);
                  }
                  else
                  {
                    var finalresult = result;
                  }
                  if(finalresult == 2)
                  {   
                      document.getElementById('available_quantity').innerHTML='Available Codes : '+count;
                      document.getElementById('quantity_present').value=count;
                      document.getElementById('quantityplaced').value=count;
                      //added to disable the quantityplaced field
                      if ($('#quantityplaced').val() == 0){
                        $('#quantityplaced').prop('readonly', true);
                      }
                      //added to disable the quantityplaced field                      
                      document.getElementById('successid').style.display='block';
                      document.getElementById('failureid').style.display='none';
                      document.getElementById('successid').innerHTML='Successfully added .';
                      showIotCodes();
                    $("#successid").addClass("callout callout-success");
                    $("#failureid").removeClass("callout-danger");
                    $("#failureid").removeClass("callout-info");
                    $("#failureid").removeClass("callout-warning");
                    statusmessage();

                  }else if(finalresult == 1)
                  {
                    //alert('Please select different Vendor.');
                     document.getElementById('successid').style.display='none';
                     document.getElementById('failureid').style.display='block';
                     document.getElementById('failureid').innerHTML='Please select different Vendor.';
                      $("#failureid").addClass("callout callout-info");
                      $("#failureid").removeClass("callout-danger");
                      $("#failureid").removeClass("callout-warning");
                      $("#successid").removeClass("callout-success");
                      statusmessage();

                  }
                  else
                  {
                    //alert('IOT Codes are not added.');
                    document.getElementById('successid').style.display='block';
                    document.getElementById('failureid').style.display='none';
                    document.getElementById('successid').innerHTML='Successfully added .';
                    $("#successid").addClass("callout callout-success");
                    $("#failureid").removeClass("callout-danger");
                    $("#failureid").removeClass("callout-info");
                    $("#failureid").removeClass("callout-warning");
                    statusmessage();

                  }
              }
          });
        }
      }
  }
}

function submitvendorEdit()
{   

    document.getElementById('failureid').innerHTML='';
    document.getElementById('successid').innerHTML=''; 
    document.getElementById('failureid1').innerHTML='';
    document.getElementById('successid1').innerHTML='';     
    var cust_id = $('#custid').val();
    var id = $('#id').val();
    var delivery_id = $('#delivery_mode_id').val()?$('#delivery_mode_id').val():'';
    var delivery_name = $('#delivery_mode_id option:selected').html();
    var vendor_id = $('#vendor_id option:selected').val();
    var vendor_idValidate = $('#vendorpopup').val();
    var vendor_name = $('#vendor_id option:selected').html();
    var quantity = parseInt($('#quantity').val());

    var prevquantity = parseInt(document.getElementById('quantitypopup').value);
    var available_quantity = parseInt($('#quantity_present').val());
    available_quantity = available_quantity + prevquantity;
    
    var greater=(quantity <= available_quantity);
    if(!greater)
    { 
        //alert('Available quantity is less than Total quantity.');
        document.getElementById('successid1').style.display='none';
        document.getElementById('failureid1').style.display='block';
        document.getElementById('failureid1').innerHTML='Available quantity is less than Total quantity.';
        $("#failureid1").addClass("callout callout-danger");
        $("#successid1").removeClass("callout-success");
        $("#failureid1").removeClass("callout-info");
        $("#failureid1").removeClass("callout-warning");
        statusmessage();
    }
    else
    {
        var available_codes = available_quantity - quantity;
        if(delivery_name == 'Vendor-Direct')
        {
            if(delivery_name == '' || vendor_id == '' || quantity == '')
            {
                //alert('Please fill all fields');
                document.getElementById('successid').style.display='none';
                document.getElementById('failureid').style.display='block';
                document.getElementById('failureid').innerHTML='Please fill all fields';
                $("#failureid").addClass("callout callout-warning");
                $("#successid").removeClass("callout-success");
                $("#failureid").removeClass("callout-info");
                $("#failureid").removeClass("callout-danger");
                statusmessage();
                return false;
            }
            else
            {
                $.ajax({
                url: '/orders/updateiotcodes/1',
                data: {
                    'customer_id' : cust_id, 
                    'id' : id, 
                    'deliveryid' : delivery_id,
                    'deliveryname' : delivery_name,
                    'vendorid' : vendor_id,
                    'vendor_idValidate' : vendor_idValidate,
                    'prevquantity' : prevquantity,
                    'vendor_name' : vendor_name,
                    'total_codes' : quantity
                },
                type:'POST',
                success: function(result)
                {
                   if(result.length>1)
                    {
                      var finalresult = result.substr( 0, result.indexOf('-') );
                      var count = result.substr(result.indexOf('-')+1);
                    }
                    else
                    {
                      var finalresult = result;
                    }
                    if(finalresult == 2)
                    {   
                        //alert('hi');
                        document.getElementById('available_quantity').innerHTML='Available Codes : '+count;
                        document.getElementById('quantity_present').value=count;
                        document.getElementById('quantityplaced').value=count;
                        //added to disable the quantityplaced field
                        if ($('#quantityplaced').val() !== 0){
                          $('#quantityplaced').prop('readonly', false);
                        }
                        //added to disable the quantityplaced field                          
                        document.getElementById('successid').style.display='block';
                        document.getElementById('failureid').style.display='none';
                        document.getElementById('successid').innerHTML='Succesfully updated .';
                        $("#successid").addClass("callout callout-success");
                        $("#failureid").removeClass("callout-danger");
                        $("#failureid").removeClass("callout-info");
                        $("#failureid").removeClass("callout-warning");
                        statusmessage();
                        showIotCodes();
                       
                    }else if(finalresult == 1)
                    {
                      //alert('Please select different Vendor.');
                      document.getElementById('successid').style.display='none';
                      document.getElementById('failureid').style.display='block';
                      document.getElementById('failureid').innerHTML='Please select different Vendor.';
                      $("#failureid").addClass("callout callout-info");
                      $("#successid").removeClass("callout-success");
                      $("#failureid").removeClass("callout-warning");
                      $("#failureid").removeClass("callout-danger");
                      statusmessage();

                    }
                    else
                    {
                      document.getElementById('available_quantity').innerHTML='Available Codes : '+count;
                     document.getElementById('successid').style.display='block';
                        document.getElementById('failureid').style.display='none';
                        document.getElementById('successid').innerHTML='Succesfully updated .';
                        $("#successid").addClass("callout callout-success");
                        $("#failureid").removeClass("callout-danger");
                        $("#failureid").removeClass("callout-info");
                        $("#failureid").removeClass("callout-warning");
                        statusmessage();
                    }
                    showIotCodes();
                    $('#basicvalCodeModal').modal('hide');
                }
            });
            }
        }
        else
        {
            var vendor_id = document.getElementById('vendorid').value=0;
            if(delivery_name == '' || quantity == '')
            {
               // alert('Please fill all fields');
                document.getElementById('successid').style.display='none';
                document.getElementById('failureid').style.display='block';
                document.getElementById('failureid').innerHTML='Please fill all fields';
                $("#failureid").addClass("callout callout-warning");
                $("#successid").removeClass("callout-success");
                $("#failureid").removeClass("callout-info");
                $("#failureid").removeClass("callout-danger");
                statusmessage();
                return false;
                
            }
            else
            {
                $.ajax({
                url: '/orders/updateiotcodes/1',
                data: {
                    'customer_id' : cust_id, 
                    'id' : id, 
                    'deliveryid' : delivery_id,
                    'deliveryname' : delivery_name,
                    'vendorid' : vendor_id,
                    'vendor_idValidate' : vendor_idValidate,
                    'vendor_name' : vendor_name,
                    'total_codes' : quantity
                },
                type:'POST',
                success: function(result)
                {
                    if(result.length>1)
                    {
                      var finalresult = result.substr( 0, result.indexOf('-') );
                      var count = result.substr(result.indexOf('-')+1);
                    }
                    else
                    {
                      var finalresult = result;
                    }
                    if(finalresult == 2)
                    {   
                        //alert('hello');
                        document.getElementById('available_quantity').innerHTML='Available Codes : '+count;
                        document.getElementById('quantity_present').value=count;
                        document.getElementById('quantityplaced').value=count;
                        //added to disable the quantityplaced field
                        if ($('#quantityplaced').val() !== 0){
                          $('#quantityplaced').prop('readonly', false);
                        }
                        //added to disable the quantityplaced field                          
                        document.getElementById('successid').style.display='block';
                        document.getElementById('failureid').style.display='none';
                        document.getElementById('successid').innerHTML='Succesfully updated .';
                        $("#successid").addClass("callout callout-success");
                        $("#failureid").removeClass("callout-danger");
                        $("#failureid").removeClass("callout-info");
                        $("#failureid").removeClass("callout-warning");
                        statusmessage();                        
                       
                    }else if(finalresult == 1)
                    {
                      //alert('Please select different Vendor.');
                      document.getElementById('successid').style.display='none';
                      document.getElementById('failureid').style.display='block';
                      document.getElementById('failureid').innerHTML='Please select different Vendor';
                      $("#failureid").addClass("callout callout-info");
                      $("#successid").removeClass("callout-success");
                      $("#failureid").removeClass("callout-danger");
                      $("#failureid").removeClass("callout-warning");
                      statusmessage();

                    }
                    else
                    {
                      document.getElementById('available_quantity').innerHTML='Available Codes : '+count;
                      document.getElementById('successid').style.display='block';
                        document.getElementById('failureid').style.display='none';
                        document.getElementById('successid').innerHTML='Succesfully updated .';
                        $("#successid").addClass("callout callout-success");
                        $("#failureid").removeClass("callout-danger");
                        $("#failureid").removeClass("callout-info");
                        $("#failureid").removeClass("callout-warning");
                        statusmessage();

                    }
                    showIotCodes();
                    $('#basicvalCodeModal').modal('hide');
                
              }
            });
            }
        }
       
    }
}
function showIotCodes()
    {
        var url = "/orders/showiotcodes";
        var source =
        {
            datatype: "json",
            datafields: [
                { name: 'id', type: 'integer' },
                { name: 'customer_id', type: 'integer' },
                { name: 'delivery_mode', type: 'varchar' },
                { name: 'vendor', type: 'integer' },
                { name: 'quantity', type: 'integer' },
                { name: 'actions', type: 'string' },
            ],
            url: url,
            pager: function (pagenum, pagesize, oldpagenum) {
            }
        };
        var dataAdapter = new $.jqx.dataAdapter(source);
        $("#showiotgrid").jqxGrid(
        {
            width: "100%",
            source: source,
            selectionmode: 'multiplerowsextended',
            sortable: true,
            pageable: false,
            autoheight: true,
            autoloadstate: false,
            autosavestate: false,
            columnsresize: true,
            columnsreorder: true,
            showfilterrow: false,
            filterable: false,
            columns: [
              { text: 'Delivery Mode', filtercondition: 'starts_with', datafield: 'delivery_mode', width: "40%" },
              { text: 'Vendor', datafield: 'vendor', width: "40%"},
              { text: 'Quantity', datafield: 'quantity', width: "10%"},
              { text: 'Actions', datafield: 'actions',width:"10%" }
            ]               
        });           
           
    }
function makePopupEditAjaxProceed($el, primaryKey) {
    $el.on('shown.bs.modal', function (e) {
        var url = $(e.relatedTarget).data('href'),
                $this = $(this),
                $form = $this.find('form'),
                key = primaryKey || 'id';        
        $.get(url, function (data) {
            $.each(data, function (i, v) {
                if ( i == key ) {
                    $form.attr('action', function () {
                        return $(this).data('url') + v;
                    });
                }
                var el = $form.find('[name="' + i + '"]');
                if ( el.length && el[0].type.toLowerCase() == 'checkbox' ) {
                    el.prop('checked', false);
                    el.filter('[value=' + v + ']').prop('checked', true);
                    return;
                }
                el.val(v);
            });
            var reqid = $('#vendor_id option:selected').val();
            document.getElementById('vendorpopup').value = reqid;
            document.getElementById('quantitypopup').value = document.getElementById('quantity').value;
            var delivery_mode_id = $('#delivery_mode_id').val();
            var delivery_name_edit = $('#delivery_mode_id option:selected').html();
            //alert(delivery_name_edit);
            if(delivery_name_edit=='Downloadable' || delivery_name_edit=='Print and Deliver')
            {
                document.getElementById('replacecodeEdit').style.display='block';
                document.getElementById('vendor_id_edit').style.display='none';
            }
            else
            {
                document.getElementById('replacecodeEdit').style.display='none';
                document.getElementById('vendor_id_edit').style.display='block';
            }               
            //$('#basicvalCodeModal').find('option').not('#delivery_mode_id :selected').prop('disabled', true);
            //popreqid =  reqid;       
            //alert($('#vendorpopup').val());       
        });
        $form.validate();
    });
    
}
    function deleteIot(id)
    {
        var dec = confirm("Are you sure you want to Delete ?");
        if ( dec == true )
            $.ajax({
                url: '/orders/deleteiot/' + id,
                type:'GET',
                success: function(result)
                {
                    if(result != 0)
                    {
                      //alert('IoTs Succesfully Deleted !!');
                      document.getElementById('failureid').style.display='none';
                      document.getElementById('successid').style.display='block';
                      document.getElementById('successid').innerHTML='IoTs Succesfully Deleted !!';
                      $("#successid").addClass("callout callout-success");
                      $("#failureid").removeClass("callout-info");
                      $("#failureid").removeClass("callout-danger");
                      $("#failureid").removeClass("callout-warning");
                      console.log(result);
                      showIotCodes();
                      statusmessage();
                      document.getElementById('available_quantity').innerHTML='Available Codes : '+result;
                      $('#quantity_present').val(result);
                      $('#quantityplaced').val(result);
                      //added to disable the quantityplaced field
                      if ($('#quantityplaced').val() !== 0){
                        $('#quantityplaced').prop('readonly', false);
                      }
                      //added to disable the quantityplaced field                        
                      
                    }else{
                      //alert('Unable to Delete.');
                      document.getElementById('successid').style.display='none';
                      document.getElementById('failureid').style.display='block';
                      document.getElementById('failureid').innerHTML='Unable to Delete';
                      $("#failureid").addClass("callout callout-danger");
                      $("#successid").removeClass("callout-success");
                      $("#failureid").removeClass("callout-info");
                      $("#failureid").removeClass("callout-warning");
                      statusmessage();
                    }
                },
                error: function(err){
                    console.log('Error: '+err);
                },
                complete: function(data){
                    console.log(data);
                }                
            });
    }
  
  function vendordropdown(id){
   
    console.log(id);
    $("#vendorid").val($("#vendorid option:first").val());
    var test=id.substring(16); 
    var delivery_mode = $('#deliverymode').val();
    var delivery_name = delivery_mode.substr( delivery_mode.indexOf('_') + 1 );
    //alert(delivery_name);
    //if(($('#replacecode').text()=='Downloadable') || ($('#replacecode').text()=='Print and Deliver'))
    if(delivery_name=='Downloadable' || delivery_name=='Print and Deliver')
    {
      document.getElementById('replacecode').style.display='block';
      document.getElementById('vendorid').style.display='none';
    }
    else
    {
        document.getElementById('replacecode').style.display='none';
        document.getElementById('vendorid').style.display='block';
        vendorid = document.getElementById('vendorid');
        /*myOption = document.createElement("option");
        myOption.text = "Select";
        myOption.value = "";
        vendorid.appendChild(myOption);*/
    }

    var delivery_mode_id = $('#delivery_mode_id').val();
   // var delivery_name_edit = $('#delivery_mode_id').text();
    var delivery_name_edit = $('#delivery_mode_id option:selected').html();

    //alert(delivery_name_edit);
    //if(($('#replacecode').text()=='Downloadable') || ($('#replacecode').text()=='Print and Deliver'))
    if(delivery_name_edit=='Downloadable' || delivery_name_edit=='Print and Deliver')
    {
        document.getElementById('replacecodeEdit').style.display='block';
        document.getElementById('vendor_id_edit').style.display='none';
    }
    else
    {
        document.getElementById('replacecodeEdit').style.display='none';
        document.getElementById('vendor_id_edit').style.display='block';
        vendor_id = document.getElementById('vendor_id');
       /* myOption1 = document.createElement("option");
        myOption1.text = "Select";
        myOption1.value = "";
        myOption1.selected="selected";
        vendor_id.appendChild(myOption1);  */      
    }

  }  
  function showPostPaid()
  {
    $("#postpaidDiv").css("display","block");
    $("#paymentsDiv").css("display","none");
  }
  function showPayments(val)
  {
    /*if(val=='Online')
    {*/
      $("#postpaidDiv").css("display","none");
      $("#paymentsDiv").css("display","block");
    /*}
    else
    {
      $("#postpaidDiv").css("display","block");
      $("#paymentsDiv").css("display","none");
    }*/
    $.ajax
    (
      {
        url: "/orders/getPayments", 
        type: "GET", 
        data: "type=" +val,
        success: function(reponse)
        {
          
        },
        error:function()
        {
          //console.log("AJAX request was a failure");
        }   
      }
    );

  }
  
  //initialize file upload button
      $('.btn-file :file').on('fileselect', function(event, numFiles, label) {
        
        var input = $(this).parents('.input-group').find(':text'),
          log = numFiles > 1 ? numFiles + ' files selected' : label;
        
        if( input.length ) {
          input.val(log);
        } else {
          if( log ) alert(log);
        }
        
      });

  
    function validateMe(tabName, closingId, nextId)
    {
        //var availqty = '<?php echo $finaldata[0]->review_qty; ?>'; 
        var availqty=document.getElementById("quantityplaced").value;
        if(availqty!=0)
        {
             document.getElementById('successid').style.display='none';
              document.getElementById('failureid').style.display='block';
              document.getElementById('failureid').innerHTML='Codes are not assigned completely';
               $("#failureid").addClass("callout callout-warning");
               $("#failureid").removeClass("callout-success");
                $("#failureid").removeClass("callout-info");
               $("#failureid").removeClass("callout-danger");
               statusmessage();
        }
        else
        {           

        var temp = true;
        if('billing' == tabName)
        {            
            var fv   = $('#form').data('bootstrapValidator');
            switch (tabName) {
                case 'billing':
                    billingFields = ['bill_first_name', 'bill_last_name', 'bill_address', 'bill_city', 'bill_country_id', 'bill_zone_id', 'bill_phone_no'];
                    $.each(billingFields, function(element, value){
                        var isValidStep = fv.validateField(value).isValid();
                        var isValidField = fv.isValidField(value);
                        if (isValidField === false || isValidField === null) {                
                            // Do not jump to the target tab
                            temp = false;
                        }else{
                            if(temp)
                            {
                                temp = true;
                            }                
                        }
                    });
                    break;
                default:
                    break;
            }
        }
        if(temp)
        {
            if('billing' == tabName)
            {
                $('.resp-accordion.hor_1').show().trigger('click');
            }
            $('#'+closingId).hide();
            $('#'+nextId).show();
        }
      }
    }
  </script>   
@stop