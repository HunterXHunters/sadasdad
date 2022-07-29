<?php

// use Illuminate\Auth\UserTrait;
// use Illuminate\Auth\UserInterface;
// use Illuminate\Auth\Reminders\RemindableTrait;
// use Illuminate\Auth\Reminders\RemindableInterface;
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class BusinessUnit extends Model {
//Eloquent implements UserInterface, RemindableInterface {

	//use UserTrait, RemindableTrait;
    public $timestamps = false;
	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'business_units';

	/**
	 * The attributes excluded from the model's JSON form.
	 *
	 * @var array
	 */
	protected $hidden = array('password', 'remember_token');
     
    protected $fillable = array('business_unit_id','name','manufacturer_id','is_active','description');

}
