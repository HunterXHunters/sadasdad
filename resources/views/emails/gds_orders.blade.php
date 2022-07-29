<!DOCTYPE html>
<html lang="en-US">
    <head>
<meta charset="UTF-8" >
    </head>
    <body>
        <div>
           <br/>
           Hi,
           Order has been Successfully updated with Status as {{$status_value}}. 
           
           You can login to eSeal at http://dev2.esealinc.com/login and check the order details
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
                      <tbody>
                        <tr>
                        <th>Product Name</th>
                        <th>Quantity</th>
                        <th>Price</th>
                        <th>Subtotal</th>
                        <th>Tax</th>
                        <th>Total</th>
                        </tr>

                        <tr>
                        <?php $main_tot=0; ?> 
                          @foreach($eseal_order_products as $result)
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
                        </tr>

                        </tbody>
                    </table>
              <br />
           <br />
        </div>
    </body>
</html>
