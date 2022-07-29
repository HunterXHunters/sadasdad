<?php
use Central\Repositories\CommonRepo;
use Central\Repositories\RoleRepo;

class RackController extends \BaseController 
{
     
    private $commonRepo;
    private $roleRepo;

    public function __construct()
    {
        $this->commonRepo = new CommonRepo;
         $this->roleRepo = new RoleRepo;
    } 

     public function racktypeindex()
    {
    	
    	parent::Breadcrumbs(array('Home'=>'/','Racks'=>'#'));
    	$racktype=RackType::all();
    	//return 'hi';
    	return View::make('entities.racktypeindex',compact("racktype"));

    }

    public function getrackdata()
	{
		    $currentUserId = \Session::get('userId');
        \Log::info($currentUserId);
        $manufacturerId = DB::table('users')->where('user_id', $currentUserId)->pluck('customer_id');
        \Log::info($manufacturerId); 
        if($manufacturerId)
        { 
          $entity_types = RackType::where('mfg_id',$manufacturerId)->get();  
        } else{
          $entity_types = RackType::all();
        }       
    //$mapEntities = DB::table('wms_eseal')->get();
        $getArr = array();
        $finalgetArr = array();
        foreach($entity_types as $value)
        {
        	
        	$getArr['id'] = $value->id;
        	$getArr['rack_type_name'] = $value->rack_type_name;
        	//$getArr['status'] = $status;
        	$getArr['rack_capacity'] = $value->rack_capacity;
        	//$getArr['rack_capacity'] = $value->rack_capacity;
        	$getArr['no_of_bins'] = $value->no_of_bins;
   //      	$getArr['edit'] = '<a href="/wms/rack/racktypeedit/'.$value->id.'"><img src="/wms/img/edit.png" /></a>'; 
			// $getArr['delete'] = '<a onclick="deleteRackType('.$value->id.')" href="#"><img src="/wms/img/delete.png" /></a>';
            $getArr['actions']='<span style="padding-left:20px;" ><a href="javascript:void(0);" onclick="editRack(' . "'" . $this->roleRepo->encodeData($value['id']). "'" .')" data-target="#basicvalCodeModal"><span class="badge bg-light-blue"><i class="fa fa-pencil"></i></span></a></span>
            <span style="padding-left:5px;" ><a onclick="deleteRackType(' . "'" . $this->roleRepo->encodeData($value['id']). "'" .')"><span class="badge bg-red"><i class="fa fa-trash-o"></i></span></a></span>';
			$finalgetArr[] = $getArr;
        }
       // return 'nikhil kishore';
		return json_encode($finalgetArr);
	}
     public function racktypecreate()
	{
		
		
        // $capacity_uom_id= UomGroup::where('description','capacity')->first()->id;
        // $capacity_uom = DB::table('wms_uom')->where('uom_group_id',$capacity_uom_id)->lists('description', 'id');
        // $capacity_uom = ['' => 'Select Capacity UOM'] + $capacity_uom;
        // $dimension_uom_id= UomGroup::where('description','dimension')->first()->id;
        // $dimension_uom = DB::table('wms_uom')->where('uom_group_id',$dimension_uom_id)->lists('description', 'id');
        // $dimension_uom = ['' => 'Select Dimension UOM'] + $dimension_uom;

        $capacity_uom = $this->commonRepo->getLookupData('Capacity UOM');
        $dimension_uom = $this->commonRepo->getLookupData('Length UOM');
        $currentUserId = \Session::get('userId');
        \Log::info($currentUserId);
        $manufacturerId = DB::table('users')->where('user_id', $currentUserId)->pluck('customer_id');
        \Log::info($manufacturerId);
        if($manufacturerId)
        {  
        $WHDetails=DB::table('wms_entities')->where(array('entity_type_id'=>6001,'org_id'=>$manufacturerId))->lists('entity_name','id');
        $mfgDetails='';
        }else{
        $WHDetails=DB::table('wms_entities')->where(array('entity_type_id'=>6001))->lists('entity_name','id'); 
        $mfgDetails=DB::table('wms_entities')
                    ->join('eseal_customer','wms_entities.org_id','=','eseal_customer.customer_id')
                    ->where('wms_entities.entity_type_id',6001)
                    ->get(array('eseal_customer.brand_name','wms_entities.org_id'));
        //$mfgDetails='';
        }        
		//$dimension_uom=DB::table('wms_uom')->where('uom_group_id',1)->lists('description', 'id');
		
		
		return View::make('entities.racktypecreate',compact("capacity_uom","dimension_uom","WHDetails","manufacturerId","mfgDetails"));
		
	}
     public function racktypestore()
    {
    	
    	DB::table('wms_rack_types')->insert([
			'rack_type_name' => Input::get('rack_type_name'),
      'ware_id' => Input::get('ware_id'),
      'mfg_id' => Input::get('org_id'),
			'rack_height' => Input::get('rack_height'),
			'rack_width' => Input::get('rack_width'),
			'rack_depth' => Input::get('rack_depth'),
			'rack_capacity' => Input::get('rack_capacity'),
			'rack_dimension_id' => Input::get('rack_dimension_id'),
			'rack_capacity_uom_id' => Input::get('rack_capacity_uom_id'),
		  'bin_height' => Input::get('bin_height'),
			'bin_width' => Input::get('bin_width'),
			'bin_depth' => Input::get('bin_depth'),
			'bin_capacity' => Input::get('bin_capacity'),
			'bin_dimension_id' => Input::get('bin_dimension_id'),
			'bin_capacity_uom_id' => Input::get('bin_capacity_uom_id'),
		  'no_of_bins' => Input::get('no_of_bins'),
		]);
     

     return Redirect::to('rack/racktypeindex')->withFlashMessage('RackType Created Successfully'); 
    }

    public function racktypeedit($entity_id)
    {
    	$entity_id = $this->roleRepo->decodeData($entity_id);
    	$racktypes = RackType::find($entity_id);
		if($racktypes->id)
			$rack_type_code = 'RT'.$racktypes->id;
		$racktypes->rack_type_code = $rack_type_code;
		 //$entity_type_id = $entities->entity_type_id;
/*		$capacity_uom_id= UomGroup::where('description','capacity')->first()->id;
        $capacity_uom = DB::table('wms_uom')->where('uom_group_id',$capacity_uom_id)->lists('description', 'id');
        $capacity_uom = ['' => 'Select Capacity UOM'] + $capacity_uom;
        $dimension_uom_id= UomGroup::where('description','dimension')->first()->id;
        $dimension_uom = DB::table('wms_uom')->where('uom_group_id',$dimension_uom_id)->lists('description', 'id');
        $dimension_uom = ['' => 'Select Dimension UOM'] + $dimension_uom;*/
        $capacity_uom = $this->commonRepo->getLookupData('Capacity UOM');
        $dimension_uom = $this->commonRepo->getLookupData('Length UOM');
        $currentUserId = \Session::get('userId');
        \Log::info($currentUserId);
        $manufacturerId = DB::table('users')->where('user_id', $currentUserId)->pluck('customer_id');
        \Log::info($manufacturerId);
        if($manufacturerId)
        {  
        $WHDetails=DB::table('wms_entities')->where(array('entity_type_id'=>6001,'org_id'=>$manufacturerId))->lists('entity_name','id');
        $mfgDetails='';
        }else{
        $WHDetails=DB::table('wms_entities')->where(array('entity_type_id'=>6001))->lists('entity_name','id'); 
        $mfgDetails=DB::table('wms_entities')
                    ->join('eseal_customer','wms_entities.org_id','=','eseal_customer.customer_id')
                    ->where('wms_entities.entity_type_id',6001)
                    ->get(array('eseal_customer.brand_name','wms_entities.org_id'));
        //$mfgDetails='';
        }         
		//return $dimension_uom;
		//return $racktypes->rack_type_name;
        //return 'Hi';
		return View::make('entities.racktypeedit',compact("capacity_uom","dimension_uom","racktypes","mfgDetails","WHDetails","manufacturerId"));
          
    }
    public function racktypeupdate($entity_id)
     {
     	$racktypes = RackType::find($entity_id);
        $racktypes->rack_type_name = Input::get('rack_type_name');
        $racktypes->ware_id = Input::get('ware_id');
        $mfg_id=Input::get('org_id');
        if(!empty($mfg_id) && isset($mfg_id))
        {
          $racktypes->mfg_id = Input::get('org_id');
        }
        $racktypes->rack_height = Input::get('rack_height');
        $racktypes->rack_width = Input::get('rack_width');
        $racktypes->rack_depth = Input::get('rack_depth');
        $racktypes->rack_capacity = Input::get('rack_capacity');
        //$racktypes->status = 1;
       $racktypes->rack_dimension_id = Input::get('rack_dimension_id');
       $racktypes->rack_capacity_uom_id = Input::get('rack_capacity_uom_id');
       $racktypes->bin_height = Input::get('bin_height');
       $racktypes->bin_width = Input::get('bin_width');
       $racktypes->bin_depth = Input::get('bin_depth');
       $racktypes->bin_capacity = Input::get('bin_capacity');
       $racktypes->bin_dimension_id = Input::get('bin_dimension_id');
       $racktypes->bin_capacity_uom_id = Input::get('bin_capacity_uom_id');
       $racktypes->no_of_bins = Input::get('no_of_bins');
        $racktypes->save();
     
      return Redirect::to('rack/racktypeindex')->withFlashMessage('RackType updated Successfully');
     }
      public function racktypedelete($entity_id){
        
        $entity_id = $this->roleRepo->decodeData($entity_id);
        $password = Input::get();
        $userId = Session::get('userId');
        $verifiedUser = $this->roleRepo->verifyUser($password['password'], $userId);
        if($verifiedUser >= 1)
        {
        RackType::where('id',$entity_id)->delete();
        return 1;
        }else{
            return "You have entered incorrect password !!";
        }

       


		//$racktypes->delete();
		//$dimensions = Dimension::find($entity_id);
		//$dimensions->delete();
		//return 'hi';
        return Redirect::to('rack/racktypeindex');
    }

     public function validaterack()
     {

      $data = Input::get('rack_type_name');
      $id = Input::get('id');

            if($id)
            {
        
                $rackname = DB::Table('wms_rack_types') 
                              ->where('id' ,'!=',$id)
                              ->where('rack_type_name',$data)
                              ->pluck('rack_type_name');
            }
            else {
           $rackname = DB::Table('wms_rack_types')
                      //->select('name')
                      ->where('rack_type_name',$data)
                      ->pluck('rack_type_name');
                    }
           if(empty($rackname))
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
     public function getwarehouses($id)
    {
      $warehouses=DB::table('wms_entities')->where(array('entity_type_id'=>6001,'org_id'=>$id))->lists('entity_name','id');
      return $warehouses;
    }
}