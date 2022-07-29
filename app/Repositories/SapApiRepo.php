<?php

namespace App\Repositories;
use DB;
use Location;
use Location1;
use Response;
class SapApiRepo
{
	protected $_username;
	protected $_password;
	protected $_url;
	protected $_domain;
	protected $_method;
	protected $_table_name;
	protected $_sap_api_repo;
	protected $_return_type;
	protected $_token;
	protected $_manufacturer_id;
	
	public function __construct()
	{
		$this->_username = 'eseal1';
		$this->_password = 'eseal@123'; 
		$this->_domain = '14.141.81.243:8000';
		$this->_url = 'http://' . $this->_domain . '/sap/opu/odata/sap/';
		//$this->_sap_api_repo = new SapApiRepo();
		$this->_return_type = 'xml';
		$this->_token = '3h8M8A2q8iv7nMq4Rpft5G5TBE4O7PC8';       
	}

	public function callSapApi($method,$method_name,$data,$data1 = null,$manufacturer_id,$xml=null){
                $this->_manufacturer_id = $manufacturer_id;
		$cred =DB::table('erp_integration')->where('manufacturer_id', $manufacturer_id)->first(['web_service_url','web_service_username','web_service_password','token','sap_client']);
		if(empty($cred)){
			return 'There is no erp-configuration for this brand owner';
		}
		else{
			$this->_method = $method;
			$this->_method_name = $method_name;
			$sap_client = $cred->sap_client;
			$url = $cred->web_service_url;
			$url = $url . $this->_method . '/' . $this->_method_name . '/';
			$method = 'GET';
			$data['TOKEN'] = $cred->token;
			$username = $cred->web_service_username;
			$password = $cred->web_service_password;
            if($xml){
            \Log::info('First call');
             $response = $this->request($username,$password,$url, $method,null,'xml',1,null,$xml,$sap_client);
             $status = $this->$method_name($response);
             return $status;
            }
            else{
            \Log::info('Second call');
			$response = $this->request($username,$password,$url, $method, $data, $this->_return_type,null,null,null,$sap_client);      
			$status = $this->$method_name($response,$data1);
			return $status;
		}
		}
	}

	// Set up a request function
	
	public function PLANT_DATA($response,$data1)
	{
		try{ 
			//Log::info($response);      
			$status =0;
			$message = 'Data successfully retrieved and inserted';
			$storage_type = DB::table('location_types')->where(['manufacturer_id'=>$data1['manufacturer_id'],'location_type_name'=>'Storage Location'])->pluck('location_type_id');
			$parseData1 = xml_parser_create();
			xml_parse_into_struct($parseData1,$response,$documentValues1,$documentIndex1);
			xml_parser_free($parseData1);
			$documentData = array();
			foreach ($documentValues1 as $data) {
				if(isset($data['tag']) && $data['tag'] == 'D:GET_PLANT_DATA')
				{
					$documentData = $data['value'];
				}
			}
			$deXml = simplexml_load_string($documentData);
			$deJson = json_encode($deXml);
			$xml_array = json_decode($deJson,TRUE); 
            
			$status = $xml_array['HEADER']['STATUS'];
			if($status == 1){
				foreach($xml_array['DATA'] as $data){
					foreach($data as $key => $value){
						if(is_array($value) && empty($value)){
							$data[$key] = '';
						}
					}
					$erp_code = $data['PLANT'];
					$plant_name = $data['NAME'];
					$valuation_area = $data['VALUATION_AREA'];
					$company_code = $data['COMPANY_CODE'];
					$address_code = $data['ADDRESS'];
					$city = $data['CITY'];
					$postal_code = $data['POSTAL_CODE'];
					$street = $data['STREET'];
					$street2 = $data['STREET2'];
					$street3= $data['STREET3'];
					$country_key =$data['COUNTRY_KEY'];
					$country_name = $data['COUNTRY_NAME'];
					$region = $data['REGION'];
					$state = $data['DESCRIPTION'];

					$isExists = Location::where(['location_name'=>$plant_name,'manufacturer_id'=>$data1['manufacturer_id'],'location_type_id'=>$data1['location_type_id']])->pluck('location_id');

					if(!empty($isExists)){
						if(!array_key_exists('STORAGE_CODE', $data['ITEM'])){  

							foreach($data['ITEM'] as $item){
								$storage_code = $item['STORAGE_CODE'];
								$storage_name = $item['STORAGE_NAME'];

								$isThere  = Location::where(['location_name'=>$storage_name,'erp_code'=>$storage_code,'parent_location_id'=>$isExists])->pluck('location_id');
								if(!empty($isThere)){
									continue;
								}
								else{
									$location = new Location;
									$location->location_name = $storage_name;
									$location->location_type_id = $storage_type;
									$location->manufacturer_id = $data1['manufacturer_id'];
									$location->firstname = '';
									$location->lastname = '';
									$location->location_email = 'noemail@xxx.com';
									$location->location_address = $street.''.$street2.''.$street3;
									$location->location_details = $address_code;
									$location->city = $city;
									$location->region = $region;
									$location->country = $country_name;
									$location->pincode = $postal_code;
									$location->phone_no = '';
									$location->parent_location_id = $isExists;
									$location->erp_code = $storage_code;
									$location->save(); 
								} 
							}
						}
						else{                    
							$storage_code = $data['ITEM']['STORAGE_CODE'];
							$storage_name = $data['ITEM']['STORAGE_NAME'];

							$isThere  = Location::where(['location_name'=>$storage_name,'erp_code'=>$storage_code,'parent_location_id'=>$isExists])->pluck('location_id');
							if(!empty($isThere)){
								continue;
							}
							else{
								$location = new Location;
								$location->location_name = $storage_name;
								$location->location_type_id = $storage_type;
								$location->manufacturer_id = $data1['manufacturer_id'];
								$location->firstname = '';
								$location->lastname = '';
								$location->location_email = 'noemail@xxx.com';
								$location->location_address = $street.''.$street2.''.$street3;
								$location->location_details = $address_code;
								$location->city = $city;
								$location->region = $region;
								$location->country = $country_name;
								$location->pincode = $postal_code;
								$location->phone_no = '';
								$location->parent_location_id = $isExists;
								$location->erp_code = $storage_code;
								$location->save(); 
							} 
						}
						$status =1;
						goto xyz;
					}
					else{
						$location = new Location;
						$location->location_name = $plant_name;
						$location->location_type_id = $data1['location_type_id'];
						$location->manufacturer_id = $data1['manufacturer_id'];
						$location->firstname = '';
						$location->lastname = '';
						$location->location_email = 'noemail@xxx.com';
						$location->location_address = $street.''.$street2.''.$street3;
						$location->location_details = $address_code;
						$location->city = $city;
						$location->region = $region;
						$location->country = $country_name;
						$location->pincode = $postal_code;
						$location->phone_no = '';
						$location->erp_code = $erp_code;
						$location->save();  

						$parent_location_id = DB::getPdo()->lastInsertId();

						if(!array_key_exists('STORAGE_CODE', $data['ITEM'])){    
							foreach($data['ITEM'] as $item){

								$storage_code = $item['STORAGE_CODE'];
								$storage_name = $item['STORAGE_NAME'];

								$location = new Location;
								$location->location_name = $storage_name;
								$location->location_type_id = $storage_type;
								$location->manufacturer_id = $data1['manufacturer_id'];
								$location->firstname = '';
								$location->lastname = '';
								$location->location_email = 'noemail@xxx.com';
								$location->location_address = $street.''.$street2.''.$street3;
								$location->location_details = $address_code;
								$location->city = $city;
								$location->region = $region;
								$location->country = $country_name;
								$location->pincode = $postal_code;
								$location->phone_no = '';
								$location->parent_location_id = $parent_location_id;
								$location->erp_code = $storage_code;
								$location->save();  

							}
						}
						else{

							$storage_code = $data['ITEM']['STORAGE_CODE'];
							$storage_name = $data['ITEM']['STORAGE_NAME'];

							$location = new Location;
							$location->location_name = $storage_name;
							$location->location_type_id = $storage_type;
							$location->manufacturer_id = $data1['manufacturer_id'];
							$location->firstname = '';
							$location->lastname = '';
							$location->location_email = 'noemail@xxx.com';
							$location->location_address = $street.''.$street2.''.$street3;
							$location->location_details = $address_code;
							$location->city = $city;
							$location->region = $region;
							$location->country = $country_name;
							$location->pincode = $postal_code;
							$location->phone_no = '';
							$location->parent_location_id = $parent_location_id;
							$location->erp_code = $storage_code;
							$location->save();  

						}
					}
					$status =1;
					xyz:
				}
			}  
			else{
				throw new Exception('Data not found');
			}
		}
		catch(Exception $e){
			$message = $e->getMessage();
		}
		return Response::json(['Status' => $status,'Message' => $message]);  
	}

	public function GET_PORDER_DETAILS($response,$data1)
	{
		 $parseData1 = xml_parser_create();
		xml_parse_into_struct($parseData1, $response, $documentValues1, $documentIndex1);
		xml_parser_free($parseData1);
		$documentData = array();
		foreach ($documentValues1 as $data) {
			if(isset($data['tag']) && $data['tag'] == 'D:PORDER_DATA')
			{
				$documentData = $data['value'];
			}
		}

	   $deXml = simplexml_load_string($documentData);
		$deJson = json_encode($deXml);
		$xml_array = json_decode($deJson,TRUE);      
		$status = $xml_array['HEADER']['STATUS'];

		if($status == 1){

			$order_no = $xml_array['DATA']['PRODUCTION_ORDER_NO'];
			$order_date = $xml_array['DATA']['PRODUCTION_ORDER_DATE'];
			
			$plant_code = $xml_array['DATA']['PLANT_CODE'];
			$plant_name = $xml_array['DATA']['PLANT_NAME'];


			
			$material_code = $xml_array['DATA']['FINISHED_MATERIAL']['ITEM']['MATERIAL_CODE'];
			$material_description = $xml_array['DATA']['FINISHED_MATERIAL']['ITEM']['MAT_DESC'];
			$batch_no = $xml_array['DATA']['FINISHED_MATERIAL']['ITEM']['BATCH_NO'];
			$qty = $xml_array['DATA']['FINISHED_MATERIAL']['ITEM']['QUANTITY'];
			$exp_date = $xml_array['DATA']['FINISHED_MATERIAL']['ITEM']['EXP_DATE'];
			$mfg_date = $xml_array['DATA']['FINISHED_MATERIAL']['ITEM']['MFG_DATE'];                                                  
			$storage_loc_code = $xml_array['DATA']['FINISHED_MATERIAL']['ITEM']['STORAGE_LOC_CODE'];
			$storage_loc_name = $xml_array['DATA']['FINISHED_MATERIAL']['ITEM']['STORAGE_LOC_NAME'];


			foreach($xml_array['DATA']['BOM']['ITEM2'] as $item){
				return $item;
			}

			return Response::json(['Status' => 1,'Message' => 'Data successfully retrieved']); 

		}
		else{
			return Response::json(['Status'=>0,'Message'=>'Data not retrieved']);
		}

	}
	
	
	public function GRN_OUTPUT($response,$data1)
	{
		
		$parseData1 = xml_parser_create();
		xml_parse_into_struct($parseData1, $response, $documentValues1, $documentIndex1);
		xml_parser_free($parseData1);
		$documentData = array();
		foreach ($documentValues1 as $data) {
			if(isset($data['tag']) && $data['tag'] == 'D:GET_GRN')
			{
				$documentData = $data['value'];
			}
		}
		
		$deXml = simplexml_load_string($documentData);
		$deJson = json_encode($deXml);
		$xml_array = json_decode($deJson,TRUE);      

	   $status = $xml_array['HEADER']['Status'];
		if($status == 1){

			$doc_no = $xml_array['DATA']['DOC_NO'];
			$doc_year = $xml_array['DATA']['DOC_YEAR'];
			$vendor_no = $xml_array['DATA']['VENDOR_NO'];
			$plant_code = $xml_array['DATA']['PLANT_CODE'];
			$plant_name = $xml_array['DATA']['PLANT_NAME'];

			foreach($xml_array['DATA']['ITEMS']['ITEM'] as $item){
				return $item;
			}

			return Response::json(['Status' => 1,'Message' => 'Data successfully retrieved']); 

		}
		else{
			return Response::json(['Status'=>0,'Message'=>'Data not retrieved']);
		}
	}
 
        
	public function GET_VENDOR_DETAILS($response,$data1)
	{
		 try{
		$status =0;
		$parseData1 = xml_parser_create();
		xml_parse_into_struct($parseData1, $response, $documentValues1, $documentIndex1);
		xml_parser_free($parseData1);
		$documentData = array();
		foreach ($documentValues1 as $data) {
			if(isset($data['tag']) && $data['tag'] == 'D:VENDOR_DATA')
			{
				$documentData = $data['value'];
			}
		}

		$deXml = simplexml_load_string($documentData);
		$deJson = json_encode($deXml);
		$xml_array = json_decode($deJson,TRUE);  
		//return $xml_array;    
	   $status = $xml_array['HEADER']['STATUS'];
		if($status == 1){

		   foreach($xml_array['DATA'] as $data){

			 foreach($data as $key => $value){
					if(is_array($value) && empty($value)){
						$data[$key] = '';
					}
				}

			$vendor_code = $data['VENDOR_CODE'];
			$purchase_org = $data['PURCHASE_ORG'];
			$title = $data['TITLE'];
			$name1 = $data['NAME1'];
			$name2 = $data['NAME2'];
			$email = $data['EMAIL'];
			$address1 = $data['ADDRESS1'];
			$address2 = $data['ADDRESS2'];
			$city = $data['CITY'];
			$region_key = $data['REGION_KEY'];
			$region_text = $data['REGION_TEXT'];
			$country_key = $data['COUNTRY_KEY'];
			$country = $data['COUNTRY'];
			$post_code = $data['POST_CODE'];
			$phone = $data['PHONE'];

			$isExists = Location::where(['location_name'=>$name1,'manufacturer_id'=>$data1['manufacturer_id'],'location_type_id'=>$data1['location_type_id']])->pluck('location_id');
			if(!empty($isExists)){
				$message ='Some vendors already exists';
				goto xyz;
			}
			$location = new Location;
			$location->location_name = $name1;
			$location->location_type_id = $data1['location_type_id'];
			$location->manufacturer_id = $data1['manufacturer_id'];
			$location->firstname = $name2;
			$location->lastname = $name2;
			$location->location_email = $email;
			$location->location_address = $address1;
			$location->location_details = $address2;
			$location->city = $city;
			$location->region = $region_text;
			$location->country = $country;
			$location->pincode = $post_code;
			$location->phone_no = $phone;
			$location->erp_code = $vendor_code;
			$location->save();  
		xyz:
		}
			$status =1;
			if(!isset($message) && empty($message)){
			$message= 'Data successfully retrieved and inserted'; 
		}

		}
		else{
			throw new \Exception('Data not retrieved');
		}
		}
		catch(Exception $e){
			$message = $e->getMessage();
		}
		return Response::json(['Status' => $status,'Message' => $message]); 

			}

	public function CUSTOMER($response,$data1)
	{
	  try{
		$status =0;
		$parseData1 = xml_parser_create();
		xml_parse_into_struct($parseData1, $response, $documentValues1, $documentIndex1);
		xml_parser_free($parseData1);
		$documentData = array();
		foreach ($documentValues1 as $data) {
			if(isset($data['tag']) && $data['tag'] == 'D:GET_CUSTOMER')
			{
				$documentData = $data['value'];
			}
		}

		if(empty($documentData))
			throw new Exception('ERP error call occurred');
	  
		$deXml = simplexml_load_string($documentData);
		$deJson = json_encode($deXml);
		$xml_array = json_decode($deJson,TRUE);  
	   
		$status = $xml_array['HEADER']['Status'];
		if($status == 1){
			
			\Log::info($xml_array['DATA']);

			 foreach($xml_array['DATA'] as $data){

				foreach($data as $key => $value){
					if(is_array($value) && empty($value)){
						$data[$key] = '';
					}
				}


			$customer_id = ltrim($data['CUSTOMER_ID'],0);
			$sales_org = $data['SALES_ORG'];
			$dist_ch = $data['DIST_CH'];
			$division = $data['DIVISION'];
			$title = $data['TITLE'];
			$name1 = $data['NAME1'];
			$name2 = $data['NAME2'];
			$name3 = $data['NAME3'];
			$email = $data['EMAIL'];
			$address1 = $data['ADDRESS1'];
			$address2 = $data['ADDRESS2'];
			$city = $data['CITY'];
			$district = $data['DISTRICT'];
			$region_key = $data['REGION_KEY'];
			$country_key = $data['COUNTRY_KEY'];
			$country = $data['COUNTRY'];
			$po = $data['PO'];
			$phone = $data['PHONE'];
			$date = date("Y-m-d H:i:s");

			$isExists = Location::where(['erp_code'=>$customer_id,'manufacturer_id'=>$data1['manufacturer_id']])->pluck('location_id');
			if(!empty($isExists)){
				

				\Log::info('Customer exists');
                \Log::info('Updating Customer');
                         
                Location::where(['location_id' => $isExists])
                          ->update([
                       'location_name'=>$name1,
                       'firstname'=>$name2,
                       'lastname' =>$name3,
                       'location_email' => '',
                       'location_address' => $address1,
                       'location_details'=> $address2,
                       'city'=>$city,
                       'region'=>$district,
                       'country' => $country,
                       'pincode' => $po,
                       'phone_no'=>$phone,
                       'created_date'=>$date
                       ]);

                $message ='Some Customers already exists';
				goto xyz;     

			}


			$location = new Location;
			$location->location_name = $name1;
			$location->location_type_id = $data1['location_type_id'];
			$location->manufacturer_id = $data1['manufacturer_id'];
			$location->firstname = $name2;
			$location->lastname = $name3;
			$location->location_email = '';
			$location->location_address = $address1;
			$location->location_details = $address2;
			$location->city = $city;
			$location->region = $district;
			$location->country = $country;
			$location->pincode = $po;
			$location->phone_no = $phone;
			$location->erp_code = $customer_id;
			$location->created_date = $date;
			$location->save();  
		   
		   xyz:
		   }
			$status =1;
			if(!isset($message) && empty($message)){
			$message= 'Data successfully retrieved and inserted'; 
		} 

		}
		else{
			throw new Exception('Data not retrieved');
		}
}
catch(Exception $e){
	$message = $e->getMessage();
}
return Response::json(['Status' => $status,'Message' => $message]); 

	}

	
	// Set up a request function
    public function request($username, $password, $url, $method = "GET", $data = null, $return_type = 'json', $token_method = null, $csrf = null, $xml = null,$sap_client=null)
    {
        try
        {
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            curl_setopt($curl, CURLOPT_USERPWD, "$username:$password");
            switch ($method)
            {
                case "POST":
                   
                    curl_setopt($curl, CURLOPT_POST, 1);
                    if ($data)
                    {
                        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
                    }
                    break;
                case "PUT":
                    curl_setopt($curl, CURLOPT_PUT, 1);
                    break;
                default:

                    if (!$token_method)
                    {

                        $url = sprintf("%s?\$filter=%s", $url, urlencode($this->generateData($data)));
                        if($sap_client){
                            if($method == 'POST')
                            {
                                $url = $url.'?&sap-client='.$sap_client;	
                            }else if($method == 'GET')
                            {
                                $url = $url.'&sap-client='.$sap_client;	
                            }
                        } 
                    }if ($token_method)
                    {

                        $url = $url . '/?$filter=';
                        $url .= rawurlencode('ESEAL_INPUT eq ') . "'" . urlencode($xml) . "'" . '&sap-client='.$sap_client;
                  
                    }
            }           
            if ($return_type != 'xml')
            {
                $url = $url . '&$format=' . $return_type;
            }
            	
            curl_setopt($curl, CURLOPT_URL, $url);

            if ($token_method)
            {
                /* Return the HTTP headers */
                if ($token_method == 1)
                {

                    curl_setopt($curl, CURLOPT_HEADER, true);
                    curl_setopt($curl, CURLOPT_NOBODY, true);
                    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, false);
                    curl_setopt($curl, CURLOPT_HTTPHEADER, array('X-CSRF-Token:Fetch'));
                }
                if ($token_method == 2)
                {
                    curl_setopt($curl, CURLOPT_HEADER, false);
                    curl_setopt($curl, CURLOPT_NOBODY, true);
                    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, false);
                    curl_setopt($curl, CURLOPT_POST, true);
                    curl_setopt($curl, CURLOPT_POSTFIELDS, $xml);
                    curl_setopt($curl, CURLOPT_HTTPHEADER, array('X-Requested-With: X', 'Content-Type: application/atom+xml;type=entry;charset=utf-8'));
                }
                /* Return the HTTP headers */
                curl_setopt($curl, CURLINFO_HEADER_OUT, false);
            } else
            {
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($curl, CURLOPT_HEADER, 0);
            }
            \Log::info($url);
            
            $result = curl_exec($curl);
            

            curl_close($curl);
            \Log::info($result);
            return $result;
        } catch (\ErrorException $ex)
        {
            return $ex->getMessage();
        }
    }

    public function DELIVERY(){
		return $response;
	}

	
	public function generateData($data)
	{
		try
		{
			if(!empty($data))
			{
				$resultString = '';
				foreach($data as $key => $value)
				{
					$pos = strpos($value, 'datetime');
					if ($pos === false) {
						
						$resultString = $resultString . $key . ' eq ' . "'" .$value . "' and ";
					}else{
					  
						$resultString = $resultString . $key . ' eq ' .$value . " and ";
					}
				}
				$resultString = substr($resultString, 0, -4);
				return $resultString;
			}
		} catch (\ErrorException $ex) {
			return $ex->getMessage();
		}
	}    

	public function SKU($response,$data1){
       try{
           \Log::info(__FUNCTION__);       	 
       	  $status =1;
       	  $message ='Product Inventory Updated successfully'; 

       	  $parseData1 = xml_parser_create();
            xml_parse_into_struct($parseData1, $response, $documentValues1, $documentIndex1);
            xml_parser_free($parseData1);
            $documentData = array();
            foreach ($documentValues1 as $data)
            {
                if (isset($data['tag']) && $data['tag'] == 'D:ESEAL_OUTPUT')
                {
                    $documentData = $data['value'];
                }
            }

            $deXml = simplexml_load_string($documentData);
            $deJson = json_encode($deXml);
            $xml_array = json_decode($deJson, TRUE);

             $status = $xml_array['HEADER']['Status'];
             if ($status == 1)
            {
                foreach ($xml_array['ITEM'] as $data)
                {
                    $insertData = array();
                    if(!empty($data))
                    {                        
                        $materialCode = isset($data['MATERIAL_NUMBER']) ? trim(ltrim(rtrim($data['MATERIAL_NUMBER']))) : 0;
                        \Log::info($materialCode);
                        if(strlen($materialCode) > 0)
                        {
                            \Log::info('materialCode => '.$materialCode);
                            $products = DB::table('products')->where(array('material_code' => $materialCode, 'manufacturer_id' => $this->_manufacturer_id))->pluck('product_id');
                            //$products = DB::table('products')->where('material_code', $materialCode)->pluck('product_id');
                            \Log::info('product => '.$products);
                            \Log::info('strlen => '.strlen($products));
                            if(strlen($products) > 0)
                            {
                                $plantErpCode = isset($data['PLANT']) ? $data['PLANT'] : '';
                                if($plantErpCode != '')
                                {
                                    $locationId = DB::table('locations')->where('erp_code', $plantErpCode)->pluck('location_id');
                                }
                               
                                $query = DB::table('product_inventory')->where(['product_id'=>$products,'location_id'=>$locationId]);
                                if(!$query->count()){
                                   $insertData['product_id'] = $products;
                                   $insertData['location_id'] = $locationId;
                                   $insertData['available_inventory'] = $data['VALUATED_UNRESTRICTED_USE'];
                                   $insertData['intransit_inventory'] = $data['STOCK_IN_TRANSIT'];
                                   $insertData['reserved'] = $data['RESERVATIONS'];
                                   $insertData['sold'] = $data['BLOCKED_STOCK'];

                                   DB::table('product_inventory')->insert($insertData);
                                }
                                else{

                                	$query->update([
                                       'available_inventory' => $data['VALUATED_UNRESTRICTED_USE'],
                                       'intransit_inventory' => $data['STOCK_IN_TRANSIT'],
                                       'reserved' => $data['RESERVATIONS'],
                                       'sold' => $data['BLOCKED_STOCK']
                                    ]);

                                }

                            }else{
                                \Log::info('Product does not exist');
                               }
                        }else{
                            \Log::info('Material code empty');
                        }
                    }else{
                        \Log::info('No Data');
                    }                    
                }
                $status = 1;
                if (!isset($message) && empty($message))
                {
                    $message = 'Data successfully retrieved and inserted';
                }
            }
            else
            {
                throw new \Exception('Data not retrieved');
            }

       }
       catch(Exception $e){
       	   $status =0;
           $message = $e->getMessage();
       }       
       \log::info(['Status' => $status, 'Message' => $message]);
       return json_encode(['Status' => $status, 'Message' => $message]);
	}

    public function ET_MAT_DISPLAY($response)
    {
        try
        {
            $status = 0;
            $parseData1 = xml_parser_create();
            xml_parse_into_struct($parseData1, $response, $documentValues1, $documentIndex1);
            xml_parser_free($parseData1);
            $documentData = array();
            foreach ($documentValues1 as $data)
            {
                if (isset($data['tag']) && $data['tag'] == 'D:PRODUCT_SKU')
                {
                    $documentData = $data['value'];
                }
            }
            $deXml = simplexml_load_string($documentData);
            $deJson = json_encode($deXml);
            $xml_array = json_decode($deJson, TRUE);

            $status = $xml_array['HEADER']['Status'];
            if ($status == 1)
            {
                foreach ($xml_array['ITEM'] as $data)
                {
                    $insertData = array();
                    if(!empty($data))
                    {                        
                        $materialCode = isset($data['MATERIAL_NO']) ? trim(ltrim(rtrim($data['MATERIAL_NO']))) : 0;
                        \Log::info($materialCode);
                        if(strlen($materialCode) > 0)
                        {
                            \Log::info('materialCode => '.$materialCode);
                            $products = DB::table('products')->where(array('material_code' => $materialCode, 'manufacturer_id' => $this->_manufacturer_id))->pluck('product_id');
                            //$products = DB::table('products')->where('material_code', $materialCode)->pluck('product_id');
                            \Log::info('product => '.$products);
                            \Log::info('strlen => '.strlen($products));
                            if(strlen($products) == 0)
                            {
                                $plantErpCode = isset($data['Plant']) ? $data['Plant'] : '';
                                if($plantErpCode != '')
                                {
                                    $locationId = DB::table('locations')->where('erp_code', $plantErpCode)->pluck('location_id');
                                }
                                $MaterialType = isset($data['Material_Type']) ? $data['Material_Type'] : '';
                                if('fert' == $MaterialType)
                                {
                                    $MaterialType = DB::table('master_lookup')->where('name', 'Finished Product')->pluck('value');
                                }else{
                                    $MaterialType = DB::table('master_lookup')->where('name', 'Component')->pluck('value');
                                }
                                
                                $dateCreated = isset($data['CREATED_ON']) ? $data['CREATED_ON'] : '';
                                $dateToTime = strtotime(implode('',array_reverse(explode('/', "30/06/2015"))));
                                $dateCreated = date('Y-m-d H:i:s', $dateToTime);
                                
                                $insertData['manufacturer_id'] = $this->_manufacturer_id;
                                $insertData['material_code'] = $materialCode;
                                $insertData['product_type_id'] = $MaterialType;
                                $insertData['name'] = isset($data['MATERIAL_DES']) ? $data['MATERIAL_DES'] : '';
                                $insertData['description'] = isset($data['MATERIAL_DES']) ? $data['MATERIAL_DES'] : '';
                                $insertData['business_unit_id'] = isset($data['BUSINESS_UNIT']) ? $data['BUSINESS_UNIT'] : '';
                                $insertData['mrp'] = isset($data['SELLING_PRICE']) ? $data['SELLING_PRICE'] : '';
                                $insertData['date_added'] = $dateCreated;
                                $insertData['created_from'] = 'ERP Import';
                                if(!empty($insertData))
                                {
                                    $latestInsertId = DB::table('products')->insertGetId($insertData);
                                    $updateData['sku'] = 'sku-'.$latestInsertId;
                                    DB::table('products')->where('product_id', $latestInsertId)->update($updateData);
                                    if(!empty($locationId) || $locationId != '')
                                    {
                                        $insertLocations['product_id'] = $latestInsertId; 
                                        $insertLocations['location_id'] = $locationId; 
                                        DB::table('product_locations')->insert($insertLocations);
                                    }
                                }else{
                                    \Log::info('Empty insertdata');
                                }
                            }else{
                                \Log::info('Product exists');
                                \Log::info('Updating Product');
                                \Log::info('Product MRP:- '.$data['SELLING_PRICE'] );
                                $updateData['mrp'] = isset($data['SELLING_PRICE']) ? $data['SELLING_PRICE'] : '';
                                
                                DB::table('products')
                                     ->where(array('material_code' => $materialCode, 'manufacturer_id' => $this->_manufacturer_id))
                                     ->update($updateData);

                            }
                        }else{
                            \Log::info('Material code empty');
                        }
                    }else{
                        \Log::info('No Data');
                    }                    
                }
                $status = 1;
                if (!isset($message) && empty($message))
                {
                    $message = 'Data successfully retrieved and inserted';
                }
            } else
            {
                throw new \Exception('Data not retrieved');
            }
        } catch (Exception $e)
        {
            $message = $e->getMessage();
        }
        return Response::json(['Status' => $status, 'Message' => $message]);
    }
}
