@extends('layouts.default')

@extends('layouts.header')

@extends('layouts.sideview')

@section('content')
<style>
.nav-tabs-custom>.nav-tabs.pull-right>li{font-weight:bold !important;}
/*progressbar*/
#progressbar {
	margin-bottom: 30px;
	overflow: hidden;
	/*CSS counters to number the steps*/
	counter-reset: step;
}
#progressbar li {
	list-style-type: none;
	color: #333;
	text-transform: uppercase;
	font-size: 9px;
	width: 33.33%;
	float: left;
	position: relative;
	text-align:center;
}
#progressbar li:before {
	content: counter(step);
	counter-increment: step;
	width: 20px !important;
	line-height: 20px;
	display: block;
	font-size: 10px;
	color: #333;
	background: white;
	border-radius: 30px;
	margin: 0 auto 5px auto;
	position: relative;
	z-index:1;
}
/*progressbar connectors*/
#progressbar li:after {
	content: '';
	width: 100%;
	height: 2px;
	background-color: #222d32;
	position: absolute;
	left:-50%;
	top: 9px;
	z-index: 0; /*put it behind the numbers*/
	background-color:#222d32;
}
#progressbar li:first-child:after {
	/*connector not needed before the first step*/
	content: none; 
}
/*marking active/completed steps green*/
/*The number of the step and the connector before it = green*/
#progressbar li.active:before,  #progressbar li.active:after{
	background: #3c8dbc;
	color: white;
}

</style>

       <!-- /tile body -->
 <div class="main">

  <div class="container-fluid">
  <a href="/reportapis/PrintInvoice/{{$order_details[0]->order_id}}/1" target="_blank" data-toggle="tooltip" title="" class="btn btn-info" data-original-title="Print Invoice"><i class="fa fa-print"></i></a>
  <a href="/reportapis/PrintInvoice/{{$order_details[0]->order_id}}/0" target="_blank" data-toggle="tooltip" title="" class="btn btn-info" data-original-title="Print Shipping List"><i class="fa fa-truck"></i></a>
  
  <div class="pull-right ">
    <a href="/reportapis/index" class="refresh text-muted btn btn-primary" data-toggle="tooltip" title="" data-original-title="Back"><i class="fa fa-arrow-circle-left"></i></a>                  
  </div>    
 
  </div>
  


<div class="nav-tabs-custom" style="cursor: move;"> 
  <!-- Tabs within a box -->
  <ul class="nav nav-tabs pull-right ui-sortable-handle">
    <li><a href="#history" data-toggle="tab">History</a></li>
    <li class="active"><a href="#orders" data-toggle="tab">Order Details</a></li>
    <li class="pull-left header"><i class="fa fa-inbox"></i> Order</li>
  </ul>

  <div class="tab-content no-padding"> 
   
 <div class="chart tab-pane active">



  <section id="rootwizard" class="tab tbable tile">

        

        
        <!-- tile body -->
        <div class="tile-body">

        <div class="tab-content"> 

        <div class=" col-lg-12 tab-pane active" id="orders">   
         {{Form::open(array('url' => 'ebaydeveloper/UpdateAllOrders/'.$order_details[0]->order_id))}}
        {{ Form::hidden('_method', 'POST') }}    
<div class="row">

<div class="col-md-2">
<!-- <div class="box box-solid">
<div class="box-header with-border">
<h3 class="box-title">Order View</h3>
</div>
<div class="box-body no-padding" style="display: block;">
<ul class="nav nav-pills nav-stacked">


<li class="active"><a href="#">Information</a></li>
<li><a href="#">Invoices</a></li>
<li><a href="#">Credit Memos</a></li>
<li><a href="#">Shipments</a></li>
<li><a href="#">RMA</a></li>
<li><a href="#">Comments History</a></li>
<li><a href="#">Transactions</a></li>
</ul>
</div>
</div> -->
</div>


<div class="col-md-12">

<div class="row">


<div class="col-md-6">

<div class="box box-primary">
<div class="box-header">
<h3 class="box-title">Order <a href="#"># {{$order_details[0]->order_id}}</a></h3>
</div>


<div class="box-body">



<div class="row invoice-info">

<div class="col-sm-6 invoice-col">
<address>
Order Date<br>
Order Status<br>
Purchased From<br>
eRp Order Id<br>
</address>
</div>
<div class="col-sm-6 invoice-col">
@foreach($order_details as $details)
<address>
<strong>{{$details->order_date}}</strong><br>
<strong>{{$details->channel_order_status}}</strong><br>
<strong>{{$details->city}}</strong><br>
@if(empty($details->erp_order_id))
<strong>NA</strong><br>
@else
<strong>{{$details->erp_order_id}}</strong><br>
@endif
</address>
@endforeach
</div>
</div>



</div>



</div>

</div>
<div class="col-md-6">


<div class="box box-primary" style="min-height:165px;">
<div class="box-header">
<h3 class="box-title">Account Information</h3>
</div>


<div class="box-body">



<div class="row invoice-info">
<div class="col-sm-6 invoice-col">
<address>
Customer Name<br>
Email<br>
Phone<br>
</address>
</div>
<div class="col-sm-6 invoice-col">
<address>
@foreach($order_details as $customer_det)
<strong><a href="#">{{$customer_det->name}}</a></strong><br>
@if(empty($customer_det->email))
<strong>Not Provided</strong><br>
@else
<strong><a href="#">{{$customer_det->email}}</a></strong><br>
@endif
<strong>{{$customer_det->phone}}</strong><br>
@endforeach
</address>
</div>
</div>



</div>



</div>


</div>

</div>

<div class="row">
<div class="col-md-6">

<div class="box box-primary" style="min-height:244px;">
<div class="box-header">
<h3 class="box-title">Channel</h3>
@foreach($order_details as $ch) 
</div>
<strong><img src="{{$ch->channel_logo}}"  width="100"></strong><br>

<div class="box-body">



<div class="row invoice-info">

<div class="col-sm-4 invoice-col">
<address>
Channel Name:<br>
Channel Url:<br>
Channel orderId:<br>
</address>
</div>
<div class="col-sm-6 invoice-col">
<p>

<strong>{{$ch->channnel_name}}</strong><br>
<strong><a href="{{$ch->site_url}}">{{$ch->site_url}}</a></strong><br>
<strong>{{$ch->channel_order_id}}</strong><br>

@endforeach
</p>
</div>
</div>




</div>



</div>

</div>

<div class="col-md-6">

<div class="box box-primary" style="min-height:244px;">
<div class="box-header">
<h3 class="box-title">Payment Information</h3>
@foreach($order_details as $ch) 

</div>


<div class="box-body">



<div class="row invoice-info">

<div class="col-sm-4 invoice-col">
<address>
Payment Method:<br>
Payment Status:<br>
Payment Currency:<br>

</address>
</div>
<div class="col-sm-6 invoice-col">
<p>
 

<strong>{{$ch->payment_method}}</strong><br>
<strong>{{$ch->payment_status}}</strong><br>
<strong>{{$ch->payment_currency}}</strong><br>

@endforeach
</p>
</div>
</div>




</div>



</div>

</div>
</div>

<div class="row">
<div class="col-md-6">



</div>

<div class="col-md-12">

<div class="box box-primary">
<div class="box-header">
<h3 class="box-title">Shipping Address</h3>
</div>


<div class="box-body">



<div class="row invoice-info">

<div class="col-sm-12 invoice-col">
<address>
@foreach($order_details as $det)
{{$det->name}}<br>
{{$det->address1}}<br>
{{$det->address2}}<br>
{{$det->city}}<br>
{{$det->state}}<br>
{{$det->country}}<br>
{{$det->pincode}}<br>
@endforeach
</address>
</div>

</div>



</div>



</div>

</div>
</div>

<div class="row">
<div class="box box-primary">
<div class="box-header">
<h3 class="box-title">Items Ordered</h3>
</div>


<div class="box-body">


<table class="table table-bordered">
<tbody>

<tr>
<th>Product Name</th>
<th>Quantity</th>
<th>Price</th>
<th>Subtotal</th>
<th>Tax</th>
<th>Total</th>
</tr>

<tr>
<?php $main_tot=0; ?> 
  @foreach($final_product_array as $result)
  <tr>
  <td>{{$result['product_name'] }}</td>
  <td  class="text-right">{{$result['quantity']}}</td>
  <td  class="text-right">{{$result['price']}}</td>
  <td  class="text-right">{{number_format($result['subtotal'],2,'.',',')}}</td>
  <td  class="text-right">{{number_format($result['tax'],2,'.',',')}}</td>
  <td  class="text-right">{{number_format($result['subtotal']+$result['tax'],2,'.',',')}}</td>
  <?php
  
  $sub_tot = $result['subtotal']+$result['tax']+$order_details[0]->shipping_cost; 
  $main_tot=$main_tot+$sub_tot; ?> 
  </tr>
  @endforeach
  <tr>
    <td colspan="5" class="text-right">Shipping Cost :</td>
    <td colspan="6" class="text-right"><?php echo !is_null($order_details[0]->shipping_cost) ? $order_details[0]->shipping_cost : '0.00'; ?></td>
  </tr>
  <tr>
    <td colspan="5" class="text-right">Total :</td>
    <td colspan="6" class="text-right"><?php echo number_format($main_tot,2,'.',','); ?></td></tr>
  </tr>

</tbody>

</table>



</div>

</div>
</div>

<div class="row">



</div>
</div>

</div> 
                
                {{ Form::close() }}  
                </div>
        
          <div class="col-lg-12 tab-pane" id="payments">
            <input type="hidden" id="test_id" name="test_id" value="">
             <h4>Payments Of Order <button class="btn pull-right " id ="add_payment" style="margin-bottom:10px" data-toggle="modal" data-target="#basicvalCodeModal">Add Payment</button></h4>
              <div>
          
            </div>
              <div id="paymentsgrid">
              </div>
               
               </br>

             <div class="modal fade" id="basicvalCodeModal" tabindex="-1" role="dialog" aria-labelledby="basicvalCode" aria-hidden="true">
                    <div class="modal-dialog wide">
                      <div class="modal-content">
                        <div class="modal-header">
                          <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                          <h4 class="modal-title" id="basicvalCode">Adding a New Payment</h4>
                        </div>
                        <div class="modal-body">
                         
                            {{ Form::open(array('url' => 'orders/paymentStore/', 'id' => 'form')) }}
                            {{ Form::hidden('_method', 'POST') }}
                            <!-- /tile header -->
                  <div id="parentVerticalTab">
                    <ul class="resp-tabs-list hor_1">
                        <li><i class="fa fa-gift"></i>RTGS/NEFT</li>
                        <li><i class="fa fa-credit-card"></i>Cheque</li>
                        <li><i class="fa fa-desktop"></i>DD</li>
                        <li><i class="fa fa-credit-card"></i>Credit Card</li>
                        <li><i class="fa fa-credit-card"></i>Debit Card</li>
                        <li><i class="fa fa-inr"></i>Net Banking</li>
                        <li><i class="fa fa-gift"></i>Wallet</li>
                    </ul>
                    <div class="resp-tabs-container hor_1">
                        <div>
                            <div class="row">
                            <div class="form-group col-sm-6">
                              <label for="exampleInputEmail">Reference Number</label>
                              <input type="text" class="form-control" placeholder="Reference Number" name="trans_reference_no" style="width:200px;" id="ref_number">
                            </div>
                            <div class="form-group col-sm-6">
                              <label for="exampleInputEmail">Payee Bank</label>
                              <input type="text" class="form-control" placeholder="Payee Bank Name" name="payee_bank" style="width:200px;" id="payee_bank_name">
                            </div>
                          </div>
                          <div class="row">
                            <div class="form-group col-sm-6">
                              <label for="exampleInputEmail">IFSC CODE</label>
                              <input type="text" class="form-control" placeholder="IFSC Code" name="ifsc_code" style="width:200px;" id="ifsc_code">
                            </div>
                            <div class="form-group col-sm-6">
                              <label for="exampleInputEmail">Payment Date </label>
                              <input type="text" class="form-control" value="<?php echo date('Y-m-d'); ?>" name="payment_date" style="width:200px;" id="payment_date" readonly="true">
                            </div>
                          </div>
                          <div class="row">
                            <div class="form-group col-sm-6">
                              <label for="exampleInputEmail">Amount</label>
                              <input type="number" min=0 step="any" class="form-control"placeholder="Amount" name="amount" style="width:200px;" id="amount">
                            </div>
                           <div class="form-group col-sm-6">
                                    <label for="exampleInputEmail"></label>
                                    <input type="hidden" class="form-control" placeholder="Payee Bank Name" name="payment_type" style="width:200px;" id="payment_type" value="16001">
                              </div>
                          </div>                          
                        </div>
                        <div>
                           2
                        </div>
                        <div>
                           3
                        </div>
                        <div>
                            4
                        </div>
                        <div>
                            5
                        </div>
                        <div>
                           6
                        </div>
                    </div>
                </div>
              {{ Form::submit('Proceed', array('class' => 'btn btn-primary')) }}
              {{ Form::close() }}

                        </div>
                      </div><!-- /.modal-content -->
                    </div><!-- /.modal-dialog -->
                  </div><!-- /.modal -->




                   <!-- Modal -->
                  <div class="modal fade" id="basicvalCodeModal1" tabindex="-1" role="dialog" aria-labelledby="basicvalCode" aria-hidden="true">
                    <div class="modal-dialog wide">
                      <div class="modal-content">
                        <div class="modal-header">
                          <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                          <h4 class="modal-title" id="basicvalCode">Edit This Payment</h4>
                        </div>
                        <div class="modal-body">
                        {{ Form::open(array('url' => 'orders/paymentUpdate/', 'data-url' => '/orders/paymentUpdate/','id'=>'editForm')) }}
                        {{ Form::hidden('_method', 'PUT') }}
                          <div id="parentVerticalTab1">
                            <ul class="resp-tabs-list hor_1">
                                <li data-payment-type="16001"><i class="fa fa-gift"></i>RTGS/NEFT</li>
                                <li data-payment-type="16002"><i class="fa fa-credit-card"></i>Cheque</li>
                                <li data-payment-type="16003"><i class="fa fa-desktop"></i>DD</li>
                                <li data-payment-type="16004"><i class="fa fa-credit-card"></i>Credit Card</li>
                                <li data-payment-type="16005"><i class="fa fa-credit-card"></i>Debit Card</li>
                                <li data-payment-type="16006"><i class="fa fa-inr"></i>Net Banking</li>
                                <li data-payment-type="16007"><i class="fa fa-gift"></i>Wallet</li>
                            </ul>
                            <div class="">
                                <div data-payment-type="16001">
                                <div style="clear:both"></div>
                                    <div class="row">
                                    <div class="form-group col-sm-6">
                                      <label for="exampleInputEmail">Reference Number</label>
                                        <input type="text" class="form-control required" placeholder="Reference Number" name="trans_reference_no" id="trans_reference_no">
                                    </div>
                                    <div class="form-group col-sm-6">
                                      <label for="exampleInputEmail">Payee Bank</label>
                                        <input type="text" class="form-control required" placeholder="Payee Bank Name" name="payee_bank" id="payee_bank">
                                    </div>
                                  </div>
                                  <div class="row">
                                    <div class="form-group col-sm-6">
                                      <label for="exampleInputEmail">IFSC CODE</label>
                                        <input type="text" class="form-control required" placeholder="IFSC Code" name="ifsc_code" id="ifsc_code">

                                    </div>
                                    <div class="form-group col-sm-6">
                                      <label for="exampleInputEmail">Payment Date </label>
                                      <input type="text" class="form-control" placeholder="Payment Date" name="payment_date" id="payment_date">
                                    </div>
                                  </div>
                                  <div class="row">
                                    <div class="form-group col-sm-6">
                                      <label for="exampleInputEmail">Amount</label>
                                       <input type="text" class="form-control required" placeholder="Amount" name="amount"  id="amount">
                                    </div>
                                  <div class="form-group col-sm-6">
                                      <label for="exampleInputEmail"></label>
										<input type="hidden" class="form-control" placeholder="Payee Bank Name" name="payment_type" id="payment_type" value="RTGS/NEFT" >
                                    </div>

                                  </div>                          
                                </div>
                               


                                <div >
                                    
                                </div>
                                <div>
                                   
                                </div>
                                <div>
                                   
                                </div>
                                <div>
                                   
                                </div>
                                <div>
                                   
                                </div>
                            </div>
                          </div>
                        {{ Form::submit('Proceed', array('class' => 'btn btn-primary')) }}
                        {{ Form::close() }}
                        </div>
                      </div><!-- /.modal-content -->
                    </div><!-- /.modal-dialog -->
                  </div><!-- /.modal -->
             
         </div>
            
          <div class="col-lg-12 tab-pane" id="shippings">
           <table class="table table-bordered">
                      
                      <tbody>
                        @foreach($order_details as $details)
                        <tr>
                          <td>Buyer Name:</td>
                          <td>{{$details->name}}</td>
                        </tr>
                         <tr>
                          <td>Address1:</td>
                          <td>{{$details->address1}}</td>
                        </tr>
                         <tr>
                          <td>Address2:</td>
                          <td>{{$details->address2}}</td>
                        </tr>
                        <tr>  
                          <td>City:</td>
                          <td>{{$details->city}}</td>
                        </tr>
                        <tr>
                          <td>State:</td>
                          <td>{{$details->state}}</td>
                        </tr>
                        <tr>
                          <td>Country:</td>
                          <td>{{$details->country}}</td>
                        </tr>
                        <tr>
                          <td>Pincode:</td>
                          <td>{{$details->pincode}}</td>
                        </tr>
                        <tr>
                          <td>phone Number:</td>
                          <td>{{$details->phone}}</td>
                        </tr>
                        <tr>
                          <td>email:</td>
                          <td>{{$details->email}}</td>
                        </tr>
                        
                      @endforeach
                      </tbody>
                    </table>
                </div>
          
          <div class="col-lg-12 tab-pane" id="products">
           <section class="tile">


                  <!-- tile header -->
                  <div class="tile-header">
                   <h4>Total Order</h4>
                  </div>
                  <!-- /tile header -->

                  <!-- tile body -->
                  <div class="tile-body nopadding">
                    
                    <table class="table table-bordered">
                      <thead>
                        <tr>
                          
                          <th>Product Name</th>
                          <th>Quantity</th>
                          <th>Price</th>
                          <th>Subtotal</th>
                          <th>Tax</th>
                          <th>Total</th>
                        </tr>
                      </thead>
                      <tbody>
                      <?php $main_tot=0; ?> 
                        @foreach($final_product_array as $result)
                        <tr>
                          <td>{{$result['product_name'] }}</td>
                          <td  class="text-right">{{$result['quantity']}}</td>
                          <td  class="text-right">{{$result['price']}}</td>
                          <td  class="text-right">{{number_format($result['subtotal'],2,'.',',')}}</td>
                          <td  class="text-right">{{number_format($result['tax'],2,'.',',')}}</td>
                          <td  class="text-right">{{$result['subtotal']+$result['tax']}}</td>
                          <?php
                          $sub_tot = $result['subtotal']+$result['tax']; 
                          $main_tot=$main_tot+$sub_tot; ?> 
                        </tr>
                       @endforeach
                       <tr>
                        <td colspan="5" class="text-right">Total :</td>
                        <td colspan="6" class="text-right"><?php echo $main_tot; ?></td></tr>
                      </tbody>
                    </table>

                  </div>
                  <!-- /tile body -->
               </section>  

                  
          </div>



          <div class="col-lg-12 tab-pane" id="history">
             {{Form::open(array('url' => 'ebaydeveloper/UpdateAllOrders/'.$order_details[0]->order_id,'id'=>'order_status'))}}
        {{ Form::hidden('_method', 'POST') }} 
            <section class="tile">
                  

                  <!-- tile header -->
                  <div class="tile-header">                    
                     <br>
                  </div>
                  <!-- /tile header -->

                  <!-- tile body -->
                  <div class="tile-body nopadding">
                    
                    

                  </div>
                  <!-- /tile body -->
               </section>
                <!-- /tile -->
        
         @if(empty($cust_id))

          <div class="row">
           <div class="form-group col-sm-6">
             
             <h4>Order status</h4>
             <div class="input-group">
                  <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
                  <div id="selectbox"> 
              
            <select class="form-control" id="order_status_id" name="order_status_id">
              <option value="">Select Status</option>
              @foreach($order_status as $key=>$status)
              <option  value="{{$status->status_id}}">{{$status->status_value}}</option>
              @endforeach
            </select>
              </div>
               <input type="hidden" id="post_channel_id" name="post_channel_id" value="nikhil kishore"> 
                </div>
              </div>
            </div>
           <div class="row">
        <div class="form-group col-sm-6">
                  <label for="exampleInputEmail">Comments</label>
                  <div class="input-group">
                    <span class="input-group-addon addon-red"><i class="fa fa-newspaper-o"></i></span>
                    <textarea class="form-control required" id="comments" name="comments"  rows="3"></textarea>
                    
                  </div>
                              
                </div>

        </div>
        @endif
       
           <div class="row">
      
                <div class="navbar-fixed-bottom" role="navigation">
                  <div id="content" class="col-md-12">
                  
             <input type="hidden" name="status_id" id="status_id" value="" />
                  
                  <button type="submit" name="action" value="action" onclick="submitupdate()"class="btn btn-primary ">Submit</button>
                  
                  <button type="submit" name="redirect" value="redirect" class="btn btn-primary ">Close</button>
           </div>
        </div>
         
            </div>
                </div>

              {{ Form::close() }}
            
            

        </div>
      </section>
</div>
 </div>
</div>
 @stop

@section('style')
    {{HTML::style('jqwidgets/styles/jqx.base.css')}}
    {{HTML::style('css/easy-responsive-tabs.css')}} 
     
@stop

@section('script')
    {{HTML::script('js/easyResponsiveTabs.js')}}
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
<script>
$(function(){
$('#order_status').bootstrapValidator({
        
        messages:'This value is not valid',
            feedbackIcons: {
                valid: 'glyphicon glyphicon-ok',
                invalid: 'glyphicon glyphicon-remove',
                validating: 'glyphicon glyphicon-refresh'
            },

        fields: {
                order_status_id: {
                    validators: {
                    callback: {
                        message: 'Order Status is required.',
                        callback: function(value, validator, $field) {
                            var options = $('[id="order_status_id"]').val();
                            return (options != '');
                        }
                    },                       
/*                      notEmpty: {
                          message: 'Order Status is required.'
                        }*/
                    }
                }
        }
      });
});

function submitupdate(){
  
  alert('Order has been Successfully Updated');
}
</script>

@stop