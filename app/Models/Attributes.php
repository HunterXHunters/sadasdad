<?php
namespace App\Models;
use DB;
use Illuminate\Database\Eloquent\Model;
class Attributes extends Model {

//class Attributes extends \Eloquent {

    protected $table = 'attributes'; // table name
    protected $primaryKey = 'attribute_id';
    public $timestamps = false;

    
    public function insertTrack($srcLocId, $destLocId =0, $transitId, $updateTime)
    {
    	DB::table($this->table)->insert(Array(
    		'src_loc_id' => $srcLocId, 'dest_loc_id' => $destLocId, 'transition_id' => $transitId, 'update_time' => $updateTime
    		));
        return DB::getPdo()->lastInsertId();
    }


    public function getAttributeIdForCodes($attributeCodesArray){
        $attributeIds = DB::table($this->table)
        ->whereIn('attribute_code', $attributeCodesArray)
        ->select('attribute_id', 'attribute_code')
        ->get()->toArray();
        if(count($attributeIds))
            return $attributeIds;
        else
            return false;
    }



}
