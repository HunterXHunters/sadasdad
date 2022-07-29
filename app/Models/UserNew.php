<?php

namespace App\Models;

use Laravel\Passport\HasApiTokens;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\UserNew as Authenticatable;

class UserNew extends Model
{
    use HasApiTokens;

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
}
