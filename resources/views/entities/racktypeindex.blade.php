@extends('layouts.default')

@extends('layouts.sideview')

@section('content')    

<div class="box">

<div class="box-header">
    <h3 class="box-title"><strong>Rack Type </strong>List</h3>
    @if (Session::has('flash_message'))            
            <div class="alert alert-info">{{ Session::get('flash_message') }}</div>
    @endif
    <!-- <span style="float:right;"><a href="entities/racktypecreate"><i class="fa fa-plus-circle"></i> <span>Add</span></a></span> -->
    <a href="javascriot:void(0)"  data-toggle="modal" id="addRack" class="pull-right" data-target="#basicvalCodeModal" data-url="{{URL::asset('rack/racktypecreate')}}"><i class="fa fa-plus-circle"></i><span style="font-size:11px;">Add Rack</span></a>
</div>        
      
<div class="col-sm-12" style="margin-top:15px;">
     <div class="tile-body nopadding">                  
        <div id="jqxgrid"></div>
      </div>
</div>
<div class="col-sm-12">
   <div class="tile-body nopadding">                  
      <div id="jqxgrid"  style="width:100% !important;"></div>
        <button data-toggle="modal" id="editRack" class="btn btn-default" data-target="#basicvalCodeModal" style="display: none" data-url="{{URL::asset('/rack/racktypeedit')}}"></button>
    </div>
</div>

</div>

<div class="modal fade" id="basicvalCodeModal" tabindex="-1" role="dialog" aria-labelledby="basicvalCode" aria-hidden="true">
    <div class="modal-dialog wide">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" id="close" data-dismiss="modal" aria-hidden="true">x</button>
                <h4 class="modal-title" id="basicvalCode">Add Rack</h4>
            </div>
            <div class="modal-body" id="rackDiv">
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
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
    <script type="text/javascript">
    
    $(document).ready(function(){
    window.setTimeout(function(){
        $(".alert").hide();
    },3000);
    });


    function editRack(id){ 
        $.get('/rack/racktypeedit/'+id,function(response){
        $("#basicvalCode").html('Edit Rack');
        $("#rackDiv").html(response);
        $("#basicvalCodeModal").modal('show');
        $("#close").click(function(){
        $("#basicvalCodeModal").modal('hide');
                })   
/*            $("#basicvalCode").html('Edit Rack');
            $("#rackDiv").html(response);  
            //console.log('Hi we are here...!!!');         
            $("#editRack").click(); */   
  
        });   
    }
    $(document).ready(function(){
        $("#addRack").click(function(){
        $.get($(this).attr('data-url'),function(response){
        $("#basicvalCode").html('Add New Rack');
        $("#rackDiv").html(response);
            });
        });    
    });    
        $(document).ready(function () {           
            var url = "/rack/getrackdata";
            
            // prepare the data
            var source =
            {
                datatype: "json",
                datafields: [
                    { name: 'rack_type_name', type: 'string' },
                    { name: 'rack_capacity', type: 'string' },
                    { name: 'no_of_bins', type: 'number' },
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
                  { text: 'Rack Type Name', filtercondition: 'starts_with', datafield: 'rack_type_name', width: "30%" },
                  { text: 'Rack capacity', datafield: 'rack_capacity', width: "25%" },
                  { text: 'Number Of Bins', datafield: 'no_of_bins', width: "25%"  },
                  { text: 'Actions', datafield: 'actions' ,width: "20%" }
                 
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

    function deleteRackType(id)
    { 
        var dec = confirm("Are you sure you want to Delete ?");
        if ( dec == true )
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
                return false
            } else
            $.ajax({
                url: '/rack/racktypedelete/'+id,
                data: 'password='+userPassword,
                type:'POST',
                success: function(result)
                {
                    if(result == 1){
                        alert('Successfully Deleted !!');
                        location.reload();
                        //window.location.href = '/customer/editcustomer/'+manufacturerId;
                        $('#verifyUserPassword').modal('hide');
                    }else{
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
    </script>    
@stop

