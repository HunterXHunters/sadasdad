<?php

use Central\Repositories\AmazonApiRepo; 

//protected $_form_url;

Class AmazonApiController extends BaseController
{

public function __construct(AmazonApiRepo $ApiObj)
{
$this->ApiRepoObj = $ApiObj;
}
 

public function AddItem($json_data, $i, $sku)
{
$json_data=json_decode($json_data,true);
$sku=json_decode($sku,true);

$apiname='AddProduct';
$messageType='Product';

/*include_once ('.config.inc.php'); 
include_once (app_path().'/MarketplaceWebService/MarketplaceWebService_Client.php'); */

include_once ($_SERVER['DOCUMENT_ROOT'].'/MarketplaceWebService/Samples/.config.inc.php'); 
include_once ($_SERVER['DOCUMENT_ROOT'].'/MarketplaceWebService/Client.php'); 

$serviceUrl = "https://mws.amazonservices.in";

$config = array (
  'ServiceURL' => $serviceUrl,
  'ProxyHost' => null,
  'ProxyPort' => -1,
  'MaxErrorRetry' => 3,
);
$service = new MarketplaceWebService_Client(
     AWS_ACCESS_KEY_ID, 
     AWS_SECRET_ACCESS_KEY, 
     $config,
     APPLICATION_NAME,
     APPLICATION_VERSION);

$file_contents = $this->ApiRepoObj->getXml($json_data,$apiname,$i,$messageType);
//print_r($file_contents);exit;
$marketplaceIdArray = array("Id" => array('A21TJRUUN4KGV'));
$feedHandle = @fopen('php://temp', 'rw+');
fwrite($feedHandle, $file_contents);
rewind($feedHandle);
require_once ($_SERVER['DOCUMENT_ROOT'].'/MarketplaceWebService/Model/SubmitFeedRequest.php');
$request = new MarketplaceWebService_Model_SubmitFeedRequest();
$request->setMerchant(MERCHANT_ID);
$request->setMarketplaceIdList($marketplaceIdArray);
$request->setFeedType('_POST_PRODUCT_DATA_');
$request->setContentMd5(base64_encode(md5(stream_get_contents($feedHandle), true)));
rewind($feedHandle);
$request->setPurgeAndReplace(false);
$request->setFeedContent($feedHandle);
rewind($feedHandle);


$send_curl=$this->ApiRepoObj->invokeSubmitFeed($service,$request,$sku,$apiname);
return $send_curl;  
}


/*public function UpdateItem($json_data, $i, $sku)
{
$json_data=json_decode($json_data,true);
$apiname='UpdateProduct';
$messageType='Product'; 

include_once ($_SERVER['DOCUMENT_ROOT'].'/MarketplaceWebService/Samples/.config.inc.php'); 
include_once ($_SERVER['DOCUMENT_ROOT'].'/MarketplaceWebService/Client.php'); 

$serviceUrl = "https://mws.amazonservices.in";

$config = array (
  'ServiceURL' => $serviceUrl,
  'ProxyHost' => null,
  'ProxyPort' => -1,
  'MaxErrorRetry' => 3,
);
$service = new MarketplaceWebService_Client(
     AWS_ACCESS_KEY_ID, 
     AWS_SECRET_ACCESS_KEY, 
     $config,
     APPLICATION_NAME,
     APPLICATION_VERSION);


$file_contents = $this->ApiRepoObj->getXml($json_data,$apiname,$i,$messageType);
print_r($file_contents);exit;
$marketplaceIdArray = array("Id" => array('A21TJRUUN4KGV'));
$feedHandle = @fopen('php://temp', 'rw+');
fwrite($feedHandle, $file_contents);
rewind($feedHandle);
require_once ($_SERVER['DOCUMENT_ROOT'].'/MarketplaceWebService/Model/SubmitFeedRequest.php');
$request = new MarketplaceWebService_Model_SubmitFeedRequest();
$request->setMerchant(MERCHANT_ID);
$request->setMarketplaceIdList($marketplaceIdArray);
$request->setFeedType('_POST_PRODUCT_DATA_');
$request->setContentMd5(base64_encode(md5(stream_get_contents($feedHandle), true)));
rewind($feedHandle);
$request->setPurgeAndReplace(false);
$request->setFeedContent($feedHandle);
rewind($feedHandle);


$send_curl=$this->ApiRepoObj->invokeSubmitFeed($service,$request,$sku,$apiname);
@fclose($feedHandle);
return $send_curl;  
}*/


public function UpdateInventory($json_data,$i,$sku)
 {
    try
    {     
    $json_data=json_decode($json_data,true);
    $apiname="InventoryApi";
    $messageType='Inventory';


include_once ($_SERVER['DOCUMENT_ROOT'].'/MarketplaceWebService/Samples/.config.inc.php'); 
include_once ($_SERVER['DOCUMENT_ROOT'].'/MarketplaceWebService/Client.php'); 

$serviceUrl = "https://mws.amazonservices.in";

$config = array (
  'ServiceURL' => $serviceUrl,
  'ProxyHost' => null,
  'ProxyPort' => -1,
  'MaxErrorRetry' => 3,
);
$service = new MarketplaceWebService_Client(
     AWS_ACCESS_KEY_ID, 
     AWS_SECRET_ACCESS_KEY, 
     $config,
     APPLICATION_NAME,
     APPLICATION_VERSION);

$file_contents = $this->ApiRepoObj->getXml($json_data,$apiname,$i,$messageType);
//print_r($file_contents);exit;
$marketplaceIdArray = array("Id" => array('A21TJRUUN4KGV'));
$feedHandle = @fopen('php://temp', 'rw+');
fwrite($feedHandle, $file_contents);
rewind($feedHandle);
require_once ($_SERVER['DOCUMENT_ROOT'].'/MarketplaceWebService/Model/SubmitFeedRequest.php');
$request = new MarketplaceWebService_Model_SubmitFeedRequest();
$request->setMerchant(MERCHANT_ID);
$request->setMarketplaceIdList($marketplaceIdArray);
$request->setFeedType('_POST_INVENTORY_AVAILABILITY_DATA_');
$request->setContentMd5(base64_encode(md5(stream_get_contents($feedHandle), true)));
rewind($feedHandle);
$request->setPurgeAndReplace(false);
$request->setFeedContent($feedHandle);
rewind($feedHandle);

$send_curl=$this->ApiRepoObj->invokeSubmitFeed($service,$request,$sku,$apiname);
@fclose($feedHandle);
return $send_curl;  
}
    catch(Exception $e){
         $message=$e->getMessage();
    }
   
    }



public function cancelOrder($json_data,$i)
 {
    try
    {  
    $json_data=json_decode($json_data,true);
    $apiname="cancelApi";
    $messageType='OrderAcknowledgement';

include_once ($_SERVER['DOCUMENT_ROOT'].'/MarketplaceWebService/Samples/.config.inc.php'); 
include_once ($_SERVER['DOCUMENT_ROOT'].'/MarketplaceWebService/Client.php'); 

$serviceUrl = "https://mws.amazonservices.in";

$config = array (
  'ServiceURL' => $serviceUrl,
  'ProxyHost' => null,
  'ProxyPort' => -1,
  'MaxErrorRetry' => 3,
);
$service = new MarketplaceWebService_Client(
     AWS_ACCESS_KEY_ID, 
     AWS_SECRET_ACCESS_KEY, 
     $config,
     APPLICATION_NAME,
     APPLICATION_VERSION);

$file_contents = $this->ApiRepoObj->getXml($json_data,$apiname,$i,$messageType);
$marketplaceIdArray = array("Id" => array('A21TJRUUN4KGV'));
$feedHandle = @fopen('php://temp', 'rw+');
fwrite($feedHandle, $file_contents);
rewind($feedHandle);
require_once ($_SERVER['DOCUMENT_ROOT'].'/MarketplaceWebService/Model/SubmitFeedRequest.php');
$request = new MarketplaceWebService_Model_SubmitFeedRequest();
$request->setMerchant(MERCHANT_ID);
$request->setMarketplaceIdList($marketplaceIdArray);
$request->setFeedType('_POST_ORDER_ACKNOWLEDGEMENT_DATA_');
$request->setContentMd5(base64_encode(md5(stream_get_contents($feedHandle), true)));
rewind($feedHandle);
$request->setPurgeAndReplace(false);
$request->setFeedContent($feedHandle);
rewind($feedHandle);

$send_curl=$this->ApiRepoObj->invokeSubmitFeed($service,$request,' ',$apiname);
@fclose($feedHandle);
return $send_curl;  
}
    catch(Exception $e){
         $message=$e->getMessage();
    }
   
    }

public function UpdatePrice($json,$i,$sku)
{

$json_data=json_decode($json,true);
$apiname="PriceApi";
$messageType='Price';

include_once ($_SERVER['DOCUMENT_ROOT'].'/MarketplaceWebService/Samples/.config.inc.php'); 
include_once ($_SERVER['DOCUMENT_ROOT'].'/MarketplaceWebService/Client.php'); 

$serviceUrl = "https://mws.amazonservices.in";

$config = array (
  'ServiceURL' => $serviceUrl,
  'ProxyHost' => null,
  'ProxyPort' => -1,
  'MaxErrorRetry' => 3,
);
$service = new MarketplaceWebService_Client(
     AWS_ACCESS_KEY_ID, 
     AWS_SECRET_ACCESS_KEY, 
     $config,
     APPLICATION_NAME,
     APPLICATION_VERSION);
$file_contents = $this->ApiRepoObj->getXml($json_data,$apiname,$i,$messageType);
//print_r($file_contents);exit;
$marketplaceIdArray = array("Id" => array('A21TJRUUN4KGV'));
$feedHandle = @fopen('php://temp', 'rw+');
fwrite($feedHandle, $file_contents);
rewind($feedHandle);
require_once ($_SERVER['DOCUMENT_ROOT'].'/MarketplaceWebService/Model/SubmitFeedRequest.php');
$request = new MarketplaceWebService_Model_SubmitFeedRequest();
$request->setMerchant(MERCHANT_ID);
$request->setMarketplaceIdList($marketplaceIdArray);
$request->setFeedType('_POST_PRODUCT_PRICING_DATA_');
$request->setContentMd5(base64_encode(md5(stream_get_contents($feedHandle), true)));
rewind($feedHandle);
$request->setPurgeAndReplace(false);
$request->setFeedContent($feedHandle);
rewind($feedHandle);

$send_curl=$this->ApiRepoObj->invokeSubmitFeed($service,$request,$sku,$apiname);
@fclose($feedHandle);
return $send_curl;  
}


public function UpdateImage($json_data,$i,$sku)
{

$json_data=urldecode($json_data);
$json_data=json_decode($json_data,true);
$apiname='UpdateImage';
$messageType='ProductImage';
/*include_once ('.config.inc.php'); 
include_once (app_path().'/MarketplaceWebService/MarketplaceWebService_Client.php'); */

include_once ($_SERVER['DOCUMENT_ROOT'].'/MarketplaceWebService/Samples/.config.inc.php'); 
include_once ($_SERVER['DOCUMENT_ROOT'].'/MarketplaceWebService/Client.php'); 

$serviceUrl = "https://mws.amazonservices.in";

$config = array (
  'ServiceURL' => $serviceUrl,
  'ProxyHost' => null,
  'ProxyPort' => -1,
  'MaxErrorRetry' => 3,
);
$service = new MarketplaceWebService_Client(
     AWS_ACCESS_KEY_ID, 
     AWS_SECRET_ACCESS_KEY, 
     $config,
     APPLICATION_NAME,
     APPLICATION_VERSION);

$file_contents = $this->ApiRepoObj->getXml($json_data,$apiname,$i,$messageType);
//print_r($file_contents);exit;
$marketplaceIdArray = array("Id" => array('A21TJRUUN4KGV'));
$feedHandle = @fopen('php://temp', 'rw+');
fwrite($feedHandle, $file_contents);
rewind($feedHandle);
require_once ($_SERVER['DOCUMENT_ROOT'].'/MarketplaceWebService/Model/SubmitFeedRequest.php');
$request = new MarketplaceWebService_Model_SubmitFeedRequest();
$request->setMerchant(MERCHANT_ID);
$request->setMarketplaceIdList($marketplaceIdArray);
$request->setFeedType('_POST_PRODUCT_IMAGE_DATA_');
$request->setContentMd5(base64_encode(md5(stream_get_contents($feedHandle), true)));
rewind($feedHandle);
$request->setPurgeAndReplace(false);
$request->setFeedContent($feedHandle);
rewind($feedHandle);



$send_curl=$this->ApiRepoObj->invokeSubmitFeed($service,$request,$sku,$apiname);
@fclose($feedHandle);
return $send_curl;  
}








/*public function GetFeedResult($json_data,$i,$sku)
{
 try
    {     
      echo 'here';
    $json_data=json_decode($json_data,true);
    $apiname="FeedResult";
    $api='ProcessingReport';
//print_r($sku);
//print_r($json_data);exit;
    //print_r($i);exit;
include_once ('.config.inc.php'); 
include_once (app_path().'/MarketplaceWebService/MarketplaceWebService_Client.php'); 

$serviceUrl = "https://mws.amazonservices.in";

$config = array (
  'ServiceURL' => $serviceUrl,
  'ProxyHost' => null,
  'ProxyPort' => -1,
  'MaxErrorRetry' => 3,
);
$service = new MarketplaceWebService_Client(
     AWS_ACCESS_KEY_ID, 
     AWS_SECRET_ACCESS_KEY, 
     $config,
     APPLICATION_NAME,
     APPLICATION_VERSION);
//print_r($json_data);exit;
$file_contents = $this->ApiRepoObj->getXml($json_data,$apiname,$i,$api);
//print_r($file_contents);exit;
$marketplaceIdArray = array("Id" => array('A21TJRUUN4KGV'));
$feedHandle = @fopen('php://temp', 'rw+');
fwrite($feedHandle, $file_contents);
rewind($feedHandle);
$request = new MarketplaceWebService_Model_SubmitFeedRequest();
$request->setMerchant(MERCHANT_ID);
$request->setMarketplaceIdList($marketplaceIdArray);
$request->setFeedType('_POST_INVENTORY_AVAILABILITY_DATA_');
$request->setContentMd5(base64_encode(md5(stream_get_contents($feedHandle), true)));
rewind($feedHandle);
$request->setPurgeAndReplace(false);
$request->setFeedContent($feedHandle);
rewind($feedHandle);

$send_curl=$this->ApiRepoObj->invokeSubmitFeed($service,$request,$sku,$apiname);
@fclose($feedHandle);
return $send_curl;  
}
    catch(Exception $e){
         $message=$e->getMessage();
    }
    
}*/



}