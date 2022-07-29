<?php
/*
namespace Products;
use \DB;
use \Response;
use Central\Repositories\RoleRepo;
use Central\Repositories\CustomerRepo;
use Session;*/
namespace App\Models\Products;
use App\Repositories\RoleRepo;
use App\Repositories\CustomerRepo;
use DB;
use Session;
use Illuminate\Database\Eloquent\Model;
class ProductAttributes extends Model {

//class ProductAttributes extends \Eloquent{

    protected $table = 'product_attributes'; // table name
    protected $primaryKey = 'id';
    public $timestamps = false;
    public function __construct()
    {
        $this->roleRepo = new RoleRepo;
    }

    // model function to store product data to database

    public function saveProductAttributes($data)
    {
        try
        {
            if(!isset($data['product']['attribute_set_id']))
            {
                if(isset($data['attributes']))
                {
                    foreach ($data['attributes'] as $attributeCode => $attributeValue)
                    {
                        $attributeId = DB::table('attributes')->where('attribute_code', $attributeCode)->value('attribute_id');
                        if(!empty($attributeId))
                        {
                            $updateArray['product_id'] = $data['product_id']; 
                            $updateArray['attribute_id'] = $attributeId; 
                            $updateArray['value'] = $attributeValue; 
                            $productAttrId = DB::table('product_attributes')->where(array('product_id' => $data['product_id'], 'attribute_id' => $attributeId))->value('id');
                            if(!empty($productAttrId))
                            {
                                DB::table('product_attributes')->where(array('product_id' => $data['product_id'], 'attribute_id' => $attributeId))->update($updateArray);
                            }else{
                                DB::table('product_attributes')->insert($updateArray);
                            }    
                        }
                    }
                }
                return;
            }
            $attributeList = $this->getAttributesListById($data['product']['attribute_set_id']);
            $productData = $data['attributes'];
            $productId = isset($data['product_id']) ? $data['product_id'] : 0;
            $manufacturerId = isset($data['product']['manufacturer_id']) ? $data['product']['manufacturer_id'] : 0;
            if (!empty($productId))
            {
                foreach ($attributeList as $attribute)
                {
                    $productAttributeValue = isset($productData[$attribute->attribute_code]) ? $productData[$attribute->attribute_code] : ' ';
                    $attributeProductData = DB::table('product_attributes')
                            ->where('attribute_id', $attribute->attribute_id)
                            ->where('product_id', $productId)
                            ->first(array('id', 'value'));                    
                    if($attribute->input_type == 'file' && !empty($productAttributeValue) && $productAttributeValue != '' && $productAttributeValue != ' ')
                    {
                        $folderName = $manufacturerId.'/instructions/';
                        $filePath = $this->upload($folderName, $productAttributeValue);
                        $productAttributeValue = $filePath;
                    }
                    $updateData['value'] = $productAttributeValue;
                    if(gettype($attributeProductData) == 'object')
                    {
                        if($productAttributeValue != '' && $productAttributeValue != ' ')
                        {
                            DB::table('product_attributes')
                                ->where('id', $attributeProductData->id)
                                ->update($updateData);
                        }
                    }else{
                        if($productId && $attribute->attribute_id)
                        {
                            DB::table('product_attributes')->insert(['product_id' => $productId, 'attribute_id' => $attribute->attribute_id, 'value' => $productAttributeValue]);
                        }
                    }
                }
                return true;
            } else
            {
                return false;
            }
        } catch (Exception $ex)
        {
            return $ex;
        }
    }

    public function upload($folder_name, $file)
    {
        // setting up rules
        $rules = array('image' => 'required',); //mimes:jpeg,bmp,png and for max size max:10000
        // doing the validation, passing post data, rules and the messages
        if (!empty($file))
        {
            $destinationPath = public_path() . '/uploads/products/'; // upload path               
            if (!file_exists($destinationPath . $folder_name))
            {
                $result = \File::makeDirectory($destinationPath . $folder_name, 0775);
            }
            $extension = $file->getClientOriginalExtension(); // getting image extension
            $fileName = rand(11111, 99999) . '.' . $extension; // renameing image
            $file->move($destinationPath . $folder_name, $fileName); // uploading file to given path
            // sending back with message
            return $folder_name . $fileName;
        } else
        {
            // sending back with error message.
            return false;
        }
    }

    public function getAttributeId($key)
    {
        try
        {
            $result = DB::table('attributes')->where('attribute_code', $key)->first(array('attribute_id'));
            if ($result)
            {
                return $result->attribute_id;
            } else
            {
                return 0;
            }
        } catch (Exception $ex)
        {
            
        }
    }
    
    public function getAttributeList($data)
    {
        try
        {
            if(isset($data['attribute_set_id']))
            {
                $attributeSetId = $data['attribute_set_id'];
//                $result = DB::table('attributes_groups')                        
//                        ->join('attribute_mapping', 'attribute_mapping.attribute_map_id', '=', 'attributes_groups.attribute_group_id')
//                        ->join('attributes', 'attributes.attribute_id', '=', 'attribute_mapping.attribute_id')
//                        ->select('attributes.attribute_id', 'attributes.attribute_code', 'attributes.name', 'attributes.input_type', 'attributes.is_dynamic')
//                        ->where('attributes_groups.attribute_group_id', $data['attribute_set_id'])
//                        ->get();
                $productId = isset($data['product_id']) ? $data['product_id'] : 0;
                $productAttributeSetId = 0;
                if($productId)
                {
                    $productAttributeSetId = DB::table('products')->where('product_id', $productId)->value('attribute_set_id');                    
                }
                if($productId && $productAttributeSetId && ($productAttributeSetId == $attributeSetId))
                {
                    $attributeResult = DB::table('attributes')
                        ->join('attribute_set_mapping', 'attribute_set_mapping.attribute_id', '=', 'attributes.attribute_id')
                        ->join('product_attributes', 'product_attributes.attribute_id', '=', 'attributes.attribute_id')
                        ->where('attribute_set_mapping.attribute_set_id', $data['attribute_set_id'])
                        ->where('attributes.is_inherited', 0)
                        ->where('product_attributes.product_id', $productId)
                        ->select('attributes.*', 'product_attributes.value')
                        ->get();
                }else{
                    $attributeResult = DB::table('attributes')
                        ->join('attribute_set_mapping', 'attribute_set_mapping.attribute_id', '=', 'attributes.attribute_id')                        
                        ->where('attribute_set_mapping.attribute_set_id', $data['attribute_set_id'])
                        ->where('attributes.is_inherited', 0)                        
                        ->get();
                }
                if(!empty($attributeResult))
                {
                    foreach($attributeResult as $attribute)
                    {
                        if('select' == $attribute->input_type)
                        {
                            /*$attributeLookup = DB::table('attribute_lookup_group')
                                    ->where('group', $attribute->lookup_id)
                                    ->get(array('id', 'display_text'));
                            $attributeOptions = array();
                            if(!empty($attributeLookup))
                            {
                                foreach($attributeLookup as $lookup)
                                {
                                    $attributeOptions[$lookup->id] = $lookup->display_text;
                                }
                            }
                            $attribute->options = $attributeOptions;*/
                            $attributeLookup = DB::table('attribute_options')
                                    ->where('attribute_id', $attribute->attribute_id)
                                    ->get(array('option_id', 'option_value'));
                            $attributeOptions = array();
                            if(!empty($attributeLookup))
                            {
                                foreach($attributeLookup as $lookup)
                                {
                                    $attributeOptions[$lookup->option_id] = $lookup->option_value;
                                }
                            }
                            $attribute->options = $attributeOptions;
                        }
                    }
                }
                return $attributeResult;
            }else{
                return 'No Attribute group Id';
            }
        } catch (\ErrorException $ex) {
            return $ex->getMessage();
        }
    }
    
    public function getAttributesListById($attibuteSetId)
    {
        try
        {
            if(isset($attibuteSetId))
            {
                $result = DB::table('attributes')
                        ->join('attribute_set_mapping', 'attribute_set_mapping.attribute_id', '=', 'attributes.attribute_id')
                        ->where('attribute_set_mapping.attribute_set_id', $attibuteSetId)
                        ->get(array('attributes.attribute_id', 'attributes.attribute_code', 'attributes.input_type'));
                return $result;
            }else{
                return 'No Attribute group Id';
            }
        } catch (\ErrorException $ex) {
            return $ex->getMessage();
        }
    }
    
  public function getAllAttributes($manufacturerId)
    { 
        try
        {
            $allowAddAttributeSet = $this->roleRepo->checkPermissionByFeatureCode('ATTG002');
            $allowEditAttributeSet = $this->roleRepo->checkPermissionByFeatureCode('ATTG003');
            $allowDeleteAttributeSet = $this->roleRepo->checkPermissionByFeatureCode('ATTG004');
            $allowAssignAttributeSet = $this->roleRepo->checkPermissionByFeatureCode('ATTG005');
            $allowAddAttribute = $this->roleRepo->checkPermissionByFeatureCode('ATT002');
            $allowEditAttribute = $this->roleRepo->checkPermissionByFeatureCode('ATT003');
            $allowDeleteAttribute = $this->roleRepo->checkPermissionByFeatureCode('ATT004');
            $ag1 = DB::table('attribute_sets')
                    ->Join('eseal_customer', 'attribute_sets.manufacturer_id', '=', 'eseal_customer.customer_id')
                    ->join('categories', 'categories.category_id', '=', 'attribute_sets.category_id')
                    ->select('attribute_sets.attribute_set_id', 'attribute_sets.attribute_set_name as attribute_set_name', 'eseal_customer.brand_name', 'categories.name as cname')
                    ->where('attribute_sets.manufacturer_id', $manufacturerId)
                    ->get();
            $agarr = array();
            $finalagarr = array();
            //return $ag1;
            $ags = json_decode(json_encode($ag1), true);

            foreach ($ags as $ag)
            {
                $attr = DB::table('attribute_set_mapping')
                        ->Join('attribute_sets', 'attribute_sets.attribute_set_id', '=', 'attribute_set_mapping.attribute_set_id')
                        ->Join('attributes', 'attributes.attribute_id', '=', 'attribute_set_mapping.attribute_id')
                        ->Join('attributes_groups', 'attributes_groups.attribute_group_id', '=', 'attributes.attribute_group_id')
                        ->select('attributes.attribute_id', 'attributes.name as attribute_name', 'attributes_groups.name as attribute_group_name', 'attributes.text', 'attributes.input_type', 'attribute_set_mapping.attribute_set_id', 'attributes.default_value', 'attributes.is_required', 'attribute_sets.attribute_set_name', 'attribute_sets.attribute_set_id' , 'attribute_set_mapping.is_searchable')
                        ->where('attribute_set_mapping.attribute_set_id', $ag['attribute_set_id'])
                        ->get();
                $atr = array();
                $atrgrp = array();
                $atrjson = json_decode(json_encode($attr), true);
                //return $atrjson;
                if(!empty($atrjson))
                {
                    foreach ($atrjson as $value)
                    {
                        //return $value;
                        $atr['attribute_id'] = $value['attribute_id'];
                        $atr['attribute_set_id'] = $value['attribute_set_id'];
                        $atr['attribute_group_name'] = $value['attribute_group_name'];
                        $atr['attribute_name'] = $value['attribute_name'];
                        $atr['text'] = $value['text'];
                        $atr['input_type'] = $value['input_type'];
                        $atr['default_value'] = $value['default_value'];
                        $atr['is_required'] = $value['is_required'];
                        $atr['aid'] = $value['attribute_set_id'];
                        $atr['actions'] ='';
                        $checkDefaultAttribute=$this->checkDefaultAttribute($value['attribute_id']);
                        if($allowEditAttribute && !$checkDefaultAttribute){
                        $atr['actions'] = $atr['actions'].'<a data-href="/product/editattribute/' .$this->roleRepo->encodeData($value['attribute_id']) . '/' .$value['attribute_set_id'] . '" data-toggle="modal" data-target="#basicvalCodeModal1"><span class="badge bg-light-blue"><i class="fa fa-pencil"></i></span></a><span style="padding-left:10px;" ></span>';
                        }
                        if($allowDeleteAttribute){
                        $atr['actions'] =$atr['actions'].'<a onclick = "delAttributeFromGroup(' ."'".$this->roleRepo->encodeData($value['attribute_id'])."'". ',' .$value['attribute_set_id'] . ')"><span class="badge bg-red"><i class="fa fa-trash-o"></i></span></a><span style="padding-left:20px;" ></span>';
                        }
                        if($value['is_searchable'] == 0){
                        $atr['actions'] =$atr['actions'].'<a onclick = "switchAttributeSearchable(' ."'".$this->roleRepo->encodeData($value['attribute_id'])."'". ',' ."'".$this->roleRepo->encodeData($value['attribute_set_id'])."'". ',1)"><span class="badge bg-green"><i class="fa fa-search-plus"></i></span></a><span style="padding-left:20px;" ></span>';
                        }
                        if($value['is_searchable'] == 1){
                        $atr['actions'] =$atr['actions'].'<a onclick = "switchAttributeSearchable(' ."'".$this->roleRepo->encodeData($value['attribute_id'])."'". ',' ."'".$this->roleRepo->encodeData($value['attribute_set_id'])."'". ',0)"><span class="badge bg-red"><i class="fa fa-search-minus"></i></span></a><span style="padding-left:20px;" ></span>';    
                        }
                        $atrgrp[] = $atr;
                    }
                }
                    $agarr['attribute_set_name'] = $ag['attribute_set_name'];
                    $agarr['attribute_set_id'] =$ag['attribute_set_id'];
                    $agarr['category_id'] = $ag['cname'];
                    $agarr['manufacturer_id'] = $ag['brand_name'];
                    $agarr['actions'] = '';
                    if($allowAddAttribute){
                    $agarr['actions'] = $agarr['actions'].'<a data-href="product/saveAttributeGroup/" data-toggle="modal" onclick="getAttributeGroupName('.$ag['attribute_set_id'].');" data-target="#basicvalCodeModal"><span class="badge bg-green"><i class="fa fa-plus"></i></span></a><span style="padding-left:10px;" ></span>';
                    }              
                    if($allowEditAttributeSet){
                    $agarr['actions'] = $agarr['actions'].'<a data-href="/product/editattributeset/' .$this->roleRepo->encodeData($ag['attribute_set_id']). '" data-attributeId="'.$this->roleRepo->encodeData($ag['attribute_set_id']).'" data-toggle="modal" data-target="#editAttributeSet"><span class="badge bg-light-blue"><i class="fa fa-pencil"></i></span></a><span style="padding-left:10px;" ></span>';
                    }
                    if($allowDeleteAttributeSet){
                    $agarr['actions'] = $agarr['actions'].'<a onclick = "deleteAttrSet('."'".$this->roleRepo->encodeData($ag['attribute_set_id'])."'".')"><span class="badge bg-red"><i class="fa fa-trash-o"></i></span></a><span style="padding-left:10px;" ></span>';
                    }
                    if($allowAssignAttributeSet){
                        $agarr['actions'] = $agarr['actions'].'<a data-href="/product/editattributeset/' .$this->roleRepo->encodeData($ag['attribute_set_id']). '" data-attributeId="'.$ag['attribute_set_id'].'" data-toggle="modal" data-target="#assignAttributeSet" onclick="getAssignAttribute('.$ag['attribute_set_id'].')"><span class="badge bg-light-blue"><i class="fa fa-pencil"></i></span></a><span style="padding-left:10px;" ></span>';
                    }
                    $agarr['children'] = $atrgrp;
                    $finalagarr[] = $agarr;
                
            }
            return json_encode($finalagarr);
        } catch (\ErrorException $ex) {
            return json_encode($ex->getMessage());
        }
    }

    public function saveAttribute($data)
    {
        try
        {
            if(!empty($data))
                {
                //validator
                     $validator = \Validator::make(
                                    array(
                                'name' => isset($data['name']) ? $data['name'] : '',
                                'attribute_set_id' => isset($data['attribute_set_id']) ? $data['attribute_set_id'] : '',
                                'attribute_group_id' => isset($data['attribute_group_id']) ? $data['attribute_group_id'] : '',
                                'attribute_type' => isset($data['attribute_type']) ? $data['attribute_type'] : ''
                                    ), array(
                                'name' => 'required',
                                'attribute_set_id' => 'required',
                                'attribute_group_id' => 'required',
                                'attribute_type' => 'required'
                                    ));
                    if($validator->fails())
                    {
                        //$data = $this->_product->getProductFields($this->_manufacturerId);
                        $errorMessages = json_decode($validator->messages());
                        $errorMessage = '';
                        if(!empty($errorMessages))
                        {
                            foreach($errorMessages as $field => $message)
                            {
                                $errorMessage = implode(',', $message);
                            }
                        }
                        //return Response::back()->withErrors([$errorMessage]);
                        return Response::json([
                                'status' => false,
                                'message' => $errorMessage
                    ]);
                    }
                //validator
                    $startTime = $this->getTime();
                if(isset($data['option_values']))
                {
                    $option_values=$data['option_values'];
                }else{
                    $option_values=0;
                }
                $attributeName = isset($data['name']) ? $data['name'] : '';
                if($attributeName == '')
                {
                    $attributeName = isset($data['attributeFields']['name']) ? $data['attributeFields']['name'] : '';
                    $data = isset($data['attributeFields']) ? $data['attributeFields'] : array();
                }
                if($attributeName != '')
                {
                    $attribute_set_id = $data['attribute_set_id'];
                    unset($data['_method']);
                    unset($data['_token']);
                    unset($data['attribute_set_id']);
                    unset($data['option_values']);
                    
                    //$data['attribute_code'] = str_replace(' ', '_', strtolower($data['name']));
                    $checkForDefaultAttribute=$this->checkForDefaultAttribute($data['name']);
                    $checkForMfgCode=$this->checkForAttributes($data['attribute_code']);
                    if($checkForDefaultAttribute || $checkForMfgCode){
                       return Response::json([
                                'status' => false,
                                'message' => 'Attribute with this Code exists.']); 
                    }
                    unset($data['manufacturer_id']);                    
                    if(!$checkForMfgCode && !$checkForDefaultAttribute){
                    $attribute_id = DB::table('attributes')->insertGetId($data);
                    $sort_order=DB::table('attribute_set_mapping')->where('attribute_set_id',$attribute_set_id)->max('sort_order'); 
                    if(!empty($option_values) && isset($attribute_id)) {
                        $values=json_decode($option_values);
                        foreach ($values as $raw) {
                            $options=explode(';', $raw);
                            //return $options[0];\
                            DB::table('attribute_options')->insert([
                                'attribute_id'=> $attribute_id,
                                'option_name'=>$options[0],
                                'option_value'=>$options[1],
                                'sort_order'=>$options[2]]);
                        }
                    }
                    DB::table('attribute_set_mapping')->insert([
                        'attribute_set_id' => $attribute_set_id,
                        'attribute_id' => $attribute_id,
                        'sort_order' =>  $sort_order+1
                    ]);
                     $endTime = $this->getTime();
                    DB::table('user_tracks')->insert(array('user_id'=>Session::get('userId'),'manufacturer_id'=>Session::get('customerId'),'message'=>'Successfully Saved.','service_name'=>'saveattributes','status'=>1,'response_duration'=>($endTime - $startTime)));
                    return Response::json([
                                'status' => true,
                                'message' => 'Attribute Sucessfully Created.']);
                    }
                }
            }

        } catch (Exception $ex) {
            return Response::json([
                        'status' => false,
                        'message' => $ex->getMessage()
            ]);
        }
    }
    
    public function saveAttributeGroup($data)
    {
        //return $data;
        //return $data['attribute_group']['name'];
        try
        {    
            $startTime = $this->getTime();
            if(!empty($data) && isset($data['attribute_group']))
            {   
                //validator
                     $validator = \Validator::make(
                                    array(
                                'name' => isset($data['attribute_group']['name']) ? $data['attribute_group']['name'] : '',
                                'category_id' => isset($data['attribute_group']['category_id']) ? $data['attribute_group']['category_id'] : '',
                                'manufacturer_id' => isset($data['attribute_group']['manufacturer_id']) ? $data['attribute_group']['manufacturer_id'] : ''
                                    ), array(
                                'name' => 'required',
                                'category_id' => 'required',
                                'manufacturer_id' => 'required'
                                    ));
                    if($validator->fails())
                    {
                        //$data = $this->_product->getProductFields($this->_manufacturerId);
                        $errorMessages = json_decode($validator->messages());
                        $errorMessage = '';
                        if(!empty($errorMessages))
                        {
                            foreach($errorMessages as $field => $message)
                            {
                                $errorMessage = implode(',', $message);
                            }
                        }
                        return Response::json([
                                'status' => false,
                                'message' => $errorMessage
                    ]);
                    }
                //validator

                $checkGroup = $this->checkIfAttributeSetGroupExists($data['attribute_group']['manufacturer_id'],$data['attribute_group']['name'],'');
                if(!$checkGroup){
                DB::table('attributes_groups')->insert($data['attribute_group']);
                $endTime = $this->getTime();
                DB::table('user_tracks')->insert(array('user_id'=>Session::get('userId'),'manufacturer_id'=>Session::get('customerId'),'message'=>'Successfully saved.','service_name'=>'saved attributeGroup','status'=>1,'response_duration'=>($endTime - $startTime)));
                return Response::json([
                            'status' => true,
                            'message' => 'Sucessfully Created.'
                ]);
                }
                return Response::json([
                        'status' => false,
                        'message' => 'Attribute group already exists with this name'
            ]);
            }
            return Response::json([
                        'status' => false,
                        'message' => 'Unable to save Attribute group'
            ]);
        } catch (Exception $ex) {
            return Response::json([
                        'status' => false,
                        'message' => $ex->getMessage()
            ]);
        }        
    }
    
    public function saveAttributeSet($data)
    {
        //return $data;
        try
        {
                    $startTime = $this->getTime();
            if(!empty($data) && isset($data['attribute_set']))
            {
                //validator
                $validator = \Validator::make(
                                    array(
                                'attribute_set_name' => isset($data['attribute_set']['attribute_set_name']) ? $data['attribute_set']['attribute_set_name'] : '',
                                'category_id' => isset($data['attribute_set']['category_id']) ? $data['attribute_set']['category_id'] : '',
                                'manufacturer_id' => isset($data['attribute_set']['manufacturer_id']) ? $data['attribute_set']['manufacturer_id'] : ''
                                    ), array(
                                'attribute_set_name' => 'required',
                                'category_id' => 'required',
                                'manufacturer_id' => 'required'
                                    ));
                    if($validator->fails())
                    {
                        //$data = $this->_product->getProductFields($this->_manufacturerId);
                        $errorMessages = json_decode($validator->messages());
                        $errorMessage = '';
                        if(!empty($errorMessages))
                        {
                            foreach($errorMessages as $field => $message)
                            {
                                $errorMessage = implode(',', $message);
                            }
                        }
                        //return Response::back()->withErrors([$errorMessage]);
                        return Response::json([
                                'status' => false,
                                'message' => $errorMessage
                    ]);
                    }
                //validator
                $inheritFrom = (int) isset($data['attribute_set']['inherit_from']) ? $data['attribute_set']['inherit_from'] : 0;
                unset($data['attribute_set']['inherit_from']);
                $attributeSetName = isset($data['attribute_set']['attribute_set_name']) ? $data['attribute_set']['attribute_set_name'] : '';
                $attributeSetId = 0;
                $manufacturerId = isset($data['attribute_set']['manufacturer_id']) ? $data['attribute_set']['manufacturer_id']: 0;
                $attributeIds = isset($data['attribute_set']['attribute_id']) ? $data['attribute_set']['attribute_id']: 0;
                unset($data['attribute_set']['attribute_id']);
                if($attributeSetName != '')
                {
                    $checkIfAttributeSetCreated = $this->checkIfAttributesSetCreated($attributeSetName, $manufacturerId);
                    if(!$checkIfAttributeSetCreated)
                    {
                        $attributeSetId = DB::table('attribute_sets')->insertGetId($data['attribute_set']);
                    }else{
                        //$attributeSetId = $checkIfAttributeSetCreated;
                        return Response::json([
                                'status' => false,
                                'message' => 'Attribute set with this name exists.'
                    ]);
                    }                    
                }                
                
                if ($manufacturerId && $attributeSetId)
                {
                    //$hasGroupCreated = $this->checkIfGroupsCreated($manufacturerId);
                    //if($hasGroupCreated)
                    //{
                        //DB::statement('INSERT INTO attributes_groups (name, manufacturer_id, category_id) (SELECT name, ' . $manufacturerId . ', category_id FROM attributes_groups WHERE manufacturer_id = 0)');
                    //}
                    
                    $hasAttributesCreated = $this->checkIfAttributesCreated($attributeSetId, $manufacturerId, $attributeIds);
                    //if($hasAttributesCreated)
                    //{
                        //$this->insertNewAttributes($manufacturerId, $attributeSetId);
                    //}
                }
               
                        $endTime = $this->getTime();
                DB::table('user_tracks')->insert(array('user_id'=>Session::get('userId'),'manufacturer_id'=>Session::get('customerId'),'message'=>'Successfully Saved.'.json_encode($data),'service_name'=>'Save attributeset','status'=>1,'response_duration'=>($endTime - $startTime)));

                //return 'created';
                return Response::json([
                            'status' => true,
                            'message' => 'Sucessfully Created.',
                            'set_name' => $attributeSetName,
                            'set_id' => $attributeSetId,
                            'inherit_from' => $inheritFrom
                ]);
            }
            //return 'hi created';
            return Response::json([
                        'status' => false,
                        'message' => 'Unable to save Attribute set'
            ]);
        } catch (Exception $ex) {
            return Response::json([
                        'status' => false,
                        'message' => $ex->getMessage()
            ]);
        }        
    }
    
    public function checkIfAttributesSetCreated($attributeSetName, $manufacturerId)
    {
        try
        {
            $attributeSetData = DB::table('attribute_sets')->where('manufacturer_id', $manufacturerId)->where('attribute_set_name', $attributeSetName)->value('attribute_set_id');
            if(!empty($attributeSetData))
            {
                return $attributeSetData;
            }else{
                return 0;
            }
        } catch (Exception $exc)
        {
            echo $exc->getTraceAsString();
        }
    }

    public function checkForAttributes($attributeCode)
    {
        try
        {
            if(!empty($attributeCode))
            {
              $checkAttribute = DB::table('attributes')->where('attribute_code',$attributeCode)->get();
              if(!empty($checkAttribute))
                {
                    return $checkAttribute;
                }else{
                    return 0;
                }
            }
      
        } catch (Exception $exc)
        {
            echo $exc->getTraceAsString();
        }
    }    
    //Combined Function
    //check attribute groups and sets
    public function checkIfAttributeSetGroupExists($manufacturerId,$attributeGroupName=null,$attributeSetName=null)
    {
        try
        {
            if(!empty($manufacturerId)&&!empty($attributeSetName)&&empty($attributeGroupName))
            {
               $attribute = DB::table('attribute_sets')
                    ->where('attribute_sets.attribute_set_name',$attributeSetName)
                    ->where('attribute_sets.manufacturer_id',$manufacturerId)
                    ->get();
                    if(!empty($attribute))
                    {
                        return $attribute;
                    }else{
                        return 0;
                    }
            }
            if(!empty($manufacturerId)&&!empty($attributeGroupName)&&empty($attributeSetName))
            {
               $attribute = DB::table('attributes_groups')
                    ->where('attributes_groups.name',$attributeGroupName)
                    ->whereIn('attributes_groups.manufacturer_id',array($manufacturerId,0))
                    ->get();
                    if(!empty($attribute))
                    {
                        return $attribute;
                    }else{
                        return 0;
                    }
            }
            
        } catch (Exception $exc)
        {
            echo $exc->getTraceAsString();
        }
    }   
    //check attribute groups and sets
    public function insertNewAttributes($manufacturerId, $attributeSetId)
    {
        try
        {
            /*DB::statement('INSERT INTO attributes (attribute_code, name, text, input_type, default_value, is_required, validation,  `regexp`,  lookup_id,  attribute_group_id,  attribute_type, is_inherited) '
                    . '(select attr.attribute_code, attr.name, attr.text, attr.input_type, attr.default_value, attr.is_required, attr.validation,  attr.`regexp`,  attr.lookup_id,  attr.attribute_group_id,  attr.attribute_type, attr.is_inherited '
                    . 'from attribute_sets as aset '
                    . 'join attribute_set_mapping as map on map.attribute_set_id = aset.attribute_set_id '
                    . 'join attributes as attr on attr.attribute_id = map.attribute_id 
             * join attributes_groups as groups on groups.attribute_group_id = attr.attribute_group_id '
                    . 'where aset.attribute_set_name = "Default")');*/
            $getDefaultAttributes = DB::table('attribute_sets as aset')
                    ->join('attribute_set_mapping as map', 'map.attribute_set_id', '=', 'aset.attribute_set_id')
                    ->join('attributes as attr', 'attr.attribute_id', '=', 'map.attribute_id')
                    ->join('attributes_groups as groups', 'groups.attribute_group_id', '=', 'attr.attribute_group_id')
                    ->where('aset.attribute_set_name', 'Default')
                    ->get(array('attr.attribute_code', 'attr.name', 'attr.text', 'attr.input_type', 'attr.default_value', 'attr.is_required', 'attr.validation',  'attr.regexp',  'attr.lookup_id',  'attr.attribute_group_id',  'attr.attribute_type', 'attr.is_inherited'));
            
            foreach($getDefaultAttributes as $attribute)
            {
                $attributeGroupId = $attribute->attribute_group_id;
                $mfgGroupId = $this->getAttributeGroupId($attributeGroupId, $manufacturerId);
                $attributes = (array) $attribute;
                unset($attributes['attribute_group_id']);
                $attributes['attribute_group_id'] = $mfgGroupId;
                $attributeId = DB::table('attributes')->insertGetId($attributes);
                $attributeSetMappingData = array();
                $attributeSetMappingData['attribute_set_id'] = $attributeSetId;
                $attributeSetMappingData['attribute_id'] = $attributeId;
                $attributeSetMapId = DB::table('attribute_set_mapping')->insertGetId($attributeSetMappingData);            
            }
            $this->updateAttributeGroups($attributeSetId);
        } catch (\ErrorException $ex)
        {
            return $ex->getMessage();
        }
    }
    
    public function getAttributeGroupId($attributeGroupId, $manufacturerId)
    {
        try
        {
            $attributeManufacturerId = DB::table('attributes_groups as attr_group')
                    ->join('attributes_groups as attr_group1', 'attr_group1.name', '=', 'attr_group.name')
                    ->where('attr_group.manufacturer_id', $manufacturerId)
                    ->where('attr_group1.attribute_group_id', $attributeGroupId)
                    ->first(array('attr_group.attribute_group_id'));
            if(!empty($attributeManufacturerId))
            {
                return $attributeManufacturerId->attribute_group_id;
            }else{
                return $attributeGroupId;
            }
        } catch (\ErrorException $ex)
        {
            return $ex->getMessage();
        }
    }
    
    public function updateAttributeGroups($attributeSetId)
    {
        try
        {
            $attributeGroups = DB::table('attributes_groups as attr_group')
                    ->join('attributes_groups as attr_group1', 'attr_group1.name', '=', 'attr_group.name')
                    ->where('attr_group.manufacturer_id', $attributeSetId)
                    ->where('attr_group1.manufacturer_id', 0)
                    ->get(array('attr_group.attribute_group_id as mfg_group_id', 'attr_group1.attribute_group_id as default_id'));
            foreach($attributeGroups as $attributeGroup)
            {
                $attributeGroupData['attribute_group_id'] = $attributeGroup->mfg_group_id;
                DB::table('attributes as attr')
                        ->join('attribute_set_mapping as map', 'map.attribute_id', '=', 'attr.attribute_id')
                        ->where('attr.attribute_group_id', $attributeGroup->default_id)
                        ->where('map.attribute_set_id', $attributeSetId)
                        ->update($attributeGroupData);
            }
        } catch (\ErrorException $ex) {
            return $ex->getMessage();
        }
    }
    
    public function checkIfGroupsCreated($manufacturerId)
    {
        try
        {
            $groupData = DB::table('attributes_groups')->where('manufacturer_id', $manufacturerId)->get('name');
            $defaultDroupData = DB::table('attributes_groups')->where('manufacturer_id', 0)->get();            
            
            if(empty($groupData))
            {
                return 1;
            }else{
                return 0;
            }
        } catch (\ErrorException $ex) {
            return $ex->getMessage();
        }
    }
    
    public function checkIfAttributesCreated($attributeSetId, $manufacturerId, $attributeIds)
    {
        try
        {
            $attributeList = DB::table('attribute_sets')
            ->join('attribute_set_mapping', 'attribute_set_mapping.attribute_set_id', '=', 'attribute_sets.attribute_set_id')
            ->where('attribute_sets.manufacturer_id', $manufacturerId)
            ->where('attribute_sets.attribute_set_id', 1)
            ->value(DB::raw('group_concat(attribute_set_mapping.attribute_id)'));
            $tempArray = array();
            $InsertArray = array();
            if(!empty($attributeList))
            {
                $array = explode(',', $attributeList);
                $attributeIds = array_diff($array, $attributeIds);
            }
            foreach ($attributeIds as $key=>$attr) {
                $tempArray['attribute_id'] = $attr;
                $tempArray['sort_order']=$key;
                $tempArray['attribute_set_id'] = $attributeSetId;
                $InsertArray[] = $tempArray;
            }
            DB::table('attribute_set_mapping')->insert($InsertArray);
            //$groupData = DB::table('attribute_set_mapping')
            //        ->where('attribute_set_id', $attributeSetId)
            //        ->get();
            return 1;
        } catch (\ErrorException $ex) {
            return $ex->getMessage();
        }
    }
    public  function updateattributeset($attribute_set_id,$data){
        //return $attribute_set_id;
        if(!empty($data) && isset($attribute_set_id)){
            //validator
                $validator = \Validator::make(
                                    array(
                                'attribute_set_name' => isset($data['attribute_set_name']) ? $data['attribute_set_name'] : '',
                                'category_id' => isset($data['category_id']) ? $data['category_id'] : '',
                                'manufacturer_id' => isset($data['attribute_set']['manufacturer_id']) ? $data['attribute_set']['manufacturer_id'] : ''
                                    ), array(
                                'attribute_set_name' => 'required',
                                'category_id' => 'required',
                                'manufacturer_id' => 'required'
                                    ));
                    if($validator->fails())
                    {
                        //$data = $this->_product->getProductFields($this->_manufacturerId);
                        $errorMessages = json_decode($validator->messages());
                        $errorMessage = '';
                        if(!empty($errorMessages))
                        {
                            foreach($errorMessages as $field => $message)
                            {
                                $errorMessage = implode(',', $message);
                            }
                        }
                        //return Response::back()->withErrors([$errorMessage]);
                        return Response::json([
                                'status' => false,
                                'message' => $errorMessage
                    ]);
                    }
                //validator
             $checkIfAttributeSetCreated = $this->checkIfAttributesSetCreated($data['attribute_set_name'], $data['attribute_set']['manufacturer_id']);
             //return $checkIfAttributeSetCreated;
            if($checkIfAttributeSetCreated && $checkIfAttributeSetCreated!=$data['attribute_set_id'])
            {
                return Response::json([
                        'status' => false,
                        'message' => 'Attribute set with this name exists.'
            ]);
            } 
            if(!$checkIfAttributeSetCreated || ($checkIfAttributeSetCreated && $checkIfAttributeSetCreated==$data['attribute_set_id'])){
            //return 'Hi';
            DB::table('attribute_sets')
                    ->where('attribute_set_id', $attribute_set_id)
                    ->update(array(
                      'attribute_set_name' =>$data['attribute_set_name'],
                      'category_id' => $data['category_id']));
            $FormdAttr=$data['formattributes'];
            $setAttributes=DB::table('attribute_set_mapping')
                   ->join('attributes','attribute_set_mapping.attribute_id','=','attributes.attribute_id')
                   ->where('attribute_set_mapping.attribute_set_id','=',$attribute_set_id)
                   ->value(DB::raw('group_concat(attribute_set_mapping.attribute_id)'));
            $setAttributes=explode(',',$setAttributes);
            $formdAttributes=explode(',',$FormdAttr);        
            foreach($setAttributes as $key=>$setAttribute){
                if(!in_array($setAttribute,$formdAttributes)){
                    DB::table('attribute_set_mapping')
                    ->where('attribute_set_id',$attribute_set_id)
                    ->where('attribute_id',$setAttribute)
                    ->delete();
                }
            }
            //unset($formdAttributes[0]);
            foreach($formdAttributes as $key=>$formdAttribute){
                if(in_array($formdAttribute,$setAttributes)){
                    DB::table('attribute_set_mapping')
                    ->where('attribute_set_id',$attribute_set_id)
                    ->where('attribute_id',$formdAttribute)
                    //->update('sort_order',$key);
                    ->update(array('sort_order' => $key));
                }
                if(!in_array($formdAttribute,$setAttributes)){
                    DB::table('attribute_set_mapping')
                    ->insert(['attribute_set_id'=> $attribute_set_id,
                         'attribute_id' => $formdAttribute,
                         'sort_order'=>$key
                        ]);
                }
            }
            unset($data['token']);
             DB::table('user_tracks')->insert(array('user_id'=>Session::get('userId'),'manufacturer_id'=>Session::get('customerId'),'message'=>'Sucessfully Attributeset Updated.'.json_encode($data),'status'=>1,'service_name'=>'Update  for Attributeset'));
            return Response::json([
                'status' => true,
                'message'=>'Sucessfully updated.'
              ]);
            }
            /*else{
                //$attributeSetId = $checkIfAttributeSetCreated;
                return Response::json([
                        'status' => false,
                        'message' => 'Attribute set with this name exists.'
            ]);
            }  */
        }
    }
    public function checkForDefaultAttribute($attributeName)
    {
        try
        {
            $attribute = DB::table('attribute_sets as aset')
                    ->join('attribute_set_mapping as map', 'map.attribute_set_id', '=', 'aset.attribute_set_id')
                    ->join('attributes as attr', 'attr.attribute_id', '=', 'map.attribute_id')
                    //->join('attributes_groups as groups', 'groups.attribute_group_id', '=', 'attr.attribute_group_id')
                    ->where('aset.attribute_set_name', 'Default')
                    ->where('attr.name',$attributeName)
                    ->get();
            
            if(!empty($attribute))
            {
                return $attribute;
            }else{
                return 0;
            }
        } catch (Exception $exc)
        {
            echo $exc->getTraceAsString();
        }
    }
    public function checkDefaultAttribute($attributeId)
    {
        try
        {
            $attribute = DB::table('attribute_sets as aset')
                    ->join('attribute_set_mapping as map', 'map.attribute_set_id', '=', 'aset.attribute_set_id')
                    ->join('attributes as attr', 'attr.attribute_id', '=', 'map.attribute_id')
                    //->join('attributes_groups as groups', 'groups.attribute_group_id', '=', 'attr.attribute_group_id')
                    ->where('aset.attribute_set_name', 'Default')
                    ->where('attr.attribute_id',$attributeId)
                    ->get();
            
            if(!empty($attribute))
            {
                return $attribute;
            }else{
                return 0;
            }
        } catch (Exception $exc)
        {
            echo $exc->getTraceAsString();
        }
    }       
    
    public function assignGroups($data)
    {
        //return $data;
        try
        {
            if(!empty($data))
            {
                $assign_locations = isset($data['assign_locations']) ? $data['assign_locations'] : array();
                $attribute_set_id = isset($data['attribute_set_id']) ? $data['attribute_set_id'] : '';
                if(!empty($assign_locations))
                {
                    $assigned=DB::table('product_attributesets')->where('attribute_set_id',$attribute_set_id)->get(array('location_id','product_group_id'));
                    foreach($assign_locations as $locationData)
                    {
                        $locationAccess = json_decode($locationData);
                        $insertArray['attribute_set_id'] = $attribute_set_id;
                        $insertArray['location_id'] = $locationAccess->location_val;
                        $insertArray['product_group_id'] = $locationAccess->product_group;
                        $id = DB::table('product_attributesets')->where($insertArray)->value('id');
                        if(!$id)
                        {
                            DB::table('product_attributesets')->insert($insertArray);
                        }else{
                            foreach ($assigned as $key => $value) {
                                if($value->product_group_id == $locationAccess->product_group && $value->location_id == $locationAccess->location_val){
                                    unset($assigned[$key]);
                                }
                            }
                        }
                    }
                    //
                    foreach($assigned as $assigneds){
                        DB::table('product_attributesets')->where(array('attribute_set_id'=>$attribute_set_id,'location_id'=>$assigneds->location_id,'product_group_id'=>$assigneds->product_group_id))->delete();
                    }
                    //
                    DB::table('user_tracks')->insert(array('user_id'=>Session::get('userId'),'manufacturer_id'=>Session::get('customerId'),'message'=>'Inserted Locations and Attributes','status'=>1,'service_name'=>'Insert Location and Attributes Page'));
                    return Response::json([
                        'status' => true,
                        'message' => 'Inserted locations attributes and groups.'
                    ]);
                }else{
                    return Response::json([
                        'status' => false,
                        'message' => 'Location and product group data required.'
                    ]);
                }
            }else{
                return Response::json([
                    'status' => false,
                    'message' => 'No data.'
                ]);
            }
        } catch (\ErrorException $ex) {
            return $ex->getMessage();
        }
    }
}
?>