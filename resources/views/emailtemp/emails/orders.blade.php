<!DOCTYPE html>
<html lang="en-US">
    <head>
<meta charset="UTF-8" >
    </head>
    <body>
        <div>
        @if($vendorFlag==0)
          Hello {{ $username }},
            <br />
            <br />
            Congratulations! Your order has been successfully placed with order number {{$order_number}}.
            <br />
            <br />
        @endif
        @if($vendorFlag==1)
           Dear Vendor,
            <br />
            <br />
            Your have an order request from {{$custName}}.
            <br />
            <br />
        @endif
            You can login to eSeal at http://dev2.esealinc.com/login and continue using the service.
            <br />
            <br />
            Should you need any help or have a question, please feel free to reply to this email or reach 
            
            out to our customer support at-
            <br />
            Toll Free: 1-800-300-23305 
            <br />
            Email: support@esealinc.com
            <br />
            <br />
            Best Wishes,
            <br />
            Xyz
            <br />
            Customer Relations | eSeal Central 
            <br />
            eSeal Inc, 6150 Hellyer Avenue, San Jose, CA 95138. USA, www.esealinc.com
           
           <br />
           <br />
           <br />
              <h2>Products Details Of The Order</h2>
              <table width="70%" border="10" cellspacing="0" cellpadding="10">
                      <thead>
                        <tr>
                          <th>Product Name</th> 
                          <th>Vendor</th>
                          <th>Quantity</th>
                          <th>Subtotal</th>
                          <th>Tax</th>
                          <th>Total</th>
                        </tr>
                      </thead>
                      <tbody>
                        @foreach($eseal_order_products as $key=>$result)
                        <tr>
                          <td>{{$result->name}}</td>
                          <td  class="text-right">{{$result->delivery_to}}</td>
                          <td  class="text-right">{{$result->quantity}}</td>
                          <td  class="text-right">{{number_format($result->total,2,'.',',')}}</td>
                          <td  class="text-right">{{number_format($result->tax,2,'.',',')}}</td>
                          <td  class="text-right">{{number_format($result->total+$result->tax)}}</td>
                        </tr>
                       @endforeach
                       
                      </tbody>
                    </table>
              <br />
           <br />
        </div>
    </body>
</html>
