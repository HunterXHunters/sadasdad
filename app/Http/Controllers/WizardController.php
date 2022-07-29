<?php
use Central\Repositories\CustomerRepo;
class WizardController extends \BaseController {

	/**
	 * Display a listing of the resource.
	 *
	 * @return Respons
	 */
	  protected $_wizard;
    private $custRepo;
 
  	public function __construct()
  	{
  	    $wizard = new Wizard\Wizard;
  	    $this->_wizard = $wizard;
        $this->custRepo = new CustomerRepo;
  	}
    public function index()
    {
		return View::make('wizard/signup');
    }	
    public function saveSignup()
    {
        $data = Input::all();
        $returnObject = $this->_wizard->storeSignup($data);
        return $returnObject;   
  	}
    public function activateSignup()
    {
        $data = Input::all();
        $returnObject = $this->_wizard->activateSignup($data);
        return $returnObject;  
  	}  	
    public function validateEmail()
    {
        $data = Input::all();
        $returnObject = $this->_wizard->validateEmail($data);
        return $returnObject;  
  	}
    public function validateSignupEmail()
    {
        $data = Input::all();
        $returnObject = $this->_wizard->validateSignupEmail($data);
        return $returnObject;  
    }    
    public function validateCustomer()
    {
        $data = Input::all();
        $returnObject = $this->_wizard->validateCustomer($data);
        return $returnObject;  
    }    
  	public function sendEmailLink($activation_code)
  	{
  		$returnObject = $this->_wizard->sendEmailLink($activation_code);
  		if($returnObject)
  		{
  			return Redirect::to('wizard')->withFlashMessage('Activated Successfully.');
  		}else{
  			return Redirect::to('wizard')->withFlashMessage('Already Activated please login to '.URL::asset('/'));
  		}	
  	}	
    public function signUp()
    {
      $currentUserId = \Session::get('userId');
      \Log::info($currentUserId);
      $manufacturerId = DB::table('users')->where('user_id', $currentUserId)->pluck('customer_id');
      \Log::info($manufacturerId);  
      //return $manufacturerId;  
      $countries = $this->custRepo->getCountryData();    
      $channelCountries = DB::table('Channel')
                      ->join('countries','Channel.country_code','=','countries.iso_code_3')
                      ->select('Channel.country_code as country_code','countries.name as country_name')->distinct()->get();
      $channels = DB::table('Channel')->get();
      $erpData = DB::table('master_lookup')
                ->join('lookup_categories', 'lookup_categories.id', '=', 'master_lookup.category_id')
                ->where('lookup_categories.name', 'ERP Models')
                ->get(array('master_lookup.value', 'master_lookup.name'));
      $logistics = DB::table('carriers')->get();
      $custLogistics =DB::table('customer_logistics')->where('cust_id',$manufacturerId)->get();
      $custName = DB::table('eseal_customer')->where('customer_id',$manufacturerId)->pluck('brand_name');
      $locationTypes = DB::table('location_types')->where('manufacturer_id',0)->get();
      $currency=DB::table('currency')->select('currency_id','code')->get();
      $custSelChannelCountries = DB::table('manf_channels')
      ->join('Channel','Channel.channel_id','=','manf_channels.channel_id')
      ->where('Channel.country_code','!=','NULL')
      ->where('manf_channels.manf_id',$manufacturerId)->distinct()->pluck(DB::raw('group_concat(Channel.country_code)'));
      $custSelChannelCountries = explode(",",$custSelChannelCountries);
      $custSelCountryChannels = DB::table('Channel')->whereIn('country_code',$custSelChannelCountries)->get();
      $custSelectedChannels=DB::table('manf_channels')->where('manf_id',$manufacturerId)->pluck(DB::raw('group_concat(channel_id)'));
      $custSelectedChannels = explode(",",$custSelectedChannels);
      $getcustchannelLogistics = $this->_wizard->getcustchannelLogistics();
      $getcustchannelLogistics = json_decode($getcustchannelLogistics);
      $company = DB::table('eseal_customer')
                  ->join('countries','countries.country_id','=','eseal_customer.country_id')
                  ->where('customer_id',$manufacturerId)
                  ->select('brand_name','cin_number','pan_number','countries.country_id','countries.name as country','customer_id')
                  ->first();
      $erpintdata = DB::table('erp_integration')
                  ->select('erp_model','integration_mode','web_service_url','token','company_code','web_service_username','web_service_password','default_start_date')
                  ->where('manufacturer_id',$manufacturerId)
                  ->get();
      $custcnrct=DB::table('cust_contracts')
                 ->select('signup_type','modules')
                 ->where('cust_id',$manufacturerId)   
                 ->get();
      $custSelectedModules = DB::table('cust_contracts')
                          ->where('cust_id',$manufacturerId)
                          ->pluck(DB::raw('group_concat(modules)'));
      $custSelectedModules = explode(",",$custSelectedModules);
      $custsigndata=DB::table('master_lookup')
                ->join('lookup_categories', 'lookup_categories.id', '=', 'master_lookup.category_id')
                ->where('lookup_categories.name', 'Signup Type')
                ->get(array('master_lookup.value', 'master_lookup.name'));      
            
      $custplandata=DB::table('master_lookup')
                ->join('lookup_categories', 'lookup_categories.id', '=', 'master_lookup.category_id')
                ->where('lookup_categories.name', 'Plan')
                ->get(array('master_lookup.value', 'master_lookup.name'));  
      $finance = DB::table('cust_bank_dtl')
                  ->join('currency','currency.currency_id','=','currency')
                  ->where('eseal_cust_id',$manufacturerId)
                  ->select('vat_number','ifsc_code','benf_name','acc_type','acc_number','micr_code','bank_name','currency','currency.code')
                  ->first();
      $bank = DB::table('master_lookup')
                    ->join('lookup_categories','category_id','=','lookup_categories.id')
                    ->where('lookup_categories.name',"Banks")
                    ->select('master_lookup.name as bankname','master_lookup.value as bank_id')
                    ->get();
      /*$bank = DB::table('bank_info')->take(10)->get();*/
      //print_r($bank);exit;   
      $coupon_status = DB::table('customer_coupons')->where('cust_id',$manufacturerId)->where('activation_status',1)->get();
      $custChannelsDc=DB::table('manf_channels')
      ->join('Channel','Channel.channel_id','=','manf_channels.channel_id')
      ->where('Channel.country_code','!=','NULL')
      ->where('manf_channels.manf_id',$manufacturerId)->distinct()->get();  
      //print_r($custChannelsDc);exit;                             
      return View::make('signup/signup')->with('channelCountries',$channelCountries)->with('channels',$channels)->with('erpData',$erpData)
      ->with('logistics',$logistics)->with('manufacturerId',$manufacturerId)->with('countries',$countries)->with('locationTypes',$locationTypes)->with('currency',$currency)->with('custSelectedChannels',$custSelectedChannels)->with('custSelChannelCountries',$custSelChannelCountries)->with('custSelCountryChannels',$custSelCountryChannels)->with('getcustchannelLogistics',$getcustchannelLogistics)->with('custcnrct',$custcnrct)->with('custsigndata',$custsigndata)->with('custplandata',$custplandata)
      ->with('finance',$finance)->with('bank',$bank)->with('company',$company)->with('erpintdata',$erpintdata)->with('custSelectedModules',$custSelectedModules)->with('custChannelsDc',$custChannelsDc)->with('coupon_status',$coupon_status);
    } 

    public function getChannelsByCountry($data)
    {
      if($data){
        $data = explode(",",$data);
        $channels = DB::table('Channel')->whereIn('country_code',$data)->get();
      }else{
        $channels = DB::table('Channel')->where('country_code','!=','NULL')->get();
      }
      return $channels; 
    }
    public function saveCustomer()
    {
        $data=Input::get();
        $returnObject = $this->_wizard->saveCustomer($data);
        return $returnObject;       

    }
    public function saveCustomerData()
    {
      $data = Input::all();
      $returnObject = $this->_wizard->saveCustomerData($data);
      return $returnObject;
    }
    public function saveCustomerModuleContract()
    {
      $data = Input::all();
      $returnObject = $this->_wizard->saveCustomerModuleContract($data);
      return $returnObject;
    }  
    public function saveCustErpConfigurations()
    {
      $data = Input::all();
      $returnObject = $this->_wizard->saveCustErpConfigurations($data);
      return $returnObject;
    }
    public function saveCustChannelConfig()
    {
      $data = Input::all();
      $returnObject = $this->_wizard->saveCustChannelConfig($data);
      return $returnObject;
    }  
    public function createUser()
    {
      $data = Input::all();
      $returnObject = $this->_wizard->createUser($data);
      return $returnObject;
    } //saveCustLogistics 
    public function saveCustLogistics()
    {
      $data = Input::all();
      $returnObject = $this->_wizard->saveCustLogistics($data);
      return $returnObject;
    }  
    public function saveCustFinances()
    {
      $data = Input::all();
      $returnObject = $this->_wizard->saveCustFinances($data);
      return $returnObject;
    } 
    //saveCustDcSelection 
    public function saveCustDcSelection()
    {
      $data = Input::all();
      $returnObject = $this->_wizard->saveCustDcSelection($data);
      return $returnObject;
    }
    public function getCustomerDC()
    {
      $data = Input::all();
      $returnObject = $this->_wizard->getCustomerDC($data);
      return $returnObject;     
    }
    public function getCustomerDCGrid()
    {
      $data = Input::all();
      $returnObject = $this->_wizard->getCustomerDCGrid();
      return $returnObject;     
    }
    public function saveProduct()
    {
      $data = Input::all();
      $returnObject = $this->_wizard->saveProduct($data);
      return $returnObject;
    }
    public function thankYou()
    {
      return View::make('emails/thanks');
    }
    public function getCustomerProducts()
    {
      $data = Input::all();
      $returnObject = $this->_wizard->getCustomerProducts($data);
      return $returnObject;     
    }
    public function getLogisticsByChannel()
    {
      $data = Input::all();
      //return $data;
      $returnObject = $this->_wizard->getLogisticsByChannel($data);
      return $returnObject;     
    }    
    public function getDetailsbyPincode()
    {
      $data = Input::all();
      //print_r($data['pincode']);exit;
      $returnObject = $this->_wizard->getDetailsbyPincode($data);
      return $returnObject;     
    }  
    public function activateCouponCode()
    {
      $data = Input::all();
      $returnObject = $this->_wizard->activateCouponCode($data);
      return $returnObject;     
    }  
    public function gdsStatus()
    {
      $data = Input::all();
      $returnObject = $this->_wizard->gdsStatus($data);
      return $returnObject;     
    }        
    public function channelProductMapping()
    {
      $data = Input::all();
      $returnObject = $this->_wizard->channelProductMapping($data);
      return $returnObject;     
    }         
}	