@extends('layouts.default')
@extends('layouts.header')
@extends('layouts.sideview')
@section('content')
<style>
.btn{margin-left:10px !important;}

.fa-check {
    color: green;
}
.fa-times {
    color: red;
}
.fa-spinner {
    color: blue;
}
</style>
<!-- Page content -->
<?php if (isset($error_message))
{ ?>
    <div>
        <span><?php echo $formData['error_message']; ?></span>
    </div>
<?php } ?>

<button data-toggle="modal" id="addEntity" class="btn btn-default" data-target="#basicvalCodeModal" style="display: none" data-url="{{URL::asset('/products/preview')}}"></button>

<div class="modal fade" id="basicvalCodeModal" tabindex="-1" role="dialog" aria-labelledby="basicvalCode" aria-hidden="true">
    <div class="modal-dialog wide">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" id="close_preview" data-dismiss="modal" aria-hidden="true">X</button>
                <h4 class="modal-title" id="basicvalCode">Preview</h4>
            </div>
            <div class="modal-body" id="entitiesDiv">
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

        <div class="modal fade" id="basicvalCodeModal2" tabindex="-1" role="dialog" aria-labelledby="basicvalCode" aria-hidden="true">
          <div class="modal-dialog wide">
            <div class="modal-content">
              <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">X</button>
                <h4 class="modal-title" id="basicvalCode">Edit Product</h4>
              </div>
              <div class="modal-body">                         
                  <div class="modal-body" id="editProdDiv">
                  </div>
              </div>
            </div><!-- /.modal-content -->
          </div><!-- /.modal-dialog -->
        </div><!-- /.modal -->

<div class="box">
    <div class="box-header">
      <h3 class="box-title">Manage <strong>Products</strong></h3>
      <!-- <a href="pricemaster/add" class="pull-right"><i class="fa fa-user-plus"></i><span style="font-size:14px; padding-left:5px; ">Add Price</span></a> -->
      <button id="map"align="center"class="btn btn-primary" data-toggle="modal" data-target="#basicvalCodeModal" onclick="multiadd()">Mapping</button>
      <?php if(isset($allow_buttons['add']) && $allow_buttons['add'] == 1){ ?>
        <button class="btn btn-primary pull-right" data-toggle="modal" onclick="location.href = '/product/create';">Add Product</button>
      <?php } ?>
      <?php if(isset($allow_buttons['add_component']) && $allow_buttons['add_component'] == 1){ ?>
        <!-- <button class="btn btn-primary pull-right" data-toggle="modal" data-target="#component_add_excel">Import component products from CSV</button> -->
      <?php } ?>
      <?php if(isset($allow_buttons['import_csv']) && $allow_buttons['import_csv'] == 1){ ?>
        <button type="button" class="btn btn-primary pull-right" data-toggle="modal" data-target="#products_add_excel"> Import from CSV</button> 
      <?php } ?>
      <?php if(isset($allow_buttons['import_erp']) && $allow_buttons['import_erp'] == 1){ ?>
        <button type="button" class="btn btn-primary pull-right" data-toggle="modal" data-target="#products_add_erp"> Import from ERP</button> 
      <?php } ?>
	  <!-- <button type="button" class="btn btn-primary pull-right" data-toggle="modal" data-target="#bulk_update"> Bulk Update</button>  -->
    </div>

    <div class="col-sm-12">
        <div class="tile-body nopadding">                  
            <div id="jqxgrid"  style="width:100% !important;"></div>
             <button data-toggle="modal" id="editProduct" class="btn btn-default" data-target="#basicvalCodeModal2" style="display: none" data-url="{{URL::asset('product/editgdsproduct/')}}"></button>
        </div>
    </div>
</div>

 
    <!--<button class="btn btn-primary" data-toggle="modal" onclick="location.href = '/product/create';">Add Product</button>
    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#products_add_excel"> Import from Excel</button> -->
    
     <!--  -->
     

<!-- Modal -->
    <div class="modal fade" id="products_add_excel" tabindex="-1" role="dialog" aria-labelledby="basicvalCode" aria-hidden="true">
        <div class="modal-dialog wide">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                    <h4 class="modal-title" id="basicvalCode">Add products from excel</h4>
                </div>
                <div class="modal-body">
                    <div id="update_import_product_message"></div>
                    {{ Form::open(array('url' => '/product/saveproductsfromexcel', 'id' => 'add_products_form_excel', 'files'=>'true' )) }}
                    {{ Form::hidden('_method','POST') }}
                    <div class="row">
                        <div class="form-group col-sm-12">
                            <label class="col-sm-2 control-label" for="Manufacturers">Manufacturers*</label>
                            <div class="col-sm-10">
                                <div class="input-group input-group-sm">
                                    <div id="selectbox">
                                        <select class="form-control" name="manufacturerID" parsley-trigger="change" parsley-required="true" parsley-error-container="#selectbox">
                                            @foreach($manufacturers as $key => $value)
                                            <option value="{{ $key }}">{{ $value }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>                    
                    </div>
                    <div class="row">
                        <div class="form-group col-sm-8">
                            <span class="btn fileinput-button">                                  
                                <input id="product_fileupload" type="file" name="files" class="form-control">                                    
                            </span>
                            <a href="/customer/download/FG_Material_Codes" class="btn btn-large pull-right"><i class="icon-download-alt"> </i> Download sample file </a>
                        </div>
                    </div>
                    {{ Form::submit('Upload File', array('class' => 'btn btn-primary', 'id' => 'add_product_excel_button')) }}
                    {{ Form::close() }}
                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->
   
<!-- Modal -->
    <div class="modal fade" id="component_add_excel" tabindex="-1" role="dialog" aria-labelledby="basicvalCode" aria-hidden="true">
        <div class="modal-dialog wide">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                    <h4 class="modal-title" id="basicvalCode">Add products from excel</h4>
                </div>
                <div class="modal-body">
                    <div id="update_import_product_message"></div>
                    {{ Form::open(array('url' => '/product/saveproductcomponentsfromexcel', 'id' => 'add_component_form_excel', 'files'=>'true' )) }}
                    {{ Form::hidden('_method','POST') }}
                    <div class="row">
                        <div class="form-group col-sm-12">
                            <label class="col-sm-2 control-label" for="Manufacturers">Manufacturers*</label>
                            <div class="col-sm-10">
                                <div class="input-group input-group-sm">
                                    <div id="selectbox">
                                        <select class="form-control" name="manufacturerID" parsley-trigger="change" parsley-required="true" parsley-error-container="#selectbox">
                                            @foreach($manufacturers as $key => $value)
                                            <option value="{{ $key }}">{{ $value }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>                    
                    </div>
                    <div class="row">
                        <div class="form-group col-sm-8">
                            <span class="btn fileinput-button">                                  
                                <input id="product_fileupload" type="file" name="files" class="form-control">                                    
                            </span>
                            <a href="/customer/download/Component_Codes" class="btn btn-large pull-right"><i class="icon-download-alt"> </i> Download sample file </a>
                        </div>
                    </div>
                    {{ Form::submit('Upload File', array('class' => 'btn btn-primary', 'id' => 'add_component_excel_button')) }}
                    {{ Form::close() }}
                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->
    </div>
    </div>
</div>

    <div class="modal fade" id="products_add_erp" tabindex="-1" role="dialog" aria-labelledby="basicvalCode" aria-hidden="true">
        <div class="modal-dialog wide">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                    <h4 class="modal-title" id="basicvalCode">Add products from erp</h4>
                </div>
                <div class="modal-body">
                    <div id="update_import_product_message"></div>
                    {{ Form::open(array('url' => '/product/importfromerp', 'id' => 'add_products_form_erp' )) }}
                    {{ Form::hidden('_method','POST') }}
                    <div class="row">
                        <div class="form-group col-sm-12">
                            <label class="col-sm-2 control-label" for="Manufacturers">Manufacturers*</label>
                            <div class="col-sm-10">
                                <div class="input-group input-group-sm">
                                    <div id="selectbox">
                                        <select class="form-control" name="manufacturerID" parsley-trigger="change" parsley-required="true" parsley-error-container="#selectbox">
                                            @foreach($manufacturers as $key => $value)
                                            <option value="{{ $key }}">{{ $value }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>                    
                    </div>
                    {{ Form::submit('Import', array('class' => 'btn btn-primary', 'id' => 'add_product_erp_button')) }}
                    {{ Form::close() }}
                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->
	
	<div class="modal fade" id="bulk_update" tabindex="-1" role="dialog" aria-labelledby="basicvalCode" aria-hidden="true">
        <div class="modal-dialog wide">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                    <h4 class="modal-title" id="basicvalCode">Add products from excel</h4>
                </div>
                <div class="modal-body">
                    <div id="update_import_product_message"></div>
                    {{ Form::open(array('url' => '/product/bulkupdateproducts', 'id' => 'bulk_update_products', 'files'=>'true' )) }}
                    {{ Form::hidden('_method','POST') }}
                    <div class="row">
                        <div class="form-group col-sm-6">
                            <label class="col-sm-2 control-label" for="Manufacturers">Manufacturers*</label>
                            <div class="col-sm-10">
                                <div class="input-group input-group-sm">
                                    <div id="selectbox">
                                        <select class="form-control" name="manufacturerID" parsley-trigger="change" parsley-required="true" parsley-error-container="#selectbox">
                                            @foreach($manufacturers as $key => $value)
                                            <option value="{{ $key }}">{{ $value }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group col-sm-6">
                            <label class="col-sm-2 control-label" for="Table Name">Table Name*</label>
                            <div class="col-sm-10">
                                <div class="input-group input-group-sm">
                                    <div id="selectbox">
                                        <select class="form-control" name="table_name" parsley-trigger="change" parsley-required="true" parsley-error-container="#selectbox">
                                            <option value="products">products</option>
                                            <option value="product_attributesets">product_attributesets</option>
                                            <option value="product_locations">product_locations</option>                                            
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col-sm-6">
                            <label class="col-sm-2 control-label" for="Table Name">Table Name*</label>
                            <div class="col-sm-10">
                                <div class="input-group input-group-sm">
                                    <div id="selectbox">
                                        <select class="form-control" name="operation" parsley-trigger="change" parsley-required="true" parsley-error-container="#selectbox">
                                            <option value="update">update</option>
                                            <option value="delete">delete</option>                                            
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group col-sm-6">
                            <span class="btn fileinput-button">                                  
                                <input id="product_fileupload" type="file" name="files" class="form-control">                                    
                            </span>                          
                        </div>
                    </div>
                    {{ Form::submit('Upload File', array('class' => 'btn btn-primary', 'id' => 'bulk_update_products_button')) }}
                    {{ Form::close() }}
                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->
	
    </div>
    </div>
</div>
<div class="modal fade" id="basicvalCodeModal" tabindex="-1" role="dialog" aria-labelledby="basicvalCode" aria-hidden="true" >
                      <div class="modal-dialog wide" >
                        <div class="modal-content" style="width:50%;">
                          <div class="modal-header" align="centre">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">X</button>
                            <h4 class="modal-title" id="basicvalCode">Select Channels</h4>
                          </div>
                          <div class="modal-body">   

                             <!--  {{ Form::open(array('url' => '/productmap/store','name'=>'channelprodfrm', 'id'=>"channelprodfrm")) }} -->
                             {{ Form::open(array('url' => 'product/multistore'))}}
                            {{ Form::hidden('_method', 'POST') }}
                           
                   <div class="row">

                    <?php 
            foreach($channel as $channel1){
                    ?>
         <label class="col-sm-4 control-label" for="is_gds_enabled" style="width: 50%;"> 
         <input type="checkbox" class="case1"  name="chk[]" id="checked<?php echo $channel1->channel_id; ?>"  onclick="test1(<?php echo $channel1->channel_id; ?>,0)"  value="<?php echo $channel1->channel_id; ?>" > 
         
         
            <td><img src="<?php echo $channel1->channel_logo; ?>" width="85" height="75"></td>
            
         </label>
                    
                           <?php 
                           }  
                           ?>  
                       </div>
                           
                       
                         
                    <!--       <div class="row">
                         <div class="checkbox" style="padding-left:0px !important">                                                        
                      <label class="col-sm-4 control-label" for="is_gds_enabled" style="width: 100%;"> 
                      <input type="checkbox"  name="chk[]" id="chk3" onclick="test1()" value="Amazon">Amazon</label>
                      </div>
                      </div> -->
                      <div align="center">

                      <input type="hidden" id="check_id[]" name="check_name" value=" ">    
      {{ Form::submit('Submit', array('class' => 'btn btn-primary')) }}
            
    </div>
                          </div>{{Form::close()}}
                        </div><!-- /.modal-content -->
                      </div><!-- /.modal-dialog -->
                    </div><!-- /.modal -->
    </div>
    </div>
</div>

<div class="modal fade" id="basicvalCodeModal1" tabindex="-1" role="dialog" aria-labelledby="basicvalCode" aria-hidden="true" >
                      <div class="modal-dialog wide">
                        <div class="modal-content" style="width:50%;">
                          <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">X</button>
                            <h4 class="modal-title" id="basicvalCode">Select Channels</h4>
                          </div>
                          <div class="modal-body">   

                             <!--  {{ Form::open(array('url' => '/productmap/store','name'=>'channelprodfrm', 'id'=>"channelprodfrm")) }} -->
                             {{ Form::open(array('url' => 'product/individualstore'))}}
                            {{ Form::hidden('_method', 'POST') }}
                           
                   <div class="row">

                    <?php 
            foreach($channel as $channel1){
                    ?>
         <label class="col-sm-4 control-label" for="is_gds_enabled" style="width: 50%;"> 
         <input type="checkbox" class="case1"  name="chk[]" id="checked<?php echo $channel1->channel_id; ?>"  onclick="test1(<?php echo $channel1->channel_id; ?>,0)"  value="<?php echo $channel1->channel_id; ?>" > 
         
         
            <td><img src="<?php echo $channel1->channel_logo; ?>" width="75" height="65"></td>
            
         </label>
                    
                           <?php 
                           }  
                           ?>  
                       </div>
                           
                       
                         
                    <!--       <div class="row">
                         <div class="checkbox" style="padding-left:0px !important">                                                        
                      <label class="col-sm-4 control-label" for="is_gds_enabled" style="width: 100%;"> 
                      <input type="checkbox"  name="chk[]" id="chk3" onclick="test1()" value="Amazon">Amazon</label>
                      </div>
                      </div> -->
                      <div align="center">

                      <input type="hidden" id="product_id" name="product_id" value=" ">    
      {{ Form::submit('Submit', array('class' => 'btn btn-primary')) }}
            
    </div>
                          </div>{{Form::close()}}
                        </div><!-- /.modal-content -->
                      </div><!-- /.modal-dialog -->
                    </div><!-- /.modal -->
    </div>
    </div>
</div>

    @stop

    @section('style')
    {{HTML::style('jqwidgets/styles/jqx.base.css')}}

    @stop

    @section('script')
    {{HTML::script('jqwidgets/jqxcore.js')}}
    {{HTML::script('jqwidgets/jqxbuttons.js')}}
    {{HTML::script('jqwidgets/jqxscrollbar.js')}}
    {{HTML::script('jqwidgets/jqxmenu.js')}}
    {{HTML::script('jqwidgets/custom_jqxgrid.js')}}
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
            ajaxCall();
            makePopupEditAjax($('#basicvalCodeModal2'));
          });
        function ajaxCall()
          {
            
            $.ajax(
                      {  
            url: "/product/getgdsproducts",
            // prepare the data
            success: function(result)
                  {

            var employees = result;                    
            var source =
                    {
                        datatype: "json",
                        datafields: [
                            {name: 'select', type: 'string'},
                            {name: 'image', type: 'string', cellsalign: 'center'},
                            {name: 'name', type: 'string'},
                            {name: 'sku', type: 'string'},
                            //{name: 'manufacturer_id', type: 'string'},
                            {name: 'product_type_id', type: 'string'},
                            {name: 'status', type: 'string'},
                            {name: 'actions', type: 'string'},
                            {name: 'add', type: 'string'},
                            {name: 'inventory', type: 'string'}
                        ],
                        id: 'product_id',
                        localData: employees
                        
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
                        //autowidth: true,
                        autoloadstate: false,
                        autosavestate: false,
                        columnsresize: true,
                        columnsreorder: true,
                        showfilterrow: true,
                        filterable: true,
                        columns: [
                            {text: 'select', datafield: 'select', width: "10%"},
                            {text: 'Image', datafield: 'image', cellsalign: 'center',width: "10%" },
                            {text: 'Product Name', datafield: 'name', width: "15%"},
                            {text: 'SKU', datafield: 'sku', width: "10%"},
                            //{text: 'Manufacturer', datafield: 'manufacturer_id', width: "10%"},
                            {text: 'Status', datafield: 'status', width: "10%"},
                            {text: 'Product Type', datafield: 'product_type_id', width: "10%"},
                            {text: 'Actions', datafield: 'actions', width: "10%"},
                            {text: 'Select Channels', datafield: 'add', width: "10%"},
                            {text: 'inventory', datafield: 'inventory', width: "15%"}
                        ]
                    });
                }
        });
}

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
                                    if ( $.inArray ( get_ext[0].toLowerCase(), exts ) > -1 ){
                                        return true;
                                    }else{
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
                    success: function (data) {
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
                                    if ( $.inArray ( get_ext[0].toLowerCase(), exts ) > -1 ){
                                        return true;
                                    }else{
                                       return false; 
                                    }
                                }
                            },
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
                    success: function (data) {
                        //$('#update_import_product_message').text(data);
                        alert(data);
                        $('.close').trigger('click');
                    },
                    cache: false,
                    contentType: false,
                    processData: false
                });
                $('#add_component_excel_button').prop('disabled', false);
            });

        
function multiadd(id){
        
            //var check_id=document.getElementById('check[]').checked;
            var check_id = $("input[id='check[]']:checked");

            if(check_id.length == 0){
                document.getElementById("map").style.visibility="hidden";
            } 
            if(check_id.length > 0){
                 document.getElementById("map").style.visibility="visible";     

         //alert(check_id);

          var channel = new Array();
          $("input[id='check[]']:checked").each(function() {
            channel.push($(this).val());
          });

          
          //var kishore="1,2,3";
          //var channel_array='"'+channel+'"'.split(',');
          //console.log(nikhil);
          
          //alert(channel);
          document.getElementById('check_id[]').value = channel;

          $.ajax
    (
      {
        url: "/product/multistore",
        type: "GET", 
        data:  "channel="+channel,
        success: function()
        {
    
          //window.location = "/subscribeapis/index/";
        },

        error:function()
        {
        }   
      }
    ); 
        
}
        }


   function individualadd(id){

    //alert(id);

        document.getElementById('product_id').value=id;
   $.ajax
    (
      {
        url: "/product/individualstore",
        type: "GET", 
        data: "id=" +id,
        success: function()
        {
    
          //window.location = "/subscribeapis/index/";
        },

        error:function()
        {
        }   
      }
    );         
            
        }
             
    function inventoryupdate(id){
   
        document.getElementById('up_id').value=id;
        var text = $("#text_"+id).val();
        var myspan = document.getElementById('myspan'+id);

//alert(text);
        /*var span = $("#myspan").val();
        alert(span);*/
        myspan.innerHTML = "<i class='fa fa-spinner fa-pulse'></i>";
          $.ajax
        ( 
          {
            url: "/product/inventoryupdate",
            type: "POST", 
            data: "id=" +id+"&text="+text,
            success: function()
            {

                myspan.innerHTML = "<i class='fa fa-check'></i>";
              //window.location = "/subscribeapis/index/";
            },

            error:function()
            {
                myspan.innerHTML = "<i class='fa fa-times'></i>";
            }   
          }
        );   
    }     
            
        
        function deleteEntityType(product_id)
        {
            var deleteproduct = confirm("Are you sure you want to Delete ?"), self = $(this);
            if ( deleteproduct == true ) {
                $.ajax({
                    data: '',
                    type: 'GET',
                    datatype: "JSON",
                    url: '/product/deleteproduct/' + product_id,
                    success: function (resp) {
                        if ( resp.message )
                            alert(resp.message);
                        if ( resp.status == true )
                        {
                            self.parents('td').remove();
                            location.reload();
                        }

                    },
                    error: function (error) {
                        console.log(error.responseText);
                    },
                    complete: function () {

                    }
                });
            }
        }
        function restoreEntityType(product_id)
        {
            var restoreProduct = confirm("Are you sure you want to restore it ?"), self = $(this);
            if ( restoreProduct == true ) {
                $.ajax({
                    data: '',
                    type: 'GET',
                    datatype: "JSON",
                    url: '/product/restoreproduct/' + product_id,
                    success: function (resp) {
                        if ( resp.message )
                            alert(resp.message);
                        if ( resp.status == true )
                        {
                            self.parents('td').remove();
                            location.reload();
                        }

                    },
                    error: function (error) {
                        console.log(error.responseText);
                    },
                    complete: function () {

                    }
                });
            }
        }

    function preview(productId)
    {
        console.log(productId);
        $("#addEntity").click();
        $.get('/products/preview/?product_id='+productId,function(response){ 
            $("#basicvalCode").html('Preview');
            console.log(response);
            $("#entitiesDiv").html(response);
        });
    }

    function editProduct(id)
{
  console.log(id);
     $.get('/product/editgdsproduct/'+id,function(response){ 
        //alert('gggg');
        console.log(response);
            $("#basicvalCode").html('Edit Product');
            
            $("#editProdDiv").html(response);
            
            $("#editProduct").click();
        });
}
    
    
    function approve(productId,type)
    {
        $.get('/products/approve/?product_id='+productId+'&type='+type,function(response){             
            console.log(response);
            alert(response);
            $('#close_preview').trigger('click');

            if(type==1)
            {
                $('#save_product').prop('disabled', false).trigger('click');
                $('#product_creation').submit();
            }

        });
    }
        
        $('#add_products_form_erp').submit(function(event){
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
                success: function (data) {
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
		$('#bulk_update_products').submit(function(event){
            event.preventDefault();
            $('#bulk_update_products_button').prop('disabled', true);
            $form = $(this);
            url = $form.attr('action');
            var formData = new FormData($(this)[0]);

            $.ajax({
                url: url,
                type: 'POST',
                data: formData,
                async: false,
                success: function (data) {
                    //$('#update_import_product_message').text(data);
                    //alert(data);
                    if(data.status)
                    {
                        alert(data.message+' records '+data.sucess_records);
                        $('.close').trigger('click');
                    }else{
                        alert(data.message);
                    }
                },
                cache: false,
                contentType: false,
                processData: false
            });
            $('#bulk_update_products_button').prop('disabled', false);
        });

    </script>    
    @stop