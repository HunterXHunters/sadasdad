<?php
namespace App\Http\Controllers;
set_time_limit(0);
ini_set('memory_limit', '-1');

use App\Repositories\RoleRepo;
use App\Repositories\CustomerRepo;
use Illuminate\Http\Request;
use App\Repositories\SapApiRepo;
use App\Repositories\ConnectErp;
use App\Repositories\ApiRepo;
use App\Repositories\OrderRepo;
use App\Models\MasterLookup;
use App\Models\Location;
use App\Models\Token;
use App\Models\ApiLog;
use App\Models\User;
use App\Models\ErpObjects;
use App\Models\Conversions;
use App\Models\Trackhistory;
use App\Models\Transaction;
use App\Models\Track;
use App\Models\UserNew;
use App\Models\Products\Products;
//use App\Events\test;
use App\Events\scoapi_BindEseals;
use App\Events\scoapi_MapEseals;


use Session;
use DB;
use View;
use Input;
use Validator;
use Redirect;
use Log;
use Exception;


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
	private $_childCodes = array();
	private $_apiRepo;
	public $erp;
	public $eSeal_erp;

//	public function __construct(RoleRepo $roleAccess,CustomerRepo $custRepo,SapApiRepo $sapRepo, ApiRepo $apiRepo,ConnectErp $erp) 
	public function __construct(RoleRepo $roleAccess,CustomerRepo $custRepo,SapApiRepo $sapRepo,Request $request) 
	{
		if(isset($_SERVER['HTTP_X_FORWARDED_FOR'])){
			$_SERVER['REMOTE_ADDR']=$_SERVER['HTTP_X_FORWARDED_FOR'];
		}
		$this->roleAccess = $roleAccess;
		$this->custRepo = $custRepo;
		$this->sapRepo = $sapRepo;
	//	$this->_apiRepo = $apiRepo;	
		$this->roleAccess = $roleAccess;
        $this->_request = $request;       
        $this->mfg_id=0;
        $this->eSeal_erp=1;
	}

		public function checkUserPermission($api_name){
		try{			
			$status = 0;
			$message = 0;		
			$data = $this->_request->all();
			$headerData = $this->_request->header();
			if(array_key_exists('user-id',$headerData)){
				try{
				//if($headerData['user-id'][0])
				$data['header_user-id']=$headerData['user-id'][0];
				$data['access_token']=$headerData['token'][0];
				$data['module_id']=4002;
				}
				catch(Exception $e){
					$message = 'header data missing.';
					return json_encode(['Status'=>$status,'Message'=>'S-:' .$message]);
				}
			}

			if($api_name == 'login' || $api_name == 'login1' || $api_name == 'forgotPassword' || $api_name == 'resetPassword' || $api_name == 'sendLogEmail' || $api_name == 'apiTest' || $api_name == 'getAppVersions' || $api_name  =='getDate' || $api_name == 'test2' || $api_name == 'appActivation' ){
				
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
					$user_id = DB::table('users_token')->where('access_token',$access_token)->value('user_id');					
					$details = $this->roleAccess->getUserDetailsByUserId($user_id);
					$this->mfg_id=$details[0]->customer_id;
					$this->eSeal_erp=DB::table('eseal_customer')->where('customer_id',$this->mfg_id)->value('eseal_erp');

                    $created_on = $this->getDbDate();//json_decode($this->getDbDate(),true)[0]['date'];
                    $startTime = $this->getTime();
					$result = $this->$api_name($data);
                    $endTime = $this->getTime();
					$response = json_decode($result);								
				
					$log = new ApiLog;
					$log->user_id = $user_id;
					
						$log->location_id = $details[0]->location_id;
						$log->manufacturer_id = $details[0]->customer_id; 

					$log->api_name = $api_name;
					$log->input = serialize($data); 
					$log->created_on = date('Y-m-d h:i:s');
					$log->status = $response->Status;
					$log->message = $response->Message;
					$log->save();



					$captureLog = curl_init();
//					curl_setopt($curl, CURLOPT_HTTPHEADER,$headers);
					$logBody=[];
					$logBody['ip']=$_SERVER['REMOTE_ADDR'];
					$logBody['api_name']=$api_name;
					$logBody['request']=json_encode($data);
					$logBody['responce']=json_encode($response);
					$logBody['message'] = $response->Status;
					$logBody['status'] = $response->Message;
					$logBody['location_id'] = $details[0]->location_id;
					$logBody['user_id'] = $user_id;
					$logBody['manufacturer_id'] = $details[0]->customer_id;
					$logBody['time'] = date("d-m-Y H:i:s");

					curl_setopt($captureLog, CURLOPT_CUSTOMREQUEST,'POST');
					curl_setopt($captureLog, CURLOPT_POSTFIELDS,$logBody);
					curl_setopt($captureLog, CURLOPT_RETURNTRANSFER, true);
					curl_setopt($captureLog, CURLOPT_URL,'http://localhost/elastic/public/api/putLog?index=hul&type=apiLog');

			//		curl_setopt($captureLog, CURLOPT_TIMEOUT_MS, 10);
					$captureLog_result = curl_exec($captureLog);
				    curl_close($captureLog);  
				
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
		return json_encode(['Status'=>$status,'Message'=>'S-:' .$message]);
	}

	public function getDbDate(){
		//return json_encode(DB::select('select now() as date')) ;
		return date('Y-m-d h:i:s');
	}
	public function getDate(){
		return date("Y-m-d H:i:s");
	}

	public function getTime(){
		$time = microtime();
		$time = explode(' ', $time);
		$time = ($time[1] + $time[0]);
		return $time;
	}

	public function login($data){
		try{
			////Log::info($data);
			$status =0;
			$user_id = $data['user_id'];
			$password = $data['password'];
			$module_id = $data['module_id'];

			if(empty($user_id) || empty($password) || empty($module_id)){
				throw new Exception('Parameters Missing');
			}

			$oAuthResponse = $this->loginViaOauth();
			$user = UserNew::find(13);
			$oAuthtoken = $user->createToken('Token Name')->accessToken;
			
			if($oAuthResponse === false) {
				throw new Exception('Unable to Login.');
				return response()->json([
					'msg' => 'Unable to Login'
				],401);
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
				$master = MasterLookup::where('value',$module_id)->get()->toarray();
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
				/* $userinfo = DB::table('users')
							->leftJoin('locations','locations.location_id','=','users.location_id')
							->leftJoin('location_types','location_types.location_type_id','=','locations.location_type_id')
							->leftJoin('user_roles','user_roles.user_id','=','users.user_id')
							->where('user_roles.user_id',$user_id)
							->get(['locations.location_id','locations.location_name','locations.location_type_id','location_types.location_type_name','locations.location_email','locations.location_address','locations.location_details','locations.erp_code','users.firstname','users.user_id','users.lastname','users.email','users.customer_id',DB::raw('group_concat(user_roles.role_id) as role_id'),'users.location_id'])[0]; */
				$user_sql=" select `locations`.`location_id`, `locations`.`location_name`, `locations`.`location_type_id`, `location_types`.`location_type_name`, `locations`.`location_email`, `locations`.`location_address`, `locations`.`location_details`, `locations`.`erp_code`, `users`.`firstname`, `users`.`user_id`, `users`.`lastname`, `users`.`email`, `users`.`customer_id`, group_concat(user_roles.role_id) as role_id, `users`.`location_id` from `users` left join `locations` on `locations`.`location_id` = `users`.`location_id` left join `location_types` on `location_types`.`location_type_id` = `locations`.`location_type_id` left join `user_roles` on `user_roles`.`user_id` = `users`.`user_id` where `user_roles`.`user_id` = ".$user_id;
				$userinfo=DB::select($user_sql)[0];
			
				if(empty($userinfo)){
					throw new Exception('Role not assigned to User');
				}
				$roles =  explode(',',$userinfo->role_id);
			//	////Log::info($roles);
				$manufacturer_name =  DB::table('eseal_customer')->where('customer_id',$userinfo->customer_id)->value('brand_name');
				$user = array('user_id'=>(string)$userinfo->user_id,'firstname'=> $userinfo->firstname,'lastname'=>$userinfo->lastname,'email'=> $userinfo->email,'manufacturer_id'=> $userinfo->customer_id,'manufacturer_name'=>$manufacturer_name);
				$warehouse = DB::table('wms_entities')->where(array('location_id'=>intval($userinfo->location_id), 'entity_type_id'=>6001))->value('id');

				$location = array('location_id'=>intval($userinfo->location_id),'name'=>$userinfo->location_name,'location_type_id'=>intval($userinfo->location_type_id),'erp_code'=>$userinfo->erp_code,'location_type_name'=>$userinfo->location_type_name,'email'=>$userinfo->location_email,'address'=>$userinfo->location_address,'details'=>$userinfo->location_details,'warehouse_id'=>intval($warehouse));
				
				$permissioninfo = DB::table('role_access')
									->leftJoin('features','role_access.feature_id','=','features.feature_id')
									->join('features as fs','fs.feature_id','=','features.parent_id')
									->where(['features.master_lookup_id'=>$module_id])
									->whereIn('role_access.role_id',$roles)                     
									->get(['features.name','features.feature_code','fs.feature_code as parent_feature_code']);
				
				/*$traninfo = DB::table('transaction_master')
								->where('manufacturer_id',$userinfo->customer_id)
								->get()->toarray();*/
				$traninfo = DB::table('role_access')
								   ->join('features','role_access.feature_id','=','features.feature_id')
								   ->join('master_lookup','master_lookup.value','=','features.master_lookup_id')
								   ->join('transaction_master','transaction_master.name','=','features.name')
								   ->where(['master_lookup_id'=>4002,'transaction_master.manufacturer_id'=>$userinfo->customer_id])
								   ->whereIn('role_access.role_id',$roles)
								   ->orderBy('seq_order','desc')
								   ->select('transaction_master.*')
                                  ->addSelect(DB::raw('cast(transaction_master.id as char) as id'),DB::raw('cast(transaction_master.srcLoc_action as char) as srcLoc_action'),DB::raw('cast(transaction_master.dstLoc_action as char) as dstLoc_action'),DB::raw('cast(transaction_master.intrn_action as char) as intrn_action'),DB::raw('cast(transaction_master.seq_order as char) as seq_order'))
                                                                   ->get()->toarray();

			////Log::info('Login Successfull');
				return json_encode(['Status'=>1,'Message'=>'Successfull Login','Data'=>['user_info'=>$user,'permissions'=>$permissioninfo,'location'=>$location,'transitions'=>$traninfo,'access_token'=>$rand_id]]);
			}
			else{
				throw new Exception('Invalid UserId or Password.');
			}
		}
		catch(Exception $e){
			$message  = $e->getMessage();
		}
		return json_encode(['Status'=>$status,'Message' =>'S-: '.$message]);
	}

	public function getErpObjectResponse()
	{
		
		$status =1;
		$objectType = $this->_request->input('type');		
		$objectId = $this->_request->input('object_id');
		$cancelled = $this->_request->input('cancelled');
		$is_to = $this->_request->input('is_to');
		$docNumber = $this->_request->input('doc_number');
		$isPoNumber = $this->_request->input('is_po_number');
		$action = 'add';
		$poIntransitQty = array();
		$qty ='';
		$location_id = '';

		$locationId = $this->roleAccess->getLocIdByToken($this->_request->input('access_token'));	
		$mfg_id = $this->roleAccess->getMfgIdByToken($this->_request->input('access_token'));
		$plantId = DB::table('locations')->where('location_id',$locationId)->value('erp_code');
		$esealErp = trim($this->_request->input('eseal_Erp'));
		$ageing=array();
		$FIFOValidation=array();
		$FIFOstorageLocation_ids=array();
		
		$data = "";
		$esealData = "";
		$src_loc_id = "";
		try
		{	

				$FIFOValidation=array();
				$FIFOstorageLocation_ids=array();
				$fifo_products=array();
				$inputData=array('module_id' => $this->_request->input('module_id'),'access_token' => $this->_request->input('access_token'), 'plant_id' => $plantId,'type' => $objectType,'object_id' => $objectId,'action' => $action,'cancelled'=>$cancelled, 'doc_number' => $docNumber, 'is_po_number' => $isPoNumber,'is_to '=>$is_to);


//echo "<pre/>";print_r($inputData);exit;

				$req = Request::create('scoapi/notifyEseal', 'POST',$inputData);
				$originalInput=$this->_request->all();
				$this->_request->replace($req->all());
				$res = app()->handle($req);
				$res2 = $res->getContent();
				return $res2; 
		}
		catch(Exception $e)
		{
			    $status = 0;
				$message = $e->getMessage();
				return json_encode(['Status'=>$status,'Message'=>$message]);
		}
	}


public function notifyEseal()
{
	$startTime = $this->getTime();
	$plantId = $this->_request->input('plant_id');
	$objectType = $this->_request->input('type');
	$objectId = $this->_request->input('object_id');
	$is_to = $this->_request->input('is_to');
	$action = $this->_request->input('action');
	$createOrder = $this->_request->input('createOrder');
	$order_quantity = $this->_request->input('order_quantity');
	$order_uom = $this->_request->input('order_uom');
	$remarks = $this->_request->input('remarks');
	$docNo='';

//echo $objectType;exit;

	$status =1;
    $message = '';
	$movement_type = $this->_request->input('movement_type');
	if(!$movement_type)
		$movement_type =0;
			
	//$location_id = $this->_request->input('location_id');

	$permission = $this->roleAccess->checkPermission($this->_request->input('module_id'),$this->_request->input('access_token'));
	if(!$permission){
		////Log::info('Permission denied');
		return json_encode(['Status'=>$status,'Message'=>'Permission Denied']);
	}

	$locationId = $this->roleAccess->getLocIdByToken($this->_request->input('access_token'));
	$mfg_id = $this->roleAccess->getMfgIdByToken($this->_request->input('access_token'));
	$this->mfg_id=$mfg_id;
	$this->erp=new ConnectErp($mfg_id);

		if($objectType!='get_po_orders' && $objectType!='create_po_order' && $objectType!='PORDER' && $objectType!='putaway' &&$objectType!='Cancelled' && $objectType!='po_packed'){		
		//For checking the record already exists are not in the erp objects table.
		$query1 = DB::table('erp_objects')->where(array('manufacturer_id' => $mfg_id, 'type' => $objectType, 'object_id' => $objectId,'action' => $action));
		$objectCount = $query1->count();

		$erp_objects = new ErpObjects;
		$erp_objects->type = $objectType;
		$erp_objects->object_id = $objectId;
		$erp_objects->action = $action;
		$erp_objects->movement_type = $movement_type;
		$erp_objects->plant_id = $plantId;
		$erp_objects->location_id = $locationId;        
		$erp_objects->manufacturer_id = $mfg_id;
		$erp_objects->created_on = $this->getDate();

		}

		try
	{
			switch ($objectType)
			{	

				case "get_po_orders":
				case "create_po_order":

				$returnOrders=[];
				$returnProducts=[];
				if(!$this->eSeal_erp){
					//Calling the ECC					
				
				if($objectId==''){
					throw new Exception('mandatory Field ean/mat No missing', 1);
				}
				if($plantId==''){
					throw new Exception('mandatory Field Plant Erp code missing', 1);
				}	
					try{
					//create Order
					$result;
					$body=0;
					$methodType='';
					$params='';
					if($createOrder!=1){
						if(strlen($objectId) == 4 || strlen($objectId) == 13){
                                                	$objectId = DB::table('products')->where('ean',$objectId)->value('material_code');

                                           	 }
                            /*$objectId=DB::table('products')->where(function ($query) use ($objectId) {
    							$query->where('ean', '=', $objectId)
          							 ->orWhere('material_code', '=', $objectId);
							})->value('material_code'); 
							echo "<--------------->".$objectId."<---------------------->";
							if($objectId==NULL)
							{
								throw new Exception("No product against input in eSeal", 1);
								
							}
							exit;*/
							//echo $objectId;exit;
							           	 
						$method = 'orderDetails';
						$methodType='GET';
						$params = 'ean_material_number='.$objectId.'&plant='.$plantId;
						//$result=$this->erp->request($method,$params,0,'GET');

					} else {
						$method = 'createOrder';
						$methodType='POST';
						$body=array('order_creation'=>array('material_number'=>$objectId,'plant'=>$plantId,'order_quantity'=>$order_quantity,'order_uom'=>$order_uom));
						//$params = 'ean_material_number='.$objectId.'&plant='.$plantId;
					}
					
					$result=$this->erp->request($method,$params,$body,$methodType);
					$result=json_decode($result);
				
					if($createOrder==1){
						if(property_exists($result,'order_number')){
							$docNo=$result->order_number;
						} else {
							throw new Exception('Order not created Please check with ECC.'.$result->message, 1);
						}
					}	

					if($result->status){
						$material_code='';
						$plant='';
						$lid='';
						$product_id=0;
						$product_name=0;
						$loc_name=0;
						$orderInsert=[];
						//*************conversion insert************
						$material_code='';
						$product_id=0;
						$conInsert=[];
						foreach ($result->data->conversion as $key => $value) {
							$value->material = ltrim($value->material, '0');
							if($material_code!=$value->material)
							{
								$product_id=DB::table('products')->where('material_code',$value->material)->value('product_id');
								if(!$product_id){
									throw new Exception(' Some products are not available in Eseal', 1);
								} else $material_code=$value->material;
							}

							$alt_uom=DB::table('conversions')->where('product_id',$product_id)->where('alt_uom',$value->alt_uom)->count('id');
							
							if($alt_uom==0){
								$conInsert[]=['alt_uom'=>$value->alt_uom,'alt_quantity'=>$value->alt_quantity,'product_id'=>$product_id,'base_quantity'=>$value->base_quantity,'base_uom'=>$value->base_uom];
							}
						}
						$insert=DB::table('conversions')->insert($conInsert);

						DB::beginTransaction();
						$product_id=0;
						$product_name=0;
						$ean='';
						$loc_name=0;
						$orderInsert=[];
						$material_code='';
						foreach ($result->data->order as $key => $value) {
							$value->material = ltrim($value->material, '0');
							if($material_code!=$value->material)
							{
								$product_details=DB::table('products as p')->where('material_code',$value->material)->select(DB::raw("CONCAT(p.name,'==',p.ean) AS name"),'product_id')->pluck('name','product_id')->toarray();

								if(count($product_details)==0){
									throw new Exception(' Some products are not available in Eseal', 1);
								} else { 
									$material_code=$value->material;
									//$ean=$value->ean;
									$product_id=key($product_details); 
									$ean=explode('==',$product_details[$product_id])[1];
									$product_details[$product_id]=explode('==',$product_details[$product_id])[0];
									$product_name=strpos($product_details[$product_id],$material_code)?$product_details[$product_id]:$product_details[$product_id].'-'.$value->material;
								}
								$returnProducts[]=$product_id;
							}

							if($plant!=$value->plant)
							{
								$lidetails=DB::table('locations')->where('erp_code',$value->plant)->pluck('location_name','location_id')->toarray();
								if(count($lidetails)==0){
									throw new Exception(' Some locations are not available in Eseal', 1);
								} else {
									$plant=$value->plant;
									$lid=key($lidetails); 
									$loc_name=strpos($lidetails[$lid],$material_code)?$lidetails[$lid]:$lidetails[$lid].'-'.$value->plant;
								}
							}
							$newPo=0;
							$orderExists=DB::table('production_orders')->where('product_id',$product_id)->where('erp_doc_no',$value->order_number)->count('id');
							if($orderExists==0){
								if($createOrder && trim($docNo)==trim($value->order_number)){
									$orderInsert[]=['product_id'=>$product_id,'location_id'=>$lid,'eseal_doc_no'=>$newPo,'erp_doc_no'=>$value->order_number,'order_uom'=>$value->order_uom,'qty'=>$value->order_quantity,'manufacturer_id'=>$this->mfg_id,'is_confirm'=>0,'remarks'=> $remarks,'is_eseal'=>1,'is_erp'=>0];
								} else {
									$orderInsert[]=['product_id'=>$product_id,'location_id'=>$lid,'eseal_doc_no'=>$newPo,'erp_doc_no'=>$value->order_number,'order_uom'=>$value->order_uom,'qty'=>$value->order_quantity,'manufacturer_id'=>$this->mfg_id,'is_confirm'=>0,'remarks'=>'','is_eseal'=>0,'is_erp'=>1];
								}
								
							}

						$convet = new Conversions();
						$t_qty=$convet->getUom($product_id,$value->order_quantity,$value->order_uom);

							/*$pallet_capacity_ea=$convet->getUom($product_id,1,'PAL','EA');
							$pallet_capacity_ouom_ea=$convet->getUom($product_id,1,$value->order_uom,'EA');
							$pallet_capacity_ouom=$convet->getUom($product_id,1,'PAL',$value->order_uom);
							echo "<pre>";
							print_r($value->order_uom);
							echo "<br>";
							print_r($pallet_capacity_ea);
							echo "<br>";
							print_r($pallet_capacity_ouom_ea);
							echo "<br>";
							print_r($pallet_capacity_ouom);
							echo "<br>";
							$value['uom_capacity']=$convt->getUom($value['product_id'],1,$value['order_uom'],'EA');
							exit;*/
							// $returnOrders[]=['product_id'=>$product_id,'location_id'=>$lid,'order_no'=>(int) $value->order_number,'order_uom'=>$value->order_uom,'qty'=>$value->order_quantity,'poqty'=>$value->order_quantity,'ís_eseal'=>0,'loc_name'=>$loc_name,'product_name'=>$product_name,'qty'=>$t_qty,'ean'=>$ean,'pallet_capacity'=>$convet->getUom($product_id,1,'PAL',$value->order_uom),'uom_capacity'=>$convet->getUom($product_id,1,$value->order_uom,'EA')];
						}
						$insert=DB::table('production_orders')->insert($orderInsert);

						//*************price insert************
						$material_code='';
						$plant='';
						$lid='';
						$product_id=0;
						$priceInsert=[];
						foreach ($result->data->price as $key => $value) {
							$value->material = ltrim($value->material, '0');
							if($material_code!=$value->material)
							{
								$product_id=DB::table('products')->where('material_code',$value->material)->value('product_id');
								if(!$product_id){
									throw new Exception(' Some products are not available in Eseal price insert', 1);
								} else $material_code=$value->material;
							}

							if($plant!=$value->plant)
							{
								$lid=DB::table('locations')->where('erp_code',$value->plant)->value('location_id');
								if(!$lid){
									throw new Exception(' Some locations are not available in Eseal', 1);
								} else $plant=$value->plant;
							}

							$priceExists=DB::table('price_lot')->where('product_id',$product_id)->where('location_id',$lid)->where('price_lot',$value->price_lot)->count('id');
							if($priceExists==0){
								$priceInsert[]=['price_lot'=>$value->price_lot,'mrp'=>$value->mrp,'product_id'=>$product_id,'location_id'=>$lid];
							}
						}
						$insert=DB::table('price_lot')->insert($priceInsert);

						//*************sku_info insert************
						$material_code='';
						$product_id=0;
						$plant='';
						$lid='';
						$skuInsert=[];
						foreach ($result->data->sku_info as $key => $value) {
							$value->material = ltrim($value->material, '0');
							if($material_code!=$value->material)
							{
								$product_id=DB::table('products')->where('material_code',$value->material)->value('product_id');
								if(!$product_id){
									throw new Exception(' Some products are not available in Eseal', 1);
								} else $material_code=$value->material;
							}

							if($plant!=$value->plant)
							{
								$lid=DB::table('locations')->where('erp_code',$value->plant)->value('location_id');
								if(!$lid){
									throw new Exception(' Some locations are not available in Eseal', 1);
								} else $plant=$value->plant;
							}

							$skuExists=DB::table('sku_info')->where('product_id',$product_id)->where('sku_number',$value->sku_number)->where('location_id',$lid)->count('id');

							if($skuExists==0){
								$skuInsert[]=['sku_number'=>$value->sku_number,'case_config'=>$value->case_config,'product_id'=>$product_id,'location_id'=>$lid];
							}
						}
						$insert=DB::table('sku_info')->insert($skuInsert);

						
					}	else {
						throw new Exception('E- '.$result->message, 1);
					}
					DB::commit();
					} 
				catch(Exception $e)
				{
						$status = 0;
						$message = $e->getMessage();
						DB::rollBack();
						return json_encode(['Status'=>$result->status,'Message'=>$message]);

				}
				}
				$esealOrders=DB::table('production_orders as po')
							->join('products as p','p.product_id','=','po.product_id')
							->join('locations as l','l.location_id','=','po.location_id')
							->select(['po.product_id','po.location_id',DB::raw("DATE_FORMAT(po.timestamp, '%d-%m-%Y %H-%i-%s') as date"),DB::raw('IF(po.erp_doc_no = 0, po.eseal_doc_no, po.erp_doc_no) as order_no'),'po.order_uom','po.qty as poqty','po.is_eseal','l.location_name as loc_name','p.name as product_name','p.material_code','l.erp_code','p.ean as ean'])
							->where('p.material_code',$objectId)
							->orWhere('p.ean',$objectId)
							->where('l.erp_code',$plantId)
							->where('po.is_confirm',0)
							->where('po.is_erp',0)
							->where('po.is_eseal',1)
							->orderBy('po.timestamp','desc')
							->get()
							->toarray();
// return json_encode(['Status'=>1,'Message'=>'Server: '.$message,'Data'=>$esealOrders]);
							/*->get(['po.product_id','po.location_id',,'po.eseal_doc_no as order_no','po.order_uom','po.qty as poqty','po.is_eseal','l.location_name as loc_name','p.name as product_name','p.material_code','l.erp_code','p.ean as ean'])->toarray();*/
				$esealOrders=json_decode(json_encode($esealOrders),1);
				foreach ($esealOrders as $key => $value) {
					if(!strpos($value['product_name'],$value['material_code']))
						$value['product_name']=$value['product_name'].'-'.$value['material_code'];
					if(!strpos($value['loc_name'],$value['erp_code']))
						$value['loc_name']=$value['loc_name'].'-'.$value['erp_code'];
					unset($value['material_code']);
					unset($value['erp_code']);
					// $value['loc_erp_code']=$plantId;
					$convt=new Conversions();
					$value['qty']=$value['poqty'];
					$value['ean']=$value['ean'];
					$value['qty']=$convt->getUom($value['product_id'],$value['poqty'],$value['order_uom']);
//					$value['pallet_capacity_ea']=$convt->getUom($value['product_id'],1,'pal');
					$value['pallet_capacity']=$convt->getUom($value['product_id'],1,'PAL',$value['order_uom']);
					$value['uom_capacity']=$convt->getUom($value['product_id'],1,$value['order_uom'],'EA');

					$returnOrders[]=$value;
				}
				$product_id=DB::table('products')->where('material_code',$objectId)->value('product_id');
				//$conversions=DB::table('conversions')->where('product_id',$product_id)->get()->toarray();
				$conversions=[];
				$price_lot_master=DB::table('price_lot')->where(array('product_id'=>$product_id,'location_id'=>$locationId))->get(['price_lot','mrp']);
				$sku_master=DB::table('sku_info')->where(array('product_id'=>$product_id,'location_id'=>$locationId))->get(['sku_number','case_config']);
				if(count($returnOrders)>0){
					$message="Order retrieved successfully";
					return json_encode(['Status'=>1,'Message'=>'S-: '.$message,'Data'=>$returnOrders,'docNo'=>$docNo,'conversions'=>$conversions,'loc_erp_code'=>$plantId,'Price_lot_master'=>$price_lot_master,'Sku_Master'=>$sku_master]);
				} else 					
					return json_encode(['Status'=>0,'Message'=>'S-: No orders found']);
				
                 break;  
				case "PORDER":
		/*			$poDetails=DB::table('production_orders')->where('erp_doc_no',$po_number)->orWhere('eseal_doc_no',$po_number)->get()->toarray(); */
				$product_id=0;
				$esealOrders=DB::table('production_orders as po')
							->join('products as p','p.product_id','=','po.product_id')
							->join('locations as l','l.location_id','=','po.location_id')
							->where('po.is_confirm',0)
							->where(function ($query) use ($objectId) {
    							$query->where('erp_doc_no', '=', $objectId)
          							 ->orWhere('eseal_doc_no', '=', $objectId);
							})
							->get(['po.product_id','po.location_id','po.eseal_doc_no as order_no','po.order_uom','po.qty as poqty','po.is_eseal','l.location_name as loc_name','p.name as product_name','p.material_code','l.erp_code'])->toarray();
				$esealOrders=json_decode(json_encode($esealOrders),1);
				foreach ($esealOrders as $key => $value) {
					$product_id=$value['product_id'];
					if(!strpos($value['product_name'],$value['material_code']))
						$value['product_name']=$value['product_name'].'-'.$value['material_code'];
					if(!strpos($value['loc_name'],$value['erp_code']))
						$value['loc_name']=$value['loc_name'].'-'.$value['erp_code'];
					
						$convet = new Conversions();
						$value['qty']=$convet->getUom($product_id,$value['poqty'],$value['order_uom']);
					
					unset($value['material_code']);
					unset($value['erp_code']);
					$returnOrders[]=$value;
				}
				$conversions=DB::table('conversions')->where('product_id',$product_id)->get()->toarray();
				
				if(count($returnOrders)>0){
					$message="Order retrieved successfully";
					return json_encode(['Status'=>1,'Message'=>'S-ver: '.$message,'Data'=>$returnOrders,'conversions'=>$conversions]);
				} else 					
					return json_encode(['Status'=>0,'Message'=>'S-: No orders found']);
				break;

				case "DELIVERYDETAILS":

					$cancelled = trim($this->_request->input('cancelled'))?1:0;
				//where('is_sto',0)->	DB::enableQueryLog();
				$delivery=DB::table('delivery_master as dm')->leftJoin('locations as l1','l1.location_id','=','dm.frm_location')->leftJoin('locations as l2','l2.location_id','=','dm.to_location');
			
				if($cancelled)
				$delivery=$delivery->where('action_code','>',4);

				
				else
				$delivery=$delivery->where('action_code','<=',4);
				$delivery=$delivery->where('document_no',$this->_request->input('object_id'))->select('dm.id','dm.document_no','dm.frm_location','dm.to_location','dm.receving_location','dm.is_sto','dm.manufacturer_id','dm.user_id','dm.shipment_no','dm.delivery_shipment_flag','dm.doc_date','dm.type','dm.sto_no','dm.is_processed','l1.location_name as frm_location_name','dm.action_code as action_code','l2.location_name as to_location_name')->get()->toArray();
				

	
				if(count($delivery)){
				$delivery=json_decode(json_encode($delivery[0]),true);	
				} else {
					throw new Exception('Delivery Not available');
				}
				$wmsArray=[1,2,5,6];
				if(in_array($delivery['action_code'],$wmsArray)){
					$delivery['is_wms_enabled']="true";
				}else{
					$delivery['is_wms_enabled']="false";
				}
				/*$wms= DB::table('delivery_master')->where('id',$delivery['id'])->whereIn('action_code',[1,5])->count();*/
				/*if($wms ==1){
					$delivery['is_wms_enabled']="true";
				}else{
					$delivery['wms_enabled']="false";
				}*/
				$delivery['items']=DB::table('delivery_details as dd')->join('products as p','p.product_id','=','dd.product_id')
				->where('ref_id',$delivery['id'])
				->get(['dd.id','dd.ref_id','dd.product_id','dd.line_item_no','dd.qty','dd.returnable_qty','dd.ltrs','dd.kg','dd.priority', 'dd.src_stor_type','dd.src_stor_sec','dd.src_bin','dd.dest_stor_sec','dd.dest_stor_type','dd.dest_bin','dd.to_no','dd.to_line_no','dd.batch_no','p.name','p.description','p.material_code','p.ean'])->toArray();
		return json_encode(['Status'=>1,'Message'=>'Data inserted succesfully','Data'=>$delivery]);
					break;
			case "Cancelled";
						//echo "hai";exit;
						$delivery=DB::table('delivery_master as dm')->leftJoin('locations as l1','l1.location_id','=','dm.frm_location')->leftJoin('locations as l2','l2.location_id','=','dm.to_location');
						$delivery=$delivery->where('document_no',$this->_request->input('object_id'))->where('action_code','=',5)->get(['dm.id as id','dm.document_no','dm.frm_location','dm.to_location','dm.receving_location','dm.is_sto','dm.manufacturer_id','dm.user_id','dm.shipment_no','dm.delivery_shipment_flag','dm.doc_date','dm.type','dm.sto_no','dm.is_processed','l1.location_name as frm_location_name','dm.action_code as action_code','l2.location_name as to_location_name'])->toArray();
						$delivery=json_decode(json_encode($delivery[0]),true);
				 		// print_r($delivery);exit;
						$delivery['items']=DB::table('delivery_details as dd')->join('products as p','p.product_id','=','dd.product_id')
				->where('ref_id',$delivery['id'])
				->get(['dd.id as id','dd.ref_id','dd.product_id','dd.line_item_no','dd.qty','dd.returnable_qty','dd.ltrs','dd.kg','dd.priority', 'dd.src_stor_type','dd.src_stor_sec','dd.src_bin','dd.dest_stor_sec','dd.dest_stor_type','dd.dest_bin','dd.to_no','dd.to_line_no','dd.batch_no','p.name','p.description','p.material_code','p.ean'])->toArray();

				$tp_no=DB::table('tp_attributes as ta')->where('ta.value',$this->_request->input('object_id'))->get(['tp_id']);
				 // echo "<pre/>hhh";print_r($tp_no);exit;
				$iots1=DB::table('tp_data as  td')->where('td.tp_id',$tp_no[0]->tp_id)->pluck('level_ids')->toArray();


				$iddd[] = implode(',',$iots1);
				$kkk= $iddd[0];

				//echo $kkk;exit;
				// $iots1[]=$iots;
				// foreach ($iots as $iot ) {
				$eseal_ids=DB::table('eseal_'.$mfg_id.' as e')->whereIn('e.primary_id',explode(',',$kkk))->get(['primary_id','batch_no','pkg_qty'])->toArray();
				// }

//echo "<pre/>";print_r($eseal_ids);exit;

				

					return json_encode(['Status'=>1,'Message'=>'Data Retrieved succesfully','Data'=>$delivery,'eseal'=>$eseal_ids]);
					break;
			case "po_packed":

			$product_id=DB::table('products')->where(function($query) use($objectId){
							$query->where('material_code', '=', $objectId)
							->orWhere('ean', '=', $objectId);
						})->value('product_id');

			$packed_po=DB::table('eseal_'.$mfg_id.' as e')
						->join('products as p','p.product_id','=','e.pid')
						->groupby('e.po_number')
						->where('e.pid',$product_id)
						->where('is_confirmed','=','0')
						->where('po_number','!=','')
						->orderBy('e.eseal_id','desc')
						->get(['po_number as order_no'])
						->toArray();
						if(count($packed_po)>0){
				return json_encode(['Status'=>1,'Message'=>'Packed PO Retrieved Succesfully','Data'=>$packed_po]);
				}
				else 					
					return json_encode(['Status'=>0,'Message'=>'S-:No PO packed against material code.']);
				
			break;						

		case "putaway":	

					$grn_doc=DB::table('putaway_queue')
						->where(function($query) use($objectId){
							$query->where('to_no', '=', $objectId)
							->orWhere('document_no', '=', $objectId);
						})->value('document_no');
						//echo $objectId;exit;
					/*if($grn_doc==0){
						throw new Exception("No Grn against TO", 1);
						
					}*/	
					$putaway_data=DB::table('putaway_queue as pq')
					->join('products as p','p.product_id','=','pq.product_id')
						->where(function($query) use($objectId){
							$query->where('to_no', '=', $objectId)
							->orWhere('document_no', '=', $objectId);
						})
						/*->where('to_no',$objectId)
						->orWhere('document_no',$objectId)*/
						->where('status',0)
						->get(['document_no','document_no','warehouse_no','to_no','line_item','qty','p.description','p.material_code','batch','src_stor_type','src_stor_sec','src_bin','dest_stor_type','dest_stor_sec','dest_bin','status'])
						->toArray();
					if(count($putaway_data)==0){
						throw new Exception("No data against GRN or TO number");
					}
					else {
						$data=$putaway_data;
					}		

					$tp_no=DB::table('tp_attributes as ta')->where('ta.reference_value',$grn_doc)->get(['tp_id'])->toArray();
					if(count($tp_no)==0){
						$iots1=DB::table('Importpo_mapping as  im')->where('im.grn_number',$grn_doc)->pluck('IOT')->toArray();
				  	
					$iddd[] = implode(',',$iots1);
					$kkk= $iddd[0];
					$eseal_ids=DB::table('eseal_'.$mfg_id.' as e')->join('products as p','p.product_id','=','e.pid')->whereIn('e.primary_id',explode(',',$kkk))->get(['e.primary_id','p.description','material_code','e.batch_no','e.pkg_qty'])->toArray();

					}else{
					$iots1=DB::table('tp_data as  td')->where('td.tp_id',$tp_no[0]->tp_id)->pluck('level_ids')->toArray();
				  	
					$iddd[] = implode(',',$iots1);
					$kkk= $iddd[0];
					$eseal_ids=DB::table('eseal_'.$mfg_id.' as e')->join('products as p','p.product_id','=','e.pid')->whereIn('e.primary_id',explode(',',$kkk))->get(['e.primary_id','p.description','material_code','e.batch_no','e.pkg_qty'])->toArray();
					}
											
				return  json_encode(['Status'=>1,'Message'=>'Putaway TO or GR details retrieved ','Data'=>$data,'iots'=>$eseal_ids]);		
		break;



					case "PO":
					$data = ['TOKEN' => $token, 'PO' => $objectId];
					$method = 'Z0049_GET_PO_DETAILS_SRV';
					$method_name = 'PURCHASE';
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
			 			if ($xml_array['HEADER']['Status'] == 1 )
						{
							$query1->update(['is_active'=>1,'response'=>$documentData,'plant_id'=>(int)$xml_array['DATA']['VENDOR']]);
							$message = 'Response Updated succesfully';
						}else{
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
					                     
				case "PO_GRN":
					$data = ['TOKEN' => $token, 'DOCUMENT' => $objectId];					
					$method = 'Z029_ESEAL_GET_GRN_DATA_SRV';
					$method_name = 'GRN_OUTPUT';
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
						if (isset($data['tag']) && $data['tag'] == 'D:GET_GRN')
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
                        if($xml_array['HEADER']['Status'] == 1)
						{
							$query1->update(['is_active'=>1,'response'=>$documentData]);
							$message = 'PO_GRN DETAILS updated successfully';
						}else{
                       		throw new Exception("PO_GRN DETAILS not updated.SAP response negative");
						}
					}					
                    
                    if($movement_type == 0){
					//Calling the BindGrnData API from Eseal
					$request = Request::create('scoapi/bindGrnData', 'POST', array('module_id' => $this->_request->input('module_id'), 'access_token' => $this->_request->input('access_token'), 'grn_no' => $objectId,'transitionTime'=> $this->getDate()));
					$originalInput = Request::input(); //backup original input
					Request::replace($request->input());
					$res2 = Route::dispatch($request)->getContent();
					$result = json_decode($res2);
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
					
                    $transitionId = DB::table('transaction_master')->where(['name'=>'Reverse PGI','manufacturer_id'=>$mfg_id])->value('id');

                    if(empty($transitionId))
                    	throw new Exception('Reverse PGI transaction is not created');

					$request = Request::create('scoapi/reverseDelivery', 'POST', array('module_id' => $this->_request->input('module_id'), 'access_token' => $this->_request->input('access_token'), 'delivery' => $objectId,'transitionTime'=> $this->getDate(),'transitionId'=>$transitionId,'plant_id'=>$plantId,'movement_type'=>$movement_type));
					$originalInput = Request::input(); //backup original input
					Request::replace($request->input());
					////Log::info($request->input());
					$res = Route::dispatch($request)->getContent();
					$result = json_decode($res,true);

					if($result['Status'] == 0)
						throw new Exception($result['Message']);
					else
						$message = $result['Message'];

					}
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
				
					case "mulTest": 
					return json_encode(['Status'=>1,'Message'=>'S-: mulTest']);
					break;
					// Import PO Qr generation
					case "importPO":
					
					$docNumber = $this->_request->input('doc_number');
					$poFlag = $this->_request->input('is_po_number');
					$params = '';
					$bodyData = '';
					if(is_null($poFlag)){
						throw new Exception("mandatory Field PO/IBD flag", 1);
					}
					if($poFlag == false){
						if(!$docNumber){
							throw new Exception('mandatory Field idb number', 1);
						}
						$params = 'idb_number='.$docNumber;
						$bodyData = array('IHeader' => array('Vbeln' => $docNumber));
					}
					if($poFlag == true){
						if(!$docNumber){
							throw new Exception('mandatory Field po number', 1);
						}
						$params = 'po_number='.$docNumber;
						$bodyData = array('IHeader' => array('Ebeln' => $docNumber));
					}
					
					$method = 'poQRGeneration';
					$methodType='GET';
					$result=$this->erp->request($method,$params,0,$methodType);
					// return json_encode(['Status'=>$status,'Message'=>'S-: Working fine', 'Data' => json_encode($result)]);
					$result=json_decode($result);
					if (empty($result)){
						throw new Exception("Please try after sometime", 1);
					}
					if($result->EStatus->Status == 0){
						throw new Exception($result->EStatus->Message, 1);
					}
					$plant_location_id = DB::table('locations')->where('erp_code', $result->EPodetails->item[0]->Plant)->value('location_id');
					 if($plant_location_id != $locationId){
					 	throw new Exception('PO/IBD is not belong with this plant location', 1);
					 }

					//*************conversion insert************
					$material_code='';
					$product_id=0;
					$conInsert=[];
					foreach ($result->EConversion->item as $key => $value) {
						$value->Material = ltrim($value->Material, '0');
						if($material_code!=$value->Material)
						{
							$product_id=DB::table('products')->where('material_code',$value->Material)->value('product_id');
							if(!$product_id){
								throw new Exception(' Some products are not available in Eseal', 1);
							} else $material_code=$value->Material;
						}

						$alt_uom=DB::table('conversions')->where('product_id',$product_id)->where('alt_uom',$value->Auom)->count('id');
						
						if($alt_uom==0){
							$conInsert[]=['alt_uom'=>$value->Auom,'alt_quantity'=>$value->ConvQt,'product_id'=>$product_id,'base_quantity'=>$value->QtyBum,'base_uom'=>$value->Bum];
						}
					}
					$insert=DB::table('conversions')->insert($conInsert);

					$importPoInsert = [];
					$resposeData = [];
					$convt = new Conversions();
					
					foreach ($result->EPodetails->item as $key => $value) {
						$value->Material = ltrim($value->Material, '0');
						$impotID =DB::table('ImportPO')
									->where('po_number', $value->Pono)
									->where('material', $value->Material)
									->value('id');
                        if($poFlag == false){
                            $impotID =DB::table('ImportPO')
                                        ->where('idb_number', $value->Ibdno)
                                        ->where('material', $value->Material)
                                        ->value('id');
                        }
						$productData = DB::table('products')->where('material_code',$value->Material)->first();
						$product_id = $productData->product_id;
						$ean = $productData->ean;
			   			$pkgQty = $convt->getUom($product_id, $value->OpenQa, $value->OrderUom, 'PAL');
			   			$palQty = DB::table('conversions')->where('product_id', $product_id)->where('alt_uom', 'PAL')->where('base_uom', $value->BaseUom)->value('base_quantity');

						if (!$impotID)
						{
							$importPoInsert[] = [
								'po_number'		=> $value->Pono,
								'po_item'		=> $value->Poitem,
								'idb_number'	=> $value->Ibdno,
								'idb_item'		=> $value->IbdItem,
								'material'		=> $value->Material,
								'material_des'	=> $value->MaterialDes,
								'product_id'	=> $product_id,
								'location_id'	=> '',
								'vender'		=> $value->Vendor,
								'open_quantity'	=> $value->OpenQa,
								'order_uom'		=> $value->OrderUom,
								'base_uom'		=> $value->BaseUom,
								'remarks'		=> $value->Remarks,
							];
						}
						$resposeData[] = [
							'line_items'	=>($poFlag == true ? $value->Poitem : $value->IbdItem),
							'po_item'			=> $value->Poitem,
							'idb_item'			=> $value->IbdItem,
							'material'			=> $value->Material,
							'material_des'		=> $value->MaterialDes,
							'product_id'		=> $product_id,
							'location_id'		=> '',
							'storage_location'	=> $value->StorageLoc,
							'vender'			=> $value->Vendor,
							'open_quantity'		=> $value->OpenQa,
							'order_uom'			=> $value->OrderUom,
							'base_uom'			=> $value->BaseUom,
							'remarks'			=> $value->Remarks,
							'scan_qty'			=> $pkgQty,
							'pallet_qty'		=> $palQty,
							'ean'				=> $ean
						];
					}
					$insert=DB::table('ImportPO')->insert($importPoInsert);

					$StockType = array(
						'0'	=> array('stock_code' => '', 'stock_des'	=> 'Unrestricted Use'),
						'1'	=> array('stock_code' => '2', 'stock_des'	=> 'Quality Inspection'),
						'2'	=> array('stock_code' => '3', 'stock_des'	=> 'Blocked'),
						);

					$finalData = array(
						'line_items'	=> $resposeData,
						'price'			=> $result->EPricelot->item,
						'sku_info'		=> $result->ESku->item,
						'conversion'	=> $result->EConversion->item,
						'stock_type'	=> $StockType);

					return json_encode(['Status'=>1,
						'Message'		=>'S-: '.$result->EStatus->Message,
						'po_number'		=> ($poFlag == true ? $docNumber : ''),
						'idb_number'	=> ($poFlag == false ? $docNumber : ''),
						'vendor'		=> $result->EPodetails->item[0]->Vendor,
						'Description'	=> $result->EPodetails->item[0]->Description,
						'Data'			=> $finalData]);
					// return json_encode(['Status'=>1,'Message'=>'S-: '.$result->message, 'Data' => $result->po_details]);
					break;

			}
		} catch(Exception $e)
	{
			$status = 0;
			////Log::info($e->getMessage());
			$message = $e->getMessage();
	}
	$endTime = $this->getTime();
	return json_encode(['Status'=>$status,'Message'=>'S-: '.$message]);
}

	public function productsByLocation($data)
	{
		try
		{
			//Log::info($data);
			$status =0;
			$prod = array();
			$location_id = $data['location_id'];
			
			$mfg_id = $this->roleAccess->getMfgIdByToken($data['access_token']);
			if(empty($location_id))
			{
				throw new Exception('Parameters Missing.');
			}
			$business_unit_id =  Location::where('location_id',$location_id)->value('business_unit_id');
			
            $result = DB::table('product_locations')
			->join('products','products.product_id','=','product_locations.product_id')            
			->where('product_locations.location_id',$location_id);
			if($business_unit_id != 0)	
				$result->where('products.business_unit_id',$business_unit_id);					
			$result = $result->groupBy('products.group_id')				
						     ->select('products.group_id')
						     ->get()->toarray();	

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
				$attribute_set_id = DB::table('product_attributesets')->where(['product_group_id'=>$res->group_id,'location_id'=>$location_id])->value('attribute_set_id');		
				$prodCollection = DB::table('products as pr')							
										->join('master_lookup as ml','ml.value','=','pr.product_type_id') 
										->join('product_locations as pl' ,'pr.product_id','=','pl.product_id')
										->where(['pr.group_id'=>$res->group_id,'pl.location_id'=>$location_id])
										->distinct()
										->select(['pr.product_id','ml.name as product_type','pr.group_id','pr.name','pr.title','pr.description','pr.image','pr.sku','pr.material_code','pr.is_traceable','is_batch_enabled','is_backflush','is_serializable','inspection_enabled','pr.ean','pr.field1','pr.field2','pr.field3','pr.field4','pr.field5','pr.model_name','pr.uom_unit_value'])->get()->toarray();
				$productInfo = array();
				if(count($prodCollection)){
					foreach($prodCollection as $collection){
					$group_name = DB::table('product_groups')->where(['group_id'=>$collection->group_id,'manufacture_id'=>$mfg_id])->value('name');	
					$prodInfo = ['product_id'=>(string)$collection->product_id,'name'=>$collection->name,'sku'=>$collection->sku,'title'=>$collection->title,'description'=>$collection->description,'material_code'=>$collection->material_code,'product_type_name'=>$collection->product_type,'is_traceable'=>$collection->is_traceable,'group_id'=>(int)$collection->group_id,'is_serializable'=>$collection->is_serializable,'is_batch_enabled'=>$collection->is_batch_enabled,'is_backflush'=>$collection->is_backflush,'inspection_enabled'=>$collection->inspection_enabled,'field1'=>$collection->field1,'field2'=>$collection->field2,'field3'=>$collection->field3,'field4'=>$collection->field4,'field5'=>$collection->field5,'model_name'=>$collection->model_name,'group_name'=>$group_name,'uom_value'=>$collection->uom_unit_value,'ean'=>$collection->ean];
					
					$image = $collection->image;

					$levelCollection = DB::table('product_packages as pp')
										   ->join('master_lookup','master_lookup.value','=','pp.level')
										   ->where('pp.product_id',$collection->product_id)
										   ->get(array(DB::raw('substr(master_lookup.name,-1) as level'),'master_lookup.name','master_lookup.description','pp.quantity as capacity','pp.height','pp.stack_height','pp.length','pp.width','pp.weight','pp.is_shipper_pack','pp.is_pallet'))->toarray();
				
                    $staticCollection = DB::table('attributes as attr')
							               ->join( 'product_attributes as pa','pa.attribute_id','=','attr.attribute_id')
							               ->where('pa.product_id',$collection->product_id)
							               ->orderBy('sort_order')
							               ->get(['attr.attribute_id','attr.text as name','attr.attribute_code','attr.input_type','pa.value as default_value','attr.is_required','attr.validation',DB::raw('0 as is_searchable')])->toarray();
					$po_attributes=[];
					$appendStaticAttributes=DB::table('attributes as attr')->where('attribute_type',9)->get(['attr.attribute_id','attr.text as name','attr.attribute_code','attr.input_type','attr.default_value as default_value','attr.is_required','attr.validation',DB::raw('0 as is_searchable')])->toArray();
					foreach ($appendStaticAttributes as $key => $value) {
/*						$options=(DB::table($value->attribute_code)->where('product_id',$collection->product_id)->pluck($value->default_value)->toarray());	*/
						$tableName = ($value->attribute_code == 's_price_lot' ? 'price_lot' : $value->attribute_code);
						$tableName = ($value->attribute_code == 's_sku_info' ? 'sku_info' : $value->attribute_code);
 						$options=array_unique(DB::table($tableName)->where('product_id',$collection->product_id)->pluck($value->default_value)->toarray());	
						 //print_r($options);exit;
						$value->default_value='';			
						$value->options=$options;
						$po_attributes[]=$value;			
					}

					$productInfo[] = ['product_info'=>$prodInfo,'image'=>$image,'static_attributes'=>$staticCollection,'po_attributes'=>$po_attributes,'levels'=>$levelCollection];
					}

					$attributeCollection = DB::table('attributes as attr')
											  ->join('attribute_set_mapping as asm','asm.attribute_id','=','attr.attribute_id')										  
											  ->where(['asm.attribute_set_id'=>$attribute_set_id])
											  ->orderBy('asm.sort_order','asc')
											  ->get(['attr.attribute_id','attr.text as name','attr.attribute_code','attr.input_type','attr.default_value','attr.is_required','attr.validation','asm.is_searchable'])->toarray();

                    $attrCnt = count($attributeCollection);

                    for($i=0;$i < $attrCnt;$i++){
                    	if($attributeCollection[$i]->input_type == 'select'){
                         $defaults=  DB::table('attribute_options')->where('attribute_id',$attributeCollection[$i]->attribute_id)->pluck('option_value')->toarray();
                         $attributeCollection[$i]->options = $defaults;
                    	}
                    }       
					$prod[] = ['products'=>$productInfo,'late_attributes'=>$attributeCollection];
				}
			}
		}
		catch(Exception $e){
			$message = $e->getMessage();
		}
		return json_encode(['Status'=>$status,'Message' =>'S-: '.$message,'Data'=>$prod]);
	}


	public function DownloadEsealByLocationId()
	{
		$startTime = $this->getTime();
		try
		{
			$status = 0;
			$message = '';
			$po_number = rtrim(ltrim($this->_request->input('po_number')));
			$lid = trim($this->_request->input('srcLocationId'));
			$qty = trim($this->_request->input('qty'));
			$transitionTime = $this->_request->input('transitionTime');
			$attributes = trim($this->_request->input('attributes'));
			$packingValues = trim($this->_request->input('packingValues'));
			$ignoreMultiPackingForPo = trim($this->_request->input('ignoreMultiPackingForPo'));
			$level = trim($this->_request->input('level'));
			$transitionId = trim($this->_request->input('transitionId'));
			$unpackedItems = trim($this->_request->input('unpackedItems'));
			$str = '';
			$data = array();
			$locationObj = new Location();
			$mfgId = $locationObj->getMfgIdForLocationId($lid);
			$str1 ='';
			if(!empty($po_number) && $level ==0)
			{
				$exists = DB::table('eseal_'.$mfgId)->where(['po_number'=>$po_number,'level_id'=>0])->select('primary_id')->get()->toarray();			
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
								 ->get()->toarray();
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
					return json_encode(['Status'=>$status,'Message'=>'S-: '.$message,'Codes'=>$str1]);
				}				 
			}

			DB::beginTransaction();
			if(!empty($mfgId))
			{
				if(!empty($level) && $level >0 && !empty($po_number))
				{
				   try
				   {    
				   	$poDetails=DB::table('production_orders')->where(function($query) use ($po_number){
				   		$query->where('erp_doc_no',$po_number)->orWhere('eseal_doc_no',$po_number);
				   	})->get()->toarray();  
						if(count($poDetails)>0)
							$pid = $poDetails[0]->product_id;
						else
							throw new Exception($response->Message);

						$inputData=array('module_id'=> $this->_request->input('module_id'),'access_token'=>$this->_request->input('access_token'),'parentQty'=>$qty,'level'=>$level,'attributes'=>$attributes,'transitionTime'=>$transitionTime,'transitionId'=>$transitionId,'pid'=>$pid,'po_number'=>$po_number);
						$req = Request::create('scoapi/bindMapWithEseals', 'POST',$inputData);
						$originalInput=$this->_request->all();
						$this->_request->replace($req->all());
						$response = app()->handle($req);
						$response = $response->getContent();
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
					return json_encode(['Status'=>$status,'Message'=>'S-: '.$message,'Data'=>$data,'Codes'=>'']);
				}
				$esealBankTable = 'eseal_bank_'.$mfgId;
				if(is_numeric($qty) && $qty>0)
				{ 
					try
					{
						$download_token = DB::table('download_flag')->insertGetId(['update_time'=>date('Y-m-d H:i:s'),'user_id'=>0]); 
						DB::table($esealBankTable)->where(array('download_token'=>0))
						    ->take($qty)
						    ->update(array('location_id'=>$lid,'download_status'=>1,'download_token'=>$download_token));
						Log::info('download_token update into bank');
						$result = DB::table($esealBankTable)->select('id')
								  ->where(array('download_token'=>$download_token))
								  ->orderBy('serial_id','asc')->take($qty)->get()->toarray();
					}
					catch(PDOException $e)
					{
						throw new Exception($e->getMessage());
					}

					if(count($result) && count($result)==$qty)
					{
					  $idArr = array();
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
						$request = Request::create('scoapi/BindEseals', 'POST', array('module_id'=> $this->_request->input('module_id'),'access_token'=>$this->_request->input('access_token'),'ids'=>$str,'srcLocationId'=>$lid,'po_number'=>$po_number,'attributes'=>$attributes,'transitionTime'=>$transitionTime,'packingValues'=>$packingValues,'ignoreMultiPackingForPo'=>$ignoreMultiPackingForPo));
						$originalInput=$this->_request->all();
						$this->_request->replace($request->all());
						$response = app()->handle($request);
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
	Log::info(['Status' => $status, 'Message'=> $message, 'Codes' => $str]);
	return json_encode(['Status' => $status, 'Message' =>'S-: '.$message, 'Codes' => $str]);
	}


public function bindMapWithEseals(){
	$startTime = $this->getTime();
	try{
		$str=[];
		//DB::beginTransaction();
		$data ='';
		$parentQty = trim($this->_request->input('parentQty'));
		$child_Qty = trim($this->_request->input('childQty'));
		$locationId = $this->roleAccess->getLocIdByToken($this->_request->input('access_token'));
		$mfg_id = $this->roleAccess->getMfgIdByToken($this->_request->input('access_token'));
		$pid = trim($this->_request->input('pid'));
		$transitionId = trim($this->_request->input('transitionId'));
		$transitionTime =trim($this->_request->input('transitionTime'));
		$attributes = trim($this->_request->input('attributes'));
		$level = trim($this->_request->input('level')); 
		$po_number = trim($this->_request->input('po_number')); 
		$esealBankTable = 'eseal_bank_'.$mfg_id;
		
		Log::info(__FUNCTION__.' : '.print_r($this->_request->all(),true));

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
					 ->pluck('primary_id')->toarray();
					 $queries = DB::getQueryLog();
				   foreach($cartons as $carton)
				   {
					   $childs = DB::table('eseal_'.$mfg_id)->where('parent_id',$carton)->pluck('primary_id')->toarray();
					   $data[] = ['parent'=>$carton,'childs'=>$childs];
				   }
				   $status =2;
				   $message = 'Data already exists for the given PO number';
				   return json_encode(['Status'=>$status,'Message'=>'S-: '.$message,'Data'=>$data]); 
			}
		}
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
							 ->value('quantity');
		
		if(!$childQty){
			throw new Exception('Product Package not configured');
		}
		$qty=0;		
		if($level == 0){
			$qty = $parentQty;
		}
		else{
			if(!$child_Qty)
			   $qty = $parentQty+($parentQty * $childQty);
			else
			   $qty = $parentQty+($parentQty * $child_Qty);
		}
		$request = Request::create('scoapi/SaveBindingAttributes', 'POST', array('module_id'=> $this->_request->input('module_id'),'access_token'=>$this->_request->input('access_token'),'attributes'=>$attributes,'lid'=>$locationId,'pid'=>$pid));
		$originalInput = $this->_request->all();//backup original input
		$this->_request->replace($request->all());
		$res = app()->handle($request);
		$response = $res->getContent();
		$response = json_decode($response);		
		
		if($response->Status){
			$map_id = $response->AttributeMapId;
		} else{
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
			}
			$batch_no = $attrArr['batch_no'];
			$trakHistoryObj = new Trackhistory();
			$track = $trakHistoryObj->insertTrack($locationId,0, $transitionId, $transitionTime);
 		
				if($level == 0)
					$childLevel = 0;
				else
					$childLevel = $level-1;

				$download_token = DB::table('download_flag')->insertGetId(array('update_time'=>date('Y-m-d H:i:s')));
				
				$result = DB::table($esealBankTable.'')->where(array('download_token'=>0))->orderBy('serial_id','asc')->take($qty)->get(['id as primary_id',DB::raw($pid.' as pid'),DB::raw($map_id.' as attribute_map_id'),DB::raw($childLevel.' as level_id'),DB::raw($track.' as track_id'),DB::raw('"'.$batch_no.'" as batch_no'),DB::raw('"'.$transitionTime.'" as mfg_date')])->toarray();

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

		 DB::table($esealBankTable)->whereIn('id',$str)->update(['download_token'=>$download_token]);
		 
		  $result = json_encode($result);
		  $result = json_decode($result,true);

		  foreach($str as $id){
			$history[] = ['eseal_id'=>$id,'location_id'=>$locationId,'attribute_map_id'=>$map_id,'created_on'=>$transitionTime];
		  }

		  DB::beginTransaction();
		  //Bulk Insert into eseal_mfgid table.
		  $sql="insert into  eseal_".$mfg_id." (primary_id,pid,attribute_map_id,level_id,track_id,batch_no,prod_batch_no,mfg_date) (
		  	select `id` as `primary_id`, ".$pid." as pid, ".$map_id." as attribute_map_id, ".$childLevel." as level_id, ".$track." as track_id, '".$batch_no."' as batch_no, '".$batch_no."' as prod_batch_no, '".date("Y-m-d",strtotime($transitionTime))."' as mfg_date from eseal_bank_".$mfg_id." where download_token='".$download_token."')";
		  	DB::statement($sql);
		  	 $sql1="insert into  bind_history (eseal_id,location_id,attribute_map_id,created_on) (
		  	select `id` as `eseal_id`, ".$locationId." as location_id, ".$map_id." as attribute_map_id,'".date("Y-m-d",strtotime($transitionTime))."' as created_on from eseal_bank_".$mfg_id." where download_token='".$download_token."')";
		  		DB::statement($sql1);
		  
		  DB::table($esealBankTable)->whereIn('download_token',[$download_token])->update(['used_status'=>1,'download_status'=>1,'location_id'=> $locationId,'pid'=>$pid,'utilizedDate'=>$transitionTime]);
		  DB::commit();

		if($level > 0){
		   //Partitioning the array of ids into slices and chunks based on the parent level capacity.
		  if(!$child_Qty)
			  $arrChunk = array_chunk($ids,$childQty+1);
		  else
			  $arrChunk = array_chunk($ids,$child_Qty+1);
		  
		  	//Mapping Parent to Child.
			$data=[];
		  foreach($arrChunk as $chunk){

			$parent = $chunk[0];
			$childs = array_slice($chunk,1);
			$data[] = ['parent'=>$parent,'childs'=>$childs];
			$childs = implode(',',$childs);
			DB::table('eseal_'.$mfg_id)->where('primary_id',$parent)->update(['level_id'=>1]);
			//Mapping Parent to Child.
			$request = Request::create('scoapi/MapEseals', 'POST', array('module_id'=> $this->_request->input('module_id'),'access_token'=>$this->_request->input('access_token'),'parent'=>$parent,'ids'=>$childs,'srcLocationId'=>$locationId,'transitionTime'=>$transitionTime));
			$originalInput = $this->_request->all();//backup original input
			$this->_request->replace($request->all());
			$res = app()->handle($request);
			$response = $res->getContent();
			$response = json_decode($response);	
			if(!$response->Status){
				throw new Exception($response->Message);
			}
			
		} 
		
	}
	   $trArr =  DB::table('eseal_'.$mfg_id)
						   ->where('track_id',$track)
						   ->get(['primary_id as code','track_id'])->toarray();
	   
	   $trArr = json_encode($trArr);
	   $trArr1 = json_decode($trArr,true);
	   Track::insert($trArr1);                   
		if($level == 0){
		$data[] = ['childs'=>$ids];
	}
		$status =1;
		$message ='Process successfull';
	}
	catch(Exception $e){
		$status =0;
		$data = [];
		$message = $e->getMessage();
	}
	return json_encode(['Status'=>$status,'Message' =>'S-: '.$message,'Data'=>$data]);
}


public function bindMapWithEsealsCodes(){
	$startTime = $this->getTime();
	try{
		$str=[];
		//DB::beginTransaction();
		$data ='';
		$parentQty = trim($this->_request->input('parentQty'));
		$child_Qty = trim($this->_request->input('childQty'));
		$locationId = $this->roleAccess->getLocIdByToken($this->_request->input('access_token'));
		$mfg_id = $this->roleAccess->getMfgIdByToken($this->_request->input('access_token'));
		$pid = trim($this->_request->input('pid'));
		$transitionId = trim($this->_request->input('transitionId'));
		$transitionTime =trim($this->_request->input('transitionTime'));
		$attributes = trim($this->_request->input('attributes'));
		$level = trim($this->_request->input('level')); 
		$po_number = trim($this->_request->input('po_number')); 
		$esealBankTable = 'eseal_bank_'.$mfg_id;
		
		Log::info(__FUNCTION__.' : '.print_r($this->_request->all(),true));

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
					 ->pluck('primary_id')->toarray();
					 $queries = DB::getQueryLog();
				   foreach($cartons as $carton)
				   {
					   $childs = DB::table('eseal_'.$mfg_id)->where('parent_id',$carton)->pluck('primary_id')->toarray();
					   $data[] = ['parent'=>$carton,'childs'=>$childs];
				   }
				   $status =2;
				   $message = 'Data already exists for the given PO number';
				   return json_encode(['Status'=>$status,'Message'=>'S-: '.$message,'Data'=>$data]); 
			}
		}
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
							 ->value('quantity');
		$attrArr = json_decode($attributes,true);
		$quantity = $attrArr['s_quantity'];
		// $childQty = $quantity;
		
		if(!$childQty){
			throw new Exception('Product Package not configured');
		}
		$qty=0;		
		if($level == 0){
			$qty = $parentQty;
		}
		else{
			if(!$child_Qty)
			   $qty = $parentQty+($parentQty * $childQty);
			else
			   $qty = $parentQty+($parentQty * $child_Qty);
		}
		$request = Request::create('scoapi/SaveBindingAttributes', 'POST', array('module_id'=> $this->_request->input('module_id'),'access_token'=>$this->_request->input('access_token'),'attributes'=>$attributes,'lid'=>$locationId,'pid'=>$pid));
		$originalInput = $this->_request->all();//backup original input
		$this->_request->replace($request->all());
		$res = app()->handle($request);
		$response = $res->getContent();
		$response = json_decode($response);		
		
		if($response->Status){
			$map_id = $response->AttributeMapId;
		} else{
			throw new Exception($response->Message);
		}

		
		  if(json_last_error() != JSON_ERROR_NONE)
			 throw new Exception('Json not valid');

			if($level<2)
			{
				if(!array_key_exists('s_batch_no',$attrArr)){
					throw new Exception('Batch No not passed in Attribute List');
				}
			}
			$batch_no = $attrArr['s_batch_no'];
			
			$trakHistoryObj = new Trackhistory();
			$track = $trakHistoryObj->insertTrack($locationId,0, $transitionId, $transitionTime);
 		
				if($level == 0)
					$childLevel = 0;
				else
					$childLevel = $level-1;

				$download_token = DB::table('download_flag')->insertGetId(array('update_time'=>date('Y-m-d H:i:s')));
				
				$result = DB::table($esealBankTable.'')->where(array('download_token'=>0))->orderBy('serial_id','asc')->take($qty)->get(['id as primary_id',DB::raw($pid.' as pid'),DB::raw($map_id.' as attribute_map_id'),DB::raw($childLevel.' as level_id'),DB::raw($track.' as track_id'),DB::raw('"'.$batch_no.'" as batch_no'),DB::raw('"'.$transitionTime.'" as mfg_date')])->toarray();

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

		 DB::table($esealBankTable)->whereIn('id',$str)->update(['download_token'=>$download_token]);
		 
		  $result = json_encode($result);
		  $result = json_decode($result,true);

		  foreach($str as $id){
			$history[] = ['eseal_id'=>$id,'location_id'=>$locationId,'attribute_map_id'=>$map_id,'created_on'=>$transitionTime];
		  }

		  DB::beginTransaction();
		  //Bulk Insert into eseal_mfgid table.
		  $sql="insert into  eseal_".$mfg_id." (primary_id,pid,attribute_map_id,level_id,track_id,batch_no,prod_batch_no,mfg_date) (
		  	select `id` as `primary_id`, ".$pid." as pid, ".$map_id." as attribute_map_id, ".$childLevel." as level_id, ".$track." as track_id, '".$batch_no."' as batch_no, '".$batch_no."' as prod_batch_no, '".date("Y-m-d",strtotime($transitionTime))."' as mfg_date from eseal_bank_".$mfg_id." where download_token='".$download_token."')";
		  	DB::statement($sql);
		  	 $sql1="insert into  bind_history (eseal_id,location_id,attribute_map_id,created_on) (
		  	select `id` as `eseal_id`, ".$locationId." as location_id, ".$map_id." as attribute_map_id,'".date("Y-m-d",strtotime($transitionTime))."' as created_on from eseal_bank_".$mfg_id." where download_token='".$download_token."')";
		  		DB::statement($sql1);
		  
		  DB::table($esealBankTable)->whereIn('download_token',[$download_token])->update(['used_status'=>1,'download_status'=>1,'location_id'=> $locationId,'pid'=>$pid,'utilizedDate'=>$transitionTime]);
		  DB::commit();

		if($level > 0){
		   //Partitioning the array of ids into slices and chunks based on the parent level capacity.
		  if(!$child_Qty)
			  $arrChunk = array_chunk($ids,$childQty+1);
		  else
			  $arrChunk = array_chunk($ids,$child_Qty+1);
		  
		  	//Mapping Parent to Child.
			$data=[];
		  foreach($arrChunk as $chunk){

			$parent = $chunk[0];
			$childs = array_slice($chunk,1);
			$data[] = ['parent'=>$parent,'childs'=>$childs];
			$childs = implode(',',$childs);
			DB::table('eseal_'.$mfg_id)->where('primary_id',$parent)->update(['level_id'=>1]);
			//Mapping Parent to Child.
			$request = Request::create('scoapi/MapEseals', 'POST', array('module_id'=> $this->_request->input('module_id'),'access_token'=>$this->_request->input('access_token'),'parent'=>$parent,'ids'=>$childs,'srcLocationId'=>$locationId,'transitionTime'=>$transitionTime));
			$originalInput = $this->_request->all();//backup original input
			$this->_request->replace($request->all());
			$res = app()->handle($request);
			$response = $res->getContent();
			$response = json_decode($response);	
			if(!$response->Status){
				throw new Exception($response->Message);
			}
			
		} 
		
	}
	   $trArr =  DB::table('eseal_'.$mfg_id)
						   ->where('track_id',$track)
						   ->get(['primary_id as code','track_id'])->toarray();
	   
	   $trArr = json_encode($trArr);
	   $trArr1 = json_decode($trArr,true);
	   Track::insert($trArr1);

	   DB::table('eseal_'.$mfg_id)->whereIn('primary_id',$ids)->update(['pkg_qty'=>$quantity]);

		$status =1;
		$message ='Process successfull';
	   return json_encode(['Status'=> $status,'Message' =>'S-: '.$message,'Ids'=>$ids]);             
	}
	catch(Exception $e){
		$status =0;
		$data = [];
		$message = $e->getMessage();
	}
	return json_encode(['Status'=>$status,'Message' =>'S-: '.$message,'Data'=>$data]);
}

 public function MapEseals(){
	$startTime = $this->getTime();    
	  try{
		  $status = 0;
		  $message = 'Failed to map';

		$childs = trim($this->_request->input('ids'));
		$parent = trim($this->_request->input('parent'));
		$pid = trim($this->_request->input('pid'));
		$attributes = trim($this->_request->input('attributes'));
		$createParent = trim($this->_request->input('createParent'));
		//$locationId = trim($this->_request->input('srcLocationId'));
		$locationId = $this->roleAccess->getLocIdByToken($this->_request->input('access_token'));
		$removeMappedCodes = trim($this->_request->input('removeMappedCodes'));
		$mapParent = trim($this->_request->input('mapParent'));
		$isPallet = trim($this->_request->input('isPallet'));
		$transitionTime = trim($this->_request->input('transitionTime'));
		$flagJson = trim($this->_request->input('flagsJson'));
		$flagArr = json_decode($flagJson,true);
		$childsPacked = array();
		//$parentArray=explode(',',$parent);		
		if(!empty($childs) && $parent!='' && !empty($locationId)){

			$locationObj = new Location();
			$mfgId = $locationObj->getMfgIdForLocationId($locationId);

			  $splitChilds = explode(',', $childs);
			  $uniqueSplitChilds = array_unique($splitChilds);
			  $joinChilds = '\''.implode('\',\'', $uniqueSplitChilds).'\'';
			  $childCnt = count($uniqueSplitChilds);
			  //Log::info('$childCnt'.$childCnt);
			  
			  if(!empty($mfgId)){
		//		DB::beginTransaction();
				try{
				  $esealTable = 'eseal_'.$mfgId;
				  $esealBankTable = 'eseal_bank_'.$mfgId;

				  //$cnt = DB::table($esealBankTable)->where('issue_status', 1)->orWhere('download_status',1)->where('id', $parent)->count();
				  $cnt = DB::table($esealBankTable)->where('id',$parent)
							->where(function($query){
								$query->where('issue_status',1);
								$query->orWhere('download_status',1);
							})->count();

				//  Log::info(count($parent).' == '.$cnt);
				  if($cnt!=1){
					throw new Exception('Codes count not matching with code bank');
				  }
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
				                      ->pluck('primary_id')->toarray();				     				      
				      	
				      if(count($childsPacked)>0)	
				      	throw new Exception('The childs are already mapped with another IOT');				      
				  }	


				 jump:
				 $resultsCnt = DB::table($esealTable)->whereIn('primary_id', $uniqueSplitChilds)->select(DB::raw('distinct(primary_id) as cnt'))->get()->toarray();
				 //echo "<pre/>";print_r(count($resultsCnt));exit;
				  Log::info('resultsCnt '.count($resultsCnt));
				  $resultsCnt = count($resultsCnt);
				  if($resultsCnt > 0 ){

				  	 if($resultsCnt != $childCnt && (!isset($flagArr['ignoreInvalid']) || $flagArr['ignoreInvalid'] != 1) )
				  	 	throw new Exception('Child count not matching');

	                $pkg_qty = DB::table($esealTable)->whereIn('primary_id', $uniqueSplitChilds)->sum('pkg_qty');			  	

	                $storage_locations = DB::table($esealTable)->whereIn('primary_id', $uniqueSplitChilds)->distinct()->pluck('storage_location')->toarray();			  	
	                if(count($storage_locations) > 1)
	                	$storage_location = '';
	                else
	                	$storage_location = $storage_locations[0];

					log::info('pkg_qty :- ' .$pkg_qty);

					DB::table($esealTable)->whereIn('primary_id',$uniqueSplitChilds)->update(['parent_id'=> $parent]);
					//DB::table($esealTable)->where('primary_id',$parent)->update(['pkg_qty'=>$pkg_qty]);
					
					//Getting the maximum level_id of childs passed from eseal_mfgid table.	
					$maxLevelId = DB::table($esealTable)->whereIn('primary_id', $uniqueSplitChilds)->max('level_id');
					$prod_batch_no=DB::table($esealTable)->whereIn('primary_id', $uniqueSplitChilds)->max('prod_batch_no');

                    $distinctPIDCount = DB::table($esealTable)
							  ->where('parent_id', $parent)
							  ->where('pid','!=', 0)
							  ->select('pid')
							  ->groupBy('pid')->get()->toarray();
					
					//Checking whether the child is a finished product or not. 
					$isFinished =DB::table($esealTable)
								 ->join('products','products.product_id','=',$esealTable.'.pid')
								 ->join('master_lookup','master_lookup.value','=','products.product_type_id')
								 ->where('master_lookup.name','Finished Product')
								 ->whereIn($esealTable.'.primary_id',$uniqueSplitChilds)
								 ->value('id');
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
                          $req = Request::create('scoapi/SaveBindingAttributes', 'POST', array('module_id'=> $this->_request->input('module_id'),'access_token'=>$this->_request->input('access_token'),'attributes'=>$attributes,'lid'=>$locationId,'pid'=>$pid));
							$originalInput=$this->_request->all();
							$this->_request->replace($req->all());
							$res = app()->handle($req);
							$res = $res->getContent();					  
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
					
				//	Log::info($maxLevelId);					

					if(!empty($maxLevelId)){

					  $prentID = DB::table($esealTable)->where('primary_id', $parent)->first();
				      Log::info($distinctPIDCount);
					  if($prentID && $prentID->eseal_id){
					  	Log::info('into second iff');
					  	
					  	if($prentID->level_id==0)
					  		throw new Exception('A primary IOT is scanned as a Carton');

						if(count($distinctPIDCount)>1){
							if(!$isPallet){
						  DB::table($esealTable)->where('primary_id', $parent)->update(array('level_id'=> $maxLevelId, 'pid'=>0,'prod_batch_no'=>$prod_batch_no));
						}
						}else{
							if($mapParent){
						  DB::table($esealTable)->where('primary_id', $parent)->update( 
							array(
							  'level_id'=> $maxLevelId,'prod_batch_no'=>$prod_batch_no
							  ) 
						  );
						}
						else{
						   DB::table($esealTable)->where('primary_id', $parent)->update( 
							array(
							  'level_id'=> $maxLevelId,
							  'pid' => $distinctPIDCount[0]->pid,
							  'prod_batch_no'=>$prod_batch_no
							  ) 
						  );	
						}
						}
					  }else{
					  	jump1:
					  	Log::info('into else');
                        Log::info($distinctPIDCount);
                        if($createParent){
                          DB::table($esealTable)->insert(
							  ['primary_id'=> $parent,'mfg_date'=>$this->getDate(), 'level_id'=>0, 'pid'=> $pid,'pkg_qty'=>$pkg_qty,'storage_location'=>$storage_location,'attribute_map_id'=>$attributeMapId]
							);						  
                          DB::table($this->bindHistoryTable)->insert(['eseal_id'=>$parent,'attribute_map_id'=>$attributeMapId,'created_on'=>$this->getDate()]);
                        }
                        else{
						if(count($distinctPIDCount)>1){

						DB::table($esealTable)->insert(
							array('primary_id'=> $parent, 'level_id'=> $maxLevelId,'prod_batch_no'=>$prod_batch_no,'pid'=>0,'pkg_qty'=>$pkg_qty,'storage_location'=>$storage_location)
						  );
						}else{
						  DB::table($esealTable)->insert(
							  array('primary_id'=> $parent, 'level_id'=> $maxLevelId,'prod_batch_no'=>$prod_batch_no,'pid'=> $distinctPIDCount[0]->pid,'pkg_qty'=>$pkg_qty,'storage_location'=>$storage_location)
							);						  
						}
					}

					  }
					  
					}
					
					//Updating EsealBank table 
					DB::table($esealBankTable)->whereIn('id', array($parent))->update(array(
					  'used_status'=>1,
					  'level'=>$maxLevelId,
					  'location_id' => $locationId,
					  'utilizedDate' => $transitionTime,
                                          'pid'=>$distinctPIDCount[0]->pid
     
					));

					$distinctAttributeID = DB::table($esealTable)->where('parent_id', $parent)->select('attribute_map_id')->groupBy('attribute_map_id')->get()->toarray();
					if(count($distinctAttributeID)==1){
						if($isFinished){
						DB::table($esealTable)->where('primary_id', $parent)->update(['attribute_map_id'=>$distinctAttributeID[0]->attribute_map_id]);
						}					
						event(new scoapi_MapEseals($this->_request->all())); 

					//	log::info('no problem3');						
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
		$status = (isset($status_flag) && $status_flag==1) ? 2 : 0;	
		  Log::info($e->getMessage());
		  $message = $e->getMessage();
	  }
	$endTime = $this->getTime();
	return json_encode(array('Status'=>$status, 'Message' =>'S-: '.$message,'iots'=>$childsPacked));      
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

			$attributes = trim($this->_request->input('attributes'));
			$pid = trim($this->_request->input('pid'));
			$locationId = trim($this->_request->input('lid'));
	  
			Log::info(__FUNCTION__.' : '.print_r($this->_request->all(),true));

			if(!empty($attributes) && !empty($pid) && !empty($locationId))
			{
				$attributes_json = $attributes;
				$attributes = json_decode($attributes);
				if(json_last_error() == JSON_ERROR_NONE)
				{
					$attributeMapId = '';
					$nextId = 0;
						Log::info(print_r($nextId,true));         
						try
						{				  
							if(empty($nextId))
							{
								$nextId =DB::table('attribute_map')->insertGetId(array('attribute_json'=>$attributes_json,'location_id'=>$locationId,'created_on'=>date('Y-m-d H:i:s')));
								
								Log::info('New Instered NextId='.$nextId);
								foreach($attributes as $key => $value)
								{
									Log::info($key.' == '.$value);
									$attributeId = DB::table($this->attributeTable)->where('attribute_code', $key)->value('attribute_id');
									// echo $attributeId;exit;

									Log::info($attributeId);
									if(!empty($attributeId))
									{
										$insert = DB::insert(
											'insert into '.$this->attributeMappingTable.' (attribute_map_id, attribute_id, attribute_name, value, location_id,mapping_date) VALUES (?, ?, ?, ?, ?,?)',
										array($nextId, $attributeId, $key, trim($value,'"'), $locationId,$dateTime));
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


		return json_encode(array('Status'=>$status, 'Message' =>'S-: '.$message, 'AttributeMapId'=> (int)$nextId));
	}		

private function removeMappedEscortCodes($child, $parent){
	$status = 0;
	$message = 'Unable to remove given codes';
	try{
		$row = DB::table('escortData')->where('code', $parent)->select('pid','packingType')->first()->toarray();
		
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
			$rows = DB::table('escortData')->whereIn('code', $ids)->update(array('usedStatus'=>1));	
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


public function BindWithTrackupdate($request = '')
{
	if(!$request)
	{
		$this->_request->replace($request);
	}
	$startTime = $this->getTime();
	try
	{
		$status = 0;
		$message = 'Failed to sync';
		$mfg_id = $this->roleAccess->getMfgIdByToken($this->_request->input('access_token'));
		$this->mfg_id=$mfg_id;
		$attributeMapId = 0;
		$ids = trim($this->_request->input('ids'));
		$pid = trim($this->_request->input('pid'));
		$locationId = trim($this->_request->input('srcLocationId'));
		$attributeMapId = trim($this->_request->input('attribute_map_id'));
		$parent = trim($this->_request->input('parent'));
		$codes = trim($this->_request->input('codes'));
		$destLocationId = trim($this->_request->input('destLocationId'));
		$transitionTime = trim($this->_request->input('transitionTime'));
		$transitionId = trim($this->_request->input('transitionId'));
		$internalTransfer = trim($this->_request->input('internalTransfer'));
		$pkg_qty = trim($this->_request->input('pkg_qty')); 
		$po_number=trim($this->_request->input('po_number'));
/*-----------validation against over packing-------------------------*/
if($po_number!=''){
		$poDetails=DB::table('production_orders')->where(function($query) use($po_number){
			$query->where('erp_doc_no', '=', $po_number)->orWhere('eseal_doc_no', '=', $po_number);
		})->first();

		$convt=new Conversions();
		$to_pack_PAL=$convt->getUom($poDetails->product_id,$poDetails->qty,$poDetails->order_uom); 
		$packed_PAL = DB::table('eseal_'.$this->mfg_id)->where(['po_number'=>$po_number,'level_id'=>0,'is_active'=>1])->count('primary_id');
		
		//echo $po_number."--".$packed_PAL;exit;
		if($packed_PAL>=$to_pack_PAL){
			throw new Exception("Exceeded Packing Count of Pallets. Please Stop", 1);			
		}
}
		
/*-----------end od validation----------------------------------------------*/	
		Log::info(__FUNCTION__.' : '.print_r($this->_request->all(),true));

                if(date('Y', strtotime($transitionTime)) == '2009')
	           Input::merge(array('transitionTime' => $this->getDate()));
		DB::beginTransaction();
		// $req = Request::create('scoapi/BindEseals1', 'POST');
		// $res = app()->handle($req);
		// $bindResult = $res->getContent();
		// $res1 = json_decode($bindResult);
		$resData = self::BindEseals11($this->_request->all());
		$res1 = json_decode($resData);
		//print_r($res1);exit;
		if($res1->Status)
		{
			// $request = Request::create('scoapi/UpdateTracking', 'POST');
			// $res = app()->handle($request);
			// $trackResult = $res->getContent();

			$UTresData = self::UpdateTrackingNew($this->_request->all());
			$trackResult = $UTresData;
//			$trackResult = Route::dispatch($request)->getContent();
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
	return json_encode(array('Status'=>$status, 'Message' =>'S-:' .$message));
}



public function MapWithTrackupdate(){
	$startTime = $this->getTime();
	 try{
	  $status = 0;
	  $message = 'Failed to sync';
      $childsPacked = array();
	  $attributeMapId = 0;
	  $ids = trim($this->_request->input('ids'));
	  $locationId = trim($this->_request->input('srcLocationId'));


	  $parent = trim($this->_request->input('parent'));

	  $codes = trim($this->_request->input('codes'));
	  $destLocationId = trim($this->_request->input('destLocationId'));
	  $transitionTime = trim($this->_request->input('transitionTime'));
	  $transitionId = trim($this->_request->input('transitionId'));
	  $internalTransfer = trim($this->_request->input('internalTransfer'));
	  $mapParent = trim($this->_request->input('mapParent'));

	  if(date('Y', strtotime($transitionTime)) == '2009')
		    	Input::merge(array('transitionTime' => $this->getDate()));



	  Log::info(__FUNCTION__.' : '.print_r($this->_request->all(),true));
	  
	  DB::beginTransaction();

	  $request = Request::create('scoapi/MapEseals', 'POST');
	  $res = app()->handle($request);
		$mapResult = $res->getContent();
				
//	  $mapResult = Route::dispatch($request)->getContent();
	  Log::info(print_r($mapResult, true));
	  $res1 = json_decode($mapResult);
	  if($res1->Status == 1){	  	
		$request = Request::create('scoapi/UpdateTracking', 'POST');
		$res = app()->handle($request);
		$trackResult = $res->getContent();
//		$trackResult = Route::dispatch($request)->getContent();
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
	return json_encode(['Status'=>$status, 'Message' =>'S-: '.$message,'iots'=>$childsPacked]);
}



public function GetEsealDataByLocationId()
{
	$startTime = $this->getTime();    
	try
	{
		$data=$this->_request->all();
		$status = 0;
		$message = '';
		$locationId = $this->_request->input('locationId');
		$fromDate = $this->_request->input('fromDate');
		$toDate =  $this->_request->input('toDate');
		//$fromDate = date("Y-m-d H:i:s",strtotime(trim($this->_request->input('fromDate'))));
		//$toDate =  date("Y-m-d H:i:s",strtotime(trim($this->_request->input('toDate'))));
		$checkSyncTime = $this->_request->input('isSyncTime');
		$levels = $this->_request->input('levels');
		$po_number = $this->_request->input('po_number');
		$delivery_no = $this->_request->input('delivery_no');
		$Range = $this->_request->input('Range');
		$RangeCheck = $Range+1; //echo $Range.'---'.$RangeCheck;exit;
		$loadComponents = $this->_request->input('loadComponents');
		$loadAccessories = $this->_request->input('loadAccessories');	
		$excludePrimary = $this->_request->input('excludePrimary');	
		$confirmedStock = $this->_request->input('confirmedStock');		
		$finalQcStock = $this->_request->input('finalQcStock');		
		$isDataAvailable = 0;
		$productTypes[] = 8003;
		$trackArray = array();
		$ip = $_SERVER['REMOTE_ADDR'];
		$pids ='';
		$productArray = array();
		$i= 0;


                if($toDate == '' || empty($toDate))
                   $toDate = '9999-12-31 11:59:59';

		Log::info(__FUNCTION__.' : '.print_r($this->_request->all(),true));
		if(empty($locationId) || !is_numeric($locationId))
		{
			throw new Exception('Pass valid numeric location Id');
		}
		if(empty($levels) && $levels != 0)
		{
			throw new Exception('Parameters missing');
		}
		$locationObj = new Location();
		$mfgId = $locationObj->getMfgIdForLocationId($locationId);
		
		$childIds = array();
		$childIds = $locationObj->getAllChildIdForParentId($locationId);
		if($childIds)
		{
			array_push($childIds, $locationId);
		}
		$parentId = $locationObj->getParentIdForLocationId($locationId);
		$childIds1 = array();
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
		Log::info($locationId);
//echo $locationId;exit;
		array_push($splitLevels,'levels'); 


		if($loadComponents)
			$productTypes[] = 8001;

		if($loadAccessories)
			$productTypes[] = 8004;


		Log::info('Product Types:-');
		Log::info($productTypes);
		$productType = implode(',',$productTypes); 

		if($po_number){
			$pid = DB::table($esealTable)->where('po_number',$po_number)->groupBy('pid')->value('pid');
			if(empty($pid)){
				throw new Exception('The given PO number doesnt exist');
			}
		}



		if($delivery_no)
		{
			$products =  new Products\Products;
			$pArray = $products->getProductsFromDelivery($this->_request->input('access_token'),$delivery_no);
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


                 if(!empty($Range))
			{
				$sql .=' limit '.$RangeCheck;
			}
//echo $sql; exit;
		 $result = DB::select($sql);
		 if(empty($result)){
			throw new Exception('Data not-found');
		 }
		 foreach ($result as $res){
			$trackArray[] = $res->track_id;
		 }

                 $lastTrackId = end($trackArray);
		 $lastSyncTime = DB::table($this->trackHistoryTable)->where('track_id',$lastTrackId)->value($column);
		 $lastTrackIds[] = DB::table($this->trackHistoryTable)
		                    ->where($column,$lastSyncTime)
		                    ->whereIn('src_loc_id',explode(',',$locationId))
		                    ->where('dest_loc_id',0)
		                    ->value('track_id');
		 $trackArray = array_merge($lastTrackIds,$trackArray);
		                    // print_r($trackArray);exit;

		 $trackArray =  array_unique($trackArray);
		 $endTrack = end($trackArray);
		 $trackIds  = implode(',',$trackArray);                 
		 //echo "<pre/>";print_r($trackIds);exit;
			
				$sql = '
				SELECT 
					p.material_code AS matcode,				
					cast(p.group_id as UNSIGNED) as group_id,
					(select update_time from track_history th where e.track_id=th.track_id)  as utime,
                                        (select sync_time from track_history th where e.track_id=th.track_id) as stime,
					CASE WHEN e.pid=0 THEN "Hetrogenious Item" WHEN e.pid=-1 THEN "Pallet" ELSE p.name END AS name,
					IFNULL((select value as exp from attribute_mapping am where e.attribute_map_id=am.attribute_map_id and 
                    attribute_name="date_of_exp"),"") exp,
					IFNULL((select value as exp_valid from attribute_mapping am where e.attribute_map_id=am.attribute_map_id and attribute_name="exp_valid"),"0") exp_valid,
					"" zpace,					
					"" plt,
					"" wid,
					"" tp,
					cast(e.pkg_qty as UNSIGNED) as pkg_qty,
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
					e.is_active,e.prod_batch_no,IFNULL(ll.erp_code,"") as storage_location
				FROM '.$esealTable.' e
				INNER JOIN products p ON e.pid=p.product_id
				INNER JOIN master_lookup ml ON ml.value= p.product_type_id
				LEFT JOIN locations ll on ll.location_id=e.storage_location
				WHERE
				p.product_type_id in('.$productType.') and e.track_id in('.$trackIds.') and e.level_id in('.$levels.')';
		 
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

			//$sql .='order by e.track_id';
			 $sql .=' group by e.pid,e.primary_id order by e.track_id';
			
			 // echo $sql; exit;
			Log::info($sql);
			try
			{
				$result = DB::select($sql); 
				//Log::info($result);			
				DB::table('lookup_log')
				         ->insert([
				         	  'input'=>serialize($this->_request->all()),
				         	  'tracks'=>$trackIds,
				         	  'query'=>$sql,
				         	  'track1'=>$lastTrackId,
				         	  'track2'=>$endTrack,                  
				         	  'location_id'=>$this->_request->input('locationId'),                                
				         	  'last_sync'=>$lastSyncTime,
				         	  'response'=>json_encode($result),
				         	  'count'=>count($result),
				         	  'ip'=>$ip
				         	  ]);		

				Log::info('TOTAL COUNT :-'.count($result));
				$totResult = count($result);
				// print_r($totResult);exit; 
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
	Log::info(['Status'=>$status, 'Message' =>'S-: '.$message, 'esealData' => $productArray]);
	return json_encode(['Status'=>$status, 'Message' =>'S-: '.$message, 'isDataAvailable'=>$isDataAvailable,'esealData' => $productArray],JSON_UNESCAPED_SLASHES);
}

public function multiLevelMapping(){

	try{
		Log::info(__FUNCTION__.' : '.print_r($this->_request->all(),true));
		$status =1;
		$message = 'Binding,Mapping and Trackupdation is successfull';
		//$attributes = trim($this->_request->input('attributes'));
		$locationId = $this->roleAccess->getLocIdByToken($this->_request->input('access_token'));
		$pid = trim($this->_request->input('pid'));
		$tertiary_id = trim($this->_request->input('tertiary_id'));
		$childJson = trim($this->_request->input('child_json'));
		$transitionId = trim($this->_request->input('transitionId'));
		$transitionTime = trim($this->_request->input('transitionTime'));
		$attribute_map_id = trim($this->_request->input('attribute_map_id'));

			if(empty($locationId) || empty($tertiary_id) || empty($childJson) || empty($transitionId) || empty($transitionTime) || empty($pid) || empty($attribute_map_id))
			  throw new Exception('Parameters Missing');
			DB::beginTransaction();


			 $childArray = json_decode($childJson,true);
			 
			 if(json_last_error() != JSON_ERROR_NONE)
				throw new Exception ('Json Error');

			foreach($childArray as $childs){ 
			
			  $ids = $childs['childs'];
			  $parent = $childs['parent'];
			  $parentArray[] = $childs['parent'];
			  $parentImploded = implode(',',$parentArray);

			 $req = Request::create('scoapi/SyncEseal', 'POST', array('module_id'=> $this->_request->input('module_id'),'access_token'=>$this->_request->input('access_token'),'attribute_map_id'=>$attribute_map_id,'ids'=>$ids,'codes'=>$ids,'parent'=>$parent,'srcLocationId'=>$locationId,'pid'=>$pid,'transitionId'=>$transitionId,'transitionTime'=>$transitionTime));
			 $originalInput=$this->_request->all();
				$this->_request->replace($req->all());
				$res = app()->handle($req);
				$response = $res->getContent();
				 $response = json_decode($response,true); 
			   
			   if(!$response['Status'])
				  throw new Exception($response['Message']);

		   }
			  $req = Request::create('scoapi/MapWithTrackupdate', 'POST', array('module_id'=> $this->_request->input('module_id'),'access_token'=>$this->_request->input('access_token'),'parent'=>$tertiary_id,'ids'=>$parentImploded,'codes'=>$parentImploded,'srcLocationId'=>$locationId,'transitionId'=>$transitionId,'transitionTime'=>$transitionTime,'mapParent'=>true,'notUpdateChild'=>true));
			  $originalInput=$this->_request->all();
				$this->_request->replace($req->all());
				$res = app()->handle($req);
				$response = $res->getContent();
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



	public function SyncEseal(){
		$startTime = $this->getTime();
		 try{
		  $status = 0;
		  $message = 'Failed to sync';

		  $attributeMapId = 0;
		  $ids = trim($this->_request->input('ids'));
		  $pid = trim($this->_request->input('pid'));
		  $locationId = trim($this->_request->input('srcLocationId'));
		  $attributeMapId = trim($this->_request->input('attribute_map_id'));


		  $parent = trim($this->_request->input('parent'));

		  $codes = trim($this->_request->input('codes'));
		  $destLocationId = trim($this->_request->input('destLocationId'));
		  $transitionTime = trim($this->_request->input('transitionTime'));
		  $transitionId = trim($this->_request->input('transitionId'));
		  $internalTransfer = trim($this->_request->input('internalTransfer'));

		  Log::info(__FUNCTION__.' : '.print_r($this->_request->all(),true));

	          if(date('Y', strtotime($transitionTime)) == '2009')
		    Input::merge(array('transitionTime' => $this->getDate()));
	  
		  DB::beginTransaction();
		  	$req = Request::create('scoapi/BindEseals1', 'POST');
		  	$res = app()->handle($req);
			$res1 = $res->getContent();
		  $res1 = json_decode($bindResult);
		  if($res1->Status == 1){
			$request = Request::create('scoapi/MapEseals', 'POST');
			$res = app()->handle($request);
			$mapResult = $res->getContent();
//			$mapResult = Route::dispatch($request)->getContent();
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
			$res = app()->handle($request);
			$trackResult = $res->getContent();
//			$trackResult = Route::dispatch($request)->getContent();
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
			$message = 'Binding, Mapping & Track Info Updated Successfully';
		  }

	     commit:     
	     DB::commit();
		}catch(Exception $e){
		  DB::rollback();
		  $status = 0;
		  $message = $e->getMessage();
		}
		$endTime = $this->getTime();
		Log::info(__FUNCTION__.' Finishes execution in '.($endTime-$startTime));
		if($status == 0)
		{
			$subject = "Error Tracker from eSeal  - SyncEseal";
			$input_data = __FUNCTION__.' : '.print_r($this->_request->all(),true);
			$error_cnt = DB::table("error_email_tracker")->where('input_data',$input_data)->where('message',$message)->where('log_date',date('Y-m-d'))->count();
			if($error_cnt == 0)
			{
				$location_id = $this->roleAccess->getLocIdByToken($this->_request->input('access_token'));
				$user_id = $this->roleAccess->getUserIdByToken($this->_request->input('access_token'));

				$location = DB::table('locations')->where('location_id',$location_id)->value('location_name');
				$username = DB::table('users')->where('user_id',$user_id)->value('username');
				DB::table('error_email_tracker')->insert(['input_data'=>$input_data,'message'=>$message,'log_date'=>date('Y-m-d')]);
				$msg = 'Username: '.$username.'<br/>Location: '.$location.'<br/>Input Data: '.$input_data.'<br/>Message: '.$message;
				$this->sendErrorTrackerEmail($subject,$msg);
			}
		}
		return json_encode(array('Status'=>$status, 'Message' => $message));
	 }


	public function BindEseals1(){

	  	if(DB::table('flag')->value('flag')){
			return json_encode(array('Status'=>0, 'Message' =>'S-: S- busy,please try after sometime'));
		}
		DB::table('flag')->update(['flag'=>1]);

		$startTime = $this->getTime();    
		try{

		  $status = 0;
		  $message = 'Failed to bind';
		  $mfgId= $this->roleAccess->getMfgIdByToken($this->_request->input('access_token'));
		  $attributeMapId = 0;
		  $ids = trim($this->_request->input('ids'));
		  $parent = trim($this->_request->input('parent'));
		  $po_number = trim($this->_request->input('po_number')); 
		  $attributes = trim($this->_request->input('attributes'));
		  $flagJson = trim($this->_request->input('flagsJson'));
		  $flagArr = json_decode($flagJson,true);	  
		  $pid = trim($this->_request->input('pid'));
		  $locationId = trim($this->_request->input('srcLocationId'));
		  $attributeMapId = trim($this->_request->input('attribute_map_id'));
		  $transitionTime = $this->_request->input('transitionTime');
		  $pkg_qty_input = (int) trim($this->_request->input('pkg_qty'));
		  Log::info(__FUNCTION__.' : '.print_r($this->_request->all(),true));
		  $prod_batch_no=date("Ym");
			

		 if(empty($ids) && empty($locationId) && empty($pid))
		 	throw new Exception('Parameters Missing.');
		 
			$ids1 = explode(',', $ids);
			$ids1 = array_unique($ids1);
			$newIds = '\''.implode('\',\'', $ids1).'\'';

			$locationObj = new Location();
			$mfgId = $locationObj->getMfgIdForLocationId($locationId);
			$erp_code = $locationObj->getSAPCodeFromLocationId($locationId);
			
			if(empty($mfgId))
				throw new Exception('Locations does\'nt belong to any customer');

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
					})->value('id');
		  	}
		  	else{
		  	  throw new Exception('Codes count not matching with code bank');	
		  	}

		  }
		  $resultCnt = DB::table($esealTable)->whereIn('primary_id', $ids1)->count();
		  Log::info($resultCnt);
		  
		  $productExists = DB::table('products')->select('product_id','uom_unit_value','expiry_period')->where('product_id', $pid)->get()->toarray();
		  
		  //Log::info(print_r($result[0]->cnt, true));
		  Log::info($productExists);
			if(empty($productExists[0]->product_id))
				throw new Exception('Product not found');

			$pkg_qty = $productExists[0]->uom_unit_value;	

			if($mfgId==6){
				if($pkg_qty_input!=0){
					$pkg_qty=$pkg_qty_input;
				} else {
					$convt=new Conversions();
			   		$pkg_qty=$convt->getUom($productExists[0]->product_id,1,'PAL','EA');
				}
			}


			$batch_no = DB::table('attribute_mapping')->where(['attribute_map_id'=>$attributeMapId,'attribute_name'=>'batch_no'])->value('value');		 
			if(!$batch_no)
				$batch_no ='';

	                $servBatchExists = DB::table('attribute_mapping')->where(['attribute_map_id'=>$attributeMapId,'attribute_name'=>'service_batch_no'])->count('id');		 
			if($servBatchExists){
			  $batch_no= $erp_code.date('Y');
	                  DB::table('attribute_mapping')->where(['attribute_map_id'=>$attributeMapId,'attribute_name'=>'service_batch_no'])->update(['value'=>$batch_no]);		 			

	          $storage_loc_code = DB::table('attribute_mapping')->where(['attribute_map_id'=>$attributeMapId,'attribute_name'=>'storage_loc_code'])->value('value');		 			          
			}

	                $expValid = DB::table('attribute_mapping')->where(['attribute_map_id'=>$attributeMapId,'attribute_name'=>'exp_valid'])->count('id');		 

			if($expValid){
	                        Log::info('expiry period is :'.$productExists[0]->expiry_period);
	   			if(empty($productExists[0]->expiry_period) || is_null($productExists[0]->expiry_period))
				 throw new Exception('Expiry period is not configured for the product.');
	           
	          $expDate = DB::table('attribute_mapping')->where(['attribute_map_id'=>$attributeMapId,'attribute_name'=>'date_of_exp'])->count('id');		 
	              if(!$expDate)
	           	 throw new Exception('There is no expiry date attribute for update.');

	           $expiry_date =  date('Y-m-d', strtotime("+".$productExists[0]->expiry_period." days"));

	           DB::table('attribute_mapping')->where(['attribute_map_id'=>$attributeMapId,'attribute_name'=>'date_of_exp'])->update(['value'=>$expiry_date]);		 


			}


			$po_number = DB::table('attribute_mapping')->where(['attribute_map_id'=>$attributeMapId,'attribute_name'=>'po_number'])->value('value');	

	 
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

					DB::update('Update '.$esealTable.' SET pid = '.$pid.', attribute_map_id = '.$attributeMapId.' WHERE eseal_id = ? ', array($res[0]->eseal_id));
					DB::insert('INSERT INTO bind_history (eseal_id,location_id,attribute_map_id,created_on) values (?, ?,?,?)', array($res[0]->primary_id,$locationId ,$attributeMapId,$transitionTime));
				  }else{
					DB::insert('INSERT INTO '.$esealTable.' (primary_id, pid, attribute_map_id,mfg_date,batch_no,prod_batch_no,po_number,pkg_qty) values (?, ?, ?,?,?,?,?,?)', array($id, $pid, $attributeMapId,$transitionTime,$batch_no,$prod_batch_no,$po_number,$pkg_qty));  
					DB::insert('INSERT INTO bind_history (eseal_id,location_id,attribute_map_id,created_on) values (?, ?,?,?)', array($id,$locationId ,$attributeMapId,$transitionTime));
				  }
				}
	         
	            if($servBatchExists)              
	                   DB::table($esealTable)->whereIn('primary_id',$ids1)
	                    ->update(['storage_location'=>$storage_loc_code]);            

	            DB::table($esealBankTable)->whereIn('id',$ids1)->update(['used_status'=>1,'location_id'=> $locationId,'pid'=>$pid,'utilizedDate'=>$this->getDate()]);            
				  	
			//	foreach($ids1 as $id){
			//	DB::table($esealBankTable)->where('id',$id)->update(array('used_status'=>1, 'location_id'=> $locationId));
			//  }

			  }catch(PDOException $e){
				
				Log::error($e->getMessage());
				throw new Exception('Error while binding');  
			  }
			  $status = 1;
			  $message = 'Binding Succesfull';
			  
			
		  
	    commit:
		}catch(Exception $e){
		  $status =0;
		  
		  Log::info($e->getMessage());
		  $message = $e->getMessage();
		}
		if($status){
			event(new scoapi_BindEseals($this->_request->all()));
		  	/*Event::fire('scoapi/BindEseals', array('attribute_map_id'=>$attributeMapId, 'codes'=>$ids1, 'mfg_id'=>$mfgId));*/
		}
		$endTime = $this->getTime();
		Log::info(__FUNCTION__.' Finishes execution in '.($endTime-$startTime));
		
		DB::table('flag')->update(['flag'=>0]);
		 return json_encode(array('Status'=>$status, 'Message' =>'S-: '.$message));
	}

	public function BindEseals11($request = ''){

		if(!$request)
		{
			$this->_request->replace($request);
		}

	  	if(DB::table('flag')->value('flag')){
			return json_encode(array('Status'=>0, 'Message' =>'S-: Server busy,please try after sometime'));
		}
		DB::table('flag')->update(['flag'=>1]);

		$startTime = $this->getTime();    
		try{

		  $status = 0;
		  $message = 'Failed to bind';
		  $mfgId= $this->roleAccess->getMfgIdByToken($this->_request->input('access_token'));
		  $attributeMapId = 0;
		  $ids = trim($this->_request->input('ids'));
		  $parent = trim($this->_request->input('parent'));
		  $po_number = trim($this->_request->input('po_number')); 
		  $attributes = trim($this->_request->input('attributes'));
		  $flagJson = trim($this->_request->input('flagsJson'));
		  $flagArr = json_decode($flagJson,true);	  
		  $pid = trim($this->_request->input('pid'));
		  $locationId = trim($this->_request->input('srcLocationId'));
		  $attributeMapId = trim($this->_request->input('attribute_map_id'));
		  $transitionTime = $this->_request->input('transitionTime');
		  $pkg_qty_input = (int) trim($this->_request->input('pkg_qty'));
		  Log::info(__FUNCTION__.' : '.print_r($this->_request->all(),true));
		  $prod_batch_no=date("Ym");
			

		 if(empty($ids) && empty($locationId) && empty($pid))
		 	throw new Exception('Parameters Missing.');
		 
			$ids1 = explode(',', $ids);
			$ids1 = array_unique($ids1);
			$newIds = '\''.implode('\',\'', $ids1).'\'';

			$locationObj = new Location();
			$mfgId = $locationObj->getMfgIdForLocationId($locationId);
	         $erp_code = $locationObj->getSAPCodeFromLocationId($locationId);
			if(empty($mfgId))
				throw new Exception('Locations does\'nt belong to any customer');

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
					})->value('id');
		  	}
		  	else{
		  	  throw new Exception('Codes count not matching with code bank');	
		  	}

		  }
		  $resultCnt = DB::table($esealTable)->whereIn('primary_id', $ids1)->count();
		  Log::info($resultCnt);
		  
		  $productExists = DB::table('products')->select('product_id','uom_unit_value','expiry_period')->where('product_id', $pid)->get()->toarray();
		  //Log::info(print_r($result-lt[0]->cnt, true));
		  Log::info($productExists);
			if(empty($productExists[0]->product_id))
				throw new Exception('Product not found');

			$pkg_qty = $productExists[0]->uom_unit_value;	

			if($mfgId==6){
				if($pkg_qty_input!=0){
					$pkg_qty=$pkg_qty_input;
				} else {
					$convt=new Conversions();
			   		$pkg_qty=$convt->getUom($productExists[0]->product_id,1,'PAL','EA');
				}
			}


			$batch_no = DB::table('attribute_mapping')->where(['attribute_map_id'=>$attributeMapId,'attribute_name'=>'batch_no'])->value('value');		 
			if(!$batch_no)
				$batch_no ='';

	                $servBatchExists = DB::table('attribute_mapping')->where(['attribute_map_id'=>$attributeMapId,'attribute_name'=>'service_batch_no'])->count('id');		 
			if($servBatchExists){
			  $batch_no= $erp_code.date('Y');
	                  DB::table('attribute_mapping')->where(['attribute_map_id'=>$attributeMapId,'attribute_name'=>'service_batch_no'])->update(['value'=>$batch_no]);		 			

	          $storage_loc_code = DB::table('attribute_mapping')->where(['attribute_map_id'=>$attributeMapId,'attribute_name'=>'storage_loc_code'])->value('value');		 			          
			}

	                $expValid = DB::table('attribute_mapping')->where(['attribute_map_id'=>$attributeMapId,'attribute_name'=>'exp_valid'])->count('id');		 

			if($expValid){
	                        Log::info('expiry period is :'.$productExists[0]->expiry_period);
	   			if(empty($productExists[0]->expiry_period) || is_null($productExists[0]->expiry_period))
				 throw new Exception('Expiry period is not configured for the product.');
	           
	          $expDate = DB::table('attribute_mapping')->where(['attribute_map_id'=>$attributeMapId,'attribute_name'=>'date_of_exp'])->count('id');		 
	              if(!$expDate)
	           	 throw new Exception('There is no expiry date attribute for update.');

	           $expiry_date =  date('Y-m-d', strtotime("+".$productExists[0]->expiry_period." days"));

	           DB::table('attribute_mapping')->where(['attribute_map_id'=>$attributeMapId,'attribute_name'=>'date_of_exp'])->update(['value'=>$expiry_date]);		 


			}


			$po_number = DB::table('attribute_mapping')->where(['attribute_map_id'=>$attributeMapId,'attribute_name'=>'po_number'])->value('value');	

	 
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

					DB::update('Update '.$esealTable.' SET pid = '.$pid.', attribute_map_id = '.$attributeMapId.' WHERE eseal_id = ? ', array($res[0]->eseal_id));
					DB::insert('INSERT INTO bind_history (eseal_id,location_id,attribute_map_id,created_on) values (?, ?,?,?)', array($res[0]->primary_id,$locationId ,$attributeMapId,$transitionTime));
				  }else{
					DB::insert('INSERT INTO '.$esealTable.' (primary_id, pid, attribute_map_id,mfg_date,batch_no,prod_batch_no,po_number,pkg_qty) values (?, ?, ?,?,?,?,?,?)', array($id, $pid, $attributeMapId,$transitionTime,$batch_no,$prod_batch_no,$po_number,$pkg_qty));  
					DB::insert('INSERT INTO bind_history (eseal_id,location_id,attribute_map_id,created_on) values (?, ?,?,?)', array($id,$locationId ,$attributeMapId,$transitionTime));
				  }
				}
	         
	            if($servBatchExists)              
	                   DB::table($esealTable)->whereIn('primary_id',$ids1)
	                    ->update(['storage_location'=>$storage_loc_code]);            

	            DB::table($esealBankTable)->whereIn('id',$ids1)->update(['used_status'=>1,'location_id'=> $locationId,'pid'=>$pid,'utilizedDate'=>$this->getDate()]);            
				  	
			//	foreach($ids1 as $id){
			//	DB::table($esealBankTable)->where('id',$id)->update(array('used_status'=>1, 'location_id'=> $locationId));
			//  }

			  }catch(PDOException $e){
				
				Log::error($e->getMessage());
				throw new Exception('Error while binding');  
			  }
			  $status = 1;
			  $message = 'Binding Succesfull';
			  
			
		  
	    commit:
		}catch(Exception $e){
		  $status =0;
		  
		  Log::info($e->getMessage());
		  $message = $e->getMessage();
		}
		if($status){
			event(new scoapi_BindEseals($this->_request->all()));
		  	/*Event::fire('scoapi/BindEseals', array('attribute_map_id'=>$attributeMapId, 'codes'=>$ids1, 'mfg_id'=>$mfgId));*/
		}
		$endTime = $this->getTime();
		Log::info(__FUNCTION__.' Finishes execution in '.($endTime-$startTime));
		
		DB::table('flag')->update(['flag'=>0]);
		 return json_encode(array('Status'=>$status, 'Message' =>'S-: '.$message));
	}

public function UpdateTracking(){
	$startTime = $this->getTime();
	try{

		$status = 0;
		$message = 'Failed to update track info';

		$destLocationId = 0;
		$searchInChild = trim($this->_request->input('searchInChild')); 
		$codes = trim($this->_request->input('codes'));
		$parent = trim($this->_request->input('parent'));
		$srcLocationId = rtrim(ltrim($this->_request->input('srcLocationId')));
		$destLocationId = trim($this->_request->input('destLocationId'));
		$transitionTime = trim($this->_request->input('transitionTime'));
		//$transitionTime = $this->getDate();
		$transitionId = rtrim(ltrim($this->_request->input('transitionId')));
		$internalTransfer = trim($this->_request->input('internalTransfer'));
		$notUpdateChild = trim($this->_request->input('notUpdateChild'));
		Log::info(__FUNCTION__.' : '.print_r($this->_request->all(),true));

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

		$locationObj = new Location();
		$mfgId = $locationObj->getMfgIdForLocationId($srcLocationId);
		$esealTable = 'eseal_'.$mfgId;
		$transactionObj = new Transaction();
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

	//	Log::info('$childCnt'.$childCnt);
		//echo '<pre/>';print_r('SrcLocAction : ' . $srcLocationAction.' , DestLocAction: '. $destLocationAction.', inTransitAction: '. $inTransitAction);exit;
		$trakHistoryObj = new Trackhistory();
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
					->get()->toarray();
				
				$locationObj = new Location();
				
				$childIds = $locationObj->getAllChildIdForParentId($srcLocationId);	
				Log::info($childIds);
				if(!$searchInChild){
					$childIds = array();
				}
				if(count($codesTrack)){
					foreach($codesTrack as $trackRow){
						Log::info('Source Location:'.$trackRow->src_loc_id);
						Log::info('Passed source location:'.$srcLocationId);
						if(($trackRow->src_loc_id!=$srcLocationId && !in_array($trackRow->src_loc_id,$childIds)) || $trackRow->dest_loc_id>0){
							throw new Exception('Some of the codes are not available at given locations');
						}
					}
				}
				
				 $lastInrtId = $trakHistoryObj->insertTrack(
					$srcLocationId, $destLocationId, $transitionId, $transitionTime
					);
				
				//Log::info('track_id'.$lastInrtId);
				
				if($notUpdateChild){
				DB::table($esealTable)
					->whereIn('primary_id', $uniqueSplitChilds)					
					->update(array('track_id'=>$lastInrtId));  
				}
				else{
				DB::table($esealTable)
					->whereIn('primary_id', $uniqueSplitChilds)
					->orWhereIn('parent_id', $uniqueSplitChilds)
					->update(array('track_id'=>$lastInrtId));
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
					->value('id');
					//echo 'tranis1'.$transitionId;exit;
				
				
				$lastInrtId = $trakHistoryObj->insertTrack(
					$destLocationId, 0, $transitionId, $transitionTime
					);  
			
				DB::table($esealTable)
					->whereIn('primary_id', $uniqueSplitChilds)
					->orWhereIn('parent_id', $uniqueSplitChilds)
					->update(array('track_id'=>$lastInrtId));

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
					->get()->toarray();
			   
				if(count($codesTrack)){
					foreach($codesTrack as $trackRow){
						Log::info($trackRow->src_loc_id.'*****'.$srcLocationId);
						Log::info('^^^^^'.$trackRow->dest_loc_id);
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
					->value('id');
				
				$lastInrtId = $trakHistoryObj->insertTrack(
					$destLocationId, 0, $transitionId, $transitionTime
					);  
			
				DB::table($esealTable)
					->whereIn('primary_id', $uniqueSplitChilds)
					->orWhereIn('parent_id', $uniqueSplitChilds)
					->update(array('track_id'=>$lastInrtId));


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

		if($srcLocationAction==-1 && $destLocationAction==0 && $inTransitAction==1){
		//////////////////For stock out
		$trakHistoryObj = new Trackhistory();
		//Log::info(var_dump($trakHistoryObj));
		
		try{
			Log::info('Destination Location:');
			Log::info($destLocationId);
			
			$lastInrtId = DB::table($this->trackHistoryTable)->insertGetId( array(
				'src_loc_id'=>$srcLocationId, 'dest_loc_id'=>$destLocationId, 
				'transition_id'=>$transitionId,'update_time'=>$transitionTime));
			Log::info($lastInrtId);

			$maxLevelId = 	DB::table($esealTable)
								->whereIn('parent_id', $uniqueSplitChilds)
								->orWhereIn('primary_id', $uniqueSplitChilds)->max('level_id');



			if(!$this->updateTrackForChilds($esealTable, $lastInrtId, $uniqueSplitChilds, $maxLevelId)){
				throw new Exception('Exception occured during track updation');
			}
			/*DB::table($esealTable)->whereIn('primary_id', )
				  ->orWhereIn('parent_id', $explodedIds)
				  ->update(array('track_id' => $lastInrtId));	*/
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
	return json_encode(['Status'=>$status, 'Message' =>'S-: '.$message]);      
}

public function UpdateTrackingNew($request){
	if(!$request)
	{
		$this->_request->replace($request);
	}
	$startTime = $this->getTime();
	try{

		$status = 0;
		$message = 'Failed to update track info';

		$destLocationId = 0;
		$searchInChild = trim($this->_request->input('searchInChild')); 
		$codes = trim($this->_request->input('codes'));
		$parent = trim($this->_request->input('parent'));
		$srcLocationId = rtrim(ltrim($this->_request->input('srcLocationId')));
		$destLocationId = trim($this->_request->input('destLocationId'));
		$transitionTime = trim($this->_request->input('transitionTime'));
		//$transitionTime = $this->getDate();
		$transitionId = rtrim(ltrim($this->_request->input('transitionId')));
		$internalTransfer = trim($this->_request->input('internalTransfer'));
		$notUpdateChild = trim($this->_request->input('notUpdateChild'));
		Log::info(__FUNCTION__.' : '.print_r($this->_request->all(),true));

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

		$locationObj = new Location();
		$mfgId = $locationObj->getMfgIdForLocationId($srcLocationId);
		$esealTable = 'eseal_'.$mfgId;
		$transactionObj = new Transaction();
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

	//	Log::info('$childCnt'.$childCnt);
		//echo '<pre/>';print_r('SrcLocAction : ' . $srcLocationAction.' , DestLocAction: '. $destLocationAction.', inTransitAction: '. $inTransitAction);exit;
		$trakHistoryObj = new Trackhistory();
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
					->get()->toarray();
				
				$locationObj = new Location();
				
				$childIds = $locationObj->getAllChildIdForParentId($srcLocationId);	
				Log::info($childIds);
				if(!$searchInChild){
					$childIds = array();
				}
				if(count($codesTrack)){
					foreach($codesTrack as $trackRow){
						Log::info('Source Location:'.$trackRow->src_loc_id);
						Log::info('Passed source location:'.$srcLocationId);
						if(($trackRow->src_loc_id!=$srcLocationId && !in_array($trackRow->src_loc_id,$childIds)) || $trackRow->dest_loc_id>0){
							throw new Exception('Some of the codes are not available at given locations');
						}
					}
				}
				
				 $lastInrtId = $trakHistoryObj->insertTrack(
					$srcLocationId, $destLocationId, $transitionId, $transitionTime
					);
				
				//Log::info('track_id'.$lastInrtId);
				
				if($notUpdateChild){
				DB::table($esealTable)
					->whereIn('primary_id', $uniqueSplitChilds)					
					->update(array('track_id'=>$lastInrtId));  
				}
				else{
				DB::table($esealTable)
					->whereIn('primary_id', $uniqueSplitChilds)
					->orWhereIn('parent_id', $uniqueSplitChilds)
					->update(array('track_id'=>$lastInrtId));
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
					->value('id');
					//echo 'tranis1'.$transitionId;exit;
				
				
				$lastInrtId = $trakHistoryObj->insertTrack(
					$destLocationId, 0, $transitionId, $transitionTime
					);  
			
				DB::table($esealTable)
					->whereIn('primary_id', $uniqueSplitChilds)
					->orWhereIn('parent_id', $uniqueSplitChilds)
					->update(array('track_id'=>$lastInrtId));

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
					->get()->toarray();
			   
				if(count($codesTrack)){
					foreach($codesTrack as $trackRow){
						Log::info($trackRow->src_loc_id.'*****'.$srcLocationId);
						Log::info('^^^^^'.$trackRow->dest_loc_id);
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
					->value('id');
				
				$lastInrtId = $trakHistoryObj->insertTrack(
					$destLocationId, 0, $transitionId, $transitionTime
					);  
			
				DB::table($esealTable)
					->whereIn('primary_id', $uniqueSplitChilds)
					->orWhereIn('parent_id', $uniqueSplitChilds)
					->update(array('track_id'=>$lastInrtId));


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

		if($srcLocationAction==-1 && $destLocationAction==0 && $inTransitAction==1){
		//////////////////For stock out
		$trakHistoryObj = new Trackhistory();
		//Log::info(var_dump($trakHistoryObj));
		
		try{
			Log::info('Destination Location:');
			Log::info($destLocationId);
			
			$lastInrtId = DB::table($this->trackHistoryTable)->insertGetId( array(
				'src_loc_id'=>$srcLocationId, 'dest_loc_id'=>$destLocationId, 
				'transition_id'=>$transitionId,'update_time'=>$transitionTime));
			Log::info($lastInrtId);

			$maxLevelId = 	DB::table($esealTable)
								->whereIn('parent_id', $uniqueSplitChilds)
								->orWhereIn('primary_id', $uniqueSplitChilds)->max('level_id');



			if(!$this->updateTrackForChilds($esealTable, $lastInrtId, $uniqueSplitChilds, $maxLevelId)){
				throw new Exception('Exception occured during track updation');
			}
			/*DB::table($esealTable)->whereIn('primary_id', )
				  ->orWhereIn('parent_id', $explodedIds)
				  ->update(array('track_id' => $lastInrtId));	*/
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
	return json_encode(['Status'=>$status, 'Message' =>'S-: '.$message]);      
}

public function getPoQuantity(){
	// try{

		$status =1;
		$message ='Data successfully retrieved';
		$qty ='';
		$bindQty = '';
		$confirmQty = '';
                $material_code = '';
                $description ='';
		$po_number = trim($this->_request->input('po_number'));



		if($po_number=="")

		throw new Exception('PO number not passed');
		

		$mfgId = $this->roleAccess->getMfgIdByToken($this->_request->input('access_token'));
		$poDetails=DB::table('production_orders')->where(function($query) use($po_number){
			$query->where('erp_doc_no', '=', $po_number)->orWhere('eseal_doc_no', '=', $po_number);
		})->first(); 

		

		if(empty($poDetails)){
			throw new Exception('PO doesnt exists in eseal ');
		}

		//echo "<pre/>";print_r($poDetails);exit;
	    $description1 = Products::where('product_id',$poDetails->product_id)->get(['description','multiPack'])->toarray();

	    $material_code1 = Products::where('product_id',$poDetails->product_id)->get(['material_code','ean'])->toarray();

	    $material_code  = $material_code1[0]['material_code'];
//echo $material_code;exit;
	    $ean  = $material_code1[0]['ean'];
//echo $ean;exit;
	    //echo $poDetails->product_id;exit;
	    $qty_in_eaches='';
	    /*$qty_in_eaches = DB::table('conversions as c')->join('production_orders as po','c.product_id','=','po.product_id')
	    					->where('po.product_id','=',$poDetails->product_id)
	    					->where('c.alt_uom','=',$poDetails->order_uom)
	    					->where('c.product_id','=',$poDetails->product_id)
	    					->where('po.erp_doc_no','=',$po_number)
	    					->orWhere('po.eseal_doc_no','=',$po_number)
	    					->select(DB::raw('c.base_quantity*po.qty AS qty_eaches'))
	    					->get()->toarray();

	     $kk1=$qty_in_eaches[0]->qty_eaches;*/
		if(count($description1)==0)
		   	throw new Exception('The material in PORDER doesnt exist in the system');

		   	$po_qty=$poDetails->qty;
		   	$p_id=$poDetails->product_id;
		   	$uom=$poDetails->order_uom;		
			$convt=new Conversions();
		   	$qty=$convt->getUom($poDetails->product_id,$poDetails->qty,$poDetails->order_uom); 
			
		   	$eSealCnfQty='';

//		   	$qty=$convt->getUom($poDetails->product_id,$poDetails->qty,$poDetails->order_uom); 
			$description = $description1[0]['description'];
			$multiPack = $description1[0]['multiPack'];
			$manf_date='';
			$manf_date1 = DB::table('eseal_'.$mfgId)->where(['po_number'=>$po_number])->get(['mfg_date'])->toarray();
                $manf_date = $manf_date1[0]->mfg_date;

			   	if($multiPack){
                 $bindQty = DB::table('eseal_'.$mfgId)->where(['po_number'=>$po_number,'level_id'=>0,'is_active'=>1])
                 ->sum('pkg_qty');
                 // $bindQty=$bindQty1/40;
                 $confirmQty = DB::table('eseal_'.$mfgId)->where(['po_number'=>$po_number,'level_id'=>0,'is_active'=>1])->where('is_confirmed','!=',0)->sum('pkg_qty');
                 $eSealCnfQty = DB::table('eseal_'.$mfgId)->where(['po_number'=>$po_number,'level_id'=>0,'is_active'=>1])->where('eseal_confirmed','!=',0)->sum('pkg_qty');

                 


			   	}
                else{
                 $bindQty = DB::table('eseal_'.$mfgId)->where(['po_number'=>$po_number,'level_id'=>0,'is_active'=>1])->count('eseal_id');
                 $confirmQty = DB::table('eseal_'.$mfgId)->where(['po_number'=>$po_number,'level_id'=>0,'is_active'=>1])->where('is_confirmed','!=',0)->count('eseal_id');
                 $eSealCnfQty = DB::table('eseal_'.$mfgId)->where(['po_number'=>$po_number,'level_id'=>0,'is_active'=>1])->where('eseal_confirmed','!=',0)->count('eseal_id');

                
                }
//echo $confirmQty;exit;
             $order_uom = DB::table('production_orders')->where('erp_doc_no','=',$po_number)->orWhere('eseal_doc_no','=',$po_number)->get('order_uom')->toArray();
             

            $convet = new Conversions();
			$pallets_to_pack=$convet->getUomAll($po_qty,$uom,$p_id);
			
			  $packed_qty_pallets=DB::table('eseal_'.$mfgId)->where(['po_number'=>$po_number,'level_id'=>0,'is_active'=>1])->count('eseal_id');
			  $packed_qty_EA = DB::table('eseal_'.$mfgId)->where(['po_number'=>$po_number,'level_id'=>0,'is_active'=>1])
              ->sum('pkg_qty');
			  $packed_qty_z01=$convet->getZ01($packed_qty_EA,$uom,$p_id);
			 
			  

	// }
	// catch(Exception $e){
	// 	$status =0;
	// 	$message = $e->getMessage();
	// }
    log::info(['Status'=>$status,'Message'=>$message,'qty'=>$qty,'packedqty'=>$bindQty,'pallets_to_pack'=>$pallets_to_pack,'material_code'=>$material_code,'description'=>$description,'confirmQty'=>$confirmQty,'eSealCnfQty'=>$eSealCnfQty,'EAN'=>$ean]);
/*	return json_encode(['Status'=>$status,'Message'=>$message,'qty'=>$qty,'pallets_to_pack'=>$pallets_to_pack,'po_qty'=>$po_qty,'packedqty'=>(int)$bindQty,'material_code'=>$material_code,'description'=>$description,'confirmQty'=>$confirmQty,'eSealCnfQty'=>$eSealCnfQty,'EAN'=>$ean,'mfg_date'=>$manf_date,'Qty_in_eaches'=>$kk1,'packed_Qty_in_eaches'=>$packed_qty_eaches[0]->packed_qty_eaches,'packed_Qty_in_pallets'=>$packed_qty_pallets,'order_uom'=>$order_uom[0]->order_uom]);
*/
	return json_encode(['Status'=>$status,'Message'=>$message,'qty'=>$qty,'pallets_to_pack'=>$pallets_to_pack,'po_qty'=>$po_qty,'packedqty'=>$bindQty,'material_code'=>$material_code,'description'=>$description,'confirmQty'=>$confirmQty,'eSealCnfQty'=>$eSealCnfQty,'EAN'=>$ean,'mfg_date'=>$manf_date,'packed_Qty_in_pallets'=>$packed_qty_pallets,'packed_pallets_to_cartons'=> $packed_qty_z01,'packed_pallets_to_Eaches'=>$packed_qty_EA,'order_uom'=>$order_uom[0]->order_uom]);
}


	public function confirmProductionOrder_old()
	{
            $startTime = $this->getTime();
            $status=0;
			$message = '';
		
		try{ 
			$accessToken = $this->_request->input('access_token');
			$userId=DB::table('users_token')->where('access_token',$accessToken)->value('user_id');
			$productionOrderId = $this->_request->input('production_order_id'); 
			//echo $production_order_id;exit;
			$mfg_date = $this->_request->input('mfg_date');
			$storage_loc_code = trim($this->_request->input('storage_loc_code'));
			$conf_status = trim($this->_request->input('confirmation_status'));
			$remarks = trim($this->_request->input('remarks'));
			$quality_cost_loss = trim($this->_request->input('quality_cost_loss'));
			// echo $conf_status;exit;
			$manufacturerId = $this->roleAccess->getMfgIdByToken($accessToken);
			$orderID = DB::table('production_orders')->where('erp_doc_no', $productionOrderId)->value('id');
			$esealErp=0;
			if(!$orderID){
				$orderID = DB::table('production_orders')->where('eseal_doc_no', $productionOrderId)->value('id');
				// echo $orderID;exit;
				$esealErp=1;
				if(empty($orderID)){
					throw new Exception('In-valid Production Order Id.');
				}
				//$serialsArray = DB::table('eseal_'.$manufacturerId)->where(['po_number'=>$productionOrderId,'is_confirmed'=>'0','is_active'=>1,'eseal_confirmed'=>0])->count('primary_id');
				$serialsArray = DB::table('eseal_'.$manufacturerId)->where(['is_active'=>1,'eseal_confirmed'=>0,'po_number'=>$productionOrderId])->count('primary_id');
				if($serialsArray<=0){
					throw new Exception('No Data for Production Order ID or Already Confirmed');
				}else{
				$insert=DB::table('po_confirm_queue')->insertGetId(['po_number'=>$productionOrderId,'mfg_id'=>$manufacturerId,'qty'=>$serialsArray,'userstamp'=>$userId,'final_confirm'=>$conf_status,'remarks'=>$remarks,'conf_date'=>$mfg_date,'cost_loss'=>$quality_cost_loss]);
				// echo $insert;exit;
				$update=DB::table('eseal_'.$manufacturerId)->where(['po_number'=>$productionOrderId,'is_confirmed'=>'0','is_active'=>1,'eseal_confirmed'=>0])->update(['eseal_confirmed'=>$insert]);
				$update=DB::table('production_orders')->where(['id'=>$orderID])->update(['is_confirm'=>1]);

					$status=1;
					$message ='Stock confirmed successfully in eseal, will update in Ecc shortly';

				}

			}  else {
            //sleep(200000); 

			if(empty($orderID)){
				throw new Exception('In-valid Production Order Id.');
			}

			$serialsArray = DB::table('eseal_'.$manufacturerId)->where(['is_active'=>1,'eseal_confirmed'=>0,'po_number'=>$productionOrderId])->count('primary_id');

			$po_details=DB::table('production_orders as p')->where('erp_doc_no',$productionOrderId)->first();

			$convet=new Conversions();
			$poQtyEA=$convet->getUom($po_details->product_id,$po_details->qty,$po_details->order_uom,'EA');
			// echo $serialsArray;exit;

			if($serialsArray<=0){
				throw new Exception('No Data for Production Order ID or Already Confirmed');
			}else{
				/* confirm po start */
				$conf_status=$conf_status==1?1:0;
				/*$conf_status=$conf_status=='x'?'x':'';*/
				DB::beginTransaction();
				try{

				$insert=DB::table('po_confirm_queue')->insertGetId(['po_number'=>$productionOrderId,'mfg_id'=>$manufacturerId,'qty'=>$serialsArray,'userstamp'=>$userId,'final_confirm'=>$conf_status,'remarks'=>$remarks,'conf_date'=>$mfg_date,'cost_loss'=>$quality_cost_loss
			]);
				$update=DB::table('eseal_'.$manufacturerId)->where(['po_number'=>$productionOrderId,'is_confirmed'=>'0','is_active'=>1])->update(['eseal_confirmed'=>$insert]);

				 $confirmQty = DB::table('eseal_'.$manufacturerId)
					 ->where(['po_number'=>$productionOrderId,'level_id'=>0,'is_active'=>1])
					 ->where(function($query){
					 	$query->where('is_confirmed','!=',0);
						$query->orWhere('eseal_confirmed','!=',0);
					 })->sum('pkg_qty');
					
					if($conf_status==1 || $confirmQty==$poQtyEA){
						$updatepo=DB::table('production_orders')->where(['id'=>$orderID])->update(['is_confirm'=>1]);
					}
					DB::commit();
				$status=1;
				$message ='Stock confirmed successfully in eseal, will update in Ecc shortly';

				} catch (Exception $e) {
					DB::rollBack();	
				$message ='Something Went wrong, Please try once again. '.$e->getMessage();
				$status=0;
				}
				/* confirm po*/
			}		    
		   
		   }
		} catch (Exception $e) {	
			DB::rollBack();		
			$status=0;
			$message = $e->getMessage();
		}

        $endTime = $this->getTime();
		return json_encode(['Status'=>$status, 'Message' =>'S-: '.$message.' total process time :'.($endTime-$startTime)]);
	}



	public function confirmProductionOrderEcc_old()
	{
        $startTime = $this->getTime();
        $status=0;
        $message=0;
		try{
//			DB::beginTransaction();
			$_podetails=DB::table('po_confirm_queue')->whereIn('status',[0,3])->first();
			 /*echo "hai";
			print_r($_podetails);exit;*/
			if(!$_podetails){
				throw new Exception("nothing to confirm", 1);
			}
			try{
			$manufacturerId=$_podetails->mfg_id;
			DB::table('po_confirm_queue')->where('id',$_podetails->id)->update(['status'=>2]);
			$po_number=$_podetails->po_number;
			$data=DB::table('eseal_'.$manufacturerId)->where('eseal_confirmed',$_podetails->id)->where('is_confirmed',0)->where('po_number',$po_number)->where('level_id',0)->pluck('primary_id')->toArray();
			// print_r($data);exit;
			/*echo "hai";
			print_r($data);exit;*/
			//$data=array_keys($dataMain);
/*			echo "<pre>";
			print_r($data);
			exit;*/
			// $update=DB::table('eseal_'.$manufacturerId)->whereIn('primary_id',$data)->update(['eseal_confirmed'=>2]);
			$am_ids=DB::table('eseal_'.$manufacturerId)->whereIn('primary_id',$data)->groupby('attribute_map_id')->where('level_id',0)->pluck(DB::raw('sum(pkg_qty) as cnt'),'attribute_map_id')->toArray();
// echo "<pre/>";print_r($am_ids);exit;

			$amc=[];
			$temp_amids=[];
			foreach ($am_ids as $akey => $avalue) {
				$am_id=$akey;
				$sku_info=DB::table('attribute_mapping')->where('attribute_name','sku_info')->where('attribute_map_id',$am_id)->value('value');
				$price_lot=DB::table('attribute_mapping')->where('attribute_name','price_lot')->where('attribute_map_id',$am_id)->value('value');
				$shift=DB::table('attribute_mapping')->where('attribute_name','shift')->where('attribute_map_id',$am_id)->value('value');
				$free_text=DB::table('attribute_mapping')->where('attribute_name','free_text')->where('attribute_map_id',$am_id)->value('value');
				$quality_cost_loss=DB::table('attribute_mapping')->where('attribute_name','quality_cost_loss')->where('attribute_map_id',$am_id)->value('value');
				 // echo $quality_cost_loss;exit;
				$temp=array('sku_info'=>$sku_info,'price_lot'=>$price_lot,'id'=>$am_id,'shift'=>$shift,'free_text'=>$free_text,'quality_cost_loss'=>$quality_cost_loss);
				$exists=0;
				foreach ($temp_amids as $tkey => $tvalue) {
					if($tvalue['sku_info']==$temp['sku_info'] && $tvalue['price_lot']==$temp['price_lot'] ){
						$exists=1;
						$amc[$tvalue['id']]=$amc[$tvalue['id']].','.$am_id;
					}
				}
				$temp_amids[$am_id]=$temp;
				// print_r($temp_amids[$am_id]);exit;
				if(!$exists)
					$amc[$am_id]=$am_id;
			}
			$poDetails=DB::table('production_orders as po')->join('locations as l','l.location_id','=','po.location_id')->join('products as p','p.product_id','=','po.product_id')->where(function($query) use($po_number){
			$query->where('po.erp_doc_no', '=', $po_number)->orWhere('po.eseal_doc_no', '=', $po_number);
		})->get(['po.id','po.product_id','po.location_id','po.erp_doc_no','po.eseal_doc_no','po.order_uom','po.qty','po.manufacturer_id','po.is_confirm','po.timestamp','po.is_erp','po.is_eseal','l.erp_code','p.material_code']); 
			$poDetails=$poDetails[0];
			$totolInc=count($amc);
			$inc=0;
/*echo "<pre/>";print_r($poDetails);exit;
*/
			foreach ($amc as $key => $value) {
				$inc++;
				$confirmQty=0;
				$amds=explode(',',$value);
				foreach ($amds as $akey => $avalue){
					$confirmQty+=$am_ids[$avalue];
				}

				// echo "<pre/>";print_r($temp_amids[$key]['shift']);exit;
				$sku=DB::table('sku_info')->where('sku_number',$temp_amids[$key]['sku_info'])->where('product_id',$poDetails->product_id)->first();
				$price=DB::table('price_lot')->where('price_lot',$temp_amids[$key]['price_lot'])->where('product_id',$poDetails->product_id)->first();
				
				// print_r($sku);exit;
				$convet=new Conversions();
				$c_uomQty=$convet->getUom($poDetails->product_id,$confirmQty,'EA',$poDetails->order_uom);
				
//				echo $c_uomQty; exit;
					$params='';
					$method = 'orderConfirmation';
					$methodType='POST';
					$inputArray=[];

					$inputArray['plant']=$poDetails->erp_code;
					// $inputArray['order_number']=$poDetails->po_number;
					$inputArray['order_number']=$poDetails->erp_doc_no==0?$poDetails->eseal_doc_no:$poDetails->erp_doc_no;
					// $inputArray['order_quantity']=$poDetails->qty;
					$inputArray['order_quantity']=number_format((float)$c_uomQty, 2, '.', '');
					$inputArray['price_lot']=$price->price_lot;
					$inputArray['mrp']=number_format((float)$price->mrp, 2, '.', '');
					$inputArray['sku']=$sku->sku_number;
					$inputArray['case_config']=$sku->case_config;
					//$inputArray['free_text']=$_podetails->remarks;
					$inputArray['free_text']=$temp_amids[$key]['free_text'];
					//$inputArray['batch']='batch'.$temp_amids[$key]['shift'].date("dm");
					/*$inputArray['shift']=$temp_amids[$key]['shift']==''?'A':$temp_amids[$key]['shift'];*/
					$inputArray['shift']=$temp_amids[$key]['shift'];
					$inputArray['final_confirmation_indc']=$_podetails->final_confirm==1?'X':"";
					/*$inputArray['final_confirmation_indc']=" ";
					if($totolInc==$inc)
					$inputArray['final_confirmation_indc']=$_podetails->final_confirm;*/

					//$inputArray['posting_date']=date("Y-m-d");
					/*   */
					// $inputArray['posting_date']=$_podetails->conf_date;
					// $inputArray['quality_cost_loss']=10;
					// $inputArray['quality_cost_loss']=$_podetails->cost_loss;
					$inputArray['posting_date']= date('Y-m-d');
					$inputArray['quality_cost_loss']=$temp_amids[$key]['quality_cost_loss'];
					
					$body=array('order_confirmation'=>$inputArray);

					if($poDetails->erp_doc_no==0){
						$method = 'orderCreationAndConfirmation';
						$orderData=[];
						$orderData['material_number']=$poDetails->material_code;
						$orderData['plant']=$poDetails->erp_code;
						$orderData['order_quantity']=$poDetails->qty;
						$orderData['order_uom']=$poDetails->order_uom;
						$body=array('order_creation'=>$orderData,'order_confirmation'=>$inputArray);
					}

					/*echo "<pre>";
					print_r(json_encode($body));exit;*/

					$this->erp=new ConnectErp($manufacturerId);
					$result=$this->erp->request($method,$params,$body,$methodType);
					  // print( $result);exit;
					$result=json_decode($result);

					if($result->status){
						$status=$result->status;
						$message=$result->message;
						$data=$result->data->order_conf_response[0];

						if($poDetails->erp_doc_no==0){
							if(isset($data->order_number)){
								DB::table('production_orders as po')->where('id',$poDetails->id)->update(['erp_doc_no'=>$data->order_number]);
								$poDetails->erp_doc_no=$data->order_number;
							}
						}

						$update=[];
						if(isset($data->batch))
							$update['batch_no']=$data->batch;
						if(isset($data->confirmation_number))
							$update['reference_value']=$data->confirmation_number;
						if(isset($data->confirmation_counter))
							$update['is_confirmed']=$data->confirmation_counter;
						$updatestatus=DB::table('eseal_'.$manufacturerId)->whereIn('attribute_map_id',$amds)->update($update);
						$insert=$update;
						$insert['attribute_map_id']=implode(',',$amds);
						$insert['status']=$result->status;
						$insert['message']=$result->message;
						
						$insert['po_number']=$poDetails->erp_doc_no;
						$insert['q_ref']=$_podetails->id;
						$inserStatus=DB::table('po_confirm')->insert($insert);

					} else {
						throw new Exception("E-".$result->message, 1);	
					}
			}
			DB::table('po_confirm_queue')->where('id',$_podetails->id)->update(['status'=>1]);
		} catch (Exception $e) {
			DB::table('po_confirm_queue')->where('id',$_podetails->id)->update(['status'=>3]);
			//DB::rollBack();			
			$status=0;
			$message = $e->getMessage();
		}
		} catch (Exception $e) {
			//DB::rollBack();			
			$status=0;
			$message = $e->getMessage();
		}

        $endTime = $this->getTime();
        return json_encode(['Status'=>$status, 'Message' =>'S-: '.$message.' total process time :'.($endTime-$startTime)]);
	}


	public function confirmProductionOrder_merge()
	{

        $startTime = $this->getTime();
        $status=0;
        $message=0;
		try{
        $accessToken = $this->_request->input('access_token');
        $mfg_id = $this->roleAccess->getMfgIdByToken($this->_request->input('access_token'));
			$this->mfg_id=$mfg_id;
			$userId=DB::table('users_token')->where('access_token',$accessToken)->value('user_id');
			$productionOrderId = $this->_request->input('production_order_id'); 
			$mfg_date = $this->_request->input('mfg_date');
			$storage_loc_code = trim($this->_request->input('storage_loc_code'));
			$conf_status = trim($this->_request->input('confirmation_status'));
			$remarks = trim($this->_request->input('remarks'));
			$quality_cost_loss = trim($this->_request->input('quality_cost_loss'));
			$posting_date = trim($this->_request->input('posting_date'));
			$manufacturerId = $this->roleAccess->getMfgIdByToken($accessToken);
			$orderID = DB::table('production_orders')->where('erp_doc_no', $productionOrderId)->value('id');
		
//			
			//echo $productionOrderId;
			$data=DB::table('eseal_'.$manufacturerId)->where('is_confirmed',0)->where('po_number',$productionOrderId)->where('level_id',0)->pluck('primary_id')->toArray();
			//echo count($data); exit;
			//print_r($data);
			if(empty($data)){
				throw new Exception("No data against PO", 1);
				
			}
			//print_r($data);exit;
			$am_ids=DB::table('eseal_'.$manufacturerId)->whereIn('primary_id',$data)->groupby('attribute_map_id')->where('level_id',0)->pluck(DB::raw('sum(pkg_qty) as cnt'),'attribute_map_id')->toArray();
			$amc=[];
			$temp_amids=[];
			foreach ($am_ids as $akey => $avalue) {
				$am_id=$akey;
				$sku_info=DB::table('attribute_mapping')->where('attribute_name','sku_info')->where('attribute_map_id',$am_id)->value('value');
				$price_lot=DB::table('attribute_mapping')->where('attribute_name','price_lot')->where('attribute_map_id',$am_id)->value('value');
				$shift=DB::table('attribute_mapping')->where('attribute_name','shift')->where('attribute_map_id',$am_id)->value('value');
				$free_text=DB::table('attribute_mapping')->where('attribute_name','free_text')->where('attribute_map_id',$am_id)->value('value');
				$quality_cost_loss=DB::table('attribute_mapping')->where('attribute_name','quality_cost_loss')->where('attribute_map_id',$am_id)->value('value');
				 // echo $quality_cost_loss;exit;
				$temp=array('sku_info'=>$sku_info,'price_lot'=>$price_lot,'id'=>$am_id,'shift'=>$shift,'free_text'=>$free_text,'quality_cost_loss'=>$quality_cost_loss);
				$exists=0;
				foreach ($temp_amids as $tkey => $tvalue) {
					if($tvalue['sku_info']==$temp['sku_info'] && $tvalue['price_lot']==$temp['price_lot'] ){
						$exists=1;
						$amc[$tvalue['id']]=$amc[$tvalue['id']].','.$am_id;
					}
				}
				$temp_amids[$am_id]=$temp;
				// print_r($temp_amids[$am_id]);exit;
				if(!$exists)
					$amc[$am_id]=$am_id;
			}
			$poDetails=DB::table('production_orders as po')->join('locations as l','l.location_id','=','po.location_id')->join('products as p','p.product_id','=','po.product_id')->where(function($query) use($productionOrderId){
			$query->where('po.erp_doc_no', '=', $productionOrderId)->orWhere('po.eseal_doc_no', '=', $productionOrderId);
		})->get(['po.id','po.product_id','po.location_id','po.erp_doc_no','po.eseal_doc_no','po.order_uom','po.qty','po.manufacturer_id','po.is_confirm','po.timestamp','po.is_erp','po.is_eseal','l.erp_code','p.material_code']); 
			$poDetails=$poDetails[0];
			$totolInc=count($amc);
			$inc=0;
			foreach ($amc as $key => $value) {
				$inc++;
				$confirmQty=0;
				$amds=explode(',',$value);
				foreach ($amds as $akey => $avalue){
					$confirmQty+=$am_ids[$avalue];
				}

				$sku=DB::table('sku_info')->where('sku_number',$temp_amids[$key]['sku_info'])->where('product_id',$poDetails->product_id)->first();
				$price=DB::table('price_lot')->where('price_lot',$temp_amids[$key]['price_lot'])->where('product_id',$poDetails->product_id)->first();
				
				$convet=new Conversions();
				$c_uomQty=$convet->getUom($poDetails->product_id,$confirmQty,'EA',$poDetails->order_uom);
				
//				echo $c_uomQty; exit;
					$params='';
					$method = 'orderConfirmation';
					$methodType='POST';
					$inputArray=[];

					$inputArray['plant']=$poDetails->erp_code;
					// $inputArray['order_number']=$poDetails->po_number;
					$inputArray['order_number']=$poDetails->erp_doc_no==0?$poDetails->eseal_doc_no:$poDetails->erp_doc_no;
					// $inputArray['order_quantity']=$poDetails->qty;
					$inputArray['order_quantity']=number_format((float)$c_uomQty, 2, '.', '');
					$inputArray['price_lot']=$price->price_lot;
					$inputArray['mrp']=number_format((float)$price->mrp, 2, '.', '');
					$inputArray['sku']=$sku->sku_number;
					$inputArray['case_config']=$sku->case_config;
					//$inputArray['free_text']=$_podetails->remarks;
					$inputArray['free_text']=$temp_amids[$key]['free_text'];
					//$inputArray['batch']='batch'.$temp_amids[$key]['shift'].date("dm");
					/*$inputArray['shift']=$temp_amids[$key]['shift']==''?'A':$temp_amids[$key]['shift'];*/
					$inputArray['shift']=$temp_amids[$key]['shift'];
					$inputArray['final_confirmation_indc']=$conf_status==1?'X':"";
					/*$inputArray['final_confirmation_indc']=" ";
					if($totolInc==$inc)
					$inputArray['final_confirmation_indc']=$_podetails->final_confirm;*/

					//$inputArray['posting_date']=date("Y-m-d");
					/*   */
					// $inputArray['posting_date']=$_podetails->conf_date;
					// $inputArray['quality_cost_loss']=10;
					// $inputArray['quality_cost_loss']=$_podetails->cost_loss;
					//$inputArray['posting_date']= date('Y-m-d');
					$inputArray['posting_date']= $posting_date;
					$inputArray['quality_cost_loss']=$temp_amids[$key]['quality_cost_loss'];
					
					$body=array('order_confirmation'=>$inputArray);

					if($poDetails->erp_doc_no==0){
						$method = 'orderCreationAndConfirmation';
						$orderData=[];
						$orderData['material_number']=$poDetails->material_code;
						$orderData['plant']=$poDetails->erp_code;
						$orderData['order_quantity']=$poDetails->qty;
						$orderData['order_uom']=$poDetails->order_uom;
						$body=array('order_creation'=>$orderData,'order_confirmation'=>$inputArray);
					}

					/*echo "<pre>";
					print_r(json_encode($body));exit;*/

					$this->erp=new ConnectErp($manufacturerId);
					$result=$this->erp->request($method,$params,$body,$methodType);
					  //print( $result);exit;
					$result=json_decode($result);

					if($result->status){
						$status=$result->status;
						$message=$result->message;
						$data=$result->data->order_conf_response[0];

						if($poDetails->erp_doc_no==0){
							if(isset($data->order_number)){
								DB::table('production_orders as po')->where('id',$poDetails->id)->update(['erp_doc_no'=>$data->order_number]);
								$poDetails->erp_doc_no=$data->order_number;
							}
						}

						$update=[];
						if(isset($data->batch))
							$update['batch_no']=$data->batch;
						if(isset($data->confirmation_number))
							$update['reference_value']=$data->confirmation_number;
						if(isset($data->confirmation_counter))
							$update['is_confirmed']=$data->confirmation_counter;
						$updatestatus=DB::table('eseal_'.$manufacturerId)->whereIn('attribute_map_id',$amds)->update($update);
						$insert=$update;
						$insert['attribute_map_id']=implode(',',$amds);
						$insert['status']=$result->status;
						$insert['message']=$result->message;
						
						$insert['po_number']=$poDetails->erp_doc_no;
						$insert['q_ref']=$poDetails->id;
						$inserStatus=DB::table('po_confirm')->insert($insert);

						$conf_qty=number_format((float)$c_uomQty, 2, '.', '');
/*--------------------insert into po_confirm_queue-------------------------------- */
						$insert_po['po_number']=$poDetails->erp_doc_no;
						$insert_po['mfg_id']=$this->roleAccess->getMfgIdByToken($this->_request->input('access_token'));
						$insert_po['status']=$result->status;
						$insert_po['qty']=$conf_qty;
						$insert_po['userstamp']=$userId;
						$insert_po['final_confirm']=$conf_status;
						$insert_po['po_id']=$poDetails->id;
						$insert_po_queue=DB::table('po_confirm_queue')->insert($insert_po);
/*----------finish insert into po_confirm_queue-----------------------------------*/
						
						if($poDetails==$conf_qty){
			DB::table('production_orders')->where('id',$poDetails->id)->update(['is_confirm'=>1]);
			}
						DB::table('po_confirm_queue')->where('id',$poDetails->id)->update(['status'=>1]);

			}
			else {
				throw new Exception("E-".$result->message, 1);	
			DB::table('po_confirm_queue')->where('id',$poDetails->id)->update(['status'=>3]);
					}
		}

	}
			catch (Exception $e) {
			//DB::rollBack();			
			$status=0;
			$message = $e->getMessage();
			//echo $message;
		}
		

        $endTime = $this->getTime();
        return json_encode(['Status'=>$status, 'Message' =>'S-: '.$message.' total process time :'.($endTime-$startTime)]);
	}
public function goodsMovement()
	{

        $startTime = $this->getTime();
        $status=0;
        $message=0;
		try{
        $accessToken = $this->_request->input('access_token');
        $mfg_id = $this->roleAccess->getMfgIdByToken($this->_request->input('access_token'));
			$this->mfg_id=$mfg_id;
			$userId=DB::table('users_token')->where('access_token',$accessToken)->value('user_id');
			$productionOrderId = $this->_request->input('production_order_id'); 
			$mfg_date = $this->_request->input('mfg_date');
			$storage_loc_code = trim($this->_request->input('storage_loc_code'));
			$conf_status = trim($this->_request->input('confirmation_status'));
			$action = trim($this->_request->input('action'));
			$posting_date = trim($this->_request->input('posting_date'));
			$manufacturerId = $this->roleAccess->getMfgIdByToken($accessToken);
			$itemData=$this->_request->input('itemData');

			$orderID = DB::table('production_orders')->where('erp_doc_no', $productionOrderId)->value('id');
			$data=DB::table('eseal_'.$manufacturerId)->where('is_confirmed',0)->where('po_number',$productionOrderId)->where('level_id',0)->pluck('primary_id')->toArray();
			
			if(empty($data)){
				throw new Exception("No data against PO", 1);
				
			}
		
			$am_ids=DB::table('eseal_'.$manufacturerId)->whereIn('primary_id',$data)->groupby('attribute_map_id')->where('level_id',0)->pluck(DB::raw('sum(pkg_qty) as cnt'),'attribute_map_id')->toArray();
			$amc=[];
			$temp_amids=[];
			foreach ($am_ids as $akey => $avalue) {
				$am_id=$akey;
				$sku_info=DB::table('attribute_mapping')->where('attribute_name','sku_info')->where('attribute_map_id',$am_id)->value('value');
				$price_lot=DB::table('attribute_mapping')->where('attribute_name','price_lot')->where('attribute_map_id',$am_id)->value('value');
				$shift=DB::table('attribute_mapping')->where('attribute_name','shift')->where('attribute_map_id',$am_id)->value('value');
				$free_text=DB::table('attribute_mapping')->where('attribute_name','free_text')->where('attribute_map_id',$am_id)->value('value');
				$quality_cost_loss=DB::table('attribute_mapping')->where('attribute_name','quality_cost_loss')->where('attribute_map_id',$am_id)->value('value');
				 // echo $quality_cost_loss;exit;
				$temp=array('sku_info'=>$sku_info,'price_lot'=>$price_lot,'id'=>$am_id,'shift'=>$shift,'free_text'=>$free_text,'quality_cost_loss'=>$quality_cost_loss);
				$exists=0;
				foreach ($temp_amids as $tkey => $tvalue) {
					if($tvalue['sku_info']==$temp['sku_info'] && $tvalue['price_lot']==$temp['price_lot'] ){
						$exists=1;
						$amc[$tvalue['id']]=$amc[$tvalue['id']].','.$am_id;
					}
				}
				$temp_amids[$am_id]=$temp;
				
				if(!$exists)
					$amc[$am_id]=$am_id;
			}
			$poDetails=DB::table('production_orders as po')->join('locations as l','l.location_id','=','po.location_id')->join('products as p','p.product_id','=','po.product_id')->where(function($query) use($productionOrderId){
			$query->where('po.erp_doc_no', '=', $productionOrderId)->orWhere('po.eseal_doc_no', '=', $productionOrderId);
		})->get(['po.id','po.product_id','po.location_id','po.erp_doc_no','po.eseal_doc_no','po.order_uom','po.qty','po.manufacturer_id','po.is_confirm','po.timestamp','po.is_erp','po.is_eseal','l.erp_code','p.material_code']); 
			$poDetails=$poDetails[0];
			$totolInc=count($amc);
			$inc=0;
			foreach ($amc as $key => $value) {
				$inc++;
				$confirmQty=0;
				$amds=explode(',',$value);
				foreach ($amds as $akey => $avalue){
					$confirmQty+=$am_ids[$avalue];
				}

				$sku=DB::table('sku_info')->where('sku_number',$temp_amids[$key]['sku_info'])->where('product_id',$poDetails->product_id)->first();
				$price=DB::table('price_lot')->where('price_lot',$temp_amids[$key]['price_lot'])->where('product_id',$poDetails->product_id)->first();
				
				$convet=new Conversions();
				$c_uomQty=$convet->getUom($poDetails->product_id,$confirmQty,'EA',$poDetails->order_uom);
				
					$params='';
					$method = 'goodsMovement';
					$methodType='POST';
					$inputArray=[];

					$inputArray['plant']=$poDetails->erp_code;
					// $inputArray['order_number']=$poDetails->po_number;
					$inputArray['order_number']=$poDetails->erp_doc_no==0?$poDetails->eseal_doc_no:$poDetails->erp_doc_no;
					// $inputArray['order_quantity']=$poDetails->qty;
					$inputArray['order_quantity']=number_format((float)$c_uomQty, 2, '.', '');
					$inputArray['price_lot']=$price->price_lot;
					$inputArray['mrp']=number_format((float)$price->mrp, 2, '.', '');
					$inputArray['sku']=$sku->sku_number;
					$inputArray['case_config']=$sku->case_config;
					//$inputArray['free_text']=$_podetails->remarks;
					$inputArray['free_text']=$temp_amids[$key]['free_text'];
					//$inputArray['batch']='batch'.$temp_amids[$key]['shift'].date("dm");
					/*$inputArray['shift']=$temp_amids[$key]['shift']==''?'A':$temp_amids[$key]['shift'];*/
					$inputArray['shift']=$temp_amids[$key]['shift'];
					$inputArray['final_confirmation_indc']=$conf_status==1?'X':"";
					$inputArray['posting_date']= $posting_date;
					$inputArray['quality_cost_loss']=$temp_amids[$key]['quality_cost_loss'];
					$inputArray['batch']="";
/*					$inputArray['batch']=$this->_request->input('batch');
*/					$inputArray['action']=$action==1?'P':'G';

					$inputArray['itemData']=json_decode($this->_request->input('itemData'));
				
					$body=array('goods_movement_request'=>$inputArray);

//print_r($body);exit;
					$this->erp=new ConnectErp($manufacturerId);
					$result=$this->erp->request($method,$params,$body,$methodType);
					  //print( $result);
					$result=json_decode($result);

					if($result->status==1){
						$status=$result->status;
						$message=$result->message;
						$goodsMovement['goods_movement_screen']=$result->data->goods_movement_screen;
						$goodsMovement['batch_stock']=$result->data->batch_stock;
						//echo $status;exit;
						

			}
			else {
				throw new Exception("E-".$result->message, 1);	
			
					}
		}

	}
			catch (Exception $e) {
			//DB::rollBack();			
			$status=0;
			$message = $e->getMessage();
			//echo $message;
		}
		

        $endTime = $this->getTime();
        return json_encode(['Status'=>$status, 'Message' =>'S-: '.$message.' total process time :'.($endTime-$startTime),'Data'=>$goodsMovement]);
	}	

public function confirmProductionOrder()
	{
            $startTime = $this->getTime();
            $status=0;
			$message = '';

		
		try{ 
			$accessToken = $this->_request->input('access_token');
			$module_id = $this->_request->input('module_id');
			$userId=DB::table('users_token')->where('access_token',$accessToken)->value('user_id');
			$productionOrderId = $this->_request->input('production_order_id');
			//echo "hi".$productionOrderId;
			//print_r($productionOrderId);
			//$productionOrderId1 = '2';
			//$request->request->add(['variable' => 'value']);
			//echo $production_order_id;exit;
			$mfg_date = $this->_request->input('mfg_date');
			$storage_loc_code = trim($this->_request->input('storage_loc_code'));
			$conf_status = trim($this->_request->input('confirmation_status'));
			$remarks = trim($this->_request->input('remarks'));
			$quality_cost_loss = trim($this->_request->input('quality_cost_loss'));
			// echo $conf_status;exit;
			$posting_date = trim($this->_request->input('posting_date'));
			$manufacturerId = $this->roleAccess->getMfgIdByToken($accessToken);
			$action = trim($this->_request->input('action'));
			$itemData=$this->_request->input('itemData');
			$orderID = DB::table('production_orders')->where('erp_doc_no', $productionOrderId)->value('id');
			//print_r  (json_decode($itemData));exit;
			$esealErp=0;
			if(!$orderID){
				$orderID = DB::table('production_orders')->where('eseal_doc_no', $productionOrderId)->value('id');
				// echo $orderID;exit;
				$esealErp=1;
				if(empty($orderID)){
					throw new Exception('In-valid Production Order Id.');
				}
				$serialsArray = DB::table('eseal_'.$manufacturerId)->where(['po_number'=>$productionOrderId,'is_confirmed'=>'0','is_active'=>1,'eseal_confirmed'=>0])->count('primary_id');

			/*	$serialsArray = DB::table('eseal_'.$manufacturerId)->where(['is_active'=>1,'eseal_confirmed'=>0,'po_number'=>$productionOrderId])->count('primary_id'); (commented by jyothi)*/

				/*$serialsArray = DB::table('eseal_'.$manufacturerId)->where(['is_active'=>1,'is_confirmed'=>0,'po_number'=>$productionOrderId])->count('primary_id'); (commented by ruchita.g)*/

				/*print "hiii";
				print_r($serialsArray);
			*/
				if($serialsArray<=0){
					/*return json_encode(Array('Status'=>0, 'Message' =>'Server: No Data for Production Order ID or Already Confirmed'));*/
					throw new Exception('No Data for Production Order ID or Already Confirmed');
				}else{
				$insert=DB::table('po_confirm_queue')->insertGetId(['po_number'=>$productionOrderId,'mfg_id'=>$manufacturerId,'qty'=>$serialsArray,'userstamp'=>$userId,'final_confirm'=>$conf_status,'remarks'=>$remarks,'conf_date'=>$mfg_date,'cost_loss'=>$quality_cost_loss]);
				// echo $insert;exit;
				$update=DB::table('eseal_'.$manufacturerId)->where(['po_number'=>$productionOrderId,'is_confirmed'=>'0','is_active'=>1,'eseal_confirmed'=>0])->update(['eseal_confirmed'=>$insert]);
				/*$update=DB::table('production_orders')->where(['id'=>$orderID])->update(['is_confirm'=>1]); chanage by jyothi*/

					/*$status=1;
					$message ='Stock confirmed successfully in eseal, will update in Ecc shortly';*/

				}

			}  else {
            //sleep(200000); 

			if(empty($orderID)){
				throw new Exception('In-valid Production Order Id.');
			}

			$serialsArray = DB::table('eseal_'.$manufacturerId)->where(['is_active'=>1,'eseal_confirmed'=>0,'po_number'=>$productionOrderId])->count('primary_id');
			/*$serialsArray = DB::table('eseal_'.$manufacturerId)->where(['is_active'=>1,'is_confirmed'=>0,'po_number'=>$productionOrderId])->count('primary_id'); commented by ruchita.G*/
				//echo $serialsArray;
			if($serialsArray<=0){
				throw new Exception('No Data for Production Order ID or Already Confirmed1111');
			}

			$po_details=DB::table('production_orders as p')->where('erp_doc_no',$productionOrderId)->first();

			$convet=new Conversions();
			$poQtyEA=$convet->getUom($po_details->product_id,$po_details->qty,$po_details->order_uom,'EA');
			// echo $serialsArray;exit;

			if($serialsArray<=0){
				throw new Exception('No Data for Production Order ID or Already Confirmed1111');
				//exit;
			}else{
				/*throw new Exception('coming in else');
				echo "hii test ss";exit;*/
				/* confirm po start */
				$conf_status=$conf_status==1?1:0;
				/*$conf_status=$conf_status=='x'?'x':'';*/
				DB::beginTransaction();
				try{

				$insert=DB::table('po_confirm_queue')->insertGetId(['po_number'=>$productionOrderId,'mfg_id'=>$manufacturerId,'qty'=>$serialsArray,'userstamp'=>$userId,'final_confirm'=>$conf_status,'remarks'=>$remarks,'conf_date'=>$mfg_date,'cost_loss'=>$quality_cost_loss
			]);
				$update=DB::table('eseal_'.$manufacturerId)->where(['po_number'=>$productionOrderId,'is_confirmed'=>'0','is_active'=>1])->update(['eseal_confirmed'=>$insert]);

				 $confirmQty = DB::table('eseal_'.$manufacturerId)
					 ->where(['po_number'=>$productionOrderId,'level_id'=>0,'is_active'=>1])
					 ->where(function($query){
					 	$query->where('is_confirmed','!=',0);
						$query->orWhere('eseal_confirmed','!=',0);
					 })->sum('pkg_qty');
					
					if($conf_status==1 || $confirmQty==$poQtyEA){
						$updatepo=DB::table('production_orders')->where(['id'=>$orderID])->update(['is_confirm'=>1]);
					}
					
					//var_dump($resultProdArray);
					/*if($resultProdArray['Status']==0){
						echo "error mam";
						throw new Exception($resultProdArray['Message']);
					}
					if($resultProdArray['Status']==1){
						$destLocationId = $resultProdArray['total process time'];
						echo "finally".$destLocationId;
					}*/

					
					// return Array('Status'=>$status, 'Message' =>'Server: '.$message.' total process time :'.($endTime-$startTime));
					//echo $productionOrderId;exit;
				/*	$resultProdArrayDecode = json_decode($resultProdArray);
					print_r($resultProdArrayDecode);
					exit;*/

					// return Array('Status'=>$status, 'Message'=>$message, 'Id'=>$id);
					/*
					 return Array(['Status'=>$status, 'Message' =>'Server: '.$message.' total process time :'.($endTime-$startTime)]);
        			Log::info('==='.print_r($destLocation,true));
					if($destLocation['Status']==0){
						throw new Exception($destLocation['Message']);
					}
					if($destLocation['Status']==1){
						$destLocationId = $destLocation['Id'];
					}
					*/
						
					DB::commit();
				//print_r($itemData);exit;
					$resultProdArray = $this->confirmProductionOrderEcc($productionOrderId,$posting_date,$action,$itemData,$accessToken,$module_id);

					//echo "------------------------>>>><<<<<<-------------------";
						print_r($resultProdArray);
						exit;
				$status=1;
				$message ='Stock confirmed successfully in eseal, will update in Ecc shortly';
			/*$status=$resultProdArray->Status;
			$message=$resultProdArray->Message;*/
			//print_r($status);exit;

				} catch (Exception $e) {
					DB::rollBack();	
				$message ='Something Went wrong, Please try once again. '.$e->getMessage();
				$status=0;
				}
			}		    
		   
		   }
		} catch (Exception $e) {	
			DB::rollBack();		
			$status=0;
			$message = $e->getMessage();
			return json_encode(Array('Status'=> $status, 'Message' =>'S-: '.$message));
		}

        $endTime = $this->getTime();
		//return json_encode([$resultProdArray]);
	}



	public function confirmProductionOrderEcc($productionOrderId,$posting_date,$action,$itemData,$accessToken,$module_id)
	{
		
        $startTime = $this->getTime();
        $status=0;
        $message=0;
   

		try{
//			DB::beginTransaction();

			$_podetails=DB::table('po_confirm_queue')->where('po_number',$productionOrderId)->whereIn('status',[0,3])->first();
			
			//$_podetails=DB::table('po_confirm_queue')->whereIn('status',[0,3])->first();
			
		//echo "hii".print_r($_podetails);

			if(!$_podetails){
				throw new Exception("nothing to confirm", 1);
			}
			
			try{
					
			$manufacturerId=$_podetails->mfg_id;
			DB::table('po_confirm_queue')->where('id',$_podetails->id)->update(['status'=>2]);
			$po_number=$_podetails->po_number;
			$data=DB::table('eseal_'.$manufacturerId)->where('eseal_confirmed',$_podetails->id)->where('is_confirmed',0)->where('po_number',$po_number)->where('level_id',0)->pluck('primary_id')->toArray();
			if(!$data){
				throw new Exception("nothing to confirm", 1);

			}
			/*$data=DB::table('eseal_'.$manufacturerId)->where('is_confirmed',0)->
			where('po_number',$productionOrderId)->where('level_id',0)->pluck('primary_id')->toArray();*/
			//echo "hi";
			//print_r($data);
			//exit;
			
			// $update=DB::table('eseal_'.$manufacturerId)->whereIn('primary_id',$data)->update(['eseal_confirmed'=>2]);
			$am_ids=DB::table('eseal_'.$manufacturerId)->whereIn('primary_id',$data)->groupby('attribute_map_id')->where('level_id',0)->
			pluck(DB::raw('sum(pkg_qty) as cnt'),'attribute_map_id')->toArray();
 			//echo "<pre/>";print_r($am_ids);

			$amc=[];
			$temp_amids=[];
			foreach ($am_ids as $akey => $avalue) {
			//	echo "foreach ";
				$am_id=$akey;
				$sku_info=DB::table('attribute_mapping')->where('attribute_name','sku_info')->where('attribute_map_id',$am_id)->value('value');
				$price_lot=DB::table('attribute_mapping')->where('attribute_name','price_lot')->where('attribute_map_id',$am_id)->value('value');
				$shift=DB::table('attribute_mapping')->where('attribute_name','shift')->where('attribute_map_id',$am_id)->value('value');
				$free_text=DB::table('attribute_mapping')->where('attribute_name','free_text')->where('attribute_map_id',$am_id)->value('value');
				$quality_cost_loss=DB::table('attribute_mapping')->where('attribute_name','quality_cost_loss')->where('attribute_map_id',$am_id)->value('value');
				// echo $quality_cost_loss;exit;
				$temp=array('sku_info'=>$sku_info,'price_lot'=>$price_lot,'id'=>$am_id,'shift'=>$shift,'free_text'=>$free_text,'quality_cost_loss'=>$quality_cost_loss);
				$exists=0;
				foreach ($temp_amids as $tkey => $tvalue) {
					if($tvalue['sku_info']==$temp['sku_info'] && $tvalue['price_lot']==$temp['price_lot'] ){
						$exists=1;
						$amc[$tvalue['id']]=$amc[$tvalue['id']].','.$am_id;
					}
				}
				$temp_amids[$am_id]=$temp;
			/*	echo "here you go";
			print_r($temp_amids[$am_id]);
			echo "h";*/
				if(!$exists)
					$amc[$am_id]=$am_id;
			}
			$poDetails=DB::table('production_orders as po')->join('locations as l','l.location_id','=','po.location_id')->join('products as p','p.product_id','=','po.product_id')->where(function($query) use($productionOrderId){
			$query->where('po.erp_doc_no', '=', $productionOrderId)->orWhere('po.eseal_doc_no', '=', $productionOrderId);
		})->get(['po.id','po.product_id','po.location_id','po.erp_doc_no','po.eseal_doc_no','po.order_uom','po.qty','po.manufacturer_id','po.is_confirm','po.timestamp','po.is_erp','po.is_eseal','l.erp_code','p.material_code']); 
			$poDetails=$poDetails[0];
			$totolInc=count($amc);
			$inc=0;
		//	echo "<pre/>";print_r($poDetails);

			foreach ($amc as $key => $value) {
				$inc++;
				$confirmQty=0;
				$amds=explode(',',$value);
				foreach ($amds as $akey => $avalue){
				
				$confirmQty+=$am_ids[$avalue];
				}
				/*echo "br";
				print_r($temp_amids[$key]['sku_info']);
				echo "br";
				print_r($temp_amids[$key]['price_lot']);
				echo "br";
				print_r($temp_amids[$key]['shift']);*/
				
				//echo "this only<pre/>";print_r($temp_amids[$key]['shift']);
				
				$sku=DB::table('sku_info')->where('sku_number',$temp_amids[$key]['sku_info'])->where('product_id',$poDetails->product_id)->first();
				$price=DB::table('price_lot')->where('price_lot',$temp_amids[$key]['price_lot'])->where('product_id',$poDetails->product_id)->first();
			//	echo "<pre/>";print_r($price);
				//echo "<prerrr/>";print_r($sku);
				$convet=new Conversions();
				$c_uomQty=$convet->getUom($poDetails->product_id,$confirmQty,'EA',$poDetails->order_uom);
				
					$params='';
					$method = 'orderConfirmation';
					$methodType='POST';
					$inputArray=[];
					//print_r($inputArray);
					

					$inputArray['plant']=$poDetails->erp_code;
					//echo $inputArray['plant']."<br>1";
					// $inputArray['order_number']=$poDetails->po_number;
					$inputArray['order_number']=$poDetails->erp_doc_no==0?$poDetails->eseal_doc_no:$poDetails->erp_doc_no;
					//echo $inputArray['order_number']."<br>2";
					// $inputArray['order_quantity']=$poDetails->qty;
					$inputArray['order_quantity']=number_format((float)$c_uomQty, 2, '.', '');
					//echo "<br>";
					//echo $inputArray['order_quantity']."<br>3";
					//echo "<br>";
					$inputArray['price_lot']=$price->price_lot;
					//echo $inputArray['price_lot']."<br>4444444444444";
					$inputArray['mrp']=number_format((float)$price->mrp, 2, '.', '');
					//echo $inputArray['mrp']."<br>5";
					$inputArray['sku']=$sku->sku_number;
					//echo $inputArray['sku']."<br>6";
					$inputArray['case_config']=$sku->case_config;
				//	echo $inputArray['case_config']."<br>7";
					//$inputArray['free_text']=$_podetails->remarks;
					//echo "hey";
					$inputArray['free_text']=$temp_amids[$key]['free_text'];
					//echo $inputArray['free_text']."<br>8";
					//$inputArray['batch']='batch'.$temp_amids[$key]['shift'].date("dm");
					/*$inputArray['shift']=$temp_amids[$key]['shift']==''?'A':$temp_amids[$key]['shift'];*/
					$inputArray['shift']=$temp_amids[$key]['shift'];

					//echo $inputArray['shift']."<br>9";
					
					
					$inputArray['final_confirmation_indc']=$_podetails->final_confirm==1?'X':'';
					
					//echo $inputArray['final_confirmation_indc']."<br>10";
					/*$inputArray['final_confirmation_indc']=" ";
					if($totolInc==$inc)
					$inputArray['final_confirmation_indc']=$_podetails->final_confirm;*/

					//$inputArray['posting_date']=date("Y-m-d");
					/*   */
					// $inputArray['posting_date']=$_podetails->conf_date;
					// $inputArray['quality_cost_loss']=10;
					// $inputArray['quality_cost_loss']=$_podetails->cost_loss;
					//$inputArray['posting_date']= date('Y-m-d');
					$inputArray['posting_date']= $posting_date;
					$inputArray['quality_cost_loss']=$temp_amids[$key]['quality_cost_loss'];
					$inputArray['action']=$action==1?'P':'G';

					$inputArray['itemData']=json_decode($itemData);
					//echo $inputArray['quality_cost_loss']."<br>12";
				//echo "hi, till here ok";
					$body=array('order_confirmation'=>$inputArray);
					//echo "here Im";
					if($poDetails->erp_doc_no==0){
						$method = 'orderCreationAndConfirmation';
						$orderData=[];
						$orderData['material_number']=$poDetails->material_code;
						$orderData['plant']=$poDetails->erp_code;
						$orderData['order_quantity']=$poDetails->qty;
						$orderData['order_uom']=$poDetails->order_uom;
						$body=array('order_creation'=>$orderData,'order_confirmation'=>$inputArray);
					}

					/*echo "<pre>";
					print_r($inputArray);
					//print_r(json_encode($body));
					exit;
						*/
	/*$transitionId=DB::table('transaction_master')->where('name','=','PO Confirmed')->value('id');
	$po_num=$poDetails->erp_doc_no==0?$poDetails->eseal_doc_no:$poDetails->erp_doc_no;
	$srcLocationId=DB::table('production_orders')->where(function($query)use($po_num) {
                $query->where('erp_doc_no','=',$po_num)->orWhere('eseal_doc_no','=',$po_num); 
            })->value('location_id');
	$confirm_ids = DB::table('eseal_'.$manufacturerId)->where(['po_number'=>$po_num,'level_id'=>0,'is_active'=>1])->where('is_confirmed','=',0)->where('eseal_confirmed',$_podetails->id)->pluck('primary_id')->toArray();
	print_r($confirm_ids);
	
					$request = Request::create('scoapi/UpdateTracking', 'POST', array('module_id'=>$module_id,'access_token'=>$accessToken,'codes'=>implode(",",$confirm_ids),'srcLocationId'=>$srcLocationId,'destLocationId'=>0,'transitionId'=>$transitionId,'internalTransfer'=>0));
					
						$originalInput=$this->_request->all();
					$this->_request->replace($request->all());
					$res = app()->handle($request);
					$response = $res->getContent();
						$response = json_decode($response,true);
						print_r($response);exit;*/

					$this->erp=new ConnectErp($manufacturerId);
					$result=$this->erp->request($method,$params,$body,$methodType);
					  
					$result=json_decode($result);
					/*print_r($result);
					exit;*/

					if($result->status){
						$status=$result->status;
						$message=$result->message;
						$data=$result->data->order_conf_response[0];

						if($poDetails->erp_doc_no==0){
							if(isset($data->order_number)){
								DB::table('production_orders as po')->where('id',$poDetails->id)->update(['erp_doc_no'=>$data->order_number]);
								$poDetails->erp_doc_no=$data->order_number;
							}
						}
						/*----track_update*/
						$po_num=$poDetails->erp_doc_no==0?$poDetails->eseal_doc_no:$poDetails->erp_doc_no;
	//echo $po_num;
						$transitionId=DB::table('transaction_master')->where('name','=','PO Confirmed')->value('id');
	$srcLocationId=DB::table('production_orders')->where(function($query)use($po_num) {
                $query->where('erp_doc_no','=',$po_num)->orWhere('eseal_doc_no','=',$po_num); 
            })->value('location_id');
	$confirm_ids = DB::table('eseal_'.$manufacturerId)->where(['po_number'=>$po_num,'level_id'=>0,'is_active'=>1])->where('is_confirmed','=',0)->where('eseal_confirmed',$_podetails->id)->pluck('primary_id')->toArray();
	//print_r($confirm_ids);
	
					$request = Request::create('scoapi/UpdateTracking', 'POST', array('module_id'=>$module_id,'access_token'=>$accessToken,'codes'=>implode(",",$confirm_ids),'srcLocationId'=>$srcLocationId,'destLocationId'=>0,'transitionTime'=>date("Y-m-d h:i:s"),'transitionId'=>$transitionId,'internalTransfer'=>0));
					
						$originalInput=$this->_request->all();
					$this->_request->replace($request->all());
					$res = app()->handle($request);
					$response = $res->getContent();
						$response = json_decode($response,true);
						//print_r($response);exit;
						/*-------------ends-------*/

						$update=[];
						if(isset($data->batch))
							$update['batch_no']=$data->batch;
						if(isset($data->confirmation_number))
							$update['reference_value']=$data->confirmation_number;
						if(isset($data->confirmation_counter))
							$update['is_confirmed']=$data->confirmation_counter;
						$updatestatus=DB::table('eseal_'.$manufacturerId)->whereIn('attribute_map_id',$amds)->where('eseal_confirmed',$_podetails->id)->update($update);
						$insert=$update;
						$insert['attribute_map_id']=implode(',',$amds);
						$insert['status']=$result->status;
						$insert['message']=$result->message;						
						$insert['po_number']=$poDetails->erp_doc_no;
						$insert['q_ref']=$_podetails->id;
						$inserStatus=DB::table('po_confirm')->insert($insert);
						$transitionId=DB::table('transaction_master')->where('name','=','PO Confirmed')->value('id');



						
					} else {

						DB::table('po_confirm_queue')->where('id',$_podetails->id)->delete();
						DB::table('eseal_'.$manufacturerId)->where('eseal_confirmed',$_podetails->id)->update(['eseal_confirmed'=>0]);

						throw new Exception("E-".$result->message, 1);

						
					}
/*					echo "otherwise here";
*/
			}
/*			echo "gotcha";
*/			DB::table('po_confirm_queue')->where('id',$_podetails->id)->update(['status'=>1]);
		} catch (Exception $e) {
			DB::table('po_confirm_queue')->where('id',$_podetails->id)->update(['status'=>3]);
			//DB::rollBack();			
			$status=0;
			$message = $e->getMessage();
		}
		} catch (Exception $e) {
			//DB::rollBack();			
			$status=0;
			$message = $e->getMessage();
		}

	
        $endTime = $this->getTime();
       // $mySample = 4;
       // return json_encode(Array(['Status'=>$status, 'Message' =>'Server: '.$message.' total process time :'.($endTime-$startTime)]));
       // echo "finaaly done";
         return json_encode(['Status'=>$status, 'Message' =>'S-: '.$message.' total process time :'.($endTime-$startTime)]);

	}



public function SyncStockOut(){
	
	$startTime = $this->getTime();
	try{
		$inputAll= $this->_request->input();
		


		$status = 0;
		$isSapEnabled = 0 ;
		//$existingQuantity =0;
		$sapProcessTime = 0;
		$message = 'Failed to do stockout/sale';
		$deliver_no = trim($this->_request->input('delivery_no'));
		$ids = trim($this->_request->input('ids'));
		$codes =  trim($this->_request->input('codes'));
		$mfgId = $this->roleAccess->getMfgIdByToken($this->_request->input('access_token'));
		$srcLocationId = trim($this->_request->input('srcLocationId'));    
		$destLocationId = trim($this->_request->input('destLocationId'));  
		$transitionTime = trim($this->_request->input('transitionTime'));
		$transitionId = trim($this->_request->input('transitionId'));
		$tpDataMapping = trim($this->_request->input('tpDataMapping'));
		$pdfContent = trim($this->_request->input('pdfContent'));
		$pdfFileName = trim($this->_request->input('pdfFileName'));
		$sapcode = trim($this->_request->input('sapcode'));
		$isSapEnabled = trim($this->_request->input('isSapEnabled'));
		$isEsealErpEnabled = trim($this->_request->input('isEsealErpEnabled'));
		$destinationLocationDetails = trim($this->_request->input('destinationLocationDetails'));
		$clocationId = $this->roleAccess->getLocIdByToken($this->_request->input('access_token'));
		$FIFOViolations = isset($inputAll['fifo_violation'])?trim($inputAll['fifo_violation']):'';
		$delivery_data = isset($inputAll['delivery_data'])?trim($inputAll['delivery_data']):'';
		$deliveryWms=0;
		$tp_aval=DB::table('syncstockout_tp')->where('tp_id',$codes)->count();
		if($tp_aval==0){
			DB::table('syncstockout_tp')->insert(['tp_id'=>$codes]);
		} else {
			throw new Exception("TP is in process, try later", 1);
		} 
		DB::beginTransaction();

		if($transitionTime > $this->getDate() || date('Y', strtotime($transitionTime)) == '2009')
			$transitionTime = $this->getDate();	

		if($FIFOViolations!=''){
			$insertArray=(array) json_decode($FIFOViolations,true);
			foreach ($insertArray as $ikey => $ival) {
				$insert=(array) $ival;
				$insert['location_id']=$clocationId;
				$insert=DB::table('fifoviolations')->insert($insert);
			}
		}

		$locationObj = new Location();
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
		$tranName = Transaction::where(['id'=>$transitionId])->value('name');
		

		//////Convert IDS into string and array
		$explodeIds = explode(',', $ids);
		$explodeIds = array_unique($explodeIds);
		
		$idCnt = count($explodeIds);
		$strCodes = '\''.implode('\',\'', $explodeIds).'\'';
		


        ////Check if this request is already processed.
		$alreadyProcessCount = DB::table($this->tpDataTable)->whereIn('level_ids',$explodeIds)->where('tp_id',$codes)->count();
		Log::info($alreadyProcessCount);
		if($alreadyProcessCount == $idCnt){
			DB::rollback();
			DB::table('syncstockout_tp')->where('tp_id',$codes)->delete();
			return json_encode(['Status'=>1,'Message'=>'Already Processed']);
		}

		Log::info(print_r($this->_request->all(),true));
		
		////Check if these ids have already some tp
		$tpCount = DB::table($esealTable.' as eseal')->join($this->trackHistoryTable.' as th', 'eseal.track_id', '=', 'th.track_id')
		->whereIn('primary_id', $explodeIds)
		->where('tp_id','!=', 0)
		->where('dest_loc_id', '>', 0)
		->select('tp_id')
		->distinct()
		->get()->toArray();

		Log::info(count($tpCount));
		if(count($tpCount)){
			throw new Exception('Some of the codes are already assigned some TPs');
		}


                ///Check if its a valid tp
		//$cnt = DB::table($esealBankTable)->where('id',$codes)->where('issue_status',1)->orWhere('download_status',1)->count();
		//echo $esealBankTable; exit;
		//DB::enableQueryLog();
		$cnt1 = DB::table($esealBankTable)->where('id', $codes)
		->where(function($query){
			$query->where('issue_status',1);
			$query->orWhere('download_status',1);
		})->count();
		/*$queries = DB::getQueryLog();
		print_r($queries);
		exit;*/
		Log::info($cnt1);
		if(!$cnt1){
			throw new Exception('Not a valid TP.');
		}         

		//Check if TP Id already Used
		$result = DB::table($esealBankTable)->where('id',$codes)->select('id', 'used_status')->get()->toArray();
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

		$transitCnt = DB::table($esealTable.' as eseal')->join($this->trackHistoryTable.' as th', 'eseal.track_id', '=', 'th.track_id')
		->whereIn('primary_id', $explodeIds)
		->select('src_loc_id','dest_loc_id')->groupBy('src_loc_id','dest_loc_id')->get()->toArray();
		Log::info($transitCnt);
		if(count($transitCnt)>1){
        
        $locationObj = new Location();
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
				'location_id' => $srcLocationId
				));		
		}catch(PDOException $e){
			throw new Exception($e->getMessage());
		}
		
		$status = 1;
		$message = 'Stock out done successfully';

/*		if(!empty($deliver_no)){
			DB::table('delivery_master')->where('document_no',$deliver_no)->update(['is_processed'=>1]);
		}
*/
		//Deleting the tp from partial_transactions table as the tp is processed completely.
		DB::table('partial_transactions')->where('tp_id',$codes)->delete();
	/*	
		if(empty($deliver_no) || $isSapEnabled ==0 || $tranName=='Sales PO'){
			DB::commit();
			goto stockout;
		}*/
		
		if($status){

			if($deliver_no && $deliveryWms){

	$deliverydetails=DB::table('delivery_master as dm')->join('delivery_details as dd','dd.ref_id','=','dm.id')->join('products as p','p.product_id','=','dd.product_id')->where('document_no',$deliver_no)->get(['dd.id as ddid','dd.ref_id as refid','dd.product_id as pid' ,'dd.line_item_no','dd.qty','dd.returnable_qty','dd.ltrs','dd.kg','dd.priority','dd.src_stor_type','dd.src_stor_sec','dd.src_bin','dd.dest_stor_sec','dd.dest_stor_type','dd.dest_bin','dd.to_no','dd.to_line_no','dd.batch_no','dm.action_code','dm.document_no','p.material_code'])->toArray();


	if(count($deliverydetails)>0){

	$confirmData=$body=[];

	/*foreach ($esealData as $key => $value) {
		foreach ($deliverydetails as $dkey => $dvalue) {
			if($dvalue->src_bin==$value->bin_location){
				$temp=[];
				$temp['item_number']=(string) $dvalue->line_item_no;
				$temp['item_quantity']=(string) number_format($value->qty,3, '.', '');
				$temp['to_no']="000000".$dvalue->to_no;
				$temp['to_line_no']="000".$dvalue->to_line_no;
				$temp['material_code']="0000000000".$value->material_code;
				$confirmData[]=$temp;
			}
		}
	}
*/	
		foreach ($deliverydetails as $dkey => $dvalue) {
			DB::enableQueryLog();
			$esealData=DB::table($esealTable.' as e')->whereIn('primary_id',$explodeIds);
			
			if($dvalue->src_bin!='')
			$esealData=$esealData->where('bin_location',$dvalue->src_bin);
			
			if($dvalue->batch_no!='')
			$esealData=$esealData->where('batch_no',$dvalue->batch_no);
			
			$esealData=$esealData->where('pid',$dvalue->pid);

			$esealData=$esealData->get([DB::raw('count(primary_id) as qty'),DB::raw('sum(pkg_qty) as pkg_qty')])->toArray();
			if(count($esealData)>0){
				$value=$esealData[0];
				if($dvalue->qty==$value->pkg_qty){
					$temp=[];
					$temp['item_number']=$dvalue->line_item_no;
					$temp['item_quantity']=$value->qty;
					$temp['to_no']=$dvalue->to_no;
					$temp['to_line_no']=$dvalue->to_line_no;
					$temp['material_code']=$dvalue->material_code;
					$confirmData[]=$temp;
				} else throw new Exception("quantity missmatch with deliery", 1);			
			} else throw new Exception("error while fetching data details againsest to delivery", 1);
		}

	$body['action_code']=(string) $deliverydetails[0]->action_code;
	$body['delivery_confirmation']=array('delivery_number'=>(string) $deliverydetails[0]->document_no,'item_info'=>$confirmData);
	$method='pickingConfirmation';
	$this->erp=new ConnectErp($mfgId);
	$result=$this->erp->request($method,'',$body,'POST');
	$result=json_decode($result);
	if($result->status!=0){
		DB::table('delivery_master')->where('document_no',$deliver_no)->update(['is_processed'=>1]);
		DB::commit();
	} else {
		throw new Exception($result->message, 1);		
	}

	}
	else {
		throw new Exception("delivery data not available", 1);
		
	}	 
			} else if($deliver_no && $deliveryWms==0){ 


				// input delivery details by line item wise
				if($delivery_data=='')				
				throw new Exception('delivery json not found');
				$deliveryJson=json_decode($delivery_data,true);

				  if(json_last_error() != JSON_ERROR_NONE)
				throw new Exception('delivery json error');
$confirmData=$body=[];

				foreach ($deliveryJson as $jkey => $jvalue) {


					$deliverydetails=DB::table('delivery_master as dm')
					->join('delivery_details as dd','dd.ref_id','=','dm.id')
					
					->join('products as p','p.product_id','=','dd.product_id')
					->where('dm.document_no',$deliver_no)
					->where('dd.id',$jvalue['id'])
					
					->get(['dd.id as ddid','dd.ref_id as refid','dd.product_id as pid' ,'dd.line_item_no','dd.qty','dd.returnable_qty','dd.ltrs','dd.kg','dd.priority','dd.src_stor_type','dd.src_stor_sec','dd.src_bin','dd.dest_stor_sec','dd.dest_stor_type','dd.dest_bin','dd.to_no','dd.to_line_no','dd.batch_no','dm.action_code','dm.document_no','p.material_code'])->toArray();
					
					if(count($deliverydetails)==0){
						throw new Exception("delivery line item not found or  material code mismatch", 1);						
					}

					$matcode=DB::table('eseal_'.$mfgId.' as e')->join('products as p','p.product_id','=','e.pid')->whereIn('e.primary_id',explode(",",trim($jvalue['iot'])))->get()->toArray();
					// print_r($matcode) ;
					
					$dvalue=$deliverydetails[0];

					$temp=[];
					$temp['item_number']=$dvalue->line_item_no;
					$temp['item_quantity']=$jvalue['qty'];
					$temp['to_no']=$dvalue->to_no;
					$temp['to_line_no']=$dvalue->to_line_no;
					$temp['material_code']=$dvalue->material_code;
					// $temp['material_code']=$matcode;
					$confirmData[]=$temp;


				}

$body['action_code']=(string) $deliverydetails[0]->action_code;
	$body['delivery_confirmation']=array('delivery_number'=>(string) $deliverydetails[0]->document_no,'item_info'=>$confirmData);

	$method='pickingConfirmation';
		/*echo "<pre>";
		echo "method";
		print_r($method);
		echo "body";
		print_r($body);*/
		// exit;
		$this->erp=new ConnectErp($mfgId);


	$result=$this->erp->request($method,'',$body,'POST');
			/*echo "result";
		print_r($result);*/
	$result=json_decode($result);

	if($result->status!=0){
		DB::table('delivery_master')->where('document_no',$deliver_no)->update(['is_processed'=>1]);
		DB::commit();
	} else {
		/*echo "Request::";
		echo "<br> Method".$method;
		echo "<pre>";
		print_r($body);
		echo "<br> body".json_encode($body);
		print_r("Result");
		print_r($result);*/

		throw new Exception("E-".$result->message, 1);		
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
				//return json_encode(Array('Status'=>$status, 'Message' =>'Server: '.$message.' eseal_time:'.($endTime - $startTime).' sap process time'.$sapProcessTime));
			DB::table('syncstockout_tp')->where('tp_id',$codes)->delete();

				if($isSapEnabled){
					return json_encode(Array('Status'=>$status, 'Message' =>'S-: '.$message.' eseal_time:'.($endTime - $startTime).' sap process time'.$sapProcessTime));
				}
				else{
				return json_encode(Array('Status'=>$status, 'Message' =>'S-: '.$message.' eseal_time:'.($endTime - $startTime)));
			   }
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
	Log::info(' === '. print_r($this->_request->all(),true));
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

	  $locationObj = new Location();
	  $mfgId = $locationObj->getMfgIdForLocationId($srcLocationId);
	  
	  $esealTable = 'eseal_'.$mfgId;

	  $transactionObj = new Transaction();
	  $transactionDetails = $transactionObj->getTransactionDetails($mfgId, $transitionId);

	  Log::info(print_r($transactionDetails, true));

	  DB::beginTransaction();

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
		$trakHistoryObj = new Trackhistory();
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
							})->pluck('primary_id')->toArray();
								
			if(!empty($res)){
				
				$attributeMaps =  DB::table('bind_history')->whereIn('eseal_id',$res)->distinct()->pluck('attribute_map_id')->toArray();

				$componentIds =  DB::table('attribute_mapping')->whereIn('attribute_map_id',$attributeMaps)->where('attribute_name','Stator')->pluck('value')->toArray();
				
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
	  DB::commit();        
			//Log::info(__LINE__);
	}catch(Exception $e){
		Log::info($e->getMessage());
		DB::rollback();        
		$message = $e->getMessage();
	}
	$endTime = $this->getTime();
	Log::info(__FUNCTION__.' Finishes execution in '.($endTime-$startTime));

	return json_encode(Array('Status'=>$status, 'Message' => $message));      
}


private function updateTrackForChilds($esealTable, $lastInrtId, $explodedIds, $maxLevelId){
	try{
		//DB::table($esealTable)
		//	->whereIn('parent_id', $explodedIds)
		//	->orWhereIn('primary_id', $explodedIds)
		//	->update(Array('track_id' => $lastInrtId));
		//print_r($explodedIds);exit;
		DB::statement('update '.$esealTable.' set track_id='.$lastInrtId.' where parent_id in ('.implode(',',$explodedIds).') 
		              or primary_id in ('.implode(',',$explodedIds).')');

		//$res = DB::table($esealTable)
		//	->whereIn('parent_id', $explodedIds)
		//	->orWhereIn('primary_id', $explodedIds)
		//	->select(DB::raw('primary_id'))->get()->toArray();

        $res =  DB::select('select primary_id from '.$esealTable.' where primary_id in ('.implode(',',$explodedIds).') or parent_id in ('.implode(',',$explodedIds).')');
			
		if($maxLevelId>0 && count($res)>0){
			$explodedIds1 = Array();
			foreach($res as $val){
				array_push($explodedIds1, $val->primary_id);
			}
			$explodedIds1 = array_diff($explodedIds1, $explodedIds);
			
			//$maxLevelId = 	DB::table($esealTable)
			//	->whereIn('parent_id', $explodedIds1)
			//	->orWhereIn('primary_id', $explodedIds1)->max('level_id');
             Log::info('unprepeared statement');
            $maxLevelId =  DB::select('select max(level_id) as level from '.$esealTable.' where primary_id in ('.implode(',',$explodedIds1).') or parent_id in ('.implode(',',$explodedIds1).')');
            Log::info('level');
            Log::info($maxLevelId);
            $maxLevelId = $maxLevelId[0]->level;

            //throw new Exception('xxxxxx');

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
		  DB::table($this->tpDataTable)->insert(Array('tp_id'=>$codes, 'level_ids'=>$id));
		}
		if(!empty($pdfContent)){
		  DB::table($this->tpPDFTable)->insert(Array('tp_id'=>$codes, 'pdf_content'=>$pdfContent,'pdf_file'=>$pdfFileName));
		}
		
		
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
	public function deliveryCancel_Confirmation(){
		//PGICAN
		try{
			 DB::beginTransaction();
		$message='Delivery cancelled';
		$status=1;
        $input = $this->_request->all();
        $deliver_no=trim($input['deliver_no']);
        $mfgId = $this->roleAccess->getMfgIdByToken($this->_request->input('access_token'));

        if($deliver_no=='')
        	throw new Exception("Delivery No not found", 1);
        
        $tpa=DB::table('tp_attributes as tpa')->where('tpa.value','=',$deliver_no)->get()->toArray();
        if(count($tpa)==0)
        	throw new Exception("Delivery Not Processed", 1);
        else 
        	$tpa=$tpa[0];
        if($tpa->reference_value!='')
        	throw new Exception("Delivery received", 1);
		$tp=$tpa->tp_id;
		$tp_info=DB::table('track_history')->where('tp_id',$tp)->get()->toArray();
		if(count($tp_info)>1)
			throw new Exception("tomany tracks exist agains to this TP", 1);
		if(count($tp_info)==0)
			throw new Exception("no tracks found agains to this tp", 1);
		else $tp_info=$tp_info[0];

		$transitionId=DB::table('transaction_master')->where('name','=','Delivery Cancel')->value('id');

	
		$tpdata=DB::table('tp_data')->where('tp_id',$tp)->pluck('level_ids')->toArray();
		
		if(!count($tpdata))
			throw new Exception("tp Data Not found", 1);
		
		$th_data=[];
		$th_data['src_loc_id']=$tp_info->src_loc_id;
		$th_data['dest_loc_id']=0;
		$th_data['transition_id']=$transitionId;
		$th_data['tp_id']=$tp;
		$th_data['pallate_id']=0;
		$lastInrtId=DB::table('track_history')->insertGetId($th_data);
		DB::table('eseal_'.$mfgId)
					->whereIn('primary_id', $tpdata)
					->orWhereIn('parent_id', $tpdata)
					->update(array('track_id'=>$lastInrtId));
		$sql = '
					INSERT INTO 
						track_details (code, track_id) 
					SELECT 
						primary_id, '.$lastInrtId.' 
					FROM 
						eseal_'.$mfgId.' 
					WHERE 
						track_id = '.$lastInrtId;
				DB::insert($sql);
		
		$confirmData=$body=[];
		$deliverydetails=DB::table('delivery_master as dm')->join('delivery_details as dd','dd.ref_id','=','dm.id')->join('products as p','p.product_id','=','dd.product_id')->where('dm.document_no',$deliver_no)->where('dm.action_code','>=',5)->get(['dd.id as ddid','dd.ref_id as refid','dd.product_id as pid' ,'dd.line_item_no','dd.qty','dd.returnable_qty','dd.ltrs','dd.kg','dd.priority','dd.src_stor_type','dd.src_stor_sec','dd.src_bin','dd.dest_stor_sec','dd.dest_stor_type','dd.dest_bin','dd.to_no','dd.to_line_no','dd.batch_no','dm.action_code','dm.document_no','p.material_code'])->toArray();
		foreach ($deliverydetails as $key => $dvalue) {
			$temp=[];
			$temp['item_number']=$dvalue->line_item_no;
			$temp['item_quantity']=$dvalue->qty;
			$temp['to_no']=$dvalue->to_no;
			$temp['to_line_no']=$dvalue->to_line_no;
			$temp['material_code']=$dvalue->material_code;
			$confirmData[]=$temp;
		}
		$body['action_code']=(string) $deliverydetails[0]->action_code;
		$body['delivery_confirmation']=array('delivery_number'=>(string) $deliverydetails[0]->document_no,'item_info'=>$confirmData);
		$method='pickingConfirmation';

			$this->erp=new ConnectErp($mfgId);
		$result=$this->erp->request($method,'',$body,'POST');
		$result=json_decode($result);

		if($result->status!=0){		
			DB::table('delivery_master')->where('document_no',$deliver_no)->update(['is_processed'=>1]);
		} else {
			throw new Exception("E-".$result->message, 1);		
		}
		print_r($body);
		print_r($result);
		DB::commit();
	}catch(Exception $e){
		DB::rollback();
		$message=$e->getMessage();
		$status=0;
/*		$status = FALSE;
*/	}
	    $result=json_encode(['Status'=>$status,'Message'=>'S-:' .$message]);
      	$this->erp=new ConnectErp(6);
      	$this->erp->captureReqLog('deliveryCancel_Confirmation',json_encode($input),$result);
   		return $result;
	}

  	public function createDeliveryOrders(){
        $input = $this->_request->all();
        $status=0;
        $scStatus=0;
        $deliveryId=0;
        $cencelDelivery=0;
        $message='Something Went wrong,Please check logs for more info';
      
        try{
          DB::beginTransaction();
          if(!isset($input['sto_no']))
            throw new Exception("sto_no should not be empty", 1);
          if(!isset($input['document_no']))
            throw new Exception("document_no should not be empty", 1);
//           $sto=DB::table('delivery_master')->where('sto_no',$input['sto_no'])->value('id');
           $sto=DB::table('delivery_master')->where('sto_no',$input['sto_no'])->where('is_sto',1)->get()->toArray();
           if(count($sto)<=0)
            throw new Exception("sto not exists in Eseal", 1);
            else
              $sto=$sto[0];
          // if(!isset($input['shipment_no']))
          //   throw new Exception("shipment_no missing", 1); 
          if(!isset($input['delivery_shipment_flag']))
            throw new Exception("delivery_shipment_flag missing", 1);
          if(!isset($input['items']))
            throw new Exception("items missing", 1);
          $items=$input['items'];
          if(count($items)<=0)
            throw new Exception("itemms data missing", 1);

           $deliveryId=DB::table('delivery_master')->where('action_code','<=',4)->where('document_no',$input['document_no'])->value('id');
           if($deliveryId){
           		if($input['action_code']<=4)
           			throw new Exception("delivery already exist, Plese consult team for further", 1);
           		else {
           			 $deliveryId=DB::table('delivery_master')->where('action_code','>',4)->where('document_no',$input['document_no'])->value('id');
           			 if($deliveryId){
           				throw new Exception("cencelDelivery already exist, Plese consult team for further", 1);
           			 }else{
						DB::table('delivery_master')->where('id',$deliveryId)->update(['is_processed'=>2]);
						$deliveryId=0;
					}
           		}
           }

          if(!$deliveryId)
          {
              $deliveryData['document_no']=$input['document_no'];
              $deliveryData['action_code']=$input['action_code'];
              $deliveryData['sto_no']=$input['sto_no'];
              $deliveryData['shipment_no']=$input['shipment_no'];
              $deliveryData['frm_location']=$sto->frm_location;
              $deliveryData['to_location']=$sto->to_location;
              $deliveryData['receving_location']=$sto->receving_location;
              $deliveryData['manufacturer_id']=$sto->manufacturer_id;
              $deliveryData['user_id']=$sto->user_id;
              $deliveryData['is_sto']=0;
              $deliveryId=DB::table("delivery_master")->insertGetId($deliveryData);
            }
           
          foreach ($items as $key => $item) {
            $product_id=DB::table('products')->where('material_code',$item['material_code'])->value('product_id');
            if($product_id==0 || $product_id==''){
            	throw new Exception("Product not found, Please check with team", 1);
            }
            $dd=[];
            $dd['src_stor_type']=$item['src_stor_type'];
            $dd['src_stor_sec']=$item['src_stor_sec'];
            $dd['src_bin']=$item['src_bin'];
            $dd['dest_stor_sec']=$item['dest_stor_sec'];
            $dd['dest_stor_type']=$item['dest_stor_type'];
            $dd['dest_bin']=$item['dest_bin'];
            $dd['batch_no']=$item['batch_no'];
            $dd['ref_id']=$deliveryId;
            $dd['product_id']=$product_id;
            $dd['qty']=$item['qty'];
            $dd['line_item_no']=$item['line_item'];
            $dd['to_no']=$item['to_no'];
            $dd['to_line_no']=$item['to_line_no'];
            $delivery_details_in=DB::table("delivery_details")->insertGetId($dd);
            if(!$delivery_details_in)
              $scStatus=1;
          }

          if(!$scStatus){
            $update=['shipment_no'=>$input['shipment_no'],'delivery_shipment_flag'=>$input['delivery_shipment_flag'],'delivery_info'=>$input['delivery_info']];
              $stoupdate=DB::table('delivery_master')->where('id',$sto->id)->update($update);
            $status=1;
            $message='Delivery Created Sucessfully';
            DB::commit();
          } else {
            throw new Exception("error in inserting deliveries", 1);
          }

        } catch(Exception $e){
          DB::rollBack();
          $status=0;
          $message = $e->getMessage();
    } 

      $result=json_encode(['Status'=>$status,'Message'=>'S-:' .$message]);
      $this->erp=new ConnectErp(6);
      $this->erp->captureReqLog('createDeliveryOrders',json_encode($input),$result);
    return $result;
  }


public function ReceiveByTp(){
	$startTime = $this->getTime();
	try{
		$status = 0;
		$message = '';
		$data_request=$this->_request->all();
		$tp = $this->_request->input('tp');
		$locationId = $this->_request->input('location_id');		
		$transitionTime = $this->getDate();
		$transitionId = $this->_request->input('transition_id');
		$previousTrackId = '';
		$tpArr = explode(',', $tp);
		$missingIds = $this->_request->input('missing_ids');
		$transitIds = $this->_request->input('damage_ids');
		$excess_ids = $this->_request->input('excess_ids');
   		$deliveryNo = $this->_request->input('delivery_no');
   		$materialBatches = $this->_request->input('materialBatches');
   		$store_location = trim($this->_request->input('store_location'));
		$documentNo = array();

		$deliveryNoExists = FALSE;
		$purchaseNoExists = FALSE;
		$subcontractNoExists = FALSE;
		$esealdocNoExists = FALSE;
		$parkGRN = FALSE;
		$xml = Array();
		$isPostGrn=false;
		$locationObj = new Location();
		$mfgId = $locationObj->getMfgIdForLocationId($locationId);
		Log::info('recive by recive by recive by');
		Log::info(__FUNCTION__.'==>'.print_r($this->_request->all(),true));
		$movementType=0;
		///GET MfgId for geiven Location

		DB::beginTransaction();
		////SAP Delivery No associated with TP
		if(!empty($deliveryNo)){
			$delivery_attribute_id= DB::table($this->attributeTable)->where('attribute_code','document_no')->value('attribute_id');
			$purchase_attribute_id= DB::table($this->attributeTable)->where('attribute_code','purchase_no')->value('attribute_id');
			$esealdoc_attribute_id= DB::table($this->attributeTable)->where('attribute_code','eseal_document_no')->value('attribute_id');
			$park_grn_id = DB::table($this->attributeTable)->where('attribute_code','park_grn_no')->value('attribute_id');
			$parkGrnExists = DB::table('tp_attributes')->whereIn('tp_id', $tpArr)->where(['attribute_id'=>$park_grn_id])->value('value');
			$deliveryNoCnt = DB::table('tp_attributes')->whereIn('tp_id', $tpArr)->where(['attribute_id'=>$delivery_attribute_id,'value'=>$deliveryNo])->count();
			if(!$deliveryNoCnt){
				$purchaseNoCnt = DB::table('tp_attributes')->whereIn('tp_id', $tpArr)->where(['attribute_id'=>$purchase_attribute_id,'value'=>$deliveryNo])->count();
				  if(!$purchaseNoCnt){
				  	$esealdocNoCnt = DB::table('tp_attributes')->whereIn('tp_id', $tpArr)->where(['attribute_id'=>$esealdoc_attribute_id,'value'=>$deliveryNo])->count();
				      if(!$esealdocNoCnt){
				          throw new Exception('Given delivery no not exists for passed tp id');
				      }
				      else{
				      	$esealdocNoExists = TRUE;
				      }
				  }
				  else{
				  	$purchaseNoExists = TRUE;				  	
				  	if($deliveryNoCnt){
				  		throw new Exception("For the Delivery not possible for Park and Post GRN");
				  	}
				  	$POGRN=DB::table('transaction_master')->where('manufacturer_id',$mfgId)->where('id',$transitionId)->value('action_code');				  
				  	//if($parkGrnExists != 'unknown' && $POGRN=='POGRN'){
				  	if($POGRN=='POGRN'){
						$movementType = 105;
						$isPostGrn=true;
						$checkTP = DB::table('tp_attributes')->where('tp_id',$tp)->where('attribute_id',$park_grn_id)->value('tp_id');
						if($checkTP){
							$value = DB::table('tp_attributes')->where('tp_id',$tp)->where('attribute_id',$park_grn_id)->where('value','<>','unknown')->count();
							if($value==0)
							throw new Exception('Please do park GRN against TP');
						}

				  	}  else if($POGRN=='PGRN'){
						$movementType = 103;
						$checkTP = DB::table('tp_attributes')->where('tp_id',$tp)->where('attribute_id',$park_grn_id)->value('tp_id');
						if($checkTP){
							$value = DB::table('tp_attributes')->where('tp_id',$tp)->where('attribute_id',$park_grn_id)->where('value','<>','unknown')->count();
							if($value>0)
							throw new Exception('Park GRN completed against TP');
						}
						$isPostGrn=false;
				  	} else if($POGRN=='GRN') {
				  		$movementType = 101;
				  		$isPostGrn=false;
				  	}else {
				  		 throw new Exception('tail mismatch error');
				  	}
				  }
			}else{
				$deliveryNoExists = TRUE;
			}
		}
/*
		echo "Test";
		echo "<br>movementType:".$movementType;
		echo "<br>parkGrnExists:".$parkGrnExists;
		echo "<br>esealdocNoExists:".$esealdocNoExists;
		echo "<br>deliveryNoExists:".$deliveryNoExists;
exit;
	*/

		if(!$mfgId)
			throw new Exception('In-valid location');

			$esealTable = 'eseal_'.$mfgId;
			$transactionObj = new Transaction();
			$transactionDetails = $transactionObj->getTransactionDetails($mfgId, $transitionId);
			if(!count($transactionDetails)){
				throw new Exception('Transition details not found');
			}
			Log::info(print_r($transactionDetails, true));

			$srcLocationAction = $transactionDetails[0]->srcLoc_action;
			$destLocationAction = $transactionDetails[0]->dstLoc_action;
			$inTransitAction = $transactionDetails[0]->intrn_action;
			
			if(!($srcLocationAction==0 && $destLocationAction==1 && $inTransitAction==-1) && !($srcLocationAction==-1 && $destLocationAction==0 && $inTransitAction==1))
			 		throw new Exception('The given transition ID is not allowed');

				$tpTrackIDs = Array();
				//echo $this->trackHistoryTable; exit;
				foreach($tpArr as $tp){
					try{
						$res = DB::table($this->trackHistoryTable)->where('tp_id', $tp)->orderBy('update_time','desc')->take(1)->get()->toArray();
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
				log::info($tpArr);
                 log::info("tptrackids");
				Log::info($tpTrackIDs[$tp]);
				try{
					log::info("first_try");
					foreach($tpArr as $tp){
						log::info("inner foreach first");
						$destLocationId =0;
						$tpDetails = DB::table($this->trackHistoryTable)->where('tp_id', $tp)->get(['src_loc_id','dest_loc_id']);
						log::info($tpDetails);
						$srcLocationId = $tpDetails[0]->src_loc_id;
						//dd($srcLocationId);
						log::info($srcLocationId);
						// if(($srcLocationAction==0 && $destLocationAction==1 && $inTransitAction==-1) && $purchaseNoExists && $movementType == 105){
						// 	throw new Exception('Please proceed for Park GRN');
					 //    }
					    log::info("parkgrnends");

                         log::info("movementtype103/105_starts");

						if(($srcLocationAction==0 && $destLocationAction==1 && $inTransitAction==-1) && $purchaseNoExists){
							//echo "Test movementtype103_starts"; exit;							
							if($isPostGrn){ 
							$movementType = 105;
							$destLocationId=0;
							$srcLocationId = $tpDetails[0]->dest_loc_id;
                            }
							else { 
							$parkGRN = true; 
							$movementType = 103; 
						    $locationId = $tpDetails[0]->src_loc_id;
						    $destLocationId = $tpDetails[0]->dest_loc_id;
						  }
						}
				

						$lastInsertId = DB::table($this->trackHistoryTable)->insertGetId(Array(
							'src_loc_id'=>$locationId,
							'dest_loc_id'=> $destLocationId,
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
						$transitionId = DB::table('transaction_master')->where(['manufacturer_id'=>$mfgId,'name'=>'Damaged'])->value('id');
						if(!$transitionId)
							throw new Exception('Transaction : Damage not created');

						$request = Request::create('scoapi/UpdateTracking', 'POST', array('module_id'=>$this->_request->input('module_id'),'access_token'=>$this->_request->input('access_token'),'codes'=>$transitIds,'srcLocationId'=>$locationId,'destLocationId'=>0,'transitionTime'=>$transitionTime,'transitionId'=>$transitionId,'internalTransfer'=>0));
						$originalInput = Request::input();//backup original input
						Request::replace($request->input());						
						$response = Route::dispatch($request)->getContent();
						$response = json_decode($response,true);
						if($response['Status'] == 0)
							throw new Exception($response['Message']);

					}
					if(!empty($missingIds)){
						Log::info('Execution in MISSING:');
						$transitionId = DB::table('transaction_master')->where(['manufacturer_id'=>$mfgId,'name'=>'Missing'])->value('id');
						if(!$transitionId)
							throw new Exception('Transaction : Missing not created');


						$request = Request::create('scoapi/UpdateTracking', 'POST', array('module_id'=>$this->_request->input('module_id'),'access_token'=>$this->_request->input('access_token'),'codes'=>$missingIds,'srcLocationId'=>$locationId,'destLocationId'=>0,'transitionTime'=>$transitionTime,'transitionId'=>$transitionId,'internalTransfer'=>0));
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

                    $vehicleId = DB::table($this->attributeTable)->where('attribute_code','vehicle_no')->value('attribute_id');
                    $invoiceId = DB::table($this->attributeTable)->where('attribute_code','docket_no')->value('attribute_id');

                    $XML_DYNAMIC ='';

				    $vehicleNo = DB::table('tp_attributes')->whereIn('tp_id', $tpArr)->where('attribute_id',$vehicleId)->value('value');

                    $invoiceNo = DB::table('tp_attributes')->whereIn('tp_id', $tpArr)->where('attribute_id',$invoiceId)->value('value');

					if(empty($vehicleNo))
						$vehicleNo ='';
					if(empty($invoiceNo))
						$invoiceNo ='';
						
                    $XML_DYNAMIC  =  $vehicleNo.','.$invoiceNo;

				if($deliveryNoExists){

					$data=DB::table($esealTable.' as e')->join('products as p','p.product_id','=','e.pid')->where('track_id', $lastInsertId)->groupBy('e.batch_no')->groupBy('e.pid')->get([DB::raw('count(eseal_id) as cnt'),DB::raw('sum(pkg_qty) as qty'),'pid','batch_no','p.material_code']);

					$eccItemData=[];
	$sto=DB::table('delivery_master')->where('document_no',$deliveryNo)->value('sto_no');
					foreach ($data as $key => $value) {
						
						$temp=[];
						$temp['material']=$value->material_code;
						$temp['batch']=$value->batch_no;
						// $temp['batch']='01C4054000';
						$temp['quantity']=$value->qty;
						// $temp['quantity']=1080;
						$temp['stock_type']='';
						$temp['date_of_mfg']='';	
						$temp['sku_code']='';
						$temp['price_lot']='';	
						$eccItemData[]=$temp;
					}
	
	$body=array('headerData'=>array('obd_number'=>$deliveryNo,'po_sto_number'=>$sto),'itemData'=>$eccItemData);


	$method='grProcess';
	$this->erp=new ConnectErp($mfgId);
	$result=$this->erp->request($method,'',$body,'POST');
	$result=json_decode($result);
// print_r($result);exit;
		if($result->status!=0){
			DB::table('tp_attributes')->where('value',$deliveryNo)->where('tp_id',$tp)->where('attribute_name','Document Number')->update(['reference_value'=>$result->data->material_doc]);
			DB::commit();
		} else {
				echo "<pre>";
	print_r($body);
	print_r($result);
//	exit;
			throw new Exception('E-'.$result->message, 1);		
		}
	}

	DB::commit();			
		
	}catch(Exception $e){
		$status =0;
		DB::rollback();
		Log::info($e->getMessage());
		$message = $e->getMessage();

	}
	$endTime = $this->getTime();
	Log::info(__FUNCTION__.' Finishes execution in '.($endTime-$startTime));
	Log::info(Array('Status'=>$status, 'Message'=>$message, 'documentNo' =>$documentNo));
	return json_encode(Array('Status'=>$status, 'Message' =>'S-: '.$message, 'documentNo' =>$documentNo));
}


public function putawayCreation(){
	$input = $this->_request->all();
	$status=0;
	$message="putawayCreation Failed";
	$insert=[];
	foreach ($input['items'] as $key => $value) {
		$value['to_no']=$input['to_no'];
		$value['warehouse_no']=$input['warehouse_no'];
		$value['document_no']=$input['document_no'];
		$value['product_id']=DB::table('products as p')->where('p.material_code',$value['material_code'])->value('product_id');
		$value['status']=0;
		$insert[]=$value;
	}
	$insertStatus=DB::table('putaway_queue')->insert($insert);
	if($insertStatus){
		$status=1;
		$message="putawayCreation done succesfully";
	}
	return json_encode(Array('Status'=>$status, 'Message' =>'S-: '.$message));
}



	public function putawayConfirmation_old(){
		$input = $this->_request->all();
		$mfgId = $this->roleAccess->getMfgIdByToken($this->_request->input('access_token'));
		$status=0;
		$message="putawayCreation Failed";
		$insert=[];
		try{

			$queue=DB::table('putaway_queue')->where('document_no','=',$input['document_no'])->get()->toArray();
			if(count($queue)<=0)
				throw new Exception(" Document no not found in database", 1);
			$baseQ=$queue[0];
			$itemData=[];
			$headerData=['gr_doc_number'=>(string) $baseQ->document_no,'to_number'=>(string) $baseQ->to_no,'warehouse_number'=>(string) $baseQ->warehouse_no];
			foreach ($queue as $key => $value) {
				$temp=[];
				$temp['to_line_item']=(string) $value->line_item;
				$temp['material_code']=(string) $value->material_code;
				$temp['batch']=(string) $value->batch;
				$temp['quantity']=(string) $value->qty;
				$temp['source_storage_type']=(string) $value->src_stor_type;
				$temp['source_storage_section']=(string) $value->src_stor_sec;
				$temp['source_bin']=(string) $value->src_bin;
				$temp['destination_storage_type']=(string) $value->dest_stor_type;
				$temp['destination_storage_section']=(string) $value->dest_stor_sec;
				$temp['destination_bin']=(string) $value->dest_bin;
				$itemData[]=$temp;
			}
			$body=['headerData'=>$headerData,'itemData'=>$itemData];

/*			echo "<pre>";
			print_r(json_encode($body));
			exit;

*/			$method='putAwayConfirmation';
			$this->erp=new ConnectErp($mfgId);
			$result=$this->erp->request($method,'',$body,'POST');
			$result=json_decode($result);

			if($result->status){
				DB::table('putaway_queue')->update(['status'=>$result->status]);
				$status=1;
				$message='Putaway Confirmation Done';
			} else {
				throw new Exception($result->message, 1);				
			}

		} catch(Exception $e)
		{
			return json_encode(['Status'=>0,'Message'=> $e->getMessage()]);
			//DB::rollback();	
		}   	
		return json_encode(Array('Status'=>$status, 'Message' =>'S-: '.$message));
	}

	public function putawayConfirmation(){
		$input = $this->_request->all();
		//print_r($input);exit;
		$mfgId = $this->roleAccess->getMfgIdByToken($this->_request->input('access_token'));
		$status=0;
		$message="putawayCreation Failed";
		$insert=[];
		try{

			/*$queue=DB::table('putaway_queue')->where('document_no','=',$input['document_no'])->get()->toArray();
			if(count($queue)<=0)
				throw new Exception(" Document no not found in database", 1);
			$baseQ=$queue[0];
			$itemData=[];*/
			$headerData=['gr_doc_number'=>(string)$this->_request->input('gr_doc_number'),'to_number'=>(string)$this->_request->input('to_no'),'warehouse_number'=>(string)$this->_request->input('warehouse_no')];
			$itemData = json_decode($this->_request->input('itemData'));
			/*foreach ($queue as $key => $value) {
				$temp=[];
				$temp['to_line_item']=(string) $value->line_item;
				$temp['material_code']=(string) $value->material_code;
				$temp['batch']=(string) $value->batch;
				$temp['quantity']=(string) $value->qty;
				$temp['source_storage_type']=(string) $value->src_stor_type;
				$temp['source_storage_section']=(string) $value->src_stor_sec;
				$temp['source_bin']=(string) $value->src_bin;
				$temp['destination_storage_type']=(string) $value->dest_stor_type;
				$temp['destination_storage_section']=(string) $value->dest_stor_sec;
				$temp['destination_bin']=(string) $value->dest_bin;
				$itemData[]=$temp;
			}*/
			$body=['headerData'=>$headerData,'itemData'=>$itemData];
			//return json_encode(Array('Status'=>$status, 'Message' =>'Server: Successfull', 'Data' => $body));
/*			echo "<pre>";
			print_r(json_encode($body));
			exit;

*/			$method='putAwayConfirmation';
			$this->erp=new ConnectErp($mfgId);
			$result=$this->erp->request($method,'',$body,'POST');
			$result=json_decode($result);

			if($result->status){
				DB::table('putaway_queue')->where('document_no','=',$this->_request->input('gr_doc_number'))->update(['status'=>$result->status]);
				$status=1;
				$message='Putaway Confirmation Done';
			} else {
				throw new Exception($result->message, 1);				
			}

		} catch(Exception $e)
		{
			return json_encode(['Status'=>0,'Message'=> $e->getMessage()]);
			//DB::rollback();	
		}   	
		return json_encode(Array('Status'=>$status, 'Message' =>'S-: '.$message));
	}

	public function apiTest(){	
		return json_encode(['Status'=>1,'Message'=>'Call Successfull.']);
	} 	

	public function movePallet()
	{
		//$pallet_id = $this->_request->input('pallet_id');
		$placed_location = $this->_request->input('placed_location');
		//$allocated_status = $this->_request->input('status');
		$allocated_date = $this->_request->input('allocated_date');
		$pallet_id = $this->_request->input('pallet_id');
		$location_id = $this->roleAccess->getLocIdByToken($this->_request->input('access_token'));
		$mfg_id = $this->roleAccess->getMfgIdByToken($this->_request->input('access_token'));
		$transitionId = $this->_request->input('transitionId');
		$delivery = $this->_request->input('delivery');
		$grn_document = $this->_request->input('grn_document');
		if($grn_document)
		$warehouseEnabled=0;
		$warehouse_id = DB::table('wms_entities')->where(array('location_id'=>intval($location_id), 'entity_type_id'=>6001))->value('id');
		if($warehouse_id)
		$warehouseEnabled=1;
		$status='';
		$message='';
		try
		{
			Log::info(__FUNCTION__.' : '.print_r($this->_request->all(),true));
			if($warehouseEnabled){
				$pallet_weight = DB::Table('eseal_'.$mfg_id)->select(DB::raw('sum(pkg_qty) as pkg_qty, bin_location'))->where(array('bin_location'=>$placed_location,'ware_id'=>$warehouse_id))->get()->toArray();
				$bin_capacity = DB::table('wms_entities')->where(array('org_id'=>$mfg_id, 'ware_id'=>$warehouse_id,'entity_location'=>$placed_location))->value('capacity');
				$pallet_exists= DB::Table('eseal_'.$mfg_id)->select('eseal_'.$mfg_id.'.eseal_id')->where(array('bin_location'=>$placed_location,'parent_id'=>$pallet_id,'ware_id'=>$warehouse_id))->get()->toArray();
				
			}
				if($bin_capacity>=$pallet_weight[0]->pkg_qty || $warehouseEnabled==0)
				{
					//return $transitionId;
					DB::beginTransaction();
					$req = Request::create('scoapi/UpdateTracking', 'POST',array('module_id'=>$this->_request->input('module_id'),'access_token'=>$this->_request->input('access_token'),'codes'=>$pallet_id,'srcLocationId'=>$location_id,'destLocationId'=>0,'transitionTime'=>$allocated_date,'transitionId'=>$this->_request->input('transitionId'),'internalTransfer'=>0));
					$originalInput=$this->_request->all();
					$this->_request->replace($req->all());
					$res = app()->handle($req);
					$response = $res->getContent();
					$response = json_decode($response,true);

					if($response['Status']==1)
					{
						$check_pallet = DB::table('eseal_'.$mfg_id)->where(array('primary_id'=>$pallet_id))->value('eseal_id');
						//$queries = DB::getQueryLog();
						//return end($queries);
						//return $check_pallet;
						if(!empty($check_pallet))
						{
							try
							{    
								$movedPallet = DB::Table('eseal_'.$mfg_id)
									   ->where(array('parent_id'=>$pallet_id))
									   ->orWhere(array('primary_id'=>$pallet_id))
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
		}
		catch(Exception $e)
		{
			return json_encode(['Status'=>0,'Message'=> $e->getMessage()]);
			DB::rollback();	
		}       	
		return json_encode(['Status'=>$status,'Message'=>$message]);
	}	

// <?php 

public function getAdjacentInfo($data)
	{		
		try
		{	
			$status =0;
			$eseal_id = $data['eseal_id'];
			$manufacturer_id= $this->roleAccess->getMfgIdByToken($data['access_token']);
			$wmsenabled =  DB::table('wms_entities')->where('org_id',$manufacturer_id)->value('id');
			$esealCollection=  DB::table('eseal_'.$manufacturer_id)->where('primary_id',$data['eseal_id'])->get()->toArray();
			//print_r($esealCollection);exit;
			if(!empty($esealCollection[0]))
			{
				if($esealCollection[0]->level_id == 0)
				{
					//$main = "Level Info : \r\n\r\n";
					$main = "";
					$level1_id = $esealCollection[0]->parent_id;
					$level =  DB::table('eseal_'.$manufacturer_id)->where('primary_id',$level1_id)->value('level_id');
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
						$level1_Collection =DB::table('eseal_'.$manufacturer_id)->where('primary_id',$level1_id)->get()->toArray();
						//print_r($level1_Collection);exit;
						$level2_id = $level1_Collection[0]->parent_id;
						if($level2_id != 0)
						{
							if(!$wmsenabled)
								$main .= "Level2: ". $level2_id."\r\n";  
							$tp = DB::table('tp_data')->where('level_ids',$level2_id)->get()->toArray();
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

							$tp = DB::table('tp_data')->where('level_ids',$level1_id)->get()->toArray();
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
						$tp = DB::table('tp_data')->where('level_ids',$eseal_id)->orderBy('id','desc')->get()->toArray();						
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
					$manufacturer_name = DB::table('eseal_customer')->where('customer_id',$productCollection[0]->manufacturer_id)->value('brand_name');
					$attributeCollection = DB::table('attribute_mapping')->where('attribute_map_id',$attribute_map_id)->get()->toArray();   
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
						$tp = DB::table('tp_data')->where('level_ids',$level2_id)->get()->toArray();
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
						$tp = DB::table('tp_data')->where('level_ids',$eseal_id)->get()->toArray();
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

							$manufacturer_name = DB::table('eseal_customer')->where('customer_id',$productCollection[0]->manufacturer_id)->value('brand_name');
							$attributeCollection = DB::table('attribute_mapping')->where('attribute_map_id',$res->attribute_map_id)->get()->toArray();
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

						$manufacturer_name = DB::table('eseal_customer')->where('customer_id',$productCollection[0]->manufacturer_id)->value('brand_name');
						$attributeCollection = DB::table('attribute_mapping')->where('attribute_map_id',$attribute_map_id)->get()->toArray();   

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
					   
					   $tp = DB::table('tp_data')->where('level_ids',$level2_id)->get()->toArray();
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
						$tp = DB::table('tp_data')->where('level_ids',$eseal_id)->get()->toArray();
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

							$manufacturer_name = DB::table('eseal_customer')->where('customer_id',$productCollection[0]->manufacturer_id)->value('brand_name');
							$attributeCollection = DB::table('attribute_mapping')->where('attribute_map_id',$res->attribute_map_id)->get()->toArray();   

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

						$manufacturer_name = DB::table('eseal_customer')->where('customer_id',$manufacturer_id)->value('brand_name');
						$attributeCollection = DB::table('attribute_mapping')->where('attribute_map_id',$attribute_map_id)->get()->toArray();   

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
							$name =  Products::where('product_id',$child->pid)->value('name');
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
					$tp = DB::table('tp_data')->where('level_ids',$eseal_id)->get()->toArray();
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

							$manufacturer_name = DB::table('eseal_customer')->where('customer_id',$productCollection[0]->manufacturer_id)->value('brand_name');
							$attributeCollection = DB::table('attribute_mapping')->where('attribute_map_id',$res->attribute_map_id)->get()->toArray();   

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

						$manufacturer_name = DB::table('eseal_customer')->where('customer_id',$productCollection[0]->manufacturer_id)->value('brand_name');
						$attributeCollection = DB::table('attribute_mapping')->where('attribute_map_id',$attribute_map_id)->get()->toArray();   

						foreach($attributeCollection as $attribute)
						{
							$main .= $attribute->attribute_name." : ".$attribute->value."\r\n";
						}
					}
				}
				$main .= "Trace Info : \r\n\r\n";

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
				$status =1;
				return json_encode(['Status'=>$status,'Message'=>"\r\n".$main."\r\n",'Product Name'=>$productCollection[0]->name,'Manufacturer Name'=>$manufacturer_name,'Image'=>$productCollection[0]->image]);
			}
			$tpCollection = DB::table('tp_data')->where('tp_id',$eseal_id)->get(['level_ids']);			
			if(!empty($tpCollection[0]) ) 
			{
				$main = "Transit Info : \r\n\r\n";
				//$tpattr = DB::table('tp_attributes')->where('tp_id',$eseal_id)->get()->toArray();
				$tpdetails =DB::select('select * from track_history th join track_details td on th.track_id=td.track_id join transaction_master tr on tr.id=th.transition_id where td.code='.$eseal_id.' order by th.update_time limit 1');
				
				$src_name =DB::table('locations')->where('location_id',$tpdetails[0]->src_loc_id)->value('location_name');
				$dest_name = DB::table('locations')->where('location_id',$tpdetails[0]->dest_loc_id)->value('location_name');
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
				//$mfg_id =Location::where('location_id',$attr->location_id)->value('manufacturer_id');
				//$mfg_id = $this->roleAccess->getMfgIdByToken($data['access_token']);
				//return $mfg_id;
				$manufacturer_name = DB::table('eseal_customer')->where('customer_id',$manufacturer_id)->value('brand_name');
				 $levelIds = DB::table('tp_data')->where('tp_id',$eseal_id)->value('level_ids');
				 $ids1 = implode(',',$levelIds);
				 // echo '<pre>'; print_r($ids1); exit;

				// $cc= implode(',', array_map('intval', $levelIds));
				  //echo '<pre>'; print_r($cc); exit;
				 $result =DB::select('select distinct(level_id)  from eseal_'.$manufacturer_id.' where primary_id IN (' . $ids1. ')');				
				 if($result[0]->level_id == 2)
				 {
					$level1Ids=DB::table('eseal_'.$manufacturer_id)->whereIn('parent_id',$levelIds)->value('primary_id');
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
			$final = array();
			$message ='Data Retrieved Successfully';
			$manufacturer_id= $this->roleAccess->getMfgIdByToken($data['access_token']);

                        $user_id = $this->roleAccess->checkAccessToken($data['access_token'])[0]->user_id;
                        $role_id = $this->roleAccess->getRolebyUserId($user_id)[0]->role_id;
                        $inspRoleExists = DB::table('inspect_role_attribute')->where('role_id',$role_id)->count();
                 
                 if(!$inspRoleExists)
            	      $role_id = false;
         	
			$primaryCollection = DB::table('eseal_'.$manufacturer_id)->where(['primary_id'=>$eseal_id])->get()->toArray();


			if(!empty($primaryCollection))
			{
				if($primaryCollection[0]->is_active == 0)
					$message = 'The IOT is scrapped';

				$responsetype = 'level'.$primaryCollection[0]->level_id;
				$final['response_type'] = $responsetype;

				if($primaryCollection[0]->po_number != NULL)
				{
					$final['po_number'] = $primaryCollection[0]->po_number;
				}

				if($primaryCollection[0]->storage_location != NULL && $primaryCollection[0]->storage_location !='')
				{
					$final['storage_location'] = $primaryCollection[0]->storage_location;
				}
				if($primaryCollection[0]->is_redeemed != 0)
				{
					$final['is_redeemed'] = 1;
				}
				if(!is_null($primaryCollection[0]->serial_no) || !empty($primaryCollection[0]->serial_no))
				{
					$final['serial_no'] = (string)$primaryCollection[0]->serial_no;
				}
				
				$pid = $primaryCollection[0]->pid;
				if(!empty($pid) && $primaryCollection[0]->level_id == 0)
				{				
					if(!empty($primaryCollection[0]->attribute_map_id))
					{
						$final['product_data'] = $this->getProductInfoFromAttributeMap($pid,$primaryCollection[0]->attribute_map_id,$eseal='',$manufacturer_id,$eseal_id,$role_id);
						// print_r($final);exit;
					}
					else
					{
						$prodCollection = DB::table('products')->where('product_id',$pid)->get()->toArray();
						$prodInfo = ['product_id'=>$prodCollection[0]->product_id,'name'=>$prodCollection[0]->name,'qty'=>1,'title'=>$prodCollection[0]->title,'description'=>$prodCollection[0]->description,'manufacturer'=> $prodCollection[0]->manufacturer_id,'eseal_id'=>$eseal_id,'batch_no'=>$primaryCollection[0]->batch_no];
						foreach($prodInfo as $key=>$value)
						{
							$prodInfo1[]=['name'=>$key,'value'=>$value];
						}
						$port='';
						if($_SERVER['SERVER_PORT']){
							$port=':'.$_SERVER['SERVER_PORT'];
						}
						$image = 'http://'.$_SERVER['SERVER_NAME'].$port.'/uploads/products/'.$prodCollection[0]->image;
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
						$ppid = DB::table('eseal_'.$manufacturer_id)->where('primary_id',$packingIdLevel1)->value('pid');
						$pname = Products::where('product_id',$ppid)->get()->toArray();
						$pname = $pname[0]->name;
						$qty = DB::table('eseal_'.$manufacturer_id)->where('parent_id',$packingIdLevel1)
						->select(DB::raw('count(distinct(primary_id)) as qty'))->get()->toArray();
						$qty = $qty[0]->qty; 
						$level1info = ['levelId'=>$packingIdLevel1,'level'=>1,'name'=>$pname,'qty'=>intval($qty),'mrp'=>$primaryCollection[0]->mrp,'batch_no'=>$primaryCollection[0]->batch_no];  
						foreach($level1info as $key=>$value)
						{
							$level1info1[] = ['name'=>$key,'value'=>$value];
						}
						$final['level1_info'] = $level1info1;    
						$parent_id =  DB::table('eseal_'.$manufacturer_id)->where('primary_id',$packingIdLevel1)->select('parent_id')->get()->toArray();
						$parent_id = $parent_id[0]->parent_id;
						if($parent_id != 0)
						{
							$qty1 = DB::table('eseal_'.$manufacturer_id)->where('parent_id',$parent_id)->select(DB::raw('count(distinct(primary_id)) as qty1'))->get()->toArray();
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
									  ->get()->toArray();
                            if(!$lot_number){
							$lot_number = DB::table('tp_data')
									  ->join('track_history as th','th.tp_id','=','tp_data.tp_id')
									  ->join('eseal_'.$manufacturer_id.' as es','es.track_id','=','th.track_id')
									  ->where('es.primary_id',$parent_id)	
									  ->orderBy('th.update_time','desc')
									  ->select('th.tp_id')
									  ->get()->toArray();
                             }

							if(!empty($lot_number))
							{
								$lot_number= $lot_number[0]->tp_id;
								$final['tp_info'] = (string)$lot_number;
							}
						}
						else{
							log::info('tp execution :'.$eseal_id);

							$lot_number = DB::table('tp_data')
									  ->join('track_history as th','th.tp_id','=','tp_data.tp_id')
									  ->join('eseal_'.$manufacturer_id.' as es','es.track_id','=','th.track_id')
									  ->where('es.primary_id',$eseal_id)	
									  ->orderBy('th.update_time','desc')
									  ->select('th.tp_id')
									  ->get()->toArray();
							
							Log::info($lot_number);

							if(!$lot_number){		  
							$lot_number = DB::table('tp_data')
									  ->join('track_history as th','th.tp_id','=','tp_data.tp_id')
									  ->join('eseal_'.$manufacturer_id.' as es','es.track_id','=','th.track_id')
									  ->where('es.primary_id',$packingIdLevel1)	
									  ->orderBy('th.update_time','desc')
									  ->select('th.tp_id')
									  ->get()->toArray();
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
									  ->get()->toArray();

                        if(!empty($lot_number))
							{		
								$lot_number = $lot_number[0]->tp_id;
								$final['tp_info'] = (string)$lot_number; 
							} 
					}
				}
				if($primaryCollection[0]->level_id == 1 || $primaryCollection[0]->level_id == 8)
				{
Log::info('inside level1');
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
Log::info('inside else in level1');
						$attribute_map_id= DB::table('eseal_'.$manufacturer_id)->where('parent_id',$eseal_id)->select(DB::raw('count(*) as qty'),'attribute_map_id')->take(1)->get()->toArray();
						if(empty($attribute_map_id[0]->attribute_map_id))
						{
							$prodCollection = DB::table('products')->where('product_id',$pid)->get()->toArray();
							$prodInfo = ['product_id'=>$prodCollection[0]->product_id,'name'=>$prodCollection[0]->name,'qty'=>$attribute_map_id[0]->qty,'title'=>$prodCollection[0]->title,'description'=>$prodCollection[0]->description,'manufacturer'=> $prodCollection[0]->manufacturer_id,'eseal_id'=>$eseal_id,'batch_no'=>$primaryCollection[0]->batch_no];
							foreach($prodInfo as $key=>$value)
							{
								$prodInfo1[]=['name'=>$key,'value'=>$value];
							}
							$port='';
						if($_SERVER['SERVER_PORT']){
							$port=':'.$_SERVER['SERVER_PORT'];
						}
							$image = 'http://'.$_SERVER['SERVER_NAME'].$port.'/uploads/products/'.$prodCollection[0]->image;
							$final['product_data'] = array('product_info'=>$prodInfo1,'image'=>$image);	
						}
						else
						{
Log::info('inside else of get product info from attribute map');
							$final['product_data'] = $this->getProductInfoFromAttributeMap($pid,$attribute_map_id[0]->attribute_map_id,$eseal_id,$manufacturer_id,$eseal_id,$role_id);				
Log::info('after product info');
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
					->select(DB::raw('sum(pkg_qty) as qty'))->get()->toArray();
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
									  ->select('th.tp_id')->get()->toArray();
					
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
									  ->get()->toArray();
					if(!empty($lot_number)){
					$lot_number = $lot_number[0]->tp_id;
					$final['tp_info'] = (string)$lot_number; 	
					}		 
					
				}
			}

			if($primaryCollection[0]->level_id == 2 ){
				//$primary =DB::table('eseal_'.$manufacturer_id)->where('parent_id',$eseal_id)->value('primary_id');
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
					$attribute_map_id= DB::table('eseal_'.$manufacturer_id)->where('parent_id',$eseal_id)->select(DB::raw('count(*) as qty'),'attribute_map_id')->take(1)->get()->toArray();
					$att_map_id = DB::table('eseal_'.$manufacturer_id)->where('primary_id',$eseal_id)->value('attribute_map_id');
					if($att_map_id != 0)
						$attribute_map_id[0]->attribute_map_id = $att_map_id;
					
					if(empty($attribute_map_id[0]->attribute_map_id))
					{
						$prodCollection = DB::table('products')->where('product_id',$pid)->get()->toArray();
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
									  ->select('th.tp_id')->get()->toArray();
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
							   ->orderBy('td.track_id')
							   ->get()->toArray();
							   
				if(empty($trackInfo[0])){
					if($primaryCollection[0]->level_id == 2){
						  $level1_id = DB::table('eseal_'.$manufacturer_id)->where('parent_id',$eseal_id)->select(DB::raw('distinct(primary_id)'))->take(1)->get()->toArray();
						$trackInfo =DB::table('track_details')->where('code',$level1_id[0]->primary_id)->select(DB::raw('distinct(track_id)'))->get()->toArray(); 
						 if(empty($trackInfo[0]->track_id)){
							$primary_id = DB::table('eseal_'.$manufacturer_id)->where('parent_id',$level1_id[0]->primary_id)->select(DB::raw('distinct(primary_id)'))->take(1)->get()->toArray();
						   $trackInfo =DB::table('track_details')->where('code',$primary_id[0]->primary_id)->select(DB::raw('distinct(track_id)'))->get()->toArray();
						 } 
					}
					if($primaryCollection[0]->level_id == 1){
						$primary_id =  DB::table('eseal_'.$manufacturer_id)->where('parent_id',$eseal_id)->select(DB::raw('distinct(primary_id)'))->take(1)->get()->toArray();
	
						if(!empty($primary_id))
							$trackInfo =DB::table('track_details')->where('code',$primary_id[0]->primary_id)->select(DB::raw('distinct(track_id)'))->get()->toArray();
					}
				}

				$trackfinal = array();
				
				for($u= 0;$u < count($trackInfo);$u++){


					Log::info('In the Loop'.$u);
					$array1= array(); $array2 = array();
					$tracktr = Trackhistory::where('track_id',$trackInfo[$u]->track_id)->get(['src_loc_id','dest_loc_id','transition_id','sync_time as update_time'])->toArray();
					$tracktr[0]=(object) $tracktr[0];
					$tran  = Transaction::where('id',$tracktr[0]->transition_id)->get()->toArray();
					$tran[0]=(object) $tran[0];
					Log::info($tran);
					if(!empty($tracktr[0]->src_loc_id)){
						$srcloc= DB::table('locations')
						->leftJoin('location_types','locations.location_type_id','=','location_types.location_type_id')
						->where('locations.location_id',$tracktr[0]->src_loc_id)
						->select('locations.location_name','locations.location_address','locations.latitude','locations.longitude','location_types.location_type_name')  
						->get()->toArray();
						$array1 = array('status'=>$tran[0]->name,'source'=>$srcloc[0]->location_name,'source_loc_type'=>$srcloc[0]->location_type_name,'source_address'=>$srcloc[0]->location_address,'src_lat'=>$srcloc[0]->latitude,'src_long'=>$srcloc[0]->longitude,'time'=>$tracktr[0]->update_time);  
					}
					if(!empty($tracktr[0]->dest_loc_id)){
						$destloc= DB::table('locations')
						->leftJoin('location_types','locations.location_type_id','=','location_types.location_type_id')
						->where('locations.location_id',$tracktr[0]->dest_loc_id)
						->select('locations.location_name','locations.location_address','locations.latitude','locations.longitude','location_types.location_type_name')  
						->get()->toArray();
						$array2 = array('status'=>$tran[0]->name,'destination'=>$destloc[0]->location_name,'dest_loc_type'=>$destloc[0]->location_type_name,'dest_address'=>$destloc[0]->location_address,'dest_lat'=>$destloc[0]->latitude,'dest_long'=>$destloc[0]->longitude,'time'=>$tracktr[0]->update_time);
					}
					array_push($trackfinal,$array1+$array2);
				}
				$final['trace_info'] = $trackfinal;
				$status= 1;	

				return json_encode(array('Status'=>$status,'Message'=>$message,'Data'=>$final));	
			}
/////////////////////////////////end of Level0,Level1,Level2 Info//////////////////////////////////////
			$primaryCollection = DB::table('eseal_bank_'.$manufacturer_id)->where(['id'=>$eseal_id,'level'=>9])->get()->toArray();
			// print_r($primaryCollection);exit;
			 $status =0;
			if(!empty($primaryCollection)){
				$final['response_type'] = 'tp';	
				$tpattr = DB::table('tp_attributes')->where('tp_id',$eseal_id)->get()->toArray();
				
				$tpdetails =DB::select('select * from track_history th join track_details td on th.track_id=td.track_id where td.code='.$eseal_id.' order by th.update_time limit 1');
				
				$transitfinal = array();
				
				if(empty($tpdetails)){
    				throw new Exception("Tp does not Exists");
    			}
				
				$srcloc= DB::table('locations')
							  ->leftJoin('location_types','locations.location_type_id','=','location_types.location_type_id')
							  ->where('locations.location_id',$tpdetails[0]->src_loc_id)
							  ->select('locations.location_name','locations.location_address','locations.latitude','locations.longitude','location_types.location_type_name')  
							  ->get()->toArray();

				$destloc= DB::table('locations')
							  ->leftJoin('location_types','locations.location_type_id','=','location_types.location_type_id')
							  ->where('locations.location_id',$tpdetails[0]->dest_loc_id)
							  ->select('locations.location_name','locations.location_address','locations.latitude','locations.longitude','location_types.location_type_name')  
							  ->get()->toArray();

				array_push($transitfinal,array('source'=>$srcloc[0]->location_name,'source_loc_type'=>$srcloc[0]->location_type_name,'source_address'=>$srcloc[0]->location_address,'src_lat'=>$srcloc[0]->latitude,'src_long'=>$srcloc[0]->longitude,
					'destination'=>$destloc[0]->location_name,'dest_loc_type'=>$destloc[0]->location_type_name,'dest_address'=>$destloc[0]->location_address,'dest_lat'=>$destloc[0]->latitude,'dest_long'=>$destloc[0]->longitude,'modified_date'=>$tpdetails[0]->sync_time));
				$final['transitInfo'] = $transitfinal;

				$trackInfo =DB::table('track_details')->where('code',$eseal_id)->select(DB::raw('distinct(track_id)'))->get()->toArray();
				$trackfinal = array();
                
   				for($u=0; $u < count($trackInfo); $u++){
					$array1= array(); 
					$array2 = array();
					$tracktr = Trackhistory::where('track_id',$trackInfo[$u]->track_id)->get(['src_loc_id','dest_loc_id','transition_id as transition_id','sync_time as update_time'])->toArray();  
					$tran    = Transaction::where('id',$tracktr[0]['transition_id'])->get()->toArray();
					if(!empty($tracktr[0]['src_loc_id'])){
						
						$srcloc= DB::table('locations')
						->leftJoin('location_types','locations.location_type_id','=','location_types.location_type_id')
						->where('locations.location_id',$tracktr[0]['src_loc_id'])
						->select('locations.location_name','locations.location_address','locations.latitude','locations.longitude','location_types.location_type_name')  
						->get()->toArray();
						$array1 = array('status'=>$tran[0]['name'],'source'=>$srcloc[0]->location_name,'source_loc_type'=>$srcloc[0]->location_type_name,'source_address'=>$srcloc[0]->location_address,'src_lat'=>$srcloc[0]->latitude,'src_long'=>$srcloc[0]->longitude,'time'=>$tracktr[0]['update_time']);  
					}

					if(!empty($tracktr[0]['dest_loc_id'])){
						$destloc= DB::table('locations')
						->leftJoin('location_types','locations.location_type_id','=','location_types.location_type_id')
						->where('locations.location_id',$tracktr[0]['dest_loc_id'])
						->select('locations.location_name','locations.location_address','locations.latitude','locations.longitude','location_types.location_type_name')  
						->get()->toArray();
						$array2 = array('status'=>$tran[0]['name'],'destination'=>$destloc[0]->location_name,'dest_loc_type'=>$destloc[0]->location_type_name,'dest_address'=>$destloc[0]->location_address,'dest_lat'=>$destloc[0]->latitude,'dest_long'=>$destloc[0]->longitude,'time'=>$tracktr[0]['update_time']);
					}
					//print_r($array1+$array2);exit;
					 array_push($trackfinal,$array1+$array2);
				}

				$final['trace_info'] = $trackfinal;

/*				 $levelIds = DB::table('tp_data')->where('tp_id',$eseal_id)->get(['level_ids'])->toArray();
*/				 $levelIds = DB::table('tp_data')->where('tp_id',$eseal_id)->get(['level_ids'])->toArray();
				 //$x= implode(',', $levelIds);
				 //get(['level_ids'])->toArray();

//code added by jyothi starts 

foreach ($levelIds as $levelId) {
	$new_arr[]=$levelId->level_ids;
	}
	$res=implode(',',$new_arr);
//code added by jyothi ends


				 //print_r($res);exit;
				 $pids = DB::select('select cast(cast(pkg_qty as UNSIGNED) as char) as qty,level_id as level,cast(primary_id as char) as primary_id,products.name,CAST(pid as CHAR) as pid,eseal_'.$manufacturer_id.'.mrp,eseal_'.$manufacturer_id.'.batch_no from eseal_'.$manufacturer_id.' join products on products.product_id = eseal_'.$manufacturer_id.'.pid where primary_id IN (' .$res. ')');

				$final['child_list'] = $pids;
				if(!empty($tpattr) && count($tpattr)>0){
					$tpattr1= array();     
					foreach($tpattr as $tpvalue){
						if($tpvalue->attribute_name== 'Park GRN No'){
							array_push($tpattr1,array('name'=>$tpvalue->attribute_name,'value'=>$tpvalue->reference_value));
						}else{
						array_push($tpattr1,array('name'=>$tpvalue->attribute_name,'value'=>$tpvalue->value));
					    }
					}
					$final['tp_attributes'] = $tpattr1;
				}

				$status =1;
				$message = 'Data Retrieved Successfully';
		// print_r($status);exit;
				return json_encode(['Status'=>$status,'Message'=>$message,'Data'=>$final]);
			}
			if(empty($primaryCollection)){
				throw new Exception('In-valid eseal Id.');
			}
		}
		catch(Exception $e){
			$message = $e->getMessage();
		}	
		return json_encode(array('Status'=>$status,'Message'=>$message,'Data'=>$final));	
	}
		private function getProductInfoFromAttributeMap($pid,$attribute_map_id,$eseal_id='',$manufacturer_id,$code,$role_id){
Log::info('role id is:'.$role_id);
		$finAttr = array();
		$attributeMap = DB::table('bind_history')->where('eseal_id',$code)->select(['location_id','attribute_map_id','created_on'])->get()->toArray();
		$batch_no = DB::table('eseal_'.$manufacturer_id)->where('primary_id',$code)->value('batch_no');
	
	if(!empty($attributeMap)){	
		foreach($attributeMap as $map){
		
			$locAttr = array();
			$map1 = explode(',',$map->attribute_map_id); 
			$location_name = Location::where('location_id',$map->location_id)->value('location_name');

			
			$attributes = DB::table('attribute_mapping as am')
						   ->join('attributes as attr','attr.attribute_id','=','am.attribute_id');
				if($role_id){

					$attributes->join('inspect_role_attribute as ira','ira.attribute_id','=','attr.attribute_id')
					           ->where('ira.role_id',$role_id);

				}		   
			 $attributes = $attributes->whereIn('am.attribute_map_id',$map1)
						              ->get(['attr.name','am.value'])->toArray();
			if(!empty($attributes)){
				foreach($attributes as $attribute){
					if($attribute->name != 'Valid Expiry Date'){
						$locAttr[] = ['name'=>$attribute->name,'value'=>$attribute->value];	
					}
					
				}
				$finAttr[] = ['location_name' =>$location_name,'attributes'=>$locAttr,'created_on'=>$map->created_on];
			}               
		
		}
  }
	 Log::info('almost compattr');
		$compAttr = array();
		
		
		$level =  DB::table('eseal_'.$manufacturer_id)->where('primary_id',$code)->value('level_id');
		if($level == 0){
		/*$Eseal_id =DB::table('attribute_mapping as am')
								->join('attributes as a','a.attribute_id','=','am.attribute_id')
								->where('am.attribute_map_id',$attribute_map_id)
								->where('a.default_value','=','DYNAMIC')
								
								->get(['a.name','am.value']);*/
		$Eseal_id = DB::table('eseal_'.$manufacturer_id.' as eseal')
						  ->join('products','products.product_id','=','eseal.pid')
						  ->where('eseal.parent_id',$code)
						  ->get(['primary_id','name'])->toArray();
		if(!empty($Eseal_id)){
		foreach($Eseal_id as $id){
		$attributeMap = DB::table('bind_history')->where('eseal_id',$id->primary_id)->groupBy('location_id')->groupBy('attribute_map_id')->get(['location_id','attribute_map_id'])->toArray();
 if(!empty($attributeMap)){
		foreach($attributeMap as $map){
			$location_name = Location::where('location_id',$map->location_id)->value('location_name');
			$attributes = DB::table('attribute_mapping as am')
						   ->join('attributes as attr','attr.attribute_id','=','am.attribute_id');
						   if($role_id){

					$attributes->join('inspect_role_attribute as ira','ira.attribute_id','=','attr.attribute_id')
					           ->where('ira.role_id',$role_id);

				}		
				$attributes = $attributes->where('am.attribute_map_id',$map->attribute_map_id)
						                 ->get(['attr.name','am.value'])->toArray();

				
			if(!empty($attributes)){
 Log::info('almost attributes');
				foreach($attributes as $attribute){
					$locAttr1[] = ['name'=>$attribute->name,'value'=>$attribute->value];
				}
				$vendor_code ='';
				$vendor_name ='';
				$vendor = DB::table('attribute_mapping as am')
								->join('attributes as a','a.attribute_id','=','am.attribute_id')
								->where('am.attribute_map_id',$map->attribute_map_id)
								->where('a.attribute_code','vendor_code')
								->get(['am.value'])->toArray();
				if(!empty($vendor)){
					$vendor_code = $vendor[0]->value;
					$vendor_name = Location::where('erp_code',$vendor_code)->value('location_name');
				}           
				$compAttr[] = ['component_name'=>$id->name,'location_name' =>$location_name,'vendor_code'=>$vendor_code,'vendor_name'=>$vendor_name,'attributes'=>$locAttr1];
			}               
		}
		}						
}
}
}
		Log::info('almost endddddd');
		 
		$prodAttrs = array();
		$prodCollection = DB::table('products')->where('product_id',$pid)->get()->toArray();
		if(empty($eseal_id)){
			$qty =1;
		}
		else{
		 $qty = DB::select('select sum(pkg_qty) as qty from eseal_'.$manufacturer_id.' where parent_id='.$eseal_id);
		 $qty = $qty[0]->qty;
		}
		$type = DB::table('master_lookup')->where('value',$prodCollection[0]->product_type_id)->value('name');
		$prodInfo1= array();
		$prodInfo = ['Product Id'=>$prodCollection[0]->product_id,'Name'=>$prodCollection[0]->name,'Title'=>$prodCollection[0]->title,'Description'=>$prodCollection[0]->description,'Qty'=>intval($qty),'Eseal Id'=>$code,'batch_no'=>$batch_no,'Type'=>$type];
		foreach($prodInfo as $key=>$value){
			array_push($prodInfo1,array('name'=>$key,'value'=>$value));
		}
		$port='';
		if($_SERVER['SERVER_PORT']){
			$port=':'.$_SERVER['SERVER_PORT'];
		}
		$image = 'http://'.$_SERVER['SERVER_NAME'].$port.'/uploads/products/'.$prodCollection[0]->image;
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
Log::info('almost after enddd at enddd');		
		$attributeCollection = $attributeCollection->where('am.attribute_map_id',$attribute_map_id)
								                   ->get(['a.name','am.value','am.location_id'])->toArray();
		$mapped_date = DB::table('attribute_mapping')->where('attribute_map_id',$attribute_map_id)->value('mapping_date');						
		if(!empty($attributeCollection)){
			
		foreach($attributeCollection as $attribute){
			$location_id = $attribute->location_id;
			if($attribute->name !='Valid Expiry Date'){
				array_push($prodAttr,array('name'=>$attribute->name,'value'=>$attribute->value));	
			}
			
			}
		Log::info('doneeeeeeee');
/*			$location = Location::where('location_id',$location_id)->get(['location_name'])->toArray();
*/			
$location = Location::where('location_id',$location_id)->value('location_name');


Log::info($location);
Log::info($location_id);
Log::info('steppp0');
			array_push($prodAttr,array('name'=>'LOCATION_NAME','value'=>$location));
			// print_r($prodAttr);exit;
                Log::info('steppp1');
                        array_push($prodAttr,array('name'=>'Name','value'=>$prodCollection[0]->name));
Log::info('steppp2');
			array_push($prodAttr,array('name'=>'Eseal Id','value'=>$code));
		Log::info('xxxxxxxxxxxxxxxxxxxx');
		$prodAttr1['mapped_date'] = $mapped_date;
		$prodAttr1['attributes'] = $prodAttr;
		
	}
Log::info('after doneeee');
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
	 public function GetAllShipments(){
	    $startTime = $this->getTime();
	    try{
		$status = 0;
		$message = '';
		$res = Array();
		Log::info(__FUNCTION__.' === '. print_r($this->_request->all(),true));
		//$currentLocationId = trim($this->_request->input('dest_location_id'));
		$currentLocationId = $this->roleAccess->getLocIdByToken($this->_request->input('access_token'));
		$requestType = trim($this->_request->input('request_type')); ///////Array('in','out', 'pr')

		//$otherLocationId = trim($this->_request->input('src_location_id'));
                $otherLocationId = '';
		$tpId = trim($this->_request->input('tpId'));
		$fromDate = trim($this->_request->input('fromDate'));
		$toDate = trim($this->_request->input('toDate'));
		$res1 =  array();
		Log::info($toDate);
		Log::info('location:'.$currentLocationId);
		

		$locationObj = new Location();

         
		$mfgId = $locationObj->getMfgIdForLocationId($currentLocationId);		
		if($mfgId){
		  $esealTable = 'eseal_'.$mfgId;

			if(strtolower($requestType) == 'in' || strtolower($requestType) == 'pr' || strtolower($requestType) == 'pg'){
			if(strtolower($requestType) == 'in' || strtolower($requestType) == 'pr'){
				/*$sql = '
					SELECT 
						tp.id, th.tp_id, pdf_file, src_loc_id, 
						(select location_name from locations ll where ll.location_id=th.src_loc_id) as locationName, th.update_time, \'In Transit\' as status,
						(select value from '.$this->TPAttributeMappingTable.'  tpa where tpa.tp_id=th.tp_id and (tpa.attribute_name="Document Number" or tpa.attribute_name="Purchase Order No" or tpa.attribute_name="Eseal Document Number") ) as delivery_no,
						"line_items" as line_items
					FROM 
						tp_pdf tp, track_history th
						
					WHERE 
						tp.tp_id=th.tp_id and tp.tp_id not in (select tp_id from tp_attributes as tpa where (tpa.tp_id=th.tp_id and tpa.attribute_name="Park GRN No" AND value!="" AND value!="unknown"  AND reference_value!=""))
						and th.dest_loc_id='.$currentLocationId;*/

						/*			$sql = '
					SELECT 
						tp.id, th.tp_id, pdf_file, src_loc_id, 
						(select location_name from locations ll where ll.location_id=th.src_loc_id) as locationName, th.update_time, \'In Transit\' as status,
						(select value from '.$this->TPAttributeMappingTable.'  tpa where tpa.tp_id=th.tp_id and (tpa.attribute_name="Document Number" or tpa.attribute_name="Purchase Order No") ) as delivery_no,
						"line_items" as line_items
					FROM 
						tp_pdf tp, track_history th
					WHERE 
						tp.tp_id=th.tp_id 
						and th.dest_loc_id='.$currentLocationId;*/

						$sql = '
					SELECT 
						tp.id, th.tp_id, pdf_file, src_loc_id, 
						(select location_name from locations ll where ll.location_id=th.src_loc_id) as locationName, th.update_time, \'In Transit\' as status,tpa.value as delivery_no,
						"line_items" as line_items
					FROM 
						tp_pdf tp, track_history th
						join '.$this->TPAttributeMappingTable.' tpa on (tpa.tp_id=th.tp_id and (tpa.attribute_name="Document Number" or tpa.attribute_name="Purchase Order No"))
					WHERE 
						tp.tp_id=th.tp_id and tp.tp_id not in (select tp_id from tp_attributes as tpa where (tpa.tp_id=th.tp_id and tpa.attribute_name="Park GRN No" AND value!="" AND value!="unknown"  AND reference_value!="")
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
					//echo $sql; exit;
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
                }
				if(strtolower($requestType) != 'pr' && strtolower($requestType) != 'pg'){
					/*$sql1 = '
						SELECT 
							tp.id, th.tp_id, pdf_file, src_loc_id, 
							(select location_name from locations ll where ll.location_id=th.src_loc_id) as locationName, th.update_time, \'Receive\' as status ,
							(select value from '.$this->TPAttributeMappingTable.'  tpa where tpa.tp_id=th.tp_id and (tpa.attribute_name="Document Number" or tpa.attribute_name="Purchase Order No" or tpa.attribute_name="Eseal Document Number")) as delivery_no,
							"line_items" as line_items
						FROM
							tp_pdf tp, track_history th, '.$esealTable.' e 
						WHERE 
							tp.tp_id = th.tp_id and th.track_id=e.track_id and th.src_loc_id = '.$currentLocationId.' and th.dest_loc_id = 0';*/

							/*$sql1 = '
						SELECT 
							tp.id, th.tp_id, pdf_file, src_loc_id, 
							(select location_name from locations ll where ll.location_id=th.src_loc_id) as locationName, th.update_time, \'Receive\' as status ,
							(select value from '.$this->TPAttributeMappingTable.'  tpa where tpa.tp_id=th.tp_id and (tpa.attribute_name="Document Number" or tpa.attribute_name="Purchase Order No")) as delivery_no,
							"line_items" as line_items
						FROM
							tp_pdf tp, track_history th, '.$esealTable.' e 
						WHERE 
							tp.tp_id = th.tp_id and th.track_id=e.track_id and th.src_loc_id = '.$currentLocationId.' and th.dest_loc_id = 0';*/

							$sql1 = ' 
						SELECT 
							tp.id, th.tp_id, pdf_file, src_loc_id, 
							(select location_name from locations ll where ll.location_id=th.src_loc_id) as locationName, th.update_time, \'Receive\' as status ,tpa.value as delivery_no,
							"line_items" as line_items
						FROM
							tp_pdf tp, track_history th, '.$esealTable.' e 
							join '.$this->TPAttributeMappingTable.' as tpa on (tpa.tp_id=th.tp_id and (tpa.attribute_name="Document Number" or tpa.attribute_name="Purchase Order No"))
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
				if(strtolower($requestType) == 'pg'){

				/*	$sql = '
					SELECT 
						tp.id, th.tp_id, pdf_file, src_loc_id, 
						(select location_name from locations ll where ll.location_id=th.src_loc_id) as locationName, th.update_time, \'In Transit\' as status,
						(select value from '.$this->TPAttributeMappingTable.'  tpa where tpa.tp_id=th.tp_id and (tpa.attribute_name="Park GRN No" and value="unknown") ) as delivery_no,
						"line_items" as line_items
					FROM 
						tp_pdf tp, track_history th
					WHERE 
						tp.tp_id=th.tp_id 
						and th.dest_loc_id='.$currentLocationId;*/
//echo $currentLocationId; exit;
						$sql = 'select 
						tp.id, th.tp_id, pdf_file, src_loc_id, 
						(
							SELECT location_name
							FROM locations ll
							WHERE ll.location_id=th.src_loc_id) AS locationName, th.update_time, "Park GRN" AS status,
							tpa.value AS delivery_no,
							"line_items" AS line_items
							FROM tp_pdf tp, track_history th
							join tp_attributes as tpa on (tpa.tp_id=th.tp_id and tpa.attribute_name="Park GRN No" AND tpa.value!="" 
							AND tpa.value!="unknown"  
							AND tpa.reference_value!="")
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
				//	echo $sql; exit;
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
                }

			}
			

			if(strtolower($requestType) == 'out'){
				/*$sql = '
					SELECT 
						tp.id, th.tp_id, pdf_file, dest_loc_id, 
						(select location_name from locations ll where ll.location_id=th.dest_loc_id) as locationName, th.update_time, tr.name as status,
						(select value from '.$this->TPAttributeMappingTable.'  tpa where tpa.tp_id=th.tp_id and (tpa.attribute_name="Document Number" or tpa.attribute_name="Purchase Order No" or tpa.attribute_name="Eseal Document Number")) as delivery_no,
						"line_items" as line_items
					FROM 
						tp_pdf tp, track_history th,transaction_master tr 
					WHERE 					   
						tp.tp_id=th.tp_id and tr.id=th.transition_id
						and th.src_loc_id = '. $currentLocationId;*/


						$sql = '
					SELECT 
						tp.id, th.tp_id, pdf_file, dest_loc_id, 
						(select location_name from locations ll where ll.location_id=th.dest_loc_id) as locationName, th.update_time, tr.name as status, tpa.value as delivery_no,
						"line_items" as line_items
					FROM 
						tp_pdf tp
						join track_history th on tp.tp_id=th.tp_id
						join transaction_master tr on tr.id=th.transition_id
						join '.$this->TPAttributeMappingTable.' tpa on (tpa.tp_id=th.tp_id and (tpa.attribute_name="Document Number" or tpa.attribute_name="Purchase Order No" or tpa.attribute_name="Eseal Document Number"))
					WHERE 
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
                    		$status = DB::table($this->transactionMasterTable)->where('id',$isReceived[0]->transition_id)->value('name');

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

			Log::info('======='.count($res).' '.!empty($res));
			if(!empty($res)){

				foreach($res as $re){
					$line_items = DB::table('tp_data as tp')
					                  ->join('eseal_'.$mfgId.' as es','es.primary_id','=','tp.level_ids')
					                  ->join('products as pr','pr.product_id','=','es.pid')
					                  ->join('uom_classes as uc','uc.id','=','pr.uom_class_id')					                  
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

		$locationId = trim($this->_request->input('srcLocationId'));
		$tpId = trim($this->_request->input('tpId'));
		$locationObj = new Location();
		$mfgId = $locationObj->getMfgIdForLocationId($locationId);
		$pdfData = Array();
		if($mfgId){
			try{
			  $res =  DB::table($this->tpPDFTable)
						->where('tp_id', $tpId)
						->select('pdf_content', 'pdf_file')
						->get()->toArray();
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

	return json_encode(Array('Status'=>$status, 'Message' =>'S-: '.$message, 'pdfData' => $pdfData)); 
}

public function RetryEseals()
	{
 $startTime = $this->getTime();
		try
		{
			$status = 0;
			$message = '';
			$locationId = $this->_request->input('locationId');
			$ids = $this->_request->input('ids');
			$loadChilds = $this->_request->input('loadChilds');
			$productArray = array();
			$trackArray = array();
			$inHouseInventory = false;
			Log::info(__FUNCTION__ . ' : ' . print_r($this->_request->all(), true));
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
			$locationObj = new Location();
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
				$levels = DB::table($esealTable)->whereIn('primary_id',$ids)->distinct()->pluck('level_id');
				if(empty($levels))
					throw new Exception('The IOTS are invalid');
			foreach($levels as $level){
		  if($level != 0){
		 	$childs = DB::table($esealTable)->whereIn('parent_id',$ids)->pluck('primary_id');
		 	if(!empty($childs)){
		 		$ids = array_unique(array_merge($childs,$ids));
		 	}
		 }
		 else{
		 	$parents = DB::table($esealTable)->whereIn('primary_id',$ids)->where('parent_id','!=',0)->pluck('parent_id');
		 	$childs = DB::table($esealTable)->whereIn('parent_id',$parents)->pluck('primary_id');
		 	if(!empty($childs)){
		 		$ids = array_unique(array_merge($childs,$ids));
		 	}
		 }


		 }
		 }

			$location_type_id = DB::table('location_types')->where(['location_type_name'=>'Plant','manufacturer_id'=>$mfgId])->value('location_type_id');
           
            $ids = implode(',',$ids);
			$sql = 'select th.track_id,th.src_loc_id from track_history th join eseal_'.$mfgId.' es on es.track_id=th.track_id join locations on locations.location_id=th.src_loc_id and locations.location_type_id='.$location_type_id.' and dest_loc_id=0 and es.primary_id in('.$ids.')';
		 
		 $result = DB::select($sql);
		 if(empty($result)){
	     log::info('Location Ids:-' .$locationId);

	             $result =   DB::table('track_history as th')
                                 ->join($esealTable.' as es','es.track_id','=','th.track_id')
                                 ->whereIn('th.src_loc_id',explode(',',$locationId))
                                 ->where('th.dest_loc_id',0)
                                 ->whereIn('primary_id',explode(',',$ids))
                                 ->get(['th.track_id','th.src_loc_id'])->toArray();

         Log::info($result);
                                 
              if(empty($result))
			      throw new Exception('Data not-found');

			  $inHouseInventory = true;
		 }
		 
         $currentLocationId = $this->roleAccess->getLocIdByToken($this->_request->input('access_token'));

		 $plant_location_id = $result[0]->src_loc_id;

		 if($currentLocationId == $plant_location_id)
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
WHERE e1.parent_id=e.primary_id) as UNSIGNED) AS qty,e.prod_batch_no,e.storage_location,
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
			 //  echo $sql; exit;
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
						$message ="Data Found.";
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

			$inTransit = DB::table('transaction_master')->where(['name'=>'Stock Transfer','manufacturer_id'=>$mfgId])->value('id');
				/**************STOCK TRANSFER***********/
             
			$request = Request::create('scoapi/UpdateTracking', 'POST', array('module_id'=>$this->_request->input('module_id'),'access_token'=>$this->_request->input('access_token'),'codes'=>$transitIds,'srcLocationId'=>$plant_location_id,'destLocationId'=>$currentLocationId,'transitionTime'=>$transitionTime,'transitionId'=>$inTransit,'internalTransfer'=>0));
		    $originalInput = Request::input();//backup original input
			Request::replace($request->input());						
		    $response = Route::dispatch($request)->getContent();
			$response = json_decode($response,true);
						if($response['Status'] == 0)
							throw new Exception($response['Message']);
               
            $receive = DB::table('transaction_master')->where(['name'=>'Receive','manufacturer_id'=>$mfgId])->value('id');  
            /**************RECEIVE******************/ 

            $request = Request::create('scoapi/UpdateTracking', 'POST', array('module_id'=>$this->_request->input('module_id'),'access_token'=>$this->_request->input('access_token'),'codes'=>$transitIds,'srcLocationId'=>$currentLocationId,'destLocationId'=>0,'transitionTime'=>$transitionTime,'transitionId'=>$receive,'internalTransfer'=>0));
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
		Log::info(['Status'=>$status, 'Message' =>'S-:'.$message,'esealData' => $result]);
		return json_encode(['Status' => $status, 'Message' =>'S-:' . $message, 'esealData' => $result]);
	}

public function getTpAttributeInfo($data){	
		try{
			$status =0;
			$tpattr = $consu_attr=array();
			$mfg_id= $this->roleAccess->getMfgIdByToken($data['access_token']);
			$location_id = $data['location_id'];

			$user_location_id = $this->roleAccess->getLocIdByToken($this->_request->input('access_token'));
			$distributor=DB::table('locations as l')
							->join('location_types as lt','l.location_type_id','=','lt.location_type_id')
							->where('l.location_id',$this->roleAccess->getLocIdByToken($this->_request->input('access_token')))
							->where('lt.location_type_name',"Distributor")
							->value('lt.location_type_id');
						//	print_r(DB::getQueryLog());
						//	echo $distributor; exit;
			$isDistributor=$distributor==''?0:$distributor;
			if(empty($location_id)){	
			$tpattr= DB::table('location_tp_attributes')
			->join('attributes','attributes.attribute_id','=','location_tp_attributes.attribute_id')
			->where('location_tp_attributes.manufacturer_id',$mfg_id)
			->where('attributes.attribute_type','<>',6)
			->get(array('attributes.name','attributes.attribute_code','attributes.input_type','attributes.regexp','attributes.default_value','attributes.is_required','attributes.validation'))->toArray();
		}
		else{
			$tpattr= DB::table('location_tp_attributes')
			->join('attributes','attributes.attribute_id','=','location_tp_attributes.attribute_id')
			->where('location_tp_attributes.location_id',$location_id)
			->where('attributes.attribute_type','<>',6)
			->get(array('attributes.name','attributes.attribute_code','attributes.input_type','attributes.regexp','attributes.default_value','attributes.is_required','attributes.validation'))->toArray();
		  
		}
		if($isDistributor){
			if(empty($location_id)){	
				$consu_attr= DB::table('location_tp_attributes')
				->join('attributes','attributes.attribute_id','=','location_tp_attributes.attribute_id')
				->where('location_tp_attributes.manufacturer_id',$mfg_id)
				->where('attributes.attribute_type',6)
				->get(array('attributes.name','attributes.attribute_code','attributes.input_type','attributes.regexp','attributes.default_value','attributes.is_required','attributes.validation'))->toArray();
			} else{
				$consu_attr= DB::table('location_tp_attributes')
				->join('attributes','attributes.attribute_id','=','location_tp_attributes.attribute_id')
				->where('location_tp_attributes.location_id',$location_id)
				->where('attributes.attribute_type',6)
				->get(array('attributes.name','attributes.attribute_code','attributes.input_type','attributes.regexp','attributes.default_value','attributes.is_required','attributes.validation'))->toArray();			  
			}
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
		if(count($consu_attr)>0){

			foreach ($consu_attr as $key => $value) {
				if($value->input_type=='select'){
					$defaults=  DB::table('attribute_options as ao')
					->join('attributes as a','a.attribute_id','=','ao.attribute_id')
					->where('attribute_code',$value->attribute_code)->pluck('option_value')->toArray();
                         $consu_attr[$key]->options = $defaults;
				}
			}


		return json_encode(array('Status'=>$status,'Message'=>'S-: '.$message,'tpData'=>$tpattr,'consuData'=>$consu_attr));

		} else 
		return json_encode(array('Status'=>$status,'Message'=>'S-: '.$message,'tpData'=>$tpattr));
	}
public function getAllLocations($data){
		try{
			Log::info(__FUNCTION__.' : '.print_r($this->_request->all(),true));			
			$status =0;
			$locations = array();
			$manufacturer_id= $this->roleAccess->getMfgIdByToken($data['access_token']);
			$location_id = $this->roleAccess->getLocIdByToken($data['access_token']);
			$location_type = strtolower(trim($this->_request->input('location_type')));			 
			$location_type_arr = array();

			
			$locations = Array();
			if(empty($manufacturer_id)){
				throw new Exception('Parameters missing');
			}
			
			if($location_type)
			{  				
				$checkLocationTypes = DB::table('location_types')->where('manufacturer_id','=',$manufacturer_id)->where('location_type_name','=',$location_type)->value('location_type_name');
				if(strtolower($checkLocationTypes) == $location_type){
					array_push($location_type_arr,$location_type);

					if($checkLocationTypes == 'vendor')
						array_push($location_type_arr,'supplier');
                      if($checkLocationTypes == 'Retailer'){
                         array_push($location_type_arr,'Retailer');
                           $locations = DB::table('locations')
                ->join('location_types','location_types.location_type_id','=','locations.location_type_id')
                ->where('locations.manufacturer_id','=',$manufacturer_id)
                ->whereIn('location_types.location_type_name',$location_type_arr)
                ->where('retailer_id','=',$location_id)
                ->select('locations.*')
			    ->get()->toArray();
			          goto commit;  
                      } 
               $locations = DB::table('locations')
                ->leftJoin('location_types','location_types.location_type_id','=','locations.location_type_id')
                ->where('locations.manufacturer_id','=',$manufacturer_id)
                ->whereIn('location_types.location_type_name',$location_type_arr)
                ->where('location_id','!=',$location_id)
                ->select('locations.*')
                ->addSelect('location_types.location_type_name')
			    ->get()->toArray();
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
			->get()->toArray();
	      	}
	      	commit:
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
	Log::info(['Status'=> $status, 'Message'=>'S-: '.$message, 'locationData'=> $locations]);	
	return json_encode(Array('Status'=> $status, 'Message'=>'S-: '.$message, 'locationData'=> $locations));
	}


public function getDeliveryDataOld(){

    try{
    	$status =1;
    	$inputData=$this->_request->all();
    	$mfgId = $this->roleAccess->getMfgIdByToken($inputData['access_token']);
    	$locationId = $this->roleAccess->getLocIdByToken($inputData['access_token']);
    	// echo $locationId;exit;
    	$module_id = $inputData['module_id'];
    	$getUserId = DB::table('users_token')->where('access_token',$inputData['access_token'])->where('module_id',$module_id)->pluck('user_id');
    	 $shipment_no=$this->_request->input('shipment_no');
    	 $sto_number=$this->_request->input('sto_number');
    	 $shipment_no=$this->_request->input('shipment_no');
    	 $send_cancelled_deliveries=$this->_request->input('send_cancelled_deliveries');
    	 // echo $send_cancelled_deliveies;exit;
    	 //print_r($inputData);exit;
    	// $sto_number=$input['sto_number'];
        $getDelivery=array();
        if(!$locationId){
              throw new Exception('Location doesnt exists');
        }
    	if($locationId){
    		// echo $locationId;exit;

    		if($send_cancelled_deliveries==1){
    			$getDelivery = DB::table('delivery_master as dm')
	    		//->join('master_lookup as ml','ml.value','=','dm.type')
	    		->join('locations as l','l.location_id','=','dm.to_location')
	    		//->where('shipment_no',$shipment_no)
	    		//->orWhere('sto_no',$sto_number)
	    		->where('action_code','=',5)
	    		->where('frm_location',$locationId)
	    		->where('is_processed','=',0) //only cancelled but not confirmed 
	    		->where('is_sto','=',0)
	    		->orderBy('document_no','desc')->select('document_no','location_name as destination_loc')->get();

    		}
    		elseif($shipment_no!='' || $sto_number!=''){
	    		$getDelivery = DB::table('delivery_master as dm')
	    		//->join('master_lookup as ml','ml.value','=','dm.type')
	    		->join('locations as l','l.location_id','=','dm.to_location')
	    		->where('shipment_no',$shipment_no)
	    		->orWhere('sto_no',$sto_number)
	    		->where('frm_location',$locationId)
	    		->where('is_processed','=',0)
	    		->where('is_sto','=',0)
	    		->where('action_code','=',1)
	    		->orderBy('document_no','desc')->select('document_no','location_name as destination_loc')->get();
    		}

    		elseif($sto_number=='' and $shipment_no=='' and $send_cancelled_deliveries=='')
    		{
    			$getDelivery = DB::table('delivery_master as dm')
	    		//->join('master_lookup as ml','ml.value','=','dm.type')
	    		->join('locations as l','l.location_id','=','dm.to_location')
	    		
	    		->where('frm_location',$locationId)
	    		->where('is_processed','=',0)
	    		->where('is_sto','=',0)

	    		->orderBy('document_no','desc')->select('document_no','location_name as destination_loc')->get();
    		}
    			
    	// echo (count($getDelivery));exit;		
    		
    		
// print_r($getDelivery);exit;
    	}
             
        if(count($getDelivery)==0)
        	throw new Exception("No deliveries found against to location", 1);
        	
       $message = 'Delivery Data Retrieved Successfully';
       
    }
    catch(Exception $e){
    	$status =0;
    	$message =$e->getMessage();    	
    }
   return json_encode(['Status'=>$status,'Message'=>$message,'Data'=>$getDelivery]);

   }


    public function getDeliveryData(){

    try{
    	$status =1;
    	$inputData=$this->_request->all();
    	$mfgId = $this->roleAccess->getMfgIdByToken($inputData['access_token']);
    	$locationId = $this->roleAccess->getLocIdByToken($inputData['access_token']);
    	$module_id = $inputData['module_id'];
    	$getUserId = DB::table('users_token')->where('access_token',$inputData['access_token'])->where('module_id',$module_id)->pluck('user_id');
    	 $shipment_no=$this->_request->input('shipment_no');
    	 $sto_number=$this->_request->input('sto_number');
    	 $shipment_no=$this->_request->input('shipment_no');
    	 $send_cancelled_deliveries=$this->_request->input('send_cancelled_deliveries');
        $getDelivery=array();
        if(!$locationId){
              throw new Exception('Location doesnt exists');
        }
    	if($locationId){
    		if($send_cancelled_deliveries==1){
    			$getDelivery = DB::table('delivery_master as dm')
	    		->join('locations as l','l.location_id','=','dm.to_location')
	    		->where('action_code','=',5)
	    		->where('frm_location',$locationId)
	    		->where('is_processed','=',0) //only cancelled but not confirmed 
	    		->where('is_sto','=',0)
	    		->orderBy('document_no','desc')->select('document_no','location_name as destination_loc')->get();

    		}
    		elseif($shipment_no!=''){
	    		$getDelivery = DB::table('delivery_master as dm')
	    		->join('locations as l','l.location_id','=','dm.to_location')
	    		->where('shipment_no',$shipment_no)
	    		->where('frm_location',$locationId)
	    		->where('is_processed','=',0)
	    		->where('is_sto','=',0)
	    		->where('action_code','<=',4)
	    		->orderBy('document_no','desc')->select('document_no','location_name as destination_loc')->get();
    		}elseif($sto_number!=''){

	    		$getDelivery = DB::table('delivery_master as dm')
	    		->join('locations as l','l.location_id','=','dm.to_location')
	    		->where('sto_no',$sto_number)
	    		->where('frm_location',$locationId)
	    		->where('is_processed','=',0) 
	    		->where('is_sto','=',0)
	    		->where('action_code','<=',4)
	    		->orderBy('document_no','desc')->select('document_no','location_name as destination_loc')->get();
    		}

    		elseif($sto_number=='' and $shipment_no=='' and $send_cancelled_deliveries=='')
    		{
    			$getDelivery = DB::table('delivery_master as dm')
	    		->join('locations as l','l.location_id','=','dm.to_location')
	    		->where('frm_location',$locationId)
	    		->where('is_processed','=',0)
	    		->where('is_sto','=',0)
	    		->orderBy('document_no','desc')->select('document_no','location_name as destination_loc')->get();
    		}
    	}
             
        if(count($getDelivery)==0)
        	throw new Exception("No deliveries found against given input", 1);
        	
       $message = 'Delivery Data Retrieved Successfully';
       
    }
    catch(Exception $e){
    	$status =0;
    	$message =$e->getMessage();    	
    }
   return json_encode(['Status'=>$status,'Message'=>$message,'Data'=>$getDelivery]);

   }	

   public function getLabelTemplates(){
		try{
			Log::info(__FUNCTION__.' : '.print_r($this->_request->all(),true));
			$data = array();
			$group_id = trim($this->_request->input('group_id'));
			$mfg_id = $this->roleAccess->getMfgIdByToken($this->_request->input('access_token'));
			$loc_type_id = $this->roleAccess->getLocTypeByAccessToken($this->_request->input('access_token'));
			if(!$loc_type_id){
			   throw new Exception('Location Type doesnt exist');
			}
			$tran_ids = DB::table('transaction_master')
								->where('manufacturer_id',$mfg_id)
								->pluck('id');
								

			$query = DB::table('label_master as lm')
					  ->whereIn('transaction_id',$tran_ids)
					  ->where('lm.manufacturer_id',$mfg_id);
			if($group_id){
			$data = $query->where('lm.group_id',$group_id)
						  ->join('transaction_master as tm','tm.id','=','lm.transaction_id')
						  ->join('product_groups as pg','pg.group_id','=','lm.group_id')
						  ->get(['lm.name as template_name','lm.template','tm.id as transition_id','tm.name as transition_name','protocol','lm.group_id','pg.name as group_name','sort_order','lm.dpi','lm.labelcategory','lm.noOfColumns'])->toArray();
			}   
			else{
			$data = $query->join('transaction_master as tm','tm.id','=','lm.transaction_id')
						  ->join('product_groups as pg','pg.group_id','=','lm.group_id')
						  ->get(['lm.name as template_name','lm.template','tm.id as transition_id','tm.name as transition_name','protocol','lm.group_id','pg.name as group_name','sort_order','lm.dpi','lm.labelCategory','lm.noOfColumns'])->toArray();
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
		Log::info(['Status'=>$status,'Message'=>'S-: '.$message,'Data'=>$data]);
		return json_encode(['Status'=>$status,'Message'=>'S-: '.$message,'Data'=>$data]);
	}

	
	public function ReceiveByTp_test(){

	$startTime = $this->getTime();
	try{
		$status = 0;
		$message = '';
		$data_request=$this->_request->all();
		$tp = $this->_request->input('tp');
		$locationId = $this->_request->input('location_id');		
		$transitionTime = $this->getDate();
		$transitionId = $this->_request->input('transition_id');
		$previousTrackId = '';
		$tpArr = explode(',', $tp);
		$missingIds = $this->_request->input('missing_ids');
		$transitIds = $this->_request->input('damage_ids');
		$excess_ids = $this->_request->input('excess_ids');
   		$deliveryNo = $this->_request->input('delivery_no');
   		//new field for GRN//

   		$stn_no = $this->_request->input('stn_no');

   		///
   		$materialBatches = $this->_request->input('materialBatches');
   		$store_location = trim($this->_request->input('store_location'));
		$documentNo = array();
		// echo $stn_no;exit;

		$deliveryNoExists = FALSE;
		$purchaseNoExists = FALSE;
		$subcontractNoExists = FALSE;
		$esealdocNoExists = FALSE;
		$parkGRN = FALSE;
		$xml = Array();
		$isPostGrn=false;
		$locationObj = new Location();
		$mfgId = $locationObj->getMfgIdForLocationId($locationId);
		Log::info('recive by recive by recive by');
		Log::info(__FUNCTION__.'==>'.print_r($this->_request->all(),true));
		$movementType=0;
		///GET MfgId for geiven Location

		DB::beginTransaction();
		////SAP Delivery No associated with TP
		
	/* commented by ruchita 24/10 */
		if(!empty($stn_no)){
		//if(!empty($deliveryNo)){
			$delivery_attribute_id= DB::table($this->attributeTable)->where('attribute_code','document_no')->value('attribute_id');
			$purchase_attribute_id= DB::table($this->attributeTable)->where('attribute_code','purchase_no')->value('attribute_id');
			$esealdoc_attribute_id= DB::table($this->attributeTable)->where('attribute_code','eseal_document_no')->value('attribute_id');
			$park_grn_id = DB::table($this->attributeTable)->where('attribute_code','park_grn_no')->value('attribute_id');
			$parkGrnExists = DB::table('tp_attributes')->whereIn('tp_id', $tpArr)->where(['attribute_id'=>$park_grn_id])->value('value');
			//$deliveryNoCnt = DB::table('tp_attributes')->whereIn('tp_id', $tpArr)->where(['attribute_id'=>$delivery_attribute_id,'value'=>$deliveryNo])->count();
			$tp_deliveries=DB::table('tp_attributes')->whereIn('tp_id', $tpArr)->where(['attribute_id'=>$delivery_attribute_id])->pluck('value')->toArray();
			/*$tp_deliveries=DB::table('tp_attributes')->whereIn('tp_id', $tpArr)->Where(['attribute_id'=>$esealdoc_attribute_id])->pluck('value')->toArray();
			$tp_deliveries_separated=implode(',',$tp_deliveries);*/
			 //print_r($tp_deliveries);exit;
			if(!$stn_no){
				$purchaseNoCnt = DB::table('tp_attributes')->whereIn('tp_id', $tpArr)->where(['attribute_id'=>$purchase_attribute_id])->count();
				  if(!$purchaseNoCnt){
				  	$esealdocNoCnt = DB::table('tp_attributes')->whereIn('tp_id', $tpArr)->where(['attribute_id'=>$esealdoc_attribute_id])->count();
				      if(!$esealdocNoCnt){
				         // throw new Exception('Given delivery no not exists for passed tp id');
				          throw new Exception('Given TP is not assigned any Delivery/PO number');
				      }
				      else{
				      	$esealdocNoExists = TRUE;
				      }
				  }
				  else{
				  	$purchaseNoExists = TRUE;				  	
				  	if($deliveryNoCnt){
				  		throw new Exception("For the Delivery not possible for Park and Post GRN");
				  	}
				  	$POGRN=DB::table('transaction_master')->where('manufacturer_id',$mfgId)->where('id',$transitionId)->value('action_code');				  
				  	//if($parkGrnExists != 'unknown' && $POGRN=='POGRN'){
				  	if($POGRN=='POGRN'){
						$movementType = 105;
						$isPostGrn=true;
						$checkTP = DB::table('tp_attributes')->where('tp_id',$tp)->where('attribute_id',$park_grn_id)->value('tp_id');
						if($checkTP){
							$value = DB::table('tp_attributes')->where('tp_id',$tp)->where('attribute_id',$park_grn_id)->where('value','<>','unknown')->count();
							if($value==0)
							throw new Exception('Please do park GRN against TP');
						}

				  	}  else if($POGRN=='PGRN'){
						$movementType = 103;
						$checkTP = DB::table('tp_attributes')->where('tp_id',$tp)->where('attribute_id',$park_grn_id)->value('tp_id');
						if($checkTP){
							$value = DB::table('tp_attributes')->where('tp_id',$tp)->where('attribute_id',$park_grn_id)->where('value','<>','unknown')->count();
							if($value>0)
							throw new Exception('Park GRN completed against TP');
						}
						$isPostGrn=false;
				  	} else if($POGRN=='GRN') {
				  		$movementType = 101;
				  		$isPostGrn=false;
				  	}else {
				  		 throw new Exception('tail mismatch error');
				  	}
				  }
			}else{
				$deliveryNoExists = TRUE;
			}
		}else{
			throw new Exception("Please Enter STN Number!");
			
		}
	/*till here 		
/*
		echo "Test";
		echo "<br>movementType:".$movementType;
		echo "<br>parkGrnExists:".$parkGrnExists;
		echo "<br>esealdocNoExists:".$esealdocNoExists;
		echo "<br>deliveryNoExists:".$deliveryNoExists;
exit;
	*/

		if(!$mfgId)
			throw new Exception('In-valid location');

			$esealTable = 'eseal_'.$mfgId;
			$transactionObj = new Transaction();
			$transactionDetails = $transactionObj->getTransactionDetails($mfgId, $transitionId);
			if(!count($transactionDetails)){
				throw new Exception('Transition details not found');
			}
			Log::info(print_r($transactionDetails, true));

			$srcLocationAction = $transactionDetails[0]->srcLoc_action;
			$destLocationAction = $transactionDetails[0]->dstLoc_action;
			$inTransitAction = $transactionDetails[0]->intrn_action;
			
			if(!($srcLocationAction==0 && $destLocationAction==1 && $inTransitAction==-1) && !($srcLocationAction==-1 && $destLocationAction==0 && $inTransitAction==1))
			 		throw new Exception('The given transition ID is not allowed');

				$tpTrackIDs = Array();
				//echo $this->trackHistoryTable; exit;
				foreach($tpArr as $tp){
					try{
						$res = DB::table($this->trackHistoryTable)->where('tp_id', $tp)->orderBy('update_time','desc')->take(1)->get()->toArray();
						// print_r($res);exit;
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
				log::info($tpArr);
                 log::info("tptrackids");
				Log::info($tpTrackIDs[$tp]);
				try{
					log::info("first_try");
					foreach($tpArr as $tp){
						log::info("inner foreach first");
						$destLocationId =0;
						$tpDetails = DB::table($this->trackHistoryTable)->where('tp_id', $tp)->get(['src_loc_id','dest_loc_id']);
						log::info($tpDetails);
						$srcLocationId = $tpDetails[0]->src_loc_id;
						//dd($srcLocationId);
						log::info($srcLocationId);
						// if(($srcLocationAction==0 && $destLocationAction==1 && $inTransitAction==-1) && $purchaseNoExists && $movementType == 105){
						// 	throw new Exception('Please proceed for Park GRN');
					 //    }
					    log::info("parkgrnends");

                         log::info("movementtype103/105_starts");

						if(($srcLocationAction==0 && $destLocationAction==1 && $inTransitAction==-1) && $purchaseNoExists){
							//echo "Test movementtype103_starts"; exit;							
							if($isPostGrn){ 
							$movementType = 105;
							$destLocationId=0;
							$srcLocationId = $tpDetails[0]->dest_loc_id;
                            }
							else { 
							$parkGRN = true; 
							$movementType = 103; 
						    $locationId = $tpDetails[0]->src_loc_id;
						    $destLocationId = $tpDetails[0]->dest_loc_id;
						  }
						}
				

						$lastInsertId = DB::table($this->trackHistoryTable)->insertGetId(Array(
							'src_loc_id'=>$locationId,
							'dest_loc_id'=> $destLocationId,
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
						$transitionId = DB::table('transaction_master')->where(['manufacturer_id'=>$mfgId,'name'=>'Damaged'])->value('id');
						if(!$transitionId)
							throw new Exception('Transaction : Damage not created');

						$request = Request::create('scoapi/UpdateTracking', 'POST', array('module_id'=>$this->_request->input('module_id'),'access_token'=>$this->_request->input('access_token'),'codes'=>$transitIds,'srcLocationId'=>$locationId,'destLocationId'=>0,'transitionTime'=>$transitionTime,'transitionId'=>$transitionId,'internalTransfer'=>0));
						$originalInput = Request::input();//backup original input
						Request::replace($request->input());						
						$response = Route::dispatch($request)->getContent();
						$response = json_decode($response,true);
						if($response['Status'] == 0)
							throw new Exception($response['Message']);

					}
					if(!empty($missingIds)){
						Log::info('Execution in MISSING:');
						$transitionId = DB::table('transaction_master')->where(['manufacturer_id'=>$mfgId,'name'=>'Missing'])->value('id');
						if(!$transitionId)
							throw new Exception('Transaction : Missing not created');


						$request = Request::create('scoapi/UpdateTracking', 'POST', array('module_id'=>$this->_request->input('module_id'),'access_token'=>$this->_request->input('access_token'),'codes'=>$missingIds,'srcLocationId'=>$locationId,'destLocationId'=>0,'transitionTime'=>$transitionTime,'transitionId'=>$transitionId,'internalTransfer'=>0));
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
				/*$status = 1;
				$message = 'TP received succesfully';
				*/
				
               //if($deliveryNoExists || $purchaseNoExists){
				DB::table('partial_transactions')->where('tp_id',$tp)->delete();

                    $vehicleId = DB::table($this->attributeTable)->where('attribute_code','vehicle_no')->value('attribute_id');
                    $invoiceId = DB::table($this->attributeTable)->where('attribute_code','docket_no')->value('attribute_id');

                    $XML_DYNAMIC ='';

				    $vehicleNo = DB::table('tp_attributes')->whereIn('tp_id', $tpArr)->where('attribute_id',$vehicleId)->value('value');

                    $invoiceNo = DB::table('tp_attributes')->whereIn('tp_id', $tpArr)->where('attribute_id',$invoiceId)->value('value');

					if(empty($vehicleNo))
						$vehicleNo ='';
					if(empty($invoiceNo))
						$invoiceNo ='';
						
                    $XML_DYNAMIC  =  $vehicleNo.','.$invoiceNo;

				/*if($deliveryNoExists){

					$data=DB::table($esealTable.' as e')->join('products as p','p.product_id','=','e.pid')->where('track_id', $lastInsertId)->groupBy('e.batch_no')->groupBy('e.pid')->get([DB::raw('count(eseal_id) as cnt'),DB::raw('sum(pkg_qty) as qty'),'pid','batch_no','p.material_code']);

					$eccItemData=[];
	$sto=DB::table('delivery_master')->where('document_no',$deliveryNo)->value('sto_no');*/
					/*foreach ($data as $key => $value) {
						
						$temp=[];
						$temp['material']=$value->material_code;
						$temp['batch']=$value->batch_no;
						// $temp['batch']='01C4054000';
						$temp['quantity']=$value->qty;
						// $temp['quantity']=1080;
						$temp['stock_type']='';
						$temp['date_of_mfg']='';	
						$temp['sku_code']='';
						$temp['price_lot']='';	
						$eccItemData[]=$temp;
					}*/

					//echo "<pre/>";print_r($tp_deliveries);exit;
//echo "<pre/>";print_r($tp_deliveries);exit;
						$tp_deliveries1=DB::table('tp_attributes')->whereIn('tp_id', $tpArr)->where(['attribute_id'=>$delivery_attribute_id])->get(['value']);
						$temp=array();
					foreach($tp_deliveries as $key => $tp_del){
						
						$temp[]['obd_number'] = $tp_del;
						
					}

		$eccItemnew = $temp;
		//print_r($temp);exit;
	//$body=array('headerData'=>array('obd_number'=>$deliveryNo,'po_sto_number'=>$sto),'it0emData'=>$eccItemData);
	//$body=array('headerData'=>array('STN_number'=>$stn_no),'itemData'=>$tp_deliveries);
	$body1=array('headerData'=>array('stn_number'=>$stn_no),'itemData'=>($eccItemnew));
	//$body = json_encode($body1);
	 //$body = '{"headerData":{"STN_number":"2019300424"},"itemData":[{"obd_number":"5736409406"}]}';
 //print_r($body);


	$method='grProcess';
	$this->erp=new ConnectErp($mfgId);
	$result=$this->erp->request($method,'',$body1,'POST');
 //print_r($result);exit;
	$result=json_decode($result);
	$failedTP=array();
	$SuccessTP=array();
		if($result->status!=0){
			foreach($result->itemData as $key){
				// print_r($key);exit;
/*
				if($key->status!=0 ||$key->status!='')
				{
				foreach($tpArr as $tp ){
				DB::table('tp_attributes')->where('value',$key->obd_number)->where('tp_id',$tp)->where('attribute_name','Document Number')->update(['reference_value'=>$key->grn_doc_number]);

				}
				DB::commit();
*/
if($key->status == 1 )
                {
                foreach($tpArr as $tp ){
                    DB::table('tp_attributes')->where('value',$key->obd_number)->where('tp_id',$tp)->where('attribute_name','Document Number')->update(['reference_value'=>$key->grn_doc_number]);
                    DB::table('delivery_master')->where('document_no',$key->obd_number)->update(['reference_value'=>$result->stn_number]);

 

//                        echo "loop in";
                    DB::commit();
                }
					/*$SuccessTP['TP']=$tp;
					$SuccessTP['obd_number']=$key->obd_number;
					$SuccessTP['message']=$key->message;*/
				$status = 1;
				$message = 'TP received succesfully SAP message-'.$key->message;
				}
				
				else {
					DB::rollback();
				$status = 0;
				$message = 'TP Failed , reason E-'.$key->message;
					/*$failedTP['obd_number']=$key->obd_number;
					$failedTP['message']=$key->message;*/

				}
				
				/*print_r($SuccessTP);
				print_r($failedTP);exit;*/
				
			}
			/*DB::table('tp_attributes')->where('value',$deliveryNo)->where('tp_id',$tp)->where('attribute_name','Document Number')->update(['reference_value'=>$result->data->material_doc]);*/
		} else {
				echo "<pre>";
	/*print_r($body);
	print_r($result);*/

			throw new Exception('E-'.$result->message, 1);		
		}
	// }

	//DB::commit();			
		
	}catch(Exception $e){
		$status =0;
		DB::rollback();
		Log::info($e->getMessage());
		$message = $e->getMessage();

	}
	$endTime = $this->getTime();
	Log::info(__FUNCTION__.' Finishes execution in '.($endTime-$startTime));
	Log::info(Array('Status'=>$status, 'Message'=>$message, 'documentNo' =>$documentNo));
	return json_encode(Array('Status'=>$status, 'Message' =>'S-: '.$message));
/*	return json_encode(Array('Status'=>$status, 'Message' =>'S-: '.$message, 'documentNo' =>$documentNo));
*/
}


public function GetTPDetailsByIdWince(){
	$startTime = $this->getTime();
	try{
		$status = 0;
		$message = '';
		$finalArray = array();
		$flag =0;
		Log::info(__FUNCTION__.' === '. print_r($this->_request->input(),true));
		$locationId = trim($this->_request->input('srcLocationId'));
		$tpList = trim($this->_request->input('tpIds'));
		$tpArrar=explode(",",$tpList);
		$delivery_no = trim($this->_request->input('delivery_no'));
		if(empty($tpList)){
			if(empty($delivery_no)){
				throw new Exception('Either TP or Delivery Number must be passed.');
			}
   			else {
			$tpList = DB::table('tp_attributes')
						->select('tp_id')	
						->where(['attribute_name'=>'Document Number','value'=>$delivery_no])
						->get();
				if(count($tpList)==0){
				  throw new Exception('There is no TP associated with the given Delivery Number'); 
				}
			}			

			/*if(!$tpList){
				$tpList = DB::table('tp_attributes')
						->where(['attribute_name'=>'Document Number','value'=>$delivery_no])
						->value('tp_id');
				if(count($tpList)==0)
				  throw new Exception('There is no TP associated with the given Delivery Number');            		
             } 
             if(!$tpList){
				$tpList = DB::table('tp_attributes')
						->where(['attribute_name'=>'Eseal Document Number','value'=>$delivery_no])
						->value('tp_id');
				if(!$tpList)
				  throw new Exception('There is no TP associated with the given Delivery Number');            		
             } */

             $tpIds = $tpList; 
             //print_r($tpIds);  
				
		}
		else{
		 $tpIds = $tpArrar;	
		}
		
		$dataArray = array();

		$locationObj = new Location();
		if(!is_numeric($locationId) || empty($locationId))
			throw new Exception('Location params is missing');

		$mfgId = $locationObj->getMfgIdForLocationId($locationId);
		
		$finalArray = Array();
		$esealDataArray = Array();
		$highestLevelIds = Array();


		if($mfgId){
			$tpIds = json_encode($tpIds);
			$tpIds = json_decode($tpIds,true);
			//print_r($tpIds); exit;
			if($delivery_no)
			{
				//echo 'sdfsf';
				$tpIds = array_values($tpIds[0]);
			}
			//print_r($tpIds); exit;
			$esealTable = 'eseal_'.$mfgId;
			foreach($tpIds as $tps){
				//print_r($tpIds);exit;
				$tpcnt = DB::table($esealTable.' as eseal')->join($this->trackHistoryTable.' as th', 'eseal.track_id', '=', 'th.track_id')
				  ->where('th.tp_id','=', $tps)->count();
				  //print_r( $tps);exit;
			Log::info($tpcnt);
			if($tpcnt){
				$flag =1;
				$trackHistoryData = DB::table($this->trackHistoryTable)->where('tp_id',$tps)->orderBy('update_time')->take(1)->get();
				//$trackHistoryData = json_encode($trackHistoryData);
			//$trackHistoryData = json_decode($trackHistoryData,true);
//print_r($trackHistoryData);
			//echo count($trackHistoryData);exit;
				if(count($trackHistoryData)){
					$srcLocName = DB::table($this->locationsTable)->where('location_id', $trackHistoryData[0]->src_loc_id)->select('location_name')->get();

					$destLocName = DB::table($this->locationsTable)->where('location_id', $trackHistoryData[0]->dest_loc_id)->select('location_name')->get();
					$transitInfo= array(
									'ID' => $tps, 'Status' => 1, 
									'source' => $srcLocName[0]->location_name, 'destination' => $destLocName[0]->location_name, 
									'source_id' => $trackHistoryData[0]->src_loc_id, 'destination_id'=> $trackHistoryData[0]->dest_loc_id 
							);

					$tpAttributes = DB::table('tp_attributes')->where('tp_id',$tps)->get(['attribute_name','value']);

					//print_r($tpAttributes);exit;
				 $sql =  'SELECT 
					 p.name as Name,
					 p.material_code AS MatCode,					
					 e.primary_id AS HId, CAST((
					 pkg_qty) AS UNSIGNED) AS Qty
					 FROM '.$esealTable.' e
					 INNER JOIN products p ON e.pid=p.product_id
					 INNER JOIN tp_data tp ON tp.level_ids=e.primary_id
					 WHERE	p.product_type_id=8003 AND tp.tp_id='.$tps.' AND e.level_id IN(0,1,2) group by e.primary_id';

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
//print_r($trackHistoryData);exit;
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
	return json_encode(['Status'=>$status,'Message' =>'S-: '.$message,'Data' => $finalArray]);
}

public function scrapEseals(){

 		try{
 			
 			DB::beginTransaction();          
 			Log::info(__FUNCTION__.' : '.print_r($this->_request->input(),true));
 			$status =1; 			
 			$message = 'Iot\'s scrapped successfully';
   			$ids = trim($this->_request->input('ids'));
  			$mfgId = $this->roleAccess->getMfgIdByToken($this->_request->input('access_token'));
  		//	Log::info('mfgId'.$mfgId);
			$srcLocationId = $this->roleAccess->getLocIdByToken($this->_request->input('access_token'));    			  
			$transitionTime = $this->getDate();
			$transitionId = trim($this->_request->input('transitionId'));			
            $deScrap = trim($this->_request->input('deScrap'));	
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

   			 $transitionId = DB::table('transaction_master')->where(array('name'=>'De Scraping','manufacturer_id'=>$mfgId))->value('id');
			}

            //////Convert IDS into string and array
		     $explodeIds = explode(',', $ids);
		     $explodeIds = array_unique($explodeIds);
		//print_r($explodeIds);exit;
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
   			                ->get('primary_id')->toArray();

				if(empty($validIOTs) && $deScrap==0)
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
					//print_r($invalidIOT);exit;
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

             $transactionObj = new Transaction();
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
	   
	   
		$trakHistoryObj = new TrackHistory();
		try{
			$lastInrtId = DB::table($this->trackHistoryTable)->insertGetId( Array(
				'src_loc_id'=>$srcLocationId, 'dest_loc_id'=>0, 
				'transition_id'=>$transitionId,'update_time'=>$transitionTime));
			//Log::info($lastInrtId);

			$maxLevelId = 	DB::table($esealTable)
								->whereIn('parent_id', $explodeIds)
								->orWhereIn('primary_id', $explodeIds)->max('level_id');

            //Component Trackupdating
			//print_R($explodeIds); exit;					
			$res = DB::table($esealTable)->where('level_id', 0)
							->where(function($query) use($explodeIds){
								$query->whereIn('primary_id',$explodeIds);
								$query->orWhereIn('parent_id',$explodeIds);
							})->pluck('primary_id');
			//print_r($res);exit;
								
			if(!empty($res)){
				
				$attributeMaps =  DB::table('bind_history')->whereIn('eseal_id',$res)->distinct()->pluck('attribute_map_id');
				//echo($attributeMaps); exit;
				$componentIds =  DB::table('attribute_mapping')->whereIn('attribute_map_id',$attributeMaps)->where('attribute_name','Stator')->get(['value'])->toArray();
				//print_r($componentIds);exit;
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

public function grnCanTOCreation(){
	$input = $this->_request->all();
	$status=0;
	$message="GRN-Cancellation PO Creation Failed";
	$insert=[];
	$params = (array) json_decode(file_get_contents('php://input'), TRUE);
	if(!array_key_exists('items', $params))
	{
		$message = 'JSON format is incorrct';
		return json_encode(Array('Status'=>$status, 'Message' =>'S-: '.$message));
	}
	
	foreach ($params['items'] as $key => $value) {
		$value['gr_document_no'] = $params['cancelled_gr_doc_no'];
		$value['to_number'] = $params['to_number'];
		$value['warehouse_no'] = $params['warehouse_no'];
		$value['product_id']=DB::table('products as p')->where('p.material_code',$value['material_code'])->value('product_id');
		$insert[]=$value;

	}
	
	$insertStatus = DB::table('grn_to_creation_queue')->insert($insert);
	if($insertStatus){
		$status=1;
		$message="GRN-Cancellation PO Creation done succesfully";
	}
	return json_encode(Array('Status'=>$status, 'Message' =>'S-: '.$message));
}

public function grnCanToConfirmation(){
	$grnNumber = $this->_request->input('grn_number');
	$access_tokne = $this->_request->input('access_token');
	$moduleId = $this->_request->input('module_id');
	$mfgId = $this->roleAccess->getMfgIdByToken($access_tokne);

	if (empty($grnNumber)){
		return json_encode(array('Status' => 0, 'Message' => 'S- : Please Enter GRN Number', 'Data' => array()));
	}

	$grnToCreationData = DB::table('grn_to_creation_queue')
						->where('gr_document_no', $grnNumber)
						->get()->toArray();

	if(empty($grnToCreationData)){
		return json_encode(array('Status' => 0, 'Message' => 'S- : Confirmation is not possible as TO for GRN  is not yet Created', 'Data' => array()));
	}

	$requestData = array();
	$itemData = array();
	$itemsData = [];
	foreach ($grnToCreationData as $key => $value) {
		$itemData = array(
					'Tapos'		=>	$value->to_line_item,
					'Nista'		=>	'',
					'Matnr'		=>	$value->material_code,
					'Charg'		=>	$value->batch,
					'Bestq'		=>	'',
					'Vltyp'		=>	$value->source_storage_type,
					'Vlber'		=>	$value->source_storage_section,
					'Vlpla'		=>	$value->source_bin,
					'Nltyp'		=>	$value->destination_storage_type,
					'Nlber'		=>	$value->destination_storage_section,
					'Nlpla'		=>	$value->destination_bin);
		$itemsData[] = $itemData;
	}
	$headerData = array(
			'Mblnr'		=>		$grnNumber,
			'Tanum'		=>		$grnToCreationData[0]->to_number,
			'Lgnum'		=>		$grnToCreationData[0]->warehouse_no);
	$body = array(
		'Yh3mmEsealCangrnConfrmTo'		=>		array(
			'IHeader'		=>		$headerData,
			'TTodata'		=>		array('item'		=> $itemsData)));
	$method = 'toConfirmationForGRNCancellation';
	$methodType = 'POST';
	$this->erp=new ConnectErp($mfgId);
	$result=$this->erp->request($method,'',$body,$methodType);
	// return $result;
	$result=json_decode($result);

	if(empty($result)){
		return json_encode(array('Status' => 0, 'Message' => 'S- : Please try after sometime', 'Data' => array()));
	}

	if($result->Yh3mmEsealCangrnConfrmToResponse->EStatus == 0){
		$message = $result->Yh3mmEsealCangrnConfrmToResponse->EMessage;
		$data = $result->Yh3mmEsealCangrnConfrmToResponse->TTodata;
		return json_encode(array('Status' => 0, 'Message' => 'S- : '.$message, 'Data' => $data));
	}

	if($result->Yh3mmEsealCangrnConfrmToResponse->EStatus == 1){
		$message = $result->Yh3mmEsealCangrnConfrmToResponse->EMessage;
		$data = $result->Yh3mmEsealCangrnConfrmToResponse->TTodata;
		DB::table('grn_to_creation_queue')->where('gr_document_no', $grnNumber)->update(['status'	=> 1]);
		return json_encode(array('Status' => 1, 'Message' => 'S- : '.$message, 'Data' => $data));
	}
}
function grnCreation(){

		$output = array('Status' => 0, 'Message' => '', 'Data' => array());
		$access_tokne = $this->_request->input('access_token');
		$moduleId = $this->_request->input('module_id');
		$mfgId = $this->roleAccess->getMfgIdByToken($access_tokne);
		$esealTable = 'eseal_'.$mfgId;
		$esealBankTable = 'eseal_bank_'.$mfgId;
		$locationId = $this->roleAccess->getLocIdByToken($this->_request->input('access_token'));

		$docNumber = $this->_request->input('doc_number');
		$poFlag = $this->_request->input('is_po_number');
		$postingdate = $this->_request->input('posting_date');
		$docdate = $this->_request->input('doc_date');
		$deliverNote = $this->_request->input('delivery_note');
		$billLading = $this->_request->input('bill_landing');

		if(is_null($poFlag)){
			$output['Message'] = 'S-: mandatory field Is PO Number';
			return json_encode($output);
		}

		if(empty($docNumber)){
			$output['Message'] = 'S-: mandatory field document Number';
			return json_encode($output);
		}
		//$params = $this->_request->input('params');
		if(empty($docdate)){
			$output['Message'] = 'S-: mandatory field document date';
			return json_encode($output);
		}

		Log::info('Params '.$this->_request->input('params'));

		$params = (array)json_decode($this->_request->input('params'), true);
		if(!array_key_exists('itemData', $params))
		{
			$message = 'JSON format is incorrct';
			return json_encode(Array('Status'=> 0, 'Message' =>'S-: '.$message));
		}

		$scanData = [];
		$esealId = [];
		$esealIdQty = 0;
		$esealIDQty = [];

		$indicatorFlag = ($poFlag == true) ? 'P' : 'I';
		$headerData = array(
			'Flag'			=> $indicatorFlag,
			'DocDate'		=> $docdate,
			'PostDate'		=> $postingdate,
			'DeliveryNote'	=> $deliverNote,
			'BillLading' 	=> $billLading);
		$itemData = [];
		foreach ($params['itemData'] as $key => $value) {
			foreach ($value['scandata'] as $svalue) {
					$esealId[] = $svalue['esealId'];
					$esealIdQty += $svalue['qty'];
					$esealIDQty[] = $svalue['qty'];
				}
			$sesealId = implode(',', $esealId);
			$explodeIds = explode(',', $sesealId);
			$explodeIds = array_unique($esealId);
			
			$tpCount = DB::table($esealTable.' as eseal')
						->leftjoin($this->trackHistoryTable.' as th', 'eseal.track_id', '=', 'th.track_id')
						->whereIn('primary_id', $explodeIds)
						->distinct()
						->select('eseal.track_id')
						->get();
			Log::info(count($tpCount));
			if(count($tpCount)){
				$output['Message'] = 'S-: Some of the codes are already assigned';
				return json_encode($output);
			}

			// Validate IOTs
			$esealBankData = DB::table($esealBankTable)
							->whereIn('id', $explodeIds)
							->where('issue_status', '0')
							->get()->toArray();

			if(empty($esealBankData)){
				return json_encode(Array('Status'=> 0, 'Message' =>'S-: IOTs are not available in eSeal'));
				$output['Message'] = 'S-: IOTs are not available in eSeal';
				return $output;
			}
			
			if (count($explodeIds) != count($esealBankData))
			{
				return json_encode(Array('Status'=> 0, 'Message' =>'S-: Some of the codes are not available in eSeal'));
				$output['Message'] = 'S-: Some of the codes are not available in eSeal';
				return $output;
			}
			LOG::info('Total Qty :- '.$esealIdQty);
			$value['material'] = ltrim($value['material'], '0');
			$pid = DB::table('products')->where('material_code', $value['material'])->value('product_id');
			$convt=new Conversions();
			$grnQty = $convt->getUom($pid, $esealIdQty, 'EA', $value['uom']);

			$skuInfo = DB::table('sku_info')->where('product_id', $pid)->first();
	
			$plantId = DB::table('locations')->where('location_id',$locationId)->value('erp_code');
			if(!$plantId){
				return json_encode(Array('Status'=> 0, 'Message' =>'S-: Storage Location is not available in eSeal'));
				$output['Message'] = 'S-: Storage Location is not available in eSeal';
				return $output;
			}
			$poNumber = ($poFlag == true) ? $docNumber : '';
			$idbNumber = ($poFlag == true) ? '' : $docNumber;
			$poItem = ($poFlag == true) ? $value['line_item'] : '';
			$idbItem = ($poFlag == true) ? '' : $value['line_item'];
			$material = $value['material'];
			$storageLoction = $value['storage_location'];
			$stockType = (isset($value['stock_type']) == true) ? $value['stock_type'] : '';
			$grnQty = $grnQty; //(int)$value['grn_quantity'];
			$uom = $value['uom'];
			$skuCode  = $value['sku_code']; // $skuInfo->sku_number;
			$caseConfig = $value['case_config']; // $skuInfo->case_config;
			$date = date('d.m.Y');
			$mfgDate = $value['date_of_mfg'];
			$priceLot = $value['price_lot'];
			$itemData[] = array(
				'Ponumber'		=> $poNumber,
				'Poitem'		=> $poItem,
				'Ibdnumber'		=> $idbNumber,
				'Ibditem'		=> $idbItem,
				'Material'		=> $material,
				'StorageLoc'	=> strtoupper($storageLoction),//$plantId, //$storageLoction,
				'GrnQty'		=> $esealIdQty, //$grnQty,
				'StockType'		=> $stockType,
				'Uom'			=> $uom,
				'Skucode'		=> $skuCode,
				'Caseconfig'	=> $caseConfig,
				'PriceLot'		=> $priceLot,
				'MfgDate'		=> $mfgDate);
		$esealIdQty = 0; 
		$esealId = [];
		}
		
		$body= array(
			'Yh3mmEsealGrnCreation'	=>	array(
				'IHeader'	=> $headerData,
				'TItem'		=> array('item'		=> $itemData))
			);
		
		Log::info($body);
		$method = 'grnCreation';
		$methodType = 'POST';
		$str = json_encode($body);
		$this->erp=new ConnectErp($mfgId);
		$result=$this->erp->request($method,'',$body,$methodType);
		// return $result;
		$result=json_decode($result);
		$output['Data'] = $result;	
		// return json_encode($output);

		if (empty($result)){
			return json_encode(Array('Status'=> 0, 'Message' =>'S-: Unable to ConnectErp'));
		}
		if($result->Yh3mmEsealGrnCreationResponse->EStatus->Status == 0){
			return json_encode(Array(
				'Status'=> 0,
				'Message' =>'S-: '.$result->Yh3mmEsealGrnCreationResponse->EStatus->Message));
		}
		$grnNumber = '';
		if($result->Yh3mmEsealGrnCreationResponse->EStatus->Status == 1){
			$EccGRNCreationData = $result->Yh3mmEsealGrnCreationResponse->EFinal->item;
			// $EccGRNCreationData = array_unique($EccGRNCreationData);
			$uniqGRNRes = [];
			$grnBatchNo = [];
			$i = 0;
			foreach ($EccGRNCreationData as $key => $grnRes) {
				$whereclmName = ($poFlag == true) ? 'po_number' : 'idb_number';
				$whereclmName1 = ($poFlag == true) ? 'po_item' : 'idb_item';
				$whereVal = ($poFlag == true) ? $grnRes->Ponumber : $grnRes->Ibdnumber;
				$whereVal1 = ($poFlag == true) ? $grnRes->Poitem : $grnRes->Ibditem;
				$importPODetails = DB::table('ImportPO')
									->where($whereclmName, $whereVal)
									->where($whereclmName1, $whereVal1)
									->first();
				$grnNumber = $grnRes->GrnNumber;
				$insertImportGrn = array(
					'import_po_id'	 => $importPODetails->id,
					'po_number'		 => $grnRes->Ponumber,
					'ibd_number'	 => $grnRes->Ibdnumber,
					'action_quantity'=>  $itemData[$i]['GrnQty'], //$grnQty,
					'action_uom'	 => $params['itemData'][$i]['uom'],
					'status'		 =>	1,
					'batch'			 => $grnRes->Batch,
					'grn_number'	 => $grnNumber,);
				DB::table('import_grn')->insert($insertImportGrn);
				$grnBatchNo[$i] = $grnRes->Batch;
				$i++;
			}
			$j = 0;
			$output['Data'] = $uniqGRNRes;
			foreach ($params['itemData'] as $key => $value) {
				$GRNBatch = $grnBatchNo[$j];
				// $value = (array) $value;
				foreach ($value['scandata'] as $svalue) {
					// $svalue = (array) $svalue;
					$esealId[] = $svalue['esealId'];
					$esealIdQty += $svalue['qty'];
					$esealIDQty[] = $svalue['qty'];
				}
				$sesealId = implode(',', $esealId);
				$explodeIds = explode(',', $sesealId);
				$explodeIds = array_unique($esealId);
				
				$value['material'] = ltrim($value['material'], '0');
				$pid = DB::table('products')->where('material_code', $value['material'])->value('product_id');
				$convt=new Conversions();
				$grnQty = $convt->getUom($pid, $esealIdQty, 'EA', $value['uom']);
				$plantId = DB::table('locations')->where('location_id',$locationId)->value('erp_code');
				if(!$plantId){
					return json_encode(Array('Status'=> 0, 'Message' =>'S-: Storage Location is not available in eSeal'));
				}

				foreach ($value['scandata'] as $skey => $svalue) {
					// $svalue = (array) $svalue;
					$eSealCode = $svalue['esealId'];
					$eSealCodeQty = $svalue['qty'];
					$grnQty = $convt->getUom($pid, $eSealCodeQty, $value['uom'], 'EA');
					$attributes = json_encode(array('material_code' => $value['material'], 'po_number' => $docNumber, 'MFG_DATE' => date('Y-m-d'), 'batch_no' => $GRNBatch, 'sku_info' => $skuCode, 'uom' => $value['uom'], 'grn_number' => $grnNumber));
					
					// save bind attributes
					$request = Request::create('scoapi/SaveBindingAttributes', 'POST', array('module_id'=> $this->_request->input('module_id'),'access_token'=>$this->_request->input('access_token'),'attributes'=>$attributes,'lid'=>$locationId,'pid'=>$pid));
					$originalInput = $this->_request->all();//backup original input
					$this->_request->replace($request->all());
					$res = app()->handle($request);
					$response = $res->getContent();
					$response = json_decode($response);		
					$map_id = 0;
					if($response->Status){
						$map_id = $response->AttributeMapId;
					}

					$transactionId = DB::table('transaction_master')->where('action_code', 'IMPO')->value('id');

					$request = Request::create('scoapi/BindWithTrackupdate', 'POST',
                                                                    array(
                                                                        'module_id'   => $this->_request->input('module_id'),
                                                                        'access_token' => $this->_request->input('access_token'),
                                                                        'srcLocationId' => $locationId,
                                                                        'destLocationId' => '',
                                                                        'codes' => $eSealCode,
                                                                        'transitionTime' => date('Y-m-d H:i:s'),
                                                                        'internalTransfer' => 0,
                                                                        'ids' => $eSealCode,
                                                                        'pid' => $pid,
                                                                        'attribute_map_id' => $map_id,
                                                                        'flagsJson' => '{"ignoreMultiBinding":"0","ignoreInvalid":"0"}',
                                                                        'transitionId' => $transactionId,
                                                                        'pkg_qty' => $grnQty
                                                                        ));
                                                                        $originalInputd = $this->_request->all();//backup original input
					$this->_request->replace($request->all());
					$resData = self::BindWithTrackupdate($request->all());
					// return json_encode(Array('Status'=> 1, 'Message' =>'S-: checking bind track', 'Data' => $resData));
					$importMappingData = array(
						'Import_po_id' => $importPODetails->id,
						'grn_number'	=> $grnNumber,
						'IOT' => $eSealCode);
					DB::table('Importpo_mapping')->insert($importMappingData);
					// $response = json_decode($response);		
					
				}
				$eSealCodeQty = 0;
				$esealIdQty = 0;
				$esealId = [];
				$j++;
			}
		}
		
		$message = 'GRN Created Successfully!';
		$message = $result->Yh3mmEsealGrnCreationResponse->EStatus->Message;
		return json_encode(Array('Status'=> 1, 'Message' =>'S-: '.$message, 'Data' => $EccGRNCreationData));
	}


	public function checkEsealId(){
		$output = array('Status' => 1, 'Message' => 'S-: Unused');
		$esealId = $this->_request->input('esealId');
		if(empty($esealId)){
			$output['Status'] = 0;
			$output['Message'] = 'S-: eSealCode is mandatory';
			return json_encode($output);
		}
		// Validate IOTs
		$esealBankData = DB::table('eseal_bank_6')
						->where('id', $esealId)
						// ->where('issue_status', '0')
						->first();
		if($esealBankData == NULL){
			$output['Status'] = 0;
			$output['Message'] = 'S-: eSealCode is not available in eSeal';
			return json_encode($output);
		}
		
		$tpCount = DB::table('eseal_6 as eseal')
						->leftjoin($this->trackHistoryTable.' as th', 'eseal.track_id', '=', 'th.track_id')
						->where('primary_id', $esealId)
						->distinct()
						->select('eseal.track_id')
						->first();
			if($tpCount !== NULL){
				$output['Status'] = 0;
				$output['Message'] = 'S-: Used';
			}
		return json_encode($output);
	}

public function grnCancellation(){
	$output = array('Status' => 0, 'Message' => '', 'Data' => array());
	$access_tokne = $this->_request->input('access_token');
	$moduleId = $this->_request->input('module_id');
	$grnNumber = $this->_request->input('grn_number');
	$docYear = $this->_request->input('doc_year');
	$mfgId = $this->roleAccess->getMfgIdByToken($access_tokne);

	if(is_null($access_tokne)){
		$output['Message'] = 'S-: mandatory field Is Access Tokne';
		return json_encode($output);
	}
	if(is_null($moduleId)){
		$output['Message'] = 'S-: mandatory field Is module Id';
		return json_encode($output);
	}

	if(is_null($grnNumber)){
		$output['Message'] = 'S-: mandatory field Is GRN Number';
		return json_encode($output);
	}
	if(is_null($docYear)){
		$output['Message'] = 'S-: mandatory field Is document year';
		return json_encode($output);
	}
	$result = $this->roleAccess->checkPermission($moduleId,$access_tokne);
	if($result == 1){
		$params = 'grn_number='.$grnNumber.'&doc_year='.$docYear.''; 
	    $body = array(
	        'Yh3mmEsealGrnCancellation' => array(
	            'IHeader'   => array(
	                'GrnNumber'     => $grnNumber,
	                'DocumentYear'  => $docYear)));

	    $method = 'grnCancellation';
	    $this->erp=new ConnectErp($mfgId);
	    $result=$this->erp->request($method,'',$body,'POST');
	    $result=json_decode($result);
		if (empty($result)){
	        $output['Message'] = 'S-: Unable to ConnectErp';
	        return json_encode($output);
	    }
	    if($result->Yh3mmEsealGrnCancellationResponse->EStatus->Status == 0){
	        $msg = explode('##', $result->Yh3mmEsealGrnCancellationResponse->EStatus->Message);
	     	$output['Message'] = $msg;
	        return json_encode($output);
	    }
	    if($result->Yh3mmEsealGrnCancellationResponse->EStatus->Status == 1){
	        $cancelledDocNo = $result->Yh3mmEsealGrnCancellationResponse->EOutput->item[0]->CancelledDocNo;
	        $grnDetail = DB::table('import_grn')->where('grn_number', $grnNumber)->update(['cancelled_doc_no'   => $cancelledDocNo, 'status'    => 3]);
	        $output['Message'] = 'S-: GRN Cancel Successfully!!';
	        return json_encode($output);
	    }
	}else{
		$output['Message'] = 'User dont have permission.';
	}
	return json_encode($output);
}

public function conversionPAL(){
	$productId = $this->_request->input('product_id');
	if($productId==''){
		$material_code=ltrim($this->_request->input('material_code'),'0');
		$productId=db::table('products as p')->where('material_code',$material_code)->value('product_id');
	}
	$Auom = $this->_request->input('auom');
	$UomQty = $this->_request->input('uom_qty');
	if(!$productId)
	{
		return json_encode(Array('Status'=> 0, 'Message' =>'S-: product id field is required'));	
	} 
	if(!$Auom)
	{
		return json_encode(Array('Status'=> 0, 'Message' =>'S-: UOM field is required'));	
	}
	if(!$UomQty)
	{
		return json_encode(Array('Status'=> 0, 'Message' =>'S-: UOM quantity field is required'));	
	}
	$convt=new Conversions();
	$pkgQty = $convt->getUom($productId, $UomQty, $Auom, 'PAL');
	$palQty = DB::table('conversions')->where('product_id', $productId)->where('alt_uom', 'PAL')->where('base_uom', $Auom)->value('base_quantity');
	$bea = DB::table('conversions')->where('product_id',$productId)->where('alt_uom', $Auom)->value('base_quantity');
	$aeaAlt = DB::table('conversions')->where('product_id',$productId)->where('alt_uom','PAL')->value('alt_quantity');
	$aea = DB::table('conversions')->where('product_id',$productId)->where('alt_uom','PAL')->value('base_quantity');
	$palQty = $aea*$aeaAlt/$bea;
	return json_encode(Array('Status'=> 1, 'Message' =>'S-: Successfully', 'Data' => array('scan_qty' => $pkgQty, 'palQty' => $palQty)));	

}

public function loginViaOauth() {
	$currentURL = url()->current();
	$checkURL = url('/').'/oauth/authorize';

	if($currentURL == $checkURL) {
		return abort(401);
	}
	$publicKey = DB::table('oauth_clients')->where('user_id',13)->value('public_key');
	$headerPublicKey = $this->_request->header('Public-Key');
	Log::info($publicKey.' -- '.$headerPublicKey);
	log::info($this->_request->header());
	if($headerPublicKey === $publicKey) {
		return true;
	} else {
		return false;
	}
}

public function geteSealinfo(){
	try{

	$status=1;
	$access_tokne = $this->_request->input('access_token');
	$moduleId = $this->_request->input('module_id');
	$mfg_id = $this->roleAccess->getMfgIdByToken($this->_request->input('access_token'));
	$locationId = $this->roleAccess->getLocIdByToken($this->_request->input('access_token'));
	//$esealBankTable = 'eseal_bank_'.$mfgId;
	$eSeal_id = $this->_request->input('eSeal_id');
	$data=array();
	if($eSeal_id==0){
		throw new Exception(" please pass eSeal id ");
		
	}
		$loc_validate=DB::table('track_history as th')->join('eseal_'.$mfg_id.' as e','e.track_id','=','th.track_id')->where('e.primary_id',$eSeal_id)->where('th.dest_loc_id',0)->where('e.is_active',1)->value('th.src_loc_id');
		if(empty($loc_validate)){
			throw new Exception("IOT is already Scrapped or is In-transit", 1);	
		}
		if($loc_validate!=$locationId){
			throw new Exception("IOT not available in this location ", 1);	
		}
		$data=DB::table('eseal_bank_'.$mfg_id.' as eb')->join('eseal_'.$mfg_id.' as e','eb.id','=','e.primary_id')->join('products as p','p.product_id','=','e.pid')->where('eb.id',$eSeal_id)->get(['eb.id','e.batch_no','p.material_code','p.description','e.pkg_qty','e.is_active','eb.level','e.pid'])->toArray();
		$convt=new Conversions();
		$qty_in_cases=$convt->getZ01($data[0]->pkg_qty,'Z01',$data[0]->pid);


			if(count($data)==0){
				$data_tp=DB::table('eseal_bank_'.$mfg_id.' as eb')->where('eb.id',$eSeal_id)->get()->toArray();

				if(empty($data_tp)){
				throw new Exception("IOT not available in eSeal.");
				}
				else if($data_tp[0]->level==9){
					throw new Exception("Tp quantity cannot be updated.");
				}
				else if(count($data_tp)){
					throw new Exception("IOT is not yet used.");
				}
			}		
			else if($data[0]->is_active==0){
				throw new Exception("Iot is already scrapped", 1);	
			}else if($data[0]->level==9){
				throw new Exception("Tp quantity cannot be updated", 1);	
			}
			$info['id']=$data[0]->id;
			$info['batch_no']=$data[0]->batch_no;
			$info['material_code']=$data[0]->material_code;
			$info['description']=$data[0]->description;
			$info['is_active']=$data[0]->is_active;
			$info['level']=$data[0]->level;
			$info['pkg_qty']=$data[0]->pkg_qty;
			$info['qty_in_cases']=$qty_in_cases;
			$x[]=$info;

			
			$message="Iot Data Retrieved ";
   			return json_encode(['Status'=>$status,'Message'=>$message,'Data'=>$x]);
	}catch(Exception $e){
    	$status =0;
    	$message =$e->getMessage(); 
    	return json_encode(['Status'=>$status,'Message'=>$message]);
   	
    }
}
public function syncRetry(){
	try{

	$status=1;
	$access_token = $this->_request->input('access_token');
	$moduleId = $this->_request->input('module_id');
	$mfg_id = $this->roleAccess->getMfgIdByToken($this->_request->input('access_token'));
	$locationId = $this->roleAccess->getLocIdByToken($this->_request->input('access_token'));
	//$esealBankTable = 'eseal_bank_'.$mfgId;
	$eSeal_id = $this->_request->input('eSeal_id');
	$explodeIds = explode(',', $eSeal_id);
	$output=array();
		
	foreach($explodeIds as $iot){
		$ids_loc=DB::table('track_history as th')->join('eseal_'.$mfg_id.' as e','e.track_id','=','th.track_id')->where('e.primary_id',$iot)->where('th.dest_loc_id',0)->where('e.is_active',1)->value('th.src_loc_id');
		if($locationId==$ids_loc){
			$data=DB::table('eseal_bank_'.$mfg_id.' as eb')->join('eseal_'.$mfg_id.' as e','eb.id','=','e.primary_id')->join('products as p','p.product_id','=','e.pid')->where('eb.id',$iot)->get(['e.primary_id','e.batch_no','p.material_code','p.description','e.pkg_qty'])->toArray();
			$output[]=$data[0];
		}
	}
	if(count($output)==0){
		throw new Exception("IOT not available in Location", 1);	
	}
	
	/*$ids_loc=DB::table('track_history as th')->join('eseal_'.$mfg_id.' as e','e.track_id','=','th.track_id')->whereIn('e.primary_id',$explodeIds)->value('th.src_loc_id');*/
	//echo $locationId.'-------'.$ids_loc;
	/*if($locationId==$ids_loc){
		$data=DB::table('eseal_bank_'.$mfg_id.' as eb')->join('eseal_'.$mfg_id.' as e','eb.id','=','e.primary_id')->whereIn('eb.id',$explodeIds)->get(['id','batch_no','pkg_qty','is_active','level'])->toArray();
	}else{
		throw new Exception("IOT not available in Location", 1);	
	}*/

	$message='Data Retrieved Successfully';
	//$data=[];
	return json_encode(['Status'=>$status,'Message'=>$message,'Data'=>$output]);

	}catch(Exception $e){
		$status =0;
    	$message =$e->getMessage(); 
    	return json_encode(['Status'=>$status,'Message'=>$message]);
		
	}
}

public function updateEsealqty(){

	try{
		$status=1;
		$access_tokne = $this->_request->input('access_token');
		$moduleId = $this->_request->input('module_id');
		$mfg_id = $this->roleAccess->getMfgIdByToken($this->_request->input('access_token'));
		$eSeal_id = $this->_request->input('eSeal_id');
		$updated_qty=$this->_request->input('updated_qty');
		if($eSeal_id==0){
			throw new Exception("Empty eSeal id", 1);
			
		}
		$data=DB::table('eseal_bank_'.$mfg_id.' as eb')->join('eseal_'.$mfg_id.' as e','eb.id','=','e.primary_id')->where('eb.id',$eSeal_id)->get()->toArray();
			if(count($data)==0){
				throw new Exception("IOT not available in eSeal");			
			}else if($data[0]->is_active==0){
				throw new Exception("Iot is already scrapped", 1);	
			}else if($data[0]->level==9){
				throw new Exception("This is a TP", 1);	
			}

			$pallet_capacity=DB::table('conversions as c')->where('c.product_id',$data[0]->pid)->where('alt_uom','=','PAL')->value('base_quantity');
			/*---------conversion of Z01 to EA-----------------*/
			$convt=new Conversions();
			$qty_in_cases=$convt->getZ01($pallet_capacity,'Z01',$data[0]->pid);
			$updated_qty=$convt->getZ01toEA($data[0]->pid,$updated_qty,'Z01');
			/*------------conversion ends-------------------------*/

			if($updated_qty>$pallet_capacity){
				throw new Exception("Pallet capacity exceeded.Maximum capacity:-".$qty_in_cases." Cases", 1);				
			}
			else{
				DB::table('eseal_'.$this->mfg_id)->where('primary_id',$eSeal_id)->update(['pkg_qty'=>$updated_qty]);
			}

			$message="Quantity updated";
			return json_encode(['Status'=>$status,'Message'=>$message]);

	}catch(Exception $e){
		$status=0;
		$message=$e->getMessage();
		return json_encode(['Status'=>$status,'Message'=>$message]);
	}
}
public function poQtyDetails(){
	try{
		$status=1;
		$access_tokne = $this->_request->input('access_token');
		$moduleId = $this->_request->input('module_id');
		$mfg_id = $this->roleAccess->getMfgIdByToken($this->_request->input('access_token'));
		$po_number = $this->_request->input('po_number');
		$po_data=DB::table('production_orders')
		             ->where(function ($query) use ($po_number){
    			        $query->where('erp_doc_no', '=', $po_number)
        			    ->orWhere('eseal_doc_no', '=', $po_number);
					    })->first();
		if(empty($po_data)){
			throw new Exception("No Data agaisnt PO ", 1);			
		}
		$packed_qty = DB::table('eseal_'.$mfg_id)->where(['po_number'=>$po_number,'level_id'=>0,'is_active'=>1])->sum('pkg_qty');
        $confirmQty = DB::table('eseal_'.$mfg_id)->where(['po_number'=>$po_number,'level_id'=>0,'is_active'=>1])->where('is_confirmed','!=',0)->sum('pkg_qty');
/*-------conversion---------------*/
$convt=new Conversions();
$packed_cases=$convt->getZ01($packed_qty,$po_data->order_uom,$po_data->product_id);
$cnfmd_cases=$convt->getZ01($confirmQty,$po_data->order_uom,$po_data->product_id);
$pallet_to_pack=$convt->getUom_new($po_data->product_id,$po_data->qty,$po_data->order_uom);
$eaches_to_pack=$convt->getZ01toEA($po_data->product_id,$po_data->qty,$po_data->order_uom);
$pallet_confirmed=$convt->getUom_new($po_data->product_id,$cnfmd_cases,$po_data->order_uom);
$pallet=db::table('conversions')->where('product_id',$po_data->product_id)
					->where(function ($query){
    			        $query->where('alt_uom', '=', 'PAL')
        			    ->orWhere('alt_uom', '=', 'Y01');
        			})->value('base_quantity');
$cases=	db::table('conversions')->where('product_id',$po_data->product_id)
					->where('alt_uom','=','Z01')->value('base_quantity');
									
/*--------------end of conversion-----------*/
		$data['po_number']=$po_number;
		$data['po_qty_in_cases']=$po_data->qty;
		$data['po_qty_in_PAl']=$pallet_to_pack;
		$data['po_qty_in_Eaches']=$eaches_to_pack;

		$data['packed_cases']=$packed_cases;
		$data['packed_PAL']=$convt->getUom_new($po_data->product_id,$packed_cases,$po_data->order_uom);
		$data['confirmed_PAL']=$pallet_confirmed;
		$data['confirmed_cases']=$cnfmd_cases;
		$data['confirmed_Eaches']=$convt->getZ01toEA($po_data->product_id,$cnfmd_cases,$po_data->order_uom);

		$data['cases_to_pack']=$po_data->qty-$packed_cases;
		$data['PAL_to_pack']=$convt->getUom_new($po_data->product_id,$data['cases_to_pack'],$po_data->order_uom);
		$data['eaches_to_pack']=$convt->getZ01toEA($po_data->product_id,$data['cases_to_pack'],$po_data->order_uom);

		$data['cases_per_pallet']=$pallet/$cases;
		$data['eaches_per_pallet']=$pallet;
		$message="Data Retrieved";
			return json_encode(['Status'=>$status,'Message'=>$message,'Data'=>$data]);


	}catch(Exception $e){
		$status=0;
		$message=$e->getMessage();

		return json_encode(['Status'=>$status,'Message'=>$message]);

	}

}



/**/
public function forgotPassword(){
		try{
                        //Log::info(__FUNCTION__.' : '.print_r(Input::all(),true));
			$status =0;
			$otp = '';
			$email = '';
			$username = $this->_request->input('username');
			if(empty($username))
				throw new Exception('Paramaters Missing');
			$email = User::where('username',$username)->value('email');
			//echo $email;exit;
Log::info('email id'); Log::info($email);
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
				/*$fields=['otp']=$otp;	
				$fields=['email']=$email;*/	
				$fields = array('otp' => $otp, 'email' => $email);
				$status1 = \Mail::send('emails.reset', $fields, function($message) use ($email)
				{
					$message->to($email);
				});
				//echo $status1;exit;
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
/**/


}        
