<?php

class PackageController extends \BaseController 
{
	public function packageindex()
    {
    	$pack=DB::table('wms_packages')->get();
    	return View::make('entities.packageindex',compact("pack"));

    }


    public function getpackagedata()
	{
		//$mapEntities = DB::table('eseal')->get();

		
		$entity_types = Package::all();
        $getArr = array();
        $finalgetArr = array();
        foreach($entity_types as $value)
        {
        	
        	$getArr['id'] = $value->id;
        	$getArr['package_name'] = $value->package_name;
        	//$getArr['status'] = $status;
        	$getArr['package_type_id'] = $value->package_type_id;
        	//$getArr['rack_capacity'] = $value->rack_capacity;
        	
        	$product_name = DB::table('catalog_product_entity_text')->where(array('attribute_id'=>64,'entity_id'=> $value->pname))->first();
        	$getArr['pname'] = $product_name->value;
        	$getArr['edit'] = '<a href="/wms/package/packageedit/'.$value->id.'"><img src="/wms/img/edit.png" /></a>'; 
			$getArr['delete'] = '<a onclick="deletePackage('.$value->id.')" href="#"><img src="/wms/img/delete.png" /></a>';
			$finalgetArr[] = $getArr;
        }
       // return 'nikhil kishore';
		return json_encode($finalgetArr);
	}
    
    public function packagecreate()
	{
		$weight_uom_id= UomGroup::where('description','capacity')->first()->id;
		$weight_uom = DB::table('wms_uom')->where('uom_group_id',$weight_uom_id)->lists('description', 'id');
        $weight_uom = ['' => 'Select Capacity UOM'] + $weight_uom;
        $dimension_uom_id= UomGroup::where('description','dimension')->first()->id;
		$dimension_uom = DB::table('wms_uom')->where('uom_group_id',$dimension_uom_id)->lists('description', 'id');
		$dimension_uom = ['' => 'Select Dimension UOM'] + $dimension_uom;
		
		//$dimension_uom=DB::table('uom')->where('uom_group_id',1)->lists('description', 'id');
		$pr= array();
        $ids = DB::table('catalog_product_entity_int')->select('entity_id')->where('attribute_id','=',73)->where('value','=',64)->get();
		foreach($ids as $id){
			array_push($pr,$id->entity_id); 
		}
		
		$products =['' => 'Select Product']+DB::table('catalog_product_entity_text')->where('entity_type_id','=',4)
		->where('attribute_id','=',64)->whereIn('entity_id',$pr)->lists('value','entity_id');
		
		return View::make('entities.packagecreate',compact("weight_uom","dimension_uom","products"));
		//return View::make('entities.create', compact("capacity_uom","dimension_uom","locations","entity_type_id"));	
		
	}

	 public function packagestore()
    {
    	
    	DB::table('wms_packages')->insert([

			'package_name' => Input::get('package_name'),
			'pname' => Input::get('pname'),
			'dimension_id' => Input::get('dimension_id'),
			'weight' => Input::get('weight'),
			'weight_uom_id' => Input::get('weight_uom_id'),
			'package_type_id' => Input::get('package_type_id'),
			'package_length' =>	 Input::get('package_length'),
			'package_width' =>	 Input::get('package_width'),
			'package_height' =>	 Input::get('package_height'),		
			'package_dimension_id' => Input::get('package_dimension_id'),
            
			]);
     return Redirect::to('package/packageindex'); 	
    
    }
   
    public function packageedit($entity_id)
    {
    	//return $entity_id;
    	$packages = Package::find($entity_id);
		//$entities->entity_code = $entity_code;
		// return $packages;
		 $wt=$packages->weight;
         
		 $package_type_id = $packages->package_type_id;
		
		$weight_uom_id= UomGroup::where('description','capacity')->first()->id;
		$weight_uom = DB::table('wms_uom')->where('uom_group_id',$weight_uom_id)->lists('description', 'id');
        $weight_uom = ['' => 'Select Capacity UOM'] + $weight_uom;
        $dimension_uom_id= UomGroup::where('description','dimension')->first()->id;
		$dimension_uom = DB::table('wms_uom')->where('uom_group_id',$dimension_uom_id)->lists('description', 'id');
		$dimension_uom = ['' => 'Select Dimension UOM'] + $dimension_uom;
		//$dimensions=DB::table('dimensions')->get();

		$pr= array();
        $ids = DB::table('catalog_product_entity_int')->select('entity_id')->where('attribute_id','=',73)->where('value','=',64)->get();
		foreach($ids as $id){
			array_push($pr,$id->entity_id); 
		}
		
		$products =['' => 'Select Product']+DB::table('catalog_product_entity_text')->where('entity_type_id','=',4)
		->where('attribute_id','=',64)->whereIn('entity_id',$pr)->lists('value','entity_id');
		//$locations = DB::connection('mysql2')->table('track_and_trace_location')->lists('location_name', 'location_id');
		// show the edit form and pass the nerd

      // return 'edit';
     return View::make('entities.packageedit',compact("weight_uom","dimension_uom","products","packages","package_type_id","wt"));
        	
    }

     public function packageupdate($entity_id)
     {
     	$packages = Package::find($entity_id);
       
        $packages->package_name = Input::get('package_name');
        $packages->pname = Input::get('pname');
        $packages->dimension_id = Input::get('dimension_id');
        $packages->weight = Input::get('weight');
        $packages->weight_uom_id = Input::get('weight_uom_id');
        $packages->package_type_id = Input::get('package_type_id');
        $packages->package_length = Input::get('package_length');
		$packages->package_width = Input::get('package_width');	
		$packages->package_height = Input::get('package_height');
		$packages->package_dimension_id = Input::get('package_dimension_id');
		$packages->save();

     return Redirect::to('package/packageindex');
     // return 'edit';
     }
         public function packagedelete($entity_id){

    	$packages = Package::where('id',$entity_id)->delete();
   //		$dimensions = Dimension::where('entity_id',$entity_id)->delete();
		
		return Redirect::to('package/packageindex'); 
    }

  
}