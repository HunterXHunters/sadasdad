<?php
namespace App\Models\Customers;
use App\Repositories\RoleRepo;
use App\Repositories\CustomerRepo;
use DB;
use Session;


class EsealCustomers extends \Eloquent
{
    protected $table = 'eseal_customer'; // table name
    protected $primaryKey = 'customer_id';
    public $timestamps = false;
    private $custRepo;
    private $roleRepo;
    private $_sap_api_repo;
    
    public function __construct()
    {
        $this->custRepo = new CustomerRepo;
        $this->roleRepo = new RoleRepo;
        $this->_sap_api_repo = new SapApiRepo;
    }

    public function saveCustomer($data, $returnObject)
    {
        try
        {
            $customerId = 0;
            $status = 1;                        
            if (isset($data['eseal_customers']))
            {
                $data['eseal_customers']['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
                $data['eseal_customers']['ip'] = Request::getClientIp();
                $data['eseal_customers']['status'] = $status;
                $customerId = isset($data['customer_id']) ? $data['customer_id'] : 0;
                if($customerId)
                {
                    $customerId = $this->roleRepo->decodeData($customerId);
                    $data['customer_id'] = $customerId;
                }
                $brandName = str_replace(' ', '_', $data['eseal_customers']['brand_name']).'_'.$customerId;
                $brandType = $data['eseal_customers']['customer_type_id'];
                $fileName = '';
                $file = isset($data['eseal_customers']['logo']) ? $data['eseal_customers']['logo'] : array();
                $customerData = $data['eseal_customers'];
                if (!empty($brandName) && !empty($file))
                {
                    $customerData['logo']=$data['eseal_customers']['logo'];
                    /*$fileName = $this->uploadLogo($file, $brandName);
                    $customerData['logo'] = $fileName;*/
                }
                if(!isset($data['customer_id'])){
                    $customerData['token'] = md5(uniqid(rand(),1));
                    $customerData['otp'] = rand(11111, 99999);
                }else{
                    $customerData['customer_id'] = isset($data['customer_id']) ? $data['customer_id'] : 0;
                }
                foreach ($customerData as $key => $value)
                {
                    if($key == 'product_types')
                    {
                        $value = implode(',', $value);
                    }
                    $this->$key = $value;
                }
                if(!isset($data['customer_id'])){
                    $this->save();                    
                }else{
                    if(isset($customerData['product_types']))
                    {
                        $customerData['product_types'] = implode(',', $customerData['product_types']);
                    }
                    $this->where('customer_id', $data['customer_id'])->update($customerData);
                    $returnObject->result = true;
                }
                $customerId = $this->customer_id;
                Log::info($customerId);
                Log::info('--------------');
                if (!isset($data['customer_id']) && $customerId)
                {                    
                    $this->saveCustomerProductPlan($customerId, $data, $returnObject);                    
                    $this->saveCustomerAddress($customerId, $data, $returnObject);                    
                    $message = $this->sendConfirmationEmail($customerId);
                    if(empty($message))
                    {
                        //$returnObject->message = 'Unable to send email.';
                    }
                }
                if(isset($data['customer_id']))
                {
                    $this->saveCustomerProductPlan($customerId, $data, $returnObject);
                    if (isset($data['customer_address']['address_id']) && $data['customer_address']['address_id'] != '')
                    {
                        $addressData = \DB::table('customer_address')->where('address_id', $data['customer_address']['address_id'])->first();
                        if ($addressData->address_id)
                        {
                            \DB::table('customer_address')->where('address_id', $data['customer_address']['address_id'])->update($data['customer_address']);
                        }
                        $returnObject->result = true;
                    }else{
                        $this->saveCustomerAddress($customerId, $data, $returnObject);
                    }
                }
                if ($returnObject->result)
                {
                    $returnObject->customer_id = $customerId;
                    $returnObject->message = 'Done saving customer';
                }
            } else
            {
                $returnObject->result = false;
                $returnObject->message = 'No Data in post form';
            }
            $returnObject->customer_id = $customerId;
            return $returnObject;
        } catch (\ErrorException $ex)
        {
            $returnObject->result = false;
            $returnObject->message = $ex->getMessage().' => '.$ex->getTraceAsString();
            return $returnObject;
        }
    }

    public function sendConfirmationEmail($customerId)
    {
        try
        {
            $customerDetails = $this->where('customer_id', $customerId)->first(array('firstname', 'lastname', 'email', 'phone', 'token', 'email'));
            if (!empty($customerDetails))
            {
                $token = $customerDetails->token;
                $username = $customerDetails->firstname.' '.$customerDetails->lastname;
                $url = action('CustomerController@confirmationForm', $token);
                \Mail::send('emails.customer', array('url' => $url, 'username' => $username), function($message) use ($customerDetails)
                {
                    $message->to($customerDetails->email)->subject('Registration successfull.');
                });
            }
        } catch (\ErrorException $ex)
        {
            return $ex->getMessage();
        }
    }
    
    public function createCustomerOrders($customerId, $data, $returnObject)
    {
        try
        {
            $priceDetails = isset($data['price_details']) ? $data['price_details'] : array();
            if(!empty($priceDetails))
            {
                $orderNames = ['AIDC', 'GDS', 'IOT'];
                $subscriptionId = DB::table('customer_ima')->orderBy('subscription_id', 'desc')->pluck('subscription_id');
                $latestSubscriptionId = ++$subscriptionId;
                $imaId = substr($latestSubscriptionId, -1);
                foreach($priceDetails as $details)
                {
                    $priceData = json_decode($details);
                    if($priceData && property_exists($priceData, 'name') && in_array(strtoupper($priceData->name), $orderNames))                            
                    {
                        $subscriptionPrice = 0.0000;
                        $eseal_price_master_id = property_exists($priceData, 'eseal_price_master_id') ? $priceData->eseal_price_master_id : 0;
                        $subscriptionPrice = DB::table('eseal_price_master')->where('id', $eseal_price_master_id)->pluck('price');
                        $insertData = array();
                        $insertData = [
                            'customer_id' => $customerId,
                            'start_date'=> date('Y-m-d H:i:s'),
                            'end_date'=> date('Y-m-d H:i:s'),
                            'annual_subscription_fee'=> $subscriptionPrice,
                            'agreement_type' => $priceData->name,
                            'ima_id'=> $imaId,
                            'subscription_id'=> $latestSubscriptionId,
                            'agreed_price' => $priceData->agreedPrice
                        ];
                        DB::table('customer_ima')->insert($insertData);                        
                    }
                }
            }
        } catch (\ErrorException $ex) {
            $returnObject->result = false;
            $returnObject->message = $ex->getMessage().' => '.$ex->getTraceAsString();
            return $returnObject;
        }
    }
    
    public function sendOtp($data)
    {
        try
        {
            $jsonReturn = array();
            if(isset($data['customer_id']))
            {
                $customerDetails = $this->where('customer_id', $data['customer_id'])->first(array('is_otp_sent', 'firstname', 'lastname', 'phone', 'otp', 'email'));
                if(!empty($customerDetails))
                {
                    if(isset($data['type']) && $data['type'] == 'resend')
                    {
                        $updateData['is_otp_sent'] = 0;
                        $this->where('customer_id', $data['customer_id'])->update($updateData);
                    }
                    $customerOtp = $customerDetails->otp;
                    $username = $customerDetails->firstname.' '.$customerDetails->lastname;
                    $isOtpSent = $customerDetails->is_otp_sent;
                    if(!$isOtpSent && $customerOtp)
                    {
                        $fields = array('otp' => $customerOtp, 'username' => $username);
                        $email['email'] = $customerDetails->email;
                        \Mail::send('emails.sendotp', $fields, function($message) use ($customerDetails)
                        {
                            $message->to($customerDetails->email)->subject('OTP for registration');
                        });
                        $updateData['is_otp_sent'] = 1;
                        $this->where('customer_id', $data['customer_id'])->update($updateData);
                        $jsonReturn['result'] = 1;
                        $jsonReturn['message'] = 'Mail sent';
                    }else{
                        $jsonReturn['result'] = 1;
                        $jsonReturn['message'] = 'OTP is Already sent please check your emails.';
                    }
                }else{
                    $jsonReturn['result'] = 0;
                    $jsonReturn['message'] = 'Customer doest not exist.';
                }
            }else{
                $jsonReturn['result'] = 0;
                $jsonReturn['message'] = 'Customer ID not passed.';
            }
            return $jsonReturn;
        } catch (\ErrorException $ex) {
            return $ex->getMessage();
        }         
    }
    
    public function validateOtp($data)
    {
        if(isset($data['otp']) && isset($data['customer_id']))
        {
            $customerDetails = $this->where('customer_id', $data['customer_id'])->where('otp', $data['otp'])->first(array('otp'));
            if(!empty($customerDetails))
            {
                $updateData['is_otp_approved'] = 1;
                $this->where('customer_id', $data['customer_id'])->update($updateData);
                return json_encode(array('result' => true));
            }
        }
        return json_encode(array('result' => false));
    }
    
    public function checkOtpVerified($data, $returnObject)
    {
        try
        {
            if(isset($data['eseal_customers']['customer_id']))
            {
                $result = $this->where('customer_id', $data['eseal_customers']['customer_id'])->first(array('otp', 'token', 'is_otp_approved'));
                if(!empty($result))
                {
                    if($result->is_otp_approved)
                    {
                        $returnObject->result = true;
                        $returnObject->message = 'OTP approved';
                    }else{
                        $returnObject->result = false;
                        $returnObject->message = 'No OTP Not approved';
                    }
                }
            }else{
                $returnObject->result = false;
                $returnObject->message = 'No Customer ID';
            }
            return $returnObject;
        } catch (\ErrorException $ex) {
            $returnObject->result = false;
            $returnObject->message = $ex->getMessage().' => '.$ex->getTraceAsString();
            return $returnObject;
        }
    }
    
    public function saveCustomerAddress($customerId, $data, $returnObject)
    {
        try
        {
            $customerAddress = isset($data['customer_address']) ? $data['customer_address'] : array();
            if (!empty($customerAddress))
            {
                $customerAddress['customer_id'] = $customerId;
                $customerAddress['firstname'] = isset($data['eseal_customers']['firstname']) ? $data['eseal_customers']['firstname'] : '';
                $customerAddress['lastname'] = isset($data['eseal_customers']['lastname']) ? $data['eseal_customers']['lastname'] : '';
                \DB::table('customer_address')->insert($customerAddress);
            }
        } catch (\ErrorException $ex)
        {
            $returnObject->result = false;
            $returnObject->message = $ex->getMessage().' => '.$ex->getTraceAsString();
            return $returnObject;
        }
    }

    public function saveCustomerProductPlan($customerId, $data, $returnObject)
    {
        try
        {
            $customerTypeId = isset($data['eseal_customers']['customer_type_id']) ? $data['eseal_customers']['customer_type_id'] : '';
            $currencyCode = isset($data['currency_code']) ? $data['currency_code'] : 4;
            $priceDetails = isset($data['price_details']) ? $data['price_details'] : array();
            $taxClassDetails = isset($data['tax_class_id']) ? implode(',', $data['tax_class_id']) : 0;
            $priceMaster = new \Customers\PriceMaster();
            
            if (!empty($priceDetails))
            {
                foreach ($priceDetails as $jsonString)
                {
                    $masterData = json_decode($jsonString);
                    $esealPriceMasterId = $masterData->eseal_price_master_id;
                    $agreedPrice = $masterData->agreedPrice;
                    $validFrom = $masterData->priceFrom;
                    $validTo = $masterData->priceTo;
                    $getMasterData = $priceMaster->getPriceData($esealPriceMasterId);
                    if (!empty($getMasterData))
                    {
                        unset($getMasterData['id']);
                        unset($getMasterData['product_lookup_id']);
                        unset($getMasterData['customer_type_lookup_id']);
                        unset($getMasterData['component_type_lookup_id']);
                        unset($getMasterData['product_lookup_id']);
                        unset($getMasterData['tax_class_id']);

                        $getMasterData['customer_id'] = $customerId;
                        $getMasterData['customer_type_id'] = $customerTypeId;
                        $getMasterData['currency_code'] = $currencyCode;
                        $getMasterData['created_by'] = 1;
                        $getMasterData['created_on'] = 1;
                        $getMasterData['product_plan_id'] = $esealPriceMasterId;
                        $getMasterData['tax_class_id'] = $taxClassDetails;
                        $getMasterData['agreed_price'] = $agreedPrice;
                        $getMasterData['valid_from'] = $validFrom;
                        $getMasterData['valid_upto'] = $validTo;
                        DB::table('customer_products_plans')->insert($getMasterData);
                        $this->createCustomerOrders($customerId, $data, $returnObject);
                    } else
                    {
                        $returnObject->message = 'Wrong Product Id';
                        return $returnObject->result = false;                        
                    }
                }
                return $returnObject->result = true;
            } else
            {
                return $returnObject->result = false;
            }
        } catch (\ErrorException $ex)
        {
            $returnObject->result = false;
            $returnObject->message = $ex->getMessage().' => '.$ex->getTraceAsString();
            return $returnObject;
        }
    }
    
    public function allocateTableData($customerId, $returnObject, $is_manufacturer)
    {
        try
        {
            if($is_manufacturer)
            {
                $defaultRoles = array('COMPANY ADMIN ROLE', 'PRODUCTION SUPERVISOR', 'LOGISTIC SUPERVISORS', 'SUPPLY CHAIN MANAGERS', 'FINANCE MANAGERS', 'FIELD MANAGERS');
            }else{
                $defaultRoles = array('COMPANY ADMIN ROLE');
            }
            
            foreach($defaultRoles as $role)
            {
                $allowRolesCreation = $this->checkLocationTypesAdded($customerId, 'roles', $role);
                $lastInsertedId = 0;
                $userId = 0;
                if($allowRolesCreation)
                {
                    $userDetails = DB::table('users')->where('customer_id', $customerId)->first(array('user_id'));
                    if(!empty($userDetails))
                    {
                        $userId = $userDetails->user_id;
                    }else
                    {
                        $userArray = $this->where('customer_id', $customerId)->first(array('customer_id', 'firstname', 'lastname', 'email'));
                        if (!empty($userArray))
                        {
                            $username = $userArray->firstname.'_'.$userArray->lastname;
                            $password = 'e$e@l123';
                            $userId = $this->createNewUserWithRole($userArray, $returnObject, $password, $username);
                        }
                    }
                    $lastInsertedId = $this->roleCreateAssign($returnObject, $customerId, $role);
                    $userRoleArray = array();
                    $userRoleArray['user_id'] = $userId;
                    $userRoleArray['role_id'] = $lastInsertedId;
                    \DB::table('user_roles')->insert($userRoleArray);
                }else{
                    $roleDetails = DB::table('roles')->where('manufacturer_id', $customerId)->where('description', $role)->first(array('role_id'));
                    if(!empty($roleDetails))
                    {
                        $lastInsertedId = $roleDetails->role_id;
                    }
                }
            }
            
            $existingLocationTypes = DB::table('location_types')->where('manufacturer_id', $customerId)->first(array(DB::Raw('group_concat(distinct "\'" , location_type_name , "\'") as location_type_names')));
            $insertLocationTypeArray = array();
            if(!empty($existingLocationTypes) && property_exists($existingLocationTypes, 'location_type_names') && !empty($existingLocationTypes->location_type_names))
            {
                //'insert into location_types (location_type_name, manufacturer_id) (select location_type_name, '.$customerId.' from location_types where manufacturer_id = 0 and location_type_name not in '.$existingLocationTypes->location_type_names.')'
                $locationTypeResult = DB::select( DB::raw('select location_type_name, '.$customerId.' as manufacturer_id from location_types where manufacturer_id = 0 and location_type_name not in ('.$existingLocationTypes->location_type_names.')'));
            }else{
                //DB::Raw('insert into location_types (location_type_name, manufacturer_id) (select location_type_name, '.$customerId.' from location_types where manufacturer_id = 0');
                $locationTypeResult = DB::select( DB::raw('select location_type_name, '.$customerId.' as manufacturer_id from location_types where manufacturer_id = 0'));
            }
            $insertLocationTypeArray = json_decode(json_encode($locationTypeResult), true);
            if(!empty($insertLocationTypeArray))
            {
                DB::table('location_types')->insert($insertLocationTypeArray);
            }            
            $allowLocationCreation = $this->checkLocationTypesAdded($customerId, 'locations');
            if($allowLocationCreation)
            {
                $customerDetails = $this->getCustomerDetails($customerId);
                $firstName = '';
                $lastName = '';
                $email = '';
                $locationTypeId = '';
                if(!empty($customerDetails))
                {
                    $firstName = isset($customerDetails['customer']->firstname) ? $customerDetails['customer']->firstname : '';
                    $lastName = isset($customerDetails['customer']->lastname) ? $customerDetails['customer']->lastname : '';
                    $email = isset($customerDetails['customer']->email) ? $customerDetails['customer']->email : '';
                }
                $locationTypeIdData = DB::table('location_types')->where('manufacturer_id', $customerId)->where('location_type_name', 'Corporate')->first(array('location_type_id'));
                if(!empty($locationTypeIdData))
                {
                    $locationTypeId = $locationTypeIdData->location_type_id;
                }
                $existsLocation = DB::table('locations')->where(array('location_name' => 'Corporate', 'manufacturer_id' => $customerId))->pluck('location_id');
                if(!empty($existsLocation))
                {
                    DB::statement('INSERT INTO locations (location_name, firstname, lastname, location_email, manufacturer_id, location_type_id) (select location_name, "'.$firstName.'", "'.$lastName.'", "'.$email.'", "'.$customerId.'", "'.$locationTypeId.'" from locations where manufacturer_id = 0 and location_type_id = 0)');
                }                
            }
            
            $existingTransactionMaster = DB::table('transaction_master')->where('manufacturer_id', $customerId)->first(array(DB::Raw('group_concat(distinct "\'" , name , "\'") as name')));
            $insertTransactionMasterArray = array();
            if(!empty($existingTransactionMaster) && property_exists($existingTransactionMaster, 'name') && !empty($existingTransactionMaster->name))
            {
                $transactionMasterResult = DB::select( DB::raw('select name, description, action_code, srcLoc_action, dstLoc_action, intrn_action, `group`, '.$customerId.' as manufacturer_id, feature_code from transaction_master where manufacturer_id = 0 and name not in ('.$existingTransactionMaster->name.')'));
            }else{
                $transactionMasterResult = DB::select( DB::raw('select name, description, action_code, srcLoc_action, dstLoc_action, intrn_action, `group`, '.$customerId.' as manufacturer_id, feature_code from transaction_master where manufacturer_id = 0'));
            }
            $insertTransactionMasterArray = json_decode(json_encode($transactionMasterResult), true);
            if(!empty($insertTransactionMasterArray))
            {
                DB::table('transaction_master')->insert($insertTransactionMasterArray);
            }
                  
            return $lastInsertedId;
        } catch (\ErrorException $ex) {
            $returnObject->result = false;
            $returnObject->message = $ex->getMessage().' => '.$ex->getTraceAsString();
            return $returnObject;
        }
    }
    
    public function deleteCustomer($customerId)
    {
        try
        {
            if($customerId)
            {
                $parent = DB::table('eseal_customer')->where('customer_id', $customerId)->get();
                $child = DB::table('eseal_customer')->where('parent_company_id', $customerId)->get();
                if ($parent[0]->parent_company_id == -1)
                {
                    $this->where('customer_id', $customerId)->update(array('is_deleted' => 1,'status'=>0));
                    $this->where('parent_company_id', $customerId)->update(array('is_deleted' => 1,'status'=>0));
                    DB::table('users')->where('customer_id', $customerId)->update(array('is_active' => 0));
                    DB::table('users')->where('customer_id', $child[0]->customer_id)->update(array('is_active' => 0));
                } else
                {
                    $this->where('customer_id', $customerId)->update(array('is_deleted' => 1,'status'=>0));
                    DB::table('users')->where('customer_id', '=', $customerId)->update(array('is_active' => 0));
                    return 1;
                }
            }
        } catch (Exception $ex) {
            return $ex->getMessage();
        }
    }
    
    public function restoreCustomer($customerId)
    {
        try
        {
            if($customerId)
            {                
                $parent = DB::table('eseal_customer')->where('customer_id', $customerId)->get();
                $child = DB::table('eseal_customer')->where('parent_company_id', $customerId)->get();
                if($parent[0]->parent_company_id==-1){             
                    $this->where('customer_id', $customerId)->update(array('is_deleted'=>0,'status'=>1));
                    $this->where('parent_company_id', $customerId)->update(array('is_deleted'=>0,'status'=>1));
                    DB::table('users')->where('customer_id',$customerId)->update(array('is_active'=>1));
                    DB::table('users')->where('customer_id',$child[0]->customer_id)->update(array('is_active'=>1));
                }
                else{
                $this->where('customer_id', $customerId)->update(array('is_deleted'=>0, 'status'=>1));
                DB::table('users')->where('customer_id','=',$customerId)->update(array('is_active'=>1));
                //return 1;
                }
            }else{
                return 'No Customer Id';
            }
        } catch (\ErrorException $ex) {
            return $ex->getMessage();
        }
    }

    public function roleCreateAssign($returnObject, $customerId, $roleName)
    {
        try
        {
            $lastInsertedId = -1;
            $lastInsertedId = DB::table('roles')->where('manufacturer_id', $customerId)->where('name', $roleName)->pluck('role_id');
            if(empty($lastInsertedId))
            {
                DB::statement('insert into roles (name, description, manufacturer_id, is_grant_allowed, is_active,created_by, created_on, role_type, parent_role_id ) (select name, description, '.$customerId.', is_grant_allowed, is_active, 0, now(), role_type, parent_role_id from roles where manufacturer_id = 0 and upper(name) = "'.$roleName.'")');
                $lastInsertedId = DB::getPdo()->lastInsertId();
            }
            $insertRoleAccessArray = array();
            if(!empty($lastInsertedId))
            {
                $defaultRoleId = 0;
                $defaultRoleData = DB::select( DB::raw('select role_id from roles where manufacturer_id = 0 and upper(name) = "'.$roleName.'"'));
                if(!empty($defaultRoleData) && isset($defaultRoleData[0]))
                {
                    $defaultRoleId = $defaultRoleData[0]->role_id;
                }
                if($defaultRoleId != 0)
                {
                    $existingFeatures = DB::table('role_access')->where('role_id', $lastInsertedId)->first(array(DB::Raw('group_concat(feature_id) as feature_id')));
                    if(empty($existingFeatures))
                    {
                        $roleAccessResult = DB::table('role_access')->where('role_id', $defaultRoleId)->get(array(DB::raw($lastInsertedId." as role_id "), 'feature_id'));
                    }else if(property_exists($existingFeatures, 'feature_id')){
                        $roleAccessResult = DB::table('role_access')->where('role_id', $defaultRoleId)->whereNotIn('feature_id', explode(',', $existingFeatures->feature_id))->get(array(DB::raw($lastInsertedId." as role_id "), 'feature_id'));
                    }
                    $insertRoleAccessArray = json_decode(json_encode($roleAccessResult), true);
                }
            }
            if(!empty($insertRoleAccessArray))
            {
                DB::table('role_access')->insert($insertRoleAccessArray);
            }            
            return $lastInsertedId;
        } catch (\ErrorException $ex) {
            $returnObject->result = false;
            $returnObject->message = $ex->getMessage().' => '.$ex->getTraceAsString();
            return $returnObject;
        }
    }
    
    public function checkLocationTypesAdded($customerId, $tableName, $roleName = null)    
    {
        try
        {
            if($roleName)
            {
                $locationTypes = DB::table($tableName)->where('manufacturer_id', $customerId)->where('description', $roleName)->get();
            }else{
                $locationTypes = DB::table($tableName)->where('manufacturer_id', $customerId)->get();
            }
            if(!empty($locationTypes))
            {
                return 0;                
            }else{
                return 1;
            }
        } catch (\ErrorException $ex)
        {
            echo "<pre>";print_R($ex);die;
        }
    }
    
    public function getCustomerLocations($manufacturerId)
    {
        try
        {
            if($manufacturerId)
            {
                $locationDataArray = array();
                $locationData = DB::table('locations')->where('manufacturer_id', $manufacturerId)->where('location_type_id', '!=', 874)->get(array('location_id', 'location_name'));
                if(!empty($locationData))
                {
                    foreach($locationData as $location)
                    {
                        $locationDataArray[$location->location_id] = $location->location_name;
                    }                    
                }
                return $locationDataArray;
            }
        } catch (\ErrorException $ex) {

        }
    }
    
    public function addLocationTypes($data)
    {
        try
        {
            $locationTypeId = DB::table('location_types')->insertGetId([
                    'location_type_name' => $data['location_type_name'],
                    'manufacturer_id' => $data['manufacturer_id']
            ]);
            return $locationTypeId;
        } catch (\ErrorException $ex)
        {
            return $ex->getMessage();
        }
    }

    public function createNewUserWithRole($userObj, $returnObject, $password, $username)
    {
        try
        {
            // create new user with the role on the user type  
            $userArray['username'] = $username;
            $userArray['firstname'] = $userObj->firstname;
            $userArray['lastname'] = $userObj->lastname;
            $userArray['email'] = $userObj->email;
            $userArray['customer_id'] = $userObj->customer_id;
            $userArray['password'] = md5($password);
            return DB::table('users')->insertGetId($userArray);            
        } catch (\ErrorException $ex)
        {
            $returnObject->result = false;
            $returnObject->message = $ex->getMessage().' => '.$ex->getTraceAsString();
            return $returnObject;
        }
    }
    
    public function updateRoleData($customerId, $returnObject, $roleId = null)
    {
        try
        {
            if(!empty($customerId))
            {
                $userData = DB::table('users')->where('customer_id', $customerId)->first(array('user_id'));
                if (!empty($userData))
                {
                    $userUpdates['is_active'] = 1;
                    \DB::table('users')->where('customer_id', $customerId)->update($userUpdates);                    
                    $this->where('customer_id', $customerId)->update(array('approved' => 1));
                    $returnObject->result = true;
                }
            }
        } catch (\ErrorException $ex) {
            $returnObject->result = false;
            $returnObject->message = $ex->getMessage().' => '.$ex->getTraceAsString();
            return $returnObject;
        }
        
    }

    public function uploadLogo($file, $brandName)
    {
        try
        {
            // getting all of the post data
            // setting up rules
            $rules = array('image' => 'required',); //mimes:jpeg,bmp,png and for max size max:10000
            // doing the validation, passing post data, rules and the messages
            if (!empty($file))
            {
                $destinationPath = public_path() . '/uploads/customers/'; // upload path
                $folderName = $destinationPath . $brandName;
                if (!file_exists($folderName))
                {
                    $result = \File::makeDirectory($folderName, 0777);
                    if ($result)
                    {
                        $fileDetails = pathinfo($file);
                        $extension = isset($fileDetails['extension']) ? $fileDetails['extension'] : 'JPG';            
                        $fileName = rand(11111, 99999) . '.' . $extension; // renameing image
                        //$file->move($destinationPath.$product_folder, $fileName); // uploading file to given path
                        if (file_exists($file))
                        {
                            copy($file, $folderName . '/' . $fileName);
                            if (!file_exists($folderName . '/' . $fileName))
                            {
                                Log::info('Not moved');
                                //die('not moved');
                            }else{
                                @unlink($file);
                                @unlink(str_replace('/thumbnail', '', $file));
                            }
                        } else
                        {
                            Log::info('file not exists');
                            //die('file not exists');
                        }
                        // sending back with message
                        return $brandName . '/' . $fileName;
                    } else
                    {
                        Log::info('Not able to create folder');
                        return 'Not able to create folder';
                    }
                } else
                {
                    $fileDetails = pathinfo($file);
                    $extension = isset($fileDetails['extension']) ? $fileDetails['extension'] : 'JPG';            
                    $fileName = rand(11111, 99999) . '.' . $extension; // renameing image
                    //$file->move($destinationPath.$product_folder, $fileName); // uploading file to given path
                    if(file_exists($file))
                    {
                        copy($file, $folderName.'/'.$fileName);
                        if(!file_exists($folderName.'/'.$fileName))
                        {
                            Log::info('file not moved');
                            //die('not moved');
                        }else{
                                @unlink($file);
                                @unlink(str_replace('/thumbnail', '', $file));
                            }
                    }else{
                        Log::info('file not exists');
                        //die('file not exists');
                    }
                    /*$extension = $file->getClientOriginalExtension(); // getting image extension
                    $fileName = rand(11111, 99999) . '.' . $extension; // renameing image
                    $file->move($folderName, $fileName); // uploading file to given path*/
                    // Resize logo
                    //$image = \Image::make($folderName.'/'.$fileName)->resize(111, 37)->save($folderName.'/'.$fileName);
                    //$image->destroy();
                    // sending back with message
                    return $brandName . '/' . $fileName;
                }
            } else
            {
                Log::info('no file');
                // sending back with error message.
                return false;
            }
        } catch (\ErrorException $ex)
        {
            $returnObject->result = false;
            $returnObject->message = $ex->getMessage().' => '.$ex->getTraceAsString();
            Log::info($ex->getTraceAsString());
            return $returnObject;
        }
    }

    public function createTableSchema($manufacturerId, $returnObject)
    {
        try
        {
            // creating eseal_data_id table for the Manufacturer
            $manufacturerTableName = 'eseal_' . $manufacturerId;
            if (!\Schema::hasTable($manufacturerTableName))
            {
                $DefaultTableName = 'eseal_id';
                $query = 'CREATE TABLE ' . $manufacturerTableName . ' LIKE ' . $DefaultTableName;
                \DB::statement($query);
            } else if (\Schema::hasTable($manufacturerTableName))
            {
                
            }else{
                $returnObject->message = 'Unable to create eseal_data table';
            }

            // creating eseal_bank table for the Manufacturer
            $esealBankName = 'eseal_bank_' . $manufacturerId;
            if (!\Schema::hasTable($esealBankName))
            {
                $coreTableName = 'eseal_bank';
                $query = 'CREATE TABLE ' . $esealBankName . ' LIKE ' . $coreTableName;
                \DB::statement($query);
            } else if (\Schema::hasTable($esealBankName))
            {
                
            }else{
                $returnObject->message = 'Unable to create eseal_bank table';
            }

//            // creating eseal_bank table for the Manufacturer
//            $esealTable = 'eseal_pregenerated_' . $manufacturerId;
//            if (!\Schema::hasTable($esealTable))
//            {
//                $coreeSeaName = 'eseal_pregenerated_ids';
//                $query = 'CREATE TABLE ' . $esealTable . ' LIKE ' . $coreeSeaName;
//                \DB::select(DB::raw($query));
//                return true;
//            } else
//            {
//                $returnObject->message = 'Unable to create eseal table';
//            }bo admin
            return $returnObject->result = true;
        } catch (\ErrorException $ex)
        {
            $returnObject->result = false;
            $returnObject->message = $ex->getMessage().' => '.$ex->getTraceAsString();
            return $returnObject;
        }
    }

    public function getCustomerDetailsWithToken($token)
    {
        return $this->where('token', $token)->first();
    }

    public function updateCustomerConfirmationDetails($data, $returnObject)
    {
        try
        {
            $customerId = 0;
            if (!empty($data))
            {
                if (isset($data['eseal_customers']['customer_id']))
                {
                    $customerData = $this->where('customer_id', $data['eseal_customers']['customer_id'])->first();
                    if ($customerData->customer_id)
                    {
                        $customerId = $customerData->customer_id;
                        $username = isset($data['eseal_customers']['username']) ? $data['eseal_customers']['username'] : '';
                        $password = isset($data['eseal_customers']['password']) ? $data['eseal_customers']['password'] : '';
                        unset($data['eseal_customers']['username']);
                        unset($data['eseal_customers']['password']);
                        if(isset($data['eseal_customers']['firstname']))
                        {
                            unset($data['eseal_customers']['firstname']);
                        }
                        $this->where('customer_id', $customerData->customer_id)->update($data['eseal_customers']);
                        $this->updateCustomerUserDetails($customerId, $password, $username, $returnObject);
                    }
                }
                if (isset($data['customer_address']['address_id']))
                {
                    $addressData = \DB::table('customer_address')->where('address_id', $data['customer_address']['address_id'])->first();
                    if ($addressData->address_id)
                    {
                        \DB::table('customer_address')->where('address_id', $data['customer_address']['address_id'])->update($data['customer_address']);
                    }
                }
                $returnObject->result = 'Sucess';
                $returnObject->customer_id = $customerId;
                $returnObject->message = 'Done updating customer';
            }else{
                $returnObject->result = 'Failure';
                $returnObject->customer_id = $customerId;
                $returnObject->message = 'No Data in post parameters';
            }            
            return $returnObject;
        } catch (\ErrorException $ex)
        {
            $returnObject->result = false;
            $returnObject->message = $ex->getMessage().' => '.$ex->getTraceAsString();
            return $returnObject;
        }
    }
    
    public function updateCustomerUserDetails($customerId, $password, $username, $returnObject)
    {
        try
        {
            if (isset($customerId))
            {
                $userDetails = \DB::table('users')->where('customer_id', $customerId)->first(array('email'));
                if(empty($userDetails))
                {
                    $userArray = $this->where('customer_id', $customerId)->first(array('customer_id', 'firstname', 'lastname', 'email'));
                    if (!empty($userArray))
                    {
                        $this->createNewUserWithRole($userArray, $returnObject, $password, $username);
                    }
                }else{
                    if($password != '')
                    {
                        $data['password'] = md5($password);
                    }
                    $data['is_active'] = 1;
                    \DB::table('users')->where('customer_id', $customerId)->update($data);
                }
                $returnObject->result = true;
                $returnObject->customer_id = $customerId;
                $returnObject->message = 'Done updating customer';
            }else{
                $returnObject->result = false;
                $returnObject->customer_id = $customerId;
                $returnObject->message = 'Done updating customer';            
            }
            return $returnObject;
        } catch (\ErrorException $ex) {
            $returnObject->result = false;
            $returnObject->message = $ex->getMessage().' => '.$ex->getTraceAsString();
            return $returnObject;
        }
    }  
    
    public function saveLocationFromErp($data)
    {
        try
        {
            if(!empty($data))
            {
                $vendor = isset($data['vendor']) ? 1 : 0;
                $plant = isset($data['plant']) ? 1 : 0;
                $customer = isset($data['customer']) ? 1 : 0;
                $manufacturerId = isset($data['manufacturer_id']) ? $data['manufacturer_id'] : 0;
                if(strlen($manufacturerId) > 15)
                {
                    $manufacturerId = $this->roleRepo->decodeData($manufacturerId);
                }
                $message = '';
                if($vendor)
                {
                    $locationTypeId = isset($data['location_type_id_vendor']) ? $data['location_type_id_vendor'] : 0;
                    $message = $message . $this->getVendorDetails($locationTypeId, $manufacturerId);
                }
                if($plant)
                {
                    $locationTypeId = isset($data['location_type_id_plant']) ? $data['location_type_id_plant'] : 0;
                    $message = $message . $this->getPlantDetails($locationTypeId, $manufacturerId);
                }
                if($customer)
                {
                    $locationTypeId = isset($data['location_type_id_customer']) ? $data['location_type_id_customer'] : 0;
                    $message = $message . $this->getCustomersDetails($locationTypeId, $manufacturerId);
                }
            }            
            return \Response::json([
                'status' => true,
                'message' => $message
            ]);
        } catch (\ErrorException $ex) {
            return \Response::json([
                'status' => false,
                'message' => $ex->getMessage()
            ]);
        }
    }
    
    public function getVendorDetails($locationTypeId, $manufacturerId)
    {
        try
        {
            $comp_code = DB::table('erp_integration')->where('manufacturer_id',$manufacturerId)->pluck('company_code');
            $method = 'Z032_ESEAL_GET_VENDOR_DETAILS_SRV';
            $method_name = 'GET_VENDOR_DETAILS';
            $data =['FROM_DATE'=>"datetime'2015-08-15T00:00:00'",'TO_DATE'=>"datetime'".date('Y-m-d',strtotime(date('Y-m-d') . "+1 days"))."T00:00:00'",'COMP_CODE'=>(int)$comp_code];
            $data1 = ['location_type_id'=>$locationTypeId,'manufacturer_id'=>$manufacturerId];
            $response = $this->_sap_api_repo->callSapApi($method,$method_name,$data,$data1,$manufacturerId);
            
            return $response;
        }   catch (\ErrorException $ex) {
            return $ex->getMessage();
        }
    }

    public function getCustomersDetails($locationTypeId, $manufacturerId)
    {
        try
        {
            $start_date = DB::table('locations')->where(['manufacturer_id'=>$manufacturerId,'location_type_id'=>$locationTypeId])->max('created_date');
            $start_date = date("Y-m-d", strtotime($start_date));
            $comp_code = DB::table('erp_integration')->where('manufacturer_id',$manufacturerId)->pluck('company_code');
            $method = 'ZESEAL_026_CUSTOMER_LIST_ORNT_SRV';
            $method_name = 'CUSTOMER';
            $data =['FROM_DATE'=>"datetime'".$start_date."T00:00:00'",'TO_DATE'=>"datetime'".date('Y-m-d',strtotime(date('Y-m-d')."+1 days" ))."T00:00:00'",'COMPANYCODE'=>(int)$comp_code];
            //$data =['FROM_DATE'=>"datetime'2016-01-18T00:00:00'",'TO_DATE'=>"datetime'2016-07-29T00:00:00'",'COMPANYCODE'=>(int)$comp_code];
            $data1 = ['location_type_id'=>$locationTypeId,'manufacturer_id'=>$manufacturerId];
            $response = $this->_sap_api_repo->callSapApi($method,$method_name,$data,$data1,$manufacturerId);

            return $response;
            } catch (\ErrorException $ex) {
            return $ex->getMessage();
        }
    }
    
    public function getPlantDetails($locationTypeId, $manufacturerId)
    {
        try
        {

            $comp_code = DB::table('erp_integration')->where('manufacturer_id',$manufacturerId)->pluck('company_code');
            $method = 'Z027_ESEAL_GET_PLANT_ORNT_SRV';
            $method_name = 'PLANT_DATA';
            $data =['COMPANYCODE'=>(int)$comp_code];
            $data1 = ['location_type_id'=>$locationTypeId,'manufacturer_id'=>$manufacturerId];
            $response = $this->_sap_api_repo->callSapApi($method,$method_name,$data,$data1,$manufacturerId);

            return $response;   
                       
        } catch (\ErrorException $ex) {

            return $ex->getMessage();
        }
    }
        
    
    public function getCitiesList($data)
    {
        try
        {
            $countryId = isset($data['countryId']) ? $data['countryId'] : 0;
            $stateId = isset($data['stateId']) ? $data['stateId'] : array();
            $stateDescription = isset($data['stateDescription']) ? $data['stateDescription'] : 0;
            $locationId = isset($data['locationId']) ? $data['locationId'] : 0;
            $manufacturerId = isset($data['manufacturerId']) ? $data['manufacturerId'] : 0;
            $citiesArray = array();
            $tempArray = array();
            if($countryId && !empty($stateId))
            {
                $selectedData = DB::table('location_city_mapping')
                    ->whereIn('state_id', $stateId)
                    ->where('manufacturer_id', $manufacturerId)
                    ->where('location_id', $locationId)
                    ->first(array(DB::Raw('group_concat(state_id, "_" ,cities) as cities')));
                foreach ($stateDescription as $stateName)
                {
                    $state_id = '';
                    $cities = '';
                    $stateIdData = '';
                    $tempArray = array();
                    $stateName = strtoupper($stateName);
                    $stateIdData = DB::table('zone')
                        ->where('name', $stateName)
                        ->where('country_id', $countryId)
                        ->first(array('zone_id'));
                    $cities = DB::table('cities_pincodes')
                        ->where('country_id', $countryId)
                        ->where('State', $stateName)
                        ->groupBy('City')
                        ->get(array('city_id', 'City'));
                    if(!empty($stateIdData))
                    {
                        $state_id = $stateIdData->zone_id;
                    }
                    foreach ($cities as $citi)
                    {
                        $tempArray[$state_id.'_'.$citi->city_id] = $citi->City;
                    }
                    if(!empty($tempArray))
                    {
                        $citiesArray['cities'][$stateName] = $tempArray;
                    }
                }                
                if(!empty($selectedData))
                {
                    $citiesArray['selected'] = $selectedData->cities;
                }
            }else{
                $citiesArray = 'No Data';
            }
            return $citiesArray;
        } catch (\ErrorException $ex)
        {
            return $ex->getMessage();
        }
    }
    
    public function saveLocationCity($data)
    {
        try
        {
            $location_id = isset($data['location_id']) ? $data['location_id'] : '';
            $manufacturer_id = isset($data['manufacturer_id']) ? $this->roleRepo->decodeData($data['manufacturer_id']) : '';
            $state_id = isset($data['state_id']) ? $data['state_id'] : '';
            $cities = isset($data['cities']) ? $data['cities'] : '';
            $tempArray = array();
            $insertArray = array();
            if(!empty($cities))
            {
                $insertCityData = array();
                $getDiffCityData = array();
                $existingCityList = array();
                $citiesListData = $cities;
                $citiesData = \DB::table('location_city_mapping')->whereIn('state_id', $state_id)->where('manufacturer_id', $manufacturer_id)->first(array(DB::Raw('group_concat(state_id,"_",cities) as cities')));
                
                if(property_exists($citiesData, 'cities') && $citiesData->cities != '')
                {
                    $existingCityList = explode(',', $citiesData->cities);
                    $getDiffCityData = array_diff($existingCityList, $citiesListData);
                }
                foreach($citiesListData as $cityId)
                {                    
                    $tempArray = array();
                    if(!empty($existingCityList) && in_array($cityId, $existingCityList))
                    {
                    }else{
                        $cityData = explode('_', $cityId);
                        if(!empty($cityData))
                        {
                            $cityId = isset($cityData[1]) ? $cityData[1] : 0;
                            $state_id = isset($cityData[0]) ? $cityData[0] : 0;
                        }
                        $tempArray['manufacturer_id'] = $manufacturer_id;
                        $tempArray['location_id'] = $location_id;
                        $tempArray['state_id'] = $state_id;
                        $tempArray['cities'] = $cityId;
                    }
                    if(!empty($tempArray))
                    {
                        $insertCityData[] = $tempArray;
                    }
                }
                if(!empty($insertCityData))
                {
                    DB::table('location_city_mapping')->insert($insertCityData);
                }
                if(!empty($getDiffCityData))
                {
                    $diffCitiesArray = array();
                    foreach($getDiffCityData as $diffCities)
                    {
                        $diffCitiesData = explode('_', $diffCities);
                        $diffCitiesArray[] = isset($diffCitiesData[1]) ? $diffCitiesData[1] : 0;
                    }
                    DB::table('location_city_mapping')->where('manufacturer_id', $manufacturer_id)->whereIn('cities', $diffCitiesArray)->delete();
                }
                return 'Sucesss';
                /*foreach($cities as $city)
                {
                    $tempArray['manufacturer_id'] = $manufacturer_id;
                    $tempArray['location_id'] = $location_id;
                    $tempArray['state_id'] = $state_id;
                    $tempArray['cities'] = $city;
                    $insertArray[] = $tempArray;
                }
                if(!empty($insertArray))
                {
                    DB::table('location_city_mapping')->insert($insertArray);
                    return 'Sucesss';
                }*/
            }else{
                $selectedData = DB::table('location_city_mapping')
                    ->where('state_id', $state_id)
                    ->where('manufacturer_id', $manufacturer_id)
                    ->where('location_id', $location_id)
                    ->first(array(DB::Raw('group_concat(cities) as cities')));
                if(!empty($selectedData))
                {
                    DB::table('location_city_mapping')->where('manufacturer_id', $manufacturer_id)->where('location_id', $location_id)->delete();
                }
                return 'Sucesss';
            }
            return 'Failed';
        } catch (\ErrorException $ex) {
            return $ex->getMessage();
        }
    }

    public function getCustomerType($customerTypeId)
    {
        $data = DB::table('master_lookup')->where('value', '=', $customerTypeId)->first(array('name'));
        if (!empty($data))
        {
            return $data->name;
        } else
        {
            return '';
        }
    }
    
    public function validateBrandOwner($data)
    {
        if(!empty($data))
        {
            $brandName = isset($data['brand_name']) ? $data['brand_name'] : '';
            $manufacturerId = isset($data['customer_id']) ? $this->roleRepo->decodeData($data['customer_id']) : '';
            $customerId = DB::table('eseal_customer')->where('brand_name', $brandName)->where('customer_id', '!=', $manufacturerId)->pluck('customer_id');
            if(empty($customerId))
            {
                return json_encode([ "valid" => true ]);
            }else{
                return json_encode([ "valid" => false ]);
            }
        }else{
            return json_encode([ "valid" => false ]);
        }
    }
    
    public function validateEmail($data)
    {
        try
        {
            $email = isset($data['email']) ? $data['email'] : '';
            $customerId = isset($data['customer_id']) ? $data['customer_id'] : 0;            
            if($customerId)
            {
                $customerId = $this->roleRepo->decodeData($customerId);
            }
            if($email == '')
            {
                $email = isset($data['eseal_customers']['email']) ? $data['eseal_customers']['email'] : '';
            }
            if($email != '')
            {
                if($customerId)
                {
                    $customerDetails = $this->where('email', $email)->where('customer_id', '!=', $customerId)->first(array('email'));
                }else{
                    $customerDetails = $this->where('email', $email)->first(array('email'));
                }
                $last = DB::getQueryLog();                
                if(empty($customerDetails))
                {
                    if($customerId)
                    {
                        $userDetails = DB::table('users')->where('email', $email)->where('customer_id', '!=', $customerId)->first(array('email'));
                    }else{
                        $userDetails = DB::table('users')->where('email', $email)->first(array('email'));
                    }
                    if(empty($userDetails))
                    {
                        return json_encode([ "valid" => true ]);
                    }else{
                        return json_encode([ "valid" => false ]); 
                    }
                }else{
                    return json_encode([ "valid" => false ]);
                }
            }
            return json_encode(false);
        } catch (\ErrorException $ex) {
            print_R($ex);
        }
    }
    public function uniqueValidation($data)
    {
        try
        {
            $customerId = isset($data['manufacturer_id']) ? $data['manufacturer_id'] : 0;
            $tableName = isset($data['table_name']) ? $data['table_name'] : '';
            $validateFieldName = isset($data['field_name']) ? $data['field_name'] : '';
            $validateField = isset($data['field_value']) ? $data['field_value'] : '';
            if($validateField == '')
            {
                $validateField = isset($data[$validateFieldName]) ? $data[$validateFieldName] : '';
            }
            $rowId = isset($data['row_id']) ? $data['row_id'] : 0;
            $skipDecode = isset($data['skip_decode']) ? $data['skip_decode'] : 0;
            $skipId = isset($data['skip_id']) ? $data['skip_id'] : 0;
            $skipColumn = isset($data['skip_column']) ? $data['skip_column'] : '';
            $pluckId = isset($data['pluck_id']) ? $data['pluck_id'] : 'id';
            $checkId = isset($data['check_id']) ? $data['check_id'] : '';
            $checkVal = isset($data['check_val']) ? $data['check_val'] : 0;
            
            if($customerId && !$skipDecode)
            {   
                $customerId = $this->roleRepo->decodeData($customerId);
            }
            if($tableName != '' && $validateField && $validateFieldName != '')
            {                
                if($customerId && $rowId != 0)
                {
                    if($skipColumn != 0 && $skipId != 0)
                    {                        
                        $rowData = DB::table($tableName)->where(array('manufacturer_id' => $customerId, $validateFieldName => $validateField))->where($pluckId, '!=', $rowId)->where($skipColumn, '!=', $skipId)->value($pluckId);
                    }
                    else{
                        if($checkId && $checkVal !=0)
                        {
                            $rowData = DB::table($tableName)->where(array('manufacturer_id' => $customerId, $validateFieldName => $validateField))->where($pluckId, '!=', $rowId)->where($checkId,"=",$checkVal)->value($pluckId);
                        }
                        else {
                            $rowData = DB::table($tableName)->where(array('manufacturer_id' => $customerId, $validateFieldName => $validateField))->where($pluckId, '!=', $rowId)->value($pluckId);
                        } 
                    }                   
                }else{
                    if($skipColumn != '' && $skipId != 0)
                    {
                        $rowData = DB::table($tableName)->where(array('manufacturer_id' => $customerId, $validateFieldName => $validateField))->where($skipColumn, '=', $skipId)->value($pluckId);
                    }elseif($customerId != 0){
                        $rowData = DB::table($tableName)->where(array('manufacturer_id' => $customerId, $validateFieldName => $validateField))->value($pluckId);
                    }else{
                        $rowData = DB::table($tableName)->where(array($validateFieldName => $validateField))->value($pluckId);
                    }
                }
                if(empty($rowData))
                {                    
                    return json_encode([ "valid" => true ]);
                }else{                    
                    return json_encode([ "valid" => false ]);
                }
            }
            return json_encode([ "valid" => false ]);
        } catch (\ErrorException $ex) {
            

            return json_encode([ "valid" => false, 'message' => $ex->getMessage() ]);
        }
    }


    public function getCustomerDetails($customerId)
    {
        $customerDetailsArray = array();
        $customerDetails = $this->where('customer_id', $customerId)->first();
        $customerDetailsArray['customer'] = $customerDetails;
        $customerPlanDetails = \DB::table('customer_products_plans')->where('customer_id', $customerId)->get();        
        $temp = 1;
        foreach ($customerPlanDetails as $customerDetail)
        {
            if (!empty($customerDetail))
            {
                $customerDetailsArray['plans'][] = $customerDetail;               
            }
        }
	$planColumns = \Schema::getColumnListing('customer_products_plans');
	if(sizeof($customerDetailsArray) == 1)
        {
            $planData = new \stdClass();
            $planColumns = \Schema::getColumnListing('customer_products_plans');
            foreach($planColumns as $key => $columns)
            {
                if('currency_code' == $columns)
                {
                    $planData->$columns = 4;
                }else{
                    $planData->$columns = '';
                }
            }
            $customerDetailsArray['plans'][] = $planData;
        }
        return $customerDetailsArray;
    }
    
    public function getCustomerAddressData($customerId)
    {
        try
        {
            $addressData = \DB::table('customer_address')->where('customer_id', $customerId)->first();
            if(empty($addressData))
            {
                $addressData = new \stdClass();
                $addressColumns = \Schema::getColumnListing('customer_address');
                foreach($addressColumns as $key => $columns)
                {
                    $addressData->$columns = '';
                }
            }
            return $addressData;
        } catch (\ErrorException $ex) {
            return $ex->getMessage();
        }
    }
    
    public function getLocationsByType($data)
    {
        try
        {
            $manufacturerId = isset($data['manufacturer_id']) ? $data['manufacturer_id'] : ''; 
            $locationTypeId = isset($data['location_type_id']) ? $data['location_type_id'] : '';
            $locationId = isset($data['location_id']) ? $data['location_id'] : '';
            if($manufacturerId != '')
            {
                $result = DB::table('locations')
                        ->where('manufacturer_id', $manufacturerId)
                        ->where('location_type_id', $locationTypeId)
                        ->where('location_id', '!=' ,$locationId)
                        ->where('is_deleted', 0)
                        ->get(array('location_id', 'location_name'));
                return json_encode($result);
            }
        } catch (\ErrorException $ex) {
            return false;
        }
    }
    
    public function deleteCustomerDependents($manufacturerId)
    {
        try
        {
            if($manufacturerId)
            {                
                $this->custRepo->softDelete($manufacturerId, 'location_types');
                $this->custRepo->softDelete($manufacturerId, 'locations');
                $this->custRepo->softDelete($manufacturerId, 'transaction_master');
                $this->custRepo->softDelete($manufacturerId, 'customer_products_plans');
                $this->custRepo->softDelete($manufacturerId, 'products');
                $this->custRepo->softDelete($manufacturerId, 'eseal_customer');
            }   
        } catch (\ErrorException $ex) {
            return $ex->getMessage();
        }
    }

    public function saveErpConfigurations($data, $currentUser)
    {
        try
        {
            if(!empty($data))
            {
                $manufacturerId = isset($data['manufacturer_id']) ? $data['manufacturer_id'] : '';
                if($manufacturerId != '')
                {
                    $manufacturerId = $this->roleRepo->decodeData($manufacturerId);
                    $data['manufacturer_id'] = $manufacturerId;
                    $checkData = DB::table('erp_integration')->where('manufacturer_id', $manufacturerId)->first(array('id'));
                    if(!empty($checkData))
                    {
                        $data['is_active'] = 1;
                        $data['modified_by'] = $currentUser;
                        //$data['modified_on'] = date('Y-m-d H:i:s');
                        DB::table('erp_integration')->where('manufacturer_id', $manufacturerId)->update($data);
                    }else{
                        $data['is_active'] = 1;
                        $data['created_by'] = $currentUser;
                        DB::table('erp_integration')->insert($data);
                    }
                    return json_encode(array('Status' => 1));
                }else{
                    return json_encode(array('Status' => 'No Manufacturer Id'));
                }
            }else{
                return json_encode(array('Status' => 'No Data Sent'));
            }
        } catch (\ErrorException $ex) {
            return $ex->getMessage();
        }
    }
    
    public function getCustomerErpConfiguration($manufacturerId)
    {
        try
        {
            $customerErpData = new \stdClass();
            if($manufacturerId)
            {
                $customerErpData = DB::table('erp_integration')->where('manufacturer_id', $manufacturerId)->first();
                if(empty($customerErpData))
                {
                    $customerErpColumns = \Schema::getColumnListing('erp_integration');
                    foreach($customerErpColumns as $key => $columns)
                    {
                        $customerErpData[$columns] = '';
                    }
                    $customerErpData = (object)$customerErpData;
                }
            }
            return $customerErpData;
        } catch (\ErrorException $ex) {
            return $ex->getMessage();
        }
    }
    
    public function getERPData()
    {
        try
        {
            $erpDataArray = array();
            $erpData = DB::table('master_lookup')
                    ->join('lookup_categories', 'lookup_categories.id', '=', 'master_lookup.category_id')
                    ->where('lookup_categories.name', 'ERP Models')
                    ->get(array('master_lookup.value', 'master_lookup.name'));
            if (!empty($erpData))
            {
                foreach($erpData as $data)
                {
                    $erpDataArray[$data->value] = $data->name;
                }                
            }
            return $erpDataArray;
        } catch (\ErrorException $ex) {
            return $ex->getMessage();
        }
    }
    //Get ParentStatus
    public function getCustomerParentStatus($customerId)
    {
        try
        {
            if($customerId)
            {                
                $company = DB::table('eseal_customer')->where('customer_id', $customerId)->get();
                $parent = DB::table('eseal_customer')->where('customer_id',$company[0]->parent_company_id)->get();
                if($parent[0]->is_deleted==1){             
                    return $parent[0]->is_deleted;
                }
                else{
                return $parent[0]->is_deleted;
                }
            }else{
                return 'No Customer Id';
            }
        } catch (\ErrorException $ex) {
            return $ex->getMessage();
        }
    }
}
