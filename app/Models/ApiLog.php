<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class ApiLog extends Model {
    public $timestamps = false;
	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'api_log';
    protected $primaryKey ='id';
	/**
	 * The attributes excluded from the model's JSON form.
	 *
	 * @var array
	 */
	protected $hidden = array('password', 'remember_token');

}
