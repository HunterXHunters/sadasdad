@extends('layouts.default')

@extends('layouts.sideview')

@section('content')

    <div class="box">
      <div class="box-header with-border ">
        <h3 class="box-title"><strong>Pallets </strong>  List</h3>
        @if (Session::has('flash_message'))            
            <div class="alert alert-info">{{ Session::get('flash_message') }}</div>
            @endif
        <a href="javascriot:void(0)"  data-toggle="modal" id="addPallet" class="pull-right" data-target="#basicvalCodeModal" data-url="{{URL::asset('pallets/create')}}"><i class="fa fa-plus-circle"></i><span style="font-size:11px;">Add Pallet</span></a>
        <a id="excelExport" class="pull-right"><i class="fa fa-file-excel-o"></i><span style="font-size:11px;">Export</span></a>
      </div>
       <div class="col-sm-12" style="margin-top:15px;">
         <div class="tile-body nopadding">                  
            <div id="jqxgrid"></div>
          </div>
        </div>

      </div>

       <div class="modal fade" id="basicvalCodeModal" tabindex="-1" role="dialog" aria-labelledby="basicvalCode" aria-hidden="true">
                        <div class="modal-dialog wide">
                          <div class="modal-content">
                            <div class="modal-header">
                              <button type="button" class="close" id="close" data-dismiss="modal" aria-hidden="true">x</button>
                              <h4 class="modal-title" id="basicvalCode">Add</h4>
                            </div>
                            <div class="modal-body" id="palletDiv">





                     </div>
                          </div><!-- /.modal-content -->
                        </div><!-- /.modal-dialog -->
                      </div><!-- /.modal -->
<!-- Modal - Popup for Verify User Password while deleting -->
    <div class="modal fade" id="verifyUserPassword" tabindex="-1" role="dialog" aria-labelledby="basicvalCode" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                    <h4 class="modal-title" id="basicvalCode">Enter Password</h4>
                </div>
                <div class="modal-body">
                    <div class="">
                        <div class="form-group col-sm-12">
                            <label class="col-sm-2 control-label" for="BusinessType">Password*</label>
                            <div class="col-sm-10">
                                <div class="input-group input-group-sm">
                                    <span class="input-group-addon addon-red"><i class="fa fa-flag-checkered"></i></span>
                                    <input type="password" id="verifypassword" name="passwordverify" class="form-control">      
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal" id="cancel-btn">Cancel</button>
                    <button type="button" id="save-btn" class="btn btn-success">Submit</button>
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
    {{HTML::script('jqwidgets/jqxdata.export.js')}}
    {{HTML::script('jqwidgets/jqxgrid.export.js')}}
    <script type="text/javascript">
        $(document).ready(function(){
    window.setTimeout(function(){
        $(".alert").hide();
    },3000);
});
        $(document).ready(function () {           
            var url = "pallets/getdata";

            // prepare the data
            var source =
            {
                datatype: "json",
                datafields: [
                    { name: 'pallet_id', type: 'string' },
                    { name: 'pallet_type_id', type: 'string' },
                    { name: 'weight', type: 'string' },
                    { name: 'weightUOMId', type: 'string' },
                    /*{ name: 'height', type: 'string' },
                    { name: 'width', type: 'string' },
                    { name: 'length', type: 'string' },
                    { name: 'dimensionUOMId', type: 'string' },*/
                    { name: 'warehouse', type: 'string' },
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
                width:"100%",
                source: source,
                selectionmode: 'multiplerowsextended',
                sortable: true,
                pageable: true,
                autoheight: true,
                autoloadstate: false,
                autosavestate: false,
                columnsresize: true,
                columnsreorder: true,
                filterable: true,
                //showfilterrow: true,
                columns: [
                 { text: 'Pallet Id', datafield: 'pallet_id', width:"20%"},
                 { text: 'Pallet Type', datafield: 'pallet_type_id', width: "20%"},
                 { text: 'Weight UOM', datafield: 'weightUOMId', width: "15%"},
                 { text: 'Weight', datafield: 'weight', width: "10%"},
                 /*{ text: 'Dimensions UOM', datafield: 'dimensionUOMId', width: "13%"},
                 { text: 'Height', datafield: 'height', width: "10%" },
                 { text: 'Width', datafield: 'width', width: "10%" },
                 { text: 'Length', datafield: 'length', width: "10%" },*/
                 { text: 'WareHouse', datafield: 'warehouse', width: "20%"},
                 { text: 'Actions', datafield: 'actions',width:"15%",exportable:false }
                ]               
            });
/*            $("#excelExport").jqxButton({
                theme: 'energyblue'
            });*/

            $("#excelExport").click(function() {
                $("#jqxgrid").jqxGrid('exportdata', 'xls', 'Pallets');
            });
             makePopupAjax($('#basicvalCodeModal'));
             makePopupEditAjax($('#basicvalCodeModal1'), 'id');

            setTimeout(function(){ $('#jqxgrid').jqxGrid({showfilterrow: true}); },700);

            setInterval(function(){
                $('#jqxgrid').find('#row0jqxgrid > div').eq(0)
                        .css('width','130'); 
            },300);

            if($("#saveState").length){
                $("#saveState").jqxButton({ theme: theme });
                $("#saveState").click(function () {
                    // save the current state of jqxGrid.
                    state = $("#jqxgrid").jqxGrid('savestate');
                });
            }

            if($("#loadState").length){
                $("#loadState").jqxButton({ theme: theme });
                $("#loadState").click(function () {
                    // load the Grid's state.
                    if (state) {
                        $("#jqxgrid").jqxGrid('loadstate', state);
                    }
                    else {
                        $("#jqxgrid").jqxGrid('loadstate');
                    }
                });
            }
            
            var state = null;           
        });

       function deletePallet(id)
    { 
        var dec = confirm("Are you sure you want to Delete ?");
        if ( dec == true ){
        $('#verifyUserPassword').modal('show');
        $('#verifyUserPassword button#cancel-btn').on('click',function(e){
            e.preventDefault();
            //console.log('clicked cancel');
            $('#verifyUserPassword').modal('hide');
        });
        $('#verifyUserPassword button#save-btn').on('click',function(e){
            e.preventDefault();
            //console.log('cliked submit');
            var userPassword = $.trim($('#verifyUserPassword input').val());
            if(userPassword == ''){
                alert('Field is required');
                return false;
            } else
            $.ajax({
                url: '/pallets/delete/'+id,
                data: 'password='+userPassword,
                type:'POST',
                success: function(result)
                {
                    if(result == 1){
                        alert('Successfully Deleted !!');
                        location.reload();
                        //window.location.href = '/customer/editcustomer/'+manufacturerId;
                        $('#verifyUserPassword').modal('hide');
                    }else if(result == 0){
                        alert('Already pallatised cannot delete this pallet.');
                        location.reload();
                        //window.location.href = '/customer/editcustomer/'+manufacturerId;
                        $('#verifyUserPassword').modal('hide');
                    }
                    else{
                        alert(result);
                    }
                },
                error: function(err){
                    console.log('Error: '+err);
                },
                complete: function(data){
                    console.log(data);
                }
            });
        });
    }
}
          
       
    </script> 


    <script type="text/javascript">
$(document).ready(function(){
    $("#addPallet").click(function(){
        $.get($(this).attr('data-url'),function(response){
            $("#basicvalCode").html('Add New Pallet');
            $("#palletDiv").html(response);
        });
    });    
});

</script>

<script type="text/javascript">
function editPallet(id){ 
    $.get('pallets/edit/'+id,function(response){
        $("#basicvalCode").html('Edit Pallet');
        $("#palletDiv").html(response);
        $("#basicvalCodeModal").modal('show');
        $("#close").click(function(){
            $("#basicvalCodeModal").modal('hide');
        })
    });
    
    
}
</script>   
@stop

