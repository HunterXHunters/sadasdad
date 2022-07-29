<?php

use Central\Repositories\CustomerRepo;

class PickListController extends \BaseController 
{
	private $custRepo;
	
    function __construct(CustomerRepo $custRepo) 
    {
    	$this->custRepo = $custRepo;
  	}

    public function index()
    {
    	
      parent::Breadcrumbs(array('Home'=>'/','Locations Report'=>'#'));
        
        $currentUserId = \Session::get('userId');
        \Log::info($currentUserId);
        $manufacturerId = DB::table('users')->where('user_id', $currentUserId)->pluck('customer_id');
        \Log::info($manufacturerId);
        if($manufacturerId)
        {
          $orgs = DB::table('wms_entities')
                  //->select('')
                  ->where(array('wms_entities.org_id' => $manufacturerId, 'wms_entities.entity_type_id' => 6001))
                  ->lists('entity_name','id');
          
        }else{
          $orgs = DB::table('wms_entities')
                  ->where('wms_entities.entity_type_id',0)
                  ->lists('entity_name','org_id');
        }
         
          
        return View::make('picklist.searchreport',compact("orgs","manufacturerId"));    
    }
    public function getWareLocations($manufacturer_id,$ware_id)
    {
        $locationsArr = DB::Table('wms_entities')->leftJoin('locations','locations.location_id','=','wms_entities.location_id')->select('wms_entities.location_id','locations.location_name')->where(array('wms_entities.org_id'=>$manufacturer_id,'id'=>$ware_id))->get();
        /*$locationsArr = DB::Table('locations')->select('locations.location_id','locations.location_name')->where(array('manufacturer_id'=>$manufacturer_id,'location_id'=>$location_id))->get(); 
        $seloptions=array();*/
        $suboptions=array();
        $reqsel ='<select name="location_id" id="location_id" class="form-control requiredDropdown"><option value="">Select Location</option>';
        foreach($locationsArr as $val)
        {
          $reqsel.='<option value="'.$val->location_id.'" >'.$val->location_name.'</option>';
          $suboptions['value'] = $val->location_id;
          $suboptions['option'] = $val->location_name;
          $seloptions[]=$suboptions; 
        }
        $reqsel.='</select>';
        return $reqsel;  
    }
    public function getProducts($manufacturer_id,$location_id)
  	{
  		$productdetails = DB::table('product_locations')
  							->leftJoin('products','products.product_id','=','product_locations.product_id')
  							->select('products.product_id','products.name')
  							->where(array('product_locations.location_id'=>$location_id))->get();
  		$seloptions=array();
  		$suboptions=array();
  		$reqsel ='<select name="product_id" id="product_id" class="form-control requiredDropdown"><option value="">Select Product</option>';
  		foreach($productdetails as $val)
  		{
  			$reqsel.='<option value="'.$val->product_id.'" >'.$val->name.'</option>';
  			$suboptions['value'] = $val->product_id;
  			$suboptions['option'] = $val->name;
  			$seloptions[]=$suboptions; 
  		}
  		$reqsel.='</select>';
  		return $reqsel;
  	}

	public function getAttributes($manufacturer_id,$location_id,$product_id)
	{
		$attribute_set_id = DB::Table('product_attributesets')->where(array('product_id'=>$product_id,'location_id'=>$location_id))->pluck('attribute_set_id');
		
        $attributesArr = DB::table('attributes as attr')
             ->join('attribute_set_mapping as asm','asm.attribute_id','=','attr.attribute_id')
             ->where(['asm.attribute_set_id'=>$attribute_set_id,'is_searchable'=>1])
             ->orderBy('asm.sort_order','asc')
             ->get(['attr.attribute_id','attr.name','attr.attribute_code','attr.input_type','attr.default_value','attr.is_required','attr.validation']);
		//$querires= DB::getQueryLog();
        //return end($querires);
		$seloptions=array();
		$suboptions=array();
		$reqsel ='<select name="attribute_id" id="attribute_id" class="form-control requiredDropdown"><option value="">Select Attribute</option>';
		foreach($attributesArr as $val)
		{
			$reqsel.='<option value="'.$val->attribute_id.'" >'.$val->name.'</option>';
			$suboptions['value'] = $val->attribute_id;
			$suboptions['option'] = $val->name;
			$seloptions[]=$suboptions; 
		}
		$reqsel.='</select>';
		return $reqsel;
	}
    
    public function getReportData($manufacturer_id,$location_id,$product_id,$attribute_id,$searchValues)
    {      
        //return $searchValues;
        if(!empty($searchValues)){
          //return 'Here';
          $searchValues = parse_str($searchValues, $searchAttrVal);
          $attrCount = count($searchAttrVal['filter']);          
          for($i=0;$i<$attrCount;$i++)
          {                   
              $filterArr = json_decode($searchAttrVal['filter'][$i],true);            
              foreach($filterArr as $key1=>$val1)
              {                
                $filterArray[$key1] = $val1;                 
              } 
              $filter[] = $filterArray;
          }  
        }else{
          //return 'where';
          $filter=array();
        }
        //return $filter;   
        //echo '<pre>';print_r($filter); exit;            
        $tbl = 'eseal_'.$manufacturer_id;
        $resqry = DB::Table($tbl)
              ->leftJoin('bind_history','bind_history.eseal_id','=',$tbl.'.primary_id')
              ->leftJoin('attribute_mapping','attribute_mapping.attribute_map_id','=','bind_history.attribute_map_id')
              ->leftJoin('products','products.product_id','=',$tbl.'.pid')
              ->leftJoin('attribute_set_mapping', function($join)
              {
                  $join->on('products.attribute_set_id', '=', 'attribute_set_mapping.attribute_set_id')
                  ->on('attribute_set_mapping.attribute_id', '=', 'attribute_mapping.attribute_id');
              })
              ->select($tbl.'.eseal_id',$tbl.'.primary_id',$tbl.'.parent_id','attribute_mapping.attribute_name','attribute_mapping.value',$tbl.'.bin_location','products.name','attribute_set_mapping.is_searchable','products.attribute_set_id')
              //->where('attribute_set_mapping.attribute_id','=','attribute_mapping.attribute_id')
              ->where($tbl.'.parent_id','!=','unknown')
              ->where($tbl.'.bin_location','!=','NULL')
              ->where('attribute_set_mapping.is_searchable',1);
              
              if(!empty($product_id))
              {
                $resqry = $resqry->where(array($tbl.'.pid'=>$product_id));               
              }
              $resData = array();
              $res='';
             
              $filterCount = count($filter);
              //return $filterCount;
              //return $filter;
              //if(!empty($filter) && isset($filter) && $filter != [[]]){
              if(!empty($filter) && $filterCount > 0){
                foreach($filter as $key=>$val)
                {
                  $welqry ='';
                  $welqry = clone $resqry;
                  $welqry = $welqry->where('attribute_mapping.attribute_id',$val['attribute_id']);
                  $welqry = $welqry->where('attribute_mapping.value',$val['report_operator_text'],$val['values']);
                  $welqry= $welqry->get();
                   //$querires = DB::getQueryLog();
                   //return $querires;
                   $resData = array_merge($welqry,$resData);
                }
              }else{
                  $welqry ='';
                  $welqry = $resqry;
                  $resData= $welqry->get();               
              }
              $finalRes = array();
              $finalData = array();
              //return $key;
              foreach($resData as $key=>$value)
              { 
                   //echo "<pre/>";print_r($value->eseal_id);exit;
                  $finalRes['id'] = $value->eseal_id;
                  $finalRes['product'] = $value->primary_id;
                  $finalRes['product_name'] = $value->name;
                  $finalRes['attribute'] = $value->attribute_name;
                  $finalRes['value'] = $value->value;
                  $finalRes['parent'] = $value->parent_id;
                  $finalRes['bin'] = $value->bin_location;
                  $finalRes['product'] = $value->primary_id;
                  $finalRes['is_searchable'] = $value->is_searchable;
                  $finalRes['attribute_set_id'] = $value->attribute_set_id;
                  $finalRes['actions'] = '';
                  $finalData[]=$finalRes;
              }
      return json_encode($finalData);exit;
    }

    public function getLocations()
    {
        parent::Breadcrumbs(array('Home'=>'/','Locations Report'=>'#'));
        
        $currentUserId = \Session::get('userId');
        \Log::info($currentUserId);
        $manufacturerId = DB::table('users')->where('user_id', $currentUserId)->pluck('customer_id');
        \Log::info($manufacturerId);
        if($manufacturerId)
        {
          $orgs = DB::table('wms_entities')
                  //->select('')
                  ->where(array('wms_entities.org_id' => $manufacturerId, 'wms_entities.entity_type_id' => 6001))
                  ->lists('entity_name','id');
          
        }else{
          $orgs = DB::table('wms_entities')
                  ->where('wms_entities.entity_type_id',0)
                  ->lists('entity_name','org_id');
        }
          
        return View::make('picklist.bin_locations',compact("orgs","manufacturerId"));
    }

    public function getFilterWarehouse($ware_id)
    {
        $getWares = DB::table('wms_entities')
                      ->where(array('org_id'=>$ware_id, 'entity_type_id'=>6001))
                      ->lists('entity_name','id');                      

        $seloptions=array();
        $suboptions=array();
        $reqsel ='<select name="warehouse_id" id="warehouse_id" class="form-control"><option value="">Select Warehouse</option>';
        foreach($getWares as $key=>$val)
        {
          $reqsel.='<option value="'.$key.'" >'.$val.'</option>';
          $suboptions['value'] = $key;
          $suboptions['option'] = $val;
          $seloptions[]=$suboptions; 
        }
        $reqsel.='</select>';
        return $reqsel;
    }

    public function getFilterBins($ware_id)
    {
        $getBins = DB::table('wms_storage_bins')
                                  ->where('ware_id',$ware_id)
                                  ->lists('wms_storage_bins.storage_bin_id');

        $seloptions=array();
        $suboptions=array();
        $reqsel ='<select name="storage_bin_id" id="storage_bin_id" class="form-control"><option value="">Select Storage-Bin</option>';
        foreach($getBins as $key=>$val)
        {
          $reqsel.='<option value="'.$val.'" >'.$val.'</option>';
          $suboptions['value'] = $key;
          $suboptions['option'] = $val;
          $seloptions[]=$suboptions; 
        }
        $reqsel.='</select>';
        return $reqsel;
    }

    public function getFilterPallets($bin_id,$manuf_id)
    {
        $tbl = 'eseal_'.$manuf_id;
        $getPallets = DB::table($tbl)
                      ->where(array('bin_location'=>$bin_id, 'level_id'=>8))
                      ->lists('primary_id');                      

        $seloptions=array();
        $suboptions=array();
        $reqsel ='<select name="pallet_id" id="pallet_id" class="select1"><option value="">Select Pallet</option>';
        foreach($getPallets as $key=>$val)
        {
          $reqsel.='<option value="'.$val.'" >'.$val.'</option>';
          $suboptions['value'] = $key;
          $suboptions['option'] = $val;
          $seloptions[]=$suboptions; 
        }
        $reqsel.='</select>';
        return $reqsel;
        //return json_encode($seloptions);
    }

    public function getFilterProducts($pallet_id,$manuf)
    {
        $tbl = 'eseal_'.$manuf;
        $getProducts = DB::table($tbl)
                        ->where(array('parent_id'=>$pallet_id, 'level_id'=>0))
                        ->lists('primary_id');
       /* $querires= DB::getQueryLog();
        return end($querires);*/
        $seloptions=array();
        $suboptions=array();
        $reqsel ='<select name="pallet_id" id="pallet_id" class="select1"><option value="">Select Pallet</option>';
        foreach($getProducts as $key=>$val)
        {
          $reqsel.='<option value="'.$key.'" >'.$val.'</option>';
          $suboptions['value'] = $key;
          $suboptions['option'] = $val;
          $seloptions[]=$suboptions; 
        }
        $reqsel.='</select>';
        return $reqsel;
    }
    
    public function getBinLocationsData($manuf,$ware,$bin,$pallet,$product)
	  {
      
      $binLocations = DB::table('wms_storage_bins')
                      ->select('wms_storage_bins.entity_id','wms_storage_bins.storage_bin_id');
      
      if(!empty($bin))
          $binLocations = $binLocations->where(array('storage_bin_id'=>$bin));
      if(!empty($manuf))
          $binLocations = $binLocations->where(array('org_id'=>$manuf));
      if(!empty($ware))
          $binLocations = $binLocations->where(array('ware_id'=>$ware));
                      
      $binLocations = $binLocations->get();

      $finalLcArrs = array();
    	$lcs = array();
      $tbl = 'eseal_'.$manuf;
      $master_bin = json_decode(json_encode($binLocations), true);
    	foreach($master_bin as $valus)
    	{
    			$getPallets = DB::table($tbl)
                        ->select('level_id', 'primary_id', 'pkg_qty');
          if(!empty($pallet))
              $getPallets = $getPallets->where(array('bin_location'=>$valus['storage_bin_id'], 'primary_id'=>$pallet, 'level_id'=>8))->get();
          else
              $getPallets = $getPallets->where(array('bin_location'=>$valus['storage_bin_id'], 'level_id'=>8))->get();
          
          $finalMlArr = array();
          $ml = array();
          $master_pallet = json_decode(json_encode($getPallets), true);
          foreach($master_pallet as $valu)
          {         
              $getProducts = DB::table($tbl)
                              ->select('primary_id');
              
              if(!empty($product))
                $getProducts = $getProducts->where(array('parent_id'=>$valu['primary_id'], 'primary_id'=>$product))->get();
              else
                $getProducts = $getProducts->where(array('parent_id'=>$valu['primary_id']))->get();

              $finalProdArr = array();
              $prod = array();
              $master_product = json_decode(json_encode($getProducts), true);
              foreach($master_product as $prod_val)
              {
                  $prod['primary_id'] = $prod_val['primary_id'];
                  //$prod['actions'] =  '';
                  $finalProdArr[] = $prod_val;
              }
            $ml['parent_id'] = $valu['primary_id'];
            $ml['pkg_qty'] = $valu['pkg_qty'];
            //$ml['actions'] =  '';
            $ml['children']=$finalProdArr;
            $finalMlArr[] = $ml;
          }
      
          $lcs['storage_bin_id'] = $valus['storage_bin_id'];
          $lcs['entity_id'] = $valus['entity_id'];
          //$lcs['actions'] = '';
          $lcs['children']=$finalMlArr;
          $finalLcArrs[] = $lcs;
      }
       return json_encode($finalLcArrs);
    }

}