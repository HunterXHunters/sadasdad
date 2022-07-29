<?php
namespace App\Models\Customers;
use App\Repositories\RoleRepo;
use App\Repositories\CustomerRepo;
use DB;
use Session;

use Illuminate\Database\Eloquent\Model;
class Onboarding extends Model
{
    private $customerRepo;
    private $roleRepo;
    
    public function __construct()
    {
        $this->customerRepo = new CustomerRepo;
        $this->roleRepo = new RoleRepo;
    }
    public function getparentCompanyList($customerId)
    {
        try
        {
            $parentCompanyArray = array();            
            if($customerId)
            {
                $parent_company_id = DB::table('eseal_customer')->where('customer_id', $customerId)->value('parent_company_id');
                if($parent_company_id == 0)
                {
                    $parentCompanyArray[0] = 'None';
                    //$parentCompanyArray[-1] = 'Parent Company';
                }else if($parent_company_id == -1)
                {
                    $parentCompanyArray[0] = 'None';
                    $parentCompanyArray[-1] = 'Parent Company';
                    $parentCompanyDetails = DB::table('eseal_customer')->where('parent_company_id', -1)->get(['customer_id', 'brand_name'])->toArray();
                }else{
                    $parentCompanyDetails = DB::table('eseal_customer')->where(array('parent_company_id' => $parent_company_id))->get(['customer_id', 'brand_name'])->toArray();
                }
            }else{
                $parentCompanyArray[0] = 'None';
                $parentCompanyArray[-1] = 'Parent Company';
                $parentCompanyDetails = DB::table('eseal_customer')->where('parent_company_id', -1)->get(['customer_id', 'brand_name'])->toArray();
            }            
            if(!empty($parentCompanyDetails))
            {
                foreach ($parentCompanyDetails as $companyData)
                {
                    $parentCompanyArray[$companyData->customer_id] = $companyData->brand_name;
                }
            }
            return $parentCompanyArray;
        } catch (\ErrorException $ex) {
            die($ex);
        }
    }
    public function getCustomerLookupIds()
    {
        $getCategoryId = $this->getCategoryId('Customer Types');
        $customerLookupData = DB::table('master_lookup')
                ->where('category_id', $getCategoryId)
                ->where('is_active', 1)
                ->orderBy('sort_order', 'asc')
                ->get(array('value', 'name'));
        $customerLookupArray = array();
        foreach ($customerLookupData as $lookupData)
        {
            $customerLookupArray[$lookupData->value] = $lookupData->name;
        }
        return $customerLookupArray;
    }
    
    public function getProductLookupIds()
    {
        $getCategoryId = $this->getCategoryId('Eseal Products');
        $productLookupData = DB::table('master_lookup')->where('category_id', '=', $getCategoryId)->get(array('value', 'name'));
        $productLookupArray = array();
        foreach ($productLookupData as $lookupData)
        {
            $productLookupArray[$lookupData->value] = $lookupData->name;
        }
        return $productLookupArray;
    }
    
    public function getComponentLookupIds()
    {
        $getCategoryId = $this->getCategoryId('Component Types');
        $componentLookupData = DB::table('master_lookup')
                ->join('eseal_price_master', 'eseal_price_master.component_type_lookup_id', '=', 'master_lookup.value')
                ->where('master_lookup.category_id', $getCategoryId)
                ->where('eseal_price_master.is_active', 1)
                ->get(array('master_lookup.value', 'eseal_price_master.name', 'eseal_price_master.price', 'eseal_price_master.id'));        
        return $componentLookupData;
    }
        
    public function getPriceData()
    {
        $customerTypeLookupId = 1001;
        $componentTypeLookupId = 2005;
        $priceMaster = new PriceMaster();
        return $priceMaster->getPriceMasters($customerTypeLookupId, $componentTypeLookupId);
    }
        
    public function getTaxData()
    {
        $data = array();
        $responseData = DB::table('lookup_categories')
                ->join('master_lookup', 'master_lookup.category_id', '=', 'lookup_categories.id')
                ->where('lookup_categories.name', 'Tax Classes')
                ->get(array('master_lookup.name', 'master_lookup.value'));
        if(!empty($responseData))
        {
            foreach($responseData as $fieldData)
            {
                $data[$fieldData->value] = $fieldData->name;
            }
        }
        return $data;
    }
    
    public function getCurrencies()
    {
        $currencyData = DB::table('currency')->get(array('currency_id', 'code'));
        $currencyArray = array();
        foreach ($currencyData as $currency)
        {
            $currencyArray[$currency->currency_id] = $currency->code;
        }
        return $currencyArray;
    }
    
    public function getCategoryId($lookupCategoryName)
    {
        try
        {
            $categoryId = 0;
            $result = DB::table('lookup_categories')->where('name', $lookupCategoryName)->first(array('id'));
            if(!empty($result))
            {
                $categoryId = $result->id;
            }
            return $categoryId;
        } catch (\ErrorException $ex) {
            die($ex);
        }
    }
}