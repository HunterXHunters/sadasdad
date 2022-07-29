@extends('layouts.default')
@extends('layouts.header')
@extends('layouts.sideview')
<div align="center" id="loading" style="text-align:center; z-index:9999;position:absolute;background:rgba(0,0,0,0.3);height:709px; width:1261px; display:none;" ><img src="/img/loading.gif" >    </div>
@section('content')

<style>
    .btn {
        margin-left: 10px !important;
    }
</style>
<!-- Page content -->
<?php if (isset($error_message))
{ ?>
    <div>
        <span><?php echo $formData['error_message']; ?></span>
    </div>
    <?php } ?>

<link href="http://cdn.rawgit.com/davidstutz/bootstrap-multiselect/master/dist/css/bootstrap-multiselect.css"
    rel="stylesheet" type="text/css" />


        <div class="box">
    <div class="box-header">
      <h3 class="box-title"><i class="fa fa-th"></i><strong>  Label Template  </strong> Config </h3>
    </div>

            <div class="col-sm-12">
                <div class="tile-body nopadding">
                    {{ Form::open(array('url' => '/product/getproduct_loactionmapping_layout', 'id' => 'getproduct_loactionmapping', 'files'=>'true' )) }}
                   
 @if(!empty($customers))
  
    <div class="form-group col-sm-5">
      <label for="exampleInputEmail">Choose Customer</label>
      <div id="selectbox">
      <!-- {{ Form::open(array('url' => '/dashboard/iotBankReport','method'=>'POST','id'=>'IOTBFrm','role'=>"form",'name'=>'IOTBFrm')) }}
       -->  
       <select class="form-control requiredDropdown multiSelect" id="customer_id" name="customer_id"       parsley-trigger="change" parsley-required="true" parsley-error-container="#selectbox" onchange="getproductsLocationTypesProductTypesCategories()" >
          <option  value="">Select Customer</option>
            @foreach($customers as $customer)  
              <option value="{{$customer->customer_id}}" >{{$customer->brand_name}}</option>
            @endforeach
        </select>
        <!-- {{Form::close()}} -->
      </div>
    </div>
    <div class="clr"></div>
    @else
    <input type = "hidden" name ="customer_id" id ="customer_id" value= "{{$cust_id}}">
    @endif

                <div class="form-group col-sm-12">
                    <style>
                        .modal-content{
                            width: 75% !important;
                            margin: auto !important;
                        }
                        .btn {
                            margin-left: 0px !important;
                        }
                        .btn-group{
                            width: 550px !important;
                        }
                        .btn-group .multiselect{
                            width: 550px !important;
                        } 
                    </style>
                <label for="exampleInputEmail"><b>Vendor</b></label>
                <div class="input-group ">
                    <div id="selectbox">
                      <!--   <select class="multiSelect" id="product_id" name="product_id[]"  multiple="multiple">
                        @foreach($products as $key=>$result)
                            <option  value="{{$result->product_id}}">{{$result->name}}</option>
                        @endforeach
                        </select> -->
                         <select multiple="multiple" class="multiSelect getLayouts" id="ser_location_id" name="location_id[]" >
                            @foreach($locations as $key=>$result)
                                <option  value="{{$result->location_id}}">{{$result->location_name}}-{{$result->erp_code}}</option>
                            @endforeach
                        </select>
                         <button type="submit" class="gridrefresh btn btn-primary" >Search</button>
                    </div>
                </div>
                </div>
                
               <!--  <div class="col-sm-3 pull-right">
                    <br>
                   
                    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#bulk_update">Add Pdf Layout</button>
                </div>
 -->
                <div class="clearfix"></div>

                    {{ Form::close() }}
                    <br>
                    <br>
                    <div id="jqxgrid"></div>

                    <!-- Modal -->
                    <br><br>
                    <div class="thumbnail">
                        <h4><b>Legend</b></h4>
                        <ul>
                            @foreach($layouts as $key=>$result)
                            <li>Type {{$result->template}}</li>
                            @endforeach
                        </Ul>
                    </div>


                    <!-- /.modal -->
                </div>
            </div>
        </div>

       
        </div>
        </div>
        </div>
<style>
    .addlayout .col-xs-12{
        line-height: 35px;
    }
    .addlayout .btn-group,.addlayout button{
        width: 100%;
    }
</style>

        <div class="modal fade addlayout" id="bulk_update" tabindex="-1" role="dialog" aria-labelledby="basicvalCode" aria-hidden="true">
            <div class="modal-dialog wide">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true" style="width: 12px;">×</button>
                        <h4 class="modal-title" id="basicvalCode">Import Product mapping</h4>
                    </div>
                    <div class="modal-body" style="min-height:120px; ">
                        <div id="update_import_product_message"></div>
                        {{ Form::open(array('url' => '/product/saveProductLocMaping_layout', 'id' => 'bulk_update_products', 'files'=>'true' )) }} {{ Form::hidden('_method','POST') }}
                        <div>
                        <div class="form-group">
                             <div class="col-xs-6 hidden">
                                    <div class="col-xs-2">
                                   <label for="exampleInputEmail">Location</label>
                                    </div><div class="col-xs-10">
                                    <div class="input-group ">
                                        <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
                                        <div id="selectbox">
                                            <select class="multiSelect getLayouts" id="location_id" name="location_id" >
                                                <option value="">Select Location</option>
                                            @foreach($locations as $key=>$result)
                                                <option  value="{{$result->location_id}}">{{$result->location_name}}</option>
                                            @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    </div>
                            </div>

                            <div class="clearfix">

                                  <div class="col-xs-9">
                                <div class="">
                                   <label for="exampleInputEmail">Product</label></div>
                                   <div class="">
                                    <div class="input-group ">
                                        <div id="selectbox">
                                            <select class=" getLayouts "   multiple="multiple" id="get_product_id" name="product_id[]"  >
                                                 <option value="">Select Product</option>
                                            </select>
                                        </div>
                                    </div>
                                    </div>
                            </div>
                            <div class="clearfix">
                            <div class="col-xs-9">
                                <div class="">
                                   <label for="exampleInputEmail">Layout</label></div>
                                   <div class="">
                                    <div class="input-group ">
                                        <div id="selectbox">
                                            <select class="multiSelect"  multiple="multiple" id="layout_id" name="layout_id[]" >
                                            @foreach($layouts as $key=>$result)
                                                <option  value="{{$result->template_id}}">Type {{$result->template}}</option>
                                            @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    </div>
                            </div>

                            <div class="col-xs-3"><br>
                                <input type="submit"  class="btn btn-primary pull-right" name="submit" value="Update" style="margin-right: 6px;    margin-top: 12px;">
                            </div>
                        </div>
                        </div>
                        {{ Form::close() }}
                    </div>
                </div>
                <!-- /.modal-content -->
            </div>
            <!-- /.modal-dialog -->
        </div>
        <!-- /.modal -->

        </div>
        </div>
        </div>

        @stop
        @section('style') 
        {{HTML::style('jqwidgets/styles/jqx.base.css')}}
        {{HTML::style('css/bootstrap-select.css')}}
         @stop 

         @section('script') 
        {{HTML::script('js/plugins/bootstrap-select/bootstrap-select.js')}}
        {{HTML::script('jqwidgets/jqxcore.js')}} {{HTML::script('jqwidgets/jqxbuttons.js')}} {{HTML::script('jqwidgets/jqxscrollbar.js')}} {{HTML::script('jqwidgets/jqxmenu.js')}} {{HTML::script('jqwidgets/custom_jqxgrid.js')}} {{HTML::script('jqwidgets/jqxgrid.selection.js')}} {{HTML::script('jqwidgets/jqxgrid.columnsresize.js')}} {{HTML::script('jqwidgets/jqxdata.js')}} {{HTML::script('scripts/demos.js')}} {{HTML::script('jqwidgets/jqxlistbox.js')}} {{HTML::script('jqwidgets/jqxdropdownlist.js')}} {{HTML::script('jqwidgets/jqxgrid.pager.js')}} {{HTML::script('jqwidgets/jqxgrid.sort.js')}} {{HTML::script('jqwidgets/jqxgrid.filter.js')}} {{HTML::script('jqwidgets/jqxgrid.storage.js')}} {{HTML::script('jqwidgets/jqxgrid.columnsreorder.js')}} {{HTML::script('jqwidgets/jqxpanel.js')}} {{HTML::script('jqwidgets/jqxcheckbox.js')}}

        <script src="http://cdn.rawgit.com/davidstutz/bootstrap-multiselect/master/dist/js/bootstrap-multiselect.js" type="text/javascript"></script>
        <script type="text/javascript">
 


 
function config($pid,$lid){

     const selectMembers = $("#get_product_id");
    selectMembers.empty();

     //$('#get_product_id').empty().append('<option selected="selected" value="">Select Product</option>');
        var formData = new FormData();
        formData.append("vendor", $lid);
        $.ajax({
            url: '/qrcode/getProductsByLocation',
            type: 'POST',
            data: formData,
            async: false,
            success: function(data) {

                $.each(data, function(key, value) { 
                    console.log("key",key,"vak", value);
                  selectMembers
                  .append($("<option></option>")
                  .attr("value",value.product_id)
                  .text(value.name+"-"+value.material_code));
                });    
            
                
                $('#get_product_id').val($pid);
                selectMembers.multiselect({
                //columns: 1,
                nonSelectedText :'Select One',
                includeSelectAllOption: true,
                enableFiltering:true,
                numberDisplayed: 1,
                enableCaseInsensitiveFiltering: true,
                maxHeight: 300
                }); 
                selectMembers.multiselect('refresh');
              /*    
                $('#get_product_id').multiselect('refresh');*/  
                    
            },
            cache: false,
            contentType: false,
            processData: false
          });

    $('#bulk_update').modal('show');    
    $('#location_id').val($lid);
  
    //$("#location_id").multiselect("refresh");
    checkLayouts();
}
       
       
 /*$('.getLayouts').change(function(event) {
                event.preventDefault();
               checkLayouts();
            });*/


function checkLayouts(){
     var get_product_id=$('#get_product_id').val();
        var location_id=$('#location_id').val();
        if(get_product_id!='' && location_id!=''){
              $("#loading").show();
            var formData = new FormData();

            formData.append("product_id", get_product_id);
            formData.append("location_id", location_id);
               $.ajax({
                    url: 'getProductLocMaping_layout',
                    type: 'POST',
                    data: formData,
                    success: function(data) {

                        var options = data.layout; 
                        var dataarray = options.split(",");
                        console.log(dataarray);
                        $("#layout_id").val(dataarray);
                        $("#layout_id").multiselect("refresh");
                        $("#loading").hide();
                        console.log(data);

                    },
                    cache: false,
                    contentType: false,
                    processData: false
                });  
        }
 } 
    
  $('.multiSelect').multiselect({
        //columns: 1,
        nonSelectedText :'Select One',
        includeSelectAllOption: true,
        enableFiltering:true,
        numberDisplayed: 1,
        enableCaseInsensitiveFiltering: true,
        maxHeight: 300
    }); 
      

$(document).ready(function() {


var url = "getproduct_loactionmapping_layout";
var source =
    {
        localdata: [],
        datafields: [{
                        name: 'name',
                        type: 'string',
                        cellsalign: 'left'
                    }, {
                        name: 'material_code',
                        type: 'string'
                    }, {
                        name: 'location_name',
                        type: 'string'
                    }, {
                        name: 'erp_code',
                        type: 'string'
                    }, {
                        name: 'layout_id',
                        type: 'string'
                    }],
        datatype: "json"
    };
var dataAdapter = new $.jqx.dataAdapter(source);
var columns = [{
                text: 'Material Name',
                    datafield: 'name',
                     cellsalign: 'left',
                    width: "30%"
                }, {
                    text: 'Material Code',
                    datafield: 'material_code',
                    width: "12%"
                }, {
                    text: 'Location',
                    datafield: 'location_name',
                    width: "28%"
                }, {
                    text: 'Location ERP',
                    datafield: 'erp_code',
                    width: "15%"
                }, {
                    text: 'Layout Ids',
                    datafield: 'layout_id',
                    width: "15%"
                }];

$("#jqxgrid").jqxGrid(
{
    width: "100%",
    source: dataAdapter,
     rowsheight: 30,
    columns: columns,
    selectionmode: 'multiplerowsextended',
    sortable: true,
    pageable: true,
    autoheight: true,
    //autowidth: true,
    autoloadstate: false,
    autosavestate: false,
    columnsresize: true,
    columnsreorder: true,
    showfilterrow: true,
    filterable: true,
});
$('#getproduct_loactionmapping').submit(function(event) {
    event.preventDefault();
  //    $form = $(this);
    url = $(this).attr('action');
    var formData = new FormData($(this)[0]);
    $.ajax({
    url: url,
    type: 'POST',
    data: formData,
    async: false,
    success: function(data) {
        source.localdata = data;
        $("#jqxgrid").jqxGrid('updatebounddata', 'cells');
    },
    cache: false,
    contentType: false,
    processData: false
    });
});

                $('#bulkupdateerrorlog').hide();
                $('#add_products_form_excel').bootstrapValidator({
                    //        live: 'disabled',
                    message: 'This value is not valid',
                    feedbackIcons: {
                        valid: 'glyphicon glyphicon-ok',
                        invalid: 'glyphicon glyphicon-remove',
                        validating: 'glyphicon glyphicon-refresh'
                    },
                    fields: {
                        manufacturerID: {
                            validators: {
                                callback: {
                                    message: 'Please choose product category',
                                    callback: function(value, validator, $field) {
                                        return (value != 0);
                                    }
                                },
                                notEmpty: {
                                    message: 'Name cannot be empty.'
                                }
                            }
                        },
                        files: {
                            validators: {
                                callback: {
                                    message: 'The selected file is not valid',
                                    callback: function(value, validator, $field) {
                                        console.log($field);
                                        var exts = ['csv'];
                                        // split file name at dot
                                        var get_ext = value.split('.');
                                        // reverse name to check extension
                                        get_ext = get_ext.reverse();
                                        // check file type is valid as given in ‘exts’ array
                                        if ($.inArray(get_ext[0].toLowerCase(), exts) > -1) {
                                            return true;
                                        } else {
                                            return false;
                                        }
                                    }
                                },
                            }
                        }
                    }
                }).on('success.form.bv', function(event) {
                    event.preventDefault();
                    $('#add_product_excel_button').prop('disabled', true);
                    $form = $(this);
                    url = $form.attr('action');
                    var formData = new FormData($(this)[0]);

                    $.ajax({
                        url: url,
                        type: 'POST',
                        data: formData,
                        async: false,
                        success: function(data) {
                            //$('#update_import_product_message').text(data);

                            alert(data);
                            $('.close').trigger('click');

                        },
                        cache: false,
                        contentType: false,
                        processData: false
                    });
                    $('#add_product_excel_button').prop('disabled', false);
                });

                $('#add_component_form_excel').bootstrapValidator({
                    //        live: 'disabled',
                    message: 'This value is not valid',
                    feedbackIcons: {
                        valid: 'glyphicon glyphicon-ok',
                        invalid: 'glyphicon glyphicon-remove',
                        validating: 'glyphicon glyphicon-refresh'
                    },
                    fields: {
                        manufacturerID: {
                            validators: {
                                callback: {
                                    message: 'Please choose product category',
                                    callback: function(value, validator, $field) {
                                        return (value != 0);
                                    }
                                },
                                notEmpty: {
                                    message: 'Name cannot be empty.'
                                }
                            }
                        },
                        files: {
                            validators: {
                                callback: {
                                    message: 'The selected file is not valid',
                                    callback: function(value, validator, $field) {
                                        console.log($field);
                                        var exts = ['csv'];
                                        // split file name at dot
                                        var get_ext = value.split('.');
                                        // reverse name to check extension
                                        get_ext = get_ext.reverse();
                                        // check file type is valid as given in ‘exts’ array
                                        if ($.inArray(get_ext[0].toLowerCase(), exts) > -1) {
                                            return true;
                                        } else {
                                            return false;
                                        }
                                    }
                                },
                                notEmpty: {
                                    message: 'Please choose a CSV file.'
                                }
                            }
                        }
                    }
                }).on('success.form.bv', function(event) {
                    event.preventDefault();
                    $('#add_component_excel_button').prop('disabled', true);
                    $form = $(this);
                    url = $form.attr('action');
                    var formData = new FormData($(this)[0]);

                    $.ajax({
                        url: url,
                        type: 'POST',
                        data: formData,
                        async: false,
                        success: function(data) {
                            //$('#update_import_product_message').text(data);
                            //alert(data);
                            var error = "";
                            $.each(data.message, function(i, val) {
                                error = error + "\n" + val;
                            });
                            alert(error);                           
                           // $('.close').trigger('click');
                        },
                        cache: false,
                        contentType: false,
                        processData: false
                    });
                    $('#add_component_excel_button').prop('disabled', false);
                });




   /* $('#get_product_id').multiselect({
        //columns: 1,
        nonSelectedText :'Select One',
        includeSelectAllOption: true,
        enableFiltering:true,
        numberDisplayed: 1,
        enableCaseInsensitiveFiltering: true,
        maxHeight: 300
    }); */
      
 });





            $('#bulk_update_products').submit(function(event) {
                event.preventDefault();
              //  $("#loading").show();
               
                $form = $(this);
                url = $form.attr('action');
                var formData = new FormData($(this)[0]);
              

                $.ajax({
                    url: url,
                    type: 'POST',
                    data: formData,
                    success: function(data) {

                        //$('#update_import_product_message').text(data);
                        //alert(data);
                        if (data.status) {
                            $("#loading").hide();
                            alert(data.message);
                        } else {
                            $('#bulkupdateerrorlog').hide();
                            alert(data.message);
                            $("#loading").hide();
                        }
                        $('#bulk_update').modal('toggle'); 
                        $('#getproduct_loactionmapping').submit();
                       // location.reload();
                        //$('#bulk_update_products').data('bootstrapValidator').resetForm();
                        $('#bulk_update_products')[0].reset();
                    },
                    cache: false,
                    contentType: false,
                    processData: false
                });  
                  
                
            });


   
        </script>
        @stop