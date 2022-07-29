<?php  namespace App\Repositories; 
  //use Central\Repositories\AmazonApiRepo;
  //namespace Central\MarketplaceWebService; 
  
  //use controllers\AmazondeveloperController; 
  
  use Token;
  use User;
  use DB;  //Include laravel db class
  use Session;
  
  
  
  Class AmazonApiRepo
  {
    public function getXml($json_data,$apiname, $i, $messageType)
    { 
      $xml_user_info = new \SimpleXMLElement("<?xml version='1.0' encoding='utf-8'?><root></root>");    
      $test=$this->array_to_xml($json_data,$xml_user_info); 
      $json_data = $xml_user_info->asXML();
      
      $json_data = str_replace('<root>', "<AmazonEnvelope><Header>
      <DocumentVersion>1.01</DocumentVersion>
      <MerchantIdentifier>M_FLEXPAX_424611791</MerchantIdentifier>
      </Header><MessageType>".$messageType."</MessageType>", $json_data);
      
      $json_data = str_replace('</root>', "</AmazonEnvelope> ", $json_data);
            
      for($y=0;$y<=$i;$y++)
            {
        
        $item = '<item'.$y.'>';
        $itemend= '</item'.$y.'>';
        $json_data = str_replace($item,' ',$json_data);
        $json_data = str_replace($itemend,' ',$json_data);
        
      } 
            
      if($apiname=="PriceApi")
      {
        $doc = new \DOMDocument();
        $doc->loadXML($json_data);           
        $fragment = $doc->createDocumentFragment();
        $specifications = $doc->getElementsByTagName('StandardPrice');
        foreach($specifications as $a) 
        {
          $fragment = $doc->createDocumentFragment();
          $attr = $doc->createAttribute('currency');
          $attr->value = 'INR';
          $a->appendChild($attr);
          $json_data = $doc->saveXML($doc->documentElement);
        }
      }
      return $json_data;
    }
    
    public function array_to_xml($array, $xml_user_info) {
      
      foreach($array as $key => $value) {
        
        if(is_array($value)) 
        {
          if(!is_numeric($key))
          {
            $subnode = $xml_user_info->addChild("$key");
            $this->array_to_xml($value, $subnode);
          }
          
          else
          {
            $subnode = $xml_user_info->addChild("item$key");
            $this->array_to_xml($value, $subnode);
          }
          }else { 
          $xml_user_info->addChild("$key",htmlspecialchars("$value"));
        }
      }
    }
    
    public function invokeSubmitFeed($service,$request,$sku,$apiname) 
    {
      try 
      
      {
        
        $response = $service->submitFeed($request);
        
                //echo ("Service Response\n");
                //echo ("================\n");
        if ($response->isSetSubmitFeedResult()) 
        { 
          $submitFeedResult = $response->getSubmitFeedResult();
          if ($submitFeedResult->isSetFeedSubmissionInfo()) 
          { 
            $feedSubmissionInfo = $submitFeedResult->getFeedSubmissionInfo();
                        if ($feedSubmissionInfo->isSetFeedSubmissionId()) 
                        {
                            //echo("                    FeedSubmissionId\n");
                            //echo("                        " . $feedSubmissionInfo->getFeedSubmissionId() . "\n");
              echo $FeedSubmissionId = $feedSubmissionInfo->getFeedSubmissionId();
            }
            
          } 
        } 
        
                if ($response->isSetResponseMetadata()) 
                { 
          $responseMetadata = $response->getResponseMetadata();
                    if ($responseMetadata->isSetRequestId()) 
                    {
          }
        } 
        
        
        } catch (MarketplaceWebService_Exception $ex) {
        echo("Caught Exception: " . $ex->getMessage() . "\n");
        echo("Response Status Code: " . $ex->getStatusCode() . "\n");
        echo("Error Code: " . $ex->getErrorCode() . "\n");
        echo("Error Type: " . $ex->getErrorType() . "\n");
        echo("Request ID: " . $ex->getRequestId() . "\n");
        echo("XML: " . $ex->getXML() . "\n");
        echo("ResponseHeaderMetadata: " . $ex->getResponseHeaderMetadata() . "\n");
      }
      
      return $FeedSubmissionId;
    }
    
    
    
    public function getGdsProducts(){
      
      $channel_id=$this->getChannelID();
      
      return  DB::table('Channel_product_add_update')
      ->leftJoin('products as prod','Channel_product_add_update.product_id','=','prod.product_id')
      ->leftJoin('product_inventory','product_inventory.product_id','=','Channel_product_add_update.product_id')
      ->leftJoin('locations','product_inventory.location_id','=','locations.location_id')
      ->leftJoin('product_attributes','product_attributes.product_id','=','prod.product_id')
      ->leftJoin('attributes','product_attributes.attribute_id','=','attributes.attribute_id')
      ->leftJoin('Channel_category','Channel_category.category_id','=','Channel_product_add_update.category_id')
      ->where('Channel_product_add_update.is_added',1)
      ->where('Channel_product_add_update.channel_id',$channel_id)
      ->select(DB::raw('CONCAT(group_concat(product_attributes.value,"$",attributes.name)) as product_attributes'),'prod.name as Title','prod.description as Description','prod.product_id','prod.mrp as Price','prod.image as Image','Channel_category.channel_category_id','product_inventory.available_inventory as qty','prod.upc','locations.location_address','locations.city','prod.sku as sku')
      ->groupBy('prod.product_id')
      ->take(15)
      ->get();
      
      
    }
    
    public function push_data_amazon($FeedSubmissionId, $sku)
    
    {
      
      $result= DB::table('amazon_staging_table')->insert([
      'feedsubmissionid' => $FeedSubmissionId,
      'sku'=>$sku,
      'API_Flag'=> 'ADD',
      'Submission_date'=> date("Y-m-d h:i:sa")
      
      ]);
      
      return  $result;
    }
    
    public function push_inventory_amazon($FeedSubmissionId, $sku)
    
    {
      
      $result=DB::table('amazon_staging_table')->insert([
      'feedsubmissionid' => $FeedSubmissionId,
      'sku'=>$sku,
      'API_Flag'=> 'INVENTORY',
      'Submission_date'=> date("Y-m-d h:i:sa"),
      'is_check'=>0
      ]);
      return  $result;
    }
    public function push_price_amazon($FeedSubmissionId, $sku)
    
    {
      
      $result= DB::table('amazon_staging_table')->insert([
      'feedsubmissionid' => $FeedSubmissionId,
      'sku'=>$sku,
      'API_Flag'=> 'PRICE',
      'Submission_date'=> 'date("Y-m-d h:i:sa")'
      ]);
      
      return  $result;
    }
    
    public function push_update_amazon($FeedSubmissionId, $sku)
    
    {
      
      $result= DB::table('amazon_staging_table')->insert([
      'feedsubmissionid' => $FeedSubmissionId,
      'sku'=>$sku,
      'API_Flag'=> 'UPDATEPRODUCT',
      'Submission_date'=> 'date("Y-m-d h:i:sa")'
      ]);
      
      return  $result;
    }
    public function push_image_amazon($FeedSubmissionId, $sku)
    
    {
      
      $result= DB::table('amazon_staging_table')->insert([
      'feedsubmissionid' => $FeedSubmissionId,
      'sku'=>$sku,
      'API_Flag'=> 'IMAGE',
      'Submission_date'=> 'date("Y-m-d h:i:sa")'
      ]);
      
      return  $result;
    }
    
    
    
    public function get_updated_item()
    {
      $channel_id=$this->getChannelID();
      
      return  DB::table('Channel_product_add_update')
      ->leftJoin('products as prod','Channel_product_add_update.product_id','=','prod.product_id')
      ->leftJoin('product_inventory','product_inventory.product_id','=','Channel_product_add_update.product_id')
      ->leftJoin('locations','product_inventory.location_id','=','locations.location_id')
      ->leftJoin('product_attributes','product_attributes.product_id','=','prod.product_id')
      ->leftJoin('attributes','product_attributes.attribute_id','=','attributes.attribute_id')
      ->leftJoin('Channel_category','Channel_category.category_id','=','Channel_product_add_update.category_id')
      ->where('Channel_product_add_update.is_update',1)
      ->where('Channel_product_add_update.channel_id',$channel_id)
      ->where('Channel_product_add_update.is_added',0)
      ->select(DB::raw('CONCAT(group_concat(product_attributes.value,"$",attributes.name)) as product_attributes'),'prod.name as Title','prod.description as Description','prod.product_id','prod.mrp as Price','prod.image as Image','Channel_category.channel_category_id','product_inventory.available_inventory as qty','prod.upc','locations.location_address','locations.city','prod.sku as sku','Channel_product_add_update.channel_product_key')
      ->groupBy('prod.product_id')
      ->take(15)
      ->get();
    }
    
    
    public function get_delete_item()
    {
      $channel_id=$this->getChannelID();
      
      return  DB::table('Channel_product_add_update')
      //  ->leftJoin('products as prod','Channel_product_add_update.product_id','=','prod.product_id')
      //->leftJoin('product_inventory','product_inventory.product_id','=','Channel_product_add_update.product_id')
      // ->leftJoin('locations','product_inventory.location_id','=','locations.location_id')
      // ->leftJoin('product_attributes','product_attributes.product_id','=','prod.product_id')
      // ->leftJoin('attributes','product_attributes.attribute_id','=','attributes.attribute_id')
      // ->leftJoin('Channel_category','Channel_category.category_id','=','Channel_product_add_update.category_id')
      ->where('Channel_product_add_update.is_deleted',1)
      ->where('Channel_product_add_update.channel_id',$channel_id)
      ->where('Channel_product_add_update.channel_product_key','<>',empty('channel_product_key'))
      ->where('Channel_product_add_update.is_added',0)
      // ->select(DB::raw('CONCAT(group_concat(product_attributes.value,"$",attributes.name)) as product_attributes'),'prod.name as Title','prod.description as Description','prod.product_id','prod.mrp as Price','prod.image as Image','Channel_category.channel_category_id','product_inventory.available_inventory as qty','prod.upc','locations.location_address','locations.city','prod.sku as sku','Channel_product_add_update.channel_product_key')
      // ->groupBy('prod.product_id')
      ->take(15)
      ->get();
    }
    
    
    
    public function get_updated_qty()
    {
      $channel_id=$this->getChannelID();
      
      $result= DB::table('product_inventory as prodinv')
      ->leftJoin('Channel_product_add_update as cod','prodinv.product_id','=','cod.product_id')
      ->leftJoin('Channel as c','c.channel_id','=','cod.channel_id')
      ->select('cod.channel_product_key as ItemID','prodinv.available_inventory as Quantity','prodinv.product_id as product_id','cod.is_added as is_added')
      ->where('prodinv.is_updated',1)
      ->where('c.channel_id',$channel_id)
      ->take(10)
      ->get();
      
      return  $result;
      
    }
    
    
    
    public function get_updated_price()
    {
      $channel_id=$this->getChannelID();
      
      $result= DB::table('products as prodprice')
      ->leftJoin('Channel_product_add_update as cod','prodprice.product_id','=','cod.product_id')
      ->select('cod.channel_product_key as ItemID','prodprice.mrp as Price','prodprice.product_id as product_id','prodprice.sku as sku')
      ->where('cod.is_added',0)
      ->where('cod.channel_id',$channel_id)
      ->where('cod.channel_product_key','<>',empty('cod.channel_product_key'))
      ->take(25)
      ->get();
      
      return  $result;
      
    }
    
    public function get_updated_image()
    {
      $channel_id=$this->getChannelID();
      
      $result= DB::table('products as prodprice')
      ->leftJoin('Channel_product_add_update as cod','prodprice.product_id','=','cod.product_id')
      ->select('cod.channel_product_key as ItemID','prodprice.product_id as product_id','prodprice.image as image','prodprice.sku as sku')
      ->where('cod.is_added',0)
      ->where('cod.channel_id',$channel_id)
      ->where('cod..channel_product_key','<>',empty('cod.channel_product_key'))
      ->get();
      
      return  $result;
      
    }
    
    public function update_details($sku,$update_condition)
    {
      $sku1=json_decode($sku);
      print_r($sku1);
      if($update_condition=='DeleteProduct')
      {
        foreach($sku1 as $s)
        {
          $s1=DB::table('Channel_product_add_update as cod')
          ->leftjoin('products as pro','pro.product_id','=','cod.product_id')
          ->select('pro.product_id')
          ->where('sku',$s)
          ->first();
          
          DB::table('Channel_product_add_update')
          ->where('product_id',$s1->product_id)
          ->update(array('is_delete'=>0));
        }
      }
      
      elseif($update_condition=='UpdateProduct')
      {
        foreach($sku1 as $s)
        {
          $s1=DB::table('Channel_product_add_update as cod')
          ->leftjoin('products as pro','pro.product_id','=','cod.product_id')
          ->select('pro.product_id')
          ->where('sku',$s)
          ->first();
          
          DB::table('Channel_product_add_update')
          ->where('product_id',$s1->product_id)
          ->update(array('is_update'=>0));
        }
      }
      elseif($update_condition=='AddProduct')
      {
        foreach($sku1 as $s)
        {
          $s1=DB::table('Channel_product_add_update as cod')
          ->leftjoin('products as pro','pro.product_id','=','cod.product_id')
          ->select('pro.product_id')
          ->where('sku',$s)
          ->first();
          
          DB::table('Channel_product_add_update')
          ->where('product_id',$s1->product_id)
          ->update(array('is_added'=>0));
        }
      }
      else
      {
        echo 'No need to update a value';
      }
      
      
    }
    
    
    
    public function getXmlArrayProduct($sku,$Title,$Description,$i)
    {
      
      $OperationType='Update';
      $ConditionType='New';
      $xml_array=array();
      $attributes=$this->getAttributes($sku);
      
      /*   Manufacturer Details  
        
        $manufactuer_name=DB::table('products as pro')
        ->leftjoin('eseal_customer as ec','ec.customer_id','=','pro.manufacturer_id')
        ->select('pro.manufacturer_id','ec.brand_name')
        ->where('pro.sku',$sku)
      ->first();*/
      
      $xml_array['Message']['MessageID']= $i+1;
      $xml_array['Message']['OperationType']=$OperationType;
      $xml_array['Message']['Product']['SKU']= $sku;
      $xml_array['Message']['Product']['LaunchDate']=gmdate(DATE_ATOM,mktime(0,0,0,10,3,2015));
      $xml_array['Message']['Product']['Condition']['ConditionType']=$ConditionType;
      $xml_array['Message']['Product']['DescriptionData']['Title']= $Title;
      $xml_array['Message']['Product']['DescriptionData']['Brand']= 'Storemate'; //$manufactuer_name->brand_name;
      $xml_array['Message']['Product']['DescriptionData']['Description']=$Description;
      $xml_array['Message']['Product']['DescriptionData']['Manufacturer']='Storemate';   //$manufactuer_name->brand_name;
      $xml_array['Message']['Product']['DescriptionData']['MfrPartNumber']=$sku;
      
      
      foreach($attributes as $attr)
      {
        if($attr->name=='material')
        {
          
          $color=$this->getMaterial($sku);
          $xml_array['Message'] ['Product']['ProductData']['Home']['ProductType']['Home']['Material']= $color->value;
        }
        
        $xml_array['Message'] ['Product']['ProductData']['Home']['ProductType']['Home']['VariationData']['VariationTheme']='Size-Color';
        
        if($attr->name=='Color')
        {
          $color=$this->getColor($sku);
          $xml_array['Message'] ['Product']['ProductData']['Home']['ProductType']['Home']['VariationData']['Color']=$color->value;  //$attr->value;
        }
        
        if($attr->name=='Size')
        {
          $size=$this->getSize($sku); 
          $xml_array['Message'] ['Product']['ProductData']['Home']['ProductType']['Home']['VariationData']['Size'] =$size->value; //'Large';
        }
        
        
      }
      
      //$xml_array['Message'] ['Product']['ProductData']['Home']['ProductType']['Home']['VariationData']['Color']='Green';
      // $xml_array['Message'] ['Product']['ProductData']['Home']['ProductType']['Home']['VariationData']['VariationTheme']='Size-Color'; //$attr->value; //
      // $xml_array['Message'] ['Product']['ProductData']['Home']['ProductType']['Home']['Material']= 'leather'; //'leather';
      // $xml_array['Message'] ['Product']['ProductData']['Home']['ProductType']['Home']['VariationData']['Size'] =''; // 'Large';
      // $xml_array['Message'] ['Product']['ProductData']['Home']['ProductType']['Home']['VariationData']['Color'] =''; //'Blue';
      // $xml_array['Message'] ['Product']['ProductData']['Home']['Parentage']= '';//'child';
      return $xml_array;
      
    }
    
    public function getAttributes($sku)
    {
      $productId=substr($sku,4);
      
      return DB::table('product_attributes as pa')
      ->leftjoin('attributes as a','pa.attribute_id','=','a.attribute_id')
      ->select('a.name','pa.value')
      ->where('pa.product_id','=',$productId)
      ->orderBy('a.attribute_id', 'desc')
      ->get();
    }
    
    
    public function getSKUforProduct()
    {
      
      $channel_id= $this->getChannelID();
      
      return DB::table('Channel_product_add_update')
      ->select('product_id')
      ->where('is_added',0)
      ->where('channel_id',$channel_id)
      ->where('channel_product_key','=',' ')
      ->get();
      
    }
    
    public function getMaterial($sku)
    {
      $productId=substr($sku,4);
      
      return DB::table('product_attributes as pa')
      ->leftjoin('attributes as a','pa.attribute_id','=','a.attribute_id')
      ->select('pa.value')
      ->where('pa.product_id','=',$productId)
      ->where('a.name','=','material')
      ->first();
    }
    
    public function getSize($sku)
    {
      $productId=substr($sku,4);
      
      return DB::table('product_attributes as pa')
      ->leftjoin('attributes as a','pa.attribute_id','=','a.attribute_id')
      ->select('pa.value')
      ->where('pa.product_id','=',$productId)
      ->where('a.name','=','Size')
      ->first();
    }
    
    
    public function getColor($sku)
    {
      $productId=substr($sku,4);
      
      return DB::table('product_attributes as pa')
      ->leftjoin('attributes as a','pa.attribute_id','=','a.attribute_id')
      ->select('pa.value')
      ->where('pa.product_id','=',$productId)
      ->where('a.name','=','Color')
      ->first();
    }
    
    public function getXmlArrayUpdate($sku,$Title,$Description,$i,$asin)
    {
      
      $OperationType='Update';
      $type='ASIN';
      $xml_array=array();
      $attributes=$this->getAttributes($sku);
      
      $xml_array['Message']['MessageID']= $i+1;
      $xml_array['Message']['OperationType']=$OperationType;
      $xml_array['Message']['Product']['SKU']= $sku;
      $xml_array['Message']['Product']['StandardProductID']['Type']= $type;
      $xml_array['Message']['Product']['StandardProductID']['Value']=$asin;
      $xml_array['Message']['Product']['DescriptionData']['Title']=$Title;
      $xml_array['Message']['Product']['DescriptionData']['Description']=$Description;
      //      $xml_array['Message']['Product']['DescriptionData']['Brand']='Storemate';   
      
      return $xml_array;
      
    }
    public function getXmlArrayDelete($sku,$i,$asin)
    {
      // echo 'here';exit;
      $OperationType='Delete';
      $type='ASIN';
      $xml_array=array();
      //$attributes=$this->getAttributes($sku);
      $SKU=DB::table('products')
      ->select('sku')
      ->where('product_id',$sku)
      ->first();
      
      $xml_array['Message']['MessageID']= $i+1;
      $xml_array['Message']['OperationType']=$OperationType;
      $xml_array['Message']['Product']['SKU']= $SKU->sku;
      $xml_array['Message']['Product']['StandardProductID']['Type']= $type;
      $xml_array['Message']['Product']['StandardProductID']['Value']=$asin;
      //print_r($xml_array);exit;
      
      return $xml_array;
      
    }
    
    public function getXmlArrayImage($sku,$i)
    {
      
      $OperationType='Update';
      $ImageType='Main';
      $xml_array=array(); 
      
      $xml_array['Message']['MessageID']= $i+1;
      $xml_array['Message']['OperationType']=$OperationType;
      $xml_array['Message'] ['ProductImage']['SKU']= $sku;
      $xml_array['Message'] ['ProductImage']['ImageType']=$ImageType;
      return $xml_array;
    }
    
    public function getXmlArrayPrice($sku,$mrp,$i)
    
    {
      $xml_array=array(); 
      
      $xml_array['Message']['MessageID']= $i+1;
      $xml_array['Message'] ['Price']['SKU']= $sku;
      $xml_array['Message'] ['Price']['StandardPrice']=$mrp;
      
      return $xml_array;
    }
    
    
    public function getXmlArrayInventory($sku,$qty,$i)
    
    {
      $OperationType='Update';
      $fulfillmentlatency='15';
      
      $SKU=DB::table('products')
      ->select('sku')
      ->where('product_id',$sku)
      ->first();
      //print_r($SKU->sku);exit;
      
      $xml_array=array(); 
      $xml_array['Message']['MessageID']= $i+1;
      $xml_array['Message']['OperationType']=$OperationType;
      $xml_array['Message'] ['Inventory']['SKU']= $SKU->sku;//'sku-'.$sku;
      $xml_array['Message'] ['Inventory']['Quantity']=$qty;
      $xml_array['Message'] ['Inventory']['FulfillmentLatency']=$fulfillmentlatency;
      return $xml_array;
    }
    
    public function getXmlArrayCancel($order_id,$channel_order_item_id,$i,$reason)
    
    {
      $StatusCode='Failure';
      
      $xml_array=array(); 
      $xml_array['Message']['MessageID']= $i+1;
      $xml_array['Message']['OrderAcknowledgement']['AmazonOrderID']=$order_id;
      $xml_array['Message']['OrderAcknowledgement']['StatusCode']=$StatusCode;
      $xml_array['Message']['OrderAcknowledgement']['Item']['AmazonOrderItemCode']=$channel_order_item_id;
      $xml_array['Message']['OrderAcknowledgement']['Item']['CancelReason']=$reason;
      
      return $xml_array;
    }
    
    public function invokeGetFeedSubmissionResult($service, $request) 
    {
      try {
        /*  
          ,$StatusCode,$MessagesProcessed,$MessagesSuccessful,$MessagesWithError,$MessagesWithWarning
          echo 'Status Code-----:'.$StatusCode.'<br>';
          echo 'Message Processed----------:'.$MessagesProcessed.'<br>';
          echo 'MessagesWithError---------:'.$MessagesWithError.'<br>';
          echo 'MeassageWithW=-------------:'.$MessagesWithWarning.'<br>';
          if($MessagesWithError==0 && $MessagesWithWarning==0)
          {
          //enter into database
          return $StatusCode;
          }
          else
          {
          // faliure case enter result status value
        }*/
        // && $MessagesWithError==0 && $MessagesWithWarning==0
        
        $response = $service->getFeedSubmissionResult($request);
        // print_r($response); die;
        echo ("Service Response FeedSubmission\n");
                echo ("=============================================================================\n");
        
                echo("        GetFeedSubmissionResultResponse\n");
                if ($response->isSetGetFeedSubmissionResultResult()) {
          $getFeedSubmissionResultResult = $response->getGetFeedSubmissionResultResult(); 
          echo ("            GetFeedSubmissionResult");
          
          if ($getFeedSubmissionResultResult->isSetContentMd5()) {
            echo ("                ContentMd5");
            echo ("                " . $getFeedSubmissionResultResult->getContentMd5() . "\n");
          }
        }
                if ($response->isSetResponseMetadata()) { 
          echo("            ResponseMetadata\n");
                    $responseMetadata = $response->getResponseMetadata();
                    if ($responseMetadata->isSetRequestId()) 
                    {
                        echo("                RequestId\n");
                        echo("                    " . $responseMetadata->getRequestId() . "\n");
          }
        } 
        
                echo("            ResponseHeaderMetadata: " . $response->getResponseHeaderMetadata() . "\n");
        } catch (MarketplaceWebService_Exception $ex) {
        echo("Caught Exception: " . $ex->getMessage() . "\n");
        echo("Response Status Code: " . $ex->getStatusCode() . "\n");
        echo("Error Code: " . $ex->getErrorCode() . "\n");
        echo("Error Type: " . $ex->getErrorType() . "\n");
        echo("Request ID: " . $ex->getRequestId() . "\n");
        echo("XML: " . $ex->getXML() . "\n");
        echo("ResponseHeaderMetadata: " . $ex->getResponseHeaderMetadata() . "\n");
      }
    }
    
    
    
    
    
    public function push_asin($asin, $sku){
      
      $sku1=substr($sku,4);
      
      DB::table('Channel_product_add_update')
      ->where('product_id', $sku1)
      ->update(array('channel_product_key'=> $asin));
      
      echo 'Success Updating ASIN Number';
      
    }
    
    
    
    public function getFeedSubmissionId()
    {
      
      $getFeedSubmissionId =DB::table('amazon_staging_table')
      ->select('feedsubmissionid')
      ->where('is_check',0)
      ->get();
      
      return $getFeedSubmissionId;
    }
    
    
    public function ListOrderItems()
    {
      $channel_id= $this->getChannelID();
      $date=date('Y-m-d');
      $result=array();
      
      $orders = DB::Table('Channel_orders')
      ->select('channel_order_id')
      ->where('channel_id',$channel_id)
      ->where(DB::raw('DATE(order_date)'),$date)
      ->get();
      
      if(!empty($orders))
      {
        
        $tokens=$this->getAccessTokens();
        
        $seller_id=$tokens['seller_id'];
        $marketplace_id=$tokens['marketplace_id'];
        $Key_name=$tokens['key_name'];
        $Key_value=$tokens['key_value'];
        
        foreach($orders as $order)
        {
          
          $ordId = $order->channel_order_id;
          $orderapiname = 'ListOrderItems';
          
          $params = array(
          'AWSAccessKeyId' => $Key_name,
          'Action' => "ListOrderItems",
          'SellerId' => $seller_id,
          'SignatureMethod' => "HmacSHA256",
          'SignatureVersion' => "2",
          'Version'=> "2013-09-01",
          'MarketplaceId.Id.1' => $marketplace_id,
          'Timestamp'=> gmdate("Y-m-d\TH:i:s\Z"),
          'AmazonOrderId'=>$ordId
          
          );
          
          $url_parts = array();
          foreach(array_keys($params) as $key)
          $url_parts[] = $key . "=" . str_replace('%7E', '~', rawurlencode($params[$key]));
          sort($url_parts);
          $url_string = implode("&", $url_parts);
          $string_to_sign = "GET\nmws.amazonservices.in\n/Orders/2013-09-01\n" . $url_string;
          $signature = hash_hmac("sha256", $string_to_sign, $Key_value, TRUE);
          $signature = urlencode(base64_encode($signature));
          $url = "https://mws.amazonservices.in/Orders/2013-09-01" . '?' . $url_string . "&Signature=" . $signature;
          $output = file_get_contents($url);
          $parsed_xml = simplexml_load_string($output);
          $parsed_xml=json_encode($parsed_xml);
          $result[]=$this->insertOrderDetails($orderapiname,$parsed_xml);
        } 
        
      }
      else
      {
        print_r('No OrderItems For Today');
      }
      
      return json_encode($result);
    }
    
    public function insertOrderDetails($apiname,$parsed_xml)
    {
      $channel_id= $this->getChannelID();
      $message=array();
      $i=0;
      
      if($apiname=='ListOrders')
      {
        $item=json_decode($parsed_xml);
        $OrderStatus=$item->OrderStatus;
        if($OrderStatus=='Canceled')
        {     
          $LatestShipDate=$item->LatestShipDate;
          $OrderType=$item->OrderType;
          $PurchaseDate=$item->PurchaseDate;
          $AmazonOrderId=$item->AmazonOrderId;
          $LastUpdateDate=$item->LastUpdateDate;
          $NumberOfItemsShipped=$item->NumberOfItemsShipped;
          $ShipServiceLevel=$item->ShipServiceLevel;
          $OrderStatus=$item->OrderStatus;
          $SalesChannel=$item->SalesChannel;
          $NumberOfItemsUnshipped=$item->NumberOfItemsUnshipped;
          $EarliestShipDate=$item->EarliestShipDate;
          $MarketplaceId=$item->MarketplaceId;
          $FulfillmentChannel=$item->FulfillmentChannel;
          $IsPrime=$item->IsPrime;
          $ShipmentServiceLevelCategory=$item->ShipmentServiceLevelCategory;
          
          $timestamp = strtotime($PurchaseDate);
          date_default_timezone_set("Asia/Calcutta");
          $date=date('Y-m-d H:i:s',$timestamp);
          
          $channelorder= array('channel_id' => $channel_id,
          'channel_order_id' =>$AmazonOrderId, 
          'channel_order_status'=>$OrderStatus,
          'order_date'=>$date);
          //'order_status'=>$OrderStatus 
          
          $channelshippingaddress = array('order_id' =>$AmazonOrderId, 
          'channel_id' => $channel_id);
          
          $channelorderpayment = array('order_id'=>$AmazonOrderId,
          'channel_id' => $channel_id);
          
          $channelshippingdetails=array('order_id'=>$AmazonOrderId,
          'channel_id' => $channel_id,
          'service_name'=> $ShipServiceLevel);
          
        }
        if($OrderStatus=='Unshipped' || $OrderStatus=='Shipped')
        {
          
          $LatestShipDate=$item->LatestShipDate;
          $OrderType=$item->OrderType;
          $PurchaseDate=$item->PurchaseDate;
          $LastUpdateDate=$item->LastUpdateDate;
          $BuyerEmail=$item->BuyerEmail;
          $AmazonOrderId=$item->AmazonOrderId;
          $ShipServiceLevel=$item->ShipServiceLevel;
          $NumberOfItemsShipped=$item->NumberOfItemsShipped;
          //$OrderStatus=$item->OrderStatus;
          $SalesChannel=$item->SalesChannel;
          $ShippedByAmazonTFM=$item->ShippedByAmazonTFM;
          $LatestDeliveryDate=$item->LatestDeliveryDate;
          $NumberOfItemsUnshipped=$item->NumberOfItemsUnshipped;
          $BuyerName=$item->BuyerName;
          $EarliestDeliveryDate=$item->EarliestDeliveryDate;
          //$OrderTotal=$parsed_xml->ListOrdersResult->Orders->Order->OrderTotal;
          $CurrencyCode=$item->OrderTotal->CurrencyCode;
          $Amount=$item->OrderTotal->Amount;
          $IsPremiumOrder=$item->IsPremiumOrder;
          $EarliestShipDate=$item->EarliestShipDate;
          $MarketplaceId=$item->MarketplaceId;
          $FulfillmentChannel=$item->FulfillmentChannel;
          $PaymentMethod=$item->PaymentMethod;
          $StateOrRegion=$item->ShippingAddress->StateOrRegion;
          $Phone=$item->ShippingAddress->Phone;
          $City=$item->ShippingAddress->City;
          $PostalCode=$item->ShippingAddress->PostalCode;
          $CountryCode=$item->ShippingAddress->CountryCode;
          $Name=$item->ShippingAddress->Name;
          $AddressLine1=$item->ShippingAddress->AddressLine1;
          $IsPrime=$item->IsPrime;
          $ShipmentServiceLevelCategory=$item->ShipmentServiceLevelCategory;
          
          $timestamp = strtotime($PurchaseDate);
          date_default_timezone_set("Asia/Calcutta");
          $date=date('Y-m-d H:i:s',$timestamp);
          
          $channelorder= array('channel_id' => $channel_id,
          'channel_order_id' =>$AmazonOrderId, 
          'channel_order_status'=>$OrderStatus,
          'payment_method'=>$PaymentMethod,
          'total_amount'=> $Amount,
          'order_date'=>$date,
          'currency_code'=>$CurrencyCode,
          'order_status'=>$OrderStatus
          );
          
          if(!empty($AddressLine2))
          {
            
            $channelshippingaddress = array('order_id' =>$AmazonOrderId, 
            'name'=>$Name,
            'channel_id' => $channel_id,
            'address1'=>$AddressLine1,
            'address2'=>$AddressLine2,
            'city'=>$City,
            'state'=>$StateOrRegion,
            'country'=>$CountryCode,
            'pincode'=>$PostalCode,
            'phone'=>$Phone,
            'email'=>$BuyerEmail);
          }
          else
          {
            $channelshippingaddress = array('order_id' =>$AmazonOrderId, 
            'name'=>$Name,
            'channel_id' => $channel_id,
            'address1'=>$AddressLine1,
            'city'=>$City,
            'state'=>$StateOrRegion,
            'country'=>$CountryCode,
            'pincode'=>$PostalCode,
            'phone'=>$Phone,
            'email'=>$BuyerEmail);
          }
          
          
          
          $channelorderpayment = array('order_id'=>$AmazonOrderId,
          'channel_id' => $channel_id,
          'payment_method' =>$PaymentMethod,
          'payment_currency' => $CurrencyCode,
          'amount' => $Amount,
          'buyer_email'=> $BuyerEmail,
          'buyer_name'=> $Name,
          'buyer_phone' =>$Phone
          );
          
          
          
          $channelshippingdetails=array('order_id'=>$AmazonOrderId,
          'channel_id' => $channel_id,
          'service_name'=> $ShipServiceLevel);
          
        }
        
        
        if($OrderStatus=='Pending')
        {
          
          $LatestShipDate=$item->LatestShipDate;
          $OrderType=$item->OrderType;
          $PurchaseDate=$item->PurchaseDate;
          $AmazonOrderId=$item->AmazonOrderId;
          $LastUpdateDate=$item->LastUpdateDate;
          $NumberOfItemsShipped=$item->NumberOfItemsShipped;
          $ShipServiceLevel=$item->ShipServiceLevel;
          $OrderStatus=$item->OrderStatus;
          $SalesChannel=$item->SalesChannel;
          $NumberOfItemsUnshipped=$item->NumberOfItemsUnshipped;
          $IsPremiumOrder=$item->IsPremiumOrder;
          $EarliestShipDate=$item->EarliestShipDate;
          $MarketplaceId=$item->MarketplaceId;
          $FulfillmentChannel=$item->FulfillmentChannel;
          $IsPrime=$item->IsPrime;
          $ShipmentServiceLevelCategory=$item->ShipmentServiceLevelCategory;
          
          $timestamp = strtotime($PurchaseDate);
          date_default_timezone_set("Asia/Calcutta");
          $date=date('Y-m-d H:i:s',$timestamp);
          
          $channelorder= array('channel_id' => $channel_id,
          'channel_order_id' =>$AmazonOrderId, 
          'channel_order_status'=>$OrderStatus,
          'order_date'=>$date,
          'order_status'=>$OrderStatus
          );
          
          $channelshippingaddress = array('order_id' =>$AmazonOrderId, 
          'channel_id' => $channel_id
          );
          
          
          $channelorderpayment = array('order_id'=>$AmazonOrderId,
          'channel_id' => $channel_id
          );
          
          $channelshippingdetails=array('order_id'=>$AmazonOrderId,
          'channel_id' => $channel_id,
          'service_name'=> $ShipServiceLevel);
        }
        if(!empty($AmazonOrderId))
        {
          $order_exists = DB::table('Channel_orders')->where('channel_order_id',$AmazonOrderId)->get();
          
          if(!empty($order_exists))
          {
            echo 'Updating Rows<br>';
            
            
            DB::Table('Channel_orders')
            ->where('channel_order_id',$AmazonOrderId)
            ->update($channelorder);
            
            DB::Table('Channel_orders_shipping_address')
            ->where('order_id',$AmazonOrderId)
            ->update($channelshippingaddress);
            
            DB::Table('Channel_order_payment')
            ->where('order_id',$AmazonOrderId)
            ->update($channelorderpayment);
            
            DB::Table('Channel_order_shipping_details')
            ->where('order_id',$AmazonOrderId)
            ->update($channelshippingdetails);
          }
          
          else
          {
            echo 'Inserting Rows <br>';
            
            DB::Table('Channel_orders')
            ->insert($channelorder);
            
            DB::Table('Channel_orders_shipping_address')
            ->insert($channelshippingaddress);
            
            DB::Table('Channel_order_payment')
            ->insert($channelorderpayment);
            
            DB::Table('Channel_order_shipping_details')
            ->insert($channelshippingdetails);
            
          }
        }
        
        
      }
      
      
      elseif($apiname=='ListOrderItems')
      {
        $parsed_xml=json_decode($parsed_xml);
        $AmazonOrderId = $parsed_xml->ListOrderItemsResult->AmazonOrderId;
        
        $AmazonOrderIdSuccess= DB::Table('Channel_orders')
        ->select('channel_order_status')
        ->where('channel_order_id',$AmazonOrderId)
        ->get();
        
        if($AmazonOrderIdSuccess[0]->channel_order_status=='Unshipped')
        {
          if(count($parsed_xml->ListOrderItemsResult->OrderItems->OrderItem) == 1)
          { 
            //foreach ($parsed_xml->ListOrderItemsResult->OrderItems as $item)
            //{
            //echo 'in single '.$AmazonOrderId;
            $item=$parsed_xml->ListOrderItemsResult->OrderItems->OrderItem;  
            $QuantityOrdered = $item->QuantityOrdered;
            $Title = $item->Title;
            $ShippingTaxCurrencyCode = $item->ShippingTax->CurrencyCode;
            $ShippingTaxAmount = $item->ShippingTax->Amount;
            $PromotionDiscountCurrencyCode = $item->PromotionDiscount->CurrencyCode;
            $PromotionDiscountAmount = $item->PromotionDiscount->Amount;
            $ConditionId = $item->ConditionId;
            $ASIN = $item->ASIN;
            $SellerSKU = $item->SellerSKU;
            $OrderItemId = $item->OrderItemId ;
            $OrderItemIdCurrencyCode = $item->GiftWrapTax->CurrencyCode;
            $OrderItemIdAmount = $item->GiftWrapTax->Amount;
            $QuantityShipped = $item->QuantityShipped;
            $QuantityShippedCurrencyCode = $item->ShippingPrice->CurrencyCode;
            $QuantityShippedAmount = $item->ShippingPrice->Amount;
            $GiftWrapPriceCurrencyCode = $item->GiftWrapPrice->CurrencyCode;
            $GiftWrapPriceAmount = $item->GiftWrapPrice->Amount;
            $ConditionSubtypeId = $item->ConditionSubtypeId;
            $ItemPriceCurrencyCode = $item->ItemPrice->CurrencyCode;
            $ItemPriceAmount = $item->ItemPrice->Amount;
            $ItemTaxCurrencyCode = $item->ItemTax->CurrencyCode;
            $ItemTaxAmount = $item->ItemTax->Amount;
            $ShippingDiscountCurrencyCode = $item->ShippingDiscount->CurrencyCode;
            $ShippingDiscountAmount = $item->ShippingDiscount->Amount;
            
            if(!empty($AmazonOrderId))
            { 
              $order_exists = DB::table('Channel_order_details')->where('order_id',$AmazonOrderId)->get();
              
              if(!empty($order_exists))
              { 
                DB::Table('Channel_order_details')
                ->where('order_id',$AmazonOrderId)
                ->update(array( 'channel_id' => $channel_id,
                'order_id'=>$AmazonOrderId,
                'channel_item_id'=>$ASIN,
                'channel_order_item_id'=>$OrderItemId,
                'quantity'=>$QuantityOrdered,
                'price'=>$ItemPriceAmount,
                'sco_item_id'=> $SellerSKU,
                'discount_price'=>$PromotionDiscountAmount,
                'tax'=>$ItemTaxAmount                       
                ));
                print_r('Success Updating Orders Items');
              }
              
              else
              { 
                
                $product_availability=DB::table('Channel_product_add_update as Cpau')
                ->leftJoin('product_inventory as pi','pi.product_id','=','Cpau.product_id')
                ->leftJoin('products as pd','pd.product_id','=','Cpau.product_id')
                ->where('channel_product_key',$ASIN)
                //  ->where('location_id','105') //due to repaeation
                //  ->get();
                ->first();
                // print_r($product_availability);exit;
                $products['sku']        = $product_availability->sku;
                $products['channelId']  = '1';
                $products['order_id']   = $AmazonOrderId;
                $products['channelitemid']   = $OrderItemId;
                $products['scoitemid']      = $SellerSKU;
                $products['quantity']      = $QuantityOrdered;
                $products['price'] = $ItemPriceAmount;
                $products['sellprice'] = " ";
                $products['discounttype']= " ";
                $products['discountprice']= " ";
                $products['tax']= " ";
                $products['subtotal'] = " ";
                $products['channelcancelitem'] = " ";
                $products['total']= $product_availability->mrp*$QuantityOrdered;
                $products['shippingcompanyname']= " ";
                $products['servicename']= " ";
                $products['servicecost']= " ";
                $products['dispatchdate']= " ";
                $products['mintimetodispatch']= " ";
                $products['maxtimetodispatch']= " ";
                $products['timeunits']= " ";        
                
                $final_array[] = $products;
                
                DB::Table('Channel_order_details')
                ->insert(array( 'channel_id' => $channel_id,
                'order_id'=>$AmazonOrderId,
                'channel_item_id'=>$ASIN,
                'channel_order_item_id'=>$OrderItemId,
                'quantity'=>$QuantityOrdered,
                'price'=>$ItemPriceAmount,
                'sco_item_id'=> $SellerSKU,
                'discount_price'=>$PromotionDiscountAmount,
                'tax'=>$ItemTaxAmount,   
                'channel_order_status'=>'Order Placed' 
                )); 
                
                print_r('Success Inserting Orders Items');
                
                if(!empty($product_availability))
                {
                  $i=0;
                  $checkInventory=$this->checkInventoryAvailability($product_availability,$i,$QuantityOrdered,$AmazonOrderId);
                  $catResult=json_decode($checkInventory);
                  // if($catResult->Message!="Out of Stock for the following products.")
                  if($catResult->Message!="Stock not available")
                  {
                    
                    $place_order=$this->placeOrder($AmazonOrderId,$i,$product_availability,$QuantityOrdered,$final_array);
                    $place=json_decode($place_order);
                    // print_r($place);exit;
                    if($place->Message =='Successfully placed order.')
                    {
                      print_r('Order Placed '.$AmazonOrderId);   
                      //$ord_id=substr($place->Message,31);
                      //$order = 'Order_id';
                      $ord_id=$place->order_id;
                      
                      $erp_ord_id=DB::Table('Channel_orders')
                      ->where('channel_order_id',$AmazonOrderId)
                      ->update(array('erp_order_id'=>$ord_id));
                    }
                    else
                    {
                      print_r('Order Not Placed '.$AmazonOrderId);
                    }
                    $message[0]['msg'] =  'StockAvailable'; 
                    $message[0]['orderid'] = $AmazonOrderId;
                    $message[0]['orderitemid']=$OrderItemId;
                  }
                  else
                  {
                    $order_status="StockUnavailable";
                    $message[0]['msg'] =$order_status;
                    $message[0]['orderid'] = $AmazonOrderId;
                    $message[0]['orderitemid']=$OrderItemId;
                  }
                }
              }
            }
            
            //--} 
          }
          
          else
          {
            $products = array();
            $check_outstock= array();
            $final_array = array();
            $i=0;
            foreach ($parsed_xml->ListOrderItemsResult->OrderItems->OrderItem as $item)
            {    
              //echo 'in multiple '.$AmazonOrderId;
              $QuantityOrdered = $item->QuantityOrdered;
              $Title = $item->Title;
              $ShippingTaxCurrencyCode = $item->ShippingTax->CurrencyCode;
              $ShippingTaxAmount = $item->ShippingTax->Amount;
              $PromotionDiscountCurrencyCode = $item->PromotionDiscount->CurrencyCode;
              $PromotionDiscountAmount = $item->PromotionDiscount->Amount;
              $ConditionId = $item->ConditionId;
              $ASIN = $item->ASIN;
              $SellerSKU = $item->SellerSKU;
              $OrderItemId = $item->OrderItemId ;
              $OrderItemIdCurrencyCode = $item->GiftWrapTax->CurrencyCode;
              $OrderItemIdAmount = $item->GiftWrapTax->Amount;
              $QuantityShipped = $item->QuantityShipped;
              $QuantityShippedCurrencyCode = $item->ShippingPrice->CurrencyCode;
              $QuantityShippedAmount = $item->ShippingPrice->Amount;
              $GiftWrapPriceCurrencyCode = $item->GiftWrapPrice->CurrencyCode;
              $GiftWrapPriceAmount = $item->GiftWrapPrice->Amount;
              $ConditionSubtypeId = $item->ConditionSubtypeId;
              $ItemPriceCurrencyCode = $item->ItemPrice->CurrencyCode;
              $ItemPriceAmount = $item->ItemPrice->Amount;
              $ItemTaxCurrencyCode = $item->ItemTax->CurrencyCode;
              $ItemTaxAmount = $item->ItemTax->Amount;
              $ShippingDiscountCurrencyCode = $item->ShippingDiscount->CurrencyCode;
              $ShippingDiscountAmount = $item->ShippingDiscount->Amount;
              
              
              
              if(!empty($OrderItemId))
              {
                
                $order_exists = DB::table('Channel_order_details')->where('channel_order_item_id',$OrderItemId)->get();
                
                if(!empty($order_exists))
                {    
                  
                  DB::Table('Channel_order_details')
                  ->where('order_id',$AmazonOrderId)
                  ->update(array( 'channel_id' => $channel_id,
                  'order_id'=>$AmazonOrderId,
                  'channel_item_id'=>$ASIN,
                  'channel_order_item_id'=>$OrderItemId,
                  'quantity'=>$QuantityOrdered,
                  'price'=>$ItemPriceAmount,
                  'sco_item_id'=> $SellerSKU,
                  'discount_price'=>$PromotionDiscountAmount,
                  'tax'=>$ItemTaxAmount                       
                  ));
                  print_r('Success Updating Orders Items');
                }
                
                else
                {         
                  $i=0;
                  $product_availability=DB::table('Channel_product_add_update as Cpau')
                                    ->leftJoin('product_inventory as pi','pi.product_id','=','Cpau.product_id')
                                    ->leftJoin('products as pd','pd.product_id','=','Cpau.product_id')
                                    ->where('channel_product_key',$ASIN)
                                    //->where('location_id','105') //due to repaeation
                  // ->get();
                                    ->first();
                  //  print_r($product_availability);exit; 
                  
                  $products['sku']        = $product_availability->sku;
                  $products['channelId']  = '1';
                  $products['order_id']   = $AmazonOrderId;
                  $products['channelitemid']   = $OrderItemId;
                  $products['scoitemid']      = $SellerSKU;
                  $products['quantity']      = $QuantityOrdered;
                  $products['price'] = $ItemPriceAmount; //$product_availability[$i]->mrp;
                  $products['sellprice'] = " ";
                  $products['discounttype']= " ";
                  $products['discountprice']= " ";
                  $products['tax']= " ";
                  $products['subtotal'] = " ";
                  $products['channelcancelitem'] = " ";
                  $products['total']= $product_availability->mrp*$QuantityOrdered;
                  $products['shippingcompanyname']= " ";
                  $products['servicename']= " ";
                  $products['servicecost']= " ";
                  $products['dispatchdate']= " ";
                  $products['mintimetodispatch']= " ";
                  $products['maxtimetodispatch']= " ";
                  $products['timeunits']= " ";
                  //echo "multiple item <br>"; print_r($product_availability); 
                  $checkInventory=$this->checkInventoryAvailability($product_availability,$i,$QuantityOrdered,$AmazonOrderId);
                  $catResult=json_decode($checkInventory);
                  // print_r($catResult);exit;
                  
                  $check_outstock[] = $catResult->Message;
                  $final_array[] = $products;
                  
                  DB::Table('Channel_order_details')
                  ->insert(array( 'channel_id' => $channel_id,
                  'order_id'=>$AmazonOrderId,
                  'channel_item_id'=>$ASIN,
                  'channel_order_item_id'=>$OrderItemId,
                  'quantity'=>$QuantityOrdered,
                  'price'=>$ItemPriceAmount,
                  'sco_item_id'=> $SellerSKU,
                  'discount_price'=>$PromotionDiscountAmount,
                  'tax'=>$ItemTaxAmount,
                  'channel_order_status'=>'Order Placed'
                  )); 
                  
                  print_r('Success Inserting Orders Items');
                  
                  
                }
                
              } 
            }
            
            $outofstock=strpos(json_encode($check_outstock),'Stock not available');
            // print_r($outofstock);
            if(!empty($product_availability))
            {
              $i=0;
              //$checkInventory=$this->checkInventoryAvailability($product_availability,$i,$QuantityOrdered,$AmazonOrderId);
              //$catResult=json_decode($checkInventory);
              
              //if($catResult[$i]->Message!="Out of Stock for the following products.")
              if(empty($outofstock))
              {
                $place_order=$this->placeOrder($AmazonOrderId,$i,$product_availability,$QuantityOrdered,$final_array);
                $place=json_decode($place_order);
                
                // if(substr($place->Message,0,25)=="Successfully placed order")
                if($place->Message=="Successfully placed order.")
                {
                  print_r('Order Placed '.$AmazonOrderId);
                  
                  //$order = 'Order Id';
                  $ord_id=$place->order_id;
                  
                  $erp_ord_id=DB::Table('Channel_orders')
                  ->where('channel_order_id',$AmazonOrderId)
                  ->update(array('erp_order_id'=>$ord_id));
                  
                }
                else
                {
                  print_r('Order Not Placed '.$AmazonOrderId);
                }
                $message[0]['msg'] = 'StockAvailable';
                $message[0]['orderid']=$AmazonOrderId;
                $message[0]['orderitemid']=$OrderItemId;
              }
              else
              {
                $order_status="StockUnavailable";
                $message[0]['msg'] = $order_status;
                $message[0]['orderid']=$AmazonOrderId;
                $message[0]['orderitemid']=$OrderItemId;
                
              }
              
            }
            
            //}
            // }
            // }
            
            
          }
          //return json_encode($message);
        }
        
        
        if($AmazonOrderIdSuccess[0]->channel_order_status=='Canceled')
        {
          
          if(count($parsed_xml->ListOrderItemsResult->OrderItems->OrderItem) == 1)
          { 
            //echo 'here';exit;
            $item=$parsed_xml->ListOrderItemsResult->OrderItems->OrderItem;
            $QuantityOrdered = $item->QuantityOrdered;
            $ASIN = $item->ASIN;
            $SellerSKU = $item->SellerSKU;
            $OrderItemId = $item->OrderItemId ;
            
            $cancelOrder=$this->cancelOrder($AmazonOrderId,$SellerSKU,$ASIN,$OrderItemId,$QuantityOrdered);   
          }
          
          else
          {
            foreach ($parsed_xml->ListOrderItemsResult->OrderItems->OrderItem as $item) 
            {
              $QuantityOrdered = $item->QuantityOrdered;
              $ASIN = $item->ASIN;
              $SellerSKU = $item->SellerSKU;
              $OrderItemId = $item->OrderItemId ;
              
              $cancelOrder=$this->cancelOrder($AmazonOrderId,$SellerSKU,$ASIN,$OrderItemId,$QuantityOrdered);         
            }
          }
        }
        
        
      }
      
      elseif($apiname=='GetOrder')
      {
        $parsed_xml=json_decode($parsed_xml);
        $item=$parsed_xml->GetOrderResult->Orders->Order;
        $OrderStatus=$item->OrderStatus;
        $AmazonOrderId=$item->AmazonOrderId;
        
        $updateOrderStatus=DB::table('Channel_orders')
        ->where('channel_order_id',$AmazonOrderId)
        ->update(array('channel_order_status'=>$OrderStatus));
        
        return 'Successfully Updated Order Status for ID '.$AmazonOrderId;
        
      }
      
      return json_encode($message);
      
    }
    
    
    public function listOrders()
    {
      try
      {
        //$CreatedAfter = '2015-08-29T19:00:00Z';
        $CreatedAfter=date('Y-m-d')."T".date('00:00:00').".00Z";
        $tokens=$this->getAccessTokens();
        
        $seller_id=$tokens['seller_id'];
        $marketplace_id=$tokens['marketplace_id'];
        $Key_name=$tokens['key_name'];
        $Key_value=$tokens['key_value'];
        
        $orderapiname='ListOrders';
        $result='';
        
        $params = array(
        'AWSAccessKeyId' => $Key_name,
        'Action' => "ListOrders",
        'SellerId' => $seller_id,
        'SignatureMethod' => "HmacSHA256",
        'SignatureVersion' => "2",
        'Version'=> "2013-09-01",
        'MarketplaceId.Id.1' => $marketplace_id,
        'Timestamp'=> gmdate("Y-m-d\TH:i:s\Z"),
        'CreatedAfter'=>$CreatedAfter
        );
        
        $url_parts = array();
        foreach(array_keys($params) as $key)
        $url_parts[] = $key . "=" . str_replace('%7E', '~', rawurlencode($params[$key]));
        sort($url_parts);
        
        $url_string = implode("&", $url_parts);
        $string_to_sign = "GET\nmws.amazonservices.in\n/Orders/2013-09-01\n" . $url_string;
        $signature = hash_hmac("sha256", $string_to_sign,$Key_value, TRUE);
        $signature = urlencode(base64_encode($signature));
        $url = "https://mws.amazonservices.in/Orders/2013-09-01" . '?' . $url_string . "&Signature=" . $signature;
        $output = file_get_contents($url);
        $parsed_xml = simplexml_load_string($output);
        $xml=$parsed_xml->ListOrdersResult->Orders->Order;
        
        if(!empty($xml))
        {
          $arr=array();
          foreach($xml as $val)
          {
            $parsed_xml=json_encode($val);
            $result=$this->insertOrderDetails($orderapiname,$parsed_xml);
          }
        }
        
        else
        {
          $result='No Orders For Today';
        }
        
        return $result;
      }
      catch(Exception $e){
        
        $message=$e->getMessage(); 
      }
    }
    
    
    
    
    public function getOrder($order_id)
    {
      try
      {
        $tokens=$this->getAccessTokens();
        
        $seller_id=$tokens['seller_id'];
        $marketplace_id=$tokens['marketplace_id'];
        $Key_name=$tokens['key_name'];
        $Key_value=$tokens['key_value'];
        
        $orderapiname='GetOrder';
        
        
        $params = array(
        'AWSAccessKeyId' => $Key_name,
        'Action' => "GetOrder",
        'SellerId' => $seller_id,
        'SignatureMethod' => "HmacSHA256",
        'SignatureVersion' => "2",
        'Version'=> "2013-09-01",
        'Timestamp'=> gmdate("Y-m-d\TH:i:s\Z"),
        'AmazonOrderId.Id.1'=>$order_id
        );
        
        $url_parts = array();
        foreach(array_keys($params) as $key)
        $url_parts[] = $key . "=" . str_replace('%7E', '~', rawurlencode($params[$key]));
        sort($url_parts);
        
        $url_string = implode("&", $url_parts);
        $string_to_sign = "GET\nmws.amazonservices.in\n/Orders/2013-09-01\n" . $url_string;
        $signature = hash_hmac("sha256", $string_to_sign,$Key_value, TRUE);
        $signature = urlencode(base64_encode($signature));
        $url = "https://mws.amazonservices.in/Orders/2013-09-01" . '?' . $url_string . "&Signature=" . $signature;
        $output = file_get_contents($url);
        $parsed_xml = simplexml_load_string($output);
        //print_r($parsed_xml);exit;
        $parsed_xml=json_encode($parsed_xml);
        $result=$this->insertOrderDetails($orderapiname,$parsed_xml);
        return $result;
      }
      catch(Exception $e)
      {
        
        $message=$e->getMessage(); 
      }
    }
    
    
    
    
    
    
    public function getAccessTokens()
    {
      $channel_id=$this->getChannelID();
      
      $seller_id=  DB::table('Channel_configuration')
      ->where(array('Key_name'=>'seller_id','channel_id'=>$channel_id))
      -> pluck('Key_value');
      
      $marketplace_id=  DB::table('Channel_configuration')
      ->where(array('Key_name'=>'marketplace_id','channel_id'=>$channel_id))
      -> pluck('Key_value');
      
      $AWSAccessKeyId=  DB::table('Channel_configuration')
      ->where(array('Key_name'=>'AWSAccessKeyId','channel_id'=>$channel_id))
      -> pluck('Key_value');
      
      $secret_key=  DB::table('Channel_configuration')
      ->where(array('Key_name'=>'Secret Key','channel_id'=>$channel_id))
      -> pluck('Key_value');
      
      $headers=array(
      'seller_id'=>$seller_id,
      'marketplace_id'=>$marketplace_id,
      'key_name'=>$AWSAccessKeyId,
      'key_value'=>$secret_key);
      
      return $headers;
      
    }
    
    public function getChannelID()
    {
      $channel_id= DB::Table('Channel')->select('channel_id')->where('channnel_name','amazon')->get();
      $channel_id= $channel_id[0]->channel_id;
      return $channel_id;
    }
    
    
    public function getPaymentZoneDetails($postcode)
    {
      
      $state_name= DB::table('cities_pincodes')
      ->where('PinCode',$postcode)
      ->pluck('State');
      
      $payment_state_name = ucwords(strtolower($state_name));
      
      $zones = DB::table('zone')
      ->where('name',$state_name)->first();
      
      return $zones;
      
    }
    
    public function checkInventoryAvailability($product_availability,$i,$QuantityOrdered,$AmazonOrderId)
    {
      //$baseurl= $_SERVER['SERVER_NAME'];
      //$baseurl= 'dev2.esealinc.com';
      //$form_url="http://".$baseurl."/dmapi/checkInventoryAvailability";
      //$data_to_post['api_key'] = 'orient_developer_1';
      //$data_to_post['secret_key'] = '8gju!eDX?bc9_n#%';
      
      
      $url=$this->getDMAccess();
      $baseurl=$url['api_key']->channel_url;
      $form_url=$baseurl."checkInventoryAvailability";
      $data_to_post['api_key'] = $url['api_key']->Key_value;
      $data_to_post['secret_key'] = $url['secret_key']->Key_value;
      
      
      $productData = json_encode(array(
      'sku'=>$product_availability->sku,
      'quantity'=>$product_availability->available_inventory,
      'price'=>$product_availability->mrp,
      'total'=>($product_availability->mrp*$product_availability->available_inventory)));
      
      $pincode=DB::table('Channel_orders_shipping_address')
      ->select('pincode')
      ->where('order_id',$AmazonOrderId)
      ->get(); 
      
      
      $data_to_post['is_blocked'] =1;
      $data_to_post['product_data'] = $productData;
      //$data_to_post['pincode']=$pincode[0]->pincode;
      //500022/500001/110001
      $data_to_post['pincode']=110001;
      
      $curl = curl_init();
      
      curl_setopt($curl,CURLOPT_URL, $form_url);
      
      curl_setopt($curl,CURLOPT_RETURNTRANSFER, TRUE);
      
      curl_setopt($curl,CURLOPT_POST, sizeof($data_to_post));
      
      curl_setopt($curl,CURLOPT_POSTFIELDS, $data_to_post);
      
      $catResult = curl_exec($curl);
      
      curl_close($curl);
      
      print_r($catResult);
      
      return $catResult;                    
      
    }
    
    
    public function placeOrder($AmazonOrderId,$i,$product_availability,$QuantityOrdered,$final_array='')
    {
      //$baseurl= $_SERVER['SERVER_NAME']; 
      //$baseurl="dev2.esealinc.com";
      //$form_url="http://".$baseurl."/dmapi/placeOrder";
      //$data['api_key'] = 'orient_developer_1';
      // $data['secret_key'] = '8gju!eDX?bc9_n#%';
      
      $url=$this->getDMAccess();
      $baseurl=$url['api_key']->channel_url;
      $form_url=$baseurl."placeOrder";
      $data['api_key'] = $url['api_key']->Key_value;
      $data['secret_key'] = $url['secret_key']->Key_value;
      
      $details= DB::table('Channel_orders as co')
      ->leftJoin('Channel_orders_shipping_address as cosa','co.channel_order_id','=','cosa.order_id')
      ->leftjoin('Channel_order_shipping_details as cosd','co.channel_order_id','=','cosd.order_id')
      ->leftjoin('Channel_order_payment as cop','co.channel_order_id','=','cop.order_id')
      ->where('co.channel_order_id',$AmazonOrderId)
      ->get();
      
      $state_name=DB::table('cities_pincodes')
      ->where('PinCode',$details[0]->pincode)
      ->pluck('State');
      
      $state_name= ucwords(strtolower($state_name));
      
      $query = DB::table('zone')
      ->where('name',$state_name)->get();
      
      $customer_info['suffix']= 'Ms';
      $customer_info['first_name']=$details[0]->buyer_name; //"First";
      $customer_info['middle_name']='';
      $customer_info['last_name']='';
      $customer_info['channel_user_id']=$details[0]->buyer_email;
      $customer_info['email_address']=$details[0]->buyer_email;
      $customer_info['mobile_no']=$details[0]->buyer_phone;
      $customer_info['dob']='';
      $customer_info['channel_id']=$details[0]->channel_id;
      $customer_info['gender']='';
      $customer_info['registered_date']='';
      
      
      $address_info[0]['address_type']='shipping';
      $address_info[0]['first_name']='test shipping first';
      $address_info[0]['middle_name']='test shipping middle';
      $address_info[0]['last_name']='test shipping lastname';
      $address_info[0]['email']='test shipping email';
      $address_info[0]['address1']='test shipping streetaddress';
      $address_info[0]['address2']='test shipping streetaddress2';
      $address_info[0]['city']='test shipping city';
      $address_info[0]['state']='test shipping state';
      $address_info[0]['phone']='test shipping phonenumber';
      $address_info[0]['pincode']='test shipping postcode';
      $address_info[0]['country']='countryID';
      $address_info[0]['company']='company';
      $address_info[0]['mobile_no']='mobile_no';
      
      $address_info[1]['address_type']='billing';
      $address_info[1]['first_name']='test shipping first';
      $address_info[1]['middle_name']='test shipping middle';
      $address_info[1]['last_name']='test shipping lastname';
      $address_info[1]['email']='test shipping email';
      $address_info[1]['address1']='test shipping streetaddress';
      $address_info[1]['address2']='test shipping streetaddress2';
      $address_info[1]['city']='test shipping city';
      $address_info[1]['state']='test shipping state';
      $address_info[1]['phone']='test shipping phonenumber';
      $address_info[1]['pincode']='test shipping postcode';
      $address_info[1]['country']='countryID';
      $address_info[1]['company']='company';
      $address_info[1]['mobile_no']='mobile_no';
            
      $order_info['channelid']=$details[0]->channel_id;
      $order_info['channelorderid']=$details[0]->channel_order_id;
      $order_info['orderstatus']=$details[0]->order_status;
      $order_info['orderdate']=$details[0]->order_date;
      $order_info['paymentmethod']=$details[0]->payment_method;
      $order_info['shippingcost']=$details[0]->shipping_cost;
      $order_info['subtotal']=$details[0]->sub_total;
      $order_info['tax']=$details[0]->tax;
      $order_info['totalamount']=$details[0]->total_amount;
      $order_info['currencycode']=$details[0]->currency_code;
      $order_info['channelorderstatus']='Placed';//$details[0]->channel_order_status;
      $order_info['updateddate']='';
      $order_info['gdsorderid']=$details[0]->channel_order_id;
      $order_info['channelcustid']='4';
      $order_info['createddate']='';
      
      $payment_info['order_id']=$details[0]->channel_order_id;;
      $payment_info['channelid']=$details[0]->channel_id;
      $payment_info['paymentmethod']=$details[0]->payment_method;
      $payment_info['paymentstatus']=$details[0]->payment_status;
      $payment_info['paymentcurrency']=$details[0]->payment_currency;
      $payment_info['amount']=$details[0]->amount;
      $payment_info['buyeremail']=$details[0]->buyer_email;
      $payment_info['buyername']=$details[0]->buyer_name;
      $payment_info['buyerphone']=$details[0]->buyer_phone;
      $payment_info['transactionId']='123';
      $payment_info['paymentDate']='';
      
      
      $order_data['product_info']=$final_array;
      $order_data['customer_info'] = $customer_info;
      $order_data['address_info'] = $address_info;
      $order_data['order_info'] = $order_info;
      // $order_data['product_info']= $final_array ;
      $order_data['payment_info'] = $payment_info;
      
      $order_data_req = json_encode($order_data);
      $data['orderdata'] = $order_data_req;
      $curl = curl_init();
      curl_setopt($curl,CURLOPT_URL, $form_url);
      curl_setopt($curl,CURLOPT_RETURNTRANSFER, TRUE);
      curl_setopt($curl,CURLOPT_POST, sizeof($data));
      curl_setopt($curl,CURLOPT_POSTFIELDS, $data);
      $catResult = curl_exec($curl);
      curl_close($curl);
      print_r($catResult);
      return $catResult;
    }
    
    
    public function cancelOrder($AmazonOrderId,$SellerSKU,$ASIN,$OrderItemId,$QuantityOrdered)
    {
      $order=DB::Table('Channel_order_details')
      ->select('channel_order_status','cancelReason')
      ->where('channel_order_item_id',$OrderItemId)
      ->where('order_id',$AmazonOrderId)
      ->get();
      
      $order_details=DB::table('Channel_order_details')
      ->where('order_id',$AmazonOrderId)
      ->where('channel_order_item_id',$OrderItemId)
      ->get();
      
      $channel_order_status=$order[0]->channel_order_status;
      $cancelReason=$order[0]->cancelReason;
      
      
      $order_status=DB::table('Channel_orders')
      ->where('channel_order_id',$AmazonOrderId)
      ->get();
      
      if($order_status[0]->channel_order_status=='Canceled')
      {
        if(!empty($order_details))
        {
          if($channel_order_status=='Order Placed' && $cancelReason=='BuyerCanceled' && $QuantityOrdered ==0)
          {
            //$baseurl= $_SERVER['SERVER_NAME'];     
            // $baseurl= 'dev2.esealinc.com';            
            // $form_url="http://".$baseurl."/dmapi/cancelOrder";
            
            // $data['api_key'] = 'orient_developer_1';
            //$data['secret_key'] = '8gju!eDX?bc9_n#%';     
            
            
            $url=$this->getDMAccess();
            $baseurl=$url['api_key']->channel_url;
            $form_url=$baseurl."cancelOrder";
            $data['api_key'] = $url['api_key']->Key_value;
            $data['secret_key'] = $url['secret_key']->Key_value;  
            
            $order_id= DB::Table('Channel_orders')
            ->select('erp_order_id')
            ->where('channel_order_id',$AmazonOrderId)
            ->get();      
            
            $data['order_id'] =  $order_id[0]->erp_order_id;
            
            $curl = curl_init();
            
            curl_setopt($curl,CURLOPT_URL, $form_url);
            
            curl_setopt($curl,CURLOPT_RETURNTRANSFER, TRUE);
            
            curl_setopt($curl,CURLOPT_POST, sizeof($data));
            
            curl_setopt($curl,CURLOPT_POSTFIELDS, $data);
            
            $catResult = curl_exec($curl);
            
            curl_close($curl);
            
            print_r($catResult);
            
            $catResult = json_decode($catResult);
            
            
            if($catResult->Message=="Successfully cancelled the order")
            {
              
              if($order[0]->cancelReason=='BuyerCanceled')
              {
                $order_status=DB::Table('Channel_order_details')
                ->where('order_id',$AmazonOrderId)
                ->update(array('channel_order_status'=>'Cancelled By Buyer'));
                
                $channel_status=DB::Table('Channel_orders')
                ->where('channel_order_id',$AmazonOrderId)
                ->update(array('order_status'=>'Cancelled By Buyer'));
                
                
                print_r('Sucessfully Updated Cancelled By Buyer');
              }
              
            }
            return ($catResult);
            
          } 
          
        }
      }
      else
      {
        print_r('Order has Been Canceled Already By: '.$order_status[0]->order_status);
      }
      
    }
    
    
    
    public function productAvailability($ASIN)
    {
      
      $product_availability=DB::table('Channel_product_add_update as Cpau')
      ->leftJoin('product_inventory as pi','pi.product_id','=','Cpau.product_id')
      ->leftJoin('products as pd','pd.product_id','=','Cpau.product_id')
      ->where('channel_product_key',$ASIN)
      ->where('location_id','105')
      ->get();
      
      return $product_availability;
      
    }
    
    public function getUnshippedOrders($order_id)
    {
      return DB::table('Channel_orders')
            ->select('channel_order_status')
            ->where('channel_order_id',$order_id)
            ->first();
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
    
    /*public function getXmlArrayOrderFulfillment($channel_order_id,$Carrier_Code,$Shipping_Method,$Shipper_Tracking_Number,$i)
      {
      // echo $channel_order_id;exit;
      //$DocumentVersion='1.01';
      //$MerchantIdentifier='M_FLEXPAX_424611791';
      //$MessageType='OrderFulfillment';
      $xml_array=array();
      $xml_array['Message']['MessageID']= $i+1;
      $xml_array['Message']['OrderFulfillment']['AmazonOrderID']=$channel_order_id;
      $xml_array['Message']['OrderFulfillment']['FulfillmentDate']= gmdate(DATE_ATOM,mktime(0,0,0,9,20,2015));
      $xml_array['Message']['OrderFulfillment']['FulfillmentData']['CarrierCode']= $Carrier_Code;
      $xml_array['Message']['OrderFulfillment']['FulfillmentData']['ShippingMethod']= $Shipping_Method;
      $xml_array['Message']['OrderFulfillment']['FulfillmentData']['ShipperTrackingNumber']= $Shipper_Tracking_Number;
      //  print_r($xml_array);exit;
      return $xml_array;
      
    }*/
    //}
    
    
    
    public function getAllStatus()
    { 
      $order_status = DB::Table('Channel_orders')
      ->select('order_status')
      ->groupby('order_status')
      ->get();
      
      return $order_status;
    }
    
    
    public function getAllChannels()
    {
      $channels = DB::Table('Channel')
      ->select('channel_id','channnel_name')
      ->where('channel_id','!=','4')
      ->get();
      
      return $channels;
    }
    
    public function getAllChannelID($fname){
      
      if($fname=="all")
      {
        $channel_id = "0";
        //return $channel_id;
        
        }else{
        $channel_id= DB::Table('Channel')
        ->select('channel_id')
        ->where('channnel_name','=',$fname)
        ->get();
        $channel_id= $channel_id[0]->channel_id;
        
      }
      
      return $channel_id;
      
    }
    public function getDates()
    {
      //echo "hi";exit;
      date_default_timezone_set('Asia/Kolkata');
      $to_date =   date("Y/m/d H:i:s");
      
      
      $from_date = date("Y/m/d H:i:s", strtotime("-1 week"));
      
      $Dates = array('from_date'=>$from_date,'to_date'=>$to_date);
      
      
      return $Dates;
      
    }
    
    public function getMinMaxOrderDate()
    {
      
      return DB::table('Channel_orders')->select(DB::raw('min(order_date) as minimum_date,max(order_date) as maximum_date'))->get();
    }
    
    public function AllValues($StartDate,$EndDate,$channel_id,$order_status,$customerId)
    {
      if($StartDate>date("Y/m/d H:i:s"))
      
      {
        date_default_timezone_set('Asia/Kolkata');
        $StartDate =   date("Y/m/d 00:00:00");
      }
      
      else{
        
        if(!empty($StartDate)){
          date_default_timezone_set('Asia/Kolkata');
          $StartDate =   date_create($StartDate);
          
          $StartDate =  date_format($StartDate,"Y/m/d H:i:s"); 
          
        }
        if(empty($StartDate))
        {date_default_timezone_set('Asia/Kolkata');
          
        $StartDate = date("Y/m/d H:i:s", strtotime("-1 week"));}
        
      }
      if(!empty($EndDate)){
        
        if($EndDate==date('Y/m/d 00:00:00')){
          date_default_timezone_set('Asia/Kolkata');  
          $EndDate = date("Y/m/d H:i:s");}else{
          
          date_default_timezone_set('Asia/Kolkata');
          $EndDate =   date_create($EndDate);
          
          $EndDate =  date_format($EndDate,"Y/m/d H:i:s");
        }
      }   
      
      
      if(empty($EndDate)){
        
        date_default_timezone_set('Asia/Kolkata');
        $EndDate=date("Y/m/d H:i:s");
        
      }
      
      /*print_r($StartDate);
      print_r($EndDate);exit;*/
      
      
      if($order_status=="all")
      {
        
      if($customerId=="0"){
            if($channel_id=="0"){
              
              $data = 
              DB::Table('Channel_orders as Co')
              ->leftJoin('Channel as Ci','Ci.channel_id','=','Co.channel_id')
              ->leftJoin('gds_orders as go', 'go.gds_order_id','=','Co.erp_order_id')
              ->leftJoin('Channel_order_details as cod','Co.channel_order_id','=','cod.order_id')
              ->leftJoin('Channel_product_add_update as cpau','cpau.channel_product_key','=','cod.channel_item_id')
              ->leftJoin('products as prod','prod.product_id','=','cpau.product_id')
              ->select('Ci.channel_logo', DB::Raw('go.erp_order_id as erp_order_id'),'Ci.channnel_name','Co.order_id',DB::Raw('Co.erp_order_id as gds_order_id'),'Ci.channel_id','Co.channel_order_id','Co.channel_order_status',DB::Raw('DATE(Co.order_date) as order_date'),'Co.payment_method',DB::Raw('IF(Co.shipping_cost IS NULL or Co.shipping_cost="","0.00",Co.shipping_cost) as shipping_cost'),DB::Raw('IF(Co.tax IS NULL or Co.tax="","0.00",Co.tax) as tax'),'Co.total_amount')
              //->where('prod.manufacturer_id','=',$customerId)
              //->where('Co.channel_order_status',$order_status)
              ->whereBetween(DB::Raw('DATE(Co.order_date)'),array($StartDate,$EndDate))
              ->orderBy('Co.order_date','DESC')
              
              ->get();
              //echo count($data); die;
            }
            else
            {
              $data = 
              DB::Table('Channel_orders as Co')
              ->leftJoin('Channel as Ci','Ci.channel_id','=','Co.channel_id')
              ->leftJoin('gds_orders as go', 'go.gds_order_id','=','Co.erp_order_id')
              ->leftJoin('Channel_order_details as cod','Co.channel_order_id','=','cod.order_id')
              ->leftJoin('Channel_product_add_update as cpau','cpau.channel_product_key','=','cod.channel_item_id')
              ->leftJoin('products as prod','prod.product_id','=','cpau.product_id')
              ->select('Ci.channel_logo',DB::Raw('go.erp_order_id as erp_order_id'),'Ci.channnel_name','Co.order_id',DB::Raw('Co.erp_order_id as gds_order_id'),'Ci.channel_id','Co.channel_order_id','Co.channel_order_status',DB::Raw('Date(Co.order_date) as order_date'),'Co.payment_method',DB::Raw('IF(Co.shipping_cost IS NULL or Co.shipping_cost="","0.00",Co.shipping_cost) as shipping_cost'),DB::Raw('IF(Co.tax IS NULL or Co.tax="","0.00",Co.tax) as tax'),'Co.total_amount')
              ->where('Co.channel_id',$channel_id)
              //->where('Co.channel_order_status',$order_status)
              ->whereBetween(DB::Raw('DATE(Co.order_date)'),array($StartDate,$EndDate))
              //->where('prod.manufacturer_id','=',$customerId)
              ->orderBy('order_date','DESC')
              ->get();
              
              
            }
            }else{
            if($channel_id=="0"){
              
              $data = 
              DB::Table('Channel_orders as Co')
              ->leftJoin('Channel as Ci','Ci.channel_id','=','Co.channel_id')
              ->leftJoin('gds_orders as go', 'go.gds_order_id','=','Co.erp_order_id')
              ->leftJoin('Channel_order_details as cod','Co.channel_order_id','=','cod.order_id')
              ->leftJoin('Channel_product_add_update as cpau','cpau.channel_product_key','=','cod.channel_item_id')
              ->leftJoin('products as prod','prod.product_id','=','cpau.product_id')
              ->select('Ci.channel_logo', DB::Raw('go.erp_order_id as erp_order_id'),'Ci.channnel_name','Co.order_id',DB::Raw('Co.erp_order_id as gds_order_id'),'Ci.channel_id','Co.channel_order_id','Co.channel_order_status',DB::Raw('DATE(Co.order_date) as order_date'),'Co.payment_method',DB::Raw('IF(Co.shipping_cost IS NULL or Co.shipping_cost="","0.00",Co.shipping_cost) as shipping_cost'),DB::Raw('IF(Co.tax IS NULL or Co.tax="","0.00",Co.tax) as tax'),'Co.total_amount')
              ->where('prod.manufacturer_id','=',$customerId)
             // ->where('Co.channel_order_status',$order_status)
              ->whereBetween(DB::Raw('DATE(Co.order_date)'),array($StartDate,$EndDate))
              ->orderBy('Co.order_date','DESC')
              
              ->get();
              //echo count($data); die;
            }
            else
            {
              $data = /*DB::Table('Channel_orders')
                ->select('erp_order_id','channel_id','channel_order_id','channel_order_status','order_date','payment_method','shipping_cost','sub_total','tax','total_amount','order_status')
                ->whereBetween('order_date',array($StartDate,$EndDate))
                
                ->where('channel_id',$channel_id)
                
                ->where('channel_order_status','=',$order_status)
                ->orderBy('order_date', 'DESC')
                
                
              ->get();*/
              DB::Table('Channel_orders as Co')
              ->leftJoin('Channel as Ci','Ci.channel_id','=','Co.channel_id')
              ->leftJoin('gds_orders as go', 'go.gds_order_id','=','Co.erp_order_id')
              ->leftJoin('Channel_order_details as cod','Co.channel_order_id','=','cod.order_id')
              ->leftJoin('Channel_product_add_update as cpau','cpau.channel_product_key','=','cod.channel_item_id')
              ->leftJoin('products as prod','prod.product_id','=','cpau.product_id')
              ->select('Ci.channel_logo',DB::Raw('go.erp_order_id as erp_order_id'),'Ci.channnel_name','Co.order_id',DB::Raw('Co.erp_order_id as gds_order_id'),'Ci.channel_id','Co.channel_order_id','Co.channel_order_status',DB::Raw('Date(Co.order_date) as order_date'),'Co.payment_method',DB::Raw('IF(Co.shipping_cost IS NULL or Co.shipping_cost="","0.00",Co.shipping_cost) as shipping_cost'),DB::Raw('IF(Co.tax IS NULL or Co.tax="","0.00",Co.tax) as tax'),'Co.total_amount')
              ->where('Co.channel_id',$channel_id)
              //->where('Co.channel_order_status',$order_status)
              ->whereBetween(DB::Raw('DATE(Co.order_date)'),array($StartDate,$EndDate))
              ->where('prod.manufacturer_id','=',$customerId)
              ->orderBy('order_date','DESC')
              ->get();
              
              
            }
            
          }}
        else
        {
          
          if($customerId=="0"){
            if($channel_id=="0"){
              
              $data = 
              DB::Table('Channel_orders as Co')
              ->leftJoin('Channel as Ci','Ci.channel_id','=','Co.channel_id')
              ->leftJoin('gds_orders as go', 'go.gds_order_id','=','Co.erp_order_id')
              ->leftJoin('Channel_order_details as cod','Co.channel_order_id','=','cod.order_id')
              ->leftJoin('Channel_product_add_update as cpau','cpau.channel_product_key','=','cod.channel_item_id')
              ->leftJoin('products as prod','prod.product_id','=','cpau.product_id')
              ->select('Ci.channel_logo', DB::Raw('go.erp_order_id as erp_order_id'),'Ci.channnel_name','Co.order_id',DB::Raw('Co.erp_order_id as gds_order_id'),'Ci.channel_id','Co.channel_order_id','Co.channel_order_status',DB::Raw('DATE(Co.order_date) as order_date'),'Co.payment_method',DB::Raw('IF(Co.shipping_cost IS NULL or Co.shipping_cost="","0.00",Co.shipping_cost) as shipping_cost'),DB::Raw('IF(Co.tax IS NULL or Co.tax="","0.00",Co.tax) as tax'),'Co.total_amount')
              //->where('prod.manufacturer_id','=',$customerId)
              ->where('Co.channel_order_status',$order_status)
              ->whereBetween(DB::Raw('DATE(Co.order_date)'),array($StartDate,$EndDate))
              ->orderBy('Co.order_date','DESC')
              
              ->get();
              //echo count($data); die;
            }
            else
            {
              $data = 
              DB::Table('Channel_orders as Co')
              ->leftJoin('Channel as Ci','Ci.channel_id','=','Co.channel_id')
              ->leftJoin('gds_orders as go', 'go.gds_order_id','=','Co.erp_order_id')
              ->leftJoin('Channel_order_details as cod','Co.channel_order_id','=','cod.order_id')
              ->leftJoin('Channel_product_add_update as cpau','cpau.channel_product_key','=','cod.channel_item_id')
              ->leftJoin('products as prod','prod.product_id','=','cpau.product_id')
              ->select('Ci.channel_logo',DB::Raw('go.erp_order_id as erp_order_id'),'Ci.channnel_name','Co.order_id',DB::Raw('Co.erp_order_id as gds_order_id'),'Ci.channel_id','Co.channel_order_id','Co.channel_order_status',DB::Raw('Date(Co.order_date) as order_date'),'Co.payment_method',DB::Raw('IF(Co.shipping_cost IS NULL or Co.shipping_cost="","0.00",Co.shipping_cost) as shipping_cost'),DB::Raw('IF(Co.tax IS NULL or Co.tax="","0.00",Co.tax) as tax'),'Co.total_amount')
              ->where('Co.channel_id',$channel_id)
              ->where('Co.channel_order_status',$order_status)
              ->whereBetween(DB::Raw('DATE(Co.order_date)'),array($StartDate,$EndDate))
              //->where('prod.manufacturer_id','=',$customerId)
              ->orderBy('order_date','DESC')
              ->get();
              
              
            }
            }else{
            if($channel_id=="0"){
              
              $data = 
              DB::Table('Channel_orders as Co')
              ->leftJoin('Channel as Ci','Ci.channel_id','=','Co.channel_id')
              ->leftJoin('gds_orders as go', 'go.gds_order_id','=','Co.erp_order_id')
              ->leftJoin('Channel_order_details as cod','Co.channel_order_id','=','cod.order_id')
              ->leftJoin('Channel_product_add_update as cpau','cpau.channel_product_key','=','cod.channel_item_id')
              ->leftJoin('products as prod','prod.product_id','=','cpau.product_id')
              ->select('Ci.channel_logo', DB::Raw('go.erp_order_id as erp_order_id'),'Ci.channnel_name','Co.order_id',DB::Raw('Co.erp_order_id as gds_order_id'),'Ci.channel_id','Co.channel_order_id','Co.channel_order_status',DB::Raw('DATE(Co.order_date) as order_date'),'Co.payment_method',DB::Raw('IF(Co.shipping_cost IS NULL or Co.shipping_cost="","0.00",Co.shipping_cost) as shipping_cost'),DB::Raw('IF(Co.tax IS NULL or Co.tax="","0.00",Co.tax) as tax'),'Co.total_amount')
              ->where('prod.manufacturer_id','=',$customerId)
              ->where('Co.channel_order_status',$order_status)
              ->whereBetween(DB::Raw('DATE(Co.order_date)'),array($StartDate,$EndDate))
              ->orderBy('Co.order_date','DESC')
              
              ->get();
              //echo count($data); die;
            }
            else
            {
              $data = /*DB::Table('Channel_orders')
                ->select('erp_order_id','channel_id','channel_order_id','channel_order_status','order_date','payment_method','shipping_cost','sub_total','tax','total_amount','order_status')
                ->whereBetween('order_date',array($StartDate,$EndDate))
                
                ->where('channel_id',$channel_id)
                
                ->where('channel_order_status','=',$order_status)
                ->orderBy('order_date', 'DESC')
                
                
              ->get();*/
              DB::Table('Channel_orders as Co')
              ->leftJoin('Channel as Ci','Ci.channel_id','=','Co.channel_id')
              ->leftJoin('gds_orders as go', 'go.gds_order_id','=','Co.erp_order_id')
              ->leftJoin('Channel_order_details as cod','Co.channel_order_id','=','cod.order_id')
              ->leftJoin('Channel_product_add_update as cpau','cpau.channel_product_key','=','cod.channel_item_id')
              ->leftJoin('products as prod','prod.product_id','=','cpau.product_id')
              ->select('Ci.channel_logo',DB::Raw('go.erp_order_id as erp_order_id'),'Ci.channnel_name','Co.order_id',DB::Raw('Co.erp_order_id as gds_order_id'),'Ci.channel_id','Co.channel_order_id','Co.channel_order_status',DB::Raw('Date(Co.order_date) as order_date'),'Co.payment_method',DB::Raw('IF(Co.shipping_cost IS NULL or Co.shipping_cost="","0.00",Co.shipping_cost) as shipping_cost'),DB::Raw('IF(Co.tax IS NULL or Co.tax="","0.00",Co.tax) as tax'),'Co.total_amount')
              ->where('Co.channel_id',$channel_id)
              ->where('Co.channel_order_status',$order_status)
              ->whereBetween(DB::Raw('DATE(Co.order_date)'),array($StartDate,$EndDate))
              ->where('prod.manufacturer_id','=',$customerId)
              ->orderBy('order_date','DESC')
              ->get();
              
              
            }
            
          }}
          /*$queries = DB::getQueryLog();
            $last_query = end($queries);
            print_r($last_query) ;die;
            print_r($data);exit;
            
          print_r($data);exit;*/
          return $data;
          
          
    }
    
    public function getpieChannelId($cname)
    {
      
      $cid = DB::table('Channel')
      ->Select('channel_id')
      ->where('channnel_name','=',$cname)
      ->get();
      return $cid;
    }
    
    public function getOrderStatus($cid)
    {
      //print_r($cid);exit;
      if($cid =="0"){
        $ostatus = DB::table('Channel_orders')
        ->Select('channel_order_status')
        
        ->groupby('channel_order_status')
        ->lists('channel_order_status');
        
        } else{
        $ostatus = DB::table('Channel_orders')
        ->Select('channel_order_status')
        ->where('channel_id','=',$cid[0]->channel_id)
        ->groupby('channel_order_status')
        ->lists('channel_order_status');
      }
      
      
      return $ostatus;
    } 
    
    public function PieAllVal($order_status,$from_date,$to_date)
    {
      
      $result = DB::Table('Channel_orders')
      ->select(DB::raw('DATE(order_date) as dorder'),DB::raw('count(channel_id) as total'))
      ->where('channel_order_status','=',$order_status)
      ->whereBetween('order_date',array($from_date,$to_date))
      ->groupby( DB::raw('DATE(order_date)'))
      ->get();
      /* $queries = DB::getQueryLog();
        $last_query = end($queries);
      print_r($last_query) ;die;*/
      
      return $result;
    }
    
    public function PieIndexVal($i,$from_date,$to_date)
    {
      $orders = DB::Table('Channel_orders')
      ->select(DB::raw('DATE(order_date) as dorder'),DB::raw('count(channel_id) as total'))
      
      ->where('channel_id','=',$i);
      if(empty($from_date))
      $orders->where(DB::raw('DATE(order_date)'),'>=' ,$from_date);
      if(empty($to_date))
      $orders->where(DB::raw('DATE(order_date)'),'<=' ,$to_date);
      
      //->whereBetween('order_date',array($from_date,$to_date))
      $orders->groupby( DB::raw('DATE(order_date)'));
      
      $result = $orders->get();
      //print_r($result);exit;
      return $result;
    }
    
    public function IndexVal($from_date,$to_date,$customerId)
    {
      
      if($customerId=="0"){$results = DB::Table('Channel_orders as Co')
        
        ->leftJoin('Channel as Ci','Ci.channel_id','=','Co.channel_id')
        ->leftJoin('gds_orders as go', 'go.gds_order_id','=','Co.erp_order_id')
        ->leftJoin('Channel_order_details as cod','Co.channel_order_id','=','cod.order_id')
        ->leftJoin('Channel_product_add_update as cpau','cpau.channel_product_key','=','cod.channel_item_id')
        ->leftJoin('products as prod','prod.product_id','=','cpau.product_id')
        ->select('Ci.channel_logo', DB::Raw('go.erp_order_id as erp_order_id'),'Ci.channnel_name','Co.order_id',DB::Raw('Co.erp_order_id as gds_order_id'),'Ci.channel_id','Co.channel_order_id','Co.channel_order_status',DB::Raw('DATE(Co.order_date) as order_date'),'Co.payment_method',DB::Raw('IF(Co.shipping_cost IS NULL or Co.shipping_cost="","0.00",Co.shipping_cost) as shipping_cost'),DB::Raw('IF(Co.tax IS NULL or Co.tax="","0.00",Co.tax) as tax'),'Co.total_amount')
        //->whereBetween('Co.order_date',array($from_date,$to_date))
        //->where('prod.manufacturer_id','=',$customerId)
        ->orderBy('order_date','DESC')
      ->get();}
      else{$results = DB::Table('Channel_orders as Co')
        
        ->leftJoin('Channel as Ci','Ci.channel_id','=','Co.channel_id')
        ->leftJoin('gds_orders as go', 'go.gds_order_id','=','Co.erp_order_id')
        ->leftJoin('Channel_order_details as cod','Co.channel_order_id','=','cod.order_id')
        ->leftJoin('Channel_product_add_update as cpau','cpau.channel_product_key','=','cod.channel_item_id')
        ->leftJoin('products as prod','prod.product_id','=','cpau.product_id')
        ->select('Ci.channel_logo', DB::Raw('go.erp_order_id as erp_order_id'),'Ci.channnel_name','Co.order_id',DB::Raw('Co.erp_order_id as gds_order_id'),'Ci.channel_id','Co.channel_order_id','Co.channel_order_status',DB::Raw('DATE(Co.order_date) as order_date'),'Co.payment_method',DB::Raw('IF(Co.shipping_cost IS NULL or Co.shipping_cost="","0.00",Co.shipping_cost) as shipping_cost'),DB::Raw('IF(Co.tax IS NULL or Co.tax="","0.00",Co.tax) as tax'),'Co.total_amount')
        //->whereBetween('Co.order_date',array($from_date,$to_date))
        ->where('prod.manufacturer_id','=',$customerId)
        ->orderBy('order_date','DESC')
      ->get();}
      
      //print_r($results);exit;
      return $results;
    }
    
    public function AllValIndex($order_status, $from_date,$to_date){
      
      $results = DB::Table('Channel_orders as Co')
      ->leftJoin('Channel as Ci','Ci.channel_id','=','Co.channel_id')
      ->select('Ci.channnel_name','Co.order_id','Co.channel_id','Co.channel_order_id','Co.channel_order_status','Co.order_date','Co.payment_method','Co.shipping_cost','Co.sub_total','Co.tax','Co.total_amount')
      
      ->whereBetween('Co.order_date',array($from_date,$to_date))
      ->where('order_status','=',$order_status)
      ->get();
      //print_r($results);exit;
      return $results;
    }
    
    
    public function getOrderDetails($order_id){
      /*print_r($order_id);exit;*/
      $OrderDetails =DB::table('Channel_order_details as cod')
      ->leftjoin('channel_orders as co','cod.order_id','=','co.channel_order_id')
      ->select('cod.channel_item_id','co.channel_order_id','cod.channel_order_status','cod.quantity','cod.price')
      ->where('cod.order_id','=',$order_id)
      ->get();
      
      return $OrderDetails; 
    }
    function PiechartValues($order_status,$channel_id,$StartDate,$EndDate){
      
      if($channel_id == "0"){
        
        $total_orders = DB::Table('Channel_orders')
        ->select(DB::raw('count(channel_order_id) as order_num'))
        //->whereBetween('order_date',array($StartDate,$EndDate))
        ->get();
        
        $channel_orders = DB::Table('Channel_orders')
        ->select(DB::raw('count(channel_order_id) as channel_num'))
        ->where('channel_order_status','=',$order_status)
        ->whereBetween('order_date',array($StartDate,$EndDate))
        
        ->get(); 
        }else{
        $total_orders = DB::Table('Channel_orders')
        ->select(DB::raw('count(channel_order_id) as order_num'))
        ->where('channel_id','=',$channel_id)
        ->whereBetween('order_date',array($StartDate,$EndDate))
        ->get();
        
        $channel_orders = DB::Table('Channel_orders')
        ->select(DB::raw('count(channel_order_id) as channel_num'))
        ->where('channel_order_status','=',$order_status)
        ->whereBetween('order_date',array($StartDate,$EndDate))
        ->where('channel_id','=',$channel_id)
        ->get();
        
        
      }
      $Data = array('total_orders'=>$total_orders,'channel_orders'=>$channel_orders);
      
      //echo "<pre>"; print_r($Data); die;
      return $Data;
    }
    public function TodayOrderDetails($customerId){
      
      date_default_timezone_set('Asia/Kolkata');
      
      $StartDate = date("Y-m-d");
      $StartDate = date_create($StartDate);
      $StartDate = date_format($StartDate,"Y-m-d H:i:s");
      //print_r($stt);exit;
      
      $EndDate = date("Y-m-d H:i:s");
      $EndDate = date_create($EndDate);
      $EndDate = date_format($EndDate,"Y-m-d H:i:s");
      
      if($customerId=="0"){
        $TodayOrders = DB::Table('Channel_orders as Co')
        ->leftJoin('Channel as Ci','Ci.channel_id','=','Co.channel_id')
        ->leftJoin('gds_orders as go', 'go.gds_order_id','=','Co.erp_order_id')
        ->leftJoin('Channel_order_details as cod','Co.channel_order_id','=','cod.order_id')
        ->leftJoin('Channel_product_add_update as cpau','cpau.channel_product_key','=','cod.channel_item_id')
        ->leftJoin('products as prod','prod.product_id','=','cpau.product_id')
        ->select(DB::Raw('  count( DISTINCT Co.Channel_order_id) as TodayOrders '))
        //->where('prod.manufacturer_id','=',$customerId)
        ->whereBetween('Co.order_date',array($StartDate,$EndDate))
        ->get();
        
        $TodayRevenue = DB::Table('Channel_orders as Co')
        ->leftJoin('Channel as Ci','Ci.channel_id','=','Co.channel_id')
        ->leftJoin('gds_orders as go', 'go.gds_order_id','=','Co.erp_order_id')
        ->leftJoin('Channel_order_details as cod','Co.channel_order_id','=','cod.order_id')
        ->leftJoin('Channel_product_add_update as cpau','cpau.channel_product_key','=','cod.channel_item_id')
        ->leftJoin('products as prod','prod.product_id','=','cpau.product_id')
        
        ->select(DB::raw('sum(Co.total_amount) as TodayRevenue'))
        // ->where('prod.manufacturer_id','=',$customerId)
        ->whereBetween('Co.order_date',array($StartDate,$EndDate))
        ->get();
        
        if($TodayRevenue[0]->TodayRevenue==NULL){
          $TodayRevenue[0]->TodayRevenue = '0';
          
        }
        
        $Unshipped = DB::Table('Channel_orders as Co')
        ->leftJoin('Channel as Ci','Ci.channel_id','=','Co.channel_id')
        ->leftJoin('gds_orders as go', 'go.gds_order_id','=','Co.erp_order_id')
        ->leftJoin('Channel_order_details as cod','Co.channel_order_id','=','cod.order_id')
        ->leftJoin('Channel_product_add_update as cpau','cpau.channel_product_key','=','cod.channel_item_id')
        ->leftJoin('products as prod','prod.product_id','=','cpau.product_id')
        ->select(DB::Raw('  count( DISTINCT Co.Channel_order_id) as Unshipped'))
        // ->where('prod.manufacturer_id','=',$customerId)
        ->whereIn('Co.channel_order_status',array('Unshipped','APPROVED','PACKED','READY TO DISPATCH'))
        // ->where('channel_order_status','=','APPROVED') 
        
        ->get();
        
        $Completed = DB::Table('Channel_orders as Co')
        ->leftJoin('Channel as Ci','Ci.channel_id','=','Co.channel_id')
        ->leftJoin('gds_orders as go', 'go.gds_order_id','=','Co.erp_order_id')
        ->leftJoin('Channel_order_details as cod','Co.channel_order_id','=','cod.order_id')
        ->leftJoin('Channel_product_add_update as cpau','cpau.channel_product_key','=','cod.channel_item_id')
        ->leftJoin('products as prod','prod.product_id','=','cpau.product_id')
        ->select(DB::Raw('  count( DISTINCT Co.Channel_order_id) as Completed'))
        //->where('prod.manufacturer_id','=',$customerId)
        ->wherein('Co.channel_order_status',array('Shipped','Completed'))
        ->whereBetween('Co.order_date',array($StartDate,$EndDate))
        ->get();
        }else{$TodayOrders = DB::Table('Channel_orders as Co')
        ->leftJoin('Channel as Ci','Ci.channel_id','=','Co.channel_id')
        ->leftJoin('gds_orders as go', 'go.gds_order_id','=','Co.erp_order_id')
        ->leftJoin('Channel_order_details as cod','Co.channel_order_id','=','cod.order_id')
        ->leftJoin('Channel_product_add_update as cpau','cpau.channel_product_key','=','cod.channel_item_id')
        ->leftJoin('products as prod','prod.product_id','=','cpau.product_id')
        ->select(DB::Raw('  count( DISTINCT Co.Channel_order_id) as TodayOrders '))
        ->where('prod.manufacturer_id','=',$customerId)
        ->whereBetween('Co.order_date',array($StartDate,$EndDate))
        ->get();
        
        $TodayRevenue = DB::Table('Channel_orders as Co')
        ->leftJoin('Channel as Ci','Ci.channel_id','=','Co.channel_id')
        ->leftJoin('gds_orders as go', 'go.gds_order_id','=','Co.erp_order_id')
        ->leftJoin('Channel_order_details as cod','Co.channel_order_id','=','cod.order_id')
        ->leftJoin('Channel_product_add_update as cpau','cpau.channel_product_key','=','cod.channel_item_id')
        ->leftJoin('products as prod','prod.product_id','=','cpau.product_id')
        
        ->select(DB::raw('sum(Co.total_amount) as TodayRevenue'))
        ->where('prod.manufacturer_id','=',$customerId)
        ->whereBetween('Co.order_date',array($StartDate,$EndDate))
        ->get();
        
        if($TodayRevenue[0]->TodayRevenue==NULL){
          $TodayRevenue[0]->TodayRevenue = '0';
          
        }
        
        $Unshipped = DB::Table('Channel_orders as Co')
        ->leftJoin('Channel as Ci','Ci.channel_id','=','Co.channel_id')
        ->leftJoin('gds_orders as go', 'go.gds_order_id','=','Co.erp_order_id')
        ->leftJoin('Channel_order_details as cod','Co.channel_order_id','=','cod.order_id')
        ->leftJoin('Channel_product_add_update as cpau','cpau.channel_product_key','=','cod.channel_item_id')
        ->leftJoin('products as prod','prod.product_id','=','cpau.product_id')
        ->select(DB::Raw('  count( DISTINCT Co.Channel_order_id) as Unshipped'))
        ->where('prod.manufacturer_id','=',$customerId)
        ->whereIn('Co.channel_order_status',array('Unshipped','APPROVED','PACKED','READY TO DISPATCH'))
        // ->where('channel_order_status','=','APPROVED') 
        
        ->get();
        
        $Completed = DB::Table('Channel_orders as Co')
        ->leftJoin('Channel as Ci','Ci.channel_id','=','Co.channel_id')
        ->leftJoin('gds_orders as go', 'go.gds_order_id','=','Co.erp_order_id')
        ->leftJoin('Channel_order_details as cod','Co.channel_order_id','=','cod.order_id')
        ->leftJoin('Channel_product_add_update as cpau','cpau.channel_product_key','=','cod.channel_item_id')
        ->leftJoin('products as prod','prod.product_id','=','cpau.product_id')
        ->select(DB::Raw('  count( DISTINCT Co.Channel_order_id) as Completed'))
        ->where('prod.manufacturer_id','=',$customerId)
        ->wherein('Co.channel_order_status',array('Shipped','Completed'))
        ->whereBetween('Co.order_date',array($StartDate,$EndDate))
      ->get();}
      
      
      
      $TodayOrderDetails = array_merge( $TodayRevenue, $TodayOrders , $Unshipped, $Completed);
      return $TodayOrderDetails;
      
    }
    
    public function UnshippedAdminOrders(){
      
      
      $orders = DB::Table('Channel_orders as Co')
            ->leftJoin('Channel as Ci','Ci.channel_id','=','Co.channel_id')
            ->leftJoin('gds_orders as go', 'go.gds_order_id','=','Co.erp_order_id')
            ->leftJoin('Channel_order_details as cod','Co.channel_order_id','=','cod.order_id')
            ->leftJoin('Channel_product_add_update as cpau','cpau.channel_product_key','=','cod.channel_item_id')
            ->leftJoin('products as prod','prod.product_id','=','cpau.product_id')
            
            ->select('Ci.channel_logo', 'Ci.channnel_name','Co.order_id','Co.erp_order_id','Ci.channel_id','Co.channel_order_id','Co.channel_order_status','Co.order_date','Co.payment_method',DB::Raw('IF(Co.shipping_cost IS NULL or Co.shipping_cost="","0.00",Co.shipping_cost) as shipping_cost'),DB::Raw('IF(Co.tax IS NULL or Co.tax="","0.00",Co.tax) as tax'),'Co.total_amount')
            ->whereIn('Co.channel_order_status',array('Unshipped','APPROVED','PACKED','READY TO DISPATCH'))
            //->whereBetween('Co.order_date',array($StartDate,$EndDate))
            //->where('prod.manufacturer_id','=',$customerId)
            ->orderBy('Co.order_date','DESC')
            ->get();
      
            return $orders;
    }
    
    public function CompletedAdminOrders($StartDate,$EndDate){
      
      $orders = DB::Table('Channel_orders as Co')
            
            ->leftJoin('Channel as Ci','Ci.channel_id','=','Co.channel_id')
            ->leftJoin('gds_orders as go', 'go.gds_order_id','=','Co.erp_order_id')
            ->leftJoin('Channel_order_details as cod','Co.channel_order_id','=','cod.order_id')
            ->leftJoin('Channel_product_add_update as cpau','cpau.channel_product_key','=','cod.channel_item_id')
            ->leftJoin('products as prod','prod.product_id','=','cpau.product_id')
            ->select('Ci.channel_logo', 'Ci.channnel_name','Co.order_id','Co.erp_order_id','Ci.channel_id','Co.channel_order_id','Co.channel_order_status','Co.order_date','Co.payment_method',DB::Raw('IF(Co.shipping_cost IS NULL or Co.shipping_cost="","0.00",Co.shipping_cost) as shipping_cost'),DB::Raw('IF(Co.tax IS NULL or Co.tax="","0.00",Co.tax) as tax'),'Co.total_amount')
            //->where('prod.manufacturer_id','=',$customerId)
            ->wherein('Co.channel_order_status',array('Shipped','Completed'))
            
            ->whereBetween('Co.order_date',array($StartDate,$EndDate))
            ->get();
      
            return $orders;
    }
    
    public function AllAdminOrders($StartDate,$EndDate){
      
      $orders = DB::Table('Channel_orders as Co')
            ->leftJoin('Channel as Ci','Ci.channel_id','=','Co.channel_id')
            ->leftJoin('gds_orders as go', 'go.gds_order_id','=','Co.erp_order_id')
            ->leftJoin('Channel_order_details as cod','Co.channel_order_id','=','cod.order_id')
            ->leftJoin('Channel_product_add_update as cpau','cpau.channel_product_key','=','cod.channel_item_id')
            ->leftJoin('products as prod','prod.product_id','=','cpau.product_id')
            ->select('Ci.channel_logo', 'Ci.channnel_name','Co.order_id','Co.erp_order_id','Ci.channel_id','Co.channel_order_id','Co.channel_order_status','Co.order_date','Co.payment_method',DB::Raw('IF(Co.shipping_cost IS NULL or Co.shipping_cost="","0.00",Co.shipping_cost) as shipping_cost'),DB::Raw('IF(Co.tax IS NULL or Co.tax="","0.00",Co.tax) as tax'),'Co.total_amount')
            //->wherein('channel_order_status',array('Shipped','Completed'))
            //->where('prod.manufacturer_id','=',$customerId)
            ->whereBetween('Co.order_date',array($StartDate,$EndDate))
            ->get();
      
            return $orders;
    }
    
    public function CustomerUnshippedOrders($customerId){
      
      $orders = DB::Table('Channel_orders as Co')
            ->leftJoin('Channel as Ci','Ci.channel_id','=','Co.channel_id')
            ->leftJoin('gds_orders as go', 'go.gds_order_id','=','Co.erp_order_id')
            ->leftJoin('Channel_order_details as cod','Co.channel_order_id','=','cod.order_id')
            ->leftJoin('Channel_product_add_update as cpau','cpau.channel_product_key','=','cod.channel_item_id')
            ->leftJoin('products as prod','prod.product_id','=','cpau.product_id')
            
            ->select('Ci.channel_logo', 'Ci.channnel_name','Co.order_id','Co.erp_order_id','Ci.channel_id','Co.channel_order_id','Co.channel_order_status','Co.order_date','Co.payment_method',DB::Raw('IF(Co.shipping_cost IS NULL or Co.shipping_cost="","0.00",Co.shipping_cost) as shipping_cost'),DB::Raw('IF(Co.tax IS NULL or Co.tax="","0.00",Co.tax) as tax'),'Co.total_amount')
            ->whereIn('Co.channel_order_status',array('Unshipped','APPROVED','PACKED','READY TO DISPATCH'))
            //->whereBetween('Co.order_date',array($StartDate,$EndDate))
            ->where('prod.manufacturer_id','=',$customerId)
            ->orderBy('Co.order_date','DESC')
            ->get();
      
            return $orders;
    }
    
    public function CustomerCompletedOrders($StartDate,$EndDate,$customerId){
      
      $orders = DB::Table('Channel_orders as Co')
            
            ->leftJoin('Channel as Ci','Ci.channel_id','=','Co.channel_id')
            ->leftJoin('gds_orders as go', 'go.gds_order_id','=','Co.erp_order_id')
            ->leftJoin('Channel_order_details as cod','Co.channel_order_id','=','cod.order_id')
            ->leftJoin('Channel_product_add_update as cpau','cpau.channel_product_key','=','cod.channel_item_id')
            ->leftJoin('products as prod','prod.product_id','=','cpau.product_id')
            ->select('Ci.channel_logo', 'Ci.channnel_name','Co.order_id','Co.erp_order_id','Ci.channel_id','Co.channel_order_id','Co.channel_order_status','Co.order_date','Co.payment_method',DB::Raw('IF(Co.shipping_cost IS NULL or Co.shipping_cost="","0.00",Co.shipping_cost) as shipping_cost'),DB::Raw('IF(Co.tax IS NULL or Co.tax="","0.00",Co.tax) as tax'),'Co.total_amount')
            ->where('prod.manufacturer_id','=',$customerId)
            ->wherein('Co.channel_order_status',array('Shipped','Completed'))
            
            ->whereBetween('Co.order_date',array($StartDate,$EndDate))
            ->get();
      
            return $orders;
    }
    
    public function CustomerAllOrders($StartDate,$EndDate,$customerId){
      
            $orders = DB::Table('Channel_orders as Co')
            ->leftJoin('Channel as Ci','Ci.channel_id','=','Co.channel_id')
            ->leftJoin('gds_orders as go', 'go.gds_order_id','=','Co.erp_order_id')
            ->leftJoin('Channel_order_details as cod','Co.channel_order_id','=','cod.order_id')
            ->leftJoin('Channel_product_add_update as cpau','cpau.channel_product_key','=','cod.channel_item_id')
            ->leftJoin('products as prod','prod.product_id','=','cpau.product_id')
            ->select('Ci.channel_logo', 'Ci.channnel_name','Co.order_id','Co.erp_order_id','Ci.channel_id','Co.channel_order_id','Co.channel_order_status','Co.order_date','Co.payment_method',DB::Raw('IF(Co.shipping_cost IS NULL or Co.shipping_cost="","0.00",Co.shipping_cost) as shipping_cost'),DB::Raw('IF(Co.tax IS NULL or Co.tax="","0.00",Co.tax) as tax'),'Co.total_amount')
            //->wherein('channel_order_status',array('Shipped','Completed'))
            ->where('prod.manufacturer_id','=',$customerId)
            ->whereBetween('Co.order_date',array($StartDate,$EndDate))
            ->get();
      
      
            return $orders;
      
    }
    
  }
