    <html dir="ltr" lang="en"><head>
    <meta charset="UTF-8">
    @if($print_invoice==1)
    <title>Invoice</title>
    @else
    <title>Dispatch Note</title>
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

    @foreach($order_details as $details)
    @if($print_invoice==1)
    <h1>Invoice #{{$details->order_id}}</h1>
    @else
    <h1>Dispatch Note #{{$details->order_id}}</h1>
    @endif
    
    <table class="table table-bordered">
      <thead>
        <tr>
          <td colspan="2">Order Details</td>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td style="width: 50%;"><b>Customer Name:</b>{{$details->name}}<br>
            <b>Telephone:</b>{{$details->phone}}<br>
            @if(!empty($details->email))
            <b>E-Mail:</b>{{$details->email}}<br>
            @else
            <b>E-Mail:</b>NA<br>
            @endif
           </td> 
          <td style="width: 50%;"><b>Date Added:</b>{{$details->order_date}}<br>
           <b>Order ID:</b>{{$details->order_id}}<br>
            @if($print_invoice==1)
            <b>Payment Method:</b>{{$details->payment_method}}<br>
            @else
            <b>Shipping Method:</b>{{$details->service_name}}<br>
            @endif
            </td>
        </tr>
      </tbody>
    </table>
    <table class="table table-bordered">
      <thead>
        <tr>
          <td style="width: 50%;"><b>Shipping Address</b></td>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>
            <address>
            {{$details->name}}<br>
            {{$details->address1}}<br>
            {{$details->address2}}<br>
            {{$details->city}}<br>
            {{$details->state}}<br>
            {{$details->country}}<br>
            {{$details->pincode}}<br>
           </address>
        </td>
        </tr>
      </tbody>
      @endforeach
    </table>

     <table class="table table-bordered">
     <thead>
                        <tr>
                          <th>Product Name</th>
                          <th>Quantity</th>
                          <th>Price</th>
                          <th>Subtotal</th>
                          <th>Tax</th>
                          <th>Total</th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr>
                         <?php $main_tot=0; ?> 
                        @foreach($final_product_array as $result)
                        <tr>
                        <td>{{$result['product_name'] }}</td>
                        <td  class="text-right">{{$result['quantity']}}</td>
                        <td  class="text-right">{{$result['price']}}</td>
                        <td  class="text-right">{{number_format($result['subtotal'],2,'.',',')}}</td>
                        <td  class="text-right">{{number_format($result['tax'],2,'.',',')}}</td>
                        <td  class="text-right">{{$result['subtotal']+$result['tax']}}</td>
                        <?php
                        $sub_tot = $result['subtotal']+$result['tax']; 
                        $main_tot=$main_tot+$sub_tot; ?> 
                        </tr>
                        @endforeach
                        <tr>
                        <td colspan="5" class="text-right">Total :</td>
                        <td colspan="6" class="text-right"><?php echo $main_tot; ?></td></tr>
                      </tbody>
    </table>
      
    
       
      </div>

  </div>

</body></html>