@extends('layouts.default')
@extends('layouts.header')
@extends('layouts.sideview')
@section('content')
@if($errors->any())
<h4>{{$errors->first()}}</h4>
@endif
<div class="box">

<style type="text/css">
    .error {
        color: red;
    }
    .modal-header h4{margin-bottom:0px !important}
    .fileinput-button i{position:absolute; z-index:-99999!important;}
    .form-control-feedback{top:0px !important;}
    .checkbox input[type=checkbox], .checkbox-inline input[type=checkbox], .radio input[type=radio], .radio-inline input[type=radio]{margin-left:0px !important;}
    .col-sm-1{padding-left:0px;}
    h4{margin-bottom:20px !important;}
</style>

<?php
$permissions = isset($permissions['approval']) ? $permissions['approval'] : 0;
//$customers = isset($customerDetails['customer']) ? $customerDetails['customer'] : new stdClass();
//$product_types = isset($customers['product_types']) ? explode(',', $customers['product_types']) : array();
//$customerErpDetails = isset($erp_details) ? $erp_details : new stdClass();
//$customerbusinessid = isset($customerbusinessid) ? $customerbusinessid : new stdClass();
//$customerstoragelocations = isset($customerstoragelocations) ? $customerstoragelocations : new stdClass();
//$customerPlans = isset($customerDetails['plans']) ? $customerDetails['plans'] : new stdClass();
$taxClass = array();
if (empty($customerPlans))
{
    $currencyCode = 4;
} else
{
    $currencyCode = isset($customerPlans[0]) ? $customerPlans[0]->currency_code : 4;
    $taxClass = isset($customerPlans[0]) ? explode(',', $customerPlans[0]->tax_class_id) : array();
}
$customerAddress = isset($customerAddressData) ? $customerAddressData : new stdClass();
$customerLocations = isset($customer_locations) ? $customer_locations : array();
$customer_id = isset($customer_id) ? $customer_id : '';
?>


<div><span class="error_message"></span></div>
    <input type="hidden" name="customer_id"  value ="{{$customer_id}}">
    <div class="box-header">
      <a href="#location_types"><h3 class="box-title"><strong>Transaction </strong>Details</h3></a>
      </div>

    <!-- tile body -->
    <div class="col-sm-12">
       <div class="tile-body nopadding">
         <div class="tab-content">                   
    <div id="transaction">    
            <div class="row">
                <div class="form-group col-sm-6">
                    <input type="button" class="btn btn-primary" data-toggle="modal" data-target="#TransactionAddModal" value="Add Transaction" />
                </div>
            </div>
            <div class="row">
                <div class="form-group col-sm-12">
                    <div id="transaction_grid"></div>
                </div>
            </div>
        </div>
    </div>
  </div>
 </div>
</div>          
        



<div id="customer_approval" class="modal fade">
  <div class="modal-dialog">
    <div class="modal-content">
      <!-- dialog body -->
      <div class="modal-body">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        Do you want to approve.
      </div>
      <!-- dialog buttons -->
      <div class="modal-footer"><button type="button" class="btn btn-primary">Yes</button><button type="button" class="btn btn-danger">No</button></div>      
    </div>
  </div>
</div>

<!-- Modal - Popup for ADD Transaction -->
<div class="modal fade" id="TransactionAddModal" tabindex="-1" role="dialog" aria-labelledby="basicvalCode" aria-hidden="true">
    <div class="modal-dialog wide">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h4 class="modal-title" id="basicvalCode">Add Transaction</h4>
            </div>
            <div class="modal-body">
                {{ Form::open(array('url' => '/transaction/savetransaction', 'data-url' => '/transaction/savetransaction/','id' => 'add_transactiontypes_form')) }}
                {{ Form::hidden('_method','POST') }}
                <input type="hidden" name="manufacturer_id" id="manufacturer_id" value="{{$manufacturer_id}}" />
                <!-- tile body -->
                <div class="row">
                    <div class="form-group col-sm-6">
                        <label for="exampleInputEmail">Name *</label>
                        <div class="input-group ">
                            <span class="input-group-addon addon-red"><i class="fa fa-puzzle-piece"></i></span>
                            <input type="text" id="name" name="name" value="" class="form-control">
                        </div>
                    </div>                
                    <div class="form-group col-sm-6">
                        <label for="forName">Description</label>
                        <div class="input-group ">
                            <span class="input-group-addon addon-red"><i class="ion-clipboard"></i></span>
                            <input type="text"  id="description" name="description" placeholder="description" class="form-control">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="form-group col-sm-6">
                        <label for="forName">Action Code *</label>
                        <div class="input-group ">
                            <span class="input-group-addon addon-red"><i class="ion-aperture"></i></span>
                            <input type="text" id="action_code" name="action_code" placeholder="action code" class="form-control">
                        </div>
                    </div>                                      
                    <div class="form-group col-sm-6">
                        <label for="exampleInputEmail">Source Location Action *</label>
                        <div class="input-group ">
                            <span class="input-group-addon addon-red"><i class="fa fa-map-marker"></i></span>
                            <select name="srcLoc_action" id="srcLoc_action" class="form-control" >
                                <option value="-1">-1</option>
                                <option value="0">0</option>
                                <option value="1">1</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="form-group col-sm-6">
                        <label for="exampleInputEmail">Destination Location Action *</label>
                        <div class="input-group">
                            <span class="input-group-addon addon-red"><i class="fa fa-map-marker"></i></span>
                            <select name="dstLoc_action" id="dstLoc_action" class="form-control" >
                                <option value="-1">-1</option>
                                <option value="0">0</option>
                                <option value="1">1</option>
                            </select>
                        </div>                        
                    </div>                
                    <div class="form-group col-sm-6">
                        <label for="exampleInputEmail">Intrn Action *</label>
                        <div class="input-group ">
                            <span class="input-group-addon addon-red"><i class="fa fa-exchange"></i></span>
                            <select name="intrn_action" id="intrn_action" class="form-control" >
                                <option value="-1">-1</option>
                                <option value="0">0</option>
                                <option value="1">1</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="form-group col-sm-6">
                        <label for="exampleInputEmail">Feature Code *</label>
                        <div class="input-group ">
                            <span class="input-group-addon addon-red"><i class="fa fa-newspaper-o"></i></span>
                            <input type="text" id="feature_code" name="feature_code" placeholder="feature_code" class="form-control">
                        </div>
                    </div>
                    <div class="form-group col-sm-6">
                        <label for="exampleInputEmail">Group</label>
                        <div class="input-group ">
                            <span class="input-group-addon addon-red"><i class="fa fa-cubes"></i></span>
                            <input type="text" id="group" name="group" placeholder="group" class="form-control">
                        </div>
                    </div>                    
                </div>
                {{ Form::submit('Add Transaction', array('class' => 'btn btn-primary', 'id' => 'add_transactiontypes_button')) }}
                {{Form::close()}}
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal --

<!-- Modal - Popup for EDIT Transaction -->
<div class="modal fade" id="TransactionEditModal" tabindex="-1" role="dialog" aria-labelledby="basicvalCode" aria-hidden="true">
    <div class="modal-dialog wide">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h4 class="modal-title" id="basicvalCode">Edit Transactions</h4>
            </div>
            <div class="form-group">
              <div id="erroMsg" style="color: #FF0000"></div>
                </div> 
            <div class="modal-body">
                {{ Form::open(array('url' => '/transaction/updatetransaction','data-url' => '/transaction/updatetransaction/','id' => 'edit_transactiontypes_form')) }}
                {{ Form::hidden('_method','PUT') }}
                <input type="hidden" name="manufacturer_id" id="manufacturer_id" value="{{$manufacturer_id}}" />                
                <!-- tile header -->
                <!-- tile body -->
                <div class="row">
                    <div class="form-group col-sm-6">
                        <label for="forName">Name *</label>
                        <div class="input-group ">
                            <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
                            <input type="text" id="name" name="name" value="" class="form-control">
                            <input type="hidden" name="id" value="" />
                        </div>
                    </div>                
                    <div class="form-group col-sm-6">
                        <label for="forName">Action Code *</label>
                        <div class="input-group ">
                            <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
                            <input type="text"  id="action_code" name="action_code" value="" class="form-control">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="form-group col-sm-6">
                        <label for="forName">Description</label>
                        <div class="input-group ">
                            <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
                            <input type="text" id="description" name="description" value="" class="form-control">
                        </div>
                    </div>                
                    <div class="form-group col-sm-6">
                        <label for="exampleInputEmail">Source Location Action *</label>
                        <div class="input-group ">
                            <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
                            <select name="srcLoc_action" id="srcLoc_action" class="form-control" >
                                <option value="-1">-1</option>
                                <option value="0">0</option>
                                <option value="1">1</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="form-group col-sm-6">
                        <label for="exampleInputEmail">Destination Location Action *</label>
                        <div class="input-group">
                            <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
                            <select name="dstLoc_action" id="dstLoc_action" class="form-control" >
                                <option value="-1">-1</option>
                                <option value="0">0</option>
                                <option value="1">1</option>
                            </select>
                        </div>
                    </div>                
                    <div class="form-group col-sm-6">
                        <label for="exampleInputEmail">Intrn Action *</label>
                        <div class="input-group ">
                            <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
                            <select name="intrn_action" id="intrn_action" class="form-control" >
                                <option value="-1">-1</option>
                                <option value="0">0</option>
                                <option value="1">1</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="form-group col-sm-6">
                        <label for="exampleInputEmail">Feature Code *</label>
                        <div class="input-group ">
                            <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
                            <input type="text" id="feature_code" name="feature_code" value="" class="form-control">
                        </div>
                    </div>
                    <div class="form-group col-sm-6">
                        <label for="exampleInputEmail">Group</label>
                        <div class="input-group ">
                            <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
                            <input type="text" id="group" name="group" placeholder="group" class="form-control">
                        </div>
                    </div>                       
                </div>
                {{ Form::submit('Update Transactions', array('class' => 'btn btn-primary')) }}
                {{Form::close()}}

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
                                    <input type="password" id="verifypassword" name="passwordverify">      
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
{{HTML::style('css/bootstrap-select.css')}}
{{HTML::style('css/datepicker.min.css')}}
{{HTML::style('css/jquery.fileupload.css')}}
@stop

@section('script')
<!-- location data -->
{{HTML::script('jqwidgets/jqxcore.js')}}
{{HTML::script('jqwidgets/jqxbuttons.js')}}
{{HTML::script('jqwidgets/jqxscrollbar.js')}}
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
<!-- location data end -->

<script type="text/javascript">
    var count = 1;
    $(document).ready(function () {
        $('#customer_onboard_update').formValidation({
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
                    },
                    onSuccess: function(e, data) {
                        if(!validateImage())
                        {
                            $('#customer_onboard_update').formValidation('addField', $('[name="files[]"]','blank'));
                            return false;
                        }else{
                            $('#upload_field').removeClass('has-error');
                            $('#upload_field').children('div.col-sm-10').children('small').hide();
                            return true;
                        }
                    }
                },
                'eseal_customers[customer_type_id]': {
                    validators: {
                        notEmpty: {
                            message: 'Please select business type.'
                        }
                    }
                },
                'eseal_customers[parent_company_id]': {
                    validators: {
                        notEmpty: {
                            message: 'Please parent company.'
                        }
                    }
                },
                'eseal_customers[brand_name]': {
                    validators: {
                        notEmpty: {
                            message: 'The brand name is required and can\'t be empty.'
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
                            message: 'The email address is required and can\'t be empty'
                        },
                        remote: {
                            url: '/customer/validateemail',
                            type: 'POST',
                            data: function(validator, $field, value) {
                                return  {
                                    email: value,
                                    customer_id: $('[name="customer_id"]').val() 
                                };
                            },
                            delay: 2000,     // Send Ajax request every 2 seconds
                            message: 'Email already exists, please provide new email.'
                        },

                         regexp: {
                        regexp: '^[^@\\s]+@([^@\\s]+\\.)+[^@\\s]+[^@\\s]+$',
                            message : 'Please enter a valid email address'
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
                },
                type: {
                    validators: {
                        notEmpty: {
                            message: 'Please check one checkbox.'
                        }
                    }
                }
            }
        }).bootstrapWizard({
            tabClass: 'nav nav-tabs',
            onTabClick: function(tab, navigation, index) {                
                var currentTabName = $('.nav.nav-tabs li.active').find('a').text();
                if(currentTabName == 'Basic')
                {
                    return validateTab(0);
                }else if(currentTabName == 'eSeal Products')
                {
                    return validateTab(1);
                }else if(currentTabName == 'Contracts'){
                    return validateTab(2);
                }
                //return validateTab(index);
            },
            onNext: function(tab, navigation, index) {
                var numTabs    = $('#customer_onboard_update').find('.tab-pane').length,
                    isValidTab = validateTab(index - 1);
                if (!isValidTab) {
                    return false;
                }
                return true;
            },
            onPrevious: function(tab, navigation, index) {
                return validateTab(index + 1);
            },
            onTabShow: function(tab, navigation, index) {
            }
        });       
        
        $('#erp_configurations_form').bootstrapValidator({
    //        live: 'disabled',
            message: 'This value is not valid',
            feedbackIcons: {
                valid: 'glyphicon glyphicon-ok',
                invalid: 'glyphicon glyphicon-remove',
                validating: 'glyphicon glyphicon-refresh'
            },
            fields: {
                erp_model: {
                    validators: {
                        notEmpty: {
                            message: 'Please select erp model.'
                        }                        
                    }
                },
                integration_mode: {
                    validators: {
                        notEmpty: {
                            message: 'Please select integration model.'
                        }                        
                    }
                },
                // web_service_url: {
                //     validators: {
                //         uri: {
                //             message: 'The website address is not valid.'
                //         }                        
                //     }
                // },
                company_code: {
                    validators: {
                        notEmpty: {
                            message: 'Please provide company code.'
                        }                        
                    }
                },                
                web_service_username: {
                    validators: {
                        notEmpty: {
                            message: 'Please field is required.'
                        }                        
                    }
                },
                web_service_password: {
                    validators: {
                        notEmpty: {
                            message: 'Please field is required.'
                        }                        
                    }
                },
                default_start_date: {
                    validators: {
                        notEmpty: {
                            message: 'Please field is required.'
                        }                        
                    }
                }
            }
        });
        $('#location_types_add').on('hide.bs.modal',function(){
            console.log('resetForm');
            $('#add_locationtypes_form').data('bootstrapValidator').resetForm();
            $('#add_locationtypes_form')[0].reset();
        });
        $('#add_locationtypes_form').bootstrapValidator({
    //        live: 'disabled',
            message: 'This value is not valid',
            feedbackIcons: {
                valid: 'glyphicon glyphicon-ok',
                invalid: 'glyphicon glyphicon-remove',
                validating: 'glyphicon glyphicon-refresh'
            },
            fields: {
                location_type_name: {
                    validators: {
                        notEmpty: {
                            message: 'The location type name is required and can\'t be empty'
                        },
                        regexp: {
                            regexp: /^[a-zA-Z0-9\s]+$/i,
                               message: 'The Location Name can consist of alphabetical characters and spaces only'
                        },
                    remote: {
                            url: '/customer/uniquevalidation',
                            type: 'POST',
                            data: {
                                table_name: 'location_types', 
                                field_name: 'location_type_name', 
                                field_value: $('#add_locationtypes_form #location_type_name').val(), 
                                manufacturer_id: $('[name="customer_id"]').val(),
                                pluck_id: 'location_type_id',

                            },
                            delay: 2000,     // Send Ajax request every 2 seconds
                            message: 'Name already exists.'
                        },                                  
                    }
                }
            }
        }).on('success.form.bv', function(event) {
            event.preventDefault();
            $('#add_locationtypes_button').attr("disabled", true);
            $form = $(this);
            url = $form.attr('action');
            var location_type_name = $('[name="location_type_name"]').val();
            var locationTypeFields = {location_type_name: location_type_name , manufacturer_id: $('#add_locationtypes_form #manufacturer_id').val() };
            // Send the data using post
            var posting = $.post(url, { location_type: locationTypeFields });
            // Put the results in a div
            posting.done(function( data ) {
                if(data.status == true)
                {
                    var location_type_id = data.location_type_id;
                    var optionData = '<option value="' + location_type_id + '">'+location_type_name +'</option>';
                    $('#location_type_id_vendor').append(optionData);
                    $('#add_location_type_id').append(optionData);
                    $('#update_location_type_id').append(optionData);
                    $('#location_type_id_plant').append(optionData);
                    $('#location_type_id_customer').append(optionData);
                    alert('Location type created.');
                    $('.close').trigger('click');
                    $('[name="location_type_name"]').val('');
                    //$('#location_type_manufacturer_id').val('');
                }
                $('#add_locationtypes_button').attr("disabled", false);
            });
            $form.bootstrapValidator('resetForm',true); 
            return false;      
    }).validate({
        submitHandler: function (form) {
            return false;
        }
    });


$('#edit_locationtypes_form').bootstrapValidator({
            message: 'This value is not valid',
            feedbackIcons: {
                valid: 'glyphicon glyphicon-ok',
                invalid: 'glyphicon glyphicon-remove',
                validating: 'glyphicon glyphicon-refresh'
            },
            fields: {
                location_type_name: {
                    validators: {
                        notEmpty: {
                            message: 'The location type name is required and can\'t be empty'
                        },
                           remote: {
                            url: '/customer/uniquevalidation',
                            type: 'POST',
                           data: function(validator, $field, value) {
                         return  {
                                table_name: 'location_types', 
                                field_name: 'location_type_name', 
                                field_value: value, 
                                manufacturer_id: $('#manufacturer_id').val(),
                                pluck_id: 'location_type_id', 
                                row_id: $('#edit_locationtypes_form [name="location_type_id"]').val()
                             };
                            },
                            delay: 2000,     // Send Ajax request every 2 seconds
                            message: 'Name already exists.'
                        },
                        regexp: {
                            regexp: /^[a-zA-Z0-9\s]+$/i,
                               message: 'The Location Name can consist of alphabetical characters and spaces only'
                        },
                                                   
                    }
                }
            }
        }).on('success.form.bv', function(event) {
            ajaxCallPopup($('#edit_locationtypes_form'));
        return false;     
    }).validate({
        submitHandler: function (form) {
            return false;
        }
    });



    //$('#location_types_add').on('show.bs.modal',function(){console.log('location_types_add'+' opening');$('#location_types_add form')[0].reset()});

        $('#import_locations_from_erp').bootstrapValidator({
    //        live: 'disabled',
            message: 'This value is not valid',
            feedbackIcons: {
                valid: 'glyphicon glyphicon-ok',
                invalid: 'glyphicon glyphicon-remove',
                validating: 'glyphicon glyphicon-refresh'
            },
            fields: {
                location_type_id_vendor: {
                    validators: {
                        notEmpty: {
                            message: 'The field is required and can\'t be empty'
                        }                        
                    }
                },
                location_type_id_plant: {
                    validators: {
                        notEmpty: {
                            message: 'The field is required and can\'t be empty'
                        }                        
                    }
                },
                location_type_id_customer: {
                    validators: {
                        notEmpty: {
                            message: 'The field is required and can\'t be empty'
                        }                        
                    }
                },
                'location_type[]': {
                    validators: {
                        notEmpty: {
                            message: 'Please check atleat one check box.'
                        }
                    }
                }
            }
        }).on('success.form.bv', function(event) {
            event.preventDefault();
            $('#import_locations_from_erp_button').attr("disabled", true);
            $form = $(this);
            url = $form.attr('action');
            var vendor = ($('#vendor_supplier').prop('checked')) ? 1 : 0;
            var plant = ($('#Plant').prop('checked')) ? 1 : 0;
            var customer = ($('#customer').prop('checked')) ? 1 : 0;
            var location_type_id_vendor = $('#location_type_id_vendor').val();
            var location_type_id_plant = $('#location_type_id_plant').val();
            var location_type_id_customer = $('#location_type_id_customer').val();
            var locationTypeFields = { vendor: vendor, location_type_id_vendor: location_type_id_vendor, plant: plant, location_type_id_plant: location_type_id_plant, customer: customer, location_type_id_customer: location_type_id_customer, manufacturer_id: $('#location_type_manufacturer_id').val() };
            // Send the data using post
            var posting = $.post(url, { locationTypeFields });
            // Put the results in a div
            posting.done(function( data ) {
                console.log(data);
                if(data['status'])
                {
                    // loadLocations();
                }
                alert(data['message']);
                $('#import_locations_from_erp_button').attr("disabled", false);
            });
            return false;
    }).validate({
        submitHandler: function (form) {
            return false;
        }
    });
    $('#basicvalCodeModal').on('hide.bs.modal',function(){
            console.log('resetForm');
            $('#add_location_form').data('bootstrapValidator').resetForm();
            $('#add_location_form')[0].reset();
        });
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
                   // remote: {
                   //          url: '/customer/uniquevalidation',
                   //          type: 'POST',
                   //          data: function(validator, $field, value) {
                   //              return {
                   //                  table_name: 'locations', 
                   //                  field_name: 'location_name', 
                   //                  field_value: value, 
                   //                  manufacturer_id: $('#manufacturer_id').val(),
                   //                  pluck_id: 'location_id',
                   //                  skip_id:$("#add_location_type_id").val(),
                   //                  skip_column:'location_type_id'
                   //                  //skip_decode:1
                   //              };
                   //          },
                   //          delay: 2000,     // Send Ajax request every 2 seconds
                   //          message: 'Name already exists.'
                   //      },


                    regexp: {
                        regexp: /^[a-zA-Z0-9\s]+$/i,
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
            location_email: {
                    validators: {
                        notEmpty: {
                            message: 'The email is required and can\'t be empty'
                        },
                       
                        regexp: {
                        regexp: '^[^@\\s]+@([^@\\s]+\\.)+[^@\\s]+[^@\\s]+$',
                            message : 'Please enter a valid email address'
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

            },
            storage_location_type_code:{
                validators: {
                    callback: {
                            message: 'Please choose storage location',
                            callback: function(value, validator, $field) {
                                return (value != '');
                            }
                        },
                        notEmpty: {
                            message: 'Storage location cannot be empty.'
                        }
                }
            },
            business_unit_id:{
                validators: {
                    callback: {
                            message: 'Please choose business unit',
                            callback: function(value, validator, $field) {
                                return (value != '');
                            }
                        },
                        notEmpty: {
                            message: 'Business unit cannot be empty.'
                        }
                }
            },
             erp_code:{
                    validators:{
                         notEmpty: {
                            message: 'The ERP Code  is required and can\'t be empty'
                        },

                    remote: {
                            url: '/customer/erpuniquevalidation',
                            type: 'POST',
                            data: function(validator, $field, value) {
                                return {
                                    table_name: 'locations', 
                                    field_name: 'erp_code', 
                                    erp_code: value, 
                                    manufacturer_id: $('#manufacturer_id').val(),
                                    pluck_id: 'erp_code',
                                    skip_id:$("#add_location_type_id").val(),
                                    skip_column:'location_type_id',
                                    request_type:'create'
                                    //skip_decode:1
                                };
                            },
                            delay: 2000,     // Send Ajax request every 2 seconds
                            message: 'ERP Code already exists.'
                        }
                       
                    }
                }
            }
        }).on('success.form.bv', function(event) {
            //alert($('#add_location_form').length);
            ajaxCallPopup($('#add_location_form'));
            console.log('we are before loadLocations');
            loadLocations();
            console.log('we are after loadLocations');
            return false;
        }).validate({
        submitHandler: function (form) {
            return false;
        }
    });

$('#basicvalCodeModal1').on('hide.bs.modal',function(){
            console.log('resetForm');
            $('#edit_location_form').data('bootstrapValidator').resetForm();
            $('#edit_location_form')[0].reset();
        });
        $('#edit_location_form').bootstrapValidator({
    //        live: 'disabled',
            message: 'This value is not valid',
            feedbackIcons: {
                valid: 'glyphicon glyphicon-ok',
                invalid: 'glyphicon glyphicon-remove',
                validating: 'glyphicon glyphicon-refresh'
            },
            fields: {
                erp_code: {
                    validators:{


                    remote: {
                            url: '/customer/erpuniquevalidation',
                            type: 'POST',
                            data: function(validator, $field, value) {
                                return {
                                    table_name: 'locations', 
                                    field_name: 'erp_code', 
                                    erp_code: value, 
                                    manufacturer_id: $('#manufacturer_id').val(),
                                    pluck_id: 'erp_code',
                                    skip_id:$("#add_location_type_id").val(),
                                    skip_column:'location_type_id',
                                    //skip_decode:1
                                    request_type:'edit',
                                    location_id: $('#edit_location_form [name="location_id"]').val()
                                };
                            },
                            delay: 2000,     // Send Ajax request every 2 seconds
                            message: 'ERP Code already exists.'
                        },
                        notEmpty: {
                            message: 'The ERP Code  is required and can\'t be empty'
                        }
                    }
                },
                location_name: {
                    validators: {
                        //   remote: {
                        //     url: '/customer/uniquevalidation',
                        //     type: 'POST',
                        //     data: function(validator, $field, value) {
                        //         return {
                        //             table_name: 'locations', 
                        //             field_name: 'location_name', 
                        //             field_value: value, 
                        //             manufacturer_id: $('#manufacturer_id').val(),
                        //             pluck_id: 'location_id',
                        //             check_id:'location_type_id',
                        //             check_val: $('#edit_location_form [name="location_type_id"]').val(),
                        //             row_id: $('#edit_location_form [name="location_id"]').val() 
                        //         };
                        //     },
                        //     delay: 2000,     // Send Ajax request every 2 seconds
                        //     message: 'Name already exists.'
                        // },
                    regexp: {
                        regexp: /^[a-zA-Z0-9\s]+$/i,
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

                location_email: {
                    validators: {
                        notEmpty: {
                            message: 'The email is required and can\'t be empty'
                        },
                           regexp: {
                            regexp: '^[^@\\s]+@([^@\\s]+\\.)+[^@\\s]+[^@\\s]+$',
                            message : 'Please enter a valid email address'
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

            },
            storage_location_type_code:{
                validators: {
                    callback: {
                            message: 'Please choose storage location',
                            callback: function(value, validator, $field) {
                                return (value != '');
                            }
                        },
                        notEmpty: {
                            message: 'Storage location cannot be empty.'
                        }
                }
            },
            business_unit_id:{
                validators: {
                    callback: {
                            message: 'Please choose business unit',
                            callback: function(value, validator, $field) {
                                return (value != '');
                            }
                        },
                        notEmpty: {
                            message: 'Business unit cannot be empty.'
                        }
                }
            }
            }

        }).on('success.form.bv', function(event) {
            event.preventDefault();
            ajaxCallPopup($('#edit_location_form'));
            console.log('we are before loadLocations');
            //loadLocations();
            console.log('we are after loadLocations');
            return false;
            }).validate({
        submitHandler: function (form) {
            return false;
        }
        });
        $('#add_transactiontypes_form').bootstrapValidator({
    //        live: 'disabled',
            message: 'This value is not valid',
            feedbackIcons: {
                valid: 'glyphicon glyphicon-ok',
                invalid: 'glyphicon glyphicon-remove',
                validating: 'glyphicon glyphicon-refresh'
            },
            fields: {
                name: {
                    validators: {
                        notEmpty: {
                            message: 'The name is required and can\'t be empty'
                        },
                        remote: {
                            url: '/customer/uniquevalidation',
                            type: 'POST',
                            data: {
                                table_name: 'transaction_master', 
                                field_name: 'name', 
                                field_value: $('#add_transactiontypes_form #name').val(), 
                                manufacturer_id: $('[name="customer_id"]').val() 
                            },
                            delay: 2000,     // Send Ajax request every 2 seconds
                            message: 'Name already exists.'
                        }
                    }
                },
                action_code: {
                    validators: {
                        notEmpty: {
                            message: 'The action code is required and can\'t be empty'
                        },
                        remote: {
                            url: '/customer/uniquevalidation',
                            type: 'POST',
                            data: {table_name: 'transaction_master', field_name: 'action_code', field_value: $('#add_transactiontypes_form #action_code').val(), manufacturer_id: $('[name="customer_id"]').val() },
                            delay: 2000,     // Send Ajax request every 2 seconds
                            message: 'Action code already exists.'
                        }
                    }                
                },
                feature_code: {
                    validators: {
                        notEmpty: {
                            message: 'The feature code is required and can\'t be empty'
                        },
                        remote: {
                            url: '/customer/uniquevalidation',
                            type: 'POST',
                            data: {table_name: 'transaction_master', field_name: 'feature_code', field_value: $('#add_transactiontypes_form #feature_code').val(), manufacturer_id: $('[name="customer_id"]').val() },
                            delay: 2000,     // Send Ajax request every 2 seconds
                            message: 'Feature code already exists.'
                        }                        
                    }
                }
            }
        }).on('success.form.bv', function(event) {
            event.preventDefault();
            ajaxCallPopup($('#add_transactiontypes_form'));
            $('#add_transactiontypes_form').bootstrapValidator('resetForm',true);
            return true;

            //return true;
        }).validate({
        submitHandler: function (form) {
            return false;
        }
    });
        
        $('#edit_transactiontypes_form').bootstrapValidator({
    //        live: 'disabled',
            message: 'This value is not valid',
            feedbackIcons: {
                valid: 'glyphicon glyphicon-ok',
                invalid: 'glyphicon glyphicon-remove',
                validating: 'glyphicon glyphicon-refresh'
            },
            fields: {
                name: {
                    validators: {
                        notEmpty: {
                            message: 'The name is required and can\'t be empty'
                        },
                        remote: {
                            url: '/customer/uniquevalidation',
                            type: 'POST',
                            data: function(validator, $field, value) {
                                return {
                                    table_name: 'transaction_master', 
                                    field_name: 'name', 
                                    field_value: value, 
                                    manufacturer_id: $('[name="customer_id"]').val() ,
                                    row_id: $('#edit_transactiontypes_form [name="id"]').val() 
                                };
                            },
                            delay: 2000,     // Send Ajax request every 2 seconds
                            message: 'Name already exists.'
                        }
                    }
                },
                action_code: {
                    validators: {
                        notEmpty: {
                            message: 'The action code is required and can\'t be empty'
                        }
                    }                
                },
                feature_code: {
                    validators: {
                        notEmpty: {
                            message: 'The feature code is required and can\'t be empty'
                        }                     
                    }
                }
            }
        }).on('success.form.bv', function(event) {
            ajaxCallPopup($('#edit_transactiontypes_form'));
            $('#edit_transactiontypes_form').bootstrapValidator('resetForm',true); 
        //     $.post($(this).attr('action'),$(this).serialize(),function(response){
        //     //var res_arr = response.split('|');
        //         //$("#img").show();
        //         alert(response);
        //         var data = $.parseJSON(res_arr[1])
        //         if(res_arr[0]=='success')
        //         {
        //             // $("#popupLoader").hide();    
        //             // $("#userDiv").show();
        //             // $(".close").click();
        //           //$("#img").show();
        //            //alert("User Created successfully.");
        //      location.reload();
        //         }else{
        //            $("#erroMsg").html(Str);
        //         }    
        // });
        location.reload();
            return true;

        }).validate({
        submitHandler: function (form) {
            return false;
        }
    });
        
        $('#add_products_form_erp').bootstrapValidator({
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
                }
            },
            onSuccess: function(e, data) {
                console.log('data');
                console.log(data);
                e.preventDefault();
                $('#add_product_erp_button').prop('disabled', true);
                url = $('#add_products_form_erp').attr('action');
                var formData = new FormData($('#add_products_form_erp')[0]);
                console.log(formData);
                $.ajax({
                    url: url,
                    type: 'POST',
                    data: formData,
                    async: false,
                    success: function (data) {                    
                        if(data['status'])
                        {
                            $('.close').trigger('click');
                        }
                        alert(data['message']);
                    },
                    cache: false,
                    contentType: false,
                    processData: false
                });
                $('#add_product_erp_button').prop('disabled', false);
            }
        });
        
        datePicket();
        //$('.tab-pane.active').removeClass('active');
        $('#basic').addClass('active');
        $("#componentLookupIds").trigger("change");
        $("#region_country").trigger("change");
        $("#country").trigger("change");
        /*$("#location_country_id").val(99).trigger('change');*/
        $('[id="location_country_id"]').val(99).trigger('change');
        $("#edit_location_country_id").val(99).trigger('change');
        $('.checkbox :input').first().prop('checked', true);
        updatePriceManagement();
//        loadProducts();
        loadTransactions();
    });
    // $('.tab-pane').each(function (i, t) {
    //     $('#myTabs li').removeClass('active');
    //     $(this).addClass('active');
    // });
    $("#componentLookupIds").change(function () {
        var componentData = $(this).val();
        if ( componentData )
        {
            var componentArray = componentData.split('#');
            var price = componentArray[1];
            var id = componentArray[2];
            var priceTo = componentArray[3];
            $('#agreed_price').val(price);
            $('#eseal_price_master_id').val(id);
            $('#fixed_price').val(price); 
            //$('#price_to').val(priceTo);
        }
    });
    $('#customer_type_id').change(function(event){
        $selected = $(this);
        if($selected.val() == 1005)
        {
            $('#parent_company_id').val('-1');
            $('#parent_company_id').prop('disabled', true);
            $('#parent_company_id').selectpicker('refresh');
            $('<input/>').attr('type', 'hidden').attr('name', $('#parent_company_id').attr('name')).val('-1').appendTo($('#parent_company_id').parent());
        }else{
            $('#parent_company_id').prop('disabled', false);
            $('input[name="eseal_customers[parent_company_id]"').remove();
            $('#parent_company_id').selectpicker('refresh');
        }
    });
    $("#continue").click(function () {
        if(validateTab(0))
        {
            console.log('validated and need tochange tab');
            changeTab('#eseal_products');
        }
    });
    $('#customer_onboard_update').validate({
        submitHandler: function (form) {
            if($('#customer_onboard_update').data('formValidation') && checkEmail())
            {
                form.submit();
            }
        }
    });
    
   /* $('#add_transactiontypes_form').validate({
        submitHandler: function (form) {
            if($('#add_transactiontypes_form').data('bootstrapValidator').isValid())
            {
                //$('#add_transactiontypes_form').data('bootstrapValidator').defaultSubmit();
                var $form = $(form);
                ajaxCallPopup($form);
            }
        }
    });*/
    $("#continue2").click(function () {
        updatePriceManagement();
        changeTab('#price_management');       
    });
    $("#back").click(function () {
        changeTab('#basic');
    });
    $("#back2").click(function () {
        changeTab('#eseal_products');
    });
    $("#continue3").click(function () {
        changeTab('#tax_class');       
    });
    $("#back3").click(function () {
        changeTab('#price_management');
    });
    
    function validateTab(index) {
        var fv   = $('#customer_onboard_update').data('formValidation'), // FormValidation instance
            // The current tab
            $tab = $('#customer_onboard_update').find('.tab-pane').eq(index);
        // Validate the container
        fv.validateContainer($tab);
        var isValidStep = fv.isValidContainer($tab);
        if (isValidStep === false || isValidStep === null) {
            if(index == 1)
            {
                return fv.validateField('type').isValid();
            }
            // Do not jump to the target tab
            return false;
        }else if(index == 0 && isValidStep == true){
            console.log('call validate image');
            var temp = validateImage();
            console.log(temp);
            return temp;
        }
        return true;
    }
    
    function validateImage()
    {
        console.log('validate image');
        var fileName = $('#files').children().find('input');
        var customerLogo = $('#customer_log').val();
        if(customerLogo == '')
        {
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
        }else{
            $('#upload_field').children('div.col-sm-10').children('span').children('i.form-control-feedback').removeClass('glyphicon-remove').addClass('glyphicon-ok');
            $('#upload_field').removeClass('has-error');
            $('#upload_field').children('div.col-sm-10').children('small').hide();
            return true;
        }
    }
    
    function changeTab(tabName)
    {
        console.log('change tab => '+tabName);
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

    function alertDelete()
    {   
        var del = $('#remCF').val();
        if(del = true)
        {
            alert('Are you sure you want to delete?');
        }
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
    $("#add").click(function () {
        if ( $("#componentLookupIds").has("option") )
        {
            console.log('we are in if');
            var name = $('#componentLookupIds option:selected').text();
            var agreedPrice = $('#agreed_price').val();
            var priceFrom = $('#price_from').val();
            var priceTo = $('#price_to').val();
            var format = "YYYY-MM-DD"
            if(isValidDate(priceFrom) == 0 || isValidDate(priceTo) == 0)
            {
                 alert("Invalid date format: ");                
            }else{
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
                        console.log(priceFrom+' > '+priceTo);
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
        }else{
            console.log('we are in else');
        }
    });
    $("#confirmation").on('click', '#remCF', function () {
        $(this).parent().parent().remove();
    });

    $("#country").on('change', function () {
        ajaxCall($(this).val(), 'state_options', 0);
    });
    $("#location_country_id").on('change', function () {
        $('#country_input_id').val($(this).val());
        ajaxCall($(this).val(), 'location_state_options', 0, 1);
    });
    $("#edit_location_country_id").on('change', function () {
        $('#edit_country_input_id').val($(this).val());
        ajaxCall($(this).val(), 'edit_location_state_options', 0, 1);
    });
    $("#region_country").on('change', function () {
        ajaxCall($(this).val(), 'region_state', 0);
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
    $("#region_state").on('change', function () {
        loadCities();
    });
    function loadCities()
    {
        $('#cities1').empty();
        var countryId = $("#region_country").val();
        var locationId = $('#add_region_form').find('input[name="location_id"]').val();
        var manufacturerId = $('#manufid').val();
        //var stateDescription = $("#region_state option:selected").text();
        //var stateId = $("#region_state option:selected").val();
        var stateDescription = [];
        var stateId = [];
        $('#region_state :selected').each(function(i, selected){ 
            stateDescription.push($(selected).text());
            stateId.push($(selected).val());
        });        
        var url = '/customer/getcities';
        // Send the data using post
        var posting = $.get(url, {countryId: countryId, stateId: stateId, locationId: locationId, manufacturerId: manufacturerId, stateDescription: stateDescription });
        // Put the results in a div
        posting.done(function (data) {
            var result = $.parseJSON(data); 
            if (result != 'No Data')
            {
                var container1 = $('#cities1');
                var container2 = $('#cities2');
                var temp = 1;
                var cities = result['cities'];
                var selectedOptions = result['selected'];
                if(selectedOptions != null)
                {
                    selectedOptions = selectedOptions.split(',');
                }
                $.each(cities, function(stateName, citiesList){
                    var $stateTemplate = $('#state_name'),
                    $stateClone = $stateTemplate.clone().attr('id', 'state_');
                    $stateClone.show();
                    container1.append($stateClone);
                    $stateClone.find('label').text(stateName);
                    $.each(citiesList, function(key, value){
                        var $template = $('#checkboxTemplate'),
                        $clone = $template.clone().attr('id', 'checkboxs_'+key);
                        $clone.show();
                        $clone.find('label').text(value);
                        $clone.find('label').attr('for', key);
                        $clone.find('input').attr('id', key);
                        $clone.find('input').attr('name', 'location_city_ids[]');
                        if($.inArray(key, selectedOptions) >= 0)
                        {
                            $clone.find('input').prop('checked', true);
                        }
                        if(temp == 1)
                        {                        
                            container1.append($clone);
                            //temp++;
                        }/*else if(temp ==2)
                        {
                            container2.append($clone);
                            temp = 1;
                        }*/
                    });
                });
            }
        });
    }
    function addRegion(locationId)
    {
        $('#add_region_form').find('input[name="location_id"]').val(locationId);
    }
    
    $('#add_region_form').submit(function(event){
        event.preventDefault();
        $('#add_region_button').attr("disabled", true);
        $form = $(this);
        url = $form.attr('action');
        
        var arr = [];
        $("input[name='location_city_ids[]']:checked:enabled").each(function () {
            arr.push($(this).attr('id'));
        });

        // Send the data using post
        var manufacturer_id = $('[name="customer_id"]').val();
        var location_id = $form.find('input[name="location_id"]').val();
        var state_id = $('#region_state').val();
        var cities = arr;
        var posting = $.post(url, { manufacturer_id: manufacturer_id, location_id: location_id, state_id: state_id, cities: cities });
        // Put the results in a div
        posting.done(function( data ) {
            if(data == 'Sucesss')
            {
                alert('Sucess');
                $('.close').trigger('click');
                $('region_country').val(1);
                $('region_country').trigger('click');
                $('#cities1').empty();                
            }else{
                alert('Unable to add');
            }
            $('#add_region_button').attr("disabled", false);
        });
    });
    
    $('#erp_configurations_form').submit(function(event){
        var isValid = $(this).data('formValidation').isValid();
        if(isValid)
        {            
            event.preventDefault();
            $('#erp_configurations_button').attr("disabled", true);
            $form = $(this);
            url = $form.attr('action');        
            var arr = [];

            // Send the data using post
            var manufacturer_id = $('[name="customer_id"]').val();
            var erp_model = $('[name="erp_model"]').val();
            var integration_mode = $('[name="integration_mode"]').val();
            var default_start_date = $('[name="default_start_date"]').val();
            var web_service_url = $('#web_service_url').val();
            var token = $('#token').val();
            var company_code = $('#company_code').val();
            var web_service_username = $('#web_service_username').val();
            var web_service_password = $('#web_service_password').val();
            var posting = $.post(url, { manufacturer_id: manufacturer_id, erp_model: erp_model, default_start_date: default_start_date,integration_mode: integration_mode, web_service_url: web_service_url, token: token, company_code: company_code, web_service_username: web_service_username, web_service_password: web_service_password });
            // Put the results in a div
            posting.done(function( data ) {
                var responseData = JSON.parse(data);
                if(responseData['Status'] == 1)
                {
                    alert('Sucessfully Updated');
                }else{
                    alert(responseData['Status']);
                }
                $('#erp_configurations_button').attr("disabled", false);
            });
        }
    });
    
    $('[data-toggle="tab"]').click(function () {
        if ( $('#eseal_products').attr('class') == 'tab-pane active' )
        {
            updatePriceManagement();
        }
    });
    
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
    function changeData(price_from, yyyyy)
    {
        $('#dateRangePickerTo')
            .datepicker({
                format: 'yyyy-mm-dd',
                startDate: price_from,
                endDate: yyyyy+'-12-30',
                Default: false
            })
            .on('changeDate', function(e) {
                // Revalidate the date field
                //$('#dateRangePickerTo').formValidation('revalidateField', 'date');
                $('.datepicker.datepicker-dropdown').hide();
            });
    } 

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
       // getComponentData(checkedIds);
    }

    // function getComponentData(checkedIds)
    // {
    //     url = '/customer/getcomponentdata';
    //     // Send the data using post
    //     var posting = $.get(url, {productLookupIds: checkedIds});
    //     // Put the results in a div
    //     posting.done(function (data) {
    //         responseData = JSON.parse(data);
    //         if ( data )
    //         {
    //             $('#componentLookupIds').find('option').remove();
    //             $('#componentLookupIds_chosen').find('li').remove();
    //             $.each(responseData, function (key, esealPriceData) {
    //                 $('#componentLookupIds').append('<option value="' + esealPriceData['component_type_lookup_id'] + '#' + esealPriceData['price'] + '#' + esealPriceData['id'] + '#' + esealPriceData['valid_upto'] + '"><font><font>' + esealPriceData['name'] + '</font></font></option>');
    //                 $('#componentLookupIds_chosen').find('.chosen-results').append('<li class="active-result" style="" data-option-array-index="' + key + '">' + esealPriceData['name'] + '</li>');
    //             });
    //             $('#componentLookupIds').selectpicker('refresh');
    //         }
    //     });
    // }
</script>
<!-- location Data -->
<script type="text/javascript">
  

        // $.ajax(
        //         {
        //             url: "/customer/getTreeLocation/" + customerId,
        //             success: function (result)
        //             {
        //                 var employees = result;
        //                 // prepare the data
        //                 var source =
        //                         {
        //                             datatype: "json",
        //                             datafields: [
        //                                 {name: 'location_type_name', type: 'varchar'},
        //                                 {name: 'location_name', type: 'varchar'},
        //                                 {name: 'state', type: 'varchar'},
        //                                 {name: 'region', type: 'varchar'},
        //                                 //{name: 'region', type: 'varchar'},
        //                                 {name: 'business_unit', type: 'varchar'},
        //                                 {name: 'storage_location', type: 'varchar'},
        //                                 {name: 'actions', type: 'varchar'},
        //                                 {name: 'children', type: 'array'},
        //                                 {name: 'expanded', type: 'bool'}
        //                             ],
        //                             hierarchy:
        //                                     {
        //                                         root: 'children'
        //                                     },
        //                             id: 'location_id',
        //                             localData: employees,
        //                             pager: function (pagenum, pagesize, oldpagenum) {
        //                 // callback called when a page or page size is changed.
        //             }
        //                         };

        //                 var dataAdapter = new $.jqx.dataAdapter(source);

        //                 $("#locations_treegrid").jqxTreeGrid(
        //                         {
        //                             width: "100%",
        //                             source: dataAdapter,
        //                             //sortable: true,
        //                             sortable: true,
        //                             pageable: true,
        //                             autoheight: true,
        //                             autoloadstate: false,
        //                             autosavestate: false,
        //                             columnsresize: true,
        //                             columnsreorder: true,
        //                             showfilterrow: true,
        //                             filterable: true,
        //                             columns: [
        //                                 {text: 'Location Type Name', datafield: 'location_type_name', width: "30%"},
        //                                 {text: 'Location Name', datafield: 'location_name', width: "20%"},
        //                                 {text: 'State', datafield: 'state', width: "20%"},
        //                                 {text: 'Business Unit', datafield: 'business_unit', width:"10%"},
        //                                 {text: 'Storage Location', datafield: 'storage_location', width:"10%"},
        //                                 {text: 'Actions', datafield: 'actions', width: "10%"}
        //                             ]
        //                         });

        //             }

        //         });


   

   
function getLocName(locationTypeId)
{
    $('#add_location_type_id').val(locationTypeId);
}
function getSubLoc(locationTypeId,location_id)
{
    $('#add_location_type_id').val(locationTypeId);
    $('#parent_location_id').val(location_id);

}
function getLocationTypeName(locationTypeId)
{
    $('#edit_locationtypes_form #location_type_id').val(locationTypeId);

}
function getLocationName(locationTypeId)
{
    $('#edit_location_form #location_id').val(locationTypeId);
    $('#update_location_type_id').trigger('change');
}
$('#update_location_type_id').change(function(){
    $('#edit_parent_location_id').empty();
    $('#edit_parent_location_id').append('<option value="0">None</option>');
    var locationTypeId = $(this).val();
    url = '/customer/getlocationsbytype';
    var manufacturerId = $('#edit_location_form #manufacturer_id').val();
    var locationId = $('#edit_location_form #location_id').val();
    // Send the data using post
    var posting = $.get( url, { manufacturer_id: manufacturerId, location_type_id: locationTypeId, location_id: locationId } );
    // Put the results in a div
    posting.done(function( data ) {
      var result = JSON.parse(data);      
      $.each(result, function(key, value){
          $('#edit_parent_location_id').append('<option value="' + value['location_id'] + '">' + value['location_name'] + '</option>');
      });
    });
});
$("div .modal-footer .btn-primary").on("click", function(e) {
        //console.log("button pressed");   // just as an example...
        $('.mask, .loader').show();
        updateCustomerApproval();
        $("#customer_approval").modal('hide');     // dismiss the dialog
        $('.mask, .loader').hide();
    });
    $("div .modal-footer .btn-danger").on("click", function(e) {
        //console.log("danger button pressed");   // just as an example...
        $("#customer_approval").modal('hide');     // dismiss the dialog
    });
    //$('img[data-target="#customer_approval"]').click(alert('we are here'));

    $("#customer_approval").on("hide", function() {    // remove the event listeners when the dialog is dismissed
        $("#customer_approval a.btn").off("click");
    });    
    
    function updateCustomerApproval()
    {
        url = $('#approvecustomer').attr('data-href');
        var customer_id = url.split("/").pop();
        // Send the data using post
        var posting = $.post( url, { customer_id: customer_id } );
        // Put the results in a div
        posting.done(function( data ) {
          var result = JSON.parse(data);
          if(result['result'] == 1)
          {
              alert('Customer Approved');          
              location.reload();
          }else{
              alert(result['message']);
              return 0;
          }
        });
    }
</script>  
<!-- location data end -->
<!-- transaction Data -->
<script type="text/javascript">

function loadTransactions()
    {
        var customerId = $('[name="customer_id"]').val();
        var url = "/transaction/gettransaction/"+customerId;
        // prepare the data
        var source =
                {
                    datatype: "json",
                    datafields: [
                        {name: 'name', type: 'string'},
                        {name: 'action_code', type: 'string'},
                        {name: 'feature_code', type: 'integer'},
                        {name: 'actions', type: 'string'}
                    ],
                    id: 'id',
                    url: url,
                    pager: function (pagenum, pagesize, oldpagenum) {
                        // callback called when a page or page size is changed.
                    }
                };
        var dataAdapter = new $.jqx.dataAdapter(source);
        $("#transaction_grid").jqxGrid(
                {
                    width: '100%',
                    source: source,
                    selectionmode: 'multiplerowsextended',
                    sortable: true,
                    pageable: true,
                    autoheight: true,
                    autoloadstate: false,
                    autosavestate: false,
                    columnsresize: true,
                    columnsreorder: true,
                    showfilterrow: true,
                    filterable: true,
                    columns: [
                        {text: 'Name', datafield: 'name', width: '50%'},
                        {text: 'Action Code', datafield: 'action_code', width: '20%'},
                        {text: 'Feature Code', datafield: 'feature_code', width: '20%'},
                        {text: 'Actions', filterable: false, datafield: 'actions', width: '10%'}
                    ]
                });
        makePopupAjax($('#TransactionAddModal'));
        makePopupEditAjax($('#TransactionEditModal'), 'id');
    }

    function deleteTransaction(id)
    {
        var dec = confirm("Are you sure you want to Delete ?"), self = $(this);
        if ( dec == true ) {
            $.ajax({
                data: '',
                type: 'GET',
                datatype: "JSON",
                url: '/transaction/deletetransaction/' + id,
                success: function (resp) {
                    if ( resp.message )
                        alert(resp.message);
                    if ( resp.status == true )
                        self.parents('td').remove(); 
                    location.reload();
                    
                },
                error: function (error) {
                    console.log(error.responseText);
                },
                complete: function () {

                }
            });
        }
    }    
    
    $('#add_products_form_excel').submit(function(event){
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
                $('#add_products_form_excel')[0].reset();
                $('.close').trigger('click');
            },
            cache: false,
            contentType: false,
            processData: false
        });
        $('#add_product_excel_button').prop('disabled', false);
    });


    

    $('#add_locationtypes_form_excel').submit(function(event){
        event.preventDefault();
        $('#add_locationtypes_excel_button').prop('disabled', true);
        $form = $(this);
        url = $form.attr('action');
        var formData = new FormData($(this)[0]);
        var location_type_name = $('[name="location_type_name"]').val();
        var locationTypeFields = {location_type_name: location_type_name , manufacturer_id: $('#add_locationtypes_form_excel #manufacturer_id').val() };
            // Send the data using post
        /*var posting = $.post(url, { location_type: locationTypeFields });*/
        $.ajax({
            url: url,
            type: 'POST',
            data: formData,
            async: false,
            success: function (data) {
                $('#update_import_locations_message').text(data.msg);
                alert(data.msg);         
                if(data.msg.indexOf('Successfully')>=0)
                    $('.close').trigger('click');
                var optionData = '';

                location_type_names = data.loctpname;
                $.each(data.newItems,function(i,v){
                    console.log(v);
                    console.log('item added');
                    location_type_id = v.location_type_id;
                    location_type_name = location_type_names[i].location_type_names;
                    console.log(location_type_names[i].location_type_name);
                    optionData += '<option value="' + location_type_id + '">'+location_type_name +'</option>';
                    console.log(optionData);
                });

                $('#location_type_id_vendor').append(optionData);
                $('#add_location_type_id').append(optionData);
                $('#update_location_type_id').append(optionData);
                $('#location_type_id_plant').append(optionData);
                $('#location_type_id_customer').append(optionData);
                
                $('[name="location_type_name"]').val('');
                /*posting.done(function( data ) {
                    if(data.status == true)
                    {
                        var location_type_id = data.location_type_id;
                        var optionData = '<option value="' + location_type_id + '">'+location_type_name +'</option>';
                        $('#location_type_id_vendor').append(optionData);
                        $('#add_location_type_id').append(optionData);
                        $('#update_location_type_id').append(optionData);
                        $('#location_type_id_plant').append(optionData);
                        $('#location_type_id_customer').append(optionData);
                        alert('Location type created.');
                        $('.close').trigger('click');
                        $('[name="location_type_name"]').val('');
                        //$('#location_type_manufacturer_id').val('');
                        //loadLocations();
                    }
                    $('#add_locationtypes_excel_button').attr("disabled", false);
                });*/
            },
            cache: false,
            contentType: false,
            processData: false
        });
        
        $form.bootstrapValidator('resetForm',true); 
        loadLocations();
        }).validate({
        submitHandler: function (form) {
        return false;
        }   
    });

    $('#location_types_add_excel').on('show.bs.modal',function(){
        console.log('reset: #add_locationtypes_form_excel');
        $('#add_locationtypes_form_excel')[0].reset();
    });
    $('#locations_fileupload').click(function(){
        $('#update_import_locations_message').text('');
    });
    $('#product_fileupload').click(function(){
        $('#update_import_product_message').text('');
    });
function postData(data)
{
    console.log('we are in view');    
    return;
}

</script>
<!-- transaction data end -->
@stop
@extends('layouts.footer')