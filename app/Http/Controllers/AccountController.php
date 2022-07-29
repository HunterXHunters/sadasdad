<?php 
set_time_limit(0);
ini_set('memory_limit', '-1');
use Central\Repositories\CustomerRepo;
use Central\Repositories\RoleRepo;
date_default_timezone_set("Asia/Calcutta"); 
header('Access-Control-Allow-Origin: *'); 
header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');

class AccountController extends BaseController{

    protected $CustomerObj;
    protected $roleAccessObj;
    protected $roleid;
    protected $_labelorder;

    private function getTime(){
    $time = microtime();
    $time = explode(' ', $time);
    $time = ($time[1] + $time[0]);
    return $time;
  }
   public function getDate(){
        return date("Y-m-d H:i:s");
    }

            
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
        $this->_labelorder = new LabelOrders\Labelorder();
    }

    public function index(){

      $manufacturerId = Session::get('customerId');
      $addAttributesets = $this->roleRepo->checkPermissionByFeatureCode('ATTG002');
      $addAttributegroups = $this->roleRepo->checkPermissionByFeatureCode('ATG001');
      $addlabelaccess = $this->roleRepo->checkPermissionByFeatureCode('ALB001');
      //$addlabelaccess =1;
      $data = Input::all();
      $postMethod = 0;
      if(!empty($data)){
        $manufacturerId = Input::get('manufacturer_id');
        $postMethod = 1;
      }
        parent::Breadcrumbs(array('Home' => '/', 'Label Orders' => '#'));

       // echo "<pre/>";print_r($manufacturerId);exit;
        if($manufacturerId)
        {
          // get the locations based on manufacturer
        $vendordata = $this->_labelorder->getlocationsByManufacturer($manufacturerId);
        }else{
          $vendordata = $this->_labelorder->getalllocations();
        }

        // get all ponumbers
        $data = $this->_labelorder->getponumbers();
/*
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
        }*/
        $userType = Session::get('userId');
        
        // get customer type id based on userid
        $custType = $this->_labelorder->getcustomertypes($userType);

        
        /*$custType=DB::table('users')
                ->join('eseal_customer','users.customer_id','=','eseal_customer.customer_id')
                ->join('master_lookup','master_lookup.value','=','eseal_customer.customer_type_id')
                ->where('users.customer_type','!=',7001)
                ->where('user_id',$userType)
                ->select('eseal_customer.customer_type_id')
                ->get();
        */
        //  $getUser =$this->_labelorder->getUserByusertype($userType);
           /*$getUser = DB::table('users')
                      ->where('user_id',$userType)
                      ->select('customer')
                      ->get();*/
            //dd(json_encode($getUser[0]));
        /* $custType=json_encode($custType);*/
        $manufactuerArray = array();
        if(!empty($vendordata))
        {
            foreach($vendordata as $manufacturer)
            {
                $manufactuerArray[$manufacturer->customer_id] = $manufacturer->brand_name;
            }
        }

      $labelswithQty = $this->_labelorder->getlableswithqty();

      //echo "<pre/>";print_r($labelswithQty);exit;
      $labelsNqty = $this->_labelorder->getlableschilddata();        
      $getStatus = $this->_labelorder->getlabelstatus();
      $getLabelTypes = $this->_labelorder->getlabeltypes();
      $getvendorbymanf = DB::table('locations as l')
                        ->join('location_types as lt','lt.location_type_id','=','l.location_type_id')
                        ->join('account_po as ap','ap.vendor_id','=','l.location_id')
                        ->where('location_type_name','=','Vendor')
                        ->where('ap.is_download','=',0)
                        ->where('ap.status','>=',51001)
                        ->select('l.location_name','location_id');
      
      if(Session::get('userName') == "superadmin"){
        $getvendorbymanf = $getvendorbymanf->get();
        $addlabelaccess=1;
      }else{
        $getvendorbymanf = $getvendorbymanf->where('l.manufacturer_id',$manufacturerId)->get();
      }
        if($postMethod)
        {
            return json_encode($attrsets);
        }else{
      //echo "<pre/>";print_r($vendordata);exit;
    return View::make('account.index')
                        ->with('vendor', $vendordata)
                        ->with('data', json_encode($data))
                        ->with('labelswithQty', $labelswithQty)
                        ->with('labelsNqty', $labelsNqty)
                        //->with('manu', $manu)
                        //->with('getUser', $getUser)
                        ->with('manufacturerData', $manufactuerArray)
                        ->with('custType',$custType) 
                        ->with('getStatus',$getStatus)
                        ->with('labels',$getLabelTypes)
                        ->with('addlabelaccess',$addlabelaccess)
                        ->with('getvendorbymanf',$getvendorbymanf);  
        }
              
    } 
          
     public function add(){
         $manufacturerId = Session::get('customerId');

        $addlabelaccess = $this->roleRepo->checkPermissionByFeatureCode('ALB001');
        parent::Breadcrumbs(array('Home'=>'/','Create Po '=>'#'));

        $getVendor = DB::table('locations as l')->join('location_types as lt','lt.location_type_id','=','l.location_type_id')->where('location_type_name','=','Vendor')
                      //->where('l.manufacturer_id',$manufacturerId)
                      ->select('l.location_name','location_id');
                      //->get();

      if(Session::get('userName') == "superadmin"){
        $getVendor = $getVendor->get();
        $addlabelaccess =1;
      }else{
        $getVendor = $getVendor->where('l.manufacturer_id',$manufacturerId)->get();
      }

        $getLabelTypes = DB::table('account_labletype_config')->select(DB::raw('(concat(label_type_name," (",codes_qty,"-Code For Sheet)","-size ",codes_size)) as label_type_name'),'id')->get();              
       
         //dd($products);die;
        return View::make('account/add')->with(array('vendor'=>$getVendor,'labels'=>$getLabelTypes,'addlabelaccess'=>$addlabelaccess));
         
    }
     public function save(){
      $manufacturerId = Session::get('customerId');
      $data = Input::all();

     //  echo "<pre/>";print_r($data);exit;
     $date = $this->getdate();

     $checkponumber = $this->_labelorder->checkponumber($data);
     $s3file = "";
     if($data['file_documnet']){
      $destinationPath = public_path().'/uploads/po_uploads'; 
          $extension = Input::file('file_documnet')->getClientOriginalExtension(); 
          $fileName = time('Y-m-d').'.'.$extension; 
          Input::file('file_documnet')->move($destinationPath, $fileName);
            $s3 = new s3\S3();
           $s3file=$s3->uploadFile(public_path().$destinationPath.$fileName,'upload_po_file');
      }

      //echo $s3file;exit;

     if(count($checkponumber) == 0){
        $poInsert = DB::table('account_po')->insert(['master_po'=>0,'po_number'=>$data['po_number'],'current_date'=>$date,'vendor_id'=>$data['vendor_id'],'po_document'=>$s3file]); 

        $statusInsert = DB::table('account_status_details')->insert(['po_id'=>$data['po_number'],'status_id'=>51001,'created_by'=>Session::get('userId')]); 
     }

     foreach($data['data'] as $insert){

        $jsonData = json_decode($insert);

        $getCodesQty  = DB::table('account_labletype_config')->where('id',$jsonData->label_id)->pluck('codes_qty');

        if($getCodesQty){
            $total = $getCodesQty * $jsonData->qty_id;
        }

        $labelInsert = DB::table('account_labels')->insert(['po_number'=>$data['po_number'],'label_id'=>$jsonData->label_id,'sheet_qty'=>$jsonData->qty_id,'codes_qty'=>$total]);

     }
     

     /*  $getMasterPo = DB::table('wallet_po')->orderby('po_id','desc')->pluck('po_number');
       if(!empty($data)){
        $poInsert = DB::table('account_po')->insert(['master_po'=>$getMasterPo,'po_number'=>$data['po_number'],'current_date'=>$date,'vendor_id'=>$data['vendor_id']]);
        foreach($data['data'] as $insert){
             $jsonData = json_decode($insert);
             $getCodesQty  = DB::table('account_labletype_config')->where('id',$jsonData->label_id)->pluck('codes_qty');
             if($getCodesQty){
                $total = $getCodesQty * $jsonData->qty_id;
             } 
            $labelInsert = DB::table('account_labels')->insert(['po_number'=>$data['po_number'],'label_id'=>$jsonData->label_id,'sheet_qty'=>$jsonData->qty_id,'codes_qty'=>$total]);

        }
        $statusInsert = DB::table('account_status_details')->insert(['po_id'=>$data['po_number'],'status_id'=>51001]);

       }*/
        return Redirect::to('account/index');
         
    }

    public function getElementData_old()
    {   
       $manufacturerId = Session::get('customerId');
      // dd($manufacturerId);
       if(empty($manufacturerId)){
       $manufacturerId = $this->_manufacturerId = $this->_product->getManufacturerId();
     }
        try
        {
            $ag1 = DB::table('account_po as ap')
                   ->join('locations as l','l.location_id','=','ap.vendor_id')
                   ->join('master_lookup as ml','ml.value','=','ap.status')
                    ->select(DB::raw("ap.po_number as po_number,vendor_id,l.location_name,ap.current_date as date,ml.name as status,CONCAT('<a data-href=\"/account/getLabeldetailsForPO/',po_number,'\" data-toggle=\"modal\"  data-target=\"#basicvalCodeModal2\" onclick=\"getPonumwithIds(',po_number,');\"><span class=\"badge bg-grey\"><i class=\"fa fa-plus\"></i></span></a><span style=\"padding-left:10px;\" ></span><a data-href=\"/account/changeStatus/',status,'/',po_number,'\" data-toggle=\"modal\"  data-target=\"#basicvalCodeModal\" onclick=\"getPonum(',po_number,');\"><span class=\"badge bg-grey\"><i class=\"fa fa-pencil\"></i></span></a><span style=\"padding-left:10px;\" ></span>') as actions"))
                    ->where('l.manufacturer_id',$manufacturerId)
                    //->where('product_type_id','=', 8003)
                    ->get();
            //echo "<pre>";print_r($ag1);         
            $agarr = array();
            $prodarr = array();
            //return $ag1;
            $ags = json_decode(json_encode($ag1), true);
        //dd($ags[0]['product_id']);die;
            $insert=array();
            foreach($ags as $ag){
                $insert[]=$ag['po_number'];
            }
            //dd(implode(',',$insert));die;
            
               $attr = DB::select("select account_labels.sheet_qty,
                (concat(label_type_name,' (',account_labletype_config.codes_qty,'-Code For Sheet)','-size ',codes_size)) as label_type_name,po_number,CONCAT('<a data-href=\"/account/editLabelDetails/',account_labels.label_id,'/',po_number,'\" data-toggle=\"modal\" data-target=\"#basicvalCodeModal1\"><span class=\"badge bg-light-blue\"><i class=\"fa fa-pencil\"></i></span></a><span style=\"padding-left:10px;\" ></span> <a 
                            data-href=\"javascript:void(0)\" onclick = \"delLabelwithPo(',account_labels.label_id,',',account_labels.po_number,')\"><span class=\"badge bg-red\"><i class=\"fa fa-trash-o\"></i></span></a><span style=\"padding-left:10px;\" ></span>') as actions from account_labels join account_labletype_config on account_labletype_config.id = account_labels.label_id where account_labels.po_number in (".implode(',',$insert).")");         
             $finalarr=[];
             $finalarr['ponumbers'] = $ags;
            $finalarr['labels'] = json_decode(json_encode($attr),true);
            //print_r($finalarr);die;
            return $finalarr;
        } catch (\ErrorException $ex) {
            return json_encode($ex->getMessage());
        }
    }

public function getElementData()
    {   

       $manufacturerId = Session::get('customerId');
    if(empty($manufacturerId)){
       $manufacturerId = $this->_manufacturerId = $this->_product->getManufacturerId();
     }
     $getUser = Session::get('userId');

     $username = Session::get('userName');
 
     // get the location id
     $getLocId =$this->_labelorder->getlocationid($getUser);
     //dd($getLocId);
     $manufacturerId=5;

     $crudopAccess = $this->roleRepo->checkPermissionByFeatureCode('LBO001');

     return $this->_labelorder->LoadElementData($crudopAccess,$manufacturerId,$username,$getLocId);

     
    }


     public function delLabelwithPo($label_id,$po_number)
    {   
        //dd($attribute_id);
    if($label_id && $po_number){    
    DB::table('account_labels')->where('po_number',$po_number)->where('label_id',$label_id)->delete();
    }             
    return 1;
    }

 public function editLabelDetails($label_id,$po_number)
    {

      $labelswithQty = DB::table('account_labels as al')->join('account_labletype_config as alc','alc.id','=','al.label_id')->where('label_id',$label_id)->where('po_number',$po_number)->select(DB::raw('(concat(label_type_name," (",alc.codes_qty,"-Code For Sheet)","-size ",codes_size)) as name'),'label_id','sheet_qty','po_number')->first();
          
    return Response::json($labelswithQty);

    }
    public function changeStatus($status_id,$po_number)
    {

      $getStatus = DB::table('master_lookup as ml')->join('account_po as ap','ap.status','=','ml.value')->where('ml.value','like','%5100%')->where('status',$status_id)->where('po_number',$po_number)->select('po_number','status as status_id','ml.name as name')->first(); 
      //dd($getStatus);
          
    return Response::json($getStatus);

    }

  public function updateLabelDetails($label_id,$po_number)
    {
        $data=Input::all();
        //dd($data);die;
                             $validator = \Validator::make(
                                    array(
                                'label_id' => isset($data['label_id']) ? $data['label_id'] : '',
                                'sheet_qty' => isset($data['qty']) ? $data['qty'] : ''
                                    ), array(
                                'label_id' => 'required',
                                'sheet_qty' => 'required'
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
                    $sheet_qty = Input::get('qty');
  $getCodesQty  = DB::table('account_labletype_config')->where('id',$label_id)->pluck('codes_qty');
             if($getCodesQty){
                $total = $getCodesQty * $sheet_qty;
             } 
        DB::table('account_labels')
                ->where('label_id', $label_id)
                ->where('po_number',$po_number)
                ->update(array(
                    'sheet_qty' => $sheet_qty,
                     'codes_qty'=>$total));
        
        return Response::json([
                    'status' => true,
                    'message' => 'Sucessfully updated.'
        ]);
    }
public function saveStatus($status_id,$po_number)
    {
        $data=Input::all();
        $date = $this->getdate();
              $validator = \Validator::make(
                        array(
                        'status_id' => isset($data['status_id']) ? $data['status_id'] : ''
                        ), array(
                        'status_id' => 'required'
              ));
              if($validator->fails()){
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
              $sessiondetails = Session::all();

        $s3file="";
        if($data['url']){
        $destinationPath = public_path().'/uploads/po_uploads'; 
        $extension = Input::file('url')->getClientOriginalExtension(); 
        $fileName = time('Y-m-d').'.'.$extension; 
        Input::file('url')->move($destinationPath, $fileName);
            $s3 = new s3\S3();
           $s3file=$s3->uploadFile(public_path().$destinationPath.$fileName,'upload_po_file');
        }

         $invoice_image="";
        if($data['invoice_image']){
        $destinationPath = public_path().'/uploads/po_uploads'; 
        $extension = Input::file('invoice_image')->getClientOriginalExtension(); 
        $fileName = time('Y-m-d').'.'.$extension; 
        Input::file('invoice_image')->move($destinationPath, $fileName);
            $s3 = new s3\S3();
           $invoice_image=$s3->uploadFile(public_path().$destinationPath.$fileName,'upload_po_file');
        }

        $userId = Session::get('userId');

        // update the po status details
        $postatusdetails =$this->_labelorder->updatepostatusdetails($status_id,$po_number,$data,$date,$sessiondetails['userName'],$s3file,$invoice_image,$userId);
        return $postatusdetails;
    }

  public function getLabeldetailsForPO($po_number)
    {
        $getLabelDetails=DB::table('account_po')
                            ->join('account_labels','account_labels.po_number','=','account_po.po_number')
                            //->join('locations','locations.location_id','=','account_po.vendor_id')
                            ->join('account_labletype_config','account_labletype_config.id','=','account_labels.label_id')
                            ->where('account_po.po_number',$po_number)->get(array(DB::raw('(concat(label_type_name," (",account_labletype_config.codes_qty,"-Code For Sheet)","-size ",codes_size)) as label_type'),'account_labels.label_id as label_id','account_labels.sheet_qty as qty','current_date as date'));
        return $getLabelDetails;
    }   

     public function getVendordata()
    {
        $data = Input::all();
        if (!empty($data))
        {
            $dataElement = isset($data['data_type']) ? $data['data_type'] : '';
            $dataValue = isset($data['data_value']) ? $data['data_value'] : '';
            //$manufac_id = isset($data['manufacturer_id'])?$data['manufacturer_id']:"";
            switch ($dataElement)
            {
                
                case 'vendorGroups':
                    $result = DB::table('account_po as ap')
                             ->join('locations as l','l.location_id','=','ap.vendor_id')
                    ->whereIn('po_number', array($dataValue))
                    ->get(array('vendor_id', 'location_name'));
                    break;
              
                default:
                    break;
            }
            return json_encode($result);
        } else
        {
            return 'No Data Posted';
        }
    }
    public function updateLabelwithPO()
    {   
       Log::info(__FUNCTION__.' : '.print_r(Input::get(),true));
        $manufacturerId = Session::get('customerId');
       $data = Input::all();
       unset($data['_token']);
        //dd($data);die;
       //   $getMasterPo = DB::table('wallet_po')->orderby('po_id','desc')->pluck('po_number');
        $date = $this->getdate();
       if(!empty($data)){
        $poInsert = DB::table('account_po')->where(['po_number'=>$data['po_number'],'vendor_id'=>$data['vendor_id']])
                       ->update(['current_date'=>$date]);
        $labeldelete = DB::table('account_labels')->where('po_number',$data['po_number'])->delete();
        foreach($data['data'] as $insert){
             $jsonData = json_decode($insert);
             log::info("start foreach");
             //$chkPO = DB::table('account_labels')->where('po_number',$data['po_number'])->pluck('id');
              $getCodesQty  = DB::table('account_labletype_config')->where('id',$jsonData->label_id)->pluck('codes_qty');
             if($getCodesQty){
                $total = $getCodesQty * $jsonData->qty_id;
             } 
             //if($chkPO){
          //      log::info("+++innner if");
            //update(['sheet_qty'=>$jsonData->qty_id,'codes_qty'=>$total]);
            $labelInsert = DB::table('account_labels')->insert(['po_number'=>$data['po_number'],'label_id'=>$jsonData->label_id,'sheet_qty'=>$jsonData->qty_id,'codes_qty'=>$total]);
                 }

        //}
        log::info("++++after foreach");
       }
        //return Redirect::to('account/index');
       return Response::json([
                    'status' => true,
                    'message' => 'Sucessfully Updated.'
        ]);
    }

    public function saveLableName(){

      $requset = Input::all();

      $labeldetails =$this->_labelorder->savelabelindb($requset);
      $data = json_decode(json_encode($labeldetails), true);
      return $data;

    }

    public function podownload(){
      $requset = Input::all();     

      $exportdetails =$this->_labelorder->ExportPoDetails($requset);
        $headers=[];
        $headers['location']='location';
        $export=[];
        $loopCounter=1;

        foreach ($exportdetails as $detValue) {

          $key=substr(trim($detValue->label_type),0,3);         
          $headers[$key.'-codes']=$key.'-codes';
          $headers[$key.'-lables']=$key.'-lables';
          
          if(!isset($export[$detValue->po_number]['totcodes'])){
            $export[$detValue->po_number]['totcodes']=0;
          } 
          
          if(!isset($export[$detValue->po_number]['totlbls'])){
            $export[$detValue->po_number]['totlbls']=0;
          }
         /* if(!isset($export[$detValue->po_number]['qty'])){
            $export[$detValue->po_number]['qty']=0;
          }*/ 
          
          $export[$detValue->po_number]['location']=$detValue->location_name;
          $export[$detValue->po_number][$key.'-codes']=$detValue->number_of_codes;
          $export[$detValue->po_number][$key.'-lables']=$detValue->number_of_labels;
          $export[$detValue->po_number][$key.'-qty']=$detValue->qty;
          $export[$detValue->po_number]['date']=$detValue->date;
          $export[$detValue->po_number]['status']='';
          $export[$detValue->po_number]['ponum']=$detValue->po_number;
          //$export[$detValue->po_number]['qty']+=$detValue->qty;
          

          $export[$detValue->po_number]['totcodes']+=$detValue->number_of_codes;            
          $export[$detValue->po_number]['totlbls']+=$detValue->number_of_labels;
        }
         $headers['totlbls']='Total no labels';
         $headers['totcodes']='Total no codes';
         $headers['ponum']='Po number';
         $headers['date']='Po date';
         $headers['status']='Status';
       
        Excel::create('poreport', function($excel) use($headers,$export) 
            {
                $excel->sheet("poreport", function($sheet) use($headers,$export)
                {
                    $sheet->loadView('account.downloadTemplate', array('headers' => $headers,'data' => $export)); 
                });
              ob_end_clean();  

            })->export('xlsx');
    }

    public function checkponumbersbasedonvendor(){
      $data = Input::all();
      $ponumbers =$this->_labelorder->getponumbersbyvendor($data);
      return $ponumbers; 
    }

    public function getpohistory($ponumer){

    $podata =$this->_labelorder->getpohistorydata($ponumer);
    $data1 = json_decode(json_encode($podata));

    $data = "";
    $loopcoun=0;
    foreach($data1 as $dat){
      $data .= "<tr>
              <td>".$dat->po_number."</td>
              <td>".$dat->updated_at."</td>
              <td>".$dat->username."</td>
              <td>".$dat->name."</td>
              </tr>";
      $loopcoun++;
        
    }

      return $data;

    }




}