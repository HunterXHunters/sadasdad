@extends('layouts.default')
@extends('layouts.sideview')
@section('content')

<style type="text/css">
.box-title {
    display: inline-block;
    font-size: 12px !important;
    font-weight: bold;
    margin: 0;
    line-height: 1;
}
.orderbilling{background:#fcfac9; border:1px solid #d7c699;}
.billrates{color:#eb5e12 !important;}
.topbar{margin-top: -14px;
    padding: 10px 0px;}
.padbutt{padding: 13px !important;}

pre#t1 {
    tab-size:16;
}

</style>
@section('style')
{{HTML::style('css/bootstrap-select.css')}}
{{HTML::style('css/jquery.fileupload.css')}}
{{HTML::style('css/dragdrop/jquery-ui.css')}}
{{HTML::style('css/dragdrop/style.css')}}
{{HTML::style('css/jquery.filer.css')}}

@stop
{{ Form::open(array('url' => 'gdsOrders', 'method' => 'GET', 'files'=>true, 'id' => 'editgdsOrders')) }}

	<div class="row">
		<div class="box topbar">

				<div class="col-md-4">
				<div class="box-header">
		
				<h3 class="box-title"> Order ID: @if(isset($data[0]->gds_order_id)) {{$data[0]->gds_order_id}} @endif | @if(isset($data[0]->order_date)) {{$data[0]->order_date}} @endif</h3>

				</div>
				</div>

				<div class="col-md-2 col-md-offset-6 pull-right">


					<div class="btn-group">
	

						<button type="button" class="btn btn-success dropdown-toggle padbutt" data-toggle="dropdown" aria-expanded="false">
						<span class="caret"></span><span class="sr-only">Toggle Dropdown</span>
						</button>
									<ul class="dropdown-menu" role="menu">
									<li><a href="#"><i class="fa fa-arrow-left"></i> Back</a></li>
									<li><a href="#"><i class="fa fa-edit"></i> Edit</a></li>
									<li><a href="#"><i class="fa fa-remove"></i> Cancel</a></li>
									<li><a href="#"><i class="fa fa-envelope-o"></i> Send Mail</a></li>
									<li><a href="#"><i class="fa fa-compress"></i> Hold</a></li>
									<li><a href="#"><i class="fa fa-file-text-o"></i> Invoice</a></li>
									<li><a href="#"><i class="fa fa-ship"></i> Ship</a></li>
									<li><a href="#"><i class="fa fa-reorder"></i> Reorder</a></li>
									</ul>
					</div>
				</div>

		</div>
	</div>

	<div class="row">
		<div class="col-md-3">
			<div class="box box-solid">
				<div class="box-header with-border">
					<h3 class="box-title">Order View</h3>
				</div>
				<div class="box-body no-padding" style="display: block;">
					<ul class="nav nav-pills nav-stacked">
					<li class="active"><a href="/gdsOrders/edit/{{$data[0]->gds_order_id}}">Information</a></li>
					<li><a href="/gdsOrders/editInvoice/{{$data[0]->gds_order_id}}">Invoices</a></li>
					<li><a href="#">Credit Memos</a></li>
					<li><a href="/gdsOrders/shipmentsIndex/{{$data[0]->gds_order_id}}">Shipments</a></li>
					<li><a href="#">RMA</a></li>
					<li><a href="#">Comments History</a></li>
					<li><a href="#">Transactions</a></li>
					</ul>
				</div>
			</div>
		</div>

		<div class="col-md-9">
			<div class="row">
				<div class="col-md-6">
					<div class="box box-primary">
						<div class="box-header">
							<h3 class="box-title">Order ID:<a href="#">@if(isset($data[0]->gds_order_id)) {{$data[0]->gds_order_id}} @endif</a></h3>
							<input type="hidden" name="gds_order_id" id="gds_order_id" value="{{$data[0]->gds_order_id}}">
						</div>

						<div class="box-body">
							<div class="row invoice-info">
									<div class="col-sm-6 invoice-col">
										<address>
										Order Date<br>
										Order Status<br>
										Purchased From<br>
										Placed from<br>
										</address>
									</div>
								<div class="col-sm-6 invoice-col">
										<address>

										<strong>@if(isset($data[0]->order_date)){{$data[0]->order_date}} @endif</strong><br>
										<strong>{{$order_stat}}</strong><br>
										<strong>@if(isset($data[0]->channnel_name)) {{$data[0]->channnel_name}} @endif</strong><br>
										<strong>@if(isset($billing_address[0]->city)) {{$billing_address[0]->city}} @endif</strong><br>
										</address>
								</div>
							</div>
						</div>
					</div>
				</div>
			<div class="col-md-6">
				<div class="box box-primary">
					<div class="box-header">
						<h3 class="box-title">Account Information</h3>
					</div>
						<div class="box-body">
							<div class="row invoice-info">
								<div class="col-sm-6 invoice-col">
									<address>
									Customer Name<br>
									Email<br>
									
									<!-- Gender<br> -->
									</address>
								</div>
								<div class="col-sm-6 invoice-col">
									<address>
									<strong><!-- <a href="#"> -->@if(isset($data[0]->firstname)) {{$data[0]->firstname}} @endif @if(isset($data[0]->lastname)) {{$data[0]->lastname}} @endif</a></strong><br>
									<strong><!-- <a href="#"> -->@if(isset($data[0]->email)) {{$data[0]->email}} @endif</a></strong><br>
									<!--  -->
									<!-- <strong>{{$data[0]->gender}}</strong><br> -->
									</address>
								</div>
							</div>
						</div>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-6">
				<div class="box box-primary" style="min-height:150px;">
					<div class="box-header">
						<h3 class="box-title">Channel</h3>
					</div>
					<div class="box-body">
						<div class="row invoice-info">
							<div class="col-sm-6 invoice-col">
								<address>
								Channel Name<br>
								Channel Order ID<br>
								<strong>Channel Fee</strong><br>
								@if(isset($charges[0]->service_name)) {{$charges[0]->service_name}} @endif<br>
								eSeal Fee<br>
								Sub Total<br>
								</address>
							</div>
							<div class="col-sm-6 invoice-col">
								<p>
								<strong>{{$data[0]->channnel_name}}</strong><br>
								<strong>{{$data[0]->channel_order_id}}</strong><br>
								<br>
								<strong>@if(isset($payment_method[0]->symbol_left)) {{$payment_method[0]->symbol_left}} @endif @if(isset($charges[0]->charges)) {{$charges[0]->charges}} @endif</strong><br>
								<strong>@if(isset($payment_method[0]->symbol_left)) {{$payment_method[0]->symbol_left}} @endif @if(isset($charges[0]->eseal_fee)) {{$charges[0]->eseal_fee}} @endif</strong><br>
								<strong>@if(isset($payment_method[0]->symbol_left)) {{$payment_method[0]->symbol_left}} @endif @if(isset($charges[0]->eseal_fee)) @if(isset($charges[0]->charges)) {{$charges[0]->eseal_fee + $charges[0]->charges}} @endif @endif</strong><br><br>
								</p>
							</div>
						</div>
					<div class="row">
						<div class="col-sm-6 col-xs-offset-3 text-center"><p>
						<button type="button" class="btn btn-primary" id="confirmOrder">Confirm Order</button>
						</p></div>
					</div>
				</div>
			</div>
		</div>
		<div class="col-md-6">
			<div class="box box-primary">
				<div class="box-header">
					<h3 class="box-title">Payment Information</h3>
				</div>
				<div class="box-body">
					<div class="row invoice-info">
						<div class="col-sm-12 invoice-col">
							<address>
							@if(isset($payment_method[0]->name)) {{$payment_method[0]->name}} @endif<br>
							Order was placed using @if(isset($payment_method[0]->code)) {{$payment_method[0]->code}} @endif
							</address>
						</div>
					</div>
				</div>
				<div class="box-header">
					<h3 class="box-title">Shipping &amp; Handling Information</h3>
				</div>
				<div class="box-body">
					<div class="row invoice-info">
						<div class="col-sm-12 invoice-col">
							<address>
							@if(isset($shipper[0]->service_name)) {{$shipper[0]->service_name}} @endif - @if(isset($payment_method[0]->symbol_left)) {{$payment_method[0]->symbol_left}} @endif @if(isset($shipper[0]->service_cost)) {{$shipper[0]->service_cost}} @endif</address>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-md-6">
			<div class="box box-primary">
				<div class="box-header">
					<h3 class="box-title">Billing Address</h3>
					<!-- <h3 class="box-title pull-right"><a href="#"><i class="fa fa-edit"></i> Edit</a></h3> -->
				</div>
				<div class="box-body">
					<div class="row invoice-info">
						<div class="col-sm-12 invoice-col">
							<address>
							@if(isset($billing_address[0]->fname)) {{$billing_address[0]->fname}} @endif @if(isset($billing_address[0]->mname)) {{$billing_address[0]->mname}} @endif @if(isset($billing_address[0]->lname)) {{$billing_address[0]->lname}} @endif<br>
							@if(isset($billing_address[0]->company)) {{$billing_address[0]->company}} @endif<br>
							@if(isset($billing_address[0]->addr1)) {{$billing_address[0]->addr1}} @endif, @if(isset($billing_address[0]->addr2)) {{$billing_address[0]->addr2}} @endif, @if(isset($billing_address[0]->city)) {{$billing_address[0]->city}} @endif, @if(isset($billing_address[0]->state)) {{$billing_address[0]->state}} @endif<br>
							@if(isset($billing_address[0]->country)) {{$billing_address[0]->country}} @endif<br>
							T: @if(isset($billing_address[0]->mobile)) {{$billing_address[0]->mobile}} @endif
							</address>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="col-md-6">
			<div class="box box-primary">
				<div class="box-header">
					<h3 class="box-title">Shipping Address</h3>
					<!-- <h3 class="box-title pull-right"><a href="#"><i class="fa fa-edit"></i> Edit</a></h3> -->
				</div>
				<div class="box-body">
					<div class="row invoice-info">
						<div class="col-sm-12 invoice-col">
							<address>
							@if(isset($shipping_address[0]->fname)) {{$shipping_address[0]->fname}} @endif @if(isset($shipping_address[0]->mname)) {{$shipping_address[0]->mname}} @endif @if(isset($shipping_address[0]->lname)) {{$shipping_address[0]->lname}} @endif<br>
							@if(isset($shipping_address[0]->company)) {{$shipping_address[0]->company}} @endif<br>
							@if(isset($shipping_address[0]->addr1)) {{$shipping_address[0]->addr1}} @endif, @if(isset($shipping_address[0]->addr2)) {{$shipping_address[0]->addr2}} @endif, @if(isset($shipping_address[0]->city)) {{$shipping_address[0]->city}} @endif, @if(isset($shipping_address[0]->state)) {{$shipping_address[0]->state}} @endif<br>
							@if(isset($shipping_address[0]->country)) {{$shipping_address[0]->country}} @endif<br>
							T: @if(isset($shipping_address[0]->mobile)) {{$shipping_address[0]->mobile}} @endif
							</address>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

		<div class="row">
		<div class="col-md-12">
			<div class="box box-primary">
				<div class="box-header">
					<h3 class="box-title">Items Ordered</h3>
				</div>
				<div class="box-body">
					<table class="table table-bordered">
						<tbody>
							<tr style="font-size:12px">
								<th>Product ID</th>
								<th>Product</th>
								<th>Price</th>
								<th>Quantity</th>
								<th>Discount</th>
								<th>Tax</th>
								<th>Sub Total</th>
							</tr>
							@foreach ($data as $key => $value)
							<tr style="font-size:12px">
								<td>@if(isset($value->pid)) {{$value->pid}} @endif</td>
								<td>@if(isset($value->pname)) {{$value->pname}} @endif</td>
								<td>@if(isset($payment_method[0]->symbol_left)) {{$payment_method[0]->symbol_left}} isset($value->price) {{$value->price}} @endif</td>
								<td>@if(isset($value->qty))<?php echo round($value->qty); ?>@endif</td>
								<td>@if(isset($value->discount)) {{$value->discount}} @endif</td>
								<td>@if(isset($payment_method[0]->symbol_left)) {{$payment_method[0]->symbol_left}} isset($value->tax) {{$value->tax}} @endif</td>
								<td>@if(isset($payment_method[0]->symbol_left)) {{$payment_method[0]->symbol_left}} isset($value->subtotal) {{$value->subtotal}} @endif</td>
							</tr>
							@endforeach
						</tbody>
					</table>
				</div>
			</div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-6">
				<div class="box box-primary">
					<div class="box-header">
						<h3 class="box-title">Comments History</h3>
					</div>
					<div class="box-body">
						<div class="row invoice-info">
							<div class="col-sm-12 invoice-col">
								<div id="testForm">
								<div class="form-group">
									<label>Add Order Comment Status</label>
									<select name="orderStatus" id="orderStatus" class="form-control">
									<option value="">Please Select</option>
									@if(isset($orderStatus) && !empty($orderStatus))
										@foreach($orderStatus as $value)
										<option value="{{$value->value}}">{{$value->name}}</option>
										@endforeach
									@endif
									</select>
								</div>
									<div class="form-group">
										<label>Comment</label>
											<textarea class="form-control" rows="3" id="order_comment" name="order_comment" placeholder=""></textarea>
									</div>
									<div class="row">
										<div>
										<div class="col-md-4 pull-left padbuttom">
											<button type="button" class="btn btn-primary" id="testId">Submit Comment</button>
										</div>
										</div>
									</div>
								</div>
								<div>
								@if(isset($comment) && !empty($comment))
									@foreach($comment as $key => $value)
									<p><i class="fa fa-file-o"></i> <strong>{{$value->comment_date}}</strong></p>
									<!-- <p>Customer <a href="#">Notified <i class="fa fa-check"></i></a></p> -->
									<p>{{$value->comment}}</p>
									<hr>
									@endforeach
									@endif
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>

			<div class="col-md-6">
				<div class="box box-primary">
					<div class="box-header">
						<h3 class="box-title">Order Totals</h3>
					</div>
				<div class="box-body  orderbilling">
					<div class="row invoice-info">
						<div class="col-sm-6 invoice-col" style="text-align:right;">
							<p>
								Subtotal<br>
								Shipping & Handling<br>
								Discount<br>
								Tax<br>
								<strong>Grand Total</strong><br>
								<strong>Total paid</strong><br>
								<strong>Total Refunded</strong><br>
								<strong>Total Due</strong><br>
							</p>
						</div>
						<div class="col-sm-6 invoice-col">
							<p>
								@if(isset($data[0]->sub_total)) {{$data[0]->sub_total}} @endif<br>
								@if(isset($data[0]->ship_total)) {{$data[0]->ship_total}} @endif<br>
								@if(isset($data[0]->discount)) {{$data[0]->discount}} @endif<br>
								@if(isset($data[0]->tax_total)) {{$data[0]->tax_total}} @endif<br>
								<strong class="billrates">@if(isset($data[0]->total)) {{$data[0]->total}} @endif</strong><br>
								<strong class="billrates">---</strong><br>
								<strong class="billrates">---</strong><br>
								<strong class="billrates">---</strong><br>
							</p>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
</div>

{{Form::submit('back', array('class' => 'btn btn-primary')) }}
{{Form::close()}}
@stop

@section('script')
<script type="text/javascript">
	$(document).ready(function() {

		$('#testId').click(function () {
			var gds_order_id = $('#gds_order_id').val();
			var order_comment = $('#order_comment').val();
			var orderStatus = $('#orderStatus').val();
			/*alert(gds_order_id);*/
			/*alert('here in js');*/
			$.ajax({
      url: "/gdsOrders/saveComment/"+gds_order_id , 
      type: "POST",
      data: {'order_comment': order_comment,'orderStatus': orderStatus},
      /*data: "gds_order_id=" , //The data your sending to some-page.php*/
      success: function(response){
        alert('Sucessfully saved comment');
        console.log('called');
        location.reload();
                     
      },
      error:function(response){
      	console.log('errored');
      	alert('Unable to saved comment');
         // console.log("AJAX request was a failure");
      }
		 });
		
	});

	$('#confirmOrder').click(function () {
			var gds_order_id = $('#gds_order_id').val();
			
			console.log(gds_order_id);
			/*alert('here in js');*/
	$.ajax({
      url: "/gdsOrders/confirmOrder/"+gds_order_id , 
      
       /*data: "gds_order_id=" , //The data your sending to some-page.php*/
      success: function(response){
        alert('Order Confirmed');
        console.log('called');
        location.reload();
                     
      },
      error:function(response){
      	console.log('errored');
      	alert('Unable to Confirm Order');
      	/*location.reload();*/
         // console.log("AJAX request was a failure");
      }
		 });
		
	});
});
</script>
@stop