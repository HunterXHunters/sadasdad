<?php

class EntityTypeController extends BaseController {

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{
		parent::Breadcrumbs(array('Home' => '/', 'Entity Types' => '#'));
        return View::make('entitytypes.index');
	}
	public function getdata()
	{
		//$mapEntities = DB::table('eseal')->get();

		$entity_types = EntityType::all();
        $getArr = array();
        $finalgetArr = array();
        foreach($entity_types as $value)
        {
        	if($value->status==1)
        		$status='Active';
        	else
        		$status='In-Active';
        	$getArr['id'] = $value->id;
        	$getArr['entity_type_name'] = $value->entity_type_name;
        	$getArr['status'] = $status;
        	$getArr['created_date'] = date_format($value->created_at,'Y-m-d');
        	$getArr['updated_date'] = date_format($value->updated_at,'Y-m-d');
			
        	$getArr['actions'] = '<span style="padding-left:5px;"><a href="entitytypes/edit/'.$value->id.'"><span class="badge bg-light-blue"><i class="fa fa-pencil"></i></span></span><span style="padding-left:50px;" ><a onclick="deleteEntityType('.$value->id.')" href=""><span class="badge bg-red"><i class="fa fa-trash-o"></i></span></a></span>		
'; 
			
			/*$getArr['delete'] = '<span style="padding-left:5px;"><a onclick = "deleteEntityType('.$value->id.')"><span class="badge bg-red"><i class="fa fa-trash-o"></i></span></a></span>';*/
			$finalgetArr[] = $getArr;
        }

		return json_encode($finalgetArr);
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create()
	{
		parent::Breadcrumbs(array('Home' => '/', 'Entity Types' => '#'));
		// load the create form (app/views/nerds/create.blade.php)
        return View::make('entitytypes.create');
	}


	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store()
	{
	   $validation = Validator::make(Input::all(), ['entity_type_name'=>'required']);
		if($validation->fails())
		{
			return Redirect::back()->withInput()->withErrors($validation->messages());
		}
	    // store
       	$entity_types = new EntityType;
        $entity_types->entity_type_name = Input::get('entity_type_name');
        $entity_types->status = Input::get('status');
        $entity_types->created_at = date('Y-m-d H:i:s');
        $entity_types->updated_at = date('Y-m-d H:i:s');
        $entity_types->save();

		/*DB::table('entity_types')->insert([

			'entity_type_name' => 'santosh',
			'status' => 1
		]);*/

        // redirect
        Session::flash('message', 'Successfully created entity type!');
        return Redirect::to('entitytypes');
        
	}


	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function show($id)
	{
		 // get the nerd
        $entity_type = EntityType::find($id);

        // show the view and pass the nerd to it
        return View::make('entitytypes.show')
            ->with('entity_type', $entity_type);
	}


	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function edit($id)
	{
		parent::Breadcrumbs(array('Home' => '/', 'Entity Types' => '#'));
		// get the nerd
         $entity_type = EntityType::find($id);

        // show the edit form and pass the nerd
        return View::make('entitytypes.edit')
            ->with('entity_type', $entity_type);
	}


	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update($id)
	{
		 // validate
        // read more on validation at http://laravel.com/docs/validation
        $rules = array(
            'entity_type_name'       => 'required',
            'status'      => 'required|status'
        );
        $validator = Validator::make(Input::all(), ['entity_type_name'=>'required']);
		if($validator->fails())
		{
			return Redirect::back()->withInput()->withErrors($validation->messages());
		} 
        else {
            // store
            $entity_type = EntityType::find($id);
            $entity_type->entity_type_name = Input::get('entity_type_name');
            $entity_type->status = Input::get('status');
            $entity_type->save();

            // redirect
            Session::flash('message', 'Successfully updated entity type!');
            return Redirect::to('entitytypes');
        }
	}


	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function delete($id)
	{
		 // delete
        $entity_type = EntityType::find($id);
        $entity_type->delete();

        // redirect
        //Session::flash('message', 'Successfully deleted the entity type!');
        return Redirect::to('entitytypes');
	}



}
