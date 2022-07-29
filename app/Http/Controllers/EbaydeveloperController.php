<?php

use Central\Repositories\ApiRepo;
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

Class EbaydeveloperController extends BaseController{
  
    var $ApiObj;
   
    public function __construct(ApiRepo $ApiObj) {
    
        $this->ApiRepoObj = $ApiObj;
        
    }
  
 
     public function AddItem(){
     
     try{
          
      
      $products=$this->ApiRepoObj->getGdsProducts();
      
      $baseurl= URL::asset('/uploads/products/');
      
      $product_array=array();
     
      
      foreach($products as $addproducts){
       
       $get_add_inventory = $this->ApiRepoObj->getAddInventory($addproducts->product_id);
       
       $get_xml_array = $this->ApiRepoObj->getXmlArray($get_add_inventory ,$addproducts->channel_category_id,$addproducts->upc,$addproducts->location_address,$addproducts->city);
      
       $product_array['Item'] = $get_xml_array;        
       
       $product_array['Item']['Title'] = $addproducts->Title;
       
       $product_array['Item']['Description'] = $addproducts->Description;
       
       $product_array['Item']['StartPrice'] = $addproducts->StartPrice;

       $product_array['Item']['PictureDetails']['PictureURL'] = $baseurl.'/'.$addproducts->PictureURL;
       
       $product_attributes=explode(',',$addproducts->product_attributes);
       
       $product_attributes=json_encode($product_attributes);
       
       $json=json_encode($product_array);
         
       $json=urlencode($json);
        
       $ebayApis = new EbayApiController($this->ApiRepoObj);
       print_r($ebayApis);exit;        
       $message[] = $ebayApis->AddItem($json,$addproducts->product_id,$product_attributes);
       
      }
      
    }
        catch(Exception $e){
            $message=$e->getMessage();
        }
     return Response::json(['Message'=>$message]);
  }  
    
     public function UpdateItem(){
      try{
         
          $products=$this->ApiRepoObj->getUpdateItem();
          
          $baseurl= URL::asset('/uploads/products/');

          $product_array=array();
          
          foreach($products as $updateproducts){

          $product_array['ErrorLanguage'] = "en_IN";
          $product_array['WarningLevel'] = "High";
          $product_array['Item']['ItemID']=$updateproducts->ItemID;
          $product_array['Item']['ItemSpecifics']="";
          $product_array['Item']['Description']=$updateproducts->Description;
          $product_array['Item']['Title'] = $updateproducts->Title;
          $product_array['Item']['PictureDetails']['PictureURL'] = $baseurl.'/'.$updateproducts->PictureURL;
          $product_array['Item']['StartPrice'] = $updateproducts->StartPrice;
       
         
         $product_attributes=explode(',',$updateproducts->product_attributes);
         
         $product_attributes=json_encode($product_attributes);
        
         $json=json_encode($product_array);
         
         $ebayApis = new EbayApiController($this->ApiRepoObj);
         
         $message[] = $ebayApis->UpdateItem($json,$updateproducts->ItemID,$product_attributes);
         
         }
         
          }       
   
        
        catch(Exception $e){
            $message=$e->getMessage();
        }
       return Response::json(['Message'=>$message]);
     }

      public function UpdateInventory(){

        try{

         $getinventory=$this->ApiRepoObj->get_updated_qty();  
         
         
         $product_array=array();
         
          foreach($getinventory as $arr)
          {
           
           $get_add_inventory = $this->ApiRepoObj->getAddInventory($arr->product_id); 
           $product_array['ErrorLanguage']="en_IN";
           $product_array['WarningLevel']="High";
           $arr->Quantity=$get_add_inventory;
           $product_array['Item']=$arr;
           
          $json=json_encode($product_array);
       
          $ebayApis = new EbayApiController($this->ApiRepoObj);
         
          $message[] = $ebayApis->UpdateInventory($json,$arr->ItemID);
          
          }
           
      }
        catch(Exception $e){
            $message=$e->getMessage();
        }
       return Response::json(['Message'=>$message]);
     }

   /*  public function placeorder(){
     
     try{
            

            $item_id="110169961119";
            
            $Quantity="1";
            
            $maxprice="500.00";
            
            $ip_address='111.93.24.118'; 

            $json_array=array();
          
            $json_array['EndUserIP'] = $ip_address;
            $json_array['ItemID'] = $item_id;
            $json_array['Offer']['Action']="Purchase";
            $json_array['Offer']['Quantity']=$Quantity;
            $json_array['Offer']['MaxBid']=$maxprice;

            $json=json_encode($json_array);
            $request = Request::create('/ebayapis/placeorder/'.$json.'/', 'GET',array());
            return Route::dispatch($request)->getContent();
     }
     catch(Exception $e){
        $message=$e->getMessage();
     }
     }*/

   
   public function getorders(){
     try{
            $role='Seller';
            $from_date= date('Y-m-d')."T00:00:00.000Z";
            $to_date = date('Y-m-d')."T".date('H:i:s').".000Z";
            

            $json_array=array();
              
            $json_array['CreateTimeFrom'] = $from_date;
            $json_array['CreateTimeTo'] = $to_date;
            $json_array['OrderRole']= $role;
            $json_array['OrderStatus']="All";

            $json=json_encode($json_array);
             
            $request = Request::create('/ebayapis/getorders/'.$json.'/', 'GET',array());
            return Route::dispatch($request)->getContent();
        }
        catch(Exception $e){
            $message=$e->getMessage();
        }
     }

     
     public function updateorder(){
      try{
          
          $get_update_order=$this->ApiRepoObj->getUpdatedOrder();
          
          $update_order=array();
          
          foreach($get_update_order as $updated_data){
          
          $update_order['AmountPaid']= $updated_data->amount;
          $update_order['OrderID'] = $updated_data->order_id;
          $update_order['CheckoutStatus'] = $updated_data->OrderStatus;
          $update_order['PaymentMethodUsed'] = $updated_data->PaymentMethodUsed;
          $update_order['ShippingService']= $updated_data->service_name;
          $update_order['ShippingIncludedInTax'] = "true";
          $update_order['ShippingAddress']['Name'] = $updated_data->name;
          $update_order['ShippingAddress']['Street1'] = $updated_data->address1;
          $update_order['ShippingAddress']['Street2'] = $updated_data->address2;
          $update_order['ShippingAddress']['CityName'] = $updated_data->city;
          $update_order['ShippingAddress']['StateOrProvince'] = $updated_data->state;
          $update_order['ShippingAddress']['Country'] = 'IN';
          $update_order['ShippingAddress']['PostalCode'] = $updated_data->pincode;   
          
            
          $json=json_encode($update_order);

          $ebayApis = new EbayApiController($this->ApiRepoObj);
         
          $message[] = $ebayApis->updateorder($json,$updated_data->ItemID,$updated_data->order_id);
          
      }
      }
      catch(Exception $e){
          $message=$e->getMessage();
      }

       return Response::json(['Message'=>$message]);
     }


    
    
    public function addDispute($data=''){
     
     try{
         
         if(!empty($data))
          $get_disputes=json_decode($data);
          else
          $get_disputes=$this->ApiRepoObj->getdispute();  
          //print_r($get_disputes);exit;
          if(!empty($data)){

          $size=sizeof($get_disputes);
          $j=0;
          
          foreach($get_disputes as $value){
            $get_disputes[$j]->raise_dispute=$size;
            $j++;
          }
        }
         
          
          $finalarray = array();

          foreach($get_disputes as $value){
            
            $ItemID = $value->item_id;
              
            $TransactionID = $value->transaction_id;
            $DisputeReason = $value->dispute_reason;
            $DisputeExplanation = $value->dispute_explanation;

            $json_array=array();
              
            $json_array['ItemID'] = $ItemID;
            $json_array['TransactionID'] = $TransactionID;
            $json_array['DisputeReason']=$DisputeReason;
            $json_array['DisputeExplanation']=$DisputeExplanation;
             
            if($value->raise_dispute==1){
            
            $json=json_encode($json_array);
            
            $ebayApis = new EbayApiController($this->ApiRepoObj);
            
            $message = $ebayApis->addDispute($json,$value->order_id,$ItemID,$DisputeExplanation);
            //return $message;
            }
            else{

              $finalarray[]=$json_array;
            }
           }

            if(isset($finalarray)){
            
            $order_id=DB::table('Channel_order_details')->where('channel_item_id',$finalarray[0]['ItemID'])->pluck('order_id');
            
            $json=json_encode($finalarray);
            
            $ebayApis = new EbayApiController($this->ApiRepoObj);
            
            $message = $ebayApis->addDispute($json,$order_id,$ItemID,$DisputeExplanation);
            
            }
      }
      
      catch(Exception $e){
        $message=$e->getMessage();
      }
       return Response::json(['Message'=>$message]);
    }
    
    public function addDisputeResponse(){
      try{
           
            
            $getdisputes = $this->ApiRepoObj->getraisedDisputes();
           
            foreach($getdisputes as $value){
            
            if($value->Days>7){
            
            $DisputeActivity = 'CameToAgreementNeedFVFCredit';
            $DisputeID = $value->dispute_id;

            $json_array=array();
              
            $json_array['DisputeActivity'] = $DisputeActivity;
            $json_array['DisputeID'] = $DisputeID;
            
            $json=json_encode($json_array);
            
            $ebayApis = new EbayApiController($this->ApiRepoObj);
            
            $message[] = $ebayApis->addDisputeResponse($json);
           
           }
         }
      }
      catch(Exception $e){
        $message=$e->getMessage();
      }
    return Response::json(['Message'=>$message]);
    }
    
    public function getUserDisputes(){
      try{

            $DisputeFilterType='AllInvolvedDisputes';
            $DisputeSortType='DisputeCreatedTimeDescending';
            

            $json_array=array();
              
            $json_array['DisputeFilterType'] = $DisputeFilterType;
            $json_array['DisputeSortType'] = $DisputeSortType;
            $json_array['Pagination']['EntriesPerPage'] = 5;
            $json_array['Pagination']['PageNumber'] = 1;
            
            $json=json_encode($json_array);
                          
            $ebayApis = new EbayApiController($this->ApiRepoObj);
         
            $message[] = $ebayApis->getUserDisputes($json);
      }
      catch(Exception $e){
        $message=$e->getMessage();
      }
    return Response::json(['Message'=>$message]);
    }

 /* public function endListing(){
      try{
          $json = '{
        "ItemID": "221914494371",
        "EndingReason": "NotAvailable"
          }';

           $json=json_encode($json);
           
           $request = Request::create('/ebayapis/endListing/'.$json.'/', 'GET',array());
           return Route::dispatch($request)->getContent(); 
      }
      catch(Exception $e){
         $message=$e->getMessage();
      }
    }*/

     public function relistProduct(){
      
      try{
          
          $getproducts = $this->ApiRepoObj->getrelistproducts();
         
          $relist_product=array();
          
          foreach($getproducts as $products){
          
          $relist_product['Item']['ItemID']=$products->ItemID;

          $json=json_encode($relist_product);

          $ebayApis = new EbayApiController($this->ApiRepoObj);
         
          $message[] = $ebayApis->relistProduct($json,$products->product_id,$products->channel_id);  
      }
    }
      catch(Exception $e){
        $message=$e->getMessage();
      }
    return Response::json(['Message'=>$message]);
    }

    
    public function gdsDashboard(){
      
      $currentUserId = \Session::get('userId');
      
      $manufacturerDetails = DB::table('users')->where('user_id', $currentUserId)->pluck('customer_id');
      
      $order_data = DB::table('Channel_order_details as cod')
                    ->leftJoin('Channel_orders as co','co.channel_order_id','=','cod.order_id')
                    ->leftJoin('products as pd','pd.product_id','=','cod.sco_item_id')
                    ->select(DB::raw('count(co.order_id) as total_orders,ROUND(sum(co.total_amount),0) as total'));
      if(!empty($manufacturerDetails)){
      $order_data = $order_data->where('pd.manufacturer_id',$manufacturerDetails);
      }
      $order_data=$order_data->get(); 
      
      $sold_orders = DB::table('Channel_order_details as cod')
                     ->leftJoin('Channel_orders as co','co.channel_order_id','=','cod.order_id')
                     ->leftJoin('products as pd','pd.product_id','=','cod.sco_item_id')
                     ->select(DB::raw('count(*) as sold_orders'))
                     ->whereNotIn('co.channel_order_status', array('CANCELLED', 'Canceled'));
      if(!empty($manufacturerDetails)){
      $sold_orders = $sold_orders->where('pd.manufacturer_id',$manufacturerDetails);
      }
      $sold_orders=$sold_orders->get();      
      
      $canceled_orders = DB::table('Channel_order_details as cod')
                        ->leftJoin('Channel_orders as co','co.channel_order_id','=','cod.order_id')
                        ->leftJoin('products as pd','pd.product_id','=','cod.sco_item_id')
                        ->select(DB::raw('count(*) as canceled_orders'))
                        ->whereIn('co.channel_order_status', array('CANCELLED', 'Canceled'));
     if(!empty($manufacturerDetails)){
     $canceled_orders = $canceled_orders->where('pd.manufacturer_id',$manufacturerDetails);
      }
      $canceled_orders=$canceled_orders->get();
      
      
  
     
     $channel_graphs = DB::table('Channel_order_details as cod')
                      ->leftJoin('Channel_orders as co','co.channel_order_id','=','cod.order_id')
                      ->leftJoin('products as pd','pd.product_id','=','cod.sco_item_id')
                      ->leftJoin('Channel as ch','ch.channel_id','=','co.channel_id')
                      ->select(DB::raw('count(co.channel_order_id) as orders,MONTH(co.order_date) as month'),
                      'co.channel_id','ch.channnel_name')
                      ->groupBY('co.channel_id');
     
     if(!empty($manufacturerDetails)){
     $channel_graphs = $channel_graphs->where('pd.manufacturer_id',$manufacturerDetails);
      }
      $channel_graphs=$channel_graphs->get();

      
     $channel_order_count = DB::table('Channel_orders')
                            ->leftJoin('Channel','Channel.channel_id','=','Channel_orders.channel_id')
                            ->select(DB::raw('count(Channel_orders.order_id) as channel_order_count,Channel_orders.channel_id'),'Channel.channnel_name')    
                            ->groupBY('Channel_orders.channel_id')
                            ->get();
    
    
    
    $channel_skus = DB::table('Channel_product_add_update')
                     ->leftJoin('Channel','Channel.channel_id','=','Channel_product_add_update.channel_id') 
                     ->leftJoin('product_inventory','product_inventory.product_id','=','Channel_product_add_update.product_id')                 
                     ->leftJoin('products','products.product_id','=','Channel_product_add_update.product_id') 
                     ->select(DB::raw('sum(product_inventory.available_inventory) as sku_count,products.name'),'products.product_id','products.manufacturer_id')
                     ->where('Channel.channnel_name','eBay')
                     ->groupBY('products.product_id')
                     ->take(20)
                     ->get();

   
    
    $products_view = DB::table('Channel_order_details as cod')
                     ->leftJoin('products as pd','pd.product_id','=','cod.sco_item_id')
                     ->leftJoin('Channel as ch','ch.channel_id','=','cod.channel_id')
                     ->leftJoin('Channel_orders as co','co.channel_order_id','=','cod.order_id')
                     //->leftJoin('Channel_orders as co','co.channel_order_id','=','cod.order_id')
                     ->select('pd.product_id','pd.name','co.channel_order_id','pd.description','pd.mrp','pd.image','ch.channnel_name')
                     ->orderBy('co.order_date','desc');
     if(!empty($manufacturerDetails)){
     $products_view = $products_view->where('pd.manufacturer_id',$manufacturerDetails);
      }
      $products_view = $products_view->take(4)->get();
                     
                                       
    
    if(!empty($manufacturerDetails)){
    $subscribed_channel = DB::table('manf_channels as mc')
                          ->leftJoin('Channel as ch','ch.channel_id','=','mc.channel_id')
                          ->where('mc.manf_id',$manufacturerDetails)->get();
      }
    else{
      $subscribed_channel = DB::table('Channel')
                          ->get();
      }
    
   

    $colour_array = array();
    
    $colour_array[0] = "red";
    $colour_array[1] = "green";
    $colour_array[2] = "yellow";
    $colour_array[3] = "aqua";
    $colour_array[4] = "light-blue";
    $colour_array[5] = "gray";
    $colour_array[6] = "red";
    $colour_array[7] = "green";
    $colour_array[8] = "yellow";
    $colour_array[9] = "aqua";
    $colour_array[10] = "light-blue";
    $colour_array[11] = "gray"; 
    $colour_array[12] = "red";
    $colour_array[13] = "green";
    $colour_array[14] = "yellow";
    $colour_array[15] = "aqua";
    
    

    
    $min_and_max_dates = DB::table('Channel_order_details as cod')
                         ->leftJoin('products as pd','pd.product_id','=','cod.sco_item_id') 
                         ->leftJoin('Channel_orders as co','co.channel_order_id','=','cod.order_id')  
                         ->select(DB::raw('date(min(co.order_date)) as minimum_date,date(max(co.order_date)) maximum_date'))->get();

     
     foreach($channel_graphs as $key=>$value){
        
        if($value->channnel_name=="eBay"){
          $ebay_count[$value->month] = $value->orders;
         }
        
        if($value->channnel_name=="Flipkart"){
          $flipkart_count[$value->month] = $value->orders;
          }
        
        if($value->channnel_name=="amazon"){
          $amazon_count[$value->month] = $value->orders;
          }
      
      }
     
     if(empty($amazon_count))
      $amazon_count = '';
     if(empty($ebay_count))
      $ebay_count = '';
      if(empty($flipkart_count))
      $flipkart_count='';  
      
      for($i=1;$i<sizeof($channel_skus);$i++){
        
      $ebay_valid = (isset($ebay_count[$i]))?'true':'false';
      $flipkart_valid = (isset($flipkart_count[$i]))?'true':'false';
      $amazon_valid = (isset($amazon_count[$i]))?'true':'false'; 
       
       if($ebay_valid=='false')
       {
        $ebay_count[$i] ='';
       }
      if($flipkart_valid=='false')
       {
        $flipkart_count[$i] ='';
       }
       if($amazon_valid=='false')
       {
        $amazon_count[$i] ='';
       }

      }
     

      return View::make('orders.gdsdashboard')->with(array('order_data'=>$order_data,'sold_orders'=>$sold_orders,'channel_graphs'=>$channel_graphs,'ebay_count'=>json_encode($ebay_count),
        'flipkart_count'=>json_encode($flipkart_count),'amazon_count'=>json_encode($amazon_count),'channel_order_count'=>$channel_order_count,'channel_skus'=>$channel_skus,'count_channel_skus'=>json_encode($channel_skus),'canceled_orders'=>$canceled_orders,'min_and_max_dates'=>$min_and_max_dates,'colour_array'=>$colour_array,'products_view'=>$products_view,'subscribed_channel'=>$subscribed_channel));
    
    }

    public function addScoProducts(){
   
    try{

      $gds_enabled = $this->ApiRepoObj->gdsEnabled(); 
     
      $message = $this->ApiRepoObj->channelInsert($gds_enabled);
      
     
      }
      
    catch(Exception $e){
        $message=$e->getMessage();
      }
    
    return Response::json(['Message'=>$message]);
    
    }
 
   public function UpdateAllOrders($order_id){
      try{
        
        $order_status_id = Input::get('order_status_id');
        
        $status_value = DB::table('Channel_order_status')->where('status_id',$order_status_id)->pluck('status_value');
        
        $channel_name = DB::table('Channel_orders as co')
                        ->leftJoin('Channel as ch','ch.channel_id','=','co.channel_id')
                        ->where('co.channel_order_id',$order_id)
                        ->pluck('ch.channnel_name');
        
        /*$order_details  = DB::table('Channel_orders as co')
                          ->leftJoin('Channel_order_details as cod','co.channel_order_id','=','cod.order_id')
                          ->leftJoin('Channel_order_payment as cop','cop.order_id','=','co.channel_order_id')
                          ->leftJoin('Channel_address as cad','cad.channel_id','=','co.channel_id')
                          ->leftJoin('Channel as ch','ch.channel_id','=','co.channel_id')
                          ->leftJoin('Channel_orders_shipping_address as cosa','cosa.order_id','=','co.channel_order_id')
                          ->where('co.channel_order_id',$order_id)
                          ->get();
*/
             
        $order_product_details = DB::table('Channel_order_details as cod')
                                ->leftJoin('Channel_product_add_update as cpau','cpau.channel_product_key','=','cod.channel_item_id') 
                                 ->where('order_id',$order_id)
                                 ->get();
        
        
                  
        
        $product_array=array();

        foreach($order_product_details as $details){
        
        $product_details=  DB::table('products')
                            ->where('product_id',$details->product_id)
                            ->get();
                           
         $product_array['product_name'] = $product_details[0]->name;                   
         $product_array['quantity'] = $details->quantity;
         $product_array['price'] = $details->price;
         $product_array['subtotal'] = $details->price*$details->quantity;
         $product_array['tax'] = $details->tax;
         $product_array['total'] = $product_array['subtotal']+$product_array['tax'];
         $eseal_order_products[] = $product_array;
        
        }
                         
        
        if($channel_name=="eBay"){

         if($status_value!="Shipped"){

         DB::table('Channel_order_payment')
         ->where('order_id',$order_id)
         ->update(array('payment_status'=>$status_value));
         
         DB::table('Channel_order_details')
         ->where('order_id',$order_id)
         ->update(array('order_status'=>1));
         
         
         if($status_value!="CancelOrder"){
        
         $updateOrder=$this->updateOrder();
        
         }
         else{

         $cancel_exists=DB::table('Channel_order_disputes')->where('order_id',$order_id)->get();
         
         if(empty($cancel_exists)){

         DB::table('Channel_order_disputes')
         ->insert(['Order_id'=>$order_id,
                    'dispute_status'=>"WaitingForBuyerResponse",
                    'dispute_reason'=>"TransactionMutuallyCanceled",
                    'dispute_explanation'=>"SellerRanOutOfStock",
                    'raise_dispute'=>1]); 
        
        $result=$this->addDispute();
        
        }

        }
      }
      else{

         $updateOrder=$this->CompleteOrder();
        // print_r($updateOrder);exit;
      }
      }
      elseif($channel_name=="amazon" || $channel_name=="Flipkart"){
        
        if($status_value=="CancelOrder"){
         
         $channel_order_item_id =  DB::table('Channel_order_details')
                                    ->where('order_id',$order_id)
                                    ->pluck('channel_order_item_id');
          
         $cancel_reason = "BuyerCanceled"; 

        if($channel_name=="amazon")
        $url = URL::asset('/amazondeveloper/cancelOrder/'.$order_id.'/'.$channel_order_item_id.'/'.$cancel_reason);
        
        elseif($channel_name=="Flipkart")  
        $url = URL::asset('/Flipkartdeveloper/Cancelorder/'.$channel_order_item_id);

         $curl = curl_init();

          curl_setopt($curl,CURLOPT_URL, $url);

          curl_setopt($curl,CURLOPT_RETURNTRANSFER, TRUE);

          //curl_setopt($curl,CURLOPT_POST, sizeof($data));

          //curl_setopt($curl,CURLOPT_POSTFIELDS, $data);

          $catResult = curl_exec($curl);

          curl_close($curl);
          
        
          
         }
         if($status_value=="PACKED" || $status_value=="READY_TO_DISPATCH"){
  
         if($status_value=="PACKED")
         $url = URL::asset('/Flipkartdeveloper/Packorder');
         else
         $url = URL::asset('/Flipkartdeveloper/Readytodispatch');
          
         $curl = curl_init();

          curl_setopt($curl,CURLOPT_URL, $url);

          curl_setopt($curl,CURLOPT_RETURNTRANSFER, TRUE);

          //curl_setopt($curl,CURLOPT_POST, sizeof($data));

          //curl_setopt($curl,CURLOPT_POSTFIELDS, $data);

          $catResult = curl_exec($curl);

          curl_close($curl);
        }
      }
      
      $mailids="nikhil.kishore@esealinc.com" ;
      $custMail="nikhil.kishore@esealinc.com";
      $custName="Orient";


      \Mail::send(['html' => 'emails.gds_orders'], array('status_value' => $status_value, 'username' => 'eSeal','eseal_order_products'=> $eseal_order_products), 
         
          function($message) use ($mailids,$custMail,$custName) 
         {        
                    $message->to($custMail)->bcc($mailids)->subject('Order from '.$custName);
         });

      return Redirect::to('reportapis/index')->withFlashMessage('Your Order has been '.$status_value.'  Successfully.');
      
      }
      catch(Exception $e){
        $message=$e->getMessage();
      }
    }

    public function completeOrder(){
      try{

            $json_array=array();
            
            $complete_order_details = $this->ApiRepoObj->completeorderdetails();
           // print_r($complete_order_details);exit;
            foreach($complete_order_details as $complete){
            
            //$json_array['ItemID'] = $complete->channel_item_id;
            $json_array['OrderLineItemID'] = $complete->order_id;
            $json_array['Shipment']['ShipmentTrackingDetails']['ShippingCarrierUsed'] = "Blue Dart";
            $json_array['Shipment']['ShippedTime'] = "2015-11-19T06:40:11.000Z";
            $json_array['Shipped'] = "true";
           // $json_array['TransactionID'] = $complete->transaction_id;
            //print_r($json_array);exit;
            $json=json_encode($json_array);
                          
            $ebayApis = new EbayApiController($this->ApiRepoObj);
         
            $message[] = $ebayApis->completeOrder($json);
           
          }
      }
      catch(Exception $e){
        $message=$e->getMessage();
      }
    return Response::json(['Message'=>$message]);
    }

 
 }   