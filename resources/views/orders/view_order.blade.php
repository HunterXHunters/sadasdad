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
  <a href="/orders/printInvoice/{{$id}}/{{$ima_id}}/1" target="_blank" data-toggle="tooltip" title="" class="btn btn-info" data-original-title="Print Invoice"><i class="fa fa-print"></i></a>
  <a href="/orders/printInvoice/{{$id}}/{{$ima_id}}/2" target="_blank" data-toggle="tooltip" title="" class="btn btn-info" data-original-title="Print Shipping List"><i class="fa fa-truck"></i></a>
  @if($result[0]->name !='Approve')
  <a href="/orders/editmyOrder/{{$id}}/{{$ima_id}}" data-toggle="tooltip" title="" class="btn btn-primary" data-original-title="Edit"><i class="fa fa-pencil"></i></a>  
  @endif
  <div class="pull-right ">
    <a href="#" class="refresh text-muted btn btn-primary" data-toggle="tooltip" title="" data-original-title="Back"><i class="fa fa-arrow-circle-left"></i></a>                  
  </div>    
  <form id="msform">
   
          <ul id="progressbar">
           @if($status_update=="Placed")
            <li class="active">Placed Order</li>
            <li >Admin Approval</li>
            <li >Delivered Order</li>
            @endif
            
            @if($status_update=="Approve")
            <li class="active">Placed Order</li>
            <li class="active">Admin Approval</li>
            <li >Delivered Order</li>
            @endif
             
            @if($status_update=="Processing")
            <li class="active">Placed Order</li>
            <li class="active">Processing</li>
            <li >Delivered Order</li>
            @endif
            

            @if($status_update=="Delivered" )
            <li class="active">Placed Order</li>
            <li class="active">Admin Approval</li>
            <li class="active">Delivered Order</li>
            @endif

            @if($status_update=="Shipped" )
            <li class="active">Placed Order</li>
            <li class="active">Admin Approval</li>
            <li class="active">Shipped Order</li>
            @endif


            @if($status_update=="Canceled")
            <li class="active">Placed Order</li>
            <li class="active">Admin Approval</li>
            <li class="active">Cancelled Order</li>
            @endif

            @if($status_update=="Complete")
            <li class="active">Placed Order</li>
            <li class="active">Admin Approval</li>
            <li class="active">Completed Order</li>
            @endif

            @if($status_update=="IoTs Generated")
            <li class="active">Placed Order</li>
            <li class="active">Admin Approval</li>
            <li class="active">IoTs Generated</li>
            @endif
          </ul>
    </form>
  </div>
  


<div class="nav-tabs-custom" style="cursor: move;"> 
  <!-- Tabs within a box -->
  <ul class="nav nav-tabs pull-right ui-sortable-handle">
    <li><a href="#history" data-toggle="tab">History</a></li>
    <li><a href="#products" data-toggle="tab">Products</a></li>
    <li ><a href="#payments" data-toggle="tab">Payment Details</a></li>
    <li><a href="#shippings" data-toggle="tab">Delivery Details</a></li>
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
         {{Form::open(array('url' => 'orders/orderStatus/'.$id))}}
        {{ Form::hidden('_method', 'POST') }}    
              <!-- tile -->
                <section class="tile">


                  <!-- tile header -->
                  <div class="row">
                  <input type="hidden" name="approve_status_id" id="approve_status_id" value="{{$approved_status}}" />

                  @if(empty($cust_id))
                    @if($result[0]->name=='Placed')
                      <input class="btn btn-primary margin pull-right" name="action" type="submit" value="Approve">
                    @endif
                    @if($result[0]->name=='Delivered')  
                      <input class="btn bg-purple margin pull-right" type="submit" value="ReActivete">
                    @endif
                  @endif                
                  </div>
                  <!-- /tile header -->
                  <!-- tile body -->
                  <div class="tile-body nopadding">
                    
                    <table class="table table-bordered">
                      
                      <tbody>
                       
                        <tr>
                          <td>Order Number:</td>
                          <td>#{{$result[0]->order_number}}</td>
                        </tr>
                         <tr>
                          <td>Invoice Number:</td>
                          <td>{{$id}}</td>
                        </tr>
                         <tr>
                          <td>Customer:</td>
                          <td>{{$customer_name}}</td>
                        </tr>
                        <tr>
                          <td>Customer Group:</td>
                          <td></td>
                        </tr>
                        <tr>
                          <td>E-mail:</td>
                          <td>{{$result[0]->email}}</td>
                        </tr>
                        <tr>
                          <td>Telephone:</td>
                          <td>{{$result[0]->mobile_number}}</td>
                        </tr>
                        <tr>
                          <td>Total:</td>
                          <td>Rs.{{$result[0]->total}}</td>
                        </tr>
                        <tr>
                          <td>Order Status:</td>
                          <td>{{$result[0]->name}}</td>
                        </tr>
                        <tr>
                          <td>IP Address:</td>
                          <td>10.175.8.34</td>
                        </tr>
                        <tr>
                          <td>Date Added:</td>
                          <td>{{$result[0]->dateadded}}</td>
                        </tr>
                        <tr>
                          <td>Date Modified:</td>
                          <td>{{$result[0]->datemodified}}</td>
                        </tr>
                      
                      </tbody>
                    </table>

                  </div>
                  <!-- /tile body -->
               </section>
                <!-- /tile -->
                {{ Form::close() }}  
                </div>
        
          <div class="col-lg-12 tab-pane" id="payments">
            <input type="hidden" id="test_id" name="test_id" value="{{$id}}">
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
                         
                            {{ Form::open(array('url' => 'orders/paymentStore/'.$id, 'id' => 'form')) }}
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
            <section class="tile">


					<br>
                  <!-- tile body -->
                  <div class="tile-body nopadding">
                    
                    <!-- <table class="table table-bordered">
                      
                      <tbody>
                       
                        <tr>
                          <td>First Name</td>
                          <td width="10">:</td>
                          <td>{{$result[0]->shipping_firstname}}</td>
                        </tr>
                         <tr>
                          <td>Last Name</td>
                          <td>:</td>
                          <td>{{$result[0]->shipping_lastname}}</td>
                        </tr>
                         <tr>
                          <td>Company</td>
                          <td>:</td>
                          <td>eSeal</td>
                        </tr>
                        <tr>
                          <td>Address 1</td>
                          <td>:</td>
                          <td>{{$result[0]->shipping_address_1}}</td>
                        </tr>
                        <tr>
                          <td>City</td>
                          <td>:</td>
                          <td>{{$result[0]->shipping_city}}</td>
                        </tr>
                        
                        <tr>
                          <td>Country</td>
                          <td>:</td>
                          <td>{{$country}}</td>
                        </tr>
                        <tr>
                          <td>Zone</td>
                          <td>:</td>
                          <td>{{$zone}}</td>
                        </tr>
                        <tr>
                          <td>Payment Method</td>
                          <td>:</td>
                          <td>{{$payment_method}}</td>
                        </tr>
                        
                      </tbody>
                    </table> -->

                  
                  <div class="tile-header">
                   <h4>Delivery Details</h4>
                  </div>
                     <div class="tile-body nopadding">
                    
                    <table class="table table-bordered">
                      <thead>
                        <tr>
                        <th>Type Of Delivery</th>
                        <th>Delivery To</th>
                        <!-- <th>Deliver Address</th> -->
                        <th>Quantity of Codes</th>
                        </tr>
                      </thead>
                      <tbody>
                        @foreach($delivery_details as $key=>$result)
                        <tr>
                          <td width="20%">{{$result->delivery_mode}}</td>
                          @if($result->vendor_id)
                          <td width="30%">{{$result->firstname}}-{{$result->lastname}}-{{$result->location_address}}</td>
                          @else
                          <td width="30%">{{$result->custfname}}-{{$result->custlname}}</td>
                          @endif
                         <!--  <td  class="text-right" width="20%">{{$result->firstname}}</td> -->
                          <td width="20%">{{$result->quantity}}</td>
                       </tr>
                       @endforeach
                       
                      </tbody>
                    </table>

                  </div>

                  </div>
                  <!-- /tile body -->
               </section>
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
                        @foreach($customer_details as $key=>$result)
                        <tr>
                          <td>{{$result->name}}</td>
                          <td  class="text-right">{{$result->quantity}}</td>
                          <td  class="text-right">{{$result->price}}</td>
                          <td  class="text-right">{{number_format($result->total,2,'.',',')}}</td>
                          <td  class="text-right">{{number_format($result->tax,2,'.',',')}}</td>
                          <td  class="text-right">{{$result->total+$result->tax}}</td>
                          <?php
                          $sub_tot = $result->total+$result->tax; 
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
             {{Form::open(array('url' => 'orders/orderStatus/'.$id, 'id'=>'order_status'))}}
        {{ Form::hidden('_method', 'POST') }} 
            <section class="tile">
                  

                  <!-- tile header -->
                  <div class="tile-header">                    
                     <br>
                  </div>
                  <!-- /tile header -->

                  <!-- tile body -->
                  <div class="tile-body nopadding">
                    
                    <table class="table table-bordered">
                      <thead>
                        <tr>
                          
                          <th>Date Added</th>
                          <th>Comment</th>
                          <th>Status</th>
                          <th>Customer Notified</th>
                        </tr>
                      </thead>
                      <tbody>
                        @foreach($order_history as $key=>$value)
                        <tr>
                          <td>{{$value->dateadded}}</td>
                          <td>{{$value->comment}}</td>
                          <td>{{$value->name}}</td>
                          @if($value->notify==1)
                          <td>Yes</td>
                          @else
                          <td>No</td>
                          @endif
                        </tr>
                       @endforeach
                      </tbody>
                    </table>

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
              @foreach($admin as $key=>$status)
              <option  value="{{$status->value}}" >{{$status->name}}</option>
              @endforeach
              </select>
        
         </div>
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
                  @if(empty($cust_id))  
                  <button type="submit" name="action" value="action" class="btn btn-primary ">Submit</button>
                   @endif
                  <button type="submit" name="redirect" value="redirect" class="btn btn-primary ">Close</button>
           </div>
        </div>
         
            </div>
                </div>

              {{ Form::close() }}
            
            

        </div>
      </section>
        <!-- /tile body -->
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
    <script type="text/javascript">
    $(document).ready(function () 
        {           
         viewPayments();
        }); 
    </script>
<script type="text/javascript">
$('#order_status_id').change(function(){ $('#status_id').val($('#order_status_id').val()); })
</script>
<script type="text/javascript">
    function viewPayments()
    {
        var id=document.getElementById('test_id').value;
            //alert(id);
            var url = "/orders/viewPayments/"+id;
            //alert(url);
            var source =
            {
                datatype: "json",
                datafields: [
                    { name: 'sno', type: 'integer' },
                    { name: 'payment_type', type: 'string' },
                    { name: 'reference_no', type: 'integer' },
                    { name: 'ifsc_code', type: 'integer' },
                    { name: 'amount', type: 'decimal' },
                    { name: 'payment_date', type: 'datetime' },
                    { name: 'actions', type: 'string' },
                   // { name: 'delete', type: 'string' }
                ],
                //id: 'customer_id',
                url: url,
                pager: function (pagenum, pagesize, oldpagenum) {
                    // callback called when a page or page size is changed.
                }
            };
            var dataAdapter = new $.jqx.dataAdapter(source);
            $("#paymentsgrid").jqxGrid(
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
                  { text: 'Payment Id', filtercondition: 'starts_with', datafield: 'sno', width: "10%" },
                  { text: 'Payment Type', datafield: 'payment_type', width: "15%"},
                  { text: 'Reference Number', datafield: 'reference_no', width: "20%"},
                  { text: 'Ifsc Code', datafield: 'ifsc_code', width: "15%"},
                  { text: 'Amount', datafield: 'amount', width: "10%"},
                  { text: 'Payment Date', datafield: 'payment_date', width: "20%"},
                  
                  //{ text: 'Edit', datafield: 'edit' },
                  { text: 'Actions', datafield: 'actions',width:"10%" }
                ]               
            });            
            // makePopupAjax($('#basicvalCodeModal'));
            makePopupEditAjax($('#basicvalCodeModal1'));
            $(document).ajaxSuccess(function(e, xhr, settings){
              if(settings.url.indexOf('/orders/paymentEdit/') == 0){
                var payment_type = $('#payment_type').val(),
                    cur = $('[data-payment-type=' + payment_type + ']');
                $('#parentVerticalTab1 li').removeClass('resp-tab-active hide');
                $('#parentVerticalTab1 .resp-tabs-container div').removeClass('resp-tab-content-active');
                $('#parentVerticalTab1 li').not(cur).addClass('hide');
                cur.addClass('resp-tab-active resp-tab-content-active');
              }
            });
            
    } 
    $('#parentVerticalTab').easyResponsiveTabs({
            type: 'vertical', //Types: default, vertical, accordion
            width: 'auto', //auto or any width like 600px
            fit: true, // 100% fit in a container
            //closed: 'accordion', // Start closed if in accordion view
            tabidentify: 'hor_1', // The tab groups identifier
            activate: function(event) { // Callback function if tab is switched
                var $tab = $(this);
                var $info = $('#nested-tabInfo2');
                var $name = $('span', $info);
                $name.text($tab.text());
                $info.show();
            }
        }); 
    $('#parentVerticalTab1').easyResponsiveTabs({
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
$('#basicvalCodeModal').on('hide.bs.modal', function () {
    console.log('resetForm');
    $('#ref_number').val('');
    $('#form').data('bootstrapValidator').resetField($('#ref_number'));
    $('#payee_bank_name').val('');
    $('#form').data('bootstrapValidator').resetField($('#payee_bank_name'));
    $('#ifsc_code').val('');
    $('#form').data('bootstrapValidator').resetField($('#ifsc_code'));
    $('#amount').val('');
    $('#form').data('bootstrapValidator').resetField($('#amount'));
    //$('#form')[0].reset();
}); 
    function postData()
    {
        console.log('we are in view');
        return;
    }
 $(function(){
      $('#form').bootstrapValidator({
        
        messages:'This value is not valid',
            feedbackIcons: {
                valid: 'glyphicon glyphicon-ok',
                invalid: 'glyphicon glyphicon-remove',
                validating: 'glyphicon glyphicon-refresh'
            },

        fields: {
         trans_reference_no:{
                  validators: {
                      regexp: {
                          regexp: '^[a-zA-Z0-9]+$',
                          message: 'Please enter only alpha-numeric'
                      },                     
                      notEmpty: { 
                          message:'Referrence Number is required'
                        }
                  }
              },
         payee_bank: {
                    validators: {
                      regexp: {
                          regexp: '^[a-zA-Z0-9 .]+$',
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
/*                payment_date: {
                    validators: {
                        notEmpty: {
                            message: 'The date is required and can\'t be empty'
                        },
                        date: {
                            format: 'YYYY/MM/DD'
                        }
                    }
                },*/
                amount: {
                    validators: {
                        notEmpty: {
                            message: 'The amount is required and can\'t be empty'
                        }
                    }
                }
        }/*,submitHandler:function(form){
          // form.submit();
          return false;
        },errorPlacement: function(error, element) {
          element.closest('.form-group').append(error);
          //alert('Check Errors');
        },unhighlight: function (element, errorClass, validClass) {
          if ($(element).hasClass('optional') && $(element).val() == '') {
            $(element).removeClass('error valid');
          }else{
            $(element).removeClass('error').addClass('valid');
          }
        }*/
      }).on('success.form.bv', function(event) {
        ajaxCallPopup($('#form'));
        //viewPayments();
        setTimeout('viewPayments()', 2000);
        return false;
      }).validate({
          submitHandler: function (form) {
              return false;
          }
      });
    });
//
//edit Payment
      $('#editForm').bootstrapValidator({
        
        messages:'This value is not valid',
            feedbackIcons: {
                valid: 'glyphicon glyphicon-ok',
                invalid: 'glyphicon glyphicon-remove',
                validating: 'glyphicon glyphicon-refresh'
            },

        fields: {
         trans_reference_no:{
                  validators: {
                      regexp: {
                          regexp: '^[a-zA-Z0-9]+$',
                          message: 'Please enter only alpha-numeric'
                      },                     
                      notEmpty: { 
                          message:'Referrence Number is required'
                        }
                  }
              },
         payee_bank: {
                    validators: {
                      regexp: {
                          regexp: '^[a-zA-Z0-9 .]+$',
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
                amount: {
                    validators: {                     
                        notEmpty: {
                            message: 'The amount is required and can\'t be empty'
                        },
                         between: {
                          min: 0.01,
                          max:10000000,
                          message: 'Enter valid Amount'
                      }                        
                    }
                }
        }
      }).on('success.form.bv', function(event) {
        ajaxCallPopup($('#editForm'));
        //viewPayments();
        setTimeout('viewPayments()', 2000);
        return false;
      }).validate({
          submitHandler: function (form) {
              return false;
          }
      });
//edit Payment
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
//

function deleteEntityType(id)
{
    var dec = confirm("Are you sure you want to Delete ?");
    if (dec == true)
        //window.location.href = '/orders/paymentDelete/'+id;
        $.ajax({
        url: '/orders/paymentDelete/'+id,
        type:'POST',
        success: function(result)
        {
            if(result == 1){
                alert('Succesfully Deleted !!');
                //location.reload();
                viewPayments();
            }else{
                alert(result);
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

jQuery(document).ready(function($) {

  if (window.history && window.history.pushState) {
    
    window.history.pushState('', null,'');

    $(window).on('popstate', function() {
    window.location='/orders/customerIma';
    });

  }
});

    </script>    
@stop
