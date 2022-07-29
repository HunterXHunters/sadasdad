<style type="text/css">
    .modal-dialog {
    margin: 30px auto;
    width: 90% !important;
}
    
</style>


<!-- Page content -->
<!-- Page content -->
<?php $attributeData = (object) $productData->attribute_data; ?>
<?php $attributesData = $attributeData; ?>
<?php $palletData = (object) $productData->pallet_data; ?>
<?php $packageData = (object) $productData->package_data; ?>
<?php $mediaData = (object) $productData->media_data; ?>
<?php $productAttributeSets = (object) $productData->product_attributesets; ?>
{{ Form::open(array('url' => 'product/editsave', 'method' => 'POST', 'files'=>true, 'id' => 'product_creation')) }}
<div class="main">           
    <div id="error_message" class="error">
        @if($errors->any())
        <?php echo $errors->first(); ?>
        @endif
    </div>
    <div class="row">
        <div class="col-md-12">
            <!-- tile body -->
            <div class="tile-body">
                <section class="tile">
                    <div class="panel-group" id="accordion" role="tablist" aria-multiselectable="true">
                        <div class="panel panel-default"> 
                            <a role="button" data-toggle="collapse" data-parent="#accordion" href="#collapseOne" aria-expanded="true" aria-controls="collapseOne" id="one">
                                <div class="panel-heading panel-new" role="tab" id="headingOne">
                                    <h4 class="panel-title"> Product </h4>
                                </div>
                            </a>
                            <div id="collapseOne" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="headingOne">
                                <div class="panel-body">
                                    <section class="content">
                                        <div class="row">
<div class="box box-primary">
                                            <div class="col-md-6">
                                                <div class="row" style="display: none;">
                                                    <div class="form-group col-sm-6">
                                                        <label for="exampleInputEmail">Manufacturer Name</label>
                                                        <div id="selectbox">
                                                            <select class="form-control" data-live-search="true" name="product[manufacturer_id]" id="main_manufacturer_id" parsley-trigger="change" parsley-required="true" parsley-error-container="#selectbox">
                                                                <option value="">Please choose</option>
                                                                @foreach ($data['general']['manufacturer_data']['options'] as $key => $value)
                                                                <option value="{{ $key }}">{{ $value }}</option>
                                                                @endforeach
                                                            </select>                                                    
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-4">
                                                        <input type="hidden" name="request_from" value="gds" />
                                                        <input type="hidden" name="product_id" value="{{ $productData->product_id }}" />
                                                        <input type="hidden" name="attribute_set_id" value="{{ $productData->attribute_set_id }}" />
                                                        <p>Product Image</p>
                                                        <div class="btn btn-block btn-success btn-file"> <i class="fa fa-cloud-upload"></i> Upload Image
                                                            <input id="fileupload" type="file" name="files[]" multiple>
                                                        </div>
                                                        <div class="input-group-sm">
                                                            <table role="presentation" class="table table-striped"><tbody id="files" class="files"></tbody></table>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-8">
                                                        <div class="form-group">
                                                            <label for="Title">Title*</label>
                                                            <input type="text" class="form-control" id="product_title" value="" placeholder="Brand Name">
                                                        </div>
                                                        <div class="form-group">
                                                            <label>Description*</label>
                                                            <textarea class="form-control" id="product_description" name="" value="" rows="4" placeholder="Description"></textarea>
                                                        </div>
                                                        <div class="form-group">
                                                            <label>Warranty Policy</label>
                                                            <textarea class="form-control" rows="3" id="warranty_policy" placeholder="Warranty Policy"></textarea>
                                                        </div>
                                                        <div class="form-group">
                                                            <label>Return Policy</label>
                                                            <textarea class="form-control" rows="3" id="return_policy" placeholder="Return Policy"></textarea>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <div class="form-group col-sm-6">
                                                            <label for="Category">Category*</label>
                                                            <div id="selectbox">
                                                                <select class="form-control" data-live-search="true"id="product_category" multiple="true" parsley-trigger="change" parsley-required="true" parsley-error-container="#selectbox" style="height:107px;">
                                                                    <option value="">Please choose</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                
                                                    <div role="form">
                                                        <div class="box-body">
                                                            <div class="form-group">
                                                                <label for="Brand Name">Brand Name</label>
                                                                <input type="text" class="form-control" id="brand_name" placeholder="Brand Name">
                                                            </div>
                                                            <div class="form-group">
                                                                <label for="MRP">Model Number</label>
                                                                <input type="text" class="form-control" id="model_name" placeholder="Model Number" value="{{$productData->model_name}}">
                                                            </div>
                                                            <div class="form-group">
                                                                <label for="MRP">SKU Number</label>
                                                                <input type="text" class="form-control" id="sku" placeholder="SKU Number" value="{{$productData->sku}}">
                                                            </div>
                                                            <div class="form-group">
                                                                <label for="MRP">MRP</label>
                                                                <input type="text" class="form-control" id="mrp" placeholder="2000" value="{{$productData->model_name}}">
                                                            </div>
                                                            <div class="form-group">
                                                                <label for="MRP">MSP</label>
                                                                <input type="text" class="form-control" id="msp" placeholder="2000" value="{{$productData->msp}}">
                                                            </div>
                                                            <div class="form-group">
                                                                <label>HTML Description</label>
                                                                <textarea class="form-control" rows="3" id="prod_desc" placeholder="HTML Description"></textarea>
                                                            </div>
                                                        </div>
                                                    </div>
                                                
                                            </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="box-header">
                                                <h3 class="box-title">Dimensions</h3>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label for="Height">Height</label>
                                                    <input type="text" class="form-control" id="height" value="{{$productData->height}}">
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label for="Width">Width</label>
                                                    <input type="text" class="form-control" id="width" value="{{$productData->width}}">
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label for="Width">Length</label>
                                                    <input type="text" class="form-control" id="length" value="{{$productData->length}}">
                                                </div>
                                            </div>
                                            <div class="col-md-3" style="margin-top:16px;">
                                                <button type="button" class="btn btn-info" onClick="collapsDiv('two')">Next</button>
                                            </div>
                                        </div>
                                    </section>
                                </div>
                            </div>
                        </div>
                        <div class="panel panel-default"> 
                            <a class="collapsed" role="button" data-toggle="collapse" data-parent="#accordion" href="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo" id="two">
                                <div class="panel-heading panel-new" role="tab" id="headingTwo">
                                    <h4 class="panel-title"> Images & Videos </h4>
                                </div>
                            </a>
                            <div id="collapseTwo" class="panel-collapse collapse" role="tabpanel" aria-labelledby="headingTwo">
                                <div class="panel-body">
                                    <section class="content">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="box box-primary">
                                                    <div role="form">
                                                        <div class="box-body">
                                                            <div class="form-group">
                                                                <label for="Product Image">Product Image</label>
                                                                <div class="btn btn-default btn-file"> <i class="fa fa-image"></i> Product Image
                                                                    <input type="file" name="Product Image">
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="box box-primary">
                                                    <div role="form">
                                                        <div class="box-body">
                                                            <div class="form-group">
                                                                <label for="Product Video">Product Video</label>
                                                                <div class="btn btn-default btn-file"> <i class="fa fa-file-video-o"></i> Product Video
                                                                    <input type="file" name="Product Image">
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-md-12">
                                                            <div style="background: #f7f8fa; position:relative; left:-130px;">
                                                                <input type="file" multiple name="files[]" id="input2">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-12" >
                                                <div class="margin" style="float:right">
                                                    <div class="btn-group">
                                                        <button type="button" class="btn btn-default" onClick="collapsDiv('one')" >Back</button>
                                                    </div>
                                                    <div class="btn-group">
                                                        <button type="button" class="btn btn-info" onClick="collapsDiv('three')" >Next</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </section>
                                </div>
                            </div>
                        </div>
                        <div class="panel panel-default"> 
                            <a class="collapsed" role="button" data-toggle="collapse" data-parent="#accordion" href="#collapseThree" aria-expanded="false" aria-controls="collapseThree" id="three">
                                <div class="panel-heading panel-new" role="tab" id="headingThree">
                                    <h4 class="panel-title"> SEO </h4>
                                </div>
                            </a>
                            <div id="collapseThree" class="panel-collapse collapse" role="tabpanel" aria-labelledby="headingThree">
                                <div class="panel-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="box box-primary">
                                                <div role="form">
                                                    <div class="box-body">
                                                        <div class="form-group">
                                                            <label for="Product Tag Title">Meta Title</label>
                                                            <input type="Product Tag Title" class="form-control" id="product_tag" placeholder="Product Tag Title">
                                                        </div>
                                                        <div class="form-group">
                                                            <label>Meta Description</label>
                                                            <textarea class="form-control" rows="3" placeholder="Product Tag Description"></textarea>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="box box-primary">
                                                <div role="form">
                                                    <div class="box-body">
                                                        <div class="form-group">
                                                            <label for="Product Tags">Meta keywords</label>
                                                            <input type="Product Tags" class="form-control" id="Product Tags" placeholder="Product Tags">
                                                        </div>

                                                    </div>
                                                </div>
                                                <div class="col-md-12" >
                                                    <div class="margin" style="float:right">
                                                        <div class="btn-group">
                                                            <button type="button" class="btn btn-default" onClick="collapsDiv('two')" >Back</button>
                                                        </div>
                                                        <div class="btn-group">
                                                            <button type="button" class="btn btn-info" onClick="collapsDiv('four')" >Next</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <h4 class="panel-title">
                                    <a data-toggle="collapse" data-parent="#accordion" href="#collapseFour" id="four">
                                        Attribute
                                    </a>
                                </h4>
                            </div>
                            <div id="collapseFour" class="panel-collapse collapse">
                                <div class="panel-body">
                                    <div class="row">
                                        <div class="form-group col-sm-3">
                                            <label for="Produc Attribute Set">Product Attribute Set</label>
                                            <div id="selectbox">
                                                <select class="list-unstyled selectpicker" data-live-search="true" id="attribute_sets_id" parsley-trigger="change" parsley-required="true" parsley-error-container="#selectbox" style="display: none;">
                                                    <option value="">Please choose</option>
                                                    <option value="1">1</option>
                                                    <option value="2">2</option>
                                                    <option value="3">3</option>
                                                    <option value="4">4</option>
                                                    <option value="5">5</option>
                                                </select>
                                            </div>                                                    
                                        </div>
                                        <div class="form-group col-sm-3">
                                            <label for="Produc Attribute Set">Locations</label>
                                            <div id="selectbox">
                                                <select class="list-unstyled selectpicker" data-live-search="true" id="locations" parsley-trigger="change" parsley-required="true" parsley-error-container="#selectbox" style="display: none;">                                                            
                                                </select>
                                            </div>
                                        </div>
                                        <div class="form-group col-sm-3">
                                            <label for="Produc Attribute Set"></label>
                                            <div class="col-xs-6" style="padding-left:0px; margin-top:22px;">
                                                <input type="button" class="btn btn-primary " data-toggle="modal" id="add_attribute_set_locations" value="Add" />
                                            </div>
                                        </div>                                                
                                    </div>
                                    <div class="row">
                                        <section class="tile">
                                            <div class="panel panel-default">
                                                <!-- Default panel contents -->
                                                <div class="panel-heading">Attribute location details</div>
                                                <!-- Table -->
                                                <table class="table" id="attribute_location_data">
                                                    <thead>
                                                        <tr>
                                                            <th>Attribute Set</th>
                                                            <th>Location</th>
                                                            <th style="width: 30px;">Actions</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </section>
                                    </div>
                                    <br/>
                                    <div class="row">
                                        <div class="form-group col-sm-3">
                                            <label for="Produc Attribute Set">Product Attribute Set</label>
                                            <div id="selectbox">
                                                <select class="list-unstyled selectpicker" data-live-search="true" id="main_attribute_set_id" parsley-trigger="change" parsley-required="true" parsley-error-container="#selectbox" style="display: none;">
                                                    <option value="">Please choose</option>
                                                    <option value="1">1</option>
                                                    <option value="2">2</option>
                                                    <option value="3">3</option>
                                                    <option value="4">4</option>
                                                    <option value="5">5</option>
                                                </select>
                                            </div>                                                    
                                        </div>    
                                        <div class="form-group col-sm-4">
                                            <div class="" style="padding-left:0px; border-left:0px;">
                                                <input type="button" class="btn btn-primary " style="margin-top:22px; margin-right:10px;" data-toggle="modal" data-target="#addAttributeSet" value="Add Attribute Set" />
                                                <input type="button" class="btn btn-primary " style="margin-top:22px;" data-toggle="modal" data-target="#add_attibute" value="Add Attribute" />
                                            </div>
                                        </div>
                                    </div>
                                    <h6>Add attribute List</h6>
                                    <div id="dynamic"></div>
                                    <div class="row"> 
                                        <div class="col-md-12" >
                                            <div class="margin" style="float:right">
                                                <div class="btn-group">
                                                    <button type="button" class="btn btn-default" onClick="collapsDiv('three')" >Back</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>                                            
                                </div>
                            </div>
                            <div class="navbar-fixed-bottom" role="navigation">
                                <div id="content product_page" class="col-md-12">
                                    <button class="btn btn-primary" onclick="validateForm()"><i class="fa fa-hdd-o"></i> Save</button>
                                    <button class="btn btn-primary" id="save_product" type="submit" style="display: none;"><i class="fa fa-hdd-o"></i> Submit</button>
                                    <!-- <button class="btn btn-default" onclick="window.history.back();"><i class="fa fa-times-circle"></i> Cancel</button> -->
                                    <!-- button class="btn btn-default" onclick="preview({{ $productData->product_id }});"><i class="fa fa-eye"></i> Preview</button -->
                                    
                                </div>           
                            </div>
                        </div>
                        <input type="hidden" id="category_id_selected" value="{{ $productData->category_id }}" />
                        <input type="hidden" id="location_id_selected" value="{{ $productData->location_id }}" />
                        {{Form::close()}}
                    </div>
                </section>
            </div>
        </div>
    </div>
</div>
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
                {{ Form::submit('Add', array('class' => 'btn btn-primary', 'id' => 'business_unit_submit_button')) }}
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
                {{ Form::open(array('url' => '/product/saveattributeset', 'id' => 'save_attribute_set')) }}
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
                                <option value="0">Please Select...</option>                                
                                @foreach ($data['product_category']['options'] as $key => $value)
                                <option value="{{ $key }}">{{ $value }}</option>
                                @endforeach
                            </select>
                        </div>  
                    </div>
                </div>
                <div class="row">
                    <div class="form-group col-sm-6">
                        <label for="exampleInputEmail">Manufacture Name</label>
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
                {{ Form::open(array('url' => '/product/saveattribute', 'id' => 'addAttribute')) }}
                {{ Form::hidden('_method', 'POST') }}

                <div class="row">
                    <div class="form-group col-sm-6">
                        <label for="exampleInputEmail">Attribute Set *</label>
                        <div class="input-group">
                            <span class="input-group-addon addon-red"><i class="fa fa-cubes"></i></span>
                            <input type="text" class="form-control"  readonly id="attibute_form_attribute_set_id" value="" />
                            <input type="hidden" class="form-control" readonly id="attribute_set_id" name="attribute_set_id" value="" />
                        </div>                        
                    </div>
                    <div class="form-group col-sm-6">                       
                        <input type="hidden" class="form-control"  readonly id="manufacturer_id" name="manufacturer_id" value="" />
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
                            <span class="input-group-addon addon-red"><i class="fa fa-cube"></i></span>
                            <input type="text"  id="attribute_code" name="attribute_code" placeholder="name" class="form-control">
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


 <div class="tile-body nopadding">                  
                    
 <button data-toggle="modal" id="addEntity" class="btn btn-default" data-target="#basicvalCodeModal" style="display: none" data-url="{{URL::asset('/products/preview')}}"></button>
                     
</div>
              
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
    <div class="row" id="dateTemplate">
        <div class="form-group col-sm-6">
            <label for="Pallet Width">Pallet Width</label>
            <div class="input-group input-group-sm">
                <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
                <input type="date" class="form-control" name="">
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
                            <select name="location_type_id" id="add_location_type_id" data-live-search="true" class="form-control">
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
                    <div class="form-group col-sm-6">
                        <label for="exampleInputEmail">ERP Code</label>
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
{{HTML::style('css/jquery.filer.css')}}

@stop

@section('script')
<!-- <script src="{{URL::asset('js/plugins/bootstrap-select/bootstrap-select.js')}}"></script>
<script src="{{URL::asset('js/plugins/bootstrap-select/bootstrap-datepicker.min.js')}}"></script> -->
<script src="{{URL::asset('js/plugins/jquery-file-upload/vendor/jquery.ui.widget.js')}}"></script>
<script src="{{URL::asset('js/plugins/jquery-file-upload/load-image.all.min.js')}}"></script>
<script src="{{URL::asset('js/plugins/jquery-file-upload/jquery.iframe-transport.js')}}"></script>
<script src="{{URL::asset('js/plugins/jquery-file-upload/jquery.fileupload.js')}}"></script>
<script src="{{URL::asset('js/plugins/jquery-file-upload/jquery.fileupload-process.js')}}"></script>
<script src="{{URL::asset('js/plugins/jquery-file-upload/jquery.fileupload-image.js')}}"></script>
<script src="{{URL::asset('js/plugins/jquery-file-upload/jquery.fileupload-audio.js')}}"></script>
<script src="{{URL::asset('js/plugins/jquery-file-upload/jquery.fileupload-video.js')}}"></script>
<script src="{{URL::asset('js/plugins/jquery-file-upload/jquery.fileupload-validate.js')}}"></script>
<script src="{{URL::asset('js/plugins/jquery-file-upload/upload-script.js')}}"></script>
<script src="{{URL::asset('js/plugins/dragdrop/jquery-ui.js')}}"></script>
<script src="{{URL::asset('js/plugins/dragdrop/fieldChooser.js')}}"></script>
<script src="{{URL::asset('js/plugins/bootstrap-select/bootstrap-select.js')}}"></script>
<script src="{{URL::asset('js/plugins/bootstrap-select/bootstrap-select.js')}}"></script>
<script src="{{URL::asset('js/plugins/validator/formValidation.min.js')}}"></script>
<script src="{{URL::asset('js/plugins/validator/validator.bootstrap.min.js')}}"></script>
<script src="{{URL::asset('js/plugins/validator/jquery.bootstrap.wizard.min.js')}}"></script>
<script src="{{URL::asset('js/jquery.filer.min.js')}}"></script>
<script src="{{URL::asset('js/plugins/dragdrop/filer.js')}}"></script>

<!-- <script src="{{URL::asset('scripts/jquery-1.10.2.min.js')}}"></script> -->
<script type="text/javascript">
    $(document).ready(function () {
        var $sourceFields = $("#Selectattribute");
        var $destinationFields = $("#attribute_id");
        var $chooser = $("#fieldChooser").fieldChooser(Selectattribute, attribute_id);
    });
    $('#addAttribute [name="name"]').keyup(function () {
        //console.log('Hi');
        $('#addAttribute [name="attribute_code"]').val($('#addAttribute [name="name"]').val().replace(/\s+/g, '_').toLowerCase());
        $('[name="attribute_code"]').change();
    });
    $(document).ready(function () {
        $('#product_creation').formValidation({
            //        live: 'disabled',
            //ignore: false,
            message: 'This value is not valid',
            feedbackIcons: {
                valid: 'glyphicon glyphicon-ok',
                invalid: 'glyphicon glyphicon-remove',
                validating: 'glyphicon glyphicon-refresh'
            },
            fields: {
                'product[title]': {
                    validators: {
                        notEmpty: {
                            message: 'Please enter title.'
                        }
                    }
                },
                'product[description]': {
                    validators: {
                        notEmpty: {
                            message: 'Please enter description.'
                        }
                    }
                },
                'product[category_id][]': {
                    validators: {
                        callback: {
                            message: 'Please choose product category',
                            callback: function (value, validator, $field) {
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
                'files[]': {
                    validators: {
                        file: {
                            extension: 'jpeg,png,jpg',
                            type: 'image/jpeg,image/png',
                            maxSize: 2097152, // 2048 * 1024
                            message: 'The selected file is not valid'
                        }
                    },
                    onSuccess: function (e, data) {
                        if ( !validateImage() )
                        {
                            $('#product_creation').formValidation('addField', $('[name="files[]"]', 'blank'));
                            return false;
                        } else {
                            $('#upload_field').removeClass('has-error');
                            $('#upload_field').children('div.col-sm-10').children('small').hide();
                        }
                    }
                }
            }
        }).on('success.form.bv', function (event) {
            event.preventDefault();
        });
        var jsArray = <?php echo json_encode($data); ?>;
        $.each($('input, select ,textarea', '#product_creation'), function (k) {
            var el = $(this);
            var elementId = el.attr('id');
            if ( el.is('input') ) { //we are dealing with an input
                var type = el.attr('type'); //will either be 'text', 'radio', or 'checkbox
            } else if ( el.is('select') ) { //we are dealing with a select
                //code here       
                if ( jsArray.hasOwnProperty(elementId) )
                {
                    reloadSelect(jsArray[elementId]['options'], elementId, jsArray[elementId]['value']);
                }
            } else if ( el.is('textarea') ) { //we are dealing with a select
                //code here
                //console.log('Id => ' + el.attr('id') + ' Type => select');
            } else { //we are dealing with a textarea
                //code here
                //console.log('Id => ' + el.attr('id') + ' Type => '+el);
            }
            if ( typeof jsArray[elementId] != 'undefined' && el.attr('type') == 'checkbox' )
            {
                if ( jsArray[elementId]['value'] )
                {
                    $("#" + elementId).attr('checked', true);
                }
            }
            if ( typeof jsArray[elementId] != 'undefined' && el.attr('type') != 'checkbox' )
            {
                $("#" + elementId).attr('name', jsArray[elementId]['name']);
                $("#" + elementId).val(jsArray[elementId]['value']);
            }
        });
        $.each($('input, select ,textarea', '#add_location_form'), function (k) {
            var el = $(this);
            var elementId = el.attr('id');
            if ( el.is('input') ) { //we are dealing with an input
                var type = el.attr('type'); //will either be 'text', 'radio', or 'checkbox
            } else if ( el.is('select') ) { //we are dealing with a select
                //code here       
                if ( jsArray.hasOwnProperty(elementId) )
                {
                    reloadSelect(jsArray[elementId]['options'], elementId);
                }
            }
            if ( typeof jsArray[elementId] != 'undefined' )
            {
                $("#" + elementId).attr('name', jsArray[elementId]['name']);
            }
            $('#location_country_id').val(99);
            $('#country_input_id').val($('#location_country_id').val());
        });

        $('#package_level').trigger('change');
        $('#attribute_sets_id').trigger('change');
        $('#locations').trigger('change');
        populateTables(jsArray);
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
                            data: function (validator, $field, value) {
                                return {
                                    table_name: 'business_units',
                                    field_name: 'name',
                                    name: value,
                                    manufacturer_id: $('#main_manufacturer_id').val(),
                                    pluck_id: 'business_unit_id',
                                    skip_decode: 1
                                };
                            },
                            delay: 2000, // Send Ajax request every 2 seconds
                            message: 'Business unit already exists.'
                        }
                    }
                }
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
                'attribute_set[manufacturer_name]': {
                    validators: {
                        callback: {
                            message: 'Please choose Manufacturer Name',
                            callback: function (value, validator, $field) {
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
                manufacturer_id: $('#manufacturer_id').val(),
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
                    $("#main_attribute_set_id").append('<option value="' + data['set_id'] + '">' + data['set_name'] + '</option>').val(data['set_id']);
                    $("#attribute_sets_id").append('<option value="' + data['set_id'] + '">' + data['set_name'] + '</option>');
                    $('.selectpicker#main_attribute_set_id').selectpicker('refresh');
                    $('#main_attribute_set_id').trigger('change');
                    $('.selectpicker#attribute_sets_id').selectpicker('refresh');
                    $('.close').trigger('click');
                    alert(data['message']);
                } else {
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
        $('#addAttributeSet').on('hide.bs.modal', function () {
            console.log('resetForm');
            $('#save_attribute_set').data('bootstrapValidator').resetForm();
            $('#save_attribute_set')[0].reset();
            $('#attribute_id').empty();
            $('#Selectattribute').empty();
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
                            callback: function (value, validator, $field) {
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
                            message: 'Please enter only alpha-numeric and underscore'
                        },
                        remote: {
                            message: 'Attribute Exists with this code.Please enter a new code',
                            url: '/product/checkAttrAvailability',
                            type: 'GET',
                            data: function (validator, $field, value) {
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
                            message: 'Name already exists.Please enter a new name',
                            url: '/product/checkAttributeAvailability',
                            type: 'GET',
                            data: function (validator, $field, value) {
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
            var responseData = ajaxCallPopup($('#addAttribute'));
            appendData();
            return false;
        }).validate({
            submitHandler: function (form) {
                return false;
            }
        });
        $('#add_attibute').on('hide.bs.modal', function () {
            console.log('resetForm');
            $('#addAttribute').data('bootstrapValidator').resetForm();
            $('#addAttribute')[0].reset();
            $('#attribute_manufacturer_name').val($("#main_manufacturer_id option:selected").text());
            $('[name="manufacturer_id"]').val($("#main_manufacturer_id").val());
            $('#attibute_form_attribute_set_id').val($("#main_attribute_set_id option:selected").text());
            $('#attribute_set_id').val($("#main_attribute_set_id option:selected").val());
        });

        $('#package_level').trigger('change');
        $('#attribute_sets_id').trigger('change');
        $('#locations').trigger('change');
        //loadComponentProducts(); 
        //$('#main_manufacturer_id').trigger('change');
        $('#product_type_id').trigger('change');
        $('#main_attribute_set_id').trigger('change');
        $('[name="attribute_set[manufacturer_name]"]').val($("#main_manufacturer_id option:selected").text());
        $('[name="attribute_set[manufacturer_id]"]').val($("#main_manufacturer_id").val());
        $('#attribute_manufacturer_name').val($("#main_manufacturer_id option:selected").text());
        $('[name="attribute[manufacturer_id]"]').val($("#main_manufacturer_id").val());
    });
    $('#main_manufacturer_id').change(function () {
        if ( $(this).val() != 0 )
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
        }
    });

    $('#add_attibute').on('show.bs.modal', function () {
        $('#attribute_manufacturer_name').val($("#main_manufacturer_id option:selected").text());
        $('[name="manufacturer_id"]').val($("#main_manufacturer_id").val());
        $('#attibute_form_attribute_set_id').val($("#main_attribute_set_id option:selected").text());
        $('#attribute_set_id').val($("#main_attribute_set_id option:selected").val());
    });

    function validateProductForm()
    {
        var fv = $('#product_creation').data('formValidation');
        fv.validate();
        var fields = ['product[title]', 'product[description]', 'product[category_id][]', 'files[]'];
        var temp = 1;
        $.each(fields, function (key, field) {
            if ( field == 'files[]' )
            {
                temp = validateImage();
            } else {
                $('#product_creation').data('formValidation').revalidateField(field);
                if ( $('[name="' + field + '"]').val() == 0 || $('[name="' + field + '"]').val() == '' )
                {
                    temp = 0;
                }
            }
        });
        if ( !temp )
        {
            //            collapsDiv('two');
            fv.validate();
        }
        console.log('temp ' + temp);
        $('input,textarea,select').filter('[required]').each(function () {
            var el = $(this);
            if ( el.is('input') && el.attr('type') == 'text' && $(this).val() == '' )
            {
                temp = 0;
                //                collapsDiv('four');
                fv.validate();
            } else if ( el.is('input') && el.attr('type') == 'checkbox' && $(this).prop('checked') == false ) {
                temp = 0;
                //                collapsDiv('four');
                fv.validate();
            } else if ( el.is('input') && el.attr('type') == 'radio' && $(this).prop('checked') == false ) {
                temp = 0;
                //                collapsDiv('four');
                fv.validate();
            } else if ( el.is('select') && $(this).val == '' ) {
                temp = 0;
                //                collapsDiv('four');
                fv.validate();
            } else if ( el.is('textarea') && $(this).val == '' ) {
                temp = 0;
                //                collapsDiv('four');
                fv.validate();
            }
            var value = $(this).attr('name');
            var isValidStep = fv.validateField(value).isValid();
            var isValidField = fv.revalidateField(value).isValid();
            if ( isValidField === false || isValidField === null ) {
                // Do not jump to the target tab
                temp = 0;
            } else {
                if ( temp )
                {
                    temp = 1;
                }
            }
        });
        return temp;   
    }

    function validateForm()
    {
        var temp = validateProductForm();
        console.log('temp ' + temp);
        if ( temp )
        {
            console.log('can Submit');
            $('#save_product').prop('disabled', false).trigger('click');
            $('#product_creation').submit();
        } else {
            //$('#product_creation').data('formValidation').validate();
        }
    }

    function locations()
    {
        var manufacturer_id = $('#main_manufacturer_id').val();
        $('[name="location[location_id][]"]').find('option').remove();
        var location_id = $('#location_id').val();
        var location_id_selected_value = $('#location_id_selected').val();
        var location_id_selected = location_id_selected_value.split(",");
        if ( manufacturer_id )
        {
            url = '/product/getelementdata';
            // Send the data using post
            var posting = $.post(url, {data_type: 'locations', data_value: manufacturer_id});
            // Put the results in a div
            posting.done(function (data) {
                $('#locations').empty();
                $('#parent_location_id').empty();
                responseData = JSON.parse(data);
                $.each(responseData, function (key, value) {
                    $('#locations').append('<option value="' + value['location_id'] + '">' + value['location_name'] + '</option>');
                    if ( location_id_selected.length > 0 )
                    {
                        var temp_id = value['location_id'].toString();
                        if ( $.inArray(temp_id, location_id_selected) !== -1 )
                        {
                            $('[name="location[location_id][]"]').append('<option value="' + value['location_id'] + '" selected>' + value['location_name'] + '</option>');
                        } else {
                            $('[name="location[location_id][]"]').append('<option value="' + value['location_id'] + '">' + value['location_name'] + '</option>');
                        }
                    } else {
                        if ( value['location_id'] == location_id_selected )
                        {
                            $('[name="location[location_id][]"]').append('<option value="' + value['location_id'] + '"  selected>' + value['location_name'] + '</option>');
                        } else {
                            $('[name="location[location_id][]"]').append('<option value="' + value['location_id'] + '">' + value['location_name'] + '</option>');
                        }
                    }

                });
                $('[name="location[location_id][]"]').selectpicker('refresh');
                $('#locations').selectpicker('refresh');
            });
        }
    }
    function loadLocationsType()
    {
        var manufacturer_id = $('#main_manufacturer_id').val();
        $('[name="location[location_id][]"]').find('option').remove();
        if ( manufacturer_id != 0 )
        {
            url = '/product/getelementdata';
            // Send the data using post
            var posting = $.post(url, {data_type: 'location_types', data_value: manufacturer_id});
            // Put the results in a div
            posting.done(function (data) {
                $('#add_location_type_id').empty();
                responseData = JSON.parse(data);
                //$('#add_location_type_id').append('<option value="0" selected="true">Please select... </option>');
                $.each(responseData, function (key, value) {
                    $('#add_location_type_id').append('<option value="' + value['location_type_id'] + '">' + value['location_type_name'] + '</option>');
                });
                $('#add_location_type_id').selectpicker('refresh');
            });
        }
    }

    function loadGroups()
    {
        var manufacturer_id = $('#main_manufacturer_id').val();
        $('#group_id').empty();
        if ( manufacturer_id != 0 )
        {
            url = '/product/getelementdata';
            // Send the data using post
            var posting = $.post(url, {data_type: 'groups', data_value: manufacturer_id});
            // Put the results in a div
            posting.done(function (data) {
                responseData = JSON.parse(data);
                //$('#add_location_type_id').append('<option value="0" selected="true">Please select... </option>');
                $.each(responseData, function (key, value) {
                    $('#group_id').append('<option value="' + value['group_id'] + '">' + value['name'] + '</option>');
                });
                $('#group_id').selectpicker('refresh');
            });
        }
    }

    $('#main_attribute_set_id').change(function (event) {
        attributeGroups();
        appendData();
    });

    function categories()
    {
        var manufacturer_id = $('#main_manufacturer_id').val();
        $('#product_category').find('option').remove();
        $('#attribute_set_category_id').find('option').remove();
        var selectedCategories = $('#category_id_selected').val();
        selectedCategories = selectedCategories.split(",");
        if ( manufacturer_id )
        {
            url = '/product/getelementdata';
            // Send the data using post
            var posting = $.post(url, {data_type: 'categories', data_value: manufacturer_id});
            // Put the results in a div
            posting.done(function (data) {
                responseData = JSON.parse(data);
                $.each(responseData, function (key, value) {
                    if ( selectedCategories.length > 1 )
                    {
                        var temp_id = value['category_id'].toString();
                        if ( $.inArray(temp_id, selectedCategories) !== -1 )
                        {
                            $("#product_category").append('<option value="' + value['category_id'] + '" selected>' + value['name'] + '</option>');
                        } else {
                            $("#product_category").append('<option value="' + value['category_id'] + '">' + value['name'] + '</option>');
                        }
                    } else {
                        if ( value['category_id'] == selectedCategories )
                        {
                            $("#product_category").append('<option value="' + value['category_id'] + '" selected>' + value['name'] + '</option>');
                        } else {
                            $("#product_category").append('<option value="' + value['category_id'] + '">' + value['name'] + '</option>');
                        }
                    }
                    $('[name="attribute_set[category_id]"]').append('<option value="' + value['category_id'] + '">' + value['name'] + '</option>');
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
        $('[name="attribute[attribute_set_id]"]').val($("#main_attribute_set_id option:selected").val());
        $('#attribute_set_id').val($("#main_attribute_set_id option:selected").val());
        var manufacturerId = $('#main_manufacturer_id').val();
        //mfg id populating
        $('#manufacturer_name').val($('#main_manufacturer_id option:selected').text());
        $('#manufacturer_id').val($('#main_manufacturer_id option:selected').val());
        //mfg id                  
        $('#attribute_group_id').find('option').remove();
        if ( manufacturerId )
        {
            url = '/product/getelementdata';
            // Send the data using post
            var posting = $.post(url, {data_type: 'attributeGroups', data_value: manufacturerId});
            // Put the results in a div
            posting.done(function (data) {
                responseData = JSON.parse(data);
                $("#attribute_group_id").append('<option value="0" selected="true">Please select... </option>');
                $.each(responseData, function (key, value) {
                    $("#attribute_group_id").append('<option value="' + value['attribute_group_id'] + '">' + value['name'] + '</option>');
                });
                $('.selectpicker#attribute_group_id').selectpicker('refresh');
            });
        }
    }

    $('#product_submit_button').click(function (event) {
        $('#product_submit_button').valid();
        $('#product_submit_button').attr("disabled", true);
        event.preventDefault();
        var imgField = $('[name="media[image][]"]');
        if ( imgField )
        {
            var imgLength = $('[name="media[image][]"]').length;
            if ( imgLength )
            {
                /*submitHandler: function (form) {
                 form.submit();
                 }*/
            }
        }
    });
    function businessUnits()
    {
        var manufacturer_id = $('#main_manufacturer_id').val();
        $('#business_unit_id').find('option').remove();
        if ( manufacturer_id )
        {
            url = '/product/getelementdata';
            // Send the data using post
            var posting = $.post(url, {data_type: 'businessUnits', data_value: manufacturer_id});
            // Put the results in a div
            posting.done(function (data) {
                responseData = JSON.parse(data);
                $("#locations_business_unit_id").empty();
                $("#business_unit_id").append('<option value="0" selected="true">Please select... </option>');
                $.each(responseData, function (key, value) {
                    $("#business_unit_id").append('<option value="' + value['business_unit_id'] + '">' + value['name'] + '</option>');
                    $("#locations_business_unit_id").append('<option value="' + value['business_unit_id'] + '">' + value['name'] + '</option>');
                });
                $('.selectpicker').selectpicker('refresh');
            });
        }
    }

    function attributeSets()
    {
        var manufacturerId = $('#main_manufacturer_id').val();
        $('#main_attribute_set_id').find('option').remove();
        if ( manufacturerId )
        {
            url = '/product/getelementdata';
            var attribute_set_id = $('[name="attribute_set_id"]').val();
            // Send the data using post
            var posting = $.post(url, {data_type: 'attributeSets', data_value: manufacturerId});
            // Put the results in a div
            posting.done(function (data) {
                responseData = JSON.parse(data);
                $("#main_attribute_set_id").append('<option value="0" selected="true">Please select... </option>');
                $("#attribute_sets_id").append('<option value="0" selected="true">Please select... </option>');
                $.each(responseData, function (key, value) {
                    if ( attribute_set_id == value['attribute_set_id'] )
                    {
                        $("#main_attribute_set_id").append('<option value="' + value['attribute_set_id'] + '" selected>' + value['attribute_set_name'] + '</option>');
                    } else {
                        $("#main_attribute_set_id").append('<option value="' + value['attribute_set_id'] + '">' + value['attribute_set_name'] + '</option>');
                    }
                });
                $('.selectpicker#main_attribute_set_id').selectpicker('refresh');
                $("#main_attribute_set_id").trigger('change');
            });
        }
    }


    function preview(productId)
    {
        if(validateProductForm())
        {
            console.log(productId);
            $("#addEntity").click();

             $.get('/products/preview/?product_id='+productId,function(response){ 
                    $("#basicvalCode").html('Add Entity');
                    console.log(response);
                    $("#entitiesDiv").html(response);
                    
                    
                });
        }
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



    $("#add_packages").click(function () {
        var package_level = $('#package_level option:selected').text();
        if ( package_level == '' )
        {
            alert('Please select package.');
            return false;
        }
        var packageElements = new Array();
        $('[id="package_name"]').each(function () {
            packageElements.push($(this).text());
        });
        if ( packageElements.length > 0 && $.inArray(package_level, packageElements) >= 0 )
        {
            alert('This package already added.');
        } else {
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
                    + '</td><td>' + length + '</td><td>' + breadth + '</td><td>' + height + '</td><td>' + is_shipper_pack + '</td><td>' + is_pallet + '</td><td><a href="javascript:void(0);" class="check-toggler" id="remCF"><span class="badge bg-red"><i class="fa fa-trash-o"></i></span></a><input type="hidden" name="package[package_details][]" value=' + "'" + JSON.stringify(jsonArg1) + "'" + ' /></td></tr>');
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

        if ( attribute_set_id == 0 || attribute_set_id == '' )
        {
            alert('Please select attribtue set.');
        } else if ( location_id == 0 || location_id == '' )
        {
            alert('Please select locations.');
        } else {
            var attributeSetElements = new Array();
            $('[id="attribute_set_name"]').each(function () {
                attributeSetElements.push($(this).text() + '##' + $(this).next('td#location_name').text());
            });
            var temp;
            temp = attribute_set_name + '##' + location_name;
            if ( attributeSetElements.length > 0 && $.inArray(temp, attributeSetElements) >= 0 )
            {
                alert('This element already added.');
            } else {
                var jsonArg2 = new Object();
                jsonArg2.attribute_set_id = attribute_set_id;
                jsonArg2.location_id = location_id;

                var hiddenJsonData1 = new Array();
                hiddenJsonData1.push(jsonArg2);

                $("#attribute_location_data").append('<tr><td scope="row" id="attribute_set_name">' + attribute_set_name + '</td><td id="location_name">' + location_name
                        + '</td><td><a href="javascript:void(0);" class="check-toggler" id="remCF1"><span class="badge bg-red"><i class="fa fa-trash-o"></i></span></a><input type="hidden" name="product_attribute_sets[attribute_details][]" value=' + "'" + JSON.stringify(jsonArg2) + "'" + ' /></td></tr>');
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
        if ( slab_range == '' )
        {
            alert('Please enter Slab Upper Limit');
            $('[name="slab_rate[end_range]"]').focus();
            temp = 0;
            return false;
        } else {
            $("#slab_rate_data td.slab_range").each(function () {
                var previousRange = $(this).text();
                if ( previousRange == slab_range )
                {
                    $('[name="slab_rate[end_range]"]').focus();
                    alert('This range is already provided this price slab.');
                    temp = 0;
                    return false;
                }
            });
        }
        if ( slab_price == '' )
        {
            alert('Please enter price');
            $('[name="slab_rate[price]"]').focus();
            temp = 0;
            return false;
        }
        if ( temp )
        {
            var jsonArg2 = new Object();
            jsonArg2.end_range = slab_range;
            jsonArg2.price = slab_price;
            var slabData = new Array();
            slabData.push(jsonArg2);
            $("#slab_rate_data").append('<tr><td class="slab_range">' + slab_range + '</td><td>' + slab_price
                    + '</td><td><a href="javascript:void(0);" class="check-toggler" id="remSlabRates"><span class="badge bg-red"><i class="fa fa-trash-o"></i></span></a><input type="hidden" name="slab_rate[rates][]" value=' + "'" + JSON.stringify(jsonArg2) + "'" + ' /></td></tr>');
        }
    });

    $("#slab_rate_data").on('click', '#remSlabRates', function () {
        $(this).parent().parent().remove();
    });

    $('#attribute_set_id').change(function () {
        appendData();
    });

    function appendData()
    {
        $('#dynamic').empty();
        var attribute_set_id = $('#main_attribute_set_id').find("option:selected").val();
        var product_id = $('[name="product_id"]').val();
        url = '/product/getattributelist';
        // Send the data using post
        if ( attribute_set_id == '' )
        {
            return;
        }
        var requiredFields = [];
        var posting = $.post(url, {attribute_set_id: attribute_set_id, product_id: product_id});
        // Put the results in a div
        posting.done(function (data) {
            responseData = JSON.parse(data);
            if ( responseData == 'No Attribute group Id' )
            {
                return;
            }
            var tempCloneData;
            var countRows = 1;
            var len = responseData.length;
            $.each(responseData, function (key, attributes) {
                if ( typeof attributes.attribute_id != 'undefined' ) {
                    var attribute_id = attributes.attribute_id;
                    var attribute_code = attributes.attribute_code;
                    var attribute_name = attributes.name;
                    var input_type = attributes.input_type;
                    var is_dynamic = attributes.is_dynamic;
                    var is_required = attributes.is_required;
                    var options = attributes.options;
                    var value = attributes.value;
                    var attribute_type = attributes.attribute_type;
                    var outputElement = '';
                    console.log('attribute_code => ' + attribute_code);
                    console.log('is_required => ' + is_required);
                    switch (input_type)
                    {
                        case 'input':
                            var $template1 = $('#inputTemplate'),
                                    $clone1 = $template1.clone().attr('id', 'addNext_' + key);
                            $clone1.find('label').text(attribute_name);
                            $clone1.find('label').attr('for', attribute_code);
                            $clone1.find('input').attr('id', attribute_code);
                            $clone1.find('input').attr('name', 'attributes[' + attribute_code + ']');
                            $clone1.find('input').val(value);
                            if ( 1 == attribute_type && is_required == 1 )
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
                                    $clone1 = $template1.clone().attr('id', 'addNext_' + key);
                            $clone1.find('label').text(attribute_name);
                            $clone1.find('label').attr('for', attribute_code);
                            $clone1.find('input').attr('id', attribute_code);
                            if ( 1 == attribute_type && is_required == 1 )
                            {
                                $clone1.find('input').prop('required', true);
                                //$('#product_creation').formValidation('addField', $clone1);
                            }
                            $clone1.find('input').attr('name', 'attributes[' + attribute_code + ']');
                            //$('#product_creation').formValidation('addField', 'attributes[' + attribute_code + ']');
                            $clone1.find('input').val(value);
                            //$clone1.insertBefore(dynamic);
                            outputElement = $clone1;
                            /*outputElement = '<div class="row"><div class="form-group col-sm-6"><label for="exampleInputEmail">'+attribute_name+
                             '</label><div class="input-group input-group-sm"><input class="form-control" type="text" id="'+ attribute_code +'" name="attributes[' + attribute_code +']" value="" /></div></div></div>';*/
                            break;
                        case 'checkbox':
                            var $template2 = $('#checkboxTemplate'),
                                    $clone2 = $template2.clone().attr('id', 'addNext_' + key);
                            $clone2.find('label').text(attribute_name);
                            $clone2.find('label').attr('for', attribute_code);
                            $clone2.find('input').attr('id', attribute_code);
                            $clone2.find('input').attr('name', 'attributes[' + attribute_code + ']');
                            if ( 1 == attribute_type && is_required == 1 )
                            {
                                $clone2.find('input').prop('required', true);
                            }
                            if ( value )
                            {
                                $clone2.find('input').prop('checked', true);
                            }
                            //$clone2.insertBefore(dynamic);
                            outputElement = $clone2;
                            /*outputElement = '<div class="row"><div class="form-group col-sm-6"><label for="exampleInputEmail">'+attribute_name+
                             '</label><div class="input-group input-group-sm"><input type="checkbox" id="'+ attribute_code +'" name="attributes[' + attribute_code +']" value="" /></div></div></div>';*/
                            break;
                        case 'radio':
                            var $template3 = $('#radioTemplate'),
                                    $clone3 = $template3.clone().attr('id', 'addNext_' + key);
                            $clone3.find('label').text(attribute_name);
                            $clone3.find('label').attr('for', attribute_code);
                            $clone3.find('input').attr('id', attribute_code);
                            $clone3.find('input').attr('name', 'attributes[' + attribute_code + ']');
                            if ( 1 == attribute_type && is_required == 1 )
                            {
                                $clone3.find('input').prop('required', true);
                            }
                            if ( value )
                            {
                                $clone2.find('input').prop('checked', true);
                            }
                            outputElement = $clone3;
                            /*outputElement = '<div class="row"><div class="form-group col-sm-6"><label for="exampleInputEmail">'+attribute_name+
                             '</label><div class="input-group input-group-sm"><input type="radio" id="'+ attribute_code +'" name="attributes[' + attribute_code +']" value="" /></div></div></div>';*/
                            break;
                        case 'select':
                            var $template4 = $('#selectTemplate');
                            $clone4 = $template4.clone().attr('id', 'addNext_' + key);
                            $clone4.find('label').text(attribute_name);
                            $clone4.find('label').attr('for', attribute_code);
                            $clone4.find('select').attr('id', attribute_code);
                            $clone4.find('select').attr('name', 'attributes[' + attribute_code + ']');
                            if ( 1 == attribute_type && is_required == 1 )
                            {
                                $clone4.find('select').prop('required', true);
                            }
                            if ( value )
                            {
                                $clone2.find('select').val(value);
                            }
                            outputElement = $clone4;
                            break;
                        case 'textarea':
                            var $template5 = $('#textareaTemplate'),
                                    $clone5 = $template5.clone().attr('id', 'addNext_' + key);
                            $clone5.find('label').text(attribute_name);
                            $clone5.find('label').attr('for', attribute_code);
                            $clone5.find('textarea').attr('id', attribute_code);
                            $clone5.find('textarea').attr('name', 'attributes[' + attribute_code + ']');
                            if ( 1 == attribute_type && is_required == 1 )
                            {
                                $clone5.find('textarea').prop('required', true);
                            }
                            if ( value )
                            {
                                $clone2.find('textarea').text(value);
                            }
                            //$clone5.insertBefore(dynamic);
                            outputElement = $clone5;
                            /*outputElement = '<textarea class="form-control" type="text" name="attributes[' + attribute_code +']" id="'+ attribute_code +'" row="4"></textarea>';*/
                            break;
                        case 'file':
                            var $template6 = $('#fileTemplate'),
                                    $clone6 = $template6.clone().attr('id', 'addNext_' + key);
                            $clone6.find('label').text(attribute_name);
                            $clone6.find('label').attr('for', attribute_code);
                            $clone6.find('input').attr('id', attribute_code);
                            $clone6.find('input').attr('name', 'attributes[' + attribute_code + ']');
                            if ( 1 == attribute_type && is_required == 1 )
                            {
                                $clone5.find('input').prop('required', true);
                            }
                            //$clone6.insertBefore(dynamic);
                            outputElement = $clone6;
                            /*outputElement = '<textarea class="form-control" type="text" name="attributes[' + attribute_code +']" id="'+ attribute_code +'" row="4"></textarea>';*/
                            break;
                        case 'multiselect':
                            var $template7 = $('#multiselectTemplate'),
                                    $clone7 = $template7.clone().attr('id', 'addNext_' + key);
                            $clone7.find('label').text(attribute_name);
                            $clone7.find('label').attr('for', attribute_code);
                            $clone7.find('select').attr('id', attribute_code);
                            $clone7.find('select').attr('name', 'attributes[' + attribute_code + ']');
                            if ( 1 == attribute_type && is_required == 1 )
                            {
                                $clone5.find('select').prop('required', true);
                            }
                            if ( value )
                            {
                                $clone2.find('select').val(value);
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
                            if ( value )
                            {
                                $clone2.find('input').val(value);
                            }
                            //$clone1.insertBefore(dynamic);
                            outputElement = $clone1;
                            /*outputElement = '<div class="row"><div class="form-group col-sm-6"><label for="exampleInputEmail">'+attribute_name+
                             '</label><div class="input-group input-group-sm"><input class="form-control" type="text" id="'+ attribute_code +'" name="attributes[' + attribute_code +']" value="" /></div></div></div>';*/
                            break;
                        default:
                            break;
                    }
                    if ( 1 == attribute_type && is_required )
                    {
                        requiredFields[key] = 'attributes[' + attribute_code + ']';
                    }
                }
                var rowHeader = '<div class="row" id="addNext_' + key + '">';
                var rowFooter = '</div>';
                if ( countRows == 2 )
                {
                    countRows = 1;
                    tempFields = rowHeader + tempCloneData.html() + outputElement.html() + rowFooter;
                    $('#dynamic').append(tempFields);
                    tempCloneData = [];
                } else {
                    if ( key == len - 1 ) {
                        tempFields = rowHeader + outputElement.html() + rowFooter;
                        $('#dynamic').append(tempFields);
                        tempCloneData = [];
                    } else {
                        tempCloneData = outputElement;
                        countRows++;
                    }
                }
            });
            $.each(requiredFields, function (fieldKey, fieldName) {
                $('#product_creation').formValidation('addField', fieldName);
            });
        });
    }
    $('#business_unit_form').submit(function (event) {
        $('#business_unit_form').formValidation();
        event.preventDefault();
        if ( $('#business_unit_form').data('formValidation').isValidField('business_unit[name]') )
        {
            $('#business_unit_submit_button').attr("disabled", true);
            $form = $(this);
            url = $form.attr('action');
            // Send the data using post
            var posting = $.post(url, {'name': $('[name="business_unit[name]"]').val(), 'manufacturer_id': $('[name="business_unit[manufacturer_id]"]').val(), 'description': $('[name="business_unit[description]"]').val()});
            // Put the results in a div
            posting.done(function (data) {
                if ( data )
                {
                    responseData = JSON.parse(data);
                    $.each(responseData, function (key, value) {
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
                } else {
                    alert('Unable to added business unit.');
                    $('#business_unit_submit_button').attr("disabled", false);
                }
            });
        }
    });

    $('#attibute_form').submit(function (event) {
        $('#attibute_form').formValidation();
        event.preventDefault();
        if ( $('#attibute_form').data('formValidation').isValid() )
        {
            event.preventDefault();
            $('#attribute_submit_button').attr("disabled", true);
            $form = $(this);
            url = $form.attr('action');
            var attributeFields = new Array();
            // Send the data using post
            var attributeFields = {attribute_group_id: $('[name="attribute[attribute_group_id]"]').val(),
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
                validation: $('[name="attribute[validation]"]').val()};
            var posting = $.post(url, {attributeFields});
            // Put the results in a div
            posting.done(function (data) {
                console.log(data);
                if ( data )
                {
                    appendData();
                    alert('Sucessfully added attribute set.');
                    $('.close').trigger('click');
                    $('#attribute_submit_button').attr("disabled", false);
                } else {
                    alert('Unable to added attribute set.');
                    $('#attribute_submit_button').attr("disabled", false);
                }
            });
        }
    });
    $('#attribute_set_form').submit(function (event) {
        $('#attribute_set_form').formValidation();
        event.preventDefault();
        if ( $('#attribute_set_form').data('formValidation').isValid() )
        {
            $('#attribute_set_button').attr("disabled", true);
            event.preventDefault();
            $form = $(this);
            url = $form.attr('action');
            var attributeSetFields = {attribute_set_name: $('[name="attribute_set[attribute_set_name]"]').val(), category_id: $('#category_id').val(), inherit_from: $('[name="attribute_set[inherit_from]"]:checked').length, is_active: $('#is_active').val(), manufacturer_id: $('[name="attribute_set[manufacturer_id]"]').val()};
            // Send the data using post
            var posting = $.post(url, {attribute_set: attributeSetFields});
            // Put the results in a div
            posting.done(function (data) {
                if ( data )
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
                } else {
                    alert('Unable to added attribute set.');
                    $('#attribute_set_button').attr("disabled", false);
                }
            });
        }
    });
    //added for AttributeSet
    $('[data-target="#addAttributeSet"]').click(function () {
        var manufacturer_id = $('#main_manufacturer_id').val();
        //mfg id populating
        $('#manufacturer_name').val($('#main_manufacturer_id option:selected').text());
        $('#manufacturer_id').val($('#main_manufacturer_id option:selected').val());
        //mfg id 
        var url = '/product/attributes';
        // Send the data using post
        var posting = $.get(url, {manufacturer_id: manufacturer_id});
        // Put the results in a div
        posting.done(function (data) {
            var result = JSON.parse(data);
            $.each(result, function (key, value) {
                /*$('#Selectattribute').append('<option value="' + value['attribute_id'] + '">' + value['name'] + '</option>');  */
                /*$('#Selectattribute').append('<li value="' + value['attribute_id'] + '">' + value['name'] + '</li>');*/
                $('#Selectattribute').append('<div class="fc-field" value="' + value['attribute_id'] + '">' + value['name'] + '</div>');
            });
        });
        //getAttributes();
    });
    //added for AttributeSet
    //ADDED FOR ATTRIBUTE
    $('[data-target="#add_attibute"]').click(function () {
        $('#attibute_form_attribute_set_id').val($('#main_attribute_set_id option:selected').text());
        $('#attribute_set_id').val($('#main_attribute_set_id option:selected').val());
        $('[name="manufacturer_id"]').val($('#main_manufacturer_id option:selected').val());
    });
    //ADDED FOR ATTRIBUTE

    function reloadSelect(responseData, elementId, dataValue)
    {
        $("#" + elementId).empty();
        if ( !$.isEmptyObject(responseData) )
        {
            $.each(responseData, function (key, value) {
                isKey = 0;
                if ( typeof dataValue == 'object' )
                {
                    if ( $.inArray(key, dataValue) !== -1 )
                    {
                        isKey = 1;
                    }
                } else if ( dataValue == key ) {
                    isKey = 1;
                }
                if ( isKey )
                {
                    $("#" + elementId).append('<option value="' + key + '" selected>' + value + '</option>');
                } else {
                    $("#" + elementId).append('<option value="' + key + '">' + value + '</option>');
                }
            });
            $('.selectpicker#' + elementId).selectpicker('refresh');
        }
    }

    function collapsDiv(id, validateThis)
    {
        var temp = true;
        var fields = [];
        var fv = $('#product_creation').data('formValidation');
        console.log(fv);
        console.log(id);
        if (id == 'two')
        {
            fields = ['product[title]', 'product[description]', 'product[category_id][]', 'files[]'];
            //temp = validateTab(fields);
            $.each(fields, function (element, value) {
                console.log(value);
                if ( value == 'files[]' )
                {
                    var isValidField = validateImage();
                } else {
                    var isValidStep = fv.validateField(value).isValid();
                    var isValidField = fv.isValidField(value);
                }
                console.log('isValidField -> '+isValidField);
                if ( isValidField === false || isValidField === null ) {
                    // Do not jump to the target tab
                    temp = false;
                } else {
                    if ( temp )
                    {
                        temp = true;
                    }
                }
            });
        }
        console.log(temp);
        if ( validateThis != 0 && (id == 'five') )
        {
            $('input,textarea,select').filter('[required]').each(function () {
                //console.log($(this).attr('name'));
                var value = $(this).attr('name');
                var isValidStep = fv.validateField(value).isValid();
                var isValidField = fv.isValidField(value);
                if ( isValidField === false || isValidField === null ) {
                    // Do not jump to the target tab
                    temp = false;
                } else {
                    if ( temp )
                    {
                        temp = true;
                    }
                }
            });
        }
        if ( temp )
        {
            $('#' + id).trigger('click');
        }
    }

    function validateImage()
    {
        var fileName = $('#files').children().find('input');
        if ( typeof fileName.val() != 'undefined' )
        {
            $('#upload_field').children('div.col-sm-10').children('span').children('i.form-control-feedback').removeClass('glyphicon-remove').addClass('glyphicon-ok');
            $('#upload_field').removeClass('has-error');
            $('#upload_field').children('div.col-sm-10').children('small').hide();
            return true;
        } else {
            $('#upload_field').children('div.col-sm-10').children('span').children('i.form-control-feedback').removeClass('glyphicon-ok').addClass('glyphicon-remove');
            $('#upload_field').removeClass('has-success');
            $('#upload_field').addClass('has-error');
            $('#upload_field').children('div.col-sm-10').children('small').show();
            return false;
        }
    }

    $('#is_pallet').click(function () {
        if ( $('#is_pallet').prop('checked') == true )
        {
            $('#pallet_stack_height').prop('readonly', false);
        } else {
            $('#pallet_stack_height').val('');
            $('#pallet_stack_height').prop('readonly', true);
        }
    });

    function populateTables(jsArray)
    {
        var package_data;
        var pallet_data;
        var media_data;
        var product_attributesets;
        var product_locations;

        if ( typeof jsArray['package_data']['value'] != 'undefined' )
        {
            package_data = jsArray['package_data']['value'];
        }
        if ( typeof jsArray['pallet_data']['value'] != 'undefined' )
        {
            pallet_data = jsArray['pallet_data']['value'];
        }
        if ( typeof jsArray['media_data']['value'] != 'undefined' )
        {
            media_data = jsArray['media_data']['value'];
        }
        if ( typeof jsArray['product_attributesets']['value'] != 'undefined' )
        {
            product_attributesets = jsArray['product_attributesets']['value'];
        }
        if ( typeof jsArray['product_attributesets']['value'] != 'undefined' )
        {
            product_locations = jsArray['product_locations']['value'];
        }
        $.each(package_data, function (key, value) {
            var jsonArg11 = new Object();
            jsonArg11.id = value['id'];
            jsonArg11.level = value['level'];
            jsonArg11.quantity = value['quantity'];
            jsonArg11.length = value['length'];
            jsonArg11.width = value['width'];
            jsonArg11.height = value['height'];
            jsonArg11.is_shipper_pack = value['is_shipper_pack'];
            jsonArg11.is_pallet = value['is_pallet'];
            var hiddenJsonData = new Array();
            hiddenJsonData.push(jsonArg11);

            $("#package_data").append('<tr><td scope="row" id="package_name">' + value['name'] + '</td><td>' + value['quantity']
                    + '</td><td>' + value['length'] + '</td><td>' + value['width'] + '</td><td>' + value['height'] + '</td><td>' + (value['is_shipper_pack'] ? 'true' : 'false')
                    + '</td><td>' + (value['is_pallet'] ? 'true' : 'false') +
                    '</td><td><a href="javascript:void(0);" class="check-toggler" id="remCF"><span class="badge bg-red"><i class="fa fa-trash-o"></i></span></a><input type="hidden" name="package[package_details][]" \n\
                value=' + "'" + JSON.stringify(jsonArg11) + "'" + ' /></td></tr>');
        });

        $.each(product_attributesets, function (key, value) {
            var jsonArg22 = new Object();
            jsonArg22.id = value['id'];
            jsonArg22.attribute_set_id = value['attribute_set_id'];
            jsonArg22.location_id = value['location_id'];

            $("#attribute_location_data").append('<tr><td scope="row" id="attribute_set_name">' + value['attribute_set_name'] + '</td><td id="location_name">' + value['location_name']
                    + '</td><td><a href="javascript:void(0);" class="check-toggler" id="remCF1"><span class="badge bg-red"><i class="fa fa-trash-o"></i></span></a><input type="hidden" name="product_attribute_sets[attribute_details][]" value=' + "'" + JSON.stringify(jsonArg22) + "'" + ' /></td></tr>');
        });
        if ( !jQuery.isEmptyObject(product_locations) )
        {
            $('#product_locations').val(product_locations.split(",")).selectpicker("refresh");
        }
        $.each(media_data, function (key, value) {
            $('#fileupload').attr('required', false);
            var temp;
            var url = value['url'];
            var product_media_id = value['product_media_id'];
            var sort_order = value['sort_order'];
            temp = '<tr class="template-upload"><td class="preview">';
            temp = temp + '<a target="_blank" href="' + url + '"></a><img src="/uploads/products/' + url + '" width="100" height="100" />';
            if ( sort_order )
            {
                temp = temp + '<br/><input type="radio" id="is_default" value="' + product_media_id + '" name="media[is_default]" checked />';
            } else {
                temp = temp + '<br/><input type="radio" id="is_default" value="' + product_media_id + '" name="media[is_default]" />';
            }
            temp = temp + '<label for="is_default">Is default</label></span></td></tr>';
            $("#files").append(temp);
        });
    }

    $('#product_type_id').change(function () {
        var product_type_id = $(this).val();
        if ( 8003 == product_type_id )
        {
            $('.component').show();
        } else {
            $('.component').hide();
        }
    });

    function loadComponentProducts()
    {
        $('#component_product_default_list').val('');
        $('#component_product_selected_list').val('');
        var manufacturerId = $('#main_manufacturer_id').val();
        var productId = $('[name="product_id"]').val();
        if ( manufacturerId > 0 )
        {
            url = '/product/getelementdata';
            // Send the data using post
            var posting = $.post(url, {data_type: 'component_products', data_value: manufacturerId, product_id: productId});

            // Put the results in a div
            posting.done(function (data) {
                if ( data )
                {
                    var temp = '';
                    var component_ids;
                    $.each(data, function (key, value) {
                        if ( 'component_ids' != key )
                        {
                            temp = temp + key + ',';
                            $('#component_product_list').append('<div class="fc-field" value="' + key + '">' + value + ' <input type="hidden" value="' + key + '" /></div>');
                        } else {
                            component_ids = value;
                        }
                    });
                    if ( temp.slice(-1) == ',' ) {
                        temp = temp.slice(0, -1);
                    }
                    $('#component_product_default_list').val(temp);
                    if ( component_ids )
                    {
                        var temp2 = '';
                        $.each(component_ids, function (key, value) {
                            temp2 = temp2 + key + ',';
                            $('#component_product_selected').append('<div class="fc-field" value="' + key + '">' + value + ' <input type="hidden" value="' + key + '" /></div>');
                        });
                        if ( temp2.slice(-1) == ',' ) {
                            temp2 = temp2.slice(0, -1);
                        }
                        $('#component_product_selected_list').val(temp2);
                    }
                }
            });
        } else {
            $('#component_product_list').empty();
        }
    }

    $(document).ready(function () {
        var $sourceFields = $("#component_product_list");
        var $destinationFields = $("#component_product_selected");
        //var $chooser = $("#fieldChooser").fieldChooser(component_product_list, component_product_selected);
        $(".fc-destination-fields").droppable({
            over: function (event, ui) {
                pushData(ui.draggable.find('input').val());
            },
            out: function (event, ui) {
                popData(ui.draggable.find('input').val());
            }
        });
    });

    function pushData(inputData)
    {
        var result = '';
        var selectedList = $('#component_product_selected_list').val();
        if ( selectedList == '' )
        {
            result = inputData;
        } else {
            result = selectedList + ',' + inputData;
        }
        $('#component_product_selected_list').val(result);
    }

    function popData(inputData)
    {
        var result = '';
        var selectedList = $('#component_product_selected_list').val();
        if ( selectedList == '' )
        {
            result = inputData;
        } else {
            result = jQuery.grep(selectedList.split(","), function (value) {
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
        $('#' + stateId).find('option').remove();
        $.get('/customer/getZones?countryId=' + countryId, function (data) {
            var result = $.parseJSON(data);
            $('#' + stateId).find('option').remove().end();
            $.each(result, function (k, v) {
                if ( isKey )
                {
                    $('#' + stateId).append($("<option>").attr('value', k).text(v));
                } else {
                    $('#' + stateId).append($("<option>").attr('value', v).text(v));
                }

            });
            if ( isMultiselect )
            {
                $('#' + stateId).multiselect({
                    enableFiltering: true
                }).multiselect('rebuild');
                $('#' + stateId).multiselect('rebuild');
            } else {
                $('#' + stateId).selectpicker('refresh');
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
                        delay: 2000, // Send Ajax request every 2 seconds
                        message: 'Name already exists.'
                    }, regexp: {
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
                        callback: function (value, validator, $field) {
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
            longitude: {
                validators: {
                    between: {
                        min: -180,
                        max: 180,
                        message: 'The longitude must be between -180.0 and 180.0'
                    }
                }
            },
            latitude: {
                validators: {
                    between: {
                        min: -90,
                        max: 90,
                        message: 'The latitude must be between -90.0 and 90.0'
                    }

                }
            }
        }
    }).on('success.form.bv', function (event) {
        var $form = $('#add_location_form');
        var formData = prePostData($form.serialize());
        $.post($form.attr('action'), formData, function (data) {
            if ( data )
            {
                alert(data.message);
                if ( data.status === true ) {
                    console.log(data.location_id);
                    $('#product_locations').append('<option value="' + data.location_id + '" selected>' + $form.find('[name="location_name"]').val() + '</option>');
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
        return data;
    }
</script>    

@stop
