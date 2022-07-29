<?php


use Central\Repositories\ApiRepo;

//use Central\Repositories\ApiConfig;

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

Class EbayApiController extends BaseController{
    
    var $ApiObj;
    
    protected $_url;
    protected $_domain;
    protected $_auth_token;
    //protected $_dev_token;
    //protected $_app_token;
    //protected $_cert_token;
    //protected $_live_token;
    protected $_buyer_token;
    
    
    public function __construct(ApiRepo $ApiObj) {
        
        $this->ApiRepoObj = $ApiObj;
        
        $token=$this->ApiRepoObj->getUrl();
        $this->_url=$token->channel_url;
        //$this->_buyer_token='AgAAAA**AQAAAA**aAAAAA**zwK2VQ**nY+sHZ2PrBmdj6wVnY+sEZ2PrA2dj6wFk4GhDZmBoQ6dj6x9nY+seQ**l4IDAA**AAMAAA**c+QTu0MlWrXX2yrz5fbKzHY0S+dr0uxgcNcd7euZ6P10xSlJuMVi0lBYL2h77Yk3Ydn5h0JmpDofqFHRI6/zGvDh4w1iN8tdSq7yzfV0iKfIUW1eq4NQjba/VV7Z58yTZZj2IXoBsPlg/Mzc7P4FiN6VU/pDSA8ucxSjBpea9Z5hqpp91WB1XrFDbGu/M5yi2+IR/D7HdrXDlarWLM89JWeon/V6qdtN1LfIv/JPJPH4Ej1KQB5hNuUUDn7IP1kTODKm3jimDAuXPcXsFVUnzLZJlJfk69dKjJ2LBBW7JGslqjQOGIbkYvbl3t4dXnlKxDwX0m8f8QcK4L3FDcO0KVpr54lrDr/wiU1lrr/e9rIjf15fbrAUGdhY9SQYKYlKacFe/5RNNSGoQowcj0n9ZySP9tkfR9txJIGAceJEJ6H565Un7nIc33FZhOoRa8FP0IAx9gMa2e3fg3hKFlgpLCCUwrgj1fNNQcFoEsx477mRpIjrllrJ9j2+8Q8WmFSPOSLHwGwbOmYlMegZw3bH6bim9ejB2L4WGle2Wp06QSkNwf7JbPcJ79ET0qNNEFubxO/ApfZ6Ww6WRGQpmArPIWdDx7yRkAufemCHdE156LifEawWVu2KkaPCZN/GrJw0et7OTZVSWGthMtX1ldMubB9lwEk032l9lpAFJTFKqbw9suKqubJd7rVKHqAf8wo2frAlXTkdn1vr9tWn8bEDxL0W46X/E6/EyfZ161DU60Y5Rr89RpihuO97AHRB07n9';
        $this->_auth_token='AgAAAA**AQAAAA**aAAAAA**C2/2VQ**nY+sHZ2PrBmdj6wVnY+sEZ2PrA2dj6AAkIqoCZGFoQ2dj6x9nY+seQ**T/0CAA**AAMAAA**rqcWjPJF45JTJh7cEH8RMHzsl8m6bXSCMOIxLN+f16oRTyhlb1ptDZYX45l4Rball2gQBFUmVpO7flxguNjrsw+7y6bielqgLkRUPQxr8gOYS5OVdrmhKy5AWIjva8bC25ESTUgefopW/rH/jKCyiHZcYBjr+H7gYPxpFlekE05c1va4T3gCkS4LP6IGQ2SE3c6ALA/4pUEmBNmnGkKF0AkC1zhDJjZ8cSsl+Cjzb7bKyF5xjyO9ujY8NL23L4IQ6xRq5W3sZtgwHHasXc2r9NqTplpsZsfRkaOkZ6qJW30ZQ5lZ9sqinVVVmi7wreyYPoJjk0o6OemEVVS8jVgsRrepaR6RWo00CoxIzoOO9PGCvhlg0t3lIW9ZYR1bCHxyIElRzv6LTIkbCDVhPhTtXY9Ir87Dy4hhqHSwyW3Ck5FiJ6tjdtuA2eTdTbKsw/6sTL1plzg8tpNa1beoVSMKvkrfVu4vuDDO7RwS5feiIW219Th+ne1kmK+U8xc1VYc0Vvwe7DX++O3efARxHqLArbzTOCHfY46aUxsSGIbSRnaA2v9lzX4daQbDE+UYY8NGs0AdzAAr0mBAzGP3gt/8AYnIdKf75ApBZ9d82dUUHs6LVL3imNwmF72hRnFA8npL484vOZKRUGzY+DWjJbIBxkVSKh3JUe9aZFy/jLKKHWRtvo14KiaZcYhDvFWKcS47h5zwuyRi0Qct+M4GoA1Aau02XM1Et9mX17USw5PPx/jygdafcnLCNcWF8du9hmzV';
             
    }
  
    public function AddItem($input_data,$product_id,$product_attributes){
    try{
      
      $input_data=urldecode($input_data);
      $product_attributes=urldecode($product_attributes);
      $is_json=is_string($input_data) && is_object(json_decode($input_data)) ? true : false;
      $url=$this->_url;
      $url = str_replace(' ', '%20', $url);
      $apiname='AddFixedPriceItem';
      if($is_json==true)
      {
      $json_data=json_decode($input_data,true);
      $input_type='json';
      $file_contents = $this->ApiRepoObj->getXml($json_data,$apiname,$this->_auth_token,$input_type,$product_attributes);
      
      $send_curl=$this->ApiRepoObj->sendRequest($url,$file_contents,$apiname,$product_id);
      return $send_curl;  
      }
      else
      {
      $file_contents = strchr($input_data,'<?xml');
      $input_type='xml';
      $file_contents = $this->ApiRepoObj->getXml($file_contents,$apiname,$this->_auth_token,$input_type);
      if($file_contents)
      {
      $send_curl=$this->ApiRepoObj->sendRequest($url,$file_contents,$apiname);  
      }
     return $send_curl;
      }
     }
    catch(Exception $e){
         $message=$e->getMessage();
    }
   
    }
    public function UpdateItem($input_data,$item_id,$product_attributes){
    try{
     
      $is_json=is_string($input_data) && is_object(json_decode($input_data)) ? true : false;
      $url=$this->_url;
      $url = str_replace(' ', '%20', $url);
      $apiname= 'ReviseItem';
      
      if($is_json==true)
      {
      $json_data=json_decode($input_data,true);
      $input_type='json';
      $file_contents = $this->ApiRepoObj->getXml($json_data,$apiname,$this->_auth_token,$input_type,$product_attributes);
      $product_id='';
      $status=1;
      $send_curl=$this->ApiRepoObj->sendRequest($url,$file_contents,$apiname,$product_id,$item_id,$json_data,$status);
      return $send_curl;  
      }
      else
      {
      $file_contents = strchr($input_data,'<?xml');
      $input_type='xml';
      $file_contents = $this->ApiRepoObj->getXml($file_contents,$apiname,$this->_auth_token,$input_type);
      if($file_contents)
      {
      $send_curl=$this->ApiRepoObj->sendRequest($url,$file_contents,$apiname);  
      }
     return $send_curl;
      }
     }
    catch(Exception $e){
         $message=$e->getMessage();
    }
   
    }


     public function UpdateInventory($input_data,$item_id){
    try{
      
      $is_json=is_string($input_data) && is_object(json_decode($input_data)) ? true : false;
      $url=$this->_url;
      $url = str_replace(' ', '%20', $url);
      $apiname= 'ReviseItem';
      if($is_json==true)
      {
      $json_data=json_decode($input_data,true);
      $input_type='json';
      $file_contents = $this->ApiRepoObj->getXml($json_data,$apiname,$this->_auth_token,$input_type);
      $product_id='';
      $status=0;
      $send_curl=$this->ApiRepoObj->sendRequest($url,$file_contents,$apiname,$product_id,$item_id,$status);
      return $send_curl;  
      }
      else
      {
      $file_contents = strchr($input_data,'<?xml');
      $input_type='xml';
      $file_contents = $this->ApiRepoObj->getXml($file_contents,$apiname,$this->_auth_token,$input_type);
      if($file_contents)
      {
      $send_curl=$this->ApiRepoObj->sendRequest($url,$file_contents,$apiname);  
      }
     return $send_curl;
      }
     }
    catch(Exception $e){
         $message=$e->getMessage();
    }
   
    }
    
    public function placeorder($input_data){
    try{
       
      $is_json=is_string($input_data) && is_object(json_decode($input_data)) ? true : false;
      $url=$this->_url;
      $url = str_replace(' ', '%20', $url);
      $apiname='PlaceOffer';
      
      if($is_json==true)
      {
      $json_data=json_decode($input_data,true);
      $input_type='json';
      $file_contents = $this->ApiRepoObj->getXml($json_data,$apiname,$this->_buyer_token,$input_type);
      $send_curl=$this->ApiRepoObj->sendRequest($url,$file_contents,$apiname);
      return $send_curl;  
      }
      else
      {
      $file_contents = strchr($input_data,'<?xml');
      $input_type='xml';
      $file_contents = $this->ApiRepoObj->getXml($file_contents,$apiname,$this->_buyer_token,$input_type);
      if($file_contents)
      {
      $send_curl=$this->ApiRepoObj->sendRequest($url,$file_contents,$apiname);  
      }
     return $send_curl;
      }
     }
    catch(Exception $e){
         $message=$e->getMessage();
    }
   
    }

    public function completeOrder($input_data){
    try{
        
      $is_json=is_string($input_data) && is_object(json_decode($input_data)) ? true : false;
      $url=$this->_url;
      $url = str_replace(' ', '%20', $url);
      $apiname='CompleteSale';
      if($is_json==true)
      {
      $json_data=json_decode($input_data,true);
      $input_type='json';
      $file_contents = $this->ApiRepoObj->getXml($json_data,$apiname,$this->_auth_token,$input_type);
      $send_curl=$this->ApiRepoObj->sendRequest($url,$file_contents,$apiname);
      return $send_curl;  
      }
      else
      {
      $file_contents = strchr($input_data,'<?xml');
      $input_type='xml';
      $file_contents = $this->ApiRepoObj->getXml($file_contents,$apiname,$this->_auth_token,$input_type);
      if($file_contents)
      {
      $send_curl=$this->ApiRepoObj->sendRequest($url,$file_contents,$apiname);  
      }
     return $send_curl;
      }
     }
    catch(Exception $e){
         $message=$e->getMessage();
    }
   
    }

    public function getorders($input_data){
    try{
      
      $is_json=is_string($input_data) && is_object(json_decode($input_data)) ? true : false;
      $url=$this->_url;
      $url = str_replace(' ', '%20', $url);
      $apiname='GetOrders';
      if($is_json==true)
      {
      $json_data=json_decode($input_data,true);
      $input_type='json';
      $file_contents = $this->ApiRepoObj->getXml($json_data,$apiname,$this->_auth_token,$input_type);
      $send_curl=$this->ApiRepoObj->sendRequest($url,$file_contents,$apiname);
      
      return $send_curl;  
      }
      else
      {
      $file_contents = strchr($input_data,'<?xml');
      $input_type='xml';
      $file_contents = $this->ApiRepoObj->getXml($file_contents,$apiname,$this->_auth_token,$input_type);
      if($file_contents)
      {
      $send_curl=$this->ApiRepoObj->sendRequest($url,$file_contents,$apiname,$this->_dev_token,$this->_app_token,$this->_cert_token);  
      }
     return $send_curl;
      }
     }
    catch(Exception $e){
         $message=$e->getMessage();
    }
   
    }
    public function viewOrder($input_data){
    try{
      
      $is_json=is_string($input_data) && is_object(json_decode($input_data)) ? true : false;
      $url=$this->_url;
      $url = str_replace(' ', '%20', $url);
      $apiname='GetOrderTransactions';
      if($is_json==true)
      {
      $json_data=json_decode($input_data,true);
      $input_type='json';
      $file_contents = $this->ApiRepoObj->getXml($json_data,$apiname,$this->_auth_token,$input_type);
      $send_curl=$this->ApiRepoObj->sendRequest($url,$file_contents,$apiname);
      return $send_curl;  
      }
      else
      {
      $file_contents = strchr($input_data,'<?xml');
      $input_type='xml';
      $file_contents = $this->ApiRepoObj->getXml($file_contents,$apiname,$this->_auth_token,$input_type);
      if($file_contents)
      {
      $send_curl=$this->ApiRepoObj->sendRequest($url,$file_contents,$apiname,$this->_dev_token,$this->_app_token,$this->_cert_token);  
      }
     return $send_curl;
      }
     }
    catch(Exception $e){
         $message=$e->getMessage();
    }
   
    }
     public function updateorder($input_data,$item_id,$order_id){
    try{
      
      
      $is_json=is_string($input_data) && is_object(json_decode($input_data)) ? true : false;
      $url=$this->_url;
      $url = str_replace(' ', '%20', $url);
      $apiname='ReviseCheckoutStatus';
      if($is_json==true)
      {
      $json_data=json_decode($input_data,true);
      $input_type='json';
      $file_contents = $this->ApiRepoObj->getXml($json_data,$apiname,$this->_auth_token,$input_type);
      $product_id='';
      $json_data='';
      $status='';
      $send_curl=$this->ApiRepoObj->sendRequest($url,$file_contents,$apiname,$product_id,$item_id,$json_data,$status,$order_id);
      return $send_curl;  
      }
      else
      {
      $file_contents = strchr($input_data,'<?xml');
      $input_type='xml';
      $file_contents = $this->ApiRepoObj->getXml($file_contents,$apiname,$this->_auth_token,$input_type);
      if($file_contents)
      {
      $send_curl=$this->ApiRepoObj->sendRequest($url,$file_contents,$apiname);  
      }
     return $send_curl;
      }
     }
    catch(Exception $e){
         $message=$e->getMessage();
    }
   
    }

    public function getallCategories($input_data){
    try{
      
      $input_data=json_decode($input_data); 
      $is_json=is_string($input_data) && is_object(json_decode($input_data)) ? true : false;
      $url=$this->_url;
      $url = str_replace(' ', '%20', $url);
      $apiname='GetCategories';
      if($is_json==true)
      {
      $json_data=json_decode($input_data,true);
      $input_type='json';
      $file_contents = $this->ApiRepoObj->getXml($json_data,$apiname,$this->_auth_token,$input_type);
      $send_curl=$this->ApiRepoObj->sendRequest($url,$file_contents,$apiname);
      return $send_curl;  
      }
      else
      {
      $file_contents = strchr($input_data,'<?xml');
      $input_type='xml';
      $file_contents = $this->ApiRepoObj->getXml($file_contents,$apiname,$this->_auth_token,$input_type);
      if($file_contents)
      {
      $send_curl=$this->ApiRepoObj->sendRequest($url,$file_contents,$apiname);  
      }
     return $send_curl;
      }
     }
    catch(Exception $e){
         $message=$e->getMessage();
    }
   
    }
     public function endListing($input_data){
    try{
      
      $input_data=json_decode($input_data);
      $is_json=is_string($input_data) && is_object(json_decode($input_data)) ? true : false;
      $url=$this->_url;
      $url = str_replace(' ', '%20', $url);
      $apiname='EndItem';
      if($is_json==true)
      {
      $json_data=json_decode($input_data,true);
      $input_type='json';
      $file_contents = $this->ApiRepoObj->getXml($json_data,$apiname,$this->_auth_token,$input_type);
      //return $file_contents;
      $send_curl=$this->ApiRepoObj->sendRequest($url,$file_contents,$apiname);
      return $send_curl;  
      }
      else
      {
      $file_contents = strchr($input_data,'<?xml');
      $input_type='xml';
      $file_contents = $this->ApiRepoObj->getXml($file_contents,$apiname,$this->_auth_token,$input_type);
      if($file_contents)
      {
      $send_curl=$this->ApiRepoObj->sendRequest($url,$file_contents,$apiname);  
      }
     return $send_curl;
      }
     }
    catch(Exception $e){
         $message=$e->getMessage();
    }
   
    }
   

   public function addDispute($input_data,$order_id,$ItemID,$DisputeExplanation){
    try{
      
     // $is_json=is_string($input_data) && is_object(json_decode($input_data)) ? true : false;
     // print_r('here');exit;

      $url=$this->_url;
      $url = str_replace(' ', '%20', $url);
      $apiname='AddDispute';
      
      //if($is_json==true)
      //{
      
      $json_data=json_decode($input_data,true);
      $input_type='json';

      $file_contents = $this->ApiRepoObj->getXml($json_data,$apiname,$this->_auth_token,$input_type);
      //return $file_contents;
      $product_id='';
      $json_data='';
      $status='';
      $send_curl=$this->ApiRepoObj->sendRequest($url,$file_contents,$apiname,$product_id,$ItemID,$json_data,$status,$order_id,$DisputeExplanation);
      return $send_curl;  
      //}
      /*else
      {
      $file_contents = strchr($input_data,'<?xml');
      $input_type='xml';
      $file_contents = $this->ApiRepoObj->getXml($file_contents,$apiname,$this->_auth_token,$input_type);
      if($file_contents)
      {
      $send_curl=$this->ApiRepoObj->sendRequest($url,$file_contents,$apiname,$this->_dev_token,$this->_app_token,$this->_cert_token);  
      }
     return $send_curl;
      }*/
     }
    catch(Exception $e){
         $message=$e->getMessage();
    }
   
    }

    public function addDisputeResponse($input_data){
     try{
      
      $is_json=is_string($input_data) && is_object(json_decode($input_data)) ? true : false;
      $url=$this->_url;
      $url = str_replace(' ', '%20', $url);
      $apiname='AddDisputeResponse';
      
      if($is_json==true)
      {

      $json_data=json_decode($input_data,true);
      $input_type='json';
      $file_contents = $this->ApiRepoObj->getXml($json_data,$apiname,$this->_auth_token,$input_type);
      $send_curl=$this->ApiRepoObj->sendRequest($url,$file_contents,$apiname);
      return $send_curl;  
      
      }
      else
      {
      $file_contents = strchr($input_data,'<?xml');
      $input_type='xml';
      $file_contents = $this->ApiRepoObj->getXml($file_contents,$apiname,$this->_auth_token,$input_type);
      if($file_contents)
      {
      $send_curl=$this->ApiRepoObj->sendRequest($url,$file_contents,$apiname);  
      }
     return $send_curl;
      }
     }
    catch(Exception $e){
         $message=$e->getMessage();
    }
    }

    public function getUserDisputes($input_data){
     try{
      
      $is_json=is_string($input_data) && is_object(json_decode($input_data)) ? true : false;
      $url=$this->_url;
      $url = str_replace(' ', '%20', $url);
      $apiname='GetUserDisputes';
      
      if($is_json==true)
      {
        
      $json_data=json_decode($input_data,true);
      $input_type='json';
      $file_contents = $this->ApiRepoObj->getXml($json_data,$apiname,$this->_auth_token,$input_type);
      $send_curl=$this->ApiRepoObj->sendRequest($url,$file_contents,$apiname);
      return $send_curl;  
      
      }
      else
      {
      $file_contents = strchr($input_data,'<?xml');
      $input_type='xml';
      $file_contents = $this->ApiRepoObj->getXml($file_contents,$apiname,$this->_auth_token,$input_type);
      if($file_contents)
      {
      $send_curl=$this->ApiRepoObj->sendRequest($url,$file_contents,$apiname);  
      }
     return $send_curl;
      }
     }
    catch(Exception $e){
         $message=$e->getMessage();
    }
    }

    public function relistProduct($input_data,$product_id,$channel_id){
    try{
      
      
      $is_json=is_string($input_data) && is_object(json_decode($input_data)) ? true : false;
      $url=$this->_url;
      $url = str_replace(' ', '%20', $url);
      $apiname='RelistFixedPriceItem';
      if($is_json==true)
      {
      $json_data=json_decode($input_data,true);
      $input_type='json';
      $file_contents = $this->ApiRepoObj->getXml($json_data,$apiname,$this->_auth_token,$input_type);
     return $file_contents;
      //$product_id='';
      $item_id='';
      //$json_data='';
      $status='';
      $order_id='';
      $DisputeExplanation='';
      $send_curl=$this->ApiRepoObj->sendRequest($url,$file_contents,$apiname,$this->_dev_token,$this->_app_token,$this->_cert_token,$product_id,$item_id,$json_data,$status,$order_id,$DisputeExplanation,$channel_id);
      return $send_curl;  
      }
      else
      {
      $file_contents = strchr($input_data,'<?xml');
      $input_type='xml';
      $file_contents = $this->ApiRepoObj->getXml($file_contents,$apiname,$this->_auth_token,$input_type);
      if($file_contents)
      {
      $send_curl=$this->ApiRepoObj->sendRequest($url,$file_contents,$apiname,$this->_dev_token,$this->_app_token,$this->_cert_token);  
      }
     return $send_curl;
      }
     }
    catch(Exception $e){
         $message=$e->getMessage();
    }
   
    }

  }   