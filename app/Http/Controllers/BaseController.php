<?php

namespace App\Http\Controllers;
use App\Repositories\RoleRepo;
use App\Repositories\CustomerRepo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Session;
use DB;
use View;
use Input;
use Validator;
use Redirect;
use Log;
class BaseController extends Controller {

	/**
	 * Setup the layout used by the controller.
	 *
	 * @return void
	 */
        /*public  $roleAccess;
        public function __construct() {
            
        }
        
        public function checkPermission()
        {
            $contName = Request::segment(1); 
            $methodName = Request::segment(2);
            
            
        }*/
     public function Breadcrumbs($breadCrumbs)
     { 
        if(!empty($breadCrumbs))
        {
            $str = '';
             $str .='<li><a href="javascript:void(0)" ><i class="glyphicon glyphicon-home"></i></a></li>';
            foreach ($breadCrumbs as $key=>$breadCrumb): 
                if($breadCrumb!='#'){
                     $str .='<li><a href="'.URL::asset($breadCrumb).'" >'.$key.'</a></li>';
                }else{
                    $str .='<li><a class="active">'.$key.'</a></li>';
                }    
            endforeach;
        }
         return View::share('breadcrumd', $str); 
         
     }
     public function getFeaturesByRoleId($roleId)
	{
//            $results = DB::table('role_access')
//                     ->select('role_access.role_id','role_access.feature_id','features.name','features.parent_id','features.url','features.icon')
//                     ->leftJoin('features','role_access.feature_id','=','features.feature_id')
//                     ->where(array('role_access.role_id'=>$roleId))
//                     ->groupBy('role_access.feature_id')
//                     ->orderBy('features.sort_order','ASC')
//                     ->get();
//          . "(select group_concat(concat(name,'-',url)) from features where parent_id=role_access.feature_id ) as submenu "             
            if(!empty($roleId)){
                // print_r($roleId);exit;
                // foreach($roleId as $r){
                    // print_r($r);exit;
            $results = DB::select(DB::raw("SELECT role_access.role_id,role_access.feature_id, features.name, features.parent_id, features.url, features.icon "
                     . "FROM role_access left join features on role_access.feature_id=features.feature_id where role_access.role_id =".$roleId." and is_menu=1 and is_active=1 order by features.sort_order ASC"));
          // $results[]=$features;
         // }
    }
                /*$queries = DB::getQueryLog(); 
            }

       $last_query = end($queries);
                echo "<pre>"; print_r($last_query) ; die;    */
             else
                $results = array();
       /* $featuresarr = array();
		$finalarr = array();
		$subfeaturesarr = array();
    	if(!empty($result))
		{	            		
    		foreach($result as $feature)
            {
            	$featuresarr['feature']=$feature->name;
            	$sub_result = DB::table('features')
                ->where(array('parent_id'=>$feature->feature_id))
                ->get();
            	
            	if(!empty($sub_result))
				{	  
					$temp = array();          		
    				foreach($sub_result as $subfeature)
            		{
            			
            			$subfeaturesarr['feature_id']= $subfeature->feature_id;
            			$subfeaturesarr['name']= $subfeature->name;
            			$subfeaturesarr['url']= $subfeature->url;
            			$subfeaturesarr['sort_order']= $subfeature->sort_order;
            			//$subfeaturesarr['image']= $subfeature->image;
            			$temp[] = $subfeaturesarr;
            		}
            		$featuresarr['subfeatures']=$temp;
            	}
            	$finalarr[]=$featuresarr;
            	
			}
		}*/
           // echo "<pre>"; print_r($results); die;
		return $results;
        }
	protected function setupLayout()
	{
		if ( ! is_null($this->layout))
		{
			$this->layout = View::make($this->layout);
		}
	}

}
