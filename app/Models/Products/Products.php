<?php
namespace App\Models\Products;
use App\Repositories\RoleRepo;
use App\Repositories\CustomerRepo;
use DB;
use Session;

use Illuminate\Database\Eloquent\Model;
class Products extends Model {
//class Products extends \Eloquent {

    protected $table = 'products'; // table name
    protected $primaryKey = 'product_id';
    public $timestamps = false;
    private $customerRepo;
    private $roleRepo;
    
    public function __construct()
    {
        $this->customerRepo = new CustomerRepo;
        $this->roleRepo = new RoleRepo;
    }
    // model function to store product data to database

    public function getManufacturerIdForProductId($pid){
        if(!empty($pid) && is_numeric($pid)){
            $mfgId = DB::table($this->table)
                        ->where('product_id', $pid)
                        ->value('manufacturer_id');
            return $mfgId;
        }else{
            return false;
        }
    }

    public function getNameFromId($pid){
        if(!empty($pid) && is_numeric($pid)){
            $pname = DB::table($this->table)
                        ->where('product_id', $pid)
                        ->value('name');
            return $pname;
        }else{
            return false;
        }
    }

    public function getMatCodeFromId($pid){
        if(!empty($pid) && is_numeric($pid)){
            $pmatcode = DB::table($this->table)
                        ->where('product_id', $pid)
                        ->value('material_code');
            return $pmatcode;
        }else{
            return false;
        }
    }

    public function getProductInfo($pid){
        if(!empty($pid) && is_numeric($pid)){
            $pname = DB::table($this->table)
                        ->where('product_id', $pid)
                        ->get(['name','group_id']);
            return $pname;
        }else{
            return false;
        }
    }

    public function getProductSearchAttributes($pid,$primary_id,$location_id){
          if(!empty($pid) && is_numeric($pid)){
            $searchAttributes = DB::table($this->table.' as pr')
                                ->join('product_attributesets as pas','pas.product_group_id','=','pr.group_id')
                                ->join('attribute_set_mapping as asm','asm.attribute_set_id','=','pas.attribute_set_id')
                                ->join('attributes','attributes.attribute_id','=','asm.attribute_id')
                                ->where(['pr.product_id'=>$pid,'asm.is_searchable'=>1,'pas.location_id'=>$location_id])
                                ->pluck('attribute_code')->toArray();

             $attributes = DB::table($this->table.' as pr')
                                ->join('product_attributesets as pas','pas.product_group_id','=','pr.group_id')
                                ->join('attribute_set_mapping as asm','asm.attribute_set_id','=','pas.attribute_set_id')
                                ->join('attributes','attributes.attribute_id','=','asm.attribute_id')
                                ->where(['pr.product_id'=>$pid,'asm.is_searchable'=>0,'pas.location_id'=>$location_id])
                                ->pluck('attribute_code')->toArray();                   

            
            $searchValues = DB::table('bind_history as bh')
                           ->join('attribute_mapping as am','am.attribute_map_id','=','bh.attribute_map_id')    
                           ->where(['bh.eseal_id'=>$primary_id])
                           ->whereIn('am.attribute_name',$searchAttributes)
                           ->get(['am.attribute_name as name','am.value'])->toArray();


            $Values = DB::table('bind_history as bh')
                           ->join('attribute_mapping as am','am.attribute_map_id','=','bh.attribute_map_id')    
                           ->where(['bh.eseal_id'=>$primary_id])
                           ->whereIn('am.attribute_name',$attributes)
                           ->get(['am.attribute_name as name','am.value'])->toArray();
             
            return ['search_attributes'=>$searchValues,'attributes'=>$Values];

        }else{
            return false;
        }       
    }


    public function getProductFields($manufacturerId, $productId = null)
    {
        $productData = new \stdClass();
        $attributeData = new \stdClass();
        $packageData = new \stdClass();
        $mediaData = new \stdClass();
        $productAttributesetsData = new \stdClass();
        $brandName = '';
        if($productId)
        {
            $productData = $this->getProductData($productId);
            //print_r($productData);exit;
            $manufacturerId = $productData->manufacturer_id;
            $attributeData = (property_exists($productData, 'attribute_data') ? $productData->attribute_data : '');
            $packageData = (property_exists($productData, 'package_data') ? $productData->package_data : '');
            $mediaData = (property_exists($productData, 'media_data') ? $productData->media_data : '');
            $productAttributesetsData = (property_exists($productData, 'product_attributesets') ? $productData->product_attributesets : '');
            //echo "<pre>";print_r($productData);die;
            //$brandName = $this->getBrandName($productId);
        }
        //$this->getCategories();
        $data = array();
        $data['title'] = 'Create Product';
        $manufacturer['field'] = 'product[manufacturer_id]';
        $manufacturer['title'] = 'Manufacturer Name';
        $manufacturers = $this->getManufacturers($manufacturerId);
        $manufacturer['options'] = $manufacturers;        
        //$categories = array(1 => 'Home & Kitchen', 2 => 'Bestsellers', 3 => 'Kitchen & Home Appliances', 4 => 'Large Appliances', 5 => 'Kitchen & Dining', 6 => 'Home & Decor', 7 => 'Home Furnishing', 8 => 'Indoor Lighting', 9 => 'Home Improvement', 10 => 'Lawn & Garden');
        //$sub_category[1] = array(1 => 'Artwork', 2 => 'Home & Décor', 3 => 'Home Furnishing', 4 => 'Home Improvement', 5 => 'Home Storage & Organization', 6 => 'Indoor Lighting', 7 => 'Kitchen & Dining', 8 => 'Kitchen & Home Appliances', 9 => 'Large Appliances', 10 => 'Lawn & Garden');
        $businessUnitsResult = \DB::table('business_units')->where('manufacturer_id', $manufacturerId)->get(['business_unit_id', 'name'])->toArray(); 
        //print_r($businessUnitsResult);exit;
        $businessUnitsData = array();
        
        foreach ($businessUnitsResult as $businessSet) {            
            $businessUnitsData[$businessSet->business_unit_id] = $businessSet->name;
        }
        
        $groupIdResult = \DB::table('product_groups')->where('manufacture_id', $manufacturerId)->get(['group_id', 'name'])->toArray(); 
        $groupIdData = array();
        $groupIdData[0] = 'Please select..';
        foreach ($groupIdResult as $groupSet) {
            $groupIdData[$groupSet->group_id] = $groupSet->name;
        }
        
        $attributes_set_data = \DB::table('attribute_sets')->where('manufacturer_id', $manufacturerId)->get(['attribute_set_id', 'attribute_set_name'])->toArray();
        if(empty($attributes_set_data))
        {
            $attributes_set_data = \DB::table('attribute_sets')->where('manufacturer_id', 0)->get(['attribute_set_id', 'attribute_set_name'])->toArray();
        }
        $attributes_sets = array();
        
        foreach ($attributes_set_data as $attributesSet) {
            $attributes_sets[$attributesSet->attribute_set_id] = $attributesSet->attribute_set_name;
        }
        
        /*$attributes_fields = DB::table('attribute_mapping')
                ->join('attributes', 'attributes.attribute_id', '=', 'attribute_mapping.attribute_id')
                ->select('attribute_mapping.attribute_map_id', 'attribute_mapping.attribute_id', 'attributes.attribute_code', 'attributes.name', 'attributes.input_type')
                ->get();*/
 
        $weightClassData = $this->getWeightClass();
        $lenghtClassData = $this->getLookupData('Length UOM');
        $levelData = $this->getLookupData('Levels');
        $taxData = $this->getLookupData('Tax Classes');
        $productTypeData = $this->getLookupData('Product Types');
        //print_r($productTypeData);exit;
        $creditPeriodData = $this->getLookupData('Credit Period');
        $categories = $this->getCategories();

        
        $data['general']['manufacturer_data'] = $manufacturer;
        $data['general']['product_category'] = $categories;
        $data['general']['business_unit_id'] = $businessUnitsData;
        $data['general']['product_class'] = '';
        $data['general']['product_type'] = $productTypeData;
        $data['general']['product_title'] = '';
        $data['general']['product_description'] = '';
        $data['general']['product_model_name'] = '';
        $data['general']['product_brand_name'] = '';
        $data['general']['meta_tag_title'] = '';
        $data['general']['meta_tag_description'] = '';
        $data['general']['product_tags'] = '';
        $data['attributes']['sets'] = $attributes_sets;
        $data['dimensions']['length_classes'] = $lenghtClassData;
        $data['packages']['weight_classes'] = $weightClassData;
        $data['packages']['levels'] = $levelData;
        $data['tax'] = $taxData;
        $data['credit_period'] = $creditPeriodData;

        $data['locations'] = ['name' => 'locations', 'options' => $this->getLocationsData($manufacturerId) ];
        $UOMS = $this->getUomData($manufacturerId);
        $data['UOM']= ['name'=>'UOM','options'=> $UOMS];

        $data['main_manufacturer_id'] = ['name' => 'product[manufacturer_id]', 'options' => $manufacturers, 'value' => (property_exists($productData, 'manufacturer_id') ? $productData->manufacturer_id : '') ];
        $data['product_category'] = ['name' => 'product[category_id][]', 'options' => $categories, 'value' => (property_exists($productData, 'category_id') ? explode(',', $productData->category_id) : '') ];
        $data['business_unit_id'] = ['name' => 'product[business_unit_id]', 'options' => $businessUnitsData, 'value' => (property_exists($productData, 'business_unit_id') ? $productData->business_unit_id : '') ];
        $data['group_id'] = ['name' => 'product[group_id]', 'options' => $groupIdData, 'value' => (property_exists($productData, 'group_id') ? $productData->group_id : '') ];
        $data['product_type_id'] = ['name' => 'product[product_type_id]', 'options' => $productTypeData, 'value' => (property_exists($productData, 'product_type_id') ? $productData->product_type_id : '') ];
        $data['product_name'] = ['name' => 'product[name]', 'value' => (property_exists($productData, 'name') ? $productData->name : '') ];
        $data['product_title'] = ['name' => 'product[title]', 'value' => (property_exists($productData, 'title') ? $productData->title : '') ];
        $data['product_description'] = ['name' => 'product[description]', 'value' => (property_exists($productData, 'description') ? $productData->description : '') ];
        $data['brand_name'] = ['name' => 'attributes[product_brand_name]', 'value' => (property_exists($productData, 'product_brand_name') ? $productData->product_brand_name : '') ];
        $data['height'] = ['name' => 'product[height]', 'value' => (property_exists($productData, 'height') ? $productData->height : '') ];
        $data['width'] = ['name' => 'product[width]', 'value' => (property_exists($productData, 'width') ? $productData->width : '') ];
        $data['primary_capacity'] = ['name' => 'package[quantity]'];
        $data['primary_product_weight'] = ['name' => 'package[weight]'];
        $data['is_traceable'] = ['name' => 'product[is_traceable]', 'value' => (property_exists($productData, 'is_traceable') ? $productData->is_traceable : '')];
        $data['is_gds_enabled'] = ['name' => 'product[is_gds_enabled]', 'value' => (property_exists($productData, 'is_gds_enabled') ? $productData->is_gds_enabled : '')];
        $data['is_serializable'] = ['name' => 'product[is_serializable]', 'value' => (property_exists($productData, 'is_serializable') ? $productData->is_serializable : '')];
        $data['is_batch_enabled'] = ['name' => 'product[is_batch_enabled]', 'value' => (property_exists($productData, 'is_batch_enabled') ? $productData->is_batch_enabled : '')];
        $data['inspection_enabled'] = ['name' => 'product[inspection_enabled]', 'value' => (property_exists($productData, 'inspection_enabled') ? $productData->inspection_enabled : '')];
        $data['is_backflush'] = ['name' => 'product[is_backflush]', 'value' => (property_exists($productData, 'is_backflush') ? $productData->is_backflush : '')];
        $data['material_code'] = ['name' => 'product[material_code]', 'value' => (property_exists($productData, 'material_code') ? $productData->material_code : '')];
        $data['main_attribute_set_id'] = ['name' => 'product[attribute_set_id]', 'options' => $attributes_sets, 'value' => (property_exists($productData, 'attribute_set_id') ? $productData->attribute_set_id : '') ];
        $data['attribute_sets_id'] = ['name' => 'attribute_sets_id', 'options' => $attributes_sets ];
        //$data['length_classes'] = $lenghtClassData;
        $data['weight_class_id'] = ['name' => 'product[weight_class_id]', 'options' => $weightClassData, 'value' => (property_exists($productData, 'weight_class_id') ? $productData->weight_class_id : '')];
        $data['package_weight_class_id'] = ['name' => 'package[weight_class_id]', 'options' => $weightClassData];
        $data['package_level'] = ['name' => 'package[level]', 'options' => $levelData];
        $data['mrp'] = ['name' => 'product[mrp]', 'value' => (property_exists($productData, 'mrp') ? $productData->mrp : '') ];
        $data['pallet_stack_height'] = ['name' => 'package[stack_height]', 'value' => (property_exists($productData, 'stack_height') ? $productData->stack_height : '') ];
        $data['package_data'] = ['value' => (property_exists($productData, 'package_data') ? (array) $productData->package_data : array())];
	$data['product_locations'] = ['name' => 'location[location_id][]', 'options' => $this->getLocationsData($manufacturerId), 'value' => 
(property_exists($productData, 'location_id') ? $productData->location_id : 0) ];
        $data['product_UOM']= ['name'=>'UOM','options'=>$UOMS,'value'=>(property_exists($productData, 'uom_class_id') ? $productData->uom_class_id : 0)];
        $data['pallet_data'] = ['value' => (property_exists($productData, 'pallet_data') ? (array) $productData->pallet_data : array())];
        $data['media_data'] = ['value' => (property_exists($productData, 'media_data') ? (array) $productData->media_data : array())];
        $data['product_attributesets'] = ['value' => (property_exists($productData, 'product_attributesets') ? (array) $productData->product_attributesets : array())];
        $data['model_name'] = ['name' => 'product[model_name]', 'value' => (property_exists($productData, 'model_name') ? $productData->model_name : '')];
        $data['error_message'] = '';
        \Log::info('we arewc1234 in '.__METHOD__);
        $data['tax_class_id'] = ['name' => 'product[tax_class_id]', 'options' => $taxData, 'value' => (property_exists($productData, 'tax_class_id') ? $productData->tax_class_id : 0) ];
        $data['location_country_id'] = ['name' => 'location_country_id', 'options' => $this->customerRepo->getCountryData() ];
        $data['country_input_id'] = ['name' => 'country', 'value' => 99 ];
        $data['location_state_options'] = ['name' => 'state', 'options' => $this->customerRepo->getZones(99)];
        \Log::info('we arewc12345-1 in '.__METHOD__);
//DB::enableQueryLog();
//        $productTextDesc = DB::table('prod_text_det')->where('product_id', $productId)->first(['warranty_policy', 'return_policy', 'prod_desc']);
//$temp = DB::table('prod_text_det')->get();
//\Log::info($temp);
//$last = DB::getQueryLog();
//\Log::info(end($last));
$productTextDesc = array();
        if(!empty($productTextDesc))
        {
\Log::info('we arewc12345-3 in '.__METHOD__);
            $data['warranty_policy'] = ['name' => 'prod_text_det[warranty_policy]', 'value' => (property_exists($productTextDesc, 'warranty_policy') ? $productTextDesc->warranty_policy : '') ];
            $data['return_policy'] = ['name' => 'prod_text_det[return_policy]', 'value' => (property_exists($productTextDesc, 'return_policy') ? $productTextDesc->return_policy : '') ];
            $data['prod_desc'] = ['name' => 'prod_text_det[prod_desc]', 'value' => (property_exists($productTextDesc, 'prod_desc') ? $productTextDesc->prod_desc : '') ];            
        }else{
\Log::info('we arewc12345-4 in '.__METHOD__);
            $data['warranty_policy'] = ['name' => 'prod_text_det[warranty_policy]'];
            $data['return_policy'] = ['name' => 'prod_text_det[return_policy]'];
            $data['prod_desc'] = ['name' => 'prod_text_det[prod_desc]'];            
        }        
\Log::info('we arewc123456 in '.__METHOD__);
        $data['product_tag'] = ['name' => 'product[meta_tags]', 'value' => (property_exists($productData, 'meta_tags') ? $productData->meta_tags : '') ];
        $customerstoragelocations = DB::table('master_lookup')->join('lookup_categories','lookup_categories.id','=','master_lookup.category_id')->select('master_lookup.name', 'master_lookup.value')->where('lookup_categories.name','=','Storage Location Types')->get()->toArray();
        $storageLocationData = array();
        foreach ($customerstoragelocations as $storageLocation) {            
            $storageLocationData[$storageLocation->value] = $storageLocation->name;
        }
\Log::info('we arewc1234567 in '.__METHOD__);
        $data['storage_location_type_code'] = ['name' => 'storage_location_type_code', 'options' => $storageLocationData];
        $data['allow_add_business_unit'] = $this->roleRepo->checkPermissionByFeatureCode('BUS002');
        $data['allow_add_location'] = $this->roleRepo->checkPermissionByFeatureCode('LOC002');
        
        \Log::info('we arewc12345678 in '.__METHOD__);
        /*foreach($attributes_fields as $field)
        {            
            if('select' == $field->input_type)
            {
                $options = \DB::table('attribute_options')->where('attribute_id', $field->attribute_id)->get(array('option_id', 'option_value', 'language_id'));
                $optionData = array();

                foreach ($options as $option) {
                    $optionData[$option->option_id] = $option->option_value;
                }
                $field->options = $optionData;
            }
            $data['attributes']['fields'][$field->attribute_code] = $field;
        }*/
        //$data['attributes']['fields'] = $attributes_fields;
        //echo "<pre/>";
        //print_r($data);exit;
        return $data;
    }
    
//    public function getBrandName($productId)
//    {
//        try
//        {
//            $attributeId = DB::table('attributes')->where('attribute_code', 'product_brand_name')->plick('attribute_id');
//            return DB::table('product_attributes')->where(['product_id' => $productId, 'attribute_id' => $attributeId])->value('value');
//        } catch (\ErrorException $ex)
//        {
//            return '';
//        }
//    }
    
    public function getLocations($manufacturerId)
    {
        try
        {
            $result = DB::table('locations')->where('manufacturer_id', $manufacturerId)->where('location_type_id','!=',874)->get(array('location_id', 
'location_name'));
            $resultArray = array();
            if(!empty($result))
            {
                foreach($result as $key => $value)
                {
                    $resultArray[$key] = $value;
                }
            }
            return $resultArray;
        } catch (\ErrorException $ex) {

        }
    }
    
    public function getUomData($manufacturer_id)
    {
        // $results = DB::table('master_lookup')->select('name','value as ml_value')->where(array('category_id'=>13))->get();
        
        // $uom_array = array();
        // if(!empty($results)) {
        //     foreach ($results as  $value) {
        //         $uom_array[$value->ml_value] = $value->name;
        //     }
        // }
        // return $uom_array;
         $results = DB::table('uom_classes')->where('manufacturer_id',$manufacturer_id)->pluck('uom_name','id')->toArray();
        
        $uom_array = array();
        if(!empty($results)) {
            foreach ($results as  $id=>$value) {
                $uom_array[$id] = $value;
            }
        }
        return $uom_array;
    }
    public function getLocationsData($manufacturerId)
    {
        try
        {
            /*$result = DB::table('locations')->where(array('manufacturer_id' => $manufacturerId, 'is_deleted' => 0))
                                            ->where('location_type_id','!=',874)
                                            ->get(array('location_id', 'location_name'));
*/
            $result = DB::table('locations')
                            ->select('locations.location_id', 'locations.location_name')
                            ->join('location_types','locations.location_type_id','=','location_types.location_type_id')                            
                            ->where(array('locations.manufacturer_id' => $dataValue, 'locations.is_deleted' => 0))
                            ->whereIn('location_types.location_type_name',array('Plant','Warehouse','Depot','supplier'))
                            ->get();            
            $resultArray = array();
            if(!empty($result))
            {
                foreach($result as $location)
                {
                    $resultArray[$location->location_id] = $location->location_name;
                }
            }
            return $resultArray;
        } catch (\ErrorException $ex) {

        }
    }
    
    public function saveProduct($data) {
        try {
            if(isset($data['product']))
            {
                $productData = $data['product'];                
                foreach($productData as $key => $value){
                    if('category_id' == $key)
                    {
                        $value = implode($value, ',');
                    }
                    if('is_traceable' == $key && $value == 'on')
                    {
                        $value = 1;
                    }
                    if('is_gds_enabled' == $key && $value == 'on')
                    {
                        $value = 1;
                    }
                    if('is_serializable' == $key && $value == 'on')
                    {
                        $value = 1;
                    }
                    if('is_batch_enabled' == $key && $value == 'on')
                    {
                        $value = 1;
                    }
                    if('inspection_enabled' == $key && $value == 'on')
                    {
                        $value = 1;
                    }
                    if('is_backflush' == $key && $value == 'on')
                    {
                        $value = 1;
                    }
                    if($key !='attribute_set_id'){

                        $this->$key = $value;
                    }
                }
                $this->created_by=Session::get('userId');
                $this->date_added=date('Y-m-d H-i-s');
                $this->save();
                $product_id = $this->product_id;                
                //$this->sku = 'sku-'.$product_id;
                //$this->save();
                $this->where('product_id',$product_id)->update(['sku'=>'sku-'.$product_id]);
                return $product_id;
            }
        } catch (Exception $ex) {
            return $ex->getMessage();
        }
    }
    
    public function editSaveProduct($data)
    {
        try {
            if(isset($data['product']) && isset($data['product_id']))
            {
                $productId = $data['product_id'];
                $this->find($productId);
                $productData = $data['product'];
                $productSetData = array();
                foreach($productData as $key => $value){
                    if('category_id' == $key)
                    {
                        $value = implode($value, ',');
                    }
                    $productSetData[$key] = $value;
                   }
                   $productSetData['modified_by']=Session::get('userId');
                   $productSetData['date_modified']=date('Y-m-d H-i-s');
                $this->where('product_id', $productId)->update($productSetData);                
            }
        } catch (Exception $ex) {
            return $ex->getMessage();
        }
    }
    
    public function getAllProducts($data)
    {
        try
        {
            $custArr = array();
            $finalCustArr = array();
            $customerId = isset($data['customerId']) ? $data['customerId'] : 0;           //echo $customerId;exit;
            if($customerId)
            {
                // $customerId = (int)$this->roleRepo->decodeData($customerId);
             // echo "<pre/>";print_r($customerId);exit;
                $data['customerId'] = $customerId;
            }


            $manufacturerId = 0;
            if(!$customerId)
            {
                //$currentUserId = \Session::get('userId');
                $manufacturerId = $this->getManufacturerId($customerId);
            }
            if($manufacturerId)
            {
                $data['customerId'] = $manufacturerId;
            }

            if(isset($data['customerId']))
            {
                $childManufacturersIds = $this->getChildManufacturers($data['customerId']);
                $products_data = $this
                        ->whereIn('products.manufacturer_id', $childManufacturersIds)
                        ->where('products.is_deleted', 0)
			->join('eseal_customer', 'eseal_customer.customer_id', '=', 'products.manufacturer_id')
            ->join('product_groups as pg','pg.group_id','=','products.group_id')
                        ->join('master_lookup', 'master_lookup.value', '=', 'products.product_type_id')
                        ->select('products.product_id', 'pg.name as group_name','products.name', 'products.image','products.group_id', 'products.sku','products.material_code as material_code', 'eseal_customer.brand_name as manufacturer_id', DB::Raw('master_lookup.name as product_type'), 'products.is_deleted', DB::Raw("IF(products.is_active = 1, 'Active', 'In-Active') as status"))
                        ->where('products.manufacturer_id',$data['customerId'])
                        ->orderBy('products.product_id', 'DESC')
                        ->skip(0)->take(5450)
                        ->get()->toArray();	                       
            }else{
            $products_data = $this->join('eseal_customer', 'eseal_customer.customer_id', '=', 'products.manufacturer_id')
                        ->join('master_lookup', 'master_lookup.value', '=', 'products.product_type_id')
                        ->join('product_groups as pg','pg.group_id','=','products.group_id')
                        ->select('products.product_id', 'products.name', 'products.image','pg.name as group_name', 'products.sku','products.material_code as material_code', 'eseal_customer.brand_name as manufacturer_id', DB::Raw('master_lookup.name as product_type'), 'products.is_deleted', DB::Raw("IF(products.is_active = 1, 'Active', 'In-Active') as status"))
                        ->orderBy('products.product_id', 'DESC')
                        ->skip(0)->take(5450)
                        ->get()->toArray();
	    }	

            $product_details = json_decode(json_encode($products_data), true);
            // print_r($product_details);exit;
            $imagePath = \URL::to('/') . '/uploads/products/';            
            if(!empty($product_details))
            {
                $allowEditProduct = 1; //$this->roleRepo->checkPermissionByFeatureCode('PRD003');
                $allowDeleteProduct = 1; //$this->roleRepo->checkPermissionByFeatureCode('PRD004');
                $allowRestoreProduct = 1; //$this->roleRepo->checkPermissionByFeatureCode('PRD005');
                foreach ($product_details as $value)
                {
                    $deleted = '';
                    if ($value['is_deleted'] == 1)
                    {
                        $deleted = 'Deleted';
                    }
                    $imageUrl = $this->getProductImageUrl($value['product_id']);
                    if ($imageUrl != '')
                    {
                        $custArr['image'] = '<img src="' . $imagePath . $imageUrl . '" width="75" height="65" />';
                    } else
                    {
                        $custArr['image'] = '';
                    }

                    $custArr['name'] = $value['name'];
                    $custArr['group_name'] = $value['group_name'];
                    $custArr['sku'] = $value['sku'];
                    $custArr['material_code'] = $value['material_code'];
                    $custArr['manufacturer_id'] = $value['manufacturer_id'];
                    $custArr['status'] = $value['status'];
                    $custArr['is_deleted'] = $deleted;
                    $custArr['product_type_id'] = $value['product_type'];
                    $custArr['actions'] = '';
                    if($allowEditProduct)
                    {
                         $custArr['actions'] = $custArr['actions'] . '<span style="padding-left:10px;" ><a href="/products/editproduct/' . $this->roleRepo->encodeData($value['product_id']) . '"><span class="  badge bg-light-blue"><i class="fa fa-pencil"></i></span></a></span>';
                    }
                    if($allowDeleteProduct && $value['is_deleted'] != 1)
                    {
                        $custArr['actions'] = $custArr['actions'] . '<span style="padding-left:5px;" ><a onclick = "deleteEntityType(' . "'" . $this->roleRepo->encodeData($value['product_id']) . "'" . ')" ><span class="badge bg-red"><i class="fa fa-trash-o"></i></span></a></span>';
                    }
                    if($value['is_deleted'] && $allowRestoreProduct)
                    {
                        //$custArr['actions'] = $custArr['actions'] . 'Deleted';
                        $custArr['actions'] = $custArr['actions'] . '<span style="padding-left:5px;" ><a onclick = "restoreEntityType(' . "'" . $this->roleRepo->encodeData($value['product_id']). "'" . ')" ><span class="badge bg-green"><i class="fa fa-refresh"></i></span></a></span>';
                    }
                    $finalCustArr[] = $custArr;
                }
            }
            //print_r($finalCustArr);exit;
            return $finalCustArr;
        } catch (\ErrorException $ex) {
            return $ex->getMessage(). ' , '.$ex->getLine().' , '.$ex->getCode().' , '.$ex->getTraceAsString();
        }        
    }

    public function getAllGdsProducts($data)
    {
        try
        {            
            $custArr = array();
            $finalCustArr = array();
            $customerId = isset($data['customerId']) ? $data['customerId'] : 0;            
            if($customerId)
            {
                $customerId = (int)$this->roleRepo->decodeData($customerId);
                $data['customerId'] = $customerId;
            }
            $manufacturerId = 0;
            if(!$customerId)
            {
                //$currentUserId = \Session::get('userId');
                $manufacturerId = $this->getManufacturerId($customerId);
            }
            if($manufacturerId)
            {
                $data['customerId'] = $manufacturerId;
            }
            if(isset($data['customerId']))
            {
                $childManufacturersIds = $this->getChildManufacturers($data['customerId']);
                $products_data = $this->whereIn('manufacturer_id', $childManufacturersIds)
                        ->where('is_deleted', 0)
                        ->orderBy('product_id', 'DESC')->get();
            }else{
                $products_data = $this->orderBy('product_id', 'DESC')->get();
            }
            $product_details = json_decode(json_encode($products_data), true);            
            $imagePath = \URL::to('/') . '/uploads/products/';            
            if(!empty($product_details))
            {
                $allowEditProduct = $this->roleRepo->checkPermissionByFeatureCode('PRD003');
                $allowDeleteProduct = $this->roleRepo->checkPermissionByFeatureCode('PRD004');
                $allowRestoreProduct = $this->roleRepo->checkPermissionByFeatureCode('PRD005');
                foreach ($product_details as $value)
                {
                    $deleted = '';
                    if ($value['is_active'] == 1)
                    {
                        $status = 'Active';
                    } else
                    {
                        $status = 'In-Active';
                    }
                    if ($value['is_deleted'] == 1)
                    {
                        $deleted = 'Deleted';
                    }
                    $imageUrl = $this->getProductImageUrl($value['product_id']);
                    if ($imageUrl != '')
                    {
                        $custArr['image'] = '<img src="' . $imagePath . $imageUrl . '" width="75" height="65" />';
                    } else
                    {
                        $custArr['image'] = '';
                    }
                $inventory_count=DB::table('product_inventory')
->select(DB::raw('sum(available_inventory) as available_inventory'))
->where('product_id',$value['product_id'])
->get();
//print_r($inventory_count); die;
                    $chann=DB::table('Channel_product as chp')
->select('chp.channel_id')
->where('chp.product_id',$value['product_id'])
->get();
//print_r($chann);exit;
$i=0;
$logoString = '';
foreach ($chann as $chann1) {
    $logo=DB::table('Channel')
    ->select('channel_logo')
    ->where('channel_id',$chann1->channel_id)
    ->first();
     //echo "<pre>";print_r($logo->channel_logo);die;
    if(!empty($logo))
    {
        $logoString = $logoString . "<img src='".$logo->channel_logo."' />";     
    }    
    // echo $logoString;
}
// echo "<pre>";print_r($logo);die;


                    $custArr['select'] = '<span style="padding-left:15px;" > <input type="checkbox" name="check[]"    id="check[]"  value="'.$value['product_id'].'" class="btn btn-default" onChange="multiadd()"></span><span style="padding-left:30px;" ></span>';

                    $custArr['name'] = $value['name'];
                    $custArr['sku'] = $value['sku'];
                    $custArr['manufacturer_id'] = $this->getManufacturerName($value['manufacturer_id']);
                    //$custArr['manufacturer_id'] = $value['manufacturer_id'];
                    $custArr['status'] = $status;
                    $custArr['is_deleted'] = $deleted;
                    $custArr['product_type_id'] = $this->getLookupValue('Product Types', $value['product_type_id']);
                    $custArr['actions'] = '';

                    $custArr['add'] = '<span style="padding-left:11px;"><a data-toggle="modal" data-target="#basicvalCodeModal1" onclick="individualadd(' .$value['product_id']. ')" ><span class="badge bg-green" ><i class="fa fa-plus"></i></span></a></span>
                    <span>'.$logoString.'</span>';
                  /*  $custArr['inventory'] = '<form action="/product/gdsindex">
   <input type="text" name="text_product_name" id="text_'.$value['product_id'].'" align="top" size="7"><br>
   <input type="hidden" id="up_id"  value=" up_name">

  <input type="button" id="update_id" value="Update" onclick="return test1(' . "'" . $this->roleRepo->encodeData($value['product_id']) . "'" . ')">
                    </form>';*/

                     $custArr['inventory'] = '<input type="text" name="text_'.$value['product_id'].'" id="text_'.$value['product_id'].'" align="top" size="5"  value="'.$inventory_count[0]->available_inventory.'">
   <input type="hidden" id="up_id"  value=" up_name">
   <a onclick = "inventoryupdate(' . $value['product_id'] . ')">
 <span class="badge bg-green">Update</span></a></span>';

                    if($allowEditProduct)
                    {
                         $custArr['actions'] = $custArr['actions'] . '<span style="padding-left:10px;" ><a href="/product/editgdsproduct/' . $this->roleRepo->encodeData($value['product_id']) . '"><span class="badge bg-light-blue"><i class="fa fa-pencil"></i></span></a></span>';
                    }

                    if($allowDeleteProduct && $value['is_deleted'] != 1)
                    {
                        $custArr['actions'] = $custArr['actions'] . '<span style="padding-left:5px;" ><a onclick = "deleteEntityType(' . "'" . $this->roleRepo->encodeData($value['product_id']) . "'" . ')" ><span class="badge bg-red"><i class="fa fa-trash-o"></i></span></a></span>';
                    }
                    if($value['is_deleted'] && $allowRestoreProduct)
                    {
                        //$custArr['actions'] = $custArr['actions'] . 'Deleted';
                        $custArr['actions'] = $custArr['actions'] . '<span style="padding-left:5px;" ><a onclick = "restoreEntityType(' . "'" . $this->roleRepo->encodeData($value['product_id']). "'" . ')" ><span class="badge bg-green"><i class="fa fa-refresh"></i></span></a></span>';
                    }
                    $custArr['actions'] = $custArr['actions'] . '<span style="padding-left:5px;" ><a onclick = "preview(' . "'" . $value['product_id']. "'" . ')" ><span class="badge bg-green">Preview</span></a></span>';
                    $finalCustArr[] = $custArr;
                }
            }
            return $finalCustArr;
        } catch (\ErrorException $ex) {
            return $ex->getMessage(). ' , '.$ex->getLine().' , '.$ex->getCode().' , '.$ex->getTraceAsString();
        }        
    }
    
    public function getChildManufacturers($custId)
    {
        try
        {
            $childCompany = DB::table('eseal_customer')->where('eseal_customer.parent_company_id', $custId)->get(array('customer_id'));
            $childCompanyArray = array($custId);
            if(!empty($childCompany))
            {
                foreach($childCompany as $manufacturer)
                {
                    $childCompanyArray[] = $manufacturer->customer_id;
                }
                return $childCompanyArray;
            }else{
                return $childCompanyArray;
            }
        } catch (\ErrorException $ex) {
            die($ex);
        }
    }
    
    public function getProductImageUrl($productId)
    {
        try
        {
            $imageData = DB::table('product_media')->where('product_id', $productId)->where('media_type', 'Image')->where('sort_order', 1)->first(array('url'));
            $imageUrl = '';
            if(!empty($imageData))
            {
                $imageUrl = $imageData->url;
            }
            return $imageUrl;
        } catch (\ErrorException $ex) {
            return $ex;
        }
    }
    
    public function saveProductSlabRate($data)
    {
        try
        {
            if(!empty($data) && isset($data['slab_rate']))
            {
                $slabRates = $data['slab_rate'];
                if(isset($slabRates['rates']))
                {                    
                    foreach($slabRates['rates'] as $rates)
                    {
                        $tempArray = (array) json_decode($rates);
                        $tempArray['product_id'] = isset($data['product_id']) ? $data['product_id'] : 0;
                        \DB::table('products_slab_rates')->insert($tempArray);
                    }
                    return true;
                }
            }
        } catch (\ErrorException $ex) {
            return $ex;
        }
    }
    
    
    public function saveProductLocation($data)
    {
        try
        { 
            if(!empty($data) && isset($data['location']))
            {
                $locationData = \DB::table('product_locations')->where('product_id', $data['location']['product_id'])->first(array(DB::Raw('group_concat(location_id) as location_id')));
                $insertLocationData = array();
                $getDiffLocationData = array();
                $existingLocationList = array();
                $locationListData = isset($data['location']['location_id']) ? $data['location']['location_id'] : '';
                $productId = $data['location']['product_id'];
                if(property_exists($locationData, 'location_id') && $locationData->location_id != '')
                {
                    $existingLocationList = explode(',', $locationData->location_id);
                    $getDiffLocationData = array_diff($existingLocationList, $locationListData);
                }
                foreach($locationListData as $locationId)
                {
                    $tempArray = array();
                    if(!empty($existingLocationList) && in_array($locationId, $existingLocationList))
                    {
                    }else{
                        $tempArray['product_id'] = $productId;
                        $tempArray['location_id'] = $locationId;
                    }
                    if(!empty($tempArray))
                    {
                        $insertLocationData[] = $tempArray;
                    }
                }
                if(!empty($insertLocationData))
                {
                    DB::table('product_locations')->insert($insertLocationData);
                }
                // if(!empty($getDiffLocationData))
                // {
                //     DB::table('product_locations')->where('product_id', $productId)->whereIn('location_id', $getDiffLocationData)->delete();
                // }
            }
        } catch (\ErrorException $ex) {
            return $ex;
        }
    }
    
    public function saveProductAttributesets($data)
    {
        try
        {
            if(!empty($data) && isset($data['product_attribute_sets']))
            {
                $productAttributeDetails = isset($data['product_attribute_sets']['attribute_details']) ? $data['product_attribute_sets']['attribute_details'] : array();
                if(!empty($productAttributeDetails))
                {
                    $productId = isset($data['product_attribute_sets']['product_id']) ? $data['product_attribute_sets']['product_id'] : 0;
                    //DB::table('product_attributesets')->where('product_id', $productId)->delete();
                    $attributeSetIds = DB::table('product_attributesets')->where('product_id', $productId)->value(DB::raw('group_concat(id)'));
                    $attributeSetIdsArray = array();                    
                    if(!empty($attributeSetIds))
                    {
                        $attributeSetIdsArray = explode(',' ,$attributeSetIds);
                    }                    
                    $tempIds = array();
                    foreach($productAttributeDetails as $productAttr)
                    {
                        $tempArray = (array) json_decode($productAttr);
                        if(!isset($tempArray['id']))
                        {
                            $tempArray['product_id'] = $productId;
                            $groupId = isset($data['product']['group_id']) ? $data['product']['group_id'] : 0;
                            $tempArray['product_group_id'] = $groupId;
                            DB::table('product_attributesets')->insert($tempArray);   
                        }else if(isset($tempArray['id'])){
                            $tempIds[] = $tempArray['id'];
                        }
                    }
                    $deleteIds = array();
                    $tempDiffArray = array_diff($attributeSetIdsArray, $tempIds);
                    if(!empty($tempDiffArray))
                    {
                        $deleteIds = $tempDiffArray;
                    }else{
                        $tempDiffArray = array_diff($tempIds, $attributeSetIdsArray);
                        if(!empty($tempDiffArray))
                        {
                            $deleteIds = $tempDiffArray;
                        }
                    }
                    // foreach($deleteIds as $id)
                    // {
                    //     DB::table('product_attributesets')->where('id', $id)->delete();
                    // }
                }
            }
        } catch (\ErrorException $ex) {
            return $ex;
        }
    }
    
    public function saveComponentProducts($data)
    {
        try
        {
            if(!empty($data) && isset($data['component_selected']))
            {
                $productTypeId = isset($data['product']['product_type_id']) ? $data['product']['product_type_id'] : 0;
                if($productTypeId != 8003)
                {
                    return;
                }
                $productComponentSelected = isset($data['component_selected']) ? $data['component_selected'] : array(); 
                if(!empty($productComponentSelected))
                {
                    $productId = isset($data['product_id'])?$data['product_id']:0;
                    if(isset($data['component_selected']['product_id'])){
                        unset($productComponentSelected['product_id']);    
                    }
                    
                    /*$componentIds = DB::table('product_components')->where('product_id', $productId)->value(DB::raw('group_concat(component_id)'));
                    $attributeSetIdsArray = array(); 
                    $componentIdsArray = array();
                    if(!empty($componentIds))
                    {
                        $componentIdsArray = explode(',' ,$componentIds);
                    }
*/                  
                    //DB::table('product_components')->where('product_id', $productId)->delete();
                    $productComponentSelected = isset($productComponentSelected[0]) ? array_unique(explode(',', $productComponentSelected[0])) : array();
                    // if(empty($productComponentSelected))
                    // {
                    //     DB::table('product_components')->where('product_id', $productId)->delete();
                    //     return;
                    // }
                    //dd($productComponentSelected);
                    $tempIds = array();
                    $componentdata = array();
                    foreach($productComponentSelected as $id=>$componentId)
                    {
                        //$aa++;
                        $tempArray = array();
                        if(!empty($componentId))
                        {
                            $productDetails = DB::table('products')->where('product_id', $componentId)->first(array('product_type_id', 'material_code'));
                            $productErpCode = DB::table('products')->where('product_id', $productId)->value('material_code');
                            // if(isset($data['quantity-'.$componentId]) and !empty($data['quantity-'.$componentId])){
                            //     for($i=0;$i<$data['quantity-'.$componentId];$i++){
                                    $tempIds[] = $componentId;
                                    $tempArray['product_id'] = $productId;
                                    $tempArray['product_erp_code'] = $productErpCode;
                                    $tempArray['component_id'] = $componentId;                                
                                    $tempArray['component_type_id'] = $productDetails->product_type_id;
                                    $tempArray['component_erp_code'] = $productDetails->material_code;    
                                    if(isset($data['quantity-'.$componentId]) && $data['quantity-'.$componentId] >0){
                                        $tempArray['qty'] = $data['quantity-'.$componentId];    
                                    }
                                    else{
                                     $tempArray['qty'] = 1;       
                                    }
                                //}
                                
                            //}
                                                        
                        }
                        // else if(!empty($componentId) && !in_array($componentId, $componentIdsArray)){
                        //     $productDetails = DB::table('products')->where('product_id', $componentId)->first(array('product_type_id', 'material_code'));
                        //     $productErpCode = DB::table('products')->where('product_id', $productId)->value('material_code');
                        //     $tempIds[] = $componentId;
                        //     //if(isset($data['quantity-'.$componentId])){
                                
                        //             $tempIds[] = $componentId;
                        //             $tempArray['product_id'] = $productId;
                        //             $tempArray['product_erp_code'] = $productErpCode;
                        //             $tempArray['component_id'] = $componentId;                                
                        //             $tempArray['component_type_id'] = $productDetails->product_type_id;
                        //             $tempArray['component_erp_code'] = $productDetails->material_code;    
                        //             if(isset($data['quantity-'.$componentId])){
                        //                 $tempArray['qty'] = $data['quantity-'.$componentId];    
                        //             }
                                
                                
                        //     //}
                        // }
                        if(!empty($tempArray))
                        {
                            array_push($componentdata,$tempArray);
                            
                        }
    //                     $componentIds = DB::table('product_components')->where('product_id', $productId)->value(DB::raw('group_concat(component_id)'));
    // //                    $attributeSetIdsArray = array(); 
    //                     $componentIdsArray = array();
    //                     if(!empty($componentIds))
    //                     {
    //                         $componentIdsArray = explode(',' ,$componentIds);
    //                     }

                    }
                    //dd($componentdata);
                    if(!empty($componentdata)){
                        DB::table('product_components')->insert($componentdata);
                    }
                    //dd($aa);
                    // if(!empty($componentIdsArray))
                    // {
                    //     $deleteIds = array();
                    //     $tempDiffArray = array();
                    //     $tempDiffArray2 = array();
                    //     $tempDiffArray = array_diff($productComponentSelected, $componentIdsArray);
                    //     $tempDiffArray2 = array_diff($componentIdsArray, $productComponentSelected);
                    //     if(!empty($tempDiffArray))
                    //     {
                    //         $deleteIds = $tempDiffArray;
                    //     }else if(!empty($tempDiffArray2)){
                    //         $deleteIds = $tempDiffArray2;
                    //     }/*else{
                    //         $tempDiffArray = array_diff($tempIds, $attributeSetIdsArray);
                    //         if(!empty($tempDiffArray))
                    //         {
                    //             $deleteIds = $tempDiffArray;
                    //         }
                    //     }*/
                    //     foreach($deleteIds as $id)
                    //     {
                    //         DB::table('product_components')->where(array('product_id' => $productId, 'component_id' => $id))->delete();
                    //     }
                    // }                    
                }
            }
        } catch (\ErrorException $ex) {
            return $ex;
        }
    }
    
    public function saveProductsGdsData($data)
    {
        //echo "hai";exit;
        try
        {
            if(!empty($data) && isset($data['prod_text_det']))
            {                    
                $productTextData = isset($data['prod_text_det']) ? $data['prod_text_det'] : '';
                if($productTextData == '')
                {
                    return;                    
                }else{
                    $productId = isset($data['product_id']) ? $data['product_id'] : 0;
                    if(!$productId)
                    {
                        return;
                    }
                    $temp = DB::table('prod_text_det')->where('product_id', $productId)->value('product_id');                    
                    if(empty($temp))
                    {
                        $data['prod_text_det']['product_id'] = $productId;
                        DB::table('prod_text_det')->insert($data['prod_text_det']);
                        $last = DB::getQueryLog();
                        \Log::info(end($last));
                    }else{
                        DB::table('prod_text_det')->where('product_id', $productId)->update($data['prod_text_det']);
                    }
                }
            }
        } catch (\ErrorException $ex) {
            return $ex;
        }
    }
    
    public function getProductData($productId)
    {

        $productsData = $this->where('product_id', $productId)->first();
        //print_r($productData);exit;
       
        $productData = array();
        if(!empty($productsData))
        {
            $productData = $productsData->attributes;
        }
        
        $attributesData = array();
        if(isset($productData['attribute_set_id']))
        {
            $attributeList = DB::table('attributes')
                    ->join('attribute_set_mapping', 'attribute_set_mapping.attribute_id', '=', 'attributes.attribute_id')
                    ->join('product_attributes', 'product_attributes.attribute_id', '=', 'attributes.attribute_id')
                    ->where('attribute_set_mapping.attribute_set_id', $productData['attribute_set_id'])
                    ->where('attributes.is_inherited', 0)
                    ->where('product_attributes.product_id', $productId)
                    ->select('attributes.*', 'product_attributes.value')
                    ->get()->toArray();
                    
            if(!empty($attributeList))
            {
                $attributesData = $attributeList;
                if(!empty($attributeList))
                {
                    foreach($attributeList as $attributeLists)
                    {
                        $attributeOptions = array();
                        if($attributeLists->lookup_id)
                        {
                            $lookupData = DB::table('attribute_lookup_group')
                                    ->where('group', $attributeLists->lookup_id)
                                    ->get(array('id', 'display_text'))->toArray();
                            if(!empty($lookupData))
                            {
                                foreach($lookupData as $lookup)
                                {
                                    $attributeOptions[$lookup->id] = $lookup->display_text;
                                }
                                $attributeLists->options = $attributeOptions;
                            }
                        }
                    }
                    $attributesData = $attributeList;
                }
            }else{
                $defaultAttributeList = DB::table('attributes')
                    ->join('attribute_set_mapping', 'attribute_set_mapping.attribute_id', '=', 'attributes.attribute_id')
                    ->where('attribute_set_mapping.attribute_set_id', $productData['attribute_set_id'])
                    ->where('attributes.is_inherited', 0)
                    ->select('attributes.*', 'default_value as value')
                    ->get()->toArray();
                if(!empty($defaultAttributeList))
                {
                    foreach($defaultAttributeList as $attributeLists)
                    {
                        $attributeOptions = array();
                        if($attributeLists->lookup_id)
                        {
                            $lookupData = DB::table('attribute_lookup_group')
                                    ->where('group', $attributeLists->lookup_id)
                                    ->get(array('id', 'display_text'))->toArray();
                            if(!empty($lookupData))
                            {
                                foreach($lookupData as $lookup)
                                {
                                    $attributeOptions[$lookup->id] = $lookup->display_text;
                                }
                                $attributeLists->options = $attributeOptions;
                            }
                        }
                    }
                    $attributesData = $defaultAttributeList;
                }
            }            
        }
        
        $products_attribute_data = DB::table('product_attributes')
                ->join('attributes', 'attributes.attribute_id', '=', 'product_attributes.attribute_id')
                ->where('product_attributes.product_id', $productId)
                ->where('attributes.is_inherited', 1)
                ->select('attributes.attribute_code', 'product_attributes.value')
                ->get()->toArray();
                // echo "<pre/>";
                // print_r($products_attribute_data);exit;
        
        $productAttributeData = array();
        if(!empty($products_attribute_data))
        {
            foreach($products_attribute_data as $attributeData)
            {
                $attributesData[$attributeData->attribute_code] = $attributeData->value;
            }
        }elseif(isset($productData['attribute_set_id'])){
            $defaultAttributeList = DB::table('attributes')
                    ->join('attribute_set_mapping', 'attribute_set_mapping.attribute_id', '=', 'attributes.attribute_id')
                    ->where('attribute_set_mapping.attribute_set_id', $productData['attribute_set_id'])
                    ->where('attributes.is_inherited', 1)
                    ->select('attributes.attribute_code')
                    ->get()->toArray();
            if(!empty($defaultAttributeList))
            {
                foreach($defaultAttributeList as $defaultAttributeData)
                {
                    $attributesData[$defaultAttributeData->attribute_code] = '';
                }
            }
        }
        
        $productPackage = new ProductPackage();
        $products_package_data = $productPackage
                ->join('master_lookup', 'master_lookup.value', '=', 'product_packages.level')
                ->where('product_id', $productId)
                ->get(array('product_packages.*', 'master_lookup.name'));
        
        $productPackageData = array();
        if(!empty($products_package_data))
        {
            foreach($products_package_data as $packageData)
            {
                $productPackageData[] = (object) $packageData->attributes;
            }
        }
        
        $productPallet = new ProductPallet();
        $products_pallet_data = $productPallet->where('product_id', $productId)->first();
        $productPalletData = array();
        if(!empty($products_pallet_data))
        {
            $productPalletData = $products_pallet_data->attributes;            
        }else{ 
            $palletColumns = \Schema::getColumnListing('product_pallet');

            $palletDefaultData = array();
            foreach($palletColumns as $columns)
            {
                $palletDefaultData[$columns] = '';
            }
            $productPalletData = (object) $palletDefaultData;
        }
        
        $productMedia = new ProductMedia();
        $products_media_data = $productMedia->where('product_id', $productId)->get();
        $productMediaData = array();
        if(!empty($products_media_data))
        {
            foreach($products_media_data as $mediaData)
            {
                $productMediaData[] = (object) $mediaData->attributes;
            }
        }
        $products_location_data = \DB::table('product_locations')->where('product_id', $productId)->first(array(DB::Raw('group_concat(location_id) as location_id')));
        
        $productLocationData = array();
        if(!empty($products_location_data))
        {
            $productLocationData = $products_location_data->location_id;
        }else{
            $productLocationData = 0;
            }
        
        $productAttributeSets = array();
//        $productsAttributeSetData = \DB::table('product_attributesets')
//                ->where('product_id', $productId)
//                ->get();
        $productsAttributeSetData = \DB::table('product_attributesets')
                ->join('attribute_sets', 'attribute_sets.attribute_set_id', '=', 'product_attributesets.attribute_set_id')
                ->join('locations', 'locations.location_id', '=', 'product_attributesets.location_id')
                ->where('product_attributesets.product_id', $productId)
                ->get(array('product_attributesets.id','attribute_sets.attribute_set_id','attribute_sets.attribute_set_name', 'locations.location_id', 'locations.location_name'))->toArray();
        if(!empty($productsAttributeSetData))
        {
            $productAttributeSets = $productsAttributeSetData;
        }
        
        $productData['attribute_data'] = $attributesData;
        $productData['package_data'] = $productPackageData;
        $productData['pallet_data'] = $productPalletData;
        $productData['media_data'] = $productMediaData;
        $productData['location_id'] = $productLocationData;
        $productData['product_attributesets'] = $productAttributeSets;
        
        $merged = (object) $productData;
        return $merged;
    }
    
    
    public function getManufacturerId($currentUserId = null)
    {
        if(!$currentUserId)
        {
            $currentUserId = \Session::get('userId');
        }   
        // echo $currentUserId;exit;     
        $manufacturerDetails = DB::table('users')->where('user_id', $currentUserId)->first(array('customer_id')); 

        $manufacturerId = 0;
        if(!empty($manufacturerDetails))
        {
            $manufacturerId = $manufacturerDetails->customer_id;
        }
        return $manufacturerId;
    }
    
    public function deleteProduct($productId)
    {
        try
        {
            if($productId)
            {
                $productId = $this->roleRepo->decodeData($productId);
                $dependentTables[] = 'products_slab_rates';
                $dependentTables[] = 'product_attributes';
                $dependentTables[] = 'product_inventory';
                $dependentTables[] = 'product_locations';
                $dependentTables[] = 'product_media';
                $dependentTables[] = 'product_packages';
                $dependentTables[] = 'product_pallet';
                $dependentTables[] = 'products';
//                foreach($dependentTables as $tableName)
//                {
//                    DB::table($tableName)->where('product_id', $productId)->delete();
//                }
                $updateData['is_deleted'] = 1; 
                DB::table('products')->where('product_id', $productId)->update($updateData);
            }else{
                return 'No Product Id';
            }
        } catch (\ErrorException $ex) {
            return $ex->getMessage();
        }
    }
    
    public function restoreProduct($productId)
    {
        try
        {
            if($productId)
            {                
                $updateData['is_deleted'] = 0; 
                DB::table('products')->where('product_id', $productId)->update($updateData);
                return 'Sucessfully Restored.';
            }else{
                return 'No Product Id';
            }
        } catch (\ErrorException $ex) {
            return $ex->getMessage();
        }
    }
    
    public function getManufacturers($manufacturerId)
    {
        if($manufacturerId)
        {
            $eselaCustomers = DB::table('eseal_customer')->where('customer_id', $manufacturerId)->get(array('customer_id', 'brand_name'));
        }else{
            $eselaCustomers = DB::table('eseal_customer')->get(array('customer_id', 'brand_name'));
        }
        $manufacturerDetails = array();
        $customerTypeId = $this->getCurrentUserType($this->getManufacturerId());        
        if(1001 != $customerTypeId)
        {
            $manufacturerDetails[0] = 'Please select ..';
        }
        foreach ($eselaCustomers as $customers) {            
            $manufacturerDetails[$customers->customer_id] = $customers->brand_name;
        }
        // echo "hai";exit;
        return $manufacturerDetails;
    }
    
    public function getManufacturerName($manufacturerId)
    {
        if($manufacturerId != '' && $manufacturerId != 0)
        {
            $eselaCustomers = DB::table('eseal_customer')->where('customer_id', $manufacturerId)->first(array('brand_name'));
            if(!empty($eselaCustomers))
            {
                $manufacturerDetails = $eselaCustomers->brand_name;            
            }else{
                return $manufacturerId;
            }
        }else{
            $manufacturerDetails = $manufacturerId;
        }
        return $manufacturerDetails;
    }
    
    public function getWeightClass()            
    {
        $weightClassData = DB::table('weight_classes')->get(array('weight_class_id', 'title'));
        $weightClassDetails = array();
        foreach ($weightClassData as $weightClass) {            
            $weightClassDetails[$weightClass->weight_class_id] = $weightClass->title;
        }
        return $weightClassDetails;   
    }
    
    public function getLookupData($categoryName)            
    {
        $result = DB::table('lookup_categories')->where('name', $categoryName)->first(array('id'));
        //print_r($result);exit;
        $returnData = array();
        if(!empty($result))
        {
            $categoryId = $result->id;
            $result = DB::table('master_lookup')->where('category_id', $categoryId)->get(array('value', 'name'));
            if(!empty($result))
            {
                $returnData[0] = 'Please select..';
                foreach ($result as $data) {            
                    $returnData[$data->value] = $data->name;
                }
            }            
        }
            // echo "hai";exit;
        return $returnData;
    }
    
    public function getLookupValue($categoryName, $id)   

    {
        // echo "hai";exit;
        $result = DB::table('lookup_categories')->where('name', $categoryName)->first(array('id'));
        $returnData = array();
        if(!empty($result))
        {
            $categoryId = $result->id;
            $result = DB::table('master_lookup')->where('value', $id)->first(array('name'));
            if(!empty($result))
            {
                $returnData = $result->name;
            }            
        }
        return $returnData;
    }
    
    public function getCategories()
    {
        $result = DB::table('categories')->get(array('category_id', 'name'));
        $returnData = array();
        if(!empty($result))
        {
            foreach ($result as $data) {            
                $returnData[$data->category_id] = $data->name;
            }
        }
        return $returnData;
    }
    
    public function getCategoryList($categoryId)
    {
        try
        {
            $resultArray = array();            
            $categoryArray = array();            
            $categoryList = $this->listCategoriesById($categoryId);            
            foreach($categoryList as $category)
            {                
                $index = 0;
                $index2 = 0;
                $index3 = 0;
                $tempArray = array();
                $categoryId = $category->category_id;
                if(in_array($categoryId, $categoryArray))
                {
                    continue;                    
                }
                $categoryArray[] = $categoryId;
                $tempArray[$index] = $category;
                $childCategoryList = $this->listCategoriesById($categoryId);                
                if(count($childCategoryList) == 1)
                {
                    $tempArray[$index]->childs = $childCategoryList;
                }elseif(count($childCategoryList) > 1){
                    $tempArray2 = array();
                    foreach($childCategoryList as $categoryLists)
                    {
                        $tempArray3 = array();
                        $tempArray2[$index2] = $categoryLists;
                        $childCategoryId = $categoryLists->category_id;
                        $categoryArray[] = $childCategoryId;
                        $childCategoryList = $this->listCategoriesById($childCategoryId);
                        if(count($childCategoryList) == 1)
                        {
                            $tempArray2[$index2]->childs = $childCategoryList;                            
                        }elseif(count($childCategoryList) > 1){                            
                            foreach($childCategoryList as $childCategories)
                            {
                                $tempArray4 = array();                                
                                $tempArray3[$index3] = $childCategories;
                                $childChildCategoryId = $childCategories->category_id;
                                $categoryArray[] = $childChildCategoryId;
                                $childChildCategoryList = $this->listCategoriesById($childChildCategoryId);
                                if(count($childChildCategoryList) == 1)
                                {
                                    $tempArray3[$index3]->childs = $childChildCategoryList;                            
                                }elseif(count($childChildCategoryList) > 1){                            
                                    foreach($childChildCategoryList as $childChildCategories)
                                    {
                                        $categoryArray[] = $childChildCategories->category_id;
                                        $tempArray4[] = $childChildCategories;
                                    }
                                }
                                if(!empty($tempArray4))
                                {
                                    $tempArray3[$index3]->childs = $tempArray4;
                                }
                                $index3++;
                            }
                        }
                        if(!empty($tempArray3))
                        {
                            $tempArray2[$index2]->childs = $tempArray3;
                        }
                        $index2++;
                    }
                    $tempArray[$index]->childs = $tempArray2;
                    $index++;
                }
                $resultArray[] = $tempArray;
                $index++;
            }
            return ($resultArray);
        } catch (Exception $ex) {
            echo "<pre>";print_r($ex);die;
        }
    }
    
    public function saveManufacturerCategory($data)
    {
        try
        {
            $manufacturerId = isset($data['manufacturer_id']) ? $data['manufacturer_id'] : '';
            $categoryList = isset($data['category_list']) ? $data['category_list'] : '';
            $categoryList = array_unique($categoryList);
            if($manufacturerId != '')
            {
                $getManufacturerCategories = $this->getManufacturerCategories($manufacturerId);
                $categoryLists = '';
                if(!empty($getManufacturerCategories))
                {
                    $categoryLists = $getManufacturerCategories->category_id;
                }
                if($categoryList  != '' )
                {
                    $insertData = array();
                    $getDiffData = array();
                    $existingCategoryList = array();                        
                    if($categoryLists != '')
                    {
                        $existingCategoryList = explode(',', $categoryLists);
                        $getDiffData = array_diff($existingCategoryList, $categoryList);
                    }
                    foreach($categoryList as $categoryId)
                    {
                        if($categoryId == 0 || $categoryId == '')
                        {
                            continue;
                        }
                        $tempArray = array();
                        if(!empty($existingCategoryList) && in_array($categoryId, $existingCategoryList))
                        {
                        }else{
                            $tempArray['customer_id'] = $manufacturerId;
                            $tempArray['category_id'] = $categoryId;
                        }
                        if(!empty($tempArray))
                        {
                            $insertData[] = $tempArray;
                        }
                    }
                    if(!empty($insertData))
                    {
                        DB::table('customer_categories')->insert($insertData);
                    }
                    // if(!empty($getDiffData))
                    // {
                    //     DB::table('customer_categories')->where('customer_id', $manufacturerId)->whereIn('category_id', $getDiffData)->delete();
                    // }
                }else{
                    if($categoryLists != '')
                    {
                        $existingCategoryList = explode(',', $categoryLists);
                        // if(count($existingCategoryList) > 0)
                        // {
                        //     DB::table('customer_categories')->where('customer_id', $manufacturerId)->delete();                            
                        // }
                    }
                }
            }
        } catch (\ErrorException $ex) {
            echo "<pre>";print_R($ex);die;
        }   
    }
    
    public function getManufacturerCategories($manufacturerId)
    {
        try
        {
            return DB::table('customer_categories')->where('customer_id', $manufacturerId)->first(array(DB::Raw('group_concat(category_id) as category_id')));
        } catch (Exception $ex) {

        }
    }
    
    public function listCategoriesById($categoryId)
    {
        if(!$categoryId)
        {
            $result = DB::table('categories')->get(array('category_id', 'name', 'parent_id'));
        }else{
            $result = DB::table('categories')->where('parent_id', $categoryId)->get(array('category_id', 'name', 'parent_id'));
        }
        $returnData = array();
        if(!empty($result))
        {
            foreach ($result as $data) {            
                //$returnData[$data->category_id] = $data->name;
                $returnData[] = $data;
            }
        }
        return $returnData;
    }
    
    public function saveCompleteData($productId)
    {
        try
        {
            $productDataResult = DB::table('products')
                    ->join('eseal_customer', 'eseal_customer.customer_id', '=', 'products.manufacturer_id')
                    ->join('product_media', 'product_media.product_id', '=', 'products.product_id')
                    ->join('product_media as video', 'video.product_id', '=', 'products.product_id')
                    ->where('products.product_id', $productId)
                    ->where('product_media.media_type', 'Image')
                    ->where('video.media_type', 'Video')
                    ->where('product_media.sort_order', 1)
                    ->select(array('products.category_id as product_category', 'products.product_type_id as product_type_id'
                        , 'products.title as product_title', 'products.description as product_description', 'products.weight as product_weight'
                        , 'eseal_customer.brand_name as product_manufacturer_name', 'eseal_customer.brand_description as product_manufacturer_description'
                        , 'eseal_customer.website as product_manufacturer_website_link'
                        , 'product_media.url as product_images', 'video.url as product_videos'))
                    ->first();
            if(!empty($productDataResult))
            {
                $productAttribute = new ProductAttributes();
                $productDataArray = array();
                foreach($productDataResult as $key => $value)
                {
                    $attributeId = $productAttribute->getAttributeId($key);
                    $productAttributeData = $productAttribute
                            ->where('product_id', $productId)
                            ->where('attribute_id', $attributeId)
                            ->first(array('id'));                    
                    if(gettype($productAttributeData) == 'object')
                    {
                        $updateData['value'] = $value;
                        DB::table('product_attributes')
                                ->where('id', $productAttributeData->id)
                                ->update($updateData);
                    }else{
                        $productDataArray[$attributeId] = $value;    
                    }
                }
                if(!empty($productDataArray))
                {
                    $productAttribute->save($productDataArray);
                }
            }
        } catch (\ErrorException $ex)
        {
            return $ex->getMessage();
        }
    }
    
    public function getManufacturerProducts($manufacturerId, $productId)
    {
        try
        {
            $productData = array();            
            $componentDataArray = array();
            if($productId)
            {
                $componentArray = array();
                $componentArray = DB::table('product_components')->where('product_id', $productId)->value(DB::Raw('group_concat(component_id)'));                
                $productCollection = DB::table('products')->join('master_lookup', 'master_lookup.value', '=', 'products.product_type_id')
                    ->leftJoin('product_components', 'product_components.product_id', '=', 'products.product_id')
                    ->where('manufacturer_id', $manufacturerId)
                    ->where('master_lookup.name', '!=', 'Finished Product')
                    ->whereNotIn('products.product_id', explode(',', $componentArray))
                    ->get(array('products.product_id', 'products.name'));
                $componentDataArray = DB::table('products')->join('master_lookup', 'master_lookup.value', '=', 'products.product_type_id')
                    ->leftJoin('product_components', 'product_components.product_id', '=', 'products.product_id')
                    ->where('manufacturer_id', $manufacturerId)
                    ->where('master_lookup.name', '!=', 'Finished Product')
                    ->whereIn('products.product_id', explode(',', $componentArray))
                    ->get(array('products.product_id', 'products.name'));
            }else{
                $productCollection = DB::table('products')->join('master_lookup', 'master_lookup.value', '=', 'products.product_type_id')
                    ->leftJoin('product_components', 'product_components.product_id', '=', 'products.product_id')
                    ->where('manufacturer_id', $manufacturerId)
                    ->where('master_lookup.name', '!=', 'Finished Product')
                    ->get(array('products.product_id', 'products.name'));
            }            
            if(!empty($productCollection))
            {
                foreach($productCollection as $product)
                {
                    $productData[$product->product_id] = $product->name;
                }
            }
            if($productId)
            {
                $tempArray = array();
                if(!empty($componentDataArray))
                {
                    foreach($componentDataArray as $componentData)
                    {
                        $tempArray[$componentData->product_id] = $componentData->name;
                    }
                }
                if(!empty($tempArray))
                {
                    $productData['component_ids'] = $tempArray;
                }
            } 
            if($productId){
                $productData['product_quantity'] = DB::table('product_components')->where(['product_id'=>$productId])->groupby('component_id')->lists('qty','component_id');
            }
            else{
                $productData['product_quantity'] = [];
            }
            return $productData;
        } catch (\ErrorException $ex) {
            return $ex->getMessage();
        }
    }
    
    public function getCurrentUserType($manufacturerId)
    {
        try
        {
            return DB::table('eseal_customer')->where('customer_id', $manufacturerId)->value('customer_type_id');
        } catch (\ErrorException $ex) {
            return 0;
        }
        
    }
   
}
