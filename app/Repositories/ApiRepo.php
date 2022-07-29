<?php  namespace App\Repositories; 


use Token;
use User;
use DB;  //Include laravel db class
use Session;


Class ApiRepo{

    public function getXml($json_data,$apiname,$auth_token,$input_type,$product_attributes=''){
       
        
        
        $product_attributes=json_decode($product_attributes);
        $dispute_size = sizeof($json_data);

        if($input_type=='json' || $input_type=='xml'){
        if($input_type=='json')
        {
        $apirequest=$apiname.'Request';
        $xml_input='<?xml version=\'1.0\' encoding=\'utf-8\'?><'.$apirequest.' xmlns="urn:ebay:apis:eBLBaseComponents"></'.$apirequest.'>';
        $xml_user_info = new \SimpleXMLElement($xml_input);
        $test=$this->array_to_xml($json_data,$xml_user_info);
        $json_data = $xml_user_info->asXML();
        }
        
        
        $doc = new \DOMDocument();
        $doc->loadXML($json_data);
        $fragment = $doc->createDocumentFragment();
        $fragment->appendXML("<RequesterCredentials>
            <eBayAuthToken>".$auth_token."</eBayAuthToken>
        </RequesterCredentials>
          ");
        $doc->documentElement->appendChild($fragment);
        $str = $doc->saveXML($doc->documentElement);
        $test_input='<?xml version=\'1.0\' encoding=\'utf-8\'?>';
        
        $dom = new \DOMDocument();
        
        $dom->loadXML($str);
        
        if($apiname=='AddFixedPriceItem' || $apiname=='ReviseItem'){
        
        $specifications = $dom->getElementsByTagName('ItemSpecifics');
        
       foreach($specifications as $a) {
        
        $fragment = $dom->createDocumentFragment();
        
        foreach ($product_attributes as $attributes) {
        
        $explode=explode('$',$attributes);
       
        $fragment->appendXML("<NameValueList><Name>".htmlspecialchars($explode[1])."</Name><Value>".$explode[0]."</Value></NameValueList>");
       
        }
       
        $a->appendChild($fragment);
        }
        
        }
       
        $str = $dom->saveXML($dom->documentElement);
        
        $final_xml=$test_input.$str;
        
        if($apiname=="AddDispute"){
        
        for($y=0;$y<=$dispute_size;$y++)
            {
             
            $item = '<item'.$y.'>';
            $itemend= '</item'.$y.'>';
            $final_xml = str_replace($item,' ',$final_xml);
            $final_xml = str_replace($itemend,' ',$final_xml);
            
            }
          
        }
         return $final_xml;
        }
       
    }
       
       
        

    public function sendRequest($url,$xml_data,$api_name,$product_id='',$item_id='',$json_data='',$status='',$order_id='',$DisputeExplanation=''){
           
           $headers=$this->getHeaders($api_name);
           //return $headers;
            
            $ch = curl_init();

                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE); //Set curl to return the data instead of printing it to the browser.
                curl_setopt($ch, CURLOPT_TIMEOUT,1200);
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT,600); # timeout after 100 seconds, you can increase it
                curl_setopt($ch, CURLOPT_POSTFIELDS,
                        "xmlRequest=" . $xml_data);
                curl_setopt($ch, CURLOPT_URL, $url);
                
                $result = curl_exec($ch);
                curl_close($ch);
                 
               //print_r($result);exit;
                
               if($api_name=='AddFixedPriceItem'){
                
                $dom = new \DOMDocument();
                $dom->loadXML($result);
                $i=0;
                $links = $dom->getElementsByTagName('Ack')->item($i)->nodeValue;
                

              if($links=='Success' || $links=='Warning'){
                
                $eBay_channel_id = DB::table('Channel')->where('channnel_name','eBay')->pluck('channel_id');
                $channel_product_key = $dom->getElementsByTagName('ItemID')->item($i)->nodeValue;
                $listing_date = $dom->getElementsByTagName('Timestamp')->item($i)->nodeValue;
                $next_listing_date = $dom->getElementsByTagName('EndTime')->item($i)->nodeValue;
                     
                     DB::table('Channel_product_add_update')
                    ->where('product_id', $product_id)
                    ->where('channel_id', $eBay_channel_id)
                    ->update(array('is_added'=> 0 ,
                                   'listing_date' => $listing_date,
                                   'next_listing_date' => $next_listing_date,
                                   'channel_product_key' => $channel_product_key
                    ));

                  return 'Successfully Added Product with ItemID-'.$channel_product_key;
                    

                } 
              else{
                  print_r($result);
                }
                
                }
               
                 if($api_name=='ReviseCheckoutStatus')
                {

                  $eBay_channel_id = DB::table('Channel')->where('channnel_name','eBay')->pluck('channel_id');
                
                  DB::table('Channel_order_details')
                    ->where('order_id', $order_id)
                    ->where('channel_id', $eBay_channel_id)
                    ->update(array('order_status'=> 0
                    ));

                return 'Successfully Updated Order with OrderID-'.$order_id;
                }

                if($api_name=='RelistFixedPriceItem')
                {
               
                $dom = new \DOMDocument();
                $dom->loadXML($result);
                $i=0;
                $links = $dom->getElementsByTagName('Ack')->item($i)->nodeValue;

                if($links=='Success'){
                
                $ItemID = $dom->getElementsByTagName('ItemID')->item($i)->nodeValue;
                $StartTime= $dom->getElementsByTagName('StartTime')->item($i)->nodeValue;
                $EndTime= $dom->getElementsByTagName('EndTime')->item($i)->nodeValue;
                
                  DB::table('Channel_product_add_update')
                    ->insert([
                        'product_id' => $product_id,
                        'listing_date'=> $StartTime,
                        'next_listing_date'=> $EndTime,
                        'channel_product_key'=>$ItemID,
                        'channel_id'=>$channel_id
                        ]);
                return 'Successfully Inserted Product with ItemID-'.$ItemID;
                }
              }

               if($api_name=='ReviseItem')
                {
                  
                  if(!empty($status)){
                    
                    DB::table('Channel_product_add_update')
                    ->where('channel_product_key', $item_id)
                    ->update(array('is_update'=> 0
                    ));
                    
                    }
                    else{
                    
                     DB::table('Channel_product_add_update')
                     ->leftJoin('product_inventory','product_inventory.product_id','=','Channel_product_add_update.product_id')
                    ->where('Channel_product_add_update.channel_product_key', $item_id)
                    ->update(array('product_inventory.is_updated'=> 0
                    )); 
                    }
                   return 'Successfully Updated Product with ItemID-'.$item_id;
                }
                
                
                if($api_name=='ReviseCheckoutStatus'){
                  return $result;
                }
                if($api_name=='PlaceOffer'){
                print_r($result);exit; 
                }
                if($api_name=='CompleteSale'){
                
                print_r($result);
                }
                if($api_name=='AddDispute'){

                $dom = new \DOMDocument();
                $dom->loadXML($result);
                $i=0;
                $links = $dom->getElementsByTagName('Ack')->item($i)->nodeValue;

                if($links=='Success'){
                            
                $Timestamp = $dom->getElementsByTagName('Timestamp')->item($i)->nodeValue;
                $DisputeID = $dom->getElementsByTagName('DisputeID')->item($i)->nodeValue;
                

                 $message = "Successfully Raised a Dispute with DisputeId-".$DisputeID;
                 

                  if($DisputeExplanation=="BuyerNoLongerWantsItem"){

                    
                  $cancel_order_id = DB::table('Channel_orders')->where('channel_order_id',$order_id)->pluck('erp_order_id'); 
                   
                  $url=$this->getDMAccess();
                  $baseurl=$url['api_key']->channel_url;
                  $form_url=$baseurl."cancelOrder";
                  $data['api_key'] = $url['api_key']->Key_value;
                  $data['secret_key'] = $url['secret_key']->Key_value;
                  $data['order_id'] = $cancel_order_id ;
                  
                   $curl = curl_init();

                    curl_setopt($curl,CURLOPT_URL, $form_url);

                    curl_setopt($curl,CURLOPT_RETURNTRANSFER, TRUE);

                    curl_setopt($curl,CURLOPT_POST, sizeof($data));

                    curl_setopt($curl,CURLOPT_POSTFIELDS, $data);

                    $catResult = curl_exec($curl);

                    curl_close($curl);
                   
                   print_r($catResult); 
                  
                    
                    DB::table('Channel_order_disputes')
                    ->where('order_id',$order_id)
                    ->update([
                        'dispute_id'=>$DisputeID,
                        'ack'=>$links,
                        'dispute_created_time'=>$Timestamp,
                        'raise_dispute'=>'0'
                        ]);
                    
                    //print_r('sucee');exit;

                  /* DB::table('Channel_order_details')
                    ->where('order_id',$order_id)
                    ->where('channel_item_id', $item_id)
                    ->update([
                      'order_id'=>$order_id,
                      'channel_order_status'=> 'CancelledByBuyer'
                      ]); */
                  
                  
                  }else{
                
                DB::table('Channel_order_disputes')->insert([
                         'dispute_id' =>$DisputeID,
                         'ack' => "Success",
                         'order_id'=>$order_id,
                         'item_id'=> $item_id,
                         'dispute_status'=>"WaitingForBuyerResponse",
                        'dispute_reason'=>"TransactionMutuallyCanceled",
                        'dispute_explanation'=>"SellerRanOutOfStock",
                        'raise_dispute' => '0'

                 ]);

                     /*DB::table('Channel_order_details')
                    ->where('item_id', $item_id)
                    ->update(['channel_order_status'=> 'CancelledBySeller'
                      ]);*/

                  }
                
                print_r($message);
                }
                else{
                     print_r($result);
                   
                }
                //return $message;
                }
                if($api_name=='AddDisputeResponse'){
                  return $result;
                }
                
                if($api_name=='GetUserDisputes'){
               // print_r($result);exit;

                $dom = new \DOMDocument();
                $dom->loadXML($result);
                $i=0;
                $links = $dom->getElementsByTagName('Ack')->item($i)->nodeValue;
                $Timestamp = $dom->getElementsByTagName('Timestamp')->item($i)->nodeValue;

                if($links=='Success'){
                $specifications = $dom->getElementsByTagName('Dispute');
               
                foreach($specifications as $a) {
                
                $DisputeID = $a->getElementsByTagName('DisputeID')->item($i)->nodeValue;
                $DisputeStatus = $a->getElementsByTagName('DisputeStatus')->item($i)->nodeValue;
                $DisputeReason = $a->getElementsByTagName('DisputeReason')->item($i)->nodeValue;
                $DisputeExplanation = $a->getElementsByTagName('DisputeExplanation')->item($i)->nodeValue;
                $ItemID = $a->getElementsByTagName('ItemID')->item($i)->nodeValue;
                
                 
                 DB::table('Channel_order_disputes')
                     ->where('dispute_id', $DisputeID)
                     ->update(array( 'dispute_status' => $DisputeStatus,
                    'dispute_reason'=>$DisputeReason,
                    'dispute_explanation'=>$DisputeExplanation,
                    'dispute_modified_time'=>$Timestamp,
                    'raise_dispute' => 0                     
                  ));

                $order_id=DB::table('Channel_order_details')->where('channel_item_id',$ItemID)->pluck('order_id');
                

                if($DisputeExplanation=="SellerRanOutOfStock"){
                
                DB::table('Channel_orders')
                  ->where('channel_order_id',$order_id)
                  ->update(array( 'order_status' => 'CancelledBySeller',
                    'channel_order_status'=>'Canceled'
                    ));

                  DB::table('Channel_order_details')
                  ->where('order_id',$order_id)
                  ->update(array( 'channel_order_status' => 'CancelledBySeller',
                    ));
                }
                else{

                  DB::table('Channel_orders')
                  ->where('channel_order_id',$order_id)
                  ->update(array( 'order_status' => 'CancelledByBuyer',
                    'channel_order_status'=>'Canceled'
                    ));

                   DB::table('Channel_order_details')
                  ->where('order_id',$order_id)
                  ->update(array( 'channel_order_status' => 'CancelledByBuyer',
                    ));

                }

                   $message="Successfully updated disputes";  
                
                }
                
                }
                return $message;
                }

                if($api_name=='GetOrders')
                {
                //print_r($result);exit;
                $dom = new \DOMDocument();
                $dom->loadXML($result);  
                $i=0;
                
                $ack = $dom->getElementsByTagName('Ack')->item($i)->nodeValue;
                
                if($ack=='Success'){
                
                $specifications = $dom->getElementsByTagName('Order');

                date_default_timezone_set("Asia/Calcutta");
                
               
                if(!empty($specifications->item($i)->nodeValue)){
                  
                foreach($specifications as $a) {
                
                $order_id=$a->getElementsByTagName('OrderID')->item($i)->nodeValue;
                
                $order_status=$a->getElementsByTagName('OrderStatus')->item($i)->nodeValue;
                
                $order_createdtime=$a->getElementsByTagName('CreatedTime')->item($i)->nodeValue;
                $timestamp = strtotime($order_createdtime);
                $order_createdtime=date('Y-m-d H:i:s',$timestamp);
                $LastModifiedTime=$a->getElementsByTagName('LastModifiedTime')->item($i)->nodeValue;
                $timestamp = strtotime($LastModifiedTime);
                $LastModifiedTime=date('Y-m-d H:i:s',$timestamp);
                $order_adjustment_amount=$a->getElementsByTagName('AdjustmentAmount')->item($i)->nodeValue;  
                $order_TransactionID=$a->getElementsByTagName('TransactionID')->item($i)->nodeValue; 
                $order_amountpaid=$a->getElementsByTagName('AmountPaid')->item($i)->nodeValue;
                $order_paymentmethod=$a->getElementsByTagName('PaymentMethods')->item($i)->nodeValue;
                $order_paymentstatus=$a->getElementsByTagName('Status')->item($i)->nodeValue;
                $order_subtotal=$a->getElementsByTagName('Subtotal')->item($i)->nodeValue;
                $order_Total=$a->getElementsByTagName('Total')->item($i)->nodeValue;
                $Phone=$a->getElementsByTagName('Phone')->item($i)->nodeValue;
                
                $order_ItemID=$a->getElementsByTagName('ItemID')->item($i)->nodeValue; 

                $order_discount=$a->getElementsByTagName('AdjustmentAmount')->item($i)->nodeValue;
                $order_TransactionPrice=$a->getElementsByTagName('TransactionPrice')->item($i)->nodeValue;
                $order_quantity=$a->getElementsByTagName('QuantityPurchased')->item($i)->nodeValue;
                $order_servicename=$a->getElementsByTagName('ShippingService')->item($i)->nodeValue;
                $order_servicecost=$a->getElementsByTagName('ShippingServiceCost')->item($i)->nodeValue;
                
                //$order_min_timeto_dispatch=$a->getElementsByTagName('ShippingTimeMin')->item(0)->nodeValue;
                
                //$order_max_timeto_dispatch=$a->getElementsByTagName('ShippingTimeMax')->item(0)->nodeValue;
                
                $order_name=$a->getElementsByTagName('Name')->item($i)->nodeValue;
                $order_address1=$a->getElementsByTagName('Street1')->item($i)->nodeValue;
                $order_address2=$a->getElementsByTagName('Street2')->item($i)->nodeValue;
                $order_city=$a->getElementsByTagName('CityName')->item($i)->nodeValue;
                //$order_ShippingService=$a->getElementsByTagName('ShippingService')->item(0)->nodeValue;
                $order_state=$a->getElementsByTagName('StateOrProvince')->item($i)->nodeValue;
                $order_country=$a->getElementsByTagName('CountryName')->item($i)->nodeValue;
                $order_pincode=$a->getElementsByTagName('PostalCode')->item($i)->nodeValue;
                $order_phone=$a->getElementsByTagName('Phone')->item($i)->nodeValue;
                $order_buyeremail=$a->getElementsByTagName('Email')->item($i)->nodeValue;
                $order_buyername=$a->getElementsByTagName('UserFirstName')->item($i)->nodeValue;
                $channel_id= DB::Table('Channel')->where('channnel_name','eBay')->first();
                
                
                if(!empty($order_id)){
                
               $order_exists = DB::table('Channel_orders')->where('channel_order_id',$order_id)->get();
               
               //DB::table('Channel_product_add_update')->where('channel_product_key',)
                

                if(!empty($order_exists)){
                
                $order_Items = $a->getElementsByTagName('Item');
                $products = array();
                $final_array = array();
                $j=0;

                foreach($order_Items as $value) { 
                  $order_quantity=$a->getElementsByTagName('QuantityPurchased')->item($j)->nodeValue;
                  $ItemID=$a->getElementsByTagName('ItemID')->item($j)->nodeValue;
                  $TransactionID=$a->getElementsByTagName('TransactionID')->item($j)->nodeValue;
                  $TransactionPrice=$a->getElementsByTagName('TransactionPrice')->item($j)->nodeValue;
                  
                  $ItemID = DB::table('Channel_product_add_update')->where('channel_product_key',$ItemID)->pluck('product_id');
                  
                   DB::table('Channel_order_details')
                  ->where('channel_item_id',$value->getElementsByTagName('ItemID')->item(0)->nodeValue)
                  ->update(array('order_id' =>$order_id,
                         'channel_id' => $channel_id->channel_id,
                         'channel_item_id'=>$value->getElementsByTagName('ItemID')->item(0)->nodeValue,
                         'quantity'=> $order_quantity,
                         'price'=>$TransactionPrice,
                        'discount_price'=>$order_discount,
                        'sco_item_id'=>$ItemID,
                        'transaction_id'=>$TransactionID                       
                      ));
                 $j++;
                }
                     
                     DB::Table('Channel_orders')
                    ->where('channel_order_id',$order_id)
                    ->update(array( 'channel_id' => $channel_id->channel_id,
                    'channel_order_id'=>$order_id,
                    //'order_status'=>$order_status,
                    'payment_method'=>$order_paymentmethod,
                    'shipping_cost'=>$order_servicecost,
                    'sub_total'=>$order_subtotal,
                    'total_amount'=> $order_Total,
                    'order_date'=>$order_createdtime,
                    'currency_code'=>'INR'                        
                  ));
                 
                 

                  DB::table('Channel_order_shipping_details')
                  ->where('order_id',$order_id)
                  ->update(array('channel_id' => $channel_id->channel_id,
                        'order_id'=>$order_id,
                        'service_name'=>$order_servicename,
                        'service_cost'=>$order_servicecost                      
                      ));

                  DB::table('Channel_orders_shipping_address')
                  ->where('order_id',$order_id)
                  ->update(array( 'order_id'=>$order_id,
                          'channel_id' => $channel_id->channel_id,
                          'name'=>$order_name,
                          'address1'=> $order_address1,
                          'address2'=>$order_address2,
                          'city'=>$order_city,
                          'state'=>$order_state,
                          'country'=>$order_country,
                          'pincode'=> $order_pincode,
                          'phone'=> $order_phone                       
                      ));

                  DB::table('Channel_order_payment')
                  ->where('order_id',$order_id)
                  ->update(array('order_id'=>$order_id,
                    'channel_id' => $channel_id->channel_id,
                    'payment_method'=>$order_paymentmethod,
                    'payment_status'=>$order_paymentstatus,
                    'payment_currency'=>'INR',
                     'amount'=>$order_Total,
                    'buyer_email'=>$order_buyeremail,
                    'buyer_name'=>$order_buyername                     
                      ));
                  
                $message = "Successfully Updated the records";
                print_r($message);
                
                }
                
                else{
                 
                
                $order_Items = $a->getElementsByTagName('Item');
                $products = array();
                $check_inventory_arr = array();
                $final_array = array();
                $j=0;
               
                 //print_r($order_id);
                $final_dispute_array = array();    
                
                foreach($order_Items as $value) { //echo "TEst =><pre>"; print_r($value);
                  $order_quantity=$a->getElementsByTagName('QuantityPurchased')->item($j)->nodeValue;
                  $ItemID=$a->getElementsByTagName('ItemID')->item($j)->nodeValue;
                  $TransactionID=$a->getElementsByTagName('TransactionID')->item($j)->nodeValue;
                  $TransactionPrice=$a->getElementsByTagName('TransactionPrice')->item($j)->nodeValue;
                  
                  $ItemID = DB::table('Channel_product_add_update')->where('channel_product_key',$ItemID)->pluck('product_id');

                  $product_availability=DB::table('Channel_product_add_update as Cpau')
                                    ->leftJoin('product_inventory as pi','pi.product_id','=','Cpau.product_id')
                                    ->leftJoin('products as pd','pd.product_id','=','Cpau.product_id')
                                    ->where('channel_product_key',$value->getElementsByTagName('ItemID')->item(0)->nodeValue)
                                    ->first();
                 // print_r($value->getElementsByTagName('ItemID')->item(0)->nodeValue);
                  //print_r($product_availability);
                  $products['sku']        = $product_availability->sku;
                  $products['channelId']  = '1';
                  $products['order_id']   = $order_id;
                  $products['channelitemid']   = $value->getElementsByTagName('ItemID')->item(0)->nodeValue;
                  $products['scoitemid']      = $product_availability->product_id;
                  $products['quantity']      = $order_quantity;
                  $products['price'] = $TransactionPrice;
                  $products['sellprice'] = " ";
                  $products['discounttype']= " ";
                  $products['discountprice']= " ";
                  $products['tax']= " ";
                  $products['subtotal'] = $TransactionPrice;
                  $products['channelcancelitem'] = "";
                  $products['total']= $TransactionPrice;
                  $products['shippingcompanyname']= " ";
                  $products['servicename']= $order_servicename;
                  $products['servicecost']= $order_servicecost;
                  $products['dispatchdate']= " ";
                  $products['mintimetodispatch']= " ";
                  $products['maxtimetodispatch']= " ";
                  $products['timeunits']= " ";

                  $dispute_array['item_id'] = $value->getElementsByTagName('ItemID')->item(0)->nodeValue;
                  $dispute_array['transaction_id'] = $TransactionID;
                  $dispute_array['dispute_reason'] = 'TransactionMutuallyCanceled';
                  $dispute_array['dispute_explanation'] = 'SellerRanOutOfStock';
                  $dispute_array['order_id'] = $order_id;
                  
                  $final_dispute_array[$j] = $dispute_array;

                  
                  

                  $final_array[] = $products;

                   
                   
                    DB::table('Channel_order_details')->insert([
                         'order_id' =>$order_id,
                         'channel_id' => $channel_id->channel_id,
                         'channel_item_id'=>$value->getElementsByTagName('ItemID')->item(0)->nodeValue,
                         'quantity'=> $order_quantity,
                         'price'=>$order_TransactionPrice,
                        'discount_price'=>$order_discount,
                        'channel_order_status'=>$order_status,
                        'sco_item_id'=>$ItemID,
                        'transaction_id' => $TransactionID

                 ]);
                  
                  
                   $url=$this->getDMAccess();
                   $baseurl=$url['api_key']->channel_url;
                   $form_url=$baseurl."checkInventoryAvailability";
                   $data_to_post['api_key'] = $url['api_key']->Key_value;
                   $data_to_post['secret_key'] = $url['secret_key']->Key_value;

                   $productData = json_encode(array('sku'=>$product_availability->sku,'quantity'=>$order_quantity,
                    'price'=>$TransactionPrice,'total'=>($TransactionPrice*$order_quantity)));
                   
                   $data_to_post['is_blocked'] =1;
                   $data_to_post['product_data'] = $productData;
                   $data_to_post['pincode']= 110001;

                   //print_r($data_to_post);exit;
                    $curl = curl_init();

                    curl_setopt($curl,CURLOPT_URL, $form_url);

                    curl_setopt($curl,CURLOPT_RETURNTRANSFER, TRUE);

                    curl_setopt($curl,CURLOPT_POST, sizeof($data_to_post));
                    
                    curl_setopt($curl,CURLOPT_POSTFIELDS,$data_to_post);
                    
                    $catResult = curl_exec($curl);
                    
                    curl_close($curl);
                  
                    $catResult = json_decode($catResult);
                   
                    $check_outstock[] = $catResult->Message;
                    
                    $j++;  
                }
                
                
                $outofstock=strpos(json_encode($check_outstock),'Stock not available');
                

                if(!empty($product_availability)){
                  
                 if(empty($outofstock)){
                
                   $eBay_channel_id = DB::table('Channel')->where('channnel_name','eBay')->pluck('channel_id');
                   
                   $customer_info['suffix']=' ';
                   $customer_info['first_name']=$order_name;
                   $customer_info['middle_name']=' ';
                   $customer_info['last_name']=' ';
                   $customer_info['channel_user_id']='';
                   $customer_info['email_address']=$order_buyeremail;
                   $customer_info['mobile_no']=$Phone;
                   $customer_info['dob']=' ';
                   $customer_info['channel_id']=$eBay_channel_id;
                   $customer_info['gender']=' ';
                   $customer_info['registered_date']=' ';
                   
                   $address_info['address_type']=' ';
                   $address_info['first_name']=$order_name;
                   $address_info['middle_name']=' ';
                   $address_info['last_name']=' ';
                   $address_info['address1']=$order_address1;
                   $address_info['address2']=$order_address2;
                   $address_info['city']=$order_city;
                   $address_info['state']=$order_state;
                   $address_info['phone']=$order_phone;
                   $address_info['pincode']=$order_pincode;
                   $address_info['country']=$order_country;
                   $address_info['company']=' ';
                   $address_info['mobile_no']=$order_phone;

                   $order_info['channelid']=$eBay_channel_id;
                   $order_info['channelorderid']=$order_id;
                   $order_info['orderstatus']=$order_status;
                   $order_info['orderdate']=$order_createdtime;
                   $order_info['paymentmethod']=$order_paymentmethod;
                   $order_info['shippingcost']=$order_servicecost;
                   $order_info['subtotal']=$order_subtotal;
                   $order_info['tax']=' ';
                   $order_info['totalamount']=$order_Total;
                   $order_info['currencycode']='INR';
                   $order_info['channelorderstatus']=$order_status;
                   $order_info['updateddate']=$LastModifiedTime;
                   $order_info['gdsorderid']=$order_id;;
                   $order_info['channelcustid']=' ';
                   $order_info['createddate']=' ';

                  $payment_info["order_id"] = $order_id;
                  $payment_info['channelid'] = $eBay_channel_id;
                  $payment_info['paymentmethod'] = $order_paymentmethod;
                  $payment_info["paymentstatus"] =$order_paymentstatus;
                  $payment_info["paymentcurrency"] ="INR";
                  $payment_info["amount"]= $order_Total;
                  $payment_info["buyeremail"] = $order_buyeremail;
                  $payment_info["buyername"] = $order_name;
                  $payment_info["buyerphone"] = $order_phone;
                  $payment_info["transactionId"] = $order_TransactionID;
                  $payment_info["paymentDate"] = " ";
                    


                   $order_data['customer_info'] = $customer_info;
                   $order_data['address_info'] = $address_info;
                   $order_data['order_info'] = $order_info;
                   $order_data['product_info']= $final_array ;
                   $order_data['payment_info'] = $payment_info;

                   $order_data_req = json_encode($order_data);
                   $data['orderdata'] = $order_data_req;
                   
                   $url=$this->getDMAccess();
                   $baseurl=$url['api_key']->channel_url;
                   $form_url=$baseurl."placeOrder";
                   $data['api_key'] = $url['api_key']->Key_value;
                   $data['secret_key'] = $url['secret_key']->Key_value;

                   $curl = curl_init();

                    curl_setopt($curl,CURLOPT_URL, $form_url);

                    curl_setopt($curl,CURLOPT_RETURNTRANSFER, TRUE);

                    curl_setopt($curl,CURLOPT_POST, sizeof($data));

                    curl_setopt($curl,CURLOPT_POSTFIELDS, $data);
                    
                    $catResult = curl_exec($curl);
                    
                    curl_close($curl);
                    
                    $catResult=json_decode($catResult);   

                    $erp_order_id=$catResult->order_id;

                     DB::table('Channel_orders')->insert([
                        'channel_id' => $channel_id->channel_id,
                        'channel_order_id'=>$order_id,
                        'order_status'=>$order_status,
                        'channel_order_status'=>$order_status,
                        'payment_method'=>$order_paymentmethod,
                        'shipping_cost'=>$order_servicecost,
                        'sub_total'=>$order_subtotal,
                        'total_amount'=> $order_Total,
                        'order_date'=>$order_createdtime,
                        'erp_order_id'=>$erp_order_id,
                        'currency_code'=>'INR',
                  ]);

                    DB::table('Channel_orders')->where('channel_order_id',$order_id)->update(array('erp_order_id'=>$erp_order_id));

                    print_r($catResult); 
                   
                 }  
                else{
                    
                
                $messag[] = "Stock is not available for product with Item Id:".$order_ItemID;

                $url=$this->getDMAccess();
                
                $channelurl=$url['api_key']->channel_url;
                
                $baseurl=substr($channelurl,0,strpos($channelurl,'dmapi'));
                
                $productData = json_encode($final_dispute_array);
                
                $form_url = $baseurl."ebaydeveloper/addDispute/".$productData;
                
                $curl = curl_init();

                curl_setopt($curl,CURLOPT_URL, $form_url);

                curl_setopt($curl,CURLOPT_RETURNTRANSFER, TRUE);

                //curl_setopt($curl,CURLOPT_POST, sizeof($data));

                //curl_setopt($curl,CURLOPT_POSTFIELDS, $data);

                $catResult = curl_exec($curl);
                
                curl_close($curl);     
                
                print_r($catResult); 
                
                
                 DB::table('Channel_orders')->insert([
                        'channel_id' => $channel_id->channel_id,
                        'channel_order_id'=>$order_id,
                        'order_status'=>$order_status,
                        'channel_order_status'=>$order_status,
                        'payment_method'=>$order_paymentmethod,
                        'shipping_cost'=>$order_servicecost,
                        'sub_total'=>$order_subtotal,
                        'total_amount'=> $order_Total,
                        'order_date'=>$order_createdtime,
                        'currency_code'=>'INR',
                  ]);
                 
            }
                
                              
              DB::table('Channel_order_shipping_details')->insert([
                        'channel_id' => $channel_id->channel_id,
                        'order_id'=>$order_id,
                        'service_name'=>$order_servicename,
                        'service_cost'=>$order_servicecost
                        //'min_time_to_dispatch'=>$order_min_timeto_dispatch,
                        //'max_time_to_dispatch'=>$order_max_timeto_dispatch
                  ]);
            
              

                DB::table('Channel_orders_shipping_address')->insert([
                          'order_id'=>$order_id,
                          'channel_id' => $channel_id->channel_id,
                          'name'=>$order_name,
                          'address1'=> $order_address1,
                          'address2'=>$order_address2,
                          'city'=>$order_city,
                          'state'=>$order_state,
                          'country'=>$order_country,
                          'pincode'=> $order_pincode,
                          'phone'=> $order_phone 
                     ]);
                
            
              DB::table('Channel_order_payment')->insert([
                    'order_id'=>$order_id,
                    'channel_id' => $channel_id->channel_id,
                    'payment_method'=>$order_paymentmethod,
                    'payment_status'=>$order_paymentstatus,
                    'payment_currency'=>'INR',
                     'amount'=>$order_Total,
                    'buyer_email'=>$order_buyeremail,
                    'buyer_name'=>$order_buyername
                     ]);
              
               $message = "Successfully Inserted the records";
               
               
                }
              else{
                  
                  $message = 'The Following Product with Item Id '. $order_ItemID .' Not In The eSeal GDS System';
                  
                  print_r($message);
                  
                  }
                }
                }
                }
                
                }
              else{
                  
                  $message = 'No New Orders';
                  
                  print_r($message);
                 
                 } 
                }
             
                if(!empty($catResult)){
                  print_r($catResult);
                  }
                  
                  print_r($message);  
                  
                }
         
    }
   
    public function getHeaders($api_name){
              
              $eBay_channel_id = DB::table('Channel')->where('channnel_name','eBay')->pluck('channel_id');
              
              $dev_token = DB::table('Channel_configuration')
                           ->where(array('Key_name'=>'dev_token','channel_id'=>$eBay_channel_id))
                           ->pluck('Key_value');

              $app_token = DB::table('Channel_configuration')
                           ->where(array('Key_name'=>'app_token','channel_id'=>$eBay_channel_id))
                           ->pluck('Key_value');

              $cert_token = DB::table('Channel_configuration')
                           ->where(array('Key_name'=>'cert_token','channel_id'=>$eBay_channel_id))
                           ->pluck('Key_value');                      
              
             if($api_name == 'AddDispute'){
              $site_id=0;
              }
              else{
               $site_id=203; 
              }

              $headers=array('X-EBAY-API-COMPATIBILITY-LEVEL:967',
              'X-EBAY-API-DEV-NAME:'.$dev_token,
              'X-EBAY-API-APP-NAME:'.$app_token,
              'X-EBAY-API-CERT-NAME:'.$cert_token,
              'X-EBAY-API-SITEID:'.$site_id,
              'X-EBAY-API-CALL-NAME:'.$api_name,);
            
           //return 'here';

          return $headers;
    }
   
    public function array_to_xml($array, $xml_user_info) {
     
        foreach($array as $key => $value) {
     
            if(is_array($value)) {
                if(!is_numeric($key)){
                    $subnode = $xml_user_info->addChild("$key");
                    $this->array_to_xml($value, $subnode);
                }else{
                    $subnode = $xml_user_info->addChild("item$key");
                    $this->array_to_xml($value, $subnode);
                }
            }else {
                $xml_user_info->addChild("$key",htmlspecialchars("$value"));
            }
        }
    }

    public function getGdsProducts(){
     
        $eBay_channel_id = DB::table('Channel')->where('channnel_name','eBay')->pluck('channel_id');
        //print_r($eBay_channel_id);exit;
        return  DB::table('Channel_product_add_update')
                ->leftJoin('products as prod','Channel_product_add_update.product_id','=','prod.product_id')
                ->leftJoin('product_inventory','product_inventory.product_id','=','Channel_product_add_update.product_id')
                ->leftJoin('locations','product_inventory.location_id','=','locations.location_id')
                ->leftJoin('product_attributes','product_attributes.product_id','=','prod.product_id')
                ->leftJoin('attributes','product_attributes.attribute_id','=','attributes.attribute_id')
                ->leftJoin('Channel_category','Channel_category.category_id','=','Channel_product_add_update.category_id')
                ->where('is_added',1)
                ->where('Channel_product_add_update.channel_id',$eBay_channel_id)
                ->select(DB::raw('CONCAT(group_concat(product_attributes.value,"$",attributes.name)) as product_attributes'),'prod.name as Title','prod.description as Description','prod.product_id','prod.mrp as StartPrice','prod.image as PictureURL','Channel_category.channel_category_id','prod.upc','locations.location_address','locations.city')
                ->groupBy('prod.product_id')
                ->take(5)
                ->get();
     
     }
     
     public function getAddInventory($pid){
     
      return DB::table('product_inventory')
             ->select(DB::raw('sum(available_inventory) as qty'))
             ->where('product_id',$pid)
             ->pluck('qty');  
     
     }
     
     public function getXmlArray($quantity,$category_id='',$upc,$address,$city){
      
      $paypal_account = 'venkatmyd@gmail.com';
      
      if(empty($upc))
      {
      $upc_number = 'NA';
      }
      $new_product_category = '1000';
      $PostalCode = '500039';
      $status_update = 'false'; 
      $shipping_cost = '0.00';

     
      $xml_array=array();
      
      
      $xml_array['PrimaryCategory']['CategoryID']=$category_id;
      $xml_array['ConditionID']=$new_product_category;
      $xml_array['Country']="IN";
      $xml_array['Currency']="INR";
      $xml_array['DispatchTimeMax']="3";
      $xml_array['ListingDuration']="Days_7";
      $xml_array['ItemSpecifics']="";
      $xml_array['PaymentMethods']="PaisaPayEscrow";
      //$xml_array['PaymentMethods']="PaisaPayEscrow";
      $xml_array['Location'] = $address.$city; 
      $xml_array['PayPalEmailAddress']=$paypal_account;
      $xml_array['PostalCode']=$PostalCode;
      $xml_array['ProductListingDetails']['UPC']=$upc_number;
      //$xml_array['ProductListingDetails']['IncludeStockPhotoURL']="true";
      //$xml_array['ProductListingDetails']['IncludePrefilledItemInformation']=$status_update;
      //$xml_array['ProductListingDetails']['UseFirstProduct']=$status_update;
      //$xml_array['ProductListingDetails']['UseStockPhotoURLAsGallery']=$status_update;
      //$xml_array['ProductListingDetails']['ReturnSearchResultOnDuplicates']=$status_update;
      $xml_array['Quantity']=$quantity;
      $xml_array['ReturnPolicy']['ReturnsAcceptedOption']="ReturnsAccepted";
      $xml_array['ReturnPolicy']['RefundOption']="MoneyBack";
      $xml_array['ReturnPolicy']['ReturnsWithinOption']="Days_30";
      $xml_array['ReturnPolicy']['ShippingCostPaidByOption']="Buyer";
      $xml_array['ShippingDetails']['ShippingType']="Flat";
      //$xml_array['ShippingDetails']['ShippingServiceOptions']['ShippingServicePriority']="1";
      $xml_array['ShippingDetails']['ShippingServiceOptions']['ShippingService']="IN_Courier";
      $xml_array['ShippingDetails']['ShippingServiceOptions']['FreeShipping']="true";
      $xml_array['ShippingDetails']['ShippingServiceOptions']['ShippingServiceAdditionalCost']=$shipping_cost;
      $xml_array['Site']="US";
      
      return $xml_array;
     
     }
     
     public function getUpdateItem()
    {
              $eBay_channel_id = DB::table('Channel')->where('channnel_name','eBay')->pluck('channel_id'); 
              
              $result= DB::table('products as prod')
                ->leftJoin('Channel_product_add_update as cpau','prod.product_id','=','cpau.product_id')
                ->leftJoin('product_attributes','product_attributes.product_id','=','prod.product_id')
                ->leftJoin('attributes','product_attributes.attribute_id','=','attributes.attribute_id')
                ->select(DB::raw('CONCAT(group_concat(product_attributes.value,"$",attributes.name)) as product_attributes'),'prod.name as Title','prod.description as Description','prod.product_id','prod.mrp as StartPrice','cpau.channel_product_key as ItemID','prod.image as PictureURL')
                ->where('is_update','=','1')
                ->where('cpau.channel_id',$eBay_channel_id)
                ->groupBy('prod.product_id')
                ->take(5)
                ->get();

                return  $result;


    }

     public function get_updated_qty()
    {
           
          $eBay_channel_id = DB::table('Channel')->where('channnel_name','eBay')->pluck('channel_id'); 

           $result= DB::table('product_inventory as prodinv')
                         ->leftJoin('Channel_product_add_update as cod','prodinv.product_id','=','cod.product_id')
                         ->select('cod.channel_product_key as ItemID','prodinv.available_inventory as Quantity','prodinv.product_id')
                         ->where('prodinv.is_updated',1)
                         ->where('cod.channel_id',$eBay_channel_id)
                         ->get();
          
          return  $result;

    }
    public function getUpdatedOrder()
    {
      
        $eBay_channel_id = DB::table('Channel')->where('channnel_name','eBay')->pluck('channel_id'); 

              return DB::table('Channel_orders as Co')
              ->leftJoin('Channel_order_details as Cod','Cod.order_id','=','Co.channel_order_id')
              ->leftJoin('Channel_order_payment as Cop','Cop.order_id','=','Cod.order_id')
              ->leftJoin('Channel_order_shipping_details as Cosd','Cosd.order_id','=','Cop.order_id')
              ->leftJoin('Channel_orders_shipping_address as Cosa','Cosa.order_id','=','Cosd.order_id')
              ->leftJoin('Channel as ch','ch.channel_id','=','Co.channel_id')
              ->where('Cod.order_status',1)
              ->where('ch.channel_id',$eBay_channel_id)
              ->select('Cosa.*','Cod.channel_item_id as ItemID','Cop.payment_status as OrderStatus','Co.payment_method as PaymentMethodUsed','Cop.amount','Cosd.service_name','Cod.transaction_id')
              ->get();
        
    }

    public function completeorderdetails()
    {
     $eBay_channel_id = DB::table('Channel')->where('channnel_name','eBay')->pluck('channel_id'); 

      return DB::table('Channel_order_details')->where(array('order_status'=>1,'channel_id'=>$eBay_channel_id))->get();
    
    }
     public function getdispute()
    {
         return DB::table('Channel_order_disputes')
               //->leftJoin('Channel_order_details as channel','channel.order_id','=','Channel_order_disputes.order_id')
               //->Join('Channel_order_details as ch','ch.channel_item_id','=','Channel_order_disputes.item_id')
               ->where('raise_dispute',1)
               ->orWhere('raise_dispute',2)
               ->select('dispute_reason','dispute_explanation','order_id','item_id','transaction_id','raise_dispute')
               ->get();
    }
    public function getraisedDisputes() 
    {
      return DB::table('Channel_order_disputes')
               ->where('dispute_status','WaitingForBuyerResponse')
               ->where(DB::raw('DATEDIFF(dispute_modified_time,dispute_created_time)'),'>',7)
               ->select(DB::raw("DATEDIFF(dispute_modified_time,dispute_created_time) AS Days"),'Channel_order_disputes.dispute_id')
               ->get();
    }         
    public function getPaymentZoneDetails($postcode){
      
      $state_name= DB::table('cities_pincodes')
                                        ->where('PinCode',$postcode)
                                        ->pluck('State');
                   
      $payment_state_name = ucwords(strtolower($state_name));
                   
      $zones = DB::table('zone')
                       ->where('name',$state_name)->first();
      
      return $zones;
    
    }

     public function getrelistproducts() 
    {
      $eBay_channel_id = DB::table('Channel')->where('channnel_name','eBay')->pluck('channel_id');

      return DB::table('Channel_product_add_update')
                ->where(DB::raw('DATEDIFF(NOW(),listing_date)'),'>','30')
                ->where('channel_id',$eBay_channel_id)
                ->where(DB::raw('DATEDIFF(NOW(),listing_date)'),'<','90')
               ->select('channel_product_key as ItemID','product_id','channel_id')
               ->get();
    }

    public function getUrl(){

                $eBay_channel_id = DB::table('Channel')->where('channnel_name','eBay')->pluck('channel_id');

                $url = DB::table('Channel_configuration as conf')
                           ->leftJoin('Channel as ch','conf.channel_id','=','ch.channel_id')
                           ->select ('conf.Key_value','ch.channel_url')
                           ->where(array('conf.Key_name'=>'auth_token','conf.channel_id'=>$eBay_channel_id))
                           ->first();
                 
                return $url;
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

   public function gdsEnabled(){

        $finished_product=DB::table('master_lookup')->where('name','Finished Product')->first();
      
          return DB::table('products as pd')
                 ->leftJoin('Channel_product as cp','cp.product_id','=','pd.product_id')
                 ->where(array('cp.status'=>1,'pd.is_gds_enabled'=>1,'pd.product_type_id'=>$finished_product->value,'pd.content_approved'=>1))
                 ->select('cp.channel_id','pd.product_id','pd.is_channel_updated','cp.status','pd.category_id','pd.is_deleted')->get();
      
   
   }

   public function channelInsert($gds_enabled){
        
      
      foreach($gds_enabled as $key=>$value){
      
      $product_exists = DB::table('Channel_product_add_update as cpu')
                         ->where(array('cpu.product_id'=>$value->product_id,'cpu.channel_id'=>$value->channel_id))
                         ->get(); 
        
       if(empty($product_exists)){
      
       if($value->status==1){
         
         DB::table('Channel_product_add_update')
         ->insert([ 
          'channel_id' =>$value->channel_id,
          'product_id' =>$value->product_id,
          'is_added' =>1,
          'is_update' =>0,
          'category_id' =>$value->category_id
         ]);
          
          }
        
        }

        if($value->is_channel_updated==1){

           DB::table('Channel_product_add_update')
          ->where('product_id', $value->product_id)
          ->update(array('is_update'=> 1,
                   ));
         }
        
        if($value->is_deleted==1){

           DB::table('Channel_product_add_update')
          ->where('product_id', $value->product_id)
          ->update(array('is_deleted'=> 1,
                   ));
         }
    }
    return "Successfully inserted into gdsDatabase";
   
   }
   
   public function getGDSorderData($data)
    {
        try
        {
            $responseData = array();
            $locationId = isset($data['location_id']) ? $data['location_id'] : 0;
            $manufacturerId = isset($data['manufacturer_id']) ? $data['manufacturer_id'] : 0;
            $myDate = date("Y-m-d H:i:s", strtotime(date("Y-m-d H:i:s", strtotime(date("Y-m-d H:i:s"))) . "-3 month"));
            $fromDate = isset($data['from_date']) ? (($data['from_date'] != '') ? $data['from_date'] : $myDate) : $myDate;
            $toDate = isset($data['to_date']) ? (($data['to_date'] != '') ? $data['to_date'] : date('Y-m-d H:i:s')) : date('Y-m-d H:i:s');
            $order_status = isset($data['order_status']) ? (($data['order_status'] != '') ? $data['order_status'] : '17002, 17003, 17004, 17005') : '17002, 17003, 17004, 17005';
            $channelId = isset($data['channel_id']) ? (($data['channel_id'] != '') ? $data['channel_id'] : $this->getAllchannelIds()) : $this->getAllchannelIds();
            if ($locationId)
            {
                $orderDetails = DB::table('gds_orders')
                    ->join('gds_customer as cust', 'cust.gds_cust_id', '=', 'gds_orders.gds_cust_id')
                    ->join('gds_order_products as prds', 'prds.gds_order_id', '=', 'gds_orders.gds_order_id')
                    ->leftJoin('product_locations as loc', 'loc.product_id', '=', 'prds.pid')
                    // ->leftJoin('gds_orders_addresses as addrs', 'addrs.gds_order_id', '=', 'gds_orders.gds_order_id')
                    ->join('locations', 'locations.location_id', '=', 'loc.location_id')
//                    ->join(DB::raw('locations on locations.manufacturer_id = -1'))
                    ->join('channel_orders as orders','orders.channel_order_id','=','gds_orders.channel_order_id')
                    ->join('channel as chn','chn.channel_id','=','orders.channel_id')
                    ->join('channel_order_payment as pay','pay.order_id','=','orders.channel_order_id')
                    ->join('channel_orders_address as ship','ship.order_id','=','orders.channel_order_id')
                    ->join('master_lookup as lookup','lookup.value','=','gds_orders.order_status_id')
//                        ->where('gds_orders.order_date', '>', $fromDate)
                    ->whereBetween('gds_orders.order_date', [$fromDate, $toDate])
                    ->whereNotNull('prds.pid')
                    ->where('loc.location_id', $locationId)
                    ->whereIn('chn.channel_id', explode(',', $channelId))
                    ->whereIn('gds_orders.order_status_id', explode(',', $order_status))
                    ->select('gds_orders.gds_order_id', 'gds_orders.gds_cust_id', 
                            'gds_orders.channel_id', 'gds_orders.channel_order_id', 
                            'gds_orders.order_date', 'cust.email_address', 'chn.channnel_name',
                            'chn.channel_url', 
                            'lookup.name as order_status', 
                            'orders.payment_method',
                            'pay.payment_currency', 
                            'locations.location_id',
                            DB::raw("concat(cust.firstname, ' ',cust.lastname) as customer_name"),
                            DB::raw("IFNULL(CONCAT(locations.firstname , ' ',locations.lastname), '') as name"), 
                            DB::raw("IFNULL(gds_orders.erp_order_id, '') as erp_order_id"), 
                            DB::raw("IFNULL(pay.payment_status_id, '') as payment_status"), 
                            DB::raw("IFNULL(locations.location_name, '') as company"), 
                            DB::raw("IFNULL(locations.location_address, '') as addr1"), 
                            DB::raw("IFNULL(locations.location_details, '') as addr2"), 
                            DB::raw("IFNULL(locations.city, '') as city"), 
                            DB::raw("IFNULL(locations.pincode, '') as postcode"), 
                            DB::raw("IFNULL(locations.state, '') as state_id"), 
                            DB::raw("IFNULL(locations.country, '') as country_id"), 
                            DB::raw("IFNULL(locations.phone_no, '') as telephone"), 
                            DB::raw("IFNULL(locations.phone_no, '') as mobile"),
                            DB::raw("IFNULL(ship.address1, '') as address1"),
                            DB::raw("IFNULL(ship.address2, '') as address2"),
                            DB::raw("IFNULL(ship.city, '') as city"),
                            DB::raw("IFNULL(ship.state, '') as state"),
                            DB::raw("IFNULL(ship.country, '') as country"),
                            DB::raw("IFNULL(ship.pincode, '') as pincode"))
                    ->get();
				$last = DB::getQueryLog();
                \Log::info(end($last));	
//                echo "<pre>";print_R(end($last));die;
//                echo "<pre>";print_R($orderDetails);die;
                if (!empty($orderDetails))
                {
                    $esealTable = 'eseal_' . $manufacturerId;
                    foreach ($orderDetails as $orders)
                    {
                        $temp = array();
                        $temp['order_data'] = $orders;
                        //                   echo "<pre>";print_R($orders);die;
                        $productDetails = DB::table('gds_orders')
                                ->join('gds_order_products as prds', 'prds.gds_order_id', '=', 'gds_orders.gds_order_id')
                                ->join('products', 'products.product_id', '=', 'prds.pid')
                                ->join($esealTable, $esealTable.'.pid', '=', 'prds.pid')
                                ->join('track_history', $esealTable.'.track_id', '=', 'track_history'.'.track_id')
                                ->where(array('track_history.src_loc_id' => $locationId, 
                                    'track_history.dest_loc_id'=> 0,
                                    $esealTable.'.level_id' => 0, 
                                    'gds_orders.gds_order_id' => $orders->gds_order_id
                                    ))
                                ->select(DB::raw("IFNULL(prds.pid, '') as product_id"), 
                                        DB::raw("IFNULL(prds.pname, '') as product_name"), 
                                        DB::raw("IFNULL(products.material_code, '') as material_code"), 
                                        DB::raw("IFNULL(prds.price, '') as price"),
                                        DB::raw("IFNULL(prds.qty, '') as order_quantity"), 
                                        DB::raw('count(*) as available_quantity'))
                                ->get();
                        $temp['product_data'] = $productDetails;
                        $responseData[] = $temp;
                    }
                }
            } else
            {
                return 'No location';
            }
            return $responseData;
        } catch (Exception $ex)
        {
            \Log::info($ex->getTraceAsString());
            return $ex->getTraceAsString();
        }
    }
    
    public function getInvoiceDetails($data)
    {
        try
        {
            $responseData = array();
            $orderId = isset($data['order_id']) ? $data['order_id'] : 0;
            if ($orderId)
            {
                $invoiceDetails = DB::table('gds_order_invoice')
                    ->leftJoin('gds_invoice_items as items', 'items.gds_order_invoice_id', '=', 'gds_order_invoice.gds_order_invoice_id')
                    ->where('gds_order_invoice.order_id', $orderId)
                        ->get();
                if (!empty($invoiceDetails))
                {
                    foreach ($invoiceDetails as $invoice)
                    {
                        $temp = array();
                        $temp['invoice_data'] = $invoice;
//                        echo "<pre>";print_R($invoice);die;
                        $productDetails = DB::table('gds_invoice_items')
                                ->join('gds_order_invoice as invoice', 'invoice.gds_order_invoice_id', '=', 'gds_invoice_items.gds_order_invoice_id')
                                ->join('products', 'products.product_id', '=', 'gds_invoice_items.product_id')
                                ->where('invoice.order_id', $orderId)
                                ->select('products.product_id', 'products.name as product_name', 
                                        'products.material_code', 'gds_invoice_items.qty', 
                                        'gds_invoice_items.price')
                                ->get();
                        //                   echo "<pre>";print_R($productDetails);die;
                        $temp['product_data'] = $productDetails;
                        $responseData[] = $temp;
                    }
                }
            } else
            {
                return 'No location';
            }
            return $responseData;
        } catch (Exception $ex)
        {
            \Log::info($ex->getTraceAsString());
            return $ex->getTraceAsString();
        }
    }

    public function getAllChannelIds()
    {
        try
        {
            $channelIds = DB::table('Channel')
                    ->select(DB::raw('group_concat(channel_id) as channel_ids'))->first();
            if(!empty($channelIds))
            {
                $channelIds = $channelIds->channel_ids;
            }
            if($channelIds == '')
            {
                $channelIds = 1;
            }
            return $channelIds;
        } catch (\ErrorException $ex) {
            \Log::info($ex->getMessage());
        }
    }
    
    public function updateOrderDetails($data)
    {
        try
        {
            \Log::info(__METHOD__.' => '.__LINE__);
            \Log::info($data);
            $channelId = isset($data['channel_id']) ? $data['channel_id'] : 0;
            $statusId = isset($data['status_id']) ? $data['status_id'] : 0;
            $gdsOrderId = isset($data['gds_order_id']) ? $data['gds_order_id'] : 0;
            $gdsOrderMessage = isset($data['message']) ? $data['message'] : 0;
            $tpAttributes = isset($data['tp_attributes']) ? $data['tp_attributes'] : array();
            if($statusId != 17003)
            {
                $updateData['order_status_id'] = $statusId;
                $whereConditions['gds_order_id'] = $gdsOrderId;
                if($channelId)
                {
                    $whereConditions['channel_id'] = $channelId;
                }
                DB::table('gds_orders')->where($whereConditions)->update($updateData);                
            }else{
                $mfgId = $data['mfgID'];
                $locationId = $data['location_id'];
            		$module_id = trim($data['module_id']);
            		$access_token = trim($data['access_token']);
                $tpData = isset($data['tp_details']) ? json_decode($data['tp_details'], true) : array();
            		$child_listJson = isset($tpData['child_list']) ? $tpData['child_list'] : array();
            		$child_listArray = $child_listJson;
            //		echo "<pre/>";print_r($child_list);exit;
            //		$new_pallet = trim($tpData['new_pallet']);
            //		$stock_transfer = trim($tpData['stock_transfer']);
            		$tp = trim($tpData['tp']);
            		//$srcLocationId = trim($tpData['srcLocationId'));
            		$destLocationId = isset($tpData['destLocationId']) ? trim($tpData['destLocationId']) : 0;
            		$transitionId = isset($tpData['transitionId']) ? trim($tpData['transitionId']) : 0;
            		$transitionTime = isset($tpData['transitionTime']) ? trim($tpData['transitionTime']) : date('Y-m-d H:i:s');
            		$tpDataMapping = isset($tpData['tpDataMapping']) ? trim($tpData['tpDataMapping']): '';
            		$pdfFileName = isset($tpData['pdfFileName']) ? trim($tpData['pdfFileName']) : '';
            		$pdfContent = isset($tpData['pdfContent']) ? trim($tpData['pdfContent']) : '';
            		$sapcode = isset($tpData['sapcode']) ? trim($tpData['sapcode']): '';
                            
                $palletsArray=array();
                $productsArray=$child_listArray;
                \Log::info(__METHOD__.' => '.__LINE__);
//                $productsArray=array();
//                foreach ($child_listArray as $key => $value)
//                {
//                    $child_list = explode(',', $value['ids']);
//                    $weight = $value['weight'];
//                    $totweight = $totweight + $weight;
//                    $i = 0;
//                    foreach ($child_list as $key1 => $val1)
//                    {
//                        $getPallet = DB::Table('eseal_' . $mfgId)
//                                ->where(array('primary_id' => $val1, 'level_id' => 0))
//                                ->pluck('parent_id');
//                        if (in_array($getPallet, $palletsArray))
//                        {
//                            
//                        } else
//                        {
//                            $palletsArray[] = $getPallet;
//                        }
//                        $pallet_weight = DB::Table('eseal_' . $mfgId)
//                                ->where(array('primary_id' => $getPallet, 'level_id' => 8))
//                                ->pluck('pkg_qty');
//                        $new_weight = $pallet_weight - $weight;
//                        //return $new_weight;
//                        DB::table('eseal_' . $mfgId)
//                                ->where('primary_id', $getPallet)
//                                ->update(['pkg_qty' => $new_weight]);
//
//                        $pres_pallet_weight = DB::Table('eseal_' . $mfgId)
//                                ->where(array('primary_id' => $getPallet, 'level_id' => 8))
//                                ->pluck('pkg_qty');
//                        if ($pres_pallet_weight == 0)
//                        {
//                            DB::table('eseal_' . $mfgId)
//                                    ->where('primary_id', $getPallet)
//                                    ->update(['bin_location' => 'NULL']);
//                        }
//                        $productsArray[] = $val1;
//                    }
//                }
                $ids = implode(',',$productsArray);
                $request = \Request::create('scoapi/SyncStockOut', 'POST', 
                        array('module_id'=> intval($module_id),
                            'access_token'=>$access_token,
                            'ids'=>$ids,
                            'codes'=>$tp,
                            'srcLocationId'=>$locationId,
                            'destLocationId'=>$destLocationId,
                            'transitionTime'=>$transitionTime,
                            'transitionId'=>$transitionId,
                            'tpDataMapping'=>$tpDataMapping,
                            'pdfContent'=>$pdfContent,
                            'pdfFileName'=>$pdfFileName,
                            'sapcode'=>$sapcode));
            }
            if($statusId > 0)
            {
                $fetchOrderStatusString = DB::table('Channel_order_status')->where('master_lookup_id', $statusId)-pluck('status_value');
                $getChannelOrderId = DB::table('gds_orders')->where('gds_order_id', $gdsOrderId)->pluck('channel_order_id');
                $updateChannelOrders = DB::table('Channel_orders')->update(['order_status' => $fetchOrderStatusString])
                ->where(['channel_id' => $channelId, 'channel_order_id' => $getChannelOrderId]);
            }
            return 'Updated sucessfully';
        } catch (Exception $ex) {
            \Log::info($ex->getTraceAsString());
            return $ex->getTraceAsString();
        }
    }
}