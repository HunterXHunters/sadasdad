@extends('layouts.default')

@extends('layouts.header')

@section('style')
	
	<link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.3.0/css/datepicker.min.css" />
	<link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.3.0/css/datepicker3.min.css" />
	<link href="//cdn-na.infragistics.com/igniteui/latest/css/themes/infragistics/infragistics.theme.css" rel="stylesheet" />
	<link href="//cdn-na.infragistics.com/igniteui/latest/css/structure/infragistics.css" rel="stylesheet" />
	<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js"></script>	
	<style type="text/css">
		.cardWidget{
		    background: white;
		    padding: 10px;
		    width: 100%;
		    height: 500px;
		    border: 1px solid #ddd;
		    margin-top: 10px;
		}
		.cardWidget-heading{
			padding-bottom: 10px;
		    border-bottom: 1px solid #ddd;
		    line-height: 28px;
		}
		.reportHeading span{
		    font-size: 16px; 
		    font-weight: 600;
		}
		.custIcon.active{
		    background: #e5e5e5;
		    border: 1px solid #e5e5e5;
		}
		.navProductRep {
		    padding-left: 0;
		    margin-bottom: 0;
		    list-style: none;
		}
		.navProductRep>li {
		    position: relative;
		    display: block;
		}
		.navProductRep>li>a {
		    position: relative;
		    display: block;
		    padding: 8px 8px;
		}

		/* Icon when the collapsible content is shown */
		.rotate{
		    -moz-transition: all .5s linear;
		    -webkit-transition: all .5s linear;
		    transition: all .5s linear;
		    float: right;
		    font-size: 12px;
		    padding: 8px;
		}

		.rotate.up{
		    -moz-transform:rotate(180deg);
		    -webkit-transform:rotate(180deg);
		    transform:rotate(180deg);
		}
		.formInput{
		    border-bottom: 1px solid #ddd;
		    border-right: none;
		    border-left: none;
		    border-top: none;
		    width: 100%;
		    height: 30px;
		    margin-bottom: 5px;
		}
		input:focus, textarea:focus, select:focus {
		    outline: none;
		}
		.loader {
		    position:relative;
		    top:40%;
		    left: 40%;
		    border: 5px solid #f3f3f3;
		    border-radius: 50%;
		    border-top: 5px solid #d3d3d3;
		    width: 50px;
		    height: 50px;
		    -webkit-animation: spin 2s linear infinite;
		    animation: spin 2s linear infinite;
		}
		.input-group-addon {
		    padding: 6px 12px;
		    font-size: 14px;
		    font-weight: 400;
		    line-height: 1;
		    color: #555;
		    text-align: center;
		    background-color: #eee;
		    border: 1px solid #eee !important;
		    border-radius: 0px !important;
		}
		.submitBut{
		    height: 30px;
		    border-radius: 0px;
		    line-height: 0;
		    border-color: #abe3ab;
		    color: #abe3ab;
		}

		/*
		    disabling fusion trail label
		*/
		[class*="creditgroup"]{
		  display: none;
		}
	</style>
@stop
@section('script')
	
	<script>
		var groupResult = {{$groupResult}};
		var groups = {{$groups}};
		var shiftResults = {{$shiftResults}};
		var catResults = {{$catResults}};
		var locationResults = {{$locationResults}};
		var locationTypeResults = {{$locationTypeResults}};
		var productsResult = {{$productsResult}};
		var supplierLocation = {{$supplierLocationResults}};
		var dispatchLocationResults = {{$dispatchLocationResults}}
		$(document).ready(function() {
		   $('#dateFromPicker')
			    .datepicker({
			        autoclose: true,
			        format: 'yyyy-mm-dd'
			    });
			    $('#dateToPicker')
			    .datepicker({
			        autoclose: true,
			        format: 'yyyy-mm-dd'
			    });

			    $('#dateFromSupplier')
			    .datepicker({
			        autoclose: true,
			        format: 'yyyy-mm-dd'
			    });
			    $('#dateToSupplier')
			    .datepicker({
			        autoclose: true,
			        format: 'yyyy-mm-dd'
			    });
			    $('#FromDispatchDate')
			    .datepicker({
			        autoclose: true,
			        format: 'yyyy-mm-dd'
			    });
			    $('#ToDispatchDate')
			    .datepicker({
			        autoclose: true,
			        format: 'yyyy-mm-dd'
			    });
			});
		$(".rotate").click(function(){
	 		$(this).toggleClass("up")  ; 
		})
		
	</script>
@stop

@extends('layouts.sideview')

@section('content')
	<script src="http://code.jquery.com/ui/1.10.3/jquery-ui.min.js"></script>	
	<script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.6.4/angular.min.js"></script>

  	<!-- App File -->
  	
  	{{HTML::script('assets/app.js')}}

  	<script src="//cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.3.0/js/bootstrap-datepicker.min.js"></script>

  	<!-- Fusion Chart -->
  	
  	{{HTML::script('assets/angular-fusioncharts.min.js')}}
	<script src="https://static.fusioncharts.com/code/latest/fusioncharts.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.10.6/moment.min.js"></script>

	<!-- Ignite UI -->

	<script src="http://cdn-na.infragistics.com/igniteui/2017.2/latest/js/infragistics.core.js"></script>
	<script src="http://cdn-na.infragistics.com/igniteui/2017.2/latest/js/infragistics.lob.js"></script>

	<div class="box">
		<div class="box-header with-border">
	      <h3 class="box-title">Dashboard</h3>
	    </div><!-- /.box-header -->
	    <div class="box-body" ng-app="eSealKpi" ng-controller="eSealKpiCtrl">
	    	<div class="row" ng-init="loadFilterData()">
	    		<div class="col-md-6" ng-init="loadReport('allgroupedProd', 'allProducts', 'allLocations', 'Plant', '', 'allCategoryName', 'allShifts','current','production','{{$startDate}}','{{$endDate}}');">
				  <div class="cardWidget">
				    <div class="cardWidget-heading">
				      <div class="reportHeading" data-toggle="collapse" data-target="#prodReport" class="btn btn-lg btn-info collapsed"><span>Production</span><i class="fa fa-chevron-down rotate"></i></div>
				      <div id="prodReport" class="collapse">
				      	<div class="row">
				      		<!-- <div class="col-md-3">
								<select class="formInput" ng-model="groupedProd">
								    <option value="allgroupedProd">Product Group</option>
								    <option ng-repeat="(key,value) in productGroupId" value="@{{value.group_id}}">@{{value.name}}</option>
								</select>
							</div> -->
							<div class="col-md-3">
								<select class="formInput" ng-model="productNameList">
								    <option value="allProducts" >Select Product</option>
								    
								    <option ng-repeat="(key,value) in productIds" value="@{{value.product_id}}">@{{value.product_name}}</option>
								</select>
							</div>
							<div class="col-md-3">
							  	<select class="formInput" ng-model="selectedLoaction">
					                <option value="allLocations">Location Name</option>
								    
					                <option ng-repeat="(key,value) in locationList" value="@{{value.location_id}}">@{{value.location_name}}</option>
					            </select>
							</div>
							
							<div class="col-md-3">
							  	<input type="text" class="formInput" ng-model="selectedBatch" placeholder="Batch Number">
							</div>

							<div class="col-md-3">
							  	<select class="formInput" ng-model="selectedCategory">
					                <option value="allCategoryName">Category Name</option>
								    
					                <option ng-repeat="(key,value) in categoryList" value="@{{value.category_id}}">@{{value.category_name}}</option>
					            </select>
							</div>
							<div class="col-md-3">
							  	<select class="formInput" ng-model="selectedShift">
					                <option value="allShifts">Shift</option>
								    
					                <option ng-repeat="(key,value) in shiftList" value="@{{value.shift}}">@{{value.shift}}</option>
					            </select>
							</div>
							<div class="col-md-3">
							  	<select class="formInput" ng-model="productionPeriodType" ng-change="loadSelectedDates(productionPeriodType)">
					                <option value="">Period Type</option>
					                <option value="today">Today</option>
		                            <option value="yesterday">Yesterday</option>
		                            <option value="wtd">WTD</option>
		                            <option value="mtd">MTD</option>
		                            <option value="ytd">YTD</option>
		                            <option value="customDate">Custom Date</option>
					            </select>
							</div>
							<div ng-show="productionPeriodType == 'customDate'">
								<div class="col-md-3 form-group">
					        		<div class="input-group input-append date" id="dateFromPicker">
						                <input type="text" class="formInput" placeholder="From Date" name="date" id="fromDate" ng-model="fromDate"/>
						                <span class="input-group-addon add-on"><span class="glyphicon glyphicon-calendar"></span></span>
						            </div>
					        	</div>
					        	<div class="col-md-3 form-group">
					        		<div class="input-group input-append date" id="dateToPicker">
						                <input type="text" class="formInput" placeholder="To Date" name="date" id="toDate" ng-model="toDate"/>
						                <span class="input-group-addon add-on"><span class="glyphicon glyphicon-calendar"></span></span>

						            </div>
					        	</div>	
					        	<input id="preport_name" ng-model="preport_name" style="display:none" value=""/>
							</div>
				        	<div class="col-md-3">
				        		<button type="button" class="btn btn-default submitBut" ng-click="loadReport(groupedProd, productNameList, selectedLoaction, 'Plant', selectedBatch, selectedCategory, selectedShift, productionPeriodType,'production',fromDate,toDate)">Submit</button>
				        	</div>
				      	</div>
				      </div>
				    </div>
				    <div class="cardWidget-body">
        				<div id="loadProductionReport" style="display: none" class="loader" ></div>

				    	<div class="tabbable tabs-below">
		                    <div class="tab-content">
		                        <div class="tab-pane active" id="prodReportColumn"> 
		                        	<div id="productionStackChart"></div> 
		                        </div>
		                        <div class="tab-pane" id="prodReportStacked">
		                        	<div id="productionLineChart"></div> 
		                        </div>
		                        <div class="tab-pane" id="prodReportGrid">
		                        	<div id="prodReportIgGrid"></div>
		                        </div>
		                    </div>
		                    <div class="barChatCLass" style="border-top: 1px solid #eee;position: absolute;bottom: 0px;width: 93%;">
		                        <ul class="navProductRep" style="display: flex;float: right;">
		                            <li class="custIcon active"><a href="#prodReportColumn" data-toggle="tab" class="fa fa-bar-chart fa-lg" aria-hidden="true" title="Stacked Chart"></a></li>
		                            <li class="custIcon"><a href="#prodReportStacked" data-toggle="tab" class="fa fa-line-chart fa-lg" aria-hidden="true" title="Stacked Chart"></a></li>
		                            <li class="custIcon"><a href="#prodReportGrid" ng-click="ngGridFIx()" data-toggle="tab" class="fa fa-th fa-lg" aria-hidden="true" title="Grid Data"></a></li>
		                        </ul>
		                    </div>
		                </div>
				    </div>
				  </div>
				</div>
				<div class="col-md-6" ng-init="loadReport('allgroupedProd', 'allProducts', 'allLocations', 'Supplier', '', 'allCategoryName', 'allShifts','current','supplier','{{$startDate}}','{{$endDate}}');">
				  <div class="cardWidget">
				    <div class="cardWidget-heading">
				      <div class="reportHeading" data-toggle="collapse" data-target="#supplierReport" class="btn btn-lg btn-info collapsed"><span>Supplier - Production</span><i class="fa fa-chevron-down rotate"></i></div>
				      <div id="supplierReport" class="collapse">
				      	<div class="row">
							<div class="col-md-3">
								<select class="formInput" ng-model="supplierNameList">
								    <option value="allProducts">Select Product</option>
								    <option ng-repeat="(key,value) in productIds" value="@{{value.product_id}}">@{{value.product_name}}</option>
								</select>
							</div>
							<div class="col-md-3">
							  	<select class="formInput" ng-model="selectedsupplierLoaction">
					                <option value="allLocations">Location Name</option>
					                <option ng-repeat="(key,value) in supplierLocation" value="@{{value.location_id}}">@{{value.location_name}}</option>
					            </select>
							</div>
							

							<div class="col-md-3">
							  	<select class="formInput" ng-model="selectedsupplierCategory">
					                <option value="allCategoryName">Category Name</option>
					                <option ng-repeat="(key,value) in categoryList" value="@{{value.category_id}}">@{{value.category_name}}</option>
					            </select>
							</div>
							
							<div class="col-md-3">
							  	<select class="formInput" ng-model="productionsupplierPeriodType" ng-change="loadSelectedDates(productionsupplierPeriodType)">
					                <option value="" selected="selected">Period Type</option>
					                <option value="today">Today</option>
		                            <option value="yesterday">Yesterday</option>
		                            <option value="wtd">WTD</option>
		                            <option value="mtd">MTD</option>
		                            <option value="ytd">YTD</option>
		                            <option value="customDate">Custom Date</option>
					            </select>
							</div>
							<div ng-show="productionsupplierPeriodType == 'customDate'">
								<div class="col-md-3 form-group">
				        		<div class="input-group input-append date" id="dateFromSupplier">
					                <input type="text" class="formInput" placeholder="From Date" name="date" id="sfromDate" ng-model="sfromDate"/>
					                <span class="input-group-addon add-on"><span class="glyphicon glyphicon-calendar"></span></span>
					            </div>
				        	</div>
				        	<div class="col-md-3 form-group">
				        		<div class="input-group input-append date" id="dateToSupplier">
					                <input type="text" class="formInput" placeholder="To Date" name="date" id="stoDate" ng-model="stoDate"/>
					                <span class="input-group-addon add-on"><span class="glyphicon glyphicon-calendar"></span></span>
					            </div>
				        	</div>
				        	<input id="report_name" ng-model="report_name" style="display:none" value="supplier"/>
							</div>
				        	<div class="col-md-3">
				        		<button type="button" class="btn btn-default submitBut" ng-click="loadReport(groupedProd, supplierNameList, selectedsupplierLoaction, 'Supplier', selectedBatch, selectedsupplierCategory, 'allShifts', productionsupplierPeriodType,'supplier',sfromDate,stoDate)">Submit</button>
				        	</div>
				      	</div>
				      </div>
				    </div>
				    <div class="cardWidget-body">
	    				<div id="loadSuplierReport" style="display: none" class="loader" ></div>

				    	<div class="tabbable tabs-below">
		                    <div class="tab-content">
		                        <div class="tab-pane active" id="supplierReportColumn"> 
		                        	<div id="supplierStackChart"></div> 
		                        </div>
		                        <div class="tab-pane" id="supplierReportStacked">
		                        	<div id="supplierLineChart"></div> 
		                        </div>
		                        <div class="tab-pane" id="supplierReportGrid">
	                        	<div id="supplierReportIgGrid"></div> 
		                        </div>
		                    </div>
		                    <div class="barChatCLass" style="border-top: 1px solid #eee;position: absolute;bottom: 0px;width: 93%;">
		                        <ul class="navProductRep" style="display: flex;float: right;">
		                            <li class="custIcon active"><a href="#supplierReportColumn" data-toggle="tab" class="fa fa-bar-chart fa-lg" aria-hidden="true" title="Stacked Chart"></a></li>
		                            <li class="custIcon"><a href="#supplierReportStacked" data-toggle="tab" class="fa fa-line-chart fa-lg" aria-hidden="true" title="Stacked Chart"></a></li>
		                            <li class="custIcon"><a href="#supplierReportGrid" ng-click="ngGridFIx()" data-toggle="tab" class="fa fa-th fa-lg" aria-hidden="true" title="Grid Data"></a></li>
		                        </ul>
		                    </div>
		                </div>
				    </div>
				  </div>
				</div>
				<div class="col-md-6" ng-init="loadInventoryReport('allgroupedProd', 'allProducts', 'allLocations', 'allLocationType', 'allCategoryName')">
					<div class="cardWidget">
					    <div class="cardWidget-heading">
					      <div class="reportHeading" data-toggle="collapse" data-target="#inventoryReport" class="btn btn-lg btn-info collapsed"><span>Inventory Availablity</span><i class="fa fa-chevron-down rotate"></i></div>
					      <div id="inventoryReport" class="collapse">
					      	<div class="row">
								<div class="col-md-3">
									<select class="formInput" ng-model="groupedProdInventory">
									    <option value="allgroupedProd">Select Product Group</option>		    
									    <option ng-repeat="(key,value) in productGroupId" value="@{{value.group_id}}">@{{value.name}}</option>
									</select>
								</div>
								<div class="col-md-3">
									<select class="formInput" ng-model="InventoryNameList">
									    <option value="allProducts">Select Product</option>
									    <option ng-repeat="(key,value) in productIds" value="@{{value.product_id}}">@{{value.product_name}}</option>
									</select>
								</div>
								<div class="col-md-3">
						            <select class="formInput" ng-model="selectedInventoryLoactionType" ng-change="getLocation('inventoryLocations', selectedInventoryLoactionType)">
						                <option value="allLocationType">Select Location Type</option>
						                <option ng-repeat="(key,value) in locationTypeList" value="@{{value.location_type_id}}">@{{value.location_type_name}}</option>
						            </select>
								</div>
								<div class="col-md-3">
								  	<select id="inventoryLocations" class="formInput" ng-model="selectedInventoryLoaction">
						                <option value="allLocations">Select Location Name</option>
						                <option ng-repeat="(key,value) in locationList" value="@{{value.location_id}}">@{{value.location_name}}</option>
						            </select>
								</div>
								

								<div class="col-md-3">
								  	<select class="formInput" ng-model="selectedInventoryCategory">
						                <option value="allCategoryName">Select Category Name</option>
						                <option ng-repeat="(key,value) in categoryList" value="@{{value.category_id}}">@{{value.category_name}}</option>
						            </select>
								</div>
								
					        	<div class="col-md-3">
					        		<button type="button" class="btn btn-default submitBut" ng-click="loadInventoryReport(groupedProdInventory, InventoryNameList, selectedInventoryLoaction, selectedInventoryLoactionType, selectedInventoryCategory)">Submit</button>
					        	</div>
					      	</div>
					      </div>
					    </div>
					    <div class="cardWidget-body">
		    				<div id="loadInventoryReport" style="display: none" class="loader" ></div>

					    	<div class="tabbable tabs-below">
			                    <div class="tab-content">
			                        <div class="tab-pane active" id="inventoryReportColumn"> 
			                        	<div id="inventoryPieChart"></div> 
			                        </div>
			                        <div class="tab-pane" id="inventoryReportStacked">
			                        	<div id="inventoryColumnChart"></div> 
			                        </div>
			                        <div class="tab-pane" id="inventoryReportGrid">
			                        	<div id="inventoryReportIgGrid"></div> 
			                        </div>
			                    </div>
			                    <div class="barChatCLass" style="border-top: 1px solid #eee;position: absolute;bottom: 0px;width: 93%;">
			                        <ul class="navProductRep" style="display: flex;float: right;">
			                            <li class="custIcon active"><a href="#inventoryReportColumn" data-toggle="tab" class="fa fa-pie-chart fa-lg" aria-hidden="true" title="Stacked Chart"></a></li>
			                            <li class="custIcon"><a href="#inventoryReportStacked" data-toggle="tab" class="fa fa-bar-chart fa-lg" aria-hidden="true" title="Stacked Chart"></a></li>
			                            <li class="custIcon"><a href="#inventoryReportGrid" ng-click="ngGridFIx()" data-toggle="tab" class="fa fa-th fa-lg" aria-hidden="true" title="Grid Data"></a></li>
			                        </ul>
			                    </div>
			                </div>
					    </div>
					  </div>
				</div>
				<div class="col-md-6" ng-init="loaddispatchReport('allProducts', 'allLocations', 'undefined', 'current')">
					<div class="cardWidget">
					    <div class="cardWidget-heading">
					      <div class="reportHeading" data-toggle="collapse" data-target="#dispatchReport" class="btn btn-lg btn-info collapsed"><span>Dispatch</span><i class="fa fa-chevron-down rotate"></i></div>
					      <div id="dispatchReport" class="collapse">
					      	<div class="row">
								<div class="col-md-3">
									<select class="formInput" ng-model="dispatchNameList">
									    <option value="allProducts">Select Product</option>
									    <option ng-repeat="(key,value) in productIds" value="@{{value.product_id}}">@{{value.product_name}}</option>
									</select>
								</div>
								<div class="col-md-3">
								  	<select class="formInput" ng-model="selecteddispatchLoaction">
						                <option value="allLocations">Location Name</option>
						                <option ng-repeat="(key,value) in dispatchLocationList" value="@{{value.location_id}}">@{{value.location_name}}</option>
						            </select>
								</div>
								<div class="col-md-3">
								  	<input type="text" class="formInput" ng-model="selectedDispatchBatch" placeholder="Batch Number">
								</div>

								<div class="col-md-3">
								  	<select class="formInput" ng-model="DispatchPeriodType" ng-change="loadSelectedDates(DispatchPeriodType)">
						                <option value="" selected="selected">Period Type</option>
						                <option value="today">Today</option>
			                            <option value="yesterday">Yesterday</option>
			                            <option value="wtd">WTD</option>
			                            <option value="mtd">MTD</option>
			                            <option value="ytd">YTD</option>
			                            <option value="customDate">Custom Date</option>
						            </select>
								</div>
								<div ng-show="DispatchPeriodType == 'customDate'">
									<div class="col-md-3 form-group">
						        		<div class="input-group input-append date" id="FromDispatchDate">
							                <input type="text" class="formInput" placeholder="From Date" name="date" id="fromDispatchDate" ng-model="fromDispatchDate"/>
							                <span class="input-group-addon add-on"><span class="glyphicon glyphicon-calendar"></span></span>
							            </div>
						        	</div>
						        	<div class="col-md-3 form-group">
						        		<div class="input-group input-append date" id="ToDispatchDate">
							                <input type="text" class="formInput" placeholder="To Date" name="date" id="toDispatchDate" ng-model="toDispatchDate"/>
							                <span class="input-group-addon add-on"><span class="glyphicon glyphicon-calendar"></span></span>

							            </div>
						        	</div>	
						        	<input id="preDispatch_name" ng-model="preDispatch_name" style="display:none" value=""/>
								</div>
								
					        	<div class="col-md-3">
					        		<buttson type="button" class="btn btn-default submitBut" ng-click="loaddispatchReport(dispatchNameList, selecteddispatchLoaction, selectedDispatchBatch, DispatchPeriodType)">Submit</button>
					        	</div>
					      	</div>
					      </div>
					    </div>
					    <div class="cardWidget-body">
		    				<div id="loaddispatchReport" style="display: none" class="loader" ></div>

					    	<div class="tabbable tabs-below">
			                    <div class="tab-content">
			                        <div class="tab-pane active" id="dispatchReportColumn"> 
			                        	<div id="dispatchPieChart"></div> 
			                        </div>
			                        <div class="tab-pane" id="dispatchReportStacked">
			                        	<div id="dispatchColumnChart"></div> 
			                        </div>
			                        <div class="tab-pane" id="dispatchReportGrid">
			                        	<div id="dispatchReportIgGrid"></div> 
			                        </div>
			                    </div>
			                    <div class="barChatCLass" style="border-top: 1px solid #eee;position: absolute;bottom: 0px;width: 93%;">
			                        <ul class="navProductRep" style="display: flex;float: right;">
			                            <li class="custIcon active"><a href="#dispatchReportColumn" data-toggle="tab" class="fa fa-pie-chart fa-lg" aria-hidden="true" title="Stacked Chart"></a></li>
			                            <li class="custIcon"><a href="#dispatchReportStacked" data-toggle="tab" class="fa fa-bar-chart fa-lg" aria-hidden="true" title="Stacked Chart"></a></li>
			                            <li class="custIcon"><a href="#dispatchReportGrid" ng-click="ngGridFIx()" data-toggle="tab" class="fa fa-th fa-lg" aria-hidden="true" title="Grid Data"></a></li>
			                        </ul>
			                    </div>
			                </div>
					    </div>
					  </div>
				</div>	
	    	</div>
	    </div>
	</div>    	
@stop