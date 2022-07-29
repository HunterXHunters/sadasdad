<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Redirect;
class Url extends Model {
    public $timestamps = false;
	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'users';

	/**
	 * The attributes excluded from the model's JSON form.
	 *
	 * @var array
	 */
	protected $hidden = array('password', 'remember_token');
        
        protected $primaryKey  = 'user_id';
    protected function previous(){
    	echo"hai";
    }

}
