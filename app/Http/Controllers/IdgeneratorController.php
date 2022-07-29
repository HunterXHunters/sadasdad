<?php
namespace App\Http\Controllers;
set_time_limit(1200);
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use DB;
use View;
use Input;

class IdgeneratorController extends BaseController {

	private $_allowedLengthOfIds = Array(8,10,12,14,16);
	private $_randLimitArray = Array(
			'8' => 
				Array(
					'min'=>'00000001', 
					'max'=>'99999999'
					),
			'9' =>
				Array(
					'min'=>'000000001',
					'max'=>'999999999'
					), 
			'10' => 
				Array(
					'min'=>'0000000001',
					'max'=>'9999999999'
					),
			'11' =>
				Array(
					'min'=>'00000000001',
					'max'=>'99999999999'
					),
			'12' =>
				Array(
					'min'=>'000000000001',
					'max'=>'999999999999'
					),
			'13' => 
				Array(
					'min'=>'0000000000001',
					'max'=>'9999999999999'
					),
			'14' =>
				Array(
					'min'=>'00000000000001',
					'max'=>'99999999999999'
					),
			'15' => 
				Array(
					'min'=>'000000000000001',
					'max'=>'999999999999999'
					),
			'16' => 
				Array(
					'min'=>'0000000000000001',
					'max'=>'9999999999999999'
					)
				);
	private $_MULTIPLE_INSERT_CHUNK_SIZE = 100;
	private $_noOfIds = '';
	private $_idLength = '';
	private $_loopCount = 0;
		public function __construct(Request $request) 
	{
		if(isset($_SERVER['HTTP_X_FORWARDED_FOR'])){
			$_SERVER['REMOTE_ADDR']=$_SERVER['HTTP_X_FORWARDED_FOR'];
		}
        $this->_request = $request;       
    }



	public function generate(){
		try{
			$status = 0;
			$message = '';
			$this->_noOfIds = $this->_request->input('id_qty');
			$this->_idLength = $this->_request->input('id_length');		

			if(!is_numeric($this->_noOfIds) || !is_numeric($this->_idLength)){
				throw new Exception('All input should be numeric');
			}
			$isValidIdLength = $this->checkForValidLength();
			if(!$isValidIdLength){
				throw new Exception('Invalid ID Length Passed');
			}

			$this->setLoopCount();

			$res = $this->generateIds();

			$status = 1;
			$message = 'Ids Generated Successfully';
			$message .= $res;
		}catch(Exception $e){
			$message = $e->getMessage();
		}
		return response()->json(Array('Status'=>$status, 'Message'=> $message));
	}

	private function checkForValidLength(){
		if(!in_array($this->_idLength, $this->_allowedLengthOfIds)){
			return FALSE;
		}else{
			return TRUE;
		}
	}

	private function setLoopCount(){
		if($this->_noOfIds > $this->_MULTIPLE_INSERT_CHUNK_SIZE){
			$this->_loopCount = ($this->_noOfIds / $this->_MULTIPLE_INSERT_CHUNK_SIZE);
		}else{
			$this->_loopCount = 1;
			$this->_MULTIPLE_INSERT_CHUNK_SIZE = $this->_noOfIds;
		}
	}

	private function generateIds(){
		$beforeCnt = DB::table('eseal_pregenerated_ids_bank')->where('id_length', $this->_idLength)->count();
		//DB::statement('LOCK TABLES `eseal_pregenerated_ids_bank` WRITE');
		DB::statement('ALTER TABLE `eseal_pregenerated_ids_bank` DISABLE KEYS');
		for($i=1; $i<=$this->_loopCount; $i++){
			$qry = 'INSERT IGNORE INTO eseal_pregenerated_ids_bank (id, id_length) VALUES ';
			$str = '';
			for($j=1; $j<=$this->_MULTIPLE_INSERT_CHUNK_SIZE; $j++){

				$var = 'random'.$j;
				$$var = '';
				$cntOfRandomArray = mt_rand(1,10);
				/*for($k=1; $k<=$cntOfRandomArray; $k++){
					$$var .= str_pad(mt_rand(0, $this->_randLimitArray[$this->_idLength]['max']), $this->_idLength, mt_rand(1,9),STR_PAD_LEFT).str_pad(mt_rand(0, $this->_randLimitArray[$this->_idLength]['max']), $this->_idLength, mt_rand(1,9),STR_PAD_RIGHT);
					Log::info($$var);
				}*/
				$id = str_pad(mt_rand($this->_randLimitArray[$this->_idLength]['min'], $this->_randLimitArray[$this->_idLength]['max']), $this->_idLength, '0', STR_PAD_RIGHT);
				$str .= '(\''.$id.'\', '.$this->_idLength.'),';
				$$var = '';
			}
			$str = RTRIM($str,',');
			Log::info($qry.$str);
			DB::insert($qry.$str);
		}
		DB::statement('ALTER TABLE `eseal_pregenerated_ids_bank` ENABLE KEYS');
		//DB::statement('UNLOCK TABLES');
		$afterCnt = DB::table('eseal_pregenerated_ids_bank')->where('id_length', $this->_idLength)->count();
		return  'Count Before ID Geenration is '.$beforeCnt.'. Count after id geberation is '.$afterCnt.'. Total Ids generated are '.$afterCnt-$beforeCnt.'.';
	}


	
	public function generateIot(){
		try{
			set_time_limit(1200);
			ini_set('max_execution_time', 1200);
			ini_set('memory_limit', '-1');
			$status = 0;
			$message = '';	
			$inputs=$this->_request->input();
			$this->_noOfIds = $inputs['id_qty'];
			$this->_idLength = $inputs['id_length'];		
			$this->_customer_id = $inputs['customer_id'];		
			$this->_issue_status = isset($inputs['print_status'])?$inputs['print_status']:1;
			$this->download_token = isset($inputs['download_token'])?$inputs['download_token']:1;
			$this->order_no = $inputs['order_no'];		
			$this->order_no = $inputs['order_no'];		

			$customerName=DB::table('eseal_customer')->where('customer_id',$this->_customer_id)->pluck('brand_name');
			
			if($this->order_no!='')
			$usedFor=$this->order_no;
			else
			$usedFor=$customerName.'_'.$this->_noOfIds.'_'.$this->order_no.'_'.date('dMY');

			if(!is_numeric($this->_noOfIds) || !is_numeric($this->_idLength)){
				throw new Exception('All input should be numeric');
			}
			$isValidIdLength = $this->checkForValidLength();
			if(!$isValidIdLength){
				throw new Exception('Invalid ID Length Passed');
			}
			DB::statement('TRUNCATE TABLE `eseal_pregenerated_ids_bank`');
			DB::statement('ALTER TABLE `eseal_pregenerated_ids_bank` DISABLE KEYS');
			$i=0;
			$temp=array();
			$time_start = microtime(true); 
			$chunkQty=25000;

			while($i<$this->_noOfIds){
				
				$ids=array_unique($this->iotChunkArray($chunkQty));

				$checkPreGen=DB::table('eseal_pregenerated_ids_bank')->whereIn('id',$ids)->pluck('id')->toArray();
				/*echo "<pre>";
				print_r($checkPreGen);
				exit;
*/				if(count($checkPreGen)>0){
					$ids=array_diff($ids,$checkPreGen);
				}

				$checkBnkGen=DB::table('eseal_bank_'.$this->_customer_id)->whereIn('id',$ids)->pluck('id');
				if(count($checkBnkGen)>0){
					$ids=array_diff($ids,$checkBnkGen);
				}

				if(count($ids)>0){ 

					if(($i+$chunkQty)>$this->_noOfIds){
						$ids=array_slice($ids,0,($this->_noOfIds-$i));						
					}
					

					$sql=" INSERT  INTO eseal_pregenerated_ids_bank (id, id_length) VALUES ('".implode("','".$this->_idLength."'),('",$ids)."','".$this->_idLength."')";
					DB::insert($sql);
					echo "<br><br> ittaration ::".$i;
					$i=$i+count($ids);					
				}
			}

			
			//DB::table('eseal_pregenerated_ids_bank')->insert($temp);					
			
			if($customerName=='V Guard' ||$customerName=='V-Guard Industries Limited'){
				$dtoken=DB::table('download_flag')->insertGetId(['user_id'=>0]);
				$dtoken=0;
				$insert='INSERT INTO eseal_bank_'.$this->_customer_id.' (id,order_no,issue_status,download_token) SELECT id,"'.$usedFor.'",'.($this->_issue_status).','.$dtoken.' FROM eseal_pregenerated_ids_bank';
			} else {
				$insert='INSERT INTO eseal_bank_'.$this->_customer_id.' (id,order_no,issue_status) SELECT id,"'.$usedFor.'",'.($this->_issue_status).' FROM eseal_pregenerated_ids_bank';
			}
			DB::statement('ALTER TABLE `eseal_pregenerated_ids_bank` ENABLE KEYS');
			$status = 0;
			$iots=[];
			if(DB::statement($insert)){
				$iots=DB::table('eseal_pregenerated_ids_bank')->pluck('id')->toArray();
			$status = 1;
			}
			//echo $insert; exit;
		//	echo "<br>final:.'".$id.':'.((microtime(true)- $time_start)/60)."'";	
		//	exit;
			$message = $i.' Ids Generated Successfully';

		}catch(Exception $e){
			$message = $e->getMessage();
		}
			if($status==1){
		return response()->json(Array('Status'=>$status, 'Message'=> $message,'iots'=>$iots,'orderno'=>$usedFor));
			} else {
		return response()->json(Array('Status'=>$status, 'Message'=> $message));				
			}
	}

	public function iotChunkArray($qty){
		$return=array();
		for($i=0;$i<$qty;$i++)
		{
			$return[] = str_pad(mt_rand($this->_randLimitArray[$this->_idLength]['min'], $this->_randLimitArray[$this->_idLength]['max']), $this->_idLength, '0', STR_PAD_RIGHT);	
		}	
		return $return;	
	}

	public function assign($customer_id)
	{
		$duplicate_pregenerated_ids = DB::table('eseal_pregenerated_ids_bank')->groupBy('id')->having('qty', '>', 1)->select(DB::raw('count(*) as qty'))->get()->toArray();

		if(count($duplicate_pregenerated_ids)>0)
		{
		foreach($duplicate_pregenerated_ids as $duplicate_pregenerated_id)
		{
		$duplicate_pregenerated_ids_arr[]= $duplicate_pregenerated_id; 
		}

		DB::table('eseal_pregenerated_ids_bank')->whereIn('id',$duplicate_pregenerated_ids_arr)->delete(); 
		}

		$duplicate_ids = DB::table('eseal_pregenerated_ids_bank as e')->join('eseal_bank_'.$customer_id.' as e1','e1.id','=','e.id')->whereNotNull('e.id')->select('e.id')->get()->toArray();

		$deleted_ids_arr = array();
		if(count($duplicate_ids)>0)
		{
		foreach($duplicate_ids as $duplicate_id)
		{
		$deleted_ids_arr[]= $duplicate_id->id; 
		}


		DB::table('eseal_pregenerated_ids_bank')->whereIn('id',$deleted_ids_arr)->delete();
		}

		$customer = DB::table('eseal_customer')->where('customer_id',$customer_id)->select('brand_name')->first();
		$brand_name = str_replace(" ","_",strtolower($customer->brand_name));
		DB::table('eseal_pregenerated_ids_bank')->where('used_for','unknown')->update(array('used_for' => $brand_name."_".date("Y-m-d")."_".$customer_id))->toArray();

		$pregenerated_bank_datas = DB::table('eseal_pregenerated_ids_bank')->where('used_for',$brand_name."_".date("Y-m-d")."_".$customer_id)->select('id','used_status')->get()->toArray();

		if(count($pregenerated_bank_datas)>0)
		{
		foreach($pregenerated_bank_datas as $pregenerated_bank_data)
		{
		DB::table('eseal_bank_'.$customer_id)->insert(array('id' => $pregenerated_bank_data->id,'used_status' => $pregenerated_bank_data->used_status));
		}
		}
		echo "IDs Succesfully Assigned.";
	}

}