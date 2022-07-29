@extends('layouts.default')

@extends('layouts.header')

@extends('layouts.sideview')

@section('content')
<style>
.box-header i {
    margin-left: 0px !important;
}
ul, menu, dir {
    display: block;
    list-style-type: none;
    -webkit-margin-before: 0em;
    -webkit-margin-after: 0em;
    -webkit-margin-start: 0px;
    -webkit-margin-end: 0px;
    -webkit-padding-start: 0px !important;
}
.btn-group-horzintal{
		position: absolute;
    text-align: right;
    top: -35px;
	right:70px;
}
.dropdown-menu{
	min-width:100% !important;
}
input{
	width:100% !important;
	border:1px solid #f4f4f4 !important;
	padding:6px !important;
	
}
</style>

          
          
 			<div class="box">
              <div class="box-header with-border">
                <h3 class="box-title"><strong>Create </strong> Order</h3>
              </div>
               
               <div class="col-sm-12">
                 <div class="tile-body nopadding">                  
          
                                 
              <input type="hidden" id="check_id" name="check_id" value="{{$id}}">
           			 <div class="btn-group-horzintal">
                        <ul>
                          <li class="dropdown settings" data-toggle="tooltip" data-original-title="Filter&nbsp;View"> 
                              <a class="dropdown-toggle options" data-toggle="dropdown" href="#" > 
                                <i class="ion-funnel" style="font-size:22px;"></i>
                              </a>
                            <ul class="dropdown-menu arrow">
                               <li onclick='test({{$temp_id}},{{$ima_id}},{{$temp_cust_id}})'><a href="#"><i class="fa fa-credit-card"></i>All</a> </li>
                               <li><a href="#"><i class="fa fa-credit-card"></i>AIDC</a> </li>
                               @if(!empty($Aidcchildren))
                               @foreach($Aidcchildren as $key=>$result)
                               <li><a onclick='test({{$temp_id}},{{$ima_id}},{{$temp_cust_id}})'>-{{$result}}</a> </li>
                               @endforeach
                               @endif
                               <li><a href="#"><i class="fa fa-credit-card"></i>IOT</a> </li>
                               @if(!empty($Iotchildren))
                               @foreach($Iotchildren as $key=>$result)
                               <li><a onclick='test({{$temp_id}},{{$ima_id}},{{$temp_cust_id}})'>-{{$result}}</a> </li>
                               @endforeach
                               @endif
                               <li><a href="#"><i class="fa fa-credit-card"></i>PLAN</a> </li>
                               @if(!empty($Planchildren))
                               @foreach($Planchildren as $key=>$result)
                               <li><a onclick='test({{$temp_id}},{{$ima_id}},{{$temp_cust_id}})'>-{{$result}}</a> </li>
                               @endforeach
                               @endif
                               <li><a href="#"><i class="fa fa-credit-card"></i>APPS</a> </li>
                                @if(!empty($Appschildren))
                                @foreach($Appschildren as $key=>$result)
                               <li><a onclick='test({{$temp_id}},{{$ima_id}},{{$temp_cust_id}})'>-{{$result}}</a> </li>
                               @endforeach
                               @endif
                            </ul>
                          </li>
                        </ul>
					</div>
                   
        <div id="cbp-vm" class="cbp-vm-switcher cbp-vm-view-grid">
                  
          <div class="cbp-vm-options">
                      
            <a href="#" data-view="cbp-vm-view-grid" data-toggle="tooltip" data-original-title="Grid View"><i class="ion-ios-keypad-outline"></i></a>
            <a href="#" data-view="cbp-vm-view-list" data-toggle="tooltip" data-original-title="List View"><i class="ion-ios-list-outline"></i></a>
            <a class="" style="display:none" href="/orders/checkOut/{{$ima_id}}/{{$id}}/{{$temp_cust_id}}" onclick="cartsubmit()" data-toggle="tooltip" data-original-title="Cart"><i class="ion-ios-cart-outline"></i><span id="cart">{{$cart_qty[0]->cart_qty}}</span></a>
          </div>
                   
                    {{Form::open(array('url' => 'orders/checkOut/'.$ima_id.'/'.$id.'/'.$temp_cust_id))}}
                    {{ Form::hidden('_method', 'POST') }}
                    <ul>
                        @foreach($finalcomponentarr as $key=>$result)
                        
                        <li>
                          
                           <a class="cbp-vm-image" href="#"><img src="{{$result->image_url}}"></a>
                            <h3 class="cbp-vm-title">{{$result->name}}</h3>
                            <div class="cbp-vm-details">
                            {{substr($result->description,0,50)}}
                           </div>
                            @if(empty($cust_id))
                            <div class="cbp-vm-price">{{$result->price}}</div>
                            @else
                            <div class="cbp-vm-price-agreed">{{$result->price}}</div>
                            <div class="cbp-vm-price" >{{$result->agreed_price}}</div>
                            @endif
                          <div class="cbp-vm-table">
                          <table border="0" width="100%">
                              <tbody>
                                <tr>
                                  <td>
                                    <button type="button" class="btn btn1 btn-default btn-default1"><i class="fa fa-gratipay"></i></button>
                                  </td>
                                  <td>
                        @if(!empty($cust_id))
                        @if($cart_qty[0]->qty!="") 
                        <input id="qty{{$result->customer_product_plan_id}}"  name="qty{{$result->customer_product_plan_id}}" type="number" min=0 step="100000" class="rating spinner" value="0"   data-size="xl"
    data-symbol="&#xe005;" data-default-caption="{rating} hearts" data-star-captions="{}">
                        @else
                        <input id="qty{{$result->customer_product_plan_id}}"  name="qty{{$result->customer_product_plan_id}}" type="number" min=0 step="100000" class="rating spinner" value="0" data-size="xl"
    data-symbol="&#xe005;" data-default-caption="{rating} hearts" data-star-captions="{}">
                         @endif
                                   @else
                                   <input id="qty{{$result->id}}"  name="qty{{$result->id}}" type="number" min=0 step="100000" class="rating spinner" value="0"  data-size="xl"
    data-symbol="&#xe005;" data-default-caption="{rating} hearts" data-star-captions="{}">
                                   @endif
                                  </td>
                                  <td>
                                    @if(!empty($cust_id))
                                    <button type="button" id="add{{$result->customer_product_plan_id}}" name="add{{$result->customer_product_plan_id}}" onclick="getTotal({{$result->customer_product_plan_id}},{{$ima_id}})" class="btn btn1 btn-default btn-default2"><i class="fa fa-shopping-cart"></i></button>
                                    @else
                                    <button type="button" id="add{{$result->id}}" name="add{{$result->id}}" onclick="getTotal({{$result->id}},{{$ima_id}})" class="btn btn1 btn-default btn-default2"><i class="fa fa-shopping-cart"></i></button>
                                    @endif
                                  </td>
                                </tr>
                              </tbody>
                            </table>                            

                            <br/>
                            <div>
                            @if(!empty($cust_id))
                                    <button type="button" id="add{{$result->customer_product_plan_id}}" name="add{{$result->customer_product_plan_id}}" onclick="getTotal({{$result->customer_product_plan_id}},{{$ima_id}})" class="btn btn-primary">Add To Cart</button>
                                    @else
                                    <button type="button" id="add{{$result->id}}" name="add{{$result->id}}" onclick="getTotal({{$result->id}},{{$ima_id}})" class="btn btn-primary">Add To Cart</button>
                                    @endif
                                    </div>
                            <div id="cart_message"></div>
                       
                            </div>
                        </li>
                       @endforeach
                        
                    </ul>
                   
                           <div class="navbar-fixed-bottom" role="navigation">
                        <div id="content" class="col-md-12">
                    <button type="submit" onclick="return verifycheckout(<?php if(isset($cart_qty[0]->qty)) echo $cart_qty[0]->qty ; else echo 0;  ?>)"  class="btn btn-primary ">Check Out</button>
           </div>           
        </div>
        {{Form::close()}}
        </div>
      </div>
      </div>
        </div>
      </div>
          <!-- /content container -->

          <!-- Fixed bar-->

    <!-- /Fixed end -->
@stop
@section('script')
 <script type="text/javascript">

function verifycheckout(qty){
  /*alert(qty);*/
form.reload();

 if(qty!=0)
  {
   form.submit();

  }
  else
     alert('Your cart is empty');
    return false;
    
}
function getTotal(id,ima_id){
 
   
   var qty = $('#qty'+id).val();
   var check_id= document.getElementById('check_id').value;
   
  if(qty!=0)
  {
  $.ajax({
      url: "/orders/addCart", //This is the page where you will handle your SQL insert
      type: "GET",
      data: "id=" + id + '&qty=' + qty + '&ima_id=' + ima_id + '&check_id=' + check_id+ '&check_id=' + check_id, //The data your sending to some-page.php
      success: function(response){
        //console.log("AJAX request was successfull");
        document.getElementById('cart').innerHTML= + response;
        document.getElementById('cart_message').innerHTML = 'Added to cart Successfully';
         $("#cart_message").addClass("callout callout-success");
             
      },
      error:function(){
         // console.log("AJAX request was a failure");
      }   
    });
  }
  else{
    alert('Please select Quantity');
  }
}

function cartsubmit()
{
  var count=document.getElementById('cart').innerHTML;
  if(count==0){
    alert('Your Cart Is Empty');
    //return false;
  }

}

function test(id,ima_id,temp_cust_id)
{   
  //alert(cust_id);
  window.location = "/orders/createOrder/"+ id +'/'+ima_id+'/'+temp_cust_id;
     
}
 </script>
 @stop