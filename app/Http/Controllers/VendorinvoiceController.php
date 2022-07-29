<?php 
set_time_limit(0);
ini_set('memory_limit', '-1');
use Central\Repositories\CustomerRepo;
use Central\Repositories\RoleRepo;
date_default_timezone_set("Asia/Kolkata");


//use Carbon;

class VendorinvoiceController extends BaseController{

    protected $CustomerObj;
    
    protected $roleAccessObj;
    protected $roleid;
            
     public function __construct()
    { 
        $product = new Products\Products();
        $productattr = new Products\ProductAttributes();
        $this->_product = $product;
        $this->_productattr = $productattr;
        $this->roleRepo = new RoleRepo;
        $this->_manufacturerId = $this->_product->getManufacturerId();
    }
    public function number_format($number)
    {
        setlocale(LC_MONETARY,"en_IN");
        $temp = money_format("%i",$number);
        return str_replace("INR ", "", $temp);
    }
    public function index()
    {
         $manufacturerId = Session::get('customerId');
        //echo 'manufacturerId => '.$manufacturerId;die;
        parent::Breadcrumbs(array('Home'=>'/','Invoice Details '=>'#'));

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
             $getLocId = DB::table('users')->where('user_id',$userType)->pluck('location_id');
              $getPrd = DB::table('products')->where('product_type_id','=',8003)->where('manufacturer_id',$manufacturerId)->select('name','product_id')->get();

    return View::make('vendorinvoice/index')
                        ->with('manu', $manu)
                        ->with('products',$getPrd)
                        ->with('manufacturerData', $manufactuerArray)
                        ->with('custType',$custType);
     }

public function importusermanual()
    {
        parent::Breadcrumbs(array('Home'=>'/','Index'=>'#')); 

       //$view = 'http://vguard.esealinc.com:555/public/download/IMPORT_USER_MANUAL.pdf';
//dd($view);
        return View::make('vendorinvoice.importusermanual');  //->with(array('results' => $results));
    }


    public function export_excel(){
    $userId = Session::get('userId');
     $getLocId = DB::table('users')->where('user_id',$userId)->pluck('location_id');

        $data = "select po_number,invoice_no,invoice_date,bill_no,bill_date,material_code,name as prdname,qty as qty,location_name from vendor_invoice_master vim join vendor_invoice_details vid
on vid.invoice_id = vim.id
join products p on p.product_id=vid.product_id join locations as l on l.location_id = vim.location_id where vim.location_id =".$getLocId;
    $data = DB::select($data);
      Excel::create('Invoice Report', function($excel) use($data) {
          return $excel->sheet('New sheet', function($sheet) use($data){
            $sheet->loadView('vendorinvoice.export_excel',array('data'=>$data));
          });

      })->export('csv');
    }
  public function add()
    {
    parent::Breadcrumbs(array('Home'=>'/','Vendor Invoice'=>'vendorinvoice/index','VendorInvoice'=>'#')); 
    $custId=Session::get('customerId');
    $userId = Session::get('userId');
    //dd($userId);die;
     $getLocId = DB::table('users')->where('user_id',$userId)->pluck('location_id');
    
        $Tolocations = DB::table('locations as l')->join('location_types as lt','lt.location_type_id','=','l.location_type_id')->where('l.manufacturer_id',$custId)->whereIn('lt.location_type_name',array('Retailer'))
       ->select('location_id','location_name')->get();
     //$getType = DB::table('master_lookup')->where('value','like','3000%')->select('description as name','value')->get();
     $getPrd = DB::table('products as p')->join('product_locations as pl','pl.product_id','=','p.product_id')->where('location_id',$getLocId)->where('product_type_id','=',8003)->select('name','p.product_id as product_id')->get();
     //dd($getType);die;
 //$getPrd = DB::table('products')->where('product_type_id','=',8003)->where('manufacturer_id',$custId)->select('name','product_id')->get();
    return View::make('vendorinvoice/add')->with(array('tolocations'=>$Tolocations,'products'=>$getPrd,'locId'=>$getLocId));      
    }
   public function editDeliveryDetails($delivery_id,$product_id)
    {

      $products = DB::table('delivery_details')->where('product_id',$product_id)->where('ref_id',$delivery_id)->select('product_id','qty','ref_id as delivery_id')->first();
          
    return Response::json($products);

    }
public function invoiceNumberUniquevalidation($po_no,$invoice_no){
    //$data = Input::all();
    //dd($data);

   
             $checkInvoice = DB::table('vendor_invoice_master')->where('po_number',$po_no)->where('invoice_no',$invoice_no)->pluck('po_number');
             if($checkInvoice){
                goto jump;
             }
               // return Redirect::to('delivery/index');
        return Response::json([
                    'status' => true,
                    'message' => 'Sucessfully Added.'
        ]); 
        jump:
         return Response::json([
                    'status' => false,
                    'message' => 'Already Exists'
        ]); 
   

}


    public function save()
    {
       $data = Input::all();
        //dd($data);die;
     Log::info(__FUNCTION__.' : '.print_r(Input::get(),true)); 

        $custId=Session::get('customerId');
        unset($data['_token']);
        //$docid = date("Ymdhis");
        $userId = Session::get('userId');
        $date = date("Y-m-d H:i:s");
        //dd($data);die; 
        $userId = Session::get('userId');
    //dd($userId);die;
     $getLocId = DB::table('users')->where('user_id',$userId)->pluck('location_id');
    log::info($getLocId);
    //$getLocId = explode(',',$getLocId);
        $getInvoiceId = DB::table('vendor_invoice_master')->insertGetId(['po_number'=>$data['po_number'],'invoice_no'=>$data['invoice_number'],'invoice_date'=>$data['invoice_date'],'bill_no'=>$data['bill_number'],'bill_date'=>$data['bill_date'],'location_id'=>$getLocId,'user_id'=>$userId]);
        
        //dd($getDeliveryId);die;
         $line = 1;
         //dd($data['data']);die;
         if(isset($data['data'])){
        foreach($data['data'] as $insert){
          $insert = json_decode($insert);
         // dd($insert->product_id);die;
           $validator = \Validator::make(
                                    array(
                                'product_id' => isset($insert->product_id) ? $insert->product_id : '',
                                'qty' => isset($insert->qty_id) ? $insert->qty_id : ''
                                    ), array(
                                'product_id' => 'required',
                                'qty' => 'required'
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
                        return Response::json([
                                'status' => false,
                                'message' => $errorMessage
                    ]);
                    }    
          $insertTo = DB::table('vendor_invoice_details')->insert(['invoice_id'=>$getInvoiceId,'product_id'=>$insert->product_id,'qty'=>$insert->qty_id]);
          
        }
       }
       else{
         $validator = \Validator::make(
                                    array(
                                'product_id' => isset($data['item']) ? $data['item'] : '',
                                'qty' => isset($data['qty']) ? $data['qty'] : ''
                                    ), array(
                                'product_id' => 'required',
                                'qty' => 'required'
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
                        return Response::json([
                                'status' => false,
                                'message' => $errorMessage
                    ]);
                    }    
            $insertTo = DB::table('vendor_invoice_details')->insert(['invoice_id'=>$getInvoiceId,'product_id'=>$data['item'],'qty'=>$data['qty']]);
       }
       log::info("endif");

      
       // return Redirect::to('delivery/index');
        return Response::json([
                    'status' => true,
                    'message' => 'Sucessfully Added.'
        ]); 
        // jump:
        //  return Response::json([
        //             'status' => true,
        //             'message' => 'Already Exists'
        // ]); 

    }
      public function updateDeliveryDetails($delivery_id,$product_id)
    {
        $data=Input::all();
        //dd($data);die;
                             $validator = \Validator::make(
                                    array(
                                'product_id' => isset($data['product_id']) ? $data['product_id'] : '',
                                'qty' => isset($data['qty']) ? $data['qty'] : ''
                                    ), array(
                                'product_id' => 'required',
                                'qty' => 'required'
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
                        return Response::json([
                                'status' => false,
                                'message' => $errorMessage
                    ]);
                    }

        DB::table('delivery_details')
                ->where('product_id', $product_id)
                ->where('ref_id',$delivery_id)
                ->update(array(
                    'qty' => Input::get('qty')));
        
        return Response::json([
                    'status' => true,
                    'message' => 'Sucessfully updated.'
        ]);
    }

    public function delwithProduct($Id,$product_id)
    {
      DB::table('delivery_details')->where('ref_id',$Id)->where('product_id',$product_id)->delete();
        return 1;   

    }
   
    public function getElementdata(){ 
        try
        {
           
             $userId = Session::get('userId');
             //dd($userId);
             $getLocId = DB::table('users')->where('user_id',$userId)->pluck('location_id');
           $getVendorinvoice = DB::table('vendor_invoice_master as vim')->join('locations as l','l.location_id','=','vim.location_id')->select('po_number','invoice_no as invoice_number','invoice_date','bill_date','bill_no as bill_number','id')
                    ->get();
            //echo "<pre>";print_r($getDeliveryMaster);         
            $agarr = array();
            $prodarr = array();
            //dd($getVendorinvoice);die;
            $ags = json_decode(json_encode($getVendorinvoice), true);            
            $InvoicelDetails = DB::table('vendor_invoice_master as vim')
                        ->Join('vendor_invoice_details as vid', 'vid.invoice_id', '=', 'vim.id')
                        ->join('products as p','p.product_id','=','vid.product_id')
                        ->select(DB::raw("p.name as product_name,vid.qty as qty,id"))
                       ->whereIn('vid.invoice_id',array_column($ags, 'id'))
                        ->get();
             $finalarr=[];
             $finalarr['masterData'] = $ags;
            $finalarr['detailsData'] = json_decode(json_encode($InvoicelDetails),true);
//            dd($finalarr);die;
            return $finalarr;
        } catch (\ErrorException $ex) {
            return json_encode($ex->getMessage());
        }
    }

}
