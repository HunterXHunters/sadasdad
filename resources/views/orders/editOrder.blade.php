@extends('layouts.default')

@extends('layouts.header')

@extends('layouts.sideview')

@section('content')

<aside class="right-side">
    <!-- Content Header (Page header) -->
    <section class="content-header">
       <h1>
            Edit Order
            
        </h1>
        <ol class="breadcrumb">
            <li><a href=""><i class="fa fa-dashboard"></i> Home</a></li>
            <!-- <li class="active">Dashboard</li> -->
        </ol>
    </section>

    <!-- Main content -->
    <section class="content">
    <!--box-start-->
    <div class="box">
    
      <div class="box-body table-responsive">

    <div class="container" style="width:auto !important;">



{{Form::open(array('url' => ''))}}
{{ Form::hidden('_method', 'POST') }}
<fieldset>
  <legend></legend>
  <table width="100%" border="0" cellspacing="0" cellpadding="6">
    <tbody>
      <tr><th></th><th>Sno</th><th>Product Name</th><th>Price</th><th>Quantity</th><th>SubTotal</th><th>Tax</th><th></th></tr>  
      <tr><td colspan="7">&nbsp;</td></tr>      
        @foreach($finaldata as $key=>$final)
        <tr>
          <td><img src="/img/footer-logo.gif" /></td>
          <td>{{$final->order_product_id}}</td>
          <td>{{$final->name}}</td>
          <td>{{$final->price}}</td>
          <td><input id="qty{{$final->pid}}"  name="qty{{$final->pid}}" value="{{$final->quantity}}"type="number" class="rating spinner" value="0" min="0" max="" step="1" data-size="xl"
          data-symbol="&#xe005;" data-default-caption="{rating} hearts" data-star-captions="{}"
          onblur="dispRate({{$final->pid}},{{$final->price}},{{$final->order_product_id}},{{$final->tax}})" ></td>
          <td id="subtotColVal{{$final->pid}}" class="subtotCol">
               {{$final->price*$final->quantity}}</td>
           <td id="taxtotColVal{{$final->pid}}" class="taxtotCol">
                {{($final->price*$final->quantity)*($final->tax/100)}}</td>
          <td><input type="hidden" id="tot{{$final->pid}}" name="tot{{$final->pid}}"   value="{{$final->price*$final->quantity}}" /></td>
          <td><input type="button" id="add{{$final->order_product_id}}" name="add{{$final->order_product_id}}" 
                value="Delete" onclick="getTotal({{$final->order_product_id}})" /></td>
      </tr>  
      @endforeach  
      <tr>
				<td colspan="5" align="right">Total </td><td id="finalTot">{{$total_sum}}</td>
        <td align="right">Tax</td><td id="finalTax">{{$total_tax}}</td>
	     <tr> 
      </tbody>
    </table>
  </fieldset>

     <div> {{ Form::submit('REPLACE ORDER', array('class' => 'btn btn-primary')) }}
{{ Form::close() }}
</div>

</div>
    </div>
    <!-- /.box-end -->
    
  </section><!-- /.content -->
</aside><!-- /.right-side -->

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

  function dispRate(pid,price,id,tax)
  {
     //alert('hi');
    var rateTot= $('#qty'+pid).val() * price;
    rateTot = Number(rateTot);
    var qty=$('#qty'+pid).val();  
    document.getElementById('tot'+pid).innerHTML= + rateTot;
    //$('#subtotColVal'+pid).val(rateTot);
    $('#subtotColVal'+pid).html(rateTot);
    var taxTot = Number(rateTot*(tax/100)); 
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
  
    document.getElementById('finalTot').innerHTML= + pval;
    document.getElementById('finalTax').innerHTML= + tval;
    
    $.ajax
    (
      {
        url: "/orders/updateOrder",
        type: "GET", 
        data: "pid=" + pid + '&qty=' + qty + '&id=' + id ,
        success: function()
        {
        },
        error:function()
        {
          //console.log("AJAX request was a failure");
        }   
      }
    ); 
  }

 function getTotal(id)
 {  
    $.ajax
    (
      {
        url: "/orders/deleteCart",
        type: "GET", 
        data: "id=" + id ,
        success: function()
        {
          window.location = "/orders/checkOut";
        },
        error:function()
        {
        }   
      }
    );
 }
 </script>
 @stop