<?php

class verifyMobileController extends \BaseController {
	private $_mobile;
    private $_OTPCode;
    private $_message = 'Your verification code is : ';
    private $_smsURL = 'http://api.mvaayoo.com/mvaayooapi/MessageCompose?user=vinil@esealinc.com:eseal@123&senderID=ESEALS&msgtxt={SMS_CONTENT}&state=4&receipientno={MOBILE}';

	/**
	 * To send an OTP code to given mobile number.
	 *
	 * @return OTPCode
	 */
	public function verify()
	{
		$this->_mobile = Input::get('mobile');
		if( !is_numeric($this->_mobile) || strlen($this->_mobile)!=10 ){
			return json_encode( Array('Status'=>0, 'Message' => 'Invalid input for mobile') );
		}else{
			$this->_OTPCode = mt_rand(1000000, 9999999);
			$message = rawurlencode($this->_message . $this->_OTPCode);
			$this->sendOTPCode($message);
			return json_encode( Array('Status'=>1, 'OTPCode' => $this->_OTPCode) );
		}
	}

	public function sendOTPCode($message){
	    $this->_smsURL = str_replace('{SMS_CONTENT}', $message, $this->_smsURL);
		$this->_smsURL = str_replace('{MOBILE}', $this->_mobile, $this->_smsURL);
		$ch = curl_init();
	    Log::info(print_r($ch,true));
	    curl_setopt($ch, CURLOPT_URL,$this->_smsURL);
	    curl_setopt($ch, CURLOPT_HEADER, false);
	    curl_setopt($ch,  CURLOPT_CONNECTTIMEOUT, 30); 	
	    curl_setopt($ch,  CURLOPT_RETURNTRANSFER, true); 	
	    $response = curl_exec($ch);
	    Log::info(print_r($response,true));            
	    curl_close($ch);
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