<?php
namespace App\Http\Controllers;
//ini_set('memory_limit', '-1');

use App\Repositories\RoleRepo;
use App\Repositories\CustomerRepo;
use Illuminate\Http\Request;
use App\Repositories\SapApiRepo;
use App\Repositories\ApiRepo;
use App\Repositories\OrderRepo;
use App\Repositories\ConnectErp;
use App\Models\Conversions;

use App\Models\Products\Products;

use Session;
use DB;
use View;
use Input;
use Validator;
use Redirect;
use Log;
use Exception;
//use Request;


class ProductionOrderController extends BaseController 
{

    public $_request;
		public function __construct(RoleRepo $roleAccess,CustomerRepo $custRepo,SapApiRepo $sapRepo, ApiRepo $apiRepo,Request $request) 
	{
		if(isset($_SERVER['HTTP_X_FORWARDED_FOR'])){
			$_SERVER['REMOTE_ADDR']=$_SERVER['HTTP_X_FORWARDED_FOR'];
		}
		$this->roleAccess = $roleAccess;
		$this->custRepo = $custRepo;
		$this->sapRepo = $sapRepo;
		$this->_apiRepo = $apiRepo;	
		$this->roleAccess = $roleAccess;
        $this->_request = $request;       
        $this->mfg_id=Session::get('customerId');
        $this->user_id=Session::get('userId');
        $this->esealErp=DB::table('eseal_customer')->where('customer_id',$this->mfg_id)->value('eseal_erp');
         //      $this->mfg_id=9;	
	}
	public function getPriceLots($productId){
		 $data=DB::table('price_lot');
		if($productId!=0)
			$data->where('product_id',$productId);
		$data=$data->get()->toArray();
		return $data;	
	}
	public function checkpo_orderExists($po_order){
		$data=DB::table('production_orders');
		$data->where('eseal_doc_no',$po_order);
		$data=$data->count();
		return $data;	
	}
	public function getUom($product_id,$returnArray=0){
		$data=DB::table('conversions');
		$data->where('product_id',$product_id);
		$data->where('alt_uom','like','z%');
		$data=$data->pluck('alt_uom','id')->toArray();
		if($returnArray)
		return $data;
		else 
		return json_encode($data);
	}


public function getPoQuantity(){
	try{
		$status =1;
		$message ='Data successfully retrieved';
		$qty ='';
		$bindQty = '';
		$confirmQty = '';
                $material_code = '';
                $description ='';
		$po_number = trim($this->_request->input('po_number'));

		if(empty($po_number))
			throw new Exception('PO number not passed');

		$mfgId = (int) $this->mfg_id;

		if($mfgId==0)
			throw new Exception("session not available", 1);
			
		$poDetails=DB::table('production_orders')->where(function($query) use($po_number){
			$query->where('erp_doc_no', '=', $po_number)->orWhere('eseal_doc_no', '=', $po_number);
		})->first(); 

	    $description1 = Products::where('product_id',$poDetails->product_id)->get(['description','multiPack'])->toarray();

		if(count($description1)==0)
		   	throw new Exception('The material in PORDER doesnt exist in the system');

		   	$po_qty=$poDetails->qty;
		   	$po_uom=$poDetails->order_uom;
		   	$createdDt=date("d-m-Y",strtotime($poDetails->timestamp));
		   				
			$convt=new Conversions();
		   	$qty=$convt->getUom($poDetails->product_id,$poDetails->qty,$poDetails->order_uom); 
			


//		   	$qty=$convt->getUom($poDetails->product_id,$poDetails->qty,$poDetails->order_uom); 
			$description = $description1[0]['description'];
			$multiPack = $description1[0]['multiPack'];


			   	if($multiPack){
                 $bindQty = DB::table('eseal_'.$mfgId)->where(['po_number'=>$po_number,'level_id'=>0,'is_active'=>1])->sum('pkg_qty');
                 $confirmQty = DB::table('eseal_'.$mfgId)->where(['po_number'=>$po_number,'level_id'=>0,'is_active'=>1])->where('is_confirmed','!=',0)->sum('pkg_qty');
                 $eSealCnfQty = DB::table('eseal_'.$mfgId)->where(['po_number'=>$po_number,'level_id'=>0,'is_active'=>1])->where('eseal_confirmed','!=',0)->sum('pkg_qty');
			   	}
                else{
                 $bindQty = DB::table('eseal_'.$mfgId)->where(['po_number'=>$po_number,'level_id'=>0,'is_active'=>1])->count('eseal_id');
                 $confirmQty = DB::table('eseal_'.$mfgId)->where(['po_number'=>$po_number,'level_id'=>0,'is_active'=>1])->where('is_confirmed','!=',0)->count('eseal_id');
                /* $confirmQty = DB::table('eseal_'.$mfgId)->where(['po_number'=>$po_number,'level_id'=>0,'is_active'=>1])->where('is_confirmed','!=',0)->sum('pkg_qty');*/
                 $eSealCnfQty = DB::table('eseal_'.$mfgId)->where(['po_number'=>$po_number,'level_id'=>0,'is_active'=>1])->where('eseal_confirmed','!=',0)->count('eseal_id');
                }
                //echo $confirmQty;

                /*if($erp_doc_no!=0){
        	$po_number=DB::table('production_orders')->where('erp_doc_no','=',$erp_doc_no)->first();
        } 
        else if($eseal_doc_no!=0){
        	$po_number=DB::table('production_orders')->where('eseal_doc_no','=',$eseal_doc_no)->first();
        }           	
        $pallets_packed=DB::table('eseal_'.$this->mfg_id)
         		->where('po_number','=',$erp_doc_no)

         		->where('is_active',1)->count();
        */
         /*------conversion----------*/       
          $cartons_confm=$convt->getPALtoZ01($confirmQty,$poDetails->order_uom,$poDetails->product_id);
          $eaches_confirmed=$convt->getZ01toEA($poDetails->product_id,$cartons_confm,$poDetails->order_uom);
          /*----------------------------*/

/*	return json_encode(['Status'=>$status,'Message'=>$message,'qty'=>$qty,'createdDt'=>$createdDt,'po_qty'=>$po_qty,'po_uom'=>$po_uom,'packedqty'=>$convt->getUom($poDetails->product_id,$bindQty),'material_code'=>$material_code,'description'=>$description,'confirmQty'=>$convt->getUom($poDetails->product_id,$confirmQty), 'remark' => $poDetails->remarks, 'eSealCnfQty'=>$convt->getUom($poDetails->product_id,$eSealCnfQty)]);
*/
return json_encode(['Status'=>$status,'Message'=>$message,'qty'=>$qty,'createdDt'=>$createdDt,'po_qty'=>$po_qty,'po_uom'=>$po_uom,'packedqty'=>$bindQty,'material_code'=>$material_code,'description'=>$description,'confirmQty'=>$confirmQty, 'remark' => $poDetails->remarks, 'eSealCnfQty'=>$convt->getUom($poDetails->product_id,$eSealCnfQty),'çonfirm_cartons'=>$cartons_confm,'confirm_EA'=>$eaches_confirmed]);
	}
	catch(Exception $e){
		$status =0;
		$message = $e->getMessage();
	return json_encode(['Status'=>$status,'Message'=>$message]);
	}
//    log::info(['Status'=>$status,'Message'=>$message,'qty'=>$qty,'packedqty'=>$convt->getUom($poDetails->product_id,$bindQty),'material_code'=>$material_code,'description'=>$description,'confirmQty'=>$confirmQty,'eSealCnfQty'=>$eSealCnfQty]);
}
	public function createOrder(){
		try{
		$data=$this->_request->all();
		//print_r($data);exit;
		$errors=[];
		$insert=0;
		$product_erp=$loc_erp='';
		if(!isset($data['product']) || $data['product']=='')
			$errors[]='Please Select Product';

		if(!isset($data['vendor']) || $data['vendor']=='')
			$errors[]='Please Select vendor';
		if(!isset($data['orderQty']) || $data['orderQty']=='')
			$errors[]='Please Enter Quatiry';
		if(!isset($data['uom']) || $data['uom']=='')
			$errors[]='Please Enter UOM';
		if(!isset($data['remarks']))
			$data['remarks']='';

		if(count($errors)>0){
			throw new Exception("few mandatory fields are missing", 1);
		}

		$product_erp=DB::table('products')->where('product_id',$data['product'])->value('material_code');
		//print_r($product_erp);exit;
		$loc_erp=DB::table('locations')->where('location_id',$data['vendor'])->value('erp_code');

		
		$is_erp=0;
		$erp_doc_no=0;
		$createEseal=1;
		$data['uom_value']=DB::table('conversions')->where('id',$data['uom'])->value('alt_uom');
		if($this->esealErp!=1){
			$createEseal=0;
			// cal erp to create po
			$access_token=DB::table('users_token')->where('user_id',session::get('userId'))->where('module_id',4002)->value('access_token');
			$inputData=array('module_id' =>4002,'access_token' => $access_token, 'plant_id' => $loc_erp,'type' => 'create_po_order','object_id' => $product_erp,'action' =>'create_po_order','createOrder'=>1,'order_quantity'=>$data['orderQty'],'order_uom'=>$data['uom_value'], 'remarks' => $data['remarks']);
			//echo "<pre/>";
			//print_r($inputData);exit;
			$req = Request::create('/scoapi/notifyEseal','POST',$inputData);
			$originalInput=$this->_request->all();
			$this->_request->replace($req->all());
			$res = app()->handle($req);
			$res2 = $res->getContent();
			$res2=json_decode($res2);
			if($res2->Status){
				$insert=1;
				$errors[]='Order Added Successfully';
			}
			else{
				$errors[]=$res2->Message;
//				$createEseal=1;
			}
		}  
		
		if($createEseal){
			$newPo=0;
			do{
				sleep(rand(0,4));
				$newPo='8'.time();
				//print_r($newPo);exit;$is_erp
			}while($this->checkpo_orderExists($newPo));

		$insert=DB::table('production_orders')->insertGetId(['product_id'=>$data['product'],'location_id'=>$data['vendor'],'eseal_doc_no'=>$newPo,'erp_doc_no'=>$erp_doc_no,'order_uom'=>$data['uom_value'],'qty'=>$data['orderQty'],'manufacturer_id'=>$this->mfg_id,'is_confirm'=>0,'remarks'=>$data['remarks'],'is_eseal'=>1,'is_erp'=>1]);

		}


		if($insert){
			return redirect('https://'.$_SERVER['HTTP_HOST'].'/production_orders?result=1|Order Added Successfully');			 
		}
//		return $this->getOrders();
		} catch(Exception $e){
			array_unshift($errors , $e->getMessage());
		}
			return redirect('https://'.$_SERVER['HTTP_HOST'].'/production_orders?result=0|'.implode('|',$errors));
		
		//return $this->getOrders($errors);

	}
		
	public function getOrders($errors=[]){	
		$errors=explode('|', $this->_request->get('result'));	
		
		$bindData=[];
        parent::Breadcrumbs(array('Home'=>'/','Process Orders'=>'#'));
        $data=DB::table('production_orders as po')->join('products as p','p.product_id','=','po.product_id')->join('locations as l','po.location_id','=','l.location_id')->where('po.manufacturer_id',$this->mfg_id)->get(['p.material_code','l.erp_code as location_erp_code',DB::raw('IF(po.erp_doc_no = 0, po.eseal_doc_no, po.erp_doc_no) as order_no'),'qty','order_uom'])->toArray();
        $bindData['data']=json_encode($data);
        $user_location=DB::table('users')->where('user_id',$this->user_id)->value('location_id');
        $vendors=DB::table('locations')->where('location_id',$user_location)->get(['location_id','location_name','erp_code'])->toArray();
        $bindData['vendors']=$vendors;
        $products=DB::table('products as p')->join('product_locations as pl','pl.product_id','=','p.product_id')->where('p.manufacturer_id',$this->mfg_id)->where('pl.location_id',$user_location)->get()->toArray();
        $shifts_id=DB::table('master_lookup')->where('name','shifts')->pluck('id'); 
        $shifts=DB::table('master_lookup')->where('parent_lookup_id',$shifts_id)->get(['name','id'])->toArray();
        $bindData['products']=$products;
        //$uoms=array(['id'=>1,"name"=>"Each"],['id'=>2,"name"=>"Lt"]);
        $bindData['shifts']=$shifts;
        $bindData['uoms']=[];
        $bindData['errors']=$errors;        
    	return View::make('production_order.production_orders')->with($bindData);
	}

	public function getPOorders(Request $request,$p_id,$l_id){

		$p_id = trim($p_id);
		// 
		// if(($p_id  && $l_id) !=0 ){
			//echo "hai".$p_id."hh".$l_id;exit;
	//$x[]=	echo '<button>hai</button>';
		
	$product_orders= DB::table('production_orders as po')
         ->join('products as p','po.product_id','=','p.product_id' )
//         ->leftJoin('po_confirm as pc','pc.po_number','=','po.erp_doc_no')
//         ->leftJoin('po_confirm_queue as pcq','pc.po_number','=','po.erp_doc_no')
         ->leftJoin('locations as l','po.location_id','=','l.location_id');
        //->where('po.location_id',$l_id)
        $dmval = explode(',', $p_id);
     	if ($p_id != 'null')
     	{
     		if (count($dmval) == 1)
     		{
     			$product_orders= $product_orders->where('po.product_id',$p_id);
         	}
         	if (sizeof($dmval) > 1)
         	{
         		Log::info('product-id-values - '.implode(',', $dmval));
         		/*$product_orders= $product_orders->whereIn('po.product_id', [ implode(',', $dmval) ]);*/
         		$product_orders= $product_orders->whereIn('po.product_id', $dmval);
         	}
     	}
     	// print_r($product_orders);exit;
        // ->where('po.product_id',$p_id);
        // ->whereIn('products.product_id', ["$p_id"])    
        $product_orders= $product_orders->select(DB::raw("p.material_code,p.description,l.erp_code,DATE_FORMAT(po.timestamp, '%d-%m-%Y %H:%i:%s') as date,
        	CONCAT('<a 
         data-href=\"javascript:void(0)\" onclick = \"createOrderpopup(',IF(po.erp_doc_no = 0, po.eseal_doc_no, po.erp_doc_no),')\">',
        	IF(po.erp_doc_no = 0, po.eseal_doc_no, po.erp_doc_no),'</a>') as order_no,
        	
        	concat(po.qty,' (',po.order_uom,') ') as qty,po.qty as po_qty,IF(po.is_erp = 1 and po.is_eseal=1,'Hybrid',if(po.is_erp,'ECC','eSeal')) as po_type,IF(po.is_confirm=1,'closed','open')as po_status,po.order_uom,l.location_id,p.product_id,p.ean,CONCAT('<a 
         data-href=\"javascript:void(0)\" onclick = \"createOrderpopup(',eseal_doc_no,erp_doc_no,')\"><span class=\" badge default\"><i class=\"fa fa-eye\"></i></span></a><span style=\"padding-left:10px;\" ></span>')  as actions,(select concat(count(primary_id),' (PAL) ') as packed_qty from eseal_6 where CASE WHEN po.erp_doc_no = 0 then po_number=po.eseal_doc_no else po_number=po.erp_doc_no END ) as packed_qty "))->orderBy('po.timestamp','desc')->where('po.location_id',$l_id)->get()->toArray();
        // $production_orders = $production_orders;
		
		foreach ($product_orders as $key => $value) {
			$convet = new Conversions();
			$t_qty=$convet->getUom($value->product_id,$value->po_qty,$value->order_uom);
			$product_orders[$key]->qty.=' <span <i class="fa fa-arrows-h" style="size:3px;"></i></span> '.ceil($t_qty)."(PAL)";		
       	}
//        	$product_orders= DB::table('production_orders as po')
//         ->join('products as p','po.product_id','=','p.product_id' )
//          // ->join('po_confirm as pc','pc.po_number','=','po.erp_doc_no')
//          ->join('po_confirm_queue as pcq','pc.po_number','=','po.erp_doc_no')
//         ->join('eseal_6 as e ','e.po_number','=','po.erp_doc_no')
//         ->select(DB::raw("p.material_code,l.erp_code,pc.status,IF(po.is_erp = 0, po.eseal_doc_no, po.erp_doc_no) as order_no,po.qty,po.order_uom,p.product_id,pcq.qty as packed_qty,p.ean,

//          CONCAT('<a 
//          data-href=\"javascript:void(0)\" onclick = \"createOrderpopup(',eseal_doc_no,erp_doc_no,')\"><span class=\" badge default\"><i class=\"fa fa-eye\"></i></span></a><span style=\"padding-left:10px;\" ></span>')  as actions "),DB::RAW("SELECT COUNT(e.primary_id) FROM  eseal_6 e  WHERE e.po_number= 1103522) as pkg_qty")       
//         ->where('po.location_id',$l_id)
//         ->where('po.product_id',$p_id)       
// 		->get()
//        	->toArray();
       	
			
// echo "<prev>";
//                 print_r($product_orders);exit;
//$y=array_merge($x,$product_orders);
        //$dataArr = [];
               // $dataArr['product_orders']=
        return json_encode($product_orders);
        // foreach($product_orders as $po) {
                            
        //             $dataArr[] = array(
        //                     'material_code'=>$po->material_code,
        //                     'erp_code'=>$po->erp_code,
        //                     'order_no'=>$po->order_no,
        //                     'qty'=>$po->qty,
        //                     'order_uom'=>$po->order_uom,
        //                     'location_id'=>$po->location_id
        //                     // 'action'=>$po->action
        //             );
        //         }
        //return View::make('production_orders.production_orders')->with($dataArr);


       //return $dataArr;
		//s}
}

public function cancelOrder_old(){
	try{

	$data=$this->_request->input();
	$canId=$data['can_id'];
	$confirmData=DB::table('po_confirm')->where('id',$canId)->get()->toArray();
	print_r($confirmData);
	if(count($confirmData)>0){
		$confirmData=$confirmData[0];
		print_r($confirmData);
		$params='';
		$method = 'orderReversal';
		$methodType='POST';
		$inputArray=[];
		$inputArray['order_number']=$confirmData->po_number;
		$inputArray['confirmation_number']=$confirmData->reference_value;
		$inputArray['confirmation_counter']=$confirmData->is_confirmed;
		$inputArray['reversal_reson']=$data['reson'];
		$body=array('order_reversal'=>$inputArray);
		$this->erp=new ConnectErp($this->mfg_id);
		$result=$this->erp->request($method,$params,$body,$methodType);
		$result=json_decode($result);
		if($result->status){
			print_r($confirmData);
			DB::table('eseal_'.$this->mfg_id)->where('reference_value',$confirmData->reference_value)->where('is_confirmed',$confirmData->is_confirmed)->update(['eseal_confirmed'=>0,'is_confirmed'=>0]);
			DB::table('po_confirm_queue')->where('id',$confirmData->q_ref)->update(['status'=>5]);
						print_r($confirmData);

			/*DB::table('production_orders')->where('erp_doc_no',$confirmData->po_number)->where('eseal_doc_no',$confirmData->po_number)->update(['is_confirm'=>1]);*/
			DB::table('production_orders')->where(function ($query) {
                $query->where('erp_doc_no',$confirmData->po_number)->orWhere('eseal_doc_no',$confirmData->po_number)->update(['is_confirm'=>1]);
            });
            echo ($result->message);
						print_r($confirmData);

/*			echo "Reversed Successfully  ".$result->message;
*/		} else {
			echo "Not Reversed,reson:E-".$result->message;
		}
	} else 
	throw new Exception("po record not found", 1);

	} catch(Exception $e) {
		echo $e->getMessage();
	}
}

public function cancelOrder_original(){
	try{

	$data=$this->_request->input();
	$canId=$data['can_id'];
	$confirmData=DB::table('po_confirm')->where('id',$canId)->get()->toArray();
	if(count($confirmData)>0){
		$confirmData=$confirmData[0];

		$params='';
		$method = 'orderReversal';
		$methodType='POST';
		$inputArray=[];
		$inputArray['order_number']=$confirmData->po_number;
		$inputArray['confirmation_number']=$confirmData->reference_value;
		$inputArray['confirmation_counter']=$confirmData->is_confirmed;
		$inputArray['reversal_reson']=$data['reson'];
		$body=array('order_reversal'=>$inputArray);
		$this->erp=new ConnectErp($this->mfg_id);
		$result=$this->erp->request($method,$params,$body,$methodType);
		$result=json_decode($result);
		if($result->status){
			DB::table('eseal_'.$this->mfg_id)->where('reference_value',$confirmData->reference_value)->where('is_confirmed',$confirmData->is_confirmed)->update(['eseal_confirmed'=>0,'is_confirmed'=>0]);
			DB::table('po_confirm_queue')->where('id',$confirmData->q_ref)->update(['status'=>5]);
			/*DB::table('production_orders')->where('erp_doc_no',$confirmData->po_number)->where('eseal_doc_no',$confirmData->po_number)->update(['is_confirm'=>1]);*/
			DB::table('production_orders')->where(function ($query) {
                $query->where('erp_doc_no',$confirmData->po_number)->orWhere('eseal_doc_no',$confirmData->po_number)->update(['is_confirm'=>1]);
            });
            echo ($result->message);
		} else {
			echo "Not Reversed, Please Check with team reason E-".$result->message;
		}
	} else 
	throw new Exception("po record not found", 1);

	} catch(Exception $e) {
		echo $e->getMessage();
	}
}

public function cancelOrder(){
	try{
		//DB::beginTransaction();
	$data=$this->_request->input();
	//print_r($data);
	$canId=$data['can_id'];
	//echo $canId;exit;
	$confirm=DB::table('po_confirm')->where('id',$canId)->get()->toArray();
	
	
	$po_num=$data['rpno'];
	/*-----track_update------*/
	/*$transitionId=DB::table('transaction_master')->where('name','=','Reverse PO')->value('id');
	$srcLocationId=DB::table('production_orders')->where(function($query)use($po_num) {
                $query->where('erp_doc_no','=',$po_num)->orWhere('eseal_doc_no','=',$po_num);
            })->value('location_id');
	$confirm_ids = DB::table('eseal_'.$this->mfg_id)->where(['po_number'=>$confirm->po_number,'level_id'=>0,'is_active'=>1])->where('is_confirmed','!=',0)->where('eseal_confirmed',$confirm->q_ref)->pluck('primary_id')->toArray();
	$access_token=DB::table('users_token')->where('user_id',session::get('userId'))->where('module_id',4002)->value('access_token');

	foreach($confirm_ids as $id){
		$th_data=[];
		$th_data['src_loc_id']=$srcLocationId;
		$th_data['dest_loc_id']=0;
		$th_data['transition_id']=$transitionId;
		$th_data['tp_id']=0;
		$th_data['pallate_id']=0;
		$lastInrtId=DB::table('track_history')->insertGetId($th_data);
		
		DB::table('eseal_'.$this->mfg_id)
					->where('primary_id', $id)
					->orWhere('parent_id', $id)
					->update(array('track_id'=>$lastInrtId));
		$sql = '
					INSERT INTO 
						track_details (code, track_id) 
					SELECT 
						'.$id.',' .$lastInrtId.' 
					FROM 
						eseal_'.$this->mfg_id.' 
					WHERE 
						track_id = '.$lastInrtId;
				DB::insert($sql);
					
	}*/
	/*-------------*/

	if(count($confirm)>0){
		$confirm=$confirm[0];
/*------------------checking if ids are available in source location before reverse*/		
				$po_number=$confirm->po_number;
				$confirm_ids = DB::table('eseal_'.$this->mfg_id)->where(['po_number'=>$confirm->po_number,'level_id'=>0,'is_active'=>1])->where('is_confirmed','!=',0)->where('eseal_confirmed',$confirm->q_ref)->pluck('primary_id')->toArray();
				//$explodeIds = explode(',', $confirm_ids); 
				/*$ids_loc=DB::table('track_history as th')->join('eseal_'.$this->mfg_id.' as e','e.track_id','=','th.track_id')->whereIn('e.primary_id',$confirm_ids)->value('th.src_loc_id');*/
				$po_location=DB::table('production_orders')->where(function($query)use($po_number) {
		                $query->where('erp_doc_no','=',$po_number)->orWhere('eseal_doc_no','=',$po_number);
		            })->value('location_id');
				$pass_loc=0;
				foreach($confirm_ids as $ids){
					$ids_loc=DB::table('track_history as th')->join('eseal_'.$this->mfg_id.' as e','e.track_id','=','th.track_id')->where('e.primary_id',$ids)->value('th.src_loc_id');
					if($po_location==$ids_loc){
						$pass_loc++;
					}

				}
				if(count($confirm_ids)!=$pass_loc){
					throw new Exception("Some iots are not available in this location,cannot reverse PO", 1);
				}
/*--------------checking ends ----------------------------------------------------------*/

		$params='';
		$method = 'orderReversal';
		$methodType='POST';
		$inputArray=[];
		$inputArray['order_number']=$confirm->po_number;
		$inputArray['confirmation_number']=$confirm->reference_value;
		$inputArray['confirmation_counter']=$confirm->is_confirmed;
		$inputArray['reversal_reson']=$data['reson'];
		$body=array('order_reversal'=>$inputArray);
		$this->erp=new ConnectErp($this->mfg_id);
		$result=$this->erp->request($method,$params,$body,$methodType);
		$result=json_decode($result);
		// print_r($confirm);
		
		if($result->status){
			$po_number=$confirm->po_number;
/*------------------------track updation---------------------------*/
			$transitionId=DB::table('transaction_master')->where('name','=','Reverse PO')->value('id');
			$srcLocationId=DB::table('production_orders')->where(function($query)use($po_number) {
                $query->where('erp_doc_no','=',$po_number)->orWhere('eseal_doc_no','=',$po_number);
            })->value('location_id');
			$confirm_ids = DB::table('eseal_'.$this->mfg_id)->where(['po_number'=>$confirm->po_number,'level_id'=>0,'is_active'=>1])->where('is_confirmed','!=',0)->where('eseal_confirmed',$confirm->q_ref)->pluck('primary_id')->toArray();
			$access_token=DB::table('users_token')->where('user_id',session::get('userId'))->where('module_id',4002)->value('access_token');

		foreach($confirm_ids as $id){
		$th_data=[];
		$th_data['src_loc_id']=$srcLocationId;
		$th_data['dest_loc_id']=0;
		$th_data['transition_id']=$transitionId;
		$th_data['tp_id']=0;
		$th_data['pallate_id']=0;
		$th_data['update_time']=date("Y-m-d h:i:s");
		$lastInrtId=DB::table('track_history')->insertGetId($th_data);
		
		DB::table('eseal_'.$this->mfg_id)
					->where('primary_id', $id)
					->orWhere('parent_id', $id)
					->update(array('track_id'=>$lastInrtId));
		$sql = '
					INSERT INTO 
						track_details (code, track_id) 
					SELECT 
						'.$id.',' .$lastInrtId.' 
					FROM 
						eseal_'.$this->mfg_id.' 
					WHERE 
						track_id = '.$lastInrtId;
				DB::insert($sql);
					
	}
/*-----------------------------------ends track update----------------------------*/

			DB::table('eseal_'.$this->mfg_id)->where('reference_value',$confirm->reference_value)->where('is_confirmed',$confirm->is_confirmed)->update(['eseal_confirmed'=>0,'is_confirmed'=>0]);
			DB::table('po_confirm_queue')->where('id',$confirm->q_ref)->update(['status'=>5]);			
			/*DB::table('production_orders')->where('erp_doc_no',$confirmData->po_number)->where('eseal_doc_no',$confirmData->po_number)->update(['is_confirm'=>1]);*/
			DB::table('production_orders')->where(function ($query) use($po_number){
                $query->where('erp_doc_no','=',$po_number)->orWhere('eseal_doc_no','=',$po_number);
            })->update(['is_confirm'=>0]);
            
           // echo ($result->message);
            DB::commit();
			echo "Reversed Successfully  ".$result->message;
			//print_r($confirm);
			//return 'Reversed Successfully!';
		} else {
			echo "Not Reversed,Please Check with team reason:E-".($result->message);
		}
	} else 
	throw new Exception("po record not found", 1);
	
	} catch(Exception $e) {
		DB::rollback();
		echo $e->getMessage();
	}
}




public function getPOconfirmdetails($erp_doc_no,$eseal_doc_no){
	// echo $erp_doc_no;exit;
	/*echo 'IF(q.status!=1,"confirmation error at ecc",CONCAT("<center>","<a 
         data-href=\"javascript:void(0)\" onclick = \"reversePO()\"><span class=\" btn\"><button\">reversePO</button></span></a><span style=\"padding-left:5px;\" ></span>","</center>")) as actions';
         exit;*/
        
        $sql="SELECT `con`.`batch_no`, `con`.`reference_value`, `con`.`is_confirmed`,`q`.`timestamp`,concat(`q`.`qty`,' (PAL)') as qty, 
IF(q.status=1,\"Confirmed\",if(q.status=5,\"Reversed\",\"in process\")) AS status,IF(po.erp_doc_no = 0, po.eseal_doc_no, po.erp_doc_no) as erp_doc_no,IF(q.status=1,CONCAT(\"<center><a data-href='javascript:void(0)' onclick = reversePO('\",con.id,\"','\",batch_no,\"','\",is_confirmed,\"','\",reference_value,\"','\",IF(po.erp_doc_no = 0, po.eseal_doc_no, po.erp_doc_no),\"')>ReversePo</a><span style='padding-left:5px;'></span></center>\"),IF(q.status=0,' will confirm shortly',IF(q.status=2,' processing',IF(q.status=3,'Error occurred from Ecc','Reversed')))) as actions         
FROM `production_orders` AS `po` 
Inner JOIN `po_confirm_queue` AS `q` ON (`q`.`po_number` = `po`.`erp_doc_no` or`q`.`po_number` = `po`.`eseal_doc_no` )
Left JOIN `po_confirm` AS `con` ON `con`.`q_ref` = `q`.`id`
where po.erp_doc_no=".$erp_doc_no." or po.eseal_doc_no=".$erp_doc_no;
/*$sql="SELECT `con`.`batch_no`, `con`.`reference_value`, `con`.`is_confirmed`,`q`.`timestamp`,concat(`q`.`qty`,' (PAL)') as qty, 
IF(q.status=1,\"Confirmed\",if(q.status=5,\"Reversed\",\"in process\")) AS status,IF(po.erp_doc_no = 0, po.eseal_doc_no, po.erp_doc_no) as erp_doc_no,IF(q.status=1,CONCAT(\"<center><a data-href='javascript:void(0)' onclick = reversePO('\",con.id,\"','\",batch_no,\"','\",is_confirmed,\"','\",reference_value,\"','\",IF(po.erp_doc_no = 0, po.eseal_doc_no, po.erp_doc_no),\"')>ReversePo</a><span style='padding-left:5px;'></span></center>\"),IF(q.status=0,' will confirm shortly',IF(q.status=2,' processing',IF(q.status=3,'Error occurred from Ecc','Reversed')))) as actions         
FROM `production_orders` AS `po` 
Inner JOIN `po_confirm_queue` AS `q` ON (`q`.`po_number` = `po`.`erp_doc_no` or`q`.`po_number` = `po`.`eseal_doc_no` )
LEFT JOIN `po_confirm` AS `con` ON `con`.`q_ref` = `q`.`po_id`
where po.erp_doc_no=".$erp_doc_no." or po.eseal_doc_no=".$erp_doc_no;*/
//echo $sql; exit;
	$podata=DB::select($sql);
	// echo "<pre>";
	// // echo $sql;
	// print_r($podata);
	// exit;

/*<i class="fas fa-window-close"></i>
<span class='btn'><button>reversePO</button></span></a><span style='padding-left:5px;'></span>*/
//print_r($erp_doc_no);exit;

/*	$po_confrim=DB::table('po_confirm as pc')
				->join('production_orders as  po','po.erp_doc_no','=','pc.po_number')
				->select(DB::raw("pc.batch_no,pc.reference_value,po.qty,CONCAT('<center>','<a 
         data-href=\"javascript:void(0)\" onclick = \"reversePO()\"><span class=\" btn\"><button\">reversePO</button></span></a><span style=\"padding-left:5px;\" ></span>','</center>') as actions" ))
				->where('po_number','=',$erp_doc_no)
				->orWhere('po_number','=',$eseal_doc_no)
				->where('pc.status','=',1)
				->get()->toArray();*/

	/*$po_confrim=DB::table('po_confirm_queue as pcq')
					->leftJoin('po_confirm as  pc','po.erp_doc_no','=','pcq.po_number')

				->join('production_orders as  po','po.erp_doc_no','=','pcq.po_number')
				->select(DB::raw("pc.batch_no,pc.reference_value,pcq.qty,CONCAT('<center>','<a 
         data-href=\"javascript:void(0)\" onclick = \"reversePO()\"><span class=\" btn\"><button\">reversePO</button></span></a><span style=\"padding-left:5px;\" ></span>','</center>') as actions" ))
				->where('po_number','=',$erp_doc_no)
				//->orWhere('po_number','=',$eseal_doc_no)
				->where('pc.status','=',1)
				->get()->toArray();			
*/
// echo "<pre/>";print_r($podata);exit;
				return json_encode($podata);
}
/*
desc:fucniton to know whether ECC is working or Down*/
public function getECCstatus(){
	$stat= DB::table('eseal_customer')->value('eseal_erp');
	return $stat;
}
public function getConversion($qty,$UOM,$p_id){
	$convet = new Conversions();
			$baseUom=$convet->getbaseUomName($UOM);
			// print_r($baseUom);exit;
			$t_qty=$convet->getUomAll($qty,$UOM,$p_id);
			$qty.=$baseUom."<span <i class='fa fa-arrows-h' style='size:3px;'></i></span> ".($t_qty).' ["PAL"]';
			return $qty;

}


}

