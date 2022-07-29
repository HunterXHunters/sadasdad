<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use DB;
class Track extends Model {
//class Track extends Eloquent implements UserInterface, RemindableInterface {

    public $timestamps = false;
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'track_details';
    protected $primaryKey ='id';
    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = array('password', 'remember_token');
     
    protected $fillable = array('code','track_id');

}
