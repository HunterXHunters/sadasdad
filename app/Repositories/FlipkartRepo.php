<?php  namespace App\Repositories; 


use Token;
use User;
use DB;  //Include laravel db class
use Session;


Class FlipkartRepo{

    public function sendRequest($url,$listing_id='',$json='',$api_name,$SKUID,$channel_order_item_id){
    
           $headers=$this->getHeaders();
           //return $url.$api_name.$SKUID;
           //print_r($json);exit;
           $ch = curl_init();

                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

                curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE); //Set curl to return the data instead of printing it to the browser.

               curl_setopt($ch, CURLOPT_TIMEOUT,1200);

               curl_setopt($ch, CURLOPT_CONNECTTIMEOUT,600); # timeout after 100 seconds, you can increase it

                if ($api_name!='GetProduct'){
                curl_setopt($ch, CURLOPT_POSTFIELDS,
                         $json);
              }
            
              /*  curl_setopt($ch, CURLOPT_URL, $url);

              // if($api_name=='UpdateItem' || $api_name=='UpdateInventory' ){
              //print_r($result);exit;
              // }
                if(1){

                }*/

                curl_setopt($ch, CURLOPT_URL, $url);
             
                $result = curl_exec($ch);
                curl_close($ch);
                $result=json_decode($result,false,512,JSON_BIGINT_AS_STRING);
           //print_r($result);exit;
    if($api_name=='UpdateItem' || $api_name=='UpdateInventory' ){
                print_r($result);
              }

    elseif($api_name=='GetProduct'){
                  $json_array=array();
                  $json_array['listing_id']=$result->listingId;
                  $json_array['SKUID']=$result->skuId;
                  $json_array['fsn']=$result->fsn;
                  $json_array['attributeValues']['national_shipping_charge'] =$result->attributeValues->national_shipping_charge;
                  $json_array['attributeValues']['listing_status']=$result->attributeValues->listing_status;
                  $json_array['attributeValues']['zonal_shipping_charge']=$result->attributeValues->zonal_shipping_charge;
                  $json_array['attributeValues']['local_shipping_charge']=$result->attributeValues->local_shipping_charge;


  DB::table('Channel_product_add_update as cpau')
  ->leftJoin('products as prod','prod.product_id','=','cpau.product_id')
                    ->where('sku',$SKUID)
                    ->update(array('cpau.is_added'=> 0 ,
                    'cpau.channel_product_key' => ($result->listingId)
                    ));


    DB::table('Channel_product')
  ->leftJoin('products as prod','prod.product_id','=','Channel_product.product_id')
                    ->where('prod.sku',$SKUID)
                    ->update(array('Channel_product.flipkart_fsn'=>($result->fsn)
                    ));
  

return "Success";
//print_r($result) ;
}

elseif($api_name == 'Readytodispatch')
{

 // foreach ($result->orderItems as $orderItems => $value) {
  return $result;

     DB:: table('Channel_order_details')->update(array(
                  'order_status','=','0'
                  ));

 // }

/*  foreach ($result as  $value) {
  DB:: table('Channel_order_details')->update(array(
                  'channel_order_status','=','ReadyToDispatch'
                  ));*/

}

elseif($api_name == 'Packorder')
{
return $result;
/*  foreach ($result as  $value) {
  DB:: table('Channel_order_details')->update(array(
                  'channel_order_status','=','ReadyToDispatch'
                  ));*/

}


elseif($api_name == 'Cancelorder')
{
  //$result = '{"cancelled":["2405386390025000"],"failedCancellations":[]}';
//$result=json_decode($result);
//echo "<br> test=>";
//stdClass Object ( [cancelled] => Array ( [0] => 3404738747533100 ) [failedCancellations] => Array ( ) )
if(!empty($result->cancelled))
{
  foreach ($result->cancelled as  $value) {
    DB::table('Channel_order_details')
                  ->where('channel_order_item_id', $value)
                  ->update(array('channel_order_status'=> 'CancelledBySeller'
                   // 'channel_order_status' => 'CANCELLED'
                    )
                    );

    $channelOrderId = DB:: table('Channel_order_details')
                  ->select('order_id')
                  ->where('channel_order_item_id', $value)
                  ->get();

    DB::table('Channel_orders')
                  ->where('channel_order_id', $channelOrderId)
                  ->update(array('order_status' =>'CancelledBySeller'));
  }
 
//print_r($value->order_item_id);exit;
}
}



if($api_name=='GetOrders'){

//echo "<pre>"; print_r($result->orderItems); 
foreach ($result->orderItems as $orderItems => $value) {

//return $result->orderItems[$i]->orderItemId;
$i=0;
//if(!empty($value->orderItemId)){

 $order_exists = DB::table('Channel_orders')
             //->where('channel_order_id',$value->orderItemId)
             ->where('channel_order_id',$value->orderId)
              ->get();
//return $result->orderItems[$i]->orderItemId;
//print_r($order_exists);exit;

                $state_name=DB::table('cities_pincodes')
                            ->where('PinCode',$value->shippingPincode)
                            ->pluck('State');
               // return $state_name;

                $state_name= ucwords(strtolower($state_name));
              
               //print_r($order_exists);

                if(!empty($order_exists)){

                  //print_r('exists');exit;

                     //return $order_exists;
                    //return $result->orderItems[$i]->orderItemId;
                  $channel_id=DB::table('Channel')
                              ->where('channnel_name','Flipkart')
                              ->pluck('channel_id');

                      DB::table('Channel_orders')
                     // ->where('channel_order_item_id',$value->orderItemId)
                    ->where('channel_order_id',$value->orderId)
                    ->update(array('channel_id'=> $channel_id ,
                   // 'channel_order_item_id'=>$value->orderItemId,
                    'channel_order_id'=>$value->orderId,
                    'order_status'=>$value->status,
                    'shipping_cost'=>$value->shippingFee,
                    'total_amount'=> $value->priceComponents->totalPrice,
                    'order_date'=>$value->orderDate,
                    'channel_order_status'=>$value->status,
                    'currency_code'=>'INR'
                    ));

                       DB::table('Channel_orders_shipping_address')
                      ->where('order_id', $value->orderId)
                      ->update(array('order_id' => $value->orderId,
                        'pincode' => $value->shippingPincode,
                        'channel_id' =>$channel_id));
                      
                    $orderItem =  DB::table('Channel_order_details')
                      ->select('channel_order_item_id')
                      ->where('channel_item_id',$value->listingId)->get();
                    //echo "<br>test=><br>"; print_r($orderItem); echo "<br>";
                    if(empty($orderItem)) {
                      DB::table('Channel_order_details')->insert([
                         'channel_order_item_id' =>$value->orderItemId,
                         'order_id' =>$value->orderId,
                         'channel_id' =>$channel_id,
                         'channel_item_id'=>$value->listingId,
                         'quantity'=>$value->quantity,
                         'price'=>$value->priceComponents->sellingPrice,
                         'order_status' => '1',
                         'channel_order_status'=>$value->status
                      ]);
                    } else {
                       DB::table('Channel_order_details')
                      ->where('channel_order_item_id',$value->orderItemId)
                      ->update(array('channel_order_item_id' =>$value->orderItemId,
                         'order_id' =>$value->orderId,
                         'channel_id' =>$channel_id,
                         'channel_item_id'=>$value->listingId,
                         'quantity'=>$value->quantity,
                         'price'=>$value->priceComponents->sellingPrice,
                         'order_status' => '1',
                         'sco_item_id' => $value->sku,
                         'channel_order_status'=>$value->status
                    ));
                    }  

               print_r('Successfully updated');
               // return $result->orderItems[$i]->priceComponents->sellingPrice;
}
    
//return "Successfully Updated the records";

//echo $result->orderItems[$i]->orderId; exit;

                //print_r($product_availability);exit;                           
          /*  $product_availability=DB::table('Channel_product_add_update as Cpau')
                                    ->leftJoin('product_inventory as pi','pi.product_id','=','Cpau.product_id')
                                    ->leftJoin('products as pd','pd.product_id','=','Cpau.product_id')
                                    ->where('channel_product_key',$value->listingId)
                                    ->get();

            if(!empty($product_availability)){*/
                 // if(1){
                   //$i=0;
                  
                   //$form_url="http://".$baseurl."/dmapi/checkInventoryAvailability";

                  // if($catResult->Message!="Out of Stock for the following products."){
               /* $baseurl= $_SERVER['SERVER_NAME'];
               */

/*$single = DB::table('Channel_orders')
->leftJoin('Channel_orders_shipping_address', 'Channel_orders.channel_order_id','=','Channel_orders_shipping_address.order_id')
->where('channel_order_id',$value->orderId)
->get();
*/
//print_r($single); exit;

/*if(empty($single))
{*/


else
{
         
          $channel_id = DB::table('Channel')
                              //->select('channel_id')
                              ->where('channnel_name','Flipkart')
                              ->pluck('channel_id');
                  DB::table('Channel_orders')->insert([
                        'channel_id' => $channel_id,
                        //'channel_order_item_id'=>$value->orderItemId,
                        'channel_order_id'=>$value->orderId,
                        'order_status'=>$value->status,
                        'shipping_cost'=>$value->shippingFee,
                        'total_amount'=> $value->priceComponents->totalPrice,
                        'order_date'=>$value->orderDate,
                        'channel_order_status'=>$value->status,
                        'currency_code'=>'INR'
                        ]);

                   DB::table('Channel_order_details')->insert([
                         'channel_order_item_id' =>$value->orderItemId,
                         'order_id' =>$value->orderId,
                         'channel_id' =>$channel_id,
                         'channel_item_id'=>$value->listingId,
                         'quantity'=>$value->quantity,
                         'price'=>$value->priceComponents->sellingPrice,
                         'order_status' => '1',
                         'sco_item_id' => $value->sku,
                         'channel_order_status'=>$value->status
                      ]);
                  
                  DB::table('Channel_orders_shipping_address')->insert([
                      //->where('order_id', $value->orderId)
                      'order_id' => $value->orderId,
                      'pincode' => $value->shippingPincode,
                      'channel_id' =>$channel_id
                      ]);

              //print_r('successful');exit;
              $message = "Successfully Inserted the records";
             
               print_r($message);            
              
  } 
  }

 // if(!empty($product_availability)){
  /*$order_stat= DB::table('Channel_order_details')
                  ->where('order_id', $result->order_id)              
                  ->get(); */
 // $arr[] = $order_stat; 
                 
                  //print_r(count($order_stat));exit;

/*foreach($order_stat as $val)
{*/



$order_status_check = DB::table('Channel_order_details')
                  //->select('order_id','channel_order_item_id')
                  ->where('order_status','1')
                  //->where('channel_order_item_id',$value->orderItemId)               
                  ->get();
//echo "<pre>"; 

                  // $check_inventory_arr = array();
                  $products = array();
                  $final_array = array();
                  $j=0;

foreach ($order_status_check as $result) {
  //print_r($order_status_check[3]->channel_item_id);exit;

  if($result->channel_order_status=='APPROVED')
  {

            $product_availability=DB::table('Channel_product_add_update as Cpau')
                                    ->leftJoin('product_inventory as pi','pi.product_id','=','Cpau.product_id')
                                    ->leftJoin('products as pd','pd.product_id','=','Cpau.product_id')
                                   ->where('channel_product_key',$result->channel_item_id)
                                    //->where('channel_product_key','LSTDIADYQFREPDKVJ8NRZSOOB')
                                    ->get();

                                    //print_r($product_availability);exit;                                   
          
            if(!empty($product_availability)){

        //return $value->quantity;
              
              $channel_id=DB::table('Channel')
                              //->select('channel_id')
                              ->where('channnel_name','Flipkart')
                              ->pluck('channel_id');

                  $products['product_id'] = $product_availability[$j]->product_id;
                  $products['name']       = $product_availability[$j]->name;
                  $products['model']      = '';
                  $products['option']     = '';
                  $products['download']   = '';
                  $products['sku']        = $product_availability[$j]->sku;
                  $products['quantity']   = $result->quantity;//$value->quantity;
                  $products['subtract']   = '';
                  $products['price']      = $product_availability[$j]->mrp;
                  $products['total']      = $product_availability[$j]->mrp*$result->quantity;
                  $products['tax']        = '';
                  $products['reward']     = '';
                  //);

              /*    $check_inventory_arr['sku'] = $product_availability[$j]->sku;
                  $check_inventory_arr['quantity'] = $result->quantity;
                  $check_inventory_arr['price'] = $product_availability[$j]->mrp;
                  $check_inventory_arr['total'] = $product_availability[$j]->mrp*$result->quantity;

                  $final_check_array[] = $check_inventory_arr;*/

                  $final_array[] = $products;
                   //print_r($final_array);exit;

                 // print_r($final_check_array);exit;
                   // $j++;

               // print_r($final_array['total']);exit;

               
//print_r($final_check_array);exit;
         //       }
//echo "<br>".$result->channel_order_id; die;
                $order_values = DB::table('Channel_orders as chord')
                  ->leftJoin('Channel_order_details as chnordet', 'chnordet.order_id','=','chord.channel_order_id')
                  ->leftJoin('Channel_orders_shipping_address as chnship', 'chnship.order_id','=','chord.channel_order_id')
                  ->where('chord.channel_order_id',$result->order_id)
                  ->where('chnordet.order_status','=','1')
                  ->where('chnordet.channel_id','=',$channel_id)
                  //->where('channel_order_item_id',$value->orderItemId)               
                  ->get();

                 // print_r($order_values);exit;

/*$queries = DB::getQueryLog();
       $last_query = end($queries);

                print_r($last_query); die;*/
/*
                $order_values = DB::table('Channel_order_details as chnordet')
                  ->leftJoin('Channel_orders as chords', 'chnordet.channel_id','=','chord.channel_id')
                  ->leftJoin('Channel_orders_shipping_address as chnship', 'chnship.order_id','=','chnordet.order_id')

                  ->where('chnordet.order_status','=','1')
                  ->where('chord.channel_id','=','2')
                  //->where('channel_order_item_id',$value->orderItemId)               
                  ->get();*/
                  //print_r($order_values); exit;

               /* $order_det = DB:: table('Channel_orders')
                ->where('channel_id','2')
                ->get();*/

           //  echo "<pre>"; print_r($order_values); echo "<br>";

               
        //-----------------------------------------------------------------------------    

           foreach($order_values as $order)
           {

           // print_r($order_values);exit;

        //------------------------------------------------------------------------------
            $url=$this->getDMAccess();
                  $baseurl=$url['api_key']->channel_url;
                  $form_url=$baseurl."checkInventoryAvailability";
                  $data_to_post['api_key'] = $url['api_key']->Key_value;
                  $data_to_post['secret_key'] = $url['secret_key']->Key_value;

                     /*$baseurl = 'dev2.esealinc.com';
                     $form_url = "http://".$baseurl."/dmapi/checkInventoryAvailability";
                     $data_to_post['api_key'] = 'orient_developer_1';
                     $data_to_post['secret_key'] = '8gju!eDX?bc9_n#%';*/

                     //print_r($product_availability[$j]->sku);exit;
                     $productData = json_encode(array('sku'=>$product_availability[$j]->sku,
                      'quantity'=>$result->quantity,
                      'price'=>$product_availability[$j]->mrp,
                      'total'=>($product_availability[$j]->mrp*$result->quantity)));
                       /* $productData = json_encode(array('sku'=>$check_inventory_arr['sku'],'quantity'=>$check_inventory_arr['quantity'],
                      'price'=>$check_inventory_arr['price'],'total'=>$check_inventory_arr['total']));*/
                     
                     $data_to_post['is_blocked'] =1;
                     $data_to_post['product_data'] = $productData;
                    // $data_to_post['pincode']=$order->pincode   //$pincode[0]->pincode;
                   //500022/500001/110001
                     $data_to_post['pincode']=110001;
                    // $data_to_post['ppid']=111;

                     //$data_to_post['shipping_pincode']=$order->pincode;
                     //exit;

                      $curl = curl_init();

                      curl_setopt($curl,CURLOPT_URL, $form_url);

                      curl_setopt($curl,CURLOPT_RETURNTRANSFER, TRUE);

                      curl_setopt($curl,CURLOPT_POST, sizeof($data_to_post));
                      
                      curl_setopt($curl,CURLOPT_POSTFIELDS,$data_to_post);
                      
                      $catResult = curl_exec($curl);
                      
                      curl_close($curl);
                    
                      $catResult = json_decode($catResult);
                      if($catResult->Message=="Stock not available"){
                        $cancel_order_id=$result->channel_order_item_id;
                      }

                     print_r($catResult);

                    $check_outstock[] = $catResult->Message;
                    
                   $outofstock=strpos(json_encode($check_outstock),'Stock not available'); 

                     // print_r($outofstock);exit;     
                
                if(empty($outofstock)){

               // if($catResult->Message!="Out of Stock for the following products."){


    //----------------------------------------------------------------------------------------------              

/*foreach($order_values as $order)
           {*/

            $url=$this->getDMAccess();
                  $baseurl=$url['api_key']->channel_url;
                  $form_url=$baseurl."placeOrder";
                  $data['api_key'] = $url['api_key']->Key_value;
                  $data['secret_key'] = $url['secret_key']->Key_value;


            /*$baseurl= 'dev2.esealinc.com';
              $form_url="http://".$baseurl."/dmapi/placeOrder";
                 
                   $data['api_key'] = 'orient_developer_1';
                   $data['secret_key'] = '8gju!eDX?bc9_n#%';*/
                  // $order_pincode = 500091;
                   $state_name=DB::table('cities_pincodes')
                            ->where('PinCode',$order->pincode)
                            ->pluck('State');

                   $i=0;
                   $state_name= ucwords(strtolower($state_name));

                   $query = DB::table('zone')
                        ->where('name',$state_name)->get();
                     //print_r($query[$i]->country_id); exit;      
                  //  $customer_info = array();
                   $customer_info['suffix']= '';
                   $customer_info['first_name']='';
                   $customer_info['middle_name']='';
                   $customer_info['last_name']='';
                   $customer_info['channel_user_id']='';
                   $customer_info['email_address']='';
                   $customer_info['mobile_no']='';
                   $customer_info['dob']='';
                   $customer_info['channel_id']=$order->channel_id;
                   $customer_info['gender']= '';          
                   $customer_info['registered_date']= '';


                   $address_info[0]['address_type']='Shipping';          
                   $address_info[0]['first_name']= '';           
                   $address_info[0]['middle_name']= '';         
                   $address_info[0]['last_name']= '';           
                   $address_info[0]['email']= '';               
                   $address_info[0]['address1']=  '';    
                   $address_info[0]['address2']= '';   
                   $address_info[0]['city']='';   
                   $address_info[0]['state']=$state_name; 
                   $address_info[0]['phone']=  ''; 
                   $address_info[0]['pincode']= $order->pincode;
                   $address_info[0]['country']= $query[$i]->country_id;
                   $address_info[0]['company']= '';                
                   $address_info[0]['mobile_no']='';   

                   $address_info[1]['address_type']='Billing';          
                   $address_info[1]['first_name']= '';           
                   $address_info[1]['middle_name']= '';         
                   $address_info[1]['last_name']= '';           
                   $address_info[1]['email']= '';               
                   $address_info[1]['address1']=  '';    
                   $address_info[1]['address2']= '';   
                   $address_info[1]['city']='';   
                   $address_info[1]['state']=$state_name; 
                   $address_info[1]['phone']=  ''; 
                   $address_info[1]['pincode']= $order->pincode;
                   $address_info[1]['country']= $query[$i]->country_id;
                   $address_info[1]['company']= '';                
                   $address_info[1]['mobile_no']='';

                   $order_info['channelid']= $order->channel_id;
                   $order_info['channelorderid']= $order->channel_order_id;
                   $order_info['orderstatus']=$order->channel_order_status;
                   $order_info['orderdate']= $order->order_date;
                   $order_info['paymentmethod']='';
                   $order_info['shippingcost']=$order->shipping_cost;
                   $order_info['subtotal']=  $order->price;
                   $order_info['tax']= '';                                   
                   $order_info['totalamount']= $order->price*$order->quantity;
                   $order_info['currencycode']=$order->currency_code;
                   $order_info['channelorderstatus']= $order->channel_order_status;
                   $order_info['updateddate']= '04-12-2015';                           
                   $order_info['gdsorderid']=  '';
                   $order_info['channelcustid']='4';
                   $order_info['createddate']='2015-09-2914: 43: 57';
 
                   $product_info['sku']='';
                   $product_info['channelId']=$order->channel_id;
                   $product_info['order_id']= $order->order_id;
                   $product_info['channelitemid']='';
                   $product_info['scoitemid']='';
                   $product_info['quantity']='';
                   $product_info['price']=$order->price;
                   $product_info['sellprice']='';
                   $product_info['discounttype']='';
                   $product_info['discountprice']='';
                   $product_info['tax']='null';
                   $product_info['subtotal']='';
                   $product_info['channelcancelitem']='';
                   $product_info['total']='';
                   $product_info['shippingcompanyname']='';
                   $product_info['servicename']='';
                   $product_info['servicecost']='';
                   $product_info['dispatchdate']='';
                   $product_info['mintimetodispatch']='2';
                   $product_info['maxtimetodispatch']='7';
                   $product_info['timeunits']='5';

                   $payment_info['order_id']=  $order->order_id;
                   $payment_info['channelid']=  $order->channel_id;
                   $payment_info['paymentmethod']=  '';                
                   $payment_info['paymentstatus']=  '';          
                   $payment_info['paymentcurrency']= '';           
                   $payment_info['amount']=$order->total_amount;
                   $payment_info['buyeremail']= '';              
                   $payment_info['buyername']= '';                   
                   $payment_info['buyerphone']= '';                 
                   $payment_info['transactionId']= '';                 
                   $payment_info['paymentDate']='2015-09-2914: 43: 57';
                   


                  $order_data['customer_info'] = $customer_info;
                  $order_data['address_info'] = $address_info;
                  $order_data['order_info'] = $order_info;
                  $order_data['product_info'] = $final_array;
                  $order_data['payment_info'] = $payment_info;
                  
                  //$order_data['products']= $final_array ;

                   $order_data_req = json_encode($order_data);
                   $data['orderdata'] = $order_data_req;
                   $data['gds_order_id']=$order->order_id;

                   //print_r($data);exit;

                  $curl = curl_init();

                    curl_setopt($curl,CURLOPT_URL, $form_url);

                    curl_setopt($curl,CURLOPT_RETURNTRANSFER, TRUE);

                    curl_setopt($curl,CURLOPT_POST, sizeof($data));

                    curl_setopt($curl,CURLOPT_POSTFIELDS, $data);

                    $catResult = curl_exec($curl);

                    curl_close($curl);

                   
                    echo 'success';

                    $catResult=json_decode($catResult);   
                    print_r($catResult); 
                    //print_r($catResult->order_id);exit;
                   // $erp_order_id=$catResult->OrderId;

                   /* $order = 'Order Id';
                    $ord_id=$place->$order;*/
                   //$order_id=
                   // print_r('Success');exit;
/*
                    $orders = 'Order Id';
                    $erp_order_id=$catResult->$orders;*/

                  //  $erp_order_id=substr($catResult->Message,31);

                    DB::table('Channel_orders')
                        ->where('channel_order_id',$order->order_id)
                        ->update(array('erp_order_id' => $catResult->order_id,
                          ));

                    //echo "<br>";
              
        
}

else
{

  //$messag[] = "Stock is not available for product with Item Id:".$order->//$order_ItemID;

                //$baseurl= "central.local";
      //  $baseurl= 'dev2.esealinc.com';
               // $productData = json_encode($final_dispute_array);
        $url=$this->getDMA();

        //print_r($url);exit;
                  $baseurl=substr($url,0,25);
                  $form_url=$baseurl."Flipkartdeveloper/Cancelorder/".$cancel_order_id;
                  
//print_r($form_url);exit;
        //$form_url="http://".$baseurl."/Flipkartdeveloper/Cancelorder/". $cancel_order_id;

                  $curl = curl_init();

                    curl_setopt($curl,CURLOPT_URL, $form_url);

                    curl_setopt($curl,CURLOPT_RETURNTRANSFER, TRUE);

                    //curl_setopt($curl,CURLOPT_POST, sizeof($data));

                    //curl_setopt($curl,CURLOPT_POSTFIELDS, $data);

                    $catResult = curl_exec($curl);

                    curl_close($curl);
                    print_r($catResult); 


                
}
}
}
}

}

//----cancel order--


         // ->leftJoin('Channel_order_details as chnordet', 'chord.channel_id','=','chnordet.channel_id')
       // ->leftJoin('Channel_order_details as chnordet', 'chord.channel_order_id','=','chnordet.order_id')
        //->select('chord.order_status','chnordet.channel_item_id','chord.channel_order_item_id','chord.channel_order_status')
         //->select('chord.order_status','chord.channel_order_item_id','chord.channel_order_status')
        //->where('channel_order_item_id',$value->orderItemId)
        //->where('chord.order_status','=','CANCELLED')
$channel_id=DB::table('Channel')
                              //->select('channel_id')
                              ->where('channnel_name','Flipkart')
                              ->pluck('channel_id');

$status=DB::table('Channel_order_details as chord')
            ->where('chord.channel_id',$channel_id)
            ->get();


foreach($status as $cancelresult){

//print_r($cancelresult->channel_order_status);exit;

if($cancelresult->channel_order_status=='CANCELLED')

{

   $i=0;

                   //print_r($cancelresult->order_id);
                   $cancel_order_id = DB::table('Channel_orders')
                   ->where('channel_order_id',$cancelresult->order_id)
                   ->pluck('erp_order_id');

                   $url=$this->getDMAccess();
                  $baseurl=$url['api_key']->channel_url;
                  $form_url=$baseurl."cancelOrder";
                  $data['api_key'] = $url['api_key']->Key_value;
                  $data['secret_key'] = $url['secret_key']->Key_value;


                   /*$baseurl= 'dev2.esealinc.com';
                   $form_url="http://".$baseurl."/dmapi/cancelOrder";
                   $data['api_key'] = 'orient_developer_1';
                   $data['secret_key'] = '8gju!eDX?bc9_n#%';*/
                   $data['order_id'] = $cancel_order_id ;
                  
                   $curl = curl_init();

                    curl_setopt($curl,CURLOPT_URL, $form_url);

                    curl_setopt($curl,CURLOPT_RETURNTRANSFER, TRUE);

                    curl_setopt($curl,CURLOPT_POST, sizeof($data));

                    curl_setopt($curl,CURLOPT_POSTFIELDS, $data);

                    $catResult = curl_exec($curl);

                    curl_close($curl);
                    $catResult=json_decode($catResult);
                   
                   print_r($catResult); 

              if($catResult->Message=="Successfully cancelled the order")
                    {
  
                     // ->update(array('order_status'=>'CanceledByBuyer'));
                     /*if($cancelresult->channel_order_status == 'CANCELLED' && $cancelresult->channel_order_status != 'CancelledBySeller'
                     // && $order_status[0]->order_status != 'CancelledBySeller'
                      ){*/

                      DB::table('Channel_order_details')
                      ->where('channel_order_item_id',$cancelresult->channel_order_item_id)
                      ->update(array('channel_order_status' => 'CancelledByBuyer'));
                    
                      DB::table('Channel_orders')
                      ->where('channel_order_id',$cancelresult->order_id)
                      ->update(array('order_status' => 'CancelledByBuyer'));

                      }
                
                    //print_r('Sucessfully Updated');
                   }

      }

//----cancel order--
}  
}  

/*public function getHeaders() {   $headers=array('content-type:application/json',
          'Authorization:Bearer45f8ac17-d567-422c-aae4-5bcd452589af',);
     return $headers;
    }
      */
   public function getHeaders(){  

     $Flipkart_channel_id = DB::table('Channel')->where('channnel_name','Flipkart')->pluck('channel_id');
              
              $Content_type = DB::table('Channel_configuration')
                           ->where(array('Key_name'=>'Content-type','channel_id'=>$Flipkart_channel_id))
                           ->pluck('Key_value');

              $Authorization = DB::table('Channel_configuration')
                           ->where(array('Key_name'=>'Authorization','channel_id'=>$Flipkart_channel_id))
                           ->pluck('Key_value');
          
   $headers=array(
              'Content-type:'.$Content_type,
              'Authorization:'.$Authorization);
     return $headers;
    }


    /* $headers=array('content-type:application/json',
          'Authorization:Bearer45f8ac17-d567-422c-aae4-5bcd452589af',);
     return $headers;*/
   // }
   public function getUpdateItem()
    {
              $result= DB::table('products as prod')
                 ->leftJoin('Channel_product_add_update as cpau','prod.product_id','=','cpau.product_id')
                 ->leftJoin('Channel','Channel.channel_id','=', 'cpau.channel_id')
                ->select('prod.mrp as mrp','prod.mrp as sellingprice','cpau.channel_product_key as listing_id', 'Channel.channel_url as url')
                 ->where('is_update','=','1')
                ->get();

                return  $result;


    }
    public function getitem()
    {
      $channel_id=DB::table('Channel')
                              //->select('channel_id')
                              ->where('channnel_name','Flipkart')
                              ->pluck('channel_id');
                              //->get();
                   // print_r($channel_id);exit;

              $result= DB::table('Channel_product_add_update as cpau')
                 ->leftJoin('products as prod','prod.product_id','=','cpau.product_id')
                 ->leftJoin('Channel','Channel.channel_id','=', 'cpau.channel_id')
                ->select('prod.sku as SKUID','Channel.channel_url as url')
                 ->where('cpau.is_added','=','1')
                 ->where('cpau.channel_id',$channel_id)
                ->get();

                return $result;


    }

  
    public function get_updated_qty()
    {

              $result= DB::table('product_inventory as prodinv')
                 ->leftJoin('Channel_product_add_update as cpau','prodinv.product_id','=','cpau.product_id')
                ->leftJoin('Channel as chn','chn.channel_id','=', 'cpau.channel_id')
                ->select('prodinv.available_inventory as stock_count','prodinv.product_id as product_id','cpau.channel_product_key as listing_id','chn.channel_url as url')
                 ->where('cpau.is_update','=','1')
                 ->where('channnel_name','Flipkart')
                ->get();
                return $result;

 }

 public function getorders()
    {

              $result= DB::table('Channel')
             ->select('channel_url as url')
             ->where('channnel_name','Flipkart')
              ->get();
                return $result;

 }

 public function view_order_details()
    {
      $channel_id=DB::table('Channel')
                              ->select('channel_id')
                              ->where('channel_name','Flipkart')
                              ->get();


              $result= DB::table('Channel_orders as chord')
              ->leftJoin('Channel as chn','chn.channel_id','=','chord.channel_id')
              ->select('chn.channel_url as url','chord.channel_order_item_id')
              ->where('chn.channel_id',$channel_id)
              ->get();
              return $result;


    }
  
 public function cancel_order()
      {

            $result = DB:: table('Channel')
            ->select('channel_url as url' )
            ->where('channnel_name','Flipkart')
            ->get();
              return $result;

      }

  public function getDMAccess(){

            $channel_id=DB::table('Channel')->select('channel_id')->where('channnel_name','dmapi')->first();
   
            $api_key=DB::table('Channel_configuration as cf')
                ->leftjoin('Channel as c','c.channel_id','=','cf.channel_id')
                ->where(array('cf.channel_id'=>$channel_id->channel_id,'Key_name'=>'api_key'))
                ->first();

            $secret_key=DB::table('Channel_configuration as cf')
                ->leftjoin('Channel as c','c.channel_id','=','cf.channel_id')
                ->where(array('cf.channel_id'=>$channel_id->channel_id,'Key_name'=>'secret_key'))
                ->first();
                          
             $url=array('api_key'=>$api_key,'secret_key'=>$secret_key);
                          
                return $url;
                 } 
public function getDMA(){
  $channel_url=DB::table('Channel')
  //->select('channel_url')
  ->where('channnel_name','dmapi')
  ->pluck('channel_url');
  return $channel_url;
}


  public function ready_to_dispatch()
      {

        $result = DB:: table('Channel_order_details as chndetails')
             ->leftJoin('Channel as chn', 'chn.channel_id','=','chndetails.channel_id')
             ->select('chndetails.channel_order_item_id','chn.channel_url','chndetails.quantity as quantity')
             ->where('order_status','1')
             ->where('channel_order_status','PACKED')
             ->where('channnel_name','Flipkart')
             ->get();
             return $result;

      }

      public function Packorder()
      {    
          $result = DB:: table('Channel_order_details as chndetails')
             ->leftJoin('Channel as chn', 'chn.channel_id','=','chndetails.channel_id')
             ->select('chndetails.channel_order_item_id','chn.channel_url','chndetails.tax as tax')
             ->where('order_status','1')
             ->where('channel_order_status','APPROVED')
             ->where('channnel_name','Flipkart')
             ->get();
             return $result;
       }     
 
  public function updated_qty()
    {

              $result= DB::table('product_inventory as prodinv')
                 ->leftJoin('Channel_product_add_update as cpau','prodinv.product_id','=','cpau.product_id')
                ->leftJoin('Channel as chn','chn.channel_id','=', 'cpau.channel_id')
                ->select('prodinv.available_inventory as stock_count','prodinv.product_id as product_id','cpau.channel_product_key as listing_id','chn.channel_url as url')
                 ->where('channnel_name','Flipkart')
                ->get();
               return $result;

 }

}
    