@extends('layouts.default')
@extends('layouts.header')
@extends('layouts.sideview')
@section('content')
	<style type="text/css">
		.box.box-primary{border:1px solid #d2d6de;}
		.box{border:1px solid #d2d6de;}
		canvas {
        -moz-user-select: none;
        -webkit-user-select: none;
        -ms-user-select: none;
      }
      #map_field{
          position:relative;
          width:100%;
          height: calc(100% - 60px);
          
      }
      #map_customer{
        position:relative;
        width:100%;
        height: calc(100% - 60px);
      }
	</style>
	
	<div class="nav-tabs-custom">
		<ul class="nav nav-tabs">
			<li role="presentation" class="active"><a href="#dashboard" role="tab" data-toggle="tab">Dashboard</a></li>
            <li role="presentation"><a href="#supplier" role="tab" data-toggle="tab">Live Supplier</a></li>
            <li role="presentation"><a href="#production" role="tab" data-toggle="tab">Live Production </a></li>
            <li role="presentation"><a href="#warehouse" role="tab" data-toggle="tab">Live Inventory</a></li>
            <li role="presentation"><a href="#channel" role="tab" data-toggle="tab">Live Channel</a></li>
            <!-- <li role="presentation"><a href="#field" role="tab" data-toggle="tab">Field Sales</a></li>
            <li role="presentation"><a href="#customer" role="tab" data-toggle="tab">Customer Information</a></li> -->
            <!-- <li role="presentation"><a href="#service" role="tab" data-toggle="tab">Live Service</a></li> -->
		</ul>
		<div class="tab-content" id="ParentTab">
			<div role="tabpanel" class="tab-pane active" id="dashboard">
				<div class="container" style="width: 100%;padding-left: 0; padding-right: 0;margin-top:5px">
					<div class="row">
						<div class="col-sm-4 col-xs-12">
							<div class="box box-primary">
				                <div class="box-header  with-border">
				                  <h3 class="box-title">Today's Production</h3>
				                  <div class="box-tools pull-right">
				                    <button class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
				                    <button class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button>
				                  </div>
				                </div><!-- /.box-header -->
				                <div class="box-body">
				                	<ul class="products-list product-list-in-box">
					                    <li class="item">
					                      <div class="col-sm-4"><strong>Qty</strong></div>
					                      <div class="col-sm-4"><strong>Material Code</strong></div>
					                      <div class="col-sm-4"><strong>Product Name</strong></div>
					                    </li><!-- /.item -->
					                    <li class="item">
					                      <div class="col-sm-4"><span class="label label-info pull-left" style="font-size:14px;">100</span></div>
					                      <div class="col-sm-4">
					                        <a href="javascript::;" class="product-title">2345676 </a>
					                      </div>
					                      <div class="col-sm-4">DU 875 PRO</div>
					                    </li><!-- /.item -->
					                    <li class="item">
					                      <div class="col-sm-4"><span class="label label-info pull-left" style="font-size:14px;">100</span></div>
					                      <div class="col-sm-4">
					                        <a href="javascript::;" class="product-title">0239844</a>
					                      </div>
					                      <div class="col-sm-4">DU 885 PRO</div>
					                    </li><!-- /.item -->
					                    <li class="item">
					                      <div class="col-sm-4"><span class="label label-info pull-left" style="font-size:14px;">100</span></div>
					                      <div class="col-sm-4">
					                        <a href="javascript::;" class="product-title">9876543 </a>
					                      </div>
					                      <div class="col-sm-4">EI Power 750</div>
					                    </li><!-- /.item -->
					                    <li class="item">
					                      <div class="col-sm-4"><span class="label label-info pull-left" style="font-size:14px;">200</span></div>
					                      <div class="col-sm-4">
					                        <a href="javascript::;" class="product-title">28192103</a>
					                      </div>
					                      <div class="col-sm-4">EI Power 950</div>
					                    </li><!-- /.item -->
					                </ul>  
				                </div><!-- /.box-body -->
				            </div>
						</div>
						<div class="col-sm-4 col-xs-12">
							<div class="box box-primary">
				                <div class="box-header with-border">
				                  <h3 class="box-title">Today's Inspection</h3>
				                  <div class="box-tools pull-right">
				                    <button class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
				                    <button class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button>
				                  </div>
				                </div><!-- /.box-header -->
				                <div class="box-body">
				                	<ul class="products-list product-list-in-box">
					                    <li class="item">
					                      <div class="col-sm-4"><strong>Qty Approved</strong></div>
					                      <div class="col-sm-4"><strong>Qty Failed</strong></div>
					                      <div class="col-sm-4"><strong>Product Name</strong></div>
					                    </li><!-- /.item -->
					                    <li class="item">
					                      <div class="col-sm-4"><span class="label label-info pull-left" style="font-size:14px;background-color:#008000 !important;">100</span></div>
					                      <div class="col-sm-4">
					                        <span class="label label-info pull-left" style="font-size:14px;background-color:#ff0000 !important;">2 </span>
					                      </div>
					                      <div class="col-sm-4">DU 875 PRO</div>
					                    </li><!-- /.item -->
					                    <li class="item">
					                      <div class="col-sm-4"><span class="label label-info pull-left" style="font-size:14px;background-color:#008000 !important;">100</span></div>
					                      <div class="col-sm-4"><span class="label label-info pull-left" style="font-size:14px;background-color:#ff0000 !important;">0 </span></div>
					                      <div class="col-sm-4">DU 885 PRO</div>
					                    </li><!-- /.item -->
					                    <li class="item">
					                      <div class="col-sm-4"><span class="label label-info pull-left" style="font-size:14px;background-color:#008000 !important;">100</span></div>
					                      <div class="col-sm-4"><span class="label label-info pull-left" style="font-size:14px;background-color:#ff0000 !important;">1 </span></div>
					                      <div class="col-sm-4">EI Power 750</div>
					                    </li><!-- /.item -->
					                    <li class="item">
					                      <div class="col-sm-4"><span class="label label-info pull-left" style="font-size:14px;background-color:#008000 !important;">200</span></div>
					                      <div class="col-sm-4"><span class="label label-info pull-left" style="font-size:14px;background-color:#ff0000 !important;">6 </span></div>
					                      <div class="col-sm-4">EI Power 950</div>
					                    </li><!-- /.item -->
					                </ul>    
				                </div><!-- /.box-body -->
				            </div>
						</div>
						<div class="col-sm-4 col-xs-12">
							<div class="box box-primary">
				                <div class="box-header with-border">
				                  <h3 class="box-title">Available Inventory</h3>
				                  <div class="box-tools pull-right">
				                    <button class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
				                    <button class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button>
				                  </div>
				                </div><!-- /.box-header -->
				                <div class="box-body">
				                	<ul class="products-list product-list-in-box">
					                    <li class="item">
					                      <div class="col-sm-4"><strong>Qty</strong></div>
					                      <div class="col-sm-4"><strong>Material Code</strong></div>
					                      <div class="col-sm-4"><strong>Product Name</strong></div>
					                    </li><!-- /.item -->
					                    <li class="item">
					                      <div class="col-sm-4"><span class="label label-info pull-left" style="font-size:14px;">3000</span></div>
					                      <div class="col-sm-4">
					                        <a href="javascript::;" class="product-title">2345676 </a>
					                      </div>
					                      <div class="col-sm-4">DU 875 PRO</div>
					                    </li><!-- /.item -->
					                    <li class="item">
					                      <div class="col-sm-4"><span class="label label-info pull-left" style="font-size:14px;">900</span></div>
					                      <div class="col-sm-4">
					                        <a href="javascript::;" class="product-title">0239844</a>
					                      </div>
					                      <div class="col-sm-4">DU 885 PRO</div>
					                    </li><!-- /.item -->
					                    <li class="item">
					                      <div class="col-sm-4"><span class="label label-info pull-left" style="font-size:14px;">5000</span></div>
					                      <div class="col-sm-4">
					                        <a href="javascript::;" class="product-title">9876543 </a>
					                      </div>
					                      <div class="col-sm-4">EI Power 750</div>
					                    </li><!-- /.item -->
					                    <li class="item">
					                      <div class="col-sm-4"><span class="label label-info pull-left" style="font-size:14px;">2000</span></div>
					                      <div class="col-sm-4">
					                        <a href="javascript::;" class="product-title">28192103</a>
					                      </div>
					                      <div class="col-sm-4">EI Power 950</div>
					                    </li><!-- /.item -->
					                </ul>  
				                </div><!-- /.box-body -->
				            </div>
						</div>	
					</div>
					<div class="row">
						<div class="col-sm-6 col-xs-12">
							<div class="box box-primary">
								<div class="box-header with-border">
				                  <h3 class="box-title">Secondary Sales</h3>
				                  <div class="box-tools pull-right">
				                    <button class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
				                    <button class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button>
				                  </div>
				                </div><!-- /.box-header -->
				                <div class="box-body no-padding">
				                	<div class="chart">
				                		<canvas  id="secondarySales" ></canvas>
				                	</div>
				                </div>
							</div>
						</div>
						<div class="col-sm-6 col-xs-12">
							<div class="box box-primary">
								<div class="box-header with-border">
				                  <h3 class="box-title">Supplier Stock</h3>
				                  <div class="box-tools pull-right">
				                    <button class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
				                    <button class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button>
				                  </div>
				                </div><!-- /.box-header -->
				                <div class="box-body no-padding">
				                	<div class="chart">
				                		<canvas  id="supplierStock" ></canvas>
				                	</div>
				                </div>
							</div>
						</div>
					</div>
					<?PHP /*<div class="row">
						<div class="col-sm-6 col-xs-12">
							
							<div class="box box-primary">
								<div class="box-header with-border">
				                  <h3 class="box-title">Field Sales</h3>
				                  <div class="box-tools pull-right">
				                    <button class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
				                    <button class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button>
				                  </div>
				                </div><!-- /.box-header -->
				                <div class="box-body no-padding">
				                	<div class="col-md-12" style="overflow:hidden; height:400px;">
				                		<div  id="map_field"></div>
				                	</div>
				                </div>
							</div>
							
						</div>
						<div class="col-sm-6 col-xs-12">
							
							<div class="box box-primary">
								<div class="box-header with-border">
				                  <h3 class="box-title">Customer Information</h3>
				                  <div class="box-tools pull-right">
				                    <button class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
				                    <button class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button>
				                  </div>
				                </div><!-- /.box-header -->
				                <div class="box-body no-padding">
				                	<div class="col-md-12" style="overflow:hidden; height:400px;">
				                		<div  id="map_customer"></div>
				                	</div>	
				                </div>
							</div>
							
						</div>
					</div> */ ?>
					<div class="row">
						<div class="col-sm-6 col-xs-12">
							<div class="box box-primary">
								<div class="box-header with-border">
				                  <h3 class="box-title">Return Verification</h3>
				                  <div class="box-tools pull-right">
				                    <button class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
				                    <button class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button>
				                  </div>
				                </div><!-- /.box-header -->
				                <div class="box-body no-padding">
				                	<div class="chart">
				                		<canvas  id="returnVarification" ></canvas>
				                	</div>
				                </div>
							</div>
						</div>
						<div class="col-sm-6 col-xs-12">
							<div class="box box-primary">
								<div class="box-header with-border">
				                  <h3 class="box-title">Warranty Claim & Service</h3>
				                  <div class="box-tools pull-right">
				                    <button class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
				                    <button class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button>
				                  </div>
				                </div><!-- /.box-header -->
				                <div class="box-body no-padding">
				                	<div class="chart">
				                		<canvas  id="warrantyService" ></canvas>
				                	</div>
				                </div>
							</div>
						</div>
					</div>
				</div>		
			</div>
			<div role="tabpanel" class="tab-pane" id="supplier">
				<div class="container" style="width: 100%;padding-left: 0; padding-right: 0;margin-top:5px">
					<div class="col-sm-12 col-xs-12">
	                	<div class="nav-tabs-custom">
	                		<ul class="nav nav-tabs">
	                			<li role="presentation" class="active"><a href="#dispatch" role="tab" data-toggle="tab">Dispatch Report</a></li>
    							<li role="presentation"><a href="#inventoryMonitor" role="tab" data-toggle="tab"> Inventory Report</a></li>
	                		</ul>
	                		<div class="tab-content" id="ParentTab1">
	                			<div role="tabpanel1" class="tab-pane active" id="dispatch">
	                				<div class="box box-primary">
	                					<div class="box-header  with-border">
			                				<div class="row">
			                					<div class="form-group col-sm-4">
									                <label for="exampleInputEmail">Products</label>
									                <div class="input-group ">
									                    
									                    <div id="selectbox">
									                        <select class="list-unstyled selectpicker" data-live-search="true" id="product_group" name="product_group"
									                                parsley-trigger="change" parsley-required="true" parsley-error-container="#selectbox" onchange="changeProducts()">
									                            <option  value="">Select Product</option>
									                            <option  value="">VD 400</option>
									                            <option  value="">V 400</option>
									                            
									                        </select>
									                    </div>
									                </div>
									            </div>
									            <div class="form-group col-sm-4">
									                <label for="exampleInputEmail">Location</label>
									                <div class="input-group ">
									                    
									                    <div id="selectbox">
									                        <select class="list-unstyled selectpicker" data-live-search="true" id="location_type" name="location_type"
									                                parsley-trigger="change" parsley-required="true" parsley-error-container="#selectbox" onchange="changeLocations()">
									                            <option  value="">Select Locations</option>
									                            <option  value="">Gurgaon Hub</option>
									                            <option  value="">vijaywada</option>
									                        </select>
									                    </div>
									                </div>
									            </div>

									            <div class="form-group col-sm-4">
									                <label for="exampleInputEmail">Vendor Name</label>
									                <div class="input-group ">
									                    
									                    <div id="selectbox">
									                        <select class="list-unstyled selectpicker" data-live-search="true" id="location_type" name="location_type"
									                                parsley-trigger="change" parsley-required="true" parsley-error-container="#selectbox" onchange="changeLocations()">
									                            <option  value="">Vendor Locations</option>
									                            <option  value="">KALAKRITI INFOTECH PVT. LTD.</option>
									                            <option  value="">SURYA BATTERIES</option>
									                        </select>
									                    </div>
									                </div>
									            </div>
			                				</div>
			                				<div class="row">

									            <div class="form-group col-sm-4">
									                <label for="exampleInputEmail">From Date</label>
									                <div class="input-group ">
									                   <input type="date" name="from_date" id="from_date">
									                </div>
									            </div>
									            <div class="form-group col-sm-4">
									                <label for="exampleInputEmail">TO Date</label>
									                <div class="input-group ">
									                    <input type="date" name="to_date" id="to_date">
									                </div>
									            </div>	
									            <div class="form-group col-sm-4">
									                <label for="exampleInputEmail"></label>
									                <div class="input-group ">
									                    <div id="button">
									                        <button class="btn btn-primary" data-toggle="modal"  onclick="makeGrid();">Filter</button>
									                        <button type="button" class="btn btn-primary" aria-label="Left Align" id="demo">
									                            Export
									                        </button>
									                    </div>
									                    
									                </div>
									            </div>
									        </div>
									    </div>
									    <div class="box-body">
									    	<div class="row">
									    		<div id="dipatchControlGrid"></div>
									    	</div>	
									    </div>    
								    </div>        
	                			</div>
	                			<div role="tabpanel1" class="tab-pane" id="inventoryMonitor">
	                				<div class="box box-primary">
	                					<div class="box-header  with-border">
	                						<div class="row">
	                							<div class="form-group col-md-3">
									                <label for="exampleInputEmail">Products</label>
									                <div class="input-group ">
									                    
									                    <div id="selectbox">
									                        <select class="list-unstyled selectpicker" data-live-search="true" id="product_group" name="product_group"
									                                parsley-trigger="change" parsley-required="true" parsley-error-container="#selectbox" onchange="changeProducts()">
									                            <option  value="">Select Product</option>
									                            <option  value="">VD 400</option>
									                            <option  value="">V 400</option>
									                            
									                        </select>
									                    </div>
									                </div>
									            </div>
									            <div class="form-group col-md-3">
									                <label for="exampleInputEmail">Location Type</label>
									                <div class="input-group ">
									                    
									                    <div id="selectbox">
									                        <select class="list-unstyled selectpicker" data-live-search="true" id="location_type" name="location_type"
									                                parsley-trigger="change" parsley-required="true" parsley-error-container="#selectbox" onchange="changeLocations()">
									                            <option  value="">Vendor </option>
									                            <option  value="">Depo / Wherehouse</option>
									                            <option  value="">Plant</option>
									                        </select>
									                    </div>
									                </div>
									            </div>
									            <div class="form-group col-md-3">
									                <label for="exampleInputEmail">Location</label>
									                <div class="input-group ">
									                    
									                    <div id="selectbox">
									                        <select class="list-unstyled selectpicker" data-live-search="true" id="location_type" name="location_type"
									                                parsley-trigger="change" parsley-required="true" parsley-error-container="#selectbox" onchange="changeLocations()">
									                            <option  value="">Vendor Locations</option>
									                            <option  value="">KALAKRITI INFOTECH PVT. LTD.</option>
									                            <option  value="">SURYA BATTERIES</option>
									                        </select>
									                    </div>
									                </div>
									            </div>
									            <div class="form-group col-md-3">
									                <label for="exampleInputEmail">Freshness</label>
									                <div class="input-group ">
									                    
									                    <div id="selectbox">
									                        <select class="list-unstyled selectpicker" data-live-search="true" id="location_type" name="location_type"
									                                parsley-trigger="change" parsley-required="true" parsley-error-container="#selectbox" onchange="changeLocations()">
									                            <option  value="">Select</option>
									                            <option  value="">10%</option>
									                            <option  value="">20%</option>
									                            <option  value="">30%</option>
									                            <option  value="">50%</option>
									                            <option  value="">80%</option>
									                            <option  value="">100%</option>
									                        </select>
									                    </div>
									                </div>
									            </div>
	                						</div>
	                						<div class="row">
	                							<div class="form-group col-md-3">
									                <label for="exampleInputEmail">From Date</label>
									                <div class="input-group ">
									                   <input type="date" name="from_date" id="from_date">
									                </div>
									            </div>
									            <div class="form-group col-md-3">
									                <label for="exampleInputEmail">TO Date</label>
									                <div class="input-group ">
									                    <input type="date" name="to_date" id="to_date">
									                </div>
									            </div>
									            <div class="form-group col-md-3">
									                <label for="exampleInputEmail">MRP</label>
									                <div class="input-group ">
									                    <div id="selectbox">
									                        <select class="list-unstyled selectpicker" data-live-search="true" id="location_type" name="location_type"
									                                parsley-trigger="change" parsley-required="true" parsley-error-container="#selectbox" onchange="changeLocations()">
									                            <option  value="">1500</option>
									                            <option  value="">1600</option>
									                            <option  value="">1900</option>
									                            
									                        </select>
									                    </div>
									                </div>
									            </div>
									            <div class="form-group col-sm-3">
									                <label for="exampleInputEmail"></label>
									                <div class="input-group ">

									                    <div id="button">
									                        <button class="btn btn-primary" data-toggle="modal"  onclick="makeGrid();">Filter</button>
									                        <button type="button" class="btn btn-primary" aria-label="Left Align" id="demo">
									                            Export
									                        </button>
									                    </div>
									                    
									                </div>
									            </div>
	                						</div>
	                					</div>
	                					<div class="box-body">
									    	<div class="row">
									    		<div id="inventoryMonitorGrid"></div>
									    	</div>	
									    </div>
	                				</div>	
	                			</div>
	                		</div>
	                	</div>
					</div>
				</div>
			</div>
			<div role="tabpanel" class="tab-pane" id="production">
				<div class="container" style="width: 100%;padding-left: 0; padding-right: 0;margin-top:5px">
					<div class="col-sm-12 col-xs-12">
	                	<div class="nav-tabs-custom">
	                		<ul class="nav nav-tabs">
	                			<li role="presentation" class="active"><a href="#PP" role="tab" data-toggle="tab">Packing Report </a></li>
    							<li role="presentation"><a href="#IM" role="tab" data-toggle="tab"> Dispatch Report</a></li>
	                		</ul>
	                		<div class="tab-content" id="ParentTab2">
	                			<div role="tabpanel2" class="tab-pane active" id="PP">
	                				<div class="box box-primary">
	                					<div class="box-header  with-border">
			                				<div class="row">
			                					<div class="form-group col-md-4">
									                <label for="exampleInputEmail">Products</label>
									                <div class="input-group ">
									                    
									                    <div id="selectbox">
									                        <select class="list-unstyled selectpicker" data-live-search="true" id="product_group" name="product_group"
									                                parsley-trigger="change" parsley-required="true" parsley-error-container="#selectbox" onchange="changeProducts()">
									                            <option  value="">Select Product</option>
									                            <option  value="">DU 875 PRO</option>
									                            <option  value="">EI POWER 750</option>
									                            
									                        </select>
									                    </div>
									                </div>
									            </div>
									            <div class="form-group col-md-4">
									                <label for="exampleInputEmail">Batch no</label>
									                <div class="input-group ">
									                    
									                    <div id="selectbox">
									                        <select class="list-unstyled selectpicker" data-live-search="true" id="location_type" name="location_type"
									                                parsley-trigger="change" parsley-required="true" parsley-error-container="#selectbox" onchange="changeLocations()">
									                            <option  value="">Batch No</option>
									                            <option  value="">052017</option>
									                            <option  value="">042017</option>
									                            <option  value="">032017</option>
									                        </select>
									                    </div>
									                </div>
									            </div>
									            <div class="form-group col-md-4">
									                <label for="exampleInputEmail">Location</label>
									                <div class="input-group ">
									                    
									                    <div id="selectbox">
									                        <select class="list-unstyled selectpicker" data-live-search="true" id="location_type" name="location_type"
									                                parsley-trigger="change" parsley-required="true" parsley-error-container="#selectbox" onchange="changeLocations()">
									                            <option  value="">Locations</option>
									                            <option  value="">Location 1</option>
									                            <option  value="">Location 2</option>
									                        </select>
									                    </div>
									                </div>
									            </div>
			                				</div>
			                				<div class="row">

									            <div class="form-group col-sm-4">
									                <label for="exampleInputEmail">From Date</label>
									                <div class="input-group ">
									                   <input type="date" name="from_date" id="from_date">
									                </div>
									            </div>
									            <div class="form-group col-sm-4">
									                <label for="exampleInputEmail">TO Date</label>
									                <div class="input-group ">
									                    <input type="date" name="to_date" id="to_date">
									                </div>
									            </div>	
									            <div class="form-group col-sm-4">
									                <label for="exampleInputEmail"></label>
									                <div class="input-group ">
									                    <div id="button">
									                        <button class="btn btn-primary" data-toggle="modal"  onclick="makeGrid();">Filter</button>
									                        <button type="button" class="btn btn-primary" aria-label="Left Align" id="demo">
									                            Export
									                        </button>
									                    </div>
									                    
									                </div>
									            </div>
									        </div>
									    </div>
									    <div class="box-body">
									    	<div class="row">
									    		<div id="ppGrid"></div>
									    	</div>	
									    </div>    
								    </div>        
	                			</div>
	                			<div role="tabpanel2" class="tab-pane" id="IM">
	                				<div class="box box-primary">
	                					<div class="box-header  with-border">
	                						<div class="row">
	                							<div class="form-group col-md-4">
									                <label for="exampleInputEmail">Products</label>
									                <div class="input-group ">
									                    
									                    <div id="selectbox">
									                        <select class="list-unstyled selectpicker" data-live-search="true" id="product_group" name="product_group"
									                                parsley-trigger="change" parsley-required="true" parsley-error-container="#selectbox" onchange="changeProducts()">
									                            <option  value="">Select Product</option>
									                            <option  value="">DU 875 PRO</option>
									                            <option  value="">EI POWER 750</option>
									                            
									                        </select>
									                    </div>
									                </div>
									            </div>
									            <div class="form-group col-md-4">
									                <label for="exampleInputEmail">Batch no</label>
									                <div class="input-group ">
									                    
									                    <div id="selectbox">
									                        <select class="list-unstyled selectpicker" data-live-search="true" id="location_type" name="location_type"
									                                parsley-trigger="change" parsley-required="true" parsley-error-container="#selectbox" onchange="changeLocations()">
									                            <option  value="">Batch No</option>
									                            <option  value="">052017</option>
									                            <option  value="">042017</option>
									                            <option  value="">032017</option>
									                        </select>
									                    </div>
									                </div>
									            </div>
									            <div class="form-group col-md-4">
									                <label for="exampleInputEmail">Inspection Location</label>
									                <div class="input-group ">
									                    
									                    <div id="selectbox">
									                        <select class="list-unstyled selectpicker" data-live-search="true" id="location_type" name="location_type"
									                                parsley-trigger="change" parsley-required="true" parsley-error-container="#selectbox" onchange="changeLocations()">
									                            <option  value="">Inspection Locations</option>
									                            <option  value="">Stator Mapping.</option>
									                            <option  value="">Fianl Testing</option>
									                        </select>
									                    </div>
									                </div>
									            </div>
	                						</div>
	                						<div class="row">
	                							<div class="form-group col-md-4">
									                <label for="exampleInputEmail">From Date</label>
									                <div class="input-group ">
									                   <input type="date" name="from_date" id="from_date">
									                </div>
									            </div>
									            <div class="form-group col-md-4">
									                <label for="exampleInputEmail">TO Date</label>
									                <div class="input-group ">
									                    <input type="date" name="to_date" id="to_date">
									                </div>
									            </div>
									            
									            <div class="form-group col-sm-4">
									                <label for="exampleInputEmail"></label>
									                <div class="input-group ">

									                    <div id="button">
									                        <button class="btn btn-primary" data-toggle="modal"  onclick="makeGrid();">Filter</button>
									                        <button type="button" class="btn btn-primary" aria-label="Left Align" id="demo">
									                            Export
									                        </button>
									                    </div>
									                    
									                </div>
									            </div>
	                						</div>
	                					</div>
	                					<div class="box-body">
									    	<div class="row">
									    		<div id="IMGrid"></div>
									    	</div>	
									    </div>
	                				</div>	
	                			</div>
	                		</div>
	                	</div>
					</div>
				</div>
			</div>
			<div role="tabpanel" class="tab-pane" id="warehouse">
				<div class="container" style="width: 100%;padding-left: 0; padding-right: 0;margin-top:5px">
					<div class="col-sm-12 col-xs-12">
	                	<div class="nav-tabs-custom">
	                		<ul class="nav nav-tabs">
	                			<li role="presentation" class="active"><a href="#CIM" role="tab" data-toggle="tab">Inventory Report</a></li>
    							<li role="presentation"><a href="#DA" role="tab" data-toggle="tab"> Dispathc Report</a></li>
    							<li role="presentation"><a href="#GRN" role="tab" data-toggle="tab"> GRN Report</a></li>
    							<li role="presentation"><a href="#Pickup" role="tab" data-toggle="tab"> Pickup Report</a></li>
    							<li role="presentation"><a href="#Putaway" role="tab" data-toggle="tab"> Putaway Report</a></li>
    							<li role="presentation"><a href="#RV" role="tab" data-toggle="tab"> Return Report</a></li>
	                		</ul>
	                		<div class="tab-content" id="ParentTab3">
	                			<div role="tabpanel3" class="tab-pane active" id="CIM">
	                				<div class="box box-primary">
	                					<div class="box-header  with-border">
			                				<div class="row">
			                					<div class="form-group col-md-4">
									                <label for="exampleInputEmail">Products</label>
									                <div class="input-group ">
									                    
									                    <div id="selectbox">
									                        <select class="list-unstyled selectpicker" data-live-search="true" id="product_group" name="product_group"
									                                parsley-trigger="change" parsley-required="true" parsley-error-container="#selectbox" onchange="changeProducts()">
									                            <option  value="">Select Product</option>
									                            <option  value="">DU 875 PRO</option>
									                            <option  value="">EI POWER 750</option>
									                            
									                        </select>
									                    </div>
									                </div>
									            </div>
									            <div class="form-group col-md-4">
									                <label for="exampleInputEmail">Location</label>
									                <div class="input-group ">
									                    
									                    <div id="selectbox">
									                        <select class="list-unstyled selectpicker" data-live-search="true" id="location_type" name="location_type"
									                                parsley-trigger="change" parsley-required="true" parsley-error-container="#selectbox" onchange="changeLocations()">
									                            <option  value="">Depo Locations</option>
									                            <option  value="">Depo Location 1</option>
									                            <option  value="">Depo Location 2</option>
									                        </select>
									                    </div>
									                </div>
									            </div>
									            <div class="form-group col-md-4">
									                <label for="exampleInputEmail">Inventory Age</label>
									                <div class="input-group ">
									                    
									                    <div id="selectbox">
									                        <select class="list-unstyled selectpicker" data-live-search="true" id="location_type" name="location_type"
									                                parsley-trigger="change" parsley-required="true" parsley-error-container="#selectbox" onchange="changeLocations()">
									                            <option  value="">Select</option>
									                            <option  value="">1 Month</option>
									                            <option  value="">3 Month</option>
									                            <option  value="">6 Month</option>
									                            <option  value="">9 Month</option>
									                            <option  value="">1 Year</option>
									                            <option  value="">2 Years</option>
									                            <option  value="">3 Years</option>
									                            <option  value="">3+ Years</option>
									                        </select>
									                    </div>
									                </div>
									            </div>
			                				</div>
			                				<div class="row">

									            <div class="form-group col-sm-4">
									                <label for="exampleInputEmail">From Date</label>
									                <div class="input-group ">
									                   <input type="date" name="from_date" id="from_date">
									                </div>
									            </div>
									            <div class="form-group col-sm-4">
									                <label for="exampleInputEmail">TO Date</label>
									                <div class="input-group ">
									                    <input type="date" name="to_date" id="to_date">
									                </div>
									            </div>	
									            <div class="form-group col-sm-4">
									                <label for="exampleInputEmail"></label>
									                <div class="input-group ">
									                    <div id="button">
									                        <button class="btn btn-primary" data-toggle="modal"  onclick="makeGrid();">Filter</button>
									                        <button type="button" class="btn btn-primary" aria-label="Left Align" id="demo">
									                            Export
									                        </button>
									                    </div>
									                    
									                </div>
									            </div>
									        </div>
									    </div>
									    <div class="box-body">
									    	<div class="row">
									    		<div id="cimGrid"></div>
									    	</div>	
									    </div>    
								    </div>        
	                			</div>
	                			<div role="tabpanel3" class="tab-pane" id="DA">
	                				<div class="box box-primary">
	                					<div class="box-header  with-border">
	                						<div class="row">
	                							<div class="form-group col-md-4">
									                <label for="exampleInputEmail">Products</label>
									                <div class="input-group ">
									                    
									                    <div id="selectbox">
									                        <select class="list-unstyled selectpicker" data-live-search="true" id="product_group" name="product_group"
									                                parsley-trigger="change" parsley-required="true" parsley-error-container="#selectbox" onchange="changeProducts()">
									                            <option  value="">Select Product</option>
									                            <option  value="">DU 875 PRO</option>
									                            <option  value="">EI POWER 750</option>
									                            
									                        </select>
									                    </div>
									                </div>
									            </div>
									        
									            <div class="form-group col-md-4">
									                <label for="exampleInputEmail">Batch no</label>
									                <div class="input-group ">
									                    
									                    <div id="selectbox">
									                        <select class="list-unstyled selectpicker" data-live-search="true" id="location_type" name="location_type"
									                                parsley-trigger="change" parsley-required="true" parsley-error-container="#selectbox" onchange="changeLocations()">
									                            <option  value="">Batch No</option>
									                            <option  value="">052017</option>
									                            <option  value="">042017</option>
									                            <option  value="">032017</option>
									                        </select>
									                    </div>
									                </div>
									            </div>

									            <div class="form-group col-md-4">
									                <label for="exampleInputEmail">Dispatch Location</label>
									                <div class="input-group ">
									                    
									                    <div id="selectbox">
									                        <select class="list-unstyled selectpicker" data-live-search="true" id="location_type" name="location_type"
									                                parsley-trigger="change" parsley-required="true" parsley-error-container="#selectbox" onchange="changeLocations()">
									                            <option  value="">Dispatch Locations</option>
									                            <option  value="">Kasipur Plant</option>
									                            <option  value="">Kasipur Plant2</option>
									                        </select>
									                    </div>
									                </div>
									            </div>
	                						</div>
	                						<div class="row">
	                							<div class="form-group col-md-4">
									                <label for="exampleInputEmail">From Date</label>
									                <div class="input-group ">
									                   <input type="date" name="from_date" id="from_date">
									                </div>
									            </div>
									            <div class="form-group col-md-4">
									                <label for="exampleInputEmail">TO Date</label>
									                <div class="input-group ">
									                    <input type="date" name="to_date" id="to_date">
									                </div>
									            </div>
									            
									            <div class="form-group col-sm-4">
									                <label for="exampleInputEmail"></label>
									                <div class="input-group ">

									                    <div id="button">
									                        <button class="btn btn-primary" data-toggle="modal"  onclick="makeGrid();">Filter</button>
									                        <button type="button" class="btn btn-primary" aria-label="Left Align" id="demo">
									                            Export
									                        </button>
									                    </div>
									                    
									                </div>
									            </div>
	                						</div>
	                					</div>
	                					<div class="box-body">
									    	<div class="row">
									    		<div id="DAGrid"></div>
									    	</div>	
									    </div>
	                				</div>	
	                			</div>
	                			<div role="tabpanel3" class="tab-pane" id="GRN">
	                				<div class="box box-primary">
	                					<div class="box-header  with-border">
	                						<div class="row">
	                							 <div class="form-group col-md-4">
									                <label for="exampleInputEmail">Products</label>
									                <div class="input-group ">
									                    
									                    <div id="selectbox">
									                        <select class="list-unstyled selectpicker" data-live-search="true" id="product_group" name="product_group"
									                                parsley-trigger="change" parsley-required="true" parsley-error-container="#selectbox" onchange="changeProducts()">
									                            <option  value="">Select Product</option>
									                            <option  value="">DU 875 PRO</option>
									                            <option  value="">EI POWER 750</option>
									                            
									                        </select>
									                    </div>
									                </div>
									            </div>
									        

									            <div class="form-group col-md-4">
									                <label for="exampleInputEmail">Batch no</label>
									                <div class="input-group ">
									                    
									                    <div id="selectbox">
									                        <select class="list-unstyled selectpicker" data-live-search="true" id="location_type" name="location_type"
									                                parsley-trigger="change" parsley-required="true" parsley-error-container="#selectbox" onchange="changeLocations()">
									                            <option  value="">Batch No</option>
									                            <option  value="">052017</option>
									                            <option  value="">042017</option>
									                            <option  value="">032017</option>
									                        </select>
									                    </div>
									                </div>
									            </div>

									            <div class="form-group col-md-4">
									                <label for="exampleInputEmail">GRN Location</label>
									                <div class="input-group ">
									                    
									                    <div id="selectbox">
									                        <select class="list-unstyled selectpicker" data-live-search="true" id="location_type" name="location_type"
									                                parsley-trigger="change" parsley-required="true" parsley-error-container="#selectbox" onchange="changeLocations()">
									                            <option  value="">GRN Locations</option>
									                            <option  value="">Gurgaon Hub.</option>
									                            <option  value="">Vijaywada</option>
									                        </select>
									                    </div>
									                </div>
									            </div>
	                						</div>
	                						<div class="row">
	                							<div class="form-group col-md-4">
									                <label for="exampleInputEmail">From Date</label>
									                <div class="input-group ">
									                   <input type="date" name="from_date" id="from_date">
									                </div>
									            </div>
									            <div class="form-group col-md-4">
									                <label for="exampleInputEmail">TO Date</label>
									                <div class="input-group ">
									                    <input type="date" name="to_date" id="to_date">
									                </div>
									            </div>
									            
									            <div class="form-group col-sm-4">
									                <label for="exampleInputEmail"></label>
									                <div class="input-group ">

									                    <div id="button">
									                        <button class="btn btn-primary" data-toggle="modal"  onclick="makeGrid();">Filter</button>
									                        <button type="button" class="btn btn-primary" aria-label="Left Align" id="demo">
									                            Export
									                        </button>
									                    </div>
									                    
									                </div>
									            </div>
	                						</div>
	                					</div>
	                					<div class="box-body">
									    	<div class="row">
									    		<div id="GRNGrid"></div>
									    	</div>	
									    </div>
	                				</div>	
	                			</div>
	                			<div role="tabpanel3" class="tab-pane" id="Pickup">
	                				<div class="box box-primary">
	                					<div class="box-header  with-border">
	                						<div class="row">
	                							<div class="form-group col-md-4">
									                <label for="exampleInputEmail">Products</label>
									                <div class="input-group ">
									                    
									                    <div id="selectbox">
									                        <select class="list-unstyled selectpicker" data-live-search="true" id="product_group" name="product_group"
									                                parsley-trigger="change" parsley-required="true" parsley-error-container="#selectbox" onchange="changeProducts()">
									                            <option  value="">Select Product</option>
									                            <option  value="">DU 875 PRO</option>
									                            <option  value="">EI POWER 750</option>
									                            
									                        </select>
									                    </div>
									                </div>
									            </div>
									        

									            <div class="form-group col-md-4">
									                <label for="exampleInputEmail">Batch no</label>
									                <div class="input-group ">
									                    
									                    <div id="selectbox">
									                        <select class="list-unstyled selectpicker" data-live-search="true" id="location_type" name="location_type"
									                                parsley-trigger="change" parsley-required="true" parsley-error-container="#selectbox" onchange="changeLocations()">
									                            <option  value="">Batch No</option>
									                            <option  value="">052017</option>
									                            <option  value="">042017</option>
									                            <option  value="">032017</option>
									                        </select>
									                    </div>
									                </div>
									            </div>

									            <div class="form-group col-md-4">
									                <label for="exampleInputEmail">Pickup Location</label>
									                <div class="input-group ">
									                    
									                    <div id="selectbox">
									                        <select class="list-unstyled selectpicker" data-live-search="true" id="location_type" name="location_type"
									                                parsley-trigger="change" parsley-required="true" parsley-error-container="#selectbox" onchange="changeLocations()">
									                            <option  value="">Pickup Locations</option>
									                            <option  value="">Pickup Location 1</option>
									                            <option  value="">Pickup Location 2</option>
									                        </select>
									                    </div>
									                </div>
									            </div>
	                						</div>
	                						<div class="row">
	                							<div class="form-group col-md-4">
									                <label for="exampleInputEmail">From Date</label>
									                <div class="input-group ">
									                   <input type="date" name="from_date" id="from_date">
									                </div>
									            </div>
									            <div class="form-group col-md-4">
									                <label for="exampleInputEmail">TO Date</label>
									                <div class="input-group ">
									                    <input type="date" name="to_date" id="to_date">
									                </div>
									            </div>
									            
									            <div class="form-group col-sm-4">
									                <label for="exampleInputEmail"></label>
									                <div class="input-group ">

									                    <div id="button">
									                        <button class="btn btn-primary" data-toggle="modal"  onclick="makeGrid();">Filter</button>
									                        <button type="button" class="btn btn-primary" aria-label="Left Align" id="demo">
									                            Export
									                        </button>
									                    </div>
									                    
									                </div>
									            </div>
	                						</div>
	                					</div>
	                					<div class="box-body">
									    	<div class="row">
									    		<div id="pickupGrid"></div>
									    	</div>	
									    </div>
	                				</div>	
	                			</div>
	                			<div role="tabpanel3" class="tab-pane" id="Putaway">
	                				<div class="box box-primary">
	                					<div class="box-header  with-border">
	                						<div class="row">
	                							<div class="form-group col-md-4">
									                <label for="exampleInputEmail">Products</label>
									                <div class="input-group ">
									                    
									                    <div id="selectbox">
									                        <select class="list-unstyled selectpicker" data-live-search="true" id="product_group" name="product_group"
									                                parsley-trigger="change" parsley-required="true" parsley-error-container="#selectbox" onchange="changeProducts()">
									                            <option  value="">Select Product</option>
									                            <option  value="">DU 875 PRO</option>
									                            <option  value="">EI POWER 750</option>
									                            
									                        </select>
									                    </div>
									                </div>
									            </div>
									        

									            <div class="form-group col-md-4">
									                <label for="exampleInputEmail">Batch no</label>
									                <div class="input-group ">
									                    
									                    <div id="selectbox">
									                        <select class="list-unstyled selectpicker" data-live-search="true" id="location_type" name="location_type"
									                                parsley-trigger="change" parsley-required="true" parsley-error-container="#selectbox" onchange="changeLocations()">
									                            <option  value="">Batch No</option>
									                            <option  value="">052017</option>
									                            <option  value="">042017</option>
									                            <option  value="">032017</option>
									                        </select>
									                    </div>
									                </div>
									            </div>

									            <div class="form-group col-md-4">
									                <label for="exampleInputEmail">Putaway Location</label>
									                <div class="input-group ">
									                    
									                    <div id="selectbox">
									                        <select class="list-unstyled selectpicker" data-live-search="true" id="location_type" name="location_type"
									                                parsley-trigger="change" parsley-required="true" parsley-error-container="#selectbox" onchange="changeLocations()">
									                            <option  value="">Putaway Locations</option>
									                            <option  value="">Putaway Location 1</option>
									                            <option  value="">Putaway Location 2</option>
									                        </select>
									                    </div>
									                </div>
									            </div>
	                						</div>
	                						<div class="row">
	                							<div class="form-group col-md-4">
									                <label for="exampleInputEmail">From Date</label>
									                <div class="input-group ">
									                   <input type="date" name="from_date" id="from_date">
									                </div>
									            </div>
									            <div class="form-group col-md-4">
									                <label for="exampleInputEmail">TO Date</label>
									                <div class="input-group ">
									                    <input type="date" name="to_date" id="to_date">
									                </div>
									            </div>
									            
									            <div class="form-group col-sm-4">
									                <label for="exampleInputEmail"></label>
									                <div class="input-group ">

									                    <div id="button">
									                        <button class="btn btn-primary" data-toggle="modal"  onclick="makeGrid();">Filter</button>
									                        <button type="button" class="btn btn-primary" aria-label="Left Align" id="demo">
									                            Export
									                        </button>
									                    </div>
									                    
									                </div>
									            </div>
	                						</div>
	                					</div>
	                					<div class="box-body">
									    	<div class="row">
									    		<div id="putawayGrid"></div>
									    	</div>	
									    </div>
	                				</div>	
	                			</div>
	                			<div role="tabpanel3" class="tab-pane" id="RV">
	                				<div class="box box-primary">
	                					<div class="box-header  with-border">
	                						<div class="row">
	                							<div class="form-group col-md-4">
									                <label for="exampleInputEmail">Products</label>
									                <div class="input-group ">
									                    
									                    <div id="selectbox">
									                        <select class="list-unstyled selectpicker" data-live-search="true" id="product_group" name="product_group"
									                                parsley-trigger="change" parsley-required="true" parsley-error-container="#selectbox" onchange="changeProducts()">
									                            <option  value="">Select Product</option>
									                            <option  value="">DU 875 PRO</option>
									                            <option  value="">EI POWER 750</option>
									                            
									                        </select>
									                    </div>
									                </div>
									            </div>
									        

									            <div class="form-group col-md-4">
									                <label for="exampleInputEmail">Batch no</label>
									                <div class="input-group ">
									                    
									                    <div id="selectbox">
									                        <select class="list-unstyled selectpicker" data-live-search="true" id="location_type" name="location_type"
									                                parsley-trigger="change" parsley-required="true" parsley-error-container="#selectbox" onchange="changeLocations()">
									                            <option  value="">Batch No</option>
									                            <option  value="">052017</option>
									                            <option  value="">042017</option>
									                            <option  value="">032017</option>
									                        </select>
									                    </div>
									                </div>
									            </div>

									            <div class="form-group col-md-4">
									                <label for="exampleInputEmail">Return Location</label>
									                <div class="input-group ">
									                    
									                    <div id="selectbox">
									                        <select class="list-unstyled selectpicker" data-live-search="true" id="location_type" name="location_type"
									                                parsley-trigger="change" parsley-required="true" parsley-error-container="#selectbox" onchange="changeLocations()">
									                            <option  value="">Return Locations</option>
									                            <option  value="">Return Location 1</option>
									                            <option  value="">Return Location 2</option>
									                        </select>
									                    </div>
									                </div>
									            </div>
	                						</div>
	                						<div class="row">
	                							<div class="form-group col-md-4">
									                <label for="exampleInputEmail">From Date</label>
									                <div class="input-group ">
									                   <input type="date" name="from_date" id="from_date">
									                </div>
									            </div>
									            <div class="form-group col-md-4">
									                <label for="exampleInputEmail">TO Date</label>
									                <div class="input-group ">
									                    <input type="date" name="to_date" id="to_date">
									                </div>
									            </div>
									            
									            <div class="form-group col-sm-4">
									                <label for="exampleInputEmail"></label>
									                <div class="input-group ">

									                    <div id="button">
									                        <button class="btn btn-primary" data-toggle="modal"  onclick="makeGrid();">Filter</button>
									                        <button type="button" class="btn btn-primary" aria-label="Left Align" id="demo">
									                            Export
									                        </button>
									                    </div>
									                    
									                </div>
									            </div>
	                						</div>
	                					</div>
	                					<div class="box-body">
									    	<div class="row">
									    		<div id="rvGrid"></div>
									    	</div>	
									    </div>
	                				</div>	
	                			</div>
	                		</div>
	                	</div>
					</div>
				</div>
			</div>
			<div role="tabpanel" class="tab-pane" id="channel">
				<div class="container" style="width: 100%;padding-left: 0; padding-right: 0;margin-top:5px">
					<div class="col-sm-12 col-xs-12">
						<div class="box box-primary">
	                		<div class="box-header  with-border">
	                			<div class="box-header  with-border">			
	                				<h3 class="box-title">Secondary Sales</h3>
	                			</div>	
	                			<div class="row">
	            					<div class="form-group col-md-4">
	            						 <label for="exampleInputEmail">Products</label>
		            					<div class="input-group ">
	                  						<div id="selectbox">
	                        					<select class="list-unstyled selectpicker" data-live-search="true" id="product_group" name="product_group" parsley-trigger="change" parsley-required="true" parsley-error-container="#selectbox" >
	                           						<option  value="">Select Product</option>
	                           						<option  value="">DU 875 PRO</option>
	                            					<option  value="">EI POWER 750</option>
	                                            </select>
	                    					</div>
	                					</div>
	            					</div>
	            					<div class="form-group col-md-4">
                						<label for="exampleInputEmail">Distributor Location</label>
                						<div class="input-group ">
                   							<div id="selectbox">
                        						<select class="list-unstyled selectpicker" data-live-search="true" id="product_group" name="product_group" parsley-trigger="change" parsley-required="true" parsley-error-container="#selectbox">
                            						<option  value="">Select Distributor</option>
                            						<option  value="">Distributor 1</option>
                            						<option  value="">Distributor 2</option>
                            						<option  value="">Distributor 3</option>
                        						</select>
                    						</div>
                						</div>
            						</div>
            						<div class="form-group col-md-4">
						                <label for="exampleInputEmail">Retailer Location</label>
						                <div class="input-group ">
						                   <div id="selectbox">
						                        <select class="list-unstyled selectpicker" data-live-search="true" id="product_group" name="product_group"
						                                parsley-trigger="change" parsley-required="true" parsley-error-container="#selectbox" onchange="changeProducts()">
						                            <option  value="">Select Retailer</option>
						                            <option  value="">Retailer 1</option>
						                            <option  value="">Retailer 2</option>
						                            <option  value="">Retailer 3</option>
						                            
						                        </select>
						                    </div>
						                </div>
						            </div>
	                			</div>
	                			<div class="row">
	                				<div class="form-group col-sm-4">
						                <label for="exampleInputEmail">From Date</label>
						                <div class="input-group ">
						                   <input type="date" name="from_date" id="from_date">
						                </div>
						            </div>
						            <div class="form-group col-sm-4">
						                <label for="exampleInputEmail">TO Date</label>
						                <div class="input-group ">
						                    <input type="date" name="to_date" id="to_date">
						                </div>
						            </div>	
						            <div class="form-group col-sm-4">
						                <label for="exampleInputEmail"></label>
						                <div class="input-group ">
						                    <div id="button">
						                        <button class="btn btn-primary" data-toggle="modal"  onclick="makeGrid();">Filter</button>
						                        <button type="button" class="btn btn-primary" aria-label="Left Align" id="demo">
						                            Export
						                        </button>
						                    </div>
						                    
						                </div>
						            </div>	
							    </div>	
	                		</div>
	                		<div class="box-body">
								<div class="row">
									<div id="SSGrid"></div>
								</div>	
							</div>
	                	</div>		
					</div>
				</div>		
			</div>
			<!-- <div role="tabpanel" class="tab-pane" id="field">
				<div class="box-header  with-border">			
    				<h3 class="box-title">Under Development</h3>
    			</div>	
			</div>
			<div role="tabpanel" class="tab-pane" id="customer">
				<div class="box-header  with-border">			
    				<h3 class="box-title">Under Development</h3>
    			</div>
			</div> -->
			<div role="tabpanel" class="tab-pane" id="service">
				<div class="box-header  with-border">			
    				<h3 class="box-title">Under Development</h3>
    			</div>
			</div>
		</div>
	</div>
	
@stop
@section('style')
    {{HTML::style('jqwidgets/styles/jqx.base.css')}}
@stop
@section('script')
	{{HTML::script('jqwidgets/jqxcore.js')}}
	{{HTML::script('jqwidgets/jqxdata.js')}}
	{{HTML::script('jqwidgets/jqxbuttons.js')}}
	{{HTML::script('jqwidgets/jqxscrollbar.js')}}
	{{HTML::script('jqwidgets/jqxmenu.js')}}
	{{HTML::script('jqwidgets/jqxgrid.js')}}
	{{HTML::script('jqwidgets/jqxlistbox.js')}}
	{{HTML::script('jqwidgets/jqxdropdownlist.js')}}
	{{HTML::script('jqwidgets/jqxgrid.selection.js')}}
	{{HTML::script('jqwidgets/jqxgrid.pager.js')}}
	{{HTML::script('jqwidgets/jqxgrid.sort.js')}}
	{{HTML::script('jqwidgets/jqxgrid.filter.js')}}
    {{HTML::script('jqwidgets/jqxgrid.storage.js')}}
    {{HTML::script('jqwidgets/jqxcheckbox.js')}}
    {{HTML::script('jqwidgets/jqxgrid.columnsresize.js')}}
    {{HTML::script('jqwidgets/jqxdata.export.js')}}
    {{HTML::script('jqwidgets/jqxgrid.export.js')}}
    {{HTML::script('jqwidgets/jqxtabs.js')}}
    {{HTML::script('jqwidgets/jqxinput.js')}}
	{{HTML::script('js/plugins/chartjs/Chart.min.js')}}
	<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBZPbGy3tGuz0Pq8aCvCeIJ_JX22cgQFFI&region=IN"></script>
	<script>
		$(function(){
	   

			var barChartData01 = {
		        labels: ["DU 875 PRO", "DU 885 PRO", "EI POWER 750", "EI POWER 950"],
		        datasets: [{
		            label: 'April 17',
			        fillColor: "rgba(210, 214, 222, 1)",
			        strokeColor: "rgba(210, 214, 222, 1)",
			        pointColor: "rgba(210, 214, 222, 1)",
		            data: [380,700,1220,800]
		        },{
		        	label: 'May 17',
	                fillColor: "rgba(210, 214, 222, 1)",
			        strokeColor: "rgba(210, 214, 222, 1)",
			        pointColor: "rgba(210, 214, 222, 1)",
	                data: [400,460,940,760]
		        }]
		    };

		    var activeJobChartData01 = {
		        labels: ["DU 875 PRO", "DU 885 PRO", "EI POWER 750", "EI POWER 950"],
		        datasets: [{
		            label: 'Supplier Stock',
			        fillColor: "rgba(210, 214, 222, 1)",
			        strokeColor: "rgba(210, 214, 222, 1)",
			        pointColor: "rgba(210, 214, 222, 1)",
		            data: [380,700,1200,800]
		        },{
		        	label: 'ATP',
	                fillColor: "rgba(210, 214, 222, 1)",
			        strokeColor: "rgba(210, 214, 222, 1)",
			        pointColor: "rgba(210, 214, 222, 1)",
	                data: [1000,1000,1200,900]
		        }]
		    };

		    var barChartData2 = {
	            labels: ["DU 875 PRO", "DU 885 PRO", "EI POWER 750", "EI POWER 950"],
	            datasets: [{
	                label: 'Returns',
	   		        fillColor: "rgba(22, 47, 36, 0.36)",
			        strokeColor: "rgba(22, 47, 36, 0.36)",
			        pointColor: "rgba(22, 47, 36, 0.36)",

	                data: [8,3,6,10]
	            }]

	        };

	        var barChartData3 = {
	            labels: ["DU 875 PRO", "DU 885 PRO", "EI POWER 750", "EI POWER 950"],
	            datasets: [{
	                label: 'Warranty',
	                fillColor: "rgba(25, 90, 60, 0.87)",
			        strokeColor: "rgba(25, 90, 60, 0.87)",
			        pointColor: "rgba(25, 90, 60, 0.87)",
	                data: [8,13,6,10]
	            }, {
	                
	                label: 'Service',
	                fillColor: "rgba(25, 90, 60, 0.87)",
			        strokeColor: "rgba(25, 90, 60, 0.87)",
			        pointColor: "rgba(25, 90, 60, 0.87)",
	                data: [12,10,10,19]
	            }]

	        };

		    var barChartCanvas = $("#secondarySales").get(0).getContext("2d");
			var barChart = new Chart(barChartCanvas);
			var barChartData = barChartData01;
			//barChart.type = 'horizontalBar';
			barChartData.datasets[0].fillColor = "#00a65a";
			barChartData.datasets[0].strokeColor = "#00a65a";
			barChartData.datasets[0].pointColor = "#00a65a";
			var barChartOptions = {
		      scaleBeginAtZero: true,

		      scaleShowGridLines: false,
		      //String - Colour of the grid lines
		      scaleGridLineColor: "rgba(0,0,0,.05)",
		      //Number - Width of the grid lines
		      //Boolean - Whether to fill the dataset with a color
		      datasetFill: true,
		      
		      //Boolean - whether 	to make the chart responsive to window resizing
		      responsive: true
		    }
				barChartOptions.datasetFill = false;
				
			barChart.Bar(barChartData, barChartOptions);

			var activeJobCanvas = $("#supplierStock").get(0).getContext("2d");
			var activeJobsCharts = new Chart(activeJobCanvas);
			var activeJobChartData = activeJobChartData01;;
			activeJobChartData.datasets[0].fillColor = "#f56954";
			activeJobChartData.datasets[0].strokeColor = "#f56954";
			activeJobChartData.datasets[0].pointColor = "#00a65a";
			var activeJobOptions = { 
			  scaleBeginAtZero: true,

		      scaleShowGridLines: false,
		      //String - Colour of the grid lines
		      scaleGridLineColor: "rgba(0,0,0,.05)",
		      //Number - Width of the grid lines
		      //Boolean - Whether to fill the dataset with a color
		      datasetFill: true,
		      
		      //Boolean - whether 	to make the chart responsive to window resizing
		      responsive: true
			};
			activeJobOptions.datasetFill = false;
			activeJobsCharts.Bar(activeJobChartData, activeJobOptions);

			var returnCanvas = $("#returnVarification").get(0).getContext("2d");
			var returnCharts = new Chart(returnCanvas);
			var returnChartData = barChartData2;;
			returnChartData.datasets[0].fillColor = "#57c396";
			returnChartData.datasets[0].strokeColor = "#57c396";
			returnChartData.datasets[0].pointColor = "#04242b";
			var returnOptions = { 
			  scaleBeginAtZero: true,

		      scaleShowGridLines: false,
		      //String - Colour of the grid lines
		      scaleGridLineColor: "rgba(0,0,0,.05)",
		      //Number - Width of the grid lines
		      //Boolean - Whether to fill the dataset with a color
		      datasetFill: true,
		      
		      //Boolean - whether 	to make the chart responsive to window resizing
		      responsive: true
			};
			returnOptions.datasetFill = false;
			returnCharts.Bar(returnChartData, returnOptions);

			var warrantyCanvas = $("#warrantyService").get(0).getContext("2d");
			var warrantyCharts = new Chart(warrantyCanvas);
			var warrantyChartData = barChartData3;;
			warrantyChartData.datasets[0].fillColor = "#f56954";
			warrantyChartData.datasets[0].strokeColor = "#f56954";
			warrantyChartData.datasets[0].pointColor = "#00a65a";
			var warrantyOptions = { 
			  scaleBeginAtZero: true,

		      scaleShowGridLines: false,
		      //String - Colour of the grid lines
		      scaleGridLineColor: "rgba(0,0,0,.05)",
		      //Number - Width of the grid lines
		      //Boolean - Whether to fill the dataset with a color
		      datasetFill: true,
		      
		      //Boolean - whether 	to make the chart responsive to window resizing
		      responsive: true
			};
			warrantyOptions.datasetFill = false;
			warrantyCharts.Bar(warrantyChartData, warrantyOptions);   


			var DmyLatlng = new google.maps.LatLng(17.408794, 78.543990);
	        var DmapOptions = {
	          zoom: 13,
	          center: DmyLatlng,
	          scrollwheel: false,
	        }
	        var Dmap = new google.maps.Map(document.getElementById("map_field"), DmapOptions);

	        var Dmarker = new google.maps.Marker({
	            position: DmyLatlng,
	            title:"Hello World!"
	        });

	        // To add the marker to the map, call setMap();
	        Dmarker.setMap(Dmap);
	        
	        var custLatlng = new google.maps.LatLng(17.408794, 78.543990);
	        var custMapOption = {zoom:3,center:custLatlng,scrollwheel:false}    
	        var custMap = new google.maps.Map(document.getElementById("map_customer"),custMapOption);
	        var locations = [
	          [33.890542, 151.274856, 4],
	          [33.923036, 151.259052, 5],
	          [34.028249, 151.157507, 3],
	          [33.80010128657071, 151.28747820854187],
	          [33.950198, 151.259302, 1]
	        ];
	        
	        for (i = 0; i < locations.length; i++) { 
	            custmarker = new google.maps.Marker({
	            position: new google.maps.LatLng(locations[i][0], locations[i][1]),
	            
	          });

	          custmarker.setMap(custMap);
	        }
		});
		//Dispatch Control Grid
		$(document).ready(function () {
            // prepare the data
            var source =
            {
                datatype: "json",
                
                month: 'Month',
                localdata: {{$dispatchControlJson}},
                 pager: function (pagenum, pagesize, oldpagenum) {
                    // callback called when a page or page size is changed.
                }
            };
            var dataAdapter = new $.jqx.dataAdapter(source);

            $("#dipatchControlGrid").jqxGrid(
            {
                width: '100%',
                source: dataAdapter,                
                pageable: true,
                autoheight: true,
                sortable: true,
                //altrows: true,
                
                editable: false,
                filterable: true,
                showfilterrow: true,
                selectionmode: 'multiplecellsadvanced',
                columns: [
                  { text: 'Month', datafield: 'Month', width: 50 },
                  { text: 'Supplier Name', datafield: 'Supplier_Name', width: 100 },
                  { text: 'Product Name', datafield: 'Product_Name', width: 100 },
                  { text: 'Material Code', datafield: 'Material_Code', width: 100 },
                  { text: 'Batch No', datafield: 'Betch_no', minwidth: 50 },
                  { text: 'Number', columngroup: 'PODetails', datafield: 'Sales_PO_No', minwidth: 100 },
                  { text: 'Date', columngroup: 'PODetails', datafield: 'Sales_PO_Creation_Date', minwidth: 100 },
                  { text: 'Location', columngroup: 'PODetails', datafield: 'Sales_PO_Creation_Location', minwidth: 100 },
                  { text: 'Qty', columngroup: 'PODetails', datafield: 'Sales_PO_Qty', minwidth: 100 },
                  { text: 'Dispatch QTY', datafield: 'Dispatch_Qty', minwidth: 50 },
                  { text: 'Dispatch Date', datafield: 'Dispatch_Date', minwidth: 100 },
                  { text: 'Differance', datafield: 'Differance', minwidth: 50 },
                  { text: 'Promise Date', datafield: 'Expected_Delivery_Date', minwidth: 100 },
                  { text: 'Current Status', datafield: 'Current_Status', minwidth: 100 },
                  { text: 'MRP', datafield: 'MRP', minwidth: 100 },
                  { text: 'Total Value', datafield: 'total_value', minwidth: 100 },
                  { text: 'TP No.', datafield: 'TP', minwidth: 100 }
              ],
                columngroups: 
                [
                  { text: 'Purchase Order Details', align: 'center', name: 'PODetails' }
                ]
            });
        });
		//Inventory Monitor Grid
		$(document).ready(function () {
            // prepare the data
            var source =
            {
                datatype: "json",
                month: 'Month',
                localdata: {{$inventoryMonitorJson}}
            };
            var dataAdapter = new $.jqx.dataAdapter(source);

            $("#inventoryMonitorGrid").jqxGrid(
            {
                
                width: '100%',
                source: dataAdapter,                
                pageable: true,
                autoheight: true,
                sortable: true,
                //altrows: true,
                
                editable: false,
                filterable: true,
                showfilterrow: true,
                selectionmode: 'multiplecellsadvanced',
                columnsresize: true,
                columns: [
                  { text: 'Month', datafield: 'Month', width: 50 },
                  { text: 'Supplier Name', datafield: 'Supplier_Name', width: 200 },
                  { text: 'Product Name', datafield: 'Product_Name', width: 150 },
                  { text: 'Material Code', datafield: 'Material_Code', width:100 },
                  { text: 'ATP', datafield: 'ATP', minwidth: 70 },
                  { text: 'Actual', datafield: 'Actaual', minwidth: 100 },
                  { text: 'Differance', datafield: 'Differance', minwidth: 100 },
                  { text: 'MFG Date', datafield: 'Manufacturer_Date', minwidth: 100 },
                  { text: 'Ege', datafield: 'Ege', minwidth: 100 },
                  { text: 'MRP', datafield: 'MRP', minwidth: 100 },
                  { text: 'Total value', datafield: 'total_value', minwidth: 100 },
              ]
            });
        });
		//Prodiction performace grid
		$(document).ready(function () {
            var url = "getperformanceReprot";

            // prepare the data
            var source =
            {
                datatype: "json",
                month: 'Date',
                localdata: {{$performaceJson}}
            };
            var dataAdapter = new $.jqx.dataAdapter(source);

            $("#ppGrid").jqxGrid(
            {
                width: '100%',
                source: dataAdapter,                
                pageable: true,
                autoheight: true,
                sortable: true,
                //altrows: true,
                
                editable: false,
                filterable: true,
                showfilterrow: true,
                selectionmode: 'multiplecellsadvanced',
                columnsresize: true,
                columns: [
                  { text: 'Date', datafield: 'Date' },
                  { text: 'Location', datafield: 'location'},
                  { text: 'Product Name', datafield: 'Product_Name'},
                  { text: 'Material Code', datafield: 'Material_Code'},
                  { text: 'Batch No', datafield: 'Batch_no'},
                  { text: 'Production Order No', datafield: 'PO_No' },
                  { text: 'Shift No', datafield: 'shift_no' },
                  { text: 'Machine No', datafield: 'Machine_no' },
                  { text: 'Line Number', datafield: 'line' },
                  { text: 'Shift Incharge', datafield: 'Shift_Incharge'},
                  { text: 'Quantity', datafield: 'Qty'},
                  { text: 'Sync Date', datafield: 'Sync_Date'},
                  { text: 'Sync Time', datafield: 'Sync_time'} 
              ]
            });
        });
		//Inventory Monitor Grid
		$(document).ready(function () {

            // prepare the data
            var source =
            {
                datatype: "json",
                month: 'Date',
                localdata: {{$inspectionControlJson}}
            };
            var dataAdapter = new $.jqx.dataAdapter(source);

            $("#IMGrid").jqxGrid(
            {
                width: '100%',
                source: dataAdapter,                
                pageable: true,
                autoheight: true,
                sortable: true,
                //altrows: true,
                
                editable: false,
                filterable: true,
                showfilterrow: true,
                selectionmode: 'multiplecellsadvanced',
                columnsresize: true,
                columns: [
                  { text: 'Date', datafield: 'Date', width: 80 },
                  { text: 'Location', datafield: 'Inspection_location', width: 80 },
                  { text: 'Product Name', datafield: 'Product_Name', width: 150 },
                  { text: 'Material Code', datafield: 'Material_Code', width:100 },
                  { text: 'Batch No', datafield: 'Batch_no', minwidth: 100 },
                  { text: 'Production Order No', datafield: 'PO_No', minwidth: 50 },
                  { text: 'Shift No', datafield: 'shift_no', minwidth: 50 },
                  { text: 'Line Number', datafield: 'line', minwidth: 50 },
                  { text: 'Stage', datafield: 'Stage', minwidth: 100 },
                  { text: 'Inspector Name', datafield: 'Inspector', minwidth: 100 },
                  { text: 'Pass', datafield: 'Pass', minwidth: 100 },
                  { text: 'Reject', datafield: 'Reject', minwidth: 100 } 
              ]
            });
        });
		//Company Inventory Grid cimGrid
		$(document).ready(function () {
            var url = "getinventoryAutomation";

            // prepare the data
            var source =
            {
                datatype: "json",
                month: 'Date',
                localdata: {{$inventoryAccountingJson}}
            };
            var dataAdapter = new $.jqx.dataAdapter(source);

            $("#cimGrid").jqxGrid(
            {
                width: '100%',
                source: dataAdapter,                
                pageable: true,
                autoheight: true,
                sortable: true,
                //altrows: true,
                
                editable: false,
                filterable: true,
                showfilterrow: true,
                selectionmode: 'multiplecellsadvanced',
                columnsresize: true,
                columns: [
                  { text: 'Location Code', datafield: 'Location_Code', width: 100 },
                  { text: 'Location', datafield: 'Location', width: 100 },
                  { text: 'Product Name', datafield: 'Product_Name', width: 150 },
                  { text: 'Material Code', datafield: 'Material_Code', width:100 },
                  { text: 'Manufacturer/ GRN Date', datafield: 'Manufacturer_Date', width:100 },
                  { text: 'Qty', datafield: 'Qty', minwidth: 60 },
                  { text: 'eSeal_Qty', datafield: 'eSeal_Qty', minwidth: 70 },
                  { text: 'Differance', datafield: 'Differance', minwidth: 70 },
                  { text: 'eSeal Location', datafield: 'eSeal_Location', minwidth: 100 },
                  { text: 'MRP', datafield: 'MRP', minwidth: 80 },
                  { text: 'Total_Value', datafield: 'Total_Value', minwidth: 80 }  
              ]
            });
        });
		//Dispathc Automation Grid 
		$(document).ready(function () {
            // prepare the data
            var source =
            {
                datatype: "json",
                
                month: 'Date',
                localdata: {{$dispatchAutomationJson}}
            };
            var dataAdapter = new $.jqx.dataAdapter(source);

            $("#DAGrid").jqxGrid(
            {
                width: '100%',
                source: dataAdapter,                
                pageable: true,
                autoheight: true,
                sortable: true,
                //altrows: true,
                
                editable: false,
                filterable: true,
                showfilterrow: true,
                selectionmode: 'multiplecellsadvanced',
                columnsresize: true,
                columns: [
                  { text: 'Date', datafield: 'Date', width: 100 },
                  { text: 'Sales Order No', datafield: 'STN', width: 100 },
                  { text: 'Product Name', datafield: 'Product_Name', width: 150 },
                  { text: 'Material Code', datafield: 'Material_Code', width:100 },
                  { text: 'Batch No', datafield: 'Batch_no', minwidth: 100 },
                  { text: 'Quantity', datafield: 'Qty', width: 100 },
                  
                  { text: 'Dispatch Location', datafield: 'Dispatch_Location', minwidth: 100 },
                  { text: 'Status', datafield: 'Status', minwidth: 100 } 
              ]
            });
        });
		// GRN Automation GRid
		$(document).ready(function () {
            // prepare the data
            var source =
            {
                datatype: "json",
                month: 'Date',
                localdata: {{$grnAutomationJson}}
            };
            var dataAdapter = new $.jqx.dataAdapter(source);

            $("#GRNGrid").jqxGrid(
            {
                width: '100%',
                source: dataAdapter,                
                pageable: true,
                autoheight: true,
                sortable: true,
                //altrows: true,
                
                editable: false,
                filterable: true,
                showfilterrow: true,
                selectionmode: 'multiplecellsadvanced',
                columnsresize: true,
                columns: [
                  { text: 'Date', datafield: 'Date', width: 100 },
                  { text: 'GRN No', datafield: 'GRN_NO', width: 100 },
                  { text: 'Product Name', datafield: 'Product_Name', width: 150 },
                  { text: 'Material Code', datafield: 'Material_Code', width:100 },
                  { text: 'Batch No', datafield: 'Batch_no', minwidth: 100 },
                  { text: 'GRN Location', datafield: 'grn_location', width: 100 },
                  { text: 'Dispatch Date', datafield: 'Dispatch_Date', minwidth: 100 },
                  { text: 'Dispatch Location', datafield: 'Dispatch_Location', minwidth: 100 },
                  { text: 'Status', datafield: 'Status', minwidth: 100 },
                  { text: 'Stock Age', datafield: 'Stock_Age', minwidth: 100 }  
              ]
            });
        });
		//Pickup Automation Grid
		$(document).ready(function () {
            
            // prepare the data
            var source =
            {
                datatype: "json",
                month: 'Date',
                localdata: {{$pickupautomationJson}}
            };
            var dataAdapter = new $.jqx.dataAdapter(source);

            $("#pickupGrid").jqxGrid(
            {
                width: '100%',
                source: dataAdapter,                
                pageable: true,
                autoheight: true,
                sortable: true,
                //altrows: true,
                
                editable: false,
                filterable: true,
                showfilterrow: true,
                selectionmode: 'multiplecellsadvanced',
                columnsresize: true,
                columns: [
                  { text: 'Date', datafield: 'Date', width: 100 },
                  { text: 'Sales Order No', datafield: 'STN', width: 100 },
                  { text: 'Product Name', datafield: 'Product_Name', width: 150 },
                  { text: 'Material Code', datafield: 'Material_Code', width:100 },
                  { text: 'Batch No', datafield: 'Batch_no', minwidth: 100 },
                  { text: 'Pickup Qty', datafield: 'Qty', width: 100 },
                  { text: 'Order Qty', datafield: 'Order_Qty', minwidth: 100 },
                  { text: 'Differance', datafield: 'Differance', minwidth: 100 },
                  { text: 'Location', datafield: 'Location', minwidth: 100 },
                  { text: 'Time', datafield: 'Time', minwidth: 100 } 
              ]
            });
        });
		//Putaway Automation Grid
		$(document).ready(function () {

            // prepare the data
            var source =
            {
                datatype: "json",
                month: 'Date',
                localdata: {{$putawayJson}}
            };
            var dataAdapter = new $.jqx.dataAdapter(source);

            $("#putawayGrid").jqxGrid(
            {
                width: '100%',
                source: dataAdapter,                
                pageable: true,
                autoheight: true,
                sortable: true,
                //altrows: true,
                
                editable: false,
                filterable: true,
                showfilterrow: true,
                selectionmode: 'multiplecellsadvanced',
                columnsresize: true,
                columns: [
                  { text: 'Date', datafield: 'Date', width: 100 },
                  { text: 'Sales Order No', datafield: 'STN', width: 100 },
                  { text: 'Product Name', datafield: 'Product_Name', width: 150 },
                  { text: 'Material Code', datafield: 'Material_Code', width:100 },
                  { text: 'Batch No', datafield: 'Batch_no', minwidth: 100 },
                  { text: 'Putaway Qty', datafield: 'Qty', width: 100 },
                  { text: 'Qty', datafield: 'Order_Qty', minwidth: 100 },
                  { text: 'Differance', datafield: 'Differance', minwidth: 100 },
                  { text: 'Location', datafield: 'Location', minwidth: 100 },
                  { text: 'Time', datafield: 'Time', minwidth: 100 } 
              ]
            });
        });
		//REturn Varification Grid
		$(document).ready(function () {
            var url = "getreturnAutomation";

            // prepare the data
            var source =
            {
                datatype: "json",
                month: 'Date',
                localdata: {{$returnVerificationJson}}
            };
            var dataAdapter = new $.jqx.dataAdapter(source);

            $("#rvGrid").jqxGrid(
            {
                width: '100%',
                source: dataAdapter,                
                pageable: true,
                autoheight: true,
                sortable: true,
                //altrows: true,
                
                editable: false,
                filterable: true,
                showfilterrow: true,
                selectionmode: 'multiplecellsadvanced',
                columnsresize: true,
                columns: [
                  { text: 'Date', datafield: 'Date', width: 100 },
                  { text: 'Product Name', datafield: 'Product_Name', width: 150 },
                  { text: 'Material Code', datafield: 'Material_Code', width:100 },
                  { text: 'Batch no', datafield: 'Batch_no', width:100 },
                  { text: 'Invoice Date', datafield: 'Invoice_Date', width:100 },
                  { text: 'Return Qty', datafield: 'Return_Qty', width: 100 },
                  { text: 'Return Location', datafield: 'Return_Location', minwidth: 100 },
                  { text: 'Reason', datafield: 'Reason', minwidth: 100 },
                  { text: 'Status', datafield: 'Status', minwidth: 100 }
                  
              ]
            });
        });
		//Secondary Sales Grid
		$(document).ready(function () {
            
            // prepare the data
            var source =
            {
                datatype: "json",
                
                month: 'Date',
                localdata: {{$secondarySale}}
            };
            var dataAdapter = new $.jqx.dataAdapter(source);

            $("#SSGrid").jqxGrid(
            {
                width: '100%',
                source: dataAdapter,                
                pageable: true,
                autoheight: true,
                sortable: true,
                //altrows: true,
                
                editable: false,
                filterable: true,
                showfilterrow: true,
                selectionmode: 'multiplecellsadvanced',
                columnsresize: true,
                columns: [
                  { text: 'Date', datafield: 'Date', width: 100 },
                  { text: 'Product Name', datafield: 'Product_Name', width: 150 },
                  { text: 'Material Code', datafield: 'Material_Code', width:100 },
                  { text: 'Distributor Available Stock', datafield: 'Distributor_Available_Stock', width:100 },
                  { text: 'Distributor Location', datafield: 'Distributor_Location', width: 100 },
                  { text: 'Distributor Sale Date', datafield: 'Distributor_Sale_Date', minwidth: 100 },
                  { text: 'Distributor Sale Qty', datafield: 'Distributor_Sale_Qty', minwidth: 100 },
                  { text: 'Retailer Stock', datafield: 'Retailer_Stock', minwidth: 100 },
                  { text: 'Retailer Location', datafield: 'Retailer_Location', minwidth: 100 },
                  { text: 'GRN Date', datafield: 'GRN_Date', minwidth: 100 },
                  { text: 'Retailer Sale Qty', datafield: 'Retailer_Sale_Qty', minwidth: 100 }
                  
              ]
            });
        });
	</script>	
@stop
@extends('layouts.footer')
