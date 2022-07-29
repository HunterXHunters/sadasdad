<?php

use Central\Repositories\FlipkartRepo;
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

Class FlipkartdeveloperController extends BaseController{
  
    var $FlipkartObj;
   
    public function __construct(FlipkartRepo $FlipkartObj) {
    
        $this->FlipkartRepoObj = $FlipkartObj;
       
    }
  
 
     
    
  public function UpdateItem(){

        try{

         $getupdate=$this->FlipkartRepoObj->getUpdateItem();  
        
        
         $product_array=array();
         //return $getupdate;
          
          foreach($getupdate as $arr)
          {
            
          
           $product_array['attributeValues']['mrp']=$arr->mrp;
           $product_array['attributeValues']['selling_price']=$arr->sellingprice;
          $json=json_encode($product_array);
          $arr->api_name="UpdateItem";

          $arr->url=$arr->url.'skus/listings/'.$arr->listing_id;

          $flipApis = new FlipkartApiController($this->FlipkartRepoObj);
          
         $message[] = $flipApis->UpdateItem($json,$arr->listing_id,$arr->api_name,$arr->url);
          
          
          /*$request = Request::create('Flipkartapis/UpdateItem/'.$json.'/'.$arr->listing_id.'/'.$arr->api_name.'/'.$arr->url.'/', 'GET',array());
          $message[]=Route::dispatch($request)->getContent(); */ 
          
          }
           
      }
        catch(Exception $e){
            $message=$e->getMessage();
        }
       return Response::json(['Message'=>$message]);
     }
     public function UpdateInventory(){

        try{
         $getupdate=$this->FlipkartRepoObj->get_updated_qty();  
    
         $product_array=array();

          foreach($getupdate as $arr)
          {

           $product_array['attributeValues']['stock_count']=$arr->stock_count;
          
          $json=json_encode($product_array);
          $arr->api_name="UpdateInventory";

          //return $arr->listing_id;

           $flipApis = new FlipkartApiController($this->FlipkartRepoObj);
           
         $message[] = $flipApis->UpdateInventory($json,$arr->listing_id,$arr->api_name,$arr->url);
         // $request = Request::create('Flipkartapis/UpdateInventory/'.$json.'/'.$arr->listing_id.'/'.$arr->api_name.'/', 'GET',array());
           
         // $message[]=Route::dispatch($request)->getContent();  
          
          }
         
      }
        catch(Exception $e){
            $message=$e->getMessage();
        }
      return Response::json(['Message'=>$message]);
     }


     public function GetProduct(){
      try{
      $getproduct=$this->FlipkartRepoObj->getitem();  
      
        $product_array=array();
          //print_r($getproduct);exit;
          foreach($getproduct as $arr)
          {
           
          $arr->api_name= 'GetProduct';
          //return $arr->url;

           $flipApis = new FlipkartApiController($this->FlipkartRepoObj);
         
         $message[] = $flipApis->GetProduct($arr->SKUID,$arr->api_name,$arr->url);
          
         /*$request = Request::create('Flipkartapis/GetProduct/'.$arr->SKUID.'/'.$arr->api_name.'/', 'GET',array());
          $message[]=Route::dispatch($request)->getContent();*/ 
          
          }
        }
         catch(Exception $e){

           $message=$e->getMessage();
        }
        return $message;
      }
      

      public function GetOrders(){
        try{
          //$role='Seller';
          $getorders=$this->FlipkartRepoObj->getorders();
          //print_r($getorders);exit;
         //$from_date=date('Y-m-d').'T00:00:00Z';
          $from_date='2015-11-17T00:00:00Z';
          //$to_date= '2015-10-1T00:00:00Z'
          $to_date=date('Y-m-d')."T".date('H:i:s').".000Z";
          //return $to_date;
          //return $getorders;
           // return date("Y-m-d h:i:s");
            $product_array=array();
            foreach($getorders as $arr){

           $product_array['filter']['orderDate']['fromDate']=$from_date;
           $product_array['filter']['orderDate']['toDate']=$to_date;
           
           $url=$arr->url.'orders/search';

           $json=json_encode($product_array);
           
           //print_r($json);exit;
          $flipApis = new FlipkartApiController($this->FlipkartRepoObj);
          
          $message = $flipApis->GetOrders($json,$url);
        }
      }
        catch(Exception $e){
            $message=$e->getMessage();

        }
      return Response::json(['Message'=>$message]);
      }


/* public function GetPreviousOrders(){
        try{
          $getorders=$this->FlipkartRepoObj->getpreviousorders();
        // $from_date=date('Y-m-d').'T00:00:00Z';
         $from_date='2015-10-5T00:00:00Z';
          //$to_date= '2015-10-1T00:00:00Z'
          $to_date=date('Y-m-d')."T".date('H:i:s').".000Z";
           // return date("Y-m-d h:i:s");
            $product_array=array();
            foreach($getorders as $arr){

           $product_array['filter']['orderDate']['fromDate']=$from_date;
           $product_array['filter']['orderDate']['toDate']=$to_date;
           
           $url=$arr->url.'orders/search';
           //return $arr->fromDate;
           //print_r($getorders);exit;

           $json=json_encode($product_array);
           
           //print_r($json);exit;
          $flipApis = new FlipkartApiController($this->FlipkartRepoObj);
          
          $message = $flipApis->GetOrders($json,$url);
        }
      }
        catch(Exception $e){
            $message=$e->getMessage();

        }
      return Response::json(['Message'=>$message]);
      }
*/

      public function Vieworder(){
      try{
      $vieworderdet=$this->FlipkartRepoObj->view_order_details();  
      
       // $product_array=array();

          foreach($vieworderdet as $arr)
          {
        //  $arr->api_name= 'Vieworder';

         $arr->url=$arr->url.'orders/'.$arr->channel_order_itemid;
        

          //$url=$arr->url.'/orders';

           $flipApis = new FlipkartApiController($this->FlipkartRepoObj);
         
      $message[] = $flipApis->Vieworder($arr->channel_order_itemid,$arr->url);
          // $message[] = $flipApis->Vieworder($json,$url);
          

          }
        }
         catch(Exception $e){

           $message=$e->getMessage();
        }
        return $message;
      }





public function Cancelorder($channel_order_itemid=''){
 // public function Cancelorder($cancel_order_id=''){
      try{

      $cancelord=$this->FlipkartRepoObj->cancel_order();  

      //$cancel_order_itemid = $val->erp_order_id;
      //$cancel_order_itemid='4402919287504001';
       $cancel_reason='not_enough_inventory';

       $product_array=array();

        foreach($cancelord as $arr){
          $product_array = array();
         // print_r($cancel_order_itemid);
          $product_array[0]['orderItemId']=$channel_order_itemid;
          $product_array[0]['reason']=$cancel_reason;

          $url=$arr->url.'orders/cancel';  
         /// print_r($product_array);exit;
          $json=json_encode($product_array);

      $flipApis = new FlipkartApiController($this->FlipkartRepoObj);
    
      //$message[] = $flipApis->Cancelorder($arr->$cancel_order_itemid,$json,$url);
       $message = $flipApis->Cancelorder($json,$url);
  }
}
   catch(Exception $e){

           $message=$e->getMessage();
        }
        return $message;   

      }

public function Readytodispatch(){

      try{

        $getproduct=$this->FlipkartRepoObj->ready_to_dispatch();  

          $product_array=array();

          foreach($getproduct as $arr)
          { 
           // print_r($arr->channel_order_item_id);exit;

          $product_array['orderItems'][0]['orderItemId']=$arr->channel_order_item_id;
          $product_array['orderItems'][0]['quantity']=$arr->quantity;

          $url=$arr->channel_url.'orders/dispatch';
          //$product_array['orderItemId']=$arr->dispatch_order_itemid;

           $json=json_encode($product_array);
          //print_r($json);exit;
           $flipApis = new FlipkartApiController($this->FlipkartRepoObj);
           $message[] = $flipApis->Readytodispatch($json,$url);       
         /*$request = Request::create('Flipkartapis/GetProduct/'.$arr->SKUID.'/'.$arr->api_name.'/', 'GET',array());
          $message[]=Route::dispatch($request)->getContent();*/ 
          }
        }
         catch(Exception $e){

           $message=$e->getMessage();
        }
        return $message;
      }

public function Packorder(){
      try{

       $getproduct=$this->FlipkartRepoObj->Packorder(); 

        $invoiceDate=date('Y-m-d');
        $taxRate='20';
        $invoiceNumber="INOO4";

        $product_array=array();

          foreach($getproduct as $arr)
          {

         $product_array['orderItems'][0]['orderItemId']=$arr->channel_order_item_id;
         $product_array['orderItems'][0]['taxRate']=$taxRate;
         $product_array['orderItems'][0]['invoiceNumber']=$invoiceNumber;
         $product_array['orderItems'][0]['invoiceDate']=$invoiceDate;

         $url=$arr->channel_url.'v2/orders/labels';

           $json=json_encode($product_array);
           //print_r($json);exit;

           $flipApis = new FlipkartApiController($this->FlipkartRepoObj);
           $message[] = $flipApis->Packorder($json,$url);
          
          }
        }
         catch(Exception $e){

           $message=$e->getMessage();
        }
        return $message;
      }


}