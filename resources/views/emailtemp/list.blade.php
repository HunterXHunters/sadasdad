@extends('layouts.default')
@extends('layouts.header')
@extends('layouts.sideview')
@section('content')

   

  <div class="box">
    <div class="box-header">
      <h3 class="box-title">Email <strong> Templates</strong></h3>
      @if (Session::has('flash_message'))            
            <div class="alert alert-info">{{ Session::get('flash_message') }}</div>
            @endif
      @if(isset($allowed_buttons['add_emailtemp']) && $allowed_buttons['add_emailtemp'] == 1)
      <a href="{{URL::asset('email/add')}}" class="pull-right"><i class="ion-plus-round"></i> <span style="font-size:11px;">Add Email Template</span></a>
     @endif
    </div>
   
     <div class="col-sm-12">
       <div class="tile-body nopadding">                  
       <div id="jqxgrid"></div>
     </div>

  </div>                     

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

    <!-- Include all compiled plugins (below), or include individual files as needed -->
  <script src="js/bootstrap.min.js"></script>
    
  <script type="text/javascript">
  $(document).ready(function(){
    window.setTimeout(function(){
        $(".alert").hide();
    },3000);
});
    $(document).ready(function () 
        {           
            var url = "email/show/";
            // prepare the data
            var source =
            {
                datatype: "json",
                datafields: [
                    { name: 'code', type: 'string' },
                    { name: 'name', type: 'string' },
                    { name: 'from', type: 'string' },
                    { name: 'replyto', type: 'string' },
                    { name: 'subject', type: 'string' },
                    { name: 'htmlbody', type: 'string' },
                    { name: 'textbody', type: 'string' },
                    { name: 'signature', type: 'string' },
                    { name: 'version', type: 'string' },
                    { name: 'actions', type: 'string' }
                   // { name: 'delete', type: 'string' }
                ],
                id: 'Id',
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
                  { text: 'Code', filtercondition: 'starts_with', datafield: 'code', width: "10%" },
                  { text: 'Template Name', filtercondition: 'starts_with', datafield: 'name', width: "20%" },
                  { text: 'From', datafield: 'from', width: "20%" },
                  //{ text: 'Reply To', datafield: 'replyto', width:100},
                  { text: 'Subject', datafield: 'subject', width:"30%"},
                  //{ text: 'HTML Body', datafield: 'htmlbody', width:100},
                  //{ text: 'Text Body', datafield: 'textbody', width:100},
                  //{ text: 'Signature', datafield: 'signature', width:100},
                  { text: 'Version', datafield: 'version', width:"10%"},
                  //{ text: 'Edit', datafield: 'edit' },
                  { text: 'Actions', datafield: 'actions',width:"10%" }
                ]               
            });  
            makePopupAjax($('#basicvalCodeModal'));
            makePopupEditAjax($('#basicvalCodeModal1'), 'Id');
      
        });    
        // function deleteEntityType(Id)
        // {
        //     var decission = confirm("Are you sure you want to Delete.");
        //     if(decission==true)
        //         window.location.href='email/delete/'+Id;
        // }


        function deleteEntityType(Id)
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
                url: '/email/delete/'+Id,
                data: 'password='+userPassword,
                type:'POST',
                success: function(result)
                {
                    if(result == 1){
                        alert('Succesfully Deleted !!');
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
  }
    </script>    
@stop