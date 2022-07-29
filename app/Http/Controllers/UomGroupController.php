<?php

class UomGroupController extends \BaseController {

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{

		// get all the entity types
        $uomgroups = UomGroup::all();

        // load the view and pass the entitytypes
        return View::make('uomgroups.index')
            ->with('uomgroups', $uomgroups);
	}


	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create()
	{
		
		return View::make('uomgroups.create');		
	}

public function getdata()
  {
    //$mapEntities = DB::table('eseal')->get();

    $uomgroup = UomGroup::all();
        $getArr = array();
        $finalgetArr = array();
        foreach($uomgroup as $value)
        {
          
           
          
          $getArr['description'] = $value->description;
          if($value->status == 1){
            $status = 'Active';
          }
          else{
            $status = 'In-Active';
          }
          $getArr['status'] = $status;
          
          
          $getArr['edit'] = '<a href="uomgroup/edit/'.$value->id.'"><img src="img/edit.png" /></a>'; 
          $getArr['delete'] = '<a onclick="deleteUomgroup('.$value->id.')" href=""><img src="img/delete.png" /></a>';
          $finalgetArr[] = $getArr;
        }

    return json_encode($finalgetArr);
  }
	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store()
	{
		$validation = Validator::make(Input::all(), ['description'=>'required']);
		if($validation->fails())
		{
			return Redirect::back()->withInput()->withErrors($validation->messages());
		}
	    // store
       	$uomgroups = new UomGroup;
        $uomgroups->description = Input::get('description');
        $uomgroups->status = Input::get('status');
        $uomgroups->save();

		/*DB::table('entity_types')->insert([

			'entity_type_name' => 'santosh',
			'status' => 1
		]);*/

        // redirect
        Session::flash('message', 'Successfully created uom group!');
        return Redirect::to('uomgroup');
	}

public function view($id){


$uomgroup = UomGroup::find($id);

        // show the view and pass the nerd to it
        return View::make('uomgroups.show')
            ->with('uomgroup', $uomgroup);

}
public function delete($id){

$uomgroup = UomGroup::find($id);

$uomgroup->delete();

        // redirect
        Session::flash('message', 'Successfully deleted the Uom Group');
        return Redirect::to('uomgroup');
}	
 public function edit($id){

  $uomgroup = UomGroup::find($id);

        // show the edit form and pass the nerd
        return View::make('uomgroups.edit')
            ->with('uomgroup', $uomgroup);



 }	
public function update($id){

            $uomgroup = UomGroup::find($id);
            $uomgroup->description = Input::get('description');
            $uomgroup->status = Input::get('status');
            $uomgroup->save();

        Session::flash('message', 'Updated Uom Group Successfully');
        return Redirect::to('uomgroup');


 }	
	

}

