<?php
ini_set('max_execution_time', 3000); //300 seconds = 5 minutes
use Central\Repositories\CommonRepo;
use Central\Repositories\RoleRepo;

class EntitiesController extends \BaseController 
{
	private $commonRepo;
    private $roleRepo;

	public function __construct()
    {
        $this->commonRepo = new CommonRepo;
        $this->roleRepo = new RoleRepo;
    }


	public function getWareHouseData()
	{
		$location_id = Input::get('location_id');
		
		     $wares = DB::table('wms_entities')
	            	->leftJoin('wms_entity_types', 'wms_entity_types.id', '=', 'wms_entities.entity_type_id')
	            	->leftJoin('wms_dimensions', 'wms_dimensions.entity_id', '=', 'wms_entities.id')
            		->select('wms_entities.*','wms_dimensions.*','wms_dimensions.id as dimension_id')
	            	->where(array('wms_entities.entity_type_id'=>1,'wms_entities.location_id'=>$location_id))
	            	->get();//return $wares;//->location_id;
	        		$totalResult = array();
	        		$warearr = array();
					$finalwarearr = array();
	            	if(!empty($wares))
            		{	            		
	            		foreach($wares as $ware)
			            {
			            	$zones = DB::table('wms_entities')
			            	->leftJoin('wms_entity_types', 'wms_entity_types.id', '=', 'wms_entities.entity_type_id')
			            	->leftJoin('wms_dimensions', 'wms_dimensions.entity_id', '=', 'wms_entities.id')
            				->select('wms_entities.*','wms_entities.id as entity_id','wms_dimensions.*','wms_dimensions.id as dimension_id')
			            	->where('wms_entities.parent_entity_id',$ware->entity_id)
			            	->get();
			            	/*$queries = DB::getQueryLog();
							$last_query = end($queries);
			            	return $last_query;*/
			            	
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
								            		$binarr['capacity'] = $bin->capacity;
								            		$binarr['capacity_uom_id'] = $bin->capacity_uom_id;
								            		//$binarr['location_id'] = $bin->location_id;
								            		$binarr['dimension_id'] = $bin->dimension_id;
								            		//$binarr['org_id'] = $bin->org_id;
								            		$binarr['height'] = $bin->height;
								            		$binarr['width'] = $bin->width;
								            		$binarr['depth'] = $bin->depth;
								            		$binarr['length'] = $bin->length;
								            		$binarr['area'] = $bin->area;
								            		$binarr['uom_id'] = $bin->uom_id;
								            		
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
								            $rackarr['capacity'] = $rack->capacity;
								            $rackarr['capacity_uom_id'] = $rack->capacity_uom_id;
								            //$rackarr['location_id'] = $rack->location_id;
								            $rackarr['dimension_id'] = $rack->dimension_id;
								            //$rackarr['org_id'] = $rack->org_id;
								            $rackarr['height'] = $rack->height;
								            $rackarr['width'] = $rack->width;
								            $rackarr['depth'] = $rack->depth;
								            $rackarr['length'] = $rack->length;
								            $rackarr['area'] = $rack->area;
								            $rackarr['uom_id'] = $rack->uom_id;

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
						            $zonearr['capacity'] = $zone->capacity;
						            $zonearr['capacity_uom_id'] = $zone->capacity_uom_id;
						            //$zonearr['location_id'] = $zone->location_id;
						            $zonearr['dimension_id'] = $zone->dimension_id;
						            //$zonearr['org_id'] = $zone->org_id;
						            $zonearr['height'] = $zone->height;
						            $zonearr['width'] = $zone->width;
						            $zonearr['depth'] = $zone->depth;
						            $zonearr['length'] = $zone->length;
						            $zonearr['area'] = $zone->area;
						            $zonearr['uom_id'] = $zone->uom_id;

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
					        $warearr['entity_id'] = $ware->entity_id;
		            		$warearr['entity_name'] = $ware->entity_name;
		            		$warearr['entity_code'] = $ware->entity_code;
		            		$warearr['entity_type_id'] = $ware->entity_type_id;
				            $warearr['parent_entity_id'] = $ware->parent_entity_id;
				            $warearr['capacity'] = $ware->capacity;
				            $warearr['capacity_uom_id'] = $ware->capacity_uom_id;
				            //$warearr['location_id'] = $ware->location_id;
				            $warearr['dimension_id'] = $ware->dimension_id;
				            //$warearr['org_id'] = $ware->org_id;
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
			        
			        $result = array("status"=>1,"message"=>"Success","data"=>$totalResult);
	            
           return json_encode($result);
		
	}

public function getUomData(){

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
            		->select('id','code','description','uom_group_id')	            	
	            	->get();
	            	 $uom_array = array();
                     if(!empty($uoms)){
                        foreach ($uoms as $uom){
                           $uom_array['uom_id'] = $uom->id;
                           $uom_array['uom_code'] = $uom->code;
                           $uom_array['uom_description'] = $uom->description;
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
                   $arr['Data'] = $finalarray;
                   return json_encode($arr);
                   }
	
	

	public function allentities()
	{
		parent::Breadcrumbs(array('Home'=>'/','Entities'=>'#'));
		$currentUserId = \Session::get('userId');
        \Log::info($currentUserId);
        $manufacturerId = DB::table('users')->where('user_id', $currentUserId)->pluck('customer_id');
        \Log::info($manufacturerId);
        $mfgDetails=DB::table('wms_entities')
            ->join('eseal_customer','wms_entities.org_id','=','eseal_customer.customer_id')
            ->where('wms_entities.entity_type_id',0)
            ->get(array('eseal_customer.brand_name','wms_entities.org_id'));
		return View::make('entities.allentities')->with('mfgDetails',$mfgDetails)->with('manufacturerId',$manufacturerId);
	}
	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create($entity_type_id,$parent_entity_id,$org_id,$location_id='',$ware_id='')
	{
         
        $capacity_uom = $this->commonRepo->getLookupData('Capacity UOM');
		$dimension_uom = $this->commonRepo->getLookupData('Length UOM');
		
		$location_type_id = DB::table('location_types')
                        ->where('manufacturer_id',$org_id)
           				->whereIn('location_type_name',['warehouse','Depot','Plant'])
        				->lists('location_type_id');
        if(empty($location_type_id))
        	$location_type_id = '';

    if(empty($location_id)){    			
		$locations = DB::table('locations')->whereIn('location_type_id',$location_type_id)->where('manufacturer_id',$org_id)->lists('location_name', 'location_id');
        
        $locations = ['' => 'Select Location'] + $locations;
        
    }
    else{
     $locations = DB::table('locations')->where('location_id',$location_id)->lists('location_name','location_id');
    }
		if($entity_type_id==6001)
		{
			
			$entity_name = 'Warehouse';
			$entity_code = 'Warehouse';
			$warelength ='';
			$warewidth = '';
			$wareheight = '';
			$warearea = '';
			$floorsumheight = '';
			$floorsumlength = '';
			$floorsumarea = '';
			$floorsumwidth = '';
			$parent_capacity = '';
			$child_capacity = '';
		}
		else if($entity_type_id==6002)
		{
			$warelength = DB::table('wms_dimensions')
                ->selectRaw('sum(wms_dimensions.length) as lengthsum')
                ->leftjoin('wms_entities','wms_entities.id','=','wms_dimensions.entity_id')
                ->where('wms_dimensions.entity_id',$parent_entity_id)
                ->get();

            $floorid = DB::table('wms_entities')
	                    ->select(array(DB::raw('group_concat(id) as floor_id')))
	                    ->where(array('parent_entity_id'=>$parent_entity_id, 'entity_type_id'=>$entity_type_id))
	                    ->get();

	        if(isset($floorid[0]->floor_id) && !empty($floorid)){
                $parent = array($floorid[0]->floor_id);            
                $parent = implode(',', $parent);
                $floorsumlength = DB::select(DB::Raw("select sum(length) as floorlengthsum from wms_dimensions where entity_id in ($parent)"));
                $floorsumwidth = DB::select(DB::Raw("select sum(width) as floorwidthsum from wms_dimensions where entity_id in ($parent)"));
                $floorsumheight = DB::select(DB::Raw("select sum(height) as floorheightsum from wms_dimensions where entity_id in ($parent)"));
//WareArea utilised                
                $floorsumarea = DB::select(DB::Raw("select sum(area) as floorareasum from wms_dimensions where entity_id in ($parent)"));;
//WareArea utilised                
            }else{
	        	$floorsumlength = 0;
	        	$floorsumwidth = 0;
	        	$floorsumheight = 0;
	        	$floorsumarea = 0;
	        }

	        $child_capacity = DB::select(DB::Raw("select sum(capacity) as total_child_capacity from wms_entities where parent_entity_id = ".$parent_entity_id));

	        $warewidth = DB::table('wms_dimensions')
		                ->selectRaw('sum(wms_dimensions.width) as widthsum')
		                ->leftjoin('wms_entities','wms_entities.id','=','wms_dimensions.entity_id')
		                ->where('wms_dimensions.entity_id',$parent_entity_id)
		                ->get();
		    $wareheight = DB::table('wms_dimensions')
		                ->selectRaw('sum(wms_dimensions.height) as heightsum')
		                ->leftjoin('wms_entities','wms_entities.id','=','wms_dimensions.entity_id')
		                ->where('wms_dimensions.entity_id',$parent_entity_id)
		                ->get();
//warearea
			$warearea = DB::table('wms_dimensions')
		                ->selectRaw('sum(wms_dimensions.area) as areasum')
		                ->leftjoin('wms_entities','wms_entities.id','=','wms_dimensions.entity_id')
		                ->where('wms_dimensions.entity_id',$parent_entity_id)
		                ->get();		    
//warearea
		    $parent_capacity = DB::table('wms_entities')->where('id',$parent_entity_id)->pluck('capacity');
		    $entity_name = 'Floor';
			$entity_code = 'Floor';
		}
		else if($entity_type_id==6003)
		{
			$warelength = DB::table('wms_dimensions')
                ->selectRaw('sum(wms_dimensions.length) as lengthsum')
                ->leftjoin('wms_entities','wms_entities.id','=','wms_dimensions.entity_id')
                ->where('wms_dimensions.entity_id',$parent_entity_id)
                ->get();

            $floorid = DB::table('wms_entities')
	                    ->select(array(DB::raw('group_concat(id) as floor_id')))
	                    ->where(array('parent_entity_id'=>$parent_entity_id, 'entity_type_id'=>$entity_type_id))
	                    ->get();

	        if(isset($floorid[0]->floor_id) && !empty($floorid)){
                $parent = array($floorid[0]->floor_id);            
                $parent = implode(',', $parent);
                $floorsumlength = DB::select(DB::Raw("select sum(length) as floorlengthsum from wms_dimensions where entity_id in ($parent)"));
                $floorsumwidth = DB::select(DB::Raw("select sum(width) as floorwidthsum from wms_dimensions where entity_id in ($parent)"));
                $floorsumheight = DB::select(DB::Raw("select sum(height) as floorheightsum from wms_dimensions where entity_id in ($parent)"));
//WareArea utilised                
                $floorsumarea = DB::select(DB::Raw("select sum(area) as floorareasum from wms_dimensions where entity_id in ($parent)"));;
//WareArea utilised                 
            }else{
	        	$floorsumlength = 0;
	        	$floorsumwidth = 0;
	        	$floorsumheight = 0;
	        	$floorsumarea = 0;
	        }

	        $child_capacity = DB::select(DB::Raw("select sum(capacity) as total_child_capacity from wms_entities where parent_entity_id = ".$parent_entity_id));

	        $warewidth = DB::table('wms_dimensions')
		                ->selectRaw('sum(wms_dimensions.width) as widthsum')
		                ->leftjoin('wms_entities','wms_entities.id','=','wms_dimensions.entity_id')
		                ->where('wms_dimensions.entity_id',$parent_entity_id)
		                ->get();
		    $wareheight = DB::table('wms_dimensions')
		                ->selectRaw('sum(wms_dimensions.height) as heightsum')
		                ->leftjoin('wms_entities','wms_entities.id','=','wms_dimensions.entity_id')
		                ->where('wms_dimensions.entity_id',$parent_entity_id)
		                ->get();
//warearea
			$warearea = DB::table('wms_dimensions')
		                ->selectRaw('sum(wms_dimensions.area) as areasum')
		                ->leftjoin('wms_entities','wms_entities.id','=','wms_dimensions.entity_id')
		                ->where('wms_dimensions.entity_id',$parent_entity_id)
		                ->get();		                
//warearea
		    $parent_capacity = DB::table('wms_entities')->where('id',$parent_entity_id)->pluck('capacity');
			$entity_name = 'Zone/Dock';
			$entity_code = 'Zone/Dock';
		}
		else if($entity_type_id==6004)
		{
			$warelength = DB::table('wms_dimensions')
                ->selectRaw('sum(wms_dimensions.length) as lengthsum')
                ->leftjoin('wms_entities','wms_entities.id','=','wms_dimensions.entity_id')
                ->where('wms_dimensions.entity_id',$parent_entity_id)
                ->get();
                
            $floorid = DB::table('wms_entities')
	                    ->select(array(DB::raw('group_concat(id) as floor_id')))
	                    ->where(array('parent_entity_id'=>$parent_entity_id, 'entity_type_id'=>$entity_type_id))
	                    ->get();
            $zoneid = DB::table('wms_entities')
	                    ->where('id',$parent_entity_id)
	                    ->pluck('parent_entity_id');
            $getfloor = DB::table('wms_entities')
	                    ->where('id',$zoneid)
	                    ->pluck('id');

            $wareheight = DB::select(DB::Raw("select height as heightsum from wms_dimensions where entity_id = ".$getfloor));

	        if(isset($floorid[0]->floor_id) && !empty($floorid)){
                $parent = array($floorid[0]->floor_id);            
                $parent = implode(',', $parent);
                $floorsumlength = DB::select(DB::Raw("select sum(length) as floorlengthsum from wms_dimensions where entity_id in ($parent)"));
                $floorsumwidth = DB::select(DB::Raw("select sum(width) as floorwidthsum from wms_dimensions where entity_id in ($parent)"));
                $floorsumheight = DB::select(DB::Raw("select sum(height) as floorheightsum from wms_dimensions where entity_id in ($parent)"));
//WareArea utilised                
                $floorsumarea = DB::select(DB::Raw("select sum(area) as floorareasum from wms_dimensions where entity_id in ($parent)"));;
//WareArea utilised                 
            }else{
	        	$floorsumlength = 0;
	        	$floorsumwidth = 0;
	        	$floorsumheight = 0;
	        	$floorsumarea = 0;
	        }

	        $child_capacity = DB::select(DB::Raw("select sum(capacity) as total_child_capacity from wms_entities where parent_entity_id = ".$parent_entity_id));

	        $warewidth = DB::table('wms_dimensions')
		                ->selectRaw('sum(wms_dimensions.width) as widthsum')
		                ->leftjoin('wms_entities','wms_entities.id','=','wms_dimensions.entity_id')
		                ->where('wms_dimensions.entity_id',$parent_entity_id)
		                ->get();
//warearea
			$warearea = DB::table('wms_dimensions')
		                ->selectRaw('sum(wms_dimensions.area) as areasum')
		                ->leftjoin('wms_entities','wms_entities.id','=','wms_dimensions.entity_id')
		                ->where('wms_dimensions.entity_id',$parent_entity_id)
		                ->get();		                
//warearea
		    $parent_capacity = DB::table('wms_entities')->where('id',$parent_entity_id)->pluck('capacity');
		    $entity_name = 'Rack';
			$entity_code = 'Rack';
		}
		else if($entity_type_id==6005)
		{
			$warelength = DB::table('wms_dimensions')
                ->selectRaw('sum(wms_dimensions.length) as lengthsum')
                ->leftjoin('wms_entities','wms_entities.id','=','wms_dimensions.entity_id')
                ->where('wms_dimensions.entity_id',$parent_entity_id)
                ->get();

            $floorid = DB::table('wms_entities')
	                    ->select(array(DB::raw('group_concat(id) as floor_id')))
	                    ->where(array('parent_entity_id'=>$parent_entity_id, 'entity_type_id'=>$entity_type_id))
	                    ->get();

	        if(isset($floorid[0]->floor_id) && !empty($floorid)){
                $parent = array($floorid[0]->floor_id);            
                $parent = implode(',', $parent);
                $floorsumlength = DB::select(DB::Raw("select sum(length) as floorlengthsum from wms_dimensions where entity_id in ($parent)"));
                $floorsumwidth = DB::select(DB::Raw("select sum(width) as floorwidthsum from wms_dimensions where entity_id in ($parent)"));
                $floorsumheight = DB::select(DB::Raw("select sum(height) as floorheightsum from wms_dimensions where entity_id in ($parent)"));
//WareArea utilised                
                $floorsumarea = DB::select(DB::Raw("select sum(area) as floorareasum from wms_dimensions where entity_id in ($parent)"));;
//WareArea utilised                 
            }else{
	        	$floorsumlength = 0;
	        	$floorsumwidth = 0;	
	        	$floorsumheight = 0;
	        	$floorsumarea = 0;
	        }

	        $child_capacity = DB::select(DB::Raw("select sum(capacity) as total_child_capacity from wms_entities where parent_entity_id = ".$parent_entity_id));

	        $warewidth = DB::table('wms_dimensions')
		                ->selectRaw('sum(wms_dimensions.width) as widthsum')
		                ->leftjoin('wms_entities','wms_entities.id','=','wms_dimensions.entity_id')
		                ->where('wms_dimensions.entity_id',$parent_entity_id)
		                ->get();
			$openzone = DB::table('wms_entities')
						->where('id',$parent_entity_id)
						->pluck('entity_type_id');
			if($openzone == 6006 || $openzone == 6007)
			{
				$ozid = DB::table('wms_entities')
						->where('id',$parent_entity_id)
						->pluck('parent_entity_id');

				$wareheight = DB::table('wms_dimensions')
			                ->selectRaw('sum(wms_dimensions.height) as heightsum')
			                ->leftjoin('wms_entities','wms_entities.id','=','wms_dimensions.entity_id')
			                ->where('wms_dimensions.entity_id',$ozid)
			                ->get();
			}else{
		    $wareheight = DB::table('wms_dimensions')
		                ->selectRaw('sum(wms_dimensions.height) as heightsum')
		                ->leftjoin('wms_entities','wms_entities.id','=','wms_dimensions.entity_id')
		                ->where('wms_dimensions.entity_id',$parent_entity_id)
		                ->get();
		    }
//warearea		    
			$warearea = DB::table('wms_dimensions')
		                ->selectRaw('sum(wms_dimensions.area) as areasum')
		                ->leftjoin('wms_entities','wms_entities.id','=','wms_dimensions.entity_id')
		                ->where('wms_dimensions.entity_id',$parent_entity_id)
		                ->get();		    
//warearea
		    $parent_capacity = DB::table('wms_entities')->where('id',$parent_entity_id)->pluck('capacity');
		    $entity_name = 'Bin';
			$entity_code = 'Bin';
		}
		else
		{
			$entity_name = 'Entity';
			$entity_code = 'Entity';
		}
    	
		   if(empty($warelength)){
            	$warelength=0;
           }else{
            	if(empty($warelength[0]->lengthsum))
	            {
	            	$warelength[0]->lengthsum = 0;
	            	$warelength = $warelength[0]->lengthsum;
	            }else{
	            	$warelength=$warelength[0]->lengthsum;
	        	}
           }
           if(empty($warewidth)){
            	$warewidth=0;
           }else{
            	if(empty($warewidth[0]->widthsum))
	            {
	            	$warewidth[0]->widthsum = 0;
	            	$warewidth = $warewidth[0]->widthsum;
	            }else{
	            	$warewidth=$warewidth[0]->widthsum;
	        	}
           }
           if(empty($wareheight)){
            	$wareheight=0;
           }else{
            	if(empty($wareheight[0]->heightsum))
	            {
	            	$wareheight[0]->heightsum = 0;
	            	$wareheight = $wareheight[0]->heightsum;
	            }else{
	            	$wareheight=$wareheight[0]->heightsum;
	        	}
           }
//warearea
           if(empty($warearea)){
            	$warearea=0;
           }else{
            	if(empty($warearea[0]->areasum))
	            {
	            	$warearea[0]->areasum = 0;
	            	$warearea = $warearea[0]->areasum;
	            }else{
	            	$warearea=$warearea[0]->areasum;
	        	}
           }
//warearea           
           if(empty($floorsumlength)){
            	$floorsumlength=0;
           }else{
            	if(empty($floorsumlength[0]->floorlengthsum))
	            {
	            	$floorsumlength[0]->floorlengthsum = 0;
	            	$floorsumlength = $floorsumlength[0]->floorlengthsum;
	            }else{
	            	$floorsumlength=$floorsumlength[0]->floorlengthsum;
	        	}
           }
           if(empty($floorsumwidth)){
            	$floorsumwidth=0;
           }else{
            	if(empty($floorsumwidth[0]->floorwidthsum))
	            {
	            	$floorsumwidth[0]->floorwidthsum = 0;
	            	$floorsumwidth = $floorsumwidth[0]->floorwidthsum;
	            }else{
	            	$floorsumwidth=$floorsumwidth[0]->floorwidthsum;
	        	}
           }
           if(empty($floorsumheight)){
            	$floorsumheight=0;
           } else{
            	//$floorsumheight=$floorsumheight[0]->floorheightsum;
            	if(empty($floorsumheight[0]->floorheightsum))
	            {
	            	$floorsumheight[0]->floorheightsum = 0;
	            	$floorsumheight = $floorsumheight[0]->floorheightsum;
	            }else{
	            	$floorsumheight = $floorsumheight[0]->floorheightsum;
	        	}
           }
//wareArea utilisation
           if(empty($floorsumarea)){
            	$floorsumarea=0;
           }else{
            	if(empty($floorsumarea[0]->floorareasum))
	            {
	            	$floorsumarea[0]->floorareasum = 0;
	            	$floorsumarea = $floorsumarea[0]->floorareasum;
	            }else{
	            	$floorsumarea=$floorsumarea[0]->floorareasum;
	        	}
           }
//wareArea utilisation                      
           if(empty($parent_capacity)){
            	$parent_capacity=0;
           }
           if(empty($child_capacity)){
            	$child_capacity=0;
           }else{
           		if(empty($child_capacity[0]->total_child_capacity))
	            {
	            	$child_capacity[0]->total_child_capacity = 0;
	            	$child_capacity = $child_capacity[0]->total_child_capacity;
	            }else{
	            	$child_capacity=$child_capacity[0]->total_child_capacity;
	        	}
           }

        return View::make('entities.create', compact("capacity_uom","org_id","ware_id","dimension_uom","locations","entity_type_id","parent_entity_id","entity_name","entity_code","warelength","warewidth","wareheight","floorsumlength","floorsumheight","floorsumwidth","parent_capacity","child_capacity","warearea","floorsumarea"));	
	}
	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */


	
public function store()
	{
		    	
		$zone_type_id = Input::get('zone_type_id');
		
		if($zone_type_id)
		{
			if($zone_type_id==1)
				$entity_type_id=6003;
			elseif($zone_type_id==2)
				$entity_type_id=6006;
			elseif($zone_type_id==4)
				$entity_type_id=6008; //should check if its Dock-(id-8)functionality of it
            elseif($zone_type_id==3)
            	$entity_type_id=6007;
            else
            	$entity_type_id=6003;	
		}
		else
		{
			$entity_type_id=Input::get('entity_type_id');	
		}
		
        $data = Input::get();

        if(!isset($data['xco'])){
	       $data['xco'] = '';
        }	
        if(!isset($data['yco'])){
	       $data['yco'] = '';
        }	
        if(!isset($data['zco'])){
	       $data['zco'] = '';
        }	

        $cpty = Input::get('capacity');
        $uom_id = Input::get('capacity_uom_id');
        $capacity = $this->kgConverter($uom_id,$cpty);
	    
	    DB::table('wms_entities')->insert([
	    	'entity_name' => Input::get('entity_name'),
			'entity_type_id' => $entity_type_id,
			'entity_location' => Input::get('entity_location'),
			'location_id' => Input::get('location_id'),
			'ware_id' => Input::get('ware_id'),
			'org_id' => Input::get('org_id'),
			//'capacity' => Input::get('capacity'),
			'capacity' =>$capacity['capacity'],
			'xco' => $data['xco'],
			'yco' => $data['yco'],
			'zco' => $data['zco'],
			'capacity_uom_id' => Input::get('capacity_uom_id'),
			'parent_entity_id'=> Input::get('parent_entity_id'),
			'status' => 1,
		]);	
        	
  		$entity_id = DB::getPdo()->lastInsertId();
  		
  		if($entity_type_id == 6005)
  		{
  			 $parent_bin_id = Input::get('parent_entity_id');
  			 $rack_value_id = DB::table('wms_entities')
			  			        ->where('id',$parent_bin_id)
			  			        ->pluck('parent_entity_id');
			 $zone_value_id = DB::table('wms_entities')
			  			        ->where('id',$rack_value_id)
			  			        ->pluck('parent_entity_id');
  			 //$flooridvalue = $this->getFloorValue($parent_bin_id);
  			 $bins =  new Bins;
	         $bins->entity_id = $entity_id;
	         $bins->storage_bin_name = Input::get('entity_name');
	         $bins->storage_bin_id = Input::get('entity_location');
	         $bins->status = 'Empty';
	         $bins->org_id = Input::get('org_id');
	         $bins->ware_id = Input::get('ware_id');
	         $bins->floor_id = $zone_value_id;
	         //$bins->storage_capacity = Input::get('capacity');
	         $bins->storage_capacity = $capacity['capacity'];
	         $bins->is_allocated = 0;
	         $bins->save(); 
        }
        
        if($entity_type_id == 6001)
		{
			$entity_code = 'W'.$entity_id;
		}
		else if($entity_type_id==6003)
		{
			$entity_code = 'Z'.$entity_id;
		}
		else if($entity_type_id==6004)
		{
			$entity_code = 'R'.$entity_id;
		}
		else if($entity_type_id==6005)
		{
			$entity_code = 'B'.$entity_id;
		}

		else if($entity_type_id==6006)
		{
			$entity_code = 'Oz'.$entity_id;
		}

		else if($entity_type_id==6007)
		{
			$entity_code = 'Paz'.$entity_id;
		}
		else if($entity_type_id==6008)
		{
			$entity_code = 'D'.$entity_id;
		}
		else
		{
            $entity_code = 'F'.$entity_id;
		}
		 
		 $entities = Entities::find($entity_id);
		 $entities->entity_code = $entity_code;
		 $entities->save(); 
		//converting UOM into feet before saving in dimensions
		$dimUom = Input::get('uom_id');
		$dimLen = Input::get('length');
		$dimWid = Input::get('width');
		$dimDep = Input::get('depth');
		$dimHei = Input::get('height');
		$dimFeet=$this->feetConverter($dimUom,$dimLen,$dimHei,$dimWid,$dimDep);
		//converting UOM into feet before saving in dimensions
        //Storing into dimensions table
		$dimensions = new Dimension;
		$dimensions->entity_id = $entity_id;
		/*$dimensions->height = Input::get('height');
		$dimensions->width = Input::get('width');
		$dimensions->depth = Input::get('depth');
		$dimensions->length = Input::get('length');
		$dimensions->area = Input::get('area');*/
		$dimensions->height = $dimFeet['height'];
		$dimensions->width = $dimFeet['width'];
		$dimensions->depth = $dimFeet['depth'];
		$dimensions->length = $dimFeet['length'];		
		$dimensions->area = $dimFeet['area'];
		$dimensions->uom_id = Input::get('uom_id');
		$dimensions->save();

		$dimensions_entity = DB::table('wms_dimensions')->select('id','entity_id')
								->where('entity_id',$entity_id)->get();		
		$entity_dimension_update = DB::table('wms_entities')
									->where('id',$dimensions_entity[0]->entity_id)
									->update(array('dimension_id'=>$dimensions_entity[0]->id));

		if($entity_type_id==6006 || $entity_type_id==6007)
		{
			
			DB::table('wms_entities')->insert([

			'entity_name' => 'Openbin',
			'entity_type_id' => 6005,
			'entity_location' => Input::get('entity_location'),
			'location_id' => Input::get('location_id'),
			'ware_id' => Input::get('ware_id'),
			'org_id' => Input::get('org_id'),
			'capacity' => 0,
			'capacity_uom_id' => Input::get('capacity_uom_id'),
			'parent_entity_id'=> $entity_id,
			'status' => 1,
			]);

			$bin_entity_id = DB::getPdo()->lastInsertId();

			$floor_value_id = DB::table('wms_entities')
			  			        ->where('id',$entity_id)
			  			        ->pluck('parent_entity_id');
			
			$bins =  new Bins;
            $bins->entity_id = $bin_entity_id;
            $bins->storage_bin_name = 'Openbin';
            $bins->storage_bin_id = Input::get('entity_location');
            $bins->status = 'Empty';
            $bins->org_id = Input::get('org_id');
            $bins->ware_id = Input::get('ware_id');
            $bins->floor_id = $floor_value_id;
            $bins->storage_capacity = 0;
            $bins->is_allocated = 0;
            $bins->save(); 
	        
			$bin_entity_code = 'B'.$bin_entity_id;
			
			 
			 $entities = Entities::find($bin_entity_id);
			 $entities->entity_code = $bin_entity_code;
			 $entities->save(); 

	        //Storing into dimensions table
			$dimensions = new Dimension;
			$dimensions->entity_id = $bin_entity_id;
			$dimensions->height = 0; /*Input::get('height')*/
			$dimensions->width = 0;/*Input::get('width')*/
			$dimensions->depth = 0;/*Input::get('depth')*/
			$dimensions->length = 0;/*Input::get('length')*/
			$dimensions->area = 0;/*Input::get('area')*/
			$dimensions->uom_id = Input::get('uom_id');
			$dimensions->save();

			$dimensions_entity_bin = DB::table('wms_dimensions')->select('id','entity_id')
								->where('entity_id',$bin_entity_id)->get();		
			$entity_dimension_update = DB::table('wms_entities')
									->where('id',$dimensions_entity_bin[0]->entity_id)
									->update(array('dimension_id'=>$dimensions_entity_bin[0]->id));
		}
		else{
		  $children_parent_entity_id=Input::get('children_parent_entity_id');
          if($children_parent_entity_id){
			$children = Entities::where('parent_entity_id',$children_parent_entity_id)->get(); 
			//return $children;
			$rackarr = array();
			$finalrackarr = array();
			$totalResult = array();
			if(count($children)){
			
			foreach($children as $children){
            
            $child_id=$children->id;
            $children_dimension=Dimension::where('entity_id',$child_id)->get();
            
            //$grand=array();
            $grand = DB::table('wms_entities')->where('parent_entity_id','=',$child_id)->get();
            
            //return $grand; 
            $binarr = array();
            $finalbinarr = array();
            $rackarr['entity_id'] = $children->id;
			$rackarr['entity_name'] = $children->entity_name;
			$rackarr['entity_code'] = $children->entity_code;
			$rackarr['entity_type_id'] = $children->entity_type_id;
	        $rackarr['parent_entity_id'] = $children->parent_entity_id;
	        $rackarr['capacity'] = $children->capacity;
	        $rackarr['location_id'] = $children->location_id;
	        $rackarr['ware_id'] = $children->ware_id;
    		$rackarr['org_id'] = $children->org_id;
	        $rackarr['capacity_uom_id'] = $children->capacity_uom_id;
	        //$rackarr['location_id'] = $rack->location_id;
	        $rackarr['dimension_id'] = $children->dimension_id;
            
            $rackarr['height'] = $children_dimension[0]->height;
			$rackarr['width'] = $children_dimension[0]->width;
			$rackarr['depth'] = $children_dimension[0]->depth;
			$rackarr['length'] = $children_dimension[0]->length;
			$rackarr['area']= $children_dimension[0]->area;
			$rackarr['uom_id'] = $children_dimension[0]->uom_id;
	       
              
			//$totalResult[] = $rackarr; 
           DB::table('wms_entities')->insert([

			'entity_name' => $rackarr['entity_name'],
			'entity_type_id' => $rackarr['entity_type_id'],
			'location_id' => $rackarr['location_id'],
			'ware_id' => $rackarr['ware_id'],
			'org_id' => $rackarr['org_id'],
			'capacity' => $rackarr['capacity'],
			'capacity_uom_id' => $rackarr['capacity_uom_id'],
			'parent_entity_id'=> $entity_id,
			'status' => 1,
		]);

        $fin_id = DB::getPdo()->lastInsertId();
        if($rackarr['entity_type_id'] == 6001)
		{
			
			$entity_code = 'W'.$fin_id;

		}
		else if($rackarr['entity_type_id']==6003)
		{
			
			$entity_code = 'Z'.$fin_id;
		}
		else if($rackarr['entity_type_id']==6004)
		{
			
			$entity_code = 'R'.$fin_id;
		}
		else if($rackarr['entity_type_id']==6005)
		{
			
			$entity_code = 'B'.$fin_id;
		}

		else if($rackarr['entity_type_id']==6006)
		{
			
			$entity_code = 'Oz'.$fin_id;
		}

		else if($rackarr['entity_type_id']==6007)
		{
			
			$entity_code = 'Paz'.$fin_id;
		}
		else if($rackarr['entity_type_id']==6002)
		{
			
			$entity_code = 'F'.$fin_id;
		}
		else{

            $entity_code = 'D'.$fin_id;

		}
		 
		 $entities = Entities::find($fin_id);
		 $entities->entity_code = $entity_code;
		 $entities->save(); 

		    $dimensions = new Dimension;
			$dimensions->entity_id = $fin_id;
			$dimensions->height = $rackarr['height'];
			$dimensions->width = $rackarr['width'];
			$dimensions->depth = $rackarr['depth'];
			$dimensions->length = $rackarr['length'];
			$dimensions->area = $rackarr['area'];
			$dimensions->uom_id = $rackarr['uom_id'];
			$dimensions->save();

			$dimensions_entity_rack = DB::table('wms_dimensions')->select('id','entity_id')
								->where('entity_id',$fin_id)->get();		
			$entity_dimension_update_rack = DB::table('wms_entities')
									->where('id',$dimensions_entity_rack[0]->entity_id)
									->update(array('dimension_id'=>$dimensions_entity_rack[0]->id));


	     $binarr = array();
	     $finalbinarr = array();
          
          if(count($grand)){
            
            foreach($grand as $grand){ 
            

            $grand_child_id=$grand->id;
            $grand_children_dimension=Dimension::where('entity_id',$grand_child_id)->get();
            
            $binarr['entity_id'] = $grand->id;
    		$binarr['entity_name'] = $grand->entity_name;
    		$binarr['entity_code'] = $grand->entity_code;
    		$binarr['entity_type_id'] = $grand->entity_type_id;
    		$binarr['parent_entity_id'] = $fin_id;
    		$binarr['capacity'] = $grand->capacity;
    		$binarr['location_id'] = $grand->location_id;
    		$binarr['ware_id'] = $grand->ware_id;
    		$binarr['org_id'] = $grand->org_id;
    		$binarr['capacity_uom_id'] = $grand->capacity_uom_id;
    		//$binarr['location_id'] = $bin->location_id;
    		$binarr['dimension_id'] = $grand->dimension_id;
    	    
    	    $binarr['height'] = $grand_children_dimension[0]->height;
			$binarr['width'] = $grand_children_dimension[0]->width;
			$binarr['depth'] = $grand_children_dimension[0]->depth;
			$binarr['length'] = $grand_children_dimension[0]->length;
			$binarr['area']= $grand_children_dimension[0]->area;
			$binarr['uom_id'] = $grand_children_dimension[0]->uom_id;
             
			 DB::table('wms_entities')->insert([

			'entity_name' => $binarr['entity_name'],
			'entity_type_id' => $binarr['entity_type_id'],
			'location_id' => $binarr['location_id'],
			'ware_id' => $binarr['ware_id'],
			'org_id' => $binarr['org_id'],
			'capacity' => $binarr['capacity'],
			'capacity_uom_id' => $binarr['capacity_uom_id'],
			'parent_entity_id'=> $fin_id,
			'status' => 1,
		]);


           //return $copyzone->entity_type_id;
		$tin_id = DB::getPdo()->lastInsertId();
        if($binarr['entity_type_id'] == 6001)
		{
			
			$entity_code = 'W'.$tin_id;

		}
		else if($binarr['entity_type_id']==6003)
		{
			
			$entity_code = 'Z'.$tin_id;
		}
		else if($binarr['entity_type_id']==6004)
		{
			
			$entity_code = 'R'.$tin_id;
		}
		else if($binarr['entity_type_id']==6005)
		{
			
			$entity_code = 'B'.$tin_id;
		}

		else if($binarr['entity_type_id']==6006)
		{
			
			$entity_code = 'Oz'.$tin_id;
		}

		else if($binarr['entity_type_id']==6007)
		{
			
			$entity_code = 'Paz'.$tin_id;
		}
		else if($binarr['entity_type_id']==6002)
		{
			
			$entity_code = 'F'.$tin_id;
		}
		else{
		     $entity_code = 'D'.$tin_id;	
		}
		 
		 $entities = Entities::find($tin_id);
		 $entities->entity_code = $entity_code;
		 $entities->save();            								
		 $dimensions = new Dimension;
		 $dimensions->entity_id = $tin_id;
		$dimensions->height = $binarr['height'];
		$dimensions->width = $binarr['width'];
		$dimensions->depth = $binarr['depth'];
		$dimensions->length = $binarr['length'];
		$dimensions->area = $binarr['area'];
		$dimensions->uom_id = $binarr['uom_id'];
		$dimensions->save();

		$dimensions_entity_binarr = DB::table('wms_dimensions')->select('id','entity_id')
								->where('entity_id',$tin_id)->get();		
		$entity_dimension_update_binarr = DB::table('wms_entities')
									->where('id',$dimensions_entity_binarr[0]->entity_id)
									->update(array('dimension_id'=>$dimensions_entity_binarr[0]->id));
           
          }
        }
           
         }
	   }
     }
   }

		
        //$bindetails=Input::get('bin_height');
        $numberofbins=Input::get('no_of_bins');
      	// return $bindetails;
       if($numberofbins)
       {        
       		$find = Entities::find($entity_id);
	        $id=6005;
	        $parent_entity_id=$find->id;
	        for($i=0;$i<$numberofbins;$i++)
	        {
		        $binCap = Input::get('bin_capacity');
		        $binUom = Input::get('bin_capacity_uom_id');
		        $bin_capacity = $this->kgConverter($binCap,$binUom);
		        DB::table('wms_entities')->insert([
		            
		            'entity_name' => Input::get('bin_name'),
					'entity_type_id' => $id,
					//'capacity' => Input::get('bin_capacity'),
					'capacity' => $bin_capacity['capacity'],
					'org_id' => Input::get('org_id'),
					'location_id' => Input::get('location_id'),
					'ware_id' => Input::get('ware_id'),
					'capacity_uom_id' => Input::get('bin_capacity_uom_id'),
					'parent_entity_id'=> $parent_entity_id,
					'status' => 1,
				]);

		      	$entity_id = DB::getPdo()->lastInsertId();
		        
		        if($id == 6001)
				{
					
					$entity_code = 'W'.$entity_id;

				}
				else if($id==6003)
				{
					
					$entity_code = 'Z'.$entity_id;
				}
				else if($id==6004)
				{
					
					$entity_code = 'R'.$entity_id;
				}
				else if($id==6005)
				{
					
					$entity_code = 'B'.$entity_id;
				}
				else if($id==6002)
				{
					
					$entity_code = 'F'.$entity_id;
				}
				else{
				    $entity_code = 'D'.$entity_id;	
				}
		 
		
				$entities = Entities::find($entity_id);
				$entities->entity_code = $entity_code;
				$entities->save(); 
				//converting UOM into feet before saving in dimensions
				$uomDim = Input::get('uom_id');
				$lenDim = Input::get('length');
				$widDim = Input::get('bin_width');
				$depDim = Input::get('bin_depth');
				$heiDim = Input::get('bin_height');
				$feetDim=$this->feetConverter($uomDim,$lenDim,$heiDim,$widDim,$depDim);
				//converting UOM into feet before saving in dimensions								
				$dimensions = new Dimension;
				$dimensions->entity_id = $entity_id;
				/*$dimensions->height = Input::get('bin_height');
				$dimensions->width = Input::get('bin_width');
				$dimensions->depth = Input::get('bin_depth');*/
				$dimensions->height = $feetDim['height'];
				$dimensions->width = $feetDim['width'];
				$dimensions->depth = $feetDim['depth'];
				//$dimensions->length = Input::get('length');
				//$dimensions->area = Input::get('area');
				$dimensions->uom_id = Input::get('bin_dimension_id');
				$dimensions->save();

				$dimensions_entity_numbin = DB::table('wms_dimensions')->select('id','entity_id')
								->where('entity_id',$entity_id)->get();		
				$entity_dimension_update_binarr = DB::table('wms_entities')
									->where('id',$dimensions_entity_numbin[0]->entity_id)
									->update(array('dimension_id'=>$dimensions_entity_numbin[0]->id));

       		}
		}
	
	  	return Redirect::to('entities')->withFlashMessage('Created Successfully.'); 	
	}
	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function edit($entity_id)
	{

        $entity_id = $this->roleRepo->decodeData($entity_id);
		$entities = Entities::find($entity_id);
		//return $entities;
		$ware_id = $entities->ware_id;
		$capUom = $entities->capacity_uom_id;
		$cap = $entities->capacity;
		$capacity=$this->fromKgConverter($capUom,$cap);
		$entities->capacity = $capacity['capacity'];
		//return $entities;
		$entity_type_id = $entities->entity_type_id; 
		$location_id = $entities->location_id;

      	if($entity_type_id==6001)
		{
			$entity_name = 'Warehouse Name';
			$entity_code = 'Warehouse Code';
			$entity_code_val = 'W'.$entities->id;
			$warelength ='';
			$warewidth = '';
			$wareheight = '';
			$floorsumheight = '';
			$floorsumlength = '';
			$ware_id = '';
			$floorsumwidth = '';
			$warearea = '';
			$floorsumarea = '';
			$parent_capacity = '';
			$child_capacity ='';
			$childCap = DB::select(DB::Raw("select sum(capacity) as total_child_capacity from wms_entities where parent_entity_id = ".$entities->id." and id not in (".$entities->id.")"));
		}
		else if($entity_type_id==6003)
		{
			$entity_name = 'Zone Name';
			$entity_code = 'Zone Code';
			$entity_code_val = 'Z'.$entities->id;

			$warelength = DB::table('wms_dimensions')
                ->selectRaw('sum(wms_dimensions.length) as lengthsum')
                ->leftjoin('wms_entities','wms_entities.id','=','wms_dimensions.entity_id')
                ->where('wms_dimensions.entity_id',$entities->parent_entity_id)
                ->get();

            $floorid = DB::table('wms_entities')
	                    ->select(array(DB::raw('group_concat(id) as floor_id')))
	                    ->where(array('parent_entity_id'=>$entities->parent_entity_id, 'entity_type_id'=>$entities->entity_type_id))
	                    ->get();

	        if(isset($floorid[0]->floor_id) && !empty($floorid)){
                $parent = array($floorid[0]->floor_id);            
                $parent = implode(',', $parent);
                $floorsumlength = DB::select(DB::Raw("select sum(length) as floorlengthsum from wms_dimensions where entity_id in ($parent) and entity_id not in ($entities->id)"));
                $floorsumwidth = DB::select(DB::Raw("select sum(width) as floorwidthsum from wms_dimensions where entity_id in ($parent) and entity_id not in ($entities->id)"));
                $floorsumheight = DB::select(DB::Raw("select sum(height) as floorheightsum from wms_dimensions where entity_id in ($parent) and entity_id not in ($entities->id)"));
//WareArea utilised                
                $floorsumarea = DB::select(DB::Raw("select sum(area) as floorareasum from wms_dimensions where entity_id in ($parent) and entity_id not in ($entities->id)"));;
//WareArea utilised                  
            }else{
	        	$floorsumlength = 0;
	        	$floorsumwidth = 0;
	        	$floorsumheight = 0;
	        	$floorsumarea = 0;
	        }

	        $child_capacity = DB::select(DB::Raw("select sum(capacity) as total_child_capacity from wms_entities where parent_entity_id = ".$entities->parent_entity_id." and id not in (".$entities->id.")"));
			$childCap = DB::select(DB::Raw("select sum(capacity) as total_child_capacity from wms_entities where parent_entity_id = ".$entities->id." and id not in (".$entities->id.")"));	        

	        $warewidth = DB::table('wms_dimensions')
		                ->selectRaw('sum(wms_dimensions.width) as widthsum')
		                ->leftjoin('wms_entities','wms_entities.id','=','wms_dimensions.entity_id')
		                ->where('wms_dimensions.entity_id',$entities->parent_entity_id)
		                ->get();
		    $wareheight = DB::table('wms_dimensions')
		                ->selectRaw('sum(wms_dimensions.height) as heightsum')
		                ->leftjoin('wms_entities','wms_entities.id','=','wms_dimensions.entity_id')
		                ->where('wms_dimensions.entity_id',$entities->parent_entity_id)
		                ->get();
//warearea		    
			$warearea = DB::table('wms_dimensions')
		                ->selectRaw('sum(wms_dimensions.area) as areasum')
		                ->leftjoin('wms_entities','wms_entities.id','=','wms_dimensions.entity_id')
		                ->where('wms_dimensions.entity_id',$entities->parent_entity_id)
		                ->get();		    
//warearea		                
		    $parent_capacity = DB::table('wms_entities')->where('id',$entities->parent_entity_id)->pluck('capacity');
		}
		else if($entity_type_id==6004)
		{
			$entity_name = 'Rack Name';
			$entity_code = 'Rack Code';
			$entity_code_val = 'R'.$entities->id;

			$warelength = DB::table('wms_dimensions')
                ->selectRaw('sum(wms_dimensions.length) as lengthsum')
                ->leftjoin('wms_entities','wms_entities.id','=','wms_dimensions.entity_id')
                ->where('wms_dimensions.entity_id',$entities->parent_entity_id)
                ->get();
                
            $floorid = DB::table('wms_entities')
	                    ->select(array(DB::raw('group_concat(id) as floor_id')))
	                    ->where(array('parent_entity_id'=>$entities->parent_entity_id, 'entity_type_id'=>$entities->entity_type_id))
	                    ->get();
            $zoneid = DB::table('wms_entities')
	                    ->where('id',$entities->parent_entity_id)
	                    ->pluck('parent_entity_id');
            $getfloor = DB::table('wms_entities')
	                    ->where('id',$zoneid)
	                    ->pluck('id');

            $wareheight = DB::select(DB::Raw("select height as heightsum from wms_dimensions where entity_id = ".$getfloor));

	        if(isset($floorid[0]->floor_id) && !empty($floorid)){
                $parent = array($floorid[0]->floor_id);            
                $parent = implode(',', $parent);
                $floorsumlength = DB::select(DB::Raw("select sum(length) as floorlengthsum from wms_dimensions where entity_id in ($parent) and entity_id not in ($entities->id)"));
                $floorsumwidth = DB::select(DB::Raw("select sum(width) as floorwidthsum from wms_dimensions where entity_id in ($parent) and entity_id not in ($entities->id)"));
                $floorsumheight = DB::select(DB::Raw("select sum(height) as floorheightsum from wms_dimensions where entity_id in ($parent) and entity_id not in ($entities->id)"));
//WareArea utilised                
                $floorsumarea = DB::select(DB::Raw("select sum(area) as floorareasum from wms_dimensions where entity_id in ($parent) and entity_id not in ($entities->id)"));;
//WareArea utilised            
            }else{
	        	$floorsumlength = 0;
	        	$floorsumwidth = 0;
	        	$floorsumheight = 0;
	        	$floorsumarea = 0;
	        }

	        $child_capacity = DB::select(DB::Raw("select sum(capacity) as total_child_capacity from wms_entities where parent_entity_id = ".$entities->parent_entity_id." and id not in (".$entities->id.")"));

			$childCap = DB::select(DB::Raw("select sum(capacity) as total_child_capacity from wms_entities where parent_entity_id = ".$entities->id." and id not in (".$entities->id.")"));	        
	        $warewidth = DB::table('wms_dimensions')
		                ->selectRaw('sum(wms_dimensions.width) as widthsum')
		                ->leftjoin('wms_entities','wms_entities.id','=','wms_dimensions.entity_id')
		                ->where('wms_dimensions.entity_id',$entities->parent_entity_id)
		                ->get();
//warearea		    
			$warearea = DB::table('wms_dimensions')
		                ->selectRaw('sum(wms_dimensions.area) as areasum')
		                ->leftjoin('wms_entities','wms_entities.id','=','wms_dimensions.entity_id')
		                ->where('wms_dimensions.entity_id',$entities->parent_entity_id)
		                ->get();		    
//warearea		                
		    $parent_capacity = DB::table('wms_entities')->where('id',$entities->parent_entity_id)->pluck('capacity');
		}
		else if($entity_type_id==6005)
		{
			$entity_name = 'Bin Name';
			$entity_code = 'Bin Code';
			$entity_code_val = 'B'.$entities->id;

			$warelength = DB::table('wms_dimensions')
                ->selectRaw('sum(wms_dimensions.length) as lengthsum')
                ->leftjoin('wms_entities','wms_entities.id','=','wms_dimensions.entity_id')
                ->where('wms_dimensions.entity_id',$entities->parent_entity_id)
                ->get();

            $floorid = DB::table('wms_entities')
	                    ->select(array(DB::raw('group_concat(id) as floor_id')))
	                    ->where(array('parent_entity_id'=>$entities->parent_entity_id, 'entity_type_id'=>$entities->entity_type_id))
	                    ->get();

	        if(isset($floorid[0]->floor_id) && !empty($floorid)){
                $parent = array($floorid[0]->floor_id);            
                $parent = implode(',', $parent);
                $floorsumlength = DB::select(DB::Raw("select sum(length) as floorlengthsum from wms_dimensions where entity_id in ($parent) and entity_id not in ($entities->id)"));
                $floorsumwidth = DB::select(DB::Raw("select sum(width) as floorwidthsum from wms_dimensions where entity_id in ($parent) and entity_id not in ($entities->id)"));
                $floorsumheight = DB::select(DB::Raw("select sum(height) as floorheightsum from wms_dimensions where entity_id in ($parent) and entity_id not in ($entities->id)"));
//WareArea utilised                
                $floorsumarea = DB::select(DB::Raw("select sum(area) as floorareasum from wms_dimensions where entity_id in ($parent) and entity_id not in ($entities->id)"));;
//WareArea utilised            
            }else{
	        	$floorsumlength = 0;
	        	$floorsumwidth = 0;	
	        	$floorsumheight = 0;
	        	$floorsumarea = 0;
	        }

	        $child_capacity = DB::select(DB::Raw("select sum(capacity) as total_child_capacity from wms_entities where parent_entity_id = ".$entities->parent_entity_id." and id not in (".$entities->id.")"));

			$childCap = DB::select(DB::Raw("select sum(capacity) as total_child_capacity from wms_entities where parent_entity_id = ".$entities->id." and id not in (".$entities->id.")"));	        
	        $warewidth = DB::table('wms_dimensions')
		                ->selectRaw('sum(wms_dimensions.width) as widthsum')
		                ->leftjoin('wms_entities','wms_entities.id','=','wms_dimensions.entity_id')
		                ->where('wms_dimensions.entity_id',$entities->parent_entity_id)
		                ->get();
//warearea		    
			$warearea = DB::table('wms_dimensions')
		                ->selectRaw('sum(wms_dimensions.area) as areasum')
		                ->leftjoin('wms_entities','wms_entities.id','=','wms_dimensions.entity_id')
		                ->where('wms_dimensions.entity_id',$entities->parent_entity_id)
		                ->get();		    
//warearea		                

			$openzone = DB::table('wms_entities')
						->where('id',$entities->parent_entity_id)
						->pluck('entity_type_id');
			
			if($openzone == 6006 || $openzone == 6007)
			{
				$ozid = DB::table('wms_entities')
						->where('id',$entities->parent_entity_id)
						->pluck('parent_entity_id');

				$wareheight = DB::table('wms_dimensions')
			                ->selectRaw('sum(wms_dimensions.height) as heightsum')
			                ->leftjoin('wms_entities','wms_entities.id','=','wms_dimensions.entity_id')
			                ->where('wms_dimensions.entity_id',$ozid)
			                ->get();
			}else{					    
			    $wareheight = DB::table('wms_dimensions')
			                ->selectRaw('sum(wms_dimensions.height) as heightsum')
			                ->leftjoin('wms_entities','wms_entities.id','=','wms_dimensions.entity_id')
			                ->where('wms_dimensions.entity_id',$entities->parent_entity_id)
			                ->get();
		    }
		    $parent_capacity = DB::table('wms_entities')->where('id',$entities->parent_entity_id)->pluck('capacity');
		}
		else if($entity_type_id==6006)
		{
			$entity_name = 'Zone Name';
			$entity_code = 'Zone Code';
			$entity_code_val = 'Oz'.$entities->id;

			$warelength = DB::table('wms_dimensions')
                ->selectRaw('sum(wms_dimensions.length) as lengthsum')
                ->leftjoin('wms_entities','wms_entities.id','=','wms_dimensions.entity_id')
                ->where('wms_dimensions.entity_id',$entities->parent_entity_id)
                ->get();

            $floorid = DB::table('wms_entities')
	                    ->select(array(DB::raw('group_concat(id) as floor_id')))
	                    ->where(array('parent_entity_id'=>$entities->parent_entity_id, 'entity_type_id'=>$entities->entity_type_id))
	                    ->get();

	        if(isset($floorid[0]->floor_id) && !empty($floorid)){
                $parent = array($floorid[0]->floor_id);            
                $parent = implode(',', $parent);
                $floorsumlength = DB::select(DB::Raw("select sum(length) as floorlengthsum from wms_dimensions where entity_id in ($parent) and entity_id not in ($entities->id)"));
                $floorsumwidth = DB::select(DB::Raw("select sum(width) as floorwidthsum from wms_dimensions where entity_id in ($parent) and entity_id not in ($entities->id)"));
                $floorsumheight = DB::select(DB::Raw("select sum(height) as floorheightsum from wms_dimensions where entity_id in ($parent) and entity_id not in ($entities->id)"));
//WareArea utilised                
                $floorsumarea = DB::select(DB::Raw("select sum(area) as floorareasum from wms_dimensions where entity_id in ($parent) and entity_id not in ($entities->id)"));;
//WareArea utilised            
            }else{
	        	$floorsumlength = 0;
	        	$floorsumwidth = 0;
	        	$floorsumheight = 0;
	        	$floorsumarea = 0;
	        }

	        $child_capacity = DB::select(DB::Raw("select sum(capacity) as total_child_capacity from wms_entities where parent_entity_id = ".$entities->parent_entity_id." and id not in (".$entities->id.")"));

			$childCap = DB::select(DB::Raw("select sum(capacity) as total_child_capacity from wms_entities where parent_entity_id = ".$entities->id." and id not in (".$entities->id.")"));	        
	        $warewidth = DB::table('wms_dimensions')
		                ->selectRaw('sum(wms_dimensions.width) as widthsum')
		                ->leftjoin('wms_entities','wms_entities.id','=','wms_dimensions.entity_id')
		                ->where('wms_dimensions.entity_id',$entities->parent_entity_id)
		                ->get();
		    $wareheight = DB::table('wms_dimensions')
		                ->selectRaw('sum(wms_dimensions.height) as heightsum')
		                ->leftjoin('wms_entities','wms_entities.id','=','wms_dimensions.entity_id')
		                ->where('wms_dimensions.entity_id',$entities->parent_entity_id)
		                ->get();
//warearea		    
			$warearea = DB::table('wms_dimensions')
		                ->selectRaw('sum(wms_dimensions.area) as areasum')
		                ->leftjoin('wms_entities','wms_entities.id','=','wms_dimensions.entity_id')
		                ->where('wms_dimensions.entity_id',$entities->parent_entity_id)
		                ->get();		    
//warearea		                
		    $parent_capacity = DB::table('wms_entities')->where('id',$entities->parent_entity_id)->pluck('capacity');
		}
		else if($entity_type_id==6007)
		{
			$entity_name = 'Zone Name';
			$entity_code = 'Zone Code';
			$entity_code_val = 'Paz'.$entities->id;

			$warelength = DB::table('wms_dimensions')
                ->selectRaw('sum(wms_dimensions.length) as lengthsum')
                ->leftjoin('wms_entities','wms_entities.id','=','wms_dimensions.entity_id')
                ->where('wms_dimensions.entity_id',$entities->parent_entity_id)
                ->get();

            $floorid = DB::table('wms_entities')
	                    ->select(array(DB::raw('group_concat(id) as floor_id')))
	                    ->where(array('parent_entity_id'=>$entities->parent_entity_id, 'entity_type_id'=>$entities->entity_type_id))
	                    ->get();

	        if(isset($floorid[0]->floor_id) && !empty($floorid)){
                $parent = array($floorid[0]->floor_id);            
                $parent = implode(',', $parent);
                $floorsumlength = DB::select(DB::Raw("select sum(length) as floorlengthsum from wms_dimensions where entity_id in ($parent) and entity_id not in ($entities->id)"));
                $floorsumwidth = DB::select(DB::Raw("select sum(width) as floorwidthsum from wms_dimensions where entity_id in ($parent) and entity_id not in ($entities->id)"));
                $floorsumheight = DB::select(DB::Raw("select sum(height) as floorheightsum from wms_dimensions where entity_id in ($parent) and entity_id not in ($entities->id)"));
//WareArea utilised                
                $floorsumarea = DB::select(DB::Raw("select sum(area) as floorareasum from wms_dimensions where entity_id in ($parent) and entity_id not in ($entities->id)"));;
//WareArea utilised            
            }else{
	        	$floorsumlength = 0;
	        	$floorsumwidth = 0;
	        	$floorsumheight = 0;
	        	$floorsumarea = 0;
	        }

	        $child_capacity = DB::select(DB::Raw("select sum(capacity) as total_child_capacity from wms_entities where parent_entity_id = ".$entities->parent_entity_id." and id not in (".$entities->id.")"));

			$childCap = DB::select(DB::Raw("select sum(capacity) as total_child_capacity from wms_entities where parent_entity_id = ".$entities->id." and id not in (".$entities->id.")"));	        
	        $warewidth = DB::table('wms_dimensions')
		                ->selectRaw('sum(wms_dimensions.width) as widthsum')
		                ->leftjoin('wms_entities','wms_entities.id','=','wms_dimensions.entity_id')
		                ->where('wms_dimensions.entity_id',$entities->parent_entity_id)
		                ->get();
		    $wareheight = DB::table('wms_dimensions')
		                ->selectRaw('sum(wms_dimensions.height) as heightsum')
		                ->leftjoin('wms_entities','wms_entities.id','=','wms_dimensions.entity_id')
		                ->where('wms_dimensions.entity_id',$entities->parent_entity_id)
		                ->get();
//warearea		    
			$warearea = DB::table('wms_dimensions')
		                ->selectRaw('sum(wms_dimensions.area) as areasum')
		                ->leftjoin('wms_entities','wms_entities.id','=','wms_dimensions.entity_id')
		                ->where('wms_dimensions.entity_id',$entities->parent_entity_id)
		                ->get();		    
//warearea		                
		    $parent_capacity = DB::table('wms_entities')->where('id',$entities->parent_entity_id)->pluck('capacity');
		}
		else if($entity_type_id==6002)
		{
			$entity_name = 'Floor Name';
			$entity_code = 'Floor Code';
			$entity_code_val = 'F'.$entities->id;

			$warelength = DB::table('wms_dimensions')
                ->selectRaw('sum(wms_dimensions.length) as lengthsum')
                ->leftjoin('wms_entities','wms_entities.id','=','wms_dimensions.entity_id')
                ->where('wms_dimensions.entity_id',$entities->parent_entity_id)
                ->get();
            $floorid = DB::table('wms_entities')
	                    ->select(array(DB::raw('group_concat(id) as floor_id')))
	                    ->where(array('parent_entity_id'=>$entities->parent_entity_id, 'entity_type_id'=>$entities->entity_type_id))
	                    ->get();

	        if(isset($floorid[0]->floor_id) && !empty($floorid)){
                $parent = array($floorid[0]->floor_id);            
                $parent = implode(',', $parent);
                $floorsumlength = DB::select(DB::Raw("select sum(length) as floorlengthsum from wms_dimensions where entity_id in ($parent) and entity_id not in ($entities->id)"));
                $floorsumwidth = DB::select(DB::Raw("select sum(width) as floorwidthsum from wms_dimensions where entity_id in ($parent) and entity_id not in ($entities->id)"));
                $floorsumheight = DB::select(DB::Raw("select sum(height) as floorheightsum from wms_dimensions where entity_id in ($parent) and entity_id not in ($entities->id)"));
//WareArea utilised                
                $floorsumarea = DB::select(DB::Raw("select sum(area) as floorareasum from wms_dimensions where entity_id in ($parent) and entity_id not in ($entities->id)"));;
//WareArea utilised            
            }else{
	        	$floorsumlength = 0;
	        	$floorsumwidth = 0;
	        	$floorsumheight = 0;
	        	$floorsumarea = 0;
	        }

	        $child_capacity = DB::select(DB::Raw("select sum(capacity) as total_child_capacity from wms_entities where parent_entity_id = ".$entities->parent_entity_id." and id not in (".$entities->id.")"));

			$childCap = DB::select(DB::Raw("select sum(capacity) as total_child_capacity from wms_entities where parent_entity_id = ".$entities->id." and id not in (".$entities->id.")"));	        
	        $warewidth = DB::table('wms_dimensions')
		                ->selectRaw('sum(wms_dimensions.width) as widthsum')
		                ->leftjoin('wms_entities','wms_entities.id','=','wms_dimensions.entity_id')
		                ->where('wms_dimensions.entity_id',$entities->parent_entity_id)
		                ->get();
		    $wareheight = DB::table('wms_dimensions')
		                ->selectRaw('sum(wms_dimensions.height) as heightsum')
		                ->leftjoin('wms_entities','wms_entities.id','=','wms_dimensions.entity_id')
		                ->where('wms_dimensions.entity_id',$entities->parent_entity_id)
		                ->get();
//warearea		    
			$warearea = DB::table('wms_dimensions')
		                ->selectRaw('sum(wms_dimensions.area) as areasum')
		                ->leftjoin('wms_entities','wms_entities.id','=','wms_dimensions.entity_id')
		                ->where('wms_dimensions.entity_id',$entities->parent_entity_id)
		                ->get();		    
//warearea		                
		    $parent_capacity = DB::table('wms_entities')->where('id',$entities->parent_entity_id)->pluck('capacity');

		}
		else if($entity_type_id==6008)
		{
			$entity_name = 'Dock Name';
			$entity_code = 'Dock Code';
			$entity_code_val = 'D'.$entities->id;

			$warelength = DB::table('wms_dimensions')
                ->selectRaw('sum(wms_dimensions.length) as lengthsum')
                ->leftjoin('wms_entities','wms_entities.id','=','wms_dimensions.entity_id')
                ->where('wms_dimensions.entity_id',$entities->parent_entity_id)
                ->get();

            $floorid = DB::table('wms_entities')
	                    ->select(array(DB::raw('group_concat(id) as floor_id')))
	                    ->where(array('parent_entity_id'=>$entities->parent_entity_id, 'entity_type_id'=>$entities->entity_type_id))
	                    ->get();

	        if(isset($floorid[0]->floor_id) && !empty($floorid)){
                $parent = array($floorid[0]->floor_id);            
                $parent = implode(',', $parent);
                $floorsumlength = DB::select(DB::Raw("select sum(length) as floorlengthsum from wms_dimensions where entity_id in ($parent) and entity_id not in ($entities->id)"));
                $floorsumwidth = DB::select(DB::Raw("select sum(width) as floorwidthsum from wms_dimensions where entity_id in ($parent) and entity_id not in ($entities->id)"));
                $floorsumheight = DB::select(DB::Raw("select sum(height) as floorheightsum from wms_dimensions where entity_id in ($parent) and entity_id not in ($entities->id)"));
//WareArea utilised                
                $floorsumarea = DB::select(DB::Raw("select sum(area) as floorareasum from wms_dimensions where entity_id in ($parent) and entity_id not in ($entities->id)"));;
//WareArea utilised            
            }else{
	        	$floorsumlength = 0;
	        	$floorsumwidth = 0;
	        	$floorsumheight = 0;
	        	$floorsumarea = 0;
	        }

	        $child_capacity = DB::select(DB::Raw("select sum(capacity) as total_child_capacity from wms_entities where parent_entity_id = ".$entities->parent_entity_id." and id not in (".$entities->id.")"));
			$childCap = DB::select(DB::Raw("select sum(capacity) as total_child_capacity from wms_entities where parent_entity_id = ".$entities->id." and id not in (".$entities->id.")"));	        

	        $warewidth = DB::table('wms_dimensions')
		                ->selectRaw('sum(wms_dimensions.width) as widthsum')
		                ->leftjoin('wms_entities','wms_entities.id','=','wms_dimensions.entity_id')
		                ->where('wms_dimensions.entity_id',$entities->parent_entity_id)
		                ->get();
		    $wareheight = DB::table('wms_dimensions')
		                ->selectRaw('sum(wms_dimensions.height) as heightsum')
		                ->leftjoin('wms_entities','wms_entities.id','=','wms_dimensions.entity_id')
		                ->where('wms_dimensions.entity_id',$entities->parent_entity_id)
		                ->get();
//warearea		    
			$warearea = DB::table('wms_dimensions')
		                ->selectRaw('sum(wms_dimensions.area) as areasum')
		                ->leftjoin('wms_entities','wms_entities.id','=','wms_dimensions.entity_id')
		                ->where('wms_dimensions.entity_id',$entities->parent_entity_id)
		                ->get();		    
//warearea		                
		    $parent_capacity = DB::table('wms_entities')->where('id',$entities->parent_entity_id)->pluck('capacity');
		}
		else
		{   
			$entity_name = 'Entity Name';
			$entity_code = 'Entity Code';
			$entity_code_val = 'Entity Code';
		}		
        $dim = Dimension::where('entity_id', $entity_id)->first();
        //return $dim;
		//converting UOM into feet before saving in dimensions
		$dUom = $dim->uom_id;
		$dLen = $dim->length;
		$dWid = $dim->width;
		$dDep = $dim->depth;
		$dHei = $dim->height;
		$dimensions=$this->fromFeetConverter($dUom,$dLen,$dHei,$dWid,$dDep);
		//converting UOM into feet before saving in dimensions  
		$dimensions['id']=$dim->id; 
		$dimensions['entity_id']=$dim->entity_id;
		$dimensions['uom_id']=$dim->uom_id;     
		//return $dimensions;
        $capacity_uom = $this->commonRepo->getLookupData('Capacity UOM');
		$dimension_uom = $this->commonRepo->getLookupData('Length UOM');

		$locations = DB::table('locations')->where('location_id',$location_id)->lists('location_name','location_id');
        $locations = ['' => 'Select Location'] + $locations;

           if(empty($warelength)){
            	$warelength=0;
           }else{
           		if(empty($warelength[0]->lengthsum))
	            {
	            	$warelength[0]->lengthsum = 0;
	            	$warelength = $warelength[0]->lengthsum;
	            }else{
	            	$warelength=$warelength[0]->lengthsum;
	        	}
           }
           if(empty($warewidth)){
            	$warewidth=0;
           }else{
            	if(empty($warewidth[0]->widthsum))
	            {
	            	$warewidth[0]->widthsum = 0;
	            	$warewidth = $warewidth[0]->widthsum;
	            }else{
	            	$warewidth=$warewidth[0]->widthsum;
	        	}
           }
           if(empty($wareheight)){
            	$wareheight=0;
           }else{
            	if(empty($wareheight[0]->heightsum))
	            {
	            	$wareheight[0]->heightsum = 0;
	            	$wareheight = $wareheight[0]->heightsum;
	            }else{
	            	$wareheight=$wareheight[0]->heightsum;
	        	}
           }
//warearea
           if(empty($warearea)){
            	$warearea=0;
           }else{
            	if(empty($warearea[0]->areasum))
	            {
	            	$warearea[0]->areasum = 0;
	            	$warearea = $warearea[0]->areasum;
	            }else{
	            	$warearea=$warearea[0]->areasum;
	        	}
           }
//warearea            
           if(empty($floorsumlength)){
            	$floorsumlength=0;
           }else{
            	if(empty($floorsumlength[0]->floorlengthsum))
	            {
	            	$floorsumlength[0]->floorlengthsum = 0;
	            	$floorsumlength = $floorsumlength[0]->floorlengthsum;
	            }else{
	            	$floorsumlength=$floorsumlength[0]->floorlengthsum;
	        	}
           }
           if(empty($floorsumwidth)){
            	$floorsumwidth=0;
           }else{
           		if(empty($floorsumwidth[0]->floorwidthsum))
	            {
	            	$floorsumwidth[0]->floorwidthsum = 0;
	            	$floorsumwidth = $floorsumwidth[0]->floorwidthsum;
	            }else{
	            	$floorsumwidth=$floorsumwidth[0]->floorwidthsum;
	        	}
           }
           if(empty($floorsumheight)){
            	$floorsumheight=0;
           } else{
           		if(empty($floorsumheight[0]->floorheightsum))
	            {
	            	$floorsumheight[0]->floorheightsum = 0;
	            	$floorsumheight = $floorsumheight[0]->floorheightsum;
	            }else{
	            	$floorsumheight = $floorsumheight[0]->floorheightsum;
	        	}
           }
//wareArea utilisation
           if(empty($floorsumarea)){
            	$floorsumarea=0;
           }else{
            	if(empty($floorsumarea[0]->floorareasum))
	            {
	            	$floorsumarea[0]->floorareasum = 0;
	            	$floorsumarea = $floorsumarea[0]->floorareasum;
	            }else{
	            	$floorsumarea=$floorsumarea[0]->floorareasum;
	        	}
           }
//wareArea utilisation              
           if(empty($parent_capacity)){
            	$parent_capacity=0;
           }
           if(empty($child_capacity)){
            	$child_capacity=0;
           }else{
           		if(empty($child_capacity[0]->total_child_capacity))
	            {
	            	$child_capacity[0]->total_child_capacity = 0;
	            	$child_capacity = $child_capacity[0]->total_child_capacity;
	            }else{
	            	$child_capacity=$child_capacity[0]->total_child_capacity;
	        	}
           }
           if(empty($childCap)){
            	$childCap=0;
           }else{
           		if(empty($childCap[0]->total_child_capacity))
	            {
	            	$childCap[0]->total_child_capacity = 0;
	            	$childCap = $childCap[0]->total_child_capacity;
	            }else{
	            	$childCap=$childCap[0]->total_child_capacity;
	        	}
           }
          //return 'PC: '.$parent_capacity."---CC: ".$child_capacity;

        return View::make('entities.edit',compact("capacity_uom","ware_id","childCap","dimension_uom","locations","entities","entity_type_id","dimensions","entity_name","entity_code","entity_code_val","parent_capacity","child_capacity","floorsumheight","floorsumwidth","floorsumlength","wareheight","warewidth","warelength","warearea","floorsumarea"));
	}
	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update($entity_id)
	{
	    
        //return Input::get('entity_code');
        //converting capacity to KG
        $cap = Input::get('capacity');
        $capUom = Input::get('capacity_uom_id');
        $capacity = $this->kgConverter($capUom,$cap);
        //converting capacity to KG
		$entities = Entities::find($entity_id);
        $entities->entity_name = Input::get('entity_name');
        $entities->entity_location = Input::get('entity_location');
        $entities->entity_type_id = Input::get('entity_type_id');
        $entities->location_id = Input::get('location_id');
        $entities->xco = Input::get('xco');
        $entities->yco = Input::get('yco');
        $entities->zco = Input::get('zco');
        //$entities->capacity = Input::get('capacity');
        $entities->capacity =  $capacity['capacity'];
        $entities->capacity_uom_id = Input::get('capacity_uom_id');
        $entities->status = 1;
        $entities->save();
        //converting UOM into feet before saving in dimensions
		$dimUom = Input::get('uom_id');
		$dimLen = Input::get('length');
		$dimWid = Input::get('width');
		//$dimDep = Input::get('depth');
		$dimDep = 0;
		$dimHei = Input::get('height');
		$dimFeet=$this->feetConverter($dimUom,$dimLen,$dimHei,$dimWid,$dimDep);
		//converting UOM into feet before saving in dimensions
   	
   		//Storing into dimensions table
		$dimensions = Dimension::where('entity_id', $entity_id)->first();
		$dimensions->entity_id = $entity_id;
		/*		$dimensions->height = Input::get('height');
		$dimensions->width = Input::get('width');
		$dimensions->depth = Input::get('depth');
		$dimensions->length = Input::get('length');
		$dimensions->area = Input::get('area');*/
		$dimensions->height = $dimFeet['height'];
		$dimensions->width = $dimFeet['width'];
		$dimensions->depth = $dimFeet['depth'];
		$dimensions->length = $dimFeet['length'];		
		$dimensions->area = $dimFeet['area'];
		$dimensions->uom_id = Input::get('uom_id');
		$dimensions->save();

		$dimensions_entity = DB::table('wms_dimensions')->select('id','entity_id')
								->where('entity_id',$entity_id)->get();		
		$entity_dimension_update = DB::table('wms_entities')
									->where('id',$dimensions_entity[0]->entity_id)
									->update(array('dimension_id'=>$dimensions_entity[0]->id));

		$entity_dimension_update = DB::table('wms_storage_bins')
									->where('entity_id',$entity_id)
									->update(array('storage_bin_id'=>Input::get('entity_location'),'storage_capacity'=>$capacity['capacity'],'storage_bin_name'=>Input::get('entity_name')));

		 return Redirect::to('entities')->withFlashMessage('Updated Successfully.'); 	
	}
	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function delete($entity_id)
	{
	  $entity_id = $this->roleRepo->decodeData($entity_id);
      $password = Input::get();
      $userId = Session::get('userId');
      $verifiedUser = $this->roleRepo->verifyUser($password['password'], $userId);
      if($verifiedUser >= 1)
      {	  
		  $entities = Entities::find($entity_id);
		  $mfg_id = $entities['org_id'] ;
		  $depEntities = $this->getChildEntities($entity_id);
		  $checkValidity = $this->checkStorageBins($depEntities,$mfg_id,$entities['ware_id']);
		  //return $checkValidity;
		  if(!empty($checkValidity) && $checkValidity == 1)
		  {
			  //return 'Hi';
			  if($entities['is_assigned'] == 1)
			  {
			  		$eseal = Eseal::where('entity_id',$entity_id)->orWhere('parent_entity_id',$entity_id)->delete();
			  } 
			  if($entities['entity_type_id'] == 6005)
			  {
			     	$bin = DB::table('wms_storage_bins')->where('entity_id',$entity_id)->delete();
		      } 
		  	  //$dimensions = Dimension::where('entity_id', $entity_id)->first();
		      $dimensions = Dimension::whereIn('entity_id', $depEntities)->delete();
		      $storageBins = DB::table('wms_storage_bins')->whereIn('entity_id',$depEntities)->delete();
			  //$dimensions->delete();
			  //$entities->delete();
			  $entities = Entities::whereIn('id',$depEntities)->delete();
			  //return Redirect::to('entities/'); 
		      return 1;
		  }
		  else
		  {
		  	return '2'.'-'.$checkValidity;;
		  }		      
	  }
	  else{
	  	return "You have entered incorrect password !!";
	  }
	}
	public function copy($entity_type_id,$parent_entity_id)
	{
		$warehouse_id = DB::table('wms_entities')->where('id', $parent_entity_id)->lists('parent_entity_id');
		$warehouse_id = $warehouse_id[0]; //return $warehosue_id;

		$org_id = DB::table('wms_entities')->where('id', $warehouse_id)->lists('id');
		$org_id = $org_id[0];
      
		$warehouse_details = DB::table('wms_entities')->where('parent_entity_id', $org_id)->lists('entity_name', 'id');
		
		return View::make('entities.copy',compact("warehouse_details","parent_entity_id"));
	}
	public function getzones($warehouse_id)
	{
		 
		$zonedetails = DB::table('wms_entities')->where('parent_entity_id',$warehouse_id)->lists('entity_name','id'); //return $zonedetails;
		//return $zonedetails;
		//$seloptions = json_encode($zonedetails);
		$seloptions=array();
		$suboptions=array();
		$reqsel ='<select name="zone_id" id="zone_id" class="select1"><option value="">Select Zone</option>';
		foreach($zonedetails as $key=>$val)
		{
			$reqsel.='<option value="'.$key.'" >'.$val.'</option>';
			$suboptions['value'] = $key;
			$suboptions['option'] = $val;
			$seloptions[]=$suboptions; 
		}
		$reqsel.='</select>';
		return $reqsel;
		return json_encode($seloptions);
	}

	public function zonedetails($org_parent_entity_id,$zone_id)
	{
		$parent_entity_id = $org_parent_entity_id;
		$entity_id = $zone_id;
		$entities = Entities::find($entity_id);
		$location_id = Entities::where('id',$parent_entity_id)->pluck('location_id');
		$entity_name = 'Zone Name';
		$entity_code = 'Zone Code';
		$entity_code_val = 'Z'.$entities->id;
        $children_parent_entity_id = $entities->id;

        $parent_capacity = Entities::where('id',$parent_entity_id)->pluck('capacity');
        $child_capacity = DB::select(DB::Raw("select sum(capacity) as total_child_capacity from wms_entities where parent_entity_id = ".$parent_entity_id));
        $warelength = DB::table('wms_dimensions')
                ->selectRaw('wms_dimensions.length as lengthsum')
                ->leftjoin('wms_entities','wms_entities.id','=','wms_dimensions.entity_id')
                ->where('wms_dimensions.entity_id',$parent_entity_id)
                ->get();
        $warewidth = DB::table('wms_dimensions')
		                ->selectRaw('wms_dimensions.width as widthsum')
		                ->leftjoin('wms_entities','wms_entities.id','=','wms_dimensions.entity_id')
		                ->where('wms_dimensions.entity_id',$parent_entity_id)
		                ->get();
		$zoneid = DB::table('wms_entities')
			        ->select(array(DB::raw('group_concat(id) as zone_id')))
			        ->where('parent_entity_id',$parent_entity_id)
			        ->get();
		if(isset($zoneid[0]->zone_id) && !empty($zoneid)){
            $parent = array($zoneid[0]->zone_id);            
            $parent = implode(',', $parent);
            $zonesumlength = DB::select(DB::Raw("select sum(length) as zonelengthsum from wms_dimensions where entity_id in ($parent)"));
            $zonesumwidth = DB::select(DB::Raw("select sum(width) as zonewidthsum from wms_dimensions where entity_id in ($parent)"));
		}else{
        	$zonesumlength = 0;
        	$zonesumwidth = 0;
		}

		$dimensions = Dimension::where('entity_id', $entity_id)->first();
        $capacity_uom = $this->commonRepo->getLookupName($entities->capacity_uom_id);
		$dimension_uom = $this->commonRepo->getLookupName($dimensions->uom_id);
        $locations = DB::table('locations')->where('location_id',$location_id)->lists('location_name', 'location_id');

        if(empty($parent_capacity)){
            $parent_capacity=0;
           }
        if(empty($child_capacity)){
         $child_capacity=0;
        }else{
         	if(empty($child_capacity[0]->total_child_capacity))
            {
            	$child_capacity[0]->total_child_capacity = 0;
            	$child_capacity = $child_capacity[0]->total_child_capacity;
            }else{
            	$child_capacity=$child_capacity[0]->total_child_capacity;
        	}
        }
        if(empty($warelength)){
		   $warelength=0;
		}else{
			if(empty($warelength[0]->lengthsum))
            {
            	$warelength[0]->lengthsum = 0;
            	$warelength = $warelength[0]->lengthsum;
            }else{
		    	$warelength=$warelength[0]->lengthsum;
		    }
		}
	    if(empty($warewidth)){
	       $warewidth=0;
	    }else{
	    	if(empty($warewidth[0]->widthsum))
            {
            	$warewidth[0]->widthsum = 0;
            	$warewidth = $warewidth[0]->widthsum;
            }else{
		    	$warewidth=$warewidth[0]->widthsum;
		    }
	    }
	    if(empty($zonesumlength)){
           $zonesumlength=0;
        }else{
           if(empty($zonesumlength[0]->zonelengthsum))
            {
            	$zonesumlength[0]->zonelengthsum = 0;
            	$zonesumlength = $zonesumlength[0]->zonelengthsum;
            }else{
		    	$zonesumlength=$zonesumlength[0]->zonelengthsum;
		    }	 	
        }
        if(empty($zonesumwidth)){
           $zonesumwidth=0;
        }else{
        	if(empty($zonesumwidth[0]->zonewidthsum))
            {
            	$zonesumwidth[0]->zonewidthsum = 0;
            	$zonesumwidth = $zonesumwidth[0]->zonewidthsum;
            }else{
		    	$zonesumwidth=$zonesumwidth[0]->zonewidthsum;
		    }
        }
		
		return View::make('entities.makezone',compact("capacity_uom","dimension_uom","locations","entities","dimensions","entity_name","entity_code","entity_code_val","parent_entity_id","children_parent_entity_id","parent_capacity","child_capacity","warelength","warewidth","zonesumwidth","zonesumlength"));
	}
	public function getalldata()
    {
        $currentUserId = \Session::get('userId');
        \Log::info($currentUserId);
        $manufacturerId = DB::table('users')->where('user_id', $currentUserId)->pluck('customer_id');
        \Log::info($manufacturerId);
        if($manufacturerId)
        {
          $orgs = DB::table('wms_entities')
            ->select('wms_entities.*')
            ->where(array('wms_entities.org_id' => $manufacturerId, 'wms_entities.entity_type_id' => 0))
            ->get();
        }else{
          $orgs = DB::table('wms_entities')
            ->select('wms_entities.*')
            ->where('wms_entities.entity_type_id',0)
            ->get();
        }
            $getWareHouseId = DB::table('master_lookup')
                            ->select('master_lookup.value','master_lookup.name')
                            ->where('master_lookup.name','Warehouse')
                            ->get();
              $getFloorId = DB::table('master_lookup')
                            ->select('master_lookup.value','master_lookup.name')
                            ->where('master_lookup.name','Floor')
                            ->get();
            $getZoneId = DB::table('master_lookup')
                            ->select('master_lookup.value','master_lookup.name')
                            ->where('master_lookup.name','Zone')
                            ->get();
            $getRackId = DB::table('master_lookup')
                            ->select('master_lookup.value','master_lookup.name')
                            ->where('master_lookup.name','Rack')
                            ->get();
            $getBinId = DB::table('master_lookup')
                            ->select('master_lookup.value','master_lookup.name')
                            ->where('master_lookup.name','Bin')
                            ->get();                

            if(!empty($orgs))
            {
                $orgarr = array();
                $finalorgarr = array();
                foreach($orgs as $org)
                {
                    $wares = DB::table('wms_entities')
                    ->leftJoin('master_lookup', 'wms_entities.entity_type_id', '=', 'master_lookup.value')
                    ->select('wms_entities.*','master_lookup.name','master_lookup.value')
                    ->where(array('wms_entities.parent_entity_id'=>$org->id))
                    ->get();

                    $warearr = array();
                    $finalwarearr = array();
                    if(!empty($wares))
                    {                        
                        foreach($wares as $ware)
                        {
                            $floors = DB::table('wms_entities')
                            ->leftJoin('master_lookup', 'wms_entities.entity_type_id', '=', 'master_lookup.value')
		                    ->select('wms_entities.*','master_lookup.name','master_lookup.value')
		                    ->where(array('wms_entities.parent_entity_id'=>$ware->id))
		                    ->get();

                            $floorarr = array();
                            $finalfloorarr = array();
                            if(!empty($floors))
                            {
                                foreach($floors as $floor)
                                {
                                    $zones = DB::table('wms_entities')
                                    ->leftJoin('master_lookup', 'wms_entities.entity_type_id', '=', 'master_lookup.value')
				                    ->select('wms_entities.*','master_lookup.name','master_lookup.value')
				                    ->where(array('wms_entities.parent_entity_id'=>$floor->id))
				                    ->get();
                                    $zonearr = array();
                                    $finalzonearr = array();
                                if(!empty($zones))
                                {    
                                    foreach($zones as $zone)
                                    {
                                    $racks = DB::table('wms_entities')
                                    ->leftJoin('master_lookup', 'wms_entities.entity_type_id', '=', 'master_lookup.value')
				                    ->select('wms_entities.*','master_lookup.name','master_lookup.value')
				                    ->where(array('wms_entities.parent_entity_id'=>$zone->id))
				                    ->get();
                                    $rackarr = array();
                                    $finalrackarr = array();
                                    if(!empty($racks))
                                    {                                        
                                        foreach($racks as $rack)
                                        {
                                            $bins = DB::table('wms_entities')
                                            ->leftJoin('master_lookup', 'wms_entities.entity_type_id', '=', 'master_lookup.value')
						                    ->select('wms_entities.*','master_lookup.name','master_lookup.value')
						                    ->where(array('wms_entities.parent_entity_id'=>$rack->id))
						                    ->get();
                                            $binarr = array();
                                            $finalbinarr = array();
                                            if(!empty($bins))
                                            {
                                                foreach($bins as $bin)
                                                {
                                                    $capacity = $this->fromKgConverter($bin->capacity_uom_id,$bin->capacity);
                                                    $binarr['id'] = $bin->id;
                                                    $binarr['entity_name'] = $bin->entity_name;
                                                    $binarr['entity_location'] = $bin->entity_location;
                                                    $binarr['entity_code'] = $bin->entity_code;
                                                    $binarr['entity_type_name'] = $bin->name;
                                                    //$binarr['capacity'] = $bin->capacity;
                                                    $binarr['capacity'] = $capacity['capacity'];
                                                    $binarr['capacity_uom'] = DB::table('master_lookup')->where('value',$bin->capacity_uom_id)->pluck('name');
                                                    $binarr['entity_type_id'] = $bin->entity_type_id;
                                                    //$child_entity_type_id = $bin->value;
		            								$binarr['actions'] = '<span style="padding-left:28px;" ></span><span style="padding-left:58px;" ><a href="javascript:void(0);" onclick="editEntity(' . "'" . $this->roleRepo->encodeData($bin->id). "'" .')"  data-target="#basicvalCodeModal"><span class="badge bg-light-blue"><i class="fa fa-pencil"></i></span></a></span><span style="padding-left:50px;" ><a onclick="deleteEntity(' . "'" . $this->roleRepo->encodeData($bin->id). "'" .')" ><span class="badge bg-red"><i class="fa fa-trash-o"></i></span></a></span>';
								            		//$binarr['edit'] = '<a href="entities/edit/'.$bin->id.'"><span class="badge bg-light-blue"><i class="fa fa-pencil"></i></span></a>'; 
								            		//$binarr['delete'] = '<a onclick="deleteEntity('.$bin->id.')" href=""><span class="badge bg-red"><i class="fa fa-trash-o"></i></span></a>';
                                                    $binarr['assign'] = '<a href="assignlocation/create/'.$bin->id.'">Assign</a>'; 
                                                    $finalbinarr[] = $binarr;
                                                }
                                            }
                                            else{
                                                $finalbinarr[] = '';
                                            }
                                            $capacity = $this->fromKgConverter($rack->capacity_uom_id,$rack->capacity);
                                            $rackarr['id'] = $rack->id;
                                            $rackarr['entity_name'] = $rack->entity_name;
                                            $rackarr['entity_location'] = $rack->entity_location;
                                            $rackarr['entity_code'] = $rack->entity_code;
                                            $rackarr['entity_type_name'] = $rack->name;
                                            //$rackarr['capacity'] = $rack->capacity;
                                            $rackarr['capacity'] = $capacity['capacity'];
                                            $rackarr['capacity_uom'] = DB::table('master_lookup')->where('value',$rack->capacity_uom_id)->pluck('name');
                                            $rackarr['entity_type_id'] = $rack->entity_type_id;
                                            $child_entity_type_id = $getBinId[0]->value;
                                            if($rack->entity_type_id == 6005)
		            						{
		            							$rackarr['actions'] = '<span style="padding-left:28px;" ></span><span style="padding-left:58px;" ><a href="javascript:void(0);" onclick="editEntity(' . "'" . $this->roleRepo->encodeData($rack->id). "'" .')"  data-target="#basicvalCodeModal"><span class="badge bg-light-blue"><i class="fa fa-pencil"></i></span></a></span><span style="padding-left:50px;" ><a onclick="deleteEntity(' . "'" . $this->roleRepo->encodeData($rack->id). "'" .')" ><span class="badge bg-red"><i class="fa fa-trash-o"></i></span></a></span>';
		            							$rackarr['assign'] = '<a href="assignlocation/create/'.$rack->id.'">Assign</a>'; 
                                            	$rackarr['children'] = $finalbinarr;
								            		
		            						}else{
		            						$rackarr['actions'] = '<span style="padding-left:20px;" ><a href="javascript:void(0);" onclick="addEntity('.$child_entity_type_id.','.$rack->id.','.$rack->org_id.','.$rack->location_id.','.$rack->ware_id.')"  data-target="#basicvalCodeModal" ><span class="badge bg-green"><i class="fa fa-plus"></i></span></a><span style="padding-left:50px;" ><a href="javascript:void(0);" onclick="editEntity(' . "'" . $this->roleRepo->encodeData($rack->id). "'" .')"  data-target="#basicvalCodeModal"><span class="badge bg-light-blue"><i class="fa fa-pencil"></i></span></a></span><span style="padding-left:50px;" ><a onclick="deleteEntity(' . "'" . $this->roleRepo->encodeData($rack->id). "'" .')"><span class="badge bg-red"><i class="fa fa-trash-o"></i></span></a></span>'; 
								            $rackarr['assign'] = '<a href="assignlocation/create/'.$rack->id.'">Assign</a>'; 
                                            $rackarr['children'] = $finalbinarr;
                                        }
                                            $finalrackarr[] = $rackarr;
                                        }
                                    }
                                    else{
                                        $finalrackarr[] = '';
                                    }
                                    //echo $zone->name;die;
                                    $capacity = $this->fromKgConverter($zone->capacity_uom_id,$zone->capacity);
                                    $zonearr['id'] = $zone->id;
                                    $zonearr['entity_name'] = $zone->entity_name;
                                    $zonearr['entity_location'] = $zone->entity_location;
                                    $zonearr['entity_code'] = $zone->entity_code;
                                    $zonearr['entity_type_name'] = $zone->name;
                                    //$zonearr['capacity'] = $zone->capacity;
                                    $zonearr['capacity'] = $capacity['capacity'];
                                    $zonearr['capacity_uom'] = DB::table('master_lookup')->where('value',$zone->capacity_uom_id)->pluck('name');
                                    $zonearr['entity_type_id'] = $zone->entity_type_id;
                                    $child_entity_type_id = $getRackId[0]->value;
                                    if($zone->entity_type_id==6007)
                                    {
		            					// $zonearr['actions'] = '<span style="padding-left:35px;" ></span><span style="padding-left:50px;" ><a href="javascript:void(0);" onclick="editEntity('.$zone->id.')"  data-target="#basicvalCodeModal"><span class="badge bg-light-blue"><i class="fa fa-pencil"></i></span></a></span><span style="padding-left:50px;" ><a onclick="deleteEntity('.$zone->id.')" ><span class="badge bg-red"><i class="fa fa-trash-o"></i></span></a></span>';
		            					//$zonearr['entity_name'] = $getBinId[0]->name;
                                    	$child_entity_type_id =  $getBinId[0]->value;
		            					$zonearr['actions'] = '<span style="padding-left:20px;" ><a href="javascript:void(0);" onclick="addEntity('.$child_entity_type_id.','.$zone->id.','.$zone->org_id.','.$zone->location_id.','.$zone->ware_id.')"  data-target="#basicvalCodeModal"><span class="badge bg-green"><i class="fa fa-plus"></i></span></a></span><span style="padding-left:50px;" ><a href="javascript:void(0);" onclick="editEntity(' . "'" . $this->roleRepo->encodeData($zone->id). "'" .')"  data-target="#basicvalCodeModal"><span class="badge bg-light-blue"><i class="fa fa-pencil"></i></span></a></span><span style="padding-left:50px;" ><a onclick="deleteEntity(' . "'" . $this->roleRepo->encodeData($zone->id). "'" .')" ><span class="badge bg-red"><i class="fa fa-trash-o"></i></span></a></span>';
                                        //$zonearr['assign'] = '';
                                    }
                                    else if($zone->entity_type_id==6008){
                                    	$zonearr['actions'] = '<span style="padding-left:40px;" ></span><span style="padding-left:50px;" ><a href="javascript:void(0);" onclick="editEntity(' . "'" . $this->roleRepo->encodeData($zone->id). "'" .')"  data-target="#basicvalCodeModal"><span class="badge bg-light-blue"><i class="fa fa-pencil"></i></span></a></span><span style="padding-left:50px;" ><a onclick="deleteEntity(' . "'" . $this->roleRepo->encodeData($zone->id). "'" .')" ><span class="badge bg-red"><i class="fa fa-trash-o"></i></span></a></span>';
                                    }
                                    else if($zone->entity_type_id==6006){
                                    	$child_entity_type_id =  $getBinId[0]->value;
		            					$zonearr['actions'] = '<span style="padding-left:20px;" ><a href="javascript:void(0);" onclick="addEntity('.$child_entity_type_id.','.$zone->id.','.$zone->org_id.','.$zone->location_id.','.$zone->ware_id.')"  data-target="#basicvalCodeModal"><span class="badge bg-green"><i class="fa fa-plus"></i></span></a></span><span style="padding-left:50px;" ><a href="javascript:void(0);" onclick="editEntity(' . "'" . $this->roleRepo->encodeData($zone->id). "'" .')"  data-target="#basicvalCodeModal"><span class="badge bg-light-blue"><i class="fa fa-pencil"></i></span></a></span><span style="padding-left:50px;" ><a onclick="deleteEntity(' . "'" . $this->roleRepo->encodeData($zone->id). "'" .')" ><span class="badge bg-red"><i class="fa fa-trash-o"></i></span></a></span>';
                                    }
                                    else
                                    {
                                      	$zonearr['actions'] = '<span style="padding-left:20px;" ><a href="javascript:void(0);" onclick="addEntity('.$child_entity_type_id.','.$zone->id.','.$zone->org_id.','.$zone->location_id.','.$zone->ware_id.')"  data-target="#basicvalCodeModal"><span class="badge bg-green"><i class="fa fa-plus"></i></span></a></span><span style="padding-left:50px;" ><a href="javascript:void(0);" onclick="editEntity(' . "'" . $this->roleRepo->encodeData($zone->id). "'" .')"  data-target="#basicvalCodeModal"><span class="badge bg-light-blue"><i class="fa fa-pencil"></i></span></a></span><span style="padding-left:50px;" ><a onclick="deleteEntity(' . "'" . $this->roleRepo->encodeData($zone->id). "'" .')" ><span class="badge bg-red"><i class="fa fa-trash-o"></i></span></a></span>'; 
                                             
                                      }
                                    $zonearr['assign'] = '<a href="assignlocation/create/'.$zone->id.'">Assign</a>';
								    //$zonearr['edit'] = '<a href="entities/edit/'.$zone->id.'"><span class="badge bg-light-blue"><i class="fa fa-pencil"></i></span></a>'; 
								   // $zonearr['delete'] = '<a onclick="deleteEntity('.$zone->id.')" href=""><span class="badge bg-red"><i class="fa fa-trash-o"></i></span></a>';
                                    
                                    $zonearr['children'] = $finalrackarr;
                                    $finalzonearr[] = $zonearr;                         
                                }
                                
                            }
                            else
                            {
                                   $finalzonearr[] = '';
                            }
                            $capacity = $this->fromKgConverter($floor->capacity_uom_id,$floor->capacity);
                            $floorarr['id'] = $floor->id;
                            $floorarr['entity_name'] = $floor->entity_name;
                            $floorarr['entity_location'] = $floor->entity_location;
                            $floorarr['entity_code'] = $floor->entity_code;
                            $floorarr['entity_type_name'] = $floor->name;
                            //$floorarr['capacity'] = $floor->capacity;
                            $floorarr['capacity'] = $capacity['capacity'];
                            $floorarr['capacity_uom'] = DB::table('master_lookup')->where('value',$floor->capacity_uom_id)->pluck('name');
                            $floorarr['entity_type_id'] = $floor->entity_type_id;
                            $child_entity_type_id = $getZoneId[0]->value;
		            		$floorarr['actions'] = '<span style="padding-left:20px;" ><a href="javascript:void(0);" onclick="addEntity('.$child_entity_type_id.','.$floor->id.','.$floor->org_id.','.$floor->location_id.','.$floor->ware_id.')"  data-target="#basicvalCodeModal"><span class="badge bg-green"><i class="fa fa-plus"></i></span></a></span><span style="padding-left:50px;" ><a href="javascript:void(0);" onclick="editEntity(' . "'" . $this->roleRepo->encodeData($floor->id). "'" .')"  data-target="#basicvalCodeModal"><span class="badge bg-light-blue"><i class="fa fa-pencil"></i></span></a></span><span style="padding-left:50px;" ><a onclick="deleteEntity(' . "'" . $this->roleRepo->encodeData($floor->id). "'" .')" ><span class="badge bg-red"><i class="fa fa-trash-o"></i></span></a></span>';   
							$floorarr['assign'] = ''; 
                            $floorarr['children'] = $finalzonearr;
                            $finalfloorarr[] = $floorarr;
                        }
                        
                    }
                    else
                    {
                        $finalfloorarr[] = '';
                    }
                            $capacity = $this->fromKgConverter($ware->capacity_uom_id,$ware->capacity);
                            $warearr['id'] = $ware->id;
                            $warearr['entity_name'] = $ware->entity_name;
                            $warearr['entity_location'] = $ware->entity_location;
                            $warearr['entity_code'] = $ware->entity_code;
                            $warearr['entity_code'] = $ware->entity_code;
                            $warearr['entity_type_name'] = $ware->name;
                            //$warearr['capacity'] = $ware->capacity;
                            $warearr['capacity'] = $capacity['capacity'];
                            $warearr['capacity_uom'] = DB::table('master_lookup')->where('value',$ware->capacity_uom_id)->pluck('name');
                            $warearr['entity_type_id'] = $ware->entity_type_id;
                            $child_entity_type_id = $getFloorId[0]->value;
		            		$warearr['actions'] = '<span style="padding-left:20px;" ><a href="javascript:void(0);" onclick="addEntity('.$child_entity_type_id.','.$ware->id.','.$ware->org_id.','.$ware->location_id.','.$ware->id.')"  data-target="#basicvalCodeModal"><span class="badge bg-green"><i class="fa fa-plus"></i></span></a></span><span style="padding-left:50px;" ><a href="javascript:void(0);" onclick="editEntity(' . "'" . $this->roleRepo->encodeData($ware->id). "'" .')"  data-target="#basicvalCodeModal"><span class="badge bg-light-blue"><i class="fa fa-pencil"></i></span></a></span><span style="padding-left:50px;" ><a onclick="deleteEntity(' . "'" . $this->roleRepo->encodeData($ware->id). "'" .')" ><span class="badge bg-red"><i class="fa fa-trash-o"></i></span></a></span>';   
							//$warearr['edit'] = '<a href="entities/edit/'.$ware->id.'"><span class="badge bg-light-blue"><i class="fa fa-pencil"></i></span></a>'; 
							//$warearr['delete'] = '<a onclick="deleteEntity('.$ware->id.')" href=""><span class="badge bg-red"><i class="fa fa-trash-o"></i></span></a>';
                            $warearr['assign'] = ''; 
                            $warearr['children'] = $finalfloorarr;
                            $finalwarearr[] = $warearr;
                        }
                    }
                    else
                    {
                        $finalwarearr[] = '';
                    }    
                     $orgarr['id'] = $org->id;
                    $orgarr['entity_name'] = $org->entity_name;
                    $orgarr['entity_location'] = $org->entity_location;
                    $orgarr['entity_code'] = $org->entity_code;
                    //$orgarr['entity_location'] = $org->entity_location;
                    //$orgarr['entity_type_name'] = $org->entity_type_name;
                    $orgarr['capacity'] = $org->capacity;
                    $orgarr['entity_type_id'] = $org->entity_type_id;
                    $child_entity_type_id = $getWareHouseId[0]->value;
		            $orgarr['actions'] = '<a href="javascript:void(0);" onclick="addEntity1('.$child_entity_type_id.','.$org->id.','.$org->org_id.')"  data-target="#basicvalCodeModal"><span style="padding-left:20px;" ><span class="badge bg-green"><i class="fa fa-plus"></i></span></a></span><span style="padding-left:50px;" >'; 
					//$orgarr['edit'] = '<a href=""><span class="badge bg-light-blue"><i class="fa fa-pencil"></i></span></a>'; 
					//$orgarr['delete'] = '<a href=""><span class="badge bg-red"><i class="fa fa-trash-o"></i></span></a>';
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

   public function rackuse($entity_type_id,$parent_entity_id,$org_id)
	{
		//return $entity_type_id.'=='.$parent_entity_id.'--'.$org_id;
		$location_id= Entities::where('id',$parent_entity_id)->pluck('location_id');
		$rackuse_details = DB::table('wms_rack_types')->lists('rack_type_name', 'id');
		//$rackuse_details = DB::table('wms_entities')->where('parent_entity_id',$parent_entity_id)->lists('entity_name', 'id');
		//return $rackuse_details;
		//return $warehouse_details;
		//lists('entity_name', 'id')
		 
		return View::make('entities.rackuse',compact("rackuse_details","location_id","entity_type_id","parent_entity_id","org_id"));
	}
    
 	public function rackusestore($entity_type_id,$parent_entity_id,$org_id,$location_id,$rack_id)
	{
		//$parent_entity_id = $_REQUEST['warehouse_id'];
		/*$entity_id = $_REQUEST['rack_id'];
		$racktypes = RackType::find($entity_id);*/
		$entity_id = $rack_id;
		$racktypes = RackType::find($entity_id);
		//$entities = Entities::find($entity_id);
		/*		$location_id = Input::get('location_id');
		$entity_type_id =Input::get('entity_type_id');
		$parent_entity_id =Input::get('parent_entity_id');
		$org_id = Input::get('org_id');*/
		$location_id = $location_id;
		$entity_type_id = $entity_type_id;
		$parent_entity_id = $parent_entity_id;
		$org_id = $org_id;		
		
		if($racktypes->id)
			$rack_type_code = 'RT'.$racktypes->id;
		$racktypes->rack_type_code = $rack_type_code;
		 //$entity_type_id = $entities->entity_type_id;
		/*		$capacity_uom_id= UomGroup::where('description','capacity')->first()->id;
		$capacity_uom = DB::table('wms_uom')->where('uom_group_id',$capacity_uom_id)->lists('description', 'id');
        $capacity_uom = ['' => 'Select Capacity UOM'] + $capacity_uom;
        $dimension_uom_id= UomGroup::where('description','dimension')->first()->id;
		$dimension_uom = DB::table('wms_uom')->where('uom_group_id',$dimension_uom_id)->lists('description', 'id');
		$dimension_uom = ['' => 'Select Dimension UOM'] + $dimension_uom;*/
		$capacity_uom = $this->commonRepo->getLookupData('Capacity UOM');
        $dimension_uom = $this->commonRepo->getLookupData('Length UOM');
		//return $dimension_uom;
		//return $racktypes;
		 return View::make('entities.applyracktype',compact("capacity_uom","dimension_uom","racktypes","entities","entity_type_id","parent_entity_id","org_id","location_id"));
		
	}
//Excel Import    
 	public function saveEntitiesFromExcel()
    {
        $datas = Input::all();
        if(isset($datas['manufacturer_id'])){
        	$manufacturer_id = $datas['manufacturer_id'];
        }else{
        	$manufacturer_id = 0;
        }

        if(empty($manufacturer_id))
        {
	       	$currentUserId = \Session::get('userId');
	        \Log::info($currentUserId);
	        $manufacturerId = DB::table('users')->where('user_id', $currentUserId)->pluck('customer_id');
	        \Log::info($manufacturerId);        	
        }
        if(!empty($manufacturer_id) && $manufacturer_id != 0)
        {
        	$manufacturerId = $manufacturer_id;
        }
        //return $manufacturerId;
        $filePath = isset($_FILES['files']['tmp_name']) ? $_FILES['files']['tmp_name'] : '';
        $fileName = isset($_FILES['files']['name']) ? $_FILES['files']['name'] : '';
        $extension = 'csv';
        if($fileName != '')
        {
            $extension = pathinfo($_FILES['files']['name'], PATHINFO_EXTENSION);
        }
        if(strtoupper($extension) != 'CSV')
        {
            return 'Please provide CSV file.';
        }
        $errorMessage = '';
        if ($filePath != '')
        {
            if (!$fh = fopen($filePath, 'r'))
            {
                throw new Exception("Could not open $filePath for reading.");
            }            
            $i = 0;
            $buffer = array();
            $tempArray = array();
            while (!feof($fh))
            {
                $buffer[] = fgets($fh);
                $i++;
                $fields = array();
                foreach ($buffer as $line)
                {
                    $fields = explode(',', $line);
                }
                $tempArray[] = $fields;
            }
            if(isset($tempArray[0]) && in_array('Entity Name', $tempArray[0]))
            {
                unset($tempArray[0]);
            }
            if(isset($tempArray[count($tempArray)]) && count($tempArray[count($tempArray)]) < 2)
            {
                unset($tempArray[count($tempArray)]);
            }

            $count = 0;            
            $entitiesHeaders = array('Entity Name','Entity Location','Entity Type','Parent Entity','Capacity','Capacity UOM','Height','Width','Length','Depth','Dimension UOM','Location','X Co-ordinate','Y Co-ordinate','Z Co-ordinate','Warehouse'); 
                    
            $entitiesHeadersTrim = array_map('trim', $entitiesHeaders);         
            $insertentitiesData = array();            
            $tempStoredentitiess = array();
            $j = 1;
            $countrows = 0;
            $message = array();
            DB::beginTransaction();
            foreach ($tempArray as $entitiess)
            {
                if (!empty($entitiess) && !empty($entitiesHeadersTrim))
                {
                    if (count($entitiess) == count($entitiesHeadersTrim))
                    {
                        $tempStoredentitiess = array_combine($entitiesHeadersTrim, array_map('trim', $entitiess));

                                $data = array();
                                $data['entity_name'] = $tempStoredentitiess['Entity Name'];                             
								if($tempStoredentitiess['Entity Type'] == 1)
                                {
                                	$entity_type_id=$this->getUomID('WH Entity Types','Warehouse');
                                } 
								if($tempStoredentitiess['Entity Type'] == 2)
                                {
                                	$entity_type_id=$this->getUomID('WH Entity Types','Floor');
                                } 
								if($tempStoredentitiess['Entity Type'] == 3)
                                {
                                	$entity_type_id=$this->getUomID('WH Entity Types','Zone');                         	
                                }
								if($tempStoredentitiess['Entity Type'] == 4)
                                {
                                	$entity_type_id=$this->getUomID('WH Entity Types','Rack');
                                }
								if($tempStoredentitiess['Entity Type'] == 5)
                                {
                                	$entity_type_id=$this->getUomID('WH Entity Types','Bin');
                                }                                
								if($tempStoredentitiess['Entity Type'] == 6)
                                {
                                	$entity_type_id=$this->getUomID('WH Entity Types','OpenZone');                         
                                } 
								if($tempStoredentitiess['Entity Type'] == 7)
                                {
                                	$entity_type_id=$this->getUomID('WH Entity Types','Put Away Zone');
                                }
								if($tempStoredentitiess['Entity Type'] == 8)
                                {
                                	$entity_type_id=$this->getUomID('WH Entity Types','Dock');
                                }
								if($tempStoredentitiess['Entity Type'] == 9)
                                {
                                	$entity_type_id=$this->getUomID('WH Entity Types','Office');
                                } 
								if($tempStoredentitiess['Entity Type'] == 10)
                                {
                                	$entity_type_id=$this->getUomID('WH Entity Types','Stair Case');
                                } 
								if($tempStoredentitiess['Entity Type'] == 11)
                                {
                                	$entity_type_id=$this->getUomID('WH Entity Types','Lift');
                                }
                                $data['entity_type_id'] = $entity_type_id;
                                //$data['org_id'] = $manufacturerId;
                                if($tempStoredentitiess['Entity Type'] == 1)
                                {
                                	$wareId=0;
                                	$parent_entity_id=DB::table('wms_entities')
                                ->where(array('entity_name'=>$tempStoredentitiess['Parent Entity'],'org_id'=>$manufacturerId))->pluck('id');
                                }elseif($tempStoredentitiess['Entity Type'] == 2){
                                 	$wareId=DB::table('wms_entities')->where(array('entity_type_id'=>6001,'org_id'=>$manufacturerId,'entity_name'=> $tempStoredentitiess['Warehouse']))->pluck('id');
                                	$parent_entity_id=DB::table('wms_entities')
                                ->where(array('entity_name'=>$tempStoredentitiess['Parent Entity'],'org_id'=>$manufacturerId))->pluck('id');                               	
                                }else{
                                	$wareId=DB::table('wms_entities')->where(array('entity_type_id'=>6001,'org_id'=>$manufacturerId,'entity_name'=> $tempStoredentitiess['Warehouse']))->pluck('id');
                                	$parent_entity_id=DB::table('wms_entities')
                                ->where(array('entity_name'=>$tempStoredentitiess['Parent Entity'],'org_id'=>$manufacturerId,'ware_id'=>$wareId))->pluck('id');
                                }                                
                                /*$parent_entity_id=DB::table('wms_entities')
                                ->where(array('entity_name'=>$tempStoredentitiess['Parent Entity'],'org_id'=>$manufacturerId))->pluck('id');*/
                                //parentEntity
                                $data['parent_entity_id'] = $parent_entity_id;
                                $data['capacity'] = $tempStoredentitiess['Capacity'];
                                //capacityUomID
                                if($tempStoredentitiess['Capacity UOM'] == 1)
                                {
                                	$capacity_uom_id=$this->getUomID('Capacity UOM','Litre');
                                }
                                if($tempStoredentitiess['Capacity UOM'] == 2)
                                {
                                	$capacity_uom_id=$this->getUomID('Capacity UOM','KiloGrams');
                                }
                                if($tempStoredentitiess['Capacity UOM'] == 3)
                                {
                                	$capacity_uom_id=$this->getUomID('Capacity UOM','Grams');
                                }
                                if($tempStoredentitiess['Capacity UOM'] == 4)
                                {
                                	$capacity_uom_id=$this->getUomID('Capacity UOM','Metric Tonnes');
                                }                                                                                              
                                //capacityUomID
                                $data['capacity_uom_id'] = $capacity_uom_id;
                                //locationsDetails
								$location_type_id = DB::table('location_types')
						                        ->where('manufacturer_id',$manufacturerId)
						           				->whereIn('location_type_name',['warehouse','Depot','Plant'])
						        				->lists('location_type_id');
						        if(empty($location_type_id))
						        {
						        	$location_type_id = ''; 
						        }
        						$locations = DB::table('locations')->whereIn('location_type_id',$location_type_id)
        						->where(array('manufacturer_id'=>$manufacturerId,'location_name'=>$tempStoredentitiess['Location'],'is_deleted'=>0))->pluck('location_id');
        						if(!empty($locations))
        						{
        							$data['location_id'] = $locations;
        						}                          
                                //locationsdetails                                
                                $data['ware_id'] = $wareId;
                                $data['status'] = 1;
                                $data['org_id'] = $manufacturerId;
                                $data['entity_location']=$tempStoredentitiess['Entity Location'];
                                $data['xco']=$tempStoredentitiess['X Co-ordinate'];
                                $data['yco']=$tempStoredentitiess['Y Co-ordinate'];
                                $data['zco']=$tempStoredentitiess['Z Co-ordinate'];
                            	$data['height']=$tempStoredentitiess['Height'];
                            	$data['width']=$tempStoredentitiess['Width'];
                            	$data['depth']=$tempStoredentitiess['Depth'];
                            	$data['length']=$tempStoredentitiess['Length'];
                            	/*$data['zone_type_id']=$tempStoredentitiess['Zone Type'];*/
                            	$data['area']= $data['length'] * $data['width'];
                            	//Dimension UOM ID
                            	if($tempStoredentitiess['Dimension UOM'] == 1)
                            	{
			                       $dimension_uom_id=$this->getUomID('Length UOM','Meter');
                            	}
                            	if($tempStoredentitiess['Dimension UOM'] == 2)
                            	{  
			                       $dimension_uom_id=$this->getUomID('Length UOM','Feet');
                            	} 
                            	if($tempStoredentitiess['Dimension UOM'] == 3)
                            	{  
			                       $dimension_uom_id=$this->getUomID('Length UOM','Yard');
                            	}
                            	//Dimension UOM ID
                            	$data['uom_id']=$dimension_uom_id;
                            	//return $data;
                            	$insert = $this->excelStoreEntities($data);
                            	//return 'Exists ='.$insert;
                            	if($insert['status'] == 1){
                            		$countrows++;
                            	}else{
                            		return $insert;
                            	}
                    }
                }
            }
            if($countrows == count($tempArray))
            {
                DB::commit();
                return Response::json([
                    'status' => 'success',
                    'sucess_records' => $countrows,
                    'message' => 'Imported sucessfully'
                ]);
            }else{
                DB::rollback();
                return Response::json([
                    'status' => true,
                    'sucess_records' => $countrows,
                    'failed_records' => (count($tempArray) - $countrows),
                    //'message' => $message
                    'message' => 'Row count does not match'
                ]);
            }
        }
    }
//Excel Import   

	public function validateEntity()
   {

      $data = Input::get('entity_name');
      $id = Input::get('id');
      $parentEntityId = Input::get('parent_entity_id');
      $entityId = Input::get('entity_type_id');
      $orgId = Input::get('org_id');
      $wareId = Input::get('ware_id');


			if(empty($id))
            {
              
              if($entityId == 6001)
                        {
                    
                            $entityname = DB::Table('wms_entities') 
                                          ->where('entity_name',$data)
                                          ->where('entity_type_id',$entityId)
                                          ->where('org_id',$orgId)
                                          ->pluck('entity_name');
                        }
                        elseif ($entityId == 6002 ) {
                        	$entityname = DB::Table('wms_entities') 
                                          ->where('entity_name',$data)
                                          ->where('entity_type_id',$entityId)
                                          ->where('parent_entity_id',$parentEntityId)
                                          ->where('org_id',$orgId)
                                          ->pluck('entity_name');
                        }
            
                        elseif ($entityId == 6003 || $entityId == 6006 || $entityId == 6007 || $entityId == 6008)
                        {
                        	$entityname = DB::Table('wms_entities') 
                                          ->where('entity_name',$data)
                                          ->where('parent_entity_id',$parentEntityId)
                                          ->where('org_id',$orgId)
                                          ->where('ware_id',$wareId)
                                          ->pluck('entity_name');	
                        }
            
                        elseif ($entityId ==6004) {
                        	$entityname = DB::Table('wms_entities') 
                                          ->where('entity_name',$data)
                                          ->where('entity_type_id',$entityId)
                                          ->where('parent_entity_id',$parentEntityId)
                                          ->where('org_id',$orgId)
                                          ->where('ware_id',$wareId)
                                          ->pluck('entity_name');
                        }
            
                        elseif ($entityId == 6005)
                        {
                        	$entityname = DB::Table('wms_entities') 
                                          ->where('entity_name',$data)
                                          ->where('entity_type_id',$entityId)
                                          ->where('parent_entity_id',$parentEntityId)
                                          ->where('org_id',$orgId)
                                          ->where('ware_id',$wareId)
                                          ->pluck('entity_name');
            
                        }


            }

            else
            	{

             	if($entityId == 6001)
                        {
                    		
                            $entityname = DB::Table('wms_entities')
                            			  ->where('id' ,'!=',$id) 
                                          ->where('entity_name',$data)
                                          ->where('entity_type_id',$entityId)
                                          ->where('org_id',$orgId)
                                          ->pluck('entity_name');

                        }
                        elseif ($entityId == 6002 ) {
                        	$entityname = DB::Table('wms_entities')
                        				  ->where('id' ,'!=',$id) 
                                          ->where('entity_name',$data)
                                          ->where('entity_type_id',$entityId)
                                          ->where('parent_entity_id',$parentEntityId)
                                          ->where('org_id',$orgId)
                                          ->where('ware_id',$wareId)
                                          ->pluck('entity_name');
                        }
            
                        elseif ($entityId == 6003 || $entityId == 6006 || $entityId == 6007 || $entityId == 6008)
                        {
                        	$entityname = DB::Table('wms_entities') 
                                          ->where('id' ,'!=',$id)
                                          ->where('entity_name',$data)
                                          ->where('parent_entity_id',$parentEntityId)
                                          ->where('org_id',$orgId)
                                          ->where('ware_id',$wareId)
                                          ->pluck('entity_name');	
                        }
            
                        elseif ($entityId ==6004) {
                        	$entityname = DB::Table('wms_entities') 
                                          ->where('id' ,'!=',$id)
                                          ->where('entity_name',$data)
                                          ->where('entity_type_id',$entityId)
                                          ->where('parent_entity_id',$parentEntityId)
                                          ->where('org_id',$orgId)
                                          ->where('ware_id',$wareId)
                                          ->pluck('entity_name');
                        }
            
                        elseif ($entityId == 6005)
                        {
                        	$entityname = DB::Table('wms_entities') 
                                          ->where('id' ,'!=',$id)
                                          ->where('entity_name',$data)
                                          ->where('entity_type_id',$entityId)
                                          ->where('parent_entity_id',$parentEntityId)
                                          ->where('org_id',$orgId)
                                          ->where('ware_id',$wareId)
                                          ->pluck('entity_name');
            
                        }

           			
                    }
			        if(empty($entityname))
			           {
			            //return 'success';
			            return json_encode([ "valid" => true ]);
			           }                     
			          else 
			          {
			            //return 'fail';
			            return json_encode([ "valid" => false ]);
			          }          

   }
	public function getUomID($lookupCategoryName,$masterLookupName)
	{
       $dimension_uom_id=DB::table('lookup_categories')
                		->join('master_lookup','lookup_categories.id','=','master_lookup.category_id')
                		->where(array('lookup_categories.name'=>$lookupCategoryName,'master_lookup.name'=>$masterLookupName))
                		->pluck('master_lookup.value');	
       return $dimension_uom_id;	
	}
	public function feetConverter($uomid,$length,$height,$width,$depth)
	{
		//meter to feet conversion
		if($uomid == 12001){
			$dimension=array();
			$dimension['length'] = round(($length / 0.3048),2);
			$dimension['height'] = round(($height / 0.3048),2);
			$dimension['width'] = round(($width / 0.3048),2);
			$dimension['depth'] = round(($depth / 0.3048),2);
			$dimension['area'] = $dimension['length']*$dimension['width'];
			return $dimension;
		}
		//yard to feet conversion
		if($uomid == 12003){
			$dimension=array();
			$dimension['length'] = round(($length / 0.33333),2);
			$dimension['height'] = round(($height / 0.33333),2);
			$dimension['width'] = round(($width / 0.33333),2);
			$dimension['depth'] = round(($depth / 0.33333),2);
			$dimension['area'] = $dimension['length']*$dimension['width'];
			return $dimension;
		}
		if($uomid == 12002){
			$dimension=array();
			$dimension['length'] = $length;
			$dimension['height'] = $height;
			$dimension['width'] = $width;
			$dimension['depth'] = $depth;
			$dimension['area'] = $dimension['length']*$dimension['width'];
			return $dimension;
		}
	}	
	public function fromFeetConverter($uomid,$length,$height,$width,$depth)
	{
		//feet to meter conversion
		if($uomid == 12001){
			$dimension=array();
			$dimension['length'] = round($length * 0.3048,2);
			$dimension['height'] = round($height * 0.3048,2);
			$dimension['width'] = round($width * 0.3048,2);
			$dimension['depth'] = round($depth * 0.3048,2);
			$dimension['area'] = $dimension['length']*$dimension['width'];
			return $dimension;
		}
		//feet to yard conversion
		if($uomid == 12003){
			$dimension=array();
			$dimension['length'] = round($length * 0.33333,2);
			$dimension['height'] = round($height * 0.33333,2);
			$dimension['width'] = round($width * 0.33333,2);
			$dimension['depth'] = round($depth * 0.33333,2);
			$dimension['area'] = $dimension['length']*$dimension['width'];
			return $dimension;
		}
		if($uomid == 12002){
			$dimension=array();
			$dimension['length'] = $length;
			$dimension['height'] = $height;
			$dimension['width'] = $width;
			$dimension['depth'] = $depth;
			$dimension['area'] = $dimension['length']*$dimension['width'];
			return $dimension;
		}
	}
	public function kgConverter($uomid,$capacity)
	{
		//litre to KG conversion
		if($uomid == 13001){
			$dimension=array();
			$dimension['capacity'] = $capacity * 1;
			return $dimension;
		}
		//gram to KG conversion
		if($uomid == 13003){
			$dimension=array();
			$dimension['capacity'] = $capacity / 1000;
			return $dimension;
		}
		//MetricTon to KG conversion
		if($uomid == 13004){
			$dimension=array();
			$dimension['capacity'] = $capacity * 1000;
			return $dimension;
		}		
		if($uomid == 13002){
			$dimension['capacity'] = $capacity;
			return $dimension;
		}
	}	
	public function fromKgConverter($uomid,$capacity)
	{
		//KG to litre conversion
		if($uomid == 13001){
			$dimension=array();
			$dimension['capacity'] = $capacity / 1;
			return $dimension;
		}
		//KG to gram conversion
		if($uomid == 13003){
			$dimension=array();
			$dimension['capacity'] = $capacity * 1000;
			return $dimension;
		}
		//KG to MetricTon conversion
		if($uomid == 13004){
			$dimension=array();
			$dimension['capacity'] = $capacity / 1000;
			return $dimension;
		}		
		if($uomid == 13002){
			$dimension['capacity'] = $capacity;
			return $dimension;
		}
	}
	public function excelStoreEntities($data)
	{
		//return 2;
		$entity_type_id=$data['entity_type_id'];
        if(!isset($data['xco'])){
	       $data['xco'] = '';
        }	
        if(!isset($data['yco'])){
	       $data['yco'] = '';
        }	
        if(!isset($data['zco'])){
	       $data['zco'] = '';
        }
        if(empty($data['location_id']))	{
 			return array('status' => 0,
                    'message' => 'Given Location doesnot exist for this customer for entity '.$data['entity_name']);	        	
        }
        if(empty($data['entity_name']))	{
 			return array('status' => 0,
                    'message' => 'Please provide Entity Name for all entities.');	        	
        }         
        if(empty($data['entity_location']))	{
 			return array('status' => 0,
                    'message' => 'Please provide Entity Location for entity '.$data['entity_location']);	        	
        }                
        if(empty($data['entity_type_id']))	{
 			return array('status' => 0,
                    'message' => 'Please provide Entity Type for entity '.$data['entity_name']);	        	
        }   
        if(empty($data['capacity_uom_id']))	{
 			return array('status' => 0,
                    'message' => 'Please provide Capacity UOM for entity '.$data['entity_name']);	        	
        }  
        if(empty($data['capacity']))	{
 			return array('status' => 0,
                    'message' => 'Please provide Capacity for entity '.$data['entity_name']);	        	
        }        
        if(empty($data['uom_id']))	{
 			return array('status' => 0,
                    'message' => 'Please provide Dimension UOM for entity '.$data['entity_name']);	        	
        } 
        if(empty($data['length']) && $data['length'] < 0)	{
 			return array('status' => 0,
                    'message' => 'Please provide Length for entity '.$data['entity_name']);	        	
        }  
        if(empty($data['width']) &&  $data['width'] < 0)	{
 			return array('status' => 0,
                    'message' => 'Please provide Width for entity '.$data['entity_name']);	        	
        } 
        if(empty($data['depth']) &&  $data['depth'] < 0)	{
 			return array('status' => 0,
                    'message' => 'Please provide Depth for entity '.$data['entity_name']);	        	
        } 
        if(empty($data['height']) &&  $data['height'] < 0)	{
 			return array('status' => 0,
                    'message' => 'Please provide Height for entity '.$data['entity_name']);	        	
        }      
        if($data['entity_type_id'] != 6001 && empty($data['ware_id']))	{
 			return array('status' => 0,
                    'message' => 'Please provide Warehouse for entity '.$data['entity_name']);	        	
        }                                                       
        if(empty($data['parent_entity_id']))	{
 			return array('status' => 0,
                    'message' => 'Given Parent doesnot exist.Please provide valid parent entity for entity '.$data['entity_name']);	        	
        }        
        $cpty = $data['capacity'];
        $uom_id = $data['capacity_uom_id'];
        $capacity = $this->kgConverter($uom_id,$cpty);
		$dimUom1 = $data['uom_id'];
		$dimLen1 = $data['length'];
		$dimWid1 = $data['width'];
		$dimDep1 = $data['depth'];
		$dimHei1 = $data['height'];
		$dimFeet1=$this->feetConverter($dimUom1,$dimLen1,$dimHei1,$dimWid1,$dimDep1);        

        $exists = $this->validateExcelEntity($data);
        //return $exists;
        if($exists)
        {  
		    $checkLength = $this->checkForDimensions($data,$dimFeet1['length'],'length');
		    $checkWidth = $this->checkForDimensions($data,$dimFeet1['width'],'width');
		    $checkDepth = $this->checkForDimensions($data,$dimFeet1['depth'],'depth');
		    $checkHeight = $this->checkForDimensions($data,$dimFeet1['height'],'height');
		    $checkCapacity = $this->checkForDimensions($data,$capacity['capacity'],'capacity');
		    $checkArea = $this->checkForDimensions($data,$dimFeet1['area'],'area');
		    if($checkCapacity)
		    {
			    if($checkLength)
			    {
			    	if($checkWidth)
			    	{
			    		if($checkDepth)
			    		{
			    			if($checkHeight)
			    			{
				    			if($checkArea)
				    			{									
									DB::table('wms_entities')->insert([
								    	'entity_name' => $data['entity_name'],
										'entity_type_id' => $entity_type_id,
										'entity_location' => $data['entity_location'],
										'location_id' => $data['location_id'],
										'ware_id' => $data['ware_id'],
										'org_id' => $data['org_id'],
										'capacity' =>$capacity['capacity'],
										'xco' => $data['xco'],
										'yco' => $data['yco'],
										'zco' => $data['zco'],
										'capacity_uom_id' => $data['capacity_uom_id'],
										'parent_entity_id'=> $data['parent_entity_id'],
										'status' => 1,
									]);		
							  		$entity_id = DB::getPdo()->lastInsertId();
							  		if($entity_type_id == 6005)
							  		{
							  			 $parent_bin_id = $data['parent_entity_id'];
							  			 $rack_value_id = DB::table('wms_entities')
										  			        ->where('id',$parent_bin_id)
										  			        ->pluck('parent_entity_id');
										 $zone_value_id = DB::table('wms_entities')
										  			        ->where('id',$rack_value_id)
										  			        ->pluck('parent_entity_id');
							            $bins = array();
							            $bins['entity_id'] = $entity_id;
							            $bins['storage_bin_name'] = $data['entity_name'];
							            $bins['storage_bin_id'] = $data['entity_location'];
							            $bins['status'] = 'Empty';
							            $bins['org_id'] = $data['org_id'];
							            $bins['ware_id'] = $data['ware_id'];
							            $bins['floor_id'] = $zone_value_id;
							            //$bins['storage_capacity'] = $data['capacity'];
							            $bins['storage_capacity'] = $capacity['capacity'];
							            //$bins['storage_capacity'] = 0;
							            $bins['is_allocated'] = 0;
							            $bin_enti_id = DB::table('wms_storage_bins')->insertGetId($bins);	         
							        }       
							        if($entity_type_id == 6001)
									{
										$entity_code = 'W'.$entity_id;
									}
									else if($entity_type_id==6003)
									{
										$entity_code = 'Z'.$entity_id;
									}
									else if($entity_type_id==6004)
									{
										$entity_code = 'R'.$entity_id;
									}
									else if($entity_type_id==6005)
									{
										$entity_code = 'B'.$entity_id;
									}
									else if($entity_type_id==6006)
									{
										$entity_code = 'Oz'.$entity_id;
									}
									else if($entity_type_id==6007)
									{
										$entity_code = 'Paz'.$entity_id;
									}
									else if($entity_type_id==6008)
									{
										$entity_code = 'D'.$entity_id;
									}
									else
									{
							            $entity_code = 'F'.$entity_id;
									}
									 $entities = Entities::find($entity_id);
									 $entities->entity_code = $entity_code;
									 $entities->save();
										$dimUom = $data['uom_id'];
										$dimLen = $data['length'];
										$dimWid = $data['width'];
										$dimDep = $data['depth'];
										$dimHei = $data['height'];
										$dimFeet=$this->feetConverter($dimUom,$dimLen,$dimHei,$dimWid,$dimDep);
										$dimensions = new Dimension;
										$dimensions->entity_id = $entity_id;
										$dimensions->height = $dimFeet['height'];
										$dimensions->width = $dimFeet['width'];
										$dimensions->depth = $dimFeet['depth'];
										$dimensions->length = $dimFeet['length'];		
										$dimensions->area = $dimFeet['area'];
										$dimensions->uom_id = $data['uom_id'];
										$dimensions->save();
										$dimensions_entity = DB::table('wms_dimensions')->select('id','entity_id')
																->where('entity_id',$entity_id)->get();		
										$entity_dimension_update = DB::table('wms_entities')
																	->where('id',$dimensions_entity[0]->entity_id)
																	->update(array('dimension_id'=>$dimensions_entity[0]->id));
										if($entity_type_id==6006 || $entity_type_id==6007)
										{				
											DB::table('wms_entities')->insert([
											'entity_name' => 'Openbin',
											'entity_type_id' => 6005,
											'entity_location' => $data['entity_location'],
											'location_id' => $data['location_id'],
											'ware_id' => $data['ware_id'],
											'org_id' => $data['org_id'],
											'capacity' => 0,
											'capacity_uom_id' => $data['capacity_uom_id'],
											'parent_entity_id'=> $entity_id,
											'status' => 1
											]);
											$bin_entity_id = DB::getPdo()->lastInsertId();
											$floor_value_id = DB::table('wms_entities')
											  			        ->where('id',$entity_id)
											  			        ->pluck('parent_entity_id');			  			        				
								            $bins = array();
								            $bins['entity_id'] = $bin_entity_id;
								            $bins['storage_bin_name'] = 'Openbin';
								            $bins['storage_bin_id'] = $data['entity_location'];
								            $bins['status'] = 'Empty';
								            $bins['org_id'] = $data['org_id'];
								            $bins['ware_id'] = $data['ware_id'];
								            $bins['floor_id'] = $floor_value_id;
								            //$bins['storage_capacity'] = $data['capacity'];
								            $bins['storage_capacity'] = 0;
								            $bins['is_allocated'] = 0;
								            $bin_enti_id = DB::table('wms_storage_bins')->insertGetId($bins);
											//return $bin_enti_id;		            		        
											$bin_entity_code = 'B'.$bin_entity_id;
											$entities = Entities::find($bin_entity_id);
											$entities->entity_code = $bin_entity_code;
											$entities->save(); 
											$dimensions = new Dimension;
											$dimensions->entity_id = $bin_entity_id;
											$dimensions->height = 0;
											$dimensions->width = 0;
											$dimensions->depth = 0;
											$dimensions->length = 0;
											$dimensions->area = 0;
											$dimensions->uom_id = $data['uom_id'];
											$dimensions->save();
											$dimensions_entity_bin = DB::table('wms_dimensions')->select('id','entity_id')
																->where('entity_id',$bin_entity_id)->get();		
											$entity_dimension_update = DB::table('wms_entities')
																	->where('id',$dimensions_entity_bin[0]->entity_id)
																	->update(array('dimension_id'=>$dimensions_entity_bin[0]->id));
										    //$bin_id = DB::getPdo()->lastInsertId();	
										}			
										//return 1; 
										return array(
							                    'status' => 1,
							                    'message' => 'Entity inserted Successfully.'
							                );
				    			}else{
				    				//return 'Height is exceeding';
				    				return array('status' => 0,
		                    				'message' => 'You dont have area to create Entities from name ' .$data['entity_name'].'.');
				    			}
			    			}else{
			    				//return 'Height is exceeding';
			    				return array('status' => 0,
	                    				'message' => 'Height is exceeding for Entity with name ' .$data['entity_name'].'.');
			    			}
			    		}else{
		    				//return 'Depth is exceeding';
		    				return array('status' => 0,
	                    			'message' => 'Depth is exceeding for Entity with name ' .$data['entity_name'].'.');
		    			}
			    	}else{
	    				//return 'Width is exceeding';
	    				return array('status' => 0,
	                    		'message' => 'Width is exceeding for Entity with name ' .$data['entity_name'].'.');
	    			}
			    }else{
					//return 'Length is exceeding';
					return array('status' => 0,
	                    	'message' => 'Length is exceeding for Entity with name ' .$data['entity_name'].'.');
				}
			}else{
					//return 'Capacity is exceeding';
					return array('status' => 0,
	                    	'message' => 'Capacity is exceeding for Entity with name ' .$data['entity_name'].'.');
			}							    			
	  	}else{
	  		//return 0;
			return array('status' => 0,
                    'message' => 'Entity with name ' .$data['entity_name'].' exists.');	  		
	  	}	
	}
	public function validateExcelEntity($data)
	{
      $entity = $data['entity_name'];
      $parentEntityId = $data['parent_entity_id'];
      $entityId = $data['entity_type_id'];
      $orgId = $data['org_id'];
      $wareId = $data['ware_id'];
		if($entityId == 6001)
		{
			$entityname = DB::Table('wms_entities') 
						  ->where('entity_name',$entity)
						  ->where('entity_type_id',$entityId)
						  ->where('org_id',$orgId)
						  ->pluck('entity_name');
		}
		elseif ($entityId == 6002 ) {
			$entityname = DB::Table('wms_entities') 
						  ->where('entity_name',$entity)
						  ->where('entity_type_id',$entityId)
						  ->where('parent_entity_id',$parentEntityId)
						  ->where('org_id',$orgId)
						  ->where('ware_id',$wareId)
						  ->pluck('entity_name');
		}
		elseif ($entityId == 6003 || $entityId == 6006 || $entityId == 6007 || $entityId == 6008)
		{
			$entityname = DB::Table('wms_entities') 
						  ->where('entity_name',$entity)
						  ->where('parent_entity_id',$parentEntityId)
						  ->where('org_id',$orgId)
						  ->where('ware_id',$wareId)
						  ->pluck('entity_name');	
		}
		elseif ($entityId ==6004) {
			$entityname = DB::Table('wms_entities') 
						  ->where('entity_name',$entity)
						  ->where('entity_type_id',$entityId)
						  ->where('parent_entity_id',$parentEntityId)
						  ->where('org_id',$orgId)
						  ->where('ware_id',$wareId)
						  ->pluck('entity_name');
		}
		elseif ($entityId == 6005)
		{
			$entityname = DB::Table('wms_entities') 
						  ->where('entity_name',$entity)
						  ->where('entity_type_id',$entityId)
						  ->where('parent_entity_id',$parentEntityId)
						  ->where('org_id',$orgId)
						  ->where('ware_id',$wareId)
						  ->pluck('entity_name');

		}
		if(empty($entityname))
		   {
			return 1;
		   }                     
		else 
		  {
			return 0;
		  }          
	}
	public function checkForDimensions($data,$dimension,$mode)
	{
		if($mode == 'capacity')
		{
			if($data['entity_type_id'] == 6001)
			{
				return 1;
			}else{
				$parentCapacity = DB::table('wms_entities')->where('id',$data['parent_entity_id'])->pluck('capacity');
				$parentsChildCapacity = DB::table('wms_entities')->where('parent_entity_id',$data['parent_entity_id'])->sum('capacity');
				$remCapacity = $parentCapacity - $parentsChildCapacity;
				if(intval($remCapacity) >= intval($dimension))
				{
					return 1;
				}else{
					return 0;
				}
			}
		}elseif($mode == 'area') 
		{
			if($data['entity_type_id'] == 6001)
			{
				return 1;
			}else{
				$parentArea = DB::table('wms_entities')->join('wms_dimensions','wms_entities.id','=','wms_dimensions.entity_id')->where('wms_entities.id',$data['parent_entity_id'])->pluck('wms_dimensions.area');
				$parentsChildArea = DB::table('wms_entities')->join('wms_dimensions','wms_entities.id','=','wms_dimensions.entity_id')->where('wms_entities.parent_entity_id',$data['parent_entity_id'])->sum('wms_dimensions.area');
				$remArea = $parentArea - $parentsChildArea;
				//$remArea = $parentArea;
				if(intval($remArea) >= intval($dimension))
				{
					return 1;
				}else{
					return 0;
				}
			}
		}elseif($mode == 'length') 
		{
			if($data['entity_type_id'] == 6001)
			{
				return 1;
			}else{
				$parentLength = DB::table('wms_entities')->join('wms_dimensions','wms_entities.id','=','wms_dimensions.entity_id')->where('wms_entities.id',$data['parent_entity_id'])->pluck('wms_dimensions.length');
				//$parentsChildLength = DB::table('wms_entities')->join('wms_dimensions','wms_entities.id','=','wms_dimensions.entity_id')->where('wms_entities.parent_entity_id',$data['parent_entity_id'])->sum('wms_dimensions.length');
				//$remLength = $parentLength - $parentsChildLength;
				//
				$remLength = $parentLength;
				if(intval($remLength) >= intval($dimension))
				{
					return 1;
				}else{
					return 0;
				}
			}
		}elseif($mode == 'depth') {
			if($data['entity_type_id'] == 6001)
			{
				return 1;
			}else{
				$parentDepth = DB::table('wms_entities')->join('wms_dimensions','wms_entities.id','=','wms_dimensions.entity_id')->where('wms_entities.id',$data['parent_entity_id'])->pluck('wms_dimensions.depth');
				//$parentsChildDepth = DB::table('wms_entities')->join('wms_dimensions','wms_entities.id','=','wms_dimensions.entity_id')->where('wms_entities.parent_entity_id',$data['parent_entity_id'])->sum('wms_dimensions.depth');
				//$remDepth = $parentDepth - $parentsChildDepth;
				$remDepth = $parentDepth;
				if(intval($remDepth) >= intval($dimension))
				{
					return 1;
				}else{
					return 0;
				}
			}
		}elseif($mode == 'height') {
			if($data['entity_type_id'] == 6001)
			{
				return 1;
			}/*elseif($data['entity_type_id'] == 6005){
				$parentWidth = DB::table('wms_entities')->join('wms_dimensions','wms_entities.id','=','wms_dimensions.entity_id')->where('wms_entities.id',$data['parent_entity_id'])->pluck('wms_dimensions.width');
				//$parentsChildWidth = DB::table('wms_entities')->join('wms_dimensions','wms_entities.id','=','wms_dimensions.entity_id')->where('wms_entities.parent_entity_id',$data['parent_entity_id'])->sum('wms_dimensions.width');
				$remWidth = $parentWidth;
				if($remWidth >= $dimension)
				{
					return 1;
				}else{
					return 0;
				}
			}*/else{
				$parentHeight = DB::table('wms_entities')->join('wms_dimensions','wms_entities.id','=','wms_dimensions.entity_id')->where('wms_entities.id',$data['parent_entity_id'])->pluck('wms_dimensions.height');
				//$parentsChildHeight = DB::table('wms_entities')->join('wms_dimensions','wms_entities.id','=','wms_dimensions.entity_id')->where('wms_entities.parent_entity_id',$data['parent_entity_id'])->sum('wms_dimensions.height');
				//$remHeight = $parentHeight - $parentsChildHeight;
				$remHeight = $parentHeight;
				if(intval($remHeight) >= intval($dimension))
				{
					return 1;
				}else{
					return 0;
				}
			}
		}
		elseif($mode == 'width') {
			if($data['entity_type_id'] == 6001)
			{
				return 1;
			}elseif($data['entity_type_id'] == 6005){
				$parentWidth = DB::table('wms_entities')->join('wms_dimensions','wms_entities.id','=','wms_dimensions.entity_id')->where('wms_entities.id',$data['parent_entity_id'])->pluck('wms_dimensions.width');
				//$parentsChildWidth = DB::table('wms_entities')->join('wms_dimensions','wms_entities.id','=','wms_dimensions.entity_id')->where('wms_entities.parent_entity_id',$data['parent_entity_id'])->sum('wms_dimensions.width');
				$remWidth = $parentWidth;
				if(intval($remWidth) >= intval($dimension))
				{
					return 1;
				}else{
					return 0;
				}
			}else{
				$parentWidth = DB::table('wms_entities')->join('wms_dimensions','wms_entities.id','=','wms_dimensions.entity_id')->where('wms_entities.id',$data['parent_entity_id'])->pluck('wms_dimensions.width');
				//$parentsChildWidth = DB::table('wms_entities')->join('wms_dimensions','wms_entities.id','=','wms_dimensions.entity_id')->where('wms_entities.parent_entity_id',$data['parent_entity_id'])->sum('wms_dimensions.width');
				/*$remWidth = $parentWidth - $parentsChildWidth;*/
				$remWidth = $parentWidth;
				if(intval($remWidth) >= intval($dimension))
				{
					return 1;
				}else{
					return 0;
				}
			}
		}

	}

	public function getChildEntities($entity_id)
	{
		$child = Entities::Where('id',$entity_id)->orWhere('parent_entity_id',$entity_id)->pluck(DB::raw('group_concat(id)'));
		if(!empty($child))
		{
			$child = explode(',', $child);
	  		$child = array_unique($child);
			$childChild = Entities::WhereIn('parent_entity_id',$child)->orWhereIn('id',$child)
			->pluck(DB::raw('group_concat(id)'));
			//return $childChild;
			if(!empty($childChild))
			{
				$childChild = explode(',', $childChild);
		  		$childChild = array_unique($childChild);
				$childChildChild = Entities::WhereIn('parent_entity_id',$childChild)->orWhereIn('id',$childChild)
				->pluck(DB::raw('group_concat(id)'));
				if(!empty($childChildChild))
				{
					$childChildChild = explode(',', $childChildChild);
			  		$childChildChild = array_unique($childChildChild);	
			  		//return $childChildChild;	
					$childChildChildChild = Entities::WhereIn('parent_entity_id',$childChildChild)->orWhereIn('id',$childChildChild)
					->pluck(DB::raw('group_concat(id)'));
					if(!empty($childChildChildChild))
					{
						$childChildChildChild = explode(',', $childChildChildChild);
				  		$childChildChildChild = array_unique($childChildChildChild);	
				  		return $childChildChildChild;				
				  	}
				  	else
				  	{
						$childChildChildChild = explode(',', $childChildChildChild);
				  		$childChildChildChild = array_unique($childChildChildChild);
				  		return $childChildChildChild;	  		
				  	}								  					
			  	}
			  	else
			  	{
					$childChildChild = explode(',', $childChildChild);
			  		$childChildChild = array_unique($childChildChild);
			  		return $childChildChild;	  		
			  	}		  		
			}
			else
			{
				$childChild = explode(',', $childChild);
		  		$childChild = array_unique($childChild);
		  		return $childChild;				
			}	  		
		}
		else
		{
			$child = explode(',', $child);
	  		$child = array_unique($child);
	  		return $child;			
		}
		
	}
	public function checkStorageBins($entities,$mfg_id,$ware_id)
	{
		$valid = DB::table('wms_storage_bins')->whereIn('entity_id',$entities)
		->pluck(DB::raw('group_concat(storage_bin_id)'));
		if(!empty($valid))
		{
			$valid = explode(',', $valid);
			$valid = array_unique($valid);
			$checkBin = DB::table('eseal_'.$mfg_id)->whereIn('bin_location',$valid)->where('ware_id',$ware_id)
			->pluck(DB::raw('group_concat(bin_location)'));
			if(!empty($checkBin))
			{
				return $checkBin;
			}
			else
			{
				return 1;
			}
		}
		else
		{
			return 1;
		}

	}

}



