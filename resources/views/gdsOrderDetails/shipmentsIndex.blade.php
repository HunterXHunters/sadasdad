@extends('layouts.default')

@extends('layouts.header')

@extends('layouts.sideview')

@section('content')


<html>
<head>
@section('style')
    {{HTML::style('jqwidgets/styles/jqx.base.css')}}
@stop

@section('script')
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

    <!-- Include all compiled plugins (below), or include individual files as needed -->
    

    <script type="text/javascript">
    
    $(document).ready(function () 
        {            
            var id = {{ $id }};
            var url = "/gdsOrders/showShipmentsIndex/"+id;
            
            // prepare the data
            var source =
            {
                datatype: "json",
                datafields: [
                    { name: 'gds_ship_grid_id', type: 'integer' },
                    { name: 'gds_order_id', type: 'integer' },
                    { name: 'created_date', type: 'string' },
                    { name: 'update_date', type: 'string' },               
                    { name: 'actions', type: 'string' }
                   // { name: 'delete', type: 'string' }
                ],
                id: 'id',
                url: url,
                 pager: function (pagenum, pagesize, oldpagenum) {
                    // callback called when a page or page size is changed.
                }
            };
            var dataAdapter = new $.jqx.dataAdapter(source);
            $("#jqxgrid1").jqxGrid(
            {
                width: "100%",
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
                  { text: 'Shipment ID', filtercondition: 'starts_with', datafield: 'gds_ship_grid_id', width: "15%" },
                  { text: 'Gds Order ID', datafield: 'gds_order_id', width: "15%" },
                  { text: 'Ordered Date', datafield: 'created_date', width: "25%" },
                  { text: 'Order Shipped Date', datafield: 'update_date', width: "25%" },            
                  { text: 'Actions', datafield: 'actions',width:"20%" }
                ]               
            });
            
          }); 

function deleteEntityType(id)
        {
            var decission = confirm("Are you sure you want to Delete.");
            if(decission==true)
                window.location.href='gdsOrders/delete/'+id;
        }
    
    </script> 
    <script type="text/javascript">
function editShipmentOrders(id)
{
  console.log(id);
     $.get('/gdsOrders/editShipments/'+id,function(response){ 
            $("#basicvalCode").html('Edit Channel Order');
            
            $("#gdsShipmentOrdersDiv").html(response);
            
            $("#editShipmentOrdersDiv").click();
        });
}
</script> 
@stop

<div class="row">
    <div class="box topbar">

        <div class="col-md-4">
        <div class="box-header">
    
        <h3 class="box-title">Order ID: {{$id}}</h3>

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
          <li><a href="/gdsOrders/edit/{{$id}}">Information</a></li>
          <li><a href="/gdsOrders/editInvoice/{{$id}}">Invoices</a></li>
          <li><a href="#">Credit Memos</a></li>
          <li class="active"><a href="/gdsOrders/shipmentsIndex/{{$id}}">Shipments</a></li>
          <li><a href="#">RMA</a></li>
          <li><a href="#">Comments History</a></li>
          <li><a href="#">Transactions</a></li>
          </ul>
        </div>
      </div>
    </div>
    <div class="col-md-9">
    
      <div class="box">
              <div class="box-header">
                <h3 class="box-title"><strong>Shipments </strong> </h3>
                <button data-toggle="modal" id="editShipmentOrdersDiv" class="btn btn-default" data-target="#basicvalCodeModal" style="display: none" data-url="{{URL::asset('gdsOrders/editShipments')}}"></button>
              </div>  
            <div id="jqxgrid1">            
            </div>
             
          </div>
    </div>
    </div>
  


     @if (Session::has('message'))
     <div class="flash alert">
         <p>{{ Session::get('message') }}</p>
     </div>
     @endif


<!-- Modal -->
                    <div class="modal fade" id="basicvalCodeModal" tabindex="-1" role="dialog" aria-labelledby="basicvalCode" aria-hidden="true">
                      <div class="modal-dialog wide">
                        <div class="modal-content">
                          <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">X</button>
                            
                          </div>
                          <div class="modal-body">                         
                              <div class="modal-body" id="gdsShipmentOrdersDiv">
                              </div>
                          </div>
                        </div><!-- /.modal-content -->
                      </div><!-- /.modal-dialog -->
                    </div><!-- /.modal -->


           <!-- Modal - Popup for Verify User Password while deleting -->
  @stop









