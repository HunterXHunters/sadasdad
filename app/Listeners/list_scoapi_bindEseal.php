<?php

namespace App\Listeners;

use App\Events\scoapi_BindEseals;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Models\Attributes;
use App\Models\Location;
use App\Models\AttributeMapping;
use DB;
use Illuminate\Http\Request;

class list_scoapi_bindEseal
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  scoapi_BindEseals  $event
     * @return void
     */
    public function handle(scoapi_BindEseals $event)
    {       

    $req=$event->inputData;
    $attributeMapId = $req['attribute_map_id'];
    $codes = $req['ids'];
    $codesArray = explode(',', $codes);
    $attributeCodesArray= array('batch_no', 'date_mfg', 'mrp', 'pkg_qty', 'exp_date');

    $attributesObj = new Attributes();
    $attributeIds = $attributesObj->getAttributeIdForCodes($attributeCodesArray);

    $mfgId = '';
    $esealTable = '';
    
    $locationId = DB::table('attribute_mapping')->where('attribute_map_id', $attributeMapId)->value('location_id');
    $locationObj = new Location();

    $mfgId = $locationObj->getMfgIdForLocationId($locationId);
    $esealTable = 'eseal_'.$mfgId;

    $attMappingObj = new AttributeMapping();
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
    }
}
