<!-- <!DOCTYPE html>
<html>
<head> -->
<meta charset="UTF-8">
<!-- <title>Address</title> -->
<meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>
<!-- Bootstrap 3.3.4 -->
<link href="/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
<!-- Font Awesome Icons -->
<link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css" rel="stylesheet" type="text/css" />
<!-- Ionicons -->
<link href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css" rel="stylesheet" type="text/css" />
<!-- Theme style -->
<link href="/css/AdminLTE.min.css" rel="stylesheet" type="text/css" />
<!-- AdminLTE Skins. Choose a skin from the css/skins 
folder instead of downloading all of them to reduce the load. -->
<link href="/css/_all-skins.min.css" rel="stylesheet" type="text/css" />

<style type="text/css">
/* custom inclusion of right, left and below tabs */

.tabs-below > .nav-tabs,
.tabs-right > .nav-tabs,
.tabs-left > .nav-tabs {
  border-bottom: 0;
}

.tab-content > .tab-pane,
.pill-content > .pill-pane {
  display: none;
}

.tab-content > .active,
.pill-content > .active {
  display: block;
}

.tabs-below > .nav-tabs {
  border-top: 1px solid #ddd;
}

.tabs-below > .nav-tabs > li {
  margin-top: -1px;
  margin-bottom: 0;
}

.tabs-below > .nav-tabs > li > a {
  -webkit-border-radius: 0 0 4px 4px;
     -moz-border-radius: 0 0 4px 4px;
          border-radius: 0 0 4px 4px;
}

.tabs-below > .nav-tabs > li > a:hover,
.tabs-below > .nav-tabs > li > a:focus {
  border-top-color: #ddd;
  border-bottom-color: transparent;
}

.tabs-below > .nav-tabs > .active > a,
.tabs-below > .nav-tabs > .active > a:hover,
.tabs-below > .nav-tabs > .active > a:focus {
  border-color: transparent #ddd #ddd #ddd;
}

.tabs-left > .nav-tabs > li,
.tabs-right > .nav-tabs > li {
  float: none;
}

.tabs-left > .nav-tabs > li > a,
.tabs-right > .nav-tabs > li > a {
  min-width: 74px;
  margin-right: 0;
  margin-bottom: 3px;
}

.tabs-left > .nav-tabs {
  float: left;
  margin-right: 19px;
  border-right: 1px solid #ddd;
}

.tabs-left > .nav-tabs > li > a {
  margin-right: -1px;
  -webkit-border-radius: 4px 0 0 4px;
     -moz-border-radius: 4px 0 0 4px;
          border-radius: 4px 0 0 4px;
}

.tabs-left > .nav-tabs > li > a:hover,
.tabs-left > .nav-tabs > li > a:focus {
  border-color: #eeeeee #dddddd #eeeeee #eeeeee;
}

.tabs-left > .nav-tabs .active > a,
.tabs-left > .nav-tabs .active > a:hover,
.tabs-left > .nav-tabs .active > a:focus {
  border-color: #ddd transparent #ddd #ddd;
  *border-right-color: #ffffff;
}

.tabs-right > .nav-tabs {
  float: right;
  margin-left: 19px;
  border-left: 1px solid #ddd;
}

.tabs-right > .nav-tabs > li > a {
  margin-left: -1px;
  -webkit-border-radius: 0 4px 4px 0;
     -moz-border-radius: 0 4px 4px 0;
          border-radius: 0 4px 4px 0;
}

.tabs-right > .nav-tabs > li > a:hover,
.tabs-right > .nav-tabs > li > a:focus {
  border-color: #eeeeee #eeeeee #eeeeee #dddddd;
}

.tabs-right > .nav-tabs .active > a,
.tabs-right > .nav-tabs .active > a:hover,
.tabs-right > .nav-tabs .active > a:focus {
  border-color: #ddd #ddd #ddd transparent;
  *border-left-color: #ffffff;
}
.spinner {
  width: 100px;
}
.spinner input {
  text-align: right;
}
.input-group-btn-vertical {
  position: relative;
  white-space: nowrap;
  width: 1%;
  vertical-align: middle;
  display: table-cell;
}
.input-group-btn-vertical > .btn {
  display: block;
  float: none;
  width: 100%;
  max-width: 100%;
  padding: 8px;
  margin-left: -1px;
  position: relative;
  border-radius: 0;
}
.input-group-btn-vertical > .btn:first-child {
  border-top-right-radius: 4px;
}
.input-group-btn-vertical > .btn:last-child {
  margin-top: -2px;
  border-bottom-right-radius: 4px;
}
.input-group-btn-vertical i{
  position: absolute;
  top: 0;
  left: 4px;
}

    .piclist{
        margin:0 auto; margin-top:10px; padding:3px;
    }
    .piclist li{
        display: inline-block;
        width: 50px;
        height: 50px; margin-right:5px; border:1px solid #B7B7B7; padding:2px;
    }
    .piclist li img{
        width: 100%;
        height: auto; cursor:pointer;
    }

    /* custom style */
    .picZoomer-pic-wp,
    .picZoomer-zoom-wp{
        border: 1px solid #fff;
    }


</style>

<link rel="stylesheet" type="text/css" href="/zoom/jquery-picZoomer.css">
<script src="{{URL::asset('http://code.jquery.com/jquery-1.11.3.min.js')}}"></script>
    <script type="text/javascript" src="/zoom/jquery.picZoomer.js"></script>
    <script type="text/javascript">
        $(function() {
            $('.picZoomer').picZoomer();
            $('.piclist li').on('click',function(event){
                var $pic = $(this).find('img');
                $('.picZoomer-pic').attr('src',$pic.attr('src'));
            });
        });
    </script>

</head>
<body class="skin-blue sidebar-mini">
<!-- <div class="wrapper">



<aside class="main-sidebar">
<section class="sidebar">


</section>
</aside> -->


<div class="content-wrapper">


<!-- <section class="content-header">
<ol class="breadcrumb">
<li><a href="#"><i class="glyphicon glyphicon-home"></i></a></li>
<li><a class="active">Containers</a></li>
<li><a>Bulk Containers</a></li>
<li><a>Pallet Containers</a></li>
<li><a>Metal Pallets</a></li>
 -->
</ol>
</section>



<section class="content">


<div class="row">

            
<div class="col-md-4">
<div class="box box-primary">
<div class="box-body">

<div class="row">
<div class="col-md-12">
<div class="picZoomer">
<img src="images/0.jpg" width:"100%" alt="">
</div>
</div>
<div class="col-md-12">
<ul class="piclist">
  <?php //echo "<Pre>";print_R($productData);die; ?>
@foreach ($productData->media_data as $value)

<li><img src="<?php echo \URL::to('/') . '/uploads/products/'?>{{$value->url}}" alt=""></li>
@endforeach
<!-- <li><img src="/images/1.jpg" alt=""></li>
<li><img src="/images/2.jpg" alt=""></li>
<li><img src="/images/3.jpg" alt=""></li>
<li><img src="/images/4.jpg" alt=""></li> -->
</ul>


</div>

</div>

</div>
</div>

</div>
            
            
<div class="col-md-8">
<div class="box box-primary">
<div class="box-body">


<div class="tabbable tabs-left">
<ul class="nav nav-tabs">
<li class="active"><a href="#a" data-toggle="tab">Product</a></li>
<!-- <li><a href="#b" data-toggle="tab">Technical Specifications</a></li> -->
<!-- <li><a href="#c" data-toggle="tab">Features</a></li> -->
<!-- <li><a href="#d" data-toggle="tab">Product Info</a></li> -->
<li><a href="#e" data-toggle="tab">Warranty</a></li>
<!-- <li><a href="#f" data-toggle="tab">Highlights</a></li> -->

</ul>
<div class="tab-content">


<div class="tab-pane active tabscontent" id="a">


<h1>{{$productData->name}}</h1>
<h3>{{$productData->title}}</h3>
<hr>

<!-- <p class="padleft">
<a href="#"><i class="fa fa-star text-yellow"></i></a>
<a href="#"><i class="fa fa-star text-yellow"></i></a>
<a href="#"><i class="fa fa-star text-yellow"></i></a>
<a href="#"><i class="fa fa-star text-gray"></i></a>
<a href="#"><i class="fa fa-star text-gray"></i></a>
<span>Have a question? <small>Details</small></span>
</p> -->

<p>{{$productData->description}} </p>



<div class="row">

<div class="col-md-4 prices">
<p class="mrp">Mrp: {{$productData->mrp}}</p>
<!-- <p>Sale : 2,145.00</p> -->
<p class="margn"><big>Margin : </big> <small>Inclusive of all taxes</small></p>
</div>

<!-- <div class="col-md-4">
<p><strong>Quantity</strong></p>
  
<div class="input-group spinner">
<input type="text" class="form-control" value="1">
<div class="input-group-btn-vertical padleft">
<button class="btn btn-default" type="button"><i class="fa fa-caret-up"></i></button>
<button class="btn btn-default" type="button"><i class="fa fa-caret-down"></i></button>
</div>
</div>  

</div> -->


</div>

<div class="clearfix"></div>


<div class="row addresses">
<div class="col-md-4 col-md-offset-3">
<!-- <address>
<strong>Shipping, Delivery & Returns</strong><br>
Ships in 24 hours:<br>
VAT/Shipping Charges may be applicable <br>
Return within 30 days  ?
</address>
 -->
</div>

</div>

</div>

<div class="tab-pane tabscontent" id="b">

<div class="row">
<div class="col-md-6">

<h1>General</h1>

<p><strong>Type :</strong> <i>Ceiling Fan</i></p>	
<p><strong>Brand :</strong> <i>Orient</i></p>
<p><strong>Model Name :</strong> <i>{{$productData->model_name}}</i></p>
<p><strong>Fan size :</strong> <i>(inches)	48</i></p>
<p><strong>Max Speed  :</strong> <i>(RPM)	320</i></p>
<p><strong>Air Displacement :</strong> <i>230(cu.mtr/min)</i></p>	
<p><strong>Sweep :</strong> <i>1200mm</i></p>
<p><strong>Additional Features</strong></p>

<h1>Technical</h1>

<p><strong>Air Flow :</strong> <i>225cfm</i></p>
<p><strong>Power Consumption :</strong> <i>65watts</i></p>
<p><strong>Power Requirement :</strong> <i>220v</i></p>
<p><strong>Frequency :</strong> <i>50 hertz</i></p>


</div>
</div>

</div>

<div class="tab-pane tabscontent" id="c">

<h1>Features</h1>

<p><strong>Number of Speed Setting :</strong> <i>4</i></p>
<p><strong>Blade Material :</strong> <i>Steel</i></p>
<p><strong>Blade Sweep :</strong> <i>1200mm</i></p>
<p><strong>Bearing Type Double :</strong> <i>Bell</i></p>
<p><strong>Number of Bearing :</strong> <i>1</i></p>
<p><strong>Remote Control :</strong> <i>No</i></p>
<p><strong>Other Features :</strong></p>

</div>


<div class="tab-pane tabscontent" id="d">

<p>4Safe, Durable, Fireproof, Rust-Free Aluminum Pallets. Pallets like these pay for themselves over time. Use them internally or in a closed-loop distribution system. All board and runners are made from extruded aluminum.
</p>

<ul class="list-unstyled">

<li><i class="fa fa-check"></i> 7 top deck boards, 3 in between each board</li>
<li><i class="fa fa-check"></i> 4-way runners</li>
<li><i class="fa fa-check"></i> 4500 lb capacity</li>
<li><i class="fa fa-check"></i> 4 pc minimum</li>
<li><i class="fa fa-check"></i> Add $45 ea for orders less than 26 pcs</li>
<li><i class="fa fa-check"></i> Add $15 ea for orders from 26 - 99 pcs</li>

</ul>

</div>


<div class="tab-pane tabscontent" id="e">
@if(isset($warrantyDetails[0]))
<p><strong>Warranty Policy :</strong>{{$warrantyDetails[0]->warranty_policy}} </p>
@endif
<!-- <p><strong>Warranty Period :</strong> </p>
<p><strong>Replacement Warrantee :</strong> </p>
<p><strong>Warranty Service Type :</strong> </p> -->

</div>



<div class="tab-pane tabscontent" id="f">
Thirdamuno, ipsum dolor sit amet, consectetur adipiscing elit. Duis pharetra varius quam sit amet vulputate.
</div>

</div>
</div>

</div> 

</div>
</div>
</div>

<div class="row">
  <div>
<p>
<!-- <button class="btn  bg-olive margin">approve</button> -->
<?php if($productData->content_approved == 0 && $productData->content_rejected == 0) { ?>
<button class="btn bg-olive margin"  onclick="approve({{$productData->product_id}},1);">Approve</button>
<button class="btn bg-orange margin" onclick="approve({{$productData->product_id}},0);">Reject</button>
<?php } if($productData->content_approved == 1 && $productData->content_rejected == 0) {  ?>
<button class="btn bg-orange margin" onclick="approve({{$productData->product_id}},0);">Reject</button>
<?php } if($productData->content_approved == 0 && $productData->content_rejected == 1) { 
?>
<button class="btn bg-olive margin" onclick="approve({{$productData->product_id}},1);">Approve</button>
<?php } 
?>

</p>
</div>

</div>
</section>

<script type="text/javascript">

  //   function approve(productId,type)
  // {
   
    

  //    $.get('/products/approve/?product_id='+productId+'&type='+type,function(response){ 
            
  //           console.log(response);
  //          alert(response);
            
            
  //       });
  // }

</script>


</div>
<footer class="main-footer">
<strong>Copyright &copy; 2014-2015 <a href="#">eSealCentral</a>.</strong> All rights reserved.
</footer>

</div>


<!-- jQuery 2.1.4 -->
<!-- Bootstrap 3.3.2 JS -->
@section('script')
<!-- <script src="{{URL::asset('/js/bootstrap.min.js" type="text/javascript')}}"></script> -->

<!-- <script src="{{URL::asset('/js/bootstrap-select.js')}}"></script> -->

<!-- <script src="{{URL::asset('/scripts/jquery-1.10.2.min.js')}}"></script> -->
@stop
</body>
</html>
