<?php  namespace App\Repositories; 

use DB;

class OrderRepo
{
	public function addOrders()
	{
		//return DB::table('eseal_customer')->get();
	}
	public function getCustomerCartDetails($id,$customer_id,$ima_id)
	{
		 
     $result_customer = DB::table('customer_cart')
                ->select('customer_cart.*','customer_products_plans.*')
                ->join('customer_products_plans','customer_products_plans.customer_product_plan_id','=','customer_cart.product_id');
                
     $result = DB::table('customer_cart')
                ->select('customer_cart.*','eseal_price_master.*')
                ->join('eseal_price_master','eseal_price_master.id','=','customer_cart.product_id');

    if($id==1){
    if(empty($customer_id)){
    $result = $result->where(array('customer_cart.customer_id'=>$customer_id,'customer_cart.ima_id'=>$ima_id,'eseal_price_master.name'=>'IoT'))
                ->get();
        return $result;
	 }
  
   else{
    $result = $result_customer->where(array('customer_cart.customer_id'=>$customer_id,'customer_cart.ima_id'=>$ima_id,'customer_products_plans.name'=>'IoT'))
              ->get();
    return $result;    
   }
  }
  if($id==9){
   if(empty($customer_id)){
    $result = $result->where(array('customer_cart.customer_id'=>$customer_id,'customer_cart.ima_id'=>$ima_id))
                ->whereNotIn('eseal_price_master.name',array('IoT'))
                ->get();
        return $result;
   }
  
   else{
    $result = $result_customer->where(array('customer_cart.customer_id'=>$customer_id,'customer_cart.ima_id'=>$ima_id))
                ->whereNotIn('customer_products_plans.name',array('IoT'))
                ->get();
        return $result;
   } 
  }
  }
	public function getComponentType($id,$customer_id,$status,$temp_cust_id)
	{
	   $comp_id=DB::select('select value from master_lookup where name="AIDC"');
     $comp_id=$comp_id[0]->value;
     $aidc_components=DB::table('eseal_price_master')
                          ->leftJoin('customer_products_plans','customer_products_plans.product_plan_id','=','eseal_price_master.id') 
                          ->where('eseal_price_master.component_type_lookup_id',$comp_id);
                          
	   $iot_components=DB::table('customer_products_plans')
                    ->where('customer_id',$customer_id);

     if(!empty($customer_id)){
	   if(!empty($id)){
     if($id==9){
         $component_types=$aidc_components->where('customer_products_plans.customer_id',$customer_id)
                          ->select('customer_products_plans.*')->get();
         }
         else 
         $component_types = $iot_components->where('product_plan_id',$id)->select('*')->get();
        }
        else{
        if($status==1){
          $component_types = $iot_components->where('product_plan_id',$status)->select('*')->get();
         }
         if($status==0){
          $component_types=$aidc_components->where('customer_products_plans.customer_id',$customer_id)
                          ->select('customer_products_plans.*')->get();
         }
         }
        }
        else{
          if(!empty($id)){
         if($id==9){
         $component_types=$aidc_components->where('customer_products_plans.customer_id',$temp_cust_id)
                          ->select('eseal_price_master.*')->get();
         }
      else{
        $component_types = DB::select('select DISTINCT(master_lookup.name),eseal_price_master.name,eseal_price_master.price,eseal_price_master.id,eseal_price_master.image_url,eseal_price_master.description from master_lookup left join lookup_categories on lookup_categories.id= master_lookup.category_id 
    		inner join eseal_price_master on master_lookup.value=eseal_price_master.component_type_lookup_id
    		where master_lookup.category_id=2 and eseal_price_master.id='.$id);	
         }
          }
         else{
          if($status==1)
          $component_types = DB::select('select * from customer_products_plans where customer_id="'.$temp_cust_id.'" and product_plan_id='.$status);
          if($status==0){
          $component_types=$aidc_components->where('customer_products_plans.customer_id',$temp_cust_id)
                          ->select('eseal_price_master.*')->get();
          }
         }
        }
          
        
        return $component_types;
	}

	public function getProductCost($pid)
	{
    
    $product_cost=DB::select('select epm.*,ml.description from eseal_price_master epm 
    left join master_lookup ml ON ml.value=epm.tax_class_id where epm.id='.$pid);
         
	     return $product_cost;
	}
	public function checkCustId($pid,$customer_id,$ima_id)
	{
		//return $customer_id;
		$check=DB::select('select id from customer_cart where product_id="'.$pid.'" 
			and customer_id="'.$customer_id.'" and ima_id='.$ima_id);
	    return $check;
	}
	public function getCartQuantity($cust_id,$ima_id,$check_id)
  {
		if($check_id==1){
  if(!empty($cust_id)){
    $product_id=DB::select('select customer_product_plan_id from customer_products_plans where customer_id="'.$cust_id.'" and name="IoT"');
    $product_id=$product_id[0]->customer_product_plan_id;
    $cart_qty=DB::select('select count(distinct product_id) as cart_qty from customer_cart where customer_id="'.$cust_id.'" and ima_id="'.$ima_id.'" and product_id='.$product_id);
	 }
   else
   $cart_qty=DB::select('select count(distinct product_id) as cart_qty from customer_cart where customer_id="'.$cust_id.'" and ima_id="'.$ima_id.'" and product_id=1');
   
   }
   if($check_id==9){
  if(!empty($cust_id)){
    $product_id=DB::select('select customer_product_plan_id from customer_products_plans where customer_id="'.$cust_id.'" and name="IoT"');
    $product_id=$product_id[0]->customer_product_plan_id;
    $cart_qty=DB::select('select count(distinct product_id) as cart_qty from customer_cart where customer_id="'.$cust_id.'" and ima_id="'.$ima_id.'" and product_id!='.$product_id);
  }
  else
   $cart_qty=DB::select('select count(distinct product_id) as cart_qty from customer_cart where customer_id="'.$cust_id.'" and ima_id="'.$ima_id.'" and product_id!=1');
   }
  	
    return $cart_qty;
	 
  }
    public function checkOut($cust_id,$ima_id,$id)
    {
    	  $finaldata=DB::table('customer_cart as cc')
                  ->leftJoin('eseal_price_master as epm','cc.product_id','=','epm.id')
                  ->leftJoin('master_lookup as ml','ml.value','=','epm.tax_class_id')
                  ->where('cc.ima_id',$ima_id);
        $finaldata_pid=DB::table('customer_cart as cc')
                      ->leftJoin('customer_products_plans as epm','cc.product_id','=','epm.customer_product_plan_id')
                      ->leftJoin('master_lookup as ml','ml.value','=','epm.tax_class_id')
                      ->where('cc.ima_id',$ima_id);

        if(empty($cust_id))
        {
          if($id==1)
          {         
              $finaldata=$finaldata->where('cc.customer_id',$cust_id)->where('cc.product_id',1)->select('epm.id as pid','epm.*','ml.description as tax','cc.*')->get();
          }
         if($id==9)
         {
            $finaldata=$finaldata->where('cc.customer_id',$cust_id)->where('cc.product_id','!=',1)->select('epm.id as pid','epm.*','ml.description as tax','cc.*')->get();
         }
        }
        else
        {
            $product_id=DB::select('select customer_product_plan_id from customer_products_plans where customer_id="'.$cust_id.'" and name="IoT"');
            $product_id=$product_id[0]->customer_product_plan_id;
            if($id==1)
            {
                $finaldata=$finaldata_pid->where('cc.customer_id',$cust_id)->where('cc.product_id',$product_id)->select('epm.customer_product_plan_id as pid','epm.*','ml.description as tax','cc.*')->get();
            }
            if($id==9)
            {
              $finaldata=$finaldata_pid->where('cc.customer_id',$cust_id)->where('cc.product_id','!=',$product_id)->select('epm.customer_product_plan_id as pid','epm.*','ml.description as tax','cc.*')->get();
            }
        }
        return $finaldata;

    }
    public function editCart($pid,$cust_id)
    {
        if(empty($cust_id))
        $product_cost=DB::select('select epm.price,ml.description from eseal_price_master epm left join master_lookup ml ON ml.value=epm.tax_class_id where epm.id='.$pid);
        else
         $product_cost=DB::select('select epm.*,ml.description from customer_products_plans epm 
         left join master_lookup ml ON ml.value=epm.tax_class_id where epm.customer_product_plan_id='.$pid);
        
        return $product_cost;
    }
    public function getCountries()
    {
    	$countries=DB::select('select country_id,name from countries');
        return $countries;
    }
   public function getZones()
   {
   		$zones=DB::select('select zone_id,name from zone');
   		return $zones; 
   }
   public function mapping($id)
   {
     $mapping=DB::select('select  master_lookup.* from eseal_price_master inner join master_lookup on master_lookup.value=eseal_price_master.component_type_lookup_id
   where eseal_price_master.id='.$id);
     return$mapping;   
  }
    public function getProductCostcust($pid,$cid)
    {
         $product_cost=DB::select('select epm.*,ml.description from customer_products_plans epm 
            left join master_lookup ml ON ml.value=epm.tax_class_id where epm.customer_product_plan_id='.$pid.' and customer_id='.$cid);

         return $product_cost;
    }
    
    public function orderStatus()
    {
        return DB::select('SELECT ml.name, ml.value FROM lookup_categories as ls , master_lookup as ml where ls.id = ml.category_id and ls.name ="Order Status"');
    }
    
    public function getOrders($data=array())
    {
        if(empty($data))
        {
            //$order_status = '17006';
            $filter_type = '';
        }else{
            if(isset($data['order_status_id']) && !empty($data['order_status_id']))
            {
                $order_status = $data['order_status_id'];
            }else{
                $order_status = '';
            }
            if(isset($data['customer_id']) && !empty($data['customer_id']))
            {
               $customer_id = $data['customer_id'];
            }else{
               $customer_id = '';
            }
            if(isset($data['from_date']) && !empty($data['from_date']))
            {
               $from_date = $data['from_date'];
            }else{
                $from_date = '';
            }
            
            if(isset($data['to_date']) && !empty($data['to_date']))
            {
               $to_date = $data['to_date'];
            }else{
                $to_date = '';
            }
            if(isset($data['filter_type']) && $data['filter_type'])
            {
                $filter_type = $data['filter_type'];
            }else{
                 $filter_type = '';
            }    
        }
        $where = '';
        //$results = DB::table('eseal_orders')->select('count(*) AS total','date_added');
        if(!empty($order_status)){
            //$results = $results->where('order_status_id',$order_status);
            $where .= (empty($where)) ? 'order_status_id = '.$order_status : ' and order_status_id = '.$order_status;
        }
        
        if(!empty($customer_id)){
            //$results = $results->where('customer_id',$customer_id);
            $where .= (empty($where)) ? 'customer_id = '.$customer_id : ' and customer_id = '.$customer_id;
        }
        if(!empty($from_date)){
            //$results = $results->where('data_added', '>=' ,$from_date);
            $where .= (empty($where)) ? "DATE(date_added) >= '".date('Y-m-d',strtotime($from_date))."'" : " and DATE(date_added) >= '".date('Y-m-d',strtotime($from_date))."'"; 
            
        }
        if(!empty($to_date)){
            //$results = $results->where('data_added', '<=' ,$to_date);
            $where .= (empty($where)) ? " DATE(date_added) <= '".date('Y-m-d',strtotime($to_date))."'" : " and DATE(date_added) <= '".date('Y-m-d',strtotime($to_date))."'";  
        }
        if(!empty($where)){
            $where = ' WHERE '.$where;
        }
        
        if($filter_type == 'MONTH'){
            $groupBy = "group by MONTH(date_added)";
            //$coulmn = 'count(order_id) as total, MONTH(date_added) as date, YEAR(date_added) as yeardate';
        }elseif($filter_type == 'YEAR')
        {
            $groupBy = "group by YEAR(date_added)";
            //$coulmn = 'count(order_id) as total, YEAR(date_added) as date';
        }else{
            $groupBy = "group by date_added";
            //$coulmn = 'count(order_id) as total, date_added as date';
        }    
        $coulmn = 'count(order_id) as total, date_added as date';
        //echo 'SELECT '.$coulmn.' FROM eseal_orders '.$where.' '.$groupBy.' order by date_added'; die;
        $result = DB::select('SELECT '.$coulmn.' FROM eseal_orders '.$where.' '.$groupBy.' order by date_added');
       //$results->groupby('order_status_id');
        //$results = $results->get();

        return $result;
    }
}

?>