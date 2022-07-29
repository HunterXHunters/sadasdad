<?php
use Central\Repositories\CommonRepo;

use Central\Repositories\RoleRepo;

class PalletController extends \BaseController {

	/**
	 * Display a listing of the resource.
	 *
	 * @return Respons
	 */

  private $commonRepo;
  private $roleRepo;


  public function __construct()
    {
        $this->commonRepo = new CommonRepo;
        $this->roleRepo = new RoleRepo;
    }

      

	public function index()
	{
      
		// get all the entity types
       

        // load the view and pass the entitytypes
        parent::Breadcrumbs(array('Home'=>'/','Pallets'=>'#'));
        return View::make('pallet.index');
            

	}


	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create()
	{ 

        $pallettype = $this->commonRepo->getLookupData('Pallet types'); 
        $weights = $this->commonRepo->getLookupData('Capacity UOM');
        $dimensions = $this->commonRepo->getLookupData('Length UOM');
        $capacity_uom = $this->commonRepo->getLookupData('Capacity UOM');
        $currentUserId = \Session::get('userId');
        \Log::info($currentUserId);
        $manufacturerId = DB::table('users')->where('user_id', $currentUserId)->pluck('customer_id');
        \Log::info($manufacturerId);
        if($manufacturerId)
        {  
        $WHDetails=DB::table('wms_entities')->where(array('entity_type_id'=>6001,'org_id'=>$manufacturerId))->lists('entity_name','id');
        }else{
        $WHDetails=DB::table('wms_entities')->where(array('entity_type_id'=>6001))->lists('entity_name','id'); 
        }
  return View::make('pallet.create')->with('capacity_uom',$capacity_uom)->with('pallettype',$pallettype)->with('weights',$weights)->with('dimensions',$dimensions)->with('WHDetails',$WHDetails);		
	}

public function getdata()
 {
     
        $currentUserId = \Session::get('userId');
        \Log::info($currentUserId);
        $manufacturerId = DB::table('users')->where('user_id', $currentUserId)->pluck('customer_id');
        \Log::info($manufacturerId);          
        $getArr = array();
        $finalgetArr = array();
        if($manufacturerId)
        {        
        $pallets = DB::table('wms_pallet')
                   ->leftjoin('master_lookup as pml','wms_pallet.pallet_type_id','=','pml.value')
                   ->leftjoin('master_lookup as wml','wms_pallet.weightUOMId','=','wml.value')
                   ->leftjoin('master_lookup as dml','wms_pallet.dimensionUOMId','=','dml.value')
                   ->leftJoin('wms_entities','wms_entities.id','=','wms_pallet.ware_id')
                   ->select('wms_pallet.*','pml.name as pallet_type_name','wml.name as weightuom',
                    'dml.name as dimensionuom','wms_entities.entity_name') 
                   ->where('wms_pallet.org_id','=',$manufacturerId)
                   ->orderBy('id','desc')->get();
        }else{
        $pallets = DB::table('wms_pallet')
                   ->leftjoin('master_lookup as pml','wms_pallet.pallet_type_id','=','pml.value')
                   ->leftjoin('master_lookup as wml','wms_pallet.weightUOMId','=','wml.value')
                   ->leftjoin('master_lookup as dml','wms_pallet.dimensionUOMId','=','dml.value')
                   ->leftJoin('wms_entities','wms_entities.id','=','wms_pallet.ware_id')
                   ->select('wms_pallet.*','pml.name as pallet_type_name','wml.name as weightuom',
                    'dml.name as dimensionuom','wms_entities.entity_name') 
                   ->orderBy('id','desc')->get();          
        }
        $pallet_details=json_decode(json_encode($pallets),true);

        foreach($pallets as $value)
        {
          


          $getArr['id'] = $value->id;
          $getArr['pallet_id'] = $value->pallet_id;
          $getArr['pallet_type_id'] = $value->pallet_type_name;
          $getArr['weight'] = $value->weight;
          $getArr['weightUOMId'] = $value->weightuom;
          /*$getArr['height'] = $value->height;
          $getArr['width'] = $value->width;
          $getArr['length'] = $value->length;
          $getArr['dimensionUOMId'] = $value->dimensionuom;*/
          $getArr['warehouse'] = $value->entity_name;
          $getArr['actions']='<span style="padding-left:20px;" ><a href="javascript:void(0);" onclick="editPallet(' . "'" . $this->roleRepo->encodeData($value->id). "'" .')" data-target="#basicvalCodeModal"><span class="badge bg-light-blue"><i class="fa fa-pencil"></i></span></a></span>
          <span style="padding-left:5px;" ><a onclick="deletePallet(' . "'" . $this->roleRepo->encodeData($value->id). "'" .')"><span class="badge bg-red"><i class="fa fa-trash-o"></i></span></a></span>';
          $finalgetArr[] = $getArr;
        }

    return json_encode($finalgetArr);
  }

	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
  private function getDate(){
    return date("Y-m-d H:i:s", strtotime('+335 minutes'));
  }
	public function store()
	{
      $noOfPallets=Input::get('no_of_pallets');
      //echo "<pre/>";print_r(Input::get());die;
      $pallet_type_id = Input::get('pallet_type_id');
      $ware_id = Input::get('ware_id');
      $org_id = Input::get('org_id');
      $pweight = Input::get('weight');
      //echo $weight;die;
      $weightUOMId = Input::get('weightUOMId');
      $dimensionUOMId = Input::get('dimensionUOMId');
      $height = Input::get('height');
      $length = Input::get('length');
      $width = Input::get('width');
      $capacity = Input::get('capacity');
      $capacityUOMId = Input::get('capacityUOMId');
      $no_of_pallets = Input::get('no_of_pallets');

      $mfg_id=Input::get('org_id');
      $module_id =DB::Table('master_lookup')->leftjoin('lookup_categories','lookup_categories.id','=','master_lookup.category_id')->where(array('master_lookup.name'=>'PM'))->pluck('value');      
      $user_id = Session::get('userId');
      $username = User::where('user_id',$user_id)->pluck('username');
      $password = Session::get('password');//return $password;
      $pid = Products::where('name','Pallet')->pluck('product_id');
      $transitionTime = date('Y-m-d h:i:s');
      Log::info('Passed Date:');
      Log::info($transitionTime);
      //return Session::get();
      $transitionId = Transaction::where(['name'=>'Pallet Placement','manufacturer_id'=>$mfg_id])->pluck('id');

      //echo "i am here";

      $request = Request::create('scoapi/login', 'POST', array('module_id'=>$module_id,'user_id'=>$username,'password'=>$password));
      $originalInput = Request::input();//backup original input
      Request::replace($request->input());
      $response = Route::dispatch($request)->getContent();
      Log::info('Login response:-');
      Log::info($response);
      $response = json_decode($response);
      if($response->Status == 0){
        return Redirect::to('pallets')->withFlashMessage($response->Message);
      }
     // echo "<pre/>";print_r($response->Data->access_token);die;
      $access_token = $response->Data->access_token;
      $locationId = $this->roleRepo->getLocIdByToken($access_token);//return $locationId;
     /* $getCodesArray = array();
      $getCodesArray = DB::table('eseal_bank_'.$mfg_id)
                      ->where(array('issue_status'=>1,'used_status'=>0))
                      ->take($noOfPallets)
                      ->lists('id');
      $codes = implode(",", $getCodesArray);*/
     $request = Request::create('scoapi/DownloadEsealByLocationId', 'POST', array('module_id'=>$module_id,'access_token'=>$access_token,'srcLocationId'=>$locationId,'qty'=>$noOfPallets));
       $originalInput = Request::input();//backup original input
      Request::replace($request->input());
      $response = Route::dispatch($request)->getContent();
      $response = json_decode($response);
      //echo "<pre/>";print_r($response);die;
     if(!$response->Status){
         return Redirect::to('pallets')->withFlashMessage($response->Message);
      }
      $codes = $response->Codes;
      
      $weight = $pweight;
      $attrJson = json_encode(['weight'=>$weight]);
      //$transitionTime = $this->getDate();

      $request = Request::create('scoapi/BindEsealsWithAttributes', 'POST', array('module_id'=> $module_id,'access_token'=>$access_token,'pid'=>$pid,'ids'=>$codes,'attributes'=>$attrJson,'srcLocationId'=>$locationId,'transitionTime'=>$transitionTime,'isPallet'=>1));
           $originalInput = Request::input();
           Request::replace($request->input());
           $res = Route::dispatch($request)->getContent();
           $res = json_decode($res);
           //return $res;
             if(!$res->Status){
             return Redirect::to('pallets')->withFlashMessage($res->Message);
             }

          $request = Request::create('scoapi/UpdateTracking', 'POST', array('module_id'=>$module_id,'access_token'=>$access_token,'codes'=>$codes,'srcLocationId'=>$locationId,'destLocationId'=>0,'transitionTime'=>$transitionTime,'transitionId'=>$transitionId,'internalTransfer'=>0));
          $originalInput = Request::input();//backup original input
          Request::replace($request->input());
          Log::info($request->input());
          $response = Route::dispatch($request)->getContent();
          $response = json_decode($response);
          
          $esealCodes = explode(",",$codes);

          /*$upqry = DB::Table('eseal_bank_'.$mfg_id)
                      ->whereIn('id',$getCodesArray)
                      ->update(array('used_status'=>1));*/
          //$queries = DB::getQueryLog();
          //return end($queries);

          if($response->Status==1)
          {
              if($no_of_pallets)
              {
                  for($i=0;$i<$no_of_pallets;$i++) 
                  {

                      //$pallet_id = str_random(16);    
                      DB::table('wms_pallet')->insert([
                     'ware_id'=>$ware_id,
                     'org_id'=>$org_id,
                     'pallet_type_id' => $pallet_type_id,
                     'weight'=>$pweight,
                     'weightUOMId'=>$weightUOMId,
                     'height' => $height,
                     'width'=>$width,
                     'length'=>$length,
                     'capacity' => $capacity,
                     'capacityUOMId' => $capacityUOMId,
                     'dimensionUOMId'=>$dimensionUOMId,
                     'pallet_id'=>$esealCodes[$i]
                    ]);

                   /* $queries = DB::getQueryLog();
                    return end($queries);*/

                    /*$upqry = DB::Table('eseal_bank_'.$mfg_id)
                      ->where('id',$pallet_id[$i]->id)
                      //->get();
                      ->update(array('used_status'=>1,'issue_status'=>1,'download_status'=>1));*/
             
                  /*$insQuery=DB::table('eseal_'.$mfg_id)->insert(array('primary_id'=>$pallet_id[$i]->id,'level_id'=>8));*/
                }
          }
          return Redirect::to('pallets')->withFlashMessage('Pallet created Successfully.');
        }
	}

public function view()
{

return View::make('pallet.index');

}


public function delete($id)
{

        $id = $this->roleRepo->decodeData($id);
        $password = Input::get();
        $userId = Session::get('userId');
        $verifiedUser = $this->roleRepo->verifyUser($password['password'], $userId);
        if($verifiedUser >= 1)
        {
        $mfgId=DB::Table('wms_pallet')->where('id', '=', $id)->pluck('org_id');
        $pallet_id=DB::Table('wms_pallet')->where('id', '=', $id)->pluck('pallet_id');
        $query = DB::table('eseal_'.$mfgId)->where('parent_id',$pallet_id);
        if($pallet_id)
          $cnt = $query->where('level_id',0)->count();
        if($cnt > 0){
          return 0;
        }
        DB::Table('wms_pallet')->where('id', '=', $id)->delete();
        DB::table('eseal_'.$mfgId)->where('primary_id',$pallet_id)->delete();
        DB::table('eseal_bank_'.$mfgId)->where('id',$pallet_id)
        ->update(array('used_status'=>0,'location_id'=>0,'level'=>0,'utilizedDate'=>'0000-00-00 00:00:00'));
        return 1;
        }else{
            return "You have entered incorrect password !!";
        }

       

}	
 public function edit($id)
 {

  $id = $this->roleRepo->decodeData($id);
  $pallet= Pallet::find($id);

        $capacity_uom = $this->commonRepo->getLookupData('Capacity UOM');
        $pallettype = $this->commonRepo->getLookupData('Pallet types');
         
        //return $pallettype;
        $weights = $this->commonRepo->getLookupData('Capacity UOM');
        //return $weights;
        $dimensions = $this->commonRepo->getLookupData('Length UOM');
        $currentUserId = \Session::get('userId');
        \Log::info($currentUserId);
        $manufacturerId = DB::table('users')->where('user_id', $currentUserId)->pluck('customer_id');
        \Log::info($manufacturerId);
        if($manufacturerId)
        {  
        $WHDetails=DB::table('wms_entities')->where(array('entity_type_id'=>6001,'org_id'=>$manufacturerId))->lists('entity_name','id');
        }else{
        $WHDetails=DB::table('wms_entities')->where(array('entity_type_id'=>6001))->lists('entity_name','id'); 
        }        
return View::make('pallet.edit')->with('capacity_uom',$capacity_uom)->with('pallet',$pallet)->with('pallettype',$pallettype)->with('weights',$weights)->with('dimensions',$dimensions)->with('WHDetails',$WHDetails);
	        
 }	
public function update($id)

{
            $data=array();
            $data['ware_id']=Input::get('ware_id');
            $data['pallet_id']=Input::get('pallet_id');
            $data['pallet_type_id']=Input::get('pallet_type_id');
            $data['weightUOMId']=Input::get('weightUOMId');
            $data['dimensionUOMId']=Input::get('dimensionUOMId');
            $data['weight']=Input::get('weight');
            $data['height']=Input::get('height');
            $data['width']=Input::get('width');
            $data['length']=Input::get('length');
            $data['capacity']= Input::get('capacity');
            $data['capacityUOMId']= Input::get('capacityUOMId');
            $org_id=Input::get('org_id');
            if(!empty($org_id))
            {
              $data['org_id']=Input::get('org_id');
            }
            //return $data;
             DB::table('wms_pallet')
                ->where('id', $id)
                ->update($data);
/*            $org_id=Input::get('org_id');
            DB::table('wms_pallet')
                ->where('id', $id)
                ->update(array(
                  'ware_id'=>Input::get('ware_id'),
                  'org_id'=>Input::get('org_id'),
                  'pallet_name'=> Input::get('pallet_name'),
                  'pallet_type_id' => Input::get('pallet_type_id'),
                  'weightUOMId' => Input::get('weightUOMId'),
                  'weight'=>Input::get('weight'),
                  'dimensionUOMId' => Input::get('dimensionUOMId'),
                  'height' => Input::get('height'),
                  'width' => Input::get('width'),
                  'length' => Input::get('length')));*/

    
 return Redirect::to('pallets')->withFlashMessage('Pallet updated Successfully.');

 }	

  public function validatepallet()
  {

      $data = Input::get('pallet_name');
      $ware_id=Input::get('ware_id');
      $id = Input::get('id');
      //return $id; 

            if($id)
            {
        
                $palletname = DB::Table('wms_pallet') 
                              ->where('id' ,'!=',$id)
                              ->where('pallet_name','=',$data)
                              ->where('ware_id','=',$ware_id)
                              ->pluck('pallet_name');
                //return $palletname;
            }
            else {
           $palletname = DB::Table('wms_pallet')
                      //->select('name')
                      ->where('pallet_name','=',$data)
                      ->where('ware_id','=',$ware_id)
                      ->pluck('pallet_name');
                    }
           if(empty($palletname))
           {
            //return 'success';
            return json_encode([ "valid" => true ]);
           }                     
          else 
          {
            //return 'fail';
            return json_encode([ "valid" => false ]);
          }          

     }
public function getOrg($id)
{
  $org_id=DB::table('wms_entities')->where('id',$id)->pluck('org_id');
  return $org_id;
}
	
}

