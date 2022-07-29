<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Global Distribution System | Dashboard</title>
<meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>
<!-- Bootstrap 3.3.4 -->
<link href="/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
<!-- <link href="/css/style.css" rel="stylesheet" type="text/css" /> -->
<!-- Font Awesome Icons -->
<link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css" rel="stylesheet" type="text/css" />
<!-- Ionicons -->
<link href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css" rel="stylesheet" type="text/css" />
<!-- Theme style -->
<link href="/css/AdminLTE.min.css" rel="stylesheet" type="text/css" />
<!-- AdminLTE Skins. Choose a skin from the css/skins 
folder instead of downloading all of them to reduce the load. -->
<link href="/css/_all-skins.min.css" rel="stylesheet" type="text/css" />
<link href="js/plugins/morris/morris.css" rel="stylesheet" type="text/css" />
<!-- jvectormap -->
<link href="/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
<link href="/css/bootstrapValidator.css" rel="stylesheet" type="text/css" />
  <link href="/css/jquery.filer.css" type="text/css" rel="stylesheet" />
        
<!--<script type="text/javascript">
  $.noConflict();
jQuery(document).ready(function(){
   
});
</script>-->
    
<!-- <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.9.0/jquery.min.js"></script> -->
<script src="js/plugins/jQuery/jQuery-2.1.4.min.js"></script>


    
<link href="select/sumoselect.css" rel="stylesheet" />


<link href="/css/bootstrap-datetimepicker.min.css" rel="stylesheet" media="screen">

<!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
<!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
<!--[if lt IE 9]>
<script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
<script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
<![endif]-->
<!--upload scripts-->
<script src="js/plugins/jquery-file-upload/upload-script1.js"></script>
<script src="js/plugins/jquery-file-upload/vendor/jquery.ui.widget.js"></script>
<script src="js/plugins/jquery-file-upload/load-image.all.min.js"></script>
<script src="js/plugins/jquery-file-upload/jquery.iframe-transport.js"></script>
<script src="js/plugins/jquery-file-upload/jquery.fileupload.js"></script>
<script src="js/plugins/jquery-file-upload/jquery.fileupload-process.js"></script>
<script src="js/plugins/jquery-file-upload/jquery.fileupload-image.js"></script>
<script src="js/plugins/jquery-file-upload/jquery.fileupload-audio.js"></script>
<script src="js/plugins/jquery-file-upload/jquery.fileupload-video.js"></script>
<script src="js/plugins/jquery-file-upload/jquery.fileupload-validate.js"></script>
<!--upload scripts-->
<style type="text/css">
.tooltip {
    background-color:#000;
    border:1px solid #fff;
    padding:10px 15px;
    width:200px;
    display:none;
    color:#fff;
    text-align:left;
    font-size:12px;
 
    /* outline radius for mozilla/firefox only */
    -moz-box-shadow:0 0 10px #000;
    -webkit-box-shadow:0 0 10px #000;
}
.signuphead{color:#29aeec !important;font-size: 18px !important;
    margin-top: 28px !important;
}
.bg-navy {background-color: #81889d !important;}
.content { min-height:auto !important;}
a:focus, a:hover {text-decoration: none !important;}
.checkpadright{padding-right:40px !important;}
.countryselec{height:80px; overflow:auto;margin-left:11px;}
.countryhead{background:#efefef; padding:10px;}

.productbox{
background:#fff; -webkit-box-shadow: 1px 1px 6px 1px rgba(0,0,0,0.2);
-moz-box-shadow: 1px 1px 6px 1px rgba(0,0,0,0.2);
box-shadow: 1px 1px 6px 1px rgba(0,0,0,0.2); padding:0px 10px 10px 10px; width:100%; float:left; margin-bottom:20px; margin-right:10px; 
}

.productchkbox{margin-left:10px;}
.productchkdetalinks {
    float: right;
    margin-right: 12px;
    margin-top: 10px;
}
.imgresborder{/*border:1px solid #efefef;*/}
.swithmarg{margin-top:33px !important;}
.productdetails{margin-top:20px;}
.badge {
    display: inline-block;
    min-width: 30px;
    min-height: 30px;
  padding: 9px 7px;
    font-size: 12px;
    font-weight: 700;
    line-height: 1;
    color: #fff;
    text-align: center;
    white-space: nowrap;
    vertical-align: baseline;
    background-color: #777;
    border-radius: 100%;
}
.productmenu{font-size:18px;}





.modal-content-channels{margin:0 auto; text-align:center; width:250px !important;}

.modal-body-channels {
    position: relative;
    padding: 15px;
  height:150px;
  overflow-y:scroll;
}



.modal-body_lightbox{
position: relative;
    padding: 15px;
  height:auto !important;
  overflow:hidden !important;
}

.modal-dialog_lightbox {
    margin: 30px auto;
}

[data-tooltip],
.tooltip {
  position: relative;
  cursor: pointer;
}

/* Base styles for the entire tooltip */
[data-tooltip]:before,
[data-tooltip]:after,
.tooltip:before,
.tooltip:after {
  position: absolute;
  visibility: hidden;
  -ms-filter: "progid:DXImageTransform.Microsoft.Alpha(Opacity=0)";
  filter: progid:DXImageTransform.Microsoft.Alpha(Opacity=0);
  opacity: 0;
  -webkit-transition: 
      opacity 0.2s ease-in-out,
        visibility 0.2s ease-in-out,
        -webkit-transform 0.2s cubic-bezier(0.71, 1.7, 0.77, 1.24);
    -moz-transition:    
        opacity 0.2s ease-in-out,
        visibility 0.2s ease-in-out,
        -moz-transform 0.2s cubic-bezier(0.71, 1.7, 0.77, 1.24);
    transition:         
        opacity 0.2s ease-in-out,
        visibility 0.2s ease-in-out,
        transform 0.2s cubic-bezier(0.71, 1.7, 0.77, 1.24);
  -webkit-transform: translate3d(0, 0, 0);
  -moz-transform:    translate3d(0, 0, 0);
  transform:         translate3d(0, 0, 0);
  pointer-events: none;
}

/* Show the entire tooltip on hover and focus */
[data-tooltip]:hover:before,
[data-tooltip]:hover:after,
[data-tooltip]:focus:before,
[data-tooltip]:focus:after,
.tooltip:hover:before,
.tooltip:hover:after,
.tooltip:focus:before,
.tooltip:focus:after {
  visibility: visible;
  -ms-filter: "progid:DXImageTransform.Microsoft.Alpha(Opacity=100)";
  filter: progid:DXImageTransform.Microsoft.Alpha(Opacity=100);
  opacity: 1;
}

/* Base styles for the tooltip's directional arrow */
.tooltip:before,
[data-tooltip]:before {
  z-index: 1001;
  border: 6px solid transparent;
  background: transparent;
  content: "";
}

/* Base styles for the tooltip's content area */
.tooltip:after,
[data-tooltip]:after {
  z-index: 1000;
  padding: 8px;
  width: 160px;
  background-color: #000;
  background-color: hsla(0, 0%, 20%, 0.9);
  color: #fff;
  content: attr(data-tooltip);
  font-size: 14px;
  line-height: 1.2;
}

/* Directions */

/* Top (default) */
[data-tooltip]:before,
[data-tooltip]:after,
.tooltip:before,
.tooltip:after,
.tooltip-top:before,
.tooltip-top:after {
  bottom: 100%;
  left: 50%;
}

[data-tooltip]:before,
.tooltip:before,
.tooltip-top:before {
  margin-left: -6px;
  margin-bottom: -12px;
  border-top-color: #000;
  border-top-color: hsla(0, 0%, 20%, 0.9);
}

/* Horizontally align top/bottom tooltips */
[data-tooltip]:after,
.tooltip:after,
.tooltip-top:after {
  margin-left: -80px;
}

[data-tooltip]:hover:before,
[data-tooltip]:hover:after,
[data-tooltip]:focus:before,
[data-tooltip]:focus:after,
.tooltip:hover:before,
.tooltip:hover:after,
.tooltip:focus:before,
.tooltip:focus:after,
.tooltip-top:hover:before,
.tooltip-top:hover:after,
.tooltip-top:focus:before,
.tooltip-top:focus:after {
  -webkit-transform: translateY(-12px);
  -moz-transform:    translateY(-12px);
  transform:         translateY(-12px); 
}

/* Left */
.tooltip-left:before,
.tooltip-left:after {
  right: 100%;
  bottom: 50%;
  left: auto;
}

.tooltip-left:before {
  margin-left: 0;
  margin-right: -12px;
  margin-bottom: 0;
  border-top-color: transparent;
  border-left-color: #000;
  border-left-color: hsla(0, 0%, 20%, 0.9);
}

.tooltip-left:hover:before,
.tooltip-left:hover:after,
.tooltip-left:focus:before,
.tooltip-left:focus:after {
  -webkit-transform: translateX(-12px);
  -moz-transform:    translateX(-12px);
  transform:         translateX(-12px); 
}

/* Bottom */
.tooltip-bottom:before,
.tooltip-bottom:after {
  top: 100%;
  bottom: auto;
  left: 50%;
}

.tooltip-bottom:before {
  margin-top: -12px;
  margin-bottom: 0;
  border-top-color: transparent;
  border-bottom-color: #000;
  border-bottom-color: hsla(0, 0%, 20%, 0.9);
}

.tooltip-bottom:hover:before,
.tooltip-bottom:hover:after,
.tooltip-bottom:focus:before,
.tooltip-bottom:focus:after {
  -webkit-transform: translateY(12px);
  -moz-transform:    translateY(12px);
  transform:         translateY(12px); 
}

/* Right */
.tooltip-right:before,
.tooltip-right:after {
  bottom: 50%;
  left: 100%;
}

.tooltip-right:before {
  margin-bottom: 0;
  margin-left: -12px;
  border-top-color: transparent;
  border-right-color: #000;
  border-right-color: hsla(0, 0%, 20%, 0.9);
}

.tooltip-right:hover:before,
.tooltip-right:hover:after,
.tooltip-right:focus:before,
.tooltip-right:focus:after {
  -webkit-transform: translateX(12px);
  -moz-transform:    translateX(12px);
  transform:         translateX(12px); 
}

/* Move directional arrows down a bit for left/right tooltips */
.tooltip-left:before,
.tooltip-right:before {
  top: 3px;
}

/* Vertically center tooltip content for left/right tooltips */
.tooltip-left:after,
.tooltip-right:after {
  margin-left: 0;
  margin-bottom: -16px;
}


@media only screen and (min-width : 320px) {
  .modal-dialog1 {
    width: 90% !important;
  height:auto !important;
    margin: 30px auto;
}


 .fixedsearch{position:fixed; top:0; left:0; background:#fff; z-index:889; margin-bottom:10px;} 
 .headtitl{padding-top:30; margin-top:30px;}     
 .productbox{padding:0px 10px 10px 10px; width:90%; float:left; margin-bottom:20px;}
 
    }

    /* Extra Small Devices, Phones */ 
    @media only screen and (min-width : 480px) {

    }

    /* Small Devices, Tablets */
    @media only screen and (min-width : 768px) {
.productbox{padding:0px 10px 10px 10px; width:90%; float:left; margin-bottom:20px; margin-right:10px; margin-left:0px;}
.modal-dialog1 {
    width: 90% !important;
  height:auto !important;
    margin: 30px auto;
}

    }

    /* Medium Devices, Desktops */
    @media only screen and (min-width : 992px) {
.modal-dialog1 {
    width: 90% !important;
  height:auto !important;
    margin: 30px auto;
}

    }

    /* Large Devices, Wide Screens */
    @media only screen and (min-width : 1200px) {
    .modal-dialog1 {
    width: 50% !important;
  height:auto !important;
    margin: 30px auto;
}
.fixedsearch{position:relative; background:none;}
.headtitl{}
    }

.switch {
  position: relative;
  display: block;
  vertical-align: top;
  width: 100px;
  height: 30px;
  padding: 3px;
  margin: 0 10px 10px 0;
  cursor: pointer;
}
.switch-input {
  position: absolute;
  top: 0;
  left: 0;
  opacity: 0;
}
.switch-label {
  position: relative;
  display: block;
  height: 15px; width:15px;
  font-size: 10px;
  text-transform: uppercase;
  background: #4c9232;
  border-radius: 100%;
  box-shadow: inset 0 1px 2px rgba(0, 0, 0, 0.12), inset 0 0 2px rgba(0, 0, 0, 0.15);
}
.switch-label:before, .switch-label:after {
  position: absolute;
  top: 50%;
  margin-top: -.5em;
  line-height: 1;
  -webkit-transition: inherit;
  -moz-transition: inherit;
  -o-transition: inherit;
  transition: inherit;
}
.switch-label:before {
  content: attr(data-off);
  right: 11px;
  color: #aaaaaa;
  text-shadow: 0 1px rgba(255, 255, 255, 0.5);
}
.switch-label:after {
  content: attr(data-on);
  left: 11px;
  color: #FFFFFF;
  text-shadow: 0 1px rgba(0, 0, 0, 0.2);
  opacity: 0;
}
.switch-input:checked ~ .switch-label {
  background: #D8081C;
  box-shadow: inset 0 1px 2px rgba(0, 0, 0, 0.15), inset 0 0 3px rgba(0, 0, 0, 0.2);
}
.switch-input:checked ~ .switch-label:before {
  opacity: 0;
}
.switch-input:checked ~ .switch-label:after {
  opacity: 1;
}
.switch-handle {
  position: absolute;
  top: 4px;
  left: 4px;
  width: 28px;
  height: 28px;
  background: linear-gradient(to bottom, #FFFFFF 40%, #f0f0f0);
  background-image: -webkit-linear-gradient(top, #FFFFFF 40%, #f0f0f0);
  border-radius: 100%;
  box-shadow: 1px 1px 5px rgba(0, 0, 0, 0.2);
}
.switch-handle:before {
  content: "";
  position: absolute;
  top: 50%;
  left: 50%;
  margin: -6px 0 0 -6px;
  width: 12px;
  height: 12px;
  background: linear-gradient(to bottom, #eeeeee, #FFFFFF);
  background-image: -webkit-linear-gradient(top, #eeeeee, #FFFFFF);
  border-radius: 6px;
  box-shadow: inset 0 1px rgba(0, 0, 0, 0.02);
}
.switch-input:checked ~ .switch-handle {
  left: 74px;
  box-shadow: -1px 1px 5px rgba(0, 0, 0, 0.2);
}
 
/* Transition
========================== */
.switch-label, .switch-handle {
  transition: All 0.3s ease;
  -webkit-transition: All 0.3s ease;
  -moz-transition: All 0.3s ease;
  -o-transition: All 0.3s ease;
}
</style>


<script type="text/javascript"> 
<!-- 
function showHide(){ 
//create an object reference to the div containing images 
var oImageDiv=document.getElementById('myimageDiv') 
//set display to inline if currently none, otherwise to none 
oImageDiv.style.display=(oImageDiv.style.display=='none')?'inline-block':'none'

} 
//--> 
</script>

</head>
<body class="skin-blue sidebar-mini">

  
<div class="wrapper">


<!-- Left side column. contains the logo and sidebar -->



<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
<!-- Content Header (Page header) -->







<section class="content">








<!-- <div class="row">
<div class="col-lg-6">
<h3 class="signuphead">Add details</h3>
</div>
<div class="col-lg-6">
<div class="margin pull-right">
<div class="btn-group">
<button type="button" class="btn bg-navy margin" id="wizard-save1">Save</button>
</div>
<div class="btn-group">
<button type="button" class="btn bg-navy btn-flat margin">Cancel</button>
</div>

</div>
</div>
</div> -->

<div class="row">
<section class="col-lg-12">
<div class="panel-group" id="accordion" role="tablist" aria-multiselectable="true">
<div class="panel panel-default" id="acc1">
<a role="button" data-toggle="collapse" data-parent="#accordion" href="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
<div class="panel-heading panel-new" role="tab" id="headingOne" >
<h4 class="panel-title">
Company Setup
</h4>
</div>
</a>

<div id="collapseOne" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="headingOne">
<div class="panel-body">
<section class="content">



{{ Form::open(array('url' => '/saveCustomerData','id'=>'saveCustomerData')) }}
{{ Form::hidden('_method', 'POST') }}


<div class="row">
<input type="hidden" class="form-control" id= "customer_id" value""/>
<input type="hidden" class="form-control" id= "customer_id1" value="{{$manufacturerId}}">

<div class="col-md-4">

<div class="form-group">
<label for="Country" data-tooltip="Enter your Country">Country *</label><a data-tooltip="Please select the country where your company is registered.">
<i class="fa fa-exclamation-circle"></i>
</a>
<!-- <input type="input" class="form-control" id="Height" placeholder="Country" title="Must be at least 8 characters"> -->
<div id="selectbox">
    <select class="chosen-select form-control parsley-validated" data-live-search="true" name="customer_country" id="customer_country" parsley-trigger="change" parsley-required="true" parsley-error-container="#selectbox">
    @if(isset($company->country_id) && !empty($company->country_id))
      @foreach ($countries as $key => $value)
        @if($company->country_id == $key)
          <option value="{{ $company->country_id }}" selected="true">{{ $company->country }}</option>
        @else
          <option value="{{ $key }}">{{ $value }}</option>
        @endif
      @endforeach
    @else
      @foreach ($countries as $key => $value)
        @if($value == 'India')
          <option value="{{ $key }}" selected="true">{{ $value }}</option>
        @else
          <option value="{{ $key }}">{{ $value }}</option>
        @endif
      @endforeach
    @endif
    </select>                                    
</div>
</div>

</div>

<div class="col-md-4">
<div class="form-group">
<label for="Width">Company Legal Name *</label><a data-tooltip="Legal Name of your Company.">
<i class="fa fa-exclamation-circle"></i>
</a>
<!-- <input type="Height" class="form-control" placeholder="Enter Company Legal Name" name="company_name" id="company_name"> -->
@if(isset($company->brand_name) && !empty($company->brand_name))
<input type="Height" class="form-control"  value="{{$company->brand_name}}" placeholder="Enter Company Legal Name" name="company_name" id="company_name">
@else
<input type="Height" class="form-control"  placeholder="Enter Company Legal Name" name="company_name" id="company_name">
@endif
</div>
</div>

<div class="col-md-4">
                 
<div class="form-group">
<label for="Width">RN Number *</label><a data-tooltip="Company CIN Number.">
<i class="fa fa-exclamation-circle"></i>
</a>
<!-- <input type="Width" class="form-control" placeholder="Enter Cin Number" name="cin_number" id="cin_number"> -->
@if(isset($company->cin_number) && !empty($company->cin_number))
<input type="Width" class="form-control" value="{{$company->cin_number}}" placeholder="Enter Cin Number" name="cin_number" id="cin_number">
@else
<input type="Width" class="form-control" placeholder="Enter Cin Number" name="cin_number" id="cin_number">
@endif
</div>

</div>
</div>
 
<div class="row">
<div class="col-md-4">
<div class="form-group">
<label for="PAN Number">TAX Number *</label><a data-tooltip="Company PAN Number">
<i class="fa fa-exclamation-circle"></i>
</a>
<!-- <input type="Width" class="form-control" placeholder="Enter PAN Number" name="pan_number" id="pan_number"> -->
@if(isset($company->pan_number) && !empty($company->pan_number))
  <input type="Width" class="form-control" value="{{$company->pan_number}}"placeholder="Enter PAN Number" name="pan_number" id="pan_number">
@else
  <input type="Width" class="form-control" placeholder="Enter PAN Number" name="pan_number" id="pan_number">
@endif
</div>

</div>
<!--<div class="col-md-4">
<label for="Width">Upload Documents</label>
<input type="file" multiple name="files[]" id="input2">
</div>-->
<div class="col-md-4">
<div class="form-group">
<label for="Brand Name">Brand Name *</label><a data-tooltip="Company Brand Name">
<i class="fa fa-exclamation-circle"></i></a>
<!-- <input type="Width" class="form-control" placeholder="Enter Brand Name" name="brand_name" id="brand_name"> -->
@if(isset($company->brand_name) && !empty($company->brand_name))
  <input type="Width" class="form-control" value="{{$company->brand_name}}"placeholder="Enter Brand Name" name="brand_name" id="brand_name">
@else
  <input type="Width" class="form-control" placeholder="Enter Brand Name" name="brand_name" id="brand_name">
@endif
</div>

</div>

<div class="col-md-4" id = "upload_field">
<div class="form-group">
<label for="Brand Name">Documents *</label><a data-tooltip="Upload company documents.">
<i class="fa fa-exclamation-circle"></i></a>
<input id="fileupload" type="file" name="files[]" multiple />
<table role="presentation" class="table table-striped"><tbody id="files" class="files"></tbody></table>
<!--<input type="file" class="form-control" value = "upload" multiple name="files[]" id="input2"> -->
<input type="hidden" id="customer_log" value="" />
</div>
</div>
</div>


<div class="row">
<div class="col-md-12" style="text-align:right;">
<div class="btn-group">
<button type="submit" class="btn bg-navy btn-flat" id = "customer_save">Next</button>
<!-- <button type="button" class="btn bg-navy btn-flat" id = "customer_save">Next</button> -->
</div>
</div>
</div>
<!-- {{ Form::submit('Submit', array('class' => 'btn btn-primary')) }} -->
{{ Form::close() }}

</section>
</div>
</div>
</div>

<div class="panel panel-default" id="acc2">

<a class="collapsed" role="button" data-toggle="collapse" data-parent="#accordion" href="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
<div class="panel-heading panel-new" role="tab" id="headingTwo">
<h4 class="panel-title">
<!-- Module Selection & Contract Signing -->
Ebutor Product Selection
</h4>
</div>
</a>

<div id="collapseTwo" class="panel-collapse collapse" role="tabpanel" aria-labelledby="headingTwo">
<div class="panel-body">

<section class="content">
<div class="row" id="couponValidator">
<div class="col-md-4">
<div class="form-group">
<label for="Coupon Code">Coupon Code *</label><a data-tooltip="Enter Coupon code.">
<i class="fa fa-exclamation-circle"></i></a>
<input type="text" class="form-control" placeholder="Enter Coupon code" name="coupon_code" id="coupon_code">
@if(isset($coupon_status) && !empty($coupon_status))
<input type="hidden" class="form-control"  name="coupon_status" id="coupon_status" value="{{$coupon_status[0]->activation_status}}">
@endif
</div>
</div>
<div class="col-md-4" style="text-align:left;padding-top:32px;">
<div class="btn-group">
<button type="button" class="btn bg-navy btn-flat" id = "coupon_activate">Activate</button>
<!-- <button type="button" class="btn bg-navy btn-flat" id = "customer_save">Next</button> -->
</div>
</div>
</div>
<div class="row">

<div class="col-md-12">

<div class="form-group">
<div class="radio">
<!-- <label>
<input type="radio" name="signup_type" id="optionsRadios1" value="1" checked="">
Free Trial <a data-tooltip="Select it for free trial"><i class="fa fa-exclamation-circle"></i> </a>
</label>
<label>
<input type="radio" name="signup_type" id="optionsRadios2" value="2">
Production<a data-tooltip="Select it for production.">
<i class="fa fa-exclamation-circle"></i></a>
</label> -->
@if(isset($custcnrct[0]->signup_type) && !empty($custcnrct[0]->signup_type))
  @foreach($custsigndata as $key => $value )
    @if($custcnrct[0]->signup_type == $value->value)
      <label>
      <input type="radio" name="signup_type" id="{{$value->name}}" value="{{$value->value}}" checked="true">{{$value->name}}
      </label><a data-tooltip="Select it for {{$value->name}}."></label>
<i class="fa fa-exclamation-circle"></i></a>
    @else
      <label><input type="radio" name="signup_type" id="{{$value->name}}" value="{{$value->value}}">{{$value->name}}</label><a data-tooltip="Select it for {{$value->name}}.">
<i class="fa fa-exclamation-circle"></i></a>
    @endif
  @endforeach
@else
  @foreach($custsigndata as $key => $value )
    <label><input type="radio" name="signup_type" id="{{$value->name}}" value="{{$value->value}}">{{$value->name}}</label><a data-tooltip="Select it for {{$value->name}}.">
<i class="fa fa-exclamation-circle"></i></a>
  @endforeach
@endif
</div>

</div>



</div>





</div>

<div class="row">
<div class="col-md-8">
<!-- <label class="checkpadright"><input type="checkbox" id="gds" name="plan[]" value = "1"> GDS</label><a data-tooltip="Select to choose GDS.">
<i class="fa fa-exclamation-circle"></i></a>
<label class="checkpadright"><input type="checkbox" id="sco" name="plan[]" value = "2"> SCO</label><a data-tooltip="Select to choose SCO.">
<i class="fa fa-exclamation-circle"></i></a> -->
@if(isset($custSelectedModules))
  @foreach($custplandata as $key => $value )
    @if(in_array($value->value,$custSelectedModules))
    <label class="checkpadright"><input type="checkbox" id="{{$value->name}}" name="plan[]" value="{{$value->value}}" checked="true">{{$value->name}}</label><a data-tooltip="Select to choose {{$value->name}}.">
<i class="fa fa-exclamation-circle"></i></a>
  @else
    @if($value->name == 'GDS')
    <label class="checkpadright"><input type="checkbox" checked="true" id="{{$value->name}}" name="plan[]" value="{{$value->value}}">{{$value->name}}</label><a data-tooltip="Select to choose {{$value->name}}.">
<i class="fa fa-exclamation-circle"></i></a>
    @else
    <label class="checkpadright"><input type="checkbox" id="{{$value->name}}" name="plan[]" value="{{$value->value}}">{{$value->name}}</label><a data-tooltip="Select to choose {{$value->name}}.">
<i class="fa fa-exclamation-circle"></i></a>    
    @endif
   @endif
  @endforeach
 @else
   @foreach($custplandata as $key => $value )
   @if($value->name == 'GDS')
    <label class="checkpadright"><input type="checkbox" checkbox = "true" id="{{$value->name}}" name="plan[]" value="{{$value->value}}">{{$value->name}}</label><a data-tooltip="Select to choose a {{$value->name}}.">
<i class="fa fa-exclamation-circle"></i></a>
    @else
    <label class="checkpadright"><input type="checkbox" id="{{$value->name}}" name="plan[]" value="{{$value->value}}">{{$value->name}}</label><a data-tooltip="Select to choose a {{$value->name}}.">
<i class="fa fa-exclamation-circle"></i></a>
    @endif    
   @endforeach
@endif
</div>


</div>
<div class="row">
<div class="col-md-12" style="text-align:right;">
<div class="btn-group">
<button type="button" class="btn bg-navy btn-flat" id="customer_contract">Next</button>
</div>
</div>
</div>
</section>

</div>
</div>
</div>

<div class="panel panel-default" id="acc3">

<a class="collapsed" role="button" data-toggle="collapse" data-parent="#accordion" href="#collapseThree" aria-expanded="false" aria-controls="collapseThree">

<div class="panel-heading panel-new" role="tab" id="headingThree">
<h4 class="panel-title">
<!-- Channel Selection -->Region/Channel Selection 
</h4>
</div>
</a>

<div id="collapseThree" class="panel-collapse collapse" role="tabpanel" aria-labelledby="headingThree">
<div class="panel-body">

<section class="content">

<div class="row">

<div class="col-md-6">


<div class="box-header">
<h3 class="box-title">Select Countries</h3><a data-tooltip="select countries">
<i class="fa fa-exclamation-circle"></i></a>
</div>


<div class="checkbox countryhead">
<label><input type="checkbox" class="check" id="checkAll"> <strong>All</strong></label><a data-tooltip="To choose all countries.">
<i class="fa fa-exclamation-circle"></i></a></div>
<div class="countryselec">

<form role="form">
<div class="form-group">

@foreach($channelCountries as $channelCountries)
@if(in_array($channelCountries->country_code,$custSelChannelCountries))
<div class="checkbox"><label><input type="checkbox" id="countries[]" name= "countries[]" class="check" value="{{$channelCountries->country_code}}" checked="true"> {{$channelCountries->country_name}}</label></div>
@else
<div class="checkbox"><label><input type="checkbox" id="countries[]" name= "countries[]" class="check" value="{{$channelCountries->country_code}}"> {{$channelCountries->country_name}}</label></div>
@endif
@endforeach
</div>
</form>
</div>

</div>
<div class="col-md-6">
&nbsp;
</div>
<div class="container">
<div class="row">
<div class="col-md-12">
<div class="box-header">
<h3 class="box-title">Select Channel</h3><a data-tooltip="Select Channels.">
<i class="fa fa-exclamation-circle"></i></a>
</div>

<div class="checkbox" id= "channels">
@foreach($custSelCountryChannels as $key=>$value)
<div class="row {{$value->channnel_name}}">
@if(in_array($value->channel_id,$custSelectedChannels))
<div class="col-md-5">
<a target="_blank" href="{{$value->price_url}}">Charges</a></br>
<label class="checkpadright"><input type="checkbox"checked="true" class="terms">I Agree</label><!-- <iframe height="100px" width="auto" frameborder="1" style="vertical-align:top;" src= "{{$value->price_url}}"></iframe> -->
</div>
<div class="col-md-5">
<a target="_blank" href="{{$value->tnc_url}}">Terms and Condition</a></br>
<label class="checkpadright"><input type="checkbox" checked="true" class="terms">I Agree</label><!-- <iframe height="100px" width="auto" frameborder="1" style="vertical-align:top;" src="{{$value->tnc_url}}"></iframe> -->

</div>
<div class="col-md-2 channelValidation">
<label class="checkpadright"><input type="checkbox" id="channel[]" name="channel[]" value="{{$value->channel_id}}" checked="true">{{$value->channnel_name}}</label>
</div>
@else
<div class="col-md-5">
<a target="_blank" href="{{$value->price_url}}">Charges</a></br>
<label class="checkpadright"><input type="checkbox" class="terms">I Agree</label><!-- <iframe height="100px" width="auto" frameborder="1" style="vertical-align:top;" src= "{{$value->price_url}}"></iframe> -->
</div>
<div class="col-md-5">
<a target="_blank" href="{{$value->tnc_url}}">Terms and Condition</a></br>
<label class="checkpadright"><input type="checkbox" class="terms">I Agree</label><!-- <iframe height="100px" width="auto" frameborder="1" style="vertical-align:top;" src="{{$value->tnc_url}}"></iframe> -->
</div>
<div class="col-md-2 channelValidation">
<label class="checkpadright"><input type="checkbox" id="channel[]" name="channel[]" value="{{$value->channel_id}}">{{$value->channnel_name}}</label>
</div>
@endif
</div>
</br>
@endforeach
</div>



</div>
</div>
</div>
<div class="row">
<div class="col-md-12" style="text-align:right;">
<div class="btn-group">
<button type="button" class="btn bg-navy btn-flat" id="channel_customer_save">Next</button>
</div>
</div>
</div>
</section>



</div>
</div>
</div>

<div class="panel panel-default" id="acc4">

<a role="button" data-toggle="collapse" data-parent="#accordion" href="#collapseFour" aria-expanded="true" aria-controls="collapseFour">
<div class="panel-heading panel-new" role="tab" id="headingFour">
<h4 class="panel-title">
<!-- Connect to ERP -->Software Setup
</h4>
</div>
</a>


<div id="collapseFour" class="panel-collapse collapse" role="tabpanel" aria-labelledby="headingOne">
<div class="panel-body">
<section class="content">
<div class="row" id="erp-row">
<div class="col-md-6">
<div class="form-group">
<label for="is_dc">Do you have ERP? </label><a data-tooltip="Select if you have ERP">
<i class="fa fa-exclamation-circle"></i></a>
 <!-- <input type="checkbox" value="1" id="erp_flag" name="erp_flag" parsley-group="mygroup" parsley-trigger="change" parsley-required="true" parsley-mincheck="2" parsley-error-container="#myproperlabel .last" class="parsley-validated"> -->
 <input type="checkbox" value="1" id="erp_flag" name="erp_flag">
</div>
</div>
<div class="col-md-6" style="text-align:right;">
<div class="btn-group">
<button type="button" class="btn bg-navy btn-flat" id="no_erp">Next</button>
</div>
</div>
</div>
{{ Form::open(array('url' => '/saveCustErpConfigurations','id'=>'saveCustErpConfigurations')) }}
{{ Form::hidden('_method', 'POST') }}
<div id="erpdiv" class="hidden">
<div class="row">

<div class="col-md-6">
<div class="form-group">
<label for="ERP Type">ERP Type * </label><a data-tooltip="Select your ERP Type.">
<i class="fa fa-exclamation-circle"></i></a>
    <select name="erp_model" id="erp_model" class="chosen-select form-control parsley-validated">
<!--         <option value="0" selected="true">Please select...</option>
        @foreach($erpData as $key => $value )
        <option value="@{{ $value->value }}">{{ $value->name}}</option>
        @endforeach -->
       @if(isset($erpintdata[0]->erp_model) && !empty($erpintdata[0]->erp_model))
       @foreach($erpData as $key => $value )
        @if($erpintdata[0]->erp_model == $value->value)
         <option value="{{ $value->value }}" selected="true">{{ $value->name}}</option>
        @else
          <option value="{{ $value->value }}">{{ $value->name}}</option>
        @endif                   
       @endforeach
      @else
      <option value="">Please Select</option>
      @foreach($erpData as $key => $value )
      <option value="{{ $value->value }}">{{ $value->name}}</option>
      @endforeach
      @endif        
    </select>
</div>
</div>

<div class="col-md-6">
<div class="form-group">
<label for="Integration Mode">Integration Mode</label><a data-tooltip="Enter your integration mode.">
<i class="fa fa-exclamation-circle"></i></a>
<!-- <input type="input" class="form-control" id="integration_mode" name="integration_mode" placeholder="Integration Mode">
 -->
@if(isset($erpintdata[0]->integration_mode) && !empty($erpintdata[0]->integration_mode))
   <input type="input" class="form-control" id="integration_mode" name="integration_mode" placeholder="Integration Mode" value ="{{$erpintdata[0]->integration_mode}}">
@else
   <input type="input" class="form-control" id="integration_mode" name="integration_mode" placeholder="Integration Mode">
@endif
</div>
</div>

</div>

<div class="row">

<!--<div class="col-md-6">
<div class="form-group">
<label for="ERP Model">ERP Model</label>
<input type="input" class="form-control" id="Test" placeholder="ERP Model">
</div>
</div>-->
<div class="col-md-6">
<div class="form-group">

<div class="control-group">
<label class="control-label">Default Start Date</label><a data-tooltip="Select start date">
<i class="fa fa-exclamation-circle"></i></a>
<div class="controls input-append date form_datetime" data-date="2015-11-10T05:25:07Z" data-date-format="dd MM yyyy - HH:ii p" data-link-field="dtp_input1">
<input size="16" type="text" value="" readonly class="form-control" name = "start_date">
<span class="add-on"><i class="icon-remove"></i></span>
<span class="add-on"><i class="icon-th"></i></span>
</div>
<!-- <input type="hidden" id="dtp_input1" value="" name = "default_start_date"/> -->
@if(isset($erpintdata[0]->default_start_date) && !empty($erpintdata[0]->default_start_date))
   <input type="hidden" id="dtp_input1" name="default_start_date" value ="{{$erpintdata[0]->default_start_date}}">
@else
  <input type="hidden" id="dtp_input1" value="" name = "default_start_date"/>
@endif
</div>


</div>
</div>

<div class="col-md-6">
<div class="form-group">
<label for="Token">Token</label><a data-tooltip="Give your access token">
<i class="fa fa-exclamation-circle"></i></a>
<!-- <input type="input" class="form-control" id="token" name="token" placeholder="Token"> -->
@if(isset($erpintdata[0]->token) && !empty($erpintdata[0]->token))
   <input type="input" class="form-control" id="token" name="token" placeholder="Token"value ="{{$erpintdata[0]->token}}">
@else
  <input type="input" class="form-control" id="token" name="token" placeholder="Token">
@endif
</div>
</div>

</div>

<div class="row">

<div class="col-md-6">
<div class="form-group">
<label for="Web Service URL">Web Service URL</label><a data-tooltip="Give web service URL">
<i class="fa fa-exclamation-circle"></i></a>
<!-- <input type="input" class="form-control" id="web_service_url" name ="web_service_url" placeholder="Web Service url"> -->
@if(isset($erpintdata[0]->web_service_url) && !empty($erpintdata[0]->web_service_url))
  <input type="input" class="form-control" id="web_service_url" name ="web_service_url" placeholder="Web Service url"value ="{{$erpintdata[0]->web_service_url}}">
@else
 <input type="input" class="form-control" id="web_service_url" name ="web_service_url" placeholder="Web Service url">
@endif
</div>
</div>

<div class="col-md-6">
<div class="form-group">
<label for="Company Code">Company Code</label><a data-tooltip="Give Company code">
<i class="fa fa-exclamation-circle"></i></a>
<!-- <input type="input" class="form-control" id="company_code" name="company_code" placeholder="Company Code"> -->
@if(isset($erpintdata[0]->company_code) && !empty($erpintdata[0]->company_code))
     <input type="input" class="form-control" id="company_code" name="company_code" placeholder="Company Code" value ="{{$erpintdata[0]->company_code}}">
@else
   <input type="input" class="form-control" id="company_code" name="company_code" placeholder="Company Code">
@endif
</div>
</div>

</div>

<div class="row">
<div class="col-md-6">
<div class="form-group">
<label for="Web Service Username">Web Service Username</label><a data-tooltip="Give web service username">
<i class="fa fa-exclamation-circle"></i></a>
<!-- <input type="input" class="form-control" id="web_service_username" name="web_service_username" placeholder="Web Service Username"> -->
@if(isset($erpintdata[0]->web_service_username) && !empty($erpintdata[0]->web_service_username))
     <input type="input" class="form-control" id="web_service_username" name="web_service_username" placeholder="Web Service Username" value ="{{$erpintdata[0]->web_service_username}}">
@else
    <input type="input" class="form-control" id="web_service_username" name="web_service_username" placeholder="Web Service Username">
@endif
</div>
</div>
<div class="col-md-6">
<div class="form-group">
<label for="Web Service Password">Web Service Password</label><a data-tooltip="Give webservice password">
<i class="fa fa-exclamation-circle"></i></a>
<!-- <input type="password" class="form-control" id="web_service_password" name="web_service_password" placeholder="Web Service Password"> -->
@if(isset($erpintdata[0]->web_service_password) && !empty($erpintdata[0]->web_service_password))
  <input type="password" class="form-control" id="web_service_password" name="web_service_password" placeholder="Web Service Password" value ="{{$erpintdata[0]->web_service_password}}">
@else
  <input type="password" class="form-control" id="web_service_password" name="web_service_password" placeholder="Web Service Password">
@endif
</div>
</div>
</div>

<div class="row">
<div class="col-md-12" style="text-align:right;">
<div class="btn-group">
<button type="submit" class="btn bg-navy btn-flat" id="erp_save">Next</button>
</div>
</div>
</div>

</div>
{{ Form::close() }}

</section>
</div>
</div>
</div>


<div class="panel panel-default" id="acc5">

<a role="button" data-toggle="collapse" data-parent="#accordion" href="#collapseFive" aria-expanded="true" aria-controls="collapseFive">
<div class="panel-heading panel-new" role="tab" id="headingFive">
<h4 class="panel-title">
<!-- DC selection -->Logistics Setup
</h4>
</div>
</a>


<div id="collapseFive" class="panel-collapse collapse" role="tabpanel" aria-labelledby="headingOne">
<div class="panel-body">
<section class="content">




{{ Form::open(array('url' => '/saveCustDcSelection','id'=>'saveCustDcSelection')) }}
{{ Form::hidden('_method', 'POST') }}


<div class="row">


<!-- <div class="col-md-3">

<div class="form-group">
<label for="Country">Country</label>
<input type="input" class="form-control" id="Height" placeholder="Country">
</div>

</div> -->
<div class="col-md-3"  style="margin-top:20px;">
<label for="Location Address">Location</label><a data-tooltip="Address of the location">
<i class="fa fa-exclamation-circle"></i></a>
<a href="#AddAddress" data-toggle="modal" class="productmenu">
<button type="button" class="btn btn-primary btn-flat margin">Add Location</button>
</a>
    <div class="form-group pull-left" style="margin-left:15px;">
        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#import_from_erp"> 
            Import from ERP 
        </button>
    </div>
    <div class="form-group pull-left" style="margin-left:15px;">
        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#location_types_add_excel"> 
            Import from CSV 
        </button>
    </div>
  </div>
<div class="bs-example">
<div id="AddAddress" class="modal fade">
<div class="modal-dialog modal-dialog1">
<div class="modal-content">
<div class="modal-header">
<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
<h4 class="modal-title">Add Address</h4>
</div>
<div class="modal-body modal-body1">
<div class="row">
<div class="col-md-6">
                 
<div class="form-group">
<label for="Location Name">Location Name</label><a data-tooltip="Give Location Name ">
<i class="fa fa-exclamation-circle"></i></a>
<input type="input" class="form-control" id="location_name" name="location_name" placeholder="Location Name">
</div>

</div>
<div class="col-md-6">
                 
<div class="form-group">
<label for="Location Type">Location Type</label><a data-tooltip="Type of the Location">
<i class="fa fa-exclamation-circle"></i></a>
<select class="form-control" id="location_type_id" name="location_type_id">
@foreach($locationTypes as $key=>$value)
<option value="{{ $value->location_type_id }}">{{ $value->location_type_name}}</option>
@endforeach
</select>
</div>

</div>
</div>
<div class="row">
<div class="col-md-12">
<div class="form-group">
<label>Address</label><a data-tooltip="Location Address">
<i class="fa fa-exclamation-circle"></i></a>
<textarea class="form-control" rows="3" id="location_address" name= "location_address" placeholder="Enter ..."></textarea>
</div>
</div>
</div>


<div class="row">


<div class="col-md-6">

<div class="form-group">
<label for="Country">Country</label><a data-tooltip="Location Country">
<i class="fa fa-exclamation-circle"></i></a>
<select class="form-control" id= "location_country" name= "location_country">
<option value="" selected="true">Please Select...</option>
@foreach ($countries as $key => $value)
<option value="{{ $key }}">{{ $value }}</option>
@endforeach
</select>
</div>

</div>

<div class="col-md-6">

<div class="form-group">
<label for="Pincode">Pincode</label><a data-tooltip="Location Pincode">
<i class="fa fa-exclamation-circle"></i></a>
<input type="input" class="form-control" id="pincode" name ="pincode" placeholder="Pincode">
</div>

</div>






</div>

<!-- <div class="row">
<div class="col-md-12">
<div class="form-group">
<label for="Landmark">Landmark</label>
<input type="input" class="form-control" id="Height" placeholder="Landmark">
</div>
</div>
</div> -->

<div class="row">


<div class="col-md-12">

<div class="form-group">
<label for="State">State</label><a data-tooltip="Location State">
<i class="fa fa-exclamation-circle"></i></a>
<select class="form-control" id= "state" name = "state">
<option value="" selected="true">Please Select...</option>
</select>
</div>

</div>








</div>

<div class="row">


<div class="col-md-12">

<div class="form-group">
<label for="City">City</label><a data-tooltip="Location City">
<i class="fa fa-exclamation-circle"></i></a>
<!-- <select class="form-control">
<option>India </option>
<option>USA</option>
</select> -->
<input type="input" class="form-control" id="city" name="city" placeholder="City">
</div>

</div>








</div>

<div class="row">


<div class="col-md-6">

<div class="form-group">
<label for="Latitude">Latitude</label><a data-tooltip="Latitude of Location">
<i class="fa fa-exclamation-circle"></i></a>
<input type="input" class="form-control" id="latitude" name="latitude" placeholder="Latitude">
</div>

</div>

<div class="col-md-6">

<div class="form-group">
<label for="Longitude">Longitude</label><a data-tooltip="Longitude of Location">
<i class="fa fa-exclamation-circle"></i></a>
<input type="input" class="form-control" id="longitude"  name="longitude" placeholder="Longitude">
</div>

</div>






</div>
<div class="row">
<div class="col-md-6">
<div class="form-group">
<label for="is_dc">IS Dc?</label><a data-tooltip="Select as DC ">
<i class="fa fa-exclamation-circle"></i></a>
 <input type="checkbox" value="1" id="dc_flag" name="dc_flag" parsley-group="mygroup" parsley-trigger="change" parsley-required="true" parsley-mincheck="2" parsley-error-container="#myproperlabel .last" class="parsley-validated">
</div>
</div>
</div>
<div class="row" id="loc-dc-map">

<div class="checkbox" id ="cust-dc-div">
<div class="col-md-12">
<div class="box-header">
<h3 class="box-title">Select Channel</h3>
</div>
<!-- <label class="checkpadright"><input type="checkbox"> Flipkart</label>
<label class="checkpadright"><input type="checkbox"> Amazon</label>
<label class="checkpadright"><input type="checkbox"> Snapdeal</label>
<label class="checkpadright"><input type="checkbox"> eBay</label> -->
@if(isset($custChannelsDc) && !empty($custChannelsDc))
@foreach($custChannelsDc as $key=>$value)
<label class="checkpadright"><input type="checkbox" id="dcChannels[]" name="dcChannels[]" value="{{$value->channel_id}}" 
checked="true">{{$value->channnel_name}}</label>
@endforeach
@endif
</div>




</div>

</div>


<div class="row">
<div class="col-md-12 text-center">
<div class="btn-group">
<button type="submit" class="btn btn-success btn-flat margin" id ="dc-save">Submit</button>
</div>

</div>
</div>






</div>
</div>
</div>
</div>
</div>


<!-- </div> -->


</div>
{{ Form::close() }}
</br> 
<div id="showDcgrid">
</div>
</br> 
<!-- <div class="row" style="margin-top:15px;">
<div class="col-md-12 table-responsive">
<table class="table table-striped" id="custdc">
<thead>
<tr>
<th>Country</th>
<th>Location Name</th>
<th>Location Type</th>
<th>Location Address</th>
</tr>
</thead>
<tbody id="custdc-body">

</tbody>
</table>
</div>
</div> -->

<div class="row">
<div class="col-md-12" style="text-align:right;">
<div class="btn-group">
<button type="button" class="btn bg-navy btn-flat" id="dc-next">Next</button>
</div>
</div>
</div>

</section>
</div>
</div>
</div>


<div class="panel panel-default" id="acc6">

<a role="button" data-toggle="collapse" data-parent="#accordion" href="#collapseSix" aria-expanded="true" aria-controls="collapseSix">
<div class="panel-heading panel-new" role="tab" id="headingSix">
<h4 class="panel-title">
Product Setup
</h4>
</div>
</a>


<div id="collapseSix" class="panel-collapse collapse" role="tabpanel" aria-labelledby="headingOne">
<div class="panel-body">
<section class="content">


<div class="row">
<div class="col-lg-6">
<div class="margin">
<div class="btn-group">

<button type="button" class="btn bg-primary margin"data-toggle="modal" data-target="#basicvalCodeModal" >Add Products</button>
</div>
<div class="btn-group">
<button type="button" class="btn bg-primary btn-flat margin">Import from ERP</button>
</div>
<div class="btn-group">
<button type="button" class="btn bg-primary btn-flat margin">Import from CSV</button>
</div>

</div>
</div>

</div>


<div class="modal fade" id="basicvalCodeModal" tabindex="-1" role="dialog" aria-labelledby="basicvalCode" aria-hidden="true">
            <div class="modal-dialog wide">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                        <h4 class="modal-title" id="basicvalCode">Add New Product</h4>
                    </div>
                    <div class="modal-body">
                        {{ Form::open(array('url' => 'signup/saveProduct','id'=>'addproduct')) }}
                        {{ Form::hidden('_method', 'POST') }}
                                  <div class="row">
                                  <div class="form-group col-sm-4" >
                                                        <label for="exampleInputPassword1">Product Image*</label>
                                                        <div class="image-block">
                                                        </div>
                                                        <div class="input-group input-group-sm">
                                                            <span class="btn btn-success fileinput-button">
                                                                <i class="glyphicon glyphicon-plus"></i>
                                                                <span>Upload Image*...</span>
                                                                <input id="fileupload" type="file" name="files[]" multiple>
                                                            </span>
                                                        </div>
                                                        <div class="input-group-sm">
                                                            <table role="presentation" class="table table-striped"><tbody id="files" class="files"></tbody></table>
                                                        </div>
                                                    </div>
                                        </div>
                              <div class="row">
                         
                          <div class="form-group col-sm-6">
                            <label for="exampleInputEmail">Title</label>
                            <div class="input-group ">
                              <span class="input-group-addon addon-red"><i class="ion-arrow-shrink"></i></span>
                              <input type="text"  id="title" name="title"  placeholder="title" class="form-control" >
                          </div>
                           </div>
                         </div> 
                            <div class="row">
                         
                          <div class="form-group col-sm-6">
                            <label for="exampleInputEmail">Description</label>
                            <div class="input-group ">
                              <span class="input-group-addon addon-red"><i class="ion-arrow-shrink"></i></span>
                              <input type="text"  id="description" name="description"  placeholder="description" class="form-control" >
                          </div>
                           </div>
                         </div>  

                           <div class="row">
                         
                          <div class="form-group col-sm-6">
                            <label for="exampleInputEmail">Sku</label>
                            <div class="input-group ">
                              <span class="input-group-addon addon-red"><i class="ion-arrow-shrink"></i></span>
                              <input type="text"  id="sku" name="sku"  placeholder="sku" class="form-control" >
                          </div>
                           </div>
                         </div>  

                             <div class="row">
                         
                          <div class="form-group col-sm-6">
                            <label for="exampleInputEmail">Category</label>
                            <div class="input-group ">
                              <span class="input-group-addon addon-red"><i class="ion-arrow-shrink"></i></span>
                              <input type="text"  id="category" name="category"  placeholder="category" class="form-control" >
                          </div>
                           </div>
                         </div>        
                         
                          <div class="row">
                         
                          <div class="form-group col-sm-6">
                            <label for="exampleInputEmail">Price</label>
                            <div class="input-group ">
                              <span class="input-group-addon addon-red"><i class="ion-arrow-shrink"></i></span>
                              <input type="text"  id="price" name="price"  placeholder="price" class="form-control" >
                          </div>
                           </div>
                         </div> 
                       
                        {{ Form::submit('Submit', array('class' => 'btn btn-primary')) }}
                        {{ Form::close() }}

                    </div>
                </div><!-- /.modal-content -->
            </div><!-- /.modal-dialog -->
        </div><!-- /.modal -->
<div class="row" id="prod-box" >
<input type="hidden" id="latest_count">
<input type="hidden" id="prod-tot-count">
</div>
<!-- <div class="row" id="prod-grid"> -->
  <div class="col-lg-4" id="prod-details" style="display:none;">
  <input type="hidden" id="product_id" name="product_id">
    <div class="productbox">
      <div class="row producttopbar">
        <div class="col-md-6 col-xs-6">
          <div class="form-group productchkbox"><div class="checkbox"><label><input type="checkbox"></label></div></div>
        </div>
        <div class="col-md-6 col-xs-6 pull-right">
          <div class="form-group productchkdetalinks">
            <a class="productmenu" data-tooltip="Click here to view the description" data-toggle="modal" href="#Description">
            <i class="fa fa-eye"></i>
            </a>
          </div>
          <div class="bs-example">
            <div class="modal fade" id="Description">
              <div tabindex="-1" id="myModal" class="modal-dialog">
                <div class="modal-content">
                  <div class="modal-header">
                    <button aria-hidden="true" data-dismiss="modal" class="close" type="button">×</button>
                    <h4 class="modal-title">Product Details</h4>
                  </div>
                  <div class="modal-body">
                    <div class="row">
                      <div class="col-md-12">
                        <img class="img-responsive" src="img/productdiscimg.png">
                        <h2>Orient 48 Summercool Ceiling Fan Brown</h2>
                        <h3>(White Silver)</h3>
                        <hr>
                        <p class="padleft">
                        <a href="#"><i class="fa fa-star text-yellow"></i></a>
                        <a href="#"><i class="fa fa-star text-yellow"></i></a>
                        <a href="#"><i class="fa fa-star text-yellow"></i></a>
                        <a href="#"><i class="fa fa-star text-gray"></i></a>
                        <a href="#"><i class="fa fa-star text-gray"></i></a>
                        <span>Have a question? <small>Details</small></span>
                        </p>
                        <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod 
                        tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, 
                        quis nostrud exercitation ullamco laboris Ut enim ad minim veniam, 
                        quis nostrud exercitation ullamco laboris </p>
                        <div class="row">
                          <div class="col-md-8 prices">
                            <p class="mrp">M.R.P. : 2,945.00</p>
                            <p>Sale : 2,145.00</p>
                            <p class="margn"><big>Margin : 800.00</big> <small>Inclusive of all taxes</small></p>
                          </div>
                        </div>
                        <div class="clearfix"></div>
                        <div class="row addresses">
                          <div class="col-md-12 ">
                            <address>
                            <strong>Shipping, Delivery &amp; Returns</strong><br>
                            Ships in 24 hours:<br>
                            VAT/Shipping Charges may be applicable <br>
                            Return within 30 days  ?
                            </address>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="row">
        <div class="col-md-8 col-xs-6 col-md-offset-2 col-xs-offset-2 text-center imgresborder">
          <a data-target="#lightbox" data-toggle="modal" class="thumbnail" href="#"> 
          <img class="img-responsive" id = "product-image">
          </a>
        </div>
        <div aria-hidden="true" aria-labelledby="myLargeModalLabel" role="dialog" tabindex="-1" class="modal fade" id="lightbox">
          <div class="modal-dialog modal-dialog_lightbox">
            <button aria-hidden="true" data-dismiss="modal" class="close hidden" type="button">×</button>
            <div class="modal-content">
              <div class="modal-body modal-body_lightbox">
                <img class="img-responsive" src="img/fan.png">
              </div>
            </div>
          </div>
        </div>
        <div class="col-md-2 col-xs-4 ">
          <label class="switch">
            <input type="checkbox" id="prod_gds" onclick="disableElements()" class="switch-input">
            <span class="switch-label"></span> 
          </label>
        </div>
      </div>
      <div class="row invoice-info productdetails">
        <div class="col-sm-6 col-xs-6 invoice-col">
          <!-- <p>Name:<br>Type:<br>SKU:<br>Manufacturer:<br>Price:<br></p> -->
          <p>Name:<br>Type:<br>SKU:<br>Price:<br></p>
        </div>
        <div class="col-sm-6 col-xs-6 invoice-col" id="prod-grid">
          <p>Orient Fan<br>Finished Product<br>Sku-487<br>Orient<br>$4.00<br></p>
        </div>
      </div>
      <div class="row invoice-info productdetails" id="prod-gds-channels">
        <div class="col-md-4  col-xs-12"><p><strong>Channels</strong><br></p></div>
        <div id="imageset" class="col-md-6 col-xs-8"><!-- <p> -->
<!--           <a href="#"><img src="img/products1.png">  </a>
          <a href="#"><img src="img/products2.png">  </a>
          <a href="#"><img src="img/products3.png">  </a> -->
          <div class="row" id="channels-images">
          </div>
          <!-- <br></p> -->
        </div>
        <div class="col-md-2 col-xs-4">
          <a class="productmenu" data-tooltip="Click here too ..." onclick="showHide()"><i class="fa fa-chevron-down"></i></a>
        </div>
      </div>
      <div class="row">
        <div class="col-md-12">
          <div style="display:none; border-top:1px solid #efefef; padding:10px;" id="myimageDiv"> 
            <a href="#"><img src="img/products1.png" id="myimage1"> </a>
            <a href="#"><img src="img/products2.png" id="myimage2">  </a>
            <a href="#"><img src="img/products3.png" id="myimage3">  </a>
            <a href="#"><img src="img/products1.png" id="myimage4"> </a>
            <a href="#"><img src="img/products2.png" id="myimage5">  </a>
            <a href="#"><img src="img/products3.png" id="myimage6">  </a>
            <a href="#"><img src="img/products1.png" id="myimage7"> </a>
            <a href="#"><img src="img/products2.png" id="myimage8">  </a>
            <a href="#"><img src="img/products3.png" id="myimage9">  </a>
            <a href="#"><img src="img/products1.png" id="myimage10"> </a>
            <a href="#"><img src="img/products2.png" id="myimage11">  </a>
            <a href="#"><img src="img/products3.png" id="myimage12">  </a>
          </div>
        </div>
      </div>
      <div style="margin-top:20px;" class="row">
        <div class="col-md-12 text-center" id="edit-delete-prod">
          <a onclick="editProduct()"><span class="badge bg-light-blue" ><i class="fa fa-pencil"></i></span></a>
          <a href="#"><span class="badge bg-red"><i class="fa fa-trash-o"></i></span></a>
          <a data-toggle="modal" data-tooltip="Click here too add new channels" href="#AddChannel"><span class="badge bg-green"><i class="fa fa-plus"></i></span></a>
        </div>
        <div class="bs-example">
          <div class="modal fade" id="address-model">
            <div class="modal-dialog modal-dialog1">
              <div class="modal-content">
                <div class="modal-header">
                  <button aria-hidden="true" data-dismiss="modal" class="close" type="button">×</button>
                  <h4 class="modal-title">Product Details</h4>
                </div>
                <div class="modal-body modal-body1">
                  <div class="row">
                    <div class="col-md-12">
                      <h2>Orient 48 Summercool Ceiling Fan Brown</h2>
                      <h3>(White Silver)</h3>
                      <hr>
                      <p class="padleft">
                      <a href="#"><i class="fa fa-star text-yellow"></i></a>
                      <a href="#"><i class="fa fa-star text-yellow"></i></a>
                      <a href="#"><i class="fa fa-star text-yellow"></i></a>
                      <a href="#"><i class="fa fa-star text-gray"></i></a>
                      <a href="#"><i class="fa fa-star text-gray"></i></a>
                      <span>Have a question? <small>Details</small></span>
                      </p>
                      <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod 
                      tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, 
                      quis nostrud exercitation ullamco laboris Ut enim ad minim veniam, 
                      quis nostrud exercitation ullamco laboris </p>
                      <div class="row">
                        <div class="col-md-8 prices">
                          <p class="mrp">M.R.P. : 2,945.00</p>
                          <p>Sale : 2,145.00</p>
                          <p class="margn"><big>Margin : 800.00</big> <small>Inclusive of all taxes</small></p>
                        </div>
                      </div>
                      <div class="clearfix"></div>
                      <div class="row addresses">
                        <div class="col-md-12 ">
                          <address>
                          <strong>Shipping, Delivery &amp; Returns</strong><br>
                          Ships in 24 hours:<br>
                          VAT/Shipping Charges may be applicable <br>
                          Return within 30 days  ?
                          </address>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="bs-example">
          <div class="modal fade" id="AddChannel">
            <div class="modal-dialog">
              <div class="modal-content modal-content-channels">
                <div class="modal-body modal-body-channels">
                  <div class="row">
                    <div class="col-md-2 col-xs-2"><label class="checkpadright"><input type="checkbox"></label></div>
                    <div class="col-md-4 col-xs-4"><img src="img/products1.png"></div>
                    <div class="col-md-6 col-xs-6">Amozon</div>
                  </div>
                  <div class="row">
                    <div class="col-md-2 col-xs-2"><label class="checkpadright"><input type="checkbox"></label></div>
                    <div class="col-md-4 col-xs-4"><img src="img/products2.png"></div>
                    <div class="col-md-6 col-xs-6">Snapdeal</div>
                  </div>
                  <div class="row">
                    <div class="col-md-2 col-xs-2"><label class="checkpadright"><input type="checkbox"></label></div>
                    <div class="col-md-4 col-xs-4"><img src="img/products3.png"></div>
                    <div class="col-md-6 col-xs-6">Flipkart</div>
                  </div>
                  <div class="row">
                    <div class="col-md-2 col-xs-2"><label class="checkpadright"><input type="checkbox"></label></div>
                    <div class="col-md-4 col-xs-4"><img src="img/products1.png"></div>
                    <div class="col-md-6 col-xs-6">Amozon</div>
                  </div>
                  <div class="row">
                    <div class="col-md-2 col-xs-2"><label class="checkpadright"><input type="checkbox"></label></div>
                    <div class="col-md-4 col-xs-4"><img src="img/products2.png"></div>
                    <div class="col-md-6 col-xs-6">Snapdeal</div>
                  </div>
                  <div class="row">
                    <div class="col-md-2 col-xs-2"><label class="checkpadright"><input type="checkbox"></label></div>
                    <div class="col-md-4 col-xs-4"><img src="img/products3.png"></div>
                    <div class="col-md-6 col-xs-6">Flipkart</div>
                  </div>
                  <div class="row">
                    <div class="col-md-2 col-xs-2"><label class="checkpadright"><input type="checkbox"></label></div>
                    <div class="col-md-4 col-xs-4"><img src="img/products1.png"></div>
                    <div class="col-md-6 col-xs-6">Amozon</div>
                  </div>
                  <div class="row">
                    <div class="col-md-2 col-xs-2"><label class="checkpadright"><input type="checkbox"></label></div>
                    <div class="col-md-4 col-xs-4"><img src="img/products2.png"></div>
                    <div class="col-md-6 col-xs-6">Snapdeal</div>
                  </div>
                  <div class="row">
                    <div class="col-md-2 col-xs-2"><label class="checkpadright"><input type="checkbox"></label></div>
                    <div class="col-md-4 col-xs-4"><img src="img/products3.png"></div>
                    <div class="col-md-6 col-xs-6">Flipkart</div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
<!-- </div> -->

<div class="row">
<div class="col-md-6" style="text-align:right;">
<div class="btn-group">
<button type="button" class="btn bg-navy btn-flat" id="prod-get">Load more</button>
</div>
</div>
<div class="col-md-6" style="text-align:right;">
<div class="btn-group">
<button type="button" class="btn bg-navy btn-flat" id="prod-next">Next</button>
</div>
</div>
</div>
</section>
</div>
</div>
</div>


<!-- <div class="panel panel-default" id="acc7">

<a role="button" data-toggle="collapse" data-parent="#accordion" href="#collapseSeven" aria-expanded="true" aria-controls="collapseSeven">
<div class="panel-heading panel-new" role="tab" id="headingSeven">
<h4 class="panel-title">
Logistics Setup
</h4>
</div>
</a>


<div id="collapseSeven" class="panel-collapse collapse" role="tabpanel" aria-labelledby="headingOne">
<div class="panel-body">
<section class="content">

    {{ Form::open(array('url' => '/saveCustLogistics','id'=>'saveSignup')) }}
    {{ Form::hidden('_method', 'POST') }}
<div class="row">
<input type="hidden" id="mfg_channel_count" name="mfg_channel_count" value="">
<input type="hidden" id="mfg_id" name="mfg_id" value="">
<label for="Select Own">Select Logistics</label>
<div class="checkbox row" id="logistics-div">
@foreach($logistics as $key =>$value)
<label class="checkpadright"><input type="checkbox" value ="{{$value->carrier_id}}" id= "carrier[]" name = "carrier[]"> {{$value->name}}</label>
@endforeach
</div>
</div>
<div class="row">
<div class="col-md-12" style="text-align:right;">
<div class="btn-group">
<button type="submit" class="btn bg-navy btn-flat" id="logistics-save">Next</button>
</div>
</div>
</div>
{{ Form::close() }}

</section>
</div>
</div>
</div> -->


<div class="panel panel-default" id="acc8">

<a role="button" data-toggle="collapse" data-parent="#accordion" href="#collapseEight" aria-expanded="true" aria-controls="collapseEight">
<div class="panel-heading panel-new" role="tab" id="headingeight">
<h4 class="panel-title">
Finance Setup
</h4>
</div>
</a>


<div id="collapseEight" class="panel-collapse collapse" role="tabpanel" aria-labelledby="headingOne">
<div class="panel-body">



<section class="content">

{{ Form::open(array('url' => '/saveCustFinances','id'=>'saveCustFinances')) }}
{{ Form::hidden('_method', 'POST') }}
<div class="row">

<div class="col-md-6">
<div class="form-group">
<label for="VAT/CST Number">VAT/CST Number</label>
<!-- <input type="input" class="form-control" id="vat_number" name="vat_number" placeholder="VAT/CST Number"> -->
@if(isset($finance->vat_number) && !empty($finance->vat_number))
<input type="input" class="form-control" id="vat_number" value="{{$finance->vat_number}}" number="vat_number" placeholder="VAT/CST Number">
@else
<input type="input" class="form-control" id="vat_number" number="vat_number" placeholder="VAT/CST Number">
@endif
</div>
</div>

<div class="col-md-6">
<div class="form-group">
<label for="Bank Name" >Bank Name</label>
@if(isset($finance->bank_name) && !empty($finance->bank_name))
 <select class="form-control" id="bank_name" name="bank_name">
 @foreach($bank as $key => $pair)
   @if($pair->bank_id == $finance->bank_name)
    <option value ="{{$pair->bank_id}}" selected="true">{{$pair->bankname}}</option>
  @else
   <option value="{{$pair->bank_id}}">{{$pair->bankname}}</option>
   @endif
  @endforeach
 </select>
@else
<select class="form-control" id="bank_name" name="bank_name">
  <option value="">Please Select</option>
   @foreach($bank as $key => $pair)
    <option value="{{$pair->bank_id}}">{{$pair->bankname}}</option>
   @endforeach
   </select>
@endif
</div>
</div>

</div>

<div class="row">

<div class="col-md-6">
<div class="form-group">
<label for="Beneficiary Name">Beneficiary Name</label>
<!-- <input type="input" class="form-control" id="benf_name" name="benf_name" placeholder="Beneficiary Name"> -->
@if(isset($finance->benf_name) && !empty($finance->benf_name))
<input type="input" class="form-control" value="{{$finance->benf_name}}" id="benf_name" name="benf_name" placeholder="Beneficiary Name">
@else
<input type="input" class="form-control" id="benf_name" name="benf_name" placeholder="Beneficiary Name">
@endif
</div>
</div>

<div class="col-md-6">
<div class="form-group">
<label for="Account Number">Account Number</label>
<!-- <input type="input" class="form-control" id="acc_number" name="acc_number" placeholder="Account Number"> -->
@if(isset($finance->acc_number) && !empty($finance->acc_number))
<input type="input" class="form-control" value="{{$finance->acc_number}}" id="acc_number" name="acc_number" placeholder="Account Number">
@else
<input type="input" class="form-control" id="acc_number" name="acc_number" placeholder="Account Number">
@endif
</div>
</div>

</div>

<div class="row">

<div class="col-md-6">
<div class="form-group">
<label for="Account Type">Account Type</label>
<!-- <select class="form-control" name="acc_type" id="acc_type">
<option value ="1">Current Account</option>
<option value ="2">Savings Account</option>
</select> -->
@if(isset($finance->acc_type) && !empty($finance->acc_type))
<select class="form-control" name="acc_type" id="acc_type">
  @if($finance->acc_type == 1)
  <option value ="1" selected="selected">Current Account</option>
  <option value ="2">Savings Account</option>
  @elseif($finance->acc_type == 2)
  <option value ="2" selected="selected">Savings Account</option>
  <option value="1">Current Account</option>
  @else
  <option value="" selected="selected">Please Select</option>
  <option value="1">Current Account</option>
  <option value ="2">Savings Account</option>
  @endif
</select>
@else
 <select class="form-control" name="acc_type" id="acc_type">
   <option value="">Please Select</option>
   <option value ="1">Current Account</option>
   <option value ="2">Savings Account</option>
 </select>
@endif
</div>
</div>

<div class="col-md-6">
<div class="form-group">
<label for="IFSC Code">IFSC Code</label>
<!-- <input type="input" class="form-control" id="ifsc_code" name="ifsc_code" placeholder="IFSC Code"> -->
@if(isset($finance->ifsc_code) && !empty($finance->ifsc_code))
<input type="input" class="form-control" value="{{$finance->ifsc_code}}" id="ifsc_code" name="ifsc_code" placeholder="IFSC Code">
@else
<input type="input" class="form-control" id="ifsc_code" name="ifsc_code" placeholder="IFSC Code">
@endif
</div>
</div>

</div>

<div class="row">
<div class="col-md-6">
<div class="form-group">
<label for="Company PAN Number">MICR Code</label>
<!-- <input type="input" class="form-control" id="micr_code" name = "micr_code" placeholder="MICR Code"> -->
@if(isset($finance->micr_code) && !empty($finance->micr_code))
<input type="input" class="form-control" id="micr_code" value="{{$finance->micr_code}}" name = "micr_code" placeholder="MICR Code">
@else
 <input type="input" class="form-control" id="micr_code" name = "micr_code" placeholder="MICR Code">
@endif
</div>
</div>
<div class="col-md-6">
<div class="form-group">
<label for="Currency">Currency</label>
@if(isset($finance->currency) && !empty($finance->currency))
<select class="form-control" id="currency" name="currency">
    @foreach($currency as $key => $value)
       @if($value->currency_id == $finance->currency)
       <option value="{{$value->currency_id}}" selected="true">{{$value->code}}</option>
       @else
       <option value="{{$value->currency_id}}">{{$value->code}}</option>
       @endif
    @endforeach
</select>
@else
<select class="form-control" id="currency" name="currency">
  <option value="">Please Select</option>
    @foreach($currency as $key => $value)
      <option value="{{$value->currency_id}}">{{$value->code}}</option>
    @endforeach
</select>
@endif
</div>
</div>

</div>



<!-- <div class="row">
<div class="col-md-12">
<label for="Width">Upload Documents</label>
<input type="file" multiple name="files[]" id="input1">
</div>
</div> -->




<div class="row">
<div class="col-md-12" style="text-align:right;">
<div class="btn-group">
<!--<button type="button" class="btn bg-navy btn-flat">Finish</button>-->
<button type="submit" id="finance-save" class="btn bg-navy btn-flat">Finish</button>
</div>
</div>
</div>
{{ Form::close() }}

</section>



</div>
</div>
</div>



</div>


</section>


</div>
<!-- /.Erp Import -->
        <!-- Modal -->
        <div class="modal fade" id="import_from_erp" tabindex="-1" role="dialog" aria-labelledby="basicvalCode" aria-hidden="true">
            <div class="modal-dialog wide">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                        <h4 class="modal-title" id="basicvalCode">Import Locations from ERP</h4>
                    </div>
                    <div class="modal-body">
                        {{ Form::open(array('url' => '/customer/savelocationfromerp', 'id' => 'import_locations_from_erp' )) }}
                        {{ Form::hidden('_method','POST') }}
                        <input type="hidden" name="manufacturer_id" value="{{ $company->customer_id }}" />
                        <div class="row">
                            <div class="form-group col-sm-6">
                                <div class="checkbox">
                                    <input type="checkbox" id="vendor_supplier" name="location_type[]" value="vendor" parsley-group="mygroup" parsley-trigger="change" parsley-required="true" parsley-mincheck="2" parsley-error-container="#myproperlabel .last" class="parsley-validated">
                                    <label for="vendor_supplier">Vendor/Supplier</label>
                                </div>
                            </div>
                            <div class="form-group col-sm-6">
                                <label for="Location Types">Location Types</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
                                    <select name="location_type_id_vendor" id="location_type_id_vendor"  class="chosen-select form-control parsley-validated"  >
                                        @foreach($locationTypes as  $loc)
                                            <option value="{{ $loc->location_type_id }}">{{ $loc->location_type_name }}</option>
                                        @endforeach
                                    </select>
                                    <input type="hidden" id="location_type_manufacturer_id" value="{{ $company->customer_id }}" />
                                </div>                                            
                            </div>
                        </div>
                        <div class="row">
                            <div class="form-group col-sm-6">
                                <div class="checkbox">
                                    <input type="checkbox" id="Plant" name="location_type[]" value="plant" parsley-group="mygroup" parsley-trigger="change" parsley-required="true" parsley-mincheck="2" parsley-error-container="#myproperlabel .last" class="parsley-validated">
                                    <label for="Plant">Plant</label>
                                </div>
                            </div>
                            <div class="form-group col-sm-6">
                                <label for="Location Types">Location Types</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
                                    <select name="location_type_id_plant" id="location_type_id_plant"  class="chosen-select form-control parsley-validated"  >
                                        @foreach($locationTypes as  $loc)
                                            <option value="{{ $loc->location_type_id }}">{{ $loc->location_type_name }}</option>
                                        @endforeach
                                    </select>
                                    <input type="hidden" id="location_type_manufacturer_id" value="{{ $company->customer_id }}" />
                                </div>                                            
                            </div>
                        </div>
                        <div class="row">
                            <div class="form-group col-sm-6">
                                <div class="checkbox">
                                    <input type="checkbox" id="customer" name="location_type[]" value="customer" parsley-group="mygroup" parsley-trigger="change" parsley-required="true" parsley-mincheck="2" parsley-error-container="#myproperlabel .last" class="parsley-validated">
                                    <label for="customer">Customer</label>
                                </div>
                            </div>
                            <div class="form-group col-sm-6">
                                <label for="Location Types">Location Types</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-addon addon-red"><i class="ion-ios-location"></i></span>
                                    <select name="location_type_id_customer" id="location_type_id_customer"  class="chosen-select form-control parsley-validated"  >
                                        @foreach($locationTypes as  $loc)
                                            <option value="{{ $loc->location_type_id }}">{{ $loc->location_type_name }}</option>
                                        @endforeach
                                    </select>
                                    <input type="hidden" id="location_type_manufacturer_id" value="{{ $company->customer_id }}" />
                                </div>                                            
                            </div>
                        </div>
                        {{ Form::submit('Submit', array('class' => 'btn btn-primary', 'id' => 'import_locations_from_erp_button')) }}
                        {{ Form::close() }}
                    </div>
                </div><!-- /.modal-content -->
            </div><!-- /.modal-dialog -->
        </div><!-- /.modal -->
<!-- /.Erp Import -->
<!-- Add Location Types from Excel -->
    <!-- Modal -->
    <div class="modal fade" id="location_types_add_excel" tabindex="-1" role="dialog" aria-labelledby="basicvalCode" aria-hidden="true">
        <div class="modal-dialog wide">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                    <h4 class="modal-title" id="basicvalCode">Add Location Type from CSV</h4>
                </div>
                <div class="modal-body">
                    <div id="update_import_locations_message"></div>
                    {{ Form::open(array('url' => '/customer/savelocationtypefromexcel', 'id' => 'add_locationtypes_form_excel', 'files'=>'true' )) }}
                    {{ Form::hidden('_method','POST') }}

                    <div class="form-group ">
                            <!--<label class="col-sm-2 control-label"></label>-->
                      <div class="col-xs-6 col-sm-4"> <a href="/customer/download/Locations"  style="margin-top:0px;"><i class="fa fa-download"></i> Download sample file </a></div>
                      <div class="col-xs-6 col-sm-5">
                               <!--  <span class="btn btn-success fileinput-button">
                                    <i class="glyphicon glyphicon-plus"></i>
                                    <span>Import from CSV</span>     -->                                
                                    <input id="locations_fileupload" required type="file" name="files">
                                    <input type="hidden" name="manufacturerID" value="{{ $company->customer_id }}"/>                               
                                
                            </div> 
                     
                            
                           <div class="col-xs-6 col-sm-3"> {{ Form::submit('Upload File', array('class' => 'btn btn-primary', 'id' => 'add_locationtypes_excel_button')) }}
                    {{ Form::close() }}</div>
                    </div>
                   <br><br>
                   
                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->    
<!-- Add Location Types from Excel --> 


<!--edit GDSProduct-->
 <button data-toggle="modal" id="editEntity" class="btn btn-default" data-target="#prod-basicvalCodeModal" style="display: none" data-url="{{URL::asset('product/editgdsproduct')}}"></button>
<div class="modal fade" id="prod-basicvalCodeModal" tabindex="-1" role="dialog" aria-labelledby="basicvalCode" aria-hidden="true">
  <div class="modal-dialog wide">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" id="close_it_now" data-dismiss="modal" aria-hidden="true">X</button>
        <h4 class="modal-title" id="prod-basicvalCode">Add Entity</h4>
      </div>
        <div class="modal-body" id="entitiesDiv">
        </div>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<!--edit GDSProduct-->










</section>
</div>
<footer class="main-footer">
<strong>Copyright &copy; 2014-2015 <a href="#">eSealCentral</a>.</strong> All rights reserved.
</footer>

</div>




<script type="text/javascript">
/*$('#myModal').modal({keyboard: true})*/
</script>


 <script>
function changeImage() {
    var image = document.getElementById('myImage');
    if (image.src.match("bulbon")) {
        image.src = "img/products1d.png";
    } else {
        image.src = "img/products1.png";
    }
}
</script>

  <script src="js/bootstrap-select.js"></script>


<!-- jQuery 2.1.4 -->
<!-- Bootstrap 3.3.2 JS -->
<script src="js/bootstrap.min.js" type="text/javascript"></script>
<script src="js/bootstrapValidator.js" type="text/javascript"></script>


<script type="text/javascript"> 
$("#myform :input").tooltip({
 
      // place tooltip on the right edge
      position: "center right",
 
      // a little tweaking of the position
      offset: [-2, 10],
 
      // use the built-in fadeIn/fadeOut effect
      effect: "fade",
 
      // custom opacity setting
      opacity: 0.7
 
      });
</script>

<script type="text/javascript">
        $(document).ready(function () {
            window.asd = $('.SlectBox').SumoSelect({ csvDispCount: 3 });
            window.test = $('.testsel').SumoSelect({okCancelInMulti:true });
            window.testSelAll = $('.testSelAll').SumoSelect({okCancelInMulti:true, selectAll:true });
            window.testSelAll2 = $('.testSelAll2').SumoSelect({selectAll:true });
        });
    </script>
<script src="select/jquery.sumoselect.js"></script>


<script type="text/javascript" src="js/bootstrap-datetimepicker.js" charset="UTF-8"></script>
<script type="text/javascript" src="js/bootstrap-datetimepicker.fr.js" charset="UTF-8"></script>
    {{HTML::style('jqwidgets/styles/jqx.base.css')}}
    {{HTML::style('css/easy-responsive-tabs.css')}} 
    {{HTML::script('js/easyResponsiveTabs.js')}}
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

<script type="text/javascript">
    $('.form_datetime').datetimepicker({
        //language:  'fr',
        weekStart: 1,
        todayBtn:  1,
    autoclose: 1,
    todayHighlight: 1,
    startView: 2,
    forceParse: 0,
        showMeridian: 1
    });
  $('.form_date').datetimepicker({
        language:  'fr',
        weekStart: 1,
        todayBtn:  1,
    autoclose: 1,
    todayHighlight: 1,
    startView: 2,
    minView: 2,
    forceParse: 0
    });
  $('.form_time').datetimepicker({
        language:  'fr',
        weekStart: 1,
        todayBtn:  1,
    autoclose: 1,
    todayHighlight: 1,
    startView: 1,
    minView: 0,
    maxView: 1,
    forceParse: 0
    });
</script>




  

<script>
function disableElements()
{
document.getElementById("imageset").disabled=true;
}
</script>

<script type="text/javascript" src="js/jquery.filer.min.js"></script>
<script type="text/javascript">
  $(document).ready(function() {
        $('#input1').filer();
        
        $('.file_input').filer({
            showThumbs: true,
            templates: {
                box: '<ul class="jFiler-item-list"></ul>',
                item: '<li class="jFiler-item">\
                            <div class="jFiler-item-container">\
                                <div class="jFiler-item-inner">\
                                    <div class="jFiler-item-thumb">\
                                        <div class="jFiler-item-status"></div>\
                                        <div class="jFiler-item-info">\
                                            <span class="jFiler-item-title"><b title="@{{fi-name}}">@{{fi-name | limitTo: 25}}</b></span>\
                                        </div>\
                                        @{{fi-image}}\
                                    </div>\
                                    <div class="jFiler-item-assets jFiler-row">\
                                        <ul class="list-inline pull-left">\
                                            <li><span class="jFiler-item-others">@{{fi-icon}} @{{fi-size2}}</span></li>\
                                        </ul>\
                                        <ul class="list-inline pull-right">\
                                            <li><a class="icon-jfi-trash jFiler-item-trash-action"></a></li>\
                                        </ul>\
                                    </div>\
                                </div>\
                            </div>\
                        </li>',
                itemAppend: '<li class="jFiler-item">\
                            <div class="jFiler-item-container">\
                                <div class="jFiler-item-inner">\
                                    <div class="jFiler-item-thumb">\
                                        <div class="jFiler-item-status"></div>\
                                        <div class="jFiler-item-info">\
                                            <span class="jFiler-item-title"><b title="@{{fi-name}}">@{{fi-name | limitTo: 25}}</b></span>\
                                        </div>\
                                        @{{fi-image}}\
                                    </div>\
                                    <div class="jFiler-item-assets jFiler-row">\
                                        <ul class="list-inline pull-left">\
                                            <span class="jFiler-item-others">@{{fi-icon}} @{{fi-size2}}</span>\
                                        </ul>\
                                        <ul class="list-inline pull-right">\
                                            <li><a class="icon-jfi-trash jFiler-item-trash-action"></a></li>\
                                        </ul>\
                                    </div>\
                                </div>\
                            </div>\
                        </li>',
                progressBar: '<div class="bar"></div>',
                itemAppendToEnd: true,
                removeConfirmation: true,
                _selectors: {
                    list: '.jFiler-item-list',
                    item: '.jFiler-item',
                    progressBar: '.bar',
                    remove: '.jFiler-item-trash-action',
                }
            },
            addMore: true,
            files: [{
                name: "appended_file.jpg",
                size: 5453,
                type: "image/jpg",
                file: "http://dummyimage.com/158x113/f9f9f9/191a1a.jpg",
            },{
                name: "appended_file_2.png",
                size: 9503,
                type: "image/png",
                file: "http://dummyimage.com/158x113/f9f9f9/191a1a.png",
            }]
        });
        
        $('#input2').filer({
            limit: null,
            maxSize: null,
            extensions: null,
            changeInput: '<div class="jFiler-input-dragDrop"><div class="jFiler-input-inner"><div class="jFiler-input-text"></div><a class="jFiler-input-choose-btn blue">Upload</a></div></div>',
            showThumbs: true,
            appendTo: null,
            theme: "dragdropbox",
            templates: {
                box: '<ul class="jFiler-item-list"></ul>',
                item: '<li class="jFiler-item">\
                            <div class="jFiler-item-container">\
                                <div class="jFiler-item-inner">\
                                    <div class="jFiler-item-thumb">\
                                        <div class="jFiler-item-status"></div>\
                                        <div class="jFiler-item-info">\
                                            <span class="jFiler-item-title"><b title="@{{fi-name}}">@{{fi-name | limitTo: 25}}</b></span>\
                                        </div>\
                                        @{{fi-image}}\
                                    </div>\
                                    <div class="jFiler-item-assets jFiler-row">\
                                        <ul class="list-inline pull-left">\
                                            <li>@{{fi-progressBar}}</li>\
                                        </ul>\
                                        <ul class="list-inline pull-right">\
                                            <li><a class="icon-jfi-trash jFiler-item-trash-action"></a></li>\
                                        </ul>\
                                    </div>\
                                </div>\
                            </div>\
                        </li>',
                itemAppend: '<li class="jFiler-item">\
                            <div class="jFiler-item-container">\
                                <div class="jFiler-item-inner">\
                                    <div class="jFiler-item-thumb">\
                                        <div class="jFiler-item-status"></div>\
                                        <div class="jFiler-item-info">\
                                            <span class="jFiler-item-title"><b title="@{{fi-name}}">@{{fi-name | limitTo: 25}}</b></span>\
                                        </div>\
                                        @{{fi-image}}\
                                    </div>\
                                    <div class="jFiler-item-assets jFiler-row">\
                                        <ul class="list-inline pull-left">\
                                            <span class="jFiler-item-others">@{{fi-icon}} @{{fi-size2}}</span>\
                                        </ul>\
                                        <ul class="list-inline pull-right">\
                                            <li><a class="icon-jfi-trash jFiler-item-trash-action"></a></li>\
                                        </ul>\
                                    </div>\
                                </div>\
                            </div>\
                        </li>',
                progressBar: '<div class="bar"></div>',
                itemAppendToEnd: false,
                removeConfirmation: false,
                _selectors: {
                    list: '.jFiler-item-list',
                    item: '.jFiler-item',
                    progressBar: '.bar',
                    remove: '.jFiler-item-trash-action',
                }
            },
            uploadFile: {
                url: "./php/upload.php",
                data: {},
                type: 'POST',
                enctype: 'multipart/form-data',
                beforeSend: function(){},
                success: function(data, el){
                    var parent = el.find(".jFiler-jProgressBar").parent();
                    el.find(".jFiler-jProgressBar").fadeOut("slow", function(){
                        $("<div class=\"jFiler-item-others text-success\"><i class=\"icon-jfi-check-circle\"></i> Success</div>").hide().appendTo(parent).fadeIn("slow");    
                    });
                },
                error: function(el){
                    var parent = el.find(".jFiler-jProgressBar").parent();
                    el.find(".jFiler-jProgressBar").fadeOut("slow", function(){
                        $("<div class=\"jFiler-item-others text-error\"><i class=\"icon-jfi-minus-circle\"></i> Error</div>").hide().appendTo(parent).fadeIn("slow");    
                    });
                },
                statusCode: {},
                onProgress: function(){},
            },
            dragDrop: {
                dragEnter: function(){},
                dragLeave: function(){},
                drop: function(){},
            },
            addMore: true,
            clipBoardPaste: true,
            excludeName: null,
            beforeShow: function(){return true},
            onSelect: function(){},
            afterShow: function(){},
            onRemove: function(){},
            onEmpty: function(){},
            captions: {
                button: "Choose Files",
                feedback: "Choose files To Upload",
                feedback2: "files were chosen",
                drop: "Drop file here to Upload",
                removeConfirmation: "Are you sure you want to remove this file?",
                errors: {
                    filesLimit: "Only @{{fi-limit}} files are allowed to be uploaded.",
                    filesType: "Only Images are allowed to be uploaded.",
                    filesSize: "@{{fi-name}} is too large! Please upload file up to @{{fi-maxSize}} MB.",
                    filesSizeAll: "Files you've choosed are too large! Please upload files up to @{{fi-maxSize}} MB."
                }
            }
        });
  });
  </script>
    
  

<script type="text/javascript">
$(document).ready(function(){
    $('[data-toggle="tooltip"]').tooltip({
        placement : 'top'
    });
});
</script>
<script type="text/javascript">
$(document).ready(function() {
    var $lightbox = $('#lightbox');
    
    $('[data-target="#lightbox"]').on('click', function(event) {
        var $img = $(this).find('img'), 
            src = $img.attr('src'),
            alt = $img.attr('alt'),
            css = {
                'maxWidth': $(window).width() - 100,
                'maxHeight': $(window).height() - 100
            };
    
        $lightbox.find('.close').addClass('hidden');
        $lightbox.find('img').attr('src', src);
        $lightbox.find('img').attr('alt', alt);
        $lightbox.find('img').css(css);
    });
    
    $lightbox.on('shown.bs.modal', function (e) {
        var $img = $lightbox.find('img');
            
        $lightbox.find('.modal-dialog').css({'width': $img.width()});
        $lightbox.find('.close').removeClass('hidden');
    });
});
</script>
<!-- SlimScroll 1.3.0 -->
<script src="js/plugins/slimScroll/jquery.slimscroll.min.js" type="text/javascript"></script>
<!-- FastClick -->
<script src='js/plugins/fastclick/fastclick.min.js'></script>
<!-- AdminLTE App -->
<script src="js/app.min.js" type="text/javascript"></script>
<!-- AdminLTE for demo purposes -->
<script src="js/demo.js" type="text/javascript"></script>





<!--<script type="text/javascript">
$("#checkAll").click(function () {
    $(".check").prop('checked', $(this).prop('checked'));
});
</script>-->
<script type="text/javascript">
/*$(document).ready(function()
{
  if($('[id="countries[]"]').prop('checked') == false){
    $("#checkAll").prop('unchecked');
  }
});*/
/*$("#checkAll").click(function () {
  //alert('hi');
    $(".check").prop('checked', $(this).prop('checked'));
    if($("#checkAll").prop('checked') == true)
  {
    var url = '/getChannelsByCountry/'+0;
    var posting =$.get(url);
    posting.done(function(data){
    //console.log(data);
    $('[id="channels"]').empty();
      $.each(data, function(key, value){
          $('[id="channels"]').append('<label class="checkpadright"><input type="checkbox" value="'+value['channel_id']+'">'+value['channnel_name']+'</label>');
      });   
    });  
  }else{
    $('[id="channels"]').empty();
  } 
});
$('[id="countries[]"]').change(function () {
  var data = new Array();
  $("input[name='countries[]']:checked").each(function() {
    data.push($(this).val());
  }); 
  var url = '/getChannelsByCountry/'+data;
  var posting =$.get(url);
  posting.done(function(data){
  //console.log(data);
  $('[id="channels"]').empty();
    $.each(data, function(key, value){
        $('[id="channels"]').append('<label class="checkpadright"><input type="checkbox" value="'+value['channel_id']+'">'+value['channnel_name']+'</label>');
    });   
  });   
});*/
$("input:checkbox.check").click(function () {
  if(!$(this).is(":checked"))
  {
    $('#checkAll').attr('checked', false);
  }
});
$("input:checkbox.check").click(function () {
  if(!$(this).is(":checked"))
  {
    $('#checkAll').attr('checked', true);
  }
}); 
$("#checkAll").click(function () {
  //alert('hi');
    $(".check").prop('checked', $(this).prop('checked'));
    if($("#checkAll").prop('checked') == true)
  {
    custChannels = <?php echo json_encode($custSelectedChannels);?>;
    var url = '/getChannelsByCountry/'+0;
    var posting =$.get(url);
    posting.done(function(data){
    $('[id="channels"]').empty();
      $.each(data, function(key, value){
        chnl = '' +value['channel_id'];
        if ($.inArray(chnl, custChannels) > -1){
          $('[id="channels"]').append('<div class="row"><div class="col-md-5"><a target="_blank" href="'+value['price_url']+'">Charges</a></br><label class="checkpadright"><input type="checkbox" checked="true" class="terms">I Agree</label></div><div class="col-md-5"><a target="_blank" href="'+value['tnc_url']+'">Terms and Condition</a></br><label class="checkpadright"><input type="checkbox" checked="true" class="terms">I Agree</label></div><div class="col-md-2"><label class="checkpadright"><input type="checkbox" id="channel[]" name="channel[]" value="'+value['channel_id']+'" checked="true">'+value['channnel_name']+'</label></div></div></br>');
          }else{
          $('[id="channels"]').append('<div class="row"><div class="col-md-5"><a target="_blank" href="'+value['price_url']+'">Charges</a></br><label class="checkpadright"><input type="checkbox" class="terms">I Agree</label></div><div class="col-md-5"><a target="_blank" href="'+value['tnc_url']+'">Terms and Condition</a></br><label class="checkpadright"><input type="checkbox" class="terms">I Agree</label></div><div class="col-md-2"><label class="checkpadright"><input type="checkbox" id="channel[]" name="channel[]" value="'+value['channel_id']+'">'+value['channnel_name']+'</label></div></div></br>');         
         }
      });   
    });  
  }else{
    $('[id="channels"]').empty();
  } 
});
//working correctly  
/*$("#checkAll").click(function () {
  //alert('hi');
    $(".check").prop('checked', $(this).prop('checked'));
    if($("#checkAll").prop('checked') == true)
  {
    custChannels = <?php echo json_encode($custSelectedChannels);?>;
    var url = '/getChannelsByCountry/'+0;
    var posting =$.get(url);
    posting.done(function(data){
    $('[id="channels"]').empty();
      $.each(data, function(key, value){
        chnl = '' +value['channel_id'];
        if ($.inArray(chnl, custChannels) > -1){
          $('[id="channels"]').append('<label class="checkpadright"><input type="checkbox" id="channel[]" name="channel[]" value="'+value['channel_id']+'" checked="true">'+value['channnel_name']+'</label>');
          }else{
          $('[id="channels"]').append('<label class="checkpadright"><input type="checkbox" id="channel[]" name="channel[]" value="'+value['channel_id']+'">'+value['channnel_name']+'</label>');          
         }
      });   
    });  
  }else{
    $('[id="channels"]').empty();
  } 
});*/
//working correctly
/*$('[id="countries[]"]').change(function () {
  var data = new Array();
  var custChannels = {};
  custChannels = <?php echo json_encode($custSelectedChannels);?>;
  //console.log(custChannels);
  $("input[name='countries[]']:checked").each(function() {
    data.push($(this).val());
  }); 
  if(data == '')
  {
    $('[id="channels"]').empty();    
  }else{
    var url = '/getChannelsByCountry/'+data;
    var posting =$.get(url);
    posting.done(function(data){
    //console.log(data);
    $('[id="channels"]').empty();
      $.each(data, function(key, value){
        chnl = '' +value['channel_id'];
         if ($.inArray(chnl, custChannels) > -1){
           $('[id="channels"]').append('<label class="checkpadright"><input type="checkbox" id="channel[]" name="channel[]" value="'+value['channel_id']+'" checked="true">'+value['channnel_name']+'</label>');         
         }else{
          $('[id="channels"]').append('<label class="checkpadright"><input type="checkbox" id="channel[]" name="channel[]" value="'+value['channel_id']+'">'+value['channnel_name']+'</label>');          
         }
      });   
    });
  }   
});*///working correctly
$('[id="countries[]"]').change(function () {
  var data = new Array();
  var custChannels = {};
  custChannels = <?php echo json_encode($custSelectedChannels);?>;
  //console.log(custChannels);
  $("input[name='countries[]']:checked").each(function() {
    data.push($(this).val());
  }); 
  if(data == '')
  {
    $('[id="channels"]').empty();    
  }else{
    var url = '/getChannelsByCountry/'+data;
    var posting =$.get(url);
    posting.done(function(data){
    //console.log(data);
    $('[id="channels"]').empty();
      $.each(data, function(key, value){
        chnl = '' +value['channel_id'];
         if ($.inArray(chnl, custChannels) > -1){
          $('[id="channels"]').append('<div class="row"><div class="col-md-5"><a target="_blank" href="'+value['price_url']+'">Charges</a></br><label class="checkpadright"><input type="checkbox" checked="true" class="terms">I Agree</label></div><div class="col-md-5"><a target="_blank" href="'+value['tnc_url']+'">Terms and Condition</a></br><label class="checkpadright"><input type="checkbox" checked="true" class="terms">I Agree</label></div><div class="col-md-2"><label class="checkpadright"><input type="checkbox" id="channel[]" name="channel[]" value="'+value['channel_id']+'" checked="true">'+value['channnel_name']+'</label></div></div></br>');        
         }else{
          $('[id="channels"]').append('<div class="row"><div class="col-md-5"><a target="_blank" href="'+value['price_url']+'">Charges</a></br><label class="checkpadright"><input type="checkbox" class="terms">I Agree</label></div><div class="col-md-5"><a target="_blank" href="'+value['tnc_url']+'">Terms and Condition</a></br><label class="checkpadright"><input type="checkbox" class="terms">I Agree</label></div><div class="col-md-2"><label class="checkpadright"><input type="checkbox" id="channel[]" name="channel[]" value="'+value['channel_id']+'">'+value['channnel_name']+'</label></div></div></br>');        
         }
      });   
    });
  }   
});
$('#wizard-save').on('click', function () {
  var customer = new Array();
  var module = new Array();
  var channels = new Array();
  var erp = new Array();
  var products = new Array();
  var logistics = new Array();
  var finance = new Array();
  var customer_legal_name = $('#company_name').val();
  var cin_number = $('#cin_number').val();
  var pan_number = $('#pan_number').val();
  var signup_type = $('[name="signup_type"]').val();
  var plan = new Array();
  $("input[name='plan[]']:checked").each(function() {
    plan.push($(this).val());
  }); 
  var country = new Array();
  $("input[id='countries[]']:checked").each(function() {
    country.push($(this).val());
  });
  var channel = new Array();
  $("input[id='channel[]']:checked").each(function() {
    channel.push($(this).val());
  }); 
  var web_service_password = $('#web_service_password').val();
  var web_service_username = $('#web_service_username').val();
  var default_start_date = $('#default_start_date').val();
  var company_code = $('#company_code').val();
  var token = $('#token').val();
  var web_service_url = $('#web_service_url').val();
  var integration_mode = $('#integration_mode').val();
  var erp_model = $('#erp_model').val();  
  erp = {web_service_password:web_service_password,web_service_username:web_service_username,default_start_date:default_start_date,company_code:company_code,token:token,web_service_url:web_service_url,integration_mode:integration_mode,erp_model:erp_model};
  var data = new Array();
  //data = {customer:customer,module:module,channels:channels,erp:erp,products:products,logistics:logistics,finance :finance};
  customer = {customer_legal_name : customer_legal_name,cin_number : cin_number,pan_number : pan_number};
  module = {signup_type : signup_type, plan : plan};
  channels = {country : country, channel : channel};
  data = {customer : customer, module : module, channels : channels, erp : erp};
    $.ajax({
        url: '/saveCustomer',
        data : {'data' : data },
        type:'POST',
        success: function(result)
        {
            console.log(result);
            return false;
            if( result['status'] == true){
              $('#activationCode').modal('hide');
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
$('#customer_contract').on('click', function () {
  var condition = $('[name="plan[]"]').is(':checked');
  if(condition == false){
    alert('Please choose a plan.');
    return false;
  }else{
  var module = new Array();
  //var signup_type = $('[name="signup_type"]').val();
  var signup_type = $('input[name=signup_type]:checked').val()
  var cust_id = $('#customer_id1').val();
  //var cust_id =125;
  var plan = new Array();
  $("input[name='plan[]']:checked").each(function() {
    plan.push($(this).val());
  });     
  module = {signup_type : signup_type, cust_id : cust_id, plan : plan};
    $.ajax({
        url: '/saveCustomerModuleContract',
        data : module,
        type:'POST',
        success: function(result)
        {
            console.log(result);
            if( result['status'] == true){
              //$('#headingThree').click();
              var btn = $('#customer_contract');
              var activePanel = btn.parents('.panel');
              var nextPanel = activePanel.next();
              if(nextPanel.hasClass('hide'))
              nextPanel = nextPanel.next();
              nextPanel.find('>a:first-child').click(); 
              $('#customer_contract').removeAttr("disabled");         
              return false;
            }
            else
            {
                $('#customer_contract').removeAttr("disabled");
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
  }
});

$('#channel_customer_save').on('click', function () {
  var condition = $('[name="channel[]"]').is(':checked');
  if(condition == false){
    alert('Please select a channel');
    return false;
  }else{
  var channel = new Array();
  $("input[id='channel[]']:checked").each(function() {
    channel.push($(this).val());
  }); 
  var manufacturer_id = $('#customer_id1').val();
  //var manufacturer_id =125;
  var channels = new Array(); 
  channels = {channel : channel ,manf_id : manufacturer_id};
    $.ajax({
        url: '/saveCustChannelConfig',
        data : channels,
        type:'POST',
        datatype: 'JSON',
        success: function(result)
        {
          if( result['status'] == true){
            //$('#headingFour').click();
            var btn = $('#channel_customer_save');
            var activePanel = btn.parents('.panel');
            var nextPanel = activePanel.next();
            if(nextPanel.hasClass('hide'))
            nextPanel = nextPanel.next();
            nextPanel.find('>a:first-child').click();             
            $('#logistics-div').empty();
            createLogistcsAcc(result);
            createDCChannels(result);
            $('#channel_customer_save').removeAttr("disabled");
          } else
          {
              alert(result['message']);
              $('#channel_customer_save').removeAttr("disabled");
          }
        },
        error: function(err){
            console.log('Error: '+err);
        },
        complete: function(data){
            console.log(data);
        }
    });
  } 
});

function createLogistcsAcc(result){
  // console.log(result);
  $.each(result['custChnlLogs'],function(a,channel){
    var key = a, value = channel;
    $('#mfg_channel_count').val(a+1);                  
    $('[id="logistics-div"]').append('<div class="col-md-6 xolo" id="mc-div'+key+'"><label class="checkpadright"><input type="checkbox" id="manfChannels'+key+'[]" name="manfChannels'+key+'[]" value="'+value['channel_id']+'" checked="true" onclick="return false" class="xas">'+value['channel_name']+'</label></br></div>');
      carrier_ids = value['carrier_id'];
      // console.log(result['logistic']);
      $.each(result['logistic'],function(i,value){
        // checked = false;
        var checked = $.inArray(''+value['carrier_id'],carrier_ids);
        console.log(checked);
/*        checked = checked != -1 ? true : false;
        console.log(checked);*/
         /*console.log(i,value,'<label style="padding-left:20px;" class="checkpadright"><input type="checkbox" id="logist'+i+'[]" name="logist'+i+'[]" value="'+value['carrier_id']+'" checked="'+checked+'">'+value['name']+'</label></br>');*/
        if(checked >-1){
        $('[id="mc-div'+key+'"]').append('<label style="padding-left:20px;" class="checkpadright"><input type="checkbox" id="logist'+key+'[]" name="logist'+key+'[]" value="'+value['carrier_id']+'" checked="true">'+value['name']+'</label></br>');
        }else{
        $('[id="mc-div'+key+'"]').append('<label style="padding-left:20px;" class="checkpadright"><input type="checkbox" id="logist'+key+'[]" name="logist'+key+'[]" value="'+value['carrier_id']+'">'+value['name']+'</label></br>');
        }
      });
  });
}

function createDCChannels(result)
{
  $('[id="cust-dc-div"]').empty();
  $.each(result['manfChannels'],function(a,channel){
    var key = a, value = channel;               
    $('[id="cust-dc-div"]').append('<div class="col-md-12"><div class="box-header"><h3 class="box-title">Select Channel</h3></div><label class="checkpadright"><input type="checkbox" id="dcChannels[]" name="dcChannels[]" value="'+value['channel_id']+'" checked="true">'+value['channel_name']+'</label>');    
  });
}

  $('#location_country').on('change', function() {
    var zone_value= $('#location_country').val();
    $('[id="state"]').empty();
    if(zone_value > 0){
      var url = '/orders/getZones';
      var posting = $.get(url, {type: zone_value});
      posting.done(function (data) {
         $('#state').append('<option value="">' +'Please Select..'+ '</option>');
         var result = data;
         $.each(result, function (key, value) {
              $('#state').append('<option value="' + value['id'] + '">' + value['name'] + '</option>');
          });
      }); 
    }else{
        $('#state').append('<option value="">' +'Please Select..'+ '</option>');      
    }  
   });
//getting States
function getStates(zone,state)
{
  console.log(zone,state);
  if(zone>0){
      var url = '/orders/getZones';
      var posting = $.get(url, {type: zone});
      posting.done(function (data) {
        $('#state').empty();
         $('#state').append('<option value="">' +'Please Select..'+ '</option>');
         var result = data;
         $.each(result, function (key, value) {
              //console.log(state,value['name']);
              if(state==value['name']){
              $('#state').append('<option value="' + value['id'] + '" selected="selected">' + value['name'] + '</option>'); 
              }else{
                $('#state').append('<option value="' + value['id'] + '">' + value['name'] + '</option>');
              }
          });
      });     
  }else{
        $('#state').append('<option value="">' +'Please Select..'+ '</option>');      
    } 
}
//getting States
$(document).ready(function() { 
if($('#SCO').is(':checked')==true && 
 $('#GDS').is(':checked')==false)  
{
$('#acc3').addClass('hide');
$('#acc7').addClass('hide');    
}else{
$('#acc3').removeClass('hide');
$('#acc7').removeClass('hide');
}
if($('[name="default_start_date"]').val()!=''){
  $('[name="start_date"]').val($('[name="default_start_date"]').val());
}
if ($('#web_service_url').val()!='')
{
  $('#erp-row').addClass('hidden')
  $('#erpdiv').removeClass('hidden');
  $('#no_erp').addClass('hidden');
}
$('#logistics-div').empty();
//getDc();
showDcGrid();
getProducts();
});
//excek and ERP Imports
$(document).ready(function(){
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
                    loadLocations();
                }
                alert(data['message']);
                $('#import_locations_from_erp_button').attr("disabled", false);
            });
            return false;
    })/*.validate({
        submitHandler: function (form) {
            return false;
        }
    })*/;
});
//import from excel
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
        })/*.validate({
        submitHandler: function (form) {
        return false;
        }   
    })*/;

    $('#location_types_add_excel').on('show.bs.modal',function(){
        console.log('reset: #add_locationtypes_form_excel');
        $('#add_locationtypes_form_excel')[0].reset();
    });
    $('#locations_fileupload').click(function(){
        $('#update_import_locations_message').text('');
    });
//import from excel
$(document).ready(function() { 
var cls = <?php echo json_encode($getcustchannelLogistics);?>;
var logs = <?php echo json_encode($logistics);?>;
console.log(cls);
$.each(cls,function(a,channel){
    var key = a, value = channel;
    $('#mfg_channel_count').val(a+1);                  
    $('[id="logistics-div"]').append('<div class="col-md-6 xolo" id="mc-div'+key+'"><label class="checkpadright"><input type="checkbox" id="manfChannels'+key+'[]" name="manfChannels'+key+'[]" value="'+value['channel_id']+'" checked="true" onclick="return false" class="xas">'+value['channel_name']+'</label></br></div>');
      carrier_ids = value['carrier_id'];
      $.each(logs,function(i,value){
        var checked = $.inArray(''+value['carrier_id'],carrier_ids);
        //console.log(checked);
        if(checked >-1){
        $('[id="mc-div'+key+'"]').append('<label style="padding-left:20px;" class="checkpadright"><input type="checkbox" id="logist'+key+'[]" name="logist'+key+'[]" value="'+value['carrier_id']+'" checked="true">'+value['name']+'</label></br>');
        }else{
        $('[id="mc-div'+key+'"]').append('<label style="padding-left:20px;" class="checkpadright"><input type="checkbox" id="logist'+key+'[]" name="logist'+key+'[]" value="'+value['carrier_id']+'">'+value['name']+'</label></br>');
        }
      });
  });
});
$("#dc-next").click(function () {
//$('#headingSix').click();
  var btn = $('#dc-next');
  var activePanel = btn.parents('.panel');
  var nextPanel = activePanel.next();
  if(nextPanel.hasClass('hide'))
  nextPanel = nextPanel.next();
  nextPanel.find('>a:first-child').click(); 
});

$("#prod-next").click(function () {
//$('#headingSeven').click();
  var btn = $('#prod-next');
  var activePanel = btn.parents('.panel');
  var nextPanel = activePanel.next();
  if(nextPanel.hasClass('hide'))
  nextPanel = nextPanel.next();
  nextPanel.find('>a:first-child').click(); 
});
$("#no_erp").click(function () {
//$('#headingFive').click();
    var btn = $('#no_erp');
    var activePanel = btn.parents('.panel');
    var nextPanel = activePanel.next();
    if(nextPanel.hasClass('hide'))
    nextPanel = nextPanel.next();
    nextPanel.find('>a:first-child').click(); 
});
$(document).ready(function() { 
  $('#mfg_id').val($('#customer_id1').val());
      $('#saveSignup').bootstrapValidator({
          //live: 'disabled',
          message: 'This value is not valid',
          feedbackIcons: {
              valid: 'glyphicon glyphicon-ok',
              invalid: 'glyphicon glyphicon-remove',
              validating: 'glyphicon glyphicon-refresh'
          },
      fields: {            

          }
      }).on('success.form.bv', function(event) {
        event.preventDefault();
        $('.xolo').each(function(i,v){
            var as = $(v).find('input[type="checkbox"]:not(.xas)').is(':checked');
            if(as==false){
              return false;
            }             
          });
        carrier = $("#saveSignup").serializeArray();
          $.ajax({
              url: '/saveCustLogistics',
              data : carrier,
              type:'POST',
              success: function(result)
              {
                  console.log(result);
                  if( result['status'] == true){
                    //$('#headingeight').click();
                    var btn = $('#logistics-save');
                    var activePanel = btn.parents('.panel');
                    var nextPanel = activePanel.next();
                    if(nextPanel.hasClass('hide'))
                    nextPanel = nextPanel.next();
                    nextPanel.find('>a:first-child').click();                     
                    return false;
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
   $('#saveCustomerData').bootstrapValidator({
          //live: 'disabled',
          message: 'This value is not valid',
          feedbackIcons: {
              valid: 'glyphicon glyphicon-ok',
              invalid: 'glyphicon glyphicon-remove',
              validating: 'glyphicon glyphicon-refresh'
          },
      fields: {            
             customer_country: {
                validators: {
                    notEmpty: {
                        message: 'Country is required.'
                    }
                }
            },
             company_name: {
                validators: {
                    notEmpty: {
                        message: 'Company Legal Name is required.'
                    }
                }
            },  
             cin_number: {
                validators: {
                    notEmpty: {
                        message: 'CIN Number is required.'
                    }
                }
            }, 
             pan_number: {
                validators: {
                    notEmpty: {
                        message: 'CIN Number is required.'
                    }
                }
            },
            'files[]': {
                validators: {
                    notEmpty: {
                        message: 'upload a file is required.'
                    }
                }
            },            
            brand_name: {
                validators: {
                    notEmpty: {
                        message: 'Brand Number is required.'
                    }
                }
            },                                  
          }
      }).on('success.form.bv', function(event) {
        event.preventDefault();
        customer = $("#saveCustomerData").serializeArray();
          $.ajax({
              url: '/saveCustomerData',
              data : customer,
              type:'POST',
              success: function(result)
              {
                  console.log(result);
                  if( result['status'] == true){
                    $('#customer_id').val(result['customer_id']);
                    //$('#headingTwo').click();
                    var btn = $('#customer_save');
                    var activePanel = btn.parents('.panel');
                    var nextPanel = activePanel.next();
                    if(nextPanel.hasClass('hide'))
                    nextPanel = nextPanel.next();
                    nextPanel.find('>a:first-child').click();                     
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
   $('#saveCustFinances').bootstrapValidator({
          //live: 'disabled',
          message: 'This value is not valid',
          feedbackIcons: {
              valid: 'glyphicon glyphicon-ok',
              invalid: 'glyphicon glyphicon-remove',
              validating: 'glyphicon glyphicon-refresh'
          },
      fields: {            
             currency: {
                validators: {
                    notEmpty: {
                        message: 'Currency is required.'
                    }
                }
            },
             ifsc_code: {
                validators: {
                    notEmpty: {
                        message: 'IFSC Code is required.'
                    }
                }
            },  
             acc_type: {
                validators: {
                    notEmpty: {
                        message: 'Account Type is required.'
                    }
                }
            }, 
             benf_name: {
                validators: {
                    notEmpty: {
                        message: 'Beneficiary Name is required.'
                    }
                }
            },
            vat_number: {
                validators: {
                    notEmpty: {
                        message: 'VAT/CST Number is required.'
                    }
                }
            }, 
             micr_code: {
                validators: {
                    notEmpty: {
                        message: 'MICR Code is required.'
                    }
                }
            },
             acc_number: {
                validators: {
                    notEmpty: {
                        message: 'Account Number is required.'
                    }
                }
            },  
             bank_name: {
                validators: {
                    notEmpty: {
                        message: 'Bank Number is required.'
                    }
                }
            },                                                                    
          }
      }).on('success.form.bv', function(event) {
        event.preventDefault();
  var finance = new Array();
  var manufacturer_id = $('#customer_id1').val();
  //var manufacturer_id =125;
  var vat_number = $('#vat_number').val();
  var benf_name = $('#benf_name').val();
  var acc_number = $('#acc_number').val();
  var acc_type = $('#acc_type').val();  
  var ifsc_code = $('#ifsc_code').val();
  var bank_name = $('#bank_name').val();  
  var micr_code = $('#micr_code').val(); 
  var currency = $('#currency').val(); 
  var finances = new Array(); 
  finances = {vat_number : vat_number ,eseal_cust_id : manufacturer_id ,acc_number : acc_number ,benf_name : benf_name ,acc_type :acc_type,ifsc_code : ifsc_code,bank_name : bank_name,micr_code :micr_code,currency :currency};
    $.ajax({
        url: '/saveCustFinances',
        data : finances,
        type:'POST',
        success: function(result)
        {
            console.log(result);
            if( result['status'] == true){
              //return false;
              window.location.href ='/thankyou';
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
  $('#saveCustDcSelection').bootstrapValidator({
          //live: 'disabled',
          message: 'This value is not valid',
          feedbackIcons: {
              valid: 'glyphicon glyphicon-ok',
              invalid: 'glyphicon glyphicon-remove',
              validating: 'glyphicon glyphicon-refresh'
          },
      fields: {            
             location_name: {
                validators: {
                    notEmpty: {
                        message: 'location name is required.'
                    }
                }
            },
             location_type_id: {
                validators: {
                    notEmpty: {
                        message: 'location type is required.'
                    }
                }
            },  
             location_address: {
                validators: {
                    notEmpty: {
                        message: 'Location Address is required.'
                    }
                }
            }, 
             location_country: {
                validators: {
                    notEmpty: {
                        message: 'Country Name is required.'
                    }
                }
            },
            pincode: {
                validators: {
                    notEmpty: {
                        message: 'Pincode Number is required.'
                    }
                }
            }, 
             state: {
                validators: {
                    notEmpty: {
                        message: 'state is required.'
                    }
                }
            },
             city: {
                validators: {
                    notEmpty: {
                        message: 'City is required.'
                    }
                }
            },  
             bank_name: {
                validators: {
                    notEmpty: {
                        message: 'Bank Number is required.'
                    }
                }
            },                                                                    
          }
      }).on('success.form.bv', function(event) {
        event.preventDefault();
  var condition = $('[name="dcChannels[]"]').is(':checked');
  if(condition == false){
    alert('Please Map a Channel.');
    return false;
  }else{        
  var dcselection = new Array();
  var manufacturer_id = $('#customer_id1').val();
  //var manufacturer_id =125;
  var location_name = $('#location_name').val();
  var location_type_id = $('#location_type_id').val();
  var location_address = $('#location_address').val();
  var country = $('#location_country').val();  
  var pincode = $('#pincode').val();
  var state = $('#state').val();  
  var city = $('#city').val();  
  var latitude = $('#latitude').val();  
  var longitude = $('#longitude').val();  
  //var dc_flag = $('#dc_flag').val(); 
  var dc_flag = $("input[id='dc_flag']:checked").val();
  if(dc_flag == 'undefined'){
    dc_flag = 0;
  }
  var dcChannel = new Array();
  $("input[id='dcChannels[]']:checked").each(function() {
    dcChannel.push($(this).val());
  }); 
  var dcselections = new Array(); 
  dcselections = {location_name : location_name ,manufacturer_id : manufacturer_id ,location_type_id : location_type_id ,location_address : location_address ,country :country,pincode : pincode,state : state,longitude :longitude,latitude :latitude,city : city ,dc_flag:dc_flag,dcChannel:dcChannel};
  //$('button#dc-save').addClass('disabled');
    $.ajax({
        url: '/saveCustDcSelection',
        data : dcselections,
        type:'POST',
        success: function(result)
        {
            console.log(result);
            if( result['status'] == true){           
              //getDc();
              showDcGrid()
              $('#AddAddress').modal('hide');
              return false;
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
   }
  });
   $('#saveCustErpConfigurations').bootstrapValidator({
          //live: 'disabled',
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
                        message: 'ERP Model is required.'
                    }
                }
            },
             integration_mode: {
                validators: {
                    notEmpty: {
                        message: 'Integration Mode is required.'
                    }
                }
            },  
             start_date: {
                validators: {
                    notEmpty: {
                        message: 'Start Date is required.'
                    }
                }
            }, 
             token: {
                validators: {
                    notEmpty: {
                        message: 'Token Name is required.'
                    }
                }
            },
            web_service_url: {
                validators: {
                    notEmpty: {
                        message: 'Web Service URL is required.'
                    }
                }
            }, 
             company_code: {
                validators: {
                    notEmpty: {
                        message: 'Company Code is required.'
                    }
                }
            },
             web_service_username: {
                validators: {
                    notEmpty: {
                        message: 'Web Service Username is required.'
                    }
                }
            },  
             web_service_password: {
                validators: {
                    notEmpty: {
                        message: 'Web Service Password is required.'
                    }
                }
            },                                                                    
          }
      }).on('success.form.bv', function(event) {
        event.preventDefault();
  var erp = new Array();
  var web_service_password = $('#web_service_password').val();
  var web_service_username = $('#web_service_username').val();
  var default_start_date = $('[name="default_start_date"]').val();
  var company_code = $('#company_code').val();
  var token = $('#token').val();
  var web_service_url = $('#web_service_url').val();
  var integration_mode = $('#integration_mode').val();
  var erp_model = $('#erp_model').val();  
  var manufacturer_id = $('#customer_id1').val();
  //var manufacturer_id =125;
  var plan = new Array();   
  erp = {web_service_password:web_service_password,web_service_username:web_service_username,default_start_date:default_start_date,company_code:company_code,token:token,web_service_url:web_service_url,integration_mode:integration_mode,erp_model:erp_model,manufacturer_id : manufacturer_id};
    $.ajax({
        url: '/saveCustErpConfigurations',
        data : erp,
        type:'POST',
        success: function(result)
        {
            console.log(result);
            if( result['status'] == true){
              //$('#headingFive').click();
              var btn = $('#erp_save');
              var activePanel = btn.parents('.panel');
              var nextPanel = activePanel.next();
              if(nextPanel.hasClass('hide'))
              nextPanel = nextPanel.next();
              nextPanel.find('>a:first-child').click();                          
              return false;
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
});
$('#erp_flag').change(function(){
  if($('[name="erp_flag"]').is(':checked')==true){
  $('#erpdiv').removeClass('hidden');
  $('#no_erp').addClass('hidden');
  }else if($('[name="erp_flag"]').is(':checked')==false){
  $('#erpdiv').addClass('hidden');
  $('#no_erp').removeClass('hidden');
  }
});
$('[name="plan[]"]').change(function(){
  if($('#SCO').is(':checked')==true && 
     $('#GDS').is(':checked')==false)  
  {
    $('#acc3').addClass('hide');
    $('#acc7').addClass('hide'); 
    //$('#loc-dc-map').addClass('hide');   
  }else{
    $('#acc3').removeClass('hide');
    $('#acc7').removeClass('hide');
    //$('#loc-dc-map').removeClass('hide');
  }
});
function showDcGrid()
{
       var url = "/getCustomerDCGrid";
       var manufacturer_id = $('#customer_id1').val();
        var source =
        {
            datatype: "json",
            datafields: [
                { name: 'location_id', type: 'string' },
                { name: 'country', type: 'string' },
                { name: 'location_address', type: 'string' },
                { name: 'location_type_name', type: 'string' },
                { name: 'location_name', type: 'string' }/*,
                { name: 'actions', type: 'string' }*/
            ],
            /*data : {manufacturer_id:manufacturer_id},*/
            url: url,
            pager: function (pagenum, pagesize, oldpagenum) {
            }
        };
        var dataAdapter = new $.jqx.dataAdapter(source);
        $("#showDcgrid").jqxGrid(
        {
            width: "100%",
            source: source,
            selectionmode: 'multiplerowsextended',
            sortable: true,
            pageable: true,
            autoheight: true,
            //autowidth: true,
            autoloadstate: false,
            autosavestate: false,
            columnsresize: true,
            columnsreorder: true,
            //showfilterrow: true,
            //filterable: true,
            columns: [
              { text: 'Country', /*filtercondition: 'starts_with',*/ datafield: 'country', width: "25%" },
              { text: 'Location Name', datafield: 'location_name', width: "25%"},
              { text: 'Location Type', datafield: 'location_type_name', width: "25%"},
              { text: 'Location Address', datafield: 'location_address', width: "25%"}
              /*{ text: 'Actions', datafield: 'actions',width:"20%" }*/
            ]               
        });             
}

$('#AddAddress').on('hide.bs.modal', function () {
    console.log('resetForm');
    $('#saveCustDcSelection').data('bootstrapValidator').resetForm();
    $('#saveCustDcSelection')[0].reset();
});

$('.channelValidation').click(function(e){
  //e.preventDefault();
  //$(this).find('input').prop('checked',false).removeAttr('checked');
  
  var isChecked = true;
  $(this).parent()
  .find('input[type="checkbox"]:not(.channelValidation input)')
  .each(function(){
    if(isChecked)
      isChecked = $(this).is(':checked');
    console.log('in:',isChecked,$(this));
  });
  console.log(isChecked);
  if(isChecked == false){
    //$(this).find('input').prop('checked',false).removeAttr('checked');
    alert('Please check the Policies');
    return false;
  }else{
    //alert('hi');
    if($(this).find('input').is(':checked'))
      $(this).find('input').prop('checked',false).removeAttr('checked');
    else
      $(this).find('input').prop('checked',true).attr('checked','checked');
  }
});

$('.terms').change(function(e){   
  e.preventDefault(); 
  var terms = $(this); 
  var parent = terms.parents('.row:first');   
  console.log(parent); 
  var isChecked_terms = terms.is(':checked'); 
  console.log(isChecked_terms); 
  if(!isChecked_terms){   
    parent.find('.channelValidation input')   
    .prop('checked',false).removeAttr('checked'); 
  }    
});

$('#pincode').change(function(){
    var pincode = $('#pincode').val();
    $.ajax({
        url: '/getDetailsbyPincode',
        data : {'pincode': pincode},
        type:'POST',
        success: function(result)
        {
            console.log(result);
            if( result['status'] == true){ 
              var pindetails = {};  
              pindetails = result['pinDetails'];
              console.log(pindetails);
              $('#location_country').val(pindetails['country_id']);  
              $('#city').val(pindetails['City']);  
              var zone = $('#location_country').val();
              getStates(zone,pindetails['State']);                
              return false;
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
//coupon_activate
$('#coupon_activate').click(function(){
    var coupon_code = $('#coupon_code').val();
    var cust_id = $('#customer_id1').val();
    $.ajax({
        url: '/activateCouponCode',
        data : {'coupon_code': coupon_code,'cust_id':cust_id},
        type:'POST',
        success: function(result)
        {
            //console.log(result);
            if( result['status'] == true){ 
              alert('Coupon Activated Successfully.');
              $('#couponValidator').addClass('hide');            
              return false;
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
  function getProducts()
  {
    var manufacturer_id = $('#customer_id1').val();
    var product_id =$('#latest_count').val();
    if(product_id > 0){
      product_id =product_id; 
    }else{
      product_id = 0;
    }
    $.ajax({
        url: '/getCustomerProducts',
        data : {manufacturer_id:manufacturer_id,product_id:product_id},
        type:'POST',
        success: function(result)
        {
            if( result['status'] == true){
              var data=result['customerProducts'];
              $('#prod-tot-count').val(result['count']);
              var path= result['path'];
              counter = $('#latest_count').val();
              var channels ={};
              var channels = result['channels'];
              /*console.log(channels);*/
             $.each(data, function (key, value) {
                  //$('#latest_count').val(counter+key);
                  $('#latest_count').val(value['product_id']);
                  prod_id = value['product_id'];
                  name = value['name'];
                  var prodetails = $("#prod-details" ).clone();
                  prodetails.removeAttr("style").appendTo('#prod-box').addClass("prods");
                  prodetails.find("#product_id").val(value['product_id']);
                  prodetails.find('#prod_gds').removeAttr("checked");
                  prodetails.find('#prod-grid').empty();               
                  prodetails.find('#prod-grid').append('<p id ="name-product">' + value['name'] + '</br>'+value['producttype']+'</br>'+value['sku']+'</br>'+value['mrp']+'</br>');

                  if(value['is_gds_enabled'] == 1){
                    console.log('If ============= '+value['is_gds_enabled']);
                    prodetails.find('#prod_gds').attr("checked","checked").prop('checked',true);
                  }else{
                    console.log('Else ============= '+value['is_gds_enabled']);
                    prodetails.find('#prod_gds').removeAttr("checked");
                    //prodetails.find('#prod-gds-channels').addClass('hide');
                  }
                  prodetails.find('#product-image').attr('src',path+value['image']);
                  prodetails.find('#channels-images').empty();
                  $.each(channels,function (key,value) {
                   //console.log(key,value['channel_disable.logo']);
                   //value=JSON.parse(value);
                  prodetails.find('#channels-images').append('<div class="col-md-3 channel-toggle"><a class="hide" onclick="channelAct(this,'+prod_id+','+value['channel_id']+','+0+')"><img src="'+value['channel_disable_logo']+'"></a><a onclick="channelAct(this,'+prod_id+','+value['channel_id']+','+1+')"><img src="'+value['channel_enable_logo']+'"></a></div>'); 
                  });
              });                        
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
  }
  function channelAct(self,product_id,channel_id,status)
  {
/*    alert(product_id+','+channel_id);
    return false;*/
//Ajaxcall
    $.ajax({
        url: '/channelProductMapping',
        data : {'channel_id':channel_id,'product_id':product_id,'status':status},
        type:'POST',
        success: function(result)
        {
            //console.log(result);
            if( result['status'] == true){
              $(self).parent().find('.hide').removeClass('hide');
              $(self).addClass('hide');
              return false;        
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
//Ajaxcall     
  }
  $('body').on('click','span.switch-label',function(e){
  //alert('hi');
  var cust_id = $('#customer_id1').val();
  var self = $(this);
  var parent = self.parent();
  var checkbox = parent.find('input[type="checkbox"]');
  var container = self.parents('.prods');
  var product_id = container.find('input#product_id').val();
  //console.log(product_id,checkbox.is(':checked'));
  if(checkbox.is(':checked') == true){
    gds_flag = 1;
    //console.log('1====== '+gds_flag);
  }else{
    gds_flag = 0;
    //console.log('0============ '+gds_flag);
  }
//Ajaxcall
    $.ajax({
        url: '/gdsStatus',
        data : {'cust_id':cust_id,'product_id':product_id,'gds_flag':gds_flag},
        type:'POST',
        success: function(result)
        {
            console.log(result);
            if( result['status'] == true){
              if(gds_flag == 1){
                $('#prod-gds-channels').removeClass('hide');
                return false;
              }
              return false;        
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
//Ajaxcall  
});
function editProduct(product_id){
  editEntity(product_id); 
}
function editEntity(id)
{
  $.get('product/editgdsproduct/'+id ,function(response){ 
        $("#prod-basicvalCode").html('Edit Product');
        
        $("#entitiesDiv").html(response);
        
        $("#editEntity").click();
    });
}
$('#prod-get').on('click',function(){getProducts()});
</script>
</body>
</html>
