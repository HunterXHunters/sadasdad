@extends('layouts.default')
@extends('layouts.header')
@extends('layouts.sideview')
@section('content')
<!--Added for tinyMCE-->
<!--<script src="//tinymce.cachefly.net/4.1/tinymce.min.js"></script>-->
<script type="text/javascript" src="/tinymce/tinymce.min.js"></script>
 <script type="text/javascript">
  tinyMCE.init({
  theme : "modern",
  theme_advanced_toolbar_location : "top",
  theme_advanced_toolbar_align : "left",
  mode : "exact",
  elements : "HtmlBody"
});
</script>
<!--Added for tinyMCE till here-->
 <style type="text/css">
.form-horizontal .form-group {
    margin-left: -0px !important;
    margin-right: -0px !important;
}
.checkbox input[type="checkbox"], .checkbox-inline input[type="checkbox"], .radio input[type="radio"], .radio-inline input[type="radio"]
{margin-left: 0px !important;}
</style>
<div class="box">
  <div class="box-header with-border">
    <h3 class="box-title"><strong>Edit </strong>Email Templates</h3>    
  </div>
   
   <div class="col-sm-12">
     <div class="tile-body nopadding">  
               
 <!-- tile -->
<section class="tile">

<!--{{Form::open(array('url'=>'email/store','method'=>'post'))}}-->
        <!--{{ Form::open(array('url' => 'email/update','data-url' => 'email/update/')) }}
        {{ Form::hidden('_method','PUT') }}--> 
        {{Form::open(array('url'=>'email/update/'.$email[0]->Id,'id'=>'form-template','method'=>'put'))}} 

                  <!-- /tile header -->
          
                  <!-- tile body -->
  <div class="tile-body form-horizontal">
    <form id="basicvalidations" parsley-validate="" role="form" class="form-horizontal">
	
    <div class="row">
        <div class="form-group col-sm-6">
          <label for="exampleInputEmail">Code *</label>
          <div class="input-group ">
            <span class="input-group-addon addon-red"><i class="ion-ios-compose"></i></span>
            <input type="text"  id="Code" name="Code" value="{{$email[0]->Code}}" class="form-control" required>
          </div>
        </div>


        <div class="form-group col-sm-6">
          <label for="exampleInputEmail">Name *</label>
          <div class="input-group ">
            <span class="input-group-addon addon-red"><i class="ion-person"></i></span>
            <input type="text"  id="Name" name="Name"  value="{{$email[0]->Name}}" class="form-control" required>
          </div>
        </div>
    </div>  
    
    <div class="row">
        <div class="form-group col-sm-6">
          <label for="exampleInputEmail">From *</label>
          <div class="input-group ">
            <span class="input-group-addon addon-red"><i class="ion-arrow-right-c"></i></span>
            <input type="text" id="From" name="From" class="form-control data-fv-emailaddress-message" value="{{$email[0]->From}}"  class="form-control" required>
          </div>
        </div>


        <div class="form-group col-sm-6">
          <label for="exampleInputEmail">Reply To *</label>
          <div class="input-group ">
            <span class="input-group-addon addon-red"><i class="ion-arrow-left-c"></i></span>
            <input type="text"  id="ReplyTo" class="form-control data-fv-emailaddress-message" name="ReplyTo" value="{{$email[0]->ReplyTo}}"  class="form-control" required>
          </div>
        </div>
    </div>      

    <div class="row">
        <div class="form-group col-sm-6">
          <label for="exampleInputEmail">Subject *</label>
          <div class="input-group ">
           <span class="input-group-addon addon-red"><i class="ion-android-create"></i></span>
            <input type="text" class="form-control" id="Subject" value="{{$email[0]->Subject}}" name="Subject" required></textarea>
          </div>
          <br>
          <label for="exampleInputEmail">Text Body *</label>
          <div class="input-group ">
           <span class="input-group-addon addon-red"><i class="ion-ios-paper-outline"></i></span>
            <textarea class="form-control" id="TextBody" value="" name="TextBody" rows="6" required>{{$email[0]->TextBody}}</textarea>
          </div>
        </div>

        <div class="form-group col-sm-6">
          <label for="exampleInputEmail">HTML Body</label>
            <textarea class="form-control" id="HtmlBody" value="" name="HtmlBody" rows="3" >{{$email[0]->HtmlBody}}</textarea>
        </div>
    </div> 
         
    <div class="row">
        <div class="form-group col-sm-6">
          <label for="exampleInputEmail">Signature *</label>
          <div class="input-group ">
            <span class="input-group-addon addon-red"><i class="ion-android-create"></i></span>
            <input type="text"  id="Signature" name="Signature" value="{{$email[0]->Signature}}" class="form-control" required>
          </div>
        </div>


        <div class="form-group col-sm-6">
          <label for="exampleInputEmail">Version *</label>
          <div class="input-group ">
             <span class="input-group-addon addon-red"><i class="ion-more"></i></span>
            <input type="text"  id="Version" name="Version" value="{{$email[0]->Version}}" class="form-control" required>
          </div>
        </div>
    </div>                   
	

    
    <br/>

    <div class="row">
     <div class="form-group form-footer">
       
       <div class="row" align="center">
        <button class="btn btn-primary" type="submit">Update</button>
        <button class="btn btn-primary" type="cancel" onclick="cancel()">Cancel</button>
       </div>
     </div>
    </div>

    
  <!--{{ Form::submit('Add', array('class' => 'btn btn-primary')) }}
            {{Form::close()}}-->
      {{Form::close()}}     
  </form>
</div>
                  <!-- /tile body -->               
</section>
</div>
</div>
</div>
                <!-- /tile -->
<!--</div>--><!--commented to get 1 row-->

<!--Added till here for edit page-->  
</div> <!--/.main --> 
<script type="text/javascript">

function cancel()
{
  location.href = '/email';
}
$(document).ready(function() {
    $('#form-template').bootstrapValidator({
//        live: 'disabled',
        message: 'This value is not valid',
        feedbackIcons: {
            valid: 'glyphicon glyphicon-ok',
            invalid: 'glyphicon glyphicon-remove',
            validating: 'glyphicon glyphicon-refresh'
        },   
fields: {
          
            Code: {
                    validators: {
                        notEmpty: {
                            message: 'Code is required.'
                        }
                    }
                },
                 Name: {
                    validators: {
                        notEmpty: {
                            message: 'Name is required.'
                        }
                    }
                },
                 From: {
                    validators: {
                        notEmpty: {
                            message: 'From is required.'
                        },
                         emailAddress: {
                            message: 'The value is not a valid email address'
                        }
                    }
                },
            ReplyTo: {
                validators: {
                  notEmpty: {
                        message: 'Replyto is required'
                    },
                         emailAddress: {
                            message: 'The value is not a valid email address'
                        }
                }
            },
            Subject: {
                validators: {
                  notEmpty: {
                        message: 'Subject is required'
                    }
                }
            },
            Signature: {
                validators: {
                  notEmpty: {
                        message: 'Signature is required.'
                    }
                }
            },
            TextBody: {
                validators: {
                  notEmpty: {
                        message: 'Text body is required.'
                    }
                }
            },
            Version: {
                validators: {
                  notEmpty: {
                        message: 'Version is required.'
                    }
                }
            },
            
        }
    });

});
</script>

@stop

