<?php 
namespace  App\Repositories; 
// use Illuminate\Database\Eloquent\Model;

use DB;

class CustomerRepo 
{
	public function getCustomerOrders($id,$cust_id,$ima_id)
	{
		if($id==1){
			$place=DB::select('select value from master_lookup where name="placed"');
			$placed=$place[0]->value;
		     if(!empty($cust_id)){
             $result=DB::table('eseal_orders')
                 ->select('eseal_customer.*','eseal_orders.*','master_lookup.name')
                 ->Leftjoin('eseal_customer','eseal_orders.customer_id','=','eseal_customer.customer_id')
                 ->Leftjoin('master_lookup','master_lookup.value','=','eseal_orders.order_status_id')
                 ->where('eseal_orders.order_status_id','=',$placed)
                 ->where('eseal_customer.customer_id','=',$cust_id)
                 ->get();    
		    }
            else{
             $result=DB::table('eseal_orders')
                 ->select('eseal_customer.*','eseal_orders.*','master_lookup.name')
                 ->Leftjoin('eseal_customer','eseal_orders.customer_id','=','eseal_customer.customer_id')
                 ->Leftjoin('master_lookup','master_lookup.value','=','eseal_orders.order_status_id')
                 ->where('eseal_orders.order_status_id','=',$placed)
                 ->get(); 
            }
        }
		if($id==2){
			$approve=DB::select('select value from master_lookup where name="Approve"');
			$approved=$approve[0]->value;
			if(!empty($cust_id)){
            $result=DB::table('eseal_orders')
		         ->select('eseal_customer.*','eseal_orders.*','master_lookup.name')
                 ->Leftjoin('eseal_customer','eseal_orders.customer_id','=','eseal_customer.customer_id')
                 ->Leftjoin('master_lookup','master_lookup.value','=','eseal_orders.order_status_id')
                 ->where('eseal_orders.order_status_id','=',$approved)
                 ->where('eseal_customer.customer_id','=',$cust_id)
                 ->get();
		      }
              else{
                 $result=DB::table('eseal_orders')
                 ->select('eseal_customer.*','eseal_orders.*','master_lookup.name')
                 ->Leftjoin('eseal_customer','eseal_orders.customer_id','=','eseal_customer.customer_id')
                 ->Leftjoin('master_lookup','master_lookup.value','=','eseal_orders.order_status_id')
                 ->where('eseal_orders.order_status_id','=',$approved)
                 ->get();
              }
        }
		if($id==3){
			$Deliver=DB::select('select value from master_lookup where name="Delivered"');
			$Delivered=$Deliver[0]->value;
			if(!empty($cust_id)){
            $result=DB::table('eseal_orders')
		         ->select('eseal_customer.*','eseal_orders.*','master_lookup.name')
                 ->Leftjoin('eseal_customer','eseal_orders.customer_id','=','eseal_customer.customer_id')
                 ->Leftjoin('master_lookup','master_lookup.value','=','eseal_orders.order_status_id')
                 ->where('eseal_orders.order_status_id','=',$Delivered)
                 ->where('eseal_customer.customer_id','=',$cust_id)
                 ->get();
		       }
               else{
                $result=DB::table('eseal_orders')
                 ->select('eseal_customer.*','eseal_orders.*','master_lookup.name')
                 ->Leftjoin('eseal_customer','eseal_orders.customer_id','=','eseal_customer.customer_id')
                 ->Leftjoin('master_lookup','master_lookup.value','=','eseal_orders.order_status_id')
                 ->where('eseal_orders.order_status_id','=',$Delivered)
                 ->get();
               }
        }
		if($id==0){
		if(empty($cust_id)){
        // return 'hi';
        $result=DB::table('eseal_orders')
		         ->select('eseal_customer.*','eseal_orders.*','master_lookup.name')
                 ->Leftjoin('master_lookup','master_lookup.value','=','eseal_orders.order_status_id')
                 ->Leftjoin('eseal_customer','eseal_orders.customer_id','=','eseal_customer.customer_id')
                 ->get();
	     }
         else{
             $result=DB::table('eseal_orders')
                 ->select('eseal_customer.*','eseal_orders.*','master_lookup.name')
                 ->Leftjoin('master_lookup','master_lookup.value','=','eseal_orders.order_status_id')
                 ->Leftjoin('eseal_customer','eseal_orders.customer_id','=','eseal_customer.customer_id')
                 ->where('eseal_customer.customer_id','=',$cust_id)
                 ->where('eseal_orders.ima_id','=',$ima_id)
                 ->get();
         }
         }
	     return $result;
	}
	/*
		This function is used for getting the customer details based on the user id
		params : user_id
		return : customer related details.
	*/
	public function getCustomerDetails($cust_id,$user_id)
	{
		
            if(!empty($cust_id))
            {	
                //return 'hi';
                $result = DB::table('eseal_customer')
                ->select('eseal_customer.*')
                ->join('users','users.customer_id','=','eseal_customer.customer_id')
                ->where(array('users.user_id'=>$user_id,'users.is_active'=>1))
                ->get();
             }
             else{

                $result=DB::table('eseal_customer')
               ->where('status',1)         
               ->get();
             }
            return $result;
	}
        
    public function getAllCustomers($cust_id = '')
    {
        $result = DB::table('eseal_customer');
        if (!empty($cust_id))
        {
            $result->where('status', 1);
            $checkParent = $this->checkParent($cust_id);
            if($checkParent)
            {
                $childCompany = DB::table('eseal_customer')->where('parent_company_id', $checkParent);
                $result->where('customer_id', $cust_id)->union($childCompany);
            }else{
                $result->where('customer_id', $cust_id);
            }
        }
        $custResult = $result->orderBy('brand_name', 'ASC')->get();
        return $custResult;
    }
    
    public function checkParent($custId)
    {
        try
        {
            $parentCompany = DB::table('eseal_customer')->where('eseal_customer.customer_id', $custId)->first(array('customer_id' ,'parent_company_id'));
            if(!empty($parentCompany) && $parentCompany->parent_company_id == -1)
            {
                return $parentCompany->customer_id;
            }else{
                return 0;
            }            
        } catch (\ErrorException $ex) {
            die($ex);
        }
    }

    public function getChildDetails($currentUserId)
    {
        try
        {
          //   $currentUserId = \Session::get('userId');
             //return $currentUserId;
        $manufacturerDetails = DB::table('users')->where('user_id', $currentUserId)->pluck('customer_id');
        //return $manufacturerDetails;
            $parent=$this->checkParent($manufacturerDetails);
            //return $parent;
            
            $childCompanies=DB::table('eseal_customer')
                          ->select('eseal_customer.brand_name','eseal_customer.customer_id')
                          ->where('eseal_customer.parent_company_id','=',$parent)
                          ->get();
            return $childCompanies;
            
        }
        catch (\ErrorException $ex) {
            die($ex);
        }
    }

    public function getAllCustomerDetails()
        {
            $result = DB::table('eseal_customer')
                ->where('customer_type_id', 1001)
                ->where('approved', 1)
                ->where('status', 1)
                ->select('customer_id', 'brand_name')
                ->get();
        return $result;
        }
        
        public function getUserDetails($user_id)
        {
        	$result=DB::table('users')->where('user_id','=',$user_id)->get();
        	return $result;
        }
        
        // get location details
        public function prepareLocationData($manufacturerId)
        {
            if($manufacturerId)
            {
                $locs = DB::Table('location_types')
                    //->join('eseal_customer', 'eseal_customer.customer_id', '=', 'location_types.manufacturer_id')
                    ->where('manufacturer_id', $manufacturerId)
                    ->select('location_types.location_type_name', 'location_types.location_type_id', 'location_types.manufacturer_id')
                    ->get();

            $locas = DB::Table('locations')
                    ->where('manufacturer_id', $manufacturerId)
                    ->select('locations.location_id', 'locations.location_name', 'locations.manufacturer_id', 'locations.parent_location_id', 'locations.location_type_id', 'locations.location_email', 'locations.location_address', 'locations.location_details', 'locations.state', 'locations.region', 'locations.longitude', 'locations.latitude', 'locations.erp_code')
                    ->get();

            $manu = DB::Table('eseal_customer')
                    ->where('customer_id', $manufacturerId)
                    ->select('customer_id', 'brand_name')
                    ->get();
            }else{
                $locs = DB::Table('location_types')
                        ->select('location_types.location_type_name', 'location_types.location_type_id', 'location_types.manufacturer_id')
                        ->get();

                $locas = DB::Table('locations')
                        ->select('locations.location_id', 'locations.location_name', 'locations.manufacturer_id', 'locations.parent_location_id', 'locations.location_type_id', 'locations.location_email', 'locations.location_address', 'locations.location_details', 'locations.state', 'locations.region', 'locations.longitude', 'locations.latitude', 'locations.erp_code')
                        ->get();

                $manu = DB::Table('eseal_customer')
                        ->select('customer_id', 'brand_name')
                        ->get();
        }
            
        return array('locs' => $locs, 'locas' => $locas,'manu' => $manu);
        }
        
        public function getCustomerLogo($customerId) {
            return DB::table('eseal_customer')->select('logo')
                    ->where(array('customer_id'=>$customerId,'status'=>1))->get();
        }
        
    public function getManufacturerId()
    {
        $currentUserId = \Session::get('userId');
        $manufacturerDetails = DB::table('users')->where('user_id', $currentUserId)->first(array('customer_id'));
        $manufacturerId = -1;
        if (!empty($manufacturerDetails))
        {
            $manufacturerId = $manufacturerDetails->customer_id;
        }
        return $manufacturerId;
    }
    public function getGdsOrders($cust_id)
    {
      $order_status=DB::select('select value from master_lookup where name="GDS orders"');
     // return $order_status;
      $order_status=$order_status[0]->value;
      
      if(!empty($cust_id)){
             $result=DB::table('eseal_orders')
                 ->select('eseal_customer.*','eseal_orders.*','master_lookup.name')
                 ->Leftjoin('eseal_customer','eseal_orders.customer_id','=','eseal_customer.customer_id')
                 ->Leftjoin('master_lookup','master_lookup.value','=','eseal_orders.order_status_id')
                 ->where('eseal_orders.order_type','=',$order_status)
                 ->where('eseal_customer.customer_id','=',$cust_id)
                 ->orderBy('eseal_orders.order_id', 'desc')
                 ->get();    
            }
            else{
             $result=DB::table('eseal_orders')
                 ->select('eseal_customer.*','eseal_orders.*','master_lookup.name')
                 ->Leftjoin('eseal_customer','eseal_orders.customer_id','=','eseal_customer.customer_id')
                 ->Leftjoin('master_lookup','master_lookup.value','=','eseal_orders.order_status_id')
                 ->where('eseal_orders.order_type','=',$order_status)
                 ->orderBy('eseal_orders.order_id', 'desc')
                 ->get(); 
            }   
       return $result;
    }
    
    public function softDelete($manufacturerId, $tableName)
    {
        try
        {
            if($tableName && $manufacturerId)
            {
                $updateFields['is_deleted'] = 1;
                if($tableName == 'customer_products_plans')
                {
                    DB::table($tableName)->where('customer_id', $manufacturerId)->update($updateFields);
                }else{
                    DB::table($tableName)->where('manufacturer_id', $manufacturerId)->update($updateFields);
                }
            }
            return 1;
        } catch (\ErrorException $ex) {
            return $ex->getMessage();
        }
    }
    
    public function hardDelete($manufacturerId, $tableName)
    {
        try
        {
            if($tableName && $manufacturerId)
            {
                if($tableName == 'customer_products_plans')
                {
                    DB::table($tableName)->where('customer_id', $manufacturerId)->delete();
                }else{
                    DB::table($tableName)->where('manufacturer_id', $manufacturerId)->delete();
                }                
            }else{
                return 0;
            }
            return 1;
        } catch (\ErrorException $ex) {
            return $ex->getMessage();
        }
    }
    
    public function getCountryData()
    {
        $countryData = DB::table('countries')->get(array('country_id', 'name'));
        $countryArray = array();        
        foreach ($countryData as $country)
        {
            $countryArray[$country->country_id] = $country->name;
        }
        return $countryArray;
    }
    
    public function getZones($countryId)
    {
        try
        {
            $zones = DB::table('zone')
                ->where('country_id', '=', $countryId)
                ->where('status', '=', 1)
                ->get(array('zone_id', 'name'));      
            $zonesArray = array();
            $zonesArray[0] = 'Please select..';
            foreach ($zones as $zone)
            {
                $zonesArray[$zone->zone_id] = $zone->name;
            }
            return $zonesArray;
        } catch (\ErrorException $ex)
        {
            echo $ex->getMessage();
        }
    }
    
    public function getZonesByName($countryName)
    {
        try
        {
            $zones = DB::table('zone')
                ->join('countries', 'countries.country_id', '=', 'zone.country_id')
                ->where('countries.name', '=', $countryName)
                ->where('zone.status', 1)
                ->get(array('zone.zone_id', 'zone.name'));      
            $zonesArray = array();            
            foreach ($zones as $zone)
            {
                $zonesArray[$zone->zone_id] = $zone->name;
            }
            return $zonesArray;
        } catch (\ErrorException $ex)
        {
            echo $ex->getMessage();
        }
    }

}

?>