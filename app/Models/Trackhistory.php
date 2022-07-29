<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use DB;
class Trackhistory extends Model {
	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'track_history';

	/**
	 * The attributes excluded from the model's JSON form.
	 *
	 * @var array
	 */
	protected $hidden = array('password', 'remember_token');
	
    public function insertTrack($srcLocId, $destLocId =0, $transitId, $updateTime)
    {
    	DB::table($this->table)->insert(Array(
    		'src_loc_id' => $srcLocId, 'dest_loc_id' => $destLocId, 'transition_id' => $transitId, 'update_time' => $updateTime
    		));
        return DB::getPdo()->lastInsertId();
    }

}
