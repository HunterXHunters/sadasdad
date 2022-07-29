<?php

namespace App\Listeners;

use App\Events\scoapi_MapEseals;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Models\Attributes;
use App\Models\Location;
use App\Models\AttributeMapping;
use DB;
class list_scoapi_MapEseals
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public $list_request;
    public function __construct()
    {
        //
       // $this->list_request=$request;
    }

    /**
     * Handle the event.
     *
     * @param  scoapi_MapEseals  $event
     * @return void
     */
    public function handle(scoapi_MapEseals $event)
    {
            $req=$event->inputData;
            
            $childs = trim($req['ids']);
            $parent = trim($req['parent']);
            $codesArray = explode(',', $parent);
            $locationId = trim($req['srcLocationId']);

            $attributeCodesArray= array('mrp', 'pkg_qty', 'exp_date');
            $attributesObj = new Attributes();
            $attributeIds = $attributesObj->getAttributeIdForCodes($attributeCodesArray);

            $mfgId = '';
            $esealTable = '';
            
            $locationId = trim($req['srcLocationId']);
            $locationObj = new Location();

            $mfgId = $locationObj->getMfgIdForLocationId($locationId);
            
            $esealTable = 'eseal_'.$mfgId;
           
            $attributeMapId = DB::table($esealTable)->where('primary_id', $parent)->value('attribute_map_id');

            $attMappingObj = new AttributeMapping();
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
                        $attMappingObj->updateEsealAttributes($esealTable, $attributeMapId, $codesArray, $val->attribute_code, $value[0]->value);           
                }
            }


    }
}
