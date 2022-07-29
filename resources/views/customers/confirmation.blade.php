@extends('layouts.default')
@extends('layouts.header')
@extends('layouts.sideview')
@section('content')
<!-- Preloader -->
<div class="mask">
  <div id="loader"></div>
</div>
<!--/Preloader -->
<!-- show code btn -->
<button class="btn show-code" data-toggle="modal" data-target="#wizardCodeModal" style="display: none;">
    show code
</button>
<!-- /show code btn -->

<!-- Modal -->
<div class="modal fade" id="wizardCodeModal" tabindex="-1" role="dialog" aria-labelledby="wizardCode" aria-hidden="true" style="display: none;">
    <div class="modal-dialog wide">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h4 class="modal-title" id="wizardCode">Custom OTP Confirmation</h4>
            </div>
            <div class="modal-body">
                <form name="otpValidation" action="/customer/validateotp" method="post" id="otp_form">
                    <div>
                        <div class="alert alert-success" role="alert" style="display: none;" id="otp_message_sucess"></div>
                        <div class="alert alert-danger" role="alert" style="display: none;" id="otp_message_error"></div>
                    </div>
                    <div class="row">
                        <div class="form-group col-sm-12">
                            <label for="OTP">Please enter OTP</label>
                            <div class="form-group ">
                                <input type="text" class="form-control required" placeholder="OTP" name="otp" value="" data-bind="value:replyNumber">
                                <input type="hidden" name="customer_id" value="{{ $customerData->customer_id }}" id="customer_id_data" readonly="true" />                                
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <center>
                            <button type="submit" class="btn btn-primary">Submit</button>
                            <button type="button" id="resend_otp" class="btn btn-primary">Resend OTP</button>
                        </center>
                    </div>
                </form>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<div class="main">
    <!-- row -->
    <div class="row">
        <!-- col 6 -->
            <section class="tile">
                <!-- tile header -->
                <div class="tile-header">
                    <h1><strong>Customer</strong> Confirmation Form</h1>
                </div>
                <!-- /tile header -->

                <!-- tile body -->
                <div class="tile-body">
                    {{ Form::open(array('url' => 'customer/confirmcustomer', 'method' => 'POST', 'files'=>true, 'id' => 'confirmcustomer_form')) }}
                    <div class="row">
                        <div class="form-group col-sm-6">
                            <label class="col-sm-2 control-label" for="Organization Name">Organization Name*</label>
                            <div class="col-sm-10">
                                <div class="input-group ">
                                <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
                                <input type="text" class="form-control" placeholder="Organization Name" name="eseal_customers[brand_name]" value="{{ $customerData->brand_name }}" readonly="true">
                                <input type="hidden" name="eseal_customers[customer_id]" value="{{ $customerData->customer_id }}" readonly="true">
                                <input type="hidden" id="is_otp_approved" name="is_otp_approved" value="{{ $customerData->is_otp_approved }}" readonly="true">
                                <input type="hidden" name="token" value="{{ $customerData->token }}" id="customer_id_data" readonly="true" />
                            </div>
                            </div>                        
                        </div>
                    </div>

                    <!-- tile header -->
                <div class="tile-header">
                    <h1><strong>Primary</strong> Information</h1>
                </div>
                <!-- /tile header -->

                    <div class="row">
                        <div class="form-group col-sm-6">
                            <label class="col-sm-2 control-label" for="Address1">Address1*</label>
                            <div class="col-sm-10">
                            <div class="input-group input-group-sm">
                                <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
                                <input type="text"  required class="form-control" placeholder="Address1" name="customer_address[address_1]" value="{{ $customerAddress->address_1 }}">
                                <input type="hidden" name="customer_address[address_id]" value="{{ $customerAddress->address_id }}">
                            </div>
                            </div>                        
                        </div>
                        <div class="form-group col-sm-6">
                            <label class="col-sm-2 control-label" for="Address1">Address2</label>
                            <div class="col-sm-10">
                            <div class="input-group input-group-sm">
                                <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
                                <input type="text" class="form-control" placeholder="Address2" name="customer_address[address_2]" value="{{ $customerAddress->address_2 }}">
                            </div>                        
                        </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col-sm-6">
                            <label class="col-sm-2 control-label" for="City">City*</label>
                            <div class="col-sm-10">
                            <div class="input-group input-group-sm">
                                <span class="input-group-addon addon-red"><i class="fa fa-newspaper-o"></i></span>
                                <input type="text" required class="form-control" placeholder="City" name="customer_address[city]" value="{{ $customerAddress->city }}">
                            </div>
                            </div>
                        </div>                        
                        <div class="form-group col-sm-6">
                            <label class="col-sm-2 control-label" for="State">State*</label>
                            <div class="col-sm-10">
                            <div class="input-group input-group-sm">
                                <span class="input-group-addon addon-red"><i class="fa fa-newspaper-o"></i></span>
                                <select name="customer_address[zone_id]" id="state_options" class="chosen-select form-control parsley-validated" parsley-trigger="change" parsley-required="true" parsley-error-container="#selectbox">
                                    @foreach ($formData['states'] as $key => $value)
                                        @if($key == $customerAddress->zone_id)
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
                        <div class="form-group col-sm-6">
                            <label class="col-sm-2 control-label" for="BusinessType">Country*</label>
                            <div class="col-sm-10">
                            <div class="input-group input-group-sm">
                                <span class="input-group-addon addon-red"><i class="fa fa-newspaper-o"></i></span>
                                <div id="selectbox">
                                    <select class="chosen-select form-control parsley-validated" name="customer_address[country_id]" id="country" parsley-trigger="change" parsley-required="true" parsley-error-container="#selectbox">
                                        @foreach ($formData['countries'] as $key => $value)
                                        @if($key == $customerAddress->country_id)
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
                            <label class="col-sm-2 control-label" for="Zipcode">Zip code*</label>
                            <div class="col-sm-10">
                            <div class="input-group input-group-sm">
                                <span class="input-group-addon addon-red"><i class="fa fa-newspaper-o"></i></span>
                                <input type="text" required class="form-control" placeholder="Zipcode" name="customer_address[postcode]" value="{{ $customerAddress->postcode }}">
                            </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col-sm-6">
                            <label class="col-sm-2 control-label" for="Primary Contact">Primary Contact</label>
                            <div class="col-sm-10">
                            <div class="input-group  input-group-sm">
                                <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
                                <input type="text" class="form-control" placeholder="Primary Contact" name="eseal_customers[firstname]" value="{{ $customerData->firstname }} {{ $customerData->lastname }}">
                            </div>
                            </div>
                        </div>
                        <div class="form-group col-sm-6">
                            <label class="col-sm-2 control-label" for="Designation">Designation</label>
                            <div class="col-sm-10">
                            <div class="input-group  input-group-sm">
                                <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
                                <input type="text" class="form-control" placeholder="Designation" name="eseal_customers[designation]" value="{{ $customerData->designation }}">
                            </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="form-group col-sm-6">
                            <label class="col-sm-2 control-label" for="Email">Email address</label>
                            <div class="col-sm-10">
                            <div class="input-group  input-group-sm">
                                <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
                                <input type="text" class="form-control" placeholder="Email" name="eseal_customers[email]" value="{{ $customerData->email }}" readonly>
                            </div>
                          </div>
                        </div>
                        <div class="form-group col-sm-6">
                            <label class="col-sm-2 control-label" for="Phone">Phone</label>
                            <div class="col-sm-10">
                            <div class="input-group  input-group-sm">
                                <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
                                <input type="text" class="form-control" placeholder="Phone" name="eseal_customers[phone]" value="{{ $customerData->phone }}">
                            </div>
                            </div>
                        </div>
                    </div>
                
                    <div class="row">
                        <div class="form-group col-sm-6">
                            <label class="col-sm-2 control-label" for="Username">Username*</label>
                            <div class="col-sm-10">
                            <div class="input-group  input-group-sm">
                                <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
                                <input type="text" required class="form-control" placeholder="Username" name="eseal_customers[username]" value="">
                            </div>                 
                            </div>       
                        </div>
                    </div>

                    <div class="row">
                        <div class="form-group col-sm-6">
                            <label class="col-sm-2 control-label" for="Password">Password*</label>
                            <div class="col-sm-10">
                            <div class="input-group  input-group-sm">
                                <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
                                <input type="password" required class="form-control" placeholder="Password" name="eseal_customers[password]" value="">
                            </div>                 
                            </div>       
                        </div>
                        <div class="form-group col-sm-6">
                            <label class="col-sm-2 control-label" for="Confirm Password">Confirm Password*</label>
                            <div class="col-sm-10">
                            <div class="input-group  input-group-sm">
                                <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
                                <input type="password" required class="form-control" name="confirmPassword" placeholder="Confirm Password" name="" value="">
                            </div>                 
                            </div>       
                        </div>
                    </div>

                        <!-- tile header -->
                <div class="tile-header">
                    <h1><strong>Other</strong> Information</h1>
                </div>
                <!-- /tile header -->

                    <div class="row">
                        <div class="form-group col-sm-6">
                            <label class="col-sm-2 control-label" for="CINnumber">CIN NO*</label>
                            <div class="col-sm-10">
                            <div class="input-group  input-group-sm">
                                <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
                                <input type="text" pattern="^[a-zA-Z0-9 ]+$" required class="form-control" placeholder="CIN Number" name="eseal_customers[cin_number]" value="">
                            </div>
                          </div>                        
                        </div>
                        <div class="form-group col-sm-6">
                            <label class="col-sm-2 control-label" for="PANnumber">PAN NO*</label>
                            <div class="col-sm-10">
                            <div class="input-group  input-group-sm">
                                <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
                                <input type="text" pattern="^[a-zA-Z0-9 ]+$" required class="form-control" placeholder="PAN Number" name="eseal_customers[pan_number]" value="">
                            </div>
                            </div>                        
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col-sm-6">
                            <label class="col-sm-2 control-label" for="TANnumber">TAN NO*</label>
                            <div class="col-sm-10">
                            <div class="input-group  input-group-sm">
                                <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
                                <input type="text" pattern="^[a-zA-Z0-9 ]+$" required class="form-control" placeholder="TAN Number" name="eseal_customers[tan_number]" value="">
                            </div>
                            </div>                        
                        </div>
                        <div class="form-group col-sm-6">
                            <label class="col-sm-2 control-label" for="TIN Number">TIN NO*</label>
                            <div class="col-sm-10">
                            <div class="input-group  input-group-sm">
                                <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
                                <input type="text" pattern="^[a-zA-Z0-9 ]+$" required class="form-control" placeholder="TIN Number" name="eseal_customers[tin_number]" value="">
                            </div>
                            </div>                
                        </div>
                    </div>
                    

                    <div class="row">
                        <div class="form-group col-sm-6">
                            <div class="checkbox">
                                <input type="checkbox" required value="" id="terms" name="terms" parsley-group="mygroup" parsley-trigger="change" parsley-required="true" parsley-mincheck="2" parsley-error-container="#myproperlabel .last" class="parsley-validated">
                                <label for="terms" name="terms">I hereby agree to the terms and conditions*</label>
                            </div>                        
                        </div>
                    </div>

                    <div class="row">
                        <div class="form-group form-footer">
                            <div class="col-sm-offset-5 col-sm-3">
                                <button class="btn btn-primary" type="submit">Submit</button>
                            </div>                            
                        </div>
                    </div>
                    {{ Form::close() }}
                </div>
                <!-- /tile body -->
            </section>
    </div>
</div>
@stop 
@section('script')
<script type="text/javascript">
    $('document').ready(function () {
        $('#confirmcustomer_form').bootstrapValidator({
    //        live: 'disabled',
            message: 'This value is not valid',
            feedbackIcons: {
                valid: 'glyphicon glyphicon-ok',
                invalid: 'glyphicon glyphicon-remove',
                validating: 'glyphicon glyphicon-refresh'
            },
            fields: {
                'customer_address[address_1]': {
                    validators: {
                        notEmpty: {
                            message: 'This field is required and can\'t be empty'
                        }  
                    }
                },
                'customer_address[city]': {
                    validators: {
                        notEmpty: {
                            message: 'This field is required and can\'t be empty'
                        }  
                    }
                },
                'customer_address[zone_id]': {
                    validators: {
                        callback: {
                            message: 'Please choose state',
                            callback: function(value, validator, $field) {
                                return (value != 0);
                            }
                        },
                        notEmpty: {
                            message: 'This field is required and can\'t be empty'
                        }  
                    }
                },
                'customer_address[country_id]': {
                    validators: {
                        callback: {
                            message: 'Please choose country',
                            callback: function(value, validator, $field) {
                                return (value != 0);
                            }
                        },
                        notEmpty: {
                            message: 'This field is required and can\'t be empty'
                        }  
                    }
                },
                'eseal_customers[username]': {
                    validators: {
                        notEmpty: {
                            message: 'This field is required and can\'t be empty'
                        }  
                    }
                },
                'eseal_customers[email]':{
                    validators: {
                        notEmpty: {
                            message: 'Email is required and can\'t be empty'
                        },
                        regexp: {
                        regexp: '^[^@\\s]+@([^@\\s]+\\.)+[^@\\s]+[^@\\s]+$',
                            message : 'Please enter a valid email address'
                        }

                    }
                },
                'eseal_customers[password]': {
                    validators: {
                        notEmpty: {
                            message: 'This field is required and can\'t be empty'
                        },
                        identical: {
                            field: 'confirmPassword',
                            message: 'The password and its confirm are not the same'
                        }
                    }
                },
                confirmPassword: {
                    validators: {
                        notEmpty: {
                            message: 'This field is required and can\'t be empty'
                        },
                        identical: {
                            field: 'eseal_customers[password]',
                            message: 'The password and its confirm are not the same'
                        }
                    }
                },
                'eseal_customers[cin_number]': {
                    validators: {
                        notEmpty: {
                            message: 'This field is required and can\'t be empty'
                        }  
                    }
                },
                'eseal_customers[pan_number]': {
                    validators: {
                        notEmpty: {
                            message: 'This field is required and can\'t be empty'
                        }  
                    }
                },
                'eseal_customers[tan_number]': {
                    validators: {
                        notEmpty: {
                            message: 'This field is required and can\'t be empty'
                        }  
                    }
                },
                'eseal_customers[tin_number]': {
                    validators: {
                        notEmpty: {
                            message: 'This field is required and can\'t be empty'
                        }  
                    }
                },
                terms: {
                    validators: {
                        notEmpty: {
                            message: 'This field is required and can\'t be empty'
                        }  
                    }
                }
            }
        });
        $('#otp_form').bootstrapValidator({
    //        live: 'disabled',
            message: 'This value is not valid',
            feedbackIcons: {
                valid: 'glyphicon glyphicon-ok',
                invalid: 'glyphicon glyphicon-remove',
                validating: 'glyphicon glyphicon-refresh'
            },
            fields: {
                otp: {
                    validators: {
                        integer: {
                            message: 'This field only needed numbers.'
                        },
                        notEmpty: {
                            message: 'This field is required and can\'t be empty'
                        }
                    }
                }
            }
        }).on('success.form.bv', function(event) {
            event.preventDefault();
            $('.alert.alert-success').hide();
            var $form = $( this );
            otp = $form.find( "input[name='otp']" ).val();
            customer_id = $form.find( "input[name='customer_id']" ).val();
            url = $form.attr( "action" );

            // Send the data using post
            var posting = $.post( url, { otp: otp, customer_id: customer_id } );

            // Put the results in a div
            posting.done(function( data ) {
              var result = JSON.parse(data);
              $.each(result, function(k, v) {
                    if(k == 'result' && v == true)
                    {
                        $('#otp_message_error').hide();
                        $('#otp_message_sucess').text('Otp Success');
                        $('#otp_message_sucess').show();
                        $('.close').trigger('click');                
                    }else{
                        $('.alert.alert-danger').show();
                        $('#otp_message_error').text('Please enter a valid OTP.');
                    }
                });
            });
            return false;      
        });
        var is_otp_approved = $('#is_otp_approved').val();
        if(is_otp_approved == 0)
        {
            $('.btn.show-code').trigger('click');
            $('.mask, .loader').show();
            sendOtp('new');
            $('.mask, .loader').hide();
        }
    });
    
    $('#resend_otp').click(function(){
        $('#resend_otp').prop('disabled', true);
        $('.mask, .loader').show();
        sendOtp('resend');
        $('.mask, .loader').hide();
        $('#resend_otp').prop('disabled', false);
    });
    
    function sendOtp(type)
    {
        url = '/customer/sendotp';
        customer_id = $('#customer_id_data').val();
        // Send the data using post
        var posting = $.post(url, { customer_id: customer_id, type: type });
        // Put the results in a div
        posting.done(function( responseData ) {
            //responseData = JSON.parse(data);
            if(responseData['result'])
            {
                $('.alert.alert-success').show();
                $('#otp_message_sucess').text(responseData['message']);
            }else{
                $('.alert.alert-danger').show();
                $('#otp_message_error').text(responseData['message']);
            }
        });
    }
</script>
@stop

  