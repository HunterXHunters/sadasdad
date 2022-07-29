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
            var url = "/gdsOrders/show";
            
            // prepare the data
            var source =
            {
                datatype: "json",
                datafields: [
                    { name: 'gds_order_id', type: 'integer' },
                    { name: 'channnel_name', type: 'string' },
                    { name: 'name', type: 'string' },
                    { name: 'order_date', type: 'string' },                    
                    { name: 'price', type: 'decimal' },
                    { name: 'stat', type: 'string' },                    
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
            $("#jqxgrid").jqxGrid(
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
                  { text: 'GDS Order Id', filtercondition: 'starts_with', datafield: 'gds_order_id', width: "10%" },
                  { text: 'Channel Name', datafield: 'channnel_name', width: "20%" }, 
                  { text: 'Ordered By', datafield: 'name', width: "15%" },
                  { text: 'Order Date', datafield: 'order_date', width:"15%"},
                  { text: 'Amount', datafield: 'price', width:"15%"},
                  { text: 'Order Status', datafield: 'stat', width: "15%" },
                  { text: 'Actions', datafield: 'actions',width:"10%" }
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
function editChannelOrders(id)
{
  console.log(id);
     $.get('/gdsOrders/edit/'+id,function(response){ 
            $("#basicvalCode").html('Edit Channel Order');
            
            $("#channelOrdersDiv").html(response);
            
            $("#editchannelOrders").click();
        });
}
</script> 
@stop

  


     @if (Session::has('message'))
     <div class="flash alert">
         <p>{{ Session::get('message') }}</p>
     </div>
     @endif

 <div class="box">
              <div class="box-header">
                <h3 class="box-title"><strong>GDS Orders </strong> </h3>
                
               <button data-toggle="modal" id="editchannelOrders" class="btn btn-default" data-target="#basicvalCodeModal" style="display: none" data-url="{{URL::asset('gdsOrders/edit')}}"></button>
              </div>
                     
  
            <div id="jqxgrid">
<!--             <button data-toggle="modal" id="editchannelOrders" class="btn btn-default" data-target="#basicvalCodeModal" style="display: none" data-url="{{URL::asset('gdsOrders/edit')}}"></button> -->
              
            </div>
             
</div>
<!-- Modal -->
                    <div class="modal fade" id="basicvalCodeModal" tabindex="-1" role="dialog" aria-labelledby="basicvalCode" aria-hidden="true">
                      <div class="modal-dialog wide">
                        <div class="modal-content">
                          <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">X</button>
                            
                          </div>
                          <div class="modal-body">                         
                              <div class="modal-body" id="channelOrdersDiv">
                              </div>
                          </div>
                        </div><!-- /.modal-content -->
                      </div><!-- /.modal-dialog -->
                    </div><!-- /.modal -->


           <!-- Modal - Popup for Verify User Password while deleting -->
  @stop









