<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Global Distribution System | Dashboard</title>
<meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>
<!-- Bootstrap 3.3.4 -->
<link href="css/bootstrap.min.css" rel="stylesheet" type="text/css" />
<!-- Font Awesome Icons -->
<link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css" rel="stylesheet" type="text/css" />
<!-- Ionicons -->
<link href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css" rel="stylesheet" type="text/css" />
<!-- Theme style -->
<link href="css/AdminLTE.min.css" rel="stylesheet" type="text/css" />
<!-- AdminLTE Skins. Choose a skin from the css/skins 
folder instead of downloading all of them to reduce the load. -->
<link href="css/_all-skins.min.css" rel="stylesheet" type="text/css" />
<link href="js/plugins/morris/morris.css" rel="stylesheet" type="text/css" />
<!-- jvectormap -->
<link href="js/plugins/jvectormap/jquery-jvectormap-1.2.2.css" rel="stylesheet" type="text/css" />

	<link href="css/jquery.filer.css" type="text/css" rel="stylesheet" />
	


<link rel="stylesheet" href="images/ammap.css" type="text/css">
<script src="images/ammap.js" type="text/javascript"></script>
<!-- map file should be included after ammap.js -->
<script src="images/worldLow.js" type="text/javascript"></script>



  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css">
  <link rel="stylesheet" href="css/bootstrap-select.css">




<script>
AmCharts.makeChart("mapdiv", {

type: "map",


dataProvider: {
map: "worldLow",
getAreasFromMap: true
},

areasSettings: {
adjustOutlineThickness:true,
autoZoom: true,
outlineThickness:1,
selectedColor: "#CC0000"
},

smallMap: {}
});

</script>    


<!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
<!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
<!--[if lt IE 9]>
<script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
<script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
<![endif]-->



<style type="text/css">

.signuphead{color:#29aeec !important;font-size: 18px !important;
    margin-top: 28px !important;
}
.bg-navy {background-color: #81889d !important;}
.content { min-height:auto !important;}
a:focus, a:hover {text-decoration: none !important;}
.checkpadright{padding-right:40px !important;}
.countryselec{height:80px; overflow:auto;}
.countryhead{background:#efefef; padding:10px;}

.productbox{
background:#fff; -webkit-box-shadow: 1px 1px 6px 1px rgba(0,0,0,0.2);
-moz-box-shadow: 1px 1px 6px 1px rgba(0,0,0,0.2);
box-shadow: 1px 1px 6px 1px rgba(0,0,0,0.2); padding:20px;
}

.productchkbox{margin-left:10px;}
.productchkdetalinks {
    float: right;
    margin-right: 12px;
    margin-top: 10px;
}
.imgresborder{border:1px solid #efefef;}
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

.modal-dialog {
    width: 200px !important;
    margin: 30px auto;
}

.modal-dialog1 {
    width: 700px !important;
	height:auto !important;
    margin: 30px auto;
}


.modal-header {
    border-bottom: none !important;
}
.modal-body {
    position: relative;
    padding: 15px;
	height:150px;
	overflow-y:scroll;
}


.modal-body1 {
    position: relative;
    padding: 15px;
	height:500px !important;
	overflow:hidden !important;
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
	background: #D8081C;
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
	background: #4c9232;
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


</head>
<body class="skin-blue sidebar-mini">
<div class="wrapper">


<!-- Left side column. contains the logo and sidebar -->



<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
<!-- Content Header (Page header) -->







<section class="content">








<div class="row">
<div class="col-lg-2 col-xs-12">
<h3 class="signuphead">Manage Products</h3>
</div>
<div class="col-lg-10 col-xs-12">
<div class="margin pull-right">

<div class="btn-group">
<button type="button" class="btn bg-primary margin">Add</button>
</div>
<div class="btn-group">
<button type="button" class="btn bg-primary margin">Bulk Update</button>
</div>
<div class="btn-group">
<button type="button" class="btn bg-primary margin">Import from ERP</button>
</div>
<div class="btn-group">
<button type="button" class="btn bg-primary margin">Import from CSV</button>
</div>
<div class="btn-group">
<button type="button" class="btn bg-primary margin">Import Component products from CSV</button>
</div>
<div class="btn-group">
<button type="button" class="btn bg-primary margin">Add Product</button>
</div>


</div>
</div>
</div>


<div class="row">

<div class="col-md-10 pull-right">

<div class="input-group margin">
<!-- /btn-group -->
<input type="text" class="form-control">
<div class="input-group-btn">
<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown"><span class="fa fa-search"></span></button>

</div></div>

</div>

</div>






<div class="row">
<div class="col-lg-3">
<div class="productbox">

<div class="row">
<div class="col-md-6 col-xs-6">

<div class="form-group productchkbox"><div class="checkbox"><label><input type="checkbox"></label></div></div>

</div>
<div class="col-md-6 col-xs-6 pull-right">
<div class="form-group productchkdetalinks"><a href="#"><i class="fa fa-exclamation-circle"></i></a></div>

</div>
</div>


<div class="row">
<div class="col-md-8 col-md-offset-2 text-center imgresborder"><img src="img/fan.png" class="img-responsive"></div>
<div class="col-md-2">

<label class="switch">
	<input class="switch-input" type="checkbox" />
	<span class="switch-label"></span> 
</label>

</div>
</div>


<div class="row invoice-info productdetails">

<div class="col-sm-6 invoice-col">



<p>
Name:<br>
Type:<br>
SKU:<br>
Manufacture:<br>
Price:<br>
</p>
</div>
<div class="col-sm-6 invoice-col">
<p>
Orient Fan<br>
Finished Product<br>
Sku-487<br>
Orient<br>
$4.00<br>
</p>
</div>
</div>

<div class="row invoice-info productdetails">
<div class="col-sm-4 invoice-col"><p><strong>Channels</strong><br></p></div>
<div class="col-sm-8 invoice-col"><p>
<a href="#"><img src="img/products1.png"> </a>
<a href="#"><img src="img/products2.png">  </a>
<a href="#"><img src="img/products3.png">  </a>
<br></p></div>
</div>



<div class="row" style="margin-top:20px;">
<div class="col-md-10 text-center">
<a href="#"><span class="badge bg-light-blue"><i class="fa fa-pencil"></i></span></a>
<a href="#"><span class="badge bg-red"><i class="fa fa-trash-o"></i></span></a>
<a href="#AddChannel" data-toggle="modal"><span class="badge bg-green"><i class="fa fa-plus"></i></span></a>
</div>

<div class="col-md-2">
<a href="#Discription" data-toggle="modal" class="productmenu"><i class="fa fa-ellipsis-h"></i></a>
</div>

<div class="bs-example">
<div id="Discription" class="modal fade">
<div class="modal-dialog modal-dialog1">
<div class="modal-content">
<div class="modal-header">
<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
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
<div id="AddChannel" class="modal fade">
<div class="modal-dialog">
<div class="modal-content">
<div class="modal-body">

<div class="row">
<div class="col-md-2"><label class="checkpadright"><input type="checkbox"></label></div>
<div class="col-md-4"><img src="img/products1.png"></div>
<div class="col-md-6">Amozon</div>
</div>

<div class="row">
<div class="col-md-2"><label class="checkpadright"><input type="checkbox"></label></div>
<div class="col-md-4"><img src="img/products2.png"></div>
<div class="col-md-6">Snapdeal</div>
</div>

<div class="row">
<div class="col-md-2"><label class="checkpadright"><input type="checkbox"></label></div>
<div class="col-md-4"><img src="img/products3.png"></div>
<div class="col-md-6">Flipkart</div>
</div>

<div class="row">
<div class="col-md-2"><label class="checkpadright"><input type="checkbox"></label></div>
<div class="col-md-4"><img src="img/products1.png"></div>
<div class="col-md-6">Amozon</div>
</div>

<div class="row">
<div class="col-md-2"><label class="checkpadright"><input type="checkbox"></label></div>
<div class="col-md-4"><img src="img/products2.png"></div>
<div class="col-md-6">Snapdeal</div>
</div>

<div class="row">
<div class="col-md-2"><label class="checkpadright"><input type="checkbox"></label></div>
<div class="col-md-4"><img src="img/products3.png"></div>
<div class="col-md-6">Flipkart</div>
</div>

<div class="row">
<div class="col-md-2"><label class="checkpadright"><input type="checkbox"></label></div>
<div class="col-md-4"><img src="img/products1.png"></div>
<div class="col-md-6">Amozon</div>
</div>

<div class="row">
<div class="col-md-2"><label class="checkpadright"><input type="checkbox"></label></div>
<div class="col-md-4"><img src="img/products2.png"></div>
<div class="col-md-6">Snapdeal</div>
</div>

<div class="row">
<div class="col-md-2"><label class="checkpadright"><input type="checkbox"></label></div>
<div class="col-md-4"><img src="img/products3.png"></div>
<div class="col-md-6">Flipkart</div>
</div>

</div>
</div>
</div>
</div>
</div>

</div>



</div>
</div>
<div class="col-lg-3">
<div class="productbox">

<div class="row">
<div class="col-md-6 col-xs-6">

<div class="form-group productchkbox"><div class="checkbox"><label><input type="checkbox"></label></div></div>

</div>
<div class="col-md-6 col-xs-6 pull-right">
<div class="form-group productchkdetalinks"><a href="#"><i class="fa fa-exclamation-circle"></i></a></div>

</div>
</div>


<div class="row">
<div class="col-md-8 col-md-offset-2 text-center imgresborder"><img src="img/fan.png" class="img-responsive"></div>
<div class="col-md-2">

<label class="switch">
	<input class="switch-input" type="checkbox" />
	<span class="switch-label"></span> 
</label>

</div>
</div>


<div class="row invoice-info productdetails">

<div class="col-sm-6 invoice-col">



<p>
Name:<br>
Type:<br>
SKU:<br>
Manufacture:<br>
Price:<br>
</p>
</div>
<div class="col-sm-6 invoice-col">
<p>
Orient Fan<br>
Finished Product<br>
Sku-487<br>
Orient<br>
$4.00<br>
</p>
</div>
</div>

<div class="row invoice-info productdetails">
<div class="col-sm-4 invoice-col"><p><strong>Channels</strong><br></p></div>
<div class="col-sm-8 invoice-col"><p>
<a href="#"><img src="img/products1.png"> </a>
<a href="#"><img src="img/products2.png">  </a>
<a href="#"><img src="img/products3.png">  </a>
<br></p></div>
</div>



<div class="row" style="margin-top:20px;">
<div class="col-md-10 text-center">
<a href="#"><span class="badge bg-light-blue"><i class="fa fa-pencil"></i></span></a>
<a href="#"><span class="badge bg-red"><i class="fa fa-trash-o"></i></span></a>
<a href="#AddChannel" data-toggle="modal"><span class="badge bg-green"><i class="fa fa-plus"></i></span></a>
</div>

<div class="col-md-2">
<a href="#Discription" data-toggle="modal" class="productmenu"><i class="fa fa-ellipsis-h"></i></a>
</div>

<div class="bs-example">
<div id="Discription" class="modal fade">
<div class="modal-dialog modal-dialog1">
<div class="modal-content">
<div class="modal-header">
<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
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
<div id="AddChannel" class="modal fade">
<div class="modal-dialog">
<div class="modal-content">
<div class="modal-body">

<div class="row">
<div class="col-md-2"><label class="checkpadright"><input type="checkbox"></label></div>
<div class="col-md-4"><img src="img/products1.png"></div>
<div class="col-md-6">Amozon</div>
</div>

<div class="row">
<div class="col-md-2"><label class="checkpadright"><input type="checkbox"></label></div>
<div class="col-md-4"><img src="img/products2.png"></div>

<div class="col-md-6">Snapdeal</div>
</div>

<div class="row">
<div class="col-md-2"><label class="checkpadright"><input type="checkbox"></label></div>
<div class="col-md-4"><img src="img/products3.png"></div>
<div class="col-md-6">Flipkart</div>
</div>

<div class="row">
<div class="col-md-2"><label class="checkpadright"><input type="checkbox"></label></div>
<div class="col-md-4"><img src="img/products1.png"></div>
<div class="col-md-6">Amozon</div>
</div>

<div class="row">
<div class="col-md-2"><label class="checkpadright"><input type="checkbox"></label></div>
<div class="col-md-4"><img src="img/products2.png"></div>
<div class="col-md-6">Snapdeal</div>
</div>

<div class="row">
<div class="col-md-2"><label class="checkpadright"><input type="checkbox"></label></div>
<div class="col-md-4"><img src="img/products3.png"></div>
<div class="col-md-6">Flipkart</div>
</div>

<div class="row">
<div class="col-md-2"><label class="checkpadright"><input type="checkbox"></label></div>
<div class="col-md-4"><img src="img/products1.png"></div>
<div class="col-md-6">Amozon</div>
</div>

<div class="row">
<div class="col-md-2"><label class="checkpadright"><input type="checkbox"></label></div>
<div class="col-md-4"><img src="img/products2.png"></div>
<div class="col-md-6">Snapdeal</div>
</div>

<div class="row">
<div class="col-md-2"><label class="checkpadright"><input type="checkbox"></label></div>
<div class="col-md-4"><img src="img/products3.png"></div>
<div class="col-md-6">Flipkart</div>
</div>

</div>
</div>
</div>
</div>
</div>

</div>



</div>
</div>
<div class="col-lg-3">
<div class="productbox">

<div class="row">
<div class="col-md-6 col-xs-6">

<div class="form-group productchkbox"><div class="checkbox"><label><input type="checkbox"></label></div></div>

</div>
<div class="col-md-6 col-xs-6 pull-right">
<div class="form-group productchkdetalinks"><a href="#"><i class="fa fa-exclamation-circle"></i></a></div>

</div>
</div>


<div class="row">
<div class="col-md-8 col-md-offset-2 text-center imgresborder"><img src="img/fan.png" class="img-responsive"></div>
<div class="col-md-2">

<label class="switch">
	<input class="switch-input" type="checkbox" />
	<span class="switch-label"></span> 
</label>

</div>
</div>


<div class="row invoice-info productdetails">

<div class="col-sm-6 invoice-col">



<p>
Name:<br>
Type:<br>
SKU:<br>
Manufacture:<br>
Price:<br>
</p>
</div>
<div class="col-sm-6 invoice-col">
<p>
Orient Fan<br>
Finished Product<br>
Sku-487<br>
Orient<br>
$4.00<br>
</p>
</div>
</div>

<div class="row invoice-info productdetails">
<div class="col-sm-4 invoice-col"><p><strong>Channels</strong><br></p></div>
<div class="col-sm-8 invoice-col"><p>
<a href="#"><img src="img/products1.png"> </a>
<a href="#"><img src="img/products2.png">  </a>
<a href="#"><img src="img/products3.png">  </a>
<br></p></div>
</div>



<div class="row" style="margin-top:20px;">
<div class="col-md-10 text-center">
<a href="#"><span class="badge bg-light-blue"><i class="fa fa-pencil"></i></span></a>
<a href="#"><span class="badge bg-red"><i class="fa fa-trash-o"></i></span></a>
<a href="#AddChannel" data-toggle="modal"><span class="badge bg-green"><i class="fa fa-plus"></i></span></a>
</div>

<div class="col-md-2">
<a href="#Discription" data-toggle="modal" class="productmenu"><i class="fa fa-ellipsis-h"></i></a>
</div>

<div class="bs-example">
<div id="Discription" class="modal fade">
<div class="modal-dialog modal-dialog1">
<div class="modal-content">
<div class="modal-header">
<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
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
<div id="AddChannel" class="modal fade">
<div class="modal-dialog">
<div class="modal-content">
<div class="modal-body">

<div class="row">
<div class="col-md-2"><label class="checkpadright"><input type="checkbox"></label></div>
<div class="col-md-4"><img src="img/products1.png"></div>
<div class="col-md-6">Amozon</div>
</div>

<div class="row">
<div class="col-md-2"><label class="checkpadright"><input type="checkbox"></label></div>
<div class="col-md-4"><img src="img/products2.png"></div>

<div class="col-md-6">Snapdeal</div>
</div>

<div class="row">
<div class="col-md-2"><label class="checkpadright"><input type="checkbox"></label></div>
<div class="col-md-4"><img src="img/products3.png"></div>
<div class="col-md-6">Flipkart</div>
</div>

<div class="row">
<div class="col-md-2"><label class="checkpadright"><input type="checkbox"></label></div>
<div class="col-md-4"><img src="img/products1.png"></div>
<div class="col-md-6">Amozon</div>
</div>

<div class="row">
<div class="col-md-2"><label class="checkpadright"><input type="checkbox"></label></div>
<div class="col-md-4"><img src="img/products2.png"></div>
<div class="col-md-6">Snapdeal</div>
</div>

<div class="row">
<div class="col-md-2"><label class="checkpadright"><input type="checkbox"></label></div>
<div class="col-md-4"><img src="img/products3.png"></div>
<div class="col-md-6">Flipkart</div>
</div>

<div class="row">
<div class="col-md-2"><label class="checkpadright"><input type="checkbox"></label></div>
<div class="col-md-4"><img src="img/products1.png"></div>
<div class="col-md-6">Amozon</div>
</div>

<div class="row">
<div class="col-md-2"><label class="checkpadright"><input type="checkbox"></label></div>
<div class="col-md-4"><img src="img/products2.png"></div>
<div class="col-md-6">Snapdeal</div>
</div>

<div class="row">
<div class="col-md-2"><label class="checkpadright"><input type="checkbox"></label></div>
<div class="col-md-4"><img src="img/products3.png"></div>
<div class="col-md-6">Flipkart</div>
</div>

</div>
</div>
</div>
</div>
</div>

</div>



</div>
</div>
<div class="col-lg-3">
<div class="productbox">

<div class="row">
<div class="col-md-6 col-xs-6">

<div class="form-group productchkbox"><div class="checkbox"><label><input type="checkbox"></label></div></div>

</div>
<div class="col-md-6 col-xs-6 pull-right">
<div class="form-group productchkdetalinks"><a href="#"><i class="fa fa-exclamation-circle"></i></a></div>

</div>
</div>


<div class="row">
<div class="col-md-8 col-md-offset-2 text-center imgresborder"><img src="img/fan.png" class="img-responsive"></div>
<div class="col-md-2">

<label class="switch">
	<input class="switch-input" type="checkbox" />
	<span class="switch-label"></span> 
</label>

</div>
</div>


<div class="row invoice-info productdetails">

<div class="col-sm-6 invoice-col">



<p>
Name:<br>
Type:<br>
SKU:<br>
Manufacture:<br>
Price:<br>
</p>
</div>
<div class="col-sm-6 invoice-col">
<p>
Orient Fan<br>
Finished Product<br>
Sku-487<br>
Orient<br>
$4.00<br>
</p>
</div>
</div>

<div class="row invoice-info productdetails">
<div class="col-sm-4 invoice-col"><p><strong>Channels</strong><br></p></div>
<div class="col-sm-8 invoice-col"><p>
<a href="#"><img src="img/products1.png"> </a>
<a href="#"><img src="img/products2.png">  </a>
<a href="#"><img src="img/products3.png">  </a>
<br></p></div>
</div>



<div class="row" style="margin-top:20px;">
<div class="col-md-10 text-center">
<a href="#"><span class="badge bg-light-blue"><i class="fa fa-pencil"></i></span></a>
<a href="#"><span class="badge bg-red"><i class="fa fa-trash-o"></i></span></a>
<a href="#AddChannel" data-toggle="modal"><span class="badge bg-green"><i class="fa fa-plus"></i></span></a>
</div>

<div class="col-md-2">
<a href="#Discription" data-toggle="modal" class="productmenu"><i class="fa fa-ellipsis-h"></i></a>
</div>

<div class="bs-example">
<div id="Discription" class="modal fade">
<div class="modal-dialog modal-dialog1">
<div class="modal-content">
<div class="modal-header">
<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
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
<div id="AddChannel" class="modal fade">
<div class="modal-dialog">
<div class="modal-content">
<div class="modal-body">

<div class="row">
<div class="col-md-2"><label class="checkpadright"><input type="checkbox"></label></div>
<div class="col-md-4"><img src="img/products1.png"></div>
<div class="col-md-6">Amozon</div>
</div>

<div class="row">
<div class="col-md-2"><label class="checkpadright"><input type="checkbox"></label></div>
<div class="col-md-4"><img src="img/products2.png"></div>

<div class="col-md-6">Snapdeal</div>
</div>

<div class="row">
<div class="col-md-2"><label class="checkpadright"><input type="checkbox"></label></div>
<div class="col-md-4"><img src="img/products3.png"></div>
<div class="col-md-6">Flipkart</div>
</div>

<div class="row">
<div class="col-md-2"><label class="checkpadright"><input type="checkbox"></label></div>
<div class="col-md-4"><img src="img/products1.png"></div>
<div class="col-md-6">Amozon</div>
</div>

<div class="row">
<div class="col-md-2"><label class="checkpadright"><input type="checkbox"></label></div>
<div class="col-md-4"><img src="img/products2.png"></div>
<div class="col-md-6">Snapdeal</div>
</div>

<div class="row">
<div class="col-md-2"><label class="checkpadright"><input type="checkbox"></label></div>
<div class="col-md-4"><img src="img/products3.png"></div>
<div class="col-md-6">Flipkart</div>
</div>

<div class="row">
<div class="col-md-2"><label class="checkpadright"><input type="checkbox"></label></div>
<div class="col-md-4"><img src="img/products1.png"></div>
<div class="col-md-6">Amozon</div>
</div>

<div class="row">
<div class="col-md-2"><label class="checkpadright"><input type="checkbox"></label></div>
<div class="col-md-4"><img src="img/products2.png"></div>
<div class="col-md-6">Snapdeal</div>
</div>

<div class="row">
<div class="col-md-2"><label class="checkpadright"><input type="checkbox"></label></div>
<div class="col-md-4"><img src="img/products3.png"></div>
<div class="col-md-6">Flipkart</div>
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
<br>
<div class="row">
<div class="col-lg-3">
<div class="productbox">

<div class="row">
<div class="col-md-6 col-xs-6">

<div class="form-group productchkbox"><div class="checkbox"><label><input type="checkbox"></label></div></div>

</div>
<div class="col-md-6 col-xs-6 pull-right">
<div class="form-group productchkdetalinks"><a href="#"><i class="fa fa-exclamation-circle"></i></a></div>

</div>
</div>


<div class="row">
<div class="col-md-8 col-md-offset-2 text-center imgresborder"><img src="img/fan.png" class="img-responsive"></div>
<div class="col-md-2">

<label class="switch">
	<input class="switch-input" type="checkbox" />
	<span class="switch-label"></span> 
</label>

</div>
</div>


<div class="row invoice-info productdetails">

<div class="col-sm-6 invoice-col">



<p>
Name:<br>
Type:<br>
SKU:<br>
Manufacture:<br>
Price:<br>
</p>
</div>
<div class="col-sm-6 invoice-col">
<p>
Orient Fan<br>
Finished Product<br>
Sku-487<br>
Orient<br>
$4.00<br>
</p>
</div>
</div>

<div class="row invoice-info productdetails">
<div class="col-sm-4 invoice-col"><p><strong>Channels</strong><br></p></div>
<div class="col-sm-8 invoice-col"><p>
<a href="#"><img src="img/products1.png"> </a>
<a href="#"><img src="img/products2.png">  </a>
<a href="#"><img src="img/products3.png">  </a>
<br></p></div>
</div>



<div class="row" style="margin-top:20px;">
<div class="col-md-10 text-center">
<a href="#"><span class="badge bg-light-blue"><i class="fa fa-pencil"></i></span></a>
<a href="#"><span class="badge bg-red"><i class="fa fa-trash-o"></i></span></a>
<a href="#AddChannel" data-toggle="modal"><span class="badge bg-green"><i class="fa fa-plus"></i></span></a>
</div>

<div class="col-md-2">
<a href="#Discription" data-toggle="modal" class="productmenu"><i class="fa fa-ellipsis-h"></i></a>
</div>

<div class="bs-example">
<div id="Discription" class="modal fade">
<div class="modal-dialog modal-dialog1">
<div class="modal-content">
<div class="modal-header">
<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
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
<div id="AddChannel" class="modal fade">
<div class="modal-dialog">
<div class="modal-content">
<div class="modal-body">

<div class="row">
<div class="col-md-2"><label class="checkpadright"><input type="checkbox"></label></div>
<div class="col-md-4"><img src="img/products1.png"></div>
<div class="col-md-6">Amozon</div>
</div>

<div class="row">
<div class="col-md-2"><label class="checkpadright"><input type="checkbox"></label></div>
<div class="col-md-4"><img src="img/products2.png"></div>
<div class="col-md-6">Snapdeal</div>
</div>

<div class="row">
<div class="col-md-2"><label class="checkpadright"><input type="checkbox"></label></div>
<div class="col-md-4"><img src="img/products3.png"></div>
<div class="col-md-6">Flipkart</div>
</div>

<div class="row">
<div class="col-md-2"><label class="checkpadright"><input type="checkbox"></label></div>
<div class="col-md-4"><img src="img/products1.png"></div>
<div class="col-md-6">Amozon</div>
</div>

<div class="row">
<div class="col-md-2"><label class="checkpadright"><input type="checkbox"></label></div>
<div class="col-md-4"><img src="img/products2.png"></div>
<div class="col-md-6">Snapdeal</div>
</div>

<div class="row">
<div class="col-md-2"><label class="checkpadright"><input type="checkbox"></label></div>
<div class="col-md-4"><img src="img/products3.png"></div>
<div class="col-md-6">Flipkart</div>
</div>

<div class="row">
<div class="col-md-2"><label class="checkpadright"><input type="checkbox"></label></div>
<div class="col-md-4"><img src="img/products1.png"></div>
<div class="col-md-6">Amozon</div>
</div>

<div class="row">
<div class="col-md-2"><label class="checkpadright"><input type="checkbox"></label></div>
<div class="col-md-4"><img src="img/products2.png"></div>
<div class="col-md-6">Snapdeal</div>
</div>

<div class="row">
<div class="col-md-2"><label class="checkpadright"><input type="checkbox"></label></div>
<div class="col-md-4"><img src="img/products3.png"></div>
<div class="col-md-6">Flipkart</div>
</div>

</div>
</div>
</div>
</div>
</div>

</div>



</div>
</div>
<div class="col-lg-3">
<div class="productbox">

<div class="row">
<div class="col-md-6 col-xs-6">

<div class="form-group productchkbox"><div class="checkbox"><label><input type="checkbox"></label></div></div>

</div>
<div class="col-md-6 col-xs-6 pull-right">
<div class="form-group productchkdetalinks"><a href="#"><i class="fa fa-exclamation-circle"></i></a></div>

</div>
</div>


<div class="row">
<div class="col-md-8 col-md-offset-2 text-center imgresborder"><img src="img/fan.png" class="img-responsive"></div>
<div class="col-md-2">

<label class="switch">
	<input class="switch-input" type="checkbox" />
	<span class="switch-label"></span> 
</label>

</div>
</div>


<div class="row invoice-info productdetails">

<div class="col-sm-6 invoice-col">



<p>
Name:<br>
Type:<br>
SKU:<br>
Manufacture:<br>
Price:<br>
</p>
</div>
<div class="col-sm-6 invoice-col">
<p>
Orient Fan<br>
Finished Product<br>
Sku-487<br>
Orient<br>
$4.00<br>
</p>
</div>
</div>

<div class="row invoice-info productdetails">
<div class="col-sm-4 invoice-col"><p><strong>Channels</strong><br></p></div>
<div class="col-sm-8 invoice-col"><p>
<a href="#"><img src="img/products1.png"> </a>
<a href="#"><img src="img/products2.png">  </a>
<a href="#"><img src="img/products3.png">  </a>
<br></p></div>
</div>



<div class="row" style="margin-top:20px;">
<div class="col-md-10 text-center">
<a href="#"><span class="badge bg-light-blue"><i class="fa fa-pencil"></i></span></a>
<a href="#"><span class="badge bg-red"><i class="fa fa-trash-o"></i></span></a>
<a href="#AddChannel" data-toggle="modal"><span class="badge bg-green"><i class="fa fa-plus"></i></span></a>
</div>

<div class="col-md-2">
<a href="#Discription" data-toggle="modal" class="productmenu"><i class="fa fa-ellipsis-h"></i></a>
</div>

<div class="bs-example">
<div id="Discription" class="modal fade">
<div class="modal-dialog modal-dialog1">
<div class="modal-content">
<div class="modal-header">
<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
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
<div id="AddChannel" class="modal fade">
<div class="modal-dialog">
<div class="modal-content">
<div class="modal-body">

<div class="row">
<div class="col-md-2"><label class="checkpadright"><input type="checkbox"></label></div>
<div class="col-md-4"><img src="img/products1.png"></div>
<div class="col-md-6">Amozon</div>
</div>

<div class="row">
<div class="col-md-2"><label class="checkpadright"><input type="checkbox"></label></div>
<div class="col-md-4"><img src="img/products2.png"></div>

<div class="col-md-6">Snapdeal</div>
</div>

<div class="row">
<div class="col-md-2"><label class="checkpadright"><input type="checkbox"></label></div>
<div class="col-md-4"><img src="img/products3.png"></div>
<div class="col-md-6">Flipkart</div>
</div>

<div class="row">
<div class="col-md-2"><label class="checkpadright"><input type="checkbox"></label></div>
<div class="col-md-4"><img src="img/products1.png"></div>
<div class="col-md-6">Amozon</div>
</div>

<div class="row">
<div class="col-md-2"><label class="checkpadright"><input type="checkbox"></label></div>
<div class="col-md-4"><img src="img/products2.png"></div>
<div class="col-md-6">Snapdeal</div>
</div>

<div class="row">
<div class="col-md-2"><label class="checkpadright"><input type="checkbox"></label></div>
<div class="col-md-4"><img src="img/products3.png"></div>
<div class="col-md-6">Flipkart</div>
</div>

<div class="row">
<div class="col-md-2"><label class="checkpadright"><input type="checkbox"></label></div>
<div class="col-md-4"><img src="img/products1.png"></div>
<div class="col-md-6">Amozon</div>
</div>

<div class="row">
<div class="col-md-2"><label class="checkpadright"><input type="checkbox"></label></div>
<div class="col-md-4"><img src="img/products2.png"></div>
<div class="col-md-6">Snapdeal</div>
</div>

<div class="row">
<div class="col-md-2"><label class="checkpadright"><input type="checkbox"></label></div>
<div class="col-md-4"><img src="img/products3.png"></div>
<div class="col-md-6">Flipkart</div>
</div>

</div>
</div>
</div>
</div>
</div>

</div>



</div>
</div>
<div class="col-lg-3">
<div class="productbox">

<div class="row">
<div class="col-md-6 col-xs-6">

<div class="form-group productchkbox"><div class="checkbox"><label><input type="checkbox"></label></div></div>

</div>
<div class="col-md-6 col-xs-6 pull-right">
<div class="form-group productchkdetalinks"><a href="#"><i class="fa fa-exclamation-circle"></i></a></div>

</div>
</div>


<div class="row">
<div class="col-md-8 col-md-offset-2 text-center imgresborder"><img src="img/fan.png" class="img-responsive"></div>
<div class="col-md-2">

<label class="switch">
	<input class="switch-input" type="checkbox" />
	<span class="switch-label"></span> 
</label>

</div>
</div>


<div class="row invoice-info productdetails">

<div class="col-sm-6 invoice-col">



<p>
Name:<br>
Type:<br>
SKU:<br>
Manufacture:<br>
Price:<br>
</p>
</div>
<div class="col-sm-6 invoice-col">
<p>
Orient Fan<br>
Finished Product<br>
Sku-487<br>
Orient<br>
$4.00<br>
</p>
</div>
</div>

<div class="row invoice-info productdetails">
<div class="col-sm-4 invoice-col"><p><strong>Channels</strong><br></p></div>
<div class="col-sm-8 invoice-col"><p>
<a href="#"><img src="img/products1.png"> </a>
<a href="#"><img src="img/products2.png">  </a>
<a href="#"><img src="img/products3.png">  </a>
<br></p></div>
</div>



<div class="row" style="margin-top:20px;">
<div class="col-md-10 text-center">
<a href="#"><span class="badge bg-light-blue"><i class="fa fa-pencil"></i></span></a>
<a href="#"><span class="badge bg-red"><i class="fa fa-trash-o"></i></span></a>
<a href="#AddChannel" data-toggle="modal"><span class="badge bg-green"><i class="fa fa-plus"></i></span></a>
</div>

<div class="col-md-2">
<a href="#Discription" data-toggle="modal" class="productmenu"><i class="fa fa-ellipsis-h"></i></a>
</div>

<div class="bs-example">
<div id="Discription" class="modal fade">
<div class="modal-dialog modal-dialog1">
<div class="modal-content">
<div class="modal-header">
<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
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
<div id="AddChannel" class="modal fade">
<div class="modal-dialog">
<div class="modal-content">
<div class="modal-body">

<div class="row">
<div class="col-md-2"><label class="checkpadright"><input type="checkbox"></label></div>
<div class="col-md-4"><img src="img/products1.png"></div>
<div class="col-md-6">Amozon</div>
</div>

<div class="row">
<div class="col-md-2"><label class="checkpadright"><input type="checkbox"></label></div>
<div class="col-md-4"><img src="img/products2.png"></div>

<div class="col-md-6">Snapdeal</div>
</div>

<div class="row">
<div class="col-md-2"><label class="checkpadright"><input type="checkbox"></label></div>
<div class="col-md-4"><img src="img/products3.png"></div>
<div class="col-md-6">Flipkart</div>
</div>

<div class="row">
<div class="col-md-2"><label class="checkpadright"><input type="checkbox"></label></div>
<div class="col-md-4"><img src="img/products1.png"></div>
<div class="col-md-6">Amozon</div>
</div>

<div class="row">
<div class="col-md-2"><label class="checkpadright"><input type="checkbox"></label></div>
<div class="col-md-4"><img src="img/products2.png"></div>
<div class="col-md-6">Snapdeal</div>
</div>

<div class="row">
<div class="col-md-2"><label class="checkpadright"><input type="checkbox"></label></div>
<div class="col-md-4"><img src="img/products3.png"></div>
<div class="col-md-6">Flipkart</div>
</div>

<div class="row">
<div class="col-md-2"><label class="checkpadright"><input type="checkbox"></label></div>
<div class="col-md-4"><img src="img/products1.png"></div>
<div class="col-md-6">Amozon</div>
</div>

<div class="row">
<div class="col-md-2"><label class="checkpadright"><input type="checkbox"></label></div>
<div class="col-md-4"><img src="img/products2.png"></div>
<div class="col-md-6">Snapdeal</div>
</div>

<div class="row">
<div class="col-md-2"><label class="checkpadright"><input type="checkbox"></label></div>
<div class="col-md-4"><img src="img/products3.png"></div>
<div class="col-md-6">Flipkart</div>
</div>

</div>
</div>
</div>
</div>
</div>

</div>



</div>
</div>
<div class="col-lg-3">
<div class="productbox">

<div class="row">
<div class="col-md-6 col-xs-6">

<div class="form-group productchkbox"><div class="checkbox"><label><input type="checkbox"></label></div></div>

</div>
<div class="col-md-6 col-xs-6 pull-right">
<div class="form-group productchkdetalinks"><a href="#"><i class="fa fa-exclamation-circle"></i></a></div>

</div>
</div>


<div class="row">
<div class="col-md-8 col-md-offset-2 text-center imgresborder"><img src="img/fan.png" class="img-responsive"></div>
<div class="col-md-2">

<label class="switch">
	<input class="switch-input" type="checkbox" />
	<span class="switch-label"></span> 
</label>

</div>
</div>


<div class="row invoice-info productdetails">

<div class="col-sm-6 invoice-col">



<p>
Name:<br>
Type:<br>
SKU:<br>
Manufacture:<br>
Price:<br>
</p>
</div>
<div class="col-sm-6 invoice-col">
<p>
Orient Fan<br>
Finished Product<br>
Sku-487<br>
Orient<br>
$4.00<br>
</p>
</div>
</div>

<div class="row invoice-info productdetails">
<div class="col-sm-4 invoice-col"><p><strong>Channels</strong><br></p></div>
<div class="col-sm-8 invoice-col"><p>
<a href="#"><img src="img/products1.png"> </a>
<a href="#"><img src="img/products2.png">  </a>
<a href="#"><img src="img/products3.png">  </a>
<br></p></div>
</div>



<div class="row" style="margin-top:20px;">
<div class="col-md-10 text-center">
<a href="#"><span class="badge bg-light-blue"><i class="fa fa-pencil"></i></span></a>
<a href="#"><span class="badge bg-red"><i class="fa fa-trash-o"></i></span></a>
<a href="#AddChannel" data-toggle="modal"><span class="badge bg-green"><i class="fa fa-plus"></i></span></a>
</div>

<div class="col-md-2">
<a href="#Discription" data-toggle="modal" class="productmenu"><i class="fa fa-ellipsis-h"></i></a>
</div>

<div class="bs-example">
<div id="Discription" class="modal fade">
<div class="modal-dialog modal-dialog1">
<div class="modal-content">
<div class="modal-header">
<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
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
<div id="AddChannel" class="modal fade">
<div class="modal-dialog">
<div class="modal-content">
<div class="modal-body">

<div class="row">
<div class="col-md-2"><label class="checkpadright"><input type="checkbox"></label></div>
<div class="col-md-4"><img src="img/products1.png"></div>
<div class="col-md-6">Amozon</div>
</div>

<div class="row">
<div class="col-md-2"><label class="checkpadright"><input type="checkbox"></label></div>
<div class="col-md-4"><img src="img/products2.png"></div>

<div class="col-md-6">Snapdeal</div>
</div>

<div class="row">
<div class="col-md-2"><label class="checkpadright"><input type="checkbox"></label></div>
<div class="col-md-4"><img src="img/products3.png"></div>
<div class="col-md-6">Flipkart</div>
</div>

<div class="row">
<div class="col-md-2"><label class="checkpadright"><input type="checkbox"></label></div>
<div class="col-md-4"><img src="img/products1.png"></div>
<div class="col-md-6">Amozon</div>
</div>

<div class="row">
<div class="col-md-2"><label class="checkpadright"><input type="checkbox"></label></div>
<div class="col-md-4"><img src="img/products2.png"></div>
<div class="col-md-6">Snapdeal</div>
</div>

<div class="row">
<div class="col-md-2"><label class="checkpadright"><input type="checkbox"></label></div>
<div class="col-md-4"><img src="img/products3.png"></div>
<div class="col-md-6">Flipkart</div>
</div>

<div class="row">
<div class="col-md-2"><label class="checkpadright"><input type="checkbox"></label></div>
<div class="col-md-4"><img src="img/products1.png"></div>
<div class="col-md-6">Amozon</div>
</div>

<div class="row">
<div class="col-md-2"><label class="checkpadright"><input type="checkbox"></label></div>
<div class="col-md-4"><img src="img/products2.png"></div>
<div class="col-md-6">Snapdeal</div>
</div>

<div class="row">
<div class="col-md-2"><label class="checkpadright"><input type="checkbox"></label></div>
<div class="col-md-4"><img src="img/products3.png"></div>
<div class="col-md-6">Flipkart</div>
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
<br>
<div class="row">
<div class="col-lg-3">
<div class="productbox">

<div class="row">
<div class="col-md-6 col-xs-6">

<div class="form-group productchkbox"><div class="checkbox"><label><input type="checkbox"></label></div></div>

</div>
<div class="col-md-6 col-xs-6 pull-right">
<div class="form-group productchkdetalinks"><a href="#"><i class="fa fa-exclamation-circle"></i></a></div>

</div>
</div>


<div class="row">
<div class="col-md-8 col-md-offset-2 text-center imgresborder"><img src="img/fan.png" class="img-responsive"></div>
<div class="col-md-2">

<label class="switch">
	<input class="switch-input" type="checkbox" />
	<span class="switch-label"></span> 
</label>

</div>
</div>


<div class="row invoice-info productdetails">

<div class="col-sm-6 invoice-col">



<p>
Name:<br>
Type:<br>
SKU:<br>
Manufacture:<br>
Price:<br>
</p>
</div>
<div class="col-sm-6 invoice-col">
<p>
Orient Fan<br>
Finished Product<br>
Sku-487<br>
Orient<br>
$4.00<br>
</p>
</div>
</div>

<div class="row invoice-info productdetails">
<div class="col-sm-4 invoice-col"><p><strong>Channels</strong><br></p></div>
<div class="col-sm-8 invoice-col"><p>
<a href="#"><img src="img/products1.png"> </a>
<a href="#"><img src="img/products2.png">  </a>
<a href="#"><img src="img/products3.png">  </a>
<br></p></div>
</div>



<div class="row" style="margin-top:20px;">
<div class="col-md-10 text-center">
<a href="#"><span class="badge bg-light-blue"><i class="fa fa-pencil"></i></span></a>
<a href="#"><span class="badge bg-red"><i class="fa fa-trash-o"></i></span></a>
<a href="#AddChannel" data-toggle="modal"><span class="badge bg-green"><i class="fa fa-plus"></i></span></a>
</div>

<div class="col-md-2">
<a href="#Discription" data-toggle="modal" class="productmenu"><i class="fa fa-ellipsis-h"></i></a>
</div>

<div class="bs-example">
<div id="Discription" class="modal fade">
<div class="modal-dialog modal-dialog1">
<div class="modal-content">
<div class="modal-header">
<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
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
<div id="AddChannel" class="modal fade">
<div class="modal-dialog">
<div class="modal-content">
<div class="modal-body">

<div class="row">
<div class="col-md-2"><label class="checkpadright"><input type="checkbox"></label></div>
<div class="col-md-4"><img src="img/products1.png"></div>
<div class="col-md-6">Amozon</div>
</div>

<div class="row">
<div class="col-md-2"><label class="checkpadright"><input type="checkbox"></label></div>
<div class="col-md-4"><img src="img/products2.png"></div>
<div class="col-md-6">Snapdeal</div>
</div>

<div class="row">
<div class="col-md-2"><label class="checkpadright"><input type="checkbox"></label></div>
<div class="col-md-4"><img src="img/products3.png"></div>
<div class="col-md-6">Flipkart</div>
</div>

<div class="row">
<div class="col-md-2"><label class="checkpadright"><input type="checkbox"></label></div>
<div class="col-md-4"><img src="img/products1.png"></div>
<div class="col-md-6">Amozon</div>
</div>

<div class="row">
<div class="col-md-2"><label class="checkpadright"><input type="checkbox"></label></div>
<div class="col-md-4"><img src="img/products2.png"></div>
<div class="col-md-6">Snapdeal</div>
</div>

<div class="row">
<div class="col-md-2"><label class="checkpadright"><input type="checkbox"></label></div>
<div class="col-md-4"><img src="img/products3.png"></div>
<div class="col-md-6">Flipkart</div>
</div>

<div class="row">
<div class="col-md-2"><label class="checkpadright"><input type="checkbox"></label></div>
<div class="col-md-4"><img src="img/products1.png"></div>
<div class="col-md-6">Amozon</div>
</div>

<div class="row">
<div class="col-md-2"><label class="checkpadright"><input type="checkbox"></label></div>
<div class="col-md-4"><img src="img/products2.png"></div>
<div class="col-md-6">Snapdeal</div>
</div>

<div class="row">
<div class="col-md-2"><label class="checkpadright"><input type="checkbox"></label></div>
<div class="col-md-4"><img src="img/products3.png"></div>
<div class="col-md-6">Flipkart</div>
</div>

</div>
</div>
</div>
</div>
</div>

</div>



</div>
</div>
<div class="col-lg-3">
<div class="productbox">

<div class="row">
<div class="col-md-6 col-xs-6">

<div class="form-group productchkbox"><div class="checkbox"><label><input type="checkbox"></label></div></div>

</div>
<div class="col-md-6 col-xs-6 pull-right">
<div class="form-group productchkdetalinks"><a href="#"><i class="fa fa-exclamation-circle"></i></a></div>

</div>
</div>


<div class="row">
<div class="col-md-8 col-md-offset-2 text-center imgresborder"><img src="img/fan.png" class="img-responsive"></div>
<div class="col-md-2">

<label class="switch">
	<input class="switch-input" type="checkbox" />
	<span class="switch-label"></span> 
</label>

</div>
</div>


<div class="row invoice-info productdetails">

<div class="col-sm-6 invoice-col">



<p>
Name:<br>
Type:<br>
SKU:<br>
Manufacture:<br>
Price:<br>
</p>
</div>
<div class="col-sm-6 invoice-col">
<p>
Orient Fan<br>
Finished Product<br>
Sku-487<br>
Orient<br>
$4.00<br>
</p>
</div>
</div>

<div class="row invoice-info productdetails">
<div class="col-sm-4 invoice-col"><p><strong>Channels</strong><br></p></div>
<div class="col-sm-8 invoice-col"><p>
<a href="#"><img src="img/products1.png"> </a>
<a href="#"><img src="img/products2.png">  </a>
<a href="#"><img src="img/products3.png">  </a>
<br></p></div>
</div>



<div class="row" style="margin-top:20px;">
<div class="col-md-10 text-center">
<a href="#"><span class="badge bg-light-blue"><i class="fa fa-pencil"></i></span></a>
<a href="#"><span class="badge bg-red"><i class="fa fa-trash-o"></i></span></a>
<a href="#AddChannel" data-toggle="modal"><span class="badge bg-green"><i class="fa fa-plus"></i></span></a>
</div>

<div class="col-md-2">
<a href="#Discription" data-toggle="modal" class="productmenu"><i class="fa fa-ellipsis-h"></i></a>
</div>

<div class="bs-example">
<div id="Discription" class="modal fade">
<div class="modal-dialog modal-dialog1">
<div class="modal-content">
<div class="modal-header">
<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
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
<div id="AddChannel" class="modal fade">
<div class="modal-dialog">
<div class="modal-content">
<div class="modal-body">

<div class="row">
<div class="col-md-2"><label class="checkpadright"><input type="checkbox"></label></div>
<div class="col-md-4"><img src="img/products1.png"></div>
<div class="col-md-6">Amozon</div>
</div>

<div class="row">
<div class="col-md-2"><label class="checkpadright"><input type="checkbox"></label></div>
<div class="col-md-4"><img src="img/products2.png"></div>

<div class="col-md-6">Snapdeal</div>
</div>

<div class="row">
<div class="col-md-2"><label class="checkpadright"><input type="checkbox"></label></div>
<div class="col-md-4"><img src="img/products3.png"></div>
<div class="col-md-6">Flipkart</div>
</div>

<div class="row">
<div class="col-md-2"><label class="checkpadright"><input type="checkbox"></label></div>
<div class="col-md-4"><img src="img/products1.png"></div>
<div class="col-md-6">Amozon</div>
</div>

<div class="row">
<div class="col-md-2"><label class="checkpadright"><input type="checkbox"></label></div>
<div class="col-md-4"><img src="img/products2.png"></div>
<div class="col-md-6">Snapdeal</div>
</div>

<div class="row">
<div class="col-md-2"><label class="checkpadright"><input type="checkbox"></label></div>
<div class="col-md-4"><img src="img/products3.png"></div>
<div class="col-md-6">Flipkart</div>
</div>

<div class="row">
<div class="col-md-2"><label class="checkpadright"><input type="checkbox"></label></div>
<div class="col-md-4"><img src="img/products1.png"></div>
<div class="col-md-6">Amozon</div>
</div>

<div class="row">
<div class="col-md-2"><label class="checkpadright"><input type="checkbox"></label></div>
<div class="col-md-4"><img src="img/products2.png"></div>
<div class="col-md-6">Snapdeal</div>
</div>

<div class="row">
<div class="col-md-2"><label class="checkpadright"><input type="checkbox"></label></div>
<div class="col-md-4"><img src="img/products3.png"></div>
<div class="col-md-6">Flipkart</div>
</div>

</div>
</div>
</div>
</div>
</div>

</div>



</div>
</div>
<div class="col-lg-3">
<div class="productbox">

<div class="row">
<div class="col-md-6 col-xs-6">

<div class="form-group productchkbox"><div class="checkbox"><label><input type="checkbox"></label></div></div>

</div>
<div class="col-md-6 col-xs-6 pull-right">
<div class="form-group productchkdetalinks"><a href="#"><i class="fa fa-exclamation-circle"></i></a></div>

</div>
</div>


<div class="row">
<div class="col-md-8 col-md-offset-2 text-center imgresborder"><img src="img/fan.png" class="img-responsive"></div>
<div class="col-md-2">

<label class="switch">
	<input class="switch-input" type="checkbox" />
	<span class="switch-label"></span> 
</label>

</div>
</div>


<div class="row invoice-info productdetails">

<div class="col-sm-6 invoice-col">



<p>
Name:<br>
Type:<br>
SKU:<br>
Manufacture:<br>
Price:<br>
</p>
</div>
<div class="col-sm-6 invoice-col">
<p>
Orient Fan<br>
Finished Product<br>
Sku-487<br>
Orient<br>
$4.00<br>
</p>
</div>
</div>

<div class="row invoice-info productdetails">
<div class="col-sm-4 invoice-col"><p><strong>Channels</strong><br></p></div>
<div class="col-sm-8 invoice-col"><p>
<a href="#"><img src="img/products1.png"> </a>
<a href="#"><img src="img/products2.png">  </a>
<a href="#"><img src="img/products3.png">  </a>
<br></p></div>
</div>



<div class="row" style="margin-top:20px;">
<div class="col-md-10 text-center">
<a href="#"><span class="badge bg-light-blue"><i class="fa fa-pencil"></i></span></a>
<a href="#"><span class="badge bg-red"><i class="fa fa-trash-o"></i></span></a>
<a href="#AddChannel" data-toggle="modal"><span class="badge bg-green"><i class="fa fa-plus"></i></span></a>
</div>

<div class="col-md-2">
<a href="#Discription" data-toggle="modal" class="productmenu"><i class="fa fa-ellipsis-h"></i></a>
</div>

<div class="bs-example">
<div id="Discription" class="modal fade">
<div class="modal-dialog modal-dialog1">
<div class="modal-content">
<div class="modal-header">
<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
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
<div id="AddChannel" class="modal fade">
<div class="modal-dialog">
<div class="modal-content">
<div class="modal-body">

<div class="row">
<div class="col-md-2"><label class="checkpadright"><input type="checkbox"></label></div>
<div class="col-md-4"><img src="img/products1.png"></div>
<div class="col-md-6">Amozon</div>
</div>

<div class="row">
<div class="col-md-2"><label class="checkpadright"><input type="checkbox"></label></div>
<div class="col-md-4"><img src="img/products2.png"></div>

<div class="col-md-6">Snapdeal</div>
</div>

<div class="row">
<div class="col-md-2"><label class="checkpadright"><input type="checkbox"></label></div>
<div class="col-md-4"><img src="img/products3.png"></div>
<div class="col-md-6">Flipkart</div>
</div>

<div class="row">
<div class="col-md-2"><label class="checkpadright"><input type="checkbox"></label></div>
<div class="col-md-4"><img src="img/products1.png"></div>
<div class="col-md-6">Amozon</div>
</div>

<div class="row">
<div class="col-md-2"><label class="checkpadright"><input type="checkbox"></label></div>
<div class="col-md-4"><img src="img/products2.png"></div>
<div class="col-md-6">Snapdeal</div>
</div>

<div class="row">
<div class="col-md-2"><label class="checkpadright"><input type="checkbox"></label></div>
<div class="col-md-4"><img src="img/products3.png"></div>
<div class="col-md-6">Flipkart</div>
</div>

<div class="row">
<div class="col-md-2"><label class="checkpadright"><input type="checkbox"></label></div>
<div class="col-md-4"><img src="img/products1.png"></div>
<div class="col-md-6">Amozon</div>
</div>

<div class="row">
<div class="col-md-2"><label class="checkpadright"><input type="checkbox"></label></div>
<div class="col-md-4"><img src="img/products2.png"></div>
<div class="col-md-6">Snapdeal</div>
</div>

<div class="row">
<div class="col-md-2"><label class="checkpadright"><input type="checkbox"></label></div>
<div class="col-md-4"><img src="img/products3.png"></div>
<div class="col-md-6">Flipkart</div>
</div>

</div>
</div>
</div>
</div>
</div>

</div>



</div>
</div>
<div class="col-lg-3">
<div class="productbox">

<div class="row">
<div class="col-md-6 col-xs-6">

<div class="form-group productchkbox"><div class="checkbox"><label><input type="checkbox"></label></div></div>

</div>
<div class="col-md-6 col-xs-6 pull-right">
<div class="form-group productchkdetalinks"><a href="#"><i class="fa fa-exclamation-circle"></i></a></div>

</div>
</div>


<div class="row">
<div class="col-md-8 col-md-offset-2 text-center imgresborder"><img src="img/fan.png" class="img-responsive"></div>
<div class="col-md-2">

<label class="switch">
	<input class="switch-input" type="checkbox" />
	<span class="switch-label"></span> 
</label>

</div>
</div>


<div class="row invoice-info productdetails">

<div class="col-sm-6 invoice-col">



<p>
Name:<br>
Type:<br>
SKU:<br>
Manufacture:<br>
Price:<br>
</p>
</div>
<div class="col-sm-6 invoice-col">
<p>
Orient Fan<br>
Finished Product<br>
Sku-487<br>
Orient<br>
$4.00<br>
</p>
</div>
</div>

<div class="row invoice-info productdetails">
<div class="col-sm-4 invoice-col"><p><strong>Channels</strong><br></p></div>
<div class="col-sm-8 invoice-col"><p>
<a href="#"><img src="img/products1.png"> </a>
<a href="#"><img src="img/products2.png">  </a>
<a href="#"><img src="img/products3.png">  </a>
<br></p></div>
</div>



<div class="row" style="margin-top:20px;">
<div class="col-md-10 text-center">
<a href="#"><span class="badge bg-light-blue"><i class="fa fa-pencil"></i></span></a>
<a href="#"><span class="badge bg-red"><i class="fa fa-trash-o"></i></span></a>
<a href="#AddChannel" data-toggle="modal"><span class="badge bg-green"><i class="fa fa-plus"></i></span></a>
</div>

<div class="col-md-2">
<a href="#Discription" data-toggle="modal" class="productmenu"><i class="fa fa-ellipsis-h"></i></a>
</div>

<div class="bs-example">
<div id="Discription" class="modal fade">
<div class="modal-dialog modal-dialog1">
<div class="modal-content">
<div class="modal-header">
<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
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
<div id="AddChannel" class="modal fade">
<div class="modal-dialog">
<div class="modal-content">
<div class="modal-body">

<div class="row">
<div class="col-md-2"><label class="checkpadright"><input type="checkbox"></label></div>
<div class="col-md-4"><img src="img/products1.png"></div>
<div class="col-md-6">Amozon</div>
</div>

<div class="row">
<div class="col-md-2"><label class="checkpadright"><input type="checkbox"></label></div>
<div class="col-md-4"><img src="img/products2.png"></div>

<div class="col-md-6">Snapdeal</div>
</div>

<div class="row">
<div class="col-md-2"><label class="checkpadright"><input type="checkbox"></label></div>
<div class="col-md-4"><img src="img/products3.png"></div>
<div class="col-md-6">Flipkart</div>
</div>

<div class="row">
<div class="col-md-2"><label class="checkpadright"><input type="checkbox"></label></div>
<div class="col-md-4"><img src="img/products1.png"></div>
<div class="col-md-6">Amozon</div>
</div>

<div class="row">
<div class="col-md-2"><label class="checkpadright"><input type="checkbox"></label></div>
<div class="col-md-4"><img src="img/products2.png"></div>
<div class="col-md-6">Snapdeal</div>
</div>

<div class="row">
<div class="col-md-2"><label class="checkpadright"><input type="checkbox"></label></div>
<div class="col-md-4"><img src="img/products3.png"></div>
<div class="col-md-6">Flipkart</div>
</div>

<div class="row">
<div class="col-md-2"><label class="checkpadright"><input type="checkbox"></label></div>
<div class="col-md-4"><img src="img/products1.png"></div>
<div class="col-md-6">Amozon</div>
</div>

<div class="row">
<div class="col-md-2"><label class="checkpadright"><input type="checkbox"></label></div>
<div class="col-md-4"><img src="img/products2.png"></div>
<div class="col-md-6">Snapdeal</div>
</div>

<div class="row">
<div class="col-md-2"><label class="checkpadright"><input type="checkbox"></label></div>
<div class="col-md-4"><img src="img/products3.png"></div>
<div class="col-md-6">Flipkart</div>
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

</section>
</div>
<footer class="main-footer">
<strong>Copyright &copy; 2014-2015 <a href="#">eSealCentral</a>.</strong> All rights reserved.
</footer>

</div>

	

  <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
  <script src="js/bootstrap-select.js"></script>


<!-- jQuery 2.1.4 -->
<script src="js/plugins/jQuery/jQuery-2.1.4.min.js"></script>
<script src="http://code.jquery.com/ui/1.11.2/jquery-ui.min.js" type="text/javascript"></script>
<!-- Bootstrap 3.3.2 JS -->
<script src="js/bootstrap.min.js" type="text/javascript"></script>


<script type="text/javascript" src="http://code.jquery.com/jquery-latest.min.js"></script>
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
                                            <span class="jFiler-item-title"><b title="{{fi-name}}">{{fi-name | limitTo: 25}}</b></span>\
                                        </div>\
                                        {{fi-image}}\
                                    </div>\
                                    <div class="jFiler-item-assets jFiler-row">\
                                        <ul class="list-inline pull-left">\
                                            <li><span class="jFiler-item-others">{{fi-icon}} {{fi-size2}}</span></li>\
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
                                            <span class="jFiler-item-title"><b title="{{fi-name}}">{{fi-name | limitTo: 25}}</b></span>\
                                        </div>\
                                        {{fi-image}}\
                                    </div>\
                                    <div class="jFiler-item-assets jFiler-row">\
                                        <ul class="list-inline pull-left">\
                                            <span class="jFiler-item-others">{{fi-icon}} {{fi-size2}}</span>\
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
            changeInput: '<div class="jFiler-input-dragDrop"><div class="jFiler-input-inner"><div class="jFiler-input-icon"><i class="icon-jfi-cloud-up-o"></i></div><div class="jFiler-input-text"><h3>Drag&Drop files here</h3> <span style="display:inline-block; margin: 15px 0">or</span></div><a class="jFiler-input-choose-btn blue">Browse Files</a></div></div>',
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
                                            <span class="jFiler-item-title"><b title="{{fi-name}}">{{fi-name | limitTo: 25}}</b></span>\
                                        </div>\
                                        {{fi-image}}\
                                    </div>\
                                    <div class="jFiler-item-assets jFiler-row">\
                                        <ul class="list-inline pull-left">\
                                            <li>{{fi-progressBar}}</li>\
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
                                            <span class="jFiler-item-title"><b title="{{fi-name}}">{{fi-name | limitTo: 25}}</b></span>\
                                        </div>\
                                        {{fi-image}}\
                                    </div>\
                                    <div class="jFiler-item-assets jFiler-row">\
                                        <ul class="list-inline pull-left">\
                                            <span class="jFiler-item-others">{{fi-icon}} {{fi-size2}}</span>\
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
                    filesLimit: "Only {{fi-limit}} files are allowed to be uploaded.",
                    filesType: "Only Images are allowed to be uploaded.",
                    filesSize: "{{fi-name}} is too large! Please upload file up to {{fi-maxSize}} MB.",
                    filesSizeAll: "Files you've choosed are too large! Please upload files up to {{fi-maxSize}} MB."
                }
            }
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
<!-- jQuery Knob -->
<script src="js/plugins/knob/jquery.knob.js" type="text/javascript"></script>

<script src="js/plugins/jvectormap/jquery-jvectormap-1.2.2.min.js" type="text/javascript"></script>
<script src="js/plugins/jvectormap/jquery-jvectormap-world-mill-en.js" type="text/javascript"></script>


<!-- Sparkline -->
<script src="js/plugins/sparkline/jquery.sparkline.min.js" type="text/javascript"></script>

<!-- page script -->
<script type="text/javascript">
$(function () {
/* jQueryKnob */

$(".knob").knob({
/*change : function (value) {
//console.log("change : " + value);
},
release : function (value) {
console.log("release : " + value);
},
cancel : function () {
console.log("cancel : " + this.value);
},*/
draw: function () {

// "tron" case
if (this.$.data('skin') == 'tron') {

var a = this.angle(this.cv)  // Angle
, sa = this.startAngle          // Previous start angle
, sat = this.startAngle         // Start angle
, ea                            // Previous end angle
, eat = sat + a                 // End angle
, r = true;

this.g.lineWidth = this.lineWidth;

this.o.cursor
&& (sat = eat - 0.3)
&& (eat = eat + 0.3);

if (this.o.displayPrevious) {
ea = this.startAngle + this.angle(this.value);
this.o.cursor
&& (sa = ea - 0.3)
&& (ea = ea + 0.3);
this.g.beginPath();
this.g.strokeStyle = this.previousColor;
this.g.arc(this.xy, this.xy, this.radius - this.lineWidth, sa, ea, false);
this.g.stroke();
}

this.g.beginPath();
this.g.strokeStyle = r ? this.o.fgColor : this.fgColor;
this.g.arc(this.xy, this.xy, this.radius - this.lineWidth, sat, eat, false);
this.g.stroke();

this.g.lineWidth = 2;
this.g.beginPath();
this.g.strokeStyle = this.o.fgColor;
this.g.arc(this.xy, this.xy, this.radius - this.lineWidth + 1 + this.lineWidth * 2 / 3, 0, 2 * Math.PI, false);
this.g.stroke();

return false;
}
}
});
/* END JQUERY KNOB */

//INITIALIZE SPARKLINE CHARTS
$(".sparkline").each(function () {
var $this = $(this);
$this.sparkline('html', $this.data());
});

/* SPARKLINE DOCUMENTAION EXAMPLES http://omnipotent.net/jquery.sparkline/#s-about */
drawDocSparklines();
drawMouseSpeedDemo();

});
function drawDocSparklines() {

// Bar + line composite charts
$('#compositebar').sparkline('html', {type: 'bar', barColor: '#aaf'});
$('#compositebar').sparkline([4, 1, 5, 7, 9, 9, 8, 7, 6, 6, 4, 7, 8, 4, 3, 2, 2, 5, 6, 7],
{composite: true, fillColor: false, lineColor: 'red'});


// Line charts taking their values from the tag
$('.sparkline-1').sparkline();

// Larger line charts for the docs
$('.largeline').sparkline('html',
{type: 'line', height: '2.5em', width: '4em'});

// Customized line chart
$('#linecustom').sparkline('html',
{height: '1.5em', width: '8em', lineColor: '#f00', fillColor: '#ffa',
minSpotColor: false, maxSpotColor: false, spotColor: '#77f', spotRadius: 3});

// Bar charts using inline values
$('.sparkbar').sparkline('html', {type: 'bar'});

$('.barformat').sparkline([1, 3, 5, 3, 8], {
type: 'bar',
tooltipFormat: '{{value:levels}} - {{value}}',
tooltipValueLookups: {
levels: $.range_map({':2': 'Low', '3:6': 'Medium', '7:': 'High'})
}
});

// Tri-state charts using inline values
$('.sparktristate').sparkline('html', {type: 'tristate'});
$('.sparktristatecols').sparkline('html',
{type: 'tristate', colorMap: {'-2': '#fa7', '2': '#44f'}});

// Composite line charts, the second using values supplied via javascript
$('#compositeline').sparkline('html', {fillColor: false, changeRangeMin: 0, chartRangeMax: 10});
$('#compositeline').sparkline([4, 1, 5, 7, 9, 9, 8, 7, 6, 6, 4, 7, 8, 4, 3, 2, 2, 5, 6, 7],
{composite: true, fillColor: false, lineColor: 'red', changeRangeMin: 0, chartRangeMax: 10});

// Line charts with normal range marker
$('#normalline').sparkline('html',
{fillColor: false, normalRangeMin: -1, normalRangeMax: 8});
$('#normalExample').sparkline('html',
{fillColor: false, normalRangeMin: 80, normalRangeMax: 95, normalRangeColor: '#4f4'});

// Discrete charts
$('.discrete1').sparkline('html',
{type: 'discrete', lineColor: 'blue', xwidth: 18});
$('#discrete2').sparkline('html',
{type: 'discrete', lineColor: 'blue', thresholdColor: 'red', thresholdValue: 4});

// Bullet charts
$('.sparkbullet').sparkline('html', {type: 'bullet'});

// Pie charts
$('.sparkpie').sparkline('html', {type: 'pie', height: '1.0em'});

// Box plots
$('.sparkboxplot').sparkline('html', {type: 'box'});
$('.sparkboxplotraw').sparkline([1, 3, 5, 8, 10, 15, 18],
{type: 'box', raw: true, showOutliers: true, target: 6});

// Box plot with specific field order
$('.boxfieldorder').sparkline('html', {
type: 'box',
tooltipFormatFieldlist: ['med', 'lq', 'uq'],
tooltipFormatFieldlistKey: 'field'
});

// click event demo sparkline
$('.clickdemo').sparkline();
$('.clickdemo').bind('sparklineClick', function (ev) {
var sparkline = ev.sparklines[0],
region = sparkline.getCurrentRegionFields();
value = region.y;
alert("Clicked on x=" + region.x + " y=" + region.y);
});

// mouseover event demo sparkline
$('.mouseoverdemo').sparkline();
$('.mouseoverdemo').bind('sparklineRegionChange', function (ev) {
var sparkline = ev.sparklines[0],
region = sparkline.getCurrentRegionFields();
value = region.y;
$('.mouseoverregion').text("x=" + region.x + " y=" + region.y);
}).bind('mouseleave', function () {
$('.mouseoverregion').text('');
});
}

/**
** Draw the little mouse speed animated graph
** This just attaches a handler to the mousemove event to see
** (roughly) how far the mouse has moved
** and then updates the display a couple of times a second via
** setTimeout()
**/
function drawMouseSpeedDemo() {
var mrefreshinterval = 500; // update display every 500ms
var lastmousex = -1;
var lastmousey = -1;
var lastmousetime;
var mousetravel = 0;
var mpoints = [];
var mpoints_max = 30;
$('html').mousemove(function (e) {
var mousex = e.pageX;
var mousey = e.pageY;
if (lastmousex > -1) {
mousetravel += Math.max(Math.abs(mousex - lastmousex), Math.abs(mousey - lastmousey));
}
lastmousex = mousex;
lastmousey = mousey;
});
var mdraw = function () {
var md = new Date();
var timenow = md.getTime();
if (lastmousetime && lastmousetime != timenow) {
var pps = Math.round(mousetravel / (timenow - lastmousetime) * 1000);
mpoints.push(pps);
if (mpoints.length > mpoints_max)
mpoints.splice(0, 1);
mousetravel = 0;
$('#mousespeed').sparkline(mpoints, {width: mpoints.length * 2, tooltipSuffix: ' pixels per second'});
}
lastmousetime = timenow;
setTimeout(mdraw, mrefreshinterval);
};
// We could use setInterval instead, but I prefer to do it this way
setTimeout(mdraw, mrefreshinterval);
}


</script>
<script type="text/javascript">
$("#checkAll").click(function () {
    $(".check").prop('checked', $(this).prop('checked'));
});
</script>

</body>
</html>
