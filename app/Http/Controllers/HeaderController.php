<?php 
namespace App\Http\Controllers;
use App\Repositories\RoleRepo;
use App\Repositories\CustomerRepo;
use Illuminate\Http\Request;
use Session;
use DB;
use View;
use Input;
use Validator;
use Redirect;
use Log;
/*
	Description : This controller is used for user crud operations.
	Author      : Venkat Reddy Muthuru
	Date        : May-15-2015
*/

class HeaderController extends BaseController {
    public function __construct(RoleRepo $roleAccess, CustomerRepo $custRepoObj,Request $request) {
            $this->roleAccess = $roleAccess;
            $this->custRepoObj = $custRepoObj;
            $this->_request = $request;
           // echo "test"; exit;
          /*  if(!Session::has('userId')){
                    return Redirect::to('/login');
            }*/
    }
    public function compose($view)
    {
        // $x=DB::table('user_roles as ur')->where('ur.user_id', Session::get('userId'))->get('ur.role_id')->toArray();
         $roleFeatures = $this->getFeaturesByRoleId(Session::get('roleId'));
    	// $roleFeatures = $this->getFeaturesByRoleId($x);
        // echo "<pre/>";
        // print_r($roleFeatures);exit;
        // $roleFeat[]=$roleFeatures;
         $temp = array();
        foreach($roleFeatures as $roleFeature){
            if($roleFeature->parent_id==0)
            {
                $temp[$roleFeature->feature_id] = $roleFeature;
               // $temp[$roleFeature->parent_id]->submenu = array();
            }elseif ($roleFeature->parent_id > 0) {
                $temp[$roleFeature->parent_id]->submenus[] = $roleFeature->name.'-'.$roleFeature->url;
            }
        }
        if(!empty($temp)) 
            $roleFeatures = $temp;
        //echo "<pre>"; print_r($roleFeatures); die; 
        $view->with('roleFeatures', $roleFeatures);
    }

}