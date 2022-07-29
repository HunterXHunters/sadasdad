<?php  namespace App\Repositories; 


use Token;
use User;
use DB;  //Include laravel db class
use Session;


Class MasterApiRepo{
    /**
    * 
    * @param type $api_key,$secret_key,$api_name
    * @return type Status,Message
    * @Description:This method will return true or false when $manf_id, $roleId, $methodName are given as inputs.
    */ 	
    public function apiLogin($data)
    {
        try
        {
            $apikey = isset($data['api_key']) ? $data['api_key'] : '';
            $secretkey = isset($data['secret_key']) ? $data['secret_key'] : '';
            $api_name = isset($data['api_name']) ? $data['api_name'] : '';

            $checkuser = DB::Table('api_session')
                    ->select('api_session.*')
                    ->where('api_session.api_key',$apikey)
                    ->where('api_session.secret_key',$secretkey)
                    ->first();
            if (!empty($checkuser))
            {
                $hasFeaturePermission = $this->checkApiPermission($checkuser->manufacturer_id, $checkuser->role_id, $api_name);
                if ($hasFeaturePermission)
                {
                    return ['Status' => 1, 'Message' => 'Successfull Login'];
                } else
                {
                    return ['Status' => 0, 'Message' => 'You do not have permission.'];
                }
            } else
            {
                return ['Status' => 0, 'Message' => 'Invalid key.'];
            }
        } catch (\ErrorException $ex)
        {
            Log::info('apiLogin API ERROR');
            Log::info($ex->getMessage());
            Log::info($ex->getTraceAsString());
            return 0;
        }
    }
    /**
    * 
    * @param type $manf_id,$roleId,$methodName
    * @return type boolean
    * @Description:This method will return true or false when $manf_id, $roleId, $methodName are given as inputs.
    */      
    public function checkApiPermission($manf_id, $roleId, $methodName)
    {
        try
        {
            $api_role_mfgasid = DB::table('api_role_mfgassign')
                    ->leftJoin('api_session', 'api_session.role_id', '=', 'api_role_mfgassign.api_role_mfgasid')
                    ->leftJoin('api_features', 'api_features.api_fid', '=', 'api_role_mfgassign.api_fid')
                    ->where(array('api_role_mfgassign.api_role_mfgasid' => $roleId, 'api_features.feature_name' => $methodName, 'api_session.manufacturer_id' => $manf_id, 'api_session.api_status' => 1))
                    ->pluck('api_role_mfgassign.api_role_mfgasid');
            if(!empty($api_role_mfgasid))
            {
                return true;
            }else{
                return false;
            }
        } catch (\ErrorException $ex) {
            \Log::info('checkApiPermission API ERROR');
            Log::info($ex->getMessage());
            Log::info($ex->getTraceAsString());
            return 0;
        }
    } 
    /**
    * 
    * @param type $data,$api_key,$secret_key
    * @return type Message,customer_id
    * @Description:This method will return the customer when api_key,secret_key are given as inputs.
    */      
    public function getManufacturerId($data)
    {
        try
        {
            $customer_id=DB::table('api_session')
                        ->where(array('api_key'=>$data['api_key'],'secret_key'=>$data['secret_key']))
                        ->pluck('manufacturer_id');
            if(!empty($customer_id))
            {
                return $customer_id;
            }else{
                return false;
            }
        } catch (Exception $e) {
            $message = $e->getMessage();
            return $message;
        }
    }
    public function getuuid()
    {
    
      $uuid=DB::select(DB::raw('select uuid() as uuid'));
      return $uuid;
    } 	
    
    public function getErpIntegration($manfId)
    {
        try
        {

            $erp = DB::table('erp_integration')->where(array('manufacturer_id' => $manfId))->first(array('id', 'web_service_url', 'token', 'company_code', 'web_service_username', 'web_service_password', 'sap_client'));
            /*echo "<pre>"; print_r($erp); die();*/
            return $erp;
            //dd($erp);
        } catch (\ErrorException $ex) {
            return $ex->getMessage();
            Log::info($manfId);
        }
    }
    
    public function getErpIntegrationAdditionalData($erpIntId, $channelId)
    {
        try
        {
            /*echo "<Hi>"; print_r($channelId); echo "erp"; print_r($erpIntId); die();*/
        if(empty($channelId) || $channelId == '')
        {
            $channelId = 0;
        }
        $erp_data = DB::table('erp_integration_data')
                ->join('erp_integration_party_data', 'erp_integration_party_data.integration_id', '=', 'erp_integration_data.integration_id')
                ->where(array('erp_integration_data.integration_id' => $erpIntId, 'erp_integration_party_data.channel_id' => $channelId))
                ->first();
        $last = DB::getQueryLog();
        /*echo "<pre>";print_r(end($last));die;*/
        return $erp_data;
        echo "<pre>"; print_r($erp_data); die();
        } catch (\ErrorException $ex) {
            return $ex->getMessage();
        }
    }
}