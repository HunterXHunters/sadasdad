<?php

namespace Products;
use Central\Repositories\RoleRepo;
use Central\Repositories\SapApiRepo;
use \DB;

class ProductData
{
    private $_sap_api_repo;
    
    public function __construct()
    {
        $this->roleRepo = new RoleRepo;
    }
    
    public function erpDataImport($data)
    {
        try
        {
            $manufacturerId = isset($data['manufacturerID']) ? $this->roleRepo->decodeData($data['manufacturerID']) : 0;
            if($manufacturerId)
            {
                $companyData = DB::table('erp_integration')->where('manufacturer_id',$manufacturerId)->first(array('company_code', 'default_start_date'));
                if(empty($companyData))
                {
                    return \Response::json([
                        'status' => false,
                        'message' => 'Manufacturer has no erp data, please enter in company details page under Erp Configuration tab.'
                    ]);
                }
                $comp_code = property_exists($companyData, 'company_code') ? $companyData->company_code : '';
                $default_start_date = property_exists($companyData, 'default_start_date') ? $companyData->default_start_date : '';
                if($default_start_date == '' || $default_start_date == null)
                {
                    $default_start_date = 'datetime'."'".date('Y-m-d')."T00:00:00'";
                }else{
                    $default_start_date = 'datetime'."'".$default_start_date."T00:00:00'";
                }
                $to_date = 'datetime'."'".date('Y-m-d')."T00:00:00'";
                if($comp_code == '')
                {
                    return \Response::json([
                        'status' => false,
                        'message' => 'Manufacturer has no erp data, please enter in company details page under Erp Configuration tab.'
                    ]);
                }
                $this->_sap_api_repo = new SapApiRepo;
                $method = 'Z016_ESEAL_GET_PRODUCT_SKU_SRV';
                $method_name = 'ET_MAT_DISPLAY';
                $data =['FROM_DATE'=>$default_start_date,'TO_DATE'=>$to_date];
                //return $data;
                $response = $this->_sap_api_repo->callSapApi($method,$method_name,$data,null,$manufacturerId);
                echo "<pre>response => ";print_R($response);die;
                return \Response::json([
                    'status' => true,
                    'message' => $response
                ]);
            }else{
                return \Response::json([
                    'status' => false,
                    'message' => 'No manufacturer given.'
                ]);
            }
        } catch (\ErrorException $ex) {
            return \Response::json([
                'status' => false,
                'message' => $ex->getTraceAsString()
            ]);
        }
    }
}