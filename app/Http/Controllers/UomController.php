<?php

class UomController extends \BaseController {

	
	public function index()
	{
       
		// get all the entity types
        $uoms= Uom::all();

        // load the view and pass the entitytypes
        return View::make('uoms.index')
            ->with('uoms', $uoms);
	}

  public function create()
	{ 
		$uomgroup = UomGroup::all()->lists('description','id');
		$uomgroup = ['' => 'Select UOM Group'] + $uomgroup;
		
    //$dimension_uom_id= UomGroup::all()->first()->id;
    $dimension_uom = DB::table('wms_uom')->where('parent_uom_id',0)->lists('description', 'id');
    $dimension_uom = ['' => 'Select Dimension UOM'] + $dimension_uom;
    
		return View::make('uoms.create',compact("uomgroup","dimension_uom"));		
	}
  
  public function getdata()
  {
    //$mapEntities = DB::table('eseal')->get();

    $uoms = Uom::all();
        $getArr = array();
        $finalgetArr = array();
        foreach($uoms as $value)
        {
          $getArr['code'] = $value->code;
          $getArr['description'] = $value->description;
          $uom = UomGroup::find($value->uom_group_id);
          $getArr['uom_group_id'] = $uom->description;
          if($value->status ==1){
            $status= 'Active';
          }
          else{
            $status= 'In-Active';
          }
          $getArr['status'] = $status;
          $getArr['edit'] = '<a href="uoms/edit/'.$value->id.'"><img src="img/edit.png" /></a>'; 
          $getArr['delete'] = '<a onclick="deleteUom('.$value->id.')"><img src="img/delete.png" /></a>';
          $finalgetArr[] = $getArr;
        }

    return json_encode($finalgetArr);
  }


	public function store()
	{
		$validation = Validator::make(Input::all(), ['description'=>'required']);
		if($validation->fails())
		{
			return Redirect::back()->withInput()->withErrors($validation->messages());
		}
	    // store
       
       	$uom = new Uom;
        $uom->code = Input::get('code');
        $uom->description = Input::get('description');
        $uom->uom_group_id = Input::get('uom_group_id');
        $uom->status = Input::get('status');
        $uom->parent_uom_id = Input::get('parent_uom_id');
        $uom->save();

		
        Session::flash('message', 'Successfully created uom group!');
        return Redirect::to('uoms');
	}
 
  public function view($id){

  $uom = Uom::find($id);

        // show the view and pass the nerd to it
        return View::make('uoms.show')
            ->with('uom', $uom);

  }

  public function delete($id){

  $uom= Uom::find($id);

  $uom->delete();

        
        return Redirect::to('uoms');
}

  public function edit($id){

  $uom= Uom::find($id);

        // show the edit form and pass the nerd
               $uoms  = ['' => 'Select Uom Parent']+Uom::all()->lists('description','id');
       $uomgroup = UomGroup:: find($uom->uom_group_id)->lists('description', 'id');
        return View::make('uoms.edit')
            ->with('uom', $uom)->with('uomgroup',$uomgroup)->with('uoms',$uoms);

 }	

  public function update($id){

            $uom = Uom::find($id);
            $uom->code = Input::get('code');
            $uom->description = Input::get('description');
            $uom->uom_group_id = Input::get('uom_group_id'); 	
            $uom->parent_uom_id = Input::get('parent_uom_id');  			          
            $uom->status = Input::get('status');
            $uom->save();
             
        Session::flash('message', 'Updated Uom Successfully');
        return Redirect::to('uoms');


 }	
	

}


