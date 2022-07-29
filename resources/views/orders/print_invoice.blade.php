<html dir="ltr" lang="en"><head>
<meta charset="UTF-8">
@if($print_invoice==1)
<title>Invoice</title>
@else
<title>Shipping</title>
@endif
<base href="http://dev.ebutor.com/admin/">
<link href="view/javascript/bootstrap/css/bootstrap.css" rel="stylesheet" media="all">
<script type="text/javascript" src="view/javascript/bootstrap/js/bootstrap.min.js"></script>
<link href="view/javascript/font-awesome/css/font-awesome.min.css" type="text/css" rel="stylesheet">
<link type="text/css" href="view/stylesheet/stylesheet.css" rel="stylesheet" media="all">
</head>
<body>
<div class="container">
    <div style="page-break-after: always;">
    @if($print_invoice==1)
    <h1>Invoice #{{$id}}</h1>
    @else
    <h1>Dispatch Note #{{$id}}</h1>
    @endif
    <table class="table table-bordered">
      <thead>
        <tr>
          <td colspan="2">Order Details</td>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td style="width: 50%;"><address>
            <br>
             </address>
            <b>Telephone:</b>{{$result[0]->mobile_number}}<br>
            <b>E-Mail:</b>{{$result[0]->email}}<br>
            <b>Web Site:</b> <a href="http://dev2.esealinc.com/orders/customerIma">http://dev2.esealinc.com/orders/customerIma</a></td>
          <td style="width: 50%;"><b>Date Added:</b>{{$result[0]->dateadded}}<br>
                        <b>Order ID:</b>{{$id}}<br>
            @if($print_invoice==1)
            <b>Payment Method:</b>{{$payment_method}}<br>
            @else
             <b>Shipping Method:</b>Flat Shipping Rate<br>
             @endif
             </td>
        </tr>
      </tbody>
    </table>
    <table class="table table-bordered">
      <thead>
        <tr>
          <td style="width: 50%;"><b>Billing Address</b></td>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td><address>
            {{$result[0]->payment_firstname}} {{$result[0]->payment_lastname}}<br>{{$result[0]->payment_address_1}}<br>{{$result[0]->payment_city}}<br>{{$country}}<br>{{$zone}}</address></td>
        </tr>
      </tbody>
    </table>
    <table class="table table-bordered">
     <thead>
                        <tr>
                          <th>Product Name</th>
                          <th>Quantity</th>
                          <th>Delivery To</th>
                          <th>Delivery Address</th>
                          <th>Subtotal</th>
                          <th>Tax</th>
                          <th>Total</th>
                        </tr>
                      </thead>
                      <tbody>
                        @foreach($delivery_details as $key=>$result)
                        <tr>
                          <td>{{$result->name}}</td>
                          <td  class="text-right">{{$result->quantity}}</td>
                          
                            @if($result->vendor_id)
                              <td>{{$result->firstname}} {{$result->lastname}}</td>
                              <td>{{$result->location_address}}-{{$result->location_details}}-{{$result->city}}-{{$result->state}}-{{$result->country}}</td>
                            @else
                              <td>{{$result->custfname}} {{$result->custlname}}</td>
                              <td>{{$result->location_address}}-{{$result->location_details}}-{{$result->city}}-{{$result->state}}-{{$result->country}}</td>
                          @endif
                          <td  class="text-right">{{number_format($result->total,2,'.',',')}}</td>
                          <td  class="text-right">{{number_format($result->tax,2,'.',',')}}</td>
                          <td  class="text-right">{{$result->total+$result->tax}}</td>
                        </tr>
                       @endforeach
                       <tr>
                        <td colspan="4" class="text-right">Total :</td>
                        <td colspan="5" class="text-right">{{$total_master_sum}}</td></tr>
                      </tbody>
    </table>
       
      </div>
  </div>

</body></html>