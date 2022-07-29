<?php

use Central\Repositories\RoleRepo;
use Central\Repositories\OrderRepo;
use Central\Repositories\CustomerRepo;
use Central\Repositories\MasterApiRepo;

class DmapiController extends BaseController
{

    protected $roleAccess;
    protected $custRepo;
    protected $apiAccess;

    public function __construct(RoleRepo $roleAccess, CustomerRepo $custRepo, MasterApiRepo $ApiAccess)
    {
        $this->roleAccess = $roleAccess;
        $this->custRepo = $custRepo;
        $this->apiAccess = $ApiAccess;
    }

    /*
      @param type $api_name
      @return type is a string message which tlls you whether the user has permission or not
      Description: This api request is used  to authenticate user and check  user permissions.
     */

    public function checkUserPermission($api_name)
    {
        $data = Input::get();
        $data['api_name'] = $api_name;
        $result = $this->apiAccess->apiLogin($data);
        if (isset($result['Status']) && $result['Status'] == 1)
        {
            $result = $this->$api_name($data);
            return $result;
        } else
        {
            return Response::json(['Status' => 0, 'Message' => $result['Message']]);
        }
    }

    /*
      @ param type $data
      @ return type product_id,category,category_id,cost_price,manufacturer_id,product_name,sku,attributes
      Description: This API request is used to get the product information based on category_id.
     */

    public function getInventory($data)
    {
        try
        {
            $status = 0;
            $message = '';
            $category_id = $data['category_id'];

            if (!empty($category_id))
            {
                $products = DB::table('products')
                        ->select('products.product_id', 'products.name', 'categories.name as cname', 'categories.category_id', 'products.cost_price', 'products.manufacturer_id', 'products.sku')
                        ->leftJoin('categories', 'categories.category_id', '=', 'products.category_id')
                        ->where(array('categories.category_id' => $category_id))
                        ->get();

                $finalarr = array();
                $finalProductsarr = array();
                $attributesarr = array();
                foreach ($products as $key => $value)
                {
                    $pattr = DB::Table('product_attributes')
                            ->select('product_attributes.value', 'attributes.attribute_code', 'attributes.name', 'attributes.attribute_id')
                            ->leftJoin('attributes', 'attributes.attribute_id', '=', 'product_attributes.attribute_id')
                            ->where(array('product_attributes.product_id' => $value->product_id, 'attributes.attribute_type' => 1))
                            ->get();

                    $temp = array();
                    foreach ($pattr as $key1 => $value1)
                    {
                        $attributesarr[$value1->name] = $value1->value;
                    }
                    $temp[] = $attributesarr;
                    $finalProductsarr['product_id'] = $value->product_id;
                    $finalProductsarr['category'] = $value->cname;
                    $finalProductsarr['category_id'] = $value->category_id;
                    $finalProductsarr['cost_price'] = $value->cost_price;
                    $finalProductsarr['manufacturer_id'] = $value->manufacturer_id;
                    $finalProductsarr['product_name'] = $value->name;
                    $finalProductsarr['sku'] = $value->sku;
                    $finalProductsarr['attributes'] = $temp;
                    $finalarr[] = $finalProductsarr;
                }
                $status = 1;
                $message = 'Data Successfully Retrieved.';
            } else
            {
                return Response::json(['Status' => 0, 'Message' => 'Parameter Missing.']);
            }
        } catch (Exception $e)
        {
            $message = $e->getMessage();
        }
        return Response::json(Array('Status' => $status, 'Message' => $message, 'Data' => $finalarr));
    }

    /*
      @ param type $data
      @ return type channel_product_id,channel_id,product_id
      Description: This API request is used to check the product existence.
     */

    public function getProductExistence($data)
    {
        try
        {
            //echo "<pre>";print_r($data);die;
            $prodids = explode(',', $data['product_id']);
            $prodcheck = DB::table('channel_product')
                    ->leftJoin('products', 'products.product_id', '=', 'channel_product.product_id')
                    ->select('channel_product.*')
                    ->whereIn('channel_product.product_id', $prodids)
                    ->get();
            if (!empty($prodcheck))
            {
                return Response::json(['Status' => true, 'Message' => 'Successfully.', 'Data' => $prodcheck]);
            } else
            {
                return Response::json(['Status' => false, 'Message' => 'Not Successfull.']);
            }
        } catch (Exception $e)
        {
            $message = $e->getMessage();
        }
        return $message;
    }

    /*
      @ param type $data
      @ return type product_id,available_inventory
      Description: This API request is used to get products inventory based on product_id
     */

    public function getProductsInventory($data)
    {
        try
        {
            $status = 0;
            $message = '';
            $pids = $data['pids'];
            $product_ids = explode(',', $pids);
            if (!empty($product_ids))
            {
                $pqty = DB::table('product_inventory')
                        ->select('product_inventory.product_id', 'product_inventory.available_inventory')
                        ->leftJoin('products', 'products.product_id', '=', 'product_inventory.product_id')
                        ->where('products.is_gds_enabled', '=', '1')
                        ->whereIn('product_inventory.product_id', $product_ids)
                        ->get();

                //return $pqty;
                $status = 1;
                $message = 'Data Successfully Retrieved.';
            } else
            {
                return Response::json(['Status' => 0, 'Message' => 'Parameter Missing.']);
            }
        } catch (Exception $e)
        {
            $message = $e->getMessage();
        }
        return Response::json(Array('Status' => $status, 'Message' => $message, 'Data' => $pqty));
    }

    /*
      @param type $data
      @ return type product_name,description,model_name,upc,ean,jan,isbn,mpn,category_name,cost_price,manufacturer_name,sku,attributes,slab_rates,available_stock
      Description: This API request is used to get product dynamic info. */

    public function getProductDynamicInfo($data)
    {
        try
        {
            $status = 0;
            $message = '';
            $product_id = $data['sku'];
            //$ppid = isset($data['product_id']) ? $data['product_id'] : '';
            
            $manufacturerId = $this->apiAccess->getManufacturerId($data);
            //$ppid = (isset($data['ppid']) && $data['ppid'] != '') ? $data['ppid'] : '';
            $pincode = (isset($data['pincode']) && $data['pincode'] != '') ? $data['pincode'] : 0;
            $locationData = $this->getZonebyPincode($pincode, $manufacturerId);
            $locationDetails = json_decode($locationData);
            //echo "<pre>";print_R($locationDetails);die;
            $locationId = $locationDetails->location_id;
            $ppid = $locationId;
            
            if (!empty($product_id))
            {
                $products = DB::table('products')
                        ->select('products.*', 'categories.name as cname', 'categories.category_id', 'eseal_customer.brand_name', 'eseal_customer.customer_id')
                        ->leftJoin('categories', 'categories.category_id', '=', 'products.category_id')
                        ->leftJoin('eseal_customer', 'eseal_customer.customer_id', '=', 'products.manufacturer_id')
                        ->where(array('products.sku' => $product_id))
                        ->get();

                $finalarr = array();
                $finalStaticsarr = array();
                $finalDynamicarr = array();
                $finalSlabarr = array();

                //Products dynamic info
                $prodStaticattr = DB::Table('product_attributes')
                        ->select('product_attributes.value', 'attributes.attribute_code', 'attributes.name', 'attributes.attribute_id', 'attributes.attribute_group_id', 'attributes_groups.name as attribute_group_name')
                        ->leftJoin('attributes', 'attributes.attribute_id', '=', 'product_attributes.attribute_id')
                        ->leftJoin('attributes_groups', 'attributes_groups.attribute_group_id', '=', 'attributes.attribute_group_id')
                        ->where(array('product_attributes.product_id' => $products[0]->product_id, 'attribute_type' => 2))
                        ->get();

                $StaticAttributesarr = array();
                $attributeGrouparr = array();
                foreach ($prodStaticattr as $key1 => $value1)
                {
                    $StaticAttributesarr[$value1->name] = $value1->value;
                    if (in_array($value1->attribute_group_name, $attributeGrouparr))
                    {
                        array_push($attributeGrouparr[$value1->attribute_group_name], $StaticAttributesarr);
                    } else
                    {
                        $attributeGrouparr[$value1->attribute_group_name] = $StaticAttributesarr;
                    }
                }
                $finalarr['product_name'] = $products[0]->name;
                $finalarr['description'] = $products[0]->description;
                $finalarr['model_name'] = $products[0]->model_name;
                $finalarr['upc'] = $products[0]->upc;
                $finalarr['ean'] = $products[0]->ean;
                $finalarr['jan'] = $products[0]->jan;
                $finalarr['isbn'] = $products[0]->isbn;
                $finalarr['mpn'] = $products[0]->mpn;
                $finalarr['category_name'] = $products[0]->cname;
                $finalarr['cost_price'] = $products[0]->cost_price;
                $finalarr['manufacturer_name'] = $products[0]->brand_name;
                $finalarr['sku'] = $products[0]->sku;
                $finalarr['attributes'] = $attributeGrouparr;


                //Get Products slab rates              
                $slabRates = DB::table('products_slab_rates')
                        ->select('products_slab_rates.start_range', 'products_slab_rates.end_range', 'products_slab_rates.price')
                        ->leftJoin('products', 'products.product_id', '=', 'products_slab_rates.product_id')
                        ->where('products_slab_rates.product_id', $products[0]->product_id)
                        ->get();
                //echo '<pre/>';print_r($inTransitQty);exit; 
                $tempSlab = array();
                foreach ($slabRates as $key2 => $value2)
                {
                    $slabarr['quantity'] = $value2->start_range . '-' . $value2->end_range;
                    $slabarr['price'] = $value2->price;
                    $tempSlab[] = $slabarr;
                }
                $finalSlabarr[] = $tempSlab;
                $finalarr['slab_rates'] = $finalSlabarr;


                // if(!empty($ppid))
                // {
                //Get Products Intransit stock

                if (!empty($ppid))
                {
                    $inTransitQty = DB::table('product_inventory')
                            ->select('product_inventory.location_id', 'product_inventory.available_inventory')
                            ->leftJoin('products', 'products.product_id', '=', 'product_inventory.product_id');
                    $inTransitQty = $inTransitQty->where(array('product_inventory.product_id' => $products[0]->product_id, 'product_inventory.location_id' => $ppid));


                    $inTransitQty = DB::table('eseal_' . $products[0]->customer_id)
                            ->select('eseal_' . $products[0]->customer_id . '.primary_id')
                            ->leftJoin('track_history', 'track_history.track_id', '=', 'eseal_' . $products[0]->customer_id . '.track_id')
                            ->where(array('eseal_' . $products[0]->customer_id . '.level_id' => 0, 'eseal_' . $products[0]->customer_id . '.pid' => $products[0]->product_id, 'eseal_' . $products[0]->customer_id . '.gds_status' => 0, 'track_history.dest_loc_id' => $ppid))
                            ->groupBy('eseal_' . $products[0]->customer_id . '.pid')
                            ->count();
                } else
                {
                    $inTransitQty = DB::table('product_inventory')
                            ->select('product_inventory.location_id', 'product_inventory.available_inventory')
                            ->leftJoin('products', 'products.product_id', '=', 'product_inventory.product_id');
                    $inTransitQty = $inTransitQty->where(array('product_inventory.product_id' => $products[0]->product_id));

                    $inTransitQty = DB::table('eseal_' . $products[0]->customer_id)
                            ->select('eseal_' . $products[0]->customer_id . '.primary_id')
                            ->where(array('eseal_' . $products[0]->customer_id . '.level_id' => 0, 'eseal_' . $products[0]->customer_id . '.gds_status' => 0, 'eseal_' . $products[0]->customer_id . '.pid' => $products[0]->product_id))
                            ->groupBy('eseal_' . $products[0]->customer_id . '.pid')
                            ->count();
                }
                $finalarr['available_stock'] = $inTransitQty;


                /* $queries = DB::getQueryLog();
                  $last_query = end($queries);
                  return $last_query; */

                $status = 1;
                $message = 'Data Successfully Retrieved.';
            } else
            {
                return Response::json(['Status' => 0, 'Message' => 'Parameter Missing.']);
            }
        } catch (Exception $e)
        {
            $message = $e->getMessage();
        }
        return Response::json(Array('Status' => $status, 'Message' => $message, 'Data' => $finalarr));
    }

    public function getNearestDpsInventory($data)
    {
        return $data;
    }
    
    public function checkInventoryAvailability($data)
    {
        try
        {
            $status = 0;
            $message = '';
            $responseData = array();
            $orderToken = '';
            $inpuData = json_decode($data['product_data'], true);
            Log::info('inpuData');
            Log::info($inpuData);
            $sku = isset($inpuData['sku']) ? $inpuData['sku'] : '';
            $quantity = isset($inpuData['quantity']) ? $inpuData['quantity'] : 0;
            $price = isset($inpuData['price']) ? $inpuData['price'] : 0;
            $total = isset($inpuData['total']) ? $inpuData['total'] : 0;
            $pincode = isset($inpuData['pincode']) ? $inpuData['pincode'] : 0;
            $locationId = '';
            
            if($sku == '')
            {
                $message = 'No Sku';
                return $this->returnData($status, $message, $responseData);
            }
            $materialResponse = $this->getMaterialCode($sku);
            Log::info('materialResponse');
            Log::info($materialResponse);
//            echo $materialResponse;
            $materialCode = 0;
            if($materialResponse == '')
            {
                return $this->returnData($status, 'Wrong data.', $responseData);
            }else{
                $materialResponseData = json_decode($materialResponse); 
//                echo $materialResponseData->status;
                if($materialResponseData->status)
                {
                    $materialCode = $materialResponseData->data;
                }else{
                    $message = $materialResponseData->message;
                }
            }
            if(strlen($materialCode) < 1)
            {
                return $this->returnData($status, $message, $responseData);
            }
            
            //SAP CALL
            $mfgId = $this->apiAccess->getManufacturerId($data);
            if (empty($mfgId))
            {
                //return Response::json(Array('Status' => false, 'Message' => 'Wrong data.'));
                return $this->returnData($status, 'Wrong data.', $responseData);
            }
            
//            if ($pincode == 0) 
//            {
//                //return Response::json(Array('Status' => false, 'Message' => 'Wrong data.'));
//                return $this->returnData($status, 'No pincode', $responseData);
//            }            
            if($pincode != 0)
            {
                $locationData = $this->getZonebyPincode($pincode, $mfgId);
                //echo $locationData;die;
                if(empty($locationData) || $locationData == '')
                {
                    //return $this->returnData($status, 'Location not matched', $responseData);
                    $locationId = '';
                }else{
                    $locationDetails = json_decode($locationData);
                    if($locationDetails->Status)
                    {
                        $locationId = $locationDetails->location_id;
                    }else{
                        $locationId = '';
                    }
                }
            }
            
            $erpIntegrationData = $this->apiAccess->getErpIntegration($mfgId);
            if (empty($erpIntegrationData))
            {
                //return Response::json(Array('Status' => false, 'Message' => 'Wrong data.'));
                return $this->returnData($status, 'Wrong data.', $responseData);
            }
            $this->_url = $erpIntegrationData->web_service_url;
            $this->_method = 'Z0040_GET_INVENTORY_SKU_SRV';
            $this->_method_name = 'SKU';
            $url = $this->_url . $this->_method . '/' . $this->_method_name;
            $this->_sap_api_repo = new Central\Repositories\SapApiRepo();
            $this->_return_type = 'xml';
            $this->_token = $erpIntegrationData->token;
            
            $username = $erpIntegrationData->web_service_username;
            $password = $erpIntegrationData->web_service_password;
            $sapClient = $erpIntegrationData->sap_client;
            
            $orderData['TOKEN'] = $this->_token;
            $orderData['MATERIAL'] = $materialCode;
            $orderData['PLANT'] = $locationId;
            Log::info('URL');
            Log::info($url);
            Log::info('orderData');
            Log::info($orderData);
            
            $response = $this->_sap_api_repo->request($username, $password, $url, 'GET', $orderData, $this->_return_type, '', '', '', $sapClient);
            //END SAP CALL
            Log::info('ERP response');
            Log::info($response);
            if($response != '')
            {
                $parseData1 = xml_parser_create();
                xml_parse_into_struct($parseData1, $response, $documentValues1, $documentIndex1);
                xml_parser_free($parseData1);
                //Log::info($parseData1);
                $documentData = '';
                $status = '';
                if(!empty($documentValues1))
                {
                    foreach ($documentValues1 as $data)
                    {
                        if (isset($data['tag']) && $data['tag'] == 'D:ESEAL_OUTPUT')
                        {
                            $documentData = $data['value'];
                            //$status = $data['type'];
                        }
                    }
                    $deXml = simplexml_load_string($documentData);
                    $deJson = json_encode($deXml);
                    $xml_array = json_decode($deJson,TRUE);  
                    //echo "<pre>";print_R($xml_array);die;
                    if(!empty($xml_array))
                    {
                        foreach($xml_array as $key => $value)
                        {
                            if($key == 'DATA')
                            {
                                if(isset($value['ITEM']))
                                {
                                    foreach($value['ITEM'] as $itemKey => $itemValue)
                                    {
                                        if($itemKey == 'QUALITY_INSPECTION')
                                        {
                                            $erpResponseData['qty'] = $itemValue;                                
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
            if(isset($erpResponseData['qty']) && $erpResponseData['qty'] > 0)
            {
                $productResponse = DB::table('products')
                        ->where(['sku' => $sku])
                        ->first(['product_id', 'sku']);
                if(!empty($productResponse))
                {
                    $productData = json_decode(json_encode($productResponse), true);
                    $productData['qty'] = $erpResponseData['qty'];
                    $productData['price'] = $price;
                    $productData['total'] = $total;
                    $message = "Stock is available.";
                    $orderTokenData = $this->apiAccess->getuuid();
                    $orderToken = $orderTokenData[0]->uuid;
                    $responseData = $productData;
                }else{
                    $message = "Stock not available";
                }
            }else{
                $message = "Stock not available";
            }
        } catch (ErrorException $ex) {
            $message = $ex->getMessage();
        }
        return Response::json(Array('Status' => $status, 'Message' => $message, 'order_token' => $orderToken, 'Data' => $responseData));
    }

    public function checkInventoryAvailability123($data)
    {
        try
        {
            $status = 0;
            $message = '';
            $p_data = json_decode($data['product_data'], true);
            // $gds_order_token = $data['order_token'];
            $return_token = '';
            if (!empty($p_data))
            {
                $manufacturerId = $this->apiAccess->getManufacturerId($data);
                //$ppid = (isset($data['ppid']) && $data['ppid'] != '') ? $data['ppid'] : '';
                $pincode = (isset($data['pincode']) && $data['pincode'] != '') ? $data['pincode'] : 0;
                $locationData = $this->getZonebyPincode($pincode, $manufacturerId);
                $locationDetails = json_decode($locationData);
                //echo "<pre>";print_R($locationDetails);die;
                $locationId = $locationDetails->location_id;
                $ppid = $locationId;
                
                $product_data = array();
                $sku = isset($p_data['sku']) ? $p_data['sku'] : '';
                $quantity = isset($p_data['quantity']) ? $p_data['quantity'] : 0;
                $price = isset($p_data['price']) ? $p_data['price'] : 0;
                $total = isset($p_data['total']) ? $p_data['total'] : 0;
                $product_ids = DB::table('products')
                        ->select('products.product_id', 'products.sku', 'eseal_customer.customer_id')
                        ->leftJoin('eseal_customer', 'eseal_customer.customer_id', '=', 'products.manufacturer_id')
                        ->where('products.sku', $sku)
                        ->get();
                $last = DB::getQueryLog();
                if(!empty($product_ids))
                {   
                    $pids['product_id'] = $product_ids[0]->product_id;
                    $pids['sku'] = $product_ids[0]->sku;
                    $pids['customer_id'] = $product_ids[0]->customer_id;
                    $pids['qty'] = $quantity;
                    $pids['price'] = $price;
                    $pids['total'] = $total;
                    $product_data[] = $pids; 
                }
                //print_r($product_data);exit;
                if(empty($product_data))
                {
                    return Response::json(Array('Status' => 0, 
                        'Message' => 'No Product'));
                }
                $tmpProductAvailablearr = array();
                $productAvailablearr = array();
                $reqProductAvailablearr = array();
                $available = 1;
                foreach ($product_data as $key => $value)
                {
                    $pdb = 'eseal_' . $value['customer_id'];
                    $qty = $value['qty'];
                    if (!empty($ppid))
                    {
                        try
                        {
                            $pqty = DB::table($pdb)
                                    ->select($pdb . '.primary_id')
                                    ->leftJoin('track_history', 'track_history.track_id', '=', $pdb . '.track_id')
                                    ->where(array($pdb . '.level_id' => 0, $pdb . '.pid' => $value['product_id'], $pdb . '.gds_status' => 0, 'track_history.src_loc_id' => $ppid, 'dest_loc_id' => 0))
                                    ->groupBy($pdb . '.pid')
                                    ->count();
                        } catch (ErrorException $e)
                        {
//                            Log::info($e->getMessage());
                            $message = $e->getMessage();
                            //throw new Exception($message);
                        }

                        if ($qty > $pqty)
                        {
                            $available = 0;
                            $tmpProductAvailablearr['pid'] = $value['product_id'];
                            $tmpProductAvailablearr['sku'] = $value['sku'];
                            $tmpProductAvailablearr['qty'] = $pqty;
                            $reqProductAvailablearr[] = $tmpProductAvailablearr;
                        } else
                        {
                            $tmpProductAvailablearr['pid'] = $value['product_id'];
                            $tmpProductAvailablearr['sku'] = $value['sku'];
                            $tmpProductAvailablearr['qty'] = $pqty;
                            $reqProductAvailablearr[] = $tmpProductAvailablearr;
                        }
                    } else
                    {
                        $pqty = DB::table($pdb)
                                ->select($pdb . '.primary_id')
                                ->where(array($pdb . '.level_id' => 0, $pdb . '.gds_status' => 0, $pdb . '.pid' => $value['product_id']))
                                ->groupBy($pdb . '.pid')
                                ->count();
                        $last = DB::getQueryLog();
                        //print_R(end($last));die;
                        if ($qty > $pqty)
                        {
                            $available = 0;
                            $tmpProductAvailablearr['pid'] = $value['product_id'];
                            $tmpProductAvailablearr['sku'] = $value['sku'];
                            $tmpProductAvailablearr['qty'] = $pqty;
                            $reqProductAvailablearr[] = $tmpProductAvailablearr;
                        } else
                        {
                            $tmpProductAvailablearr['pid'] = $value['product_id'];
                            $tmpProductAvailablearr['sku'] = $value['sku'];
                            $tmpProductAvailablearr['qty'] = $pqty;
                            $reqProductAvailablearr[] = $tmpProductAvailablearr;
                        }
                    }
                }
                $productAvailablearr = $reqProductAvailablearr;
                if ($available == 1)
                {
                    $is_blocked = (isset($data['is_blocked']) && $data['is_blocked'] != '') ? $data['is_blocked'] : '';
                    if ($is_blocked == 1 && $ppid > 0)
                    {
                        //echo 'hi';exit;
                        //$access_token = $data['access_token']; 
                        //echo "<pre/>";print_r($data['orderdata']);exit;
                        //Finding whether this order token is valid or not
                        /*                        $validOrder = DB::table('users_token')
                          ->select('users.customer_id')
                          ->leftJoin('users','users.user_id','=','users_token.user_id')
                          ->where(array('users_token.access_token'=>$access_token))
                          ->get(); */
                        $customer_id = $this->apiAccess->getManufacturerId($data);
                        //echo '<pre/>';print_r($validOrder[0]->customer_id);exit;
                        //$customer_id = $validOrder[0]->customer_id;

                        $order_token = $this->apiAccess->getuuid();
                        $dm_order_token = new DmOrderToken;
                        $dm_order_token->customer_id = $customer_id;
                        $dm_order_token->order_token = $order_token[0]->uuid;
                        $dm_order_token->date_time = date('Y-m-d h:i:s');
                        //$dm_order_token->user_agent=$data['user_agent'];
                        //echo "<pre>";print_r($dm_order_token);die;
                        $dm_order_token->save();
                        $return_token = $order_token[0]->uuid;
                        $subOrderGroupArr = array();
                        foreach ($product_data as $key => $value)
                        {
                            $pdb = 'eseal_' . $value['customer_id'];
                            $qty = $value['qty'];
                            //Update the stock for blocking
                            $Sql = "select e1.eseal_id  from " . $pdb . " e1 , track_history th  
                                    where th.track_id=e1.track_id and e1.gds_status=0 and e1.level_id=0 and e1.pid=" . $value['product_id'] . " and th.src_loc_id=".$ppid." and th.dest_loc_id=0 LIMIT " . $qty;
                            //echo $Sql;die;
                            $results = DB::select($Sql);
                            $temp = array();
                            foreach ($results as $result)
                            {
                                $temp [] = $result->eseal_id;
                            }
                            //echo "<pre/>";print_r($temp);exit;
                            DB::table($pdb)
                                    ->where(array('pid' => $value['product_id'], 'level_id' => 0, 'gds_status' => 0))
                                    ->whereIn('eseal_id', $temp)
                                    ->update(array('gds_status' => 1, 'gds_order' => $order_token[0]->uuid));
                        }
                    }
                    $status = 1;
                    $message = 'Stock is available.';
                } else
                {
                    $order_token = '';
                    $message = 'Out of Stock for the following products.';
                }
            } else
            {
                $status = 0;
                $message = 'Parameter Missing.';
                //throw new Exception($message);
            }
        } catch (Exception $e)
        {
            $message = $e->getMessage();
            //echo "<pre>";echo $message;print_R($e->getTraceAsString());die;
        }
        return Response::json(Array('Status' => $status, 'Message' => $message, 'order_token' => $return_token, 'Data' => $productAvailablearr));
    }

    public function unblockInventory($data)
    {
        try
        {
            $status = 0;
            $message = '';
            $p_data = json_decode($data['product_data'], true);
            $gds_order_token = $data['order_token'];

            $return_token = '';
            if (!empty($p_data))
            {
                $ppid = (isset($data['ppid']) && $data['ppid'] != '') ? $data['ppid'] : '';

                foreach ($p_data as $key => $value)
                {
                    $product_ids = DB::table('products')
                            ->select('products.product_id', 'products.sku', 'eseal_customer.customer_id')
                            ->leftJoin('eseal_customer', 'eseal_customer.customer_id', '=', 'products.manufacturer_id')
                            ->where('products.sku', $value['sku'])
                            ->get();
                    //print_r($product_ids[0]->product_id);exit;
                    $pids['product_id'] = $product_ids[0]->product_id;
                    $pids['sku'] = $product_ids[0]->sku;
                    $pids['customer_id'] = $product_ids[0]->customer_id;
                    $pids['qty'] = $value['quantity'];
                    $pids['price'] = $value['price'];
                    $pids['total'] = $value['total'];
                    $product_data[] = $pids;
                }
                //print_r($product_data);exit;

                $available = 1;
                foreach ($product_data as $key => $value)
                {
                    $pdb = 'eseal_' . $value['customer_id'];
                    $qty = $value['qty'];

                    DB::table($pdb)
                            ->where(array('pid' => $value['product_id'], 'level_id' => 0, 'gds_status' => 1, 'gds_order' => $gds_order_token))
                            ->update(array('gds_status' => 0, 'gds_order' => 'unknown', 'gds_sub_order' => 'unknown'));
                    $message = 'Quantity is ublocked.';
                }
            } else
            {
                $status = 0;
                $message = 'Parameter Missing.';
                //throw new Exception($message);
            }
        } catch (Exception $e)
        {
            $message = $e->getMessage();
        }
        return Response::json(Array('Status' => $status, 'Message' => $message));
    }

    /**
     * 
     * @param type $data,api_key,secret_key,gds_order_id,orderdata:{accept_language,comment,currency_code,currency_id,currency_value,firstname,invoice_prefix,ip,language_id,order_token,payment_address_1,payment_address_2,payment_city,payment_code,payment_company,payment_country,payment_country_id,payment_firstname,payment_lastname,payment_postcode,payment_zone,payment_zone_id,shipping_address_1,shipping_address_2,shipping_city,shipping_code,shipping_company,shipping_country,shipping_country_id,shipping_firstname,shipping_lastname,shipping_method,shipping_postcode,shipping_zone,shipping_zone_id,total,user_agent,products:{sku}}
     * @return type Status,Message,orderId.
     * @Description:This API call will place the order.
     */
    public function placeOrderOld($data)
    {
        try
        {
            $status = 0;
            $message = '';
            $order_data = json_decode($data['orderdata']);
            //return $order_data->order_token;
            //$access_token = $data['access_token']; 
            //echo "<pre/>";print_r($data['orderdata']);exit;
            //Finding whether this order token is valid or not
            /*          $validOrder = DB::table('users_token')
              ->select('users.customer_id')
              ->leftJoin('users','users.user_id','=','users_token.user_id')
              ->where(array('users_token.access_token'=>$access_token))
              ->get(); */
            $customer_id = $this->apiAccess->getManufacturerId($data);
            //echo '<pre/>';print_r($validOrder[0]->customer_id);exit;
            //$customer_id = $validOrder[0]->customer_id;
            //Finding whether this order token is valid or not
            //return $customer_id;
            $validOrder = DB::table('dm_order_token')
                    ->select('dm_order_token.order_token_id')
                    ->where(array('dm_order_token.customer_id' => $customer_id, 'dm_order_token.order_token' => $order_data->order_token))
                    ->count();
            //return $validOrder;
            //$queries = DB::getQueryLog();
            // return end($queries);
            //echo $validOrder;exit;
            $customer_details = $this->custRepo->getAllCustomers($customer_id);
            //echo '<pre/>';print_r($customer_details);exit;
            $eseal_orders = new EsealOrders;

            $eseal_orders->customer_id = $customer_details[0]->customer_id;
            $eseal_orders->customer_group_id = $customer_details[0]->customer_type_id;

            $eseal_orders->invoice_prefix = $order_data->invoice_prefix;
            $eseal_orders->firstname = $order_data->firstname;
            $eseal_orders->lastname = $customer_details[0]->lastname;
            $eseal_orders->email = $customer_details[0]->email;
            $eseal_orders->telephone = $customer_details[0]->phone;

            //payment person address details
            $eseal_orders->payment_firstname = $order_data->payment_firstname;
            $eseal_orders->payment_lastname = $order_data->payment_lastname;
            $eseal_orders->payment_company = $order_data->payment_company;
            $eseal_orders->payment_address_1 = $order_data->payment_address_1;
            $eseal_orders->payment_address_2 = $order_data->payment_address_2;
            $eseal_orders->payment_city = $order_data->payment_city;
            $eseal_orders->payment_postcode = $order_data->payment_postcode;
            $eseal_orders->payment_zone = $order_data->payment_zone;
            $eseal_orders->payment_zone_id = $order_data->payment_zone_id;
            $eseal_orders->payment_country = $order_data->payment_country;
            $eseal_orders->payment_country_id = $order_data->payment_country_id;
            $eseal_orders->payment_method = 22010;
            $eseal_orders->payment_code = $order_data->payment_code;


            //shipment person address details
            $eseal_orders->shipping_firstname = $order_data->shipping_firstname;
            $eseal_orders->shipping_lastname = $order_data->shipping_lastname;
            $eseal_orders->shipping_company = $order_data->shipping_company;
            $eseal_orders->shipping_address_1 = $order_data->shipping_address_1;
            $eseal_orders->shipping_address_2 = $order_data->shipping_address_2;
            $eseal_orders->shipping_city = $order_data->shipping_city;
            $eseal_orders->shipping_postcode = $order_data->shipping_postcode;
            $eseal_orders->shipping_zone = $order_data->shipping_zone;
            $eseal_orders->shipping_zone_id = $order_data->shipping_zone_id;
            $eseal_orders->shipping_country = $order_data->shipping_country;
            $eseal_orders->shipping_country_id = $order_data->shipping_country_id;
            $eseal_orders->shipping_method = $order_data->shipping_method;
            $eseal_orders->shipping_code = $order_data->shipping_code;

            $eseal_orders->order_status_id = 17006;
            $eseal_orders->order_token = $order_data->order_token;
            $eseal_orders->order_type = 20001;
            $eseal_orders->gds_order_id = $data['gds_order_id'];
            $eseal_orders->comment = $order_data->comment;
            $eseal_orders->total = $order_data->total;
            //$eseal_orders->tracking = $order_data->tracking;
            $eseal_orders->language_id = $order_data->language_id;
            $eseal_orders->currency_id = $order_data->currency_id;
            $eseal_orders->currency_code = $order_data->currency_code;
            $eseal_orders->currency_value = $order_data->currency_value;
            $eseal_orders->ip = $order_data->ip;
            //$eseal_orders->forwarded_ip = $order_data->forwarded_ip;
            $eseal_orders->user_agent = $order_data->user_agent;
            $eseal_orders->accept_language = $order_data->accept_language;
            $eseal_orders->date_added = date('Y-m-d h:i:s');
            $eseal_orders->date_modified = date('Y-m-d h:i:s');

            $eseal_orders->save();

            $order_id = DB::getPdo()->lastInsertId();
            $order_number = 'ORD' . date('yy') . date('mm') . str_pad($order_id, 6, "0", STR_PAD_LEFT);

            DB::table('eseal_orders')
                    ->where('order_id', $order_id)
                    ->update(array('order_number' => $order_number));

            //echo '<pre/>';print_r($order_data->products);exit;
            $mfgGrouparr = array();
            foreach ($order_data->products as $key => $value)
            {
                //echo '<pre/>';print_r($value->sku);exit;
                $product_ids = DB::table('products')
                        ->select('products.product_id', 'products.name', 'eseal_customer.customer_id')
                        ->leftJoin('eseal_customer', 'eseal_customer.customer_id', '=', 'products.manufacturer_id')
                        ->where('products.sku', $value->sku)
                        ->get();


                $eseal_order_products = new EsealOrderProducts;
                $eseal_order_products->order_id = $order_id;
                $eseal_order_products->pid = $product_ids[0]->product_id;
                $eseal_order_products->name = $product_ids[0]->name;
                $eseal_order_products->quantity = $value->quantity;
                $eseal_order_products->price = $value->price;
                //$eseal_order_products->discount =
                $eseal_order_products->total = $value->total;
                $eseal_order_products->tax = $value->tax;
                $eseal_order_products->sub_order_id = 0;
                $eseal_order_products->customer_id = 0;
                $eseal_order_products->location_id = 0;
                $eseal_order_products->save();

                $last_order_product_id = DB::getPdo()->lastInsertId();

                if (in_array($product_ids[0]->customer_id, $mfgGrouparr))
                {
                    
                } else
                {
                    array_push($mfgGrouparr, $product_ids[0]->customer_id);
                    $sub_order_id = $order_number . '_' . $product_ids[0]->customer_id;
                    DB::table('eseal_order_products')
                            ->where(array('order_product_id' => $last_order_product_id))
                            ->update(array('sub_order_id' => $sub_order_id, 'customer_id' => $product_ids[0]->customer_id));
                }

//             $pdb = 'eseal_'.$product_ids[0]->customer_id;
                /* $Sql =  "select e1.eseal_id  from ".$pdb." e1 , track_history th  
                  where th.track_id=e1.track_id and e1.gds_status=1 and e1.level_id=0 and e1.pid=".$product_ids[0]->product_id." and th.src_loc_id=22 and th.dest_loc_id=0 LIMIT ".$value->quantity; */

//              $Sql = "select eseal_id,gds_status,gds_order,gds_sub_order from ".$pdb." where gds_order='".$order_data->order_token."' and gds_status=1 and level_id=0 and pid=".$product_ids[0]->product_id." ";
//                  DB::table($pdb)
//                  ->where(array('pid'=>$product_ids[0]->product_id,'level_id'=>0,'gds_status'=>1,'gds_order'=>$order_data->order_token))
//                  ->update(array('gds_status' => 2,'gds_order'=>$order_number,'gds_sub_order'=>$sub_order_id));
                //Update the stock for reserve
                /* DB::table('eseal_'.$product_ids[0]->customer_id)
                  ->where(array('pid'=>$product_ids[0]->product_id,'gds_order'=>$order_data->order_token))
                  ->update(array('gds_status' => 2,'gds_order'=>$order_number,'gds_sub_order'=>$sub_order_id)); */
            }

            //For storing the payments
            $order_payments = new OrderPayments;
            $order_payments->order_id = $order_id;
            //$order_payments->payment_mode = $payments['payment_mode'];
            $order_payments->payment_type = 22010;
            /* $order_payments->trans_reference_no = $payments['trans_reference_no'];
              $order_payments->payee_bank = $payments['payee_bank'];
              $order_payments->ifsc_code = $payments['ifsc_code'];
              $order_payments->amount = $payments['amount']; */
            $order_payments->payment_date = date('Y-m-d h:i:s');
            $order_payments->save();

            $this->createSalesOrders($data, $order_data, $order_id);

            $status = 1;
            $message = 'Successfully placed order.';
        } catch (Exception $e)
        {
            $message = $e->getMessage();
        }
        return Response::json(Array('Status' => $status, 'Message' => $message, 'orderId' => $order_id));
    }

    /**
     * 
     * @param type $data,$order_id,$api_key,$secret_key
     * @return type Status,Message,orderId
     * @Description:This API call will cancel the order by updating the order status to 2.
     */
    public function cancelOrder($data)
    {
        try
        {
            $status = 0;
            $message = '';

            $orderId = $data['order_id'];
            $customer_id = $this->apiAccess->getManufacturerId($data);
            $channel_orders = new ChannelOrders();
            $gdsOrderId = $channel_orders->where('order_id', $orderId)->pluck('gds_order_id');
            //$last = DB::getQueryLog();
            //echo "<pre>";print_R(end($last));die;
            //echo $gdsOrderId;die;
            if (!empty($gdsOrderId))
            {
                $gds_orders = new GDSOrders();
                $gds_orders->where('gds_order_id', $gdsOrderId)->update(['order_status_id' => 17004]);
                $last = DB::getQueryLog();
                Log::info(end($last));
                //update eseal_manufacturer_id table
                $manfTable = 'eseal_' . $customer_id;
                //$gdsOrderId = EsealOrders::where(['order_id' => $order_id])->pluck('gds_order_id');
                DB::table($manfTable)->where(['gds_order' => $gdsOrderId])->update(['gds_status' => 0, 'gds_order' => '']);
                $last = DB::getQueryLog();
                Log::info(end($last));
                $channel_orders = new ChannelOrders;
                $channel_orders->where('order_id', $orderId)->update(['order_status' => 'CANCELLED']);
                $this->deleteSalesOrder($data);
                $status = 1;
                $message = 'Successfully cancelled the order';
            } else
            {
                $status = 0;
                $message = 'Order doesnot exist.';
            }
        } catch (\Whoops\Exception\ErrorException $e)
        {
            $status = 0;
            $message = $e->getMessage() . $e->getTraceAsString();
        }
        return Response::json(Array('Status' => $status, 'Message' => $message, 'orderId' => $orderId));
    }

    /**
     * 
     * @param type $data
     * @return type $data
     * @Description:
     */
    public function categoryCount($data)
    {
        return $data;
    }

    /**
     * 
     * @param type $data
     * @return type $data
     * @Description:
     */
    public function productCount($data)
    {
        return $data;
    }

    /**
     * 
     * @param type $data
     * @return type $data
     * @Description:
     */
    public function returnOrder($data)
    {
        return $data;
    }

    /**
     * 
     * @param type $data
     * @return type $data
     * @Description:
     */
    public function updateOrderStatus($data)
    {
        return $data;
    }

    /**
     * 
     * @param type $data
     * @return type $data
     * @Description:
     */
    public function placeBackOrder($data)
    {
        return $data;
    }

    /*
      @param type $data
      @return type product_name,description,model_name,upc,ean,jan,isbn,mpn,categories,mrp,cost_price,sku,attributes
      Description : This API request gets the manufacturer specific products in a limit that are sorted on product_id */

    public function getAllInventory($data)
    {
        try
        {
            $finalProductsarr = array();
            $finaltempProducts = array();
            
            $start_limit = (isset($data['start_limit']) && $data['start_limit'] != '') ? $data['start_limit'] : 0;
            $end_limit = (isset($data['end_limit']) && $data['end_limit'] != '') ? $data['end_limit'] : 50;
            // $filter = (isset($data['filter']) && $data['filter']!='') ? $data['filter'] : asc;
            //$manufacturer_id = (isset($data['manufacturer_id']) && $data['manufacturer_id'] != '') ? $data['manufacturer_id'] : '';
            $manufacturer_id = $this->apiAccess->getManufacturerId($data);
            $products = DB::table('products')
                    ->select('products.*', 'channel_category.category_id')
                    //->select('products.*','categories.name as cname','categories.category_id','eseal_customer.brand_name')
                    //->leftJoin('categories','categories.category_id','=','products.category_id')
                    ->leftJoin('channel_category', 'channel_category.category_id', '=', 'products.category_id')
                    ->where('products.is_gds_enabled', '=', '1');

            if (!empty($manufacturer_id))
            {
                $products = $products->where('products.manufacturer_id', $manufacturer_id);
            }
            $products = $products->orderBy('product_id', 'ASC')->skip($start_limit)->take($end_limit)->get();



            /* Log::info($products);
              $queries = DB::getQueryLog();
              return end($queries); */
            //echo '<pre/>';print_r($products);exit;

            foreach ($products as $key => $value)
            {
                if (!empty($value->sku))
                {
                    $categories = explode(',', $value->category_id);
                    $prodCatarr = DB::Table('categories')
                            //->select('categories.name')
                            ->whereIn('categories.category_id', $categories)
                            ->lists('categories.name');

                    //echo '<pre/>';print_r($prodCatarr);exit;

                    $finalarr = array();
                    $finalStaticsarr = array();
                    $finalSlabarr = array();
                    $finalMediaarr = array();
                    $prodStaticattr = DB::Table('product_attributes')
                            ->select('product_attributes.value', 'attributes.attribute_code', 'attributes.name', 'attributes.attribute_id', 'attributes.attribute_group_id', 'attributes_groups.name as attribute_group_name')
                            ->leftJoin('attributes', 'attributes.attribute_id', '=', 'product_attributes.attribute_id')
                            ->leftJoin('attributes_groups', 'attributes_groups.attribute_group_id', '=', 'attributes.attribute_group_id')
                            ->where(array('product_attributes.product_id' => $value->product_id, 'attribute_type' => 1))
                            ->get();

                    $StaticAttributesarr = array();
                    $attributeGrouparr = array();

                    foreach ($prodStaticattr as $key1 => $value1)
                    {
                        //$StaticAttributesarr[$value1->name] = $value1->value;
                        if (in_array($value1->attribute_group_name, $attributeGrouparr))
                        {
                            array_push($attributeGrouparr[$value1->attribute_group_name][$value1->name], $value1->value);
                        } else
                        {
                            $attributeGrouparr[$value1->attribute_group_name][$value1->name] = $value1->value;
                        }
                    }

                    $finalarr['product_name'] = $value->name;
                    $finalarr['description'] = $value->description;
                    $finalarr['model_name'] = $value->model_name;
                    $finalarr['upc'] = $value->upc;
                    $finalarr['ean'] = $value->ean;
                    $finalarr['jan'] = $value->jan;
                    $finalarr['isbn'] = $value->isbn;
                    $finalarr['mpn'] = $value->mpn;
                    $finalarr['categories'] = $prodCatarr;
                    $finalarr['mrp'] = $value->mrp;
                    $finalarr['cost_price'] = $value->cost_price;
                    // $finalarr['manufacturer_name']=$value->brand_name;              
                    $finalarr['sku'] = $value->sku;
                    $finalarr['attributes'] = $attributeGrouparr;

                    //Get Products Image Data              
                    $media = DB::table('product_media')
                            ->select('product_media.media_type', 'product_media.url')
                            ->leftJoin('products', 'products.product_id', '=', 'product_media.product_id')
                            ->where('product_media.product_id', $value->product_id)
                            ->get();
                    $doc_root = $_SERVER['SERVER_NAME'] . '/uploads/products/';
                    //echo '<pre/>';print_r($inTransitQty);exit; 
                    $mediaarr = array();
                    foreach ($media as $key3 => $value3)
                    {
                        if (!empty($value3->media_type))
                            $mediaarr[$value3->media_type][] = $doc_root . $value3->url;
                    }
                    $finalarr['media'] = $mediaarr;

                    //Get Products slab rates              
                    $slabRates = DB::table('products_slab_rates')
                            ->select('products_slab_rates.start_range', 'products_slab_rates.end_range', 'products_slab_rates.price')
                            ->leftJoin('products', 'products.product_id', '=', 'products_slab_rates.product_id')
                            ->where('products_slab_rates.product_id', $value->product_id)
                            ->get();
                    //echo '<pre/>';print_r($inTransitQty);exit; 
                    $tempSlab = array();
                    foreach ($slabRates as $key2 => $value2)
                    {
                        $slabarr['qty'] = $value2->start_range . '-' . $value2->end_range;
                        $slabarr['price'] = $value2->price;
                        $tempSlab[] = $slabarr;
                    }
                    $finalSlabarr[] = $tempSlab;
                    $finalarr['slab_rates'] = $finalSlabarr;
                    $finaltempProducts[] = $finalarr;
                }
            }
            $status = 1;
            $message = 'Data Successfully Retrieved.';
        } catch (ErrorException $e)
        {
            $status = 0;
            $message = $e->getMessage();
            //Log::info($e->getTraceAsString());
        }
        return Response::json(Array('Status' => $status, 'Message' => $message, 'Data' => $finaltempProducts));
    }

    /**
     * 
     * @param type $data
     * @return type category_id,name,description,parent
     * @Description: This API request is used to get all the available categories.
     */
    public function getAllCategories($data)
    {
        $status = 0;
        try
        {
            //$manufacturer_id = (isset($data['manufacturer_id']) && $data['manufacturer_id'] != '') ? $data['manufacturer_id'] : '';
            $manufacturer_id = $this->apiAccess->getManufacturerId($data);
            
            $categories = DB::select("SELECT  c.category_id as category_id,  c.name as name,  c.description as description,  p.name  as parent FROM categories c left join categories p
              on c.parent_id=p.category_id order by p.parent_id,c.sort_order");

            $status = 1;
            $message = 'Data Successfully Retrieved.';
        } catch (Exception $e)
        {
            $status = 0;
            $message = $e->getMessage();
        }
        return Response::json(Array('Status' => $status, 'Message' => $message, 'Data' => $categories));
    }

    /**
     * 
     * @param type $data
     * @return type status,message,array(categoryname,brand_name)
     * @Description: This API request is used to get all the categories along with customers.
     */
    public function getAllCategoriesByCustomer($data)
    {
        $status = 0;
        try
        {
            $manufacturerId = $this->apiAccess->getManufacturerId($data);
            $customer_sub_groups = DB::table('customer_categories')
                    ->select('customer_categories.category_id', 'categories.name')
                    ->leftJoin('categories', 'categories.category_id', '=', 'customer_categories.category_id')
                    ->where('customer_categories.customer_id', $manufacturerId)
                    ->get();
            $catArr = array();
            foreach ($customer_sub_groups as $key1 => $value1)
            {
                $catArr[$value1->category_id] = $value1->name;
            }
            $finalCustCatArr[] = $catArr;

            $status = 1;
            $message = 'Data Successfully Retrieved.';
        } catch (Exception $e)
        {
            $status = 0;
            $message = $e->getMessage();
        }
        return Response::json(Array('Status' => $status, 'Message' => $message, 'Data' => $finalCustCatArr));
    }

    public function createSalesOrders($data, $productInfo, $orderId, $channelId)
    {
        try
        {
            $mfgId = $this->apiAccess->getManufacturerId($data);
            if ($mfgId == 0)
            {
                return Response::json(Array('Status' => false, 'Message' => 'Wrong data.'));
            }
            $erpIntegrationData = $this->apiAccess->getErpIntegration($mfgId);
            if (empty($erpIntegrationData))
            {
                return Response::json(Array('Status' => false, 'Message' => 'Wrong data.'));
            }
            $erpIntegrationId = $erpIntegrationData->id;
            $this->_url = $erpIntegrationData->web_service_url;
            $this->_method = 'Z0013_ESEAL_CREATE_SALES_ORDER_SRV';
            $this->_method_name = 'CREATE_SO';
            $url = $this->_url . $this->_method . '/' . $this->_method_name;
            $this->_sap_api_repo = new Central\Repositories\SapApiRepo();
            $this->_return_type = 'xml';
            $this->_token = $erpIntegrationData->token;
            $id = 123;
            $username = $erpIntegrationData->web_service_username;
            $password = $erpIntegrationData->web_service_password;
            $channelId = 0;
            $erpIntegrationAdditionalData = $this->apiAccess->getErpIntegrationAdditionalData($erpIntegrationId, $channelId);

            $SALES_ORG = $erpIntegrationAdditionalData->sales_org;
            $DISTR_CHAN = $erpIntegrationAdditionalData->distr_chan;
            $DIVISION = $erpIntegrationAdditionalData->division;
            $CREATE_DELIVERY = $erpIntegrationAdditionalData->create_delivery;
            $SHIPPING_POINT = $erpIntegrationAdditionalData->shipping_point;
            $DOC_TYPE = $erpIntegrationAdditionalData->doc_type;
            $SH_PARTN_NUMB = $erpIntegrationAdditionalData->sh_partn_numb;
            $SP_PARTN_NUMB = $erpIntegrationAdditionalData->sp_partn_numb;

            $itemData = '';
            $patnersData = '';
            $incrementId = 000010;
            $plantCode = 1010;
            //Log::info($productInfo);
            foreach ($productInfo as $key => $value)
            {
                $productData = DB::table('products')
                        ->select('products.product_id', 'products.name', 'products.weight_class_id', 'products.material_code', 'eseal_customer.customer_id')
                        ->leftJoin('eseal_customer', 'eseal_customer.customer_id', '=', 'products.manufacturer_id')
                        ->where('products.sku', $value->sku)
                        ->first();
                if (!empty($productData))
                {
                    //Log::info($incrementId);
                    $itemData = $itemData . '<ITEM ITM_NUMBER="' . $incrementId . '" NAME="' . substr(str_replace(' ', '', $productData->material_code), 0, 18) . '" ' . 'QTY="' . $value->quantity . '" ' . 'UOM="' . $productData->weight_class_id . '" ' . 'PLANT="' . $plantCode . '" ' . 'STORAGE_LOC="" /> ';
                    $patnersData = $patnersData . '<PARTNER ROLE="SH" PARTN_NUMB="' . $SH_PARTN_NUMB . '" ITM_NUMBER="' . $incrementId . '" TITLE="' . $order_data->shipping_company . '" NAME="' . $order_data->shipping_firstname . '"  NAME_2="' . $order_data->shipping_lastname . '"  NAME_3=""  STREET="' . substr(str_replace(' ', '', $order_data->shipping_address_1 . ' ' . $order_data->shipping_address_2), 0, 18) . '"  COUNTRY_KEY="IN"  POSTL_CODE="' . $order_data->shipping_postcode . '"  CITY="' . $order_data->shipping_city . '"  DISTRICT=""  REGION_KEY="01"  TELEPHONE=""  /> ';
                    $incrementId + 10;
                } else
                {
                    return Response::json(Array('Status' => false, 'Message' => 'Wrong Sku.'));
                }
                //Log::info($incrementId);
            }
            $patnersData = $patnersData . '<PARTNER ROLE="SP" PARTN_NUMB="' . $SP_PARTN_NUMB . '" ITM_NUMBER="" TITLE="" NAME="" NAME_2="" NAME_3="" STREET="" COUNTRY_KEY="" POSTL_CODE="" CITY="" DISTRICT="" REGION_KEY="" TELEPHONE="" /> ';
            $esealKey = uniqid();
            $xml = '<entry 
    xmlns="http://www.w3.org/2005/Atom" 
    xmlns:m="http://schemas.microsoft.com/ado/2007/08/dataservices/metadata" 
    xmlns:d="http://schemas.microsoft.com/ado/2007/08/dataservices" xml:base="' . $this->_url . '/' . $this->_method . '/">
    <id>' . $url . '(' . $id . ')</id>
    <title type="text">CREATE_SO(' . $id . ')</title>
    <updated>2015-07-22T10:35:14Z</updated>
    <category term="Z0013_ESEAL_CREATE_SALES_ORDER_SRV.CREATE_SO" scheme="http://schemas.microsoft.com/ado/2007/08/dataservices/scheme" />
    <link href="CREATE_SO(' . $id . ')" rel="self" title="CREATE_SO" />
    <content type="application/xml">
        <m:properties>
            <d:Sales_Order />
            <d:Message />
            <d:Eseal_input>"
                <![CDATA[ <?xml version="1.0" encoding="utf-8" ?> 
  <REQUEST>
  <DATA>
  <INPUT TOKEN_NO="' . $this->_token . '" ESEALKEY="' . $esealKey . '" 
  DOC_TYPE="' . $DOC_TYPE . '" SALES_ORG="' . $SALES_ORG . '" DISTR_CHAN="' . $DISTR_CHAN . '" DIVISION="' . $DIVISION . '" CREATE_DELIVERY="' . $CREATE_DELIVERY . '" SHIPPING_POINT="' . $SHIPPING_POINT . '" PO_REFERENCE="123" /> 
  <ITEMS>
  ' . $itemData . '
  </ITEMS>
  <PARTNERS>
  ' . $patnersData . '  
  </PARTNERS>
  <CONDITIONS>
  <CONDITION ITM_NUMBER="" COND_TYPE="" COND_VALUE="" />
  </CONDITIONS>
  </DATA>
  </REQUEST> ]]>"
            </d:Eseal_input>
        </m:properties>
    </content>
</entry>';
            //Log::info($xml);
            //echo "<pre>";print_R($xml);die;
            $method = 'POST';
            $response = $this->_sap_api_repo->request($username, $password, $url, $method, null, 'xml', 2, '', $xml);
            //Log::info($response);
            $parseData1 = xml_parser_create();
            xml_parse_into_struct($parseData1, $response, $documentValues1, $documentIndex1);
            xml_parser_free($parseData1);
            //Log::info($parseData1);
            $documentData = '';
            $status = '';
            foreach ($documentValues1 as $data)
            {
                if (isset($data['tag']) && $data['tag'] == 'D:SALES_ORDER')
                {
                    $documentData = $data['value'];
                    $status = $data['type'];
                }
            }
            DB::table('gds_orders')
                    ->join('channel_order', 'channel_order.channel_order_id', '=', 'gds_orders.channel_order_id')
                    ->where('order_id', $orderId)
                    ->update(['erp_order_id' => $documentData]);
            return Response::json(Array('Status' => true, 'Message' => $status));
        } catch (\ErrorException $ex)
        {
            return $ex->getMessage() . $ex->getTraceAsString();
        }
    }

    //public function updateSalesOrders($data, $order_data, $orderId)
    public function updateSalesOrders($data)
    {
        try
        {
            $order_data = json_decode($data['order_data']);
            $orderId = $order_data->order_id;
            //Log::info(__METHOD__);
            $mfgId = $this->apiAccess->getManufacturerId($data);
            //Log::info(' mfgId => ' . $mfgId);
            if ($mfgId == 0)
            {
                return Response::json(Array('Status' => false, 'Message' => 'Wrong data.'));
            }
            $erpIntegrationData = $this->apiAccess->getErpIntegration($mfgId);
            if (empty($erpIntegrationData))
            {
                return Response::json(Array('Status' => false, 'Message' => 'Wrong data.'));
            }
            $erpIntegrationId = $erpIntegrationData->id;
            $this->_url = $erpIntegrationData->web_service_url;
            $this->_method = 'Z0046_ESEAL_UPDATE_SO_SRV';
            $this->_method_name = 'UPDATE_SO';
            $url = $this->_url . $this->_method . '/' . $this->_method_name;
            $this->_sap_api_repo = new Central\Repositories\SapApiRepo();
            $this->_return_type = 'xml';
            $this->_token = $erpIntegrationData->token;
            $id = 123;
            $username = $erpIntegrationData->web_service_username;
            $password = $erpIntegrationData->web_service_password;
            if (property_exists($order_data, 'channel_id'))
            {
                $channelId = $order_data->channel_id;
            } else
            {
                return Response::json(Array('Status' => false, 'Message' => 'Need channel id.'));
            }
            $erpIntegrationAdditionalData = $this->apiAccess->getErpIntegrationAdditionalData($erpIntegrationId, $channelId);

            $erpOrderID = DB::table('eseal_orders')->where('order_id', $orderId)->pluck('erp_order_id');

            $SALES_ORG = $erpIntegrationAdditionalData->sales_org;
            $DISTR_CHAN = $erpIntegrationAdditionalData->distr_chan;
            $DIVISION = $erpIntegrationAdditionalData->division;
            $CREATE_DELIVERY = $erpIntegrationAdditionalData->create_delivery;
            $SHIPPING_POINT = $erpIntegrationAdditionalData->shipping_point;
            $DOC_TYPE = $erpIntegrationAdditionalData->doc_type;
            $SH_PARTN_NUMB = $erpIntegrationAdditionalData->sh_partn_numb;
            $SP_PARTN_NUMB = $erpIntegrationAdditionalData->sp_partn_numb;

            $indicator = $order_data->indicator;
            $itemData = '';
            $patnersData = '';
            $plantCode = 1010;
            //Log::info($order_data->products);
            foreach ($order_data->products as $product)
            {
                $productData = DB::table('products')
                        ->select('products.product_id', 'products.name', 'products.weight_class_id', 'products.material_code', 'eseal_customer.customer_id')
                        ->leftJoin('eseal_customer', 'eseal_customer.customer_id', '=', 'products.manufacturer_id')
                        ->where('products.sku', $product->sku)
                        ->first();
                if (!empty($productData))
                {
                    $itemData = $itemData . '<ZESEALS046_SO_UPDATE MATERIAL="' . substr(str_replace(' ', '', $productData->material_code), 0, 18) . '" BATCH="" PLANT="' . $plantCode . '" STORE_LOC="" TARGET_QTY="' . $product->quantity . '" INDICATOR="' . $indicator . '" />';
                } else
                {
                    return Response::json(Array('Status' => false, 'Message' => 'Wrong Sku.'));
                }
            }
            $patnersData = $patnersData . '<PARTNER ROLE="SP" PARTN_NUMB="' . $SP_PARTN_NUMB . '" ITM_NUMBER="" TITLE="" NAME="" NAME_2="" NAME_3="" STREET="" COUNTRY_KEY="" POSTL_CODE="" CITY="" DISTRICT="" REGION_KEY="" TELEPHONE="" /> ';
            $esealKey = uniqid();
            $xml = '<entry xmlns="http://www.w3.org/2005/Atom" xmlns:m="http://schemas.microsoft.com/ado/2007/08/dataservices/metadata" xmlns:d="http://schemas.microsoft.com/ado/2007/08/dataservices" xml:base="http://14.141.81.243:8000/sap/opu/odata/sap/Z0046_ESEAL_UPDATE_SO_SRV/">
<id>http://14.141.81.243:8000/sap/opu/odata/sap/Z0046_ESEAL_UPDATE_SO_SRV/UPDATE_SO(\'123\')</id>
<title type="text">UPDATE_SO(\'123\')</title>
<updated>2015-09-16T13:30:24Z</updated>
<category term="Z0046_ESEAL_UPDATE_SO_SRV.UPDATE_SO" scheme="http://schemas.microsoft.com/ado/2007/08/dataservices/scheme" />
<link href="UPDATE_SO(\'123\')" rel="self" title="UPDATE_SO" />
<content type="application/xml">
 <m:properties>
<d:ESEAL_INPUT>"<![CDATA[ <?xml version="1.0" encoding="utf-8" ?> 
  <REQUEST>
  <DATA>
  <INPUT TOKEN="' . $this->_token . '" ESEAL_KEY="' . $esealKey . '" SALES_ORDER="' . $erpOrderID . '" /> 
  <SO_UPDATE>
  ' . $itemData . '
  </SO_UPDATE>
  </DATA></REQUEST> ]]>"
</d:ESEAL_INPUT>
<d:ESEAL_OUTPUT />
</m:properties>
</content>
</entry>';
            //echo $xml;
            //echo "-----------------------------";
            //Log::info($xml);
            //echo "<pre>";print_R($xml);die;
            $method = 'GET';
            $response = $this->_sap_api_repo->request($username, $password, $url, $method, null, 'xml', 2, '', $xml);
            //Log::info($response);
            //echo $response;
            $parseData1 = xml_parser_create();
            xml_parse_into_struct($parseData1, $response, $documentValues1, $documentIndex1);
            xml_parser_free($parseData1);
            //Log::info($parseData1);
//            echo "<pre>";
//            print_R($parseData1);
            $documentData = '';
            $status = '';
            foreach ($documentValues1 as $data)
            {
                if (isset($data['tag']) && $data['tag'] == 'D:ESEAL_OUTPUT')
                {
                    $documentData = $data['value'];
                    //$status = $data['type'];
                }
            }
//            echo "<pre>";
//            print_R($documentData);
//            die;
            //DB::table('eseal_orders')->where('order_id', $orderId)->update(['erp_order_id' => $documentData]);
            return Response::json(Array('Status' => true, 'Message' => $status));
        } catch (\ErrorException $ex)
        {
            return $ex->getMessage() . $ex->getTraceAsString();
        }
    }

    public function deleteSalesOrder($data)
    {
        try
        {
            $status = 0;
            $response = array();
            $this->ApiAccess = new MasterApiRepo();
            $mfgId = $this->ApiAccess->getManufacturerId($data);
            if (empty($mfgId))
            {
                return Response::json(Array('Status' => false, 'Message' => 'Wrong data.'));
            }
            $erpIntegrationData = $this->apiAccess->getErpIntegration($mfgId);
            if (empty($erpIntegrationData))
            {
                return Response::json(Array('Status' => false, 'Message' => 'Wrong data.'));
            }
            $this->_url = $erpIntegrationData->web_service_url;
            $this->_method = 'Z0045_DELETE_SALES_ORDER_SRV';
            $this->_method_name = 'DELETE_SO';
            $url = $this->_url . $this->_method . '/' . $this->_method_name;
            $this->_sap_api_repo = new Central\Repositories\SapApiRepo();
            $this->_return_type = 'xml';
            $this->_token = $erpIntegrationData->token;
            $username = $erpIntegrationData->web_service_username;
            $password = $erpIntegrationData->web_service_password;
            $sapClient = $erpIntegrationData->sap_client;

            $orderId = $data['order_id'];
            $eseal_order_id = DB::table('gds_orders')
                    ->join('channel_orders', 'channel_orders.gds_order_id', '=', 'gds_orders.gds_order_id')
                    ->where(array('channel_orders.order_id' => $orderId))
                    ->pluck('gds_orders.erp_order_id');
            //$last = DB::getQueryLog();
            //echo "<pre>";print_R(end($last));die;
            $last = DB::getQueryLog();
            Log::info(end($last));
            Log::info('eseal_order_id');
            Log::info($eseal_order_id);
            $orderData['TOKEN'] = $this->_token;
            $orderData['SALES_ORDER'] = $eseal_order_id;
            $response = $this->_sap_api_repo->request($username, $password, $url, 'GET', $orderData, $this->_return_type, '', '', '', $sapClient);
            $status = 1;
            $message = 'Order deleted sucessfully.';
        } catch (ErrorException $e)
        {
            $message = $e->getMessage();
        }
        return json_encode(['Status' => $status, 'Message' => $message, 'Response' => $response]);
    }

    public function getProductCount($data)
    {
        try
        {
            $result = '';
            $status = '';
            $message = '';
            $countProducts = 0;
            $manufacturer_id = $this->apiAccess->getManufacturerId($data);
            $category_id = Input::get('category_id');
            /* retrieve product count based on manufacturer_id and category_id */
            if ($category_id > 0)
            {
                $countProducts = DB::table('products')
                        ->select('product_id', 'category_id')
                        ->where(array('manufacturer_id' => $manufacturer_id, 'category_id' => $category_id, 'is_gds_enabled' => 1, 'is_deleted' => 0))
                        ->count();
                $last = DB::getQueryLog();
                Log::info(end($last));
            } else
            {
                $countProducts = DB::table('products')
                        ->select('product_id', 'category_id')
                        ->where(array('manufacturer_id' => $manufacturer_id, 'is_gds_enabled' => 1, 'is_deleted' => 0))
                        ->count();
            }

            if (!empty($countProducts))
            {
                $status = 1;
                $result = $countProducts;
                $message = 'Count Retrieved Successfully';
            } else
            {
                $status = 0;
                $result = 0;
                $message = 'No count Retrieved';
            }
        } catch (ErrorException $ex) {
            $status = 0;
            $result = 0;
            $message = $ex->getMessage();
        }
        return Response::json(['Status' => $status, 'Message' => $message, 'Count' => $result]);
    }

    public function getZonebyPincode($pincode, $manufacturerId)
    {
        try
        {
            //$pincode = Input::get('pincode');
            $result = '';
            $status = '';
            $message = '';
            //$values = '';

            $locationPincodeData = DB::table('cities_pincodes')
                    ->leftJoin('countries', 'countries.country_id', '=', 'cities_pincodes.country_id')
                    //->join(DB::raw('zone', 'zone.name', '=', 'cities_pincodes.state'))
                    ->select('cities_pincodes.*', 'countries.name as Country')
                    ->where(array('cities_pincodes.PinCode' => $pincode))
                    ->first();
            $last = DB::getQueryLog();
            //echo "<pre>";print_R(end($last));die;
            //echo "<pre>"; echo "data :"; print_r($locationPincodeData); die();
            $locationId = 0;
            if(!empty($locationPincodeData))
            {
                $cityId = $locationPincodeData->city_id;
                $locationId = DB::table('location_city_mapping')
                    ->where(array('cities' => $cityId, 'manufacturer_id' => $manufacturerId))
                    ->pluck('location_id');
            }
            if (!empty($locationId))
            {
                $status = 1;
                $result = $locationId;
                $message = 'Data retrieved';
            } else
            {
                $status = 0;
                $result = $locationId;
                $message = 'No data Retrieved';
            }
        } catch (Exception $ex) {
            $message = $ex->getMessage();
        }
        return json_encode(['Status' => $status, 'Message' => $message, 'location_id' => $result]);
    }

    public function channelCustomer($data)
    {
        try
        {
            if(is_array($data))
            {
              $data = json_decode(json_encode($data), FALSE);
            }
            /*echo "<pre>"; print_r($data); die();*/
            $status = 0;
            $message = '';
            $userId = 0;
            if(!property_exists($data, 'channel_user_id'))
            {
              $userId = DB::table('channel_customer')
                    ->where(['email_address' => $data->email_address, 'channel_id' => $data->channel_id])
                    ->pluck('channel_cust_id');
            }elseif(property_exists($data, 'channel_user_id') && $data->channel_user_id != ''){                
              $userId = DB::table('channel_customer')
                    ->where(['channel_user_id' => $data->channel_user_id, 'channel_id' => $data->channel_id])
                    ->pluck('channel_cust_id');  
            }elseif(property_exists($data, 'channel_user_id') && $data->channel_user_id == ''){
              $userId = DB::table('channel_customer')
                    ->where(['email_address' => $data->email_address, 'channel_id' => $data->channel_id])
                    ->pluck('channel_cust_id');
            }
            $customerArray = ['suffix' => isset($data->suffix) ? $data->suffix : '',
                'first_name' => $data->first_name,
                'last_name' => $data->last_name,
                'middle_name' => $data->middle_name,
                'channel_user_id' => property_exists($data, 'channel_user_id') ? $data->channel_user_id : $data->email_address,
                'email_address' => $data->email_address,
                'mobile_no' => $data->mobile_no,
                'dob' => isset($data->dob) ? $data->dob : '',
                'channel_id' => $data->channel_id,
                'gender' => isset($data->gender) ? $data->gender : '',
                'registered_date' => isset($data->registered_date) ? $data->registered_date : date('Y-m-d H:i:s')
            ];
            try
            {
                if (empty($userId))
                {
                    $userId = DB::table('channel_customer')->insertGetId($customerArray);
                    $status = 1;
                    $message = 'Successfully inserted.';
                } else
                {
                    if (isset($customerArray['channel_user_id']))
                    {
                        unset($customerArray['channel_user_id']);
                    }
                    if (isset($customerArray['channel_id']))
                    {
                        unset($customerArray['channel_id']);
                    }
                    if (isset($customerArray['registered_date']))
                    {
                        unset($customerArray['registered_date']);
                    }
                    DB::table('channel_customer')
                            ->where('channel_cust_id', $userId)
                            ->update($customerArray);
                  $status = 1;
                  $message = 'Successfully Updated.';
                }
                Log::info('userId');
                Log::info($userId);
                if($userId > 0)
                {
                    $address1 = property_exists($data, 'address1') ? $data->address1 : '';
                    Log::info('address1');
                    Log::info($address1);
                    if($address1 != '')
                    {
                        $otherInfo['customer_id'] = $userId;
                        $otherInfo['order_id'] = 0;
                        $otherInfo['channel_id'] = $data->channel_id;
                        Log::info('otherInfo');
                        Log::info($otherInfo);
                        $data->email = $data->email_address;
                        $data->phone = '';
                        $response = $this->customerAddress($data, $otherInfo);
                        Log::info('response');
                        Log::info($response);
                    }
                }
            } catch (ErrorException $e)
            {
                $message = $e->getMessage();
            }
        } catch (Exception $e)
        {
            $message = $e->getMessage(). $e->getTraceAsString();
        }
        return json_encode(Array('Status' => $status, 'Message' => $message, 'channel_cust_id' => $userId));
    }

    public function customerAddress($data, $otherInfo)
    {
        try
        {
            $channelCustId = isset($otherInfo['customer_id']) ? $otherInfo['customer_id'] : 0;
            $orderId = isset($otherInfo['order_id']) ? $otherInfo['order_id'] : 0;
            $channelId = isset($otherInfo['channel_id']) ? $otherInfo['channel_id'] : 0;
            if ($channelCustId)
            {
                if (isset($otherInfo['customer_id']))
                {
                    unset($otherInfo['customer_id']);
                }
                //customer address check
                $custAddressId = DB::table('channel_orders_address')
                        ->where($otherInfo)
                        ->where('address_type', $data->address_type)
                        ->pluck('channel_address_id');

                $custAddressArray = [
                    'first_name' => property_exists($data, 'first_name') ? $data->first_name : '',
                    'middle_name' => property_exists($data, 'middle_name') ? $data->middle_name : '',
                    'last_name' => property_exists($data, 'last_name') ? $data->last_name : '',
                    'address_type' => property_exists($data, 'address_type') ? $data->address_type : '',
                    'company' => property_exists($data, 'company') ? $data->company : '',
                    'address1' => property_exists($data, 'address1') ? $data->address1 : '',
                    'address2' => property_exists($data, 'address2') ? $data->address2 : '',
                    'city' => property_exists($data, 'city') ? $data->city : '',
                    'state' => property_exists($data, 'state') ? $data->state : '',
                    'country' => property_exists($data, 'country') ? $data->country : '',
                    'pincode' => property_exists($data, 'pincode') ? $data->pincode : '',
                    'email' => property_exists($data, 'email') ? $data->email : '',
                    'phone' => property_exists($data, 'phone') ? $data->phone : '',
                    'mobile' => property_exists($data, 'mobile_no') ? $data->mobile_no : '',
                    'channel_cust_id' => $channelCustId,
                    'channel_id' => $channelId,
                    'order_id' => $orderId
                ];

                if (empty($custAddressId))
                {
                    DB::table('channel_orders_address')->insert($custAddressArray);
                } else
                {
//                    if (isset($custAddressArray['channel_cust_id']))
//                    {
//                        unset($custAddressArray['channel_cust_id']);
//                    }
                    $tempArray = array_diff_assoc($custAddressArray, $otherInfo);
                    DB::table('channel_orders_address')
                            ->where('channel_cust_id', $channelCustId)
                            ->where('address_type', $data->address_type)
                            ->update($tempArray);
                }
                $status = 1;
                $message = "Sucessfully";
            } else
            {
                $status = 0;
                $message = "No Channel Cust id.";
            }
            return json_encode(Array('Status' => $status, 'Message' => $message, 'channel_cust_id' => $channelCustId));
        } catch (ErrorException $ex)
        {
            return $ex->getMessage() . $ex->getTraceAsString();
        }
    }

    public function placeOrder($orderdata)
    {
        //return $data;
        try
        {
            //Log::info($orderdata);
            $status = 0;
            $order_id = 0;
            $message = '';
            $order_data = json_decode($orderdata['orderdata']);
            //Log::info($order_data);
            //return $data;
            $customerData = array();
            $customerArray = isset($order_data->customer_info) ? ($order_data->customer_info) : array();
            if (!empty($customerArray))
            {
                $customerData = $this->channelCustomer($customerArray);
            }
            $customerInfo = json_decode($customerData);
            if (isset($customerInfo->Status) && $customerInfo->Status == 0)
            {
                $status = 0;
                $message = isset($customerInfo->Status) ? $customerInfo->Status : 'Wrong data provided.';
                return Response::json(array('Status' => $status, 'Message' => $message));
            }
            $customer_id = isset($customerInfo->channel_cust_id) ? $customerInfo->channel_cust_id : 0;
            //Log::info($customer_id);
            // return $customer_id;

            /*          $validOrder = DB::table('dm_order_token')
              ->select('dm_order_token.order_token_id')
              ->where(array('dm_order_token.customer_id'=>$customer_id,'dm_order_token.order_token'=>$order_data->order_token))
              ->count(); */
            $channelId = $order_data->customer_info->channel_id;
            //$customer_details = $this->custRepo->getAllCustomers($customer_id);
            if (property_exists($order_data, 'order_info'))
            {
                $orderInfo = $order_data->order_info;
            }
            if (empty($orderInfo))
            {
                $status = 0;
                $message = 'No Order info.';
                return Response::json(array('Status' => $status, 'Message' => $message));
            }
            $order_id = $this->channelOrderDetails($orderInfo,$customer_id);
            if (!$order_id)
            {
                $status = 0;
                $message = 'Unable to save order info.';
                return Response::json(array('Status' => $status, 'Message' => $message));
            } else
            {
                if ($customer_id)
                {
                    $customerAddress = isset($order_data->address_info) ? $order_data->address_info : array();
                    if (!empty($customerAddress))
                    {
                        $otherInfo['customer_id'] = $customer_id;
                        $otherInfo['order_id'] = $order_id;
                        $otherInfo['channel_id'] = $channelId;
                        foreach ($customerAddress as $address)
                        {
                            $cust = $this->customerAddress($address, $otherInfo);
                        }
                    }
                }
            }
            //Log::info($order_id);

            if (!empty($order_id) && isset($order_id))
            {
                //payments starts
                if (property_exists($order_data, 'payment_info'))
                {
                    $paymentDetails = $order_data->payment_info;
                }
                if (!empty($paymentDetails))
                {
                    foreach ($paymentDetails as $paymentInfo)
                    {
                        $this->channelPayment($order_id, $paymentInfo);
                    }
                }
                //payments ends
                $productDetails = array();
                if (property_exists($order_data, 'product_info'))
                {
                    $productDetails = $order_data->product_info;
                }
                if (!empty($productDetails))
                {
                    foreach ($productDetails as $product)
                    {
                        $this->channelOrderProducts($order_id, $product, $channelId);
                    }
                }
            }
            $tempData['order_id'] = $order_id;
            $this->gdsOrder($tempData);
            $this->createSalesOrders($orderdata, $productDetails, $order_id, $channelId);
            $status = 1;
            $message = 'Successfully placed order.';
            //Log::info($order_id);
        } catch (ErrorException $e)
        {
            $order_id = 0;
            $message = $e->getTraceAsString();
        }
        return Response::json(array('Status' => $status, 'Message' => $message, 'order_id' => $order_id));
    }

    public function channelOrderDetails($order_data,$customer_id)
    {
        try
        {
            $channel_orders = new ChannelOrders;

            $orderId = $channel_orders->where('channel_order_id', $order_data->channelorderid)->pluck('order_id');
            if (empty($orderId))
            {
                $channel_orders->channel_id = $order_data->channelid;
                $channel_orders->channel_order_id = $order_data->channelorderid;
                $channel_orders->order_status = $order_data->orderstatus;
                $channel_orders->order_date = $order_data->orderdate;
                $channel_orders->payment_method = $order_data->paymentmethod;
                $channel_orders->shipping_cost = $order_data->shippingcost;
                $channel_orders->sub_total = $order_data->subtotal;
                $channel_orders->tax = $order_data->tax;
                $channel_orders->total_amount = $order_data->totalamount;
                $channel_orders->currency_code = $order_data->currencycode;
                $channel_orders->channel_order_status = $order_data->channelorderstatus;
                $channel_orders->updated_date = $order_data->updateddate;
                $channel_orders->gds_order_id = $order_data->gdsorderid;
                $channel_orders->created_date = $order_data->createddate;
                $channel_orders->channel_cust_id = $customer_id;
                /*
                  $channel_orders->date_added = date('Y-m-d h:i:s');
                  $channel_orders->date_modified = date('Y-m-d h:i:s');
                 */
                $channel_orders->save();
                //return $channel_orders;
                $order_id = DB::getPdo()->lastInsertId();
            } else
            {
                $order_id = $orderId;
            }

            return $order_id;
        } catch (ErrorException $ex)
        {
            //Log::info($ex->getMessage());
            return 0;
        }
    }

    public function channelPayment($order_id, $order_data)
    {
        try
        {
            $paymentId = DB::table('channel_order_payment')
                    ->where(['order_id' => $order_id,
                        'channel_id' => $order_data->channelid,
                        'payment_method_id' => $order_data->paymentmethod,
                        'payment_status_id' => $order_data->paymentstatus,
                        'payment_currency' => $order_data->paymentcurrency,
                        'amount' => $order_data->amount,
                        'buyer_email' => $order_data->buyeremail])
                    ->pluck('transaction_id');
            if (empty($paymentId))
            {
                DB::table('channel_order_payment')->insert([
                    'order_id' => $order_id,
                    'channel_id' => $order_data->channelid,
                    'payment_method_id' => $order_data->paymentmethod,
                    'payment_status_id' => $order_data->paymentstatus,
                    'payment_currency' => $order_data->paymentcurrency,
                    'amount' => $order_data->amount,
                    'buyer_email' => $order_data->buyeremail,
                    'buyer_name' => $order_data->buyername,
                    'buyer_phone' => $order_data->buyerphone,
                    'transaction_id' => $order_data->transactionId,
                    'payment_date' => $order_data->paymentDate
                ]);
            }
        } catch (ErrorException $ex)
        {
            //Log::info($ex->getTraceAsString());
            return 0;
        }
    }

    public function channelShippingDetails($order_id)
    {
        try
        {
            DB::table('channel_orders_address')->insert([
                'channel_id' => $order_data->channelid,
                'order_id' => $order_id,
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'middle_name' => $data['middle_name'],
                'suffix' => isset($data['suffix']) ? $data['suffix'] : '',
                'company' => $data['company'],
                'address1' => $order_data->shippingaddress1,
                'address2' => $order_data->shippingaddress2,
                'address_type' => $data['address_type'],
                'city' => $order_data->city,
                'state' => $order_data->state,
                'country' => $order_data->country,
                'pincode' => $order_data->pincode,
                'phone' => $order_data->shippingphone,
                'mobile' => $data['mobile_no'],
                'email' => $order_data->shippingemail,
                'updated_date' => $order_data->updateddate
            ]);
        } catch (ErrorException $ex)
        {
            //Log::info($ex->getTraceAsString());
            return 0;
        }
    }

    public function channelOrderProducts($order_id, $product, $channelId)
    {
        try
        {
            $productData = DB::table('products')
                    ->select('products.product_id', 'products.name', 'eseal_customer.customer_id')
                    ->leftJoin('eseal_customer', 'eseal_customer.customer_id', '=', 'products.manufacturer_id')
                    ->where('products.sku', $product->sku)
                    ->first();

            $productId = DB::table('channel_product')->where([
                        'channel_id' => $channelId,
                        'product_id' => $productData->product_id
                    ])->pluck('product_id');
            //channel products      '
            if (empty($productId))
            {
                DB::table('channel_product')->insert([
                    'channel_id' => $channelId,
                    'product_id' => $productData->product_id
                ]);
            }

            //channel products

            $channel_item_id = DB::table('channel_order_item_details')->where([
                        'channel_id' => $channelId,
                        'order_id' => $order_id,
                        'channel_item_id' => $product->channelitemid,
                        'sco_item_id' => $product->scoitemid,
                        'quantity' => $product->quantity])->pluck('channel_item_id');
            //channel_order_details          
            if (empty($channel_item_id))
            {
                DB::table('channel_order_item_details')->insert([
                    'channel_id' => $channelId,
                    'order_id' => $order_id,
                    'channel_item_id' => $product->channelitemid,
                    'sco_item_id' => $product->scoitemid,
                    'quantity' => $product->quantity,
                    'price' => $product->price,
                    'sell_price' => $product->sellprice,
                    'discount_type' => $product->discounttype,
                    'discount_price' => $product->discountprice,
                    'tax' => $product->tax,
                    'sub_total' => $product->subtotal,
                    'channel_cancel_item' => $product->channelcancelitem,
                    'total' => $product->total
                ]);
            }

            //channel_order_details
            //channel_order_shipping_details
            $channelitemid = DB::table('channel_order_shipping_details')->where([
                        'channel_id' => $channelId,
                        'order_id' => $order_id,
                        'channel_item_id' => $product->channelitemid,
                        'shipping_company_name' => $product->shippingcompanyname])->pluck('channel_item_id');
            //$last = DB::getQueryLog();
            if (empty($channelitemid))
            {
                DB::table('channel_order_shipping_details')->insert([
                    'channel_id' => $channelId,
                    'order_id' => $order_id,
                    'channel_item_id' => $product->channelitemid,
                    'shipping_company_name' => $product->shippingcompanyname,
                    'service_name' => $product->servicename,
                    'service_cost' => $product->servicecost,
                    'dispatch_date' => $product->dispatchdate,
                    'min_time_to_dispatch' => $product->mintimetodispatch,
                    'max_time_to_dispatch' => $product->maxtimetodispatch,
                    'time_units' => $product->timeunits
                ]);
            }

            //channel_order_shipping_details            
        } catch (ErrorException $ex)
        {
            //Log::info($ex->getTraceAsString());
            return 0;
        }
    }

    public function productSearch($data)
    {
        try
        {
            $productSearch = array();
            $productObj = new Products\Products();
            $category = isset($data['category_id']) ? $data['category_id'] : '';
            $productName = isset($data['product_name']) ? $data['product_name'] : '';
            $sku = isset($data['sku']) ? $data['sku'] : '';
            $brand_name = isset($data['brand_name']) ? $data['brand_name'] : '';
            $mrpl = isset($data['mrp-low']) ? $data['mrp-low'] : '';
            $mrph = isset($data['mrp-high']) ? $data['mrp-high'] : '';
            $start_limit = (isset($data['start_limit']) && $data['start_limit'] != '') ? $data['start_limit'] : 0;
            $end_limit = (isset($data['end_limit']) && $data['end_limit'] != '') ? $data['end_limit'] : 50;

            if (!empty($category))
            {
                $productSearchQuery = "Select `products`.`name` as `product_name`, `products`.`title`, `products`.`category_id`, `products`.`sku`, `products`.`mrp`, `eseal_customer`.`brand_name` 
                    from `products` ";
                $productSearchQuery = $productSearchQuery . " join eseal_customer on eseal_customer.customer_id = products.manufacturer_id ";
                $productSearchQuery = $productSearchQuery . " join categories on products.category_id = categories.category_id ";
                $productSearchWhere = " Where ";
                $where = 0;

                if ($category != '')
                {
                    $where = 1;
                    $productSearchWhere = $productSearchWhere . ' (categories.category_id LIKE "%' . $category . '%") ';
                }
                if ($productName != '')
                {
                    if ($where)
                    {
                        $productSearchWhere = $productSearchWhere . ' AND (products.name LIKE "%' . $productName . '%") ';
                    } else
                    {
                        $productSearchWhere = $productSearchWhere . ' (products.name LIKE "%' . $productName . '%") ';
                    }
                    $where = 1;
                }
                if ($sku != '')
                {
                    if ($where)
                    {
                        $productSearchWhere = $productSearchWhere . ' AND (products.sku LIKE "%' . $sku . '%") ';
                    } else
                    {
                        $productSearchWhere = $productSearchWhere . ' (products.sku LIKE "%' . $sku . '%") ';
                    }
                    $where = 1;
                }

                if ($brand_name != '')
                {
                    if ($where)
                    {
                        $productSearchWhere = $productSearchWhere . ' AND (eseal_customer.brand_name LIKE "%' . $brand_name . '%") ';
                    } else
                    {
                        $productSearchWhere = $productSearchWhere . ' (eseal_customer.brand_name LIKE "%' . $brand_name . '%") ';
                    }
                    $where = 1;
                }
                $limit = ' limit ' . $end_limit . ' offset ' . $start_limit;
                $completeQuery = $productSearchQuery . $productSearchWhere;
                $productSearch = DB::select(DB::raw($completeQuery));
            } else
            {
                $productSearch = DB::Table('products')
                        ->Join('categories', 'products.category_id', '=', 'categories.category_id')
                        ->Join('eseal_customer', 'eseal_customer.customer_id', '=', 'products.manufacturer_id')
                        ->select('products.name as product_name', 'products.title', 'products.category_id', 'products.sku', 'products.mrp', 'eseal_customer.brand_name')
                        //->where('products.category_id','like','%'.$category.'%')
                        ->where('products.name', 'like', '%' . $productName . '%')
                        //->whereRaw('MATCH(products.name) AGAINST("'.$productName.'" )')
                        ->orWhere('products.sku', 'like', '%' . $productName . '%')
                        ->orWhere('products.title', 'like', '%' . $productName . '%')
                        ->orWhere('eseal_customer.brand_name', 'like', '%' . $productName . '%')
                        ->skip($start_limit)->take($end_limit)
                        ->get();
                //$last = DB::getQueryLog();
            }
            //return json_encode($productSearch);
            $status = 1;
            $message = 'Sucessfull';
        } catch (ErrorException $e)
        {
            //Log::info($e->getTraceAsString());
            $status = 0;
            $message = $e->getMessage();
            //return $message;
        }
        //return json_encode(Array('Status' => $status, 'Message' => $message, 'search_info' => $productSearch));
        return Response::json(Array('Status' => $status, 'Message' => $message, 'search_info' => $productSearch));
    }

    public function getGdsCustomer($data)
    {
        try
        {
            $status = 0;
            $message = '';
            $channelinfo = DB::table('channel_customer')
                    ->where('channel_cust_id', $data)
                    ->select('channel_customer.first_name as firstname', 'channel_customer.last_name as lastname', 'channel_customer.channel_user_id as channel_user_id', 'channel_customer.email_address as email_address', 'channel_customer.mobile_no as mobile_no', 'channel_customer.dob as dob', 'channel_customer.channel_id as channel_id', 'channel_customer.gender as gender', 'channel_customer.registered_date as registered_date')
                    ->first();

            $chanladdressinfo = DB::Table('channel_orders_address')
                    ->where('channel_cust_id', $data)
                    ->select('channel_orders_address.city as city', 'channel_orders_address.state as state', 'channel_orders_address.country as country', 'channel_orders_address.address1 as street1', 'channel_orders_address.address2 as street2', 'channel_orders_address.pincode as zipcode', 'channel_orders_address.mobile as mobile_no', 'channel_orders_address.address_type as address_type')
                    ->first();

            $cust = DB::table('gds_customer')
                    ->where('gds_customer.channel_user_id', '=', $channelinfo->channel_user_id)
                    ->where('gds_customer.channel_id', '=', $channelinfo->channel_id)
                    ->pluck('gds_customer.channel_user_id');

            if (empty($cust))
            {
                try
                {
                    $lastinsertid = DB::table('gds_customer')->insertGetId([
                        'firstname' => $channelinfo->firstname,
                        'lastname' => $channelinfo->lastname,
                        //'erp_code'=> $valus['erp_code'],
                        'channel_user_id' => $channelinfo->channel_user_id,
                        'email_address' => $channelinfo->email_address,
                        'mobile_no' => $channelinfo->mobile_no,
                        'dob' => $channelinfo->dob,
                        'channel_id' => $channelinfo->channel_id,
                        'gender' => $channelinfo->gender,
                        'registered_date' => $channelinfo->registered_date
                    ]);

                    if (!empty($lastinsertid))
                    {

                        DB::table('channel_customer')->where('channel_cust_id', $data)
                                ->update(array('gds_cust_id' => $lastinsertid));
                    }   //return $lastinsertid;
                } catch (ErrorException $e)
                {
                    //Log::info($e->getMessage());
                    $message = $e->getMessage();
                    //throw new Exception($message);
                }
                $cust_add_id = DB::table('gds_cust_address')
                        ->where('gds_cust_address.gds_cust_id', '=', $lastinsertid)
                        ->pluck('gds_cust_address.gds_cust_id');
                if (empty($cust_add_id))
                {
                    try
                    {
                        DB::table('gds_cust_address')->insert([
                            'address_type' => $chanladdressinfo->address_type,
                            'gds_cust_id' => $lastinsertid,
                            'street1' => $chanladdressinfo->street1,
                            'street2' => $chanladdressinfo->street2,
                            'city' => $chanladdressinfo->city,
                            'country' => $chanladdressinfo->country,
                            'zipcode' => $chanladdressinfo->zipcode,
                            'state' => $chanladdressinfo->state,
                            'mobile_no' => $chanladdressinfo->mobile_no
                        ]);
                    } catch (ErrorException $e)
                    {
                        //Log::info($e->getMessage());
                        $message = $e->getMessage();
                        //throw new Exception($message);
                    }
                }
                $status = 1;
                $message = 'Successfully inserted.';
            } else
            {
                try
                {
                    DB::table('gds_customer')
                            ->where('channel_user_id', $channelinfo->channel_user_id)
                            ->where('channel_id', $channelinfo->channel_id)
                            ->update(array(
                                'firstname' => $channelinfo->firstname,
                                'lastname' => $channelinfo->lastname,
                                //'erp_code'=> $valus['erp_code'],
                                'channel_user_id' => $channelinfo->channel_user_id,
                                'email_address' => $channelinfo->email_address,
                                'mobile_no' => $channelinfo->mobile_no,
                                'dob' => $channelinfo->dob,
                                'channel_id' => $channelinfo->channel_id,
                                'gender' => $channelinfo->gender,
                                'registered_date' => $channelinfo->registered_date
                    ));
                } catch (ErrorException $e)
                {
                    //Log::info($e->getMessage());
                    $message = $e->getMessage();
                    //throw new Exception($message);
                }
                $custid = DB::table('gds_customer')
                        ->where('channel_user_id', $channelinfo->channel_user_id)
                        ->where('channel_id', $channelinfo->channel_id)
                        ->pluck('gds_cust_id');
                try
                {
                    DB::table('gds_cust_address')
                            ->where('gds_cust_id', $custid)
                            ->update(array(
                                'address_type' => $chanladdressinfo->address_type,
                                'street1' => $chanladdressinfo->street1,
                                'street2' => $chanladdressinfo->street2,
                                'city' => $chanladdressinfo->city,
                                'country' => $chanladdressinfo->country,
                                'zipcode' => $chanladdressinfo->zipcode,
                                'state' => $chanladdressinfo->state,
                                'mobile_no' => $chanladdressinfo->mobile_no
                    ));
                } catch (ErrorException $e)
                {
                    //Log::info($e->getMessage());
                    $message = $e->getMessage();
                    //throw new Exception($message);
                }
                $status = 1;
                $message = 'Successfully updated.';
            }
            if (!empty($lastinsertid))
            {
                return json_encode(Array('Status' => $status, 'Message' => $message, 'gds_cust_id' => $lastinsertid));
            } else
            {
                return json_encode(Array('Status' => $status, 'Message' => $message, 'gds_cust_id' => $custid));
            }
        } catch (Exception $e)
        {
            $message = $e->getMessage();
        }
    }

    public function gdsOrder($data)
    {
        try
        {
            //die('we are in '.__METHOD__);
            //$data = json_decode($data);
            $status = 0;
            $message = '';
            $orderId = $data['order_id'];
            // $gds_customer_id=45;
            $channelOrderData = DB::table('channel_orders')
                    ->where('order_id', $orderId)
                    ->select(['channel_id', 'channel_cust_id', 'channel_order_id', 'order_date', 'shipping_cost as ship_total',
                        'sub_total', 'tax as tax_total', 'total_amount as total',
                        'channel_order_status as order_status_id'])
                    ->first();
            //echo "<pre>";print_r($channelOrderData);die;
            $channel_cust_id = $channelOrderData->channel_cust_id;
            //return $channel_cust_id;
            $gds_customer_id = $this->getGdsCustomer($channel_cust_id);
            //echo "<pre>";print_r($gds_customer_id);die;
            $var = json_decode($gds_customer_id);

            if (empty($channelOrderData))
            {
                return ['status' => 0, 'message' => 'No Order id from channel orders table.'];
            }
            $channelOrderId = $channelOrderData->channel_order_id;

            if (empty($channelOrderId))
            {
                return ['status' => 0, 'message' => 'No channel order id data.'];
            }

            $gdsCust = DB::table('gds_customer')
                    ->where('gds_cust_id', $var->gds_cust_id)
                    ->select(['gds_cust_id', 'firstname', 'lastname', 'email_address as email', 'mobile_no as phone'])
                    ->first();

//            echo "<Pre>";
//            print_r(json_decode(json_encode($channelOrderData), true));
//            print_r(json_decode(json_encode($gdsCust), true));
//            die;

            $oneDimensionalArray = array_merge((json_decode(json_encode($channelOrderData), true)), (json_decode(json_encode($gdsCust), true)));
            //echo "<pre> aarray=>",print_r($array);die;
            //$oneDimensionalArray = call_user_func_array('array_merge', $array);
            //echo "<pre>",print_r($oneDimensionalArray);
            //die;

            unset($oneDimensionalArray['channel_cust_id']);

            $gds_orders = new GDSOrders;
            $order_id = $gds_orders->insertGetId($oneDimensionalArray);



            $update_order_id = $gds_orders->where('gds_order_id', $orderId)->update($oneDimensionalArray);


            if (!empty($order_id) && isset($order_id))
            {
                //payments starts  
                $channelOrderPaymentData = DB::table('channel_order_payment')
                                ->join('currency', 'currency.code', '=', 'channel_order_payment.payment_currency')
                                ->where('order_id', $orderId)
                                ->select(['channel_order_payment.transaction_id', 'channel_order_payment.payment_date', 'channel_order_payment.updated_date', 'channel_order_payment.created_date', 'channel_order_payment.payment_method_id', 'channel_order_payment.amount', 'currency.currency_id', 'channel_order_payment.payment_status_id'])->get();


                $paymentArray = json_decode(json_encode($channelOrderPaymentData), true);

                foreach ($paymentArray as $paymentData)
                {
                    $paymentData['gds_order_id'] = $order_id;

                    DB::table('gds_orders_payment')->insert([$paymentData]);
                }
                //Log::info($channelOrderPaymentData);
                //payments ends
                //shipping Starts

                $channelOrderAddr = DB::table('channel_orders_address')
                                ->join('countries', 'countries.iso_code_2', '=', 'channel_orders_address.country')
                                ->join('zone', 'zone.name', '=', 'channel_orders_address.state')
                                ->where('order_id', $orderId)
                                ->select(['channel_orders_address.first_name as fname', 'channel_orders_address.middle_name as mname', 'channel_orders_address.last_name as lname', 'channel_orders_address.company', 'channel_orders_address.suffix', 'channel_orders_address.address_type', 'channel_orders_address.address1 as addr1', 'channel_orders_address.address2 as addr2', 'channel_orders_address.city', 'channel_orders_address.pincode as postcode', 'channel_orders_address.phone as telephone', 'channel_orders_address.mobile', 'channel_orders_address.updated_date', 'channel_orders_address.created_date', 'countries.country_id', 'zone.zone_id as state_id'])->get();



                $addrArray = json_decode(json_encode($channelOrderAddr), true);

                foreach ($addrArray as $address)
                {
                    $address['gds_order_id'] = $order_id;
                    DB::table('gds_orders_addresses')->insert([$address]);
                }

                //shipping ends
                //gds_order_products starts

                $channelOrderDetails = DB::table('channel_order_item_details')
                                ->where('order_id', $orderId)
                                ->select(['channel_item_id', 'quantity as qty', 'price', 'discount_price as discount', 'tax', 'total'])->get();



                $orderDetailsArray = json_decode(json_encode($channelOrderDetails), true);



                foreach ($orderDetailsArray as $orderDetails)
                {
                    $channelItemId = $orderDetails['channel_item_id'];
                    $productId = DB::table('Channel_product_add_update')
                            ->where('channel_product_key', $channelItemId)
                            ->pluck('product_id');
                    $productName = DB::table('products')
                            ->where('product_id', $productId)
                            ->pluck('name');

                    unset($orderDetails['channel_item_id']);

                    //gds_order_products      
                    $orderDetails['gds_order_id'] = $order_id;
                    $orderDetails['pid'] = $productId;
                    $orderDetails['pname'] = $productName;
                    DB::table('gds_order_products')->insert([$orderDetails]);
                    //gds_order_products 
                }
                //gds_order_ship_details
                // $channelOrderShippedDetails = DB::table('channel_orders_shipped_dtl')->where('channel_order_id', $orderId)->select(['ship_method','created_date','updated_date','fname', 'mname','lname','addr1','addr2','city','postcode','country_id','state_id','telephone',       'mobile','ship_service_id','tracking_id'] )->get();
                // $shipDetailsArray = json_decode(json_encode($channelOrderShippedDetails),true);
                //  foreach($shipDetailsArray as $shipDetails)
                //  {
                //   $shipDetails['gds_order_id'] = $order_id;
                //   echo "<pre",print_r($shipDetails);die;
                //    DB::table('gds_orders_ship_details')->insert([$shipDetails]); 
                //   }
                //gds_order_ship_details  
                //gds_order_shipping_details
                $shipCmpnyName = DB::table('channel_order_shipping_details')
                        ->where('order_id', $orderId)
                        ->select(['channel_order_shipping_details.shipping_company_name'])
                        ->get();
                //print_r($shipCmpnyName);die;
//                Log::info('orderId => ');
//                Log::info($orderId);
//                Log::info('shipCmpnyName => ');
//                Log::info($shipCmpnyName);
                $ShippingName = $shipCmpnyName[0]->shipping_company_name;


                $channelShippingDetails = DB::table('channel_order_shipping_details')
                        ->join('shipping_services', 'shipping_services.service_name', '=', 'channel_order_shipping_details.service_name')
                        ->join('carriers', 'shipping_services.carrier_id', '=', 'carriers.carrier_id')
                        ->where('order_id', $orderId)
                        //->where('carriers.name', $ShippingName)
                        ->select(['channel_item_id', 'shipping_services.service_id', 'service_cost', 'dispatch_date', 'min_time_to_dispatch', 'max_time_to_dispatch', 'time_units'])
                        ->get();

                // $last=DB::getQueryLog();
                // echo "<pre>";print_r(end($last));die;
                // print_r($channelShippingDetails);die;  

                $DetailsArray = json_decode(json_encode($channelShippingDetails), true);
                // print_r($DetailsArray);die;
                foreach ($DetailsArray as $shipping)
                {
                    $shipping['gds_order_id'] = $order_id;
                    DB::table('gds_order_shipping_details')->insert([$shipping]);
                    
                }

                //gds_order_shipping_details
                //gds_order_ship_items
                //  $channelShipItems = DB::table('channel_ship_items')->where('channel_order_id', $orderId)->select(['created_date','updated_date','pid','qty'] )->get();
                //  $shipItemsArray = json_decode(json_encode($channelShipItems),true);
                //   foreach($shipItemsArray as $shipItem)
                // {
                //  $shipItem['gds_order_id'] = $order_id;
                //   DB::table('gds_ship_items')->insert([$shipItem]); 
                //  }
                //gds_order_ship_items
            }            

            $status = 1;
            $message = 'Successfully placed order.';
        } catch (ErrorException $ex)
        {
            $message = $ex->getMessage() . $ex->getTraceAsString();
//            echo $message;
//            die;
            //Log::info();
            //Log::info($ex->getMessage());
            //Log::info($ex->getTraceAsString());
            return 0;
        }
        return Response::json(Array('Status' => $status, 'Message' => $message));
    }

    public function finance($data){
      try {
            $status = 0;
            $message = '';

              $this->subscriptionCharges($data);
              $this->channelOrderCharges($data);
              $this->getCategoryCharges($data);
              $status = 1;
              $message = 'Success';

      } catch (Exception $e)
        {
            $message = $e->getMessage();
        }
        return Response::json(Array('Status' => $status, 'Message' => $message));
      

    }


    public function subscriptionCharges($data)
{
  try
       {  
              $status = 0;
              $message = '';
              $recurring_period=DB::table('channel_charges')
                 ->join('channel_service_type', 'channel_service_type.service_type_id', '=', 'channel_charges.service_type_id')
                 ->where('channel_service_type.service_type_id', '=','1')
                 ->select(['recurring_period'])
                ->get();

                if($recurring_period[0]->recurring_period =='M')
                {
               
                  $manf_channels=DB::table('channel_charges')
                 ->join('manf_channels', 'manf_channels.channel_id', '=', 'channel_charges.channel_id')
                 ->where('channel_charges.service_type_id', '=',1)
                 ->where('manf_channels.status', '=',1)              
                 ->select(['channel_charges.charges','channel_charges.channel_charges_id','manf_channels.manf_channel_id as reference_id','channel_charges.eseal_fee','channel_charges.channel_id','channel_charges.service_type_id','channel_charges.currency_id'])
                 ->get();
                
             
                  $charges_entities=DB::table('charges_entities')
                 ->where('entity_table_name', '=','channel_charges')              
                 ->select(['charges_entity_id as entity_table_name_id'])
                 ->get();
               
        $array = array_merge((json_decode(json_encode($manf_channels),true)), (json_decode(json_encode($charges_entities),true)));
       
          $oneDimensionalArray = call_user_func_array('array_merge', $array);


           $this->manfCharges($oneDimensionalArray);
    
                 
                }

        $status = 1;
        $message = 'Success.';
        
        }
   catch(ErrorException $ex)
      {
        $message = $ex->getMessage().$ex->getTraceAsString();
        echo $message;die;
            //Log::info();
            //Log::info($ex->getMessage());
            //Log::info($ex->getTraceAsString());
            return 0;
      }
      return Response::json(Array('Status'=>$status, 'Message' => $message));    
 }

    public function getCategoryCharges($data)
   {
         try
      { 
        $status = 0;
        $message = '';
        $product=$data['product_key'];       //110167581347
        
        $product_id=DB::table('Channel_product_add_update')
                    ->select(['Channel_product_add_update.product_id','Channel_product_add_update.channel_product_key as reference_id'])
                    ->where('Channel_product_add_update.channel_product_key','=',$product)
                    ->get();
        //print_r($product_id);die;
        $prodctg=DB::table('products')
                 ->select('products.category_id')
                 ->where('products.product_id','=',$product_id[0]->product_id)
                 ->get();

        //print_r($prodctg);die;      
        $category = DB::table('products')
                    ->join('categories','products.category_id','=','categories.category_id')
                    ->whereIn('categories.category_id',[$prodctg[0]->category_id])
                    ->where('products.product_id','=',$product_id[0]->product_id)
                    ->where('categories.parent_id','=',0)
                    ->select('categories.category_id')
                    ->get();
       //print_r($category);die;    
       $ctgcharges=DB::table('category_charges')
                   ->select(['charges'])
                   ->where('category_id','=',$category[0]->category_id)
                   ->get();
       //print_r($ctgcharges[0]->charges);die;   
      $charges_entities=DB::table('charges_entities')
                 ->where('entity_table_name', '=','Channel_product_add_update')              
                 ->select(['charges_entity_id as entity_table_name_id'])
                 ->get();  
       //return $charges_entities;
       $manf_data=db::table('channel_charges')
             ->join('channel_service_type','channel_service_type.service_type_id','=','channel_charges.service_type_id')
             ->where('channel_service_type.service_type_name','=','PRODUCT_CATEGORY_LIST_FEE')
             ->select(['channel_charges.channel_charges_id','channel_charges.service_type_id','channel_charges.eseal_fee','channel_charges.channel_id','channel_charges.currency_id'])
             ->get();

          
           $array1 = array_merge((json_decode(json_encode($ctgcharges),true)), (json_decode(json_encode($manf_data),true)));
            //echo "<pre> aarray=>",print_r($array1);
            
            $array2 = array_merge((json_decode(json_encode($charges_entities),true)), (json_decode(json_encode($product_id),true)));    
          //echo "<pre> aarray=>",print_r($array2);

           $array = array_merge((json_decode(json_encode($array1),true)), (json_decode(json_encode($array2),true)));
          //echo "<pre> aarray=>",print_r($array);die;

          $oneDimensionalArray = call_user_func_array('array_merge', $array);
          //echo "<pre> aarray=>",print_r($oneDimensionalArray);
       
       $this->manfCharges($oneDimensionalArray);
        $status = 1;
        $message = 'Successfully inserted  into manf_charges.';
       
  }
      catch(ErrorException $ex)
      {
        $message = $ex->getMessage().$ex->getTraceAsString();
        echo $message;die;
            //Log::info();
            //Log::info($ex->getMessage());
            //Log::info($ex->getTraceAsString());
            return 0;
      }
     return Response::json(Array('Status'=>$status, 'Message' => $message));  
}

    public function channelOrderCharges($data)
    {
      try {
        
        $status = 0;
        $message = '';

        $fromDate = $data['fromDate'];
        $toDate = $data['toDate'];

        
        $charge = DB::table('channel_charges')
                                    ->leftJoin('channel_service_type as service','service.service_type_id','=','channel_charges.service_type_id')
                                    ->leftJoin('currency','currency.currency_id','=','channel_charges.currency_id')
                                    ->leftJoin('channel_orders as orders','orders.channel_id','=','channel_charges.channel_id')
                                    ->where('service.service_type_name','=','PAYMENT_GATEWAY_FEE')
                                    
                                    ->whereBetween('orders.created_date', [$fromDate,$toDate])
                                    ->select('channel_charges.channel_charges_id','channel_charges.eseal_fee','channel_charges.charges as chrg','channel_charges.currency_id','channel_charges.service_type_id','orders.channel_order_id','orders.order_id as reference_id','orders.total_amount as charges','orders.channel_id','orders.created_date')
                                    ->get();
                                    /*echo "<pre>"; echo "Hello"; print_r($charge); die();*/
       if (!empty($charge)) {
                
        $charges_entities=DB::table('charges_entities')
                 ->where('entity_table_name', '=','channel_orders')              
                 ->select(['charges_entity_id as entity_table_name_id'])
                 ->get();
        $charge_id = isset($charges_entities[0]->entity_table_name_id) ? $charges_entities[0]->entity_table_name_id : '';
        
        
          $amount =array(); 

             foreach ($charge as $key => $value) {
            $amount = $value->charges * $value->chrg;
            $value->charges = $amount / 100;
            $value->entity_table_name_id = $charge_id;

          }
          $order_charge = json_decode(json_encode($charge), true);
          
          foreach ($order_charge as $key => $value) {
            /*echo "<pre>"; print_r($value);*/
            
            $manfId = DB::table('manf_charges')->where([
                        'reference_id' => $value['reference_id'],
                        'channel_id' => $value['channel_id']
                    ])->pluck('manf_charges_id');
            /*$last = DB::getQueryLog();
            print_R(end($last));*/
            //if (!empty($manfId)) {
            if($manfId != ''){

              $message = 'ManfCharge already exists for referenceId: ' .$value['reference_id'];
               
             }
             else{
              $this->manfCharges($value);
              $status = 1;
              $message = 'Successfully inserted  into manf_charges.';
             }
          }
          
        }
        else{
        $message = 'No Data Found in ChannelOrderCharges';
      } 
      }
      

      catch(ErrorException $ex)
      {
        $message = $ex->getMessage().$ex->getTraceAsString();
        
       return 0;
      }
      return Response::json(Array('Status'=>$status, 'Message' => $message));  

     }

    public function manfCharges($data)
  {

  try
       {       $status = 0;
               $message = '';
               $manf_charges=DB::table('manf_charges')->insert([
                            'charges'=> $data['charges'],
                            'channel_charges_id'=> $data['channel_charges_id'],
                            'reference_id'=> $data['reference_id'],
                            'eseal_fee'=> $data['eseal_fee'],
                            'channel_id'=> $data['channel_id'],
                            'service_type_id'=> $data['service_type_id'],
                            'entity_table_name_id'=> $data['entity_table_name_id'],
                            'currency_id'=> $data['currency_id']
                          ]);
        
        }
   catch(ErrorException $ex)
      {
        $message = $ex->getMessage().$ex->getTraceAsString();
        echo $message;die;
            //Log::info();
            //Log::info($ex->getMessage());
            //Log::info($ex->getTraceAsString());
            return 0;
      }
      return Response::json(Array('Status'=>$status, 'Message' => $message));    

   }
   
   public function returnData($status, $message, $data)
   {
       return Response::json(Array('Status'=>$status, 'Message' => $message, 'Data' => $data));
   }
   
   public function getMaterialCode($sku)
   {
       try
       {
           $status = 0;
           $data = 0;
           $message = '';
           $productData = DB::table('products')->where(['sku' => $sku])->pluck('material_code');
           if($productData != '')
           {
               $data = $productData;
               $status = 1;
               $message = 'Success';
           }else{
               $message = 'No product.';
           }
       } catch (ErrorException $ex) {
           $message = $ex->getMessage();
       }
       return json_encode(['status' => $status, 'message' => $message, 'data' => $data]);
   }
}