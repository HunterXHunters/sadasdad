@extends('layouts.default')
@extends('layouts.header')
@extends('layouts.sideview')
@section('content')
@if($errors->any())
<h4>{{$errors->first()}}</h4>
@endif
<div class="box">
<div class="nav-tabs-custom left-nav">
            <ul class="nav nav-tabs pull-right">                  
                <!-- <li class=""><a href="#tax_class" data-toggle="tab" aria-expanded="true">Tax Class</a></li>
                <li class=""><a href="#price_management" data-toggle="tab" aria-expanded="true">Contracts</a></li>
                <li class=""><a href="#eseal_products" data-toggle="tab" aria-expanded="false">eSeal Products</a></li> -->
                <li class="active"><a href="#basic" data-toggle="tab" aria-expanded="false">Basic</a></li>
                <li class="pull-left header"><i class="fa fa-th"></i> Register Customer</li>
            </ul>
        </div>          
    <div class="col-sm-12">
       
    <div class="tile-body nopadding">                  
        
        <style type="text/css">
            .error {
                color: red;
            }
            .fileinput-button i{position:absolute; z-index:-99999!important;}
            .form-control-feedback{top:0px !important;}
            .checkbox input[type=checkbox], .checkbox-inline input[type=checkbox], .radio input[type=radio], .radio-inline input[type=radio]{margin-left:0px !important;}
            .col-sm-1{padding-left:0px;}
            h4{margin-bottom:20px !important;}
        </style>

        <?php if (isset($formData['error_message']))
        {
            ?>
            <div>
                <span><?php echo $formData['error_message']; ?></span>
            </div>
        <?php } ?>

        {{ Form::open(array('url' => 'customer/savecustomer', 'method' => 'POST', 'files'=>true, 'id' => 'customer_onboard')) }}
        
        <div class="tab-content">                    
            <div class="tab-pane active" id="basic">    
                <div class="tile-header">
                    <h4><strong>Company</strong> Information</h4>
                </div>
                    <div class="row">
                        <div class="form-group col-sm-6">
                            <label class="col-sm-2 control-label" for="BusinessType">Business Type*</label>
                            <div class="col-sm-10">
                                <div class="input-group input-group-sm">
                                    <span class="input-group-addon addon-red"><i class="fa fa-building-o"></i></span>
                                    <div id="selectbox">
                                        <select class="form-control" data-live-search="true" name="eseal_customers[customer_type_id]" id="customer_type_id" parsley-trigger="change" parsley-required="true" parsley-error-container="#selectbox">
                                            <option value="">Please Select...</option>
                                            @foreach ($formData['customerLookupIds'] as $key => $value)
                                            <option value="{{ $key }}">{{ $value }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group col-sm-6">
                            <label class="col-sm-2 control-label" for="BusinessType">Parent Company *</label>
                            <div class="col-sm-10">
                                <div class="input-group input-group-sm">
                                    <span class="input-group-addon addon-red"><i class="fa fa-building-o"></i></span>
                                    <div id="selectbox">
                                        <select class="form-control" data-live-search="true" name="eseal_customers[parent_company_id]" id="parent_company_id" parsley-trigger="change" parsley-required="true" parsley-error-container="#selectbox">
                                            <option value="">Please Select...</option>
                                            @foreach ($formData['parentCompanyList'] as $key => $value)
                                            <option value="{{ $key }}">{{ $value }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>                                                
                    </div>
                    <div class="row">
                        <div class="form-group col-sm-6">
                            <label class="col-sm-2 control-label" for="OrganizationName">Organization Name*</label>
                            <div class="col-sm-10">
                                <div class="input-group input-group-sm">
                                    <span class="input-group-addon addon-red"><i class="fa fa-sitemap"></i></span>
                                    <input type="text" class="form-control" placeholder="Organization Name" name="eseal_customers[brand_name]">
                                </div>
                            </div>                        
                        </div>
                        <div class="form-group col-sm-6">
                            <label class="col-sm-2 control-label" for="Website">Website*</label>
                            <div class="col-sm-10">
                                <div class="input-group input-group-sm">
                                    <span class="input-group-addon addon-red"><i class="fa fa-globe"></i></span>
                                    <input type="url" class="form-control data-fv-uri-allowlocal" placeholder="Website" name="eseal_customers[website]" >
                                </div>  
                            </div>                      
                        </div>                        
                    </div>
                    <div class="row">
                        <div class="form-group col-sm-6" id="upload_field">
                            <label class="col-sm-2 control-label"></label>
                            <div class="col-sm-10">
                                <span class="btn btn-success fileinput-button">
                                    <i class="glyphicon glyphicon-plus"></i>
                                    <span>Upload Logo...</span>
                                    <input type="hidden" name="files1">
                                    <input id="fileupload" type="file" name="files[]">
                                    <span>
                                    <div id="error" style="display: block;"></div>
                                    </span>
                                </span>
                            </div>
                        </div> 
                        <div class="form-group col-sm-6">
                            <div class="input-group input-group-sm">
                                <div id="files" class="files"></div>
                            </div>
                        </div>
                    </div>                
                <div class="row">
                    <div class="form-group col-sm-6">
                            <label class="col-sm-2 control-label" for="BusinessType">Country</label>
                            <div class="col-sm-10">
                                <div class="input-group input-group-sm">
                                    <span class="input-group-addon addon-red"><i class="fa fa-flag-checkered"></i></span>
                                    <div id="selectbox">
                                        <select class="chosen-select form-control parsley-validated" name="customer_address[country_id]" id="country" parsley-trigger="change" parsley-required="true" parsley-error-container="#selectbox">
                                            <option value="" selected>Please Select...</option>
                                            @foreach ($formData['countries'] as $key => $value)
                                            @if($key == 99)
                                            <option value="{{ $key }}" selected>{{ $value }}</option>
                                            @else
                                            <option value="{{ $key }}">{{ $value }}</option>
                                            @endif
                                            @endforeach
                                        </select>                                    
                                    </div>
                                </div>
                            </div>                        
                        </div>
                        <div class="form-group col-sm-6">
                            <label class="col-sm-2 control-label" for="Address1">Address1</label>
                            <div class="col-sm-10">
                                <div class="input-group input-group-sm">
                                    <span class="input-group-addon addon-red"><i class="fa fa-map-marker"></i></span>
                                    <input type="text" class="form-control" placeholder="Address1" name="customer_address[address_1]">
                                </div>
                            </div>                        
                        </div>                    
                    </div>          
                    <div class="row">
                        <div class="form-group col-sm-6">
                            <label class="col-sm-2 control-label" for="State">State</label>
                            <div class="col-sm-10">
                                <div class="input-group input-group-sm">
                                    <span class="input-group-addon addon-red"><i class="fa fa-flag"></i></span>
                                    <!-- <select name="customer_address[zone_id]" id="state_options" class="list-unstyled" parsley-trigger="change" parsley-required="true" parsley-error-container="#selectbox"> -->
                                    <select name="customer_address[zone_id]" class ="form-control" id="state_options" parsley-trigger="change" parsley-required="true" parsley-error-container="#selectbox">
<!--                                         <option value="" selected>Please Select...</option> -->
                                        @foreach ($formData['states'] as $key => $value)
                                        <option value="{{ $key }}">{{ $value }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="form-group col-sm-6">
                            <label class="col-sm-2 control-label" for="Address1">Address2</label>
                            <div class="col-sm-10">
                                <div class="input-group input-group-sm">
                                    <span class="input-group-addon addon-red"><i class="fa fa-map-marker"></i></span>
                                    <input type="text" class="form-control" placeholder="Address2" name="customer_address[address_2]">
                                </div>
                            </div>                        
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col-sm-6">
                            <label class="col-sm-2 control-label" for="City">City</label>
                            <div class="col-sm-10">
                                <div class="input-group input-group-sm">
                                    <span class="input-group-addon addon-red"><i class="fa fa-building-o"></i></span>
                                    <input type="text" class="form-control" placeholder="City" name="customer_address[city]">
                                </div>
                            </div>
                        </div>
                        <div class="form-group col-sm-6">
                            <label class="col-sm-2 control-label" for="Zipcode">Pin code</label>
                            <div class="col-sm-10">
                                <div class="input-group input-group-sm">
                                    <span class="input-group-addon addon-red"><i class="fa fa-barcode"></i></span>
                                    <input type="text" class="form-control" placeholder="Pincode" name="customer_address[postcode]">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="tile-header">
                        <h4><strong>Contact</strong> Information</h4>
                    </div>
                    <div class="row">
                        <div class="form-group col-sm-6">
                            <label class="col-sm-2 control-label" for="FirstName">First Name</label>
                            <div class="col-sm-10">
                                <div class="input-group input-group-sm">
                                    <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
                                    <input type="text" class="form-control" placeholder="First Name" name="eseal_customers[firstname]">
                                </div>
                            </div>
                        </div>
                        <div class="form-group col-sm-6">
                            <label class="col-sm-2 control-label" for="Last Name">Last Name</label>
                            <div class="col-sm-10">
                                <div class="input-group input-group-sm">
                                    <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
                                    <input type="text" class="form-control" placeholder="Last Name" name="eseal_customers[lastname]">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">                        
                        <div class="form-group col-sm-6">
                            <label class="col-sm-2 control-label" for="Designation">Designation</label>
                            <div class="col-sm-10">
                                <div class="input-group input-group-sm">
                                    <span class="input-group-addon addon-red"><i class="fa fa-list-alt"></i></span>
                                    <input type="text" class="form-control" placeholder="Designation" name="eseal_customers[designation]">
                                </div>
                            </div>
                        </div>
                        <div class="form-group col-sm-6">
                            <label class="col-sm-2 control-label" for="BusinessPhoneNumber">Business Phone Number</label>
                            <div class="col-sm-10">
                                <div class="input-group input-group-sm">
                                    <span class="input-group-addon addon-red"><i class="fa fa-fax"></i></span>
                                    <input type="text" class="form-control" placeholder="Business Phone Number" data-fv-phone="true" name="eseal_customers[phone]">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col-sm-6">
                            <label class="col-sm-2 control-label" for="Email">Email*</label>
                            <div class="col-sm-10">
                                <div class="input-group input-group-sm">
                                    <span class="input-group-addon addon-red"><i class="fa fa-envelope-o"></i></span>
                                    <input type="text" class="form-control data-fv-emailaddress-message" placeholder="Email" name="eseal_customers[email]">
                                </div>
                            </div>
                        </div>
                        <div class="form-group col-sm-6">
                            <label class="col-sm-2 control-label" for="MobileNumber">Mobile Number</label>
                            <div class="col-sm-10">
                                <div class="input-group input-group-sm">
                                    <span class="input-group-addon addon-red"><i class="fa fa-mobile"></i></span>
                                    <input type="text" class="form-control" placeholder="Mobile Number" name="eseal_customers[mobile_number]">
                                </div>
                            </div>
                        </div>                        
                    </div>
                   <!--  <div class="row">
                        <div class=" margin pull-right">
                            <button type="button" class="next btn btn-primary" id="continue">Continue</button>                        
                        </div>
                    </div> -->
                    <div class="row">
                    <div class="margin pull-right">
<!--                         <button type="button" class="previous btn btn-primary"  id="back3">Back</button> 
 -->                        {{ Form::submit('Submit', array('class' => 'btn btn-primary')) }}                       
                    </div>
                </div> 
            </div>
                    {{ Form::close() }}   

            <div class="tab-pane" id="eseal_products">
                <div class="row">
                    <div class="form-group col-sm-6">
                        <label for="BusinessType">Currency*</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-addon addon-red"><i class="fa fa-list-alt"></i></span>
                            <div id="selectbox">
                                <select class="chosen-select form-control parsley-validated" name="currency_code" id="currency_code" parsley-trigger="change" parsley-required="true" parsley-error-container="#selectbox">
                                    @foreach ($formData['currency'] as $key => $value)
                                    @if($key == 4)
                                    <option value="{{ $key }}" selected>{{ $value }}</option>
                                    @else
                                    <option value="{{ $key }}">{{ $value }}</option>
                                    @endif
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">                
                    <div class="form-group col-sm-12">
                        <label for="BusinessType">Products*</label>
                        @foreach ($formData['productLookupIds'] as $key => $value)
                        <div id="row">
                        <div class="col-xs-6 col-sm-1">
                        <div class="checkbox">
                            <input type="checkbox" class="checkthis control-label prods" value="{{ $key }}" id="{{ $key }}" name="eseal_customers[product_types][]" parsley-group="mygroup" parsley-trigger="change" parsley-required="true" parsley-mincheck="2" parsley-error-container="#myproperlabel .last" class="parsley-validated">
                            <label for="{{ $key }}">{{ $value }}</label>
                        </div>
                        </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                <div class="row">
                    <div class="margin pull-right">
                        <button type="button" class="previous btn btn-primary"  id="back">Back</button> 
                        <button type="button" class="next btn btn-primary"  id="continue2">Continue</button> 
                    </div>
                </div>
            </div>
            <div class="tab-pane" id="price_management">
                <div class="row">
                    <div class="form-group col-sm-6">
                        <label class="col-sm-2 control-label"  for="BusinessType">Components*</label>
                        <div class="col-sm-10">
                            <div class="input-group input-group-sm">
                                <span class="input-group-addon addon-red"><i class="fa fa-list-alt"></i></span>
                                <div id="selectbox">
                                    <select class="form-control" id="componentLookupIds" name="componentLookupIds" parsley-trigger="change" parsley-required="true" parsley-error-container="#selectbox">

                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group col-sm-6">
                        <label class="col-sm-2 control-label"  for="Agreed Price">Agreed Price</label>
                        <div class="col-sm-10">
                            <div class="input-group input-group-sm">
                                <span class="input-group-addon addon-red"><i class="fa fa-list-alt"></i></span>
                                <div class="row">
                                    <div class="col-xs-6" style="padding-right:0px; border-right:0px solid #fff;"><input type="number" min=0 step="any" class="form-control" placeholder="Agreed Price" name="agreed_price" id="agreed_price" value="" style="border-right:0px;"></div>
                                    <div class="col-xs-6" style="padding-left:0px; border-left:0px;"><input type="text" class="form-control" id="fixed_price" style="border-left:0px;" readonly></div>
                                </div>
                            </div>
                        </div>
                        <input type="hidden" name="eseal_price_master_id" value="" id="eseal_price_master_id" />
                    </div>  
                </div>
                <div class="row">
                    <div class="form-group col-sm-6">
                        <label class="col-sm-2 control-label"  for="Agreed Date From">Agreed Date From</label>
                        <div class="col-sm-10">
                            <div class="input-group input-append date" id="dateRangePickerFrom">
                                <span class="input-group-addon addon-red"><span class="glyphicon glyphicon-calendar"></span></span>
                                <input type="text" class="form-control" name="price_from" id="price_from" />                                    
                            </div>
                        </div>
                    </div>  
                    <div class="form-group col-sm-6">
                        <label class="col-sm-2 control-label"  for="Agreed Date To">Agreed Date To</label>
                        <div class="col-sm-10">
                            <div class="input-group input-append date" id="dateRangePickerTo">
                                <span class="input-group-addon addon-red"><span class="glyphicon glyphicon-calendar"></span></span>
                                <input type="text" class="form-control" name="price_to" id="price_to" />                                    
                            </div>
                        </div>
                    </div>  
                </div>
                <div class="row">
                    <div class="margin">
                        <button type="button" id="add" class="btn btn-primary">Add</button>                        
                    </div>
                </div>
                <div class="row">
                    <div class="form-group col-sm-12">
                        <!-- Default panel contents -->
                        <div class="tile-header">
                        <h4><strong>Details</strong> </h4>
                        </div>
                        <!-- Table -->
                        <div class="tile-body nopadding">
                            <table class="table table-bordered" id="confirmation">
                                <thead>
                                    <tr>
                                        <th>S.No</th>
                                        <th>Name</th>
                                        <th>Agreed Price</th>
                                        <th>Price From</th>
                                        <th>Price To</th>
                                        <th style="width: 30px;">Delete</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>                
                <div class="row">
                    <div class="margin pull-right">
                        <button type="button" class="previous btn btn-primary"  id="back2">Back</button> 
                        <button type="button" class="next btn btn-primary"  id="continue3">Continue</button> 
                    </div>
                </div>
            </div>              
            <div class="tab-pane" id="tax_class">
                <div class="row">
                    <div class="form-group col-sm-6">
                        <label class="col-sm-2 control-label"  for="BusinessType">Tax Class</label>
                        <div class="col-sm-10">
                            <div class="input-group input-group-sm">
                                <span class="input-group-addon addon-red"><i class="fa fa-list-alt"></i></span>
                                <div id="selectbox">
                                    <select class="form-control" name="tax_class_id[]" id="tax_class_id" multiple="true" parsley-trigger="change" parsley-required="true" parsley-error-container="#selectbox">
                                        @foreach ($formData['taxClassData'] as $key => $value)
                                            <option value="{{ $key }}">{{ $value }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>                      
                </div>
               <!--  <div class="row">
                    <div class="margin pull-right">
                        <button type="button" class="previous btn btn-primary"  id="back3">Back</button> 
                        {{ Form::submit('Submit', array('class' => 'btn btn-primary')) }}                       
                    </div>
                </div>  -->                       
            </div>
        </div>
        

    </div>
    
    </div>
</div>

@stop

@section('style')
{{HTML::style('jqwidgets/styles/jqx.base.css')}}
{{HTML::style('css/bootstrap-select.css')}}
{{HTML::style('css/datepicker.min.css')}}
{{HTML::style('css/jquery.fileupload.css')}}
@stop

@section('script')
<!-- location data -->
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
<!-- location data end -->

<script type="text/javascript">
    var count = 1;
    $(document).ready(function () {
        $('#customer_onboard').formValidation({
    //        live: 'disabled',
            framework: 'bootstrap',
            message: 'This value is not valid',
            feedbackIcons: {
                valid: 'glyphicon glyphicon-ok',
                invalid: 'glyphicon glyphicon-remove',
                validating: 'glyphicon glyphicon-refresh'
            },
            fields: {
                'files[]': {
                    validators: {
                        file: {
                            extension: 'jpeg,png,jpg',
                            type: 'image/jpeg,image/png',
                            maxSize: 2097152,   // 2048 * 1024
                            message: 'The selected file is not valid'                        
                        }
                    }
                },
                'eseal_customers[customer_type_id]': {
                    validators: {
                        callback: {
                            message: 'Please select business type.',
                            callback: function (value, validator, $field) {
                                var options = $('[id="customer_type_id"]').val();
                                return (options != "");
                            }
                        }
                    }
                },
                'customer_address[country_id]': {
                    validators: {
                        callback: {
                            message: 'Please select a Country.',
                            callback: function (value, validator, $field) {
                                var options = $('[id="country"]').val();
                                return (options != "");
                            }
                        }
                    }
                },                 
                'customer_address[zone_id]': {
                    validators: {
                        callback: {
                            message: 'Please select a state.',
                            callback: function (value, validator, $field) {
                                var options = $('[id="state_options"]').val();
                                return (options != "");
                            }
                        }
                    }
                },                
                componentLookupIds: {
                    validators: {
                        callback: {
                            message: 'Please select components.',
                            callback: function (value, validator, $field) {
                                var options = $('[id="componentLookupIds"]').val();
                                return (options != "");
                            }
                        }
                    }
                },
                currency_code: {
                    validators: {
                        callback: {
                            message: 'Please select Currency Code.',
                            callback: function (value, validator, $field) {
                                var options = $('[id="currency_code"]').val();
                                return (options != "");
                            }
                        }
                    }
                },                                  
                'eseal_customers[parent_company_id]': {
                    validators: {
                        notEmpty: {
                            message: 'Please select parent company.'
                        }
                    }
                },
                'eseal_customers[brand_name]': {
                    validators: {
                        notEmpty: {
                            message: 'The brand name is required and can\'t be empty.'
                        },
                        remote: {
                            url: '/customer/uniquevalidation',
                            type: 'POST',
                            data: function(validator, $field, value) {
                                return  {
                                    table_name: 'eseal_customer', 
                                    field_name: 'brand_name', 
                                    field_value: value, 
                                    pluck_id: 'customer_id'
                                };
                            },
                            delay: 2000,     // Send Ajax request every 2 seconds
                            message: 'Brand name exists, please provide new brand name.'
                        }
                    }
                },
                'eseal_customers[website]': {
                    validators: {
                        notEmpty: {
                            message: 'The web address is required and can\'t be empty'
                        },
                        uri: {
                            message: 'The website address is not valid'
                        }
                    }
                },
                'eseal_customers[email]': {
                    validators: {
                        notEmpty: {
                            message: 'The email address is required and can\'t be empty 1'
                        },
                        remote: {
                            url: '/customer/validateemail',
                            type: 'POST',
                            data: {email: $('[name="eseal_customers[email]"]').val()},
                            delay: 2000,     // Send Ajax request every 2 seconds
                            message: 'Email already exists, please provide new email.'
                        },
                        regexp: {
                        regexp: '^[^@\\s]+@([^@\\s]+\\.)+[^@\\s]+[^@\\s]+$',
                        message: 'Please enter a valid email address'
                           
                        }
                    }
                },
                'eseal_customers[phone]': {
                    validators: {
                        phone: {
                            country: 'IN',
                            message: 'The value is not valid %s phone number'
                        },
                        numeric: {
                            message: 'The value is not a number'
                        },
                        stringLength: {
                            max: 10
                        }
                    }
                },
         'eseal_customers[firstname]': {
                    validators: {
                      regexp: {
                          regexp: '^[a-zA-Z .]+$',
                          message: 'Please enter only alphabets'
                      }
                    }
                },  
         'eseal_customers[lastname]': {
                    validators: {
                      regexp: {
                          regexp: '^[a-zA-Z .]+$',
                          message: 'Please enter only alphabets'
                      }
                    }
                }, 
         'eseal_customers[designation]': {
                    validators: {
                      regexp: {
                          regexp: '^[a-zA-Z0-9 .]+$',
                          message: 'Please enter only alphabets'
                      }
                    }
                },
            'eseal_customers[product_types][]': {
              selector: '.prods',
                validators: {
                     choice: {
                        min: 1,
                        message: 'Please choose atleast one product.'
                      }
                  }
                },                
         'customer_address[city]': {
                    validators: {
                      regexp: {
                          regexp: '^[a-zA-Z0-9 .]+$',
                          message: 'Please enter only alpha-numeric'
                      }
                    }
                },                
         'customer_address[postcode]': {
                    validators: {
                      regexp: {
                          regexp: '^[0-9]+$',
                          message: 'Please enter only Numbers'
                      },
                        stringLength: {
                            min: 6,
                            max: 6
                        }
                    }
                },                                                               
                'eseal_customers[mobile_number]': {
                    validators: {
                        phone: {
                            country: 'IN',
                            message: 'The value is not valid %s phone number'
                        },
                        numeric: {
                            message: 'The value is not a number'
                        },
                        stringLength: {
                            max: 10
                        }
                    }
                }
            }
        }).submit(function(){
        $('#customer_onboard').submit();
         }).validate({
            submitHandler: function (form) {            
            if($('#customer_onboard').data('formValidation') && checkEmail())
            {
                form.submit();
            }
        }
    });
});

    // $('#customer_type_id').change(function(event){
    //     $selected = $(this);
    //     if($selected.val() == 1005)
    //     {
    //         $('#parent_company_id').val('-1');
    //         $('#parent_company_id').prop('disabled', true);
    //         //$('#parent_company_id').selectpicker('refresh');
    //         $('<input/>').attr('type', 'hidden').attr('name', $('#parent_company_id').attr('name')).val('-1').appendTo($('#parent_company_id').parent());
    //     }else{
    //         $('#parent_company_id').prop('disabled', false);
    //         $('input[name="eseal_customers[parent_company_id]"').remove();
    //         //$('#parent_company_id').selectpicker('refresh');
    //     }
    // });
    
    // $('.tab-pane').each(function (i, t) {
    //     $('#myTabs li').removeClass('active');
    //     $(this).addClass('active');
    // });
    // $("#componentLookupIds").change(function () {
    //     var componentData = $(this).val();
    //     if ( componentData )
    //     {
    //         var componentArray = componentData.split('#');
    //         var price = componentArray[1];
    //         var id = componentArray[2];
    //         $('#agreed_price').val(price);
    //         $('#eseal_price_master_id').val(id);
    //         $('#fixed_price').val(price);
    //     }
    // });
    
    // $("#continue").click(function () {
    //     if(validateTab(0))
    //     {
    //         changeTab('#eseal_products');
    //     }
    // });
    $('#customer_onboard').submit(function(){
        $('#customer_onboard').submit();
    });
    $('#customer_onboard').validate({
        submitHandler: function (form) {            
            if($('#customer_onboard').data('formValidation') && checkEmail())
            {
                form.submit();
            }
        }
    });
    // $("#continue2").click(function () {
    //     updatePriceManagement();
    //     changeTab('#price_management');       
    // });
    // $("#back").click(function () {
    //     changeTab('#basic');
    // });
    // $("#back2").click(function () {
    //     changeTab('#eseal_products');
    // });
    // $("#continue3").click(function () {
    //     changeTab('#tax_class');       
    // });
    // $("#back3").click(function () {
    //     changeTab('#price_management');
    // });
    
    function validateTab(index) {
        var fv   = $('#customer_onboard').data('formValidation'), // FormValidation instance
            // The current tab
            $tab = $('#customer_onboard').find('.tab-pane').eq(index);
        // Validate the container
        fv.validateContainer($tab);

        var isValidStep = fv.isValidContainer($tab);
        if (isValidStep === false || isValidStep === null) {
            // Do not jump to the target tab
            return false;
        }else if(index == 0 && isValidStep == true){
            return validateImage();            
        }
        return true;
    }
    
    function validateImage()
    {
        var fileName = $('#files').children().find('input');
        if(typeof fileName.val() != 'undefined')
        {
            $('#upload_field').children('div.col-sm-10').children('span').children('i.form-control-feedback').removeClass('glyphicon-remove').addClass('glyphicon-ok');
            $('#upload_field').removeClass('has-error');
            $('#upload_field').children('div.col-sm-10').children('small').hide();
            return true;
        }else{
            $('#upload_field').children('div.col-sm-10').children('span').children('i.form-control-feedback').removeClass('glyphicon-ok').addClass('glyphicon-remove');
            $('#upload_field').removeClass('has-success');
            $('#upload_field').addClass('has-error');
            $('#upload_field').children('div.col-sm-10').children('small').show();
            return false;
        }
    }
    
    function changeTab(tabName)
    {
        $('[data-toggle="tab"]').each(function(event){
            $tab = $(this);
            if($tab.attr('href') == tabName)
            {
                $tab.parent().addClass('active');
                $(tabName).addClass('tab-pane active');
            }else{
                $tab.parent().removeClass('active');
                $($tab.attr('href')).removeClass('active');
            }
        });
    }
  
    function isValidDate(dateVal)
    {
        var dob = dateVal;
        var data = dob.split("/");
        if (isNaN(Date.parse(data[2] + "-" + data[1] + "-" + data[0]))) {
            return 0;
        }
        else
        return 1;
    }

function alertDelete()
{   
    var del = $('#remCF').val();
    if(del = true)
    {
        alert('Are you sure you want to delete?');
    }
}

$("#add").click(function () {
        var name = $('#componentLookupIds option:selected').text();
        var agreedPrice = $('#agreed_price').val();
        var priceFrom = $('#price_from').val();
        var priceTo = $('#price_to').val();
        
        if(isValidDate(priceFrom) == 0 || isValidDate(priceTo) == 0)
              {
                 alert("Invalid date format");
                }
        

        else{   
        if(agreedPrice != '' && priceFrom != '' && priceTo != '')
        {
            var priceElements = new Array();
            $('[id="price_master_name"]').each(function(){
                priceElements.push($(this).text());
            });
            if(priceElements.length > 0 && $.inArray(name, priceElements) >= 0)
            {
                alert('This element is already added.');
            }else{
                var jsonArg1 = new Object();
                jsonArg1.eseal_price_master_id = $('#eseal_price_master_id').val();
                jsonArg1.name = name;
                jsonArg1.fixed_price = $('#fixed_price').val();
                jsonArg1.agreedPrice = agreedPrice;
                jsonArg1.priceFrom = priceFrom;
                jsonArg1.priceTo = priceTo;
                var hiddenJsonData = new Array();
                hiddenJsonData.push(jsonArg1);
                if(priceFrom>priceTo)
                {
                    $('#price_to').focus();
                }
                else
                {
                    $("#confirmation").append('<tr><td scope="row">' + count + '</td><td id="price_master_name">' + name + '</td><td>' + agreedPrice + '</td><td>' + priceFrom + '</td><td>' + priceTo + '</td><td><a href="javascript:void(0);" class="check-toggler" onclick="alertDelete()" id="remCF"><span class="badge bg-red"><i class="fa fa-trash-o"></i></span></a><input type="hidden" name="price_details[]" value=' + "'" + JSON.stringify(jsonArg1) + "'" + ' /></td></tr>');
                    count++;
                }
            } 
        }else{
            if(agreedPrice == '')
            {
                $('#agreed_price').focus();
            }
            if(priceFrom == '')
            {
                $('#price_from').focus();
            }
            if(priceTo == '')
            {
                $('#price_to').focus();
             }
        }
    }        
});

    $("#confirmation").on('click', '#remCF', function () {
        $(this).parent().parent().remove();
    });

    $("#country").on('change', function () {
        ajaxCall();
    });
    function ajaxCall()
    {
        $('#state_options').empty();
        var countryId = $('#country').val();
        $.get('/customer/getZones?countryId=' + countryId, function (data) {
            var result = $.parseJSON(data);
            $('#state_options').find('option').remove().end();
            //$('#state_options').append($("<option>").attr('value', '0').text('Please select'));
            $.each(result, function (k, v) {
                //display the key and value pair
                $('#state_options').append($("<option>").attr('value', k).text(v));
            });
            //$('#state_options').selectpicker('refresh');
        });
    }

    /* code to get eseal products dynamically when moving to Price management tab  */
    $('[data-toggle="tab"]').click(function () {
        if ( $('#eseal_products').attr('class') == 'tab-pane active' )
        {
            updatePriceManagement();
        }
    });

    function updatePriceManagement()
    {
        var checkedIds = new Array();
        var count = 0;
        $('[parsley-group="mygroup"]').each(function () {
            if ( $(this).is(":checked") ) {
                checkedIds[count] = $(this).val();
                count++;
            }
        });
        getComponentData(checkedIds);
    }

    function getComponentData(checkedIds)
    {
        url = '/customer/getcomponentdata';
        // Send the data using post
        var posting = $.get(url, {productLookupIds: checkedIds});
        // Put the results in a div
        posting.done(function (data) {
            if ( data )
            {
                responseData = JSON.parse(data);
                $('#componentLookupIds').find('option').remove();
                $('#componentLookupIds_chosen').find('li').remove();
                $('#componentLookupIds').append('<option  value="">Please Select...</option>');
                $.each(responseData, function (key, esealPriceData) {
                    $('#componentLookupIds').append('<option value="' + esealPriceData['component_type_lookup_id'] + '#' + esealPriceData['price'] + '#' + esealPriceData['id'] + '#' + esealPriceData['valid_upto'] + '"><font><font>' + esealPriceData['name'] + '</font></font></option>');
                    $('#componentLookupIds_chosen').find('.chosen-results').append('<li class="active-result" style="" data-option-array-index="' + key + '">' + esealPriceData['name'] + '</li>');
                });
                //$('#componentLookupIds').selectpicker('refresh');
                $("#componentLookupIds").trigger("change");
            }
        });
    }
    function datePicket()
    {
        var today = new Date();
        var dd = today.getDate();
        var ddd = today.getDate()+1;
        var mm = today.getMonth()+1; //January is 0!

        var yyyy = today.getFullYear();
        var yyyyy = today.getFullYear()+20;
        if(dd<10){
            dd='0'+dd
        } 
        if(mm<10){
            mm='0'+mm
        } 
        var today = yyyy+'-'+mm+'-'+dd;
        var tomorrow = yyyy+'-'+mm+'-'+ddd;
        
        $('#dateRangePickerFrom')
            .datepicker({
                format: 'yyyy-mm-dd',
                startDate: today,
                endDate: yyyyy+'-12-30'
            })
            .on('changeDate', function(e) {
                // Revalidate the date field
                //$('#dateRangeForm').formValidation('revalidateField', 'date');
                $('#price_to').val('');
                $('#dateRangePickerTo').datepicker('remove');
                changeData($("#price_from").val(), yyyyy);
                $('.datepicker.datepicker-dropdown').hide();
            });
    }
    function changeData(tomorrow, yyyyy)
    {
        $('#dateRangePickerTo')
            .datepicker({
                format: 'yyyy-mm-dd',
                startDate: tomorrow,
                endDate: yyyyy+'-12-30',
                Default: false
            })
            .on('changeDate', function(e) {
                // Revalidate the date field
                //$('#dateRangePickerTo').formValidation('revalidateField', 'date');
                $('.datepicker.datepicker-dropdown').hide();
            });
    }    

</script>
@stop
@extends('layouts.footer')
