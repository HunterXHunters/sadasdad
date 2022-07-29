<?php
set_time_limit(0);
ini_set('memory_limit', '-1');

use Central\Repositories\RoleRepo;
use Central\Repositories\OrderRepo;
use Central\Repositories\CustomerRepo;
use Central\Repositories\SapApiRepo;
use Central\Repositories\ApiRepo;

class ToolsController extends ScoapiController  
{

	public function __construct(RoleRepo $roleAccess,CustomerRepo $custRepo,SapApiRepo $sapRepo, ApiRepo $apiRepo) 
	{
		$this->tools=new ToolsModel();
		$this->roleAccess = $roleAccess;
		$this->custRepo = $custRepo;
		$this->sapRepo = $sapRepo;
		$this->_apiRepo = $apiRepo;		
	}

	public function index(){
		$data = Input::get();
		$api_name=$data['type'];
		$this->$api_name();
	}

	public function UpdateProdOrder(){
		$data = Input::get();
		$hasError=0;
		$noError=0;
		$dateTime 		= 	parent::getDate();
		$location_id 	= 	$this->roleAccess->getLocIdByToken(Input::get('access_token'));
		$iot_id 		=	Input::get('iot_id');
		$newProductionNumber 		=	Input::get('input');
		$mfg_id 		= 	$this->roleAccess->getMfgIdByToken(Input::get('access_token'));
		if($newProductionNumber==''){
			echo  json_encode(array("Status"=>0,"Message"=>"Please provide new production number"));
			exit;
		}
		$iotData=$this->tools->validateIot($iot_id,$mfg_id,1);
		$postData=$data;
		$postData['type']='PORDER';
		$postData['object_id']=$newProductionNumber;
		$request = Request::create('scoapi/getErpObjectResponse', 'POST',$postData);
		$originalInput = Request::input();//backup original input
		Request::replace($request->input());
		$response = Route::dispatch($request)->getContent();
		$responce='{
    "Status": 1,
    "Message": "Successfully retrieved the response.",
    "Data": "<?xml version=\"1.0\"?>\n<RESPONSE><HEADER><Status>1</Status><Message> S Successfully Retrieved</Message></HEADER><DATA><PRODUCTION_ORDER_NO>1499355</PRODUCTION_ORDER_NO><PRODUCTION_ORDER_DATE>27/09/2018</PRODUCTION_ORDER_DATE><PLANT_CODE>1032</PLANT_CODE><PLANT_NAME>Kala Amb Electric Water Heater</PLANT_NAME><FINISHED_MATERIAL><ITEM><MATERIAL_CODE>1601832</MATERIAL_CODE><MAT_DESC>PEBBLE 25 L IVORY</MAT_DESC><BATCH_NO/><QUANTITY>150.000</QUANTITY><UOM>EA</UOM><EXP_DATE/><MFG_DATE/><STORAGE_LOC_CODE>PR01</STORAGE_LOC_CODE><STORAGE_LOC_NAME>Production Locat</STORAGE_LOC_NAME></ITEM></FINISHED_MATERIAL><BOM><ITEM><NO>0010</NO><MATERIAL_CODE>1205099</MATERIAL_CODE><MAT_DESC>WH ASSY PEBBLE 25 L</MAT_DESC><BACKFLUSH/><BATCH_NO/><QUANTITY>150.000</QUANTITY><BAL_QUANTITY>150.000</BAL_QUANTITY><UOM>EA</UOM><EXP_DATE/><MFG_DATE/><STORAGE_LOC_CODE>PR01</STORAGE_LOC_CODE><STORAGE_LOC_NAME>Production Locat</STORAGE_LOC_NAME><RESERVATION_NO>0001009416</RESERVATION_NO></ITEM><ITEM><NO>0020</NO><MATERIAL_CODE>1303750</MATERIAL_CODE><MAT_DESC>THERMCOL PEBBLE 25 L</MAT_DESC><BACKFLUSH>1</BACKFLUSH><BATCH_NO/><QUANTITY>150.000</QUANTITY><BAL_QUANTITY>150.000</BAL_QUANTITY><UOM>EA</UOM><EXP_DATE/><MFG_DATE/><STORAGE_LOC_CODE>PR01</STORAGE_LOC_CODE><STORAGE_LOC_NAME>Production Locat</STORAGE_LOC_NAME><RESERVATION_NO>0001009416</RESERVATION_NO></ITEM><ITEM><NO>0030</NO><MATERIAL_CODE>1102115</MATERIAL_CODE><MAT_DESC>ERECTION CLAMP PEBBLE</MAT_DESC><BACKFLUSH>1</BACKFLUSH><BATCH_NO/><QUANTITY>150.000</QUANTITY><BAL_QUANTITY>150.000</BAL_QUANTITY><UOM>EA</UOM><EXP_DATE/><MFG_DATE/><STORAGE_LOC_CODE>PR01</STORAGE_LOC_CODE><STORAGE_LOC_NAME>Production Locat</STORAGE_LOC_NAME><RESERVATION_NO>0001009416</RESERVATION_NO></ITEM><ITEM><NO>0040</NO><MATERIAL_CODE>1102129</MATERIAL_CODE><MAT_DESC>MFV 8 KG/CM&#xB2;</MAT_DESC><BACKFLUSH>1</BACKFLUSH><BATCH_NO/><QUANTITY>150.000</QUANTITY><BAL_QUANTITY>150.000</BAL_QUANTITY><UOM>EA</UOM><EXP_DATE/><MFG_DATE/><STORAGE_LOC_CODE>PR01</STORAGE_LOC_CODE><STORAGE_LOC_NAME>Production Locat</STORAGE_LOC_NAME><RESERVATION_NO>0001009416</RESERVATION_NO></ITEM><ITEM><NO>0050</NO><MATERIAL_CODE>1102198</MATERIAL_CODE><MAT_DESC>FLEXI CONNECTION PIPE</MAT_DESC><BACKFLUSH>1</BACKFLUSH><BATCH_NO/><QUANTITY>150.000</QUANTITY><BAL_QUANTITY>150.000</BAL_QUANTITY><UOM>EA</UOM><EXP_DATE/><MFG_DATE/><STORAGE_LOC_CODE>PR01</STORAGE_LOC_CODE><STORAGE_LOC_NAME>Production Locat</STORAGE_LOC_NAME><RESERVATION_NO>0001009416</RESERVATION_NO></ITEM><ITEM><NO>0060</NO><MATERIAL_CODE>1303733</MATERIAL_CODE><MAT_DESC>CARTON PEBBLE  25 L</MAT_DESC><BACKFLUSH>1</BACKFLUSH><BATCH_NO/><QUANTITY>150.000</QUANTITY><BAL_QUANTITY>150.000</BAL_QUANTITY><UOM>EA</UOM><EXP_DATE/><MFG_DATE/><STORAGE_LOC_CODE>PR01</STORAGE_LOC_CODE><STORAGE_LOC_NAME>Production Locat</STORAGE_LOC_NAME><RESERVATION_NO>0001009416</RESERVATION_NO></ITEM></BOM><GENRAL_DATA/><SYSTEM_STATUS/><SERIAL/><CHARACTERISTICS/><INSPECTION/></DATA></RESPONSE>\n",
    "Qty": 150,
    "Location_id": ""
}';		$data=[];
		$result=(array) json_decode($responce,true);
		if($result['Status']==0){
			echo  json_encode(array("Status"=>0,"Message"=>"Purchase Order No. Not valid"));
			exit;
		} else {
			$deXml = simplexml_load_string($result['Data']);
			$newMetrialCode=(array) $deXml->DATA->FINISHED_MATERIAL->ITEM->MATERIAL_CODE;
			$newMetrialCode=$newMetrialCode[0];
			$newGroupId=DB::table('products')
							->where('material_code',$newMetrialCode)
							->pluck('group_id');

			foreach ($iotData['avbl'] as $key => $value) {
				$current_pinfo=DB::table('eseal_'.$mfg_id.' as es')
             		        ->join('products as pr','pr.product_id','=','es.pid')
							->where('primary_id',$value)
							->select('es.pid','pr.material_code','pr.group_id')
							->get();
				$cgroupId=$current_pinfo[0]->group_id;
				if($newGroupId==$cgroupId){
					$update=DB::table('eseal_'.$mfg_id)->where('primary_id',$value)->update(array('po_number'=>$newProductionNumber));
					$data[][$value]='po_number Updated';
					$noError=1;
				} else {
					$data[][$value]='product Groups are not matching';
					$hasError=1;
				}
		}

		foreach ($iotData['notinEseal'] as $key => $value) {
			$hasError=1;
			$data[][$value]='IOT not available in Eseal';
		}
		foreach ($iotData['notExist'] as $key => $value) {
			$hasError=1;
			$data[][$value]='IOT not available in EsealBank';
		}
		
		$status =$noError?($hasError+$noError):0;
		if($status==0){
			$message = 'not updated Successfully';
		} else if($status==1){
			$message = 'updated Successfully';	
		} else if($status==2){
			$message = 'updated Partially';	
		}
		echo json_encode(array("Status"=>$status,"Message"=>"Server:".$message,"Data"=>$data));
		exit;
		}
	}

	public function UpdateStorageLoc(){
		$hasError=0;
		$noError=0;
		$dateTime 		= 	parent::getDate();
		$location_id 	= 	$this->roleAccess->getLocIdByToken(Input::get('access_token'));
		$iot_id 		=	Input::get('iot_id');
		$updatingStorageLoc 		=	Input::get('input');
		$mfg_id 		= 	$this->roleAccess->getMfgIdByToken(Input::get('access_token'));
		$iotData=$this->tools->validateIot($iot_id,$mfg_id);
		foreach ($iotData['avbl'] as $key => $value) {
			$update=DB::table('eseal_'.$mfg_id)->where('primary_id',$value)->update(array('storage_location'=>$updatingStorageLoc));
			$data[][$value]='Storage Location Updated';
			$noError=1;
		}

		foreach ($iotData['notinEseal'] as $key => $value) {
			$hasError=1;
			$data[][$value]='IOT not available in Eseal';
		}
		foreach ($iotData['notExist'] as $key => $value) {
			$hasError=1;
			$data[][$value]='IOT not available in EsealBank';
		}

		$status =$noError?($hasError+$noError):0;
		if($status==0){
			$message = 'not updated Successfully';
		} else if($status==1){
			$message = 'updated Successfully';	
		} else if($status==2){
			$message = 'updated Partially';	
		}
		echo  json_encode(array("Status"=>$status,"Message"=>"Server:".$message,"Data"=>$data));
		exit;
	}

	public function UpdateMaterial(){
		
		$hasError=0;
		$noError=0;
		$dateTime 			= 	parent::getDate();
		$location_id 		= 	$this->roleAccess->getLocIdByToken(Input::get('access_token'));
		$iot_id 			=	Input::get('iot_id');
		$newMaterial_code 	=	Input::get('input');
		$mfg_id 			= 	$this->roleAccess->getMfgIdByToken(Input::get('access_token'));
		$iotData=$this->tools->validateIot($iot_id,$mfg_id);
		
		foreach ($iotData['avbl'] as $key => $value) {
			$current_pinfo=DB::table('eseal_'.$mfg_id.' as es')
             		        ->join('products as pr','pr.product_id','=','es.pid')
							->where('primary_id',$value)
							->select('es.pid','pr.material_code','pr.group_id')
							->get();

			$new_pinfo=DB::table('products')
							->where('material_code',$newMaterial_code)
							->select('material_code','group_id','product_id')
							->get();

			if(count($new_pinfo) > 0){
				$cgroupId=$current_pinfo[0]->group_id;	
				$ngroupId=$new_pinfo[0]->group_id;	
				$nproductId=$new_pinfo[0]->product_id;	
			
				if($cgroupId==$ngroupId){			
					$update=DB::table('eseal_'.$mfg_id)->where('primary_id',$value)->update(array('pid'=>$nproductId));
					$data[][$value]='Product changed';
					$noError=1;
				} else {
					unset($iotData['avbl'][$key]);
					$data[][$value]='Product group not matching.';
					$hasError=1;
				}
			} else {
				unset($iotData['avbl'][$key]);
				$data[][$value]='Product Not Found.';
				$hasError=1;
			}
			
		}
		foreach ($iotData['notinEseal'] as $key => $value) {
			$hasError=1;
			$data[][$value]='IOT not available in Eseal';
		}
		foreach ($iotData['notExist'] as $key => $value) {
			$hasError=1;
			$data[][$value]='IOT not available in EsealBank';
		}
		$status =$noError?($hasError+$noError):0;
		if($status==0){
			$message = 'not updated Successfully';
		} else if($status==1){
			$message = 'updated Successfully';	
		} else if($status==2){
			$message = 'updated Partially';	
		}
		echo  json_encode(array("Status"=>$status,"Message"=>"Server:".$message,"Data"=>$data));		
		exit;
	}

	public function RemoveTrack(){
		
		$hasError=0;
		$noError=0;
		$status=0;
		$message='';
		$data=[];
		try{
		$dateTime 		= 	parent::getDate();
		$location_id 	= 	$this->roleAccess->getLocIdByToken(Input::get('access_token'));
		$iot_id 		=	Input::get('iot_id');
		$mfg_id 		= 	$this->roleAccess->getMfgIdByToken(Input::get('access_token'));
		$iotData=$this->tools->validateIot($iot_id,$mfg_id);
		$iotData['notrack']=array();
			/* removing last track for iots*/
			foreach ($iotData['avbl'] as $key => $value) {
				$sql='select td.track_id from `track_details` as `td` inner join `track_history` as `th` on `th`.`track_id` = `td`.`track_id` where `td`.`code` ='.$value.' order by track_id desc';
				$esealEsist = DB::select($sql);
				if(count($esealEsist)==0){
					unset($iotData['avbl'][$key]);
					$iotData['notrack'][]=$value;
					$hasError=1;
				} else {				
					if(count($esealEsist)>1){
						$latestTackId=$esealEsist[0]->track_id;
						$prvTackId=$esealEsist[1]->track_id;
						$delete=DB::table('track_details')->where('code',$value)->where('track_id',$latestTackId)->delete();
						$update=DB::table('eseal_'.$mfg_id)->where('primary_id',$value)->update(array('track_id'=>$prvTackId));
						$data[][$value]='Track Reversed';
						$noError=1;
					} else {
						$latestTackId=$esealEsist[0]->track_id;
						$delete=DB::table('track_details')->where('code',$value)->where('track_id',$latestTackId)->delete();
						$delete_eseal=DB::table('eseal_'.$mfg_id)->where('primary_id',$value)->delete();
						$update_esealBank=DB::table('eseal_bank_'.$mfg_id)->where('id',$value)->update(Array('used_status'=>0,'download_status'=>0));
						$data[][$value]='Track Reversed';
						$noError=1;
					}
				}		
			}
			foreach ($iotData['tpdata'] as $key => $value) {
				$sql='select td.track_id from `track_details` as `td` inner join `track_history` as `th` on `th`.`track_id` = `td`.`track_id` where `td`.`code` ='.$value.' order by track_id desc';
				$esealEsist = DB::select($sql);
				$latestTackId=$esealEsist[0]->track_id;
				$delete=DB::table('track_details')->where('code',$value)->where('track_id',$latestTackId)->delete();
				$noError=1;
			}
			
			foreach ($iotData['notrack'] as $key => $value) {
				$hasError=1;
				$data[][$value]='No Track available';
			}

			foreach ($iotData['notinEseal'] as $key => $value) {
				$hasError=1;
				$data[][$value]='IOT not available in Eseal';
			}
			foreach ($iotData['notExist'] as $key => $value) {
				$hasError=1;
				$data[][$value]='IOT not available in EsealBank';
			}
			
			$status =$noError?($hasError+$noError):0;
			if($status==0){
				$message = 'not updated Successfully';
			} else if($status==1){
				$message = 'updated Successfully';	
			} else if($status==2){
				$message = 'updated Partially';	
			}
			
		} catch(Exception $e){
			$status =0;
			DB::rollback();
			$message = $e->getMessage();
		}
		echo  json_encode(array("Status"=>$status,"Message"=>"Server:".$message,"Data"=>$data));
		exit;
	}

}        
