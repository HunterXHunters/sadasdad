@extends('layouts.default')

@extends('layouts.header')
<style>
.jqx-popover{width:180px;}
.jqxcellCustom { text-decoration: underline; cursor: pointer;}  
    
</style>
@extends('layouts.sideview')

@section('content')
<div align="center" id="loading" style="text-align:center; z-index:9999;position:absolute;background:rgba(0,0,0,0.3);height:709px; width:1261px; display:none;" ><img src="/img/loading.gif">    </div>
<div id="popOverView"><div id="popover"></div></div>
<div class="box">
	<div class="box-header with-border">
        @if($supplier=='supplier')
            <h3 class="box-title">Live Supplier - Dispatch</h3>
        @elseif($supplier=='channel')
            <h3 class="box-title">Live Channel - Primary Sales</h3>
        @else
            <h3 class="box-title">Live Production - Dispatch</h3>
        @endif    
    </div><!-- /.box-header -->
    <div class="box-body">
        
    	<div class="box">
	    	<div class="box-header">
		      <h3 class="box-title">Search</h3>
		    </div><!-- /.box-header -->
		    <div class="box-body">
                <form id="SearchFrm" name="SearchFrm" action="exportProduction" method="post" >
    		    	<div class="row">
    					<div class="form-group col-md-4">
    						<input type="text" name="material_code" id="material_code" class="form-control" placeholder="Material Code">							
    					</div>
                        <div class="form-group col-md-4">
                            <input type="text" name="batch_no" id="batch_no" class="form-control" placeholder="Batch Number">                            
                        </div>
    					<div class="form-group col-md-4">
    						<div id="selectbox">
    							<select class="form-control select2" name="location_id" id="location_id" parsley-trigger="change" parsley-required="true" parsley-error-container="#selectbox">
    								<option value="">Select Location</option>
    								@foreach($locations as $location) 
    									<option value="{{$location->location_id}}">{{$location->location_name}}</option>
    								@endforeach
    							</select>
    						</div>
    					</div>
    				</div>
    		    	<div class="row">
    					<div class="form-group  col-md-4">
    						<input type="text" name="from_date" id="from_date" class="form-control" placeholder="From Date">
    					</div>
    					<div class="form-group col-md-4">
    						<input type="text" class="form-control" placeholder="To Date" name="to_date" id="to_date">
    					</div>
    					<div class="form-group col-md-4" align="right">
    						<button type="button" class="btn btn-primary" onclick="getResult();">Search</button>
                            <button type="button" class="btn btn-default">Cancel</button>
                            <!-- <button type="button" class="btn btn-green" onclick="SaveExport();">Export</button>  -->                   
    					</div>
    				</div>
                    @if($supplier=='supplier')
                        <input type="hidden" name="supplier" id="supplier" value="yes">
                    @elseif($supplier=='channel')
                        <input type="hidden" name="supplier" id="supplier" value="channel">                        
                    @else
                         <input type="hidden" name="supplier" id="supplier" value="">
                    @endif        
                </form>    
		    </div>
    	</div>
        
    	<div class="box">
		    <div class="box-body" id="gridDiv">
		    	<div id="inventoryGrid"></div>
		    </div>
    	</div>
    </div>
</div>    
@stop
@section('style')
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.4.1/css/bootstrap-datepicker3.css"/>
    {{HTML::style('jqwidgets/styles/jqx.base.css')}}
@stop
@section('script')
	<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.4.1/js/bootstrap-datepicker.min.js"></script>
	
	{{HTML::script('jqwidgets/jqxcore.js')}}
    {{HTML::script('jqwidgets/jqxbuttons.js')}}
    {{HTML::script('jqwidgets/jqxscrollbar.js')}}
    {{HTML::script('jqwidgets/jqxmenu.js')}}
    {{HTML::script('jqwidgets/jqxgrid.js')}}
    {{HTML::script('jqwidgets/jqxgrid.selection.js')}}
    {{HTML::script('jqwidgets/jqxgrid.columnsresize.js')}}
    {{HTML::script('jqwidgets/jqxdata.js')}}
    {{HTML::script('jqwidgets/jqxgrid.edit.js')}}
    {{HTML::script('jqwidgets/jqxlistbox.js')}}
    {{HTML::script('jqwidgets/jqxdropdownlist.js')}}
    {{HTML::script('jqwidgets/jqxgrid.pager.js')}}
    {{HTML::script('jqwidgets/jqxgrid.sort.js')}}
    {{HTML::script('jqwidgets/jqxgrid.filter.js')}}
    {{HTML::script('jqwidgets/jqxgrid.storage.js')}}
    {{HTML::script('jqwidgets/jqxgrid.columnsreorder.js')}}
    {{HTML::script('jqwidgets/jqxpanel.js')}}
    {{HTML::script('jqwidgets/jqxcheckbox.js')}}
    {{HTML::script('jqwidgets/jqxinput.js')}}
    {{HTML::script('jqwidgets/jqxpopover.js')}}
    {{HTML::script('jqwidgets/jqxdata.export.js')}}
    {{HTML::script('jqwidgets/jqxgrid.export.js')}}
	<script type="text/javascript">
		$(function (){
    		$("#from_date").datepicker({format:'M dd, yyyy',todayHighlight:true,autoclose: true})
    		$("#to_date").datepicker({format:'M dd, yyyy',todayHighlight:true,autoclose: true})

            $("#material_code").jqxInput({placeHolder: "Material Code",height:34,width:'100%' ,source: {{$materialCode}} });
            $("#batch_no").jqxInput({placeHolder: "Batch No",height:34,width:'100%',  source: {{$batchNo}} });
           

    	});	
    	$(document).ready(function () 
        {           
            
            var pId;
            var source =
            {
                id: 'material_code',
                datafields: [
                    { name: 'date', type: 'date' },
                    { name: 'document_no', type: 'string' },
                    { name: 'product_name', type: 'string' },
                    { name: 'material_code', type: 'number' },
                    { name: 'batch_no', type: 'number' },
                    { name: 'po_number', type: 'string' },
                    { name: 'src_loc_name', type: 'string' },
                    { name: 'dest_loc_name', type: 'string' },
                    { name: 'quantity', type: 'string' },
                    { name: 'tp_id', type: 'string' },
                    { name: 'vehicle_no', type: 'string' },
                   
                ],
                datatype: "json",
                localdata:{{$results}},
                pagesize:50,
                pager: function (pagenum, pagesize, oldpagenum) {
                    // callback called when a page or page size is changed.
                }
            };
            var cellclass = function (row, columnfield, value) { 
               
                    return 'jqxcellCustom rowIndex'+row;
                
            }
            var dataAdapter = new $.jqx.dataAdapter(source);
            $("#inventoryGrid").jqxGrid(
            {
                width: '100%',
				source: dataAdapter,                
                pageable: true,
                autoheight: true,
                sortable: true,
                //altrows: true,
                pagesizeoptions: ['50', '80', '100'],
                editable: false,
                filterable: true,
                showfilterrow: true,
                selectionmode: 'multiplecellsadvanced',
                columns: [
                  { text: 'Date',  datafield: 'date', width: '8%',cellsformat:'MMM d,yyyy' },
                  { text: 'Delivery No',  datafield: 'document_no', width: '8%' },  
                  { text: 'Product Name',  datafield: 'product_name', width: '15%' },
                  { text: 'Material Code',  datafield: 'material_code', width: '8%' },
                  { text: 'Batch No.',  datafield: 'batch_no', width: '8%' },
                  { text: 'Production Order No.',  datafield: 'po_number', width: '9%' },
                  { text: 'Source Location Name',  datafield: 'src_loc_name', width: '13%' },
                  { text: 'Destination Location Name',  datafield: 'dest_loc_name', width: '13%' },
                  { text: 'Quantity',  datafield: 'quantity', width: '6%' },
                  { text: 'TP Id',  datafield: 'tp_id', width: '12%', cellclassname: cellclass },
                  
                ],
               
            });
            $("#inventoryGrid").on('cellclick', function (event) {
                console.log(event);
                
                var pName = event.args.rowindex;
                
                if(event.args.row.bounddata.vehicle_no!='null'){
                    $("#popover").html(event.args.row.bounddata.vehicle_no);
                    $("#popOverView").jqxPopover({theme: 'arctic', arrowOffsetValue: 20,title: "Vehicle Number", showCloseButton: true, isModal: false,showArrow: true,rtl: false, selector: $(".rowIndex"+pName) } );
                    $("#popOverView").jqxPopover('render');
                    
                }
            });
        });
        function getResult()
        {
            $("#loading").show();
            var url = '/reports/searchDispatch'
            $.post(url,$( "#SearchFrm" ).serialize(),function(response){
                $("#inventoryGrid").remove();
                var div = '<div id="inventoryGrid"></div>';
                $("#gridDiv").html(div);

                var source =
                {
                    id: 'material_code',
                    datafields: [
                        { name: 'date', type: 'date' },
                        { name: 'document_no', type: 'string' },
                        { name: 'product_name', type: 'string' },
                        { name: 'material_code', type: 'number' },
                        { name: 'batch_no', type: 'number' },
                        { name: 'po_number', type: 'string' },
                        { name: 'src_loc_name', type: 'string' },
                        { name: 'dest_loc_name', type: 'string' },
                        { name: 'quantity', type: 'string' },
                        { name: 'tp_id', type: 'string' },
                        { name: 'vehicle_no', type: 'string' },
                       
                    ],
                    datatype: "json",
                    localdata:response,
                    pagesize:50,
                    pager: function (pagenum, pagesize, oldpagenum) {
                        // callback called when a page or page size is changed.
                    }
                };
                var cellclass = function (row, columnfield, value) { 
               
                    return 'jqxcellCustom rowIndex'+row;
                
            }
            var dataAdapter = new $.jqx.dataAdapter(source);
            $("#inventoryGrid").jqxGrid(
            {
                width: '100%',
                source: dataAdapter,                
                pageable: true,
                autoheight: true,
                sortable: true,
                //altrows: true,
                pagesizeoptions: ['50', '80', '100'],
                editable: false,
                filterable: true,
                showfilterrow: true,
                selectionmode: 'multiplecellsadvanced',
                columns: [
                  { text: 'Date',  datafield: 'date', width: '8%',cellsformat:'MMM d,yyyy' },
                  { text: 'Delivery No',  datafield: 'document_no', width: '8%' },  
                  { text: 'Product Name',  datafield: 'product_name', width: '20%' },
                  { text: 'Material Code',  datafield: 'material_code', width: '8%' },
                  { text: 'Batch No.',  datafield: 'batch_no', width: '8%' },
                  { text: 'Production Order No.',  datafield: 'po_number', width: '10%' },
                  { text: 'Source Location Name',  datafield: 'src_loc_name', width: '10%' },
                  { text: 'Destination Location Name',  datafield: 'dest_loc_name', width: '10%' },
                  { text: 'Quantity',  datafield: 'quantity', width: '6%' },
                  { text: 'TP Id',  datafield: 'tp_id', width: '12%', cellclassname: cellclass },
                  
                ],
               
            });
            $("#inventoryGrid").on('cellclick', function (event) {
                console.log(event);
                
                var pName = event.args.rowindex;
                
                if(event.args.row.bounddata.vehicle_no!='null'){
                    $("#popover").html(event.args.row.bounddata.vehicle_no);
                    $("#popOverView").jqxPopover({theme: 'arctic', arrowOffsetValue: 20,title: "Vehicle Number", showCloseButton: true, isModal: false,showArrow: true,rtl: false, selector: $(".rowIndex"+pName) } );
                    $("#popOverView").jqxPopover('render');
                    
                }
            });
                $("#loading").hide();
            });
   
        }
        function SaveExport()
        {
            $("#loading").show();
            var emptyFlage=0;
            $(".form-control").each(function (){
                if($(this).val()!=''){
                    emptyFlage=1;
                }
            });
            if(emptyFlage==0)
                alert('Please select any search criteria for export');
            else{

                $.post('exportProductionEntry',$( "#SearchFrm" ).serialize(),function(response){
                    if(response)
                    {
                      $("#loading").hide();
                      alert('Your request is loged, we will sent you report on your registered mail.');
                    }
                    
                });    
            }
            $("#loading").hide();
        }
	</Script>
@stop	
@extends('layouts.footer')
