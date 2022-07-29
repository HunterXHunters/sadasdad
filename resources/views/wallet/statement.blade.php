@extends('layouts.default')

@extends('layouts.header')

@extends('layouts.sideview')

@section('content')
<style type="text/css">
.box {border-top :1px solid #d2d6de;}
.info-tile {
    margin: 0px 0px 15px;
    box-shadow: 0 1px 3px 0px rgba(0, 0, 0, 0.2);
    background: #fff;
    padding: 16px;
    position: relative;
    overflow: hidden;
    border-radius: 2px;
    display: block;
}
.info-tile .tile-icon {
    position: absolute;
    height: 160px;
    width: 160px;
    border-radius: 50%;
    left: -80px;
    bottom: -80px;
    color: #9f9f9f;
}
.info-tile .tile-body {
    text-align: right;
    color: #616161;
    font-size: 36px;
    font-weight: 400;
    line-height: 72px;
    position: relative;
    z-index: 1;
 }
 .info-tile .tile-footer {
    text-align: right;
    font-size: 12px;
    position: absolute;
    right: 12px;
    bottom: 8px;
}   
.info-tile .tile-icon i {
    font-size: 52px;
    position: absolute;
    left: 96px;
    top: 0;
}
</style>
	<div class="box">
		<div class="box-header with-border">
          <h3 class="box-title">eSeal Wallet</h3>
        </div><!-- /.box-header -->
        <div class="box-body">
        		
    		<div class="col-sm-4 col-xs-12">
    			<div class="box">
    				<div class="box-header">
			          <h3 class="box-title">Current Balance</h3>
			        </div><!-- /.box-header --> 
			        <div class="box-body" >
			        	<div class="row">
							<div class="col-sm-12 col-xs-12">
								<div class="info-tile" style="visibility: visible; opacity: 1; display: block; transform: translateY(0px);">
									<div class="tile-icon"><i class="fa fa-barcode"></i></div>
									<div class="tile-body"><span id="availableIds">{{$availableIds}}</span></div>
						            <div class="tile-footer" align="right">Available ID's</div>
								</div>
							</div>
						</div>                    
						<div class="row">
							<div class="col-md-12">
								<div class="info-tile" style="visibility: visible; opacity: 1; display: block; transform: translateY(0px);">
									<div class="tile-icon"><i class="fa fa-download"></i></div>
									<div class="tile-body"><span id="issuedIds">{{$issued_count}}</span></div>
						            <div class="tile-footer" align="right">Total Issued ID's</div>
								</div>
							</div>
						</div>                    
						<div class="row">
							<div class="col-md-12">
								<div class="info-tile" style="visibility: visible; opacity: 1; display: block; transform: translateY(0px);">
									<div class="tile-icon"><i class="fa fa-bar-chart-o"></i></div>
									<div class="tile-body"><span id="totalIds">{{$totalUsedCount}}</span></div>
						            <div class="tile-footer" align="right">Total Used ID's</div>
								</div>
							</div>
						</div>
			        </div>
    			</div>
    		</div>
    		<div class="col-sm-8 col-xs-12">
    			<div class="box">
    				<div class="box-header">
			          <h3 class="box-title">Usage Analysis</h3>
			        </div><!-- /.box-header --> 
			        <div class="box-body">
			        	<div class="row">
							<div class="col-md-5">
								<div class="input-group date">
									<input type="text" name="from_date" id="from_date"class="form-control" placeholder="From Date">
									<span class="input-group-addon"><i class="fa fa-calendar"></i></span>
								</div>
							</div>
							<div class="col-md-5">
								<div class="input-group date">
									<input type="text" class="form-control" placeholder="To Date" name="to_date" id="to_date">
									<span class="input-group-addon"><i class="fa fa-calendar"></i></span>
								</div>
							</div>
							<div class="col-md-2">
								<button type="button" class="btn btn-default" onclick="getIds();">Search</button>                    
							</div>
						</div>
						<div class="row">
							<div class="col-md-12">
								<h4>Used ID's : <span id="IdsResult">{{$currentMonthUsedId}}</span></h4>
							</div>
						</div> 

						<div class="row">
							<div class="col-md-12" id="stmntcanvasDiv">
								<canvas id="stmntcanvas" style="width: 681px; height: 285px;"></canvas>
							</div>
						</div> 
			        </div>
    			</div>
    		</div>
	        
    		<?PHP /*<div class="box ">
        		<div class="box-header with-border">
		          <h3 class="box-title">Usage Analysis</h3>
		        </div><!-- /.box-header -->
		        <div class="box-body">
	        		<div class="col-sm-12 col-xs-12">
	        			<div class="box">
			                <div class="box-body">
			                	<h4 class="bg-blue" style="font-size: 18px; text-align: center; padding: 7px 10px; margin-top: 0;color:#fff	">Search</h4>
			                	<!-- <div class="col-sm-6 col-xs-12">
			                		<span class="info-box-text">Month To Date</span> 
	                				<span class="info-box-number" >{{$currentMonthUsedId}}</span>
			                	</div>
	                			<div class="col-sm-6 col-xs-12">	
		                			<span class="info-box-text">Start To Date</span> 
	                				<span class="info-box-number" >{{$totalUsedCount}}</span>
		                		</div>
		                		<div class="col-sm-12 col-xs-12"><hr></div> -->
		                		<div class="col-sm-3 col-xs-12">
	                            	<input type="text"  name="from_date" id="from_date" class="form-control date" value="" placeholder="From Date"/>
		                        </div>
		                        
	                            <div class="col-sm-3 col-xs-12">
	                            	<input type="text"  name="to_date" id="to_date" class="form-control" value="" placeholder="To Date"/>
	                            </div>
	                            
	                            <div class="col-sm-3 col-xs-12"style="text-align:left" >
	                              <button type="button" class="btn btn-primary" onclick="getIds();">Search</button>
	                            </div>
	                            <div class="col-sm-3 col-xs-12">                 	<span class="info-box-number" id="IdsResult"></span>
		                		</div>
			                </div>
			            </div>      
	        		</div>
	        		<!-- <div class="col-sm-6 col-xs-12">
	        			<div class="box">
			                <div class="box-body">
			                	<h4 style="background-color:#d2d6de; font-size: 18px; text-align: center; padding: 7px 10px; margin-top: 0;color:#fff	">Scans</h4>
			                	<div class="col-sm-6 col-xs-12">
			                		<span class="info-box-text">Month To Date</span> 
	                				<span class="info-box-number" ></span>
			                	</div>
	                			<div class="col-sm-6 col-xs-12">	
		                			<span class="info-box-text">Start To Date</span> 
	                				<span class="info-box-number" ></span>
		                		</div>
		                		<div class="col-sm-12 col-xs-12"><hr></div>
		                		<div class="col-sm-3 col-xs-12">
	                            	<input type="text"  name="from_date_scan" id="from_date_scan" class="form-control date" value="" placeholder="From Date"/>
		                        </div>
		                        <div class="col-sm-3 col-xs-12"></div>
	                            <div class="col-sm-3 col-xs-12">
	                            	<input type="text"  name="to_date_scan" id="to_date_scan" class="form-control" value="" placeholder="To Date"/>
	                            </div>
	                            
	                            <div class="col-sm-3 col-xs-12"style="text-align:right" >
	                              <button type="button" class="btn btn-primary" onclick="getScans();">Search</button>
	                            </div>
	                            <div class="col-sm-12 col-xs-12"><hr>
	                            	<span class="info-box-number" id="IdsResultScan"></span>
		                		</div>
			                </div>
			            </div>			
	        		</div> -->
	        	</div>
        	</div>*/ ?>
        	<div class="col-sm-12 col-xs-12">
        		<div class="box ">
	        		<div class="box-header with-border">
			          <h3 class="box-title">Purchase Order History</h3>
			        </div><!-- /.box-header -->
			        <div class="box-body">
			        	<div class="table-responsive">
			        		<table class="table no-margin">
			                    <thead>
			                        <tr>
			                          <th>PO Number</th>
			                          <th>Date</th>
			                          <th>Quantity</th>
			                          <th>Type</th>
			                          <th>PO</th>
			                        </tr>
			                    </thead>
			                    <tbody>
			                    @foreach($po_results as $result)
		                    		<tr>
		                    			<td>{{$result->po_number}}</td>
		                    			<td>{{$result->date}}</td>	
		                    			<td><?PHP echo number_format($result->quantity,0,'.',',');?></td>
		                    			<td>{{$result->po_for}}</td>
		                    			<td><a href="{{URL::asset($result->po_file_path)}}" target="_blank"><i class="fa fa-file-pdf-o" aria-hidden="true"></i></a></td>
		                    		</tr>
			                    @endforeach 
			                    </tbody>
			                </table>    
			        	</div>
			        </div>
			    </div>
        	</div>
	        	    
        </div>
    </div>    
@stop 
@section('style')
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.4.1/css/bootstrap-datepicker3.css"/>
@stop
@section('script')
	<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.4.1/js/bootstrap-datepicker.min.js"></script>
	<script type="text/javascript" src="/assets/plugins/chartJs/Chart.bundle.js"></script>

	<script type="text/javascript" src="/assets/plugins/chartJs/utils.js"></script>
    <script type="text/javascript">
    	$(function (){
    		$("#from_date").datepicker({format:'M dd, yyyy',todayHighlight:true,autoclose: true})
    		$("#to_date").datepicker({format:'M dd, yyyy',todayHighlight:true,autoclose: true})
    		setInterval(function(){
    			$.get('autoRefreshStatement', function(response){
    				var res = $.parseJSON(response);
    				
    				$("#availableIds").html(res[0]);
    				$("#issuedIds").html(res[1]);
    				$("#totalIds").html(res[2]);	
    			});	
    		},20000);
    	});
    	var Months = {{$months}};
		var quantity = {{$quantity}}
		getChart(Months,quantity,"stmntcanvas");
    	
    	function getIds(){
    		
    		var from_date = $("#from_date").val();
    		
    		var to_date = $("#to_date").val();
    		
    		$.post('getId',{from_date:from_date,to_date:to_date}, function(response){
    			var res = $.parseJSON(response);
    			var canvas = '<canvas id="stmntcanvas" style="width: 681px; height: 285px;"></canvas>';
    			$("#stmntcanvas").remove();
    			$("#stmntcanvasDiv").html(canvas)
    			getChart(res.months,res.quantity,"stmntcanvas");
    			$("#IdsResult").html(res.total);
    		});

    	}
    	function getChart(labels,quantity,canvasId)
		{
			var stmntChatData = {
				labels: labels,
				datasets:[{
					label: 'IDs',
			        backgroundColor: "#0825f3",
			        borderColor: "#0825f3",
			        borderWidth: 1,
			        data: quantity
				}]
			};
			var stmntCanvas = $("#"+canvasId).get(0).getContext("2d");
			stmntCanvas.canvas.width = "618px";
			stmntCanvas.canvas.height = "279px";
			window.myBar = new Chart(stmntCanvas, {
		        type: 'bar',
		        data: stmntChatData,
		        options: {
		            responsive: true,
		            legend: {
		                display: false,
		            },
		            elements: {
		                rectangle: {
		                    borderWidth: 2,
		                }
		            },
		            tooltips: {
		                mode: 'nearest',
		                intersect: true
		            },
		            scales: {
		                xAxes: [{
		                    ticks: {
		                        beginAtZero: true,
		                        
		                    },
		                    barPercentage: 0.4,
		                    stacked: false,

		                }],
		                yAxes:[{stacked: false}]
		            },
		            
		            title: {
		                display: false,
		                text: ''
		            }
		        }
		    });
		}
    </script>
@stop
@extends('layouts.footer')
