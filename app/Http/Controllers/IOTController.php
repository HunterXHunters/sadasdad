<?php

use Central\Repositories\CustomerRepo;

class IOTController extends \BaseController 
{
	private $custRepo;
	
    function __construct(CustomerRepo $custRepo) 
    {
    	$this->custRepo = $custRepo;
  	}

    public function index()
    {	
      parent::Breadcrumbs(array('Home'=>'/','Import IOT Report'=>'#'));
        
        $currentUserId = \Session::get('userId');
         
          $errorr = "result ok";
        return View::make('IOTreport.index')->with('error',$errorr);    
    }

    public function importiot(){

    $file_data    =  Input::file('upload_iot_file');
      $file_extension  = $file_data->getClientOriginalExtension();
      $msg = "";

      if( $file_extension != 'xlsx'){

        $msg .= "Invalid file type" . PHP_EOL;
              return 'Invalid file type';

          }else{

        if (Input::hasFile('upload_iot_file')) {
            $path                           = Input::file('upload_iot_file')->getRealPath();
            $data                           = $this->readExcel($path);
            $file_data                      = Input::file('upload_iot_file');
            $result                         = json_decode($data['prod_data'], true);
            $headers                        = json_decode($data['cat_data'], true);
            
            $headers1                       = array('S.no','Serial number
','MFG Date / PKG Date','Material Code','Product Name','Parent IOT','Primary IOT','Category','Location Type');

    if(!empty($headers) && count($headers) !=0){
      if(isset($headers['serial_number'])  && isset($headers['sno'])){
            $concatids=[];
            foreach ($result as $key => $value) {
              $concatids[] = array(
                'serial_num' =>$value['serial_number'],
              ); 
            }
          $ids =  implode(',', array_column($concatids, 'serial_num'));
          $details = $this->getdetilsByIds($ids);
          $iot_array = array();
          $recivedids=[];
        foreach ($details as  $value) {  
          if(array_key_exists($value->primary_iot,$iot_array)){
            $iot_array[$value->primary_iot]['primary_iot'] = $value->primary_iot;
            $iot_array[$value->primary_iot]['mfg_date'] = $value->mfg_date;
            $iot_array[$value->primary_iot]['material_code'] = $value->material_code;
            $iot_array[$value->primary_iot]['product_name'] = $value->product_name;
            $iot_array[$value->primary_iot]['parent_iot'] = $value->parent_iot;
            $iot_array[$value->primary_iot]['category_name'] = $value->category_name;
            $iot_array[$value->primary_iot]['ERP_Code_of_Manufacturing_Location'] =$value->ERP_Code_of_Manufacturing_Location;
            $iot_array[$value->primary_iot]['batch_no'] = $value->batch_no;
            $iot_array[$value->primary_iot]['po_number'] = $value->po_number;
            $iot_array[$value->primary_iot]['manufacturing_name'] =$value->manufacturing_name;

              $iot_array[$value->primary_iot]['Dest_Loc'] = $value->Des_Loc;
              $iot_array[$value->primary_iot]['update_time'] = $value->update_time;
              $iot_array[$value->primary_iot]['location_type_name'] =$value->location_type_name;
              $iot_array[$value->primary_iot]['name'] = $value->name;
          } else {
            $iot_array[$value->primary_iot]['2Dest_Loc'] = $value->Des_Loc;
            $iot_array[$value->primary_iot]['2update_time'] = $value->update_time;
            $iot_array[$value->primary_iot]['2location_type_name'] =$value->location_type_name;
            $iot_array[$value->primary_iot]['2name'] = $value->name;
            $iot_array[$value->primary_iot]['2manufacturing_name'] =$value->manufacturing_name;
          }
        }
        
      //  echo "<pre/>";print_r($iot_array);exit;
        $headers = array('Uploaded Serial Number','MFG Date','Product Code','Product Name','Primary IOT','Parent IOT','Category','MFG Name','Batch No','Prd Ord Number','Packing Name','Dest Location','Transition Time','Dest Manufacture','Dest Location End','Final Billing Date');

        Excel::create('IOT Report Sheet-', function($excel) use($headers, $iot_array) 
            {
                $excel->sheet("IOT sheet", function($sheet) use($headers, $iot_array)
                {
                    $sheet->loadView('IOTreport.downloadtemplate', array('headers' => $headers, 'data' => $iot_array)); 
                });
                ob_end_clean(); 
            })->export('xlsx');

      }else{

        $errorr = "Invalid file";
        return View::make('IOTreport.index')->with('error',$errorr);

        //return "Invalid file";
      }
      }else{
        $errorr= "please provide some data";
        return View::make('IOTreport.index')->with('error',$errorr);
        
      }

    } 
    }
  } 


  public function readExcel($path) {
        try {
            $headerRowNumber = 1;
            Config::set('excel.import.startRow', $headerRowNumber);
            Config::set('excel.import.heading', 'numeric');
            $cat_data = Excel::selectSheetsByIndex(0)->load($path, function($reader) {
                        
                    })->first();
            $headerRowNumber = 1;
            Config::set('excel.import.startRow', $headerRowNumber);
            Config::set('excel.import.heading', 'slugged');
            $prod_data = Excel::selectSheetsByIndex(0)->load($path, function($reader) {
                        
                    })->get();
            $data['cat_data'] = $cat_data;
            $data['prod_data'] = $prod_data;
            return $data;
        } catch (\ErrorException $ex) {
            Log::error($ex->getMessage());
            Redirect::to('/')->send();
        }
    }

    public function getdetilsByIds($ids){
      $query1 = "select * from (
        select  e.mfg_date,p.material_code,p.name AS product_name,e.primary_id AS primary_iot,
        e.parent_id AS parent_iot,c.name AS category_name,
        l.erp_code AS ERP_Code_of_Manufacturing_Location,e.batch_no,e.po_number, l.location_name AS manufacturing_name,
        (select location_name from locations WHERE location_id = th.dest_loc_id) AS 'Des_Loc',th.update_time,lt.location_type_id,lt.location_type_name,tm.name from eseal_5 AS e 
        inner join products AS p ON e.pid=p.product_id
        inner join track_details AS td ON td.code=e.primary_id
        inner join track_history AS th ON td.track_id=th.track_id
        inner join transaction_master AS tm ON tm.id=th.transition_id
        inner join categories AS c ON c.category_id=p.category_id
        inner join locations AS l ON th.src_loc_id=l.location_id
        inner join location_types AS lt ON lt.location_type_id=l.location_type_id WHERE primary_id IN(".$ids.") AND level_id=0  AND tm.name IN('PO Packing','Packing')
        UNION ALL
        select e.mfg_date,p.material_code,p.name AS product_name,e.primary_id AS primary_iot,
        e.parent_id AS parent_iot,c.name AS category_name,
        l.erp_code AS ERP_Code_of_Manufacturing_Location,e.batch_no,e.po_number, l.location_name AS manufacturing_name,
        (select location_name from locations WHERE location_id = th.dest_loc_id) AS 'Des_Loc',th.update_time,lt.location_type_id,lt.location_type_name,tm.name FROM 
        (select MAX(th.track_id) AS mx,e1.*
        from eseal_5 AS e1 
        inner join track_details AS td ON td.code=e1.primary_id
        inner join track_history AS th ON td.track_id=th.track_id
        inner join transaction_master AS tm ON tm.id=th.transition_id
        WHERE e1.primary_id IN(".$ids.") AND e1.level_id=0 AND th.dest_loc_id!='' GROUP BY e1.primary_id) AS e
        inner join products AS p ON e.pid=p.product_id
        inner join track_history AS th ON e.mx=th.track_id
        inner join transaction_master AS tm ON tm.id=th.transition_id
        inner join categories AS c ON c.category_id=p.category_id
        inner join locations AS l ON th.src_loc_id=l.location_id
        inner join location_types AS lt ON lt.location_type_id=l.location_type_id
        ) myta ORDER BY primary_iot";
//echo "<pre/>";print_r($query1);exit;
        $data = DB::select(DB::raw($query1));
        //var_dump($data);exit;
        return $data;

    }
    

}