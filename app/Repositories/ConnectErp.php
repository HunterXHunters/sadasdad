<?php

namespace App\Repositories;
use DB;
use Location;
use Location1;
use Response;
use Log;
class ConnectErp
{
	public $_username;
	public $_password;
	public $_url;
	public $_domain;
	public $_method;
	public $_table_name;
	public $_sap_api_repo;
	public $_return_type;
	public $_token;
	public $_manufacturer_id;
	public $_company_code;
	public $_sap_client;
	public $_oauth;
	public $client_id;
	public $client_secret;
	
	public function __construct($_manufacturer_id=0)
	{
		if(!$_manufacturer_id){
			echo "Please Povide _manufacturer_id with object";
			// exit;		 
		} else {
			$this->_manufacturer_id=$_manufacturer_id;
			$erp = DB::table('erp_integration')->where('manufacturer_id', $_manufacturer_id)->get()->toArray();
			$this->_domain = $erp[0]->web_service_url;
			$this->_token = $erp[0]->token;
			$this->_company_code = $erp[0]->company_code;
			$this->_username = $erp[0]->web_service_username;
			$this->_password = $erp[0]->web_service_password;
			$this->_sap_client = $erp[0]->sap_client;
			if($this->_manufacturer_id==6){
				$this->_oauth=0;
				$this->client_id=$erp[0]->web_service_username;
				$this->client_secret=$erp[0]->web_service_password;
			}
		}     
	}

	public function request($method,$params,$body=0,$method_type='POST'){
		//body accepts array or body in string format
		//method type default is post
		//param can be used in get but if any query params avaiable u can pass it independ to method type
		//method will get or post it is mandatory fields
		if(is_array($params)){
			$_tparams=$params;$params='';
			foreach ($_tparams as $key => $value)
				$params.=trim($key).'='.trim($value);				
		}

		if($params!='')
			$params='?'.trim($params);
		if($method!='')
			$method='/'.trim($method);
		$this->_url=$this->_domain.$method.$params;

		$headers=[];
		if($this->_manufacturer_id==6){
			$headers[]="Content-Type:application/json";
			$headers[]="client_id:".$this->client_id;
			$headers[]="client_secret:".$this->client_secret;
		}

		if($body && $body !=''){
			if(is_array($body))
				$body=json_encode($body);				
		}
		
		$method_type=strtoupper($method_type);
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_HTTPHEADER,$headers);
		curl_setopt($curl, CURLOPT_CUSTOMREQUEST,$method_type);
		if($body && $body !='') 
		curl_setopt($curl, CURLOPT_POSTFIELDS,$body);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_URL, $this->_url);

		Log::info("============================");
		Log::info("============================");
		Log::info("============================");
		Log::info("ConnectErp start");
		Log::info("info");
		Log::info("headers");
		Log::info($headers);
		Log::info("method_type");
		Log::info($method_type);
		Log::info($body);
		Log::info("URL");
		Log::info($this->_url);
		Log::info("Result");
		$result = curl_exec($curl);
		Log::info("result");
		Log::info($result);
		Log::info("");
		Log::info("");
		Log::info("ConnectErp end");
		Log::info("============================");
		Log::info("============================");
		Log::info("============================");
		curl_close($curl);
		
//\\		$resultJ=json_decode($result);  
		$this->captureReqLog($this->_url,$body,$result);
		
		return $result;
	}

	public function captureReqLog($url,$req,$result){
			$resultJ=json_decode($result);
			$captureLog = curl_init();
			$logBody=[];
			$logBody['ip']=$_SERVER['REMOTE_ADDR'];
			$logBody['api_name']=$url;
			$logBody['request']=$req;
			$logBody['response']=$result;
			if(isset($resultJ->message))
			$logBody['message'] = $resultJ->message;
			if(isset($resultJ->status))
			$logBody['status'] = $resultJ->status;
			$logBody['location_id'] = '';
			$logBody['user_id'] = '';
			$logBody['manufacturer_id'] = '';
			$logBody['time'] = date("d-m-Y H:i:s");


		Log::info("log started");
			$api_log_sap=[];
			$api_log_sap['api_name']=$url;
			$api_log_sap['manufacturer_id']=$this->_manufacturer_id;
			if(isset($resultJ->status))
			$api_log_sap['status']=$resultJ->status;
			else 
				$api_log_sap['status']=0;
			if(isset($resultJ->message))
			$api_log_sap['message']=$resultJ->message;
			else $api_log_sap['message']='';
			
			$api_log_sap['input']=$req;
			$api_log_sap['res']=$result;
			$api_log_sap['created_on']=date("Y-m-d H:i:s");

			$insID= DB::connection('log')->table('api_log_sap')->insertGetId($api_log_sap);

			if($insID){
				Log::info("log captured");
			} else {
				Log::info("log not captured");
			}


			curl_setopt($captureLog, CURLOPT_CUSTOMREQUEST,'POST');
			curl_setopt($captureLog, CURLOPT_POSTFIELDS,$logBody);
			curl_setopt($captureLog, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($captureLog, CURLOPT_URL,'http://localhost/elastic/public/api/putLog?index=hul_sap&type=saplog');

			//curl_setopt($captureLog, CURLOPT_TIMEOUT_MS, 10);
			$captureLog_result = curl_exec($captureLog);
		    curl_close($captureLog);  



	}
}