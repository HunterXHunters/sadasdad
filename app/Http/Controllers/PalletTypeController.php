<?php

class PalletTypeController extends \BaseController {

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */

//public 

	public function index()
	{

		// get all the entity types
        $pallettypes = PalletType::all();

        // load the view and pass the entitytypes
        return View::make('pallettypes.index')
            ->with('pallettypes', $pallettypes);
	}


	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create()
	{
		
		return View::make('pallettypes.create');		
	}
public function getdata()
	{
		//$mapEntities = DB::table('eseal')->get();

		$pallet_types = PalletType::all();
        $getArr = array();
        $finalgetArr = array();
        foreach($pallet_types as $value)
        {
        	if($value->status==1)
        		$status='Active';
        	else
        		$status='In-Active';
        	$getArr['id'] = $value->id;
        	$getArr['pallet_name'] = $value->pallet_name;
        	$getArr['status'] = $status;
        	
        	$getArr['edit'] = '<a href="pallettype/edit/'.$value->id.'"><img src="img/edit.png" /></a>'; 
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
		
		$validation = Validator::make(Input::all(), ['pallet_name'=>'required','status'=>'required']);
		if($validation->fails())
		{
			return Redirect::back()->withInput()->withErrors($validation->messages());
		}
	    // store
       	$pallettype = new PalletType;
        $pallettype->pallet_name = Input::get('pallet_name');
        $pallettype->status = Input::get('status');
        $pallettype->save();

		/*DB::table('entity_types')->insert([

			'entity_type_name' => 'santosh',
			'status' => 1
		]);*/

        // redirect
        Session::flash('message', 'Successfully created Pallet Type!');
        return Redirect::to('pallettype');
	}

public function view($id){


$pallettype = PalletType::find($id);

        // show the view and pass the nerd to it
        return View::make('pallettypes.show')
            ->with('pallettype', $pallettype);

}

 public function edit($id){

  $pallettype = PalletType::find($id);

        // show the edit form and pass the nerd
        return View::make('pallettypes.edit')
            ->with('pallettype', $pallettype);



 }	
public function update($id){

            $pallettype = PalletType::find($id);
            $pallettype->pallet_name = Input::get('pallet_name');
            $pallettype->status = Input::get('status');
            $pallettype->save();

        Session::flash('message', 'Updated Pallet Type Successfully');
        return Redirect::to('pallettype');


 }	
	

}

