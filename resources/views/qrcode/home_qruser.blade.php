@extends('layouts.default')
@extends('layouts.header')
@extends('layouts.sideview')
@section('content')
@section('style')
    {{HTML::style('jqwidgets/styles/jqx.base.css')}}    
    <style>

#dvLoading
{
   background:#000 url(public/img1/ajax-loader.gif) no-repeat center center;
   height: 100px;
   width: 100px;
   position: fixed;
   z-index: 1000;
   left: 50%;
   top: 50%;
   margin: -25px 0 0 -25px;
}
</style>

 <!-- Latest compiled and minified CSS -->
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">

<!-- jQuery library -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>

<!-- Latest compiled JavaScript -->
<!-- <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>  -->

@stop
<div align="center" id="loading" style="text-align:center; z-index:9999;position:absolute;background:rgba(0,0,0,0.3);height:709px; width:1261px; display:none;" ><img src="/img/loading.gif">    </div>
<div class="box">
<!--     <div class="box-header">
      <h3 class="box-title"><i class="fa fa-th"></i><strong>Print   </strong> Label </h3>
    </div> -->
    <div class="box-body"><br><br><br>
     <center><h3> Hello <b>{{Session::get('userName')}}</b>,</h3><!-- <h2>Welcome to Eseal <h3>E-commerces ECO System</h3> </h2> -->
      <img src="/download/qrimages/Logo-CLR-BW-1.jpg" class="img-responsive" style="width: 420px;">
      <br><br><br><br><br>
      </center>
    </div>
    <div class="box-footer">
    </div>  
  </div>
  @stop