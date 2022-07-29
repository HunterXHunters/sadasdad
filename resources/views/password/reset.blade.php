@extends('layouts.default')
@extends('layouts.header')
@extends('layouts.sideview')
@section('content')



		<h2>Password Reset</h2>
		{{Form::open(array('url'=> 'passwordreset', 'method'=>'post','class'=>'form-resetpassword','id'=>'form-resetpassword')) }}

			 
             
                      Reset Password: <div class="form-group">
                                <input type="password" class="form-control" name="resetpswd" placeholder="Reset Password"/> </br> 
                        </div>
                      Confirm Password: <div class="form-group">                            
                                <input type="password" class="form-control" name="confirmpswd" placeholder="Confirm Password"/> </br>
                        </div>
                        
                        <input type="hidden" class="form-control" name="user_id" value="{{$user[0]->user_id}}" placeholder="Confirm Password"/>
                     
                        <button type="submit" class="btn btn-primary">Submit</button>

         {{ Form::close() }}



<script type="text/javascript">
$(document).ready(function() {
    $('#form-resetpassword').bootstrapValidator({

        message: 'This value is not valid',
        feedbackIcons: {
            valid: 'glyphicon glyphicon-ok',
            invalid: 'glyphicon glyphicon-remove',
            validating: 'glyphicon glyphicon-refresh'
        },
        fields: {

            resetpswd: {
                validators: {
                    notEmpty: {
                        message: 'The password is required and can\'t be empty'
                    },
                    stringLength: {
                        min: 5,
                        max: 14,
                        message: 'The password must be more than 4 and less than 14 characters long'
                    }
                }
            },
            confirmpswd: {
                validators: {
                    notEmpty: {
                        message: 'The confirm password is required and can\'t be empty'
                    },
                    identical: {
                        field: 'resetpswd',
                        message: 'The password and its confirm are not the same'
                    }
                }
            }

        }
            })

            });



</script>
@stop
	
