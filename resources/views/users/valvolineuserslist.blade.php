 @extends('layouts.default')

@extends('layouts.header')

@extends('layouts.sideview')

@section('content')

@section('style')
    {{HTML::style('jqwidgets/styles/jqx.base.css')}}
    {{HTML::style('css/bootstrap-select.css')}}
@stop
@section('script')
    {{HTML::script('js/plugins/bootstrap-select/bootstrap-select.js')}}
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

    $(document).ready(function ()
        {          
            var url = "/users/valvolineuserlist";

            // prepare the data

            var source =
            {
                datatype: "json",

                datafields: [
                    
                     { name: 'name', type: 'string' },
                    { name: 'email', type: 'string' },
                    { name: 'phone_no', type: 'integer' },
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

                  { text: 'Name', datafield: 'name', width: "30%" },

                  { text: 'Email', datafield: 'email', width: "30%" },
                  
                  { text: 'Phone No', datafield: 'phone_no', width: "30%" },
                  { text: 'Actions', datafield: 'actions',width: "10%" }

                ]              

            });
        });

 
 $('#add_valvoline_user').bootstrapValidator({
    message: 'This value is not valid',
    icon: {
        validating: 'glyphicon glyphicon-refresh'
    },
    fields: {
        name: {
                    validators: {
                        notEmpty: {
                            message: 'The name required and can\'t be empty'
                        },                        
                    }
                },
                email: {
                    validators: {
                        notEmpty: {
                            message: 'The email required'
                        },
                    regexp: {
                      regexp: '^[a-z0-9](\.?[a-z0-9]){5,}@valvolinecummins\.com$',
                      message: 'Official Email is not valid.'
                    },
                      
                    }
                },
                phone_no: {
                    validators: {
                        regexp: {
                        regexp: '^[0-9]{10,10}$',
                        message: "Please enter 10 digits only."
                    },
                       
                    }
                },
    }
}).on('success.form.bv', function(e){
    e.preventDefault();

    var frmData = $('#add_valvoline_user').serialize();
    $.ajax({
        type: "POST",
        url: '/users/addvalvolineuser',
        data: frmData,
        success: function (respData)
        {
            alert(respData);
            window.location.reload();
            
        }
    });
});

    </script>   
@stop
@section('content')
<style>
#dragandrophandler
{
border: 2px dashed #92AAB0;
width: 350px;
height: 50px;
color: #92AAB0;
text-align: center;
vertical-align: middle;
padding: 10px 0px 10px 10px;
font-size:200%;
display: table-cell;
}
.progressBar {
    width: 100px;
    height: 22px;
    border: 1px solid #ddd;
    border-radius: 5px; 
    overflow: hidden;
    display:inline-block;
    margin:0px 10px 5px 5px;
    vertical-align:top;
}

.progressBar div {
    height: 100%;
    color: #fff;
    text-align: right;
    line-height: 22px; /* same as #progressBar height if we want text middle aligned */
    width: 0;
    background-color: #0ba1b5; border-radius: 3px; 
}
.statusbar
{
    border-top:1px solid #A9CCD1;
    min-height:25px;
    width:450px;
    padding:10px 10px 0px 10px;
    vertical-align:top;
}
.statusbar:nth-child(odd){
    background:#EBEFF0;
}
.filename
{
display:inline-block;
vertical-align:top;
width:150px;
}
.filesize
{
display:inline-block;
vertical-align:top;
color:#30693D;
width:80px;
margin-left:10px;
margin-right:5px;
}
.abort{
    background-color:#A8352F;
    -moz-border-radius:4px;
    -webkit-border-radius:4px;
    border-radius:4px;display:inline-block;
    color:#fff;
    font-family:arial;font-size:13px;font-weight:normal;
    padding:4px 15px;
    cursor:pointer;
    vertical-align:top
    }

    #loading{

    display:none;
    position:fixed;
    top:300px;
    left:700px;
    z-index: 1;
}
</style>
            <div class="box">
              <div class="box-header">
                <h3 class="box-title"><strong>Valvoline </strong>  Users</h3>
                 
                 
                 <!-- <a href="#"  data-toggle="modal" id="addUser" class="pull-right" data-target="#wizardCodeModal" data-url="{{URL::asset('users/add')}}"><i class="ion-android-person-add"></i> <span style="font-size:11px;">Add Valvoline User</span></a>
                 &nbsp; -->

                 <button  data-toggle="modal"  data-target="#addusers" class="pull-right btn btn-primary" style = "margin-left:20px;">Add User</button>

                 <button  data-toggle="modal"  data-target="#bulkupdatemodel" name="bulkupdateusers" id="bulkupdateusers" class="pull-right btn btn-primary">Import Users</button>&nbsp;&nbsp;


              </div>
               
               <div class="col-sm-12">
                 <div class="tile-body nopadding">                  
                    <div id="jqxgrid"  style="width:100% !important;"></div>
                     <button data-toggle="modal" id="edit" class="btn btn-default" data-target="#wizardCodeModal" style="display: none"></button>
                    </div>
                </div>

              </div>

              <div id='loading' style="display:none" >
                  <img src="/jqwidgets/styles/images/loader.gif" >
              </div>

                 
            
             
            <div class="modal fade" id="addusers" tabindex="-1" role="dialog" aria-labelledby="basicvalCode" aria-hidden="true">

                <div class="modal-dialog wide">

                  <div class="modal-content">

                    <div class="modal-header">

                      <button type="button" class="close" data-dismiss="modal" aria-hidden="true">x</button>

                      <h4 class="modal-title" id="basicvalCode">Add Valvoline User</h4>

                    </div>

                  <div class="modal-body">
                    {{ Form::open(array('url' => '','id' => 'add_valvoline_user','method'=>'post'))}}
                    <div class="row">    

                        <div class="form-group col-sm-4">
                        <label>name</label>
                        <div class="fileinput fileinput-new" data-provides="fileinput">
                            <input type ="text" class ="form-control" name="name" id= "name">
                       </div>
                        </div>

                        <div class="form-group col-sm-4">
                        <label>Email</label>
                        <div class="fileinput fileinput-new" data-provides="fileinput">
                            <input type ="text" class ="form-control" name="email" id= "email">
                       </div>
                        </div>

                        <div class="form-group col-sm-4">
                        <label>Phone Number</label>
                        <div class="fileinput fileinput-new" data-provides="fileinput">
                            <input type ="text" class ="form-control" name="phone_no" id= "phone_no">
                       </div>
                        </div>

                        <div class=" form-group col-md-8" >
                    
                        <button type="submit" class="btn green-meadow btn btn-primary" style="margin-top: 11px;
    margin-left: 404px;">Add</button>
                </div>

                    </div>
                    
                    {{ Form::close() }}
                </div>

                  </div><!-- /.modal-content -->

                </div><!-- /.modal-dialog -->

              </div><!-- /.modal -->    

              <div class="modal fade" id="bulkupdatemodel" tabindex="-1" role="dialog" aria-labelledby="basicvalCode" aria-hidden="true">

                <div class="modal-dialog wide">

                  <div class="modal-content">

                    <div class="modal-header">

                      <button type="button" class="close" data-dismiss="modal" aria-hidden="true">x</button>

                      <h4 class="modal-title" id="basicvalCode">Import From Excel</h4>

                    </div>
                    <!-- <div class="modal-body" id="popupLoader" align="center" style="display: none">
                        <img src="/img/ajax-loader.gif" >
                    </div>  -->    
                    <div class="modal-body">
                    <div id="update_import_users_message"></div>
                    {{ Form::open(array('url' => '/users/savevalvolineusers', 'id' => 'add_valvoline_users_from_excel', 'files'=>'true' )) }}
                    {{ Form::hidden('_method','POST') }}

                    <div class="form-group ">
                            <!--<label class="col-sm-2 control-label"></label>-->
                      <div class="col-xs-6 col-sm-4"> <a href="/download/valvoline_users_excel.xlsx"  style="margin-top:0px;"><i class="fa fa-download"></i> Download sample file </a></div>
                      <div class="col-xs-6 col-sm-4">
                               <!--  <span class="btn btn-success fileinput-button">
                                    <i class="glyphicon glyphicon-plus"></i>
                                    <span>Import from CSV</span>     -->                                
                                    <input id="locations_fileupload" required type="file" name="files">
                                    <input type="hidden" name="manufacturerID" value=""/>
                                                                   
                                
                            </div> 
                            
                     
                            
                           <div class="col-xs-6 col-sm-3"> {{ Form::submit('Upload File', array('class' => 'btn btn-primary', 'id' => 'add_users_excel_button')) }}
                    {{ Form::close() }}</div>

                    
                    </div>
                   <br><br>
                   
                </div>
                  </div><!-- /.modal-content -->

                </div><!-- /.modal-dialog -->

              </div>
        </section>


<script type="text/javascript">





// $('#bulkupdateusers').on('click',function(){
//     alert("add users from csv");
// });


$('#add_valvoline_users_from_excel').submit(function(event){
        event.preventDefault();
        $('#add_valvoline_users_from_excel').prop('disabled', true);
        $form = $(this);
        url = $form.attr('action');
        var formData = new FormData($(this)[0]);
        //var location_type_name = $('[name="location_type_name"]').val();
        //var locationTypeFields = {location_type_name: location_type_name , manufacturer_id: $('#add_locationtypes_form_excel #manufacturer_id').val() };
            // Send the data using post
        /*var posting = $.post(url, { location_type: locationTypeFields });*/
        $.ajax({
            url: url,
            type: 'POST',
            data: formData,
            async: false,
            success: function (data) {
                $('#update_import_users_message').text(data.msg);
                alert(data.msg);         
                
                
                
            },
            cache: false,
            contentType: false,
            processData: false
        });
        
        $form.bootstrapValidator('resetForm',true); 
        
        });
// .validate({
//         submitHandler: function (form) {
//         return false;
//         }   
//     });

    /*$('#bulkupdatemodel').on('show.bs.modal',function(){
        console.log('reset: #add_users_from_excel');
        $('#add_users_from_excel')[0].reset();
    });*/


    function deletevalvolineuser(id)
        {
            var user_delete = confirm("Are you sure you want to delete this row ?"), self = $(this);
            if ( user_delete == true )
            {
                $.ajax({
                type: "POST",
                url: '/users/deletefromvalvoline',
                data: 'id='+id,
                    success: function (respData){
                        alert('Deleted successfully');

                        window.location.reload();
                    }
                });
        }
}





</script>
@stop