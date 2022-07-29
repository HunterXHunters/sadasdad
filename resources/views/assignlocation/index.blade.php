 @extends('layouts.default')

@extends('layouts.sideview')

@section('content')

<div class="box">
  <div class="box-header with-border ">
    <h3 class="box-title"><strong>List of </strong>  Assigned Locations</h3>
  </div>
  <div class="col-sm-12" style="margin-top:15px;">
    <div class="tile-body nopadding">                  
       <div id="jqxgrid"></div>
       <button data-toggle="modal" id="editassign" class="btn btn-default" data-target="#basicvalCodeModal" style="display: none" data-url="{{URL::asset('assignlocation/edit')}}"></button>
    </div>
  </div>
</div>

<div class="modal fade" id="basicvalCodeModal" tabindex="-1" role="dialog" aria-labelledby="basicvalCode" aria-hidden="true">
  <div class="modal-dialog wide">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" id="close_it_now" data-dismiss="modal" aria-hidden="true">X</button>
        <h4 class="modal-title" id="basicvalCode">Edit Assigned Product</h4>
      </div>
        <div class="modal-body" id="assignDiv">
        </div>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

@stop

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

    
    <script type="text/javascript">
        $(document).ready(function () {           
            var url = "assignlocation/getdata";
            
            // prepare the data
            var source =
            {
                datatype: "json",
                 datafields: [
                    { name: 'entity_name', type: 'string' },
                    { name: 'product_name', type: 'string' },
                    { name: 'locator', type: 'string' },
                    { name: 'actions', type: 'string' }
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
                  { text: 'Entity Name', datafield: 'entity_name', width: "30%" },
                  { text: 'Product Name', datafield: 'product_name', width: "30%" },
                  { text: 'Locator', datafield: 'locator', width: "25%" },
                  { text: 'Actions', datafield: 'actions',width:"15%" }
                ]               
            });

            if($("#saveState").length) $("#saveState").jqxButton({ theme: theme });
            if($("#loadState").length) $("#loadState").jqxButton({ theme: theme });
            var state = null;
            $("#saveState").click(function () {
                // save the current state of jqxGrid.
                state = $("#jqxgrid").jqxGrid('savestate');
            })
            ;
            $("#loadState").click(function () {
                // load the Grid's state.
                if (state) {
                    $("#jqxgrid").jqxGrid('loadstate', state);
                }
                else {
                    $("#jqxgrid").jqxGrid('loadstate');
                }
            });
        });    
        function deleteEntity(id)
        {
            
            var decission = confirm("Are you sure you want to Delete.");
            if(decission==true)
                 
                window.location.href='/assignlocation/delete/'+id;
        }

        function editAssign(id)
        {

          $.get('assignlocation/edit/'+id ,function(response){ 
                $("#basicvalCode").html('Edit Assigned Product');
                
                $("#assignDiv").html(response);
                
                $("#editassign").click();
            });
        }
    </script>  
@stop

