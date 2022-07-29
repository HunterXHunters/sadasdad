<!DOCTYPE html>
<html>
  <head>
    <title>eSeal - Login</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta charset="UTF-8" />

   <!-- Bootstrap 3.3.4 -->
  <link href="{{ URL::asset('css/bootstrap.min.css') }}" rel="stylesheet" type="text/css" />
  <!-- Font Awesome Icons -->
  <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css" rel="stylesheet" type="text/css" />  
  <!-- Theme style -->
  <link href="{{ URL::asset('css/AdminLTE.min.css') }}" rel="stylesheet" type="text/css" />
@yield('style')

  </head>
  <body class="brownish-scheme login-bg" >
   <div id="wrap">
    <div class="row">
        <!-- Page content -->
        <div id="content" class="col-md-12 full-page login">
			<!--header start-->
            <div class="col-xs-12">
        		<div class="header1">
                <a href="/wizard">Free Trial</a>	<a href="#">Help</a>  |  <a href="#">Support</a>  |  <a href="#">eSealinc.com</a>   |  <a href="#">Contact Us</a>
                </div>
       		</div>
            <!--header end-->
			<div class="col-xs-12">
               <!--left start-->
               <div class="col-md-6"> <img src="../img/login-img.png" alt="" style="width:100%"/></div>
               <!--/left end-->
               <!--right start-->
               <div class="col-md-6">
               		<div class="login-box">
                      <div class="login-box-body">
                      <div class="login-logo">
                        <a href="#"><b>Welcome</b></a>
                      </div><!-- /.login-logo -->
						@if(isset($errorMsg))
                        <!-- <div style="color: #FF0000">{{$errorMsg}}</div> -->
                        {{$errorMsg}}
                        @endif
                        {{Form::open(array('url'=>'login/authorize','method'=>'put','class'=>'form-signin','id'=>'form-signin')) }} 
 

            @if (Session::has('flash_message'))            
            <div class="alert alert-info">{{ Session::get('flash_message') }}</div>
            @endif 


                       <div class="form-group">
                                <input type="text" class="form-control" name="email" />
                        </div>
                        <div class="form-group">                            
                                <input type="password" class="form-control" name="password" />
                        </div>
                        <button type="submit" class="btn btn-primary" name="signup" value="Sign up">Login</button>
                         <a href="javascript:void(0);" data-toggle="modal" data-target="#forgotPasswordModal">Forgot password?</a>
                          </div>   
                         {{ Form::close() }}
                      </div><!-- /.login-box-body -->
                    </div><!-- /.login-box -->
               </div>
               <!--/right end-->

			   <!--footer start-->
                <div class="col-xs-12">
                    <div class="footer navbar-fixed-bottom" style="color:#fff; font-size:12px; margin:0px 0px 15px 20px" >
                        Copyright 2019. All Right Reserved  |  <a href="#">Privacy Policy </a>  |  <a href="#">Terms of Use</a>  
                    </div>
                </div>
              <!--footer end-->
        </div>
        <!-- Page content end -->
      </div>
      <!-- Make page fluid-->
   </div>
      <div class="modal fade" id="forgotPasswordModal" tabindex="-1" role="dialog" aria-labelledby="wizardCode" aria-hidden="true" style="display: none;">
      <div class="modal-dialog wide">
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            <h4 class="modal-title" id="wizardCode">Forgot Password</h4>
          </div>
            <div class="modal-body" >
                
               	  <div class="welcome">
                    <section class="error" style="color: #FF0000">
                        
                    </section>  
                    <section>
                      
                      {{Form::open(array('url'=> 'forgot', 'method'=>'put','class'=>'form-forgotpassword','id'=>'form-forgotpassword')) }} 
                       
                        <div class="form-group">
                          <input type="text" class="form-control" name="emailId" placeholder="Enter registered email address"/>
                        </div>

                    </section>              
                      <section class="new-acc" style="text-align: center; float: none; padding-top: 10px;">
                        <button type="submit" class="btn btn-greensea forgot">Reset Password</button>
                    </section>  
                  </div>
                  {{ Form::close() }}
          </div>
        </div><!-- /.modal-content -->
      </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->
    <!-- Wrap all page content end -->
    <link rel="stylesheet" href="/css/bootstrap.css"/>
    <link rel="stylesheet" href="/css/bootstrapValidator.css"/>
    <script type="text/javascript" src="/js/plugins/jQuery/jQuery-2.1.4.min.js"></script> 
    <script type="text/javascript" src="/js/bootstrap.min.js"></script>
    <script type="text/javascript" src="/js/bootstrapValidator.js"></script>
    <script>
    $(function(){      
      $('.welcome').addClass('animated bounceIn');
    })
   $(document).ready(function(){
        $('.forgot').on('click', function(){
            if($('#form-forgotpassword').data('bootstrapValidator').isValid())
            {
                $.post('forgot',{email : $('[name="emailId"]').val()},function(response){
                    $(".error").html(response);
                });
            }
        })
    });   
    </script>
    @if(Session::has('errorMsg'))    
    <button class="btn" id="errorMsgBtn" data-toggle="modal" data-target="#wizardCodeModal"style="display: none" >
      Add New Role
    </button>  
    <!-- /tile header -->
    <div class="modal fade" id="wizardCodeModal" tabindex="-1" role="dialog" aria-labelledby="wizardCode" aria-hidden="true" style="display: none;">
      <div class="modal-dialog wide">
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            <h4 class="modal-title" id="wizardCode">Error Message</h4>
          </div>
            <div class="modal-body" style="color: #ff0000;">

             <?PHP   echo Session::get('errorMsg'); ?>
              <?PHP Session::forget('errorMsg');?>  
          </div>
        </div><!-- /.modal-content -->
      </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->  

<script> 
    $(document).ready(function(){
        $("#errorMsgBtn").click();
    });
</script>
@endif

  <script type="text/javascript">
$(document).ready(function() {
    $('#form-signin').bootstrapValidator({
//        live: 'disabled',
        message: 'This value is not valid',
        feedbackIcons: {
            valid: 'glyphicon glyphicon-ok',
            invalid: 'glyphicon glyphicon-remove',
            validating: 'glyphicon glyphicon-refresh'
        },
        fields: {
            email: {
                validators: {
                  notEmpty: {
                        message: 'The username is required and cannot be empty'
                    }
                    /*,
                    emailAddress: {
                        message: 'The input is not a valid email address'
                    }*/
                }
            },
            password: {
                validators: {
                    notEmpty: {
                        message: 'The password is required and cannot be empty'
                    },
                    stringLength: {
                        min: 5,
                        max: 14,
                        message: 'The password must be more than 4 and less than 14 characters long'
                    },
                    different: {
                        field: 'username',
                        message: 'The password cannot be the same as username'
                    }
                }
            }
        }
    });

});
</script>
<script type="text/javascript">
$(document).ready(function() {
    $('#form-forgotpassword').bootstrapValidator({

        message: 'This value is not valid',
        feedbackIcons: {
            valid: 'glyphicon glyphicon-ok',
            invalid: 'glyphicon glyphicon-remove',
            validating: 'glyphicon glyphicon-refresh'
        },
        fields: {
            emailId: {
                validators: {
                  notEmpty: {
                        message: 'The emailId is required and cannot be empty'
                    },
                    emailAddress: {
                        message: 'The input is not a valid email address'
                    }
                    
                }
            }
            }
    });


});



$(document).ready(function(){
    window.setTimeout(function(){
        $(".alert").hide();
    },3000);
});


</script>

  </body>
</html>