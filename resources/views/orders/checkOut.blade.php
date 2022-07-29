@extends('layouts.default')

@extends('layouts.header')

@extends('layouts.sideview')

@section('content')
<style>
table td{
  vertical-align:middle !important;
}
</style>

<div class="box">
              <div class="box-header with-border">
                <h3 class="box-title">CheckOut</h3>
        <div class="pull-right ">
                  <a href="#" class="refresh text-muted"><i class="fa fa-arrow-circle-left"></i></a>                  
                </div>
              <div id="successid" class="callout" align="center"></div>
              <div id="failureid" class="callout" align="center"></div>
              </div>
               
                 <div class="tile-body nopadding">                  
                    
              @if(!empty($finaldata))
              {{Form::open(array('url' => 'orders/proceedCheckOut/'.$ima_id.'/'.$id))}}
              {{ Form::hidden('_method', 'POST') }}   
              @else
              {{Form::open(array('url' => 'orders/createOrder/'.$temp_id.'/'.$ima_id.'/'.$temp_cust_id))}}
              {{ Form::hidden('_method', 'POST') }} 
              @endif  
                
                <section class="tile">
                 <!-- tile header -->
                  <div class="tile-header">
                    <input type="hidden" id="check_id" name="check_id" value="{{$id}}">
                    <input type="hidden" id="temp_cust_id" name="temp_cust_id" value="{{$temp_cust_id}}">
                    @if(!empty($finaldata)) 
                    @else
                    <h1><strong>Your Cart Is Empty</strong></h1>
                     @endif

                    
                  </div>
                  <!-- /tile header -->

                  <!-- tile body -->

                  <div class="box-body no-padding">
                   
                    <table class="table table-striped">
                      @if(!empty($finaldata)) 
                      <thead>
                        <tr>
                          <th>Product Image</th>
                          <th>Product </th>
                          <th>Price</th>
                          <th>Quantity</th>
                          <th>SubTotal</th>
                          <th>Tax %</th>
                          <th>Tax Value</th>
                          <th>Total</th>
                          <th>Action</th>
                        </tr>
                      </thead> 
                      @endif  
          <tbody>
          @if(!empty($finaldata))  
        @foreach($finaldata as $key=>$final)
        <tr>
          <td><img src="{{$final->image_url}}" width="50" /></td>
          
          <td>{{$final->name}} </br></td>
          @if(empty($cust_id))
          <td>{{$final->price}}</td>
          <td colspan="1"><input id="qty{{$final->pid}}"  name="qty{{$final->pid}}" value="{{$final->qty}}" type="number" class="rating spinner" value="0"  step="1" data-size="xl"
          data-symbol="&#xe005;" data-default-caption="{rating} hearts" data-star-captions="{}"
          onchange="dispRate({{$final->pid}},{{$final->price}},{{$final->id}},{{$final->tax}})" style="width:200px;"></td>
          <td id="subtotColVal{{$final->pid}}" class="subtotCol" >
               {{$final->price*$final->qty}}</td>
           <td>{{$final->tax}}</td>
           <td  id="taxtotColVal{{$final->pid}}" class="taxtotCol">
                 {{($final->price*$final->qty)*($final->tax/100)}}</td>
          <td><input type="hidden" id="tot{{$final->pid}}" name="tot{{$final->pid}}"   value="{{$final->price*$final->qty}}" /></td>
          @else
          <td>{{$final->agreed_price}}</td>
          <td><input id="qty{{$final->pid}}"  name="qty{{$final->pid}}" value="{{$final->qty}}"type="number" class="rating spinner" value="0"  step="1" data-size="xl"
          data-symbol="&#xe005;" data-default-caption="{rating} hearts" data-star-captions="{}"
          onchange="dispRate({{$final->pid}},{{$final->agreed_price}},{{$final->id}},{{$final->tax}})" ></td>
          <td id="subtotColVal{{$final->pid}}" class="subtotCol">
               {{$final->agreed_price*$final->qty}}</td>
           <td>{{$final->tax}}</td>
           <td  id="taxtotColVal{{$final->pid}}" class="taxtotCol">
                {{($final->agreed_price*$final->qty)*($final->tax/100)}}</td>
                <td id="incTax">{{$inc_tax}}<input type="hidden" id="tot{{$final->pid}}" name="tot{{$final->pid}}"   value="{{$final->agreed_price*$final->qty}}" /></td>
          @endif
          <!-- <input type="button" class="btn btn-default" id="add{{$final->id}}" name="add{{$final->id}}" value="Delete" onclick="getTotal({{$final->id}}, {{$ima_id}}, {{$temp_cust_id}})" /> -->
          <td><span style="padding-left:10px;" ><a onclick = "getTotal({{$final->id}}, {{$ima_id}}, {{$temp_cust_id}})"><span class="badge bg-red"><i class="fa fa-trash-o"></i></span></a></span>
          </td>
      </tr>  
      @endforeach
       
     <tr>
              <td colspan="4" align="right">Sub Total:</td><td  id="finalTot">{{$total_sum}} </td>
              <td></td>
              <td colspan="2" id="finalTax">{{$total_tax}}</td>
             </tr> 
            
            <tr>
                <td colspan="4" align="right"><b>Sum Total :</b></td>
                <td colspan="3"></td>
                <td colspan="2" id="incTax2">{{$inc_tax}}</td>
                
            </tr>
           
            </tbody>
          </table>

        </div>
        <!-- /tile body -->
      </section>
      <div style="margin: 15px 0px 15px 15px">        
      {{ Form::submit('PLACE ORDER', array('class' => 'btn btn-primary pull-right')) }}
      {{ Form::close()}}
      </div>
        @else
         <div> 
       
      {{ Form::submit('Continue Shopping', array('class' => 'btn btn-primary')) }}
      {{ Form::close()}}
      </div>   
        @endif
    </div>

                    </div>
                </div>

       
@stop 

@section('style')
    {{HTML::style('css/style.css')}}
    {{HTML::style('css/default.css')}}
    {{HTML::style('css/component.css')}}
@stop
@section('script')
  {{HTML::script('js/classie.js')}}
  {{HTML::script('js/cbpViewModeSwitch.js')}}
 
  <script type="text/javascript">
   $(document).ready(function() 
   {
      document.getElementById('successid').style.display='none';
      document.getElementById('failureid').style.display='none';
    });
  function dispRate(pid,price,id,tax)
  {
     //alert('hi');
    var rateTot= $('#qty'+pid).val() * price;
    var rateTot=rateTot.toFixed(2);    


    var qty=$('#qty'+pid).val();  
    document.getElementById('tot'+pid).innerHTML= + rateTot;
    //$('#subtotColVal'+pid).val(rateTot);
    $('#subtotColVal'+pid).html(rateTot);
    var taxTot = rateTot*(tax/100);
    var taxTot = taxTot.toFixed(2);

    $('#taxtotColVal'+pid).html(taxTot);
    var pval = 0;
    $(".subtotCol").each(function() 
    {  //alert($(this).html());
      pval += Number($(this).html());    
    });
    var tval = 0;
    $(".taxtotCol").each(function() 
    { 
      tval += Number($(this).html());    
    });
  
    var pval = pval.toFixed(2);
    var tval = tval.toFixed(2);
    
    document.getElementById('finalTot').innerHTML= + pval;
    document.getElementById('finalTax').innerHTML= + tval;
    
    var incval=parseFloat(pval) + parseFloat(tval);;
    var incval=incval.toFixed(2);
    
    document.getElementById('incTax').innerHTML= + incval;
    document.getElementById('incTax2').innerHTML= + incval;
      
    $.ajax
    (
      {
        url: "/orders/editCart",
        type: "GET", 
        data: "pid=" + pid + '&qty=' + qty + '&id=' + id ,
        success: function(result)
        {
            if(result==1)
            {
              document.getElementById('successid').style.display='block';
              document.getElementById('failureid').style.display='none';
              document.getElementById('successid').innerHTML = 'Successfully updated the cart.';
              $("#successid").addClass("callout callout-success");
              $("#successid").removeClass("callout-danger");
              $("#successid").removeClass("callout-info");
              $("#successid").removeClass("callout-warning");
            }
            else
            {
                document.getElementById('successid').style.display='none';
                document.getElementById('failureid').style.display='block';
                document.getElementById('failureid').innerHTML = 'Please delete the assigned codes before updating the cart.';
                $("#failureid").addClass("callout callout-danger");
                $("#failureid").removeClass("callout-success");
                $("#failureid").removeClass("callout-info");
                $("#failureid").removeClass("callout-warning");
            }
            location.reload(); 
        },
        error:function()
        {
          //console.log("AJAX request was a failure");
        }   
      }
    ); 
  }

 function getTotal(id,ima_id,temp_cust_id)
 {  
    var check_id=document.getElementById('check_id').value;
    $.ajax
    (
      {
        url: "/orders/deleteCart",
        type: "GET", 
        data: "id=" + id + "&ima_id=" + ima_id,
        success: function()
        {
          
          window.location = "/orders/checkOut/"+ ima_id +"/" +check_id+'/'+temp_cust_id;
        },
        error:function()
        {
        }   
      }
    );
 }
jQuery(document).ready(function($) {

  var temp_id = '<?PHP echo $temp_id;?>';
  var ima_id = '<?PHP echo $ima_id;?>';
  var temp_cust_id = '<?PHP echo $temp_cust_id;?>';
   //alert(temp_cust_id);
  if (window.history && window.history.pushState) {
    
    window.history.pushState('', null, './'+temp_id);

    $(window).on('popstate', function() {
    window.location='/orders/createOrder/'+temp_id+'/'+ima_id +'/'+temp_cust_id;

    });

  }
});

 </script>
 @stop