<?php
set_time_limit(0);
ini_set('memory_limit', '-1');

use Central\Repositories\RoleRepo;
use Central\Repositories\OrderRepo;
use Central\Repositories\CustomerRepo;
use Central\Repositories\SapApiRepo;
use Central\Repositories\ApiRepo;


class TrackandtraceController extends BaseController 
{

	
	protected $custRepo;
	protected $roleAccess;
	protected $attributeTable = 'attributes';
	protected $TPAttributeMappingTable = 'tp_attributes';    
	protected $attributeMappingTable = 'attribute_mapping';  
	protected $trackHistoryTable = 'track_history';
	protected $trackDetailsTable = 'track_details';
	protected $tpDetailsTable = 'tp_details';    
	protected $tpDataTable = 'tp_data';        
	protected $tpPDFTable = 'tp_pdf';            
	protected $locationsTable = 'locations';            
	protected $prodSummaryTable = 'production_summary';            
	protected $transactionMasterTable = 'transaction_master';  
	protected $bindHistoryTable = 'bind_history';  
	protected $valuation ='valuation_type';          
	private $_childCodes = Array();
	private $_apiRepo;

	public function __construct(RoleRepo $roleAccess,CustomerRepo $custRepo,SapApiRepo $sapRepo, ApiRepo $apiRepo) 
	{
		$this->roleAccess = $roleAccess;
		$this->custRepo = $custRepo;
		$this->sapRepo = $sapRepo;
		$this->_apiRepo = $apiRepo;		
	}

	
	public function kill(){

		/*$result = DB::select("SHOW FULL PROCESSLIST");

		foreach($result as $res){
		  $process_id=$res->Id;
		  
			$sql="KILL ".$process_id;
			DB::statement($sql);
		  }   */ 

        $locations = DB::table('locations1')->get();

        foreach($locations as $loc){
        	DB::table('locations1')->where('location_id',$loc->location_id)->update(['erp_code'=> ltrim($loc->erp_code,0)]);
        }
        $status =1;
        $message = 'Updated successfully';

        return json_encode(['Status'=>$status,'Message'=>$message]);


	}

        private function getTime(){
		$time = microtime();
		$time = explode(' ', $time);
		$time = ($time[1] + $time[0]);
		return $time;
	}
 
        public function getDate(){
		return date("Y-m-d H:i:s");
	}
 
	public function checkUserPermission($api_name){
		try{		
			$status = 0;
			
			$data = Input::get();
			if($api_name == 'login' || $api_name == 'login1' || $api_name == 'forgotPassword' || $api_name == 'resetPassword' || $api_name == 'sendLogEmail' || $api_name == 'apiTest' || $api_name == 'getAppVersions' || $api_name  =='getDate' || $api_name == 'test2'){
				
				$result = $this->$api_name($data);
				$response = json_decode($result);
				if($api_name == 'login' || $api_name == 'login1'){
				if($response->Status){
					$user_id = $this->roleAccess->getUserId($data['user_id']);
					$details = $this->roleAccess->getUserDetailsByUserId($user_id);
				   
					$log = new ApiLog;
					$log->user_id = $user_id;
					$log->location_id = $details[0]->location_id;
					$log->api_name = $api_name;
					$log->manufacturer_id = $details[0]->customer_id;            
					$log->input = serialize($data); 
					$log->created_on = date('Y-m-d h:i:s');
					$log->status =1;
					$log->message = $response->Message;
					$log->save();

					DB::table('user_tracks')->insert([
						'user_id'=>$user_id,
						'service_name'=>$api_name,
						'message'=>$response->Message,
						'status'=>1,
						'manufacturer_id'=> $details[0]->customer_id
						]);

				}		
				}
				return $result;
			} 	
			else{
			$module_id = $data['module_id'];
			$access_token = $data['access_token'];
			if(empty($module_id) || empty($access_token)){
				throw new Exception('Parameters Missing.');	
			}else{
				$result = $this->roleAccess->checkPermission($module_id,$access_token);
				
				if($result == 1){					
                                    
					$result = $this->$api_name($data);
                                     
					$response = json_decode($result);
										
					$user_id = DB::table('users_token')->where('access_token',$access_token)->pluck('user_id');
					$details = $this->roleAccess->getUserDetailsByUserId($user_id);
				
					$log = new ApiLog;
					$log->user_id = $user_id;
					$log->location_id = $details[0]->location_id;
					$log->api_name = $api_name;
					$log->manufacturer_id = $details[0]->customer_id;            
					$log->input = serialize($data); 
					$log->created_on = date('Y-m-d h:i:s');
					$log->status = $response->Status;
					$log->message = $response->Message;
					$log->save();

					DB::table('user_tracks')->insert([
						'user_id'=>$user_id,
						'service_name'=>$api_name,
						'message'=>$response->Message,
						'status'=>$response->Status,
						'manufacturer_id'=> $details[0]->customer_id
						]);
			
					return $result;
					
				}else{
					throw new Exception('User dont have permission.');	
				}
			}
		}
	
		}

		catch(Exception $e){
			$message = $e->getMessage();
		}
		return json_encode(['Status'=>$status,'Message'=>'Server:' .$message]);
	}

	

	public function login($data){
		try{
			Log::info($data);
			
			$status =0;
			$user_id = $data['user_id'];
			$password = $data['password'];
			$module_id = $data['module_id'];

			if(empty($user_id) || empty($password) || empty($module_id)){
				throw new Exception('Parameters Missing');
			}
			
			$user= $this->roleAccess->authenticateUser($user_id,$password);
			if(!empty($user))
			{
				$user_id = $user[0]->user_id;
				$length =16;
				$rand_id="";
				for($i=1; $i<=$length; $i++)
				{
					mt_srand((double)microtime() * 1000000);
					$num = mt_rand(1,36);
					$rand_id .= $this->roleAccess->assign_rand_value($num);
				}
				$master = MasterLookup::where('value',$module_id)->get();
				if(empty($master[0]))
					throw new Exception('In-valid Module Id.');

				$access = Token::where(['user_id'=>$user_id,'module_id'=>$module_id])->first();
				if(empty($access)){
					$token = new Token;
					$token->user_id = $user_id;
					$token->module_id = $module_id;
					$token->access_token = $rand_id;
					$token->save();
				}
				else{
					$rand_id = $access->access_token;
				}
				$userinfo = DB::table('users')
							->leftJoin('locations','locations.location_id','=','users.location_id')
							->leftJoin('location_types','location_types.location_type_id','=','locations.location_type_id')
							->leftJoin('user_roles','user_roles.user_id','=','users.user_id')
							->where('user_roles.user_id',$user_id)
							->get(['locations.location_id','locations.location_name','locations.location_type_id','location_types.location_type_name','locations.location_email','locations.location_address','locations.location_details','locations.erp_code','users.firstname','users.user_id','users.lastname','users.email','users.customer_id',DB::raw('group_concat(user_roles.role_id) as role_id'),'users.location_id'])[0];
				if(empty($userinfo)){
					throw new Exception('Role not assigned to User');
				}
				$roles =  explode(',',$userinfo->role_id);
				Log::info($roles);
				$manufacturer_name =  DB::table('eseal_customer')->where('customer_id',$userinfo->customer_id)->pluck('brand_name');
				$user = array('user_id'=>(string)$userinfo->user_id,'firstname'=> $userinfo->firstname,'lastname'=>$userinfo->lastname,'email'=> $userinfo->email,'manufacturer_id'=> $userinfo->customer_id,'manufacturer_name'=>$manufacturer_name);
				$warehouse = DB::table('wms_entities')->where(array('location_id'=>intval($userinfo->location_id), 'entity_type_id'=>6001))->pluck('id');
				$location = array('location_id'=>intval($userinfo->location_id),'name'=>$userinfo->location_name,'location_type_id'=>intval($userinfo->location_type_id),'erp_code'=>$userinfo->erp_code,'location_type_name'=>$userinfo->location_type_name,'email'=>$userinfo->location_email,'address'=>$userinfo->location_address,'details'=>$userinfo->location_details,'warehouse_id'=>intval($warehouse));
				
				$permissioninfo = DB::table('role_access')
									->leftJoin('features','role_access.feature_id','=','features.feature_id')
									->join('features as fs','fs.feature_id','=','features.parent_id')
									->where(['features.master_lookup_id'=>$module_id])
									->whereIn('role_access.role_id',$roles)                     
									->get(['features.name','features.feature_code','fs.feature_code as parent_feature_code']);
				
				/*$traninfo = DB::table('transaction_master')
								->where('manufacturer_id',$userinfo->customer_id)
								->get();*/
				$traninfo = DB::table('role_access')
								   ->join('features','role_access.feature_id','=','features.feature_id')
								   ->join('master_lookup','master_lookup.value','=','features.master_lookup_id')
								   ->join('transaction_master','transaction_master.name','=','features.name')
								   ->where(['master_lookup_id'=>4002,'transaction_master.manufacturer_id'=>$userinfo->customer_id])
								   ->whereIn('role_access.role_id',$roles)
								   ->orderBy('seq_order','desc')
								   ->select('transaction_master.*')
                                   ->addSelect(DB::raw('cast(transaction_master.id as char) as id'),DB::raw('cast(transaction_master.srcLoc_action as char) as srcLoc_action'),DB::raw('cast(transaction_master.dstLoc_action as char) as dstLoc_action'),DB::raw('cast(transaction_master.intrn_action as char) as intrn_action'),DB::raw('cast(transaction_master.seq_order as char) as seq_order'))
                                   ->get();

			Log::info('Login Successfull');
				return json_encode(['Status'=>1,'Message'=>'Successfull Login','Data'=>['user_info'=>$user,'permissions'=>$permissioninfo,'location'=>$location,'transitions'=>$traninfo,'access_token'=>$rand_id]]);
			}
			else{
				throw new Exception('Invalid UserId or Password.');
			}
		}
		catch(Exception $e){
			$message  = $e->getMessage();
		}
		return json_encode(['Status'=>$status,'Message' =>'Server: '.$message]);
	}


	public function login1($data){
		try{
			Log::info($data);
			
			$status =0;
			$user_id = $data['user_id'];
			$password = $data['password'];
			$module_id = $data['module_id'];

			if(empty($user_id) || empty($password) || empty($module_id)){
				throw new Exception('Parameters Missing');
			}
			
			$user= $this->roleAccess->authenticateUser($user_id,$password);
			if(!empty($user)){
				$user_id = $user[0]->user_id;
				$length =16;
				$rand_id="";
				for($i=1; $i<=$length; $i++)
				{
					mt_srand((double)microtime() * 1000000);
					$num = mt_rand(1,36);
					$rand_id .= $this->roleAccess->assign_rand_value($num);
				}
				$master = MasterLookup::where('value',$module_id)->get();
				if(empty($master[0]))
					throw new Exception('In-valid Module Id.');

				$access = Token::where(['user_id'=>$user_id,'module_id'=>$module_id])->first();
				if(empty($access)){
					$token = new Token;
					$token->user_id = $user_id;
					$token->module_id = $module_id;
					$token->access_token = $rand_id;
					$token->save();
				}
				else{
					$rand_id = $access->access_token;
				}
				$userinfo = DB::table('users')
							->leftJoin('locations','locations.location_id','=','users.location_id')
							->leftJoin('location_types','location_types.location_type_id','=','locations.location_type_id')
							->leftJoin('user_roles','user_roles.user_id','=','users.user_id')
							->where('user_roles.user_id',$user_id)
							->groupBy('user_roles.user_id')
							->get(['locations.location_id','locations.location_name','locations.location_type_id','location_types.location_type_name','locations.location_email','locations.location_address','locations.location_details','locations.erp_code','users.firstname','users.user_id','users.lastname','users.email','users.customer_id',DB::raw('group_concat(user_roles.role_id) as role_id'),'users.location_id'])[0];
				
				
			
				if(empty($userinfo)){
					throw new Exception('Role not assigned to User');
				}
				$roles = explode(',',$userinfo->role_id);
				Log::info($roles);
				$manufacturer_name =  DB::table('eseal_customer')->where('customer_id',$userinfo->customer_id)->pluck('brand_name');
				$user = array('user_id'=>(string)$userinfo->user_id,'firstname'=> $userinfo->firstname,'lastname'=>$userinfo->lastname,'email'=> $userinfo->email,'manufacturer_id'=> $userinfo->customer_id,'manufacturer_name'=>$manufacturer_name);
				$location = array('location_id'=>intval($userinfo->location_id),'name'=>$userinfo->location_name,'location_type_id'=>intval($userinfo->location_type_id),'erp_code'=>$userinfo->erp_code,'location_type_name'=>$userinfo->location_type_name,'email'=>$userinfo->location_email,'address'=>$userinfo->location_address,'details'=>$userinfo->location_details);
				$warehouse = DB::table('wms_entities')->select('id','entity_type_id','location_id')->where(array('location_id'=>$location['location_id'], 'entity_type_id'=>6001))->get();
				
				$permissioninfo = DB::table('role_access')
									->leftJoin('features','role_access.feature_id','=','features.feature_id')
									->join('features as fs','fs.feature_id','=','features.parent_id')
									->where(array('features.master_lookup_id'=>$module_id))
									->whereIn('role_access.role_id',$roles)                     
									->get(['features.name','features.feature_code','fs.feature_code as parent_feature_code']);

				/*$traninfo = DB::table('transaction_master')
								->where('manufacturer_id',$userinfo->customer_id)
								->get();*/
				$traninfo = DB::table('role_access')
								   ->join('features','role_access.feature_id','=','features.feature_id')
								   ->join('master_lookup','master_lookup.value','=','features.master_lookup_id')
								   ->join('transaction_master','transaction_master.name','=','features.name')
								   ->where(['master_lookup_id'=>4002,'transaction_master.manufacturer_id'=>$userinfo->customer_id])
								   ->whereIn('role_access.role_id',$roles)
								   ->orderBy('seq_order','desc')
								    ->select('transaction_master.*')
                                                                   ->addSelect(DB::raw('cast(transaction_master.id as char) as id'),DB::raw('cast(transaction_master.srcLoc_action as char) as srcLoc_action'),DB::raw('cast(transaction_master.dstLoc_action as char) as dstLoc_action'),DB::raw('cast(transaction_master.intrn_action as char) as intrn_action'),DB::raw('cast(transaction_master.seq_order as char) as seq_order'))
                                                                   ->get();




			Log::info('Login Successfull');
				return json_encode(['Status'=>1,'Message'=>'Successfull Login','Data'=>['user_info'=>$user,'permissions'=>$permissioninfo,'location'=>$location,'warehouse'=>$warehouse,'transitions'=>$traninfo,'access_token'=>$rand_id]]);
			}
			else{
				throw new Exception('Invalid UserId or Password.');
			}
		}
		catch(Exception $e){
			$message  = $e->getMessage();
		}
		return json_encode(['Status'=>$status,'Message'=>$message]);
	}


   public function productsByLocationService($data)
	{
		try
		{
			Log::info($data);
			$status =0;
			$filterArray =  array();
			$prod = array();
			$location_id = $data['location_id'];
			$pid = trim(Input::get('pid'));
			$update_time = trim(Input::get('update_time'));
			$mfg_id = $this->roleAccess->getMfgIdByToken($data['access_token']);

			if(empty($location_id) || empty($pid) || empty($update_time))
			{
				throw new Exception('Parameters Missing.');
			}
			$filters = ['newProducts'=>['product_id'=>$pid],'modifiedProducts'=>['date_modified'=>$update_time]];
			$business_unit_id =  Location::where('location_id',$location_id)->pluck('business_unit_id');
			Log::info('Business Unit Id :'.$business_unit_id);

			$result = DB::table('product_locations')
						->join('products','products.product_id','=','product_locations.product_id')			            
						->where('product_locations.location_id',$location_id)
						->where('products.business_unit_id',$business_unit_id)
						//->orWhereIn('location_id',$childIds)
						->groupBy('products.group_id')				
						->select('products.group_id')
						->get();			
			Log::info($result);			
			if(!empty($result))
			{
				$status =1;
				$message ='Data retrieved successfully.';
			}
			else
			{
				throw new Exception('Data not found.');	
			}
			foreach($filters as $key => $value){
			$prod =  array();
			foreach($value as $key1 => $value1){	
			foreach($result as $res)
			{

              
                   
				$products = array();				
				//$products = explode(',',$res->products);
				$attribute_set_id = DB::table('product_attributesets')->where(['product_group_id'=>$res->group_id,'location_id'=>$location_id])->pluck('attribute_set_id');
				
				$prodCollection = DB::table('products as pr')
                                        ->join('master_lookup as ml','ml.value','=','pr.product_type_id') 
										->join('product_locations as pl' ,'pr.product_id','=','pl.product_id')   
										->where(['pr.group_id'=>$res->group_id,'pr.business_unit_id'=>$business_unit_id,'pl.location_id'=>$location_id])
										->where('pr.'.$key1,'>=',$value1)
										->distinct()
										->get(['pr.product_id','ml.name as product_type','pr.group_id','pr.name','pr.title','pr.description','pr.image','pr.sku','pr.material_code','pr.is_traceable','is_batch_enabled','is_backflush','is_serializable','inspection_enabled','pr.field1','pr.field2','pr.field3','pr.field4','pr.field5','pr.model_name','pr.uom_unit_value']);
					$queries=DB::getQueryLog();
					//echo "<pre/>";print_r(end($queries));exit;

				$productInfo = array();
				if(count($prodCollection)){
					foreach($prodCollection as $collection){
					$group_name = DB::table('product_groups')->where(['group_id'=>$collection->group_id,'manufacture_id'=>$mfg_id])->pluck('name');	
					$prodInfo = ['product_id'=>(string)$collection->product_id,'name'=>$collection->name,'sku'=>$collection->sku,'title'=>$collection->title,'description'=>$collection->description,'material_code'=>$collection->material_code,'product_type_name'=>$collection->product_type,'is_traceable'=>$collection->is_traceable,'group_id'=>(int)$collection->group_id,'is_serializable'=>$collection->is_serializable,'is_batch_enabled'=>$collection->is_batch_enabled,'is_backflush'=>$collection->is_backflush,'inspection_enabled'=>$collection->inspection_enabled,'field1'=>$collection->field1,'field2'=>$collection->field2,'field3'=>$collection->field3,'field4'=>$collection->field4,'field5'=>$collection->field5,'model_name'=>$collection->model_name,'group_name'=>$group_name,'uom_value'=>$collection->uom_unit_value];
					
					$image = $collection->image;

					$levelCollection = DB::table('product_packages as pp')
										   ->join('master_lookup','master_lookup.value','=','pp.level')                                   
										   ->where('pp.product_id',$collection->product_id)
										   ->get(array(DB::raw('substr(master_lookup.name,-1) as level'),'master_lookup.name','master_lookup.description','pp.quantity as capacity','pp.height','pp.stack_height','pp.length','pp.width','pp.weight','pp.is_shipper_pack','pp.is_pallet'));
				
                                       $staticCollection = DB::table('attributes as attr')
							       ->join( 'product_attributes as pa','pa.attribute_id','=','attr.attribute_id')
							       ->where('pa.product_id',$collection->product_id)
							       ->orderBy('sort_order')											                                                              ->get(['attr.attribute_id','attr.text as name','attr.attribute_code','attr.input_type','pa.value as default_value','attr.is_required','attr.validation',DB::raw('0 as is_searchable')]);						  
											  					   
				$productInfo[] = ['product_info'=>$prodInfo,'image'=>$image,'static_attributes'=>$staticCollection,'levels'=>$levelCollection];



					}

					$attributeCollection = DB::table('attributes as attr')
											  ->join('attribute_set_mapping as asm','asm.attribute_id','=','attr.attribute_id')											  
											  ->where(['asm.attribute_set_id'=>$attribute_set_id])
											  ->orderBy('asm.sort_order','asc')
											  ->get(['attr.attribute_id','attr.text as name','attr.attribute_code','attr.input_type','attr.default_value','attr.is_required','attr.validation','asm.is_searchable']);

					$staticCollection = DB::table('attributes as attr')
											  ->join( 'product_attributes as pa','pa.attribute_id','=','attr.attribute_id')
											  ->where('pa.product_id',$collection->product_id)
											  ->orderBy('sort_order')											  
											  ->get(['attr.attribute_id','attr.text as name','attr.attribute_code','attr.input_type','pa.value as default_value','attr.is_required','attr.validation',DB::raw('0 as is_searchable')]);						  
                    
                    $attributeCollection = array_merge($staticCollection,$attributeCollection);

                    $attrCnt = count($attributeCollection);

                    for($i=0;$i < $attrCnt;$i++){
                    	if($attributeCollection[$i]->input_type == 'select'){
                         $defaults=  DB::table('attribute_options')->where('attribute_id',$attributeCollection[$i]->attribute_id)->lists('option_value');
                         $attributeCollection[$i]->options = $defaults;
                    	}
                    }       
					$prod[] = ['products'=>$productInfo,'late_attributes'=>$attributeCollection];
				}
				//echo "<pre/>";print_r(count($productInfo));exit;

			
			}
		  }

		  $filterArray[$key] = $prod; 	
		}
		
		}
		catch(Exception $e){
			$message = $e->getMessage();
		}
		//Log::info($prod);
		return json_encode(['Status'=>$status,'Message' =>'Server: '.$message,'Data'=>$filterArray]);
	}	
   
    

           private function sapCall($mfgId,$method,$method_name,$data,$access_token,$userErp){

	   try{
		$startTime = $this->getTime();
		$result = array();
		$query = DB::table('erp_integration')->where('manufacturer_id',$mfgId);
		if($query->pluck('id')){
			$erp = $query->select('web_service_url','token','company_code','web_service_username','web_service_password','sap_client')->get();
			
			$domain = $erp[0]->web_service_url;
			$token = $erp[0]->token;
			$company_code = $erp[0]->company_code;
			$sap_client = $erp[0]->sap_client;
			$username = $erp[0]->web_service_username;
		        $password = $erp[0]->web_service_password;	

			
			if($userErp){
			$erp = $this->roleAccess->getErpDetailsByUserId($access_token);	
			if(!empty($erp)){
				$username = $erp[0]->erp_username;
				$password = $erp[0]->erp_password;			
			}
			else{
				throw new Exception('There are no erp username and password');
			}	
		  }

			$data['TOKEN'] = $token;		
			$url = $domain.$method.'/'.$method_name;
			$url = sprintf("%s?\$filter=%s", $url, urlencode($this->sapRepo->generateData($data)));
			$url = $url.'&sap-client='.$sap_client;
			Log::info($url);
			$curl = curl_init();
			curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
			curl_setopt($curl, CURLOPT_USERPWD, "$username:$password");
			curl_setopt($curl, CURLOPT_URL, $url);
			curl_setopt($curl,CURLOPT_RETURNTRANSFER,true);
			curl_setopt($curl, CURLOPT_HEADER, 0);
		   
			$result = curl_exec($curl);
			curl_close($curl);
			Log::info($result);
			$status =1;
			$message =  'Data successfully retrieved';
		}
		else{
			throw new Exception('There is no erp-Configuration');
		}
		}
		catch(Exception $e){
			$status =0;
			$message = $e->getMessage();
		}
		$endTime = $this->getTime();
		Log::info(__FUNCTION__.' Finishes execution in '.($endTime-$startTime));
		Log::info(['Status'=>$status, 'Message' => $message,'Data'=>$result]);

		return json_encode(['Status'=>$status,'Message'=>$message,'Data'=>$result]);

  }

public function getPordersResponse()
	{
		$startTime = $this->getTime();
		Log::info(__FUNCTION__.' : '.print_r(Input::get(),true));
		$status =1;
		$message = 'Data retrieved successfully';
		$i= 0;
		$qty =0;
		$objectIds = trim(Input::get('object_ids'));
		$locationId = $this->roleAccess->getLocIdByToken(Input::get('access_token'));
		$mfgId = $this->roleAccess->getMfgIdByToken(Input::get('access_token'));
		$plantId = DB::table('locations')->where('location_id',$locationId)->pluck('erp_code');
		$data1 = array();		
		

		if(empty($objectIds))
		  throw new Exception('Document Ids are not passed.');

		try
		{	   		

		$documentArray = explode(',',$objectIds);
		$documentCnt = count($documentArray);

		foreach($documentArray as $document){
                  $xml_array = array();
                  $qty =0;
                  $method = 'Z030_ESEAL_GET_PORDER_DETAILS_SRV';
	          $method_name = 'GET_PORDER_DETAILS';
            $data = ['PORDER'=>$document];
           
           $response =  json_decode($this->sapCall($mfgId,$method,$method_name,$data,Input::get('access_token'),false),true);
		   Log::info('GET PORDER response:-');
		   Log::info($response);



		             $parseData1 = xml_parser_create();
					 xml_parse_into_struct($parseData1, $response['Data'], $documentValues1, $documentIndex1);
					 xml_parser_free($parseData1);
					 $documentData = array();	

					 foreach ($documentValues1 as $data) {
					  if(isset($data['tag']) && $data['tag'] == 'D:PORDER_DATA')
					  {
					   $documentData = $data['value'];
					  }
					 }

					 if(empty($documentData)){
						$xml_array = 'Error from ERP call';
						$status = 0;
						$i ++;
						goto jump;
					}

					$deXml = simplexml_load_string($documentData);
					$deJson = json_encode($deXml);
					$xml_array = json_decode($deJson,TRUE);
				    


		          Log::info($xml_array);




		          if($xml_array['HEADER']['Status'] == 0)
		          {
		          	$status = 0;
		          	$xml_array = $xml_array['HEADER']['Message'];		          	
		          	$i ++;
		          	goto jump;
		          }

		               $uom = $xml_array['DATA']['FINISHED_MATERIAL']['ITEM']['UOM'];
					   $material_code = $xml_array['DATA']['FINISHED_MATERIAL']['ITEM']['MATERIAL_CODE'];
					   $quantity = (int)$xml_array['DATA']['FINISHED_MATERIAL']['ITEM']['QUANTITY'];

					   if($uom == 'M'){
					   	$uom_unit_value = (int)Products::where('material_code',$material_code)->pluck('uom_unit_value');
					   	if(!$uom_unit_value)
					   		throw new Exception('Product doesnt exist');

					   	$qty = $quantity/$uom_unit_value; 
					   }
					   else{
					   	$qty = $quantity;
					   }

                   $doc = new DOMDocument();
                   $doc->loadXML($documentData);
                   $xml_array=$doc->saveXML();

                   jump:
                   array_push($data1,['Status'=>$status,'Message'=>$xml_array,'Document'=>$document,'Qty'=>$qty]);



			
		}

		if($documentCnt == $i)
			throw new Exception('Data not found for all the Production Orders');
		if($i !=0 && $documentCnt > $i){
		   $status =1;
                   $message='Partial data found';
		}
		
				
				
		}
		catch(Exception $e)
		{
			    $status = 0;
				Log::info($e->getMessage());
				$message = $e->getMessage();
		}
		$endTime = $this->getTime();
		Log::info(__FUNCTION__.' : finishes execution in '. ($startTime - $endTime).' seconds');
		Log::info(['Status'=>$status,'Message'=>$message,"Data"=>$data1]);
		return json_encode(['Status'=>$status,'Message'=>$message,"Data"=>$data1]);
	}



public function getScannedPrimaryForDelivery(){

    try{
    	Log::info(__FUNCTION__.' : '.print_r(Input::get(),true));
    	$status =1;
    	$i =0;
    	$delivery_no = trim(Input::get('delivery_no'));    	
    	$tp = trim(Input::get('tp'));
    	//dd($tp);
    	$inTransit = Input::get('inTransit');
    	$mfgId = $this->roleAccess->getMfgIdByToken(Input::get('access_token'));
        $batchData =  array();


        if(empty($delivery_no)){    		
    		  if(!$tp)
    			throw new Exception('Please pass TP');

    		$delivery_no = $tp;
    	}    	
     	//dd($delivery_no);

            $sql = 'select input,message from api_log where match (input) against('.$delivery_no.') and api_name="SyncStockOut" order by id desc';
            $result = DB::select($sql);

            if(empty($result))
              throw new Exception('There is no STO record for this deliveryNo');
            
            foreach($result as $res){
            
            $inputArray = unserialize($res->input);
            //dd($inputArray);
            $message = $res->message;
            log::info($inputArray);
            
            if($tp){
              $logDeliveryNo = $inputArray['codes'];
             }
            else{
            $logDeliveryNo = $inputArray['delivery_no'];
             }
            
            

            if(trim($logDeliveryNo) ==  $delivery_no)
               	break;
            
            
            $i++;  
            }
            
            
            if($i == count($result))
            	throw new Exception('There is no STO record.');

            $ids = $inputArray['ids'];
            $ids = explode(',',$ids);
            $locationId = $inputArray['srcLocationId'];
            
            if($inTransit){

            	$batchData = DB::table('eseal_'.$mfgId.' as es')
                              ->join('products as p','p.product_id','=','es.pid')
                              ->join('track_history as th','th.track_id','=','es.track_id')
                              ->where(function ($query) use($locationId){
                                $query->where('src_loc_id','!=',$locationId)
                                      ->orWhere('dest_loc_id','!=',0);
                              }
                              )
                              ->whereIn('primary_id',$ids)
							  ->get(['batch_no as Batch','primary_id as IOT','material_code as Material','level_id as Level','po_number as PORDER']);	

            }
            else{

            $batchData = DB::table('eseal_'.$mfgId.' as es')
                              ->join('products as p','p.product_id','=','es.pid')
                              ->where(function($query) use($ids){
							    $query->whereIn('primary_id', $ids)
									  ->orWhereIn('parent_id',$ids);
							   }
							   )
							  ->where('level_id',0)
							  ->get(['batch_no as Batch','primary_id as IOT','material_code as Material','level_id as Level','po_number as PORDER']);	
            }
            
            
       
       
    }
    catch(Exception $e){
    	$status =0;
    	$message = $e->getMessage();    	
    }
    Log::info(['Status'=>$status,'Message'=>'Server :'.$message,'Data'=>$batchData]);
    return json_encode(['Status'=>$status,'Message'=>'Server :'.$message,'Data'=>$batchData]);

   }

public function notifyEseal()
{
	$startTime = $this->getTime();
	$plantId = Input::get('plant_id');
	$objectType = Input::get('type');
	$objectId = Input::get('object_id');
	$action = Input::get('action');
	$status =1;
	$movement_type = Input::get('movement_type');
	if(!$movement_type)
		$movement_type =0;
	//$location_id = Input::get('location_id');

	$permission = $this->roleAccess->checkPermission(Input::get('module_id'),Input::get('access_token'));
	if(!$permission){
		Log::info('Permission denied');
		return json_encode(['Status'=>$status,'Message'=>'Permission Denied']);
	}

	$locationId = $this->roleAccess->getLocIdByToken(Input::get('access_token'));
	$mfg_id = $this->roleAccess->getMfgIdByToken(Input::get('access_token'));
	Log::info(__FUNCTION__.' : '.print_r(Input::get(),true));
		//For checking the record already exists are not in the erp objects table.
		$query1 = DB::table('erp_objects')->where(array('manufacturer_id' => $mfg_id, 'type' => $objectType, 'object_id' => $objectId,'action' => $action));
		$objectCount = $query1->count();

        
		
	   
		$query = DB::table('erp_integration')->where('manufacturer_id', $mfg_id);
		$erp = $query->select('web_service_url','token','company_code','web_service_username', 'web_service_password','sap_client')->get();

		$domain = $erp[0]->web_service_url;
		$token = $erp[0]->token;
		$company_code = $erp[0]->company_code;
		$username = $erp[0]->web_service_username;
		$password = $erp[0]->web_service_password;
		$sap_client = $erp[0]->sap_client;

	 
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		curl_setopt($curl, CURLOPT_USERPWD, "$username:$password");
		//curl_setopt($curl,CURLOPT_USERAGENT,$agent);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		
		
		$erp_objects = new ErpObjects;
		$erp_objects->type = $objectType;
		$erp_objects->object_id = $objectId;
		$erp_objects->action = $action;
		$erp_objects->movement_type = $movement_type;
		$erp_objects->plant_id = $plantId;
		$erp_objects->location_id = $locationId;        
		$erp_objects->manufacturer_id = $mfg_id;
		$erp_objects->created_on = $this->getDate();
		try
	{
			switch ($objectType)
			{
				case "PO_GRN":
					//Calling the SAP				
					
					$data = ['TOKEN' => $token, 'DOCUMENT' => $objectId];
					
					$method = 'Z029_ESEAL_GET_GRN_DATA_SRV';
					$method_name = 'GRN_OUTPUT';
					$url = $domain . $method . '/' . $method_name;
					$url = sprintf("%s?\$filter=%s", $url, urlencode($this->sapRepo->generateData($data)));
					$url = $url.'&sap-client='.$sap_client;
					Log::info('URL hit in notifyEseal:-');
					Log::info($url);
					curl_setopt($curl, CURLOPT_URL, $url);
					curl_setopt($curl, CURLOPT_HEADER, 0);
					$result = curl_exec($curl);
					curl_close($curl);
                                        Log::info($result);
					//echo "<pre/>";print_r($result);exit;
					$parseData1 = xml_parser_create();
					xml_parse_into_struct($parseData1, $result, $documentValues1, $documentIndex1);
					xml_parser_free($parseData1);
					$documentData = array();

					foreach ($documentValues1 as $data)
					{
						if (isset($data['tag']) && $data['tag'] == 'D:GET_GRN')
						{
							$documentData = $data['value'];
						}
					}
					if(empty($documentData)){
					   throw new Exception('Error from ERP call');
				   }
                                        Log::info($documentData);
					$deXml = simplexml_load_string($documentData);
					$deJson = json_encode($deXml);
					$xml_array = json_decode($deJson, TRUE);
					Log::info($xml_array);
					//echo "<pre/>";print_r($xml_array);exit;
					
                    $status =1;
					if ($objectCount == 0)
					{
						
						$erp_objects->process_status = 0;
						if ($xml_array['HEADER']['Status'] == 1)
						{
							$erp_objects->is_active =1;
							$erp_objects->response = $documentData;
							$message ='Data inserted succesfully';
							$erp_objects->save();
						}
						else
						{
							$erp_objects->save();
							throw new Exception('PO_GRN DETAILS notified.SAP response negative');
						}
						
						
					}
					else
			 		{
			 			
                        
                        if ($xml_array['HEADER']['Status'] == 1)
						{
							$query1->update(['is_active'=>1,'response'=>$documentData]);
							$message = 'PO_GRN DETAILS updated successfully';
						}else{

                       throw new Exception("PO_GRN DETAILS not updated.SAP response negative");
					}
					}
					
                    
                    if($movement_type == 0){
					//Calling the BindGrnData API from Eseal
					$request = Request::create('scoapi/bindGrnData', 'POST', array('module_id' => Input::get('module_id'), 'access_token' => Input::get('access_token'), 'grn_no' => $objectId,'transitionTime'=> $this->getDate()));
					$originalInput = Request::input(); //backup original input
					Request::replace($request->input());
					Log::info($request->input());
					$res2 = Route::dispatch($request)->getContent();
					$result = json_decode($res2);
					//echo "<pre/>";print_r($result->Status);exit;
					if ($xml_array['HEADER']['Status'] == 1)
					{
						$status =1;
						if ($result->Status == 1)
						{
							DB::table('erp_objects')
									->where(array('type' => $objectType, 'object_id' => $objectId, 'action' => $action, 'plant_id' => $plantId, 'location_id' => $locationId))
									->update(array('process_status' => $result->Status));
							$message = 'Data is inserted successfully and GRN received';
						}
						else
						{
							$message = "Data is inserted successfully but GRN not received. BindGrnData response:- ". $result->Message;
						}
					}
					else
					{
						throw new Exception('Data not inserted. GRN response:- '.$xml_array['HEADER']['Message']);
					}
				}
				
				else{
                  if($status == 1){
					
                    $transitionId = DB::table('transaction_master')->where(['name'=>'Reverse PGI','manufacturer_id'=>$mfg_id])->pluck('id');

                    if(empty($transitionId))
                    	throw new Exception('Reverse PGI transaction is not created');

					$request = Request::create('scoapi/reverseDelivery', 'POST', array('module_id' => Input::get('module_id'), 'access_token' => Input::get('access_token'), 'delivery' => $objectId,'transitionTime'=> $this->getDate(),'transitionId'=>$transitionId,'plant_id'=>$plantId,'movement_type'=>$movement_type));
					$originalInput = Request::input(); //backup original input
					Request::replace($request->input());
					Log::info($request->input());
					$res = Route::dispatch($request)->getContent();
					$result = json_decode($res,true);

					if($result['Status'] == 0)
						throw new Exception($result['Message']);
					else
						$message = $result['Message'];

				}

				}
					
					//echo "<pre/>";print_r($result);exit;
					break;
				case "PORDER":                
					//$mfg_id = $this->roleAccess->getMfgIdByToken(Input::get('access_token'));
					
					$data = ['TOKEN' => $token, 'PORDER' => $objectId];
					$method = 'Z030_ESEAL_GET_PORDER_DETAILS_SRV';
					$method_name = 'GET_PORDER_DETAILS';
					$url = $domain . $method . '/' . $method_name;
					$url = sprintf("%s?\$filter=%s", $url, urlencode($this->sapRepo->generateData($data)));
					$url = $url.'&sap-client='.$sap_client;
					Log::info('URL hit:-');
					Log::info($url);
					curl_setopt($curl, CURLOPT_URL, $url);
					curl_setopt($curl, CURLOPT_HEADER, 0);
					$result = curl_exec($curl);
					curl_close($curl);

                    Log::info($result);
					//echo "<pre/>";print_r($result);exit;
					$parseData1 = xml_parser_create();
					xml_parse_into_struct($parseData1, $result, $documentValues1, $documentIndex1);
					xml_parser_free($parseData1);
					$documentData = array();
					foreach ($documentValues1 as $data)
					{
						if (isset($data['tag']) && $data['tag'] == 'D:PORDER_DATA')
						{
							$documentData = $data['value'];
						}
					}
					if(empty($documentData)){
						throw new Exception('Error from ERP call');
				   }
					//return $documentData;
					$deXml = simplexml_load_string($documentData);
					$deJson = json_encode($deXml);
					$xml_array = json_decode($deJson, TRUE);
					Log::info($xml_array);
					//echo "<pre/>";print_r($xml_array);exit;
					if ($objectCount == 0)
					{
						$status =1;
						$erp_objects->process_status = 0;
						if ($xml_array['HEADER']['Status'] == 1)
						{
							$erp_objects->is_active = 1;
							$erp_objects->response = $documentData;
						}
						else
						{
							$message = "PORDER notified.SAP response negative";
						}
						$erp_objects->save();
						$message = $xml_array['HEADER']['Message'];
					} 
					else
					{
                       if ($xml_array['HEADER']['Status'] == 1)
						{
							$query1->update(['is_active'=>1,'response'=>$documentData]);
							$message = 'PODER updated successfully';
						}
                        else{
                          throw new Exception($xml_array['HEADER']['Message']);
                        }
					}
					break;
				case "SALESORDER":
					$data = ['TOKEN' => $token, 'SORDER' => $objectId];
					$method = 'Z037_ESEAL_GET_SORDER_DETAILS_SRV';
					$method_name = 'SALES_ORDER';
					$url = $domain . $method . '/' . $method_name;
					$url = sprintf("%s?\$filter=%s", $url, urlencode($this->sapRepo->generateData($data)));
					$url = $url.'&sap-client='.$sap_client;
					curl_setopt($curl, CURLOPT_URL, $url);
					curl_setopt($curl, CURLOPT_HEADER, 0);
					$result = curl_exec($curl);
					curl_close($curl);
                                         Log::info($result);
					//echo "<pre/>";print_r($result);exit;
					$parseData1 = xml_parser_create();
					xml_parse_into_struct($parseData1, $result, $documentValues1, $documentIndex1);
					xml_parser_free($parseData1);
					$documentData = array();
					foreach ($documentValues1 as $data)
					{
						if (isset($data['tag']) && $data['tag'] == 'D:GET_SO')
						{
							$documentData = $data['value'];
						}
					}
					if(empty($documentData)){
						throw new Exception('Error from ERP call');
					}
					$deXml = simplexml_load_string($documentData);
					$deJson = json_encode($deXml);
					$xml_array = json_decode($deJson, TRUE);
					Log::info($xml_array);
					//echo "<pre/>";print_r($xml_array);exit;
					if ($objectCount == 0)
					{
						$status =1;
						$erp_objects->process_status = 0;
						if ($xml_array['HEADER']['Status'] == 1)
						{
							$erp_objects->is_active =1;
							$erp_objects->response = $documentData;
							$message ='Data is inserted successfully';
						}
						else
						{
							$message = "Data is inserted but the response field is null because ERP status is zero.";
						}
						$erp_objects->save();
						
					} 
					else
					{
						throw new Exception("Records not inserted in ERP Objects due to duplicate entry.");
					}
					break;
				case "STOCKTRANSFER":
					$data = ['TOKEN' => $token, 'SORDER' => $objectId];
					$method = 'Z037_ESEAL_GET_SORDER_DETAILS_SRV';
					$method_name = 'SALES_ORDER';
					$url = $domain . $method . '/' . $method_name;
					$url = sprintf("%s?\$filter=%s", $url, urlencode($this->sapRepo->generateData($data)));
					$url = $url.'&sap-client='.$sap_client;
					curl_setopt($curl, CURLOPT_URL, $url);
					curl_setopt($curl, CURLOPT_HEADER, 0);
					$result = curl_exec($curl);
					curl_close($curl);

					$parseData1 = xml_parser_create();
					xml_parse_into_struct($parseData1, $result, $documentValues1, $documentIndex1);
					xml_parser_free($parseData1);
					$documentData = array();
					foreach ($documentValues1 as $data)
					{
						if (isset($data['tag']) && $data['tag'] == 'D:GET_SO')
						{
							$documentData = $data['value'];
						}
					}
					if(empty($documentData)){
						throw new Exception('Error from ERP call');
					}
					$deXml = simplexml_load_string($documentData);
					$deJson = json_encode($deXml);
					$xml_array = json_decode($deJson, TRUE);
					Log::info($xml_array);                   
					if ($objectCount == 0)
					{   
						$status =1;
						$erp_objects->process_status = $xml_array['HEADER']['Status'];
						if ($xml_array['HEADER']['Status'] == 1)
						{
							$erp_objects->response = $documentData;
							$message = 'Data inserted successfully';
						}
						else
						{
							$message = "Data is inserted but the response field is null because status getting zero.";
						}
						$erp_objects->save();
						
					} 
					else
					{
						throw new Exception("Records not inserted in ERP Objects due to duplicate entry.");
					}
					break;
				case "DELIVERYDETAILS":
					$data = ['TOKEN' => $token, 'DELIVERY' => $objectId];
					$method = 'Z036_ESEAL_GET_DELIVERY_DETAIL_SRV';
					$method_name = 'DELIVER_DETAILS';
					$url = $domain . $method . '/' . $method_name;
					$url = sprintf("%s?\$filter=%s", $url, urlencode($this->sapRepo->generateData($data)));
					$url = $url.'&sap-client='.$sap_client;
					Log::info('URL hit:-');
					Log::info($url);
					curl_setopt($curl, CURLOPT_URL, $url);
					curl_setopt($curl, CURLOPT_HEADER, 0);
					$result = curl_exec($curl);
					curl_close($curl);
                                         Log::info($result);
					//echo "<pre/>";print_r($result);exit;
					$parseData1 = xml_parser_create();
					xml_parse_into_struct($parseData1, $result, $documentValues1, $documentIndex1);
					xml_parser_free($parseData1);
					$documentData = array();
					foreach ($documentValues1 as $data)
					{
						if (isset($data['tag']) && $data['tag'] == 'D:GET_DELIVER')
						{
							$documentData = $data['value'];
						}
					}
					if(empty($documentData)){
						throw new Exception('Error from ERP call');
					}
					$deXml = simplexml_load_string($documentData);
					$deJson = json_encode($deXml);
					$xml_array = json_decode($deJson, TRUE);
					Log::info($xml_array);
					//echo "<pre/>";print_r($objectCount);exit;
					$status =1;
					if ($objectCount == 0)
					{
						
						$erp_objects->process_status = 0;
						if ($xml_array['HEADER']['Status'] == 1)
						{
							$erp_objects->is_active =1;
							$erp_objects->response = $documentData;
							$message ='Data inserted succesfully';
						}
						else
						{
							$message = "DELIVERY DETAILS notified.SAP response negative";
						}
						$erp_objects->save();
						
					}
					else
			 		{
			 			
                        
                        if ($xml_array['HEADER']['Status'] == 1)
						{
							$query1->update(['is_active'=>1,'response'=>$documentData]);
							$message = 'DELIVERY DETAILS updated successfully';
						}else{

                       throw new Exception("DELIVERYDETAILS not updated.SAP response negative");
					}
					}
					break;
					case "PO":
					$data = ['TOKEN' => $token, 'PO' => $objectId];
					$method = 'Z0049_GET_PO_DETAILS_SRV';
					$method_name = 'PURCHASE';
					$url = $domain . $method . '/' . $method_name;
					$url = sprintf("%s?\$filter=%s", $url, urlencode($this->sapRepo->generateData($data)));
					$url = $url.'&sap-client='.$sap_client;
					Log::info('URL hit:-');
					Log::info($url);
					curl_setopt($curl, CURLOPT_URL, $url);
					curl_setopt($curl, CURLOPT_HEADER, 0);
					$result = curl_exec($curl);
					curl_close($curl);
                                       Log::info($result);
					//echo "<pre/>";print_r($result);exit;
					$parseData1 = xml_parser_create();
					xml_parse_into_struct($parseData1, $result, $documentValues1, $documentIndex1);
					xml_parser_free($parseData1);
					$documentData = array();
					foreach ($documentValues1 as $data)
					{
						if (isset($data['tag']) && $data['tag'] == 'D:GET_PO')
						{
							$documentData = $data['value'];
						}
					}
					if(empty($documentData)){
						throw new Exception('Error from ERP call');
					}
					$deXml = simplexml_load_string($documentData);
					$deJson = json_encode($deXml);
					$xml_array = json_decode($deJson, TRUE);
					Log::info($xml_array);
					//echo "<pre/>";print_r($objectCount);exit;
					$status =1;
					if ($objectCount == 0)
					{
						
						$erp_objects->process_status = 0;
						if ($xml_array['HEADER']['Status'] == 1)
						{
							$erp_objects->plant_id = (int)$xml_array['DATA']['VENDOR'];
							$erp_objects->is_active =1;
							$erp_objects->response = $documentData;
							$message ='Data inserted succesfully';
						}
						else
						{
							$message = "Data inserted but the response field is null.";
						}
						$erp_objects->save();
						
					}
					else
			 		{
			 			//$noResponse = $query1->where('response',null)->pluck('id');
                        
                        if ($xml_array['HEADER']['Status'] == 1 )
						{
							//DB::table('erp_objects')->where('id',$noResponse)->update(['is_active'=>1,'response'=>$documentData]);
							$query1->update(['is_active'=>1,'response'=>$documentData,'plant_id'=>(int)$xml_array['DATA']['VENDOR']]);
							$message = 'Response Updated succesfully';
						}else{
                       
                        /*if($xml_array['HEADER']['Status'] != 1)
                        	throw new Exception('SAP response negative');
			 			else*/
                          	throw new Exception($xml_array['HEADER']['Message']);
					}
					}
					break;

			}
		} catch(Exception $e)
	{
			$status = 0;
			Log::info($e->getMessage());
			$message = $e->getMessage();
	}
	$endTime = $this->getTime();
	Log::info(__FUNCTION__.' Finishes execution in '.($endTime-$startTime));
	Log::info(['Status'=>$status, 'Message' => $message]);
	return json_encode(['Status'=>$status,'Message'=>'Server: '.$message]);
}

     public function warrantyInspect(){
       Log::info(__FUNCTION__.' : '.print_r(Input::get(),true));       
       try{
       	$status =1;
       	$message = 'Inspect data retrieved';
       	$data = array();       	
       	$mfgId = $this->roleAccess->getMfgIdByToken(Input::get('access_token'));
        $eseal_id = trim(Input::get('iot'));

        if(empty($eseal_id))
            throw new Exception('IOT parameter missing');

        $primaryCollection = DB::table('eseal_'.$mfgId)->where('primary_id',$eseal_id)->get(['is_active','level_id']);

        
        if(empty($primaryCollection))
        	throw new Exception('The IOT is in-valid');

        if($primaryCollection[0]->is_active == 0)
        	throw new Exception('The IOT is de-activated');

        if($primaryCollection[0]->level_id != 0)
        	throw new Exception('The IOT is not a product');
    
    try{

    	 $query = ' SELECT                    
                   pr.material_code AS MaterialCode,
                   pr.name AS ProductName,
                   (select am.value from bind_history bh join attribute_mapping am on am.attribute_map_id=bh.attribute_map_id where bh.eseal_id='.$eseal_id.' and am.attribute_id=471 limit 1) as Transformer,                   
                   l.location_name AS ManufacturedLocation,
                   es.mfg_date AS MfgDate,
                   th.update_time as ManufacturedTime,
                   "'.$eseal_id.'" as iot,
                   (select am.value from bind_history bh join attribute_mapping am on am.attribute_map_id=bh.attribute_map_id where bh.eseal_id='.$eseal_id.' and am.attribute_id=396 limit 1) as Pcb_Ems_Cem,
                   (select am.value from bind_history bh join attribute_mapping am on am.attribute_map_id=bh.attribute_map_id where bh.eseal_id='.$eseal_id.' and am.attribute_id=112 limit 1) as Pcb_Ems_Cem2,
                   (select am.value from bind_history bh join attribute_mapping am on am.attribute_map_id=bh.attribute_map_id where bh.eseal_id='.$eseal_id.' and am.attribute_id=505 limit 1) as PCB_version,
                   (select am.value from bind_history bh join attribute_mapping am on am.attribute_map_id=bh.attribute_map_id where bh.eseal_id='.$eseal_id.' and am.attribute_id=486 limit 1) as version,
                   (select am.value from bind_history bh join attribute_mapping am on am.attribute_map_id=bh.attribute_map_id where bh.eseal_id='.$eseal_id.' and am.attribute_id=494 limit 1) as Highcut,
                   (select am.value from bind_history bh join attribute_mapping am on am.attribute_map_id=bh.attribute_map_id where bh.eseal_id='.$eseal_id.' and am.attribute_id=496 limit 1) as Highcuthys,
                   (select am.value from bind_history bh join attribute_mapping am on am.attribute_map_id=bh.attribute_map_id where bh.eseal_id='.$eseal_id.' and am.attribute_id=497 limit 1) as Lowcut,
                   (select am.value from bind_history bh join attribute_mapping am on am.attribute_map_id=bh.attribute_map_id where bh.eseal_id='.$eseal_id.' and am.attribute_id=498 limit 1) as Lowcuthys,
                   (select am.value from bind_history bh join attribute_mapping am on am.attribute_map_id=bh.attribute_map_id where bh.eseal_id='.$eseal_id.' and am.attribute_id=499 limit 1) as Timedelay,
                   (select am.value from bind_history bh join attribute_mapping am on am.attribute_map_id=bh.attribute_map_id where bh.eseal_id='.$eseal_id.' and am.attribute_id=500 limit 1) as Outputvoltage,
                   (select am.value from bind_history bh join attribute_mapping am on am.attribute_map_id=bh.attribute_map_id where bh.eseal_id='.$eseal_id.' and am.attribute_id=501 limit 1) as Changeover_1,
                   (select am.value from bind_history bh join attribute_mapping am on am.attribute_map_id=bh.attribute_map_id where bh.eseal_id='.$eseal_id.' and am.attribute_id=502 limit 1) as Changeover_1Hys,
                   (select am.value from bind_history bh join attribute_mapping am on am.attribute_map_id=bh.attribute_map_id where bh.eseal_id='.$eseal_id.' and am.attribute_id=506 limit 1) as Changeover_2,
                   (select am.value from bind_history bh join attribute_mapping am on am.attribute_map_id=bh.attribute_map_id where bh.eseal_id='.$eseal_id.' and am.attribute_id=507 limit 1) as Changeover_2Hys,
                   (select am.value from bind_history bh join attribute_mapping am on am.attribute_map_id=bh.attribute_map_id where bh.eseal_id='.$eseal_id.' and am.attribute_id=525 limit 1) as Changeover_3,
                   (select am.value from bind_history bh join attribute_mapping am on am.attribute_map_id=bh.attribute_map_id where bh.eseal_id='.$eseal_id.' and am.attribute_id=526 limit 1) as Changeover_3Hys,
                   (select am.value from bind_history bh join attribute_mapping am on am.attribute_map_id=bh.attribute_map_id where bh.eseal_id='.$eseal_id.' and am.attribute_id=527 limit 1) as Changeover_4,
                   (select am.value from bind_history bh join attribute_mapping am on am.attribute_map_id=bh.attribute_map_id where bh.eseal_id='.$eseal_id.' and am.attribute_id=531 limit 1) as Changeover_4Hys,
                   (select am.value from bind_history bh join attribute_mapping am on am.attribute_map_id=bh.attribute_map_id where bh.eseal_id='.$eseal_id.' and am.attribute_id=530 limit 1) as Changeover_5,
                   (select am.value from bind_history bh join attribute_mapping am on am.attribute_map_id=bh.attribute_map_id where bh.eseal_id='.$eseal_id.' and am.attribute_id=532 limit 1) as Changeover_5Hys,
                   (select am.value from bind_history bh join attribute_mapping am on am.attribute_map_id=bh.attribute_map_id where bh.eseal_id='.$eseal_id.' and am.attribute_id=508 limit 1) as Outputmaxload,
                   (select am.value from bind_history bh join attribute_mapping am on am.attribute_map_id=bh.attribute_map_id where bh.eseal_id='.$eseal_id.' and am.attribute_id=533 limit 1) as charging_current,
                   (select am.value from bind_history bh join attribute_mapping am on am.attribute_map_id=bh.attribute_map_id where bh.eseal_id='.$eseal_id.' and am.attribute_id=519 limit 1) as battery_low_cut,
                   (select am.value from bind_history bh join attribute_mapping am on am.attribute_map_id=bh.attribute_map_id where bh.eseal_id='.$eseal_id.' and am.attribute_id=535 limit 1) as short_circuit,
                   (select am.value from bind_history bh join attribute_mapping am on am.attribute_map_id=bh.attribute_map_id where bh.eseal_id='.$eseal_id.' and am.attribute_id=518 limit 1) as FieldValidation,
                   (select am.value from bind_history bh join attribute_mapping am on am.attribute_map_id=bh.attribute_map_id where bh.eseal_id='.$eseal_id.' and am.attribute_id=466 limit 1) as Type,
                   (select am.value from bind_history bh join attribute_mapping am on am.attribute_map_id=bh.attribute_map_id where bh.eseal_id='.$eseal_id.' and am.attribute_id=545 limit 1) as Capacity                   
                   FROM eseal_'.$mfgId.' es JOIN track_details td ON td.code=es.primary_id
                   JOIN track_history th ON th.track_id=td.track_id
                   JOIN locations l ON l.location_id=th.src_loc_id
                   JOIN products pr ON pr.product_id=es.pid
                   WHERE es.primary_id='.$eseal_id.' and th.dest_loc_id =0 AND level_id=0 AND th.transition_id IN (702,708,710,711,712,719,787,790) order by th.track_id desc limit 1';
        
        $data = DB::select($query);  
        //dd($data);die;              
       }
        catch(PDOException $e)
    {
				Log::info($e->getMessage());
				throw new Exception('SQlError while fetching data');
			}

        if(empty($data))
        	throw new Exception('The IOT has\'nt undergone packing transactions');

        $query = 'select erp_code as CustomerSapCode,sync_time as TransactionTime,location_name as Customer from eseal_'.$mfgId.' es 
                      join track_history th on th.track_id=es.track_id
                      join locations l on l.location_id=th.dest_loc_id
                      where es.primary_id='.$eseal_id.' and dest_loc_id!=0 and level_id=0 and location_type_id=745';

        $customer =  DB::select($query);

        $data[0]->CustomerSapCode = empty($customer) ? '' : (int) $customer[0]->CustomerSapCode;

        $data[0]->Customer = empty($customer) ? '' : $customer[0]->Customer;
        $data[0]->TransactionTime = empty($customer) ? '' : $customer[0]->TransactionTime;

        //$data = json_encode($data, JSON_UNESCAPED_SLASHES);
        //dd($data);die;
        //$data = json_decode($data,true);
             //dd($data);die;
 

        Log::info('arrayyyyy');
        Log::info($data);       


       }
       catch(Exception $e){
       	$status = 0;
       	$message = $e->getMessage();
       }
       Log::info(['Status'=>$status,'Message'=>$message,'Data'=>$data]);
       return stripslashes(json_encode(['Status'=>$status,'Message'=>$message,'Data'=>$data]));

     }

     public function warrantyInspectMod1(){
       Log::info(__FUNCTION__.' : '.print_r(Input::get(),true));       
       try{
       	$status =1;
       	$message = 'Inspect data retrieved';
       	$data = array();
       	$dataString = '';
       	$mfgId = $this->roleAccess->getMfgIdByToken(Input::get('access_token'));
        $eseal_id = trim(Input::get('iot'));

        if(empty($eseal_id))
            throw new Exception('IOT parameter missing');

        $primaryCollection = DB::table('eseal_'.$mfgId)->where('primary_id',$eseal_id)->get(['is_active','level_id']);

        
        if(empty($primaryCollection))
        	throw new Exception('The IOT is in-valid');

        if($primaryCollection[0]->is_active == 0)
        	throw new Exception('The IOT is de-activated');

        if($primaryCollection[0]->level_id != 0)
        	throw new Exception('The IOT is not a product');

     try{

        $query = ' SELECT 
                   pr.material_code AS MaterialCode,
                   pr.name AS ProductName,
                   (select am.value from bind_history bh join attribute_mapping am on am.attribute_map_id=bh.attribute_map_id where bh.eseal_id='.$eseal_id.' and am.attribute_id=471) as Transformer,
                   (select am.value from bind_history bh join attribute_mapping am on am.attribute_map_id=bh.attribute_map_id where bh.eseal_id='.$eseal_id.' and am.attribute_id=396) as VendorName,
                   (select am.value from bind_history bh join attribute_mapping am on am.attribute_map_id=bh.attribute_map_id where bh.eseal_id='.$eseal_id.' and am.attribute_id=510) as Mft_Ocation,
                   l.location_name AS ManufacturedLocation,
                   es.mfg_date AS MfgDate,
                   th.update_time as ManufacturedTime,
                   "'.$eseal_id.'" as iot,
                   (select am.value from bind_history bh join attribute_mapping am on am.attribute_map_id=bh.attribute_map_id where bh.eseal_id='.$eseal_id.' and am.attribute_id=503) as Pcb_Ems_Cem,
                   (select am.value from bind_history bh join attribute_mapping am on am.attribute_map_id=bh.attribute_map_id where bh.eseal_id='.$eseal_id.' and am.attribute_id=505) as PCB_version,
                   (select am.value from bind_history bh join attribute_mapping am on am.attribute_map_id=bh.attribute_map_id where bh.eseal_id='.$eseal_id.' and am.attribute_id=486) as version,
                   (select am.value from bind_history bh join attribute_mapping am on am.attribute_map_id=bh.attribute_map_id where bh.eseal_id='.$eseal_id.' and am.attribute_id=494) as Highcut,
                   (select am.value from bind_history bh join attribute_mapping am on am.attribute_map_id=bh.attribute_map_id where bh.eseal_id='.$eseal_id.' and am.attribute_id=496) as Highcuthys,
                   (select am.value from bind_history bh join attribute_mapping am on am.attribute_map_id=bh.attribute_map_id where bh.eseal_id='.$eseal_id.' and am.attribute_id=497) as Lowcut,
                   (select am.value from bind_history bh join attribute_mapping am on am.attribute_map_id=bh.attribute_map_id where bh.eseal_id='.$eseal_id.' and am.attribute_id=498) as Lowcuthys,
                   (select am.value from bind_history bh join attribute_mapping am on am.attribute_map_id=bh.attribute_map_id where bh.eseal_id='.$eseal_id.' and am.attribute_id=499) as Timedelay,
                   (select am.value from bind_history bh join attribute_mapping am on am.attribute_map_id=bh.attribute_map_id where bh.eseal_id='.$eseal_id.' and am.attribute_id=500) as Outputvoltage,
                   (select am.value from bind_history bh join attribute_mapping am on am.attribute_map_id=bh.attribute_map_id where bh.eseal_id='.$eseal_id.' and am.attribute_id=501) as Changeover_1,
                   (select am.value from bind_history bh join attribute_mapping am on am.attribute_map_id=bh.attribute_map_id where bh.eseal_id='.$eseal_id.' and am.attribute_id=502) as Changeover_1Hys,
                   (select am.value from bind_history bh join attribute_mapping am on am.attribute_map_id=bh.attribute_map_id where bh.eseal_id='.$eseal_id.' and am.attribute_id=506) as Changeover_2,
                   (select am.value from bind_history bh join attribute_mapping am on am.attribute_map_id=bh.attribute_map_id where bh.eseal_id='.$eseal_id.' and am.attribute_id=507) as Changeover_2Hys,
                   (select am.value from bind_history bh join attribute_mapping am on am.attribute_map_id=bh.attribute_map_id where bh.eseal_id='.$eseal_id.' and am.attribute_id=525) as Changeover_3,
                   (select am.value from bind_history bh join attribute_mapping am on am.attribute_map_id=bh.attribute_map_id where bh.eseal_id='.$eseal_id.' and am.attribute_id=526) as Changeover_3Hys,
                   (select am.value from bind_history bh join attribute_mapping am on am.attribute_map_id=bh.attribute_map_id where bh.eseal_id='.$eseal_id.' and am.attribute_id=527) as Changeover_4,
                   (select am.value from bind_history bh join attribute_mapping am on am.attribute_map_id=bh.attribute_map_id where bh.eseal_id='.$eseal_id.' and am.attribute_id=531) as Changeover_4Hys,
                   (select am.value from bind_history bh join attribute_mapping am on am.attribute_map_id=bh.attribute_map_id where bh.eseal_id='.$eseal_id.' and am.attribute_id=530) as Changeover_5,
                   (select am.value from bind_history bh join attribute_mapping am on am.attribute_map_id=bh.attribute_map_id where bh.eseal_id='.$eseal_id.' and am.attribute_id=532) as Changeover_5Hys,
                   (select am.value from bind_history bh join attribute_mapping am on am.attribute_map_id=bh.attribute_map_id where bh.eseal_id='.$eseal_id.' and am.attribute_id=508) as Outputmaxload,
                   (select am.value from bind_history bh join attribute_mapping am on am.attribute_map_id=bh.attribute_map_id where bh.eseal_id='.$eseal_id.' and am.attribute_id=533) as charging_current,
                   (select am.value from bind_history bh join attribute_mapping am on am.attribute_map_id=bh.attribute_map_id where bh.eseal_id='.$eseal_id.' and am.attribute_id=519) as battery_low_cut,
                   (select am.value from bind_history bh join attribute_mapping am on am.attribute_map_id=bh.attribute_map_id where bh.eseal_id='.$eseal_id.' and am.attribute_id=535) as short_circuit,
                   (select am.value from bind_history bh join attribute_mapping am on am.attribute_map_id=bh.attribute_map_id where bh.eseal_id='.$eseal_id.' and am.attribute_id=518) as FieldValidation                              
                   FROM eseal_'.$mfgId.' es JOIN track_details td ON td.code=es.primary_id
                   JOIN track_history th ON th.track_id=td.track_id
                   JOIN locations l ON l.location_id=th.src_loc_id
                   JOIN products pr ON pr.product_id=es.pid
                   WHERE es.primary_id='.$eseal_id.' and th.dest_loc_id =0 AND level_id=0 AND th.transition_id IN (702,708,710,711,712,719,790)';


        $data = DB::select($query);                

    }
     catch(PDOException $e)
    {
				Log::info($e->getMessage());
				throw new Exception('SQlError while fetching data');
			}

        if(empty($data))
        	throw new Exception('The IOT has\'nt undergone packing transactions');

        $query = 'select erp_code as CustomerSapCode,sync_time as TransactionTime,location_name as Customer from eseal_'.$mfgId.' es 
                      join track_history th on th.track_id=es.track_id
                      join locations l on l.location_id=th.dest_loc_id
                      where es.primary_id='.$eseal_id.' and dest_loc_id!=0 and level_id=0 and location_type_id=745';

        $customer =  DB::select($query);

        $data[0]->TransactionTime = empty($customer) ? '' : $customer[0]->TransactionTime;
        $data[0]->CustomerSapCode = empty($customer) ? '' : $customer[0]->CustomerSapCode;        
        $data[0]->Customer = empty($customer) ? '' : $customer[0]->Customer;

        $data = json_encode($data);
        $data = json_decode($data,true);


        Log::info('arrayyyyy');
        Log::info($data);

        foreach($data[0] as $key => $value){
        	$dataString .= $value.'|';
        }


       }
       catch(Exception $e){
       	$status = 0;
       	$message = $e->getMessage();
       }
       Log::info(['Status'=>$status,'Message'=>$message,'Data'=>$dataString]);
       return json_encode(['Status'=>$status,'Message'=>$message,'Data'=>$dataString]);

     }

     public function warrantyInspectMod(){
       Log::info(__FUNCTION__.' : '.print_r(Input::get(),true));       
       try{
       	$status =1;
       	$message = 'Inspect data retrieved';
       	$data = array();
       	$dataString = '';
       	$isComponent = 0;
       	$mfgId = $this->roleAccess->getMfgIdByToken(Input::get('access_token'));
        $eseal_id = trim(Input::get('iot'));

        if(empty($eseal_id))
            throw new Exception('IOT parameter missing');

        $primaryCollection = DB::table('eseal_'.$mfgId)->where('primary_id',$eseal_id)->get(['is_active','level_id','pid']);

        
        if(empty($primaryCollection))
        	throw new Exception('The IOT is in-valid');

        if($primaryCollection[0]->is_active == 0)
        	throw new Exception('The IOT is de-activated');

        if($primaryCollection[0]->level_id != 0)
        	throw new Exception('The IOT is not a product');


        $product_type = DB::table('products')->where('product_id',$primaryCollection[0]->pid)->pluck('product_type_id');

        if($product_type != 8003)
        	$isComponent = 1;

try{
        $query = ' SELECT                    
                   pr.material_code AS MaterialCode,
                   pr.name AS ProductName,
                   (select am.value from bind_history bh join attribute_mapping am on am.attribute_map_id=bh.attribute_map_id where bh.eseal_id='.$eseal_id.' and am.attribute_id=471 limit 1) as Transformer,                   
                   l.location_name AS ManufacturedLocation,
                   es.mfg_date AS MfgDate,
                   th.sync_time as ManufacturedTime,
                   "'.$eseal_id.'" as iot,
                   (select am.value from bind_history bh join attribute_mapping am on am.attribute_map_id=bh.attribute_map_id where bh.eseal_id='.$eseal_id.' and am.attribute_id=396 limit 1) as Pcb_Ems_Cem,
                   (select am.value from bind_history bh join attribute_mapping am on am.attribute_map_id=bh.attribute_map_id where bh.eseal_id='.$eseal_id.' and am.attribute_id=112 limit 1) as Pcb_Ems_Cem2,
                   (select am.value from bind_history bh join attribute_mapping am on am.attribute_map_id=bh.attribute_map_id where bh.eseal_id='.$eseal_id.' and am.attribute_id=505 limit 1) as PCB_version,
                   (select am.value from bind_history bh join attribute_mapping am on am.attribute_map_id=bh.attribute_map_id where bh.eseal_id='.$eseal_id.' and am.attribute_id=486 limit 1) as version,
                   (select am.value from bind_history bh join attribute_mapping am on am.attribute_map_id=bh.attribute_map_id where bh.eseal_id='.$eseal_id.' and am.attribute_id=494 limit 1) as Highcut,
                   (select am.value from bind_history bh join attribute_mapping am on am.attribute_map_id=bh.attribute_map_id where bh.eseal_id='.$eseal_id.' and am.attribute_id=496 limit 1) as Highcuthys,
                   (select am.value from bind_history bh join attribute_mapping am on am.attribute_map_id=bh.attribute_map_id where bh.eseal_id='.$eseal_id.' and am.attribute_id=497 limit 1) as Lowcut,
                   (select am.value from bind_history bh join attribute_mapping am on am.attribute_map_id=bh.attribute_map_id where bh.eseal_id='.$eseal_id.' and am.attribute_id=498 limit 1) as Lowcuthys,
                   (select am.value from bind_history bh join attribute_mapping am on am.attribute_map_id=bh.attribute_map_id where bh.eseal_id='.$eseal_id.' and am.attribute_id=499 limit 1) as Timedelay,
                   (select am.value from bind_history bh join attribute_mapping am on am.attribute_map_id=bh.attribute_map_id where bh.eseal_id='.$eseal_id.' and am.attribute_id=500 limit 1) as Outputvoltage,
                   (select am.value from bind_history bh join attribute_mapping am on am.attribute_map_id=bh.attribute_map_id where bh.eseal_id='.$eseal_id.' and am.attribute_id=501 limit 1) as Changeover_1,
                   (select am.value from bind_history bh join attribute_mapping am on am.attribute_map_id=bh.attribute_map_id where bh.eseal_id='.$eseal_id.' and am.attribute_id=502 limit 1) as Changeover_1Hys,
                   (select am.value from bind_history bh join attribute_mapping am on am.attribute_map_id=bh.attribute_map_id where bh.eseal_id='.$eseal_id.' and am.attribute_id=506 limit 1) as Changeover_2,
                   (select am.value from bind_history bh join attribute_mapping am on am.attribute_map_id=bh.attribute_map_id where bh.eseal_id='.$eseal_id.' and am.attribute_id=507 limit 1) as Changeover_2Hys,
                   (select am.value from bind_history bh join attribute_mapping am on am.attribute_map_id=bh.attribute_map_id where bh.eseal_id='.$eseal_id.' and am.attribute_id=525 limit 1) as Changeover_3,
                   (select am.value from bind_history bh join attribute_mapping am on am.attribute_map_id=bh.attribute_map_id where bh.eseal_id='.$eseal_id.' and am.attribute_id=526 limit 1) as Changeover_3Hys,
                   (select am.value from bind_history bh join attribute_mapping am on am.attribute_map_id=bh.attribute_map_id where bh.eseal_id='.$eseal_id.' and am.attribute_id=527 limit 1) as Changeover_4,
                   (select am.value from bind_history bh join attribute_mapping am on am.attribute_map_id=bh.attribute_map_id where bh.eseal_id='.$eseal_id.' and am.attribute_id=531 limit 1) as Changeover_4Hys,
                   (select am.value from bind_history bh join attribute_mapping am on am.attribute_map_id=bh.attribute_map_id where bh.eseal_id='.$eseal_id.' and am.attribute_id=530 limit 1) as Changeover_5,
                   (select am.value from bind_history bh join attribute_mapping am on am.attribute_map_id=bh.attribute_map_id where bh.eseal_id='.$eseal_id.' and am.attribute_id=532 limit 1) as Changeover_5Hys,
                   (select am.value from bind_history bh join attribute_mapping am on am.attribute_map_id=bh.attribute_map_id where bh.eseal_id='.$eseal_id.' and am.attribute_id=508 limit 1) as Outputmaxload,
                   (select am.value from bind_history bh join attribute_mapping am on am.attribute_map_id=bh.attribute_map_id where bh.eseal_id='.$eseal_id.' and am.attribute_id=533 limit 1) as charging_current,
                   (select am.value from bind_history bh join attribute_mapping am on am.attribute_map_id=bh.attribute_map_id where bh.eseal_id='.$eseal_id.' and am.attribute_id=519 limit 1) as battery_low_cut,
                   (select am.value from bind_history bh join attribute_mapping am on am.attribute_map_id=bh.attribute_map_id where bh.eseal_id='.$eseal_id.' and am.attribute_id=535 limit 1) as short_circuit,
                   (select am.value from bind_history bh join attribute_mapping am on am.attribute_map_id=bh.attribute_map_id where bh.eseal_id='.$eseal_id.' and am.attribute_id=518 limit 1) as FieldValidation                              
                   FROM eseal_'.$mfgId.' es JOIN track_details td ON td.code=es.primary_id
                   JOIN track_history th ON th.track_id=td.track_id
                   JOIN locations l ON l.location_id=th.src_loc_id
                   JOIN products pr ON pr.product_id=es.pid
                   WHERE es.primary_id='.$eseal_id.' and th.dest_loc_id =0 AND level_id=0 AND th.transition_id IN (702,708,710,711,712,719,787,790)';


        $data = DB::select($query);                
    }
    catch(PDOException $e)
    {
				Log::info($e->getMessage());
				throw new Exception('SQlError while fetching data');
			}

        if(empty($data))
        	throw new Exception('The IOT has\'nt undergone packing transactions');
        //dd($data);
        $query = 'select erp_code as CustomerSapCode,sync_time as TransactionTime,location_name as Customer from eseal_'.$mfgId.' es 
                      join track_history th on th.track_id=es.track_id
                      join locations l on l.location_id=th.dest_loc_id
                      where es.primary_id='.$eseal_id.' and dest_loc_id!=0 and level_id=0 and location_type_id=745';

        $customer =  DB::select($query);

        $data[0]->CustomerSapCode = empty($customer) ? ' ' : (int) $customer[0]->CustomerSapCode;        
        $data[0]->Customer = empty($customer) ? ' ' : $customer[0]->Customer;
        $data[0]->isComponent = $isComponent;
        $data[0]->TransactionTime = empty($customer) ? ' ' : $customer[0]->TransactionTime;

        $data = json_encode($data);
        $data = json_decode($data,true);


        Log::info('arrayyyyy');
        Log::info($data);

        foreach($data[0] as $key => $value){
        	$dataString .= $value.'|';
        }

        $dataString = rtrim($dataString,'|');

       }
       catch(Exception $e){
       	$status = 0;
       	$message = $e->getMessage();       	
       }
       Log::info(['Status'=>$status,'Message'=>$message,'Data'=>$dataString]);
       return json_encode(['Status'=>$status,'Message'=>$message,'Data'=>$dataString]);

     }


       public function verifySalesOrderReversals(){
  	try{
  		Log::info(__FUNCTION__.' : '.print_r(Input::get(),true));
  		$status =1;
  		$message = 'Verified Successfully';
  		$uomArray = array();
  		$systemArray = array();  		
  		$materialArray =  array();
        $delivery_no = trim(Input::get('delivery_no'));
        $purchase_no = trim(Input::get('purchase_order_no'));
        $grn_no = (int)trim(Input::get('grn_no'));
        $ids = trim(Input::get('ids'));
        $locationId = $this->roleAccess->getLocIdByToken(Input::get('access_token'));
        $mfgId = $this->roleAccess->getMfgIdByToken(Input::get('access_token'));
   		$erpDetails = DB::table('erp_integration')->where('manufacturer_id',$mfgId)->get(['token','web_service_url']);
		$erpToken = $erpDetails[0]->token;
	    $erpUrl = $erpDetails[0]->web_service_url;	    
               $i = false;

	    if($delivery_no)
	    {
	    $method = 'Z036_ESEAL_GET_DELIVERY_DETAIL_SRV'; 
		$method_name ='DELIVER_DETAILS';
		$data =['DELIVERY'=>$delivery_no];
		$documentTag = 'D:GET_DELIVER';
		$materialTag = 'MATERIAL_CODE';
		$serialNoTag = 'NO';
	    }
	    else{

	    	if($purchase_no){
	    		$method = 'Z0049_GET_PO_DETAILS_SRV';
		        $method_name = 'PURCHASE';
		        $data =['PO'=>$purchase_no];
		        $documentTag = 'D:GET_PO';       
		        $materialTag = 'MAT_CODE';
		        $serialNoTag = 'NO';
		        
	    	}
	    	else{
	    		if($grn_no){
                    $method = 'Z029_ESEAL_GET_GRN_DATA_SRV';
				    $method_name = 'GRN_OUTPUT';
				    $data =['DOCUMENT'=>$grn_no];
				    $documentTag = 'D:GET_GRN';       
		            $materialTag = 'MATERIAL_CODE';
		            $serialNoTag = 'SNO';
	    		}
	    		else{
	    		throw new Exception('Please pass document no.');
	    	}
	    	
	    	}

	    }
	    
        if(empty($ids) || (empty($delivery_no) && empty($purchase_no) && empty($grn_no)))
            throw new Exception('Parameters Missing');


        $explodeIds = explode(',',$ids);
        $explodeIds = array_unique($explodeIds);
        $idsCnt = count($explodeIds);

        $systemCnt = DB::table('eseal_'.$mfgId)->whereIn('primary_id',$explodeIds)->count();

        if($idsCnt != $systemCnt)
        	throw new Exception('Ids count not matching');

	              
				  //SAP call for getting Document Details.
				  $response =  $this->sapCall($mfgId,$method,$method_name,$data,Input::get('access_token'),true);
				  Log::info('GET DOCUMENT SAP response:-');
				  Log::info($response);
				  
				  $response = json_decode($response);
		    if($response->Status){
					$response =  $response->Data;

					$parseData1 = xml_parser_create();
					xml_parse_into_struct($parseData1, $response, $documentValues1, $documentIndex1);
					xml_parser_free($parseData1);
					$documentData = array();
					foreach ($documentValues1 as $data) {
						if(isset($data['tag']) && $data['tag'] == $documentTag)
						{
							$documentData = $data['value'];
						}
					}
					if(empty($documentData)){
					   throw new Exception('Error from ERP call');
					 }

					$deXml = simplexml_load_string($documentData);
					$deJson = json_encode($deXml);
					$xml_array = json_decode($deJson,TRUE);
					Log::info('GET DOCUMENT array response:-');
					Log::info($xml_array);
					$data = $xml_array['DATA']['ITEMS'];
					
					if(!array_key_exists($serialNoTag, $data['ITEM'])){
                        foreach($data['ITEM'] as $data1){                                                               
                        	$uomArray[$data1[$materialTag]] = ['qty'=>round($data1['QUANTITY'],2),'uom'=>$data1['UOM']];
                        }
					}
					else{
						$data2 = $data['ITEM'];                                                
						$uomArray[$data2[$materialTag]] = ['qty'=>round($data2['QUANTITY'],2),'uom'=>$data2['UOM']];

					}

                    Log::info('UOM ARRAY');
             		Log::info($uomArray);

                  foreach($uomArray as $key => $value){
                       $systemMaterials = array();
 
             		$query = DB::table('eseal_'.$mfgId.' as es')
             		            ->join('products as pr','pr.product_id','=','es.pid')             		            
             		            ->where(function($query) use($explodeIds){
													 $query->whereIn('es.parent_id', $explodeIds)
														   ->orWhereIn('es.primary_id',$explodeIds);
											 }
											 )
             		            ->where(['es.level_id'=>0,'material_code'=>$key]);

             		     if($value['uom'] == 'M'){
             		        $systemMaterials = $query->groupBy('es.pid')->get([DB::raw('sum(pkg_qty) as qty'),'pr.material_code']);
             		        }
             		        else{
             		        $systemMaterials = $query->groupBy('es.pid')->get([DB::raw('CASE WHEN multiPack=0 THEN count(eseal_id) ELSE sum(pkg_qty) END AS qty'),'pr.material_code']);	
             		        }

                         if($systemMaterials)
                         	$systemArray[$key] = ['qty'=>$systemMaterials[0]->qty,'uom'=>$value['uom']];


                         //////////////////////output formatt///////////////////////////


                             if($value['uom'] == 'M'){
                                         $query = 'select es.primary_id, pkg_qty AS qty,pr.material_code,product_id as pid,pr.name
                                    from eseal_'.$mfgId.' as es join products pr on pr.product_id=es.pid and material_code="'.$key.'" and es.primary_id in ('.implode(',',$explodeIds).')';
                                    }
                                  else{ 
                                     $query = 'select es.primary_id, cast((
                                    SELECT  CASE when multiPack=0
                                             then
                                              case when COUNT(es1.primary_id) = 0 then 1 else COUNT(es1.primary_id) end
                                             else
                                              case when COUNT(es1.primary_id) = 0 then es.pkg_qty else sum(es1.pkg_qty) end
                                             end
                                    FROM eseal_'.$mfgId.' es1
                                    WHERE es1.parent_id=es.primary_id) as UNSIGNED) AS qty,pr.material_code,product_id as pid,pr.name
                                    from eseal_'.$mfgId.' as es join products pr on pr.product_id=es.pid and material_code="'.$key.'" and es.primary_id in ('.implode(',',$explodeIds).')';
                                       }

                                    $iotScanned = DB::select($query);
                                   
                                   if($iotScanned){
                                    foreach($iotScanned as $iot){
                                    	$materialArray[] = ['iot'=>(string)$iot->primary_id,'qty'=>(int)$iot->qty,'material_code'=>$iot->material_code,'pid'=>(int)$iot->pid,'name'=>$iot->name];
                                    }
                                   }
                         //////////////////////output formatt///////////////////////////


             		        }
             		                    
                      
                    Log::info('SYSTEM ARRAY');
             		Log::info($systemArray);

             		if(count($uomArray) != count($systemArray))
             		    throw new Exception('The line items count is not matching.');

             		if($uomArray != $systemArray)
             			throw new Exception('The line items are not matching.');

             }
             else{
             	throw new Exception($response->Message);
             }		

  	}
  	catch(Exception $e){  		
  		$status =0 ;
  		$message = $e->getMessage();  		
  	}
  	Log::info(['Status'=>$status,'Message'=>$message,'Material'=>$materialArray]);
  	return json_encode(['Status'=>$status,'Message'=>$message,'Material'=>$materialArray]);
  }

   
             public function getStorageLocations(){
     	try{
     		Log::info(__FUNCTION__.' : '.print_r(Input::get(),true));
     		$result = array();
     		$status =1;
     		$message = 'Data successfully retrieved';  
     		$is_moment_type = (Input::get('is_momentType')==1) ? true : false;   		
     		$locationId = $this->roleAccess->getLocIdByToken(Input::get('access_token'));
     		$mfgId = $this->roleAccess->getMfgIdByToken(Input::get('access_token'));
     		$storageLocType = Loctype::where(['manufacturer_id'=>$mfgId,'location_type_name'=>'Storage Location'])->pluck('location_type_id');

     		if(!$storageLocType)
     			throw new Exception('Storage Location Type doesnt exist for the manufacturer');

            $result['storage_location'] = Location::where(['manufacturer_id'=>$mfgId,'location_type_id'=>$storageLocType,'parent_location_id'=>$locationId])->get(['erp_code','location_name','location_id']);
            //echo $result;
            Log::info($result);

            if($is_moment_type)
            {

            	Log::info('moment type=>'."\n\r");
            	//$results = array();
            	$movement_type = DB::table('lookup_categories as lc')
            					 ->select('ml.value as code','ml.name as description')
            					 ->join('master_lookup as ml','lc.id','=','ml.category_id')
            					 ->whereIn('lc.name',['eseal_SAP_MT_STR','eseal_SAP_MT_TBS','eseal_SAP_MT_TUS','eseal_SAP_MT_TRUR','eseal_SAP_MT_ICCR','eseal_SAP_MT_IACR','eseal_SAP_MT_MTR','eseal_SAP_MT_MCR'])
            					 ->where('ml.is_active',1)
            					 ->get();
            	//print_r($movement_type);	
            	
            	//print_r($result['storage_location'] ); 
            	$result['movement_type'] = $movement_type;
            	//print_r($result); 

            	//$result = json_encode($results);
            	//$result = $results;			 
            	 Log::info($result);				 
            } 
            if(empty($result['storage_location']))
            	throw new Exception('There are no storage locations under this location');
     	}
     	catch(Exception $e){
     		$status =0;
     		$message = $e->getMessage();
     	}
        Log::info(['Status'=>$status,'Message'=>$message,'Data'=>$result]);
     	return json_encode(['Status'=>$status,'Message'=>$message,'Data'=>$result]);
     }


       public function getProductsByMfg(){
   	try{
   		$status =1;
   		$message = 'Data retrieved successfully';
        $mfgId = $this->roleAccess->getMfgIdByToken(Input::get('access_token'));

        $data = Products::where(['manufacturer_id'=>$mfgId,'product_type_id'=>8003])->get(['product_id','name','description','material_code']);

        if(empty($data))
        	throw new Exception('There are no products configured for this manufacturer.');
   	}
   	catch(Exception $e){
   		$status =0;
   		$message =$e->getMessage();
   	}
   	Log::info(['Status'=>$status,'Message'=>$message,'Data'=>$data]);
   	return json_encode(['Status'=>$status,'Message'=>$message,'Data'=>$data]);
   }


   public function unblockDeliveryIot(){

    try{
    	Log::info(__FUNCTION__.' : '.print_r(Input::get(),true));
    	$status =1; 
    	$message = 'The IOT\'s in the delivery are un-blocked successfully';
    	$delivery_no = trim(Input::get('delivery_no'));    	
    	$isSalesPo = Input::get('isSalesPo');
    	$tp = trim(Input::get('tp'));
    	$mfgId = $this->roleAccess->getMfgIdByToken(Input::get('access_token'));        
        $i = 0;
        $iots = array();

    	
    	if(empty($delivery_no)){    		
    		  if(!$tp)
    			throw new Exception('Please pass TP');

    		$delivery_no = $tp;
    	}    	

            $sql = 'select status,input,message from api_log where match (input) against('.$delivery_no.') and api_name="SyncStockOut" order by id desc';
            $result = DB::select($sql);            

            if(empty($result))
               throw new Exception('There is no delivery record.');
            
            foreach($result as $res){
            
            $inputArray = unserialize($res->input);                        
            log::info($inputArray);                        

             if($tp){
            $logDeliveryNo = $inputArray['codes'];
            }  
            else{
            $logDeliveryNo = $inputArray['delivery_no'];
            }
            
            

            if(trim($logDeliveryNo) ==  $delivery_no)
               	break;
            
            
            $i++;  
            }
            
            
            if($i == count($result))
            	throw new Exception('There is no delivery record.');

            if($res->status == 1)
            	throw new Exception('The delivery record is already synced to server.');

            $ids = $inputArray['ids'];
            $ids = explode(',',$ids);            

       DB::beginTransaction();     
            
            $query = DB::table('eseal_'.$mfgId)                              
                              ->where(function($query) use($ids){
							    $query->whereIn('primary_id', $ids)
									  ->orWhereIn('parent_id',$ids);
							   }
							   )							
							  ->where('is_active',0);

			$iots = $query->get(['primary_id as iot',DB::raw('case when level_id=0 then "mono-carton" else "carton" end as package')]);	                                  

			$query->update(['is_active'=>1]);
       
       DB::commit();
       
    }
    catch(Exception $e){
    	DB::rollback();
    	$status =0;
    	$message = $e->getMessage();    	
    }
    Log::info(['Status'=>$status,'Message'=>$message,'Iots'=>$iots]);
    return json_encode(['Status'=>$status,'Message'=>"Server:".$message,'Iots'=>$iots]);

   }   


public function postAutoGrn(){

    try{
    	Log::info(__FUNCTION__.' : '.print_r(Input::get(),true));
    	$status =1;
    	$message = 'TP received successfully';
    	$i =0;
    	$isStockOut = false;
    	$updateStorLoc= true;
    	$delivery_no = trim(Input::get('delivery_no'));    	
    	$mfgId = $this->roleAccess->getMfgIdByToken(Input::get('access_token'));
    	$esealTable = 'eseal_'.$mfgId;
    	$isSalesPo = trim(Input::get('isSalesPo'));
        $batch = trim(Input::get('batch'));
        $tp = trim(Input::get('tp'));
        $grn = trim(Input::get('grn'));                       

    	
    	if(empty($delivery_no)){    		
    		  if(!$tp)
    			throw new Exception('Please pass TP');

    		$delivery_no = $tp;
    	}    	

    	if(empty($grn))
    		throw new Exception('Please pass grn no');

    	

    	DB::beginTransaction();

        
            $sql = 'select input,message,status from api_log where match (input) against("'.$delivery_no.'") and api_name="ReceiveByTp" order by id desc';
            $result = DB::select($sql);

            if(empty($result)){
            	$sql = 'select input,message,status from api_log where match (input) against("'.$delivery_no.'") and api_name="SyncStockOut" order by id desc';
                $result = DB::select($sql);

            if(empty($result))
               throw new Exception('There is no STO delivery record for proceeding to GRN');                                             

              $isStockOut = true;
            }

            
            foreach($result as $res){
            
            $inputArray = unserialize($res->input);
                        
            log::info($inputArray);
            
         if($tp){
          if(!$isStockOut)	
             $logDeliveryNo = $inputArray['tp'];
          else
             $logDeliveryNo = $inputArray['codes'];
        }
        else{
            $logDeliveryNo = $inputArray['delivery_no'];
        }
            

            if(trim($logDeliveryNo) ==  $delivery_no)
               	break;
            
            
            $i++; 

            }
            
            
            if($i == count($result))
            	throw new Exception('There is no delivery record for GRN');

            
            if(!$isStockOut){
              if($res->status == 1)
            	throw new Exception('The delivery record is already synced to system and passed.');
            }
            else{
              if($res->status == 0)
                throw new Exception('The dispatch record is still not synced to system');   
            }
            
            	
        if(!$isStockOut){
            $module_id= $inputArray['module_id'];
            $access_token = $inputArray['access_token'];                        
            $location_id = $inputArray['location_id'];                                    
            $transition_id = $inputArray['transition_id'];
            $transition_time = $inputArray['transition_time'];
            $tp = $inputArray['tp'];
        }
        else{        	
        	$location_id = $inputArray['destLocationId'];       
        	$creds = DB::table('users as u')
        	                   ->join('users_token as ut','ut.user_id','=','u.user_id')
        	                   ->where(['location_id'=>$location_id,'customer_id'=>$mfgId])
        	                   ->get(['ut.access_token','ut.module_id']);        	                            
        	    if(empty($creds))               
        	    	throw new Exception('Unable to find user access at receiving location');

        	$access_token = $creds[0]->access_token;                   
        	$module_id = $creds[0]->module_id;
        	$transition_id = Transaction::where(['manufacturer_id'=>$mfgId,'name'=>'receive'])->pluck('id');
        	$transition_time = $this->getDate();
        	$tp = $inputArray['codes'];
        }


            if(!empty($batch)){

            	$stoCnt = DB::table($this->TPAttributeMappingTable)
            	            ->where('tp_id',$tp)
            	            ->where('attribute_name','Document Number')
            	            ->count('id');
                if($stoCnt)
                    throw new Exception('The batch cannot be updated as the document is a STO/SALES PGI');

            	if(empty($batch))
            		throw new Exception('The batch parameter is empty');
            	

            	$track_id = DB::table($this->trackHistoryTable)->where('tp_id',$tp)->pluck('track_id');

            	DB::table($esealTable)
            	      ->where(['track_id'=>$track_id,'level_id'=>0])
            	      ->update(['batch_no'=>$batch]);

            }


            	DB::table($this->TPAttributeMappingTable)
            	            ->where('tp_id',$tp)
            	            ->whereIn('attribute_name',['Document Number','Purchase Order No'])
            	            ->update(['reference_value'=>$grn]);


                $stoTrack = Trackhistory::where(['tp_id'=>$tp,'dest_loc_id'=>$location_id])->pluck('track_id');
				$pids = DB::table($esealTable.' as es')				                 
				                 ->where(['track_id'=>$stoTrack,'level_id'=>0])
				                 ->distinct()
				                 ->lists('pid');

				  foreach($pids as $pid) {
				      $business_unit_id = Products::where('product_id',$pid)->pluck('business_unit_id');
                      $store_location = Location::where(['parent_location_id'=>$location_id,'business_unit_id'=>$business_unit_id,'storage_location_type_code'=>25001])->pluck('erp_code');  	
                      DB::table($esealTable)
                         ->where(['track_id'=>$stoTrack,'pid'=>$pid])
                         ->update(['storage_location'=>$store_location]);
				  }				

            
                $request = Request::create('scoapi/ReceiveByTp', 'POST', array('module_id'=>$module_id,
                	'access_token'=>$access_token,'location_id'=>$location_id,'transition_time'=>$transition_time,                	
                	'transition_id'=>$transition_id,'tp'=>$tp));                

				$originalInput = Request::input();//backup original input
				Request::replace($request->input());
				$response = Route::dispatch($request)->getContent();
				$response = json_decode($response,true);

				if($response['Status'] == 0 )
				      throw new Exception($response['Message']);



      DB::commit();
       
    }
    catch(Exception $e){
    	DB::rollback();
    	$status =0;
    	$message = $e->getMessage();    	
    }
    Log::info(['Status'=>$status,'Message'=>$message]);
    return json_encode(['Status'=>$status,'Message'=>"Server:".$message]);

   }


public function updateBatch(){

    try{
    	Log::info(__FUNCTION__.' : '.print_r(Input::get(),true));
    	$status =1;
    	$message = 'The batch is updated for the IOT\'s';
    	$ids = Input::get('ids');
    	$batch = trim(Input::get('batch'));
    	$po_no = trim(Input::get('po_number'));
    	$grn = trim(Input::get('grn'));
    	$mfgId = $this->roleAccess->getMfgIdByToken(Input::get('access_token'));        
    	$esealTable = 'eseal_'.$mfgId;


    	if(empty($ids) && empty($po_no))
    		throw new Exception('Parameters missing');

    	if(empty($batch))
    		throw new Exception('Batch parameter is not passed');


        DB::beginTransaction();

    	if($ids){

    	  $idArray =  explode(',',$ids);

    	  $existCnt = DB::table($esealTable)
    	                 ->whereIn('primary_id',$idArray)
    	                 ->count('eseal_id');

    	     if($existCnt == 0)            
    	     	throw new Exception('ALL the IOT\'s are not binded');

    	     if($existCnt != count($idArray))
    	     	throw new Exception('Some of the IOT\'s are not binded');

          DB::table($esealTable)
                  ->where(function($query) use($idArray){
					   $query->whereIn('parent_id',$idArray)
		                     ->orWhereIn('primary_id',$idArray);
											 })
				  ->where('level_id',0)
				  ->update(['batch_no'=>$batch]);


    	}
    	else{
    		
    		if(empty($po_no))
    			throw new Exception('Batch update needs either Production Order or IOT\'s');

    		$confirmCnt = DB::table($esealTable)->where('po_number',$po_no)->max('is_confirmed');

    		if(is_null($confirmCnt))
    			throw new Exception('There is no production order');

    		DB::table($esealTable)
   				  ->where(['level_id'=>0,'po_number'=>$po_no,'is_confirmed'=>0])
				  ->update(['batch_no'=>$batch,'is_confirmed'=>$confirmCnt+1,'reference_value'=>$grn]);

    	}
        

    	DB::commit();
    	
       
    }
    catch(Exception $e){
    	DB::rollback();
    	$status =0;
    	$message = $e->getMessage();    	
    }
    Log::info(['Status'=>$status,'Message'=>$message]);
    return json_encode(['Status'=>$status,'Message'=>"Server:".$message]);

   }
    
  
public function invoiceUpdate(){

    try{
    	Log::info(__FUNCTION__.' : '.print_r(Input::get(),true));
    	$status =1;
    	$message = 'The Tp is updated Successfully';
    	$tp = Input::get('tp_id');
    	$purchaseNumber = Input::get('purchase_no');
    	$invoiceNumber = Input::get('invoice_number');
    	$docNumber = Input::get('doc_number');    	
    	$vehicleNumber = Input::get('vehicle_number');
    	$mfgId = $this->roleAccess->getMfgIdByToken(Input::get('access_token'));    	
    	$esealTable = 'eseal_'.$mfgId;
        //dd($query);
       $result1='';
       $result2='';
       $result3='';
     //  $i=0;
    	if(empty($invoiceNumber) && empty($vehicleNumber))
    		throw new Exception('Invoice and Vehicle Number Not Passed');

    	if(empty($tp) && empty($docNumber))
    		throw new Exception('TP and DocNumber not passed');



        DB::beginTransaction();

        if(!empty($docNumber))
    	{
    		$tp = DB::table('tp_attributes')->where(['attribute_name'=>'Document Number','value'=>$docNumber])->pluck('tp_id');
           //dd($tp);
    		if(!$tp)
    			throw new Exception('In-valid document number');
    	}

    

    	if(!empty($invoiceNumber)){
          $invoiceNot = DB::table('tp_attributes')->where(['tp_id'=>$tp,'attribute_name'=>'Invoice Number'])->pluck('tp_id');
          if(!empty($invoiceNot)){

         $result1 = DB::table('tp_attributes')->where(['tp_id'=>$tp,'attribute_name' =>'Invoice Number'])->update(['value'=>$invoiceNumber]);
         }
    	  else
    	  	throw new Exception('Invoice Number Not exist for TP');
    	  
    	 }

    	 if(!empty($vehicleNumber)){
    	 $vehicleNot = DB::table('tp_attributes')->where(['tp_id'=>$tp,'attribute_name'=>'Vehicle No'])->pluck('tp_id');
    	 
    	 if(!empty($vehicleNot)){
           $result2 = DB::table('tp_attributes')->where(['tp_id'=>$tp,'attribute_name' =>'Vehicle No'])->update(['value'=>$vehicleNumber]);
    	  }
    	  else
    	  	throw new Exception('Vehicle Number Not exist for TP');
    	 }

    	 if(!empty($purchaseNumber)){
    	 $purchaseNot = DB::table('tp_attributes')->where(['tp_id'=>$tp,'attribute_name'=>'Purchase Order No'])->pluck('tp_id');
    	 
    	 if(!empty($purchaseNot)){
    	   $tpDetails = DB::table($this->trackHistoryTable)
    	                        ->where('tp_id',$tp)
    	                        ->orderBy('track_id','desc')
    	                        ->take(1)
    	                        ->get(['src_loc_id','dest_loc_id']);
    	      if(empty($tpDetails))                  
    	      	throw new Exception('Record missing for TP');

    	      if($tpDetails[0]->dest_loc_id == 0)
    	      	throw new Exception('TP is already received');

    	      /////fetching sales po info from ERP///////

               $data =['PO'=>$purchaseNumber];

                    $method = 'Z0049_GET_PO_DETAILS_SRV';
		            $method_name = 'PURCHASE';
				
					  //SAP call for getting Delivery Details.
					  $response =  $this->sapCall($mfgId,$method,$method_name,$data,Input::get('access_token'),true);
					  Log::info('GET PURCHASE ORDER DETAILS SAP response:-');
					  Log::info($response);
					  
					  $response = json_decode($response);
					  
					  if(!$response->Status)
					  	throw new Exception($response->Message);

						$response =  $response->Data;

						$parseData1 = xml_parser_create();
						xml_parse_into_struct($parseData1, $response, $documentValues1, $documentIndex1);
						xml_parser_free($parseData1);
						$documentData = array();
						foreach ($documentValues1 as $data) {
							if(isset($data['tag']) && $data['tag'] == 'D:GET_PO')
							{
								$documentData = $data['value'];
							}
						}
						if(empty($documentData)){
						   throw new Exception('Error from ERP call');
						 }

						$deXml = simplexml_load_string($documentData);
						$deJson = json_encode($deXml);
						$xml_array = json_decode($deJson,TRUE);
						Log::info('GET PURCHASE ORDER DETAILS array response:-');
						Log::info($xml_array);

						if($xml_array['HEADER']['Status'] == 0)
							throw new Exception($xml_array['HEADER']['Message']);


						

						$data = $xml_array['DATA']['ITEMS'];
						$srcLocationErp = ltrim($xml_array['DATA']['VENDOR'],0);
					
					if(!array_key_exists('NO', $data['ITEM'])){
                        foreach($data['ITEM'] as $data1){                                                        
                        	$uomArray[] = $data1['MAT_CODE'];
                        	$destLocationErp = $data1['PLANT'];
                        }
					}
					else{
						$data2 = $data['ITEM'];                                              
						$uomArray[] = $data2['MAT_CODE'];
						$destLocationErp = $data2['PLANT'];

					}
                    $uomArray = array_unique($uomArray);
                    Log::info('UOM ARRAY');
             		Log::info($uomArray);

             		
    	      //////end of info/////////////////////////

              
             		 $destLocationId = DB::table($this->locationsTable)
             		                      ->where(['manufacturer_id'=>$mfgId,'erp_code'=>$destLocationErp])
             		                      ->pluck('location_id');

                     $srcLocationId = DB::table($this->locationsTable)
             		                      ->where(['manufacturer_id'=>$mfgId,'erp_code'=>$srcLocationErp])
             		                      ->pluck('location_id');             		                      

             		   if($tpDetails[0]->src_loc_id != $srcLocationId)
             		     throw new Exception('The vendor location doesn\'t match with the existing location');

                       if($tpDetails[0]->dest_loc_id != $destLocationId)
             		     throw new Exception('The destination location doesn\'t match with the existing location');

    	      $scannedMaterials = DB::table($this->tpDataTable.' as tp')
             		                       ->join($esealTable.' as es','es.primary_id','=','tp.level_ids')
             		                       ->join('products as pr','pr.product_id','=','es.pid')
             		                       ->where('tp.tp_id',$tp)
             		                       ->distinct()
             		                       ->lists('pr.material_code');

             	foreach($scannedMaterials as $material){
             		if(!isset($uomArray[$material]))
             			throw new Exception('Material '.$material.' is missing in the document');	                       
             	}	                                    	
    	      	

           $result3 = DB::table('tp_attributes')->where(['tp_id'=>$tp,'attribute_name' =>'Purchase Order No'])->update(['value'=>$purchaseNumber]);
    	  }
    	  else
    	  	throw new Exception('Purchase Number Not exist for TP');
    	 }

    	 if(!$result1 && !$result2 && !$result3)
    	 	throw new Exception('No records found for update');
    	  
    	DB::commit();
    	
       
    }
    catch(Exception $e){
    	DB::rollback();
    	$status =0;
    	$message = $e->getMessage();    	
    }
    Log::info(['Status'=>$status,'Message'=>$message]);
    return json_encode(['Status'=>$status,'Message'=>'Server: '.$message]);

   }     

public function mergeTPs(){
	try{
		DB::beginTransaction();
		$status = 1;
		$message = "Tp's Merged Successfully";
		$tp = '';
		$mfgId = $this->roleAccess->getMfgIdByToken(Input::get('access_token'));
    	$esealTable = 'eseal_'.$mfgId;
    	//$esealBankTable = 'eseal_bank_'.$mfgId;
		$tps = Input::get('tps');
		if(empty($tps)){
			throw new Exception("Please pass the tps parameter");
		}
		$tps = explode(',',$tps);
		if(count($tps) == 1){
			throw new Exception("Please pass atleast two TP's for merging");
		}
		if(count($tps) > 5){
			throw new Exception("Maximum 5 TP's are alowed to merge");
		}
		$tps_not_exists = [];
		$already_rec_tps = [];
		$tps_deleted = [];
		$src_loc_ids = [];
		$dest_loc_ids = [];
		foreach($tps as $t){
			$tpDetails = DB::table('track_history')->where('tp_id',$t)->get();
			//$count = count($tpDetails);
			if(!$tpDetails){
				$tps_not_exists[] = $t;
			}
			else{
				foreach($tpDetails as $td){
					$src_loc_ids[] =$td->src_loc_id; 
					$dest_loc_ids[] = $td->dest_loc_id;
					if($td->dest_loc_id == 0){
						$already_rec_tps[] = $t;
					}
				}

			}
				
		}
		if(!empty($tps_not_exists)){
			$tp = implode(',',$tps_not_exists);
			throw new Exception("These Tps does not Exists");
		}
		if(!empty($already_rec_tps)){
			$tp = implode(',',$already_rec_tps);
			throw new Exception("These Tps are already received");
		}
		if((count(array_unique($src_loc_ids)) != 1) || (count(array_unique($dest_loc_ids)) != 1)){
			throw new Exception("All the given TP's does not belong to the same source or destination location");
		}

		// if(count(array_unique($dest_loc_ids)) != 1){
		// 	throw new Exception("All the given TP's does not belong to the same location");
		// }

		foreach($tps as $t){
			$tp_track_details = DB::table('track_details')->where('code',$t)->get();
			if(!$tp_track_details){
				$tps_deleted[] = $t;
			}		
		}
		if(!empty($tps_deleted)){
			$tp = implode(',',$tps_deleted);
			throw new Exception("The TPs are deleted");
		}

		


		// $tp_details = DB::table('track_history')->whereIn('tp_id',$tps)->get();	
		// if(!$tp_details){
		// 	throw new exception("No data found agaisnt these TPs");
		// }
		// if(count($tp_details) < count($tps)){
		// 	throw new Exception("Some of the TPs does not exist");
		// }
		// if(count($tp_details) > count($tps)){
		// 	throw new Exception("Some of the TPs are received");
		// }

		// $i =1;
		// foreach($tp_details as $tp){
		// if($i== 1){
		// 	$presrc = $tp->src_loc_id;
		// 	$predest = $tp->dest_loc_id;
		// 	$i = 2;		
		// }
		// if($tp->dest_loc_id == 0){
		// 	throw new Exception("Some of the TP's are already received");
		// }

		// 	if(!($tp->src_loc_id == $presrc and $tp->dest_loc_id == $predest)){
		// 		throw new Exception("All the TP's does not belong to the same source or destination location");
		// 	}

		// }

		$values = DB::table('tp_attributes')->whereIn('tp_id',$tps)->whereIn('attribute_name',['Purchase Order No','Document Number'])->lists('value');
		//dd($values);die;
		if(empty($values)){
			throw new Exception("No attributes configured for these TPs");
		}
		if(count($values) !=count($tps)){
			throw new Exception("Some of the Tp's does not have the required attributes");
		}
		if(count(array_unique($values)) > 1 ){
			throw new Exception("Po numbers does not match");
		}
		$tp_tracks = DB::table('track_history')->whereIn('tp_id',$tps)->lists('tp_id','track_id');
		$tracks = array_keys($tp_tracks);
		$track = $tracks[0];
		$tp = $tp_tracks[$track];

		//DB::table('track_history')->whereIn('tp_id',$tps)->delete();
		
		DB::table('track_details')->whereIn('track_id',$tracks)->update(['track_id'=>$track]);
		DB::table('track_details')->whereIn('code',$tps)->where('code','!=',$tp)->delete();
		DB::table($esealTable)->whereIn('track_id',$tracks)->update(['track_id'=>$track]);
		DB::table('tp_data')->whereIn('tp_id',$tps)->update(['tp_id'=>$tp]);
		DB::table('tp_pdf')->whereIn('tp_id',$tps)->where('tp_id','!=',$tp)->delete();
		$merged_tps = array_diff($tps, array($tp));
		$merged_tps = implode(",",$merged_tps);
		DB::table('merged_tps')->insert([
			'document_no'=>$values[0],
			'present_tp'=>$tp,
			'merged_tps'=>$merged_tps
			]);
		DB::commit();
	}catch(Exception $e){
		DB::rollback();
		$status = 0;
		$message = $e->getMessage();
		//$tp ='';

	}
	return json_encode(["Status"=>$status,"Message"=>"Server:".$message,"Tp"=>$tp]);
}

public  function modelInterchange(){
	try{
		$status = 1;
		$message = "Models are interchanged successfully";
		$invalids= [];
		$mfgId = $this->roleAccess->getMfgIdByToken(Input::get('access_token'));
		$location_id = $this->roleAccess->getLocIdByToken(Input::get('access_token'));
    	$esealTable = 'eseal_'.$mfgId;
		$esealBankTable= 'eseal_bank_'.$mfgId;

    	$iots =  Input::get('iots');

    	if(empty($iots)){
    		throw new Exception("IOTs should not be empty");
    	}

    	$iots = explode(',',$iots);
    	$new_po = Input::get('new_po');
    	$new_material = trim(Input::get('new_material'));
    	$updatePO = 0;

    	
		$valids = DB::table($esealBankTable)->whereIn('id',$iots)->lists('id');
		$invalids  = array_values(array_diff($iots,$valids));
		if($invalids){
			//$invalid_iots = implode(',',$invalids);
			throw new Exception("Some of the IOTs are Invalid");
		}	
		
    	if(empty($new_material)){
    		throw new Exception("new_material parameter should not be empty");
    	}
    	$primaries = DB::table($esealTable)->whereIn('primary_id',$iots)->where(function($query){
					$query->where('parent_id',0);
					$query->orWhere('parent_id',null);
				})->where('level_id',0)->count();
    	$cartons = DB::table($esealTable)->whereIn('primary_id',$iots)->where(function($query){
					$query->where('parent_id',0);
					$query->orWhere('parent_id',null);
				})->where('level_id',1)->count();
    	if($cartons  == 0 and (count($iots) == $primaries)){
    		$level = 0;
    		$count = $primaries;
    	}else if($primaries == 0 and (count($iots) == $cartons)){
    		$level = 1;
    		$count = DB::table($esealTable)->whereIn('primary_id',$iots)->where(function($query) use($iots){
					$query->whereIn('primary_id',$iots);
					$query->orWhereIn('parent_id',$iots);
				})->count();
    	}else{
    		throw new Exception("Please pass either baby carton or parent carton IOTs");
    	}

    	$scrappedCount = DB::table($esealTable)->whereIn('primary_id',$iots)->where(function($query) use($iots,$level){
					$query->whereIn('primary_id',$iots);
					if($level){
						$query->orWhereIn('parent_id',$iots);	
					}
					
				})->where('is_active',0)->count();
    	if($scrappedCount){
    		throw new Exception("Some of the IOTs are blocked");
    	}
    	
    	$old_pids = DB::table($esealTable)->where(function($query) use($iots,$level){
					$query->whereIn('primary_id',$iots);
					if($level)
						$query->orWhereIn('parent_id',$iots);
				})->lists('pid');
    	if(count(array_unique($old_pids)) > 1){
    		throw new Exception("The IOTs are binded with more than one material");
    	}

    	$uom_unit_val_old = DB::table('products')->where('product_id',$old_pids[0])->pluck('uom_unit_value');
    	$old_pos = DB::table($esealTable)->where(function($query) use($iots,$level){
					$query->whereIn('primary_id',$iots);
					if($level){
						$query->orWhereIn('parent_id',$iots);	
					}
					
				})->where('level_id','=',0)->lists('po_number');
    	if(count(array_unique($old_pos)) > 1){
    		throw new Exception("The IOTs are binded with two or more pos");
    	}
    	$attribute_sets = DB::table($esealTable)->where(function($query) use($iots,$level){
					$query->whereIn('primary_id',$iots);
					if($level == 1)
					$query->orWhereIn('parent_id',$iots);
				})->lists('attribute_map_id');
    	$attribute_mapping_sets = array_unique($attribute_sets);
    	//for checking if the all the IOTs are packed at the same location or not
    	$locations = DB::table('attribute_mapping')->whereIn('attribute_map_id',$attribute_mapping_sets)->lists('location_id');
    	if(count(array_unique($locations))>1){
    		throw new Exception("All the IOts does not belongs to the same location");
    	}
    	$location = $locations[0];


    	$attribute_set_count =  DB::table($esealTable)->whereIn('attribute_map_id',$attribute_mapping_sets)->count();
    	$new_product = DB::table('products')->where(['material_code'=>$new_material])->first();
    	if(!$new_product){
    		throw new Exception("The given Material does not exists");
    	}
    	if($new_product->uom_unit_value != $uom_unit_val_old){
    		throw new Exception("Packaging quantity does not match for the given material");
    	}
    	$attribute_map_set_id = $attribute_mapping_sets[0];
    	if($attribute_set_count != $count){
    		$attri = DB::table('attribute_mapping as am')->join('attributes as a','a.attribute_id','=','am.attribute_id')->where('am.attribute_map_id',$attribute_mapping_sets[0])->lists('value','attribute_code');
    		$attri = json_encode($attri);

    		$request = Request::create('scoapi/SaveBindingAttributes', 'POST', array('module_id'=> 
			Input::get('module_id'),'access_token'=>Input::get('access_token'),'attributes'=>$attri,'lid'=>$location_id,'pid'=>$new_product->product_id));
			$originalInput = Request::input();//backup original input
			Request::replace($request->input());
			$res1 = Route::dispatch($request)->getContent();//invoke API
			$res1 = json_decode($res1);
			$attribute_map_set_id = 0;
			if($res1->Status){						
				$attribute_map_set_id = $res1->AttributeMapId;
				$updateArray['attribute_map_id']= $attribute_map_set_id;
			}
			else{
				throw new Exception($res1->Message);
			}

    	}
    	else{
    		$attribute_map_set_id = $attribute_mapping_sets[0];
    	}
    	if(!empty($old_pos[0])){
    		//PO pcking;
    		$is_confirmed = DB::table($esealTable)->where(function($query)use($iots,$level){
					$query->whereIn('primary_id',$iots);
					if($level == 1)
					$query->orWhereIn('parent_id',$iots);
				})->where('is_confirmed',1)->count();
    		if($is_confirmed){
    			throw new Exception("Model cannot be interchanged due to some of the IOTs are already confirmed.");
    		}
    		if($new_po){
    			$objectcount  = DB::table('erp_objects')->where(['object_id'=>$new_po])->whereNotNull('response')->count();
    			if($objectcount ==0){
    				throw new Exception("New PO given is not still loaded into the eSeal System");
    			}

    			$updatePO = 1;
    			//$updateArray = ['pid'=>$new_product->product_id,'attribute_map_id'=>$attribute_map_set_id];
    		}
    		
    			$updateArray = ['pid'=>$new_product->product_id,'attribute_map_id'=>$attribute_map_set_id];
    		    		
    	}
    	else{
    		//Vendor Packing
    		if(!empty($new_po)){
    			throw new Exception("Po number can't be updated since IOTs are packed against Purchase Order");
    		}
    		$updateArray = ['pid'=>$new_product->product_id,'attribute_map_id'=>$attribute_map_set_id];
    	}

    	//$new_product = DB::table('products')->where(['material_code'=>$new_material])->first();

    	$material_code_attributeId = DB::table('attributes')->where('attribute_code','=','material_code')->take(1)->pluck('attribute_id');
    	$material_description_attributeId = DB::table('attributes')->where('attribute_code','=','material_description')->take(1)->pluck('attribute_id');
    	$mat_desc_attributeId = DB::table('attributes')->where('attribute_code','=','mat_desc')->take(1)->pluck('attribute_id');
    	$po_number_attributeId = DB::table('attributes')->where('attribute_code','=','po_number')->take(1)->pluck('attribute_id');
    	$model_attributeId = DB::table('attributes')->where('attribute_code','=','model')->take(1)->pluck('attribute_id');
    	DB::beginTransaction();
    	if(!empty($material_code_attributeId)){
    		DB::table('attribute_mapping')->where(['attribute_map_id'=>$attribute_map_set_id,'attribute_id'=>$material_code_attributeId])->update(['value'=>$new_material]);
    	}
    	if(!empty($material_description_attributeId)){
    		DB::table('attribute_mapping')->where(['attribute_map_id'=>$attribute_map_set_id,'attribute_id'=>$material_description_attributeId])->update(['value'=>$new_product->description]);	
    	}
    	if(!empty($mat_desc_attributeId)){
    		DB::table('attribute_mapping')->where(['attribute_map_id'=>$attribute_map_set_id,'attribute_id'=>$mat_desc_attributeId])->update(['value'=>$new_product->description]);
    	}
    	if(!empty($po_number_attributeId)){
    		DB::table('attribute_mapping')->where(['attribute_map_id'=>$attribute_map_set_id,'attribute_id'=>$po_number_attributeId])->update(['value'=>$new_po]);
    	}
    	if(!empty($model_attributeId)){
    		DB::table('attribute_mapping')->where(['attribute_map_id'=>$attribute_map_set_id,'attribute_id'=>$model_attributeId])->update(['value'=>$new_product->model_name]);
    	}

    	DB::table('attribute_mapping')->where(['attribute_map_id'=>$attribute_map_set_id])->update(['location_id'=>$location]);  	
    	DB::table($esealTable)->whereIn('primary_id',$iots)->orWhereIn('parent_id',$iots)->update($updateArray);
    	if($updatePO){
    		DB::table($esealTable)->where(function($query) use($iots,$level){
					$query->whereIn('primary_id',$iots);
					$query->orWhereIn('parent_id',$iots);
				})->where('level_id','=',0)->update(["po_number"=>$new_po]);	
    	}



    	//updating the Bind History for IOTS
    	if($level==1){
    		$childs = DB::table($esealTable)->whereIn('parent_id',$iots)->lists('primary_id');
    	}
    	else{
    		$childs = $iots;
    	}
    	//$allIots =  
    	DB::table('bind_history')->whereIn('eseal_id',$childs)->whereIn('attribute_map_id',$attribute_mapping_sets)->update(['attribute_map_id'=>$attribute_map_set_id]);
    	
    	//section1: for PO packing.

    	//section2: For vendor packing
    	DB::commit();
    }catch(Exception $e){
    	DB::rollback();
    	$status = 0;
    	$message =$e->getMessage();
    	$line = $e->getline();

    }
    return json_encode(["Status"=>$status,"Message"=>"Server:".$message,"invalid_iots"=>$invalids]);
}


public function superRetry()
	{
 $startTime = $this->getTime();
		try
		{
			$status = 0;
			$message = '';
			$locationId = Input::get('locationId');
			$ids = Input::get('ids');
			$loadChilds = Input::get('loadChilds');
			$productArray = array();
			$trackArray = array();
			$inHouseInventory = true;
			Log::info(__FUNCTION__ . ' : ' . print_r(Input::get(), true));
			if (empty($locationId) || !is_numeric($locationId))
			{
				throw new Exception('Pass valid numeric location Id');
			}
			if (empty($ids))
			{
				throw new Exception('Parameters missing');
			}
			$codes = explode(',',$ids);
			$codesCount = count($codes);
			$locationObj = new Locations\Locations();
			$mfgId = $locationObj->getMfgIdForLocationId($locationId);

			$childIds = Array();
			$childIds = $locationObj->getAllChildIdForParentId($locationId);
			if ($childIds)
			{
				array_push($childIds, $locationId);
			}
			$parentId = $locationObj->getParentIdForLocationId($locationId);
			$childIds1 = Array();
			if ($parentId)
			{
				$childIds1 = $locationObj->getAllChildIdForParentId($parentId);
				if ($childIds1)
				{
					array_push($childIds1, $parentId);
				}
			}
			$childsIDs = array_merge($childIds, $childIds1);
			$childsIDs = array_unique($childsIDs);
			if (count($childsIDs))
			{
				$locationId = implode(',', $childsIDs);
			}
			$esealTable = 'eseal_'.$mfgId;

			$ids = explode(',',$ids);
           
			if($loadChilds){
				$levels = DB::table($esealTable)->whereIn('primary_id',$ids)->distinct()->lists('level_id');
				if(empty($levels))
					throw new Exception('The IOTS are invalid');
			foreach($levels as $level){
		  if($level != 0){
		 	$childs = DB::table($esealTable)->whereIn('parent_id',$ids)->lists('primary_id');
		 	if(!empty($childs)){
		 		$ids = array_unique(array_merge($childs,$ids));
		 	}
		 }
		 else{
		 	$parents = DB::table($esealTable)->whereIn('primary_id',$ids)->where('parent_id','!=',0)->lists('parent_id');
		 	$childs = DB::table($esealTable)->whereIn('parent_id',$parents)->lists('primary_id');
		 	if(!empty($childs)){
		 		$ids = array_unique(array_merge($childs,$ids));
		 	}
		 }


		 }
		 }

			$location_type_id = DB::table('location_types')
			                                ->whereIn('location_type_name',['Plant','Depot','Warehouse','Supplier','Vendor'])
			                                ->where('manufacturer_id',$mfgId)
			                                ->lists('location_type_id');
           
            $ids = implode(',',$ids);
			
			$location_type_ids = implode(',',$location_type_id);


			$sql = 'select th.track_id,th.src_loc_id from track_history th join eseal_'.$mfgId.' es on es.track_id=th.track_id join locations on locations.location_id=th.src_loc_id and locations.location_type_id in ('.$location_type_ids.') and dest_loc_id=0 and es.primary_id in('.$ids.')';
		 
		 $result = DB::select($sql);
		 if(empty($result)){
	     log::info('Location Ids:-' .$locationId);

	             $result =   DB::table('track_history as th')
                                 ->join($esealTable.' as es','es.track_id','=','th.track_id')
                                 ->whereIn('th.src_loc_id',explode(',',$locationId))
                                 ->where('th.dest_loc_id',0)
                                 ->whereIn('primary_id',explode(',',$ids))
                                 ->get(['th.track_id','th.src_loc_id']);

         Log::info($result);
                                 
              if(empty($result))
			      throw new Exception('Data not-found');

			  $inHouseInventory = true;
		 }
		 
         $currentLocationId = $this->roleAccess->getLocIdByToken(Input::get('access_token'));

		foreach ($result as $res) {
         	$plant_location_id = $res->src_loc_id;

		 //if($currentLocationId == $plant_location_id)
		 //	$inHouseInventory = true;
         $trackArray[] = $res->track_id;
		 if(!in_array($plant_location_id,$childsIDs)){
            $inHouseInventory = false;		 	
            $trackArray1[] = $res->track_id;
        }        

        } 

		 $trackArray =  array_unique($trackArray);
		 //Log::info($trackArray);
		 $trackIds  = implode(',',$trackArray);  
         

					$sql = 'SELECT 
p. material_code AS matcode,
cast(p.group_id as UNSIGNED) as group_id,
CASE WHEN e.pid=0 THEN "Hetrogenious Item" WHEN e.pid=-1 THEN "Pallet" ELSE p.name END AS name,
IFNULL((select value as exp from attribute_mapping am where e.attribute_map_id=am.attribute_map_id and attribute_name="date_of_exp"),"") exp,
IFNULL((select value as exp_valid from attribute_mapping am where e.attribute_map_id=am.attribute_map_id and attribute_name="exp_valid"),"0") exp_valid,
/*"" zpace,
 "" exp,
 "" plt,
 "" wid,
 "" tp,*/
cast(e.pkg_qty as UNSIGNED) as pkg_qty,
cast(e.pid as UNSIGNED) as pid,
cast(e.primary_id as char) as id,
CASE when e.parent_id=0 then "" else e.parent_id end AS lid,
cast(e.level_id AS UNSIGNED) as lvl,
cast((SELECT  CASE when COUNT(e1.primary_id) = 0 then 1 else COUNT(e1.primary_id) end  
FROM ' . $esealTable . ' e1
WHERE e1.parent_id=e.primary_id) as UNSIGNED) AS qty,
p.multiPack,
(select update_time from track_history th where e.track_id=th.track_id) as utime,
(select sync_time from track_history th where e.track_id=th.track_id) as stime,
CASE when e.batch_no="unknown" then "" else e.batch_no end AS batch,
e. po_number, IF(p.mrp, NULL,"") mrp,
concat("{",fn_Get_print_attributes(e.primary_id),"}") AS print_attributes,
cast((SELECT  CASE when COUNT(e1.eseal_id) > 0 then 0 else e.is_active end  
FROM ' . $esealTable . ' e1
WHERE e1.parent_id=e.primary_id and e1.is_active=0) as UNSIGNED) AS is_active
FROM ' . $esealTable . ' e
INNER JOIN products p ON e.pid=p.product_id
INNER JOIN master_lookup ml ON ml.value= p.product_type_id
WHERE
p.product_type_id=8003 and e.track_id in ('.$trackIds.') and e.primary_id in('.$ids.')';
			   
				Log::info($sql);
				try
				{
					$result = DB::select($sql);
					/* Log::info(json_encode(['data'=>$result]));
					  die; */
					//Log::info(DB::select($sql)->toSql()); 
				} catch (PDOException $e)
				{
					Log::info($e->getMessage());
					throw new Exception('SQlError while fetching data');
				}
				if (count($result))
				{
					if($codesCount==count($result))
					{
						$message = "Data Found.";
					}
					else 
						$message = "Partial Data Found";
					$status=1;
            if(!$inHouseInventory){

            	//throw new Exception('The stock is in some other location');

                foreach($result as $ids){
                	$transitIds[] = $ids->id;
                } 

                $transitIds = DB::table($esealTable)
                                ->whereIn('track_id',$trackArray1)
                                ->whereIn('primary_id',$transitIds)
                                ->lists('primary_id');

                $transitIds = implode(',',$transitIds);
            $transitionTime = $this->getDate();    
			DB::beginTransaction();

			$inTransit = DB::table('transaction_master')->where(['name'=>'Stock Transfer','manufacturer_id'=>$mfgId])->pluck('id');
				/**************STOCK TRANSFER***********/
             
			$request = Request::create('scoapi/UpdateTracking', 'POST', array('module_id'=>Input::get('module_id'),'access_token'=>Input::get('access_token'),'codes'=>$transitIds,'srcLocationId'=>$plant_location_id,'destLocationId'=>$currentLocationId,'transitionTime'=>$transitionTime,'transitionId'=>$inTransit,'internalTransfer'=>0));
		    $originalInput = Request::input();//backup original input
			Request::replace($request->input());						
		    $response = Route::dispatch($request)->getContent();
			$response = json_decode($response,true);
						if($response['Status'] == 0)
							throw new Exception($response['Message']);
               
            $receive = DB::table('transaction_master')->where(['name'=>'Receive','manufacturer_id'=>$mfgId])->pluck('id');  
            /**************RECEIVE******************/ 

            $request = Request::create('scoapi/UpdateTracking', 'POST', array('module_id'=>Input::get('module_id'),'access_token'=>Input::get('access_token'),'codes'=>$transitIds,'srcLocationId'=>$currentLocationId,'destLocationId'=>0,'transitionTime'=>$transitionTime,'transitionId'=>$receive,'internalTransfer'=>0));
		    $originalInput = Request::input();//backup original input
			Request::replace($request->input());						
		    $response = Route::dispatch($request)->getContent();
			$response = json_decode($response,true);
						if($response['Status'] == 0)
							throw new Exception($response['Message']);

             }

             DB::commit();
				}
				else
				{
				   $status = 0;
					$message = 'Data Not found.'; 
				}
          

		} catch (Exception $e)
		{
			DB::rollback();
			$status = 0;
			$result = Array();
			Log::info($e->getMessage());
			$message = $e->getMessage();
		}
		$endTime = $this->getTime();
		Log::info(__FUNCTION__ . ' Finishes execution in ' . ($endTime - $startTime));
		Log::info(['Status'=>$status, 'Message' =>'Server: '.$message, 'esealData' => $result]);
		return json_encode(['Status' => $status, 'Message' => 'Server: ' . $message, 'esealData' => $result]);
	}

  
	public function getExcessStock(){
		$startTime = $this->getTime();
		 try
		 {
 
			 Log::info(__FUNCTION__ . ' : ' . print_r(Input::get(), true));
			 $status = 1;
			 $message = 'Successfully retrieved excess material stock';			
			 $locationId = $this->roleAccess->getLocIdByToken(Input::get('access_token'));
			 $stockout = trim(Input::get('stockout'));
			 $materials = trim(Input::get('materials'));
			 $locationObj = new Locations\Locations();
			 $mfgId = $locationObj->getMfgIdForLocationId($locationId);
			 $esealTable = 'eseal_'.$mfgId;
 
			 $childIds = Array();
			 $childIds = $locationObj->getAllChildIdForParentId($locationId);
			 if ($childIds)
			 {
				 array_push($childIds, $locationId);
			 }
			 $parentId = $locationObj->getParentIdForLocationId($locationId);
			 $childIds1 = Array();
			 if ($parentId)
			 {
				 $childIds1 = $locationObj->getAllChildIdForParentId($parentId);
				 if ($childIds1)
				 {
					 array_push($childIds1, $parentId);
				 }
			 }
			 $childsIDs = array_merge($childIds, $childIds1);
			 $childsIDs = array_unique($childsIDs);
			 array_push($childsIDs, $locationId);
			 $childsIDs = array_unique($childsIDs);
			 $esealTable = 'eseal_'.$mfgId;
 
			 $locationIds = implode(",",$childsIDs);
 
			 
		if($stockout){
			$materialArray = explode(',',$materials);
			if(empty($materialArray))
				throw new Exception('The materials are empty');

			$result1 =  DB::table($esealTable.' as es')
		 		 ->join('track_history as th','th.track_id','=','es.track_id')
			     ->join('products as pr','pr.product_id','=','es.pid')			      
			     ->where(function ($query) use($childsIDs){
                                $query->whereIn('src_loc_id',$childsIDs)
                                      ->where('dest_loc_id',0);
                              }
                              )
			     ->whereIn('pr.material_code',$materialArray)
			     ->lists('es.primary_id');

            $result2 = DB::table($esealTable.' as es')							
							->join('physical_inventory_log as pil','pil.iot','=','es.primary_id')
							->join('physical_inventory_ref as pir','pir.ref_id','=','pil.ref_id')
							->join('products as pr','pr.product_id','=','es.pid')							
							->where(function ($query) use($childsIDs){
                                $query->whereIn('eseal_location',$childsIDs)
                                      ->whereIn('present_location',$childsIDs);
                              }
                              )
							->whereIn('pr.material_code',$materialArray)
							->where('pir.is_deleted',0)
							->where('pil.level',0)
							->lists('es.primary_id');

				$cartons = DB::table($esealTable.' as es')
				               ->whereIn('primary_id',$result2)
				               ->groupBy('parent_id')
				               ->lists('parent_id');

				$result2 = array_merge($result2,$cartons);


               $ids = array_diff($result1,$result2);

               if(empty($ids)){
               	Log::info(__line__);
               	throw new Exception('There is no excess stock to be moved to stockout location');
               }
                              
                                  
                 $destLocationId = $locationObj->getStockoutLocation($mfgId);

			if(!$destLocationId)
				throw new Exception('Stockout location not configured.');

                 $lastInsertId = DB::table($this->trackHistoryTable)->insertGetId(Array(
						'src_loc_id'=>$locationId,
						'dest_loc_id'=>$destLocationId,
						'transition_id' => 791,
						'tp_id'=>0,
						'update_time'=>$this->getDate()
						));
					
					DB::table($esealTable)
						->whereIn('primary_id', $ids)
						->orWhereIn('parent_id', $ids)
						->update(['track_id'=>$lastInsertId]);
                   

					$sql = 'INSERT INTO  '.$this->trackDetailsTable.' 
					(code, track_id) SELECT primary_id, '.$lastInsertId.' FROM '.$esealTable.' WHERE track_id='.$lastInsertId;
					DB::insert($sql);

					$result= array();
					$message = 'Successfully moved the stock to stockout location';															
		}	
		 else{
		 	
		 	$result1 =  json_decode(json_encode(DB::select('select pr.material_code,pr.name,
		 		sum(pkg_qty) as available,0 as scanned,0 as missing ,uc.uom_name from eseal_'.$mfgId.' es 
		 		 join track_history th on th.track_id=es.track_id
			      join products pr on pr.product_id=es.pid
			      join uom_classes uc on uc.id=pr.uom_class_id
			      where src_loc_id in ('.$locationIds.') and dest_loc_id=0
			      and level_id=0 group by es.pid')),true);


		 $result2 = json_decode(json_encode(DB::select('SELECT pr.material_code,pr.name, SUM(pkg_qty) AS qty,uc.uom_name
							FROM eseal_5 es
							JOIN physical_inventory_log pil ON pil.iot=es.primary_id
							JOIN physical_inventory_ref pir on pir.ref_id=pil.ref_id							
							JOIN products pr ON pr.product_id=es.pid
							JOIN uom_classes uc ON uc.id=pr.uom_class_id
							WHERE (eseal_location IN ('.$locationIds.') AND present_location in ('.$locationIds.')) and
							level=0 and pir.is_deleted=0
							GROUP BY pil.material_code')),true);

           $cnt = count($result1);

           for($i=0;$i < $cnt;$i++){
           	$material = $result1[$i]['material_code'];
           	$qty = $result1[$i]['available'];
               
               foreach($result2 as $re2){
               	if($re2['material_code'] == $material){
               		$result1[$i]['scanned'] = $re2['qty'];
               		$result1[$i]['missing'] = (string)($qty - $re2['qty']);
               	}
               }

           }


		 	if(!$result1)
			  throw new Exception('There is no excess stock');

			$result = $result1;

		 }
 			 
		 } catch(Exception $e)
		 {			
			 $status = 0;
			 $result = Array();
			 Log::info($e->getMessage());
			 $message = $e->getMessage();
		 }
		 $endTime = $this->getTime();
		 Log::info(__FUNCTION__ . ' Finishes execution in ' . ($endTime - $startTime));
		 Log::info(['Status'=>$status, 'Message' =>'Server: '.$message, 'esealData' => $result]);
		 return json_encode(['Status' => $status, 'Message' => 'Server: ' . $message, 'esealData' => $result]);		
	 }


    


		
}        

