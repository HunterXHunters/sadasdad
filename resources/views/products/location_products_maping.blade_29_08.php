@extends('layouts.default')
@extends('layouts.header')
@extends('layouts.sideview')
<div align="center" id="loading" style="text-align:center; z-index:9999;position:absolute;background:rgba(0,0,0,0.3);height:709px; width:1261px; display:none;" ><img src="/img/loading.gif" >    </div>
@section('content')

<style>
    .btn {
        margin-left: 1px !important;
    }
</style>
<!-- Page content -->
<?php if ($message= Session::get('success'))
{ ?>
    <div>
        <span><?php echo $message; ?></span>
    </div>
    <?php } ?>

<link href="http://cdn.rawgit.com/davidstutz/bootstrap-multiselect/master/dist/css/bootstrap-multiselect.css"
    rel="stylesheet" type="text/css" />
<script src="http://cdn.rawgit.com/davidstutz/bootstrap-multiselect/master/dist/js/bootstrap-multiselect.js"
    type="text/javascript"></script>

        <div class="box">

            <div class="box-header">
                <h3 class="box-title"><strong>Manage </strong>Products-Locations</h3>
            </div>
            <div>
                
            </div>
            <div class="box-body">
                @if($errors[0]=="")
          <div></div>
          @else
          <div class="alert <?=$errors[0]?'alert-success':'alert-danger'?>">
          <a href="/products/product_location_mapping" class="close" data-dismiss="alert" aria-label="close"  id= "close"title="close">×</a>
          <?php
           foreach ($errors as $key => $value){
             if($key==0) continue;
             echo '<p>'.$value.'</p>';
          }
          ?>
          </div>
          @endif
            
            <div class="col-sm-12">
                <div class="tile-body nopadding">
                    {{ Form::open(array('url' => '/products/getproduct_loactionmapping', 'id' => 'getproduct_loactionmapping', 'files'=>'true' )) }}
                   
                @if(!empty($customers))
  
    <div class="form-group col-sm-5">
      <label for="exampleInputEmail">Choose Customer</label>
      <div id="selectbox">
      <!-- {{ Form::open(array('url' => '/dashboard/iotBankReport','method'=>'POST','id'=>'IOTBFrm','role'=>"form",'name'=>'IOTBFrm')) }}
       -->  <select class="form-control requiredDropdown" id="customer_id" name="customer_id"       parsley-trigger="change" parsley-required="true" parsley-error-container="#selectbox" onchange="getproductsLocationTypesProductTypesCategories()" >
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

                <div class="form-group col-sm-4">
                <label for="exampleInputEmail">Location Type</label>
                <div class="input-group ">
                    <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
                    <div id="selectbox">
                        <select class="list-unstyled selectpicker" data-live-search="true" id="location_type" name="location_type"
                                parsley-trigger="change" parsley-required="true" parsley-error-container="#selectbox" onchange="changeLocations()">
                            <option  value="">Select Location Type</option>
                            @foreach($location_types as $key=>$result)
                                <option  value="{{$result->location_type_id}}">{{$result->location_type_name}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                </div>


                <div class="form-group col-sm-4">
                <label for="exampleInputEmail">Location</label>
                <div class="input-group ">
                    <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
                    <div id="selectbox">
                        <select class="" multiple="multiple" id="location_id" name="location_id[]"  onchange = "changeStorageLocations()">

                            <option  value="0">Select Location</option>
                            
                            
                        </select>
                    </div>
                </div>
                </div>

                <div class="form-group col-sm-4">
                <label for="exampleInputEmail">Product Group</label>
                <div class="input-group ">
                    <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
                    <div id="selectbox">
                        <select class="list-unstyled selectpicker" data-live-search="true" id="product_group" name="product_group"
                                parsley-trigger="change" parsley-required="true" parsley-error-container="#selectbox" onchange="changeProducts();">
                            <option  value="">Select Product Group</option>
                            @foreach($product_groups as $key=>$result)
                                <option  value="{{$result->group_id}}">{{$result->name}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                </div>

                <div class="form-group col-sm-6">
                <label for="exampleInputEmail">Product</label>
                <div class="input-group ">
                    <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
                    <div id="selectbox">
                        <select class="" id="product_id" name="product_id[]"  multiple="multiple">
                            
                                @foreach($products as $key=>$result)
                                    <option  value="{{$result->product_id}}">{{$result->name}}</option>
                                @endforeach
                        </select>
                    </div>
                </div>
                </div>
                
                <div class="col-sm-6 pull-right">
                    <br>
                    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#maplocation">Map</button>
                    <button type="submit" class="gridrefresh btn btn-primary" >Search</button>
                    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#bulk_update"> Import</button>
                    <button type="button" class="btn btn-primary" onclick="deletefromGrid();">Delete</button>
                </div>

                <div class="clearfix"></div>

                    {{ Form::close() }}
                    <br>
                    <br>
                    <div id="jqxgrid"></div>

                    <!-- Modal -->
                    
                    <!-- /.modal -->
                </div>
            </div>
        </div>
</div>
       
        </div>
        </div>
        </div>

        <div class="modal fade" id="bulk_update" tabindex="-1" role="dialog" aria-labelledby="basicvalCode" aria-hidden="true">
            <div class="modal-dialog wide">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                        <h4 class="modal-title" id="basicvalCode">Import Product mapping</h4>
                    </div>
                    <div class="modal-body" style="min-height:120px; ">
                        <div id="update_import_product_message"></div>
                        {{ Form::open(array('url' => '/products/getProductLocMaping', 'id' => 'bulk_update_products', 'files'=>'true' )) }} {{ Form::hidden('_method','POST') }}
                        
                        <div>
                        <div class="form-group">
                            <div class="col-xs-9">
                                <input type="file" class="form-control" name="files" >
                                <br>
                                <a href="/download/products-locations.xlsx" download>Sample File Download</a>
                            </div>
                            <div class="col-xs-3">
                                <button class="btn btn-primary" type="submit" >Upload</button>
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

        <!-- product location mapping modal start  -->
                        <form method="post" action="/products/mapProduct">

        <div class="modal" id="maplocation">
            <div class="modal-dialog" >
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h3 class="modal-title">Map  Product</h3>
                    </div>
                    <div class="modal-body">
                        
                        <div class="row">
                            <div class="form-group col-md-4" >
                                <label style="font-weight: bold;">Product</label>
                                <br>
                                <select class="form-control " id="prod_id" name="prod_id[]" multiple="multiple" required="required">
                                     <option value="">Please select </option>  
                                @foreach($products as $product)
                                <option value="{{$product->product_id}}"> {{$product->name}}</option>
                                @endforeach
                                </select>
                            </div>

                            <div class="form-group col-md-4" >
                                <label style="font-weight: bold;">Location</label>
                                <br>
                                <select class="form-control" id="loca_id" name="loca_id[]" multiple="multiple" required="required">
                                <!-- <option value="">Please select </option>     -->
                                    @foreach($locations as $location)
                                <option value="{{$location->location_id}}"> {{$location->location_name}}</option>
                                @endforeach
                                </select>
                            </div>      
                        
                            <br>
                        <div class="form-group col-sm-4">
                            <button  class="btn btn-primary" type="submit">Map</button>
                        </div>
                   </div>

                    </div>

                </div>
            </div>
        </div>
</form>


               

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
        <script type="text/javascript">
 
$('#prod_id').multiselect({
                nonSelectedText :'Select Products',
               includeSelectAllOption: true,
                       enableFiltering:true,
                        numberDisplayed: 2,
                 enableCaseInsensitiveFiltering: true,
                 maxHeight: 300,

             });
        
        $('#loca_id').multiselect({
                nonSelectedText :'Select Locations',
               includeSelectAllOption: true,
                       enableFiltering:true,
                        numberDisplayed: 2,
                 enableCaseInsensitiveFiltering: true,
                 maxHeight: 300
             });
    
        function changeLocations(){
        var location_type=$('#location_type').val();
        var cust = $('#customer_id').val();
        $('#location_id').multiselect('destroy');
        $('#location_id').empty();
        if(location_type== 0 || location_type == ""){
            $('#location_id').empty();
            var opt="";
            //opt=new Option('Select Location',0);
            //$('#location_id').append(opt);
             //$('#location_id').selectpicker('refresh');
             $('#location_id').multiselect({
                nonSelectedText :'Select Locations',
               includeSelectAllOption: true,
                       enableFiltering:true,
                        numberDisplayed: 2,
                 enableCaseInsensitiveFiltering: true,
                 maxHeight: 300
             });
        }
        else{
            $.ajax({
                url: "/dashboard/getLocations", //This is the page where you will handle your SQL insert
                type: "GET",
                data: 'location_type_id=' +location_type+'&customer_id='+cust, //The data your sending to some-page.php
                success: function(response){
                    $('#location_id').multiselect('destroy');
                    $('#location_id').empty();
                    var opt='';
                    var select=$('#location_id');
                    console.log(response);
                    //opt=new Option('Select Location',0);
                    //select.append(opt);
                    $.each(response,function(key,value){
                        var opt=new Option(value.location_name,value.location_id);
                        select.append(opt);
                    });
                    //select.selectpicker('refresh');
                    $('#location_id').multiselect({
                nonSelectedText :'Select Locations',
               includeSelectAllOption: true,
                       enableFiltering:true,
                        numberDisplayed: 2,
                        enableCaseInsensitiveFiltering: true,
                        maxHeight: 300
             });

                    
                },
                error:function(){
                    // console.log("AJAX request was a failure");
                }
            });
        }

    }

function changeStorageLocations(){
console.log("hai");
        var location=$('#location_id').val();
        var cust = $('#customer_id').val();
        if(location== 0 || location == ""){
            $('#storage_location_id').empty();
            var opt="";
            opt=new Option('Select Storage Location');
            $('#storage_location_id').append(opt);
             $('#storage_location_id').selectpicker('refresh');
        }
        else{
            $.ajax({
                url: "/dashboard/getStorageLocations", //This is the page where you will handle your SQL insert
                type: "GET",
                data: 'location_id=' +location+'&customer_id='+cust, //The data your sending to some-page.php
                success: function(response){
                    //alert(response);
                    $('#storage_location_id').empty();
                    var opt='';
                    var select=$('#storage_location_id');
                    opt=new Option('Select Storage Location');
                    select.append(opt);
                    $.each(response,function(key,value){
                        var opt=new Option(value);
                        select.append(opt);
                    });
                    select.selectpicker('refresh');

                    
                },
                error:function(){
                    // console.log("AJAX request was a failure");
                }
            });
        }


    }


            $(document).ready(function() {

    $('#product_id').multiselect({
        //columns: 1,
        nonSelectedText :'Select Products',
        includeSelectAllOption: true,
        enableFiltering:true,
        numberDisplayed: 2,
        enableCaseInsensitiveFiltering: true,
        maxHeight: 300
    });
    $('#location_id').multiselect({
        //columns: 1,
        nonSelectedText :'Select Locations',
        includeSelectAllOption: true,
        enableFiltering:true,
        numberDisplayed: 2,
        enableCaseInsensitiveFiltering: true,
        maxHeight: 300
    });
    // $('#loca_id').multiselect({
    //     //columns: 1,
    //      nonSelectedText :'Select Locations',
    //     includeSelectAllOption: true,
    //     enableFiltering:true,
    //     numberDisplayed: 2,
    //     enableCaseInsensitiveFiltering: true,
    //     maxHeight: 300
    // });

var url = "getproduct_loactionmapping";
var source =
    {
        localdata: [],
        datafields: [{
                        name: 'name',
                        type: 'string',
                        cellsalign: 'center'
                    }, {
                        name: 'material_code',
                        type: 'string'
                    }, {
                        name: 'location_name',
                        type: 'string'
                    }, {
                        name: 'erp_code',
                        type: 'string'
                    },{
                        name: 'chk',
                        type: 'string'
                    }],
        datatype: "json"
    };
var dataAdapter = new $.jqx.dataAdapter(source);
var columns = [{
                text: 'Material Name',
                    datafield: 'name',
                    cellsalign: 'center',
                    width: "20%"
                }, {
                    text: 'Material Code',
                    datafield: 'material_code',
                    width: "15%"
                }, {
                    text: 'Location',
                    datafield: 'location_name',
                    width: "25%"
                }, {
                    text: 'Location ERP',
                    datafield: 'erp_code',
                    width: "20%"
                },{
                    text: 'Actions',
                    datafield: 'chk',
                    width: "20%"
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
 function changeProducts(){
            var product_group=$('#product_group').val();
            var category_id = $('#category').val();
            var cust = $('#customer_id').val();

            // if((product_group == "" || product_group == 0) && (category_id == "" || category_id == 0)){
            //     $('#product_id').empty();
            //     var b=new Option('Select Product',0);
            //     $('#product_id').append(b);
            //      $('#product_id').selectpicker('refresh');
            // }
           // else{
                 $.ajax({
                 url: "/dashboard/getProducts", //This is the page where you will handle your SQL insert
                type: "GET",
                data: 'product_group_id=' +product_group+'&category_id='+category_id+'&customer_id='+cust, //The data your sending to some-page.php
                success: function(response){
                    $('#product_id').multiselect('destroy');
                    $('#product_id').empty();
                    var opt='';
                    var select=$('#product_id');
                   // opt=new Option('Select Product',0);
                    //select.append(opt);
                    $.each(response,function(key,value){
                        var opt=new Option(value,key);
                        select.append(opt);
                    });
                    select.multiselect({
                            nonSelectedText :'Select Products',
                               includeSelectAllOption: true,
                                       enableFiltering:true,
                                        numberDisplayed: 2,
                        enableCaseInsensitiveFiltering: true,
                        maxHeight: 300
                    });

                    
                },
                error:function(){
                    // console.log("AJAX request was a failure");
                }

            });
          //  }           
    }

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
                        //     files: {
                        //     validators: {
                        //         notEmpty: {
                        //             message: 'Please choose a file to upload'
                        //         },
                        //         file: {
                        //             extension: 'csv',
                        //             type: 'application/csv',
                        //             //maxSize: 2 * 1024 * 1024,
                        //             message: 'The file must be in .csv format.'
                        //         }
                        //     }
                        // }
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
                            // $('#add_component_form_excel')[0].reset();
                            //$('#add_component_form_excel').data('bootstrapValidator').resetForm();
                            //$('#add_component_form_excel')[0].reset();
                            $('.close').trigger('click');
                        },
                        cache: false,
                        contentType: false,
                        processData: false
                    });
                    $('#add_component_excel_button').prop('disabled', false);
                });

/*                var url = "getproduct_loactionmapping";
                // prepare the data
                var source = {
                    datatype: "json",
                    datafields: [{
                        name: 'name',
                        type: 'string',
                        cellsalign: 'center'
                    }, {
                        name: 'material_code',
                        type: 'string'
                    }, {
                        name: 'location_name',
                        type: 'string'
                    }, {
                        name: 'erp_code',
                        type: 'string'
                    }],
                    id: 'product_id',
                    url: url,
                    pager: function(pagenum, pagesize, oldpagenum) {
                        // callback called when a page or page size is changed.
                    }
                };


               var dataAdapter = new $.jqx.dataAdapter(source);
                $("#jqxgrid").jqxGrid({
                    width: "100%",
                    source: source,
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
                    columns: [{
                        text: 'Material Name',
                        datafield: 'name',
                        cellsalign: 'center',
                        width: "40%"
                    }, {
                        text: 'Material Code',
                        datafield: 'material_code',
                        width: "15%"
                    }, {
                        text: 'Location',
                        datafield: 'location_name',
                        width: "25%"
                    }, {
                        text: 'Location ERP',
                        datafield: 'erp_code',
                        width: "20%"
                    }]
                });*/
            });

            function deleteEntityType(product_id) {
                var deleteproduct = confirm("Are you sure you want to Delete ?"),
                    self = $(this);
                if (deleteproduct == true) {
                    $.ajax({
                        data: '',
                        type: 'GET',
                        datatype: "JSON",
                        url: '/products/deleteproduct/' + product_id,
                        success: function(resp) {
                            if (resp.message)
                                alert(resp.message);
                            if (resp.status == true) {
                                self.parents('td').remove();
                                location.reload();
                            }

                        },
                        error: function(error) {
                            console.log(error.responseText);
                        },
                        complete: function() {

                        }
                    });
                }
            }

            function restoreEntityType(product_id) {
                var restoreProduct = confirm("Are you sure you want to restore it ?"),
                    self = $(this);
                if (restoreProduct == true) {
                    $.ajax({
                        data: '',
                        type: 'GET',
                        datatype: "JSON",
                        url: '/products/restoreproduct/' + product_id,
                        success: function(resp) {
                            if (resp.message)
                                alert(resp.message);
                            if (resp.status == true) {
                                self.parents('td').remove();
                                location.reload();
                            }

                        },
                        error: function(error) {
                            console.log(error.responseText);
                        },
                        complete: function() {

                        }
                    });
                }
            }
            $('#add_products_form_erp').submit(function(event) {
                event.preventDefault();
                $('#add_product_erp_button').prop('disabled', true);
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
                $('#add_product_erp_button').prop('disabled', false);
            });
          
         
            $('#bulk_update_products').submit(function(event) {
                
                event.preventDefault();
                $("#loading").show();
               
               // $('#bulk_update_products_button').prop('disabled', true);
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
                            var error = false;
                            if (data.hasOwnProperty("error_log_link") & data.error_log_link != "") {
                                error = true;
                            }
                            if (error) {
                                $('#bulkupdateerrorlog').attr('href', data.error_log_link);
                                $('#bulkupdateerrorlog').show();
                                alert(data.message + ' ,\nSuccessfully Updated records: ' + data.sucess_records + ',\n Existing Records:' + data.existing_records + '\n Falied Records:' + data.failed_records + '.\n Please  click on the below link to download the error Log');
                                $("#loading").hide();
                            } else {
                                alert(data.message + ' Successfully Updated records: ' + data.sucess_records + ',\n Existing Records:' + data.existing_records + '\n Falied Records:' + data.failed_records);
                                $('#bulkupdateerrorlog').hide();
                                $("#loading").hide();

                                $('.close').trigger('click');
                            }

                        } else {
                            $('#bulkupdateerrorlog').hide();
                            alert(data.message);
                            $("#loading").hide();
                        }
                        //$('#bulk_update_products').data('bootstrapValidator').resetForm();
                        $('#bulk_update_products')[0].reset();
                    },
                    cache: false,
                    contentType: false,
                    processData: false
                });  
                
            });


function deletefromGrid(){
    var selected = [];
    $("input[name='chk[]']").each( function () {
       if($(this).val() != 'on') {
        if($(this).prop('checked') == true){
            selected.push($(this).val());
        }
       }
        });

    if(selected == ""){
        alert("please select any option");
        return false;
    };
    var delete_rows = confirm("Are you sure you want to delete this Data ?"), self = $(this);
    if ( delete_rows == true ){
       $.ajax({
            data: {grid_id:selected},
            type: "POST",
            url: '/products/deleteFromgrid',
            success: function (data)
            {
            alert("Deleted successfully");
      window.location.reload();
            }
    });
   }
 }
         </script>
        @stop