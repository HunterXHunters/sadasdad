@extends('layouts.default')
@extends('layouts.header')
@extends('layouts.sideview')
@section('content')
<script src="{{URL::asset('scripts/select2.full.js')}}"></script>
<link href="{{ URL::asset('css/select2.min.css') }}" rel="stylesheet" type="text/css" />
<style>
.checkbox input[type=checkbox], .checkbox-inline input[type=checkbox], .radio input[type=radio], .radio-inline input[type=radio]{margin-left:-20px !important; position:absolute;}
h6{
    font-size:17px;}
    .col-sm-1 {
   padding-right:10px !important;
   padding-left:0px ;
}
.col-sm-3 {
   padding-right:0px !important;

}
</style>
<div class="box">
              <div class="box-header">
                <h3 class="box-title"><strong>New </strong>  Product</h3>
              </div>
               
               <div class="col-sm-12">
                 <div class="tile-body nopadding">                  
                    
                    

<!-- Page content -->
<!-- Page content -->
    {{ Form::open(array('url' => 'products/save', 'method' => 'POST', 'files'=>true, 'id' => 'product_creation')) }}
   <?php //echo "<pre>";print_R($data);die;  ?>
    <div class="main pricemaster">           
        <div id="error_message" class="error">
            @if($errors->any())
            <?php echo $errors->first(); ?>
            @endif
        </div>
        <div class="row margleft">
            <div class="col-md-12">
                <!-- tile body -->
                <div class="tile-body">
                    <section class="tile">
                        <div class="panel-group accordion" id="accordion">
                            <div class="panel panel-default">
                                <div class="panel-heading">
                                    <h4 class="panel-title">
                                        <a data-toggle="collapse" data-parent="#accordion" href="#collapseOne" id="one">
                                            Manufacturer details
                                        </a>
                                    </h4>
                                </div>
                                <div id="collapseOne" class="panel-collapse collapse in">
                                    <div class="panel-body">
                                        <div class="row">
                                            <div class="form-group col-sm-6">
                                                <label for="exampleInputEmail">Manufacturer Name</label>
                                                <div id="selectbox">
                                                    <select class="form-control" data-live-search="true" name="product[manufacturer_id]" id="main_manufacturer_id" parsley-trigger="change" parsley-required="true" parsley-error-container="#selectbox">
                                                        <option value="">Please choose</option>
                                                        @foreach ($data['general']['manufacturer_data']['options'] as $key => $value)
                                                        <option value="{{ $key }}">{{ $value }}</option>
                                                        @endforeach
                                                    </select>
                                                    <input type="hidden" name="product[created_from]" value="Website" />
                                                </div>
                                            </div>
                                            <div class="form-group col-sm-6">
                                                <button type="button" class="btn btn-primary " onClick="collapsDiv('two');" style="margin-top:22px">Next</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="panel panel-default">
                                <div class="panel-heading">
                                    <h4 class="panel-title">
                                        <a data-toggle="collapse" data-parent="#accordion" href="#collapseTwo" id="two">
                                            Product detail
                                        </a>
                                    </h4>
                                </div>
                                <div id="collapseTwo" class="panel-collapse collapse">
                                    <div class="panel-body">
                                            <div class="row">
                                                <div class="form-group col-sm-6" style="padding:0px 0px !important" id="upload_field">
                                                    <div class="form-group col-sm-4" >
                                                        <label for="exampleInputPassword1">Product Image</label>
                                                        <div class="image-block">
                                                        </div>
                                                        <div class="input-group input-group-sm">
                                                            <span class="btn btn-success fileinput-button">
                                                                <i class="glyphicon glyphicon-plus"></i>
                                                                <span>Upload Image...</span>
                                                                <input id="fileupload" type="file" name="files[]" multiple>
                                                            </span>
                                                        </div>
                                                        <div class="input-group-sm">
                                                            <table role="presentation" class="table table-striped"><tbody id="files" class="files"></tbody></table>
                                                        </div>
                                                    </div>
                                                    <div class="form-group col-sm-8 ">
                                                        <label for="Name">Name*</label>
                                                        <div class="validator-Error">
                                                        <input type="text" id="product_name" class="form-control" placeholder="Username" >
                                                        </div>
                                                        <br>
                                                        <input type="hidden" id="product_title" value="">
                                                        <div class="none">
                                                        <label for="Description">Description</label>
                                                        <textarea class="form-control" id="product_description" rows="3" style="height:107px;"></textarea></div>
                                                        <label for="Name">Model Name</label>
                                                        <input type="text" id="model_name" class="form-control" placeholder="Model Name" name="product[model_name]" >
                                                    </div>
                                                </div>
                                                <div class="form-group col-sm-6">
                                                    <label for="Brand Name">Brand Name</label>
                                                    <input type="text" class="form-control" id="brand_name" placeholder="Brand Name"><br>
                                                    <div class="validator-Error">
                                                    <label for="Product Type">Product Type*</label>
                                                    <div id="selectbox">
                                                        <select class="form-control" data-live-search="true" id="product_type_id" parsley-trigger="change" parsley-required="true" parsley-error-container="#selectbox">
                                                        </select>
                                                    </div>
                                                    </div>
                                                    <br>
                                                    <div class="validator-Error">
                                                    <label for="Business Unit">Business Unit*</label>
                                                    <div id="selectbox">
                                                        <div class="input-group">
                                                            <select class="form-control" data-live-search="true" id="business_unit_id" parsley-trigger="change" parsley-required="true" parsley-error-container="#selectbox">
                                                                <option value="">Please choose</option>
                                                            </select>                                                            
                                                            <div class="input-group-addon">
                                                                <?php if(isset($data['allow_add_business_unit']) && $data['allow_add_business_unit'] == 1){ ?>
                                                                <i class="fa fa-plus" data-toggle="modal" data-target="#add_business_unit" style="cursor: pointer;"></i>
                                                                <?php } ?>
                                                            </div> 
                                                            </div>                                                           
                                                        </div>
                                                    </div>
                                                    <br />
                                                    <label for="Group">Group</label>
                                                    <div id="selectbox">
                                                        <select class="form-control " data-live-search="true" id="group_id" parsley-trigger="change" parsley-required="true" parsley-error-container="#selectbox">
                                                            <option value="">Please choose</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row">

                                                <div class="form-group col-sm-6 validator-Error">
                                                    <label for="Category">Category*</label>
                                                    <div id="selectbox">
                                                        <select class="form-control" data-live-search="true"id="product_category"  parsley-trigger="change" parsley-required="true" parsley-error-container="#selectbox" >
                                                            <option value="">Please choose</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                    <div class="form-group col-sm-6 ">
                                                        <label for="exampleInputEmail">MRP</label>
                                                    <input type="number" class="form-control" id="mrp" placeholder="MRP">
                                                        
                                                    
                                                </div>
                                                
                                           </div>
                                            
                                            <h6>Dimensions</h6>

                                            <div class="row">
                                                <div class="form-group col-sm-6" style="padding:0px 0px !important">
                                                    <div class="form-group col-sm-3" style="padding-right: 15px !important;">
                                                        <label for="Height">Height</label>
                                                        <input type="text" class="form-control" id="height" name="product[height]" placeholder="Height">
                                                    </div>
                                                    <div class="form-group col-sm-3">
                                                        <label for="Width">Width</label>
                                                        <input type="text" class="form-control" id="width" name="product[width]" placeholder="Width">
                                                    </div>
                                                    <div class="form-group col-sm-3">
                                                        <label for="Width">Length</label>
                                                        <input type="text" class="form-control" id="length" name="product[length]" placeholder="Length">
                                                    </div>
                                                    <div class="form-group col-sm-3 validator-Error">
                                                        <label for="Width">UOM*</label>
                                                        <select  class="form-control" id="uom_class_id" name="product[uom_class_id]" parsley-trigger="change" parsley-required="true" parsley-error-container="#selectbox">
                                                        </select>
                                                    </div>
                                                    <div class="form-group col-sm-3" style="padding-right: 15px !important;">
                                                        <label for="Height">UOM unit Value</label>
                                                        <input type="text" class="form-control" id="uom_unit_value" name="product[uom_unit_value]" placeholder="UOM Value">
                                                    </div>
                                                    <div class="form-group col-sm-3">
                                                        <label for="Width">Field1</label>
                                                        <input type="text" class="form-control" id="field1" name="product[field1]" placeholder="Field1">
                                                    </div>
                                                    <div class="form-group col-sm-3">
                                                         <label for="Width">Field2</label>
                                                        <input type="text" class="form-control" id="field2" name="product[field2]" placeholder="Field2">
                                                    </div>
                                                    <div class="form-group col-sm-3">
                                                         <label for="Width">Field3</label>
                                                        <input type="text" class="form-control" id="field3" name="product[field3]" placeholder="Field3">
                                                    </div>
                                                     <div class="form-group col-sm-3">
                                                         <label for="Width">Field4</label>
                                                        <input type="text" class="form-control" id="field4" name="product[field4]" placeholder="Field4">
                                                    </div>
                                                     <div class="form-group col-sm-3">
                                                         <label for="Width">Field5</label>
                                                        <input type="text" class="form-control" id="field5" name="product[field5]" placeholder="Field5">
                                                    </div>
                                                </div>
                                                <div class="form-group col-sm-6">
                                                    <div class="form-group col-sm-3">
                                                        <div class="checkbox" style="padding-left:0px !important">                                                                                                                 
                                                            <label class="col-sm-4 control-label" for="is_traceable" style="width: 100%;"> <input type="checkbox" id="is_traceable" name="product[is_traceable]" parsley-group="mygroup" parsley-trigger="change" parsley-required="true" parsley-mincheck="2" parsley-error-container="#myproperlabel " class="parsley-validated">  Is Traceable</label>
                                                        </div>
                                                        <div class="checkbox" style="padding-left:0px !important">                                                        
                                                            <label class="col-sm-4 control-label" for="is_gds_enabled" style="width: 100%;"> 
                                                                <input type="checkbox" id="is_gds_enabled" name="product[is_gds_enabled]" parsley-group="mygroup" parsley-trigger="change" parsley-required="true" parsley-mincheck="2" parsley-error-container="#myproperlabel " class="parsley-validated"> Is GDS enabled</label>
                                                        </div>
                                                    </div>                                                    
                                                    <div class="form-group col-sm-9 validator-Error">
                                                        <label for="ERP code">ERP code*</label>
                                                        <input type="text" id="material_code" minlength="8"  maxlength="32" name="product[material_code]" class="form-control" placeholder="ERP code" />
                                                    </div>
                                                </div>
                                                
                                            </div>                                            
                                            <div class="row">
                                                <button type="button" class="btn btn-primary" onClick="collapsDiv('one')" style="margin:0px 15px" >Back</button>
                                                <button type="button" class="btn btn-primary" onClick="collapsDiv('three', 1)" >Next</button>
                                            </div>
                                    </div>
                                </div>
                            </div>
                            <div class="panel panel-default">
                                <div class="panel-heading">
                                    <h4 class="panel-title">
                                        <a data-toggle="collapse" data-parent="#accordion" href="#collapseThree" id="three">
                                            Packages & pallets
                                        </a>
                                    </h4>
                                </div>
                                <div id="collapseThree" class="panel-collapse collapse">
                                    <div class="panel-body">
                                        <!-- tile body -->
                                            <div class="row">
                                                <div class="form-group col-sm-6">
                                                    <label for="Primary Capacity">Primary Capacity</label>
                                                    <input type="text" class="form-control" id="primary_capacity" placeholder="Primary Capacity">
                                                </div>
                                                <div class="form-group col-sm-4" style="padding-right:0px;" >
                                                    <label for="Primary Product Weight">Primary Product Weight</label>
                                                    <input type="text" class="form-control" id="primary_product_weight" placeholder="Primary Product Weight" />                                                    
                                                </div>
                                                <div class="form-group col-sm-2" style="padding-left:0px !important;">
                                                    <label for="Primary Product Weight"></label>
                                                    <select  class="form-control" style=" margin-top:5px;" id="weight_class_id" parsley-trigger="change" parsley-required="true" parsley-error-container="#selectbox">                                                        
                                                    </select>
                                                </div>
                                            </div>
                                            <h6>Package Details</h6>
                                            <div class="row">
                                                <div class="form-group col-sm-2">
                                                    <label for="Level">Level</label>
                                                    <div id="selectbox">
                                                        <select  class="list-unstyled selectpicker"  id="package_level" parsley-trigger="change" parsley-required="true" parsley-error-container="#selectbox" style="display: none;">
                                                            <option value="">Please choose</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="form-group col-sm-1">
                                                    <label for="Capacity">Capacity</label>
                                                    <input type="text" class="form-control" id="package_capacity" placeholder="Capacity">
                                                </div>
                                                <div class="form-group col-sm-1">
                                                    <label for="Length">Length</label>
                                                    <input type="text" class="form-control" id="package_length" placeholder="Length">
                                                </div>
                                                <div class="form-group col-sm-1">
                                                    <label for="Breadth">Breadth</label>
                                                    <input type="text" class="form-control" id="package_breadth" placeholder="Breadth">
                                                </div>
                                                <div class="form-group col-sm-1">
                                                    <label for="Height">Height</label>
                                                    <input type="text" class="form-control" id="package_height" placeholder="Height">
                                                </div>
                                                <div class="form-group col-sm-1">
                                                    <label for="UOM">UOM</label>
                                                    <select  class="form-control" id="package_weight_class_id" parsley-trigger="change" parsley-required="true" parsley-error-container="#selectbox">                                                        
                                                    </select>
                                                </div>
                                                <div class="form-group col-sm-3">
                                                    <label for="exampleInputEmail">Multiple select box *</label>
                                                    <div id="myproperlabel">
                                                        <div class="form-group col-sm-7" style="margin-bottom:0px !important">
                                                            <div class="checkbox">
                                                                <input type="checkbox" id="is_shipper_pack" name="shipper_pack" parsley-group="mygroup" parsley-trigger="change" parsley-required="true" parsley-mincheck="2" parsley-error-container="#myproperlabel .last" class="parsley-validated">
                                                                <label for="is_shipper_pack">Is Shipping Pack</label>
                                                            </div>
                                                        </div>
                                                        <div class="form-group col-sm-5" style="margin-bottom:0px !important">
                                                            <div class="checkbox">
                                                                <input type="checkbox" id="is_pallet" name="pallet" parsley-group="mygroup" parsley-trigger="change" parsley-required="true" parsley-mincheck="2" parsley-error-container="#myproperlabel .last" class="parsley-validated">
                                                                <label for="is_pallet">Is Pallet</label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="form-group col-sm-2">
                                                    <label for="Pallet stack height">Pallet stack height</label>
                                                    <input type="text" class="form-control" id="pallet_stack_height" placeholder="Pallet stack height" readonly />
                                                </div>

                                            </div>
                                            <div class="row">
                                                <div class="form-group col-sm-2" style="margin-bottom:20px;">
                                                    <button type="button" id="add_packages" class="btn btn-primary">Add Package</button>                        
                                                </div>
                                            </div>
                                            <div class="row">
                                                <section class="tile">
                                                    <div class="panel panel-default">
                                                        <!-- Default panel contents -->
                                                        <div class="panel-heading">Package Details</div>
                                                        <!-- Table -->
                                                        <table class="table" id="package_data">
                                                            <thead>
                                                                <tr>
                                                                    <th>Level</th>
                                                                    <th>Capacity</th>
                                                                    <th>Length</th>
                                                                    <th>Breadth</th>
                                                                    <th>Height</th>
                                                                    <th>Is Shipper Pack</th>
                                                                    <th>Is Pallet</th>
                                                                    <th style="width: 30px;">Action</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </section>
                                            </div>
                                            <div class="row" style="padding-top:15px ">
                                                <button type="button" class="btn btn-primary" onClick="collapsDiv('two')" style="margin:0px 15px;">Back</button>
                                                <button type="button" class="btn btn-primary" onClick="collapsDiv('four')">Next</button>
                                            </div>
                                        <!-- /tile body -->  
                                    </div>
                                </div>
                            </div>
                            
                                            
                            <div class="panel panel-default">
                                <div class="panel-heading">
                                    <h4 class="panel-title">
                                        <a data-toggle="collapse" data-parent="#accordion" href="#collapseFive" id="four">
                                            Configuration
                                        </a>
                                    </h4>
                                </div>
                                <div id="collapseFive" class="panel-collapse collapse">
                                    <div class="panel-body">
                                        <div class="row">
                                            <div class="form-group col-sm-6">        
                                                <div class="form-group col-sm-3">
                                                    <div class="checkbox" style="padding-left:0px !important">
                                                        <label class="col-sm-4 control-label" for="is_serializable" style="width: 100%;"> 
                                                            <input type="checkbox" id="is_serializable" name="product[is_serializable]" parsley-group="mygroup" parsley-trigger="change" parsley-required="true" parsley-mincheck="2" parsley-error-container="#myproperlabel " class="parsley-validated">  Is Serializable</label>
                                                    </div>
                                                    <div class="checkbox" style="padding-left:0px !important">
                                                        <label class="col-sm-4 control-label" for="is_batch_enabled" style="width: 100%;"> 
                                                            <input type="checkbox" id="is_batch_enabled" name="product[is_batch_enabled]" parsley-group="mygroup" parsley-trigger="change" parsley-required="true" parsley-mincheck="2" parsley-error-container="#myproperlabel " class="parsley-validated"> Is batch enabled</label>
                                                    </div>
                                                </div>
                                                <div class="form-group col-sm-3">
                                                    <div class="checkbox" style="padding-left:0px !important">
                                                        <label class="col-sm-4 control-label" for="inspection_enabled" style="width: 100%;"> 
                                                            <input type="checkbox" id="inspection_enabled" name="product[inspection_enabled]" parsley-group="mygroup" parsley-trigger="change" parsley-required="true" parsley-mincheck="2" parsley-error-container="#myproperlabel " class="parsley-validated">  Inspection enabled</label>
                                                    </div>
                                                    <div class="checkbox" style="padding-left:0px !important">                                                        
                                                        <label class="col-sm-4 control-label" for="is_backflush" style="width: 100%;"> 
                                                            <input type="checkbox" id="is_backflush" name="product[is_backflush]" parsley-group="mygroup" parsley-trigger="change" parsley-required="true" parsley-mincheck="2" parsley-error-container="#myproperlabel " class="parsley-validated"> Is backflush</label>
                                                    </div>
                                                </div>
                                            </div> 
                                            <!-- <div class="form-group col-sm-6">
                                                <label for="exampleInputEmail">Tax class</label>
                                                <div id="selectbox">
                                                    <select class="form-control" data-live-search="true" name="product[tax_class_id]" id="tax_class_id" parsley-trigger="change" parsley-required="true" parsley-error-container="#selectbox">
                                                    </select>
                                                </div>
                                            </div> -->
                                        </div>
                                        
                                        <div class="row">
                                            <div class="form-group col-sm-6">
                                                <button type="button" class="btn btn-primary " onClick="collapsDiv('four')" style="margin-top:22px">Back</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>
                </div>
                </div>
                </div>

              </div>
                <!-- /tile body -->
                <div class="navbar-fixed-bottom" role="navigation">
                    <div id="content product_page" class="col-md-12">
                        <button class="btn btn-primary" onclick="validateForm()"><i class="fa fa-hdd-o"></i> Save</button>
                        <button class="btn btn-primary" id="save_product" type="submit" style="display: none;"><i class="fa fa-hdd-o"></i> Submit</button>                        
                        <button class="btn btn-default" onclick="window.history.back();"><i class="fa fa-times-circle"></i> Cancel</button>
                    </div>           
                </div>
            </div>
        </div>   
    </div>
    
    {{ Form::close() }}
<div class="modal fade" id="add_business_unit" tabindex="-1" role="dialog" aria-labelledby="basicvalCode" aria-hidden="true">
    <div class="modal-dialog wide">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h4 class="modal-title" id="basicvalCode">Add Business Unit</h4>
            </div>
            <div class="modal-body">
                {{ Form::open(array('url' => '/customer/savebusinessunit', 'id' => 'business_unit_form')) }}
                {{ Form::hidden('_method','PUT') }}
                    <div>
                        <div class="alert alert-success" role="alert" style="display: none;" id="otp_message_sucess"></div>
                        <div class="alert alert-danger" role="alert" style="display: none;" id="otp_message_error"></div>
                    </div>
                    <div class="row">
                        <div class="form-group col-sm-6">
                            <label for="Business Unit Name">Business Unit Name</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
                                <input type="text" class="form-control" placeholder="Business Unit Name" id="business_unit1" name="business_unit[name]">
                                <input type="hidden" class="form-control" value="" name="business_unit[manufacturer_id]">
                            </div>                        
                        </div>
                        <div class="form-group col-sm-6">
                            <label for="Business Unit Description">Business Unit Description</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-addon addon-red"><i class="fa fa-newspaper-o"></i></span>
                                <textarea class="form-control" id="description" rows="3" name="business_unit[description]"></textarea>
                            </div>
                        </div>
                    </div>
                <!-- tile body -->
                <input class="btn btn-primary" id="business_unit_submit_button" type="submit" value="Add">
                {{Form::close()}}

            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<div class="modal fade" id="addAttributeSet" tabindex="-1" role="dialog" aria-labelledby="basicvalCode" aria-hidden="true">
    <div class="modal-dialog wide">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-times"></i></button>
                <h4 class="modal-title" id="basicvalCode">Add Attribute Set</h4>
            </div>
            <div class="modal-body">
                {{ Form::open(array('url' => '/products/saveattributeset', 'id' => 'save_attribute_set')) }}
                {{ Form::hidden('_method', 'POST') }}

                <div class="row">
                    <div class="form-group col-sm-6">
                        <label for="exampleInputEmail">Attribute Set Name *</label>
                        <div class="input-group ">
                            <span class="input-group-addon addon-red"><i class="fa fa-cubes"></i></span>
                            <input type="attribute_set_name"  id="name" name="attribute_set[attribute_set_name]" placeholder="name" class="form-control">
                        </div>
                    </div>
                    <div class="form-group col-sm-6">
                        <label for="exampleInputEmail">Category Name *</label>
                        <div class="input-group ">
                            <span class="input-group-addon addon-red"><i class="fa fa-bars"></i></span>
                            <select name="attribute_set[category_id]" id="attribute_set_category_id" class="form-control" >
                                    <option value="0">Please Select</option>                                
                                @foreach ($data['product_category']['options'] as $key => $value)
                                    <option value="{{ $key }}">{{ $value }}</option>
                                @endforeach
                            </select>
                        </div>  
                    </div>
                </div>
                <div class="row">
                    <div class="form-group col-sm-6">
                        <label for="exampleInputEmail">Manufacture Name *</label>
                        <div class="input-group ">
                            <span class="input-group-addon addon-red"><i class="fa fa-building-o"></i></span>
                            <input type="text" class="form-control" readonly id="manufacturer_name" name="attribute_set[manufacturer_name]" value="" />
                            <input type="hidden" class="form-control" readonly name="attribute_set[manufacturer_id]" id="manufacturer_id" value="" />
                        </div>
                    </div>
<!--                     <div class="form-group col-sm-6">
                        <label for="inherit_from"></label>
                        <div class="checkbox">
                            <input type="checkbox" id="inherit_from" name="attribute_set[inherit_from]" parsley-group="mygroup" parsley-trigger="change" parsley-required="true" parsley-mincheck="2" parsley-error-container="#myproperlabel .last" class="parsley-validated">
                            <label for="inherit_from">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Inherit all attributes from <b>Default</b> attribute set</label>
                        </div>
                    </div> -->
                    <input type="hidden" id="is_active" name="attribute_set[is_active]" id="is_active" value="1" />
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
                {{ Form::submit('Submit', array('class' => 'btn btn-primary', 'id' => 'save_attribute_set_button')) }}
                {{ Form::close() }}
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->



<div class="modal fade" id="add_attibute" tabindex="-1" role="dialog" aria-labelledby="basicvalCode" aria-hidden="true">
    <div class="modal-dialog wide">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h4 class="modal-title" id="basicvalCode">Add Attribute</h4>
            </div>
            <div class="modal-body">
                {{ Form::open(array('url' => '/products/saveattribute', 'id' => 'addAttribute')) }}
                {{ Form::hidden('_method', 'POST') }}

                <div class="row">
                    <div class="form-group col-sm-6">
                        <label for="exampleInputEmail">Attribute Set *</label>
                        <div class="input-group">
                            <span class="input-group-addon addon-red"><i class="fa fa-cubes"></i></span>
                            <input type="text" class="form-control"  readonly id="attibute_form_attribute_set_id" value="" />
                            <input type="hidden" class="form-control" readonly id="attribute_set_id" name="attribute_set_id" value="" />
                        </div> 
                    <input type="hidden" class="form-control"  readonly id="manufacturer_id" name="manufacturer_id" value="" />                     
                    </div>
                    
                    <div class="form-group col-sm-6">
                        <label for="exampleInputEmail">Attribute Group *</label>
                        <div class="input-group">
                            <span class="input-group-addon addon-red"><i class="ion-ios-color-filter-outline"></i></span>
                            <select name="attribute_group_id" id="attribute_group_id" class="form-control" >
                                
                            </select>
                        </div>                        
                    </div>                  
                </div>
                <div class="row">
                    <div class="form-group col-sm-6">
                        <label for="exampleInputEmail">Attribute Name *</label>
                        <div class="input-group ">
                            <span class="input-group-addon addon-red"><i class="fa fa-cube"></i></span>
                            <input type="text"  id="name" name="name" placeholder="name" class="form-control">
                        </div>
                    </div>
                    <div class="form-group col-sm-6">
                        <label for="exampleInputEmail">Attribute Code *</label>
                        <div class="input-group ">
                            <span class="input-group-addon addon-red"><i class="fa fa-keyboard-o"></i></span>
                            <input type="text"  id="attribute_code" name="attribute_code" placeholder="Attribute Code" class="form-control">
                        </div>
                    </div>                  
                </div>
                <div class="row">
                    <div class="form-group col-sm-6">
                        <label for="exampleInputEmail">Attribute Text</label>
                        <div class="input-group ">
                            <span class="input-group-addon addon-red"><i class="fa fa-keyboard-o"></i></span>
                            <input type="text"  id="text" name="text" placeholder="text" class="form-control">
                        </div>
                    </div>
                    <div class="form-group col-sm-6">
                        <label for="exampleInputEmail">Input Type *</label>
                        <div class="input-group ">
                            <span class="input-group-addon addon-red"><i class="fa fa-shield"></i></span>
                            <select name="input_type" id="input_type" class="form-control">
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
                </div>
                <div class="row">
                    <div class="form-group col-sm-6">
                        <label for="exampleInputEmail">Default Value</label>
                        <div class="input-group ">
                            <span class="input-group-addon addon-red"><i class="fa fa-credit-card"></i></span>
                            <input type="text" id="default_value" name="default_value" placeholder="default_value" class="form-control">
                        </div>
                    </div>
                    <div class="form-group col-sm-6">
                        <label for="exampleInputEmail">Is_Required</label>
                        <div class="input-group">
                            <span class="input-group-addon addon-red"><i class="ion-android-done"></i></span>
                            <select name="is_required" id="is_required" class="form-control">
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
                            <input type="text" id="validation" name="validation" placeholder="validation" class="form-control">
                        </div>
                    </div>
                    <div class="form-group col-sm-6">
                        <label for="exampleInputEmail">Regexp</label>
                        <div class="input-group">
                            <span class="input-group-addon addon-red"><i class="fa fa-newspaper-o"></i></span>
                            <input type="text" id="regexp" name="regexp" placeholder="regexp" class="form-control">
                        </div>                        
                    </div>
                </div>


                <div class="row">
                    <div class="form-group col-sm-6">
                        <label for="exampleInputEmail">Lookup ID</label>
                        <div class="input-group ">
                            <span class="input-group-addon addon-red"><i class="fa fa-ticket"></i></span>
                            <input type="text" id="lookup_id" name="lookup_id" placeholder="lookup_id" class="form-control">
                        </div>
                    </div>
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
                </div>

                {{ Form::submit('Submit', array('class' => 'btn btn-primary', 'id' => 'attribute_submit_button')) }}
                {{ Form::close() }}


            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->
</div><!-- /.modal -->


<div style="display: none">
    <div class="row" id="inputTemplate">
        <div class="form-group col-sm-6">
            <label for="Pallet Width">Pallet Width</label>
            <div class="input-group input-group-sm">
                <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
                <input type="text" class="form-control" name="">
            </div>                        
        </div>  
    </div>
    <div class="row" id="checkboxTemplate">
        <div class="form-group col-sm-6">
            <div class="checkbox">
                <input type="checkbox" value="1" id="opt01" name="" parsley-group="mygroup" parsley-trigger="change" parsley-required="true" parsley-mincheck="2" parsley-error-container="#myproperlabel .last" class="parsley-validated">
                <label for="opt01">Option 1</label>
            </div>                       
        </div>  
    </div>
    <div class="row" id="radioTemplate">
        <div class="form-group col-sm-6">            
            <div class="radio">
                <input type="radio" name="" id="optionsRadios1" value="option1">
                <label for="optionsRadios1">Option 1</label>
            </div>                       
        </div>  
    </div>
    <div class="row" id="selectTemplate">
        <div class="form-group col-sm-6">
            <label for="Pallet Width">Pallet Width</label>
            <div class="input-group input-group-sm">
                <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
                <div id="selectbox">
                    <select class="chosen-select form-control parsley-validated" name="" id="" parsley-trigger="change" parsley-required="true" parsley-error-container="#selectbox">
                        <option>Please select</option>
                    </select>
                </div>
            </div>                        
        </div>  
    </div>
    <div class="row" id="textareaTemplate">
        <div class="form-group col-sm-6">
            <label for="Pallet Width">Pallet Width</label>
            <div class="input-group input-group-sm">
                <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
                <textarea class="form-control" id="" rows="3" name=""></textarea>
            </div>                        
        </div>  
    </div>
    <div class="row" id="fileTemplate">
        <div class="form-group col-sm-6">
            <label>Package Instructions</label>
            <div class="input-group">
                <span class="input-group-btn">
                    <span class="btn btn-primary btn-file">Browse…<input type="file" name="" multiple></span>
                </span>
                <input type="text" class="form-control" readonly>
            </div>
        </div>
    </div>
    <div class="row" id="multiselectTemplate">
        <div class="form-group col-sm-6">
            <label for="ProductCategory">Product Category *</label>
            <div class="input-group input-group-sm ">
                <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
                <div id="selectbox">
                    <select class="chosen-select form-control parsley-validated" multiple name="" id="" parsley-trigger="change" parsley-error-container="#selectbox">
                        <option>Please select</option>
                    </select>                                    
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="add_location" tabindex="-1" role="dialog" aria-labelledby="basicvalCode" aria-hidden="true">
    <div class="modal-dialog wide">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h4 class="modal-title" id="basicvalCode">Add Location</h4>
            </div>
            <div class="modal-body">
                {{ Form::open(array('url' => '/customer/savelocation', 'data-url' => '/customer/savelocation/', 'id' => 'add_location_form' )) }}
                {{ Form::hidden('_method','POST') }}
                <input type="hidden" name="manufacturer_id" id="manufacturer_id" value="" />
                <div class="row">
                    <div class="form-group col-sm-6">
                        <label for="exampleInputEmail">Location Name *</label>
                        <div class="input-group ">
                            <span class="input-group-addon addon-red"><i class="ion-ios-location"></i></span>
                            <input type="text" id="location_name" name="location_name" placeholder="location_name" class="form-control">
                            <input type="hidden" name="source_page" value="product" />
                        </div>
                    </div>

                    <div class="form-group col-sm-6">
                        <label for="exampleInputEmail">Parent Location Name</label>
                        <div class="input-group ">
                            <span class="input-group-addon addon-red"><i class="ion-ios-location"></i></span>
                            <select name="parent_location_id" id="parent_location_id" class="form-control selectpicker" data-live-search="true">
                                <option value="0">None</option>                                
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="form-group col-sm-6">
                        <label for="exampleInputEmail">Location Type Name</label>
                        <div class="input-group">
                            <span class="input-group-addon addon-red"><i class="ion-ios-location"></i></span>
                            <select name="location_type_id" id="add_location_type_id" data-live-search="true" class="form-control selectpicker">
                            </select>
                        </div>
                    </div>
                    <div class="form-group col-sm-6">
                        <label for="exampleInputEmail">Location Email *</label>
                        <div class="input-group">
                            <span class="input-group-addon addon-red"><i class="ion-ios-email"></i></span>
                            <input type="text" id="location_email" name="location_email" placeholder="location_email" class="form-control">
                        </div>                        
                    </div>
                </div>
                <div class="row">
                    <div class="form-group col-sm-6">
                        <label for="exampleInputEmail">Location Address</label>
                        <div class="input-group ">
                            <span class="input-group-addon addon-red"><i class="ion-android-locate"></i></span>
                            <input type="text" id="location_address" name="location_address" placeholder="location_address" class="form-control">
                        </div>
                    </div>
                    <div class="form-group col-sm-6">
                        <label for="exampleInputEmail">Location Details</label>
                        <div class="input-group">
                            <span class="input-group-addon addon-red"><i class="ion-android-locate"></i></span>
                            <input type="text" id="location_details" name="location_details" placeholder="location_details" class="form-control">
                        </div>                        
                    </div>
                </div>
                <div class="row">
                    <div class="form-group col-sm-6">
                        <label for="BusinessType">Country*</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-addon addon-red"><i class="ion-earth"></i></span>
                            <div id="selectbox">
                                <select class="chosen-select form-control parsley-validated" disabled="true" id="location_country_id" parsley-trigger="change" parsley-required="true" parsley-error-container="#selectbox">
                                </select> 
                                <input type="hidden" id="country_input_id" name="country" value="">                                   
                            </div>
                        </div>
                    </div>
                    <div class="form-group col-sm-6">
                        <label for="exampleInputEmail">State</label>
                        <div class="input-group ">
                            <span class="input-group-addon addon-red"><i class="ion-flag"></i></span>
                            <select name="state" id="location_state_options" class="form-control selectpicker" data-live-search="true" parsley-trigger="change" parsley-required="true" parsley-error-container="#selectbox" style="display: none;">
                                
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row">                        
                    <div class="form-group col-sm-6">
                        <label for="exampleInputEmail">Region</label>
                        <div class="input-group">
                            <span class="input-group-addon addon-red"><i class="ion-location"></i></span>
                            <input type="text" id="region" name="region" placeholder="region" class="form-control">
                        </div>                        
                    </div>
                    <div class="form-group col-sm-6">
                        <label for="exampleInputEmail">Longitude</label>
                        <div class="input-group ">
                            <span class="input-group-addon addon-red"><i class="ion-ios-world"></i></span>
                            <input type="text" id="longitude" name="longitude" placeholder="longitude" class="form-control">
                        </div>
                    </div> 
                </div>
                <div class="row">                       
                    <div class="form-group col-sm-6" >
                        <label for="exampleInputEmail">Latitude</label>
                        <div class="input-group" >
                            <span class="input-group-addon addon-red"><i class="ion-ios-world"></i></span>
                            <input type="text" id="latitude" name="latitude" placeholder="latitude" class="form-control">
                        </div>                        
                    </div>
                    
                    <div class="form-group col-sm-6 ">
                        <label for="exampleInputEmail">ERP Code*</label>
                        <div class="input-group ">
                            <span class="input-group-addon addon-red"><i class="ion-ios-barcode"></i></span>
                            <input type="text" id="erp_code" name="erp_code" placeholder="erp_code" class="form-control">
                        </div>
                    </div> 
                
                </div>
                <div class="row">                       
                    <div class="form-group col-sm-6">
                        <label for="exampleInputEmail">Business Unit</label>
                        <div class="input-group ">
                            <span class="input-group-addon addon-red"><i class="ion-home"></i></span>
                            <select name="business_unit_id" id="locations_business_unit_id" class="form-control selectpicker" data-live-search="true">                                
                            </select>
                        </div>
                    </div>
                    <div class="form-group col-sm-6">
                        <label for="exampleInputEmail">Storage Location Type</label>
                        <div class="input-group ">
                            <span class="input-group-addon addon-red"><i class="ion-android-locate"></i></span>
                            <select name="storage_location_type_code" id="storage_location_type_code" class="form-control selectpicker" data-live-search="true">                                                              
                            </select>
                        </div>
                    </div>
                </div>
                {{ Form::submit('Add', array('class' => 'btn btn-primary')) }}
                {{Form::close()}}
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- Modal -->

@section('style')
{{HTML::style('css/bootstrap-select.css')}}
{{HTML::style('css/jquery.fileupload.css')}}
{{HTML::style('css/dragdrop/jquery-ui.css')}}
{{HTML::style('css/dragdrop/style.css')}}
@stop

@section('script')
{{HTML::script('js/plugins/bootstrap-select/bootstrap-select.js')}}
{{HTML::script('js/plugins/bootstrap-select/bootstrap-datepicker.min.js')}}
{{HTML::script('js/plugins/jquery-file-upload/vendor/jquery.ui.widget.js')}}
{{HTML::script('js/plugins/jquery-file-upload/load-image.all.min.js')}}
{{HTML::script('js/plugins/jquery-file-upload/jquery.iframe-transport.js')}}
{{HTML::script('js/plugins/jquery-file-upload/jquery.fileupload.js')}}
{{HTML::script('js/plugins/jquery-file-upload/jquery.fileupload-process.js')}}
{{HTML::script('js/plugins/jquery-file-upload/jquery.fileupload-image.js')}}
{{HTML::script('js/plugins/jquery-file-upload/jquery.fileupload-audio.js')}}
{{HTML::script('js/plugins/jquery-file-upload/jquery.fileupload-video.js')}}
{{HTML::script('js/plugins/jquery-file-upload/jquery.fileupload-validate.js')}}
{{HTML::script('js/plugins/jquery-file-upload/upload-script.js')}}
{{HTML::script('js/plugins/dragdrop/jquery-ui.js')}}
{{HTML::script('js/plugins/dragdrop/fieldChooser.js')}}

{{HTML::script('js/plugins/validator/formValidation.min.js')}}
{{HTML::script('js/plugins/validator/validator.bootstrap.min.js')}}
{{HTML::script('js/plugins/validator/jquery.bootstrap.wizard.min.js')}}
<script type="text/javascript">
$(document).ready(function () {
    var $sourceFields = $("#Selectattribute");
    var $destinationFields = $("#attribute_id");
    var $chooser = $("#fieldChooser").fieldChooser(Selectattribute, attribute_id);
});
$('#addAttribute [name="name"]').keyup(function(){
    //console.log('Hi');
    $('#addAttribute [name="attribute_code"]').val($('#addAttribute [name="name"]').val().replace(/\s+/g, '_').toLowerCase());
    $('[name="attribute_code"]').change();
});
    $(document).ready(function(){
        $('#main_manufacturer_id').trigger('change'); 
        $('#main_attribute_set_id').trigger('change');
        $('#product_creation').formValidation({
    //        live: 'disabled',
            message: 'This value is not valid',
            feedbackIcons: {
                valid: 'glyphicon glyphicon-ok',
                invalid: 'glyphicon glyphicon-remove',
                validating: 'glyphicon glyphicon-refresh'
            },
            fields: {
                'product[manufacturer_id]': {
                    validators: {
                        callback: {
                            message: 'Please choose manufacturer',
                            callback: function(value, validator, $field) {
                                // Get the selected options
                                var options = $('#main_manufacturer_id').val();
                                return (options != 0);
                            }
                        },
                        notEmpty: {
                            message: 'Please select manufacturer.'
                        }
                    }
                },
                'product[name]': {
                    validators: {
                        notEmpty: {
                            message: 'Please enter name.'
                        }
                    }
                },
                'product[product_type_id]': {
                    validators: {
                        callback: {
                            message: 'Please choose product type',
                            callback: function(value, validator, $field) {
                                return (value != 0);
                            }
                        },
                        notEmpty: {
                            message: 'Please select product type.'
                        }
                    }
                },
                'product[business_unit_id]': {
                    validators: {
                        callback: {
                            message: 'Please choose business unit',
                            callback: function(value, validator, $field) {
                                console.log('product[business_unit_id] => '+ value);
                                return (value != 0);
                            }
                        },
                        notEmpty: {
                            message: 'Please select business type.'
                        }
                    }
                },
                'product[category_id][]': {
                    validators: {
                        callback: {
                            message: 'Please choose product category',
                            callback: function(value, validator, $field) {
                                // Get the selected options
                                var options = $('[name="product[category_id][]"]').val();
                                console.log(options);
                                return (options != 0);
                            }
                        },
                        notEmpty: {
                            message: 'Please choose product category'
                        }
                    }
                },
                'product[uom_class_id]': {
                    validators: {
                        callback: {
                            message: 'Please choose product UOM',
                            callback: function(value, validator, $field) {
                                // Get the selected options
                                var options = $('[name="product[uom_class_id]"]').val();
                                console.log(options);
                                return (options != 0);
                            }
                        },
                        notEmpty: {
                            message: 'Please choose product UOM'
                        }
                    }
                },
                'product[material_code]': {
                    validators: {
                        callback: {
                            message: 'Please enter product erp code',
                            callback: function(value, validator, $field) {
                                // Get the selected options
                                var options = $('[name="product[material_code]"]').val();
                                console.log(options);
                                return (options != 0);
                            }
                        },
                        notEmpty: {
                            // message: 'Please enter product erp code'
                        },
                        remote: {
                            url: '/products/erp_code_uniquevalidation',
                            type: 'POST',
                            data: function(validator, $field, value) {
                                return {
                                    table_name: 'products', 
                                    field_name: 'material_code', 
                                    code: value, 
                                    manufacturer_id: $('#main_manufacturer_id').val(),
                                    pluck_id: 'material_code',
                                    request_type: 'create'
                                };
                            },
                            delay: 30,     // Send Ajax request every 2 seconds
                            message: 'ERP Code  already exists.'
                        }
                    }
                }

            }
        });
        $('#business_unit_form').formValidation({
    //        live: 'disabled',
            message: 'This value is not valid',
            feedbackIcons: {
                valid: 'glyphicon glyphicon-ok',
                invalid: 'glyphicon glyphicon-remove',
                validating: 'glyphicon glyphicon-refresh'
            },
            fields: {
                'business_unit[name]': {
                    validators: {
                        notEmpty: {
                            message: 'Name cannot be empty.'
                        },
                        remote: {
                            url: '/customer/uniquevalidation',
                            type: 'POST',
                            data: function(validator, $field, value) {
                                return {
                                    table_name: 'business_units', 
                                    field_name: 'name', 
                                    name: value, 
                                    manufacturer_id: $('#main_manufacturer_id').val(),
                                    pluck_id: 'business_unit_id',
                                    skip_decode: 1
                                };
                            },
                            delay: 30,     // Send Ajax request every 2 seconds
                            message: 'Business unit already exists.'
                        }  
                    }
                }
            }
        }).on('success.form.bv', function(event) {
            console.log('we are in sucess');
        }).validate({            
            submitHandler: function (form) {
                console.log('we are in validate');
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
                          message : 'Name already exists.Please enter a new name',
                          url: '/product/checkSetAvailability',
                          type: 'GET',
                          data: function(validator, $field, value) {
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
            'attribute_set[manufacturer_name]': {
                validators: {
                        callback: {
                            message: 'Please choose Manufacturer Name',
                            callback: function(value, validator, $field) {
                                var options = $('[name="attribute_set[manufacturer_name]"]').val();
                                return (options != 'Please select ..');
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
                            callback: function(value, validator, $field) {
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
    }).on('success.form.bv', function(event) {
         event.preventDefault();
        $('#save_attribute_set_button').prop('disabled', true);    
        var url = '/product/saveattributeset';
        var inherit = $('[name="attribute_set[inherit_from]"]').prop('checked');
        if(inherit)
        {
            inherit = 1;
        }else{
            inherit = 0;
        }
        var selectedAttr = new Array();
        var selectedAttrArray = new Array();
        $('#attribute_id div').each(function(i,v){
            selectedAttr.push($(v).attr('value'));
            selectedAttrArray.push($(v).attr('key'));
        });
        // selectedAttr = selectedAttr.substr(1,selectedAttr.length);
        var temp = {
            attribute_set_name : $('[name="attribute_set[attribute_set_name]"]').val(),
            category_id : $('[name="attribute_set[category_id]"]').val(), 
            manufacturer_id: $('#manufacturer_id').val(), 
            is_active: $('[name="attribute_set[is_active]"]').val(),
            //inherit_from: $('[name="attribute_set[inherit_from]"]').val(),
            attribute_id: selectedAttr, 
            //sort_order: selectedAttrArray,
            inherit_from: inherit 
        };
        var posting = $.post( url, { attribute_set: temp } );
        // Put the results in a div
        posting.done(function( data ) {
            if(data['status'] == true)
            {
                $("#main_attribute_set_id").append('<option value="'+data['set_id']+'">'+data['set_name']+'</option>').val(data['set_id']);
                $("#attribute_sets_id").append('<option value="'+data['set_id']+'">'+data['set_name']+'</option>');
                $('.selectpicker#main_attribute_set_id').selectpicker('refresh');
                $('#main_attribute_set_id').trigger('change');
                $('.selectpicker#attribute_sets_id').selectpicker('refresh');
                $('.close').trigger('click');
                alert(data['message']);            
            }else{
                alert(data['message']);
            }
        });
        $('#save_attribute_set_button').prop('disabled', false);
        return false;      
    }).validate({
        submitHandler: function (form) {
            return false;
        }
    });
    $('#addAttributeSet').on('hide.bs.modal',function(){
    console.log('resetForm');
    $('#save_attribute_set').data('bootstrapValidator').resetForm();
    $('#save_attribute_set')[0].reset();
    $('#attribute_id').empty();
    $('#Selectattribute').empty();
    $('[name="attribute_set[manufacturer_name]"]').val($('#main_manufacturer_id option:selected').text());
    $('[name="attribute_set[manufacturer_id]"]').val($('#main_manufacturer_id option:selected').val());    
});
    $('#addAttributeSet').on('show.bs.modal',function(){
    $('[name="attribute_set[manufacturer_name]"]').val($('#main_manufacturer_id option:selected').text());
    $('[name="attribute_set[manufacturer_id]"]').val($('#main_manufacturer_id option:selected').val());    
});
$('#addAttribute').bootstrapValidator({
//        live: 'disabled',
        message: 'This value is not valid',
        feedbackIcons: {
            valid: 'glyphicon glyphicon-ok',
            invalid: 'glyphicon glyphicon-remove',
            validating: 'glyphicon glyphicon-refresh'
        },
        fields: {
          
            attribute_set_id: {
                    validators: {
                        notEmpty: {
                            message: 'Please select Attribute Set.'
                        }
                    }
                },
                 attribute_group_id: {
                    validators: {
                        callback: {
                            message: 'Please choose Attribute Group',
                            callback: function(value, validator, $field) {
                                var options = $('[name="attribute_group_id"]').val();
                                return (options != 0);
                            }
                        },                         
                        notEmpty: {
                            message: 'Please select Attribute Group.'
                        }
                    }
                },
                 attribute_code: {
                    trigger: 'change keyup',
                    validators: {
                        notEmpty: {
                            message: 'Attribute code is Required.'
                        }, 
                         regexp: {
                        regexp: '^[a-zA-Z0-9_]+$',
                            message : 'Please enter only alpha-numeric and underscore'
                        },                                               
                      remote: {
                          message : 'Attribute Exists with this code.Please enter a new code',
                          url: '/product/checkAttrAvailability',
                          type: 'GET',
                          data: function(validator, $field, value) {
                                return {
                                'attribute_code': validator.getFieldElements('attribute_code').val(),
                            };
                            },
                          delay: 2000     // Send Ajax request every 2 seconds
                      }                      

                    }
                },                  
                 name: {
                    validators: {
                        notEmpty: {
                            message: 'Attribute Name is Required'
                        },                        
                      remote: {
                          message : 'Name already exists.Please enter a new name',
                          url: '/product/checkAttributeAvailability',
                          type: 'GET',
                          data: function(validator, $field, value) {
                                return {
                                'manufacturer_id': validator.getFieldElements('manufacturer_id').val(),
                                };
                            },
                          delay: 2000     // Send Ajax request every 2 seconds
                      },                            
                 }/*,onSuccess: function(e, data) {
                         $('#addAttribute').data('bootstrapValidator').validateField('attribute_code');
                    }, */                        
                },
            input_type: {
                validators: {
                    callback: {
                        message: 'Please choose Input Type',
                        callback: function(value, validator, $field) {
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
                        callback: function(value, validator, $field) {
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
    }).on('success.form.bv', function(event) {
        ajaxCallPopup($('#addAttribute'));
        return false;
    }).validate({
        submitHandler: function (form) {
            return false;
        }
    });
    $('#add_attibute').on('hide.bs.modal',function(){
        console.log('resetForm');
        $('#addAttribute').data('bootstrapValidator').resetForm();
        $('#addAttribute')[0].reset();
        $('#attribute_manufacturer_name').val($("#main_manufacturer_id option:selected").text());
        $('[name="manufacturer_id"]').val($("#main_manufacturer_id").val());
        $('#attibute_form_attribute_set_id').val($("#main_attribute_set_id option:selected").text());
        $('#attribute_set_id').val($("#main_attribute_set_id option:selected").val());        
    });           
    });
    $('#add_attibute').on('show.bs.modal',function(){
        $('#attribute_manufacturer_name').val($("#main_manufacturer_id option:selected").text());
        $('[name="manufacturer_id"]').val($("#main_manufacturer_id").val());
        $('#attibute_form_attribute_set_id').val($("#main_attribute_set_id option:selected").text());
        $('#attribute_set_id').val($("#main_attribute_set_id option:selected").val()); 
    });
    $('#main_manufacturer_id').change(function(){
        if($(this).val() != 0)
        {
            $('[name="business_unit[manufacturer_id]"]').val($(this).val());
            $('[name="attribute_set[manufacturer_name]"]').val($("#main_manufacturer_id option:selected").text());
            $('[name="attribute_set[manufacturer_id]"]').val($("#main_manufacturer_id").val());
            $('#attribute_manufacturer_name').val($("#main_manufacturer_id option:selected").text());
            $('[name="attribute[manufacturer_id]"]').val($("#main_manufacturer_id").val());
            $('[name="manufacturer_id"]').val($("#main_manufacturer_id").val());
            businessUnits();
            categories();
            attributeSets();        
            locations();
            loadComponentProducts();
            loadLocationsType();
            loadGroups();
            UOM();
        }
    });
    
    function validateForm()
    {
        var fv = $('#product_creation').data('formValidation');
        fv.validate();
       // var fields = ['product[manufacturer_id]', 'product[name]', 'product[business_unit_id]', 'product[product_type_id]', ];
       var fields = ['product[manufacturer_id]','product[name]','product[material_code]']
        var temp = 1;        
        $.each(fields, function(key, field){
            if(field == 'files[]')
            {
                temp = validateImage();
            }else{
                $('#product_creation').data('formValidation').revalidateField(field);
                if($('[name="'+field+'"]').val() == 0 || $('[name="'+field+'"]').val() == '')
                {
                    temp = 0;
                }
            }
        });             
        if(!temp)
        {
//            collapsDiv('two');
            fv.validate();
        }
        console.log('temp '+temp);        
        $('input,textarea,select').filter('[required]').each(function(){
            var el = $(this);
            if(el.is('input') && el.attr('type') == 'text' && $(this).val() == '')
            {
                temp = 0;
//                collapsDiv('four');
                fv.validate();
            }else if(el.is('input') && el.attr('type') == 'checkbox' && $(this).prop('checked') == false){
                temp = 0;
//                collapsDiv('four');
                fv.validate();
            }else if(el.is('input') && el.attr('type') == 'radio' && $(this).prop('checked') == false){
                temp = 0;
//                collapsDiv('four');
                fv.validate();
            }else if(el.is('select') && $(this).val == ''){
                temp = 0;
//                collapsDiv('four');
                fv.validate();
            }else if(el.is('textarea') && $(this).val == ''){
                temp = 0;
//                collapsDiv('four');
                fv.validate();
            }
            var value = $(this).attr('name');
            var isValidStep = fv.validateField(value).isValid();
            var isValidField = fv.revalidateField(value).isValid();
            if (isValidField === false || isValidField === null) {                
                // Do not jump to the target tab
                temp = 0;
            }else{
                if(temp)
                {
                    temp = 1;
                }                
            }
        });
        console.log('temp '+temp);
        if(temp)
        {
            console.log('can Submit');
            $('#save_product').prop('disabled', false).trigger('click');
            $('#product_creation').submit();
        }else{
            //$('#product_creation').data('formValidation').validate();
        }
    }

    function getLocation()
    {
        url = "/product/getLocation";
        var posting = $.post(url ,{data_value : keyword});
        posting.done(function (data){
            alert(data);
        });
    }
    
    function locations()
    {
        var manufacturer_id = $('#main_manufacturer_id').val();
        $('[name="location[location_id][]"]').find('option').remove();
        if(manufacturer_id != 0)
        {
            url = '/product/getelementdata';
            // Send the data using post
            var posting = $.post(url, { data_type: 'locations', data_value: manufacturer_id });
            // Put the results in a div
            posting.done(function( data ) {
                $('#locations').empty();
                $('#parent_location_id').empty();
                responseData = JSON.parse(data);
                 $('#locations').append('<option value="0" selected="true">Please select... </option>');
                $.each(responseData, function(key, value) {
                    $('[name="location[location_id][]"]').append('<option value="'+value['location_id']+'">'+value['location_name']+'</option>');
                    $('#locations').append('<option value="'+value['location_id']+'">'+value['location_name']+'</option>');
                    $('#parent_location_id').append('<option value="'+value['location_id']+'">'+value['location_name']+'</option>');
                });
                $('[name="location[location_id][]"]').selectpicker('refresh');
                $('#locations').selectpicker('refresh');
            });        
        }
    }

    function UOM()
    {

        $('[name="uom_class_id[ml_value][]"]').find('option').remove();
        
            url = '/product/getelementdata';
            var manufac_id = $('#main_manufacturer_id').val();
            // Send the data using post
            var posting = $.post(url, { data_type: 'UOM', manufacturer_id:manufac_id });
            // Put the results in a div
            posting.done(function( data ) {
                $('#uom_class_id').empty();
                
                responseData = JSON.parse(data);
                 $('#uom_class_id').append('<option value="0" selected="true">Please select... </option>');
                $.each(responseData, function(key, value) {
                    
                    $('#uom_class_id').append('<option value="'+value['ml_value']+'">'+value['name']+'</option>');
                    
                });
               
                $('#ml_value').selectpicker('refresh');
            });        
        
    }
    
    function loadLocationsType()
    {
        var manufacturer_id = $('#main_manufacturer_id').val();
        $('[name="location[location_id][]"]').find('option').remove();
        if(manufacturer_id != 0)
        {
            url = '/product/getelementdata';
            // Send the data using post
            var posting = $.post(url, { data_type: 'location_types', data_value: manufacturer_id });
            // Put the results in a div
            posting.done(function( data ) {
                $('#add_location_type_id').empty();
                responseData = JSON.parse(data);
                 //$('#add_location_type_id').append('<option value="0" selected="true">Please select... </option>');
                $.each(responseData, function(key, value) {
                    $('#add_location_type_id').append('<option value="'+value['location_type_id']+'">'+value['location_type_name']+'</option>');
                });
                $('#add_location_type_id').selectpicker('refresh');
            });        
        }
    }
    
    function loadGroups()
    {
        var manufacturer_id = $('#main_manufacturer_id').val();
        $('#group_id').empty();
        if(manufacturer_id != 0)
        {
            url = '/product/getelementdata';
            // Send the data using post
            var posting = $.post(url, { data_type: 'groups', data_value: manufacturer_id });
            // Put the results in a div
            posting.done(function( data ) {                
                responseData = JSON.parse(data);
                 //$('#add_location_type_id').append('<option value="0" selected="true">Please select... </option>');
                $.each(responseData, function(key, value) {
                    $('#group_id').append('<option value="'+value['group_id']+'">'+value['name']+'</option>');
                });
                $('#group_id').selectpicker('refresh');
            });        
        }
    }
    
    $('#main_attribute_set_id').change(function(event){        
        attributeGroups();
        appendData();
    });
    
    function categories()
    {
        var manufacturer_id = $('#main_manufacturer_id').val();
        $('#product_category').find('option').remove();
        $('#attribute_set_category_id').find('option').remove();
        if(manufacturer_id)
        {
            url = '/product/getelementdata';
            // Send the data using post
            var posting = $.post(url, { data_type: 'categories', data_value: manufacturer_id });
            // Put the results in a div
            posting.done(function( data ) {
                responseData = JSON.parse(data);
                $('[name="attribute_set[category_id]"]').append('<option value="0" selected="true">Please select... </option>');
                $.each(responseData, function(key, value) {
                    $('#product_category').append('<option value="'+value['category_id']+'">'+value['name']+'</option>');
                    $('[name="attribute_set[category_id]"]').append('<option value="'+value['category_id']+'">'+value['name']+'</option>');
                });
                $('.selectpicker#product_category').selectpicker('refresh'); 
                $('.selectpicker#product_location').selectpicker('refresh'); 
                $('.selectpicker#attribute_set_category_id').selectpicker('refresh'); 
            });        
        }
    }
    
    function attributeGroups()
    {
        $('#attibute_form_attribute_set_id').val($("#main_attribute_set_id option:selected").text());
        $('#attribute_set_id').val($("#main_attribute_set_id option:selected").val());
        $('[name="attribute[attribute_set_id]"]').val($("#main_attribute_set_id option:selected").val());
        var manufacturerId = $('#main_manufacturer_id').val();  
        $('#attribute_group_id').find('option').remove();     
        if(manufacturerId != 0)
        {
            url = '/product/getelementdata';
            // Send the data using post
            var posting = $.post(url, { data_type: 'attributeGroups', data_value: manufacturerId });
            // Put the results in a div
            posting.done(function( data ) {
                responseData = JSON.parse(data);
                $("#attribute_group_id").append('<option value="0" selected="true">Please select... </option>');
                $.each(responseData, function(key, value) {
                    $("#attribute_group_id").append('<option value="'+value['attribute_group_id']+'">'+value['name']+'</option>');
                });
                $('.selectpicker#attribute_group_id').selectpicker('refresh'); 
            });        
        }
    }
    
    $('#product_submit_button').click(function(event){
        $('#product_submit_button').valid();
        $('#product_submit_button').attr("disabled", true);
        event.preventDefault();
        var imgField = $('[name="media[image][]"]');
        if(imgField)
        {
            var imgLength = $('[name="media[image][]"]').length;
            if(imgLength)
            {
                /*submitHandler: function (form) {
                    form.submit();
                }*/
                form.submit();
            }
        }
    });
    
    function businessUnits()
    {
        var manufacturer_id = $('#main_manufacturer_id').val();
        $('#business_unit_id').find('option').remove();        
        if(manufacturer_id)
        {
            url = '/product/getelementdata';
            // Send the data using post
            var posting = $.post(url, { data_type: 'businessUnits', data_value: manufacturer_id });
            // Put the results in a div
            posting.done(function( data ) {
                responseData = JSON.parse(data);
                $("#locations_business_unit_id").empty();
                $("#business_unit_id").append('<option value="0" selected="true">Please select... </option>');
                $.each(responseData, function(key, value) {
                    $("#business_unit_id").append('<option value="'+value['business_unit_id']+'">'+value['name']+'</option>');
                    $("#locations_business_unit_id").append('<option value="'+value['business_unit_id']+'">'+value['name']+'</option>');
                });
                $('.selectpicker').selectpicker('refresh'); 
            });        
        }
    }
    
    function attributeSets()
    {
        var manufacturerId = $('#main_manufacturer_id').val();
        $('#main_attribute_set_id').find('option').remove();
        $('#attribute_sets_id').find('option').remove();
        if(manufacturerId)
        {
            url = '/product/getelementdata';
            // Send the data using post
            var posting = $.post(url, { data_type: 'attributeSets', data_value: manufacturerId });
            // Put the results in a div
            posting.done(function( data ) {
                responseData = JSON.parse(data);
                $("#main_attribute_set_id").append('<option value="0" selected="true">Please select... </option>');
                $("#attribute_sets_id").append('<option value="0" selected="true">Please select... </option>');
                $.each(responseData, function(key, value) {
                    $("#main_attribute_set_id").append('<option value="'+value['attribute_set_id']+'">'+value['attribute_set_name']+'</option>');
                    $("#attribute_sets_id").append('<option value="'+value['attribute_set_id']+'">'+value['attribute_set_name']+'</option>');
                });
                $('.selectpicker#main_attribute_set_id').selectpicker('refresh');
                $('.selectpicker#attribute_sets_id').selectpicker('refresh');
                $("#main_attribute_set_id").trigger('change');
            });
        }
    }
    
    $("#add_packages").click(function () {
        var package_level = $('#package_level option:selected').text();
        var package_levels = $('#package_level').val();
            
        if(package_levels ==0){
            alert("please choose levels");
        }else{
            var packageElements = new Array();
            $('[id="package_name"]').each(function(){
                packageElements.push($(this).text());
            });
        }
        if(packageElements.length > 0 && $.inArray(package_level, packageElements) >= 0)
        {
            alert('This package already added.');
        }else{
            var capacity = $('#package_capacity').val();
            var weight_class_id = $('#weight_class_id option:selected').text();
            var product_weight = $('#product_weight').val();
            var length = $('#package_length').val();
            var breadth = $('#package_breadth').val();
            var height = $('#package_height').val();
            var package_weight_class_id = $('#package_weight_class_id').val();
            var pallet_stack_height = $('#pallet_stack_height').val();
            var is_shipper_pack = $('#is_shipper_pack').is(':checked');
            ;
            var is_pallet = $('#is_pallet').is(':checked');

            var jsonArg1 = new Object();
            jsonArg1.level = $('#package_level').val();
            jsonArg1.quantity = capacity;
            jsonArg1.length = length;
            jsonArg1.width = breadth;
            jsonArg1.height = height;
            jsonArg1.weight_class_id = package_weight_class_id;
            jsonArg1.stack_height = pallet_stack_height;
            jsonArg1.is_shipper_pack = is_shipper_pack ? 1 : 0;
            jsonArg1.is_pallet = is_pallet ? 1 : 0;
            var hiddenJsonData = new Array();
            hiddenJsonData.push(jsonArg1);

            $("#package_data").append('<tr><td scope="row" id="package_name">' + package_level + '</td><td>' + capacity
                    + '</td><td>' + length + '</td><td>' + breadth + '</td><td>' + height + '</td><td>' + is_shipper_pack + '</td><td>' + is_pallet + '</td><td><a href="javascript:void(0);" class="check-toggler" id="remCF" onclick="alertDelete()"><span class="badge bg-red"><i class="fa fa-trash-o"></i></span></a><input type="hidden" name="package[package_details][]" value=' + "'" + JSON.stringify(jsonArg1) + "'" + ' /></td></tr>');
        }        
    });
    $("#package_data").on('click', '#remCF', function () {
        $(this).parent().parent().remove();
    });
    
    $("#add_attribute_set_locations").click(function () {
        var attribute_set_id = $('#attribute_sets_id').val();
        var attribute_set_name = $('#attribute_sets_id option:selected').text();
        var location_id = $('#locations').val();
        var location_name = $('#locations option:selected').text();
        
        if(attribute_set_id == 0)
        {
            alert('Please select attribtue set.');
        }else if(location_id == 0)
        {
            alert('Please select locations.');
        }else{
            var attributeSetElements = new Array();
            $('[id="attribute_set_name"]').each(function(){
                attributeSetElements.push($(this).text()+'##'+$(this).next('td#location_name').text());
            });
            var temp;
            temp = attribute_set_name+'##'+location_name;
            if(attributeSetElements.length > 0 && $.inArray(temp, attributeSetElements) >= 0)
            {
                alert('This element already added.');
            }else{
                var jsonArg2 = new Object();
                jsonArg2.attribute_set_id = attribute_set_id;
                jsonArg2.location_id = location_id;

                var hiddenJsonData1 = new Array();
                hiddenJsonData1.push(jsonArg2);

                $("#attribute_location_data").append('<tr><td scope="row" id="attribute_set_name">' + attribute_set_name + '</td><td id="location_name">' + location_name
                        + '</td><td><a href="javascript:void(0);" class="check-toggler" onclick="alertDelete()" id="remCF1"><span class="badge bg-red"><i class="fa fa-trash-o"></i></span></a><input type="hidden" name="product_attribute_sets[attribute_details][]" value=' + "'" + JSON.stringify(jsonArg2) + "'" + ' /></td></tr>');
            }
        }
    });
    $("#attribute_location_data").on('click', '#remCF1', function () {
        $(this).parent().parent().remove();
    });
        
        
        
    $("#add_slab_rate").click(function () {
        var slab_range = $('[name="slab_rate[end_range]"]').val();
        var slab_price = $('[name="slab_rate[price]"]').val();
        var temp = 1;
        if(slab_range == '')
        {
            alert('Please enter Slab Upper Limit');
            $('[name="slab_rate[end_range]"]').focus();
            temp = 0;
            return false;
        }else{
            $("#slab_rate_data td.slab_range").each(function() {
                var previousRange = $(this).text();
                if(previousRange == slab_range)
                {
                    $('[name="slab_rate[end_range]"]').focus();
                    alert('This range is already provided this price slab.');
                    temp = 0;
                    return false;
                }
            });
        }
        if(slab_price == '')
        {
            alert('Please enter price');
            $('[name="slab_rate[price]"]').focus();
            temp = 0;
            return false;
        }
        if(temp)
        {
            var jsonArg2 = new Object();
            jsonArg2.end_range = slab_range;
            jsonArg2.price = slab_price;
            var slabData = new Array();
            slabData.push(jsonArg2);
            $("#slab_rate_data").append('<tr><td class="slab_range">' + slab_range + '</td><td>' + slab_price
                    +'</td><td><a href="javascript:void(0);" class="check-toggler" id="remSlabRates"><span class="badge bg-red"><i class="fa fa-trash-o"></i></span></a><input type="hidden" name="slab_rate[rates][]" value=' + "'" + JSON.stringify(jsonArg2) + "'" + ' /></td></tr>');
        }
    });
    
    $("#slab_rate_data").on('click', '#remSlabRates', function () {
        $(this).parent().parent().remove();
    });
 
    function alertDelete()
{   
    var del = $('#remCF','#remCF1').val();
    if(del = true)
    {
        alert('Are you sure you want to delete?');
    }
}
    
function appendData()
{
    $('#dynamic').empty();
    var attribute_set_id = $('#main_attribute_set_id').find("option:selected").val();
    url = '/product/getattributelist';
    // Send the data using post
    if(attribute_set_id == '')
    {
        return;
    }
    var requiredFields = [];
    var posting = $.post(url, { attribute_set_id: attribute_set_id });
    // Put the results in a div
    posting.done(function( data ) {
        responseData = JSON.parse(data);
        if(responseData == 'No Attribute group Id')
        {
            return;
        }
        var tempCloneData;
        var countRows = 1;
        var len = responseData.length;
        $.each(responseData, function(key, attributes) {            
            if (typeof attributes.attribute_id != 'undefined') {
                var attribute_id = attributes.attribute_id;
                var attribute_code = attributes.attribute_code;
                var attribute_name = attributes.name;
                var input_type = attributes.input_type;
                var is_dynamic = attributes.is_dynamic;
                var is_required = attributes.is_required;
                var attribute_type = attributes.attribute_type;
                var options = attributes.options;
                var outputElement = '';
                switch(input_type)
                {
                    case 'input':
                        var $template1 = $('#inputTemplate'),
                        $clone1    = $template1.clone().attr('id', 'addNext_'+key);
                        $clone1.find('label').text(attribute_name);
                        $clone1.find('label').attr('for', attribute_code);
                        $clone1.find('input').attr('id', attribute_code);
                        $clone1.find('input').attr('name', 'attributes[' + attribute_code +']');
                        if(is_required)
                        {
                            $clone1.find('input').prop('required', true);
                            //$('#product_creation').formValidation('addField', $clone1);
                        }
                        //$clone1.insertBefore(dynamic);
                        outputElement = $clone1;
                        /*outputElement = '<div class="row"><div class="form-group col-sm-6"><label for="exampleInputEmail">'+attribute_name+
            '</label><div class="input-group input-group-sm"><input class="form-control" type="text" id="'+ attribute_code +'" name="attributes[' + attribute_code +']" value="" /></div></div></div>';*/
                        break;
                    case 'text':
                        var $template1 = $('#inputTemplate'),
                        $clone1    = $template1.clone().attr('id', 'addNext_'+key);
                        $clone1.find('label').text(attribute_name);
                        $clone1.find('label').attr('for', attribute_code);
                        $clone1.find('input').attr('id', attribute_code);
                        if(is_required)
                        {
                            $clone1.find('input').prop('required', true);
                            //$('#product_creation').formValidation('addField', $clone1);
                        }
                        $clone1.find('input').attr('name', 'attributes[' + attribute_code +']');
                        //$clone1.insertBefore(dynamic);
                        outputElement = $clone1;
                        /*outputElement = '<div class="row"><div class="form-group col-sm-6"><label for="exampleInputEmail">'+attribute_name+
            '</label><div class="input-group input-group-sm"><input class="form-control" type="text" id="'+ attribute_code +'" name="attributes[' + attribute_code +']" value="" /></div></div></div>';*/
                        break;
                    case 'checkbox':
                        var $template2 = $('#checkboxTemplate'),
                        $clone2    = $template2.clone().attr('id', 'addNext_'+key);
                        $clone2.find('label').text(attribute_name);
                        $clone2.find('label').attr('for', attribute_code);
                        $clone2.find('input').attr('id', attribute_code);
                        $clone2.find('input').attr('name', 'attributes[' + attribute_code +']');
                        if(is_required)
                        {
                            $clone2.find('input').prop('required', true);
                        }
                        //$clone2.insertBefore(dynamic);
                        outputElement = $clone2;
                        /*outputElement = '<div class="row"><div class="form-group col-sm-6"><label for="exampleInputEmail">'+attribute_name+
            '</label><div class="input-group input-group-sm"><input type="checkbox" id="'+ attribute_code +'" name="attributes[' + attribute_code +']" value="" /></div></div></div>';*/
                        break;
                    case 'radio':
                        var $template3 = $('#radioTemplate'),
                        $clone3    = $template3.clone().attr('id', 'addNext_'+key);
                        $clone3.find('label').text(attribute_name);
                        $clone3.find('label').attr('for', attribute_code);
                        $clone3.find('input').attr('id', attribute_code);
                        $clone3.find('input').attr('name', 'attributes[' + attribute_code +']');
                        if(is_required)
                        {
                            $clone3.find('input').prop('required', true);
                        }
                        //$clone3.insertBefore(dynamic);
                        outputElement = $clone3;
                        /*outputElement = '<div class="row"><div class="form-group col-sm-6"><label for="exampleInputEmail">'+attribute_name+
            '</label><div class="input-group input-group-sm"><input type="radio" id="'+ attribute_code +'" name="attributes[' + attribute_code +']" value="" /></div></div></div>';*/
                        break;
                    case 'select':
                        var $template4 = $('#selectTemplate');
                        $clone4    = $template4.clone().attr('id', 'addNext_'+key);
                        $clone4.find('label').text(attribute_name);
                        $clone4.find('label').attr('for', attribute_code);
                        $clone4.find('select').attr('id', attribute_code);
                        $clone4.find('select').attr('name', 'attributes[' + attribute_code +']');
                        if(is_required)
                        {
                            $clone4.find('select').prop('required', true);
                        }
                        //$clone4.insertBefore(dynamic);
                        outputElement = $clone4;
                        break;  
                    case 'textarea':
                        var $template5 = $('#textareaTemplate'),
                        $clone5    = $template5.clone().attr('id', 'addNext_'+key);
                        $clone5.find('label').text(attribute_name);
                        $clone5.find('label').attr('for', attribute_code);
                        $clone5.find('textarea').attr('id', attribute_code);
                        $clone5.find('textarea').attr('name', 'attributes[' + attribute_code +']');
                        if(is_required)
                        {
                            $clone5.find('textarea').prop('required', true);
                        }
                        //$clone5.insertBefore(dynamic);
                        outputElement = $clone5;
                        /*outputElement = '<textarea class="form-control" type="text" name="attributes[' + attribute_code +']" id="'+ attribute_code +'" row="4"></textarea>';*/
                        break;
                    case 'file':
                        var $template6 = $('#fileTemplate'),
                        $clone6    = $template6.clone().attr('id', 'addNext_'+key);
                        $clone6.find('label').text(attribute_name);
                        $clone6.find('label').attr('for', attribute_code);
                        $clone6.find('input').attr('id', attribute_code);
                        $clone6.find('input').attr('name', 'attributes[' + attribute_code +']');
                        if(is_required)
                        {
                            $clone5.find('input').prop('required', true);
                        }
                        //$clone6.insertBefore(dynamic);
                        outputElement = $clone6;
                        /*outputElement = '<textarea class="form-control" type="text" name="attributes[' + attribute_code +']" id="'+ attribute_code +'" row="4"></textarea>';*/
                        break;    
                    case 'multiselect':
                        var $template7 = $('#multiselectTemplate'),
                        $clone7    = $template7.clone().attr('id', 'addNext_'+key);
                        $clone7.find('label').text(attribute_name);
                        $clone7.find('label').attr('for', attribute_code);
                        $clone7.find('select').attr('id', attribute_code);
                        $clone7.find('select').attr('name', 'attributes[' + attribute_code +']');
                        if(is_required)
                        {
                            $clone5.find('select').prop('required', true);
                        }
                        //$clone7.insertBefore(dynamic);
                        outputElement = $clone7;
                        /*outputElement = '<textarea class="form-control" type="text" name="attributes[' + attribute_code +']" id="'+ attribute_code +'" row="4"></textarea>';*/
                        break;    
                        case 'date':
                            var $template1 = $('#dateTemplate'),
                                    $clone1 = $template1.clone().attr('id', 'addNext_' + key);
                            $clone1.find('label').text(attribute_name);
                            $clone1.find('label').attr('for', attribute_code);
                            $clone1.find('input').attr('id', attribute_code);
                            $clone1.find('input').attr('name', 'attributes[' + attribute_code + ']');
                            $clone1.find('input').val(value);
                            $clone1.insertBefore(dynamic);
                            outputElement = $clone1;
                            /*outputElement = '<div class="row"><div class="form-group col-sm-6"><label for="exampleInputEmail">'+attribute_name+
                             '</label><div class="input-group input-group-sm"><input class="form-control" type="text" id="'+ attribute_code +'" name="attributes[' + attribute_code +']" value="" /></div></div></div>';*/
                            break;
                    default:
                        break;
                }
                if(1 == attribute_type && is_required)
                {
                    requiredFields[key] = 'attributes[' + attribute_code + ']';
                }
            }
            var rowHeader = '<div class="row" id="addNext_'+key+'">';
            var rowFooter = '</div>';
            if(countRows == 2)
            {   
                countRows = 1;
                tempFields = rowHeader + tempCloneData.html() + outputElement.html() + rowFooter;
                $('#dynamic').append(tempFields);
                tempCloneData = [];
            }else{                
                if (key == len - 1) {
                    tempFields = rowHeader + outputElement.html() + rowFooter;
                    $('#dynamic').append(tempFields);
                    tempCloneData = [];
                }else{                    
                    tempCloneData = outputElement;
                    countRows++;
                }
            }
        });
        $.each(requiredFields, function(fieldKey, fieldName){
            $('#product_creation').formValidation('addField', fieldName);
        });
    });
}
$('#business_unit_submit_button').click(function(event){
    $('#business_unit_form').formValidation();
    if($('#business_unit_form').data('formValidation').isValidField('business_unit[name]'))
    {
        $('#business_unit_submit_button').attr("disabled", true);
        $form = $('#business_unit_form');
        url = $form.attr('action');
        // Send the data using post
        var posting = $.post(url, { 'name' : $('[name="business_unit[name]"]').val(), 'manufacturer_id' : $('[name="business_unit[manufacturer_id]"]').val(), 'description' : $('[name="business_unit[description]"]').val() });
        // Put the results in a div
        posting.done(function( data ) {
            if(data)
            {
                responseData = JSON.parse(data);
                $.each(responseData, function(key, value){
                    $('<option>').val(key).text(value).appendTo('#business_unit_id');
                    $('<option>').val(key).text(value).appendTo('#locations_business_unit_id');
                    $('#locations_business_unit_id').selectpicker('refresh');
                    $('#business_unit_id').val(key);                                
                });
                alert('Sucessfully added business unit.');
                $('#business_unit_form').bootstrapValidator('resetForm', true);
                $('.close').trigger('click');
                $('#business_unit_submit_button').attr("disabled", false);
                $('.selectpicker').selectpicker('refresh');
            }else{
                alert('Unable to added business unit.');
                $('#business_unit_submit_button').attr("disabled", false);
            }
        });
    }
});

$('#attibute_form').submit(function(event){
    $('#attibute_form').formValidation();
    event.preventDefault();
    if($('#attibute_form').data('formValidation').isValid())
    {
        event.preventDefault();
        $('#attribute_submit_button').attr("disabled", true);
        $form = $(this);
        url = $form.attr('action');
        var attributeFields = new Array();
        // Send the data using post
        var attributeFields = { attribute_group_id: $('[name="attribute[attribute_group_id]"]').val(), 
            attribute_set_id: $('[name="attribute[attribute_set_id]"]').val(),
            attribute_type: $('[name="attribute[attribute_type]"]').val(),
            default_value: $('[name="attribute[default_value]"]').val(),
            input_type: $('[name="attribute[input_type]"]').val(),
            is_required: $('[name="attribute[is_required]"]').val(),
            length: $('[name="attribute[length]"]').val(),
            lookup_id: $('[name="attribute[lookup_id]"]').val(),
            manufacturer_id: $('[name="attribute[manufacturer_id]"]').val(),
            name: $('[name="attribute[name]"]').val(),
            regexp: $('[name="attribute[regexp]"]').val(),
            text: $('[name="attribute[text]"]').val(),
            validation: $('[name="attribute[validation]"]').val() };
        var posting = $.post(url, { attributeFields });
        // Put the results in a div
        posting.done(function( data ) {
            console.log(data);
            if(data)
            {
                appendData();
                alert('Sucessfully added attribute set.');
                $('.close').trigger('click');
                $('#attribute_submit_button').attr("disabled", false);
            }else{
                alert('Unable to added attribute set.');
                $('#attribute_submit_button').attr("disabled", false);
            }
        });
    }
});
$('#attribute_set_form').submit(function(event){
    $('#attribute_set_form').formValidation();
    event.preventDefault();
    if($('#attribute_set_form').data('formValidation').isValid())
    {
        $('#attribute_set_button').attr("disabled", true);
        event.preventDefault();
        $form = $(this);
        url = $form.attr('action');
        var attributeSetFields = { attribute_set_name: $('[name="attribute_set[attribute_set_name]"]').val(), category_id: $('#category_id').val(), inherit_from: $('[name="attribute_set[inherit_from]"]:checked').length, is_active: $('#is_active').val(), manufacturer_id: $('[name="attribute_set[manufacturer_id]"]').val() };
        // Send the data using post
        var posting = $.post(url, { attribute_set: attributeSetFields });
        // Put the results in a div
        posting.done(function( data ) {
            if(data)
            {
                $('<option>').val(data.set_id).text(data.set_name).appendTo('#main_attribute_set_id');            
                $('#main_attribute_set_id').val(data.set_id);
                $('.selectpicker#main_attribute_set_id').selectpicker('refresh');
                $('#main_attribute_set_id').trigger('change');
                alert('Sucessfully added attribute set.');
                $('.close').trigger('click');
                $('#attribute_set_button').attr("disabled", false);
                
                $('<option>').val(data.set_id).text(data.set_name).appendTo('#attribute_sets_id');            
                $('#attribute_sets_id').val(data.set_id);
                $('.selectpicker#attribute_sets_id').selectpicker('refresh');
                
            }else{
                alert('Unable to added attribute set.');
                $('#attribute_set_button').attr("disabled", false);
            }
        });
    }
});
//added for AttributeSet
$('[data-target="#addAttributeSet"]').click(function(){
    var manufacturer_id = $('#main_manufacturer_id').val();
    var url = '/product/attributes';
    // Send the data using post
    var posting = $.get( url, { manufacturer_id : manufacturer_id } );
    // Put the results in a div
    posting.done(function( data ) {
        var result = JSON.parse(data);              
        $.each(result, function(key, value){
            /*$('#Selectattribute').append('<option value="' + value['attribute_id'] + '">' + value['name'] + '</option>');  */ 
            /*$('#Selectattribute').append('<li value="' + value['attribute_id'] + '">' + value['name'] + '</li>');*/
            $('#Selectattribute').append('<div class="fc-field" value="' + value['attribute_id'] + '">' + value['name'] + '</div>');         
        });
    });
    //getAttributes();
});
//added for AttributeSet
var jsArray = <?php echo json_encode($data); ?>;
$.each($('input, select ,textarea', '#product_creation'),function(k){
    var el = $(this);  
    var elementId = el.attr('id');
    if(el.is('input')) { //we are dealing with an input
        var type = el.attr('type'); //will either be 'text', 'radio', or 'checkbox
    } else if(el.is('select')) { //we are dealing with a select
        //code here       
        if(jsArray.hasOwnProperty(elementId))
        {
            reloadSelect(jsArray[elementId]['options'], elementId);
        }
    } else if(el.is('textarea')) { //we are dealing with a select
        //code here
        //console.log('Id => ' + el.attr('id') + ' Type => select');
    } else { //we are dealing with a textarea
        //code here
        console.log('Id => ' + el.attr('id') + ' Type => '+el);
    }
    if(typeof jsArray[elementId]  != 'undefined')
    {
        $("#"+elementId).attr('name', jsArray[elementId]['name']);
    }    
});
$.each($('input, select ,textarea', '#add_location_form'),function(k){
    var el = $(this);  
    var elementId = el.attr('id');
    if(el.is('input')) { //we are dealing with an input
        var type = el.attr('type'); //will either be 'text', 'radio', or 'checkbox
    } else if(el.is('select')) { //we are dealing with a select
        //code here       
        if(jsArray.hasOwnProperty(elementId))
        {
            reloadSelect(jsArray[elementId]['options'], elementId);
        }
    } 
    if(typeof jsArray[elementId]  != 'undefined')
    {
        $("#"+elementId).attr('name', jsArray[elementId]['name']);
    }
    $('#location_country_id').val(99);
    $('#country_input_id').val($('#location_country_id').val());
});

function reloadSelect(responseData, elementId)
{
    $("#"+elementId).empty();
    if(!$.isEmptyObject(responseData))
    {
        $.each(responseData, function(key, value) {
            $("#"+elementId).append('<option value="'+key+'">'+value+'</option>');
        });
        $('.selectpicker#'+elementId).selectpicker('refresh');
    }
}

function collapsDiv(id, validateThis)
{
    console.log('we are here in collapsDiv '+id);
    var temp = true;
    var fields = [];
    var fv   = $('#product_creation').data('formValidation');
    if(id == 'two')
    {
        fields = ['product[manufacturer_id]'];
        //temp = validateTab(fields);
        $.each(fields, function(element, value){        
            var isValidStep = fv.validateField(value).isValid();
            if (isValidStep === false || isValidStep === null) {
                // Do not jump to the target tab
                temp = false;
            }else{
                temp = true;
            }
        });
    }
    if(validateThis != 0 && (id == 'one' || id == 'three'))
    {
        //fields = ['product[name]', 'product[product_type_id]', 'product[business_unit_id]', 'product[category_id][]'];
        fields = ['product[name]','product[product_type_id]','product[business_unit_id]','product[uom_class_id]','product[material_code]'];
        //temp = validateTab(fields);
        $.each(fields, function(element, value){
            if(value == 'files[]')
            {
                var isValidField = validateImage();
            }else{
                var isValidStep = fv.validateField(value).isValid();
                var isValidField = fv.isValidField(value);
            }
            if (isValidField === false || isValidField === null) {                
                // Do not jump to the target tab
                temp = false;
            }else{
                if(temp)
                {
                    temp = true;
                }                
            }
        });
    }
    if(validateThis != 0 && (id == 'four')){

    }
    if(validateThis != 0 && (id == 'five'))
    {
        $('input,textarea,select').filter('[required]').each(function(){
            //console.log($(this).attr('name'));
            var value = $(this).attr('name');
            var isValidStep = fv.validateField(value).isValid();
            var isValidField = fv.isValidField(value);
            if (isValidField === false || isValidField === null) {                
                // Do not jump to the target tab
                temp = false;
            }else{
                if(temp)
                {
                    temp = true;
                }                
            }
        });
    }
    if(temp)
    {
        $('#'+id).trigger('click');
    }
}

function validateImage()
{
    var fileName = $('#files').children().find('input');
    if(typeof fileName.val() != 'undefined')
    {
        $('#upload_field').children('div.form-group').children('i.form-control-feedback').removeClass('glyphicon-remove').addClass('glyphicon-ok');
        $('#upload_field').removeClass('has-error');
        $('#upload_field').children('div.col-sm-10').children('small').hide();
        return true;
    }else{
        $('#upload_field').children('div.form-group').children('i.form-control-feedback').removeClass('glyphicon-ok').addClass('glyphicon-remove');
        $('#upload_field').removeClass('has-success');
        $('#upload_field').addClass('has-error');
        $('#upload_field').children('div.col-sm-10').children('small').show();
        return false;
    }
}

$('#is_pallet').click(function(){
        if($('#is_pallet').prop('checked') == true)
        {
            $('#pallet_stack_height').prop('readonly', false);
        }else{
            $('#pallet_stack_height').val('');
            $('#pallet_stack_height').prop('readonly', true);
        }
    });
    $('#product_type_id').change(function(){
        var product_type_id = $(this).val();
        if(8003 == product_type_id)
        {
            $('.component').show();
        }else{
            $('.component').hide();            
        }
    });
    
    function loadComponentProducts()
    {
        $('#component_product_default_list').val('');
        $('#component_product_selected_list').val('');
        var manufacturerId = $('#main_manufacturer_id').val();
        if(manufacturerId > 0)
        {
            url = '/product/getelementdata';
            // Send the data using post
            var posting = $.post(url, { data_type: 'component_products', data_value: manufacturerId });

            // Put the results in a div
            posting.done(function( data ) {
                if(data)
                {
                    var temp = '';
                    $.each(data, function(key, value){
                        temp = temp + key + ',';
                        $('#component_product_list').append('<div class="fc-field" value="' + key + '">' + value + ' <input type="hidden" value="' + key + '" /></div>');
                    });
                    $('#component_product_default_list').val(temp);
                }
            });
        }else{
            $('#component_product_list').empty();
        }
    }
    
    $(document).ready(function () {
        var $sourceFields = $("#component_product_list");
        var $destinationFields = $("#component_product_selected");
        var $chooser = $("#fieldChooser").fieldChooser(component_product_list, component_product_selected);
        $( ".fc-destination-fields" ).droppable({
            over: function( event, ui ) {
                pushData(ui.draggable.find('input').val());
            },
            out: function( event, ui ) {
                popData(ui.draggable.find('input').val());
            }
        });
    });
    
    function pushData(inputData)
    {
        var result = '';
        var selectedList = $('#component_product_selected_list').val();
        if(selectedList == '')
        {
            result = inputData;
        }else{
            result = selectedList+','+inputData;
        }
        $('#component_product_selected_list').val(result);
    }
    
    function popData(inputData)
    {
        var result = '';
        var selectedList = $('#component_product_selected_list').val();
        if(selectedList == '')
        {
            result = inputData;
        }else{
            result = jQuery.grep(selectedList.split(","), function(value) {
                return value != inputData;
            });
        }
        $('#component_product_selected_list').val(result);
    }
    
    $("#location_country_id").on('change', function () {
        $('#country_input_id').val($(this).val());
        ajaxCall($(this).val(), 'location_state_options', 0, 1);
    });
    function ajaxCall(countryId, stateId, isMultiselect, isKey)
    {
        $('#'+stateId).find('option').remove();
        $.get('/customer/getZones?countryId=' + countryId, function (data) {
            var result = $.parseJSON(data);
            $('#'+stateId).find('option').remove().end();
            $.each(result, function (k, v) {
                if(isKey)
                {
                    $('#'+stateId).append($("<option>").attr('value', k).text(v));
                }else{
                    $('#'+stateId).append($("<option>").attr('value', v).text(v));
                }
                
            });
            if(isMultiselect)
            {
                $('#'+stateId).multiselect({
                    enableFiltering: true
                }).multiselect('rebuild');
                $('#'+stateId).multiselect('rebuild');
            }else{
                $('#'+stateId).selectpicker('refresh');
            }
        });
    }
    
    $('#add_location_form').bootstrapValidator({
    //        live: 'disabled',
            message: 'This value is not valid',
            feedbackIcons: {
                valid: 'glyphicon glyphicon-ok',
                invalid: 'glyphicon glyphicon-remove',
                validating: 'glyphicon glyphicon-refresh'
            },
            fields: {
                location_name: {
                    validators: {
                        remote: {
                            url: '/customer/uniquevalidation',
                            type: 'POST',
                            data: {
                                table_name: 'locations', 
                                field_name: 'location_name', 
                                field_value: $('#add_location_form #location_name').val(), 
                                manufacturer_id: $('[name="customer_id"]').val(),
                                pluck_id: 'location_id' 
                            },
                            delay: 2000,     // Send Ajax request every 2 seconds
                            message: 'Name already exists.'
                        },                    regexp: {
                        regexp: /^[a-z\s]+$/i,
                            message: 'The Location Name can consist of alphabetical characters and spaces only'
                        },
                        notEmpty: {
                            message: 'The location name is required and can\'t be empty'
                        }                        
                    }
                },
                manufacturer_name: {
                    validators: {
                        notEmpty: {
                            message: 'The manufacturer name is required and can\'t be empty'
                        }                        
                    }
                },
                location_type_id: {
                    validators: {
                        callback: {
                            message: 'Please choose manufacturer',
                            callback: function(value, validator, $field) {
                                return (value != 0);
                            }
                        },
                        notEmpty: {
                            message: 'The location type is required and can\'t be empty'
                        }                        
                    }
                },
                location_email: {
                    validators: {
                        notEmpty: {
                            message: 'The email is required and can\'t be empty'
                        },
                        emailAddress: {
                            message: 'The value is not a valid email address'
                        }
                    }
                },
                longitude:{
                    validators: {
                        between: { 
                            min: -180,
                            max: 180,
                            message: 'The longitude must be between -180.0 and 180.0'
                        }
                    }
                },
                latitude:{
                    validators: {
                        between: {
                            min: -90,
                            max: 90,
                            message: 'The latitude must be between -90.0 and 90.0'
                        }

                    }
                }
        }
        }).on('success.form.bv', function(event) {
            var $form = $('#add_location_form');
            var formData = prePostData($form.serialize());
            $.post($form.attr('action'), formData, function (data) {        
                if(data)
                {
                    alert(data.message);
                    if ( data.status === true ) {
                        console.log(data.location_id);
                        $('#product_locations').append('<option value="'+data.location_id+'" selected>'+$form.find('[name="location_name"]').val()+'</option>');
                        $('#product_locations').selectpicker('refresh');
                        $('.close').click();
                        $form.data('bootstrapValidator').resetForm();
                        $form[0].reset();
                        $form.find('[name="location_country_id"]').val(99);
                    }
                }
            });
            return false;
        }).validate({
        submitHandler: function (form) {
            return false;
        }
    });    
    function prePostData(formData)
    {
        console.log(formData);
        return formData;
    }
    function postData(data)
    {
        console.log(data);
        console.log('we are in create');
        //location.reload();
        //attributeGroups();
        appendData();
    }
</script>    
@stop

@stop
@extends('layouts.footer')
