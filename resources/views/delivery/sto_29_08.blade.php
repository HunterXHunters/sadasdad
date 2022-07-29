<?php
$sto=-1;
if(strtolower($arg)=='sto')
$sto=1; else if(strtolower($arg)=='deliveries' || strtolower($arg)=='index')
$sto=0;
// else if(strtolower($arg)=='add'){
// return Redirect::to('delivery/add');
// }
else if($sto==-1)
{
    echo "invalid argument supplied, Please check with team"; exit;
} 
?>

@extends('layouts.default')
@extends('layouts.header')
<style type="text/css">
    .row{margin-left:-10px; }
    .jqx-grid-cell-left-align { padding-left: 5px;}
    .btn-primary[disabled], .btn-primary[disabled]:hover{
        background-color:#26B99A;
        border:1px solid #169F85;
      }
    .yellow {
        color: black\9;
        background-color: yellow\9;
        text-decoration: underline;
        cursor: pointer;
    }
    .yellow:not(.jqx-grid-cell-hover):not(.jqx-grid-cell-selected), .jqx-widget .yellow:not(.jqx-grid-cell-hover):not(.jqx-grid-cell-selected) {
            color: black;
            background-color: yellow;
    }
    .red {rgba(61, 158, 61, 0.99);
        color: black\9;
        background-color: #9B3939;
        text-decoration: underline;
        cursor: pointer;
        color: #fff;
    }
    .red:not(.jqx-grid-cell-hover):not(.jqx-grid-cell-selected), .jqx-widget .red:not(.jqx-grid-cell-hover):not(.jqx-grid-cell-selected) {
            color: black;
            background-color: #9B3939;
            color: #fff;
    }
    .green {
        color: black\9;
        background-color: #4B9A4B;
        text-decoration: underline;
        cursor: pointer;
        color: #fff;
    }
    .green:not(.jqx-grid-cell-hover):not(.jqx-grid-cell-selected), .jqx-widget .green:not(.jqx-grid-cell-hover):not(.jqx-grid-cell-selected) {
            color: black;
            background-color: #4B9A4B;
            color: #fff;
    }
    .orange {
        color: black\9;
        background-color: #FFD700;
        color: #fff;
    }
    .orange:not(.jqx-grid-cell-hover):not(.jqx-grid-cell-selected), .jqx-widget .orange:not(.jqx-grid-cell-hover):not(.jqx-grid-cell-selected) {
            color: black;
            background-color: #FFD700;
            color: #fff;
    }
    .jqx-popover{width:300px;}  
    canvas {
        -moz-user-select: none;
        -webkit-user-select: none;
        -ms-user-select: none;
    }  
</style>
@extends('layouts.sideview')
@section('content')

@section('style')
{{HTML::style('jqwidgets/styles/jqx.base.css')}}
{{HTML::style('css/dragdrop/jquery-ui.css')}}
{{HTML::style('css/dragdrop/style.css')}}
{{HTML::style('css/bootstrap-select.css')}}


@stop

@section('script')

{{HTML::script('jqwidgets/jqxcore.js')}}
{{HTML::script('jqwidgets/jqxbuttons.js')}}
{{HTML::script('js/plugins/dragdrop/fieldChooser.js')}}
{{HTML::script('jqwidgets/jqxscrollbar.js')}}
{{HTML::script('js/plugins/dragdrop/jquery-ui.js')}}
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
{{HTML::script('jqwidgets/jqxdatatable.js')}}
{{HTML::script('jqwidgets/jqxtreegrid.js')}}
{{HTML::script('js/plugins/bootstrap-select/bootstrap-select.js')}}
{{HTML::script('js/plugins/bootstrap-select/bootstrap-datepicker.min.js')}}
{{HTML::script('js/plugins/bootstrap-select/bootstrap-multiselect.js')}}
{{HTML::script('js/plugins/jquery-file-upload/vendor/jquery.ui.widget.js')}}
{{HTML::script('js/plugins/jquery-file-upload/load-image.all.min.js')}}
{{HTML::script('js/plugins/jquery-file-upload/jquery.iframe-transport.js')}}
{{HTML::script('js/plugins/jquery-file-upload/jquery.fileupload.js')}}
{{HTML::script('js/plugins/jquery-file-upload/jquery.fileupload-process.js')}}
{{HTML::script('js/plugins/jquery-file-upload/jquery.fileupload-image.js')}}
{{HTML::script('js/plugins/jquery-file-upload/jquery.fileupload-audio.js')}}
{{HTML::script('js/plugins/jquery-file-upload/jquery.fileupload-video.js')}}
{{HTML::script('js/plugins/jquery-file-upload/jquery.fileupload-validate.js')}}
{{HTML::script('js/plugins/jquery-file-upload/customer-upload-script.js')}}
{{HTML::script('scripts/demos.js')}}
{{HTML::script('js/plugins/bootstrap-select/bootstrap-select.js')}}
{{HTML::script('js/plugins/bootstrap-select/bootstrap-multiselect.js')}}
{{HTML::script('js/plugins/bootstrap-select//bootstrap-datepicker.min.js')}}

{{HTML::script('js/plugins/validator/formValidation.min.js')}}
{{HTML::script('js/plugins/validator/validator.bootstrap.min.js')}}
{{HTML::script('js/plugins/validator/jquery.bootstrap.wizard.min.js')}}
<!-- Include all compiled plugins (below), or include individual files as needed -->
@stop
</head>
<body>
<div align="center" id="loading" style="text-align:center; z-index:9999;position:absolute;background:rgba(0,0,0,0.3);height:709px; width:1261px; display:none;" ><img src="/img/loading.gif">    </div>
    @if (Session::has('message'))
    <div class="flash alert">
        <p>{{ Session::get('message') }}</p>
    </div>
    @endif
    <!-- Page content -->
    <!--  <div id="content" class="col-md-12" style="padding-left:258px !important;">  -->

    <div class="box">      
        <div class="main" style="margin-top:15px;">           
            <div class="row">
                <div class="form-group col-sm-5">
              <div class="form-group">
                <div class="col-sm-10">
            @if($sto==1)
              <p style="font-size:20px">STO Details</p>
            @elseif($sto==0)
             <p style="font-size:20px">Deliveries</p>
             @endif 
             
            </div>
            </div>
           </div> 
                <div class="form-group col-sm-2" id="manuId">
                    <label class="col-sm-2 control-label" for="BusinessType">Manufacturer</label>
                    <div class="col-sm-10">
                        <div class="input-group input-group-sm">
                            <span class="input-group-addon addon-red"><i class="fa fa-building-o"></i></span>
                            <div id="selectbox">
                                <select name="manufacturer_id" id="main_manufacturer_id" class="form-control">
                                   @if(!empty($custType) && isset($custType[0]) && $custType[0]->customer_type_id==1001)
                                    @foreach($manufacturerData as $key => $value)
                                    <option value="{{ $key }}" selected="true">{{ $value }}</option>
                                    @endforeach
                                    @else
                                    <option value="0">Please select..</option>                                    
                                    @foreach($manufacturerData as $key => $value)
                                    <option value="{{ $key }}">{{ $value }}</option>
                                    @endforeach
                                    @endif
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            <div class="form-group col-sm-7">
              <div class="form-group pull-right">
                <div class="col-sm-10">
                    @if($sto)
               <a href="/deliveries/add" class="button"> <button type="button" class="btn btn-primary"><i class="fa fa-plus-circle"></i><span style="font-size:11px;">Create Delivery</span></button></a>
                  @endif
            </div>
            </div>
           </div> 
            </div> 
        </div> 

        <div class="col-sm-12">
            <div class="tile-body nopadding"> 
                      
                <div id="Attributegrid" style="width:100% !important;"></div>
                <button data-toggle="modal" id="edit" class="btn btn-default" data-target="#wizardCodeModal" style="display: none"></button>
            </div>
        </div>           
        <!-- Modal -->
        <div class="modal fade" id="basicvalCodeModal1" tabindex="-1" role="dialog" aria-labelledby="basicvalCode" aria-hidden="true">
            <div class="modal-dialog wide">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                        <h4 class="modal-title" id="basicvalCode">Edit Delivery Details</h4>
                    </div>
                    <div class="modal-body">
                        {{ Form::open(array('url' => '/delivery/updateDeliveryDetails', 'data-url' => '/delivery/updateDeliveryDetails/','id'=>'editDeliveryDetails')) }} 
                        {{ Form::hidden('_method', 'PUT') }}

                        <div class="row">
                            <div class="form-group col-sm-4 hidden">
                                <label for="exampleInputEmail"> Item*</label>
                                 <div class="input-group">
                                    <span class="input-group-addon addon-red"><i class="fa fa-cubes"></i></span>
                                    <select name="product_id" id="product_id" class="form-control">
                                        @foreach($products as $product)
                                            <option value="{{$product->product_id}}"> {{$product->name}}</option>
                                          @endforeach
                                    </select>
                                    <input type="hidden" name="delivery_id" id="delivery_id" value="" />

                                </div>                        
                            </div>
                             <div class="form-group col-sm-4">
                                <label for="exampleInputEmail">Qty *</label>
                                <div class="input-group ">
                                    <span class="input-group-addon addon-red"><i class="fa fa-cube"></i></span>
                                    <input type="text"  id="qty" name="qty" value="" class="form-control" aria-describedby="basic-addon1">
                                </div>
                            </div>  
                                                         
                        </div>
                        {{ Form::submit('Update', array('class' => 'btn btn-primary')) }}
                        {{ Form::close() }}

                    </div><!-- /.modal-content -->
                </div><!-- /.modal-dialog -->
            </div><!-- /.modal -->
        </div>

        <!-- Modal -->
        <div class="modal fade" id="basicvalCodeModal" tabindex="-1" role="dialog" aria-labelledby="basicvalCode" aria-hidden="true">
            <div class="modal-dialog wide">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                        <h4 class="modal-title" id="basicvalCode">Add QC Attributes</h4>
                    </div>
                    <div class="modal-body">
                        {{ Form::open(array('url' => 'product/saveQcattributes','id'=>'addQcAttribute')) }}
                        {{ Form::hidden('_method', 'POST') }}

                        <div class="row">
                            <div class="form-group col-sm-6">
                                <label for="exampleInputEmail">Product Name*</label>
                                <div class="input-group">
                                    <span class="input-group-addon addon-red"><i class="fa fa-cubes"></i></span>
                                    <select name="product_id" id="product_idadd" class="form-control">
                                       
                                    </select>
                                </div>                        
                            </div>
                            <input type="hidden" name="manufacturer_id" id="update_manufacturer_id" value="" /> 
                               <div class="form-group col-sm-6">
                                <label for="exampleInputEmail">Attribute Name *</label>
                                <div class="input-group ">
                                    <span class="input-group-addon addon-red"><i class="fa fa-cube"></i></span>
                                    <input type="text"  id="name" name="name" placeholder="name" class="form-control">
                                </div>
                            </div>                             
                        </div>
                        <div class="row">
                             <div class="form-group col-sm-6">
                                <label for="exampleInputEmail">Input Type *</label>
                                <div class="input-group ">
                                    <span class="input-group-addon addon-red"><i class="fa fa-shield"></i></span>
                                    <select name="input_type" id="input_type"  class="form-control">
                                        <option  value="0">Please Select ..</option>
                                        <option  value="checkbox">Check Box</option>
                                        <option  value="radio">Radio</option>
                                        <option  value="text">Text</option>
                                        <option  value="textarea">Text Area</option>
                                        <option  value="date">Date</option>
                                        <option  value="datetime">Date Time</option>
                                        <option  value="select">Select Drop Down</option>
                                        <option  value="multiselect">Multi Select Drop Down</option>
                                        <option  value="sdropdown">Single Select Drop Down</option>
                                    </select>
                                </div>
                            </div>
                             <div class="form-group col-sm-6">
                                <label for="exampleInputEmail">Default Value</label>
                                <div class="input-group ">
                                    <span class="input-group-addon addon-red"><i class="fa fa-credit-card"></i></span>
                                    <input type="text" id="default_value" name="default_value" placeholder="Default Value" class="form-control">
                                </div>
                            </div>    
                        </div>
                          
                           <div class="row">
                            <div class="form-group col-sm-6">
                                <label for="exampleInputEmail">Mininmum Value</label>
                                <div class="input-group ">
                                    <span class="input-group-addon addon-red"><i class="fa fa-puzzle-piece"></i></span>
                                    <input type="text" id="minimum" name="minimum" placeholder="Min" class="form-control">
                                </div>
                            </div>                              
                            <div class="form-group col-sm-6">
                                <label for="exampleInputEmail">Maximum Value</label>
                                <div class="input-group">
                                    <span class="input-group-addon addon-red"><i class="fa fa-newspaper-o"></i></span>
                                    <input type="text" id="maximum" name="maximum" placeholder="Max" class="form-control">
                                </div>                        
                            </div>                          
                        </div>

                        <div class="row">
                            <div class="form-group col-sm-6">
                                <label for="exampleInputEmail">Attribute Type *</label>
                                <div class="input-group">
                                    <span class="input-group-addon addon-red"><i class="fa fa-code-fork"></i></span>

                                    <select name="attribute_type" id="attribute_type" class="form-control">
                                        <option  value="0">Please Select ..</option>
                                        <option  value="1">Static</option>
                                        <option  value="2">Dynamic</option>
                                        <option  value="3">Binding</option>
                                        <option  value="4">TP</option>
                                        <option  value="5">QC</option>                                              
                                    </select>
                                </div>                        
                            </div>                   
                            <div class="form-group col-sm-6">
                                <label for="exampleInputEmail">Is_Required</label>
                                <div class="input-group">
                                    <span class="input-group-addon addon-red"><i class="ion-android-done"></i></span>

                                    <select name="is_required" class="form-control">
                                        <option  value="1">Yes</option>
                                        <option  value="0">No</option>
                                    </select>
                                </div>                         
                            </div>                           
                        </div>
                        <div class="row">
                            <div class="form-group col-sm-6">
                                <label for="exampleInputEmail">Validation</label>
                                <div class="input-group ">
                                    <span class="input-group-addon addon-red"><i class="fa fa-puzzle-piece"></i></span>
                                    <input type="text" id="validation" name="validation" value=""  class="form-control" aria-describedby="basic-addon1">
                                </div>
                            </div>
                        </div>
                        {{ Form::submit('Submit', array('class' => 'btn btn-primary')) }}
                        {{ Form::close() }}


                    </div><!-- /.modal-content -->
                </div><!-- /.modal-dialog -->
            </div><!-- /.modal -->

        </div>
        <button id="option-button" data-toggle="modal" data-target="#addoptions" style="display: none;"></button>
        <!-- Modal -->
        <div class="modal fade" id="addoptions" tabindex="-1" role="dialog" aria-labelledby="basicvalCode" aria-hidden="true">
            <div class="modal-dialog wide">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" id="option-close" aria-hidden="true">×</button>
                        <h4 class="modal-title" id="basicvalCode">Add options</h4>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="form-group col-sm-6">
                                <label for="exampleInputEmail">Key</label>                                  
                            </div>
                            <div class="form-group col-sm-6">
                                <label for="exampleInputEmail" >Value</label>
                                <label class="pull-right"><i class="fa fa-plus-circle" data-toggle="modal" id="add_new_option"  style="cursor: pointer; font-size:15px"></i></label>
                                <!--<div class="input-group-addon" id="add_option"></div>-->
                            </div>
                        </div>
                        <div class="row">
                            <div class="form-group col-sm-6">
                                <label for="exampleInputEmail"></label>
                                <input type="text" name="key[]" class="form-control" value="" />
                            </div>
                            <div class="form-group col-sm-4">
                                <label for="exampleInputEmail"></label>
                                <input type="text" name="value[]" class="form-control" value="" />                                        
                            </div>
                            <div class="form-group col-sm-2">
                                <label for="exampleInputEmail"></label>
                                <input type="text" name="sort_order[]" class="form-control" value="" />                 
                            </div>
                        </div>
                        <div class="row" id="option_data" style="display: none;">
                            <div class="form-group col-sm-6">
                                <label for="exampleInputEmail"></label>
                                <input type="text" name="key[]" class="form-control" value="" />
                            </div>
                            <div class="form-group col-sm-4">
                                <label for="exampleInputEmail"></label>
                                <input type="text" name="value[]" class="form-control" value="" />                                    
                            </div>
                            <div class="form-group col-sm-2">
                                <label for="exampleInputEmail"></label>
                                <input type="text" name="sort_order[]" class="form-control" value="" />                 
                                <button type="button" class="btn btn-default removeButton" onclick="removeActions($(this))" style="position: absolute; top: 17px; right: 14px;">
                                    <i class="fa fa-minus option-delete" data-toggle="modal" style="cursor: pointer;"></i>
                                </button> 
                            </div>
                        </div>                            
                        <div class="row">
                            <div class="form-group col-sm-6">
                                <button type="button" id="save-options" class="btn btn-success">Submit</button>
                            </div>                                
                        </div>
                    </div><!-- /.modal-content -->
                </div><!-- /.modal-dialog -->
            </div><!-- /.modal -->
        </div>

         
        <div class="modal fade" id="qcattributes_add_excel" tabindex="-1" role="dialog" aria-labelledby="basicvalCode" aria-hidden="true">
        <div class="modal-dialog wide">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                    <h4 class="modal-title" id="basicvalCode">Add products from Excel(.xls)</h4>
                </div>
                <div class="modal-body">
                    <div id="update_import_product_message"></div>
                    {{ Form::open(array('url' => '/product/saveQcAttributesFromExcel', 'id' => 'add_qcattributes_form_excel', 'files'=>'true' )) }}
                    {{ Form::hidden('_method','POST') }}
                          <div class="form-group col-sm-10">
                                <div class=" col-sm-4"> <a href="/customer/download/Qc_Attributes" class="btn bg-orange margin" style="margin-top:0px;"><i class="icon-download-alt"> </i> Download sample file </a>
                                </div>  
                                 <div class=" col-sm-4"> 
                                 <span class="">Import From XLS File</span>
                                 <div>
                                    <input id="product_fileupload" type="file" name="files">
                                    </div>
                                 </div>  
                                
                           <!--  </div> -->
                    </div>
                    <br/><br/>
                    {{ Form::submit('Upload File', array('class' => 'btn btn-primary', 'id' => 'add_qcattributes_excel_button')) }}
                    {{ Form::close() }}
                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div>


         <div class="modal fade" id="addAttributeSet" tabindex="-1" role="dialog" aria-labelledby="basicvalCode" aria-hidden="true">
            <div class="modal-dialog wide">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                        <h4 class="modal-title" id="basicvalCode">Add Attribute Set</h4>
                    </div>
                    <div class="modal-body">
                        {{ Form::open(array('url' => '/product/saveattributeset', 'id' => 'save_attribute_set')) }}
                        {{ Form::hidden('_method', 'POST') }}

                        <div class="row">
                            <div class="form-group col-sm-6">
                                <label for="exampleInputEmail">Attribute Set Name *</label>
                                <div class="input-group ">
                                    <span class="input-group-addon addon-red"><i class="fa fa-cubes"></i></span>
                                    <input type="text"  id="name" name="attribute_set[attribute_set_name]" placeholder="name" class="form-control">
                                </div>
                            </div>
                            <div class="form-group col-sm-6">
                                <label for="exampleInputEmail">Category Name *</label>
                                <div class="input-group ">
                                    <span class="input-group-addon addon-red"><i class="fa fa-bars"></i></span>
                                    <select name="attribute_set[category_id]" id="category_id" class="form-control" >
                                        <option value="0">Please Select...</option>                                        
                                       
                                    </select>
                                </div>  
                            </div>
                        </div>

                        <div class="row">
                            <div class="form-group col-sm-6">
                                <label for="exampleInputEmail">Manufacture Name *</label>
                                <div class="input-group ">
                                    <span class="input-group-addon addon-red"><i class="fa fa-building-o"></i></span>
                                    <input type="text" id="update_manufacturer_name" value="" class="form-control" name="update_manufacturer_name" readonly />
                                    <input type="hidden" name="attribute_set[manufacturer_id]" id="update_manufacturer_id" value="" />                                        

                                </div>
                            </div>
                            <input type="hidden" name="attribute_set[is_active]" value="1" />
                        </div>
                        <!--added for Pulling and adding-->
                        <div class="row">
                            <div id="fieldChooser" tabIndex="1">
                                <div class="form-group col-sm-6">
                                    <label for="exampleInputEmail">Select Attributes</label>
                                    <a href="#" data-toggle="modal" data-target="#wizardCodeModal" data-placement="right" title="Add New Attribute!"><!-- <i class="fa fa-user-plus"></i> --></a>

                                    <div id="selectbox" >
                                        <div id="Selectattribute"></div>
                                    </div>
                                </div>
                                <div class="form-group col-sm-6">
                                    <label for="exampleInputEmail" >Selected Attributes</label>  
                                    <div id="attribute_id" name="attribute_id[]"></div>
                                </div>
                            </div>
                        </div>
                        <!--added for Pulling and adding-->

                        <!--input type="button" class="btn btn-primary" name="Submit" id="saveAttributeSet" value="Submit" /-->
                        {{ Form::submit('Submit', array('class' => 'btn btn-primary', 'id' => 'save_attribute_set_button')) }}
                        {{ Form::close() }}

                    </div>
                </div><!-- /.modal-content -->
            </div><!-- /.modal-dialog -->
        </div><!-- /.modal -->


        <div class="modal fade" id="editqcAttributewithProductmodal" tabindex="-1" role="dialog" aria-labelledby="basicvalCode" aria-hidden="true">
            <div class="modal-dialog wide">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                        <h4 class="modal-title" id="basicvalCode">Edit QC Attribute with Product</h4>
                    </div>
                    <div class="modal-body">
                        {{ Form::open(array('url' => '/product/updateattributewithproducts', 'data-url' => '/product/updateattributewithproducts/','id'=>'editqcAttributewithProduct')) }} 
                        {{ Form::hidden('_method', 'POST') }}
                        <div class="row">
                            <div class="form-group col-sm-6">
                                <label for="exampleInputEmail">Product Name*</label>
                                <div class="input-group ">
                                    <span class="input-group-addon addon-red"><i class="fa fa-cubes"></i></span>
                                    <select name="product_id" id="product_id" class="form-control" readonly>
                                       
                                    </select>
                                <input type="hidden" name="product_id" id="product_id" value="" />
                                </div>
                            </div>
                            <div class="form-group col-sm-6">
                                <label for="exampleInputEmail">Manufacture Name</label>
                                <div class="input-group ">
                                    <span class="input-group-addon addon-red"><i class="fa fa-building-o"></i></span>
                                    <input type="text" id="update_manufacturer_name" value="" class="form-control" readonly />
                                    <input type="hidden" name="attribute_set[manufacturer_id]" id="update_manufacturer_id" value="" />
                                </div>
                            </div>
                            <input type="hidden" name="attribute_set[is_active]" value="1" />
                        </div>
                        <div class="row">
                            <div id="fieldChooser" tabIndex="1">
                                <div class="form-group col-sm-6">
                                    <label for="exampleInputEmail">Select Qc Attributes</label>
                                    <a href="#" data-toggle="modal" data-target="#wizardCodeModal" data-placement="right" title="Add New Attribute!"><!-- <i class="fa fa-user-plus"></i> --></a>
                                    <div id="selectbox">
                                        <input type="hidden" name="formattributes" id="formattributes1" value="0" />
                                        <div id="Selectattribute1" name="attributes"></div>
                                    </div>
                                </div>
                                <div class="form-group col-sm-6">
                                    <label for="exampleInputEmail" >Selected Qc Attributes</label>  
                                    <div id="attribute_id1" name="attribute_id[]"></div>
                                </div>
                            </div>
                        </div>
                        <!--added for Pulling and adding-->

                        {{ Form::submit('Update', array('class' => 'btn btn-primary', 'id' => 'update_attribute_set_button')) }}
                        {{ Form::close() }}
                    </div>
                </div><!-- /.modal-content -->
            </div><!-- /.modal-dialog -->
        </div><!-- /.modal -->

      

        <div class="modal fade" id="assignAttributeSet" tabindex="-1" role="dialog" aria-labelledby="basicvalCode" aria-hidden="true">
            <div class="modal-dialog wide">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                        <h4 class="modal-title" id="basicvalCode">Edit Attribute Set</h4>
                    </div>
                    <div class="modal-body">
                        {{ Form::open(array('url' => '/product/assigngroups', 'id'=>'assignGroupsLocations')) }} 
                        {{ Form::hidden('_method', 'POST') }}
                        <div class="row">
                            <div class="form-group col-sm-6">
                                <label for="exampleInputEmail">Attribute Set Name *</label>
                                <div class="input-group ">
                                    <span class="input-group-addon addon-red"><i class="fa fa-cubes"></i></span>
                                    <input type="text"  id="assign_attribute_set_name" name="attribute_set_name" placeholder="name" class="form-control" readonly>
                                    <input type="hidden" name="attribute_set_id" id="assign_attribute_set_id" value="" /> 
                                </div>
                            </div>
                            <div class="form-group col-sm-6">
                                <label for="exampleInputEmail">Manufacture Name</label>
                                <div class="input-group ">
                                    <span class="input-group-addon addon-red"><i class="fa fa-building-o"></i></span>
                                    <input type="text" id="update_manufacturer_name" value="" class="form-control" readonly />
                                    <input type="hidden" name="attribute_set[manufacturer_id]" id="update_manufacturer_id" value="" />
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="form-group col-sm-6">
                                <label for="exampleInputEmail">Product Groups</label>
                                <div class="input-group ">
                                    <span class="input-group-addon addon-red"><i class="fa fa-bars"></i></span>
                                    <select name="product_groups" id="product_groups" class="form-control">                                            
                                    </select>
                                </div>  
                            </div>
                            <div class="form-group col-sm-4">
                                <label for="exampleInputEmail">Locations</label>
                                <div class="input-group ">
                                    <span class="input-group-addon addon-red"><i class="fa fa-bars"></i></span>
                                    <select name="locations" id="locations" class="form-control selectpicker" data-live-search="true">                                            
                                    </select>
                                </div>
                            </div>
                            <div class="form-group col-sm-2">
                                <label for="exampleInputEmail"></label>
                                <div class="input-group ">
                                    <div class="input-group-addon">
                                        <i class="fa fa-plus" id="add_assign" style="cursor: pointer;"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <section class="tile">
                                <div class="panel panel-default">
                                    <!-- Default panel contents -->
                                    <div class="panel-heading">Location details</div>
                                    <!-- Table -->
                                    <table class="table" id="assign_data">
                                        <thead>
                                            <tr>
                                                <th>Product Group</th>
                                                <th>Location</th>
                                                <th style="width: 30px;">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody id="assigntable">
                                        </tbody>
                                    </table>
                                </div>
                            </section>
                        </div>
                        {{ Form::button('Save', array('class' => 'btn btn-primary', 'id' => 'update_assign_attribute_set_button')) }}
                        {{ Form::close() }}
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

    </div>


<script type="text/javascript">
    var sto=<?=$sto?>;
    $(document).ready(function () {
        $('#addQcAttribute [name="name"]').keyup(function () {
            //console.log('Hi');
            $('#addQcAttribute [name="attribute_code"]').val($('#addQcAttribute [name="name"]').val().replace(/\s+/g, '_').toLowerCase());
            $('[name="attribute_code"]').change();
        });
        $('#editDeliveryDetails [name="name"]').keyup(function () {
            //console.log('Hi');
            $('#editDeliveryDetails [name="product_id"]').val($('#editDeliveryDetails [name="name"]').val().replace(/\s+/g, '_').toLowerCase());
            $('#editDeliveryDetails [name="product_id"]').change();
        });


//validator
    
        $('#editDeliveryDetails').bootstrapValidator({
//        live: 'disabled',
            message: 'This value is not valid',
            feedbackIcons: {
                valid: 'glyphicon glyphicon-ok',
                invalid: 'glyphicon glyphicon-remove',
                validating: 'glyphicon glyphicon-refresh'
            },
            fields: {
                product_id: {
                    validators: {
                        notEmpty: {
                            message: 'Please select product name.'
                        }
                    }
                },
                qty: {
                    validators: {
                        notEmpty: {
                            message: 'Qty is Required'
                        },
                         numeric:{
                             message : 'The Qty should be numeric'
                        },
                    }
                }
            }
        }).on('success.form.bv', function (event) {
            event.preventDefault();
            var delivery_id = $('#delivery_id').val();
            var product_id = $('#product_id').val();
            var qty=$('#qty').val();
            $.ajax({
        type: "PUT",
        url: '/delivery/updateDeliveryDetails/'+ delivery_id +'/'+ product_id,
        data: $(this).serialize(),
        success: function(msg) {
        alert('Succesfully Updated');
        location.reload();
        }
    });
        }).validate({
            submitHandler: function (form) {
                return false;
            }
        });
        $('#basicvalCodeModal1').on('hide.bs.modal', function () {
            console.log('resetForm');
            $('#editDeliveryDetails').data('bootstrapValidator').resetForm();
            $('#editDeliveryDetails')[0].reset();
        });
        $('#addQcAttribute').bootstrapValidator({
//        live: 'disabled',
            message: 'This value is not valid',
            feedbackIcons: {
                valid: 'glyphicon glyphicon-ok',
                invalid: 'glyphicon glyphicon-remove',
                validating: 'glyphicon glyphicon-refresh'
            },
            fields: {
                product_id: {
                    validators: {
                        notEmpty: {
                            message: 'Please select product name.'
                        }
                    }
                },
                name: {
                    validators: {
                        notEmpty: {
                            message: 'Attribute Name is Required'
                        },
                    }
                },
                input_type: {
                    validators: {
                        callback: {
                            message: 'Please choose Input Type',
                            callback: function (value, validator, $field) {
                                var options = $('[id="input_type"]').val();
                                return (options != 0);
                            }
                        },
                        notEmpty: {
                            message: 'Input Type is required'
                        }
                    }
                },
                attribute_type: {
                    validators: {
                        callback: {
                            message: 'Please choose Attribute Type',
                            callback: function (value, validator, $field) {
                                var options = $('[id="attribute_type"]').val();
                                return (options != 0);
                            }
                        },
                        notEmpty: {
                            message: 'Attribute Type is required'
                        }
                    }
                }
            }
        }).on('success.form.bv', function (event) {
            event.preventDefault();
            ajaxCallPopup($('#addQcAttribute'));
            ajaxCall();
            return false;
        }).validate({
            submitHandler: function (form) {
                return false;
            }
        });
        $('#basicvalCodeModal').on('hide.bs.modal', function () {
            console.log('resetForm');
            $('#addQcAttribute').data('bootstrapValidator').resetForm();
            $('#addQcAttribute')[0].reset();
        });
        $('#addAttributeGroup').bootstrapValidator({
//        live: 'disabled',
            message: 'This value is not valid',
            feedbackIcons: {
                valid: 'glyphicon glyphicon-ok',
                invalid: 'glyphicon glyphicon-remove',
                validating: 'glyphicon glyphicon-refresh'
            },
            fields: {
                'attribute_group[name]': {
                    validators: {
                        remote: {
                            message: 'Name already exists.Please enter a new name',
                            url: '/product/checkGroupAvailability',
                            type: 'GET',
                            data: function (validator, $field, value) {
                                return {
                                    'manufacturer_id': validator.getFieldElements('attribute_group[manufacturer_id]').val()
                                };
                            },
                            delay: 2000     // Send Ajax request every 2 seconds
                        },
                        notEmpty: {
                            message: 'Attribute Group Name is Required'
                        }
                    }
                },
                update_manufacturer_name: {
                    validators: {
                        callback: {
                            message: 'Please choose Manufacturer Name',
                            callback: function (value, validator, $field) {
                                var options = $('[id="update_manufacturer_name"]').val();
                                return (options != 'Please select..');
                            }
                        },
                        notEmpty: {
                            message: 'Manufacturer Name is required'
                        }
                    }
                },
                'attribute_group[category_id]': {
                    validators: {
                        callback: {
                            message: 'Please choose Category Name',
                            callback: function (value, validator, $field) {
                                var options = $('[name="attribute_group[category_id]"]').val();
                                return (options != 0);
                            }
                        },
                        notEmpty: {
                            message: 'Please select Category.'
                        }
                    }
                }
            }
        }).on('success.form.bv', function (event) {
            event.preventDefault();
            //console.log('we r hwewe');
            ajaxCallPopup($('#addAttributeGroup'));
            setTimeout('updateGroups()', 2000);
            ajaxCall();
            return false;
        }).validate({
            submitHandler: function (form) {
                return false;
            }
        });
    
        $('#save_attribute_set').bootstrapValidator({
//        live: 'disabled',
            message: 'This value is not valid',
            feedbackIcons: {
                valid: 'glyphicon glyphicon-ok',
                invalid: 'glyphicon glyphicon-remove',
                validating: 'glyphicon glyphicon-refresh'
            },
            fields: {
                'attribute_set[attribute_set_name]': {
                    validators: {
                        remote: {
                            message: 'Name already exists.Please enter a new name',
                            url: '/product/checkSetAvailability',
                            type: 'GET',
                            data: function (validator, $field, value) {
                                return {
                                    'manufacturer_id': validator.getFieldElements('attribute_set[manufacturer_id]').val()
                                };
                            },
                            delay: 2000     // Send Ajax request every 2 seconds
                        },
                        notEmpty: {
                            message: 'Attribute Set Name is Required'
                        }
                    }
                },
                update_manufacturer_name: {
                    validators: {
                        callback: {
                            message: 'Please choose Manufacturer Name',
                            callback: function (value, validator, $field) {
                                var options = $('[name="update_manufacturer_name"]').val();
                                return (options != 'Please select..');
                            }
                        },
                        notEmpty: {
                            message: 'Manufacturer Name is required'
                        }
                    }
                },
                'attribute_set[category_id]': {
                    validators: {
                        callback: {
                            message: 'Please choose Category Name',
                            callback: function (value, validator, $field) {
                                var options = $('[name="attribute_set[category_id]"]').val();
                                return (options != 0);
                            }
                        },
                        notEmpty: {
                            message: 'Please select Category.'
                        }
                    }
                }
            }
        }).on('success.form.bv', function (event) {
            event.preventDefault();
            $('#save_attribute_set_button').prop('disabled', true);
            var url = '/product/saveattributeset';
            var inherit = $('[name="attribute_set[inherit_from]"]').prop('checked');
            if ( inherit )
            {
                inherit = 1;
            } else {
                inherit = 0;
            }
            var selectedAttr = new Array();
            var selectedAttrArray = new Array();
            $('#attribute_id div').each(function (i, v) {
                selectedAttr.push($(v).attr('value'));
                selectedAttrArray.push($(v).attr('key'));
            });
            // selectedAttr = selectedAttr.substr(1,selectedAttr.length);
            var temp = {
                attribute_set_name: $('[name="attribute_set[attribute_set_name]"]').val(),
                category_id: $('[name="attribute_set[category_id]"]').val(),
                manufacturer_id: $('#main_manufacturer_id').val(),
                is_active: $('[name="attribute_set[is_active]"]').val(),
                //inherit_from: $('[name="attribute_set[inherit_from]"]').val(),
                attribute_id: selectedAttr,
                //sort_order: selectedAttrArray,
                inherit_from: inherit
            };
            var posting = $.post(url, {attribute_set: temp});
            // Put the results in a div
            posting.done(function (data) {
                console.log(data['message']);
                if ( data['status'] == true )
                {
                    $('.close').trigger('click');
                    alert(data['message']);
                    //location.reload();
                    ajaxCall();
                } else {
                    alert(data['message']);
                }
                //location.reload();
            });
            $('#save_attribute_set_button').prop('disabled', false);
            return false;
        }).validate({
            submitHandler: function (form) {
                return false;
            }
        });

        $('#editqcAttributewithProduct').bootstrapValidator({
//        live: 'disabled',
            message: 'This value is not valid',
            feedbackIcons: {
                valid: 'glyphicon glyphicon-ok',
                invalid: 'glyphicon glyphicon-remove',
                validating: 'glyphicon glyphicon-refresh'
            },
            fields: {
                product_id: {
                    validators: {
                        notEmpty: {
                            message: 'Product Name is Required'
                        }
                    }
                }
            }
        }).on('success.form.bv', function (event) {
            event.preventDefault();
            //ajaxCall();
            return false;
        })
    });
    $('#editqcAttributewithProductmodal').on('hide.bs.modal', function () {
        console.log('resetForm');
        $('#editqcAttributewithProduct').data('bootstrapValidator').resetForm();
        $('#editqcAttributewithProduct')[0].reset();
        $('[id="update_manufacturer_name"]').val($('#main_manufacturer_id option:selected').text());
        $('[id="update_manufacturer_id"]').val($('#main_manufacturer_id option:selected').val());
    });
    $('#editqcAttributewithProductmodal').on('show.bs.modal', function () {
        $('[id="update_manufacturer_name"]').val($('#main_manufacturer_id option:selected').text());
        $('[id="update_manufacturer_id"]').val($('#main_manufacturer_id option:selected').val());
    });
//validator
     $(document).ready(function () {
        var $sourceFields = $("#Selectattribute1");
        var $destinationFields = $("#attribute_id1");
        var $chooser = $("#fieldChooser").fieldChooser(Selectattribute1, attribute_id1);
    });
    $(document).ready(function () {
        var $sourceFields = $("#Selectattribute");
        var $destinationFields = $("#attribute_id");
        var $chooser = $("#fieldChooser").fieldChooser(Selectattribute, attribute_id);
    });
    //alert('jeerasdf');
    $(document).ready(function ()
    {
        $('#main_manufacturer_id').trigger('change');
        makePopupAjax($('#basicvalCodeModal'),'product_id');
        //alert('4345');
        makePopupEditAjaxNew($('#basicvalCodeModal1'),'delivery_id','product_id');
        //makePopupEditAjax($('#editqcAttributewithProductmodal'), 'product_id');
               // makePopupEditAjax($('#basicvalCodeModal1'), 'delivery_id','product_id');

    });
    //alert('adfadffsadfsad');
     $(document).ready(function () {
      // Set timer to check if user is idle
      var idleTimer;
      $(this).mousemove(function(e){
        // clear prior timeout, if any
        window.clearTimeout(idleTimer);
     
        // create new timeout (3 mins)
        //idleTimer = window.setTimeout(isIdle, 300000);
      });
     
      function isIdle() {
        alert("Session Expired. Please Login");
        // window.location.assign("http://poc.esealcom.com");
        window.location.assign("http://127.0.0.1.8000/login.com");
      }
});

    function ajaxCall(){
    var manufacturerId = $('#main_manufacturer_id').val();  
        $('#manuId').hide();

    var arg="<?php echo $sto ?>"; 
    //alert(arg);

    var url = "/delivery/getElementdata/"+arg;
    // console.log("hai");
    // $("#loading").show();
    $.get(url,function(response){
        //alert(response);
        var res = response['masterData'];
        //alert(res);
        var res1 = response['detailsData'];
        var df=[
                { name: 'DeliveryId', type: 'string' },
                 { name: 'document_no', type: 'string' },
                { name: 'name', type: 'string' },
                { name: 'DestLoc', type: 'string' },
                { name: 'date', type: 'string' } ,
                { name: 'status', type: 'string' }                
            ];
        var clms=[
                { text: 'STO No', datafield: 'DeliveryId', filtertype: 'input', width:'20%'},
                { text: 'Reference No', datafield: 'document_no', filtertype: 'input', width:'20%'},
                { text: 'Type', datafield: 'name', filtertype: 'input', width:'20%'},
                { text: 'Destination Location', datafield: 'DestLoc', filtertype: 'input', width:'20%'},
                { text: 'Date', datafield: 'date', filtertype: 'input', width:'20%'}
              ];
            if(!sto){
              df=[
                { name: 'document_no', type: 'string' },
                { name: 'DeliveryId', type: 'string' },
                { name: 'name', type: 'string' },
                { name: 'DestLoc', type: 'string' },
                { name: 'date', type: 'string' } ,
                { name: 'status', type: 'string' }                
            ];  

        clms=[
                { text: 'Document No', datafield: 'document_no', filtertype: 'input', width:'20%'},
                { text: 'Sto No', datafield: 'DeliveryId', filtertype: 'input', width:'20%'},
                { text: 'Type', datafield: 'name', filtertype: 'input', width:'20%'},
                { text: 'Destination Location', datafield: 'DestLoc', filtertype: 'input', width:'20%'},
                { text: 'Date', datafield: 'date', filtertype: 'input', width:'20%'}

              ];
            }


        var source =
        {
            id: 'id',
            datafields: df, 
            datatype: "json",
            localdata: res,
            pagesize:20,
            pager: function (pagenum, pagesize, oldpagenum) {
                // callback called when a page or page size is changed.
            }
        };
        
        var jobAdapter = new $.jqx.dataAdapter(source);
        
        var jobDetailSource = {
            
            datatype: "json",
            localdata: res1,
            async: false
            
        }
                
        var jobDetailAdapter= new $.jqx.dataAdapter(jobDetailSource, { autoBind: true });
        jobsDetails = jobDetailAdapter.records;
        var nestedGrids = new Array();
        //alert('hell1');
        console.log(jobsDetails);
        var initrowdetails = function (index, parentElement, gridElement, record) {
            
            var id = record.uid.toString();
            var grid = $($(parentElement).children()[0]);
            nestedGrids[index] = grid;
            var filtergroup = new $.jqx.filter();
            var filter_or_operator = 1;
            var filtervalue = id;
            var filtercondition = 'equal';
            var filter = filtergroup.createfilter('stringfilter', filtervalue, filtercondition);
            // fill the orders depending on the id.
          //  alert('before hell2');
            var jobbyid = [];
            for (var m = 0; m < jobsDetails.length; m++) {
                var result = filter.evaluate(jobsDetails[m]["id"]);
                if (result)
                    jobbyid.push(jobsDetails[m]);
            } 

            var jobsource = {
                datafields: [
                    { name: 'PName', type: 'string' },
                    { name: 'lineNo', type: 'number' },
                    { name: 'Qty', type: 'number' },
                    { name: 'actions', type: 'string' }

                 ], 
                id: 'id',
                localdata: jobbyid
            }
            //alert('mid');
            var nestedGridAdapter = new $.jqx.dataAdapter(jobsource);
            if (grid != null) {
                grid.jqxGrid({
                    source: nestedGridAdapter, 
                    width: 650,
                    height:200,
                    rowsheight: 30,
                    filterable: true,
                    showfilterrow: true,
                    sortable: true,    
                    columns: [
                      { text: 'Product Name', datafield: 'PName', filtertype:'input', width: 270 },
                      { text: 'Line No', datafield: 'lineNo',width: 100 },
                      { text: 'Qty', datafield: 'Qty', width: 120},                     
                      { text: 'Actions', datafield: 'actions',  width:165}


                   ]
                });
            }
        }
$("#Attributegrid").jqxGrid(
        {
            width: '100%', //change
            height: '100%',
            rowsheight: 30,
            pageable: true,
            altrows: true,
            source: jobAdapter,
            rowdetails: true,   
            columnsresize: true,
            filterable: true,
            showfilterrow: true,
            sortable: true,
            pagesizeoptions: ['20','50','80','100'],
            rowdetailstemplate: { rowdetails: "<div id='grid' style='margin: 10px;'></div>", rowdetailsheight: 220, rowdetailshidden: true },           
            initrowdetails: initrowdetails,
           
                    columns:clms,ready:function(){$("#loading").hide();
          }     

        });
        $("#loading").hide();
    });     
//alert('hell4');
 }

    function makePopupAttributeAjax($el, primaryKey)
    {
        $el.on('shown.bs.modal', function (e) {
            var url = $(e.relatedTarget).data('href'),
                    $this = $(this),
                    $form = $this.find('form'),
                    key = primaryKey || 'attribute_group_id';

            $.get(url, function (data) {
                $.each(data, function (i, v) {
                    $form.find('[name="' + i + '"]').val(v);
                });
            });
        });
    }
    
    function delwithProduct(del_id, product_id)
    {
        var dec = confirm("Are you sure you want to Delete ?");
        if(dec == true){
                    $.ajax({
                        url: '/delivery/delwithProduct'+'/'+del_id+'/'+product_id,
                        type: 'GET',
                        success: function (result)
                        {
                            if ( result == 1 ) {
                                alert('Succesfully Deleted !!');
                             location.reload();
                            } else {
                                alert(result);
                            }
                        }
                    });
                }
    }
    

    function getAttributeGroupName(productId) {
        $('#product_id').val(productId);
    }

    function getAssignAttribute(attributeSetId)
    {
        $('#assign_attribute_set_id').val(attributeSetId);
        $('#attribute_set_id_add_attribute').val(attributeSetId);
        $('#assign_attribute_set_name').val($('#attribute_set_id_add_attribute option:selected').text());
    }

    function loadAssignData()
    {
        // var manufacturer_id = $('#main_manufacturer_id').val();
        // var url = '/product/getelementdata';
        // // Send the data using post
        // var posting = $.post(url, {data_type: 'locations_groups', data_value: manufacturer_id});
        // // Put the results in a div
        // posting.done(function (data) {
        //     var result = JSON.parse(data);
        //     var temp;
        //     var fieldId;
        //     $.each(result, function (field, data) {
        //         if ( field == 'locations' )
        //         {
        //             fieldId = 'locations';
        //         } else {
        //             fieldId = 'product_groups';
        //         }
        //         if ( data != '' )
        //         {
        //             $.each(data, function (key, value) {
        //                 $('#' + fieldId).append('<option value="' + value['id'] + '">' + value['name'] + '</option>');
        //             });
        //         }
        //     });
        // });
    }

    $('#main_manufacturer_id').change(function () {
        $('[id="update_manufacturer_name"]').val($('#main_manufacturer_id option:selected').text());
        $('[id="update_manufacturer_id"]').val($(this).val());
        ajaxCall($(this).val());
        updateGroups();
        loadAssignData();
    });
    function changeproductId(productId)
    {
     $('#product_id').val(productId);
    }
    function changeproductIdAddqc(productId)
    {
     
     $('#product_idadd').val(productId);
     $('#product_idadd').selectpicker('refresh');
     
    }
    function updateGroups()
    {
        // $('[name="attribute_group_id"]').empty();
        // var manufacturer_id = $('#main_manufacturer_id').val();
        // var url = '/product/getelementdata';
        // // Send the data using post
        // var posting = $.post(url, {data_type: 'attributeGroups', data_value: manufacturer_id});
        // // Put the results in a div
        // posting.done(function (data) {
        //     var result = JSON.parse(data);
        //     $('[name="attribute_group_id"]').append('<option value="" selected="true">Please select... </option>');
        //     $.each(result, function (key, value) {
        //         $('[name="attribute_group_id"]').append('<option value="' + value['attribute_group_id'] + '">' + value['name'] + '</option>');
        //     });
        // });
    }
    
    $('#update_assign_attribute_set_button').click(function () {
        var url = $('#assignGroupsLocations').attr('action');
        var postData = $('#assignGroupsLocations').serializeArray();
        var posting = $.post(url, postData );
        posting.done(function (data) {
            /*var result = JSON.parse(data);
            console.log(data);*/
            console.log(data['message']);
            if ( data['status'] == true )
            {
                $('.close').trigger('click');
                alert(data['message']);
                //location.reload();
                ajaxCall();
            } else {
                alert(data['message']);
            }            
        });
        //getAttributes();
    });
//Edit
    $('#editqcAttributewithProductmodal').on('show.bs.modal', function (e) {

        var manufacturer_id = $('#main_manufacturer_id').val();
        // console.log(manufacturer_id);
        var product_id = $('#product_id').val();
        //alert(product_id);
        var url = '/product/getQcAttributedata/' + manufacturer_id + '/' + product_id;
        //console.log('Removing already selected attributes: '+$('#Removeattribute1').val());
        $('#Removeattribute1').val('0');
        var posting = $.get(url);
        $('#Selectattribute1').html('');
        $('#attribute_id1').html('');
        posting.done(function (data) {
            var result = JSON.parse(data);
            //console.log(result);            
            $.each(result.unselected, function (key, value) {
                var key = key.substr(1, key.length);
                //$('#Selectattribute1').append('<option value="' + key + '">' + value + '</option>'); 
                $('#Selectattribute1').append('<div class="fc-field" value="' + key + '">' + value + '</div>');
            });
            $.each(result.selectedAttr, function (key, value) {
                var key = key.substr(1, key.length);
                //console.log(key);
                /*$('#attribute_id1').append('<option value="' + key + '">' + value + '</option>');*/
                $('#attribute_id1').append('<div class="fc-field" value="' + key + '">' + value + '</div>');
                //console.log($('#attribute_id').html());
            });
            $('#Removeattribute1').val('0');
            $('#attribute_id1 div').each(function (i, v) {
                $('#formattributes1').val($('#formattributes1').val() + ',' + $(v).attr('value'));
            });
        });
    });
//Edit
    $('#update_attribute_set_button').click(function (e) {
        e.preventDefault();
        e.stopPropagation();
        var formattributes1 = '';
        $('#attribute_id1 div').each(function (i, v) {
            formattributes1 += ',' + $(v).attr('value');
        });
        formattributes1 = formattributes1.substr(1, formattributes1.length);
        $('#formattributes1').val(formattributes1);
        $('#editqcAttributewithProductmodal form').submit();
        ajaxCall();
    });
    $('[name="input_type"').change(function () {
        var inputTypeValue = $(this).val();
        if ( inputTypeValue == 'select' || inputTypeValue == 'multiselect' )
        {
            $('#option-button').trigger('click');
        }
    });
    $('#add_new_option').on('click', function () {
        var $template = $('#option_data');
        $clone = $template.clone();
        $('#option_data').before($clone.removeAttr('id').removeAttr('style'));
    });
    $('#assignAttributeSet').on('show.bs.modal', function (e) {
        var attribute_set_id = $('#assign_attribute_set_id').val();
        //console.log(attribute_set_id);
        var url = '/product/getAssignGroupDetails/'+ attribute_set_id;
        var posting = $.get(url); 
        posting.done(function (data) {
            //console.log(data);
            $('#assigntable').empty();

            $.each(data, function (key, value) {
                var jsonArg = new Object();
                jsonArg.product_group = value['product_group_id'];
                jsonArg.location_val = value['location_id'];
                var hiddenJsonData = new Array();
                hiddenJsonData.push(jsonArg);                
                $("#assign_data").append('<tr><td scope="row" id="product_groups_text">' + value['productgroup'] + '</td><td id="location_text">' + value['location_name']
                            + '</td><td><a href="javascript:void(0);" class="check-toggler" id="remCF"><span class="badge bg-red"><i class="fa fa-trash-o"></i></span></a><input type="hidden" name="assign_locations[]" value=' + "'" + JSON.stringify(jsonArg) + "'" + ' /></td></tr>');
            });
        });               
    });    
    function postData()
    {
        console.log('we are in view');
        return;
    }

        $('#add_qcattributes_form_excel').submit(function(event){
        event.preventDefault();
        $('#add_qcattributes_excel_button').prop('disabled', true);
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
                $('#add_qcattributes_form_excel')[0].reset();
                $('.close').trigger('click');
                location.reload();
                //window.location.href= "/product/getallqcattributes/" + manufacturerId;
            },
            cache: false,
            contentType: false,
            processData: false
        });
        $('#add_qcattributes_excel_button').prop('disabled', false);
    });


        function removeActions($option)
        {
            $option.parent().parent().remove();
            //$option.closest('div #option_data');
            //console.log($option);
        }
        $('#save-options').click(function () {
            var keyData = [];
            var valueData = [];
            var sortOrderData = [];
            $('[name="key[]"]').each(function (elem) {
                if ( $(this).val() != '' )
                {
                    keyData.push($(this).val());
                }
            });
            $('[name="value[]"]').each(function (elem) {
                if ( $(this).val() != '' )
                {
                    valueData.push($(this).val());
                }
            });
            $('[name="sort_order[]"]').each(function (elem) {
                if ( $(this).val() != '' )
                {
                    sortOrderData.push($(this).val());
                }
            });
            var responseData = {};
            for (var i = 0, len = keyData.length; i < len; i++) {
                var keyValue = '';
                var dataValue = '';
                var sortValue = 0;
                if ( keyData[i] != '' || keyData[i] != 'undefined' )
                {
                    keyValue = keyData[i];
                }
                if ( valueData[i] != '' || valueData[i] != 'undefined' )
                {
                    dataValue = valueData[i];
                }
                if ( sortOrderData[i] != '' || sortOrderData[i] != 'undefined' )
                {
                    sortValue = sortOrderData[i];
                }
                responseData[i] = keyValue + ';' + dataValue + ';' + sortValue;
            }
            var modelId = $('[class="modal fade in"').attr('id');
            $('#' + modelId).find('[name="option_values"]').val(JSON.stringify(responseData));
            $('#option-close').trigger('click');
        });
        $('#add_assign').click(function(){
            var product_groups_val = $('#product_groups').val();
            var product_groups_text = $('#product_groups option:selected').text();
            var location_val = $('#locations').val();
            var location_text = $('#locations option:selected').text();
            
            if(product_groups_val == 0 || product_groups_val == '')
            {
                alert('Please select attribtue set.');
            }else if(location_val == 0 || location_val == '')
            {
                alert('Please select locations.');
            }else{
                var attributeSetElements = new Array();
                $('[id="product_groups_text"]').each(function(){
                    attributeSetElements.push($(this).text()+'##'+$(this).next('td#location_text').text());
                });
                var temp;
                temp = product_groups_text+'##'+location_text;
                if(attributeSetElements.length > 0 && $.inArray(temp, attributeSetElements) >= 0)
                {
                    alert('This element already added.');
                }else{            
                    var jsonArg = new Object();
                    jsonArg.product_group = product_groups_val;
                    jsonArg.location_val = location_val;
                    var hiddenJsonData = new Array();
                    hiddenJsonData.push(jsonArg);

                    $("#assign_data").append('<tr><td scope="row" id="product_groups_text">' + product_groups_text + '</td><td id="location_text">' + location_text
                            + '</td><td><a href="javascript:void(0);" class="check-toggler" id="remCF"><span class="badge bg-red"><i class="fa fa-trash-o"></i></span></a><input type="hidden" name="assign_locations[]" value=' + "'" + JSON.stringify(jsonArg) + "'" + ' /></td></tr>');
                }
            }
        });
        $("#assign_data").on('click', '#remCF', function () {
            $(this).parent().parent().remove();
        });
    </script>
    @stop            