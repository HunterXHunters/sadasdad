@extends('layouts.default')
@extends('layouts.header')
@extends('layouts.sideview')
@section('content')
 @if (Session::has('message'))
 <div class="flash alert">
     <p>{{ Session::get('message') }}</p>
 </div>
 @endif
<div class="box">
<div class="box-header">
<h3 class="box-title"><strong>Free </strong>Trial</h3>
	@if (Session::has('flash_message'))            
	<div class="alert alert-info">{{ Session::get('flash_message') }}</div>
	@endif
</div>	
<input type="hidden" id="comp_name" name="comp_name" class="form-control">
<input type="hidden" id="comp_email" name="comp_email" class="form-control">
<input type="hidden" id="company_name1" name="company_name1" class="form-control">
<div class="modal" id="loading" style="display: none; vertical-align:middle; text-align:center;z-index:99999;">
    <div class="center">
        <!-- <img alt="" src="loader.gif" /> -->
        <p style="text-align:center; vertical-align:middle;padding-top:20%;"><img src="/img/spinner.gif" alt="Loading..." /></p>
    </div>
</div>
	<div class="col-sm-12">
		<div class="tile-body nopadding">  
		{{ Form::open(array('url' => '/wizard/saveSignup','id'=>'saveSignup')) }}
		{{ Form::hidden('_method', 'POST') }}
		  <div class="row">
		     <div class="form-group col-sm-6">
		      <label for="exampleInputEmail">Name *</label>
		      <div class="input-group ">
		        <span class="input-group-addon addon-red"><i class="fa fa-cubes"></i></span>
		        <input type="text" id="name" name="name"   placeholder="Name" class="form-control">
		      </div>
		    </div>  
		  </div>

		   <div class="row"> 
		    <div class="form-group col-sm-6">
		      <label for="exampleInputEmail">Email *</label>
		      <div class="input-group ">
		        <span class="input-group-addon addon-red"><i class="fa fa-arrows-v"></i></span>
		        <input type="text" class="form-control data-fv-emailaddress-message" placeholder="Email" name="email" id = "email">
		      </div>
		    </div>  
		  </div>
		  
		     
		   <div class="row">   
		     <div class="form-group col-sm-6">
		      <label for="exampleInputEmail">Company Name *</label>
		      <div class="input-group ">
		        <span class="input-group-addon addon-red"><i class="fa fa-arrows-h"></i></span>
		        <input type="text" id="company_name" name="company_name"   placeholder="Company Name" class="form-control">
		      </div>
		    </div> 
		  </div> 
		{{ Form::submit('Try Now !!', array('class' => 'btn btn-primary','id' => 'signup-btn')) }}
		{{ Form::close() }}
		</br>
		</br>
		</div>
	</div>
</div>
<div class="box">
	<div class="col-sm-12">
		<div class="tile-body nopadding">  
		{{ Form::open(array('url' => '/wizard/createUser','id'=>'createUser')) }}
		{{ Form::hidden('_method', 'POST') }}
		  <div class="row">
		     <div class="form-group col-sm-6">
		      <label for="exampleInputEmail">Name *</label>
		      <div class="input-group ">
		        <span class="input-group-addon addon-red"><i class="fa fa-cubes"></i></span>
		        <input type="text" id="name_user" name="name"   placeholder="Name" class="form-control">
		      </div>
		    </div>  
		  </div>

		   <div class="row"> 
		    <div class="form-group col-sm-6">
		      <label for="exampleInputEmail">Email *</label>
		      <div class="input-group ">
		        <span class="input-group-addon addon-red"><i class="fa fa-arrows-v"></i></span>
		        <input type="text" class="form-control data-fv-emailaddress-message" placeholder="Email" name="email" id = "email_user">
		      </div>
		    </div>  
		  </div>
		  
		     
		   <div class="row">   
		     <div class="form-group col-sm-6">
		      <label for="exampleInputEmail">Company Name *</label>
		      <div class="input-group ">
		        <span class="input-group-addon addon-red"><i class="fa fa-arrows-h"></i></span>
		        <input type="text" id="company_name_user" name="company_name"   placeholder="Company Name" class="form-control">
		      </div>
		    </div> 
		  </div> 
		{{ Form::submit('sign-up', array('class' => 'btn btn-primary','id' => 'signup')) }}
		{{ Form::close() }}
		</br>
		</br>
		</div>
	</div>
</div>
<div class="modal fade" id="activationCode" tabindex="-1" role="dialog" aria-labelledby="basicvalCode" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                    <h4 class="modal-title" id="basicvalCode">Enter Activation Code</h4>
                </div>
                <div class="modal-body">
                    <div class="">
                        <div class="form-group col-sm-12">
                            <label class="col-sm-2 control-label" for="BusinessType">Activation code *</label>
                            <div class="col-sm-10">
                                <div class="input-group input-group-sm">
                                    <span class="input-group-addon addon-red"><i class="fa fa-flag-checkered"></i></span>
                                    <input type="hidden" id="customer" name="customer" class="form-control" value="">      
                                    <input type="text" id="activation_code" name="activation_code" class="form-control">      
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
@section('script')
<script type="text/javascript">
/*$('#signup-btn').click(function (e) {
	e.preventDefault();
	var name = $('#name').val();
	var email = $('#email').val();
	var company_name = $('#company_name').val();
	url = 'wizard/saveSignup';
	var posting = $.post(url, { name: name ,email: email ,company_name: company_name});
	posting.done(function( data ) {
		$(this).prop("disabled",true);
		console.log(data['status']);
		console.log(data['customer']);
		if(data['status'] == true)
		{
			$('#customer').val(data['customer']);
			$('#activationCode').modal('show');
			var customer = $('#customer').val();
		    $('#activationCode button#cancel-btn').on('click',function(e){
		        e.preventDefault();
		        //console.log('clicked cancel');
		        $('#activationCode').modal('hide');
		    });	
		    $('#activationCode button#save-btn').off('click');
		    $('#activationCode button#save-btn').on('click',function(e){
		        e.preventDefault();
		        //console.log('cliked submit');
		        var activationCode = $.trim($('#activation_code').val());
		        if(activationCode == ''){
		            alert('Please enter Activation code.');
		            return false;
		        } else
		        $.ajax({
		            url: 'wizard/activateSignup',
		            data : {'customer' : customer , 'activation_code' : activationCode},
		            type:'POST',
		            success: function(result)
		            {
		                console.log(result);
		                if( result['status'] == true){
		                	$('#verifyUserPassword').modal('hide');
		                    alert('Succesfully Activated !!');		      
		                    location.reload();
		                }
		                else
		                {
		                    alert(result);
		                }
		            },
		            error: function(err){
		                console.log('Error: '+err);
		            },
		            complete: function(data){
		                console.log(data);
		            }
		        });
		    });   
		}
	});
});*/
$(document).ready(function(){
	$('.main-header').hide();
    window.setTimeout(function(){
        $(".alert").hide();
    },3000);
});
  $(document).ajaxSend(function () {
      $("#loading").show();
  });

  // invoked when sending ajax completed
  $(document).ajaxComplete(function () {
      $("#loading").hide();
  });
$(document).ready(function() {
    $('#saveSignup').bootstrapValidator({
        //live: 'disabled',
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
                            message: 'Name is required.'
                        }
                    }
                },
                email: {
                    validators: {
                        notEmpty: {
                            message: 'The email address is required and can\'t be empty.'
                        },
                        remote: {
                            url: 'wizard/validateEmail',
                            type: 'GET',
                            data: {email: $('[name="email"]').val()},
                            delay: 2000,     // Send Ajax request every 2 seconds
                            message: 'Email already exists, please provide new email.'
                        },
                        regexp: {
                        regexp: '^[^@\\s]+@([^@\\s]+\\.)+[^@\\s]+[^@\\s]+$',
                        message: 'Please enter a valid email address.'
                           
                        }
                    }
                },                
                 company_name: {
                    validators: {
                        notEmpty: {
                            message: 'Company Name is required.'
                        }
                    }
                },
        }
    }).on('success.form.bv', function(event) {
    	event.preventDefault();
/*$('#signup-btn').click(function (e) {*/
	//e.preventDefault();
	var name = $('#name').val();
	var email = $('#email').val();
	var company_name = $('#company_name').val();
	url = 'wizard/saveSignup';
	var posting = $.post(url, { name: name ,email: email ,company_name: company_name});
	posting.done(function( data ) {
		$('#comp_name').val(name);
		$('#comp_email').val(email);
		$('#company_name1').val(company_name);
		$('#name_user').val(name);
		$('#email_user').val(email);
		$('#company_name_user').val($('#company_name').val());
		$(this).prop("disabled",true);
		console.log(data['status']);
		console.log(data['customer']);
		if(data['status'] == true)
		{
			$('#customer').val(data['customer']);
			$('#activationCode').modal('show');
			var customer = $('#customer').val();
		    $('#activationCode button#cancel-btn').on('click',function(e){
		        e.preventDefault();
		        //console.log('clicked cancel');
		        $('#activationCode').modal('hide');
		    });	
		    $('#activationCode button#save-btn').off('click');
		    $('#activationCode button#save-btn').on('click',function(e){
		        e.preventDefault();
		        //console.log('cliked submit');
		        var activationCode = $.trim($('#activation_code').val());
		        if(activationCode == ''){
		            alert('Please enter Activation code.');
		            return false;
		        } else
		        $.ajax({
		            url: 'wizard/activateSignup',
		            data : {'customer' : customer , 'activation_code' : activationCode},
		            type:'POST',
		            success: function(result)
		            {
		                console.log(result);
		                if( result['status'] == true){
		                	$('#activationCode').modal('hide');
		                    alert('Succesfully Activated !!');		      
		                    //location.reload();
		                }
		                else
		                {
		                    alert(result['message']);
		                }
		            },
		            error: function(err){
		                console.log('Error: '+err);
		            },
		            complete: function(data){
		                console.log(data);
		            }
		        });
		    });   
		}
	});
/*});*/
    }).validate({
        submitHandler: function (form) {
            return false;
        }
    });
    $('#createUser').bootstrapValidator({
        //live: 'disabled',
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
                            message: 'Name is required.'
                        }
                    }
                },
                email: {
                    validators: {
                        notEmpty: {
                            message: 'The email address is required and can\'t be empty.'
                        },
                        remote: {
                            url: 'wizard/validateSignupEmail',
                            type: 'GET',
                            data: {email: $('[name="email"]').val()},
                            delay: 2000,     // Send Ajax request every 2 seconds
                            message: 'Email already exists, please provide new email.'
                        },
                        regexp: {
                        regexp: '^[^@\\s]+@([^@\\s]+\\.)+[^@\\s]+[^@\\s]+$',
                        message: 'Please enter a valid email address.'
                           
                        }
                    }
                },                
                 company_name: {
                    validators: {
                        notEmpty: {
                            message: 'Company Name is required.'
                        },
                        remote: {
                            url: 'wizard/validateCustomer',
                            type: 'GET',
                            data: {company_name: $('[id="company_name"]').val()},
                            delay: 2000,     // Send Ajax request every 2 seconds
                            message: 'Customer already exists.'
                        },                        
                    }
                },
        }
    }).on('success.form.bv', function(event) {
    	event.preventDefault();
	var name = $('#name_user').val();
	var email = $('#email_user').val();
	var company_name = $('#company_name_user').val();
    $.ajax({
        url: 'wizard/createUser',
        data : {'name' : name , 'email' : email , 'company_name' : company_name},
        type:'POST',
        success: function(result)
        {
            console.log(result);
            if( result['status'] == true){	
            	//return false;
            	alert('Successfully created. Please check your Email for more details.')	      
                //location.reload();
                window.location.href ='/';
            }/*else if( result['status'] == false){
            	alert(result['status']);
            }*/
            else
            {
                alert(result['message']);
            }
        },
        error: function(err){
            console.log('Error: '+err);
        },
        complete: function(data){
            console.log(data);
        }
    });
    }).validate({
        submitHandler: function (form) {
            return false;
        }
    });    
});
/*$('#sign-up').click(function (e) {
	ajax({
        url: 'wizard/createUser',
        data : ,
        type:'POST',
        success: function(result)
        {
            console.log(result);
            if( result['status'] == true){
            	return false;
            }
            else
            {
                alert(result);
            }
        },
        error: function(err){
            console.log('Error: '+err);
        },
        complete: function(data){
            console.log(data);
        }
    });	
});*/
</script>
@stop