<?php
namespace App\Http\Controllers;
use App\Models\Products;
 use App\Models\Customers;
use Maatwebsite\Excel\Facades\Excel;

set_time_limit(0);
ini_set('memory_limit', '-1');

//use App\Models\S3;
use App\Repositories\RoleRepo;
use App\Repositories\OrderRepo;
use App\Repositories\CustomerRepo;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Repositories\Locations;
use Illuminate\Support\Facades\Input;

use Session;
use DB;
use View;
//use Input;
use Validator;
use Redirect;
use Log;
use Exception;

class ProductController extends BaseController
{

    protected $_product;
    protected $_manufacturerId;
    private $roleRepo;
    public $_request;
    public function __construct(OrderRepo $OrderObj, CustomerRepo $custRepoObj,Request $request) {

        $this->OrderObj = $OrderObj;

        $this->custRepoObj = $custRepoObj;
        $this->_request=$request;
        $product = new Products\Products();
        $productattr = new Products\ProductAttributes();

        $this->_onboarding = new Customers\Onboarding();
        //$this->_onboarding = $onboarding;
        $this->custRepo = new CustomerRepo;
        $this->roleRepo = new RoleRepo;
        $this->_esealCustomer = new Customers\EsealCustomers();        
        $this->_manufacturerId = $this->custRepo->getManufacturerId();
          // echo $this->_manufacturerId;exit;
        $this->_product = $product;
        $this->_productattr = $productattr;
        //$this->roleRepo = new RoleRepo;
        //$this->_manufacturerId = $this->_product->getManufacturerId();
    }

    private function getTime(){
        $time = microtime();
        $time = explode(' ', $time);
        $time = ($time[1] + $time[0]);
        return $time;
    }

    public function index()
    {
        $customer = Session::get('customerId');
        parent::Breadcrumbs(array('Home' => '/', 'Products' => '#'));
        $manufacturers = $this->_product->getManufacturers($this->_manufacturerId);
        //print_r($manufacturers);exit;
        $allowAddProduct = $this->roleRepo->checkPermissionByFeatureCode('PRD001');        
        $allowImportCsv = $this->roleRepo->checkPermissionByFeatureCode('PRD007');
        $allowImportErp = $this->roleRepo->checkPermissionByFeatureCode('PRD008');
        $allowAddComponentProduct = $this->roleRepo->checkPermissionByFeatureCode('PRD009');

        $listcat = DB::table('categories')->pluck('name')->toArray();

       //echo "<pre/>";print_r($listcat);exit;
        $listgroup = DB::table('product_groups')->where('manufacture_id','=',$this->_manufacturerId)->pluck('name','group_id')->toArray();
        //print_r($listgroup);exit;

        return View::make('products/index')->with(array('manufacturers' => $manufacturers, 'allow_buttons' => ['add' => $allowAddProduct, 'import_csv' => $allowImportCsv, 'import_erp' => $allowImportErp, 'add_component' => $allowAddComponentProduct],'customer'=>$customer,'listcat'=>$listcat,'listgroup'=>$listgroup));
    }
    
    public function ageing(){
    parent::Breadcrumbs(array('Home'=>'/','Company'=>'#')); 
    $data = Input::all();
    $products=DB::table('product_groups as pg');
    if(isset($data['search'])){
        if(isset($data['ser_product_id'])){
            $products=$products->where('pg.group_id',$data['ser_product_id']);
        }
        if(isset($data['onlygroup'])){
            $products   = $products->get();    
        }   else {
            $products   = $products->join('products as p','p.group_id','=','pg.group_id')
                            ->get(['p.product_id','p.name','pg.group_id','p.expiry_period','p.material_code','pg.block_period']);
        }  

        return json_encode($products);
    } 
    else 
    {
       /* $products=$products->join('products as p','p.group_id','=','pg.group_id')
                            ->get(['p.product_id','p.name','p.group_id','p.expiry_period','p.material_code','pg.block_period']);*/
        $products   = $products->get();    
        return View::make('products/ageing')->with(['products'=>$products]);
    }
    }

    public function ageing_report(){
        $mfgr=5;
        parent::Breadcrumbs(array('Home'=>'/','Company'=>'#')); 
        $data = Input::all();
        $mail=isset($data['mail'])?trim($data['mail']):0;
        /*
        $countList=DB::table('product_locations as pl')
        ->join('locations as l','l.location_id','=','pl.location_id')
        ->join('products as p','p.product_id','=','pl.product_id')
        ->join('location_types as lt', function ($join) use ($mfgr) {
            $join->on('l.location_type_id', '=','lt.location_type_id')
                 ->where('lt.location_type_name', '=', "'Warehouse'")
                 ->where('lt.manufacturer_id', '=', $mfgr);
        })->join('eseal_'.$mfgr.' as e', function ($join) use ($mfgr) {
            $join->on('e.pid', '=', 'p.product_id')
                 ->where('e.level_id', '=', 0);
        })->join('track_history as th', function ($join){
            $join->on('th.track_id', '=', 'e.track_id')
                 ->where('th.src_loc_id', '=', 'l.location_id');
        })->groupBy('e.pid','th.src_loc_id','e.level_id')
        ->get([DB::raw("count('e.primary_id') as cnt"),'e.pid','th.src_loc_id','e.level_id']);*/
        $qry="SELECT COUNT('e.primary_id') AS cnt,datediff(now(),e.mfg_date) as age, `e`.`pid`, `th`.`src_loc_id`, `e`.`level_id`,l.location_name,p.material_code,p.description,e.mfg_date
        FROM `product_locations` AS `pl`
        INNER JOIN `locations` AS `l` ON `l`.`location_id` = `pl`.`location_id`
        INNER JOIN `products` AS `p` ON `p`.`product_id` = `pl`.`product_id`
        INNER JOIN `location_types` AS `lt` ON `l`.`location_type_id` = `lt`.`location_type_id` AND `lt`.`location_type_name` = 'Warehouse' AND `lt`.`manufacturer_id` = ".$mfgr."
        INNER JOIN `eseal_".$mfgr."` AS `e` ON `e`.`pid` = `p`.`product_id` AND `e`.`level_id` = 0
        INNER JOIN `track_history` AS `th` ON `th`.`track_id` = `e`.`track_id` AND `th`.`src_loc_id` = l.location_id
        GROUP BY `e`.`pid`, `th`.`src_loc_id`, `e`.`level_id`,age";
//echo $qry; exit;
    $dataArray=DB::select($qry);

    $return=array();
    $return['data']=array();
    $return['locations']=array();


    $trdata=array();
    $trdata['100p']=0;
    $trdata['80p']=0;
    $trdata['60p']=0;
    $trdata['40p']=0;
    $trdata['40l']=0;
    foreach ($dataArray as $key => $value) {

        if(!isset($return['data'][$value->src_loc_id][$value->pid]))
            $return['data'][$value->src_loc_id][$value->pid]=$trdata;
        if($value->age>99)
            $return['data'][$value->src_loc_id][$value->pid]['100p']+=$value->cnt;
        else if($value->age>79)
            $return['data'][$value->src_loc_id][$value->pid]['80p']+=$value->cnt;
        else if($value->age>59)
            $return['data'][$value->src_loc_id][$value->pid]['60p']+=$value->cnt;
        else if($value->age>39)
            $return['data'][$value->src_loc_id][$value->pid]['40p']+=$value->cnt;
        else 
            $return['data'][$value->src_loc_id][$value->pid]['40l']+=$value->cnt;
        /*if(!isset($return['data'][$value->src_loc_id][$value->pid]))
        $return['data'][$value->src_loc_id][$value->pid]=$value->cnt;
        else 
        $return['data'][$value->src_loc_id][$value->pid]+=$value->cnt;*/
        $return['locations'][$value->src_loc_id]=$value->location_name;
        $return['mat_code'][$value->pid]=$value->material_code;
        $return['mat_name'][$value->pid]=$value->description;
    }
   /* echo "<pre>";
    print_r($return);
    exit;*/
    if(!$mail)
    return View::make('products/ageing_report')->with(['dataArray'=>$return]);
    else {
        echo "test"; exit;
    }
}
 

    public function fifoviolations_report(){
       // $mfgr=5;
        parent::Breadcrumbs(array('Home'=>'/','Company'=>'#')); 
        $data = Input::all();
        $cust_id=$this->_manufacturerId;

       

        $locations=  DB::table('locations as l')
                    ->join('location_types as lt','l.location_type_id','=','lt.location_type_id')
                    ->where('l.manufacturer_id',$this->_manufacturerId)
                    ->where('lt.location_type_name','Import Supplier')
                    ->get(['l.location_id','l.location_name','l.erp_code']);

        $fifoviolations=DB::table('fifoviolations as f')->join('locations as l','l.location_id','=','f.location_id')->join('products as p','p.material_code','=','f.material_code');
        if(isset($data['location_id'])){
              $fifoviolations=$fifoviolations->where('l.location_id',$data['location_id']);
        }
        $fifoviolations=$fifoviolations->groupBy('material_code','location_id')->get(['f.primary_id','f.material_code','f.prod_batch','f.old_batch','f.location_id','l.erp_code','p.name',DB::raw('count(*) as cnt')]);
      /*  echo "<pre>";
        print_r($fifoviolations);
        exit;*/
        //$fifoviolations=[];

        return View::make('products/fifoviolations_report')->with(['fifoviolations'=>$fifoviolations,'cust_id'=>$cust_id,'locations'=>$locations]);

    }

    public function updateAgeingConfig(){
        $status=1;
      try{
        $data=Input::get();
        $products=DB::table('product_groups')->where('group_id',$data['product_group_id'])->update(['block_period'=>$data['block_period']]);
        $message='Updated Successfully';
        }
      catch(Exception $e){
      $status=0;
      $message = $e->getMessage();
    }
    return json_encode(['status'=>$status,'message'=>$message]);    
    }

    
    public function product_loc_maping_layout(){
        parent::Breadcrumbs(array('Home' => '/', 'Products' => '#'));
        $manufacturers = $this->_product->getManufacturers($this->_manufacturerId);
       
        $products=  DB::table('products as p')
                    ->join('product_groups as pg','p.group_id','=','pg.group_id')
                    ->where('p.manufacturer_id',$this->_manufacturerId)
                    ->where('pg.name','Import Products')
                    ->get(['p.name','p.product_id'])->toArray();
        $locations=  DB::table('locations as l')
                    ->join('location_types as lt','l.location_type_id','=','lt.location_type_id')
                    ->where('l.manufacturer_id',$this->_manufacturerId)
                    ->where('lt.location_type_name','Import Supplier')
                    ->get(['l.location_id','l.location_name','l.erp_code'])->toArray();
        $layouts=  DB::table('label_master')
                    ->where('manufacturer_id',$this->_manufacturerId)
                    ->where('labelCategory','vendor_import_products')
                    ->get(['value as template_id','name','template'])->toArray();

        //return View::make('products/location_products_maping')->with(array('manufacturers' => $manufacturers,'location_types'=>$location_types,'product_groups'=>$product_groups,'products'=>$products,'categories'=>$categories,'cust_id'=>$cust_id));
        return View::make('products/location_products_maping_layouts')->with(array('manufacturers' => $manufacturers,'locations'=>$locations,'layouts'=>$layouts,'products'=>$products,'cust_id'=>$this->_manufacturerId));
    }

    public function saveProductLocMaping_layout(){
        $data = Input::all();
        $lid=$data['location_id'];
        $data['layout_id']=trim(implode(',',$data['layout_id']));
        //print_r($data['product_id']); exit;
        //$pidList=explode(',', $data['product_id']);
        if(count($data['product_id'])<=0&&!$lid){
            return ['status'=>0,'message'=>'Please Select all fields'];
        }
        foreach ($data['product_id'] as $key => $pid) {
           if($lid && $pid && isset($data['layout_id'])){            
                    $materialExits=DB::table('product_locations')
                            ->where('product_id',$pid)
                            ->where('location_id',$lid)
                            ->count();
                    if($materialExits==0){
                         $insertPL=DB::Table('product_locations')->insertGetId([
                            'product_id' => $pid,
                            'layout_id' => $data['layout_id'],
                           'location_id' => $lid]);
                    } else {
                        //$data['layout_id']=trim(implode(',',$data['layout_id']));
                        $insertPL=DB::table('product_locations')
                            ->where('product_id',$pid)
                            ->where('location_id',$lid)
                            ->update(['layout_id' =>$data['layout_id']]);                  
                    }
                   // return ['status'=>1,'message'=>'Updated Successfully'];
                } else {
                   // return ['status'=>0,'message'=>'Please Select all fields'];
                }            
            }
             return ['status'=>1,'message'=>'Updated Successfully'];

        }
        

    public function getProductLocMaping_layout(){
        $data = Input::all();
        $pid=$data['product_id'];
        $lid=$data['location_id'];
        $materialExits=DB::table('product_locations')
                    ->where('product_id',$pid)
                    ->where('location_id',$lid)
                    ->lists('layout_id');
        $layout=trim(implode(',',$materialExits));
        return ['status'=>1,'layout'=>$layout];      
    }


    public function getproduct_loactionmapping_layout(){
       
        $data = Input::all();

        $product_locations=DB::table('product_locations as pl');
        $product_locations = $product_locations->join('products as p', 'pl.product_id','=','p.product_id');
        $product_locations = $product_locations->join('locations as l', 'pl.location_id','=','l.location_id');
        $product_locations = $product_locations->join('product_groups as pg','p.group_id','=','pg.group_id');
        $product_locations = $product_locations->join('location_types as lt','l.location_type_id','=','lt.location_type_id');

        if(isset($data['location_id'])){
            $product_locations = $product_locations->whereIn('pl.location_id',$data['location_id']);
        }
        if(isset($data['product_id'])){
            $product_locations = $product_locations->whereIn('pl.product_id',$data['product_id']);
        }
        $product_locations = $product_locations->where('pg.name','Import Products')
        ->where('lt.location_type_name','Import Supplier');
        $concat='concat("<a onclick=\'config(",p.product_id,",",l.location_id,")\' >", (CASE 
            WHEN  pl.layout_id IS NULL
            THEN \'Config\'
            ELSE pl.layout_id END ) ,"</a>") as layout_id';
        
        $product_locations = $product_locations->select('p.name','p.material_code','l.location_name','l.erp_code',DB::raw($concat) )->get();
        $layouts=  DB::table('label_master')
                    ->where('manufacturer_id',$this->_manufacturerId)
                    ->where('labelCategory','vendor_import_products')
                    ->get(['value as template_id','name','template']);
        echo json_encode($product_locations);
        exit;

    }

    public function product_loc_maping(){

        $manuId = Session::get('customerId');
        //print_r($manuId);exit;
        parent::Breadcrumbs(array('Home' => '/', 'Products' => '#'));
        $manufacturers = $this->_product->getManufacturers($this->_manufacturerId);
       // print_r($manufacturers);exit;
        $allowAddProduct = $this->roleRepo->checkPermissionByFeatureCode('PRD001');        
        $allowImportCsv = $this->roleRepo->checkPermissionByFeatureCode('PRD007');
        $allowImportErp = $this->roleRepo->checkPermissionByFeatureCode('PRD008');
        $allowAddComponentProduct = $this->roleRepo->checkPermissionByFeatureCode('PRD009');
        $location_types=DB::table('location_types')->where('manufacturer_id',$manuId)->where('is_deleted',0)->get()->toArray();
        //print_r($location_types);exit;
        $locations=DB::table('locations')->where('is_deleted',0)->get(['location_id','location_name'])->toArray();

        $product_groups= DB::table('product_groups')->where('manufacture_id',$manuId)->get(['name','group_id'])->toArray();
        //print_r($product_groups);exit;
          $categories = DB::table('categories')->value('name','category_id');
        $products=  DB::table('products')->where('manufacturer_id',$manuId)->get(['name','product_id'])->toArray();
        // /print_r($products);exit;
       //  dd($products);die;
        $errors=explode('|', $this->_request->get('result'));
        return View::make('products/location_products_maping',['manufacturers' => $manufacturers,'locations'=>$locations,'location_types'=>$location_types,'product_groups'=>$product_groups,'products'=>$products,'categories'=>$categories,'cust_id'=>$manuId,'errors'=>$errors]);
    }

    public function getProductLocMaping(){
       
        $data = $this->_request->all();
        // print_r($data);exit;
        $manufacturerId = isset($data['manufacturerID']) ? $data['manufacturerID'] : 0;
        $tableName = isset($data['table_name']) ? $data['table_name'] : 'products';
        $operation = isset($data['operation']) ? $data['operation'] : 'products';
        $filePath = isset($_FILES['files']['tmp_name']) ? $_FILES['files']['tmp_name'] : '';
        $fileName = isset($_FILES['files']['name']) ? $_FILES['files']['name'] : '';
        $extension = '';
        if ($fileName != '')
        {
            $extension = pathinfo($_FILES['files']['name'], PATHINFO_EXTENSION);
        }
        $allowed_Extensions = ['XLS','XLSX','xls','xlsx'];

        if( !in_array(strtoupper($extension), $allowed_Extensions))
        {
            return response()->json([
                    'status' => false,
                    'sucess_records' => 0,
                    'message' => 'Please upload an Excel file with .xls or .xlsx extension.'
                ]);
            //return 'Please provide CSV file.';
        }
        $errorMessage = '';
        if ($filePath != '')
        {
            if (!$fh = fopen($filePath, 'r'))
            {
                throw new Exception("Could not open".$filePath." for reading.");
            }
            $i = 0;
            $buffer = array();
            $tempArray = array();
            $path = $this->_request->file('files')->getRealPath();
            //print_r($path);exit;
            $tempArray = Excel::load($path, function($reader) {})->get()->toArray();
            $metrialcodes=$locationserps=[];
            $scount=0;
            $fcount=0;
            $ecount=0;
            $dataStatus=[];
            // print_r($tempArray);exit;
           // $x =Session::get('customerId');
           // echo $x;exit;
            foreach ($tempArray as $key => $value) {
                $dataStatusTmp='product_material_code:'.$value['product_material_code'].'===location_erp_code'.$value['location_erp_code'].'===>';
                $pid=DB::table('products')
                            ->where('material_code',$value['product_material_code'])
                            ->where('manufacturer_id',Session::get('customerId'))
                            ->value('product_id');
                $lid=DB::table('locations')
                            ->where('erp_code',$value['location_erp_code'])
                            ->where('manufacturer_id',Session::get('customerId'))
                            ->value('location_id');
                $dataStatusTmp.='=====>p:'.$pid.'l:'.$lid;
                // echo "hai".$pid;exit;
                if($lid && $pid){
                    $materialExits=DB::table('product_locations')
                            ->where('product_id',$pid)
                            ->where('location_id',$lid)
                            ->count('id');
                            // echo "x".$materialExits;exit;
                    if($materialExits==0){
                        $dataStatusTmp.=' added successufully';
                        $insertPL=DB::Table('product_locations')->insertGetId([
                            'product_id' => $pid,
                            'location_id' => $lid]);
                        $scount++;
                       
                    } 
                     else {
                        $dataStatusTmp.=' duplicate exists';
                        $ecount++;
                    }
                        //$scount++;
                } else {
                    $dataStatusTmp.=' invalid erp or matrial';
                    $fcount++;
                }
                $dataStatus[]=$dataStatusTmp;
              //  $dataStatus[]=$dataStatusTmp;
            }
           // echo "response";exit;
             return response()->json([
                    'status' => true,
                    'sucess_records' => $scount,
                    'failed_records' => $fcount,
                    'existing_records' => $ecount,
                    //'dataStatus' => $dataStatus,
                    'message' => 'products mapped successufully'
                ]);
         

                
        }
    }

    public function getproduct_loactionmapping(){
       $errors=explode('|', $this->_request->get('result'));
        //$data = Input::all();
        $data =$this->_request->all();
        // print_r($data);exit;
        $product_locations=DB::table('product_locations as pl');
        $product_locations = $product_locations->join('products as p', 'pl.product_id','=','p.product_id');
        $product_locations = $product_locations->join('locations as l', 'pl.location_id','=','l.location_id');
        if(isset($data['location_id'])){
            $product_locations = $product_locations->whereIn('pl.location_id',$data['location_id']);
        }
        if(isset($data['product_id'])){
            $product_locations = $product_locations->whereIn('pl.product_id',$data['product_id']);
        }
        $product_locations = $product_locations->select('p.name','p.material_code','l.location_name','l.erp_code','pl.id')->get()->toArray();

        $dataArr = array();
        foreach($product_locations as $det) {
                    $checkbox = '<input type="checkbox" style = "zoom:1.5;margin-left:32px;margin-top:1px" id = "chk" name="chk[]" value="'.$det->id.'">';         
                    $dataArr[] = array(
                            'chk'=>$checkbox,
                            'name'=>$det->name,
                            'material_code'=>$det->material_code,
                            'location_name'=>$det->location_name,
                            'erp_code'=>$det->erp_code,

                    );
                }
                // print_r($dataArr);exit;
      return json_encode($dataArr);
        //exit;

    }


    public function gdsIndex()
    {
        parent::Breadcrumbs(array('Home' => '/', 'Products' => '#'));
        $manufacturers = $this->_product->getManufacturers($this->_manufacturerId);
        $allowAddProduct = $this->roleRepo->checkPermissionByFeatureCode('PRD001');        
        $allowImportCsv = $this->roleRepo->checkPermissionByFeatureCode('PRD007');
        $allowImportErp = $this->roleRepo->checkPermissionByFeatureCode('PRD008');
        $allowAddComponentProduct = $this->roleRepo->checkPermissionByFeatureCode('PRD009');
        return View::make('products/gdsindex')->with(array('manufacturers' => $manufacturers, 'allow_buttons' => ['add' => $allowAddProduct, 'import_csv' => $allowImportCsv, 'import_erp' => $allowImportErp, 'add_component' => $allowAddComponentProduct]));
    }

    public function create()
    {
        parent::Breadcrumbs(array('Home' => '/', 'Products' => 'products/index', 'New Product' => '#'));
        $data = $this->_product->getProductFields($this->_manufacturerId);

        //echo "<pre/>";print_r($data);exit;
        return View::make('products/create')->with("data", $data);
    }
    public function create1()
    {
        parent::Breadcrumbs(array('Home' => '/', 'Products' => 'products/index', 'New Product' => '#'));
        $data = $this->_product->getProductFields($this->_manufacturerId);

        //echo "<pre/>";print_r($data);exit;
        return View::make('products/addproduct_new')->with("data", $data);
    }

    public function gdsCreate()
    {
        parent::Breadcrumbs(array('Home' => '/', 'Products' => 'products/index', 'New Product' => '#'));
        //$data = $this->_product->getProductFields($this->_manufacturerId);
        return View::make('products/gdscreate')->with("data", $data);
    }

    public function saveProduct()
    {
        $data = $this->_request->all();
        //dd($data);
            //echo "hao".$productId;exit;
        // Start transaction!
        //DB::beginTransaction();          
        try
        {
            if(!empty($data))
            {
                $validator = Validator::make(
                                array(
                            'name' => isset($data['product']['name']) ? $data['product']['name'] : '',
                            'manufacturer_id' => isset($data['product']['manufacturer_id']) ? $data['product']['manufacturer_id'] : '',
                            'product_type_id' =>isset($data['product']['product_type_id'])? $data['product']['product_type_id'] : '',
                            'business_unit_id' =>isset($data['product']['business_unit_id'])?$data['product']['business_unit_id']:'',
                            'category_id' => isset($data['product']['category_id'])? $data['product']['category_id'] : '',
                            'uom_class_id' => isset($data['product']['uom_class_id'])? $data['product']['uom_class_id'] : '',
                            'material_code' => isset($data['product']['material_code'])? trim($data['product']['material_code']) : ''

               //             'product_type_id' => isset($data['product']['product_type_id']) ? $data['product']['product_type_id'] : '',
                //            'category_id' => isset($data['product']['category_id']) ? $data['product']['category_id'] : '',
                //            'business_unit_id' => isset($data['product']['business_unit_id']) ? $data['product']['business_unit_id'] : ''
                                ), array(
                            'name' => 'required',
                            'manufacturer_id' => 'required',
                            'product_type_id' => 'required',
                            'category_id' => 'required',
                            'business_unit_id' => 'required',
                            'uom_class_id' => 'required|not_in:0',
                            'material_code' =>'required|unique:products'
                                )
                );
                if($validator->fails())
                {
                    $data = $this->_product->getProductFields($this->_manufacturerId);
                    $errorMessages = json_decode($validator->messages());
                    $errorMessage = '';
                    if(!empty($errorMessages))
                    {
                        foreach($errorMessages as $field => $message)
                        {
                            $errorMessage = implode(',', $message);
                        }
                    }
                    return Redirect::back()->withInput($this->_request->all())->withErrors([$errorMessage]);
                }
            }
            $startTime = $this->getTime();
            if(isset($data['product']['material_code'])){
                $data['product']['material_code']= trim($data['product']['material_code']);
            }
            //$productId = $this->saveProduct();
            // $product_type_id=$this->_request->saveProduct($data);
            //     echo "hai";exit;
            // echo "hai".$productId;exit;
            // if ($productId)
            // {
            $productId = $this->_product->saveProduct($data);
            $data['product_id'] = $productId;
            if ($productId)
            {      
               //echo "test productSaveActions"; exit;
                $this->productSaveActions($data);
                $endTime = $this->getTime();
                DB::table('user_tracks')->insert(array('user_id'=>Session::get('userId'),'manufacturer_id'=>Session::get('customerId'),'message'=>'Successfully Created.'.json_encode($data),'service_name'=>'Create Product','status'=>1,'response_duration'=>($endTime - $startTime)));
                return Redirect::to('products/index')->with('message', 'Sucessfully Added');
            }
        } catch (\ErrorException $e)
        {
            dd("errorexception");
            // Rollback and then redirect
            // back to form with errors
            //DB::rollback();
            return Redirect::back()->withErrors($e->getMessage())->withInput();
        } catch (\Exception $e)
        {
            dd($e->getMessage());
            //DB::rollback();
            return Redirect::back()->withErrors($e->getMessage())->withInput();
        }
    }

    public function productSaveActions($data)
    {
        try
        {
            $productId = isset($data['product_id']) ? $data['product_id'] : '';
            if (isset($data['media']))
            {
                // save product media
                $product_media = new Products\ProductMedia();
                $product_media_id = $product_media->saveProductMedia($data);
            }

            if (isset($data['package']))
            {
                // save package details
                isset($data['package']) ? $data['package']['product_id'] = $productId : '';
                $product_package = new Products\ProductPackage();
                $product_package_id = $product_package->saveProductPackage($data);
            }else{
                DB::table('product_packages')->where('product_id', $productId)->delete();
            }

            if (isset($data['pallet']))
            {
                // save pallet details
                //$product_pallet = new Products\ProductPallet();
                //$product_pallet_id = $product_pallet->saveProductPallet($data);
            }
            
            if (isset($data['slab_rate']))
            {
                // save pallet details
                isset($data['slab_rate']) ? $data['slab_rate']['product_id'] = $productId : '';
                $this->_product->saveProductSlabRate($data);
            }
            
            if (isset($data['location']))
            {
                // save pallet details
                isset($data['location']) ? $data['location']['product_id'] = $productId : '';
                $this->_product->saveProductLocation($data);
            }elseif(isset($data)){
                DB::table('product_locations')->where('product_id', $productId)->delete();
            }
            
            if (isset($data['product_attribute_sets']))
            {
                // save pallet details
                isset($data['product_attribute_sets']) ? $data['product_attribute_sets']['product_id'] = $productId : '';
                $this->_product->saveProductAttributesets($data);
            }else{
                DB::table('product_attributesets')->where('product_id', $productId)->delete();
            }

            if (isset($data['service_center']))
            {
                // save service center details
                isset($data['service_center']) ? $data['service_center']['product_id'] = $productId : '';
                $product_service_center = new Products\ProductServiceCenter();
                $product_service_center_id = $product_service_center->saveProductServiceCenter($data);
            }

            // if (isset($data['attributes']))
            // {
            //     $product_attributes = new Products\ProductAttributes();
            //     $product_attributes_id = $product_attributes->saveProductAttributes($data);
            // }
            
            if (isset($data['component_selected']))
            {
                // save pallet details
                isset($data['component_selected']) ? $data['component_selected']['product_id'] = $productId : '';
                $this->_product->saveComponentProducts($data);
            }
            
            if (isset($data['prod_text_det']))
            {
                // save pallet details
                isset($data['prod_text_det']) ? $data['prod_text_det']['product_id'] = $productId : '';
                $this->_product->saveProductsGdsData($data);
            }

            $this->_product->saveCompleteData($productId);
        } catch (ErrorException $ex)
        {
            die($ex);
        }
    }

    public function editSaveProduct()
    {
        //$data = Session::all();
        $data=$this->_request->all();
        //dd($data);
        if (!empty($data) && isset($data['product_id']))
        {
        //echo "hai";exit;
            $requestFrom = isset($data['request_from']) ? $data['request_from'] : 'product';
            if ($requestFrom != 'gds')
            {
                $data['product']['is_gds_enabled'] = isset($data['product']['is_gds_enabled']) ? $data['product']['is_gds_enabled'] : 0;
                $data['product']['is_traceable'] = isset($data['product']['is_traceable']) ? 1 : 0;
                if ($data['product']['is_gds_enabled'] == 0 && (string) $data['product']['is_gds_enabled'] == 'on')
                {
                    $data['product']['is_gds_enabled'] = 1;
                }
                if ($data['product']['is_traceable'] == 0 && (string) $data['product']['is_traceable'] == 'on')
                {
                    $data['product']['is_traceable'] = 1;
                }
                if (isset($data['product']['is_serializable']) && $data['product']['is_serializable'] == 0 && (string) $data['product']['is_serializable'] == 'on')
                {
                    $data['product']['is_serializable'] = 1;
                }
                if (isset($data['product']['is_batch_enabled']) && $data['product']['is_batch_enabled'] == 0 && (string) $data['product']['is_batch_enabled'] == 'on')
                {
                    $data['product']['is_batch_enabled'] = 1;
                }
                if (isset($data['product']['inspection_enabled']) && $data['product']['inspection_enabled'] == 0 && (string) $data['product']['inspection_enabled'] == 'on')
                {
                    $data['product']['inspection_enabled'] = 1;
                }
                if (isset($data['product']['is_backflush']) && $data['product']['is_backflush'] == 0 && (string) $data['product']['is_backflush'] == 'on')
                {
                    $data['product']['is_backflush'] = 1;
                }
            }
            $startTime = $this->getTime();
            if(isset($data['product']['material_code'])){
              trim($data['product']['material_code']);  
            } 
             if(isset($data['product']['expiry_period'])){
              trim($data['product']['expiry_period']);  
            } 
            $this->_product->editSaveProduct($data);
            //print_r($data);exit;
            //dd($data);die;
            $this->productSaveActions($data);
            $endTime = $this->getTime();
                DB::table('user_tracks')->insert(array('user_id'=>Session::get('userId'),'manufacturer_id'=>Session::get('customerId'),'message'=>'Successfully Updated.'.json_encode($data),'service_name'=>'Update Product','status'=>1,'response_duration'=>($endTime - $startTime)));
            if ($requestFrom != 'gds')
            {
                return Redirect::to('products/index');
            }else{
                return Redirect::to('product/gdsindex');
            }
        }
        return Redirect::to('products/index');
    }

    public function editProduct($productId)
    { 
        //echo "hai";exit;
        try
        {
            $startTime = $this->getTime();
            if($productId)
            {
                $productId = $this->roleRepo->decodeData($productId);
            }        
            parent::Breadcrumbs(array('Home' => '/', 'Products' => 'products/index', 'Edit Product' => '#'));
            if (isset($productId) && $productId != '')
            {
                $productData = $this->_product->getProductData($productId);
                //print_r($productData);exit;
                
                if(!property_exists($productData, 'product_id'))
                {
                    return Redirect::to('products/index')->with('error_message', 'No Product Found');
                }
                $data = $this->_product->getProductFields($this->_manufacturerId, $productId);
                $endTime = $this->getTime();
                DB::table('user_tracks')->insert(array('user_id'=>Session::get('userId'),'manufacturer_id'=>Session::get('customerId'),'message'=>'Successfully Retrieved.','service_name'=>'Edit Product','status'=>1,'response_duration'=>($endTime - $startTime)));
                return View::make('products/edit')->with(array('productData' => $productData, 'data' => $data));
            } else
            {
                return response()->json([
                                'status' => 'false',
                                'message' => 'No Product Found']);
                //return response()->back()->withErrors(['No Product Found']);
            }
        } catch (Exception $ex) {
            return response()->json([
                                'status' => 'false',
                                'message' => 'No Product Found']);
            //return response()->back()->withErrors([$ex->getMessage()]);
        }
    }

    public function editGdsProduct($productId)
    {
        try
        {
            if($productId)
            {
                $productId = $this->roleRepo->decodeData($productId);
            }        
            parent::Breadcrumbs(array('Home' => '/', 'Products' => 'product/gdsindex', 'Edit GDS Product' => '#'));
            if (isset($productId) && $productId != '')
            {
                $productData = $this->_product->getProductData($productId);
                if(!property_exists($productData, 'product_id'))
                {
                    return Redirect::to('product/gdsindex')->with('error_message', 'No Product Found');
                }
                $data = $this->_product->getProductFields($this->_manufacturerId, $productId);
                return View::make('products/editgds')->with(array('productData' => $productData, 'data' => $data));
            } else
            {
                return Redirect::to('product/gdsindex')->with('error_message', 'No Product Found');
                //return response()->back()->withErrors(['No Product Found']);
            }
        } catch (Exception $ex) {
            return Redirect::to('product/gdsindex')->with('error_message', $ex->getMessage());
            //return response()->back()->withErrors([$ex->getMessage()]);
        }
    }

    public function getProducts()
    {
        try
        {
            $data = Session::all();
            //$data = Input::all();
           // echo "<pre/>";print_r($hh['customerId']);exit;
            $finalCustArr = $this->_product->getAllProducts($data);

             //print_r($finalCustArr);exit;

            return json_encode($finalCustArr);
        }   catch (ErrorException $ex)
        {
            echo $ex->getMessage();
        }
    }

    public function getGDSProducts()
    {
        try
        {
            $data = Input::all();
            $finalCustArr = $this->_product->getAllGdsProducts($data);
            return json_encode($finalCustArr);
        }   catch (ErrorException $ex)
        {
            echo $ex->getMessage();
        }
    }

    public function viewProduct()
    {
        parent::Breadcrumbs(array('Home' => '/', 'Products' => 'product/index', 'View Product' => '#'));
        $data = Input::all();
        $product_data = $this->_product->getProductData();
        return View::make('products/view')->with('product_data', $product_data);
    }

    public function getAttributeList()
    {
        $data = Input::all();
        $startTime = $this->getTime();
        $product_attributes = new Products\ProductAttributes();
        $attributeList = $product_attributes->getAttributeList($data);
        $endTime = $this->getTime();
                DB::table('user_tracks')->insert(array('user_id'=>Session::get('userId'),'manufacturer_id'=>Session::get('customerId'),'message'=>'Successfully Retrieved.','service_name'=>'Get Attributelist','status'=>1,'response_duration'=>($endTime - $startTime)));
        return json_encode($attributeList);
    }

    public function getElementData(Request $request)
    {
         //$data = Session::all();
         $data= $request->all();
        //print_r($data);exit;
        if (!empty($data))
        {
            $dataElement = isset($data['data_type']) ? $data['data_type'] : '';
            $dataValue = isset($data['data_value']) ? $data['data_value'] : '';
            $manufac_id = isset($data['manufacturer_id'])?$data['manufacturer_id']:"";
            //print_r($manufac_id);exit;
            $startTime = $this->getTime();
            switch ($dataElement)
            {

                case 'businessUnits':
                    $result = DB::table('business_units')->where('manufacturer_id', $dataValue)->get(array('business_unit_id', 'name'));
                    break;
                case 'attributeSets':
                    $result = DB::table('attribute_sets')->where('manufacturer_id', $dataValue)->get(array('attribute_set_id', 'attribute_set_name'));
                    if (empty($result))
                    {
                        $result = DB::table('attribute_sets')->where('manufacturer_id', 0)->get(array('attribute_set_id', 'attribute_set_name'));
                    }
                    break;
                case 'attributeGroups':
                    $result = DB::table('attributes_groups')
                    ->whereIn('manufacturer_id', array(0, $dataValue))
                    ->get(array('attribute_group_id', 'name'));
                    break;
                case 'getAttributeGroups':
                    $attributeSetId = isset($data['attribute_set_id']) ? $data['attribute_set_id'] : 0;
                    $attributeId = isset($data['attribute_id']) ? $data['attribute_id'] : 0;
                    if($attributeId)
                    {
                        $result = DB::table('attributes_groups')
                            ->where('manufacturer_id', $dataValue)
                            ->where('attribute_set_id', $attributeSetId)
                            ->get(array('attribute_group_id', 'name'));
                    }else{
                        $result = DB::table('attributes_groups')
                            ->where('manufacturer_id', $dataValue)
                            ->where('attribute_set_id', $attributeSetId)
                            ->get(array('attribute_group_id', 'name'));                        
                    }                    
                    break;
                case 'locations':
                    /*$result = DB::table('locations')->where(array('manufacturer_id' => $dataValue, 'is_deleted' => 0))->get(array('location_id', 'location_name'));*/
                        $result = DB::table('locations')
                            ->selectRaw('locations.location_id,concat(locations.location_name,"(",locations.erp_code,")") as location_name')
                            ->join('location_types','locations.location_type_id','=','location_types.location_type_id')                            
                            ->where(array('locations.manufacturer_id' => $dataValue, 'locations.is_deleted' => 0))
                            ->whereIn('location_types.location_type_name',array('Plant','Warehouse','Depot','supplier'))
                            ->get()->toArray();
                        break;
                case 'location_types':
                    $result = DB::table('location_types')->where(array('manufacturer_id' => $dataValue, 'is_deleted' => 0))->get(['location_type_id', 'location_type_name'])->toArray();
                    break;
                case 'groups':
                    $result = DB::table('product_groups')->where(array('manufacture_id' => $dataValue))->get(['group_id', 'name'])->toArray();
                    break;
                case 'categories':
                    // $result = DB::table('customer_categories as cust')
                    //         ->join('categories as cat', 'cat.category_id', '=', 'cust.category_id')
                    //         //->where('cust.customer_id', $dataValue)
                    //         ->get(array('cat.category_id', 'name'));
                $result = DB::table('categories as cat')
                            ->get(['cat.category_id', 'name'])->toArray();
                    break;
                case 'component_products':
                    $productId = isset($data['product_id']) ? $data['product_id'] : '0';
                    return $this->_product->getManufacturerProducts($dataValue, $productId);
                    break;
                case 'locations_groups':
                    $result1 = DB::table('locations')
                    ->join('location_types','locations.location_type_id','=','location_types.location_type_id')
                    ->whereIn('location_types.location_type_name',array('Plant','Warehouse','Depot','supplier'))
                    ->where(array('location_types.manufacturer_id' => $dataValue, 'location_types.is_deleted' => 0))
                    ->get(array('location_id as id', 'location_name as name'));
                    $result2 = DB::table('product_groups')->where(array('manufacture_id' => $dataValue))->get(['group_id as id', 'name'])->toArray();
                    $result = ['locations' => $result1, 'groups' => $result2];
                    break;
                case 'UOM': 
                    $result = DB::table('uom_classes')->where('manufacturer_id',intval($manufac_id))->get(['uom_name as name','id as ml_value'])->toArray(); 
                    break;  
                case 'role_transactions':
                    $result =DB::table('roles')->where('manufacturer_id',$manufac_id)->get(['name','role_id'])->toArray();
                    break;

                default:
                    break;
            }
            $endTime = $this->getTime();
                DB::table('user_tracks')->insert(array('user_id'=>Session::get('userId'),'manufacturer_id'=>Session::get('customerId'),'message'=>'Successfully Retrieved.','service_name'=>'GetElementdata','status'=>1,'response_duration'=>($endTime - $startTime)));
                //echo"<pre/>";print_r($result);exit;
                //echo "hai";exit;
            return json_encode($result);
        } else
        {
            return 'No Data Posted';
        }
    }
    
    public function importFromErp()
    {
        try
        {
            $data = Input::all();
            $productData = new \Products\ProductData();
            return $productData->erpDataImport($data);
        } catch (\ErrorException $ex) {
            return response()->json([
                'status' => false,
                'message' => $ex->getMessage()
            ]);
        }
    }

    /* Categories actions */

    public function saveCategory()
    {
        $categoryId = DB::Table('categories')->insertGetId([
            'name' => Input::get('name'),
            'parent_id' => Input::get('parent_id'),
            'status' => Input::get('status'),
            'top' => Input::get('top'),
            'column' => Input::get('column'),
            'sort_order' => Input::get('sort_order'),
        ]);
        
        if($this->_manufacturerId)
        {
            $insertData['customer_id'] = $this->_manufacturerId;
            $insertData['category_id'] = $categoryId;
            DB::table('customer_categories')->insert($insertData);
        }
        
        return response()->json([
                    'status' => true,
                    'message' => 'Sucessfully added.'
        ]);
    }
    
    public function saveManufacturerCategory()
    {
        $data = Input::all();
        $this->_product->saveManufacturerCategory($data);
        return response()->json([
                    'status' => true,
                    'message' => 'Sucessfully added.'
        ]);
    }

    public function getManufacturerCategory()
    {
        $data = Input::all();
        if(!empty($data) && $data['manufacturer_id'])
        {
            $manufacturerId = $data['manufacturer_id'];
            $categoryList = '';
            $categoryList = $this->_product->getManufacturerCategories($manufacturerId);
            $categoryLists = '';
            if(!empty($getManufacturerCategories))
            {
                $categoryLists = $categoryList->category_id;
            }
            if($categoryLists != '')
            {
                $categoryList = json_encode(explode(',', $categoryLists));
            }
            return response()->json([
                'status' => true,
                'categories' => $categoryList,
                'message' => 'Sucessfully added.'
            ]);
        }
        return response()->json([
            'status' => false,
            'message' => 'Unable to get list.'
        ]);
    }
    
    public function editCategory($category_id)
    {
        $catg = DB::Table('categories')->where('category_id', $category_id)->first();
        return response()->json($catg);
    }

    public function updateCategory($category_id)
    {
        DB::Table('categories')
                ->where('category_id', $category_id)
                ->update(array('name' => Input::get('name'),
                    'parent_id' => Input::get('parent_id'),
                    'status' => Input::get('status'),
                    'top' => Input::get('top'),
                    'column' => Input::get('column'),
                    'sort_order' => Input::get('sort_order')));

        //return Redirect::to('product/category');
        return response()->json([
                    'status' => true,
                    'message' => 'Sucessfully Edited.'
        ]);
    }
    
    public function deleteProduct($productId)
    {   
        $startTime = $this->getTime();
        $this->_product->deleteProduct($productId);
        DB::table('user_tracks')->insert(array('user_id'=>Session::get('userId'),'manufacturer_id'=>Session::get('customerId'),'message'=>'Successfully Deleted.'.$productId,'service_name'=>'Delete Product','status'=>1));
        $endTime = $this->getTime();
                DB::table('user_tracks')->insert(array('user_id'=>Session::get('userId'),'manufacturer_id'=>Session::get('customerId'),'message'=>'Successfully Deleted.','service_name'=>'delete Product','status'=>1,'response_duration'=>($endTime - $startTime)));
        return response()->json([
                    'status' => true,
                    'message' => 'Sucessfully Deleted.'
        ]); 
    }
    
    public function restoreProduct($productId)
    {
        if($productId)
        {
            $productId = $this->roleRepo->decodeData($productId);
            $message = $this->_product->restoreProduct($productId);
            return response()->json([
                        'status' => true,
                        'message' => $message
            ]); 
        }else{
            return response()->json([
                    'status' => false,
                    'message' => 'No product id.'
            ]); 
        }
    }

    public function deleteCategory($category_id)
    {
        DB::Table('categories')->where('category_id', '=', $category_id)->orWhere('parent_id','=',$category_id)->delete();
        return response()->json([
                    'status' => true,
                    'message' => 'Sucessfully Deleted.'
        ]);
    }

    public function getTreeCategories()
    {
        if($this->_manufacturerId == 0)
        {
            $categ = DB::Table('categories')
                //->join('customer_categories', 'customer_categories.category_id', '=', 'categories.category_id')
                ->select('categories.name', 'categories.category_id', 'categories.parent_id')
                //->where('categories.parent_id', 0)
                ->get()->toArray();
        }else{
            $categ = DB::Table('categories')
                ->join('customer_categories', 'customer_categories.category_id', '=', 'categories.category_id')
                ->select('categories.name', 'categories.category_id', 'categories.parent_id')
                ->where('customer_categories.customer_id', $this->_manufacturerId)
                ->get()->toArray();
        }

        // code for parent columns
        $finalcategoryparent = array();
        $categoryparent = array();
        $category = json_decode(json_encode($categ), true);
        //return $cat->id;
        $categoryArray = array();
        if(!empty($category))
        {
            foreach ($category as $categoryData)
            {
                if(isset($categoryData['category_id']) && $categoryData['category_id'] != '')
                {
                    $categoryArray[] = $categoryData['category_id'];
                }            
            }
        }
        foreach ($category as $catparent)
        {
            # code for child columns...            
            if(!empty($categoryArray))
            {
                $categchild = DB::Table('categories')
                    ->select('categories.parent_id', 'categories.category_id', 'categories.name', 'categories.status', 'categories.top', 'categories.column', 'categories.sort_order', 'categories.parent_id')
                    ->where('categories.parent_id', $catparent['category_id'])
                    ->whereIn('categories.category_id', $categoryArray)
                    ->get()->toArray();
            }else{
                $categchild = DB::Table('categories')
                    ->select('categories.parent_id', 'categories.category_id', 'categories.name', 'categories.status', 'categories.top', 'categories.column', 'categories.sort_order', 'categories.parent_id')
                    ->where('categories.parent_id', $catparent['category_id'])
                    ->get()->toArray();
            }
            $categoryparent = array();
            $allowAddCategory = $this->roleRepo->checkPermissionByFeatureCode('CAT002');
            $allowEditCategory = $this->roleRepo->checkPermissionByFeatureCode('CAT003');
            $allowDeleteCategory = $this->roleRepo->checkPermissionByFeatureCode('CAT004');
            if(!empty($categchild))
            {
                $finalcategorychild = array();
                $categorychild = array();
                $categorychildencode = json_decode(json_encode($categchild), true);                
                foreach ($categorychildencode as $catchild)
                {                    
                    if(!empty($categoryArray))
                    {
                        $getprodclass = DB::Table('categories')
                                ->SELECT('categories.name', 'categories.category_id')
                                ->whereIn('categories.category_id', $categoryArray)
                                ->where('categories.parent_id', $catchild['category_id'])
                                ->get()->toArray();
                    }else{
                        $getprodclass = DB::Table('categories')
                                ->SELECT('categories.name', 'categories.category_id')
                                ->where('categories.parent_id', $catchild['category_id'])
                                ->get()->toArray();
                    }
                    $finalProdClassArr = array();
                    $prod = array();
                    $prodclass_details = json_decode(json_encode($getprodclass), true);
                    foreach ($prodclass_details as $values)
                    {
                        $actions = '';
                        $prod['id'] = $values['category_id'];
                        $prod['pname'] = $values['name'];
                        $prod['actions'] = '';
                        if($allowEditCategory)
                        {
                            $actions = $actions . '<span style="padding-left:5px;"><a data-href="/product/editcategory/' . $values['category_id'] . '" data-toggle="modal" data-target="#editCategory" ><span class="badge bg-light-blue"><i class="fa fa-pencil"></i></span></a></span>';
                        }
                        if($allowDeleteCategory)
                        {
                            $actions = $actions . '<span style="padding-left:5px;"><a onclick = "deleteEntityType(' . $values['category_id'] . ')"><span class="badge bg-red"><i class="fa fa-trash-o"></i></span></a></span>';
                        }
                        $prod['actions'] = $actions;
                        $finalProdClassArr[] = $prod;
                    }
                    $parentActions = '';
                    $categorychild['id'] = $catchild['category_id'];
                    $categorychild['pname'] = $catchild['name'];
                    $categorychild['stat'] = ($catchild['status'] == 1) ? 'Active' : 'In-Active';
                    $categorychild['tp'] = $catchild['top'];
                    $categorychild['colm'] = $catchild['column'];
                    $categorychild['so'] = $catchild['sort_order'];
                    $categorychild['actions'] = '';
                    if($allowAddCategory)
                    {
                        $parentActions = $parentActions . '<span style="padding-left:5px;"><a data-toggle="modal" onclick="getcategoriesName(this);" data-target="#addCategory"><span class="badge bg-green"><i class="fa fa-plus"></i></span></a></span>';
                    }
                    if($allowEditCategory)
                    {
                        $parentActions = $parentActions . '<span style="padding-left:5px;"><a data-href="/product/editcategory/' . $catchild['category_id'] . '"  data-target="#editCategory" data-toggle="modal" ><span class="badge bg-light-blue"><i class="fa fa-pencil"></i></span></a></span>';
                    }
                    if($allowDeleteCategory)
                    {
                        $parentActions = $parentActions . '<span style="padding-left:5px;"><a onclick = "deleteEntityType(' . $catchild['category_id'] . ')" ><span class="badge bg-red"><i class="fa fa-trash-o"></i></span></a></span>';
                    }
                    $categorychild['actions'] = $parentActions;
                    $categorychild['children'] = $finalProdClassArr;
                    $finalcategorychild[] = $categorychild;
                }

                $categoryparent['pname'] = $catparent['name'];
                $categoryparent['id'] = $catparent['category_id'];
                $categoryParentData = '';
                if($allowAddCategory)
                {
                    $categoryParentData = '<span style="padding-left:5px;"><a data-toggle="modal" onclick="getcategoriesName(this);" data-target="#addCategory"><span class="badge bg-green"><i class="fa fa-plus"></i></span></a></span>';
                }
                $categoryparent['actions'] = $categoryParentData;
                $categoryparent['children'] = $finalcategorychild;
                $finalcategoryparent[] = $categoryparent;
            }else{
                if(isset($catparent['parent_id']) && $catparent['parent_id'] == 0)
                {
                    $categoryparent['pname'] = $catparent['name'];
                    $categoryparent['id'] = $catparent['category_id'];
                    $categoryParentData = '';
                    if($allowAddCategory)
                    {
                        $categoryParentData = '<span style="padding-left:5px;"><a data-toggle="modal" onclick="getcategoriesName(this);" data-target="#addCategory"><span class="badge bg-green"><i class="fa fa-plus"></i></span></a></span>';
                    }
                    $categoryparent['actions'] = $categoryParentData;
                    $finalcategoryparent[] = $categoryparent;
                }                
            }
        }
        return json_encode($finalcategoryparent);
    }
    
    public function getCategoryList()
    {
        return $this->_product->getCategoryList(0);        
    }

    public function getCategories()
    {   
        $allowEditCategory = $this->roleRepo->checkPermissionByFeatureCode('CAT003');
        $allowDeleteCategory = $this->roleRepo->checkPermissionByFeatureCode('CAT004');
        $cusers = DB::Table('categories')->get();
        $custArr = array();
        $finalCustArr = array();
        $customer_details = json_decode(json_encode($cusers), true);
        foreach ($customer_details as $value)
        {
            $custArr['name'] = $value['name'];
            //$custArr['parent_id'] = $this->getParentName($value['parent_id']);
            $custArr['parent_id'] = $value['parent_id'];
            $custArr['status'] = ($value['status'] == 1) ? 'Active' : 'In-Active';
            $custArr['top'] = $value['top'];
            $custArr['column'] = $value['column'];
            $custArr['sort_order'] = $value['sort_order'];
            $custArr['actions'] = '';
            if($allowEditCategory)
            {
                $custArr['actions'] = $custArr['actions'] . '<a data-href="product/editcategory/' . $value['category_id'] . '" data-toggle="modal" data-target="#basicvalCodeModal1" ><span class="badge bg-light-blue"><i class="fa fa-pencil"></i></span></a>';
            }
            if($allowDeleteCategory)
            {
                $custArr['actions'] = $custArr['actions'] . '<a onclick = "deleteEntityType(' . $value['category_id'] . ')" ><span class="badge bg-red"><i class="fa fa-trash-o"></i></span></a>';
            }
            $finalCustArr[] = $custArr;
        }
        return json_encode($finalCustArr);
    }

    public function viewCategory()
    {
        $allowAddCategory = $this->roleRepo->checkPermissionByFeatureCode('CAT002');
        $allowAddCustomerCategory = $this->roleRepo->checkPermissionByFeatureCode('CAT008');
        parent::Breadcrumbs(array('Home' => '/', 'Categories' => '#'));
        $allowedButtons['add_new_parent_category'] = $allowAddCategory;
        $allowedButtons['add_category'] = $allowAddCustomerCategory;
        $manufacturerList = $this->_product->getManufacturers($this->_manufacturerId);
        $categoryList = $this->getCategoryList();
        return View::make('products.category')->with(array('categoryList' => $categoryList, 'manufacturerList' => $manufacturerList, 'allowed_buttons' => $allowedButtons));
    }

    public function getcat()
    {
        $cuser = DB::Table('categories')->get();
        $cusArr = array();
        $finalCusArr = array();
        $customer_detail = json_decode(json_encode($cuser), true);
        foreach ($customer_detail as $val)
        {
            $cusArr['name'] = $val['name'];
            $cusArr['parent_id'] = $this->getParentName($val['parent_id']);
            $cusArr['status'] = ($val['status'] == 1) ? 'Active' : 'In-Active';
            $cusArr['top'] = $val['top'];
            $cusArr['column'] = $val['column'];
            $cusArr['sort_order'] = $val['sort_order'];

            $custArr['actions'] = '<a data-href="product/editcategory/' . $value['category_id'] . '" data-toggle="modal" data-target="#basicvalCodeModal1" ><span class="badge bg-light-blue"><i class="fa fa-pencil"></i></span></a>

                                    <a onclick = "deleteEntityType(' . $value['category_id'] . ')" ><span class="badge bg-red"><i class="fa fa-trash-o"></i></span></a>';

            $finalCusArr[] = $cusArr;
        }

        return json_encode($finalCusArr);
    }

    public function uploadHandler()
    {
        $upload_handler = new Products\UploadHandler();
        $s3 = new s3\S3();
        foreach ($upload_handler->response['files'] as $files => $file){
            //$upload_handler->response['files'][$files]->url_ser=$file->url;
            $s3file=$s3->uploadFile($file->url,'product');
            $upload_handler->response['files'][$files]->url=$s3file;
            $s3file=str_replace('products/','',$s3file);
            //$upload_handler->response['files'][$files]->thumbnailUrl_ser=$file->thumbnailUrl;
            $upload_handler->response['files'][$files]->thumbnailUrl=$s3->uploadFile($file->thumbnailUrl,'productThumbnail',$s3file);
            @unlink($file->url);
            @unlink($file->thumbnailUrl);
        }
        echo json_encode(array("files"=>$upload_handler->response['files'])); exit;
    }

    /* Categories actions ends */

    /* Attributes actions */

    public function saveAttribute()
    {   
        
        $attributeObj = new Products\ProductAttributes();
        return $attributeObj->saveAttribute(Input::all());
       
    }

    public function editAttribute($attribute_id,$attribute_set_id)
    {
//        $editattribute =DB::Table('attribute_mapping')
//                    ->join('attributes_groups','attributes_groups.attribute_group_id','=','attribute_mapping.attr_map_id')
//                    ->join('attributes','attributes.attribute_id','=','attribute_mapping.attr_id')
//                    ->select('attributes.*','attributes_groups.attribute_group_id')
//                    ->where('attribute_id', $attribute_id)->first();
        $startTime = $this->getTime();
        $attribute_id=$this->roleRepo->decodeData($attribute_id);
        //$attribute_set_id=$this->roleRepo->decodeData($attribute_set_id);
        //return $attribute_set_id;
        $editattribute = DB::Table('attributes')
                ->join('attributes_groups', 'attributes_groups.attribute_group_id', '=', 'attributes.attribute_group_id')
                ->join('attribute_set_mapping', 'attribute_set_mapping.attribute_id', '=', 'attributes.attribute_id')
                ->select('attributes.*', 'attribute_set_mapping.attribute_set_id', 'attributes_groups.name as attribute_group_name', 'attributes_groups.attribute_group_id')
                ->where('attributes.attribute_id', $attribute_id)
                ->where('attribute_set_mapping.attribute_set_id',$attribute_set_id)
                ->first();
                $endTime = $this->getTime();
                DB::table('user_tracks')->insert(array('user_id'=>Session::get('userId'),'manufacturer_id'=>Session::get('customerId'),'message'=>'Successfully Retrieved.','service_name'=>'Edit Attribute','status'=>1,'response_duration'=>($endTime - $startTime)));
        return response()->json($editattribute);
        
    }

    public function updateAttribute($attribute_id)
    {
        $data=Input::all();
                        //validator
                     $validator = \Validator::make(
                                    array(
                                'name' => isset($data['name']) ? $data['name'] : '',
                                'attribute_set_id' => isset($data['attribute_set_id']) ? $data['attribute_set_id'] : '',
                                'attribute_code'=>isset($data['attribute_code']) ? $data['attribute_code'] : '',
                                'attribute_group_id' => isset($data['attribute_group_id']) ? $data['attribute_group_id'] : '',
                                'attribute_type' => isset($data['attribute_type']) ? $data['attribute_type'] : ''
                                    ), array(
                                'name' => 'required',
                                'attribute_set_id' => 'required',
                                'attribute_code' => 'required',
                                'attribute_group_id' => 'required',
                                'attribute_type' => 'required'
                                    ));
                    if($validator->fails())
                    {
                        $errorMessages = json_decode($validator->messages());
                        $errorMessage = '';
                        if(!empty($errorMessages))
                        {
                            foreach($errorMessages as $field => $message)
                            {
                                $errorMessage = implode(',', $message);
                            }
                        }
                        return response()->json([
                                'status' => false,
                                'message' => $errorMessage
                    ]);
                    }
                //validator
        $attributeCode= $data['attribute_code'];
        $checkDefaultAttribute=$this->_productattr->checkForDefaultAttribute($data['name']);
        $checkAttributeName=$this->_productattr->checkForAttributes($attributeCode);
        //return $checkAttributeName;
        if($checkDefaultAttribute || ($checkAttributeName && $checkAttributeName[0]->attribute_id!=$attribute_id )){
            return response()->json([
                    'status' => false,
                    'message' => 'Attribute already exists with this name.']);
        }
        $startTime = $this->getTime();
        if($checkDefaultAttribute==0){
        DB::table('attributes')
                ->where('attribute_id', $attribute_id)
                ->update(array(
                    'name' => Input::get('name'),
                    'text' => Input::get('text'),
                    'input_type' => Input::get('input_type'),
                    'attribute_code'=>$attributeCode,
                    'default_value' => Input::get('default_value'),
                    'attribute_group_id' => Input::get('attribute_group_id'),
                    'is_required' => Input::get('is_required'),
                    'validation' => Input::get('validation'),
                    'regexp' => Input::get('regexp'),
                    'lookup_id' => Input::get('lookup_id'),
                    'attribute_type' => Input::get('attribute_type')));
                $endTime = $this->getTime();
                DB::table('user_tracks')->insert(array('user_id'=>Session::get('userId'),'manufacturer_id'=>Session::get('customerId'),'message'=>'Successfully Updated.','service_name'=>'Update attribute','status'=>1,'response_duration'=>($endTime - $startTime)));
        return response()->json([
                    'status' => true,
                    'message' => 'Sucessfully updated.'
        ]);
       }
       
    }

    public function deleteAttribute($attribute_id)
    {
        $startTime = $this->getTime();
        DB::table('attributes')->where('attribute_id', '=', $attribute_id)->delete();
        $endTime = $this->getTime();
                DB::table('user_tracks')->insert(array('user_id'=>Session::get('userId'),'manufacturer_id'=>Session::get('customerId'),'message'=>'Successfully Deleted.','service_name'=>'Delete Attribute','status'=>1,'response_duration'=>($endTime - $startTime)));
        return Redirect::to('attribute');

    }

    public function saveAttributeGroup()
    {   
        $attributeObj = new Products\ProductAttributes();
        return $attributeObj->saveAttributeGroup(Input::all());
    }

    public function saveAttributeSet()
    {
        $attributeObj = new Products\ProductAttributes();
        return $attributeObj->saveAttributeSet(Input::all());
    }

    public function editAttributeGroup($attribute_group_id)
    {
        $startTime = $this->getTime();
        $editAttributeGroup = DB::Table('attributes_groups')->where('attribute_group_id', $attribute_group_id)->first();
         $endTime = $this->getTime();
                DB::table('user_tracks')->insert(array('user_id'=>Session::get('userId'),'manufacturer_id'=>Session::get('customerId'),'message'=>'Successfully Retrieved.','service_name'=>'Edit attributeGroup','status'=>1,'response_duration'=>($endTime - $startTime)));
        return response()->json($editAttributeGroup);
    }

    public function editAttributeSet($attribute_set_id)
    {   
        $startTime = $this->getTime();
        $attribute_set_id=$this->roleRepo->decodeData($attribute_set_id);
        $editAttributeSet = DB::Table('attribute_sets')->where('attribute_set_id', $attribute_set_id)->first();
         $endTime = $this->getTime();
                DB::table('user_tracks')->insert(array('user_id'=>Session::get('userId'),'manufacturer_id'=>Session::get('customerId'),'message'=>'Successfully Retrieved.','service_name'=>'Edit Attributesets','status'=>1,'response_duration'=>($endTime - $startTime)));
        return response()->json($editAttributeSet);
       
    }

    public function updateAttributeGroup($attribute_group_id)
    {
        $startTime = $this->getTime();
        DB::table('attributes_groups')
                ->where('attribute_group_id', $attribute_group_id)
                ->update(array(
                    'name' => Input::get('name'),
                    'category_id' => Input::get('category_id'),
                    'manufacturer_id' => Input::get('customer_id'),
        ));
                 $endTime = $this->getTime();
                DB::table('user_tracks')->insert(array('user_id'=>Session::get('userId'),'manufacturer_id'=>Session::get('customerId'),'message'=>'Successfully Updated.','service_name'=>'Update AttributeGroup','status'=>1,'response_duration'=>($endTime - $startTime)));
        return response()->json([
                    'status' => true,
                    'message' => 'Sucessfully updated.'
        ]);
    }

    public function deleteAttributeGroup($attribute_group_id = null)
    {
         $startTime = $this->getTime();
        if(!$attribute_group_id)
        {
            $attribute_group_id = Input::get('attribute_group_id');
        }
        DB::table('attributes_groups')->where('attribute_group_id', '=', $attribute_group_id)->delete();
        $endTime = $this->getTime();
                DB::table('user_tracks')->insert(array('user_id'=>Session::get('userId'),'manufacturer_id'=>Session::get('customerId'),'message'=>'Successfully Deleted.'.$attribute_group_id,'service_name'=>'Delete Attributegroup','status'=>1,'response_duration'=>($endTime - $startTime)));
        //return Redirect::to('attribute');
        return response()->json([
            'status' => true,
            'message' => 'Sucessfully deleted.'
        ]);
    }

    public function deleteAttributeSet()
    {
        try
        {   $startTime = $this->getTime();
            $data=Input::all();
            //return $data;
            $attribute_set_id = $this->roleRepo->decodeData($data['attribute_set_id']);
            //$password=Input::get();
            $userId = Session::get('userId');
            $verifiedUser = $this->roleRepo->verifyUser($data['password'], $userId);
            if($verifiedUser >= 1)
            {
                DB::table('attribute_set_mapping')->where('attribute_set_id', '=', $attribute_set_id)->delete();
                DB::table('attribute_sets')->where('attribute_set_id', '=', $attribute_set_id)->delete();
                 $endTime = $this->getTime();
                DB::table('user_tracks')->insert(array('user_id'=>Session::get('userId'),'manufacturer_id'=>Session::get('customerId'),'message'=>'Successfully Deleted.','service_name'=>'Deleted Attributeset.'.$attribute_set_id,'status'=>1,'response_duration'=>($endTime - $startTime)));
           return 1;
           }else{
            return "You have entered incorrect password !!";
           }
        } catch (ErrorException $ex) {
            return response()->json([
                'status' => false,
                'message' => $ex->getMessage()
            ]);
        }        
    }

    public function getAllAttributesExport($manufacturer_id){
        $attributeData = new Products\ProductAttributes();
        $data=(array) json_decode($attributeData->getAllAttributes($manufacturer_id),true);
        $export=array();
        foreach ($data as $key => $parent) {
            foreach ($parent['children'] as $key => $value) {
                $temp=array(
                'Attribute Set Name'=>$parent['attribute_set_name'],
                'Category Name'=>$parent['category_id'],
                'Manufacturer Name'=>$parent['manufacturer_id'],
                'Group Name'=>$value['attribute_group_name'],
                'Attribute Name'=>$value['attribute_name'],
                'Attribute Text'=>$value['text']
                );
                $export[]=$temp;
            }
        }
        ob_end_clean();
        ob_start();
        return Excel::create('exportattributes', function($excel) use ($export) {
            $excel->sheet('mySheet', function($sheet) use ($export)
            {
                $sheet->fromArray($export);
            });
        })->download('xls');
    }
    public function getAllAttributes($manufacturer_id)
    {
        $attributeData = new Products\ProductAttributes();
        return $attributeData->getAllAttributes($manufacturer_id);
    }

    public function delAttributeFromGroup($attribute_id = null, $attribute_set_id = null)
    {   
        $data=Input::all();
        $userId = Session::get('userId');
        $verifiedUser = $this->roleRepo->verifyUser($data['password'], $userId);
        if($verifiedUser >= 1)
        {
            if(!$attribute_id && !$attribute_set_id)
            {
                $attribute_id = $this->roleRepo->decodeData($data['attribute_id']);
                $attribute_set_id = Input::get('attribute_set_id');
                $defaultAttributes=DB::table('attribute_set_mapping')
                                   ->join('attribute_sets','attribute_set_mapping.attribute_set_id','=','attribute_set_mapping.attribute_set_id')
                                   ->where('attribute_set_mapping.attribute_set_id',1)
                                   ->where('attribute_set_mapping.attribute_id',$attribute_id)
                                   ->select('attribute_set_mapping.attribute_set_id','attribute_set_mapping.attribute_id')
                                   ->get();

            //return $defaultAttributes;
                DB::table('attribute_set_mapping')->where(array('attribute_id' => $attribute_id, 'attribute_set_id' => $attribute_set_id))->delete();
                if(!$defaultAttributes)
                {
                    $attrDependency=DB::table('attribute_set_mapping')
                           ->where('attribute_id',$attribute_id)
                           ->where('attribute_set_id','!=',$attribute_set_id)
                           ->get();
                if(!$attrDependency){
                DB::table('attributes')->where('attribute_id', $attribute_id)->delete();
                    }
                }                

            }
        return 1;
       }else{
        return "You have entered incorrect password !!";
       }
    }

    public function getCustomers()
    {
        $custArr = array();
        $finalCustArr = array();
        $customer_details = DB::Table('attributes')->get();
        $cust = json_decode(json_encode($customer_details), true);
        $allowEditAttribute = $this->roleRepo->checkPermissionByFeatureCode('ATT003');
        $allowDeleteAttribute = $this->roleRepo->checkPermissionByFeatureCode('ATT004');
        foreach ($cust as $value)
        {
            if ($value['is_required'] == 1)
            {
                $status1 = 'Yes';
            } else
            {
                $status1 = 'No';
            }

            if ($value['attribute_type'] == 1)
            {
                $status2 = 'Static';
            } elseif ($value['attribute_type'] == 2)
            {
                $status2 = 'Dynamic';
            } else
            {
                $status2 = 'Binding';
            }
            $custArr['attribute_id'] = $value['attribute_id'];
            $custArr['name'] = $value['name'];
            $custArr['text'] = $value['text'];
            $custArr['input_type'] = $value['input_type'];
            $custArr['default_value'] = $value['default_value'];
            $custArr['is_required'] = $status1;
            $custArr['validation'] = $value['validation'];
            $custArr['regexp'] = $value['regexp'];
            $custArr['lookup_id'] = $value['lookup_id'];
            $custArr['attribute_type'] = $status2;
            $custArr['actions'] = '';
            if($allowEditAttribute)
            {
                $custArr['actions'] = $custArr['actions'] . '<span style="padding-left:5px;" ><a data-href="product/editAttribute/' . $value['attribute_id'] . '" data-toggle="modal" data-target="#basicvalCodeModal1"><span class="badge bg-light-blue"><i class="fa fa-pencil"></i></span></a></span>';
            }
            if($allowDeleteAttribute)
            {
                $custArr['actions'] = $custArr['actions'] . '<span style="padding-left:5px;" ><a onclick="deleteEntityType(' . $value['attribute_id'] . ')"><span class="badge bg-red"><i class="fa fa-trash-o"></i></span></a></span><span style="padding-left:50px;" ></span>';
            }
            $finalCustArr[] = $custArr;
        }
        return json_encode($finalCustArr);
    }

    public function attributes()
    { 
        $manufacturerId = $this->_manufacturerId;
        $addAttributesets = $this->roleRepo->checkPermissionByFeatureCode('ATTG002');
        $addAttributegroups = $this->roleRepo->checkPermissionByFeatureCode('ATG001');
        $data = Input::all();
        $postMethod = 0;
        if(!empty($data))
        {
            $manufacturerId = Input::get('manufacturer_id');
            $postMethod = 1;
        }
        //echo 'manufacturerId => '.$manufacturerId;die;
        parent::Breadcrumbs(array('Home' => '/', 'Attributes' => '#'));
        if($manufacturerId)
        {
            $attributeSetData = DB::Table('attribute_sets')
                ->where('manufacturer_id', $manufacturerId)
                ->select('attribute_set_id', 'attribute_set_name')
                ->get();
        }else{
            $attributeSetData = DB::Table('attribute_sets')
                ->select('attribute_set_id', 'attribute_set_name')
                ->get();
        }
        
        if($manufacturerId)
        {
            $ag = DB::Table('attributes_groups')
                ->where('manufacturer_id', $manufacturerId)
                ->orWhere('manufacturer_id', 0)
                ->select('attribute_group_id', 'name')
                ->get();
        }else{
            $ag = DB::Table('attributes_groups')
                ->select('attribute_group_id', 'name')
                ->get();
        }
        
        /*$am = DB::Table('attribute_mapping')
                ->select('id', 'attribute_map_id', 'attribute_id', 'value', 'location_id')
                ->get();*/
        $am = array();        
        $data = DB::Table('attributes')->get();


        if($manufacturerId)
        { 
            $cat = DB::Table('categories')
                ->join('customer_categories', 'customer_categories.category_id', '=', 'categories.category_id')
                ->where('customer_categories.customer_id', $manufacturerId)
                ->select('categories.category_id', 'categories.name')
                ->get();
        }else{
            $cat = DB::Table('categories')
                ->join('customer_categories', 'customer_categories.category_id', '=', 'categories.category_id')
                //->where('customer_categories.customer_id', $this->_manufacturerId)
                ->select('categories.category_id', 'categories.name')
                ->get();
        }
        if($manufacturerId)
        {        
            $manu = DB::Table('eseal_customer')
                    ->where('customer_id', $manufacturerId)
                    ->orWhere('parent_company_id',$manufacturerId)
                ->select('customer_id', 'brand_name')
                ->get();
        }else{
            $manu = DB::Table('eseal_customer')
                ->select('customer_id', 'brand_name')
                ->get();
        }
        $attrsets = array();
                //Attributes Data
        if($manufacturerId)
        {   
           /* $default= DB::Table('attribute_sets')
                    ->join('attribute_set_mapping', 'attribute_set_mapping.attribute_set_id', '=', 'attribute_sets.attribute_set_id')
                    ->join('attributes','attributes.attribute_id', '=', 'attribute_set_mapping.attribute_id')
                    ->where('attribute_sets.manufacturer_id', 0)
                    ->select('attributes.attribute_id', 'attributes.name')
                    ->get();    
            $attrsets = DB::Table('attribute_sets')
                    ->join('attribute_set_mapping', 'attribute_set_mapping.attribute_set_id', '=', 'attribute_sets.attribute_set_id')
                    ->join('attributes','attributes.attribute_id', '=', 'attribute_set_mapping.attribute_id')
                    ->where('attribute_sets.manufacturer_id', $this->_manufacturerId)
                    ->whereNotIn('attributes.attribute_id',function($query){
                        $query=DB::Table('attribute_sets')
                    ->join('attribute_set_mapping', 'attribute_set_mapping.attribute_set_id', '=', 'attribute_sets.attribute_set_id')
                    ->join('attributes','attributes.attribute_id', '=', 'attribute_set_mapping.attribute_id')
                    ->where('attribute_sets.manufacturer_id', 0)
                    ->select('attributes.attribute_id')
                    ->get();
                    })
                ->select('attributes.attribute_id', 'attributes.name')
                ->union($default)->get();*/
                $attrsets=DB::select(DB::raw("select c.attribute_id ,a.manufacturer_id,c.name from attribute_sets a,attribute_set_mapping b,attributes c 
                                                    where 1=1
                                                    and a.manufacturer_id = 0 
                                                    and a.attribute_set_id=b.attribute_set_id
                                                    and c.attribute_id=b.attribute_id
                                                    union
                                                    (select c.attribute_id,a.manufacturer_id,c.name from attribute_sets a,attribute_set_mapping b,attributes c 
                                                    where 1=1
                                                    and c.attribute_id not in (
                                                    select c.attribute_id from attribute_sets a,attribute_set_mapping b,attributes c 
                                                    where 1=1
                                                    and a.manufacturer_id = 0 
                                                    and a.attribute_set_id=b.attribute_set_id
                                                    and c.attribute_id=b.attribute_id
                                                    )
                                                    and a.attribute_set_id=b.attribute_set_id
                                                    and c.attribute_id=b.attribute_id
                                                    and a.manufacturer_id = $manufacturerId)"));
                //return  $attrsets;
        }
        //Attributes Data
        $userType = Session::get('userId');
        $custType=DB::table('users')
                ->join('eseal_customer','users.customer_id','=','eseal_customer.customer_id')
                ->join('master_lookup','master_lookup.value','=','eseal_customer.customer_type_id')
                ->where('users.customer_type','!=',7001)
                ->where('user_id',$userType)
                ->select('eseal_customer.customer_type_id')
                ->get();
        /* $custType=json_encode($custType);*/
        $manufactuerArray = array();
        if(!empty($manu))
        {
            foreach($manu as $manufacturer)
            {
                $manufactuerArray[$manufacturer->customer_id] = $manufacturer->brand_name;
            }
        }
        
        if($postMethod)
        {
            return json_encode($attrsets);
        }else{
    return View::make('products.attribute')
                        ->with('attributeSetData', $attributeSetData)
                        ->with('data', $data)
                        ->with('am', $am)
                        ->with('ag', $ag)
                        ->with('cat', $cat)
                        ->with('manu', $manu)
                        ->with('attrsets',$attrsets)
                        ->with('manufacturerData', $manufactuerArray)
                        ->with('addAttributesets',$addAttributesets)
                        ->with('addAttributegroups',$addAttributegroups)
                        ->with('custType',$custType);    
        }
        
    }

    public function exportXls(){
        $data = Input::all();
        $finalCustArr = $this->_product->getAllProductsExport($data);
    
        foreach ($finalCustArr as $key => $value) {
           unset($value['image']);
           unset($value['actions']);
           unset($value['sku']);
           unset($value['is_deleted']);
           $finalCustArr[$key]=$value;
        }
        ob_end_clean(); 
        ob_start();
        return Excel::create('products_export_to_excel', function($excel) use ($finalCustArr) {
            $excel->sheet('mySheet', function($sheet) use ($finalCustArr)
            {
                $sheet->fromArray($finalCustArr);
            });
        })->download('xls');

        
    }

    /* Attributes actions ends */    
    public function saveProductsFromExcel()
    {
        $data = Input::all();
        $startTime = $this->getTime();
        // $manufacturerId = isset($data['manufacturerID']) ? $data['manufacturerID'] : 0; 
        $manufacturerId = $this->_manufacturerId; 
        $filePath = isset($_FILES['files']['tmp_name']) ? $_FILES['files']['tmp_name'] : '';
        $fileName = isset($_FILES['files']['name']) ? $_FILES['files']['name'] : '';
        $extension = '';
        if($fileName != '')
        {
            $extension = pathinfo($_FILES['files']['name'], PATHINFO_EXTENSION);
        }
        $allowed_Extensions = ['XLS','XLSX'];

        if( !in_array(strtoupper($extension), $allowed_Extensions))
        
        {
            return 'Please upload an Excel file with .xls or .xlsx extension.';
        }
        $errorMessage = '';
        if ($filePath != '')
        {
            if (!$fh = fopen($filePath, 'r'))
            {
                throw new Exception("Could not open $filePath for reading.");
            }

            if ($manufacturerId)
            {
                $productDetails = DB::Table('location_types')->where('manufacturer_id', $manufacturerId)->get();
            }
            
            $i = 0;
            $buffer = array();
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
            //     $tempArray[] = $fields;
            // }
            $path = Input::file('files')->getRealPath();

        $tempArray = Excel::load($path, function($reader) {})->toArray();

            $count = 0;            
            $productHeaders = array('name', 'product_type_id', 'business_unit_id', 'category_id', 'model_name', 'description', 'currency_class_id', 'mrp','ean', 'material_code','uom_class_id', 'is_gds_enabled', 'Level0', 'Level1', 'Level2', 'is_serializable', 'is_batch_enabled', 'is_backflush', 'inspection_enabled', 'location_erp_code', 'fg_storage_location', 'group_id', 'field1', 'field2', 'field3', 'field4', 'field5','is_traceable', 'manufacturer_id', 'created_from');

            $headers =["product_name","product_type","business_units","product_category","product_model_name","product_description","currency_type","mrp","erp_code","ean","uom_class","is_gds_enabled","level_0_capacity","level_1_capacity","level_2_capacity","is_serializable","is_batch_enabled","is_backflush","inspection_enabled","plant_code","fg_storage_location","group_id","field1","field2","field3","field4","field5","is_traceable"];


            $headermap = ['name'=>'product_name','product_type_id'=>'product_type','business_unit_id'=>'business_units','category_id'=>'product_category','model_name'=>'product_model_name','description'=>'product_description','currency_class_id'=>'currency_type','mrp'=>'mrp','material_code'=>'erp_code','ean'=>'ean','uom_class_id'=>'uom_class','is_gds_enabled'=>'is_gds_enabled','Level0'=>'level_0_capacity','Level1'=>'level_1_capacity','Level2'=>'level_2_capacity','is_serializable'=>'is_serializable','is_batch_enabled'=>'is_batch_enabled','is_backflush'=>'is_backflush','inspection_enabled'=>'inspection_enabled','location_erp_code'=>'plant_code','fg_storage_location'=>'fg_storage_location','group_id'=>'group_id','field1'=>'field1','field2'=>'field2','field3'=>'field3','field4'=>'field4','field5'=>'field5','is_traceable'=>'is_traceable','manufacturer_id'=>'manufacturer_id','created_from'=>'created_from'];
            $excelheaders = array_keys($tempArray[0]);
            //dd($excelheaders);die;
            //dd(count(array_diff($headers, $excelheaders)));die;
            if(count(array_diff($headers, $excelheaders)) >0){
                return "Some Headers are missing Please Check.";
            }
            $productHeadersTrim = array_map('trim', $productHeaders);
            //print_r($productHeadersTrim);            
            $insertProductData = array();            
            $tempStoredProducts = array();
            $j = 1;
            $countrows = 0;
            $material_code_already_added = [];
            // if(isset($tempArray[0]) && in_array('Product Name', $tempArray[0]))
            // {
            //     unset($tempArray[0]);
            // }
            // if(isset($tempArray[count($tempArray)]) && count($tempArray[count($tempArray)]) < 2)
            // {
            //     unset($tempArray[count($tempArray)]);
            // }
            $i=1;
            foreach ($tempArray as $locations)
            {
                $i++;
                $productDetails = array();
                $productDetails = $locations;//array_map('trim', $locations);
                if(!empty($productDetails))
                {
                    $productDetails['manufacturer_id'] = $manufacturerId;
                    $productDetails['created_from'] = 'Import from CSV';
                }                
                //echo "<pre>";print_r($productDetails);die;
//                echo "<pre>";print_r($productHeadersTrim);
                if (!empty($productDetails) && !empty($productHeadersTrim))
                {
                    if(count($productDetails) == count($productHeadersTrim))
                    {

                        $productName = isset($productDetails['product_name']) ? $productDetails['product_name'] : '';
  //                      $a =  [$productDetails['erp_code'],trim($productDetails['erp_code'])];
    //                    dd($a);
                        $productErpCode = isset($productDetails['erp_code']) ? trim($productDetails['erp_code'] ): '';

                        $productTypeName = isset($productDetails['product_type'])? $productDetails['product_type']:'';
                        if ($productName != '')
                        {
                            if(!empty($productErpCode)){

                                if(!in_array($productErpCode,$material_code_already_added)){
                            // $response = $this->checkProductName($productName, $manufacturerId, $productErpCode);
                            $response = $this->checkMaterialCode($productName, $manufacturerId, $productErpCode);
                            
                // echo "<pre>";
                // print_r($response);
                // exit;
                            $material_code_already_added[]= $productErpCode; 
                            $category = DB::table('categories')->where('name',$productDetails['product_category'])->select('category_id')->get();

                            $uom_class = DB::table('uom_classes')->where('uom_name',$productDetails['uom_class'])->value('id');
                            // echo "hai";
                              //echo($category);exit;
                            $productTypeId = DB::table('master_lookup')
                        ->leftJoin('lookup_categories', 'lookup_categories.id', '=', 'master_lookup.category_id')
                        ->where('lookup_categories.name', 'Product Types')
                        ->where('master_lookup.name', $productTypeName)
                        ->value('master_lookup.value');
                            //dd($uom_class);
                        // print_r($productTypeId);exit;
                            if($response == 1 )
                            {
                                if(!empty($category)){
                                    if($productTypeId){

                                    
                                    if($uom_class){
                                        //$locationstatus = true;
                                        $locations_not_matched = [];
                                        //dd($productDetails['plant_code']);
                                        if(!empty($productDetails['plant_code'])){
                                            $plant_locations = explode(',',$productDetails['plant_code']);
                                            $location_erp_codes = DB::table('locations')->whereIn('erp_code',$plant_locations)->pluck('erp_code')->all();
                                            $locations_not_matched = array_diff($plant_locations,$location_erp_codes);

                                        }
                                        if(count($locations_not_matched)==0){
                                            $tempStoredProducts = [];
                                            foreach($productHeadersTrim as $key){
                                                
                                                    $tempStoredProducts[$key] = $productDetails[$headermap[$key]]; 
                                                
                                            }
                                            //unset($tempStoredProducts['location_erp_code']);
                                            //$tempStoredProducts = array_combine($productHeadersTrim, array_map('trim', array_values($productDetails)));
                                            // print_r($tempStoredProducts);exit;
                                        //dd($tempStoredProducts);
                                $tempStoredProducts = $this->updateFieldValues($tempStoredProducts, $manufacturerId);
                                $tempStoredProducts['date_added']= Date('Y-m-d H:i:s');
                                $insertProductData[] = $tempStoredProducts;   //print_r($tempStoredProducts);exit;            
                                        }
                                        else{
                                            $errorMessage = $errorMessage." At Line No ".$i.": Plant Codes ".implode(',',$locations_not_matched)." does not Exists \n";
                                        }
                                        
                                    }
                                    else{
                                        $errorMessage = $errorMessage ."At Line No ".$i.": UOM ".$productDetails['uom_class']." is not   valid\n";
                                    }
                                }
                                else{
                                    $errorMessage = $errorMessage . "At Line No ".$i.": Product Type does not exists \n";
                                }
                                
                                }
                                else{
                                    $errorMessage = $errorMessage ."At Line No ".$i.": Category ".$productDetails['product_category']." is not available \n";   
                                }
                            }else{
                                $errorMessage = $errorMessage ."At Line No ".$i.":". $response. " \n";
                            }
                            }else{
                                $errorMessage = $errorMessage . "At Line No ".$i.": Material Code is already given ";
                            }
                            }
                            else{
                                $errorMessage = $errorMessage ."At Line No ".$i.":"."No ERP Code given". "\n";

                            }
                        }else{
                            $errorMessage = $errorMessage . "At Line No ".$i.":"."  No Product name  \n";
                        }
                    }else{
                        $errorMessage = $errorMessage . '  Field count exceds the header count, please enter given number of fields.  ';
                    }
                }else{
                    //$errorMessage = $errorMessage . '  No Data  ';
                }
            }
//            echo $errorMessage;
//            echo "insertLocationData => <pre>";print_r($insertProductData);die;
            if (!empty($insertProductData))
            {
                foreach($insertProductData as $productData)
                {
                    if(isset($productData['material_code']))
                    {
                        $productData['material_code'] = str_replace('"', '', $productData['material_code']);
                        $productData['material_code'] = str_replace("'", '', $productData['material_code']);
                        $productData['material_code'] = trim($productData['material_code']);
                    }
                    $levelArray = ['Level0' => 0, 'Level1' => 0, 'Level2' => 0];
                    $newProductArray = array_diff_key($productData, $levelArray);
                    $newProductLevelArray = array_intersect_key($productData, $levelArray);
                    
                    $locationErpCode = 0;
                    if(isset($newProductArray['location_erp_code']))
                    {

                        $locationErpCode = $newProductArray['location_erp_code'];
                        //dd($locationErpCode);
                        
                    }
                    unset($newProductArray['location_erp_code']);
                    $latestInsertId = DB::table('products')->insertGetId($newProductArray);
                    if(strlen($locationErpCode) != 0 )
                    {
                        $insertData['product_id'] = $latestInsertId;
            $locationErpCodeArray = explode(',', $locationErpCode); 
                        foreach($locationErpCodeArray as $locationErpCodeId)
                        {
                             $esealLocationId = DB::table('locations')->where(array('erp_code' => $locationErpCodeId, 'manufacturer_id' => $manufacturerId))->value('location_id');
                             //echo $esealLocationId;exit;
                             if(!empty($esealLocationId))
                            {

                                    //$insertLocationData['location_name'] = $locationErpCodeId;
                                //$insertLocationData['erp_code'] = $locationErpCodeId;
                                //$esealLocationId = DB::table('locations')->insertGetId($insertLocationData);
                                $insertData['location_id'] = $esealLocationId;
                            DB::table('product_locations')->insert($insertData);
                             //echo $manufacturerId;exit;
                            $child_locations = DB::table('locations')->where('parent_location_id',$esealLocationId)->where('manufacturer_id',$manufacturerId)->pluck('location_id')->all();
                            foreach($child_locations as $loc){
                                DB::table('product_locations')->insert(['product_id'=>$latestInsertId,'location_id'=>$loc]);
                            }

                            }
                            
                        }
                    }
                    
                    $updateData['sku'] = 'sku-'.$latestInsertId;
                    DB::table('products')->where('product_id', $latestInsertId)->update($updateData);                    
                    foreach($newProductLevelArray as $levelData => $value)
                    {
                        $levelInsertArray = array();
                        if($value != '' && $value !=0)
                        {
                            $levelId = DB::table('master_lookup')->where('name', $levelData)->pluck('value');
                            $levelInsertArray['level'] = $levelId;
                            $levelInsertArray['quantity'] = $value;
                            $levelInsertArray['product_id'] = $latestInsertId;
                            DB::table('product_packages')->insert($levelInsertArray);
                        }                        
                    }
                }
                echo "Successfully " . count($insertProductData) . " products created.  ";
                echo "Failed to create " . (count($tempArray) - count($insertProductData)) . " products for the below reasons.  ";

                echo $errorMessage;
                if(!empty($error_messages)){
                Excel::create('ProductBulkUpdateErrorLog'.$time, function($excel) use($error_messages) {
                    return $excel->sheet('New sheet', function($sheet) use($error_messages){
                    $sheet->loadView('products.productsbulkimporterror',array('error_messages'=>$error_messages));
                    });
                })->store('xlsx', public_path()."/download");//"C://Users/300137/Desktop");
                $link = "error_log_link/ProductBulkUpdateErrorLog".$time.".xlsx";

            }
            else{

            }
            }else{
                if(count($tempArray) == 0)
                echo "No records found ";
                echo $errorMessage;
            } 
        }
    }
    
    public function saveProductComponentsFromExcel()
    {
        $data = Input::all();
        $manufacturerId = isset($data['manufacturerID']) ? $data['manufacturerID'] : 0; 
        $filePath = isset($_FILES['files']['tmp_name']) ? $_FILES['files']['tmp_name'] : '';
        $fileName = isset($_FILES['files']['name']) ? $_FILES['files']['name'] : '';
        $extension = 'csv';
        if($fileName != '')
        {
            $extension = pathinfo($_FILES['files']['name'], PATHINFO_EXTENSION);
        }
        if(strtoupper($extension) != 'CSV')
        {
            return 'Please provide CSV file.';
        }
        $errorMessage = '';
        if ($filePath != '')
        {
            if (!$fh = fopen($filePath, 'r'))
            {
                throw new Exception("Could not open $filePath for reading.");
            }            
            $i = 0;
            $buffer = array();
            $tempArray = array();
            while (!feof($fh))
            {
                $buffer[] = fgets($fh);
                $i++;
                $fields = array();
                foreach ($buffer as $line)
                {
                    $fields = explode(',', $line);
                }
                $tempArray[] = $fields;
            }
            if(isset($tempArray[0]) && in_array('Product Name', $tempArray[0]))
            {
                unset($tempArray[0]);
            }
            if(isset($tempArray[count($tempArray)]) && count($tempArray[count($tempArray)]) < 2)
            {
                unset($tempArray[count($tempArray)]);
            }

            $count = 0;            
            $productComponentHeaders = array('name', 'material_code', 'component_name', 'component_material_code');            
            $productComponentHeadersTrim = array_map('trim', $productComponentHeaders);
//            echo "<pre>";print_r($productComponentHeadersTrim);            
            $insertProductComponentData = array();            
            $tempStoredProductComponents = array();
            $j = 1;
            $countrows = 0;
        $existingCount = 0;
            $message = array();
            $i=0;
            foreach ($tempArray as $productComponents)
            {
                $i++;
                $parentMaterialCode = '';
                $componentMaterialCode = 0;
                $parentId = 0;
                $componentId = 0;
                if (!empty($productComponents) && !empty($productComponentHeadersTrim))
                {
                    if (count($productComponents) == count($productComponentHeadersTrim))
                    {
DB::enableQueryLog();
                        $tempStoredProductComponents = array_combine($productComponentHeadersTrim, array_map('trim', $productComponents));
                        if (isset($tempStoredProductComponents['material_code']))
                        {
                            $parentMaterialCode = $tempStoredProductComponents['material_code'];
                        }
                        if (isset($tempStoredProductComponents['component_material_code']))
                        {
                            $componentMaterialCode = $tempStoredProductComponents['component_material_code'];
                        }
                        if ($parentMaterialCode != '')
                        {
\Log::info('we are inside if for parent material code');
                            $parentId = $this->checkProductName(0, $manufacturerId, $parentMaterialCode);
                        }
                        if ($componentMaterialCode != 0)
                        {
                            $componentId = $this->checkProductName(0, $manufacturerId, $componentMaterialCode);
                        }
$last = DB::getQueryLog();
\Log::info(end($last));
\Log::info($parentMaterialCode);
\Log::info($parentId);
\Log::info($componentMaterialCode);
\Log::info($componentId);

                        if ($parentId != 0 && $componentId != 0)
                        {
                            $checkExisting = DB::table('product_components')->where(array('product_id' => $parentId, 'component_id' => $componentId))->pluck('id');
                            if (empty($checkExisting))
                            {
                                $insertData['product_id'] = $parentId;
                                $insertData['product_erp_code'] = $parentMaterialCode;
                                $insertData['component_id'] = $componentId;
                                $insertData['component_type_id'] = DB::table('products')->where(array('product_id' => $componentId))->value('product_type_id');
                                $insertData['component_erp_code'] = $componentMaterialCode;
                                DB::table('product_components')->insert($insertData);
                                $countrows++;
                            } else
                            {
                                $existingCount++;
                                $message[] ='Product Material code ' .$parentMaterialCode.' Already inserted at row '.$i;
                            }
                        } else
                        {
                            if ($parentId == 0)
                            {
                                $message[$parentMaterialCode] = 'Product Material code '.$parentMaterialCode.' Does not exists at row '.$i;
                            }
                            if ($componentId == 0)
                            {
                                $message[] = 'Component Material code '.$componentMaterialCode.'  Does not exists at row '.$i;
                            }
                        }
                        $insertProductComponentData[] = $tempStoredProductComponents;
                    }
                }
            }
\Log::info('existingCount');
\Log::info($existingCount);
\Log::info($message);
//            echo "<pre>";print_R($insertProductComponentData);
//            die;
            if($countrows == count($tempArray))
            {
                return response()->json([
                    'status' => true,
                    'sucess_records' => $countrows,
                    'message' => array('Imported sucessfully')
                ]);
            }else{
                return response()->json([
                    'status' => true,
                    'sucess_records' => $countrows,
                    'failed_records' => (count($tempArray) - $countrows),
                    'message' => $message
                ]);
            }
        }
    }
    
    public function bulkUpdateProducts_old()
    {
        $data = Input::all();
        $manufacturerId = isset($data['manufacturerID']) ? $data['manufacturerID'] : 0;
        $tableName = isset($data['table_name']) ? $data['table_name'] : 'products';
        $operation = isset($data['operation']) ? $data['operation'] : 'products';
        $filePath = isset($_FILES['files']['tmp_name']) ? $_FILES['files']['tmp_name'] : '';
        $fileName = isset($_FILES['files']['name']) ? $_FILES['files']['name'] : '';
        $extension = 'csv';
        if ($fileName != '')
        {
            $extension = pathinfo($_FILES['files']['name'], PATHINFO_EXTENSION);
        }
        if (strtoupper($extension) != 'CSV')
        {
            return 'Please provide CSV file.';
        }
        $errorMessage = '';
        if ($filePath != '')
        {
            if (!$fh = fopen($filePath, 'r'))
            {
                throw new Exception("Could not open $filePath for reading.");
            }
            $i = 0;
            $buffer = array();
            $tempArray = array();
            while (!feof($fh))
            {
                $buffer[] = fgets($fh);
                $i++;
                $fields = array();
                foreach ($buffer as $line)
                {
                    $fields = explode(',', $line);
                }
                $tempArray[] = $fields;
            }
            $count = 0;
            $fileHeaders = isset($tempArray[0]) ? $tempArray[0] : array();
            if (!empty($fileHeaders))
            {
                if (isset($tempArray[0]))
                {
                    unset($tempArray[0]);
                }
                $updateData = array();
                $countrows = 0;
                $fileHeadersTrim = array_map('trim', $fileHeaders);
                $message = array();
                if ($tempArray > 0)
                {
                    if (in_array('material_code', $fileHeadersTrim))
                    {
                        foreach ($tempArray as $fileData)
                        {
                            if (count($fileData) > 1)
                            {
                                $updateData = array_combine($fileHeadersTrim, array_map('trim', $fileData));
                                if (isset($updateData['material_code']) && $updateData['material_code'] != '')
                                {
                                    $productId = DB::table('products')->where(array('manufacturer_id' => $manufacturerId, 'material_code' => $updateData['material_code']))->pluck('product_id');
                                    if ($productId != '')
                                    {
                                        switch($tableName)
                                        {
                                            case 'products':
                                                unset($updateData['material_code']);
                                                DB::table($tableName)->where('product_id', $productId)->update($updateData);
                                                $countrows++;
                                                break;
                                            case 'product_locations':
                                                if(isset($updateData['location_erp_code']))
                                                {
                                                    $locationId = DB::table('locations')->where(array('manufacturer_id' => $manufacturerId, 'erp_code' => $updateData['location_erp_code']))->pluck('location_id');
                                                    if($locationId == '')
                                                    {
                                                        $message = 'Location '. $updateData['location_erp_code'] .' does not exists.';
                                                        break;
                                                    }
                                                }
                                                $product_id = DB::table($tableName)->where(array('product_id' => $productId, 'location_id' => $locationId))->pluck('product_id');
                                                if($product_id && 'delete' != $operation)
                                                {
                                                    continue;
                                                }else{
                                                    unset($updateData['location_erp_code']);
                                                    unset($updateData['material_code']);
                                                    if($operation == 'update')
                                                    {
                                                        $updateData['product_id'] = $productId;
                                                        $updateData['location_id'] = $locationId;                                                        
                                                        DB::table($tableName)->insert($updateData);
                                                    }elseif($operation == 'delete'){
                                                        $updateData['product_id'] = $productId;
                                                        $updateData['location_id'] = $locationId;
                                                        $locationData = DB::table($tableName)->where($updateData)->pluck('product_id');
                                                        if($locationData != '')
                                                        {
                                                            DB::table($tableName)->where($updateData)->delete();
                                                        }else{
                                                            $message = " No records for product material code => ".$productId." location Id => ".$locationId." ";
                                                        }
                                                    }
                                                    $countrows++;
                                                }
                                                break;
                                            case 'product_attributesets':
                                                if(isset($updateData['location_erp_code']))
                                                {
                                                    $locationId = DB::table('locations')->where(array('manufacturer_id' => $manufacturerId, 'erp_code' => $updateData['location_erp_code']))->pluck('location_id');
                                                    if($locationId != '')
                                                    {
                                                        unset($updateData['location_erp_code']);
                                                        $updateData['location_id'] = $locationId;
                                                    }else{
                                                        $message = 'Location '. $updateData['location_erp_code'] .' does not exists.';
                                                        break;
                                                    }
                                                }
                                                if(isset($updateData['attribute_set_id']))
                                                {
                                                    $attributeSetId = DB::table('attribute_sets')->where(array('manufacturer_id' => $manufacturerId, 'attribute_set_name' => $updateData['attribute_set_id']))->pluck('attribute_set_id');
                                                    if($attributeSetId != '')
                                                    {
                                                        $updateData['attribute_set_id'] = $attributeSetId;
                                                    }else{
                                                        $attributeSetId = DB::table('attribute_sets')->where(array('manufacturer_id' => $manufacturerId, 'attribute_set_id' => $updateData['attribute_set_id']))->pluck('attribute_set_id');                                                        
                                                    }
                                                    if($attributeSetId == '')
                                                    {
                                                        $message = 'Attribute set '. $updateData['attribute_set_id'] .' does not exists.';
                                                        continue;
                                                    }
                                                }
                                                if(isset($updateData['product_group_id']))
                                                {
                                                    $productGroupId = DB::table('product_groups')->where(array('manufacture_id' => $manufacturerId, 'name' => $updateData['product_group_id']))->value('group_id');
                                                    if($productGroupId != '')
                                                    {
                                                        $updateData['product_group_id'] = $productGroupId;
                                                    }else{
                                                        $productGroupId = DB::table('product_groups')->where(array('manufacture_id' => $manufacturerId, 'group_id' => $updateData['product_group_id']))->value('group_id');
                                                    }
                                                    if($productGroupId == '')
                                                    {
                                                        $message = 'Group '. $updateData['product_group_id'] .' does not exists.';
                                                        continue;
                                                    }
                                                }
                                                $entity_id = DB::table($tableName)->where(array('product_id' => $productId, 'location_id' => $locationId, 'attribute_set_id' => $attributeSetId, 'product_group_id' => $productGroupId))->pluck('product_id');
                                                if($entity_id)
                                                {
                                                    continue;
                                                }else{
                                                    $updateData['product_id'] = $productId;
                                                    $updateData['location_id'] = $locationId;
                                                    $updateData['product_group_id'] = $productGroupId;
                                                    $updateData['attribute_set_id'] = $attributeSetId;
                                                    unset($updateData['material_code']);
                                                    DB::table($tableName)->insert($updateData);
                                                    $countrows++;
                                                }
                                                break;
                                        }
                                    } else
                                    {
                                        $message = "Product not found with material code : " . $updateData['material_code'] . "  ";
                                    }
                                }
                            } else
                            {
                                $message = "Empty Data ";
                            }
                        }
                    } else
                    {
                        $message = "Material code field doesnot exists";
                    }
                } else
                {
                    $message = "No records found";
                }
            } else
            {
                $message = "No header found";
            }
            if ($countrows == count($tempArray))
            {
                return response()->json([
                    'status' => true,
                    'sucess_records' => $countrows,
                    'message' => 'Imported sucessfully'
                ]);
            } else
            {
                return response()->json([
                    'status' => true,
                    'sucess_records' => $countrows,
                    'failed_records' => (count($tempArray) - $countrows),
                    'message' => $message
            ]);
            }
        }
    }


    public function bulkUpdateProducts(Request $request)
    {
        //echo "hai";exit;
        //$data = Input::all();
        $data = Session::all();
        //$data= $request->input();
        //dd($data);die;
        $manufacturerId = isset($data['manufacturerID']) ? $data['manufacturerID'] : 0;
        //dd($manufacturerId);die;
        $tableName = isset($data['table_name']) ? $data['table_name'] : 'products';
        $operation = isset($data['operation']) ? $data['operation'] : 'products';
        $filePath = isset($_FILES['files']['tmp_name']) ? $_FILES['files']['tmp_name'] : '';
        $fileName = isset($_FILES['files']['name']) ? $_FILES['files']['name'] : '';
        dd($fileName);die;
        $extension = '';
        if ($fileName != '')
        {
            $extension = pathinfo($_FILES['files']['name'], PATHINFO_EXTENSION);
        }
        $allowed_Extensions = ['XLS','XLSX'];

        if( !in_array(strtoupper($extension), $allowed_Extensions))
        {
            return response()->json([
                    'status' => false,
                    'sucess_records' => 0,
                    'message' =>'Please upload an Excel file with .xls or .xlsx extension.'
                ]);
            //return 'Please provide CSV file.';
        }
        $errorMessage = '';
        if ($filePath != '')
        {
            if (!$fh = fopen($filePath, 'r'))
            {
                throw new Exception("Could not open".$filePath." for reading.");
            }
            $i = 0;
            $buffer = array();
            $tempArray = array();
            $path = Input::file('files')->getRealPath();
            $tempArray = Excel::load($path, function($reader) {})->get()->toArray();
            // while (!feof($fh))
            // {
            //     $buffer[] = fgets($fh);
            //     $i++;
            //     $fields = array();
            //     foreach ($buffer as $line)
            //     {
            //         $fields = explode(',', $line);
            //     }
            //     $tempArray[] = $fields;
            // }
            $count = 0;
           // $fileHeaders = isset($tempArray[0]) ? $tempArray[0] : array();
            $headers = [];
            $link='';
            //$actualHeaders = ["name","product_type_id","business_unit_id","category_id","model_name","description","currency_class_id","mrp","material_code","is_gds_enabled","level0","level1","level2","is_serializable","is_batch_enabled","is_backflush","inspection_enabled","location_id","fg_storage_location","group_id","field1","field2","field3","field4","field5"];
            $productHeaders = array('name', 'product_type_id', 'business_unit_id', 'category_id', 'model_name', 'description', 'currency_class_id', 'mrp', 'material_code','uom_class_id', 'is_gds_enabled', 'level0', 'level1', 'level2', 'is_serializable', 'is_batch_enabled', 'is_backflush', 'inspection_enabled', 'location_erp_code', 'fg_storage_location', 'group_id', 'field1', 'field2', 'field3', 'field4', 'field5','is_traceable');
            if (!empty($tempArray[0]))
            {
                // if (isset($tempArray[0]))
                // {
                //     unset($tempArray[0]);
                // }
                $updateData = array();
                $countrows = 0;
                $error_messages =[];
                $i=0;
               // $fileHeadersTrim = array_map('trim', $fileHeaders);
                 $fileHeadersTrim = array_map('trim', $productHeaders);
                $message = array();
                //dd($tempArray);
                //dd(count($tempArray[count($tempArray)]));
                // if((count($tempArray[count($tempArray)] == 0)) ||(count($tempArray[count($tempArray)]) ==  1 && empty($tempArray[count($tempArray)])) ){
                //     unset($tempArray[count($tempArray)]);
                // }
                //$tempArray = array_values($tempArray);
                //dd(count($tempArray));
                if (count($tempArray[0]) > 0)
                {
                    if (in_array('material_code', $fileHeadersTrim))
                    {
                        $j=0;
                        
                        foreach ($tempArray[0] as $fileData)
                        {
                            $j++;
                            dd($fileData);
                            //dd(count($fileData));
                            if (count($fileData) > 1)
                            {
                                //dd("dddd");
                                $fileData = array_values($fileData);
                                $updateData = array_combine($fileHeadersTrim, array_map('trim', $fileData));
                                if (isset($updateData['material_code']) && $updateData['material_code'] != '')
                                {
                                    $productId = DB::table('products')->where(array('manufacturer_id' => $manufacturerId, 'material_code' => $updateData['material_code']))->pluck('product_id');
                                    if ($productId != '')
                                    {
                                        switch($tableName)
                                        {
                                            case 'products':
                                                $status =1;
                                                //dd($updateData['material_code']);
                                                $material_code = $updateData['material_code'];
                                                $level0 = $updateData['level0'];
                                                $level1 = $updateData['level1'];
                                                $level2 = $updateData['level2'];

                                                unset($updateData['material_code']);
                                                unset($updateData['level0']);
                                                unset($updateData['level1']);

                                                $plant_code = $updateData['location_erp_code'];
                                                unset($updateData['location_erp_code']);
                                                
                                                if(!empty($level0) && !is_numeric($level0)){
                                                    $status =0;
                                                    $error_messages[$i++] = "Level0 Capacity should  be numeric at row ".$j;
                                                }
                                                
                                            if(!empty($updateData['product_type_id'])){
                                                $product_type_id = DB::table('master_lookup')->where('name',$updateData['product_type_id'])->pluck('value');

                                                if(!$product_type_id){
                                                    $status =0;
                                                    $error_messages[$i++]="Product Type does not exists at row ".$j;
                                                }

                                                else{
                                                    $updateData['product_type_id']= $product_type_id;
                                                }
                                            }
                                            if(!empty($updateData['category_id'])){
                                                $category = DB::table('categories')->where('name',$updateData['category_id'])->where('customer_id', $manufacturerId)->pluck('category_id');

                                                if(!$category){
                                                    $status =0;
                                                    $error_messages[$i++]="Category does not exists at row ".$j;   
                                                }
                                                else{
                                                    $updateData['category_id']= $category;
                                                }
                                            }
                                            if(!empty($updateData['business_unit_id'])){
                                                $business_unit = DB::table('business_units')->where('name',$updateData['business_unit_id'])->where('manufacturer_id',$manufacturerId)->pluck('business_unit_id');
                                                

                                                if(!$business_unit){
                                                    $status= 0;
                                                    $error_messages[$i++]="Business Unit is not matched at row ".$j;
                                                }
                                                else{
                                                    $updateData['business_unit_id']=$business_unit;
                                                }
                                            }
                                            if(!empty($updateData['currency_class_id'])){

                                                $currency_class_id = DB::table('currency')->where('code',$updateData['currency_class_id'])->pluck('currency_id');
                                                if(!$currency_class_id){
                                                     $status= 0;
                                                    $error_messages[$i++]="Invalid currency Code given at row ".$j;
                                                }
                                                else{
                                                    $updateData['currency_class_id']=$currency_class_id;
                                                }
                                            }
                                            if(!empty($updateData['uom_class_id'])){

                                                $uom_class_id = DB::table('uom_classes')->where('uom_name',$updateData['uom_class_id'])->where('manufacturer_id',$manufacturerId)->pluck('id');
                                                
                                                if(!$uom_class_id){
                                                     $status= 0;
                                                    $error_messages[$i++]="Invalid UOM Class name given at row ".$j;
                                                }
                                                else{
                                                    $updateData['uom_class_id']=$uom_class_id;
                                                }
                                            }
                                            if(!empty($plant_code)){
                                                $plant_locations = explode(',',$plant_code);
                                                $locations_not_matched = [];
                                        //dd($productDetails['plant_code']);
                                        
                                            //$plant_locations = explode(',',$productDetails['plant_code']);
                                            $location_erp_codes = DB::table('locations')->whereIn('erp_code',$plant_locations)->lists('erp_code');
                                            $locations_not_matched = array_diff($plant_locations,$location_erp_codes);

                                        
                                        if(count($locations_not_matched)!=0){
                                            $status = 0;
                                            $error_messages[$i++] = "Invalid Plant Codes ".implode(',',$locations_not_matched)." given at row ".$j;
                                        }

                                            }
                                                foreach($updateData as $key=>$data){
                                                    if(empty($data)){
                                                        unset($updateData[$key]);
                                                    }
                                                }
                                                if($status ==1){

                                                    ///dd("updating");
                                                    $updated=DB::table($tableName)->where('product_id', $productId)->update($updateData);  
                                                    if($plant_code !=""){
                                                        $esealLocationIds = DB::table('locations')->whereIn('erp_code',$plant_locations)->where('manufacturer_id' , $manufacturerId)->lists('location_id');
                                                         if(!empty($esealLocationIds))
                                                        {
                                                            //     $insertLocationData['location_name'] = $plant_code;
                                                            // $insertLocationData['erp_code'] = $plant_code;
                                                            // $esealLocationId = DB::table('locations')->insertGetId($insertLocationData);
                                                            DB::table('product_locations')->where('product_id',$productId)->delete();
                                                            $esealLocationIds = DB::table('locations')->whereIn('location_id',$esealLocationIds)->orWhereIn('parent_location_id',$esealLocationIds)->lists('location_id');
                                                            foreach($esealLocationIds as $id){

                                                                DB::table('product_locations')->insert(['product_id'=>$productId,'location_id'=>$id]);//where('product_id',$productId)->update(['location_id'=>$esealLocationId]);
                                                            }
                                                        }
                                                        
                                                            
                                                        // $insertData['location_id'] = $esealLocationId;
                                                        // DB::table('product_locations')->insert($insertData);
                                                        
                                                    }  

                                                    $levelinformation = ['level0' => $level0, 'level1' => $level1, 'level2' => $level2];
                                                    foreach ($levelinformation as $levelData => $value) {
                                                        if ($value != '') {
                                                            $levelInsertArray = [];
                                                            $levelId = DB::table('master_lookup')->where('name', $levelData)->pluck('value');
                                                            $levelInsertArray['level'] = $levelId;
                                                            $levelInsertArray['quantity'] = $value;
                                                            $levelInsertArray['product_id'] = $productId;
                                                            DB::table('product_packages')->where('product_id', $productId)->where('level', $levelId)->delete();
                                                            if($value !=0){
                                                                DB::table('product_packages')->insert($levelInsertArray);    
                                                            }
                                                            
                                                        }
                                                    }
                                                    $countrows++;
                                                   // dd($updated);
                                                }
                                                
                                                
                                                break;
                                            case 'product_locations':
                                                if(isset($updateData['location_erp_code']))
                                                {
                                                    $locationId = DB::table('locations')->where(array('manufacturer_id' => $manufacturerId, 'erp_code' => $updateData['location_erp_code']))->pluck('location_id');
                                                    if($locationId == '')
                                                    {
                                                        $message = 'Location '. $updateData['location_erp_code'] .' does not exists.';
                                                        break;
                                                    }
                                                }
                                                $product_id = DB::table($tableName)->where(array('product_id' => $productId, 'location_id' => $locationId))->pluck('product_id');
                                                if($product_id && 'delete' != $operation)
                                                {
                                                    continue;
                                                }else{
                                                    unset($updateData['location_erp_code']);
                                                    unset($updateData['material_code']);
                                                    if($operation == 'update')
                                                    {
                                                        $updateData['product_id'] = $productId;
                                                        $updateData['location_id'] = $locationId;                                                        
                                                        DB::table($tableName)->insert($updateData);
                                                    }elseif($operation == 'delete'){
                                                        $updateData['product_id'] = $productId;
                                                        $updateData['location_id'] = $locationId;
                                                        $locationData = DB::table($tableName)->where($updateData)->pluck('product_id');
                                                        if($locationData != '')
                                                        {
                                                            DB::table($tableName)->where($updateData)->delete();
                                                        }else{
                                                            $message = " No records for product material code => ".$productId." location Id => ".$locationId." ";
                                                        }
                                                    }
                                                    $countrows++;
                                                }
                                                break;
                                            case 'product_attributesets':
                                                if(isset($updateData['location_erp_code']))
                                                {
                                                    $locationId = DB::table('locations')->where(array('manufacturer_id' => $manufacturerId, 'erp_code' => $updateData['location_erp_code']))->pluck('location_id');
                                                    if($locationId != '')
                                                    {
                                                        unset($updateData['location_erp_code']);
                                                        $updateData['location_id'] = $locationId;
                                                    }else{
                                                        $message = 'Location '. $updateData['location_erp_code'] .' does not exists.';
                                                        break;
                                                    }
                                                }
                                                if(isset($updateData['attribute_set_id']))
                                                {
                                                    $attributeSetId = DB::table('attribute_sets')->where(array('manufacturer_id' => $manufacturerId, 'attribute_set_name' => $updateData['attribute_set_id']))->pluck('attribute_set_id');
                                                    if($attributeSetId != '')
                                                    {
                                                        $updateData['attribute_set_id'] = $attributeSetId;
                                                    }else{
                                                        $attributeSetId = DB::table('attribute_sets')->where(array('manufacturer_id' => $manufacturerId, 'attribute_set_id' => $updateData['attribute_set_id']))->pluck('attribute_set_id');                                                        
                                                    }
                                                    if($attributeSetId == '')
                                                    {
                                                        $message = 'Attribute set '. $updateData['attribute_set_id'] .' does not exists.';
                                                        continue;
                                                    }
                                                }
                                                if(isset($updateData['product_group_id']))
                                                {
                                                    $productGroupId = DB::table('product_groups')->where(array('manufacture_id' => $manufacturerId, 'name' => $updateData['product_group_id']))->pluck('group_id');
                                                    if($productGroupId != '')
                                                    {
                                                        $updateData['product_group_id'] = $productGroupId;
                                                    }else{
                                                        $productGroupId = DB::table('product_groups')->where(array('manufacture_id' => $manufacturerId, 'group_id' => $updateData['product_group_id']))->pluck('group_id');
                                                    }
                                                    if($productGroupId == '')
                                                    {
                                                        $message = 'Group '. $updateData['product_group_id'] .' does not exists.';
                                                        continue;
                                                    }
                                                }
                                                $entity_id = DB::table($tableName)->where(array('product_id' => $productId, 'location_id' => $locationId, 'attribute_set_id' => $attributeSetId, 'product_group_id' => $productGroupId))->pluck('product_id');
                                                if($entity_id)
                                                {
                                                    continue;
                                                }else{
                                                    $updateData['product_id'] = $productId;
                                                    $updateData['location_id'] = $locationId;
                                                    $updateData['product_group_id'] = $productGroupId;
                                                    $updateData['attribute_set_id'] = $attributeSetId;
                                                    unset($updateData['material_code']);
                                                    DB::table($tableName)->insert($updateData);
                                                    $countrows++;
                                                }
                                                break;
                                        }
                                    } else
                                    {
                                        $error_messages[$i++] = "Product not found with material code : " . $updateData['material_code'] . "  at row no ".$j;
                                    }
                                }
                                else{
                                    $error_messages[$i++] = "No Material Code given at row no ".$j;
                                }
                            } else
                            {
                                $message = "Empty Data ";
                            }
                        }
                    } else
                    {
                        $message = "Material code field doesnot exists";
                    }
                } else
                {
                    //$message = "No records found";
                    return response()->json([
                    'status' => false,
                    'sucess_records' => 0,
                    'message' => 'No records found'
                ]);
                }
            } else
            {
                //$message = "No header found";
                return responsse()->json([
                    'status' => false,
                    'sucess_records' => 0,
                    'message' => 'No header found'
                ]);
            }
            $time = Date('YmdHis');
            if(!empty($error_messages)){
                Excel::create('ProductBulkUpdateErrorLog'.$time, function($excel) use($error_messages) {
                    return $excel->sheet('New sheet', function($sheet) use($error_messages){
                    $sheet->loadView('products.productsbulkimporterror',array('error_messages'=>$error_messages));
                    });
                })->store('xlsx', public_path()."/download");//"C://Users/300137/Desktop");
                $link = "error_log_link/ProductBulkUpdateErrorLog".$time.".xlsx";

            }
            else{

            }

            if ($countrows == count($tempArray))
            {
                return response()->json([
                    'status' => true,
                    'sucess_records' => $countrows,
                    'failed_records' =>0,
                    'message' => 'Imported sucessfully'
                ]);
            } else
            {
                return response()->json([
                    'status' => true,
                    'sucess_records' => $countrows,
                    'failed_records' => (count($tempArray) - ($countrows)),
                    'message' => "",//$message,
                    'error_log_link'=>$link
            ]);
            }
        }
    }


    public function checkProductName($productName, $manufacturerId, $materialCode)
    {
        try
        {
            if($productName != '')
            {
\Log::info('we aer in if');
                $productId = DB::table('products')->where(array('manufacturer_id' => $manufacturerId, 'material_code' 
=> "'".$materialCode."'"))->pluck('product_id');
$query = "select product_id from products where manufacturer_id = ".$manufacturerId." and material_code =
'".$materialCode."'";
\Log::info($query);
$temp = DB::select($query);
\Log::info($temp);
                if($productId)
                {
                    return 'Product already exists.';
                }else{
                    return 1;
                }
            }elseif($productName == 0)
            {
\Log::info('we re in else');
\Log::info($materialCode);
                $productId = DB::table('products')->where(array('manufacturer_id' => $manufacturerId, 'material_code'=> $materialCode))->pluck('product_id');
$query = "select product_id from products where manufacturer_id = ".$manufacturerId." and material_code = 
'".$materialCode."'";
\Log::info($query);
$temp = DB::select($query);
\Log::info('temp');
\Log::info($temp);
\Log::info($productId);
                if($productId)
                {
                    return $productId;
                }else{
                    return 0;
                }
            }

            return 'No product name.';
        } catch (\ErrorException $ex) {
            return $ex->getMessage();
        }
    }



        public function checkMaterialCode($productName, $manufacturerId, $materialCode)
    {
        Log::info("-----------Material code validation------");
        Log::info("Product Name ------".$productName);
        Log::info("Manufacturer Id-----".$manufacturerId);
        Log::info("Material code---------".$materialCode);
        try
        {
            if(empty($productName) ){
                return " Product Name is empty.";
            }

            if( empty($materialCode)){
                return "Material Code is empty.";
            }
            if($productName != '' )
            {
\Log::info('we aer in if');
                $productId = DB::table('products')->where(array('manufacturer_id' => $manufacturerId, 'material_code' => $materialCode))->value('product_id');
                // echo $productId;exit;
                Log::info("-------".$productId);
$query = "select product_id from products where manufacturer_id = ".$manufacturerId." and material_code =
'".$materialCode."'";
\Log::info($query);
$temp = DB::select($query);

\Log::info($temp);
                if($productId)
                {
                    // echo($productId);exit;
                    Log::info('Material code aldeady exists');

                    return 'Material Code already exists.';
                }else{
                    Log::info("material code does not exists");
                    return 1;
                }
             }
             //elseif($productName == 0)
//             {
// \Log::info('we re in else');
// \Log::info($materialCode);
//                 $productId = DB::table('products')->where(array('manufacturer_id' => $manufacturerId, 'material_code' 
// => "'".$materialCode."'"))->pluck('product_id');
// $query = "select product_id from products where manufacturer_id = ".$manufacturerId." and material_code = 
// '".$materialCode."'";
// \Log::info($query);
// $temp = DB::select($query);
// \Log::info('temp');
// \Log::info($temp);
// \Log::info($productId);
//                 if($productId)
//                 {
//                     return $productId;
//                 }else{
//                     return 0;
//                 }
//             }
            else{
                return 'No Product Name.';
            }

            return 'No product name.';
        } catch (\ErrorException $ex) {
            return $ex->getMessage();
        }
    }
    

    public function updateFieldValues($tempStoredProducts, $manufacturerId)
    {
        try
        {
            $productTypeName = isset($tempStoredProducts['product_type_id']) ? $tempStoredProducts['product_type_id'] : '';
            $businessUnitName = isset($tempStoredProducts['business_unit_id']) ? $tempStoredProducts['business_unit_id'] : '';
            $categoryName = isset($tempStoredProducts['category_id']) ? $tempStoredProducts['category_id'] : '';
            $currencyClassName = isset($tempStoredProducts['currency_class_id']) ? $tempStoredProducts['currency_class_id'] : '';
            $UomClassName = isset($tempStoredProducts['uom_class_id']) ? $tempStoredProducts['uom_class_id'] : '';
            if($productTypeName != '')
            {                
                $productTypeId = DB::table('master_lookup')
                        ->leftJoin('lookup_categories', 'lookup_categories.id', '=', 'master_lookup.category_id')
                        ->where('lookup_categories.name', 'Product Types')
                        ->where('master_lookup.name', $productTypeName)
                        ->value('master_lookup.value');
                if(!$productTypeId)
                {
                    $productTypeId = DB::table('master_lookup')
                        ->leftJoin('lookup_categories', 'lookup_categories.id', '=', 'master_lookup.category_id')
                        ->where('lookup_categories.name', 'Product Types')
                        ->where('master_lookup.name', 'Component')
                        ->value('master_lookup.value');
                }
                $tempStoredProducts['product_type_id'] = $productTypeId;
            }
            if($businessUnitName != '')
            {
                $businessUnitId = DB::table('business_units')
                        ->where('name', $businessUnitName)
                        ->where('manufacturer_id', $manufacturerId)
                        ->pluck('business_unit_id');
                if(!$businessUnitId)
                {
                    $insertData['name'] = $businessUnitName;
                    $insertData['description'] = $businessUnitName;
                    $insertData['manufacturer_id'] = $manufacturerId;
                    $insertData['is_active'] = 1;
                    $businessUnitId = DB::table('business_units')->insertGetId($insertData);
                }
                $tempStoredProducts['business_unit_id'] = $businessUnitId;
            }
            if($categoryName != '')
            {                
                $categoryId = DB::table('categories')
                        ->where('name', $categoryName)
                        ->pluck('category_id');
                if(!$categoryId)
                {
                    $categoryId = DB::table('categories')
                        ->where('name', 'Electrical and Hardware')
                        ->pluck('category_id');
                }
                $tempStoredProducts['category_id'] = $categoryId;
            }
            if($currencyClassName != '')
            {
                $currencyClassId = DB::table('currency')
                        ->where('code', $currencyClassName)
                        ->pluck('currency_id');
                if(!$currencyClassId)
                {
                    $currencyClassId = $currencyClassId = DB::table('currency')
                        ->where('code', 'INR')
                        ->pluck('currency_id');
                }
                $tempStoredProducts['currency_class_id'] = $currencyClassId;
            }
            if($UomClassName != '')
            {
                $UomClassId = DB::table('uom_classes')->where('manufacturer_id',$manufacturerId)
                        ->where('uom_name', $UomClassName)
                        ->pluck('id');
                if(!$UomClassId)
                {
                
                }
                $tempStoredProducts['uom_class_id'] = $UomClassId;
            }
            if($tempStoredProducts['is_traceable'] != 1){
                $tempStoredProducts['is_traceable'] = 0;
            }
            
            return $tempStoredProducts;
        } catch (\ErrorException $ex) {
            return $ex->getMessage();
        }
    }
    //GetAttributes for User
     public function customerAttributesAll($manufacturerId,$set)
    {
     $set=$this->roleRepo->decodeData($set);
     $completeResult = array();
     $result=DB::select(DB::raw("SELECT concat('_',c.attribute_id) attribute_id,c.name from attribute_sets a,attribute_set_mapping b,attributes c 
                                where 1=1
                                and a.manufacturer_id = 0 
                                and a.attribute_set_id=b.attribute_set_id
                                and c.attribute_id=b.attribute_id

                                union

                                (select concat('_',c.attribute_id) attribute_id,c.name from attribute_sets a,attribute_set_mapping b,attributes c 
                                where 1=1
                                and c.attribute_id not in (
                                select c.attribute_id from attribute_sets a,attribute_set_mapping b,attributes c 
                                where 1=1
                                and a.manufacturer_id = 0 
                                and a.attribute_set_id=b.attribute_set_id
                                and c.attribute_id=b.attribute_id
                                )
                                and a.attribute_set_id=b.attribute_set_id
                                and c.attribute_id=b.attribute_id
                                and a.manufacturer_id = $manufacturerId)"));
    /*echo '<pre>';
    print_r($result);die;*/

    $allAttributes=array();
    foreach($result as $res)
    {
        $allAttributes[$res->attribute_id] = $res->name;
    }
    $completeResult['default'] = $result;

    $setAttributes=DB::select(DB::raw("SELECT concat('_',b.attribute_id) attribute_id,b.name from attribute_set_mapping a,attributes b
                                where 1=1
                                and a.attribute_id=b.attribute_id
                                and  a.attribute_set_id=$set
                                order by a.sort_order"));
/*
    echo '<pre>';
    print_r($setAttributes);die;*/
    $selectAttributes= array();
    foreach($setAttributes as $setAttribute)
    {
        $selectAttributes[$setAttribute->attribute_id] = $setAttribute->name;
    }
    /*echo '<pre>';
    print_r($selectAttributes);die;*/
    $completeResult['selectedAttr'] = $selectAttributes;
    
    $unselected=array_diff($allAttributes, $selectAttributes);
    $completeResult['unselected'] = $unselected;
    unset($completeResult['default']);    
/*    echo '<pre>';
    print_r($completeResult);die;*/
    return json_encode($completeResult);
    //return $completeResult;
    }
    //GetAttributes for User
    public  function updateattributeset($attribute_set_id){
    $attributeObj = new Products\ProductAttributes();
    return $attributeObj->updateattributeset($attribute_set_id,Input::all());
    }
    public function checkSetAvailability()
    {
      $data=Input::all();
      $attribute_set_id=Input::get('attribute_set_id');
      $attribute = DB::table('attribute_sets');             
      if($attribute_set_id){
        $attrName=$attribute->where('attribute_sets.attribute_set_name',$data['attribute_set_name'])
                            ->where('attribute_sets.manufacturer_id',$data['manufacturer_id'])
                            ->where('attribute_set_id','!=',$attribute_set_id)
                            ->pluck('attribute_set_name');             
      } 
      else{
        $attrName=$attribute->where('attribute_sets.attribute_set_name',$data['attribute_set']['attribute_set_name'])
                                ->where('attribute_sets.manufacturer_id',$data['manufacturer_id'])
                                ->pluck('attribute_set_name'); 

      }
      if(empty($attrName))
           {
            return json_encode([ "valid" => true ]);
           }                     
          else 
          {
            return json_encode([ "valid" => false ]);
          }      
    }
    public function checkGroupAvailability()
    {
      $data=Input::all();
       $attribute = DB::table('attributes_groups')
            ->where('attributes_groups.name',$data['attribute_group']['name'])
            ->whereIn('attributes_groups.manufacturer_id',array($data['manufacturer_id'],0))
            ->get();     
      if(empty($attribute))
           {
            return json_encode([ "valid" => true ]);
           }                     
          else 
          {
            return json_encode([ "valid" => false ]);
          }      
    }  
    public function checkAttributeAvailability()
    {
      $data=Input::all();
      $attribute_id=Input::get('attribute_id');
    if($attribute_id){
            $defaultAttr= DB::table('attribute_sets as aset')
                    ->join('attribute_set_mapping as map', 'map.attribute_set_id', '=', 'aset.attribute_set_id')
                    ->join('attributes as attr', 'attr.attribute_id', '=', 'map.attribute_id')
                    ->whereIn('attr.name',array($data['name']))
                    ->where('attr.attribute_id','!=',$attribute_id)
                    ->where('aset.attribute_set_name','=', 'Default')->get();
            //return $defaultAttr;
            if(empty($defaultAttr)){

                    $attributeSpecific = DB::table('attribute_sets as aset')
                    ->join('attribute_set_mapping as map', 'map.attribute_set_id', '=', 'aset.attribute_set_id')
                    ->join('attributes as attr', 'attr.attribute_id', '=', 'map.attribute_id')
                    ->where('attr.name',$data['name'])
                    ->where('attr.attribute_id','!=',$attribute_id)
                    ->where('aset.manufacturer_id',$data['manufacturer_id'])->get();
            if(empty($attributeSpecific))
               {
                return json_encode([ "valid" => true ]);
               }else{
                return json_encode([ "valid" => false ]);
                }                   
            }else{
                return json_encode([ "valid" => false ]);
            }
    }else{  
            $defaultAttr= DB::table('attribute_sets as aset')
                    ->join('attribute_set_mapping as map', 'map.attribute_set_id', '=', 'aset.attribute_set_id')
                    ->join('attributes as attr', 'attr.attribute_id', '=', 'map.attribute_id')
                    ->whereIn('attr.name',array($data['name']))
                    ->where('aset.attribute_set_name','=', 'Default')->get();
            //return $defaultAttr;
            if(empty($defaultAttr)){
            $attributeSpecific = DB::table('attribute_sets as aset')
                    ->join('attribute_set_mapping as map', 'map.attribute_set_id', '=', 'aset.attribute_set_id')
                    ->join('attributes as attr', 'attr.attribute_id', '=', 'map.attribute_id')
                    ->where('attr.name',$data['name'])
                    ->where('aset.manufacturer_id',$data['manufacturer_id'])->get();
              if(empty($attributeSpecific))
                   {
                    return json_encode([ "valid" => true ]);
                   }                     
                  else 
                  {
                    return json_encode([ "valid" => false ]);
                  }                   
            }else{
                return json_encode([ "valid" => false ]);
            }
       }
   
    } 
    public function checkDefaultAttributeAvailability()
    {
      $data=Input::all();
            $defaultAttr= DB::table('attribute_sets as aset')
                    ->join('attribute_set_mapping as map', 'map.attribute_set_id', '=', 'aset.attribute_set_id')
                    ->join('attributes as attr', 'attr.attribute_id', '=', 'map.attribute_id')
                    ->whereIn('attr.name',array($data['name']))
                    ->where('aset.attribute_set_name','=', 'Default')->get();
            if(empty($defaultAttr)){
                return json_encode([ "valid" => true ]);
               }else{
                return json_encode([ "valid" => false ]);
                }                   
           
    } 
    public function checkAttrAvailability()
    {
      $data=Input::all();
      $attribute_id=Input::get('attribute_id');
      if($attribute_id){
          $attributeSpecific = DB::table('attributes')
                              ->where('attribute_code',$data['attribute_code'])
                              ->where('attribute_id','!=',$data['attribute_id'])->get();
            if(empty($attributeSpecific)){
                return json_encode([ "valid" => true ]);
               }else{
                return json_encode([ "valid" => false ]);
                } 
        }else{
            $attributeSpecific = DB::table('attributes')
                              ->where('attribute_code',$data['attribute_code'])->get();
            if(empty($attributeSpecific)){
                return json_encode([ "valid" => true ]);
               }else{
                return json_encode([ "valid" => false ]);
                } 
        }                  
           
    }              
    
    public function assignGroups()
    {
        $attributeObj = new Products\ProductAttributes();
        $data = Input::all();
        return $attributeObj->assignGroups($data);
    }
    public function getAssignGroupDetails($attribute_set_id)
    {
        $AssignGroupDetails=DB::table('product_attributesets')
                            ->join('locations','locations.location_id','=','product_attributesets.location_id')
                            ->join('product_groups','product_groups.group_id','=','product_attributesets.product_group_id')
                            ->where('product_attributesets.attribute_set_id',$attribute_set_id)->get(array('locations.location_name as location_name','product_groups.name as productgroup','product_attributesets.product_group_id as product_group_id','product_attributesets.location_id as location_id'));
        return $AssignGroupDetails;
    }   
    public function getoptions($attribute_id)
    {
        $getoptions=DB::table('attribute_options')
                            ->where('attribute_options.attribute_id',$attribute_id)->get(array('attribute_id','option_value','option_name','sort_order'));
        return $getoptions;
    }    
    public function searchAttributes()
    {
       try
       {
            $data=Input::get();
            $attribute_set_id = $this->roleRepo->decodeData($data['attribute_set_id']);
            $attribute_id = $this->roleRepo->decodeData($data['attribute_id']);
            if($data['flag'] == 0)
            {
                $search = 0;
            }elseif($data['flag'] == 1)
            {
                $search = 1;
            }
           DB::table('attribute_set_mapping')
            ->where(array('attribute_set_id'=>$attribute_set_id,'attribute_id'=>$attribute_id))
            ->update(array('is_searchable'=>$search));
            return 1;
       }
        catch (ErrorException $ex) {
            return response()->json([
                'status' => false,
                'message' => $ex->getMessage()
            ]);
        } 
    }  

public function erpCodeUniquevalidation(){
    $data = Input::all();
    //dd($data);

    try
        {
            $customerId = isset($data['manufacturer_id']) ? $data['manufacturer_id'] : 0;
            $tableName = isset($data['table_name']) ? $data['table_name'] : '';
            $code = isset($data['code']) ? $data['code'] : '';
            $rowData = 1;
            $requestType = isset($data['request_type']) ? $data['request_type'] : 'create';
            if($requestType == 'edit'){
                $product_id = isset($data['product_id']) ? $data['product_id'] : 0;
            }
            if($customerId){
                if($requestType == 'edit' && $product_id  > 0 ){
                    //dd("ggg");
                    $rowData = DB::table($tableName)->where('manufacturer_id',$customerId)->where('product_id','!=',$product_id)->where('material_code',$code)->count();
                    //dd($rowData);
                    //$rowData =count($rowData);
                }
                else if($requestType == 'create'){
                    $rowData = DB::table($tableName)->where('manufacturer_id',$customerId)->where('material_code',$code)->count();
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



public function errorLog($excelname){
    $file= public_path(). "/download/".$excelname;
        // $headers = array(
        //       'Content-Type'=>  'application/octet-stream',
        //       'Content-Disposition'=> 'attachment',
        //       'Content-Transfer-Encoding'=>'binary',
        //       'Cache-Control'=> 'must-revalidate',
        //       'Pragma'=> 'public'
        //     );
        // return response()->download($file, $excelname, $headers);
 ob_clean();
    ob_start();
   return Excel::load($file, function($reader){
                    $sheet = $reader->getActiveSheet();
                    // Manipulate third row
                    // $sheet->row(3, array(
                    //         'test1', 'test2'
                    //     ));
                })->export('xls');
}



public function newuom_uniquevalidation(){
    $validator = Input::get('validator');
    $uom_name = Input::get('uom_new');
    $uom_code = Input::get('uom_code');
    $customer = Session::get('customerId');
    //dd($customer);
    $column = $validator;
    if($column == 'uom_code'){
        $value = $uom_code;
    }
    else if($column == 'uom_name'){
        $value = $uom_name;
    }
    if(!$customer){
        return json_encode([ "valid" => false ]);
    }
    $uoms = DB::table('uom_classes')->where(['manufacturer_id'=>$customer,$column=>$value])->get();
    //dd($uoms);
    if($uoms){
        return json_encode([ "valid" => false ]);
    }
    else{
        return json_encode([ "valid" => true ]);
    }
}

public function addNewUom(){
    $uom_name = Input::get('uom_new');
    $uom_code = Input::get('uom_code');
    $customer_id = Input::get('manufacturer_id');
    if(empty($uom_name) || empty($uom_code)){
        return ['status'=>false,'message'=>'Please enter all the fields'];
    }
     DB::table('uom_classes')->insert(['uom_name'=>$uom_name,'uom_code'=>$uom_code,'manufacturer_id'=>$customer_id]);
     $uom_id = DB::getPdo()->lastInsertId();
     return ['status'=>true,'message'=>"created Successfully",'uom_id'=>$uom_id];
}

public function DeletefromGrid(){
    $data = $this->_request->all();
    $delete = DB::table('product_locations')->whereIn('id',$data['grid_id'])->delete();

    return 1;
}

    public function mrp_config(){
    $data = Input::all();    
    $qry=" select p.product_id,p.name,p.material_code,p.ean,pr.price,pr.applicable_from,CONCAT('<a onclick=\"openHestory(',p.product_id,')\"> More </a>') as actions from product_price pr join products p on p.product_id=pr.product_id
where pr.id in (SELECT max(id) FROM `product_price` pp where pp.applicable_from<=now() ".(isset($data['ser_product'])?' and pp.product_id='.$data['ser_product']:'')." group by product_id)";
    $products=DB::select($qry);
    if(isset($data['ser_product'])){
        echo json_encode($products);
    } else {
       
    $productsList=DB::table('products as p')->where('manufacturer_id',Session::get('customerId'))->get(['p.product_id','p.name','p.material_code','p.ean']);
    return View::make('products/mrp_config')->with(array("products"=>$products,'productsList'=>$productsList));
    }

   /* $products=DB::table('products as p')
            ->join('product_price as pr','pr.product_id','=','p.product_id')
            ->where('p.manufacturer_id',Session::get('customerId'))
            ->where('pr.applicable_from','<=','NOW()');
    if(isset($data['ser_product'])){
        $products=$products->where('p.product_id',$data['ser_product'])->orderBy('pr.id','desc')->groupBy('p.product_id')->get(['p.product_id','p.name','p.material_code','p.ean','pr.price','pr.applicable_from']);
        echo json_encode($products);
    } else {
        $products=$products->orderBy('pr.id','desc')->groupBy('p.product_id')->get(['p.product_id','p.name','p.material_code','p.ean','pr.price','pr.applicable_from']);

        
        return View::make('products/mrp_config')->with(array("products"=>$products,'productsList'=>$productsList));
    }*/

    //echo "TEST"; exit;
    }

   /* public function mrp_config(){
    $data = Input::all();
    $products=DB::table('products as p')
            ->join('product_price as pr','pr.product_id','=','p.product_id')
            ->where('manufacturer_id',Session::get('customerId'))
            ->get(['p.product_id','p.name','p.material_code','p.ean','pr.price','pr.applicable_from']);
    return View::make('products/mrp_config')->with(array("products"=>$products));
    //echo "TEST"; exit;
    }
*/

    public function getPrice(){
        $data = Input::all();
        $status=0;
        $message = '';
        try{
            if(!isset($data['product_id'])){
                throw new Exception("Product Id Required");               
            }
            $mrpDataLog=[];
            $mrpData=DB::table('product_price as p')->join('users as u','u.user_id','=','p.user_id')->join('products as prd','prd.product_id','=','p.product_id')->where('p.product_id',$data['product_id'])->where('p.applicable_from','<=','now()')->orderBy('p.id','DESC')->take(1)->get(['p.id','p.applicable_from','p.price','p.product_id','p.timestamp','p.user_id','u.username','p.remarks','prd.material_code','prd.name']);
            if(count($mrpData)>0)
                $mrpData=$mrpData[0];
            if(isset($data['product_log'])){
                $mrpDataLog=DB::table('product_price_log as p')->join('users as u','u.user_id','=','p.user_id')->where('p.product_id',$data['product_id'])->orderBy('p.id','DESC')->get(['p.id','p.applicable_from','p.price','p.product_id','p.timestamp','p.user_id','u.username','p.remarks']);
            }
        echo json_encode(['status'=>$status,'message'=>$message,'mrp'=>$mrpData,'mrp_list'=>$mrpDataLog]);
        } catch(Exception $e){
          $status=0;
          $message = $e->getMessage();
        echo json_encode(['status'=>$status,'message'=>$message]);
        }
    }
    public function mrp_add(){
    $data = Input::all();
    $status=0;
    $message = '';
    try{

        if(count($_FILES)>0){

            
            $filePath = isset($_FILES['import']['tmp_name']) ? $_FILES['import']['tmp_name'] : '';
            $fileName = isset($_FILES['import']['name']) ? $_FILES['import']['name'] : '';
            $extension = '';
            if ($fileName != '')
            {
                $extension = pathinfo($_FILES['import']['name'], PATHINFO_EXTENSION);
            }
            $allowed_Extensions = ['XLS','XLSX','xls','xls'];

            if( !in_array(strtoupper($extension), $allowed_Extensions))
            {
                return response()->json([
                        'status' => false,
                        'sucess_records' => 0,
                        'message' => 'Please upload an Excel file with .xls or .xlsx extension.'
                    ]);
                //return 'Please provide CSV file.';
            }
            $errorMessage = '';
            if ($filePath != '')
            {
                if (!$fh = fopen($filePath, 'r'))
                {
                    throw new Exception("Could not open".$filePath." for reading.");
                }
                $i = 0;
                $buffer = array();
                $tempArray = array();
                $path = Input::file('import')->getRealPath();
                $tempArray = Excel::load($path, function($reader) {})->get()->toArray();
                $inserData=[];
                $dataFormatValidation=0;
                $productFormatValidation='';
                foreach($tempArray as $data){
                   if(!(preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/",$data['valid_from']))){
                       $dataFormatValidation=1;
                    } else {
                        $product=DB::table('products')->where('material_code',$data['mat_code'])->get(['product_id']);
                        $data['remarks']=isset($data['remarks'])?$data['remarks']:'';
                        if(count($product)>0){
                            $inserData[]=array('product_id'=>$product[0]->product_id,'price'=>$data['price'],'applicable_from'=>date("Y-m-d",strtotime($data['valid_from'])),'user_id'=>Session::get('userId'),'remarks'=>$data['remarks']);
                        }
                        else
                            $productFormatValidation.='invalid mat code '.$data['mat_code'].'/n';
                    }
                }
                if($dataFormatValidation){
                    echo json_encode(['status'=>0,'message'=>"Date Format Should be in YYYY-MM-DD only "]);
                    return '';
                } else if($productFormatValidation!=''){
                     echo json_encode(['status'=>0,'message'=>$productFormatValidation]);
                    return '';
                } else{
                    $newProductprice=DB::table('product_price')->insert($inserData);
                    $newProductprice=DB::table('product_price_log')->insert($inserData);
                }
                $status=1;
                $message = 'Added Sucessfully';
            }
        } else {
            $newProductprice=DB::table('product_price')->insertGetId(['product_id'=>$data['product'],'price'=>$data['price'],'applicable_from'=>date("Y-m-d",strtotime($data['applicable_from'])),'user_id'=>Session::get('userId'),'remarks'=>$data['remarks']]);
            $newProductprice=DB::table('product_price_log')->insertGetId(['product_id'=>$data['product'],'price'=>$data['price'],'applicable_from'=>date("Y-m-d",strtotime($data['applicable_from'])),'user_id'=>Session::get('userId'),'remarks'=>$data['remarks']]);
        }
   
    $status=1;
    $message = 'Added Sucessfully';
    } catch(Exception $e){
      $status=0;
      $message = $e->getMessage();
    }
    echo json_encode(['status'=>$status,'message'=>$message]);
    //echo "TEST"; exit;
    }

    public function exportToproducts($type)
{
    $manuId = Session::get('customerId');
    //print_r($manuId);exit;

$sql=" SELECT p.product_id,p.material_code,p.name,p.description,p.material_code AS ERP,p.product_type_id,pg.name AS GroupName,
c.name AS CategoryName,b.name AS BusinessunitName,p.uom_unit_value,p.field1,p.field2,p.field3,p.field4,
p.field5,p.mrp,u.uom_name,
(
SELECT quantity
FROM product_packages
WHERE LEVEL=16001 AND product_id=p.product_id) AS level_0, 
(
SELECT quantity
FROM product_packages
WHERE LEVEL=16002 AND product_id=p.product_id) AS level_1, GROUP_CONCAT(l.erp_code SEPARATOR ',') AS ProductLocations
FROM products p
left JOIN
 product_locations pl ON pl.product_id = p.product_id
left JOIN
 locations l ON l.location_id = pl.location_id
LEFT JOIN product_groups AS pg ON pg.group_id = p.group_id
left JOIN categories c ON c.category_id = p.category_id
left JOIN business_units AS b ON b.business_unit_id = p.business_unit_id
LEFT JOIN uom_classes u ON u.id = p.uom_class_id
WHERE  p.manufacturer_id =".$manuId." AND l.parent_location_id =0 AND p.material_code !=''
GROUP BY p.product_id;";
//$data = DB::select(DB::raw($sql));
//$products_data =
$data = Session::all();
$data2= $this->_product->getAllProducts($data);
   $data2= json_decode(json_encode($data2), true);
        ob_end_clean();
        ob_start();
// echo "<pre/>";
//  print_r($data2);exit;
/*        return Excel::create('products_export_to_excel', function($excel) use ($data) {
            $excel->sheet('mySheet', function($sheet) use ($data)
            {
                $sheet->fromArray($data);
            });
        })->download($type);*/
        //echo("hai");exit;

          /*return Excel::create('products_export_to_excel', function($excel) use ($data) {

        $excel->sheet('mySheet', function($sheet) use ($data) {
            $sheet->loadView('products/index')->array('data'=> $data)
                                         ;
            $sheet->setOrientation('landscape');
        });

    })->export('xls');*/
    $headers=array('image','name','sku','material_code','manufacturer_id','status','
        is_deleted','product_type_id','actions');
    /*$headers = array('product_id','material_code','name','description','ERP','product_type_id','GroupName','CategoryName','BusinessunitName','uom_unit_value','field1','field2','field3','field4','field5','mrp','uom_name','level_0','level_1','ProductLocations');*/
        Excel::create('Products Report Sheet-', function($excel) use($data2,$headers) 
        {
        $excel->sheet("Allproducts", function($sheet) use($data2,$headers)
        {
        $sheet->loadView('products.productsheet', array('data2' => $data2,'headers'=>$headers)); 
        });
        })->export('xlsx');

        /*Excel::create('Export data', function($excel) use($data) {

          $excel->sheet('Sheet', function($sheet) use ($data) {

           $sheet->fromArray($data);
        });
        })->download('xls');*/

        //return Excel::download($data, 'products_export_to_excel.xlsx');
}



public function getOnboardData($customerId = 0)
    {
        $formData = $this->custRepoObj->prepareLocationData($customerId);
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


    public function getStates($countryId)
    {
        try
        {
            $zones = DB::table('zone')
                ->where('country_id', '=', $countryId)
                ->where('status', '=', 1)
                ->get(array('zone_id', 'name'));      
            $zonesArray = array();
            $zonesArray[0] = 'Please select..';
            foreach ($zones as $zone)
            {
                $zonesArray[$zone->zone_id] = $zone->name;
            }
            return $zonesArray;
        } catch (\ErrorException $ex)
        {
            echo $ex->getMessage();
        }
    }
    public function getLocationsByType()
    {
        $data = $this->_request->all();
        return $this->_esealCustomer->getLocationsByType($data);
    }
         public function editLocation($locationId)
    {
        $loc = DB::table('locations')->where('location_id', $locationId)->get()->toArray();
        //print_r($loc);exit;
        return response()->json($loc);
    }

    public function updateLocation($loc_id)
    {
    //print_r($location_id);exit;
        DB::Table('locations')
                ->where('location_id', $location_id)
                ->update(array('location_name' => $this->_request->get('location_name'),
                               /*'manufacturer_id' => Input::get('manufacturer_id'),*/
                               'parent_location_id' => $this->_request->get('parent_location_id'), 
                               'location_type_id' => $this->_requestget('location_type_id'), 
                               'location_email' => $this->_request->get('location_email'), 
                               'location_address' => $this->_request->get('location_address'), 
                               'location_details' => $this->_request->get('location_details'), 
                               'country' => $this->_request->get('country'), 
                               'state' => $this->_request->get('state'), 
                               'region' => $this->_request->get('region'), 
                               'longitude' => $this->_request->get('longitude'), 
                               'latitude' => $this->_request->get('latitude'), 
                               'erp_code' => $this->_request->get('erp_code'),
                               'business_unit_id'=>$this->_request->get('business_unit_id'),
            'storage_location_type_code'=>$this->_request->get('storage_location_type_code'),
            'modified_on'=>date('Y-m-d H-i-s'),
            'modified_by'=>Session::get('userId')
                               ));
                DB::table('user_tracks')->insert(array('user_id'=>Session::get('userId'),'manufacturer_id'=>Session::get('customerId'),'service_name'=>'Location Edit','message'=>'Location Updated.'.$location_id,'status'=>1));
        return response()->json([
                    'status' => true,
                    'message' => 'Sucessfully updated.'
        ]);
    }

public function editCustomer()
    {
        // $this->_manufacturerId=
        //$customerId = $this->roleRepo->decodeData($customerId);
        $customerId=$this->_manufacturerId;
        //echo ($customerId);exit;
        parent::Breadcrumbs(array('Home'=>'/','Edit Location'=>'#')); 

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
        $customerstoragelocations = DB::table('master_lookup')->join('lookup_categories','lookup_categories.id','=','master_lookup.category_id')->select('master_lookup.name', 'master_lookup.value')->get()->toArray();
        //print_r($customerstoragelocations);exit;
        $customerbusinessid = DB::table('business_units')->select('business_unit_id','name')->where('manufacturer_id',$customerId)->get()->toArray();
        $custstates = $this->getStates(99);
        //dd($customerAddress);
        //echo "<pre>";print_r($customerstoragelocations);die;
        //echo "<pre>";print_r($customerbusinessid);die;
        return View::make('products/locations')->with(array('permissions' => $permissions, 'formData' => $formData, 'customerDetails' => $customerDetails, 'customerAddressData' => $customerAddress, 'erp_details' => $customerErpConfiguration, 'customer_locations' => $customerlocations,'customer_storage_locations'=>$customerstoragelocations,'custstates'=>$custstates,'customer_id' => $this->roleRepo->encodeData($customerId)))
                            ->with('customerstoragelocations',$customerstoragelocations)
                            ->with('customerbusinessid',$customerbusinessid )
                            ->with('allow_buttons', ['add_product' => $allowAddProduct, 'import_product_csv' => $allowImportCsv, 'import_product_component_csv' => $allowImportComponentCsv, 'import_product_erp' => $allowImportErp, 'add_locationtypes' => $allowAddLocationsTypes, 'import_locationtypes_csv' => $allowLocationsTypesImportCsv, 'import_locationtypes_erp' => $allowLocationsTypesImportErp]);
    }



    public function checkLocationTypeName($locationTypeName, $manufacturerID)
    {
        return DB::table('location_types')->where('location_type_name', $locationTypeName)->where('manufacturer_id', $manufacturerID)->value('location_type_id');
    }
    public function checkErpCode($erpCode, $manufacturerID, $locationTypeId)
    {
        return DB::table('locations')->where(array('erp_code' => $erpCode, 'manufacturer_id' => $manufacturerID))->value('location_id');
    }

    public function getDownload($type){
        $xls_list = ["FG_Material_Codes","Locations","Component_Codes"];
         ob_end_clean(); //for overcome the unformated data.
         ob_start();
        if(in_array($type,$xls_list)){
            $file= public_path(). "/download/templates/".$type.".xls";
        $headers = array(
              'Content-Type: application/vnd.ms-excel',
            );
        return response()->download($file, $type.'.xls', $headers);        
        }
        else{
            $file= public_path(). "/download/templates/".$type.".csv";
        $headers = array(
              'Content-Type: application/pdf',
            );
        return response()->download($file, $type.'.csv', $headers);    
        }
        
    }

    public function deleteLocationType($locationTypeId)
    {
        $password = $this->_request->all();
        $userId = Session::get('userId');
        $verifiedUser = $this->roleRepo->verifyUser($password['password'], $userId);
        //print_r($verifiedUser);die;
        $startTime = $this->getTime();
        if($verifiedUser >= 1)
        {
            $loctype = DB::table('location_types')
                        ->where('location_type_id', '=', $locationTypeId)->update(array('is_deleted'=>1));
            $endTime = $this->getTime();            
                         DB::table('user_tracks')->insert(array('user_id'=>Session::get('userId'),'manufacturer_id'=>Session::get('customerId'),'service_name'=>'Delete Location Type','message'=>'LocationType Deleted.'.$locationTypeId,'status'=>1,'response_duration'=>($endTime - $startTime))); 
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
    public function deleteLocation($locationId)
    {
        DB::Table('locations')
        ->where('location_id', '=', $locationId)
        ->orWhere('parent_location_id','=',$locationId)
        ->update(array('is_deleted'=>1));
        return response()->json([
            'status' => true,
            'message' => 'Sucessfully deleted.'
        ]);
    }

// locations ->button functions//
    public function updateLocationType($locationTypeId)
    {
            //print_r($locationTypeId);exit;
            //echo "hai";exit;
             DB::table('location_types')
                ->where('location_type_id', $locationTypeId)
                ->update(array(
                'location_type_name' => $this->_request->get('location_type_name')
                 
                 ));
                DB::table('user_tracks')->insert(array('user_id'=>Session::get('userId'),'manufacturer_id'=>Session::get('customerId'),'service_name'=>'Edit Location Type','message'=>'LocationType Updated.'.$locationTypeId,'status'=>1));
        return response()->json([
                    'status' => true,
                    'message' => 'Sucessfully updated.'
        ]);
    }

    public function exportTo($type)
    {
         
        $data = Locations::join('location_types','location_types.location_type_id','=','locations.location_type_id')->select('location_types.location_type_name','locations.location_name','locations.location_email','locations.location_details','locations.location_address','locations.longitude','locations.latitude','locations.erp_code','locations.pincode','locations.city','locations.country','locations.phone_no','locations.parent_location_id as parentplantcode')->get()->toArray();
                //dd($data);die;
       ob_end_clean();
        ob_start();
        return Excel::create('locations_export_to_excel', function($excel) use ($data) {
            $excel->sheet('mySheet', function($sheet) use ($data)
            {
                $sheet->fromArray($data);
            });
        })->download($type);
    }
    public function saveLocationTypeFromExcel()
    {
       try {
        $data = $this->_request->all();
        //Log::info(__FUNCTION__.' === '. print_r(Input::get(),true));
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
                $locationtype_details = DB::Table('location_types')->where('manufacturer_id', $data['manufacturerID'])->get()->toArray();
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

            $path = $this->_request->file('files')->getRealPath();

            $tempArray = Excel::load($path, function($reader) {})->get()->toArray();

            $count = 0;
            $locationHeaders = array('location_type_name', 'location_name', 'location_email', 'address', 'location_details', 'state', 'region', 'longitude', 'latitude', 'sap_code', 'pincode', 'city', 'country', 'phone_number', 'parent_plant_code');
            $locationHeadersTrim = array_map('trim', $locationHeaders);

            $excelheaders = array_keys($tempArray[0]);
            Log::info("Locations headers mismatched----------------");
            Log::info(print_r(array_diff($locationHeadersTrim, $excelheaders),true));
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
                           
                            $insertLocationData[] = ['location_type_id'=>$response,'location_name'=>$locationDetails['location_name'],'location_email'=>$locationDetails['location_email'],'location_address'=>$locationDetails['address'],'location_details'=>$locationDetails['location_details'],'longitude'=>$locationDetails['longitude'],'state'=>$locationDetails['state'],'region'=>$locationDetails['region'],'longitude'=>$locationDetails['longitude'],'latitude'=>$locationDetails['latitude'],'country'=>$locationDetails['country'],'erp_code'=>$locationDetails['sap_code'],'pincode'=>$locationDetails['pincode'],'city'=>$locationDetails['city'],'country'=>$locationDetails['country'],'phone_no'=>$locationDetails['phone_number'],'manufacturer_id'=>$data['manufacturerID']]; 
                            //array_combine($locationHeadersTrim, array_map('trim', $locationDetails)); 
                        }else{
                            $message = $message . '  Plant Code already exists with '.$erpCode.'  ';
                        }
                    }
                }                
            }
            $loctypeid = DB::table('location_types')
                                ->select('location_type_id')
                                ->get()->toArray();

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
                                                ->get()->toArray();
                                                //print_r($reqdata);exit;
                    
                    $insertLocation[$key] = $reqdata[0]->location_type_name;
                    
                }
                
            }
           
            if (!empty($insertLocationData))
            {
                DB::table('locations')->insert($insertLocationData);
/*                $msg = "Successfully imported " . count($insertLocationData) . " rows. ".$message;

*/  
  return redirect('products/location')->with('status', 'Successfully added');
//return back()->with('success','Item created successfully!');
// return Redirect::to('products/location')->with('status', 'Successfully added');
//return Redirect::back()->withErrors(['success', 'Successfully added']);   
// /**/$msg="success";
       }else{
                $msg =  "Please choose a file with different Plant Codes";
            }
            $resp['msg'] = $msg;
           /* $resp['newItems'] = $insertLocationData;
            $resp['loctpname'] = $insertLocation;
*/
            //return $resp;
           // return Redirect::back()->withErrors(['success', 'Successfully added']);
           //return back();
// print_r($resp);exit;
            return $resp;
           //return back();

        }
    }
    catch(Exception $e){
        $message = $e->getMessage(); 
        Log::info($message);  
        $resp['msg'] = $message;
           /* $resp['newItems'] = "";
            $resp['loctpname'] = "";*/
            return $resp;  
    }

    }

    public function uniqueValidation()
    {
        $data = $this->_request->all();
        //print_r($data);exit;
        return $this->_esealCustomer->uniqueValidation($data);

    }


    public function getTreeLocations($id)
    {
        //echo "hai";exit;
        try{

               $id = $this->roleRepo->decodeData($id);
        if ($id == '')
        {
            $id = 1;
        }
/*
        $allowedAddLocations = $this->roleRepo->checkPermissionByFeatureCode('LOC002');
        $allowedEditLocations = $this->roleRepo->checkPermissionByFeatureCode('LOC003');
        $allowedDeleteLocations = $this->roleRepo->checkPermissionByFeatureCode('LOC004');
        $allowedEditLocationTypes = $this->roleRepo->checkPermissionByFeatureCode('LOCT003');
        $allowedDeleteLocationTypes = $this->roleRepo->checkPermissionByFeatureCode('LOCT004');*/

        $allowedAddLocations = 1;
        $allowedEditLocations = 1;
        $allowedDeleteLocations = 1;
        $allowedEditLocationTypes = 1;
        $allowedDeleteLocationTypes = 1;
        $locas = DB::Table('location_types')
                //->join('locations', 'locations.location_type_id', '=', 'location_types.location_type_id')
                //->join('eseal_customer', 'eseal_customer.customer_id', '=', 'location_types.manufacturer_id')
                ->select('location_types.location_type_name', 'location_types.location_type_id', 'location_types.manufacturer_id','location_types.is_deleted')
                ->where('location_types.manufacturer_id', $id)
                //->where('location_types.is_deleted', 0)
                ->get()->toArray();
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
                    ->where('locations.is_deleted', 0)
                    ->where('location_types.is_deleted',0)
                    ->get()->toArray();
            
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
                    ->get()->toArray();
                
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
                        . '<a data-href="/products/editlocation/' . $valu['location_id'] . '" data-toggle="modal" onclick="getLocationName(' . $valu['location_id'] . ');" data-target = "#basicvalCodeModal1" >'
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
                        . '<a data-href="/products/editlocation/' . $subloc['location_id'] . '" data-toggle="modal" onclick="getLocationName(' . $subloc['location_id'] . ');" data-target = "#basicvalCodeModal1" >'
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
                        . '<a data-href="/products/editlocationtype/' . $valus['location_type_id'] . '" data-toggle="modal" data-target = "#location_types_edit" >'
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


    public function editLocationType($locationTypeId)
    {
        $cuser = DB::table('location_types')->where('location_type_id', '=', $locationTypeId)->first();
        return response()->json($cuser);
    }

    
      public function restoreLocationType($locationTypeId)
    {
       // echo "hai".$locationTypeId;exit;
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
            return  response()->json([
                    'status' => true,
                    'message' => 'Sucessfully restored Location Type and its locations.'
        ]);
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
                return response()->json([ 'status' => FALSE, 'message' => $validator->messages()]);
            }else{
                if(isset($locationTypeData['manufacturer_id']))
                {
                    $locationTypeData['manufacturer_id'] = $this->roleRepo->decodeData($locationTypeData['manufacturer_id']);
                }
                $result = $this->_esealCustomer->addLocationTypes($locationTypeData);
                //dd($result);
                 DB::table('user_tracks')->insert(array('user_id'=>Session::get('userId'),'manufacturer_id'=>$locationTypeData['manufacturer_id'],'service_name'=>'Add Location Type','message'=>'LocationType Added.'.json_encode($data),'status'=>1));
                if($result)
            {
                return response()->json(['status' => true, 'message' => 'Sucessfully added.', 'location_type_id' => $result ]);
            }else{
                return response()->json(['status' => true, 'message' => $result, 'location_type_id' => 0 ]);
            }
            }
            return response()->json(['status' => true, 'message' => 'Unable to add location types.' ]);
        }
        $result = $this->_esealCustomer->addLocationTypes($data);
        if($result)
        {    
            DB::table('user_tracks')->insert(array('user_id'=>Session::get('userId'),'manufacturer_id'=>Session::get('customerId'),'service_name'=>'Add Location Type','message'=>'LocationType Added.'.json_encode($data),'status'=>1));
            return response()->json(['status' => true, 'message' => 'Sucessfully added.', 'location_type_id' => $result ]);
        }else{
            return response()->json(['status' => true, 'message' => $result, 'location_type_id' => 0 ]);
        }
    }
    public function saveLocation()
    {
        $stateName = DB::Table('zone')->select('name')->where('zone_id',Session::get('state'))->get();
        // echo "hai";exit;
        $source_page = Session::get('source_page');
        $locationId = DB::Table('locations')->insertGetId([
            'location_name'=>$this->_request->get('location_name'),
            'manufacturer_id'=>$this->_request->get('manufacturer_id'),
            'parent_location_id'=>$this->_request->get('parent_location_id'), 
            'location_type_id'=>$this->_request->get('location_type_id'),
            'location_email'=>$this->_request->get('location_email'),
            'location_details'=>$this->_request->get('location_details'),
            'location_address'=>$this->_request->get('location_address'),
            'region'=>$this->_request->get('region'),
            'country'=>$this->_request->get('country'),
            'state'=>$this->_request->get('state'),
            'longitude'=>$this->_request->get('longitude'),
            'latitude'=>$this->_request->get('latitude'),
            'erp_code'=>$this->_request->get('erp_code'),
            'business_unit_id'=>$this->_request->get('business_unit_id'),
            'storage_location_type_code'=>$this->_request->get('storage_location_type_code'),
            'created_date'=>date('Y-m-d H-i-s'),
            'created_by'=>$this->_request->get('userId')
        ]);
        // echo "<pre/>";print_r($locationId);exit;
        DB::table('user_tracks')->insert(array('user_id'=>Session::get('userId'),'manufacturer_id'=>Session::get('customerId'),'status'=>1,'message'=>'Location Added.'.$locationId,'service_name'=>'Location Add'));
        if($source_page == 'product')
        {
            return response()->json([
                'status' => true,
                'message' => 'Sucessfully added.',
                'location_id' => $locationId
            ]); 
        }
        return response()->json([
                    'status' => true,
                    'message' => 'Sucessfully added.'
        ]);
    }

    public function editTransMain()
    {
	if(Session::has('userId')==''||Session::has('userId')==0){
            return Redirect::to('/login');
        }
        //$customerId = $this->roleRepo->decodeData($customerId);
        $customerId=$this->_manufacturerId;
        //echo ($customerId);exit;
        parent::Breadcrumbs(array('Home'=>'/','Edit Transaction'=>'#')); 

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
        $customerstoragelocations = DB::table('master_lookup')->join('lookup_categories','lookup_categories.id','=','master_lookup.category_id')->select('master_lookup.name', 'master_lookup.value')->get()->toArray();
        //print_r($customerstoragelocations);exit;
        $customerbusinessid = DB::table('business_units')->select('business_unit_id','name')->where('manufacturer_id',$customerId)->get()->toArray();
        $custstates = $this->getStates(99);
        //dd($customerAddress);
        //echo "<pre>";print_r($customerstoragelocations);die;
        //echo "<pre>";print_r($customerbusinessid);die;
        return View::make('products/transactions')->with(array('permissions' => $permissions, 'formData' => $formData, 'customerDetails' => $customerDetails, 'customerAddressData' => $customerAddress, 'erp_details' => $customerErpConfiguration, 'customer_locations' => $customerlocations,'customer_storage_locations'=>$customerstoragelocations,'custstates'=>$custstates,'customer_id' => $this->roleRepo->encodeData($customerId)))
                            ->with('customerstoragelocations',$customerstoragelocations)
                            ->with('customerbusinessid',$customerbusinessid )
                            ->with('allow_buttons', ['add_product' => $allowAddProduct, 'import_product_csv' => $allowImportCsv, 'import_product_component_csv' => $allowImportComponentCsv, 'import_product_erp' => $allowImportErp, 'add_locationtypes' => $allowAddLocationsTypes, 'import_locationtypes_csv' => $allowLocationsTypesImportCsv, 'import_locationtypes_erp' => $allowLocationsTypesImportErp]);
    }

public function viewTransaction()
    {
        return View::make('customers.transaction');
    }
    public function getTransaction($manufacturerId)
    {
        $manufacturerId = $this->_manufacturerId;
        //echo "bb".$manufacturerId;exit;
        $trans = DB::Table('transaction_master')->where('manufacturer_id', $manufacturerId)->get();
        $finalTransactionArr = array();
        $transaction = array(); 
        $transtype_details = json_decode(json_encode($trans), true);
        foreach($transtype_details as $values)
        {           
            
            $transaction['id'] = $values['id'];
            $transaction['name'] = $values['name'];
            $transaction['description'] = $values['description'];
            $transaction['action_code'] = $values['action_code'];
            $transaction['srcLoc_action'] = $values['srcLoc_action'];
            $transaction['dstLoc_action'] = $values['dstLoc_action'];
            $transaction['intrn_action'] = $values['intrn_action'];
            $transaction['feature_code'] = $values['feature_code'];
            $transaction['actions'] = '<span style="padding-left:5px;"><a data-href="/products/edittransaction/'.$values['id'].'" data-toggle="modal" data-target = "#TransactionEditModal" ><span class="badge bg-light-blue"><i class="fa fa-pencil"></i></span></a></span><span style="padding-left:10px;" ><a onclick = "deleteTransaction(' . $values['id'].')"><span class="badge bg-red"><i class="fa fa-trash-o"></i></span></a></span>';
            $finalTransactionArr[] = $transaction;
        }
            
            return json_encode($finalTransactionArr);
    }
    
    protected function validateTransaction($data)
    {
        $validator = Validator::make(
            array(
                'name' =>$this->_request->get('name'),
                'action_code' => $this->_request->get('action_code'),
                'srcLoc_action' => $this->_request->get('srcLoc_action'),
                'dstLoc_action' =>$this->_request->get('dstLoc_action'),
                'intrn_action' => $this->_request->get('intrn_action'),
                'manufacturer_id' => $this->_request->get('manufacturer_id'),
                'feature_code' => $this->_request->get('feature_code')
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
        $validate = $this->validateTransaction($this->_request->all());
        if($validate != 1)
        {
            return response()->json([
                'status' => false,
                'message' => [$validate]
            ]);
        }
        DB::table('transaction_master')->insert(array(
            'name' => $this->_request->get('name'),   
            'description' => $this->_request->get('description'),
            'action_code' => $this->_request->get('action_code'),
            'srcLoc_action' => $this->_request->get('srcLoc_action'),
            'dstLoc_action' => $this->_request->get('dstLoc_action'),
            'intrn_action' => $this->_request->get('intrn_action'),
            'manufacturer_id'=>$this->_request->get('manufacturer_id'), 
            'feature_code' => $this->_request->get('feature_code'),
            'group' => $this->_request->get('group')
        ));
        return response()->json([
                    'status' => true,
                    'message' => 'Sucessfully added.'
        ]);
    }
    public function editTransaction($id)
    {
        $transuser = DB::table('transaction_master')->where('id', '=', $id)->first();
        return response()->json($transuser);
    }
    public function updateTransaction($id)
    {
        $validate = $this->validateTransaction(Input::all());
        //return $validate;
        if($validate != 1)
        {
            return response()->json([
                'status' => false,
                'message' => [$validate]
            ]);
        }
        // echo"hai";exit;
        DB::table('transaction_master')
                ->where('id', $id)
                ->update(array(
                    'name' => $this->_request->get('name'), 
                    'description' => $this->_request->get('description'),
                    'action_code' => $this->_request->get('action_code'),
                    'srcLoc_action' => $this->_request->get('srcLoc_action'),
                    'dstLoc_action' => $this->_request->get('dstLoc_action'),
                    'intrn_action' => $this->_request->get('intrn_action'),
                    'feature_code' => $this->_request->get('feature_code'),
                    'group' => $this->_request->get('group')
                ));
        return response()->json([
                    'status' => true,
                    'message' => 'Sucessfully updated.'
        ]);
    }
    public function deleteTransaction($id)
    {
        DB::table('transaction_master')->where('id', '=', $id)->delete();
        //return Redirect::to('/customer/onboard');
        return response()->json([
                    'status' => true,
                    'message' => 'Sucessfully Deleted.'
        ]);
    }
    public function mapProduct(){
        
        $data=$this->_request->all();
        $manuID=$this->_manufacturerId;
         // echo "<pre>"; echo $manuID;exit;
        $errors=[];
        $insert=0;
        $product_ids=DB::table('products')->where('manufacturer_id',$manuID)->whereIn('product_id',$data['prod_id'])->get(['product_id'])->toArray();
        $location_ids=DB::table('locations')->whereIn('location_id',$data['loca_id'])->get(['location_id'])->toArray();
        
            if(!isset($data['prod_id']) || $data['prod_id']=='')
                $errors[]='Please Select Product';

            if(!isset($data['loca_id']) || $data['loca_id']=='')
                $errors[]='Please Select vendor';
            // echo"hai-1".count($errors);exit;
            // if(count($errors)==0){
                // echo "hai";exit;
                //throw new Exception("few mandatory fields are missing", 1);
            if((count($data['prod_id'])!=count($product_ids)))
            {
                // echo"hai";exit;
                $errors[]='Few products are unavailable in database';
            }
            else{
                // echo "true";exit;
                foreach($product_ids as $p_id){
                    foreach($location_ids as $l_id){
                    $C=DB::table('product_locations')->where('product_id',$p_id->product_id)->where('location_id',$l_id->location_id)->get()->toArray();
                    // echo count($C);exit;
                        if(count($C)==0){
                            $insert=DB::table('product_locations')->insert([
                        'product_id' => $p_id->product_id,   
                        'location_id' =>$l_id->location_id] );
                        }
                        else{
                            $errors[]='Products already mapped to location';
                        }
                    } 
                }
            }
           
            // }
            // else{
    //     $check=$this->validate($this->_request,['prod_id'=>'required',
    // 'loca_id'=>'required']);
       
    // }
    // echo $insert;exit;
    if($insert){
        return redirect('https://'.$_SERVER['HTTP_HOST'].'/products/product_location_mapping?result=1|Mapped Successfully');
    }
    return redirect('https://'.$_SERVER['HTTP_HOST'].'/products/product_location_mapping?result=0|'.implode('|',$errors));

    }
        // return back()->with('success','Mapped Successfully');
        // }

    // else{
    //       return back()->with('error','Mapping failed');

    // }
        // return response()->json([
        //             'status' => true,
        //             'message' => 'Sucessfully added.'
        // ]);

}
