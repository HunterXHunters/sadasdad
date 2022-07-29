<?php 
set_time_limit(0);
ini_set('memory_limit', '-1');
use Central\Repositories\CustomerRepo;
use Central\Repositories\RoleRepo;
date_default_timezone_set("Asia/Calcutta"); 
header('Access-Control-Allow-Origin: *'); 
header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');

class StockoutApiController extends BaseController{

    protected $CustomerObj;
    protected $roleAccessObj;
    protected $roleid;
    protected $_labelorder;

            
public function __construct()
    { 
      if (!Session::has('userId')) {
                Redirect::to('/login')->send();
      }
        $product = new Products\Products();
        $productattr = new Products\ProductAttributes();
        $this->_product = $product;
        $this->_productattr = $productattr;
        $this->roleRepo = new RoleRepo;
        $this->_manufacturerId = $this->_product->getManufacturerId();
        // this is for connect to labelorders model 
        $this->_stockout = new Stockout\StockoutApi();
    }

    public function index(){   
    $cust_type =  Session::get('customerId');

    $userdid =Session::get('userId');

    $attributertypeid = $this->_stockout->getAttributeTypedetails($cust_type);

    $sourcelocations = $this->_stockout->getSourceLocations($userdid);
//echo "<pre/>";print_r($sourcelocations[0]->location_id);exit;
    $destilocations = $this->_stockout->getDestinationLocations($sourcelocations[0]->location_id);    

    $transType = $this->_stockout->getTransactionType($cust_type);

    $modulname = $this->_stockout->getModuleType($cust_type); 

    return View::make('stockout.index')
                        ->with('attributetypes', $attributertypeid)
                        ->with('transType', $transType)
                        ->with('sourcelocations', $sourcelocations)
                        ->with('destilocations', $destilocations)
                        ->with('modulname', $modulname);
    }

    public function savestockout(){
      $data = Input::all();
      $userid = session::get('userId');
      $accesstoken = $this->_stockout->getAccesstoken($data['module_id'],$userid);

      if($accesstoken == ""){
        return "please login into the system";
      }else{

        $data['access_token'] = $accesstoken[0]->access_token;

          //$arr = array('trn_name'=>$data['trn_name'],'vehicle_no'=>$data['vehicle_no'],'docket_no'=>$data['docket_no'],'invoice_date'=>$data['invoice_date']);

        
      $data['tpDataMapping'] = json_encode($data);  

    $request = Request::create('scoapi/SyncStockOut', 'POST', $data);
    $originalInput = Request::input();//backup original input
    Request::replace($request->input());
    $response = Route::dispatch($request)->getContent();//invoke API  
    $response = json_decode($response,true); 
    
return $response;
      }

    }

  /*  public function getdestinationlocations($id){
      $cust_type =  Session::get('customerId');

      $destiloca = $this->_stockout->getDestiLocation($cust_type,$id);
      $destdropdown= "";
      if($destiloca != "no data"){
      foreach($destiloca as $dest){
      $destdropdown .= "<option value='" . $dest->location_id . "'>" . $dest->location_name . "</option>";
    }
    return $destdropdown;
  }else{
    return "no data";
  }
       
    }*/
}