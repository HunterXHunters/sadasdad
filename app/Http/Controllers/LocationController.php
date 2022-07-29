<?php
namespace App\Http\Controllers;
use App\Models\Locations;
ini_set('memory_limit', '-1');
set_time_limit(0);

use App\Repositories\RoleRepo;
use App\Repositories\OrderRepo;
use App\Repositories\CustomerRepo;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Repositories\Locations;
use Illuminate\Support\Facades\Input;

class LocationController extends BaseController
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
        return View::make('locations/locationview')->with(['allow_buttons' => ['add' => $allowAddCustomer]]);
    }
    
    public function location_fifo(){
        parent::Breadcrumbs(array('Home'=>'/','Company'=>'#')); 
        $vendors=[];
        $locationTypes=DB::table('location_types')->where('manufacturer_id',Session::get('customerId'))->get();
         /*$vendors=DB::table('locations as l')
                //->join('users as u','l.location_id','=','u.location_id')
                //->where('u.customer_type',1000)
                ->where('l.parent_location_id',0)
                ->get(['l.location_id','l.erp_code','l.location_name as username']);*/
        return View::make('locations/location_fifo')->with(['vendors'=>$vendors,'locationTypes'=>$locationTypes]);
    }

    public function getLocationsByLocationsType($locationType){
      $data=Input::get();
      $locations=DB::table('locations as l')
      ->where('l.location_type_id',$locationType)
      ->get(['l.location_id','l.erp_code','l.location_name as username']);
      echo json_encode($locations); exit;
    }
     public function getProductsNstorageLocByUser(){
      $locationId=Input::get('vendor');
     // $user_id=458;
     // $locationId = DB::table('users')->where('user_id',$user_id)->lists('location_id');
     // $pids = DB::table('product_locations')->whereIn('location_id',$locationId)->lists('product_id');
      $products = DB::table('products as p')
                    ->join('product_locations as pl','p.product_id','=','pl.product_id')
                    ->where('pl.location_id',$locationId)
                    ->distinct()->get(['p.product_id','p.name','p.description','p.material_code','pl.fifo']);
        $locations =$query=DB::table('locations as l')
                   ->join('location_types as lt','l.location_type_id','=','lt.location_type_id')
                   ->leftjoin('fifo_storage_locations as fl','l.location_id','=','fl.storage_loaction_id')
                   ->where('lt.location_type_name','Storage Location')
                   ->where('l.parent_location_id',$locationId)
                   ->get(['l.location_id','l.location_name','l.erp_code','fl.storage_loaction_id']);
        return ['products'=>$products,'locations'=>$locations];
    }

     public function getFifoProducts(){
      $locationId = Input::get('vendor');
      $slocs = DB::table('fifo_storage_locations')->where('location_id',$locationId)->lists('storage_loaction_id');
      $products = DB::table('products as p')
                    ->join('product_locations as pl','p.product_id','=','pl.product_id')
                    ->where('pl.location_id',$locationId)
                    ->distinct()->get(['p.product_id','p.name','p.description','p.material_code','pl.fifo','pl.id as plid','pl.fifo_mandatory']);

      $return =array();
      foreach ($products as $key => $value) {
        $temp=array();
        $temp['product_id']=$value->product_id;
        $temp['description']=$value->description;
        $temp['material_code']=$value->material_code;
        //$temp['fifo']=$value->fifo;
        if($value->fifo){
          $temp['fifo']='Fifo Enabled';
          $temp['mandatory']='<input type="checkbox" class="changeMandatory" data-plid="'.$value->plid.'" '.($value->fifo_mandatory==1?'checked':'').' data-toggle="toggle"  data-on="Enabled" data-off="Disabled" >';
          $temp['sloc']=implode(',',array_unique($slocs));
        } else{
          $temp['fifo']='';
          $temp['mandatory']='';
          $temp['sloc']='';
        }
       
        $return[]=$temp;
      }
      return ['products'=>$return];
    }

    public function fifoMandotoryUpodate(){
      $status=1;
      try{
      $data=Input::get();
      $updateFifoForLocation=DB::table('product_locations')->where('id',$data['plid'])->update(['fifo_mandatory'=>$data['checked']]);
      $message='Updated Successfully';
    }
    catch(Exception $e){
      $status=0;
      $message = $e->getMessage();
    }
    return json_encode(['status'=>$status,'message'=>$message]);   
    }

    public function updateFifoConfig(){
      $status=1;
      try{
      $data=Input::get();
     /* echo "<pre>";
      print_r($data['product_loc']);
      exit;*/
      //update all location data to 0
      $locationId = Input::get('vendor');
      $resetingFifoForLocation=DB::table('product_locations')->where('location_id',$locationId)->update(['fifo'=>0]);
      if(isset($data['product_loc'])){
        $updateFifoForLocation=DB::table('product_locations')->where('location_id',$locationId)->whereIn('product_id',$data['product_loc'])->update(['fifo'=>1]);
      }

      $deletingFifosloc=DB::table('fifo_storage_locations')->where('location_id',$locationId)->delete();
      if(isset($data['storage_loc'])){
        $slocations = DB::table('locations as l')
                     ->join('location_types as lt','l.location_type_id','=','lt.location_type_id')
                     ->where('lt.location_type_name','Storage Location')
                     ->where('l.parent_location_id',$locationId)
                     ->whereIn('l.location_id',$data['storage_loc'])
                     ->get(['location_id as storage_loaction_id','parent_location_id as location_id']);

        $slocationsInsert=DB::table('fifo_storage_locations')->insert(json_decode(json_encode($slocations),1));
      }
      $message='Updated Successfully';
    }
    catch(Exception $e){
      $status=0;
      $message = $e->getMessage();
    }
    return json_encode(['status'=>$status,'message'=>$message]);     

  } 
  

   


 }
