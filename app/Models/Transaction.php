<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use DB;
class Transaction extends Model {

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'transaction_master';

	/**
	 * The attributes excluded from the model's JSON form.
	 *
	 * @var array
	 */
	protected $hidden = array('password', 'remember_token');
	public function getTransactionDetails($mfgId, $id)
		{
			$transactionDetails = DB::select('
					SELECT 
						srcLoc_action, dstLoc_action, intrn_action 
					FROM 
						'.$this->table.' 
					WHERE manufacturer_id = '.$mfgId.' and id ='.$id);
			return $transactionDetails;
		}
}
