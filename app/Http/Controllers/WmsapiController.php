<?php
use Central\Repositories\RoleRepo;
use Central\Repositories\CustomerRepo;
class WmsapiController extends BaseController 
{
	public function __construct(RoleRepo $roleAccess,CustomerRepo $custRepo) 
	{
		$this->roleAccess = $roleAccess;
		$this->custRepo = $custRepo;
	}
 
	public function checkUserPermission($api_name)
	{
		try
		{			
			$status = 0;
			$data = Input::get();
			if($api_name == 'login' || $api_name == 'forgotPassword' || $api_name == 'resetPassword' || $api_name == 'sendLogEmail'){
				$result = $this->$api_name($data);
				return $result;
			} 	
			$module_id = $data['module_id'];
			$access_token = $data['access_token'];
			if(empty($module_id) || empty($access_token))
			{
				throw new Exception('Parameters Missing.');	
			}
			else
			{
				$result = $this->roleAccess->checkPermission($module_id,$access_token);
				
				if($result == 1)
				{
					$result = $this->$api_name($data);
					return $result;
				}
				else
				{
					throw new Exception('User dont have permission.');	
				}
			}
		}
		catch(Exception $e){
			$message = $e->getMessage();
		}
		return Response::json(['Status'=>$status,'Message'=>$message]);
	}
	
	public function login($data)
	{
		try
		{
			Log::info($data);			
			$status =0;
			$user_id = $data['user_id'];
			$password = $data['password'];
			$module_id = $data['module_id'];

			if(empty($user_id) || empty($password) || empty($module_id))
			{
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
				if(empty($access))
				{
					$token = new Token;
					$token->user_id = $user_id;
					$token->module_id = $module_id;
					$token->access_token = $rand_id;
					$token->save();
				}
				else
				{
					$rand_id = $access->access_token;
				}
				$userinfo = DB::table('users')
							->leftJoin('locations','locations.location_id','=','users.location_id')
							->leftJoin('location_types','location_types.location_type_id','=','locations.location_type_id')
							->leftJoin('user_roles','user_roles.user_id','=','users.user_id')
							->where('user_roles.user_id',$user_id)
							->get(['locations.location_id','locations.location_name','locations.location_type_id','location_types.location_type_name','locations.location_email','locations.location_address','locations.location_details','locations.erp_code','users.firstname','users.user_id','users.lastname','users.email','users.customer_id','user_roles.role_id','users.location_id'])[0];
				if(empty($userinfo))
				{
					throw new Exception('Role not assigned to User');
				}
				$manufacturer_name =  DB::table('eseal_customer')->where('customer_id',$userinfo->customer_id)->pluck('brand_name');
				$user = array('user_id'=>$userinfo->user_id,'firstname'=> $userinfo->firstname,'lastname'=>$userinfo->lastname,'email'=> $userinfo->email,'manufacturer_id'=> $userinfo->customer_id,'manufacturer_name'=>$manufacturer_name);
				$location = array('location_id'=>intval($userinfo->location_id),'name'=>$userinfo->location_name,'location_type_id'=>intval($userinfo->location_type_id),'erp_code'=>$userinfo->erp_code,'location_type_name'=>$userinfo->location_type_name,'email'=>$userinfo->location_email,'address'=>$userinfo->location_address,'details'=>$userinfo->location_details);
				
				$permissioninfo = DB::table('role_access')
									->leftJoin('features','role_access.feature_id','=','features.feature_id')
									->join('features as fs','fs.feature_id','=','features.parent_id')
									->where(array('role_access.role_id'=>$userinfo->role_id,'features.master_lookup_id'=>$module_id))                     
									->get(['features.name','features.feature_code','fs.feature_code as parent_feature_code']);
				
				$traninfo = DB::table('transaction_master')
								->where('manufacturer_id',$userinfo->customer_id)
								->get();
				  /*$traninfo = DB::table('role_access')
								   ->join('features','role_access.feature_id','=','features.feature_id')
								   ->join('master_lookup','master_lookup.value','=','features.master_lookup_id')
								   ->join('transaction_master','transaction_master.name','=','features.name')
								   ->where(['role_access.role_id'=>$userinfo->role_id,'master_lookup_id'=>4002,'transaction_master.manufacturer_id'=>$userinfo->customer_id])
								   ->orderBy('seq_order','desc')
								   ->get(['transaction_master.*']);*/

			Log::info('Login Successfull');
				return Response::json(['Status'=>1,'Message'=>'Successfull Login','Data'=>['user_info'=>$user,'permissions'=>$permissioninfo,'location'=>$location,'transitions'=>$traninfo,'access_token'=>$rand_id]]);
			}
			else
			{
				throw new Exception('Invalid UserId or Password.');
			}
		}
		catch(Exception $e)
		{
			$message  = $e->getMessage();
		}
		return Response::json(['Status'=>$status,'Message'=>$message]);
	}
	
	public function callapi()
	{		
		$name = Input::get('name');
		$prefix = Input::get('prefix');
		$data = Input::get('data');
		$api = Input::get('api');
		//$data = array('key'=>123,'uname'=>'venkat');
		//echo '<pre/>';print_r($data);echo $data['key'];exit;
		if($data['key'])
		{
			return 'key';
		}
		elseif($data['uname'] && $data['pwd'])
		{
			return 'params';
		}
		else
		{
			return 'User dont have permission.';
		}
		//$result = checkPermission($params);
		//echo $result;
		exit;
	}
	
	public function getWareHouseData()
	{
		$totalResult = array();		
		$location_id = $this->roleAccess->getLocIdByToken(Input::get('access_token'));
		$mfg_id = $this->roleAccess->getMfgIdByToken(Input::get('access_token'));
		try{
			$wares = DB::table('wms_entities')
			->leftJoin('master_lookup', 'master_lookup.value', '=', 'wms_entities.entity_type_id')
			->leftJoin('wms_dimensions', 'wms_dimensions.entity_id', '=', 'wms_entities.id')
			->select('wms_entities.*','wms_dimensions.*','wms_dimensions.id as dimension_id')
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
					$totalResult[] = $finalorgarr;//return $totalResult;

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
									$floorarr['entity_type_name'] = $floor->value;
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
					//return json_encode($result);
				}
				return json_encode($result);
	}

	public function getUomData()
	{
		$uoms = DB::table('wms_uom_group')	            	
		->select('id','description')	            	
		->get();
		$uom_group_array = array();
		$finalarray = array();

		if(!empty($uoms)){       

			foreach ($uoms as $uom){
				$uom_group_array['uom_group_id'] = $uom->id;
				$uom_group_array['uom_group_description'] = $uom->description;
				$finalarray['uomgroups'][] = $uom_group_array;
			}
			$uoms = DB::table('wms_uom')	            	
			->select('id','code','description','uom_group_id','parent_uom_id','uom_type_id')	            	
			->get();
			$uom_array = array();
			if(!empty($uoms)){
				foreach ($uoms as $uom){
					$uom_array['uom_id'] = $uom->id;
					$uom_array['uom_code'] = $uom->code;
					$uom_array['uom_description'] = $uom->description;
					$uom_array['uom_group_id'] = $uom->uom_group_id;
					$uom_array['parent_uom_id'] = $uom->parent_uom_id;
					$uom_array['uom_type_id'] = $uom->uom_type_id;
					$finalarray['uoms'][] = $uom_array;                        
				}

			}
			else{
				$finalarray['uoms'][] = '';
			}
		}
		else{
			$finalarray = array('Status'=> 0,'Message'=>'no data');

		}

		return json_encode(array('status' => 1,'message' => 'Success','Data' => $finalarray));
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
			return Response::json(array('status'=> 0,'message' =>'Package Id missing'));
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
		return Response::json(array('status'=> 1,'message' =>'Package Updated Successfully.'));
	}

	public function deletePackage()
	{
		$data = Input::get();
		if(!isset($data['package_id']) || empty($data['package_id'])){
			return Response::json(array('status'=>0,'message'=>'Package_id missing'));
		}
		$package = Package::where('id',$data['package_id'])->first();
		$package->delete();
		return Response::json(array('status'=>1,'message'=>'Package Deleted Successfully'));
	}

	public function getMasterLookupData()
	{
		$lookup_id = array();
		$lookup_id = DB::table('lookup_categories')
						->select('id','name','description','is_active')
						->whereIn('name',['WH Entity Types','Length UOM','Capacity UOM','Area UOM','Volume UOM','Storage Location Types','Pallet types'])
						->get();
		$master_lookup_value = array();
		$master_lookup_value = DB::table('lookup_categories')
								  ->join('master_lookup','lookup_categories.id','=','master_lookup.category_id')
								  ->select('master_lookup.id','master_lookup.category_id','master_lookup.name','master_lookup.value','master_lookup.description','master_lookup.is_active','master_lookup.sort_order')
								  ->whereIn('lookup_categories.name',['WH Entity Types','Length UOM','Capacity UOM','Area UOM','Volume UOM','Storage Location Types','Pallet types'])
								  ->get();
		if(!empty($lookup_id) && !empty($master_lookup_value))
		{
			return Response::json(array('status'=> 1,'message' =>'Updated Successfully.','lookup_categories[]'=>$lookup_id, 'master_lookup_data[]'=>$master_lookup_value));
		}else{
			return Response::json(array('status'=> 0,'message' =>'No Data Returned.'));
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

			return Response::json(array('Status'=> 1,'Message'=>'Product Successfully Assigned.'));
		}
	}

	public function deleteAssignedData()
	{
		$data = Input::get();
		if(!isset($data['entity_id']) || empty($data['entity_id'])){
			return Response::json(array('Status'=> 0,'Message'=>'Entity Id missing.'));	
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
		return Response::json(array('Status'=> 1,'Message'=>'Product Successfully Un-assigned.'));
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
			return Response::json(array('Status'=>0,'Message'=>'Data not Found.'));
		}
		return Response::json(array('Status'=>1,'Message'=>'Data Successfully Retrieved','Data'=> $products));
	}

	public function getStorageData()
	{
		$data = Input::get();		
		$mfg_id = $this->roleAccess->getMfgIdByToken(Input::get('access_token'));
		$location_id = $data['location_id'];
		$entities =Entities::where(['location_id'=>$location_id,'entity_type_id'=>6005,'org_id'=>$mfg_id])->get();
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
			return Response::json(array('status'=> 1,'message'=>'Data Retrieved','data'=>$finalarr));
		}           
		catch(exception $e){
			return Response::json(array('status'=>0,'message'=>'exception occurred'));
		}
	}

	public function updateHoldingCount()
	{
		$data = Input::get();
		if(!isset($data['entity_id']) && !isset($data['holding_count'])){
			return Response::json(['status'=> 0,'message'=>'Parameters Missing.']);
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
			return Response::json(['status'=> 1,'message'=>'Holding Count updated successfully.']);
			}
		}
		return Response::json(['status'=>0,'message'=>'In-valid Bin.']);
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
				  $getArr['id'] = $value->id;
		          $getArr['pallet_id'] = $value->pallet_id;
		          $getArr['pallet_type_id'] = $value->pallet_type_name;
		          $getArr['weight'] = $value->weight;
		          $getArr['weightUOMId'] = $value->weightuom;
		          $getArr['height'] = $value->height;
		          $getArr['width'] = $value->width;
		          $getArr['length'] = $value->length;
		          $getArr['dimensionUOMId'] = $value->dimensionuom;
		          $finalgetArr[] = $getArr;
		        }
		    return json_encode(array('status'=>1,'message'=>'Data Retrieved.','data'=>$finalgetArr));
		    }
		catch(exception $e){
			return Response::json(array('status'=>0,'message'=>'exception occurred'));
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
		    return Response::json(['status'=>1,'message'=>'Pallet created Successfully.']);
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

		 	return Response::json(['status'=>1,'message'=>'Pallet updated Successfully.']);   
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
        	return Response::json(['status'=>1,'message'=>'Pallet deleted Successfully.']); 
    	}else{
			return json_encode(array('status' => 0,'message' => 'Exception Occurred'));
		}              
	}
	
	private function getTime()
	{
        $time = microtime();
        $time = explode(' ', $time);
        $time = ($time[1] + $time[0]);
        return $time;
    }
	
	public function UpdatePalletDataForAllocation()
	{
        //$startTime = $this->getTime();
        $pallet_id = Input::get('pallet_id');
        $product_id = Input::get('id');
        $product_ids = explode(",", $product_id);
        $warehouse_id = Input::get('warehouse_id');
        $suggested_location = Input::get('suggested_location');
        $placed_location = Input::get('placed_location');
        $allocated_status = Input::get('status');
        $delivery_number = Input::get('delivery_number');
       
        try
        {
            DB::beginTransaction();
            $location_id = $this->roleAccess->getLocIdByToken(Input::get('access_token'));
			$mfg_id = $this->roleAccess->getMfgIdByToken(Input::get('access_token'));

            if(!empty($pallet_id) && $allocatedStatus==0) 
                {
                    $allocated_date = '0000-00-00 00:00:00';
                    //$allocated_date = $datetime;
                    try
                    {    
                        //Storing into Pallet data
						$pallet_data = new Wms\palletData;
						$chkqry = $pallet_data->where(array('pallet_id'=>$pallet_id))->pluck('id');
						if(empty($chkqry))
						{
							$pallet_data->mfg_id = $mfg_id;
							$pallet_data->location_id = $location_id;
							$pallet_data->warehouse_id = $warehouse_id;
							$pallet_data->pallete_id = $pallet_id;
							$pallet_data->suggested_location = $suggested_location;
							$pallet_data->placed_location = $placed_location;
							$pallet_data->status = $allocatedStatus;
							$pallet_data->allocated_date = $allocated_date;
							$pallet_data->delivery_number = $delivery_number;
							$pallet_data->save();
						}
						else
						{
							$entities = Wms\palletData::find($pallet_id);
							$pallet_data->mfg_id = $mfg_id;
							$pallet_data->location_id = $location_id;
							$pallet_data->warehouse_id = $warehouse_id;
							$pallet_data->pallete_id = $pallet_id;
							$pallet_data->suggested_location = $suggested_location;
							$pallet_data->placed_location = $placed_location;
							$pallet_data->status = $allocatedStatus;
							$pallet_data->allocated_date = $allocated_date;
							$pallet_data->delivery_number = $delivery_number;
							$pallet_data->save();
						}
						$pallet_data_id = DB::getPdo()->lastInsertId();
						$tblName = 'eseal_'.$mfg_id;						
						try
						{
							DB::table($tblName)->whereIn('primary_id',$product_ids)->update(array('pallet_data_flag'=>$pallet_data_id));
						}
						catch(PDOException $e)
						{
							throw new Exception($e->getMessage());
						}						
                    }
                    catch(Exception $e)
                    {
                       //throw new Exception('Error during creating pallet data');
                       return Response::json(['Status'=>0,'Message'=> 'Error during creating pallet data']);
                    }
                    DB::commit();
                }
                else
                {                   
                    //throw new Exception('Error during pallet updation');
                    return Response::json(['Status'=>0,'Message'=> 'Error during pallet updation']);
                }
        }
        catch(Exception $e)
        {
            //return $this->output('xml', 0, $e->getMessage(), '');
            return Response::json(['Status'=>0,'Message'=> $e->getMessage()]);
        }
        return Response::json(['Status'=>1,'Message'=>'Updated succesfully']);
    }

    public function getAllocatedPalletData()
	{
        $warehouse_id = Input::get('warehouse_id');
        $startTime = $this->getTime();
        $result ='';
        $status = '';
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
            					->Join('product_attributesets as prodAttSet', 'pData.location_id','=','prodAttSet.location_id')
            					->Join('product_attributesets as prodAttSet1',$tbl_name.'.pid','=','prodAttSet1.product_id')
            					->Join('attribute_set_mapping as attSetMap','prodAttSet.attribute_set_id','=','attSetMap.attribute_set_id')
            					->Join('attributes','attSetMap.attribute_id','=','attributes.attribute_id')
               					->select($tbl_name.'.pid as prod_id','products.name','prodAttSet.attribute_set_id','attSetMap.attribute_id','attributes.name')
            					->get();


            		$i++;

            }
			if(!empty($getPalletData))
			{
				$status = 1;
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
        
        return Response::json(['Status'=>$status,'Message'=>$message,'Data'=>$result]);
    }		 			

}
