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

</style>
@section('style')
{{HTML::style('css/bootstrap-select.css')}}
{{HTML::style('css/jquery.fileupload.css')}}
{{HTML::style('css/dragdrop/jquery-ui.css')}}
{{HTML::style('css/dragdrop/style.css')}}
{{HTML::style('css/jquery.filer.css')}}

@stop



	<div class="row">
		<div class="box topbar">

				<div class="col-md-4">
				<div class="box-header">
				<h3 class="box-title">Order ID: @if(isset($data[0]->gds_order_id)) {{$data[0]->gds_order_id}} @endif| @if(isset($data[0]->order_date)) {{$data[0]->order_date}} @endif</h3>

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
					<li><a href="/gdsOrders/edit/{{$data[0]->gds_order_id}}">Information</a></li>
					<li><a href="/gdsOrders/editInvoice/{{$data[0]->gds_order_id}}">Invoices</a></li>
					<li><a href="#">Credit Memos</a></li>
					<li class="active"><a href="/gdsOrders/shipmentsIndex/{{$data[0]->gds_order_id}}">Shipments</a></li>
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
										<strong>@if(isset($order_stat)) {{$order_stat}} @endif</strong><br>
										<strong>@if(isset($data[0]->channnel_name)) {{$data[0]->channnel_name}} @endif</strong><br>
										<strong>@if(isset($shipping_address->city)) {{$shipping_address->city}} @endif</strong><br>
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
			<div class="box box-primary">
				<div class="box-header">
					<h3 class="box-title">Shipping Address</h3>
					<!-- <h3 class="box-title pull-right"><a href="#"><i class="fa fa-edit"></i> Edit</a></h3> -->
				</div>
				<div class="box-body">
					<div class="row invoice-info">
						<div class="col-sm-12 invoice-col">
							<address>
							@if(isset($shipping_address->fname)) {{$shipping_address->fname}} @endif @if(isset($shipping_address->mname)) {{$shipping_address->mname}} @endif @if(isset($shipping_address->lname)) {{$shipping_address->lname}} @endif<br>
							@if(isset($shipping_address->company)) {{$shipping_address->company}} @endif<br>
							@if(isset($shipping_address->addr1)) {{$shipping_address->addr1}} @endif, @if(isset($shipping_address->addr2)) {{$shipping_address->addr2}} @endif, @if(isset($shipping_address->city)) {{$shipping_address->city}} @endif, @if(isset($shipping_address->state)) {{$shipping_address->state}} @endif<br>
							@if(isset($shipping_address->country)) {{$shipping_address->country}} @endif<br>
							T: @if(isset($shipping_address->mobile)) {{$shipping_address->mobile}} @endif
							</address>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="col-md-6">
			<div class="box box-primary">
				<div class="box-header">
					<h3 class="box-title">Shipping &amp; Handling Information</h3>
				</div>
				<div class="box-body">
					<div class="row invoice-info">
						<div class="col-sm-12 invoice-col">
							<p>@if(isset($shipper->service_name)) {{$shipper->service_name}} @endif- @if(isset($shipper->service_cost)) {{$shipper->service_cost}} @endif</p>
							<table class="table table-bordered">
								<tbody>
									<tr>
									<th>SKU</th>
									<th>Carrier</th>
									<th>Title</th>
									<th>Number *</th>
									
									</tr>
									<tr>
									<td colspan="4" align="center" valign="middle">
									<input type="button" value="Add Tracking Number" class="btn btn-warning" data-href="/gdsOrders/addTrack/1" data-toggle="modal" data-target="#basicvalCodeModal1">

									<!-- <button class="btn btn-warning" data-href="/gdsOrders/addTrack/{{$data[0]->gds_order_id}}" data-toggle="modal" data-target="#basicvalCodeModal1">Add Tracking Number</button></td> -->
									
									</tr>
									@foreach($tracking_data as $value)
									<tr>
									<td>@if(isset($value->sku)) {{$value->sku}} @endif</td>
									<td>@if(isset($value->name)) {{$value->name}} @endif</td>
									<td>@if(isset($value->ship_method)) {{$value->ship_method}} @endif</td>
									<td>@if(isset($value->track_number)) {{$value->track_number}} @endif</td>
									</tr>
									@endforeach
								</tbody>
							</table>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="modal fade" id="basicvalCodeModal1" tabindex="-1" role="dialog" aria-labelledby="basicvalCode" aria-hidden="true">
                    <div class="modal-dialog wide">
                      <div class="modal-content">
                        <div class="modal-header">
                          <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                          <h4 class="modal-title" id="basicvalCode">Edit</h4>
                        </div>
                        <div class="modal-body">

               {{ Form::open( ['url' => 'gdsOrders/saveTrack/'.$data[0]->gds_order_id, 'method'=>'POST', 'id'=>'form-AddTrack'] ) }}
               {{ Form::hidden('_method', 'PUT') }} 
               <div class="row">
                           
                <div class="form-group col-sm-6">
                    <label class="col-sm-2 control-label" for="Designation">Carrier</label>
                    <div class="col-sm-10">
                        <input type="hidden" name="gds_order_id" id="gds_order_id" value="@if(isset($data[0]->gds_order_id)) {{$data[0]->gds_order_id}} @endif">
                        <div class="input-group input-group-sm">
                            <span class="input-group-addon addon-red"><i class="fa fa-list-alt"></i></span>
                            <select name="carrier" required onChange="getTitle(this.value)" id="carrier" class="form-control">
	                          <option value="">Please Select</option>
	                        @foreach ($addTrack as $key => $value)
	                          <option value="@if(isset($value->carrier_id)) {{$value->carrier_id}} @endif">@if(isset($value->carrier)) {{ $value->carrier}} @endif</option> 
	                        @endforeach
                       		</select>
                        </div>
                    </div>
                </div>
              

                                    
                <div class="form-group col-sm-6">
                    <label class="col-sm-2 control-label" for="Title">Title</label>
                    <div class="col-sm-10">
                        <div class="input-group input-group-sm">
                            <span class="input-group-addon addon-red"><i class="fa fa-list-alt"></i></span>
                            <input type="text" class="form-control" name="title" id="title">
                        </div>
                    </div>
                </div>
                </div> 

                <div class="box-body">
					<table class="table table-bordered">
						<tbody>
							<tr style="font-size:12px">
								
								<th>Product ID</th>
								<th>Product Name</th>
								<th>Ordered Quantity</th>
								<th>Available Quantity</th>	
								<th>Quantity To Ship</th>
								<th>Select</th>
							</tr>
							@foreach ($trackData as $key => $value)
							<tr style="font-size:12px">
								
								<td>@if(isset($value->pid)) {{$value->pid}} @endif<input type="hidden" name="myTextEditBox[product_id][]" value="{{$value->pid}}"/></td>
								<td>@if(isset($value->prod_name)) {{$value->prod_name}} @endif</td><input type="hidden" name="product_name[]" value="{{$value->prod_name}}"/>
								<td>@if(isset($value->quantity)) <?php echo round($value->quantity); ?>@endif</td><input type="hidden" name="myTextEditBox[product_quantity][]" value="{{$value->quantity}}"/>
								<td>@if(isset($value->avail_qty)) <?php echo round($value->avail_qty); ?>@endif</td></td><input name="avail_qty[]" type="hidden" value="{{$value->avail_qty}}"/>
								<td>
									<div class="row">
	                    				<div class="col-sm-10">
											<div class="form-group">
												<div class="input-group input-group-sm">
													<input type="text" id="ship_quantity" name="myTextEditBox[ship_quantity][]" class="form-control">
												</div>
											</div>
										</div>
									</div>
								</td>
								<td><input type="checkbox" name="myTextEditBox[check][{{$key}}]"/></td>
							</tr>
							@endforeach
							
						</tbody>
					</table>
				</div>



                <div class="form-group col-sm-6">
                    <label class="col-sm-2 control-label" for="TrackId">Track ID</label>
                    <div class="col-sm-10">
                        <div class="input-group input-group-sm">
                            <span class="input-group-addon addon-red"><i class="fa fa-fax"></i></span>
                            <input type="text" class="form-control" placeholder="Track ID" name="track_id" id="track_id">
                        </div>
                    </div>
                </div>
            

                        {{ Form::submit('Add', array('class' => 'btn btn-warning'))}}
                        
                        </div>
                      </div><!-- /.modal-content -->
                    </div><!-- /.modal-dialog -->
                  </div><!-- /.modal -->
				</div>
		
{{ Form::close() }}
		<div class="row">
		<div class="col-md-12">
			<div class="box box-primary">
				<div class="box-header">
					<h3 class="box-title">Shipment Items</h3>
				</div>
				<div class="box-body">
					<table class="table table-bordered">
						<tbody>
							<tr style="font-size:12px">
								<th>SKU</th>
								<th>Product Name</th>
								<th>Ordered sQuantity</th>	
							</tr>
							@foreach ($prodInfo as $key => $value)
							<tr style="font-size:12px">
								<td name="pid">@if(isset($value->sku)) {{$value->sku}} @endif</td>
								<td>@if(isset($value->prod)) {{$value->prod}} @endif</td>
								<td>@if(isset($$value->qty)) <?php echo round($value->qty); ?>@endif</td>
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
										<input type="hidden" name="order_ship_id" id="order_ship_id" value="@if(isset($tracking_data[0]->order_ship_id)) {{$tracking_data[0]->order_ship_id}} @endif">
										<label>Comment</label>
											<textarea class="form-control" rows="3" id="order_comment" name="order_comment" placeholder=""></textarea>
									</div>
									<div class="col-sm-12 invoice-col" style="text-align:left; float:right;">
										<button type="button" class="btn btn-primary" id="testId">Submit</button>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="col-md-6">
				<div class="box box-primary" style="min-height:196px;">
					<div class="box-body" style="padding-bottom:60px;">
						<div class="row invoice-info">
						<label>Shipment Comments</label>
							<div>
								@foreach($comment as $key => $value)
								<p><i class="fa fa-file-o"></i> <strong>@if(isset($value->comment_date)) {{$value->comment_date}} @endif</strong></p>
								<!-- <p>Customer <a href="#">Notified <i class="fa fa-check"></i></a></p> -->
								<p>@if(isset($value->comment)) {{$value->comment}} @endif</p>
								<hr>
								@endforeach
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

@stop

@section('script')

<script type="text/javascript">
	$(document).ready(function() {

		$('#testId').click(function () {
			var order_ship_id = $('#order_ship_id').val();
			var order_comment = $('#order_comment').val();
			/*alert(gds_order_id);*/
			/*alert('here in js');*/
			$.ajax({
      url: "/gdsOrders/saveShipComment/"+order_ship_id , 
      type: "POST",
      data: {'order_comment': order_comment},
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
	makePopupEditAjax($('#basicvalCodeModal1'), 'id');
});

function getTitle(carrier_id){
	console.log(carrier_id);
    if(carrier_id=='')
      carrier_id=0;
      $.ajax({
        url: "/gdsOrders/getShipTitle/"+carrier_id,
        success: function(result){
          var sel = '';
          $("#title").val(result);
          console.log(result);
         // $('#title').val(result);
        }
      });
  
}


$(document).ready(function() {
    $('#form-AddTrack').bootstrapValidator({
    	
//        live: 'disabled',
        message: 'This value is not valid',
        feedbackIcons: {
            valid: 'glyphicon glyphicon-ok',
            invalid: 'glyphicon glyphicon-remove',
            validating: 'glyphicon glyphicon-refresh'
        },
        fields: {

        'myTextEditBox[ship_quantity][]':
	        {
	        	validators:
	        	{     	
				   between: {
		                      min: 0,
		                      max:function(value, validator, el){
		                       var $el = $( el[0] );
		                       return $el.closest('tr').find('[name="avail_qty[]"]').val();
		                   	  },
		                      message: 'Incorrect Value for ship quantity'
		                  },
		          notEmpty: {
                            message: 'The field is required and can\'t be empty'
                        }  
	        	}
	        },
           
        carrier: {
                validators: {
                  notEmpty: {
                        message: 'Carrier is required'
                    }
                }
            },
        title: {
                validators: {
	                  notEmpty: {
	                        message: 'Title is required'
	                    }
                }
            }

         }

    }).on('success.form.bv', function(event) {
            event.preventDefault();
       $form.bootstrapValidator('resetForm',true);
       return false; 
	})
});


</script>

@stop