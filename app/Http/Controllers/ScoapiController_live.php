<?php

date_default_timezone_set('Asia/Calcutta');
set_time_limit(0);
ini_set('memory_limit', '-1');

use Central\Repositories\RoleRepo;
use Central\Repositories\OrderRepo;
use Central\Repositories\CustomerRepo;
use Central\Repositories\SapApiRepo;
use Central\Repositories\ApiRepo;


class ScoapiController extends BaseController 
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

		$status =1;
		$message = 'killed successfully';

		$result = DB::select("SHOW FULL PROCESSLIST");

		foreach($result as $res){
		  $process_id=$res->Id;
		  
			$sql="KILL ".$process_id;
			DB::statement($sql);
		  }   

       

        return json_encode(['Status'=>$status,'Message'=>$message]);


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
						'service_type'=>'client application',
						'message'=>$response->Message,
						'status'=>1,
						'manufacturer_id'=> $details[0]->customer_id
						]);

					User::where('user_id',$user_id)->update(['last_login'=>date('Y-m-d h:i:s')]);
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
                    $created_on = $this->getDate();
                    $startTime = $this->getTime();
					$result = $this->$api_name($data);
                    $endTime = $this->getTime();
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
						'service_type'=>'client application',
						'message'=>$response->Message,
						'status'=>$response->Status,
						'created_on'=>$created_on,
						'manufacturer_id'=>$details[0]->customer_id,
						'response_duration'=>($endTime - $startTime)
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
			//Log::info($data);
			
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
				//Log::info($roles);
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

			//Log::info('Login Successfull');
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
			//Log::info($data);
			
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
			//	Log::info($roles);
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




			//Log::info('Login Successfull');
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


    public function updateErpCustomers(){
    	try{
    		$status =1;
    		$message = 'Customers Data Updated';
    		$mfgId = $this->roleAccess->getMfgIdByToken(Input::get('access_token'));
    		$locationTypeId = Loctype::where(['manufacturer_id'=>$mfgId,'location_type_name'=>'Customer'])->pluck('location_type_id');

    		if(empty($locationTypeId))
    			throw new Exception("LocationType not configured");
    			

            $data = ['customer'=>true,'manufacturer_id'=>$mfgId,'location_type_id_customer'=>$locationTypeId,'current_date'=>$this->getDate()];



            //$response = $this->sapRepo->callSapApi($method,$method_name,$data,$data1,$mfgId,null);

            $customerObj = new Customers\EsealCustomers();

            $response = $customerObj->saveLocationFromErp($data);
             
            //Log::info($response); 


    	}
    	catch(Exception $e){
    		Log::info($e->getMessage());
    		$status =0;
       		$message = 'Customers Data Updated';
    	}
    	Log::info(['Status'=>$status,'Message'=>$message]);
    	return json_encode(['Status'=>$status,'Message'=>$message]);
    }

	public function getAllLocationsBackup($data){
		try{
			Log::info(__FUNCTION__.' : '.print_r(Input::get(),true));			
			$status =0;
			$locations = array();
			$manufacturer_id= $this->roleAccess->getMfgIdByToken($data['access_token']);
			$location_id = $this->roleAccess->getLocIdByToken($data['access_token']);
			
			$locations = Array();
			if(empty($manufacturer_id)){
				throw new Exception('Parameters missing');
			}  
			$locations =DB::table('locations')
			->leftJoin('location_types','location_types.location_type_id','=','locations.location_type_id')
			->where('locations.manufacturer_id','=',$manufacturer_id)
			->where('location_types.location_type_name','!=','Buyer')
			->where('location_id','!=',$location_id)
			->select('locations.*')
			->addSelect('location_types.location_type_name')
			->get();
			if(!empty($locations)){
				$status =1;
				throw new Exception('Data retrieved successfully');
			}
			else{
				throw new Exception('Data not found.');	
			}
		}
		catch(Exception $e){
			$message = $e->getMessage();
		}
	Log::info(['Status'=> $status, 'Message'=>'Server: '.$message, 'locationData'=> $locations]);	
	return json_encode(Array('Status'=> $status, 'Message'=>'Server: '.$message, 'locationData'=> $locations));
	}


	public function getAllLocations($data){
		try{
			Log::info(__FUNCTION__.' : '.print_r(Input::get(),true));			
			$status =0;
			$locations = array();
			$manufacturer_id= $this->roleAccess->getMfgIdByToken($data['access_token']);
			$location_id = $this->roleAccess->getLocIdByToken($data['access_token']);
			$location_type = strtolower(trim(Input::get('location_type')));			 
			$location_type_arr = array();

			
			$locations = Array();
			if(empty($manufacturer_id)){
				throw new Exception('Parameters missing');
			}
			
			if($location_type)
			{  				
				$checkLocationTypes = DB::table('location_types')->where('manufacturer_id','=',$manufacturer_id)->where('location_type_name','=',$location_type)->pluck('location_type_name');
				if(strtolower($checkLocationTypes) == $location_type){
					array_push($location_type_arr,$location_type);

					if(strtolower($checkLocationTypes) == 'vendor')
						array_push($location_type_arr,'supplier');

               $locations = DB::table('locations')
                ->leftJoin('location_types','location_types.location_type_id','=','locations.location_type_id')
                ->where('locations.manufacturer_id','=',$manufacturer_id)
                ->whereIn('location_types.location_type_name',$location_type_arr)
                ->where('location_id','!=',$location_id)
                ->select('locations.*')
                ->addSelect('location_types.location_type_name')
			    ->get();
			            }
			       else{
			       	throw new Exception('Please Pass Valid Location Type');
			       } 
			    }               
			else{ 
			$locations =DB::table('locations')
			->leftJoin('location_types','location_types.location_type_id','=','locations.location_type_id')
			->where('locations.manufacturer_id','=',$manufacturer_id)
			->where('location_types.location_type_name','!=','Buyer')
			->where('location_id','!=',$location_id)
			->select('locations.*')
			->addSelect('location_types.location_type_name')
			->get();
	      	}
			if(!empty($locations)){
				$status =1;
				throw new Exception('Data retrieved successfully');
			}
			else{
				throw new Exception('Data not found.');	
			}
		}
		catch(Exception $e){
			$message = $e->getMessage();
		}
	Log::info(['Status'=> $status, 'Message'=>'Server: '.$message, 'locationData'=> $locations]);	
	return json_encode(Array('Status'=> $status, 'Message'=>'Server: '.$message, 'locationData'=> $locations));
	}


	public function getAttributeSetList(){
		try{
			//Log::info(Input::get());
			$status =0;
			$attr = array();
			$pid = (int)Input::get('pid');
			$location_id = $this->roleAccess->getLocIdByToken(Input::get('access_token'));

			
			
					$query = DB::table('product_attributesets as pas ')
								->join('attribute_set_mapping as asm','asm.attribute_set_id','=','pas.attribute_set_id')
								->join('products as p','p.group_id','=','pas.product_group_id')
								->join('attributes as attr','attr.attribute_id','=','asm.attribute_id')
								->where('pas.location_id',$location_id)
								->groupBy('pas.product_group_id');
								if($pid){
									$query->where('pas.product_id',$pid);
								}

								$attr = $query->orderBy('asm.sort_order','asc')
										->get(['attr.attribute_id','attr.name','attr.attribute_code','attr.input_type','attr.default_value','attr.is_required','attr.validation','p.group_id','p.product_id']);
							
				$status =1;
				$message = 'Data retrieved successfully';  
					  
			
		}  
		catch(Exception $e){
			$message = $e->getMessage();
		}
		return json_encode(['Status'=>$status,'Message'=>'Server: '.$message,'attrData'=>$attr]);
	}


	public function getLabelTemplates(){
		try{
			Log::info(__FUNCTION__.' : '.print_r(Input::get(),true));
			$data = array();
			$group_id = trim(Input::get('group_id'));
			$mfg_id = $this->roleAccess->getMfgIdByToken(Input::get('access_token'));
			$loc_type_id = $this->roleAccess->getLocTypeByAccessToken(Input::get('access_token'));
			if(!$loc_type_id){
			   throw new Exception('Location Type doesnt exist');
			}
			$tran_ids = DB::table('transaction_master')
								->where('manufacturer_id',$mfg_id)
								->lists('id');

			$query = DB::table('label_master as lm')
					  ->whereIn('transaction_id',$tran_ids)
					  ->where('lm.manufacturer_id',$mfg_id);
			if($group_id){
			$data = $query->where('lm.group_id',$group_id)
						  ->join('transaction_master as tm','tm.id','=','lm.transaction_id')
						  ->join('product_groups as pg','pg.group_id','=','lm.group_id')
						  ->get(['lm.name as template_name','lm.template','tm.id as transition_id','tm.name as transition_name','protocol','lm.group_id','pg.name as group_name','sort_order','lm.dpi','lm.labelcategory','lm.noOfColumns']);
			}   
			else{
			$data = $query->join('transaction_master as tm','tm.id','=','lm.transaction_id')
						  ->join('product_groups as pg','pg.group_id','=','lm.group_id')
						  ->get(['lm.name as template_name','lm.template','tm.id as transition_id','tm.name as transition_name','protocol','lm.group_id','pg.name as group_name','sort_order','lm.dpi','lm.labelCategory','lm.noOfColumns']);
			}       
		  if(empty($data)){          
			  throw new Exception('Data not found');
		  }  
		  $status = 1;
		  $message = 'Data retrieved successfully';        
		}
		catch(Exception $e){
			$status =0;
			$message = $e->getMessage();
		}
		Log::info(['Status'=>$status,'Message'=>'Server: '.$message,'Data'=>$data]);
		return json_encode(['Status'=>$status,'Message'=>'Server: '.$message,'Data'=>$data]);
	}

	public function saveLabelTemplate(){
		try{
			Log::info(__FUNCTION__.' : '.print_r(Input::get(),true));
			
			$group_id = trim(Input::get('group_id'));
			$transaction_id = trim(Input::get('transaction_id'));
			$name = trim(Input::get('name'));
			$protocol = trim(Input::get('protocol'));
			$dpi = trim(Input::get('dpi'));
			$template =  trim(Input::get('template'));
			$no_of_columns = trim(Input::get('no_of_columns'));
			$mfg_id = $this->roleAccess->getMfgIdByToken(Input::get('access_token'));
			
			if(empty($no_of_columns))
				$no_of_columns = NULL;

			if(empty($group_id) || empty($transaction_id) || empty($name) || empty($protocol) || empty($dpi) || empty($template))
				throw new Exception('Parameters are missing');

			$isAlreadyExists =  DB::table('label_master')
									  ->where(['transaction_id'=>$transaction_id,'name'=>$name,'group_id'=>$group_id,'protocol'=>$protocol,'manufacturer_id'=>$mfg_id])
									  ->count();
			DB::beginTransaction();
			if($isAlreadyExists){
				DB::table('label_master')
									  ->where(['transaction_id'=>$transaction_id,'name'=>$name,'group_id'=>$group_id,'protocol'=>$protocol,'manufacturer_id'=>$mfg_id])
									  ->update(['template'=>$template,'dpi'=>$dpi,'updated_on'=>date('Y-m-d h:i:s')]);
				if(!is_null($no_of_columns)){
					DB::table('label_master')
									  ->where(['transaction_id'=>$transaction_id,'name'=>$name,'group_id'=>$group_id,'protocol'=>$protocol,'manufacturer_id'=>$mfg_id])
									  ->update(['noOfColumns'=>$no_of_columns]);
				}
				$status = 1;
				$message = 'Label Template Updated Sucessfully';
			}
			else{
				DB::table('label_master')->insert(['name'=>$name,'template'=>$template,'manufacturer_id'=>$mfg_id,'transaction_id'=>$transaction_id,'protocol'=>$protocol,'group_id'=>$group_id,'updated_on'=>date('Y-m-d h:i:s'),'dpi'=>$dpi,'noOfColumns'=>$no_of_columns]);
				$status =1;
				$message = 'Label Template Saved Sucessfully';	
			}
			DB::commit();

		}
		catch(Exception $e){
		  $status =0;
		  $message = $e->getMessage();
		  DB::rollback();
		}
		Log::info(['Status'=>$status,'Message'=>'Server: '.$message]);
		return json_encode(['Status'=>$status,'Message'=>'Server: '.$message]);
	}

	public function productsByLocation($data)
	{
		try
		{
			Log::info($data);
			$status =0;
			$prod = array();
			$location_id = $data['location_id'];
			$mfg_id = $this->roleAccess->getMfgIdByToken($data['access_token']);
			if(empty($location_id))
			{
				throw new Exception('Parameters Missing.');
			}
			$business_unit_id =  Location::where('location_id',$location_id)->pluck('business_unit_id');
			//Log::info('Business Unit Id :'.$business_unit_id);

			/*$result = DB::table('product_locations')
						->join('products','products.product_id','=','product_locations.product_id')			            
						->where('product_locations.location_id',$location_id)
						->where('products.business_unit_id',$business_unit_id)
						//->orWhereIn('location_id',$childIds)
						->groupBy('products.group_id')				
						->select('products.group_id')
						->get(); */

                        $result = DB::table('product_locations')
						->join('products','products.product_id','=','product_locations.product_id')			            
						->where('product_locations.location_id',$location_id);

					if($business_unit_id != 0)	
						$result->where('products.business_unit_id',$business_unit_id);					

			$result = $result->groupBy('products.group_id')				
						     ->select('products.group_id')
						     ->get();	


			//Log::info($result);			
			if(!empty($result))
			{
				$status =1;
				$message ='Data retrieved successfully.';
			}
			else
			{
				throw new Exception('Data not found.');	
			}
			foreach($result as $res)
			{
				$products = array();				
				//$products = explode(',',$res->products);
				$attribute_set_id = DB::table('product_attributesets')->where(['product_group_id'=>$res->group_id,'location_id'=>$location_id])->pluck('attribute_set_id');
				
				$prodCollection = DB::table('products as pr')
										
										->join('master_lookup as ml','ml.value','=','pr.product_type_id') 
										->join('product_locations as pl' ,'pr.product_id','=','pl.product_id')   
										->where(['pr.group_id'=>$res->group_id,'pl.location_id'=>$location_id])
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
		catch(Exception $e){
			$message = $e->getMessage();
		}
		//Log::info($prod);
		return json_encode(['Status'=>$status,'Message' =>'Server: '.$message,'Data'=>$prod]);
	}

	public function getUomData($data){
		try{
			$status =0;
			$uoms = array();
			$uoms = DB::table('lookup_categories')
			->leftJoin('master_lookup','lookup_categories.id','=','master_lookup.category_id')
			->where('lookup_categories.id',12)
			->orWhere('lookup_categories.id',13)
			->orWhere('lookup_categories.id',14)
			->orWhere('lookup_categories.id',15)            
			->get(['lookup_categories.name as group_name','master_lookup.name','master_lookup.description']);
			$status =1;
			$message ='Data retrieved successfully.';	
		}
		catch(Exception $e){
			$message = $e->getMessage();
		}
		return json_encode(['Status'=>$status,'Message'=>$message,'uomData'=>$uoms]);
	}

	
	public function getLocationDetails($data){
		try{    
			$status =0;
			$location = array();
			$location_id= $data['location_id'];
			if(empty($location_id)){
				throw new Exception('Parameters Missing');
			}
			$location = DB::table('locations')
			->leftJoin('location_types','location_types.location_type_id','=','locations.location_type_id')
			->where('locations.location_id',$location_id)
			->select('locations.*','location_types.location_type_name')
			->get();  
			if(!empty($location)){
				$status = 1;
				$message = 'Data retrieved successfully.';
			}
			else
				throw new Exception('Data not found');  
		}
		catch(Exception $e){
			$message = $e->getMessage();
		}
		return json_encode(['Status'=>$status,'Message'=>$message,'locationData'=>$location]);	
	}

	public function getLocationsByType($data){
		try{
			$status = 0;
			$locations = array();
			$location_type_id = $data['location_type_id'];
			if(empty($location_type_id)){
				throw new Exception('Parameters Missing');
			}
			$locations = DB::table('locations')
			->leftJoin('users','users.customer_id','=','locations.manufacturer_id')
			->leftJoin('users_token','users_token.user_id','=','users.user_id')
			->where(array('locations.location_type_id'=>$location_type_id,'users_token.access_token'=>$data['access_token']))
			->select('locations.*')
			->get();
			if(!empty($locations)){
				$status= 1;
				$message = 'Data retrieved successfully';       
			}
			else
				throw new Exception('Data not found');
		}
		catch(Exception $e){
			$message = $e->getMessage();
		}
		return json_encode(['Status'=>$status,'Message'=>$message,'locationsData'=>$locations]);	
	}


	public function productDetailsByEseal($data){
		try{
			$status = 0;
			$final = array();	
			$eseal_id = $data['eseal_id'];
			if(empty($eseal_id)){
				throw new Exception('Parameters Missing'); 
			}
			$manufacturer_id= $this->roleAccess->getMfgIdByToken($data['access_token']);
			$primaryCollection = DB::table('eseal_'.$manufacturer_id)->where('primary_id',$eseal_id)->orWhere('tp_id',$eseal_id)->get();
			
			if(empty($primaryCollection)){
				throw new Exception('In-valid Eseal Id.');
			}
			$status = 1;		
			$pid = $primaryCollection[0]->pid;
			$prodCollection = DB::table('products')->where('product_id',$pid)->get();

			$prodInfo = ['name'=>$prodCollection[0]->name,'title'=>$prodCollection[0]->title,'description'=>$prodCollection[0]->description,'manufacturer'=> $prodCollection[0]->manufacturer_id];
			$final['product_info'] = $prodInfo;

			$image = $prodCollection[0]->image;
			$final['image'] = $image;
			
			$attribute_map_id = $primaryCollection[0]->attribute_map_id;
			$attributeCollection=DB::table('attribute_mapping')->where('attribute_map_id',$attribute_map_id)->get();
			foreach($attributeCollection as $attribute){
				$prodAttr[$attribute->attribute_name] = $attribute->value;
			}
			$location = Location::where('location_id',$attribute->location_id)->get();
			$prodAttr['location_name']  = $location[0]->location_name;
			$final['product_attributes'] = $prodAttr;
			
			$attributeCollection=DB::table('product_attributes')
			->join('attributes','attributes.attribute_id','=','product_attributes.attribute_id')
			->where(array('product_attributes.product_id'=>$pid,'attributes.attribute_type'=>1))                             
			->get();
			if(!empty($attributeCollection)){                
				foreach($attributeCollection as $attribute){
					$prodAttrs[$attribute->name] = $attribute->value;
				}
				$final['other_attributes'] = $prodAttrs;
			}
			$message = 'Data retrieved successfully.';
		}
		catch(Exception $e){
			$message = $e->getMessage();
		}
		return json_encode(['Status'=>$status,'Message'=>$message,'Data'=>$final]);	
	}


	public function getTpFromAnyLevel(){
		try{
			Log::info(__FUNCTION__.' : '.print_r(Input::get(),true));
			$mfg_id = $this->roleAccess->getMfgIdByToken(Input::get('access_token'));
			$eseal_id = trim(Input::get('eseal_id'));
			$tp ='';
			if(!$eseal_id)
				throw new Exception('eSealId not passed');
			$primary = DB::table('eseal_'.$mfg_id)->where('primary_id',$eseal_id)->pluck('eseal_id');
			if($primary){
				$tp = DB::table('track_details as td')
							   ->join('track_history as th','th.track_id','=','td.track_id')
							   ->where('td.code',$eseal_id)
							   ->whereNotNull('th.tp_id')
							   ->where('th.tp_id','!=',0)
							   ->pluck('tp_id');
				if(!$tp)
					throw new Exception('TP doesnt exist');
				else
					$status =1;
					$message = 'TP retrieved successfully';
			}
			else{
				throw new Exception('In-Valid eSealId');
			}
		}
		catch(Exception $e){
			$status =0;
			$message = $e->getMessage();
		}
		return json_encode(['Status'=>$status,'Message'=>'Server: '.$message,'TP'=>$tp]);
	}

	public function saveLocation($data){
		try{		
			$status =0;	
			$manufacturer_id= $this->roleAccess->getMfgIdByToken($data['access_token']);
			$x = array('email','password','module_id','access_token'); 
			foreach($x as $y){
				unset($data[$y]);
			}
			$data['manufacturer_id'] = $manufacturer_id;

			$location = Location::where(array('location_name'=>$data['location_name'],'manufacturer_id'=>$manufacturer_id))->get();
			if(empty($location[0])){
				Location::create($data);
				$status =1;
				$message ='Location created successfully.';
			}
			else{
				throw new Exception('Location already exists.');
			}
		}
		catch(Exception $e){
			$message = $e->getMessage();
		}
		return json_encode(['Status'=>$status,'Message'=>$message]);
	}

	public function saveTemplate($data){
		try{
			$status =0;
			$pid = $data['pid'];
			$label = $data['label'];
			$dat = $data['data'];
			$datetime = $data['datetime'];
			if(empty($pid) || empty($datetime) || empty($dat) || empty($label)){
				throw new Exception('Parameters Missing.');
			}
			$product = Products::where('product_id',$pid)->get();
			if(!empty($product[0])){
				$status= 1;
				$temp = Temp::where(array('pid'=>$pid,'label'=>$label))->first();
				if(empty($temp)){				
					$temp = new Temp;
					$temp->pid = $pid;
					$temp->label = $label;
					$temp->data = $dat;
					$temp->datetime = $datetime;
					$temp->save();
					$message = 'Template saved successfully.';
				}
				else{
					$temp->pid = $pid;
					$temp->label = $label;
					$temp->data = $dat;
					$temp->datetime = $datetime;
					$temp->save();
					$message ='Template updated successfully.';
				}
			}
			else{
				throw new Exception('In-valid Product');
			}
		}
		catch(Exception $e){
			$message = $e->getMessage();
		}
		return json_encode(['Status'=>$status,'Message'=>$message]);
	}


	public function getTemplateByLocation($data){
		try{
			$status =0;
			$temp = array();
			$location_id = $data['location_id'];
			if(empty($location_id))
				throw new Exception('Parameter Missing');

			$location =DB::table('product_locations')->where('location_id',$location_id)->get();
			if(!empty($location)){
				$temp = DB::table('temp_product_data')
				->Join('product_locations','product_locations.product_id','=','temp_product_data.pid')
				->where('product_locations.location_id',$location_id)
				->select('temp_product_data.*')
				->get();
				if(!empty($temp)){
					$status = 1;
					$message ='Data Retrieved Successfully.';	
				}
				else
					throw new Exception('There are no templates in this location.');	
			}
			else{
				throw new Exception('This location doesnt have any products.');
			}
		}
		catch(Exception $e){
			$message = $e->getMessage();			
		}
		return json_encode(['Status'=>$status,'Message'=>$message,'tempData'=>$temp]);
	}

	public function getTemplateById($data){
		try{
			$status =0;
			$temp = array();
			$temp_id = $data['temp_id'];
			if(empty($temp_id))
				throw new Exception('Parameter Missing');
			$temp = Temp::where('id',$temp_id)->get();
			if(!empty($temp[0])){
				$status =1;
				$message = 'Data Retrieved Successfully.';
			}
			else
				throw new Exception('Template Doesnt exist.');          
		}
		catch(Exception $e){
			$message = $e->getMessage();
		}
		return json_encode(['Status'=>$status,'Message'=>$message,'tempData'=>$temp]);
	}
	
	public function getTemplateByPid($data){
		try{
			$status =0;
			$temp = array();
			$pid = $data['pid'];
			if(empty($pid))
				throw new Exception('Parameter Missing');
			$temp = Temp::where('pid',$pid)->get();
			if(!empty($temp[0])){
				$status =1;
				$message = 'Data Retrieved Successfully.';
			}
			else
				throw new Exception('Templates Doesnt exist for this product.');          
		}
		catch(Exception $e){
			$message = $e->getMessage();
		}
		return json_encode(['Status'=>$status,'Message'=>$message,'tempData'=>$temp]);
	}

	public function deleteTemplate($data){
		try{
			$status =0;
			$temp_id = $data['temp_id'];
			if(empty($temp_id))
				throw new Exception('Parameter Missing');
			$temp = Temp::where('id',$temp_id)->first();
			if(!empty($temp)){
				$temp->delete();
				$status =1;
				$message='Template deleted successfully.';
			}
			else{
			throw new Exception('In-valid Template ID.');  
			}          
		}
		catch(Exception $e){
			$message = $e->getMessage();
		}
		return json_encode(['Status'=>$status,'Message'=>$message]);	
	}

	public function getAllTemplates($data){
		try{
			$status =0;	
			$temp =array();
			$mfg_id= $this->roleAccess->getMfgIdByToken($data['access_token']);
			$temp =  DB::table('products')
			->Join('temp_product_data','products.product_id','=','temp_product_data.pid')
			->Join('product_locations','product_locations.product_id','=','temp_product_data.pid')
			->where('products.manufacturer_id',$mfg_id)
			->select('temp_product_data.*','product_locations.location_id')
			->get();
			if(!empty($temp)){
				$status = 1;
				$message ='Data retrieved successfully.';	
			}
			else
				throw new Exception('There are no templates for this manufacturer.');	
		}
		catch(Exception $e){
			$message = $e->getMessage();
		}
		return json_encode(['Status'=>$status,'Message'=>$message,'tempData'=>$temp]);
	}


	public function getErpConfiguration($data){
		try{
			$status =0;
			$erp = array();
			$mfg_id= $this->roleAccess->getMfgIdByToken($data['access_token']);

			$query = DB::table('erp_integration')->where('manufacturer_id',$mfg_id);
			$id = $query->pluck('id');
			if(!empty($id)){
				$erp = $query->select('erp_model','integration_mode','web_service_url','client_url','token','company_code','web_service_username','web_service_password','sap_client')->get();
				
	
				/*$buffer = 8; 
				// get the amount of bytes to pack
				$extra = 8 - (strlen($buffer) % 8);
				// add the zero padding
				if($extra > 0) {
					for($i = 0; $i < $extra; $i++) {
						$buffer .= "\0";
					}
				}
				// very simple ASCII key and IV
				$key = $erp[0]->web_service_password."DR0wSS@P6660juht";
				$iv = $erp[0]->web_service_password;
				// hex encode the return value
				$string = bin2hex(mcrypt_cbc(MCRYPT_3DES, $key, $buffer, MCRYPT_ENCRYPT, $iv));
 
				$erp[0]->web_service_password = $string;*/

				$status =1;
				$message ='Erp-data retrieved successfully.';
			}
			else{
				throw new Exception('There is no erp configuration for this brand-owner');
			}
		}
		catch(Exception $e){
			$message = $e->getMessage();
		}
		return json_encode(['Status'=>$status,'Message'=>'Server: '.$message,'erpData'=>$erp]);
	}
	
	public function saveProduct(){
		try{
			Log::info(__FUNCTION__.' : '.print_r(Input::get(),true));
			$status =1;
			$i = false;
			$pid = '';
			$updateArray = array();
			$json  = trim(Input::get('data'));
			$levels = trim(Input::get('levels'));
			$attributes = trim(Input::get('attributes'));
			$mfg_id= $this->roleAccess->getMfgIdByToken(Input::get('access_token'));
			$locationId = $this->roleAccess->getLocIdByToken(Input::get('access_token'));
			$prodArray = json_decode($json,TRUE);
			
			
			  if(json_last_error() != JSON_ERROR_NONE)
					throw new Exception('Json Error');
				
					if(isset($prodArray['product_id']) || !empty($prodArray['product_id']))
					   $i = true;
			
						 DB::beginTransaction();   
			if($i){
			   
						  $pid = $prodArray['product_id'];
						  $isExists = DB::table('products')->where(['product_id'=>$prodArray['product_id'],'manufacturer_id'=>$mfg_id])->count();
			
			if(!$isExists)
				throw new Exception('The given product doesnt exist.');

					
			   try{
							unset($prodArray['product_id']);
							DB::table('products')->where('product_id',$pid)->update($prodArray);
			   }
			   catch(PDOException $e){
						   Log::info($e->getMessage());
						   throw new Exception('SQlError while updating data');
			   }

					  /* if(!empty($attributes)){
						   $attributeArray = json_decode($attributes,TRUE);
					
						  if(json_last_error() != JSON_ERROR_NONE)
							   throw new Exception('Json Error');

						  foreach($attributeArray as $key => $value){
								$sql = 'SHOW COLUMNS FROM products LIKE "'.$key.'" ';
								$result = DB::statement($sql);
							   if($result)
								   $updateArray[$key] = $value;

						   }

						  if(!empty($updateArray))
							  DB::table('products')->where('product_id',$pid)->update($updateArray);			       

					 }*/

					 if(!empty($attributes)){

						   $attributeArray = json_decode($attributes,TRUE);
					
						  if(json_last_error() != JSON_ERROR_NONE)
							   throw new Exception('Json Error');

						  foreach($attributeArray as $key => $value){
						  	    $column_name = Attributes\Attributes::where('attribute_code',$key)->pluck('default_value');
							   if($column_name)
								   $updateArray[$column_name] = $value;

						   }

						  if(!empty($updateArray))
							  DB::table('products')->where('product_id',$pid)->update($updateArray);			       

					 }


						 if(!empty($levels)){
			
							$levelArray = json_decode($levels,TRUE);    
				
				if(json_last_error() != JSON_ERROR_NONE)
					throw new Exception('Json Error');

				foreach($levelArray as $level){
				
						DB::table('product_packages')->where(['level'=>$level['level_id'],'product_id'=>$pid])->update(['quantity'=>$level['qty']]);
 
			}
				
				}

			   $message = 'Product updated successfully';				

			}
			else{
			   $isExists = DB::table('products')->where(['name'=>$prodArray['name'],'manufacturer_id'=>$mfg_id])->count();
			
			if($isExists)
				throw new Exception('A product with the same name already exists');
			   
			   $business_unit_id = DB::table('locations')->where('location_id',$locationId)->pluck('business_unit_id');

			   $prodArray['manufacturer_id'] = $mfg_id;
			   $prodArray['business_unit_id'] = $business_unit_id;

			   try{
				DB::table('products')->insert($prodArray);
			   }
			   catch(PDOException $e){
				Log::info($e->getMessage());
				throw new Exception('SQlError while fetching data');
			   }

				$pid = DB::getPdo()->lastInsertId();
						
				$product_loc = new Productlocations;
				$product_loc->product_id = $pid;
				$product_loc->location_id = $locationId;
				$product_loc->save();

				if(empty($levels))
					throw new Exception('Product Packages not defined.');

				$levelArray = json_decode($levels,TRUE);    
				
				if(json_last_error() != JSON_ERROR_NONE)
					throw new Exception('Json Error');

				foreach($levelArray as $level){

				$product_pack = new Products\ProductPackage;
				$product_pack->product_id = $pid;
				$product_pack->level = $level['level_id']; 
				$product_pack->quantity = $level['qty'];
				$product_pack->save();

			}
			   
			
				$message = 'Product saved successfully';
			}

			DB::commit();

		}
		catch(Exception $e){
			DB::rollback();
			$status =0;
			$message = $e->getMessage();
		}
		Log::info(['Status'=>$status,'Message'=>$message,'Pid'=>$pid]);
		return json_encode(['Status'=>$status,'Message'=>$message,'Pid'=>$pid]);
	}

	 public function getProductGroups(){
		$status =1;
		$message ='Data found successfully';
		$mfg_id = $this->roleAccess->getMfgIdByToken(Input::get('access_token'));

		$data = DB::table('product_groups')->where('manufacture_id',$mfg_id)->get(['name','group_id']);

		if(!$data){
				$status =0;
				$message = 'No Data found';
		}
		return json_encode(['Status'=>$status,'Message'=>$message,'Data'=>$data]);
	}

    public function gulfTesting(){

        
		$adjacents = DB::table('track_history1')->get();

		foreach ($adjacents as $adjacent){


			$track_id = DB::table('track_history')->where([
						  'src_loc_id'=>$adjacent->src_loc_id,
						  'dest_loc_id'=>$adjacent->dest_loc_id,
						  'transition_id' =>$adjacent->transition_id,
						  'update_time' =>$adjacent->update_time
						])->pluck('track_id');

			if($track_id)
			    DB::table('track_details')->insert(['code'=>$adjacent->adjacent_id,'track_id'=>$track_id]);
			
		}

	}

	public function attributeScript(){
try{
	    DB::beginTransaction();
	    $status =1;
	    $message = 'Attributes update successfully';

		$seseal_ids = DB::table('eseal_attributes')->distinct()->get('seseal_id');
        foreach($seseal_ids as $id){

       $attrArray = array();
       $attributes = DB::table('eseal_attributes')->where('seseal_id',$id->seseal_id)->get(['attribute_code','value']);

       foreach($attributes as $attr){
          $attrArray[$attr->attribute_code] = $attr->value;
       }

       $attrJson = json_encode($attrArray);


       $request = Request::create('scoapi/SaveBindingAttributes', 'POST', array('module_id'=> Input::get('module_id'),'access_token'=>Input::get('access_token'),'attributes'=>$attrJson,'lid'=>280,'pid'=>323));
		$originalInput = Request::input();//backup original input
		Request::replace($request->input());
		$response = Route::dispatch($request)->getContent();//invoke API  
		$response = json_decode($response,true); 
		  

		  if(!$response['Status'])
			throw new Exception($response['Message']);

			 $attribute_map_id = $response['AttributeMapId'];

			 DB::table('eseal_5')->where('seseal_id',$id->seseal_id)->update(['attribute_map_id'=>$attribute_map_id]);
}

            DB::commit();
}
catch(Exception $e){
	DB::rollback();
	$status =0;
	$message = $e->getMessage();
}
      Log::info(['Status'=>$status,'Message'=>$message]);
      return json_encode(['Status'=>$status,'Message'=>$message]);
	}



	public function addBuyer($data){
		try{		
			
			Log::info($data);
			$status =0;	
			$manufacturer_id= $this->roleAccess->getMfgIdByToken($data['access_token']);			
			$user_id =Token::where(['module_id'=>$data['module_id'],'access_token'=>$data['access_token']])->pluck('user_id');
			$buyer = DB::table('locations')
			->join('location_types','location_types.location_type_id','=','locations.location_type_id')
			->where(array('firstname'=>$data['firstname'],'locations.manufacturer_id'=>$manufacturer_id,'location_types.location_type_name'=>'Buyer'))
			->get(array('location_types.location_type_id'));

			$location_type_id = DB::table('location_types')->where('location_type_name','Buyer')->get(array('location_type_id'));			  
			
			//Log::info(Input::file('image'));
			if(empty($buyer[0])){
			   $image = Input::file('image');
				if(!empty($image)){
					$destinationPath = 'uploads/fm'; 
					$extension = Input::file('image')->getClientOriginalExtension(); 
					$fileName = rand(11111,99999).'.'.$extension; 
					Input::file('image')->move($destinationPath, $fileName); 
					$image = 'http://'.$_SERVER['SERVER_NAME'].'/'.$destinationPath.'/'.$fileName;
				}  
				else{
					$image ='';
				}
				$lc = new Location;
				$lc->location_name = $data['shop_name'];
				$lc->firstname = $data['firstname'];
				$lc->lastname = $data['lastname'];
				$lc->manufacturer_id = $manufacturer_id;
				$lc->location_type_id = $location_type_id[0]->location_type_id;
				$lc->location_email = $data['email'];
				$lc->location_address = $data['address'];
				$lc->state = $data['state'];
				$lc->pincode = $data['pincode'];
				$lc->city = $data['city'];
				$lc->latitude = $data['latitude'];
				$lc->longitude = $data['longitude'];
				$lc->category = $data['category'];
				$lc->phone_no =  $data['phone_no'];
				$lc->country = $data['country'];
				$lc->image = $image;
				$lc->user_id = $user_id;
				$lc->created_date = date('Y-m-d h:i:s');
				$lc->save();

				$status =1;
				$message ='Buyer created successfully.';
			}
			else{
				throw new Exception('Buyer already exists.');
			}
		}
		catch(Exception $e){
			$message = $e->getMessage();
		}
		return json_encode(['Status'=>$status,'Message'=>$message]);
	}

	public function updateBuyer($data){

		try{
			Log::info($data);
			$status =0;
			$user_id =Token::where(['module_id'=>$data['module_id'],'access_token'=>$data['access_token']])->pluck('user_id');
			$loc= Location::where(array('user_id'=>$user_id,'location_id'=>$data['buyer_id']))->first();

			if(!empty($loc)){

				$loc->location_name = $data['shop_name'];
				$loc->firstname = $data['firstname'];
				$loc->lastname = $data['lastname'];
				$loc->location_email = $data['email'];
				$loc->location_address = $data['address'];
				$loc->state = $data['state'];
				$loc->pincode = $data['pincode'];
				$loc->city = $data['city'];
				$loc->latitude = $data['latitude'];
				$loc->longitude = $data['longitude'];
				$loc->category = $data['category'];
				$loc->phone_no =  $data['phone_no'];
				$loc->country = $data['country'];
				
				$image = Input::file('image');
			 if(!empty($image)){
				$destinationPath = 'uploads/fm'; 
				$extension = Input::file('image')->getClientOriginalExtension(); 
				$fileName = rand(11111,99999).'.'.$extension; 
				Input::file('image')->move($destinationPath, $fileName); 
				$image = 'http://'.$_SERVER['SERVER_NAME'].'/'.$destinationPath.'/'.$fileName;
				}
				else{
					$image ='';
				}	
				$loc->image = $image;
				$loc->save();

				$status =1;
				$message ='Buyer updated successfully';
			}
			else{
				throw new Exception('Buyer doesnt exist.');
			}
		}
		catch(Exception $e){
			$message = $e->getMessage(); 
		}
		return json_encode(['Status'=>$status,'Message'=>$message]);

	}

	public function getBuyerDetails($data){
		try{    
			$status =0;
			$buyer = array();
			$buyer_id= $data['buyer_id'];
			if(empty($buyer_id)){
				throw new Exception('Parameters Missing');
			}
			$buyer = DB::table('locations')
			->Join('location_types','location_types.location_type_id','=','locations.location_type_id')
			->where(array('locations.location_id'=>$buyer_id,'location_types.location_type_name'=>'Buyer'))			
			->get(array('location_id as buyer_id','location_name as shop_name','firstname','lastname','location_email as email','location_address as address','phone_no','state','longitude','latitude','pincode','city','country'));  
			
			if(!empty($buyer[0])){
				$status = 1;
				$message = 'Data retrieved successfully.';
			}
			else
				throw new Exception('Data not found');  
		}
		catch(Exception $e){
			$message = $e->getMessage();
		}
		return json_encode(['Status'=>$status,'Message'=>$message,'buyerData'=>$buyer]);	
	}

	public function checkInventoryAvailability($data)
	{ 
		try
		{
			//Log::info($data);
			$status = 0;
			$message = '';
			$pskus = json_decode($data['sku']);
			$return_token='';
			//checking the product skus are empty are not
			if(!empty($pskus))
			{
				$tmpProductAvailablearr = array();
				$productAvailablearr = array();
				$reqProductAvailablearr = array();
				$available=1;
				foreach($pskus as $sku)
				{
					$products = Products::where('sku',$sku->sku)->get(['product_id','manufacturer_id']);
					if(!empty($products[0])){

						$pqty = DB::table('eseal_'.$products[0]->manufacturer_id)
						->select('eseal_'.$products[0]->manufacturer_id.'.primary_id')
						->where(array('eseal_'.$products[0]->manufacturer_id.'.level_id'=>0,'eseal_'.$products[0]->manufacturer_id.'.gds_status'=>0,'eseal_'.$products[0]->manufacturer_id.'.pid'=>$products[0]->product_id))
						->groupBy('eseal_'.$products[0]->manufacturer_id.'.pid')
						->count();

						if($sku->qty > $pqty)
						{	
							$available = 0;						
							$tmpProductAvailablearr['status']= 0;
							$tmpProductAvailablearr['pid']=$products[0]->product_id;
							$tmpProductAvailablearr['qty']=$pqty;
							$reqProductAvailablearr[]=$tmpProductAvailablearr;
						}
						else
						{
							$tmpProductAvailablearr['status']= 1;
							$tmpProductAvailablearr['pid']=$products[0]->product_id;
							$tmpProductAvailablearr['qty']=$pqty;
							$reqProductAvailablearr[]=$tmpProductAvailablearr;
						}

					}
					else{
						throw new Exception('One of the skus is in-valid');
					}
				}

				$productAvailablearr = $reqProductAvailablearr;
				//echo "<pre/>";print_r($productAvailablearr);exit;
				if($available==1)
				{ 
					$is_blocked = (isset($data['is_blocked']) && $data['is_blocked']!='')?$data['is_blocked']:'';
					if($is_blocked==1)
					{
						$order_token = 'ORD'.date('h-i-s');
						$dm_order_token = new DmOrderToken;
						$dm_order_token->customer_id= $products[0]->manufacturer_id;
						$dm_order_token->order_token= $order_token;
						$dm_order_token->date_time=date('Y-m-d h:i:s');
						//$dm_order_token->user_agent=$data['user_agent'];
						$dm_order_token->save();
						//Log::info();
						$return_token = $order_token;

						foreach($pskus as $sku)
						{
							//Log::info('---------------------------'.$sku->qty);
							 //Update the stock for blocking
							$products = Products::where('sku',$sku->sku)->get(['product_id','manufacturer_id']);
							$upqry =  DB::table('eseal_'.$products[0]->manufacturer_id)
							->orderBy('eseal_id','ASC')
							->take($sku->qty)
							->where(array('pid'=>$products[0]->product_id,'level_id'=>0,'gds_status'=>0))
							->update(array('gds_status' => 1,'gds_order'=>$order_token));

							//Log::info($upqry);
						}
					}
					$status=1;
					$message ='Stock is available.';
				}

				else
				{
					$order_token='';
					$status =1;
					$message ='Out of Stock for the following products.';
				} 
			}
			else
			{
				$status = 0;
				$message = 'Parameter Missing.';
				throw new Exception($message);

			}
		}
		catch(Exception $e)
		{
			$message = $e->getMessage();
		}
		return json_encode(Array('Status'=>$status, 'Message' =>$message,'order_token'=>$return_token,'Data'=>$productAvailablearr));
	}


	public function unblockStock($data){
		try{
			$status =0;
			$manufacturer_id= $this->roleAccess->getMfgIdByToken($data['access_token']);
			$order_token = $data['order_token'];

			DmOrderToken::where(['customer_id'=>$manufacturer_id,'order_token'=>$order_token])->delete();
			$upqry =  DB::table('eseal_'.$manufacturer_id)
						->where(array('gds_order'=>$order_token,'gds_status'=>1))
						->update(array('gds_status' => 0,'gds_order'=>'unknown'));
			Log::info($upqry);

			$status =1;
			$message = 'Un-Blocked the stock successfully.';
		}
		catch(Exception $e){
			$message = $e->getMessage();
		}
		return json_encode(['Status'=>$status,'Message'=>$message]);
	}

	public function getLeads($data){
		try{
			$status =0;
			$user_id =Token::where(['module_id'=>$data['module_id'],'access_token'=>$data['access_token']])->pluck('user_id');

			$lead = Leads::where('user_id',$user_id)->select('id','lead_name','lead_category','shop_name','shop_address','image','phone_no','last_visit','latitude','longitude')->get();//array('id','lead_name','shop_name','shop_address','phone_no','last_visit','latitude','longitude','email'));

			if(!empty($lead[0])){
				$status = 1;
				$message = 'Data retrieved successfully.';
			}
			else{
				throw new Exception('Data not found.');
			}
			}
			catch(Exception $e){
				$message = $e->getMessage();
			}
			return json_encode(['Status'=>$status, 'Message' =>$message,'leadData'=>$lead]);
			}

	public function getCountries($data){
		try{
			$status =1;
			$message= 'Data retrieved successfully';
			$countries =DB::table('countries')->get(['country_id','name']);
		}
		catch(Exception $e){
			$message = $e->getMessage();
		}
		return json_encode(['Status'=>$status, 'Message' =>$message,'countryData'=>$countries]);
	}

	public function getstates($data){
		try{
		  $status =1;
		  $message = 'Data retrieved successfully';	
		  $states = DB::table('zone')->where('country_id',$data['country_id'])->get(['zone_id as state_id','name']);
		}
		catch(Exception $e){
			$message = $e->getMessage();
		}
		return json_encode(['Status'=>$status, 'Message' =>$message,'stateData'=>$states]);

	}

	public function repeatOrder($data){
		try{
			$status =0;
			$order_number = $data['order_id'];
			$order_token = 'ORD'.date('h-i-s');
		   
			DB::beginTransaction();
			$order1= EsealOrders::where('order_number',$order_number)->get(['order_id','order_token']);
			$order_id1 = $order1[0]->order_id;
			if(!empty($order_id1)){
			DB::statement('insert into eseal_orders(customer_id,customer_group_id,firstname,lastname,email,telephone,shipping_firstname,shipping_lastname,shipping_address_1,shipping_address_2,shipping_city,shipping_postcode,shipping_zone,shipping_zone_id,shipping_country,shipping_country_id,shipping_method,shipping_code,order_status_id,order_token,order_type,total,created_by,buyer_id,date_added,date_modified,image) 
						   select customer_id,customer_group_id,firstname,lastname,email,telephone,shipping_firstname,shipping_lastname,shipping_address_1,shipping_address_2,shipping_city,shipping_postcode,shipping_zone,shipping_zone_id,shipping_country,shipping_country_id,shipping_method,shipping_code,17006 as order_status_id,"'.$order_token.'" as order_token,order_type,total,created_by,buyer_id,"'.date('Y-m-d h:i:s').'" as date_added,"'.date('Y-m-d h:i:s').'" as date_modified,image from eseal_orders where order_number="'.$order_number.'"');

			$order_id2 = DB::getPdo()->lastInsertId();
			$order_number = 'ORD'.date('yy').date('mm').str_pad($order_id2,6,"0",STR_PAD_LEFT);

			DB::table('eseal_orders')
			->where('order_id', $order_id2)
			->update(array('order_number' => $order_number)); 

			DB::statement('insert into eseal_order_products (order_id,pid,name,quantity,price,discount,total,tax,reward,delivery_mode) select "'.$order_id2.'" as order_id,pid,name,quantity,price,discount,total,tax,reward,delivery_mode from eseal_order_products where order_id="'.$order_id1.'"');
			DB::statement('insert into dm_order_token (customer_id,order_token,user_agent,date_time) select customer_id,"'.$order_token.'" as order_token,user_agent,"'.date('Y-m-d h:i:s').'" as date_time from dm_order_token where order_token="'.$order1[0]->order_token.'"');
			
			$message ='Order repeated successfully';
			$status =1;
			DB::commit();
			}
			else{
				throw new Exception ('In-valid OrderID');
			}

		}
		catch(Exception $e){
			DB::rollback();
			$message = $e->getMessage();
		}
		return json_encode(['Status'=>$status,'Message'=>$message]);
	}



	public function getOrderDetails($data){
		try{
			$status = 0;
			$order_id = $data['order_id'];
			$orderData = array();

			$orderData = DB::table('eseal_orders as eo')
							 ->join('master_lookup as ml','ml.value','=','eo.order_status_id')
							 ->where('order_number',$order_id)
							 ->get(['order_number','total as order_value','date_added as ordered_date','ml.name as order_status']);
			if(empty($orderData[0])){
				throw new Exception('In-valid OrderID');
			}
			$productData = DB::table('eseal_orders as eo')
							  ->join('eseal_order_products as eop','eop.order_id','=','eo.order_id')
							  ->join('products as p','p.product_id','=','eop.pid')
							  ->join('locations as lc','lc.location_id','=','eo.buyer_id')
							  ->where('eo.order_number',$order_id)
							  ->get(['eop.pid','p.name','eop.quantity as qty','eop.price as mrp','p.image','eop.total','eop.tax','eo.buyer_id','lc.location_name as buyer_name']);
				
			$orderData[0]->products = $productData;
			$status = 1;
			$message = 'Data retrieved successfully';
				
		}
		catch(Exception $e){
			$message = $e->getMessage();
		}
		return json_encode(['Status'=>$status,'Message'=>$message,'orderData'=>$orderData[0]]);
	}



	public function placeOrder($data){
	try{

		Log::info($data);
		$status = 0;
		$order_number ='';
		
		$order_data = json_decode($data['order_data']);
		$order_token = $order_data->order_token;

		$user_id =Token::where(['module_id'=>$data['module_id'],'access_token'=>$data['access_token']])->pluck('user_id');
		$manufacturer_id= $this->roleAccess->getMfgIdByToken($data['access_token']);
	
		$cnt = DB::table('dm_order_token')
		->select('dm_order_token.order_token_id')
		->where(array('dm_order_token.customer_id'=>$manufacturer_id,'dm_order_token.order_token'=>$order_token))
		->count();

		if($cnt){
			$customer_details = $this->custRepo->getAllCustomers($manufacturer_id);

			$buyer = Location::where('location_id',$data['buyer_id'])->get();
			$eseal_orders = new EsealOrders;

			$eseal_orders->customer_id = $customer_details[0]->customer_id;
			$eseal_orders->customer_group_id = $customer_details[0]->customer_type_id;

			//$eseal_orders->invoice_prefix = $customer_details[0]->invoice_prefix;
			$eseal_orders->firstname = $customer_details[0]->firstname;
			$eseal_orders->lastname = $customer_details[0]->lastname;
			$eseal_orders->email = $customer_details[0]->email;
			$eseal_orders->telephone = $customer_details[0]->phone;

			  //shipment person address details
			$eseal_orders->shipping_firstname = $buyer[0]->firstname;
			$eseal_orders->shipping_lastname = $buyer[0]->lastname;
			  //$eseal_orders->shipping_company = $order_data->shipping_company;
			$eseal_orders->shipping_address_1 = $buyer[0]->location_address;
			$eseal_orders->shipping_address_2 = '';
			$eseal_orders->shipping_city = $buyer[0]->city;
			$eseal_orders->shipping_postcode = $buyer[0]->pincode;
			$eseal_orders->shipping_zone = $buyer[0]->state;
			$zone_id = Zone::where('name',$buyer[0]->state)->pluck('zone_id');
			$eseal_orders->shipping_zone_id = $zone_id;
			$eseal_orders->shipping_country = $buyer[0]->country;
			$country_id = Countries::where('name',$buyer[0]->country)->pluck('country_id');
			$eseal_orders->shipping_country_id = $country_id;
			$eseal_orders->shipping_method = 'Flat Shipping Rate';
			$eseal_orders->shipping_code = 'flat.flat';

			$eseal_orders->order_status_id = 17006;
			$eseal_orders->order_token = $order_token;
			$eseal_orders->order_type = 20001;
			$eseal_orders->total = $order_data->total;
			$eseal_orders->created_by = $user_id;
			$eseal_orders->buyer_id = $data['buyer_id'];
			$eseal_orders->date_added = date('Y-m-d h:i:s');
			$eseal_orders->date_modified = date('Y-m-d h:i:s');

			$image = Input::file('image');
			if(!empty($image)){
				$destinationPath = 'uploads/fm'; 
				$extension = Input::file('image')->getClientOriginalExtension(); 
				$fileName = rand(11111,99999).'.'.$extension; 
				Input::file('image')->move($destinationPath, $fileName); 
				$image = 'http://'.$_SERVER['SERVER_NAME'].'/'.$destinationPath.'/'.$fileName;
			}  
			else{
				$image ='';
			}
			$eseal_orders->image = $image;
			$eseal_orders->save();

			$order_id = DB::getPdo()->lastInsertId();
			$order_number = 'ORD'.date('yy').date('mm').str_pad($order_id,6,"0",STR_PAD_LEFT);

			DB::table('eseal_orders')
			->where('order_id', $order_id)
			->update(array('order_number' => $order_number)); 

			  //echo '<pre/>';print_r($order_data->products);exit;
			$mfgGrouparr = array();
			
			$sql='select products.sku,products.mrp as price,count(*) as qty,products.mrp * count(*) as total,0.145 * products.mrp * count(*) as tax from eseal_'.$manufacturer_id.' join products on products.product_id=eseal_'.$manufacturer_id.'.pid where gds_order="'.$order_token.'" group by pid';
			$pskus = DB::select($sql);
			Log::info($pskus);
			foreach($pskus as $sku)
			{
				  //echo '<pre/>';print_r($value->sku);exit;
				$product = Products::where('sku',$sku->sku)->get(['product_id','name','manufacturer_id']);
				$product_ids = DB::table('products')
				->select('products.product_id','products.name','eseal_customer.customer_id')
				->leftJoin('eseal_customer','eseal_customer.customer_id','=','products.manufacturer_id')
				->where('products.sku',$sku->sku)
				->get();

				$eseal_order_products = new EsealOrderProducts;  
				$eseal_order_products->order_id = $order_id;
				$eseal_order_products->pid = $product[0]->product_id;
				$eseal_order_products->name = $product[0]->name;
				$eseal_order_products->quantity =$sku->qty;
				$eseal_order_products->price = $sku->price;
				$eseal_order_products->total = $sku->total;
				$eseal_order_products->tax = $sku->tax;
				$eseal_order_products->save();              

					//Update the stock for reserve
				DB::table('eseal_'.$product[0]->manufacturer_id)
				->where(array('pid'=>$product[0]->product_id,'gds_order'=>$order_data->order_token))
				->update(array('gds_status' => 2,'gds_order'=>$order_id));
				
			} 

			//For storing the payments
			$order_payments = new OrderPayments;
			$order_payments->order_id = $order_id;
			/*$order_payments->payment_type = $payments['payment_type'];
			$order_payments->payment_mode = $payments['payment_mode'];
			$order_payments->trans_reference_no = $payments['trans_reference_no'];
			$order_payments->payee_bank = $payments['payee_bank'];
			$order_payments->ifsc_code = $payments['ifsc_code'];
			$order_payments->amount = $payments['amount'];*/
			$order_payments->payment_date = date('Y-m-d h:i:s');
			$order_payments->save();


			$status = 1;
			$message = 'Successfully placed order.';
		}

		else{
			throw new Exception ('In-valid Order Token');
		}
		
	}
	catch(Exception $e){
		$message = $e->getMessage();
	}
	return json_encode(['Status'=>$status,'Message'=>$message,'order_id'=>$order_number]);
}


	public function addLead($data){
	try{
		Log::info($data);
		$status =0;
		$user_id = Token::where(['module_id'=>$data['module_id'],'access_token'=>$data['access_token']])->pluck('user_id');
		$lead= Leads::where(array('lead_name'=>$data['lead_name'],'shop_name'=>$data['shop_name']))->get();

		if(empty($lead[0])){

			$image = Input::file('image');
			if(!empty($image)){
				$destinationPath = 'uploads/fm'; 
				$extension = Input::file('image')->getClientOriginalExtension(); 
				$fileName = rand(11111,99999).'.'.$extension; 
				Input::file('image')->move($destinationPath, $fileName); 
				$image = 'http://'.$_SERVER['SERVER_NAME'].'/'.$destinationPath.'/'.$fileName;
			}  
			else{
				$image ='';
			}

			$lead = new Leads;
			$lead->lead_name = $data['lead_name'];
			$lead->shop_name = $data['shop_name'];
			$lead->shop_address = $data['shop_address'];
			$lead->phone_no  = $data['phone_no'];
			$lead->last_visit = $data['last_visit'];
			$lead->lead_category = $data['lead_category'];
			$lead->latitude = $data['latitude'];
			$lead->longitude = $data['longitude'];
			$lead->status = 1;
			$lead->image = $image;
			$lead->user_id = $user_id;
			$lead->save();

			$status =1;
			$message ='Lead saved successfully';
		}
		else{
			throw new Exception('Lead already exists.');
		}
	}
	catch(Exception $e){
		$message = $e->getMessage(); 
	}
	return json_encode(['Status'=>$status,'Message'=>$message]);
}


	

	public function cancelOrder($data)
	{
		try{
			$status = 0;
			$order_id = $data['order_id'];

			$eseal_orders = EsealOrders::find($order_id);
			$eseal_orders->order_status_id = 2;
			$eseal_orders->save();

			$status = 1;
			$message = 'Successfully cancelled the order';
		}
		catch(Exception $e)
		{
			$message = $e->getMessage();
		}
		return json_encode(Array('Status'=>$status, 'Message' => $message, 'orderId'=> $order_id));
	}

	public function saveFieldActivity($data){
		try{

			Log::info($data);
			$status =0;
			$user_id =Token::where(['module_id'=>$data['module_id'],'access_token'=>$data['access_token']])->pluck('user_id');

			$field = new Field;
			$field->user_id = $user_id;
			$field->buyer_id = $data['buyer_id'];
			$field->activity_type = $data['activity_type'];
			$field->activity_name  = $data['activity_name'];
			$field->last_visit = $data['last_visit'];
			$field->description = $data['description'];
			$field->activity_date = $data['activity_date'];
			$field->save();

			$status =1;
			$message ='Activity saved successfully';
			
		}
		catch(Exception $e){
			return json_encode(['Status'=>$status,'Message'=>$message]);
		}
	}

	public function convertLeadToBuyer($data){
		try{
			Log::info($data);
			$status =0;
			$lead_id = $data['lead_id'];
			$user_id =Token::where(['module_id'=>$data['module_id'],'access_token'=>$data['access_token']])->pluck('user_id');
			$mfg_id = $this->roleAccess->getMfgIdByToken($data['access_token']);
			 
			 $location_type_id = DB::table('location_types')->where(['location_type_name'=>'Buyer','manufacturer_id'=>$mfg_id])->pluck('location_type_id');
			 $lead = Leads::where('id',$data['lead_id'])->get();
				if(!empty($lead[0])){
				
				$lc = new Location;
				$lc->location_name = $data['shop_name'];
				$lc->firstname = $data['firstname'];
				$lc->lastname = $data['lastname'];
				$lc->location_type_id = $location_type_id;
				$lc->location_email = $data['email'];
				$lc->location_address = $data['address'];
				$lc->state = $data['state'];
				$lc->pincode = $data['pincode'];
				$lc->city = $data['city'];
				$lc->latitude = $data['latitude'];
				$lc->longitude = $data['longitude'];
				$lc->category = $data['category'];
				$lc->phone_no =  $data['phone_no'];
				$lc->country = $data['country'];
				$lc->user_id = $user_id;
				
				$image = Input::file('image');
				if(!empty($image)){
				$destinationPath = 'uploads/fm'; 
				$extension = Input::file('image')->getClientOriginalExtension(); 
				$fileName = rand(11111,99999).'.'.$extension; 
				Input::file('image')->move($destinationPath, $fileName); 
				$image = 'http://'.$_SERVER['SERVER_NAME'].'/'.$destinationPath.'/'.$fileName;
				}
				else{
					$image ='';
				}	

				$lc->image = $image;
				$lc->save();

				Leads::where('id',$data['lead_id'])->delete();

				$status =1;
				$message = 'Lead converted to buyer successfully.';
			}
			else{
				throw new Exception('Lead doesnt exist');
			}
		}
		catch(Exception $e){
			$message = $e->getMessage();
		}
		return json_encode(['Status'=>$status, 'Message' =>$message]);

	}

	public function getJobs($data){
		try{
			$status =0;
			$user_id =Token::where(['module_id'=>$data['module_id'],'access_token'=>$data['access_token']])->pluck('user_id');
	
			$jobs = DB::table('jobs')
					   ->join('locations','locations.location_id','=','jobs.buyer_id')
					   ->join('users','users.user_id','=','jobs.assigned_to')
					   ->where('jobs.assigned_to',$user_id)
					   ->get(array('id','job_type','job_title','job_description','jobs.created_by','job_date','assigned_to','users.username as assigned_name','jobs.status','buyer_id','locations.location_name as buyer_name','location_address as buyer_address','latitude','longitude'));
		
			if(!empty($jobs[0])){
				$status =1;
				$message = 'Data retrieved successfully.';
			}
			else{
				throw new Exception('Data not found.');
			}
		}
		catch(Exception $e){
			$message = $e->getMessage();
		}
		return json_encode(['Status'=>$status,'Message' =>$message,'jobsData'=>$jobs]);
	}


	public function getAllOrders($data){
		try{
			$manufacturer_id= $this->roleAccess->getMfgIdByToken($data['access_token']);
			

		}
		catch(Exception $e){
			$message = $e->getMessage();
		}
		return json_encode(['Status'=>$status,'Message'=>$message]);
	}

	public function getAllBuyers($data){
		try{			
			$status =0;
			$res = array();
			$manufacturer_id= $this->roleAccess->getMfgIdByToken($data['access_token']);
			
			$user_id = Token::where(['module_id'=>$data['module_id'],'access_token'=>$data['access_token']])->pluck('user_id');

			$buyers =DB::table('locations')
						->leftJoin('location_types','location_types.location_type_id','=','locations.location_type_id')
						->where(array('location_types.location_type_name'=>'Buyer','locations.user_id'=>$user_id))
						->select('locations.location_id as buyer_id')  
						->get();
			foreach($buyers as $buyer){

				$buyer_details =DB::table('locations')
								->where('locations.location_id',$buyer->buyer_id)
								->select('locations.location_id as buyer_id','location_name as shop_name','locations.firstname','locations.lastname','location_email as email','location_address as address','image','phone_no','state','region','latitude','longitude','city','country','pincode','category')  
								->get();

				$last_visit =DB::table('field_activity')
							->where(array('user_id'=>$user_id,'buyer_id'=>$buyer->buyer_id))
							->select(DB::raw('max(activity_date) as last_visit'))
							->get();
	

				$last_liquidate =DB::table('field_activity')
							->where(array('user_id'=>$user_id,'buyer_id'=>$buyer->buyer_id,'activity_name'=>'liquidate'))
							->select(DB::raw('max(activity_date) as last_liquidate'))
							->get();   

				$jobs = DB::table('jobs')
							->where(array('assigned_to'=>$user_id,'buyer_id'=>$buyer->buyer_id))
							->select(DB::raw('count(id) as cnt'))
							->get();	              	    

				$orders = DB::table('eseal_orders')
							->join('master_lookup','master_lookup.value','=','eseal_orders.order_status_id')
							->where(array('eseal_orders.created_by'=>$user_id,'buyer_id'=>$buyer->buyer_id,'master_lookup.name'=>'Processing'))
							->select(DB::raw('count(order_id) as cnt'))
							->get();
				
				$buyer_details[0]->last_visit = $last_visit[0]->last_visit;
				$buyer_details[0]->last_liquidate = $last_liquidate[0]->last_liquidate;
				$buyer_details[0]->total_jobs = $jobs[0]->cnt;
				$buyer_details[0]->total_orders = $orders[0]->cnt;
				array_push($res,$buyer_details[0]);
			} 

			if(!empty($res[0])){
				$status =1;
				throw new Exception('Data retrieved successfully');
			}
			else{
				throw new Exception('Data not found.');	
			}
		}
		catch(Exception $e){
			$message = $e->getMessage();
		}
		return json_encode(Array('Status'=> $status, 'Message'=>$message, 'buyerData'=> $res));
	}

	

	public function liquidate($data) 
	{
		Log::info(__FUNCTION__.' : '.print_r(Input::get(),true));
		$status = 0;
		$message = '';
		$user_id = Token::where(['module_id'=>$data['module_id'],'access_token'=>$data['access_token']])->pluck('user_id');
		$location_id = User::where('user_id',$user_id)->pluck('location_id'); 
		$mode = $data['mode']; 
		$transition_time = $data['transition_time'];
		//$dest_location = trim(Input::get('dest_location'));
		$ids = $data['ids']; 		
		$buyer_id = $data['buyer_id'];	

		$chkMissedIds1 = $ids;
		$chkMissedIds = explode(',', $chkMissedIds1);
		$missedIdsNumArra = array();
		$missedIdsStrArra = array();
		foreach($chkMissedIds as $values)
		{
			$values = trim($values);
			if(!empty($values))
			{
				array_push($missedIdsNumArra, $values);
				//array_push($missedIdsStrArra, "'".$values."'");
			}
		}
		//print_r($missedIdsNumArra);exit;
		//For getting carton ids
		$mfgID = Location::where('location_id',$location_id)->pluck('manufacturer_id');

		$primary_ids =DB::select('select group_concat(distinct(primary_id)) as primary_id from eseal_'.$mfgID.' 	where parent_id in ('.$ids. ') or primary_id in ('.$ids.')' );
		//$primary_ids = DB::table('eseal_'.$mfgID)->where('parent_id','in',$ids)->orWhere('primary_id',$ids)->select([DB::raw('GROUP_CONCAT(DISTINCT  primary_id) as primary_id')])->get();	
 
		//echo "<pre/>";print_r($primary_ids);exit;	
		if(count($primary_ids))
		{
			$ids = $primary_ids[0]->primary_id;
		}		
		$ids = explode(',', $ids);
		$idsNumArra = array();
		$idsStrArra = array();
		if(!empty($buyer_id))
			$location_id = $buyer_id;
		///Explode ids and remove space before and after them and convert into numeric array and string array
		foreach($ids as $values)
		{
			$values = trim($values);
			if(!empty($values))
			{
				array_push($idsNumArra, $values);
				array_push($idsStrArra, "'".$values."'");
			}
		}
		/*echo "<pre/>";print_r($idsNumArra);exit;*/
		$numIdsStr = implode(',', $idsNumArra);////converting into numeric string
		$strIdsStr = implode(",", $idsStrArra);////converting into string

		$idsCnt = count($idsNumArra);
		$mfgID = '';
		$transId = 0;
		////GET Location Details
		try
		{
			DB::beginTransaction();
			$locationDetails = Location::where('location_id',$location_id)->get();            
			if(count($locationDetails))
			{
				$mfgID = $locationDetails[0]->manufacturer_id;    
				
				$transitIdArray = Array();
				$endConsumerLocationId = '';
				/// GET Transitions for manufacturer
				$transitionDetails = Transaction::where('manufacturer_id',$mfgID)->get();
				//return $transitionDetails;
				foreach($transitionDetails as $transitionVal)
				{
					$transitIdArray[$transitionVal['id']] = $transitionVal['name'];
				}
				///Get end consumer as location id in case of out operation
				$endConsumerLocationId = DB::select('select 
					ttl.location_id 
					from 
					locations as ttl, location_types as ttlt 
					where 
					ttlt.location_type_id = ttl.location_type_id and ttlt.manufacturer_id = '.$mfgID.' and LOWER(location_type_name) like "consumer%"
					limit 1');

			   Log::info('End Consumer LocationId:-');
			   Log::info($endConsumerLocationId);
			   //echo "<pre/>";print_r($endConsumerLocationId);exit;
			   if(empty($endConsumerLocationId))
				throw new Exception('Consumer Location is not configured');
			   
				$endConsumerLocationId = $endConsumerLocationId[0]->location_id;
			   //	$endConsumerLocationId = $dest_location;
				$isAlreadyOut = 0;
				$missingIds = array();
				///// First check if already OUT is done or not
				//echo "<pre/>";print_r($chkMissedIds);exit;
				$chkForMissing = DB::select('select distinct(primary_id) as primary_id,level_id from eseal_'.$mfgID.' 	where primary_id in ('.$chkMissedIds1.')' );
				
				if(count($chkForMissing)>0)
				{
					foreach($missedIdsNumArra as $id)
					{
						foreach($chkForMissing as $requiredMissedIds)
						{
							//echo "<pre/>";print_r($requiredMissedIds);exit;
							if($requiredMissedIds->level_id==1)
							{
								$chkForMissing1 = DB::select('select distinct(primary_id) as primary_id,level_id from eseal_'.$mfgID.' 	where parent_id in ('.$requiredMissedIds->primary_id.')' );
								if(count($chkForMissing1)>0)
								{
									foreach($chkForMissing1 as $requiredMissedIds1)
									{
										if($id != $requiredMissedIds->primary_id)
										{									
											array_push($missingIds, $id);
										}
									}
								}
							}
							else
							{
								if($id != $requiredMissedIds->primary_id)
								{									
									array_push($missingIds, $id);
								}
							}
						}
					}
				}
				else
				{
					$missingIds = $chkMissedIds;
				}
				$missingIds = array_unique($missingIds);
				//echo "<pre/>";print_r($missingIds);exit;
				if(strtolower($mode) == 'in' )
				{
					//echo "<pre/>";print_r($idsStrArra);exit;

					if(count($idsStrArra))
					{
						$alreadyOutStockRes = DB::select('
						select 
						distinct td.code  
						from 
						track_details as td 
						join track_history th on th.track_id = td.track_id 
						join eseal_'.$mfgID.' es on es.primary_id=td.code
						where 
						es.level_id=0 and 
						td.code in (' . implode(',', $ids). ')  and th.dest_loc_id = 0 and th.src_loc_id = ' . $location_id . ' and 
						th.update_time = (select max(update_time) from track_history as subtu where subtu.track_id=td.track_id) 
						');
					}
					/*$queries = DB::getQueryLog();
					echo "<pre/>";print_r(end($queries));exit;*/
					$alreadyOutStockDataArray = array();
					if(count($alreadyOutStockRes))
					{
						foreach($alreadyOutStockRes as $alreadyOutStockResVal)
						{
							array_push($alreadyOutStockDataArray, $alreadyOutStockResVal->code);
						}
						//echo "<pre/>";print_r($missingIds);exit;					
						$message = 'Some of the IDs are already done with In operation : ' . implode(',', $alreadyOutStockDataArray);
						throw new Exception($message);					
					}

					//// check if IN operation is required before OUT operation
					if(count($idsStrArra))
					{
						$inStockRequiredRes = DB::select('
						select 
						distinct td.code  
						from 
						track_details as td 
						join track_history th on th.track_id = td.track_id 
						join eseal_'.$mfgID.' es on es.primary_id=td.code
						where 
						
						td.code in (' . implode(',', $ids). ')  and th.dest_loc_id > 0 and th.src_loc_id != ' . $location_id . ' and 
						th.update_time = (select max(update_time) from track_history as subtu where subtu.track_id=td.track_id) 
						');
						//$queries = DB::getQueryLog();
						//echo "<pre/>";print_r(end($queries));exit;
					}
					$successIds = array();
					//$missingIds = array();   
					$inStockRequiredResDataArray = array();
					if(count($inStockRequiredRes))
					{
						foreach($idsNumArra as $id)
						{
							foreach($inStockRequiredRes as $inStockRequiredResVal)
							{
								if($id == $inStockRequiredResVal->code)
								{
									array_push($successIds, $id);
								}
								else
								{
									//array_push($missingIds, $id);
								}
							}    
						}
						$successIds = array_unique($successIds);
						//$missingIds = array_unique($missingIds);
						//$missingIds = array_diff($missingIds, $successIds);
						//return $transitIdArray;
						foreach($transitIdArray as $transitId => $transiIdVal)
						{
							if($transiIdVal =='Receive')
							{
								$transId = $transitId;
							}
						}
						///Tracjupdate For Receive
						
						$successIds1 = implode(",",$successIds);
						$request = Request::create('scoapi/liquidateUpdateTracking', 'POST', array('codes'=>$successIds1,'srcLocationId'=>$location_id,'destLocationId'=>0,'transitionTime'=>$transition_time,'transitionId'=>$transId,'internalTransfer'=>0,'access_token'=>$data['access_token'],'module_id'=>$data['module_id']));
						$originalInput = Request::input();//backup original input
						Request::replace($request->input());
						Log::info($request->input());
						$response = Route::dispatch($request)->getContent();//invoke API

						$response = json_decode($response);
						if($response->Status == 0)
						{
							//$message = 'Something went wrong during IN operation';
							//throw new Exception('Something went wrong during IN operation');
							throw new Exception($response->Message);
						}
						else
						{
							$total =0;
							foreach($successIds as $id)
							{
								$result =	DB::select('select mrp,level_id from eseal_'.$mfgID.' where primary_id='.$id);
								if($result[0]->level_id == 0)
								{
									$total = $total + $result[0]->mrp; 
								}
								else
								{
									$result = DB::select('select sum(mrp) as mrp from eseal_'.$mfgID.' where parent_id='.$id);
									//echo "<pre/>";print_r($result);exit;	
									$total = $total + $result[0]->mrp;
								}
								
							}
							//echo "<pre/>";print_r($total);exit;					
							$field = new Field;
							$field->user_id = $user_id;
							$field->buyer_id = $buyer_id;
							$field->activity_type = $mode;
							$field->activity_name = 'liquidate';
							$field->description = 'liquidate';
							$field->total = $total;
							$field->activity_date = $transition_time;
							$field->save();

							$status = 1;
							$message = 'IN operation is done succesfully';
							DB::commit();
						}
					}
					else
					{
						//$message = 'Didn\'t get any data for in operation';
						throw new Exception('Didn\'t get any data for in operation');
					}
				}
				if( strtolower($mode) == 'out' && $isAlreadyOut == 0 )
				{
					if(count($idsStrArra))
					{
						$alreadyOutStockRes = DB::select('
						select 
						distinct td.code  
						from 
						track_details as td 
						join track_history th on th.track_id = td.track_id 
						join eseal_'.$mfgID.' es on es.primary_id=td.code
						where 
						es.level_id=0 and 
						td.code in (' . implode(',', $ids). ')  and th.dest_loc_id > 0 and th.src_loc_id = ' . $location_id . ' and 
						th.update_time = (select max(update_time) from track_history as subtu where subtu.track_id=td.track_id) 
						');
						//$queries = DB::getQueryLog();
						//echo "<pre/>";print_r(end($queries));exit;
					}
					$alreadyOutStockDataArray = array();
					if(count($alreadyOutStockRes))
					{
						foreach($alreadyOutStockRes as $alreadyOutStockResVal)
						{
							array_push($alreadyOutStockDataArray, $alreadyOutStockResVal->code);
						}					
						$message = 'Some of the IDs are already done with OUT operation : ' . implode(',', $alreadyOutStockDataArray);
						throw new Exception($message);					
					}

					//// check if Data is with given location to make Succesfull OUT operation
					if(count($idsStrArra))
					{
						$stockDataForOut = DB::select('
							select 
							distinct td.code  
							from 
							track_details as td 
							join track_history th on th.track_id = td.track_id 
							join eseal_'.$mfgID.' es on es.primary_id=td.code
							where 
							
							td.code in (' . implode(',', $ids). ')  and th.dest_loc_id = 0 and ( th.src_loc_id = ' . $location_id . ' or  th.src_loc_id != ' . $location_id . ') and 
							th.update_time = (select max(update_time) from track_history as subtu where subtu.track_id=td.track_id) 
							');
						$queries = DB::getQueryLog();
						//echo "<pre/>";print_r(end($queries));exit;
					}
					$successIds = array();
					//$missingIds = array();    
					$stockDataForOutDataArray = Array();
					if(count($stockDataForOut))
					{
						foreach($idsNumArra as $id)
						{
							foreach($stockDataForOut as $stockDataForOutVal)
							{
								if($id == $stockDataForOutVal->code)
								{
									array_push($successIds, $id);
								}
								else
								{
									//array_push($missingIds, $id);
								}
							}    
						}

						$successIds = array_unique($successIds);
						//$missingIds = array_unique($missingIds);
						//$missingIds = array_diff($missingIds, $successIds);
						foreach($transitIdArray as $transitId => $transiIdVal)
						{
							if($transiIdVal == 'Sale')
							{
								$transId = $transitId;
							}
						}
						$successIds1 = implode(",",$successIds);
						$response = $this->saveStockIssue($successIds1,$successIds1,$location_id,$endConsumerLocationId,$transition_time,$transId);
						$response = json_decode($response);
						//echo "<pre/>";print_r($response);exit;
						if($response->Status == 0)
						{
							//$message = 'Something went wrong during trackupdate of OUT operation';
							//throw new Exception('Something went wrong during trackupdate of OUT operation');
							throw new Exception($response->Message);
						}
						else
						{
							$total =0;
							foreach($successIds as $id)
							{
								$result =	DB::select('select mrp,level_id from eseal_'.$mfgID.' where primary_id='.$id);
								if($result[0]->level_id == 0)
								{
									$total = $total + $result[0]->mrp; 
								}
								else
								{
									$result = DB::select('select sum(mrp) as mrp from eseal_'.$mfgID.' where parent_id='.$id);
									$total = $total + $result[0]->mrp;
								}
							}
							$field = new Field;
							$field->user_id = $user_id;
							$field->buyer_id = $buyer_id;
							$field->activity_type = $mode;
							$field->activity_name = 'liquidate';
							$field->description = 'liquidate';
							$field->total = $total;
							$field->activity_date = $transition_time;
							$field->save();               

							$status = 1;
							$message  = 'Out operation done succesfully';
							DB::commit();
						}
					}
					else
					{
							//$message  = 'Unable to fetch data for out operation';
							throw new Exception('Unable to fetch data for out operation');
					}
				}
			}
			else
			{
					//$message = 'Unable to fetch location data';
					throw new Exception('Unable to fetch location data');
			}
		} 
		catch (Exception $e)
		{	
				$status=0;
				$message =  $e->getMessage();
				Log::info(['Status'=>0,'Message'=> $message,'missingId' => '']);
				$endTime = $this->getTime();
				//return json_encode(Array('Status'=>0,'Message'=> 'Exception occured during IN/Out operation', 'missingId' => ''));               
				return json_encode(Array('Status'=>0,'Message'=> $message, 'missingId' => ''));               
		}
		//echo "<pre/>";print_r($missingIds);exit;
		return json_encode(Array('Status'=>$status,'Message'=> $message, 'missingId' => implode(',', $missingIds)));    

	}


	public function fieldDashboard($data){
		try{
			$status =0;
			$days = $data['days'];
			$user_id = Token::where(['module_id'=>$data['module_id'],'access_token'=>$data['access_token']])->pluck('user_id');
			
			
			$cnt = Location::where('user_id',$user_id)->select(DB::raw('count(*) as no_buyers'))->where('created_date','>=',date('Y-m-d h:i:s')-$days)->get();
			$cnt1 = Leads::where('user_id',$user_id)->where( DB::raw('MONTH(created_date)'), '=', date('m'))->select(DB::raw('count(*) as no_leads'))->get();		    
			$cnt3 = EsealOrders::where('created_by',$user_id)->where( DB::raw('MONTH(date_added)'), '=', date('m'))->select(DB::raw('count(*) as no_orders'))->get();
			$cnt4 = Field::where(['user_id'=>$user_id,'activity_type'=>'out'])->select(DB::raw('sum(total) as total'))->get();
			$cnt5 = Field::where(['user_id'=>$user_id,'activity_type'=>'in'])->select(DB::raw('sum(total) as total1'))->get();

			$cnt[0]->target_buyers = 0;
			$cnt[0]->no_leads = (int)$cnt1[0]->no_leads;
			$cnt[0]->target_leads = 0;
			$cnt[0]->no_orders = (int)$cnt3[0]->no_orders;
			$cnt[0]->target_orders = 0;
			$cnt[0]->total_sales = (int)$cnt4[0]->total;
			$cnt[0]->target_sales = (int)$cnt5[0]->total1;

			$status =1;
			$message ='Data retrieved successfully';
		}
		catch(Exception $e){
			$message = $e->getMessage();
		}
		return json_encode(['Status'=>$status,'Message'=>$message,'dashData'=>$cnt[0]]);
	}



	public function buyerDashboard($data){
		try{
			$status =0;
			$offset = 10;
			$result = array();
			$cnt = array();
			$user_id = Token::where(['module_id'=>$data['module_id'],'access_token'=>$data['access_token']])->pluck('user_id');
			$buyer_id = $data['buyer_id'];

			if($data['type'] == 'liquidate'){
				$result = Field::where(['user_id'=>$user_id,'buyer_id'=>$buyer_id,'activity_name'=>'liquidate'])->get(['activity_date as liquidate_date','total as sold']);	
			}
			if($data['type'] == 'orders'){
				$result = DB::table('eseal_orders as eo')
							->join('master_lookup as ml','ml.value','=','eo.order_status_id')
							->where(['buyer_id'=>$buyer_id,'eo.created_by'=>$user_id])
							->get(['order_number','total as order_value','ml.name as status','date_modified as date']);
			}
			$queries=DB::getQueryLog();

			if($data['type'] == 'reports'){
				$result = Exc::where(['buyer_id'=>$buyer_id,'created_by'=>$user_id])->orderBy('created_date', 'DESC')->get();
			}
			//print_r(end($queries));exit;
			$status =1;
			$message ='Data retrieved successfully';
		}
		catch(Exception $e){
			$message = $e->getMessage();
		}
		return json_encode(['Status'=>$status,'Message'=>$message,'dashData'=>$result]);
	}

	
	public function getReports($data){
		try{
			$status =0;
			$arr = array();
			$user_id = Token::where(['module_id'=>$data['module_id'],'access_token'=>$data['access_token']])->pluck('user_id');
			if(!isset($data['buyer_id'])){
			   $exc = Exc::where('created_by',$user_id)->orderBy('created_date', 'DESC')->get();
			}
			else{
			   $exc = Exc::where(['buyer_id'=>$data['buyer_id'],'created_by'=>$user_id])->orderBy('created_date', 'DESC')->get();
			}
	
		   if(!empty($exc[0])){
				foreach($exc as $ex){
				$shop_name = Location::where('location_id',$ex->buyer_id)->pluck('location_name');
					$ex['buyer_name'] = $shop_name;
					array_push($arr,$ex);
			}
			$status =1;
			$message = 'Data retrieved successfully';
		   }
		   else{
			throw new Exception('Data not found.');
		   }
		}
		catch(Exception $e){
			$message = $e->getMessage();
		}
		return json_encode(['Status'=>$status,'Message'=>$message,'reportData'=>$arr]);
	}

	public function getIdReports(){
		try{
			$status =1;			
			$message = 'Data retrieved successfully';

			$location_id = trim(Input::get('location_id'));
			$pid = trim(Input::get('pid'));
			$damage = trim(Input::get('damaged'));
			$excess = trim(Input::get('excess'));
			
			

			

		   
		  
		}
		catch(Exception $e){
			$status =0;
			$message = $e->getMessage();
		}
		return json_encode(['Status'=>$status,'Message'=>$message,'reportData'=>$arr]);
	}

	
	public function saveException($data){
	try{
		Log::info($data);
		$status =0;
		$image1 = '';
		$image2 = '';
		$image3 = '';
		$destinationPath = 'uploads/fm'; 
		$user_id = Token::where(['module_id'=>$data['module_id'],'access_token'=>$data['access_token']])->pluck('user_id');

		if(!isset($data['buyer_id']))
			$buyer_id = '';
		else
			$buyer_id = $data['buyer_id'];
		if(!isset($data['eseal_id']))
			$eseal_id ='';
		else
			$eseal_id = $data['eseal_id'];

		$image1 = Input::file('image1');
		if(!empty($image1)){
			
			$extension = Input::file('image1')->getClientOriginalExtension(); 
			$fileName = rand(11111,99999).'.'.$extension; 
			Input::file('image1')->move($destinationPath, $fileName); 
			$image1 = 'http://'.$_SERVER['SERVER_NAME'].'/'.$destinationPath.'/'.$fileName;
		}  
		$image2 = Input::file('image2');
		if(!empty($image2)){
			
			$extension = Input::file('image2')->getClientOriginalExtension(); 
			$fileName = rand(11111,99999).'.'.$extension; 
			Input::file('image2')->move($destinationPath, $fileName); 
			$image2 = 'http://'.$_SERVER['SERVER_NAME'].'/'.$destinationPath.'/'.$fileName;
		}  

		$image3 = Input::file('image3');
		if(!empty($image3)){
			
			$extension = Input::file('image3')->getClientOriginalExtension(); 
			$fileName = rand(11111,99999).'.'.$extension; 
			Input::file('image3')->move($destinationPath, $fileName); 
			$image3 = 'http://'.$_SERVER['SERVER_NAME'].'/'.$destinationPath.'/'.$fileName;
		}  
		

		$exc = new Exc;
		$exc->buyer_id = $buyer_id;
		$exc->eseal_id = $eseal_id;
		$exc->exception_type = $data['exception_type'];
		$exc->exception_name = $data['exception_name'];
		$exc->description = $data['description'];
		$exc->created_date = $data['created_date'];
		$exc->created_by =  $user_id;
		$exc->image1 = $image1;
		$exc->image2 = $image2;
		$exc->image3 = $image3;
		$exc->status = $data['status'];
		$exc->save();

		$status =1;
		$message ='Report saved successfully';

	}
	catch(Exception $e){
		$message  = $e->getMessage();
	}
	return json_encode(['Status'=>$status,'Message'=>$message]);
}



	public function getAttributeList($data){
		try{
			$status = 0;
			$attr = array();
			$pid = $data['pid'];
			if(empty($pid))
				throw new Exception('Parameters Missing.');
			$product = Products::where('product_id',$pid)->get();
			if(!empty($product[0])){
				$status = 1;
				$attr = DB::table('product_attributes')				        
							->join('attributes','attributes.attribute_id','=','product_attributes.attribute_id') 
							->where(array('product_attributes.product_id'=>$pid,'attributes.attribute_type'=> 1))
							->select('attributes.name','product_attributes.value')
							->get();
				$details = DB::table('products')
							->where('product_id',$pid)
							->get(array('product_id','name','title','description','category_id','image','mrp','sku','model_name'));
				$prodData = array('product_details'=>$details,'product_attributes'=>$attr);
				$message= 'Data retrieved successfully.';
			}
			else{
				throw new Exception('In-valid product.');
			}
		}
		catch(Exception $e){
			$message = $e->getMessage();
		}
		return json_encode(['Status'=>$status,'Message'=>$message,'attrData'=>$prodData]);
	}

		public function getCategories($data){
		try{
			$status =0;
			$ctgs = array();
			$mfg_id= $this->roleAccess->getMfgIdByToken($data['access_token']);
			if(isset($data['is_sub']) && $data['is_sub'] == 1){
				$category_id = $data['category_id'];
				$categories= DB::table('categories')
				->join('customer_categories','customer_categories.category_id','=','categories.category_id')
				->where(array('customer_categories.customer_id'=>$mfg_id,'customer_categories.category_id'=>$category_id))
				->get();
				
				if(!empty($categories[0])){
					$categories1= DB::table('categories')
					->join('customer_categories','customer_categories.category_id','=','categories.category_id')
					->where(array('customer_categories.customer_id'=>$mfg_id,'categories.parent_id'=>$categories[0]->category_id))
					->get(array('categories.category_id','categories.name'));

					if(empty($categories1[0])){
						throw new Exception('There are no sub-categories');
					}
					$arr = array();
					foreach($categories1 as $ctg1){
						
						$categories2= DB::table('categories')
						->join('customer_categories','customer_categories.category_id','=','categories.category_id')
						->where(array('customer_categories.customer_id'=>$mfg_id,'categories.parent_id'=>$ctg1->category_id))
						->get();
						
						$arr = array();
						foreach($categories2 as $ctg2){
							array_push($arr,array('category_id'=>$ctg2->category_id,'category_name'=>$ctg2->name,'category_image'=>$ctg2->image));	
						}
						array_push($ctgs,array('category_id'=>$ctg1->category_id,'category_name'=>$ctg1->name,'sub_category'=>$arr));
					}
					$status =1;
					$message ='Data retrieved successfully.';
				}
				else{
					throw new Exception('In-valid CategoryID.');
				}
			}
			else{
				$ctgs= DB::table('categories')
						->join('customer_categories','customer_categories.category_id','=','categories.category_id')
						->where(array('customer_categories.customer_id'=>$mfg_id,'categories.parent_id'=>0))
						->get(array('categories.category_id','name as category_name'));
				
				if(!empty($ctgs[0])){
					$status =1;
					$message ='Data retrieved succesfully.';
				}
				else{
					throw new Exception('Categories not found.');
				}
			}
		}
		catch(Exception $e){
			$message = $e->getMessage();
		}
		return json_encode(array('Status'=>$status,'Message'=>$message,'Data'=>$ctgs));
	}

	public function getProductsByCategory($data){
		try{
			$status =0;
			$prod = array();
			$mfg_id= $this->roleAccess->getMfgIdByToken($data['access_token']);
			$category_id = $data['category_id'];
			
			$categories = DB::table('categories')
							 ->join('customer_categories','customer_categories.category_id','=','categories.category_id')
							 ->where(array('customer_categories.category_id'=>$category_id,'customer_categories.customer_id'=>$mfg_id))
							 ->get();			
			if(empty($categories[0])){
				throw new Exception('In-valid CategoryID');
			}
			$result = DB::select('select * from products where find_in_set('.$category_id.',category_id) and manufacturer_id='.$mfg_id);
			if(!empty($result[0])){
				foreach($result as $res){
					
					$prodCollection = DB::table('products')
										 ->join('product_media','product_media.product_id','=','products.product_id')
										 ->where(array('products.product_id'=>$res->product_id,'product_media.media_type'=>'Image'))
										 ->get(array('products.product_id','products.name','products.title','products.description','products.sku','url as image','products.mrp','products.rating','products.weight','products.model_name'));
					
					$prodInfo = ['product_id'=>$prodCollection[0]->product_id,'name'=>$prodCollection[0]->name,'image'=>'http://dev2.esealinc.com/uploads/products/'.$prodCollection[0]->image,'sku'=>$prodCollection[0]->sku,'mrp'=>$prodCollection[0]->mrp,'weight'=>$prodCollection[0]->weight,'model'=>$prodCollection[0]->model_name,'rating'=>$prodCollection[0]->rating,'title'=>$prodCollection[0]->title,'description'=>$prodCollection[0]->description];
					
					$attributeCollection= DB::table('product_attributes')
											->join('attributes','attributes.attribute_id','=','product_attributes.attribute_id')
											->where(array('product_attributes.product_id'=>$res->product_id,'attributes.attribute_type'=>1))                             
											->get(array('attributes.name','product_attributes.value'));
					array_push($prod,array('product_info'=>$prodInfo,'attributes'=>$attributeCollection));
				}
				$status = 1;
				$message= 'Data retrieved successfully.';
			}
			else{
				throw new Exception('There are no products under this category.');
			}
		}
		catch(Exception $e){
			$message = $e->getMessage();
		}
		return json_encode(array('Status'=>$status,'Message'=>$message,'prodData'=>$prod));
	}

	public function getTpAttributeInfo($data){
		try{
			$status =0;
			$tpattr = array();
			$mfg_id= $this->roleAccess->getMfgIdByToken($data['access_token']);
			$location_id = $data['location_id'];
			
			if(empty($location_id)){	
			$tpattr= DB::table('location_tp_attributes')
			->join('attributes','attributes.attribute_id','=','location_tp_attributes.attribute_id')
			->where('location_tp_attributes.manufacturer_id',$mfg_id)
			->get(array('attributes.name','attributes.attribute_code','attributes.input_type','attributes.regexp','attributes.default_value','attributes.is_required','attributes.validation'));
		}
		else{
			$tpattr= DB::table('location_tp_attributes')
			->join('attributes','attributes.attribute_id','=','location_tp_attributes.attribute_id')
			->where('location_tp_attributes.location_id',$location_id)
			->get(array('attributes.name','attributes.attribute_code','attributes.input_type','attributes.regexp','attributes.default_value','attributes.is_required','attributes.validation'));
		  
		}
			if(!empty($tpattr)){
				$status =1;
				$message = 'Data retrieved succesfully.';
			}
			else
				throw new Exception('There are no TP attributes for this location.');
		}
		catch(Exception $e){
			$message = $e->getMessage();
		}
		return json_encode(array('Status'=>$status,'Message'=>'Server: '.$message,'tpData'=>$tpattr));
	}

	public function sendLogEmail(){
	  try{
		   $status = 0;
		   $sub = Input::get('subject');
		   $body = Input::get('body');

		   if(!empty($sub) && !empty($body)){
				$status1 = Mail::send('emails.tracker',array('body' => $body), function($message) use ($sub)
				{
					$message->to('app.support@ebutor.com')->subject($sub);
				});

				if($status1)
					throw new Exception('couldnt send email tho the email id '.$email);            
				else{
					$status =1;
					$message = 'A log mail has been successfully sent to the tracker team.';         
				}
			}
			else{
				throw new Exception ('Parameters are empty.');
			}
	  }
	  catch(Exception $e){
		$message = $e->getMessage();
	  }
	  return json_encode(['Status'=>$status,'Message'=>$message]);
	 }

	public function forgotPassword($data){
		try{
                       // Log::info(__FUNCTION__.' : '.print_r(Input::all(),true));
			$status =0;
			$otp = '';
			$email = '';
			$username = $data['username'];
			if(empty($username))
				throw new Exception('Paramaters Missing');
			$email = User::where('username',$username)->pluck('email');
//Log::info('email id'); Log::info($email);
			if(empty($email))
				throw new Exception('There is no user with the given username or the email is not configured');

			//$email = $email[0]->email;
			if(!empty($email)){
				$cnt = User::where('email',$email)->count();
				if($cnt > 1)
					throw new Exception('There are multiple users with same email id :'.$email);

				$length =6;
				$otp="";
				for($i=1; $i<=$length; $i++)
				{
					mt_srand((double)microtime() * 1000000);
					$num = mt_rand(1,36);
					$otp .= $this->roleAccess->assign_rand_value($num);
				}
				User::where('email',$email)->update(array('otp'=>$otp));	
				$fields = array('otp' => $otp, 'email' => $email);
				$status1 = \Mail::send('emails.reset', $fields, function($message) use ($email)
				{
					$message->to($email);
				});
				if($status1)
					throw new Exception('couldnt send email tho the email id '.$email);            
				else{
					$status =1;
					$message = 'An OTP has been sent to the email id '.$email ;         
				}
			}
			else{
				throw new Exception ('In-valid Email-Id.');
			}
		}
		catch(Exception $e){
			$message = $e->getMessage();
		}
		return json_encode(['Status'=>$status,'Message'=>$message,'OTP'=>$otp,'Email'=>$email]);
	}

	public function resetPassword($data){
		try{
			$status =0;
			$otp = $data['otp'];
			$password = $data['password'];
			if(empty($otp) || empty($password))
				throw new Exception('Parameters Missing.');
			$user = User::where('otp',$otp)->first();
			if(!empty($user)){
				$user->password = md5($password);
				$user->erp_password = $password;
				$user->erp_username = $user->username;
				$user->otp = NULL;
				$user->save();
				$status =1;
				$message ='Password changed successfully.';
			}
			else{
				throw new Exception('In-valid OTP.');
			}
		}
		catch(Exception $e){
			$message = $e->getMessage();
		}
		return json_encode(['Status'=>$status,'Message'=>$message]);
	}


private function getProductInfoFromSecondary($manufacturer_id,$eseal_id,$role_id){

		
		$prodAttrs = array();
		if(is_array($eseal_id)){
			$result =DB::select('select pid,attribute_map_id,group_concat(primary_id) as ids,level_id from eseal_'.$manufacturer_id.' where primary_id IN (' . implode(',', array_map('intval', $eseal_id)). ') group by pid,attribute_map_id,level_id');
		}
		else{
			$result =DB::select('select pid,attribute_map_id,group_concat(primary_id) as ids,level_id from eseal_'.$manufacturer_id.' where parent_id='.$eseal_id.' group by pid,attribute_map_id');
		}
		$prod= array();
		foreach($result as $res){
		 
		
			$ids = explode(",",$res->ids);
			$finAttr = array();
			$compAttr = array();
			foreach($ids as $id){

			$attributeMap = DB::table('bind_history')->where('eseal_id',$id)->groupBy('location_id')->select(['location_id',DB::raw('GROUP_CONCAT(DISTINCT  attribute_map_id) as attribute_map_id'),'created_on'])->get();
			if(!empty($attributeMap)){
			foreach($attributeMap as $map){
			$locAttr = array();
			$map1 = explode(',',$map->attribute_map_id); 
			$location_name = Location::where('location_id',$map->location_id)->pluck('location_name');
			$attributes = DB::table('attribute_mapping as am')
						   ->join('attributes as attr','attr.attribute_id','=','am.attribute_id');
						   	if($role_id){

			$attributes->join('inspect_role_attribute as ira','ira.attribute_id','=','attr.attribute_id')
					            ->where('ira.role_id',$role_id);

				}		
			$attributes = $attributes->whereIn('am.attribute_map_id',$map1)
						             ->get(['attr.name','am.value']);
			if(!empty($attributes)){
				foreach($attributes as $attribute){
					$locAttr[] = ['name'=>$attribute->name,'value'=>$attribute->value];
				}
				$finAttr[] = ['location_name' =>$location_name,'attributes'=>$locAttr,'created_on'=>$map->created_on];
			}               
		}
}

			}
			$prodCollection = DB::table('products')->where('product_id',$res->pid)->get();
			if($res->level_id ==1){
			$qty = DB::select('select count(*) as qty from eseal_'.$manufacturer_id.' where(parent_id IN (' . implode(',', array_map('intval', $ids)). ') and pid='.$res->pid.')');
			$qty = $qty[0]->qty;

			}
			else{
				$qty = count($ids);
			}
			$prodInfo = ['Product Id'=>$prodCollection[0]->product_id,'Name'=>$prodCollection[0]->name,'Title'=>$prodCollection[0]->title,'Description'=>$prodCollection[0]->description,'Qty'=>intval($qty),'Eseal Id'=>$code];
			
			foreach($prodInfo as $key=>$value){
			$prodInfo1[] = ['name'=>$key,'value'=>$value];
		}
			$image = 'http://'.$_SERVER['SERVER_NAME'].'/uploads/products/'.$prodCollection[0]->image;
			$attribute_map_id = $res->attribute_map_id;
			
			$Eseal_id =DB::table('attribute_mapping as am')
								->join('attributes as a','a.attribute_id','=','am.attribute_id');
							if($role_id){

			$Eseal_id->join('inspect_role_attribute as ira','ira.attribute_id','=','a.attribute_id')
					            ->where('ira.role_id',$role_id);

				}				
			$Eseal_id = $Eseal_id->where('am.attribute_map_id',$attribute_map_id)
								 ->where('a.default_value','=','DYNAMIC')
								 ->get(['a.name','am.value']);
			foreach($Eseal_id as $id){
			$attributeMap = DB::table('bind_history')->where('eseal_id',$id->value)->groupBy('location_id')->groupBy('attribute_map_id')->get(['location_id','attribute_map_id']);
			if(!empty($attributeMap)){
			foreach($attributeMap as $map){
			$locAttr1 =array();
			$location_name = Location::where('location_id',$map->location_id)->pluck('location_name');
			$attributes = DB::table('attribute_mapping as am')
						   ->join('attributes as attr','attr.attribute_id','=','am.attribute_id');
						   	if($role_id){

			$attributes->join('inspect_role_attribute as ira','ira.attribute_id','=','attr.attribute_id')
					            ->where('ira.role_id',$role_id);

				}		
			$attributes = $attributes->where('am.attribute_map_id',$map->attribute_map_id)
						             ->get(['attr.name','am.value']);
			if(!empty($attributes)){
				$vendor = DB::table('attribute_mapping as am')
								->join('attributes as a','a.attribute_id','=','am.attribute_id')
								->where('am.attribute_map_id',$map->attribute_map_id)
								->where('a.attribute_code','vendor_code')
								->get(['am.value']);
				$vendor_code ='';
				$vendor_name ='';
				if(!empty($vendor)){
					$vendor_code = $vendor[0]->value;
					$vendor_name = Location::where('erp_code',$vendor_code)->pluck('location_name');
				}        
				foreach($attributes as $attribute){
					$locAttr1[] = ['name'=>$attribute->name,'value'=>$attribute->value];
				}
				$compAttr[] = ['component_name'=>$id->name,'location_name' =>$location_name,'vendor_code'=>$vendor_code,'vendor_name'=>$vendor_name,'attributes'=>$locAttr1];
			}               
		}	
		}					
}
			

			$prodAttr = array();
			
			$attributeCollection=DB::table('attribute_mapping as am')
									->join('attributes as a','a.attribute_id','=','am.attribute_id');
										if($role_id){

			$attributeCollection->join('inspect_role_attribute as ira','ira.attribute_id','=','a.attribute_id')
					            ->where('ira.role_id',$role_id);

				}		
			$attributeCollection = $attributeCollection->where('am.attribute_map_id',$attribute_map_id)
									                   ->get(['a.name','am.value','am.location_id']);

			$mapped_date = DB::table('attribute_mapping')->where('attribute_map_id',$attribute_map_id)->pluck('mapping_date');						

			foreach($attributeCollection as $attribute){
			  array_push($prodAttr,array('name'=>$attribute->name,'value'=>$attribute->value));
			}
			$location = Location::where('location_id',$attribute->location_id)->get();
			array_push($prodAttr,array('name'=>'LOCATION_NAME','value'=>$location[0]->location_name));
                        array_push($prodAttr,array('name'=>'Name','value'=>$prodCollection[0]->name));
			array_push($prodAttr,array('name'=>'Eseal Id','value'=>$code));
			
			$attributeCollection=DB::table('product_attributes')
									->join('attributes','attributes.attribute_id','=','product_attributes.attribute_id');
							if($role_id){

			$attributeCollection->join('inspect_role_attribute as ira','ira.attribute_id','=','attributes.attribute_id')
					            ->where('ira.role_id',$role_id);

				}				
			$attributeCollection = $attributeCollection->where(['product_attributes.product_id'=>$res->pid,'attributes.attribute_type'=>1]) 
									->where('product_attributes.value','!=','')                            
									->get(['attributes.name','product_attributes.value']);
			
			if(!empty($attributeCollection)){                
				foreach($attributeCollection as $attribute){
					$prodAttrs[] =['name'=>$attribute->name,'value'=>$attribute->value];
				}
			}
			
			array_push($prod,['product_info'=>$prodInfo1,'image'=>$image,'product_attributes'=>$prodAttr,'location_attributes'=>$finAttr,'component_attributes'=>$compAttr,'other_attributes'=>$prodAttrs]);
		}
		return $prod;
	}

	private function getProductInfoFromAttributeMap($pid,$attribute_map_id,$eseal_id='',$manufacturer_id,$code,$role_id){
//Log::info('role id is:'.$role_id);
		$finAttr = array();
		$attributeMap = DB::table('bind_history')->where('eseal_id',$code)->groupBy('location_id')->select(['location_id',DB::raw('GROUP_CONCAT(DISTINCT  attribute_map_id) as attribute_map_id'),'created_on'])->get();
		$batch_no = DB::table('eseal_'.$manufacturer_id)->where('primary_id',$code)->pluck('batch_no');
	
	if(!empty($attributeMap)){	
		foreach($attributeMap as $map){
			$locAttr = array();
			$map1 = explode(',',$map->attribute_map_id); 
			$location_name = Location::where('location_id',$map->location_id)->pluck('location_name');
			
			$attributes = DB::table('attribute_mapping as am')
						   ->join('attributes as attr','attr.attribute_id','=','am.attribute_id');
				if($role_id){

					$attributes->join('inspect_role_attribute as ira','ira.attribute_id','=','attr.attribute_id')
					           ->where('ira.role_id',$role_id);

				}		   
			 $attributes = $attributes->whereIn('am.attribute_map_id',$map1)
						              ->get(['attr.name','am.value']);
			if(!empty($attributes)){
				foreach($attributes as $attribute){
					$locAttr[] = ['name'=>$attribute->name,'value'=>$attribute->value];
				}
				$finAttr[] = ['location_name' =>$location_name,'attributes'=>$locAttr,'created_on'=>$map->created_on];
			}               
		
		}
  }
	// Log::info('almost compattr');
		$compAttr = array();
		
		
		$level =  DB::table('eseal_'.$manufacturer_id)->where('primary_id',$code)->pluck('level_id');
		if($level == 0){
		/*$Eseal_id =DB::table('attribute_mapping as am')
								->join('attributes as a','a.attribute_id','=','am.attribute_id')
								->where('am.attribute_map_id',$attribute_map_id)
								->where('a.default_value','=','DYNAMIC')
								
								->get(['a.name','am.value']);*/
		$Eseal_id = DB::table('eseal_'.$manufacturer_id.' as eseal')
						  ->join('products','products.product_id','=','eseal.pid')
						  ->where('eseal.parent_id',$code)
						  ->get(['primary_id','name']);
		if(!empty($Eseal_id)){
		foreach($Eseal_id as $id){
		$attributeMap = DB::table('bind_history')->where('eseal_id',$id->primary_id)->groupBy('location_id')->groupBy('attribute_map_id')->get(['location_id','attribute_map_id']);
 if(!empty($attributeMap)){
		foreach($attributeMap as $map){
			$location_name = Location::where('location_id',$map->location_id)->pluck('location_name');
			$attributes = DB::table('attribute_mapping as am')
						   ->join('attributes as attr','attr.attribute_id','=','am.attribute_id');
						   if($role_id){

					$attributes->join('inspect_role_attribute as ira','ira.attribute_id','=','attr.attribute_id')
					           ->where('ira.role_id',$role_id);

				}		
				$attributes = $attributes->where('am.attribute_map_id',$map->attribute_map_id)
						                 ->get(['attr.name','am.value']);

				
			if(!empty($attributes)){
 //Log::info('almost attributes');
				foreach($attributes as $attribute){
					$locAttr1[] = ['name'=>$attribute->name,'value'=>$attribute->value];
				}
				$vendor_code ='';
				$vendor_name ='';
				$vendor = DB::table('attribute_mapping as am')
								->join('attributes as a','a.attribute_id','=','am.attribute_id')
								->where('am.attribute_map_id',$map->attribute_map_id)
								->where('a.attribute_code','vendor_code')
								->get(['am.value']);
				if(!empty($vendor)){
					$vendor_code = $vendor[0]->value;
					$vendor_name = Location::where('erp_code',$vendor_code)->pluck('location_name');
				}           
				$compAttr[] = ['component_name'=>$id->name,'location_name' =>$location_name,'vendor_code'=>$vendor_code,'vendor_name'=>$vendor_name,'attributes'=>$locAttr1];
			}               
		}
		}						
}
}
}
		//Log::info('almost endddddd');
		 
		$prodAttrs = array();
		$prodCollection = DB::table('products')->where('product_id',$pid)->get();
		if(empty($eseal_id)){
			$qty =1;
		}
		else{
		 $qty = DB::select('select count(*) as qty from eseal_'.$manufacturer_id.' where parent_id='.$eseal_id);
		 $qty = $qty[0]->qty;
		}
		$prodInfo1= array();
		$prodInfo = ['Product Id'=>$prodCollection[0]->product_id,'Name'=>$prodCollection[0]->name,'Title'=>$prodCollection[0]->title,'Description'=>$prodCollection[0]->description,'Qty'=>intval($qty),'Eseal Id'=>$code,'batch_no'=>$batch_no];
		foreach($prodInfo as $key=>$value){
			array_push($prodInfo1,array('name'=>$key,'value'=>$value));
		}
		$image = 'http://'.$_SERVER['SERVER_NAME'].'/uploads/products/'.$prodCollection[0]->image;
		$attribute_map_id = $attribute_map_id;
		$prodAttr1['mapped_date'] = '';
		$prodAttr1['attributes'] = array();
		$prodAttr = array();
		$attributeCollection=DB::table('attribute_mapping as am')
								->join('attributes as a','a.attribute_id','=','am.attribute_id');
								if($role_id){

			$attributeCollection->join('inspect_role_attribute as ira','ira.attribute_id','=','a.attribute_id')
					            ->where('ira.role_id',$role_id);

				}
//Log::info('almost after enddd at enddd');		
		$attributeCollection = $attributeCollection->where('am.attribute_map_id',$attribute_map_id)
								                   ->get(['a.name','am.value','am.location_id']);
		$mapped_date = DB::table('attribute_mapping')->where('attribute_map_id',$attribute_map_id)->pluck('mapping_date');						
		if(!empty($attributeCollection)){
		foreach($attributeCollection as $attribute){
			$location_id = $attribute->location_id;
			array_push($prodAttr,array('name'=>$attribute->name,'value'=>$attribute->value));
			}
		//Log::info('doneeeeeeee');
			$location = Location::where('location_id',$location_id)->get(['location_name']);
//Log::info($location);
//Log::info($location_id);
//Log::info('steppp0');
			array_push($prodAttr,array('name'=>'LOCATION_NAME','value'=>$location[0]->location_name));
//                Log::info('steppp1');
                        array_push($prodAttr,array('name'=>'Name','value'=>$prodCollection[0]->name));
//Log::info('steppp2');
			array_push($prodAttr,array('name'=>'Eseal Id','value'=>$code));
//		Log::info('xxxxxxxxxxxxxxxxxxxx');
		$prodAttr1['mapped_date'] = $mapped_date;
		$prodAttr1['attributes'] = $prodAttr;
		
	}
//Log::info('after doneeee');
		$attributeCollection=DB::table('product_attributes')
							->join('attributes','attributes.attribute_id','=','product_attributes.attribute_id');
							if($role_id){

			$attributeCollection->join('inspect_role_attribute as ira','ira.attribute_id','=','attributes.attribute_id')
					            ->where('ira.role_id',$role_id);

				}		
	$attributeCollection = $attributeCollection->where(array('product_attributes.product_id'=>$pid,'attributes.attribute_type'=>1))                             
							->where('product_attributes.value','!=','')                            
							->get(['attributes.name','product_attributes.value']);
		
		if(!empty($attributeCollection)){
			foreach($attributeCollection as $attribute){
				$prodAttrs[] = ['name'=>$attribute->name,'value'=>$attribute->value];
			}
		}
		
		return array('product_info'=>$prodInfo1,'image'=>$image,'product_attributes'=>$prodAttr1,'location_attributes'=>$finAttr,'component_attributes'=>$compAttr,'other_attributes'=>$prodAttrs);
	}

	public function getAdjacentInfo($data)
	{		
		try
		{	
			$status =0;
			$eseal_id = $data['eseal_id'];
			$manufacturer_id= $this->roleAccess->getMfgIdByToken($data['access_token']);
			$user_id = $this->roleAccess->checkAccessToken($data['access_token'])[0]->user_id;
            $role_id = $this->roleAccess->getRolebyUserId($user_id)[0]->role_id;
			$wmsenabled =  DB::table('wms_entities')->where('org_id',$manufacturer_id)->pluck('id');
			$esealCollection=  DB::table('eseal_'.$manufacturer_id)->where('primary_id',$data['eseal_id'])->get();
			//print_r($esealCollection);exit;
			if(!empty($esealCollection[0]))
			{
				if($esealCollection[0]->level_id == 0)
				{
					//$main = "Level Info : \r\n\r\n";
					$main = "";
					$level1_id = $esealCollection[0]->parent_id;
					$level =  DB::table('eseal_'.$manufacturer_id)->where('primary_id',$level1_id)->pluck('level_id');
					$queries = DB::getQueryLog();
					//print_r(end($queries));exit;
					if($level1_id != 0)
					{
						if($level == 8)
						{
							$main .= "Pallet Id: " . $level1_id."\r\n";
						}
						else
						{
							$main .= "Level1 : " . $level1_id."\r\n";
						}
						$level1_Collection =DB::table('eseal_'.$manufacturer_id)->where('primary_id',$level1_id)->get();
						//print_r($level1_Collection);exit;
						$level2_id = $level1_Collection[0]->parent_id;
						if($level2_id != 0)
						{
							if(!$wmsenabled)
								$main .= "Level2: ". $level2_id."\r\n";  
							$tp = DB::table('tp_data')->where('level_ids',$level2_id)->get();
							if(!empty($tp[0]))
							{
								$main .= "TP : " . $tp[0]->tp_id."\r\n";
							}
							else
							{
								$main .= "TP : " ."\r\n";
							}                      
						}
						else
						{
							if(!$wmsenabled)
								$main .= "Level2 : " ."\r\n";

							$tp = DB::table('tp_data')->where('level_ids',$level1_id)->get();
							if(!empty($tp[0])){
								$main .= "TP : " . $tp[0]->tp_id."\r\n";
							}
							else
							{
								$main .= "TP : " ."\r\n";
							}
						}
					}
					else
					{
						$main .= "Level1 : " ."\r\n";
						$tp = DB::table('tp_data')->where('level_ids',$eseal_id)->get();
						if(!empty($tp[0])){
							$main .= "TP : " . $tp[0]->tp_id."\r\n";
						}
						else
						{
							$main .= "TP : " ."\r\n";
						}
					}					
					$pid = $esealCollection[0]->pid;
					$attribute_map_id = $esealCollection[0]->attribute_map_id;
					$productCollection=  Products::where('product_id',$pid)->get(['name','mrp']);
					$main .= "Product Info : \r\n\r\n";
					$main .= "Name : " .$productCollection[0]->name."\r\n";
					if(!$wmsenabled){
						$main .= "MRP : " .$productCollection[0]->mrp."\r\n";
					}
					else
					{
						/*$bin_location = DB::table('eseal_'.$manufacturer_id)->where('primary_id',$level1_id)->get(['bin_location','pkg_qty']);
						$main .= "Bin Location : " .$bin_location[0]->bin_location."\r\n";*/
						//$main .= "Quantity : " .$bin_location[0]->pkg_qty."\r\n";
					}
					//$main .= "Batch Number : " .$esealCollection[0]->batch_no."\r\n";
					$main .= "Date Of Manufacturing : " .$esealCollection[0]->mfg_date."\r\n";
					$manufacturer_name = DB::table('eseal_customer')->where('customer_id',$productCollection[0]->manufacturer_id)->pluck('brand_name');
					$attributeCollection = DB::table('attribute_mapping')->where('attribute_map_id',$attribute_map_id)->get();   
					foreach($attributeCollection as $attribute)
					{
						$main .= $attribute->attribute_name." : ".$attribute->value."\r\n";
					}
				
				}
				if($esealCollection[0]->level_id == 1)
				{
					$main = "Level Info : \r\n\r\n";
					$level2_id = $esealCollection[0]->parent_id;
					if($level2_id != 0)
					{
						$main .= "Level2 : " . $level2_id."\r\n";					   
						$tp = DB::table('tp_data')->where('level_ids',$level2_id)->get();
						if(!empty($tp[0]))
						{
							$main .= "TP : " . $tp[0]->tp_id."\r\n";
						}
						else
						{
							$main .= "TP : " ."\r\n";
						}
					}
					else
					{
						$main .= "Level2 : " ."\r\n";
						$tp = DB::table('tp_data')->where('level_ids',$eseal_id)->get();
						if(!empty($tp[0]))
						{
							$main .= "TP : " . $tp[0]->tp_id."\r\n";
						}
						else
						{
							$main .= "TP : " ."\r\n";
						}
					}
					$pid = $esealCollection[0]->pid;
					if(empty($pid))
					{
						$result =DB::select('select pid,attribute_map_id from eseal_'.$manufacturer_id.' where parent_id='.$eseal_id.' group by pid,attribute_map_id');
						foreach($result as $res)
						{
							$i= 1;
							$productCollection=  Products::where('product_id',$res->pid)->get(['name','mrp']);
							$main .= "Product Info".$i." : \r\n\r\n";
							$main .= "Name : " .$productCollection[0]->name."\r\n";
							$main .= "MRP : " .$productCollection[0]->mrp."\r\n";
							$main .= "Batch Number : " .$esealCollection[0]->batch_no."\r\n";
							$main .= "Date Of Manufacturing : " .$esealCollection[0]->mfg_date."\r\n";

							$manufacturer_name = DB::table('eseal_customer')->where('customer_id',$productCollection[0]->manufacturer_id)->pluck('brand_name');
							$attributeCollection = DB::table('attribute_mapping')->where('attribute_map_id',$res->attribute_map_id)->get();
							foreach($attributeCollection as $attribute)
							{
								$main .= $attribute->attribute_name." : ".$attribute->value."\r\n";
							}
							$i++;
						}
					}
					else
					{
						$attribute_map_id = $esealCollection[0]->attribute_map_id;
						$productCollection=  Products::where('product_id',$pid)->get(['name','mrp']);

						$main .= "Product Info : \r\n\r\n";

						$main .= "Name : " .$productCollection[0]->name."\r\n";
						$main .= "MRP : " .$productCollection[0]->mrp."\r\n";
						$main .= "Batch Number : " .$esealCollection[0]->batch_no."\r\n";
						$main .= "Date Of Manufacturing : " .$esealCollection[0]->mfg_date."\r\n";

						$manufacturer_name = DB::table('eseal_customer')->where('customer_id',$productCollection[0]->manufacturer_id)->pluck('brand_name');
						$attributeCollection = DB::table('attribute_mapping')->where('attribute_map_id',$attribute_map_id)->get();   

						foreach($attributeCollection as $attribute)
						{
							$main .= $attribute->attribute_name." : ".$attribute->value."\r\n";
						}
					}
				}
				if($esealCollection[0]->level_id == 8)
				{
					$main = "Level Info : \r\n\r\n";
					//$level2_id = $esealCollection[0]->parent_id;
					/*if($level2_id != 'unknown'){
						$main .= "Level2 : " . $level2_id."\r\n";
					   
					   $tp = DB::table('tp_data')->where('level_ids',$level2_id)->get();
						if(!empty($tp[0]))
						{
							$main .= "TP : " . $tp[0]->tp_id."\r\n";
						}
						else
						{
							$main .= "TP : " ."\r\n";
						}
					}
					else
					{
						$main .= "Level2 : " ."\r\n";
						$tp = DB::table('tp_data')->where('level_ids',$eseal_id)->get();
						if(!empty($tp[0]))
						{
							$main .= "TP : " . $tp[0]->tp_id."\r\n";
						}
						else
						{
							$main .= "TP : " ."\r\n";
						}
					}*/
					$pid = $esealCollection[0]->pid;
					if(empty($pid))
					{
						$result = DB::select('select pid,attribute_map_id from eseal_'.$manufacturer_id.' where parent_id='.$eseal_id.' group by pid,attribute_map_id');
						foreach($result as $res)
						{
							$i= 1;
							$productCollection=  Products::where('product_id',$res->pid)->get(['name','mrp']);
							$main .= "Product Info".$i." : \r\n\r\n";
							$main .= "Name : " .$productCollection[0]->name."\r\n";
							$main .= "MRP : " .$productCollection[0]->mrp."\r\n";
							$main .= "Batch Number : " .$esealCollection[0]->batch_no."\r\n";
							$main .= "Date Of Manufacturing : " .$esealCollection[0]->mfg_date."\r\n";

							$manufacturer_name = DB::table('eseal_customer')->where('customer_id',$productCollection[0]->manufacturer_id)->pluck('brand_name');
							$attributeCollection = DB::table('attribute_mapping')->where('attribute_map_id',$res->attribute_map_id)->get();   

							foreach($attributeCollection as $attribute)
							{
								$main .= $attribute->attribute_name." : ".$attribute->value."\r\n";
							}
							$i++;
						}
					}
					else
					{
						$attribute_map_id = $esealCollection[0]->attribute_map_id;
						$bin_location  = $esealCollection[0]->bin_location;
						$productCollection=  Products::where('product_id',$pid)->get(['name']);

						$main .= "Pallet Info : \r\n\r\n";

						$main .= "Name : " .$productCollection[0]->name."\r\n";

						$main .= "Bin Location : " .$bin_location."\r\n";
						$bin_location = DB::table('eseal_'.$manufacturer_id)->where('primary_id',$eseal_id)->get(['bin_location','pkg_qty']);
						$main .= "Quantity : " .$bin_location[0]->pkg_qty."\r\n";
						//$main .= "MRP : " .$productCollection[0]->mrp."\r\n";
						//$main .= "Batch Number : " .$esealCollection[0]->batch_no."\r\n";
						//$main .= "Date Of Manufacturing : " .$esealCollection[0]->mfg_date."\r\n";

						$manufacturer_name = DB::table('eseal_customer')->where('customer_id',$manufacturer_id)->pluck('brand_name');
						$attributeCollection = DB::table('attribute_mapping')->where('attribute_map_id',$attribute_map_id)->get();   

						foreach($attributeCollection as $attribute)
						{
							$main .= $attribute->attribute_name." : ".$attribute->value."\r\n";
						}
					}
					$childs = DB::table('eseal_'.$manufacturer_id)->where('parent_id',$eseal_id)->get(['primary_id','pid']);
					$main .= "Child Info : \r\n\r\n";
					if(!empty($childs))
					{
						foreach($childs as $child)
						{
							$name =  Products::where('product_id',$child->pid)->pluck('name');
							$main .= ' '.$child->primary_id.'  :  '.$name."\r\n";
					   }
					}
				}
				if($esealCollection[0]->level_id == 2)
				{
					$main = "Level Info : \r\n\r\n";
					$main .= "Level1 : " ."\r\n";
					$level1_ids = DB::table('eseal_'.$manufacturer_id)->where('parent_id',$eseal_id)->get(['primary_id']);
					foreach($level1_ids as $child)
					{
						$main .= '  '.$child->primary_id."\r\n";
					}
					$tp = DB::table('tp_data')->where('level_ids',$eseal_id)->get();
					if(!empty($tp[0]))
					{
						$main .= "TP : " . $tp[0]->tp_id."\r\n";
					}
					else
					{
						$main .= "TP : " ."\r\n";
					}
					$pid = $esealCollection[0]->pid;					
					if(empty($pid))
					{
						foreach($level1_ids as $id)
						{
							$ids[] = $id->primary_id;
						}						
						$result =DB::select('select pid,attribute_map_id from eseal_'.$manufacturer_id.' where parent_id in (' . implode(',', array_map('intval', $ids)). ') group by pid,attribute_map_id');
						foreach($result as $res)
						{
							$i= 1;
							$productCollection=  Products::where('product_id',$res->pid)->get(['name','mrp']);
							$main .= "Product Info".$i." : \r\n\r\n";
							$main .= "Name : " .$productCollection[0]->name."\r\n";
							$main .= "MRP : " .$productCollection[0]->mrp."\r\n";
							$main .= "Batch Number : " .$esealCollection[0]->batch_no."\r\n";
							$main .= "Date Of Manufacturing : " .$esealCollection[0]->mfg_date."\r\n";

							$manufacturer_name = DB::table('eseal_customer')->where('customer_id',$productCollection[0]->manufacturer_id)->pluck('brand_name');
							$attributeCollection = DB::table('attribute_mapping')->where('attribute_map_id',$res->attribute_map_id)->get();   

							foreach($attributeCollection as $attribute)
							{
								$main .= $attribute->attribute_name." : ".$attribute->value."\r\n";
							}
							$i++;
						}
					}
					else
					{
						$attribute_map_id = $esealCollection[0]->attribute_map_id;
						$productCollection=  Products::where('product_id',$pid)->get(['name','mrp']);
						$main .= "Product Info : \r\n\r\n";

						$main .= "Name : " .$productCollection[0]->name."\r\n";
						$main .= "MRP : " .$productCollection[0]->mrp."\r\n";
						$main .= "Batch Number : " .$esealCollection[0]->batch_no."\r\n";
						$main .= "Date Of Manufacturing : " .$esealCollection[0]->mfg_date."\r\n";

						$manufacturer_name = DB::table('eseal_customer')->where('customer_id',$productCollection[0]->manufacturer_id)->pluck('brand_name');
						$attributeCollection = DB::table('attribute_mapping')->where('attribute_map_id',$attribute_map_id)->get();   

						foreach($attributeCollection as $attribute)
						{
							$main .= $attribute->attribute_name." : ".$attribute->value."\r\n";
						}
					}
				}
				$main .= "Trace Info : \r\n\r\n";

				if($role_id == 418)
                      goto jump;

				$query = DB::table('track_details as td')
								->join('track_history as th','th.track_id','=','td.track_id')
								->join('transaction_master as tr','tr.id','=','th.transition_id')
								->where('code',$eseal_id);
				$cnt = DB::table('eseal_'.$manufacturer_id)->where('parent_id',$eseal_id)->count();			
				if($esealCollection[0]->level_id == 8)
				{
					if($cnt)
					{
						$query->orderBy('th.update_time','asc')->distinct();	
					}
					else
					{
						$query->where('tr.name','Pallet Placement')->orderBy('th.update_time','asc')->take(1);	
					}
				}
				$track = $query->get(['tr.name','th.update_time','th.src_loc_id','th.dest_loc_id']); 

				foreach($track as $tr1)
				{
					$main .= ' '.$tr1->update_time.'  :  '.$tr1->name;
					if(!empty($tr1->src_loc_id))
					{					
						$loc1 = DB::table('locations')
						->where('location_id',$tr1->src_loc_id)
						->get(['location_name']);

						if(!empty($tr1->dest_loc_id))
							$main .= ' : '. $loc1[0]->location_name;
					
						else
							$main .= ' : '. $loc1[0]->location_name."\r\n";
					} 
					if(!empty($tr1->dest_loc_id))
					{

						$loc2 = DB::table('locations')
						->where('location_id',$tr1->dest_loc_id)
						->get(['location_name']);
						$main .= '--'.$loc2[0]->location_name."\r\n";         
					}  
				}
				jump:
				$status =1;
				return json_encode(['Status'=>$status,'Message'=>"\r\n".$main."\r\n",'Product Name'=>$productCollection[0]->name,'Manufacturer Name'=>$manufacturer_name,'Image'=>$productCollection[0]->image]);
			}
			$tpCollection = DB::table('tp_data')->where('tp_id',$eseal_id)->get(['level_ids']);			
			if(!empty($tpCollection[0]) ) 
			{
				$main = "Transit Info : \r\n\r\n";
				//$tpattr = DB::table('tp_attributes')->where('tp_id',$eseal_id)->get();
				$tpdetails =DB::select('select * from track_history th join track_details td on th.track_id=td.track_id join transaction_master tr on tr.id=th.transition_id where td.code='.$eseal_id.' order by th.update_time limit 1');
				
				$src_name =DB::table('locations')->where('location_id',$tpdetails[0]->src_loc_id)->pluck('location_name');
				$dest_name = DB::table('locations')->where('location_id',$tpdetails[0]->dest_loc_id)->pluck('location_name');
				$main .= "Source : " .$src_name."\r\n";
				$main .= "Destination : " .$dest_name."\r\n";

				$main .= "Track Info : \r\n\r\n"; 
				$main .= ' '.$tpdetails[0]->update_time.'  :  '.$tpdetails[0]->name.'  :  '.$src_name.'----'.$dest_name."\r\n";
				
				$main .= "Child List : \r\n\r\n"; 
				foreach($tpCollection as $child)
				{
					$main .= '  '.$child->level_ids."\r\n";
				}
				$main .= "Tp Attributes : \r\n\r\n"; 
				$tp_attributes = DB::table('tp_attributes')->where('tp_id',$eseal_id)->get(['attribute_name','value','location_id']);
				if(!empty($tp_attributes))
				{
					foreach($tp_attributes as $attr)
					{
						$main .= $attr->attribute_name." : ".$attr->value."\r\n";
					}
				}				
				//$mfg_id =Location::where('location_id',$attr->location_id)->pluck('manufacturer_id');
				//$mfg_id = $this->roleAccess->getMfgIdByToken($data['access_token']);
				//return $mfg_id;
				$manufacturer_name = DB::table('eseal_customer')->where('customer_id',$manufacturer_id)->pluck('brand_name');
				 $levelIds = DB::table('tp_data')->where('tp_id',$eseal_id)->lists('level_ids');
				 $ids1 = implode(',',$levelIds);
				 // echo '<pre>'; print_r($ids1); exit;

				// $cc= implode(',', array_map('intval', $levelIds));
				  //echo '<pre>'; print_r($cc); exit;
				 $result =DB::select('select distinct(level_id)  from eseal_'.$manufacturer_id.' where primary_id IN (' . $ids1. ')');				
				 if($result[0]->level_id == 2)
				 {
					$level1Ids=DB::table('eseal_'.$manufacturer_id)->whereIn('parent_id',$levelIds)->lists('primary_id');
					$pids =DB::select('select products.name,products.image from eseal_'.$manufacturer_id.' join products on products.product_id = eseal_'.$manufacturer_id.'.pid where parent_id IN (' . implode(',', array_map('intval', $levelIds)). ')');
				 }
				 if($result[0]->level_id == 1)
				 {
					$pids =DB::select('select products.name,products.image from eseal_'.$manufacturer_id.' join products on products.product_id = eseal_'.$manufacturer_id.'.pid where parent_id IN (' . implode(',', array_map('intval', $levelIds)). ') group by parent_id');
				 } 
				 if($result[0]->level_id == 0)
				 {
					$pids = DB::select('select products.name,products.image from eseal_'.$manufacturer_id.' join products on products.product_id = eseal_'.$manufacturer_id.'.pid where primary_id IN (' . implode(',', array_map('intval', $levelIds)). ') group by pid');
				} 
				$status =1; $message ='data retrieved successfully';
				return json_encode(['Status'=>$status,'Message'=>"\r\n".$main."\r\n",'Products'=>$pids,'Manufacturer Name'=>$manufacturer_name]);                                
			}
			else
			{
				throw new Exception ('In-valid EsealID.');
			}
		}
		catch(Exception $e)
		{
			$message = $e->getMessage();
		}
		//return $main;
		return json_encode(['Status'=>$status,'Message'=>$message]);
	}

	public function inspect($data)
	{
		try
		{
			$status =0;
			$eseal_id = $data['eseal_id'];
			$manufacturer_id= $this->roleAccess->getMfgIdByToken($data['access_token']);

                        $user_id = $this->roleAccess->checkAccessToken($data['access_token'])[0]->user_id;
                        $role_id = $this->roleAccess->getRolebyUserId($user_id)[0]->role_id;
                        $role = $role_id;
                        $inspRoleExists = DB::table('inspect_role_attribute')->where('role_id',$role_id)->count();
                 if(!$inspRoleExists)
            	      $role_id = false;

			$final = array();
            $checkBank = DB::table('eseal_bank_'.$manufacturer_id)->where(['id'=>$eseal_id])->get();
            if($checkBank){
		$primaryCollection = DB::table('eseal_'.$manufacturer_id)->where(['primary_id'=>$eseal_id,'is_active'=>1])->get();
		      }
		      else{
		      	throw new Exception("Invalid eSealId.");
		      }
			// if(empty($primaryCollection)){
			// 	$checkBank = DB::table('eseal_bank_'.$manufacturer_id)->where(['id'=>$eseal_id,'used_status'=>1])->get();
			// }
			// if(empty($checkBank)){
			// 	throw new Exception('Invlid eSealId.');
			// }	
			if(!empty($primaryCollection))
			{
				$responsetype = 'level'.$primaryCollection[0]->level_id;
				$final['response_type'] = $responsetype;
				if($primaryCollection[0]->po_number != NULL)
				{
					$final['po_number'] = $primaryCollection[0]->po_number;
				}
				if($primaryCollection[0]->is_redeemed != 0)
				{
					$final['is_redeemed'] = 1;
				}
				if(!is_null($primaryCollection[0]->serial_no) || !empty($primaryCollection[0]->serial_no))
				{
					$final['serial_no'] = $primaryCollection[0]->serial_no;
				}
				$pid = $primaryCollection[0]->pid;
				if(!empty($pid) && $primaryCollection[0]->level_id == 0)
				{				
					if(!empty($primaryCollection[0]->attribute_map_id))
					{
						$final['product_data'] = $this->getProductInfoFromAttributeMap($pid,$primaryCollection[0]->attribute_map_id,$eseal='',$manufacturer_id,$eseal_id,$role_id);
					}
					else
					{
						$prodCollection = DB::table('products')->where('product_id',$pid)->get();
						$prodInfo = ['product_id'=>$prodCollection[0]->product_id,'name'=>$prodCollection[0]->name,'qty'=>1,'title'=>$prodCollection[0]->title,'description'=>$prodCollection[0]->description,'manufacturer'=> $prodCollection[0]->manufacturer_id,'eseal_id'=>$eseal_id,'batch_no'=>$primaryCollection[0]->batch_no];
						foreach($prodInfo as $key=>$value)
						{
							$prodInfo1[]=['name'=>$key,'value'=>$value];
						}
						$image = 'http://'.$_SERVER['SERVER_NAME'].'/uploads/products/'.$prodCollection[0]->image;
						$final['product_data'] = ['product_info'=>$prodInfo1,'image'=>$image];
					}
					$childs= DB::table('eseal_'.$manufacturer_id )
								 ->join('products','products.product_id','=','eseal_'.$manufacturer_id.'.pid')
								 ->where('parent_id',$eseal_id)
								 ->get([DB::raw('cast(primary_id as CHAR) as primary_id'),DB::raw('CAST(pid as CHAR) AS pid'),'name as pname','eseal_'.$manufacturer_id.'.mrp','eseal_'.$manufacturer_id.'.batch_no']);
					$final['child_list'] = $childs;

					$packingIdLevel1 = $primaryCollection[0]->parent_id;
					if($packingIdLevel1 != 0)
					{
						$ppid = DB::table('eseal_'.$manufacturer_id)->where('primary_id',$packingIdLevel1)->pluck('pid');
						$pname = Products::where('product_id',$ppid)->get();
						$pname = $pname[0]->name;
						$qty = DB::table('eseal_'.$manufacturer_id)->where('parent_id',$packingIdLevel1)
						->select(DB::raw('count(distinct(primary_id)) as qty'))->get();
						$qty = $qty[0]->qty; 
						$level1info = ['levelId'=>$packingIdLevel1,'level'=>1,'name'=>$pname,'qty'=>intval($qty),'mrp'=>$primaryCollection[0]->mrp,'batch_no'=>$primaryCollection[0]->batch_no];  
						foreach($level1info as $key=>$value)
						{
							$level1info1[] = ['name'=>$key,'value'=>$value];
						}
						$final['level1_info'] = $level1info1;    
						$parent_id =  DB::table('eseal_'.$manufacturer_id)->where('primary_id',$packingIdLevel1)->select('parent_id')->get();
						$parent_id = $parent_id[0]->parent_id;
						if($parent_id != 0)
						{
							$qty1 = DB::table('eseal_'.$manufacturer_id)->where('parent_id',$parent_id)->select(DB::raw('count(distinct(primary_id)) as qty1'))->get();
							$qty1 = $qty1[0]->qty1;
							$level2info = ['levelId'=>$parent_id,'level'=>2,'name'=>$pname,'qty'=>intval($qty1),'mrp'=>$primaryCollection[0]->mrp,'batch_no'=>$primaryCollection[0]->batch_no];
							foreach($level2info as $key=>$value)
							{
								$level2info1[] = ['name'=>$key,'value'=>$value];
							}
							$final['level2_info'] = $level2info1; 

                            $lot_number = DB::table('tp_data')
									  ->join('track_history as th','th.tp_id','=','tp_data.tp_id')
									  ->join('eseal_'.$manufacturer_id.' as es','es.track_id','=','th.track_id')
									  ->where('es.primary_id',$packingIdLevel1)	
									  ->orderBy('th.update_time','desc')
									  ->select('th.tp_id')
									  ->get();
                            if(!$lot_number){
							$lot_number = DB::table('tp_data')
									  ->join('track_history as th','th.tp_id','=','tp_data.tp_id')
									  ->join('eseal_'.$manufacturer_id.' as es','es.track_id','=','th.track_id')
									  ->where('es.primary_id',$parent_id)	
									  ->orderBy('th.update_time','desc')
									  ->select('th.tp_id')
									  ->get();
                             }

							if(!empty($lot_number))
							{
								$lot_number= $lot_number[0]->tp_id;
								$final['tp_info'] = (string)$lot_number;
							}
						}
						else{
							$lot_number = DB::table('tp_data')
									  ->join('track_history as th','th.tp_id','=','tp_data.tp_id')
									  ->join('eseal_'.$manufacturer_id.' as es','es.track_id','=','th.track_id')
									  ->where('es.primary_id',$eseal_id)	
									  ->orderBy('th.update_time','desc')
									  ->select('th.tp_id')
									  ->get();
							
							if(!$lot_number){		  
							$lot_number = DB::table('tp_data')
									  ->join('track_history as th','th.tp_id','=','tp_data.tp_id')
									  ->join('eseal_'.$manufacturer_id.' as es','es.track_id','=','th.track_id')
									  ->where('es.primary_id',$packingIdLevel1)	
									  ->orderBy('th.update_time','desc')
									  ->select('th.tp_id')
									  ->get();
                            }
							
							if(!empty($lot_number))
							{		
								$lot_number = $lot_number[0]->tp_id;
								$final['tp_info'] = (string)$lot_number; 
							} 
						}
					}
					else{

						$lot_number = DB::table('tp_data')
									  ->join('track_history as th','th.tp_id','=','tp_data.tp_id')
									  ->join('eseal_'.$manufacturer_id.' as es','es.track_id','=','th.track_id')
									  ->where('es.primary_id',$eseal_id)	
									  ->orderBy('th.update_time','desc')
									  ->select('th.tp_id')
									  ->get();

                        if(!empty($lot_number))
							{		
								$lot_number = $lot_number[0]->tp_id;
								$final['tp_info'] = (string)$lot_number; 
							} 
					}
				}
				if($primaryCollection[0]->level_id == 1 || $primaryCollection[0]->level_id == 8)
				{
//Log::info('inside level1');
					if(!empty($primaryCollection[0]->po_number))
					{
						$final['po_number'] = $primaryCollection[0]->po_number;
					}				  
					if(empty($pid))
					{
						$final['heterogenous_products'] = $this->getProductInfoFromSecondary($manufacturer_id,$eseal_id,$role_id);
						for($i=0;$i< count($final['heterogenous_products']);$i++)
						{
							$final['heterogenous_products'][$i]['product_info']['eseal_id'] = $eseal_id;
						}
					}
					else
					{
//Log::info('inside else in level1');
						$attribute_map_id= DB::table('eseal_'.$manufacturer_id)->where('parent_id',$eseal_id)->select(DB::raw('count(*) as qty'),'attribute_map_id')->take(1)->get();
						if(empty($attribute_map_id[0]->attribute_map_id))
						{
							$prodCollection = DB::table('products')->where('product_id',$pid)->get();
							$prodInfo = ['product_id'=>$prodCollection[0]->product_id,'name'=>$prodCollection[0]->name,'qty'=>$attribute_map_id[0]->qty,'title'=>$prodCollection[0]->title,'description'=>$prodCollection[0]->description,'manufacturer'=> $prodCollection[0]->manufacturer_id,'eseal_id'=>$eseal_id,'batch_no'=>$primaryCollection[0]->batch_no];
							foreach($prodInfo as $key=>$value)
							{
								$prodInfo1[]=['name'=>$key,'value'=>$value];
							}
							$image = 'http://'.$_SERVER['SERVER_NAME'].'/uploads/products/'.$prodCollection[0]->image;
							$final['product_data'] = array('product_info'=>$prodInfo1,'image'=>$image);	
						}
						else
						{
//Log::info('inside else of get product info from attribute map');
							$final['product_data'] = $this->getProductInfoFromAttributeMap($pid,$attribute_map_id[0]->attribute_map_id,$eseal_id,$manufacturer_id,$eseal_id,$role_id);				
//Log::info('after product info');
						}
					}
					$childs= DB::table('eseal_'.$manufacturer_id )
					 ->join('products','products.product_id','=','eseal_'.$manufacturer_id.'.pid')
					 ->where('parent_id',$eseal_id)
					 ->get(array(DB::raw('cast(primary_id as CHAR) as primary_id'),DB::raw('CAST(pid as CHAR) AS pid'),'name as pname','eseal_'.$manufacturer_id.'.mrp','eseal_'.$manufacturer_id.'.batch_no'));
					 $final['child_list'] = $childs;
				
				$packingIdLevel2 = $primaryCollection[0]->parent_id;
				if( $packingIdLevel2 != 0){

					$qty = DB::table('eseal_'.$manufacturer_id)->where('parent_id',$packingIdLevel2)
					->select(DB::raw('count(distinct(primary_id)) as qty'))->get();
					$qty = $qty[0]->qty;       

					$level2info = ['levelId'=>$packingIdLevel2,'level'=>2,'qty'=>intval($qty)];  
					foreach($level2info as $key=>$value){
							$level2info1[] = ['name'=>$key,'value'=>$value];
						}
					$final['level2_info'] = $level2info1;    
					
					$lot_number = DB::table('tp_data')
									  ->join('track_history as th','th.tp_id','=','tp_data.tp_id')
									  ->join('eseal_'.$manufacturer_id.' as es','es.track_id','=','th.track_id')
									  ->where('es.primary_id',$packingIdLevel2)									  
									  ->orderBy('th.update_time','desc')
									  ->select('th.tp_id')->get();
					
					if(!empty($lot_number)){
					$lot_number = $lot_number[0]->tp_id;
					$final['tp_info'] = (string)$lot_number; 	
					}							
				}
				else{
					$lot_number = DB::table('tp_data')
									  ->join('track_history as th','th.tp_id','=','tp_data.tp_id')
									  ->join('eseal_'.$manufacturer_id.' as es','es.track_id','=','th.track_id')
									  ->where('es.primary_id',$eseal_id)
									  ->orderBy('th.update_time','desc')
									  ->select('th.tp_id')
									  ->get();
					if(!empty($lot_number)){
					$lot_number = $lot_number[0]->tp_id;
					$final['tp_info'] = (string)$lot_number; 	
					}		 
					
				}
			}

			if($primaryCollection[0]->level_id == 2 ){
				//$primary =DB::table('eseal_'.$manufacturer_id)->where('parent_id',$eseal_id)->lists('primary_id');
				if(empty($pid))
				{
					$final['heterogenous_products'] = $this->getProductInfoFromSecondary($manufacturer_id,$primary,$role_id);
					for($i=0;$i< count($final['heterogenous_products']);$i++)
					{
						$final['heterogenous_products'][$i]['product_info']['eseal_id'] = $eseal_id;
					}
				}
				else
				{
					$attribute_map_id= DB::table('eseal_'.$manufacturer_id)->where('parent_id',$eseal_id)->select(DB::raw('count(*) as qty'),'attribute_map_id')->take(1)->get();
					$att_map_id = DB::table('eseal_'.$manufacturer_id)->where('primary_id',$eseal_id)->pluck('attribute_map_id');
					if($att_map_id != 0)
						$attribute_map_id[0]->attribute_map_id = $att_map_id;
					
					if(empty($attribute_map_id[0]->attribute_map_id))
					{
						$prodCollection = DB::table('products')->where('product_id',$pid)->get();
						$prodInfo = ['product_id'=>$prodCollection[0]->product_id,'name'=>$prodCollection[0]->name,'qty'=>$attribute_map_id[0]->qty,'title'=>$prodCollection[0]->title,'description'=>$prodCollection[0]->description,'manufacturer'=> $prodCollection[0]->manufacturer_id,'eseal_id'=>$eseal_id,'batch_no'=>$primaryCollection[0]->batch_no];
						foreach($prodInfo as $key=>$value)
						{
							$prodInfo1[]=['name'=>$key,'value'=>$value];
						}
						$image = 'http://'.$_SERVER['SERVER_NAME'].'/uploads/products/'.$prodCollection[0]->image;
						$final['product_data'] = array('product_info'=>$prodInfo1,'image'=>$image);				
					}
				else{
				$final['product_data'] = $this->getProductInfoFromAttributeMap($pid,$attribute_map_id[0]->attribute_map_id,$eseal_id,$manufacturer_id,$eseal_id,$role_id);	
				
			}
				}

				$childs= DB::table('eseal_'.$manufacturer_id )
								->join('products','products.product_id','=','eseal_'.$manufacturer_id.'.pid')
								->where('parent_id',$eseal_id)
								->get(array(DB::raw('cast(primary_id as CHAR) as primary_id'),DB::raw('CAST(pid as CHAR) AS pid'),'name as pname','eseal_'.$manufacturer_id.'.mrp','eseal_'.$manufacturer_id.'.batch_no'));
				
				$final['child_list'] = $childs;
				
					$lot_number = DB::table('tp_data')
									  ->join('track_history as th','th.tp_id','=','tp_data.tp_id')
									  ->join('eseal_'.$manufacturer_id.' as es','es.track_id','=','th.track_id')
									  ->where('es.primary_id',$eseal_id)	
									  ->orderBy('th.update_time','desc')
									  ->select('th.tp_id')->get();
					if(!empty($lot_number)){
					$lot_number = $lot_number[0]->tp_id;
					$final['tp_info'] = (string)$lot_number; 	
				}
			}

				$trackInfo =DB::table('track_details as td')
							   ->join('track_history as th','th.track_id','=','td.track_id')
							   ->where(['td.code'=>$eseal_id])
							   ->select(DB::raw('distinct(td.track_id)'))
							   ->groupBy('th.transition_id')
							   ->groupBy('th.src_loc_id')
							   ->groupBy('th.update_time')
							   ->orderBy('th.update_time')
							   ->get();
							   
				if(empty($trackInfo[0])){
					if($primaryCollection[0]->level_id == 2){
						  $level1_id = DB::table('eseal_'.$manufacturer_id)->where('parent_id',$eseal_id)->select(DB::raw('distinct(primary_id)'))->take(1)->get();
						$trackInfo =DB::table('track_details')->where('code',$level1_id[0]->primary_id)->select(DB::raw('distinct(track_id)'))->get(); 
						 if(empty($trackInfo[0]->track_id)){
							$primary_id = DB::table('eseal_'.$manufacturer_id)->where('parent_id',$level1_id[0]->primary_id)->select(DB::raw('distinct(primary_id)'))->take(1)->get();
						   $trackInfo =DB::table('track_details')->where('code',$primary_id[0]->primary_id)->select(DB::raw('distinct(track_id)'))->get();
						 } 
					}
					if($primaryCollection[0]->level_id == 1){
						$primary_id =  DB::table('eseal_'.$manufacturer_id)->where('parent_id',$eseal_id)->select(DB::raw('distinct(primary_id)'))->take(1)->get();
	
						if(!empty($primary_id))
							$trackInfo =DB::table('track_details')->where('code',$primary_id[0]->primary_id)->select(DB::raw('distinct(track_id)'))->get();
					}
				}

				$trackfinal = array();
				/*if($manufacturer_id == 5){
					$oldTrackInfo = DB::table('trackupdate')->where('adjacent_id',$eseal_id)->orderBy('trackupdate_id','asc')->get();
					array_pop($oldTrackInfo);

					foreach($oldTrackInfo as $oldInfo){
					$array1= array(); $array2 = array();
					$tran  = Transaction::where('id',$oldInfo->transition_status)->get();   
					
					   if(!empty($oldInfo->source_location_id)){
						$srcloc= DB::table('locations')
						->leftJoin('location_types','locations.location_type_id','=','location_types.location_type_id')
						->where('locations.location_id',$oldInfo->source_location_id)
						->select('locations.location_name','locations.location_address','locations.latitude','locations.longitude','location_types.location_type_name')  
						->get();
						$array1 = array('status'=>$tran[0]->name,'source'=>$srcloc[0]->location_name,'source_loc_type'=>$srcloc[0]->location_type_name,'source_address'=>$srcloc[0]->location_address,'src_lat'=>$srcloc[0]->latitude,'src_long'=>$srcloc[0]->longitude,'time'=>$oldInfo->created_time);  
					}
					if(!empty($oldInfo->dest_loc_id)){
						$destloc= DB::table('locations')
						->leftJoin('location_types','locations.location_type_id','=','location_types.location_type_id')
						->where('locations.location_id',$oldInfo->destination_location_id)
						->select('locations.location_name','locations.location_address','locations.latitude','locations.longitude','location_types.location_type_name')  
						->get();
						$array2 = array('status'=>$tran[0]->name,'destination'=>$destloc[0]->location_name,'dest_loc_type'=>$destloc[0]->location_type_name,'dest_address'=>$destloc[0]->location_address,'dest_lat'=>$destloc[0]->latitude,'dest_long'=>$destloc[0]->longitude,'time'=>$oldInfo->created_time);
					}
					array_push($trackfinal,$array1+$array2);
					   	
					}
				
				}*/

				
				for($u= 0;$u < count($trackInfo);$u++){
					//Log::info('In the Loop'.$u);
					$array1= array(); $array2 = array();
					$tracktr = Trackhistory::where('track_id',$trackInfo[$u]->track_id)->select('src_loc_id','dest_loc_id','transition_id','update_time')->get();  
					$tran  = Transaction::where('id',$tracktr[0]->transition_id)->get();
					//Log::info($tran);
					if(!empty($tracktr[0]->src_loc_id)){
						$srcloc= DB::table('locations')
						->leftJoin('location_types','locations.location_type_id','=','location_types.location_type_id')
						->where('locations.location_id',$tracktr[0]->src_loc_id)
						->select('locations.location_name','locations.location_address','locations.latitude','locations.longitude','location_types.location_type_name')  
						->get();
						$array1 = array('status'=>$tran[0]->name,'source'=>$srcloc[0]->location_name,'source_loc_type'=>$srcloc[0]->location_type_name,'source_address'=>$srcloc[0]->location_address,'src_lat'=>$srcloc[0]->latitude,'src_long'=>$srcloc[0]->longitude,'time'=>$tracktr[0]->update_time);  
					}
					if(!empty($tracktr[0]->dest_loc_id)){
						$destloc= DB::table('locations')
						->leftJoin('location_types','locations.location_type_id','=','location_types.location_type_id')
						->where('locations.location_id',$tracktr[0]->dest_loc_id)
						->select('locations.location_name','locations.location_address','locations.latitude','locations.longitude','location_types.location_type_name')  
						->get();
						$array2 = array('status'=>$tran[0]->name,'destination'=>$destloc[0]->location_name,'dest_loc_type'=>$destloc[0]->location_type_name,'dest_address'=>$destloc[0]->location_address,'dest_lat'=>$destloc[0]->latitude,'dest_long'=>$destloc[0]->longitude,'time'=>$tracktr[0]->update_time);
					}
					array_push($trackfinal,$array1+$array2);
				}
				$final['trace_info'] = $trackfinal;

                                 if(in_array($role,['401','404','405','406','407','415','418','419']))
                                    $final['trace_info'] = array();

				$status= 1;
				$message ='Data Retrieved Successfully';
				return json_encode(array('Status'=>$status,'Message'=>$message,'Data'=>$final));	
			}
/////////////////////////////////end of Level0,Level1,Level2 Info//////////////////////////////////////
			$primaryCollection = DB::table('eseal_bank_'.$manufacturer_id)->where(['id'=>$eseal_id,'level'=>9])->get();
			$status =0;
			if(!empty($primaryCollection)){
				$final['response_type'] = 'tp';	
				$tpattr = DB::table('tp_attributes')->where('tp_id',$eseal_id)->get();
				
				$tpdetails =DB::select('select * from track_history th join track_details td on th.track_id=td.track_id where td.code='.$eseal_id.' order by th.update_time limit 1');

				$transitfinal = array();
				
				if(empty($tpdetails)){
					throw new Exception("Tp does not Exists");
				}
				$srcloc= DB::table('locations')
							  ->leftJoin('location_types','locations.location_type_id','=','location_types.location_type_id')
							  ->where('locations.location_id',$tpdetails[0]->src_loc_id)
							  ->select('locations.location_name','locations.location_address','locations.latitude','locations.longitude','location_types.location_type_name')  
							  ->get();

				$destloc= DB::table('locations')
							  ->leftJoin('location_types','locations.location_type_id','=','location_types.location_type_id')
							  ->where('locations.location_id',$tpdetails[0]->dest_loc_id)
							  ->select('locations.location_name','locations.location_address','locations.latitude','locations.longitude','location_types.location_type_name')  
							  ->get();

				array_push($transitfinal,array('source'=>$srcloc[0]->location_name,'source_loc_type'=>$srcloc[0]->location_type_name,'source_address'=>$srcloc[0]->location_address,'src_lat'=>$srcloc[0]->latitude,'src_long'=>$srcloc[0]->longitude,
					'destination'=>$destloc[0]->location_name,'dest_loc_type'=>$destloc[0]->location_type_name,'dest_address'=>$destloc[0]->location_address,'dest_lat'=>$destloc[0]->latitude,'dest_long'=>$destloc[0]->longitude,'modified_date'=>$tpdetails[0]->update_time));
				$final['transitInfo'] = $transitfinal;

				$trackInfo =DB::table('track_details')->where('code',$eseal_id)->select(DB::raw('distinct(track_id)'))->get();
				//Log::info("count of track info");
				//Log::info(count($trackInfo));
				$trackfinal = array();
                
   				for($u=0; $u < count($trackInfo); $u++){
					$array1= array(); 
					$array2 = array();
					$tracktr = Trackhistory::where('track_id',$trackInfo[$u]->track_id)->select('src_loc_id','dest_loc_id','transition_id','update_time')->get();  
					$tran    = Transaction::where('id',$tracktr[0]->transition_id)->get();

					if(!empty($tracktr[0]->src_loc_id)){
						$srcloc= DB::table('locations')
						->leftJoin('location_types','locations.location_type_id','=','location_types.location_type_id')
						->where('locations.location_id',$tracktr[0]->src_loc_id)
						->select('locations.location_name','locations.location_address','locations.latitude','locations.longitude','location_types.location_type_name')  
						->get();
						$array1 = array('status'=>$tran[0]->name,'source'=>$srcloc[0]->location_name,'source_loc_type'=>$srcloc[0]->location_type_name,'source_address'=>$srcloc[0]->location_address,'src_lat'=>$srcloc[0]->latitude,'src_long'=>$srcloc[0]->longitude,'time'=>$tracktr[0]->update_time);  
					}
					if(!empty($tracktr[0]->dest_loc_id)){
						$destloc= DB::table('locations')
						->leftJoin('location_types','locations.location_type_id','=','location_types.location_type_id')
						->where('locations.location_id',$tracktr[0]->dest_loc_id)
						->select('locations.location_name','locations.location_address','locations.latitude','locations.longitude','location_types.location_type_name')  
						->get();
						$array2 = array('status'=>$tran[0]->name,'destination'=>$destloc[0]->location_name,'dest_loc_type'=>$destloc[0]->location_type_name,'dest_address'=>$destloc[0]->location_address,'dest_lat'=>$destloc[0]->latitude,'dest_long'=>$destloc[0]->longitude,'time'=>$tracktr[0]->update_time);
					}
					array_push($trackfinal,$array1+$array2);
				}

				$final['trace_info'] = $trackfinal;
                                
				
				 $levelIds = DB::table('tp_data')->where('tp_id',$eseal_id)->lists('level_ids');
				 $result =DB::select('select distinct(level_id) from eseal_'.$manufacturer_id.' where primary_id IN (' . implode(',', $levelIds). ')');
				 
				 if($result[0]->level_id == 2){
				 $level1Ids=DB::table('eseal_'.$manufacturer_id)->whereIn('parent_id',$levelIds)->lists('primary_id');
				 $pids =DB::select('select cast(count(*) as char) as qty,2 as level,cast(parent_id as char) as primary_id,products.name,CAST(pid as CHAR) as pid,eseal_'.$manufacturer_id.'.mrp,eseal_'.$manufacturer_id.'.batch_no from eseal_'.$manufacturer_id.' join products on products.product_id = eseal_'.$manufacturer_id.'.pid where parent_id IN (' . implode(',',$levelIds). ')');
				 }
				 if($result[0]->level_id == 1){
				 $pids =DB::select('select cast(count(*) as char) as qty,1 as level,cast(parent_id as char) as primary_id,products.name,CAST(pid as CHAR) as pid,eseal_'.$manufacturer_id.'.mrp,eseal_'.$manufacturer_id.'.batch_no from eseal_'.$manufacturer_id.' join products on products.product_id = eseal_'.$manufacturer_id.'.pid where parent_id IN (' . implode(',', $levelIds). ') group by parent_id');
				 }
				 if($result[0]->level_id == 0){
				$pids = DB::select('select cast(1 as char) as qty,0 as level,cast(primary_id as char) as primary_id,products.name,CAST(pid as CHAR) as pid,eseal_'.$manufacturer_id.'.mrp,eseal_'.$manufacturer_id.'.batch_no from eseal_'.$manufacturer_id.' join products on products.product_id = eseal_'.$manufacturer_id.'.pid where primary_id IN (' . implode(',', $levelIds). ')');
				 }
				$final['child_list'] = $pids;
				if(!empty($tpattr) && count($tpattr)>0){
					$tpattr1= array();     
					foreach($tpattr as $tpvalue){
						array_push($tpattr1,array('name'=>$tpvalue->attribute_name,'value'=>$tpvalue->value));
					}
					$final['tp_attributes'] = $tpattr1;
				}
				$status =1;
				$message = 'Data Retrieved Successfully';
				return json_encode(['Status'=>$status,'Message'=>$message,'Data'=>$final]);
			}
			if(empty($primaryCollection)){
				throw new Exception('The Id is not Packed or Scanned.');
			}
		}
		catch(Exception $e){
			$message = $e->getMessage();
		}	
		return json_encode(array('Status'=>$status,'Message'=>$message,'Data'=>$final));	
	}

	public function convertXml($data){


   
	$xml = $data['xml'];
	$main_heading = 1;
	
	$deXml = simplexml_load_string($xml);
	$deJson = json_encode($deXml);
	$xml_array = json_decode($deJson,TRUE);
	if (! empty($main_heading)) {
		$returned = $xml_array[$main_heading];
		return $returned;
	} else {
		return $xml_array;
	}
	
	}

	public function updatePO(){
		
		$startTime = $this->getTime();    
	try{
	  $status = 0;
	  $message = 'Failed to bind';

	  $attributeMapId = 0;
	  $po = trim(Input::get('po'));
	  $ids = trim(Input::get('ids'));
	  //$pid = trim(Input::get('pid'));
	  $locationId = trim(Input::get('srcLocationId'));
	  $attributeMapId = trim(Input::get('attribute_map_id'));

	  Log::info(__FUNCTION__.' : '.print_r(Input::get(),true));
	  if( !empty($ids) && !empty($locationId) && !empty($pid) ){
		$ids1 = explode(',', $ids);
		$ids1 = array_unique($ids1);
		$newIds = '\''.implode('\',\'', $ids1).'\'';

		$locationObj = new Locations\Locations();
		$mfgId = $locationObj->getMfgIdForLocationId($locationId);
		//Log::info('-----'.$mfgId);
		if(!empty($mfgId)){
		  $esealTable = 'eseal_'.$mfgId;
		  $esealBankTable = 'eseal_bank_'.$mfgId;

	  $cnt = DB::table($esealBankTable)->whereIn('id', $ids1)
				->where(function($query){
					$query->where('issue_status',1);
					$query->orWhere('download_status',1);
				})->count();
	  //Log::info(count($ids1).' == '.$cnt);
	  if(count($ids1) != $cnt){
		throw new Exception('Codes count not matching with code bank');
	  }
	  $resultCnt = DB::table($esealTable)->whereIn('primary_id', $ids1)->count();
	 // Log::info($resultCnt);
	  
	  $productExists = DB::table('products')->select('product_id')->where('product_id', $pid)->get();
	  DB::beginTransaction();
	  //Log::info(print_r($result[0]->cnt, true));
	  //Log::info($productExists);
		if(!empty($productExists[0]->product_id)){
		  
			//Log::info(print_r($ids1,true));
		  try{
			foreach($ids1 as $id){
			  $res = DB::select('SELECT eseal_id FROM '.$esealTable.' WHERE primary_id = ?', array($id));
			  if(count($res)){
				DB::update('Update '.$esealTable.' SET pid = '.$pid.', attribute_map_id = '.$attributeMapId.' WHERE eseal_id = ? ', Array($res[0]->eseal_id));
			  }else{
				DB::insert('INSERT INTO '.$esealTable.' (primary_id, pid, attribute_map_id) values (?, ?, ?)', array($id, $pid, $attributeMapId));  
			  }
			}
			DB::table($esealBankTable)->whereIn('id',$ids1)->update(Array('used_status'=>1, 'location_id'=> $locationId));
		  }catch(PDOException $e){
			DB::rollback();
			Log::error($e->getMessage());
			throw new Exception('Error while binding');  
		  }
		  $status = 1;
		  $message = 'Binding Successfull';
		  DB::commit();
		}else{
		  throw new Exception('Some codes already exists or product not found');
		}
	  }else{
		throw new Exception('Location doesn\'t belong to any Customer');
	  }
	}else{
	  throw new Exception('Some of the params missing');
	}
	}catch(Exception $e){
	  DB::rollback();
	  Log::info($e->getMessage());
	  $message = $e->getMessage();
	}
	if($status){
		Event::fire('scoapi/BindEseals', array('attribute_map_id'=>$attributeMapId, 'codes'=>$ids1, 'mfg_id'=>$mfgId));
	}
	$endTime = $this->getTime();
	Log::info(__FUNCTION__.' Finishes execution in '.($endTime-$startTime));

	 return json_encode(Array('Status'=>$status, 'Message' => $message));
	}

	public function SaveBindingAttributes()
	{

		$startTime = $this->getTime();
		try
		{
			$dateTime = $this->getDate();
			$status = 0;
			$message = '';
			$nextId =0;
			$mapCollection = array();

			$attributes = trim(Input::get('attributes'));
			$pid = trim(Input::get('pid'));
			$locationId = trim(Input::get('lid'));
	  
			Log::info(__FUNCTION__.' : '.print_r(Input::get(),true));

			if(!empty($attributes) && !empty($pid) && !empty($locationId))
			{
				$attributes_json = $attributes;
				$attributes = json_decode($attributes);
				if(json_last_error() == JSON_ERROR_NONE)
				{
					
					$attributeMapId = '';
					$nextId = 0;
					
					
					//DB::commit();		
						Log::info(print_r($nextId,true));         
						try
						{				  
							//$nextId = DB::table('attribute_map')->where(array('attribute_json'=>$attributes_json,'location_id'=>$locationId))->pluck('attribute_map_id');

		//					Log::info('NextId'.$nextId);
							
							if(empty($nextId))
							{
								$nextId =DB::table('attribute_map')->insertGetId(array('attribute_json'=>$attributes_json,'location_id'=>$locationId,'created_on'=>date('Y-m-d H:i:s')));
								
		//						Log::info('New Instered NextId='.$nextId);
								foreach($attributes as $key => $value)
								{
		//							Log::info($key.' == '.$value);
									$attributeId = DB::table($this->attributeTable)->where('attribute_code', $key)->pluck('attribute_id');
		//							Log::info($attributeId);
									if(!empty($attributeId))
									{
										$insert = DB::insert(
											'insert into '.$this->attributeMappingTable.' (attribute_map_id, attribute_id, attribute_name, value, location_id,mapping_date) VALUES (?, ?, ?, ?, ?,?)', 
										array($nextId, $attributeId, $key, $value, $locationId,$dateTime));
									}
								}
							}
							$status = 1;
							$message = 'Attributes Added Successfully';
				 
						}
						catch(Exception $e)
						{
							LOG::error('Exception occured at '.__LINE__.' '.$e->getMessage());
							throw new Exception('Error during attribute mapping');
						}
					
					   
				}
				else
				{
					throw new Exception('Attributes are not in json format');
				}
				
			}
			else
			{
				throw new Exception('Parameter missing');
			}
		}
		catch(Exception $e)
		{
			$status =0;
			//DB::rollback();
			$message = $e->getMessage();
		}
		$endTime = $this->getTime();
		Log::info(__FUNCTION__.' Finishes execution in '.($endTime-$startTime));
		Log::info('AttributeMapId is:'.$nextId);
		return json_encode(Array('Status'=>$status, 'Message' =>'Server: '.$message, 'AttributeMapId'=> (int)$nextId));
	}					

	public function SaveBindingAttributesBackup()
	{
		

		$startTime = $this->getTime();
		try
		{
			$dateTime = $this->getDate();
			$status = 0;
			$message = '';
			$nextId =0;
			$mapCollection = array();
			DB::beginTransaction();
			$attributes = trim(Input::get('attributes'));
			$pid = trim(Input::get('pid'));
			$locationId = trim(Input::get('lid'));
	  
			Log::info(__FUNCTION__.' : '.print_r(Input::get(),true));

			if(!empty($attributes) && !empty($pid) && !empty($locationId))
			{
				$attributes = json_decode($attributes);
				if(json_last_error() == JSON_ERROR_NONE)
				{
					$dupCnt = 0;
					$codeCnt = 0;
					$attributeMapId = '';
					foreach($attributes as $key => $value)
					{
		//				Log::info($key.' == '.$value);
						$attributeId = DB::table($this->attributeTable)->where('attribute_code',trim($key))->pluck('attribute_id');
		//				Log::info($attributeId);

						if(!empty($attributeId))
						{
							$cnt = DB::table($this->attributeMappingTable)
							->select('attribute_map_id')
							->where('attribute_id', '=', $attributeId)
							->where('value', '=',  trim($value))
							->where('location_id', '=' , $locationId)
							->orderBy('attribute_map_id','desc')
							->take(1)
							->get();

							if(!empty($cnt[0]))
							{
								$dupCnt++;
								$attributeMapId = $cnt[0]->attribute_map_id;
								$mapCollection[] = $attributeMapId; 
							}
							$codeCnt++;
		//					Log::info(print_r(count($cnt),true));
						}
					}
					$nextId = 0;
		//			Log::info($codeCnt.'=='.$dupCnt);
					if($codeCnt!=$dupCnt)
					{
						$mid = DB::table($this->attributeMappingTable)
							->select(DB::raw('(IFNULL(max(attribute_map_id),0)+1) mid'))
							->get();
						$nextId = $mid[0]->mid;
		//				Log::info(print_r($nextId,true));         
						try
						{				  
							foreach($attributes as $key => $value)
							{
		//						Log::info($key.' == '.$value);
								$attributeId = DB::table($this->attributeTable)->where('attribute_code', $key)->pluck('attribute_id');
		//						Log::info($attributeId);
								if(!empty($attributeId))
								{
									$insert = DB::insert(
										'insert into '.$this->attributeMappingTable.' (attribute_map_id, attribute_id, attribute_name, value, location_id,mapping_date) VALUES (?, ?, ?, ?, ?,?)', 
									array($nextId, $attributeId, $key, $value, $locationId,$dateTime));
								}
							}
							$status = 1;
							$message = 'Attributes Added Successfully';
				 
						}
						catch(Exception $e)
						{
							LOG::error('Exception occured at '.__LINE__.' '.$e->getMessage());
							throw new Exception('Error during attribute mapping');
						}
					}
					if($codeCnt == $dupCnt)
					{
						$status = 1;
						$message = 'Attribute set already exists';
						$count = count(array_unique($mapCollection));
		//				Log::info('Map Count:'.$count);
						if($count > 1)
						{
							$mid = DB::table($this->attributeMappingTable)
								->select(DB::raw('(IFNULL(max(attribute_map_id),0)+1) mid'))
								->get();
							$nextId = $mid[0]->mid;

							foreach($attributes as $key => $value)
							{
		//						Log::info($key.' == '.$value);
								$attributeId = DB::table($this->attributeTable)->where('attribute_code', $key)->pluck('attribute_id');
		//						Log::info($attributeId);
								if(!empty($attributeId))
								{
									$insert = DB::insert(
										'insert into '.$this->attributeMappingTable.' (attribute_map_id, attribute_id, attribute_name, value, location_id,mapping_date) VALUES (?, ?, ?, ?, ?,?)', 
									array($nextId, $attributeId, $key, $value, $locationId,$dateTime));
								}
							}

						}
						else
						{
							$mapCollection =  array_unique($mapCollection);

							$mapCount = DB::table($this->attributeMappingTable)->where('attribute_map_id',$mapCollection[0])->count('id');
							if($mapCount == $codeCnt){
							  $nextId =  $attributeMapId;	
							}
							else{

								$mid = DB::table($this->attributeMappingTable)
								->select(DB::raw('(IFNULL(max(attribute_map_id),0)+1) mid'))
								->get();
							$nextId = $mid[0]->mid;

							foreach($attributes as $key => $value)
							{
		//						Log::info($key.' == '.$value);
								$attributeId = DB::table($this->attributeTable)->where('attribute_code', $key)->pluck('attribute_id');
		//						Log::info($attributeId);
								if(!empty($attributeId))
								{
									$insert = DB::insert(
										'insert into '.$this->attributeMappingTable.' (attribute_map_id, attribute_id, attribute_name, value, location_id,mapping_date) VALUES (?, ?, ?, ?, ?,?)', 
									array($nextId, $attributeId, $key, $value, $locationId,$dateTime));
								}
							}

							}

							
						}
					}
					//Log::info(print_r($attributes, true));    
				}
				else
				{
					throw new Exception('Attributes are not in json format');
				}
				DB::commit();
			}
			else
			{
				throw new Exception('Parameter missing');
			}
		}
		catch(Exception $e)
		{
			$status =0;
			DB::rollback();
			$message = $e->getMessage();
		}
		$endTime = $this->getTime();
		Log::info(__FUNCTION__.' Finishes execution in '.($endTime-$startTime));
		Log::info('AttributeMapId is:'.$nextId);		
		return json_encode(Array('Status'=>$status, 'Message' =>'Server: '.$message, 'AttributeMapId'=> (int)$nextId));
	}
					

					
		

  public function bindGrnData(){
	try{
		$startTime = $this->getTime();
		Log::info(__FUNCTION__.' : '.print_r(Input::get(),true));
		$status =0;
		$grn_no = trim(Input::get('grn_no'));
		$tp = trim(Input::get('tp'));
		$transitionTime = trim(Input::get('transition_time'));
		$transitionId = trim(Input::get('transition_id'));
		$excess_ids = trim(Input::get('excess_ids'));
		$damage_ids = trim(Input::get('damage_ids'));
		$location_id = $this->roleAccess->getLocIdByToken(Input::get('access_token'));
		$mfg_id = $this->roleAccess->getMfgIdByToken(Input::get('access_token'));
		$esealTable = 'eseal_'.$mfg_id;
		$esealBankTable = 'eseal_bank_'.$mfg_id;





		$query = DB::table('erp_integration')->where('manufacturer_id',$mfg_id);
		if($query->pluck('id')){
			$erp = $query->select('web_service_url','token','company_code','web_service_username','web_service_password','sap_client')->get();
			$domain = $erp[0]->web_service_url;
			$token = $erp[0]->token;
			$company_code = $erp[0]->company_code;
			$sap_client = $erp[0]->sap_client;
			$erp = $this->roleAccess->getErpDetailsByUserId(Input::get('access_token'));	
			if($erp){
				$username = $erp[0]->erp_username;
				$password = $erp[0]->erp_password;
			}
			else{
				throw new Exception('There are no erp username and password');
			}
			$data = ['TOKEN'=>$token,'DOCUMENT'=>$grn_no];
			$method = 'Z029_ESEAL_GET_GRN_DATA_SRV';
			$method_name = 'GRN_OUTPUT';
			$url = $domain.$method.'/'.$method_name;
			$url = sprintf("%s?\$filter=%s", $url, urlencode($this->sapRepo->generateData($data)));
			$url = $url.'&sap-client='.$sap_client;
			Log::info('URL hit in bindGrnData:-');
			Log::info($url);
			$curl = curl_init();
			curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
			curl_setopt($curl, CURLOPT_USERPWD, "$username:$password");
			curl_setopt($curl,CURLOPT_RETURNTRANSFER,true);
			curl_setopt($curl, CURLOPT_URL, $url);
			curl_setopt($curl, CURLOPT_HEADER, 0);
			$result = curl_exec($curl);
			curl_close($curl);
			Log::info('Get Grn response:-');
			Log::info($result);
			$parseData1 = xml_parser_create();
			xml_parse_into_struct($parseData1, $result, $documentValues1, $documentIndex1);
			xml_parser_free($parseData1);
			$documentData = array();
			foreach ($documentValues1 as $data) {
				if(isset($data['tag']) && $data['tag'] == 'D:GET_GRN')
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
			Log::info($xml_array);
			$sts = $xml_array['HEADER']['Status'];
			if($sts == 1){
				$vendor_no = $xml_array['DATA']['VENDOR_NO'];
				if(is_array($vendor_no) && empty($vendor_no)){
					throw new Exception('Vendor Code not passed.');
				}
				
				$vendor_id  = Location::where('erp_code',$vendor_no)->pluck('location_id');
				
				if(!empty($vendor_id)){
					if(!array_key_exists('SNO', $xml_array['DATA']['ITEMS']['ITEM'])){
						foreach($xml_array['DATA']['ITEMS']['ITEM'] as $data){
							foreach($data as $key => $value){
								if(is_array($value) && empty($value)){
									$data[$key] = '';
								}
							}

							$response = $this->receiveGrn($vendor_no,$data,Input::get('module_id'),Input::get('access_token'),$transitionTime);
							$response = json_decode($response);			
							if($response->Status){
								$status =1;
								$message =$response->Message;
							}	
							else{
								throw new Exception($response->Message);
							}		
						}
					}
					else{

						$vendor_no = $xml_array['DATA']['VENDOR_NO'];
						$response = $this->receiveGrn($vendor_no,$xml_array['DATA']['ITEMS']['ITEM'],Input::get('module_id'),Input::get('access_token'),$transitionTime,$transitionId,$tp,$excess_ids,$damage_ids);
						$response = json_decode($response);
						if($response->Status){
							$status =1;
							$message = $response->Message;
						}
						else{					
							throw new Exception($response->Message);
						}
					}
				}
				else{
					throw new Exception ('Vendor Doesnt exists');
				}
			}
			else{
				throw new Exception('Data not retrieved');
			}  

		}
		else{
			throw new Exception('ERP_Configuration doesnt exist');
		}
	}
	catch(Exception $e){
		$status =0;
		DB::rollback();
		$message = $e->getMessage();
	}
	DB::commit();
	$endTime = $this->getTime();
	Log::info(__FUNCTION__.' Finishes execution in '.($endTime-$startTime));
	Log::info(['Status'=>$status, 'Message' => $message]);
	return json_encode(['Status'=>$status,'Message'=>'Server: '.$message]);
  } 

  private function receiveGrn($vendor_no,$data,$module_id,$access_token,$transitionTime,$transitionId,$tp,$excess_ids,$damage_ids)
  {
  
	try{
		$status =0;
		$mfg_id = $this->roleAccess->getMfgIdByToken($access_token);
		$esealTable = 'eseal_'.$mfg_id;
		$location_id = $this->roleAccess->getLocIdByToken($access_token);
		$vendor_id = Location::where('erp_code',$vendor_no)->pluck('location_id');

		if(!empty($vendor_id)){
			foreach($data as $key => $value){
				if(is_array($value) && empty($value)){
					$data[$key] = '';
				}
			}
			Log::info('ffffffffff=======>===========>===================>');
			Log::info($data);	
			$material_code = $data['MATERIAL_CODE'];
			$quant = $data['QUANTITY'];
			$batch_no = $data['BATCH_NO'];
			$pid = Products::where('material_code',$material_code)->pluck('product_id');
			$group_id =  Products::where('product_id',$pid)->pluck('group_id');
            /////////CHECKING FOR TP
			if(empty($tp)){
			if($pid){
				//$pid = $query->pluck('product_id');
				$is_traceable = Products::where('product_id',$pid)->pluck('is_traceable');

				if($is_traceable){
					DB::beginTransaction();
					$attribute_set_id =  DB::table('product_attributesets')->where(['product_group_id'=>$group_id,'location_id'=>$vendor_id])->pluck('attribute_set_id');
					if($attribute_set_id){
						$attribute_ids = DB::table('attributes as attr')
												->join('attribute_set_mapping as asm','asm.attribute_id','=','attr.attribute_id')
												->where(['asm.attribute_set_id'=>$attribute_set_id])
												->get(['attr.attribute_id']);

						foreach($attribute_ids as $id){
							$attribute_sets[] = $id->attribute_id;
						} 


						$batch_id = DB::table('attributes')->where('name','BATCH_NO')->pluck('attribute_id');
						$quant_id = DB::table('attributes')->where('name','QUANTITY')->pluck('attribute_id');
						$id = DB::table('attribute_mapping')
									->join($esealTable,$esealTable.'.attribute_map_id','=','attribute_mapping.attribute_map_id')
									->where(['attribute_mapping.attribute_id'=>$batch_id,'attribute_mapping.value'=>$data['BATCH_NO']])   											  
									->get([$esealTable.'.primary_id']);
						$attribute_map_id = DB::table('attribute_mapping')->where(['attribute_id'=>$batch_id,'value'=>$data['BATCH_NO']])->pluck('attribute_map_id');
						/*$qty = DB::table('attribute_mapping')->where(['attribute_map_id'=>$attribute_map_id,'attribute_id'=>$quant_id])->pluck('value');
						Log::info('Binded IDS at vendor:-');
						Log::info($id);
						if($qty != $quant){
							throw new Exception('Quantity mismatched');
						}*/
						if(!empty($id)){
							foreach($id as $id1){
								$id2[] = $id1->primary_id;
							}
							$id2 = implode(',',$id2);
							Log::info('IDS binded at Vendor:');
							Log::info($id2);
							$attribute_set_id =  DB::table('product_attributesets')->where(['product_id'=>$pid,'location_id'=>$location_id])->pluck('attribute_set_id');
							Log::info('Attribute set:'.$attribute_set_id);
							$attribute_names = DB::table('attributes as attr')
												->join('attribute_set_mapping as asm','asm.attribute_id','=','attr.attribute_id')
												->where(['asm.attribute_set_id'=>$attribute_set_id])
												->get(['attr.name']);
							
							Log::info('Attribute names:-');
							Log::info($attribute_names);	
							$attr2= array();				
							foreach($attribute_names as $name){
								$attr2[] = $name->name;
							}
							Log::info($attr2);

							foreach($data as $key => $value){
								if(!empty($value)){
									if(in_array($key,$attr2)){
										$attributes[$key] = $value;
									}
								}
							}
							Log::info("====>====>");
							Log::info($attributes);
							if(!empty($attributes)){
								$attributes = json_encode($attributes);
							  $request = Request::create('scoapi/SaveBindingAttributes', 'POST', array('module_id'=> Input::get('module_id'),'access_token'=>Input::get('access_token'),'attributes'=>$attributes,'lid'=>$location_id,'pid'=>$pid));
							  $originalInput = Request::input();//backup original input
							  Request::replace($request->input());
							  Log::info($request->input());
							  $res1 = Route::dispatch($request)->getContent();//invoke API
							  $res1 = json_decode($res1);
							  if($res1->Status){
								
								$attribute_map_id = $res1->AttributeMapId;

								$transitionId = DB::table('transaction_master')->where(['name'=>'Receive GRN','manufacturer_id'=>$mfg_id])->pluck('id');

							  $request = Request::create('scoapi/BindWithTrackupdate', 'POST', array('module_id'=> Input::get('module_id'),'access_token'=>Input::get('access_token'),'attribute_map_id'=>$attribute_map_id,'srcLocationId'=>$location_id,'pid'=>$pid,'codes'=>$id2,'ids'=>$id2,'transitionId'=>$transitionId,'transitionTime'=>$transitionTime,'destLocationId'=>0));
							  $originalInput = Request::input();//backup original input
							  Request::replace($request->input());
							  Log::info($request->input());
							  $res2 = Route::dispatch($request)->getContent();//invoke API
							  $res2 = json_decode($res2);
							  if($res2->Status){
								$status =1;
								$message ='Binding Successfull';
							  }
							  else{
								throw new Exception ($res2->Message);
							  }
							}
							else{
								throw new Exception($res1->Message);
							}
						}
						else{
							throw new Exception ('No Attributes matching for material_code-'.$material_code);
						}    
					}
					else{
						throw new Exception('Product not pre-binded');
					}
				}  
				else{
					throw new Exception('Attribute Set Doesnt Exist at vendor location for material_code-'.$material_code);
				} 
			}
			else{
				throw new Exception('Vendor doesnt exist');
			}            
		}
		else{
			throw new Exception('Product not traceable');
		}
	}
	else{
		$doesntExist[] = $material_code;
		throw new Exception ('Material-'.$material_code.' not configured');
	}
}
else{
	$level_ids = DB::table('tp_data')->where('tp_id',$tp)->lists('level_ids');
	DB::table($esealTable)->whereIn('primary_id',$level_ids)->orWhereIn('parent_id',$level_ids)->update(['batch_no'=>$batch_no]);

          $request = Request::create('scoapi/ReceiveByTp', 'POST', array('module_id'=>$module_id,'access_token'=>$access_token,'tp'=>$tp,'location_id'=>$location_id,'transition_time'=>$transitionTime,'transition_id'=>$transitionId,'excess_ids'=>$excess_ids,'damage_ids'=>$damage_ids));
		  $originalInput = Request::input();//backup original input
		  Request::replace($request->input());
		  $result = Route::dispatch($request)->getContent();//invoke API
		  $result = json_decode($result,true);
		  if(!$result['Status'])
		  	throw new Exception($result['Message']);

}

}
catch(Exception $e){
	$status =0;
	$message = $e->getMessage();
}
return json_encode(['Status'=>$status,'Message'=>$message]);
}

public function notifyEseal()
{
	$startTime = $this->getTime();
	$plantId = Input::get('plant_id');
	$objectType = Input::get('type');
	$objectId = Input::get('object_id');
	$action = Input::get('action');
	$status =1;
        $message = '';
	$movement_type = Input::get('movement_type');
	if(!$movement_type)
		$movement_type =0;
	//$location_id = Input::get('location_id');
     DB::beginTransaction();
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
			DB::commit();

		} catch(Exception $e)
	{
			$status = 0;
		    DB::rollback();
			Log::info($e->getMessage());
			$message = $e->getMessage();
	}
	$endTime = $this->getTime();
	Log::info(__FUNCTION__.' Finishes execution in '.($endTime-$startTime));
	Log::info(['Status'=>$status, 'Message' => $message]);
	return json_encode(['Status'=>$status,'Message'=>'Server: '.$message]);
}


public function BindEsealsWithAttributes(){
	//Purpose :- Data Binding,QC inspection.
	//Tables Involved :- eseal_mfgid,eseal_bank_mfgid,attribute_mapping,bind_history.
	//Scenarios Covered :- Checks if a po number is passed (or)  a simple data binding and updating takes place.
	$startTime = $this->getTime();    
	try{
		$status = 0;
		$message = 'Failed to bind';	  
		$mfg_id = $this->roleAccess->getMfgIdByToken(Input::get('access_token'));
		$attributeMapId = 0;
		$inspect_result = trim(Input::get('inspection_result'));
		if(!$inspect_result)
			$inspect_result = 0;
		$attributes = trim(Input::get('attributes'));
		$flagJson = trim(Input::get('flagsJson'));
	    $flagArr = json_decode($flagJson,true);	  
		$ids = trim(Input::get('ids'));
		$po_number = rtrim(ltrim(Input::get('po_number'))); 
		$material_code = Input::get('material_code');
		$transitionTime = Input::get('transitionTime');
		$isPallet =  trim(Input::get('isPallet'));
		$batch_no = Input::get('batch_no');
		
		

		Log::info(__FUNCTION__.' : '.print_r(Input::get(),true));
		if(!$batch_no)
			$batch_no = 'unknown';
		if(empty($material_code)){
			$pid = trim(Input::get('pid'));
		}	
		else{  
			$pid = Products::where('material_code',$material_code)->pluck('product_id');
		}

		$locationId = trim(Input::get('srcLocationId'));
		
		//CASE1:
		if(!empty($po_number) && !empty($ids)){

			$query = DB::table('erp_integration')->where('manufacturer_id',$mfg_id);
			if($query->pluck('id')){
				$erp = $query->select('web_service_url','token','company_code','web_service_username','web_service_password','sap_client')->get();
				$domain = $erp[0]->web_service_url;
				$token = $erp[0]->token;
				$company_code = $erp[0]->company_code;
				$sap_client = $erp[0]->sap_client;
				$erp = $this->roleAccess->getErpDetailsByUserId(Input::get('access_token'));	
				if($erp){
					$username = $erp[0]->erp_username;
					$password = $erp[0]->erp_password;
				}
				else{
					throw new Exception('There are no erp username and password');
				}
				$data = ['TOKEN'=>$token,'PORDER'=>$po_number];
				$method = 'Z030_ESEAL_GET_PORDER_DETAILS_SRV';
				$method_name = 'GET_PORDER_DETAILS';
				$url = $domain.$method.'/'.$method_name;
				$url = sprintf("%s?\$filter=%s", $url, urlencode($this->sapRepo->generateData($data)));
				$url = $url.'&sap-client='.$sap_client;
				$curl = curl_init();
				curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
				curl_setopt($curl, CURLOPT_USERPWD, "$username:$password");			
				curl_setopt($curl,CURLOPT_RETURNTRANSFER,true);
				curl_setopt($curl, CURLOPT_URL, $url);
				curl_setopt($curl, CURLOPT_HEADER, 0);
				$result = curl_exec($curl);
				curl_close($curl);

				$parseData1 = xml_parser_create();
				xml_parse_into_struct($parseData1, $result, $documentValues1, $documentIndex1);
				xml_parser_free($parseData1);
				$documentData = array();
				foreach ($documentValues1 as $data) {
					if(isset($data['tag']) && $data['tag'] == 'D:PORDER_DATA')
					{
						$documentData = $data['value'];
					}
				}
				if(empty($documentData)){
					throw new Exception('Error from SAP call');
				}
				$deXml = simplexml_load_string($documentData);
				$deJson = json_encode($deXml);
				$xml_array = json_decode($deJson,TRUE);      
				Log::info($xml_array);
				$status = $xml_array['HEADER']['Status'];

				if($status == 1){
					$attributes =json_decode($attributes, true);
					Log::info($attributes);

					$material_code = $xml_array['DATA']['FINISHED_MATERIAL']['ITEM']['MATERIAL_CODE'];
					$pid = Products::where('material_code',$material_code)->pluck('product_id');
					$batch_no = (string)$xml_array['DATA']['FINISHED_MATERIAL']['ITEM']['BATCH_NO'];
					$storage_loc_code = $xml_array['DATA']['FINISHED_MATERIAL']['ITEM']['STORAGE_LOC_CODE'];
					$plant_code = $xml_array['DATA']['PLANT_CODE'];
					$po_number = (string)$xml_array['DATA']['PRODUCTION_ORDER_NO'];
					$attributes1 = ['PLANT_CODE'=>$plant_code,'PO_NUMBER'=>(string)(trim($po_number)),'BATCH_NO'=>(string)(trim($batch_no)),'STORAGE_LOC_CODE'=>$storage_loc_code];
					Log::info($attributes1);
					$attributes2 = array_merge($attributes,$attributes1);
					//$attributes2 = array_unique($attributes2);
					$attributes2 = json_encode($attributes2);
					Log::info($attributes2);
				}
				else{
					return json_encode(['Status'=>0,'Message'=>'Data not retrieved from SAP.']);
				}
			}
			else{
				throw new Exception('There is no ERP configuration');
			}
			$ids1 = explode(',', $ids);
			$ids1 = array_unique($ids1);

			$mfgId= $this->roleAccess->getMfgIdByToken(Input::get('access_token'));
			$esealTable = 'eseal_'.$mfgId;
			$esealBankTable = 'eseal_bank_'.$mfgId;

			$cnt = DB::table($esealBankTable)->whereIn('id', $ids1)
			->where(function($query){
				$query->where('issue_status',1);
				$query->orWhere('download_status',1);
			})->count();

			Log::info(count($ids1).' == '.$cnt);
			if(count($ids1) != $cnt){
				throw new Exception('Codes count not matching with code bank');
			}
			try{
			  DB::beginTransaction();
			  
			  //Saving the list of Attributes passed and returning the AttributeMapId. 
			  $request = Request::create('scoapi/SaveBindingAttributes', 'POST', array('module_id'=> Input::get('module_id'),'access_token'=>Input::get('access_token'),'pid'=>$pid,'attributes'=>$attributes2,'lid'=>$locationId));
			  $originalInput = Request::input();//backup original input
			  Request::replace($request->input());
			  Log::info($request->input());
			  $res1 = Route::dispatch($request)->getContent();
			  $res1 = json_decode($res1);
			  if($res1->Status){
				$attributeMapId = $res1->AttributeMapId;
				Log::info('attributeMapId:'.$attributeMapId);
			  }
			  else{
				throw new Exception($res1->Message);
			  }
			  //Inserting and Updating Ids in eseal_mfgid table.
			  foreach($ids1 as $id){
				$res = DB::select('SELECT primary_id,eseal_id FROM '.$esealTable.' WHERE primary_id = ?', array($id));
				if(count($res)){
					DB::update('Update '.$esealTable.' SET attribute_map_id='.$attributeMapId.',inspection_result='.$inspect_result.' WHERE eseal_id = ? ', Array($res[0]->eseal_id));
					DB::insert('INSERT INTO bind_history (eseal_id,location_id,attribute_map_id,created_on) values (?, ?,?,?)', array($res[0]->primary_id,$locationId ,$attributeMapId,$transitionTime));
				}else{
					DB::insert('INSERT INTO '.$esealTable.' (primary_id,pid,attribute_map_id,batch_no,po_number,inspection_result,mfg_date) values (?, ? ,?,?,?,?,?)', array($id,$pid,$attributeMapId,(string)(trim($batch_no)),(string)(trim($po_number)),$inspect_result,$transitionTime));  
					DB::insert('INSERT INTO bind_history (eseal_id,location_id,attribute_map_id,created_on) values (?, ?,?,?)', array($id,$locationId ,$attributeMapId,$transitionTime));
				}
			  }
			  //Updating esealBankTable
			  DB::table($esealBankTable)->whereIn('id',$ids1)->update(['used_status'=>1, 'location_id'=> $locationId,'pid'=>$pid,'utilizedDate'=>$transitionTime]);
			  $status = 1;
			  $message = 'Binding with PO_NUMBER Succesfull';
			  DB::commit();
			}catch(PDOException $e){
				DB::rollback();
				Log::error($e->getMessage());
				throw new Exception('Error while binding');  
			}
			Log::info(Array('Status'=>$status, 'Message' => $message));
			return json_encode(Array('Status'=>$status, 'Message' =>'Server: '.$message));
		}   //CASE2
		else{
			if( !empty($ids) && !empty($locationId) && !empty($pid) && !empty($attributes)){
			  //Saving the list of Attributes passed and returning the AttributeMapId. 
			  $request = Request::create('scoapi/SaveBindingAttributes', 'POST', array('module_id'=> Input::get('module_id'),'access_token'=>Input::get('access_token'),'attributes'=>$attributes,'lid'=>$locationId,'pid'=>$pid));
			  $originalInput = Request::input();//backup original input
			  Request::replace($request->input());
			  Log::info($request->input());
			  $res1 = Route::dispatch($request)->getContent();//invoke API
			  $res1 = json_decode($res1);

	  if($res1->Status){
		$attributeMapId = $res1->AttributeMapId;
	  }
	  else{
		throw new Exception($res1->Message);
	  }
	  $ids1 = explode(',', $ids);
	  $ids1 = array_unique($ids1);
	  $newIds = '\''.implode('\',\'', $ids1).'\'';

	  $locationObj = new Locations\Locations();
	  $mfgId = $locationObj->getMfgIdForLocationId($locationId);
	  Log::info('-----'.$mfgId);
	  if(!empty($mfgId)){
		$esealTable = 'eseal_'.$mfgId;
		$esealBankTable = 'eseal_bank_'.$mfgId;

		//Checking whether the passed EsealIds are downloaded or not.
		$cnt = DB::table($esealBankTable)->whereIn('id', $ids1)
		->where(function($query){
			$query->where('issue_status',1);
			$query->orWhere('download_status',1);
		})->count();
		Log::info(count($ids1).' == '.$cnt);

		if(count($ids1) != $cnt){

		if(isset($flagArr['ignoreInvalid']) && $flagArr['ignoreInvalid'] == 1){
	  	$ids1 = DB::table($esealBankTable)->whereIn('id', $ids1)
				->where(function($query){
					$query->where('issue_status',1);
					$query->orWhere('download_status',1);
				})->lists('id');
	  	}
	  	else{
	  	  throw new Exception('Codes count not matching with code bank');	
	  	}

	  }

		$resultCnt = DB::table($esealTable)->whereIn('primary_id', $ids1)->count();
		Log::info($resultCnt);
		$productExists = DB::table('products')->select('product_id')->where('product_id', $pid)->get();
		DB::beginTransaction();	  
		Log::info($productExists);
		if(!empty($productExists[0]->product_id)){
			try{
				foreach($ids1 as $id){
				//Inserting and Updating Ids in eseal_mfgid table.
					$res = DB::select('SELECT primary_id,eseal_id FROM '.$esealTable.' WHERE primary_id = ?', array($id));
				if(!$isPallet){
				if(count($res)){

					if(isset($flagArr['ignoreMultiBinding']) && $flagArr['ignoreMultiBinding'] == 0)
			  		      throw new Exception('Some of the Ids are already binded');

					DB::update('Update '.$esealTable.' SET attribute_map_id='.$attributeMapId.',inspection_result='.$inspect_result.' WHERE eseal_id = ? ', Array($res[0]->eseal_id));
					DB::insert('INSERT INTO bind_history (eseal_id,location_id,attribute_map_id,created_on) values (?, ?,?,?)', array($res[0]->primary_id,$locationId ,$attributeMapId,$transitionTime));
				}else{
					DB::insert('INSERT INTO '.$esealTable.' (primary_id,pid,attribute_map_id,batch_no,inspection_result,mfg_date) values (?, ? ,?,?,?,?)', array($id,$pid,$attributeMapId,(string)(trim($batch_no)),$inspect_result,$transitionTime));  
					DB::insert('INSERT INTO bind_history (eseal_id,location_id,attribute_map_id,created_on) values (?, ?,?,?)', array($id,$locationId ,$attributeMapId,$transitionTime));
				}
			}
			else{
			if(count($res)){

				  if(isset($flagArr['ignoreMultiBinding']) && $flagArr['ignoreMultiBinding'] == 0)
			  		throw new Exception('Some of the Ids are already binded');

					DB::update('Update '.$esealTable.' SET attribute_map_id='.$attributeMapId.',inspection_result='.$inspect_result.' WHERE eseal_id = ? ', Array($res[0]->eseal_id));
					DB::insert('INSERT INTO bind_history (eseal_id,location_id,attribute_map_id,created_on) values (?, ?,?,?)', array($res[0]->primary_id,$locationId ,$attributeMapId,$transitionTime));
				}else{
					DB::insert('INSERT INTO '.$esealTable.' (primary_id,pid,attribute_map_id,batch_no,inspection_result,mfg_date,level_id,pkg_qty) values (?, ? ,?,?,?,?,?,?)', array($id,$pid,$attributeMapId,(string)(trim($batch_no)),$inspect_result,$transitionTime,8,0.00));  
					DB::insert('INSERT INTO bind_history (eseal_id,location_id,attribute_map_id,created_on) values (?, ?,?,?)', array($id,$locationId ,$attributeMapId,$transitionTime));
				}	
			}
			  // Maintain the binding history
				
				}
				//Update esealBankTable
				DB::table($esealBankTable)->whereIn('id',$ids1)->update(['used_status'=>1, 'location_id'=> $locationId,'pid'=>$pid,'utilizedDate'=>$transitionTime]);
			}catch(PDOException $e){
				DB::rollback();
				Log::error($e->getMessage());
				throw new Exception('Error while binding');  
			}
			$status = 1;
			$message = 'Binding Succesfull';
			DB::commit();
		}else{
			throw new Exception('Some codes already exists or product not found');
		}
	  }else{
		throw new Exception('Location doesn\'t belong to any Customer');
	  }
	}else{
		throw new Exception('Some of the params missing');
	}
}
}catch(Exception $e){
	DB::rollback();
	Log::info($e->getMessage());
	$message = $e->getMessage();
}
if($status){
	Event::fire('scoapi/BindEseals', array('attribute_map_id'=>$attributeMapId, 'codes'=>$ids1, 'mfg_id'=>$mfgId));
}
$endTime = $this->getTime();
Log::info(__FUNCTION__.' Finishes execution in '.($endTime-$startTime));
Log::info(Array('Status'=>$status, 'Message' => $message));
return json_encode(Array('Status'=>$status, 'Message' =>'Server: '.$message));	

}


  private function sapCall($mfgId,$method,$method_name,$data,$access_token){

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
			
			$erp = $this->roleAccess->getErpDetailsByUserId($access_token);	
			if(!empty($erp)){
				$username = $erp[0]->erp_username;
				$password = $erp[0]->erp_password;			
			}
			else{
				throw new Exception('There are no erp username and password');
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

  public function callSapApi(){
	
		$xml = '<entry xmlns="http://www.w3.org/2005/Atom" xmlns:m="http://schemas.microsoft.com/ado/2007/08/dataservices/metadata" xmlns:d="http://schemas.microsoft.com/ado/2007/08/dataservices" xml:base="http://115.112.162.52:8000/sap/opu/odata/sap/Z0013_ESEAL_CREATE_SALES_ORDER_SRV/">
<id>http://115.112.162.52:8000/sap/opu/odata/sap/Z0013_ESEAL_CREATE_SALES_ORDER_SRV/CREATE_SO(\'123\')</id>
<title type="text">CREATE_SO(\'123\')</title>
<updated>2015-07-20T07:31:01Z</updated>
<category term="Z0013_ESEAL_CREATE_SALES_ORDER_SRV.CREATE_SO" scheme="http://schemas.microsoft.com/ado/2007/08/dataservices/scheme" />
<link href="CREATE_SO(\'123\')" rel="self" title="CREATE_SO" />
<content type="application/xml">
 <m:properties>
<d:Sales_Order />
<d:Message />
<d:Eseal_input>"<![CDATA[<?xml version="1.0" encoding="utf-8" ?> 
<REQUEST>
<DATA>
<INPUT TOKEN_NO="3h8M8A2q8iv7nMq4Rpft5G5TBE4O7PC8" ESEALKEY="1234" DOC_TYPE="ZECO" SALES_ORG="1000" DISTR_CHAN="35" DIVISION="10" CREATE_DELIVERY="" SHIPPING_POINT="" PO_REFERENCE="123" /> 
<ITEMS>
<ITEM ITM_NUMBER="000010" NAME="2134801711010T" QTY="1" UOM="EA" PLANT="9020" STORAGE_LOC="FG01"   /> 
</ITEMS>
<PARTNERS>
<PARTNER ROLE="SH" PARTN_NUMB="1000" ITM_NUMBER="000010" TITLE="COMPANY" NAME="TEST_NAME1" NAME_2="TEST_NAME_2" NAME_3="TEST_NAME_3" STREET="TEST_STREET" COUNTRY_KEY="IN" POSTL_CODE="767676" CITY="HYD" DISTRICT="HYD" REGION_KEY="01" TELEPHONE="8888888888" /> 
<PARTNER ROLE="SP" PARTN_NUMB="8104027" ITM_NUMBER="000000" TITLE="" NAME="" NAME_2="" NAME_3="" STREET="" COUNTRY_KEY="" POSTL_CODE="" CITY="" DISTRICT="" REGION_KEY="" TELEPHONE="" /> 
</PARTNERS>
<CONDITIONS>
<CONDITION ITM_NUMBER="" COND_TYPE="" COND_VALUE="" /> 
</CONDITIONS>
</DATA>
</REQUEST>]]>"</d:Eseal_input>
 </m:properties>
</content>
 </entry>';


$method = 'POST';
$username ='eseal1';
$password ='eseal@123';
$sap_client = 800;
	
$url = 'http://115.112.162.52:8000/sap/opu/odata/sap/Z0013_ESEAL_CREATE_SALES_ORDER_SRV/CREATE_SO?&sap-client='.$sap_client;
Log::info('SAP start:-');
$response = $this->sapRepo->request($username,$password,$url, $method,null,'xml',2,null,$xml);
Log::info('SAP END:-');

Log::info($response);
	
  }

  public function callSapApi1(){
	 
	$locationId  = $this->roleAccess->getLocIdByToken(Input::get('access_token'));
	$manufacturerId =  $this->roleAccess->getMfgIdByToken(Input::get('access_token'));
	
	$plant_code = DB::table('locations')->where('location_id',$locationId)->pluck('erp_code');
	Log::info('PLANT CODE :'.$plant_code);

	$method = 'Z0040_GET_INVENTORY_SKU_SRV';
	$method_name = 'SKU';
	$data =['PLANT'=>$plant_code];
	//return $data;
	$response = $this->sapRepo->callSapApi($method,$method_name,$data,null,$manufacturerId);
	Log::info($response);

	return $response;

  }



  public function BindEseals(){

	//Purpose :- Data Binding.
	//Tables Involved :- eseal_mfgid,eseal_bank_mfgid,attribute_mapping,bind_history,erp_integration,track_history,track_details
	//Scenarios Covered :- If a PO number is passed binding takes place with the product and attributes in the PO (or)  a simple data binding and updating takes place.	

	$startTime = $this->getTime();    
	try{

	  $status = 0;
	  $message = 'Failed to bind';
	  $mfgId= $this->roleAccess->getMfgIdByToken(Input::get('access_token'));
	  $attributeMapId = 0;
	  $ids = trim(Input::get('ids'));
	  $po_number = trim(Input::get('po_number')); 
	  $attributes = trim(Input::get('attributes'));
	  $ignoreMultiPackingForPo = trim(Input::get('ignoreMultiPackingForPo'));
	  $packingValues = trim(Input::get('packingValues'));
	  $flagJson = trim(Input::get('flagsJson'));
	  $flagArr = json_decode($flagJson,true);	  
	  $pid = trim(Input::get('pid'));
	  $locationId = trim(Input::get('srcLocationId'));
	  $attributeMapId = trim(Input::get('attribute_map_id'));
	  $transitionTime = Input::get('transitionTime');
	  $i = 0;
	  Log::info(__FUNCTION__.' : '.print_r(Input::get(),true));

		if(!empty($po_number) && empty($ignoreMultiPackingForPo))
		{
			$cnt =  DB::table('eseal_'.$mfgId)
					  ->where(['po_number'=>$po_number,'level_id'=>0])
					  ->count();
			if($cnt)
			{
				 $childs = DB::table('eseal_'.$mfgId.' as eseal')
							 ->leftJoin('track_history as th','th.track_id','=','eseal.track_id')
							 ->where(['eseal.level_id'=>0])
							 ->where(['th.src_loc_id'=>$locationId,'th.dest_loc_id'=>0,'po_number'=>$po_number])
							 ->lists('primary_id');
				$queries = DB::getQueryLog();
			   //$x = end($queries);
				
			   //$data[] = ['childs'=>$childs];
			   //echo '<pre/>';print_r($childs);exit;
				 $status =2;
				$message = 'Data already exists for the given PO number';
				return json_encode(['Status'=>$status,'Message'=>'Server: '.$message,'Data'=>$childs]);   
			}
			
		}

	  //CASE1:-
	  if(!empty($po_number) && !empty($ids))
	  {
	 
			$data = ['PORDER'=>$po_number];
			$method = 'Z030_ESEAL_GET_PORDER_DETAILS_SRV';
			$method_name = 'GET_PORDER_DETAILS';
			$response = $this->sapCall($mfgId,$method,$method_name,$data,Input::get('access_token'));
			Log::info('SAP response:-');
			Log::info($response);
			$response = json_decode($response);
			if($response->Status){
				$result = $response->Data;
			$parseData1 = xml_parser_create();
			xml_parse_into_struct($parseData1, $result, $documentValues1, $documentIndex1);
			xml_parser_free($parseData1);
			$documentData = array();
			foreach ($documentValues1 as $data) {
				if(isset($data['tag']) && $data['tag'] == 'D:PORDER_DATA')
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
			Log::info($xml_array);

		   $status = $xml_array['HEADER']['Status'];

		if($status == 1){

			$material_code = $xml_array['DATA']['FINISHED_MATERIAL']['ITEM']['MATERIAL_CODE'];
			$product = Products::where(['material_code'=>$material_code,'manufacturer_id'=>$mfgId])->get(['product_id','uom_unit_value']);
			


			if(!$product){
				throw new Exception('The given material in po does not exist');
			}

			$pid = $product[0]->product_id;
			$pkg_qty = $product[0]->uom_unit_value;
			
			$batchEnabled = Products::where(['product_id'=>$pid,'manufacturer_id'=>$mfgId])->pluck('is_batch_enabled');
			if(is_array($xml_array['DATA']['FINISHED_MATERIAL']['ITEM']['BATCH_NO'])){
				$attrArr = json_decode($attributes,true);
				if(json_last_error() != JSON_ERROR_NONE)
					throw new Exception('Invalid Json Format.');
				if(empty($attrArr))
					throw new Exception('Attributes are empty.');
				else if(!array_key_exists('batch_no', $attrArr))
				{
					if($batchEnabled == 1)
					  throw new Exception('Batch No is not passed in Attributes.');
					else
					  $batch_no = '';
				}
				
			}
			else
			{
				$batch_no = (string)$xml_array['DATA']['FINISHED_MATERIAL']['ITEM']['BATCH_NO'];
			}
			$po_number = (string)$xml_array['DATA']['PRODUCTION_ORDER_NO'];

			if(empty($attributes)){
			
			$storage_loc_code = $xml_array['DATA']['FINISHED_MATERIAL']['ITEM']['STORAGE_LOC_CODE'];
			$plant_code = $xml_array['DATA']['PLANT_CODE'];
			$attributes = json_encode(['PO_NUMBER'=>(string)(trim($po_number)),'PLANT_CODE'=>trim($plant_code),'BATCH_NO'=>(string)(trim($batch_no)),'STORAGE_LOC_CODE'=>trim($storage_loc_code)]);
			}				
		   }
		else{
			return json_encode(['Status'=>0,'Message'=>'Data not retrieved from SAP.']);
		}

}
else{
	throw new Exception($response->Message);
}

Log::info('Attributes');
Log::info($attributes);

		$ids1 = explode(',', $ids);
		$ids1 = array_unique($ids1);

		$mfgId= $this->roleAccess->getMfgIdByToken(Input::get('access_token'));

		$esealTable = 'eseal_'.$mfgId;
		$esealBankTable = 'eseal_bank_'.$mfgId;

		$cnt = DB::table($esealBankTable)->whereIn('id', $ids1)
					->where(function($query){
					$query->where('issue_status',1);
					$query->orWhere('download_status',1);
				})->count();
				
	  Log::info(count($ids1).' == '.$cnt);
	  if(count($ids1) != $cnt){
		throw new Exception('Codes count not matching with code bank');
	  }
	  if(!empty($packingValues)){
	  	$packingValues = explode(',',$packingValues);
	  	if(count($ids1) != count($packingValues))
	  		throw new Exception('The packageValues differ from Codes being downloaded.');
	  }
		try{
			 DB::beginTransaction();
			  //Saving the list of Attributes passed and returning the AttributeMapId. 
			  $request = Request::create('scoapi/SaveBindingAttributes', 'POST', array('module_id'=> Input::get('module_id'),'access_token'=>Input::get('access_token'),'pid'=>$pid,'attributes'=>$attributes,'lid'=>$locationId));
			  $originalInput = Request::input();//backup original input
			  Request::replace($request->input());
			  Log::info($request->input());
			  $res1 = Route::dispatch($request)->getContent();
			  $res1 = json_decode($res1);
			  if($res1->Status){
			   $attributeMapId = $res1->AttributeMapId;
			  }
			  else{
				throw new Exception($res1->Message);
			  }
				 
			foreach($ids1 as $id){

				if(!empty($packingValues))
					$pkg_qty = $packingValues[$i];
			//Inserting records into eseal_mfgid and bind_history tables.	
			  $res = DB::select('SELECT primary_id,eseal_id FROM '.$esealTable.' WHERE primary_id = ?', array($id));
			  if(count($res)){
				DB::update('Update '.$esealTable.' SET attribute_map_id='.$attributeMapId.' WHERE eseal_id = ? ', Array($res[0]->eseal_id));
				DB::insert('INSERT INTO bind_history (eseal_id,location_id,attribute_map_id,created_on) values (?, ?,?,?)', array($res[0]->primary_id,$locationId ,$attributeMapId,$transitionTime));
			  }else{
				DB::insert('INSERT INTO '.$esealTable.' (primary_id,pid,attribute_map_id,batch_no,po_number,mfg_date,pkg_qty) values (?, ?, ?,?, ?,?,?)', array($id,$pid,$attributeMapId,(string)(trim($batch_no)),(string)(trim($po_number)),$transitionTime,$pkg_qty));  
				DB::insert('INSERT INTO bind_history (eseal_id,location_id,attribute_map_id,created_on) values (?, ?,?,?)', array($id,$locationId ,$attributeMapId,$transitionTime));
			  }

			  $i++;
			  
			}
			DB::commit();
		  //Trackupdating
		  $ids = implode(',',$ids1);
		  $transitionId = Transaction::where(['name'=>'Label Printing','manufacturer_id'=>$mfgId])->pluck('id');
		  $request = Request::create('scoapi/UpdateTracking', 'POST', array('module_id'=>Input::get('module_id'),'access_token'=>Input::get('access_token'),'srcLocationId'=>$locationId,'codes'=>$ids,'transitionId'=>(int)$transitionId,'transitionTime'=>$transitionTime));
		  $originalInput = Request::input();//backup original input
		  Request::replace($request->input());
		  Log::info($request->input());
		  $response = Route::dispatch($request)->getContent();
		  $response = json_decode($response);
		  if($response->Status){
			
			DB::table($esealBankTable)->whereIn('id',$ids1)->update(['used_status'=>1,'location_id'=> $locationId,'pid'=>$pid,'utilizedDate'=>$transitionTime]);
			$status = 1;
			$message = 'Binding with PO_NUMBER and trackupdate Succesfull';
			
		   }
		  else{
			 $status =0;
			  throw new Exception($response->Message);
		  }

		  }catch(PDOException $e){
			DB::rollback();
			Log::error($e->getMessage());
			throw new Exception('Error while binding');  
		  }
		  DB::commit();		  
		  return json_encode(Array('Status'=>$status, 'Message' =>'Server: '.$message));
	  }
	  else{  //CASE2:
	  if( !empty($ids) && !empty($locationId) && !empty($pid) ){
		$ids1 = explode(',', $ids);
		$ids1 = array_unique($ids1);
		$newIds = '\''.implode('\',\'', $ids1).'\'';

		$locationObj = new Locations\Locations();
		$mfgId = $locationObj->getMfgIdForLocationId($locationId);
		
		if(!empty($mfgId)){
		  $esealTable = 'eseal_'.$mfgId;
		  $esealBankTable = 'eseal_bank_'.$mfgId;

	  $cnt = DB::table($esealBankTable)->whereIn('id', $ids1)
				->where(function($query){
					$query->where('issue_status',1);
					$query->orWhere('download_status',1);
				})->count();
	  Log::info(count($ids1).' == '.$cnt);
	  if(count($ids1) != $cnt){

		if(isset($flagArr['ignoreInvalid']) && $flagArr['ignoreInvalid'] == 1){
	  	$ids1 = DB::table($esealBankTable)->whereIn('id', $ids1)
				->where(function($query){
					$query->where('issue_status',1);
					$query->orWhere('download_status',1);
				})->lists('id');
	  	}
	  	else{
	  	  throw new Exception('Codes count not matching with code bank');	
	  	}

	  }
	  $resultCnt = DB::table($esealTable)->whereIn('primary_id', $ids1)->count();
	  Log::info($resultCnt);
	  
	  $productExists = DB::table('products')->select('product_id','uom_unit_value')->where('product_id', $pid)->get();
	  DB::beginTransaction();
	  //Log::info(print_r($result[0]->cnt, true));
	  Log::info($productExists);
		if(!empty($productExists[0]->product_id)){

		$pkg_qty = $productExists[0]->uom_unit_value;	

		$batch_no = DB::table('attribute_mapping')->where(['attribute_map_id'=>$attributeMapId,'attribute_name'=>'batch_no'])->pluck('value');		 
		if(!$batch_no)
			$batch_no ='';

		$length = DB::table('attribute_mapping')->where(['attribute_map_id'=>$attributeMapId,'attribute_name'=>'length'])->pluck('value');		 
		if(!empty($length))
			$pkg_qty = $length;
				//Log::info(print_r($ids1,true));

		$po_number = DB::table('attribute_mapping')->where(['attribute_map_id'=>$attributeMapId,'attribute_name'=>'po_number'])->pluck('value');		 
		if(!$po_number){
            $po_number = '';
		}
        else{
           $existingPOCount = DB::table($esealTable)
								->whereIn('primary_id',$ids1)
								->where(function($query) {
								           $query->whereNotNull('po_number')
							                     ->orWhere('po_number','!=','');
											})
												 ->count();
		   if($existingPOCount)
		        throw new Exception('Some of the Ids are used for PO.');

        }

		  try{
			foreach($ids1 as $id){
			  $res = DB::select('SELECT primary_id,eseal_id FROM '.$esealTable.' WHERE primary_id = ?', array($id));
			  if(count($res)){
			  	
			  	if(isset($flagArr['ignoreMultiBinding']) && $flagArr['ignoreMultiBinding'] == 0)
			  		throw new Exception('Some of the Ids are already binded');

				DB::update('Update '.$esealTable.' SET pid = '.$pid.', attribute_map_id = '.$attributeMapId.' WHERE eseal_id = ? ', Array($res[0]->eseal_id));
				DB::insert('INSERT INTO bind_history (eseal_id,location_id,attribute_map_id,created_on) values (?, ?,?,?)', array($res[0]->primary_id,$locationId ,$attributeMapId,$transitionTime));
			  }else{
				DB::insert('INSERT INTO '.$esealTable.' (primary_id, pid, attribute_map_id,mfg_date,batch_no,po_number,pkg_qty) values (?, ?, ?,?,?,?,?)', array($id, $pid, $attributeMapId,$transitionTime,$batch_no,$po_number,$pkg_qty));  
				DB::insert('INSERT INTO bind_history (eseal_id,location_id,attribute_map_id,created_on) values (?, ?,?,?)', array($id,$locationId ,$attributeMapId,$transitionTime));
			  }
			}
             
            DB::table($esealBankTable)->whereIn('id',$ids1)->update(['used_status'=>1,'location_id'=> $locationId]);			
			
			foreach($ids1 as $id){
			DB::table($esealBankTable)->where('id',$id)->update(Array('used_status'=>1, 'location_id'=> $locationId));
		  }

		  }catch(PDOException $e){
			DB::rollback();
			Log::error($e->getMessage());
			throw new Exception('Error while binding');  
		  }
		  $status = 1;
		  $message = 'Binding Succesfull';
		  DB::commit();
		}else{
		  throw new Exception('Some codes already exists or product not found');
		}
	  }else{
		throw new Exception('Location doesn\'t belong to any Customer');
	  }
	}else{
	  throw new Exception('Some of the params missing');
	}
}
	}catch(Exception $e){
	  $status =0;
	  DB::rollback();
	  Log::info($e->getMessage());
	  $message = $e->getMessage();
	}
	if($status){
		Event::fire('scoapi/BindEseals', array('attribute_map_id'=>$attributeMapId, 'codes'=>$ids1, 'mfg_id'=>$mfgId));
	}
	$endTime = $this->getTime();
	Log::info(__FUNCTION__.' Finishes execution in '.($endTime-$startTime));
	DB::commit();	
	 return json_encode(Array('Status'=>$status, 'Message' =>'Server: '.$message));
  }


  public function BindEseals1(){
  	

	$startTime = $this->getTime();    
	try{

	  $status = 0;
	  $message = 'Failed to bind';
	  $mfgId= $this->roleAccess->getMfgIdByToken(Input::get('access_token'));
	  $attributeMapId = 0;
	  $ids = trim(Input::get('ids'));
	  $parent = trim(Input::get('parent'));
	  $po_number = trim(Input::get('po_number')); 
	  $attributes = trim(Input::get('attributes'));
	  $flagJson = trim(Input::get('flagsJson'));
	  $flagArr = json_decode($flagJson,true);	  
	  $pid = trim(Input::get('pid'));
	  $locationId = trim(Input::get('srcLocationId'));
	  $attributeMapId = trim(Input::get('attribute_map_id'));
	  $transitionTime = Input::get('transitionTime');
	  Log::info(__FUNCTION__.' : '.print_r(Input::get(),true));

		

	 if(empty($ids) && empty($locationId) && empty($pid))
	 	throw new Exception('Parameters Missing.');
	 
		$ids1 = explode(',', $ids);
		$ids1 = array_unique($ids1);
		$newIds = '\''.implode('\',\'', $ids1).'\'';

		$locationObj = new Locations\Locations();
		$mfgId = $locationObj->getMfgIdForLocationId($locationId);
                $erp_code = $locationObj->getSAPCodeFromLocationId($locationId);
		
		if(empty($mfgId))
			throw new Exception('Locations does\'nt belong to any customer');

		  $esealTable = 'eseal_'.$mfgId;
		  $esealBankTable = 'eseal_bank_'.$mfgId;

     	  DB::beginTransaction();

	  $cnt = DB::table($esealBankTable)->whereIn('id', $ids1)
				->where(function($query){
					$query->where('issue_status',1);
					$query->orWhere('download_status',1);
				})->count();
	  //Log::info(count($ids1).' == '.$cnt);
	  if(count($ids1) != $cnt){

		if(isset($flagArr['ignoreInvalid']) && $flagArr['ignoreInvalid'] == 1){
	  	$ids1 = DB::table($esealBankTable)->whereIn('id', $ids1)
				->where(function($query){
					$query->where('issue_status',1);
					$query->orWhere('download_status',1);
				})->lists('id');
	  	}
	  	else{
	  	  throw new Exception('Codes count not matching with code bank');	
	  	}

	  }
	  $resultCnt = DB::table($esealTable)->whereIn('primary_id', $ids1)->count();
	  //Log::info($resultCnt);
	  
	  $productExists = DB::table('products')->select('product_id','uom_unit_value','expiry_period')->where('product_id', $pid)->get();
	  
	  //Log::info(print_r($result[0]->cnt, true));
	  //Log::info($productExists);
		if(empty($productExists[0]->product_id))
			throw new Exception('Product not found');

		$pkg_qty = $productExists[0]->uom_unit_value;	

		$batch_no = DB::table('attribute_mapping')->where(['attribute_map_id'=>$attributeMapId,'attribute_name'=>'batch_no'])->pluck('value');		 
		if(!$batch_no)
			$batch_no ='';

	

                $servBatchExists = DB::table('attribute_mapping')->where(['attribute_map_id'=>$attributeMapId,'attribute_name'=>'service_batch_no'])->count('id');		 
		if($servBatchExists){
			$batch_no= $erp_code.date('Y');
                  DB::table('attribute_mapping')->where(['attribute_map_id'=>$attributeMapId,'attribute_name'=>'service_batch_no'])->update(['value'=>$batch_no]);		 			
                  $storage_loc_code = DB::table('attribute_mapping')->where(['attribute_map_id'=>$attributeMapId,'attribute_name'=>'storage_loc_code'])->pluck('value');		 			          
		}


		$expValid = DB::table('attribute_mapping')->where(['attribute_map_id'=>$attributeMapId,'attribute_name'=>'exp_valid'])->count('id');		 

		if($expValid){
        //       Log::info('expiry period is :'.$productExists[0]->expiry_period);
   			if(empty($productExists[0]->expiry_period) || is_null($productExists[0]->expiry_period))
			 throw new Exception('Expiry period is not configured for the product.');
           
          $expDate = DB::table('attribute_mapping')->where(['attribute_map_id'=>$attributeMapId,'attribute_name'=>'date_of_exp'])->count('id');		 
              if(!$expDate)
           	 throw new Exception('There is no expiry date attribute for update.');

           $expiry_date =  date('Y-m-d', strtotime("+".$productExists[0]->expiry_period." days"));

           DB::table('attribute_mapping')->where(['attribute_map_id'=>$attributeMapId,'attribute_name'=>'date_of_exp'])->update(['value'=>$expiry_date]);		 


		}

		$po_number = DB::table('attribute_mapping')->where(['attribute_map_id'=>$attributeMapId,'attribute_name'=>'po_number'])->pluck('value');		 
		if(!$po_number){
            $po_number = '';
		}
        else{
           $existingPOCount = DB::table($esealTable)
								->whereIn('primary_id',$ids1)
								->where(function($query) {
								           $query->whereNotNull('po_number')
							                     ->orWhere('po_number','!=','');
											})
												 ->count();
		   if($existingPOCount)
		        throw new Exception('Some of the Ids are used for PO.');

        }

		  try{
			foreach($ids1 as $id){
			  $res = DB::select('SELECT primary_id,eseal_id,pid FROM '.$esealTable.' WHERE primary_id = ?', array($id));
			  if(count($res)){
			  	
			  	if(isset($flagArr['ignoreMultiBinding']) && $flagArr['ignoreMultiBinding'] == 0){

			  		if($parent){

			  	$processedCount = 	DB::table($esealTable)
			  		                   ->where('parent_id',$parent)
			  		                   ->whereIn('primary_id',$ids1)
			  		                   ->count('eseal_id');
			  		if($processedCount == $resultCnt){
			  			$message = 'Already packed';
			  			$status=2;
			  			goto commit;
			  		}    
			  		else{
			  		throw new Exception('Some of the Ids are already binded');
			  	   }               

			  	}	
			  	else{
			  		throw new Exception('Some of the Ids are already binded');
			  	}

                }

                if($res[0]->pid != $pid)
			  		throw new Exception('The IOT is being re-binded to different material');

				DB::update('Update '.$esealTable.' SET pid = '.$pid.', attribute_map_id = '.$attributeMapId.' WHERE eseal_id = ? ', Array($res[0]->eseal_id));
				DB::insert('INSERT INTO bind_history (eseal_id,location_id,attribute_map_id,created_on) values (?, ?,?,?)', array($res[0]->primary_id,$locationId ,$attributeMapId,$transitionTime));
			  }else{
				DB::insert('INSERT INTO '.$esealTable.' (primary_id, pid, attribute_map_id,mfg_date,batch_no,po_number,pkg_qty) values (?, ?, ?,?,?,?,?)', array($id, $pid, $attributeMapId,$transitionTime,$batch_no,$po_number,$pkg_qty));  
				DB::insert('INSERT INTO bind_history (eseal_id,location_id,attribute_map_id,created_on) values (?, ?,?,?)', array($id,$locationId ,$attributeMapId,$transitionTime));
			  }
			}

			if($servBatchExists)              
                   DB::table($esealTable)->whereIn('primary_id',$ids1)
                    ->update(['storage_location'=>$storage_loc_code]);
             
            DB::table($esealBankTable)->whereIn('id',$ids1)->update(['used_status'=>1,'location_id'=> $locationId,'pid'=>$pid,'utilizedDate'=>$this->getDate()]);		
                 DB::commit();
	
			
		//	foreach($ids1 as $id){
		//	DB::table($esealBankTable)->where('id',$id)->update(Array('used_status'=>1, 'location_id'=> $locationId));
		//  }

		  }catch(PDOException $e){
		     DB::rollback();
			Log::error($e->getMessage());
			throw new Exception('Error while binding');  
		  }
		  $status = 1;
		  $message = 'Binding Succesfull';

	      commit:	  
	}catch(Exception $e){
	  $status =0;
	  DB::rollback();
	  Log::info($e->getMessage());
	  $message = $e->getMessage();
	}
	if($status){
		Event::fire('scoapi/BindEseals', array('attribute_map_id'=>$attributeMapId, 'codes'=>$ids1, 'mfg_id'=>$mfgId));
	}
	$endTime = $this->getTime();
	Log::info(__FUNCTION__.' Finishes execution in '.($endTime-$startTime));
		
	 return json_encode(Array('Status'=>$status, 'Message' =>'Server: '.$message));
  }


  public function recharge(){

  	$startTime = $this->getTime();    
	try{

	  $status = 0;
	  $message = 'Failed to recharge';
	  $mfgId= $this->roleAccess->getMfgIdByToken(Input::get('access_token'));
	  $attributeMapId = 0;
	  $ids = trim(Input::get('ids'));
	  $locationId = $this->roleAccess->getLocIdByToken(Input::get('access_token'));
	  $mfgId = $this->roleAccess->getMfgIdByToken(Input::get('access_token'));
	  $transitionTime = Input::get('transitionTime');
	  $transitionId = Input::get('transitionId');
	  Log::info(__FUNCTION__.' : '.print_r(Input::get(),true));


		
      if(empty($ids))
	 	throw new Exception('IOT field Missing');

	  if(empty($transitionId))
	 	throw new Exception('Transition field Missing');

	  if(empty($mfgId))
			throw new Exception('Locations does\'nt belong to any customer');

        $esealTable = 'eseal_'.$mfgId;
        $esealBankTable = 'eseal_bank_'.$mfgId;

        $ids1 = explode(',', $ids);
		$ids1 = array_unique($ids1);
		$newIds = '\''.implode('\',\'', $ids1).'\'';

	  $cnt = DB::table($esealBankTable)->whereIn('id', $ids1)
				->where(function($query){
					$query->where('issue_status',1);
					$query->orWhere('download_status',1);
				})->count();

	  //Log::info(count($ids1).' == '.$cnt);

	  if(count($ids1) != $cnt)
	  	  	  throw new Exception('Codes count not matching with code bank');	  


	  $resultsCnt = DB::table($esealTable)->whereIn('primary_id', $ids1)->count();
	  //Log::info($resultsCnt);

	  if($resultsCnt != count($ids1))
	  	throw new Exception('Some of the IOT\'s are not binded');

	  $distinctGroup = DB::table($esealTable.' as es')
	                   ->join('products as pr','pr.product_id','=','es.pid')
	                   ->whereIn('primary_id', $ids1)
	                   ->distinct()
	                   ->get(['group_id']);

	 DB::beginTransaction();                  
  
  foreach ($distinctGroup as $group) {
  	$jsonArray = array();

//Log::info($group->group_id);
if(empty($attrSet = DB::table('product_attributesets')
	                 ->where(['location_id'=>$locationId,'product_group_id'=>$group->group_id])
	                 ->pluck('attribute_set_id')))
	  	throw new Exception('There is no attibute-set defined for the product group');
	  //Log::info($attrSet);

	  if(empty($attrArray = DB::table('attribute_set_mapping as asm')
	                  ->join('attributes as atr','atr.attribute_id','=','asm.attribute_id')
	                  //->where('attribute_set_id',$group->group_id)
	                  ->where('attribute_set_id',$attrSet)
	                  ->get(['attribute_code','default_value'])))
	  	throw new Exception('There are no attributes defined in the attribute set');


	  foreach($attrArray as $attr){
	  	$jsonArray[$attr->attribute_code] = $attr->default_value;
	  }
//Log::info($jsonArray);
	    if(!isset($jsonArray['exp_valid']) || !isset($jsonArray['charging_date']) || !isset($jsonArray['date_of_exp']) || !isset($jsonArray['material_code']))
	    	throw new Exception('Required attributes for recharge are not configured');

	    $groupIds = DB::table('eseal_'.$mfgId.' as es')
	                 ->join('products as pr','pr.product_id','=','es.pid')
	                 ->whereIn('primary_id',$ids1)
	                 ->where('group_id',$group->group_id)
	                 ->lists('primary_id');

        $expiry_period = DB::table('eseal_'.$mfgId.' as es')
	                        ->join('products as pr','pr.product_id','=','es.pid')
	                        ->whereIn('primary_id',$ids1)
	                        ->where('group_id',$group->group_id)
	                        ->pluck('expiry_period');

	        if(is_null($expiry_period) || empty($expiry_period))
	          throw new Exception('The expiry period is not configured');

	    $jsonArray['charging_date'] = date('Y-m-d');
	    $jsonArray['date_of_exp'] = date('Y-m-d',strtotime("+".$expiry_period." days"));
	    //Log::info("expiry period:----------".$expiry_period);
	    //Log::info("date of expiry:-------".$jsonArray['date_of_exp']);

            unset($jsonArray['material_code']);
	    unset($jsonArray['material_description']);

        $json = json_encode($jsonArray);


        $request = Request::create('scoapi/SaveBindingAttributes', 'POST', array('module_id'=> Input::get('module_id'),'access_token'=>Input::get('access_token'),'attributes'=>$json,'lid'=>$locationId,'pid'=>1));
			  $originalInput = Request::input();//backup original input
			  Request::replace($request->input());
			  Log::info($request->input());
			  $res1 = Route::dispatch($request)->getContent();
			  $res1 = json_decode($res1);
			  if($res1->Status){
			   $attributeMapId = $res1->AttributeMapId;
			  }
			  else{
				throw new Exception($res1->Message);
			  }

	  	  

		  try{
			foreach($groupIds as $id){
			  
				DB::update('Update '.$esealTable.' SET  attribute_map_id = '.$attributeMapId.' WHERE primary_id = ? ', Array($id));
				DB::insert('INSERT INTO bind_history (eseal_id,location_id,attribute_map_id,created_on) values (?, ?,?,?)', array($id,$locationId,$attributeMapId,$transitionTime));
			
             
            	}

		  }catch(PDOException $e){
			
			Log::error($e->getMessage());
			throw new Exception('Error while binding');  
		  }		

  }

    /******TRACKUPDATE******/

    $trakHistoryObj = new TrackHistory\TrackHistory();

$codesTrack = DB::table($esealTable.' as eseal')
					->join($this->trackHistoryTable.' as th', 'eseal.track_id','=','th.track_id')
					->whereIn('eseal.primary_id', $ids1)
					->select('th.src_loc_id','th.dest_loc_id')
					->get();
				
				$sourceLocations = array();
				foreach($codesTrack as $trackRow){
						
						if($trackRow->dest_loc_id>0)
							throw new Exception('Some of the codes are in-transit');	


					$sourceLocations[] = $trackRow->src_loc_id;							
					
					}

				if(count(array_unique($sourceLocations)) > 1)
					throw new Exception('The stock is from different locations');

				$locationId = $sourceLocations[0];
				
				
				 $lastInrtId = $trakHistoryObj->insertTrack(
					$locationId,0,$transitionId,$transitionTime
					);
				
				//Log::info('track_id'.$lastInrtId);
				
				
				DB::table($esealTable)
					->whereIn('primary_id', $ids1)					
					->update(Array('track_id'=>$lastInrtId));  
				
				
				$sql = '
					INSERT INTO 
						'.$this->trackDetailsTable.' (code, track_id) 
					SELECT 
						primary_id, '.$lastInrtId.' 
					FROM 
						'.$esealTable.' 
					WHERE 
						track_id = '.$lastInrtId;
				DB::insert($sql);


    /***********/


         $status = 1;
		 $message = 'Recharge Succesfull';
		 DB::commit();

	  
	}catch(Exception $e){
	  $status =0;
	  DB::rollback();
	  Log::info($e->getMessage());
	  $message = $e->getMessage();
	}
	
	$endTime = $this->getTime();
	Log::info(__FUNCTION__.' Finishes execution in '.($endTime-$startTime));
    return json_encode(Array('Status'=>$status, 'Message' =>'Server: '.$message));
  }
  

  public function mapMultiple(){
	$startTime = $this->getTime(); 
	try{
		DB::beginTransaction();
		 Log::info(__FUNCTION__.' : '.print_r(Input::get(),true));
		$mapParent = 1;
		$searchInChild = 1;
		$transitionId = trim(Input::get('transitionId'));
		$transitionTime = trim(Input::get('transitionTime'));
		$locId = $this->roleAccess->getLocIdByToken(Input::get('access_token'));
		$mfgId = $this->roleAccess->getMfgIdByToken(Input::get('access_token'));
		$eseals = trim(Input::get('eseals'));
		$pid = trim(Input::get('pid'));
		$attr = array();
		$eseals = json_decode($eseals,true);
		$qcEnabled =  trim(Input::get('qcEnabled'));
		$notUpdateChild = false;


		//$group_id = DB::table('products')->where('product_id',$pid)->pluck('group_id');
		$attributes = DB::table('products as pr')
							  ->join('product_attributesets as pas','pas.product_group_id','=','pr.group_id')
							  ->join('attribute_set_mapping as asm','asm.attribute_set_id','=','pas.attribute_set_id')
							  ->join('attributes as attr','attr.attribute_id','=','asm.attribute_id')
							  ->where(['pr.product_id'=>$pid,'pas.location_id'=>$locId,'attr.attribute_type'=>5])
							  ->get(['attribute_code','default_value']);
			if(empty($attributes)){
				throw new Exception('There are no QC Attributes for the location');
			}
		   
			foreach($attributes as $attribute){

				$attribute_code = $attribute->attribute_code;
				//Log::info($attribute_code);
				$value = $attribute->default_value;
				//Log::info($value);
				$attr[$attribute_code] = $value;
			}			      

			$attr = json_encode($attr);



		if(json_last_error() == JSON_ERROR_NONE){
		foreach($eseals as $eseal){
			
			$parent = $eseal['parent'];
			$ids = $eseal['child'];
			$ids = implode(',',$ids);
			
			if(count($eseal['child']) > 1)
				$notUpdateChild = true;

			$isExists =  DB::table('eseal_'.$mfgId)->where(['primary_id'=>$parent,'pid'=>$pid])->pluck('eseal_id');   

			
		  
		  if($isExists){
		  $request = Request::create('scoapi/BindEsealsWithAttributes', 'POST', array('module_id'=> Input::get('module_id'),'access_token'=>Input::get('access_token'),'pid'=>$pid,'ids'=>$parent,'attributes'=>$attr,'srcLocationId'=>$locId,'transitionTime'=>$transitionTime,'inspection_result'=>1));
		  $originalInput = Request::input();//backup original input
		  Request::replace($request->input());
		  $response = Route::dispatch($request)->getContent();
		  $response = json_decode($response);

		  if(!$response->Status){
			throw new Exception($response->Message);
		  }

		  }



			  $request = Request::create('scoapi/MapWithTrackupdate', 'POST', array('module_id'=> Input::get('module_id'),'access_token'=>Input::get('access_token'),'parent'=>$parent,'ids'=>$ids,'codes'=>$ids,'srcLocationId'=>$locId,'transitionId'=>$transitionId,'transitionTime'=>$transitionTime,'mapParent'=>$mapParent,'searchInChild'=>$searchInChild,'notUpdateChild'=>$notUpdateChild));
			  $originalInput = Request::input();//backup original input
			  Request::replace($request->input());
			  Log::info($request->input());
			  $res = Route::dispatch($request)->getContent();
			  $res = json_decode($res);

			  if($res->Status){
				$status =1;
				$message ='Multiple Mapping successfull';
			  } 
			  else{
				throw new Exception($res->Message);
			  } 
		}
	}
	else{
		throw new Exception('Error in json formatt');
	}
	}
	catch(Exception $e){
		DB::rollback();
		  $status =0;
		  Log::info($e->getMessage());
		  $message = $e->getMessage();
	}
	if($status){
		DB::commit();
	}
	$endTime = $this->getTime();
	Log::info(__FUNCTION__.' Finishes execution in '.($endTime-$startTime));
	return json_encode(Array('Status'=>$status, 'Message' =>'Server: '.$message));  
  }


  /*public function bindMapWare(){
	$startTime = $this->getTime();
	try{
		DB::beginTransaction();
		$data ='';
		$secondary = trim(Input::get('secondary'));
		$childs = trim(Input::get('childs'));
		$locationId = $this->roleAccess->getLocIdByToken(Input::get('access_token'));
		$pid = trim(Input::get('pid'));
		$transitionId = trim(Input::get('transitionId'));
		$transitionTime =trim(Input::get('transitionTime'));
		$attributes = trim(Input::get('attributes'));
		$level = trim(Input::get('level')); 
		Log::info(__FUNCTION__.' : '.print_r(Input::get(),true));

		if(empty($secondary) || empty($pid) || empty($transitionId) || empty($transitionTime) || empty($attributes) || empty($childs)){
			throw new Exception('Some of the parameters are missing');
		}
		if(!is_numeric($level)){
			throw new Exception('Some parameters are not numeric');
		}



	}
	catch(Exception $e){

	}
  }  
*/
  public function deleteEseals(){
  try{
	$status =0;
	$message ='';
	$mfgId = $this->roleAccess->getMfgIdByToken(Input::get('access_token'));
	$ids = trim(Input::get('ids'));
	$idArray = explode(',',$ids);

	if(empty($idArray))
		throw new Exception('Parameters missing');
DB::beginTransaction();

	foreach($idArray as $id){
	
	  DB::table('eseal_'.$mfgId)
				   ->where('primary_id',$id)
				   ->orWhere('parent_id',$id)
				   ->delete();
	}

	DB::table('bind_history')
				   ->whereIn('eseal_id',$idArray)
				   ->delete();                   

	DB::table('track_details')
			 ->whereIn('code',$idArray)
			 ->delete();              

	DB::table('eseal_bank_'.$mfgId)
				   ->whereIn('id',$idArray)
				   ->update(['used_status'=>0,'download_status'=>0,'pid'=>null,'utilizedDate'=>null]);
	$status=1;
	$message = 'Data reset successfully';
	DB::commit();

  }
  catch(Excpetion $e){
	DB::rollback();
	$status =0;
	$message = $e->getMessage();
  }
  return json_encode(['Status'=>$status,'Message' =>'Server: '.$message]);
}


public function test2(){
	try{
		Log::info(__FUNCTION__.' : '.print_r(Input::get(),true));
		$status =1;
		$pdfContent ='';
		
		$mfgId = Input::get('mfg_id');
		$tp = Input::get('tp_id');
		$ids = Input::get('ids');

		$mfg_name = DB::table('eseal_customer')->where('customer_id',$mfgId)->pluck('brand_name');
		$tot = array();

		$ids1 = explode(',',$ids);//DB::table('tp_data')->where('tp_id',$tp)->lists('level_ids');
		Log::info('EXPLODED:');
		Log::info($ids1);
		$batch_no = DB::table('eseal_'.$mfgId)
						->whereIn('primary_id',$ids1)
						->distinct()
						->get(['batch_no']);
		Log::info('batches');
		Log::info($batch_no);

		 foreach($batch_no as $batch){
		 
			$pack = DB::table('eseal_'.$mfgId)->whereIn('parent_id',$ids1)->groupBy('parent_id')->take(1)->get([DB::raw('count(distinct(primary_id)) as cnt')]);
			Log::info('pack');
			Log::info($pack);

			$qty = DB::table('eseal_'.$mfgId)
						   ->where('batch_no',$batch->batch_no)
						   ->where(function($query) use($ids1){
							  $query->whereIn('parent_id', $ids1)
									->orWhereIn('primary_id',$ids1);
							}
							)
						   ->get([DB::raw('count(distinct(primary_id)) as cnt')]);
			Log::info('qty');
			Log::info($qty);


			$set = DB::table('eseal_'.$mfgId.' as es')
							->join('products as pr','pr.product_id','=','es.pid')    	               
							->where('batch_no',$batch->batch_no)
							->whereIn('primary_id',$ids1)
							->groupBy('es.batch_no')
							->select([DB::raw('group_concat(primary_id) as id'),'es.batch_no','pr.name','pr.mrp'])
							->get();
			Log::info('set');
			Log::info($set);

			$set[0]->qty = $qty[0]->cnt;
			  if(!empty($pack)){
			  Log::info('Pack1');
			  $set[0]->pack = $pack[0]->cnt; 
			  }
			  else{
			  Log::info('Pack2');
				$set[0]->pack = 0; 
			  }
			array_push($tot,$set);        
					}

		$th = DB::table('track_history as th')
				   ->join('locations as ls','ls.location_id','=','th.src_loc_id')
				   ->join('locations as ls1','ls1.location_id','=','th.dest_loc_id')
				   ->join('transaction_master as tr','tr.id','=','th.transition_id')
				   ->where('tp_id',$tp)
				   ->get(['ls.location_name as src','ls1.location_name as dest','ls.location_address as src_name','ls1.location_address as dest_name','th.tp_id','tr.name','th.update_time']);
		Log::info('history');
		Log::info($th);
		  if(!empty($th))
		  {
			$view = View::make('pdf', ['manufacturer' =>$mfg_name,'tp'=>$th[0]->tp_id,'status'=>$th[0]->name,'datetime'=>$th[0]->update_time,'src_name'=>$th[0]->src_name,'dest_name'=>$th[0]->dest_name,'src'=>$th[0]->src,'dest'=>$th[0]->dest,'tot'=>$tot]);
			Log::info('view hit');
			$data = (string) $view;
			$pdfContent = base64_encode($data);
			$pdfContent = base64_decode($pdfContent);
			$pdfContent = (string)$pdfContent;

			$message = 'Pdf created successfully';
		}
		 
	}
	catch(Exception $e){
		$status =0;
		$message = $e->getMessage();
	  }
	  return json_encode(['Status'=>$status,'Message'=>$message,'tp_pdf'=>$pdfContent]);
}

  public function bindMapWithEseals1(){
	$startTime = $this->getTime();
	try{
		DB::beginTransaction();
		$data ='';
		$parentQty = trim(Input::get('parentQty'));
		$child_Qty = trim(Input::get('childQty'));
		$locationId = $this->roleAccess->getLocIdByToken(Input::get('access_token'));
		$mfg_id = $this->roleAccess->getMfgIdByToken(Input::get('access_token'));
		$pid = trim(Input::get('pid'));
		$transitionId = trim(Input::get('transitionId'));
		$transitionTime =trim(Input::get('transitionTime'));
		$attributes = trim(Input::get('attributes'));
		$level = trim(Input::get('level')); 
		$po_number = trim(Input::get('po_number')); 
		
		
		Log::info(__FUNCTION__.' : '.print_r(Input::get(),true));

		if(empty($parentQty) || empty($pid) || empty($transitionId) || empty($transitionTime) || empty($attributes)){
			throw new Exception('Some of the parameters are missing');
		}
		if(!is_numeric($parentQty) || !is_numeric($level)){
			throw new Exception('Some parameters are not numeric');
		}
		if(!empty($child_Qty) && !is_numeric($child_Qty)){
			throw new Exception('Some Parameters are not numeric');	
		}
		if(!empty($po_number)){
			$cnt =  DB::table('eseal_'.$mfg_id)
						  ->where(['po_number'=>$po_number,'level_id'=>1])
						  ->count();
			if($cnt){
			   $cartons = DB::table('eseal_'.$mfg_id.' as eseal')
								 ->join('track_history as th','th.track_id','=','eseal.track_id')
								 ->where(['eseal.pid'=>$pid,'eseal.level_id'=>1])
								 ->where(['th.src_loc_id'=>$locationId,'th.dest_loc_id'=>0,'po_number'=>$po_number])
								 ->lists('primary_id');

			   foreach($cartons as $carton){
				   $childs = DB::table('eseal_'.$mfg_id)->where('parent_id',$carton)->lists('primary_id');
				   $data[] = ['parent'=>$carton,'childs'=>$childs];
			   }
			   $status =2;
			   $message = 'Data already exists for the given PO number';
			   return json_encode(['Status'=>$status,'Message'=>'Server: '.$message,'Data'=>$data]);      		                  
			}
			else{
				$cnt =  DB::table('eseal_'.$mfg_id)
						  ->where(['po_number'=>$po_number,'level_id'=>0])
						  ->count();
				if($cnt){
					 $childs = DB::table('eseal_'.$mfg_id.' as eseal')
								 ->join('track_history as th','th.track_id','=','eseal.track_id')
								 ->where(['eseal.pid'=>$pid,'eseal.level_id'=>0])
								 ->where(['th.src_loc_id'=>$locationId,'th.dest_loc_id'=>0,'po_number'=>$po_number])
								 ->lists('primary_id');

			   $data[] = ['childs'=>$childs];
			   $status =2;
			   $message = 'Data already exists for the given PO number';
			   return json_encode(['Status'=>$status,'Message'=>'Server: '.$message,'Data'=>$data]);   
				}          
			} 
			
		}

		$res1 =  DB::table('eseal_'.$mfg_id.' as eseal')
						 ->join('track_history as th','th.track_id','=','eseal.track_id')
						 ->where(['eseal.pid'=>$pid,'eseal.level_id'=>$level])
						 ->where(['th.src_loc_id'=>$locationId,'th.dest_loc_id'=>0])
						 ->count();

		if($level == 1){

		 $cartons =   DB::table('eseal_'.$mfg_id.' as eseal')
							 ->join('track_history as th','th.track_id','=','eseal.track_id')
							 ->where(['eseal.pid'=>$pid,'eseal.level_id'=>1])
							 ->where(['th.src_loc_id'=>$locationId,'th.dest_loc_id'=>0])
							 ->lists('primary_id');

		 if(!$child_Qty){
			//	Log::info('check1');
			if(count($cartons) != $parentQty){
			   goto karteek;
			}
			else{
				foreach($cartons as $carton){
				 $childs = DB::table('eseal_'.$mfg_id)->where('parent_id',$carton)->lists('primary_id');
				
				$data[] = ['parent'=>$carton,'childs'=>$childs]; 
				
			}  
			$status =2;
			$message = 'Data already exists';
			return json_encode(['Status'=>$status,'Message'=>'Server: '.$message,'Data'=>$data]);      		
			}
		   }
		   else{

			$cnt =  DB::table('eseal_'.$mfg_id.' as eseal')
							 ->join('track_history as th','th.track_id','=','eseal.track_id')
							 ->join('eseal_'.$mfg_id.' as e1','e1.parent_id','=','eseal.primary_id')
							 ->where(['eseal.pid'=>$pid,'eseal.level_id'=>1])
							 ->where(['th.src_loc_id'=>$locationId,'th.dest_loc_id'=>0])
							 ->groupBy('e1.parent_id')
							 ->select([DB::raw('count(eseal.primary_id) as cnt')])
							 ->get();

			if(empty($cnt))
				goto karteek;

			foreach($cnt as $c){
				$c1[] = $c->cnt;
			}
			$counts = array_count_values($c1);
			if(array_key_exists($child_Qty, $counts)){
			   if($parentQty == $counts[$child_Qty]){

				foreach($cartons as $carton){

				$cartCount = DB::table('eseal_'.$mfg_id.' as eseal')
								  ->where(['eseal.parent_id'=>$carton])
								  ->count();

				 if($cartCount == $child_Qty){
				$childs = DB::table('eseal_'.$mfg_id)->where('parent_id',$carton)->lists('primary_id');
				$data[] = ['parent'=>$carton,'childs'=>$childs];
				
			}      
					  

				}
				$status =2;
			$message = 'Data already exists';
			Log::info(['Status'=>$status,'Message'=>$message,'Data'=>$data]);
			return json_encode(['Status'=>$status,'Message'=>'Server: '.$message,'Data'=>$data]);  

			   }
			   else{
				 goto karteek;
			   }
			}
			else{
				goto karteek;
			}

		   }


}
		 if($level == 0){
		 
		 $primarys =  DB::table('eseal_'.$mfg_id.' as eseal')
							 ->join('track_history as th','th.track_id','=','eseal.track_id')
							 ->where(['eseal.pid'=>$pid,'eseal.level_id'=>0,'eseal.parent_id'=>0])
							 ->where(['th.src_loc_id'=>$locationId,'th.dest_loc_id'=>0])
							 ->lists('primary_id');   
		  if(count($primarys) != $parentQty){
			   goto karteek;                   
		  }
			
			$data[] = ['childs'=>$primarys];

			$status =2;
			$message = 'Data already exists';
			Log::info(['Status'=>$status,'Message'=>$message,'Data'=>$data]);
			return json_encode(['Status'=>$status,'Message'=>'Server: '.$message,'Data'=>$data]);                   


		 }   
		

		karteek:
		$childQty = DB::table('product_packages')
							 ->join('master_lookup','master_lookup.value','=','product_packages.level')
							 ->where(['master_lookup.name'=>'Level'.$level,'product_id'=>$pid])
							 ->pluck('quantity');
		
		if(!$childQty){
			throw new Exception('Product Package not configured');
		}                     

		if($level == 0){
			$qty = $parentQty;
		}
		else{
			if(!$child_Qty)
			   $qty = $parentQty+($parentQty * $childQty);
			else
			   $qty = $parentQty+($parentQty * $child_Qty);
		}
		   //Log::info('Total Qty:'.$qty);
		   
		  $request = Request::create('scoapi/DownloadEsealByLocationId', 'POST', array('module_id'=> Input::get('module_id'),'access_token'=>Input::get('access_token'),'attributes'=>$attributes,'srcLocationId'=>$locationId,'qty'=>$qty));
		  $originalInput = Request::input();//backup original input
		  Request::replace($request->input());
		  $response = Route::dispatch($request)->getContent();
		  $response = json_decode($response);


		  if(!$response->Status){
			throw new Exception($response->Message);
		  }
		  
		  $codes = $response->Codes;
		  $ids = explode(',',$codes);

		  $attrArr = json_decode($attributes,true);
		  if(json_last_error() != JSON_ERROR_NONE)
			 throw new Exception('Json not valid');

		  if(!array_key_exists('batch_no',$attrArr)){
			 throw new Exception('Batch No not passed in Attribute List');
		  }

		  $batch_no = $attrArr['batch_no'];

		  $request = Request::create('scoapi/BindEsealsWithAttributes', 'POST', array('module_id'=> Input::get('module_id'),'access_token'=>Input::get('access_token'),'pid'=>$pid,'ids'=>$codes,'attributes'=>$attributes,'srcLocationId'=>$locationId,'batch_no'=>$batch_no,'transitionTime'=>$transitionTime));
		  $originalInput = Request::input();//backup original input
		  Request::replace($request->input());
		  $response = Route::dispatch($request)->getContent();
		  $response = json_decode($response);

		  if(!$response->Status){
			throw new Exception($response->Message);
		  }
		  
			if(!empty($po_number)){
			DB::table('eseal_'.$mfg_id)->whereIn('primary_id',$ids)->update(['po_number'=>$po_number]);
		  } 
		  
if($level > 0){
		  if(!$child_Qty)
			  $arrChunk = array_chunk($ids,$childQty+1);
		  else
			  $arrChunk = array_chunk($ids,$child_Qty+1);
		  
		  foreach($arrChunk as $chunk){

			$parent = $chunk[0];
			$childs = array_slice($chunk,1);
			$data[] = ['parent'=>$parent,'childs'=>$childs];
			$childs = implode(',',$childs);
		   
			//$childs1 = explode(',',$childs);
		  
			$request = Request::create('scoapi/MapWithTrackupdate', 'POST', array('module_id'=> Input::get('module_id'),'access_token'=>Input::get('access_token'),'parent'=>$parent,'ids'=>$childs,'codes'=>$childs,'srcLocationId'=>$locationId,'transitionId'=>$transitionId,'transitionTime'=>$transitionTime));
			$originalInput = Request::input();//backup original input
			Request::replace($request->input());
			$response = Route::dispatch($request)->getContent();
			$response = json_decode($response);

			if(!$response->Status){
				throw new Exception($response->Message);
			}
			$status =1;
			$message ='Process successfull';
		} 
		
	}
	else{

			$request = Request::create('scoapi/UpdateTracking', 'POST', array('module_id'=>Input::get('module_id'),'access_token'=>Input::get('access_token'),'codes'=>$codes,'srcLocationId'=>$locationId,'destLocationId'=>0,'transitionTime'=>$transitionTime,'transitionId'=>$transitionId,'internalTransfer'=>0));
			$originalInput = Request::input();//backup original input
			Request::replace($request->input());
			$response = Route::dispatch($request)->getContent();//invoke API
			$response = json_decode($response);

			if(!$response->Status){
			  throw new Exception($response->Message);	
			}
			$data[] = ['childs'=>$ids];
			$status =1;
			$message ='Process successfull';
	}
	DB::commit();
	}
	catch(Exception $e){
		$status =0;
		$data = [];
		DB::rollback();
		$message = $e->getMessage();
	}
	$endTime = $this->getTime();
	Log::info(__FUNCTION__.' Finishes execution in '.($endTime-$startTime));
	Log::info(['Status'=>$status,'Message'=>$message,'Data'=>$data]);
	return json_encode(['Status'=>$status,'Message' =>'Server: '.$message,'Data'=>$data]);
}


public function removeMappedEseals()
{
	$startTime = $this->getTime();
	try
	{
		DB::beginTransaction();
		//$isPallet = trim(Input::get('isPallet'));
		$mfgId = $this->roleAccess->getMfgIdByToken(Input::get('access_token'));
		$locationId = $this->roleAccess->getLocIdByToken(Input::get('access_token'));

		$module_id = trim(Input::get('module_id'));
		$access_token = trim(Input::get('access_token'));
		$child_listJson = Input::get('child_list');
		$child_listArray = json_decode($child_listJson,true);
		//echo "<pre/>";print_r($child_list);exit;
		$new_pallet = trim(Input::get('new_pallet'));
		$stock_transfer = trim(Input::get('stock_transfer'));
		$tp = trim(Input::get('tp'));
		//$srcLocationId = trim(Input::get('srcLocationId'));
		$destLocationId = trim(Input::get('destLocationId'));
		$transitionId = trim(Input::get('transitionId'));
		$transitionTime = trim(Input::get('transitionTime'));
		$tpDataMapping = trim(Input::get('tpDataMapping'));
		$pdfFileName = trim(Input::get('pdfFileName'));
		$pdfContent = trim(Input::get('pdfContent'));
		$sapcode = trim(Input::get('sapcode'));
		$palletArr = array();
		Log::info(__FUNCTION__.' : '.print_r(Input::get(),true));  

		
		$palletsArray=array();
		$productsArray=array();
		$totweight='';
		//echo "<pre/>";print_r($child_listArray);exit;
		if(json_last_error() == JSON_ERROR_NONE)
		{
			foreach($child_listArray as $key=>$value)
			{
				$child_list = explode(',',$value['ids']);
				$weight = $value['weight'];
				$totweight = $totweight+$weight;
				$i=0;
				foreach($child_list as $key1=>$val1)
				{
					$getPallet = DB::Table('eseal_'.$mfgId)->where(['primary_id'=>$val1, 'level_id'=>0])->where('parent_id','!=',0)->pluck('parent_id');
					if(in_array($getPallet,$palletsArray))
					{

					}
					else
					{ 
						$palletsArray[] = $getPallet;
					}
						$pallet_weight = DB::Table('eseal_'.$mfgId)->where(array('primary_id'=>$getPallet, 'level_id'=>8))->pluck('pkg_qty');					
						$new_weight = $pallet_weight - $weight;
						//return $new_weight;
						DB::table('eseal_'.$mfgId)->where('primary_id',$getPallet)->update(['pkg_qty'=>$new_weight]);
						
					//
					$pres_pallet_weight = DB::Table('eseal_'.$mfgId)->where(array('primary_id'=>$getPallet, 'level_id'=>8))->pluck('pkg_qty');
					if($pres_pallet_weight==0)
					{
						DB::table('eseal_'.$mfgId)->where('primary_id',$getPallet)->update(['bin_location'=>'NULL']);
					}
					$productsArray[] = $val1;
				}			
			}
		}
		else
		{
		  Log::error('child list are not in json format');
		  throw new Exception('child list are not in json format');
		}
		$chkloc = DB::table('eseal_'.$mfgId)
					//->where('parent_id',$palletsArray)
					->where('parent_id',$new_pallet)->pluck('bin_location');

		//Unmaping the pallets
		if(empty($chkloc))
		{
			DB::table('eseal_'.$mfgId)
				//->where('parent_id',$palletsArray)
			->whereIn('primary_id',$productsArray)
			->update(['parent_id'=>'unknown','bin_location'=>'NULL']);
		}
		else
		{
			DB::table('eseal_'.$mfgId)
				//->where('parent_id',$palletsArray)
			->whereIn('primary_id',$productsArray)
			->update(['parent_id'=>'unknown','bin_location'=>$chkloc]);
		}

		if(empty($transitionId))
			$transitionId = Transaction::where(['name'=>'Pallet Placement','manufacturer_id'=>$mfgId])->pluck('id');

		if(isset($new_pallet) && $new_pallet!='')
		{
			$query = DB::table('eseal_'.$mfgId)->where('primary_id',$new_pallet);

			if($new_pallet)
				$cnt = $query->where('level_id',8)->count();
			
			if(!$cnt)
			{
				if($new_pallet)
					$message = 'Parent :'.$new_pallet.' is not a pallet';
				else
					$message = 'Parent is not binded';

				throw new Exception($message);
			}
			else
			{
				$pallet_weight = DB::Table('eseal_'.$mfgId)->where(array('primary_id'=>$new_pallet, 'level_id'=>8))->pluck('pkg_qty');
				$pallet_total_capacity = DB::Table('wms_pallet')->where('pallet_id',$new_pallet)->pluck('capacity');
				
				$pallet_present_capacity = $pallet_weight+$totweight;
				//return $pallet_total_capacity.'----'.$pallet_present_capacity;
				if($pallet_total_capacity>=$pallet_present_capacity)
				{
					$ids = implode(',',$productsArray);	
					//echo "<pre/>";print_r($ids);exit;
					$request = Request::create('scoapi/MapEseals', 'POST', array('module_id'=> intval($module_id),'access_token'=>$access_token,'ids'=>$ids,'parent'=>$new_pallet,'srcLocationId'=>$locationId,'mapParent'=>1,'isPallet'=>1,'transitionTime'=>$transitionTime));
					$originalInput = Request::input();//backup original input
					Request::replace($request->input());
					$response = Route::dispatch($request)->getContent();
					$response = json_decode($response); 

					if(!$response->Status)
				   throw new Exception($response->Message);

				  DB::table('eseal_'.$mfgId)->where('primary_id',$new_pallet)->update(['pkg_qty'=>$pallet_present_capacity]);

				   //trackupdating products and old pallets
				   
				   //Log::info('Old Pallets');
				   //Log::info($palletsArray);
				   if($palletsArray[0] != NULL){
				   foreach($palletsArray as $palletId)
				   {
						$chkPallet = DB::Table('eseal_'.$mfgId)->where('parent_id',$palletId)->count();
						if(!$chkPallet)
						{
							$map_id = DB::table('bind_history')->where('eseal_id',$palletId)->orderBy('created_on','asc')->pluck('attribute_map_id');
							DB::table('bind_history')->where(['eseal_id'=>$palletId])->where('attribute_map_id','!=',$map_id)->delete();
							DB::table('eseal_'.$mfgId)->where('primary_id',$palletId)->update(['attribute_map_id'=>$map_id]);

							$transitionId1 = DB::Table('transaction_master')
											 ->where('transaction_master.name','=','Pallet Placement')
											 ->where('transaction_master.manufacturer_id',$mfgId)
											 ->pluck('transaction_master.id');

							$chkqry = DB::Table("track_details")
									  ->join('track_history','track_history.track_id','=','track_details.track_id')
									  ->where('track_history.transition_id','!=',$transitionId1)
									  ->where(['track_details.code'=>$palletId])
									  ->select([DB::raw('group_concat(track_details.track_id) as track_id')])
									  ->get();
							$tracks = explode(',',$chkqry[0]->track_id);          
							//Log::info('Old Tracks:');
							//Log::info($tracks);          
									  //->select('td.track_id as track_id')
									  //->lists('track_id');

							$chkCount = DB::Table('track_details')
										  
										  ->whereIn('track_details.track_id',$tracks)
										  ->groupBy('track_details.code')
										  ->count();

							//Log::info('Is Different pallet exists:');
							//Log::info($chkCount);
							 $delqry = DB::Table('track_details')
										  ->whereIn('track_details.track_id',$tracks)
										  ->where('track_details.code',$palletId)
										  ->delete();

							 if(count($chkCount)==1)
							 {
										DB::Table('track_history')
										  ->whereIn('track_history.track_id',$tracks)										  
										  ->delete();
							 }                   
						}
						else{
							$palletArr[] = $palletId;
						}

						if(!empty($palletArr)){
						  $totalIds = implode(',',array_merge($productsArray,$palletArr));
						}
						else{
							$totalIds = implode(',',$productsArray);
						}
				   }

				}
				else{
					$totalIds = $new_pallet;
				}
				  $notUpdateChild = false;
				//$totalIds = rtrim($totalIds,",");
				  $alreadyProductExists = DB::Table('eseal_'.$mfgId)->where('parent_id',$new_pallet)->count();
				  if($alreadyProductExists)
					   $notUpdateChild = true;

				   $request = Request::create('scoapi/UpdateTracking', 'POST', array('module_id'=>intval($module_id),'access_token'=>$access_token,'codes'=>$totalIds,'parent'=>$new_pallet,'srcLocationId'=>$locationId,'destLocationId'=>0,'transitionTime'=>$transitionTime,'transitionId'=>$transitionId,'internalTransfer'=>0,'notUpdateChild'=>$notUpdateChild));
				   $originalInput = Request::input();//backup original input
				   Request::replace($request->input());
				   Log::info($request->input());
				   $response = Route::dispatch($request)->getContent();
				   $response = json_decode($response);

					if(!$response->Status)
				   throw new Exception($response->Message);	
					$status =1;
					$message = 'Codes Mapped successfully.'; 	   
					
				}
				else
				{
					$status=0;
					$message = 'Products capacity exceeds the pallet capacity.';
				}
			}
		}
		else{ 

			 if($palletsArray[0] != NULL){
				   foreach($palletsArray as $palletId)
				   {
						$chkPallet = DB::Table('eseal_'.$mfgId)->where('parent_id',$palletId)->count();
						if(!$chkPallet)
						{
							$map_id = DB::table('bind_history')->where('eseal_id',$palletId)->orderBy('created_on','asc')->pluck('attribute_map_id');
							DB::table('bind_history')->where(['eseal_id'=>$palletId])->where('attribute_map_id','!=',$map_id)->delete();
							DB::table('eseal_'.$mfgId)->where('primary_id',$palletId)->update(['attribute_map_id'=>$map_id]);

							$transitionId1 = DB::Table('transaction_master')
											 ->where('transaction_master.name','=','Pallet Placement')
											 ->where('transaction_master.manufacturer_id',$mfgId)
											 ->pluck('transaction_master.id');

							$chkqry = DB::Table("track_details")
									  ->join('track_history','track_history.track_id','=','track_details.track_id')
									  ->where('track_history.transition_id','!=',$transitionId1)
									  ->where(['track_details.code'=>$palletId])
									  ->select([DB::raw('group_concat(track_details.track_id) as track_id')])
									  ->get();
							$tracks = explode(',',$chkqry[0]->track_id);          
							//Log::info('Old Tracks:');
							//Log::info($tracks);          
									  //->select('td.track_id as track_id')
									  //->lists('track_id');

							$chkCount = DB::Table('track_details')
										  ->whereIn('track_details.track_id',$tracks)
										  ->groupBy('track_details.code')
										  ->count();
							//Log::info('Is Different pallet exists:');
							//Log::info($chkCount);
							 $delqry = DB::Table('track_details')
										  ->whereIn('track_details.track_id',$tracks)
										  ->where('track_details.code',$palletId)
										  ->delete();

							 if(count($chkCount)==1)
							 {
								$transitionId2 = DB::Table('transaction_master')
											 ->where('transaction_master.name','=','Inward')
											 ->where('transaction_master.manufacturer_id',$mfgId)
											 ->pluck('transaction_master.id');

										DB::Table('track_history')
										  ->whereIn('track_history.track_id',$tracks)
										  ->where('transition_id','!=',$transitionId2)
										  ->delete();
							 }                   
						}
						else{
							$palletArr[] = $palletId;
						}

						if(!empty($palletArr)){
						  $totalIds = implode(',',array_merge($productsArray,$palletArr));
						}
						else{
							$totalIds = implode(',',$productsArray);
						}
				   }

				}
			}		

		
		if($stock_transfer==1)
		{

			$ids = implode(',',$productsArray);	
				$request = Request::create('scoapi/SyncStockOut', 'POST', array('module_id'=> intval($module_id),'access_token'=>$access_token,'ids'=>$ids,'codes'=>$tp,'srcLocationId'=>$locationId,'destLocationId'=>$destLocationId,'transitionTime'=>$transitionTime,'transitionId'=>$transitionId,'tpDataMapping'=>$tpDataMapping,'pdfContent'=>$pdfContent,'pdfFileName'=>$pdfFileName,'sapcode'=>$sapcode));
				$originalInput = Request::input();//backup original input
				Request::replace($request->input());
				$response = Route::dispatch($request)->getContent();
				$response = json_decode($response); 

				if(!$response->Status)
			   throw new Exception($response->Message);

				$status =1;
				$message = 'Stock Transfer successfull. ';

		}		      
		DB::commit();
	}
	catch(Excpetion $e){
		$status =0;
		DB::rollback();
		$message = $e->getMessage();
	}
	$endTime = $this->getTime();
	Log::info(__FUNCTION__.' Finishes execution in '.($endTime-$startTime));
	Log::info(['Status'=>$status,'Message'=>$message]);
	return json_encode(['Status'=>$status,'Message'=>'Server: '.$message]);
}

public function getTransactionData(){
	$startTime = $this->getTime();
	try{
		$locationId = $this->roleAccess->getLocIdByToken(Input::get('access_token'));
		$mfg_id = $this->roleAccess->getMfgIdByToken(Input::get('access_token'));
		$pid = trim(Input::get('pid'));
		$transitionTime =trim(Input::get('transitionTime'));
		$level = trim(Input::get('level')); 
		$po_number = trim(Input::get('po_number')); 
		
		Log::info(__FUNCTION__.' : '.print_r(Input::get(),true));

		if(empty($pid) || empty($transitionTime)){
			throw new Exception('Some of the parameters are missing');
		}
		if(!is_numeric($level)){
			throw new Exception('Some parameters are not numeric');
		}

		$ids =   DB::table('eseal_'.$mfg_id.' as eseal')
							 ->join('track_history as th','th.track_id','=','eseal.track_id')
							 ->where(['eseal.pid'=>$pid,'eseal.level_id'=>$level])
							 ->where(['th.src_loc_id'=>$locationId,'th.dest_loc_id'=>0,'th.update_time'=>$transitionTime])
							 ->lists('primary_id');
		
		if(empty($ids)){
				throw new Exception('Data not found for given input');
			}

		if($level > 0){
			foreach($ids as $carton){
				 $childs = DB::table('eseal_'.$mfg_id)->where('parent_id',$carton)->lists('primary_id');
				 $data[] = ['parent'=>$carton,'childs'=>$childs]; 
			}                   
		}
		else{
		   $data[] =  ['childs'=>$ids];
		}
		$status =1;
		$message = 'Data retrieved successfully';
	}
	catch(Exception $e){
		  $status =0;
		  $data = [];
		  $message = $e->getMessage();
	}

	$endTime = $this->getTime();
	Log::info(__FUNCTION__.' Finishes execution in '.($endTime-$startTime));
	Log::info(['Status'=>$status,'Message'=>$message,'Data'=>$data]);
	return json_encode(['Status'=>$status,'Message' =>'Server: '.$message,'Data'=>$data]);

}


public function bindMapWithEseals(){

	$startTime = $this->getTime();
	try{
		//DB::beginTransaction();
		$debug=Input::get('debug');
		$data ='';
		$parentQty = trim(Input::get('parentQty'));
		$child_Qty = trim(Input::get('childQty'));
		$locationId = $this->roleAccess->getLocIdByToken(Input::get('access_token'));
		$mfg_id = $this->roleAccess->getMfgIdByToken(Input::get('access_token'));
		$pid = trim(Input::get('pid'));
		$transitionId = trim(Input::get('transitionId'));
		$transitionTime =trim(Input::get('transitionTime'));
		$attributes = trim(Input::get('attributes'));
		$level = trim(Input::get('level')); 
		$po_number = trim(Input::get('po_number')); 
		$import_source = trim(Input::get('import_source')); 
		$esealBankTable = 'eseal_bank_'.$mfg_id;
		
		Log::info(__FUNCTION__.' : '.print_r(Input::get(),true));
		
		if(empty($parentQty) || empty($pid) || empty($transitionId) || empty($transitionTime) || empty($attributes)){
			throw new Exception('Some of the parameters are missing');
		}
		if(!is_numeric($parentQty) || !is_numeric($level)){
			throw new Exception('Some parameters are not numeric');
		}
		if(!empty($child_Qty) && !is_numeric($child_Qty)){
			throw new Exception('Some Parameters are not numeric');	
		}

		if(!empty($po_number))
		{
			$cnt =  DB::table('eseal_'.$mfg_id)
					  ->where(['po_number'=>$po_number,'level_id'=>$level])
					  ->count();
			if($cnt)
			{
					$cartons = DB::table('eseal_'.$mfg_id.' as eseal')
					 ->join('track_history as th','th.track_id','=','eseal.track_id')
					 ->where(['eseal.pid'=>$pid,'eseal.level_id'=>$level])
					 ->where(['th.src_loc_id'=>$locationId,'th.dest_loc_id'=>0,'po_number'=>$po_number])
					 ->lists('primary_id');
					 $queries = DB::getQueryLog();
				   foreach($cartons as $carton)
				   {
					   $childs = DB::table('eseal_'.$mfg_id)->where('parent_id',$carton)->lists('primary_id');
					   $data[] = ['parent'=>$carton,'childs'=>$childs];
				   }
				   $status =2;
				   $message = 'Data already exists for the given PO number';
				   return json_encode(['Status'=>$status,'Message'=>'Server: '.$message,'Data'=>$data]); 
			}

		}

		/*if(!empty($po_number))
		{
			//Finding available no of level1's with a given PO.
			$cnt =  DB::table('eseal_'.$mfg_id)
						  ->where(['po_number'=>$po_number,'level_id'=>1])
						  ->count();
			
			if($cnt)
			{
				//Finding available no of Level1's at a particular location with a given PO.	
				$cartons = DB::table('eseal_'.$mfg_id.' as eseal')
					 ->join('track_history as th','th.track_id','=','eseal.track_id')
					 ->where(['eseal.pid'=>$pid,'eseal.level_id'=>1])
					 ->where(['th.src_loc_id'=>$locationId,'th.dest_loc_id'=>0,'po_number'=>$po_number])
					 ->lists('primary_id');

			   foreach($cartons as $carton)
			   {
				   $childs = DB::table('eseal_'.$mfg_id)->where('parent_id',$carton)->lists('primary_id');
				   $data[] = ['parent'=>$carton,'childs'=>$childs];
			   }
			   $status =2;
			   $message = 'Data already exists for the given PO number';
			   return json_encode(['Status'=>$status,'Message'=>'Server: '.$message,'Data'=>$data]);      		                  
			}
			else
			{
				$cnt =  DB::table('eseal_'.$mfg_id)
						  ->where(['po_number'=>$po_number,'level_id'=>0])
						  ->count();
				if($cnt){
					 $childs = DB::table('eseal_'.$mfg_id.' as eseal')
								 ->join('track_history as th','th.track_id','=','eseal.track_id')
								 ->where(['eseal.pid'=>$pid,'eseal.level_id'=>0])
								 ->where(['th.src_loc_id'=>$locationId,'th.dest_loc_id'=>0,'po_number'=>$po_number])
								 ->lists('primary_id');

			   $data[] = ['childs'=>$childs];
			   $status =2;
			   $message = 'Data already exists for the given PO number';
			   return json_encode(['Status'=>$status,'Message'=>'Server: '.$message,'Data'=>$data]);   
				}          
			} 
			
		}
	   
		$res1 =  DB::table('eseal_'.$mfg_id.' as eseal')
						 ->join('track_history as th','th.track_id','=','eseal.track_id')
						 ->where(['eseal.pid'=>$pid,'eseal.level_id'=>$level])
						 ->where(['th.src_loc_id'=>$locationId,'th.dest_loc_id'=>0])
						 ->count();

		if($level == 1){

		 $cartons =   DB::table('eseal_'.$mfg_id.' as eseal')
							 ->join('track_history as th','th.track_id','=','eseal.track_id')
							 ->where(['eseal.pid'=>$pid,'eseal.level_id'=>1])
							 ->where(['th.src_loc_id'=>$locationId,'th.dest_loc_id'=>0])
							 ->lists('primary_id');

		 if(!$child_Qty){
				
			if(count($cartons) != $parentQty){
			   goto jump;
			}
			else{
				foreach($cartons as $carton){
				 $childs = DB::table('eseal_'.$mfg_id)->where('parent_id',$carton)->lists('primary_id');
				
				$data[] = ['parent'=>$carton,'childs'=>$childs]; 
				
			}  
			$status =2;
			$message = 'Data already exists';
			return json_encode(['Status'=>$status,'Message'=>'Server: '.$message,'Data'=>$data]);      		
			}
		   }
		   else{

			$cnt =  DB::table('eseal_'.$mfg_id.' as eseal')
							 ->join('track_history as th','th.track_id','=','eseal.track_id')
							 ->join('eseal_'.$mfg_id.' as e1','e1.parent_id','=','eseal.primary_id')
							 ->where(['eseal.pid'=>$pid,'eseal.level_id'=>1])
							 ->where(['th.src_loc_id'=>$locationId,'th.dest_loc_id'=>0])
							 ->groupBy('e1.parent_id')
							 ->select([DB::raw('count(eseal.primary_id) as cnt')])
							 ->get();

			if(empty($cnt))
				goto jump;

			foreach($cnt as $c){
				$c1[] = $c->cnt;
			}
			$counts = array_count_values($c1);
			if(array_key_exists($child_Qty, $counts)){
			   if($parentQty == $counts[$child_Qty]){

				foreach($cartons as $carton){

				$cartCount = DB::table('eseal_'.$mfg_id.' as eseal')
								  ->where(['eseal.parent_id'=>$carton])
								  ->count();

				 if($cartCount == $child_Qty){
				$childs = DB::table('eseal_'.$mfg_id)->where('parent_id',$carton)->lists('primary_id');
				$data[] = ['parent'=>$carton,'childs'=>$childs];
				
			}      
					  

				}
				$status =2;
			$message = 'Data already exists';
			Log::info(['Status'=>$status,'Message'=>$message,'Data'=>$data]);
			return json_encode(['Status'=>$status,'Message'=>'Server: '.$message,'Data'=>$data]);  

			   }
			   else{
				 goto jump;
			   }
			}
			else{
				 goto jump;
			}

		   }


}
		 if($level == 0){
		 
		 $primarys =  DB::table('eseal_'.$mfg_id.' as eseal')
							 ->join('track_history as th','th.track_id','=','eseal.track_id')
							 ->where(['eseal.pid'=>$pid,'eseal.level_id'=>0,'eseal.parent_id'=>'unknown'])
							 ->where(['th.src_loc_id'=>$locationId,'th.dest_loc_id'=>0])
							 ->lists('primary_id');   
		  if(count($primarys) != $parentQty){
			   goto jump;                   
		  }
			
			$data[] = ['childs'=>$primarys];

			$status =2;
			$message = 'Data already exists';
			Log::info(['Status'=>$status,'Message'=>$message,'Data'=>$data]);
			return json_encode(['Status'=>$status,'Message'=>'Server: '.$message,'Data'=>$data]);                   


		 }   
		

		jump:*/

		if($level>1)
		{
			$attrArr = json_decode($attributes,true);
			if(!array_key_exists('primary', $attrArr))
				throw new Exception('Primary Attribute is not passed.');

			
			$chkCount = DB::Table('eseal_'.$mfg_id)->where(['po_number'=>$po_number,'primary_id'=>$attrArr['primary']])->count();
			if(!$chkCount)
				throw new Exception('Primary Product is not binded against the given PO.');
			
		}

		$childQty = DB::table('product_packages')
							 ->join('master_lookup','master_lookup.value','=','product_packages.level')
							 ->where(['master_lookup.name'=>'Level'.$level,'product_id'=>$pid])
							 ->pluck('quantity');
		
		if(!$childQty){
			throw new Exception('Product Package not configured');
		}                     

		if($level == 0){
			$qty = $parentQty;
		}
		else{
			if(!$child_Qty)
			   $qty = $parentQty+($parentQty * $childQty);
			else
			   $qty = $parentQty+($parentQty * $child_Qty);
		}
		   //Log::info('Total Qty:'.$qty);

		// dd(DB::getQueryLog());
			//Saving the list of Attributes passed and returning the AttributeMapId.
			$request = Request::create('scoapi/SaveBindingAttributes', 'POST', array('module_id'=> Input::get('module_id'),'access_token'=>Input::get('access_token'),'attributes'=>$attributes,'lid'=>$locationId,'pid'=>$pid));
							  $originalInput = Request::input();//backup original input
							  Request::replace($request->input());
							  Log::info($request->input());
							  $response = Route::dispatch($request)->getContent();//invoke API
							  $response = json_decode($response);
							  if($response->Status){
								
								$map_id = $response->AttributeMapId;
				}
				else{
					throw new Exception($response->Message);
				}

			  $attrArr = json_decode($attributes,true);
		  if(json_last_error() != JSON_ERROR_NONE)
			 throw new Exception('Json not valid');

			if($level<2)
			{
				if(!array_key_exists('batch_no',$attrArr)){
					throw new Exception('Batch No not passed in Attribute List');
				}

				//$batch_no = $attrArr['batch_no'];	
			}
			$batch_no = $attrArr['batch_no'];
			//inserting a new record into TrackHistory table.
			$trakHistoryObj = new TrackHistory\TrackHistory();
			$track = $trakHistoryObj->insertTrack(
					$locationId,0, $transitionId, $transitionTime
					);
		
		  //Checking for required quantity of Ids' in esealBankTable
			
				if($level == 0)
					$childLevel = 0;
				else
					$childLevel = $level-1;

				$download_token = DB::table('download_flag')->insertGetId(array('update_time'=>date('Y-m-d H:i:s')));
				
				$result = DB::table($esealBankTable)->where(array('download_token'=>0))->orderBy('serial_id','asc')->take($qty)->get(['id as primary_id',DB::raw($pid.' as pid'),DB::raw($map_id.' as attribute_map_id'),DB::raw($childLevel.' as level_id'),DB::raw($track.' as track_id'),DB::raw('"'.$batch_no.'" as batch_no'),DB::raw('"'.$transitionTime.'" as mfg_date')]);
			//	Log::info($result);
			//dd(DB::getQueryLog());
		  
		 
		if(count($result) && count($result)==$qty){
		  foreach($result as $res){
			$str[] = $res->primary_id;
			 if(!empty($po_number)){
			 	$res->po_number=$po_number;
		  	}
		  }		 
		}
 
		 $codes = implode(',',$str);
		 $ids = explode(',',$codes);

		  //Updating Ids in esealBankTable with usedStatus.
		 // download status update to next update 
		 DB::table($esealBankTable)->whereIn('id',$str)->update(['download_token'=>$download_token]);
		 
		  $result = json_encode($result);
		  $result = json_decode($result,true);

		  
		  foreach($str as $id){
			$history[] = ['eseal_id'=>$id,'location_id'=>$locationId,'attribute_map_id'=>$map_id,'created_on'=>$transitionTime];
		  }
		  DB::beginTransaction();
		  //Bulk Insert into eseal_mfgid table.
		  DB::table('eseal_'.$mfg_id)->insert($result);
		  DB::table('bind_history')->insert($history);

		  //DB::insert('INSERT INTO bind_history (eseal_id,location_id,attribute_map_id,created_on) values (?, ?,?,?)', array($res[0]->primary_id,$locationId ,$attributeMapId,$transitionTime));
		  
		  DB::table($esealBankTable)->whereIn('download_token',[$download_token])->update(['used_status'=>1,'download_status'=>1,'location_id'=> $locationId,'pid'=>$pid,'utilizedDate'=>$transitionTime]);
		  DB::commit();

		  
		/*
		moved to inserted placed in insert query 
			if(!empty($po_number)){
			//Updating the download ids with a PO. 
			DB::table('eseal_'.$mfg_id)->whereIn('primary_id',$ids)->update(['po_number'=>$po_number]);
		  } */
		
if($level > 0){
		   //Partitioning the array of ids into slices and chunks based on the parent level capacity.
		  if(!$child_Qty)
			  $arrChunk = array_chunk($ids,$childQty+1);
		  else
			  $arrChunk = array_chunk($ids,$child_Qty+1);
		  
		  foreach($arrChunk as $chunk){

		 
			$parent = $chunk[0];
			$childs = array_slice($chunk,1);
			$data[] = ['parent'=>$parent,'childs'=>$childs];
			$childs = implode(',',$childs);
			DB::table('eseal_'.$mfg_id)->where('primary_id',$parent)->update(['level_id'=>1]);
			//Mapping Parent to Child.
			$request = Request::create('scoapi/MapEseals', 'POST', array('module_id'=> Input::get('module_id'),'access_token'=>Input::get('access_token'),'parent'=>$parent,'ids'=>$childs,'srcLocationId'=>$locationId,'import_source'=>1,
				'transitionTime'=>$transitionTime,'debug'=>'shanu'));
			$originalInput = Request::input();//backup original input
			Request::replace($request->input());
			$response = Route::dispatch($request)->getContent();
			$response = json_decode($response);

			if(!$response->Status){
				throw new Exception($response->Message);
			}
			
		} 
		
	}
	//else{

			/*$request = Request::create('scoapi/UpdateTracking', 'POST', array('module_id'=>Input::get('module_id'),'access_token'=>Input::get('access_token'),'codes'=>$codes,'srcLocationId'=>$locationId,'destLocationId'=>0,'transitionTime'=>$transitionTime,'transitionId'=>$transitionId,'internalTransfer'=>0));
			$originalInput = Request::input();//backup original input
			Request::replace($request->input());
			$response = Route::dispatch($request)->getContent();//invoke API
			$response = json_decode($response);

			if(!$response->Status){
			  throw new Exception($response->Message);	
			}*/
			//$trArr = array();
		   $trArr =  DB::table('eseal_'.$mfg_id)
							   ->where('track_id',$track)
							   ->get(['primary_id as code','track_id']);
		   
		   $trArr = json_encode($trArr);
		   $trArr1 = json_decode($trArr,true);
		   //Bulk insert into track_details table for all the downloaded ids.
		   Track::insert($trArr1);                   


			if($level == 0){
			$data[] = ['childs'=>$ids];
		}
			$status =1;
			$message ='Process successfull';
	//}
	//DB::commit();
	}
	catch(Exception $e){
		$status =0;
		$data = [];
		//DB::rollback();
		$message = $e->getMessage();
	}
	$endTime = $this->getTime();
	Log::info(__FUNCTION__.' Finishes execution in '.($endTime-$startTime));
	Log::info(['Status'=>$status,'Message'=>$message,'Data'=>$data]);
	return json_encode(['Status'=>$status,'Message' =>'Server: '.$message,'Data'=>$data]);

}
public function bindMapWithEseals_old2(){
	$startTime = $this->getTime();
	try{
		DB::beginTransaction();
		$data ='';
		$parentQty = trim(Input::get('parentQty'));
		$child_Qty = trim(Input::get('childQty'));
		$locationId = $this->roleAccess->getLocIdByToken(Input::get('access_token'));
		$mfg_id = $this->roleAccess->getMfgIdByToken(Input::get('access_token'));
		$pid = trim(Input::get('pid'));
		$transitionId = trim(Input::get('transitionId'));
		$transitionTime =trim(Input::get('transitionTime'));
		$attributes = trim(Input::get('attributes'));
		$level = trim(Input::get('level')); 
		$po_number = trim(Input::get('po_number')); 
		$esealBankTable = 'eseal_bank_'.$mfg_id;
		
		Log::info(__FUNCTION__.' : '.print_r(Input::get(),true));
		
		if(empty($parentQty) || empty($pid) || empty($transitionId) || empty($transitionTime) || empty($attributes)){
			throw new Exception('Some of the parameters are missing');
		}
		if(!is_numeric($parentQty) || !is_numeric($level)){
			throw new Exception('Some parameters are not numeric');
		}
		if(!empty($child_Qty) && !is_numeric($child_Qty)){
			throw new Exception('Some Parameters are not numeric');	
		}

		if(!empty($po_number))
		{
			$cnt =  DB::table('eseal_'.$mfg_id)
					  ->where(['po_number'=>$po_number,'level_id'=>$level])
					  ->count();
			if($cnt)
			{
					$cartons = DB::table('eseal_'.$mfg_id.' as eseal')
					 ->join('track_history as th','th.track_id','=','eseal.track_id')
					 ->where(['eseal.pid'=>$pid,'eseal.level_id'=>$level])
					 ->where(['th.src_loc_id'=>$locationId,'th.dest_loc_id'=>0,'po_number'=>$po_number])
					 ->lists('primary_id');
					 $queries = DB::getQueryLog();
				   foreach($cartons as $carton)
				   {
					   $childs = DB::table('eseal_'.$mfg_id)->where('parent_id',$carton)->lists('primary_id');
					   $data[] = ['parent'=>$carton,'childs'=>$childs];
				   }
				   $status =2;
				   $message = 'Data already exists for the given PO number';
				   return json_encode(['Status'=>$status,'Message'=>'Server: '.$message,'Data'=>$data]); 
			}

		}

		/*if(!empty($po_number))
		{
			//Finding available no of level1's with a given PO.
			$cnt =  DB::table('eseal_'.$mfg_id)
						  ->where(['po_number'=>$po_number,'level_id'=>1])
						  ->count();
			
			if($cnt)
			{
				//Finding available no of Level1's at a particular location with a given PO.	
				$cartons = DB::table('eseal_'.$mfg_id.' as eseal')
					 ->join('track_history as th','th.track_id','=','eseal.track_id')
					 ->where(['eseal.pid'=>$pid,'eseal.level_id'=>1])
					 ->where(['th.src_loc_id'=>$locationId,'th.dest_loc_id'=>0,'po_number'=>$po_number])
					 ->lists('primary_id');

			   foreach($cartons as $carton)
			   {
				   $childs = DB::table('eseal_'.$mfg_id)->where('parent_id',$carton)->lists('primary_id');
				   $data[] = ['parent'=>$carton,'childs'=>$childs];
			   }
			   $status =2;
			   $message = 'Data already exists for the given PO number';
			   return json_encode(['Status'=>$status,'Message'=>'Server: '.$message,'Data'=>$data]);      		                  
			}
			else
			{
				$cnt =  DB::table('eseal_'.$mfg_id)
						  ->where(['po_number'=>$po_number,'level_id'=>0])
						  ->count();
				if($cnt){
					 $childs = DB::table('eseal_'.$mfg_id.' as eseal')
								 ->join('track_history as th','th.track_id','=','eseal.track_id')
								 ->where(['eseal.pid'=>$pid,'eseal.level_id'=>0])
								 ->where(['th.src_loc_id'=>$locationId,'th.dest_loc_id'=>0,'po_number'=>$po_number])
								 ->lists('primary_id');

			   $data[] = ['childs'=>$childs];
			   $status =2;
			   $message = 'Data already exists for the given PO number';
			   return json_encode(['Status'=>$status,'Message'=>'Server: '.$message,'Data'=>$data]);   
				}          
			} 
			
		}
	   
		$res1 =  DB::table('eseal_'.$mfg_id.' as eseal')
						 ->join('track_history as th','th.track_id','=','eseal.track_id')
						 ->where(['eseal.pid'=>$pid,'eseal.level_id'=>$level])
						 ->where(['th.src_loc_id'=>$locationId,'th.dest_loc_id'=>0])
						 ->count();

		if($level == 1){

		 $cartons =   DB::table('eseal_'.$mfg_id.' as eseal')
							 ->join('track_history as th','th.track_id','=','eseal.track_id')
							 ->where(['eseal.pid'=>$pid,'eseal.level_id'=>1])
							 ->where(['th.src_loc_id'=>$locationId,'th.dest_loc_id'=>0])
							 ->lists('primary_id');

		 if(!$child_Qty){
				
			if(count($cartons) != $parentQty){
			   goto jump;
			}
			else{
				foreach($cartons as $carton){
				 $childs = DB::table('eseal_'.$mfg_id)->where('parent_id',$carton)->lists('primary_id');
				
				$data[] = ['parent'=>$carton,'childs'=>$childs]; 
				
			}  
			$status =2;
			$message = 'Data already exists';
			return json_encode(['Status'=>$status,'Message'=>'Server: '.$message,'Data'=>$data]);      		
			}
		   }
		   else{

			$cnt =  DB::table('eseal_'.$mfg_id.' as eseal')
							 ->join('track_history as th','th.track_id','=','eseal.track_id')
							 ->join('eseal_'.$mfg_id.' as e1','e1.parent_id','=','eseal.primary_id')
							 ->where(['eseal.pid'=>$pid,'eseal.level_id'=>1])
							 ->where(['th.src_loc_id'=>$locationId,'th.dest_loc_id'=>0])
							 ->groupBy('e1.parent_id')
							 ->select([DB::raw('count(eseal.primary_id) as cnt')])
							 ->get();

			if(empty($cnt))
				goto jump;

			foreach($cnt as $c){
				$c1[] = $c->cnt;
			}
			$counts = array_count_values($c1);
			if(array_key_exists($child_Qty, $counts)){
			   if($parentQty == $counts[$child_Qty]){

				foreach($cartons as $carton){

				$cartCount = DB::table('eseal_'.$mfg_id.' as eseal')
								  ->where(['eseal.parent_id'=>$carton])
								  ->count();

				 if($cartCount == $child_Qty){
				$childs = DB::table('eseal_'.$mfg_id)->where('parent_id',$carton)->lists('primary_id');
				$data[] = ['parent'=>$carton,'childs'=>$childs];
				
			}      
					  

				}
				$status =2;
			$message = 'Data already exists';
			Log::info(['Status'=>$status,'Message'=>$message,'Data'=>$data]);
			return json_encode(['Status'=>$status,'Message'=>'Server: '.$message,'Data'=>$data]);  

			   }
			   else{
				 goto jump;
			   }
			}
			else{
				 goto jump;
			}

		   }


}
		 if($level == 0){
		 
		 $primarys =  DB::table('eseal_'.$mfg_id.' as eseal')
							 ->join('track_history as th','th.track_id','=','eseal.track_id')
							 ->where(['eseal.pid'=>$pid,'eseal.level_id'=>0,'eseal.parent_id'=>'unknown'])
							 ->where(['th.src_loc_id'=>$locationId,'th.dest_loc_id'=>0])
							 ->lists('primary_id');   
		  if(count($primarys) != $parentQty){
			   goto jump;                   
		  }
			
			$data[] = ['childs'=>$primarys];

			$status =2;
			$message = 'Data already exists';
			Log::info(['Status'=>$status,'Message'=>$message,'Data'=>$data]);
			return json_encode(['Status'=>$status,'Message'=>'Server: '.$message,'Data'=>$data]);                   


		 }   
		

		jump:*/

		if($level>1)
		{
			$attrArr = json_decode($attributes,true);
			if(!array_key_exists('primary', $attrArr))
				throw new Exception('Primary Attribute is not passed.');

			
			$chkCount = DB::Table('eseal_'.$mfg_id)->where(['po_number'=>$po_number,'primary_id'=>$attrArr['primary']])->count();
			if(!$chkCount)
				throw new Exception('Primary Product is not binded against the given PO.');
			
		}

		$childQty = DB::table('product_packages')
							 ->join('master_lookup','master_lookup.value','=','product_packages.level')
							 ->where(['master_lookup.name'=>'Level'.$level,'product_id'=>$pid])
							 ->pluck('quantity');
		
		if(!$childQty){
			throw new Exception('Product Package not configured');
		}                     

		if($level == 0){
			$qty = $parentQty;
		}
		else{
			if(!$child_Qty)
			   $qty = $parentQty+($parentQty * $childQty);
			else
			   $qty = $parentQty+($parentQty * $child_Qty);
		}
		  // Log::info('Total Qty:'.$qty);

		 
			//Saving the list of Attributes passed and returning the AttributeMapId.
			$request = Request::create('scoapi/SaveBindingAttributes', 'POST', array('module_id'=> Input::get('module_id'),'access_token'=>Input::get('access_token'),'attributes'=>$attributes,'lid'=>$locationId,'pid'=>$pid));
							  $originalInput = Request::input();//backup original input
							  Request::replace($request->input());
							  Log::info($request->input());
							  $response = Route::dispatch($request)->getContent();//invoke API
							  $response = json_decode($response);
							  if($response->Status){
								
								$map_id = $response->AttributeMapId;
				}
				else{
					throw new Exception($response->Message);
				}

			  $attrArr = json_decode($attributes,true);
		  if(json_last_error() != JSON_ERROR_NONE)
			 throw new Exception('Json not valid');

			if($level<2)
			{
				if(!array_key_exists('batch_no',$attrArr)){
					throw new Exception('Batch No not passed in Attribute List');
				}

				//$batch_no = $attrArr['batch_no'];	
			}
			$batch_no = $attrArr['batch_no'];
			//inserting a new record into TrackHistory table.
			$trakHistoryObj = new TrackHistory\TrackHistory();
			$track = $trakHistoryObj->insertTrack(
					$locationId,0, $transitionId, $transitionTime
					);
		

		  //Checking for required quantity of Ids' in esealBankTable
			
				if($level == 0)
					$childLevel = 0;
				else
					$childLevel = $level-1;
				
				$result = DB::table($esealBankTable)->where(array('used_status'=>'0','issue_status'=>'0', 'download_status'=>'0'))->orderBy('serial_id','asc')->take($qty)->get(['id as primary_id',DB::raw($pid.' as pid'),DB::raw($map_id.' as attribute_map_id'),DB::raw($childLevel.' as level_id'),DB::raw($track.' as track_id'),DB::raw('"'.$batch_no.'" as batch_no'),DB::raw('"'.$transitionTime.'" as mfg_date')]);
				//Log::info($result);
			
		  
		 
		if(count($result) && count($result)==$qty){
		  foreach($result as $res){
			$str[] = $res->primary_id;
		  }		 
		}
 
		 $codes = implode(',',$str);
		 $ids = explode(',',$codes);

		  //Updating Ids in esealBankTable with usedStatus.
		  DB::table($esealBankTable)->whereIn('id',$str)->update(['download_status'=>1,'location_id'=>$locationId]);
		 
		  $result = json_encode($result);
		  $result = json_decode($result,true);

		  
		  foreach($str as $id){
			$history[] = ['eseal_id'=>$id,'location_id'=>$locationId,'attribute_map_id'=>$map_id,'created_on'=>$transitionTime];
		  }
		  //Bulk Insert into eseal_mfgid table.
		  DB::table('eseal_'.$mfg_id)->insert($result);
		  DB::table('bind_history')->insert($history);

		  //DB::insert('INSERT INTO bind_history (eseal_id,location_id,attribute_map_id,created_on) values (?, ?,?,?)', array($res[0]->primary_id,$locationId ,$attributeMapId,$transitionTime));
		  
		  DB::table($esealBankTable)->whereIn('id',$ids)->update(['used_status'=>1, 'location_id'=> $locationId,'pid'=>$pid,'utilizedDate'=>$transitionTime]);

		 if(!empty($po_number)){
			//Updating the download ids with a PO. 
			DB::table('eseal_'.$mfg_id)->whereIn('primary_id',$ids)->update(['po_number'=>$po_number]);
		  } 
		  
if($level > 0){
		   //Partitioning the array of ids into slices and chunks based on the parent level capacity.
		  if(!$child_Qty)
			  $arrChunk = array_chunk($ids,$childQty+1);
		  else
			  $arrChunk = array_chunk($ids,$child_Qty+1);
		  
		  foreach($arrChunk as $chunk){

			$parent = $chunk[0];
			$childs = array_slice($chunk,1);
			$data[] = ['parent'=>$parent,'childs'=>$childs];
			$childs = implode(',',$childs);
		   

			//Mapping Parent to Child.
			$request = Request::create('scoapi/MapEseals', 'POST', array('module_id'=> Input::get('module_id'),'access_token'=>Input::get('access_token'),'parent'=>$parent,'ids'=>$childs,'srcLocationId'=>$locationId,'transitionTime'=>$transitionTime));
			$originalInput = Request::input();//backup original input
			Request::replace($request->input());
			$response = Route::dispatch($request)->getContent();
			$response = json_decode($response);

			if(!$response->Status){
				throw new Exception($response->Message);
			}
			
		} 
		
	}
	//else{

			/*$request = Request::create('scoapi/UpdateTracking', 'POST', array('module_id'=>Input::get('module_id'),'access_token'=>Input::get('access_token'),'codes'=>$codes,'srcLocationId'=>$locationId,'destLocationId'=>0,'transitionTime'=>$transitionTime,'transitionId'=>$transitionId,'internalTransfer'=>0));
			$originalInput = Request::input();//backup original input
			Request::replace($request->input());
			$response = Route::dispatch($request)->getContent();//invoke API
			$response = json_decode($response);

			if(!$response->Status){
			  throw new Exception($response->Message);	
			}*/
			//$trArr = array();
		   $trArr =  DB::table('eseal_'.$mfg_id)
							   ->where('track_id',$track)
							   ->get(['primary_id as code','track_id']);
		   
		   $trArr = json_encode($trArr);
		   $trArr1 = json_decode($trArr,true);
		   //Bulk insert into track_details table for all the downloaded ids.
		   Track::insert($trArr1);                   


			if($level == 0){
			$data[] = ['childs'=>$ids];
		}
			$status =1;
			$message ='Process successfull';
	//}
	DB::commit();
	}
	catch(Exception $e){
		$status =0;
		$data = [];
		DB::rollback();
		$message = $e->getMessage();
	}
	$endTime = $this->getTime();
	Log::info(__FUNCTION__.' Finishes execution in '.($endTime-$startTime));
	Log::info(['Status'=>$status,'Message'=>$message,'Data'=>$data]);
	return json_encode(['Status'=>$status,'Message' =>'Server: '.$message,'Data'=>$data]);
}


public function lateBinding(){
	$startTime = $this->getTime();
	try{
		DB::beginTransaction();
		$data ='';
		$parentIds = trim(Input::get('parentIds'));
		$child_Qty = trim(Input::get('childQty'));
		$locationId = $this->roleAccess->getLocIdByToken(Input::get('access_token'));
		$mfg_id = $this->roleAccess->getMfgIdByToken(Input::get('access_token'));
		$pid = trim(Input::get('pid'));
		$transitionId = trim(Input::get('transitionId'));
		$transitionTime =trim(Input::get('transitionTime'));
		$attributes = trim(Input::get('attributes'));
		$esealBankTable = 'eseal_bank_'.$mfg_id;
		
		Log::info(__FUNCTION__.' : '.print_r(Input::get(),true));
		
		if(empty($parentIds) || empty($pid) || empty($transitionId) || empty($transitionTime) || empty($attributes)){
			throw new Exception('Some of the parameters are missing');
		}
		
		if(!empty($child_Qty) && !is_numeric($child_Qty)){
			throw new Exception('Some Parameters are not numeric');	
		}
				

		$childQty = DB::table('product_packages')
							 ->join('master_lookup','master_lookup.value','=','product_packages.level')
							 ->where(['master_lookup.name'=>'Level0','product_id'=>$pid])
							 ->pluck('quantity');
		
		if(!$childQty){
			throw new Exception('Product Package not configured');
		}                     

		$parentArr = explode(',',$parentIds);
		$parentQty = count($parentArr);
		

		$cnt = DB::table($esealBankTable)->whereIn(['id',$parentArr])->where(['issue_status'=>1,'download_status'=>0,'used_status'=>0,'location_id'=>0])->count();
		
		//Log::info('Count : '.$parentQty.'----'.$cnt);

		if($cnt != $parentQty)
			throw new Exception('Codes count not matching with code bank');



			if(!$child_Qty)
			   $qty = $parentQty * $childQty;
			else
			   $qty = $parentQty * $child_Qty;
		
		   //Log::info('Total Child Qty:'.$qty);

		   
		   $attrArr = json_decode($attributes,true);
		  if(json_last_error() != JSON_ERROR_NONE)
			 throw new Exception('Json not valid');

			
				if(!array_key_exists('batch_no',$attrArr)){
					throw new Exception('Batch No not passed in Attribute List');
				}

				//$batch_no = $attrArr['batch_no'];	
			


			//Saving the list of Attributes passed and returning the AttributeMapId.
			$request = Request::create('scoapi/SaveBindingAttributes', 'POST', array('module_id'=> Input::get('module_id'),'access_token'=>Input::get('access_token'),'attributes'=>$attributes,'lid'=>$locationId,'pid'=>$pid));
							  $originalInput = Request::input();//backup original input
							  Request::replace($request->input());
							  Log::info($request->input());
							  $response = Route::dispatch($request)->getContent();//invoke API
							  $response = json_decode($response);
							  if($response->Status){
								
								$map_id = $response->AttributeMapId;
				}
				else{
					throw new Exception($response->Message);
				}

			
			$batch_no = $attrArr['batch_no'];
			//inserting a new record into TrackHistory table.
			$trakHistoryObj = new TrackHistory\TrackHistory();
			$track = $trakHistoryObj->insertTrack(
					$locationId,0, $transitionId, $transitionTime
					);
		

		  //Checking for required quantity of Ids' in esealBankTable
				$result = DB::table($esealBankTable)->where(array('used_status'=>'0','issue_status'=>'0', 'download_status'=>'0'))->orderBy('serial_id','asc')->take($qty)->get(['id as primary_id',DB::raw($pid.' as pid'),DB::raw($map_id.' as attribute_map_id'),DB::raw('0 as level_id'),DB::raw($track.' as track_id'),DB::raw('"'.$batch_no.'" as batch_no'),DB::raw('"'.$transitionTime.'" as mfg_date')]);
				//Log::info($result);
			
		  
		 
		if(count($result) && count($result)==$qty){
		  foreach($result as $res){
			$str[] = $res->primary_id;
		  }		 
		}

		 DB::beginTransaction();
 
		 $codes = implode(',',$str);
		 $ids = explode(',',$codes);

		  //Updating Ids in esealBankTable with usedStatus.
		  DB::table($esealBankTable)->whereIn('id',$str)->update(['download_status'=>1,'location_id'=>$locationId]);
		 
		  $result = json_encode($result);
		  $result = json_decode($result,true);

		 foreach ($parentArr as $parent) {
			array_push($str,$parent); 
		  } 

		  foreach($str as $id){
			$history[] = ['eseal_id'=>$id,'location_id'=>$locationId,'attribute_map_id'=>$map_id,'created_on'=>$transitionTime];
		  }
		  //Bulk Insert into eseal_mfgid table.
		  DB::table('eseal_'.$mfg_id)->insert($result);
		  DB::table('bind_history')->insert($history);

		  //DB::insert('INSERT INTO bind_history (eseal_id,location_id,attribute_map_id,created_on) values (?, ?,?,?)', array($res[0]->primary_id,$locationId ,$attributeMapId,$transitionTime));
		  
		  DB::table($esealBankTable)->whereIn('id',$ids)->update(['used_status'=>1, 'location_id'=> $locationId,'pid'=>$pid,'utilizedDate'=>$transitionTime]);

		
		  
		   //Partitioning the array of ids into slices and chunks based on the parent level capacity.
		  if(!$child_Qty)
			  $arrChunk = array_chunk($ids,$childQty);
		  else
			  $arrChunk = array_chunk($ids,$child_Qty);
		  
		  if($parentQty != count($arrChunk))
			throw new Exception('Mapping Error');

		  for($i=0;$i< $parentQty;$i++){

			$parent = $parentArr[$i];
			$childs = $arrChunk[$i];
			$data[] = ['parent'=>$parent,'childs'=>$childs];
			$childs = implode(',',$childs);
		   

			//Mapping Parent to Child.
			$request = Request::create('scoapi/MapEseals', 'POST', array('module_id'=> Input::get('module_id'),'access_token'=>Input::get('access_token'),'parent'=>$parent,'ids'=>$childs,'srcLocationId'=>$locationId,'transitionTime'=>$transitionTime));
			$originalInput = Request::input();//backup original input
			Request::replace($request->input());
			$response = Route::dispatch($request)->getContent();
			$response = json_decode($response);

			if(!$response->Status){
				throw new Exception($response->Message);
			}
			
		} 
		
	
		   $trArr =  DB::table('eseal_'.$mfg_id)
							   ->where('track_id',$track)
							   ->get(['primary_id as code','track_id']);
		   
		   $trArr = json_encode($trArr);
		   $trArr1 = json_decode($trArr,true);
		   //Bulk insert into track_details table for all the downloaded ids.
		   Track::insert($trArr1);                   


			$status =1;
			$message ='Process successfull';
	//}
	DB::commit();
	}
	catch(Exception $e){
		$status =0;
		$data = [];
		DB::rollback();
		$message = $e->getMessage();
	}
	$endTime = $this->getTime();
	Log::info(__FUNCTION__.' Finishes execution in '.($endTime-$startTime));
	Log::info(['Status'=>$status,'Message'=>$message,'Data'=>$data]);
	return json_encode(['Status'=>$status,'Message' =>'Server: '.$message,'Data'=>$data]);
}




public function bindAndMap(){
	$startTime = $this->getTime();
	try{
		Log::info(__FUNCTION__.' : '.print_r(Input::get(),true));
		DB::beginTransaction();
		$data ='';
		$mapParent ='';
		$mfgId = $this->roleAccess->getMfgIdByToken(Input::get('access_token'));
		$isPallet = trim(Input::get('isPallet')); 
		$parent = trim(Input::get('parent'));
		$ids = trim(Input::get('child_list'));
		$locationId = $this->roleAccess->getLocIdByToken(Input::get('access_token'));
		$pid = trim(Input::get('pid'));
		$attributes = trim(Input::get('attributes'));
		$weight =  trim(Input::get('pallet_weight'));
		$transitionTime = trim(Input::get('transitionTime'));
		//$transitionId = DB::table('transaction_master')->where(['manufacturer_id'=>$mfgId,'name'=>'Pallet Placement'])->pluck('id');
		$transitionId = trim(Input::get('transitionId'));;
		if($isPallet){
			$mapParent =1;

			$cnt = DB::table('eseal_'.$mfgId)->where(['primary_id'=>$parent,'level_id'=>8])->count();
			if(!$cnt){
				throw new Exception('Parent:'.$parent.' is not a pallet');
			}
		}

		
		if(empty($parent) || empty($pid) ||  empty($attributes) || empty($ids) || empty($transitionTime) || empty($weight))
				throw new Exception('Some of the parameters are missing');

		  $request = Request::create('scoapi/BindEsealsWithAttributes', 'POST', array('module_id'=> Input::get('module_id'),'access_token'=>Input::get('access_token'),'pid'=>$pid,'ids'=>$ids,'attributes'=>$attributes,'srcLocationId'=>$locationId,'transitionTime'=>$transitionTime,'isPallet'=>0));
		  $originalInput = Request::input();//backup original input
		  Request::replace($request->input());
		  $response = Route::dispatch($request)->getContent();
		  $response = json_decode($response);

		  if(!$response->Status)
			throw new Exception($response->Message);
		  
		  $request = Request::create('scoapi/MapEseals', 'POST', array('module_id'=> Input::get('module_id'),'access_token'=>Input::get('access_token'),'ids'=>$ids,'parent'=>$parent,'srcLocationId'=>$locationId,'mapParent'=>$mapParent,'isPallet'=>$isPallet,'transitionTime'=>$transitionTime));
		  $originalInput = Request::input();//backup original input
		  Request::replace($request->input());
		  $response = Route::dispatch($request)->getContent();
		  $response = json_decode($response); 
		  
		  if(!$response->Status)
			throw new Exception($response->Message);

			$request = Request::create('scoapi/UpdateTracking', 'POST', array('module_id'=>Input::get('module_id'),'access_token'=>Input::get('access_token'),'codes'=>$ids,'parent'=>$parent,'srcLocationId'=>$locationId,'destLocationId'=>0,'transitionTime'=>$transitionTime,'transitionId'=>$transitionId,'internalTransfer'=>0));
			$originalInput = Request::input();//backup original input
			Request::replace($request->input());
			Log::info($request->input());
			$response = Route::dispatch($request)->getContent();
			$response = json_decode($response);

		  if(!$response->Status)
			throw new Exception($response->Message);

		  DB::table('eseal_'.$mfgId)->where('primary_id',$parent)->update(['pkg_qty'=>$weight]);

		  $status =1;
		  $message = 'Binding and Mapping successfull ';
		  DB::commit();
		}
		catch(Exception $e){
			$status =0;
			$data = [];
			DB::rollback();
			$message = $e->getMessage();
		}
		$endTime = $this->getTime();
		Log::info(__FUNCTION__.' Finishes execution in '.($endTime-$startTime));
		Log::info(['Status'=>$status,'Message'=>$message]);
		return json_encode(['Status'=>$status,'Message'=>'Server: '.$message]);
	}


  public function rePack(){
  	try{
  		Log::info(__FUNCTION__.' : '.print_r(Input::get(),true));
  		$status =1;
  		$message = 'Updated Successfully.';
        $remove_childs = trim(Input::get('remove_ids'));
        $new_childs = trim(Input::get('new_ids'));
		$parent = trim(Input::get('parent'));
        $mfgId = $this->roleAccess->getMfgIdByToken(Input::get('access_token'));
        $locationId = $this->roleAccess->getLocIdByToken(Input::get('access_token')); 
        $esealTable = 'eseal_'.$mfgId;
        $esealBankTable = 'eseal_bank_'.$mfgId;
       
		if(empty($remove_childs) || empty($parent) || empty($new_childs))
			throw new Exception('Parameters Missing.');

		      $splitChilds = explode(',', $remove_childs);
			  $uniqueSplitChilds = array_unique($splitChilds);

         
		$parentCount = DB::table($esealTable)->whereIn('primary_id',$uniqueSplitChilds)->where('parent_id',$parent)->count();
        //Log::info('Matched Count: '.$parentCount.' Actual Child Count: '.count($uniqueSplitChilds));
		if($parentCount != count($uniqueSplitChilds))
			throw new Exception('Codes count not matching');
		$maxLevelId1 = DB::table($esealTable)->whereIn('primary_id',$uniqueSplitChilds)->max('level_id');

              $splitChilds = explode(',', $new_childs);
			  $uniqueSplitChilds = array_unique($splitChilds);
		
		$parentCount = DB::table($esealTable)->whereIn('primary_id',$uniqueSplitChilds)->where('parent_id','!=',0)->count();
		//Log::info('Already Packed: '.$parentCount.' Actual Child Count:' . count($uniqueSplitChilds));
		$newCount = DB::table($esealTable)->whereIn('primary_id',$uniqueSplitChilds)->count();
        $maxLevelId2 = DB::table($esealTable)->whereIn('primary_id',$uniqueSplitChilds)->max('level_id');

        if($newCount != count($uniqueSplitChilds))
        	throw new Exception('Codes count not matching');
		if($parentCount)
			throw new Exception('Some of the childs are already packed');

        if($maxLevelId1 != $maxLevelId2)
           throw new Exception('Replacement is not of same level');


        $remove_count = DB::table($this->trackHistoryTable.' as th')
	                  ->join($esealTable.' as es','es.track_id','=','th.track_id')
	                  ->whereIn('primary_id',explode(',',$remove_childs))
	                  ->where('th.src_loc_id',$locationId)
	                  ->count();        

        $new_count = DB::table($this->trackHistoryTable.' as th')
	                  ->join($esealTable.' as es','es.track_id','=','th.track_id')
	                  ->whereIn('primary_id',explode(',',$new_childs))
	                  ->where('th.src_loc_id',$locationId)
	                  ->count();        	                  
 
         //Log::info('remove count: '.$remove_count.' new count: '.$new_count);
        
        if($remove_count != $new_count)
        	throw new Exception('some of the ids are not at the given location');
        
        $pid = DB::table($esealTable)->whereIn('primary_id',explode(',',$remove_childs))->groupBy('pid','batch_no')->get(['pid','batch_no']);
         if(count($pid) > 1)
         	throw new Exception('The damaged ids belong to multiple products');
        
        $pidParent = DB::table($esealTable)->where('primary_id',$parent)->pluck('pid');
        
        $pid = DB::table($esealTable)->whereIn('primary_id',explode(',',$new_childs))->groupBy('pid','batch_no')->get(['pid','batch_no']);
         if(count($pid) > 1)
         	throw new Exception('The new ids belong to multiple products');

        if($pid[0]->pid != $pidParent)
        	throw new Exception('Product Mismatched');

        DB::table($esealTable)->whereIn('primary_id',explode(',',$remove_childs))->update(['parent_id'=>0]);

        DB::table($esealTable)->whereIn('primary_id',explode(',',$new_childs))->update(['parent_id'=>$parent]);

     }
  	catch(Exception $e){
  		DB::rollback();
  		$status =0;
  		$message = $e->getMessage();
  	}
  	Log::info(['Status'=>$status,'Message'=>$message]);
  	return json_encode(['Status'=>$status,'Message'=>$message]);
  }


  public function MapEseals(){
	//Purpose :- Mapping
	//Tables Involved :- eseal_mfgid,eseal_bank_mfgid,products,master_lookup
	//Scenarios Covered :- Checks if the child is a  finished product (or) parent is a pallet (or) codes needed to be updated in escortData table
	$debug=Input::get('debug');
	$startTime = $this->getTime();    
	  try{
		  $status = 0;
		  $message = 'Failed to map';

		$childs = trim(Input::get('ids'));
		$parent = trim(Input::get('parent'));
		$pid = trim(Input::get('pid'));
		$attributes = trim(Input::get('attributes'));
		$createParent = trim(Input::get('createParent'));
		
		//$locationId = trim(Input::get('srcLocationId'));
		$locationId = $this->roleAccess->getLocIdByToken(Input::get('access_token'));
		$removeMappedCodes = trim(Input::get('removeMappedCodes'));
		$mapParent = trim(Input::get('mapParent'));
		$isPallet = trim(Input::get('isPallet'));
		$transitionTime = trim(Input::get('transitionTime'));
		$flagJson = trim(Input::get('flagsJson'));
		$flagArr = json_decode($flagJson,true);
		$childsPacked = array();

		Input::merge(array('srcLocationId' => $locationId));
        
		Log::info(__FUNCTION__.' : '.print_r(Input::get(),true));
     	  DB::beginTransaction();
		if(!empty($childs) && !empty($parent) && !empty($locationId)){
			$locationObj = new Locations\Locations();
			$mfgId = $locationObj->getMfgIdForLocationId($locationId);

			  $splitChilds = explode(',', $childs);
			  $uniqueSplitChilds = array_unique($splitChilds);
			  $joinChilds = '\''.implode('\',\'', $uniqueSplitChilds).'\'';
			  $childCnt = count($uniqueSplitChilds);
			  
			  if(in_array($parent,$uniqueSplitChilds))
			  	throw new Exception('ERROR : one of the Primary IOT is scanned for parent IOT');

			  //Log::info('$childCnt'.$childCnt);
			  if(!empty($mfgId)){
		//		DB::beginTransaction();
				try{
				  $esealTable = 'eseal_'.$mfgId;
				  $esealBankTable = 'eseal_bank_'.$mfgId;
				  //$cnt = DB::table($esealBankTable)->where('issue_status', 1)->orWhere('download_status',1)->where('id', $parent)->count();
				  $cnt = DB::table($esealBankTable)->whereIn('id', array($parent))
							->where(function($query){
								$query->where('issue_status',1);
								$query->orWhere('download_status',1);
							})->count();
							
				  //Log::info(count($parent).' == '.$cnt);
                    /*if($debug=='shanu'){

						echo "resultsCnt".$resultsCnt;
						echo "childCnt".$childCnt;
						echo "ignoreInvalid".$flagArr['ignoreInvalid'];
						
						print_r(DB::getQueryLog());
						exit;
					}*/
				  if(count($parent) != $cnt){
					throw new Exception('Codes count not matching with code bank');
				  }

				  /*$parentCnt = DB::table($esealTable)
				                ->where('parent_id',$parent)                                
				                ->count('eseal_id');
				  if($parentCnt)
				  	throw new Exception('The parent is already packed');*/

				  $parentCnt = DB::table($esealTable)
				                ->where('parent_id',$parent)
				                ->whereIn('primary_id',$uniqueSplitChilds)
				                ->count('eseal_id');

				  if($parentCnt){
                                        
                                        $parentprim = DB::table($esealTable)
                                                ->where('primary_id',$parent)
                                                ->count('eseal_id');
                                        if($parentprim == 0)
                                       {

                                         goto jump;

                                       }

				  	$status_flag = 1;
				  	throw new Exception('This transaction is already completed');

				  }else{
				  	$parentCnt1 = DB::table($esealTable)
				                ->where(array('parent_id'=>$parent))                                
				                ->count('eseal_id');
				                
				    if($parentCnt1)
				    	throw new Exception('This parent is already mapped with different childern');	            

				    $childsPacked = DB::table($esealTable)                    
				                      ->whereIn('primary_id',$uniqueSplitChilds)
				                      ->where('parent_id','!=',0)                     
				                      ->lists('primary_id');				     				      
				      	
				      if($childsPacked)	
				      	throw new Exception('The childs are already mapped with another IOT');				      
				  }	

				 jump:
				 $resultsCnt = DB::table($esealTable)->whereIn('primary_id', $uniqueSplitChilds)->select(DB::raw('distinct(primary_id) as cnt'))->get();
					/*if($debug=='shanu'){

						echo "resultsCnt".$resultsCnt;
						echo "childCnt".$childCnt;
						echo "ignoreInvalid".$flagArr['ignoreInvalid'];
						
						print_r(DB::getQueryLog());
						exit;
					}*/
				 //echo "<pre/>";print_r(count($resultsCnt));exit;
				  //Log::info('resultsCnt '.count($resultsCnt));
				  $resultsCnt = count($resultsCnt);
				  if($resultsCnt > 0 ){

				  	 if($resultsCnt != $childCnt && (!isset($flagArr['ignoreInvalid']) || $flagArr['ignoreInvalid'] != 1) )
				  	 	throw new Exception('Child count not matching');

	                $pkg_qty = DB::table($esealTable)->whereIn('primary_id', $uniqueSplitChilds)->sum('pkg_qty');			  	

	                $storage_locations = DB::table($esealTable)->whereIn('primary_id', $uniqueSplitChilds)->distinct()->lists('storage_location');			  	
	                if(count($storage_locations) > 1)
	                	$storage_location = '';
	                else
	                	$storage_location = $storage_locations[0];

					//Log::info('pkg_qty :- ' .$pkg_qty);

					DB::table($esealTable)->whereIn('primary_id',$uniqueSplitChilds)->update(['parent_id'=> $parent]);
					//DB::table($esealTable)->where('primary_id',$parent)->update(['pkg_qty'=>$pkg_qty]);
					
					//Getting the maximum level_id of childs passed from eseal_mfgid table.	
					$maxLevelId = DB::table($esealTable)->whereIn('primary_id', $uniqueSplitChilds)->max('level_id');


                    $distinctPIDCount = DB::table($esealTable)
							  ->where('parent_id', $parent)
							  ->where('pid','!=', 0)
							  ->select('pid')
							  ->groupBy('pid')->get();
					
					//Checking whether the child is a finished product or not. 
					$isFinished =DB::table($esealTable)
								 ->join('products','products.product_id','=',$esealTable.'.pid')
								 ->join('master_lookup','master_lookup.value','=','products.product_type_id')
								 ->where('master_lookup.name','Finished Product')
								 ->whereIn($esealTable.'.primary_id',$uniqueSplitChilds)
								 ->pluck('id');
					if($isFinished){
					$maxLevelId++;
					if($isPallet){
						$maxLevelId = 8;
					}
					}else{
						$pkg_qty =1;
						if($createParent){
							if(empty($pid))
								throw new Exception('Product Id is empty');							
							
							$distinctPIDCount[0]->pid = $pid;

                          $request = Request::create('scoapi/SaveBindingAttributes', 'POST', array('module_id'=> Input::get('module_id'),'access_token'=>Input::get('access_token'),'attributes'=>$attributes,'lid'=>$locationId,'pid'=>$pid));
						  $originalInput = Request::input();//backup original input
						  Request::replace($request->input());						  
						  $res = Route::dispatch($request)->getContent();
						  Request::replace($originalInput);						  
						  $res = json_decode($res);
						  if($res->Status){
						   $attributeMapId = $res->AttributeMapId;
						  }
						  else{
						  	throw new Exception($res->Message);
						  }

                          goto jump1;

						}
					}
					
					//Log::info($maxLevelId);
					if(!empty($maxLevelId)){
						//Log::info('into first iff');						
					  $prentID = DB::table($esealTable)->where('primary_id', $parent)->first();
					  
				      //Log::info($distinctPIDCount);
					  if(count($prentID) && $prentID->eseal_id){
					  	//Log::info('into second iff');
						if(count($distinctPIDCount)>1){
							if(!$isPallet){
						  DB::table($esealTable)->where('primary_id', $parent)->update(Array('level_id'=> $maxLevelId, 'pid'=>0));
						}
						}else{
							if($mapParent){
						  DB::table($esealTable)->where('primary_id', $parent)->update( 
							Array(
							  'level_id'=> $maxLevelId
							  ) 
						  );
						}
						else{
						   DB::table($esealTable)->where('primary_id', $parent)->update( 
							Array(
							  'level_id'=> $maxLevelId,
							  'pid' => $distinctPIDCount[0]->pid
							  ) 
						  );	
						}
						}
					  }else{
					  	jump1:
					  	//Log::info('into else');
                        //Log::info($distinctPIDCount);
                        if($createParent){
                          DB::table($esealTable)->insert(
							  ['primary_id'=> $parent,'mfg_date'=>$this->getDate(),'level_id'=>0, 'pid'=> $pid,'pkg_qty'=>$pkg_qty,'storage_location'=>$storage_location,'attribute_map_id'=>$attributeMapId]
							);						  
                          DB::table($this->bindHistoryTable)->insert(['eseal_id'=>$parent,'attribute_map_id'=>$attributeMapId,'created_on'=>$this->getDate()]);
                        }
                        else{
						if(count($distinctPIDCount)>1){

						DB::table($esealTable)->insert(
							Array('primary_id'=> $parent, 'level_id'=> $maxLevelId, 'pid'=>0,'pkg_qty'=>$pkg_qty,'storage_location'=>$storage_location)
						  );
						}else{
						  DB::table($esealTable)->insert(
							  Array('primary_id'=> $parent, 'level_id'=> $maxLevelId, 'pid'=> $distinctPIDCount[0]->pid,'pkg_qty'=>$pkg_qty,'storage_location'=>$storage_location)
							);						  
						}
					}

					  }
					  
					}
					
					//Updating EsealBank table 
					DB::table($esealBankTable)->whereIn('id', array($parent))->update(Array(
					  'used_status'=>1,
					  'level'=>$maxLevelId,
					  'location_id' => $locationId,
					  'utilizedDate' => $transitionTime,
                                          'pid'=>$distinctPIDCount[0]->pid
     
					));
				  

					$distinctAttributeID = DB::table($esealTable)->where('parent_id', $parent)->select('attribute_map_id')->groupBy('attribute_map_id')->get();
					if(count($distinctAttributeID)==1){
						if($isFinished){
						DB::table($esealTable)->where('primary_id', $parent)->update(['attribute_map_id'=>$distinctAttributeID[0]->attribute_map_id]);
						}						

						//Log::info('location'.$locationId);
						Event::fire('scoapi/MapEseals', array('attribute_map_id'=>$distinctAttributeID[0]->attribute_map_id, 'codes'=>$parent, 'mfg_id'=>$mfgId,'srcLocationId'=>$locationId));                   
						//Log::info('no problem3');						
					}
					//Checking if codes are needed to be updated in escortData table or not.
					if($removeMappedCodes==1){
						if(!$this->removeMappedEscortCodes($childs, $parent)){
							throw new Exception('Exception occured while removing mapped codes');
						}
					}

					DB::commit();
					$status = 1;
					$message = 'Mapping done succesfully';
				  }else{
					throw new Exception('Child count not matching');
				  }   
				}catch(PDOException $e){
				  Log::error($e->getMessage());
				  throw new Exception('Error during parent child mapping');
				}
			  }else{
				throw new Exception('Customer id not found for given location');
			  }
		  }else{
			throw new Exception('Some of the params missing');
		  }
	  }catch(Exception $e){
		//$status =0;
		$status = (isset($status_flag) && $status_flag==1) ? 2 : 0;	
		  DB::rollback();
		  Log::info($e->getMessage());
		  $message = $e->getMessage();
	  }
	$endTime = $this->getTime();
	Log::info(__FUNCTION__.' Finishes execution in '.($endTime-$startTime));

	  return json_encode(Array('Status'=>$status, 'Message' =>'Server: '.$message,'iots'=>$childsPacked));      
  }

  
  public function MapEsealsBackup(){
	//Purpose :- Mapping
	//Tables Involved :- eseal_mfgid,eseal_bank_mfgid,products,master_lookup
	//Scenarios Covered :- Checks if the child is a  finished product (or) parent is a pallet (or) codes needed to be updated in escortData table
	$startTime = $this->getTime();    
	  try{
		  $status = 0;
		  $message = 'Failed to map';

		$childs = trim(Input::get('ids'));
		$parent = trim(Input::get('parent'));
		//$locationId = trim(Input::get('srcLocationId'));
		$locationId = $this->roleAccess->getLocIdByToken(Input::get('access_token'));
		$removeMappedCodes = trim(Input::get('removeMappedCodes'));
		$mapParent = trim(Input::get('mapParent'));
		$isPallet = trim(Input::get('isPallet'));
		$transitionTime = trim(Input::get('transitionTime'));
		$flagJson = trim(Input::get('flagsJson'));
		$flagArr = json_decode($flagJson,true);

		Input::merge(array('srcLocationId' => $locationId));

		if(date('Y', strtotime($transitionTime)) == '2009')
		    	Input::merge(array('transitionTime' => $this->getDate()));

		Log::info(__FUNCTION__.' : '.print_r(Input::get(),true));
		if(!empty($childs) && !empty($parent) && !empty($locationId)){
			$locationObj = new Locations\Locations();
			$mfgId = $locationObj->getMfgIdForLocationId($locationId);

			  $splitChilds = explode(',', $childs);
			  $uniqueSplitChilds = array_unique($splitChilds);
			  $joinChilds = '\''.implode('\',\'', $uniqueSplitChilds).'\'';
			  $childCnt = count($uniqueSplitChilds);
			  Log::info('$childCnt'.$childCnt);
			  if(!empty($mfgId)){
				DB::beginTransaction();
				try{
				  $esealTable = 'eseal_'.$mfgId;
				  $esealBankTable = 'eseal_bank_'.$mfgId;
				  //$cnt = DB::table($esealBankTable)->where('issue_status', 1)->orWhere('download_status',1)->where('id', $parent)->count();
				  $cnt = DB::table($esealBankTable)->whereIn('id', array($parent))
							->where(function($query){
								$query->where('issue_status',1);
								$query->orWhere('download_status',1);
							})->count();

				  //Log::info(count($parent).' == '.$cnt);
				  if(count($parent) != $cnt){
					throw new Exception('Codes count not matching with code bank');
				  }

				  $parentCnt = DB::table($esealTable)
				                ->where('parent_id',$parent)
                                ->orWhere('primary_id',$parent)
				                ->count('eseal_id');
				  if($parentCnt)
				  	throw new Exception('The parent is already packed');


				 
				 $resultsCnt = DB::table($esealTable)->whereIn('primary_id', $uniqueSplitChilds)->select(DB::raw('distinct(primary_id) as cnt'))->get();
				 //echo "<pre/>";print_r(count($resultsCnt));exit;
				  //Log::info('resultsCnt '.count($resultsCnt));
				  $resultsCnt = count($resultsCnt);
				  if($resultsCnt > 0 ){

				  	 if($resultsCnt != $childCnt && (!isset($flagArr['ignoreInvalid']) && $flagArr['ignoreInvalid'] != 1) )
				  	 	throw new Exception('Child count not matching');

	                $pkg_qty = DB::table($esealTable)->whereIn('primary_id', $uniqueSplitChilds)->sum('pkg_qty');			  	

					//Log::info('pkg_qty :- ' .$pkg_qty);

					DB::table($esealTable)->whereIn('primary_id', $uniqueSplitChilds)->update(Array('parent_id'=> $parent));
					//DB::table($esealTable)->where('primary_id',$parent)->update(['pkg_qty'=>$pkg_qty]);
					
					//Getting the maximum level_id of childs passed from eseal_mfgid table.	
					$maxLevelId = DB::table($esealTable)->whereIn('primary_id', $uniqueSplitChilds)->max('level_id');

                                        $distinctPIDCount = DB::table($esealTable)
							  ->where('parent_id', $parent)
							  ->where('pid','!=', 0)
							  ->select('pid')
							  ->groupBy('pid')->get();
					
					//Checking whether the child is a finished product or not. 
					$isFinished =DB::table($esealTable)
								 ->join('products','products.product_id','=',$esealTable.'.pid')
								 ->join('master_lookup','master_lookup.value','=','products.product_type_id')
								 ->where('master_lookup.name','Finished Product')
								 ->whereIn($esealTable.'.primary_id',$uniqueSplitChilds)
								 ->pluck('id');
					if($isFinished){
					$maxLevelId++;
					if($isPallet){
						$maxLevelId = 8;
					}
					}
					
					//Log::info($maxLevelId);
					if(!empty($maxLevelId)){
					  $prentID = DB::table($esealTable)->where('primary_id', $parent)->first();
					  $distinctPIDCount = DB::table($esealTable)
							  ->where('parent_id', $parent)
							  ->where('pid','!=', 0)
							  ->select('pid')
							  ->groupBy('pid')->get();
							  //Log::info($distinctPIDCount);
					  if(count($prentID) && $prentID->eseal_id){
						if(count($distinctPIDCount)>1){
							if(!$isPallet){
						  DB::table($esealTable)->where('primary_id', $parent)->update(Array('level_id'=> $maxLevelId, 'pid'=>0));
						}
						}else{
							if($mapParent){
						  DB::table($esealTable)->where('primary_id', $parent)->update( 
							Array(
							  'level_id'=> $maxLevelId
							  ) 
						  );
						}
						else{
						   DB::table($esealTable)->where('primary_id', $parent)->update( 
							Array(
							  'level_id'=> $maxLevelId,
							  'pid' => $distinctPIDCount[0]->pid
							  ) 
						  );	
						}
						}
					  }else{
						if(count($distinctPIDCount)>1){
						DB::table($esealTable)->insert(
							Array('primary_id'=> $parent, 'level_id'=> $maxLevelId, 'pid'=>0,'pkg_qty'=>$pkg_qty)
						  );
						}else{
						  DB::table($esealTable)->insert(
							  Array('primary_id'=> $parent, 'level_id'=> $maxLevelId, 'pid'=> $distinctPIDCount[0]->pid,'pkg_qty'=>$pkg_qty)
							);
						  //DB::insert('INSERT INTO '.$esealTable.' (primary_id, level_id) values (?,?)', array($parent, $maxLevelId));  
						}
					  }
					  
					}
					
					//Updating EsealBank table 
					DB::table($esealBankTable)->whereIn('id', array($parent))->update(Array(
					  'used_status'=>1,
					  'level'=>$maxLevelId,
					  'location_id' => $locationId,
					  'utilizedDate' => $transitionTime,
                                          'pid' => $distinctPIDCount[0]->pid
					));
				  

					$distinctAttributeID = DB::table($esealTable)->where('parent_id', $parent)->select('attribute_map_id')->groupBy('attribute_map_id')->get();
					if(count($distinctAttributeID)==1){
						if($isFinished){
						DB::table($esealTable)->where('primary_id', $parent)->update(['attribute_map_id'=>$distinctAttributeID[0]->attribute_map_id]);
						}
						Event::fire('scoapi/MapEseals', array('attribute_map_id'=>$distinctAttributeID[0]->attribute_map_id, 'codes'=>$parent, 'mfg_id'=>$mfgId));                   
					}
					//Checking if codes are needed to be updated in escortData table or not.
					if($removeMappedCodes==1){
						if(!$this->removeMappedEscortCodes($childs, $parent)){
							throw new Exception('Exception occured while removing mapped codes');
						}
					}

					DB::commit();
					$status = 1;
					$message = 'Mapping done succesfully';
				  }else{
					throw new Exception('Child count not matching');
				  }   
				}catch(PDOException $e){
				  Log::error($e->getMessage());
				  throw new Exception('Error during parent child mapping');
				}
			  }else{
				throw new Exception('Customer id not found for given location');
			  }
		  }else{
			throw new Exception('Some of the params missing');
		  }
	  }catch(Exception $e){
		$status =0;
		  DB::rollback();
		  Log::info($e->getMessage());
		  $message = $e->getMessage();
	  }
	$endTime = $this->getTime();
	Log::info(__FUNCTION__.' Finishes execution in '.($endTime-$startTime));

	  return json_encode(Array('Status'=>$status, 'Message' =>'Server: '.$message));      
  }


  public function MapEseals1(){
	//Purpose :- Mapping
	//Tables Involved :- eseal_mfgid,eseal_bank_mfgid,products,master_lookup
	//Scenarios Covered :- Checks if the child is a  finished product (or) parent is a pallet (or) codes needed to be updated in escortData table
	$startTime = $this->getTime();    
	  try{
		  $status = 0;
		  $message = 'Failed to map';

		$childs = trim(Input::get('ids'));
		$parent = trim(Input::get('parent'));
		//$locationId = trim(Input::get('srcLocationId'));
		$locationId = $this->roleAccess->getLocIdByToken(Input::get('access_token'));
		$removeMappedCodes = trim(Input::get('removeMappedCodes'));
		$mapParent = trim(Input::get('mapParent'));
		$isPallet = trim(Input::get('isPallet'));
		$transitionTime = trim(Input::get('transitionTime'));

		if(date('Y', strtotime($transitionTime)) == '2009')
		    	Input::merge(array('transitionTime' => $this->getDate()));

		Input::merge(array('srcLocationId' => $locationId));

		Log::info(__FUNCTION__.' : '.print_r(Input::get(),true));
		if(!empty($childs) && !empty($parent) && !empty($locationId)){
			$locationObj = new Locations\Locations();
			$mfgId = $locationObj->getMfgIdForLocationId($locationId);

			  $splitChilds = explode(',', $childs);
			  $uniqueSplitChilds = array_unique($splitChilds);
			  $joinChilds = '\''.implode('\',\'', $uniqueSplitChilds).'\'';
			  $childCnt = count($uniqueSplitChilds);
			  //Log::info('$childCnt'.$childCnt);
			  if(!empty($mfgId)){
				
				try{
				  $esealTable = 'eseal_'.$mfgId;
				  $esealBankTable = 'eseal_bank_'.$mfgId;
				  //$cnt = DB::table($esealBankTable)->where('issue_status', 1)->orWhere('download_status',1)->where('id', $parent)->count();
				  $cnt = DB::table($esealBankTable)->whereIn('id', array($parent))
							->where(function($query){
								$query->where('issue_status',1);
								$query->orWhere('download_status',1);
							})->count();

				 // Log::info(count($parent).' == '.$cnt);
				  if(count($parent) != $cnt){
					throw new Exception('Codes count not matching with code bank');
				  }

				 
				 $resultsCnt = DB::table($esealTable)->whereIn('primary_id', $uniqueSplitChilds)->select(DB::raw('distinct(primary_id) as cnt'))->get();
				 //echo "<pre/>";print_r(count($resultsCnt));exit;
				  //Log::info('resultsCnt '.count($resultsCnt));
				  $resultsCnt = count($resultsCnt);
				  if( $resultsCnt > 0 ){
	                $pkg_qty = DB::table($esealTable)->whereIn('primary_id', $uniqueSplitChilds)->sum('pkg_qty');			  	
					
					//Log::info('pkg_qty :- ' .$pkg_qty);

					DB::table($esealTable)->whereIn('primary_id', $uniqueSplitChilds)->update(Array('parent_id'=> $parent));
					//DB::table($esealTable)->where('primary_id',$parent)->update(['pkg_qty'=>$pkg_qty]);
					
					//Getting the maximum level_id of childs passed from eseal_mfgid table.	
					$maxLevelId = DB::table($esealTable)->whereIn('primary_id', $uniqueSplitChilds)->max('level_id');
					
					//Checking whether the child is a finished product or not. 
					$isFinished =DB::table($esealTable)
								 ->join('products','products.product_id','=',$esealTable.'.pid')
								 ->join('master_lookup','master_lookup.value','=','products.product_type_id')
								 ->where('master_lookup.name','Finished Product')
								 ->whereIn($esealTable.'.primary_id',$uniqueSplitChilds)
								 ->pluck('id');
					if($isFinished){
					$maxLevelId++;
					if($isPallet){
						$maxLevelId = 8;
					}
					}
					
					//Log::info($maxLevelId);
					if(!empty($maxLevelId)){
					  $prentID = DB::table($esealTable)->where('primary_id', $parent)->first();
					  $distinctPIDCount = DB::table($esealTable)
							  ->where('parent_id', $parent)
							  ->where('pid','!=', 0)
							  ->select('pid')
							  ->groupBy('pid')->get();
					//		  Log::info($distinctPIDCount);
					  if(count($prentID) && $prentID->eseal_id){
						if(count($distinctPIDCount)>1){
							if(!$isPallet){
						  DB::table($esealTable)->where('primary_id', $parent)->update(Array('level_id'=> $maxLevelId, 'pid'=>0));
						}
						}else{
							if($mapParent){
						  DB::table($esealTable)->where('primary_id', $parent)->update( 
							Array(
							  'level_id'=> $maxLevelId
							  ) 
						  );
						}
						else{
						   DB::table($esealTable)->where('primary_id', $parent)->update( 
							Array(
							  'level_id'=> $maxLevelId,
							  'pid' => $distinctPIDCount[0]->pid
							  ) 
						  );	
						}
						}
					  }else{
						if(count($distinctPIDCount)>1){
						DB::table($esealTable)->insert(
							Array('primary_id'=> $parent, 'level_id'=> $maxLevelId, 'pid'=>0,'pkg_qty'=>$pkg_qty)
						  );
						}else{
						  DB::table($esealTable)->insert(
							  Array('primary_id'=> $parent, 'level_id'=> $maxLevelId, 'pid'=> $distinctPIDCount[0]->pid,'pkg_qty'=>$pkg_qty)
							);
						  //DB::insert('INSERT INTO '.$esealTable.' (primary_id, level_id) values (?,?)', array($parent, $maxLevelId));  
						}
					  }
					  
					}
					
					//Updating EsealBank table 
					DB::table($esealBankTable)->whereIn('id', array($parent))->update(Array(
					  'used_status'=>1,
					  'level'=>$maxLevelId,
					  'location_id' => $locationId,
					  'utilizedDate' => $transitionTime
					));
				  

					$distinctAttributeID = DB::table($esealTable)->where('parent_id', $parent)->select('attribute_map_id')->groupBy('attribute_map_id')->get();
					if(count($distinctAttributeID)==1){
						if($isFinished){
						DB::table($esealTable)->where('primary_id', $parent)->update(['attribute_map_id'=>$distinctAttributeID[0]->attribute_map_id]);
						}
						Event::fire('scoapi/MapEseals', array('attribute_map_id'=>$distinctAttributeID[0]->attribute_map_id, 'codes'=>$parent, 'mfg_id'=>$mfgId));                   
					}
					//Checking if codes are needed to be updated in escortData table or not.
					if($removeMappedCodes==1){
						if(!$this->removeMappedEscortCodes($childs, $parent)){
							throw new Exception('Exception occured while removing mapped codes');
						}
					}

					
					$status = 1;
					$message = 'Mapping done succesfully';
				  }else{
					throw new Exception('Child count not matching');
				  }   
				}catch(PDOException $e){
				  Log::error($e->getMessage());
				  throw new Exception('Error during parent child mapping');
				}
			  }else{
				throw new Exception('Customer id not found for given location');
			  }
		  }else{
			throw new Exception('Some of the params missing');
		  }
	  }catch(Exception $e){
		$status =0;
		  
		  Log::info($e->getMessage());
		  $message = $e->getMessage();
	  }
	$endTime = $this->getTime();
	Log::info(__FUNCTION__.' Finishes execution in '.($endTime-$startTime));

	  return json_encode(Array('Status'=>$status, 'Message' =>'Server: '.$message));      
  }


public function UpdateTracking(){
	//Purpose :- Mapping
	//Tables Involved :- eseal_mfgid,eseal_bank_mfgid,products,master_lookup
	//Scenarios Covered :- Checks if the child is a  finished product (or) parent is a pallet (or) codes needed to be updated in escortData table
	$startTime = $this->getTime();

	try{
		$status = 0;
		$message = 'Failed to update track info';

		$destLocationId = 0;
		$searchInChild = trim(Input::get('searchInChild')); 
		$codes = trim(Input::get('codes'));
		$parent = trim(Input::get('parent'));
		$srcLocationId = rtrim(ltrim(Input::get('srcLocationId')));
		$destLocationId = trim(Input::get('destLocationId'));
		$transitionTime = trim(Input::get('transitionTime'));
		//$transitionTime = $this->getDate();
		$transitionId = rtrim(ltrim(Input::get('transitionId')));
		$internalTransfer = trim(Input::get('internalTransfer'));
		$notUpdateChild = trim(Input::get('notUpdateChild'));
		Log::info(__FUNCTION__.' : '.print_r(Input::get(),true));

		DB::beginTransaction();
	   
		if(!is_numeric($destLocationId)){
			$destLocationId =0;
		}
	   
		if(!is_numeric($srcLocationId) || !is_numeric($destLocationId) || !is_numeric($transitionId)){
		  throw new Exception('Some of the parameter is not numeric');
		}
		if(!is_string($codes) || empty($codes)){
		  throw new Exception('Codes should not be empty and must be string'); 
		}

		$locationObj = new Locations\Locations();
		$mfgId = $locationObj->getMfgIdForLocationId($srcLocationId);
		$esealTable = 'eseal_'.$mfgId;
		$transactionObj = new TransactionMaster\TransactionMaster();
		$transactionDetails = $transactionObj->getTransactionDetails($mfgId, $transitionId);
		Log::info(print_r($transactionDetails, true));
		if($transactionDetails){
		  $srcLocationAction = $transactionDetails[0]->srcLoc_action;
		  $destLocationAction = $transactionDetails[0]->dstLoc_action;
		  $inTransitAction = $transactionDetails[0]->intrn_action;
		}
	

		$splitChilds = explode(',', $codes);
		
		
		if(!empty($parent)){
		array_push($splitChilds,$parent);
		}
	   

	   if($notUpdateChild){
		$splitChilds = array();
		$splitChilds[] = $parent;
	   }

		$uniqueSplitChilds = array_unique($splitChilds);
		$joinChilds = '\''.implode('\',\'', $uniqueSplitChilds).'\'';
		$childCnt = count($uniqueSplitChilds);

//		Log::info('$childCnt'.$childCnt);
//		Log::info('SrcLocAction : ' . $srcLocationAction.' , DestLocAction: '. $destLocationAction.', inTransitAction: '. $inTransitAction);
		//echo '<pre/>';print_r('SrcLocAction : ' . $srcLocationAction.' , DestLocAction: '. $destLocationAction.', inTransitAction: '. $inTransitAction);exit;
		$trakHistoryObj = new TrackHistory\TrackHistory();
		//Log::info(var_dump($trakHistoryObj));
		
		if($internalTransfer==TRUE){
			if(empty($destLocationId)){
				throw new Exception('Provide destination location id');
			}
		}
		//echo 'kkk1';exit;
		Log::info(__LINE__);
		if($srcLocationAction==1 && $destLocationAction==0 && $inTransitAction==0){
			
			try{ 
				$codesCnt = DB::table($esealTable)
					->whereIn('primary_id', $uniqueSplitChilds)->count();
				if($codesCnt != $childCnt){
					throw new Exception('Codes count not matching');
				}
		
				$codesTrack = DB::table($esealTable.' as eseal')
					->join($this->trackHistoryTable.' as th', 'eseal.track_id','=','th.track_id')
					->whereIn('eseal.primary_id', $uniqueSplitChilds)
					->select('th.src_loc_id','th.dest_loc_id')
					->get();
				
				$locationObj = new Locations\Locations();
				$childIds = $locationObj->getAllChildIdForParentId($srcLocationId);	
//				Log::info($childIds);
				if(!$searchInChild){
					$childIds = array();
				}
				if(count($codesTrack)){
					foreach($codesTrack as $trackRow){
//						Log::info('Source Location:'.$trackRow->src_loc_id);
//						Log::info('Passed source location:'.$srcLocationId);
						if(($trackRow->src_loc_id!=$srcLocationId && !in_array($trackRow->src_loc_id,$childIds)) || $trackRow->dest_loc_id>0){
							throw new Exception('Some of the codes are not available at given locations');
						}
					}
				}
				
				 $lastInrtId = $trakHistoryObj->insertTrack(
					$srcLocationId, $destLocationId, $transitionId, $transitionTime
					);
				
//				Log::info('track_id'.$lastInrtId);
				
				if($notUpdateChild){
				DB::table($esealTable)
					->whereIn('primary_id', $uniqueSplitChilds)					
					->update(Array('track_id'=>$lastInrtId));  
				}
				else{
				DB::table($esealTable)
					->whereIn('primary_id', $uniqueSplitChilds)
					->orWhereIn('parent_id', $uniqueSplitChilds)
					->update(Array('track_id'=>$lastInrtId));
				}
				
				$sql = '
					INSERT INTO 
						'.$this->trackDetailsTable.' (code, track_id) 
					SELECT 
						primary_id, '.$lastInrtId.' 
					FROM 
						'.$esealTable.' 
					WHERE 
						track_id = '.$lastInrtId;
				DB::insert($sql);
			}catch(PDOException $e){
				Log::info($e->getMessage());
				throw new Exception('SQlError during packing');
			}
			if($internalTransfer==TRUE){
				$ReciveTransitId = DB::table($this->transactionMasterTable)
					->where('action_code','GRN')
					->where('manufacturer_id', $mfgId)
					->pluck('id');
					//echo 'tranis1'.$transitionId;exit;
				
				
				$lastInrtId = $trakHistoryObj->insertTrack(
					$destLocationId, 0, $transitionId, $transitionTime
					);  
			
				DB::table($esealTable)
					->whereIn('primary_id', $uniqueSplitChilds)
					->orWhereIn('parent_id', $uniqueSplitChilds)
					->update(Array('track_id'=>$lastInrtId));

				$sql = '
					INSERT INTO 
						'.$this->trackDetailsTable.' (code, track_id) 
					SELECT 
						primary_id, '.$lastInrtId.' 
					FROM 
						'.$esealTable.' 
					WHERE 
						track_id = '.$lastInrtId;
				DB::insert($sql);
			}
		}
		if($srcLocationAction==0 && $destLocationAction==1 && $inTransitAction==-1){
			try{
				$codesCnt = DB::table($esealTable)
					->whereIn('primary_id', $uniqueSplitChilds)->count();
				if($codesCnt != $childCnt){
					throw new Exception('Codes count not matching');
				}
		
				$codesTrack = DB::table($esealTable.' as eseal')
					->join($this->trackHistoryTable.' as th', 'eseal.track_id','=','th.track_id')
					->whereIn('eseal.primary_id', $uniqueSplitChilds)
					->select('th.src_loc_id','th.dest_loc_id')
					->get();
			   
				if(count($codesTrack)){
					foreach($codesTrack as $trackRow){
						//Log::info($trackRow->src_loc_id.'*****'.$srcLocationId);
						//Log::info('^^^^^'.$trackRow->dest_loc_id);
						if($trackRow->src_loc_id == $srcLocationId || $trackRow->dest_loc_id = 0){
							throw new Exception('Some of the codes are already available at given locations');
						}

					}
				}
				
				$lastInrtId = $trakHistoryObj->insertTrack(
					$srcLocationId, $destLocationId, $transitionId, $transitionTime
					);
		  
				
					$maxLevelId = 	DB::table($esealTable)
								->whereIn('parent_id', $uniqueSplitChilds)
								->orWhereIn('primary_id', $uniqueSplitChilds)->max('level_id');



			if(!$this->updateTrackForChilds($esealTable, $lastInrtId, $uniqueSplitChilds, $maxLevelId)){
				throw new Exception('Exception occured during track updation');
			}

				$sql = '
					INSERT INTO 
						'.$this->trackDetailsTable.' (code, track_id) 
					SELECT 
						primary_id, '.$lastInrtId.' 
					FROM 
						'.$esealTable.' 
					WHERE 
						track_id = '.$lastInrtId;
				DB::insert($sql);
			}catch(PDOException $e){
				Log::info($e->getMessage());
				throw new Exception('SQlError during packing');
			}
			if($internalTransfer==TRUE){
				$ReciveTransitId = DB::table($this->transactionMasterTable)
					->where('action_code','GRN')
					->where('manufacturer_id', $mfgId)
					->pluck('id');
				
				$lastInrtId = $trakHistoryObj->insertTrack(
					$destLocationId, 0, $transitionId, $transitionTime
					);  
			
				DB::table($esealTable)
					->whereIn('primary_id', $uniqueSplitChilds)
					->orWhereIn('parent_id', $uniqueSplitChilds)
					->update(Array('track_id'=>$lastInrtId));


				$sql = '
					INSERT INTO 
						'.$this->trackDetailsTable.' (code, track_id) 
					SELECT 
						primary_id, '.$lastInrtId.' 
					FROM 
						'.$esealTable.' 
					WHERE 
						track_id = '.$lastInrtId;
				DB::insert($sql);
			}
		}

		/*****/

		if($srcLocationAction==-1 && $destLocationAction==0 && $inTransitAction==1){//////////////////For stock out
		$trakHistoryObj = new TrackHistory\TrackHistory();
		//Log::info(var_dump($trakHistoryObj));
		
		try{
			//Log::info('Destination Location:');
			//Log::info($destLocationId);
			
			$lastInrtId = DB::table($this->trackHistoryTable)->insertGetId( Array(
				'src_loc_id'=>$srcLocationId, 'dest_loc_id'=>$destLocationId, 
				'transition_id'=>$transitionId,'update_time'=>$transitionTime));
//			Log::info($lastInrtId);

			$maxLevelId = 	DB::table($esealTable)
								->whereIn('parent_id', $uniqueSplitChilds)
								->orWhereIn('primary_id', $uniqueSplitChilds)->max('level_id');



			if(!$this->updateTrackForChilds($esealTable, $lastInrtId, $uniqueSplitChilds, $maxLevelId)){
				throw new Exception('Exception occured during track updation');
			}
			/*DB::table($esealTable)->whereIn('primary_id', )
				  ->orWhereIn('parent_id', $explodedIds)
				  ->update(Array('track_id' => $lastInrtId));	*/
			Log::info(__LINE__);
			$sql = 'INSERT INTO  '.$this->trackDetailsTable.' (code, track_id) SELECT primary_id, '.$lastInrtId.' FROM '.$esealTable.' WHERE track_id='.$lastInrtId;
			DB::insert($sql);

			
			
		}catch(PDOException $e){
			Log::info($e->getMessage());
			throw new Exception('SQlError during track update');
		}
	  }




		/*****/

		$status = 1;
		$message = 'Track info updated successfully';
		DB::commit();        
		Log::info(__LINE__);
	}catch(Exception $e){
		DB::rollback();        
		$message = $e->getMessage();
		Log::info($e->getMessage());
	}
	$endTime = $this->getTime();
	Log::info(__FUNCTION__.' Finishes execution in '.($endTime-$startTime));
Log::info(['Status'=>$status, 'Message' => $message]);
	return json_encode(['Status'=>$status, 'Message' =>'Server: '.$message]);      
}





public function liquidateUpdateTracking(){
	//Purpose :- Mapping
	//Tables Involved :- eseal_mfgid,eseal_bank_mfgid,products,master_lookup
	//Scenarios Covered :- Checks if the child is a  finished product (or) parent is a pallet (or) codes needed to be updated in escortData table
	$startTime = $this->getTime();

	try{
		$status = 0;
		$message = 'Failed to update track info';

		$destLocationId = 0;
		$searchInChild = trim(Input::get('searchInChild')); 
		$codes = trim(Input::get('codes'));
		$parent = trim(Input::get('parent'));
		$srcLocationId = rtrim(ltrim(Input::get('srcLocationId')));
		$destLocationId = trim(Input::get('destLocationId'));
		$transitionTime = trim(Input::get('transitionTime'));
		//$transitionTime = $this->getDate();
		$transitionId = rtrim(ltrim(Input::get('transitionId')));
		$internalTransfer = trim(Input::get('internalTransfer'));
		Log::info(__FUNCTION__.' : '.print_r(Input::get(),true));

		DB::beginTransaction();
	   
		if(!is_numeric($destLocationId)){
			$destLocationId =0;
		}
	   
		if(!is_numeric($srcLocationId) || !is_numeric($destLocationId) || !is_numeric($transitionId)){
		  throw new Exception('Some of the parameter is not numeric');
		}
		if(!is_string($codes) || empty($codes)){
		  throw new Exception('Codes should not be empty and must be string'); 
		}

		$locationObj = new Locations\Locations();
		$mfgId = $locationObj->getMfgIdForLocationId($srcLocationId);
		$esealTable = 'eseal_'.$mfgId;
		$transactionObj = new TransactionMaster\TransactionMaster();
		$transactionDetails = $transactionObj->getTransactionDetails($mfgId, $transitionId);
		//Log::info(print_r($transactionDetails, true));
		if($transactionDetails){
		  $srcLocationAction = $transactionDetails[0]->srcLoc_action;
		  $destLocationAction = $transactionDetails[0]->dstLoc_action;
		  $inTransitAction = $transactionDetails[0]->intrn_action;
		}
	

		$splitChilds = explode(',', $codes);
		if(!empty($parent)){
		array_push($splitChilds,$parent);
		}
		$uniqueSplitChilds = array_unique($splitChilds);
		$joinChilds = '\''.implode('\',\'', $uniqueSplitChilds).'\'';
		$childCnt = count($uniqueSplitChilds);

		//Log::info('$childCnt'.$childCnt);
		//Log::info('SrcLocAction : ' . $srcLocationAction.' , DestLocAction: '. $destLocationAction.', inTransitAction: '. $inTransitAction);
		//echo '<pre/>';print_r('SrcLocAction : ' . $srcLocationAction.' , DestLocAction: '. $destLocationAction.', inTransitAction: '. $inTransitAction);exit;
		$trakHistoryObj = new TrackHistory\TrackHistory();
		//Log::info(var_dump($trakHistoryObj));
		
		if($internalTransfer==TRUE){
			if(empty($destLocationId)){
				throw new Exception('Provide destination location id');
			}
		}
		//echo 'kkk1';exit;
		//Log::info(__LINE__);
		if($srcLocationAction==1 && $destLocationAction==0 && $inTransitAction==0){
			
			try{ 
				$codesCnt = DB::table($esealTable)
					->whereIn('primary_id', $uniqueSplitChilds)->count();
				if($codesCnt != $childCnt){
					throw new Exception('Codes count not matching');
				}
		
				$codesTrack = DB::table($esealTable.' as eseal')
					->join($this->trackHistoryTable.' as th', 'eseal.track_id','=','th.track_id')
					->whereIn('eseal.primary_id', $uniqueSplitChilds)
					->select('th.src_loc_id','th.dest_loc_id')
					->get();
				
				$locationObj = new Locations\Locations();
				$childIds = $locationObj->getAllChildIdForParentId($srcLocationId);	
		//		Log::info($childIds);
				if(!$searchInChild){
					$childIds = array();
				}
				if(count($codesTrack)){
					foreach($codesTrack as $trackRow){
		//				Log::info('Source Location:'.$trackRow->src_loc_id);
		//				Log::info('Passed source location:'.$srcLocationId);
						if(($trackRow->src_loc_id!=$srcLocationId && !in_array($trackRow->src_loc_id,$childIds)) || $trackRow->dest_loc_id>0){
							throw new Exception('Some of the codes are not available at given locations');
						}
					}
				}
				$lastInrtId = $trakHistoryObj->insertTrack(
					$srcLocationId, $destLocationId, $transitionId, $transitionTime
					);
		  //Log::info('track_id'.$lastInrtId);
				DB::table($esealTable)
					->whereIn('primary_id', $uniqueSplitChilds)
					->orWhereIn('parent_id', $uniqueSplitChilds)
					->update(Array('track_id'=>$lastInrtId));

				$sql = '
					INSERT INTO 
						'.$this->trackDetailsTable.' (code, track_id) 
					SELECT 
						primary_id, '.$lastInrtId.' 
					FROM 
						'.$esealTable.' 
					WHERE 
						track_id = '.$lastInrtId;
				DB::insert($sql);
			}catch(PDOException $e){
				Log::info($e->getMessage());
				throw new Exception('SQlError during packing');
			}
			if($internalTransfer==TRUE){
				$ReciveTransitId = DB::table($this->transactionMasterTable)
					->where('action_code','GRN')
					->where('manufacturer_id', $mfgId)
					->pluck('id');
					//echo 'tranis1'.$transitionId;exit;
				$lastInrtId = $trakHistoryObj->insertTrack(
					$destLocationId, 0, $transitionId, $transitionTime
					);  
			
				DB::table($esealTable)
					->whereIn('primary_id', $uniqueSplitChilds)
					->orWhereIn('parent_id', $uniqueSplitChilds)
					->update(Array('track_id'=>$lastInrtId));

				$sql = '
					INSERT INTO 
						'.$this->trackDetailsTable.' (code, track_id) 
					SELECT 
						primary_id, '.$lastInrtId.' 
					FROM 
						'.$esealTable.' 
					WHERE 
						track_id = '.$lastInrtId;
				DB::insert($sql);
			}
		}
		if($srcLocationAction==0 && $destLocationAction==1 && $inTransitAction==-1){
			try{
				$codesCnt = DB::table($esealTable)
					->whereIn('primary_id', $uniqueSplitChilds)->count();
				if($codesCnt != $childCnt){
					throw new Exception('Codes count not matching');
				}
		
				$codesTrack = DB::table($esealTable.' as eseal')
					->join($this->trackHistoryTable.' as th', 'eseal.track_id','=','th.track_id')
					->whereIn('eseal.primary_id', $uniqueSplitChilds)
					->select('th.src_loc_id','th.dest_loc_id')
					->get();
			   
				if(count($codesTrack)){
					foreach($codesTrack as $trackRow){
			//			Log::info($trackRow->src_loc_id.'*****'.$srcLocationId);
			//			Log::info('^^^^^'.$trackRow->dest_loc_id);
						if($trackRow->src_loc_id == $srcLocationId || $trackRow->dest_loc_id != 0){
							//throw new Exception('Some of the codes are already available at given locations');
						}
					}
				}
				$lastInrtId = $trakHistoryObj->insertTrack(
					$srcLocationId, $destLocationId, $transitionId, $transitionTime
					);
		  
				DB::table($esealTable)
					->whereIn('primary_id', $uniqueSplitChilds)
					->orWhereIn('parent_id', $uniqueSplitChilds)
					->update(Array('track_id'=>$lastInrtId));

				$sql = '
					INSERT INTO 
						'.$this->trackDetailsTable.' (code, track_id) 
					SELECT 
						primary_id, '.$lastInrtId.' 
					FROM 
						'.$esealTable.' 
					WHERE 
						track_id = '.$lastInrtId;
				DB::insert($sql);
			}catch(PDOException $e){
				Log::info($e->getMessage());
				throw new Exception('SQlError during packing');
			}
			if($internalTransfer==TRUE){
				$ReciveTransitId = DB::table($this->transactionMasterTable)
					->where('action_code','GRN')
					->where('manufacturer_id', $mfgId)
					->pluck('id');
				$lastInrtId = $trakHistoryObj->insertTrack(
					$destLocationId, 0, $transitionId, $transitionTime
					);  
			
				DB::table($esealTable)
					->whereIn('primary_id', $uniqueSplitChilds)
					->orWhereIn('parent_id', $uniqueSplitChilds)
					->update(Array('track_id'=>$lastInrtId));

				$sql = '
					INSERT INTO 
						'.$this->trackDetailsTable.' (code, track_id) 
					SELECT 
						primary_id, '.$lastInrtId.' 
					FROM 
						'.$esealTable.' 
					WHERE 
						track_id = '.$lastInrtId;
				DB::insert($sql);
			}
		}

		$status = 1;
		$message = 'Track info updated successfully';
		DB::commit();        
		Log::info(__LINE__);
	}catch(Exception $e){
		DB::rollback();        
		$message = $e->getMessage();
		Log::info($e->getMessage());
	}
	$endTime = $this->getTime();
	Log::info(__FUNCTION__.' Finishes execution in '.($endTime-$startTime));
Log::info(['Status'=>$status, 'Message' => $message]);
	return json_encode(['Status'=>$status, 'Message' =>'Server: '.$message]);      
}


  private  function getChildForGivenCode($code, $esealTable){
	array_push($this->_childCodes, $code);
	$rest = DB::select('SELECT 
				es1.primary_id 
			FROM 
				'.$esealTable.' es 
			INNER JOIN 
				'.$esealTable.' es1 
			ON 
				es.primary_id = es1.parent_id and es.primary_id= "'.$code.'"');
	if(count($rest)>0){
	  foreach($rest as $key=>$resultCode){
		$this->getChildForGivenCode($resultCode->primary_id, $esealTable);
	  }
	}
  }

public function GetInventory(){
	$startTime = $this->getTime();    
	try{

		$status =0;
		$message = 'Failed to get invetory data';
		$pid = 0;
		$srcLocationId = Input::get('locationId');
		$fromDate = Input::get('fromDate');
		$toDate =  Input::get('toDate');
		$pid = Input::get('pid');

		Log::info(__FUNCTION__.' : '.print_r(Input::get(),true));

		$locationObj = new Locations\Locations();
		$mfgId = $locationObj->getMfgIdForLocationId($srcLocationId);
		$esealTable = 'eseal_'.$mfgId;
		$pnameArray = Array();
		if(!empty($mfgId)){
		//////////////FOR Stock Availability
			//Log::info('x1');
		try{
			$SA = DB::table($esealTable.' as es')->join($this->trackHistoryTable, 'es.track_id', '=', $this->trackHistoryTable.'.track_id')
			    ->join('products as pr','pr.product_id','=','es.pid')
				->select(DB::raw('case when multiPack=0 then count(*) else sum(pkg_qty) end as cnt'), 'pid')
				->where(Array($this->trackHistoryTable.'.src_loc_id'=>$srcLocationId, 
					$this->trackHistoryTable.'.dest_loc_id'=> 0,
				    'es.level_id'=>0,'es.is_active'=>1
					));
				//->where($esealTable.'.pid','!=',0);
			if($fromDate!='')
				$SA->where($this->trackHistoryTable.'.update_time', '>=', $fromDate);
			if($toDate!='')
				$SA->where($this->trackHistoryTable.'.update_time', '<=', $toDate);
			if(!empty($pid))
				$SA->where('es.pid', $pid);

			$SA = $SA->groupBy('es.pid')->get();          
			//Log::info('xx2');
		}catch(PDOException $e){
			Log::info($e->getMessage());
			throw new Exception('SQlError');
		}


		////////////FOR STOCK SOLD
		try{
			//Log::info('xx3');
			$SS = DB::table($esealTable.' as es')->join($this->trackHistoryTable,'es.track_id', '=', $this->trackHistoryTable.'.track_id')
			        ->join('products as pr','pr.product_id','=','es.pid')
					->select(DB::raw('case when multiPack=0 then count(*) else sum(pkg_qty) end as cnt'), 'pid')
					->where($this->trackHistoryTable.'.src_loc_id', $srcLocationId) 
					->where($this->trackHistoryTable.'.dest_loc_id', '!=', 0)
					->where('es.level_id', 0)
                    ->where('es.is_active',1);
			if($fromDate!='')
				$SS->where($this->trackHistoryTable.'.update_time', '>=', $fromDate);
			if($toDate!='')
				$SS->where($this->trackHistoryTable.'.update_time', '<=', $toDate);
			if(!empty($pid))
				$SS->where('es.pid', $pid);

			$SS = $SS->groupBy('es.pid')->get();          
		}catch(PDOException $e){
			Log::info('xx4');
			Log::info($e->getMessage());
			throw new Exception('SQlError');
		}

		////////////FOR STOCK TO RECEIVE
		try{
			$SR = DB::table($esealTable.' as es')->join($this->trackHistoryTable,'es.track_id', '=', $this->trackHistoryTable.'.track_id')
			    ->join('products as pr','pr.product_id','=','es.pid')
				->select(DB::raw('case when multiPack=0 then count(*) else sum(pkg_qty) end as cnt'), 'pid')
				->where($this->trackHistoryTable.'.src_loc_id', '!=', 0) 
				->where($this->trackHistoryTable.'.dest_loc_id', $srcLocationId)
				->where('es.level_id', 0)
                ->where('es.is_active',1);
		  if($fromDate!='')
			$SR->where($this->trackHistoryTable.'.update_time', '>=', $fromDate);
		  if($toDate!='')
			$SR->where($this->trackHistoryTable.'.update_time', '<=', $toDate);
		  if(!empty($pid))
			$SR->where('es.pid', $pid);

		  $SR = $SR->groupBy('es.pid')->get();          
		}catch(PDOException $e){
		  Log::info($e->getMessage());
		  throw new Exception('SQlError');
		}

		$inventory = Array();
		if(count($SA) || count($SS) || count($SR)){
		  if(count($SA)){
			foreach($SA as $key=>$value){
			  $inventory[$value->pid]['SA'] = $value->cnt;
			}
		  }
		  if(count($SS)){
			foreach($SS as $key=>$value){
			  $inventory[$value->pid]['SS'] = $value->cnt;
			}
		  }
		  if(count($SR)){
			foreach($SR as $key=>$value){
			  $inventory[$value->pid]['SR'] = $value->cnt;
			}
		  }
		  $prodArra = Array();
		  //Log::info(print_r($inventory,true));
		  foreach($inventory as $key=>$value){
			if(!isset($value['SS']))
			  $value['SS'] = '';
			if(!isset($value['SR']))
			  $value['SR'] = '';
			if(!isset($value['SA']))
			  $value['SA'] = '';

			  $pname = '';
			  if(array_key_exists($key, $pnameArray)){
				  $pname = $pnameArray[$key];
			  }else{
				  $product = new Products\Products();
				  $pname = $product->getNameFromId($key);
				  $pmatcode = $product->getMatCodeFromId($key);
				  $pnameArray[$key] = $pname;           
			  }
			$prodArra[] = Array('pname'=>$pname, 'mat_code' => $pmatcode, 'StockAvailable'=> (int)$value['SA'], 'StockMoved' => (int)$value['SS'], 'StockToReceive' => $value['SR']);

		  }
		  $status =1;
		  $message = 'Inventory Data Found';
		}else{
		  throw new Exception('No data for inventory');
		}

	  }else{
		throw new Exception('Unable to get customer id for given location id');
	  }

	}catch(Exception $e){
	  $prodArra = Array();
	  Log::info($e->getMessage());
	  $message = $e->getMessage();
	}
	$endTime = $this->getTime();
	Log::info(__FUNCTION__.' Finishes execution in '.($endTime-$startTime));

	return json_encode(Array('Status'=>$status, 'Message' =>'Server:' .$message, 'reportData' => (array)$prodArra));
  }


public function GetIventoryByLocationId()
{
	$startTime = $this->getTime();    
	try
	{
		$status = 0;
		$message = '';
		$locationId = Input::get('locationId');
		$fromDate = Input::get('fromDate');
		$toDate =  Input::get('toDate');
		$levels = Input::get('levels');
		$searchInChild = Input::get('searchInChild');
		$pid = Input::get('pid');
		$group_id = Input::get('ProductGroupId');
		$is_redeemed = Input::get('is_redeemed');
    
		Log::info(__FUNCTION__.' : '.print_r(Input::get(),true));
		if(empty($locationId) || !is_numeric($locationId))
		{
			throw new Exception('Pass valid numeric location Id');
		}
		$locationObj = new Locations\Locations();
		$mfgId = $locationObj->getMfgIdForLocationId($locationId);
		
		if($searchInChild)
		{
			$childIds = Array();
			$childIds = $locationObj->getAllChildIdForParentId($locationId);
			if($childIds)
			{
				array_push($childIds, $locationId);
			}
			$parentId = $locationObj->getParentIdForLocationId($locationId);
			$childIds1 = Array();
			if($parentId)
			{
				$childIds1 = $locationObj->getAllChildIdForParentId($parentId);
				if($childIds1)
				{
					array_push($childIds1, $parentId);
				}
			}
			$childsIDs = array_merge($childIds, $childIds1);
			$childsIDs = array_unique($childsIDs);
			if(count($childsIDs))
			{
				$locationId = implode(',',$childsIDs);	
			}
		
		}
		$esealTable = 'eseal_'.$mfgId;
		$date = date('Y-m-d H:i:s');
		


$sql = 'SELECT CASE WHEN e.pid=0 THEN "Hetrogenious Item" WHEN e.pid=-1 THEN "Pallet" ELSE p.name END AS Name,
		  p. material_code AS MaterialCode,(SELECT erp_code FROM locations l WHERE l.location_id=t.src_loc_id) AS Location,e.primary_id AS eSealId, 
		  CASE when e.parent_id=0 then "" else e.parent_id end AS Parent,e. level_id AS PackingLevel,CAST((
					 SELECT CASE 
					         WHEN COUNT(e1.primary_id) = 0 
								   THEN 
									   case when multiPack=0 then 1 ELSE sum(e.pkg_qty) END
							 ELSE 
							           case when multiPack=0 then COUNT(e1.primary_id)  ELSE sum(e1.pkg_qty) END 
							END
					 FROM '.$esealTable.' e1
					 WHERE e1.parent_id=e.primary_id) AS UNSIGNED) AS Quantity,p.group_id as ProductGroupId,e. mfg_date AS MfgDate,e.storage_location as StorageLocation,t.update_time AS UpdateTime,
		  e.batch_no AS BatchNumber,IFNULL(e.po_number,"") PONumber, IFNULL(p.mrp,"") MRP
		  FROM '.$esealTable.' e INNER JOIN products p ON e.pid=p.product_id INNER JOIN 
		  track_history t ON t.track_id=e.track_id WHERE  t.src_loc_id in('.$locationId.')
		  and t.dest_loc_id = 0 and e.level_id in('.$levels.') and e.is_active=1';

		
			
		if($fromDate!='')
			$sql .= ' and t.update_time >= "'.$fromDate.'"  ';
		if($toDate!='')
			$sql .= ' and t.update_time <= "'.$toDate.'" ';
		if($pid)
			$sql .= ' and e.pid='.$pid;
		if($group_id)
			$sql .= ' and p.group_id='.$group_id;
		if($is_redeemed)
			$sql .= ' and e.is_redeemed=1';

		$sql .= ' group by e.primary_id order by Location';
		//echo "<pre/>";print_r($sql);exit;
		//Log::info($sql);
		try
		{
			$result = DB::select($sql); 
			//Log::info(DB::select($sql)->toSql()); 
		}
		catch(PDOException $e)
		{
			Log::info($e->getMessage());
			throw new Exception('SQlError while fetching data');
		}
		if(count($result))
		{
			//$productArray[] = $result;
			///Log::info(print_r($productArray,true));
			$status = 1;
			$message = 'Data found';
		}
		else
		{
			throw new Exception('Data not found');
		}
		//Log::error(print_r($productArray,true));
	}
	catch(Exception $e)
	{
		$status =0;
		$result = Array();
		Log::info($e->getMessage());
		$message = $e->getMessage();
	}
	$endTime = $this->getTime();
	Log::info(__FUNCTION__.' Finishes execution in '.($endTime-$startTime));
	Log::info(Array('Status'=>$status, 'Message' => $message, 'esealData' => $result));
	return json_encode(Array('Status'=>$status, 'Message' =>'Server: '.$message, 'esealData' => $result));
}  

public function GetEsealDataByLocationIdOld(){
	$startTime = $this->getTime();    
	
	try{
		$status = 0;
		$message = '';
		$locationId = Input::get('locationId');
		$fromDate = Input::get('fromDate');
		$toDate =  Input::get('toDate');
		$levels = Input::get('levels');
		$po_number = Input::get('po_number');
		$loadComponents = Input::get('loadComponents');

		Log::info(__FUNCTION__.' : '.print_r(Input::get(),true));
		if(empty($locationId) || !is_numeric($locationId)){
			throw new Exception('Pass valid numeric location Id');
		}
		$locationObj = new Locations\Locations();
		$mfgId = $locationObj->getMfgIdForLocationId($locationId);
		
		$childIds = Array();
		$childIds = $locationObj->getAllChildIdForParentId($locationId);
		if($childIds){
			array_push($childIds, $locationId);
		}
		$parentId = $locationObj->getParentIdForLocationId($locationId);
		$childIds1 = Array();
		if($parentId){
			$childIds1 = $locationObj->getAllChildIdForParentId($parentId);
			if($childIds1){
				array_push($childIds1, $parentId);
			}
		}
		$childsIDs = array_merge($childIds, $childIds1);
		$childsIDs = array_unique($childsIDs);
		if(count($childsIDs)){
			$locationId = implode(',',$childsIDs);	
		}
		

		$esealTable = 'eseal_'.$mfgId;
		$date = date('Y-m-d H:i:s');
		$splitLevels = explode(',', $levels);
		
		if($loadComponents){
			array_push($splitLevels,8);
		  }
		$productArray = Array();
		foreach($splitLevels as $levelNo){
			

			if($levelNo==0){
				$sql = '
				SELECT 
					count(*) qty, pid, primary_id, parent_id, po_number,inspection_result,
					IF(mrp,NULL,"") mrp, 
					batch_no batch, 
					IF((select value from '.$this->attributeMappingTable.' e3 where e3.attribute_map_id=es.attribute_map_id and e3.attribute_id=2 and e3.location_id in ('.$locationId.')),NULL,"") expdate, 
					level_id, eth.update_time, "" warehouse_id, "" pallete_id, "" tp_id, "" zonespace,
					IFNULL((select material_code from products pp where pp.product_id=es.pid),"") material_code 
				FROM 
					'.$esealTable.' es , '.$this->trackHistoryTable.' eth 
				WHERE 
					es.track_id=eth.track_id and eth.src_loc_id in ('.$locationId.') and eth.dest_loc_id = 0 and es.level_id = 0 
				';

			}
			if($levelNo > 0 && $levelNo !=8){
				$sql = '
				SELECT 
					count(*) qty, es.pid,es.po_number,es.inspection_result, es.parent_id as child, (select parent_id from '.$esealTable.' e1 where es.parent_id=e1.primary_id) as parent, es.level_id, 
					IF(mrp,NULL,"") mrp,
					batch_no batch, 
					IF((select value from '.$this->attributeMappingTable.' e3 where e3.attribute_map_id=es.attribute_map_id and e3.attribute_id=2 and e3.location_id in('.$locationId.') ),NULL,"") expdate, 
					eth.update_time, "" warehouse_id, "" pallete_id, "" tp_id, "" zonespace,
					IFNULL((select material_code from products pp where pp.product_id=es.pid),"") material_code 
				FROM 
				'.$esealTable.' es, '.$this->trackHistoryTable.' eth 
				WHERE 
					eth.track_id=es.track_id and eth.src_loc_id in ('.$locationId.') and eth.dest_loc_id = 0 and 
					es.parent_id in (SELECT primary_id from '.$esealTable.' es1, track_history th1 where  th1.track_id=es1.track_id and th1.src_loc_id in ('.$locationId.') and 
					th1.dest_loc_id = 0 and es1.level_id='.$levelNo.') ';
				//return $sql;
			}
			if($levelNo == 8){
				$sql = '
				SELECT 
					1 qty, es.pid, es.primary_id,es.parent_id as parent, es.po_number,es.inspection_result,
					IF(es.mrp,NULL,"") mrp, 
					es.batch_no batch, 
					IF((select value from '.$this->attributeMappingTable.' e3 where e3.attribute_map_id=es.attribute_map_id and e3.attribute_id=2 and e3.location_id in ('.$locationId.')),NULL,"") expdate, 
					es.level_id, eth.update_time, "" warehouse_id, "" pallete_id, "" tp_id, "" zonespace,
					IFNULL((select material_code from products pp where pp.product_id=es.pid),"") material_code 
				FROM 
					'.$esealTable.' es, '.$this->trackHistoryTable.' eth ,product_components pc
				WHERE 
					es.pid=pc.component_id and es.track_id=eth.track_id and eth.src_loc_id in ('.$locationId.') and eth.dest_loc_id = 0 and es.level_id = 0 
				and pc.product_id in(select pid from '.$esealTable.' es1,product_components pc1,track_history th1 where es1.pid=pc1.product_id and th1.track_id=es1.track_id and th1.src_loc_id in('.$locationId.') and th1.dest_loc_id=0 group by es1.pid)';
			}

			if($fromDate!='')
				$sql .= ' and eth.update_time >= "'.$fromDate.'"  ';
			if($toDate!='')
				$sql .= ' and eth.update_time <= "'.$toDate.'" ';
			if(!empty($po_number) && $levelNo !=8)
				$sql .= ' and es.po_number='.$po_number;
			
			if($levelNo==0){
				$sql .= ' GROUP BY pid, primary_id';
			}
			if($levelNo>0 && $levelNo != 8){
				$sql .= ' GROUP BY es.pid, child';   
			}
			
			if($levelNo==8){
				$sql .= ' GROUP BY es.primary_id';
			}
			//Log::info($sql);
			try{
				$result = DB::select($sql); 
				//Log::info(DB::select($sql)->toSql()); 
			}catch(PDOException $e){
				Log::info($e->getMessage());
				throw new Exception('SQlError while fetching data');
			}
			if(count($result)){
				$pnameArray = Array();
				foreach($result as $key=>$value){
					$pname = '';
					$product = new Products\Products();
					$prodInfo = $product->getProductInfo($value->pid);
					$group_id = $prodInfo[0]->group_id; 
					if(array_key_exists($value->pid, $pnameArray)){
						$pname = $pnameArray[$value->pid];
					}else{
						
						if($prodInfo){
						$pnameArray[$value->pid] = $prodInfo[0]->name;
						$pname = $prodInfo[0]->name;
						}
						else{
							throw new Exception('Data not found for product');
						}          
					}
					if(empty($pname))
						$pname = '';

					if(isset($value->parent_id) && $value->parent_id == 'unknown'){
						$value->parent_id = '';    
					}
					if(isset($value->parent) && $value->parent == 'unknown'){
						$value->parent = '';    
					}

					if($value->qty){
						if($levelNo == 0){
						   $eseal_id = $value->primary_id;
						}
						if($levelNo >0 && $levelNo !=8){
						   $eseal_id = $value->child;
						}
						if($levelNo == 8){
						   $eseal_id = $value->primary_id;
						}

						$attrs = DB::table('bind_history as bh')
									   ->join('attribute_mapping as am','am.attribute_map_id','=','bh.attribute_map_id')
									   ->join('attributes as attr','attr.attribute_id','=','am.attribute_id')
									   ->join('attributes_groups as ag','ag.attribute_group_id','=','attr.attribute_group_id')
									   ->where(['bh.eseal_id'=>$eseal_id,'ag.name'=>'Print'])
									   ->get(['attr.attribute_code as code','am.value']);


						if($levelNo==0){
							if($value->batch=='unknown')
								$value->batch = '';

							$productArray[] =  Array(
							'name'=>$pname, 'qty'=>intval($value->qty),'pid'=>(int)$value->pid,'group_id'=>(int)$group_id,'id'=> $value->primary_id,'po_number'=>$value->po_number,
							'lid' => $value->parent_id, 'lvl' => intval($levelNo), 'utime'=> $value->update_time, 
							'mrp' => $value->mrp, 'batch' =>$value->batch, 'zpace' => $value->zonespace, 
							'exp' => $value->expdate, 'plt' => $value->pallete_id, 'wid' => $value->warehouse_id, 'tp' => $value->tp_id,
							'matcode' => (string)$value->material_code,'print_attributes'=>$attrs
							);  
						}
						if($levelNo>0 && $levelNo !=8){
							/*$mfg_id = $this->roleAccess->getMfgIdByToken(Input::get('access_token'));
							$qty = DB::select('select count(*) as qty from eseal_'.$mfg_id.' where parent_id='.$value->child);*/
							if($value->batch=='unknown')
								$value->batch = '';
							
							$productArray[] =  Array(
							'name'=>$pname, 'qty'=>intval($value->qty),'pid'=>(int)$value->pid,'group_id'=>(int)$group_id,'id'=>$value->child,'po_number'=>$value->po_number,
							'lid' => $value->parent, 'lvl' => intval($levelNo), 'utime'=> $value->update_time, 
							'mrp' => $value->mrp, 'batch' =>$value->batch, 'zpace' => $value->zonespace, 
							'exp' => $value->expdate, 'plt' => $value->pallete_id, 'wid' => $value->warehouse_id, 'tp' => $value->tp_id,
							'matcode' => (string)$value->material_code,'print_attributes'=>$attrs
							);     
						}
						if($levelNo == 8){
							if($value->batch=='unknown')
								$value->batch = '';

							$productArray[] =  Array(
							'name'=>$pname, 'qty'=>intval($value->qty),'pid'=>(int)$value->pid,'group_id'=>(int)$group_id,'id'=> $value->primary_id,'po_number'=>$value->po_number,
							'lid' => $value->parent, 'lvl' => 0, 'utime'=> $value->update_time, 
							'mrp' => $value->mrp, 'batch' =>$value->batch, 'zpace' => $value->zonespace, 
							'exp' => $value->expdate, 'plt' => $value->pallete_id, 'wid' => $value->warehouse_id, 'tp' => $value->tp_id,
							'matcode' => (string)$value->material_code,'print_attributes'=>$attrs
							);  

						}
					}
				}
				///Log::info(print_r($productArray,true));
				$status = 1;
				$message = 'Data found';
			}
			
		}
	//Log::error(print_r($productArray,true));
	}catch(Exception $e){
		$status =0;
		$productArray = Array();
		Log::info($e->getMessage());
		$message = $e->getMessage();
	}
	$endTime = $this->getTime();
	Log::info(__FUNCTION__.' Finishes execution in '.($endTime-$startTime));
   Log::info(Array('Status'=>$status, 'Message' => $message, 'esealData' => $productArray));
	return json_encode(Array('Status'=>$status, 'Message' =>'Server: '.$message, 'esealData' => $productArray));
}



public function GetEsealDataByLocationId()
{
	$startTime = $this->getTime();    
	try
	{
		$status = 0;
		$message = '';
		$locationId = Input::get('locationId');
		$fromDate = Input::get('fromDate');
		$toDate =  Input::get('toDate');
		$checkSyncTime = Input::get('isSyncTime');
		$levels = Input::get('levels');
		$po_number = Input::get('po_number');
		$delivery_no = Input::get('delivery_no');
		//$Range = Input::get('Range');
		$Range = 1500;
		$RangeCheck = $Range+1; //echo $Range.'---'.$RangeCheck;exit;
		$loadComponents = Input::get('loadComponents');
		$loadAccessories = Input::get('loadAccessories');	
		$excludePrimary = Input::get('excludePrimary');	
		$isDataAvailable = 0;
		$productTypes[] = 8003;
		$trackArray = array();
		$pids ='';
		$productArray = Array();
		$i= 0;

		Log::info(__FUNCTION__.' : '.print_r(Input::get(),true));
		if(empty($locationId) || !is_numeric($locationId))
		{
			throw new Exception('Pass valid numeric location Id');
		}
		if(empty($levels) && $levels != 0)
		{
			throw new Exception('Parameters missing');
		}
		$locationObj = new Locations\Locations();
		$mfgId = $locationObj->getMfgIdForLocationId($locationId);
		
		$childIds = Array();
		$childIds = $locationObj->getAllChildIdForParentId($locationId);
		if($childIds)
		{
			array_push($childIds, $locationId);
		}
		$parentId = $locationObj->getParentIdForLocationId($locationId);
		$childIds1 = Array();
		if($parentId)
		{
			$childIds1 = $locationObj->getAllChildIdForParentId($parentId);
			if($childIds1)
			{
				array_push($childIds1, $parentId);
			}
		}
		$childsIDs = array_merge($childIds, $childIds1);
		$childsIDs = array_unique($childsIDs);
		if(count($childsIDs))
		{
			$locationId = implode(',',$childsIDs);	
		}
		$esealTable = 'eseal_'.$mfgId;        

		$splitLevels= array();

		/*$esealTable = 'eseal_'.$mfgId;
		$date = date('Y-m-d H:i:s');
		$splitLevels = explode(',', $levels);*/
		//Log::info($locationId);
		array_push($splitLevels,'levels'); 


		if($loadComponents)
			$productTypes[] = 8001;

		if($loadAccessories)
			$productTypes[] = 8004;


		//Log::info('Product Types:-');
		//Log::info($productTypes);
		$productType = implode(',',$productTypes); 

		if($po_number){
			$pid = DB::table($esealTable)->where('po_number',$po_number)->groupBy('pid')->pluck('pid');
			if(empty($pid)){
				throw new Exception('The given PO number doesnt exist');
			}
		}




		if($delivery_no)
		{
			$products =  new Products\Products;
			$pArray = $products->getProductsFromDelivery(Input::get('access_token'),$delivery_no);
			if($pArray)
			{
				$pids = implode(',',$pArray);
				//Log::info('Products:-'.$pids);
			}
			else
			{
				throw new Exception('There are no materials configured in delivery no');
			}
		}

		if($checkSyncTime)
			$column = 'sync_time';
		else
			$column = 'update_time';
		
		$sql = 'select th.track_id from track_history th join eseal_'.$mfgId.' es on es.track_id=th.track_id where src_loc_id in('.$locationId.') and dest_loc_id=0 and es.level_id in('.$levels.')';
		 
		 if($fromDate!='')
			$sql .= ' and '.$column.' >= "'.$fromDate.'" ';
		 if($toDate!='')
			$sql .= ' and '.$column.' <= "'.$toDate.'" ';                  
		 if($excludePrimary)
				$sql .=' and es.parent_id =0';
		 
		  
                 $sql .= ' order by th.track_id asc';


                 if(!empty($Range))
			{
				$sql .=' limit '.$RangeCheck;
			}



		 $result = DB::select($sql);
		 if(empty($result)){
			throw new Exception('Data not-found');
		 }
		 foreach ($result as $res){
			$trackArray[] = $res->track_id;
		 }

                 $lastTrackId = end($trackArray);
		 $lastSyncTime = DB::table($this->trackHistoryTable)->where('track_id',$lastTrackId)->pluck($column);
		 $lastTrackIds = DB::table($this->trackHistoryTable)
		                    ->where($column,$lastSyncTime)
		                    ->whereIn('src_loc_id',explode(',',$locationId))
		                    ->where('dest_loc_id',0)
		                    ->lists('track_id');
		 $trackArray = array_merge($lastTrackIds,$trackArray);

		 $trackArray =  array_unique($trackArray);
		 //Log::info($trackArray);
		 $trackIds  = implode(',',$trackArray);                 
			
				$sql = '
				SELECT 
					p.material_code AS matcode,					
					cast(p.group_id as UNSIGNED) as group_id,
					(select update_time from track_history th where e.track_id=th.track_id) as utime,
                    (select sync_time from track_history th where e.track_id=th.track_id) as stime,
					CASE WHEN e.pid=0 THEN "Hetrogenious Item" WHEN e.pid=-1 THEN "Pallet" ELSE p.name END AS name,
					IFNULL((select value as exp from attribute_mapping am where e.attribute_map_id=am.attribute_map_id and 
                    attribute_name="date_of_exp"),"") exp,
					IFNULL((select value as exp_valid from attribute_mapping am where e.attribute_map_id=am.attribute_map_id and attribute_name="exp_valid"),"0") exp_valid,
					"" zpace,					
					"" plt,
					"" wid,
					"" tp,
					cast(cast(e.pkg_qty as UNSIGNED) as char) as pkg_qty,
					cast(e.pid as UNSIGNED) as pid,
					cast(e.primary_id as char) as id, 
					p.multiPack,
					CASE when e.parent_id=0 then "" else e.parent_id end AS lid,
					cast(e.level_id AS UNSIGNED) as lvl,
					cast((SELECT  CASE when COUNT(e1.primary_id) = 0 then 1 else COUNT(e1.primary_id) end  
						FROM '.$esealTable.' e1
						WHERE e1.parent_id=e.primary_id) as UNSIGNED) AS qty,
					CASE when e.batch_no="unknown" then "" else e.batch_no end AS batch,
					IFNULL(po_number,"") po_number, IF(p.mrp, 0.00,"") mrp,
					concat("{",fn_Get_print_attributes(e.primary_id),"}") AS print_attributes,
					e.is_active
				FROM '.$esealTable.' e
				INNER JOIN products p ON e.pid=p.product_id				
				WHERE
				p.product_type_id in('.$productType.') and e.track_id in('.$trackIds.') and e.level_id in('.$levels.')'; 
		 
			if(!empty($pids))
				$sql .=' and e.pid in('.$pids.')';						
			
			if($po_number)
				$sql .=' and e.pid='.$pid;
			
			if($excludePrimary)
				$sql .=' and e.parent_id =0';

			$sql .=' group by e.pid,e.primary_id order by e.track_id';

			
			
			//Log::info($sql);
			try
			{
				$result = DB::select($sql); 
				//Log::info($result);				
				//Log::info('TOTAL COUNT :-'.count($result));
				$totResult = count($result); 
				if(!empty($Range))
				{
					if($totResult >= $Range)
					{
						$isDataAvailable = 1;  
					}
										
				}
				//echo "<pre/>";print_r($sql);exit;
			}
			catch(PDOException $e)
			{
				Log::info($e->getMessage());
				throw new Exception('SQlError while fetching data');
			}
			//echo "<pre/>";print_r($result);exit;
			if(count($result))
			{
			$productArray = $result;
			}

		
		//echo "<pre/>";print_r($productArray);exit;		
		///Log::info(print_r($productArray,true));
		$status = 1;
		$message = 'Data found';
			
		//Log::error(print_r($productArray,true));
	}
	catch(Exception $e)
	{
		$status =0;
		Log::info($e->getMessage());
		$message = $e->getMessage();
	}
	$endTime = $this->getTime();
	Log::info(__FUNCTION__.' Finishes execution in '.($endTime-$startTime));
	Log::info(['Status'=>$status, 'Message' =>'Server: '.$message, 'esealData' => $productArray]);
	return json_encode(['Status'=>$status, 'Message' =>'Server: '.$message, 'isDataAvailable'=>$isDataAvailable,'esealData' => $productArray],JSON_UNESCAPED_SLASHES);
}

public function GetEsealIdsByLocationId()
{
	$startTime = $this->getTime();    
	try
	{
		$status = 0;
		$message = '';
		$locationId = Input::get('locationId');
		$fromDate = Input::get('fromDate');
		$toDate =  Input::get('toDate');
		$levels = Input::get('levels');
		$material_code = Input::get('material_code');
		$isDataAvailable = 0;
		$trackArray = array();
		$productArray = Array();
		$i= 0;

		Log::info(__FUNCTION__.' : '.print_r(Input::get(),true));
		if(empty($locationId) || !is_numeric($locationId))
		{
			throw new Exception('Pass valid numeric location Id');
		}
		if(empty($levels) && $levels != 0)
		{
			throw new Exception('Parameters missing');
		}
		$locationObj = new Locations\Locations();
		$mfgId = $locationObj->getMfgIdForLocationId($locationId);
		
		$childIds = Array();
		$childIds = $locationObj->getAllChildIdForParentId($locationId);
		if($childIds)
		{
			array_push($childIds, $locationId);
		}
		$parentId = $locationObj->getParentIdForLocationId($locationId);
		$childIds1 = Array();
		if($parentId)
		{
			$childIds1 = $locationObj->getAllChildIdForParentId($parentId);
			if($childIds1)
			{
				array_push($childIds1, $parentId);
			}
		}
		$childsIDs = array_merge($childIds, $childIds1);
		$childsIDs = array_unique($childsIDs);
		if(count($childsIDs))
		{
			$locationId = implode(',',$childsIDs);	
		}
		$esealTable = 'eseal_'.$mfgId;        

		$splitLevels= array();

		/*$esealTable = 'eseal_'.$mfgId;
		$date = date('Y-m-d H:i:s');
		$splitLevels = explode(',', $levels);*/
		//Log::info($locationId);
		array_push($splitLevels,'levels'); 
		if(empty($material_code))
			throw new Exception('Material Code not passed');
		
			$pid = DB::table('products')->where('material_code',$material_code)->pluck('product_id');
			if(empty($pid)){
				throw new Exception('The given material code doesnt exist');
			}
		

		$sql = 'select th.track_id from track_history th join eseal_'.$mfgId.' es on es.track_id=th.track_id where src_loc_id in('.$locationId.') and dest_loc_id=0 and es.level_id in('.$levels.') and es.pid='.$pid;
		 
		 if($fromDate!='')
			$sql .= ' and update_time >= "'.$fromDate.'" ';
		 if($toDate!='')
			$sql .= ' and update_time <= "'.$toDate.'" ';                  
		 
			$sql .=' and is_redeemed=0';
		 $result = DB::select($sql);
		 if(empty($result)){
			throw new Exception('Data not-found');
		 }
		 foreach ($result as $res){
			$trackArray[] = $res->track_id;
		 }
		 $trackArray =  array_unique($trackArray);
		 //Log::info($trackArray);
		 $trackIds  = implode(',',$trackArray);                 
			
				$sql = '
				SELECT 
					e.primary_id as id, 
					cast(e.level_id AS UNSIGNED) as lvl
					FROM '.$esealTable.' e where
					e.pid='.$pid.' and e.track_id in('.$trackIds.') and e.level_id in('.$levels.') and is_redeemed=0'; 
		 
				$sql .=' group by e.pid,e.primary_id order by e.track_id';

			
			
			//Log::info($sql);
			try
			{
				$result = DB::select($sql); 
				//Log::info($result);				
				
				//echo "<pre/>";print_r($sql);exit;
			}
			catch(PDOException $e)
			{
				Log::info($e->getMessage());
				throw new Exception('SQlError while fetching data');
			}
			//echo "<pre/>";print_r($result);exit;
			if(count($result))
			{
			$productArray = $result;
			}

		
		//echo "<pre/>";print_r($productArray);exit;		
		///Log::info(print_r($productArray,true));
		$status = 1;
		$message = 'Data found';
			
		//Log::error(print_r($productArray,true));
	}
	catch(Exception $e)
	{
		$status =0;
		Log::info($e->getMessage());
		$message = $e->getMessage();
	}
	$endTime = $this->getTime();
	Log::info(__FUNCTION__.' Finishes execution in '.($endTime-$startTime));
	//Log::info(['Status'=>$status, 'Message' =>'Server: '.$message, 'esealData' => $productArray]);
	return json_encode(['Status'=>$status, 'Message' =>'Server: '.$message,'esealData' => $productArray]);
}






public function GetEsealComponentsByLocationId()
{
	$startTime = $this->getTime();    
	try
	{
		$status = 0;
		$message = '';
		$locationId = Input::get('locationId');
		$fromDate = Input::get('fromDate');
		$toDate =  Input::get('toDate');
		$levels = Input::get('levels');
		$po_number = Input::get('po_number');
		$delivery_no = Input::get('delivery_no');
		$Range = Input::get('Range');
		$RangeCheck = $Range+1; 
		$pids ='';
		$productArray = Array();
		$i= 0;

		Log::info(__FUNCTION__.' : '.print_r(Input::get(),true));
		if(empty($locationId) || !is_numeric($locationId))
		{
			throw new Exception('Pass valid numeric location Id');
		}
		if(empty($levels) && $levels != 0)
		{
			throw new Exception('Parameters missing');
		}
		$locationObj = new Locations\Locations();
		$mfgId = $locationObj->getMfgIdForLocationId($locationId);
		
		$childIds = Array();
		$childIds = $locationObj->getAllChildIdForParentId($locationId);
		if($childIds)
		{
			array_push($childIds, $locationId);
		}
		$parentId = $locationObj->getParentIdForLocationId($locationId);
		$childIds1 = Array();
		if($parentId)
		{
			$childIds1 = $locationObj->getAllChildIdForParentId($parentId);
			if($childIds1)
			{
				array_push($childIds1, $parentId);
			}
		}
		$childsIDs = array_merge($childIds, $childIds1);
		$childsIDs = array_unique($childsIDs);
		if(count($childsIDs))
		{
			$locationId = implode(',',$childsIDs);	
		}
		$esealTable = 'eseal_'.$mfgId;        

		$splitLevels= array();

		
		if($delivery_no)
		{
			$products =  new Products\Products;
			$pArray = $products->getProductsFromDelivery(Input::get('access_token'),$delivery_no);
			if($pArray)
			{
				$pids = implode(',',$pArray);
				Log::info('Products:-'.$pids);
			}
			else
			{
				throw new Exception('There are no materials configured in delivery no');
			}
		}
		if($po_number)
			$pid = DB::table($esealTable)->where('po_number',$po_number)->groupBy('pid')->pluck('pid');

		
			array_push($splitLevels,8);
		
		foreach($splitLevels as $levelNo)
		{	
				$sql = '
					SELECT p. material_code AS matcode,cast(p.group_id as UNSIGNED) as group_id,
					CASE WHEN e.pid=0 THEN "Hetrogenious Item" WHEN e.pid=-1 THEN "Pallet" ELSE p.name END AS name,"" zpace, "" exp, "" plt, "" wid, "" tp,cast(e.pid as UNSIGNED) as pid,e.primary_id as id, CASE when e.parent_id=0 then "" else e.parent_id end AS lid,cast(e.level_id AS UNSIGNED) as lvl,cast((SELECT  CASE when COUNT(e1.primary_id) = 0 then 1 else COUNT(e1.primary_id) end  FROM '.$esealTable.' e1
						WHERE e1.parent_id=e.primary_id) as UNSIGNED) AS qty,eth.update_time AS utime,
						CASE when e.batch_no="unknown" then "" else e.batch_no end AS batch,
						IF(e. po_number, NULL,"") po_number, IF(p.mrp, 0.00,"") mrp,""  print_attributes
				FROM '.$esealTable.' e
				INNER JOIN products p ON e.pid=p.product_id
				INNER JOIN track_history eth ON eth.track_id=e.track_id
				INNER JOIN product_components pc ON pc.component_id=e.pid
				WHERE
					eth.src_loc_id in('.$locationId.') and eth.dest_loc_id = 0 and e.level_id=0';
			
			 if($fromDate!='')
				$sql .= ' and eth.update_time >= "'.$fromDate.'"  ';
			 if($toDate!='')
				$sql .= ' and eth.update_time <= "'.$toDate.'" ';
			/* if(!empty($po_number) && $levelNo == 'levels')
				$sql .= ' and e.po_number='.$po_number;*/
			 if(!empty($po_number) && $levelNo == 8)
				$sql .= ' and e.pid in(select component_id from product_components where product_id='.$pid.')';
			 /*if(!empty($pids) && $levelNo =='levels' )
				$sql .= ' and e.pid in('.$pids.')';	*/		

			/* if($levelNo == 'levels')
				$sql .=' group by e.pid,e.primary_id';*/
			 if($levelNo == 8)
				$sql .=' group by e.primary_id';

			 $sql .= ' Order By eth.update_time ASC ';

			$isDataAvailable = 0;
			if(!empty($Range))
			{
				$sql .=' limit '.$RangeCheck;
			}
			//echo "<pre/>";print_r($sql);exit;
			//Log::info($sql);
			try
			{
				$result = DB::select($sql); 				
				$totResult = count($result); 
				if(!empty($Range))
				{
					if($totResult>$Range)
					{
						$isDataAvailable = 1;  
						array_pop($result);              		
					}
										
				}
				//echo "<pre/>";print_r($sql);exit;
			}
			catch(PDOException $e)
			{
				Log::info($e->getMessage());
				throw new Exception('SQlError while fetching data');
			}
			//echo "<pre/>";print_r($result);exit;
			if(count($result))
			{
			
				if($i > 0)
				{          
					$productArray = array_merge($result,$productArray);             
				}
				else
				{ 
					$productArray = $result;
				}
				//if(empty($loadComponents))
				$i ++;		
			}

		}
		//echo "<pre/>";print_r($productArray);exit;		
		///Log::info(print_r($productArray,true));
		$status = 1;
		$message = 'Data found';
			
		//Log::error(print_r($productArray,true));
	}
	catch(Exception $e)
	{
		$status =0;
		$productArray = Array();
		Log::info($e->getMessage());
		$message = $e->getMessage();
	}
	$endTime = $this->getTime();
	Log::info(__FUNCTION__.' Finishes execution in '.($endTime-$startTime));
	//Log::info(['Status'=>$status, 'Message' =>'Server: '.$message, 'esealData' => $productArray]);
	return json_encode(['Status'=>$status, 'Message' =>'Server: '.$message, 'isDataAvailable'=>$isDataAvailable,'esealData' => $productArray],JSON_UNESCAPED_SLASHES);
}
  


  public function GetEsealDataByPO()
  {
	$startTime = $this->getTime();    
	try
	{
		$status = 0;
		$message = '';
		$locationId =  Input::get('locationId');
		$levels = Input::get('levels');
		$po_number = Input::get('po_number');
		$loadComponents = Input::get('loadComponents');
		$loadAccessories = Input::get('loadAccessories');
		$loadPrintAttributes = Input::get('loadPrintAttributes');
		$id = trim(Input::get('id'));
		$qty ='';
		$pids ='';
		$productArray = Array();
		
		Log::info(__FUNCTION__.' : '.print_r(Input::get(),true));
		if(empty($locationId) || !is_numeric($locationId))
		{
			throw new Exception('Pass valid numeric location Id');
		}
		if(empty($levels) && $levels != 0)
		{
			throw new Exception('Parameters missing');
		}
		$locationObj = new Locations\Locations();
		$mfgId = $locationObj->getMfgIdForLocationId($locationId);
		
		$childIds = Array();
		$childIds = $locationObj->getAllChildIdForParentId($locationId);
		if($childIds)
		{
			array_push($childIds, $locationId);
		}
		$parentId = $locationObj->getParentIdForLocationId($locationId);
		$childIds1 = Array();
		if($parentId)
		{
			$childIds1 = $locationObj->getAllChildIdForParentId($parentId);
			if($childIds1)
			{
				array_push($childIds1, $parentId);
			}
		}
		$childsIDs = array_merge($childIds, $childIds1);
		$childsIDs = array_unique($childsIDs);
		if(count($childsIDs)){
			$locationId = implode(',',$childsIDs);	
		}
		$esealTable = 'eseal_'.$mfgId;
		$splitLevels= array();
		if($po_number){
			$pid = DB::table($esealTable)->where('po_number',$po_number)->groupBy('pid')->pluck('pid');
		}
		else{
			if($id){
			 $po_number = DB::table($esealTable)->where('primary_id',$id)->pluck('po_number');
			 
			 if(!$po_number)
			   throw new Exception('The passed ID doesnt have a po number');

			 $pid = DB::table($esealTable)->where('po_number',$po_number)->groupBy('pid')->pluck('pid');  		
			}

		}

		if(empty($pid))
			throw new Exception('There is no data for this PO');

		$qty = DB::table($esealTable)->where(['po_number'=>$po_number,'level_id'=>0])->count();

		if(!empty($loadComponents) || !empty($loadAccessories))
		{
			array_push($splitLevels,8);
			$pids =  DB::table('product_components')->where('product_id',$pid)->lists('component_id');
			if(empty($pids)){
				throw new Exception('There are no components configured for the material in the PO');
			}
			$pids =  implode(',',$pids);
		}
		array_push($splitLevels,'levels');  
		
		
foreach($splitLevels as $levelNo){
	
	if($levelNo == 'levels'){
		//Log::info('check1');
			$sql = '
				SELECT 
				e.pid ,
				p.name,				
				p.material_code as matcode,				
				p.group_id';
			if($loadPrintAttributes)
			{               
				$sql.=' ,concat("{",fn_Get_print_attributes(e.primary_id),"}") AS print_attributes';
			}
			else
			{
				$sql.=' ,"" AS print_attributes';
			}
			$sql.=' FROM '.$esealTable.' e
				INNER JOIN products p ON e.pid=p.product_id
				WHERE
				   p.product_type_id=8003 and e.level_id in('.$levels.')  and e.po_number='.$po_number.' and e.parent_id =0 group by e.pid';
			
			
	}		

			if($levelNo == 8){
//Log::info('check2');
			$sql = '
				SELECT 
				e.pid ,
				p.name,				
				p.material_code as matcode,
				p.group_id';
			if($loadPrintAttributes)
			{               
				$sql.=' ,concat("{",fn_Get_print_attributes(e.primary_id),"}") AS print_attributes';
			}
			else
			{
				$sql.=' ,"" AS print_attributes';
			}
			$sql.=' FROM '.$esealTable.' e
				INNER JOIN products p ON e.pid=p.product_id
				INNER JOIN track_history eth ON eth.track_id=e.track_id				
				WHERE
					eth.src_loc_id in ('.$locationId.') and dest_loc_id=0 and e.pid in ('.$pids.') and e.level_id=0 ';
			if(!empty($loadComponents) && !empty($loadAccessories))
			{
				/*if($loadComponents)
					$sql.=' and p.group_id in (1) ';
				if($loadAccessories)
					$sql.=' or p.group_id in (2) ';*/
			}
			else
			{
				if($loadComponents)
					$sql.=' and p.group_id in (1) ';
				if($loadAccessories)
					$sql.=' and p.group_id in (2) ';
			}
			$sql.=' and e.parent_id=0 group by e.pid';	

			}

			
			if(!empty($po_number) && $levelNo == 'levels'){
		//Log::info('check3');
				
				$sql1 = '
				SELECT 
				e.primary_id as id,				
				cast(e.level_id AS UNSIGNED) as level_id  FROM '.$esealTable.' e
				INNER JOIN track_history eth ON eth.track_id=e.track_id
				WHERE
				eth.src_loc_id in('.$locationId.') and eth.dest_loc_id = 0 and e.level_id in('.$levels.') and e.po_number='.$po_number.' and e.parent_id =0';

				

				$result1 =  DB::select($sql1);
			}
			
			try{
				$result = DB::select($sql); 
				//Log::info($result);
				//Log::info('Level:-'.$levelNo);
			}catch(PDOException $e){
				Log::info($e->getMessage());
				throw new Exception('SQlError while fetching data');
			}
			//Log::info('Level:-'.$levelNo);
			if(count($result)){
			//Log::info('Level:-'.$levelNo);
			  if($levelNo == 'levels'){
			//	Log::info($levelNo);
							$productArray[] = ['prod_info'=>$result[0],'eseal_data'=>$result1];
			
				   }
			if($levelNo == 8){
//Log::info('check4');
				$i=0;
				foreach($result as $res){
					
				$sql1 = '
				SELECT 
				e.primary_id as id,
				cast(e.level_id AS UNSIGNED) as level_id FROM '.$esealTable.' e
				INNER JOIN track_history eth ON eth.track_id=e.track_id
				WHERE
				eth.src_loc_id in('.$locationId.') and eth.dest_loc_id = 0 and e.level_id=0 and e.pid='.$res->pid.' and e.parent_id =0';		   
				
			

				$result1 = DB::select($sql1);
				$productArray[] = ['prod_info'=>$result[$i],'eseal_data'=>$result1];    
				$i++;
				}
			}
							
}

}
				
				///Log::info(print_r($productArray,true));
				$status = 1;
				$message = 'Data found';
			
	//Log::error(print_r($productArray,true));
	}catch(Exception $e){
		$status =0;
		$productArray = Array();
		Log::info($e->getMessage());
		$message = $e->getMessage();
	}
	$endTime = $this->getTime();
	Log::info(__FUNCTION__.' Finishes execution in '.($endTime-$startTime));
	Log::info(['Status'=>$status, 'Message' =>'Server: '.$message, 'esealData' => $productArray,'po_number'=>$po_number,'po_qty'=>$qty]);
	return json_encode(['Status'=>$status, 'Message' =>'Server: '.$message, 'esealData' => $productArray,'po_number'=>$po_number,'po_qty'=>$qty]);
}


public function RetryEseals()
	{
 $startTime = $this->getTime();
		try
		{
			$status = 0;
			$message = '';
			$locationId = Input::get('locationId');
			$ids = Input::get('ids');
			$productArray = array();
			$trackArray = array();
			$inHouseInventory = false;
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
			array_push($childsIDs, $locationId);
			$childsIDs = array_unique($childsIDs);
			if (count($childsIDs))
			{
				$locationId = implode(',', $childsIDs);
			}
			$esealTable = 'eseal_'.$mfgId;

			$location_type_id = DB::table('location_types')
			                        ->whereIn('location_type_name',['Plant','Warehouse','Supplier'])
			                        ->where('manufacturer_id',$mfgId)->lists('location_type_id');

			$location_type_ids = implode(',',$location_type_id);


			$sql = 'select th.track_id,th.src_loc_id from track_history th join eseal_'.$mfgId.' es on es.track_id=th.track_id join locations on locations.location_id=th.src_loc_id and locations.location_type_id in ('.$location_type_ids.') and dest_loc_id=0 and es.primary_id in('.$ids.')';
		 
		 $result = DB::select($sql);
		 if(empty($result)){
	     //Log::info('Location Ids:-' .$locationId);

	             $result =   DB::table('track_history as th')
                                 ->join($esealTable.' as es','es.track_id','=','th.track_id')
                                 ->whereIn('th.src_loc_id',explode(',',$locationId))
                                 ->where('th.dest_loc_id',0)
                                 ->whereIn('primary_id',explode(',',$ids))
                                 ->get(['th.track_id','th.src_loc_id']);

         //Log::info($result);
                                 
              if(empty($result))
			      throw new Exception('Data not-found');

			  $inHouseInventory = true;
		 }
		 
         $currentLocationId = $this->roleAccess->getLocIdByToken(Input::get('access_token'));

		 $plant_location_id = $result[0]->src_loc_id;

		 //if($currentLocationId == $plant_location_id)
		 //	$inHouseInventory = true;

		 if(in_array($plant_location_id,$childsIDs))
            $inHouseInventory = true;		 	

		 foreach ($result as $res){
			$trackArray[] = $res->track_id;
		 }
		 $trackArray =  array_unique($trackArray);
		 //Log::info($trackArray);
		 $trackIds  = implode(',',$trackArray);  

					$sql = 'SELECT 
p. material_code AS matcode,
cast(p.group_id as UNSIGNED) as group_id,
CASE WHEN e.pid=0 THEN "Hetrogenious Item" WHEN e.pid=-1 THEN "Pallet" ELSE p.name END AS name,
/*"" zpace,
 "" exp,
 "" plt,
 "" wid,
 "" tp,*/
cast(e.pkg_qty as char) as pkg_qty,
cast(e.pid as UNSIGNED) as pid,
cast(e.primary_id as char) as id, 
CASE when e.parent_id=0 then "" else e.parent_id end AS lid,
cast(e.level_id AS UNSIGNED) as lvl,cast((
SELECT  CASE when COUNT(e1.primary_id) = 0 then 1 else COUNT(e1.primary_id) end  
FROM ' . $esealTable . ' e1
WHERE e1.parent_id=e.primary_id) as UNSIGNED) AS qty,
p.multiPack,
(select update_time from track_history th where e.track_id=th.track_id) as utime,
CASE when e.batch_no="unknown" then "" else e.batch_no end AS batch,
e. po_number, IF(p.mrp, NULL,"") mrp,
concat("{",fn_Get_print_attributes(e.primary_id),"}") AS print_attributes
FROM ' . $esealTable . ' e
INNER JOIN products p ON e.pid=p.product_id
INNER JOIN master_lookup ml ON ml.value= p.product_type_id
WHERE
p.product_type_id=8003 and  e.track_id in ('.$trackIds.') and e.primary_id in('.$ids.')';
			   
			//	Log::info($sql);
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

            	throw new Exception('The stock is in some other location');
                
                foreach($result as $ids){
                	$transitIds[] = $ids->id;
                } 
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
             
                      

			///Log::info(print_r($productArray,true));
			

			//Log::error(print_r($productArray,true));
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



  public function wmsLookup(){
	$startTime = $this->getTime();    
	try{
		$status = 0;
		$message = 'Data not found';
		$locationId = (int)Input::get('locationId');
		$fromDate = Input::get('fromDate');
		$toDate =  Input::get('toDate');
		$levels = Input::get('levels');

		Log::info(__FUNCTION__.' : '.print_r(Input::get(),true));
		if(empty($locationId) || !is_numeric($locationId)){
			throw new Exception('Pass valid numeric location Id');
		}
		$locationObj = new Locations\Locations();
		$mfgId = $locationObj->getMfgIdForLocationId($locationId);
		
		$childIds = Array();
		$childIds = $locationObj->getAllChildIdForParentId($locationId);
		if($childIds){
			array_push($childIds, $locationId);
		}
		$parentId = $locationObj->getParentIdForLocationId($locationId);
		$childIds1 = Array();
		if($parentId){
			$childIds1 = $locationObj->getAllChildIdForParentId($parentId);
			if($childIds1){
				array_push($childIds1, $parentId);
			}
		}
		$childsIDs = array_merge($childIds, $childIds1);
		$childsIDs = array_unique($childsIDs);
		if(count($childsIDs)){
			$locationId = implode(',',$childsIDs);	
		}
		

		$esealTable = 'eseal_'.$mfgId;
		$date = date('Y-m-d H:i:s');
		$splitLevels = explode(',', $levels);
		$productArray = Array();		
		foreach($splitLevels as $levelNo){
			if($levelNo==0 || $levelNo==1){
	
				$sql = '
				SELECT 
					1 qty, pid, primary_id, parent_id, po_number,bin_location,
					IF(es.mrp,NULL,"") mrp,
					pkg_qty weight,
					batch_no batch, 
					IF((select value from '.$this->attributeMappingTable.' e3 where e3.attribute_map_id=es.attribute_map_id and e3.attribute_id=2 and e3.location_id in ('.$locationId.')),NULL,"") expdate, 
					level_id, eth.update_time, 
					IFNULL((select material_code from products pp where pp.product_id=es.pid),"") material_code 
				FROM 
					'.$esealTable.' es , '.$this->trackHistoryTable.' eth
				WHERE 

					es.track_id=eth.track_id and eth.src_loc_id in ('.$locationId.') and eth.dest_loc_id = 0 and es.level_id = 0 
				';

			}
			if($levelNo>1){
				$sql = '
				SELECT 
					count(*) qty, es.pid,es.po_number,es.primary_id as primary_id, es.parent_id as parent_id, es.level_id, 
					IF(es.mrp,NULL,"") mrp,
					es.batch_no batch, es.bin_location,es.pkg_qty weight,
					IF((select value from '.$this->attributeMappingTable.' e3 where e3.attribute_map_id=es.attribute_map_id and e3.attribute_id=2 and e3.location_id in('.$locationId.') ),NULL,"") expdate, 
					eth.update_time, 
					IFNULL((select material_code from products pp where pp.product_id=es.pid),"") material_code 
				FROM 
				'.$esealTable.' es, '.$this->trackHistoryTable.' eth ,'.$esealTable.' es1
				WHERE 
					es.primary_id=es1.parent_id and
					eth.track_id=es.track_id and eth.src_loc_id in ('.$locationId.') and eth.dest_loc_id = 0 and 
					es.primary_id in (SELECT primary_id from '.$esealTable.' es1, track_history th1 where  th1.track_id=es1.track_id and th1.src_loc_id in ('.$locationId.') and 
					th1.dest_loc_id = 0 and es1.level_id='.$levelNo.')';
			}
			if($fromDate!='')
				$sql .= ' and eth.update_time >= "'.$fromDate.'"  ';
			if($toDate!='')
				$sql .= ' and eth.update_time <= "'.$toDate.'" ';

			if($levelNo==0){
				$sql .= ' GROUP BY pid, primary_id';
			}
			if($levelNo>0){
				$sql .= ' GROUP BY es.pid, primary_id';   
			}
			//Log::info($sql);
			try{
				$result = DB::select($sql); 
				//Log::info(DB::select($sql)->toSql()); 
			}catch(PDOException $e){
				Log::info($e->getMessage());
				throw new Exception('SQlError while fetching data');
			}
			if(count($result)){
				$pnameArray = Array();
				foreach($result as $key=>$value){
					$pname = '';					
					$product = new Products\Products();
					$prodInfo = $product->getProductInfo($value->pid);
					$group_id = $prodInfo[0]->group_id; 
					if(array_key_exists($value->pid, $pnameArray)){
						$pname = $pnameArray[$value->pid];
					}else{
						
						if($prodInfo){
						$pnameArray[$value->pid] = $prodInfo[0]->name;
						$pname = $prodInfo[0]->name;
						}
						else{
							throw new Exception('Data not found for product');
						}          
					}
					$attributes = $product->getProductSearchAttributes($value->pid,$value->primary_id,$locationId);
					if(empty($pname))
						$pname = '';

					if(isset($value->parent_id) && $value->parent_id == 0){
						$value->parent_id = '';    
					}
					
					if($value->qty){
						
						$eseal_id = $value->primary_id;
						
						/*$attrs = DB::table('bind_history as bh')
									   ->join('attribute_mapping as am','am.attribute_map_id','=','bh.attribute_map_id')
									   ->join('attributes as attr','attr.attribute_id','=','am.attribute_id')
									   ->join('attributes_groups as ag','ag.attribute_group_id','=','attr.attribute_group_id')
									   ->where(['bh.eseal_id'=>$eseal_id,'ag.name'=>'Print'])
									   ->get(['attr.attribute_code as code','am.value']);*/
						if($value->bin_location == null)
							$entity_id = '';
						else
							$entity_id = DB::table('wms_entities')->where(['entity_location'=>$value->bin_location,'org_id'=>$mfgId])->pluck('id');
						
						if($levelNo==0){
							if($value->batch=='unknown')
								$value->batch = '';

							$productArray[] =  Array(
							'name'=>$pname, 'qty'=>intval($value->qty),'pid'=>(int)$value->pid,'group_id'=>(int)$group_id,'id'=> $value->primary_id,'document_number'=>$value->po_number,
							'lid' => $value->parent_id, 'lvl' => intval($levelNo), 'utime'=> $value->update_time, 
							'mrp' => $value->mrp, 'batch' =>$value->batch,  
							'exp' => $value->expdate,'weight'=>$value->weight,'bin_location'=>$value->bin_location,'entity_id'=>$entity_id,
							'matcode' => (string)$value->material_code,'total_attributes'=>$attributes
							);  
						}
						if($levelNo>0){
							if($value->bin_location=='NULL')
								$value->bin_location='';
							/*$mfg_id = $this->roleAccess->getMfgIdByToken(Input::get('access_token'));
							$qty = DB::select('select count(*) as qty from eseal_'.$mfg_id.' where parent_id='.$value->child);*/
							if($value->batch=='unknown')
								$value->batch = '';
							
							$productArray[] =  Array(
							'name'=>$pname, 'qty'=>intval($value->qty),'pid'=>(int)$value->pid,'group_id'=>(int)$group_id,'id'=> $value->primary_id,'document_number'=>$value->po_number,
							'lid' => $value->parent_id, 'lvl' => intval($levelNo), 'utime'=> $value->update_time, 
							'mrp' => $value->mrp, 'batch' =>$value->batch,  
							'exp' => $value->expdate,'weight'=>$value->weight,'bin_location'=>$value->bin_location,'entity_id'=>$entity_id,
							'matcode' => (string)$value->material_code,'total_attributes'=>$attributes
							);     
						}
					}
				}
				///Log::info(print_r($productArray,true));
				$status = 1;
				$message = 'Data found';
			}
			
		}
	//Log::error(print_r($productArray,true));
	}catch(Exception $e){
		$status =0;
		$productArray = Array();
		Log::info($e->getMessage());
		$message = $e->getMessage();
	}
	$endTime = $this->getTime();
	Log::info(__FUNCTION__.' Finishes execution in '.($endTime-$startTime));
   Log::info(Array('Status'=>$status, 'Message' => $message, 'esealData' => $productArray));
	return json_encode(Array('Status'=>$status, 'Message' => $message, 'esealData' => $productArray));
}



public function wmsLookupModified(){
	$startTime = $this->getTime();    
	try{
		$status = 0;
		$message = 'Data not found';
		$locationId = (int)Input::get('locationId');
		$fromDate = Input::get('fromDate');
		$toDate =  Input::get('toDate');
		$levels = Input::get('levels');

		Log::info(__FUNCTION__.' : '.print_r(Input::get(),true));
		if(empty($locationId) || !is_numeric($locationId)){
			throw new Exception('Pass valid numeric location Id');
		}
		$locationObj = new Locations\Locations();
		$mfgId = $locationObj->getMfgIdForLocationId($locationId);
		
		$childIds = Array();
		$childIds = $locationObj->getAllChildIdForParentId($locationId);
		if($childIds){
			array_push($childIds, $locationId);
		}
		$parentId = $locationObj->getParentIdForLocationId($locationId);
		$childIds1 = Array();
		if($parentId){
			$childIds1 = $locationObj->getAllChildIdForParentId($parentId);
			if($childIds1){
				array_push($childIds1, $parentId);
			}
		}
		$childsIDs = array_merge($childIds, $childIds1);
		$childsIDs = array_unique($childsIDs);
		if(count($childsIDs)){
			$locationId = implode(',',$childsIDs);	
		}
		

		 $sql = 'select track_id from track_history where src_loc_id in('.$locationId.') and dest_loc_id=0';
		 
		 if($fromDate!='')
			$sql .= ' and update_time >= "'.$fromDate.'"  ';
		 if($toDate!='')
			$sql .= ' and update_time <= "'.$toDate.'" ';                  
		 
		   
		 $result = DB::select($sql);
		 if(empty($result)){
			throw new Exception('Data not-found');
		 }
		 foreach ($result as $res){
			$trackArray[] = $res->track_id;
		 }
		 //Log::info($trackArray);
		 $trackIds  = implode(',',$trackArray); 

		$esealTable = 'eseal_'.$mfgId;
		$date = date('Y-m-d H:i:s');
		$splitLevels = explode(',', $levels);
		$productArray = Array();		
		foreach($splitLevels as $levelNo){
			if($levelNo==0 || $levelNo==1){
	
				$sql = '
				SELECT 
					1 qty, pid, primary_id, parent_id, po_number,bin_location,
					IF(es.mrp,NULL,"") mrp,
					pkg_qty weight,
					batch_no batch, 
					IF((select value from '.$this->attributeMappingTable.' e3 where e3.attribute_map_id=es.attribute_map_id and e3.attribute_id=2 and e3.location_id in ('.$locationId.')),NULL,"") expdate, 
					level_id, (select update_time from track_history th where es.track_id=th.track_id) as update_time, 
					IFNULL((select material_code from products pp where pp.product_id=es.pid),"") material_code 
				FROM 
					'.$esealTable.' es
				WHERE 

					es.track_id in ('.$trackIds.') and es.level_id = 0 
				';

			}
			if($levelNo>1){
				$sql = '
				SELECT 
					count(*) qty, es.pid,es.po_number,es.primary_id as primary_id, es.parent_id as parent_id, es.level_id, 
					IF(es.mrp,NULL,"") mrp,
					es.batch_no batch, es.bin_location,es.pkg_qty weight,
					IF((select value from '.$this->attributeMappingTable.' e3 where e3.attribute_map_id=es.attribute_map_id and e3.attribute_id=2 and e3.location_id in('.$locationId.') ),NULL,"") expdate, 
					(select update_time from track_history th where es.track_id=th.track_id) as update_time,  
					IFNULL((select material_code from products pp where pp.product_id=es.pid),"") material_code 
				FROM 
				'.$esealTable.' es,'.$esealTable.' es1
				WHERE 
					es.primary_id=es1.parent_id and
					es.track_id in('.$trackIds.') and 
					es.primary_id in (SELECT primary_id from '.$esealTable.' es1 where es1.track_id in ('.$trackIds.') and 
					es1.level_id='.$levelNo.')';
			}
			

			if($levelNo==0){
				$sql .= ' GROUP BY pid, primary_id';
			}
			if($levelNo>0){
				$sql .= ' GROUP BY es.pid, primary_id';   
			}
			//Log::info($sql);
			try{
				$result = DB::select($sql); 
				//Log::info(DB::select($sql)->toSql()); 
			}catch(PDOException $e){
				Log::info($e->getMessage());
				throw new Exception('SQlError while fetching data');
			}
			if(count($result)){
				$pnameArray = Array();
				foreach($result as $key=>$value){
					$pname = '';					
					$product = new Products\Products();
					$prodInfo = $product->getProductInfo($value->pid);
					$group_id = $prodInfo[0]->group_id; 
					if(array_key_exists($value->pid, $pnameArray)){
						$pname = $pnameArray[$value->pid];
					}else{
						
						if($prodInfo){
						$pnameArray[$value->pid] = $prodInfo[0]->name;
						$pname = $prodInfo[0]->name;
						}
						else{
							throw new Exception('Data not found for product');
						}          
					}
					$attributes = $product->getProductSearchAttributes($value->pid,$value->primary_id,$locationId);
					if(empty($pname))
						$pname = '';

					if(isset($value->parent_id) && $value->parent_id == 0){
						$value->parent_id = '';    
					}
					
					if($value->qty){
						
						$eseal_id = $value->primary_id;
						
						/*$attrs = DB::table('bind_history as bh')
									   ->join('attribute_mapping as am','am.attribute_map_id','=','bh.attribute_map_id')
									   ->join('attributes as attr','attr.attribute_id','=','am.attribute_id')
									   ->join('attributes_groups as ag','ag.attribute_group_id','=','attr.attribute_group_id')
									   ->where(['bh.eseal_id'=>$eseal_id,'ag.name'=>'Print'])
									   ->get(['attr.attribute_code as code','am.value']);*/
						if($value->bin_location == null)
							$entity_id = '';
						else
							$entity_id = DB::table('wms_entities')->where('entity_location',$value->bin_location)->pluck('id');
						
						if($levelNo==0){
							if($value->batch=='unknown')
								$value->batch = '';

							$productArray[] =  Array(
							'name'=>$pname, 'qty'=>intval($value->qty),'pid'=>(int)$value->pid,'group_id'=>(int)$group_id,'id'=> $value->primary_id,'document_number'=>$value->po_number,
							'lid' => $value->parent_id, 'lvl' => intval($levelNo), 'utime'=> $value->update_time, 
							'mrp' => $value->mrp, 'batch' =>$value->batch,  
							'exp' => $value->expdate,'weight'=>$value->weight,'bin_location'=>$value->bin_location,'entity_id'=>$entity_id,
							'matcode' => (string)$value->material_code,'total_attributes'=>$attributes
							);  
						}
						if($levelNo>0){
							if($value->bin_location=='NULL')
								$value->bin_location='';
							/*$mfg_id = $this->roleAccess->getMfgIdByToken(Input::get('access_token'));
							$qty = DB::select('select count(*) as qty from eseal_'.$mfg_id.' where parent_id='.$value->child);*/
							if($value->batch=='unknown')
								$value->batch = '';
							
							$productArray[] =  Array(
							'name'=>$pname, 'qty'=>intval($value->qty),'pid'=>(int)$value->pid,'group_id'=>(int)$group_id,'id'=> $value->primary_id,'document_number'=>$value->po_number,
							'lid' => $value->parent_id, 'lvl' => intval($levelNo), 'utime'=> $value->update_time, 
							'mrp' => $value->mrp, 'batch' =>$value->batch,  
							'exp' => $value->expdate,'weight'=>$value->weight,'bin_location'=>$value->bin_location,'entity_id'=>$entity_id,
							'matcode' => (string)$value->material_code,'total_attributes'=>$attributes
							);     
						}
					}
				}
				///Log::info(print_r($productArray,true));
				$status = 1;
				$message = 'Data found';
			}
			
		}
	//Log::error(print_r($productArray,true));
	}catch(Exception $e){
		$status =0;
		$productArray = Array();
		Log::info($e->getMessage());
		$message = $e->getMessage();
	}
	$endTime = $this->getTime();
	Log::info(__FUNCTION__.' Finishes execution in '.($endTime-$startTime));
   Log::info(Array('Status'=>$status, 'Message' => $message, 'esealData' => $productArray));
	return json_encode(Array('Status'=>$status, 'Message' => $message, 'esealData' => $productArray));
}


  public function SyncEseal(){
	$startTime = $this->getTime();
	 try{
	  $status = 0;
	  $message = 'Failed to sync';

	  $attributeMapId = 0;
	  $ids = trim(Input::get('ids'));
	  $pid = trim(Input::get('pid'));
	  $locationId = trim(Input::get('srcLocationId'));
	  $attributeMapId = trim(Input::get('attribute_map_id'));


	  $parent = trim(Input::get('parent'));

	  $codes = trim(Input::get('codes'));
	  $destLocationId = trim(Input::get('destLocationId'));
	  //$transitionTime = trim(Input::get('transitionTime'));
	  //$transitionTime = $this->getDate();
	  $transitionId = trim(Input::get('transitionId'));
	  $internalTransfer = trim(Input::get('internalTransfer'));

	  Log::info(__FUNCTION__.' : '.print_r(Input::get(),true));
      Input::merge(array('transitionTime' => $this->getDate()));


	  DB::beginTransaction();
	  $request = Request::create('scoapi/BindEseals1', 'POST');
	  $bindResult = Route::dispatch($request)->getContent();
	  Log::info(print_r($bindResult, true));
	  $res1 = json_decode($bindResult);
	  if($res1->Status == 1){
		$request = Request::create('scoapi/MapEseals', 'POST');
		$mapResult = Route::dispatch($request)->getContent();
	  }else{

	   if($res1->Status == 2){
	   	$status  = 1;
		$message = $res1->Message;
         goto commit;
	   }
	   else{
		throw new Exception('Error in binding data');
	   }
	  }
	  Log::info(print_r($mapResult, true));
	  $res2 = json_decode($mapResult);
	  if($res2->Status){
		$request = Request::create('scoapi/UpdateTracking', 'POST');
		$trackResult = Route::dispatch($request)->getContent();
	  }else{
		//throw new Exception('Error in while mapping'); 
		throw new Exception($res2->Message); 
	  }
	  Log::info(print_r($trackResult, true));
	  $res3 = json_decode($trackResult);
	  if(!$res3->Status){
	   throw new Exception('Error in track update');  
	  }else{		
		$status  = 1;
		$message = 'Binding, Mapping & Track Info Updated Succesfully';
	  }

     commit:     
     DB::commit();
	}catch(Exception $e){
	  DB::rollback();
	  $message = $e->getMessage();
	}
	$endTime = $this->getTime();
	Log::info(__FUNCTION__.' Finishes execution in '.($endTime-$startTime));
	return json_encode(Array('Status'=>$status, 'Message' => $message));
  }

public function BindWithTrackupdate()
{
	$startTime = $this->getTime();
	try
	{
		$status = 0;
		$message = 'Failed to sync';

		$attributeMapId = 0;
		$ids = trim(Input::get('ids'));
		$pid = trim(Input::get('pid'));
		$locationId = trim(Input::get('srcLocationId'));
		$attributeMapId = trim(Input::get('attribute_map_id'));
		$parent = trim(Input::get('parent'));

		
		
		$codes = trim(Input::get('codes'));
		$destLocationId = trim(Input::get('destLocationId'));
		//$transitionTime = trim(Input::get('transitionTime'));
		//$transitionTime = $this->getDate();
		$transitionId = trim(Input::get('transitionId'));
		$internalTransfer = trim(Input::get('internalTransfer'));
		Log::info(__FUNCTION__.' : '.print_r(Input::get(),true));
	    Input::merge(array('transitionTime' => $this->getDate()));
                
		DB::beginTransaction();
		$request = Request::create('scoapi/BindEseals1', 'POST');
		$bindResult = Route::dispatch($request)->getContent();
		Log::info(print_r($bindResult, true));
		$res1 = json_decode($bindResult);
		if($res1->Status)
		{
			$request = Request::create('scoapi/UpdateTracking', 'POST');
			$trackResult = Route::dispatch($request)->getContent();
		}
		else
		{
			throw new Exception('Binding Error: '. $res1->Message);
		}
		Log::info(print_r($trackResult, true));
		$res3 = json_decode($trackResult);
		if(!$res3->Status)
		{
			throw new Exception('Track Update Error: ' . $res3->Message);  
		}
		else
		{
			DB::commit();
			$status  = 1;
			$message = 'Binding & Track Info Updated Succesfully';
		}
	}
	catch(Exception $e)
	{
		DB::rollback();
		$message = $e->getMessage();
	}
	$endTime = $this->getTime();
	Log::info(__FUNCTION__.' Finishes execution in '.($endTime-$startTime));
	return json_encode(Array('Status'=>$status, 'Message' =>'Server:' .$message));
}


public function BindNVendorRecieve(){
	$startTime = $this->getTime();
	 try{
	  $status = 0;
	  $message = 'Failed to sync';

	  $attributeMapId = 0;
	  $ids = trim(Input::get('ids'));
	  $pid = trim(Input::get('pid'));
	  $locationId = trim(Input::get('srcLocationId'));
	  $attributeMapId = trim(Input::get('attribute_map_id'));


	  $parent = trim(Input::get('parent'));

	  $codes = trim(Input::get('codes'));
	  $destLocationId = trim(Input::get('destLocationId'));
	  $transitionTime = trim(Input::get('transitionTime'));
	  //$transitionTime = $this->getDate();
	  $transitionId = trim(Input::get('transitionId'));

		Log::info(__FUNCTION__.' : '.print_r(Input::get(),true));

	  DB::beginTransaction();
	  $request = Request::create('scoapi/BindEseals', 'POST');
	  $bindResult = Route::dispatch($request)->getContent();
	  Log::info(print_r($bindResult, true));
	  $res1 = json_decode($bindResult);
	  if($res1->Status){
		$request = Request::create('scoapi/vendorReceive', 'POST');
		$trackResult = Route::dispatch($request)->getContent();
	  }else{
		throw new Exception('Binding Error: '. $res1->Message);
	  }
	  Log::info(print_r($trackResult, true));
	  $res3 = json_decode($trackResult);
	  if(!$res3->Status){
	   throw new Exception('Track Update Error: ' . $res3->Message);  
	  }else{
		DB::commit();
		$status  = 1;
		$message = 'Binding & Track Info Updated Succesfully';
	  }


	}catch(Exception $e){
	  DB::rollback();
	  $message = $e->getMessage();
	}
	$endTime = $this->getTime();
	Log::info(__FUNCTION__.' Finishes execution in '.($endTime-$startTime));
	return json_encode(Array('Status'=>$status, 'Message' =>'Server:' .$message));
}



public function vendorReceive(){
	$startTime = $this->getTime();

	try{
		$status = 0;
		$message = 'Failed to update track info';

		$destLocationId = 0;
		$codes = trim(Input::get('codes'));
		$srcLocationId = trim(Input::get('srcLocationId'));
		$destLocationId = trim(Input::get('destLocationId'));
		$transitionTime = trim(Input::get('transitionTime'));
		$transitionId = trim(Input::get('transitionId'));
		
		Log::info(__FUNCTION__.' : '.print_r(Input::get(),true));

		DB::beginTransaction();
		if(!is_numeric($srcLocationId) || !is_numeric($destLocationId) || !is_numeric($transitionId)){
		  throw new Exception('Some of the parameter is not numeric');
		}
		if(!is_string($codes) || empty($codes)){
		  throw new Exception('Codes should not be empty and must be string'); 
		}

		$locationObj = new Locations\Locations();
		$mfgId = $locationObj->getMfgIdForLocationId($srcLocationId);
		$esealTable = 'eseal_'.$mfgId;
		$transactionObj = new TransactionMaster\TransactionMaster();
		$transactionDetails = $transactionObj->getTransactionDetails($mfgId, $transitionId);
		//Log::info(print_r($transactionDetails, true));
		if($transactionDetails){
		  $srcLocationAction = $transactionDetails[0]->srcLoc_action;
		  $destLocationAction = $transactionDetails[0]->dstLoc_action;
		  $inTransitAction = $transactionDetails[0]->intrn_action;
		}
	
		$splitChilds = explode(',', $codes);
		$uniqueSplitChilds = array_unique($splitChilds);
		$joinChilds = '\''.implode('\',\'', $uniqueSplitChilds).'\'';
		$childCnt = count($uniqueSplitChilds);

		//Log::info('$childCnt'.$childCnt);
		//Log::info('SrcLocAction : ' . $srcLocationAction.' , DestLocAction: '. $destLocationAction.', inTransitAction: '. $inTransitAction);
		
		$trakHistoryObj = new TrackHistory\TrackHistory();
		//Log::info(var_dump($trakHistoryObj));
		

		//Log::info(__LINE__);
		if($srcLocationAction==0 && $destLocationAction==1 && $inTransitAction==0){
			try{
				$codesCnt = DB::table($esealTable)
					->whereIn('primary_id', $uniqueSplitChilds)->count();
				if($codesCnt != $childCnt){
					throw new Exception('Codes count not matching');
				}
		
				$codesTrack = DB::table($esealTable.' as eseal')
					->join($this->trackHistoryTable.' as th', 'eseal.track_id','=','th.track_id')
					->whereIn('eseal.primary_id', $uniqueSplitChilds)
					->select('th.src_loc_id','th.dest_loc_id')
					->get();
		
				$srcLocationId = $destLocationId;
				$destLocationId = 0;		

				if(count($codesTrack)){
					/*foreach($codesTrack as $trackRow){
						if($trackRow->src_loc_id!=$srcLocationId || $trackRow->dest_loc_id>0){*/
							throw new Exception('Some of the codes are already received or issues');
					/*	}
					}*/
				}
				$lastInrtId = $trakHistoryObj->insertTrack(
					$srcLocationId, $destLocationId, $transitionId, $transitionTime
					);
		  
				DB::table($esealTable)
					->whereIn('primary_id', $uniqueSplitChilds)
					->orWhereIn('parent_id', $uniqueSplitChilds)
					->update(Array('track_id'=>$lastInrtId));

				$sql = '
					INSERT INTO 
						'.$this->trackDetailsTable.' (code, track_id) 
					SELECT 
						primary_id, '.$lastInrtId.' 
					FROM 
						'.$esealTable.' 
					WHERE 
						track_id = '.$lastInrtId;
				DB::insert($sql);
			}catch(PDOException $e){
				Log::info($e->getMessage());
				throw new Exception('SQlError during packing');
			}
		}
		$status = 1;
		$message = 'Track info updated successfully';
		DB::commit();        
		Log::info(__LINE__);
	}catch(Exception $e){
		DB::rollback();        
		$message = $e->getMessage();
		Log::info($e->getMessage());
	}
	$endTime = $this->getTime();
	Log::info(__FUNCTION__.' Finishes execution in '.($endTime-$startTime));

	return json_encode(Array('Status'=>$status, 'Message' =>'Server: '.$message));      
}


public function MapWithTrackupdate(){
	$startTime = $this->getTime();
	 try{
	  $status = 0;
	  $message = 'Failed to sync';
      $childsPacked = array();
	  $attributeMapId = 0;
	  $ids = trim(Input::get('ids'));
	  $locationId = trim(Input::get('srcLocationId'));
	  $parent = trim(Input::get('parent'));      

	  Input::merge(array('codes' => $ids));
	  
	  $codes = trim(Input::get('codes'));
	  $destLocationId = trim(Input::get('destLocationId'));
	  $transitionTime = trim(Input::get('transitionTime'));
	  $transitionId = trim(Input::get('transitionId'));
	  $internalTransfer = trim(Input::get('internalTransfer'));
	  $mapParent = trim(Input::get('mapParent'));

	  Log::info(__FUNCTION__.' : '.print_r(Input::get(),true));
	  
	  DB::beginTransaction();

	  $request = Request::create('scoapi/MapEseals', 'POST');
	  $mapResult = Route::dispatch($request)->getContent();
	  Log::info(print_r($mapResult, true));
	  $res1 = json_decode($mapResult);
	  if($res1->Status == 1){	  	
		$request = Request::create('scoapi/UpdateTracking', 'POST');
		$trackResult = Route::dispatch($request)->getContent();
	  }else{
		
	   if($res1->Status == 2){
	   	$status  = 1;
	   	$childsPacked = $res1->iots;
		throw new Exception($res1->Message);         
	   }
	   else{
	   	$childsPacked = $res1->iots;
		throw new Exception($res1->Message);
	   }

	  }
	  Log::info(print_r($trackResult, true));
	  $res3 = json_decode($trackResult);
	  if(!$res3->Status){
	   throw new Exception('Track Update Error: ' . $res3->Message);  
	  }else{
	    DB::commit();		
		$status  = 1;
		$message = 'Mapping & Track Info Updated Succesfully';
	  }

	}catch(Exception $e){
	  DB::rollback();
	  $message = $e->getMessage();
	}
	$endTime = $this->getTime();
	Log::info(__FUNCTION__.' Finishes execution in '.($endTime-$startTime));
	return json_encode(['Status'=>$status, 'Message' =>'Server: '.$message,'iots'=>$childsPacked]);
}


public function checkIds() {
          try {
          	    Log::info(__FUNCTION__.' : '.print_r(Input::get(),true));

          	    $mfgId = $this->roleAccess->getMfgIdByToken(Input::get('access_token'));
	            $esealBankTable = 'eseal_bank_'.$mfgId;
	            $esealTable = 'eseal_'.$mfgId;
	            $ids = trim(Input::get('ids'));
	            $status = 1;
	            $message = 'data fetched successfully';
	            

                if(empty($ids))
                	throw new Exception('Parameters Missing');


	            $ids = explode(',', $ids);
	            $count = count($ids);
	            $usedCount = 0;
	            $unusedCount = 0;
	            $mismatchedCount = 0;


   	            $matchedIds = DB::table($esealBankTable)->whereIn('id',$ids)->lists('id');
                

                $matchedCount = count($matchedIds);
                //Log::info('matched count:'.$matchedCount);

	            $mismatchedCount=  $count - $matchedCount ;

                if($mismatchedCount != $count){
	            
	            $mismatchedIds = array_diff($matchedIds,$ids);
                if(!empty($mismatchedIds)){
                	foreach($mismatchedIds as $id){
                		$idArray[] = $id;
                	}
                	$mismatchedIds = $idArray;
                }

	            $usedCount = DB::table($esealBankTable)->whereIn('id',$ids)->where('used_status',1)->count('serial_id');
	            $unusedCount = DB::table($esealBankTable)->whereIn('id',$ids)->where('used_status',0)->count('serial_id');
	            
	            }
	            else{
	            	//Log::info('all ids are invalid');
	            	$mismatchedIds = $ids;
	            }

        } catch (Exception $e) {
            $status = 0;
            $message = $e->getMessage();
        }
        return json_encode(['Status' => $status, 'Message' => $message, 'UsedCount' => $usedCount, 'UnusedCount' => $unusedCount, 'MismatchCount' => $mismatchedCount, 'MismatchedIds' => $mismatchedIds]);
    }




		public function multiLevelMapping(){

try{
	Log::info(__FUNCTION__.' : '.print_r(Input::get(),true));
	$status =1;
	$message = 'Binding,Mapping and Trackupdation is successfull';
	//$attributes = trim(Input::get('attributes'));
	$locationId = $this->roleAccess->getLocIdByToken(Input::get('access_token'));
	$pid = trim(Input::get('pid'));
	$tertiary_id = trim(Input::get('tertiary_id'));
	$childJson = trim(Input::get('child_json'));
	$transitionId = trim(Input::get('transitionId'));
	$transitionTime = trim(Input::get('transitionTime'));
	$attribute_map_id = trim(Input::get('attribute_map_id'));

		if(empty($locationId) || empty($tertiary_id) || empty($childJson) || empty($transitionId) || empty($transitionTime) || empty($pid) || empty($attribute_map_id))
		  throw new Exception('Parameters Missing');
		DB::beginTransaction();

		/*$request = Request::create('scoapi/SaveBindingAttributes', 'POST', array('module_id'=> Input::get('module_id'),'access_token'=>Input::get('access_token'),'attributes'=>$attributes,'lid'=>$locationId,'pid'=>$pid));
		$originalInput = Request::input();//backup original input
		Request::replace($request->input());
		$response = Route::dispatch($request)->getContent();//invoke API  
		$response = json_decode($response,true); 
		  

		  if(!$response['Status'])
			throw new Exception($response['Message']);

			 $attribute_map_id = $response['AttributeMapId'];*/

			 $childArray = json_decode($childJson,true);
			 
			 if(json_last_error() != JSON_ERROR_NONE)
				throw new Exception ('Json Error');

			foreach($childArray as $childs){ 
			
			  $ids = $childs['childs'];
			  $parent = $childs['parent'];
			  $parentArray[] = $childs['parent'];
			  $parentImploded = implode(',',$parentArray);

			 $request = Request::create('scoapi/SyncEseal', 'POST', array('module_id'=> Input::get('module_id'),'access_token'=>Input::get('access_token'),'attribute_map_id'=>$attribute_map_id,'ids'=>$ids,'codes'=>$ids,'parent'=>$parent,'srcLocationId'=>$locationId,'pid'=>$pid,'transitionId'=>$transitionId,'transitionTime'=>$transitionTime));
			 $originalInput = Request::input();//backup original input
			 Request::replace($request->input());
			 $response = Route::dispatch($request)->getContent();//invoke API  
			 $response = json_decode($response,true); 
			   
			   if(!$response['Status'])
				  throw new Exception($response['Message']);

		   }
			  $request = Request::create('scoapi/MapWithTrackupdate', 'POST', array('module_id'=> Input::get('module_id'),'access_token'=>Input::get('access_token'),'parent'=>$tertiary_id,'ids'=>$parentImploded,'codes'=>$parentImploded,'srcLocationId'=>$locationId,'transitionId'=>$transitionId,'transitionTime'=>$transitionTime,'mapParent'=>true,'notUpdateChild'=>true));
			  $originalInput = Request::input();//backup original input
			  Request::replace($request->input());			  
			  $response = Route::dispatch($request)->getContent();
			  $response = json_decode($response,true);

				if(!$response['Status'])
					throw new Exception($response['Message']);

				DB::commit();
	}
	catch(Exception $e){
		DB::rollback();
		$status = 0;
		$message = $e->getMessage();
	}
	Log::info(['Status'=>$status,'Message'=>$message]);
	return json_encode(['Status'=>$status,'Message'=>$message]);
}

public function changeUserDetails(){
	try{
		$status =1;
		$message = 'User Details changed successfully';
		$mobile_no = trim(Input::get('mobile_no'));
		$email = trim(Input::get('email'));

		if(empty($mobile_no) && empty($email))
			throw new Exception('Parameters not passed');

		$user = $this->roleAccess->checkAccessToken(Input::get('access_token'));
		$user_id = $user[0]->user_id;

		if(!empty($mobile_no))
			$array['phone_no'] = $mobile_no;
		if(!empty($email))
			$array['email'] = $email;


		DB::table('users')->where('user_id',$user_id)->update($array);

	}
	catch(Exception $e){
		$status =0;
		$message = $e->getMessage();
	}
	Log::info(['Status'=>$status,'Message'=>$message]);
	return json_encode(['Status'=>$status,'Message'=>$message]);
}

public function changeTpDestination(){
	try{
		Log::info(__FUNCTION__. print_r(Input::get(),true));
		$status =1;
		$message = 'TP updated successfully';
		$src_location_id = $this->roleAccess->getLocIdByToken(Input::get('access_token'));
		$tp_id = trim(Input::get('tp_id'));
		$dest_location_id = trim(Input::get('change_location_id'));
        $transitionTime = trim(Input::get('transitionTime'));

		if(empty($dest_location_id) || empty($tp_id))
			throw new Exception('Some of the params are missing');

				try{
				$res = DB::table($this->trackHistoryTable)->where('tp_id', $tp_id)->orderBy('update_time','desc')->take(1)->get();
					}
					catch(PDOException $e){
						Log::info($e->getMessage());
						throw new Exception('Error during query exceution');
					}
					
					if(!count($res)){
						throw new Exception('Invalid TP');
					}

						if($res[0]->src_loc_id == $dest_location_id && $res[0]->dest_loc_id==0){
							throw new Exception('TP is already received at the given change location');
						}

						if($res[0]->src_loc_id != $src_location_id)
							throw new Exception('The TP destination cant be changed');

						if($res[0]->src_loc_id == $src_location_id && $res[0]->dest_loc_id == $dest_location_id)
							throw new Exception('The TP is already destined for the given change location');
						
						if($res[0]->dest_loc_id == 0)
							throw new Exception('The Tp is already received at some location');
						
						if($transitionTime < $res[0]->update_time)
							throw new Exception('Change destination timestamp less than stock transfer timestamp');

						$res = DB::table($this->trackHistoryTable)->where('track_id', $res[0]->track_id)->update(['dest_loc_id'=>$dest_location_id]);

	}
	catch(Exception $e){
	   $status =0;
	   $message = $e->getMessage();
	}
	Log::info(['Status'=>$status,'Message'=>$message]);
	return json_encode(['Status'=>$status,'Message'=>$message]);
}


public function getPhysicalInventory(){
	try{
		Log::info(__FUNCTION__.' : '.print_r(Input::get(),true));
		$status =1;
		$message = 'Material Inventory successfully retrieved';
		$material = trim(Input::get('material_code'));
		$locationId = $this->roleAccess->getLocIdByToken(Input::get('access_token'));
		$plant_id = Location::where('location_id',$locationId)->pluck('erp_code');
		$mfgId = $this->roleAccess->getMfgIdByToken(Input::get('access_token'));
		$method = 'ZESEAL_053_INVENTORY_DETAILS_SRV';
		$method_name = 'INV_DETAILS';
        $data = array();
		

		if(empty($material))
			throw new Exception('Material not passed');

		if(!Products::where(['material_code'=>$material,'manufacturer_id'=>$mfgId])->pluck('product_id'))
			throw new Exception('In-Valid Material');


		$erpDetails = DB::table('erp_integration')->where('manufacturer_id',$mfgId)->get(['token','web_service_url','sap_client']);

        
        $data = ['PLANT'=>$plant_id,'MATERIAL'=>$material];

		$response =  $this->sapCall($mfgId,$method,$method_name,$data,Input::get('access_token'));
		//Log::info('GET PHYSICAL INVENTORY response:-');
	    //Log::info($response);
				  
		   $response = json_decode($response);
		   if($response->Status){
					$response =  $response->Data;

					Log::info($response);

					$parseData1 = xml_parser_create();
					xml_parse_into_struct($parseData1, $response, $documentValues1, $documentIndex1);
					xml_parser_free($parseData1);
					$documentData = array();
					foreach ($documentValues1 as $data) {
						if(isset($data['tag']) && $data['tag'] == 'D:ESEAL_OUTPUT')
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
					//Log::info('GET PHYSICAL INVENTORY array response:-');
					//Log::info($xml_array);

					if($xml_array['HEADER']['Status'] == 1){

						
                        if(!isset($xml_array['DATA']['MATERIAL'])){

						$data = $xml_array['DATA'];
					}
					else{
						array_push($data, $xml_array['DATA']);
					}

					} 
					else{
						throw new Exception($xml_array['HEADER']['Message']);
					}

         }
         else{
             throw new Exception($response->Message);
         }




	}
	catch(Exception $e){
		$status =0;
		$message = $e->getMessage();
	}
    Log::info(['Status'=>$status,'Message'=>$message,'Data'=>$data]);
	return json_encode(['Status'=>$status,'Message'=>$message,'Data'=>$data]);

}

public function updatePhysicalInventoryBackup(){
	try{
		$status =1;
		$message = 'Material Inventory updated to ERP';
		$material = trim(Input::get('material_code'));
		$batchJson = Input::get('batchJson');
		$transitionTime = Input::get('transitionTime');
		$locationId = $this->roleAccess->getLocIdByToken(Input::get('access_token'));
		$plant_id = Location::where('location_id',$locationId)->pluck('erp_code');
		$mfgId = $this->roleAccess->getMfgIdByToken(Input::get('access_token'));
		$method ='POST';
		$method1 = 'Z051_ESEAL_INV_DIFF_SRV';
		$method_name = 'INV_DIFF';
        $itemXml = '';
        $esealTable = 'eseal_'.$mfgId;

        
		if(empty($material) || empty($batchJson) || empty($transitionTime))
			throw new Exception('Parameters missing');

        $pdate = date("d.m.Y", strtotime($transitionTime));

        $pid = Products::where(['material_code'=>$material,'manufacturer_id'=>$mfgId])->pluck('product_id');

		if(!$pid)
			throw new Exception('In-Valid Material');


		$business_unit_id = Products::where('material_code',$material)->pluck('business_unit_id');

		$erpDetails = DB::table('erp_integration')->where('manufacturer_id',$mfgId)->get(['token','web_service_url','sap_client']);
        $erpToken = $erpDetails[0]->token;
        $erpUrl = $erpDetails[0]->web_service_url;
        $sap_client = $erpDetails[0]->sap_client;
        

        $batchArr = json_decode($batchJson,true);

        if(json_last_error() != JSON_ERROR_NONE)
        	throw new Exception('In-valid Json.');


        $batches = $batchArr['batches'];
        $flags = $batchArr['flags'];

        if(isset($flags['status']) && $flags['status'] == 1){
        	$matched = true;
        	$store_location = 'PR01';
        }
        if(isset($flags['is_missing']) && $flags['is_missing'] == 1){
        	$missing = true;
            $store_location = Location::where(['parent_location_id'=>$locationId,'business_unit_id'=>$business_unit_id,'storage_location_type_code'=>25003])->pluck('erp_code');
        }
        if(isset($flags['is_excess']) && $flags['is_excess'] == 1){
        	$excess =  true;
        	$store_location = Location::where(['parent_location_id'=>$locationId,'business_unit_id'=>$business_unit_id,'storage_location_type_code'=>25006])->pluck('erp_code');

        }


        foreach($batches as $batch){

           
           $ids = $batch['ids'];
           $explodedIds = explode(',',$ids);

           
              $transitCnt = DB::table($esealTable.' as es')
                              ->join($this->trackHistoryTable.' as th', 'es.track_id', '=', 'th.track_id')
		                      ->where(function($query) use($explodedIds){
									$query->whereIn('es.parent_id', $explodedIds)
										  ->orWhereIn('es.primary_id',$explodedIds);
											 }
											 )
		                      ->where('level_id',0)
		                      ->groupBy('src_loc_id','dest_loc_id')
		                      ->get(['src_loc_id','dest_loc_id']);

		//Log::info($transitCnt);

		if(count($transitCnt)>1){
			throw new Exception('Some of the codes are available with different location');
		}
		if(count($transitCnt) == 1){
			if($transitCnt[0]->dest_loc_id != 0 || $transitCnt[0]->src_loc_id != $locationId){
				throw new Exception('Some of the codes are available with different location');   
			}
		}

		$query = DB::table($esealTable.' as es')
		             ->where(function($query) use($explodedIds){
									$query->whereIn('es.parent_id', $explodedIds)
										  ->orWhereIn('es.primary_id',$explodedIds);
											 }
											 )
		             ->where('level_id',0);

		$distinctPID = $query->groupBy('pid','batch_no')->get(['pid','batch_no']);
		
		//Log::info('distinctPIDCount :'.count($distinctPID));

		if(count($distinctPID) > 1)
		  throw new Exception('The ids belong to multiple materials');

	    if($distinctPID[0]->pid != $pid)
          throw new Exception('The ids belong to another product:'.$distinctPID[0]->pid);		                 

        if($distinctPID[0]->batch_no != $batch['batch'])
          throw new Exception('The ids belong to another batch :'.$distinctPID[0]->batch_no);		                         


         if($batch['uom'] == 'M')
         	$qty = $query->sum('pkg_qty');
         else
         	$qty = $query->count('eseal_id');


         $itemXml .= '<ITEM MAT_CODE="'.$material.'" BATCH="'.$batch['batch'].'" QUANTITY="'.$qty.'" />';

        }

        $xml = '<?xml version="1.0" encoding="utf-8" ?> 
                    <REQUEST>
                    <DATA>
                    <INPUT TOKEN="'.$erpToken.'" ESEAL_KEY="'.$this->getRand().'" PLANT="'.$plant_id.'" SLOC="'.$store_location.'" PDATE="'.$pdate.'"  /> 
                    <ITEMS>';

        $xml .= $itemXml;

        $xml .= '</ITEMS></DATA></REQUEST>';

   		$erp = $this->roleAccess->getErpDetailsByUserId(Input::get('access_token'));	
					if($erp){
					$username = $erp[0]->erp_username;
					$password = $erp[0]->erp_password;
					}
					else{
						throw new Exception('There are no erp username and password');
					}
					
					
        $finalXML = '<entry xmlns="http://www.w3.org/2005/Atom" xmlns:m="http://schemas.microsoft.com/ado/2007/08/dataservices/metadata" xmlns:d="http://schemas.microsoft.com/ado/2007/08/dataservices" xml:base="'.$erpUrl.$method1.'/">
							<id>'
							.$erpUrl.$method1.'/'.$method_name.'(\'123\')
							</id>
							<title type="text">'.$method_name.'(\'123\')</title>
							<updated>2015-08-14T10:19:23Z</updated>
							<category term="'.$method1.'.'.$method_name.'" scheme="http://schemas.microsoft.com/ado/2007/08/dataservices/scheme"/>
							<link href="'.$method_name.'(\'123\')" rel="self" title="'.$method_name.'"/>
							<content type="application/xml">
							<m:properties>
							<d:ESEAL_INPUT>"<![CDATA[';
								$finalXML .= $xml;
								$finalXML .= ']]>"</d:ESEAL_INPUT>
										<d:ESEAL_OUTPUT />
										 </m:properties>
										</content>
										 </entry>';	
					
                    Log::info('XML PASSED:-');
                    Log::info($finalXML);

                    $url = $erpUrl .$method1.'/'.$method_name.'?&sap-client='.$sap_client;
					Log::info($url);
					Log::info('SAP start');
					$response = $this->sapRepo->request($username,$password,$url, $method,null,'xml',2,null,$finalXML);
					Log::info('SAP response:-'.$response);

					$parseData1 = xml_parser_create();
					xml_parse_into_struct($parseData1, $response, $documentValues1, $documentIndex1);
					xml_parser_free($parseData1);
					$documentData = array();

					foreach ($documentValues1 as $data) {
						if(isset($data['tag']) && $data['tag'] == 'D:PHY_DOC_NO')
						{
							$documentData = $data['value'];
						}
						
					}

					if(empty($documentData))
						throw new Exception('ERP call error occurred');


                    $deXml = simplexml_load_string($documentData);
					$deJson = json_encode($deXml);
					$xml_array = json_decode($deJson,TRUE);
					Log::info('PHYSICAL INVENTORY array response:-');
					Log::info($xml_array);


					if($xml_array['HEADER']['Status'] == 1)
                        DB::commit();
 					else
						throw new Exception($xml_array['HEADER']['Message']);
							
	}
	catch(Exception $e){
		DB::rollback();
		$status =0;
		$message = $e->getMessage();
	}
    Log::info(['Status'=>$status,'Message'=>$message]);
	return json_encode(['Status'=>$status,'Message'=>$message]);
}


public function updatePhysicalInventory(){
	try{
		$status =1;
		$message = 'Material Inventory updated to ERP';
		$material = trim(Input::get('material_code'));
		$transitionTime = Input::get('transitionTime');
		$ids = trim(Input::get('ids'));
		$store_code =  trim(Input::get('store_code'));
		$locationId = $this->roleAccess->getLocIdByToken(Input::get('access_token'));
		$plant_id = Location::where('location_id',$locationId)->pluck('erp_code');
		$mfgId = $this->roleAccess->getMfgIdByToken(Input::get('access_token'));
		$method ='POST';
		$method1 = 'Z051_ESEAL_INV_DIFF_SRV';
		$method_name = 'INV_DIFF';
        $itemXml = '';
        $esealTable = 'eseal_'.$mfgId;

        
		if(empty($material) || empty($ids) || empty($transitionTime))
			throw new Exception('Parameters missing');

        $pdate = date("d.m.Y", strtotime($transitionTime));

        $pid = Products::where(['material_code'=>$material,'manufacturer_id'=>$mfgId])->pluck('product_id');

		if(!$pid)
			throw new Exception('In-Valid Material');


		$uom = DB::table('products as pr')
                   ->join('uom_classes as uom','uom.id','=','pr.uom_class_id')
                   ->where(['product_id'=>$pid,'pr.manufacturer_id'=>$mfgId])
                   ->pluck('uom_code');


		$business_unit_id = Products::where('material_code',$material)->pluck('business_unit_id');

		$erpDetails = DB::table('erp_integration')->where('manufacturer_id',$mfgId)->get(['token','web_service_url','sap_client']);
        $erpToken = $erpDetails[0]->token;
        $erpUrl = $erpDetails[0]->web_service_url;
        $sap_client = $erpDetails[0]->sap_client;
                        
        

        /*********new code*************/

        $explodedIds = explode(',',$ids);
        $explodedIds = array_unique($explodedIds);

           
              $transitCnt = DB::table($esealTable.' as es')
                              ->join($this->trackHistoryTable.' as th', 'es.track_id', '=', 'th.track_id')
		                      ->where(function($query) use($explodedIds){
									$query->whereIn('es.parent_id', $explodedIds)
										  ->orWhereIn('es.primary_id',$explodedIds);
											 }
											 )
		                      ->where('level_id',0)
		                      ->groupBy('src_loc_id','dest_loc_id')
		                      ->get(['src_loc_id','dest_loc_id']);

		//Log::info($transitCnt);

		if(count($transitCnt)>1){
			throw new Exception('Some of the codes are available with different location');
		}
		if(count($transitCnt) == 1){
			if($transitCnt[0]->dest_loc_id != 0 || $transitCnt[0]->src_loc_id != $locationId){
				throw new Exception('Some of the codes are available with different location');   
			}
		}

		$query = DB::table($esealTable.' as es')
		             ->where(function($query) use($explodedIds){
									$query->whereIn('es.parent_id', $explodedIds)
										  ->orWhereIn('es.primary_id',$explodedIds);
											 }
											 )
		             ->where('level_id',0);

		$distinctPID = $query->groupBy('pid','batch_no')->get(['pid','batch_no']);
		
		//Log::info('distinctPIDCount :'.count($distinctPID));

		if(count($distinctPID) > 1)
		  throw new Exception('The ids belong to multiple materials');

	    if($distinctPID[0]->pid != $pid)
          throw new Exception('The ids belong to another product:'.$distinctPID[0]->pid);


        /*******end of new code********/

        $query =  DB::table('eseal_'.$mfgId.' as es')
										->join('products','products.product_id','=','es.pid')
										->where('es.level_id',0)
										->where('products.product_type_id',8003)
										->where(function($query) use($explodedIds){
													 $query->whereIn('es.parent_id', $explodedIds)
														   ->orWhereIn('es.primary_id',$explodedIds);
											 }
											 )
										->groupBy('batch_no');

	    if($uom == 'M')
			$batches = 	$query->get([DB::raw('sum(pkg_qty) as qty'),'batch_no']);
	    else
	    	$batches =  $query->get([DB::raw('count(eseal_id) as qty'),'batch_no']);



        foreach($batches as $batch){                     		                       	                         


         $itemXml .= '<ITEM MAT_CODE="'.$material.'" BATCH="'.$batch->batch_no.'" QUANTITY="'.$batch->qty.'" />';

        }

        $xml = '<?xml version="1.0" encoding="utf-8" ?> 
                    <REQUEST>
                    <DATA>
                    <INPUT TOKEN="'.$erpToken.'" ESEAL_KEY="'.$this->getRand().'" PLANT="'.$plant_id.'" SLOC="'.$store_code.'" PDATE="'.$pdate.'"  /> 
                    <ITEMS>';

        $xml .= $itemXml;

        $xml .= '</ITEMS></DATA></REQUEST>';

   		$erp = $this->roleAccess->getErpDetailsByUserId(Input::get('access_token'));	
					if($erp){
					$username = $erp[0]->erp_username;
					$password = $erp[0]->erp_password;
					}
					else{
						throw new Exception('There are no erp username and password');
					}
					
					
                      $finalXML = '<entry xmlns="http://www.w3.org/2005/Atom" xmlns:m="http://schemas.microsoft.com/ado/2007/08/dataservices/metadata" xmlns:d="http://schemas.microsoft.com/ado/2007/08/dataservices" xml:base="'.$erpUrl.$method1.'/">
							<id>'
							.$erpUrl.$method1.'/'.$method_name.'(\'123\')
							</id>
							<title type="text">'.$method_name.'(\'123\')</title>
							<updated>2015-08-14T10:19:23Z</updated>
							<category term="'.$method1.'.'.$method_name.'" scheme="http://schemas.microsoft.com/ado/2007/08/dataservices/scheme"/>
							<link href="'.$method_name.'(\'123\')" rel="self" title="'.$method_name.'"/>
							<content type="application/xml">
							<m:properties>
							<d:ESEAL_INPUT>"<![CDATA[';
								$finalXML .= $xml;
								$finalXML .= ']]>"</d:ESEAL_INPUT>
										<d:ESEAL_OUTPUT />
										 </m:properties>
										</content>
										 </entry>';	
					
                    Log::info('XML PASSED:-');
                    Log::info($finalXML);

                    $url = $erpUrl .$method1.'/'.$method_name.'?&sap-client='.$sap_client;
					Log::info($url);
					Log::info('SAP start');
					$response = $this->sapRepo->request($username,$password,$url, $method,null,'xml',2,null,$finalXML);
					Log::info('SAP response:-'.$response);

					$parseData1 = xml_parser_create();
					xml_parse_into_struct($parseData1, $response, $documentValues1, $documentIndex1);
					xml_parser_free($parseData1);
					$documentData = array();

					foreach ($documentValues1 as $data) {
						if(isset($data['tag']) && $data['tag'] == 'D:PHY_DOC_NO')
						{
							$documentData = $data['value'];
						}
						
					}

					if(empty($documentData))
						throw new Exception('ERP call error occurred');


                    $deXml = simplexml_load_string($documentData);
					$deJson = json_encode($deXml);
					$xml_array = json_decode($deJson,TRUE);
					Log::info('PHYSICAL INVENTORY array response:-');
					Log::info($xml_array);


					if($xml_array['HEADER']['Status'] == 1)
                        DB::commit();
 					else
						throw new Exception($xml_array['HEADER']['Message']);
							
	}
	catch(Exception $e){
		DB::rollback();
		$status =0;
		$message = $e->getMessage();
	}
    Log::info(['Status'=>$status,'Message'=>$message]);
	return json_encode(['Status'=>$status,'Message'=>$message]);
}


public function SyncStockOut(){
	
	$startTime = $this->getTime();
	try{
		$status = 0;
		$isSapEnabled = 0 ;
		//$existingQuantity =0;
		$sapProcessTime = 0;
		$message = 'Failed to do stockout/sale';
		$deliver_no = (int)trim(Input::get('delivery_no'));
		$ids = trim(Input::get('ids'));
		$codes =  trim(Input::get('codes'));
		$mfgId = $this->roleAccess->getMfgIdByToken(Input::get('access_token'));
		$srcLocationId = trim(Input::get('srcLocationId'));    
		$destLocationId = trim(Input::get('destLocationId'));  
		//$transitionTime = trim(Input::get('transitionTime'));
		$transitionTime = $this->getDate();
		$transitionId = trim(Input::get('transitionId'));
		$tpDataMapping = trim(Input::get('tpDataMapping'));
		$pdfContent = trim(Input::get('pdfContent'));
		$pdfFileName = trim(Input::get('pdfFileName'));
		$sapcode = trim(Input::get('sapcode'));
		$isSapEnabled = trim(Input::get('isSapEnabled'));
		$destinationLocationDetails = trim(Input::get('destinationLocationDetails'));

		if($transitionTime > $this->getDate() || date('Y', strtotime($transitionTime)) == '2009')
			$transitionTime = $this->getDate();
		
		$method = 'Z036_ESEAL_GET_DELIVERY_DETAIL_SRV'; 
		$method_name ='DELIVER_DETAILS';

		
		Log::info(' === '. print_r(Input::get(),true));
		
		DB::beginTransaction();
		$locationObj = new Locations\Locations();
		$mfgId = $locationObj->getMfgIdForLocationId($srcLocationId);
		if($mfgId){
			$esealTable = 'eseal_'.$mfgId;
			$esealBankTable = 'eseal_bank_'.$mfgId;

		if(!empty($sapcode) && $destLocationId==0 ){
			$destLocationId = $locationObj->getDestinationLocationIdFromSAPCode($sapcode);
			Log::info('=='.$destLocationId);
			if(!$destLocationId){
				if(!empty($destinationLocationDetails) && empty($destLocationId) ){
					$destDetails = json_decode($destinationLocationDetails);
					$destLocation = $locationObj->createOrReturnLocationId($destDetails, $mfgId);
					Log::info('==='.print_r($destLocation,true));
					if($destLocation['Status']==0){
						throw new Exception($destLocation['Message']);
					}
					if($destLocation['Status']==1){
						$destLocationId = $destLocation['Id'];
					}
				}
			}
		}
		////////Checking the number of IDS in esealDB appended to given delivery no.
		$tranName = Transaction::where(['id'=>$transitionId])->pluck('name');
		

		//////Convert IDS into string and array
		$explodeIds = explode(',', $ids);
		$explodeIds = array_unique($explodeIds);
		
		$idCnt = count($explodeIds);
		$strCodes = '\''.implode('\',\'', $explodeIds).'\'';
		


        ////Check if this request is already processed.
		$alreadyProcessCount = DB::table($this->tpDataTable)->whereIn('level_ids',$explodeIds)->where('tp_id',$codes)->count();
		Log::info($alreadyProcessCount);
		if($alreadyProcessCount == $idCnt){
			return json_encode(['Status'=>1,'Message'=>'Already Processed']);
		}

		Log::info(print_r(Input::get(),true));
		
		////Check if these ids have already some tp
		$tpCount = DB::table($esealTable.' as eseal')->join($this->trackHistoryTable.' as th', 'eseal.track_id', '=', 'th.track_id')
		->whereIn('primary_id', $explodeIds)
		->where('tp_id','!=', 0)
		->where('dest_loc_id', '>', 0)
		->select('tp_id')
		->distinct()
		->get();

		Log::info(count($tpCount));
		if(count($tpCount)){
			throw new Exception('Some of the codes are already assigned some TPs');
		}

        

		//Check if TP Id already Used
		$result = DB::table($esealBankTable)->where('id',$codes)->select('id', 'used_status')->get();
		Log::info($result);
		if($result[0]->used_status){
			throw new Exception('TP is already used');
		}

		//Check if TP id is either downloaded or issued
		//$cnt = DB::table($esealBankTable)->where('id',$codes)->where('issue_status',0)->orWhere('download_status',0)->count();
		$cnt = DB::table($esealBankTable)->where('id', $codes)
		->where('issue_status',0)
		->where('download_status',0)
		->count();

		Log::info($cnt);
		if($cnt){
			throw new Exception('Can\'t used as TP.');
		}

		 ///Check if its a valid tp
		//$cnt = DB::table($esealBankTable)->where('id',$codes)->where('issue_status',1)->orWhere('download_status',1)->count();
		$cnt1 = DB::table($esealBankTable)->where('id', $codes)
		->where(function($query){
			$query->where('issue_status',1);
			$query->orWhere('download_status',1);
		})->count();

		Log::info($cnt1);
		if(!$cnt1){
			throw new Exception('Not a valid TP.');
		} 
		//Check if all codes exists in db
		$result = DB::table($esealTable)->whereIn('primary_id',$explodeIds)->count();
		Log::info('===='.print_r($result,true));
		if($idCnt != $result){
			throw new Exception('Some of the codes not exists in database');
		}

		$result = DB::table($esealTable)
                              ->where(function($query) use($explodeIds){
                        $query->whereIn('parent_id',$explodeIds);
                        $query->orWhereIn('primary_id',$explodeIds);
                              })->where('is_active',0)->where('level_id',0)
                              ->count();
                Log::info('====blocked iots count'.print_r($result,true));
                if($result){
                        throw new Exception('Some of the codes are blocked');
                }


		//////////CHECK IF ALL THE IDS HAVE SAME SOURCE LOCATION ID As SUPPLIED
		$transitCnt = DB::table($esealTable.' as eseal')->join($this->trackHistoryTable.' as th', 'eseal.track_id', '=', 'th.track_id')
		->whereIn('primary_id', $explodeIds)
		->select('src_loc_id','dest_loc_id')->groupBy('src_loc_id','dest_loc_id')->get();
		Log::info($transitCnt);
		
		$locationObj = new Locations\Locations();
        $childIds = Array();
		$childIds = $locationObj->getAllChildIdForParentId($srcLocationId);
		if($childIds)
		{
			array_push($childIds, $srcLocationId);
		}
		$parentId = $locationObj->getParentIdForLocationId($srcLocationId);
		$childIds1 = Array();
		if($parentId)
		{
			$childIds1 = $locationObj->getAllChildIdForParentId($parentId);
			if($childIds1)
			{
				array_push($childIds1, $parentId);
			}
		}
		$childsIDs = array_merge($childIds, $childIds1);
		array_push($childsIDs, $srcLocationId);
		$childsIDs = array_unique($childsIDs);

		if(count($transitCnt)>1){                

            $inCount = DB::table($esealTable.' as es')
                           ->join('track_history as th','th.track_id','=','es.track_id')
                           ->whereIn('primary_id',$explodeIds)
                           ->whereIn('src_loc_id',$childsIDs)
                           ->count('es.primary_id');
            if($inCount != $idCnt)
      			throw new Exception('Some of the codes are available with different location');

		}
		if(count($transitCnt) == 1){
			if($transitCnt[0]->dest_loc_id>0){
				throw new Exception('Some of the codes are in transit.');   
			}
			if(!in_array($transitCnt[0]->src_loc_id,$childsIDs)){
				throw new Exception('Some of the codes are already available in different location.');   
			}
		}

		if(!empty($tpDataMapping)){
			$status = $this->mapTPAttributes($codes, $esealTable, $srcLocationId, $tpDataMapping, $transitionTime);
			if(!$status){
				throw new Exception('Failed during mapping TP Attributes');
			}
			$this->checkNUpdateOrder($tpDataMapping);
		}      

		//$trackResult = $this->trackUpdate();
		/*$trackResult = $this->saveStockIssue($codes, $ids, $srcLocationId, $destLocationId, $transitionTime, $transitionId);
		Log::info(gettype($trackResult));*/
		
		$status = $this->saveTPData($codes, $srcLocationId, $destLocationId, $pdfFileName, $ids, $transitionTime, $pdfContent,$mfgId);
		if(!$status){
			throw new Exception('Failed during saving TP data');
		}

		//$trackResult = $this->trackUpdate();
		$trackResult = $this->saveStockIssue($codes, $ids, $srcLocationId, $destLocationId, $transitionTime, $transitionId);
		Log::info(gettype($trackResult));
		$trackResultDecode = json_decode($trackResult);
		Log::info(print_r($trackResultDecode, true));
		if(!$trackResultDecode->Status){
			throw new Exception($trackResultDecode->Message);
		}
		try{
			$serial_id = DB::table($esealBankTable)->where('id',$codes)->pluck('serial_id');
			DB::table($esealBankTable)->where('serial_id',$serial_id)->update(Array(
				'used_status'=>1,
				'level'=>9,
				'location_id' => $srcLocationId,
				'utilizedDate'=>$this->getDate()
				));		
		}catch(PDOException $e){
			throw new Exception($e->getMessage());
		}
		
		$status = 1;
		$message = 'Stock out done successfully';

		//Deleting the tp from partial_transactions table as the tp is processed completely.
		DB::table('partial_transactions')->where('tp_id',$codes)->delete();
		
		if(empty($deliver_no) || $isSapEnabled ==0 || $tranName=='Sales PO'){
			DB::commit();
			goto stockout;
		}
		
		if($status){
			//Checking if we need to process a delivery no or not.
			if($deliver_no){
			    $erpToken = DB::table('erp_integration')->where('manufacturer_id',$mfgId)->pluck('token');
			    $erpUrl = DB::table('erp_integration')->where('manufacturer_id',$mfgId)->pluck('web_service_url');

				$data =['DELIVERY'=>$deliver_no];
				
				  //SAP call for getting Delivery Details.
				  $response =  $this->sapCall($mfgId,$method,$method_name,$data,Input::get('access_token'));
				  Log::info('GET DELIVERY SAP response:-');
				  Log::info($response);
				  
				  $response = json_decode($response);
				  if($response->Status){
					$response =  $response->Data;

					$parseData1 = xml_parser_create();
					xml_parse_into_struct($parseData1, $response, $documentValues1, $documentIndex1);
					xml_parser_free($parseData1);
					$documentData = array();
					foreach ($documentValues1 as $data) {
						if(isset($data['tag']) && $data['tag'] == 'D:GET_DELIVER')
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
					Log::info('GET DELIVER array response:-');
					Log::info($xml_array);
					$data = $xml_array['DATA']['ITEMS'];
					
					if(!array_key_exists('NO', $data['ITEM'])){
                        foreach($data['ITEM'] as $data1){
                          $uomArray[] = ['MATERIAL_CODE'=>$data1['MATERIAL_CODE'],'UOM'=>$data1['UOM'],'ITEM_CATEG'=>$data1['ITEM_CATEG'],'QTY'=>$data1['QUANTITY']]; 
                        }
					}
					else{
						$data1 = $data['ITEM'];
						$uomArray[] = ['MATERIAL_CODE'=>$data1['MATERIAL_CODE'],'UOM'=>$data1['UOM'],'ITEM_CATEG'=>$data1['ITEM_CATEG'],'QTY'=>$data1['QUANTITY']];
					}

                    Log::info('UOM ARRAY');
             		Log::info($uomArray);

             		$deliver1 = array();
                        $materialArr =  array();
					
              
                    
                    foreach($uomArray as $uom){
                    
                    if(!in_array($uom['MATERIAL_CODE'],$materialArr)){
                      $materialArr[] = $uom['MATERIAL_CODE'];
                    

					if($uom['UOM'] == 'M'){	

					
                    $deliver1 =  DB::table('eseal_'.$mfgId.' as es')
										->join('products','products.product_id','=','es.pid')
										->where('es.level_id',0)
										->where('products.product_type_id',8003)
										->where(function($query) use($explodeIds){
													 $query->whereIn('es.parent_id', $explodeIds)
														   ->orWhereIn('es.primary_id',$explodeIds);
											 }
											 )
										->where('products.material_code',$uom['MATERIAL_CODE'])
										->groupBy('batch_no')
										->get([DB::raw('primary_id as id'),DB::raw('sum(pkg_qty) as qty'),'batch_no','material_code','products.is_serializable']);					        

                    
                    }
                    else{
					$deliver1 =  DB::table('eseal_'.$mfgId.' as es')
										->join('products','products.product_id','=','es.pid')
										->where('es.level_id',0)
										->where('products.product_type_id',8003)
										->where(function($query) use($explodeIds){
													 $query->whereIn('es.parent_id', $explodeIds)
														   ->orWhereIn('es.primary_id',$explodeIds);
											 }
											 )
										->where('products.material_code',$uom['MATERIAL_CODE'])
										->groupBy('batch_no','primary_id')
										->get([DB::raw('primary_id as id'),DB::raw('CASE WHEN multiPack=0 THEN 1 ELSE sum(pkg_qty) END AS qty'),'batch_no','material_code','products.is_serializable']);
                    }

                    $deliver[] = $deliver1;
                    }

					}

                    
                    Log::info('materialsssss:-');
                    Log::info($deliver);
					
					$itemArr = array();

					foreach ($deliver as $arr) {

                     foreach($arr as $item){

                     	array_push($itemArr,$item);

                     }
						
					}

					$deliver = $itemArr;



					//foreach($deliver as $del){
					//	$deliver2[] = $del[0];
					//}

					//for($i=0;$i < count($deliver); $i++){
					//	$deliver2[] = $deliver[$i];
					//}

					//$deliver = $deliver2;

					////$deliver = $deliver[0];

					//$deliver =  array_merge($deliver1,$deliver2);

					Log::info('System Materials:-');
					Log::info($deliver);
					foreach($deliver as $dev){
						$devArr[] = trim($dev->material_code);
					}
					$matArr = array();
					if(!array_key_exists('NO', $data['ITEM'])){
						foreach($data['ITEM'] as $data1){
							foreach($data1 as $key=>$value){
						if(is_array($value) && empty($value)){
							$data1[$key] = '';
						}
					}
							$plant_code = $data1['PLANT'];
							$store_code = $data1['STORE_LOCATION'];
							$material_code = $data1['MATERIAL_CODE'];

							if(!in_array($material_code,$matArr)){
							$matArr[] = trim($material_code);
							$matr[] = ['plant_code'=>$plant_code,'store_code'=>$store_code,'material_code'=>$material_code];
						}
						
					}
					Log::info('eseal Unique materials:-');
					Log::info(array_unique($devArr));
					Log::info('erp Unique materials:-');
					Log::info(array_unique($matArr));
					/*if(array_unique($devArr) != array_unique($matArr)){
						$status =0;
						throw new Exception('Some of the materials are missing');
					
					}	*/			  
						
					}
					else{
                                                Log::info('hiiii');
						 $data = $data['ITEM'];
						Log::info('xxxxxx');
						foreach($data as $key=>$value){
							
							if(is_array($value) && empty($value)){
							$data[$key] = '';
						}

						}
						
						$plant_code = $data['PLANT'];
						$store_code = $data['STORE_LOCATION'];
						$material_code = $data['MATERIAL_CODE'];
						$matr[] = ['plant_code'=>$plant_code,'store_code'=>$store_code,'material_code'=>$material_code];
						$devArr = array_unique($devArr);

						if(!in_array($material_code,$devArr) || count($devArr) > 1){
						   $status =0;
						   Log::info('eseal ARR:-');
						   Log::info($devArr);
						   throw new Exception('Materials mismatched');				  			
						}

					}

					   Log::info('yyyyyyyyyyyyyyyyy');
					
					$xml = '<?xml version="1.0" encoding="utf-8" ?>
					<REQUEST>
						<DATA>
							<INPUT TOKEN="'.$erpToken.'" ESEALKEY="'.$this->getRand().'" />    
							<SUMMARY>';
								$x =0;
                                                                $qty = 0;
								$xx= array();
								foreach($uomArray as $uom){								   

								   if(1 == 1){
									$x ++;

									$xx[$uom['MATERIAL_CODE']] = $uom['ITEM_CATEG'];
                                                                        $mainQty = $uom['QTY'];
						
									
									//$xx1[] = ['batch_no'=>$item->batch_no,'cnt'=>$x,'material_code'=>$item->material_code];
									$y =0;
									$batchArray = array();
                                   
                                   $deliver2 =  array_values($deliver);
                                   $deliver = $deliver2; 
                                   $count = count($deliver);

                                   
                                  
								   for($i=0;$i < $count;$i++){
                                 
                                      


								   	
									if($deliver[$i]->material_code == $uom['MATERIAL_CODE']){

									  if($deliver[$i]->qty == 1){
										$y++;

										$batch_no = $deliver[$i]->batch_no;
                                                                                Log::info($batch_no);

										unset($deliver[$i]);
                                             if(isset($batchArray[$batch_no])){
                                      $batchArray[$batch_no] = ['QTY'=>$batchArray[$batch_no]['QTY'] + 1,'ITEM_CATEG'=>$uom['ITEM_CATEG']];
                                    } 
                                    else{
                                      $batchArray[$batch_no] = ['QTY'=>1,'ITEM_CATEG'=>$uom['ITEM_CATEG']];
                                    }
                                       }
                                     else{

                                      $batch_no = $deliver[$i]->batch_no;
                                      Log::info('inside else');
                                      Log::info($deliver[$i]->qty);
                                      Log::info($mainQty);

                                   if((int)$deliver[$i]->qty > (int)$mainQty){
                                        Log::info('inside 1st');
                                        $deliver[$i]->qty = $deliver[$i]->qty - $mainQty;
                                        $qty = $mainQty;
                                        $mainQty = 0;
                                   }
                               else{
                                   if((int)$deliver[$i]->qty < (int)$mainQty){
                                        Log::info('inside 2nd');
                                         $qty = $deliver[$i]->qty;
                                       Log::info('eyyyyyyyyyyyxxxxx');
                                         unset($deliver[$i]);
                                       Log::info('oyyyyyyyyyyyyeeeee');
                                       $mainQty = (int)$mainQty - (int)$qty;
                                   }
                                   else{

                                      Log::info('inside 3rd equal to condition');
                                       $qty = $deliver[$i]->qty;
                                         unset($deliver[$i]);
                                       $mainQty = (int)$mainQty - (int)$qty;



                                   }

                              }
                                //   if((int)$deliver[$i]->qty = (int)$qty){
                                //       Log::info('inside 3rd equal to condition');
                                //       $qty = $deliver[$i]->qty;
                                //         unset($deliver[$i]); 
                                //   }

                                    Log::info('out of all conditions');
                                    Log::info($deliver);
									$y += $qty;                                        									

									if(isset($batchArray[$batch_no])){
                                      $batchArray[$batch_no] = ['QTY'=>$batchArray[$batch_no]['QTY'] + $qty,'ITEM_CATEG'=>$uom['ITEM_CATEG']];
                                    } 
                                    else{
                                      $batchArray[$batch_no] = ['QTY'=>$qty,'ITEM_CATEG'=>$uom['ITEM_CATEG']];
                                    }

                                     }

                                     
									}
								
							if($uom['QTY'] == $y)
								     	break;	      

								   }

								     $deliver2 =  array_values($deliver);
                                     $deliver = $deliver2;                                     
   
								    foreach($batchArray as $key => $value){
                                 $xml .='<DELIVER NO="'.$deliver_no.'" METERIAL_CODE="'.$uom['MATERIAL_CODE'].'" BATCH_NO="'.$key.'" ITEM_CATEG="'.$value['ITEM_CATEG'].'" QUANTITY="'.$value['QTY'].'" PLANT="'.$plant_code.'" STORE="'.$store_code.'" COUNT="'.$x.'" />';                                   
								}
								
								}

							

								}
								$xml .= '</SUMMARY><ITEMS>';
								
								foreach($deliver as $dev1){
                                    if($dev1->is_serializable == 1){  

									foreach($xx1 as $yy){
										if($yy['batch_no'] == $dev1->batch_no && $yy['material_code'] == $dev1->material_code){
											$cnt = $yy['cnt'];
											break;
										}
									}

										$xml .= '<ITEM COUNT="'.$cnt.'" SERIAL_NO="'.$dev1->id.'" />';   
									
									}	
								}
								$xml .= '</ITEMS>';
								$xml .= '</DATA> </REQUEST>'; 
								
								Log::info('XML build:-');
								Log::info($xml);
								//die;

								$cd = '<entry xmlns="http://www.w3.org/2005/Atom" xmlns:m="http://schemas.microsoft.com/ado/2007/08/dataservices/metadata" xmlns:d="http://schemas.microsoft.com/ado/2007/08/dataservices" xml:base="'.$erpUrl.'Z0038_ESEAL_UPDATE_DELIVERY_SRV/">
										<id>'.$erpUrl.'Z0038_ESEAL_UPDATE_DELIVERY_SRV/DELIVERY(\'123\')</id>
										<title type="text">DELIVERY(\'123\')</title>
										<updated>2015-08-10T08:06:09Z</updated>
										<category term="Z0038_ESEAL_UPDATE_DELIVERY_SRV.DELIVERY" scheme="http://schemas.microsoft.com/ado/2007/08/dataservices/scheme" />
										<link href="DELIVERY(\'123\')" rel="self" title="DELIVERY" />
										<content type="application/xml">
										 <m:properties>
										<d:ESEAL_INPUT>"<![CDATA[';

								$cd .= $xml;
								$cd .= ']]>"</d:ESEAL_INPUT>
										<d:ESEAL_OUTPUT />
										 </m:properties>
										</content>
										 </entry>'	;


								$query = DB::table('erp_integration')->where('manufacturer_id',$mfgId);
								$erp = $query->select('web_service_url','token','company_code','web_service_username','web_service_password','sap_client')->get();

								$this->_url = $erp[0]->web_service_url;
								$token = $erp[0]->token;
								$company_code = $erp[0]->company_code;
								$sap_client = $erp[0]->sap_client;

								$erp = $this->roleAccess->getErpDetailsByUserId(Input::get('access_token'));

								if($erp){
									$username = $erp[0]->erp_username;
									$password = $erp[0]->erp_password;
									}
									else{
										throw new Exception('There are no erp username and password');
								}
								



								$method = 'POST';
								$this->_method = 'Z0038_ESEAL_UPDATE_DELIVERY_SRV';
								$this->_method_name = 'DELIVERY';
								//$url = 'http://14.141.81.243:8000/sap/opu/odata/sap/Z0035_CONFIRM_PRODUCTION_ORDER_SRV/PROD?&sap-client=110';
								$url = $this->_url .$this->_method.'/'.$this->_method_name.'?&sap-client='.$sap_client;
								Log::info('SAP start:-');
								$sapStartTime = $this->getTime();
								$response = $this->sapRepo->request($username,$password,$url, $method,null,'xml',2,null,$cd);
								$sapEndTime = $this->getTime();

								$sapProcessTime = ($sapEndTime - $sapStartTime);
								Log::info($response);
								
								$parseData1 = xml_parser_create();
								xml_parse_into_struct($parseData1, $response, $documentValues1, $documentIndex1);
								xml_parser_free($parseData1);
								$documentData = array();
								foreach ($documentValues1 as $data) {
									if(isset($data['tag']) && $data['tag'] == 'D:ESEAL_OUTPUT')
									{
										$documentData = $data['value'];
									}
								}
								if(!empty($documentData)){
								$deXml = simplexml_load_string($documentData);
								$deJson = json_encode($deXml);
								$xml_array = json_decode($deJson,TRUE); 
								Log::info('UPDATE DELIVERY array response:');
								Log::info($xml_array); 
								
								if($xml_array['HEADER']['Status'] == 1){
									DB::commit();
									$status =1;
									DB::table('erp_objects')->where('object_id',$deliver_no)->update(['process_status'=>1]);
									$message ='Stockout done successfully and delivery details updated';
								  
								}
								else{
									$status =0;
									throw new Exception ($xml_array['HEADER']['Message']);
								}
							 }
							 else{
								$status =0;
								throw new Exception ('error from SAP call');
							 }
							}
							else{
								$status =0;
								throw new Exception($response->Message);
							}
						}
					}
					
				  }else{
					throw new Exception('Failed to get customer id for given location id');
				  }
				}catch(Exception $e){
					$status =0;
					Log::error($e->getMessage());
					DB::rollback();
					$message = $e->getMessage();
				}
				stockout:
				$endTime = $this->getTime();
				Log::info(__FUNCTION__.' Finishes execution in '.($endTime-$startTime));
				Log::info(Array('Status'=>$status, 'Message' => $message));
				return json_encode(Array('Status'=>$status, 'Message' =>'Server: '.$message.' eseal_time:'.($endTime - $startTime).' sap process time'.$sapProcessTime));
			}


public function SyncStockOutBackup(){
	//Purpose :- Stock Transfer,Sale(with delivery no (or) without delivery no)
	//Tables Involved :- track_history,track_details,eseal_mfgid,locations,tp_data,tp_pdf,tp_attributes,eseal_orders,erp_integration,erp_objects,eseal_bank_mfgid
	//Scenarios Covered :- Checks if a delivey no is passed (or)  a simple stock transfer or sale is processed.
	$startTime = $this->getTime();
	try{
		$status = 0;
		$isSapEnabled = 0 ;
		//$existingQuantity =0;
		$message = 'Failed to do stockout/sale';
		$deliver_no = (int)trim(Input::get('delivery_no'));
		$ids = trim(Input::get('ids'));
		$codes =  trim(Input::get('codes'));
		$mfgId = $this->roleAccess->getMfgIdByToken(Input::get('access_token'));
		$srcLocationId = trim(Input::get('srcLocationId'));    
		$destLocationId = trim(Input::get('destLocationId'));  
		$transitionTime = trim(Input::get('transitionTime'));
		$transitionId = trim(Input::get('transitionId'));
		$tpDataMapping = trim(Input::get('tpDataMapping'));
		$pdfContent = trim(Input::get('pdfContent'));
		$pdfFileName = trim(Input::get('pdfFileName'));
		$sapcode = trim(Input::get('sapcode'));
		$isSapEnabled = trim(Input::get('isSapEnabled'));
		$destinationLocationDetails = trim(Input::get('destinationLocationDetails'));

		if($transitionTime > $this->getDate())
			$transitionTime = $this->getDate();

		
		$method = 'Z036_ESEAL_GET_DELIVERY_DETAIL_SRV'; 
		$method_name ='DELIVER_DETAILS';

		
		Log::info(' === '. print_r(Input::get(),true));
		
		DB::beginTransaction();
		$locationObj = new Locations\Locations();
		$mfgId = $locationObj->getMfgIdForLocationId($srcLocationId);
		if($mfgId){
			$esealTable = 'eseal_'.$mfgId;
			$esealBankTable = 'eseal_bank_'.$mfgId;

		if(!empty($sapcode) && $destLocationId==0 ){
			$destLocationId = $locationObj->getDestinationLocationIdFromSAPCode($sapcode);
			Log::info('=='.$destLocationId);
			if(!$destLocationId){
				if(!empty($destinationLocationDetails) && empty($destLocationId) ){
					$destDetails = json_decode($destinationLocationDetails);
					$destLocation = $locationObj->createOrReturnLocationId($destDetails, $mfgId);
					Log::info('==='.print_r($destLocation,true));
					if($destLocation['Status']==0){
						throw new Exception($destLocation['Message']);
					}
					if($destLocation['Status']==1){
						$destLocationId = $destLocation['Id'];
					}
				}
			}
		}
		////////Checking the number of IDS in esealDB appended to given delivery no.
		$tranName = Transaction::where(['id'=>$transitionId])->pluck('name');
		if(!empty($deliver_no) && $tranName == 'Sales PO' && $isSapEnabled == 1){

           /*$data =['PO'=>$deliver_no];

           $method = 'Z0049_GET_PO_DETAILS_SRV';
		   $method_name = 'PURCHASE';
				
				  //SAP call for getting Delivery Details.
				  $response =  $this->sapCall($mfgId,$method,$method_name,$data,Input::get('access_token'));
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

					$uomArray = array();

					/*$data = $xml_array['DATA']['ITEMS'];
					$quantity = (int)$data['ITEM']['QUANTITY'];
					$uom = $data['ITEM']['UOM'];*/
					
					/*$explodedIds =  explode(',',$ids);

					$data = $xml_array['DATA']['ITEMS'];
					
					if(!array_key_exists('NO', $data['ITEM'])){
                        foreach($data['ITEM'] as $data1){
                        	array_push($uomArray,['mat_code'=>$data1['MAT_CODE'],'uom'=>$data1['UOM'],'qty'=>$data1['QUANTITY']]); 
                        }
					}
					else{
						$data2 = $data['ITEM'];
						array_push($uomArray,['mat_code'=>$data2['MAT_CODE'],'uom'=>$data2['UOM'],'qty'=>$data2['QUANTITY']]);

					}

                    Log::info('UOM ARRAY');
             		Log::info($uomArray);




                    
                    $level_ids=DB::table('tp_attributes as tpa')
                                 ->join('tp_data as tpd','tpd.tp_id','=','tpa.tp_id')
                                 ->where(['attribute_name'=>'Document Number','value'=>$deliver_no])
                                 ->lists('level_ids');

                    
foreach($uomArray as $uom){
              $existingQuantity = 0;
              $quantity = $uom['qty'];
                    if($level_ids){
                    	$query = DB::table($esealTable.' as es')
                    	                   ->join('products as pr','pr.product_id','=','es.pid')
                    	                   ->where('pr.material_code',$uom['mat_code'])
			                               ->where(function($query) use($level_ids){
													 $query->whereIn('es.parent_id', $level_ids)
														   ->orWhereIn('es.primary_id',$level_ids);
											 }
											 )
			                               ->where('level_id',0);
                    	
                      if($uom['uom'] == 'M')
  			              $existingQuantity= $query->sum('pkg_qty');                    	
                      else
			              $existingQuantity= $query->count();			                            
                    }          

                    Log::info('existing :' .$existingQuantity);
					$query = DB::table($esealTable.' as es')
					                       ->join('products as pr','pr.product_id','=','es.pid')
					                       ->where('pr.material_code',$uom['mat_code'])
			                               ->where(function($query) use($explodedIds){
													 $query->whereIn('es.parent_id', $explodedIds)
														   ->orWhereIn('es.primary_id',$explodedIds);
											 }
											 )
			                               ->where('level_id',0);

			         if($uom['uom'] == 'M')
  			              $systemQuantity= $query->sum('pkg_qty');                    	
                      else
			              $systemQuantity= $query->count();	                 

                     Log::info('system :' .$systemQuantity);

			        $systemQuantity = $existingQuantity + $systemQuantity;
			        Log::info('system quantity :'.$systemQuantity.' quantity :'.$quantity. 'MATERIAL_CODE '.$uom['mat_code']);
			        if($systemQuantity > $quantity){
			        	throw new Exception('The Delivery quantity is exceeded by :'. ($systemQuantity - $quantity) .' for material: '.$uom['mat_code']);
			        }*/

			// }                              

  	
  		}

		//////Convert IDS into string and array
		$explodeIds = explode(',', $ids);
		$explodeIds = array_unique($explodeIds);
		
		$idCnt = count($explodeIds);
		$strCodes = '\''.implode('\',\'', $explodeIds).'\'';
                
                ////Check if this request is already processed.
		$alreadyProcessCount = DB::table($this->tpDataTable)->whereIn('level_ids',$explodeIds)->where('tp_id',$codes)->count();
		Log::info($alreadyProcessCount);
		if($alreadyProcessCount == $idCnt){
			return json_encode(['Status'=>1,'Message'=>'Already Processed']);
		}		

		Log::info(print_r(Input::get(),true));
		
		////Check if these ids have already some tp
		$tpCount = DB::table($esealTable.' as eseal')->join($this->trackHistoryTable.' as th', 'eseal.track_id', '=', 'th.track_id')
		->whereIn('primary_id', $explodeIds)
		->where('tp_id','!=', 0)
		->where('dest_loc_id', '>', 0)
		->select('tp_id')
		->distinct()
		->get();

		Log::info(count($tpCount));
		if(count($tpCount)){
			throw new Exception('Some of the codes are already assigned some TPs');
		}
		//Check if TP Id already Used
		$result = DB::table($esealBankTable)->where('id',$codes)->select('id', 'used_status')->get();
		Log::info($result);
		if($result[0]->used_status){
			throw new Exception('TP is already used');
		}

		//Check if TP id is either downloaded or issued
		//$cnt = DB::table($esealBankTable)->where('id',$codes)->where('issue_status',0)->orWhere('download_status',0)->count();
		$cnt = DB::table($esealBankTable)->where('id', $codes)
		->where('issue_status',0)
		->where('download_status',0)
		->count();

		Log::info($cnt);
		if($cnt){
			throw new Exception('Can\'t used as TP.');
		}

		 ///Check if its a valid tp
		//$cnt = DB::table($esealBankTable)->where('id',$codes)->where('issue_status',1)->orWhere('download_status',1)->count();
		$cnt1 = DB::table($esealBankTable)->where('id', $codes)
		->where(function($query){
			$query->where('issue_status',1);
			$query->orWhere('download_status',1);
		})->count();

		Log::info($cnt1);
		if(!$cnt1){
			throw new Exception('Not a valid TP.');
		} 
		//Check if all codes exists in db
		$result = DB::table($esealTable)->whereIn('primary_id',$explodeIds)->count();
		Log::info('===='.print_r($result,true));
		if($idCnt != $result){
			throw new Exception('Some of the codes not exists in database');
		}

                $result = DB::table($esealTable)
                              ->where(function($query) use($explodeIds){
                        $query->whereIn('parent_id',$explodeIds);
                        $query->orWhereIn('primary_id',$explodeIds);
                              })->where('is_active',0)->where('level_id',0)
                              ->count();
                Log::info('====blocked iots count'.print_r($result,true));
                if($result){
                        throw new Exception('Some of the codes are blocked');
                }


		//////////CHECK IF ALL THE IDS HAVE SAME SOURCE LOCATION ID As SUPPLIED
		$transitCnt = DB::table($esealTable.' as eseal')->join($this->trackHistoryTable.' as th', 'eseal.track_id', '=', 'th.track_id')
		->whereIn('primary_id', $explodeIds)
		->select('src_loc_id','dest_loc_id')->groupBy('src_loc_id','dest_loc_id')->get();
		Log::info($transitCnt);
		if(count($transitCnt)>1){
			throw new Exception('Some of the codes are available with different location');
		}
		if(count($transitCnt) == 1){
			if($transitCnt[0]->dest_loc_id>0){
				throw new Exception('Some of the codes are available in-transit');   
			}
                         if($transitCnt[0]->src_loc_id != $srcLocationId){
                                throw new Exception('Some of the codes are available in different location');
                        }

		}

		if(!empty($tpDataMapping)){
			$status = $this->mapTPAttributes($codes, $esealTable, $srcLocationId, $tpDataMapping, $transitionTime);
			if(!$status){
				throw new Exception('Failed during mapping TP Attributes');
			}
			$this->checkNUpdateOrder($tpDataMapping);
		}      

		//$trackResult = $this->trackUpdate();
		/*$trackResult = $this->saveStockIssue($codes, $ids, $srcLocationId, $destLocationId, $transitionTime, $transitionId);
		Log::info(gettype($trackResult));*/
		
		$status = $this->saveTPData($codes, $srcLocationId, $destLocationId, $pdfFileName, $ids, $transitionTime, $pdfContent,$mfgId);
		if(!$status){
			throw new Exception('Failed during saving TP data');
		}

		//$trackResult = $this->trackUpdate();
		$trackResult = $this->saveStockIssue($codes, $ids, $srcLocationId, $destLocationId, $transitionTime, $transitionId);
		Log::info(gettype($trackResult));
		$trackResultDecode = json_decode($trackResult);
		Log::info(print_r($trackResultDecode, true));
		if(!$trackResultDecode->Status){
			throw new Exception($trackResultDecode->Message);
		}
		try{
			DB::table($esealBankTable)->whereIn('id', array($codes))->update(Array(
				'used_status'=>1,
				'level'=>9,
				'location_id' => $srcLocationId,
                                'utilizedDate'=> $this->getDate()
				));		
		}catch(PDOException $e){
			throw new Exception($e->getMessage());
		}
		
		$status = 1;
		$message = 'Stock out done successfully';

		//Deleting the tp from partial_transactions table as the tp is processed completely.
		DB::table('partial_transactions')->where('tp_id',$codes)->delete();
		
		if(empty($deliver_no) || $isSapEnabled ==0 || $tranName=='Sales PO'){
			DB::commit();
			goto stockout;
		}
		
		if($status){
			//Checking if we need to process a delivery no or not.
			if($deliver_no){
			    $erpToken = DB::table('erp_integration')->where('manufacturer_id',$mfgId)->pluck('token');
			    $erpUrl = DB::table('erp_integration')->where('manufacturer_id',$mfgId)->pluck('web_service_url');

				$data =['DELIVERY'=>$deliver_no];
				
				  //SAP call for getting Delivery Details.
				  $response =  $this->sapCall($mfgId,$method,$method_name,$data,Input::get('access_token'));
				  Log::info('GET DELIVERY SAP response:-');
				  Log::info($response);
				  
				  $response = json_decode($response);
				  if($response->Status){
					$response =  $response->Data;

					$parseData1 = xml_parser_create();
					xml_parse_into_struct($parseData1, $response, $documentValues1, $documentIndex1);
					xml_parser_free($parseData1);
					$documentData = array();
					foreach ($documentValues1 as $data) {
						if(isset($data['tag']) && $data['tag'] == 'D:GET_DELIVER')
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
					Log::info('GET DELIVER array response:-');
					Log::info($xml_array);
					$data = $xml_array['DATA']['ITEMS'];
					
					if(!array_key_exists('NO', $data['ITEM'])){
                        foreach($data['ITEM'] as $data1){
                        	$uomArray[$data1['MATERIAL_CODE']] = $data1['UOM']; 
                        }
					}
					else{
						$data2 = $data['ITEM'];
						$uomArray[$data2['MATERIAL_CODE']] = $data2['UOM'];

					}

                    Log::info('UOM ARRAY');
             		Log::info($uomArray);

             		$deliver1 = array();
					

					foreach($uomArray as $key => $value){


					if($value == 'M'){	

					
                    $deliver1 =  DB::table('eseal_'.$mfgId.' as es')
										->join('products','products.product_id','=','es.pid')
										->where('es.level_id',0)
										->where('products.product_type_id',8003)
										->where(function($query) use($explodeIds){
													 $query->whereIn('es.parent_id', $explodeIds)
														   ->orWhereIn('es.primary_id',$explodeIds);
											 }
											 )
										->where('products.material_code',$key)
										->groupBy('batch_no')
										->get([DB::raw('primary_id as id'),DB::raw('sum(pkg_qty) as qty'),'batch_no','material_code','products.is_serializable']);					        

                    
                    }
                    else{
					$deliver1 =  DB::table('eseal_'.$mfgId.' as es')
										->join('products','products.product_id','=','es.pid')
										->where('es.level_id',0)
										->where('products.product_type_id',8003)
										->where(function($query) use($explodeIds){
													 $query->whereIn('es.parent_id', $explodeIds)
														   ->orWhereIn('es.primary_id',$explodeIds);
											 }
											 )
										->where('products.material_code',$key)
										->groupBy('batch_no','primary_id')
										->get([DB::raw('primary_id as id'),DB::raw('1 as qty'),'batch_no','material_code','products.is_serializable']);					        
                    }

                    $deliver[] = $deliver1;
                    

					}
                    
                    Log::info('materialsssss:-');
                    Log::info($deliver);
					
					$itemArr = array();

					foreach ($deliver as $arr) {

                     foreach($arr as $item){

                     	array_push($itemArr,$item);

                     }
						
					}

					$deliver = $itemArr;



					//foreach($deliver as $del){
					//	$deliver2[] = $del[0];
					//}

					//for($i=0;$i < count($deliver); $i++){
					//	$deliver2[] = $deliver[$i];
					//}

					//$deliver = $deliver2;

					////$deliver = $deliver[0];

					//$deliver =  array_merge($deliver1,$deliver2);

					Log::info('System Materials:-');
					Log::info($deliver);
					foreach($deliver as $dev){
						$devArr[] = trim($dev->material_code);
					}
					$matArr = array();
					if(!array_key_exists('NO', $data['ITEM'])){
						foreach($data['ITEM'] as $data1){
							foreach($data1 as $key=>$value){
						if(is_array($value) && empty($value)){
							$data1[$key] = '';
						}
					}
							$plant_code = $data1['PLANT'];
							$store_code = $data1['STORE_LOCATION'];
							$material_code = $data1['MATERIAL_CODE'];

							if(!in_array($material_code,$matArr)){
							$matArr[] = trim($material_code);
							$matr[] = ['plant_code'=>$plant_code,'store_code'=>$store_code,'material_code'=>$material_code];
						}
						
					}
					Log::info('eseal Unique materials:-');
					Log::info(array_unique($devArr));
					Log::info('erp Unique materials:-');
					Log::info(array_unique($matArr));
					/*if(array_unique($devArr) != array_unique($matArr)){
						$status =0;
						throw new Exception('Some of the materials are missing');
					
					}	*/			  
						
					}
					else{
						 $data = $data['ITEM'];
						 
						foreach($data as $key=>$value){
							
							if(is_array($value) && empty($value)){
							$data[$key] = '';
						}

						}
						
						$plant_code = $data['PLANT'];
						$store_code = $data['STORE_LOCATION'];
						$material_code = $data['MATERIAL_CODE'];
						$matr[] = ['plant_code'=>$plant_code,'store_code'=>$store_code,'material_code'=>$material_code];
						$devArr = array_unique($devArr);

						if(!in_array($material_code,$devArr) || count($devArr) > 1){
						   $status =0;
						   Log::info('eseal ARR:-');
						   Log::info($devArr);
						   throw new Exception('Materials mismatched');				  			
						}

					}

					   
					
					$xml = '<?xml version="1.0" encoding="utf-8" ?>
					<REQUEST>
						<DATA>
							<INPUT TOKEN="'.$erpToken.'" ESEALKEY="'.$this->getRand().'" />    
							<SUMMARY>';
								$x =0;
								$xx= array();
								foreach($deliver as $item){
								   
								   if(!isset($xx[$item->material_code]) || ( isset($xx[$item->material_code]) && $xx[$item->material_code] != $item->batch_no) ){
									$x ++;

									$xx[$item->material_code] = $item->batch_no;
									$xx1[] = ['batch_no'=>$item->batch_no,'cnt'=>$x];
									$y =0;

                                  
								   foreach($deliver as $dd){
								   	if($dd->qty == 1){
									if($dd->batch_no == $item->batch_no && $dd->material_code == $item->material_code){
										$y++;
									}
								}
								else{
                                                                     if($dd->batch_no == $item->batch_no && $dd->material_code == $item->material_code)
									$y = $dd->qty;
								}
								   }

									$xml .='<DELIVER NO="'.$deliver_no.'" METERIAL_CODE="'.$item->material_code.'" BATCH_NO="'.$item->batch_no.'" QUANTITY="'.$y.'" PLANT="'.$plant_code.'" STORE="'.$store_code.'" COUNT="'.$x.'" />';                                   
								}
								}
								$xml .= '</SUMMARY><ITEMS>';
								
								foreach($deliver as $dev1){
                                 if($dev1->is_serializable == 1){  

									foreach($xx1 as $yy){
										if($yy['batch_no'] == $dev1->batch_no){
											$cnt = $yy['cnt'];
											break;
										}
									}

										$xml .= '<ITEM COUNT="'.$cnt.'" SERIAL_NO="'.$dev1->id.'" />';   
									
									}	
								}
								$xml .= '</ITEMS>';
								$xml .= '</DATA> </REQUEST>'; 
								
								Log::info('XML build:-');
								Log::info($xml);
								//die;

								$cd = '<entry xmlns="http://www.w3.org/2005/Atom" xmlns:m="http://schemas.microsoft.com/ado/2007/08/dataservices/metadata" xmlns:d="http://schemas.microsoft.com/ado/2007/08/dataservices" xml:base="'.$erpUrl.'"Z0038_ESEAL_UPDATE_DELIVERY_SRV/">
										<id>'.$erpUrl.'Z0038_ESEAL_UPDATE_DELIVERY_SRV/DELIVERY(\'123\')</id>
										<title type="text">DELIVERY(\'123\')</title>
										<updated>2015-08-10T08:06:09Z</updated>
										<category term="Z0038_ESEAL_UPDATE_DELIVERY_SRV.DELIVERY" scheme="http://schemas.microsoft.com/ado/2007/08/dataservices/scheme" />
										<link href="DELIVERY(\'123\')" rel="self" title="DELIVERY" />
										<content type="application/xml">
										 <m:properties>
										<d:ESEAL_INPUT>"<![CDATA[';

								$cd .= $xml;
								$cd .= ']]>"</d:ESEAL_INPUT>
										<d:ESEAL_OUTPUT />
										 </m:properties>
										</content>
										 </entry>'	;


								$query = DB::table('erp_integration')->where('manufacturer_id',$mfgId);
								$erp = $query->select('web_service_url','token','company_code','web_service_username','web_service_password','sap_client')->get();

								$this->_url = $erp[0]->web_service_url;
								$token = $erp[0]->token;
								$company_code = $erp[0]->company_code;
								$sap_client = $erp[0]->sap_client;

								$erp = $this->roleAccess->getErpDetailsByUserId(Input::get('access_token'));

								if($erp){
									$username = $erp[0]->erp_username;
									$password = $erp[0]->erp_password;
									}
									else{
										throw new Exception('There are no erp username and password');
								}
								



								$method = 'POST';
								$this->_method = 'Z0038_ESEAL_UPDATE_DELIVERY_SRV';
								$this->_method_name = 'DELIVERY';
								//$url = 'http://14.141.81.243:8000/sap/opu/odata/sap/Z0035_CONFIRM_PRODUCTION_ORDER_SRV/PROD?&sap-client=110';
								$url = $this->_url .$this->_method.'/'.$this->_method_name.'?&sap-client='.$sap_client;
								Log::info('SAP start:-');
								$response = $this->sapRepo->request($username,$password,$url, $method,null,'xml',2,null,$cd);
								Log::info($response);
								
								$parseData1 = xml_parser_create();
								xml_parse_into_struct($parseData1, $response, $documentValues1, $documentIndex1);
								xml_parser_free($parseData1);
								$documentData = array();
								foreach ($documentValues1 as $data) {
									if(isset($data['tag']) && $data['tag'] == 'D:ESEAL_OUTPUT')
									{
										$documentData = $data['value'];
									}
								}
								if(!empty($documentData)){
								$deXml = simplexml_load_string($documentData);
								$deJson = json_encode($deXml);
								$xml_array = json_decode($deJson,TRUE); 
								Log::info('UPDATE DELIVERY array response:');
								Log::info($xml_array); 
								
								if($xml_array['HEADER']['Status'] == 1){
									DB::commit();
									$status =1;
									DB::table('erp_objects')->where('object_id',$deliver_no)->update(['process_status'=>1]);
									$message ='Stockout done successfully and delivery details updated';
								  
								}
								else{
									$status =0;
									throw new Exception ($xml_array['HEADER']['Message']);
								}
							 }
							 else{
								$status =0;
								throw new Exception ('error from SAP call');
							 }
							}
							else{
								$status =0;
								throw new Exception($response->Message);
							}
						}
					}
					
				  }else{
					throw new Exception('Failed to get customer id for given location id');
				  }
				}catch(Exception $e){
					$status =0;
					Log::error($e->getMessage());
					DB::rollback();
					$message = $e->getMessage();
				}
				stockout:
				$endTime = $this->getTime();
				Log::info(__FUNCTION__.' Finishes execution in '.($endTime-$startTime));
				Log::info(Array('Status'=>$status, 'Message' => $message));
				return json_encode(Array('Status'=>$status, 'Message' =>'Server: '.$message));
			}





private function checkNUpdateOrder($tpDataMapping){
	  $attributes = json_decode($tpDataMapping);
	  $orderNumber = '';
	  if(json_last_error() == JSON_ERROR_NONE){
			Log::info(print_r($attributes, true));
			  foreach($attributes as $key => $value){
				if( strtolower($key) == 'order_no'){
					$orderNumber = $value;
				}
				if(!empty($orderNumber)){
					DB::table('eseal_orders')->where('order_number', $orderNumber)->update(Array('order_status_id'=>17003));
					return true;
				}
			  }
			  //Log::info(print_r($attributes, true));    
	  }
	  return false;
}



///GETS Called from syncStockOut
public function saveStockIssue($codes, $ids, $srcLocationId, $destLocationId, $transitionTime, $transitionId){
	$startTime = $this->getTime();
  try{
	Log::info(' === '. print_r(Input::get(),true));
	  $status = 0;
	  $message = 'Failed to update track info';


	  if(!is_numeric($srcLocationId) || !is_numeric($destLocationId) || !is_numeric($transitionId)){
		throw new Exception('Some of the parameter is not numeric');
	  }
	  if(!is_string($codes) || empty($codes)){
		throw new Exception('Codes cannot be empty'); 
	  }
	  if(is_numeric($destLocationId) && $destLocationId==0){
		throw new Exception('Invalid destination location id');
	  }

	  $locationObj = new Locations\Locations();
	  $mfgId = $locationObj->getMfgIdForLocationId($srcLocationId);
	  
	  $esealTable = 'eseal_'.$mfgId;

	  $transactionObj = new TransactionMaster\TransactionMaster();
	  $transactionDetails = $transactionObj->getTransactionDetails($mfgId, $transitionId);

	  Log::info(print_r($transactionDetails, true));

	  //DB::beginTransaction();

	  if($transactionDetails){
		$srcLocationAction = $transactionDetails[0]->srcLoc_action;
		$destLocationAction = $transactionDetails[0]->dstLoc_action;
		$inTransitAction = $transactionDetails[0]->intrn_action;
	  }else{
		throw new Exception('Unable to find the transaction details');
	  }
		
	  $explodedIds = explode(',', $ids);
	  $explodedIds = array_unique($explodedIds);

	  Log::info('SrcLocAction : ' . $srcLocationAction.' , DestLocAction: '. $destLocationAction.', inTransitAction: '. $inTransitAction);
	  
	  
	  Log::info(__LINE__);
		
	  if($srcLocationAction==-1 && $destLocationAction==0 && $inTransitAction==1){//////////////////For stock out
		$trakHistoryObj = new TrackHistory\TrackHistory();
		//Log::info(var_dump($trakHistoryObj));
		
		try{
			Log::info('Destination Location:');
			Log::info($destLocationId);
			
			$lastInrtId = DB::table($this->trackHistoryTable)->insertGetId( Array(
				'src_loc_id'=>$srcLocationId, 'dest_loc_id'=>$destLocationId, 
				'transition_id'=>$transitionId, 'tp_id'=> $codes, 'update_time'=>$transitionTime));
			Log::info($lastInrtId);

			$maxLevelId = 	DB::table($esealTable)
								->whereIn('parent_id', $explodedIds)
								->orWhereIn('primary_id', $explodedIds)->max('level_id');

//Component Trackupdating

			$res = DB::table($esealTable)->where('level_id', 0)
							->where(function($query) use($explodedIds){
								$query->whereIn('primary_id',$explodedIds);
								$query->orWhereIn('parent_id',$explodedIds);
							})->lists('primary_id');
								
			if(!empty($res)){
				
				$attributeMaps =  DB::table('bind_history')->whereIn('eseal_id',$res)->distinct()->lists('attribute_map_id');

				$componentIds =  DB::table('attribute_mapping')->whereIn('attribute_map_id',$attributeMaps)->where('attribute_name','Stator')->lists('value');
				
				if(!empty($componentIds)){
						$componentIds = array_filter($componentIds);
						$explodedIds = array_merge($explodedIds,$componentIds);
				}

			}
//End Of Component Trackupdating

			if(!$this->updateTrackForChilds($esealTable, $lastInrtId, $explodedIds, $maxLevelId)){
				throw new Exception('Exception occured during track updation');
			}
			/*DB::table($esealTable)->whereIn('primary_id', )
				  ->orWhereIn('parent_id', $explodedIds)
				  ->update(Array('track_id' => $lastInrtId));	*/
			Log::info(__LINE__);
			$sql = 'INSERT INTO  '.$this->trackDetailsTable.' (code, track_id) SELECT primary_id, '.$lastInrtId.' FROM '.$esealTable.' WHERE track_id='.$lastInrtId;
			DB::insert($sql);
			DB::table($this->trackDetailsTable)->insert(array('code'=> $codes, 'track_id'=>$lastInrtId));
			Log::info(__LINE__);
			
		}catch(PDOException $e){
			Log::info($e->getMessage());
			throw new Exception('SQlError during track update');
		}
	  }
	  $status = 1;
	  $message = 'Stock  updated successfully';
	  //DB::commit();        
			//Log::info(__LINE__);
	}catch(Exception $e){
		Log::info($e->getMessage());
		//DB::rollback();        
		$message = $e->getMessage();
	}
	$endTime = $this->getTime();
	Log::info(__FUNCTION__.' Finishes execution in '.($endTime-$startTime));

	return json_encode(Array('Status'=>$status, 'Message' => $message));      
}


private function updateTrackForChilds($esealTable, $lastInrtId, $explodedIds, $maxLevelId){
	try{
		DB::table($esealTable)
			->whereIn('parent_id', $explodedIds)
			->orWhereIn('primary_id', $explodedIds)
			->update(Array('track_id' => $lastInrtId));

		$res = DB::table($esealTable)
			->whereIn('parent_id', $explodedIds)
			->orWhereIn('primary_id', $explodedIds)
			->select(DB::raw('primary_id'))->get();
		if($maxLevelId>0 && count($res)>0){
			$explodedIds1 = Array();
			foreach($res as $val){
				array_push($explodedIds1, $val->primary_id);
			}
			$explodedIds1 = array_diff($explodedIds1, $explodedIds);
			$maxLevelId = 	DB::table($esealTable)
				->whereIn('parent_id', $explodedIds1)
				->orWhereIn('primary_id', $explodedIds1)->max('level_id');
			return $this->updateTrackForChilds($esealTable, $lastInrtId, $explodedIds1, $maxLevelId);
		}
	}catch(PDOException $e){
		Log::info($e->getMessage());
		return FALSE;	
	}
	return TRUE;
}


private function saveTPData($codes, $srcLocationId, $destLocationId, $pdfFileName, $ids, $transitionTime, $pdfContent,$mfgId){
	$startTime = $this->getTime();
	$status = TRUE;
	  try{
		//DB::beginTransaction();
		try{
		/*DB::table($this->tpDetailsTable)->insert(Array(
		  'tp_id'=>$codes, 'src_loc_id'=>$srcLocationId, 'dest_loc_id'=>$destLocationId, 'pdf_file'=>$pdfFileName, 'modified_date'=>$transitionTime
		  ));*/
		$splidIds = explode(',', $ids);
                $splidIds = array_unique($splidIds);
		foreach($splidIds as $id){
		  DB::table($this->tpDataTable)->insert(Array(
			'tp_id'=>$codes, 'level_ids'=>$id
			));
		}
		if(!empty($pdfContent)){
		  DB::table($this->tpPDFTable)->insert(Array(
			'tp_id'=>$codes, 'pdf_content'=>$pdfContent,'pdf_file'=>$pdfFileName
			));
		}
		/*else{

		$mfg_name = DB::table('eseal_customer')->where('customer_id',$mfgId)->pluck('brand_name');
		$tot = array();
		$tp = $codes;
		$ids1 = explode(',',$ids);//DB::table('tp_data')->where('tp_id',$tp)->lists('level_ids');
		Log::info('EXPLODED:');
		Log::info($ids1);
		$batch_no = DB::table('eseal_'.$mfgId)
						->whereIn('primary_id',$ids1)
						->distinct()
						->get(['batch_no']);

		 foreach($batch_no as $batch){
		 
			$pack = DB::table('eseal_'.$mfgId)->whereIn('parent_id',$ids1)->groupBy('parent_id')->take(1)->get([DB::raw('count(distinct(primary_id)) as cnt')]);
			$qty = DB::table('eseal_'.$mfgId)
						   ->where('batch_no',$batch->batch_no)
						   ->where(function($query) use($ids1){
							  $query->whereIn('parent_id', $ids1)
									->orWhereIn('primary_id',$ids1);
							}
							)
						   ->get([DB::raw('count(distinct(primary_id)) as cnt')]);
			$set = DB::table('eseal_'.$mfgId.' as es')
							->join('products as pr','pr.product_id','=','es.pid')    	               
							->where('batch_no',$batch->batch_no)
							->whereIn('primary_id',$ids1)
							->groupBy('es.batch_no')
							->select([DB::raw('group_concat(primary_id) as id'),'es.batch_no','pr.name','pr.mrp'])
							->get();
			$set[0]->qty = $qty[0]->cnt;
			  if($pack){
			  $set[0]->pack = $pack[0]->cnt; 
			  }
			  else{
				$set[0]->pack = 0; 
			  }
			array_push($tot,$set);        
					}

		$th = DB::table('track_history as th')
				   ->join('locations as ls','ls.location_id','=','th.src_loc_id')
				   ->join('locations as ls1','ls1.location_id','=','th.dest_loc_id')
				   ->join('transaction_master as tr','tr.id','=','th.transition_id')
				   ->where('tp_id',$tp)
				   ->get(['ls.location_name as src','ls1.location_name as dest','ls.location_address as src_name','ls1.location_address as dest_name','th.tp_id','tr.name','th.update_time']);

		  if(!empty($th))
		  {
			$view = View::make('pdf', ['manufacturer' =>$mfg_name,'tp'=>$th[0]->tp_id,'status'=>$th[0]->name,'datetime'=>$th[0]->update_time,'src_name'=>$th[0]->src_name,'dest_name'=>$th[0]->dest_name,'src'=>$th[0]->src,'dest'=>$th[0]->dest,'tot'=>$tot]);
			$data = (string) $view;
			$pdfContent =  base64_encode($data);
		}
		  DB::table($this->tpPDFTable)->insert(Array(
			'tp_id'=>$codes, 'pdf_content'=>$pdfContent,'pdf_file'=>$pdfFileName
			));
		}*/
		
	  }catch(PDOException $e){
		  throw new Exception($e->getMessage());          
	  }
	  //DB::commit();
	}catch(Exception $e){
		//DB::rollback();
	  Log::error($e->getMessage());

	  $status = FALSE;
	} 
	$endTime = $this->getTime();
	Log::info(__FUNCTION__.' Finishes execution in '.($endTime-$startTime));

	return $status;
}





/*private function assignTpId($explodeIds, $codes, $esealTable){
try{
	  DB::table($esealTable)->whereIn('primary_id', $explodeIds)->update(Array('tp_id'=> $codes));  
	  DB::table($esealTable)->whereIn('parent_id', $explodeIds)->update(Array('tp_id'=> $codes));  
  }catch(PDOException $e){
	return FALSE;
  }
  return TRUE;
}*/





private function mapTPAttributes($codes, $esealTable, $srcLocationId, $tpDataMapping, $transitionTime){
	$startTime = $this->getTime();
	$status = TRUE;
  try{
		if(!empty($codes) && !empty($srcLocationId)){
	  $attributes = json_decode($tpDataMapping);
	  if(json_last_error() == JSON_ERROR_NONE){
		  try{
			Log::info(print_r($attributes, true));
			  foreach($attributes as $key => $value){
				Log::info($key.' == '.$value.' == '.gettype($value));
				if(!empty($value)){
				  try{
					$attributeData = DB::table($this->attributeTable)->where('attribute_code', $key)->first();

				  }catch(PDOException $e){
					Log::error($e->getMessage());
					throw new Exception($e->getMessage());
				  }
				  if(!empty($attributeData->attribute_id)){
					DB::table($this->TPAttributeMappingTable)->insert(Array(
					  'tp_id'=>$codes,'attribute_id'=> $attributeData->attribute_id, 
					  'attribute_name'=> $attributeData->name, 'value'=> $value, 'location_id'=> $srcLocationId, 'update_time'=> $transitionTime
					  ));
				  }
				}
			  }
		  }catch(PDOException $e){
			Log::error($e->getMessage());
			throw new Exception($e->getMessage());
		  }
			  //Log::info(print_r($attributes, true));    
	  }else{
		  Log::error('Attributes are not in json format');
		  throw new Exception('Attributes are not in json format');
	  }
	}else{
	  Log::error('TP attribute mapping parameter missing');
	  throw new Exception('TP attribute mapping parameter missing');
	}

  }catch(Exception $e){
	Log::error($e->getMessage());
	$status = FALSE;
  }
	$endTime = $this->getTime();
	Log::info(__FUNCTION__.' Finishes execution in '.($endTime-$startTime));

	return $status;
}

public function downloadCodes($qty,$mfgId,$lid)
{
	$esealBankTable = 'eseal_bank_'.$mfgId;

	try
	{
		$blocked_date = date('Ymdhis');
		//Log::info('BlockDate=='.$blocked_date);

		
		$microtime = microtime(true);
		//Log::info('blocked microSeconds=='.$microtime);
		$microtime = explode('.', $microtime);
		
		$blocked_millis = $microtime[0];
		$blocked_micro = $microtime[1];
		$blocked_date = $blocked_date.$blocked_micro;

		//echo $blocked_date.$blocked_millis.$blocked_micro ; die;
		$download_token = DB::table('download_flag')->insertGetId(array('update_time'=>date('Y-m-d H:i:s')));

		//Log::info($download_token) ; 
		DB::table($esealBankTable)->where(array('download_token'=>0))
		    ->take($qty)
		    ->update(array('location_id'=>$lid,'download_status'=>1,'download_token'=>$download_token));
		//Log::info('download_token update into bank');
		$result = DB::table($esealBankTable)->select('id')
				  ->where(array('download_token'=>$download_token))
				  ->orderBy('serial_id','asc')->take($qty)->get();

		//Log::info('get result using download token');
		//Log::info(print_r($result,true));
	}
	catch(PDOException $e)
	{
		throw new Exception($e->getMessage());

		return $e->getMessage();
	}

	if(!empty($result)){
		return $result;
	}			
}


public function DownloadEsealByLocationId()
{
    

	$startTime = $this->getTime();
	try
	{
		$status = 0;
		$message = '';
		$po_number = rtrim(ltrim(Input::get('po_number')));
		$lid = trim(Input::get('srcLocationId'));
		$qty = trim(Input::get('qty'));
		//$transitionTime = Input::get('transitionTime');
		$transitionTime = $this->getDate();
		$attributes = trim(Input::get('attributes'));
		$packingValues = trim(Input::get('packingValues'));
		$ignoreMultiPackingForPo = trim(Input::get('ignoreMultiPackingForPo'));
		$level = trim(Input::get('level'));
		$transitionId = trim(Input::get('transitionId'));
		$unpackedItems = trim(Input::get('unpackedItems'));
		$download_token = '';	
		$str = '';
		$data = array();
		$locationObj = new Locations\Locations();
		$mfgId = $locationObj->getMfgIdForLocationId($lid);
		Log::info(__FUNCTION__.' === '. print_r(Input::get(),true));
		$str1 ='';
		if(!empty($po_number) && $level ==0)
		{
//			Log::info('check1');
			$exists = DB::table('eseal_'.$mfgId)->where(['po_number'=>$po_number,'level_id'=>0])->select('primary_id')->get();			
			if(!empty($exists) && empty($ignoreMultiPackingForPo))
			{
				if(!$unpackedItems){

				foreach($exists as $row){
					  $str1 .= $row->primary_id.',';
				}
				$str1 = RTRIM($str1, ',');
				$status =2;
				$message = 'Data already exists for the given PO number';

			}
			else{
			$unpacked = DB::table('eseal_'.$mfgId)
							 ->where(['po_number'=>$po_number,'level_id'=>0])
							 ->where('parent_id','=',0)
							 ->select('primary_id')
							 ->get();
			if($unpacked){
				foreach($unpacked as $unpack){
					 $str1 .= $unpack->primary_id.',';
				}
				$str1 = RTRIM($str1, ',');
				$message = 'Retrieved unpacked items for the given PO number';
			} 
			else{
				$message = 'All the items in the PO number are packed';
			}                
			 $status =3;
		   }			        
				return json_encode(['Status'=>$status,'Message'=>'Server: '.$message,'Codes'=>$str1,'Token'=>$download_token]);
			
			}
			 
		}

		DB::beginTransaction();
		if(!empty($mfgId))
		{
			if(!empty($level) && $level >0 && !empty($po_number))
			{
			   try
			   {           	
					$data = ['PORDER'=>$po_number];
					$method = 'Z030_ESEAL_GET_PORDER_DETAILS_SRV';
					$method_name = 'GET_PORDER_DETAILS';
					$response = $this->sapCall($mfgId,$method,$method_name,$data,Input::get('access_token'));
					Log::info('SAP response:-');
					Log::info($response);
					$response = json_decode($response);
					if($response->Status)
					{
						$result = $response->Data;
						$parseData1 = xml_parser_create();
						xml_parse_into_struct($parseData1, $result, $documentValues1, $documentIndex1);
						xml_parser_free($parseData1);
						$documentData = array();
						foreach ($documentValues1 as $data) 
						{
							if(isset($data['tag']) && $data['tag'] == 'D:PORDER_DATA')
							{
								$documentData = $data['value'];
							}
						}
						if(empty($documentData))
						{
							throw new Exception('Error from ERP call');
						}
						$deXml = simplexml_load_string($documentData);
						$deJson = json_encode($deXml);
						$xml_array = json_decode($deJson,TRUE);      
						Log::info($xml_array);

						$status = $xml_array['HEADER']['Status'];

						if($status == 1)
						{
							$material_code = $xml_array['DATA']['FINISHED_MATERIAL']['ITEM']['MATERIAL_CODE'];
							$pid = Products::where('material_code',$material_code)->pluck('product_id');
						}
						else
						{
							throw new Exception($xml_array['HEADER']['Message']);
						}
					}
					else
					{
						throw new Exception($response->Message);
					}
					$request = Request::create('scoapi/bindMapWithEseals', 'POST', array('module_id'=> Input::get('module_id'),'access_token'=>Input::get('access_token'),'parentQty'=>$qty,'level'=>$level,'attributes'=>$attributes,'transitionTime'=>$transitionTime,'transitionId'=>$transitionId,'pid'=>$pid,'po_number'=>$po_number));
					$originalInput = Request::input();//backup original input
					Request::replace($request->input());
					Log::info($request->input());
					$response = Route::dispatch($request)->getContent();//invoke API
					$response1 = json_decode($response);
					if(!$response1->Status)
					{
						throw new Exception($response1->Message);	            
					}
					else
					{
						$status =1;
						$message = $response1->Message;
						$data = $response1->Data;
						DB::commit();
					}
				}
				catch(Exception $e)
				{
					DB::rollback();
					$status =0;
					$message = $e->getMessage();
				}                                
				return json_encode(['Status'=>$status,'Message'=>'Server: '.$message,'Data'=>$data,'Codes'=>'','Token'=>$download_token]);
			}
			$esealBankTable = 'eseal_bank_'.$mfgId;
			if(is_numeric($qty) && $qty>0)
			{ 
				try
				{
					// Log::info('check1');
					// //$result = DB::select('SELECT id from '.$esealBankTable.' where used_status=0 and issue_status = 0 order by serial_id limit '.$qty);
					// $result = DB::table($esealBankTable)->select('id')->where(array('used_status'=>'0', 'issue_status'=>'0', 'download_status'=>'0'))->orderBy('serial_id','asc')->take($qty)->get();
					// Log::info('check2');
					// Log::info(print_r($result,true));
					$download_token = DB::table('download_flag')->insertGetId(['update_time'=>date('Y-m-d H:i:s'),'user_id'=>0]);

					Log::info($download_token) ; 
					DB::table($esealBankTable)->where(array('download_token'=>0))
					    ->take($qty)
					    ->update(array('location_id'=>$lid,'download_status'=>1,'download_token'=>$download_token));
					Log::info('download_token update into bank');
					$result = DB::table($esealBankTable)->select('id')
							  ->where(array('download_token'=>$download_token))
							  ->orderBy('serial_id','asc')->take($qty)->get();
					Log::info('check2');
					Log::info(print_r($result,true));
				}
				catch(PDOException $e)
				{
					throw new Exception($e->getMessage());
				}

				if(count($result) && count($result)==$qty)
				{
				  $idArr = Array();
				  foreach($result as $row){
					  $str .= $row->id.',';
					  array_push($idArr, $row->id);
				}
				$str = RTRIM($str, ',');

				$status = 1;
				$message = 'Codes found';
				if(empty($po_number))
				{
					DB::commit();
				}
				$newStr = '\''.str_replace(',', '\',\'', $str).'\'';
				try
				{

						//DB::statement('UPDATE '.$esealBankTable.' SET download_status = 1, location_id = '.$lid.' WHERE id in (' . implode(',', array_map('intval', $idArr)). ')');  
					foreach($idArr as $id){
						DB::table($esealBankTable)->where('id', $id)->update(['download_status'=>1,'location_id'=>$lid]);
					}
				}
				catch(PDOException $e)
				{
					throw new Exception($e->getMessage());
				}
				//DB::commit();

			  if(!empty($po_number))
			  {
					$request = Request::create('scoapi/BindEseals', 'POST', array('module_id'=> Input::get('module_id'),'access_token'=>Input::get('access_token'),'ids'=>$str,'srcLocationId'=>$lid,'po_number'=>$po_number,'attributes'=>$attributes,'transitionTime'=>$transitionTime,'packingValues'=>$packingValues,'ignoreMultiPackingForPo'=>$ignoreMultiPackingForPo));
					$originalInput = Request::input();//backup original input
					Request::replace($request->input());
					Log::info($request->input());
					$response = Route::dispatch($request)->getContent();//invoke API
					$response = json_decode($response);
					if($response->Status)
					{
						$status =1;
						$message = $response->Message;
						if(!empty($str1))
						{
							$str = $str1;
						}
				
					}
					else
					{
						throw new Exception($response->Message);
					}
				} 
			}
			else
			{
			  throw new Exception('Codes of given qty not available');
			}
		}
		else
		{
			throw new Exception('Invalid qty for downloading codes');
		}
	}
	else
	{
		throw new Exception('Invalid location id');
	}
 }
 catch(Exception $e)
 {
	$str = '';
	$status =0;
	DB::rollback();
	Log::error($e->getMessage());
	$message = $e->getMessage();
 }
$endTime = $this->getTime();
Log::info(__FUNCTION__.' Finishes execution in '.($endTime-$startTime));
Log::info(['Status' => $status, 'Message'=> $message, 'Codes' => $str,'Token'=>$download_token]);
return json_encode(['Status' => $status, 'Message' =>'Server: '.$message, 'Codes' => $str,'Token'=>$download_token]);
}



public function GetTPDetailsByIdTesting(){
	$startTime = $this->getTime();
	try{
		$status = 0;
		$message = '';
		$flag =0;
		Log::info(__FUNCTION__.' === '. print_r(Input::get(),true));
		$locationId = trim(Input::get('srcLocationId'));
		$tpList = trim(Input::get('tpIds'));
		$tpIds = explode(',', $tpList);
		$dataArray = array();

		$locationObj = new Locations\Locations();
		if(!is_numeric($locationId) || empty($locationId))
			throw new Exception('Location params is missing');

		$mfgId = $locationObj->getMfgIdForLocationId($locationId);
		
		$finalArray = Array();
		$esealDataArray = Array();
		$highestLevelIds = Array();


		if($mfgId){
			$esealTable = 'eseal_'.$mfgId;
			foreach($tpIds as $tps){
				$tpcnt = DB::table($esealTable.' as eseal')->join($this->trackHistoryTable.' as th', 'eseal.track_id', '=', 'th.track_id')
				  ->where('th.tp_id','=', $tps)->count();
				  
			Log::info($tpcnt);
			if($tpcnt){
				$flag =1;
				$trackHistoryData = DB::table($this->trackHistoryTable)->where('tp_id',$tps)->orderBy('update_time')->take(1)->get();

				if(count($trackHistoryData)){

					$srcLocName = DB::table($this->locationsTable)->where('location_id', $trackHistoryData[0]->src_loc_id)->select('location_name')->get();
					$destLocName = DB::table($this->locationsTable)->where('location_id', $trackHistoryData[0]->dest_loc_id)->select('location_name')->get();
					$transitInfo= Array(
									'ID' => $tps, 'Status' => 1, 
									'source' => $srcLocName[0]->location_name, 'destination' => $destLocName[0]->location_name, 
									'source_id' => $trackHistoryData[0]->src_loc_id, 'destination_id'=> $trackHistoryData[0]->dest_loc_id 
							);

					$tpAttributes = DB::table('tp_attributes')->where('tp_id',$tps)->get(['attribute_name','value']);

					$select = DB::table($esealTable.' as eseal')
						->join('tp_data as tpd','tpd.level_ids','=','eseal.parent_id')
						->join($this->trackHistoryTable.' as th', 'eseal.track_id','=', 'th.track_id')
						->join('products as pr','pr.product_id','=','eseal.pid')
						->join('master_lookup as ml','ml.value','=','pr.product_type_id')
						->where('ml.name','Finished Product')
						->where('tpd.tp_id', $tps)
						//->where('eseal.level_id',0)
						->where('th.tp_id',$tps)
						->select(DB::Raw('pr.name as Name,parent_id as HId,count(*) as Qty,eseal.mrp as Mrp,batch_no as Batch'))
						->groupBy('parent_id')
						->groupBy('pid')		
						->get();
						$queries = DB::getQueryLog();
						//print_r(end($queries));exit;
					
					$select2 = DB::table($esealTable.' as eseal')
						->join('tp_data as tpd','tpd.level_ids','=','eseal.primary_id')
						->join($this->trackHistoryTable.' as th', 'eseal.track_id','=', 'th.track_id')
						->join('products as pr','pr.product_id','=','eseal.pid')
						->join('master_lookup as ml','ml.value','=','pr.product_type_id')						
						->where('ml.name','Finished Product')
						->where('tpd.tp_id', $tps)
						->where('eseal.level_id',0)
						->where('th.tp_id',$tps)
						->select(DB::Raw('pr.name as Name,primary_id as HId,count(*) as Qty,eseal.mrp as Mrp,batch_no as Batch'))
						->groupBy('primary_id')
						->groupBy('pid')		
						->get();

				$select =  array_merge($select,$select2);			
				
				//echo 'jj<pre/>';print_r($select);exit;
					//return $select;
					//Log::info($select);

					//$select = array_merge($select1,$select2);
					$pnameArray = array();
					
					if(count($select)){
						foreach($select as $row){
							//$thidCildCnt = $this->getChildCount($row->primary_id, $esealTable, $cnt=0);
							
							/*$primId = DB::table($esealTable)
								->where('track_id',$row->track_id)
								->where('pid',$row->pid)
								->where('level_id',0)
								->pluck('primary_id');*/
							//$highestID = $this->getHighestId($row->primary_id, $esealTable);
							$dataArray[] = Array(
								'Name'=> $row->Name, 'HId'=>$row->HId, 'Qty' => (integer)$row->Qty, 
								'Mrp'=>$row->Mrp, 'Batch'=>$row->Batch,'Exp'=>''
								);
						}

						$request = Request::create('scoapi/GetEsealDataForTpIds', 'POST');
						$tpResult = Route::dispatch($request)->getContent();
						//echo "<pre/>";print_r($tpResult);exit;
						$res1 = json_decode($tpResult);
						if($res1->Status){
							$res1->esealData;
						}
						//Log::info('========================================================='.print_r($res1, true));
					}

					$finalArray[] = Array('TP'=> $transitInfo, 'data'=>$dataArray, 'esealData'=>$res1->esealData,'tpAttributes'=>$tpAttributes);
					//echo "yyyyy<pre/>";print_r($finalArray);exit;
					unset($tpAttributes);
					unset($transitInfo);
					unset($dataArray);
					unset($res1);
				}
			}else{
				$finalArray[] = Array('TP'=>null, 'data'=>null, 'esealData'=>null,'tpAttributes'=>null);
				//echo "hhhhh<pre/>";print_r($finalArray);exit;
			} 
		}
			
		}else{
			throw new Exception('Invalid location id');
		}

  }catch(Exception $e){
	$message = $e->getMessage();
	Log::info($e->getMessage());
  }
	$endTime = $this->getTime();
	Log::info(__FUNCTION__.' Finishes execution in '.($endTime-$startTime));
	if($flag == 1)
	  {
		$status =1;
		$message ='Data retrieved successfully.';        
	  }	  else{
		$message = 'All TpIds are in-valid';
	  }
	  Log::info(Array('Status'=>$status,'Message'=>$message,'Data' => $finalArray));
	return json_encode(['Status'=>$status,'Message' =>'Server: '.$message,'Data' => $finalArray]);
}


public function removeTpData(){
	try{
		Log::info(__FUNCTION__.print_r(Input::get(),true));
		$status =1;
		$message = 'Successfully removed TP Data.';
		$locationId = $this->roleAccess->getLocIdByToken(Input::get('access_token'));
		$mfgId = $this->roleAccess->getMfgIdByToken(Input::get('access_token'));
		$esealTable = 'eseal_'.$mfgId;

		$tp = trim(Input::get('tp_id'));
		$ids = trim(Input::get('ids'));

		if(empty($tp) || empty($ids))
			throw new Exception('Parameters Missing.');
 
        $trackHistoryData = DB::table($this->trackHistoryTable)->where('tp_id',$tp)->orderBy('update_time')->take(1)->get();

        if($trackHistoryData[0]->dest_loc_id == 0)
        	throw new Exception('The TP is already received at some location');

        if($trackHistoryData[0]->src_loc_id != $locationId)
            throw new Exception('The User doesnt have the permission to remove TP Data.');

        $track_id = $trackHistoryData[0]->track_id;

        $explodedIds =  explode(',',$ids);
        $explodedIds = array_unique($explodedIds);

        $codesCnt = count($explodedIds);

        $childCnt = DB::table($esealTable.' as es')
                        ->join('tp_data as tp','tp.level_ids','=','es.primary_id')
                        ->whereIn('primary_id',$explodedIds)
                        ->where('track_id',$track_id)
                        ->groupBy('level_id','track_id')
                        ->get([DB::raw('count(eseal_id) as cnt'),'level_id']);

        if(count($childCnt) > 1 || $childCnt[0]->cnt != $codesCnt)
             throw new Exception('Child Count not matching');


         DB::beginTransaction();

         foreach($explodedIds as $id){

            $lastInrtId = DB::table($this->trackDetailsTable)
                                   ->whereNotIn('track_id',[$track_id])
                                   ->where('code',$id)
                                   ->max('track_id');
            
            $id1 = explode(',',$id);

            if(!$this->updateTrackForChilds($esealTable, $lastInrtId,$id1, $childCnt[0]->level_id))
				throw new Exception('Exception occured during track updation');



			/*DB::table('track_details as td')
			            ->join($esealTable.' as es','es.primary_id','=','td.code')
			            ->where(function($query) use($id){
							  $query->whereIn('primary_id',explode(',',$id))
									->orWhereIn('parent_id',explode(',',$id));
											 }
											 )
			            ->where('td.track_id',$track_id)
			            ->delete();*/


			  $sql = 'delete td.* from track_details td 
			         join '.$esealTable.' es on es.primary_id=td.code 
			         where (primary_id='.$id.' or parent_id='.$id.') 
			         and td.track_id='.$track_id;          
              
  			  DB::statement($sql);       

  			  DB::table('tp_data')->where(['tp_id'=>$tp,'level_ids'=>$id])->delete();
			
           

         }

         DB::commit();

	}
	catch(Exception $e){
		DB::rollback();
		$status = 0;
		$message = $e->getMessage();
	}
	Log::info(['Status'=>$status,'Message'=>$message]);
	return json_encode(['Status'=>$status,'Message'=>$message]);
}

public function GetTPDetailsById(){
	$startTime = $this->getTime();
	try{
		$status = 0;
		$message = '';
		$flag =0;
		Log::info(__FUNCTION__.' === '. print_r(Input::get(),true));
		$locationId = trim(Input::get('srcLocationId'));
		$tpList = trim(Input::get('tpIds'));
		$tpIds = explode(',', $tpList);
		$dataArray = array();

		$locationObj = new Locations\Locations();
		if(!is_numeric($locationId) || empty($locationId))
			throw new Exception('Location params is missing');

		$mfgId = $locationObj->getMfgIdForLocationId($locationId);
		
		$finalArray = Array();
		$esealDataArray = Array();
		$highestLevelIds = Array();


		if($mfgId){
			$esealTable = 'eseal_'.$mfgId;
			foreach($tpIds as $tps){
				$tpcnt = DB::table($esealTable.' as eseal')->join($this->trackHistoryTable.' as th', 'eseal.track_id', '=', 'th.track_id')
				  ->where('th.tp_id','=', $tps)->count();
				  
//			Log::info($tpcnt);
			if($tpcnt){
				$flag =1;
				$trackHistoryData = DB::table($this->trackHistoryTable)->where('tp_id',$tps)->orderBy('update_time')->take(1)->get();

				if(count($trackHistoryData)){

					$srcLocName = DB::table($this->locationsTable)->where('location_id', $trackHistoryData[0]->src_loc_id)->select('location_name')->get();
					$destLocName = DB::table($this->locationsTable)->where('location_id', $trackHistoryData[0]->dest_loc_id)->select('location_name')->get();
					if(empty($destLocName))
						$destLocName[0]->location_name = '';

					$transitInfo= Array(
									'ID' => $tps, 'Status' => 1, 
									'source' => $srcLocName[0]->location_name, 'destination' => $destLocName[0]->location_name, 
									'source_id' => $trackHistoryData[0]->src_loc_id, 'destination_id'=> $trackHistoryData[0]->dest_loc_id 
							);

					$tpAttributes = DB::table('tp_attributes')->where('tp_id',$tps)->get(['attribute_name','value']);

					
						//print_r(end($queries));exit;
					$sql =  'SELECT 
					 p.name AS Name,					
					 e.primary_id AS HId, CAST((
					 SELECT CASE 
					         WHEN COUNT(e1.primary_id) = 0 
								   THEN 
									   case when multiPack=0 then 1 ELSE sum(e.pkg_qty) END
							 ELSE 
							           case when multiPack=0 then COUNT(e1.primary_id)  ELSE sum(e1.pkg_qty) END 
							END
					 FROM eseal_5 e1
					 WHERE e1.parent_id=e.primary_id) AS UNSIGNED) AS Qty, 
					 e.mrp as Mrp,
					 (select update_time from track_history th where e.track_id=th.track_id) as utime,
					 CASE when e.parent_id=0 then "" else e.parent_id end AS lid,
					 cast(e.level_id AS UNSIGNED) as lvl,
					 e.batch_no as Batch                     
					 FROM '.$esealTable.' e
					 INNER JOIN products p ON e.pid=p.product_id
					 INNER JOIN tp_data tp ON tp.level_ids=e.primary_id
					 WHERE	p.product_type_id=8003 AND tp.tp_id='.$tps.' AND e.level_id IN(0,1) group by e.primary_id';
						
				$select = DB::select($sql);
				
				//echo 'jj<pre/>';print_r($select);exit;
					//return $select;
					//Log::info($select);

					//$select = array_merge($select1,$select2);
					$pnameArray = array();
					
					if(count($select)){
						foreach($select as $row){
							//$thidCildCnt = $this->getChildCount($row->primary_id, $esealTable, $cnt=0);
							
							/*$primId = DB::table($esealTable)
								->where('track_id',$row->track_id)
								->where('pid',$row->pid)
								->where('level_id',0)
								->pluck('primary_id');*/
							//$highestID = $this->getHighestId($row->primary_id, $esealTable);
							$dataArray[] = Array(
								'Name'=> $row->Name, 'HId'=>$row->HId, 'Qty' => (integer)$row->Qty, 
								'Mrp'=>$row->Mrp, 'Batch'=>$row->Batch,'Exp'=>'','Lvl' => $row->lvl,
								'lvlid' =>$row->lid,'utime' =>$row->utime
								);
						}

						
						//Log::info('========================================================='.print_r($res1, true));
					}

					$finalArray[] = Array('TP'=> $transitInfo, 'data'=>$dataArray,'tpAttributes'=>$tpAttributes);
					//echo "yyyyy<pre/>";print_r($finalArray);exit;
					unset($tpAttributes);
					unset($transitInfo);
					unset($dataArray);
					
				}
			}else{
				$finalArray[] = Array('TP'=>null, 'data'=>null,'tpAttributes'=>null);
				//echo "hhhhh<pre/>";print_r($finalArray);exit;
			} 
		}
			
		}else{
			throw new Exception('Invalid location id');
		}

  }catch(Exception $e){
	$message = $e->getMessage();
	Log::info($e->getMessage());
  }
	$endTime = $this->getTime();
	Log::info(__FUNCTION__.' Finishes execution in '.($endTime-$startTime));
	if($flag == 1)
	  {
		$status =1;
		$message ='Data retrieved successfully.';        
	  }	  else{
		$message = 'All TpIds are in-valid';
	  }
	  Log::info(Array('Status'=>$status,'Message'=>$message,'Data' => $finalArray));
	return json_encode(['Status'=>$status,'Message' =>'Server: '.$message,'Data' => $finalArray]);
}

public function GetTPDetailsByIdWince(){
	$startTime = $this->getTime();
	try{
		$status = 0;
		$message = '';
		$finalArray = array();
		$flag =0;
		Log::info(__FUNCTION__.' === '. print_r(Input::get(),true));
		$locationId = trim(Input::get('srcLocationId'));
		$tpList = trim(Input::get('tpIds'));
		$delivery_no = trim(Input::get('delivery_no'));
		if(empty($tpList)){
			if(empty($delivery_no))
				throw new Exception('Either TP or Delivery Number must be passed.');
   
			$tpList = DB::table('tp_attributes')
						->where(['attribute_name'=>'Document Number','value'=>$delivery_no])
						->lists('tp_id');
						

			if(!$tpList){
				$tpList = DB::table('tp_attributes')
						->where(['attribute_name'=>'Purchase Order No','value'=>$delivery_no])
						->lists('tp_id');
				if(!$tpList)
				  throw new Exception('There is no TP associated with the given Delivery Number');            		
             } 

             $tpIds = $tpList;   
				
		}
		else{
		 $tpIds[] = $tpList;	
		}
		
		$dataArray = array();

		$locationObj = new Locations\Locations();
		if(!is_numeric($locationId) || empty($locationId))
			throw new Exception('Location params is missing');

		$mfgId = $locationObj->getMfgIdForLocationId($locationId);
		
		$finalArray = Array();
		$esealDataArray = Array();
		$highestLevelIds = Array();


		if($mfgId){
			$esealTable = 'eseal_'.$mfgId;
			foreach($tpIds as $tps){
				$tpcnt = DB::table($esealTable.' as eseal')->join($this->trackHistoryTable.' as th', 'eseal.track_id', '=', 'th.track_id')
				  ->where('th.tp_id','=', $tps)->count();
				  
//			Log::info($tpcnt);
			if($tpcnt){
				$flag =1;
				$trackHistoryData = DB::table($this->trackHistoryTable)->where('tp_id',$tps)->orderBy('update_time')->take(1)->get();

				if(count($trackHistoryData)){

					$srcLocName = DB::table($this->locationsTable)->where('location_id', $trackHistoryData[0]->src_loc_id)->select('location_name')->get();
					$destLocName = DB::table($this->locationsTable)->where('location_id', $trackHistoryData[0]->dest_loc_id)->select('location_name')->get();
					$transitInfo= Array(
									'ID' => $tps, 'Status' => 1, 
									'source' => $srcLocName[0]->location_name, 'destination' => $destLocName[0]->location_name, 
									'source_id' => $trackHistoryData[0]->src_loc_id, 'destination_id'=> $trackHistoryData[0]->dest_loc_id 
							);

					$tpAttributes = DB::table('tp_attributes')->where('tp_id',$tps)->get(['attribute_name','value']);

					
					

				 $sql =  'SELECT 
					 p.name as Name,
					 p.material_code AS MatCode,					
					 e.primary_id AS HId, CAST((
					 SELECT CASE 
					         WHEN COUNT(e1.primary_id) = 0 
								   THEN 
									   case when multiPack=0 then 1 ELSE sum(e.pkg_qty) END
							 ELSE 
							           case when multiPack=0 then COUNT(e1.primary_id)  ELSE sum(e1.pkg_qty) END 
							END
					 FROM '.$esealTable.' e1
					 WHERE e1.parent_id=e.primary_id) AS UNSIGNED) AS Qty
					 FROM '.$esealTable.' e
					 INNER JOIN products p ON e.pid=p.product_id
					 INNER JOIN tp_data tp ON tp.level_ids=e.primary_id
					 WHERE	p.product_type_id=8003 AND tp.tp_id='.$tps.' AND e.level_id IN(0,1) group by e.primary_id';

				$select  = DB::select($sql); 
					$pnameArray = array();
					
					if(count($select)){
						foreach($select as $row){
							
							$dataArray[] = Array(
								'HId'=>$row->HId, 'Qty' => (integer)$row->Qty, 
								'MatCode'=>$row->MatCode,'Name' => $row->Name
								);
						}

						
						
					}

					$finalArray[] = Array('TP'=> $transitInfo, 'data'=>$dataArray,'tpAttributes'=>$tpAttributes);
					//echo "yyyyy<pre/>";print_r($finalArray);exit;
					unset($tpAttributes);
					unset($transitInfo);
					unset($dataArray);
					
				}
			}else{
				$finalArray[] = Array('TP'=>null, 'data'=>null,'tpAttributes'=>null);
				//echo "hhhhh<pre/>";print_r($finalArray);exit;
			} 
		}
			
		}else{
			throw new Exception('Invalid location id');
		}

  }catch(Exception $e){
	$message = $e->getMessage();
	Log::info($e->getMessage());
  }
	$endTime = $this->getTime();
	Log::info(__FUNCTION__.' Finishes execution in '.($endTime-$startTime));
	if($flag == 1)
	  {
		$status =1;
		$message ='Data retrieved successfully.';        
	  }
	  if(empty($delivery_no) && $flag ==0){
		$message = 'All TpIds are in-valid';
	  }

	  Log::info(Array('Status'=>$status,'Message'=>$message,'Data' => $finalArray));
	return json_encode(['Status'=>$status,'Message' =>'Server: '.$message,'Data' => $finalArray]);
}

private function getHighestId($parentId, $esealTable){
	$res = DB::select('SELECT parent_id FROM '.$esealTable.' WHERE primary_id = '.$parentId);
	//Log::info('==========>'.$res[0]->parent_id);
	if($res[0]->parent_id == 0 || empty($res[0]->parent_id)){
		//Log::info('====>>'.$parentId);
		return $parentId;
	}else{
		return $this->getHighestId($res[0]->parent_id, $esealTable);
	}
}

private function getChildCount($parentId, $esealTable, $cnt){
  $res = DB::table($esealTable)->where('parent_id', $parentId)->select(DB::raw('primary_id, level_id'))->get();
  $level = 0;
  foreach($res as $row){
	if($row->level_id==0)
	  $cnt += 1;
	else
	  return $this->getChildCount($row->primary_id, $esealTable, $cnt);
  }
  return $cnt;
}



public function GetEsealDataForTpIds(){
 
	$startTime = $this->getTime();
	try{
		Log::info(__FUNCTION__.' === '. print_r(Input::get(),true));
		$status = 0;
		$message = '';

		$tpList = trim(Input::get('tpIds'));
		$locationId = trim(Input::get('srcLocationId'));

		$locationObj = new Locations\Locations();
		$mfgId = $locationObj->getMfgIdForLocationId($locationId);
		$esealTable = 'eseal_'.$mfgId;
		$tpIDs = explode(',', $tpList);
		$tpDataArray = Array();
		//Log::info($tpIDs);
		foreach($tpIDs as $tp){
			$maxLevelId = DB::table($esealTable.' as eseal')
				->join($this->trackHistoryTable.' as th', 'eseal.track_id','=','th.track_id')
				->where('th.tp_id',$tp)
				->max('level_id');
		//	Log::info($maxLevelId);
			for($i=0; $i<=$maxLevelId; $i++){
				if($i==0){
					$sql = '
						SELECT 
							count(*) qty, pid, primary_id, parent_id, 
							IF(es.mrp,NULL,"") mrp, 
							es.batch_no batch, 
							\'\' as \'expdate\', 
							level_id, eth.update_time, "" warehouse_id, "" pallete_id, "" tp_id, "" zonespace 
						FROM 
							'.$esealTable.' es , '.$this->trackHistoryTable.' eth ,products pr
						WHERE 
						  
							es.track_id=eth.track_id and pr.product_id=es.pid and pr.product_type_id=8003 and
							eth.dest_loc_id = '.$locationId.' and es.level_id = '.$i.' and eth.tp_id = "'.$tp.'"
						GROUP BY 
						  pid, primary_id
						';
					try{
						$result = DB::select($sql);
					}catch(PDOException $e){
						throw new Exception($e->getMessage());
					}

					if(count($result)){
						$pnameArray = Array();
						foreach($result as $row){
							$pname = '';
							if(array_key_exists($row->pid, $pnameArray)){
								$pname = $pnameArray[$row->pid];
							}else{
								$product = new Products\Products();
								$pname = $product->getNameFromId($row->pid);
								//Log::info('22222'.print_r($pname,true));
								$pnameArray[$row->pid] = $pname;           
							}
							if(empty($pname))
								$pname = '';
						  
						$tpDataArray[] = Array('qty'=>$row->qty, 'id'=>$row->primary_id, 'lvlid'=>$row->parent_id, 'lvl'=>$i, 
							'name'=>$pname, 'utime'=>$row->update_time, 'mrp'=>$row->mrp, 'batch'=>$row->batch, 
							'tpid'=>$tp, 'exp'=>$row->expdate);
						}
					}
				}

				if($i>0){
					$sql = '
						SELECT 
							count(*) qty, es.pid, es.parent_id as child, (select parent_id from '.$esealTable.' e1 where es.parent_id=e1.primary_id) as parent,
							IF(es.mrp,NULL,"") mrp, 
							es.batch_no batch, 
							 \'\' as \'expdate\', 
							es.level_id, eth.update_time, "" warehouse_id, "" pallete_id, "" tp_id, "" zonespace 
						FROM 
							'.$esealTable.' es, '.$this->trackHistoryTable.' eth , products pr
						WHERE 
						   
							eth.track_id=es.track_id and pr.product_id=es.pid and eth.dest_loc_id = '.$locationId.' and pr.product_type_id=8003 
							and es.parent_id in (SELECT primary_id FROM '.$esealTable.' es1, '.$this->trackHistoryTable.' th WHERE 
								es1.track_id=th.track_id and th.tp_id = '.$tp.' and es1.level_id='.$i.') 
						GROUP BY 
						  es.pid, es.parent_id
					  ';
					try{
						$result = DB::select($sql);
						//echo 'ki<pre/>';print_r($result);exit;
					}catch(PDOException $e){
						throw new Exception($e->getMessage());
					}
					//echo 'ki<pre/>';print_r($result);exit;
					if(count($result)){
						$pnameArray = Array();
						foreach($result as $row){
							$pname = '';
							if(array_key_exists($row->pid, $pnameArray)){
								$pname = $pnameArray[$row->pid];
							}else{
								$product = new Products\Products();
								$pname = $product->getNameFromId($row->pid);
								//Log::info('22222'.print_r($pname,true));
								$pnameArray[$row->pid] = $pname;           
							}
							if(empty($pname))
								$pname = '';
						  
						$tpDataArray[] = Array(
								'qty'=>$row->qty, 'id'=>$row->child, 'lvlid'=>$row->parent, 'lvl'=>$i, 
								'name'=>$pname, 'utime'=>$row->update_time, 'mrp'=>$row->mrp, 'batch'=>$row->batch, 
								'tpid'=>$tp, 'exp'=>$row->expdate
							);
						}
					}
				}
			}
		}
		if(count($tpDataArray)){
			$status = 1;
			$message = 'TP data found';
		}else{
			$message = 'Unable to find TP data';
		}
	}catch(Exception $e){
		$message = $e->getMessage();
	}
	$endTime = $this->getTime();
	Log::info(__FUNCTION__.' Finishes execution in '.($endTime-$startTime));

	return json_encode(Array('Status'=>$status, 'Message' => $message, 'esealData' => $tpDataArray));
}

public function getParentForChild(){
	try{
		$parent = array();
		$mfg_id = $this->roleAccess->getMfgIdByToken(Input::get('access_token'));
		$child = Input::get('child');
		if(empty($child)){
			throw new exception('Child ID not passed');
		}
		$parent = DB::table('eseal_'.$mfg_id)->where('primary_id',$child)->get(['primary_id','parent_id']);
		if(!empty($parent)){
			$parent = $parent[0]->parent_id;
			if(empty($parent)){
				throw new Exception('Parent does not exist');
			}
			$status =1;
			$message ='Parent successfully retrieved';
		}
		else{
			throw new Exception('Child does not exist');
		}
	}
	catch(Exception $e){
		$status =0;
		$message = $e->getMessage();
	}
	return json_encode(['Status'=>$status,'Message'=>$message,'parent'=>$parent]);
}


      public function GetAllShipments(){
	    $startTime = $this->getTime();
	    try{
		$status = 0;
		$message = '';
		$res = Array();
		Log::info(__FUNCTION__.' === '. print_r(Input::get(),true));
		//$currentLocationId = trim(Input::get('dest_location_id'));
		$currentLocationId = $this->roleAccess->getLocIdByToken(Input::get('access_token'));
		$requestType = trim(Input::get('request_type')); ///////Array('in','out', 'pr')

		//$otherLocationId = trim(Input::get('src_location_id'));
        $otherLocationId = '';
		$tpId = trim(Input::get('tpId'));
		$fromDate = trim(Input::get('fromDate'));
		$toDate = trim(Input::get('toDate'));
		$res1 =  array();
		//Log::info($toDate);
		//Log::info('location:'.$currentLocationId);
		

		$locationObj = new Locations\Locations();

         
		$mfgId = $locationObj->getMfgIdForLocationId($currentLocationId);		
		if($mfgId){
		  $esealTable = 'eseal_'.$mfgId;

			if(strtolower($requestType) == 'in' || strtolower($requestType) == 'pr' ){
				$sql = '
					SELECT 
						tp.id, th.tp_id, pdf_file, src_loc_id, 
						(select location_name from locations ll where ll.location_id=th.src_loc_id) as locationName, th.update_time, \'In Transit\' as status,
						(select value from '.$this->TPAttributeMappingTable.'  tpa where tpa.tp_id=th.tp_id and (tpa.attribute_name="Document Number" or tpa.attribute_name="Purchase Order No") ) as delivery_no,
						"line_items" as line_items
					FROM 
						tp_pdf tp, track_history th
					WHERE 
						tp.tp_id=th.tp_id 
						and th.dest_loc_id='.$currentLocationId;
					if(is_numeric($otherLocationId)){
						$sql .= ' and th.src_loc_id = '.$otherLocationId;
					}
					if($tpId){
						$sql .= ' and th.tp_id = '.$tpId;   
					}
					if($fromDate){
						$sql .= ' and th.update_time >= \''.$fromDate.'\'';      
					}
					if($toDate){
						$sql .= ' and th.update_time <= \''.$toDate.'\'';         
					}
					$sql .= ' group by tp_id';
				$res = DB::select($sql);
				if(!empty($res)){
					$resCount = count($res);

                    	for($i=0;$i < $resCount;$i++){

                    	$isReceived = DB::table($this->trackHistoryTable)->where(['tp_id'=>$res[$i]->tp_id,'dest_loc_id'=>0])->count();	
                    		//$tpArray[] = $shipment->tp_id;
                    	if(empty($isReceived)){
                    		$res1[] = $res[$i];
                    	}

                    	}  

                    	$res = $res1;                  	                     

                    }	
				//this is to test the api control

				if(strtolower($requestType) != 'pr'){
					$sql1 = '
						SELECT 
							tp.id, th.tp_id, pdf_file, src_loc_id, 
							(select location_name from locations ll where ll.location_id=th.src_loc_id) as locationName, th.update_time, \'Receive\' as status ,
							(select value from '.$this->TPAttributeMappingTable.'  tpa where tpa.tp_id=th.tp_id and (tpa.attribute_name="Document Number" or tpa.attribute_name="Purchase Order No")) as delivery_no,
							"line_items" as line_items
						FROM
							tp_pdf tp, track_history th, '.$esealTable.' e 
						WHERE 
							tp.tp_id = th.tp_id and th.track_id=e.track_id and th.src_loc_id = '.$currentLocationId.' and th.dest_loc_id = 0';
					if($tpId){
						$sql1 .= ' and th.tp_id = '.$tpId;   
					}
					if($fromDate){
						$sql1 .= ' and th.update_time >= \''.$fromDate.'\'';      
					}
					if($toDate){
						$sql1 .= ' and th.update_time <= \''.$toDate.'\'';         
					}
					$sql1 .= ' group by tp_id';
					$res = DB::select($sql1);
					

				}

			}
			

			if(strtolower($requestType) == 'out'){
				$sql = '
					SELECT 
						tp.id, th.tp_id, pdf_file, dest_loc_id, 
						(select location_name from locations ll where ll.location_id=th.dest_loc_id) as locationName, th.update_time, tr.name as status,
						(select value from '.$this->TPAttributeMappingTable.'  tpa where tpa.tp_id=th.tp_id and (tpa.attribute_name="Document Number" or tpa.attribute_name="Purchase Order No")) as delivery_no,
						"line_items" as line_items
					FROM 
						tp_pdf tp, track_history th,transaction_master tr 
					WHERE 					   
						tp.tp_id=th.tp_id and tr.id=th.transition_id
						and th.src_loc_id = '. $currentLocationId;
					if(is_numeric($otherLocationId)){
						$sql .= ' and th.dest_loc_id = '.$otherLocationId;
					}else{
						$sql .= ' and th.dest_loc_id > 0 ';
					}
					if($tpId){
						$sql .= ' and th.tp_id = '.$tpId;   
					}
					if($fromDate){
						$sql .= ' and th.update_time >= \''.$fromDate.'\'';      
					}
					if($toDate){
						$sql .= ' and th.update_time <= \''.$toDate.'\'';         
					}
					$sql .= ' group by tp_id';
					$res = DB::select($sql);
				if(!empty($res)){
                    	foreach($res as $shipment){

                    	$isReceived = DB::table($this->trackHistoryTable)->where(['tp_id'=>$shipment->tp_id,'dest_loc_id'=>0])->get(['track_id','transition_id','update_time']);	
                    		//$tpArray[] = $shipment->tp_id;
                    	if(!empty($isReceived)){
                    		$status = DB::table($this->transactionMasterTable)->where('id',$isReceived[0]->transition_id)->pluck('name');

                    		$shipment->status = $status;
                    		$shipment->update_time = $isReceived[0]->update_time;
                    	}

                    	}
                    	//$tpStr = implode(',',$tpArray); 

                    	/*$sql = '
					SELECT 
						tp.id, th.tp_id, pdf_file, dest_loc_id, 
						(select location_name from locations ll where ll.location_id=th.dest_loc_id) as locationName, th.update_time, tr.name as status,
						(select value from '.$this->TPAttributeMappingTable.'  tpa where tpa.tp_id=th.tp_id and (tpa.attribute_name="Document Number" or tpa.attribute_name="Purchase Order No")) as delivery_no 
					FROM 
						tp_pdf tp, track_history th, '.$esealTable.' e,transaction_master tr 
					WHERE 					   
						tp.tp_id=th.tp_id and th.track_id = e.track_id  and tr.id=th.transition_id
						and th.dest_loc_id =0 and th.tp_id in ('.$tpStr.')';*/


                    }	
			}

			//Log::info('======='.count($res).' '.!empty($res));
			if(!empty($res)){

				foreach($res as $re){
					$line_items = DB::table('tp_data as tp')
					                  ->join('eseal_'.$mfgId.' as es','es.primary_id','=','tp.level_ids')
					                  ->join('products as pr','pr.product_id','=','es.pid')
					                  ->leftJoin('uom_classes as uc','uc.id','=','pr.uom_class_id')					                  
					                  ->where('tp_id',$re->tp_id)
					                  ->groupBy('es.pid')
					                  ->get(['pr.material_code as mat_code','pr.name',DB::raw('sum(pkg_qty) as qty'),'uc.uom_code']);
					$re->line_items = $line_items;                  
				}

			  $status = 1;
			  $message = 'Data found succesfully';
			  //Log::info(print_r($res, true));

			}else{
			  throw new Exception('Unable to find any record');
			}
		}else{
		  throw new exception('Unable to find location details');
		}
	}catch(Exception $e){
		$message = $e->getMessage();
	}
	$endTime = $this->getTime();
	Log::info(__FUNCTION__.' Finishes execution in '.($endTime-$startTime));
	Log::info(Array('Status'=>$status, 'Message' => $message, 'shipmentData' => $res));
	return json_encode(Array('Status'=>$status, 'Message' =>'Server: '.$message, 'shipmentData' => $res));
}


public function DownloadShipmentByTP(){
	$startTime = $this->getTime();
	try{
		$status = 0;
		$message = '';

		$locationId = trim(Input::get('srcLocationId'));
		$tpId = trim(Input::get('tpId'));
		$locationObj = new Locations\Locations();
		$mfgId = $locationObj->getMfgIdForLocationId($locationId);
		$pdfData = Array();
		if($mfgId){
			try{
			  $res =  DB::table($this->tpPDFTable)
						->where('tp_id', $tpId)
						->select('pdf_content', 'pdf_file')
						->get();
			}catch(PDOException $e){
				Log::info($e->getMessage());
			  throw new Exception('Exception during query execution');          
			}
			if(count($res)){
			  $status = 1;
			  $message = 'Data found successfully';
			  $pdfData['filename'] = $res[0]->pdf_file;
			  $pdfData['content'] = $res[0]->pdf_content;
			}else{
			  throw new Exception('Data not found');
			}
		}else{
		  throw new Exception('Invalid location id');
		}

  }catch(Exception $e){
	  $message = $e->getMessage();
  }
	$endTime = $this->getTime();
	Log::info(__FUNCTION__.' Finishes execution in '.($endTime-$startTime));

	return json_encode(Array('Status'=>$status, 'Message' =>'Server: '.$message, 'pdfData' => $pdfData)); 
}


public function SaveProductionSummary(){
	$startTime = $this->getTime();
	try{
		$status = 0;
		$message = 'Unable to save production summary';
		$locationId = Input::get('srcLocationId');
		$pid = Input::get('pid');
		$batchNo = Input::get('batch_no');
		$shift = Input::get('shift');
		$productionLine = Input::get('productionLine');
		$primaryCount = Input::get('primaryCnt');
		$secondaryCount = Input::get('secondaryCnt');
		$rejectionCount = Input::get('rejectionCnt');
		$productionDate = Input::get('productionDate');
		$pdfFile = Input::get('pdfFile');
		$pdfContent = Input::get('pdfContent');

		$locationObj = DB::table($this->locationsTable)
					->select('location_id','location_name','manufacturer_id')
					->where('location_id', $locationId)
					->get();

		$mfgId = $locationObj[0]->manufacturer_id;
		$locationName = $locationObj[0]->location_name;

		$prodCollection = DB::table('products')
						->where('product_id',$pid)
						->get();
		$prodName  = $prodCollection[0]->name;

		if(!empty($prodName) && !empty($locationName)){
			try{
				DB::table($this->prodSummaryTable)->insert(Array(
					'mfg_id'=> $mfgId, 'location_id'=>$locationId, 'product_id'=>$pid, 'location_name'=>$locationName,
					'product_name'=>$prodName, 'batch_no'=>$batchNo, 'shift'=>$shift, 'production_line'=>$productionLine,
					'primary_cnt'=>$primaryCount, 'secondary_cnt'=>$secondaryCount, 'rejection_cnt'=>$rejectionCount,
					'production_date'=>$productionDate, 'pdf_file'=>$pdfFile, 'pdf_content'=>$pdfContent
				));  
			}catch(PDOException $e){
				throw new Exception($e->getMessage());
			}
			$status = 1;
			$message = 'Summary saved successfuly';
		}
	}catch(Exception $e){
	  $message = $e->getMessage();
	}
	$endTime = $this->getTime();
	Log::info(__FUNCTION__.' Finishes execution in '.($endTime-$startTime));

	return json_encode(Array('Status'=>$status, 'Message'=>$message));
}


public function ReceiveByTp(){
	$startTime = $this->getTime();
	try{
		$status = 0;
		$message = '';
		$tp = Input::get('tp');
		$locationId = Input::get('location_id');
		//$transitionTime = Input::get('transition_time');
		$transitionTime = $this->getDate();
		$transitionId = Input::get('transition_id');
		$previousTrackId = '';
		$tpArr = explode(',', $tp);
		$missingIds = Input::get('missing_ids');
		$transitIds = Input::get('damage_ids');
		$excess_ids = Input::get('excess_ids');
   		$deliveryNo = Input::get('delivery_no');
   		$store_location = trim(Input::get('store_location'));
		$documentNo = array();
		$materialCodes = ['1500687'];
		
		

		$deliveryNoExists = FALSE;
		$purchaseNoExists = FALSE;
		$xml = Array();

		Log::info(__FUNCTION__.'==>'.print_r(Input::get(),true));
	
		///GET MfgId for geiven Location
		DB::beginTransaction();

		////SAP Delivery No associated with TP
		if(!empty($deliveryNo)){
			$delivery_attribute_id= DB::table($this->attributeTable)->where('attribute_code','document_no')->pluck('attribute_id');
			$purchase_attribute_id= DB::table($this->attributeTable)->where('attribute_code','purchase_no')->pluck('attribute_id');
			$deliveryNoCnt = DB::table('tp_attributes')->whereIn('tp_id', $tpArr)->where(['attribute_id'=>$delivery_attribute_id,'value'=>$deliveryNo])->count();
			if(!$deliveryNoCnt){
				$purchaseNoCnt = DB::table('tp_attributes')->whereIn('tp_id', $tpArr)->where(['attribute_id'=>$purchase_attribute_id,'value'=>$deliveryNo])->count();
				  if(!$purchaseNoCnt)
				     throw new Exception('Given delivery no not exists for passed tp id');
				  else
				  	$purchaseNoExists = TRUE;
			}else{
				$deliveryNoExists = TRUE;				
			}
		}

		$locationObj = new Locations\Locations();
		$mfgId = $locationObj->getMfgIdForLocationId($locationId);

		if($mfgId){
			$esealTable = 'eseal_'.$mfgId;
			$transactionObj = new TransactionMaster\TransactionMaster();
			$transactionDetails = $transactionObj->getTransactionDetails($mfgId, $transitionId);
			if(!count($transactionDetails)){
				throw new Exception('Transition details not found');
			}
			Log::info(print_r($transactionDetails, true));

			$srcLocationAction = $transactionDetails[0]->srcLoc_action;
			$destLocationAction = $transactionDetails[0]->dstLoc_action;
			$inTransitAction = $transactionDetails[0]->intrn_action;
			if($srcLocationAction==0 && $destLocationAction==1 && $inTransitAction==-1){
				$tpTrackIDs = Array();
				foreach($tpArr as $tp){
					try{
						$res = DB::table($this->trackHistoryTable)->where('tp_id', $tp)->orderBy('update_time','desc')->take(1)->get();
					}catch(PDOException $e){
						Log::info($e->getMessage());
						throw new Exception('Error during query exceution');
					}
					
					if(!count($res)){
						throw new Exception('Invalid TP');
					}
					foreach($res as $val){
						if($val->src_loc_id == $locationId && $val->dest_loc_id==0){
							throw new Exception('TP is already received at given location');
						}
						if($val->dest_loc_id != $locationId){
							throw new Exception('TP destination not matches with given location');
						}
						if($transitionTime < $val->update_time)
							throw new Exception('Receive timestamp less than stock transfer timestamp');

						$tpTrackIDs[$tp] = $val->track_id;
					}
				}

				Log::info($tpTrackIDs[$tp]);
				try{
					foreach($tpArr as $tp){
						$srcLocationId = DB::table($this->trackHistoryTable)->where('tp_id', $tp)->pluck('src_loc_id');

						$lastInsertId = DB::table($this->trackHistoryTable)->insertGetId(Array(
							'src_loc_id'=>$locationId,
							'dest_loc_id'=> 0,
							'transition_id' => $transitionId,
							'tp_id'=> $tp,
							'update_time'=> $transitionTime
							));
						DB::table($esealTable)->where('track_id', $tpTrackIDs[$tp])->update(Array('track_id'=>$lastInsertId));
						$sql = 'INSERT INTO  '.$this->trackDetailsTable.' 
						(code, track_id) SELECT primary_id, '.$lastInsertId.' FROM '.$esealTable.' WHERE track_id='.$lastInsertId;
						DB::insert($sql);
						DB::table($this->trackDetailsTable)->insert(Array(
								'code'=> $tp,
								'track_id'=>$lastInsertId
							));

						Log::info('last insert id:'.$lastInsertId);


                        


						if(!empty($transitIds)){
						Log::info('Execution in TRANSIT:');
						$transitionId = DB::table('transaction_master')->where(['manufacturer_id'=>$mfgId,'name'=>'Damaged'])->pluck('id');
						if(!$transitionId)
							throw new Exception('Transaction : Damage not created');

						$request = Request::create('scoapi/UpdateTracking', 'POST', array('module_id'=>Input::get('module_id'),'access_token'=>Input::get('access_token'),'codes'=>$transitIds,'srcLocationId'=>$locationId,'destLocationId'=>0,'transitionTime'=>$transitionTime,'transitionId'=>$transitionId,'internalTransfer'=>0));
						$originalInput = Request::input();//backup original input
						Request::replace($request->input());						
						$response = Route::dispatch($request)->getContent();
						$response = json_decode($response,true);
						if($response['Status'] == 0)
							throw new Exception($response['Message']);

					}
					if(!empty($missingIds)){
						Log::info('Execution in MISSING:');
						$transitionId = DB::table('transaction_master')->where(['manufacturer_id'=>$mfgId,'name'=>'Missing'])->pluck('id');
						if(!$transitionId)
							throw new Exception('Transaction : Missing not created');

						$request = Request::create('scoapi/UpdateTracking', 'POST', array('module_id'=>Input::get('module_id'),'access_token'=>Input::get('access_token'),'codes'=>$excess_ids,'srcLocationId'=>$locationId,'destLocationId'=>0,'transitionTime'=>$transitionTime,'transitionId'=>$transitionId,'internalTransfer'=>0));
						$originalInput = Request::input();//backup original input
						Request::replace($request->input());						
						$response = Route::dispatch($request)->getContent();
						$response = json_decode($response,true);
						if($response['Status'] == 0)
							throw new Exception($response['Message']);

					}
					}
				}catch(PDOException $e){
					Log::info($e->getMessage());
					throw new Exception('Error during query exceution');    
				}
				$status = 1;
				$message = 'TP received succesfully';
				
				
               //if($deliveryNoExists || $purchaseNoExists){
				DB::table('partial_transactions')->where('tp_id',$tp)->delete();

                    $vehicleId = DB::table($this->attributeTable)->where('attribute_code','vehicle_no')->pluck('attribute_id');
                    $invoiceId = DB::table($this->attributeTable)->where('attribute_code','docket_no')->pluck('attribute_id');

                    $XML_DYNAMIC ='';

				    $vehicleNo = DB::table('tp_attributes')->whereIn('tp_id', $tpArr)->where('attribute_id',$vehicleId)->pluck('value');

                    $invoiceNo = DB::table('tp_attributes')->whereIn('tp_id', $tpArr)->where('attribute_id',$invoiceId)->pluck('value');

					if(empty($vehicleNo))
						$vehicleNo ='';
					if(empty($invoiceNo))
						$invoiceNo ='';
						
                    $XML_DYNAMIC  =  $vehicleNo.',';
                    $XML_DYNAMIC .= $invoiceNo;
                 //}   

				/////////////CODE FOR PSUHING DELIVERY DATA TO SAP BACK
				if($deliveryNoExists){
					$erpToken = DB::table('erp_integration')->where('manufacturer_id',$mfgId)->pluck('token');
			        $erpUrl = DB::table('erp_integration')->where('manufacturer_id',$mfgId)->pluck('web_service_url');

					 
                    $method = 'Z036_ESEAL_GET_DELIVERY_DETAIL_SRV'; 
		            $method_name ='DELIVER_DETAILS';

                    $data =['DELIVERY'=>$deliveryNo];
				
				  //SAP call for getting Delivery Details.
				  $response =  $this->sapCall($mfgId,$method,$method_name,$data,Input::get('access_token'));
				  Log::info('GET DELIVERY SAP response:-');
				  Log::info($response);
				  
				  $response = json_decode($response);
				  if($response->Status){
					$response =  $response->Data;

					$parseData1 = xml_parser_create();
					xml_parse_into_struct($parseData1, $response, $documentValues1, $documentIndex1);
					xml_parser_free($parseData1);
					$documentData = array();
					foreach ($documentValues1 as $data) {
						if(isset($data['tag']) && $data['tag'] == 'D:GET_DELIVER')
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
					Log::info('GET DELIVER array response:-');
					Log::info($xml_array);
					$data = $xml_array['DATA']['ITEMS'];
					$uomArray = array();

					if(!array_key_exists('NO', $data['ITEM'])){
                        foreach($data['ITEM'] as $data1){
                        	$is_serializable = Products::where('material_code',$data1['MATERIAL_CODE'])->pluck('is_serializable');
                        array_push($uomArray,['material_code'=>$data1['MATERIAL_CODE'],'uom'=>$data1['UOM'],'is_serializable'=>$is_serializable,'store_location'=>$data1['STORE_LOCATION']]);  
                        }
					}
					else{
						$data2 = $data['ITEM'];
						$is_serializable = Products::where('material_code',$data2['MATERIAL_CODE'])->pluck('is_serializable');
				array_push($uomArray,['material_code'=>$data2['MATERIAL_CODE'],'uom'=>$data2['UOM'],'is_serializable'=>$is_serializable,'store_location'=>$data2['STORE_LOCATION']]);
					}

                    Log::info('UOM ARRAY');
             		Log::info($uomArray);
             	}
                    else{
                       throw new Exception($response->Message);
                    }

   					$str1 = '';
					$str2 = '';
					$str3 = '';
					$str4 = '';

					$srcLocationSapCode = $locationObj->getSAPCodeFromLocationId($srcLocationId);
					$destLocationSapCode = $locationObj->getSAPCodeFromLocationId($locationId);
					if(empty($destLocationSapCode)){
						throw new Exception('Unable to find erp code for given location');
					}


					$storageLocationTypes = $this->getStorageLocationTypeValues();
					$storageLocationTypesArr = Array();
					foreach($storageLocationTypes as $val){
						$storageLocationTypesArr[$val->value] = $val->name;
					}
					
					Log::info($storageLocationTypesArr);
					$arrayOfIdsForStorageLocatioons = Array('missing'=>$missingIds, 'damage'=>$transitIds);
					$storageLocationsNameMapping = Array('missing'=>'Transit Loss', 'damage'=>'Blocked', );

					foreach($arrayOfIdsForStorageLocatioons as $key=>$values){
						if(!empty($values)){
							$explodeIdsArr = explode(',', $values);
							$businessUnitIds = $this->getBusinessUnitIds($explodeIdsArr, $mfgId);///// GET BUSINESS UNIT IDS for PASSED CODES
							Log::info('$businessUnitIds: '. print_r($businessUnitIds,true));
							$uniqueBusinessUnitIds = array_unique($businessUnitIds); /// 
							foreach($uniqueBusinessUnitIds as $buid){
								$getIdsForBusUnitId = array_keys($businessUnitIds, $buid);  /////// GET IDS for BUSINESSUNITID
								Log::info('$getIdsForBusUnitId:$buid'.print_r($getIdsForBusUnitId,true));
								Log::info('Key'. $key.' = '.print_r($storageLocationsNameMapping[$key],true));
								$storageLocId = $this->getStorageLocationIdForGivenBusinessUnitId($buid, $mfgId, $locationId, array_search($storageLocationsNameMapping[$key], $storageLocationTypesArr) );
								if(!empty($storageLocId)){
									$this->makeReceive($storageLocId, $getIdsForBusUnitId, $transitionId, $transitionTime, $mfgId);
								}

								$sapCodeForLocation = $locationObj->getSAPCodeFromLocationId($storageLocId);		
								if(empty($sapCodeForLocation)){
									throw new Exception('Unable to find erp code for given location');
								}
								$DeliveryStoreId = $this->getStorageLocationIdForGivenBusinessUnitId($buid, $mfgId, $srcLocationId, array_search('Un-Restricted', $storageLocationTypesArr) );
								$DeliveryStoreLocSapCode = $locationObj->getSAPCodeFromLocationId($DeliveryStoreId);		
								Log::info('$storageLocId:$sapCodeForLocation:'.$storageLocId.':'.$sapCodeForLocation);
								Log::info('$DeliveryStoreId:$DeliveryStoreLocSapCode:'.$DeliveryStoreId.':'.$DeliveryStoreLocSapCode);
								if($key=='missing'){
									$details = $this->_getProductDetailsForMissingORBlockedIds($getIdsForBusUnitId, $esealTable);
									if($details){
										foreach($details as $val){
											$str1 .= '<INPUT DELIVERY="'.$deliveryNo.'" GRN_PLANT="'.$destLocationSapCode.'" GRN_STOR_LOC="'.$sapCodeForLocation.'" DELIVERY_PLANT="'.$srcLocationSapCode.'" DELIVERY_STOR_LOC="'.$DeliveryStoreLocSapCode.'" MATERIAL="'.$val->material_code.'" BATCH_NO="'.$val->batch_no.'" QUANTITY="1" STOCK_TYPE="02" SERIAL_NO="'.$val->primary_id.'"/>';
										}
									}			
								}
								if($key=='damage'){
									$details = $this->_getProductDetailsForMissingORBlockedIds($getIdsForBusUnitId, $esealTable,$uomArray);
									if($details){
										foreach($details as $value){
											foreach($value as $val){
											
											if($val->is_serializable == 1)
                            	              $primary_id = $val->primary_id;
                                            else
                            	              $primary_id = '';
											
											$str2 .= '<INPUT DELIVERY="'.$deliveryNo.'" GRN_PLANT="'.$destLocationSapCode.'" GRN_STOR_LOC="'.$sapCodeForLocation.'" DELIVERY_PLANT="'.$srcLocationSapCode.'" DELIVERY_STOR_LOC="'.$DeliveryStoreLocSapCode.'" MATERIAL="'.$val->material_code.'" BATCH_NO="'.$val->batch_no.'" QUANTITY="'.$val->qty.'" STOCK_TYPE="03" SERIAL_NO="'.$primary_id.'"/>';
										  }
										}
									}			
								}

								DB::table($esealTable)
								      ->whereIn('primary_id',$explodeIdsArr)
								      ->orWhereIn('parent_id',$explodeIdsArr)
								      ->update(['storage_location'=>$sapCodeForLocation]);

							}/////END OF FOREACH
							
						}/////////END OF IF
					}/////////END OF FOREACH
					
					


					$xml = '<?xml version="1.0" encoding="utf-8" ?><REQUEST><DATA><INPUT TOKEN="'.$erpToken.'" ESEAL_KEY="'.$this->getRand().'" XML_DYNAMIC="'.$XML_DYNAMIC.'"/><GRN_DATA>';
					$details = $this->_getProductDetailsForReceivedTrackId($lastInsertId, $esealTable,$uomArray);
					if($details){
						foreach($details as $value){
                            foreach ($value as $val){
							Log::info('BUSINESS UNIT ID:'.$val->business_unit_id);
							$GRN_STORE_ID = $this->getStorageLocationIdForGivenBusinessUnitId($val->business_unit_id, $mfgId, $locationId,'25001');
							$GRNStoreLocSap = $locationObj->getSAPCodeFromLocationId($GRN_STORE_ID);		
                            
                            if(!empty($store_location))
                            	$GRNStoreLocSap = $store_location;

							$DELIVERY_STORE_ID = $this->getStorageLocationIdForGivenBusinessUnitId($val->business_unit_id, $mfgId, $srcLocationId, '25001' );
							$DeliveryStoreLocSap = $locationObj->getSAPCodeFromLocationId($DELIVERY_STORE_ID);		
							
							Log::info('$GRN_STORE_ID:$sapCodeForLocation:'.$GRN_STORE_ID.':'.$GRNStoreLocSap);
							Log::info('$DELIVERY_STORE_ID:$DeliveryStoreLocSap:'.$DELIVERY_STORE_ID.':'.$DeliveryStoreLocSap);
                            if($val->is_serializable == 1)
                            	$primary_id = $val->primary_id;
                            else
                            	$primary_id = '';
							$xml .= '<INPUT DELIVERY="'.$deliveryNo.'" GRN_PLANT="'.$destLocationSapCode.'" GRN_STOR_LOC="'.$GRNStoreLocSap.'" DELIVERY_PLANT="'.$srcLocationSapCode.'" DELIVERY_STOR_LOC="'.$val->store_location.'" MATERIAL="'.$val->material_code.'" BATCH_NO="'.$val->batch_no.'" QUANTITY="'.$val->qty.'" STOCK_TYPE="01" SERIAL_NO="'.$primary_id.'"/>';
						   }
						}

						DB::table($esealTable)
								->where('track_id',$lastInsertId)
								->update(['storage_location'=>$GRNStoreLocSap]);
					}

					if(!empty($str1))
						$xml .= $str1;
					if(!empty($str2))
						$xml .= $str2;

					
					$xml .= '</GRN_DATA></DATA></REQUEST>';
					Log::info($xml);
					
					$cd = '<entry xmlns="http://www.w3.org/2005/Atom" xmlns:m="http://schemas.microsoft.com/ado/2007/08/dataservices/metadata" xmlns:d="http://schemas.microsoft.com/ado/2007/08/dataservices" xml:base="'.$erpUrl.'Z0044_ESEAL_CREATE_GRN_STO_SRV/">
							<id>'
							.$erpUrl.'Z0044_ESEAL_CREATE_GRN_STO_SRV/GRN(\'123\')
							</id>
							<title type="text">GRN(\'123\')</title>
							<updated>2015-08-14T10:19:23Z</updated>
							<category term="Z0044_ESEAL_CREATE_GRN_STO_SRV.GRN" scheme="http://schemas.microsoft.com/ado/2007/08/dataservices/scheme"/>
							<link href="GRN(\'123\')" rel="self" title="GRN"/>
							<content type="application/xml">
							<m:properties>
							<d:DOCUMENT_NO/>
							<d:YEAR/>
							<d:ESEAL_INPUT>"<![CDATA[';
								$cd .= $xml;
								$cd .= ']]>"</d:ESEAL_INPUT>
										<d:ESEAL_OUTPUT />
										 </m:properties>
										</content>
										 </entry>';	
					
					$method ='POST';
					$this->_method = 'Z0044_ESEAL_CREATE_GRN_STO_SRV';
					$this->_method_name = 'GRN';
					$cred =DB::table('erp_integration')->where('manufacturer_id', $mfgId)->first(['web_service_url','web_service_username','web_service_password','sap_client']);
					$erp = $this->roleAccess->getErpDetailsByUserId(Input::get('access_token'));	
					if($erp){
					$username = $erp[0]->erp_username;
					$password = $erp[0]->erp_password;
					}
					else{
						throw new Exception('There are no erp username and password');
					}
					$sap_client = $cred->sap_client;
					$this->_url = $cred->web_service_url;

					$url = $this->_url .$this->_method.'/'.$this->_method_name.'?&sap-client='.$sap_client;
					Log::info($url);
					Log::info('SAP start');
					$response = $this->sapRepo->request($username,$password,$url, $method,null,'xml',2,null,$cd);
					Log::info('SAP response:-'.$response);

					$parseData1 = xml_parser_create();
					xml_parse_into_struct($parseData1, $response, $documentValues1, $documentIndex1);
					xml_parser_free($parseData1);
					$documentData = array();

					foreach ($documentValues1 as $data) {
						if(isset($data['tag']) && $data['tag'] == 'D:ESEAL_OUTPUT')
						{
							$documentData = $data['value'];
						}
						
					}

					if(!empty($documentData)){
					$deXml = simplexml_load_string($documentData);
					$deJson = json_encode($deXml);
					$xml_array = json_decode($deJson,TRUE);
					
					if($xml_array['HEADER']['Status'] == 1){
					 
						foreach ($documentValues1 as $data) {
						if(isset($data['tag']) && $data['tag'] == 'D:DOCUMENT_NO')
						{
							$documentNo = $data['value'];
						}
						
					   }

					   $message =  $message.' with GR Document no: '.$documentNo;

					 /* $levelIds = DB::table('tp_data')
									  ->where('tp_id',$tp)
									  ->get(['level_ids']);
					  foreach($levelIds as $id){
						  $childs[] = $id->level_ids;
						}			  
					  $childIds = DB::table('eseal_'.$mfgId)
									  ->whereIn('primary_id',$childs)
									  ->orWhereIn('parent_id',$childs)
									  ->groupBy('pid')
									  ->select(['pid',DB::raw('group_concat(distinct(primary_id)) as ids')])
									  ->get();
					   $attributes = json_encode(['grn_no'=>$documentNo]);

									  foreach($childIds as $ids){

									  $request = Request::create('BindEsealsWithAttributes', 'POST', array('module_id'=> Input::get('module_id'),'access_token'=>Input::get('access_token'),'pid'=>$ids->pid,'ids'=>$ids->ids,'attributes'=>$attributes,'srcLocationId'=>$locationId));
									  $originalInput = Request::input();//backup original input
									  Request::replace($request->input());
									  $res = Route::dispatch($request)->getContent();
									  $res = json_decode($res);
										   if($res->Status){
										   $message = 'TP received succesfully,GRN created in ERP and GRN binded';
										   }
										   else{
											throw new Exception($res->Message);
										   }        
									  }*/
								   }
					else{
						throw new Exception('TP not received GRN not created in ERP.'.$xml_array['HEADER']['Message']);
					}

					}
					else{						
						throw new Exception('TP not received  ERP call error occurred');
					}

				}
				if($purchaseNoExists){ /////////////CODE FOR PUSHING PO DATA TO SAP BACK

					$erp= DB::table('erp_integration')->where('manufacturer_id',$mfgId)->get(['token','web_service_url']);
			        $erpUrl = $erp[0]->web_service_url;
			        $erpToken = $erp[0]->token;
			        
   					$data =['PO'=>$deliveryNo];

                    $method = 'Z0049_GET_PO_DETAILS_SRV';
		            $method_name = 'PURCHASE';
				
					  //SAP call for getting Delivery Details.
					  $response =  $this->sapCall($mfgId,$method,$method_name,$data,Input::get('access_token'));
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
					
					if(!array_key_exists('NO', $data['ITEM'])){
                        foreach($data['ITEM'] as $data1){

                        	

                        	$uomArray[$data1['MAT_CODE']] = ['uom'=>$data1['UOM'],'plant'=>$data1['PLANT'],'storage_location'=>$data1['STO_LOC']]; 
                        }
					}
					else{
						$data2 = $data['ITEM'];

						

                            
						$uomArray[$data2['MAT_CODE']] = ['uom'=>$data2['UOM'],'plant'=>$data2['PLANT'],'storage_location'=>$data2['STO_LOC']]; 

					}

                    Log::info('UOM ARRAY');
             		Log::info($uomArray);

             		$systemMatCode = DB::table($esealTable.' as es')
             		                    ->join('products as pr','pr.product_id','=','es.pid')
             		                    ->where(['track_id'=>$lastInsertId,'level_id'=>0,'product_type_id'=>8003])
             		                    ->distinct()
             		                    ->lists('material_code');
                  
                  Log::info('SYSTEM ARRAY2');
                  Log::info($systemMatCode);
   
                    /*$systemMatCode = DB::table($esealTable.' as es')
                                           				->join('products as pr','pr.product_id','=','es.pid')
                                           				->whereIn('primary_id',$primary)  
                                           				->distinct()
                                           				->lists('material_code');*/

                      $itemXml = '';
                      $batchArr = array();
                      $itemCount = 0;
                      foreach($systemMatCode as $matCode){
		
                      if(!isset($uomArray[$matCode]))
                      	 throw new Exception('Material doesnt exist in the PO.Material: '.$matCode);

                      $uom = $uomArray[$matCode];

                       $query =     DB::table($esealTable.' as es')
					                       ->join('products as pr','pr.product_id','=','es.pid')
					                       ->where('pr.material_code',$matCode)
					                       ->where(['track_id'=>$lastInsertId,'level_id'=>0]);

                    if($uom['uom'] == 'M')
					  $qty = $query->sum('pkg_qty');
				    else{
                      $qty = $query->get([DB::raw('CASE WHEN multiPack=0 THEN count(primary_id) ELSE sum(pkg_qty) END AS qty')]);
                      $qty = $qty[0]->qty;
				    }

                    if(empty($store_location))
                      $store_location = $uom['storage_location'];
                    
                    DB::table($esealTable)
								->where('track_id',$lastInsertId)								      
								->update(['storage_location'=>$store_location]); 

                    array_push($batchArr,['material_code'=>$matCode,'track_id'=>$lastInsertId]);
       				$itemXml .= '<ZESEAL047_GRN_DATA ITEM_COUNT="'.$itemCount++.'" MVMT_TYPE="101" PURCHASE_NO="'.$deliveryNo.'" PLANT="'.$uom['plant'].'" STOR_LOC="'.$store_location.'" MATERIAL="'.$matCode.'" QTY="'.$qty.'" STOCK_TYPE="01"/>';

		}

		if(!empty($transitIds)){
           
           Log::info('DAMAGE EXECUTION :-');

			$damageMatCode = DB::table($esealTable.' as es')
             		                    ->join('products as pr','pr.product_id','=','es.pid')
             		                    ->where(['level_id'=>0,'product_type_id'=>8003])
               		                    ->where(function($query) use($transitIds){
													 $query->whereIn('es.parent_id', explode(',',$transitIds))
														   ->orWhereIn('es.primary_id',explode(',',$transitIds));
											 }
											 )
             		                    ->distinct()
             		                    ->lists('material_code');

             foreach($damageMatCode as $matCode){
		
                      if(!isset($uomArray[$matCode]))
                      	 throw new Exception('Material doesnt exist in the PO.Material: '.$matCode);

                      $business_unit_id = Products::where('material_code',$matCode)->pluck('business_unit_id');
                      $store_location = Location::where(['parent_location_id'=>$locationId,'business_unit_id'=>$business_unit_id,'storage_location_type_code'=>25002])->pluck('erp_code');

                      Log::info('DAMAGE STORE LOCATION :-'.$store_location);

                      $uom = $uomArray[$matCode];

                       $query =     DB::table($esealTable.' as es')
					                       ->join('products as pr','pr.product_id','=','es.pid')
					                       ->where('pr.material_code',$matCode)
					                       ->where(function($query) use($transitIds){
													 $query->whereIn('es.parent_id',explode(',',$transitIds))
														   ->orWhereIn('es.primary_id',explode(',',$transitIds));
											 }
											 )
     					                   ->where(['level_id'=>0]);

                    $damage_track_id = DB::table($esealTable)->whereIn('primary_id',explode(',',$transitIds))->pluck('track_id');
 
                    if($uom['uom'] == 'M')
					  $qty = $query->sum('pkg_qty');
				    else{
                      $qty = $query->get([DB::raw('CASE WHEN multiPack=0 THEN count(primary_id) ELSE sum(pkg_qty) END AS qty')]);
                      $qty = $qty[0]->qty;
				    }
                    
                    DB::table($esealTable)
								->where('track_id',$damage_track_id)								
								->update(['storage_location'=>$store_location]);
                      
                    array_push($batchArr,['material_code'=>$matCode,'track_id'=>$damage_track_id]);

       				$itemXml .= '<ZESEAL047_GRN_DATA ITEM_COUNT="'.$itemCount++.'" MVMT_TYPE="101" PURCHASE_NO="'.$deliveryNo.'" PLANT="'.$uom['plant'].'" STOR_LOC="'.$store_location.'" MATERIAL="'.$matCode.'" QTY="'.$qty.'" STOCK_TYPE="03"/>';

		} 		                    


		}

                      $xml= '<?xml version="1.0" encoding="utf-8" ?> 
							<REQUEST>
							<ZESEAL047_DO_MIGO>
							<INPUT1 TOKEN="'.$erpToken.'" XML_DYNAMIC="'.$XML_DYNAMIC.'"/> 
							<MIGO_DATA>';

                      $xml .= $itemXml;


					  $xml  .='</MIGO_DATA> 
							</ZESEAL047_DO_MIGO>
							</REQUEST>';
        


                      $finalXML = '<entry xmlns="http://www.w3.org/2005/Atom" xmlns:m="http://schemas.microsoft.com/ado/2007/08/dataservices/metadata" xmlns:d="http://schemas.microsoft.com/ado/2007/08/dataservices" xml:base="'.$erpUrl.'ZESEAL_052_DO_GRN_PO_SRV/">
							<id>'
							.$erpUrl.'ZESEAL_052_DO_GRN_PO_SRV/GRN_PO(\'123\')
							</id>
							<title type="text">GRN_PO(\'123\')</title>
							<updated>2015-08-14T10:19:23Z</updated>
							<category term="ZESEAL_052_DO_GRN_PO_SRV.GRN_PO" scheme="http://schemas.microsoft.com/ado/2007/08/dataservices/scheme"/>
							<link href="GRN(\'123\')" rel="self" title="GRN"/>
							<content type="application/xml">
							<m:properties>
							<d:ESEAL_INPUT>"<![CDATA[';
								$finalXML .= $xml;
								$finalXML .= ']]>"</d:ESEAL_INPUT>
										<d:ESEAL_OUTPUT />
										 </m:properties>
										</content>
										 </entry>';
           
                    Log::info('XML Passed');
                    Log::info($finalXML);

                    $method ='POST';
					$this->_method = 'ZESEAL_052_DO_GRN_PO_SRV';
					$this->_method_name = 'GRN_PO';
					$cred =DB::table('erp_integration')->where('manufacturer_id', $mfgId)->first(['web_service_url','web_service_username','web_service_password','sap_client']);
					$erp = $this->roleAccess->getErpDetailsByUserId(Input::get('access_token'));	
					if($erp){
					$username = $erp[0]->erp_username;
					$password = $erp[0]->erp_password;
					}
					else{
						throw new Exception('There are no erp username and password');
					}
					$sap_client = $cred->sap_client;
					$this->_url = $cred->web_service_url;

					$url = $this->_url .$this->_method.'/'.$this->_method_name.'?&sap-client='.$sap_client;
					Log::info($url);
					Log::info('SAP start');
					$response = $this->sapRepo->request($username,$password,$url, $method,null,'xml',2,null,$finalXML);
					Log::info('SAP response:-'.$response);
                    
					$parseData1 = xml_parser_create();
					xml_parse_into_struct($parseData1, $response, $documentValues1, $documentIndex1);
					xml_parser_free($parseData1);
					$documentData = array();

					foreach ($documentValues1 as $data) {
						if(isset($data['tag']) && $data['tag'] == 'D:ESEAL_OUTPUT')
						{
							$documentData = $data['value'];
						}
						
					}

					if(!empty($documentData)){
					$deXml = simplexml_load_string($documentData);
					$deJson = json_encode($deXml);
					$xml_array = json_decode($deJson,TRUE);
					
					Log::info($xml_array);

					if($xml_array['HEADER']['Status'] == 1){
					 
				        
                     foreach ($documentValues1 as $data) {
						if(isset($data['tag']) && $data['tag'] == 'D:MAT_DOC_NO')
						{
							$documentNo = $data['value'];
						}
						
					}

					DB::table($this->TPAttributeMappingTable)->where(['tp_id'=>$tp,'attribute_id'=>$purchase_attribute_id])->update(['reference_value'=>$documentNo]);


					   //$batches = $xml_array['HEADER']['Batches'];
					if(!isset($xml_array['HEADER']['Batches']['BATCH']['MATERIAL'])){
                       
                       /*foreach($xml_array['HEADER']['Batches']['BATCH'] as $batch){
                         DB::table($esealTable.' as es')
					                       ->join('products as pr','pr.product_id','=','es.pid')
					                       ->join('track_details as td','td.code','=','es.primary_id')
					                       ->where('pr.material_code',$batch['MATERIAL'])
					                       ->where(['td.track_id'=>$lastInsertId,'level_id'=>0])
					                       ->update(['batch_no'=>$batch['BATCH']]);	
                       }*/

                       for($i = 0;$i < count($batchArr);$i++){

                       	 DB::table($esealTable.' as es')
					                       ->join('products as pr','pr.product_id','=','es.pid')
					                       ->join('track_details as td','td.code','=','es.primary_id')
					                       ->where('pr.material_code',$batchArr[$i]['material_code'])
					                       ->where(['td.track_id'=>$batchArr[$i]['track_id'],'level_id'=>0])
					                       ->update(['batch_no'=>$xml_array['HEADER']['Batches']['BATCH'][$i]['BATCH'] ]);	
                       }
                   

                   }
                   else{
                   	DB::table($esealTable.' as es')
					                       ->join('products as pr','pr.product_id','=','es.pid')
					                       ->join('track_details as td','td.code','=','es.primary_id')
					                       ->where('pr.material_code',$xml_array['HEADER']['Batches']['BATCH']['MATERIAL'])
					                       ->where(['td.track_id'=>$lastInsertId,'level_id'=>0])
					                       ->update(['batch_no'=>$xml_array['HEADER']['Batches']['BATCH']['BATCH']]);	
                   }

					   $message = 'GRN created with document no : '.$documentNo.' and batch updated';       
                }
                   else{
						throw new Exception('TP not received GRN not created in ERP.'.$xml_array['HEADER']['Message']);
					}

				//****
              }
				else{
					throw new Exception('TP not received .ERP call error occurred');
				}
            }
				DB::commit();
			}else{
				throw new Exception('Given transition not allowed');
			}
		}else{
			throw new Exception('Invalid location');
		}

	}catch(Exception $e){
		$status =0;
		DB::rollback();
		Log::info($e->getMessage());
		$message = $e->getMessage();

	}
	$endTime = $this->getTime();
	Log::info(__FUNCTION__.' Finishes execution in '.($endTime-$startTime));
	Log::info(Array('Status'=>$status, 'Message'=>$message, 'documentNo' =>$documentNo));
	return json_encode(Array('Status'=>$status, 'Message' =>'Server: '.$message, 'documentNo' =>$documentNo));
}


private function _getProductDetailsForReceivedTrackId($lastInsertId, $esealTable,$uomArray){
	
	foreach ($uomArray as $uom) {

	$detailsData1 = DB::table($esealTable)
				->leftJoin('products', 'product_id', '=', 'pid')
				->where('products.product_type_id','8003')
				->where('track_id',$lastInsertId)
				->where('level_id', 0)
				->where('products.material_code',$uom['material_code']);

	if($uom['uom'] == 'M'){

		if($uom['is_serializable'] == 0){                
		$detailsData1 = $detailsData1->select('batch_no', 'primary_id', 'pid', 'name', 'material_code', 'business_unit_id','products.is_serializable',DB::raw('sum(pkg_qty) as qty'),DB::raw('"'.$uom['store_location'].'" as store_location'))
		                             ->groupBy('batch_no','pid')->get();
		         }
		         else{
		$detailsData1 = $detailsData1->select('batch_no', 'primary_id', 'pid', 'name', 'material_code', 'business_unit_id','products.is_serializable',DB::raw('pkg_qty as qty'),DB::raw('"'.$uom['store_location'].'" as store_location'))
		                              ->groupBy('primary_id','pid')->get(); 	
		         }

	}	
	else{

		 if($uom['is_serializable'] == 0){
         $detailsData1 = $detailsData1->select('batch_no', 'primary_id', 'pid', 'name', 'material_code', 'business_unit_id','products.is_serializable',DB::raw('CASE WHEN multiPack=0 THEN count(primary_id) ELSE sum(pkg_qty) END AS qty'),DB::raw('"'.$uom['store_location'].'" as store_location'))
		                              ->groupBy('batch_no','pid')->get();
         }
         else{
         $detailsData1 = $detailsData1->select('batch_no', 'primary_id', 'pid', 'name', 'material_code', 'business_unit_id','products.is_serializable',DB::raw('CASE WHEN multiPack=0 THEN 1  ELSE sum(pkg_qty) END AS qty'),DB::raw('"'.$uom['store_location'].'" as store_location'))
		                              ->groupBy('primary_id','pid')->get();
         }

	}	

	$detailsData[] = $detailsData1; 	
	
}
				
	return $detailsData;
}


private function _getProductDetailsForMissingORBlockedIds($missingIdsArr, $esealTable,$uomArray){
	foreach($uomArray as $uom){

	$detailsData1 = DB::table($esealTable)
	                 ->leftJoin('products', 'product_id', '=', 'pid')
	                 ->whereIn('primary_id', $missingIdsArr)
	                 ->where('products.material_code',$uom['material_code']);	
	
	  if($uom['uom'] == 'M'){
    
	
	 if($uom['is_serializable'] == 0){                
		$detailsData1 = $detailsData1->select('batch_no', 'primary_id', 'pid', 'name', 'material_code', 'business_unit_id','products.is_serializable',DB::raw('sum(pkg_qty) as qty'))
		                             ->groupBy('batch_no','pid')->get();
		         }
		         else{
		$detailsData1 = $detailsData1->select('batch_no', 'primary_id', 'pid', 'name', 'material_code', 'business_unit_id','products.is_serializable',DB::raw('pkg_qty as qty'))
		                              ->groupBy('primary_id','pid')->get(); 	
		         }
    }
    else{
         if($uom['is_serializable'] == 0){
         $detailsData1 = $detailsData1->select('batch_no', 'primary_id', 'pid', 'name', 'material_code', 'business_unit_id','products.is_serializable',DB::raw('CASE WHEN multiPack=0 THEN count(primary_id) ELSE sum(pkg_qty) END AS qty'))
		                              ->groupBy('batch_no','pid')->get();
         }
         else{
         $detailsData1 = $detailsData1->select('batch_no', 'primary_id', 'pid', 'name', 'material_code', 'business_unit_id','products.is_serializable',DB::raw('CASE WHEN multiPack=0 THEN 1  ELSE sum(pkg_qty) END AS qty'))
		                              ->groupBy('primary_id','pid')->get();
         }
		             
	}
	$detailsData[] = $detailsData1; 
		             
    }
    return $detailsData;
}

private function getStorageLocationTypeValues(){
	$catID = DB::table('lookup_categories')->where('name', 'Storage Location Types')->pluck('id');
	return $nameValue = DB::table('master_lookup')->where('category_id', $catID)->select('name', 'value')->get();
}


private function getBusinessUnitIds($ids, $mfgId){
	$idsBusIdArr = Array();
	$res = DB::table('eseal_'.$mfgId)
	->join('products', 'product_id', '=', 'pid')
	->where(['level_id'=>0,'product_type_id'=>'8003'])
	->where(function($query) use($ids){
				$query->whereIn('parent_id', $ids)
				->orWhereIn('primary_id',$ids);
			}
		)
	->select('business_unit_id','primary_id')
	->distinct()
	->get();
	foreach($res as $val){
		$idsBusIdArr[$val->primary_id] = $val->business_unit_id;
	}
	//Log::info(print_r($idsBusIdArr,true));
	return $idsBusIdArr;
}

private  function getStorageLocationIdForGivenBusinessUnitId($buid, $mfgId, $locationId, $storageLocationTypeCode){
	$lid = DB::table('locations')->where('parent_location_id',$locationId)
					   ->where('manufacturer_id', $mfgId)
					   ->where('business_unit_id', $buid)
					   ->where('storage_location_type_code', $storageLocationTypeCode)->pluck('location_id');
	Log::info(print_r(func_get_args(),true));
	return $lid;
}


private function makeReceive($storageLocId, $getIdsForBusUnitId, $transitionId, $transitionTime, $mfgId){
	$lastInsertId = DB::table($this->trackHistoryTable)->insertGetId(Array(
			'src_loc_id'=>$storageLocId,
			'dest_loc_id'=> 0,
			'transition_id' => $transitionId,
			'update_time'=> $transitionTime
			));
	foreach($getIdsForBusUnitId as $code){
		
		DB::table('eseal_'.$mfgId)->where('primary_id', $code)->update(Array('track_id'=>$lastInsertId));
		DB::table('eseal_'.$mfgId)->where('parent_id', $code)->update(Array('track_id'=>$lastInsertId));
		$sql = 'INSERT INTO  '.$this->trackDetailsTable.' 
		(code, track_id) SELECT primary_id, '.$lastInsertId.' FROM eseal_'.$mfgId.' WHERE track_id='.$lastInsertId;
		DB::insert($sql);
	}

}


public function call(){

	$mfgId =1;
	$xml = '<?xml version="1.0" encoding="utf-8" ?><REQUEST><DATA><INPUT TOKEN="3h8M8A2q8iv7nMq4Rpft5G5TBE4O7PC8" ESEAL_KEY="123456" /><GRN_DATA><INPUT DELIVERY="0080000075" GRN_PLANT="9001" GRN_STOR_LOC="FGF1" DELIVERY_PLANT="1010" DELIVERY_STOR_LOC="FG01" MATERIAL="TESTFAN1" BATCH_NO="0000000171" QUANTITY="1" STOCK_TYPE="01" SERIAL_NO="2585171354003251"/><INPUT DELIVERY="0080000075" GRN_PLANT="9001" GRN_STOR_LOC="FGF1" DELIVERY_PLANT="1010" DELIVERY_STOR_LOC="FG01" MATERIAL="TESTFAN1" BATCH_NO="0000000171" QUANTITY="1" STOCK_TYPE="01" SERIAL_NO="8546095145866276"/><INPUT DELIVERY="0080000075" GRN_PLANT="9001" GRN_STOR_LOC="FGF1" DELIVERY_PLANT="1010" DELIVERY_STOR_LOC="FG01" MATERIAL="TESTFAN1" BATCH_NO="0000000171" QUANTITY="1" STOCK_TYPE="01" SERIAL_NO="9920551753602923"/></GRN_DATA></DATA></REQUEST>';
	$cd = '<entry xmlns="http://www.w3.org/2005/Atom" xmlns:m="http://schemas.microsoft.com/ado/2007/08/dataservices/metadata" xmlns:d="http://schemas.microsoft.com/ado/2007/08/dataservices" xml:base="http://14.141.81.243:8000/sap/opu/odata/sap/Z0044_ESEAL_CREATE_GRN_STO_SRV/">
							<id>
							http://14.141.81.243:8000/sap/opu/odata/sap/Z0044_ESEAL_CREATE_GRN_STO_SRV/GRN(\'123\')
							</id>
							<title type="text">GRN(\'123\')</title>
							<updated>2015-08-14T10:19:23Z</updated>
							<category term="Z0044_ESEAL_CREATE_GRN_STO_SRV.GRN" scheme="http://schemas.microsoft.com/ado/2007/08/dataservices/scheme"/>
							<link href="GRN(\'123\')" rel="self" title="GRN"/>
							<content type="application/xml">
							<m:properties>
							<d:DOCUMENT_NO/>
							<d:YEAR/>
							<d:ESEAL_INPUT>"<![CDATA[';

								$cd .= $xml;
								$cd .= ']]>"</d:ESEAL_INPUT>
										<d:ESEAL_OUTPUT />
										 </m:properties>
										</content>
										 </entry>'	;	

	//return $cd;
	$method ='POST';
	$this->_method = 'Z0044_ESEAL_CREATE_GRN_STO_SRV';
	$this->_method_name = 'GRN';
	$cred =DB::table('erp_integration')->where('manufacturer_id', $mfgId)->first(['web_service_url','web_service_username','web_service_password']);

	$erp = $this->roleAccess->getErpDetailsByUserId(Input::get('access_token'));	
	if($erp){
	$username = $erp[0]->erp_username;
	$password = $erp[0]->erp_password;
	}
	else{
		return 'There are no erp username and password';
	}
	$this->_url = $cred->web_service_url;
	$url = $this->_url .$this->_method.'/'.$this->_method_name;

	//$response = $this->sapRepo->request($username,$password,$url, $method,null,'xml',1,null,$xml);
	$response = $this->sapRepo->request($username,$password,$url, $method,null,'xml',2,null,$cd);
return $response;
				   $parseData1 = xml_parser_create();
					xml_parse_into_struct($parseData1, $response, $documentValues1, $documentIndex1);
					xml_parser_free($parseData1);
					$documentData = array();
					foreach ($documentValues1 as $data) {
						
						if(isset($data['tag']) && $data['tag'] == 'D:DOCUMENT_NO')
						{
							$documentData = $data['value'];
						}

					}
					return $documentData;
}





public function Receive(){
	$startTime = $this->getTime();
	try{
		$status = 0;
		$message = '';
		$codes = Input::get('codes');
		$locationId = Input::get('location_id');
		$transitionTime = Input::get('transition_time');
		$transitionId = Input::get('transition_id');
		$previousTrackId = '';

		DB::beginTransaction();
		if(empty($codes) || empty($locationId) || empty($transitionId) || empty($transitionTime)){
			throw new Exception('Some of the input parameters are missing');
		}
		if(gettype($codes)!='string' || !is_numeric($locationId) || !is_numeric($transitionId)){
			throw new Exception('Invalid data type for some of the input params');
		}
		///GET MfgId for geiven Location

		$explodedCodes = explode(',', $codes);
		$explodedCodes = array_unique($explodedCodes);
		$explodedCodesCnt = count($explodedCodes);

		$locationObj = new Locations\Locations();
		$mfgId = $locationObj->getMfgIdForLocationId($locationId);

		if($mfgId){
			$esealTable = 'eseal_'.$mfgId;

			$transactionObj = new TransactionMaster\TransactionMaster();
			$transactionDetails = $transactionObj->getTransactionDetails($mfgId, $transitionId);
			if(!count($transactionDetails)){
				throw new Exception('Transition details not found');
			}
			Log::info(print_r($transactionDetails, true));

			$srcLocationAction = $transactionDetails[0]->srcLoc_action;
			$destLocationAction = $transactionDetails[0]->dstLoc_action;
			$inTransitAction = $transactionDetails[0]->intrn_action;

			if($srcLocationAction==0 && $destLocationAction==1 && $inTransitAction==-1){
				try{
					$codesCount = DB::table($esealTable.' as eseal')->join($this->trackHistoryTable.' as th', 'eseal.track_id','=','th.track_id')
								->whereIn('eseal.primary_id', $explodedCodes)
								->where('th.dest_loc_id', $locationId)
								->count();
					if($codesCount!=$explodedCodesCnt){
						throw new Exception('Some of the codes are not destined for given location');
					}
				}catch(PDOException $e){
					Log::info($e->getMessage());
					throw new Exception('Error during query exceution');
				}
				
				try{
					$lastInsertId = DB::table($this->trackHistoryTable)->insertGetId(Array(
						'src_loc_id'=>$locationId,
						'dest_loc_id'=> 0,
						'transition_id' => $transitionId,
						'update_time'=> $transitionTime
						));
					DB::table($esealTable)
						->whereIn('primary_id', $explodedCodes)
						->orWhereIn('parent_id', $explodedCodes)
						->update(Array('track_id'=>$lastInsertId));

					$sql = 'INSERT INTO  '.$this->trackDetailsTable.' 
					(code, track_id) SELECT primary_id, '.$lastInsertId.' FROM '.$esealTable.' WHERE track_id='.$lastInsertId;
					DB::insert($sql);
				}catch(PDOException $e){
					Log::info($e->getMessage());
					throw new Exception('Error during query exceution');    
				}
				$status = 1;
				$message = 'Codes received succesfully';
				DB::commit();
			}else{
				throw new Exception('Given transition not allowed');
			}
		}else{
			throw new Exception('Invalid location');
		}

	}catch(Exception $e){
		DB::rollback();
		Log::info($e->getMessage());
		$message = $e->getMessage();
	}
	$endTime = $this->getTime();
	Log::info(__FUNCTION__.' Finishes execution in '.($endTime-$startTime));

	return json_encode(Array('Status'=>$status, 'Message'=>'Server: '.$message));
}


public function Issue(){
	$startTime = $this->getTime();
	try{
		$status = 1;
		$message = 'Stockout done succesfully';
		$ids = trim(Input::get('ids'));
		$srcLocationId =  $this->roleAccess->getLocIdByToken(Input::get('access_token'));
		$transitionId = trim(Input::get('transition_id'));
		$attribute_json = Input::get('attributes');
		$transitionTime = trim(Input::get('transition_time'));	

		DB::beginTransaction();
		if(empty($ids) || empty($srcLocationId) || empty($transitionId) || empty($transitionTime) || empty($attribute_json)){
			throw new Exception('Some of the input parameters are missing');
		}
		if(gettype($ids)!='string' || !is_numeric($transitionId)){
			throw new Exception('Invalid data type for some of the input params');
		}
		///GET MfgId for geiven Location

		$explodedIds = explode(',', $ids);
		$explodedIds = array_unique($explodedIds);
		$explodedIdsCnt = count($explodedIds);

		$locationObj = new Locations\Locations();
		$mfgId = $locationObj->getMfgIdForLocationId($srcLocationId);		

		if($mfgId){
			$esealTable = 'eseal_'.$mfgId;
			$esealBankTable = 'eseal_bank_'.$mfgId;
			$destLocationId = $locationObj->getStockoutLocation($mfgId);

			if(!$destLocationId)
				throw new Exception('Stockout location not configured.');

			$transactionObj = new TransactionMaster\TransactionMaster();
			$transactionDetails = $transactionObj->getTransactionDetails($mfgId, $transitionId);
			if(!count($transactionDetails)){
				throw new Exception('Transition details not found');
			}

			Log::info(print_r($transactionDetails, true));

			$srcLocationAction = $transactionDetails[0]->srcLoc_action;
			$destLocationAction = $transactionDetails[0]->dstLoc_action;
			$inTransitAction = $transactionDetails[0]->intrn_action;

			if($srcLocationAction==-1 && $destLocationAction==0 && $inTransitAction==1){
				try{

			$result = DB::table($esealBankTable)->whereIn('id',$explodedIds)->count();
		          //Log::info('===='.print_r($result,true));
		   if($explodedIdsCnt != $result){
			   throw new Exception('Some of the codes does not exist in database');
		   }		

			$result = DB::table($esealTable)->whereIn('primary_id',$explodedIds)->count();
		         // Log::info('===='.print_r($result,true));
		   if($explodedIdsCnt != $result){
			   throw new Exception('Some of the codes are not binded');
		   }

		$result = DB::table($esealTable)
                              ->where(function($query) use($explodedIds){
                        $query->whereIn('parent_id',$explodedIds);
                        $query->orWhereIn('primary_id',$explodedIds);
                              })->where('is_active',0)->where('level_id',0)
                              ->count();
                //Log::info('====blocked iots count'.print_r($result,true));
                if($result){
                        throw new Exception('Some of the codes are blocked');
                }


		//////////CHECK IF ALL THE IDS HAVE SAME SOURCE LOCATION ID As SUPPLIED
		$transitCnt = DB::table($esealTable.' as eseal')->join($this->trackHistoryTable.' as th', 'eseal.track_id', '=', 'th.track_id')
		->whereIn('primary_id', $explodedIds)
		->select('src_loc_id','dest_loc_id')->groupBy('src_loc_id','dest_loc_id')->get();
		//Log::info($transitCnt);

		if(count($transitCnt)>1){               
        $childIds = Array();
		$childIds = $locationObj->getAllChildIdForParentId($srcLocationId);
		if($childIds)
		{
			array_push($childIds, $srcLocationId);
		}
		$parentId = $locationObj->getParentIdForLocationId($srcLocationId);
		$childIds1 = Array();
		if($parentId)
		{
			$childIds1 = $locationObj->getAllChildIdForParentId($parentId);
			if($childIds1)
			{
				array_push($childIds1, $parentId);
			}
		}
		$childsIDs = array_merge($childIds, $childIds1);
		$childsIDs = array_unique($childsIDs);

            $inCount = DB::table($esealTable.' as es')
                           ->join('track_history as th','th.track_id','=','es.track_id')
                           ->whereIn('primary_id',$explodedIds)
                           ->whereIn('src_loc_id',$childsIDs)
                           ->count('es.primary_id');
            if($inCount != $explodedIdsCnt)
      			throw new Exception('Some of the codes are available with different location');

		}
		if(count($transitCnt) == 1){
			if($transitCnt[0]->dest_loc_id>0){
				throw new Exception('Some of the codes are in transit.');   
			}
		}

				}catch(PDOException $e){
					Log::info($e->getMessage());
					throw new Exception('Error during query exceution');
				}
				
				try{

		      $request = Request::create('scoapi/SaveBindingAttributes', 'POST', array('module_id'=> Input::get('module_id'),'access_token'=>Input::get('access_token'),'attributes'=>$attribute_json,'lid'=>$srcLocationId,'pid'=>1));
			  $originalInput = Request::input();//backup original input
			  Request::replace($request->input());
			  Log::info($request->input());
			  $res = Route::dispatch($request)->getContent();
			  $res = json_decode($res);
			  if($res->Status){
			   $attributeMapId = $res->AttributeMapId;
			  }
			  else{
				throw new Exception($res->Message);
			  }

					$lastInsertId = DB::table($this->trackHistoryTable)->insertGetId(Array(
						'src_loc_id'=>$srcLocationId,
						'dest_loc_id'=>$destLocationId,
						'transition_id' => $transitionId,
						'tp_id'=>0,
						'update_time'=> $transitionTime
						));
					
					DB::table($esealTable)
						->whereIn('primary_id', $explodedIds)
						->orWhereIn('parent_id', $explodedIds)
						->update(Array('track_id'=>$lastInsertId,'attribute_map_id'=>$attributeMapId));

                   foreach($explodedIds  as $id){
					
					DB::insert('INSERT INTO bind_history (eseal_id,location_id,attribute_map_id,created_on) values (?, ?,?,?)', array($id,$srcLocationId,$attributeMapId,$transitionTime));
				   
				   }

					$sql = 'INSERT INTO  '.$this->trackDetailsTable.' 
					(code, track_id) SELECT primary_id, '.$lastInsertId.' FROM '.$esealTable.' WHERE track_id='.$lastInsertId;
					DB::insert($sql);
										

				}

				catch(PDOException $e){
					Log::info($e->getMessage());
					throw new Exception('Error during query exceution');    
				}				
				DB::commit();
			}else{
				throw new Exception('Given transition not allowed');
			}
		}else{
			throw new Exception('Invalid location');
		}

	}catch(Exception $e){
		$status = 0;
		Log::info($e->getMessage());
		$message = $e->getMessage();
		DB::rollback();
	}
	$endTime = $this->getTime();
	Log::info(__FUNCTION__.' Finishes execution in '.($endTime-$startTime));
	return json_encode(['Status'=>$status, 'Message'=>$message]);
}


private function getTP($mfgId, $srcLocationId){
	$TPId = DB::table('eseal_bank_'.$mfgId.' bank')
				->where('used_status',0)
				->where('issue_status',0)
				->where('download_status', 0)
				->select('id')
				->orderBy('serial_id')->take(1)->lockForUpdate()->get();
	if($TPId)
		return $TPId;
	else
		return FALSE;
}

public function GetProductDetailsForEsealCodes(){
	$startTime = $this->getTime();
	try{
		$status = 0;
		$message = '';

		$codes = trim(Input::get('codes'));
		$locationId = trim(Input::get('location_id'));

		$trackIdArr = array();
		$productArray = array();
		$productDetailArray = array();
		if(!is_numeric($locationId) || empty($codes)){
			throw new Exception('Invalid input');
		}
		$codesArray = explode(',', $codes);
		$codesArray = array_unique($codesArray);
		$codescnt = count($codesArray);

		$locationObj = new Locations\Locations();
		$mfgId = $locationObj->getMfgIdForLocationId($locationId);

		if(!$mfgId){
			throw new Exception('Unable to fetch info for given location id');
		}
		$esealTable = 'eseal_'.$mfgId;
		$esealBankTable = 'eseal_bank_'.$mfgId;

		///**********   GET TRACK ID FOR GIVEN CODES *************/
		$trackIds = DB::table($esealTable)->whereIn('primary_id',$codesArray)
			->select('track_id')
			->get();
		if(!count($trackIds)){
			throw new Exception('Codes are not yet binded with track id');
		}
		foreach($trackIds as $tid){
			array_push($trackIdArr, $tid->track_id);
		}

		////********* GET Count, Product Name, MRP and Batch No ********/
		$res = DB::table($esealTable)
			->whereIn('primary_id', $codesArray)
			->orWhereIn('parent_id', $codesArray)
			->whereIn('track_id', $trackIdArr)
			->where('level_id',0)
			->select(DB::raw('count(*) as qty, pid, mrp, batch_no'))
			->groupBy('pid','mrp','batch_no')
			->get();
		$pname = '';
		$pnameArray = array();

		foreach($res as $val){
			$pname = '';
			if(array_key_exists($val->pid, $pnameArray)){
				$pname = $pnameArray[$val->pid];
			}else{
				$product = new Products\Products();
				$pname = $product->getNameFromId($val->pid);
				//Log::info('22222'.print_r($pname,true));
				$pnameArray[$val->pid] = $pname;           
			}

			$productArray[] = Array(
					'Product' => Array(
						'pname'=>$pname, 'qty'=>$val->qty, 'mrp'=>$val->mrp, 'batch'=>$val->batch_no
						)
					);
		}

		$maxLevelId = DB::table($esealTable)->whereIn('primary_id', $codesArray)->whereIn('track_id', $trackIdArr)->max('level_id');
		//Log::info('$maxLevelId:'.$maxLevelId);
		for($i=0; $i<=$maxLevelId; $i++){
			//Log::info(__LINE__);
			if($i==0){
				$res = DB::table($esealTable.' as eseal')
						->join($this->trackHistoryTable.' as th', 'eseal.track_id', '=', 'th.track_id')
						->whereIn('eseal.track_id', $trackIdArr)
						->where('level_id', $i)
						->whereIn('primary_id', $codesArray)
						->orWhereIn('parent_id', $codesArray)
						->select(DB::raw('count(*) qty, eseal.pid, eseal.primary_id, eseal.parent_id, eseal.level_id, eseal.batch_no, eseal.mrp, th.tp_id, th.update_time'))
						->groupBy('pid', 'primary_id')
						->get();
			}
			if($i>0){

				$row = DB::table($esealTable.' as es1')->join($this->trackHistoryTable.' as th1', 'es1.track_id','=','th1.track_id')
					->where('es1.level_id',$i)
					->whereIn('primary_id', $codesArray)
					->orWhereIn('parent_id', $codesArray)
					->select('primary_id')
					->get();
				foreach($row as $levelcodes){
					$levelCode[] = $levelcodes->primary_id;
				}
				$res = DB::table($esealTable.' as eseal')
						->join($this->trackHistoryTable.' as th', 'eseal.track_id', '=', 'th.track_id')
						->whereIn('parent_id', $levelCode)
						->select(DB::raw('count(*) qty, eseal.pid, eseal.primary_id, eseal.parent_id, eseal.level_id, eseal.batch_no, eseal.mrp, th.tp_id, th.update_time'))
						->groupBy('pid', 'parent_id')
						->get();
			}
			//Log::info(__LINE__);
			$pname = '';
			$pnameArray = array();
			foreach($res as $val){
				$pname = '';
				if(array_key_exists($val->pid, $pnameArray)){
					$pname = $pnameArray[$val->pid];
				}else{
					$product = new Products\Products();
					$pname = $product->getNameFromId($val->pid);
					$pnameArray[$val->pid] = $pname;           
				}
//Log::info(__LINE__);
				$productDetailArray['esealData'][] = Array('Product'=>Array(
					'qty' => $val->qty, 'esealId'=>$val->primary_id, 'pname'=>$pname, 'levelid'=>$val->parent_id, 'level'=>$val->level_id, 
					'updateTime'=>$val->update_time, 'mrp'=>$val->mrp, 'batch'=>$val->batch_no, 'tp'=>$val->tp_id
					));
			}
//Log::info(__LINE__);
		}
		$status = 1;
		$message = 'Data retrieved succesfully';

	}catch(Exception $e){
		Log::info($e->getMessage());
		$message = $e->getMessage();
	}
	$endTime = $this->getTime();
	Log::info(__FUNCTION__.' Finishes execution in '.($endTime-$startTime));

	return json_encode(Array('Status'=>$status, 'Message'=>$message,'esealData'=>array_merge($productArray, $productDetailArray)));
}


public function getEscortData(){
	try{
		$status = 0;
		$message = '';
		$date = Input::get('lastUpdateTime');
		$escortData = '';

		$escortData = DB::table('escortData as ed')->join('products as p', 'ed.pid', '=', 'p.product_id')
			->join('product_packages as pp', 'ed.pid', '=', 'pp.product_id')
			->select('ed.code', 'ed.pid', 'p.name', 'ed.packcode', 'ed.packingType', 'utilizedDate', DB::raw('IFNULL(CASE 
	WHEN ed.packingType=\'RETAIL\' THEN (select pg.quantity from product_packages pg where pg.product_id=ed.pid and substring(level,-1)=2)
	WHEN ed.packingType=\'W/S\' THEN (select pg.quantity from product_packages pg where pg.product_id=ed.pid and substring(level,-1)=3 )
ELSE 0
END,0) as nextLevelCap '))
			->where('utilizedDate','>=',$date)
			->where('usedStatus', 0)
			->groupBy('ed.code')
			->get();
		if(count($escortData)){
			$status = 1;
			$message ='Data retreived succesfully';
		}else{
			$message = 'Data not found';
		}
	}catch(Exception $e){
		Log::info($e);
		$message = 'Exception occured '	;
	}
	return json_encode(Array('Status'=>$status, 'Message'=> $message, 'escortData'=> $escortData));
}

private function removeMappedEscortCodes($child, $parent){
	$status = 0;
	$message = 'Unable to remove given codes';
	try{
		$row = DB::table('escortData')->where('code', $parent)->select('pid','packingType')->first();
		
		Log::info(print_r($row,true));

		$packingType = $row->packingType;
		$pid = $row->pid;

		$ids = $child;
		$cnt = 0;
		Log::info('Cnt BEFORE ='.print_r($cnt,true));
		if(strtolower($packingType) == 'w/s'){
			$cnt = DB::table('escortData')->where('pid', $pid)->where('packingType','UNITIZED')->count();
			Log::info('Cnt ='.print_r($cnt,true));
		}
		if(strtolower($packingType) == 'unitized'){
			$ids .= ','.$parent;
			Log::info(print_r($ids,true));
		}
		
		if(!$cnt){
			$ids .= ','.$parent;
		}
		
		$ids = explode(',', $ids);
		Log::info(print_r($ids,true));
		try{
			$rows = DB::table('escortData')->whereIn('code', $ids)->update(Array('usedStatus'=>1));	
			$status = 1;
			$message = $rows.' affected';
		}catch(PDOException $e){
			Log::info($e->getMessage());
			throw $e;
		}
	}catch(Exception $e){
		Log::info($e->getMessage());
		Log::info($e->getCode());
		$message = $e->getMessage();
	}
	return $status;
}


	/**
	*
	*
	*
	*/
	public function mapCodesToPallete(){
		try{
			$status = 0;
			$message = '';

			$pallateId = Input::get('pallate_id');
			$codeNQty = Input::get('code_qty'); ////{"2424242424":1000,"4324343543":3444}
			$refNo = Input::get('reference_no');

			$codeNQty = json_decode($codeNQty);
			if(empty($pallateId) || empty($codeNQty))
				throw new Exception('Either of pallete id or codes are empty');

			$locationId = trim(Input::get('location_id'));		
			$locationObj = new Locations\Locations();
			DB::beginTransaction();
			try{
				$mfgId = $locationObj->getMfgIdForLocationId($locationId);

				if(!$mfgId){
					throw new Exception('Unable to fetch info for given location id');
				}
				$mdate = date('Y-m-d H:i:s');
				foreach($codeNQty as $key=>$val){
					DB::table('pallate_mapping')->insert(
							Array(
									'pallate_id' => $pallateId, 'code' => $key, 
									'qty' => $val, 'reference_no'=> $refNo, 
									'is_active'=>1,'modified_date'=> $mdate));
					if(!$val){
						$val = 0;
					}
					DB::table('eseal_'.$mfgId)->where('primary_id', $key)->orWhere('parent_id', $key)->update( array('pkg_qty'=> 'pkg_qty'.-$val));
				}
			}catch(PDOException $e){
				Log::info($e->getMessage());
				throw new Exception($e->getMessage());
			}
			$status = 1;
			$message = 'Pallate Mapped succesfully';
			DB::commit();
		}catch(Exception $e){
			Log::info($e->getMessage());
			$message = $e->getMessage();
			DB::rollback();
		}
		return json_encode(Array('Status' => $status, 'Message' => $message));
	}

	public function resetPallate(){
		try{
			$status = 0;
			$message = '';

			$pallateId = Input::get('pallate_id');
			$pallateIds = explode(',', $pallateId);
			DB::beginTransaction();
			try{
				$i = 0;
				foreach($pallateIds as $pCode){
					$i = DB::table('pallate_mapping')->where('pallate-id', $pCode)->where('is_active', 1)->update(array('is_active'=>0));
					$i += $i;
				}
				$status = 1;
				if($i>0){
					$message = 'Pallates reset successfully';
				}else{
					$message = 'Either already reset or does not exists';
				}
				
			}catch(PDOException $e){
				Log::info($e->getMessage());
				throw new Exception($e->getMessage());
			}
			DB::commit();
		}catch(Exception $e){
			DB::rollback();
			$message = 'Message occured while resetting pallate data';
		}
		return json_encode(Array('Status'=>$status, 'Message'=> $message));
	}


	public function getPallateData(){
		try{
			$status = 0;
			$message = '';
			$locationId = trim(Input::get('location_id'));		

			$locationObj = new Locations\Locations();
			$mfgId = $locationObj->getMfgIdForLocationId($locationId);
			$esealTable =  'eseal_'.$mfgId;

			try{
				$res = DB::table('pallate_mapping as pm')
							->leftJoin($esealTable.' as es', function($join){
									$join->on('pm.code','=','es.primary_id')->orOn('pm.code','=','es.parent_id');
								})
							->select('pm.pallate_id, pm.code, pm.qty, es.batch_no, pm.modified_date')
							->where('pm.is_active',1)
							->groupBy('pm.pallate_id', 'pm.code')
							->get();
				if(count($res)){
					$status = 1;
					$message = 'Data retrieved succesfully';
				}else{
					$message = 'Data not found';
				}
			}catch(PDOException $e){
				Log::info($e->getMessage());
				throw new Exception($e->getMessage());
			}
		}catch(Exception $e){
			Log::info($e->getMessage());
			$message = 'Exception occured while retrieving pallate data';
		}
		return json_encode(Array('Status'=> $status, 'Message'=>$message, 'Data'=> $res));
	}

	private function mapPallate($pallateId, $refNo, $code, $qty, $mfgId){
		$mdate = date('Y-m-d H:i:s');
		$status = 1;
		try{
			$pallate = new PalletMap;
			$pallate->pallate_id = $pallateId;
			$pallate->code = $code;
			$pallate->qty = $qty;
			$pallate->reference_no = $refNo;
			$pallate->is_active = 1;
			$pallate->modified_date = date('Y-m-d H:i:s');
			$pallate->save();

			DB::table('pallate_mapping')->insert(
					Array(
							'pallate_id' => $pallateId, 'code' => $code, 
							'qty' => $qty, 'reference_no'=> $refNo, 
							'is_active'=>1,'modified_date'=> $mdate)
					);
			if(empty($qty)){
				$qty = 0;
			}
			DB::table('eseal_'.$mfgId)
			   ->where('primary_id', $code)
			   ->orWhere('parent_id', $code)
			   ->update( array('pkg_qty'=> 'pkg_qty'.-$qty) );
		}catch(PDOException $e){
			Log::info($e->getMessage());
			$status = 0;
		}
		return $status;
	}


	public function salePallateData(){
		$startTime = $this->getTime();
		try{
			$status = 0;
			$message = 'Failed to do stockout/sale';
			$pallateIdNCodes = trim(Input::get('ids'));
			$tp =  trim(Input::get('tp'));
			$refNo = trim(Input::get('ref_no'));
			$srcLocationId = trim(Input::get('srcLocationId'));    
			$destLocationId = 0;
			$destLocationId = trim(Input::get('destLocationId'));    
			$transitionTime = trim(Input::get('transitionTime'));
			$transitionId = trim(Input::get('transitionId'));

			DB::beginTransaction();
			$locationObj = new Locations\Locations();
			$mfgId = $locationObj->getMfgIdForLocationId($srcLocationId);
			if($mfgId){
			  $esealTable = 'eseal_'.$mfgId;
			  $esealBankTable = 'eseal_bank_'.$mfgId;

			  $pallateIdNCodes = json_decode($pallateIdNCodes);

			  $explodeIds = array();
			  foreach($pallateIdNCodes as $pallateData){
				  $pallateID = $pallateData->pallateid;
				  foreach($pallateData->data as $subData){
					$code = $subData->code;
					$qty =  $subData->qty;
					if(!$this->mapPallate($pallateID, $refNo, $code, $qty, $mfgId)){
						throw new Exception('Exception occured during pallate mapping');
					}
					array_push($explodeIds, $code);
				  }
				}

			$idCnt = count($explodeIds);

		//	Log::info(print_r(Input::get(),true));

			////Check if these ids have already some tp
			$tpCount = DB::table($esealTable.' as eseal')->join($this->trackHistoryTable.' as th', 'eseal.track_id', '=', 'th.track_id')
					->whereIn('primary_id', $explodeIds)
					->where('tp_id','!=', 0)
					->where('dest_loc_id', '>', 0)
					->select('tp_id')
					->distinct()
					->get();

		//	Log::info(count($tpCount));
			if(count($tpCount)){
				throw new Exception('Some of the codes are already assigned some TPs');
			}

			//Check if TP Id already Used
			$result = DB::table($esealBankTable)->where('id', $tp)->select('id', 'used_status')->get();
			Log::info($result);
			if($result[0]->used_status){
				throw new Exception('TP is already used');
			}

			//Check if TP id is either downloaded or issued
			//$cnt = DB::table($esealBankTable)->where('id',$codes)->where('issue_status',0)->orWhere('download_status',0)->count();
			$cnt = DB::table($esealBankTable)->where('id', $tp)
						->where('issue_status',0)
						->where('download_status',0)
						->count();

		//	Log::info($cnt);
			if($cnt){
				throw new Exception('Can\'t used as TP.');
			}
			 
			 ///Check if its a valid tp
			//$cnt = DB::table($esealBankTable)->where('id',$codes)->where('issue_status',1)->orWhere('download_status',1)->count();
			$cnt1 = DB::table($esealBankTable)->where('id', $tp)
					->where(function($query){
						$query->where('issue_status',1);
						$query->orWhere('download_status',1);
					})->count();

		//	Log::info($cnt1);
			if(!$cnt1){
				throw new Exception('Not a valid TP.');
			} 
			//Check if all codes exists in db
			$result = DB::table($esealTable)->whereIn('primary_id', $explodeIds)->count();
		//	Log::info('===='.print_r($result,true));
			if($idCnt != $result){
				throw new Exception('Some of the codes not exists in database');
			}

			//////////CHECK IF ALL THE IDS HAVE SAME SOURCE LOCATION ID As SUPPLIED
			$transitCnt = DB::table($esealTable.' as eseal')->join($this->trackHistoryTable.' as th', 'eseal.track_id', '=', 'th.track_id')
				->whereIn('primary_id', $explodeIds)
					->select('src_loc_id','dest_loc_id')->groupBy('src_loc_id','dest_loc_id')->get();
		//			Log::info($transitCnt);
			if(count($transitCnt)>1){
				throw new Exception('Some of the codes are available with different location');
			}
			if(count($transitCnt) == 1){
				if($transitCnt[0]->dest_loc_id>0){
					throw new Exception('Some of the codes are available with different location');   
				}
			}
			  
			/*if(!empty($tpDataMapping)){
				$status = $this->mapTPAttributes($codes, $esealTable, $srcLocationId, $tpDataMapping, $transitionTime);
				if(!$status){
					throw new Exception('Failed during mapping TP Attributes');
				}
				$this->checkNUpdateOrder($tpDataMapping);
			} */     

			$ids = implode(',', $explodeIds);
			$status = $this->saveTPData($tp, $srcLocationId, $destLocationId, '', $ids, $transitionTime, '');
			if(!$status){
				throw new Exception('Failed during saving TP data');
			}
			  
			//$trackResult = $this->trackUpdate();
			$trackResult = $this->saveStockIssue($tp, $ids, $srcLocationId, $destLocationId, $transitionTime, $transitionId);
			Log::info(gettype($trackResult));
			$trackResultDecode = json_decode($trackResult);
			Log::info(print_r($trackResultDecode, true));
			if(!$trackResultDecode->Status){
				throw new Exception($trackResultDecode->Message);
			}
			try{
			DB::table($esealBankTable)->whereIn('id', array($tp))->update(Array(
				'used_status'=>1,
				'level'=>9,
				'location_id' => $srcLocationId
			  ));		
			}catch(PDOException $e){
				throw new Exception($e->getMessage());
			}
			$status = 1;
			$message = 'Stock out done successfully';

			DB::commit();
			}else{
				throw new Exception('Failed to get customer id for given location id');
			}

		}catch(Exception $e){
			$message = $e->getMessage();
			Log::error($e->getMessage());
			DB::rollback();
		}
		$endTime = $this->getTime();
		Log::info(__FUNCTION__.' Finishes execution in '.($endTime-$startTime));
		Log::info(Array('Status'=>$status, 'Message' => $message));
		return json_encode(Array('Status'=>$status, 'Message' => $message));
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

        public function saveStorageLocations(){
 		try{
                       Log::info(__FUNCTION__.' : '.print_r(Input::get(),true));
  			$status =1;
 			$message ='Storage Locations Saved';
 			$mfgId = $this->roleAccess->getMfgIdByToken(Input::get('access_token')); 			
 			$plant_erp_code = Input::get('plant_erp_code');
 			$storage_loc_name = Input::get('storage_loc_name');
 			$storage_loc_code = Input::get('storage_loc_code');


 			if(empty($plant_erp_code) || empty($storage_loc_name) || empty($storage_loc_code))
 				throw new Exception('Parameters Missing');

 			$locationId = DB::table('locations1')->where('erp_code',$plant_erp_code)->pluck('location_id');

 			if(empty($locationId))
 				throw new Exception('The location with given plant code doesnt exists');

 			$storage_loc_type = DB::table('master_lookup')->where(['category_id'=>25,'name'=>$storage_loc_name])->pluck('value');
 			if(empty($storage_loc_type))
 				throw new Exception('There is no storage location type defined in master lookup: '.$storage_loc_name);

 			$alreadyExists = DB::table('locations1')
 			                 ->where(['manufacturer_id'=>$mfgId,'storage_location_type_code'=>$storage_loc_type,'parent_location_id'=>$locationId])
 			                 ->count();

 			if($alreadyExists)
 			 throw new Exception('The storage location already exists');


            $location_type_id = DB::table('location_types')
                                 ->where(['manufacturer_id'=>$mfgId,'location_type_name'=>'Storage Location'])
                                 ->pluck('location_type_id');

            if(empty($location_type_id))
            throw new Exception('The storage location type is not configured');


             $business_unit_id = DB::table('locations1')->where('location_id',$locationId)->pluck('business_unit_id');

            DB::beginTransaction();
 	     $sql = 'insert into locations1 (location_name,manufacturer_id,parent_location_id,location_type_id,erp_code,business_unit_id,storage_location_type_code) values ("'.$storage_loc_name.'",'.$mfgId.','.$locationId.','.$location_type_id.',"'.$storage_loc_code.'",'.$business_unit_id.',"'.$storage_loc_type.'")';
            
            

            DB::insert($sql);


            $latest_location_id = DB::getPdo()->lastInsertId();

          //  Log::info('lastest location id'.$latest_location_id);



            //Log::info('New location created successfully');
            DB::commit();

 		}
 		catch(Exception $e){
 			$status=0;
 			$message = $e->getMessage();
 			DB::rollback();
 		}
 		Log::info(['Status'=>$status,'Message'=>$message]);
 		return json_encode(['Status'=>$status,'Message'=>$message]);
 	}


     public function reverseSalesDelivery(){
 		try{          

 			Log::info(__FUNCTION__.' : '.print_r(Input::get(),true));
 			$status =1; 			
            $isSapEnabled = 0 ;			
			$delivery_no = (int)trim(Input::get('delivery_no'));			
			$purchase_no = (int)trim(Input::get('purchase_order_no'));
			$grn_no = (int)trim(Input::get('grn_no'));
			$storage_loc_code = trim(Input::get('storage_loc_code'));
            $from_storage_loc_code = trim(Input::get('from_storage_loc_code'));
			$movementType = trim(Input::get('movement_type'));
			$ids = trim(Input::get('ids'));
			$codes =  trim(Input::get('codes'));
			$remarks = Input::get('remarks');			
			$invoice_no = (Input::get('invoice_no')== true) ? Input::get('invoice_no') : '';
            $cost_centre = (Input::get('cost_centre')== true) ? Input::get('cost_centre') : '';
            $vendor_code = (Input::get('vendor_code')== true) ? Input::get('vendor_code') : '';
			$mfgId = $this->roleAccess->getMfgIdByToken(Input::get('access_token'));
			$srcLocationId = $this->roleAccess->getLocIdByToken(Input::get('access_token'));    			  
			$transitionTime = $this->getDate();
			$transitionId = trim(Input::get('transitionId'));
			$isSapEnabled = trim(Input::get('isSapEnabled'));
			$isInternalTransfer = Input::get('isInternalTransfer');
            $isInternalReversal = Input::get('isInternalReversal');
			$esealTable = 'eseal_'.$mfgId;
			$esealBankTable = 'eseal_bank_'.$mfgId;
			$destLocationId = 0;
            $is_active =1;
            $batchGroupBy = true;
			$flag = false; /////enable the flag if the document is a purchase order or internal goods movement////
			$documentNumber1 = "";
                        $documentNumber2 = "";
                        $uomArray = array();
                     
			
			
			if(empty($remarks))
				$remarks ="";


			if(empty($storage_loc_code))
				$storage_loc_code ="";

			if($transitionTime > $this->getDate())
				$transitionTime = $this->getDate();

			if(empty($ids) || empty($transitionId) || empty($transitionTime))
				throw new Exception('Parameters Missing.');
			
                       if($movementType == 541){
                          $storage_loc_code = "";
                        }

			 if($delivery_no)
	    {
	    $method = 'Z036_ESEAL_GET_DELIVERY_DETAIL_SRV'; 
		$method_name ='DELIVER_DETAILS';
		$this->_method = 'Z0038_ESEAL_UPDATE_DELIVERY_SRV';
		$this->_method_name = 'DELIVERY';							
		$data =['DELIVERY'=>$delivery_no];
		$documentTag = 'D:GET_DELIVER';
		$materialTag = 'MATERIAL_CODE';
		$storeTag = 'STORE_LOCATION';
		$serialNoTag = 'NO';
        $batchGroupBy = true;
		$message = 'Reverse Delivery Successfull.';
	    }
	    else{

	    	$this->_method = 'ZESEAL_047_DO_MIGO_SRV';
		    $this->_method_name = 'DO_MIGO';	

	    	if($purchase_no){
	    		$documentNumber1 =$purchase_no;              	
	    		$method = 'Z0049_GET_PO_DETAILS_SRV';
		        $method_name = 'PURCHASE';		        						
		        $data =['PO'=>$purchase_no];
		        $documentTag = 'D:GET_PO';       
		        $materialTag = 'MAT_CODE';
		        $storeTag = 'STO_LOC';
		        $serialNoTag = 'NO';
		        $movementType = 161;
		        $flag = true;
		        $message = 'Purchase Returns Successfull.';
		        
	    	}
	    	else{
              if($grn_no){              	
              	$documentNumber2 = $grn_no;
	    		$method = 'Z029_ESEAL_GET_GRN_DATA_SRV';
				$method_name = 'GRN_OUTPUT';		        						
		        $data =['DOCUMENT'=>$grn_no];
		        $documentTag = 'D:GET_GRN';       
		        $materialTag = 'MATERIAL_CODE';
		        $storeTag = 'STORAGE_LOC_CODE';
		        $serialNoTag = 'SNO';
		        $movementType = 122;
		        $flag = true;
		        $message = 'Purchase Returns against GRN Successfull.';
		    }
	    	else{
	    		if(!$isInternalTransfer){
	    		  throw new Exception('Please pass document no.');
	    		}
	    		else{
	    			$message = 'Internal Goods Movement Successfull.';
	    			$flag = true;
	    			if(empty($movementType))
	    				throw new Exception('Parameters missing');
	    			if(empty($storage_loc_code)){
	    				if($movementType != 541 && $movementType != 542)
	    					throw new Exception('Parameters missing');
	    			}

	    			$plant_code = Location::where('location_id',$srcLocationId)->pluck('erp_code');
	    			$store_code = Location::where(['manufacturer_id'=>$mfgId,'parent_location_id'=>$srcLocationId,'storage_location_type_code'=>25001])->pluck('erp_code');

	    			if(empty($store_code))
	    				throw new Exception('There is no un-restricted storage location configured under the plant');
                               ////***new store location code***////

	    			$idsStoreLocations = DB::table($esealTable)
	    			                          ->where(function($query) use($ids){
								$query->whereIn('primary_id',explode(',',$ids));
								$query->orWhereIn('parent_id',explode(',',$ids));
							})->distinct()->lists('storage_location');

	    			if(count($idsStoreLocations) > 1)
	    				throw new Exception('The scanned products belong to more than one storage location');

	    			if(!empty($idsStoreLocations[0]))
	    				$store_code = $idsStoreLocations[0];

                               if($isInternalReversal)
	    				$srcLocationId = Input::get('srcLocationId');

                               if($isInternalReversal && $movementType == 542)
	    				$srcLocationId = Input::get('vendorLocationId');

	    			if($store_code == $storage_loc_code)
	    				throw new Exception('The stock is already available in '.$storage_loc_code);


	    			//if($isInternalReversal)
	    			//	$store_code = $from_storage_loc_code;

	    			


	    			////****end of new store location code****///
	    		}	    		
	       }
	    }

	    }


			$explodeIds = explode(',',$ids);
			$explodeIds = array_unique($explodeIds);
			$idsCnt = count($explodeIds);
            

			$locationDetails = DB::table('eseal_'.$mfgId.' as es')
			                       ->join('track_history as th','th.track_id','=','es.track_id')
			                       ->whereIn('es.primary_id',$explodeIds)
			                       ->groupBy('src_loc_id','dest_loc_id')
			                       ->get(['src_loc_id','dest_loc_id']);

			//Log::info($locationDetails);

            ///////Required Validations////////

   			$matchedCnt = DB::table('eseal_'.$mfgId)->whereIn('primary_id',$explodeIds)->count();

			if($matchedCnt != $idsCnt)
				throw new Exception('Printed IOT\'s missing.');
        
            

            if(!$flag){
		
			foreach($locationDetails as $location){
				if($location->dest_loc_id == 0)
					throw new Exception('Some of the IOT\'s are already received at some location.');
			}

			$locTypes = Loctype::where('manufacturer_id',$mfgId)->whereIn('location_type_name',['Depot','Warehouse','Plant'])->lists('location_type_id');

			foreach($locationDetails as $location){
				$sourceLocations[] = $location->src_loc_id;
			}


			if(Location::whereIn('location_id',$sourceLocations)->whereNotIn('location_type_id',$locTypes)->count())
				throw new Exception('The delivery location of the IOT\'s is neither a depot nor a warehouse');

		  }

		  else{
           
           foreach($locationDetails as $location){
				if($location->dest_loc_id != 0)
					throw new Exception('Some of the IOT\'s are still in-transit.');
			}

			foreach($locationDetails as $location){
				$srcLocation[] = $location->src_loc_id; 
			}
			$srcLocation = array_unique($srcLocation);

			if(count($srcLocation) > 1)
				throw new Exception('The IOT\'s are present in more than one location');

			if(!in_array($srcLocationId,$srcLocation))
				throw new Exception('The IOT\'s are present in another location');
           if(!$isInternalTransfer){
            $destinationArray = DB::table('eseal_'.$mfgId.' as es')
                                   ->join('track_details as td','td.code','=','es.primary_id')
			                       ->join('track_history as th','th.track_id','=','td.track_id')
			                       ->whereIn('es.primary_id',$explodeIds)
			                       ->where('th.dest_loc_id',$srcLocationId)
			                       ->distinct()
			                       ->lists('src_loc_id');			

			if(count(array_unique($destinationArray)) > 1)
			  throw new Exception('The IOT\'s are transferred from more than one location to the present location');
            
            //Log::info($destinationArray);            
            $srcLocationId = $destinationArray[0];
        }
			

		  }
		
		

            //////End of validations//////////

            if($movementType == 542)
		 $srcLocationId = Input::get('srcLocationId');


            if($movementType == 541){
                                      if(empty($vendor_code))
                                        throw new Exception('Vendor code must be passed.');

                                   $srcLocationId =  DB::table('locations')->where(['manufacturer_id'=>$mfgId,'erp_code'=>$vendor_code])->pluck('location_id');

                                   if(!$srcLocationId)
                                     throw new Exception ('Vendor location is not configured:'.$vendor_code);
            }


			/******************START OF REVERSAL IN ESEAL**********************/

             $transactionObj = new TransactionMaster\TransactionMaster();
		$transactionDetails = $transactionObj->getTransactionDetails($mfgId, $transitionId);
		//Log::info(print_r($transactionDetails, true));
		if($transactionDetails){
		  $srcLocationAction = $transactionDetails[0]->srcLoc_action;
		  $destLocationAction = $transactionDetails[0]->dstLoc_action;
		  $inTransitAction = $transactionDetails[0]->intrn_action;
		}else{
		throw new Exception('Unable to find the transaction details');
	  }
		
	  
	  //Log::info('SrcLocAction : ' . $srcLocationAction.' , DestLocAction: '. $destLocationAction.', inTransitAction: '. $inTransitAction);
	  
	  
	  //Log::info(__LINE__);
	   DB::beginTransaction();	
	   
		$trakHistoryObj = new TrackHistory\TrackHistory();
		try{
			$lastInrtId = DB::table($this->trackHistoryTable)->insertGetId( Array(
				'src_loc_id'=>$srcLocationId, 'dest_loc_id'=>$destLocationId, 
				'transition_id'=>$transitionId,'update_time'=>$transitionTime));
			//Log::info($lastInrtId);

			$maxLevelId = 	DB::table($esealTable)
								->whereIn('parent_id', $explodeIds)
								->orWhereIn('primary_id', $explodeIds)->max('level_id');

            //Component Trackupdating

			$res = DB::table($esealTable)->where('level_id', 0)
							->where(function($query) use($explodeIds){
								$query->whereIn('primary_id',$explodeIds);
								$query->orWhereIn('parent_id',$explodeIds);
							})->lists('primary_id');
								
			if(!empty($res)){
				
				$attributeMaps =  DB::table('bind_history')->whereIn('eseal_id',$res)->distinct()->lists('attribute_map_id');

				$componentIds =  DB::table('attribute_mapping')->whereIn('attribute_map_id',$attributeMaps)->where('attribute_name','Stator')->lists('value');
				
				if(!empty($componentIds)){
						$componentIds = array_filter($componentIds);
						$explodeIds = array_merge($explodedIds,$componentIds);
				}

			}
//End Of Component Trackupdating

			if(!$this->updateTrackForChilds($esealTable, $lastInrtId, $explodeIds, $maxLevelId)){
				throw new Exception('Exception occured during track updation');
			}
			
			//Log::info(__LINE__);
			$sql = 'INSERT INTO  '.$this->trackDetailsTable.' (code, track_id) SELECT primary_id, '.$lastInrtId.' FROM '.$esealTable.' WHERE track_id='.$lastInrtId;
			DB::insert($sql);
			Log::info(__LINE__);
                       /***********start of storage location code****/////
			if($isInternalReversal){

                 if($movementType == 343)
		   $is_active = 1;	


          DB::table($esealTable)->whereIn('primary_id',$explodeIds)->update(['storage_location'=>$storage_loc_code,'is_active'=>$is_active]);
          $message = 'Internal Goods Movement Reversal Successfull';
          goto commit;              
			}			

	 /*********endo of storage location code*****////////	
			
		}catch(PDOException $e){
			Log::info($e->getMessage());
			throw new Exception('SQlError during track update');
		}


	
		/*******************END OF REVERSAL IN ESEAL***********************88/

        /*$$$$$$$$$$$$$$$ START OF REVERSAL IN SAP $$$$$$$$$$$$$$$$$$$$$$$$$*/

             Log::info('out of if');             
             $erpToken = DB::table('erp_integration')->where('manufacturer_id',$mfgId)->pluck('token');
             $erpUrl = DB::table('erp_integration')->where('manufacturer_id',$mfgId)->pluck('web_service_url');

            if(!$isInternalTransfer){
            	Log::info('in of if');
                
				  //SAP call for getting Document Details.
				  $response =  $this->sapCall($mfgId,$method,$method_name,$data,Input::get('access_token'));
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
					
					if($xml_array['HEADER']['Status'] == 0)
						throw new Exception($xml_array['HEADER']['Message']);

					if(isset($xml_array['DATA']['ORDERTYPE']) && !empty($xml_array['DATA']['ORDERTYPE'])){
                         if($xml_array['DATA']['ORDERTYPE'] == 'ZRPI')
                         	$batchGroupBy = true;
					}

					$data = $xml_array['DATA']['ITEMS'];
					
					if(!array_key_exists($serialNoTag, $data['ITEM'])){
                        foreach($data['ITEM'] as $data1){                        	
                        	$uomArray[] = ['MATERIAL_CODE'=>$data1[$materialTag],'UOM'=>$data1['UOM'],'ITEM_CATEG'=>$data1['ITEM_CATEG'],'QTY'=>$data1['QUANTITY']]; 
                        }
					}
					else{
						$data2 = $data['ITEM'];						
						$uomArray[] = ['MATERIAL_CODE'=>$data2[$materialTag],'UOM'=>$data2['UOM'],'ITEM_CATEG'=>$data2['ITEM_CATEG'],'QTY'=>$data2['QUANTITY']];

					}
				}
				else{
					throw new Exception($response->Message);
				}

				}
				else{
Log::info('in of else');
                  $uomResult = DB::table('eseal_'.$mfgId.' as es')
                                  ->join('products as pr','pr.product_id','=','es.pid')
                                  ->join('uom_classes as uc','uc.id','=','pr.uom_class_id')
                                  ->where('es.level_id',0)
								  ->where('pr.product_type_id',8003)
										->where(function($query) use($explodeIds){
													 $query->whereIn('es.parent_id', $explodeIds)
														   ->orWhereIn('es.primary_id',$explodeIds);
											 }
											 )
                                  ->groupBy('pr.product_id')
                                  ->get(['uom_code','material_code',DB::raw('case when uom_code="M" then sum(pkg_qty) else count(eseal_id) end as qty')]);


                   foreach($uomResult as $uom){
                   	$uomArray[] = ['MATERIAL_CODE'=>$uom->material_code,'UOM'=>$uom->uom_code,'ITEM_CATEG'=>'DEFAULT','QTY'=>$uom->qty];
                   }               



				}

                    Log::info('UOM ARRAY');
             		Log::info($uomArray);

             		$deliver1 = array();             		
                    $materialArr =  array();
					
                foreach($uomArray as $uom){

                	if(!in_array($uom['MATERIAL_CODE'],$materialArr)){
                      $materialArr[] = $uom['MATERIAL_CODE'];

					if($uom['UOM'] == 'M'){	

                          $deliver1 =  DB::table('eseal_'.$mfgId.' as es')
										->join('products','products.product_id','=','es.pid')
										->where('es.level_id',0)
										->where('products.product_type_id',8003)
										->where(function($query) use($explodeIds){
													 $query->whereIn('es.parent_id', $explodeIds)
														   ->orWhereIn('es.primary_id',$explodeIds);
											 }
											 )
										->where('products.material_code',$uom['MATERIAL_CODE']);
                                
                                           if($batchGroupBy)										
		                                      $deliver1->groupBy('batch_no');
										
			  $deliver1 = $deliver1->get([DB::raw('primary_id as id'),DB::raw('sum(pkg_qty) as qty'),'batch_no','material_code','products.is_serializable']);					        
                    
                    }
                    else{					
                           $deliver1 =  DB::table('eseal_'.$mfgId.' as es')
										->join('products','products.product_id','=','es.pid')
										->where('es.level_id',0)
										->where('products.product_type_id',8003)
										->where(function($query) use($explodeIds){
													 $query->whereIn('es.parent_id', $explodeIds)
														   ->orWhereIn('es.primary_id',$explodeIds);
											 }
											 )
										->where('products.material_code',$uom['MATERIAL_CODE']);
								
								if($batchGroupBy)										
										$deliver1->groupBy('batch_no','primary_id');		
					            
					                           if($batchGroupBy){					
									$deliver1 = $deliver1->get([DB::raw('primary_id as id'),DB::raw('CASE WHEN multiPack=0 THEN 1 ELSE pkg_qty END AS qty'),'batch_no','material_code','products.is_serializable']);					        
									}
									else{
                                    $deliver1 = $deliver1->get([DB::raw('primary_id as id'),DB::raw('CASE WHEN multiPack=0 THEN count(eseal_id) ELSE sum(pkg_qty) END AS qty'),'batch_no','material_code','products.is_serializable']);					        
									}


                    }
                    $deliver[] = $deliver1;
                }
                    }
                    
                    Log::info('materialsssss:-');
                    Log::info($deliver);
					
					$itemArr = array();
					foreach ($deliver as $arr) {
                     foreach($arr as $item){
                    array_push($itemArr,$item);
                }
						
					}

					$deliver = $itemArr;
					Log::info('System Materials:-');
					Log::info($deliver);
					foreach($deliver as $dev){
						$devArr[] = trim($dev->material_code);
					}
					if(!$isInternalTransfer){
					$matArr = array();
					if(!array_key_exists($serialNoTag, $data['ITEM'])){
						foreach($data['ITEM'] as $data1){
							foreach($data1 as $key=>$value){
						if(is_array($value) && empty($value)){
							$data1[$key] = '';
						}
					}
							$plant_code = $data1['PLANT'];
							$store_code = $data1[$storeTag];
							$material_code = $data1[$materialTag];

							if(!in_array($material_code,$matArr)){
							$matArr[] = trim($material_code);
							$matr[] = ['plant_code'=>$plant_code,'store_code'=>$store_code,'material_code'=>$material_code];
						}
						
					}
					Log::info('eseal Unique materials:-');
					Log::info(array_unique($devArr));
					Log::info('erp Unique materials:-');
					Log::info(array_unique($matArr));


					if(array_diff($devArr,$matArr))
             			throw new Exception('The line items are not matching.');
					
					}
					else{
						 $data = $data['ITEM'];
						 
						foreach($data as $key=>$value){
							
							if(is_array($value) && empty($value)){
							$data[$key] = '';
						}

						}
						
						$plant_code = $data['PLANT'];
						$store_code = $data[$storeTag];
						$material_code = $data[$materialTag];
						$matr[] = ['plant_code'=>$plant_code,'store_code'=>$store_code,'material_code'=>$material_code];
						$devArr = array_unique($devArr);

						if(!in_array($material_code,$devArr) || count($devArr) > 1){
						   $status =0;
						   Log::info('eseal ARR:-');
						   Log::info($devArr);
						   throw new Exception('Materials mismatched');				  			
						}

					}
				}

					   
					$x =0;
					$qty = 0;
					$xx= array();   
					
					if(!$flag){
					$xml = '<?xml version="1.0" encoding="utf-8" ?>
					<REQUEST>
						<DATA>
							<INPUT TOKEN="'.$erpToken.'" ESEALKEY="'.$this->getRand().'" />    
							<SUMMARY>';
						}
						else{
						$xml = '<?xml version="1.0" encoding="utf-8" ?>
					<REQUEST>
						<DATA>
							<INPUT TOKEN="'.$erpToken.'" MOVEMENT_TYPE="'.$movementType.'"  PURCHASE_ORD_NO="'.$documentNumber1.'" PROD_ORD_NO="" GRN_NO="'.$documentNumber2.'" INVOICE="'.$invoice_no.'" DELIVERY="" FROM_PLANT="'.$plant_code.'" TO_PLANT="" ASSET_NO="" COST_CENTER_NO="'.$cost_centre.'" VENDOR="'.$vendor_code.'"/>
							<ITEMS>';						
						}
								
								foreach($uomArray as $uom){								   

								   if(1 == 1){
									$x ++;

									$xx[$uom['MATERIAL_CODE']] = $uom['ITEM_CATEG'];
                                    $mainQty = $uom['QTY'];
						
									
									//$xx1[] = ['batch_no'=>$item->batch_no,'cnt'=>$x,'material_code'=>$item->material_code];
									$y =0;
									$batchArray = array();
                                   
                                   $deliver2 =  array_values($deliver);
                                   $deliver = $deliver2; 
                                   $count = count($deliver);

                                   
                                  
								   for($i=0;$i < $count;$i++){
                                 
                                      


								   	
									if($deliver[$i]->material_code == $uom['MATERIAL_CODE']){

									  if($deliver[$i]->qty == 1){
										$y++;

										$batch_no = $deliver[$i]->batch_no;
                                        Log::info($batch_no);

										unset($deliver[$i]);
                                             if(isset($batchArray[$batch_no])){
                                      $batchArray[$batch_no] = ['QTY'=>$batchArray[$batch_no]['QTY'] + 1,'ITEM_CATEG'=>$uom['ITEM_CATEG']];
                                    } 
                                    else{
                                      $batchArray[$batch_no] = ['QTY'=>1,'ITEM_CATEG'=>$uom['ITEM_CATEG']];
                                    }
                                       }
                                     else{

                                      $batch_no = $deliver[$i]->batch_no;
                                      Log::info('inside else');
                                      Log::info($deliver[$i]->qty);
                                      Log::info($mainQty);

                                   if((int)$deliver[$i]->qty > (int)$mainQty){
                                        Log::info('inside 1st');
                                        $deliver[$i]->qty = $deliver[$i]->qty - $mainQty;
                                        $qty = $mainQty;
                                        $mainQty = 0;
                                   }
                               else{
                                   if((int)$deliver[$i]->qty < (int)$mainQty){
                                       Log::info('inside 2nd');
                                         $qty = $deliver[$i]->qty;
                                       Log::info('eyyyyyyyyyyyxxxxx');
                                         unset($deliver[$i]);
                                         $mainQty = (int)$mainQty - (int)$qty;
                                       Log::info('oyyyyyyyyyyyyeeeee');
                                   }
                                   else{
                                       Log::info('inside 3rd equal to condition');
                                       $qty = $deliver[$i]->qty;
                                       unset($deliver[$i]);
                                       $mainQty = (int)$mainQty - (int)$qty;
                                   }

                              }
                                //   if((int)$deliver[$i]->qty = (int)$qty){
                                //       Log::info('inside 3rd equal to condition');
                                //       $qty = $deliver[$i]->qty;
                                //         unset($deliver[$i]); 
                                //   }

                                    Log::info('out of all conditions');
                                    Log::info($deliver);
									$y += $qty;                                        									

									if(isset($batchArray[$batch_no])){
                                      $batchArray[$batch_no] = ['QTY'=>$batchArray[$batch_no]['QTY'] + $qty,'ITEM_CATEG'=>$uom['ITEM_CATEG']];
                                    } 
                                    else{
                                      $batchArray[$batch_no] = ['QTY'=>$qty,'ITEM_CATEG'=>$uom['ITEM_CATEG']];
                                    }

                                     }

                                     
									}
								
							if($uom['QTY'] == $y)
								    break;	      

								   }

								     $deliver2 =  array_values($deliver);
                                     $deliver = $deliver2;                                     
   
								    foreach($batchArray as $key => $value){

								 if(!$flag){

								 if(!$batchGroupBy){
                                        $key = '';
                                  	}   	

                                 $xml .='<DELIVER NO="'.$delivery_no.'" METERIAL_CODE="'.$uom['MATERIAL_CODE'].'" BATCH_NO="'.$key.'" ITEM_CATEG="'.$value['ITEM_CATEG'].'" QUANTITY="'.$value['QTY'].'" PLANT="'.$plant_code.'" STORE="'.$store_code.'" COUNT="'.$x.'" />';
                             }
                             else{
                                     $xml .='<ITEM FROM_SLOC="'.$store_code.'" TO_SLOC="'.$storage_loc_code.'" MATERIAL="'.$uom['MATERIAL_CODE'].'" BATCH="'.$key.'" QTY="'.$value['QTY'].'" REMARKS="'.$remarks.'" STOCK_TYPE="01" />';									                                   
                                  }
								
								}
								
								}



								}
								if(!$flag)
								  $xml .= '</SUMMARY><ITEMS>';									
								
								
                              /////updating serials to SAP///////
								foreach($deliver as $dev1){
                                 if($dev1->is_serializable == 1){  

									foreach($xx as $yy){
										if($yy['batch_no'] == $dev1->batch_no && $yy['material_code'] == $dev1->material_code){
											$cnt = $yy['cnt'];
											break;
										}
									}

										$xml .= '<ITEM COUNT="'.$cnt.'" SERIAL_NO="'.$dev1->id.'" />';   
									
									}	
								}
                               /////end of updating serials to SAP///////


                                $xml .= '</ITEMS>';
								$xml .= '</DATA> </REQUEST>'; 								

								Log::info('XML build:-');
								Log::info($xml);

											
								
								

								$cd = '<entry xmlns="http://www.w3.org/2005/Atom" xmlns:m="http://schemas.microsoft.com/ado/2007/08/dataservices/metadata" xmlns:d="http://schemas.microsoft.com/ado/2007/08/dataservices" xml:base="'.$erpUrl.$this->_method.'/">
										<id>'.$erpUrl.$this->_method.'/'.$this->_method_name.'(\'123\')</id>
										<title type="text">'.$this->_method_name.'(\'123\')</title>
										<updated>2015-08-10T08:06:09Z</updated>
										<category term="'.$this->_method.'.'.$this->_method_name.'" scheme="http://schemas.microsoft.com/ado/2007/08/dataservices/scheme" />
										<link href="'.$this->_method_name.'(\'123\')" rel="self" title="'.$this->_method_name.'" />
										<content type="application/xml">
										 <m:properties>
										<d:ESEAL_INPUT>"<![CDATA[';

								$cd .= $xml;
								$cd .= ']]>"</d:ESEAL_INPUT>
										<d:ESEAL_OUTPUT />
										 </m:properties>
										</content>
										 </entry>'	;


								$query = DB::table('erp_integration')->where('manufacturer_id',$mfgId);
								$erp = $query->select('web_service_url','token','company_code','web_service_username','web_service_password','sap_client')->get();

								$this->_url = $erp[0]->web_service_url;
								$token = $erp[0]->token;
								$company_code = $erp[0]->company_code;
								$sap_client = $erp[0]->sap_client;

								$erp = $this->roleAccess->getErpDetailsByUserId(Input::get('access_token'));

								if($erp){
									$username = $erp[0]->erp_username;
									$password = $erp[0]->erp_password;
									}
									else{
										throw new Exception('There are no erp username and password');
								}

                                $method = 'POST';
								$url = $this->_url .$this->_method.'/'.$this->_method_name.'?&sap-client='.$sap_client;
								Log::info('SAP start:-');
								
								$response = $this->sapRepo->request($username,$password,$url, $method,null,'xml',2,null,$cd);
								

								
								Log::info($response);
								
								$parseData1 = xml_parser_create();
								xml_parse_into_struct($parseData1, $response, $documentValues1, $documentIndex1);
								xml_parser_free($parseData1);
								$documentData = array();
								foreach ($documentValues1 as $data) {
									if(isset($data['tag']) && $data['tag'] == 'D:ESEAL_OUTPUT')
									{
										$documentData = $data['value'];
									}
								}
								if(!empty($documentData)){
								$deXml = simplexml_load_string($documentData);
								$deJson = json_encode($deXml);
								$xml_array = json_decode($deJson,TRUE); 
								Log::info('UPDATE REVERSAL array response:');
								Log::info($xml_array); 
								
								if($xml_array['HEADER']['Status'] == 1){

                                                                /****new code for stoarge locations****////
                                    
                                    if($isInternalTransfer){

                                      foreach ($documentValues1 as $data) {
										if(isset($data['tag']) && $data['tag'] == 'D:MAT_DOC_NO')
										{
											$documentNo = $data['value'];
										}
					               }                                                           

                                   if($movementType == 344)
					                  $is_active = 0;
					               if($movementType == 343)
					                  $is_active = 1;


                                      
                                      DB::table($esealTable)
                                            ->whereIn('primary_id',$explodeIds)
                                            ->orWhereIn('parent_id',$explodeIds)
                                            ->update(['storage_location'=>$storage_loc_code,'internal_document'=>$documentNo,'is_active'=>$is_active]);



                                      Log::info('UPDATED STORAGE LOCATION IS :' .$storage_loc_code);
                                    }


                                  /****new code for storage locations****////
									
                                    if(!$flag)
									 DB::table('erp_objects')->where('object_id',$delivery_no)->update(['process_status'=>1]);
									else
									 DB::table('erp_objects')->where('object_id',$purchase_no)->update(['process_status'=>1]);	
									
									$message .= $xml_array['HEADER']['Message'];

                                                                        
								}
								else{
									$status =0;
									throw new Exception ($xml_array['HEADER']['Message']);
								}
							 }
							 else{
								$status =0;
								throw new Exception ('error from SAP call');
							 }
        /*$$$$$$$$$$$$$$$ END OF REVERSAL IN SAP $$$$$$$$$$$$$$$$$$$$$$$*/

    //  }
    //else{

    //	throw new Exception($response->Message);
    //}

         commit:

        DB::commit();

    }

     
 		catch(Exception $e){
 			DB::rollback();
 			$status =0 ;
 			$message = $e->getMessage();
 		}
 		Log::info(['Status'=>$status,'Message'=>$message]);
 		return json_encode(['Status'=>$status,'Message'=>$message]);
 	}


  	/*public function partialReverseDelivery(){
 		try{
 			Log::info(__FUNCTION__.' : '.print_r(Input::get(),true));
			$status =1;
			$message = 'Partial Reverse Delivery Successfull';
			$access_token = trim(Input::get('access_token'));
			$codes = trim(Input::get('codes'));
			$mfg_id = $this->roleAccess->getMfgIdByToken($access_token);
			$location_id = $this->roleAccess->getLocIdByToken($access_token);
			$transitionId = trim(Input::get('transitionId'));
			$transitionTime = trim(Input::get('transitionTime'));
			$esealTable = 'eseal_'.$mfg_id;
			$esealBankTable = 'eseal_bank_'.$mfg_id;
			$destLocationId =0;

            //////Convert IDS into string and array
		    $explodeIds = explode(',', $codes);
		    $explodeIds = array_unique($explodeIds);
		
		   $idCnt = count($explodeIds);
		   $strCodes = '\''.implode('\',\'', $explodeIds).'\'';
		

		
		
		////Check if these ids have already some tp
		$tpCount = DB::table($esealTable.' as eseal')->join($this->trackHistoryTable.' as th', 'eseal.track_id', '=', 'th.track_id')
		->whereIn('primary_id', $explodeIds)
		->where('tp_id','!=', 'unknown')
		->where('dest_loc_id', '>', 0)
		->select('tp_id')
		->distinct()
		->get();

		Log::info(count($tpCount));
		if(!count($tpCount)){
			throw new Exception('Some of the codes are already in inventory at some location');
		}

		if(count($tpCount) > 1){
		   throw new Exception('Some of the codes belong to more than one tp');	
		}

		$track_id = DB::table($esealTable.' as eseal')->whereIn('primary_id',$explodedIds)->distinct()->pluck('track_id');

		$trackHistory = DB::table($this->trackHistoryTable)->where('track_id',$track_id)->get();

		if($trackHistory[0]->src_loc_id != $location_id)
			throw new Exception('The codes are not stock transferred from same location');


		


 		}
 		catch(Exception $e){
 			DB::rollback();
 			$status =0;
 			$message = $e->getMessage();
 		}
 		 Log::info(['Status'=>$status,'Message'=>$message]);
 		 return json_encode(['Status'=>$status,'Message'=>$message]);

 	}*/

    public function scrapEseals(){

 		try{
 			
 			DB::beginTransaction();          
 			Log::info(__FUNCTION__.' : '.print_r(Input::get(),true));
 			$status =1; 			
 			$message = 'Iot\'s scrapped successfully';
   			$ids = trim(Input::get('ids'));
  			$mfgId = $this->roleAccess->getMfgIdByToken(Input::get('access_token'));
  		//	Log::info('mfgId'.$mfgId);
			$srcLocationId = $this->roleAccess->getLocIdByToken(Input::get('access_token'));    			  
			$transitionTime = $this->getDate();
			$transitionId = trim(Input::get('transitionId'));			
            $deScrap = trim(Input::get('deScrap'));	
			$esealTable = 'eseal_'.$mfgId;
			$esealBankTable = 'eseal_bank_'.$mfgId;
			$destLocationId = 0;			
            $active_validation =1;
			$is_active =0;		


			if($transitionTime > $this->getDate())
				$transitionTime = $this->getDate();

			if(empty($ids) || empty($transitionId) || empty($transitionTime))
				throw new Exception('Parameters Missing.');			

            if($deScrap){
			 $is_active =1;
			 $active_validation =0;
   			 $message = 'Iot\'s de-scrapped successfully';

   			 $transitionId = DB::table('transaction_master')->where(array('name'=>'De Scraping','manufacturer_id'=>$mfgId))->pluck('id');
			}

            //////Convert IDS into string and array
		     $explodeIds = explode(',', $ids);
		     $explodeIds = array_unique($explodeIds);
		
		     $idCnt = count($explodeIds);
		     $strCodes = '\''.implode('\',\'', $explodeIds).'\'';

            
			$locationDetails = DB::table('eseal_'.$mfgId.' as es')
			                       ->join('track_history as th','th.track_id','=','es.track_id')
			                       ->whereIn('es.primary_id',$explodeIds)
			                       ->groupBy('src_loc_id','dest_loc_id')
			                       ->get(['src_loc_id','dest_loc_id']);

			//Log::info($locationDetails);

            ///////Required Validations////////

            $matchedCnt = DB::table('eseal_bank_'.$mfgId)
   			               ->whereIn('id',$explodeIds)
   			               ->count();
   			if($matchedCnt==0)
				throw new Exception('Invalid IOTs');
			else{
				$validIOTs = DB::table('eseal_'.$mfgId)
   			                ->whereIn('primary_id',$explodeIds)
                            ->where('is_active',$active_validation)
   			                ->get(['primary_id']);

				if(empty($validIOTs) && !$deScrap)
					throw new Exception('IOTs are already scrapped');
				elseif(empty($validIOTs) && $deScrap){
					throw new Exception('IOTs are already descrapped');	
				}else{
					$temp = array();
	   			    foreach($validIOTs as $validIOT)
	   			    	$temp[]=$validIOT->primary_id;
	   				
	   				//Log::info($temp);
	   			    //Log::info($explodeIds);
					$invalidIOT = array_diff($explodeIds,$temp);
					//Log::info($invalidIOT);
					
					if(!empty($invalidIOT))
					{
						$message = 'Some of the IOTs are invalid';	
						$explodeIds = 	$temp;
						$invalidIOT = implode(",", $invalidIOT);
					}    		
				}
			}
				                    
           foreach($locationDetails as $location){
		
				if($location->dest_loc_id != 0)
					throw new Exception('The IOT\'s is still in-transit.');
			
			
				
				if($location->src_loc_id != $srcLocationId)
				    throw new Exception('The IOT\'s are present in some other location');          



		}


            //////End of validations/////////		

		 


		  //Updating Ids in esealTable with is_active status.
		  DB::table($esealTable)->whereIn('primary_id',$explodeIds)->update(['is_active'=>$is_active]);		                  
            

			/******************START OF SCRAPPING TRACKUPDATE IN ESEAL**********************/

             $transactionObj = new TransactionMaster\TransactionMaster();
		$transactionDetails = $transactionObj->getTransactionDetails($mfgId, $transitionId);
		//Log::info(print_r($transactionDetails, true));
		if($transactionDetails){
		  $srcLocationAction = $transactionDetails[0]->srcLoc_action;
		  $destLocationAction = $transactionDetails[0]->dstLoc_action;
		  $inTransitAction = $transactionDetails[0]->intrn_action;
		}else{
		throw new Exception('Unable to find the transaction details');
	  }
		
	  
	  //Log::info('SrcLocAction : ' . $srcLocationAction.' , DestLocAction: '. $destLocationAction.', inTransitAction: '. $inTransitAction);
	  
	  
	   //Log::info(__LINE__);
	   
	   
		$trakHistoryObj = new TrackHistory\TrackHistory();
		try{
			$lastInrtId = DB::table($this->trackHistoryTable)->insertGetId( Array(
				'src_loc_id'=>$srcLocationId, 'dest_loc_id'=>0, 
				'transition_id'=>$transitionId,'update_time'=>$transitionTime));
			//Log::info($lastInrtId);

			$maxLevelId = 	DB::table($esealTable)
								->whereIn('parent_id', $explodeIds)
								->orWhereIn('primary_id', $explodeIds)->max('level_id');

            //Component Trackupdating

			$res = DB::table($esealTable)->where('level_id', 0)
							->where(function($query) use($explodeIds){
								$query->whereIn('primary_id',$explodeIds);
								$query->orWhereIn('parent_id',$explodeIds);
							})->lists('primary_id');
								
			if(!empty($res)){
				
				$attributeMaps =  DB::table('bind_history')->whereIn('eseal_id',$res)->distinct()->lists('attribute_map_id');

				$componentIds =  DB::table('attribute_mapping')->whereIn('attribute_map_id',$attributeMaps)->where('attribute_name','Stator')->lists('value');
				
				if(!empty($componentIds)){
						$componentIds = array_filter($componentIds);
						$explodeIds = array_merge($explodedIds,$componentIds);
				}

			}
//End Of Component Trackupdating

			if(!$this->updateTrackForChilds($esealTable, $lastInrtId, $explodeIds, $maxLevelId)){
				throw new Exception('Exception occured during track updation');
			}
			
			//Log::info(__LINE__);
			$sql = 'INSERT INTO  '.$this->trackDetailsTable.' (code, track_id) SELECT primary_id, '.$lastInrtId.' FROM '.$esealTable.' WHERE track_id='.$lastInrtId;
			DB::insert($sql);
			//Log::info(__LINE__);

			DB::commit();
			
		}catch(PDOException $e){
			Log::info($e->getMessage());
			throw new Exception('SQlError during track update');
		}		

         /******************END OF SCRAPPING TRACKUPDATE IN ESEAL**********************/           

    }

     
 		catch(Exception $e){
 			DB::rollback();
 			$status =0 ;
 			$message = $e->getMessage();
 		}
 		if(!empty($invalidIOT)){
			Log::info(['Status'=>$status,'Message'=>$message]);
			return json_encode(['Status'=>2,'Message'=>$message,'data'=>$invalidIOT]);
 		}else{
 			Log::info(['Status'=>$status,'Message'=>$message]);
			return json_encode(['Status'=>$status,'Message'=>$message]);
 		}
 		
 	
}
       


 	public function reverseDelivery(){
		try{
			Log::info(__FUNCTION__.' : '.print_r(Input::get(),true));
			$status =1;
			$message = 'Reverse Delivery Successfull';
			$access_token = trim(Input::get('access_token'));
			$delivery = ltrim(Input::get('delivery'),0);
			$isSapEnabled = trim(Input::get('isSapEnabled'));
			$mfg_id = $this->roleAccess->getMfgIdByToken($access_token);
			//$location_id = $this->roleAccess->getLocIdByToken($access_token);
			$transitionId = trim(Input::get('transitionId'));
			$transitionTime = trim(Input::get('transitionTime'));
			$plant_id = ltrim(Input::get('plant_id'),0);
                        $movement_type = Input::get('movement_type');
			$method = 'Z0039_REVERSE_DELIVERY_SRV';
			$method_name = 'REVERSE';
			$esealTable = 'eseal_'.$mfg_id;
			$esealBankTable = 'eseal_bank_'.$mfg_id;
			$reference_value='';
                        $vendor_location_id = '';
			$i =1;
                        $movementTypeArray = [641,101,601,631];
			
			if(!$delivery){
				throw new Exception('Delivery not passed');
			}

                        if(in_array($movement_type, $movementTypeArray))
				throw new Exception('Reversal aborted due to in-valid movement types');

			if(empty($transitionId) || empty($transitionTime))
				throw new Exception('Parameters Missing');


			if($plant_id)
				$location_id = Location::where('erp_code',$plant_id)->pluck('location_id');
			else
				$location_id = $this->roleAccess->getLocIdByToken($access_token);

			
			
			
            $documentData = DB::table('erp_objects')->where('object_id',$delivery)->pluck('response');

            if(empty($documentData))
            	throw new Exception('error in ERP response');

            $deXml = simplexml_load_string($documentData);
			$deJson = json_encode($deXml);
			$xml_array = json_decode($deJson,TRUE); 
			Log::info($xml_array);

			$data = $xml_array['DATA'];
            
            DB::beginTransaction();

			if(!is_array($data['PRODUCTION'])){
				//throw new Exception('Reversal against production order is not possible');
				$production_order_grn = ltrim($data['REF_DOC'],0);


				$ids = DB::table($esealTable)
				            ->where('reference_value',$production_order_grn)
				            ->lists('primary_id');
				if(!$ids)
				  throw new Exception('There is no data for the PORDER GRN:'.$production_order_grn);

				$parentIds = DB::table($esealTable)
				                  ->whereIn('primary_id',$ids)
				                  ->where('parent_id','!=',0)
				                  ->distinct()
				                  ->lists('parent_id');

				if($parentIds)
				  $esealCodes = array_merge($ids,$parentIds);
                
				DB::table($esealTable)->whereIn('primary_id',$esealCodes)->delete();
				$trackIds = DB::table($this->trackDetailsTable)->whereIn('code',$esealCodes)->distinct()->lists('track_id');
				$count = DB::table($this->trackHistoryTable)->whereIn('track_id',$trackIds)->where('dest_loc_id','!=',0)->count();
				if($count > 0)
					throw new Exception('Some of the Ids in PORDER are processed for STO');
				
				DB::table($this->trackDetailsTable)->whereIn('code',$esealCodes)->delete();
				DB::table($this->bindHistoryTable)->whereIn('eseal_id',$esealCodes)->delete();

				DB::table($esealBankTable)
                          ->whereIn('id',$esealCodes)                          
                          ->update(['location_id'=>0,'level'=>0,'used_status'=>0]);
                $message = 'PORDER reversal successfull';
                goto commit;

			}


                                    if(!is_array($data['GRN']) && empty($data['DELIVERY']) && empty($data['PURCHASE_ORDER']) && empty($data['PRODUCTION'])){
				//throw new Exception('Reversal against production order is not possible');
				$internal_grn_no = ltrim($data['GRN'],0);


				$ids = DB::table($esealTable)
				            ->where('internal_document',$internal_grn_no)
				            ->lists('primary_id');
				if(!$ids)
				  throw new Exception('There is no data for the INTERNAL GRN:'.$internal_grn_no);				

				$str = implode(',',$ids);
				

                if((empty($data['FRM_SLOC']) || empty($data['TO_SLOC'])) && $movement_type != 542)
					throw new Exception('The internal storage location fields are empty');

                $from_storage_loc_code = $data['TO_SLOC'];
                $storage_loc_code = $data['FRM_SLOC'];


                $transitionId = DB::table('transaction_master')->where(['name'=>'Internal Transfer Reversal','manufacturer_id'=>$mfg_id])->pluck('id');

                    if(empty($transitionId))
                    	throw new Exception('Internal Transfer Reversal transaction is not created');

                    if($movement_type == 542){
                    	$vendor_code = ltrim($data['VENDOR_NO'],0);
                    	$plant_code = ltrim($data['PLANT_CODE'],0);          	
                    	$location_id = Location::where('erp_code',$plant_code)->pluck('location_id');
                    	$vendor_location_id = Location::where('erp_code',$vendor_code)->pluck('location_id');
                        $storage_loc_code = '';
                    }

                    $request = Request::create('scoapi/reverseSalesDelivery', 'POST', array('module_id'=> Input::get('module_id'),'access_token'=>Input::get('access_token'),'ids'=>$str,'storage_loc_code'=>$storage_loc_code,'from_storage_loc_code'=>$storage_loc_code,'srcLocationId'=>$location_id,'vendorLocationId'=>$vendor_location_id,'transitionTime'=>$this->getDate(),'transitionId'=>$transitionId,'isInternalReversal'=>true,'movement_type'=>$movement_type,'isInternalTransfer'=>true));
					$originalInput = Request::input();//backup original input
					Request::replace($request->input());
					Log::info($request->input());
					$response = Route::dispatch($request)->getContent();//invoke API
					$response = json_decode($response,true);

					if($response['Status'] == 1){
		                $message = 'INTERNAL GOODS MOVEMENT reversal successfull';
                                  goto commit;
					}

					throw new Exception($response['Message']);

			}


			if(!is_array($data['DELIVERY']) || !is_array($data['PURCHASE_ORDER']))
				$delivery_no = ltrim($data['REF_DOC'],0);

            if(!is_array($data['PURCHASE_ORDER']))
              $tp = DB::table($this->TPAttributeMappingTable)->where('reference_value',$delivery_no)->pluck('tp_id');
            else	
              $tp = DB::table($this->TPAttributeMappingTable)->where('value',$delivery_no)->orderBy('id','desc')->pluck('tp_id');

			if(empty($tp))
				throw new Exception('There is no TP associated with delivery:'.$delivery_no);

			try{
				$res = DB::table($this->trackHistoryTable)->where('tp_id', $tp)->orderBy('update_time','desc')->take(1)->get();
					}
					catch(PDOException $e){
						Log::info($e->getMessage());
						throw new Exception('Error during query exceution');
					}
					
					if(!count($res)){
						throw new Exception('Invalid TP');
					}
						//if($res[0]->dest_loc_id == 0)
						//	throw new Exception('The Tp is already received at some location');
                       
                        if($res[0]->dest_loc_id == 0){
                            
                            $i = 0;
                            if(is_array($data['PURCHASE_ORDER'])){
                            /*if($res[0]->src_loc_id == $location_id)
                        		throw new Exception('The reversal has already happened');*/
                           }
                            

                        	$res = DB::table($this->trackHistoryTable)->where('tp_id', $tp)->orderBy('update_time','desc')->take(2)->get();
                        	array_shift($res);



                        }
                        if(!is_array($data['PURCHASE_ORDER'])){

                        	/*Log::info('xxxxx');
                          if($res[0]->dest_loc_id != $location_id)
							throw new Exception('User dont have the permission to change the Tp destination');   
                           
                           $location_id = $res[0]->src_loc_id;
						    Log::info('yyyyy');*/
                          if($i != 0)
                          	throw new Exception('The reversal cant happen because the GRN is not yet created');

                        }
                        else{
						if($res[0]->src_loc_id != $location_id && $i !=0)
							throw new Exception('User dont have the permission to do REVERSE STO');

						if($res[0]->dest_loc_id != $location_id && $i==0)
							throw new Exception('User dont have the permission to do REVERSE GRN');
					   
					   }

					   Log::info('zzzzz');
								
						if($transitionTime < $res[0]->update_time)
							throw new Exception('Tp Reversal not possible due to invalid timestamps.');

		$destLocationId = $res[0]->dest_loc_id;
		
		if($destLocationId != 0 && $i == 0){
			$transitionId = DB::table('transaction_master')
		                         ->where(['name'=>'Reverse GRN','manufacturer_id'=>$mfg_id])
		                         ->pluck('id');
		    $location_id = $res[0]->src_loc_id;                     
		}
		if($destLocationId != 0 && $i == 1)
			$destLocationId = 0;

		



		$idArr = DB::table($this->tpDataTable)->where('tp_id',$tp)->get(['level_ids']);
		
		foreach($idArr as $id){
		$ids[] = $id->level_ids;
		}
		$ids = implode(',',$ids);				

					 
		$transactionObj = new TransactionMaster\TransactionMaster();
		$transactionDetails = $transactionObj->getTransactionDetails($mfg_id, $transitionId);
		Log::info(print_r($transactionDetails, true));
		if($transactionDetails){
		  $srcLocationAction = $transactionDetails[0]->srcLoc_action;
		  $destLocationAction = $transactionDetails[0]->dstLoc_action;
		  $inTransitAction = $transactionDetails[0]->intrn_action;
		}else{
		throw new Exception('Unable to find the transaction details');
	  }
		
	  $explodedIds = explode(',', $ids);
	  $explodedIds = array_unique($explodedIds);

	  Log::info('SrcLocAction : ' . $srcLocationAction.' , DestLocAction: '. $destLocationAction.', inTransitAction: '. $inTransitAction);
	  
	  
	  Log::info(__LINE__);
		
	  //if($srcLocationAction==1 && $destLocationAction==0 && $inTransitAction== -1){//////////////////For stock out
		$trakHistoryObj = new TrackHistory\TrackHistory();
		//Log::info(var_dump($trakHistoryObj));
		
		try{
			$lastInrtId = DB::table($this->trackHistoryTable)->insertGetId( Array(
				'src_loc_id'=>$location_id, 'dest_loc_id'=>$destLocationId, 
				'transition_id'=>$transitionId, 'tp_id'=> $tp, 'update_time'=>$transitionTime));
			Log::info($lastInrtId);

			$maxLevelId = 	DB::table($esealTable)
								->whereIn('parent_id', $explodedIds)
								->orWhereIn('primary_id', $explodedIds)->max('level_id');

//Component Trackupdating

			$res = DB::table($esealTable)->where('level_id', 0)
							->where(function($query) use($explodedIds){
								$query->whereIn('primary_id',$explodedIds);
								$query->orWhereIn('parent_id',$explodedIds);
							})->lists('primary_id');
								
			if(!empty($res)){
				
				$attributeMaps =  DB::table('bind_history')->whereIn('eseal_id',$res)->distinct()->lists('attribute_map_id');

				$componentIds =  DB::table('attribute_mapping')->whereIn('attribute_map_id',$attributeMaps)->where('attribute_name','Stator')->lists('value');
				
				if(!empty($componentIds)){
						$componentIds = array_filter($componentIds);
						$explodedIds = array_merge($explodedIds,$componentIds);
				}

			}
//End Of Component Trackupdating

			if(!$this->updateTrackForChilds($esealTable, $lastInrtId, $explodedIds, $maxLevelId)){
				throw new Exception('Exception occured during track updation');
			}
			
			Log::info(__LINE__);
			$sql = 'INSERT INTO  '.$this->trackDetailsTable.' (code, track_id) SELECT primary_id, '.$lastInrtId.' FROM '.$esealTable.' WHERE track_id='.$lastInrtId;
			DB::insert($sql);
			DB::table($this->trackDetailsTable)->insert(array('code'=> $tp, 'track_id'=>$lastInrtId));
			Log::info(__LINE__);
			
		}catch(PDOException $e){
			Log::info($e->getMessage());
			throw new Exception('SQlError during track update');
		}
	


			if($isSapEnabled == 1){
			$delivery_no = DB::table('tp_attributes')->where(['tp_id'=>$tp,'attribute_name'=>'Document Number'])->pluck('value'); 
			if(!$delivery_no){
				throw new Exception('Delivery no doesnt exist for TP');
			}
			$data = ['DELIVERY'=>$delivery_no]; 
			Log::info('SAP Start:-');
			$response = $this->sapCall($mfg_id,$method,$method_name,$data,$access_token);
			Log::info('SAP End:-');
			
			$response = json_decode($response);
			if($response->Status == 0){
				throw new Exception($response->Message);
			}
			Log::info('CHECK1');
			$parseData1 = xml_parser_create();
			xml_parse_into_struct($parseData1, $response->Data, $documentValues1, $documentIndex1);
			xml_parser_free($parseData1);
			$documentData = array();	
			Log::info('CHECK2');
			foreach ($documentValues1 as $data) {
				if(isset($data['tag']) && $data['tag'] == 'D:MESSAGE')
				{
					$documentData = $data['value'];
				}
			}
			if(empty($documentData)){
				throw new Exception('Error from ERP call');
			}
			Log::info('CHECK3');
			$deXml = simplexml_load_string($documentData);
			$deJson = json_encode($deXml);
			$xml_array = json_decode($deJson,TRUE); 
			Log::info('CHECK4');
			if($xml_array['HEADER']['Status']){

				$status =1;
				$message ='Reverse Delivery Successfull and ERP updated';
				

			}
			else{
				throw new Exception($xml_array['HEADER']['Message']);
			}
		}
	//}
	/*else{
		throw new Exception('Please pass the correct transaction id');
	}*/

        commit:
		DB::commit();
		
		}
		catch(Exception $e){
			$status =0;
			DB::rollback();
			$message = $e->getMessage();
		}
		Log::info(['Status'=>$status,'Message'=>$message]);
		return json_encode(['Status'=>$status,'Message' =>'Server: '.$message]);
	}


     
     public function getStorageLocations(){
     	try{
     		Log::info(__FUNCTION__.' : '.print_r(Input::get(),true));
     		$result = array();
     		$status =1;
     		$message = 'Data successfully retrieved';
     		$locationId = $this->roleAccess->getLocIdByToken(Input::get('access_token'));
     		$mfgId = $this->roleAccess->getMfgIdByToken(Input::get('access_token'));
     		$storageLocType = Loctype::where(['manufacturer_id'=>$mfgId,'location_type_name'=>'Storage Location'])->pluck('location_type_id');

     		if(!$storageLocType)
     			throw new Exception('Storage Location Type doesnt exist for the manufacturer');

            $result = Location::where(['manufacturer_id'=>$mfgId,'location_type_id'=>$storageLocType,'parent_location_id'=>$locationId])->get(['erp_code','location_name','location_id']);
            
            if(empty($result[0]))
            	throw new Exception('There are no storage locations under this location');
     	}
     	catch(Exception $e){
     		$status =0;
     		$message = $e->getMessage();
     	}
     	return json_encode(['Status'=>$status,'Message'=>$message,'Data'=>$result]);
     }



	public function confirmProductionOrder()
	{
                $startTime = $this->getTime();
		
		try{ 
			
			Log::info(__FUNCTION__.' : '.print_r(Input::get(),true));
			$accessToken = Input::get('access_token');
		   
			$productionOrderId = Input::get('production_order_id'); 
			$mfg_date = Input::get('mfg_date');
			$storage_loc_code = trim(Input::get('storage_loc_code'));
			$conf_status = trim(Input::get('confirmation_status'));

			$manufacturerId = $this->roleAccess->getMfgIdByToken($accessToken);
			$productId = DB::table('eseal_'.$manufacturerId)->where('po_number', $productionOrderId)->pluck('pid');
			$serialsArray = DB::table('eseal_'.$manufacturerId)->where(['po_number'=>$productionOrderId,'is_confirmed'=>'0','is_active'=>1])->get(['primary_id']);

			if(empty($serialsArray)){
				throw new Exception('In-valid Production Order ID or Already Confirmed');
			}
			if(empty($productId)){
				throw new Exception('In-valid Production Order Id.');
			}else{
				Log::info('Product ID:'.$productId);
                    
				$data = $this->generateXml($productionOrderId, $serialsArray,$accessToken,$productId,$mfg_date,$storage_loc_code,$conf_status);
				$data = json_decode($data);
				if($data->Status){
						$status =1;
						$message = $data->Message;																
						
				}
				else{
				throw new Exception($data->Message);
				}
			}
		    
		   
		} catch (Exception $e) {			
			$status=0;
			$message = $e->getMessage();
			
		}
                $endTime = $this->getTime();
		Log::info(__FUNCTION__.' Finishes execution in '.($endTime-$startTime));
		Log::info(['Status'=>$status, 'Message' =>'Server: '.$message.' eseal process time :'.($endTime-$startTime)]);
		return json_encode(['Status'=>$status, 'Message' =>'Server: '.$message.' total process time :'.($endTime-$startTime)]);
	}



	protected function generateXml($productionOrderId,$serialsArray,$accessToken,$product_id,$mfg_date,$storage_loc_code,$conf_status)
	{
		echo "test"; exit;
		try
		{
			//DB::beginTransaction();
			$status =0;
                        $sapProcessTime = 0;
			$mfgId = $this->roleAccess->getMfgIdByToken(Input::get('access_token'));
            if($conf_status == 0 || empty($conf_status))
            	$conf_status ='';
            else
            	$conf_status='X';
           
             foreach($serialsArray as $se){
				$serialArray[] = $se->primary_id;
			}
			Log::info($serialArray);




            if(!$mfg_date)
               $mfg_date = DB::table('eseal_'.$mfgId)->whereIn('primary_id',$serialArray)->pluck('mfg_date');
			
			$mfg_date = date("d/m/Y", strtotime($mfg_date));

			$ESEAL_KEY = $this->getRand();
			
			
            
            $BACKFLASH = 'X';
			$INSP_DATE = date('d/m/Y');
			$PROD_ORDER = $productionOrderId;
			$final = array();
			$compBatch1 = array();
			$compBatch = array();
			$arrSerial = array();

       
           /*****/	$query = DB::table('erp_integration')->where('manufacturer_id',$mfgId);
					$erp = $query->select('web_service_url','token','company_code','web_service_username','web_service_password','sap_client')->get();
                    
					 $domain = $erp[0]->web_service_url;
					 $token = $erp[0]->token;
					 $company_code = $erp[0]->company_code;
					 $sap_client = $erp[0]->sap_client;

					 
					 
					 

                     $erp = $this->roleAccess->getErpDetailsByUserId($accessToken);

								if($erp){
									$username = $erp[0]->erp_username;
									$password = $erp[0]->erp_password;
									}
									else{
										throw new Exception('There are no erp username and password');
								}


					 $data = ['TOKEN'=>$token,'PORDER'=>$PROD_ORDER];

					 $method = 'Z030_ESEAL_GET_PORDER_DETAILS_SRV';
					 $method_name = 'GET_PORDER_DETAILS';

					 $url = $domain.$method.'/'.$method_name;

					 $url = sprintf("%s?\$filter=%s", $url, urlencode($this->sapRepo->generateData($data)));
					 $url = $url.'&sap-client='.$sap_client;
					 $curl = curl_init();
					 Log::info($url);
					 curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
					 curl_setopt($curl, CURLOPT_USERPWD, "$username:$password");
					 curl_setopt($curl,CURLOPT_RETURNTRANSFER,true);
					 curl_setopt($curl, CURLOPT_URL, $url);
					 curl_setopt($curl, CURLOPT_HEADER, 0);
					 $result12 = curl_exec($curl);
					 curl_close($curl);
                     Log::info($result12);

					 $parseData1 = xml_parser_create();
					 xml_parse_into_struct($parseData1, $result12, $documentValues1, $documentIndex1);
					 xml_parser_free($parseData1);
					 $documentData = array();	

					 foreach ($documentValues1 as $data) {
					  if(isset($data['tag']) && $data['tag'] == 'D:PORDER_DATA')
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
				
				  $characteristics= array();	
		  /*****/ Log::info($xml_array);

		          if($xml_array['HEADER']['Status'] == 0)
		          	throw new Exception($xml_array['HEADER']['Message']);

                  $uom = $xml_array['DATA']['FINISHED_MATERIAL']['ITEM']['UOM'];


            $query = DB::table('products')->where('product_id',$product_id)->get(['is_serializable','inspection_enabled','uom_unit_value']);
            //$pkg_qty = $query[0]->uom_unit_value;
            $inspection_enabled = $query[0]->inspection_enabled;
            $is_serializable = $query[0]->is_serializable;

            if($is_serializable ==1)
			   $serial_only = 'X';
			else
				$serial_only = '';

			
						
			$Qty = 1;            
			$location_id = $this->roleAccess->getLocIdByToken($accessToken);
			$mfg_id = $this->roleAccess->getMfgIdByToken($accessToken);
			$result = '<?xml version="1.0" encoding="utf-8" ?><REQUEST><DATA><INPUT TOKEN="'.$token.'" ESEAL_KEY="'.$ESEAL_KEY.'" BACKFLASH="'.$BACKFLASH.'" INSP_DATE="'.$INSP_DATE.'" RSLOC="'.$storage_loc_code.'" PDATE="'.$mfg_date.'" FINAL="'.$conf_status.'" SERIAL_ONLY="'.$serial_only.'"/>';
			
			//$serialArray = explode(',', $serialsArray);
			
			
            
            

            if($uom == 'M'){

			 $newQtyVar = DB::table('eseal_'.$mfg_id.' as es')
							 ->where('es.is_confirmed',0)			                 
							 ->where(['es.po_number'=>$PROD_ORDER,'es.is_active'=>1])
							 ->sum('pkg_qty');
            }
            else{

            	$newQtyVar = DB::table('eseal_'.$mfg_id.' as es')
							//->join('eseal_'.$mfg_id.' as es1','es.primary_id','=','es1.parent_id')
            	             ->join('products as pr','pr.product_id','=','es.pid')
							 ->where('es.is_confirmed',0)			                 
							 ->where(['es.po_number'=>$PROD_ORDER,'es.is_active'=>1])
							 ->get([DB::raw('case when multiPack=0 then count(primary_id) else sum(pkg_qty) end as qty')]);

				$newQtyVar = $newQtyVar[0]->qty;

            }
			$compBath = DB::table('eseal_'.$mfg_id.' as es')
							   ->join('products as pr','pr.product_id','=','es.pid')
							   ->join('attribute_mapping as am','am.attribute_map_id','=','es.attribute_map_id')
							   ->where('pr.product_type_id','8004')
							   ->where('am.attribute_name',$this->valuation)
							   ->whereIn('parent_id',$serialArray)
							   ->groupBy('es.batch_no')
							   ->select(['pr.material_code','es.batch_no',DB::raw('count(distinct(primary_id)) as cnt'),'am.value','is_batch_enabled'])
							   ->get();
							   if(!empty($compBath)){
						   foreach($compBath as $batch){
							$compBatch1[] = ['material_code'=>$batch->material_code,'batch_no'=>$batch->batch_no,'qty'=>$batch->cnt,'valuation'=>$batch->value,'batch_enabled'=>$batch->is_batch_enabled];
						   }
						}
						 
			
			Log::info('Components 1:-');
			Log::info($compBatch1);
		
			$attributeMaps = DB::table('bind_history')->whereIn('eseal_id',$serialArray)->distinct()->get(['attribute_map_id']);
		 
		 
			foreach($attributeMaps as $map){
				$map1[] = $map->attribute_map_id;
			}
			Log::info($map1);

			/*$componentIds = DB::table('product_components')
							   ->join('products','products.product_id','=','product_components.component_id')
							   ->where('products.is_batch_enabled',1)
							   ->where('product_components.product_id',$product_id)
							   ->get(['name']);
		   
			foreach($componentIds as $id){
				$comp[] = $id->name;
			}
			Log::info('Component names:');
			Log::info($comp);*/
	 
			$info = DB::table('attribute_mapping as am')
						//->join('products as pr','pr.name','=','am.attribute_name')
						->whereIn('am.attribute_map_id',$map1)
						->where('am.attribute_name','Stator')
						->distinct()
						->get(['am.attribute_name','am.value']);

			Log::info('Stator IDS');
			Log::info($info);
if(!empty($info)){
			foreach($info as $in){
				$valmaps = DB::table('bind_history')->where('eseal_id',$in->value)->lists('attribute_map_id');
				Log::info($valmaps);
				$valtype = DB::table('attribute_mapping')->whereIn('attribute_map_id',$valmaps)->where('attribute_name',$this->valuation)->pluck('value');
				$batch_no = DB::table('eseal_'.$mfg_id)->where('primary_id',$in->value)->pluck('batch_no');
				$pid = DB::table('eseal_'.$mfg_id)->where('primary_id',$in->value)->pluck('pid');
				$material_code = DB::table('products')->where('product_id',$pid)->pluck('material_code');
				$is_batch_enabled = DB::table('products')->where('product_id',$pid)->pluck('is_batch_enabled');
				$final[] = ['material_code'=>$material_code,'batch_no'=>$batch_no,'qty'=>$newQtyVar,'valuation'=>$valtype,'batch_enabled'=>$is_batch_enabled];	
			}	
		}
			Log::info('Components 2:-');
			Log::info($final);		
			
			$compBatch = array_merge($compBatch1,$final);
			Log::info('Components 3:-');
			Log::info($compBatch);
			
			

			Log::info('Location ID:'.$location_id);
			$location_name = Location::where('location_id',$location_id)->pluck('location_name');
			$mfg_id = $this->roleAccess->getMfgIdByToken($accessToken);
			$query = DB::table('eseal_'.$mfg_id)->where('po_number',$PROD_ORDER);
							$dval = $query->max('is_confirmed');
							$pqtno = $dval +1;
					
			

                 if($inspection_enabled == 1){
				 foreach($xml_array['DATA']['CHARACTERISTICS']['ITEM'] as $char){
					$characteristics[$char['MASTER_INSPECTION']] = $char['UPPER_SPECIFICATION_LIMIT'];
				 } 
				 }  
				 Log::info($characteristics);

				 $result_query = DB::table('eseal_'.$mfg_id.' as e')
										->select('e.po_number','am.attribute_name', 'am.value','e.primary_id','e.inspection_result')
										->join('bind_history as bh','e.primary_id','=','bh.eseal_id')
										->join('attribute_mapping as am','bh.attribute_map_id','=','am.attribute_map_id')
										->join('attributes as a','a.attribute_id','=','am.attribute_id')
										->where(['a.attribute_type'=>5,'e.po_number' =>(string)$PROD_ORDER,'e.inspection_result'=>1,'e.is_confirmed'=>0,'e.level_id'=>0])
										->distinct()
										->get();
		
			Log::info($result_query);
			$temp = array();	
				
			foreach ($result_query as $res_query) {
				
				if(empty($temp)){			
					$temp []= $res_query->primary_id;
				}
				if(!in_array($res_query->primary_id,$temp)){
					$temp []= $res_query->primary_id;	
					$Qty++;
				}
			}

			
			$temp1 = $temp;
			Log::info($temp1);
			unset($temp);
			$ins = DB::table('products')->where('product_id',$product_id)->first(['is_serializable','inspection_enabled']);
		
			//$newQtyVar = DB::table('eseal_'.$mfg_id)->where('po_number',$PROD_ORDER)->count();//sum('pkg_qty');
			//sum('pkg_qty');
			
			$result = $result . '<PROD_CONF>';
			
			$result .='<ITEM PROD_ORDER="'.$PROD_ORDER.'" QTY="'.$newQtyVar.'" INSPECTION="'.$ins->inspection_enabled.'" SERIALIZATION="'.$ins->is_serializable.'"/></PROD_CONF>';
			
			$result .='<BATCH_CHAR>
						  <ITEM MANUFAC_DATE="'.$mfg_date.'" LINE_NO="1" MANUFAC_LOC="'.$location_name.'" /> 
					   </BATCH_CHAR>';
			
			
			$result .='<INSP_DATA>';
				
			Log::info($result);
			
			if($ins->inspection_enabled==1) {
				Log::info(trim($xml_array['DATA']['GENRAL_DATA']['ITEM']['INSPECTION_LOT_NO']));
				$INSPECTIONLOT = trim($xml_array['DATA']['GENRAL_DATA']['ITEM']['INSPECTION_LOT_NO']); 
				
				$OPERATION = '0010';
				$PARTIAL_QUANTITY_NO = $pqtno;
				$APP_REJ = 'A'; 
				$YES_NO = 'X';   
				
				foreach ($result_query as $res_query) {
				if(isset($characteristics[strtoupper($res_query->attribute_name)])) {
						$result .=  '<ITEM INSPECTIONLOT="'.$INSPECTIONLOT.'" OPERATION="'.$OPERATION.'" PARTIAL_QUANTITY_NO="'.
									$PARTIAL_QUANTITY_NO.'" CHARACTERISTIC="'.strtoupper($res_query->attribute_name).'" RES_VALUE="'.$characteristics[strtoupper($res_query->attribute_name)].'" APP_REJ="'.
									$APP_REJ.'" SERIAL_NO="'.$res_query->primary_id.'" YES_NO="'.$YES_NO.'"/>';
						 
						}
				else{
					throw new Exception(strtoupper($res_query->attribute_name). ' characteristic must be removed');
				}					
					}				
			}
					
			  
			$result = $result . '</INSP_DATA>';
			
			if($dval==0)
			{
			$result = $result . '<PROD_SERIAL>';
			
			if($ins->is_serializable){
			foreach($serialArray  as $te)
			{
				$result = $result . '<ITEM SERIAL_NO="'.$te.'"/>';
			}
			}
				$result = $result . '</PROD_SERIAL>';
			}
			else{
				$result .='<PROD_SERIAL>';
				$result .= '</PROD_SERIAL>';
			}
			
			
			$result .= '<COMP_BATCH>';
			if(!empty($compBatch)){
			  foreach($compBatch as $batch){ 
				if($batch['batch_enabled']){
					$result .='<ITEM MATERIAL_CODE="'.$batch['material_code'].'" BATCH_NO="'.$batch['batch_no'].'" COMPONENT_QTY="'.$batch['qty'].'" VALUATION_TYPE="'.$batch['valuation'].'"/>'; 
				}
				else{
					$result .='<ITEM MATERIAL_CODE="'.$batch['material_code'].'" BATCH_NO="" COMPONENT_QTY="'.$batch['qty'].'" VALUATION_TYPE="'.$batch['valuation'].'"/>'; 	
				}
			}
			}
			/*$result .='<ITEM MATERIAL_CODE="13120240104" BATCH_NO="BTX001" COMPONENT_QTY="10000" VALUATION_TYPE="BODO"/>';
			$result .='<ITEM MATERIAL_CODE="12103040124" BATCH_NO="" COMPONENT_QTY="10000" VALUATION_TYPE="SFSC"/>';  */
			$result .='</COMP_BATCH>';
			$result .= '</DATA></REQUEST>';
			
		   $method = 'Z0035_CONFIRM_PRODUCTION_ORDER_SRV';
		   $method_name = 'PROD';
		   $url = $domain.$method.'/'.$method_name.'?&sap-client='.$sap_client;

			$method='POST';
			$xml = '<?xml version="1.0" encoding="utf-8"?><entry xml:base="'.$domain.'Z0035_CONFIRM_PRODUCTION_ORDER_SRV/" xmlns="http://www.w3.org/2005/Atom" xmlns:m="http://schemas.microsoft.com/ado/2007/08/dataservices/metadata" xmlns:d="http://schemas.microsoft.com/ado/2007/08/dataservices"><id>'.$domain.'Z0035_CONFIRM_PRODUCTION_ORDER_SRV/PROD(\'123\')</id><title type="text">PROD(\'123\')</title><updated>2015-08-10T09:17:17Z</updated><category term="Z0035_CONFIRM_PRODUCTION_ORDER_SRV.PROD" scheme="http://schemas.microsoft.com/ado/2007/08/dataservices/scheme"/><link href="PROD(\'123\')" rel="self" title="PROD"/><content type="application/xml"><m:properties><d:ESEAL_INPUT>"<![CDATA['.$result.']]>"</d:ESEAL_INPUT><d:GR_DOCUMENT_NO/><d:YEAR/><d:ESEAL_OUTPUT/></m:properties></content></entry>';
			Log::info('XML passed:-');
			
			Log::info($result);
			//die;
			
			Log::info('Is_confirmed:'.($pqtno-1));
			Log::info('PQT no:'.$pqtno);
			Log::info('updated IS_CONFIRMED:'.$pqtno);
			Log::info('Updated inspect result:'.($pqtno+1));
			Log::info('SAP start:-');
			

                        $sapStartTime = $this->getTime();
			$response = $this->sapRepo->request($username,$password,$url, $method,null,'xml',2,null,$xml);
                        $sapEndTime = $this->getTime();

                        $sapProcessTime = ($sapEndTime - $sapStartTime);

			DB::beginTransaction();
			Log::info($response);
				$parseData1 = xml_parser_create();
				xml_parse_into_struct($parseData1, $response, $documentValues1, $documentIndex1);
				xml_parser_free($parseData1);
				$documentData = array();
				Log::info('SAP end:-');
				foreach ($documentValues1 as $data) 
				{
					if(isset($data['tag']) && $data['tag'] == 'D:ESEAL_OUTPUT')
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
			Log::info($xml_array);

			if($xml_array['HEADER']['Status'] == 1){

			        foreach ($documentValues1 as $data) {
						if(isset($data['tag']) && $data['tag'] == 'D:BATCH')
						{
							$batchNo = $data['value'];
						}
					}  

					foreach ($documentValues1 as $data) {
						if(isset($data['tag']) && $data['tag'] == 'D:GR_DOCUMENT_NO')
						{
							$documentNo = $data['value'];
						}
					}  



					if(empty($batchNo))
						throw new Exception('Batch not created in ERP');
                    
                    if(empty($documentNo))
						throw new Exception('GRN not created in ERP');
					if(empty($storage_loc_code))
                       $storage_loc_code= DB::table($this->locationsTable)
                                            ->where(['parent_location_id'=>$location_id,'storage_location_type_code'=>25001])
                                            ->pluck('erp_code');

					DB::table('eseal_'.$mfg_id)
				  ->where(array('po_number'=>$PROD_ORDER,'inspection_result'=>1,'is_active'=>1))
				  ->whereIn('primary_id',$serialArray)
				  ->update(array('is_confirmed'=>$pqtno,'inspection_result'=>$pqtno+1,'storage_location'=>$storage_loc_code));	

					DB::table('eseal_'.$mfg_id)->whereIn('primary_id',$serialArray)->update(['batch_no'=>$batchNo,'reference_value'=>$documentNo]);

					if($conf_status == 'X')
						DB::table('erp_objects')->where('object_id',$PROD_ORDER)->update(['process_status'=>1]);

			   $status =1;
			   $message = 'Confirm production order successfull and batch updated';
			   DB::commit();
				   } 
				   else{
					 
					 /*$sub = 'PO CONFIRMATION FAILURE';
					 $status = Mail::send('order_mail',['body' => $body], 
						function($message) use ($sub, $email) {
							$message->to($email)->subject($sub);
						});*/

					 throw new Exception($xml_array['HEADER']['Message']);
				   }                                    

			

		} catch (Exception $e) {
			DB::rollback();
			$status =0;
			$message = $e->getMessage();
		}
		Log::info(__FUNCTION__.' finishes execution');
		Log::info(['Status'=>$status,'Message' =>'Server: '.$message.' sap process time :'.$sapProcessTime]);
		return json_encode(['Status'=>$status,'Message' =>'Server: '.$message.' sap process time :'.$sapProcessTime]);
	}



	public function getErpLookup()
	{
		Log::info(__FUNCTION__.' : '.print_r(Input::get(),true));
		$access_token = Input::get('access_token');
		$locationId =  $this->roleAccess->getLocIdByToken(Input::get('access_token'));
		$mfgId = $this->roleAccess->getMfgIdByToken(Input::get('access_token'));
		$type = Input::get('type');
		//$plant_id = Input::get('plant_id');
		$plant_id = Location::where('location_id',$locationId)->pluck('erp_code');
		try 
		{
			if(empty($type))
			{
				$results = DB::table('users_token')
				->select(['erp_objects.type', 'erp_objects.object_id', 'erp_objects.action', 'erp_objects.plant_id',DB::raw('cast(erp_objects.location_id as char) as location_id'),DB::raw('cast(erp_objects.process_status as char) as process_status') ,'response'])
				->join('users','users_token.user_id','=','users.user_id')
				->join('erp_objects','users.customer_id','=','erp_objects.manufacturer_id')
				->where(['users_token.access_token'=>$access_token,'erp_objects.is_active'=>1,'erp_objects.process_status'=>0,'erp_objects.manufacturer_id'=>$mfgId]);
				if($plant_id)
				$results = $results->where('plant_id',$plant_id);

				$results=$results->whereNotNull('response')
				->groupBy('erp_objects.object_id')
				->get();
			}
			else
			{
				$results = DB::table('users_token')
				->select(['erp_objects.type','erp_objects.object_id', 'erp_objects.action', 'erp_objects.plant_id',DB::raw('cast(erp_objects.location_id as char) as location_id'),DB::raw('cast(erp_objects.process_status as char) as process_status'),'response'])
				->join('users','users_token.user_id','=','users.user_id')
				->join('erp_objects','users.customer_id','=','erp_objects.manufacturer_id')
				->where(['users_token.access_token'=>$access_token,'erp_objects.type'=>$type,'erp_objects.is_active'=>1,'erp_objects.process_status'=>0,'erp_objects.manufacturer_id'=>$mfgId]);
				if($plant_id)
				$results = $results->where('plant_id',$plant_id);
				$results=$results->whereNotNull('response')
				->groupBy('erp_objects.object_id')
				->get();
				/*$queries=DB::getQueryLog();
				print_r(end($queries));exit;*/
			}
			if(!empty($results))
			{
				$status =1;
				$message ='Data successfully retrieved.';
			}
			else
			{
				throw new Exception('ERP objects not found for location.');
			}
		}
		catch (ErrorException $ex) 
		{
			$message = $ex->getMessage();
		}    
		Log::info($results);
		return json_encode(['Status'=>$status,'Message'=>'Server: '.$message,'Data'=>$results]);
	}
	public function sapconfirmProductionOrder()
	{
		$access_token = Input::get('access_token');
		$prod_order_id = Input::get('production_order_id');
		$action = Input::get('module_id');

		//Calling the BindGrnData API from Eseal
		$request = Request::create('scoapi/confirmProductionOrder', 'POST', array('module_id'=> Input::get('module_id'),'access_token'=>Input::get('access_token'),'production_order_id'=>$prod_order_id));
		 $originalInput = Request::input();//backup original input
		 Request::replace($request->input());
		 //Log::info($request->input());
		 $res2 = Route::dispatch($request)->getContent();
		 $eseal_input = json_decode($res2);
		 //return $eseal_input;exit;
		//$location_id = Input::get('location_id');
		$locationId = $this->roleAccess->getLocIdByToken(Input::get('access_token'));
		$mfg_id = $this->roleAccess->getMfgIdByToken(Input::get('access_token'));
		
				$query = DB::table('erp_integration')->where('manufacturer_id',$mfg_id);
				$erp = $query->select('web_service_url','token','company_code','web_service_username','web_service_password')->get();

				$domain = $erp[0]->web_service_url;
				$token = $erp[0]->token;
				$company_code = $erp[0]->company_code;
				$username = $erp[0]->web_service_username;
				$password = $erp[0]->web_service_password;
			   /* echo $domain.'----';
				echo $token.'----';
				echo $company_code.'----';
				echo $username.'----';
				echo $password.'----';*/
				//echo $eseal_input.'----';die;

				$data = ['ESEAL_INPUT'=>$eseal_input];
				$method = 'Z029_ESEAL_GET_GRN_DATA_SRV';
				$method_name = 'PROD';
				$url = $domain.$method.'/'.$method_name;
				$url = sprintf("%s?\$filter=%s", $url, urlencode($this->sapRepo->generateData($data)));
				echo urlencode($this->sapRepo->generateData($data));
				 echo $url;
				$curl = curl_init();
				curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
				curl_setopt($curl, CURLOPT_USERPWD, "$username:$password");
				//curl_setopt($curl,CURLOPT_USERAGENT,$agent);
				curl_setopt($curl,CURLOPT_RETURNTRANSFER,true);
				curl_setopt($curl, CURLOPT_URL, $url);
				curl_setopt($curl, CURLOPT_HEADER, 0);
				$actualResult = curl_exec($curl);
				curl_close($curl);
				//return $actualResult;exit;
				$parseData1 = xml_parser_create();
				xml_parse_into_struct($parseData1, $actualResult, $documentValues1, $documentIndex1);
				xml_parser_free($parseData1);
				$documentData = array();

				foreach ($documentValues1 as $data) 
				{
					if(isset($data['tag']) && $data['tag'] == 'D:ESEAL_OUTPUT')
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
				/*echo "<pre/>";print_r($xml_array);exit;     */
				Log::info($xml_array);
	
	}
	public function getToSapCall()
	{
	
		$access_toconfirmProken = Input::get('access_token');
		$eseal_input = Input::get('eseal_input');
		$action = Input::get('module_id');
		//echo "<pre/>";print_r($prod_order_id);exit;

		$locationId = $this->roleAccess->getLocIdByToken(Input::get('access_token'));
		$mfg_id = $this->roleAccess->getMfgIdByToken(Input::get('access_token'));
		
				$query = DB::table('erp_integration')->where('manufacturer_id',$mfg_id);
				$erp = $query->select('web_service_url','token','company_code','web_service_username','web_service_password')->get();

				$domain = $erp[0]->web_service_url;
				$token = $erp[0]->token;
				$company_code = $erp[0]->company_code;
				$username = $erp[0]->web_service_username;
				$password = $erp[0]->web_service_password;

				$data = rawurlencode(urlencode('ESEAL_INPUT eq '."'".$eseal_input))."'".'&sap-client=110'; 
				$method = 'Z0035_CONFIRM_PRODUCTION_ORDER_SRV';
				$method_name = 'PROD';
				$url = $domain.$method.'/'.$method_name;
				//$url='http://14.141.81.243:8000/sap/opu/odata/sap/Z0035_CONFIRM_PRODUCTION_ORDER_SRV/PROD/?$filter=';
				//$url = $url.'/?$filter='.rawurlencode('ESEAL_INPUT eq ').urlencode("'".($eseal_input)."'").'&sap-client=110';
				
				 $url .= '/?$filter='.rawurlencode('ESEAL_INPUT eq ').urlencode("'".($eseal_input)."'").'&sap-client=110';
				$curl = curl_init();

				curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
				curl_setopt($curl, CURLOPT_USERPWD, "$username:$password");
				curl_setopt($curl, CURLOPT_URL, $url);
				
				curl_setopt($curl,CURLOPT_RETURNTRANSFER,true);
				
				curl_setopt($curl, CURLOPT_HEADER, 0);
			
				$result12 = curl_exec($curl);
				curl_close($curl);
				echo "<pre>"; print_r($result12); die;
				$parseData1 = xml_parser_create();
				xml_parse_into_struct($parseData1, $result12, $documentValues1, $documentIndex1);
				xml_parser_free($parseData1);
				$documentData = array();

				foreach ($documentValues1 as $data) 
				{
					if(isset($data['tag']) && $data['tag'] == 'D:ESEAL_OUTPUT')
					{
						$documentData = $data['value'];
					}
				}
				
				$deXml = simplexml_load_string($documentData);
				$deJson = json_encode($deXml);
				$xml_array = json_decode($deJson,TRUE); 
				echo "<pre/>";print_r($xml_array);exit;     
				Log::info($xml_array);

	}

	public function getProductionOrder()
	{
		$postData = Input::get();
		Log::info(__FUNCTION__.' : '.print_r(Input::get(),true));
		try{

			$mfg_id = $this->roleAccess->getMfgIdByToken($postData['access_token']); 

			$erp = DB::table('erp_integration')->where('manufacturer_id',$mfg_id)
			->select('web_service_url','token','company_code','web_service_username','web_service_password','sap_client')
			->get();

			 $domain = $erp[0]->web_service_url;
			 $token = $erp[0]->token;
			 $company_code = $erp[0]->company_code;
			 $username = $erp[0]->web_service_username;
			 $password = $erp[0]->web_service_password;
			 $sap_client = $erp[0]->sap_client;
			 $date = DB::table('erp_objects')->where(['manufacturer_id'=>$mfg_id,'type'=>'PORDER'])->max('created_on');
						$date = date_create($date);
						$date = date_format($date,'Y-m-d');
						//return $date;
						$fromDate = "datetime'".$date.'T00:00:00'."'";
						Log::info($fromDate);
						$toDate = "datetime'".date('Y-m-d').'T00:00:00'."'";
			 
			 $data = ['TOKEN'=>$token,'FROM_DATE'=>$fromDate,'TO_DATE'=>$toDate];

			 $method = 'Z0041_041_GET_ALL_PRODUCTION_SRV';
			 $method_name = 'PROD';

			 $url = $domain.$method.'/'.$method_name;

			 $url = sprintf("%s?\$filter=%s", $url, rawurlencode($this->sapRepo->generateData($data)));
			 
			 $url = $url.'&sap-client='.$sap_client;
			 //echo $url; exit;
			 $curl = curl_init();

			 curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
			 curl_setopt($curl, CURLOPT_USERPWD, "$username:$password");
		   //curl_setopt($curl,CURLOPT_USERAGENT,$agent);
			 curl_setopt($curl,CURLOPT_RETURNTRANSFER,true);
			 curl_setopt($curl, CURLOPT_URL, $url);
			 curl_setopt($curl, CURLOPT_HEADER, 0);
			 $result12 = curl_exec($curl);
			 curl_close($curl);
			 Log::info($result12);
			 //echo "<pre>"; print_r($result12); die;
			 $parseData1 = xml_parser_create();
			 xml_parse_into_struct($parseData1, $result12, $documentValues1, $documentIndex1);
			 xml_parser_free($parseData1);
			 $documentData = array();	
			 //echo "<pre>"; print_r($documentValues1); die;
			 foreach ($documentValues1 as $data) {
			  if(isset($data['tag']) && $data['tag'] == 'D:ESEAL_OUTPUT')
			  {
			   $documentData = $data['value'];
			  }
			 }
			 if(empty($documentData)){
				throw new Exception('Error from ERP call');
			}

			//echo $documentData; die;
			$deXml = simplexml_load_string($documentData);
			$deJson = json_encode($deXml);
			$xml_array = json_decode($deJson,TRUE);
			//echo "<pre>"; print_r($xml_array); die;
			if($xml_array['HEADER']['Status']==1)
			{
				
				foreach($xml_array['DATA']['ITEM'] as $item){ //print_r($item); die;
					$request = Request::create('scoapi/notifyEseal', 'POST', array('plant_id'=> $item['PLANT'],'type'=>'PORDER','object_id'=>$item['ORDER_NUMBER'],'action'=>'add','access_token'=>$postData['access_token'],'module_id'=>$postData['module_id']));	
					$originalInput = Request::input();//backup original input
					Request::replace($request->input());
					Log::info($request->input());
					$res2 = Route::dispatch($request)->getContent();
					$result = json_decode($res2);			

				}	
				$status = 1;
				$message = "Product Order Created Successfully";
			}
			else{
				throw new Exception($xml_array['HEADER']['Message']);
			}

		}catch (Exception $ex) {
			$status = 0;
			$message = $ex->getMessage();
		}
		Log::info(array('Status'=>$status,'Message'=>'Server: '.$message));
		return json_encode(array('Status'=>$status,'Message'=>'Server: '.$message));
	}




	public function getDevlivery(){

		$postData = Input::get();
		Log::info(__FUNCTION__.' : '.print_r(Input::get(),true));
		try{

			$mfg_id = $this->roleAccess->getMfgIdByToken($postData['access_token']); 
			$location_id = $this->roleAccess->getLocIdByToken($postData['access_token']); 
			$erp = DB::table('erp_integration')->where('manufacturer_id',$mfg_id)
			->select('web_service_url','token','company_code','web_service_username','web_service_password','sap_client')
			->get();

			 $domain = $erp[0]->web_service_url;
			 $token = $erp[0]->token;
			 $company_code = $erp[0]->company_code;
			 $username = $erp[0]->web_service_username;
			 $password = $erp[0]->web_service_password;
			 $sap_client = $erp[0]->sap_client;
			 /*$from_Date = gmdate("Y-m-d\TH:i:s",mktime(0,0,0,date('m',strtotime($postData['from_date'])),date('d',strtotime($postData['from_date'])),date('Y',strtotime($postData['from_date']))));
			 $to_Date = gmdate("Y-m-d\TH:i:s",mktime(0,0,0,date('m',strtotime($postData['to_date'])),date('d',strtotime($postData['to_date'])),date('Y',strtotime($postData['to_date']))));*/
			 /*$from_Date = gmdate("Y-m-d\TH:i:s",mktime(0,0,0,date('m'),date('d')-1,date('Y')));
			 $to_Date = gmdate("Y-m-d\TH:i:s",mktime(0,0,0,date('m'),date('d'),date('Y')));*/

			$date = DB::table('erp_objects')->where(['manufacturer_id'=>$mfg_id,'type'=>'DELIVERYDETAILS'])->max('created_on');
			$date = date_create($date);
			$date = date_format($date,'y-m-d');
			//return $date;
			$fromDate = "datetime'".$date.'T00:00:00'."'";
			Log::info($fromDate);
			$toDate = "datetime'".date('Y-m-d').'T00:00:00'."'";
			 



			 $data = ['TOKEN'=>$token,'FROM_DATE'=>$fromDate,'TO_DATE'=>$toDate];

			 $method = 'Z0043_ESEAL_GET_OPEN_DELIVERY_SRV';
			 $method_name = 'DELIVERY';

			 $url = $domain.$method.'/'.$method_name;

			 $url = sprintf("%s?\$filter=%s", $url, rawurlencode($this->sapRepo->generateData($data)));
			 
			 $url = trim($url).'&sap-client='.$sap_client;
			 //echo $url; exit;
			 $curl = curl_init();

			 curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
			 curl_setopt($curl, CURLOPT_USERPWD, "$username:$password");
		   //curl_setopt($curl,CURLOPT_USERAGENT,$agent);
			 curl_setopt($curl,CURLOPT_RETURNTRANSFER,true);
			 curl_setopt($curl, CURLOPT_URL, $url);
			 curl_setopt($curl, CURLOPT_HEADER, 0);
			 $result12 = curl_exec($curl);
			 curl_close($curl);
			 Log::info($result12);
			 //echo "<pre>"; print_r($result12); die;
			 $parseData1 = xml_parser_create();
			 xml_parse_into_struct($parseData1, $result12, $documentValues1, $documentIndex1);
			 xml_parser_free($parseData1);
			 //$documentData = array();	
			 //echo "<pre>"; print_r($documentValues1); die;
			 foreach ($documentValues1 as $data) {
			  if(isset($data['tag']) && $data['tag'] == 'D:ESEAL_OUTPUT')
			  {
			   $documentData = $data['value'];
			  }
			 }
			 if(empty($documentData)){
				throw new Exception('Error from ERP call');
			}

			//echo $documentData; die;
			$deXml = simplexml_load_string($documentData);
			$deJson = json_encode($deXml);
			$xml_array = json_decode($deJson,TRUE);
			Log::info($xml_array);
			//echo "<pre>"; print_r($xml_array); die;
			if($xml_array['HEADER']['Status']==1)
			{
				
				foreach($xml_array['DATA']['ITEM'] as $item){ //print_r($item); die;
					$request = Request::create('scoapi/notifyEseal', 'POST', array('plant_id'=> $item['PLANT'],'type'=>'DELIVERYDETAILS','object_id'=>$item['DOCUMENT_NO'],'action'=>'add','access_token'=>$postData['access_token'],'module_id'=>$postData['module_id']));	
					$originalInput = Request::input();//backup original input
					Request::replace($request->input());
					Log::info($request->input());
					$res2 = Route::dispatch($request)->getContent();
					$result = json_decode($res2);			

				}	
				$status = 1;
				$message = "Develivery Details Created Successfully";
			}
			else{
				throw new Exception($xml_array['HEADER']['Message']);
			}

		}catch (ErrorException $ex) {
			$status = 0;
			$message = $ex->getMessage();
		}
		return json_encode(array('Status'=>$status,'Message'=>'Server: '.$message));

	}
		
	public function getAllGRN()
	{
		try
		{            
			$momentType = Input::get('MOVEMENT_TYPE');
			$accessToken = Input::get('access_token');
			$module_id = Input::get('module_id');
			Log::info(__FUNCTION__.' : '.print_r(Input::get(),true));
			$location_id = $this->roleAccess->getLocIdByToken($accessToken);
			$mfg_id = $this->roleAccess->getMfgIdByToken($accessToken);
						$query = DB::table('erp_integration')->where('manufacturer_id', $mfg_id);
			$erp = $query->select('web_service_url', 'token', 'company_code', 'web_service_username', 'web_service_password','sap_client')->get();
						
						$created_on = DB::table('erp_objects')->where('manufacturer_id', $mfg_id)->orderBy('created_on', 'DESC')->pluck('created_on');
						
			$domain = $erp[0]->web_service_url;
			$token = $erp[0]->token;
			$company_code = $erp[0]->company_code;
			$username = $erp[0]->web_service_username;
			$password = $erp[0]->web_service_password;
			$sap_client = $erp[0]->sap_client;
			Log::info($username.'****'.$password);
						/*if(empty($created_on))
						{
							$fromDate = "datetime'".str_replace(' ', 'T', $erp[0]->default_start_date)."'";
						}else{
							$fromDate = "datetime'".str_replace(' ', 'T', $created_on)."'";
						}
						if(empty($fromDate))
						{
							$date = strtotime(date('Y-m-d').' -1 year');
							$fromDate = "datetime'".date('Y-m-d', $date).'T00:00:00'."'";
						}*/
						/* code for demo */
						//$date = strtotime(date('Y-m-d').' -1 day');
						$date = DB::table('erp_objects')->where(['manufacturer_id'=>$mfg_id,'type'=>'GRN'])->max('created_on');
						$date = date_create($date);
						$date = date_format($date,'Y-m-d');
						Log::info($date);
						$fromDate = "datetime'".$date.'T00:00:00'."'";
						Log::info($fromDate);
						$toDate = "datetime'".date('Y-m-d').'T00:00:00'."'";
			
			$data = ['TOKEN' => $token, 'FROM_DATE' => $fromDate, 'TO_DATE' => $toDate, 'MOVEMENT_TYPE' => $momentType];
						$method = 'Z0042_ESEAL_GET_ALL_GRN_NO_SRV';
			Log::info($data);
			$method_name = 'GET_GRN/';
			$url = $domain . $method . '/' . $method_name;
			$url = sprintf("%s?\$filter=%s", $url, urlencode($this->sapRepo->generateData($data)));
			$url = $url.'&sap-client='.$sap_client;
						
			$curl = curl_init();
			curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
			curl_setopt($curl, CURLOPT_USERPWD, "$username:$password");
			//curl_setopt($curl,CURLOPT_USERAGENT,$agent);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

			curl_setopt($curl, CURLOPT_URL, $url);
			curl_setopt($curl, CURLOPT_HEADER, 0);
			$result = curl_exec($curl);

			
			curl_close($curl);
						//echo "<Pre/>";print_R($result);die;
			$parseData1 = xml_parser_create();
			xml_parse_into_struct($parseData1, $result, $documentValues1, $documentIndex1);
			xml_parser_free($parseData1);
			$documentData = array();
			
			foreach ($documentValues1 as $data)
			{
				if (isset($data['tag']) && $data['tag'] == 'D:ESEAL_OUTPUT')
				{
					$documentData = $data['value'];
				}
			}
			if(empty($documentData)){
				throw new Exception('Error from ERP call');
			}
//echo "<Pre>";print_R($documentData);die;
			$deXml = simplexml_load_string($documentData);
			$deJson = json_encode($deXml);
			$xml_array = json_decode($deJson, TRUE);
			Log::info($xml_array);
			//Log::info('$SAP array reponse:'.$xml_array);
						//echo "<Pre>";print_R($xml_array);die;
			$status = isset($xml_array['HEADER']['Status']) ? $xml_array['HEADER']['Status'] : 0;
			if ($status == 1)
			{
				$responseData = isset($xml_array['DATA']) ? $xml_array['DATA'] : array();
				if (!empty($responseData))
				{
					$itemCollection = array();
					foreach ($responseData as $respData)
					{                                            
						if (!empty($respData))
						{
														$itemCollection = isset($respData['ITEM']) ? $respData['ITEM'] : array();
							if (!empty($itemCollection))
							{
								foreach ($itemCollection as $item)
								{
																		$GRN_NO = isset($item['GRN_NO']) ? $item['GRN_NO'] : 0;
									$plantId = isset($item['PLANT_ID']) ? $item['PLANT_ID'] : '0';
																	if ($GRN_NO != 0)
									{
										$postData = ['access_token' => $accessToken, 'object_id' => $GRN_NO, 'plant_id' => $plantId, 'type' => 'PO_GRN', 'action' => 'add','module_id'=>$module_id];
										$request = Request::create('scoapi/notifyEseal', 'POST', $postData);

										Request::replace($request->input());
										Log::info($request->input());
										$response = Route::dispatch($request)->getContent(); //invoke API
																				$response = json_decode($response);                                                                                
									}
								}
								return json_encode(['Status' => '1', 'Message' => 'Done importing GRN']);
							} else
							{
								return json_encode(['Status' => '0', 'Message' => 'No items found in response']);
							}
						}
					}
				} else
				{
					return json_encode(['Status' => '0', 'Message' => 'Empty Data ']);
				}
			} else
			{
				return json_encode(['Status' => '0', 'Message' => 'Wrong status ' . $status]);
			}
		} catch (ErrorException $ex)
		{
			return json_encode(['Status' => '0', 'Message' => $ex->getMessage()]);
		}
	}
	public function deactiveObjectStatus()
	{
		//$plantId = Input::get('plant_id');
		//$objectType = Input::get('type');
		$objectId = Input::get('object_id');
		//$action = Input::get('action');
		//$status = Input::get('status');
		//$location_id = Input::get('location_id');
		$locationId = $this->roleAccess->getLocIdByToken(Input::get('access_token'));
		$mfg_id = $this->roleAccess->getMfgIdByToken(Input::get('access_token'));
		try
		{
			$query = DB::table('erp_objects')->where(array('manufacturer_id' => $mfg_id, 'object_id' => $objectId, 'location_id' => $locationId));
			$erpStatus = $query->select('is_active')->get(); 			
			//echo "<pre/>";print_r($erpStatus[0]->is_active);exit;           

			if($erpStatus[0]->is_active==1)
			{
				DB::table('erp_objects')->where(array('manufacturer_id'=>$mfg_id,'location_id'=>$locationId,'object_id'=>$objectId))->update(array('is_active'=>0));
				$message = "Successfully updated the status.";
			}
			else
			{
				$message = "Already status is in-active.";
			}
		}
		catch(Exception $e)
		{
				Log::info($e->getMessage());
				$message = $e->getMessage();
		}
		return json_encode(['Status'=>1,'Message'=>$message]);
	}


	public function getErpObjectResponse1()
	{
		$status =1;
		$objectType = Input::get('type');
		$objectId = Input::get('object_id');
		$action = 'add';
		$qty =0;
		$locationId = $this->roleAccess->getLocIdByToken(Input::get('access_token'));
		$mfg_id = $this->roleAccess->getMfgIdByToken(Input::get('access_token'));
		$plantId = DB::table('locations')->where('location_id',$locationId)->pluck('erp_code');

	   Log::info(__FUNCTION__.' : '.print_r(Input::get(),true));
	   
		$query = DB::table('erp_objects')->where(array('manufacturer_id'=>$mfg_id,'plant_id'=>$plantId,'object_id'=>$objectId,'type'=>$objectType,'action'=>$action));

		$query1 = DB::table('erp_objects')->where(array('manufacturer_id'=>$mfg_id,'object_id'=>$objectId,'type'=>$objectType,'action'=>$action));
		
		$count = $query->count();
		$count1 = $query1->count();

		if($count1 !=0 && $count ==0)
			throw new Exception('This '.$objectType. ' is not for this location');

	    if($count ==0 && $count1 ==0)
	    	throw new Exception(''.$objectType.' : '.$objectId.' is not notified to eSeal');

	    $erpResponse = $query->pluck('response');
		
		$data = "";
		
		try
		{	   		
		//	if(is_null($erpResponse) || $erpResponse=='')
		//	{
				//Calling the BindGrnData API from Eseal
				$request = Request::create('scoapi/notifyEseal', 'POST', array('module_id' => Input::get('module_id'),'access_token' => Input::get('access_token'), 'plant_id' => $plantId,'type' => $objectType,'object_id' => $objectId,'action' => $action));
				$originalInput = Request::input(); //backup original input
				Request::replace($request->input());
				Log::info($request->input());
				$res2 = Route::dispatch($request)->getContent();
				$result = json_decode($res2);
				//echo "<pre/>";print_r($result);exit;
				$message =$result->Message;
		//	}
			/*else
			{*/			
				$query = DB::table('erp_objects')->where(array('manufacturer_id'=>$mfg_id,'plant_id'=>$plantId,'object_id'=>$objectId,'type'=>$objectType,'action'=>$action));
				$erpResponse = $query->pluck('response');
				if(!is_null($erpResponse))
				{
					if($objectType == 'PORDER'){
			           
			           $deXml = simplexml_load_string($erpResponse);
					   $deJson = json_encode($deXml);
					   $xml_array = json_decode($deJson,TRUE);
                           
                       if(!isset($xml_array['DATA']['FINISHED_MATERIAL']['ITEM']['UOM']))
                       	   throw new Exception('The production order doesnt have UOM field');

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


					}
					$message ="Successfully retrieved the response.";
				}
				else
				{

					throw new Exception("There is no response stored in the database");
				}
				//$data = isset($erpStatus[0]->response)?$erpStatus[0]->response:'';
				$data =$erpResponse;
			//}
		}
		catch(Exception $e)
		{
			    $status =0;
				Log::info($e->getMessage());
				$message = $e->getMessage();
		}
		Log::info(['Status'=>$status,'Message'=>$message,"Data"=>$data,'Qty'=>$qty]);
	
		if($status == 1){
		$doc = new DOMDocument();
        $doc->loadXML($data);
        $data=$doc->saveXML();
       }

		return json_encode(['Status'=>$status,'Message'=>$message,"Data"=>$data,'Qty'=>$qty]);
	}

public function getPoQuantity(){
	try{
		Log::info(__FUNCTION__.' : '.print_r(Input::get(),true));
		$status =1;
		$message ='Data successfully retrieved';
		$qty ='';
		$bindQty = '';
                $material_code = '';
                $description ='';
		$po_number = trim(Input::get('po_number'));

		if(empty($po_number))
			throw new Exception('PO number not passed');

		$mfgId = $this->roleAccess->getMfgIdByToken(Input::get('access_token'));

                    $query = DB::table('erp_integration')->where('manufacturer_id',$mfgId);
					$erp = $query->select('web_service_url','token','company_code','web_service_username','web_service_password','sap_client')->get();
                    
					 $domain = $erp[0]->web_service_url;
					 $token = $erp[0]->token;
					 $company_code = $erp[0]->company_code;
					 $username = $erp[0]->web_service_username;
					 $password = $erp[0]->web_service_password;
					 $sap_client = $erp[0]->sap_client;
					 $data = ['TOKEN'=>$token,'PORDER'=>$po_number];

					 $method = 'Z030_ESEAL_GET_PORDER_DETAILS_SRV';
					 $method_name = 'GET_PORDER_DETAILS';

					 $url = $domain.$method.'/'.$method_name;

					 $url = sprintf("%s?\$filter=%s", $url, urlencode($this->sapRepo->generateData($data)));
					 $url = $url.'&sap-client='.$sap_client;
					 $curl = curl_init();
					 Log::info($url);
					 curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
					 curl_setopt($curl, CURLOPT_USERPWD, "$username:$password");
					 curl_setopt($curl,CURLOPT_RETURNTRANSFER,true);
					 curl_setopt($curl, CURLOPT_URL, $url);
					 curl_setopt($curl, CURLOPT_HEADER, 0);
					 $result12 = curl_exec($curl);
					 curl_close($curl);

					 $parseData1 = xml_parser_create();
					 xml_parse_into_struct($parseData1, $result12, $documentValues1, $documentIndex1);
					 xml_parser_free($parseData1);
					 $documentData = array();	

					 foreach ($documentValues1 as $data) {
					  if(isset($data['tag']) && $data['tag'] == 'D:PORDER_DATA')
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
				
				  $characteristics= array();	
		          Log::info($xml_array);

		          if($xml_array['HEADER']['Status'] == 0)
		          	throw new Exception($xml_array['HEADER']['Message']);

                  

                  if(!isset($xml_array['DATA']['FINISHED_MATERIAL']['ITEM']['UOM']))
                       	   throw new Exception('The production order doesnt have UOM field');

 					   $uom = $xml_array['DATA']['FINISHED_MATERIAL']['ITEM']['UOM'];
					   $material_code = $xml_array['DATA']['FINISHED_MATERIAL']['ITEM']['MATERIAL_CODE'];
					   $qty = (int)$xml_array['DATA']['FINISHED_MATERIAL']['ITEM']['QUANTITY'];

					   $description1 = Products::where('material_code',$material_code)->get(['description','multiPack']);

					   if(!$description1)
					   	throw new Exception('The material in PORDER doesnt exist in the system');

					   $description = $description1[0]->description;
					   $multiPack = $description1[0]->multiPack;


               if($uom == 'M'){
				 $bindQty = DB::table('eseal_'.$mfgId)->where(['po_number'=>$po_number,'level_id'=>0,'is_active'=>1])->sum('pkg_qty');
               }
			   else {
			   	if($multiPack)
                 $bindQty = DB::table('eseal_'.$mfgId)->where(['po_number'=>$po_number,'level_id'=>0,'is_active'=>1])->sum('pkg_qty');
                else
                 $bindQty = DB::table('eseal_'.$mfgId)->where(['po_number'=>$po_number,'level_id'=>0,'is_active'=>1])->count('eseal_id');

			   }



	}
	catch(Exception $e){
		$status =0;
		$message = $e->getMessage();
	}
    Log::info(['Status'=>$status,'Message'=>$message,'qty'=>$qty,'bindedQty'=>$bindQty,'material_code'=>$material_code,'description'=>$description]);
	return json_encode(['Status'=>$status,'Message'=>$message,'qty'=>$qty,'bindedQty'=>(int)$bindQty,'material_code'=>$material_code,'description'=>$description]);
}

public function getFailedErpObjects(){
	try{
		Log::info(__FUNCTION__.' : '.print_r(Input::get(),true));
		$status =1;
		$message = 'Objects Retrieved Successfully';
		$mfgId = $this->roleAccess->getMfgIdByToken(Input::get('access_token'));

		
		$data =['FROM_DATE'=>"datetime'2016-07-27T00:00:00'",'TO_DATE'=>"datetime'2016-07-27T017:00:00'"];
		$method = 'ZESEAL_048_FAILED_DOCUMENTS_SRV';
		$method_name = 'FAILED_DOC';





		$response = $this->sapCall($mfgId,$method,$method_name,$data,Input::get('access_token'));
			Log::info('SAP response:-');
			Log::info($response);
			$response = json_decode($response);
			if($response->Status){
				$result = $response->Data;
			$parseData1 = xml_parser_create();
			xml_parse_into_struct($parseData1, $result, $documentValues1, $documentIndex1);
			xml_parser_free($parseData1);
			$documentData = array();
			foreach ($documentValues1 as $data) {
				if(isset($data['tag']) && $data['tag'] == 'D:PORDER_DATA')
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
			Log::info($xml_array);

		   $status = $xml_array['HEADER']['Status'];

		   if($status == 0)
		   	 throw new Exception($xml_array['HEADER']['Message']);


		   	foreach($xml_array['DATA'] as $data){
		   		if(!is_array($data['PLANT'])){

		   			$plantId = $data['PLANT'];
                    $objectId = $data['OBJID']; 
                    $objectType = $data['OBJ_TYPE'];
                    $action = 'add';    

                $request = Request::create('scoapi/notifyEseal', 'POST', array('module_id' => Input::get('module_id'),'access_token' => Input::get('access_token'), 'plant_id' => $plantId,'type' => $objectType,'object_id' => $objectId,'action' => $action));
				$originalInput = Request::input(); //backup original input
				Request::replace($request->input());
				Log::info($request->input());
				$res2 = Route::dispatch($request)->getContent();
				$result = json_decode($res2);
				//echo "<pre/>";print_r($result);exit;
		   		}
		   		else{
		   			Log::info('NO PLANT OBJECT:'.$data['OBJID']);
		   		}
		   		
		   	}

        }
        else {
        	throw new Exception($response->Message);
        }


	}
	catch(Exception $e){
		$status =0;
		$message = $e->getMessage();
	}
	Log::info(['Status'=>$status,'Message'=>$message]);
	return json_encode(['Status'=>$status,'Message'=>$message]);
}


public function getErpObjectResponse()
	{
		$status =1;
		$objectType = Input::get('type');
		$objectId = Input::get('object_id');
		$action = 'add';
		$poIntransitQty = array();
		$qty ='';
		$location_id = '';
		$locationId = $this->roleAccess->getLocIdByToken(Input::get('access_token'));
		$mfg_id = $this->roleAccess->getMfgIdByToken(Input::get('access_token'));
		$plantId = DB::table('locations')->where('location_id',$locationId)->pluck('erp_code');

	   Log::info(__FUNCTION__.' : '.print_r(Input::get(),true));
	   		
		
		$data = "";
		 DB::beginTransaction();
		try
		{	   		
		
				//Calling the BindGrnData API from Eseal
				$request = Request::create('scoapi/notifyEseal', 'POST', array('module_id' => Input::get('module_id'),'access_token' => Input::get('access_token'), 'plant_id' => $plantId,'type' => $objectType,'object_id' => $objectId,'action' => $action));
				$originalInput = Request::input(); //backup original input
				Request::replace($request->input());
				Log::info($request->input());
				$res2 = Route::dispatch($request)->getContent();
				$result = json_decode($res2);
				//echo "<pre/>";print_r($result);exit;
				$status = $result->Status;
				$message = $result->Message;
				if($status == 0){
				  throw new Exception($result->Message);	
				}
						
				$query = DB::table('erp_objects')->where(array('manufacturer_id'=>$mfg_id,'object_id'=>$objectId,'type'=>$objectType,'action'=>$action));
				$erpResponse = $query->pluck('response');
				if(!is_null($erpResponse))
				{
					   $deXml = simplexml_load_string($erpResponse);
					   $deJson = json_encode($deXml);
					   $xml_array = json_decode($deJson,TRUE);

					if($objectType == 'PORDER'){
			           
			           
                           
                       if(!isset($xml_array['DATA']['FINISHED_MATERIAL']['ITEM']['UOM']))
                       	   throw new Exception('The production order doesnt have UOM field');

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


					}
					if($objectType == 'PO'){
			           
			           $data = $xml_array['DATA']['ITEMS'];


                      if(!array_key_exists('NO', $data['ITEM'])){
                        foreach($data['ITEM'] as $data1){
                        	$plantArr[] = ltrim($data1['PLANT'],0);
                        }
					  }
					   else{
						$data2 = $data['ITEM'];
						$plantArr[] = ltrim($data2['PLANT'],0);

					   }


                       $plantArr = array_unique($plantArr);

                       if(count($plantArr) > 1)
                       	 throw new Exception('The PO has ITEMS for multiple delivery locations.');

                       $location_code = $plantArr[0];
                       $location_id = Location::where('erp_code',$location_code)->pluck('location_id');

                       if(!$location_id)
                       throw new Exception('The Delivery Location doesnt exist in the system'); 


                       $tps = DB::table('tp_attributes')
                                ->where(['attribute_name'=>'Purchase Order No','value'=>$objectId])
                                ->lists('tp_id');

                          for($i=0;$i < count($tps);$i++){
                          	$dest = Trackhistory::where('tp_id',$tps[$i])->orderBy('track_id','desc')->take(1)->pluck('dest_loc_id');
                          	if($dest == 0)
                          		unset($tps[$i]);

                          }      

                          if(!empty($tps)){                                            

                          $output = DB::table('track_history as th')
                          	                   ->join('eseal_'.$mfg_id.' as es','es.track_id','=','th.track_id')
				                          	   ->join('products as pr','pr.product_id','=','es.pid')
				                          	   ->whereIn('tp_id',$tps)
				                          	   ->where('level_id',0)
				                          	   ->groupBy('pid')                          	   
				                          	   ->get([DB::raw('sum(pkg_qty) as qty'),'material_code']);



				          foreach ($output as $out) {
				          	$poIntransitQty[$out->material_code] = (int)$out->qty;
				          }


                              
                          }           	

 					   

					}
					if($objectType == 'DELIVERYDETAILS'){
			           
			          
                      if(is_array($xml_array['DATA']['TARGET_PLANT']))
			             $location_code = ltrim($xml_array['DATA']['DELIVERY_DOCUMENTS']['SOLD_TO']['PARTNER_NO'],0);
			          else
			          	 $location_code = ltrim($xml_array['DATA']['TARGET_PLANT'],0);
                      

                      $location_id = Location::where('erp_code',$location_code)->pluck('location_id');

                       if(!$location_id)
                       throw new Exception('The Delivery Location doesnt exist in the system'); 	                                          

					}
					$message ="Successfully retrieved the response.";
				}
				else
				{
					throw new Exception($message);
				}
				//$data = isset($erpStatus[0]->response)?$erpStatus[0]->response:'';
				$data =$erpResponse;
			//}
		    DB::commit();
				if($status == 1){
		         $doc = new DOMDocument();
                 $doc->loadXML($data);
                 $data=$doc->saveXML();
                }
		}
		catch(Exception $e)
		{
			    $status = 0;
			   DB::rollback();
				Log::info($e->getMessage());
				$message = $e->getMessage();
		}
		Log::info(['Status'=>$status,'Message'=>$message,"Data"=>$data,'Qty'=>$qty,'Location_id'=>$location_id]);			
		return json_encode(['Status'=>$status,'Message'=>$message,"Data"=>$data,'Qty'=>$qty,'Location_id'=>(string)$location_id]);
	}

// public function demo(){


//    $input = Input::get();

// 	$request = Request::create('scoapi/notifyEseal', 'POST', array('module_id' => Input::get('module_id'),'access_token' => Input::get('access_token'), 'plant_id' => $plantId,'type' => $objectType,'object_id' => $objectId,'action' => $action));
// 				$originalInput = Request::input(); //backup original input
// 				Request::replace($request->input());
// 				Log::info($request->input());
// 				$res2 = Route::dispatch($request)->getContent();
// 				$result = json_decode($res2);
// 				//echo "<pre/>";print_r($result);exit;
// 				$status = $result->Status;
// 				$message = $result->Message;
// 				if($status == 0){
// 				  throw new Exception($result->Message);	
// 				}

// }

	public function getProductComponents($data){
		try{
		$status =0;
		$prod = array();

		$manufacturer_id = $this->roleAccess->getMfgIdByToken($data['access_token']);
		$products = DB::table('products')->where('manufacturer_id',$manufacturer_id)->get(['product_id','material_code','name','is_traceable']);
		//Log::info('products:');
		//Log::info($products);
		foreach($products as $product){

			$componentData = DB::table('product_components')->join('products', 'products.product_id', '=', 'component_id')
			->where('product_components.product_id', $product->product_id)
			->where('is_traceable',1)
			->get(['component_id','component_type_id','component_erp_code','products.name as component_name','is_traceable','qty']);
			
			
			$prod[] = ['product_id'=>$product->product_id,'material_code'=>$product->material_code,'name'=>$product->name,'is_traceable'=>$product->is_traceable,'components'=>$componentData];
				
			
		}
		
		if(empty($prod)){
		  throw new Exception('Data not found');
		}
		else{
		  $status =1;
		  $message = 'Data successfully retrieved';
		}
	}
	catch(Exception $e){
	  $message = $e->getMessage();
	}
	  Log::info(['Status'=>$status,'Message'=>$message,'Data'=>$prod]);	
	  return json_encode(['Status'=>$status,'Message' =>'Server: '.$message,'Data'=>$prod]);
	}


	//wms related functions
	public function getWareHouseData()
	{
		$totalResult = array();		
		$location_id = $this->roleAccess->getLocIdByToken(Input::get('access_token'));
		$mfg_id = $this->roleAccess->getMfgIdByToken(Input::get('access_token'));
		try{
			$wares = DB::table('wms_entities')
			->leftJoin('master_lookup', 'master_lookup.value', '=', 'wms_entities.entity_type_id')
			->leftJoin('wms_dimensions', 'wms_dimensions.entity_id', '=', 'wms_entities.id')
			->select('wms_entities.*','wms_dimensions.*','wms_dimensions.id as dimension_id','master_lookup.name')
			->where(array('wms_entities.entity_type_id' => 6001, 'wms_entities.location_id'=>$location_id,'wms_entities.org_id'=>$mfg_id))
			->get();
			
			if(empty($wares)){
				$orgs = DB::table('wms_entities')
				->select('id as entity_id'	,'org_id','entity_name','entity_type_id')
				->where('location_id',0)->get();
				$arr['status'] = 2;
				$arr['data'] = $orgs;
				return $arr;
			}
					$gorg_id = $wares[0]->org_id;
					
					$orgs = DB::table('wms_entities')	            	
					->where('wms_entities.org_id',$gorg_id)
					->where('wms_entities.location_id',0)
					->orderBy('id', 'ASC')
					->get();

					$finalorgarr = array();
					$org_array = array();
					$org_array['entity_id']=$orgs[0]->id;
					$org_array['entity_type_id']=$orgs[0]->entity_type_id;
					$org_array['entity_type_name']=$orgs[0]->entity_type_id;
					$org_array['entity_name']='Organization';
					$org_array['entity_code']=$orgs[0]->entity_code;
					$org_array['org_id']=$orgs[0]->org_id;
					$org_array['entity_location'] = $orgs[0]->entity_location;

					$finalorgarr = $org_array;
					$totalResult[] = $finalorgarr;
					$warearr = array();
					$finalwarearr = array();
					if(!empty($wares))
					{	            		
						foreach($wares as $ware)
						{
							$floors = DB::table('wms_entities')
							->leftJoin('master_lookup', 'master_lookup.value', '=', 'wms_entities.entity_type_id')
							->leftJoin('wms_dimensions', 'wms_dimensions.entity_id', '=', 'wms_entities.id')
							->select('wms_entities.*','wms_entities.id as entity_id','wms_dimensions.*','wms_dimensions.id as dimension_id','master_lookup.name')
							->where('wms_entities.parent_entity_id',$ware->entity_id)
							->get();
							$floorarr = array();
							$finalfloorarr = array();
							if(!empty($floors))
							{
								
								foreach($floors as $floor)
								{
									$zones = DB::table('wms_entities')
									->leftJoin('master_lookup', 'master_lookup.value', '=', 'wms_entities.entity_type_id')
									->leftJoin('wms_dimensions', 'wms_dimensions.entity_id', '=', 'wms_entities.id')
									->select('wms_entities.*','wms_entities.id as entity_id','wms_dimensions.*','wms_dimensions.id as dimension_id','master_lookup.name')
									->where('wms_entities.parent_entity_id',$floor->entity_id)
									->get();
									$zonearr = array();
									$finalzonearr = array();
									if(!empty($zones))
									{
										foreach($zones as $zone)
										{
											$racks = DB::table('wms_entities')
											->leftJoin('master_lookup', 'master_lookup.value', '=', 'wms_entities.entity_type_id')
											->leftJoin('wms_dimensions', 'wms_dimensions.entity_id', '=', 'wms_entities.id')
											->select('wms_entities.*','wms_entities.id as entity_id','wms_dimensions.*','wms_dimensions.id as dimension_id','master_lookup.name')
											->where('wms_entities.parent_entity_id',$zone->entity_id)
											->get();
											$rackarr = array();
											$finalrackarr = array();
											if(!empty($racks))
											{					            		
												foreach($racks as $rack)
												{
													$bins = DB::table('wms_entities')
													->leftJoin('master_lookup', 'master_lookup.value', '=', 'wms_entities.entity_type_id')
													->leftJoin('wms_dimensions', 'wms_dimensions.entity_id', '=', 'wms_entities.id')
													->select('wms_entities.*','wms_entities.id as entity_id','wms_dimensions.*','wms_dimensions.id as dimension_id','master_lookup.name')
													->where('wms_entities.parent_entity_id',$rack->entity_id)
													->get();
													$binarr = array();
													$finalbinarr = array();
													if(!empty($bins))
													{
														foreach($bins as $bin)
														{
															$binarr['entity_id'] = $bin->entity_id;
															$binarr['entity_name'] = $bin->entity_name;
															$binarr['entity_code'] = $bin->entity_code;
															$binarr['entity_type_id'] = $bin->entity_type_id;
															$binarr['entity_type_name'] = $bin->name;
															$binarr['parent_entity_id'] = $bin->parent_entity_id;
															$binarr['ware_id'] = $ware->entity_id;
															$binarr['floor_id'] = $floor->entity_id;
															$binarr['capacity'] = $bin->capacity;
															$binarr['capacity_uom_id'] = $bin->capacity_uom_id;
															$binarr['entity_location'] = $bin->entity_location;
															$storage = Bins::where('entity_id',$bin->entity_id)->get();
															$binarr['capacity_pallet'] = $storage[0]->storage_capacity;
															$binarr['pid'] = $storage[0]->pid;
															$binarr['pname'] = $storage[0]->pname;
															$binarr['holding_count'] = $storage[0]->holding_count;
															$binarr['status'] = $storage[0]->status;
													//$binarr['location_id'] = $bin->location_id;
															$binarr['dimension_id'] = $bin->dimension_id;
													//$binarr['org_id'] = $bin->org_id;
															$binarr['xco'] = $bin->xco;
															$binarr['yco'] = $bin->yco;
															$binarr['zco'] = $bin->zco;
															$binarr['height'] = $bin->height;
															$binarr['width'] = $bin->width;
															$binarr['depth'] = $bin->depth;
															$binarr['length'] = $bin->length;
															$binarr['area'] = $bin->area;
															$binarr['uom_id'] = $bin->uom_id;
															$binarr['is_assigned'] = $bin->is_assigned;

															$child_entity_type_id = $bin->entity_type_id+1;            								
															$totalResult[] = $binarr;
														}

													}
													else{
														$finalbinarr[] = '';
													}
													$rackarr['entity_id'] = $rack->entity_id;
													$rackarr['entity_name'] = $rack->entity_name;
													$rackarr['entity_code'] = $rack->entity_code;
													$rackarr['entity_type_id'] = $rack->entity_type_id;
													$rackarr['entity_type_name'] = $rack->name;
													$rackarr['entity_location'] = $rack->entity_location;
													$rackarr['parent_entity_id'] = $rack->parent_entity_id;
													$rackarr['ware_id'] = $ware->entity_id;
													$rackarr['floor_id'] = $floor->entity_id;
													$rackarr['capacity'] = $rack->capacity;
													$rackarr['capacity_uom_id'] = $rack->capacity_uom_id;
											//$rackarr['location_id'] = $rack->location_id;
													$rackarr['dimension_id'] = $rack->dimension_id;
											//$rackarr['org_id'] = $rack->org_id;
													$rackarr['xco'] = $rack->xco;
													$rackarr['yco'] = $rack->yco;
													$rackarr['zco'] = $rack->zco;					            
													$rackarr['height'] = $rack->height;
													$rackarr['width'] = $rack->width;
													$rackarr['depth'] = $rack->depth;
													$rackarr['length'] = $rack->length;
													$rackarr['area'] = $rack->area;
													$rackarr['uom_id'] = $rack->uom_id;
													$rackarr['is_assigned'] = $rack->is_assigned;

													$child_entity_type_id = $rack->entity_type_id+1;
											//if(!empty($finalbinarr))
												//$rackarr['bin'] = $finalbinarr;
													$totalResult[] = $rackarr;
												}
											}
											else{
												$finalrackarr[] = '';
											}
											$zonearr['entity_id'] = $zone->entity_id;
											$zonearr['entity_name'] = $zone->entity_name;
											$zonearr['entity_code'] = $zone->entity_code;
											$zonearr['entity_type_id'] = $zone->entity_type_id;
											$zonearr['entity_type_name'] = $zone->name;
											$zonearr['parent_entity_id'] = $zone->parent_entity_id;
											$zonearr['entity_location'] = $zone->entity_location;
											$zonearr['ware_id'] = $ware->entity_id;
											$zonearr['floor_id'] = $floor->entity_id;
											$zonearr['capacity'] = $zone->capacity;
											$zonearr['capacity_uom_id'] = $zone->capacity_uom_id;
									//$zonearr['location_id'] = $zone->location_id;
											$zonearr['dimension_id'] = $zone->dimension_id;
									//$zonearr['org_id'] = $zone->org_id;
											$zonearr['xco'] = $zone->xco;
											$zonearr['yco'] = $zone->yco;
											$zonearr['zco'] = $zone->zco;
											$zonearr['height'] = $zone->height;
											$zonearr['width'] = $zone->width;
											$zonearr['depth'] = $zone->depth;
											$zonearr['length'] = $zone->length;
											$zonearr['area'] = $zone->area;
											$zonearr['uom_id'] = $zone->uom_id;
											$zonearr['is_assigned'] = $zone->is_assigned;

											$child_entity_type_id = $zone->entity_type_id+1;
									//if($finalrackarr!='')
										//$zonearr['racks'] = $finalrackarr;
											$totalResult[] = $zonearr;	
										}

									}
									else
									{
										$finalzonearr[] = '';
									}
									$floorarr['entity_id'] = $floor->entity_id;
									$floorarr['entity_name'] = $floor->entity_name;
									$floorarr['entity_code'] = $floor->entity_code;
									$floorarr['entity_type_id'] = $floor->entity_type_id;
									$floorarr['entity_type_name'] = $floor->name;
									$floorarr['entity_location'] = $floor->entity_location;
									$floorarr['parent_entity_id'] = $floor->parent_entity_id;
									$floorarr['ware_id'] = $ware->entity_id;
									$floorarr['floor_id'] = '';
									$floorarr['capacity'] = $floor->capacity;
									$floorarr['capacity_uom_id'] = $floor->capacity_uom_id;
							//$warearr['location_id'] = $ware->location_id;
									$floorarr['dimension_id'] = $floor->dimension_id;
							//$warearr['org_id'] = $ware->org_id;
									$floorarr['xco'] = $floor->xco;
									$floorarr['yco'] = $floor->yco;
									$floorarr['zco'] = $floor->zco;
									$floorarr['height'] = $floor->height;
									$floorarr['width'] = $floor->width;
									$floorarr['depth'] = $floor->depth;
									$floorarr['length'] = $floor->length;
									$floorarr['area'] = $floor->area;
									$floorarr['uom_id'] = $floor->uom_id;
									$floorarr['is_assigned'] = $floor->is_assigned;

									$child_entity_type_id = $floor->entity_type_id+1;		           	
							//if(!empty($finalzonearr))
								//$warearr['zones'] = $finalzonearr;
									$totalResult[] = $floorarr;
								}
							}
							else
							{
								$finalfloorarr[] = '';
							}
							$warearr['entity_id'] = $ware->entity_id;
							$warearr['entity_name'] = $ware->entity_name;
							$warearr['entity_code'] = $ware->entity_code;
							$warearr['entity_type_id'] = $ware->entity_type_id;
							$warearr['entity_type_name'] = $ware->name;
							$warearr['entity_location'] = $ware->entity_location;
							$warearr['parent_entity_id'] = $ware->parent_entity_id;
							$warearr['ware_id'] = '';
							$warearr['floor_id'] = '';
							$warearr['capacity'] = $ware->capacity;
							$warearr['capacity_uom_id'] = $ware->capacity_uom_id;
							//$warearr['location_id'] = $ware->location_id;
							$warearr['dimension_id'] = $ware->dimension_id;
							//$warearr['org_id'] = $ware->org_id;
							$warearr['xco'] = $ware->xco;
							$warearr['yco'] = $ware->yco;
							$warearr['zco'] = $ware->zco;
							$warearr['height'] = $ware->height;
							$warearr['width'] = $ware->width;
							$warearr['depth'] = $ware->depth;
							$warearr['length'] = $ware->length;
							$warearr['area'] = $ware->area;
							$warearr['uom_id'] = $ware->uom_id;

							$child_entity_type_id = $ware->entity_type_id+1;		           	
							//if(!empty($finalzonearr))
								//$warearr['zones'] = $finalzonearr;
							$totalResult[] = $warearr;


						}
					}  
					$result = array("status"=>1,"message"=>"Success","location_id"=>$wares[0]->location_id,"org_id"=>$wares[0]->org_id,"data"=>$totalResult);
				}
				catch(Exception $e)
				{
					$result = array("status"=>0,"message"=>"Failure","data"=>"no data found");
					return json_encode($result);
				}
				print_r(json_encode($result));exit;
				//return json_encode($result);
	}

	

	public function getAssignedData()
	{
		$mapEntities = DB::table('wms_eseal')
		->leftJoin('wms_entities', 'wms_entities.id', '=', 'wms_eseal.entity_id')
		->leftJoin('wms_packages', 'wms_packages.id', '=', 'wms_eseal.package_id')
		->leftJoin('catalog_product_entity_text','wms_eseal.product_id','=','catalog_product_entity_text.entity_id')
		->select('catalog_product_entity_text.value','wms_eseal.id','wms_entities.entity_name','wms_eseal.entity_id','wms_eseal.package_id','wms_packages.package_name','wms_eseal.product_id','wms_eseal.package_id','wms_eseal.locator')
		->where('catalog_product_entity_text.attribute_id',64)
		->get();
		$getArr = array();
		$finalgetArr = array();
		foreach($mapEntities as $value)
		{
			$getArr['id'] = $value->id;
			$getArr['entity_id'] = $value->entity_id;
			$getArr['entity_name'] = $value->entity_name;
			$getArr['product_id'] = $value->product_id;
			$getArr['product_name'] = $value->value;
			$getArr['package_id'] = $value->package_id;
			$getArr['package_name'] = $value->package_name;
			$getArr['locator'] = $value->locator;            	
			$finalgetArr[] = $getArr;
		}

		return json_encode(array('status' => 1,'message' => 'Success','Data' => $finalgetArr));
	}
			
	public function getPackagesData()
	{
		try {
				$entity_types = Package::all();
				$getArr = array();
				$finalgetArr = array();
				foreach($entity_types as $value)
				{
					$getArr['id'] = $value->id;
					$getArr['package_name'] = $value->package_name;
					$getArr['weight'] = $value->weight;
					$getArr['weight_uom_id'] = $value->weight_uom_id;
					$getArr['package_type_id'] = $value->package_type_id;
					$getArr['package_length'] = $value->package_length;
					$getArr['package_height'] = $value->package_height;
					$getArr['package_width'] = $value->package_width;
					$getArr['package_dimension_id'] = $value->package_dimension_id;
					$product_name = DB::table('catalog_product_entity_text')->where(array('attribute_id'=>64,'entity_id'=> $value->pname))->first();
					$getArr['pname'] = $product_name->value;
					$finalgetArr[] = $getArr;
				} 
		}
		catch(Exception $e)
		{
			$result = array("status"=>1,"message"=>"Failure","data"=>"no data found");
			//return json_encode($result);
		}                 
		return json_encode(array('status' => 1,'message' => 'Success','Data' => $finalgetArr));
	}

	public function getalldata()
	{
		$orgs = DB::table('wms_entities')
		->leftJoin('wms_entity_types', 'wms_entity_types.id', '=', 'wms_entities.entity_type_id')
		->select('wms_entities.id','wms_entities.entity_name','wms_entities.entity_location','wms_entities.entity_code','wms_entities.org_id','wms_entity_types.entity_type_name','wms_entities.parent_entity_id','wms_entities.capacity','wms_entities.entity_type_id')
		->where('wms_entities.entity_type_id',0)
		->get();
		if(!empty($orgs))
		{
			$orgarr = array();
			$finalorgarr = array();
			foreach($orgs as $org)
			{
				$wares = DB::table('wms_entities')
				->leftJoin('wms_entity_types', 'wms_entity_types.id', '=', 'wms_entities.entity_type_id')
				->select('wms_entities.id','wms_entities.entity_name','wms_entities.entity_location','wms_entities.entity_code','wms_entities.org_id','wms_entities.location_id','wms_entity_types.entity_type_name','wms_entities.parent_entity_id','wms_entities.capacity','wms_entities.entity_type_id')
				->where('wms_entities.parent_entity_id',$org->id)
				->get();

				$warearr = array();
				$finalwarearr = array();
				if(!empty($wares))
				{	            		
					foreach($wares as $ware)
					{
						$zones = DB::table('wms_entities')
						->leftJoin('wms_entity_types', 'wms_entity_types.id', '=', 'wms_entities.entity_type_id')
						->select('wms_entities.id','wms_entities.entity_name','wms_entities.entity_code','wms_entities.org_id','wms_entities.location_id','wms_entities.location_id','wms_entity_types.entity_type_name','wms_entities.parent_entity_id','wms_entities.capacity','wms_entities.entity_type_id')
						->where('wms_entities.parent_entity_id',$ware->id)
						->get();

					//return 'nikhil kishore';
						$zonearr = array();
						$finalzonearr = array();
						if(!empty($zones))
						{

							foreach($zones as $zone)
							{
								$racks = DB::table('wms_entities')
								->leftJoin('wms_entity_types', 'wms_entity_types.id', '=', 'wms_entities.entity_type_id')
								->select('wms_entities.id','wms_entities.entity_name','wms_entities.entity_code','wms_entities.org_id','wms_entities.location_id','wms_entity_types.entity_type_name','wms_entities.parent_entity_id','wms_entities.capacity','wms_entities.entity_type_id')
								->where('wms_entities.parent_entity_id',$zone->id)
								->get();
								$rackarr = array();
								$finalrackarr = array();
								if(!empty($racks))
								{					            		
									foreach($racks as $rack)
									{
										$bins = DB::table('wms_entities')
										->leftJoin('wms_entity_types', 'wms_entity_types.id', '=', 'wms_entities.entity_type_id')
										->select('wms_entities.id','wms_entities.entity_name','wms_entities.entity_code','wms_entities.org_id','wms_entity_types.entity_type_name','wms_entities.parent_entity_id','wms_entities.capacity','wms_entities.entity_type_id')
										->where('wms_entities.parent_entity_id',$rack->id)
										->get();

										$binarr = array();
										$finalbinarr = array();
										if(!empty($bins))
										{
											foreach($bins as $bin)
											{
												$binarr['id'] = $bin->id;
												$binarr['entity_name'] = $bin->entity_name;
												$binarr['entity_code'] = $bin->entity_code;
												$binarr['entity_type_name'] = $bin->entity_type_name;
												$binarr['capacity'] = $bin->capacity;
												$binarr['entity_type_id'] = $bin->entity_type_id;
												$child_entity_type_id = $bin->entity_type_id+1;
												$binarr['create'] = '';
												$binarr['edit'] = '<a href="entities/edit/'.$bin->id.'"><img src="img/edit.png" /></a>'; 
												$binarr['delete'] = '<a onclick="deleteEntity('.$bin->id.')" href=""><img src="img/delete.png" /></a>';
												$binarr['assign'] = '<a href="assignlocation/create/'.$bin->id.'">Assign</a>'; 
												$finalbinarr[] = $binarr;
											}
										}
										else{
											$finalbinarr[] = '';
										}
										$rackarr['id'] = $rack->id;
										$rackarr['entity_name'] = $rack->entity_name;
										$rackarr['entity_code'] = $rack->entity_code;
										$rackarr['entity_type_name'] = $rack->entity_type_name;
										$rackarr['capacity'] = $rack->capacity;
										$rackarr['entity_type_id'] = $rack->entity_type_id;
										$child_entity_type_id = $rack->entity_type_id+1;
										$rackarr['create'] = '<a href="entities/create/'.$child_entity_type_id.'/'.$rack->id.'/'.$rack->org_id.'/'.$rack->location_id.'"><img src="img/add.png" /></a>'; 
										$rackarr['edit'] = '<a href="entities/edit/'.$rack->id.'"><img src="img/edit.png" /></a>'; 
										$rackarr['delete'] = '<a onclick="deleteEntity('.$rack->id.')" href=""><img src="img/delete.png" /></a>';
										$rackarr['assign'] = '<a href="assignlocation/create/'.$rack->id.'">Assign</a>'; 
										$rackarr['children'] = $finalbinarr;
										$finalrackarr[] = $rackarr;
									}
								}
								else{
									$finalrackarr[] = '';
								}
								$zonearr['id'] = $zone->id;
								$zonearr['entity_name'] = $zone->entity_name;
								$zonearr['entity_code'] = $zone->entity_code;
								$zonearr['entity_type_name'] = $zone->entity_type_name;
								$zonearr['capacity'] = $zone->capacity;
								$zonearr['entity_type_id'] = $zone->entity_type_id;
								$child_entity_type_id = $zone->entity_type_id+1;
								if($zone->entity_type_id==5){
									$zonearr['create'] = '';
									$zonearr['assign'] = '';
								}
								else{
									$zonearr['create'] = '<a href="entities/create/'.$child_entity_type_id.'/'.$zone->id.'/'.$zone->org_id.'/'.$zone->location_id.'"><img src="img/add.png" /></a>'; 
									$zonearr['assign'] = '<a href="assignlocation/create/'.$zone->id.'">Assign</a>';   
								}

								$zonearr['edit'] = '<a href="entities/edit/'.$zone->id.'"><img src="img/edit.png" /></a>'; 
								$zonearr['delete'] = '<a onclick="deleteEntity('.$zone->id.')" href=""><img src="img/delete.png" /></a>';

								$zonearr['children'] = $finalrackarr;
								$finalzonearr[] = $zonearr;		             	
							}

						}
						else
						{
							$finalzonearr[] = '';
						}
						$warearr['id'] = $ware->id;
						$warearr['entity_name'] = $ware->entity_name;
						$warearr['entity_code'] = $ware->entity_code;
						$warearr['entity_type_name'] = $ware->entity_type_name;
						$warearr['capacity'] = $ware->capacity;
						$warearr['entity_type_id'] = $ware->entity_type_id;
						$child_entity_type_id = $ware->entity_type_id+1;
						$warearr['create'] = '<a href="/wms/entities/create/'.$child_entity_type_id.'/'.$ware->id.'/'.$ware->org_id.'/'.$ware->location_id.'"><img src="img/add.png" /></a>';   
						$warearr['edit'] = '<a href="entities/edit/'.$ware->id.'"><img src="img/edit.png" /></a>'; 
						$warearr['delete'] = '<a onclick="deleteEntity('.$ware->id.')" href=""><img src="img/delete.png" /></a>';
						$warearr['assign'] = ''; 
						$warearr['children'] = $finalzonearr;
						$finalwarearr[] = $warearr;
					}

				}
				else
				{
					$finalwarearr[] = '';
				}
				$orgarr['id'] = $org->id;
				$orgarr['entity_name'] = $org->entity_name;
				$orgarr['entity_code'] = 0;
				$orgarr['entity_type_name'] = $org->entity_type_name;
				$orgarr['capacity'] = $org->capacity;
				$orgarr['entity_type_id'] = $org->entity_type_id;
				$child_entity_type_id = $org->entity_type_id+1;
				$orgarr['create'] = '<a href="entities/create1/'.$child_entity_type_id.'/'.$org->id.'/'.$org->org_id.'"><img src="img/add.png" /></a>'; 
				$orgarr['edit'] = '<a href=""><img src="img/edit.png" /></a>'; 
				$orgarr['delete'] = '<a href=""><img src="img/delete.png" /></a>';
				$orgarr['assign'] = '';
				$orgarr['children'] = $finalwarearr;
				$finalorgarr[] = $orgarr;
			}

		}
		else{
			$finalorgarr[] = '';
		}
		return json_encode($finalorgarr);
		return $finalorgarr;
	}

	public function createPackage()
	{
		try{
			$data = Input::get();
			foreach($data as $d){
				if($d == ''){
					return json_encode(array('status'=> 0,'message' =>'One or more of the parameters is empty.'));
				}
			}
			$package = Package::where('package_name',$data['package_name'])->first();
			if(empty($package)){

				$package = new Package;
				$package->package_name = $data['package_name'];
				$package->pname = $data['pid'];
				$package->weight = $data['weight'];
				$package->weight_uom_id = $data['weight_uom'];
				$package->package_type_id = $data['package_type'];
				$package->package_width = $data['width'];
				$package->package_height = $data['height'];
				$package->package_length = $data['length'];
				$package->package_dimension_id = $data['dimension_uom'];
				$package->save();

				return json_encode(array('status'=> 1,'message' =>'Package Created Successfully.'));   
			}
			else{
				return json_encode(array('status'=> 0,'message' =>'Package already exists.'));
			}
		}
		catch(exception $e){
			return json_encode(array('status'=> 0,'message' =>'Parameters Missing.'));   
		}
	}

	public function updatePackage()
	{
		$data = Input::get();
		if(!isset($data['package_id']) || empty($data['package_id'])){
			return json_encode(array('status'=> 0,'message' =>'Package Id missing'));
		}
		$package = Package::where('id',$data['package_id'])->first();
		$package->package_name = $data['package_name'];
		$package->pname = $data['pid'];
		$package->weight = $data['weight'];
		$package->weight_uom_id = $data['weight_uom'];
		$package->package_type_id = $data['package_type'];
		$package->package_width = $data['width'];
		$package->package_height = $data['height'];
		$package->package_length = $data['length'];
		$package->package_dimension_id = $data['dimension_uom'];
		$package->save();
		return json_encode(array('status'=> 1,'message' =>'Package Updated Successfully.'));
	}

	public function deletePackage()
	{
		$data = Input::get();
		if(!isset($data['package_id']) || empty($data['package_id'])){
			return json_encode(array('status'=>0,'message'=>'Package_id missing'));
		}
		$package = Package::where('id',$data['package_id'])->first();
		$package->delete();
		return json_encode(array('status'=>1,'message'=>'Package Deleted Successfully'));
	}

	public function getMasterLookupData()
	{
		$lookup_id = array();
		$lookup_id = DB::table('lookup_categories')
				->select('id', 'name', 'description', 'is_active')
				->whereIn('name', ['WH Entity Types', 'Length UOM', 'Capacity UOM', 'Area UOM', 'Volume UOM', 'Storage Location Types', 'Pallet types', 'Order Status'])
				->get();
		$master_lookup_value = array();
		$master_lookup_value = DB::table('lookup_categories')
				->join('master_lookup', 'lookup_categories.id', '=', 'master_lookup.category_id')
				->select('master_lookup.id', 'master_lookup.category_id', 'master_lookup.name', 'master_lookup.value', 'master_lookup.description', 'master_lookup.is_active', 'master_lookup.sort_order')
				->whereIn('lookup_categories.name', ['WH Entity Types', 'Length UOM', 'Capacity UOM', 'Area UOM', 'Volume UOM', 'Storage Location Types', 'Pallet types', 'Order Status'])
				->get();
		$channelData = DB::table('Channel')->select('channel_id', 'channnel_name')->get();
		if (!empty($lookup_id) && !empty($master_lookup_value))
		{
			$Status = 1;
			return json_encode(array('Status' => $Status, 'Message' => 'Updated Successfully.', 'lookup_categories' => $lookup_id, 'master_lookup_data' => $master_lookup_value, 'channel_data' => $channelData));
		} else
		{
			$Status = 0;
			return json_encode(array('Status' => $Status, 'Message' => 'No Data Returned.'));
		}
	}

	public function updateWareHouse()
	{
		return $this->createWareHouse(Input::get());
	}

	public function createWareHouse($data = null)
	{
		if(!$data)
		{
			$data = Input::get();	
		}				
		if(!isset($data['height'])){
			$data['height'] = '';
		}	
		if(!isset($data['width'])){
			$data['width'] = '';
		}	
		if(!isset($data['length'])){
			$data['length'] = '';
		}	
		if(!isset($data['depth'])){
			$data['depth'] = '';
		}	
		if(!isset($data['xco'])){
			$data['xco'] = '';
		}
		if(!isset($data['yco'])){
			$data['yco'] = '';
		}
		if(!isset($data['zco'])){
			$data['zco'] = '';
		}
		$manufacturer_id = DB::table('locations')->where('location_id',$data['location_id'])->pluck('manufacturer_id');
		
		try{ 
			if(isset($data['entity_id']) && !empty($data['entity_id'])){
				$entity = Entities::where('id',$data['entity_id'])->first();

				if($data['entity_type_id'] == 6005){
					$bin = DB::table('wms_storage_bins')
					->where('entity_id',$data['entity_id'])
					->update(['storage_bin_name'=>$data['entity_name'],'storage_capacity'=>$data['capacity']]);

				}
			}  
			else{ 

				$entity = new Entities;

			}
			$entity->entity_type_id = $data['entity_type_id'];
			$entity->ware_id = $data['ware_id'];
			if($data['entity_type_id'] == 6001)
			{   
				$org_id = Entities::where('org_id',$manufacturer_id)->pluck('id');
				$entity->parent_entity_id = $org_id;
			}else{     
				$entity->parent_entity_id = $data['parent_id'];
			}
			$entity->capacity = $data['capacity'];
			$entity->entity_location = $data['entity_location'];
			$entity->capacity_uom_id = $data['capacity_uom'];
			$entity->status = 1;
			$entity->xco = $data['xco'];
			$entity->yco = $data['yco'];
			$entity->zco = $data['zco'];
			$entity->location_id = $data['location_id'];
			$entity->org_id = $manufacturer_id;
			$entity->save();

			if(!isset($data['entity_id']))
			{  
				$entity_id = DB::getPdo()->lastInsertId();
				if($data['entity_type_id']==6005){
					$bins = new Bins;
					$bins->entity_id = $entity_id;
					$bins->storage_bin_name = $data['entity_name'];
					$bins->status = 'Empty';

					$bins->ware_id = $data['ware_id'];
					$bins->storage_capacity = $data['capacity'];
					$bins->is_allocated = 0;
					$bins->save();
				}

				if($data['entity_type_id'] ==6001)
				{
					$entity_name = 'Warehouse'; 	
					$entity_code = 'W'.$entity_id;
				}
				else if($data['entity_type_id']==6002)
				{
					$entity_name = 'Floor';
					$entity_code = 'F'.$entity_id;
				}
				else if($data['entity_type_id']==6008){
					$entity_name = 'Dock';
					$entity_code = 'D'.$entity_id;
				} 
				else if($data['entity_type_id']==6003)
				{
					$entity_name = 'Zone';
					$entity_code = 'Z'.$entity_id;
				}
				else if($data['entity_type_id']==6006)
				{
					$entity_name = 'Open Zone';
					$entity_code = 'Oz'.$entity_id;
				}
				else if($data['entity_type_id']==6007)
				{
					$entity_name = 'Put Away Zone';
					$entity_code = 'Paz'.$entity_id;
				}
				else if($data['entity_type_id']==6004)
				{
					$entity_name= 'Rack';
					$entity_code = 'R'.$entity_id;
				}
				else 
				{
					$entity_name = 'Bin';
					$entity_code = 'B'.$entity_id;

				} 
				$entities = Entities::find($entity_id);
				$entities->entity_code = $entity_code;
				$entities->entity_name = $entity_name;
				$entities->save(); 
			}

			if(isset($data['entity_id']) && !empty($data['entity_id'])){
				$message = 'Updated Successfully';
				$dimension = Dimension::where('entity_id',$data['entity_id'])->first();
				$dimension->entity_id = $data['entity_id'];
				$entity_id = $data['entity_id'];
			}else{
				$message = $entity_name.' '.$entity_code.' is created successfully';
				$dimension = new Dimension;
				$dimension->entity_id = $entity_id;
			}
			$dimension->height = $data['height'];
			$dimension->width = $data['width'];
			$dimension->depth = $data['depth'];
			$dimension->length = $data['length'];
			$dimension->uom_id = $data['dimension_uom'];
			$dimension->area = $data['length'] * $data['width'];
			$dimension->save(); 

			return json_encode(array('status' => 1,'message' => $message,'entity_id' => $entity_id));
		}
		catch(exception $e){
			$entities = Entities::where('id',$entity_id)->delete();
			$dimension = Dimension::where('entity_id',$entity_id)->delete();
			return json_encode(array('status' => 0,'message' => 'Exception Occurred'));
		}
	}

	public function deleteEntity()
	{
		$data = Input::get();
		if(isset($data['entity_id']) && !empty($data['entity_id']))
		{   

			$entity = Entities::where('id',$data['entity_id'])->first();
			if($entity['is_assigned'] == 1){
				Eseal::where('entity_id',$data['entity_id'])->delete();
			}
			if($entity['entity_type_id'] == 6005){
				$bin = DB::table('wms_storage_bins')->where('entity_id',$data['entity_id'])->delete();
			}         

			$entity->delete();
			$dimension = Dimension::where('entity_id',$data['entity_id'])->first();
			$dimension->delete();
			$status = 1;
			$message = 'Entity Deleted Successfully.';
		}
		else
		{
			$status = 0;
			$message = 'Entity Id not passed.';
		}
		return json_encode(array('status'=> $status,'message'=>$message));
	}

	public function getEntityTypes()
	{
		$entitytypes = EntityType::all();
		return json_encode(array('status' =>1,'message' =>'Data retrieved successfully','Data'=> $entitytypes));
	}

	public function assignData()
	{
		$data = Input::get();
		if(!isset($data['product_id']) || !isset($data['package_id']) || !isset($data['entity_id'])){
			return json_encode(array('Status'=> 0,'Message'=>'Parameters Missing.'));
		}
		else{
			$entity = Entities::where('id',$data['entity_id'])->first();
			$eseal = Eseal::where('entity_id',$data['entity_id'])->first();
			if(!empty($eseal)){
				if($entity['entity_type_id'] == 6005){
					$bin = DB::table('wms_storage_bins')
					->where('entity_id',$data['entity_id'])
					->update(['pid'=>$data['product_id'],'pname'=>'']);
				}
			}
			else{
				$entity->is_assigned = 1;
				$entity->save();
				if($entity['entity_type_id'] == 6005){
					$bin = DB::table('wms_storage_bins')
					->where('entity_id',$data['entity_id'])
					->update(['pid'=>$data['product_id'],'pname'=>'','is_allocated'=> 1]);
				}
				$eseal = new Eseal;
			}
			$eseal->entity_id = $data['entity_id'];
			$eseal->product_id = $data['product_id'];
			$eseal->package_id = $data['package_id'];
			$eseal->save();

			return json_encode(array('Status'=> 1,'Message'=>'Product Successfully Assigned.'));
		}
	}

	public function deleteAssignedData()
	{
		$data = Input::get();
		if(!isset($data['entity_id']) || empty($data['entity_id'])){
			return json_encode(array('Status'=> 0,'Message'=>'Entity Id missing.'));	
		}
		$eseal = Eseal::where('entity_id',$data['entity_id'])->first();
		$eseal->delete();

		$entity = Entities::where('id',$data['entity_id'])->first();

		$entity->is_assigned = 0;
		$entity->save();

		if($entity['entity_type_id'] == 4){

			$bin = DB::table('wms_storage_bins')
			->where('entity_id',$data['entity_id'])
			->update(['pid'=>'','pname'=>'','is_allocated'=> 0]);  
		}
		return json_encode(array('Status'=> 1,'Message'=>'Product Successfully Un-assigned.'));
	}

	public function getProductList()
	{
		$name_id = DB::table('eav_attribute')->where(array('attribute_code'=>'name','entity_type_id'=>4))->pluck('attribute_id');
		$location_id= DB::table('eav_attribute')->where(array('attribute_code'=>'location','entity_type_id'=>4))->pluck('attribute_id');
		$products = DB::table('catalog_product_entity')
		->leftJoin('catalog_product_entity_varchar as vr','catalog_product_entity.entity_id', '=', 'vr.entity_id')
		->leftJoin('catalog_product_entity_varchar as vr1','catalog_product_entity.entity_id', '=', 'vr1.entity_id')
		->leftJoin('track_and_trace_user','track_and_trace_user.location_id','=','vr1.value')            
		->select('catalog_product_entity.entity_id as pid','vr.value as pname','track_and_trace_user.user_id as user_id')
		->where(array('vr.attribute_id'=>$name_id,'vr1.attribute_id'=>$location_id))
		->get();
		if(empty($products)){
			return json_encode(array('Status'=>0,'Message'=>'Data not Found.'));
		}
		return json_encode(array('Status'=>1,'Message'=>'Data Successfully Retrieved','Data'=> $products));
	}

	public function getStorageData()
	{
		$data = Input::get();
		$location_id = $data['location_id'];
		$entities =Entities::where(['location_id'=>$location_id,'entity_type_id'=>6005])->get();
		$binarr = array();
		$finalarr = array();
		try{
			foreach($entities as $entity){
				$bin = DB::table('wms_storage_bins')
				->select('entity_id','storage_bin_id','storage_bin_name','pid','pname','status','ware_id','floor_id','storage_capacity','holding_count','is_allocated')
				->where('entity_id',$entity['id'])
				->get();     	
				$binarr['entity_id'] = $bin[0]->entity_id;
				$binarr['storage_bin_id'] = $bin[0]->storage_bin_id;
				$binarr['storage_bin_name']= $bin[0]->storage_bin_name;
				$binarr['pid']=  $bin[0]->pid;
				$binarr['pname'] = $bin[0]->pname;
				$binarr['status'] = $bin[0]->status;
				$binarr['ware_id'] = $bin[0]->ware_id;
				$binarr['floor_id'] = $bin[0]->floor_id;
				$binarr['storage_capacity'] = $bin[0]->storage_capacity;
				$binarr['holding_count'] = $bin[0]->holding_count;
				$binarr['is_allocated'] = $bin[0]->is_allocated;  
				$finalarr[] = $binarr;
			}
			return json_encode(array('status'=> 1,'message'=>'Data Retrieved','data'=>$finalarr));
		}           
		catch(exception $e){
			return json_encode(array('status'=>0,'message'=>'exception occurred'));
		}
	}

	public function updateHoldingCount()
	{
		$data = Input::get();
		if(!isset($data['entity_id']) && !isset($data['holding_count'])){
			return json_encode(['status'=> 0,'message'=>'Parameters Missing.']);
		}
		$bin = Bins::where('entity_id',$data['entity_id'])->first();
		if(!empty($binarr)){
			$bin = DB::table('wms_storage_bins')
			->where('entity_id',$data['entity_id'])
			->update(['status'=>$data['status'],'holding_count'=> $data['holding_count'],'is_allocated'=>$data['is_allocated']]);

			$entity = Entities::where('id',$data['entity_id'])->first();
			if(!empty($entity)){
			$entity->is_assigned = $data['is_allocated'];
			$entity->save();
			return json_encode(['status'=> 1,'message'=>'Holding Count updated successfully.']);
			}
		}
		return json_encode(['status'=>0,'message'=>'In-valid Bin.']);
	}
			
	public function getPalletdata()
	{			     			           
		$location_id = $this->roleAccess->getLocIdByToken(Input::get('access_token'));
		$mfg_id = $this->roleAccess->getMfgIdByToken(Input::get('access_token'));
		$ware_id=Input::get('ware_id');
			$getArr = array();
			$finalgetArr = array();
			$pallets = DB::table('wms_pallet')
					   ->leftjoin('master_lookup as pml','wms_pallet.pallet_type_id','=','pml.value')
					   ->leftjoin('master_lookup as wml','wms_pallet.weightUOMId','=','wml.value')
					   ->leftjoin('master_lookup as dml','wms_pallet.dimensionUOMId','=','dml.value')
					   ->select('wms_pallet.*','pml.name as pallet_type_name','wml.name as weightuom',
						'dml.name as dimensionuom')
					   ->where('wms_pallet.org_id','=',$mfg_id)
					   ->where('wms_pallet.ware_id','=',$ware_id)
					   ->orderBy('id','desc')->get();
			$pallet_details=json_decode(json_encode($pallets),true);
		try{
				foreach($pallets as $value)
				{
				  $getArr['id'] = intval($value->id);
				  $getArr['pallet_id'] = $value->pallet_id;
				  $getArr['pallet_type_id'] = $value->pallet_type_name;
				  $getArr['weight'] = intval($value->weight);
				  $getArr['weightUOMId'] = $value->weightuom;
				  $getArr['height'] = intval($value->height);
				  $getArr['width'] = intval($value->width);
				  $getArr['length'] = intval($value->length);
				  $getArr['holding_capacity'] = intval($value->capacity);
				  $getArr['dimensionUOMId'] = $value->dimensionuom;
				  $finalgetArr[] = $getArr;
				}
			return json_encode(array('Status'=>1,'Message'=>'Data Retrieved.','data'=>$finalgetArr));
			}
		catch(exception $e){
			return json_encode(array('Status'=>0,'Message'=>'exception occurred'));
			}				    				    
	}
				
	public function createPallet()
	{
		$org_id = $this->roleAccess->getMfgIdByToken(Input::get('access_token'));
		try
		{
			DB::table('wms_pallet')->insert([
			   'pallet_id'=> Input::get('pallet_id'),
			   'pallet_type_id' => Input::get('pallet_type_id'),
			   'weight'=>Input::get('weight'),
			   'weightUOMId'=>Input::get('weightUOMId'),
			   'height' => Input::get('height'),
			   'width'=>Input::get('width'),
			   'length'=>Input::get('length'),
			   'dimensionUOMId'=>Input::get('dimensionUOMId'),
			   'ware_id'=>Input::get('ware_id'),
			   'org_id'=>$org_id
			 ]);
			return json_encode(['status'=>1,'message'=>'Pallet created Successfully.']);
		}
		catch(exception $e){
			return json_encode(array('status' => 0,'message' => 'Exception Occurred'));
		}        
	}
				
	public function updatePallet()
	{
		$data=Input::all();
		try
		{
			$pallet = DB::table('wms_pallet')->where('pallet_id', $data['pallet_id'])->get();			        
			if(!isset($data['pallet_type_id']))
			{
				$data['pallet_type_id']=$pallet[0]->pallet_type_id;
				//return $data['pallet_type_id'];
			}
			if(!isset($data['weightUOMId']))
			{
				$data['weightUOMId']=$pallet[0]->weightUOMId;
			}
			if(!isset($data['weight']))
			{
				$data['weight']=$pallet[0]->weight;
			}	
			if(!isset($data['height']))
			{
				$data['height']=$pallet[0]->height;
			}
			if(!isset($data['width']))
			{
				$data['width']=$pallet[0]->width;
			}
			if(!isset($data['dimensionUOMId']))
			{
				$data['dimensionUOMId']=$pallet[0]->dimensionUOMId;
			}
			if(!isset($data['length']))
			{
				$data['length']=$pallet[0]->length;
			}	
			if(!isset($data['ware_id']))
			{
				$data['ware_id']=$pallet[0]->ware_id;
			}			        			        				        				        
			DB::table('wms_pallet')
				->where('pallet_id', $data['pallet_id'])
				->update(array(
				  'pallet_type_id' => $data['pallet_type_id'],
				  'weightUOMId' => $data['weightUOMId'],
				  'weight'=>$data['weight'],
				  'dimensionUOMId' => $data['dimensionUOMId'],
				  'height' => $data['height'],
				  'width' => $data['width'],
				  'length' => $data['length'],
				  'ware_id'=>$data['ware_id']));

			return json_encode(['status'=>1,'message'=>'Pallet updated Successfully.']);   
		}
		catch(exception $e){
			return json_encode(array('status' => 0,'message' => 'Exception Occurred'));
		}          
	}	
				
	public function deletePallet()
	{
	   $exists=DB::Table('wms_pallet')->where('pallet_id', '=', Input::get('pallet_id'))->get();
	   if($exists){			        
			DB::Table('wms_pallet')->where('pallet_id', '=', Input::get('pallet_id'))->delete();
			return json_encode(['status'=>1,'message'=>'Pallet deleted Successfully.']); 
		}else{
			return json_encode(array('status' => 0,'message' => 'Exception Occurred'));
		}              
	}	
	public function mapPallet()
	{
	   $datainput = Input::get('JsonData');
	   $transitionId = Input::get('transitionId');
	   $datainput = json_decode($datainput,true);
	   $mfg_id = $this->roleAccess->getMfgIdByToken(Input::get('access_token'));
	   
	   foreach($datainput as $key=>$value)
	   { 
			try
			{	
				$pallet_total_capacity = DB::Table('wms_pallet')->where('pallet_id',$value['parent'])->pluck('capacity');
				$pallet_weight = DB::Table('eseal_'.$mfg_id)->where(array('primary_id'=>$value['parent'], 'level_id'=>8))->pluck('pkg_qty');
				
				$pallet_present_capacity = $pallet_weight+$value['pallet_weight'];
				if($pallet_total_capacity>=$pallet_present_capacity)
				{
					$transitionTime = Input::get('transitionTime');
					$request = Request::create('scoapi/bindAndMap', 'POST', array('module_id'=> Input::get('module_id'),'access_token'=>Input::get('access_token'),'isPallet'=>1,'parent'=>$value['parent'],'child_list'=>$value['child_list'],'pid'=>intval($value['pid']),'transitionTime'=>$transitionTime,'attributes'=>$value['attributes'],'pallet_weight'=>$value['pallet_weight'],'transitionId'=>$transitionId));
					$originalInput = Request::input();
					Request::replace($request->input());
					$response = Route::dispatch($request)->getContent();
					$response = json_decode($response,true);
					$status = $response['Status'];
					$message = $response['Message']; 
				}
				else
				{
					$status=0;
					$message = 'Products capacity exceeds the pallet capacity.';
				}        		
			}
			catch(Exception $e)
			{
			   $status = 0;
			   $message = $e->getMessage();
			   $response ='';
			}
	   }
	   return json_encode(['Status'=>$status,'Message'=>$message]);		
	}
	

	public function movePallet()
	{
		//$pallet_id = Input::get('pallet_id');
		$placed_location = Input::get('placed_location');
		//$allocated_status = Input::get('status');
		$allocated_date = Input::get('allocated_date');
		$pallet_id = Input::get('pallet_id');
		$location_id = $this->roleAccess->getLocIdByToken(Input::get('access_token'));
		$warehouse_id = DB::table('wms_entities')->where(array('location_id'=>intval($location_id), 'entity_type_id'=>6001))->pluck('id');
		$mfg_id = $this->roleAccess->getMfgIdByToken(Input::get('access_token'));
		$status='';
		$message='';
		//$transId = Transaction::where(['name'=>'Pallet Placement','manufacturer_id'=>$mfg_id])->pluck('id');
		$transitionId = Input::get('transitionId');
		try
		{
			Log::info(__FUNCTION__.' : '.print_r(Input::get(),true));
			$pallet_weight = DB::Table('eseal_'.$mfg_id)->select(DB::raw('sum(pkg_qty) as pkg_qty, bin_location'))->where(array('bin_location'=>$placed_location,'ware_id'=>$warehouse_id))->get();
			$bin_capacity = DB::table('wms_entities')->where(array('org_id'=>$mfg_id, 'ware_id'=>$warehouse_id,'entity_location'=>$placed_location))->pluck('capacity');

			$pallet_exists= DB::Table('eseal_'.$mfg_id)->select('eseal_'.$mfg_id.'.eseal_id')->where(array('bin_location'=>$placed_location,'parent_id'=>$pallet_id,'ware_id'=>$warehouse_id))->get();
			//echo "<pre/>";print_r($pallet_exists);exit;
			//if(empty($pallet_exists))
			//{
				if($bin_capacity>=$pallet_weight[0]->pkg_qty)
				{
					//return $transitionId;
					DB::beginTransaction();
					$request = Request::create('scoapi/UpdateTracking', 'POST', array('module_id'=>Input::get('module_id'),'access_token'=>Input::get('access_token'),'codes'=>$pallet_id,'srcLocationId'=>$location_id,'destLocationId'=>0,'transitionTime'=>$allocated_date,'transitionId'=>Input::get('transitionId'),'internalTransfer'=>0));
						$originalInput = Request::input();//backup original input
						Request::replace($request->input());
						Log::info($request->input());
						$response = Route::dispatch($request)->getContent();
						$response = json_decode($response,true);
						//echo "<pre/>";print_r($response);exit;
					if($response['Status']==1)
					{
						$check_pallet = DB::table('eseal_'.$mfg_id)->where(array('primary_id'=>$pallet_id, 'level_id'=>8))->pluck('eseal_id');
						$queries = DB::getQueryLog();
						//return end($queries);
						//return $check_pallet;
						if(!empty($check_pallet))
						{
							try
							{    
								$movedPallet = DB::Table('eseal_'.$mfg_id)
									   ->where(array('parent_id'=>$pallet_id,'level_id'=>0))
									   ->orWhere(array('primary_id'=>$pallet_id,'level_id'=>8))
									   ->update(array('bin_location' => $placed_location,'ware_id'=>$warehouse_id));
								$status = 1;
								$message = "Successfully Moved the pallet.";	
								DB::commit();											
							}
							catch(Exception $e)
							{
								DB::rollback();	
								return json_encode(['Status'=>0,'Message'=> 'Error during moving pallet data.']);
							}               
						}
						else
						{   
							DB::rollback();	                
							return json_encode(['Status'=>0,'Message'=> 'Invalid Pallet Data.']);
						}
					}					
				}
				else
				{
					DB::rollback();	                
					return json_encode(['Status'=>0,'Message'=> 'Pallets capacity exceeds the bin capacity.']);
				}
			//}
			//else
			//{
					
			//}
		}
		catch(Exception $e)
		{
			return json_encode(['Status'=>0,'Message'=> $e->getMessage()]);
			DB::rollback();	
		}       	
		return json_encode(['Status'=>$status,'Message'=>$message]);
	}	
	public function getAllocatedPalletData()
	{
		$warehouse_id = Input::get('warehouse_id');
		$startTime = $this->getTime();
		$result ='';
		$status = 1;
		$message ='';
		$args = Input::get();
		try
		{
			DB::beginTransaction();
			$location_id = $this->roleAccess->getLocIdByToken(Input::get('access_token'));
			$mfg_id = $this->roleAccess->getMfgIdByToken(Input::get('access_token'));

			$getPalletData = DB::table('pallete_data')->where(array('warehouse_id'=>$warehouse_id,'mfg_id'=>$mfg_id))->get();
			$tbl_name = 'eseal_'.$mfg_id;
	
			$i =0;
			foreach ($getPalletData as $pallData) {

					$getPalletData[$i]->prod_data = DB::table($tbl_name)
								->Join('pallete_data as pData','pData.id','=',$tbl_name.'.pallet_data_flag')
								->Join('products',$tbl_name.'.pid','=','products.product_id')
								->select($tbl_name.'.pid as prod_id','products.name')
								->get();


					$i++;

			}
			if(!empty($getPalletData))
			{
				$result = $getPalletData;
				$message = 'Retrieved Successfully';
			}
			else
			{
				$status = 0;
				$result = $getPalletData;
				$message = 'No Data Retrieved';
			}	                
			
		}
		catch(Exception $e)
		{
		   $status = 0;
		   $message = $e->getMessage();
		}
		$endTime = $this->getTime();
		
		return json_encode(['Status'=>$status,'Message'=>$message,'Data'=>$result]);
	}
	public function getStorageBindata()
	{			     			           
		$location_id = $this->roleAccess->getLocIdByToken(Input::get('access_token'));
		$mfg_id = $this->roleAccess->getMfgIdByToken(Input::get('access_token'));
		$ware_id = DB::table('wms_entities')->where(array('location_id'=>intval($location_id), 'entity_type_id'=>6001,'org_id'=>$mfg_id))->pluck('id');
		//$ware_id=Input::get('ware_id');
			$getArr = array();
			$finalgetArr = array();
			 $storagebins = DB::table('wms_storage_bins')
										   ->leftJoin('wms_eseal','wms_eseal.entity_id','=','wms_storage_bins.entity_id')
										   ->leftJoin('products','products.product_id','=','wms_eseal.product_id')
										   ->select('wms_storage_bins.*','wms_eseal.product_id','products.name')
										   ->where(array('wms_storage_bins.ware_id'=>$ware_id))
										   ->orderBy('wms_storage_bins.entity_id','desc')->get();
			if(empty($storagebins))
				return 	json_encode(array('Status'=>0,'Message'=>'no-data','data'=>$finalgetArr));

			$storagebins_details=json_decode(json_encode($storagebins),true);
		try{
				foreach($storagebins as $value)
				{
				  $getArr['EntityId'] = intval($value->entity_id);
				  $getArr['StorageBinId'] = isset($value->storage_bin_id)?$value->storage_bin_id:'';
				  $getArr['StorageBinName'] = isset($value->storage_bin_name)?$value->storage_bin_name:'';
				  $getArr['ProductId'] = intval($value->product_id);
				  $getArr['ProductName'] = isset($value->name)?$value->name:'';
				  //$getArr['Status'] = $value->status;
				  $getArr['WarehouseId'] = intval($value->ware_id);
				  $getArr['FloorId'] = intval($value->floor_id);
				  $getArr['StorageUnitCapacity'] = intval($value->storage_capacity);
				  $getArr['IsAllocated'] = intval($value->is_allocated);
				  $finalgetArr[] = $getArr;
				}
			return json_encode(array('Status'=>1,'Message'=>'Data Retrieved.','data'=>$finalgetArr));
			}
		catch(exception $e){
			return json_encode(array('Status'=>0,'Message'=>'exception occurred','data'=>$finalgetArr));
			}				    				    
	}


	public function apiTest(){
		return json_encode(['Status'=>1,'Message'=>'Call Successfull.']);
	} 	

	private function getRand(){
			$length = 10;
			$characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
			$charactersLength = strlen($characters);
			$randomString = '';
			for ($i = 0; $i < $length; $i++) {
				$randomString .= $characters[rand(0, $charactersLength - 1)];
			}
		   return $randomString;   
	}


	public function getAppVersions(){
		try{
			Log::info(__FUNCTION__.' : '.print_r(Input::get(),true));	
			$data = array();
			$app_id = trim(Input::get('app_id'));
			$isPrevious = Input::get('isPrevious');

			if(empty($app_id))
				throw new Exception('App Id is passed empty');

			$data = DB::table('app_versions')
						->where(['app_id'=>$app_id])
						->orderBy('release_date','desc');
                 if($isPrevious == true)
						$data->take(2);
				 else
				        $data->take(1);		   

		    $data = $data->get(['db_update_needed','config_reset','release_date','download_link','latest_version']);	

			

			if(empty($data)){
				throw new Exception('Data not found for given App Id');
			}

            if($isPrevious== true && count($data) == 2)
            	array_shift($data);


			$status =1;
			$message = 'Data successfully retrieved';
		}
		catch(Exception $e){
			$status =0 ;
			$message = $e->getMessage();
		}
		Log::info(['Status'=>$status,'Message'=>$message,'Data'=>$data]);
		return json_encode(['Status'=>$status,'Message'=>$message,'Data'=>$data]);
	}

	public function saveTransaction(){
		try{
			Log::info(__FUNCTION__.' : '.print_r(Input::get(),true));
			$tp_id = trim(Input::get('tp_id'));
			$delivery_no = trim(Input::get('delivery_no'));
			$json = trim(Input::get('data'));
			$transitionTime = trim(Input::get('transitionTime'));
			$location_id = $this->roleAccess->getLocIdByToken(Input::get('access_token'));
			$transitionType = trim(Input::get('transitionType'));

			if(empty($tp_id) || empty($json) || empty($transitionTime) || empty($transitionType))
				throw new Exception('Parameters Missing');

			$query = DB::table('partial_transactions')
			->where('tp_id',$tp_id)
			->count();

			if($query){
				$tp = DB::table('partial_transactions')
				->where('tp_id',$tp_id)
				->update(['delivery_no'=>$delivery_no,'data'=>$json,'date_time'=>$transitionTime,'location_id'=>$location_id,'transaction_type'=>$transitionType]);
				$message = 'Data updated successfully';
			}   
			else{
				$tp = DB::table('partial_transactions')
				->insert(['tp_id'=>$tp_id,'delivery_no'=>$delivery_no,'data'=>$json,'date_time'=>$transitionTime,'location_id'=>$location_id,'transaction_type'=>$transitionType]);
				$message ='Data saved successfully';
			}                

			$status =1;

		}
		catch(Exception $e){
			$status =0;
			$message = $e->getMessage();
		}
		return json_encode(['Status'=>$status,'Message'=>$message]);
	}


	public function loadTransactions(){
		try{
			Log::info(__FUNCTION__.' : '.print_r(Input::get(),true));
			$tp = array();
			$tp_id = trim(Input::get('tp_id'));
			$delivery_no = trim(Input::get('delivery_no'));
			$location_id = $this->roleAccess->getLocIdByToken(Input::get('access_token'));
			
			if(empty($tp_id) && empty($delivery_no)){
			  $tp = DB::table('partial_transactions')->where('location_id',$location_id)->get(['tp_id','delivery_no','data','date_time','location_id','transaction_type']);
			}

		  else{
			$tp = DB::table('partial_transactions')
			->where('tp_id',$tp_id)
			->orWhere('delivery_no',$delivery_no)
			->get(['tp_id','delivery_no','data','date_time','location_id','transaction_type']);
		   }
			if(empty($tp)){
				throw new Exception('Data not found for given inputs');
			}
			$status =1;
			$message =  'Data retrieved successfully';

		}
		catch(Exception $e){
			$status =0;
			$message = $e->getMessage();
		}
		return json_encode(['Status'=>$status,'Message'=>$message,'Data'=>$tp]);
	}


	public function deleteTransaction(){
		try{
			Log::info(__FUNCTION__.' : '.print_r(Input::get(),true));
			$tp = array();
			$tp_id = trim(Input::get('tp_id'));
			$delivery_no = trim(Input::get('delivery_no'));
			
			if(empty($tp_id) && empty($delivery_no))
				throw new Exception('Parameters Missing');
			
			$cnt = DB::table('partial_transactions')
			->where('tp_id',$tp_id)
			->orWhere('delivery_no',$delivery_no)
			->count();

			if(!$cnt){
				throw new Exception('Data not found to delete');
			}

			
			$tp = DB::table('partial_transactions')
			->where('tp_id',$tp_id)
			->orWhere('delivery_no',$delivery_no)
			->delete();
			$status =1;
			$message =  'Data deleted successfully';

		}
		catch(Exception $e){
			$status =0;
			$message = $e->getMessage();
		}
		return json_encode(['Status'=>$status,'Message'=>$message]);
	}

	public function getTransactionHistory()
	{
	   $module_id = Input::get('module_id');
	   $access_token = Input::get('access_token');
	   $location_id = Input::get('location_id');
	   $fromDate = Input::get('fromDate');
	   $toDate = Input::get('toDate');
	   $pid = Input::get('pid');
	   $transition_id = Input::get('transition_id');
	   $mfgId = $this->roleAccess->getMfgIdByToken(Input::get('access_token'));	   
	   $startTime = $this->getTime(); 

		try
		{
			$status = 0;
			$message = 'Data not found.';
			$esealTable = 'eseal_'.$mfgId;
			$date = date('Y-m-d H:i:s');

			if(empty($fromDate) || empty($toDate)  || empty($transition_id))
				throw new Exception('Some of the parameters are missing');	

			$sql = 'select th.update_time as tdate, th.transition_id as transition_id,th.src_loc_id as location_id,e.pid as product_id,(select material_code from products where product_id=e.pid) as material_code,(select name from products where product_id=e.pid) as product_name,e.level_id, count(td.code) as pcount   
				from track_history th inner join track_details td on th.track_id=td.track_id
				inner join eseal_2 e on e.primary_id=td.code where  th.src_loc_id="'.$location_id.'" and th.update_time >= "'.$fromDate.'"  and th.update_time <= "'.$toDate.'" ';
			if($pid!='')
			$sql .= '  and e.pid = '.$pid;

			if($transition_id!='')
			$sql .= '  and th.transition_id = '.$transition_id;

			$sql.= ' group by th.update_time, th.src_loc_id,e.pid,e.level_id';
			//print_r($sql);exit;
			try
			{
				$result = DB::select($sql);  
			}catch(PDOException $e)
			{
				Log::info($e->getMessage());
				throw new Exception('SQlError while fetching data');
			}
			if(count($result))
			{
				$status = 1;
				$message = 'Data found';
			}
		}
		catch(Exception $e)
		{
		   $status = 0;
		   $message = $e->getMessage();
		   $result ='';
		}
	   return json_encode(['Status'=>$status,'Message'=>$message,'Data'=>$result]);		
	}
		
	public function getAllGDSorders()
	{
		try
		{
			$data = Input::all();
			$manufacturerId = $this->roleAccess->getMfgIdByToken($data['access_token']);
			$data['manufacturer_id'] = $manufacturerId;
			$response = $this->_apiRepo->getGDSorderData($data);
			if(empty($response))
			{
				$status = 0;
				$message = 'No Data found';
				$result = array();
			}else{
				$status = 1;
				$message = 'Sucessfully retrived';
				$result = $response;
			}
//            echo "<pre>";print_r($result);die;
			return json_encode(['Status'=>$status,'Message'=>$message,'Data'=>$result]);		
		} catch (Exception $ex)
		{            
			print_R($ex->getTraceAsString());die;
			Log::info($ex->getTraceAsString());
			return $ex->getMessage();
		}
	}
		
	public function getInvoiceDetails()
	{
		try
		{
			$data = Input::all();
			$response = $this->_apiRepo->getInvoiceDetails($data);
			if(empty($response))
			{
				$status = 0;
				$message = 'No Data found';
				$result = array();
			}else{
				$status = 1;
				$message = 'Sucessfully retrived';
				$result = $response;
			}
//            echo "<pre>";print_r($result);die;
			return json_encode(['Status'=>$status,'Message'=>$message,'Data'=>$result]);		
		} catch (Exception $ex)
		{            
			print_R($ex->getTraceAsString());die;
			Log::info($ex->getTraceAsString());
			return $ex->getMessage();
		}
	}

	public function updateGDSorders()
	{
		try
		{
			$data = Input::all();
			$mfgID = $this->roleAccess->getMfgIdByToken($data['access_token']);
			$data['mfgID'] = $mfgID;
//            $data['location_id'] = $this->roleAccess->getLocIdByToken($data['access_token']);
			$response = $this->_apiRepo->updateOrderDetails($data);
			if(empty($response))
			{
				$status = 0;
				$message = 'No Data found';
				$result = array();
			}else{
				$status = 1;
				$message = $response;
				$result = array();
			}
//            echo "<pre>";print_r($result);die;
			return json_encode(['Status'=>$status,'Message'=>$message,'Data'=>$result]);		
		} catch (Exception $ex)
		{
//            print_R($ex->getTraceAsString());die;
			Log::info($ex->getTraceAsString());
			return $ex->getTraceAsString();
		}
	}
	
	public function getAllUsers()
	{
		try
		{
			$usersCollection = array();
			//$userId = Input::get('access_token');
			$userInfo = DB::table('users_token')
					->where(['access_token' => Input::get('access_token'), 'module_id' => Input::get('module_id')])
					->first(['user_id']);
			if(!property_exists($userInfo, 'user_id') || $userInfo->user_id == '')
			{
				return json_encode(['Status'=>0,'Message'=>'User id not found','Data'=>array()]);
			}
			$locationsIds = DB::table('locations')
					->leftJoin('users', 'users.location_id', '=', 'locations.parent_location_id')
					->where('users.user_id', $userInfo->user_id)
					->first([DB::raw('group_concat(locations.location_id) as location_ids')]);
			//Log::info('user_id');
			//Log::info($userInfo->user_id);
			//Log::info('location_ids');
			//Log::info($locationsIds->location_ids);
			if(!empty($locationsIds) && $locationsIds->location_ids != '')
			{
				$usersCollection = DB::table('users')
						->leftJoin('locations', 'locations.location_id', '=', 'users.location_id')
						->whereIn('locations.location_id', explode(',', $locationsIds->location_ids))
						->get(['users.user_id', 'users.email', 'locations.location_name', 'locations.longitude', 'locations.latitude', 'locations.location_details']);
				if(empty($usersCollection))
				{
					$status = 0;
					$message = 'Users not associated';
				}else{
					$status = 1;
					$message = 'Users found';
				}
				
			}else{
				$status = 0;
				$message = 'No Users found';
			}
			return json_encode(['Status'=>$status,'Message'=>$message,'Data'=>$usersCollection]);
		} catch (\ErrorException $ex) {
			Log::info($ex->getMessage());
		}
	}

	public function updateFieldForceDetails()
	{
		$status  = 1;
		$message = 'Data Updated Succesfully';
		$startTime = $this->getTime(); 
		try
		{  
			$module_id = Input::get('module_id');
			$access_token = Input::get('access_token');
			$ffid = Input::get('ff_user_id');
			$latitude = Input::get('latitude');
			$longitude = Input::get('longitude');

			Log::info(__FUNCTION__.' : '.print_r(Input::get(),true));
			DB::beginTransaction();

			$ffid = DB::table('users_token')->where('access_token',$access_token)->pluck('user_id');
			$location_id = DB::Table('users')->where('user_id',$ffid)->pluck('location_id');
			$location_details = $this->getaddress($latitude,$longitude);
			//echo "<pre/>";print_r($location_details);exit;
			$updateDetails = DB::Table('locations')->where('location_id',$location_id)->update(array('latitude'=>$latitude,'longitude'=>$longitude,'location_details'=>$location_details));
			
			DB::commit();
			$status  = 1;
			$message = 'Data Updated Succesfully';
		} 
		catch(Exception $e)
		{
			$status =0;
			DB::rollback();
			throw new Exception('SQlError while updating.');
		}
		$endTime = $this->getTime();
		Log::info(__FUNCTION__.' Finishes execution in '.($endTime-$startTime));
		return json_encode(['Status'=>$status,'Message'=>$message]);
	}

	public function getaddress($lat,$lng)
	{
		$url = 'http://maps.googleapis.com/maps/api/geocode/json?latlng='.trim($lat).','.trim($lng).'&sensor=false';
		$json = @file_get_contents($url);
		$data=json_decode($json);
		$status = $data->status;
		if($status=="OK")
			return $data->results[0]->formatted_address;
		else
		return false;
	}
	/* 
		This function is used for Sample Stock Out functionality
		Params are json format eseal ids, qty per pack

	*/
	public function sampleStockOut()
 {
  
  $datainput = Input::get('JsonData');
	 $transitionId = Input::get('transitionId');
	 $transitionTime = Input::get('transitionTime');
	 $datainput = json_decode($datainput,true);
	 $location_id = $this->roleAccess->getLocIdByToken(Input::get('access_token'));
	 $warehouse_id = DB::table('wms_entities')->where(array('location_id'=>intval($location_id), 'entity_type_id'=>6001))->pluck('id');
	 $mfg_id = $this->roleAccess->getMfgIdByToken(Input::get('access_token'));
	
	foreach($datainput as $key=>$value)
	{ 
   $eseal_id = $value['child_list']; 
   $attributes = json_decode($value['attributes']);
   /*echo '<pre/>';print_r($attributes);exit;*/
   try
   { 
	$attribute_map_id = DB::Table('eseal_'.$mfg_id)->where(array('primary_id'=>$eseal_id))->pluck('attribute_map_id'); 
	if($attribute_map_id)
	{
	 $reqData = DB::Table('attributes')->leftJoin('attribute_mapping','attribute_mapping.attribute_id','=','attributes.attribute_id')->where(array('attributes.attribute_code'=>'qtyperpack','attribute_map_id'=>$attribute_map_id))->select('attributes.attribute_id','attribute_mapping.value')->get();
	 
	 if(!empty($reqData))
	 {
	  DB::beginTransaction();
	  //echo '<pre/>';print_r($reqData[0]->value);exit;
	  $newValue = $reqData[0]->value - $attributes->qtyperpack;
	  $attribute_id = $reqData[0]->attribute_id;
	  $changed = DB::Table('attribute_mapping')->where(array('attribute_id'=>$attribute_id,'attribute_map_id'=>$attribute_map_id))->update(array('value'=>$newValue));

	  $request = Request::create('scoapi/UpdateTracking', 'POST', array('module_id'=>Input::get('module_id'),'access_token'=>Input::get('access_token'),'codes'=>$eseal_id,'srcLocationId'=>$location_id,'destLocationId'=>0,'transitionTime'=>$transitionTime,'transitionId'=>Input::get('transitionId'),'internalTransfer'=>0));
	  $originalInput = Request::input();//backup original input
	  Request::replace($request->input());
	  //Log::info($request->input());
	  $response = Route::dispatch($request)->getContent();
	  $response = json_decode($response,true);
	  //echo "<pre/>";print_r($response);exit;
	  if($response['Status']==1)
	  {
	   $pallet_id = DB::Table('eseal_'.$mfg_id)->where(array('primary_id'=>$eseal_id))->pluck('parent_id');

	   if(!empty($pallet_id))
	   {
		
		$pres_pallet_weight = DB::Table('eseal_'.$mfg_id)->where(array('primary_id'=>$pallet_id, 'level_id'=>8))->pluck('pkg_qty');
		$up_pallet_weight = $pres_pallet_weight-$attributes->qtyperpack;

		DB::table('eseal_'.$mfg_id)->where(array('primary_id'=>$pallet_id,'level_id'=>8))->update(['pkg_qty'=>$up_pallet_weight]); 
		$queries = DB::getQueryLog(); 
	   }
	   $status = 1;
		  $message = 'Succeessfully updated the products weight';
		  DB::commit();
	  }
	 }     
	}    
   }
   catch(Exception $e)
   {
	  $status = 0;
	  $message = $e->getMessage();
	  $response ='';
	  DB::rollback();
   }
	}
	return json_encode(['Status'=>$status,'Message'=>$message]);  
 }

   public function deactivateEseals(){
	try{
		Log::info(__FUNCTION__.' : '.print_r(Input::get(),true));	
		$status =1;
		$arr = array();
		$result = array();
		$mfg_id = $this->roleAccess->getMfgIdByToken(Input::get('access_token'));
		$eseals = trim(Input::get('eseals'));
		if(empty($eseals))
			throw new Exception('Eseals Ids not passed');
		$esealArray = explode(',',$eseals);
		$existEsealArray = DB::table('eseal_'.$mfg_id)->whereIn('primary_id',$esealArray)->get(['primary_id']);
		foreach($existEsealArray as $es){
			$arr[] = $es->primary_id;        	
		}
		DB::table('eseal_'.$mfg_id)->whereIn('primary_id',$arr)->update(['is_redeemed'=>1]);
		if(empty($arr)){
			$arr = $esealArray;
			throw new Exception('All the codes are in-valid');
		}

		$arr = implode(',',array_diff($esealArray,$arr));
		$message = 'Coupons redeemed successfully';
	}
	catch(Exception $e){
		$arr = implode(',',$arr);
		$status = 0;
		$message = $e->getMessage();
		Log::info($message);
	}
	return json_encode(['Status'=>$status,'Message'=>$message,'invalid'=>$arr]);
   }

	 public function paperTransfer(){
		try{
			Log::info(__FUNCTION__.' : '.print_r(Input::get(),true));	
			$from_location = trim(Input::get('from_location'));
			$to_location = trim(Input::get('to_location'));
			$transitionTime = Input::get('transition_time');
			$transitionId = Input::get('transition_id');
			$tp_id = trim(Input::get('tp_id'));
			$tpDataMapping = trim(Input::get('tpDataMapping'));
			$mfg_id = $this->roleAccess->getMfgIdByToken(Input::get('access_token'));
			$esealTable = 'eseal_'.$mfg_id;
			$esealBankTable = 'eseal_bank_'.$mfg_id;
			$pdfContent = '';
			$pdfFileName = '';
			$status =1;


			if(empty($tp_id) || empty($from_location) || empty($to_location) || empty($transitionId) || empty($transitionTime))
				throw new Exception('Parameters Missing');

			$level_ids = DB::table('tp_data')->where('tp_id',$tp_id)->lists('level_ids');
			if(empty($level_ids))
				throw new Exception('In-valid Tp');

			$level_ids = implode(',',$level_ids);
		  DB::beginTransaction();
		  $request = Request::create('scoapi/ReceiveByTp', 'POST', array('module_id'=> Input::get('module_id'),'access_token'=>Input::get('access_token'),'tp'=>$tp_id,'location_id'=>$from_location,'transition_time'=>$transitionTime,'transition_id'=>$transitionId));
		  $originalInput = Request::input();//backup original input
		  Request::replace($request->input());
		  $result = Route::dispatch($request)->getContent();//invoke API
		  $result = json_decode($result,true);
		  if($result['Status'] == 1){


		  $tp_id = DB::table($esealBankTable)->where(['used_status'=>0,'download_status'=>0,'issue_status'=>0])->pluck('id');
		  if(!$tp_id)
			throw new Exception('TP Id not downloaded');
		  
		  DB::table($esealBankTable)->where('id',$tp_id)->update(Array(
									'download_status'=>1									
								 ));	

		  $transitionId = DB::table('transaction_master')->where(['manufacturer_id'=>$mfg_id,'name'=>'Stock Transfer'])->pluck('id');

				$request = Request::create('scoapi/SyncStockOut', 'POST', array('module_id'=>Input::get('module_id'),'access_token'=>Input::get('access_token'),'ids'=>$level_ids,'codes'=>$tp_id,'srcLocationId'=>$from_location,'destLocationId'=>$to_location,'transitionTime'=>$transitionTime,'transitionId'=>$transitionId,'tpDataMapping'=>$tpDataMapping,'pdfContent'=>$pdfContent,'pdfFileName'=>$pdfFileName));
				$originalInput = Request::input();//backup original input
				Request::replace($request->input());
				$response = Route::dispatch($request)->getContent();
				$response = json_decode($response,true);
				if($response['Status'] == 1){
					 
					 DB::table($esealBankTable)->where('id',$tp_id)->update(Array(
									'used_status'=>1,
									'level'=>9,
									'location_id' => $from_location
								 ));	

					 $result = json_decode($this->generatePdfForTp($mfg_id,$tp_id,$level_ids,$tp_id.' pdf for tp'),true);
					 if($result['Status'] == 1){
					 $pdfContent = $result['tp_pdf'];
					 $message = 'Paper Transfer Successfull';
					 DB::commit();
				   }
				   else{
					throw new Exception($result['Message']);
				   }
				}
				else{
					throw new Exception($response['Message']);
				} 
		  }
		  else{
			throw new Exception($result['Message']);
		  }
		}
		catch(Exception $e){
			DB::rollback();
			DB::table('eseal_bank_'.$mfg_id)->where('id',$tp_id)->update(['used_status'=>0,'location_id'=>0,'download_status'=>0,'level'=>0]);
			$status = 0;
			$tp_id ='';
			$message = $e->getMessage();
		}
		Log::info(['Status'=>$status,'Message'=>$message,'TpID'=>$tp_id]);
		return json_encode(['Status'=>$status,'Message'=>$message,'tp_id'=>$tp_id,'tp_pdf'=>$pdfContent]);
	 }   

	public function generatePdfForTp($mfgId,$codes,$ids,$pdfFileName){

try{
		Log::info(__FUNCTION__.' : '.print_r(Input::get(),true));
		$status =1;
		$pdfContent ='';
		$mfg_name = DB::table('eseal_customer')->where('customer_id',$mfgId)->pluck('brand_name');
		$tot = array();
		$tp = $codes;
		$ids1 = explode(',',$ids);//DB::table('tp_data')->where('tp_id',$tp)->lists('level_ids');
		//Log::info('EXPLODED:');
		//Log::info($ids1);
		$batch_no = DB::table('eseal_'.$mfgId)
						->whereIn('primary_id',$ids1)
						->distinct()
						->get(['batch_no']);
        //Log::info($batch_no);
		 foreach($batch_no as $batch){
		   //Log::info($batch);

			$pack = DB::table('eseal_'.$mfgId)->whereIn('parent_id',$ids1)->groupBy('parent_id')->take(1)->get([DB::raw('count(distinct(primary_id)) as cnt')]);
		//	Log::info('pack');
		//	Log::info($pack);
            
			$qty = DB::table('eseal_'.$mfgId)
						   ->where('batch_no',$batch->batch_no)
						   ->where(function($query) use($ids1){
							  $query->whereIn('parent_id', $ids1)
									->orWhereIn('primary_id',$ids1);
							}
							)
						   ->get([DB::raw('count(distinct(primary_id)) as cnt')]);
		//	Log::info('qty');
		//	Log::info($qty);
			$set = DB::table('eseal_'.$mfgId.' as es')
							->join('products as pr','pr.product_id','=','es.pid')
							->where('batch_no',$batch->batch_no)
							->whereIn('primary_id',$ids1)
							->groupBy('es.batch_no')
							->select([DB::raw('group_concat(primary_id) as id'),'es.batch_no','pr.name','pr.mrp'])
							->get();
		//	Log::info('set');
		//	Log::info($set);

			$set[0]->qty = $qty[0]->cnt;
			  if($pack){
			  $set[0]->pack = $pack[0]->cnt; 
			  }
			  else{
				$set[0]->pack = 0; 
			  }
			array_push($tot,$set);        
					}

		$th = DB::table('track_history as th')
				   ->join('locations as ls','ls.location_id','=','th.src_loc_id')
				   ->join('locations as ls1','ls1.location_id','=','th.dest_loc_id')
				   ->join('transaction_master as tr','tr.id','=','th.transition_id')
				   ->where('tp_id',$tp)
				   ->get(['ls.location_name as src','ls1.location_name as dest','ls.location_address as src_name','ls1.location_address as dest_name','th.tp_id','tr.name','th.update_time']);

		  if(!empty($th))
		  {
			$view = View::make('pdf', ['manufacturer' =>$mfg_name,'tp'=>$th[0]->tp_id,'status'=>$th[0]->name,'datetime'=>$th[0]->update_time,'src_name'=>$th[0]->src_name,'dest_name'=>$th[0]->dest_name,'src'=>$th[0]->src,'dest'=>$th[0]->dest,'tot'=>$tot]);
			
			$data = (string) $view;
			
			$pdfContent =  base64_encode($data);
		}
		  DB::table($this->tpPDFTable)->insert(Array(
			'tp_id'=>$codes, 'pdf_content'=>$pdfContent,'pdf_file'=>$pdfFileName
			));
		  $message = 'Tp PDf created and stored successfully';
	}
	catch(Exception $e){
		$status =0;
		$message = $e->getMessage();
	  }
	  return json_encode(['Status'=>$status,'Message'=>$message,'tp_pdf'=>$pdfContent]);
	}


public function getPdfDetailsForTp(){

try{
		Log::info(__FUNCTION__.' : '.print_r(Input::get(),true));
		$status =1;
		$pdfContent ='';
		$mfgId = $this->roleAccess->getMfgIdByToken(Input::get('access_token'));
		$mfg_name = DB::table('eseal_customer')->where('customer_id',$mfgId)->pluck('brand_name');
		$tot = array();
		$qty =0;
		$pack = 0;
                $set1 = array();	
         

		$tp = trim(Input::get('tp'));
		
        if(empty($tp))
        	throw new Exception('Parameters missing.');


		$tpAttributes = DB::table($this->TPAttributeMappingTable)->where('tp_id',$tp)->get([DB::raw('attribute_name as Label'),DB::raw('value as Value')]);



		$ids1 = DB::table('tp_data')->where('tp_id',$tp)->lists('level_ids');
		//Log::info('EXPLODED:');
		//Log::info($ids1);

		if(empty($ids1))
			throw new Exception('TP not valid');

      

$pids = DB::table('eseal_'.$mfgId.' as es')
                        ->join('products as pr','pr.product_id','=','es.pid')
		                ->where(['level_id'=>0,'product_type_id'=>8003])
						->where(function($query) use($ids1){
							  $query->whereIn('parent_id', $ids1)
									->orWhereIn('primary_id',$ids1);
							}
							)
						->distinct()
						->lists('pid');

		 foreach($pids as $pid){
		 
$set1 =  array();
$pack = DB::table('eseal_'.$mfgId.' as es')
                      ->join('products as pr','pr.product_id','=','es.pid')
			          ->where(function($query) use($ids1){
							  $query->whereIn('parent_id', $ids1)
									->orWhereIn('primary_id',$ids1);
							}
							)
			          ->where('level_id',0)
					  ->where('pid',$pid)						  
			          ->get([DB::raw('CASE WHEN multiPack=0 THEN count(primary_id) ELSE sum(pkg_qty) END AS qty')]);
$pack = $pack[0]->qty;

			 //Log::info('PACK');         
			 //Log::info($pack);


$uom = DB::table('eseal_'.$mfgId)
			          ->where(function($query) use($ids1){
							  $query->whereIn('parent_id', $ids1)
									->orWhereIn('primary_id',$ids1);
							}
							)
			          ->where('level_id',0)
					  ->where('pid',$pid)						  
			          ->sum('pkg_qty');

			 //Log::info('UOM');         
			 //Log::info($uom);




$qty = DB::table('eseal_'.$mfgId.' as es')
                           ->join('products as pr','pr.product_id','=','es.pid')
						   ->where('pid',$pid)
						    ->where(function($query) use($ids1){
							  $query->whereIn('parent_id', $ids1);
									
							}
							)
							
						   ->distinct()
						   ->count('parent_id');

			//Log::info('QTY');         
			 //Log::info($qty);			   

		


			/*$set = DB::table('eseal_'.$mfgId.' as es')
							->join('products as pr','pr.product_id','=','es.pid')    	               
							->where('batch_no',$batch->batch_no)
							->where(function($query) use($ids1){
							  $query->whereIn('parent_id', $ids1)
									->orWhereIn('primary_id',$ids1);
							}
							)
							->groupBy('es.batch_no')
							->select([DB::raw('group_concat(primary_id) as id'),'es.batch_no','pr.name','pr.mrp'])
							->get();*/

$set = DB::table('eseal_'.$mfgId.' as es')
							->join('products as pr','pr.product_id','=','es.pid')    	               
							->where('pid',$pid)
							->where(function($query) use($ids1){
							  $query->whereIn('primary_id', $ids1);
									
							}
							)
							->distinct()							
							->lists('primary_id');

//Log::info($set);

$primaryArr = array();
			foreach ($set as $primary) {
				
				
              $primaryArr [] = $primary;


		   }								



			$primaries = implode(',',$primaryArr);

			
			$set1['id'] = $primaries;


			$productDetails = DB::table('products as p')
                                ->join('uom_classes as uom','uom.id','=','p.uom_class_id')
			                    ->where('product_id',$pid)
			                    ->get(['material_code','name','mrp','uom_name','uom_code']);
//Log::info($pid);
//Log::info($productDetails);
//Log::info('xxxxxxxxxxxxxxxxx');
			$set1['material_code'] = $productDetails[0]->material_code;
$set1['batch_no'] = '';
			$set1['name'] = $productDetails[0]->name;
			$set1['uom_class'] = $productDetails[0]->uom_code;
//Log::info('vvvvvvvvvvv');

            $set1['uom_qty'] = $uom;
$set1['mrp'] = $productDetails[0]->mrp;

//Log::info('xxxxxxxxxxzzzzzzzzzz');

			$set1['qty'] = $qty;
			  if($pack){
			    $set1['pack'] = $pack; 
			  }
			  else{
				$set1['pack'] = 0; 
			  }
			//  if(trim($set1['id'])!='')
			array_push($tot,$set1);      
			  
					}


		$th = DB::table('track_history as th')
				   ->join('locations as ls','ls.location_id','=','th.src_loc_id')
				   ->join('locations as ls1','ls1.location_id','=','th.dest_loc_id')
				   ->join('transaction_master as tr','tr.id','=','th.transition_id')
				   ->where('tp_id',$tp)
				   ->get(['ls.location_name as src','ls1.location_name as dest','ls.location_address as src_name','ls1.location_address as dest_name','th.tp_id','tr.name','th.update_time']);

		  if(!empty($th))
		  {
			$pdfContent = ['manufacturer' =>$mfg_name,'tp'=>$th[0]->tp_id,'status'=>$th[0]->name,'datetime'=>$th[0]->update_time,'src_name'=>$th[0]->src_name,'dest_name'=>$th[0]->dest_name,'src'=>$th[0]->src,'dest'=>$th[0]->dest,'tot'=>$tot];
		  }
		else{
           throw new Exception('Error in retrieving TP Data');
		}
		  
		  $message = 'Tp Data retrieved successfully';
	}
	catch(Exception $e){
		$status =0;
		$message = $e->getMessage();
	  }
	  Log::info(['Status'=>$status,'Message'=>$message,'tp_pdf'=>$pdfContent,'tp_attributes'=>$tpAttributes]);
	  return json_encode(['Status'=>$status,'Message'=>$message,'tp_pdf'=>$pdfContent,'tp_attributes'=>$tpAttributes]);
	}


public function updateWrongStock(){

    try{
    	DB::beginTransaction();
    	Log::info(__FUNCTION__.' : '.print_r(Input::get(),true));
    	$status =1;
    	
    	$track_id = 1;
    	$update_time= '2016-07-27 23:31:37';

    	$trackIds = DB::table('track_history')->where('track_id','>',$track_id)->lists('track_id');
    
       foreach($trackIds as $track){

       
       $update_time = strtotime($update_time);
       $update_time = $update_time+(7);
       $update_time = date("Y-m-d H:i:s", $update_time);

       DB::table('track_history')->where('track_id',$track)->update(['sync_time'=>$update_time]);	

  //     Log::info($track.' updated');
       
       }
       
       $message = 'Track history Updated Successfully.';
       DB::commit();
    }
    catch(Exception $e){
    	$status =0;
    	$message =$e->getMessage();
    	DB::rollback();
    }
    Log::info(['Status'=>$status,'Message'=>$message]);
   return json_encode(['Status'=>$status,'Message'=>$message]);

   }

public function getDeliveryStock(){

    try{
    	Log::info(__FUNCTION__.' : '.print_r(Input::get(),true));
    	$status =1;
    	$delivery_no = Input::get('delivery_no');
    	$mfgId = $this->roleAccess->getMfgIdByToken(Input::get('access_token'));
        $batchData =  array();
    	
    	if(empty($delivery_no))
    		throw new Exception('Please pass delivery no.');

            $sql = 'select input from api_log where input like "%'.$delivery_no.'%" and api_name="SyncStockOut" limit 1';
            $result = DB::select($sql);

            if(empty($result))
              throw new Exception('There is no STO record for this deliveryNo');
               
            $inputArray = unserialize($result[0]->input);
    //       Log::info($inputArray);
            $ids = $inputArray['ids'];
            $ids = explode(',',$ids);

            $batchData = DB::table('eseal_'.$mfgId.' as es')
                              ->join('products as p','p.product_id','=','es.pid')
                              ->where(function($query) use($ids){
							    $query->whereIn('primary_id', $ids)
									  ->orWhereIn('parent_id',$ids);
							}
							)
							  ->where('level_id',0)
							  ->groupBy('batch_no','pid')
							  ->get(['batch_no as Batch Number',DB::raw('sum(pkg_qty) as Quantity'),'material_code as Material Code']);              
            

       $message = 'Stock Data Retrieved Successfully';
       
    }
    catch(Exception $e){
    	$status =0;
    	$message =$e->getMessage();    	
    }
    Log::info(['Status'=>$status,'Message'=>$message,'Data'=>$batchData]);
   return json_encode(['Status'=>$status,'Message'=>$message,'Data'=>$batchData]);

   }



   public function addphysicalinventory(){
	$startTime = $this->getTime();
Log::info(__FUNCTION__.' : '.print_r(Input::get(),true));
$data = Input::all();
$module_id = $data['module_id'];
$access_token = $data['access_token'];
$IOTS = isset($data['IOTS'])?$data['IOTS']:"[]";
$reset_flag = isset($data['reset_flag'])?$data['reset_flag']:0;

$customer_id = $this->roleAccess->getMfgIdByToken($access_token);
$locationId = $this->roleAccess->getLocIdByToken($access_token);

$message = "Done Successfully";
$status = 1;
$insert_array =[];
$invalid_array = [];
$array_statistics = [];


//Log::info("----------IOTS----".print_r($IOTS,true));
try{
	DB::beginTransaction();
	{
		if($reset_flag == 1){


			$childIds = Array();
			$locationObj = new Locations\Locations();
		$childIds = $locationObj->getAllChildIdForParentId($locationId);
		if($childIds)
		{
			array_push($childIds, $locationId);
		}
		$parentId = $locationObj->getParentIdForLocationId($locationId);
		$childIds1 = Array();
		if($parentId)
		{
			$childIds1 = $locationObj->getAllChildIdForParentId($parentId);
			if($childIds1)
			{
				array_push($childIds1, $parentId);
			}
		}
		$childsIDs = array_merge($childIds, $childIds1);
		array_push($childsIDs, $locationId);
		$childsIDs = array_unique($childsIDs);

		/*if(count($childsIDs))
		{
			$locationId = implode(',',$childsIDs);	
		}*/

		    $locationId = $childsIDs;
		
			DB::table('physical_inventory_ref')->whereIn('location_id',$locationId)->update(['is_deleted'=>1,'updated_time'=>$this->getDate()]);

			$message = "Deleted Successfully";
		}
		else{
//			Log::info("inserting records--");
				$IOTS = json_decode($IOTS,true);

				if(!is_array($IOTS)){
					$IOTS = [];
					$status = 0;
					$message = "IOTS parameter is empty";
				}

				$plant_code = DB::table('locations')->where('location_id',$locationId)->pluck('erp_code');
				foreach($IOTS as $iot){
					
					if( !(isset($iot['user_id']) && isset($iot['iots'])) ){
						$status = 0;
						$message = "No User_id or iots are passed in the array";
						break;
					}
					if(isset($iot['remarks'])){
						$remarks = $iot['remarks'];
					}
					else{
						$remarks = "";
					}
					//dd($iot['iots']);
//					Log::info($iot);
					$input_iots = explode(',',$iot['iots']);
//dd($input_iots);
//					Log::info("Input iots....---".print_r($input_iots,true));
					$location_id = DB::table('users')->where('user_id',$iot['user_id'])->pluck('location_id');

					$already_inserted = DB::table('physical_inventory_log')->join('physical_inventory_ref','physical_inventory_ref.ref_id','=','physical_inventory_log.ref_id')
					->join('eseal_'.$customer_id,'physical_inventory_log.iot','=','eseal_'.$customer_id.'.primary_id')
					->where(function($query) use($input_iots){
									$query->whereIn('parent_id', $input_iots)
										  ->orWhereIn('primary_id',$input_iots);
											 }
											 )
					->where('is_deleted',0)->lists('primary_id');
//					Log::info("already inserted records ".print_r($already_inserted,true));

					// $not_inserted = array_diff($input_iots,$already_inserted);

					// if(count($not_inserted)==0){
					// 	continue;
					// }
					// else{
					// 	$iot['iots'] = implode(',',$not_inserted);
					// 	Log::info("iots---".print_r($iot['iots'],true));
					// }



					$insert = DB::table('physical_inventory_ref')->insert(['location_id'=>$location_id,'user_id'=>$iot['user_id'],'remarks'=>$remarks,'customer_id'=>$customer_id]);


					$lastinserted_id = DB::getPdo()->lastInsertId();
					
					$valid_iots = DB::table('eseal_'.$customer_id)->whereIn('primary_id',$input_iots)->lists('primary_id');
				



				//---------------
				
				//dd($insert);



				if($insert){

					if(count($already_inserted) > 0){
						//$already = 
						$already_inserted_check = " and primary_id not in (".implode(',',$already_inserted).") "; 		
					}
					else{
						$already_inserted_check = " ";
					}
				

				$ids = DB::select('select e.primary_id as id, p.material_code,e.batch_no,e.level_id,CASE  when (dest_loc_id = 0 and src_loc_id !=0) then src_loc_id  when (dest_loc_id !=0 and src_loc_id !=0) then 0 END as location from eseal_'.$customer_id.' e join products p on e.pid = p.product_id  join track_history th on th.track_id = e.track_id where (e.primary_id in ('.$iot["iots"].') or parent_id in('.$iot["iots"].')) '.$already_inserted_check);
				//dd($ids);
//Log::info("----------ids------------");
//				Log::info($ids);
//				Log::info("--------");
				$insert_array = [];
				foreach($ids as $id){

					array_push($insert_array,['ref_id'=>$lastinserted_id,'iot'=>$id->id,'material_code'=>$id->material_code,'level'=>$id->level_id,'batch_no'=>$id->batch_no,'present_location'=>$locationId,'eseal_location'=>$id->location]);
				}
				if(count($insert_array)>0){
					DB::table('physical_inventory_log')->insert($insert_array);	
				}
				$invalids = array_diff($input_iots,$valid_iots);

				//DB::table('physical_inventory_log')

//				Log::info("Invalid IOTS.-----".print_r($invalids,true));
				if(count($invalids)>0){
					$invalid_array =[];
					foreach($invalids as $invalid){
						array_push($invalid_array,['ref_id'=>$lastinserted_id,'iot'=>$invalid,'present_location'=>$locationId,'is_valid'=>0]);
					}
					DB::table('physical_inventory_log')->insert($invalid_array);
				}
				
				
				$statistics = DB::select('select count(iot) as count,material_code,batch_no,level from physical_inventory_log where ref_id = '.$lastinserted_id.' group by level,material_code,batch_no');
				$array_statistics = [];
				foreach($statistics as $stat){
					array_push($array_statistics,['ref_id'=>$lastinserted_id,'material_code'=>$stat->material_code,'batch_no'=>$stat->batch_no,'level'=>$stat->level,'count'=>$stat->count,'erp_code'=>$plant_code]);			
				}
				if(count($array_statistics)>0){
					DB::table('physical_inventory_statistics')->insert($array_statistics);	
				}



				}
				else{
					throw new Exception("Error in sql");
				}
						

			}	
		}

		
		//--------------
	}
	DB::commit();
}catch(Exception $e){
	DB::rollback();
	$status = 0;
	$message = $e->getMessage();
}
$endTime = $this->getTime();
Log::info(__FUNCTION__.' Finishes execution in '.($endTime-$startTime));
	Log::info(['Status'=>$status,'Message'=>$message]);
return json_encode(['Status'=>$status,'Message'=>$message]);

}


public function SerialnumMappingwithIot(){
	try{
    	Log::info(__FUNCTION__.' : '.print_r(Input::get(),true));
    	$status =1;
    	$mfgId = $this->roleAccess->getMfgIdByToken(Input::get('access_token'));
    	$esealTable = 'eseal_'.$mfgId;
    	$esealBankTable = 'eseal_bank_'.$mfgId;
    	$data=[];
    	$i = 0;
    	$iots =json_decode(Input::get('iots'));
    	$serialflag= trim(Input::get('serialflag'));
    	(strtolower($serialflag) == 'true' || $serialflag == 1) ? $serialflag = 1 : $serialflag =0;
    	$message='';
 	    DB::beginTransaction();  
    	 // $i=0;     
        $serialArray=[];
        $invalidArray=[];
        $IotAlreadyHavingSerialNo=[];
        $IotPairAleradyExists=[];
        $serial_update = 0;
        $invalidiot=0;
    
        if(empty($iots))
        {
        	throw new exception("Please Pass Iots");
        }
          foreach($iots as $iotserial)
    	{

    		$iothistory = DB::table($esealTable)->where('primary_id',$iotserial->IOT)->first();
    		$serialhistory = DB::table($esealTable)->where(['serial_no'=>$iotserial->SerialNo])->first();
    		$iotMapWithSerial = DB::table($esealTable)->where('serial_no','=',$iotserial->SerialNo)->
    		                                           where('primary_id','=',$iotserial->IOT)->first();
    		//dd($iotMapWithSerial);
    		if($iothistory)
    		{   
    		    if($iotMapWithSerial){
    		    	goto jumpPair;
    		    } 
    			if(!empty($iothistory->serial_no) ){
                 if($serialflag == 1 ){
            	$serial_update = 1;
            	$update = DB::table($esealTable)->where('primary_id',$iotserial->IOT)->update(['serial_no'=>$iotserial->SerialNo]);
                   }	
                   else{
                    array_push($IotAlreadyHavingSerialNo,["SerialNo"=>$iotserial->SerialNo,"IOT"=>$iotserial->IOT]);
                    }	
                  }
                  
                 if($serialhistory){
                  if(($serialhistory->primary_id == $iotserial->IOT && $serialhistory->serial_no == $iotserial->SerialNo) ||
                   	($serialhistory->primary_id == $iotserial->IOT && $serialhistory->serial_no != $iotserial->SerialNo)){array_push($serialArray,["SerialNo"=>$iotserial->SerialNo,"IOT"=>$iotserial->IOT]);
                    $i++;  
                   }
                }
                 else if(!$serialhistory && $iothistory->serial_no !=''){
                	if($serialflag ==0){
                      goto jump;
                	}
                }
                else
                {
                	$update = DB::table($esealTable)->where('primary_id',$iotserial->IOT)->update(['serial_no'=>$iotserial->SerialNo]);
                
                }
                jump:
                jumpPair:
            }
        		else{
                    array_push($invalidArray,["SerialNo"=>$iotserial->SerialNo,"IOT"=>$iotserial->IOT]);
                    $i++;
    		}
    	}
    	$message =  'Please Provide Valid Iots';
    	DB::commit();
    	$data = ['SerialNumberAlreadyExists'=>$serialArray,'IotsAreInvalid'=>$invalidArray,'IotHavingAlreadySerialNumber'=>$IotAlreadyHavingSerialNo];

      $cnt = count($serialArray)+count($invalidArray) + count($IotAlreadyHavingSerialNo);
      if(empty($serialArray) && empty($invalidArray) && empty($IotAlreadyHavingSerialNo)){
     	$status = 1;
     	$message = "All Records inserted Successfully";
         } 
        else if( $cnt == count($iots) || $cnt > count($iots)){
        $status = 0;
    	$message= 'No records are Updated';
         }
         else{
    	$status = 2;
    	$message = "Partially Updated";
        }
        if($serial_update==1 && $status==2 && $i == 0){
        $status = 1;
     	$message = "Fully Updated";
     	 }	
     	}catch(Exception $e){
    	$status =0;
         $message= $e->getMessage(); 
    	  DB::rollback();
    }
    Log::info(['Status'=>$status,'Message'=>$message]);
    return json_encode(['Status'=>$status,'Message'=>$message,'Data'=>$data]);
}
  

	
public function repairEseal(){
   try{          

    Log::info(__FUNCTION__.' : '.print_r(Input::get(),true));
    $status =1;    
    $message = 'Iot\'s replaced successfully';
      $old_iot = trim(Input::get('old_iot'));
      $new_iot = trim(Input::get('new_iot'));
   $mfgId = $this->roleAccess->getMfgIdByToken(Input::get('access_token'));
   $srcLocationId = $this->roleAccess->getLocIdByToken(Input::get('access_token'));         
   $transitionTime = $this->getDate();
   $transitionId = trim(Input::get('transitionId'));   

   $esealTable = 'eseal_'.$mfgId;
   $esealBankTable = 'eseal_bank_'.$mfgId;
   $destLocationId = 0;   

   if($transitionTime > $this->getDate())
    $transitionTime = $this->getDate();

   if(empty($old_iot) || empty($new_iot) || empty($transitionId) || empty($transitionTime))
    throw new Exception('Parameters Missing.');   
            

   $locationDetails = DB::table('eseal_'.$mfgId.' as es')
                          ->join('track_history as th','th.track_id','=','es.track_id')
                          ->where('es.primary_id',$old_iot)                          
                          ->get(['src_loc_id','dest_loc_id']);

   //Log::info($locationDetails);

            ///////Required Validations////////

      $matchedCnt = DB::table('eseal_'.$mfgId)
                     ->where('primary_id',$old_iot)
                           ->where('is_active',1)
                     ->count();

     // Log::info('Matched count :'.$matchedCnt);

   if($matchedCnt != 1)
    throw new Exception('IOT count mismatch'); 

   
    
           
           
    /*if($locationDetails[0]->dest_loc_id != 0)
     throw new Exception('The IOT\'s is still in-transit.');*/
   
   
    
    /*if($locationDetails[0]->src_loc_id != $srcLocationId)
       throw new Exception('The IOT\'s are present in some other location');*/         

       $srcLocationId = $locationDetails[0]->src_loc_id;
       $destLocationId = $locationDetails[0]->dest_loc_id;


            $cnt = DB::table($esealBankTable)->where('id', $new_iot)
                ->where('used_status',0)
    ->where(function($query){
     $query->where('issue_status',1);
     $query->orWhere('download_status',1);
    })->count();
   
   //Log::info('new iot count :'.$cnt);

   if($cnt != 1)
    throw new Exception('quantity mismatch for new iot');

   
            //////End of validations/////////  

   $replacedIots = array_combine([$old_iot],[$new_iot]);

   //Log::info('Combined IOT\'s');
   //Log::info($replacedIots);
         DB::beginTransaction();
   foreach($replacedIots as $key => $value){

    $lvl = DB::table('eseal_'.$mfgId)->where('primary_id',$key)->pluck('level_id');

    $sql = 'insert into eseal_'.$mfgId.' (pid,primary_id,parent_id,level_id,attribute_map_id,track_id,batch_no,mfg_date,pkg_qty,po_number,reference_value,is_confirmed,inspection_result)
            select pid,'.$value.',parent_id,level_id,attribute_map_id,track_id,batch_no,mfg_date,pkg_qty,po_number,reference_value,is_confirmed,inspection_result from eseal_'.$mfgId.' where primary_id='.$key;

      DB::insert($sql);

      $sql = 'insert into track_details (code,track_id) select '.$value.',track_id from '.$this->trackDetailsTable.' where code='.$key;

      DB::insert($sql);

      $sql = 'insert into bind_history(eseal_id,location_id,attribute_map_id,created_on) select '.$value.',location_id,attribute_map_id,"'.$transitionTime.'" from '.$this->bindHistoryTable.' where eseal_id='.$key;

      DB::insert($sql);

            if($lvl == 0){
        DB::table('eseal_'.$mfgId)->where('primary_id',$key)->update(['parent_id'=>0,'is_active'=>0]);
            }
      else{
        DB::table('eseal_'.$mfgId)->where('parent_id',$key)->update(['parent_id'=>$value]);
        DB::table('eseal_'.$mfgId)->where('primary_id',$key)->update(['is_active'=>0]);
      }

   }

    //Updating Ids in esealBankTable with usedStatus.
    DB::table($esealBankTable)->where('id',$new_iot)->update(['used_status'=>1]);                    



    $explodeIds = array_merge([$old_iot],[$new_iot]);
    //Log::info('IOTS COMBINATION FOR REPAIR TRACKUPDATE');
    //Log::info($explodeIds);


            ///////END OF DOWNLOADING NEW IOT'S////////////

   /******************START OF REPAIR IN ESEAL**********************/

             $transactionObj = new TransactionMaster\TransactionMaster();
  $transactionDetails = $transactionObj->getTransactionDetails($mfgId, $transitionId);
  Log::info(print_r($transactionDetails, true));
  if($transactionDetails){
    $srcLocationAction = $transactionDetails[0]->srcLoc_action;
    $destLocationAction = $transactionDetails[0]->dstLoc_action;
    $inTransitAction = $transactionDetails[0]->intrn_action;
  }else{
  throw new Exception('Unable to find the transaction details');
   }
  
   
   //Log::info('SrcLocAction : ' . $srcLocationAction.' , DestLocAction: '. $destLocationAction.', inTransitAction: '. $inTransitAction);
   
   
    //Log::info(__LINE__);
    
    
  $trakHistoryObj = new TrackHistory\TrackHistory();
  try{
   $lastInrtId = DB::table($this->trackHistoryTable)->insertGetId( Array(
    'src_loc_id'=>$srcLocationId, 'dest_loc_id'=>$destLocationId, 
    'transition_id'=>$transitionId,'update_time'=>$transitionTime));
   //Log::info($lastInrtId);

   $maxLevelId =  DB::table($esealTable)
        ->whereIn('parent_id', $explodeIds)
        ->orWhereIn('primary_id', $explodeIds)->max('level_id');

            //Component Trackupdating

   $res = DB::table($esealTable)->where('level_id', 0)
       ->where(function($query) use($explodeIds){
        $query->whereIn('primary_id',$explodeIds);
        $query->orWhereIn('parent_id',$explodeIds);
       })->lists('primary_id');
        
   if(!empty($res)){
    
    $attributeMaps =  DB::table('bind_history')->whereIn('eseal_id',$res)->distinct()->lists('attribute_map_id');

    $componentIds =  DB::table('attribute_mapping')->whereIn('attribute_map_id',$attributeMaps)->where('attribute_name','Stator')->lists('value');
    
    if(!empty($componentIds)){
      $componentIds = array_filter($componentIds);
      $explodeIds = array_merge($explodedIds,$componentIds);
    }

   }
//End Of Component Trackupdating

   if(!$this->updateTrackForChilds($esealTable, $lastInrtId, $explodeIds, $maxLevelId)){
    throw new Exception('Exception occured during track updation');
   }
   
   //Log::info(__LINE__);
   $sql = 'INSERT INTO  '.$this->trackDetailsTable.' (code, track_id) SELECT primary_id, '.$lastInrtId.' FROM '.$esealTable.' WHERE track_id='.$lastInrtId;
   DB::insert($sql);
   //Log::info(__LINE__);

   DB::commit();
   
  }catch(PDOException $e){
   Log::info($e->getMessage());
   throw new Exception('SQlError during track update');
  }  

         /******************END OF REPAIR IN ESEAL**********************/           

    }

     
   catch(Exception $e){
    DB::rollback();
    $status =0 ;
    $message = $e->getMessage();
   }
   Log::info(['Status'=>$status,'Message'=>$message]);
   return json_encode(['Status'=>$status,'Message'=>$message]);
  }


  public function script(){
 try{
 	Log::info(__FUNCTION__.' : '.print_r(Input::get(),true));
	$dels = Input::get('data');
	$new = array();
	$status = 1;
	$message = 'xxxxxx';

	$delArr = explode(',',$dels);

	foreach($delArr as $iot){
		//Log::info('delivery is'.$iot);
		
		$sql = 'select count(id) from api_log where match (input) against ('.$iot.') and api_name="syncstockout"
		             and message not like "%and delivery%" and status=0';
		$count = DB::select($sql);

		if($count == 0)
			$new[]= $iot; 

		
	}

  
}
catch(Exception $e){
	$message = $e->getMessage();
	$status = 0;
}

   return json_encode(['Status'=>$status,'Message'=>$message,'Data'=>$new]);


}  





	public function updateFinanceInfo()
	{
		try{
		$startTime = $this->getTime();
		$transitionTime = trim(Input::get('transition_time'));
		$dateTime = $this->getDate();
		//Log::info(__FUNCTION__.' : '.print_r(Input::get(),true));
		$location_id = $this->roleAccess->getLocIdByToken(Input::get('access_token'));
		$iotId=Input::get('iot_id');
		$mfg_id = $this->roleAccess->getMfgIdByToken(Input::get('access_token'));
		$iotExist =  DB::table('eseal_bank_'.$mfg_id)->where('id', $iotId)->count();
		$esealEsist = DB::table('eseal_'.$mfg_id.' as eseal')
									->where('eseal.primary_id',$iotId)
									->where('eseal.level_id',0)
									->count();

			if($iotExist>0 && $esealEsist > 0 && strlen($iotId)==16){

				/*******************
				location commenting
				*******************
				$recentLocId=DB::table('eseal_'.$mfg_id.' as e')
							->join('track_details as td','e.primary_id','=','td.code')
							->join('track_history as th','th.track_id','=','td.track_id')
							->where('e.primary_id',$iotId)
							->where('th.dest_loc_id','!=',0)
							->orderBy('th.track_id','desc')
							->pluck('dest_loc_id');

				if($recentLocId){
					$recentLocType=DB::table("locations as loc")
								->join('location_types as tp','loc.location_type_id','=','tp.location_type_id')
								->where('loc.location_id',$recentLocId)
								->pluck('location_type_name');
					if(in_array(strtolower($recentLocType),array('supplier','plant','warehouse','depot'))){
					throw new Exception("eSeal ID is present in some other location", 1);
					}
				} else {
					throw new Exception("eSeal ID is present in some other location", 1);
				}
				*/

				$pData = DB::table('eseal_'.$mfg_id.' as eseal')
									->where('eseal.primary_id',$iotId)
									->select('pid','attribute_map_id')
									->get();
				$pid=$pData[0]->pid;
				$attribute_map_id=$pData[0]->attribute_map_id;
				
				$attributsList = DB::table('eseal_'.$mfg_id.' as eseal')
									->join('attribute_mapping as map','map.attribute_map_id','=','eseal.attribute_map_id')
									->where('eseal.primary_id',$iotId)
									->lists('attribute_id');
				
				 $attributeIds=DB::table('attribute_sets as a_set')
				 ->join('attribute_set_mapping as map','a_set.attribute_set_id','=','map.attribute_set_id')
				 ->where('attribute_set_name','bajaj_fin_set')->lists('attribute_id');
				 $financeApplied=0;
				 foreach($attributeIds as $key => $value ){
				 	if(in_array($value,$attributsList))
				 		$financeApplied=1;
				 }
	
				if($financeApplied){
					// finance taken 
					$status = 2;
					$message="Serial number is already Financed";
				} else {
				//need to give finance now
				$attributeIds=DB::table('attribute_sets as a_set')
				 ->join('attribute_set_mapping as map','a_set.attribute_set_id','=','map.attribute_set_id')
				 ->join('attributes as at','map.attribute_id','=','at.attribute_id')
				 ->where('attribute_set_name','bajaj_fin_set')->lists('attribute_code');
				$attributeJsonData = DB::table('attribute_map')
					->where('attribute_map_id',$attribute_map_id)
					->lists('attribute_json');

				$attributePrepareJson=(object)[];
				if(count($attributeJsonData)>0){
					$attributePrepareJson=(object) json_decode($attributeJsonData[0]);
				}

				foreach ($attributeIds as $key => $value) {
					if(strtolower(trim($value))=='finance'){
				 		$attributePrepareJson->$value='Yes';
				 	}
					if(strtolower(trim($value))=='bajaj_company'){
				 		$attributePrepareJson->$value=DB::table('locations')->where('location_id',$location_id)->pluck('location_name');			
				 	}	 
				}



				DB::beginTransaction();
				$request = Request::create('scoapi/SaveBindingAttributes', 'POST', array('module_id'=> Input::get('module_id'),'access_token'=>Input::get('access_token'),'attributes'=>json_encode($attributePrepareJson),'lid'=>$location_id,'pid'=>$pid));

				$originalInput = Request::input();//backup original input
				Request::replace($request->input());
				Log::info($request->input());
				$res1 = Route::dispatch($request)->getContent();//invoke API
				$res1 = json_decode($res1);
				$latestId=$res1->AttributeMapId;

				DB::update('Update eseal_'.$mfg_id.' SET attribute_map_id='.$latestId.' WHERE primary_id = ? ', Array($iotId));
/*				DB::insert('INSERT INTO bind_history (eseal_id,location_id,attribute_map_id,created_on) values (?, ?,?,?)', array($iotId,$location_id ,$latestId,$transitionTime));*/
				DB::update('Update bind_history SET attribute_map_id='.$latestId.' WHERE eseal_id = ? ', Array($iotId));
				$status=1;
				$message="Serial number validated successfully";	
				DB::commit();
			}

			} else {
				throw new Exception("Serial number is not Valid");
			}

		}catch(Exception $e)
		{
			$status =0;
			DB::rollback();
			$message = $e->getMessage();
		}

		$endTime = $this->getTime();
		Log::info(__FUNCTION__.' Finishes execution in '.($endTime-$startTime));
		Log::info(['Status'=>$status, 'Message' => $message]);
		return json_encode(['Status'=>$status,'Message'=>'Server: '.$message]);
		//exit;
	}					


public function GetEsealDataByLocationIdCount()
{
	$startTime = $this->getTime();    
	try
	{
		$status = 0;
		$message = '';
		$locationId = Input::get('locationId');
		$fromDate = Input::get('fromDate');
		$toDate =  Input::get('toDate');
		$checkSyncTime = Input::get('isSyncTime');
		$levels = Input::get('levels');
		$po_number = Input::get('po_number');
		$delivery_no = Input::get('delivery_no');/*
		$Range = Input::get('Range');
		$RangeCheck = $Range+1; //echo $Range.'---'.$RangeCheck;exit;*/
		$loadComponents = Input::get('loadComponents');
		$loadAccessories = Input::get('loadAccessories');	
		$excludePrimary = Input::get('excludePrimary');	
		$confirmedStock = Input::get('confirmedStock');		
		$finalQcStock = Input::get('finalQcStock');		
		$isDataAvailable = 0;
		$productTypes[] = 8003;
		$trackArray = array();
		$ip = $_SERVER['REMOTE_ADDR'];
		$pids ='';
		$productArray = Array();
		$i= 0;


                if($toDate == '' || empty($toDate))
                   $toDate = '9999-12-31 11:59:59';

		Log::info(__FUNCTION__.' : '.print_r(Input::get(),true));
		if(empty($locationId) || !is_numeric($locationId))
		{
			throw new Exception('Pass valid numeric location Id');
		}
		if(empty($levels) && $levels != 0)
		{
			throw new Exception('Parameters missing');
		}
		$locationObj = new Locations\Locations();
		$mfgId = $locationObj->getMfgIdForLocationId($locationId);
		
		$childIds = Array();
		$childIds = $locationObj->getAllChildIdForParentId($locationId);
		if($childIds)
		{
			array_push($childIds, $locationId);
		}
		$parentId = $locationObj->getParentIdForLocationId($locationId);
		$childIds1 = Array();
		if($parentId)
		{
			$childIds1 = $locationObj->getAllChildIdForParentId($parentId);
			if($childIds1)
			{
				array_push($childIds1, $parentId);
			}
		}
		$childsIDs = array_merge($childIds, $childIds1);
		$childsIDs = array_unique($childsIDs);
		if(count($childsIDs))
		{
			$locationId = implode(',',$childsIDs);	
		}
		$esealTable = 'eseal_'.$mfgId;        

		$splitLevels= array();

		Log::info($locationId);
		array_push($splitLevels,'levels'); 


		if($loadComponents)
			$productTypes[] = 8001;

		if($loadAccessories)
			$productTypes[] = 8004;


		Log::info('Product Types:-');
		Log::info($productTypes);
		$productType = implode(',',$productTypes); 

		if($po_number){
			$pid = DB::table($esealTable)->where('po_number',$po_number)->groupBy('pid')->pluck('pid');
			if(empty($pid)){
				throw new Exception('The given PO number doesnt exist');
			}
		}




		if($delivery_no)
		{
			$products =  new Products\Products;
			$pArray = $products->getProductsFromDelivery(Input::get('access_token'),$delivery_no);
			if($pArray)
			{
				$pids = implode(',',$pArray);
				Log::info('Products:-'.$pids);
			}
			else
			{
				throw new Exception('There are no materials configured in delivery no');
			}
		}

		if($checkSyncTime)
			$column = 'sync_time';
		else
			$column = 'update_time';
		
		$sql = 'select th.track_id from track_history th join eseal_'.$mfgId.' es on es.track_id=th.track_id where src_loc_id in('.$locationId.') and dest_loc_id=0 and es.level_id in('.$levels.')';
		 
		 if($fromDate!='')
			$sql .= ' and ('.$column.' >= "'.$fromDate.'" ';
		 if($toDate!='')
			$sql .= ' and '.$column.' <= "'.$toDate.'")';                
		 if($excludePrimary)
				$sql .=' and es.parent_id =0';
		 if($finalQcStock)
		        $sql .=' and final_qc=1';	
		 if($confirmedStock)   
		 	    $sql .=' and is_confirmed=1';	
                $sql .= ' order by th.track_id asc';

		 $result = DB::select($sql);
		 if(empty($result)){
			throw new Exception('Data not-found');
		 }
		 foreach ($result as $res){
			$trackArray[] = $res->track_id;
		 }

                 $lastTrackId = end($trackArray);
		 $lastSyncTime = DB::table($this->trackHistoryTable)->where('track_id',$lastTrackId)->pluck($column);
		 $lastTrackIds = DB::table($this->trackHistoryTable)
		                    ->where($column,$lastSyncTime)
		                    ->whereIn('src_loc_id',explode(',',$locationId))
		                    ->where('dest_loc_id',0)
		                    ->lists('track_id');
		 $trackArray = array_merge($lastTrackIds,$trackArray);

		 $trackArray =  array_unique($trackArray);
		 $endTrack = end($trackArray);
		 $trackIds  = implode(',',$trackArray);                 
			
				$sql = '
				SELECT count(e.pid) as cnt,e.level_id  as levelId
				FROM '.$esealTable.' e
				INNER JOIN products p ON e.pid=p.product_id
				WHERE
				p.product_type_id in('.$productType.') and e.track_id in('.$trackIds.') and e.level_id in('.$levels.') group by  e.level_id ';
		 
			if(!empty($pids))
				$sql .=' and e.pid in('.$pids.')';			
			
			if($po_number)
				$sql .=' and e.pid='.$pid;
			
			if($excludePrimary)
				$sql .=' and e.parent_id=0';

			if($finalQcStock)
		        $sql .=' and final_qc=1';
		    if($confirmedStock)   
		 	    $sql .=' and is_confirmed=1';

			$sqlcount=$sql;
			$sql='';
		
			try
			{
					$result = DB::select($sqlcount);
					
				$totResult = count($result); 
			}
			catch(PDOException $e)
			{
				Log::info($e->getMessage());
				throw new Exception('SQlError while fetching data');
			}
			if(count($result))
			{
			$productArray = $result;
			$isDataAvailable = 1; 
			}
		$status = 1;
		$message = 'Data found';
			
	}
	catch(Exception $e)
	{
		echo "test eException".$e->getMessage(); exit;
		$status =0;
		Log::info($e->getMessage());
		$message = $e->getMessage();
	}


	$endTime = $this->getTime();
	return json_encode(['Status'=>$status, 'Message' =>'Server: '.$message, 'isDataAvailable'=>$isDataAvailable,'esealcount' => $productArray],JSON_UNESCAPED_SLASHES);
}
		
		
}        











