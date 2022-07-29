<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use DB;
class Location extends Model {

//class Location extends Eloquent implements UserInterface, RemindableInterface {

	public $timestamps = false;
	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'locations';
    protected $primaryKey ='location_id';
	/**
	 * The attributes excluded from the model's JSON form.
	 *
	 * @var array
	 */
	protected $hidden = array('password', 'remember_token');
     
    protected $fillable = array('location_id','location_name','manufacturer_id','location_type_id','location_email','location_address','location_details','state','region','longitude','latitude','erp_code','city','country','pincode');
	
    
    public function getMfgIdForLocationId($lid)
    {
        if(!empty($lid) && is_numeric($lid)){
            $mfgId = DB::table($this->table)
                        ->where('location_id', $lid)
                        ->value('manufacturer_id');
            return $mfgId;
        }else{
            return false;
        }
    }

    public function getParentIdForLocationId($lid)
    {
        if(!empty($lid) && is_numeric($lid)){
            $parentId = DB::table($this->table)
                        ->where('location_id', $lid)
                        ->value('parent_location_id');
            return $parentId;
        }else{
            return false;
        }
    }

    public function getAllChildIdForParentId($lid)
    {
        if(!empty($lid) && is_numeric($lid)){
            $childIds = DB::table($this->table)
                        ->where('parent_location_id', $lid)
                        ->pluck('location_id')->toArray();
            return $childIds;
        }else{
            return false;
        }
    }

    public function getDestinationLocationIdFromSAPCode($sapcode)
    {
        if(!empty($sapcode) && isset($sapcode)){
            $locationId = DB::table($this->table)
                        ->where('erp_code', $sapcode)
                        ->value('location_id');
            return $locationId;
        }else{
            return false;
        }
    }

    public function getSAPCodeFromLocationId($locationId)
    {
        if(!empty($locationId) && isset($locationId)){
            $erpCode = DB::table($this->table)
                        ->where('location_id', $locationId)
                        ->value('erp_code');
            return $erpCode;
        }else{
            return false;
        }
    }

    public function createOrReturnLocationId($destDetails, $mfgId)
    {
        $status = 0;
        $message = '';
        $id = 0;
        $locationTypeID = DB::table('location_types')
            ->where('manufacturer_id', $mfgId)
            ->where('location_type_name',$destDetails->Type)
            ->value('location_type_id');
        if($locationTypeID){
            try{
                $id = DB::table($this->table)->insertGetId(
                    Array(
                            'location_name' => $destDetails->name, 'manufacturer_id'=>$mfgId, 'location_type_id'=>$locationTypeID,
                            'location_email'=> $destDetails->email, 'location_address' => $destDetails->address, 'state'=>$destDetails->state, 'erp_code'=>$destDetails->sapcode,
                            'region' => $destDetails->region, 'longitude'=>$destDetails->longitude, 'latitude'=>$destDetails->latitude
                        )
                );
                $status = 1;
                $message = 'Location created succesfully';
            }catch(PDOException $e){
                $status = 0;
                $message = 'Error during location creation';
            }
        }else{
            $message = 'Invalid location type';
        }
        return Array('Status'=>$status, 'Message'=>$message, 'Id'=>$id);
    }


    public function getAllLocationsForMfgId($mfgId){
        if(!empty($mfgId) && is_numeric($mfgId)){
            $locations = DB::table($this->table)->select('location_id')
                            ->where('manufacturer_id', $mfgId)
                            ->get()->toArray();
            return $locations;
        }else{
            return FALSE;
        }
    }

    public function getStorageLocationIdForMissing($mfgId){
        $locationTypeID = DB::table('location_types')->where('manufacturer_id', $mfgId)->where('location_type_name', 'Storage Location ')->value('location_type_id');
        if($locationTypeID){
            $locationId = DB::table('locations')->where('manufacturer_id', $mfgId)->where('location_name', 'Missing Material')
                ->where('location_type_id', $locationTypeID)->value('location_id');
            if($locationId){
                return $locationId;
            }else{
                return false;
            }
        }else{
            return false;
        }

    }

    public function getStorageLocationIdForDamage($mfgId, $locationId){
        $locationTypeID = DB::table('location_types')->where('manufacturer_id', $mfgId)->where('location_type_name', 'Storage Location ')->value('location_type_id');
        if($locationTypeID){
            $locationId = DB::table('locations')->where('manufacturer_id', $mfgId)->where('parent_location_id', $locationId)
                ->where('location_type_id', $locationTypeID)->where('location_name', 'Block Material')->value('location_id');
            if($locationId){
                return $locationId;
            }else{
                return false;
            }
        }else{
            return false;
        }

    }

    public function getStockoutLocation($mfgId){
        $locationTypeID = DB::table('location_types')->where('manufacturer_id', $mfgId)->where('location_type_name', 'Stockout')->value('location_type_id');
        if($locationTypeID){
            $locationId = DB::table('locations')->where('manufacturer_id', $mfgId)
                ->where('location_type_id', $locationTypeID)
                ->where('location_name', 'Stockout')->value('location_id');
            if($locationId){
                return $locationId;
            }else{
                return false;
            }
        }else{
            return false;
        }
    }    
}
