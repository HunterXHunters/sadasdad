<?php

Event::listen('scoapi/BindEseals', function($obj){
	$attributeMapId = Input::get('attribute_map_id');
	$codes = Input::get('ids');
	$codesArray = explode(',', $codes);
	$attributeCodesArray= array('batch_no', 'date_mfg', 'mrp', 'pkg_qty', 'exp_date');

	$attributesObj = new Attributes\Attributes();
	$attributeIds = $attributesObj->getAttributeIdForCodes($attributeCodesArray);

	$mfgId = '';
	$esealTable = '';
	
	$locationId = DB::table('attribute_mapping')->where('attribute_map_id', $attributeMapId)->pluck('location_id');
	Log::info('LocationId=============='.$locationId);
	$locationObj = new Locations\Locations();

	$mfgId = $locationObj->getMfgIdForLocationId($locationId);
	
	$esealTable = 'eseal_'.$mfgId;
	Log::info('=============='.$esealTable);

	$attMappingObj = new AttributeMapping\AttributeMapping();
	foreach($attributeIds as $val){

		$value = $attMappingObj->getValueForMappingId($attributeMapId, $val->attribute_id);
		//Log::info($value);
		if(!empty($esealTable) && !empty($value)){
			if($val->attribute_code == 'batch_no'){
				$attMappingObj->updateEsealAttributes($esealTable, $attributeMapId, $codesArray, $val->attribute_code, $value[0]->value);
			}
			if($val->attribute_code == 'date_mfg'){
				$attMappingObj->updateEsealAttributes($esealTable, $attributeMapId, $codesArray,'mfg_date', $value[0]->value);
			}
			if($val->attribute_code == 'mrp'){
				$attMappingObj->updateEsealAttributes($esealTable, $attributeMapId, $codesArray, $val->attribute_code, $value[0]->value);
			}
			if($val->attribute_code == 'pkg_qty')
				$attMappingObj->updateEsealAttributes($esealTable, $attributeMapId, $codesArray, $val->attribute_code, $value[0]->value);
			}
	}

});


Event::listen('scoapi/MapEseals', function($obj){

	log::info(Input::get());
	$childs = trim(Input::get('ids'));
	$parent = trim(Input::get('parent'));
	$codesArray = explode(',', $parent);
	$locationId = trim(Input::get('srcLocationId'));

	$attributeCodesArray= array('mrp', 'pkg_qty', 'exp_date');

	$attributesObj = new Attributes\Attributes();
	$attributeIds = $attributesObj->getAttributeIdForCodes($attributeCodesArray);

	$mfgId = '';
	$esealTable = '';
	
	$locationId = trim(Input::get('srcLocationId'));
	Log::info('LocationId=============='.$locationId);
	$locationObj = new Locations\Locations();

	$mfgId = $locationObj->getMfgIdForLocationId($locationId);
	
	$esealTable = 'eseal_'.$mfgId;
	Log::info('=============='.$esealTable);

	$attributeMapId = DB::table($esealTable)->where('primary_id', $parent)->pluck('attribute_map_id');

	$attMappingObj = new AttributeMapping\AttributeMapping();
	foreach($attributeIds as $val){

		$value = $attMappingObj->getValueForMappingId($attributeMapId, $val->attribute_id);
		//Log::info($value);
		if(!empty($esealTable) && !empty($value)){
			if($val->attribute_code == 'batch_no'){
				$attMappingObj->updateEsealAttributes($esealTable, $attributeMapId, $codesArray, $val->attribute_code, $value[0]->value);
			}
			if($val->attribute_code == 'mfg_date'){
				$attMappingObj->updateEsealAttributes($esealTable, $attributeMapId, $codesArray, $val->attribute_code, $value[0]->value);
			}
			if($val->attribute_code == 'mrp'){
				$attMappingObj->updateEsealAttributes($esealTable, $attributeMapId, $codesArray, $val->attribute_code, $value[0]->value);
			}
			if($val->attribute_code == 'pkg_qty')
				$attMappingObj->updateEsealAttributes($esealTable, $attributeMapId, $codesArray, $val->attribute_code, $value[0]->value);			}
	}

});


?>
