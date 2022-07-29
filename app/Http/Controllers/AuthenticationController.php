<?php

namespace App\Http\Controllers;
use App\Models\User;
use App\Models\Url;
use App\Models\MasterLookup;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Carbon;
use App\Repositories\RoleRepo;
use App\Repositories\CustomerRepo;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\UserNew;
use Illuminate\Support\Facades\Crypt;

use Session;
use DB;
use View;
use Input;
use Validator;
use Redirect;
use Log;
use File;
//use URL;

class AuthenticationController extends BaseController
{
    
    public $roleAccess;
    public $custRepoObj;
    public $_request;
    
    public function __construct(RoleRepo $roleAccess, CustomerRepo $custRepoObj,Request $request) {
        $this->roleAccess = $roleAccess;
        $this->custRepoObj = $custRepoObj;
        $this->_request = $request;
    }

    public function exportdatausers(){

    $cusotmerId = Session::get('customerId');

    $results = $this->roleAccess->getUsersList($cusotmerId);

   // echo "<pre/>";print_r($results);exit;

    $headers = array('User Name','First Name','Last Name','Customer Type','Email','phone_no',);

         Excel::create('User Export-', function($excel) use($headers, $results) 
            {
                $excel->sheet("users", function($sheet) use($headers, $results)
                {
                    $sheet->loadView('users.downloadusers', array('headers' => $headers, 'data' => $results)); 
                });
            ob_end_clean();
            })->export('xlsx');

}

    function index()
    {   
      //  echo Session::has('userId'); exit;
        if(Session::has('azureUser'))
            return Redirect::to('/');
        else 
            return View::make('login');
 //       return view('login');
    }
     private function getTime(){
        $time = microtime();
        $time = explode(' ', $time);
        $time = ($time[1] + $time[0]);
        return $time;
    }
    function checkAuth()
    {
        $validator = Validator::make(
            array(
                'email'=>$this->_request->input('email'),
                'password'=>$this->_request->input('password')
            ),
            array(
                'email'=>'required', // |email
                'password' => 'required'
            )
        );
        if ($validator->fails())
        {
            //$row = $this->_request->input();
            $row = $this->_request->all();
            $messages = $validator->messages();
            $messageArr = json_decode($messages);
            $errorMsg ='';
            if(isset($messageArr->email[0]))
                $errorMsg .= $messageArr->email[0];
            if(isset($messageArr->password[0]))
                $errorMsg .= '<br>'.$messageArr->password[0];
            //print_r($this->_request->input()); die;
            //return View::make('login')->with(array('row'=>$row,'errorMsg'=>$errorMsg));
             return Redirect::to('login')->with('errorMsg', $errorMsg);
        }else{


            $email = $this->_request->input('email');
            $password = $this->_request->input('password');
            Log::info('password from login:-'.$password);
            $result = $this->roleAccess->authenticateUser($email,$password);
            
            //print_r($result); die;
           if(empty($result))
           {
               $errorMsg = 'Invalid email or password';
               $row = $this->_request->input();
               //echo $errorMsg; die;
               return View::make('login')->with(array('row'=>$row,'errorMsg'=>$errorMsg));
           }else{
            
                $oAuthResponse = $this->loginViaOauth();

                $user = UserNew::find($result[0]->user_id);
                $oAuthtoken = $user->createToken('Token Name')->accessToken;

                if($oAuthResponse === false) {
                    return Redirect::to('login')->with('errorMsg', 'Unable to Login');
                }

                $result = $result[0];
                $role = $this->roleAccess->getRolebyUserId($result->user_id);
                //echo '<pre>';print_r($role);exit;
                $cusomerLogo = '';
                if($result->customer_id > 0)
                {
                    $cusomerLogo = $this->custRepoObj->getCustomerLogo($result->customer_id);
                    $cusomerLogo = $cusomerLogo[0]->logo;
                }
                //print_r($cusomerLogo[0]->logo); die;
                if(!empty($role)){

                    $access_token = DB::table('users_token')->where('user_id',$result->user_id)->where('module_id','=',4002)->value('access_token');

                    if($access_token==''){
                    //echo 'test';
                    $request = Request::create('scoapi/login', 'POST', array('module_id'=> 4002 ,'user_id'=>$email,'password'=>$password));
                    $originalInput=$this->_request->all();
                $this->_request->replace($request->all());
                $response = app()->handle($request);

                   // $originalInput = Request::input();//backup original input
                   // Request::replace($request->input());
                   // Log::info($request->input());
                   // $response = Route::dispatch($request)->getContent();
                    //invoke API
                    //print_r($response);
                    //exit;
                    }   
                    $userid=$result->user_id;
                     Session::put('roleId',$role[0]->role_id);
                      Session::put('userInputEmail',$email);

                   /* Session::put('userId',$result->user_id);
                    Session::put('userName',$result->username);
                    Session::put('userType',$result->user_type);
                   
                    Session::put('customerId',$result->customer_id);
                    Session::put('esealErp',$result->eseal_erp);*/
                   // Session::put('password',$password);
                    //Session::put('access_token',$access_token);
                    //Session::put('oAuth_token',$oAuthtoken);

                    $childCompanies=$this->custRepoObj->getChildDetails($userid);
                    Session::put('childCompanies',$childCompanies);
 
                    if(!empty($cusomerLogo)){
                       Session::put('customerLogoPath','uploads/customers/'.$cusomerLogo);
                    }
                    if(!empty($result->profile_picture))
                    {
                        Session::put('userLogoPath','uploads/profile_picture/'.$result->profile_picture);
                    }
                    /*if($role[0]->name == 'Sign-up Role')
                    {
                        $cust =DB::table('users')->where('user_id',$result->user_id)->pluck('customer_id');
                        $checkProfile =DB::table('eseal_customer')->where('customer_id',$cust)->pluck('profile_completed');
                        
                        if($checkProfile){
                             return Redirect::to('/thankyou');
                        }else{
                            return Redirect::to('/signup');
                        }
                    }    */                
                }
                else{
                     $UrlerrorMsg = 'You don not have permission to access this page'; 
                     return Redirect::to(URL::previous())->with('errorMsg', $UrlerrorMsg);
                }
              //  Session::save();
                   return Redirect::to('/login/microsoft');

             //  return $this->authorizeCheck($user->user_id, $user->customer_id);
           }
          
        } 
    }
    
    public function forgot()
    {
       
        $validator = Validator::make(
            array(
                'email'=>$this->_request->input('email'),
            ),
            array(
                'email'=>'required|email',
            )
        );
        if ($validator->fails())
        {
            $messages = $validator->messages();
            $messageArr = json_decode($messages);
            $errorMsg ='';
            if(isset($messageArr->email[0]))
                $errorMsg .= $messageArr->email[0]; 
            return $errorMsg; exit;
        }else{
            $user = DB::Table('users')->select('user_id','username')->where('email',$this->_request->input('email'))->get();
            $token = md5(uniqid(rand(),1));
            
           // echo "<pre/>";print_r($user);exit;
            
            if(isset($user[0]->user_id)){
                \Mail::send(['html' => 'emails.auth.reminder'], array('token' => $token), function($message) 
                 {        
                            $message->to($this->_request->input('email'));
                 }); 
                DB::Table('users')
                -> where('user_id',$user[0]->user_id)
                ->update(array('password_token'=> $token));

                $errorMsg = "Email has been sent for reset the password.Please check the mail"; 
                return  '<div style="color: #009900">'.$errorMsg.'</div>';
                exit;
            }  
            else
            {
                 
                $errorMsg = "Email address not found. Please enter correct email address"; 
                return  '<div style="color: #FF0000">'.$errorMsg.'</div>'; exit;
            }

        }     
        
    }

   public function reset($token)
    {    
    //return $token;
    $user = DB::table('users')->where('password_token',$token)->get();
    return View::make('password.reset')->with('user',$user);
        
    }

    public function passwordreset()
    { 
          $data = Input::all();
          //echo "<pre/>";print_r($data['user_id']);exit;
          if($data['resetpswd'] == $data['confirmpswd'])
          {
             DB::Table('users')
                ->where('user_id',$data['user_id'])
                ->update(array('password'=>md5($data['confirmpswd'])));
                $ds = DB::getQueryLog();
                //return end($ds);
       
          // return response()->json([
          //           'status' => true,
          //           'message' => 'Password updated sucessfully .' ]);

                return Redirect::to('/login')->withFlashMessage('Password updated successfully');

        
          }else{
                    return response()->json([
                    'status' => false,
                    'message' => 'Passwords not matching.'
        ]);

          }
    }


    public function logout()
    { 
        Session::forget('userId');
        Session::forget('azureUser');
        Session::forget('userName');
        Session::forget('userType');
        Session::forget('roleId');
        Session::forget('customerId');
        if(Session::has('customerLogoPath'))
            Session::forget('customerLogoPath');
        if(Session::has('userLogoPath'))
            Session::forget('userLogoPath');
        return Redirect::to('/login');
        
    }
    
    public function users()
    { 
         parent::Breadcrumbs(array('Home'=>'/','Users'=>'#')); 
        $addPermission = $this->roleAccess->checkPermissionByFeatureCode('USR002');
        return View::make('users.list')->with(array('addPermission'=>$addPermission));
    }
    
    public function usersList()
    {   
        $cusotmerId = Session::get('customerId');
        $startTime =$this->getTime();  
        if(Session::get('roleId')==1){
            $results = User::all();

            //$this->roleAccess->getUsers($cusotmerId);
            $endTime = $this->getTime();
          DB::table('user_tracks')->insert(array('user_id'=>Session::get('userId'),'message'=>'Successfully Retrieved.','status'=>1,'created_on'=>date("Y-m-d H:i:s"),'manufacturer_id'=>Session::get('customerId'),'service_name'=>'get User','response_duration'=>($endTime - $startTime))); 
        } else {
            $results = $this->roleAccess->getUsersList($cusotmerId);
            $endTime = $this->getTime();
            DB::table('user_tracks')->insert(array('user_id'=>Session::get('userId'),'message'=>'Successfully Retrieved.','status'=>1,'created_on'=>date("Y-m-d H:i:s"),'manufacturer_id'=>Session::get('customerId'),'service_name'=>'get User','response_duration'=>($endTime - $startTime)));
        }
        
        $editPermission = $this->roleAccess->checkPermissionByFeatureCode('USR003');
        $DeletePermission = $this->roleAccess->checkPermissionByFeatureCode('USR004');
        $i=0;
        //echo $editPermission;exit;
       // print_r($results); die;
        foreach ($results as $result):
            $results[$i]->is_active = ($result->is_active==1) ? 'Active' : 'In-Active';
            
                
            $actions = '';
            if($editPermission)
                 $actions .= '<span style="padding-left:20px;" ><a href="javascript:void(0);" onclick="editUser('.$result->user_id.')" data-target="#basicvalCodeModal1"><span class="badge bg-light-blue"><i class="fa fa-pencil"></i></span></span>';
            if($DeletePermission)
                $actions .= '<span style="padding-left:20px;" ><a href="javascript:void(0)" onclick="deleteEntityType('.$result->user_id.')"><span class="badge bg-red"><i class="fa fa-trash-o"></i></span></a></span>'; 
            $results[$i]->actions = $actions;
            // echo "<pre>";
            // print_r($editPermission);exit;
            $i++;
        endforeach;
            
        $jsonString = json_encode($results);
        return $jsonString; exit;
    }

    public function add_User()
    {
        $roles = $this->roleAccess->getRole();
        if(Session::get('customerId') == 0) {
            $lookups = MasterLookup::where('category_id',7)->get();
            $customers = $this->custRepoObj->getAllCustomers();          
            

                $locationsall= array(); 
                $businessunits= array();    
                $location_types =array();
            
            
            return View::make('users.add')->with(array('roles'=>$roles,'lookups'=>$lookups,'customers'=>$customers,'locationsall'=>$locationsall,'businessunits'=>$businessunits,'location_types'=>$location_types));
            exit;
        }else{
            $location_types = DB::table('location_types')->where('manufacturer_id',Session::get('customerId'))->get(['location_type_name','location_type_id'])->toArray();
             $locationsall = DB::table('locations')
->join('location_types', 'location_types.location_type_id', '=', 'locations.location_type_id')
->where(['locations.manufacturer_id' => Session::get('customerId')])
->where('location_types.location_type_name', '<>', 'Customer')
->get()->toArray();
            // echo "<pre>";
            // print_r($locationsall);exit;
             $businessunits = DB::table('business_units')->where('manufacturer_id',Session::get('customerId'))->get()->toArray();

            return View::make('users.add')->with(array('roles'=>$roles,'locationsall'=>$locationsall,'businessunits'=>$businessunits,'location_types'=>$location_types)); exit;
        }    
    }
    
    public function saveUser($userId = 0)
    { 
        if($userId == 0 ) {
            $validator = Validator::make(
                array(
                    'firstname'=>$this->_request->input('firstname'),
                    'lastname'=>$this->_request->input('lastname'),
                    'customer_type'=>$this->_request->input('customer_type'),
                    'email'=>$this->_request->input('email'),
                    'username'=>$this->_request->input('username'),
                    'password'=>$this->_request->input('password'),
                    'confirm_password'=>$this->_request->input('confirm_password'),
                    'phone_no'=>$this->_request->input('phone_no')
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
        }else{
           $rules = array(
                    'firstname'=>'required',
                    'lastname' => 'required',
                    'customer_type' => 'required',
                    'email' => 'required|email',
                   // 'username' => 'required',
                    'phone_no'=>'numeric|digits:10'    
                );
            
            
            $fields_value =  array(
                    'firstname'=>$this->_request->input('firstname'),
                    'lastname'=>$this->_request->input('lastname'),
                    'customer_type'=>$this->_request->input('customer_type'),
                    'email'=>$this->_request->input('email'),
                    'username'=>$this->_request->input('username'),
                    'phone_no'=>$this->_request->input('phone_no')  

                );
            if($this->_request->input('password')!='')
            {
                $fields_value['password'] = $this->_request->input('password');
                $fields_value['confirm_password'] = $this->_request->input('confirm_password');
                $rules['password'] = 'required';
                $rules['confirm_password'] = 'required|same:password';
            }    
           $validator = Validator::make(  $fields_value,$rules);
        }
        if ($validator->fails())
        {
            $messages = $validator->messages();
            return 'fail|'.$messages;exit;
        }else {
            $data = $this->_request->input();
              //dd($data);
            $data['created_by']=Session::get('userId');
            $mytime = Carbon::now();
            if(!isset($data['customer_id']) && empty($data['customer_id']))
            {
                $data['customer_id']=0;
            }
            $data['created_on']=$mytime->toDateTimeString();
            if($userId==0){
                $password = $this->_request->input('password');//str_random(20);
                $data['password']=md5($password);
                unset($data['confirm_password']);
            }elseif($this->_request->input('password')!=''){
                $password = $this->_request->input('password');
                $data['password'] = md5($password);
                unset($data['confirm_password']);
            }else{
                unset($data['password']);
                unset($data['confirm_password']);
            }
                
            //print_r($data); die;
            unset($data['_method']);
            unset($data['_token']);
            unset($data['customer_type']);
            if(isset($data['location_type_id'])){
                unset($data['location_type_id']);    
            }
            
            $roleId = $data['role_id'];
            unset($data['role_id']);
            if(!isset($data['is_active']))
                $data['is_active'] = 0;
            if($userId > 0){
                $user_id = $this->roleAccess->saveUser($data,$userId);
            }else{
                $user_id = $this->roleAccess->saveUser($data);
            }    
            if(!empty($roleId)){
                $this->roleAccess->setUserRole($roleId, $user_id);
            }
            
            //===============================Mail to User =======================================
            if($userId==0){
                $template = EmailTemplate::where('Code','ET1000')->get();

                $emailVariable = array('firstName'=>$data['firstname'],'lastName'=>$data['lastname'],'username'=>$data['email'],'password'=>$password,'coupon_code'=>'12345');
                //mail($data['email'], $template[0]->Subject, $message);
                Mail::send(array('html'=>'emails.welcome'),$emailVariable,function($msg) use ($template,$data) {
                    $msg->to($data['email'])->subject($template[0]->Subject);

                });
            }
            if(is_numeric($user_id)){    
                return array('success',json_encode(array('user_id'=>$user_id,'username'=>$data['username']))); 
                exit;
            }else{
                return array('fail',array('messge'=>'Please try again'));
            }    
        }
       
    }

    function uploadProfilePic()
    {
       // echo "<pre>";        print_r(Input::file('file')); die;
        $filename = $this->_request->file('file')->getClientOriginalName();
        $destinationPath = $_SERVER['DOCUMENT_ROOT'].'/uploads/profile_picture/'; 
        $filename = date('YmdHis').$filename;
        $this->_request->file('file')->move($destinationPath, $filename);
        echo $filename; die;
        //echo $files['name']; die;
        //print_r($files); die;
    }
    
    public function edit_User($user_id)
    {   
        $startTime = $this->getTime();
        $user = User::find($user_id);
        $rolesIds = $this->roleAccess->getUserRoldIdByUserId($user_id);
        $rolesIds = !empty($rolesIds[0]) ? $rolesIds[0] : 0;

        $roles = $this->roleAccess->getRole();
        //dd($roles);
        if(Session::get('customerId') == 0) {
            $lookups = MasterLookup::where('category_id',7)->get();
            $customers = $this->custRepoObj->getAllCustomers();
                $locationsall= array(); 
                $businessunits= array();  
                $endTime = $this->getTime();
                DB::table('user_tracks')->insert(array('user_id'=>Session::get('userId'),'message'=>'Successfully Retrieved.','status'=>1,'created_on'=>date("Y-m-d H:i:s"),'manufacturer_id'=>Session::get('customerId'),'service_name'=>'Edit User','response_duration'=>($endTime - $startTime)));  
            return View::make('users.edit')->with(array('user'=>$user, 'roles'=>$roles,'lookups'=>$lookups,'customers'=>$customers,'rolesId'=>$rolesIds,'locationsall'=>$locationsall,'businessunits'=>$businessunits));
            exit;
        }else{
            //$locationsall=Location::where('manufacturer_id',Session::get('customerId'))->get();
             //$businessunits=BusinessUnit::where('manufacturer_id',Session::get('customerId'))->get();
$locationsall = DB::table('locations')
->join('location_types', 'location_types.location_type_id', '=', 'locations.location_type_id')
->where(['locations.manufacturer_id' => Session::get('customerId')])
->where('location_types.location_type_name', '<>', 'Customer')
->get();
             $businessunits = DB::table('business_units')->where('manufacturer_id',Session::get('customerId'))->get();
              $endTime = $this->getTime();
                DB::table('user_tracks')->insert(array('user_id'=>Session::get('userId'),'message'=>'Successfully Retrieved.','status'=>1,'created_on'=>date("Y-m-d H:i:s"),'manufacturer_id'=>Session::get('customerId'),'service_name'=>'Edit User','response_duration'=>($endTime - $startTime)));  
            return View::make('users.edit')->with(array('user'=>$user, 'roles'=>$roles,'rolesId'=>$rolesIds,'locationsall'=>$locationsall,'businessunits'=>$businessunits)); exit;
        } 
    }
    
    public function delete_User($userId)
    {   
        $startTime = $this->getTime();
        User::destroy($userId);
        $endTime = $this->getTime();
        DB::table('user_tracks')->insert(array('user_id'=>Session::get('userId'),'message'=>'Successfully Deleted.'.$userId,'created_on'=>date("Y-m-d H:i:s"),'service_name'=>'Delete User','manufacturer_id'=>Session::get('customerId'),'status'=>1,'response_duration'=>($endTime - $startTime)));
        return Redirect::to('/users');
    }


public function downloadbulkupdatetemplate(){
    ob_clean();
    ob_start();

    $file= public_path(). "/download/templates/usertemplate.xls";
        $headers = array(
              'Content-Type: application/vnd.ms-excel',
            );
        return response()->download($file, 'usertemplate.xls', $headers);

}


public function saveUsersFromExcel()
{
    //$request = new Request();
    $customer_id = Session::get('customerId');
    if($customer_id  == 0){
        $customer_id = $this->_request->input('customer_id');
    }
    else if(empty($customer_id)){
        return response()->json([
        'status'=>false,
        'msg' => 'session has been expired. Please login again'
        ]);
    }

$allowed_Extensions = ['xls','xlsx'];
    if( $this->_request->file('files')){
      $extension= File::extension($this->_request->file('files')->getClientOriginalName());
    if( !in_array($extension, $allowed_Extensions))
    {
       return response()->json([
        'status'=>false,
        'msg' => 'Invalid file given. Please Upload a valid .xls or .xlsx file'
        ]);
    }
    
        $path =  $this->_request->file('files')->getRealPath();

        $data = Excel::load($path, function($reader) {})->get();
        $error_messages = [];
        $insert = [];
//dd($data);
        if(!empty($data) && $data->count()){
            $data = $data->toArray();
            $count = 0;
            $headers =["user_name","password","first_name","last_name","email","erp_user_name","erp_password","location_erp_code","is_active","user_type","phone_number"];
            $excelheaders = array_keys($data[0]);
            if(count(array_diff($headers, $excelheaders)) >0){
                return response()->json([
                    'status'=>false,
                    'msg' => 'Some Headers are missing. Please Check'
                    ]);
            }
            $is_active_status =[1,0,'1','0'];
            foreach ($data as $key => $value) {
                $insert_status=true;

                if(!empty($value)){
                    $value['is_active'] = intval($value['is_active']);
                    //dd($value);
                    $location_id =DB::table('locations')->where('erp_code',$value['location_erp_code'])->value('location_id');
                    //$business_unit  = DB::table('business_units')->where('name',$value['business_unit'])->take(1)->pluck('business_unit_id');

                    if(empty($value['user_name'])){
                        $insert_status = false;
                        $error_messages[] = "User name should not be empty at row ".($key + 1);
                    }
                    if(User::where('username',$value['user_name'])->count()>0){
                        $insert_status = false;
                        $error_messages[] = "username ".$value['user_name']." is already Exists at row ".($key+1);
                    }

                    if(!filter_var($value['email'], FILTER_VALIDATE_EMAIL)) {
                        $insert_status = false;
                        $error_messages[] = "Email is not valid at row ".($key +1);
                    } 
                    // if(empty($business_unit)){
                    //     $insert_status = false;
                    //     $error_messages[] = "Business Unit ".$value['business_unit']." is not Valid at row ".($key+1);

                    // }
                    if(!in_array($value['is_active'], $is_active_status)){
                        $insert_status = false;
                        $error_messages[] = "Is Active should be either 0 or 1 at row ".($key+1);

                    }
                

                   if($insert_status){

                        $insert[] = ['username' => $value['user_name'],'password'=>md5($value['password']), 'firstname' => $value['first_name'],'lastname'=>$value['last_name'],'email'=>$value['email'],'erp_username'=>$value['erp_user_name'],'erp_password'=>$value['erp_password'],'location_id'=>$location_id,'is_active'=>$value['is_active'],'user_type'=>$value['user_type'],'phone_no'=>$value['phone_number'],'customer_id'=>$customer_id];
                        $count++;
                   }       

                        
                }

            }




            if(!empty($insert)){

                User::insert($insert);
                //$inserted_count = count($insert);
                //$faailed_count = count($data);
                
                //return "Inserted records";
                //return back()->with('success','Insert Record successfully.');

            }


        }
        else{
            return response()->json([
                    'status'=>false,
                    'msg' =>"Empty File given. Please check the file"
                    ]);
        }


    }
    else{
        return response()->json([
                    'status'=>false,
                    'msg' =>"No File given. Please Upload a valid .xls or .xlsx file"
                    ]);
    }
    $link ="";
    if(!empty($error_messages)){
        $time = Date('Ymdhis');
         ob_end_clean(); //for overcome the unformated data.
         ob_start();
         Excel::create('UsersBulkUpdateErrorLog'.$time, function($excel) use($error_messages) {
                    return $excel->sheet('New sheet', function($sheet) use($error_messages){
                    $sheet->loadView('products.productsbulkimporterror',array('error_messages'=>$error_messages));
                    });
                })->store('xls', public_path()."/download");//"C://Users/300137/Desktop");
         // ob_end_clean(); //for overcome the unformated data.
         // ob_start();
        $link = "product/error_log_link/UsersBulkUpdateErrorLog".$time.".xls";

       return response()->json([
        'status'=>true,
        'msg' =>" Success Records:".count($insert).", Failed Records:".(count($data) - count($insert)).". Please clik on the error link for errors",
        'link'=>$link
        ]);
    }


    return response()->json([
        'status'=>true,
        'msg' =>"Successfully added users"
        ]);

    }
    
    public function loginViaOauth() {
        $currentURL = url()->current();
        $checkURL = url('/').'/oauth/authorize';

        if($currentURL == $checkURL) {
            return abort(401);
        }
        $publicKey = DB::table('oauth_clients')->where('user_id',13)->value('public_key');
        $headerPublicKey = $this->_request->header('public_key');
        
        if($headerPublicKey === $publicKey) {
            return true;
        } else {
            return false;
        }
    }

    public function authorizeCheck($userID, $customerID) {
        
        $encryptUserID = Crypt::encryptString($userID);
        $encryptCustomerID = Crypt::encryptString($customerID);

        return view('redirection/index')
            ->with('encryptUserID', $encryptUserID)
            ->with('encryptCustomerID', $encryptCustomerID);
       // return redirect('/login/authorize/grant?userID='.$encryptUserID.'&customerID='.$encryptCustomerID);
    }

    public function authorizeGrant(Request $request) {
        sleep(4);
        $userid = $request->get('subscriberID');
        $customerID = $request->get('tenantID');

        try {
            $decryptUserID = Crypt::decryptString($userid);
            $decryptCustomerID = Crypt::decryptString($customerID);
        } catch(\Exception $ex) {
            return redirect('/logout');
        }
        
        
        if(Session::get('userId') == $decryptUserID) {
            return redirect('/');
        } else {
            return redirect('/logout');
        }
    }
}
