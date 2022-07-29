<?php
set_time_limit(0);
ini_set('memory_limit', '-1');
ini_set('display_errors','On');
use Central\Repositories\RoleRepo;

class ReportController extends BaseController{

	public function __construct(RoleRepo $roleAccess) {
        $this->roleAccess = $roleAccess;
        
    	$this->customerId = (!empty(session::get('customerId'))) ? session::get('customerId') : 3;
    }

    private function getTime(){
		$time = microtime();
		$time = explode(' ', $time);
		$time = ($time[1] + $time[0]);
		return $time;
	}

	public function index()
	{
		$start_time = $this->getTime();
		$userTrackId = DB::table('user_tracks')->insertGetId(array('user_id'=>session::get('userId'),'service_name'=>'Dashboard','service_type'=>'Web' ,'status'=>1,'manufacturer_id'=>$this->customerId,'message'=>'Dasboard access by user'));
		//echo "<pre>";
		$groupResult = DB::table('product_groups')->select('group_id','name')->where('manufacture_id',$this->customerId)->get();
		//print_r($groupResult); die;
		$groups = array_column(json_decode(json_encode($groupResult),true), 'name');

		$shiftResults = DB::table('rpt_production')->select('shift')->groupBy('shift')->whereraw('manufacturer_id ='.$this->customerId." and shift!=''" )->get();
		/*$queries = DB::getQueryLog();
       $last_query = end($queries);
                return $last_query;
		print_r($shiftResults);*/
		$catResults = DB::table('rpt_production')->select('category_id','category_name')->groupBy('category_id')->where('manufacturer_id',$this->customerId)->get();
		//print_r($catResults);
		$locationResults = DB::table('rpt_production')->select('location_id','location_name')->groupBy('location_id')->where(array('manufacturer_id'=>$this->customerId,'location_type_name'=>'Plant'))->orderBy('location_name')->get();

		$supplierLocationResults = DB::table('rpt_production')->select('location_id','location_name')->groupBy('location_id')->where(array('manufacturer_id'=>$this->customerId,'location_type_name'=>'Supplier'))->orderBy('location_name')->get();
		//print_r($locationResults);		
		$dispatchLocationResults = DB::table('rpt_production')->select('location_id','location_name')->groupBy('location_id')->where(array('manufacturer_id'=>$this->customerId))->orderBy('location_name')->get();

		$locationTypeResults = DB::table('location_types')->select('location_type_id','location_type_name')->where('manufacturer_id',$this->customerId)->whereIn('location_type_name',array('Plant','Warehouse'))->get();

		//print_r($locationTypeResults); die;
		
		$productsResult = DB::table('rpt_production')->select('product_id','product_name')->groupby('product_id')->where('manufacturer_id',$this->customerId)->orderBy('product_name','asc')->get();
	
		$end_time = $this->getTime();
		DB::table('user_tracks')->where('user_track_id',$userTrackId)->update(array('response_duration'=>($end_time-$start_time)));	

		return view::make('reports.dashboard')->with(array('groupResult'=>json_encode($groupResult),'groups'=>json_encode($groups),'shiftResults'=>json_encode($shiftResults),'catResults'=>json_encode($catResults),'locationResults'=>json_encode($locationResults),'locationTypeResults'=>json_encode($locationTypeResults),'productsResult'=>json_encode($productsResult),'supplierLocationResults'=>json_encode($supplierLocationResults),'dispatchLocationResults'=>json_encode($dispatchLocationResults),'startDate'=>date('Y-m-d',strtotime("-7 days")),'endDate'=>date('Y-m-d')));
	}

	public function getLocation()
	{
		$data = Input::get();
		$locations = DB::table('locations')->select('location_id','location_name')->where('location_type_id',$data['data']['locTypeId'])->get();
		return json_encode(json_decode(json_encode($locations),true));
	}

	public function getProductionDashbaord()
	{
		$data = Input::get();
		if(empty($data['data'])){
			$response = array('Status'=>'400','Message'=>'Bad Request','ResponseBody'=>'Invalid JSON Format');
		}else{
			$data = $data['data'];
			//print_r($data); die;
			if(is_null($data['location_name']))
				$data['location_name'] = 'NULL';
			if(is_null($data['product_id']))
				$data['product_id'] = 'NULL';
			/*if(is_null($data['product_group']))
				$data['product_group'] = 'NULL';*/
			if(is_null($data['location_type']))
				$data['location_type'] = 'NULL';
			else
				$data['location_type'] = '"'.$data['location_type'].'"';
			if(is_null($data['category']))
				$data['category'] = 'NULL';
			
			if(is_null($data['shift_type']))
				$data['shift_type'] = 'NULL';
			else
				$data['shift_type'] = '"'.$data['shift_type'].'"';
			if(is_null($data['batch_no']) || $data['batch_no']=='undefined' || empty($data['batch_no']))
				$data['batch_no'] = 'NULL';
			else
				$data['batch_no'] = '"'.$data['batch_no'].'"';

			if(is_null($data['from_date']) && is_null($data['to_date'])){
				$maxDate = DB::table('rpt_production')->pluck(DB::raw('MAX(DATE(manufacturing_date)) as date'));
				$data['from_date'] = '"'.$maxDate.'"';
				$data['to_date'] = '"'.$maxDate.'"';
			}elseif(is_null($data['from_date']) && !is_null($data['to_date'])){
				$data['from_date'] = '"'.$data['from_date'].'"';
				$data['to_date'] = '"'.$data['to_date'].'"';
			}elseif(!is_null($data['from_date']) && is_null($data['to_date'])){
				$data['from_date'] = '"'.$data['from_date'].'"';
				$data['to_date'] = '"'.$data['to_date'].'"';
			}else{ 				
				$rowCount = DB::table('rpt_production')->whereRaw('manufacturing_date between "'.$data['from_date'].' 00:00:00" and "'.$data['to_date'].' 23:59:59"')->count('id');	
				if($rowCount==0)
				{
					$maxDate = DB::table('rpt_production')->pluck(DB::raw('MAX(DATE(manufacturing_date)) as date'));
					$data['from_date'] = '"'.$maxDate.'"';
					$data['to_date'] = '"'.$maxDate.'"';
				}else{
					$data['from_date'] = '"'.$data['from_date'].'"';
					$data['to_date'] = '"'.$data['to_date'].'"';
				}
			}

				
			$results = DB::select('CALL ProductionDashboard('.$data['from_date'].','.$data['to_date'].','.$data['shift_type'].','.$data['product_id'].','.$data['location_name'].','.$data['location_type'].','.$data['category'].','.$data['batch_no'].','.$data['period_type'].');');
			
			$results = json_decode(json_encode($results),true);

			
			$finalResults = array();
			foreach ($results as $key => $result) {
				$finalResults[$result['m_date']][$result['group_name']] = array($result)	;
			}

			if(empty($finalResults))
			{
				$response = array('Status'=>'200','Message'=>'success','ResponseBody'=>'No data found!');
			}else{
				$response =  array('Status'=>'200','Message'=>'success','ResponseBody'=>$finalResults);
			}
			
		}

		return json_encode($response);
	}


	public function getInventoryDashbaord()
	{
		$data = Input::get();
		if(empty($data['data'])){
			$response = array('Status'=>'400','Message'=>'Bad Request','ResponseBody'=>'Invalid JSON Format');
		}else{
			$data = $data['data'];

			if(is_null($data['location']))
				$data['location'] = 'NULL';
			if(is_null($data['product_id']))
				$data['product_id'] = 'NULL';
			if(is_null($data['product_group']))
				$data['product_group'] = 'NULL';
			if(is_null($data['location_type']))
				$data['location_type'] = 'NULL';
			if(is_null($data['category']))
				$data['category'] = 'NULL';
			$result = DB::select('CALL inventoryReport('.$data['location'].','.$data['product_id'].','.$data['product_group'].','.$data['location_type'].','.$data['category'].');');
			
			if(empty($result))
			{
				$response = array('Status'=>'200','Message'=>'success','ResponseBody'=>'No data found!');
			}else{
				$response =  array('Status'=>'200','Message'=>'success','ResponseBody'=>$result);
			}

		}

		return json_encode($response);
	}

	public function getDispatchDashbaord()
	{
		$data = Input::get();
		if(empty($data['data'])){
			$response = array('Status'=>'400','Message'=>'Bad Request','ResponseBody'=>'Invalid JSON Format');
		}else{
			$data = $data['data'];

			if(is_null($data['src_location']))
				$data['src_location'] = 'NULL';
			if(is_null($data['product_name']))
				$data['product_name'] = 'NULL';
			if(is_null($data['batch_no']) || $data['batch_no']=='undefined')
				$data['batch_no'] = 'NULL';
			if(is_null($data['from_date']) && is_null($data['to_date'])){
				$maxDate = DB::table('rpt_dispatch')->pluck(DB::raw('MAX(DATE(dispatch_date)) as date'));
				$data['from_date'] = '"'.$maxDate.'"';
				$data['to_date'] = '"'.$maxDate.'"';
			}elseif(is_null($data['from_date']) && !is_null($data['to_date'])){
				$data['from_date'] = '"'.$data['from_date'].'"';
				$data['to_date'] = '"'.$data['to_date'].'"';
			}elseif(!is_null($data['from_date']) && is_null($data['to_date'])){
				$data['from_date'] = '"'.$data['from_date'].'"';
				$data['to_date'] = '"'.$data['to_date'].'"';
			}else{ 				
				$rowCount = DB::table('rpt_dispatch')->whereRaw('dispatch_date between "'.$data['from_date'].' 00:00:00" and "'.$data['to_date'].' 23:59:59"')->whereIn('transition_id', array(703,713))->count('dispatch_id');	
				if($rowCount==0)
				{
					$maxDate = DB::table('rpt_dispatch')->whereIn('transition_id', array(703,713))->pluck(DB::raw('MAX(DATE(dispatch_date)) as date'));
					$data['from_date'] = '"'.$maxDate.'"';
					$data['to_date'] = '"'.$maxDate.'"';
				}else{
					$data['from_date'] = '"'.$data['from_date'].'"';
					$data['to_date'] = '"'.$data['to_date'].'"';
				}
			}

			$result = DB::select('CALL supplierDispatch('.$data['from_date'].','.$data['to_date'].','.$data['product_name'].','.$data['src_location'].','.$data['batch_no'].');');
		
			if(empty($result))
			{
				$response = array('Status'=>'200','Message'=>'success','ResponseBody'=>'No data found!');
			}else{
				$response =  array('Status'=>'200','Message'=>'success','ResponseBody'=>$result);
			}
			
			
		}

		return json_encode($response);
	}	

   	public function production(){

		parent::Breadcrumbs(array('Home'=>'/')); 
		$start_time = $this->getTime();
		$customerId = (!empty(session::get('customerId'))) ? session::get('customerId') : 0;

		$userTrackId = DB::table('user_tracks')->insertGetId(array('user_id'=>session::get('userId'),'service_name'=>'Live Production Packing','service_type'=>'Web' ,'status'=>1,'manufacturer_id'=>$this->customerId,'message'=>'Report access by user'));

		$locationTypes = DB::table('location_types')->where('manufacturer_id',$customerId)->whereIn('location_type_name',["Plant","Supplier","Vendor"])->lists('location_type_id');

		$locations = DB::table('locations')->whereIn('location_type_id',$locationTypes)->get(['location_id','location_name']);

		$materialCode = array_column(json_decode(json_encode(DB::table('products')->where(array('product_type_id'=>8003,'is_active'=>1))->groupBy('material_code')->get(['material_code'])),true),'material_code');

		$batchNo = array_column(json_decode(json_encode(DB::table('rpt_production')->groupBy('batch_no')->get(['batch_no'])),true),'batch_no');

		$inventories = DB::table('rpt_production')->select(DB::raw('DATE(manufacturing_date) as date,product_name,category_name,material_code,qty,location_name,batch_no,po_number,shift as shiftNo'))->orderBy('manufacturing_date','desc')->whereRaw('manufacturing_date between DATE_SUB(now(), INTERVAL 10 DAY) and NOW()')->get();
		$end_time = $this->getTime();
		DB::table('user_tracks')->where('user_track_id',$userTrackId)->update(array('response_duration'=>($end_time-$start_time)));
		//echo "<pre>"; print_r($inventories); die;			
		return View::make('reports.production')->with(array('locations'=>$locations,'inventories'=>json_encode($inventories),'materialCode'=>json_encode($materialCode),'batchNo'=>json_encode($batchNo)));
		
	}  

	public function searchProduction()
	{
		$data = Input::get();
		$start_time = $this->getTime();
		$userTrackId = DB::table('user_tracks')->insertGetId(array('user_id'=>session::get('userId'),'service_name'=>'Live Production Packing','service_type'=>'Web','status'=>1,'manufacturer_id'=>$this->customerId,'message'=>'search by this parameter'.json_encode($data)));
		$where = '';
		if(isset($data['material_code']) && $data['material_code']!='')
			$where .= 'material_code like ("%'.$data['material_code'].'%") and ';

		if(isset($data['batch_no']) && $data['batch_no']!='')
			$where .= 'batch_no like ("%'.$data['batch_no'].'%") and ';

		if(isset($data['location_id']) && $data['location_id']!='')
			$where .= 'location_id = '.$data['location_id'].' and ';

		if(isset($data['from_date']) && $data['from_date']!='')
			$where .= 'DATE(manufacturing_date) >= "'.date('Y-m-d',strtotime($data['from_date'])).'" and ';

		if(isset($data['to_date']) && $data['to_date']!='')
			$where .= 'DATE(manufacturing_date) <= "'.date('Y-m-d',strtotime($data['to_date'])).'" and ';

		//echo $where; die;
		$query = DB::table('rpt_production')->select(DB::raw('DATE(manufacturing_date) as date,product_name,category_name,material_code,qty,location_name,batch_no,po_number,shift as shiftNo'));
						
		if(!empty($where))
			$query = $query->whereRaw(rtrim($where,' and '));
							
		$results = $query->orderBy('manufacturing_date','desc')->get();
		$end_time = $this->getTime();
		DB::table('user_tracks')->where('user_track_id',$userTrackId)->update(array('response_duration'=>($end_time-$start_time)));
		echo json_encode($results); die;

	}


	public function exportProductionEntry()
	{
		$postDatas = Input::get();
		$start_time = $this->getTime();
		$userTrackId = DB::table('user_tracks')->insertGetId(array('user_id'=>session::get('userId'),'service_name'=>'Live Production Packing','service_type'=>'Web','status'=>1,'manufacturer_id'=>$this->customerId,'message'=>'Request for export'.json_encode($postDatas)));
		$searchCondition = array();
		$i=0;
		foreach ($postDatas as $key => $postData) {
			if(empty($postData))
				unset($postDatas[$key]);
			else{
				if($key=='from_date' || $key=='to_date')
				{
					$searchCondition[$i]['fieldName'] = "DATE(".$postDatas['rpt_dateField'].")";
					$searchCondition[$i]['fieldValue'] = date('Y-m-d',strtotime($postData));
					$searchCondition[$i]['fieldCondition'] = ($key=='to_date') ? 'lessthenequal' : 'graterthenequal';
				}elseif($key=='material_code' || $key=='batch_no'){
					$searchCondition[$i]['fieldName'] = $key;
					$searchCondition[$i]['fieldValue'] = $postData;
					$searchCondition[$i]['fieldCondition'] = 'like';
				}elseif($key=='location_id'){
					$searchCondition[$i]['fieldName'] = $key;
					$searchCondition[$i]['fieldValue'] = $postData;
					$searchCondition[$i]['fieldCondition'] = 'equal';
				}
				$i++;
			}
		}
		$reportId = db::table('rpt_config_master')->where('rpt_key',$postDatas['rpt_key'])->pluck('rpt_config_id');
		$data = array();
		$data['report_config_id']=$reportId;
		$data['export_search_condition'] =json_encode($searchCondition);
		$data['status'] = 0;
		$data['request_date']=date('Y-m-d H:i:s');
		$data['sentmail']=1;
		$data['user_id'] = session::get('userId');
		$data['manufacturer_id'] = $this->customerId;
			
		DB::table('export_excel')->insert($data);
		$end_time = $this->getTime();
		DB::table('user_tracks')->where('user_track_id',$userTrackId)->update(array('response_duration'=>($end_time-$start_time)));
		echo 1; die;
	}
	

	public function exportExcel()
	{
		
		$result = DB::table('export_excel')->where(array('status'=>0,'cron_running'=>0))->take(1)->orderBy('export_excel_id','asc')->get();
		//echo "<pre>"; print_r($result); die;
		if(!empty($result))
		{
			
			DB::table('export_excel')->update(array('cron_running'=>1));
			
			$where='manufacturer_id='.$result[0]->manufacturer_id.' and ';
			
			$conditionArr = json_decode($result[0]->export_search_condition);
			
			$operator = '';
			$searchCriteria = "'Search Criteria'";
			foreach($conditionArr as $key=>$condition){
				
				if($condition->fieldCondition=='equal')
					$operator = '=';
				elseif($condition->fieldCondition=='graterthenequal')
					$operator = '>=';
				elseif($condition->fieldCondition=='lessthenequal')
					$operator = '<=';
				elseif($condition->fieldCondition=='like')
					$operator = 'like';

				if(!empty($operator) && $operator!='like'){
					$where .= $condition->fieldName.$operator."'".$condition->fieldValue."' and ";
					$searchCriteria .= ','."'".$condition->fieldName."'".','."'".$condition->fieldValue."'";
				}elseif(!empty($operator) && $operator=='like'){
					$where .= $condition->fieldName.' '.$operator."('%".$condition->fieldValue."%')".' and ';
					$searchCriteria .= ','."'".$condition->fieldName."'".','."'".$condition->fieldValue."'";
				}
			}

			$reportColumnDetails = DB::table('rpt_config_details')->where(array('rpt_config_id'=>$result[0]->report_config_id,'manufacturer_id'=>$result[0]->manufacturer_id))->get();

			$displayColumnName = implode(',', array_column(json_decode(json_encode($reportColumnDetails),true), 'rpt_display_name')) ;
			
			$exportColumnName = implode(',', array_column(json_decode(json_encode($reportColumnDetails),true), 'rpt_column_name')) ;
			
			$exportTableName = DB::table('rpt_config_master')->where(array('rpt_config_id'=>$result[0]->report_config_id))->pluck('rpt_table_name');

			if(!empty($where))
				$where = rtrim($where,' and ');
		
			$filePathResult = DB::select('show variables like "secure_file_priv"');
			
			$rowCount = DB::table($exportTableName)->whereRaw($where)->count($reportColumnDetails[0]->rpt_column_name);
			
			$loopCount = ($rowCount > 400000) ? ceil($rowCount/400000) : 1; 
			$connectionString = "mysql -h".Config::get('database.connections.mysql.host')." -u".Config::get('database.connections.mysql.username')." -p'".Config::get('database.connections.mysql.password')."' ".Config::get('database.connections.mysql.database')." -e ";
			$fileNames = array();
			$scCount = 
			$dcCount = count(array_column(json_decode(json_encode($reportColumnDetails),true), 'rpt_display_name'));
			if( count(explode(',',$searchCriteria)) <  $dcCount)
			{
				$loop = $dcCount - count(explode(',',$searchCriteria));
				//echo $loop; die;
				for($i=0;$i<$loop;$i++)	
					$searchCriteria .=','."' '";
			}
			$CompanyName = "'Vguard Industry Ltd.'";
			for($i=1;$i<$dcCount;$i++)	
				 $CompanyName .=','."' '";
			$CompanyName = '(SELECT '.$CompanyName.' LIMIT 0) UNION ALL';
			$searchCriteria = '(SELECT '.$searchCriteria.' LIMIT 1) UNION ALL';	
			for($i=0;$i<$loopCount;$i++){
				$fileNames[$i] = "Production_Report_".$i."_".date('YmdHis');

				$sql = "\"".$CompanyName.$searchCriteria."(SELECT ".$displayColumnName." LIMIT 2) UNION ALL (SELECT ".$exportColumnName." from ".$exportTableName." WHERE ".$where." LIMIT ".($i*400000).", 400000) \" | sed 's/\\t/\",\"/g;s/^/\"/;s/$/\"/;s/\\n//g' > ".public_path('download/excelExp/').$fileNames[$i].".csv";
				
				echo $sql; die;
				//INTO OUTFILE '".$filePathResult[0]->Value.$fileNames[$i]."' FIELDS TE| RMINATED BY ',' ENCLOSED BY '\\\"' LINES TERMINATED BY '\\n' 
				//echo "<br>";
				exec($connectionString.$sql);
			}
			
			$urls = '';
			if(!empty($fileNames)){
				foreach ($fileNames as $fileName) {
					//exec('unoconv -f xlsx '.public_path('download/excelExp').'/'.$fileName.".csv");
					$urls .= url('download/excelExp/'.$fileName.'.csv').",";
					//unlink(public_path('download/excelExp/').$fileName.".csv");
				}

				$urls = str_replace(',','<br>',trim($urls,','));

				$emailResult = DB::table('users')->where('user_id',$result[0]->user_id)->get(['email','firstname','lastname']);

	 			Mail::send('reports.export_email',array('username'=>$emailResult[0]->firstname.',','url'=>$urls),function($message) use($emailResult){

	 				 $message->to($emailResult[0]->email, $emailResult[0]->firstname.' '.$emailResult[0]->lastname)->subject('Prodution Report');	
	 			});

	 			DB::table('export_excel')->where('export_excel_id',$result[0]->export_excel_id)->update(array('status'=>1));
			}
			
			DB::table('export_excel')->update(array('cron_running'=>0));
			
			    
		}  
	}

	public function dispatch($supplier='')
	{
		parent::Breadcrumbs(array('Home'=>'/')); 
		$data = Input::get();
		$start_time = $this->getTime();
		if($supplier=='supplier'){
			$userTrackId = DB::table('user_tracks')->insertGetId(array('user_id'=>session::get('userId'),'service_name'=>'Live Supplier Dispatch','service_type'=>'Web' ,'status'=>1,'manufacturer_id'=>$this->customerId,'message'=>'Report access by user'));
			$locations = DB::table('locations')->whereIn('location_type_id',array(873))->get(['location_id','location_name']);
			
		}elseif($supplier=='channel'){	
			$userTrackId = DB::table('user_tracks')->insertGetId(array('user_id'=>session::get('userId'),'service_name'=>'Live Channel Primary Sales','service_type'=>'Web' ,'status'=>1,'manufacturer_id'=>$this->customerId,'message'=>'Report access by user'));
			$locations = DB::table('locations')->whereIn('location_type_id',array(872))->get(['location_id','location_name']);
		}else{	
			$userTrackId = DB::table('user_tracks')->insertGetId(array('user_id'=>session::get('userId'),'service_name'=>'Live Production Dispatch','service_type'=>'Web' ,'status'=>1,'manufacturer_id'=>$this->customerId,'message'=>'Report access by user'));
			$locations = DB::table('locations')->whereIn('location_type_id',array(871,872))->get(['location_id','location_name']);
		}	
		$materialCode = array_column(json_decode(json_encode(DB::table('products')->where(array('product_type_id'=>8003,'is_active'=>1))->groupBy('material_code')->get(['material_code'])),true),'material_code');

		$batchNo = array_column(json_decode(json_encode(DB::table('rpt_dispatch')->groupBy('batch_no')->get(['batch_no'])),true),'batch_no');
		if($supplier=='supplier')
			$results = DB::table('rpt_dispatch')->select(DB::raw('DATE(dispatch_date) as date,product_name,document_no,material_code,quantity, batch_no, category_name, src_loc_name, dest_loc_name, tp_id, vehicle_no, product_id, uom_name'))->orderBy('dispatch_date','desc')->where('transition_id',717)->groupBy(array('dispatch_date','product_id','batch_no'))->get();
		elseif($supplier=='channel')
			$results = DB::table('rpt_dispatch')->select(DB::raw('DATE(dispatch_date) as date,product_name,document_no,material_code, quantity, batch_no, category_name, src_loc_name, dest_loc_name, tp_id, vehicle_no, product_id, uom_name'))->orderBy('dispatch_date','desc')->where('transition_id',714)->groupBy(array('dispatch_date','product_id','batch_no'))->get();
		else	
			$results = DB::table('rpt_dispatch')->select(DB::raw('DATE(dispatch_date) as date,product_name,document_no,material_code,quantity, batch_no, category_name, src_loc_name, dest_loc_name, tp_id, vehicle_no, product_id, uom_name'))->orderBy('dispatch_date','desc')->whereIn('transition_id',array(713,703))->groupBy(array('dispatch_date','product_id','batch_no'))->get();
		//echo "<pre>"; print_r($inventories); die;			
		$end_time = $this->getTime();
		DB::table('user_tracks')->where('user_track_id',$userTrackId)->update(array('response_duration'=>($end_time-$start_time)));
		return View::make('reports.dispatch')->with(array('locations'=>$locations,'results'=>json_encode($results),'materialCode'=>json_encode($materialCode),'batchNo'=>json_encode($batchNo),'supplier'=>$supplier));
	}

	public function searchDispatch()
	{
		$data = Input::get();
		$start_time = $this->getTime();
		if(isset($data['supplier']) && $data['supplier']=='yes'){
			$where = 'transition_id =717 and ';
			$userTrackId = DB::table('user_tracks')->insertGetId(array('user_id'=>session::get('userId'),'service_name'=>'Live Supplier Dispatch','service_type'=>'Web' ,'status'=>1,'manufacturer_id'=>$this->customerId,'message'=>'search by this parameter'.json_encode($data)));
		}elseif(isset($data['supplier']) && $data['supplier']=='channel'){
			$where = 'transition_id =714 and ';
			$userTrackId = DB::table('user_tracks')->insertGetId(array('user_id'=>session::get('userId'),'service_name'=>'Live Channel Primary Sales','service_type'=>'Web' ,'status'=>1,'manufacturer_id'=>$this->customerId,'message'=>'search by this parameter'.json_encode($data)));
		}else{
			$where = 'transition_id in (713,703) and ';
			$userTrackId = DB::table('user_tracks')->insertGetId(array('user_id'=>session::get('userId'),'service_name'=>'Live Prodution Dispatch','service_type'=>'Web' ,'status'=>1,'manufacturer_id'=>$this->customerId,'message'=>'search by this parameter'.json_encode($data)));
		}

		if(isset($data['material_code']) && $data['material_code']!='')
			$where .= 'material_code like ("%'.$data['material_code'].'%") and ';

		if(isset($data['batch_no']) && $data['batch_no']!='')
			$where .= 'batch_no like ("%'.$data['batch_no'].'%") and ';

		if(isset($data['location_id']) && $data['location_id']!='')
			$where .= 'src_loc_id = '.$data['location_id'].' and ';

		if(isset($data['from_date']) && $data['from_date']!='')
			$where .= 'DATE(dispatch_date) >= "'.date('Y-m-d',strtotime($data['from_date'])).'" and ';

		if(isset($data['to_date']) && $data['to_date']!='')
			$where .= 'DATE(dispatch_date) <= "'.date('Y-m-d',strtotime($data['to_date'])).'" and ';

//		echo $where; die;
		$query = DB::table('rpt_dispatch')->select(DB::raw('DATE(dispatch_date) as date,product_name,document_no,material_code,quantity,batch_no,po_number,src_loc_name,dest_loc_name,tp_id,vehicle_no'));
						
		if(!empty($where))
			$query = $query->whereRaw(rtrim($where,' and '));
							
		$results = $query->orderBy('dispatch_date','desc')->get();
		$end_time = $this->getTime();
		DB::table('user_tracks')->where('user_track_id',$userTrackId)->update(array('response_duration'=>($end_time-$start_time)));
		echo json_encode($results); die;

	}


    public function userLogin()
    {
    	parent::Breadcrumbs(array('Home'=>'/')); 

      $user_id = Session::get('userId');
      $customerId = (!empty(session::get('customerId'))) ? session::get('customerId') : 0;
      
      if($customerId == 0){
        //
      }
        $location_types=DB::table('location_types')
                         ->where(['manufacturer_id'=>$customerId,'is_deleted'=>0])
                         ->whereIn('location_type_name',['plant','warehouse','supplier'])->get();
        
        $users= DB::table('users')->where('customer_id',$customerId)->lists('username','user_id');                        
      
      return View::make('reports.userlogin',compact('location_types','users','customerId'));
    }

public function getData($location_type,$user,$customer=0)
    {
        $data = array();        
        if($user !=0){
           $data =   DB::table('users as u')
                       ->join('user_roles as ur','ur.user_id','=','u.user_id')
                       ->join('roles as r','r.role_id','=','ur.role_id')
                       ->join('locations as l','l.location_id','=','u.location_id')                       
                       ->join('location_types as lt','lt.location_type_id','=','l.location_type_id')
                       ->where(['customer_id'=>$customer,'u.user_id'=>$user])
                       ->take(1)
                       ->get(['username','u.firstname','u.lastname','email','u.phone_no',DB::raw('case when u.is_active=1 then "ACTIVE" else "IN-ACTIVE" end as status'),'u.created_on','r.name as role','lt.location_type_name','last_login']);
        }
        else{
         $data =   DB::table('users as u')
                       ->join('user_roles as ur','ur.user_id','=','u.user_id')
                       ->join('roles as r','r.role_id','=','ur.role_id')
                       ->join('locations as l','l.location_id','=','u.location_id')  
                       ->join('location_types as lt','lt.location_type_id','=','l.location_type_id')                     
                       ->where(['customer_id'=>$customer])
                       ->whereIn('l.location_type_id',explode(',',$location_type))
                       ->get(['username','u.firstname','u.lastname','email','u.phone_no',DB::raw('case when u.is_active=1 then "ACTIVE" else "IN-ACTIVE" end as status'),'u.created_on','r.name as role','lt.location_type_name','last_login']);
        }

        $data = json_encode($data);
        return $data;

    }


   public function getExportData($location_type,$user,$customer=0,$type){

$data = array();        
//$data[] = ['username'=>'Username','firstname'=>'FirstName','lastname'=>'LastName','email'=>'Email','phone_no'=>'PhoneNo','status'=>'Status','created_on'=>'CreatedOn','role'=>'Role','locaton_type_name'=>'LocationType','last_login'=>'Lastlogin'];

 
        if($user !=0){
           $result =   DB::table('users as u')
                       ->join('user_roles as ur','ur.user_id','=','u.user_id')
                       ->join('roles as r','r.role_id','=','ur.role_id')
                       ->join('locations as l','l.location_id','=','u.location_id')                       
                       ->join('location_types as lt','lt.location_type_id','=','l.location_type_id')
                       ->where(['customer_id'=>$customer,'u.user_id'=>$user])
                       ->take(1)
                       ->get(['username','u.firstname','u.lastname','email','u.phone_no',DB::raw('case when u.is_active=1 then "ACTIVE" else "IN-ACTIVE" end as status'),'u.created_on','r.name as role','lt.location_type_name','last_login']);
        }
        else{
         $result =   DB::table('users as u')
                       ->join('user_roles as ur','ur.user_id','=','u.user_id')
                       ->join('roles as r','r.role_id','=','ur.role_id')
                       ->join('locations as l','l.location_id','=','u.location_id')                       
                       ->join('location_types as lt','lt.location_type_id','=','l.location_type_id')                     
                       ->where(['customer_id'=>$customer])
                       ->whereIn('l.location_type_id',explode(',',$location_type))
                       ->get(['username','u.firstname','u.lastname','email','u.phone_no',DB::raw('case when u.is_active=1 then "ACTIVE" else "IN-ACTIVE" end as status'),'u.created_on','r.name as role','lt.location_type_name','last_login']);
        }


  foreach($result as $res){
  	$data[] = ['Username'=>$res->username,'Firstname'=>$res->firstname,'Lastname'=>$res->lastname,'Email'=>$res->email,'Phoneno'=>$res->phone_no,'Status'=>$res->status,'Created_on'=>$res->created_on,'Role'=>$res->role,'Location_type'=>$res->location_type_name,'Lastlogin'=>$res->last_login];
  }

//print_r($data); die;
//return 'hieee';
ob_clean(); ob_end_clean();
return Excel::create('UserLogin'.$this->getTime(), function($excel) use ($data) {
            $excel->sheet('mySheet', function($sheet) use ($data)
            {
                $sheet->fromArray($data);
            });
        })->download($type);

   }



}
