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
	    font-size: 30px;
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
      <div class="col-sm-6 col-xs-6" style="padding-top: 10px;"><h3 class="box-title">eSeal Wallet</h3></div>
       <div class="col-sm-6 col-xs-6" align="right"><button type="button" class="btn btn-default" onclick="location.href='download'">Download</button></div>
    </div><!-- /.box-header -->
    <div class="box-body">
    	<div class="col-sm-3 col-xs-12">
    		<div class="box" style="border: 2px solid #d2d6de;">
				<div class="box-header">
		          <h3 class="box-title">Current Balance</h3>
		        </div><!-- /.box-header --> 
		        <div class="box-body" >
		        	<div class="row">
						<div class="col-sm-12 col-xs-12">
							<div class="info-tile" style="visibility: visible; opacity: 1; display: block; transform: translateY(0px);">
								<div class="tile-icon"><i class="fa fa-barcode"></i></div>
								<div class="tile-body"><span id="availableIds">{{$availableIds}}</span></div>
					            <div class="tile-footer" align="right">Bank <i class="fa fa-question-circle" data-toggle="tooltip" title="IDs available"></i></div>
							</div>
						</div>
					</div>                    
					
		        </div>
			</div>
    	</div>
    	<div class="col-sm-9 col-xs-12">
    		<div class="box" style="border: 2px solid #d2d6de;">
				<div class="box-header">
		          <h3 class="box-title">Drawn Analysis</h3>
		        </div><!-- /.box-header --> 
		        <div class="box-body" >
		        	<div class="col-md-4">
						<div class="info-tile" style="visibility: visible; opacity: 1; display: block; transform: translateY(0px);">
							<div class="tile-icon"><i class="fa fa-download"></i></div>
							<div class="tile-body"><span id="issuedIds">{{$drawn_count}}</span></div>
				            <div class="tile-footer" align="right">
				            	<div>Total Drawn <i class="fa fa-question-circle" data-toggle="tooltip" title="IDs printed or download but not activated"></i></div>
				            </div>
						</div>
					</div>                
					<div class="col-md-4" >
						<div class="info-tile" style="visibility: visible; opacity: 1; display: block; transform: translateY(0px);">
							<div class="tile-icon"><i class="fa fa-print"></i></div>
							<div class="tile-body"><span id="printIds">{{$print_count}}</span></div>
				            <div class="tile-footer" align="right">
				            	<div>Print <i class="fa fa-question-circle" data-toggle="tooltip" title="IDs printed  but not activated"></i></div>
				            </div>
						</div>
					</div>
					<div class="col-md-4" >
						<div class="info-tile" style="visibility: visible; opacity: 1; display: block; transform: translateY(0px);">
							<div class="tile-icon"><i class="fa fa-download"></i></div>
							<div class="tile-body"><span id="downloadIds">{{$download_count}}</span></div>
				            <div class="tile-footer" align="right">
				            	<div>Downloaded <i class="fa fa-question-circle" data-toggle="tooltip" title="IDs download but not activated"></i></div>
				            </div>
						</div>
					</div>
		        </div>
			</div>
    	</div>
    	<div class="col-sm-12 col-xs-12">
    		<div class="box">
    			<div class="box-header">
		          <h3 class="box-title">Usage History</h3>
		        </div><!-- /.box-header --> 
    			<div class="box-body">
    				<div class="row">
						<div class="col-md-3" >
							<div class="info-tile" style="visibility: visible; opacity: 1; display: block; transform: translateY(0px);" >
								<div class="tile-icon"><i class="fa fa-bar-chart-o"></i></div>
								<div class="tile-body"><span id="totalIds">{{$totalUsedCount}}</span></div>
					            <div class="tile-footer" align="right">Total Used <i class="fa fa-question-circle" data-toggle="tooltip" title="IDs scanned at least once"></i></div>
							</div>
						</div>
						<div class="col-md-3" >
							<div class="info-tile" style="visibility: visible; opacity: 1; display: block; transform: translateY(0px);" >
								<div class="tile-icon"><i class="fa fa-file"></i></div>
								<div class="tile-body"><span id="productIds">{{$usagesHistory[0]->qty}}</span></div>
					            <div class="tile-footer" align="right">Products <i class="fa fa-question-circle" data-toggle="tooltip" title="IDs used for products"></i></div>
							</div>
						</div>
						<div class="col-md-3" >
							<div class="info-tile" style="visibility: visible; opacity: 1; display: block; transform: translateY(0px);" >
								<div class="tile-icon"><i class="fa fa-cube"></i></div>
								<div class="tile-body"><span id="cartonIds">{{$usagesHistory[1]->qty}}</span></div>
					            <div class="tile-footer" align="right">Cartons <i class="fa fa-question-circle" data-toggle="tooltip" title="IDs used for cartons"></i></div>
							</div>
						</div>
						<div class="col-md-3" >
							<div class="info-tile" style="visibility: visible; opacity: 1; display: block; transform: translateY(0px);" >
								<div class="tile-icon"><i class="fa fa-truck"></i></div>
								<div class="tile-body"><span id="tpIds">{{$usagesHistory[3]->qty}}</span></div>
					            <div class="tile-footer" align="right">TP <i class="fa fa-question-circle" data-toggle="tooltip" title="IDs used for TP"></i></div>
							</div>
						</div>
					</div>
    			</div>
    		
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
		
		
    	<div class="col-sm-12 col-xs-12">
    		<div class="box ">
        		<div class="box-header with-border">
		          <h3 class="box-title">Invoice  History</h3>
		        </div><!-- /.box-header -->
		        <div class="box-body">
		        	<div class="table-responsive">
		        		<table class="table no-margin">
		                    <thead>
		                        <tr>
		                          <th>Invoice Number</th>
		                          <th>PO Number</th>
		                          <th>Date</th>
		                          <th>Quantity</th>
		                          <th>Type</th>
		                          
		                        </tr>
		                    </thead>
		                    <tbody>
		                    @foreach($po_results as $result)
	                    		<tr>
	                    			<td><a href="{{URL::asset($result->invoice_file_path)}}" target="_blank">{{$result->invoice_no}}</a></td>
	                    			<td><a href="{{URL::asset($result->invoice_file_path)}}" target="_blank">{{$result->po_number}}</a></td>
	                    			<td>{{$result->date}}</td>	
	                    			<td><?PHP echo number_format($result->quantity,0,'.',',');?></td>
	                    			<td>{{$result->po_for}}</td>
	                    			
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
<!-- <div id="myModal" class="modal fade" role="dialog">
	<div class="modal-dialog" style="width: 360px;">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal">&times;</button>
				<h4 class="modal-title" id="popupHeading">Heading</h4>
			</div>
			<div class="modal-body" id="popupBody">
				
			</div>
		</div>
	</div>
</div> -->    
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
			/*setInterval(function(){
    			$.get('autoRefreshStatement', function(response){
    				var res = $.parseJSON(response);
    				
    				$("#availableIds").html(res.availableIds);
    				$("#issuedIds").html(res.drawn_count);
    				$("#printIds").html(res.print_count);
    				$("#downloadIds").html(res.download_count);
    				$("#productIds").html(res.product_count);
    				$("#cartonIds").html(res.corton_count);
    				$("#tpIds").html(res.tp_count);
    				

    			});	
    		},20000);*/
    		$('[data-toggle="tooltip"]').tooltip();
    	});
    	var Months = {{$months}};
		var quantity = {{$quantity}}
		getChart(Months,quantity,"stmntcanvas");
    	
    	/*function getPopup(initFrom)
    	{
    		$.post('getHistory',{init:initFrom}, function(response){
    			if(initFrom=='Drawn'){
    				$("#popupHeading").html('Drawn Analysis');
    				
    				var res = $.parseJSON(response);
    				var html = '';

    				html = '<div class="row"><div class="col-sm-12 col-xs-12"><div class="info-tile" style="visibility: visible; opacity: 1; display: block; transform: translateY(0px);"><div class="tile-icon"><i class="fa fa-print"></i></div><div class="tile-body"><span id="availableIds">'+res.printed+'</span></div><div class="tile-footer" align="right">Bank <i class="fa fa-question-circle" data-toggle="tooltip" title="IDs available"></i></div></div></div></div>';                    
					html +='<div class="row"><div class="col-md-12" data-toggle="modal" data-target="#myModal" style="cursor: pointer;"><div class="info-tile" style="visibility: visible; opacity: 1; display: block; transform: translateY(0px);"><div class="tile-icon"><i class="fa fa-download"></i></div><div class="tile-body"><span id="issuedIds">'+res.download+'</span></div><div class="tile-footer" align="right"><div>Download <i class="fa fa-question-circle" data-toggle="tooltip" title="IDs printed or download but not activated"></i></div></div></div></div></div>';
					$("#popupBody").html(html);	
    			}else if(initFrom=='Activated'){
    				$("#popupHeading").html('Activated Analysis');
    				var res = $.parseJSON(response);
    				var html = '';
    				if(typeof(res.product) != "undefined" && res.product !== null){
    					html = '<div class="row"><div class="col-sm-12 col-xs-12"><div class="info-tile" style="visibility: visible; opacity: 1; display: block; transform: translateY(0px);"><div class="tile-icon"><i class="fa fa-file"></i></div><div class="tile-body"><span id="availableIds">'+res.product+'</span></div><div class="tile-footer" align="right">products <i class="fa fa-question-circle" data-toggle="tooltip" title="IDs activated for Products"></i></div></div></div></div>';
    				}
    				if(typeof(res.carton) != "undefined" && res.carton !== null){
    					html +='<div class="row"><div class="col-md-12" data-toggle="modal" data-target="#myModal" style="cursor: pointer;"><div class="info-tile" style="visibility: visible; opacity: 1; display: block; transform: translateY(0px);"><div class="tile-icon"><i class="fa fa-cube"></i></div><div class="tile-body"><span id="issuedIds">'+res.carton+'</span></div><div class="tile-footer" align="right"><div>Carton <i class="fa fa-question-circle" data-toggle="tooltip" title="IDs activated for Carton"></i></div></div></div></div></div>';	
    				}                    
					if(typeof(res.tp) != "undefined" && res.tp !== null){
						html +='<div class="row"><div class="col-md-12" data-toggle="modal" data-target="#myModal" style="cursor: pointer;"><div class="info-tile" style="visibility: visible; opacity: 1; display: block; transform: translateY(0px);"><div class="tile-icon"><i class="fa fa-truck"></i></div><div class="tile-body"><span id="issuedIds">'+res.tp+'</span></div><div class="tile-footer" align="right"><div>TP <i class="fa fa-question-circle" data-toggle="tooltip" title="IDs activated for transport permit"></i></div></div></div></div></div>';
					}	
					$("#popupBody").html(html);	
    			}	
    		});
    	}*/

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
