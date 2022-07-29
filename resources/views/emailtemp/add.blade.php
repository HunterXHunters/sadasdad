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
  elements : "htmlbody"
});
</script>
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
    <h3 class="box-title"><strong>Add </strong>Email Templates</h3>    
  </div>
   
   <div class="col-sm-12">
     <div class="tile-body nopadding">      

<!--    <div class="tile-header" style="padding-left:100px;">
      <h1>Manage Email Templates</h1>
    </div> -->
<!--Added for edit page-->
<!--<div class="col-md-12">--><!--commented to get 1 row-->
              

                
                <!-- tile -->
<section class="tile">

{{Form::open(array('url'=>'email/store','id'=>'form-template','method'=>'post'))}}

                  <!-- tile body -->
  <div class="tile-body form-horizontal">
    <form id="basicvalidations" parsley-validate="" role="form" class="form-horizontal">
        <!-- {{ Form::open(array('url' => 'email/store', 'class' => 'form-horizontal form1' )) }}
            {{ Form::hidden('_method','POST') }}-->
            
            

    
<!--     <div class="form-group">
      <label class="col-sm-2 control-label" for="exampleInputEmail">Code:</label>
      <div class="col-sm-10">
       <input type="text"  id="code" name="code" placeholder="Code" class="form-control" required>
      </div>
    </div>   --> 
	
    <div class="row">
        <div class="form-group col-sm-6">
          <label for="exampleInputEmail">Code *</label>
          <div class="input-group ">
            <span class="input-group-addon addon-red"><i class="ion-ios-compose"></i></span>
            <input type="text"  id="code" name="code" placeholder="code" class="form-control" >
          </div>
        </div>


        <div class="form-group col-sm-6">
          <label for="exampleInputEmail">Name *</label>
          <div class="input-group ">
            <span class="input-group-addon addon-red"><i class="ion-person"></i></span>
            <input type="text"  id="name" name="name" placeholder="Name" class="form-control" >
          </div>
        </div>
    </div>
    
	<div class="row">
        <div class="form-group col-sm-6">
          <label for="exampleInputEmail">From *</label>
          <div class="input-group ">
            <span class="input-group-addon addon-red"><i class="ion-arrow-right-c"></i></span>
            <input type="text" id="from" name="from" class="form-control data-fv-emailaddress-message" placeholder="From" class="form-control" >
          </div>
        </div>


        <div class="form-group col-sm-6">
          <label for="exampleInputEmail">Reply To *</label>
          <div class="input-group ">
            <span class="input-group-addon addon-red"><i class="ion-arrow-left-c"></i></span>
            <input type="text"  id="replyto" class="form-control data-fv-emailaddress-message" name="replyto" placeholder="Reply To" class="form-control" >
          </div>
        </div>
    </div>
    
    <div class="row">
        <div class="form-group col-sm-6">
          <label for="exampleInputEmail">Subject *</label>
          <div class="input-group ">
            <span class="input-group-addon addon-red"><i class="ion-drag"></i></span>
            <input type="text" class="form-control" id="subject" placeholder="Subject" name="subject" ></textarea>
          </div>
        </div>

		<div class="form-group col-sm-6">
        <label for="exampleInputEmail">Signature *</label>
          <div class="input-group ">
            <span class="input-group-addon addon-red"><i class="ion-android-create"></i></span>
            <input type="text"  id="signature" name="signature" placeholder="Signature" class="form-control" >
          </div>
          </div>
    </div>
	
    <div class="row">
        <div class="form-group col-sm-6">
          <label for="exampleInputEmail">HTML Body</label>
          <textarea class="form-control" id="htmlbody " placeholder="HTML Body" name="htmlbody" rows="3" ></textarea>
        </div>
        <div class="form-group col-sm-6">
          <label for="exampleInputEmail">Text Body *</label>
          <div class="input-group " style="margin-bottom:15px;">
            <span class="input-group-addon addon-red"><i class="ion-ios-paper-outline"></i></span>
            <textarea class="form-control" id="textbody" placeholder="Text Body" name="textbody" rows="6" ></textarea>
          </div>
          </div>
          <div class="form-group col-sm-6">
          <label for="exampleInputEmail">Version *</label>
          <div class="input-group ">
            <span class="input-group-addon addon-red"><i class="ion-more"></i></span>
            <input type="text"  id="version" name="version" placeholder="Version" class="form-control" >
          </div>
        </div>
    </div>
    
  
    <div class="row">
     <div class="form-group form-footer">
       <div class="box-footer">
       <div class="row" align="center">
        
        <button class="btn btn-primary" type="submit">Submit</button>
        
        <button class="btn btn-primary" type="cancel" onclick="cancel()">Cancel</button>
        


       </div>
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
                <!-- /tile -->
<!--</div>--><!--commented to get 1 row-->

<!--Added till here for edit page-->  
</div><!--/.main --> 
</div>
</div>
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
          
            code: {
                    validators: {
                        notEmpty: {
                            message: 'Code is required.'
                        }
                    }
                },
                 name: {
                    validators: {
                        notEmpty: {
                            message: 'Name is required.'
                        }
                    }
                },
                 from: {
                    validators: {
                        notEmpty: {
                            message: 'From is required.'
                        },
                         emailAddress: {
                            message: 'The value is not a valid email address'
                        }
                    }
                },
            replyto: {
                validators: {
                  notEmpty: {
                        message: 'Replyto is required'
                    },

                  emailAddress: {
                      message: 'The value is not a valid email address'
                  }
                }
            },
            subject: {
                validators: {
                  notEmpty: {
                        message: 'Subject is required'
                    }
                }
            },
            signature: {
                validators: {
                  notEmpty: {
                        message: 'Signature is required.'
                    }
                }
            },
            textbody: {
                validators: {
                  notEmpty: {
                        message: 'Text body is required.'
                    }
                }
            },
            version: {
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

