<?php

class CMController extends \BaseController {
	private $_mobile = '';
	private $_pid = '';
	private $_lat = '';
	private $_long = '';
	private $_serviceCenterTable = 'service_center';
	private $_productTable = 'products';

	public function getServiceCentersDetails(){
		$this->_mobile = trim(Input::get('mobile'));
		$this->_pid = trim(Input::get('pid'));
		$this->_lat = trim(Input::get('lat'));
		$this->_long = trim(Input::get('long'));
		$msg = '';
		$status = 0;
		$data = Array();
		try{
			if(empty($this->_mobile) || empty($this->_pid) || empty($this->_lat) || empty($this->_long)){
				throw new Exception('One of the parameter is missing');
			}else{
				$productObj = new Products\Products();
				$mfgId = $productObj->getManufacturerIdForProductId($this->_pid);
				if($mfgId){
					$serviceCenterObj = new ServiceCenter\ServiceCenter();
					$data = $serviceCenterObj->getCentersForMfgId($mfgId);

					$msg = 'Data found succesfully';
					$status = 1;
					//Log::info(print_r($centerData,true));
				}else{
					throw new Exception('Product not associated with customer');	
				}
			}
		}catch(Exception $e){
			$msg = $e->getMessage();
			Log::error($e->getMessage());
		}
		return Response::json(['Status' => $status, 'Message' => $msg, 'Data' => $data]);
	}
	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{
		//
	}


	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create()
	{
		//
	}


	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store()
	{
		//
	}


	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function show($id)
	{
		//
	}


	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function edit($id)
	{
		//
	}


	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update($id)
	{
		//
	}


	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function destroy($id)
	{
		//
	}


}
