<?php 
set_time_limit(0);
//ini_set('memory_limit', '-1');
use Central\Repositories\CustomerRepo;
use Central\Repositories\RoleRepo;
use Central\Repositories\SapApiRepo;
use Central\Repositories\ApiRepo;
date_default_timezone_set("Asia/Calcutta"); 
 


//use Carbon;

class QrController extends BaseController{

    protected $CustomerObj;
    
    protected $roleAccessObj;
    protected $roleid;

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
        
        $product = new Products\Products();
        $productattr = new Products\ProductAttributes();
        $this->_product = $product;
        $this->_productattr = $productattr;
        $this->roleRepo = new RoleRepo;
        $this->_manufacturerId = $this->_product->getManufacturerId();
    }

    public function index(){

        // parent::Breadcrumbs(array('Home'=>'/','Print  Label '=>'#'));
        parent::Breadcrumbs(array('Home'=>'/')); 
        $manufacturerId = Session::get('customerId');
         $manufacturerId = Session::get('customerId');
        parent::Breadcrumbs(array('Home'=>'/'));
        $getUser = Session::get('userId');
        $locationId = DB::table('users')->select('location_id')->where('user_id',$getUser)->lists('location_id');
        //dd($getUser);die;
        //$pids='';
        foreach ($locationId as $locid) {
          $pids = DB::table('product_locations')->where('location_id',$locid)->lists('product_id');
        }
        //dd($pids);die;
        $levelQty = DB::table('product_packages')->where('level','=','16001')->whereIn('product_id',$pids)->lists('product_id','quantity');

         $products = DB::table('products')->whereIn('product_id',$pids)->get(['product_id','name']);
         //dd($products);die;
        //return View::make('qrcode/prdqrcode')->with(array('prdArray'=>$products));
        return View::make('qrcode/home_qruser')->with(array('prdArray'=>$products));
         
    }  

    public function prdqrcode_new(){

        // parent::Breadcrumbs(array('Home'=>'/','Print  Label '=>'#'));
        parent::Breadcrumbs(array('Home'=>'/','Print Label'=>'qrcode/prdqrcode_new','Print Label'=>'#')); 
        $manufacturerId = Session::get('customerId');
         $manufacturerId = Session::get('customerId');
        parent::Breadcrumbs(array('Home'=>'/','Print  Label '=>'#'));
        $getUser = Session::get('userId');
        $locationId = DB::table('users')->select('location_id')->where('user_id',$getUser)->lists('location_id');
        //dd($getUser);die;
        //$pids='';
        foreach ($locationId as $locid) {
          $pids = DB::table('product_locations')->where('location_id',$locid)->lists('product_id');
        }
        //dd($pids);die;
        $levelQty = DB::table('product_packages')->where('level','=','16001')->whereIn('product_id',$pids)->lists('product_id','quantity');

         $products = DB::table('products')->whereIn('product_id',$pids)->get(['product_id','name']);
         //dd($products);die;
        return View::make('qrcode/prdqrcode_new')->with(array('prdArray'=>$products));
         
    }  

    public function getAttributes($prdId){
       $prdArry = DB::table('products')->where('product_id',$prdId)->get();
       $prdArry = json_decode(json_encode($prdArry),true);
       //dd($prdArry[0]['description']);
       $getProdGrp = DB::table('products')->where('product_id',$prdId)->pluck('group_id');
       $getUser = Session::get('userId');
       $locationId = DB::table('users')->select('location_id')->where('user_id',$getUser)->lists('location_id');
       $getAttrsetId = DB::table('product_attributesets')->where('location_id',$locationId)->where('product_group_id',$getProdGrp)->pluck('attribute_set_id');
       //dd($getAttrsetId);die;
      $getAttrIds = DB::table('attribute_set_mapping')->where('attribute_set_id',$getAttrsetId)->lists('attribute_id');
      $Attrs = DB::table('attributes')->select('attribute_id','name','input_type','product_column','default_value',DB::raw(' "" as options'))->whereIn('attribute_id',$getAttrIds)->get();

       $getMatcode = DB::table('products')->where('product_id',$prdId)->pluck('material_code');
       $getPrdName = DB::table('products')->where('product_id',$prdId)->pluck('name');

       $getParentQty = DB::table('product_packages')->where('level','=',16002)->where('product_id',$prdId)->pluck('quantity');
       $getImage = DB::table('product_packages')->where('level','=','16001')->where('product_id',$prdId)->pluck('image');
      
        if(!$getParentQty){
        $getParentQty = DB::table('product_packages')->where('level','=',16001)->where('product_id',$prdId)->pluck('quantity');
       }
     // $image = URL::to('/').'/public/'.$getImage;
    
       if($getImage=='1' || $getImage=='' ){
        $image = 'http://vguard.esealinc.com:555/download/qrimages/qrcode'.$getParentQty.'.jpeg';
       } else $image = 'http://vguard.esealinc.com:555/'.$getImage;

      //$imagedata = base64_encode(file_get_contents($image));
        $image1= '<img  style="height:400px;width:500px;" src="' .$image .'">';


      //$imagedata = base64_encode(file_get_contents($image));
        //$image1= '<img  style="height:400px;width:500px;" src="data:image/jpeg;base64,'.$data.'">';
      
      //dd($image);die;
       $Attrs =json_decode(json_encode($Attrs),true);
       $var['input_type']='quantity';
       $var['quantity'] = $getParentQty;
       $var['default_value']='';
       $Attrs[] =$var;
       $img['input_type'] = 'image';
       $img['image'] = $image1;
       $img['default_value']='';
       $Attrs[] =$img;
        $prdName['input_type'] = 'prdName';
       $prdName['prdName'] = $getPrdName;
       $prdName['default_value']='';
       $Attrs[] =$prdName;
        $matcode['input_type'] = 'matcode';
       $matcode['matcode'] = $getMatcode;
       $matcode['default_value']='';
       $Attrs[] =$matcode;

     //  dd(array_keys($Attrs));die;
          for($i=0;$i<count(array_keys($Attrs));$i++)
         {
          if($Attrs[$i]['input_type'] == 'inherit'){ 
          
           $Attrs[$i]['default_value'] = $prdArry[0][$Attrs[$i]['product_column']];
         }
         if($Attrs[$i]['input_type'] == 'options'){
           $Attrs[$i]['default_value'] = $prdArry[0][$Attrs[$i]['default_value']];
           $options = DB::table('attribute_options')->select('option_value')->where('attribute_id',$Attrs[$i]['attribute_id'])->get();
           $Attrs[$i]['options'] = $options;
         }
        }
    //dd($Attrs);die;
      return $Attrs;
    }
    public function getProductsByUser(){
      $user_id=Input::get('vendor');
      $locationId = DB::table('users')->where('user_id',$user_id)->lists('location_id');
      $pids = DB::table('product_locations')->whereIn('location_id',$locationId)->lists('product_id');
      $products = DB::table('products as p')
                  ->leftJoin('product_packages as pp', function($join)
                    {
                        $join->on('p.product_id','=','pp.product_id')
                             ->where('pp.level', '=','16002');
                    })
                  ->leftJoin('product_locations as pl', function($join) use($locationId)
                    {
                        $join->on('p.product_id','=','pl.product_id')
                             ->where('pl.location_id', '=',$locationId);
                    })
                  ->whereIn('p.product_id',$pids)
                  ->get(['p.product_id','p.name','p.description','p.material_code','pp.quantity','pl.layout_id','pl.location_id']);
        return $products;
    }

    public function getProductsByLocation(){
      $locationId=Input::get('vendor');
      $products = DB::table('products as p')
                  ->join('product_locations as pl','p.product_id','=','pl.product_id')
                  ->where('pl.location_id','=',$locationId)
                  ->get(['p.product_id','p.name','p.description','p.material_code','pl.layout_id','pl.location_id']);
        return $products;
    }
    
     public function genIotSerialNo($srno){
     return date("dm").str_pad($srno,5,'0', STR_PAD_LEFT);  
    }

public function reGenPdf() {
  try{
  $input = Input::all();

  $track_id=$input['track_id'];
  $qrId=$input['qr_gen_id'];
  $qrInfo=DB::table('qr_code_generator')->where('id',$qrId)->get(['user_id','product_id','mfg_date','quatity','serial_no_start','serial_no_end','status','id']);
  if(isset($qrInfo[0])){
    $data=$qrInfo[0];
    $pid=$qrInfo[0]->product_id;
    $mfg_date=$qrInfo[0]->mfg_date;
    $user_id=$qrInfo[0]->user_id;
    $iotSerialNo=$qrInfo[0]->serial_no_start;
    $location_id = DB::table('users')->where('user_id',$user_id)->pluck('location_id');
  } else 
  throw new Exception("Qr code Not Availabe", 1);
  
  $Ids=array();
  $level1s=DB::table('eseal_5 as e')->join('products as p','e.pid','=','p.product_id')->join('track_history as th','th.track_id','=','e.track_id')->join('locations as l','l.location_id','=','th.src_loc_id')->where('e.level_id',1)->where('e.track_id',$track_id)->where('e.mfg_date',$mfg_date)->where('e.pid',$pid)->get(['e.parent_id','e.primary_id','p.name as productName','p.material_code','e.mfg_date','l.location_name','l.erp_code']);


  if(count($level1s)>0){
      foreach ($level1s as $key => $parent) {
        //childs
        $childs=[];
         $level0=DB::table('eseal_5 as e')->join('products as p','e.pid','=','p.product_id')->join('track_history as th','th.track_id','=','e.track_id')->join('locations as l','l.location_id','=','th.src_loc_id')->where('e.level_id',0)->where('parent_id',$parent->primary_id)->where('e.track_id',$track_id)->where('e.mfg_date',$mfg_date)->where('e.pid',$pid)->get(['e.parent_id','e.primary_id','p.name as productName','p.material_code','e.mfg_date','l.location_name','l.erp_code']);
         foreach ($level0 as $key => $value) {
           $childs[]=$value->primary_id;
         }
        $temp=array();
        $temp['parent']=$parent->primary_id;
        $temp['childs']=$childs;
        $temp['product_name']=$parent->productName;
        $temp['material_code']=$parent->material_code;
        $temp['mfg_date']=$parent->mfg_date;
        $temp['location']=$parent->location_name;
        $temp['erp_code']=$parent->erp_code;
        $Ids[]=$temp;
        $data->mfg_date=$parent->mfg_date;
        $data->material_code=$parent->material_code;
      //  $data=(object) $temp;
      }
  } else {
   // echo "test";
      $childs=[];
         $level0=DB::table('eseal_5 as e')->join('products as p','e.pid','=','p.product_id')->join('track_history as th','th.track_id','=','e.track_id')->join('locations as l','l.location_id','=','th.src_loc_id')->where('e.level_id',0)->where('e.track_id',$track_id)->where('e.mfg_date',$mfg_date)->where('e.pid',$pid)->get(['e.parent_id','e.primary_id','p.name as productName','p.material_code','e.mfg_date','l.location_name','l.erp_code']);
         foreach ($level0 as $key => $value) {
             $childs[]=$value->primary_id;
         }
        $temp=array();
        $parent=$level0[0];
        $temp['parent']=0;
        $temp['childs']=$childs;
        $temp['product_name']=$parent->productName;
        $temp['material_code']=$parent->material_code;
        $temp['mfg_date']=$parent->mfg_date;
        $temp['location_name']=$parent->location_name;
        $temp['erp_code']=$parent->erp_code;
        $Ids[]=$temp;
        $data->mfg_date=$parent->mfg_date;
        $data->material_code=$parent->material_code;
       // $data=(object) $temp;
         
      }

   

              $str = '<html>
              <body>
              <style>               
                .page-break{ 
                  display: block !important; 
                  clear: both !important; 
                  page-break-after:always !important;
                } 
                .col1{
                  width:30.5% !important;
                  float:left !important;
                  padding:5px !important;
                 /* padding:2px !important;
                  border:1px solid #e4e4e4;
                  margin:2px !important;*/
                }
                .page,.col1{
                  height:0.9in !important;
                  margin-top: 0.05in !important;
                 /* margin-bottom: 0.05in !important; */
                }  
                .middleRow{
                  height:0.7in !important;
                }
                .firstRow,.thrdRow{
                  height:0.13in !important;
                }   
                .firstRow p{
                  padding:0px;
                  margin:0px;
                  font-size:8px;
                } 
                .thrdRow p{
                  padding:0px;
                  margin:0px;
                  font-size:11px;
                } 
                 .firstRow p{
                  width:45%;
                 }    

             </style>';   
            $path = '/download/qrimages/temp_codes/';
            //$iotSerialNo=(int) DB::table('qr_code_generator')->where('timestamp','>=','"'.date("Y-m-d").'"')->max('serial_no_end');
            
         /*   $qury=' select max(`serial_no_end`) as aggregate from `qr_code_generator` where `timestamp` >= "'.date("Y-m-d").'"';
            $iotSerialNo=(int) DB::select($qury)[0]->aggregate;*/
            $iotSerialNoIni=$iotSerialNo;
            $childsCnt=0;
            $mastersCnt=0;
            $masterAfterChild=1;
            $filesTobeDelete=[];
            $layout_id=DB::table('product_locations')
                          ->where('product_id',$pid)              
                          ->where('location_id',$location_id)              
                          ->pluck('layout_id');
            $layouts=explode(',',$layout_id);
            //$layouts=array(1);
            //design 1

            //for layout 1 and 3
            if(in_array(1,$layouts)||in_array(3,$layouts)){
              $childsCnt=3;
              $mastersCnt=1;
            }

            //for layout 2 and 5
            if(in_array(2,$layouts)||in_array(5,$layouts)){
              $childsCnt=1;
              $mastersCnt=1;
            }

            //for layout 4
            if(in_array(4,$layouts)){
              $mastersCnt=1;
              $masterAfterChild=0;
            }
             $gridCount=0;
             $filesTobeDelete=$iniotsExccel=[];
             $strCodes='';
             $strCodesParent='';
             foreach ($Ids as $iots) {  
                  $childsArray=$iots['childs'];
                  $parent=isset($iots['parent']);

                 
                  foreach ($childsArray as $childs) {
                    $qrcodefile=$path.$childs.'_'.time().'.png';

                    $temp=[];
                    $temp['parent']=empty($iots['parent'])?'0':$iots['parent'];
                    $temp['primary_id']=(string) ''.$childs.'';
                    $temp['product_name']=$iots['product_name'];
                    $temp['material_code']=$iots['material_code'];
                    $temp['mfg_date']=$iots['mfg_date'];
                    $temp['location']=$iots['location_name'];
                    $temp['erp_code']=$iots['erp_code'];
                    $iniotsExccel[]= $temp;
 
                    QrCode::format('png')->size(90)->generate($childs, public_path().$qrcodefile);
                    chmod(public_path().$qrcodefile, 0777);
                    $filesTobeDelete[]=public_path().$qrcodefile;
                    if($gridCount%3==0)
                    $strCodes .= '<div class="page-break page">';
                      $iotSerialNo=$iotSerialNo+1;
                      for($i = 0;$i< $childsCnt; $i++){ 
                        $gridCount++;
                        $strCodes .= '<div class="col1">
                                <div class="firstRow"><p style="text-align:left;float:left;">'.($iots['material_code']).'</p><p class="" style="text-align:right;float:right;">'.($this->genIotSerialNo($iotSerialNo)).'</p></div>
                                <div class="middleRow"><center><img src="'.public_path().$qrcodefile.'" style="height:72px;" ></center></div>
                                <div class="thrdRow"><p style="text-align:center">'.$childs.'</p></div></div>';
                      }   
                      if($gridCount%3==0)
                  $strCodes .= '</div>';

                }
                if($parent!='' && $masterAfterChild){
                  $parent=$iots['parent'];
                    $qrcodefile=$path.$parent.'_'.time().'.png';

                    QrCode::format('png')->size(90)->generate($parent, public_path().$qrcodefile);
                    chmod(public_path().$qrcodefile, 0777);
                    $filesTobeDelete[]=public_path().$qrcodefile;
                    
                    $strCodesParent .= '<div class="page-break page">';
                    $iotSerialNo=$iotSerialNo+1;
                    $strCodesParent .= '<div class="col1">
                              <div class="firstRow"><p style="text-align:left;float:left;">'.($iots['material_code']).'</p><p class="" style="text-align:right;float:right;">'.($this->genIotSerialNo($iotSerialNo)).'</p></div>
                              <div class="middleRow"><center><img src="'.public_path().$qrcodefile.'" style="height:72px;" ></center></div>
                              <div class="thrdRow"><p style="text-align:center">'.$parent.'</p></div></div>';
                    $strCodesParent .= '<div class="col1">
                    <div class="parentMiddle"><br>';
                      foreach ($childsArray as $key => $value) {
                        $strCodesParent .='<p style="font-size:8px;text-align:center;padding:0px;margin:0px;">'.$value.'</p>';
                      }
                      $strCodesParent.=' </div> </div>';

                    $strCodesParent .= '<div class="col1">
                              <div class="firstRow"><p style="text-align:left;float:left;">'.($iots['material_code']).'</p><p class="" style="text-align:right;float:right;">'.($this->genIotSerialNo($iotSerialNo)).'</p></div>
                              <div class="middleRow"><center><img src="'.public_path().$qrcodefile.'" style="height:72px;" ></center></div>
                              <div class="thrdRow"><p style="text-align:center">'.$parent.'</p></div></div>';
                  $strCodesParent .= '</div>';
                  $strCodes=$strCodes.$strCodesParent;
                  $strCodesParent='';
                }
              }

              if($masterAfterChild==0){
                 foreach ($Ids as $iots) {  
                    $childsArray=$iots['childs'];
                    $parent=isset($iots['parent']);
                     if($parent!=''){
                       $parent=$iots['parent'];
                      $qrcodefile=$path.$parent.'_'.time().'.png';

                      QrCode::format('png')->size(90)->generate($parent, public_path().$qrcodefile);
                      chmod(public_path().$qrcodefile, 0777);
                      $filesTobeDelete[]=public_path().$qrcodefile;
                      
                      $strCodesParent .= '<div class="page-break page">';
                      $iotSerialNo=$iotSerialNo+1;
                      $strCodesParent .= '<div class="col1">
                                <div class="firstRow"><p style="text-align:left;float:left;">'.($iots['material_code']).'</p><p class="" style="text-align:right;float:right;">'.($this->genIotSerialNo($iotSerialNo)).'</p></div>
                                <div class="middleRow"><center><img src="'.public_path().$qrcodefile.'" style="height:72px;" ></center></div>
                                <div class="thrdRow"><p style="text-align:center">'.$parent.'</p></div></div>';
                      $strCodesParent .= '<div class="col1">
                      <div class="parentMiddle"><br>';
                        foreach ($childsArray as $key => $value) {
                          $strCodesParent .='<p style="font-size:8px;text-align:center;padding:0px;margin:0px;">'.$value.'</p>';
                        }
                        $strCodesParent.=' </div> </div>';

                      $strCodesParent .= '<div class="col1">
                                <div class="firstRow"><p style="text-align:left;float:left;">'.($iots['material_code']).'</p><p class="" style="text-align:right;float:right;">'.($this->genIotSerialNo($iotSerialNo)).'</p></div>
                                <div class="middleRow"><center><img src="'.public_path().$qrcodefile.'" style="height:72px;" ></center></div>
                                <div class="thrdRow"><p style="text-align:center">'.$parent.'</p></div></div>';
                    $strCodesParent .= '</div>';
                    $strCodes=$strCodes.$strCodesParent;
                    $strCodesParent='';
                  }

                 }

              }



          $name='PDF-'.$data->user_id.'-'.$data->quatity.'_'.time();
          $str .= $strCodes.$strCodesParent.'</body></html>';

          $path='/download/qrpdfs';
          $htmlfile=trim($path).'/'.trim($name).'.html';
          $pdf=trim($path).'/'.trim($name).'.pdf';
          $htmlfile=str_replace(' ','',$htmlfile);
          $pdf=str_replace(' ','',$pdf);
          File::put(public_path().$htmlfile,$str);
          chmod(public_path().$htmlfile, 0777);

          //echo public_path().$htmlfile; exit;
          exec('/usr/local/bin/wkhtmltopdf  -L 0mm -R 0mm -T 0mm -B 0mm --page-width 3in --page-height 0.9in '.public_path().$htmlfile.' '.public_path().$pdf);
          chmod(public_path().$pdf, 0777);

           $s3 = new s3\S3();
           $s3file=$s3->uploadFile(public_path().$pdf,'download_qrpdf');
           $pdf='/'.$s3file;

          echo $pdflinkA='<a href="http://'.$_SERVER['HTTP_HOST'].'/'.$pdf.'"> click here </a>to download or copy the following URL to download.<br><br><br>';

          $PrintingBatch = date('Ymd',strtotime($data->mfg_date));
          //$currentDate =date('Y-m-d H:i:s');
          $name = "PDF-".$data->material_code."_".$PrintingBatch;

          Excel::create($name, function($excel) use ($iniotsExccel) {
          $excel->sheet('mySheet', function($sheet) use ($iniotsExccel)
          {
          $sheet->fromArray($iniotsExccel);
          });
          })->store('xlsx', public_path()."/download/qrpdfs");
          $xlsx="/download/qrpdfs/".$name.'.xlsx';
          chmod(public_path().$xlsx, 0777);
          $s3 = new s3\S3();
          $s3file=$s3->uploadFile(public_path().$xlsx,'download_qrpdf');
          @unlink(public_path().$xlsx);
          $xlsx='/'.$s3file;
          // excel file saving end
          $remarks=json_encode(['pdf'=>$pdf,'xlsx'=>$xlsx]);

          $update=DB::table('qr_code_generator')->where('id',$data->id)->update(['status'=>1,'files'=>$remarks,'serial_no_start'=>$iotSerialNoIni,'serial_no_end'=>$iotSerialNo]);

  }catch(Exception $e){
      echo $message = $e->getMessage();
    }
}




public function pdfGenCron() {
  $pdfsize=10000;
  try{
    $start = microtime(true);
      $time_elapsed_secs=0;
      do{
        try{
        $data=DB::table('qr_code_generator as q')->where('q.status',4)->orderBy('id','asc')->take(1)->get();
            echo "<pre>";
           print_r($data);
          if(count($data)>0){
            $data=$data[0];
          

            $update=DB::table('qr_code_generator')->where('id',$data->id)->update(['pdf_iteration'=>($data->pdf_iteration+1)]);
           // echo "test 1";
            $productNameMatcode=DB::table('products')->where('product_id',$data->product_id)->get(['material_code','name']);
            $data->material_code=$productNameMatcode[0]->material_code;
            $data->productName=$productNameMatcode[0]->name;
            $data->location_id = DB::table('users')->where('user_id',$data->user_id)->pluck('location_id');
           // echo "test 2";
            $locNameNerp= DB::table('locations')->where('location_id',$data->location_id)->get(['location_name','erp_code']);            
            $data->location_name= $locNameNerp[0]->location_name;
            $data->erp_code= $locNameNerp[0]->erp_code;
           // echo "test 3";
            $parentQty = (int) DB::table('product_packages')->where('product_id',$data->product_id)->where('level','=',16002)->pluck('quantity');

           // $parentQty=6;
 //echo "test 4";
            $p_limit=$limit=$pdfsize;
            if($parentQty){
              $Totalquantity = $data->quatity/$parentQty;
              $parentQty=$parentQty+1;
              $limit=intval($limit/$parentQty)*$parentQty;
              $p_limit=$limit/$parentQty;
              $totIotQty=$data->quatity+$Totalquantity;
              $level = 1;
            } else {
              $Totalquantity = $data->quatity;
              $totIotQty=$data->quatity;
              $level = 0;
            }
           //  echo "test 5";
            $offset=$data->pdf_iteration*$limit;
            $p_offset=$data->pdf_iteration*$p_limit;
            $lastIteration=0;
            if(($offset+$limit)>=$data->quatity){
                     //      echo "test 7";
              $lastIteration=1;
             //  echo "test 8";
              $qrGEnData=DB::table('qr_code_generator')->where('id',$data->id)->lists('status');
               //echo "test 9";
               // print_r($qrGEnData);
              if($qrGEnData!=6){
               //  echo "test 10";
              $update=DB::table('qr_code_generator')->where('id',$data->id)->update(['status'=>5]);
                // echo "test 11";
              }
            }
                        // echo "test 14";
           echo $iotSerialNo=$data->serial_no_start+($data->pdf_iteration*$limit);
            // echo "test 9";
            echo "offset:".$offset;
            echo "limit:".$limit;
            echo "pdf_iteration:".$data->pdf_iteration;
            echo "lastIteration:".$lastIteration;
            echo "iotSerialNo:".$iotSerialNo;

            $Ids=[];
            $level1s=DB::table('eseal_5 as e')->join('products as p','e.pid','=','p.product_id')->join('track_history as th','th.track_id','=','e.track_id')->join('locations as l','l.location_id','=','th.src_loc_id')->where('e.level_id',1)->where('e.track_id',$data->track_id)->where('e.mfg_date',$data->mfg_date)->where('e.pid',$data->product_id)->skip($p_offset)->take($p_limit)->orderBy('e.eseal_id')->get(['e.parent_id','e.primary_id','p.name as productName','p.material_code','e.mfg_date','l.location_name','l.erp_code']);
           // echo "stests";
            if(count($level1s)>0){

              foreach ($level1s as $key => $parent) {
                 $childs=[];
                 $level0=DB::table('eseal_5 as e')->join('products as p','e.pid','=','p.product_id')->join('track_history as th','th.track_id','=','e.track_id')->join('locations as l','l.location_id','=','th.src_loc_id')->where('e.level_id',0)->where('parent_id',$parent->primary_id)->where('e.track_id',$data->track_id)->where('e.mfg_date',$data->mfg_date)->where('e.pid',$data->product_id)->orderBy('e.eseal_id')->get(['e.parent_id','e.primary_id','p.name as productName','p.material_code','e.mfg_date','l.location_name','l.erp_code']);
                 foreach ($level0 as $key => $value) {
                   $childs[]=$value->primary_id;
                 }
                  $temp=array();
                  $temp['parent']=$parent->primary_id;
                  $temp['childs']=$childs;
                  $temp['product_name']=$parent->productName;
                  $temp['material_code']=$parent->material_code;
                  $temp['mfg_date']=$parent->mfg_date;
                  $temp['location']=$parent->location_name;
                  $temp['erp_code']=$parent->erp_code;
                  $Ids[]=$temp;
                  $data->mfg_date=$parent->mfg_date;
                  $data->material_code=$parent->material_code;
                  //  $data=(object) $temp;
                  }
            } else {
              $childs=[];
              $level0=DB::table('eseal_5 as e')->join('products as p','e.pid','=','p.product_id')->join('track_history as th','th.track_id','=','e.track_id')->join('locations as l','l.location_id','=','th.src_loc_id')->where('e.level_id',0)->where('e.track_id',$data->track_id)->where('e.mfg_date',$data->mfg_date)->where('e.pid',$data->product_id)->skip($offset)->take($limit)->orderBy('e.eseal_id')->get(['e.parent_id','e.primary_id','p.name as productName','p.material_code','e.mfg_date','l.location_name','l.erp_code']);
              if(count($level0)){
                 foreach ($level0 as $key => $value) {
                  $childs[]=$value->primary_id;
                }

                $parent=$level0[0];
                $temp['parent']='';
                $temp['childs']=$childs;
                $temp['product_name']=$parent->productName;
                $temp['material_code']=$parent->material_code;
                $temp['mfg_date']=$parent->mfg_date;
                $temp['location_name']=$parent->location_name;
                $temp['erp_code']=$parent->erp_code;
                $Ids[]=$temp;
                $data->mfg_date=$parent->mfg_date;
                $data->material_code=$parent->material_code;
              }
            }

            if(count($Ids)==0){
              //echo "no data recived";
              /*echo "test all done";
              exit;*/
              return 0;
            //  continue;
            } else {

               $str = '<html>
              <body>
              <style>               
                .page-break{ 
                  display: block !important; 
                  clear: both !important; 
                  page-break-after:always !important;
                } 
                .col1{
                  width:30.5% !important;
                  float:left !important;
                  padding:5px !important;
                 /* padding:2px !important;
                  border:1px solid #e4e4e4;
                  margin:2px !important;*/
                }
                .page,.col1{
                  height:0.9in !important;
                  margin-top: 0.05in !important;
                 /* margin-bottom: 0.05in !important; */
                }  
                .middleRow{
                  height:0.7in !important;
                }
                .firstRow,.thrdRow{
                  height:0.13in !important;
                }   
                .firstRow p{
                  padding:0px;
                  margin:0px;
                  font-size:8px;
                } 
                .thrdRow p{
                  padding:0px;
                  margin:0px;
                  font-size:11px;
                } 
                 .firstRow p{
                  width:45%;
                 }    

             </style>';   
            $path = '/download/qrimages/temp_codes/';

            $iotSerialNoIni=$iotSerialNo;
            $iotSerialNo=$iotSerialNo-1;
            $childsCnt=0;
            $mastersCnt=0;
            $masterAfterChild=1;
            $filesTobeDelete=[];
            $layout_id=DB::table('product_locations')
                          ->where('product_id',$data->product_id)              
                          ->where('location_id',$data->location_id)              
                          ->pluck('layout_id');
            $layouts=explode(',',$layout_id);
            //$layouts=array(1);
            //design 1

            //for layout 1 and 3
            if(in_array(1,$layouts)||in_array(3,$layouts)){
              $childsCnt=3;
              $mastersCnt=1;
            }

            //for layout 2 and 5
            if(in_array(2,$layouts)||in_array(5,$layouts)){
              $childsCnt=1;
              $mastersCnt=1;
            }

            //for layout 4
            if(in_array(4,$layouts)){
              $mastersCnt=1;
              $masterAfterChild=0;
            }
             $gridCount=0;
             $filesTobeDelete=$iniotsExccel=[];
             $strCodes='';
             $strCodesParent='';


             echo "<br>===========Layout selecction============</br>";
             echo "<br>childsCnt:".$childsCnt;
             echo "<br>mastersCnt:".$mastersCnt;
             echo "<br>masterAfterChild:".$masterAfterChild;
             echo "Ids:";
           //  echo print_r($Ids);
  //echo "strCodes start".$strCodes.'strCodes exit';
  echo "strCodes start ==>";

             foreach ($Ids as $iots) {  
                  $childsArray=$iots['childs'];
                  $parent=isset($iots['parent'])?$iots['parent']:0;

                 
                  foreach ($childsArray as $childs) {
                    $qrcodefile=$path.$childs.'_'.time().'.png';
                    echo "<br>.......>>>>....".$childs; 
                    QrCode::format('png')->size(90)->generate($childs, public_path().$qrcodefile);
                    chmod(public_path().$qrcodefile, 0777);
                    $filesTobeDelete[]=public_path().$qrcodefile;
                    if($gridCount%3==0)
                    $strCodes .= '<div class="page-break page">';
                      $iotSerialNo=$iotSerialNo+1;
                      for($i = 0;$i< $childsCnt; $i++){ 
                        $gridCount++;
                        $strCodes .= '<div class="col1">
                                <div class="firstRow"><p style="text-align:left;float:left;">'.($iots['material_code']).'</p><p class="" style="text-align:right;float:right;">'.($this->genIotSerialNo($iotSerialNo)).'</p></div>
                                <div class="middleRow"><center><img src="'.public_path().$qrcodefile.'" style="height:72px;" ></center></div>
                                <div class="thrdRow"><p style="text-align:center">'.$childs.'</p></div></div>';
                      }   
                      if($gridCount%3==0)
                  $strCodes .= '</div>';
                }

                if($parent && $masterAfterChild && $parent!=0){
                  $parent=$iots['parent'];
                    $qrcodefile=$path.$parent.'_'.time().'.png';
                    echo "<br>.......<<<<....".$parent; 
                    QrCode::format('png')->size(90)->generate($parent, public_path().$qrcodefile);
                    chmod(public_path().$qrcodefile, 0777);
                    $filesTobeDelete[]=public_path().$qrcodefile;
                    
                    $strCodes .= '<div class="page-break page">';
                    $iotSerialNo=$iotSerialNo+1;
                    $strCodes .= '<div class="col1">
                              <div class="firstRow"><p style="text-align:left;float:left;">'.($iots['material_code']).'</p><p class="" style="text-align:right;float:right;">'.($this->genIotSerialNo($iotSerialNo)).'</p></div>
                              <div class="middleRow"><center><img src="'.public_path().$qrcodefile.'" style="height:72px;" ></center></div>
                              <div class="thrdRow"><p style="text-align:center">'.$parent.'</p></div></div>';
                    $strCodes .= '<div class="col1">
                    <div class="parentMiddle"><br>';
                      foreach ($childsArray as $key => $value) {
                        $strCodes .='<p style="font-size:8px;text-align:center;padding:0px;margin:0px;">'.$value.'</p>';
                      }
                      $strCodes.=' </div> </div>';
                   // $iotSerialNo=$iotSerialNo+1;
                    $strCodes .= '<div class="col1">
                              <div class="firstRow"><p style="text-align:left;float:left;">'.($iots['material_code']).'</p><p class="" style="text-align:right;float:right;">'.($this->genIotSerialNo($iotSerialNo)).'</p></div>
                              <div class="middleRow"><center><img src="'.public_path().$qrcodefile.'" style="height:72px;" ></center></div>
                              <div class="thrdRow"><p style="text-align:center">'.$parent.'</p></div></div>';
                  $strCodes .= '</div>';
                 /* $strCodes=$strCodes.$strCodesParent;
                  $strCodesParent='';*/
                }
             
              }

              if($masterAfterChild==0){
                 foreach ($Ids as $iots) {  
                    $childsArray=$iots['childs'];
                    $parent=isset($iots['parent'])?$iots['parent']:0;
                     if($parent!='' && $parent!=0){
                       $parent=$iots['parent'];
                      $qrcodefile=$path.$parent.'_'.time().'.png';
                      echo "<br>.......<><><><>....".$parent.'-'; 
                      QrCode::format('png')->size(90)->generate($parent, public_path().$qrcodefile);
                      chmod(public_path().$qrcodefile, 0777);
                      $filesTobeDelete[]=public_path().$qrcodefile;
                      
                      $strCodes .= '<div class="page-break page">';
                      $iotSerialNo=$iotSerialNo+1;
                      $strCodes .= '<div class="col1">
                                <div class="firstRow"><p style="text-align:left;float:left;">'.($iots['material_code']).'</p><p class="" style="text-align:right;float:right;">'.($this->genIotSerialNo($iotSerialNo)).'</p></div>
                                <div class="middleRow"><center><img src="'.public_path().$qrcodefile.'" style="height:72px;" ></center></div>
                                <div class="thrdRow"><p style="text-align:center">'.$parent.'</p></div></div>';
                      $strCodes .= '<div class="col1">
                      <div class="parentMiddle"><br>';
                        foreach ($childsArray as $key => $value) {
                          $strCodes .='<p style="font-size:8px;text-align:center;padding:0px;margin:0px;">'.$value.'</p>';
                        }
                        $strCodes.=' </div> </div>';
                     //    $iotSerialNo=$iotSerialNo+1;
                      $strCodes .= '<div class="col1">
                                <div class="firstRow"><p style="text-align:left;float:left;">'.($iots['material_code']).'</p><p class="" style="text-align:right;float:right;">'.($this->genIotSerialNo($iotSerialNo)).'</p></div>
                                <div class="middleRow"><center><img src="'.public_path().$qrcodefile.'" style="height:72px;" ></center></div>
                                <div class="thrdRow"><p style="text-align:center">'.$parent.'</p></div></div>';
                    $strCodes .= '</div>';
                    /*$strCodes=$strCodes.$strCodesParent;
                    $strCodesParent='';*/
                  }

                 }

              }

        
          $name='PDF-'.$data->user_id.'-'.$data->quatity.'_'.time().'-'.$data->pdf_iteration;
          $str .= $strCodes.$strCodesParent.'</body></html>';


          $path='/download/qrpdfs';
          $htmlfile=trim($path).'/'.trim($name).'.html';
          $pdf=trim($path).'/'.trim($name).'.pdf';
          $htmlfile=str_replace(' ','',$htmlfile);
          $pdf=str_replace(' ','',$pdf);
          File::put(public_path().$htmlfile,$str);
          chmod(public_path().$htmlfile, 0777);

          //echo public_path().$htmlfile; exit;
          exec('/usr/local/bin/wkhtmltopdf  -L 0mm -R 0mm -T 0mm -B 0mm --page-width 3in --page-height 0.9in '.public_path().$htmlfile.' '.public_path().$pdf);
          chmod(public_path().$pdf, 0777);
          $filesTobeDelete[]=public_path().$pdf;
          $s3 = new s3\S3();
          $s3file=$s3->uploadFile(public_path().$pdf,'download_qrpdf');
          $pdf='/'.$s3file;
         $updateQry="UPDATE qr_code_generator SET pdf_files = CONCAT(ifnull(pdf_files,''), ',".$pdf."') where id=".$data->id;
         $update=DB::statement($updateQry);
         
        //  $update=DB::table('qr_code_generator')->where('id',$data->id)->update(['remarks'=>DB::raw('`remarks`+",'.$pdf.'"')]);
          if($lastIteration){
            $qrGEnData=DB::table('qr_code_generator')->where('id',$data->id)->lists('status');
            if($qrGEnData!=6)
            $update=DB::table('qr_code_generator')->where('id',$data->id)->update(['status'=>1]);
          }

          foreach ($filesTobeDelete as $key => $value) {
          //Log::info("UNLINKING FILE".$value);
          @unlink($value);
          }




            }
            
        //  echo $pdflinkA='<a href="http://'.$_SERVER['HTTP_HOST'].'/'.$pdf.'"> click here </a>to download or copy the following URL to download.<br><br><br>';

          
          } else {
            echo "no Data to run pdf gen";
            return 0;
//            break;
       //     exit;
           }
            $time_elapsed_secs = microtime(true) - $start;
            echo "time_elapsed_secs".$time_elapsed_secs;// exit;
           }catch(Exception $e){
    //  echo $message = $e->getMessage(); exit;
      $update=DB::table('qr_code_generator')->where('id',$data->id)->update(['status'=>6,'remarks'=>'Pdf Generation Error '.$e->getMessage()]);
    }
      }while($time_elapsed_secs<180);
         return 1;
  }catch(Exception $e){
      echo $message = $e->getMessage(); exit;
    }
}

public function qrgenCronNew(){

      $start = microtime(true);
      $time_elapsed_secs=0;
    //  do{
      $checkStatus=0;
        $data=Input::get();
   if(isset($data['checkStatus']))
    $checkStatus=(int) $data['checkStatus'];
          $data=DB::table('qr_code_generator as q')->where('q.status',$checkStatus)->orderBy('id','asc')->take(1)->get();
           
          if(count($data)>0){
            $data=$data[0];
            try{
            //process status 2 is for process started

           

            $update=DB::table('qr_code_generator')->where('id',$data->id)->update(['status'=>2,'remarks'=>'Process Started']);

            //$data->material_code = DB::table('products')->where('product_id',$data->product_id)->pluck('material_code');
            $productNameMatcode=DB::table('products')->where('product_id',$data->product_id)->get(['material_code','name']);
            $data->material_code=$productNameMatcode[0]->material_code;
            $data->productName=$productNameMatcode[0]->name;
            $data->location_id = DB::table('users')->where('user_id',$data->user_id)->pluck('location_id');
            $locNameNerp= DB::table('locations')->where('location_id',$data->location_id)->get(['location_name','erp_code']);            
            $data->location_name= $locNameNerp[0]->location_name;
            $data->erp_code= $locNameNerp[0]->erp_code;

            $parentQty = DB::table('product_packages')->where('product_id',$data->product_id)->where('level','=',16002)->pluck('quantity');

            $childAvl=0;
            $parentAvl=0;
            $lay_parentAvl=0;
            $lay_childAvl=0;
            $totIotQty=0;
            $childsCnt=0;
            $mastersCnt=0;
            $masterAfterChild=1;


            if($parentQty){
              $Totalquantity = $data->quatity/$parentQty;
              $totIotQty=$data->quatity+$Totalquantity;
              $level = 1;
              $childAvl=1;
              $parentAvl=1;
            } else {
              $Totalquantity = $data->quatity;
              $totIotQty=$data->quatity;
              $level = 0;
              $childAvl=1;
            }

            $layout_id=DB::table('product_locations')
                          ->where('product_id',$data->product_id)              
                          ->where('location_id',$data->location_id)              
                          ->pluck('layout_id');
            $layouts=explode(',',$layout_id);
          //  $layouts=array(4);
            //design 1

            if(in_array(1,$layouts)||in_array(2,$layouts)){
               $lay_childAvl=1;
            }

            if(in_array(3,$layouts)||in_array(5,$layouts)){
               $lay_parentAvl=1;
               $lay_childAvl=1;
            }

            //for layout 1 and 3
            if(in_array(1,$layouts)||in_array(3,$layouts)){
              $childsCnt=3;
              $mastersCnt=1;             
            }

            //for layout 2 and 5
            if(in_array(2,$layouts)||in_array(5,$layouts)){
              $childsCnt=1;
              $mastersCnt=1;
            }

            //for layout 4
            if(in_array(4,$layouts)){
              $mastersCnt=1;
              $masterAfterChild=0;              
              $lay_parentAvl=1;
            }
           // if()
        /*    echo "<br>layouts";
            print_r($layouts);
            echo "<br>childAvl".$childAvl;
            echo "<br>lay_childAvl".$lay_childAvl;
            echo "<br>lay_parentAvl".$lay_parentAvl;
            echo "<br>parentAvl".$parentAvl;
            exit;*/
            if($childAvl!=$lay_childAvl || $lay_parentAvl!=$parentAvl){          
              $data=DB::table('qr_code_generator')->where('id',$data->id)->update(['status'=>3,'remarks'=>'Layout configuration mismatch','files'=>'']);
                echo "<br>layouts";
                print_r($layouts);
                echo "<br>childAvl".$childAvl;
                echo "<br>lay_childAvl".$lay_childAvl;
                echo "<br>lay_parentAvl".$lay_parentAvl;
                echo "<br>parentAvl".$parentAvl;
                exit;
            }

            $qury=' select max(`serial_no_end`) as aggregate from `qr_code_generator` where `timestamp` >= "'.date("Y-m-d",strtotime($data->timestamp)).'"';
            $iotSerialNoIni=((int) DB::select($qury)[0]->aggregate )+ 1;
            $iotSerialNo=$iotSerialNoIni+($totIotQty-1);
           
            $update=DB::table('qr_code_generator')->where('id',$data->id)->update(['serial_no_start'=>$iotSerialNoIni,'serial_no_end'=>$iotSerialNo]);

            $moduleId = 4002;
            $access_token = DB::table('users_token')->where('user_id',$data->user_id)->where('module_id','=',$moduleId)->pluck('access_token');
            $attributes=json_encode(['batch_no'=>date('mY',strtotime($data->mfg_date)),'material_code'=>$data->material_code,'MFG_DATE'=>$data->mfg_date]);
            $transitionTime = date("Y-m-d H:i:s",strtotime($data->mfg_date));
            $transitionId = DB::table('transaction_master')->where('name','=','Label Printing')->pluck('id');
            
            $input =array('module_id'=> $moduleId ,'access_token'=>$access_token,'attributes'=>$attributes,'level'=>$level,'pid'=>$data->product_id,'parentQty'=>$Totalquantity,'transitionTime'=>$transitionTime,'transitionId'=>$transitionId);

           

            $request = Request::create('scoapi/bindMapWithEseals','POST',$input);
            $originalInput = Request::input();//backup original input
            Request::replace($request->input());
            $responseJson = Route::dispatch($request)->getContent();//invoke API
            $response = json_decode($responseJson);
            $Ids = json_decode(json_encode($response->Data),true);

            
            if(count($Ids)<=0){
              $data=DB::table('qr_code_generator')->where('id',$data->id)->update(['status'=>8,'remarks'=>'Ids Not Availabe','files'=>$responseJson]);
              return 0;
             //   continue;
            } else {

          
            $filesTobeDelete=[];
            $iniotsExccel=[];


           


             foreach ($Ids as $iots) {  
                  $excelArray=array();
                  $childsArray=$iots['childs'];
                  $parent=isset($iots['parent']);
                  foreach ($childsArray as $childs) {
                    $temp=[];
                    $temp['parent']=empty($iots['parent'])?'0':$iots['parent'];
                    $temp['primary_id']=$childs;
                    $temp['product_name']=$data->productName;
                    $temp['material_code']=$data->material_code;
                    $temp['mfg_date']=$data->mfg_date;
                    $temp['location']=$data->location_name;
                    $temp['erp_code']=$data->erp_code;
                   // $temp['primary_srNo']=$iotSerialNoIni++;
                    //$temp['parent_srNo']='';
                    //$excelArray=$temp;
                    $iniotsExccel[]= $temp;
                    }
                    /*$parent=empty($iots['parent'])?'0':$iots['parent'];
                    if($masterAfterChild && $parent!=0 && $parent!=''){
                      $parentSrno=$iotSerialNoIni++;
                      foreach ($excelArray as $ekey => $evalue) {
                        $excelArray[$ekey]['parent_srNo']=$parentSrno;
                      }
                    }*/ 
                  //$iniotsExccel=array_merge($iniotsExccel,$excelArray);
                }

               /* $layout_4_index=0;
                if($masterAfterChild==0 && $mastersCnt>0){
                    foreach ($Ids as $iots) {  
                    $excelArray=array();
                    $childsArray=$iots['childs'];
                    $parent=empty($iots['parent'])?'0':$iots['parent'];
                    if($parent!=0 && $parent!='')
                       $parentSrno=$iotSerialNoIni++;
                      foreach ($childsArray as $childs) {
                        $iniotsExccel[$layout_4_index]['parent_srNo']=$parentSrno;
                        $layout_4_index++;
                      }
                    }
                }*/


              if($iniotsExccel[0]['primary_id']==''){
                 $data=DB::table('qr_code_generator')->where('id',$data->id)->update(['status'=>8,'remarks'=>'Ids Not Availabe','files'=>$responseJson]);
                 return 0;
              }

          $esealInfo=DB::table('eseal_5')->where('primary_id',$iniotsExccel[0]['primary_id'])->get(['eseal_id','primary_id','track_id']);
          $pdf_itaretion=0;
          $track_id=$esealInfo[0]->track_id;

          $PrintingBatch = date('Ymd',strtotime($data->mfg_date));
          //$currentDate =date('Y-m-d H:i:s');
          $name = "PDF-".$data->material_code."_".$PrintingBatch;

          Excel::create($name, function($excel) use ($iniotsExccel) {
          $excel->sheet('mySheet', function($sheet) use ($iniotsExccel)
          {
          $sheet->fromArray($iniotsExccel);
          });
          })->store('xlsx', public_path()."/download/qrpdfs");
          $xlsx="/download/qrpdfs/".$name.'.xlsx';
          chmod(public_path().$xlsx, 0777);
          $s3 = new s3\S3();
          $s3file=$s3->uploadFile(public_path().$xlsx,'download_qrpdf');
          @unlink(public_path().$xlsx);
          $xlsx='/'.$s3file;
          // excel file saving end
          $remarks=$xlsx;

          $update=DB::table('qr_code_generator')->where('id',$data->id)->update(['status'=>4,'files'=>$remarks,'pdf_iteration'=>$pdf_itaretion,'track_id'=>$track_id]);

        }
      $time_elapsed_secs = microtime(true) - $start;
        return 1;
        }catch(Exception $e){
    //  echo $message = $e->getMessage(); exit;
      $update=DB::table('qr_code_generator')->where('id',$data->id)->update(['status'=>3,'remarks'=>'Excel Generation error'.$e->getMessage()]);
    }
    
           } else {
            echo "no Data to run xll gen";
        return 0;
           }
      //}while($time_elapsed_secs<180);
     
    }
 







public function qrgenNew(){
}

public function qrgenCron(){

/*  $start = microtime(true);
  $time_elapsed_secs=0;
  $qrgenCronNew=1;
  $pdfGenCron=1;
  do{
    if($qrgenCronNew){
    $qrgenCronNew=$this->qrgenCronNew();
    }
    if($pdfGenCron){
    $pdfGenCron=$this->pdfGenCron();
    }
    if($qrgenCronNew==0 && $pdfGenCron==0){
      echo "cron Done";
      exit;
    }
//      return 1;
    }while($time_elapsed_secs<300);*/

}


    public function qrgenCron_old(){
     
      $start = microtime(true);
      $time_elapsed_secs=0;
      do{
          $data=DB::table('qr_code_generator as q')->where('q.status',0)->orderBy('id','asc')->take(1)->get();
           
          if(count($data)>0){
            $data=$data[0];
            //process status 2 is for process started
            $update=DB::table('qr_code_generator')->where('id',$data->id)->update(['status'=>2,'remarks'=>'Process Started']);
            //$data->material_code = DB::table('products')->where('product_id',$data->product_id)->pluck('material_code');
            $productNameMatcode=DB::table('products')->where('product_id',$data->product_id)->get(['material_code','name']);
            $data->material_code=$productNameMatcode[0]->material_code;
            $data->productName=$productNameMatcode[0]->name;
            $data->location_id = DB::table('users')->where('user_id',$data->user_id)->pluck('location_id');
            $locNameNerp= DB::table('locations')->where('location_id',$data->location_id)->get(['location_name','erp_code']);            
            $data->location_name= $locNameNerp[0]->location_name;
            $data->erp_code= $locNameNerp[0]->erp_code;

            $parentQty = DB::table('product_packages')->where('product_id',$data->product_id)->where('level','=',16002)->pluck('quantity');

            if($parentQty){
              $Totalquantity = $data->quatity/$parentQty;
              $level = 1;
            } else {
              $Totalquantity = $data->quatity;
              $level = 0;
            }
        
            $moduleId = 4002;
            $access_token = DB::table('users_token')->where('user_id',$data->user_id)->where('module_id','=',$moduleId)->pluck('access_token');
            $attributes=json_encode(['batch_no'=>date('mY',strtotime($data->mfg_date)),'material_code'=>$data->material_code,'MFG_DATE'=>$data->mfg_date]);
            $transitionTime = date("Y-m-d H:i:s",strtotime($data->mfg_date));
            $transitionId = DB::table('transaction_master')->where('name','=','Label Printing')->pluck('id');
            
            $input =array('module_id'=> $moduleId ,'access_token'=>$access_token,'attributes'=>$attributes,'level'=>$level,'pid'=>$data->product_id,'parentQty'=>$Totalquantity,'transitionTime'=>$transitionTime,'transitionId'=>$transitionId);

           

            $request = Request::create('scoapi/bindMapWithEseals','POST',$input);
            $originalInput = Request::input();//backup original input
            Request::replace($request->input());
            $response = Route::dispatch($request)->getContent();//invoke API
            $response = json_decode($response);
            $Ids = json_decode(json_encode($response->Data),true);


            if(count($Ids)<=0){
              $data=DB::table('qr_code_generator')->where('id',$data->id)->update(['status'=>3,'remarks'=>'Ids Not Availabe']);
                continue;
            } else {

              $str = '<html>
              <body>
              <style>               
                .page-break{ 
                  display: block !important; 
                  clear: both !important; 
                  page-break-after:always !important;
                } 
                .col1{
                  width:30.5% !important;
                  float:left !important;
                  padding:5px !important;
                 /* padding:2px !important;
                  border:1px solid #e4e4e4;
                  margin:2px !important;*/
                }
                .page,.col1{
                  height:0.9in !important;
                  margin-top: 0.05in !important;
                 /* margin-bottom: 0.05in !important; */
                }  
                .middleRow{
                  height:0.7in !important;
                }
                .firstRow,.thrdRow{
                  height:0.13in !important;
                }   
                .firstRow p{
                  padding:0px;
                  margin:0px;
                  font-size:8px;
                } 
                .thrdRow p{
                  padding:0px;
                  margin:0px;
                  font-size:11px;
                } 
                 .firstRow p{
                  width:45%;
                 }    

             </style>';   
            $path = '/download/qrimages/temp_codes/';
            //$iotSerialNo=(int) DB::table('qr_code_generator')->where('timestamp','>=','"'.date("Y-m-d").'"')->max('serial_no_end');
            $qury=' select max(`serial_no_end`) as aggregate from `qr_code_generator` where `timestamp` >= "'.date("Y-m-d").'"';
            $iotSerialNo=(int) DB::select($qury)[0]->aggregate;
            $iotSerialNoIni=$iotSerialNo;
            $childsCnt=0;
            $mastersCnt=0;
            $masterAfterChild=1;
            $filesTobeDelete=[];
            $layout_id=DB::table('product_locations')
                          ->where('product_id',$data->product_id)              
                          ->where('location_id',$data->location_id)              
                          ->pluck('layout_id');
            $layouts=explode(',',$layout_id);
            //$layouts=array(1);
            //design 1

            //for layout 1 and 3
            if(in_array(1,$layouts)||in_array(3,$layouts)){
              $childsCnt=3;
              $mastersCnt=1;
            }

            //for layout 2 and 5
            if(in_array(2,$layouts)||in_array(5,$layouts)){
              $childsCnt=1;
              $mastersCnt=1;
            }

            //for layout 4
            if(in_array(4,$layouts)){
              $mastersCnt=1;
              $masterAfterChild=0;
            }

             $strCodes='';
             $strCodesParent='';
             $gridCount=0;
             $iniotsExccel=[];
             foreach ($Ids as $iots) {  

                  $childsArray=$iots['childs'];
                  $parent=isset($iots['parent']);


                  foreach ($childsArray as $childs) {
                    $qrcodefile=$path.$childs.'_'.time().'.png';

                    $temp=[];
                    $temp['parent']=empty($iots['parent'])?'0':$iots['parent'];
                    $temp['primary_id']=$childs;
                    $temp['product_name']=$data->productName;
                    $temp['material_code']=$data->material_code;
                    $temp['mfg_date']=$data->mfg_date;
                    $temp['location']=$data->location_name;
                    $temp['erp_code']=$data->erp_code;
                    $iniotsExccel[]= $temp;

                    QrCode::format('png')->size(90)->generate($childs, public_path().$qrcodefile);
                    chmod(public_path().$qrcodefile, 0777);
                    $filesTobeDelete[]=public_path().$qrcodefile;
                    if($gridCount%3==0)
                    $strCodes .= '<div class="page-break page">';
                      $iotSerialNo=$iotSerialNo+1;
                      for($i = 0;$i< $childsCnt; $i++){ 
                        $gridCount++;
                        $strCodes .= '<div class="col1">
                                <div class="firstRow"><p style="text-align:left;float:left;">'.($data->material_code).'</p><p class="" style="text-align:right;float:right;">'.($this->genIotSerialNo($iotSerialNo)).'</p></div>
                                <div class="middleRow"><center><img src="'.public_path().$qrcodefile.'" style="height:72px;" ></center></div>
                                <div class="thrdRow"><p style="text-align:center">'.$childs.'</p></div></div>';
                      }   
                      if($gridCount%3==0)
                  $strCodes .= '</div>';
                }

                if($parent!='' && $masterAfterChild){
                  $parent=$iots['parent'];
                    $qrcodefile=$path.$parent.'_'.time().'.png';

                    QrCode::format('png')->size(90)->generate($parent, public_path().$qrcodefile);
                    chmod(public_path().$qrcodefile, 0777);
                    $filesTobeDelete[]=public_path().$qrcodefile;
                    
                    $strCodesParent .= '<div class="page-break page">';
                    $iotSerialNo=$iotSerialNo+1;
                    $strCodesParent .= '<div class="col1">
                              <div class="firstRow"><p style="text-align:left;float:left;">'.($data->material_code).'</p><p class="" style="text-align:right;float:right;">'.($this->genIotSerialNo($iotSerialNo)).'</p></div>
                              <div class="middleRow"><center><img src="'.public_path().$qrcodefile.'" style="height:72px;" ></center></div>
                              <div class="thrdRow"><p style="text-align:center">'.$parent.'</p></div></div>';
                    $strCodesParent .= '<div class="col1">
                    <div class="parentMiddle"><br>';
                      foreach ($childsArray as $key => $value) {
                        $strCodesParent .='<p style="font-size:8px;text-align:center;padding:0px;margin:0px;">'.$value.'</p>';
                      }
                      $strCodesParent.=' </div> </div>';

                    $strCodesParent .= '<div class="col1">
                              <div class="firstRow"><p style="text-align:left;float:left;">'.($data->material_code).'</p><p class="" style="text-align:right;float:right;">'.($this->genIotSerialNo($iotSerialNo)).'</p></div>
                              <div class="middleRow"><center><img src="'.public_path().$qrcodefile.'" style="height:72px;" ></center></div>
                              <div class="thrdRow"><p style="text-align:center">'.$parent.'</p></div></div>';
                  $strCodesParent .= '</div>';
                  $strCodes=$strCodes.$strCodesParent;
                  $strCodesParent='';
                }
              }

              if($masterAfterChild==0){
                 foreach ($Ids as $iots) {  
                    $childsArray=$iots['childs'];
                    $parent=isset($iots['parent']);
                     if($parent!=''){
                       $parent=$iots['parent'];
                      $qrcodefile=$path.$parent.'_'.time().'.png';

                      QrCode::format('png')->size(90)->generate($parent, public_path().$qrcodefile);
                      chmod(public_path().$qrcodefile, 0777);
                      $filesTobeDelete[]=public_path().$qrcodefile;
                      
                      $strCodesParent .= '<div class="page-break page">';
                      $iotSerialNo=$iotSerialNo+1;
                      $strCodesParent .= '<div class="col1">
                                <div class="firstRow"><p style="text-align:left;float:left;">'.($data->material_code).'</p><p class="" style="text-align:right;float:right;">'.($this->genIotSerialNo($iotSerialNo)).'</p></div>
                                <div class="middleRow"><center><img src="'.public_path().$qrcodefile.'" style="height:72px;" ></center></div>
                                <div class="thrdRow"><p style="text-align:center">'.$parent.'</p></div></div>';
                      $strCodesParent .= '<div class="col1">
                      <div class="parentMiddle"><br>';
                        foreach ($childsArray as $key => $value) {
                          $strCodesParent .='<p style="font-size:8px;text-align:center;padding:0px;margin:0px;">'.$value.'</p>';
                        }
                        $strCodesParent.=' </div> </div>';

                      $strCodesParent .= '<div class="col1">
                                <div class="firstRow"><p style="text-align:left;float:left;">'.($data->material_code).'</p><p class="" style="text-align:right;float:right;">'.($this->genIotSerialNo($iotSerialNo)).'</p></div>
                                <div class="middleRow"><center><img src="'.public_path().$qrcodefile.'" style="height:72px;" ></center></div>
                                <div class="thrdRow"><p style="text-align:center">'.$parent.'</p></div></div>';
                    $strCodesParent .= '</div>';
                    $strCodes=$strCodes.$strCodesParent;
                    $strCodesParent='';
                  }

                 }

              }

          $name='PDF-'.$data->user_id.'-'.$data->quatity.'_'.time();
          $str .= $strCodes.$strCodesParent.'</body></html>';

          $path='/download/qrpdfs';
          $htmlfile=trim($path).'/'.trim($name).'.html';
          $pdf=trim($path).'/'.trim($name).'.pdf';
          $htmlfile=str_replace(' ','',$htmlfile);
          $pdf=str_replace(' ','',$pdf);
          File::put(public_path().$htmlfile,$str);
          chmod(public_path().$htmlfile, 0777);

          //echo public_path().$htmlfile; exit;
          exec('/usr/local/bin/wkhtmltopdf  -L 0mm -R 0mm -T 0mm -B 0mm --page-width 3in --page-height 0.9in '.public_path().$htmlfile.' '.public_path().$pdf);
          chmod(public_path().$pdf, 0777);

           $s3 = new s3\S3();
           $s3file=$s3->uploadFile(public_path().$pdf,'download_qrpdf');
           $pdf='/'.$s3file;

          echo $pdflinkA='<a href="http://'.$_SERVER['HTTP_HOST'].'/'.$pdf.'"> click here </a>to download or copy the following URL to download.<br><br><br>';


          // excel file saving start
/*
  $temp['product_name']=$data->productName;
                    $temp['material_code']=$data->material_code;
                    $temp['mfg_date']=$data->mfg_date;
                    $temp['location']=$data->location_name;
*/
          $PrintingBatch = date('Ymd',strtotime($data->mfg_date));
          //$currentDate =date('Y-m-d H:i:s');
          $name = "PDF-".$data->material_code."_".$PrintingBatch;

          Excel::create($name, function($excel) use ($iniotsExccel) {
          $excel->sheet('mySheet', function($sheet) use ($iniotsExccel)
          {
          $sheet->fromArray($iniotsExccel);
          });
          })->store('xlsx', public_path()."/download/qrpdfs");
          $xlsx="/download/qrpdfs/".$name.'.xlsx';
          chmod(public_path().$xlsx, 0777);
          $s3 = new s3\S3();
          $s3file=$s3->uploadFile(public_path().$xlsx,'download_qrpdf');
          @unlink(public_path().$xlsx);
          $xlsx='/'.$s3file;
          // excel file saving end
          $remarks=json_encode(['pdf'=>$pdf,'xlsx'=>$xlsx]);

          $update=DB::table('qr_code_generator')->where('id',$data->id)->update(['status'=>1,'remarks'=>$remarks,'serial_no_start'=>$iotSerialNoIni,'serial_no_end'=>$iotSerialNo]);

        }
      $time_elapsed_secs = microtime(true) - $start;
           } else {
            echo "no Data to run cron";
            break;
           }
      }while($time_elapsed_secs<300);
     
    }
    
    public function generate_quotes(){
       $data=Input::get();
       /*print_r(Session::all());
       exit;*/
  
       if(count($data)>0){
          if(isset($data['grid_userId']) && isset($data['grid_manufactureDate']) && isset($data['grid_Product']) && isset($data['grid_Quantity'])){

            $n=count($data['grid_userId']);
            for ($i=0; $i <$n ; $i++) { 
              $ins_data=array();
              $ins_data['user_id']=$data['grid_userId'][$i];
              $ins_data['userstamp']=Session::get('userId');
              $ins_data['product_id']=$data['grid_Product'][$i];
              $ins_data['mfg_date']=date("Y-m-d",strtotime($data['grid_manufactureDate'][$i]));
              $ins_data['quatity']=$data['grid_Quantity'][$i];
              $insert=DB::table('qr_code_generator')->insertGetId($ins_data);
            }
          }
          return $this->qrgen(1);
         // return Redirect::to('qrcode/qrgen'); 
       }
        // $vendors=DB::table('users as u')->where('u.customer_type',1000)->get(['u.user_id','u.username','u.email','u.location_id']);
        $vendors=DB::table('users as u')
                ->join('locations as l','l.location_id','=','u.location_id')
                ->where('u.customer_type',1000)
                ->get(['u.user_id','u.username as un','u.email','u.location_id','l.location_name as username']);
        return View::make('qrcode/qrgeneratio_view')->with(['vendors'=>$vendors]);
    }

    public function getImportProductDetails(){
      $input=Input::get();
      $data=DB::table('qr_code_generator as q')->join('products as p','p.product_id','=','q.product_id')
                ->join('users as u','u.user_id','=','q.user_id')
                ->leftJoin('users as dn','dn.user_id','=','q.userstamp')
                ->join('locations as l','u.location_id','=','l.location_id');
        if(isset($input['product']) && $input['product']!=''){
          $data=$data->where('q.product_id',$input['product']);
        }
        if(isset($input['vendor'])  && $input['vendor']!='' ){
          $data=$data->where('q.user_id',$input['vendor']);
        }
        if(isset($input['fromDate']) && $input['fromDate']!='' ){
          $data=$data->where('q.mfg_date','>',date("Y-m-d",strtotime($input['fromDate'])));
        }
        if(isset($input['toDate']) && $input['toDate']!='' ){
          $data=$data->where('q.mfg_date','<=',date("Y-m-d",strtotime($input['toDate'])));
        }
        $data=$data->orderBy('id','desc')->get(['q.id as qid','p.material_code','p.name','q.mfg_date','q.quatity','q.status','u.username','q.remarks','q.timestamp','l.location_name','l.erp_code','q.files','q.pdf_files','dn.username as userstamp']);
        $return=[];
        foreach ($data as $key => $value) {
            $temp=[];
            $temp['batch_no']=date('mY',strtotime($value->mfg_date));
            $temp['mfg_date']=date('d-m-Y',strtotime($value->mfg_date));
            $temp['material']=$value->material_code.'-'.$value->name;
            $temp['quatity']=$value->quatity;
            $temp['userstamp']=$value->userstamp;
            $temp['download_pdf']='';
            $temp['download_excel']='';
            $temp['download']='';
             if($value->status==1)
                  $temp['download']='Completed';
                else if($value->status==4)
                  $temp['download']='Process under PDF Generation ';
                else if($value->status==5)
                  $temp['download']='Pdf generate Shortly ';
                else if($value->status==6)
                  $temp['download']='Pdf generate Error <a onclick="regen('.$value->qid.')"><i class="fa fa-refresh " style="font-size:16px;color:red"></i></a> ';
                else if($value->status==2 ||$value->status==3)
                  $temp['download']=$value->remarks==''?'process will start soon':$value->remarks;
                else if($value->status==0)
                  $temp['download']='Process will start soon';
                else 
                  $temp['download']='Missmatch Issue plz contact Technical Team';

            //$temp['username']=$value->username;
            $temp['username']=$value->location_name.'-'.$value->erp_code;
            $temp['genDate']=date("d-m-Y h:i A",strtotime($value->timestamp));
              if($value->status==1||$value->status>=4){
                $date1=date_create(date("Y-m-d",strtotime($value->timestamp)));
                $date2=date_create(date("Y-m-d"));
                $diff=date_diff($date1,$date2);
               if($diff->format("%R%a")>=90){
                $temp['download']='Expried';
               } else {

               // $temp['download']='';               
                $avalblepdfs=[];
                $downloads=explode(',',$value->files.','.$value->pdf_files);
                $temp['download_pdf']=$temp['download_excel']='';
                foreach ($downloads as $index => $file) {
                  if($file!=''&&$file!=','){
                     if(pathinfo($file, PATHINFO_EXTENSION)=='pdf'){
                      $index=substr($file,-5,1);
                      //if(!in_array($index,$avalblepdfs))
                      if(!array_key_exists($index,$avalblepdfs))
                      {
                        $avalblepdfs[$index]='  <a href="'.$file.'" target="_blank" style="    text-decoration: underline;"> '.($index+1).' </a>  ';
                        //$temp['download_pdf'].='  <a href="'.$file.'" target="_blank" style="    text-decoration: underline;"> '.($index+1).' </a>  ';
                      }
                      
                      //   $temp['download_pdf'].='  <a href="'.$file.'" target="_blank"> <i class="fa fa-download " style="font-size:16px;color:red"></i> </a>  ';
                    } else  if(pathinfo($file, PATHINFO_EXTENSION)=='xlsx'){
                      $temp['download_excel'].='  <a href="'.$file.'" target="_blank"  style="    text-decoration: underline;"> 
  <i class="fa fa-download " style="font-size:16px;color:green"></i> </a>  ';
                    }
                  }
                 
                }
                ksort($avalblepdfs);
                $temp['download_pdf']=implode(' ',$avalblepdfs);
                /*$json=json_decode($value->remarks);
                foreach ($json as $key => $value) {
                  if($key=='pdf')
                    $temp['download_pdf']='<a href="'.$value.'" target="_blank"> <i class="fa fa-download " style="font-size:16px;color:red"></i> </a>';
                    //$temp['download'].=' &nbsp; <a href="'.$value.'" target="_blank"> Pdf </a> &nbsp; ';
                   else 
                    $temp['download_excel']='<a href="'.$value.'" target="_blank"> 
<i class="fa fa-download " style="font-size:16px;color:green"></i> </a>';
                    //$temp['download'].=' &nbsp; <a href="'.$value.'" target="_blank"> Excel </a> &nbsp; ';
                }*/
                //$temp['download']='<a href="'.$value->remarks.'" >Pdf</a>';                
               }
              }
              /*else 
              $temp['download']=$value->remarks==''?'process will start soon':$value->remarks;*/
            $return[]=$temp;
        }
        return $return;
        /* echo "<pre>"; print_r($data); exit;*/
        /*'batch_no' 'material' 'quatity' 'username' 'download'*/
    }
    public function qrgen($success=0){
       $vendors=DB::table('users as u')
                ->join('locations as l','l.location_id','=','u.location_id')
                ->where('u.customer_type',1000)
                ->get(['u.user_id','u.username','u.email','u.location_id','l.location_name','l.erp_code']);
       return View::make('qrcode/qrgen')->with(['vendors'=>$vendors,'success'=>$success]);
    }

    public function resetPdf(){
        $data = Input::all();
        if(isset($data['qid'])){
          $update=DB::table('qr_code_generator')->where('id',$data['qid'])->update(['pdf_files'=>"",'status'=>4,'pdf_iteration'=>0]);
          echo json_encode(['status'=>1,'msg'=>'Reset Successfully']);
        }
    }


    public function save2(){
        $startTime = $this->getTime();
      try{
        $status =1;
        $filesTobeDelete=array();
        $message = 'Successfully Printed the Labels';
        //Log::info(__FUNCTION__.' : '.print_r(Input::get("product_id"),true));
        $data = Input::all();
        $data['smallpage']=isset($data['smallpage'])?$data['smallpage']:'';
        $data['excelExport']=isset($data['excelExport'])?$data['excelExport']:'';
        unset($data['_token']);
     //  DB::beginTransaction();
       $mfgId = Session::get('customerId');
       $checkparentQty = DB::table('product_packages')->where('product_id',$data['product_id'])->where('level','=',16002)->pluck('quantity');
       if($checkparentQty){
       $Totalquantity = $data['qty'] / $checkparentQty;
       $level = 1;
         }
         else
         {
          $Totalquantity = $data['qty'];
          $level = 0;
         }
       $pid = $data['product_id'];
       $getMatcode = trim(DB::table('products')->where('product_id',$pid)->pluck('material_code'));
       $getPrdName = DB::table('products')->where('material_code',$getMatcode)->pluck('name');
       $getPrimaries = DB::table('eseal_'.$mfgId)->where('pid',$data['product_id'])->lists('primary_id');
       $transitionId = DB::table('transaction_master')->where('name','=','Label Printing')->pluck('id');
       //$transitionTime = $this->getDate();
       $transitionTime = date("Y-m-d H:i:s",strtotime($data['MFG_DATE']));
       $getUser = Session::get('userId');
       $access_token = DB::table('users_token')->where('user_id',$getUser)->where('module_id','=',4002)->pluck('access_token');
       $getLoc = DB::table('users')->where('user_id',$getUser)->pluck('location_id');
       $locName = DB::table('locations')->where('location_id',$getLoc)->pluck('location_name');
       $erp_code = DB::table('locations')->where('location_id',$getLoc)->pluck('erp_code');
       unset($data['product_id']);
       unset($data['qty']);
       $batch_no = date("mY",strtotime($data['MFG_DATE']));//date("mY");
       $data['batch_no'] =$batch_no; 
      $attributes = json_encode($data);
      $moduleId = 4002;
        //Log::info("save2 @ Start Time".microtime(true));
        if($level == 1 ){
        //Log::info("save2 @ REQUEST 1 started");
        $request = Request::create('scoapi/bindMapWithEseals', 'POST', array('module_id'=> $moduleId ,'access_token'=>$access_token,'attributes'=>$attributes,'level'=>$level,'pid'=>$pid,'parentQty'=>$Totalquantity,'transitionTime'=>$transitionTime,'transitionId'=>$transitionId));
      $originalInput = Request::input();//backup original input
      Request::replace($request->input());
      //Log::info($request->input());
      $response1 = Route::dispatch($request)->getContent();//invoke API
     //Log::info("save2 @ REQUEST 1 ended");
     // print_r($response1);
     // exit;
      $response = json_decode($response1);
      $Ids = json_encode($response->Data);
      }

      if($level == 0 ){
           //Log::info("save2 @ REQUEST 2 started");
        $request = Request::create('scoapi/bindMapWithEseals', 'POST', array('module_id'=> $moduleId ,'access_token'=>$access_token,'attributes'=>$attributes,'level'=>$level,'pid'=>$pid,'parentQty'=>$Totalquantity,'transitionTime'=>$transitionTime,'transitionId'=>$transitionId));
      $originalInput = Request::input();//backup original input
      Request::replace($request->input());
      //Log::info($request->input());
      $response2 = Route::dispatch($request)->getContent();//invoke API
      //Log::info("save2 @ REQUEST 2 ended");
      //exit;
      $response = json_decode($response2);
      $Ids = json_encode($response->Data);
        }
     /* echo "count :".count($Ids).'<br>time:'.microtime(true);
      echo "<pre>";
      print_r($Ids);
       exit;*/

        $Ids = json_decode($Ids,true);
        if(count($Ids)<=0){
          //Log::info("got error while geting ids".$response1.$response2);
         /* echo "<pre>";
          print_r($response1);
          print_r($response2);
          exit;*/
          throw new Exception("Ids Not Availabe", 1);
        }

          
       //dd($Ids);die;
       /*
        primary_id  parent  Product Name  Material Code   Mfg Date  Location Type   Location  Location Erp Code   Category  Available Inventory   Storage Location  */
          $iniots=[];
        if(trim($data['excelExport'])){
          foreach ($Ids as $key => $value) {
           /* if(empty($value['parent'])){
              $iniots[]=$value['parent'];
            }*/
            $parent=empty($value['parent'])?'0':$value['parent'];
            foreach ($value['childs'] as $ckey => $cvalue) {
              /*$iniots[]=$cvalue;*/
              $temp=[];
              $temp['primary_id']=$cvalue;
             // $temp['parent']=$parent;
              $temp['parent']=$parent;
              $temp['product_name']=$getPrdName;
              $temp['material_code']=$getMatcode;
              $temp['mfg_date']=$transitionTime;
              $temp['location']=$locName;
              $temp['erp_code']=$erp_code;
              $iniots[]= $temp;
            }
          }
          /*ob_end_clean();
          ob_start();*/
        $PrintingBatch = date('YmdHis');
        $currentDate =date('Y-m-d H:i:s');
        $name = "PDF-".$getMatcode."_".$PrintingBatch;

         Excel::create($name, function($excel) use ($iniots) {
            $excel->sheet('mySheet', function($sheet) use ($iniots)
            {
                $sheet->fromArray($iniots);
            });
          })->store('xlsx', public_path()."/download/qrpdfs");
         $pdf="/download/qrpdfs/".$name.'.xlsx';
          chmod(public_path().$pdf, 0777);
          $s3 = new s3\S3();
          $s3file=$s3->uploadFile(public_path().$pdf,'download_qrpdf');
          @unlink(public_path().$pdf);
          @unlink(public_path().$htmlfile);
          $pdf='/'.$s3file;
         /* Excel::create('ProductBulkUpdateErrorLog'.$time, function($excel) use($error_messages) {
            return $excel->sheet('New sheet', function($sheet) use($error_messages){
            $sheet->loadView('products.productsbulkimporterror',array('error_messages'=>$error_messages));
            });
          })->store('xlsx', public_path()."/download");*/


        } else {


            $PrintingBatch = date('YmdHis');
        $currentDate =date('Y-m-d H:i:s');
        $name = "PDF-".$getMatcode."_".$PrintingBatch;
        $str = '';
      //  $str .= '<html><body '.(trim($data['smallpage'])!=''?'style="width:350px !important;':'').'"><style>
        $str .= '<html><body '.(trim($data['smallpage'])!=''?'style="width:350px !important;':'').'"><style>

         .page_break { page-break-before: always; } 
              .tile_div{
                  text-align: center;
                  width: 100%;
              }
              .tile_table{
                  border-collapse:collapse;
              }
              .tile_table tr, td{
                padding-top:0px;
              }
             .page-break{ 
              display: block !important; 
              clear: both !important; 
              page-break-after:always !important;} 

       </style>';
      $primaryQty = DB::table('product_packages')->where(['level'=>'16001','product_id' =>$pid])->pluck('dup_qty');
      $parentQty = DB::table('product_packages')->where(['level'=>'16002','product_id' =>$pid])->pluck('dup_qty');
       $chkprimaryqty =  DB::table('product_packages')->where(['level'=>'16001','product_id' =>$pid])->pluck('quantity');
       $chkparentqty =  DB::table('product_packages')->where(['level'=>'16002','product_id' =>$pid])->pluck('quantity'); 
       if(!$parentQty){
        $parentQty = 0;
       }
       if(!$chkparentqty){
        $chkparentqty = $chkprimaryqty;
       }
       $pageno=0; 
         
  $primaryQty=3;
  $parentQty=4;
  $path = '/download/qrimages/temp_codes/';
$sno=0;
  foreach ($Ids as $iots) {   
$iotSerialNo=0;
    $pageChange=0;
    //Log::info("ids running".$pageno.'-'.$sno);
    if($pageno!=0)
      $str.='<div class="page_break"></div>';
    if($pageno==0 || $data['smallpage']=='')
      $str.='<table cellspacing="0" cellpadding="0"  class="tile_table" width="100%" style="clear:both">
              <tr>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
              </tr>
              <tr>
                  <th colspan="3" style="width:85%;text-align:left;" >Vendor Name:'.$locName.'</th>
                  <th style="width:15%;text-align:right;" >Page No :'.(++$pageno).'</th>
              </tr>
              <tr>
                  <th colspan="2" style="text-align:left;width:50%" >Material Code: '.$getMatcode.'</th>
                  <th colspan="2" style="text-align:right;width:50%" >Batch No: '.$PrintingBatch.'</th>
              </tr>
              <tr>
                  <th colspan="2" style="text-align:left;width:50%" >Product Name: '.$getPrdName.'</th>
                  <th colspan="2" style="text-align:right;width:50%" >Date : '.$currentDate.'</th>
              </tr>
               <tr>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
              </tr>
              </table>';

      $str.='<table cellspacing="0" cellpadding="0"  class="tile_table" width="100%">';
      $afteIotChilds=0;
      if($iots['childs']){
        $afteIotChilds=1;
        
        $x = 0;
          foreach ($iots['childs'] as $childs) {
            $iotSerialNo++;
            $qrcodefile=$path.$childs.'_'.time().'.png';
            //$qrcodefile=$path.'/'.$childs.'.png';
            QrCode::format('png')->size(90)->generate($childs, public_path().$qrcodefile);
            chmod(public_path().$qrcodefile, 0777);
            $filesTobeDelete[]=public_path().$qrcodefile;
            
            $str .= '<tr style="height:120px;">';
              for($i = 0;$i< $primaryQty ; $i++){ 
                $str .= '<td><img src="'.public_path().$qrcodefile.'" style="height:90px;"><p style="padding: 0px 0px;margin-top: 0px;"><font size="1"><b>'.$childs.'</b></font></p></td>';
              }   
            $str .= '</tr>';
            $x = ++$x;

           if($iotSerialNo%7==0){

            $str .= '</table>';
            $str.='<div class="page_break"></div>';
            if($data['smallpage']=='')
             $str.='<table cellspacing="0" cellpadding="0"  class="tile_table" width="100%" style="clear:both">
              <tr>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
              </tr>
              <tr>
                  <th colspan="3" style="width:85%;text-align:left;" >Vendor Name:'.$locName.'</th>
                  <th  style="width:15%;text-align:right;" >Page No :'.(++$pageno).'</th>
              </tr>
              <tr>
                  <th colspan="2" style="text-align:left;width:50%" >Material Code: '.$getMatcode.'</th>
                  <th colspan="2" style="text-align:right;width:50%" >Batch No: '.$PrintingBatch.'</th>
              </tr>
              <tr>
                  <th colspan="2" style="text-align:left;width:50%" >Product Name: '.$getPrdName.'</th>
                  <th colspan="2" style="text-align:right;width:50%" >Date : '.$currentDate.'</th>
              </tr>
               <tr>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
              </tr>
              </table>';

            $str.='<table cellspacing="0" cellpadding="0"  class="tile_table" width="100%">';
           }
        }
      }

      if(!empty($iots['parent']) && $afteIotChilds ){
          $iotSerialNo++;
          $qrcodefile=$path.$iots['parent'].'_'.time().'.png';
          QrCode::format('png')->size(130)->generate($iots['parent'], public_path().$qrcodefile);
          chmod(public_path().$qrcodefile, 0777);
          $filesTobeDelete[]=public_path().$qrcodefile;

            $str .= '</table>';
         /* if($iotSerialNo%6==0 && $data['smallpage']==''){

            $str.='<div class="page_break"></div>';
            if($data['smallpage']=='')
            $str.='<table cellspacing="0" cellpadding="0"  class="tile_table" width="100%;">
              <tr>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
              </tr>
              <tr>
                  <th colspan="3" style="width:85%;text-align:left;" >Vendor Name:'.$locName.'</th>
                  <th  style="width:15%;text-align:right;" >Page No :'.(++$pageno).'</th>
              </tr>
              <tr>
                  <th colspan="2" style="text-align:left;width:50%" >Material Code: '.$getMatcode.'</th>
                  <th colspan="2" style="text-align:right;width:50%" >Batch No: '.$PrintingBatch.'</th>
              </tr>
              <tr>
                  <th colspan="2" style="text-align:left;width:50%" >Product Name: '.$getPrdName.'</th>
                  <th colspan="2" style="text-align:right;width:50%" >Date : '.$currentDate.'</th>
              </tr>
               <tr>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
              </tr>
              </table>';


           }*/ 
            $str.='</table><table cellspacing="0" cellpadding="0"  class="tile_table" width="100%">';

          $str .= '<tr><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr><tr>';
          for($i = 0;$i< $parentQty ; $i++){ 
            $str .='<td width="3px"><img src="'.public_path().$qrcodefile.'"';
              if($i%2 != 1){
                 $str .= 'style="padding:26px;border-right: 1px solid #000;"/><p style="padding: 11px 38px;margin-top: -39px;">';
              }else{
                $str .= 'style="padding:15px;"/><p style="padding: 2px 22px;margin-top: -16px;"> ';
              }
             $str .= '<font size="2"><b>'.$iots['parent'].'</b></font></p></td>';
          }
      }
  $str .= '</tr></table>';

    }
          $str .=  '</body></html>';
   
   //ECHO $str; exit;
    if($str!=''){
    /* echo $str;
     exit;*/
     // sleep(10);
     
     //Log::info("save2 pdf creationg started ");
     //Log::info("time:".$startTime."===>".$this->getTime());
       $path='/download/qrpdfs/';
      //File::isDirectory(public_path().$path) or File::makeDirectory(public_path().$path, 0777, true, true);
      //chmod(public_path().$path, 0777);
      $htmlfile=trim($path).'/'.trim($name).'.html';
      $pdf=trim($path).'/'.trim($name).'.pdf';
      $htmlfile=str_replace(' ','',$htmlfile);
      $pdf=str_replace(' ','',$pdf);
     // chmod(public_path().$pdf, 0777);
      //File::put(public_path().$pdf, PDF::load($str)->output());
      //usr/local/bin/wkhtmltopdf google.com test.pdf
      File::put(public_path().$htmlfile,$str);
      chmod(public_path().$htmlfile, 0777);
      /*echo 'usr/local/bin/wkhtmltopdf '.public_path().$htmlfile.' '.public_path().$pdf;
      exit;*/
     
      if(trim($data['smallpage'])){
        //small pages
        exec("/usr/local/bin/wkhtmltopdf --page-size 'Letter' ".public_path().$htmlfile." ".public_path().$pdf);
      }else {

        exec('/usr/local/bin/wkhtmltopdf '.public_path().$htmlfile.' '.public_path().$pdf);
      }
      //File::put(public_path().$pdf, PDF::load($str)->output());
      chmod(public_path().$pdf, 0777);
      $s3 = new s3\S3();
      $s3file=$s3->uploadFile(public_path().$pdf,'download_qrpdf');
      @unlink(public_path().$pdf);
      @unlink(public_path().$htmlfile);
      $pdf='/'.$s3file;
      $pdflinkA='<a href="http://'.$_SERVER['HTTP_HOST'].'/'.$s3file.'"> click here </a>to download or copy the following URL to download.<br><br><br>
link to download pdf:  http://'.$_SERVER['HTTP_HOST'].'/'.$s3file;
     // echo $pdf; exit;

$username = DB::table('users')->where('user_id',Session::get('userId'))->pluck('firstname');

       if($pdf!=''){
        $body='Dear '.$username.',<br><br>Your Requested QR-code pdf generated successfully. Please'.$pdflinkA;
       $email=Session::get('userName');
       $subject='QR-code pdf generated successfully';
      /*   $status1 = Mail::send('emails.tracker',array('body' => $body), function($message) use ($email,$subject)
        {
            $message->to($email)->subject($subject);
        }); */   
      }
      
     // sleep(10);
      foreach ($filesTobeDelete as $key => $value) {
         //Log::info("UNLINKING FILE".$value);
        @unlink($value);
      }

        } else {
      throw new Exception("Error Processing Request", 1);
      }

      
    } 
    
  //  DB::commit();
  }
  catch(Exception $e){
  $status =0;
  $pdf='';
  $data = [];
  //DB::rollback();
 // DB::rollback();
  $message = $e->getMessage();
  }
  $endTime = $this->getTime();
  //Log::info(__FUNCTION__.' Finishes execution in '.($endTime-$startTime));
  //Log::info(['Status'=>$status,'Message'=>$message,'Data'=>$data]);
  return Response::json(['Status' => $status, 'Message' => 'Server: ' . $message, 'Data' => $pdf]);
}


public function checkpdf() {
      $pdf = PDF::loadView('qrcode/test',array());
      return $pdf->download('qr.pdf');
}

  public function checkqty(){
    $data = Input::all();
    //dd($data);

    try
        {
          
            //$customerId = isset($data['manufacturer_id']) ? $data['manufacturer_id'] : 0;
            $tableName = isset($data['table_name']) ? $data['table_name'] : '';
            $code = isset($data['code']) ? $data['code'] : '';
             $product_id = isset($data['product_id']) ? $data['product_id'] : 0;
             $qty = isset($data['field_name']) ? $data['field_name'] : 0;

             if($qty>21000 || $qty<=0)
               return json_encode(['valid' => False,'message' => 'The Qty should beetween 1 and 1000']);

            //dd($qty);die;
             $parentchk = DB::table('product_packages')->where('level','=',16002)->where('product_id',$product_id)->pluck('id');
                if($product_id  > 0 ){
                  if($parentchk){
                    $level1 = DB::table($tableName)->where('level','=',16002)->where('product_id','=',$product_id)->pluck('quantity');
                    $checklvl1qty  = $qty / $level1;
                    //dd($checklvl1qty);die;
                  }
                  else{
                    $checklvl1qty = $qty;
                  }
                    if(is_float($checklvl1qty)){
                      
                      return json_encode(['valid' => False,'message' => 'The Qty should be multiples of PKG qty.']);

                    }
                    else{
                      return json_encode(['valid' => True]);
                    }
                    //if(!$ch)
                    if(!$level1){
                      return json_encode(['valid' => True,'message' => 'Primaries Printing']);
                    }
                   
                }
            else{
                return json_encode(['valid' => false,'message' => 'Product not configured']);
            }
            
            return json_encode([ 'valid' => false ]);
        } catch (\ErrorException $ex) {
            

            return json_encode([ 'valid' => false, 'message' => $ex->getMessage() ]);
        }

     } 
   

}