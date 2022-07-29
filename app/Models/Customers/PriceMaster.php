<?php

namespace App\Models\Customers;
use App\Repositories\RoleRepo;
use App\Repositories\CustomerRepo;
use DB;
use Session;

class PriceMaster extends \Eloquent
{

    protected $table = 'eseal_price_master'; // table name
    protected $primaryKey = 'id';
    public $timestamps = false;

    /**
     * provide specific rows based on the product look up id
     * @param type $productLookupId
     */
    public function getPriceMasters($customerTypeLookupId, $componentTypeLookupId)
    {
        $priceMasterData = $this->whereRaw('customer_type_lookup_id = ? and component_type_lookup_id = ?', array($customerTypeLookupId, $componentTypeLookupId))->get();
        $priceMasterArray = array();
        foreach ($priceMasterData as $priceMaster)
        {
            $productPlanId = $priceMaster->attributes['id'];
            $userData = DB::table('module_users')
                    ->join('modules', 'modules.module_id', '=', 'module_users.module_id')
                    ->select('module_users.module_id', 'modules.name', 'module_users.users')
                    ->where('module_users.product_plan_id', '=', $productPlanId)
                    ->get();
            $priceMaster->attributes['userData'] = $userData;
            $priceMasterArray[] = $priceMaster->attributes;
        }
        return $priceMasterArray;
    }
    
    public function getMastersPriceData($customerTypeLookupId, $productLookupId)
    {
        $priceMasterData = $this->whereRaw('customer_type_lookup_id = ? and component_type_lookup_id = ?', array($customerTypeLookupId, $componentTypeLookupId))->get();
        $priceMasterArray = array();
        foreach ($priceMasterData as $priceMaster)
        {
            $productPlanId = $priceMaster->attributes['id'];
            $userData = DB::table('module_users')
                    ->join('modules', 'modules.module_id', '=', 'module_users.module_id')
                    ->select('module_users.module_id', 'modules.name', 'module_users.users')
                    ->where('module_users.product_plan_id', '=', $productPlanId)
                    ->get();
            $priceMaster->attributes['userData'] = $userData;
            $priceMasterArray[] = $priceMaster->attributes;
        }
        return $priceMasterArray;
    }
    
    public function getPriceData($id)
    {
        $priceData = $this->where('id', '=', $id)->first();
        return $priceData->attributes;
    }
}
