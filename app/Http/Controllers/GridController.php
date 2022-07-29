<?php

/*
	Description : This controller is used for user crud operations.
	Author      : Venkat Reddy Muthuru
	Date        : May-23-2015
*/

class GridController extends BaseController {

	/* This function is used for displaying all the users
	 params : null
	 return : users data
	 */
	public function index()
	{
		return View::make('grid.index');
	}
	public function getUsers()
	{
		$custArr = array();
        $finalCustArr = array();
        $customer_details = DB::Table('cruduser')->get();
        foreach($customer_details as $value)
        {       	
        	if($value->status==1)
        		$status = 'Active';
        	else
        		$status = 'In-Active';
        	$custArr['id'] = $value->id;
        	$custArr['name'] = $value->name;
        	$custArr['email_id'] = $value->email_id;
        	$custArr['status'] = $status;        	
        	$custArr['phone'] = $value->phone;
        	$custArr['actions'] = '
        	<span style="padding-left:20px;" ><a data-href="/grid/edit/'. $value->id .'" data-toggle="modal" data-target="#basicvalCodeModal1"><img src="img/edit.png" /></a></span>
        	<span style="padding-left:20px;" ><a href="/grid/delete/'. $value->id .'"><img src="img/delete.png" /></a></span>';
        	
			$finalCustArr[] = $custArr;
        }

       return json_encode($finalCustArr);

	}
	/* This function is used for displaying the add new user form
	 params : null
	 return : create form
	 */
	public function create()
	{
		
		return View::make('grid.create');
	}
	/* This function is used for storing the user data
	 params : null
	 return : redirect to users list page
	 */
	public function store()
	{
		DB::table('cruduser')->insert([
			'name' => Input::get('name'),
			'email_id'=>Input::get('email_id'),
			'phone'=>Input::get('phone'),
			'status' => 1
		]);

		/*return Response::json([
				'status' => false,
				'message' => 'Not valid mobile number'
			]);
		*/
		return Response::json([
				'status' => true,
				'message'=>'Sucessfully added.'
			]);
	}
	/* This function is used for displaying the edit user data
	 params : user_id
	 return : edit form
	 */
	public function edit($id)
	{
		$cuser = DB::Table('cruduser')->find($id);
		return Response::json($cuser);
		//return View::make('grid.edit')->with('cuser', $cuser);
	}
	/* This function is used for updating the user data
	 params : user_id
	 return : redirects to users list page.
	 */
	public function update($id)
	{
		DB::table('cruduser')
            ->where('id', $id)
            ->update(array('name' => Input::get('name'), 
            				'email_id'=> Input::get('email_id'),
            				'phone'=>Input::get('phone'))
            				);
        
        return Response::json([
				'status' => true,
				'message'=>'Sucessfully updated.'
			]);
	}
	/* This function is used for deleting the user data
	 params : user_id
	 return : redirects to users list page.
	 */
	public function delete($id)
	{
		 
		DB::table('cruduser')->where('id', '=', $id)->delete();
		return Redirect::to('grid');
	}

}