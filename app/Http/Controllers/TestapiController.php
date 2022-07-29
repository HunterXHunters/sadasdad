<?php
namespace App\Http\Controllers;
set_time_limit(0);
ini_set('memory_limit', '-1');

use App\Repositories\RoleRepo;
use App\Repositories\CustomerRepo;
use Illuminate\Http\Request;
use App\Repositories\SapApiRepo;
use App\Repositories\ConnectErp;
use App\Repositories\ApiRepo;
use App\Repositories\OrderRepo;
use App\Models\MasterLookup;
use App\Models\Location;
use App\Models\Token;
use App\Models\ApiLog;
use App\Models\User;
use App\Models\ErpObjects;
use App\Models\Conversions;
use App\Models\Trackhistory;
use App\Models\Transaction;
use App\Models\Track;
use App\Models\Products\Products;
//use App\Events\test;
use App\Events\scoapi_BindEseals;
use App\Events\scoapi_MapEseals;


use Session;
use DB;
use View;
use Input;
use Validator;
use Redirect;
use Log;
use Exception;


class TestapiController extends BaseController 
{
	protected $custRepo;
	protected $roleAccess;
	protected $attributeTable = 'attributes';
	protected $TPAttributeMappingTable = 'tp_attributes';
	protected $attributeMappingTable = 'attribute_mapping';    
	protected $trackHistoryTable = 'track_history';
	protected $trackDetailsTable = 'track_details';
	protected $tpDetailsTable = 'tp_details';    
	protected $tpDataTable = 'tp_data';        
	protected $tpPDFTable = 'tp_pdf';            
	protected $locationsTable = 'locations';            
	protected $prodSummaryTable = 'production_summary';            
	protected $transactionMasterTable = 'transaction_master';  
	protected $bindHistoryTable = 'bind_history';  
	protected $valuation ='valuation_type';          
	private $_childCodes = array();
	private $_apiRepo;
	public $erp;
	public $eSeal_erp;

	public function index()
	{

	}

	public function __construct(RoleRepo $roleAccess,CustomerRepo $custRepo,SapApiRepo $sapRepo,Request $request) 
	{
		if(isset($_SERVER['HTTP_X_FORWARDED_FOR'])){
			$_SERVER['REMOTE_ADDR']=$_SERVER['HTTP_X_FORWARDED_FOR'];
		}
		$this->roleAccess = $roleAccess;
		$this->custRepo = $custRepo;
		$this->sapRepo = $sapRepo;
	//	$this->_apiRepo = $apiRepo;	
		$this->roleAccess = $roleAccess;
        $this->_request = $request;       
        $this->mfg_id=0;
        $this->eSeal_erp=1;
	}

	public function createAttributes()
	{
            //return 'hi';
	        try{
	       	$data = Input::get();
	        
	        foreach($data as $d){
	        	if($d == ''){
	        		return json_encode(array('status'=> 0,'message' =>'One or more of the parameters is empty.'));
	        	}
	        }
	         //return $data;  
	         //return 'nikhil';
	        $query=DB::table('attribute_mapping')->select('attribute_mapping.*')->get();
	         // $query=Attributes::all();
	         //return var_dump($query);
	         //return $query; 
	         if(!empty($query)){
	            $id = DB::table('attribute_mapping')
		            	->orderBy('attr_map_id', 'DESC')
		            	->take(1)->get();
	            $map_id=$id[0]->attr_map_id+1;
	         }
	         else{
	         	$map_id=1;
	         }
	        foreach($data as $key=>$d){
	         
	        $att_id = Eav::where('attribute_code',$key)->lists('attribute_id');
	        if($key!='location_id'){
	        $attribute = new Attributes;
	        $attribute->attr_map_id= $map_id;
	        $attribute->attr_mapping_type= 'product';
	        $attribute->attr_id= $att_id[0];
	        //$attribute->location_id=2;
	        $attribute->value= $d;
	        $attribute->save();
	        } 
	     }
	       if($data['location_id']){
	       	$id = DB::table('attribute_mapping')
		            	->orderBy('attr_map_id', 'DESC')
		            	->take(1)->get();
	       	$map_id=$id[0]->attr_map_id;
	       	
	        DB::table('attribute_mapping')
	            ->where('attr_map_id', $map_id)
	            ->update(array('location_id' =>$data['location_id']));
	      
	       }
	      return json_encode(array('status'=> 1,'message' =>'Attributes Created Successfully.','attribute_map_id'=>$map_id));   
	   
	    }
	catch(exception $e){
		//return $e;
		return json_encode(array('status'=> 0,'message' =>'Parameters Missing.'));   
	}
    }
	public function allocateIds() {
        
 $data = Input::get();

 $results = DB::select(DB::raw('select count(e.id) as cnt from mage_helper.eseal_pregenerated_ids e LEFT JOIN  mage_helper.eseal_pregenerated_ids_bank e1 on e.id = e1.id where 
e1.id = e.id'));        

 if ($results[0]->cnt > 0) {

    $results = DB::statement('delete e from mage_helper.eseal_pregenerated_ids e LEFT JOIN mage_helper.eseal_pregenerated_ids_bank e1 on e.id = e1.id where 
e1.id = e.id');
}

    $results = DB::statement('update mage_helper.eseal_pregenerated_ids set used_for='.$data['id'].',used_status=1 where used_status=0 order by serial_id limit '.$data['qty']);
   
    $results = DB::statement('create table dev_live.eseal_'.$data['id'].' (manufacturer_id int(11),eseal_id bigint(64)) engine=INNODB as 
   (select '.$data['id'].' as manufacturer_id,id as eseal_id from mage_helper.eseal_pregenerated_ids where used_status = 1 and used_for = '.$data['id'].')');
            
    return $data['qty'].' Ids successfully allocated to manufacturer_id '.$data['id'];
   }

	
	public function databinding()
	{
		
		try
		{
       		$data = Input::get();
        	//return $data; 
        	foreach($data as $valid)
        	{
        		if($valid == '')
        		{
        			return json_encode(array('status'=> 0,'message' =>'One or more of the parameters is empty.'));
        		}
        	}
        	$attribute_map_id = $data['attribute_map_id'];
        	$product_id = $data['product_id'];
            $req = explode(",",$data['ids']);
            foreach($req as $val)
        	{   
        		//return $val;
        		DB::table('eseal_products')->insert([

				'attribute_map_id' => $attribute_map_id,
				'pid' => $product_id,
				'eseal_id' => $val,
				'level' => 0,
				'parent_eseal_id' => 0,
					]);
        	} 
      		return json_encode(array('status'=> 1,'message' =>'Data Binding Completed Successfully.'));   
   
    	}
		catch(exception $e)
		{
			//return $e;
			return json_encode(array('status'=> 0,'message' =>'Parameters Missing.'));   
		}
    }
    

    public function mapping()
	{
		try
		{
       		$data = Input::get();
       		//return $data;
        	foreach($data as $valid)
        	{
        		if($valid == '')
        		{
        			return json_encode(array('status'=> 0,'message' =>'One or more of the parameters is empty.'));
        		}
        	}
        	
        	$attribute_map_id = $data['attribute_map_id'];
        	$product_id = $data['product_id'];
            $secondary = $data['secondary'];
            $esealIds = explode(",",$data['ids']);
            //return $esealIds[0];
            $level = EsealProducts::where(array('eseal_id'=>$esealIds[0],'pid'=>$product_id))->select('level')->get('');
            //return $level[0]['level'];
            $secondary_level = $level[0]['level']+1;
            $esealProducts = new EsealProducts;
        	$esealProducts->attribute_map_id= $attribute_map_id;
        	$esealProducts->pid= $product_id;
            $esealProducts->eseal_id= $secondary;
        	$esealProducts->level= $secondary_level;
        	$esealProducts->parent_eseal_id= 0;
        	$esealProducts->save();

            foreach($esealIds as $val)
        	{   
        		// return $val;
        		 DB::table('eseal_products')
            ->where('eseal_id',$val)
            ->update(array('parent_eseal_id'=>$secondary));

            /*DB::table('attribute_mapping')
            ->where('attr_map_id', $map_id)
            ->update(array('location_id' =>$data['location_id']));*/

        	} 
      		return json_encode(array('status'=> 1,'message' =>'Data Mapping Completed Successfully.'));   
   
    	}
		catch(exception $e)
		{
			return $e;
			return json_encode(array('status'=> 0,'message' =>'Parameters Missing.'));   
		}
    } 
    public function trackupdate()
    {

	  $data = Input::all();
     // return  $data['tp_status'];
	  $secondary = $data['secondary'];
	  $ids[] = explode(',',$data['ids']);
	  
	try{
	  
	   DB::table('track_history')->insert([

				'tp_id' => Input::get('tp_id'),
				'from_location' => Input::get('from_loc'),
				'to_location' => Input::get('to_loc'),
				'transaction_name' => Input::get('transaction_name'),
				'tp_attribute_mapid' => Input::get('tp_mapid'),
				'tp_status' => $data['tp_status'],
				]);

	        $track_id = DB::getPdo()->lastInsertId();
	    foreach($ids[0] as $id){

	    DB::table('eseal_track_mapping')->insert([

				'eseal_id' => $id,
				'track_id' => $track_id,
				]);
	    }
	    DB::table('eseal_track_mapping')->insert([
	    	
				'eseal_id' => $secondary,
				'track_id' => $track_id,
				]);
	    return 'Track Update Completed Successfully.';

	}
	   catch(Exception $e){
	   return 'failure'.$e->getMessage();
	}

	}
	public function getCodes()
	{
		$results = DB::table('central_eseal_order_bank')->select('central_eseal_order_bank.id')->limit('100')->get();
		//return $results;
		$file = '/home/venkat/venkat.txt';
		$data='';
		$prim = 9;
		foreach($results as $key=>$val)
		{			
			$data.= $val->id.',';
			if($prim<=$key)
			{
				$prim = $prim+10;
				$data.='';
			}	
	   	}
	   	file_put_contents($file, $data, FILE_APPEND | LOCK_EX);
	   	//print_r($reqdata);
	}
	public function apiCall()
	{
		$file = '/home/venkat/venkat1.txt';
		$reqdata = fgets($file);
		return $reqdata;
	   	$curl = curl_init();
		// Set some options - we are passing in a useragent too here
		curl_setopt_array($curl, array(
    		CURLOPT_RETURNTRANSFER => 1,
    		CURLOPT_URL => 'venkat.reddy/wms/databinding',
    		//CURLOPT_USERAGENT => 'Codular Sample cURL Request',
    		CURLOPT_POST => 1,
    		CURLOPT_POSTFIELDS => array('attributemap_id' => 1,'product_id'=>323,'ids'=>$reqdata)
		));
		$resp = curl_exec($curl);
		curl_close($curl);
		print_r($resp);exit;
	}

	function grnCreation(){

		$response = array('Status' => 1, 'Message' => '', 'Data' => array());
		$ids = $this->_request->input('ids');
		$access_tokne = $this->_request->input('access_token');
		$mfgId = $this->roleAccess->getMfgIdByToken($access_tokne);
		$esealTable = 'eseal_'.$mfgId;
		$esealBankTable = 'eseal_bank_'.$mfgId;
		$locationId = $this->roleAccess->getLocIdByToken($this->_request->input('access_token'));

		$poNumber = $this->_request->input('po_number');
		$poItem = $this->_request->input('po_item');
		$ibdNumber = $this->_request->input('idb_number');
		$ibdItem = $this->_request->input('idb_item');
		$material = $this->_request->input('material');
		$poIbdFlag = $this->_request->input('po_idb_flag');
		$deliveryNote = $this->_request->input('delivery_note');
		$billOfLanding = $this->_request->input('bill_of_lading');

		if(empty($poNumber)){
			$response['Message'] = 'Server: mandatory field PO Number';
			return $response;
		}

		if(empty($poIbdFlag)){
			$response['Message'] = 'Server: mandatory field PO/IBD Flag';
			return $response;	
		}
		
		$importPOData = DB::table('ImportPO')
						->where('po_number', $poNumber)
						->get();
		if (count($importPOData) <= 0){
			$response['Message'] = 'Server: Details is not exist in eSeal';
			return $response;
		}
		
		/*-----------------------------------------------
		--------Convert IDS into string and array--------
		------------------------------------------------*/
		$explodeIds = explode(',', $ids);
		$explodeIds = array_unique($explodeIds);
		
		$idCnt = count($explodeIds);
		$strCodes = '\''.implode('\',\'', $explodeIds).'\'';
		/*-----------------------------------------------------
		--------Check if these ids have already some tp--------
		------------------------------------------------------*/
		$tpCount = DB::table($esealTable.' as eseal')->join($this->trackHistoryTable.' as th', 'eseal.track_id', '=', 'th.track_id')
		->whereIn('primary_id', $explodeIds)
		->where('tp_id','!=', 0)
		->where('dest_loc_id', '>', 0)
		->select('tp_id')
		->distinct()
		->get()->toArray();

		Log::info(count($tpCount));
		if(count($tpCount)){
			$response['Message'] = 'Server: Some of the codes are already assigned some TPs';
		}
		// Validate IOTs
		$esealBankData = DB::table($esealBankTable)
						->whereIn('id', $explodeIds)
						->where('issue_status', '0')
						->get();
		
		if (count($explodeIds) != count($esealBankData))
		{
			$response['Message'] = 'Server: Some of the codes are not available in eSeal';
			return $response;
		}

		$transactionId = DB::table('transaction_master')
							->where('manufacturer_id', $mfgId)
							->where('action_code', 'RVG')
							->value('id');
		$date = date('d.m.Y');
		return $postData = "{\r\n\t\"headerData\": {\r\n\r\n\t\t\"po_ibd_indicator_flag\": \"$poIbdFlag\",\r\n\t\t\"doc_date\": \"$date\",\r\n\t\t\"posting_date\": \"$date\",\r\n\t\t\"delivery_note\": \"$deliveryNote\",\r\n\t\t\"bill_of_lading\": \"$billOfLanding\"\r\n\r\n\t},\r\n\t\"itemData\": [{\r\n\t\t\t\"po_number\": \"$poNumber\",\r\n\t\t\t\"po_item\": \"$poItem\",\r\n\t\t\t\"idb_number\": \"$ibdNumber\",\r\n\t\t\t\"idb_item\": \"$ibdItem\",\r\n\t\t\t\"material\": \"$material\",\r\n\t\t\t\"storage_location\": \"KEL1\",\r\n\t\t\t\"grn_quantity\": 50.000,\r\n\t\t\t\"stock_type\": \"\",\r\n\t\t\t\"uom\": \"EA\",\r\n\t\t\t\"sku_code\": \"94028\",\r\n\t\t\t\"case_config\": \"00024\",\r\n\t\t\t\"price_lot\": \"01\",\r\n\t\t\t\"date_of_mfg\": \"16.10.2016\"\r\n\r\n\t\t}\r\n\t]\r\n}";

		$requestData = array(
			'headerData'	=> array(
									'po_ibd_indicator_flag'	=> '',
									'doc_date'	=> date('d.m.Y'),
									'posting_date'	=> date('d.m.Y'),
									'delivery_note'	=> '',
									'bill_of_lading'	=> ''
									),
			'itemData'		=> array(
									'po_number'	=>	'',
									'po_item'	=>	'',
									'idb_number'=>	'',
									'idb_item'	=>	'',
									'material'	=> 	'',
									'storage_location'	=> '',
									'grn_quantity'		=> '',
									'stock_type'		=> '',
									'uom'				=> '',
									'sku_code'			=> '',
									'case_config'		=> '',
									'price_lot'			=> '',
									'date_of_mfg'		=> date('d.m.Y')
									)
		);
		$reqData = json_encode($requestData);
		$this->erp=new ConnectErp($mfgId);
		$result=$this->erp->request('grnCreation',0,json_encode($reqData),'POST');
		return $result;
		return $message = 'grnCreation working';
		return $response;
		return json_encode(['Status'=>0,'Message'=>'Server: '.$message,'Data'=>$data]);
	}

	private function curlRequest($url, $requestData){
		$curl = curl_init();
		curl_setopt_array($curl, array(
		  CURLOPT_URL => "https://apidev.unileverservices.com/esealintegrationapi-v1/api/grnCreation",
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_ENCODING => "",
		  CURLOPT_MAXREDIRS => 10,
		  CURLOPT_TIMEOUT => 30,
		  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		  CURLOPT_CUSTOMREQUEST => "POST",
		  CURLOPT_POSTFIELDS => "{\r\n\t\"headerData\": {\r\n\r\n\t\t\"po_ibd_indicator_flag\": \"I\",\r\n\t\t\"doc_date\": \"10.16.2016\",\r\n\t\t\"posting_date\": \"10.16.2016\",\r\n\t\t\"delivery_note\": \"eseal234\",\r\n\t\t\"bill_of_lading\": \"eseal234\"\r\n\r\n\t},\r\n\t\"itemData\": [{\r\n\t\t\t\"po_number\": \"\",\r\n\t\t\t\"po_item\": \"\",\r\n\t\t\t\"idb_number\": \"100161003\",\r\n\t\t\t\"idb_item\": \"1\",\r\n\t\t\t\"material\": \"94000028\",\r\n\t\t\t\"storage_location\": \"KEL1\",\r\n\t\t\t\"grn_quantity\": 50.000,\r\n\t\t\t\"stock_type\": \"\",\r\n\t\t\t\"uom\": \"EA\",\r\n\t\t\t\"sku_code\": \"94028\",\r\n\t\t\t\"case_config\": \"00024\",\r\n\t\t\t\"price_lot\": \"01\",\r\n\t\t\t\"date_of_mfg\": \"16.10.2016\"\r\n\r\n\t\t}\r\n\t]\r\n}",
		  CURLOPT_HTTPHEADER => array(
		    "Accept: */*",
		    "Accept-Encoding: gzip, deflate",
		    "Cache-Control: no-cache",
		    "Connection: keep-alive",
		    "Content-Length: 545",
		    "Content-Type: application/json",
		    "Host: apidev.unileverservices.com",
		    "Postman-Token: 72b3362d-8620-46fe-9138-63e86f7033d9,e2141d00-e489-483d-b1e4-1b83472163ce",
		    "User-Agent: PostmanRuntime/7.18.0",
		    "cache-control: no-cache",
		    "client_id: 435710d16331408d9205a37062672fc4",
		    "client_secret: f4d33034BC554bA99b824381486d7316"
		  ),
		));

		$response = curl_exec($curl);
		$err = curl_error($curl);

		curl_close($curl);

		if ($err) {
		  echo "cURL Error #:" . $err;
		} else {
		  echo $response;
		}
	}
   
}