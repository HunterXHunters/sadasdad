<?php

use Central\Repositories\RoleRepo;

class AssignlocationController extends \BaseController 
{
	private $roleRepo;
	public function __construct()
    {
        $this->roleRepo = new RoleRepo;
    }
	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{
		
		return View::make('assignlocation.index');
	}
	public function getdata()
	{
		//$mapEntities = DB::table('eseal')->get();
		$currentUserId = \Session::get('userId');
        \Log::info($currentUserId);
        $manufacturerId = DB::table('users')->where('user_id', $currentUserId)->pluck('customer_id');
        \Log::info($manufacturerId);
        if($manufacturerId)
        { 
        	$mapEntities = DB::table('wms_eseal')
            ->leftJoin('wms_entities', 'wms_entities.id', '=', 'wms_eseal.entity_id')
            ->leftJoin('products', 'products.product_id', '=', 'wms_eseal.product_id')
            ->select('products.name', 'wms_eseal.id','wms_entities.entity_name','wms_eseal.product_id','wms_eseal.locator')
            ->where('wms_eseal.org_id',$manufacturerId)
            ->get();
        } else{
        	$mapEntities = DB::table('wms_eseal')
            ->leftJoin('wms_entities', 'wms_entities.id', '=', 'wms_eseal.entity_id')
            ->leftJoin('products', 'products.product_id', '=', 'wms_eseal.product_id')
            ->select('products.name', 'wms_eseal.id','wms_entities.entity_name','wms_eseal.product_id','wms_eseal.locator')
            ->get();
        }

            $getArr = array();
            $finalgetArr = array();
            foreach($mapEntities as $value)
            {
            	$getArr['id'] = $value->id;
            	$getArr['entity_name'] = $value->entity_name;
               	$getArr['product_name'] = $value->name;
            	$getArr['locator'] = $value->locator;
                $getArr['actions']='<span style="padding-left:20px;"><a href="javascript:void(0);" onclick="editAssign(' . "'" . $this->roleRepo->encodeData($value->id). "'" .')" data-target="#basicvalCodeModal"><span class="badge bg-light-blue"><i class = "fa fa-pencil"></i></span></a></span><span style="padding-left:5px;" ><a onclick="deleteEntity('.$value->id.')"><span class="badge bg-red"><i class="fa fa-trash-o"></i></span></a></span>';
                $finalgetArr[] = $getArr;
            }
   		return json_encode($finalgetArr);
	}
	//"'" . $this->roleRepo->encodeData($zone->id). "'"
	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create($entity_id)
	{
		$entities = Entities::find($entity_id);
		$entity_location = $entities->entity_location;
		$entity_type_id = $entities->entity_type_id;
		$org_id = $entities->org_id;
		$ware_id = $entities->ware_id;

		
		 if($entity_type_id == 6003)
		{
			$parent_id = $entities->parent_entity_id;
			$floor_code = Entities::find($parent_id)->entity_code;
			$parent_id = Entities::find($parent_id)->parent_entity_id;
			$ware_code = Entities::find($parent_id)->entity_code;
			//$entity_location = Entities::find($parent_id)->entity_location;
		}
		else if($entity_type_id ==6004)
		{
			$parent_id = $entities->parent_entity_id;
            $zone_code = Entities::find($parent_id)->entity_code;
            $parent_id  =  Entities::find($parent_id)->parent_entity_id;
            $floor_code = Entities::find($parent_id)->entity_code;
            $parent_id = Entities::find($parent_id)->parent_entity_id;
			$ware_code = Entities::find($parent_id)->entity_code;
			//$entity_location = Entities::find($parent_id)->entity_location;
		}
		else if($entity_type_id ==6005)
		{
			$parent_id = $entities->parent_entity_id;
            $rack_code = Entities::find($parent_id)->entity_code;
            $parent_id  =  Entities::find($parent_id)->parent_entity_id;
            $zone_code = Entities::find($parent_id)->entity_code;
            $parent_id  =  Entities::find($parent_id)->parent_entity_id;
            $floor_code = Entities::find($parent_id)->entity_code;
            $parent_id = Entities::find($parent_id)->parent_entity_id;
            $ware_code = Entities::find($parent_id)->entity_code;
			//$entity_location = Entities::find($parent_id)->entity_location;
		}
		
		$pr= array();
        //$ids = DB::table('products')->select('entity_id')->where('attribute_id','=',73)->where('value','=',$org_id)->get();
        $assigned_products = DB::table('wms_eseal')
        					 ->where('entity_id',$entities->id)
        					 ->lists('product_id');
        					 
        $ids = DB::table('products')
        		->select('product_id')
        		->where('manufacturer_id', $org_id)
        		->whereNotIn('product_id', $assigned_products)
        		->get();
        		
		foreach($ids as $id){
			array_push($pr,$id->product_id); 
		}
		
		$products =['' => 'Select Product']+DB::table('products')->whereIn('product_id',$pr)->lists('name','product_id');

		return View::make('assignlocation.create',compact("entity_id","entity_location","products","org_id","ware_id"));	
	}
	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store()
	{
		//storing the entity details
		DB::table('wms_eseal')->insert([
			'entity_id' => Input::get('entity_id'),
			'product_id' => Input::get('product_id'),
			'org_id' => Input::get('org_id'),
			'ware_id' => Input::get('ware_id'),
			'locator'=> Input::get('locator')
		]);	
        Entities::where('id',Input::get('entity_id'))->update(array('is_assigned' => 1));
        $pname = DB::table('products')->where('product_id', Input::get('product_id'))->pluck('name');
        $entity_type_id =Entities::where('id',Input::get('entity_id'))->pluck('entity_type_id');
        if($entity_type_id ==6005){
             $bin = DB::table('wms_storage_bins')
                    ->where('entity_id',Input::get('entity_id'))
                     ->update(['pid'=>Input::get('product_id'),'pname'=>$pname,'is_allocated'=> 1]);
        }
        return Redirect::to('assignlocation');
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function edit($id)
	{
		$id = $this->roleRepo->decodeData($id);
		$eseal= DB::table('wms_eseal')->where('id',$id)->first();
		$pr= array();
        $ids = DB::table('products')->select('product_id')->where('manufacturer_id', $eseal->org_id)->get();
		foreach($ids as $id){
			array_push($pr,$id->product_id); 
		}
		
		$products =['' => 'Select Product']+DB::table('products')->whereIn('product_id',$pr)->lists('name','product_id');
		return View::make('assignlocation.edit',compact("eseal","products"));
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update($id)
	{
		
		$eseal = Eseal::find($id);
		$eseal->product_id = Input::get('product_id');
        $eseal->locator= Input::get('locator');
        $eseal->save();
        
        return Redirect::to('assignlocation');
	}
	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function delete($id)
	{		
		$eseal = Eseal::find($id);
		$eseal->delete();
		
		return Redirect::to('assignlocation'); 
	}
	
	

}
