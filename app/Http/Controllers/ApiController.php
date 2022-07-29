<?php

class ApiController extends BaseController 
{
	public function callapi()
	{		
		$name = Input::get('name');
		$prefix = Input::get('prefix');
		$data = Input::get('data');
		$api = Input::get('api');
		//$data = array('key'=>123,'uname'=>'venkat');
		echo '<pre/>';print_r($data);echo $data['key'];exit;
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
		$location_id = Input::get('location_id');
		
		try{
			//$orgs = DB::table('wms_entities')->where('location_id',0)->get();
			//return $orgs;
			$wares = DB::table('wms_entities')
			->leftJoin('wms_entity_types', 'wms_entity_types.id', '=', 'wms_entities.entity_type_id')
			->leftJoin('wms_dimensions', 'wms_dimensions.entity_id', '=', 'wms_entities.id')
			->select('wms_entities.*','wms_dimensions.*','wms_dimensions.id as dimension_id')
			->where(array('wms_entities.entity_type_id'=>1,'wms_entities.location_id'=>$location_id))
					->get();//print_r($wares[0]->location_id);

					if(empty($wares)){
						$orgs = DB::table('wms_entities')
						->select('id as entity_id'	,'org_id','entity_name','entity_type_id')
						->where('location_id',0)->get();
						$arr['status'] = 2;
						$arr['data'] = $orgs;
						return $arr;
					}
					//getting organization dat
					$gorg_id = $wares[0]->org_id;
					
					$orgs = DB::table('wms_entities')	            	
					->where('wms_entities.org_id',$gorg_id)
					->where('wms_entities.location_id',0)
					->orderBy('id', 'ASC')
					->get();
					
					$finalorgarr = array();
					$org_array = array();
					$org_array['entity_id']=$orgs[0]->id;
					$org_array['entity_name']=$orgs[0]->entity_name;
					$org_array['entity_code']=$orgs[0]->entity_code;

					$finalorgarr = $org_array;
					$totalResult[] = $finalorgarr;//return $totalResult;

					$warearr = array();
					$finalwarearr = array();
					if(!empty($wares))
					{	            		
						foreach($wares as $ware)
						{
							$floors = DB::table('wms_entities')
							->leftJoin('wms_entity_types', 'wms_entity_types.id', '=', 'wms_entities.entity_type_id')
							->leftJoin('wms_dimensions', 'wms_dimensions.entity_id', '=', 'wms_entities.id')
							->select('wms_entities.*','wms_entities.id as entity_id','wms_dimensions.*','wms_dimensions.id as dimension_id')
							->where('wms_entities.parent_entity_id',$ware->entity_id)
							->get();
							/*$queries = DB::getQueryLog();
							$last_query = end($queries);
							return $last_query;*/
							
							$floorarr = array();
							$finalfloorarr = array();
							if(!empty($floors))
							{
								
								foreach($floors as $floor)
								{
									$zones = DB::table('wms_entities')
									->leftJoin('wms_entity_types', 'wms_entity_types.id', '=', 'wms_entities.entity_type_id')
									->leftJoin('wms_dimensions', 'wms_dimensions.entity_id', '=', 'wms_entities.id')
									->select('wms_entities.*','wms_entities.id as entity_id','wms_dimensions.*','wms_dimensions.id as dimension_id')
									->where('wms_entities.parent_entity_id',$floor->entity_id)
									->get();
									$zonearr = array();
									$finalzonearr = array();
									if(!empty($zones))
									{
										foreach($zones as $zone)
										{
											$racks = DB::table('wms_entities')
											->leftJoin('wms_entity_types', 'wms_entity_types.id', '=', 'wms_entities.entity_type_id')
											->leftJoin('wms_dimensions', 'wms_dimensions.entity_id', '=', 'wms_entities.id')
											->select('wms_entities.*','wms_entities.id as entity_id','wms_dimensions.*','wms_dimensions.id as dimension_id')
											->where('wms_entities.parent_entity_id',$zone->entity_id)
											->get();
											$rackarr = array();
											$finalrackarr = array();
											if(!empty($racks))
											{					            		
												foreach($racks as $rack)
												{
													$bins = DB::table('wms_entities')
													->leftJoin('wms_entity_types', 'wms_entity_types.id', '=', 'wms_entities.entity_type_id')
													->leftJoin('wms_dimensions', 'wms_dimensions.entity_id', '=', 'wms_entities.id')
													->select('wms_entities.*','wms_entities.id as entity_id','wms_dimensions.*','wms_dimensions.id as dimension_id')
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
															$binarr['parent_entity_id'] = $bin->parent_entity_id;
															$binarr['ware_id'] = $ware->entity_id;
															$binarr['capacity'] = $bin->capacity;
															$binarr['capacity_uom_id'] = $bin->capacity_uom_id;
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
													$rackarr['parent_entity_id'] = $rack->parent_entity_id;
													$rackarr['ware_id'] = $ware->entity_id;
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
											$zonearr['parent_entity_id'] = $zone->parent_entity_id;
											$zonearr['ware_id'] = $ware->entity_id;
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
									$floorarr['parent_entity_id'] = $floor->parent_entity_id;
									$floorarr['ware_id'] = $ware->entity_id;
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
							$warearr['parent_entity_id'] = $ware->parent_entity_id;
							$warearr['ware_id'] = '';
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
			//$getArr['rack_capacity'] = $value->rack_capacity;

						$product_name = DB::table('catalog_product_entity_text')->where(array('attribute_id'=>64,'entity_id'=> $value->pname))->first();
						$getArr['pname'] = $product_name->value;
			//$getArr['edit'] = '<a href="/wms/package/packageedit/'.$value->id.'"><img src="/wms/img/edit.png" /></a>'; 
			//$getArr['delete'] = '<a onclick="deletePackage('.$value->id.')" href=""><img src="/wms/img/delete.png" /></a>';
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
				->select('wms_entities.id','wms_entities.entity_name','wms_entities.entity_code','wms_entities.org_id','wms_entity_types.entity_type_name','wms_entities.parent_entity_id','wms_entities.capacity','wms_entities.entity_type_id')
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
						->select('wms_entities.id','wms_entities.entity_name','wms_entities.entity_code','wms_entities.org_id','wms_entities.location_id','wms_entity_types.entity_type_name','wms_entities.parent_entity_id','wms_entities.capacity','wms_entities.entity_type_id')
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

			public function createPackage(){

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

			public function updatePackage(){

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

			public function deletePackage(){

				$data = Input::get();
				if(!isset($data['package_id']) || empty($data['package_id'])){
					return Response::json(array('status'=>0,'message'=>'Package_id missing'));
				}
				$package = Package::where('id',$data['package_id'])->first();
				$package->delete();
				return Response::json(array('status'=>1,'message'=>'Package Deleted Successfully'));
			}

			public function createWareHouse(){

				$data = Input::get();

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
				$manufacturer_id = DB::table('track_and_trace_location')->where('location_id',$data['location_id'])->pluck('manufacturer_id');

				try{ 
					if(isset($data['entity_id']) && !empty($data['entity_id'])){
						$entity = Entities::where('id',$data['entity_id'])->first();

						if($data['entity_type_id'] == 4){
							$bin = DB::table('wms_storage_bins')
							->where('entity_id',$data['entity_id'])
							->update(['storage_bin_name'=>$data['entity_name'],'storage_capacity'=>$data['capacity']]);

						}
					}  
					else{ 

						$entity = new Entities;
					}
					$entity->entity_name = $data['entity_name'] ;
					$entity->entity_type_id = $data['entity_type_id'];
					$entity->ware_id = $data['ware_id'];
					if($data['entity_type_id'] == 1)
					{   
						$org_id = Entities::where('org_id',$manufacturer_id)->pluck('id');
						$entity->parent_entity_id = $org_id;
					}else{     
						$entity->parent_entity_id = $data['parent_id'];
					}

					$entity->capacity = $data['capacity'];
					$entity->capacity_uom_id = $data['capacity_uom'];
					$entity->status = 1;
					$entity->xco = $data['xco'];
					$entity->yco = $data['yco'];
					$entity->zco = $data['zco'];
					$entity->location_id = $data['location_id'];  
					$entity->org_id = $manufacturer_id;
					$entity->save();

					if(!isset($data['entity_id'])){  
						$entity_id = DB::getPdo()->lastInsertId();

						if($data['entity_type_id']==4){
							$bins = new Bins;
							$bins->entity_id = $entity_id;
							$bins->storage_bin_name = $data['entity_name'];
							$bins->status = 'Empty';

							$bins->ware_id = $data['ware_id'];
							$bins->storage_capacity = $data['capacity'];
							$bins->is_allocated = 0;
							$bins->save();
						}

						if($data['entity_type_id'] ==1)
						{
							$entity_name = 'Warehouse'; 	
							$entity_code = 'W'.$entity_id;
						}
						else if($data['entity_type_id']==7)
						{
							$entity_name = 'Floor';
							$entity_code = 'F'.$entity_id;
						}
						else if($data['entity_type_id']==8){
							$entity_name = 'Dock';
							$entity_code = 'D'.$entity_id;
						} 
						else if($data['entity_type_id']==2)
						{
							$entity_name = 'Zone';
							$entity_code = 'Z'.$entity_id;
						}
						else if($data['entity_type_id']==5)
						{
							$entity_name = 'Open Zone';
							$entity_code = 'Oz'.$entity_id;
						}
						else if($data['entity_type_id']==6)
						{
							$entity_name = 'Put Away Zone';
							$entity_code = 'Paz'.$entity_id;
						}
						else if($data['entity_type_id']==3)
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
					if($entity['entity_type_id'] == 4){
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


			public function assignData(){
				$data = Input::get();
				if(!isset($data['product_id']) || !isset($data['package_id']) || !isset($data['entity_id'])){
					return json_encode(array('Status'=> 0,'Message'=>'Parameters Missing.'));
				}
				else{
					$entity = Entities::where('id',$data['entity_id'])->first();
					$eseal = Eseal::where('entity_id',$data['entity_id'])->first();
					if(!empty($eseal)){
						if($entity['entity_type_id'] == 4){
							$bin = DB::table('wms_storage_bins')
							->where('entity_id',$data['entity_id'])
							->update(['pid'=>$data['product_id'],'pname'=>'']);
						}
					}
					else{
						$entity->is_assigned = 1;
						$entity->save();
						if($entity['entity_type_id'] == 4){
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

			public function deleteAssignedData(){

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

			public function getProductList(){

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

			public function getStorageData(){

				$data = Input::get();
				$location_id = $data['location_id'];

				$entities =Entities::where(['location_id'=>$location_id,'entity_type_id'=>4])->get();
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


			public function updateHoldingCount(){
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

		}
