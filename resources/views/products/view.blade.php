@extends('layouts.default')
@extends('layouts.header')

@section('content')
<h1>{{ $data['title'] }}</h1>
<br />
{{ Form::open(array('url' => 'save', 'method' => 'POST', 'files'=>true)) }}

{{ Form::label($data['manufacturer_data']['field'], $data['manufacturer_data']['title']) }}
{{ Form::select($data['manufacturer_data']['field'], $data['manufacturer_data']['options']) }}

<div class="tabbable" id="myTabs">
    <ul class="nav nav-pills">
        <?php $count = 1; ?>
        @foreach($data['tabs'] as $id => $tab)
        @if($count)
        <li class="active"><a href="#{{ $id }}" data-toggle="tab">{{ $tab }}</a></li>
        @else
        <li><a href="#{{ $id }}" data-toggle="tab">{{ $tab }}</a></li>
        @endif
        <?php $count = 0; ?>
        @endforeach
    </ul>
    <div class="tab-content">        
        <div class="tab-pane active" id="general">
            <div class="form-group">
                <label class="col-xs-3 control-label">Product Category</label>
                <div class="col-xs-5">

                    <select name="product[category_id]" class="selectpicker">
                        <option value=""></option>
                        @foreach($data['fields']['general']['product_category'] as $cat_id => $category)
                        <option value="{{ $cat_id }}">{{$category}}</option>
                        @endforeach
                    </select>
                </div>
            </div>  
            <div class="form-group">
                <label class="col-xs-3 control-label">Product Sub-Category</label>
                <div class="col-xs-5">
                    <select name="sub_category_id">
                        <option value=""></option>
                        @foreach($data['fields']['general']['product_sub_category'][1] as $sub_cat_id => $sub_category)
                        <option value="{{ $sub_cat_id }}">{{$sub_category}}</option>
                        @endforeach
                    </select>
                </div>
            </div> 
            <div class="form-group">
                <label class="col-xs-3 control-label">Business Unit</label>
                <div class="col-xs-5">
                    <select name="product[business_unit_id]">
                        <option value=""></option>
                        @foreach($data['fields']['general']['business_unit_id'] as $b_id => $bunitData)
                        <option value="{{ $bunitData->business_unit_id }}">{{ $bunitData->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div> 
            <div class="form-group">
                <label class="col-xs-3 control-label">Product Class</label>
                <div class="col-xs-5">
                    <input type="text" name="product_class" id="product_class" />
                </div>
            </div> 
            <div class="form-group">
                <label class="col-xs-3 control-label">Product Type</label>
                <div class="col-xs-5">
                    <input type="text" name="product[product_type_id]" id="product_type_id" />
                </div>
            </div> 
            <div class="form-group">
                <label class="col-xs-3 control-label">Product Title</label>
                <div class="col-xs-5">
                    <input type="text" name="product[title]" id="title" />
                </div>
            </div> 
            <div class="form-group">
                <label class="col-xs-3 control-label">Product Description</label>
                <div class="col-xs-5">
                    <input type="text" name="product[description]" id="description" />
                </div>
            </div> 
            <div class="form-group">
                <label class="col-xs-3 control-label">Product Model Name</label>
                <div class="col-xs-5">
                    <input type="text" name="product_model_name" id="product_model_name" />
                </div>
            </div>
            <div class="form-group">
                <label class="col-xs-3 control-label">Product Color</label>
                <div class="col-xs-5">
                    <input type="text" name="product_color" id="product_color" />
                </div>
            </div>
            <div class="form-group">
                <label class="col-xs-3 control-label">Product Brand Name</label>
                <div class="col-xs-5">
                    <input type="text" name="product_brand_name" id="product_brand_name" />
                </div>
            </div>
            <div class="form-group">
                <label class="col-xs-3 control-label">Meta Tag Title</label>
                <div class="col-xs-5">
                    <input type="text" name="meta_tag_title" id="meta_tag_title" />
                </div>
            </div>
            <div class="form-group">
                <label class="col-xs-3 control-label">Meta Tag Description</label>
                <div class="col-xs-5">
                    <textarea name="meta_tag_description" id="meta_tag_description" rows="5" cols="10" ></textarea>
                </div>
            </div>
            <div class="form-group">
                <label class="col-xs-3 control-label">Product Tags</label>
                <div class="col-xs-5">
                    <input type="text" name="product[meta_tags]" id="meta_tags" />
                </div>
            </div>
        </div>

        <div class="tab-pane" id="packages">
            <div class="form-group">
                <label class="col-xs-3 control-label">Product Capacity</label>
                <div class="col-xs-5">
                    <input type="text" name="product_capacity" id="product_capacity" />
                </div>
            </div>
            <div class="form-group">
                <label class="col-xs-3 control-label">Product Weight</label>
                <div class="col-xs-5">
                    <input type="text" name="product[weight]" id="weight" />
                </div>
            </div>
            <div class="form-group">
                <label class="col-xs-3 control-label">Product Level</label>
                <div class="col-xs-5">
                    <input type="text" name="packages[level]" id="product_capacity" />
                </div>
            </div>
            <div class="form-group">
                <label class="col-xs-3 control-label">Capacity</label>
                <div class="col-xs-5">
                    <input type="text" name="packages[quantity]" id="package_quantity" />
                </div>
            </div>
            <div class="form-group">
                <label class="col-xs-3 control-label">Length</label>
                <div class="col-xs-5">
                    <input type="text" name="packages[length]" id="package_length" />
                </div>
            </div>
            <div class="form-group">
                <label class="col-xs-3 control-label">Breadth</label>
                <div class="col-xs-5">
                    <input type="text" name="packages[width]" id="package_width" />
                </div>
            </div>
            <div class="form-group">
                <label class="col-xs-3 control-label">Height</label>
                <div class="col-xs-5">
                    <input type="text" name="packages[height]" id="package_height" />
                </div>
            </div>
            <div class="form-group">
                <label class="col-xs-3 control-label">Weight</label>
                <div class="col-xs-5">
                    <input type="text" name="packages[weight]" id="package_weight" />
                </div>
            </div>
            <div class="form-group">
                <label class="col-xs-3 control-label">Weight Class</label>
                <div class="col-xs-5">
                    <input type="text" name="packages[weight_class_id]" id="package_weight_class_id" />
                </div>
            </div>
            <div class="form-group">
                <label class="col-xs-3 control-label">Is Shipper Pack</label>
                <div class="col-xs-5">
                    <input type="checkbox" name="packages[is_shipper_pack]" id="is_shipper_pack" />
                </div>
            </div>
            <div class="form-group">
                <label class="col-xs-3 control-label">Is Pallet</label>
                <div class="col-xs-5">
                    <input type="checkbox" name="packages[is_pallet]" id="is_pallet" />
                </div>
            </div>
            
        </div>
        <div class="tab-pane" id="pallet">
            <div class="form-group">
                <label class="col-xs-3 control-label">Pallet Capacity</label>
                <div class="col-xs-5">
                    <input type="text" name="pallet[pallet_capacity]" id="pallet_capacity" />
                </div>
            </div>
            <div class="form-group">
                <label class="col-xs-3 control-label">Pallet Stack height</label>
                <div class="col-xs-5">
                    <input type="text" name="pallet[pallet_stack_height]" id="pallet_stack_height" />
                </div>
            </div>
            <div class="form-group">
                <label class="col-xs-3 control-label">Full Pallet height</label>
                <div class="col-xs-5">
                    <input type="text" name="pallet[full_pallet_height]" id="full_pallet_height" />
                </div>
            </div>
            <div class="form-group">
                <label class="col-xs-3 control-label">Full Pallet weight</label>
                <div class="col-xs-5">
                    <input type="text" name="pallet[full_pallet_weight]" id="full_pallet_weight" />
                </div>
            </div>
            <div class="form-group">
                <label class="col-xs-3 control-label">Pallet Width</label>
                <div class="col-xs-5">
                    <input type="text" name="pallet[pallet_width]" id="pallet_width" />
                </div>
            </div>
            <div class="form-group">
                <label class="col-xs-3 control-label">Pallet Length</label>
                <div class="col-xs-5">
                    <input type="text" name="pallet[pallet_length]" id="pallet_length" />
                </div>
            </div>
        </div>
        <div class="tab-pane" id="product_attributes">
            <div class="form-group">
                <label class="col-xs-3 control-label">Product Attribute Set</label>
                <div class="col-xs-5">

                </div>
            </div>
            <div class="form-group">
                <label class="col-xs-3 control-label">Attribute</label>
                <div class="col-xs-5">
                    <input type="text" name="custom_attribute_name" value="" />
                </div>
            </div>
            <div class="form-group">
                <label class="col-xs-3 control-label">Text</label>
                <div class="col-xs-5">
                    <input type="text" name="custom_attribute_value" value="" />
                </div>
            </div>            
        </div>
        <div class="tab-pane" id="image_video">
            <div class="form-group">
                <label class="col-xs-3 control-label">Product Images</label>
                <span class="btn btn-default btn-file">
                    <input type="file" name="media[image1]">
                </span>
            
                <span class="btn btn-default btn-file">
                    <input type="file" name="media[image2]">
                </span>
                <span class="btn btn-default btn-file">
                    <input type="file" name="media[image3]">
                </span>
            </div>
            <div class="form-group">
                <label class="col-xs-3 control-label">Product Video</label>
                <div class="col-xs-5">
                    <input type="text" name="media[video]" value="" />
                </div>
            </div>            
        </div>
        <div class="tab-pane" id="service">
            <div class="form-group">
                <label class="col-xs-3 control-label">Service Center Name</label>
                <div class="col-xs-5">
                    <input type="text" name="service_center[service_center_name]" value="" />
                </div>
            </div>
            <div class="form-group">
                <label class="col-xs-3 control-label">Email</label>
                <div class="col-xs-5">
                    <input type="text" name="service_center[service_center_email]" value="" />
                </div>
            </div>
            <div class="form-group">
                <label class="col-xs-3 control-label">Phone Number</label>
                <div class="col-xs-5">
                    <input type="text" name="service_center[service_center_phone_number]" value="" />
                </div>
            </div>
            <div class="form-group">
                <label class="col-xs-3 control-label">Address</label>
                <div class="col-xs-5">
                    <input type="text" name="service_center[service_center_address]" value="" />
                </div>
            </div>
        </div>
        <div class="tab-pane" id="polices">
            <div class="form-group">
                <label class="col-xs-3 control-label">Return Policy</label>
                <span class="btn btn-default btn-file">
                    <input type="file" name="attributes[return_policy]">
                </span>
            </div>    
            <div class="form-group">
                <label class="col-xs-3 control-label">Cancellation Policy</label>
                <span class="btn btn-default btn-file">
                    <input type="file" name="attributes[cancellation_policy]">
                </span>
            </div>
            <div class="form-group">
                <label class="col-xs-3 control-label">Warranty</label>
                <span class="btn btn-default btn-file">
                    <input type="file" name="attributes[warranty]">
                </span>
            </div>
        </div>
        <div class="tab-pane" id="instructions">
            <div class="form-group">
                <label class="col-xs-3 control-label">Package Instructions</label>
                <span class="btn btn-default btn-file">
                    <input type="file" name="attributes[package_instructions]">
                </span>
            </div>    
            <div class="form-group">
                <label class="col-xs-3 control-label">Handling Instructions</label>
                <span class="btn btn-default btn-file">
                    <input type="file" name="attributes[handling_instructions]">
                </span>
            </div>
            <div class="form-group">
                <label class="col-xs-3 control-label">Recycling</label>
                <span class="btn btn-default btn-file">
                    <input type="file" name="attributes[recycling]">
                </span>
            </div>
             <div class="form-group">
                <label class="col-xs-3 control-label">Is COD Allowed</label>
                <div class="col-xs-5">
                    <input type="checkbox" name="product[is_cod_allowed]" value="" />
                </div>
            </div>
        </div>
        <div class="tab-pane" id="margin">
            <div class="form-group">
                <label class="col-xs-3 control-label">Distributor Margin</label>
                <div class="col-xs-5">
                    <input type="text" name="attributes[distributor_margin]" value="" />
                </div>
            </div>
        </div>
        <div class="tab-pane" id="tax">
            <div class="form-group">
                <label class="col-xs-3 control-label">Tax Class</label>
                <div class="col-xs-5">
                    <input type="text" name="tax_class" value="" />
                </div>
            </div>
            <div class="form-group">
                <label class="col-xs-3 control-label">Tax Percentage</label>
                <div class="col-xs-5">
                    <input type="text" name="tax_percentage" value="" />
                </div>
            </div>
        </div>
        <div class="tab-pane" id="credit_payments">
            <div class="form-group">
                <label class="col-xs-3 control-label">Credit Period</label>
                <div class="col-xs-5">
                    <input type="text" name="attributes[credit_period]" value="" />
                </div>
            </div>
        </div>
    </div>
</div>
<div class="form-group" style="margin-top: 15px;">
    <div class="col-xs-5 col-xs-offset-3">
        <button type="submit" class="btn btn-default">Submit</button>
    </div>
</div>

{{ Form::close() }}
<script type="text/javascript">
    $(document).ready(function () {
        $('#general').tabs({active: #});
    });
    $('.tab-pane').each(function (i, t) {
        $('#myTabs li').removeClass('active');
        $(this).addClass('active');
    });
</script>
@stop