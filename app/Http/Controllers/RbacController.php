<?php 
namespace App\Http\Controllers;
set_time_limit(0);
ini_set('memory_limit', '-1');
use App\Models\MasterLookup;
use App\Models\BusinessUnit;
use App\Repositories\RoleRepo;
use App\Repositories\CustomerRepo;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

use Maatwebsite\Excel\Facades\Excel;

use Session;
use DB;
use View;
use Input;
use Validator;
use Redirect;
use Log;
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
class RbacController extends BaseController{
    
    protected $CustomerObj;
    
    protected $roleAccessObj;
    protected $roleid;
            
     function __construct(CustomerRepo $CustomerObj, RoleRepo $roleAccessObj,Request $request) {
         $this->CustomerObj = $CustomerObj;
         $this->roleAccessObj = $roleAccessObj;
         $this->roleid = $this->roleAccessObj->getRole();
          $this->_request = $request;  
    }
    
    function index()
    {
        //echo "test"; exit;
        $addPermission = $this->roleAccessObj->checkPermissionByFeatureCode('RLE002');
        parent::Breadcrumbs(array('Home'=>'/','RBAC'=>'#')); 
        $results = $this->getRoles();
        return View::make('roles.list')->with(array('addPermission'=>$addPermission,'results'=>$results));
    }
     private function getTime(){
        $time = microtime();
        $time = explode(' ', $time);
        $time = ($time[1] + $time[0]);
        return $time;
    }
    
    function getRoles()
    {
        $editRole = $this->roleAccessObj->checkPermissionByFeatureCode('RLE003');
        $deleteRole = $this->roleAccessObj->checkPermissionByFeatureCode('RLE004');
        
        $results = $this->roleAccessObj->getRole();
       //print_r($results); die;
        $i=0;
        foreach($results as $result)
        {   
            $actions = '';
            $name = '';
           /* $name = '<span style="padding-left:20px;"><a href="rbac/edit/'.$result->role_id.'">'.$result->name.'</a></span><span style="padding-left:10px;" ></span>';*/
            $name =$result->name;
            if($editRole){        
                $actions .= '<span style="padding-left:20px;"><a href="rbac/edit/'.$result->role_id.'" title="Edit Role"><span class="badge bg-light-blue"><i class="fa fa-pencil"></i></span></a></span><span style="padding-left:10px;" ></span>';
            }
            
            if($deleteRole){
                $actions .= '<span style="padding-left:20px;" ><a href="javascript:void(0);" title="Delete Role" onclick="if(confirm(\'Are you sure you want to delete this role ?\')) { location.href=\'rbac/delete/'.$result->role_id.'\' }"><span class="badge bg-red"><i class="fa fa-trash-o"></i></span></a></span>';
            }
            $results[$i]->name = $name;
            $results[$i]->actions = $actions;
            
            $i++;
        }
        
        return json_encode($results);exit;
        
    }
    
    function create()
    {
       $customers = $this->CustomerObj->getAllCustomers();
       parent::Breadcrumbs(array('Home'=>'/','RBAC'=>'rbac','Add New'=>'#'));
//       $modules = $this->roleAccessObj->getModuleFeatures();
     
       $lookups = MasterLookup::where('category_id',7)->get();
       /*$features = $this->roleAccessObj->getPermissionFeature(); 
       echo "<pre>"; print_r($features); die;*/
       $modules = $this->roleAccessObj->getPermissionFeature();
       $inheritRoles = $this->roleAccessObj->getRole();
       //dd("jjj");
       
      
       
//       $i=0;
//        
//        $temp = array();
//        foreach($modules as $module)
//        {  
//            $j=0;
//            $chilidCount = 0;
//            $feature_id = explode(',',$module->feature_id);
//            $feature_name = explode(',', $module->feature_name);
//            $parent_id = explode(',', $module->parent_id);
//            foreach($parent_id as $parentid)
//            {
//                if($parentid==0){
//                    if($j > 0)
//                        $temp[$j-1]=$feature_id[$chilidCount];
//                    //$chilidCount=0;
//                    $j++;
//                }elseif ($chilidCount==count($parent_id)-1) {
//                    $temp[$j-1]=$feature_id[$chilidCount];
//                }
//                $chilidCount++;
//            }
//            //echo $chilidCount. '='.count($feature_id);
//            $modules[$i]->feature_id = $feature_id; 
//            $modules[$i]->feature_name = $feature_name;
//            $modules[$i]->parent_id = $parent_id;
//            $modules[$i]->chileCount = $temp;
//            $i++;
//        } 
        
        //echo '<pre>'; print_r($modules); die;
        if(Session::get('customerId') > 0) {
            //dd(Session::get('customerId'));
            $locationsall=DB::table('locations')->where(array('manufacturer_id'=>Session::get('customerId'),'is_deleted'=>0))->get();
            // $locationsall=Location::where('manufacturer_id',Session::get('customerId'))->where('is_deleted',0)->get();
            //dd('kkkk');
            $businessunits=BusinessUnit::where('manufacturer_id',Session::get('customerId'))->get();
            //dd($businessunits);
        }else{
            //dd("0");
            $locationsall = array();
            $businessunits = array();
        }    
        //dd("gg");
        $addPermission = $this->roleAccessObj->checkPermissionByFeatureCode('USR002');

        $users = $this->roleAccessObj->getUsers(Session::get('customerId'));
        //$users = json_decode($users);
        //echo "<pre>"; print_r($users); die;
        return View::make('roles/add')->with(array('customers'=>$customers,'modules'=>$modules,'users'=>$users,'lookups'=>$lookups,'inheritRoles'=>$inheritRoles,'locationsall'=>$locationsall,'businessunits'=>$businessunits,'addPermission'=>$addPermission));
    }

    function saveRole($key_id)
    {
    //dd(Input::all()); 
        $data = array(
                'name'=>$this->_request->Input('role_name'),
                'customer_type'=>$this->_request->Input('customer_type')
            );
        $rules = array(
                //'name'=>'required',
                'customer_type' => 'required'
            );
        if($key_id == 0){
            $rules['name'] = 'required|unique:roles';
        }
        else{
         $rules['name'] = 'required|unique:roles,name,'.$key_id .',role_id,manufacturer_id,'.$this->_request->Input('manufacture_id'); 
        }
        $validator = Validator::make(
            $data,$rules
            
        );
//        dd($validator);        
        if ($validator->fails())
        {
            $messages = $validator->messages();
            $messageArr = json_decode($messages);
            //dd($messages);
            
            $message = isset($messageArr->name[0 ]) ? $messageArr->name[0] : '';
            $message .= isset($messageArr->customer_type[0]) ? $messageArr->customer_type[0] : '';
            //print_r($messageArr); die;
            if($key_id ==0){
                return Redirect::to('rbac/add/')->with(array('errorMsgArr'=>$message,'row'=>$this->_request->Input()));
            }
            else{
                return Redirect::to('rbac/edit/'.$key_id)->with(array('errorMsgArr'=>$message,'row'=>$this->_request->Input()));    
            }
            
        } else {   
            $data = $this->_request->Input();
            //echo '<pre>'; print_r($data); die;
            $startTime = $this->getTime();
            $role_id = $this->roleAccessObj->SaveRole($data,$key_id);
            if($key_id == 0 && $role_id > 0){
                $message = 'Role added successfully';
                $endTime = $this->getTime();
                DB::table('user_tracks')->insert(array('user_id'=>Session::get('userId'),'message'=>'Successfully Created.'.json_encode($data),'service_name'=>'Create Role','manufacturer_id'=>Session::get('customerId'),'status'=>1,'created_on'=>date('Y-m-d H:i:s'),'response_duration'=>($endTime - $startTime)));
              }
            elseif($key_id > 0 && $role_id > 0){
                // $startTime = $this->getTime();
                $message = 'Role updated successfully';
                $endTime = $this->getTime();
                DB::table('user_tracks')->insert(array('user_id'=>Session::get('userId'),'message'=>'Successfully Updated.','service_name'=>'Edit Role','manufacturer_id'=>Session::get('customerId'),'status'=>1,'created_on'=>date('Y-m-d H:i:s'),'response_duration'=>($endTime - $startTime)));
            }
            return Redirect::to('rbac')->with('successMsg',$message);
        } 
    }
    
    function uploadProfilePic()
    {
       // echo "<pre>";        print_r(Input::file('file')); die;
        $filename = Input::file('file')->getClientOriginalName();
        $destinationPath = $_SERVER['DOCUMENT_ROOT'].'/uploads/profile_picture/'; 
        $filename = date('YmdHis').$filename;
        Input::file('file')->move($destinationPath, $filename);
        echo $filename; die;
        //echo $files['name']; die;
        //print_r($files); die;
    }

    public function getRoleforInherit($role_id)
    {
        $roles = $this->roleAccessObj->getRoleById($role_id);
        return json_encode($roles); exit;
    }
            
    function edit($key_id){ 
    
        parent::Breadcrumbs(array('Home'=>'/','RBAC'=>'rbac','Edit'=>'#'));
        $startTime = $this->getTime();
        $roles = $this->roleAccessObj->getRoleById($key_id);
        $roles[0]->feature_id = explode(',', $roles[0]->feature_id);
        $roles[0]->user_id = explode(',', $roles[0]->user_id);
        //echo '<pre>';print_r($roles); die;
        $customers = $this->CustomerObj->getAllCustomers();
        //$modules = $this->roleAccessObj->getModuleFeatures();
        $modules = $this->roleAccessObj->getPermissionFeature();
        $lookups = MasterLookup::where('category_id',7)->get();
        //return $roles[0]->role_id;
        $userinfo=db::table('user_roles')
              ->leftjoin('users','users.user_id','=','user_roles.user_id')
              ->select('users.username','user_roles.user_id','user_roles.role_id','users.firstname',
                'users.lastname','users.email')
              ->where('user_roles.role_id','=',$roles[0]->role_id)
              ->get();
              //return $userinfo;
              //print_r($userinfo);die;
         //$a=db::getQuerylog();
         //return $a;    
//        $i=0;
//        foreach($modules as $module)
//        {  
//            $j=0;
//            $chilidCount = 0;
//            $feature_id = explode(',',$module->feature_id);
//            $feature_name = explode(',', $module->feature_name);
//            $parent_id = explode(',', $module->parent_id);
//            foreach($parent_id as $parentid)
//            {
//                if($parentid==0){
//                    if($j > 0)
//                        $temp[$j-1]=$feature_id[$chilidCount];
//                    
//                    $j++;
//                }elseif ($chilidCount==count($parent_id)-1) {
//                    $temp[$j-1]=$feature_id[$chilidCount];
//                }
//                $chilidCount++;
//            }
//            
//            $modules[$i]->feature_id = $feature_id; 
//            $modules[$i]->feature_name = $feature_name;
//            $modules[$i]->parent_id = $parent_id;
//            $modules[$i]->chileCount = $temp;
//
//            $i++;
//        } 
        if(Session::get('customerId') > 0 ){
           $customerId = Session::get('customerId');
           $locationsall = array();
           $businessunits = array();
        }else{
           $customerId = $roles[0]->manufacturer_id;
           $locationsall=DB::table('locations')->where(array('manufacturer_id'=>$customerId,'is_deleted'=>0))->get();
           $businessunits=DB::table('business_units')->where('manufacturer_id',$customerId)->get();
        }
        $users = $this->roleAccessObj->getUsers($customerId);
        
        $addPermission = $this->roleAccessObj->checkPermissionByFeatureCode('USR002');
        //echo '<pre>'; print_r($roles[0]); die;
        $endTime = $this->getTime();
        DB::table('user_tracks')->insert(array('user_id'=>Session::get('userId'),'message'=>'Successfully retrieved.','status'=>1,'created_on'=>date("Y-m-d H:i:s"),'service_name'=>'Edit Role','response_duration'=>($endTime - $startTime)));
        return View::make('roles/edit')->with(array('row'=>$roles[0],'customers'=>$customers,'modules'=>$modules,'users'=>$users,'lookups'=>$lookups,'locationsall'=>$locationsall,'businessunits'=>$businessunits,'addPermission'=>$addPermission,'userinfo'=>$userinfo));
    }
    
    public function getUser($customerId=0)
    {  
        $users = $this->roleAccessObj->getUsers($customerId);
        $i=0;
        foreach ($users as $result)
        {
            $users[$i]->is_active = ($result->is_active==1) ? 'Active' : 'In-Active';
            $i++;
        }
        $locations=Location::where('manufacturer_id',$customerId)->get();
        $businessunits=BusinessUnit::where('manufacturer_id',$customerId)->get();
        $results = array();
        $results['users'] = $users;
        $results['locations'] = $locations;
        $results['businessunits'] = $businessunits;
        
        return json_encode($results);exit;
        
    }
    
    public function delete($key_id)
    {   
        $startTime = $this->getTime();
        $this->roleAccessObj->DeleteRole($key_id);
        $endTime = $this->getTime();
        DB::table('user_tracks')->insert(array('user_id'=>Session::get('userId'),'message'=>'Successfully Deleted.'.$key_id,'status'=>1,'created_on'=>date("Y-m-d H:i:s"),'service_name'=>'Delete Role','response_duration'=>($endTime - $startTime)));
        return Redirect::to('rbac');
    }
    
    public function saveUser()
    { //echo "<pre>"; print_r(Input::get()); die;
        $validator = Validator::make(
            array(
                'firstname'=>Input::get('firstname'),
                'lastname'=>Input::get('lastname'),
                'customer_type'=>Input::get('customer_type1'),
                'email'=>Input::get('email'),
                'username'=>Input::get('username'),
                'password'=>Input::get('password'),
                'confirm_password'=>Input::get('confirm_password'),
                'phone_no'=>Input::get('phone_no')
            ),
            array(
                'firstname'=>'required',
                'lastname' => 'required',
                'customer_type' => 'required',
                'email' => 'required|email|unique:users',
                'username' => 'required|unique:users',
                'password' => 'required',
                'confirm_password' => 'required|same:password',
                'phone_no'=>'numeric|digits:10'
            )
        );
        
        if ($validator->fails())
        {
            $messages = $validator->messages();
            return 'fail|'.$messages;exit;
        }else {
            $data = Input::get();
            if($data['customer_id']==''){
                $data['customer_id']=0;
            }
            $customer_type = $data['customer_type1'];
            unset($data['customer_type1']);
            $data['customer_type'] = $customer_type;
            
            $data['created_by']=Session::get('userId');
            $mytime = Carbon\Carbon::now();

            $data['created_on']=$mytime->toDateTimeString();
            $password = $data['password']; //str_random(20);
            $data['password']=md5($password);
            
            if(empty($data['location_id']))
                unset($data['location_id']);
            
            if(empty($data['business_unit_id']))
                unset($data['business_unit_id']);
            
            unset($data['confirm_password']);
            unset($data['_method']);
            unset($data['_token']);
            //unset($data['customer_type']);
            //print_r($data); die;
            $user_id = $this->roleAccessObj->saveUser($data);
            
            $template = EmailTemplate::where('Code','ET1000')->get();
         
            $emailVariable = array('firstName'=>$data['firstname'],'lastName'=>$data['lastname'],'username'=>$data['email'],'password'=>$password);
            //mail($data['email'], $template[0]->Subject, $message);
            Mail::send('emails.welcome',$emailVariable,function($msg) use ($template,$data) {
                $msg->from($template[0]->From,'eSealinc')->to($data['email'])->subject($template[0]->Subject);
                        
            });
            if(is_numeric($user_id)){    
                return 'success|'.json_encode(array('user_id'=>$user_id,'username'=>$data['username'])); exit;
            }else{
                return 'fail|'.json_encode(array('messge'=>'Please try again'));
            }    
        }    
    }
    public function featuresExport(){
       $modules=json_decode(str_replace('&nbsp','',$this->getdata()),true);
       // echo "<pre>";
       // print_r($modules);
       $export=array();
       foreach ($modules as $key => $module) {
        $features=$module['children'];
        $modulename=trim($module['modulename'],"\xC2\xA0");
        if(count($features)>0){
            foreach ($features as $key => $feature) {
                $mainFeaturename=trim($feature['featurename'],"\xC2\xA0");
                $mainFeaturecode=trim($feature['feature_code'],"\xC2\xA0");
                $subfeatures=$feature['children'];
                $temp= array(
                'module name'=>$modulename,
                'main feature name'=>$mainFeaturename,
                'sub feature name'=>'',
                'feature name'=>'',
                'feature code'=>$mainFeaturecode,
                'state'=>''
                );
                $export[]=$temp;  
                if(count($subfeatures)>0){
                    foreach ($subfeatures as $key => $subfeature) {
                        $subfeaturename=trim($subfeature['featurename'],"\xC2\xA0");
                        $subfeaturecode=trim($subfeature['feature_code'],"\xC2\xA0");
                        $subsubfeatures=$subfeature['children'];
                        $temp= array(
                            'module name'=>$modulename,
                            'main feature name'=>$mainFeaturename,
                            'sub feature name'=>$subfeaturename,
                            'feature name'=>'',
                            'feature code'=>$subfeaturecode,
                            'state'=>''
                        );
                        $export[]=$temp;

                        if(count($subsubfeatures)>0){
                            foreach ($subsubfeatures as $key => $subsubfeature) {
                                $subsubfeaturename=trim($subsubfeature['featurename'],"\xC2\xA0");
                                $subsubfeaturecode=trim($subsubfeature['feature_code'],"\xC2\xA0");
                                $temp= array(
                                'module name'=>$modulename,
                                'main feature name'=>$mainFeaturename,
                                'sub feature name'=>$subfeaturename,
                                'feature name'=>$subsubfeaturename,
                                'feature code'=>$subsubfeaturecode,
                                'state'=>''
                                );
                                $export[]=$temp;
                            }
                        }

                    }
                }
           }
       } else{
            $temp= array(
            'module name'=>$modulename,
            'main feature name'=>'',
            'sub feature name'=>'',
            'feature name'=>'',
            'feature code'=>'',
            'state'=>''
            );
            $export[]=$temp;           
       }
      }
      ob_end_clean();
        ob_start();
        return Excel::create('exportfeatures', function($excel) use ($export) {
            $excel->sheet('mySheet', function($sheet) use ($export)
            {
                $sheet->fromArray($export);
            });
        })->download('xls');
    }

    
    public function features()
    {
        parent::Breadcrumbs(array('Home'=>'/','Features'=>'#'));
        $addFeature = $this->roleAccessObj->checkPermissionByFeatureCode('FRE002');
        $modules=DB::table('master_lookup')
                ->join('lookup_categories','lookup_categories.id','=','master_lookup.category_id')
                ->select('master_lookup.value as module_id','master_lookup.name')
                ->where('lookup_categories.name','Product Types')
                ->get()->toArray();
        $parents=DB::table('features as a')
                    ->Leftjoin('features as b','a.parent_id','=','b.feature_id')
                    ->select('a.feature_id','a.name as featurename','b.name as parentname','b.feature_id as parent_id')
                    ->get()->toArray();
        return View::make('features.index')->with('modules',$modules)->with('parents',$parents)->with('addFeature',$addFeature);
        //return View::make('features.list');
    }
    
    public function getFeatures()
    {   
        $startTime = $this->getTime();
        $modules = $this->roleAccessObj->getPermissionFeature();
         $endTime = $this->getTime();
                DB::table('user_tracks')->insert(array('user_id'=>Session::get('userId'),'message'=>'Successfully Retrieved.','status'=>1,'created_on'=>date("Y-m-d H:i:s"),'manufacturer_id'=>Session::get('customerId'),'service_name'=>'get Feature','response_duration'=>($endTime - $startTime)));
        foreach($modules as $module)
        {
            
        }
//        $i=0;
//        foreach($modules as $module)
//        {  
//            $j=0;
//            $chilidCount = 0;
//            $feature_id = explode(',',$module->feature_id);
//            $feature_name = explode(',', $module->feature_name);
//            $parent_id = explode(',', $module->parent_id);
//            foreach($parent_id as $parentid)
//            {
//                if($parentid==0){
//                    if($j > 0)
//                        $temp[$j-1]=$feature_id[$chilidCount];
//                    
//                    $j++;
//                }elseif ($chilidCount==count($parent_id)-1) {
//                    $temp[$j-1]=$feature_id[$chilidCount];
//                }
//                $chilidCount++;
//            }
//            
//            $modules[$i]->feature_id = $feature_id; 
//            $modules[$i]->feature_name = $feature_name;
//            $modules[$i]->parent_id = $parent_id;
//            $modules[$i]->chileCount = $temp;
//
//            $i++;
//        } 
       // echo "<pre>"; print_r($modules); die;
        return json_encode($modules); exit;
    }
    
    public function store()
    {
        $data=$this->_request->all();
        // print_r($data);exit;
/*        if(!empty($data['name'])&&!empty($data['master_lookup_id'])&&!empty($data['feature_code'])){*/
           // return 'hi';

                    if(isset($data['is_menu'])){
                        $menu = 1;
                    }
                    else{
                         // echo "hai";exit;
                        $menu =0;
                    }
        //validator
                     $validator = \Validator::make(
                                    array(
                                'name' => isset($data['name']) ? $data['name'] : '',
                                'master_lookup_id' => isset($data['master_lookup_id']) ? $data['master_lookup_id'] : '',
                                'feature_code' => isset($data['feature_code']) ? $data['feature_code'] : ''
                                //'url'=> isset($data['url']) ? $data['url'] : ''
                                    ), array(
                                'name' => 'required',
                                'master_lookup_id' => 'required',
                                'feature_code' => 'required'
                                //'url'=>'required'
                                    ));
                    if($validator->fails())
                    {
                        //$data = $this->_product->getProductFields($this->_manufacturerId);
                        $errorMessages = json_decode($validator->messages());
                        $errorMessage = '';
                        if(!empty($errorMessages))
                        {
                            foreach($errorMessages as $field => $message)
                            {
                                $errorMessage = implode(',', $message);
                            }
                        }
                        //return response()->back()->withErrors([$errorMessage]);
                        return response()->json([
                                'status' => false,
                                'message' => $errorMessage
                    ]);
                    }

                //validator
                    $startTime = $this->getTime();
        DB::Table('features')->insert([
                'master_lookup_id'=>$this->_request->get('master_lookup_id'),
                'name'=>$this->_request->get('name'),
                'description'=>$this->_request->get('description'),   
                'feature_code'=>$this->_request->get('feature_code'),
                'is_active'=>$this->_request->get('is_active'),
                'sort_order'=>$this->_request->get('sort_order'),
                'parent_id'=>$this->_request->get('parent_id'),
                'icon'=>$this->_request->get('icon'),
                'url'=>$this->_request->get('url'),
                'is_menu' =>$menu
                ]);
        //DB::Table('features')->insert($data);
        $feature_id = DB::getPdo()->lastInsertId();
        DB::table('role_access')->insert(['role_id'=>1,'feature_id'=>$feature_id]);
        unset($data['token']);
        //dd($data);
        $endTime= $this->getTime();
        DB::table('user_tracks')->insert(array('user_id'=>Session::get('userId'),'message'=>'Successfully Created.'.json_encode($data),'service_name'=>'Create Feature','status'=>1,'manufacturer_id'=>Session::get('customerId'),'response_duration'=>($endTime - $startTime)));
        return response()->json(['status'=>true, 'message'=>'Feature added successfully']);
/*      }
      return response()->json(['status'=>false, 'message'=>'Please Fill the fields']);*/
    }

    public function editFeature($feature_id)
    {
        $startTime = $this->getTime();
        $feature = Feature::where('feature_id',$feature_id)->first();
        $endTime = $this->getTime();
                DB::table('user_tracks')->insert(array('user_id'=>Session::get('userId'),'message'=>'Successfully Retrieved.','status'=>1,'created_on'=>date("Y-m-d H:i:s"),'manufacturer_id'=>Session::get('customerId'),'service_name'=>'edit feature','response_duration'=>($endTime - $startTime)));
        return response()->json($feature);
         
    }

public function update($feature_id)
    {
        $data=Input::all();
        //return $data;
                //validator
                     $validator = \Validator::make(
                                    array(
                                'name' => isset($data['name']) ? $data['name'] : '',
                                'master_lookup_id' => isset($data['master_lookup_id']) ? $data['master_lookup_id'] : '',
                                'feature_code' => isset($data['feature_code']) ? $data['feature_code'] : ''
                                //'url'=> isset($data['url']) ? $data['url'] : ''
                                    ), array(
                                'name' => 'required',
                                'master_lookup_id' => 'required',
                                'feature_code' => 'required'
                                //'url'=>'required'
                                    ));
                    if($validator->fails())
                    {
                        //$data = $this->_product->getProductFields($this->_manufacturerId);
                        $errorMessages = json_decode($validator->messages());
                        $errorMessage = '';
                        if(!empty($errorMessages))
                        {
                            foreach($errorMessages as $field => $message)
                            {
                                $errorMessage = implode(',', $message);
                            }
                        }
                        //return response()->back()->withErrors([$errorMessage]);
                        return response()->json([
                                'status' => false,
                                'message' => $errorMessage
                    ]);
                    }
                    if($data['is_menu']){
                        $menu =1;
                    }
                    else{
                        $menu =0;
                    }
                //validator
        //added for Group Modifications
                    $startTime = $this->getTime();
        $Parentfeature=DB::table('features')->where('parent_id',$feature_id)->get(array(DB::raw('group_concat(feature_id) as feature_id')));
        if(isset($Parentfeature[0]->feature_id)){
                $parent = array($Parentfeature[0]->feature_id);            
                $parent = implode(',', $parent);
                $feature=DB::select(DB::raw("SELECT group_concat(feature_id) feature_id FROM features 
                where parent_id in ($parent) 
                or feature_id = $feature_id or parent_id = $feature_id"));
                $feature = implode(',', array($feature[0]->feature_id));
                //return $feature; 
                $module_id=Input::get('master_lookup_id');
                 DB::select(DB::raw("Update features set  master_lookup_id =  $module_id            
                            where feature_id in ($feature)")); 
        }
        //group modifications

            DB::Table('features')
                    ->where('feature_id',$feature_id)
                    ->update(array('master_lookup_id'=>Input::get('master_lookup_id'),
                    'name'=>Input::get('name'),
                    'description'=>Input::get('description'),   
                    'feature_code'=>Input::get('feature_code'),
                    'is_active'=>Input::get('is_active'),
                    'parent_id'=>Input::get('parent_id'),
                    'icon'=>Input::get('icon'),
                    'url'=>Input::get('url'),
                    'is_menu' => $menu,
                    'sort_order'=>Input::get('sort_order'))); 
                    unset($data['token']);
                    $endTime = $this->getTime();          
      DB::table('user_tracks')->insert(array('user_id'=>Session::get('userId'),'message'=>'Successfully Updated.'.json_encode($data),'service_name'=>'Update Feature','status'=>1,'manufacturer_id'=>Session::get('customerId'),'response_duration'=>($endTime - $startTime)));
     return response()->json([
                    'status' => true,
                    'message'=>'Sucessfully updated.']);
     //dd($data);
     
    }



public function destroy($feature_id)
{
       $password = Input::get();
        $userId = Session::get('userId');
        $verifiedUser = $this->roleAccessObj->verifyUser($password['password'], $userId);
        if($verifiedUser >= 1)
        {
            $feature = Feature::find($feature_id)->delete();
            return 1;
        }else{
            return "You have entered incorrect password !!";
        }
}
public function FeatureDelete($feature_id)
{
       $password = Input::get();
        $userId = Session::get('userId');
        $startTime = $this->getTime();
        $verifiedUser = $this->roleAccessObj->verifyUser($password['password'], $userId);
        if($verifiedUser >= 1)
        {
            $Parentfeature=DB::table('features')->where('parent_id',$feature_id)->get(array(DB::raw('group_concat(feature_id) as feature_id')));
            if(isset($Parentfeature[0]->feature_id)){
                $parent = array($Parentfeature[0]->feature_id);
                $parent = implode(',', $parent);
                 $feature=DB::select(DB::raw("SELECT group_concat(feature_id) feature_id FROM features 
                    where parent_id in ($parent) 
                    or feature_id = $feature_id or parent_id = $feature_id"));
                 $feature = implode(',', array($feature[0]->feature_id)); 
                 DB::select(DB::raw("DELETE FROM features 
                        where feature_id in ($feature)")); 
                 $endTime = $this->getTime();
                        DB::table('user_tracks')->insert(array('user_id'=>Session::get('userId'),'message'=>'Successfully Deleted.'.$feature_id,'service_name'=>'Delete Feature.','status'=>1,'manufacturer_id'=>Session::get('customerId'),'response_duration'=>($endTime - $startTime)));   
            return 1;
            }else{
            $feature = DB::table('features')
            ->where('feature_id',$feature_id)
            ->orWhere('parent_id',$feature_id)->delete();
            $endTime = $this->getTime();
            DB::table('user_tracks')->insert(array('user_id'=>Session::get('userId'),'message'=>'Successfully Deleted.'.$feature_id,'service_name'=>'Delete Parent Feature','status'=>1,'manufacturer_id'=>Session::get('customerId'),'response_duration'=>($endTime - $startTime)));
            return 1;
            }

    
        }else{
            return "You have entered incorrect password !!";
        }
}


public function getdata()
{
        $addFeature = $this->roleAccessObj->checkPermissionByFeatureCode('FRE002');
        $editFeature = $this->roleAccessObj->checkPermissionByFeatureCode('FRE003');
        $deleteFeature = $this->roleAccessObj->checkPermissionByFeatureCode('FRE004');
        $modarr = array();
            $finalmodarr = array();
            $startTime = $this->getTime();
            $mods=DB::table('master_lookup')
                    ->join('lookup_categories','lookup_categories.id','=','master_lookup.category_id')
                    ->select('master_lookup.value as module_id','master_lookup.name as modulename')
                    ->where('lookup_categories.name','Product Types')
                    ->get()->toArray();
            foreach($mods as  $mod)
            {
                $featarr = array();
                $finalfeatarr = array();
                $feats=DB::table('master_lookup')
                         ->join('lookup_categories','lookup_categories.id','=','master_lookup.category_id')
                         ->join('features','features.master_lookup_id','=','master_lookup.value')
                         ->select('master_lookup.name as module','features.master_lookup_id as module_id','features.name as featurename',
                                'features.feature_id','features.parent_id as parentid','features.feature_code',
                                'features.description','features.is_active')
                         ->where(array('lookup_categories.name'=>'Product Types','features.master_lookup_id'=>$mod->module_id,'features.parent_id'=>0))
                        ->get()->toArray();
                    // print_r($feats);exit;        
                foreach($feats as  $feat)
                {    
                    $ccfeatarr = array();
                    $ccfinalfeatarr = array();
                    $ccfeats=DB::table('master_lookup')
                             ->join('lookup_categories','lookup_categories.id','=','master_lookup.category_id')
                             ->join('features','features.master_lookup_id','=','master_lookup.value')
                             ->select('master_lookup.name as module','features.name as featurename',
                                    'features.feature_id','features.parent_id as parentid','features.feature_code',
                                    'features.description','features.is_active')
                             ->where(array('lookup_categories.name'=>'Product Types','features.master_lookup_id'=>$feat->module_id,'features.parent_id'=>$feat->feature_id))
                            ->get()->toArray(); 
                    foreach($ccfeats as  $ccfeat)
                    {    
                        $cfeatarr = array();
                        $cfinalfeatarr = array();
                        $cfeats=DB::table('master_lookup')
                                 ->join('lookup_categories','lookup_categories.id','=','master_lookup.category_id')
                                 ->join('features','features.master_lookup_id','=','master_lookup.value')
                                 ->select('master_lookup.name as module','features.name as featurename',
                                        'features.feature_id','features.parent_id as parentid','features.feature_code',
                                        'features.description','features.is_active')
                                 ->where(array('lookup_categories.name'=>'Product Types','features.master_lookup_id'=>$feat->module_id,'features.parent_id'=>$ccfeat->feature_id))
                                ->get()->toArray();                          
                            foreach($cfeats as $cfeat)
                            {
                                if($cfeat->is_active ==1)
                                    $status = 'Active';
                                else
                                    $status = 'In-Active';
                                $cfeatarr['feature_id']=$cfeat->feature_id;
                                $cfeatarr['featurename']=str_repeat("&nbsp", 10).$cfeat->featurename;
                                $cfeatarr['feature_code']=$cfeat->feature_code;
                                $cfeatarr['is_active']=$status;
                                $actions = '';
                                if($editFeature){ 
                                $actions = $actions.'<span style="padding-left:10px;" ><a data-href="editfeature/'.$cfeat->feature_id.'" data-toggle="modal" data-target="#basicvalCodeModal1"><span class="badge bg-light-blue"><i class="fa fa-pencil"></i></span></a></span>';
                                }
                                if($deleteFeature){
                                 $actions = $actions.'<span style="padding-left:10px;" ><a onclick="deleteEntityType('.$cfeat->feature_id.')"><span class="badge bg-red"><i class="fa fa-trash-o"></i></span></a></span>';
                                }
                                $cfeatarr['actions'] = $actions;
                                $cfinalfeatarr[]=$cfeatarr;
                            }
                            if($ccfeat->is_active ==1)
                                $status = 'Active';
                            else
                                $status = 'In-Active';
                            $ccfeatarr['feature_id']=$ccfeat->feature_id;
                            $ccfeatarr['featurename']=str_repeat("&nbsp", 5).$ccfeat->featurename;
                            $ccfeatarr['feature_code']=$ccfeat->feature_code;
                            $ccfeatarr['is_active']=$status;
                            $actions = '';
                            if($addFeature){
                            $actions = $actions.'<span style="padding-left:10px;" ><a data-href="features" data-toggle="modal" onclick="getModuleId('.$mod->module_id.','.$ccfeat->feature_id.');" data-target="#basicvalCodeModal"><span class="badge bg-green"><i class="fa fa-plus"></i></span></a></span>';                           
                            }
                            if($editFeature){ 
                            $actions = $actions.'<span style="padding-left:10px;" ><a data-href="editfeature/'.$ccfeat->feature_id.'" data-toggle="modal" data-target="#basicvalCodeModal1"><span class="badge bg-light-blue"><i class="fa fa-pencil"></i></span></a></span>';
                            }
                            if($deleteFeature){
                             $actions = $actions.'<span style="padding-left:10px;" ><a onclick="deleteParent('.$ccfeat->feature_id.')"><span class="badge bg-red"><i class="fa fa-trash-o"></i></span></a></span>';
                            }
                            $ccfeatarr['actions'] = $actions;
                            $ccfeatarr['children']=$cfinalfeatarr;
                            $ccfinalfeatarr[]=$ccfeatarr;
                        }                              
                        if($feat->is_active ==1)
                            $status = 'Active';
                        else
                            $status = 'In-Active';
                        $featarr['feature_id']=$feat->feature_id;
                        $featarr['featurename']=$feat->featurename;
                        $featarr['feature_code']=$feat->feature_code;
                        $featarr['is_active']=$status;
                        $actions = '';
                        if($addFeature){
                        $actions = $actions.'<span style="padding-left:10px;" ><a data-href="features" data-toggle="modal" onclick="getModuleId('.$mod->module_id.','.$feat->feature_id.');" data-target="#basicvalCodeModal"><span class="badge bg-green"><i class="fa fa-plus"></i></span></a></span>';                           
                        }
                        if($editFeature){ 
                        $actions = $actions.'<span style="padding-left:10px;" ><a data-href="editfeature/'.$feat->feature_id.'" data-toggle="modal" data-target="#basicvalCodeModal1"><span class="badge bg-light-blue"><i class="fa fa-pencil"></i></span></a></span>';
                        }
                        if($deleteFeature){
                         $actions = $actions.'<span style="padding-left:10px;" ><a onclick="deleteParent('.$feat->feature_id.')"><span class="badge bg-red"><i class="fa fa-trash-o"></i></span></a></span>';
                        }
                        $featarr['actions'] = $actions;
                        $featarr['children']=$ccfinalfeatarr;
                        $finalfeatarr[]=$featarr;
                    }                   

                            $modarr['modulename']=$mod->modulename;
                            if($addFeature){ 
                            $modarr['actions']='<span style="padding-left:10px;" ><a data-href="features" data-toggle="modal" onclick="getModuleId('.$mod->module_id.');" data-target="#basicvalCodeModal"><span class="badge bg-green"><i class="fa fa-plus"></i></span></a></span>';                           
                            }
                            $modarr['children']=$finalfeatarr;
                            $finalmodarr[]=$modarr;

            }
                //echo "<pre>";  print_r($finalmodarr); die;  
        $endTime = $this->getTime();
    DB::table('user_tracks')->insert(array('user_id'=>Session::get('userId'),'message'=>'Successfully Retrieve Features.','service_name'=>'get Feature','status'=>1,'created_on'=>date("Y-m-d H:i:s"),'manufacturer_id'=>Session::get('customerId'),'response_duration'=>($endTime - $startTime)));
    return json_encode($finalmodarr);   


    }
    function exportUsers()
    {
        $addPermission = $this->roleAccessObj->checkPermissionByFeatureCode('RLE002');
        $results = $this->getRoles();
        // echo"<pre>";print_r($results);exit;
        $export=array();
        foreach (json_decode($results,true) as $key => $value) {
            $value['name']=$value['name'];
            unset($value['actions']);
            unset($value['uname']);
           $export[]=$value;
        }
        ob_end_clean();
        ob_start();
        return Excel::create('exportroleusers', function($excel) use ($export) {
            $excel->sheet('mySheet', function($sheet) use ($export)
            {
                $sheet->fromArray($export);
            });
        })->download('xls');
    }
}

