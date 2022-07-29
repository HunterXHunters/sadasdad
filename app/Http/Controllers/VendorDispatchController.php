<?php
//namespace API;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Central\Repositories\RoleRepo;

 
class VendorDispatchController extends \BaseController {
    private $demo_json = "";
    private $demo = array();
    public function __construct(RoleRepo $roleAccess) 
	{
		$this->roleAccess = $roleAccess;	
	} 
    
    public function verifyImportProducts()
    {    
           try{// http://vguard.local/demoapi/vendordispatch/{"token":"b3qg6ncj4icjjkwq","iots":"9848119963990091,4975584139412876,3476901732075991,3704936079433320,8081445815879845,9848119963990456","matcode":"1500227,1500246","location_id":5}
           //$data1 = Input::get();
           //print_r($data1);
            Log::info(__FUNCTION__.' : '.print_r(Input::get(),true));           
           $status =1;
           $message = 'data given prperly';
           $x =0;
           $access_token =  Input::get('access_token');
           $iots = Input::get('iots');
           $matcode_arr = explode(",",Input::get('matcode'));
           $vendor_code = Input::get('vendor_code');
           $mfgId = $this->roleAccess->getMfgIdByToken($access_token);           
           $iots_arr = explode(",",$iots);
           $iots_final_response = array();

           if(!$vendor_code)
            throw new Exception('The vendor code is empty');
           else{
            $location_id = Locations::where(['erp_code'=>ltrim($vendor_code,0),'manufacturer_id'=>$mfgId])->pluck('location_id');
            if(!$location_id)
                throw new Exception('There is no vendor associated with erp code: '.$vendor_code);
           }

           if(count($iots_arr)>0)
           {
                $iots_final_response = array(); 
                $iots_status = array();              
                $iots_available = DB::table("eseal_".$mfgId)
                             ->leftJoin('track_history', 'eseal_'.$mfgId.'.track_id', '=', 'track_history.track_id')
                             ->leftJoin('products', 'eseal_'.$mfgId.'.pid', '=', 'products.product_id')
                             ->whereIn('eseal_'.$mfgId.'.primary_id',$iots_arr)
                             ->select('eseal_'.$mfgId.'.primary_id', 'track_history.src_loc_id', 'track_history.dest_loc_id','products.name','products.material_code','eseal_'.$mfgId.'.level_id','batch_no','eseal_'.$mfgId.'.pkg_qty')
                             ->get();
               
                $iots_available_arr = array();
                foreach($iots_available as $iot)
                {
                    $iots_available_arr[] = $iot->primary_id;    
                }                
                
                // IOTs which are not available to ESeal System
                $iots_not_available = array_values(array_diff($iots_arr,$iots_available_arr));    

                if(count($iots_available)>0 && count($iots_not_available)==0)
                {
                    for($i=0; $i < count($iots_available); $i++)
                    {
                        if($iots_available[$i]->dest_loc_id == 0 && $iots_available[$i]->src_loc_id == $location_id)
                        {
                            if(!in_array($iots_available[$i]->material_code,$matcode_arr))
                            {
                                $iots_status[] = array('id'=>$iots_available[$i]->primary_id, 'name'=>$iots_available[$i]->name, 'matcode'=>$iots_available[$i]->material_code, 'qty'=>$iots_available[$i]->pkg_qty, 'level'=>$iots_available[$i]->level_id,'batch_no'=>$iots_available[$i]->batch_no,'status' => 0, 'message'=>'Invalid Material Code');
                                $x++;
                            }
                            else
                            {
                                $iots_status[] = array('id'=>$iots_available[$i]->primary_id, 'name'=>$iots_available[$i]->name, 'matcode'=>$iots_available[$i]->material_code, 'qty'=>$iots_available[$i]->pkg_qty, 'level'=>$iots_available[$i]->level_id,'batch_no'=>$iots_available[$i]->batch_no, 'status' => 1, 'message'=>'Success');
                            }
                        }
                        else
                        {
                            $iots_status[] = array('id'=>$iots_available[$i]->primary_id, 'name'=>$iots_available[$i]->name, 'matcode'=>$iots_available[$i]->material_code, 'qty'=>$iots_available[$i]->pkg_qty, 'level'=>$iots_available[$i]->level_id,'batch_no'=>$iots_available[$i]->batch_no, 'status' => 0, 'message'=>'ID belongs to other location');
                            $x++; 
                        }
                       
                    }                    
                    $iots_final_response = $iots_status;
                    if($i == count($iots_available)){
                    	$status =0;
                    	$message = 'Fail';
                    }
                } 
                elseif(count($iots_available)>0 && count($iots_not_available)>0)
                {
                    for($i=0; $i < count($iots_available); $i++)
                    {
                        if($iots_available[$i]->dest_loc_id == 0 && $iots_available[$i]->src_loc_id == $location_id)
                        {
                            if(!in_array($iots_available[$i]->material_code,$matcode_arr))
                            {
                                $iots_status[] = array('id'=>$iots_available[$i]->primary_id, 'name'=>$iots_available[$i]->name, 'matcode'=>$iots_available[$i]->material_code, 'qty'=>$iots_available[$i]->pkg_qty, 'level'=>$iots_available[$i]->level_id,'batch_no'=>$iots_available[$i]->batch_no, 'status' => 0, 'message'=>'Invalid Material Code');
                                $x++;
                            }
                            else
                            {
                                $iots_status[] = array('id'=>$iots_available[$i]->primary_id, 'name'=>$iots_available[$i]->name, 'matcode'=>$iots_available[$i]->material_code, 'qty'=>$iots_available[$i]->pkg_qty, 'level'=>$iots_available[$i]->level_id,'batch_no'=>$iots_available[$i]->batch_no, 'status' => 1, 'message'=>'Success');
                            }    
                        } 
                        else
                        {
                            $iots_status[] = array('id'=>$iots_available[$i]->primary_id, 'name'=>$iots_available[$i]->name, 'matcode'=>$iots_available[$i]->material_code, 'qty'=>$iots_available[$i]->pkg_qty, 'level'=>$iots_available[$i]->level_id,'batch_no'=>$iots_available[$i]->batch_no, 'status' => 0, 'message'=>'ID belongs to other location');
                            $x++;
                        }            
                    }
                    
                    for($i=0; $i < count($iots_not_available); $i++)
                    {  
                        $iots_status[] = array('id'=>$iots_not_available[$i], 'name'=>'', 'matcode'=>'', 'qty'=>'', 'level'=>'','batch_no'=>'','status' => 0, 'message'=>'Invalid Data');
                        $x++;
                    }
                    $message = 'Partial';
                    $status=1;
                    $iots_final_response = $iots_status;
                }
                else
                {
                    for($i=0; $i < count($iots_not_available); $i++)
                    {
                        $iots_status[] = array('id'=>$iots_not_available[$i], 'name'=>'', 'matcode'=>'', 'qty'=>'', 'level'=>'', 'status' => 0, 'message'=>'Invalid Data');
                    }
                    $message = 'Fail';
                    $status=0;
                    $iots_final_response = $iots_status;
                }                
           }
           //dd($iots_final_response);           
       }
       catch(Exception $e){
        $status =0 ;
        $message = $e->getMessage();
       }
       Log::info(['Status'=>$status,'Message'=>$message,'Data'=>$iots_final_response]);
       return json_encode(['Status'=>$status,'Message'=>$message,'Data'=>$iots_final_response]);
    }
    
    /*private function Validation($api_data_arr)
    {
            $error_response = array();
            
            // Required field validation
            if($api_data_arr->customer->brand_name == "")
            {
                $error_response[] = "Customer Brand Name Required";
            }
            
            return $error_response;    
    }*/            
}
?>

