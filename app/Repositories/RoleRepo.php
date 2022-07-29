<?php 
namespace App\Repositories;     //Name space define 



/* 
 * This is class is used for access role permision based on user and feature
 */

use App\Models\Token;
use App\Models\User;
use DB;  //Include laravel db class
use Session;
class RoleRepo  {    // Define class name is RoleRepo
     protected $_salt;

     public function __construct()
     {
        $this->_salt = 'e$e@1';
     }
     public function getAccess($userId, $featureId)
    {
        $result = DB::table('role_access')
                ->join('user_roles','role_access.role_id','=','user_roles.role_id')
                ->where('role_access.feature_id',$featureId)
                ->where('user_roles.user_id',$userId)
                ->count();
        return ($result > 0) ? TRUE : FALSE;
    }
    
/*    public function authenticateUser($email, $password){
        $result = DB::table('users as u')->leftJoin('eseal_customer as ec','ec.customer_id','=','u.customer_id')
        ->select('u.user_id','u.username','u.user_type','u.customer_id','u.profile_picture','ec.eseal_erp')
                ->where(array('u.email'=>$email,'u.password'=>  md5($password),'is_active'=>1))
                ->orWhere(array('u.username'=>$email,'u.password'=>  md5($password),'is_active'=>1))
                ->orWhere(array('u.phone_no'=>$email,'u.password'=> md5($password),'is_active'=>1))
                ->get()->toArray();
        return $result;
    }*/
    public function authenticateUser($email, $password){
       DB::connection()->enableQueryLog();
        $result = DB::table('users as u')->leftJoin('eseal_customer as ec','ec.customer_id','=','u.customer_id')
        ->select('u.user_id','u.username','u.user_type','u.customer_id','u.profile_picture','ec.eseal_erp')
                ->where(array('u.email'=>$email,'u.password'=>  md5($password),'is_active'=>1))
                ->orWhere(function($query) use ($email,$password){
                    $query=$query->where(array('u.username'=>$email,'u.password'=>  md5($password),'is_active'=>1));
                })->orWhere(function($query) use ($email,$password){
                    $query=$query->where(array('u.phone_no'=>$email,'u.password'=> md5($password),'is_active'=>1));
                })->orWhere(function($query) use ($email,$password){
                    $query=$query->where(array('u.erp_username'=>$email,'u.password'=> md5($password),'is_active'=>1));
                })->get()->toArray();
          /*       echo "kkkk<pre>";
        $queries = DB::getQueryLog();
        $last_query = end($queries);
        print_r($last_query);
        print_r($result);
       exit;*/
        return $result;
                //return $last_query;
    }
    public function getRolebyUserId($userId)
    {
         $result = DB::table('user_roles')
                 ->join('roles','user_roles.role_id','=','roles.role_id')
                 ->select('user_roles.role_id','roles.parent_role_id','roles.name')                 
                ->where('user_id',$userId)
                ->get()->toArray();
        return $result;
    }
    public function getFeaturesByRoleId($roleId){
        return $result = DB::table('role_access')
                ->select('features.*')
                ->join('features','role_access.feature_id','=','features.feature_id')
                ->where(array('features.is_active'=>1,'features.is_menu'=>1, 'role_access.role_id'=>$roleId))
                ->orderby('features.sort_order','ASC')
                ->get()->toArray();
    }
     private function getTime(){
        $time = microtime();
        $time = explode(' ', $time);
        $time = ($time[1] + $time[0]);
        return $time;
    }
    public function checkActionAccess($userId,$featureCode)
    {        
        $result = DB::table('role_access')
                ->select('features.name')
                ->join('features','role_access.feature_id','=','features.feature_id')
                ->join('user_roles','role_access.role_id','=','user_roles.role_id')
                ->where(array('user_roles.user_id'=>$userId, 'features.feature_code'=>$featureCode))
                ->count();
       
        return ($result > 0) ? TRUE : FALSE;
    }


    public function getUsers($customerId=0)
    {
        return $result = DB::table('users')
                ->select('user_id','username','firstname','lastname','email','is_active','salt','profile_picture')
                ->where(array('customer_id'=>$customerId,'is_active'=>1))
                ->get()->toArray();
    }
    
    public function getUsersList($customerId=0)
    {
        $userId = Session::get('userId');
        return $result = DB::table('users as u')
                ->leftJoin('master_lookup as ml','ml.value','=','u.user_type')
                ->select('u.user_id','u.username','u.salt','u.firstname','u.lastname','u.email','u.is_active','u.profile_picture','u.phone_no','ml.name')
                //->where(array('customer_id'=>$customerId,'created_by'=>$userId))
                ->where(array('customer_id'=>$customerId))
                //->orWhere('user_id',$userId)
                ->get()->toArray();
    }


    public function verifyUser($password, $userId)
    {   

   return  $result = DB::table('users')
                            ->where(array('user_id'=>$userId,'is_active'=>1,'password'=>md5($password)))
                            ->count();
                            //print_r($result);exit;

    }
    
    public function saveUser($data, $userId=0)
    {   

        if($userId > 0)
        {   $startTime = $this->getTime();
            DB::table('users')->where('user_id',$userId)->update($data);
            $endTime = $this->getTime();
            DB::table('user_tracks')->insert(array('user_id'=>Session::get('userId'),'message'=>'Successfully Updated.'.json_encode($data),'status'=>1,'created_on'=>date("Y-m-d H:i:s"),'service_name'=>'Edit User','response_duration'=>($endTime - $startTime)));
        }else{
           $startTime = $this->getTime();
            $userId= DB::table('users')->insertGetId($data);
                        $endTime = $this->getTime();
            $json = json_encode($data);
            DB::table('user_tracks')->insert(['user_id'=>Session::get('userId'),'manufacturer_id'=>$data['customer_id'],
             'service_name'=>'Create New User','status'=>1,'message'=>'Successfully Created.'.$json,'created_on'=>date("Y-m-d H:i:s"),'response_duration'=>($endTime - $startTime)]);
        }
        return $userId;
    }
    
    public function setUserRole($roleId,$userId)
    {
        $Id = $userId= DB::table('user_roles')->insertGetId(array('role_id'=>$roleId, 'user_id'=>$userId));
        
        return $Id;
    }

    public function getModuleFeatures(){
        /*$result= DB::table('master_lookup')
                ->select('master_lookup.name', '(select GROUP_CONCAT(feature_id) from features where master_lookup_id=master_lookup.value) as feature_id' , '(select GROUP_CONCAT(name) from features where master_lookup_id=master_lookup.value) as feature_name')
                ->join('lookup_categories','lookup_categories.id','=','master_lookup.category_id')
                ->where('lookup_categories.name','Modules')
                ->get()->toArray();*/
            $roleId = Session::get('roleId');    
            return DB::select(DB::raw("SELECT master_lookup.id,master_lookup.name, "
                     . "(select GROUP_CONCAT(features.feature_id) from features , role_access where master_lookup_id=master_lookup.value and features.feature_id=role_access.feature_id and role_access.role_id=".$roleId.") as feature_id, "
                     . "(select GROUP_CONCAT(features.name) from features, role_access where master_lookup_id=master_lookup.value  and features.feature_id=role_access.feature_id and  role_access.role_id=".$roleId.") as feature_name, "
                     . "(select GROUP_CONCAT(features.parent_id) from features, role_access where master_lookup_id=master_lookup.value  and features.feature_id=role_access.feature_id and  role_access.role_id=".$roleId.") as parent_id "
                     . "FROM `master_lookup` join lookup_categories on lookup_categories.id=master_lookup.category_id where lookup_categories.name='Modules'"));
        
    }
    
    public function getPermissionModules()
    {
        return DB::table('master_lookup')
                ->select('name','value')
                ->where(array('category_id'=>4,'is_active'=>1))
                ->get()->toArray();
                   
    }
    
    public function getFeatures(){
        return DB::table('features')
                ->where('is_active',1)
                ->orderBy('sort_order','asc')
                ->get()->toArray();
    }
    
    public function getFeatureByParentId($parentId)
    {
        return DB::table('features')
                ->where(array('is_active'=>1,'parent_id'=>$parentId))
                ->get()->toArray();
    }
    
    public function getFeatureswithChilds()
    {
        $result = DB::table('features as F')
                 ->select('F.master_lookup_id','F.feature_id','F.name','F.parent_id','F1.feature_id as childId1',
                         'F1.name as childName1','F1.parent_id as childParent1',
                         'F2.feature_id as childId2','F2.name as childName2','F2.parent_id as childParent2')
                 ->leftJoin('features as F1','F.feature_id','=','F1.parent_id')
                 ->leftJoin('features as F2','F1.feature_id','=','F2.parent_id');
                 
         $result =  $result->where(array('F.is_active'=>1));
        // if(Session::get('roleId')==1)
        //    $result =  $result->where(array('F.is_active'=>1));
        // else{
        //   $result =   $result->join('role_access as RC','F.feature_id','=','RC.feature_id');  
        //   $result =   $result->where(array('F.is_active'=>1,'RC.role_id'=>Session::get('roleId')));
        // }         
        $result = $result->orderBy('F.sort_order','ASC');
        $result = $result->orderBy('F1.feature_id','ASC');
        $result = $result->get()->toArray();

        return  $result;       
    }

    public function getPermissionFeature()
    {
        $modules = $this->getPermissionModules();
        
        $results = array();
        $features = $this->getFeatureswithChilds();
        $i =0;
        //echo "<pre>";        print_r($features); die;
        foreach ($modules as $module):
            $results[$i] = $module;
            $j=0;
           
            foreach ($features as $feature):
                if($module->value==$feature->master_lookup_id){
                    if(!isset($results[$i]->child[$feature->feature_id]) && $feature->parent_id==0){
                        $j=0;
                        $results[$i]->child[$feature->feature_id] = (object) array('feature_id'=>$feature->feature_id,'name'=>$feature->name,'parent_id'=>$feature->parent_id); 
                        
                        if(!empty($feature->childId1))
                        {
                            $k=0;
                            $results[$i]->child[$feature->feature_id]->child[$j] = (object) array('feature_id'=>$feature->childId1,'name'=>$feature->childName1,'parent_id'=>$feature->childParent1);
                        }
                        if(!empty($feature->childId2))
                        {
                            $k=0;
                            $results[$i]->child[$feature->feature_id]->child[$j]->child[$k] = (object) array('feature_id'=>$feature->childId2,'name'=>$feature->childName2,'parent_id'=>$feature->childParent2); 
                        }
                    }elseif(isset($results[$i]->child[$feature->feature_id]->child[$j]) && $feature->childId1 != $results[$i]->child[$feature->feature_id]->child[$j]->feature_id)
                    {
                        $j++;
                        $results[$i]->child[$feature->feature_id]->child[$j] = (object) array('feature_id'=>$feature->childId1,'name'=>$feature->childName1,'parent_id'=>$feature->childParent1);
                        if(!empty($feature->childId2))
                        {
                            $k=0;
                            $results[$i]->child[$feature->feature_id]->child[$j]->child[$k] = (object) array('feature_id'=>$feature->childId2,'name'=>$feature->childName2,'parent_id'=>$feature->childParent2); 
                        }
                    }
                    elseif(isset($results[$i]->child[$feature->feature_id]->child[$j]->child))
                    {
                        $k++;
                        $results[$i]->child[$feature->feature_id]->child[$j]->child[$k] = (object) array('feature_id'=>$feature->childId2,'name'=>$feature->childName2,'parent_id'=>$feature->childParent2);
                    }
                    /*elseif(isset($results[$i]->child[$feature->feature_id]) && !isset($results[$i]->child[$feature->feature_id]->child[$j]->child))
                    {  
                        $j++;
                        $results[$i]->child[$feature->feature_id]->child[$j] = (object) array('feature_id'=>$feature->childId1,'name'=>$feature->childName1,'parent_id'=>$feature->childParent1); 
                        if(!empty($feature->childId2))
                            $results[$i]->child[$feature->feature_id]->child[$j]->child[] = (object) array('feature_id'=>$feature->childId2,'name'=>$feature->childName2,'parent_id'=>$feature->childParent2); 
                    }elseif(isset($results[$i]->child[$feature->feature_id]->child[$j]->child) && !empty($feature->childId2))
                        $results[$i]->child[$feature->feature_id]->child[$j]->child[] = (object) array('feature_id'=>$feature->childId2,'name'=>$feature->childName2,'parent_id'=>$feature->childParent2); 
                       */     
                }
            endforeach;
            $i++; 
        endforeach;
        //echo "<pre>"; print_r($results); die;
        return $results;
    }

    public function SaveRole($data=array(),$role_id=0)
    { //print_r($data); die;
        if(!empty($data)){
            if($role_id==0){
                $insert_array = array();
                $insert_array['name']=isset($data['role_name']) ? $data['role_name'] : '';
                $insert_array['description']=isset($data['description']) ? $data['description'] : '';
                $insert_array['is_active']=isset($data['is_active']) ? $data['is_active'] : 0;
                $insert_array['manufacturer_id']=isset($data['manufacture_id']) ? $data['manufacture_id'] : 0;
                $insert_array['role_type']=isset($data['customer_type']) ? $data['customer_type'] : 0;
                $insert_array['parent_role_id']=Session::get('roleId');
                 $insert_array['created_by']=Session::get('userId');
                $insert_array['created_on']=date('Y-m-d H:i:s');
                $role_id= DB::table('roles')->insertGetId($insert_array);
                if(isset($data['user_id']) && !empty($data['user_id'])) { 
                    foreach ($data['user_id'] as $user_id){
                        DB::table('user_roles')->insert(array('role_id'=>$role_id,'user_id'=>$user_id));
                    }
                }
                if(isset($data['feature_name']) && !empty($data['feature_name'])){
                foreach ($data['feature_name'] as $featureId){
                    DB::table('role_access')->insert(array('role_id'=>$role_id,'feature_id'=>$featureId));
                } 
               }
            }else{
                $insert_array = array();
                $insert_array['name']=isset($data['role_name']) ? $data['role_name'] : '';
                $insert_array['description']=isset($data['description']) ? $data['description'] : '';
                $insert_array['is_active']=isset($data['is_active']) ? $data['is_active'] : 0;
                $insert_array['manufacturer_id']=isset($data['manufacture_id']) ? $data['manufacture_id'] : 0;
                $insert_array['role_type']=isset($data['customer_type']) ? $data['customer_type'] : 0;
                $insert_array['modified_by']=Session::get('userId');
                $insert_array['modified_on']=date('Y-m-d H:i:s');
                DB::table('roles')
                        ->where('role_id','=',$role_id)
                        ->update($insert_array);
                if(isset($data['user_id']) && !empty($data['user_id'])) { 
                    DB::table('user_roles')->where('role_id','=',$role_id)->delete();
                    foreach ($data['user_id'] as $user_id){
                        DB::table('user_roles')->insert(array('role_id'=>$role_id,'user_id'=>$user_id));
                    }
                }
                if(isset($data['feature_name']) && !empty($data['feature_name'])){
                DB::table('role_access')->where('role_id','=',$role_id)->delete();
                foreach ($data['feature_name'] as $featureId){
                    DB::table('role_access')->insert(array('role_id'=>$role_id,'feature_id'=>$featureId));
                } 
              }
            }
            return $role_id;
        }
    }
    
    public function getRole()
    {
        $customerId = Session::get('customerId');
        $startTime = $this->getTime();
        $result = DB::table('roles')
                ->select('roles.name','roles.role_id','eseal_customer.brand_name','users.username')
                ->leftJoin('user_roles','roles.role_id','=','user_roles.role_id')
                ->leftJoin('users','user_roles.user_id','=','users.user_id')
                ->leftJoin('eseal_customer','roles.manufacturer_id','=','eseal_customer.customer_id')
                ->where('roles.manufacturer_id','!=',0);
                if(!empty($customerId)){
                    $result->where('roles.manufacturer_id','=',$customerId);
                }
                
                //->orWhere('manufacturer_id','!=',0)
               $result = $result->where(array('is_delete'=>0))
                //->orWhere(array('roles.parent_role_id'=>Session::get('roleId'),'is_delete'=>0,'manufacturer_id' => 0))
                //->orWhere('roles.parent_role_id', Session::get('roleId'))
                //->orWhere('is_delete', 0)
                //->orWhere('roles.manufacturer_id', '!=', 0)
                ->groupby('roles.role_id')
                 ->orderBy('roles.name','desc')
                ->get()->toArray();
       /* $queries = DB::getQueryLog();
       $last_query = end($queries);
       print_r($last_query); 
       echo "<pre>";
       print_r($result); die;*/
       $endTime = $this->getTime();
                DB::table('user_tracks')->insert(array('user_id'=>Session::get('userId'),'message'=>'Successfully Retrieved.','status'=>1,'created_on'=>date("Y-m-d H:i:s"),'manufacturer_id'=>Session::get('customerId'),'service_name'=>'get role','response_duration'=>($endTime - $startTime)));
             return $result;

    }
    
    public function getRoles()
    {
        return DB::table('roles')
                ->select('roles.name','roles.role_id')
                ->where('roles.role_id',Session::get('roleId'))
                ->orWhere('roles.parent_role_id',Session::get('roleId'))
                ->get()->toArray();
    }

    public function DeleteRole($role_id)
    {   
        $startTime = $this->getTime();
        if(Session::get('roleId')==1){
            DB::table('roles')->where('role_id',$role_id)->delete();
            $endTime = $this->getTime();
             DB::table('user_tracks')->insert(array('user_id'=>Session::get('userId'),'message'=>'Role Deleted.'.$role_id,'service_name'=>'Delete Role','manufacturer_id'=>Session::get('customerId'),'status'=>1,'created_on'=>date('Y-m-d H:i:s'),'response_duration'=>($endTime - $startTime)));
            //DB::table('user_roles')->where('role_id','=',$role_id)->delete();
            //DB::table('role_access')->where('role_id','=',$role_id)->delete();
        }else{
            DB::table('roles')->where('role_id',$role_id)->delete();
            $endTime = $this->getTime();
            DB::table('user_tracks')->insert(array('user_id'=>Session::get('userId'),'message'=>'Role Deleted.'.$role_id,'service_name'=>'Delete Role','manufacturer_id'=>Session::get('customerId'),'status'=>1,'created_on'=>date('Y-m-d H:i:s'),'response_duration'=>($endTime - $startTime)));
            //DB::table('user_roles')->where('role_id','=',$role_id)->delete();
        }
        return TRUE;
    }
    
    public function getRoleById($role_id){
        
        return DB::select(DB::raw("SELECT roles.role_id,roles.name,roles.description, roles.is_active, roles.manufacturer_id, roles.role_type,"
                . "(select GROUP_CONCAT(feature_id) from role_access where role_id=roles.role_id) as feature_id, "
                . "(select GROUP_CONCAT(user_id) from user_roles where role_id=roles.role_id) as user_id FROM `roles`where roles.role_id=".$role_id));
    }
    
    public function checkAccessToken($access_token){
        
        return DB::table('users_token')
                ->select('user_id','access_token')
                ->where('access_token',$access_token)
                ->get()->toArray();
    }
    
    public function checkPermissionByFeatureCode($featureCode)
    {
        $userId = Session::get('userId');
        //print_r($userId);exit;
        if(!empty($userId)){
        $result = DB::table('role_access')
                ->select('features.name')
                ->join('features','role_access.feature_id','=','features.feature_id')
                ->join('user_roles','role_access.role_id','=','user_roles.role_id')
                ->where(array('user_roles.user_id'=>$userId, 'features.feature_code'=>$featureCode))
                ->count();
       //print_r($result);exit;
        return ($result > 0) ? TRUE : FALSE;
        }else
            return FALSE;
    }

    public function checkPermissionByApi($access_token,$featureCode)
    {
        $users_result = $this->checkAccessToken($access_token);
        if(!empty($users_result))
        {
            $userId = $users_result[0]->user_id;
            $result = $this->checkActionAccess($userId,$featureCode);
            return ($result) ? 1 : 0;
        }else
            return 0;
    }
    
    public function checkPermissionByUrl($contName,$methodName='')
    { 
        $url =(!empty($methodName)) ? $contName.'/'.$methodName : $contName;
        $result = DB::table('features')
                ->select('feature_code')
                ->where('url',$url)
                ->get()->toArray();
        
        if(!empty($result))
        {
            $permission = $this->checkPermissionByFeatureCode($result[0]->feature_code);
            return ($permission) ? TRUE : FALSE;
        }  else {
            return FALSE;
        }
    }
    
    public function getUserRoldIdByUserId($userId)
    {
        return DB::table('user_roles')->where('user_id',$userId)->get()->toArray();
    }

    public function assign_rand_value($num)
    {
        switch($num)
        {
            case "1":
                $rand_value = "a";
            break;
            case "2":
                $rand_value = "b";
            break;
            case "3":
                $rand_value = "c";
            break;
            case "4":
                $rand_value = "d";
            break;
            case "5":
                $rand_value = "e";
            break;
            case "6":
                $rand_value = "f";
            break;
            case "7":
                $rand_value = "g";
            break;
            case "8":
                $rand_value = "h";
            break;
            case "9":
                $rand_value = "i";
            break;
            case "10":
                $rand_value = "j";
            break;
            case "11":
                $rand_value = "k";
            break;
            case "12":
                $rand_value = "l";
            break;
            case "13":
                $rand_value = "m";
            break;
            case "14":
                $rand_value = "n";
            break;
            case "15":
                $rand_value = "o";
            break;
            case "16":
                $rand_value = "p";
            break;
            case "17":
                $rand_value = "q";
            break;
            case "18":
                $rand_value = "r";
            break;
            case "19":
                $rand_value = "s";
            break;
            case "20":
                $rand_value = "t";
            break;
            case "21":
                $rand_value = "u";
            break;
            case "22":
                $rand_value = "v";
            break;
            case "23":
                $rand_value = "w";
            break;
            case "24":
                $rand_value = "x";
            break;
            case "25":
                $rand_value = "y";
            break;
            case "26":
                $rand_value = "z";
            break;
            case "27":
                $rand_value = "0";
            break;
            case "28":
                $rand_value = "1";
            break;
            case "29":
                $rand_value = "2";
            break;
            case "30":
                $rand_value = "3";
            break;
            case "31":
                $rand_value = "4";
            break;
            case "32":
                $rand_value = "5";
            break;
            case "33":
                $rand_value = "6";
            break;
            case "34":
                $rand_value = "7";
            break;
            case "35":
                $rand_value = "8";
            break;
            case "36":
                $rand_value = "9";
            break;
        }
        return $rand_value;
    }

    public function checkToken($module_id,$access_token){

         $access = Token::where(array('module_id'=>$module_id,'access_token'=>$access_token))->get()->toArray();
         if(!empty($access[0])){
            return 1;
         }
         else{
            return 0;
         }
        }

    public function checkPermission($module_id,$access_token){
                         $access = $this->checkToken($module_id,$access_token);
                         return $access;
                    
                 }

    public function getErp($access_token){

        $mfg_id = $this->getMfgIdByToken($access_token);
        $data = DB::table('erp_integration')->where('manufacturer_id',$mfg_id)->first(['web_service_url','token','sap_client']);
        return $data;
    }   

    public function getLocTypeByAccessToken($access_token){
       
       $user_id= Token::where('access_token',$access_token)->value('user_id');
       $loc_type_id = DB::table('users')
                             ->join('locations','locations.location_id','=','users.location_id')
                             
                             ->where('users.user_id',$user_id)
                             ->value('locations.location_type_id');
                            
        return ($loc_type_id) ? $loc_type_id : FALSE;                   


    }    
    public function getUserDetailsByUserId($user_id){
        $details = DB::table('users')
                       ->where('user_id',$user_id)
                       ->orWhere('username',$user_id)
                       ->orWhere('email',$user_id)
                       ->get(['location_id','customer_id','username','business_unit_id'])->toArray();
        return ($details) ? $details : FALSE;
    }   

    public function getUserId($user_id){
        $details = DB::table('users')
                       ->where('user_id',$user_id)
                       ->orWhere('username',$user_id)
                       ->orWhere('email',$user_id)
                       ->value('user_id');
        return ($details) ? $details : FALSE;
    }   

    public function getMfgIdByToken($access_token){
            $user_id= Token::where('access_token',$access_token)->value('user_id');
            $manufacturer_id = User::where('user_id',$user_id)->value('customer_id');
            return $manufacturer_id;   
    }    

    public function getLocIdByToken($access_token){
            $user_id= Token::where('access_token',$access_token)->value('user_id');
            $location_id = User::where('user_id',$user_id)->value('location_id');
            return $location_id;   
    } 
    //added on  06/06/19
    public function getUserIdByToken($access_token){
            $user_id= Token::where('access_token',$access_token)->value('user_id');
            //$location_id = User::where('user_id',$user_id)->pluck('location_id');
            return $user_id;   
    } 
    //
    public function getErpDetailsByUserId($access_token){
        $user_id= Token::where('access_token',$access_token)->value('user_id');
        $erp = DB::table('users')->where('user_id',$user_id)
                                 ->whereNotNull('erp_username')
                                 ->whereNotNull('erp_password')
                                 ->get(['erp_username','erp_password'])->toArray();
        if(!empty($erp))                         
           return $erp;
        else
           return FALSE; 
    }
    
    public function encodeData($value)
    {
        return \Crypt::encrypt($this->_salt.$value);
    }
    
    public function decodeData($value)
    {
        try{
            //echo "hai";exit;
            return str_replace($this->_salt, '', \Crypt::decrypt($value));
        } catch (\ErrorException $ex) {
            return str_replace($ex->getMessage());
        }
    }
}
