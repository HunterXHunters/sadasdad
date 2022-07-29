<?php
ini_set('memory_limit', '-1');
set_time_limit(0);
use Central\Repositories\CustomerRepo;
use Central\Repositories\RoleRepo;
use Illuminate\Support\MessageBag;

class ErpController extends BaseController
{

    protected $_onboarding;
    protected $_esealCustomer;
    private $custRepo;
    private $roleRepo;
    protected $_manufacturerId;

    public function __construct()
    {
        $this->_onboarding = new Customers\Onboarding();
        $this->custRepo = new CustomerRepo;
        $this->roleRepo = new RoleRepo;
        $this->_esealCustomer = new Customers\EsealCustomers();        
        $this->_manufacturerId = $this->custRepo->getManufacturerId();
    }

    public function index()
    {
        parent::Breadcrumbs(array('Home'=>'/','Company'=>'#')); 
        $allowAddCustomer = $this->roleRepo->checkPermissionByFeatureCode('CUST002');
        $customer_id = $this->roleRepo->encodeData(Session::get('customerId'));    
        $formData['erp_data'] = $this->_esealCustomer->getERPData();
 
        $formData = $this->getOnboardData($customer_id);
        $customerErpConfiguration = $this->_esealCustomer->getCustomerErpConfiguration($customer_id);
//dd($customerErpConfiguration);
        return View::make('erp/viewerp')->with(['allow_buttons' => ['add' => $allowAddCustomer],'customer_id'=>$customer_id,'formData' => $formData,'customerErpConfiguration'=>$customerErpConfiguration]);
    }
    
    public function getCustomers()
    {
        $custArr = array();
        $finalCustArr = array();
        if($this->_manufacturerId != 0)
        {
            $customer_details = $this->custRepo->getAllCustomers($this->_manufacturerId);            
        }else{
            $customer_details = $this->custRepo->getAllCustomers();
        }
        $logoPath = URL::to('/') . '/uploads/customers/'; // upload path
        $allowedAddCustomer = $this->roleRepo->checkPermissionByFeatureCode('CUST002');
        $allowedEditCustomer = $this->roleRepo->checkPermissionByFeatureCode('CUST003');
        $allowedDeleteCustomer = $this->roleRepo->checkPermissionByFeatureCode('CUST004');
        $allowedApproveCustomer = $this->roleRepo->checkPermissionByFeatureCode('CUST005');
        foreach ($customer_details as $value)
        {
            if ($value->status == 1)
                $status = 'Active';
            else
                $status = 'In-Active';
            $custArr['customer_id'] = $value->customer_id;
            if($value->logo != '')
            {
                $custArr['logo'] = '<img src="'.$logoPath.$value->logo.'" />';
            }else{
                $custArr['logo'] = '';
            }            
            $customerType = $this->_esealCustomer->getCustomerType($value->customer_type_id);
            $valid = $this->_esealCustomer->getCustomerParentStatus($value->customer_id);
            $custArr['customer_type_id'] = $customerType;
            $custArr['brand_name'] = $value->brand_name;
            $custArr['status'] = $status;
            $custArr['website'] = $value->website;
            $custArr['phone'] = $value->phone;
            $custArr['approved'] = ($value->approved) ? 'Approved' : 'Not Approved';
            $actions = '';
            if($allowedEditCustomer)
            {
                $actions = $actions . '<span style="padding-left:5px;"><a href="/customer/editcustomer/' . $this->roleRepo->encodeData($custArr['customer_id']) . '"><span class="badge bg-light-blue"><i class="fa fa-pencil"></i></span>
</a></span>';
            }
            if($allowedDeleteCustomer && $value->is_deleted == 0 && $value->customer_id != $this->_manufacturerId)
            {
                $actions = $actions . '<span style="padding-left:10px;" ><a onclick = "deleteEntityType(' . "'" . $this->roleRepo->encodeData($custArr['customer_id']) . "'" . ')"><span class="badge bg-red"><i class="fa fa-trash-o"></i></span>
</a></span>';
            }
            if(!$value->approved && $allowedApproveCustomer)
            {
                $actions = $actions . '<span style="padding-left:10px;"><a data-href="/customer/approvecustomer/' . $this->roleRepo->encodeData($custArr['customer_id']) . '" data-toggle="modal" data-target="#customer_approval"><span class="badge bg-yellow"><i class="fa fa-check"></i></span>
</a></span>';
            }     
            if($value->is_deleted==1 && $allowedDeleteCustomer && $valid==0)
            {
                $actions = $actions . '<span style="padding-left:10px;" ><a onclick = "restoreEntityType(' . "'" . $this->roleRepo->encodeData($custArr['customer_id']) . "'" .')" ><i class="fa fa-refresh"></i>
</a></span>';
            }       
            $custArr['actions'] = $actions;
            $finalCustArr[] = $custArr;
        }
        return json_encode($finalCustArr);
    }
    
    public function approveCustomer($customerId)
    {
        if($customerId)
        {
            $customerId = $this->roleRepo->decodeData($customerId);
        }
        $data = Input::all();
        $data['eseal_customers']['customer_id'] = isset($data['customer_id']) ? $data['customer_id'] : $customerId;
        $data['eseal_customers']['approved'] = 1;
        $data['eseal_customers']['status'] = 1;
        $data['eseal_customers']['admin_approved'] = 1;
        $returnObject = new stdClass;
        $returnObject->result = '';
        $returnObject->message = '';
        $brandTypeData = DB::table('eseal_customer')->where('customer_id', $customerId)->first(array('customer_type_id', 'is_otp_approved'));        
        $brandType = 0;
        $otpApproved = 0;
        if(!empty($brandTypeData))
        {
            $brandType = $brandTypeData->customer_type_id;
            $otpApproved = $brandTypeData->is_otp_approved;
        }
        if(!$otpApproved)
        {
//            $returnObject->result = false;
//            $returnObject->message = 'Company OTP is not approved';
//            return json_encode($returnObject);
            DB::table('eseal_customer')->where('customer_id', $customerId)->update(array('is_otp_approved' => 1, 'admin_approved' => 1));
        }
        $custDetails=DB::table('eseal_customer')->where('customer_id', $customerId)->first(array('email','firstname','lastname','brand_name', 'admin_approved', 'product_types'));
        $admin_approved = $custDetails->admin_approved;
        $password = 'e$e@l123';
        $firstname=$custDetails->firstname;
        $lastname=$custDetails->lastname;
        $brandname=$custDetails->brand_name;
        $product_types = $custDetails->product_types;
        if(!empty($product_types))
        {
            $product_types = explode(',', $product_types);
        }
        if (1001 == $brandType)
        {
            $this->_esealCustomer->createTableSchema($customerId, $returnObject);
            $roleId = $this->_esealCustomer->allocateTableData($customerId, $returnObject, 1);
            $this->_esealCustomer->updateRoleData($customerId, $returnObject, $roleId);
            $wmsData = DB::table('master_lookup')->where('name', 'WMS')->pluck('value');
            if(!empty($product_types) && in_array($wmsData, $product_types))
            {
                $entityId = DB::table('wms_entities')->where('org_id', $customerId)->pluck('id');
                if(empty($entityId))
                {
                    $saveWarehouseData = DB::table('wms_entities')->insert([
                                    'entity_name' => $brandname,
                                    'entity_type_id' => 0,
                                    'location_id' => 0,
                                    'org_id' => $customerId,
                                    'parent_entity_id'=> 0,
                                    'status' => 1
                            ]);
                }
            }
        }else{
            $roleId = $this->_esealCustomer->allocateTableData($customerId, $returnObject, 0);
            $this->_esealCustomer->updateRoleData($customerId, $returnObject);
        }
        if($returnObject->message == '')
        {
            $returnObject->message = 'Sucess';
        }

        if( $returnObject->message == 'Sucess')
        {    
            \Mail::send('emails.thanksmsg', array('firstname' => $firstname,'lastname'=>$lastname, 'admin_approved' => $admin_approved, 'password' => $password), function($message) use ($custDetails)
            {
                $message->to($custDetails->email)->subject('Successful registration.');
            });          
        }
        return json_encode($returnObject);
    }

    public function onboard()
    {
        parent::Breadcrumbs(array('Home'=>'/','Company'=>'customer/index','Onboard Company'=>'#')); 
        $formData = $this->getOnboardData(0);
        return View::make('customers/onboard')->with(array('formData' => $formData));
    }

    public function getOnboardData($customerId = 0)
    {
        $formData = $this->custRepo->prepareLocationData($customerId);
        $formData['customerLookupIds'] = $this->_onboarding->getCustomerLookupIds();
        $formData['parentCompanyList'] = $this->_onboarding->getparentCompanyList($customerId);
        $formData['productLookupIds'] = $this->_onboarding->getProductLookupIds();
        $componentLookupIds = $this->_onboarding->getComponentLookupIds();
        $formData['componentLookupIds'] = $componentLookupIds;
        $formData['countries'] = $this->custRepo->getCountryData();
        $formData['states'] = $this->custRepo->getZones(99);
        $formData['currency'] = $this->_onboarding->getCurrencies();
        $formData['priceData'] = $this->_onboarding->getPriceData();
        $formData['taxClassData'] = $this->_onboarding->getTaxData();
        $formData['erp_data'] = $this->_esealCustomer->getERPData();
        
        $manufacturerDetails = $this->custRepo->getAllCustomerDetails();
        $manufacturerData = array();
        if(!empty($manufacturerDetails))
        {
            foreach ($manufacturerDetails as $manufacturerSet) {            
                $manufacturerData[$manufacturerSet->customer_id] = $manufacturerSet->brand_name;
            }    
        }
        $formData['manufacturer'] = $manufacturerData;
        $formData['error_message'] = '';
        return $formData;
    }

    public function saveCustomer()
    {
        try
        {
            $returnObject = new stdClass;
            $returnObject->result = '';
            $returnObject->message = '';
            $data = Input::all();
            if(!isset($data['customer_id'])){
                $emailValidation = $this->validateEmail();
                if(!$emailValidation)
                {
                    $formData = $this->getOnboardData();
                    $formData['error_message'] = 'Email id already exist';
                    return View::make('customers/onboard')->with('formData', $formData);
                }
            }            
            $this->validateCustomerData($data);            
            $returnObject = $this->_esealCustomer->saveCustomer($data, $returnObject);
            $customerId = $returnObject->customer_id;
            if ($customerId)
            {
                return Redirect::action('CustomerController@index');
            } else if(isset($data['customer_id'])){
                return Redirect::action('CustomerController@editCustomer', array($data['customer_id']));
            }else{
//                $formData = $this->getOnboardData(0);
//                $formData['error_message'] = $returnObject->message;
//                return View::make('customers/onboard')->with('formData', $formData);
                return Redirect::back()->withInput()->withErrors([$returnObject->message]);
            }
        } catch (\ErrorException $ex)
        {
            return Redirect::back()->withInput()->withErrors([$ex->getMessage().' => '.$ex->getTraceAsString()]);
            //return $ex;
        }
    }
    
    protected function validateCustomerData($data)
    {
        $validator = Validator::make(
                        array(
                    'brand_name' => isset($data['eseal_customers']['brand_name']) ? $data['eseal_customers']['brand_name'] : '',
                    'website' => isset($data['eseal_customers']['website']) ? $data['eseal_customers']['website'] : '',
                    'email' => isset($data['eseal_customers']['email']) ? $data['eseal_customers']['email'] : ''
                        ), array(
                    'brand_name' => 'required',
                    'website' => 'required',
                    'email' => 'required|email|unique:eseal_customer'
                        )
        );
        if ($validator->fails())
        {
            //$formData = $this->getOnboardData();
            //$formData['error_message'] = $validator->messages('email');
            //return View::make('customers/onboard')->with('formData', $formData);
            return Redirect::back()->withInput()->withErrors([$validator->messages()]);
        }
    }

    public function editCustomer($customerId)
    {
        $customerId = $this->roleRepo->decodeData($customerId);
        parent::Breadcrumbs(array('Home'=>'/','Company'=>'customer/index','Edit Company'=>'#')); 

        $allowAddProduct = $this->roleRepo->checkPermissionByFeatureCode('PRD001');
        $allowImportCsv = $this->roleRepo->checkPermissionByFeatureCode('PRD007');
        $allowImportComponentCsv = $this->roleRepo->checkPermissionByFeatureCode('PRD009');
        $allowImportErp = $this->roleRepo->checkPermissionByFeatureCode('PRD008');
        $allowAddLocationsTypes = $this->roleRepo->checkPermissionByFeatureCode('LOCT002');
        $allowLocationsTypesImportCsv = $this->roleRepo->checkPermissionByFeatureCode('LOCT007');
        $allowLocationsTypesImportErp = $this->roleRepo->checkPermissionByFeatureCode('LOCT008');
        
        $allowedApproveCustomer = $this->roleRepo->checkPermissionByFeatureCode('CUST005');
        $permissions['approval'] = $allowedApproveCustomer;
        $formData = $this->getOnboardData($customerId);
Log::info('xxxxx');
        $customerDetails = $this->_esealCustomer->getCustomerDetails($customerId);
Log::info('yyyy');
        $customerAddress = $this->_esealCustomer->getCustomerAddressData($customerId);
Log::info('zzzzz');
        $customerErpConfiguration = $this->_esealCustomer->getCustomerErpConfiguration($customerId);
Log::info('aaaa');
        $customerlocations = $this->_esealCustomer->getCustomerLocations($customerId);
Log::info('bbbb');
        $customerstoragelocations = DB::table('master_lookup')->join('lookup_categories','lookup_categories.id','=','master_lookup.category_id')->select('master_lookup.name', 'master_lookup.value')->where('lookup_categories.name','=','Storage Location Types')->get();
        $customerbusinessid = DB::table('business_units')->select('business_unit_id','name')->where('manufacturer_id',$customerId)->get();
        //echo "<pre>";print_r($customerstoragelocations);die;
        //echo "<pre>";print_r($customerbusinessid);die;
        return View::make('customers/viewcustomer')->with(array('permissions' => $permissions, 'formData' => $formData, 'customerDetails' => $customerDetails, 'customerAddressData' => $customerAddress, 'erp_details' => $customerErpConfiguration, 'customer_locations' => $customerlocations,'customer_id' => $this->roleRepo->encodeData($customerId)))
                            ->with('customerstoragelocations',$customerstoragelocations)
                            ->with('customerbusinessid',$customerbusinessid )
                            ->with('allow_buttons', ['add_product' => $allowAddProduct, 'import_product_csv' => $allowImportCsv, 'import_product_component_csv' => $allowImportComponentCsv, 'import_product_erp' => $allowImportErp, 'add_locationtypes' => $allowAddLocationsTypes, 'import_locationtypes_csv' => $allowLocationsTypesImportCsv, 'import_locationtypes_erp' => $allowLocationsTypesImportErp]);
    }

    public function updateCustomer()
    {
        try
        {
            $returnObject = new stdClass;
            $returnObject->result = '';
            $returnObject->token = '';
            $returnObject->message = '';            
            $formData = array();
            $data = Input::all();
            $returnObject->token = isset($data['token']) ? $data['token'] : '';
            $this->_esealCustomer->checkOtpVerified($data, $returnObject);            
            if($returnObject->result)
            {
                $this->_esealCustomer->updateCustomerConfirmationDetails($data, $returnObject);
                $url = action('AuthenticationController@index');
                $message = 'You registration is confirmed and pending with admin approval, you will receive mail once you account is approved';
                return View::make('customers.error')->with('message', $message);
            }else{
                $formData = $this->getOnboardData();
                $formData['error_message'] = $returnObject->message;
                return Redirect::to('customer/confirmation/token/'.$returnObject->token);
            }            
        } catch (\ErrorException $ex)
        {
            return Redirect::back()->withInput()->withErrors([$ex->getMessage()]);
        }
    }
    
    public function deleteCustomer($customerId)
    {
        $customerId = $this->roleRepo->decodeData($customerId);
        $this->_esealCustomer->deleteCustomer($customerId);
        return Response::json([
                    'status' => true,
                    'message' => 'Sucessfully Deleted.'
        ]); 
    }

    public function restoreCustomer($customerId)
    {
        if($customerId)
        {
            $customerId = $this->roleRepo->decodeData($customerId);
        }
        $this->_esealCustomer->restoreCustomer($customerId);
        return Response::json([
                    'status' => true,
                    'message' => 'Sucessfully Restored.'
        ]); 
    }

    public function validateEmail()
    {
        $data = Input::all();
        return $this->_esealCustomer->validateEmail($data);        
    }

    public function uniqueValidation()
    {
        $data = Input::all();
        return $this->_esealCustomer->uniqueValidation($data);        
    }

    public function validateBrandOwner()
    {
        $data = Input::all();
        return $this->_esealCustomer->validateBrandOwner($data);
    }

    public function getZones()
    {
        try
        {
            $data = Input::all();
            $countryId = isset($data['countryId']) ? $data['countryId'] : 0;
            if ($countryId)
            {
                return json_encode($this->custRepo->getZones($countryId));
            } else
            {
                return 'No Data';
            }
        } catch (\ErrorException $ex)
        {
            
        }
    }
    
    public function getCities()
    {
        try
        {
            $data = Input::all();
            return json_encode($this->_esealCustomer->getCitiesList($data));            
        } catch (\ErrorException $ex)
        {
            
        }
    }
    
    public function addlocationCity()
    {
        $data = Input::all();
        return $this->_esealCustomer->saveLocationCity($data);
    }
    
    public function saveBusinessUnit()
    {
        try{
            $temp = array();
            $data = Input::all();
            if(!empty($data))
            {                
                $businessUnitId = DB::table('business_units')->insertGetId($data);
                if($businessUnitId)
                {
//                    $temp['key'] = $businessUnitId;
//                    $temp['name'] = $data['name'];
                    $temp[$businessUnitId] = $data['name'];
                }
            }
            return json_encode($temp);
        } catch (ErrorException $ex) {
            echo $ex->getMessage();
        }
    }
    
    public function saveErpConfigurations()
    {
        $data = Input::all();
        return $this->_esealCustomer->saveErpConfigurations($data, $this->_manufacturerId);
    }

    public function confirmationForm($token)
    {
        try
        {
            $customerData = new stdClass();
            $customerData = $this->_esealCustomer->getCustomerDetailsWithToken($token);
            if (isset($customerData) && !empty($customerData) && is_object($customerData))
            {
                if ($customerData->approved)
                {
                    $url = action('AuthenticationController@index');
                    $message = 'You have already confirmed please <a href="' . $url . '">login</a> and continue';
                    return View::make('customers.error')->with('message', $message);
                }
                $customerAddress = DB::table('customer_address')->where('customer_id', $customerData->customer_id)->first();
                if(empty($customerAddress))
                {
                    $customerAddress = new stdClass();
                    $customerAddress->address_id = '';
                    $customerAddress->address_1 = '';
                    $customerAddress->address_2 = '';
                    $customerAddress->city = '';
                    $customerAddress->postcode = '';
                    $customerAddress->country_id = 99;
                }
                $formData['countries'] = $this->custRepo->getCountryData();
                $formData['states'] = $this->custRepo->getZones($customerAddress->country_id);
                return View::make('customers.confirmation')->with(array('customerData' => $customerData, 'formData' => $formData, 'customerAddress' => $customerAddress));
            } else
            {
                return View::make('customers.error')->with('message', 'You are not allowed to use this link');
            }
        } catch (Exception $ex)
        {
            return $ex->getMessage().' => '.$ex->getTraceAsString();
            //echo "<Pre>";print_r($ex);
        }
    }
    
    public function sendOtp()
    {
        try
        {
            $data = Input::all();
            return $this->_esealCustomer->sendOtp($data);
        } catch (Exception $ex) {
            return $ex;
        }
    }
    
    public function validateOtp()
    {
        try
        {
            $data = Input::all();
            return $this->_esealCustomer->validateOtp($data);            
        } catch (Exception $ex) {
            return $ex;
        }
    }
    
    public function getComponentData()
    {
        $data = Input::all();
        if(!empty($data) && isset($data['productLookupIds']))
        {
            $getComponentData = DB::table('eseal_price_master')
                    ->where('is_active', 1)
                    ->wherein('product_lookup_id', $data['productLookupIds'])
                    ->get(array('id', 'name', 'price', 'valid_from', 'valid_upto', 'component_type_lookup_id'));
            return json_encode($getComponentData);
        }else{
            return ;
        }
    }
    
    /* locations methods start */

    public function viewLocations()
    {
        parent::Breadcrumbs(array('Home' => '/', 'Locations' => '#'));
        $responseData = $this->custRepo->prepareLocationData();
        return View::make('customers.locations')->with('responseData',$responseData);
    }
    
    public function saveLocationFromErp()
    {
        $data = Input::all();
        if(!empty($data) && isset($data['locationTypeFields']))
        {
            $data = $data['locationTypeFields'];
        }
        $manufacturerId = isset($data['manufacturer_id']) ? $this->roleRepo->decodeData($data['manufacturer_id']) : '';
        if($manufacturerId == '')
        {
            return Response::json([
                'status' => false,
                'message' => 'No Manufacturer Id'
            ]);
        }else{
            $erpData = DB::table('erp_integration')->where('manufacturer_id', $manufacturerId)->pluck('id');
            if(!empty($erpData))
            {
                return $this->_esealCustomer->saveLocationFromErp($data);
            }else{
                return Response::json([
                    'status' => false,
                    'message' => 'First please update ERP data in the previous tab.'
                ]);
            }
        }
        
    }
    
//     public function saveLocationTypeFromExcel()
//     {
//        try {
//         $data = Input::all();
//         Log::info(__FUNCTION__.' === '. print_r(Input::get(),true));
//         $filePath = isset($_FILES['files']['tmp_name']) ? $_FILES['files']['tmp_name'] : '';
//         if ($filePath != '')
//         {
//             if (!$fh = fopen($filePath, 'r'))
//             {
//                 throw new Exception("Could not open $filePath for reading.");
//             }

//             if (isset($data['manufacturerID']))
//             {
//                 $locationtype_details = DB::Table('location_types')->where('manufacturer_id', $data['manufacturerID'])->get();
//               Log::info('location_types:-');
//               Log::info($locationtype_details);
//             }
            
//             $i = 0;
//             $buffer = array();
//             $tempArray = array();
//             while (!feof($fh))
//             {
//                 $buffer[] = fgets($fh);
//                 $i++;
//                 $fields = array();
//                 foreach ($buffer as $line)
//                 {
//                     $fields = explode(',', $line);
//                 }
//                 //dd($fields);
//                 $tempArray[] = $fields;
//             }

//             $count = 0;
//             $locationHeaders = array('location_type_id', 'location_name', 'location_email', 'location_address', 'location_details', 'state', 'region', 'longitude', 'latitude', 'erp_code', 'pincode', 'city', 'country', 'phone_no', 'parent_location_id', 'manufacturer_id');
//             $locationHeadersTrim = array_map('trim', $locationHeaders);
//             //print_r($locationHeadersTrim);
            
//             $insertLocationData = array();
//             $storedlocations = array();
//             $j = 1;
//             $countrows = 0;
//             $message = '';
            
//             Log::info('File Fields');
//             Log::info($tempArray);
            
// //            echo "<pre>";print_r($tempArray);
//             foreach ($tempArray as $locations)
//             {
//                 $locationDetails = array();
//                 if ($count == 0)
//                 {
//                     $count = 1;
//                 } else
//                 {
//                     $locationDetails = array_map('trim', $locations);
//                 }
//                 $locationDetails[] = '';
//                 //echo "<pre>";print_r($locationDetails);die;
// //                echo "<pre>";print_r($locationHeadersTrim);
//                 if (!empty($locationDetails) && !empty($locationHeadersTrim) && count($locationDetails) == count($locationHeadersTrim))
//                 {
//                     //echo "<pre>";print_R($locationDetails);die;
//                     $locationTypeName = isset($locationDetails[0]) ? $locationDetails[0] : '';
//                     $locationName = isset($locationDetails[1]) ? $locationDetails[1] : '';
//                     $erpCode = isset($locationDetails[9])?trim($locationDetails[9]) : '';
                    
//                     Log::info($locationTypeName);
//                     Log::info($locationName);

//                     if ($locationTypeName != '' && $locationName != '')
//                     {
//                         $response = $this->checkLocationTypeName($locationTypeName, $data['manufacturerID']);
//                         Log::info('is type exists');
//                         Log::info($response);
//                         if (!$response)
//                         {
//                             $insertArray['location_type_name'] = $locationTypeName;
//                             $insertArray['manufacturer_id'] = $data['manufacturerID'];
//                             $response = DB::table('location_types')->insertGetId($insertArray);
//                         }
//                         //$checkLocation = $this->checkLocationName($locationName, $data['manufacturerID'], $response);
//                         $checkErp = 0;
//                         if(!empty($erpCode)){
//                             $checkErp = $this->checkErpCode($erpCode, $data['manufacturerID'], $response);    
//                         }
                        
//                         Log::info('is ERP Code exists');
//                         Log::info($checkErp);
// //                        echo 'checkLocation => '.$checkLocation;
//                         if(!$checkErp)
//                         {
//                             $locationDetails[0] = $response;
//                             //echo (count($locationDetails) - 1);die;
//                             $locationDetails[(count($locationDetails) - 1)] = $data['manufacturerID'];
//                             $insertLocationData[] = array_combine($locationHeadersTrim, array_map('trim', $locationDetails)); 
//                         }else{
//                             $message = $message . '  Plant Code already exists with '.$erpCode.'  ';
//                         }
//                     }
//                 }                
//             }
//             $loctypeid = DB::table('location_types')
//                                 ->select('location_type_id')
//                                 ->get();

//             foreach ($loctypeid as $locid) {
               
//                $insert_ids[] = $locid->location_type_id;
               
//             }
           
//             $insertLocation = '';
//             foreach ($insertLocationData as $key=>$loc) {
                
            
//                 if(in_array($loc['location_type_id'], $insert_ids))
//                 {
                    

//                     $reqdata =DB::table('location_types')
//                                                 ->select('location_type_name')
//                                                 ->where('location_type_id',$loc['location_type_id'])
//                                                 ->get();
                    
//                    // $vari = DB::getQueryLog();
//                     /*$loction_name = $reqdata[0]->location_type_name;*/
//                     $insertLocation[$key]['location_type_names'] = $reqdata[0]->location_type_name;
                    
//                 }
//                 //echo $reqdata[0]->location_type_name."hhhhh => <pre/>";print_r($insertLocationData);
                
//             }
           
//              /*echo "insertLocationData => <pre/>";print_r($insertLocation);die;*/
//             if (!empty($insertLocationData))
//             {
//                 DB::table('locations')->insert($insertLocationData);
//                 $msg = "Successfully imported " . count($insertLocationData) . " rows. ".$message;
//             }else{
//                 $msg = "".$message . "Please choose a file with different Plant Codes";
//             }
//             $resp['msg'] = $msg;
//             $resp['newItems'] = $insertLocationData;
//             $resp['loctpname'] = $insertLocation;
//             return $resp;
//             //return View::make('customers/viewcustomer')->with('insertData',$insertLocationData);     
//         }
//     }
//     catch(Exception $e){
//         $message = $e->getMessage(); 
//         Log::info($message);    
//     }

//     }

   public function saveLocationTypeFromExcel()
    {
       try {
        $data = Input::all();
        Log::info(__FUNCTION__.' === '. print_r(Input::get(),true));
        $filePath = isset($_FILES['files']['tmp_name']) ? $_FILES['files']['tmp_name'] : '';
        $fileName = isset($_FILES['files']['name']) ? $_FILES['files']['name'] : '';
        if ($filePath != '')
        {
            if (!$fh = fopen($filePath, 'r'))
            {
                throw new Exception("Could not open ".$fileName." for reading.");
            }
        
            $extension = pathinfo($_FILES['files']['name'], PATHINFO_EXTENSION);

        $allowed_Extensions = ['XLS','XLSX'];

        if( !in_array(strtoupper($extension), $allowed_Extensions))
        {
            throw new Exception('Please upload an Excel file with .xls or .xlsx extension.');
        }

            if (isset($data['manufacturerID']))
            {
                $locationtype_details = DB::Table('location_types')->where('manufacturer_id', $data['manufacturerID'])->get();
              Log::info('location_types:-');
              Log::info($locationtype_details);
            }
            
            $i = 0;
            //$buffer = array();
            $tempArray = array();
            // while (!feof($fh))
            // {
            //     $buffer[] = fgets($fh);
            //     $i++;
            //     $fields = array();
            //     foreach ($buffer as $line)
            //     {
            //         $fields = explode(',', $line);
            //     }
            //     //dd($fields);
            //     $tempArray[] = $fields;
            // }

            $path = Input::file('files')->getRealPath();

            $tempArray = Excel::load($path, function($reader) {})->get()->toArray();

            $count = 0;
            $locationHeaders = array('location_type_name', 'location_name', 'location_email', 'address', 'location_details', 'state', 'region', 'longitude', 'latitude', 'sap_code', 'pincode', 'city', 'country', 'phone_number', 'parent_location_id');
            $locationHeadersTrim = array_map('trim', $locationHeaders);

            $excelheaders = array_keys($tempArray[0]);
            //Log::info(print_r(array_diff($locationHeadersTrim, $excelheaders),true));
            if(count(array_diff($locationHeadersTrim, $excelheaders)) >0){
                throw new Exception("Some Headers are missing Please Check.");
            }
            //print_r($locationHeadersTrim);
            
            $insertLocationData = array();
            $storedlocations = array();
            $j = 1;
            $countrows = 0;
            $message = '';
            
            Log::info('File Fields');
            Log::info($tempArray);
            
            foreach ($tempArray as $locationDetails)
            {
              
                Log::info(count($locationDetails));
                Log::info(count($locationHeadersTrim));
                if (!empty($locationDetails) && !empty($locationHeadersTrim))
                {
                    $locationTypeName = isset($locationDetails['location_type_name']) ? $locationDetails['location_type_name'] : '';
                    $locationName = isset($locationDetails['location_name']) ? $locationDetails['location_name'] : '';
                    $erpCode = isset($locationDetails['sap_code'])?trim($locationDetails['sap_code']) : '';
                    

                    if ($locationTypeName != '' && $locationName != '')
                    {
                        $response = $this->checkLocationTypeName($locationTypeName, $data['manufacturerID']);
                        Log::info('is type exists');
                        Log::info($response);
                        if (!$response)
                        {
                            $insertArray['location_type_name'] = $locationTypeName;
                            $insertArray['manufacturer_id'] = $data['manufacturerID'];
                            $response = DB::table('location_types')->insertGetId($insertArray);
                        }
                        //$checkLocation = $this->checkLocationName($locationName, $data['manufacturerID'], $response);
                        $checkErp = 0;
                        if(!empty($erpCode)){
                            $checkErp = $this->checkErpCode($erpCode, $data['manufacturerID'], $response);    
                        }
                        
                        Log::info('is ERP Code exists');
                        Log::info($checkErp);
//                        echo 'checkLocation => '.$checkLocation;
                        if(!$checkErp)
                        {
                           
                            $insertLocationData[] = ['location_type_id'=>$response,'location_name'=>$locationDetails['location_name'],'location_email'=>$locationDetails['location_email'],'location_details'=>$locationDetails['location_details'],'location_address'=>$locationDetails['address'],'longitude'=>$locationDetails['longitude'],'latitude'=>$locationDetails['latitude'],'country'=>$locationDetails['country'],'state'=>$locationDetails['state'],'region'=>$locationDetails['region'],'erp_code'=>$locationDetails['sap_code'],'pincode'=>$locationDetails['pincode'],'city'=>$locationDetails['city'],'phone_no'=>$locationDetails['phone_number'],'parent_location_id'=>$locationDetails['parent_location_id'],'manufacturer_id'=>$data['manufacturerID']]; 
                            //array_combine($locationHeadersTrim, array_map('trim', $locationDetails)); 
                        }else{
                            $message = $message . '  Plant Code already exists with '.$erpCode.'  ';
                        }
                    }
                }                
            }
            $loctypeid = DB::table('location_types')
                                ->select('location_type_id')
                                ->get();

            foreach ($loctypeid as $locid) {
               
               $insert_ids[] = $locid->location_type_id;
               
            }
           
            $insertLocation = '';
            foreach ($insertLocationData as $key=>$loc) {
                
            
                if(in_array($loc['location_type_id'], $insert_ids))
                {
                    

                    $reqdata = DB::table('location_types')
                                                ->select('location_type_name')
                                                ->where('location_type_id',$loc['location_type_id'])
                                                ->get();
                    
                    $insertLocation[$key]['location_type_names'] = $reqdata[0]->location_type_name;
                    
                }
                
            }
           
            if (!empty($insertLocationData))
            {
                DB::table('locations')->insert($insertLocationData);
                $msg = "Successfully imported " . count($insertLocationData) . " rows. ".$message;
            }else{
                $msg = "".$message . "Please choose a file with different Plant Codes";
            }
            $resp['msg'] = $msg;
            $resp['newItems'] = $insertLocationData;
            $resp['loctpname'] = $insertLocation;
            return $resp;
        }
    }
    catch(Exception $e){
        $message = $e->getMessage(); 
        Log::info($message);  
        $resp['msg'] = $message;
            $resp['newItems'] = "";
            $resp['loctpname'] = "";
            return $resp;  
    }

    }

    public function importLocations(){
        try{

        }
        catch(Exception $e){
            $status =0;
            $message = $e->getMessage();
        }
        return json_encode(['Status'=>$status,'Message'=>$message]);
    }
    
    public function checkLocationTypeName($locationTypeName, $manufacturerID)
    {
        return DB::table('location_types')->where('location_type_name', $locationTypeName)->where('manufacturer_id', $manufacturerID)->pluck('location_type_id');
    }
    
    public function checkLocationName($locationName, $manufacturerID, $locationTypeId)
    {
        return DB::table('locations')->where(array('location_name' => $locationName, 'manufacturer_id' => $manufacturerID, 'location_type_id' => $locationTypeId))->pluck('location_id');
    }

    public function checkErpCode($erpCode, $manufacturerID, $locationTypeId)
    {
        return DB::table('locations')->where(array('erp_code' => $erpCode, 'manufacturer_id' => $manufacturerID))->pluck('location_id');
    }
    public function getTreeLocations($id)
    {
        try{

               $id = $this->roleRepo->decodeData($id);
        if ($id == '')
        {
            $id = 1;
        }

        $allowedAddLocations = $this->roleRepo->checkPermissionByFeatureCode('LOC002');
        $allowedEditLocations = $this->roleRepo->checkPermissionByFeatureCode('LOC003');
        $allowedDeleteLocations = $this->roleRepo->checkPermissionByFeatureCode('LOC004');
        $allowedEditLocationTypes = $this->roleRepo->checkPermissionByFeatureCode('LOCT003');
        $allowedDeleteLocationTypes = $this->roleRepo->checkPermissionByFeatureCode('LOCT004');
        $locas = DB::Table('location_types')
                //->join('locations', 'locations.location_type_id', '=', 'location_types.location_type_id')
                ->join('eseal_customer', 'eseal_customer.customer_id', '=', 'location_types.manufacturer_id')
                ->select('location_types.location_type_name', 'location_types.location_type_id', 'location_types.manufacturer_id','location_types.is_deleted')
                ->where('eseal_customer.customer_id', $id)
                //->where('location_types.is_deleted', 0)
                ->get();
        $finalCustArrs = array();
        $custs = array();
        $temp = array('Warehouse', 'Depot', 'RDC');
        $states = DB::table('locations')
                    ->join('zone', 'locations.state', '=','zone.zone_id')
		    ->where('locations.location_type_id', '!=', 874)
                    ->select('zone.name')
                    ->first();
        $customers_details = json_decode(json_encode($locas), true);

        foreach ($customers_details as $valus)
        {
            //return $valus;
            $locs = DB::Table('locations')
                    ->join('location_types', 'locations.location_type_id', '=', 'location_types.location_type_id')
                    ->leftJoin('zone', 'locations.state', '=','zone.zone_id')
                    ->leftJoin('master_lookup','locations.storage_location_type_code','=','master_lookup.value')
                    ->leftJoin('business_units','business_units.business_unit_id', '=', 'locations.business_unit_id')
                    ->select('locations.location_id', 'locations.location_name', 'locations.manufacturer_id', 'locations.parent_location_id', 'locations.location_type_id', 'locations.location_email', 'locations.location_address', 'locations.location_details', 'locations.state', 'locations.region', 'locations.longitude', 'locations.latitude','zone.name as states', 'locations.erp_code', 'location_types.location_type_name','locations.is_deleted','location_types.is_deleted as loctypedel','master_lookup.name','business_units.name as bu_name')
                    ->where('location_types.location_type_id', $valus['location_type_id'])
                    ->where('locations.parent_location_id',0)
		   ->where('locations.location_type_id', '!=', 874)
                    //->where('locations.is_deleted', 0)
                    ->get();
            
            $finalCustArr = array();
            $cust = array();
            $locations = json_decode(json_encode($locs), true);
            foreach ($locations as $subloc)
            {
                $sublocs = DB::Table('locations')
                    ->join('location_types', 'locations.location_type_id', '=', 'location_types.location_type_id')
                    ->leftJoin('zone', 'locations.state', '=','zone.zone_id')
                    ->leftJoin('master_lookup','locations.storage_location_type_code','=','master_lookup.value')
                    ->leftJoin('business_units','business_units.business_unit_id', '=', 'locations.business_unit_id')
                    ->select('locations.location_id', 'locations.location_name', 'locations.manufacturer_id', 'locations.parent_location_id', 'locations.location_type_id', 'locations.location_email', 'locations.location_address', 'locations.location_details', 'locations.state', 'locations.region', 'locations.longitude', 'locations.latitude', 'zone.name as states','locations.erp_code', 'location_types.location_type_name','locations.is_deleted','location_types.is_deleted as loctypedelete','master_lookup.name','business_units.name as bu_name')
                    //->where('location_types.location_type_id', $subloc['location_type_id'])
		    ->where('locations.location_type_id', '!=', 874)
                    ->where('locations.parent_location_id',$subloc['location_id'])
                    //->where('locations.is_deleted', 0)
                    ->get();
                
            $finalCustArrl = array();
            $custl = array();
            
            $locs = json_decode(json_encode($sublocs), true);
            foreach ($locs as $valu)
            {
                $custl['location_id'] = $valu['location_id'];
                $custl['location_name'] = $valu['location_name'];
                $custll['manufacturer_id'] = $valu['manufacturer_id'];
                $custl['parent_location_id'] = $valu['parent_location_id'];
                $custl['location_type_id'] = $valu['location_type_id'];
/*                $custl['location_email'] = $valu['location_email'];
                $custl['location_address'] = $valu['location_address'];
                $custl['location_details'] = $valu['location_details'];*/
                $custl['state'] = $valu['states'];
                $custl['region'] = $valu['region'];
                $custl['loctypedelete'] = $valu['loctypedelete'];
                $custl['is_deleted'] = $valu['is_deleted'];
/*                $custl['longitude'] = $valu['longitude'];
                $custl['latitude'] = $valu['latitude'];
                $custl['erp_code'] = $valu['erp_code'];*/
                $custl['business_unit'] = $valu['bu_name'];
                $custl['storage_location'] = $valu['name'];
                $custl['actions'] = '';
                
                if($allowedEditLocations && $valu['loctypedelete']==0 && $subloc['is_deleted']==0)
                {
                    $custl['actions'] = $custl['actions'] . '<span style="padding-left:5px;" >'
                        . '<a data-href="/customer/editlocation/' . $valu['location_id'] . '" data-toggle="modal" onclick="getLocationName(' . $valu['location_id'] . ');" data-target = "#basicvalCodeModal1" >'
                        . '<span class="badge bg-light-blue"><i class="fa fa-pencil"></i></span></a></span>';
                }
                if($allowedDeleteLocations && $valu['loctypedelete']==0 && $valu['is_deleted']==0 && $subloc['is_deleted']==0)
                {
                    $custl['actions'] = $custl['actions'] 
                        . '<span style="padding-left:10px;" ><a onclick = "deleteLocation(' . $valu['location_id'] . ',' . $valu['manufacturer_id'] . ')"><span class="badge bg-red"><i class="fa fa-trash-o"></i></span></a></span>';
                }
                if($valu['is_deleted']==1 && $allowedDeleteLocations && $valu['loctypedelete']==0 && $subloc['is_deleted']==0)
                {
                    $custl['actions'] = $custl['actions'] . '<span style="padding-left:10px;" ><a onclick = "restoreLocation(' . $valu['location_id'] . ',' . $valu['manufacturer_id'] . ')"><span class="badge bg-red"><i class="fa fa-refresh"></i></span></a></span>';
                }
                if($valu['loctypedelete']==0 && $subloc['is_deleted']==0)
                {                 
                    if(in_array($valus['location_type_name'], $temp))
                    {
                        $custl['actions'] = $custl['actions'] . '<span style="padding-left:10px;" ><a data-toggle="modal" onclick="addRegion(' . $valu['location_type_id'] . ');" data-target="#location_add_region"><i class="fa fa-pencil-square-o"></i></a></span>';
                    }
                }
                
                $finalCustArrl[] = $custl;
            }
                $cust['location_id'] = $subloc['location_id'];
                $cust['location_name'] = $subloc['location_name'];
                $cust['manufacturer_id'] = $subloc['manufacturer_id'];
                $cust['parent_location_id'] = $subloc['parent_location_id'];
                $cust['location_type_id'] = $subloc['location_type_id'];
/*                $cust['location_email'] = $subloc['location_email'];
                $cust['location_address'] = $subloc['location_address'];
                $cust['location_details'] = $subloc['location_details'];*/
                $cust['state'] = $subloc['states'];
                $cust['region'] = $subloc['region'];
                $cust['is_deleted'] = $subloc['is_deleted'];
                $cust['loctypedel'] = $subloc['loctypedel'];                
/*                $cust['longitude'] = $subloc['longitude'];
                $cust['latitude'] = $subloc['latitude'];
                $cust['erp_code'] = $subloc['erp_code'];*/
                $cust['business_unit'] = $subloc['bu_name'];
                $cust['storage_location'] = $subloc['name'];
                $cust['actions'] = '';
                   if($allowedAddLocations && $subloc['loctypedel']==0 && $subloc['is_deleted']==0)
                {
                    $cust['actions'] = $cust['actions'] .'<span style="padding-left:5px;" ><a data-toggle="modal" onclick="getSubLoc(' . $subloc['location_type_id'] . ','. $subloc['location_id'] .');" data-target="#basicvalCodeModal"><span class="badge bg-green"><i class="fa fa-plus"></i></span></a></span>';
                }                
                    if($allowedEditLocations && $subloc['loctypedel']==0)
                {
                    $cust['actions'] = $cust['actions'] . '<span style="padding-left:5px;" >'
                        . '<a data-href="/customer/editlocation/' . $subloc['location_id'] . '" data-toggle="modal" onclick="getLocationName(' . $subloc['location_id'] . ');" data-target = "#basicvalCodeModal1" >'
                        . '<span class="badge bg-light-blue"><i class="fa fa-pencil"></i></span></a></span>';
                }
                if($allowedDeleteLocations && $subloc['is_deleted']==0 && $subloc['loctypedel']==0)
                {
                    $cust['actions'] = $cust['actions'] 
                        . '<span style="padding-left:10px;" ><a onclick = "deleteLocation(' . $subloc['location_id'] . ',' ."'".$this->roleRepo->encodeData( $subloc['manufacturer_id'] )."'". ')"><span class="badge bg-red"><i class="fa fa-trash-o"></i></span></a></span>';
                }
                if($allowedDeleteLocations && $subloc['is_deleted']==1 && $subloc['loctypedel']==0)
                {
                    $cust['actions'] = $cust['actions'] 
                        . '<span style="padding-left:10px;" ><a onclick = "restoreLocation(' . $subloc['location_id'] . ',' ."'".$this->roleRepo->encodeData( $subloc['manufacturer_id'] )."'". ')"><span class="badge bg-red"><i class="fa fa-refresh"></i></span></a></span>';
                } 
                if($subloc['is_deleted']==0 && $subloc['loctypedel']==0)
                {               
                    if(in_array($valus['location_type_name'], $temp))
                    {
                        $cust['actions'] = $cust['actions'] . '<span style="padding-left:10px;" ><a data-toggle="modal" onclick="addRegion(' . $subloc['location_type_id'] . ');" data-target="#location_add_region"><i class="fa fa-pencil-square-o"></i></a></span>';
                    }
                }
                
                $cust['children'] = $finalCustArrl;
                $finalCustArr[] = $cust;
            }

            $custs['actions'] = '';
            $custs['location_type_name'] = $valus['location_type_name'];
            $custs['location_type_id'] = $valus['location_type_id'];
            $custs['manufacturer_id'] = $valus['manufacturer_id'];
            $custs['is_deleted'] = $valus['is_deleted'];
            //echo 'location_type_id => '.$valus['location_type_id'];
            if($allowedAddLocations && $valus['is_deleted'] == 0)
                {
                    $custs['actions'] = '<span style="padding-left:5px;" ><a data-toggle="modal" onclick="getLocName(' . $valus['location_type_id'] . ');" data-target="#basicvalCodeModal"><span class="badge bg-green"><i class="fa fa-plus"></i></span></a></span>';
                }
            if($allowedEditLocationTypes)
                {
                     $custs['actions'] = $custs['actions'] . '<span style="padding-left:5px;" >'
                        . '<a data-href="/customer/editlocationtype/' . $valus['location_type_id'] . '" data-toggle="modal" data-target = "#location_types_edit" >'
                        . '<span class="badge bg-light-blue"><i class="fa fa-pencil"></i></span></a></span>';
                }             
            if($allowedDeleteLocationTypes && $valus['is_deleted'] == 0)
                {
                    $custs['actions'] = $custs['actions'] 
                        . '<span style="padding-left:10px;" ><a onclick = " deleteLocationType(' . $valus['location_type_id'] . ',' ."'".$this->roleRepo->encodeData( $valus['manufacturer_id'] )."'". ')"><span class="badge bg-red"><i class="fa fa-trash-o"></i></span></a></span>';
                }
            if($allowedDeleteLocationTypes && $valus['is_deleted'] == 1)
                {
                     $custs['actions'] = $custs['actions'] 
                        . '<span style="padding-left:10px;" ><a onclick = " restoreLocationType(' . $valus['location_type_id'] . ',' ."'".$this->roleRepo->encodeData( $valus['manufacturer_id'] )."'". ')"><span class="badge bg-red"><i class="fa fa-refresh"></i></span></a></span>';
                }                   

            $custs['children'] = $finalCustArr;
            $finalCustArrs[] = $custs;
        }

        return json_encode($finalCustArrs);
         }catch(Ecxeption $e){
            Log::info("message:-------");
            Log::info($e->getMessage());
        }


    }

    // get locations function
    public function getLocations()
    {
        $finalCustArr = array();
        $cust = array();
        

        $getLocationData = DB::table('locations')->get();
        $customer_details = json_decode(json_encode($getLocationData), true);
        $allowedAddLocations = $this->roleRepo->checkPermissionByFeatureCode('LOC002');
        $allowedEditLocations = $this->roleRepo->checkPermissionByFeatureCode('LOC003');
        $allowedDeleteLocations = $this->roleRepo->checkPermissionByFeatureCode('LOC004');
        foreach ($customer_details as $valu)
        {
            $cust['location_id'] = $valu['location_id'];
            $cust['location_name'] = $valu['location_name'];
            $cust['manufacturer_id'] = $valu['manufacturer_id'];
            $cust['parent_location_id'] = $valu['parent_location_id'];
            $cust['location_type_id'] = $valu['location_type_id'];
            $cust['location_email'] = $valu['location_email'];
            $cust['location_address'] = $valu['location_address'];
            $cust['location_details'] = $valu['location_details'];
            $cust['country'] = $valu['country'];
            $cust['state'] = $valu['state'];
            $cust['region'] = $valu['region'];
            $cust['longitude'] = $valu['longitude'];
            $cust['latitude'] = $valu['latitude'];
            $cust['erp_code'] = $valu['erp_code'];
            $cust['actions'] = '';
            if($allowedEditLocations)
            {
                $cust['actions'] = $cust['actions'] . '<span style="padding-left:5px;" ><a data-href="/customer/editlocation/' . $valu['location_id'] . '" data-toggle="modal" data-target="#location_edit" class="btn btn-warning"><span class="badge bg-light-blue"><i class="fa fa-pencil"></i></span></a></span>';
            }
            if($allowedDeleteLocations)
            {
                $cust['actions'] = $cust['actions'] . '<span style="padding-left:5px;" ><a onclick = "deleteLocation(' . $valu['location_id'] . ')" class="btn btn-danger"><span class="badge bg-red"><i class="fa fa-trash-o"></i></span>
                    </a></span>';
            }
            $finalCustArr[] = $cust;
        }
        return json_encode($finalCustArr);
    }

    public function saveLocation()
    {
        $stateName = DB::Table('zone')->select('name')->where('zone_id',Input::get('state'))->get();
        $source_page = Input::get('source_page');
        $locationId = DB::Table('locations')->insertGetId([
            'location_name'=>Input::get('location_name'),
            'manufacturer_id'=>Input::get('manufacturer_id'),
            'parent_location_id'=>Input::get('parent_location_id'), 
            'location_type_id'=>Input::get('location_type_id'),
            'location_email'=>Input::get('location_email'),
            'location_address'=>Input::get('location_address'),
            'location_details'=>Input::get('location_details'),
            'region'=>Input::get('region'),
            'country'=>Input::get('country'),
            'state'=>Input::get('state'),
            'longitude'=>Input::get('longitude'),
            'latitude'=>Input::get('latitude'),
            'erp_code'=>Input::get('erp_code'),
            'business_unit_id'=>Input::get('business_unit_id'),
            'storage_location_type_code'=>Input::get('storage_location_type_code'),
            'created_date'=>date('Y-m-d H-i-s'),
            'created_by'=>Session::get('userId')
        ]);
        if($source_page == 'product')
        {
            return Response::json([
                'status' => true,
                'message' => 'Sucessfully added.',
                'location_id' => $locationId
            ]); 
        }
        return Response::json([
                    'status' => true,
                    'message' => 'Sucessfully added.'
        ]);
    }

    public function editLocation($locationId)
    {
        $loc = DB::table('locations')->where('location_id', $locationId)->first();
        return Response::json($loc);
    }

    public function updateLocation($location_id)
    {
        DB::Table('locations')
                ->where('location_id', $location_id)
                ->update(array('location_name' => Input::get('location_name'),
                               /*'manufacturer_id' => Input::get('manufacturer_id'),*/
                               'parent_location_id' => Input::get('parent_location_id'), 
                               'location_type_id' => Input::get('location_type_id'), 
                               'location_email' => Input::get('location_email'), 
                               'location_address' => Input::get('location_address'), 
                               'location_details' => Input::get('location_details'), 
                               'country' => Input::get('country'), 
                               'state' => Input::get('state'), 
                               'region' => Input::get('region'), 
                               'longitude' => Input::get('longitude'), 
                               'latitude' => Input::get('latitude'), 
                               'erp_code' => Input::get('erp_code'),
                               'business_unit_id'=>Input::get('business_unit_id'),
            'storage_location_type_code'=>Input::get('storage_location_type_code'),
            'modified_on'=>date('Y-m-d H-i-s'),
            'modified_by'=>Session::get('userId')
                               ));
        return Response::json([
                    'status' => true,
                    'message' => 'Sucessfully updated.'
        ]);
    }

    public function deleteLocation($locationId)
    {
        DB::Table('locations')
        ->where('location_id', '=', $locationId)
        ->orWhere('parent_location_id','=',$locationId)
        ->update(array('is_deleted'=>1));
        return Response::json([
            'status' => true,
            'message' => 'Sucessfully deleted.'
        ]);
    }
    public function restoreLocation($locationId)
    {
        DB::Table('locations')
        ->where('location_id', '=', $locationId)
        ->orWhere('parent_location_id','=',$locationId)
        ->update(array('is_deleted'=>0));
        return Response::json([
            'status' => true,
            'message' => 'Sucessfully Restored.'
        ]);
    }
    
    public function getLocationsByType()
    {
        $data = Input::all();
        return $this->_esealCustomer->getLocationsByType($data);
    }

    /* locations methods end */

    /* locations types  methods start */

    public function viewLocationTypes()
    {
        parent::Breadcrumbs(array('Home' => '/', 'Location Types' => '#'));
        return View::make('customers.locationtypes');
    }

    public function saveLocationType()
    {
        $data = Input::all();
        if(isset($data['location_type']))
        {
            $locationTypeData = Input::get('location_type');
            $validator = Validator::make(
                            array('location_type_name' => isset($locationTypeData['location_type_name']) ? $locationTypeData['location_type_name'] : '',
                                'manufacturer_id' => isset($locationTypeData['manufacturer_id']) ? $this->roleRepo->decodeData($locationTypeData['manufacturer_id']) : ''), 
                            array('location_type_name' => 'required'));
            if ($validator->fails())
            {
                return Response::json([ 'status' => FALSE, 'message' => $validator->messages()]);
            }else{
                if(isset($locationTypeData['manufacturer_id']))
                {
                    $locationTypeData['manufacturer_id'] = $this->roleRepo->decodeData($locationTypeData['manufacturer_id']);
                }
                $result = $this->_esealCustomer->addLocationTypes($locationTypeData);
                if($result)
            {
                return Response::json(['status' => true, 'message' => 'Sucessfully added.', 'location_type_id' => $result ]);
            }else{
                return Response::json(['status' => true, 'message' => $result, 'location_type_id' => 0 ]);
            }
            }
            return Response::json(['status' => true, 'message' => 'Unable to add location types.' ]);
        }
        $result = $this->_esealCustomer->addLocationTypes($data);
        if($result)
        {
            return Response::json(['status' => true, 'message' => 'Sucessfully added.', 'location_type_id' => $result ]);
        }else{
            return Response::json(['status' => true, 'message' => $result, 'location_type_id' => 0 ]);
        }
    }

    public function editLocationType($locationTypeId)
    {
        $cuser = DB::table('location_types')->where('location_type_id', '=', $locationTypeId)->first();
        return Response::json($cuser);
    }

    public function getLocationTypes()
    {
        $data = Input::all();
        $custArr = array();
        $finalCustArr = array();
        if(isset($data['customerId']))
        {
            $locationtype_details = DB::Table('location_types')->where('manufacturer_id', $data['customerId'])->get();
        }else{
            $locationtype_details = DB::Table('location_types')->get();
        }
        $allowedAddLocationType = $this->roleRepo->checkPermissionByFeatureCode('LOCT002');
        $allowedEditLocationType = $this->roleRepo->checkPermissionByFeatureCode('LOCT003');
        $allowedDeleteLocationType = $this->roleRepo->checkPermissionByFeatureCode('LOCT004');
        foreach ($locationtype_details as $value)
        {
            $custArr['location_type_id'] = $value->location_type_id;
            $custArr['location_type_name'] = $value->location_type_name;
            $custArr['manufacturer_id'] = $value->manufacturer_id;
            $custArr['actions'] = '';
            if($allowedEditLocationType)
            {
                $custArr['actions'] = $custArr['actions'] . '<span style="padding-left:5px;" ><a data-href="/customer/editlocationtype/' . $value->location_type_id . '" data-toggle="modal" data-target="#location_type_edit" class="btn btn-primary" ><img src="/img/edit.png" /></a></span>';
            }
            if($allowedDeleteLocationType)
            {
                $custArr['actions'] = $custArr['actions'] . '<span style="padding-left:5px;" ><a onclick="deleteLocationType(' . $value->location_type_id . ')" class="btn btn-primary"><img src="/img/delete.png" /></a></span>';
            }
            $finalCustArr[] = $custArr;
        }
        return json_encode($finalCustArr);
    }

    public function updateLocationType($locationTypeId)
    {

             DB::table('location_types')
                ->where('location_type_id', $locationTypeId)
                ->update(array(
                'location_type_name' => Input::get('location_type_name')
                 
                 ));
        return Response::json([
                    'status' => true,
                    'message' => 'Sucessfully updated.'
        ]);
    }

    public function deleteLocationType($locationTypeId)
    {
        $password = Input::get();
        $userId = Session::get('userId');
        $verifiedUser = $this->roleRepo->verifyUser($password['password'], $userId);
        //print_r($verifiedUser);die;
        if($verifiedUser >= 1)
        {
            $loctype = DB::table('location_types')
                        ->where('location_type_id', '=', $locationTypeId)->update(array('is_deleted'=>1));;
            $countlocations = DB::table('locations')
                        ->where('location_type_id', '=', $locationTypeId)->count();
            if($countlocations > 0){
                $loc = DB::table('locations')
                        ->where('location_type_id', '=', $locationTypeId)
                        ->update(array('is_deleted'=>1));
            } else
                $loc = true;
            
            if($loctype && $loc)
            {
                return 1;
            }else{
                return 0;
            }
        }else{
            return "You have entered incorrect password !!";
        }
    }
    public function restoreLocationType($locationTypeId)
    {
        // $password = Input::get();
        // $userId = Session::get('userId');
        // $verifiedUser = $this->roleRepo->verifyUser($password['password'], $userId);
        // //print_r($verifiedUser);die;
        // if($verifiedUser >= 1)
        // {
            //return $locationTypeId;
            $loctype = DB::table('location_types')
                        ->where('location_type_id', '=', $locationTypeId)->update(array('is_deleted'=>0));;
            $countlocations = DB::table('locations')
                        ->where('location_type_id', '=', $locationTypeId)->count();
            if($countlocations > 0){
                $loc = DB::table('locations')
                        ->where('location_type_id', '=', $locationTypeId)
                        ->update(array('is_deleted'=>0));
            } /*else
                $loc = true;
            
            if($loctype && $loc)
            {
                return 1;
            }else{
                return 0;
            }
        }else{
            return "You have entered incorrect password !!";
        }*/
            return Response::json([
                    'status' => true,
                    'message' => 'Sucessfully restored Location Type and its locations.'
        ]);
    }    
    
    public function uploadHandler()
    {
        $upload_handler = new Customers\UploadHandler();
    }

    /* locations types methods ends */
    
    /* Transaction types methods start*/

    public function viewTransaction()
    {
        return View::make('customers.transaction');
    }

   
    
    protected function validateTransaction($data)
    {
        $validator = Validator::make(
            array(
                'name' => Input::get('name'),
                'action_code' => Input::get('action_code'),
                'srcLoc_action' => Input::get('srcLoc_action'),
                'dstLoc_action' => Input::get('dstLoc_action'),
                'intrn_action' => Input::get('intrn_action'),
                'manufacturer_id' => Input::get('manufacturer_id'),
                'feature_code' => Input::get('feature_code')
            ), array(
                'name' => 'required',
                'action_code' => 'required',
                'srcLoc_action' => 'required',
                'dstLoc_action' => 'required',
                'intrn_action' => 'required',
                'manufacturer_id' => 'required',
                'feature_code' => 'required'
            )
        );
        if ($validator->fails())
        {
            return $validator->messages();
        }else{
            return 1;
        }
    }

    public function saveTransaction()
    {
        $validate = $this->validateTransaction(Input::all());
        if($validate != 1)
        {
            return Response::json([
                'status' => false,
                'message' => [$validate]
            ]);
        }
        DB::table('transaction_master')->insert([
            'name' => Input::get('name'),   
            'description' => Input::get('description'),
            'action_code' => Input::get('action_code'),
            'srcLoc_action' => Input::get('srcLoc_action'),
            'dstLoc_action' => Input::get('dstLoc_action'),
            'intrn_action' => Input::get('intrn_action'),
            'manufacturer_id'=>Input::get('manufacturer_id'), 
            'feature_code' => Input::get('feature_code'),
            'group' => Input::get('group')
        ]);
        return Response::json([
                    'status' => true,
                    'message' => 'Sucessfully added.'
        ]);
    }

    public function editTransaction($id)
    {
        $transuser = DB::table('transaction_master')->where('id', '=', $id)->first();
        return Response::json($transuser);
    }

    public function updateTransaction($id)
    {
        $validate = $this->validateTransaction(Input::all());
        //return $validate;
        if($validate != 1)
        {
            return Response::json([
                'status' => false,
                'message' => [$validate]
            ]);
        }
        DB::table('transaction_master')
                ->where('id', $id)
                ->update(array(
                    'name' => Input::get('name'), 
                    'description' => Input::get('description'),
                    'action_code' => Input::get('action_code'),
                    'srcLoc_action' => Input::get('srcLoc_action'),
                    'dstLoc_action' => Input::get('dstLoc_action'),
                    'intrn_action' => Input::get('intrn_action'),
                    'feature_code' => Input::get('feature_code'),
                    'group' => Input::get('group')
                ));
        return Response::json([
                    'status' => true,
                    'message' => 'Sucessfully updated.'
        ]);
    }

    public function deleteTransaction($id)
    {
        DB::table('transaction_master')->where('id', '=', $id)->delete();
        //return Redirect::to('/customer/onboard');
        return Response::json([
                    'status' => true,
                    'message' => 'Sucessfully Deleted.'
        ]);
    }

    /* Transaction types methods ends*/
    
    public function getDownload($type){
         $xls_list = ["FG_Material_Codes","Locations"];
        if(in_array($type,$xls_list)){
            $file= public_path(). "/download/".$type.".xls";
        $headers = array(
              'Content-Type: application/pdf',
            );
        return Response::download($file, $type.'.xls', $headers);        
        }
        else{
            $file= public_path(). "/download/".$type.".csv";
        $headers = array(
              'Content-Type: application/pdf',
            );
        return Response::download($file, $type.'.csv', $headers);    
        }
        
    }


    public function erpuniquevalidation(){

//public function erpCodeUniquevalidation(){
        Log::info(__FUNCTION__);
    $data = Input::all();
    //dd($data);
    Log::info($data);

    try
        {
            $customerId = isset($data['manufacturer_id']) ? $this->roleRepo->decodeData($data['manufacturer_id']) : 0;
            Log::info($customerId);

            $tableName = isset($data['table_name']) ? $data['table_name'] : '';
            $code = isset($data['erp_code']) ? $data['erp_code'] : '';
            $rowData = 1;
            $requestType = isset($data['request_type']) ? $data['request_type'] : 'create';
            if($requestType == 'edit'){
                $location_id = isset($data['location_id']) ? $data['location_id'] : 0;
            }
            if($customerId){
                if($requestType == 'edit' && $location_id  > 0 ){

                    Log::info("-----------------edit----------------------");
                    //dd("ggg");
                    $rowData = DB::table($tableName)->where('manufacturer_id',$customerId)->where('location_id','!=',$location_id)->where('erp_code',$code)->count();
                    //dd($rowData);
                    //$rowData =count($rowData);
                }
                else if($requestType == 'create'){
                    Log::info("-----------------create----------------------");
                    $rowData = DB::table($tableName)->where('manufacturer_id',$customerId)->where('erp_code',$code)->count();
                    //dd($rowData);
                }    
            }
            else{
                return json_encode(['valid' => false,'message' => 'Please select the valid customer']);
            }
            

               if($rowData == 0)
                {                    
                    return json_encode([ 'valid' => true ]);
                }else{                    
                    return json_encode([ 'valid' => false,'message' =>'ERP Code already Exists' ]);
                }
            
            return json_encode([ 'valid' => false ]);
        } catch (\ErrorException $ex) {
            

            return json_encode([ 'valid' => false, 'message' => $ex->getMessage() ]);
        }

}



 }
