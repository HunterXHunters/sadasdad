@extends('layouts.default')

@extends('layouts.header')

@extends('layouts.sideview')

@section('content')

<style>
.col-md-3 {
    width: 20% !important;
  padding-right:0px !important;
}
.col-sm-1 {
    width: 6.333333% !important;
    padding-left: 0px!important;
    padding-right: 0px!important;
}

.ajax-loader {
  display:none;
  position: fixed;
  left: 0px;
  top: 0px;
  width: 100%;
  height: 100%;
  z-index: 9999;
  opacity: 1;
}

.ajax-loader img {
  position: relative;
  top:30%;
  left:50%;
}
.info-box-icon{
  background: white;
  margin: 0;
  padding:0;
}
</style>

<div class="box collapsed-box" id="report">
  <div class="box-header with-border">
    <h3 class="box-title"><strong>IOT</strong> Statement</h3>
    <div class="box-tools1 pull-right">
              <button class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-filter"></i></button>
            </div>
  </div><!-- /.box-header -->
  <div class="box-body">
  @if(!empty($customers))
  
  
    <div class="form-group col-sm-5">
      <label for="exampleInputEmail">Choose Customer</label>
      <div id="selectbox">
      <!-- {{ Form::open(array('url' => '/dashboard/iotBankReport','method'=>'POST','id'=>'IOTBFrm','role'=>"form",'name'=>'IOTBFrm')) }}
       -->  <select class="form-control requiredDropdown" id="customer_id" name="customer_id"       parsley-trigger="change" parsley-required="true" parsley-error-container="#selectbox" onchange="getproductsandlocations()" >
          <option  value="">Select Customer</option>
            @foreach($customers as $customer)  
              <option value="{{$customer->customer_id}}" @if($customerId == $customer->customer_id) selected="selected" @endif>{{$customer->brand_name}}</option>
            @endforeach
        </select>
        <!-- {{Form::close()}} -->
      </div>
    </div>
    <div class="clr"></div>
    @endif
    <?php 
      if(!empty($customers)){ 
        $class= 'col-md-3';
      }else{
      $class="col-md-5";
      }

    ?>
    <div class="form-group {{$class}}">
                <label for="exampleInputEmail">From</label>
                <div class="input-group ">
                    <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
                    <div id="">
                        <input type="date" name="from_date" id="from_date">
                     </div>
                 </div>
             </div>



            <div class="form-group {{$class}}">
                <label for="exampleInputEmail">To</label>
                <div class="input-group ">
                    <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
                    <div id="">
                        <input type="date" name="to_date" id="to_date">
                    </div>
                </div>
            </div>

             <div class="form-group col-sm-5">
                <label for="exampleInputEmail">Product</label>
                <div class="input-group ">
                    <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
                    <div id="selectbox" style="z-index:-1">
                        <select class="list-unstyled selectpicker form-control" data-live-search="true" id="product_id" name="product_id"
                                parsley-trigger="change" parsley-required="true" parsley-error-container="#selectbox" >
                            <option  value="0">Select Product</option>
                               @foreach($products as $id=>$product)
                              <option value="{{$id}}">{{$product}}</option>
                            @endforeach
                        </select>
                        
                    
                    </div>
                </div>
            </div>

             <div class="form-group col-sm-5">
                <label for="exampleInputEmail">Location</label>
                <div class="input-group ">
                    <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
                    <div id="selectbox" style="z-index:-1">
                        <select class="list-unstyled selectpicker form-control" data-live-search="true" id="location_id" name="location_id"
                                parsley-trigger="change" parsley-required="true" parsley-error-container="#selectbox" >
                            <option  value="0">Select Location</option>
                            @foreach($locations as $id=>$location)
                              <option value="{{$id}}">{{$location}}</option>
                            @endforeach
                        </select>

                        
                    </div>
                </div>
            </div>
            <div class="form-group col-sm-2">
                
              <button class="pull-right btn btn-primary" id="filter_btn">Filter</button>  
              
            </div>
    
  </div>
  
  
  
  <div class="row">
    <div class="col-md-3 col-sm-6 col-xs-12">
          <div class="info-box">
            <!-- <span class="info-box-icon bg-aqua"><img src="/img/totalInventory.png" width="90px" /></span> -->
            <div class="info-box-content">
               <span class="info-box-text">Total Issued</span>
              <span class="info-box-number data" id="total_iots"></span>
              <span class="loader"><img src="{{ asset('jqwidgets/styles/images/loader.gif') }}" class="img-responsive" /></span>
            </div><!-- /.info-box-content -->
          </div><!-- /.info-box -->
        </div><!-- /.col -->
        <div class="col-md-3 col-sm-6 col-xs-12">
          <div class="info-box">
            <!-- <span class="info-box-icon bg-red"><img src="/img/AVAILABLE_INVENTORY.png" width="90px" style="margin-top: -10px;" /></span> -->
            <div class="info-box-content">
              <span class="info-box-text">Total Available</span>
              <span class="info-box-number data"id="available_download_iots"></span>
              <span class="loader"><img src="{{ asset('jqwidgets/styles/images/loader.gif') }}" class="img-responsive" /></span>
            </div><!-- /.info-box-content -->
          </div><!-- /.info-box -->
        </div><!-- /.col -->
<?PHP /*        <div class="col-md-4 col-sm-6 col-xs-12">
          <div class="info-box">
            <span class="info-box-icon bg-red"><img src="/img/AVAILABLE_INVENTORY.png" width="90px" style="margin-top: -10px;" /></span>
            <div class="info-box-content">
              <span class="info-box-text">Printed Available IOTs</span>
              <span class="info-box-number"id="available_issue_iots">{{$data->available_issue_IOTs}}</span>
            </div><!-- /.info-box-content -->
          </div><!-- /.info-box -->
        </div><!-- /.col -->
        <div class="clearfix visible-sm-block"></div>

        <div class="col-md-4 col-sm-6 col-xs-12">
          <div class="info-box">
            <span class="info-box-icon bg-green"><img src="/img/SOLD_INVENTORY.png" width="90px" /></span>
            <div class="info-box-content">
              <span class="info-box-text">Used Printed IOTs</span>
              <span class="info-box-number" id="used_issue_iots">{{$data->used_issue_IOTs}}</span>
            </div><!-- /.info-box-content -->
          </div><!-- /.info-box -->
        </div><!-- /.col --> */ ?>
        <div class="col-md-3 col-sm-6 col-xs-12">
          <div class="info-box">
            <!-- <span class="info-box-icon bg-green"><img src="/img/sold.png" width="90px" /></span> -->
            <div class="info-box-content">
              <span class="info-box-text">Total Used</span>
              <span class="info-box-number data" id="used_download_iots"></span>
              <span class="loader"><img src="{{ asset('jqwidgets/styles/images/loader.gif') }}" class="img-responsive" /></span>
            </div><!-- /.info-box-content -->
          </div><!-- /.info-box -->
        </div><!-- /.col -->
        <div class="col-md-4 col-sm-6 col-xs-12">
          <div class="info-box">
            <!-- <span class="info-box-icon bg-green"><img src="/img/sold.png" width="90px" /></span> -->
            <div class="info-box-content">
              <span class="info-box-text">Total Downloaded But Not Used</span>
              <span class="info-box-number data" id="notused_download_iots"></span>
              <span class="loader"><img src="{{ asset('jqwidgets/styles/images/loader.gif') }}" class="img-responsive" /></span>
            </div><!-- /.info-box-content -->
          </div><!-- /.info-box -->
        </div><!-- /.col -->
        
        <div class="col-md-4 col-sm-6 col-xs-12" >
          <div class="info-box">
            <span class="info-box-icon"><img src="/img/product.jpg" width="50px" height="60px" /></span>
            <div class="info-box-content">
              <span class="info-box-text">Used for Products</span>
              <span class="info-box-number data" id="primary_iots"></span>
              <span class="loader"><img src="{{ asset('jqwidgets/styles/images/loader.gif') }}" class="img-responsive" /></span>
            </div><!-- /.info-box-content -->
          </div><!-- /.info-box -->
        </div><!-- /.col -->
        <div class="col-md-4 col-sm-6 col-xs-12" >
          <div class="info-box">
            <span class="info-box-icon "><img src="/img/carton.png" width="90px" height ="110px" /></span>
            <div class="info-box-content">
              <span class="info-box-text">Used for Cartons</span>
              <span class="info-box-number data" id="secondary_iots"></span>
              <span class="loader"><img src="{{ asset('jqwidgets/styles/images/loader.gif') }}" class="img-responsive" /></span>
            </div><!-- /.info-box-content -->
          </div><!-- /.info-box -->
        </div><!-- /.col -->
        <div class="col-md-4 col-sm-6 col-xs-12" >
          <div class="info-box">
           <span class="info-box-icon "><img src="/img/tp.png" width="80px" height ="80px" /></span>
            <div class="info-box-content">
              <span class="info-box-text">Used for Transport</span>
              <span class="info-box-number data" id="tp_iots"></span>
              <span class="loader"><img src="{{ asset('jqwidgets/styles/images/loader.gif') }}" class="img-responsive" /></span>
            </div><!-- /.info-box-content -->
          </div><!-- /.info-box -->
        </div><!-- /.col -->
  </div>
</div>
<!-- <div class="ajax-loader">
  <img src="{{ asset('jqwidgets/styles/images/loader.gif') }}" class="img-responsive" />
</div> -->

<script>

$(document).ready(function(){
   iotbankreportdata();
});

function iotbankreportdata(){
  var  customer_id  = $('#customer_id').val();
  var from_date = $('#from_date').val();
  var to_date = $('#to_date').val();
  var product = $('#product_id').val();
  var location =$('#location_id').val(); 

  $.ajax({
                        url: "/dashboard/getiotbankreportdata",
                        type: "GET",
                        data: 'customer_id='+customer_id+'&product_id='+product+'&location_id='+location+'&from_date='+from_date+'&to_date='+to_date,
                        //data: "module_id=" + module_id + "&access_token=" + access_token,
                        beforeSend: function(){
                          //$('#report').fadeOut();
                          //$('#report').css('opacity',0.3);
                          //$('.ajax-loader').show();
                          $('.data').hide();
                          $('.loader').show();
                        },
                        success: function(response)
                        {
                          //alert(response);
                            //var request_time = new Date().getTime() - start_time;
                            //alert(request_time);
                            //window.location = "/dashboard/inventoryReport";
                       //     alert("success");
                            $('.loader').hide();
                            $('.data').show();
                            $('#total_iots').html(response['total_IOTs']);
                            $('#available_download_iots').html(response['total_available_IOTs']);
                            //$('#available_issue_iots').html(response['available_issue_IOTs']);
                            //$('#used_issue_iots').html(response['used_issue_IOTs']);
                            $('#used_download_iots').html(response['total_used_IOTs']);
                            $('#notused_download_iots').html(response['downloaded_but_notused']);
                            $('#primary_iots').html(response['primary_IOTs']);
                            $('#secondary_iots').html(response['secondary_IOTs']);
                            $('#tp_iots').html(response['tp_IOTs']);
                        },
                        complete: function(){
                          //$('#report').fadeIn();
                          //$('#report').css('opacity',1);
                          //$('.ajax-loader').hide();
                          
                       
                        },
                        error:function()
                        {
                         // alert("error");
                          $('#total_iots').html("");
                            $('#available_download_iots').html("");
                            $('#available_issue_iots').html("");
                            $('#used_issue_iots').html("");
                            $('#used_download_iots').html("");
                            $('#notused_download_iots').html("");
                            $('#primary_iots').html("");
                            $('#secondary_iots').html("");
                            $('#tp_iots').html("");
                          $('.loader').hide();
                          $('.data').show();
                        }
                    }
                );
}

function getproductsandlocations(){
  var cust=$('#customer_id').val();
  $.ajax({
    url:"getproductsandlocationsbycustomerId",
    data:'customer_id='+cust,
    method:'get',
    success:function(response){
      $('#product_id').empty();
      $('#location_id').empty();
      opt = new Option('select Product',0);
      $('#product_id').append(opt);
      opt = new Option('select Location',0);
      $('#location_id').append(opt);

      $.each(response['products'],function(i,val){
        opt =new Option(val,i);
        $('#product_id').append(opt);

      });
      $.each(response['locations'],function(i,val){
        opt = new Option(val,i);
        $('#location_id').append(opt);
      });

    }
  });
}
function showproducts(product){
  if (product.length==0) {
    //document.getElementById("livesearch").innerHTML="";
    $('#productsearch').html("");
    $('#productsearch').css('border','0px');
    return;
  }
  else{
    $('#productsearch').html("<a href='product1' target='_blank'><hhhh/a>");
    $('#productsearch').css('border','1px solid #A5ACB2');
  }
  
 //alert(product);
}

$(function() {
     
     $('#from_date').datepicker({ dateFormat: 'yy-mm-dd'}); 
     $('#to_date').datepicker({ dateFormat: 'yy-mm-dd'}); 
  
});

$('#filter_btn').on('click',function(){
  iotbankreportdata();
});
</script>

@stop
