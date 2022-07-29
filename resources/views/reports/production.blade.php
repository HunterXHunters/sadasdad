@extends('layouts.default')

@extends('layouts.header')

@extends('layouts.sideview')

@section('content')
<div align="center" id="loading" style="text-align:center; z-index:9999;position:absolute;background:rgba(0,0,0,0.3);height:709px; width:1261px; display:none;" ><img src="/img/loading.gif">    </div>
<div class="box">
    <div class="box-header with-border">
      <h3 class="box-title">Live Production - Packing</h3>
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
                            <button type="button" class="btn btn-green" onclick="SaveExport();">Export</button>
                            <input type="hidden" name="rpt_key" id="rpt_key" value="production">
                            <input type="hidden" name="rpt_dateField" id="rpt_dateField" value="manufacturing_date">
                        </div>
                    </div>
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
            
            
            var source =
            {
                id: 'id',
                datatype: "json",
                localdata:{{$inventories}},
                pagesize:50,
                pager: function (pagenum, pagesize, oldpagenum) {
                    // callback called when a page or page size is changed.
                }
            };
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
                  { text: 'Date',  datafield: 'date', width: '10%' },
                  { text: 'Location Name',  datafield: 'location_name', width: '15%' },
                  { text: 'Product Name',  datafield: 'product_name', width: '10%' },
          { text: 'Category Name', datafield: 'category_name',width: '10%'},
                  { text: 'Material Code',  datafield: 'material_code', width: '10%' },
                  { text: 'Batch No.',  datafield: 'batch_no', width: '10%' },
                  { text: 'Production Order No.',  datafield: 'po_number', width: '15%' },
                  { text: 'Shift',  datafield: 'shiftNo', width: '10%' },
                  { text: 'Quantity',  datafield: 'qty', width: '10%' },

                ],
               
            });
        });
        function getResult()
        {
            $("#loading").show();
            var url = 'searchProduction'
            $.post(url,$( "#SearchFrm" ).serialize(),function(response){
                $("#inventoryGrid").remove();
                var div = '<div id="inventoryGrid"></div>';
                $("#gridDiv").html(div);

                var source =
                {
                    id: 'manufacturing_date',
                    datatype: "json",
                    localdata:response,
                    pagesize:50,
                    pager: function (pagenum, pagesize, oldpagenum) {
                        // callback called when a page or page size is changed.
                    }
                };
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
                      { text: 'Date',  datafield: 'date', width: '10%' },
                      { text: 'Location Name',  datafield: 'location_name', width: '15%' },
                      { text: 'Product Name',  datafield: 'product_name', width: '15%' },
                      { text: 'Material Code',  datafield: 'material_code', width: '10%' },
                      { text: 'Batch No.',  datafield: 'batch_no', width: '10%' },
                      { text: 'Production Order No.',  datafield: 'po_number', width: '20%' },
                      { text: 'Shift',  datafield: 'shiftNo', width: '10%' },
                      { text: 'Quantity',  datafield: 'qty', width: '10%' },
                    ],
                   
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
