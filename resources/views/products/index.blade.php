@extends('layouts.default')
@extends('layouts.header')
@extends('layouts.sideview')
@section('content')


<?php //echo "<pre/>";print_r($listcat);exit;?>
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css">
<style>
.btn{margin-left:10px !important;}
.panel-heading .accordion-toggle:after {
    /* symbol for "opening" panels */
    font-family: 'Glyphicons Halflings';  /* essential for enabling glyphicon */
    content: "\e114";    /* adjust as needed, taken from bootstrap.css */
    float: right;        /* adjust as needed */
    color: grey;         /* adjust as needed */
}
.panel-heading .accordion-toggle.collapsed:after {
    /* symbol for "collapsed" panels */
    content: "\e080";    /* adjust as needed, taken from bootstrap.css */
}
</style>
<!-- Page content -->
<?php if(isset($error_message)){ ?>
    <div>
        <span><?php echo $formData['error_message']; ?></span>
    </div>
<?php } ?>


<div class="box">
    <div class="box-header">
      <h3 class="box-title">Manage <strong>Products</strong></h3>
      <!-- <a href="pricemaster/add" class="pull-right"><i class="fa fa-user-plus"></i><span style="font-size:14px; padding-left:5px; ">Add Price</span></a> -->
      <?php if(isset($allow_buttons['add']) && $allow_buttons['add'] == 1){ ?>
        <button class="btn btn-primary pull-right" data-toggle="modal" onclick="location.href = '/products/create';">Add Product</button>
      <?php }
       if(isset($allow_buttons['add_component']) && $allow_buttons['add_component'] == 1){ ?>
        <button class="btn btn-primary pull-right" data-toggle="modal" data-target="#component_add_excel">Import BOM from CSV</button>
      <?php }
       if(isset($allow_buttons['import_csv']) && $allow_buttons['import_csv'] == 1){ ?>
        <button type="button" class="btn btn-primary pull-right" data-toggle="modal" data-target="#products_add_excel"> Import from CSV</button> 
      <?php } 
      if(isset($allow_buttons['import_erp']) && $allow_buttons['import_erp'] == 1){ ?>
        <button type="button" class="btn btn-primary pull-right" data-toggle="modal" data-target="#products_add_erp"> Import from ERP</button> 
      <?php } ?>
        <button type="button" class="btn btn-primary pull-right" data-toggle="modal" data-target="#product_info"> 
                    Product Info
                </button>

      <!-- <button type="button" class="btn btn-primary pull-right" data-toggle="modal" data-target="#bulk_update"> Bulk Update</button>  -->
      <button type="button" class="btn btn-primary pull-right" data-toggle="modal" data-target="#products_add_excel"> Import from Excel</button> 

        <div class="form-group pull-right" style="margin-left:15px;">
                      <a href="{{ url('products/exportToproducts/xls') }}" > 
                       <button type="button" class="btn btn-primary">Export to xls  </button>
                     </a>
                </div>
                
    </div>
     
    <div class="col-sm-12">
       <div class="tile-body nopadding">                  
           
       

 
    <!--<button class="btn btn-primary" data-toggle="modal" onclick="location.href = '/product/create';">Add Product</button>-->
    
    
     <div id="jqxgrid"></div>
     

<!-- Modal -->
    <div class="modal fade" id="products_add_excel" tabindex="-1" role="dialog" aria-labelledby="basicvalCode" aria-hidden="true">
        <div class="modal-dialog wide">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                    <h4 class="modal-title" id="basicvalCode">Add Products From Excel</h4>
                </div>
                <div class="modal-body">
                    <div id="update_import_product_message"></div>
                    {{ Form::open(array('url' => '/products/saveProductsFromExcel', 'id' => 'add_products_form_excel', 'files'=>'true' )) }}
                    {{ Form::hidden('_method','POST') }}
                    <!-- <div class="row">
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
                    </div> -->
                    <div class="row">
                        <div class="form-group col-sm-6">
                            <span class="btn fileinput-button">                                  
                                <input id="product_fileupload" type="file" name="files" class="form-control">                                    
                            </span>
                        </div>
                        <div class="form-group col-sm-6">
                            {{ Form::submit('Upload File', array('class' => 'btn btn-primary', 'id' => 'add_product_excel_button')) }}
                            {{ Form::close() }}
                        </div>  
                    </div>
                    <div class="row">
                        <div class="form-group col-sm-8">    
                            <a href="/products/download/FG_Material_Codes" class="btn btn-primary pull-right"><i class="icon-download-alt"> </i> Download sample file </a>
                        </div> 
                    </div>
                    
                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->
    </div>
    </div>
</div>




<!-- Modal -->
    <div class="modal fade" id="product_info" tabindex="-1" role="dialog" aria-labelledby="basicvalCode" aria-hidden="true">
        <div class="modal-dialog wide">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                    <h4 class="modal-title" id="basicvalCode">Product Master Info</h4>
                </div>
                <div class="modal-body">
                   <div class="">


  <div class="panel-group" id="accordion">
  <div class="panel panel-default">
    <div class="panel-heading">
      <h4 class="panel-title">
        <a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion" href="#collapseOne">
          List of Product categories
        </a>
      </h4>
    </div>
    <div id="collapseOne" class="panel-collapse collapse in">
      <div class="panel-body">
        <table class="table table-strriped table-bordered table-responsive">
            <thead> <th>Sno</th><th>Category</th></thead>
            <tbody>


                <?php
                if($listcat){
                $sno=0;
                ?>
                @foreach($listcat as $key=>$value)
                     <?php echo "<tr><td>".(++$sno).'</td><td>'.$value.'</td></tr>'; ?>
                
                @endforeach
                <?php } ?>
            </tbody>             
        </table>
      </div>
    </div>
  </div>
  <div class="panel panel-default">
    <div class="panel-heading">
      <h4 class="panel-title">
        <a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion" href="#collapseTwo">
          List of Groups
        </a>
      </h4>
    </div>
    <div id="collapseTwo" class="panel-collapse collapse">
      <div class="panel-body">
        <table class="table table-strriped table-bordered table-responsive">
            <thead> <th>Sno</th><th>Group Id</th><th>Group Name</th></thead>
            <tbody>
                <?php
                $sno=0;
                ?>
                @foreach($listgroup as $key=> $value) 
                  <?php echo "<tr><td>".++$sno."</td><td>".$key."</td><td>".$value."</td></tr>"; ?>
                @endforeach
            </tbody>             
        </table>
      </div>
    </div>
  </div>
</div>
  




                   </div>
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
                            <a href="/products/download/Component_Codes" class="btn btn-large pull-right"><i class="icon-download-alt"> </i> Download sample file </a>
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
                            <label class="col-sm-2 control-label" for="Table Name">Table Action*</label>
                            <div class="col-sm-10">
                                <div class="input-group input-group-sm">
                                    <div id="selectbox">
                                        <select class="form-control" name="operation" parsley-trigger="change" parsley-required="true" parsley-error-container="#selectbox">
                                            <option value="update">update</option>
                                                                                        
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
                    <a href="#" id="bulkupdateerrorlog" class="pull-right">Download Error Log</a>
                    {{ Form::submit('Upload File', array('class' => 'btn btn-primary', 'id' => 'bulk_update_products_button')) }}
                    {{ Form::close() }}
                </div>
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
                                    var exts = ['xls','xlsx'];
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
                    success: function (data) {
                        //$('#update_import_product_message').text(data);
                        //alert(data);
                        var error = "";
                        $.each(data.message, function(i,val){
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
            
            var url = "getproducts";
            // prepare the data
            var source =
                    {
                        datatype: "json",
                        datafields: [
                            // {name: 'image', type: 'string', cellsalign: 'center'},
                            {name: 'name', type: 'string'},
                            {name: 'sku', type: 'string'},
                            {name: 'material_code',type:'string'},
                            {name: 'group_name',type:'string'},
                            {name: 'manufacturer_id', type: 'string'},
                            {name: 'product_type_id', type: 'string'},
                            {name: 'status', type: 'string'},
                            {name: 'actions', type: 'string'}
                        ],
                        id: 'product_id',
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
                        //autowidth: true,
                        autoloadstate: false,
                        autosavestate: false,
                        columnsresize: true,
                        columnsreorder: true,
                        showfilterrow: true,
                        filterable: true,
                        columns: [
                            // {text: 'Image', datafield: 'image', cellsalign: 'center',width: "15%" },
                            {text: 'Product Name', datafield: 'name', width: "15%"},
                            {text: 'SKU', datafield: 'sku', width: "10%"},
                             {text: 'Material Code', datafield: 'material_code', width: "15%"},
                             {text: 'Group', datafield: 'group_name', width: "15%" },
                            {text: 'Manufacturer', datafield: 'manufacturer_id', width: "15%"},
                            {text: 'Status', datafield: 'status', width: "10%"},
                            {text: 'Product Type', datafield: 'product_type_id', width: "10%"},
                            {text: 'Actions', datafield: 'actions', width: "10%"}
                        ]
                    });
        });
        function deleteEntityType(product_id)
        {
            var deleteproduct = confirm("Are you sure you want to Delete ?"), self = $(this);
            if ( deleteproduct == true ) {
                $.ajax({
                    data: '',
                    type: 'GET',
                    datatype: "JSON",
                    url: '/products/deleteproduct/' + product_id,
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
                        var error =false;
                        if(data.hasOwnProperty("error_log_link") & data.error_log_link !=""){
                            error = true;
                        }
                        if(error){
                            $('#bulkupdateerrorlog').attr('href',data.error_log_link);
                            $('#bulkupdateerrorlog').show();
                            alert(data.message+' Successfully Updated records: '+data.sucess_records+'\n Falied Records:'+data.failed_records+'.\n Please  click on the below link to download the error Log');
                             
                        }
                        else{
                            alert(data.message+' Successfully Updated records: '+data.sucess_records+'\n Falied Records:'+data.failed_records);
                             $('#bulkupdateerrorlog').hide();

                            $('.close').trigger('click');
                        }
                        
                        
                                                                        
                    }else{
                        $('#bulkupdateerrorlog').hide();
                        alert(data.message);
                    }
                    //$('#bulk_update_products').data('bootstrapValidator').resetForm();
                    $('#bulk_update_products')[0].reset();
                },
                cache: false,
                contentType: false,
                processData: false
            });
            $('#bulk_update_products_button').prop('disabled', false);
        });
    </script>    
@stop