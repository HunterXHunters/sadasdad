<?php

use Central\Repositories\FlipkartRepo;
//use Central\Repositories\ApiConfig;

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

Class FlipkartApiController extends BaseController{
    
    var $FlipkartObj;
    
    protected $_url;
    protected $_domain;
    
    
    public function __construct(FlipkartRepo $FlipkartObj) {
        
        $this->FlipkartRepoObj = $FlipkartObj;
        $this->_domain = "flipkart";
        $domain = $this->_domain;
        //$this->_url='https://api.'.$domain.'.net/sellers/';        
        
    }
  
    
    public function UpdateItem($json,$listing_id,$api_name,$url){
    try{

      //$url=$this->_url;
      //$url = str_replace(' ', '%20', $url);

      $api_name= 'UpdateItem';
      $send_curl=$this->FlipkartRepoObj->sendRequest($url,$listing_id,$json,$api_name,'','');

      return $send_curl;  
      }
      
    catch(Exception $e){
         $message=$e->getMessage();
    }
   
    }
     public function UpdateInventory($json,$listing_id,$api_name,$url){
    try{

     // $url=$this->_url;
     // $url = str_replace(' ', '%20', $url);
      $apiname= 'UpdateInventory';
      $url=$url.'skus/listings/'.$listing_id;
      //return $url;
     // return $json;
      $send_curl=$this->FlipkartRepoObj->sendRequest($url,$listing_id,$json,$api_name,'','');  
      }
      
    catch(Exception $e){
         $message=$e->getMessage();
    }
   
    }
    public function GetProduct($SKUID,$api_name,$url){
    try{

    $url=$url.'skus/'.$SKUID.'/listings';
//$url=$this->_url;
//return $this->_url;

     $url = str_replace(' ', '%20', $url);
     $api_name= 'GetProduct';
     
      $send_curl=$this->FlipkartRepoObj->sendRequest($url,'','',$api_name,$SKUID,'');
      return $send_curl; 
      }
    catch(Exception $e){
         $message=$e->getMessage();
    }
}   


public function GetOrders($json,$url){
    try{
     
      $api_name= 'GetOrders';
     
      $send_curl=$this->FlipkartRepoObj->sendRequest($url,'',$json,$api_name,'','');

      //print_r('Hello');exit;
      return $send_curl; 
      }
    catch(Exception $e){
         $message=$e->getMessage();
    }
}

/*public function GetPreviousOrders($json,$url){
    try{
     
      $api_name= 'GetPreviousOrders';
     
      $send_curl=$this->FlipkartRepoObj->sendRequest($url,'',$json,$api_name,'','');

      //print_r('Hello');exit;
      return $send_curl; 
      }
    catch(Exception $e){
         $message=$e->getMessage();
    }
}*/



public function Vieworder($channel_order_itemid,$url){
    try{

   //$url=$url.'/orders/'.$channel_order_id;
   // $url = str_replace(' ', '%20', $url);

     $api_name= 'Vieworder';
    
      $send_curl=$this->FlipkartRepoObj->sendRequest($url,'','',$api_name,'',$channel_order_itemid);
      return $send_curl; 
      }
    catch(Exception $e){
         $message=$e->getMessage();
    }
} 


public function Cancelorder($json,$url){
    try{

      $api_name= 'Cancelorder';

      $send_curl=$this->FlipkartRepoObj->sendRequest($url,'',$json,$api_name,'','');
      return $send_curl; 
      }
    catch(Exception $e){
         $message=$e->getMessage();
    }

}

public function Readytodispatch($json,$url) {
  try {

    $api_name = 'Readytodispatch';

    $send_curl =$this->FlipkartRepoObj->sendRequest($url,'',$json,$api_name,'','');
      return $send_curl;

      }
    catch(Exception $e){
         $message=$e->getMessage();
    }
}

public function Packorder($json,$url) {
  try {

    $api_name = 'Packorder';

    $send_curl =$this->FlipkartRepoObj->sendRequest($url,'',$json,$api_name,'','');
      return $send_curl; 

      }
    catch(Exception $e){
         $message=$e->getMessage();
    }
}


}