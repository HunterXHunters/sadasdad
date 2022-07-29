@extends('layouts.default')
@extends('layouts.header')
@extends('layouts.sideview')

@section('content')


 @if (Session::has('message'))
     <div class="flash alert">
         <p>{{ Session::get('message') }}</p>
     </div>
     @endif

<div class="box">

<div class="box-header">
<h3 class="box-title"><strong>Warehouse </strong>List</h3>
<!-- <span style="float:right;"><a href="entitytypes/create"><i class="fa fa-plus-circle"></i> <span>Add</span></a></span> -->
 @if (Session::has('flash_message'))            
            <div class="alert alert-info">{{ Session::get('flash_message') }}</div>
            @endif
</div>
<!--Added for Excel Import-->
        <div class="form-group pull-left" style="margin-left:15px;">
            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#entities_add_excel"> 
                Import from CSV 
            </button>
        </div>
<!--Added for Excel Import-->
<div class="modal" id="loading" style="display: none; vertical-align:middle; text-align:center;z-index:99999;">
    <div class="center">
        <!-- <img alt="" src="loader.gif" /> -->
        <p style="text-align:center; vertical-align:middle;padding-top:20%;"><img src="/img/spinner.gif" alt="Loading..." /></p>
    </div>
</div>
<div class="col-sm-12">
                 <div class="tile-body nopadding">                  
                    <div id="treeGrid"  style="width:100% !important;"></div>
                     <button data-toggle="modal" id="addEntity" class="btn btn-default" data-target="#basicvalCodeModal" style="display: none" data-url="{{URL::asset('entities/create')}}"></button>
                     <button data-toggle="modal" id="editEntity" class="btn btn-default" data-target="#basicvalCodeModal" style="display: none" data-url="{{URL::asset('entities/edit')}}"></button>
                    </div>
                </div>
</div>

<div class="modal fade" id="basicvalCodeModal" tabindex="-1" role="dialog" aria-labelledby="basicvalCode" aria-hidden="true">
                      <div class="modal-dialog wide">
                        <div class="modal-content">
                          <div class="modal-header">
                            <button type="button" class="close" id="close_it_now" data-dismiss="modal" aria-hidden="true">X</button>
                            <h4 class="modal-title" id="basicvalCode">Add Entity</h4>
                          </div>
                            <div class="modal-body" id="entitiesDiv">
                            </div>
                        </div><!-- /.modal-content -->
                      </div><!-- /.modal-dialog -->
                    </div><!-- /.modal -->

<!-- Add Entity Types from Excel -->
    <!-- Modal -->
    <div class="modal fade" id="entities_add_excel" tabindex="-1" role="dialog" aria-labelledby="basicvalCode" aria-hidden="true">
        <div class="modal-dialog wide">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                    <h4 class="modal-title" id="basicvalCode">Add Entities from CSV</h4>
                </div>
                <div class="modal-body">
                    <div id="update_import_locations_message"></div>
                    {{ Form::open(array('url' => 'entities/saveentitiesfromexcel', 'id' => 'add_entities_from_excel', 'files'=>'true' )) }}
                    {{ Form::hidden('_method','POST') }}
                    @if(empty($manufacturerId))
                    <div class="row">
                        <div class="form-group col-sm-6">
                            <label for="exampleInputEmail">Manufacturer*</label>
                            <div class="input-group ">
                                <span class="input-group-addon addon-red"><i class="fa fa-puzzle-piece"></i></span>
                                <select name="manufacturer_id" id="manufacturer_id" class="form-control" required>
                                    <option value="0">Please select..</option>
                                    @foreach($mfgDetails as $key=>$value)
                                    <option value="{{$value->org_id}}">{{$value->brand_name}}</option>
                                     @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    @endif
                      <div class="form-group col-sm-6"> <a href="/customer/download/WMSEntities"  style="margin-top:0px;"><i class="fa fa-download"></i> Download sample file </a></div>
                      <div class="form-group col-sm-6"> <a href="/customer/download/WMSEntitiesMasterdata"  style="margin-top:0px;"><i class="fa fa-download"></i> Download masterdata file </a></div>
                      <div class="form-group col-sm-6">                               
                          <input id="locations_fileupload" required type="file" name="files">     
                      </div> 
                        {{ Form::submit('Upload File', array('class' => 'btn btn-primary', 'id' => 'add_entities_excel_button')) }}
                      </div>
                    </div>
                    {{ Form::close() }}                    
                   <br><br>              
                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->    
<!-- Add Entity Types from Excel --> 
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
    {{HTML::style('css/bootstrap.min.css')}}
    {{HTML::style('css/bootstrapValidator.css')}}   
@stop

@section('script')
<!--     {{HTML::script('scripts/jquery-1.11.1.min.js')}} -->
    {{HTML::script('jqwidgets/jqxcore.js')}}
    {{HTML::script('jqwidgets/jqxdata.js')}}
    {{HTML::script('jqwidgets/jqxbuttons.js')}}
    {{HTML::script('jqwidgets/jqxscrollbar.js')}}
    {{HTML::script('jqwidgets/jqxdatatable.js')}}
    {{HTML::script('jqwidgets/jqxtreegrid.js')}}
    {{HTML::script('scripts/demos.js')}} 

<script type="text/javascript">
  // invoked when sending ajax request
  $(document).ajaxSend(function () {
      $("#loading").show();
  });

  // invoked when sending ajax completed
  $(document).ajaxComplete(function () {
      $("#loading").hide();
  });
$(document).ready(function () 
{
    ajaxCall();
});
    function ajaxCall()
    {            
            $.ajax(
            {
                url: "entities/getalldata",
                success: function(result)
                {
                    var employees = result;
                    // prepare the data
                    var source =
                    {
                        dataType: "json",
                        dataFields: [
                            { name: 'id', type: 'number' },
                            { name: 'entity_name', type: 'string' },
                            { name: 'entity_location', type: 'string' },
                            { name: 'entity_type_name', type: 'string' },
                            { name: 'capacity', type: 'number' },
                            { name: 'capacity_uom', type: 'string' },
                            { name: 'actions', type: 'varchar' },
                            { name: 'assign', type: 'varchar' },
                            { name: 'children', type: 'array' },
                            { name: 'expanded', type: 'bool' }
                        ],
                        hierarchy:
                        {
                            root: 'children'
                        },
                        id: 'id',
                        localData: employees
                    };
                    var dataAdapter = new $.jqx.dataAdapter(source);
                    // create Tree Grid
                    $("#treeGrid").jqxTreeGrid(
                    {
                        width: "100%",
                        source: dataAdapter,
                        sortable: true,
                        columns: [
                          { text: 'EntityName', dataField: 'entity_name', width: "20%" },
                          { text: 'Entity Location', dataField: 'entity_location', width: "15%" },
                          { text: 'Entity Type Name', dataField: 'entity_type_name', width: "15%" },
                          { text: 'Capacity', dataField: 'capacity', width: "12%" },
                          { text: 'Capacity UOM', dataField: 'capacity_uom', width: "12%" },
                          { text: 'Actions', dataField: 'actions', width: "18%" },
                          { text: 'Assign', dataField: 'assign', width: "8%" }
                        ]
                    });


                }
            });
    }
/*function deleteEntity(id)
{
    var decission = confirm("Are you sure you want to Delete.");
    if(decission==true)
        window.location.href='entities/delete/'+id;
}*/
function deleteEntity(id)
{
        var dec = confirm("Are you sure you want to Delete ?");
        if ( dec == true )
        $('#verifyUserPassword').modal('show');
        $('#verifyUserPassword button#cancel-btn').on('click',function(e){
            e.preventDefault();
            //console.log('clicked cancel');
            $('#verifyUserPassword').modal('hide');
        });
        $('#verifyUserPassword button#save-btn').off('click');
        $('#verifyUserPassword button#save-btn').on('click',function(e){
            e.preventDefault();
            //console.log('cliked submit');
            var userPassword = $.trim($('#verifyUserPassword input').val());
            if(userPassword == ''){
                alert('Field is required');
                return false
            } else
            $.ajax({
                url: 'entities/delete/'+id,
                data: 'password='+userPassword,
                type:'POST',
                success: function(result)
                {
/*                    alert(result);
                    alert(result.substr( 0, result.indexOf('-')));
                    alert(result.substr(result.indexOf('-')+1));*/
                    if(result == 1){
                        alert('Succesfully Deleted !!');
                        ajaxCall();
                        //location.reload();
                        //window.location.href = '/customer/editcustomer/'+manufacturerId;
                        $('#verifyUserPassword').modal('hide');
                    }else if(result.substr( 0, result.indexOf('-')) == 2)
                    {
                        alert('Cannot delete because bins : '+ result.substr(result.indexOf('-')+1) +' is assigned.');
                        $('#verifyUserPassword').modal('hide');
                    }
                    else
                    {
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

<script type="text/javascript">
function addEntity(id,id1,id2,id3,id4)
{

     $.get('entities/create/'+id +'/' +id1 +'/' +id2 +'/' +id3 +'/' +id4,function(response){ 
            $("#basicvalCode").html('Add Entity');
            
            $("#entitiesDiv").html(response);
            
            $("#addEntity").click();
        });
}

function addEntity1(id,id1,id2)
{

      $.get('entities/create1/'+id +'/' +id1 +'/' +id2,function(response){ 
            $("#basicvalCode").html('Add Entity');
            
            $("#entitiesDiv").html(response);
            
            $("#addEntity").click();
        });
}

$(document).ready(function(){
    window.setTimeout(function(){
        $(".alert").hide();
    },3000);
});
function editEntity(id)
{

      $.get('entities/edit/'+id ,function(response){ 
            $("#basicvalCode").html('Edit Entity');
            
            $("#entitiesDiv").html(response);
            
            $("#editEntity").click();
        });
}
$('#entities_add_excel').on('hide.bs.modal',function(){
    console.log('reset: #add_entities_from_excel');
    //alert('reset');
    $('#add_entities_from_excel').data('bootstrapValidator').resetForm();
    $('#add_entities_from_excel')[0].reset();
});
$(document).ready(function(){
    window.setTimeout(function(){
        $(".alert").hide();
    },3000);
});
function postData()
{
    console.log('we are in view');
    return;
}
/*$('#add_entities_from_excel').submit(function(event){
    event.preventDefault();
    $('#add_entities_excel_button').prop('disabled', true);
    $form = $(this);
    url = $form.attr('action');
    var formData = new FormData($(this)[0]);
    $.ajax({
        url: url,
        type: 'POST',
        data: formData,
        async: false,
        success: function (data) {
            alert(data['message']);
            $('.close').trigger('click');
        },
        cache: false,
        contentType: false,
        processData: false
    });
 }); */
$('#add_entities_from_excel').bootstrapValidator({
//        live: 'disabled',
    message: 'This value is not valid',
    feedbackIcons: {
        valid: 'glyphicon glyphicon-ok',
        invalid: 'glyphicon glyphicon-remove',
        validating: 'glyphicon glyphicon-refresh'
    },
    fields: {
        manufacturer_id: {
            validators: {
                callback: {
                    message: 'Please choose Manufacturer Name',
                    callback: function (value, validator, $field) {
                        // Get the selected options
                        var options = $('[id="manufacturer_id"]').val();
                        return (options != 0);
                    }
                },
                notEmpty: {
                    message: 'Please select Manufacturer.'
                }
            }
        }
    }
}).on('success.form.bv', function (event) {
    event.preventDefault();
    $('#add_entities_excel_button').prop('disabled', true);
    $form = $(this);
    url = $form.attr('action');
    var formData = new FormData($(this)[0]);
    $.ajax({
        url: url,
        type: 'POST',
        data: formData,
        async: false,
        success: function (data) {
            //alert(data);
            if(data['status'] == 0 ){
              alert(data['message']);
              $('#locations_fileupload').val(''); 
              $('#add_entities_from_excel')
                .data('bootstrapValidator').resetField($('#locations_fileupload'));             
            }else{
            alert(data['message']);
            $('.close').trigger('click');
            ajaxCall();
            }
        },
        cache: false,
        contentType: false,
        processData: false
    });
        });  
</script>  
@stop

@extends('layouts.footer')