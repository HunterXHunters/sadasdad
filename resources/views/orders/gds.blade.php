@extends('layouts.default')
@extends('layouts.header')
@extends('layouts.sideview')
@section('content')


    <div class="box">
              <div class="box-header">
                <h3 class="box-title"><strong>GDS </strong>  Orders</h3>
              </div>
               
               <div class="col-sm-12">
                 <div class="tile-body nopadding">                  
                    <div id="jqxgrid"  style="width:100% !important;"></div>
                     <button data-toggle="modal" id="edit" class="btn btn-default" data-target="#wizardCodeModal" style="display: none"></button>
                    </div>
                </div>

              </div>



@stop
@section('style')
   {{HTML::style('jqwidgets/styles/jqx.base.css')}}   
     
@stop

@section('script')
    {{HTML::script('scripts/jquery-1.10.1.min.js')}}
    {{HTML::script('jqwidgets/jqxcore.js')}}
    {{HTML::script('jqwidgets/jqxdata.js')}}
    {{HTML::script('jqwidgets/jqxbuttons.js')}}
    {{HTML::script('jqwidgets/jqxscrollbar.js')}}
    {{HTML::script('jqwidgets/jqxdatatable.js')}}
    {{HTML::script('jqwidgets/jqxtreegrid.js')}}
    {{HTML::script('scripts/demos.js')}} 

    {{HTML::script('jqwidgets/jqxmenu.js')}}
    {{HTML::script('jqwidgets/jqxgrid.js')}}
    {{HTML::script('jqwidgets/jqxgrid.selection.js')}}
    {{HTML::script('jqwidgets/jqxgrid.columnsresize.js')}}
    
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
        var url = "/orders/getGds" ;
            // prepare the data
            var source =
            {
                datatype: "json",
                datafields: [
                    { name: 'actions', type: 'string' },
                    { name: 'customer_name', type: 'string' },
                    { name: 'order_status', type: 'string' },
                    { name: 'date_added', type: 'datetime' },
                    { name: 'bill_to_name', type: 'string' },
                    { name: 'ship_to_name', type: 'string' },
                    
                    
                   // { name: 'delete', type: 'string' }
                ],
                id: 'order_no',
                url: url,
                pager: function (pagenum, pagesize, oldpagenum) {
                    // callback called when a page or page size is changed.
                }
            };
            var dataAdapter = new $.jqx.dataAdapter(source);
            createGrid(dataAdapter);
        

        function createGrid(source){
          $("#jqxgrid").jqxGrid(
            {
                width: '100%',
                source: source,
                selectionmode: 'multiplerowsextended',
                sortable: true,
                pageable: true,
                autoheight: true,
                autoloadstate: false,
                autosavestate: false,
                columnsresize: true,
                columnsreorder: true,
                showfilterrow: true,
                filterable: true,
                columns: [
                  { text: 'Order Number', datafield: 'actions',width:"20%" },
                  { text: 'Customer Name', datafield: 'customer_name', width: "20%"},
                  { text: 'Order Status', datafield: 'order_status', width:"10%"},
                  { text: 'Order Date', datafield: 'date_added', width: "10%" },
                  { text: 'Bill To Name', datafield: 'bill_to_name', width:"20%"},
                  { text: 'Ship To Name', datafield: 'ship_to_name', width:"20%"},
                  //{ text: 'Total Cost', datafield: 'total_cost', width:"20%"},
                  
                  //{ text: 'Edit', datafield: 'edit' },
                 
                ]               
            }); 
        }
 });

</script>   
@stop
