<?php
namespace App\Models;
use DB;
use Illuminate\Database\Eloquent\Model;
class AttributeMapping extends Model {
/*
namespace AttributeMapping;
use \DB;

class AttributeMapping extends \Eloquent {
*/
    protected $table = 'attribute_mapping'; // table name
    protected $primaryKey = 'id';
    public $timestamps = false;

    
    public function updateEsealAttributes($esealTable, $attributeMapId, $codesArray, $attributeCode, $attributeValue)
    {
        try{
            DB::table($esealTable)->where('attribute_map_id', $attributeMapId)
            ->whereIn('primary_id', $codesArray)
            ->orWhereIn('parent_id', $codesArray)
            ->update(Array($attributeCode=>$attributeValue));

        }catch(PDOException $e){
            Log::info($e->getMessage());
            return false;
        }
        return true;
    }

    public function getValueForMappingId($mapId, $attributeId){
        $value = DB::table($this->table)->where('attribute_map_id', $mapId)
            ->where('attribute_id', $attributeId)
            ->select('value', 'location_id')
            ->get()->toArray();
        if(count($value))
            return $value;
        else
            return false;
    }



}
