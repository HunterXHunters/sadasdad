<?php
use Central\Repositories\AmazonApiRepo; 


Class AmazondeveloperController extends BaseController 
{

var $ApiObj;
   
public function __construct(AmazonApiRepo $ApiObj)
{
$this->ApiRepoObj = $ApiObj;
}

public function AddItem()
{
     try{ 
          $getinventory=$this->ApiRepoObj->getGdsProducts();
          $json_array=array();
          $result=array();
          $i=0;
           foreach($getinventory as $value)
            {
            $get_xml_array=$this->ApiRepoObj->getXmlArrayProduct($value->sku,$value->Title,$value->Description, $i);
            $json_array=$get_xml_array; 
            $sku[]=$value->sku;
            $i++;
            array_push($result,$json_array);           
           } 
              $sku=json_encode($sku);
              $json=json_encode($result);

             $request = new AmazonApiController($this->ApiRepoObj);
             $message[] = $request->AddItem($json,$i,$sku);

     }

catch(Exception $e){
           $message=$e->getMessage();
        }
 //return Response::json(['Message'=>$message]);
         if(!empty(Response::json(['Message'=>$message])))
       { 
        $update_condition='AddProduct';
        $update_det=$this->ApiRepoObj->update_details($sku,$update_condition);
        }
      }
 
 
 public function UpdateItem()
{
     try{
          $getinventory=$this->ApiRepoObj->get_updated_item();
          $json_array=array();
           $result=array();
          $i=0;
           foreach($getinventory as $value)
            {
            $get_xml_array=$this->ApiRepoObj->getXmlArrayUpdate($value->sku,$value->Title,$value->Description, $i,$value->channel_product_key);
            $json_array=$get_xml_array; 
            $sku[]=$value->sku;
            $i++;
            array_push($result,$json_array);           
           } 
              $sku=json_encode($sku);
              $json=json_encode($result);

             $request = new AmazonApiController($this->ApiRepoObj);
             $message[] = $request->AddItem($json,$i,$sku);        
     }
catch(Exception $e)
        {
           $message=$e->getMessage();
        }
 //return Response::json(['Message'=>$message]);

         if(!empty(Response::json(['Message'=>$message])))
       { 
        $update_condition='UpdateProduct';
        $update_det=$this->ApiRepoObj->update_details($sku,$update_condition);
        }
      }
 


 public function DeleteItem()
{
     try{
          $getinventory=$this->ApiRepoObj->get_delete_item();
          $json_array=array();
          $result=array();
          $i=0;

           foreach($getinventory as $value)
            {
            $get_xml_array=$this->ApiRepoObj->getXmlArrayDelete($value->product_id,$i,$value->channel_product_key);
            $json_array=$get_xml_array; 
            $sku[]=$value->product_id;
            $i++;
            array_push($result,$json_array);           
           } 
              $sku=json_encode($sku);
              $json=json_encode($result);

             $request = new AmazonApiController($this->ApiRepoObj);
             $message[] = $request->AddItem($json,$i,$sku);        
     }
catch(Exception $e)
        {
           $message=$e->getMessage();
        }
 //return Response::json(['Message'=>$message]);

 if(!empty(Response::json(['Message'=>$message])))
       { 
        $update_condition='DeleteProduct';
        $update_det=$this->ApiRepoObj->update_details($sku,$update_condition);
        }
        
}



public function UpdateImage()
{
  try{
         $getImage=$this->ApiRepoObj->get_updated_image();
         $json_array=array();
         $result=array();
         $i=0;
        // $baseurl= URL::asset('/uploads/products/');
          
          foreach($getImage as $arr) 
            {
            $get_xml_array=$this->ApiRepoObj->getXmlArrayImage($arr->sku,$i);
            $json_array=$get_xml_array; 
            $json_array['Message'] ['ProductImage']['ImageLocation']=$arr->image;  //$baseurl.'/'.$arr->Image;
            $sku=$arr->sku;
            $i++;
            array_push($result,$json_array);
            }

            $json=json_encode($json_array);
            $json=urlencode($json);
            $sku=json_encode($sku);
        
            $request = new AmazonApiController($this->ApiRepoObj);
            $message[] = $request->UpdateImage($json,$i,$sku);

            
     }
      
 catch(Exception $e)
        {
          $message = $e->getMessage();
        }
      return Response::json(['Message'=>$message]);
}


 public function UpdatePrice()
 {
        try
        {
         $getinventory=$this->ApiRepoObj->get_updated_price(); 
         $json_array=array();
         $result=array();
         $i=0;
         foreach($getinventory as $value)
            {
            $get_xml_array=$this->ApiRepoObj->getXmlArrayPrice($value->sku,$value->Price,$i);
            $json_array=$get_xml_array;
            $sku[]=$value->sku;
            $i++;
            array_push($result,$json_array);
            }
            $sku=json_encode($sku);
            $json=json_encode($result);
            $request = new AmazonApiController($this->ApiRepoObj);
            $message[] = $request->UpdatePrice($json,$i,$sku);
          
      
    }
        catch(Exception $e){
            $message=$e->getMessage();
        }    
      return Response::json(['Message'=>$message]);
       }

 

 public function UpdateInventory()
 {
        try{
         $getinventory=$this->ApiRepoObj->get_updated_qty(); 
         $json_array=array();
         $result=array();
         $i=0;
        foreach($getinventory as $value)
            { 
            $get_xml_array=$this->ApiRepoObj->getXmlArrayInventory($getinventory[$i]->product_id,$getinventory[$i]->Quantity,$i);
            $json_array=$get_xml_array;
            $sku[]=$getinventory[$i]->product_id;
            $i++;
            array_push($result,$json_array);
            }

             $sku=json_encode($sku);
             $json=json_encode($result);
             $request = new AmazonApiController($this->ApiRepoObj);
             $message[] = $request->UpdateInventory($json,$i,$sku);
         
      }
        catch(Exception $e){
            $message=$e->getMessage();
        }
       return Response::json(['Message'=>$message]);
     }



public function getFeedSubmissionResult()
{
  //$id=$this->ApiRepoObj->getFeedSubmissionId(); 
  //$ids=$id[0]->feedsubmissionid;
  //$id1=json_decode($id,true);
  //print_r($id);exit;

  //echo $ids;exit;

$ids='51004016710';

//include_once ('.config.inc.php'); 
//include_once (app_path().'/MarketplaceWebService/MarketplaceWebService_Client.php'); 

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

 $parameters = array (
 'Merchant' => MERCHANT_ID,
 'FeedSubmissionId' => '51161016742',
 'FeedSubmissionResult' => @fopen('php://memory', 'rw+')
);

$request = new MarketplaceWebService_Model_GetFeedSubmissionResultRequest($parameters);
     
$xml=$this->ApiRepoObj->invokeGetFeedSubmissionResult($service, $request);
print_r($xml);die;

//print_r($service);exit;
//foreach($id[]->feedsubmissionid as $feedid)
//{
//echo $feedid;exit;
//$filename ='file.xml';
//$handle = fopen($filename, 'w+');
$request = new MarketplaceWebService_Model_GetFeedSubmissionResultRequest();
$request->setMerchant(MERCHANT_ID);
$request->setFeedSubmissionId($ids);
//$request->setFeedSubmissionResult($handle);

//$xml=simplexml_load_file($filename );
echo $xml->Message->ProcessingReport->DocumentTransactionID . "<br>";
echo $StatusCode=$xml->Message->ProcessingReport->StatusCode . "<br>";
echo $MessagesProcessed=$xml->Message->ProcessingReport->ProcessingSummary->MessagesProcessed . "<br>";
echo $MessagesSuccessful=$xml->Message->ProcessingReport->ProcessingSummary->MessagesSuccessful. "<br>";
echo $MessagesWithError=$xml->Message->ProcessingReport->ProcessingSummary->MessagesWithError. "<br>";
echo $MessagesWithWarning=$xml->Message->ProcessingReport->ProcessingSummary->MessagesWithWarning. "<br>";
//print_r($request);exit;
//return $feedStatus;
die;
}


public function getASINforProduct()
 {
$tokens=$this->ApiRepoObj->getAccessTokens();


$SellerId=$tokens['seller_id'];
$MarketplaceId=$tokens['marketplace_id'];
$AWSAccessKeyId=$tokens['key_name'];
$AWSSecrectKey=$tokens['key_value'];

$Action='GetMatchingProductForId';
$SignatureMethod='HmacSHA256';
$SignatureVersion='2';
$Timestamp= gmdate("Y-m-d\TH:i:s\Z");
$version='2011-10-01';
$IdType='SellerSKU';

$sku=$this->ApiRepoObj->getSKUforProduct();
//print_r($sku);exit;


foreach ($sku as $sku1) 
{
    $SKU='sku-'.$sku1->product_id;
    $params = array(
        'AWSAccessKeyId' => $AWSAccessKeyId,
        'Action' => $Action,
        'SellerId' =>$SellerId,
        'SignatureMethod' => $SignatureMethod,
        'SignatureVersion' => $SignatureVersion,
         'MarketplaceId' => $MarketplaceId,
        'Timestamp'=>$Timestamp,
        'Version'=> $version,
        'IdType'=>$IdType,
        'IdList.Id.1'=>$SKU
        );

    $url_parts = array();
    foreach(array_keys($params) as $key)
        $url_parts[] = $key . "=" . str_replace('%7E', '~', rawurlencode($params[$key]));

    sort($url_parts);

    $url_string = implode("&", $url_parts);
    $string_to_sign = "GET\nmws.amazonservices.in\n/Products/2011-10-01\n" . $url_string;
    $signature = hash_hmac("sha256", $string_to_sign, $AWSSecrectKey, TRUE);
    $signature = urlencode(base64_encode($signature));

    $url = "https://mws.amazonservices.in/Products/2011-10-01" . '?' . $url_string . "&Signature=" . $signature;
    $result=file_get_contents($url);
    $res=simplexml_load_string($result);
    $asin=$res->GetMatchingProductForIdResult->Products->Product->Identifiers->MarketplaceASIN->ASIN;
    $database = $this->ApiRepoObj->push_asin($asin,$SKU);

}
}


 public function ListOrders()
 {
 $listorder=$this->ApiRepoObj->listOrders();
 print_r($listorder);
 }

 public function ListOrderItems()
 {
   $order_id=$this->ApiRepoObj->ListOrderItems();
   $ord=json_decode($order_id);
  // print_r($ord); exit;
   foreach($ord as $order)
   {
      $orderarr = json_decode($order);
      //print_r( $orderarr);exit;
        if(!empty($orderarr) && $orderarr[0]->msg=='StockUnavailable')
        {
           $reason='NoInventory';
           $cancelOrder=$this->cancelOrder($orderarr[0]->orderid,$orderarr[0]->orderitemid,$reason);
        }
   }  
 
 
 }

public function cancelOrder($order_id='',$orderitemid='',$reason='')
{
  try 
  {
        if(empty($order_id) && empty($reason) && empty($orderitemid))
         { 
         $order_id='403-1963366-4189953';
         $orderitemid='25186201579579';
         $reason='BuyerCanceled';
         }

        
         $status=$this->ApiRepoObj->getUnshippedOrders($order_id);
        // print_r($status);exit;

         if($status->channel_order_status=='Unshipped')
         {

            $json_array=array();
            $i=0;
            $get_xml_array=$this->ApiRepoObj->getXmlArrayCancel($order_id,$orderitemid,$i,$reason);
            $json_array=$get_xml_array;
            $json=json_encode($json_array);
            $request = new AmazonApiController($this->ApiRepoObj);
            $message[] = $request->cancelOrder($json,$i);

           
              if(!empty($message))
              {
                if(!empty($reason))
                {
                  if($reason=='NoInventory')
                  {
                  $cancelReaon= DB::table('Channel_order_details')
                            ->where('order_id',$order_id)
                            ->update(array('cancelReason'=>$reason,'channel_order_status'=>'CancelledBySeller'));

                  $channel_status=DB::Table('Channel_orders')
                      ->where('channel_order_id',$order_id)
                      ->update(array('order_status'=>'CancelledBySeller'));

                   print_r('Successful Updating Cancel reason');
                 }
                 else
                 {
                   $cancelReaon= DB::table('Channel_order_details')
                            ->where('order_id',$order_id)
                            ->update(array('cancelReason'=>$reason));

                 }

                }
              }

        }
   }
    catch(Exception $e){
        $message=$e->getMessage();
    }
     return Response::json(['Message'=>$message]); 
}


public function getOrder($order_id=' ')
{
  //$order_id='403-1963366-4189953';
  $getOrder=$this->ApiRepoObj->getOrder($order_id);
  print_r($getOrder);
}

}