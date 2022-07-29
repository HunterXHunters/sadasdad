<?php

use Central\Repositories\CustomerRepo;
use Central\Repositories\OrderRepo;

/*
  Description : This controller is used for user SCO orders.
  Author      : Venkat Reddy Muthuru
  Date        : May-18-2015
*/

class OrderController extends BaseController 
{

  private $custRepo;
  private $OrderRepo;
  private $cust_details;


  function __construct(CustomerRepo $custRepo,OrderRepo $OrderRepo) {
    $this->custRepo = $custRepo;
    $this->OrderRepo = $OrderRepo;
  }
  /* This function is used for creating the new orders
   params : null
   return : users data
   */

  public function getCustomers($test_id,$user_id='')
  {
        
        if(!$user_id){
        $user_id = Session::get('userId');
        }
        //dd($user_id);
        $user_details=$this->custRepo->getUserDetails($user_id);
        //dd($user_details);
        $cust_id=$user_details[0]->customer_id;
        
        if(!empty($cust_id))
        {
        $where = 'customer_id='.$cust_id;
        if($test_id==1)
        $where .= ' and agreement_type="IOT"';
        if($test_id==2)
        $where .= ' and agreement_type="AIDC"';  
        }
        else
        {
        if($test_id==1)
        $where =' agreement_type="IOT"';
        if($test_id==2)
        $where = ' agreement_type="AIDC"';  
        }
        
        $columns='date(start_date) as start_date,date(end_date) as end_date,ima_id,datediff(end_date,start_date)  as estimated_period,customer_id,agreement_type,subscription_id';

        $customer_ima=DB::select('SELECT'.' '.$columns.' '.'FROM customer_ima'.' '.'where'.' '.$where.' '.'order by ima_id desc');
        
        

        $finalCustArrs = array();
        $custs = array();
        $customers_details = json_decode(json_encode($customer_ima), true);

        $iot_type=DB::select('select value from master_lookup where name="SCO IOT-order"');
        $iot_type=$iot_type[0]->value;
        $aidc_type=DB::select('select value from master_lookup where name="SCO AIDC-order"');
        $aidc_type=$aidc_type[0]->value;


        /*$status_id = DB::Table('lookup_categories')
                      ->select('id')
                      ->where('name','Order Status')
                      ->get();*/

        $cancelled_status = DB::table('lookup_categories')
                 ->select('master_lookup.value')
                 ->join('master_lookup','master_lookup.category_id','=','lookup_categories.id')
                 ->where(array('master_lookup.name'=>'Canceled','lookup_categories.name'=>'Order Status'))
                 ->get();

        $cancelled_statusarr=array($cancelled_status[0]->value);
        if(!empty($customers_details)){
        
        foreach($customers_details as  $valus)
        {
          
          if($test_id==1){
          $customer_details=DB::table('eseal_orders')
                 ->select('eseal_customer.*','eseal_orders.*','master_lookup.name')
                 ->join('eseal_customer','eseal_orders.customer_id','=','eseal_customer.customer_id')
                 ->join('master_lookup','master_lookup.value','=','eseal_orders.order_status_id')
                 ->where('eseal_customer.customer_id','=',$valus['customer_id'])
                 ->where('eseal_orders.order_type','=',$iot_type)
                 ->where('eseal_orders.ima_id','=',$valus['ima_id'])
                 ->whereNotIn('eseal_orders.order_status_id',$cancelled_statusarr)
                 ->get();
          }
          else{
           $customer_details=DB::table('eseal_orders')
                 ->select('eseal_customer.*','eseal_orders.*','master_lookup.name')
                 ->join('eseal_customer','eseal_orders.customer_id','=','eseal_customer.customer_id')
                 ->join('master_lookup','master_lookup.value','=','eseal_orders.order_status_id')
                 ->where('eseal_customer.customer_id','=',$valus['customer_id'])
                 ->where('eseal_orders.order_type','=',$aidc_type)                 
                 ->where('eseal_orders.ima_id','=',$valus['ima_id'])
                 ->whereNotIn('eseal_orders.order_status_id',$cancelled_statusarr)
                 ->get();
          }
         //echo '<pre/>';pcint_r($customer_details
        $custArr = array();
        $finalCustArr = array();
        if(!empty($customer_details))
        {
        foreach($customer_details as $value)
        {         
          
          $date=new DateTime($value->date_added);
          $date_added=$date->format('Y-m-d');
          
          if($value->status==1)
          $status = 'Active';
          else
          $status = 'In-Active';
          $custArr['order_no'] = $value->order_number;
          if(empty($value->customer_id)){
          $custArr['customer_name'] = $value->payment_firstname;
          }
          else{
            $custArr['customer_name'] = $value->brand_name;
          }
          $custArr['date_added'] = $date_added;
          $custArr['bill_to_name'] = $value->payment_firstname;
          $custArr['ship_to_name'] = $value->shipping_firstname;
          $custArr['total_cost'] = 'Rs'.$value->total;
          $custArr['order_status'] = $value->name;
          $custArr['actions'] = '<span style="padding-left:20px;" ><a href="/orders/viewOrder/'.$value->order_id."/".$value->ima_id.'"><span class="badge bg-orange"><i class="fa fa-eye"></i></span></a></span><span style="padding-left:50px;" ></span>';
          
      $finalCustArr[] = $custArr;
        }
      }
/*      else{
         $finalCustArr[]='';
      }*/
        
        
        $custs['subscription_id'] =$valus['subscription_id'];
        $cust_name=DB::select('select brand_name from eseal_customer where customer_id='.$valus['customer_id']);
        if (empty($cust_name))
        {
            continue;
        }
        $custs['customer_name'] = $cust_name[0]->brand_name;
        $custs['start_date'] = $valus['start_date'];
        $custs['end_date'] = $valus['end_date'];
        if($test_id==1)
        $custs['actions'] = '<span style="padding-left:20px;" ><a  href="/orders/createOrder/1/'.$valus['ima_id']."/".$valus['customer_id'].'"><span class="badge bg-green"><i class="fa fa-plus"></i></span>
</a></span>';
        else
        $custs['actions'] = '<span style="padding-left:20px;" ><a  href="/orders/createOrder/9/'.$valus['ima_id']."/".$valus['customer_id'].'"><span class="badge bg-green"><i class="fa fa-plus"></i></span>
</a></span>'; 
        $custs['children'] = $finalCustArr;
        $finalCustArrs[] = $custs;
       }
       }
/*       else{
         $finalCustArrs[]='';
       }*/
       return json_encode($finalCustArrs);

  }
  public function createOrder($id,$ima_id,$temp_cust_id)
  {  
    
    $user_id = Session::get('userId');
    Session::put('ima_id',$ima_id);
    Session::put('iot_id',$id);
    $new_order_id=Input::get('new_order_id');
    Session::put('new_order_id', $new_order_id);
    Session::put('cust_temp_cust_id', $temp_cust_id);
    

    if($id==1 || $id==9)
    {
      Session::put('temp_id', $id);
    }
    $temp_id = Session::get('temp_id');     
    $user_details=$this->custRepo->getUserDetails($user_id);    
    //$cust_id=$user_details[0]->customer_id;
    $cust_id=Session::get('cust_temp_cust_id');
    //return $temp_cust_id;
    if(empty($cust_id))
        $component_types=$this->OrderRepo->getComponentType($id,$cust_id,0,$temp_cust_id);
    else  
        $component_types=$this->OrderRepo->getComponentType($id,$cust_id,0,0);
    
    if($id==9)
      $status=0;
    if($id==1)
      $status=1;
    
    if(empty($cust_id))
      $filters=$this->OrderRepo->getComponentType(0,$cust_id,$status,$temp_cust_id);
    else
    $filters=$this->OrderRepo->getComponentType(0,$cust_id,$status,0);
    
    foreach($filters as $key=>$value)
    {
       if(!empty($value->product_plan_id))
       $product_plan_id=$value->product_plan_id;
       else
       $product_plan_id=$value->id; 
       
      $db=DB::table('eseal_price_master')
          ->leftJoin('master_lookup','master_lookup.value','=','eseal_price_master.component_type_lookup_id')
          ->where('eseal_price_master.id',$product_plan_id)
          ->select('master_lookup.name','master_lookup.value','eseal_price_master.name as childname','eseal_price_master.id')
          ->get();
       
       

       if($db[0]->name=='IOT')
       {
         $Iotchildren[$db[0]->id]=$db[0]->childname;
       } 
       if($db[0]->name=='Plan')
       {
         $Planchildren[$db[0]->id]=$db[0]->childname;
       }  
      if($db[0]->name=='AIDC')
       {
         $Aidcchildren[$db[0]->id]=$db[0]->childname;
       }
       if($db[0]->name=='Apps')
       {
         $Appschildren[$db[0]->id]=$db[0]->childname;
       } 
    }
     if(empty($Appschildren)){
      $Appschildren='';
     }
     if(empty($Iotchildren)){
      $Iotchildren='';
     }
     if(empty($Planchildren)){
      $Planchildren='';
     }
     if(empty($Aidcchildren)){
      $Aidcchildren='';
     }
    
    $product_id=DB::select('select customer_product_plan_id from customer_products_plans where customer_id="'.$cust_id.'" and name="IoT"');
    if(!empty($product_id))
    $product_id=$product_id[0]->customer_product_plan_id;
    else
    $product_id='';
       
    if($id==1)
    {
      if(!empty($cust_id))
      {
        $where = 'product_id='.$product_id;
      }
      else
      {
        $where = 'product_id=1';
      }
    }
    if($id==9)
    {
      if(!empty($cust_id))
      {
        $where='product_id!='.$product_id;
      }
      else
      {
        $where='product_id!=1';  
      }
    }
    
    $columns='count(distinct product_id) as cart_qty,qty';
    $where .=' and customer_id="'.$cust_id.'" and ima_id="'.$ima_id.'"';
    $cart_qty=DB::select('SELECT'.' '.$columns.' '.'FROM customer_cart'.' '.'where'.' '.$where);
    
    /*if(!empty($cart_qty))
    $cart_qty=$cart_qty[0]->cart_qty;
    else
    $cart_qty=0; */ 
    //echo '<pre>';print_r($cart_qty[0]->cart_qty);exit;
    Session::put('cartValue',$cart_qty[0]->cart_qty);
    return View::make('orders.create_order')->with(array('finalcomponentarr'=>$component_types,'cart_qty'=>$cart_qty,'cust_id'=>$cust_id,
     'Iotchildren'=>$Iotchildren, 'Planchildren'=>$Planchildren,'Aidcchildren'=>$Aidcchildren,'Appschildren'=>$Appschildren,'ima_id'=>$ima_id,'temp_id'=>$temp_id,'id'=>$id,'temp_cust_id'=>$temp_cust_id));
  }
  public function addCart()
  {     
    $user_id = Session::get('userId');
    $pid=Input::get('id');
    $qty=Input::get('qty');
    $ima_id=Input::get('ima_id');
    $check_id=Input::get('check_id');
    $user_details=$this->custRepo->getUserDetails($user_id);
    //$cust_id=$user_details[0]->customer_id;
    $cust_id=Session::get('cust_temp_cust_id'); 
    $dbquant = DB::Table('customer_cart')->Select('qty')->where('customer_id',$cust_id)->get();
    if(empty($dbquant[0]->qty))
    {
      $qty=$qty;
    }
    else
    {
      $qty= $qty + $dbquant[0]->qty; 
    }  
    if(empty($cust_id))
    {
        $product_cost=$this->OrderRepo->getProductCost($pid); 
        $subtotal = $qty*$product_cost[0]->price;
    }
    else
    {
        $product_cost=$this->OrderRepo->getProductCostcust($pid,$cust_id);
        $subtotal = $qty*$product_cost[0]->agreed_price;
    }
    $subtax = $subtotal * ($product_cost[0]->description/100);
    $user_details=$this->custRepo->getUserDetails($user_id);
    //$cust_id=$user_details[0]->customer_id; 
   // return $cust_id;
    $check = $this->OrderRepo->checkCustId($pid,$cust_id,$ima_id);
   // echo 'pre';print_r($qty);
     //return $check; 
    if($check!=[])
    {
        //For updating the customer cart table
        $customer_cart = CustomerCart::find($check[0]->id);
        $customer_cart->customer_id = $cust_id;
        $customer_cart->product_id = $pid;
        $customer_cart->qty = $qty;
        $customer_cart->subtotal = $subtotal;
        $customer_cart->taxtotal= $subtax;
        $customer_cart->ima_id=$ima_id;
        $customer_cart->save();
    }
    else
    {
        //For inserting the customer cart table
        $customer_cart = new CustomerCart;
        $customer_cart->customer_id = $cust_id;
        $customer_cart->product_id = $pid;
        $customer_cart->qty = $qty;
        $customer_cart->subtotal = $subtotal;
        $customer_cart->taxtotal= $subtax;
        $customer_cart->ima_id=$ima_id;
        $customer_cart->save();
    }
    //return $check_id;
    $cart_qty=$this->OrderRepo->getCartQuantity($cust_id,$ima_id,$check_id);
    $final_cart=$cart_qty[0]->cart_qty;
    Session::put('cartValue',$cart_qty[0]->cart_qty);
    return $final_cart;
  }
  public function checkOut($ima_id,$id,$temp_cust_id)
  {      
      $user_id = Session::get('userId');
      $user_details=$this->custRepo->getUserDetails($user_id);
      //$cust_id=$user_details[0]->customer_id;
      $cust_id=Session::get('cust_temp_cust_id');     
      $finaldata=$this->OrderRepo->checkOut($cust_id,$ima_id,$id);      
      $temp_id = Session::get('temp_id');
      $count=count($finaldata);       
      if(!empty($count))
      {
          for($i=0;$i<$count;$i++)
          {
              $falg[] = $finaldata[$i]->subtotal;  
              $tax_flag[] = $finaldata[$i]->taxtotal;  
             $finaldata[0]->tax = isset($finaldata[0]->tax)?$finaldata[0]->tax:0.00;
          }
          $total_sum=array_sum($falg);
          $total_tax=array_sum($tax_flag);
          $inc_tax=$total_sum+$total_tax;
      }
      else
      {
          $total_sum='';
          $total_tax='';
          $inc_tax='';
      }

     //echo "<pre/>";print_r($finaldata);exit;
     return View::make('orders.checkOut',compact('finaldata','total_sum','total_tax','inc_tax','cust_id','ima_id','temp_id','id','temp_cust_id'));
   }
  public function editCart()
  {
    $pid=Input::get('pid');
    $qty=Input::get('qty');
    $id=Input::get('id');
    $user_id = Session::get('userId');
    $user_details=$this->custRepo->getUserDetails($user_id);
    $cust_id=Session::get('cust_temp_cust_id');
    $product_cost=$this->OrderRepo->editCart($pid,$cust_id);
    if(empty($cust_id))
    {
        $subtotal = $qty*$product_cost[0]->price; 
    }
    else
    {
        $subtotal = $qty*$product_cost[0]->agreed_price;
    }
    $subtax = $subtotal * ($product_cost[0]->description/100);

     $totQuantity = DB::select('select customer_id, sum(quantity) as cqty from customer_iot_cart where customer_id='.Session::get('cust_temp_cust_id').' group by customer_id ');

        if(empty($totQuantity))
            $totQuantity = 0;
        else
          $totQuantity = $totQuantity[0]->cqty;

     if($totQuantity<=$qty)
     {
        $customer_cart = CustomerCart::where('id', $id)->first();
        $customer_cart->customer_id = $cust_id;
        $customer_cart->product_id = $pid;
        $customer_cart->qty= $qty;
        $customer_cart->subtotal= $subtotal;
        $customer_cart->taxtotal= $subtax;
        $customer_cart->save();
        return 1;
      }
      else
      {
          return 0;
      }
      $cart_qty=$this->OrderRepo->getCartQuantity($cust_id,Session::get('ima_id'),Session::get('iot_id'));
      Session::put('cartValue',$cart_qty[0]->cart_qty);
  }
  public function proceedCheckOut($ima_id,$id)
  {      
     
      $user_id = Session::get('userId');
      //$temp_cust_id=Input::get('temp_cust_id');
      $temp_cust_id=Session::get('cust_temp_cust_id');
      $user_details=$this->custRepo->getUserDetails($user_id);
      //$cust_id=$user_details[0]->customer_id;
      $cust_id=$temp_cust_id;
     

      $finaldata=$this->OrderRepo->checkOut($cust_id,$ima_id,$id); 
      $iotquantity = DB::select('select customer_id, sum(quantity) as cqty from customer_iot_cart where customer_id='.$cust_id.' group by customer_id ');
      if(empty($iotquantity))
          $iotquantity = 0;
      else
        $iotquantity = $iotquantity[0]->cqty;

      //echo '<pre/>';print_r($finaldata);exit;
     // $finaldata[0]->review_qty =  $finaldata[0]->qty;
      
      $customer_address=DB::select('select * from customer_address where customer_id='.$cust_id);
      $customers=DB::select('select customer_id,brand_name from eseal_customer');
      $count=count($finaldata);
      for($i=0;$i<$count;$i++)
      {
          $falg[] = $finaldata[$i]->subtotal;  
          $tax_flag[] = $finaldata[$i]->taxtotal;  
          $finaldata[0]->review_qty = ($finaldata[0]->qty - $iotquantity);
      }
      $total_sum=array_sum($falg);
      $total_tax=array_sum($tax_flag);
      $inc_tax=$total_sum+$total_tax;
     
      
      $countries=$this->OrderRepo->getCountries();
      $zones=$this->OrderRepo->getZones();
      //echo '<pre/>';print_r($zones);exit;
      $delivery_mode=DB::table('lookup_categories')->where('name','IoT Delivery Modes')->get();
      $delivery_id=$delivery_mode[0]->id;
      $delivery_master_lookup=DB::table('master_lookup')->where('category_id',$delivery_id)->get();
     
      foreach ($delivery_master_lookup as $key=>$itm) 
      {
          $array_circle[] = array('id' => $itm->id, 'name' => $itm->name);
      }
     
     $circles = json_encode($array_circle);
     
     $vendors=DB::table('locations as loc')->leftjoin('location_types as loc_type','loc_type.location_type_id','=','loc.location_type_id')
              ->where('loc_type.location_type_name','Vendor')
              ->where('loc.manufacturer_id',$cust_id)
              ->select('loc.location_name','loc.location_id')->get();
     
    if(!empty($vendors))
    {
      foreach ($vendors as $key=>$itm) 
      {
        $array_circle1[] = array('id' => $itm->location_id, 'name' => $itm->location_name);
      }
    
      $vendors_array = json_encode($array_circle1);
    }
    else
    {
       $vendors_array='';
    }
    $cart_qty=$this->OrderRepo->getCartQuantity($cust_id,Session::get('ima_id'),Session::get('iot_id'));
      Session::put('cartValue',$cart_qty[0]->cart_qty);
    return View::make('orders.proceed_checkout',compact('finaldata','total_sum','total_tax','countries','zones','inc_tax','customer_address','cust_id','customers','ima_id','id','delivery_master_lookup','vendors','circles','vendors_array','temp_cust_id','array_circle','array_circle1'));
   }
   public function getQuantity()
   {
        $finaldata=$this->OrderRepo->checkOut(Session::get('cust_temp_cust_id'),Session::get('ima_id'),Session::get('iot_id')); 

        $iotquantity = DB::select('select customer_id, sum(quantity) as cqty from customer_iot_cart where customer_id='.Session::get('cust_temp_cust_id').' group by customer_id ');

        if(empty($iotquantity))
            $iotquantity = 0;
        else
          $iotquantity = $iotquantity[0]->cqty;
        $finaldata[0]->qty = ($finaldata[0]->qty - $iotquantity);
        return $finaldata[0]->qty;
   }
    public function getPresentQuantity($order_id)
   {

        $iotquantity = DB::select('select customer_id, sum(quantity) as cqty from eseal_order_products where customer_id='.Session::get('cust_temp_cust_id').' and order_id= '.$order_id.' group by customer_id ');

        if(empty($iotquantity))
            $iotquantity = 0;
        else
          $iotquantity = $iotquantity[0]->cqty;
        
        return $iotquantity;
   }

    public function addIotCodes()
     {
        $data = Input::get();
        $cid = Session::get('cust_temp_cust_id');
        if($data['vendorid']!=0)
        {
              $validate_vendor = DB::table('customer_iot_cart')
                              ->where('vendor_id',$data['vendorid'])
                              ->get();
              if($validate_vendor)
                return '1';
              else
              {
                 $vendor_name = DB::table('locations')->select('locations.location_name')->where('location_id',$data['vendorid'])->get();
                 $data_added = DB::Table('customer_iot_cart')
                              ->insert(array('customer_id' => $cid,
                                     'delivery_mode_id' => $data['deliveryid'],
                                     'delivery_mode' => $data['deliveryname'], 
                                     'vendor_id' => $data['vendorid'], 
                                     'vendor_name' => $vendor_name[0]->location_name, 
                                     'quantity' => $data['total_codes']
                                     ));
                    if($data_added)
                    {                       
                         $availQuantity = $this->getQuantity();
                         return '2'.'-'.$availQuantity;
                    }
                    else
                    {
                      return '0';
                    }                    
                }
        }
        else
        {
            $validate_vendor = DB::table('customer_iot_cart')            
                    ->select('quantity')
                    ->where(array('customer_id'=>$cid,'delivery_mode_id'=>$data['deliveryid']))
                    ->get();
            if($validate_vendor)
            {                    
                /*if($data['total_codes'] < $validate_vendor[0]->quantity)
                      $pquantity = $data['total_codes'];
                else*/
                      $pquantity = $data['total_codes'] + $validate_vendor[0]->quantity;

               $data_added = DB::Table('customer_iot_cart')
                        ->where(array('delivery_mode_id'=>Input::get('deliveryid'),'customer_id'=>$cid))
                        ->update(array('delivery_mode_id'=>$data['deliveryid'],'vendor_id' => Input::get('vendorid'),'delivery_mode'=>$data['deliveryname'], 
                         'quantity' => $pquantity,                              
                         'vendor_name' => ''                         
                      ));
                      $vq = DB::getQueryLOg();
                      //return $vq;
                  if($data_added)
                  {
                    
                      $availQuantity = $this->getQuantity();
                      return '2'.'-'.$availQuantity;
                  }else{
                    return '0';
                  }
             }               
              else
              {
                  $data_added = DB::Table('customer_iot_cart')
                              ->insert(array('customer_id' => $cid,
                                     'delivery_mode_id' => $data['deliveryid'],
                                     'delivery_mode' => $data['deliveryname'], 
                                     'vendor_id' => $data['vendorid'], 
                                     'vendor_name' => '', 
                                     'quantity' => $data['total_codes'] 
                                     ));
                  if($data_added)
                  {
                     $availQuantity = $this->getQuantity();
                      return '2'.'-'.$availQuantity;
                  }
                  else
                  {
                    return 0;
                  }
                
            }
          }
      }

  public function showIotCodes()
  {
        $cid = Session::get('cust_temp_cust_id');
        $iotArr = array();
        $finaliotArr = array();
        $iot_details = DB::table('customer_iot_cart')
                            ->select('customer_iot_cart.*')
                            ->where('customer_iot_cart.customer_id','=',$cid)
                            ->get();
                                
        foreach($iot_details as $value)
        {         
          $iotArr['id'] = $value->id;
          $iotArr['customer_id'] = $value->customer_id;
          $iotArr['delivery_mode'] = $value->delivery_mode;
          if($value->delivery_mode=='Print and Deliver' || $value->delivery_mode=='Downloadable')
             $iotArr['vendor'] = 'Not Applicable';
          else
            $iotArr['vendor'] = $value->vendor_name;
          $iotArr['quantity'] = $value->quantity;
          $iotArr['actions'] ='
          <span style="padding-left:20px;" ><a data-href="/orders/editiotcodes/' . $value->id . '" data-toggle="modal" data-target="#basicvalCodeModal"><span class="badge bg-light-blue"><i class="fa fa-pencil"></i></span></a></span><span style="padding-left:10px;" ></span>
            <span style="padding-left:10px;" ><a onclick = "deleteIot(' . $value->id . ')"><span class="badge bg-red"><i class="fa fa-trash-o"></i></span></a></span>';
          $finaliotArr[] = $iotArr;
        }
        
       return json_encode($finaliotArr);
  }

  public function editIotCodes($id)
    {
        $iot = DB::table('customer_iot_cart')->where('id', $id)->first();
        return Response::json($iot);
    }

  public function updateIotCodes($id)
  {
      
      $data = Input::get();
      $cid = Session::get('cust_temp_cust_id');
      $vend_name = Input::get('vendor_name');
      $vend_name = isset($vend_name)?$vend_name:'';
      if($data['vendorid']!=0)
      {
          $validate_vendor = '';
          //if($data['vendor_idValidate']!=$data['vendorid'])
          //{
              $validate_vendor = DB::table('customer_iot_cart')
                            ->where(array('vendor_id'=>$data['vendorid'],'customer_id'=>$cid))
                           ->get();
          //}
          if($validate_vendor)
          {
              $editIdQuantity = DB::table('customer_iot_cart')            
                      ->select('quantity','delivery_mode_id','vendor_id')
                      ->where(array('id'=>$data['id'],'customer_id'=>$cid))
                      ->get();

                  if($editIdQuantity)
                  {
                       $prev_quantity = $editIdQuantity[0]->quantity - $data['total_codes'];
                  }
                  else
                  {
                    $prev_quantity = $data['total_codes'];
                  }
                   if($editIdQuantity[0]->quantity<$data['total_codes'])
                        $prev_quantity =  0;

                  $pres_quantity = $data['total_codes']+ $validate_vendor[0]->quantity;

                  if( $editIdQuantity[0]->vendor_id == Input::get('vendorid'))
                  {
                       $data_added = DB::Table('customer_iot_cart')
                            ->where(array('delivery_mode_id'=>Input::get('deliveryid'),'customer_id'=>$cid,'vendor_id'=>$editIdQuantity[0]->vendor_id))
                            ->update(array('delivery_mode_id'=>$data['deliveryid'],'delivery_mode'=>$data['deliveryname'], 
                             'quantity' => $data['total_codes']                        
                          ));
                  }
                  else
                  {

                     $data_added = DB::Table('customer_iot_cart')
                            ->where(array('delivery_mode_id'=>Input::get('deliveryid'),'customer_id'=>$cid,'vendor_id'=>Input::get('vendorid')))
                            ->update(array('delivery_mode_id'=>$data['deliveryid'],'delivery_mode'=>$data['deliveryname'], 
                             'quantity' => $pres_quantity                      
                          ));
                      if($prev_quantity==0)
                      {
                        DB::Table('customer_iot_cart')
                          ->where(array('customer_id'=>$cid,'id'=>$data['id']))
                          ->delete();
                      }
                      else
                      {
                        $data_added_prev = DB::Table('customer_iot_cart')
                            ->where(array('id'=>Input::get('id'),'customer_id'=>$cid))
                            ->update(array('quantity' => $prev_quantity                         
                          ));
                    }
                     // DB::table('customer_iot_cart')->where(array('id'=>Input::get('id'),'customer_id'=>$cid))->delete();

                    $vq = DB::getQueryLOg();
                  }
                   
                    //return end($vq);
                  if($data_added)
                  {
                      $availQuantity = $this->getQuantity();
                      return '2'.'-'.$availQuantity;
                  }
                  else
                  {
                    return '0';
                  }
          }
          else
          {
               
              $editIdQuantity = DB::table('customer_iot_cart')            
                      ->select('quantity')
                      ->where(array('id'=>$data['id'],'customer_id'=>$cid))
                      ->get();
                  if($editIdQuantity)
                  {
                       $prev_quantity = $editIdQuantity[0]->quantity - $data['total_codes'];
                  }
                  else
                  {
                    $prev_quantity = $data['total_codes'];
                  }
                   if($editIdQuantity[0]->quantity<$data['total_codes'])
                        $prev_quantity =  0;

                  $pres_quantity = $data['total_codes'];
                 
                  $data_added = DB::Table('customer_iot_cart')
                              ->insert(array('customer_id' => $cid,
                                     'delivery_mode_id' => $data['deliveryid'],
                                     'delivery_mode' => $data['deliveryname'], 
                                     'vendor_id' => $data['vendorid'], 
                                     'vendor_name' => $vend_name, 
                                     'quantity' => $pres_quantity 
                                     ));
                  
                    if($prev_quantity==0)
                    {
                        DB::Table('customer_iot_cart')
                          ->where(array('customer_id'=>$cid,'id'=>$data['id']))
                          ->delete();
                    }
                    else
                    {
                     $data_added_prev = DB::Table('customer_iot_cart')
                            ->where(array('id'=>Input::get('id'),'customer_id'=>$cid))
                            ->update(array('quantity' => $prev_quantity                         
                          ));
                    }
              if($data_added)
              {
                 $availQuantity = $this->getQuantity();
                  return '2'.'-'.$availQuantity;
              }
              else
              {
                return '0';
              }                    
            }
      }else
      {
              
             $validate_vendor = DB::table('customer_iot_cart')            
                      ->select('quantity')
                      ->where(array('delivery_mode_id'=>$data['deliveryid'],'customer_id'=>$cid))
                      ->get();
            if($validate_vendor)
            {    
                    $editIdQuantity = DB::table('customer_iot_cart')            
                      ->select('quantity','delivery_mode_id')
                      ->where(array('id'=>$data['id'],'customer_id'=>$cid))
                      ->get();

                  if($editIdQuantity)
                  {
                       $prev_quantity = $editIdQuantity[0]->quantity - $data['total_codes'];
                  }
                  else
                  {
                    $prev_quantity = $data['total_codes'];
                  }
                   if($editIdQuantity[0]->quantity<$data['total_codes'])
                        $prev_quantity =  0;

                  $pres_quantity = $data['total_codes']+ $validate_vendor[0]->quantity;

                  if( $editIdQuantity[0]->delivery_mode_id == Input::get('deliveryid'))
                  {
                       $data_added = DB::Table('customer_iot_cart')
                            ->where(array('delivery_mode_id'=>Input::get('deliveryid'),'customer_id'=>$cid))
                            ->update(array('delivery_mode_id'=>$data['deliveryid'],'vendor_id' => Input::get('vendorid'),'delivery_mode'=>$data['deliveryname'], 
                             'quantity' => $data['total_codes'],                              
                             'vendor_name' => $vend_name                         
                          ));
                  }
                  else
                  {

                     $data_added = DB::Table('customer_iot_cart')
                            ->where(array('delivery_mode_id'=>Input::get('deliveryid'),'customer_id'=>$cid))
                            ->update(array('delivery_mode_id'=>$data['deliveryid'],'vendor_id' => Input::get('vendorid'),'delivery_mode'=>$data['deliveryname'], 
                             'quantity' => $pres_quantity,                              
                             'vendor_name' => $vend_name                         
                          ));
                      if($prev_quantity==0)
                      {
                        DB::Table('customer_iot_cart')
                          ->where(array('customer_id'=>$cid,'id'=>$data['id']))
                          ->delete();
                      }
                      else
                      {
                        $data_added_prev = DB::Table('customer_iot_cart')
                            ->where(array('id'=>Input::get('id'),'customer_id'=>$cid))
                            ->update(array('quantity' => $prev_quantity,                              
                             'vendor_name' => $vend_name                         
                          ));
                    }
                     // DB::table('customer_iot_cart')->where(array('id'=>Input::get('id'),'customer_id'=>$cid))->delete();

                    $vq = DB::getQueryLOg();
                  }
                   
                    //return end($vq);
                  if($data_added)
                  {
                      $availQuantity = $this->getQuantity();
                      return '2'.'-'.$availQuantity;
                  }
                  else
                  {
                    return '0';
                  }
             }               
              else
              {  
                  $editIdQuantity = DB::table('customer_iot_cart')            
                      ->select('quantity')
                      ->where(array('id'=>$data['id'],'customer_id'=>$cid))
                      ->get();
                  if($editIdQuantity)
                  {
                       $prev_quantity = $editIdQuantity[0]->quantity - $data['total_codes'];
                  }
                  else
                  {
                    $prev_quantity = $data['total_codes'];
                  }
                   if($editIdQuantity[0]->quantity<$data['total_codes'])
                        $prev_quantity =  0;

                  $pres_quantity = $data['total_codes'];
                 
                  $data_added = DB::Table('customer_iot_cart')
                              ->insert(array('customer_id' => $cid,
                                     'delivery_mode_id' => $data['deliveryid'],
                                     'delivery_mode' => $data['deliveryname'], 
                                     'vendor_id' => $data['vendorid'], 
                                     'vendor_name' => $vend_name, 
                                     'quantity' => $data['total_codes'] 
                                     ));
                  
                    if($prev_quantity==0)
                    {
                        DB::Table('customer_iot_cart')
                          ->where(array('customer_id'=>$cid,'id'=>$data['id']))
                          ->delete();
                    }
                    else
                    {
                     $data_added_prev = DB::Table('customer_iot_cart')
                            ->where(array('id'=>Input::get('id'),'customer_id'=>$cid))
                            ->update(array('quantity' => $prev_quantity,                              
                             'vendor_name' => $vend_name                         
                          ));
                    }
                    

                  if($data_added)
                  {
                     $availQuantity = $this->getQuantity();
                    return '2'.'-'.$availQuantity;
                  }
                  else
                  {
                    return '0';
                  }
                
            }                   

            
           }

  }  

  public function deleteIotCodes($id)
  {
        $deleted =  DB::Table('customer_iot_cart')->where('id', '=', $id)->delete();
        if($deleted)
        {               
             $availQuantity = $this->getQuantity();
             return $availQuantity;
        }
        else
        {
          return 0; 
        }
  }
   
   public function deleteCart()
   {
      $id=Input::get('id');
      $ima_id=Input::get('ima_id');
      $test_id=Input::get('test');
        //return $test_id;
      if($test_id)
      {
          $eseal_order_products=DB::table('eseal_order_products')->where('sub_order_id','=',$test_id)->delete();
      }
      if($id)
      {
          $delete_cart=CustomerCart::where('id', $id)->first();
          $delete_cart->delete();
      }
      return Response::json([
        'status' => true,
        'message'=>'Sucessfully deleted.'
      ]); 
      
   }
   public function placeOrder($ima_id,$id)
   {  
      $user_id = Session::get('userId');
      $new_order_id = Session::get('new_order_id');
      $delivery_mode_id=Input::get('delivery_mode_id');
      $vendor_id=Input::get('vendor_id');
      $divide_codes=Input::get('divide_codes');
      $present_order_id=Input::get('test');
      $user_details=$this->custRepo->getAllCustomers(Session::get('cust_temp_cust_id'));   
      $cust_id=Session::get('cust_temp_cust_id');    
      $customer_details = $this->custRepo->getAllCustomers(Session::get('cust_temp_cust_id')); 
      if($id!=9)
      {
        if(!empty($delivery_mode_id))
        {
            Session::put('delivery_mode_id',$delivery_mode_id);
            Session::put('vendor_id',$vendor_id);
            Session::put('divide_codes',$divide_codes);
            Session::put('present_order_id',$present_order_id);
            $eseal_order_edit=DB::table('eseal_order_products')->where('sub_order_id','=',$present_order_id)->get();              
        }
      }
          
      $finaldata=$this->OrderRepo->checkOut($cust_id,$ima_id,$id);
      $custom_id=Input::get('custom_id');
     
      $order_status_id=DB::table('master_lookup')
                     ->where('name',"Placed")
                     ->select('value')
                     ->get(); 

      $customer_cart = $this->OrderRepo->getCustomerCartDetails($id,$cust_id,$ima_id);

      $iot_type=DB::table('master_lookup')
              ->where('name',"SCO IOT-order")
              ->select('value')
              ->get();    
      $iot_type=$iot_type[0]->value; 

      $aidc_type=DB::table('master_lookup')
              ->where('name',"SCO AIDC-order")
              ->select('value')
              ->get();
      $aidc_type=$aidc_type[0]->value;
   
      $pay=Input::get('payment_type');
      $payment_type=DB::select('select value from master_lookup where name="'.$pay.'"');
      $payment_type=$payment_type[0]->value;
      $count=count($finaldata);
      for($i=0;$i<$count;$i++)
      {
          $falg[] = $finaldata[$i]->subtotal;  
          $tax_flag[] = $finaldata[$i]->taxtotal;  
      }
      $total_sum=array_sum($falg);
      $total_tax=array_sum($tax_flag);
      $total=$total_sum+$total_tax;
      if(empty($new_order_id))
      {
          $eseal_orders = new EsealOrders;
          if($custom_id)
            $eseal_orders->customer_id = $custom_id;
          else
            $eseal_orders->customer_id = $cust_id;
          $eseal_orders->customer_group_id = $customer_details[0]->customer_type_id;
          $eseal_orders->firstname = $customer_details[0]->firstname;
         // $eseal_orders->lastname = $customer_details[0]->lastname;
          $eseal_orders->email = $customer_details[0]->email;
          $eseal_orders->telephone = Input::get('bill_phone_no');
          $eseal_orders->payment_firstname = Input::get('bill_first_name');
          $eseal_orders->payment_lastname = Input::get('bill_last_name');
          $eseal_orders->payment_address_1 = Input::get('bill_address');
          $eseal_orders->payment_address_2 = Input::get('bill_address');
          $eseal_orders->payment_city = Input::get('bill_city');
          $eseal_orders->payment_country_id = Input::get('bill_country_id');
          $eseal_orders->payment_zone_id =Input::get('bill_zone_id');
          $eseal_orders->payment_method =$payment_type;
          $eseal_orders->date_added=date('Y-m-d');
          $eseal_orders->total = $total ;
          $eseal_orders->ima_id=$ima_id;
          $eseal_orders->order_status_id = $order_status_id[0]->value;
          if($customer_cart[0]->name=='IoT')
              $eseal_orders->order_type = $iot_type;
          else
            $eseal_orders->order_type = $aidc_type;
          $eseal_orders->save();
          
          $order_id = DB::getPdo()->lastInsertId();
          $order_number = 'ORD'.date('y').date('m').date('d').str_pad($order_id,6,"0",STR_PAD_LEFT);
          
          DB::table('eseal_orders')
                  ->where('order_id', $order_id)
                  ->update(array('order_number' => $order_number));
          }
    
          $customer_cart = $this->OrderRepo->getCustomerCartDetails($id,$cust_id,$ima_id);  
          
        foreach($customer_cart as $value)
        {
          if(empty($cust_id))
            $product=DB::select('select name from eseal_price_master where id='.$value->product_id);
          else
            $product=DB::select('select name from customer_products_plans where customer_product_plan_id='.$value->product_id);

          $pname=$product[0]->name;
          $customer_iot_cart_data = DB::table('customer_iot_cart')->where('customer_iot_cart.customer_id',$cust_id)->get();
          $sno=1;
          foreach($customer_iot_cart_data as $iot_data)
          {   
            $eseal_order_products = new EsealOrderProducts;  
              if(empty($new_order_id))
                $eseal_order_products->order_id = $order_id;
              else
                $eseal_order_products->order_id = $new_order_id;

              $sub_order_id = $order_number.'_'.$cust_id.$sno;
        
              $eseal_order_products->pid = $value->product_id;
              $eseal_order_products->name = $pname;
              $eseal_order_products->quantity = $iot_data->quantity;
              $eseal_order_products->delivery_mode = $iot_data->delivery_mode;
              $eseal_order_products->vendor_id = $iot_data->vendor_id;
              $eseal_order_products->sub_order_id = $sub_order_id;
              $eseal_order_products->price = $value->price;
              $eseal_order_products->vendor_flag= 0;
              $eseal_order_products->delivery_to=$iot_data->vendor_name;
              $eseal_order_products->customer_id=$cust_id;
              $eseal_order_products->total = $iot_data->quantity * $value->price;
              $eseal_order_products->tax = $value->taxtotal;
              $eseal_order_products->save();
              $sno++;
          }
        }
        $order_payments = new OrderPayments;
        if(empty($new_order_id))
          $order_payments->order_id = $order_id;
        else
          $order_payments->order_id = $new_order_id;      
        $order_payments->payment_type = $payment_type;
        $order_payments->trans_reference_no = Input::get('trans_reference_no');
        $order_payments->payee_bank = Input::get('payee_bank');
        $order_payments->ifsc_code = Input::get('ifsc_code');
        $order_payments->amount = Input::get('amount');
        $order_payments->payment_date = date('Y-m-d');
        $order_payments->save();
    
        $order_payments = new OrderHistory;
        if(empty($new_order_id))
        $order_payments->order_id = $order_id;
        else
        $order_payments->order_id = $new_order_id;
      
        $order_payments->order_status_id = $order_status_id[0]->value;;
        $order_payments->date_added=date('Y-m-d');
        $order_payments->save();
        $user_id = Session::get('userId');
        $user_details=$this->custRepo->getUserDetails($user_id);    
        $eseal_order_products=EsealOrderProducts::where('order_id',$order_id)->get();    
        $username=Input::get('bill_first_name');
        $custMail=$customer_details[0]->email;
        $custName=$customer_details[0]->brand_name;
        
        \Mail::send(['html' => 'emails.orders'], array('order_number' => $order_number, 'username' => $username,'eseal_order_products'=> $eseal_order_products,'vendorFlag'=>0), 
          function($message) use ($custMail)
         {        
                    $message->to($custMail)->subject('Received eSeal(IoT) order.');
         });
        $mailids = array();
        foreach ($eseal_order_products as $esp) 
        {
          $esp['vendor_id'] = isset($esp['vendor_id'])?$esp['vendor_id']:0;
          if($esp['vendor_id']!=0)
          {
              $temp = DB::table('locations')->select('location_email')->where(array('manufacturer_id' => $cust_id,'location_id'=> $esp['vendor_id']))->get();
              $mailids[] = $temp[0]->location_email;
          }    
        }
        
        \Mail::send(['html' => 'emails.orders'], array('order_number' => $order_number, 'username' => $username,'eseal_order_products'=> $eseal_order_products,'vendorFlag'=>1,'custName'=>$custName), 
          function($message) use ($mailids,$custMail,$custName) 
         {        
                    $message->to($custMail)->bcc($mailids)->subject('Order from '.$custName);
         });
        
      $customer_iot_cart_data = DB::table('customer_iot_cart')->where('customer_iot_cart.customer_id',$cust_id)->delete();
        
        if($id==1)
        {
          if(!empty($cust_id))
          {
              $product_id=DB::select('select customer_product_plan_id from customer_products_plans where customer_id="'.$cust_id.'" and name="IoT"');
              $product_id=$product_id[0]->customer_product_plan_id;
              $delete =DB::table('customer_cart')->where('customer_id', '=', $cust_id)->where('product_id','=',$product_id)->delete();
          }
          else
          {
            $delete =DB::table('customer_cart')->where('customer_id', '=', $cust_id)->where('product_id','=',1)->delete();
          }
        }    
        if($id==9)
        {
          if(!empty($cust_id))
          {
            $product_id=DB::select('select customer_product_plan_id from customer_products_plans where customer_id="'.$cust_id.'" and name="IoT"');
            $product_id=$product_id[0]->customer_product_plan_id;
            $delete =DB::table('customer_cart')->where('customer_id', '=', $cust_id)->where('product_id','!=',$product_id)->delete();
          }
          else
          {
            $delete =DB::table('customer_cart')->where('customer_id', '=', $cust_id)->where('product_id','!=',1)->delete();
          }
        }
        parent::Breadcrumbs(array('Home'=>'/','OrderConfirmation'=>'#')); 
        return View::make('orders.manage_orders',compact('order_number'));
    }
   public function getPrepaid()
   {
       $prepaid=DB::select('select ml.id,ml.category_id,ml.name from master_lookup ml inner join lookup_categories as 
         lc on ml.category_id=lc.id where ml.category_id=20');

       return json_encode($prepaid); 
   }
   public function getZones()
   {
      
     $country_id=Input::get('type');
     $zones=DB::select('select zone_id as id,name from zone where country_id='.$country_id);
     
/*     $sel='<select class="form-control" id="bill_zone_id" name="bill_zone_id" parsley-trigger="change" parsley-required="true" parsley-error-container="#selectbox" >';
     $sel=$sel.'<option value="" selected >Please Select..</option>';
     foreach($zones as $key=>$value)
     {
      $sel = $sel.'<option value="'.$value->id .'">'.$value->name .'</option>';
     
     }
     $sel.='</select>';
    echo $sel;exit;*/
    return $zones;
    
   }
   public function viewOrder($id,$ima_id)
   {
     
     
     $columns='eseal_orders.*,eseal_customer.*,master_lookup.name,master_lookup.value,date(eseal_orders.date_added) as dateadded,date(eseal_orders.date_modified) as datemodified';
     
     $leftJoin='eseal_customer on eseal_orders.customer_id=eseal_customer.customer_id left join master_lookup
       on eseal_orders.order_status_id=master_lookup.value';
     
     $where='eseal_orders.order_id="'.$id.'"';

     $where_purchased='order_id="'.$id.'"';
     
     if(!empty($ima_id)){
     $where .= 'and eseal_orders.ima_id='.$ima_id; 
     $where_purchased .='and ima_id='.$ima_id;
     }
     
     $result=DB::select('SELECT'.' '.$columns.' '.'FROM eseal_orders left join'.' '.$leftJoin.' '.'where'.' '.$where);

     $purchased_date=DB::select('SELECT date(date_added) as date_added FROM eseal_orders WHERE' .' '.$where_purchased);

    

     if(!empty($purchased_date))
     $purchased_date=$purchased_date[0]->date_added;
     else
     $purchased_date='';
 
     $date=new DateTime($purchased_date);
     $date_added=$date->format('Y-m-d');
     Session::put('order_sessionid', $id);
     Session::put('order_ima_id', $ima_id);
     Session::put('cust_temp_cust_id', $result[0]->customer_id);

     $user_id = Session::get('userId');
     $user_details=$this->custRepo->getUserDetails($user_id);
     $cust_id=$user_details[0]->customer_id;

     // $cancelled_status = DB::table('lookup_categories')
     //             ->select('master_lookup.value')
     //             ->join('master_lookup','master_lookup.category_id','=','lookup_categories.id')
     //             ->where(array('master_lookup.name'=>'Canceled','lookup_categories.name'=>'Order Status'))
     //             ->get();
    
     $lookup_id=DB::select('select id from lookup_categories where name="Order Status"');
     $lid=$lookup_id[0]->id;
     //$status_name = $result[0]->name;
     $status_value = $result[0]->value;
     $admin=DB::select('select name,value from master_lookup where category_id='.$lid.' and value >'.$status_value);
     
     $country_id=$result[0]->payment_country_id;
     $zone_id=$result[0]->payment_zone_id;
     
     if(!empty($country_id))
     {
     $country_name=DB::select('select name from countries where country_id='.$country_id);
     $country=$country_name[0]->name;
     }
     else{
      $country='';
     }    
     if(!empty($zone_id))
     {
     $zone_name=DB::select('select name from zone where zone_id='.$zone_id);
     $zone=$zone_name[0]->name;
     }
    else{
      $zone='';
    }
     
     $customer_name=DB::table('eseal_customer')->where('customer_id',$result[0]->customer_id)->pluck('brand_name');
     
     $order_status_id=$result[0]->order_status_id;
     $custArr = array();
     $finalCustArr = array();
     
     $customer_details = DB::table('eseal_order_products')->where('order_id','=',$id)->get();

     if($customer_details[0]->name=="IoT"){

       //$customer_details = DB::select('select *,sum(quantity) as quantity,sum(total) as price from eseal_order_products where order_id="'.$id.'"order by eseal_order_products.order_product_id desc limit 1');
      $customer_details = DB::select('select *,order_id,sum(quantity) as quantity,sum(total) as price,sum(tax) tax,sum(total) as total from eseal_order_products where order_id="'.$id.'" group by order_id order by eseal_order_products.order_product_id desc');
       $delivery_details = DB::table('eseal_order_products')
                          ->select('eseal_order_products.*','locations.*','eseal_customer.firstname as custfname','eseal_customer.lastname as custlname')
                          ->leftJoin('locations','locations.location_id','=','eseal_order_products.vendor_id')
                          ->leftJoin('eseal_customer','eseal_customer.customer_id','=','eseal_order_products.customer_id')
                          ->where('order_id','=',$id)
                          ->get();
       //echo '<pre/>';print_r($delivery_details);exit;
     } 
      
     $sum=DB::select('select sum(total) as sum_total,sum(tax) as tax_total from eseal_order_products where order_id='.$id);
     $total_master_sum=$sum[0]->sum_total+$sum[0]->tax_total;
     
     $order_history=DB::select('select master_lookup.*,order_history.*,date(order_history.date_added) as dateadded from order_history inner join master_lookup on order_history.order_status_id=master_lookup.value
      where order_history.order_id="'.$id.'" order by order_history.order_history_id desc');
     
     $status_update=$result[0]->name;
     $payment_id= $result[0]->payment_method;
     
     $payment_method=DB::select('select name from master_lookup where value='.$payment_id);

     $payment_method=$payment_method[0]->name;
     parent::Breadcrumbs(array('Home'=>'/','OrderView'=>'#')); 

     $approved_status = DB::table('lookup_categories')
                 ->select('master_lookup.value')
                 ->join('master_lookup','master_lookup.category_id','=','lookup_categories.id')
                 ->where(array('master_lookup.name'=>'Approve','lookup_categories.name'=>'Order Status'))
                 ->get();

     $approved_status=$approved_status[0]->value;
     
     return View::make('orders.view_order',compact('result','id','admin','cust_id','country','zone','date_added','customer_name','order_history','customer_details','total_master_sum','ima_id','status_update','payment_method','delivery_details','approved_status')); 
    

   }
   public function editOrder($id)
   {

    $finaldata=DB::select('select * from eseal_order_products where order_id='.$id);
    $count=count($finaldata);
       for($i=0;$i<$count;$i++)
       {
          $falg[] = $finaldata[$i]->total;  
          $tax_flag[] = $finaldata[$i]->total;  
       }
       $total_sum=array_sum($falg);
       $total_tax=array_sum($tax_flag);
    return View::make('orders.editOrder',compact('finaldata','total_sum','total_tax'));
   }
   public function updateOrder()
  {
    $pid=Input::get('pid');
    $qty=Input::get('quantity');
    $id=Input::get('order_product_id');
    
    $product_cost=$this->OrderRepo->editCart($pid);
    
    $subtotal = $qty*$product_cost[0]->price; 
    $subtax = $subtotal * ($product_cost[0]->description/100);

    $customer_cart = CustomerCart::where('id', $id)->first();
    $customer_cart->customer_id = 1;
    $customer_cart->product_id = $pid;
    $customer_cart->qty= $qty;
    $customer_cart->subtotal= $subtotal;
    $customer_cart->taxtotal= $subtax;
    $customer_cart->save();
    return 'success';
  }

  public function viewProducts($id)
  {
        
        $custArr = array();
        $finalCustArr = array();
        $customer_details = DB::table('eseal_order_products')->where('order_id','=',$id)->get();
        
        foreach($customer_details as $value)
        {         
          $sumtotal=$value->total+$value->tax;
          $custArr['product_name'] = $value->name;
          $custArr['quantity'] = $value->quantity;
          $custArr['price'] = $value->price;
          $custArr['subtotal'] = $value->total;
          $custArr['tax'] = $value->tax;
          $custArr['total'] = 'Rs'.$sumtotal;  
          
        
          
      $finalCustArr[] = $custArr;
        }
        
       return json_encode($finalCustArr);
  }
  public function viewPayments($id)
  {
        
        $custArr = array();
        $finalCustArr = array();
        $customer_details = DB::select('select order_payments.*,master_lookup.name as payment_name from order_payments
          left join master_lookup on order_payments.payment_type=master_lookup.value where order_payments.order_id='.$id);
        
        foreach($customer_details as $value)
        {         
          $payment_type=DB::select('select payment_type from order_payments where order_id='.$id);
          $payment_type=$payment_type[0]->payment_type;
          $payment_type=DB::select('select name from master_lookup where value='.$payment_type);
          $payment_type=$payment_type[0]->name;

          $custArr['sno'] = $value->id;
          $custArr['payment_type'] = $payment_type;
          $custArr['ifsc_code'] = $value->ifsc_code;
          $custArr['amount'] = $value->amount;
          $custArr['payment_date'] = $value->payment_date;
          $custArr['reference_no'] = $value->trans_reference_no;
          $custArr['actions'] ='
          <span style="padding-left:20px;" ><a data-href="/orders/paymentEdit/'. $value->id .'" data-toggle="modal" data-target="#basicvalCodeModal1"><span class="badge bg-light-blue"><i class="fa fa-pencil"></i></span></a></span>
        <span style="padding-left:10px;" ><a onclick = "deleteEntityType(' . $value->id . ')" href="javascript:void(0)"><span class="badge bg-red"><i class="fa fa-trash-o"></i></span></a></span>';
          
          
      $finalCustArr[] = $custArr;
        }
        
       return json_encode($finalCustArr);
  }

  public function paymentStore($id)
  {
    

    $cart_amount=DB::select('select sum(eseal_order_products.total) as total from eseal_orders inner join eseal_order_products on 
eseal_orders.order_id=eseal_order_products.order_id where eseal_orders.order_id='.$id);
     
    $payment_sum=DB::select('select sum(amount) as payment_sum from order_payments where order_id='.$id);
    
    $present_amount=Input::get('amount');
    
    $total_amount=$payment_sum[0]->payment_sum+$present_amount;
    
    
    $cart_amount = round($cart_amount[0]->total);
    
    if($total_amount > $cart_amount){
   
      return Response::json([
        'status' => false,
        'message'=>'Payment Exceeds Total Sum.'
      ]);
    }
    else{
     //return 'ok';
    DB::table('order_payments')->insert([
      'order_id' => $id,
      'payment_type'=>Input::get('payment_type'),
      'payment_mode'=>13,
      'trans_reference_no'=>Input::get('trans_reference_no'),
      'ifsc_code'=>Input::get('ifsc_code'),
      'amount'=>Input::get('amount'),
      'payment_date'=>Input::get('payment_date'),
      'payee_bank'=>Input::get('payee_bank')
    ]);
    
    return Response::json([
        'status' => true,
        'message'=>'Sucessfully added.'
      ]); 
  }
  }
  

  public function paymentEdit($id)
  {
    
    
    $cuser = DB::Table('order_payments')->find($id);
    
    return Response::json($cuser);
    
  }
  public function paymentUpdate($id)
  {
    DB::table('order_payments')
            ->where('id', $id)
            ->update(array('trans_reference_no' => Input::get('trans_reference_no'), 
                    'ifsc_code'=> Input::get('ifsc_code'),
                    'amount'=> Input::get('amount'),
                    'payment_date'=>Input::get('payment_date'),
                    'payee_bank'=>Input::get('payee_bank'))
                    );
     return Response::json([
        'status' => true,
        'message'=>'Sucessfully updated.'
      ]);
  }
  
  public function paymentDelete($id)
  {
    
    $order_session_id = Session::get('order_sessionid');
    $order_ima_id = Session::get('order_ima_id');
    
    DB::table('order_payments')->where('id', $id)->delete();
    //return Redirect::to('orders/viewOrder/'.$order_session_id.'/'.$order_ima_id);  
    return 1;
  }
  public function orderStatus($id)
  {
      $approve_status_id = Input::get('approve_status_id');
      $comments = Input::get('comments');
      $comments = isset($comments)?$comments:'';
    
      if(isset($approve_status_id))
      { 
        $approve_status_id = Input::get('approve_status_id');
        $order_status = 'Approved';
      }
      else
      {
        $approve_status_id = 0;
      }

      $action = Input::get('action');
      $value=Input::get('status_id');     
      if($approve_status_id!==0) 
      {     
          $value=$approve_status_id;
          $action = 'action';
      }      
     if($action=='action')
     {       
          $date = date('Y-m-d H:i:s');
          DB::table('order_history')->insert([
            'order_id' => $id,
            'order_status_id'=>$value,
            'comment'=>$comments,
            'date_added'=>$date
            ]);

          DB::table('eseal_orders')
              ->where('order_id', $id)
              ->update(array('order_status_id' => $value));


              $order_details = DB::table('eseal_orders')
                    ->select('eseal_orders.order_number','master_lookup.name','eseal_customer.brand_name','eseal_customer.email')
                    ->leftJoin('master_lookup','master_lookup.value','=','eseal_orders.order_status_id')
                    ->leftJoin('eseal_customer','eseal_customer.customer_id','=','eseal_orders.customer_id')
                    ->where('eseal_orders.order_status_id',$value)
                    ->where('eseal_orders.customer_id',Session::get('cust_temp_cust_id'))
                    ->get();

              $order_approval_mail = $order_details[0]->email;        

              \Mail::send(['html' => 'emails.order_status'], array('order_number' => $order_details[0]->order_number, 'username' => $order_details[0]->brand_name,'order_status'=> $order_details[0]->name), function($message) use ($order_approval_mail) 
             {        
                        $message->to($order_approval_mail)->subject('Order Status.'); 
             });

              
          return Redirect::to('orders/customerIma')->withFlashMessage('Your Order has been '.$order_details[0]->name.'  Successfully.');
      }
      if(Input::get('redirect')=='redirect')
      {
        return Redirect::to('orders/customerIma')->withFlashMessage('Group Created Successfully.');
      }
  }
    public function customerIma()
    {
        $user_id = Session::get('userId');
        $user_details=$this->custRepo->getUserDetails($user_id);
        $cust_id=$user_details[0]->customer_id?$user_details[0]->customer_id:0;
        
        $customer_ima=DB::select('select date(start_date) as start_date,date(end_date) as end_date,ima_id,datediff(end_date,start_date)  as estimated_period from customer_ima where customer_id="'.$cust_id.'"order by ima_id desc');  

        $iot_type=DB::select('select value from master_lookup where name="SCO IOT-order"');
        $iot_type=$iot_type[0]->value; 
       
        $columns='order_status_id,count(*) as count,master_lookup.name';
        $leftjoin='master_lookup on eseal_orders.order_status_id=master_lookup.value';
        if(!empty($cust_id))
        {
           $where='eseal_orders.customer_id="'.$cust_id.'" and eseal_orders.order_type="'.$iot_type.'"';
        }
        else
        {
          $where='eseal_orders.order_type="'.$iot_type.'"';
        }
        
        $placed=DB::select('SELECT'.' '.$columns.' '.'FROM eseal_orders left join'.' '.$leftjoin.' '.'where'.' '.$where.' '.'and master_lookup.name="Placed"');
        
  $Approved=DB::select('SELECT'.' '.$columns.' '.'FROM eseal_orders left join'.' '.$leftjoin.' '.'where'.' '.$where.' '.'and master_lookup.name="Approve"');
        
        $Delivered=DB::select('SELECT'.' '.$columns.' '.'FROM eseal_orders left join'.' '.$leftjoin.' '.'where'.' '.$where.' '.'and master_lookup.name="Delivered"');

        
        $allorders=$placed[0]->count+$Approved[0]->count+$Delivered[0]->count;

        $placed=$placed[0]->count;
        $approved=$Approved[0]->count;
        $delivered=$Delivered[0]->count;

        return View::make('orders.customer_ima',compact('customer_ima','allorders','placed','approved','delivered'));      
  }


public function getGds()
{
        $user_id = Session::get('userId');
        $user_details=$this->custRepo->getUserDetails($user_id);
        $cust_id=$user_details[0]->customer_id;
        $custArr = array();
        $finalCustArr = array();
       
        $customer_details = $this->custRepo->getGdsOrders($cust_id);
       
       foreach($customer_details as $value)
        {         
          $date=new DateTime($value->date_added);
          $date_added=$date->format('Y-m-d');
          if($value->status==1)
          $status = 'Active';
          else
          $status = 'In-Active';
         
          if(empty($value->customer_id)){
          $custArr['customer_name'] = $value->payment_firstname;
          }
          else{
            $custArr['customer_name'] = $value->brand_name;
          }
          $custArr['date_added'] = $date_added;
          $custArr['bill_to_name'] = $value->payment_firstname;
          $custArr['ship_to_name'] = $value->shipping_firstname;
          $custArr['order_status'] = $value->name;
          $custArr['actions'] = '<span style="padding-left:20px;" ><a href="/orders/viewOrder/'.$value->order_id."/".'0'.'">'.$value->order_number.'</a></span><span style="padding-left:50px;" ></span>';
          
      $finalCustArr[] = $custArr;
        }
       
       return json_encode($finalCustArr);
}
public function editmyOrder($id,$ima_id)
{

     $columns='eseal_orders.*,eseal_customer.*,master_lookup.name,date(eseal_orders.date_added) as dateadded,date(eseal_orders.date_modified) as datemodified';
     
     $leftJoin='eseal_customer on eseal_orders.customer_id=eseal_customer.customer_id left join master_lookup
       on eseal_orders.order_status_id=master_lookup.value';
     
     $where='eseal_orders.order_id="'.$id.'"';

     $where_purchased='order_id="'.$id.'"';
     
     if(!empty($ima_id)){
       $where .= 'and eseal_orders.ima_id='.$ima_id; 
       $where_purchased .='and ima_id='.$ima_id;
     }
     
     $result=DB::select('SELECT'.' '.$columns.' '.'FROM eseal_orders left join'.' '.$leftJoin.' '.'where'.' '.$where);
    
     $purchased_date=DB::select('SELECT date(date_added) as date_added FROM eseal_orders WHERE' .' '.$where_purchased);

    
     $date=new DateTime($purchased_date[0]->date_added);
     $date_added=$date->format('Y-m-d');
     Session::put('order_sessionid', $id);
     Session::put('order_ima_id', $ima_id);

      $user_id = Session::get('userId');
      $user_details=$this->custRepo->getUserDetails($user_id);
      $cust_id=$user_details[0]->customer_id;
    
     $lookup_id=DB::select('select id from lookup_categories where name="Order Status"');
     $lid=$lookup_id[0]->id;
     $admin=DB::select('select name,value from master_lookup where category_id='.$lid);
      
     $country_id=$result[0]->payment_country_id;
     $zone_id=$result[0]->payment_zone_id;
     
     if(!empty($country_id))
     {
     $country_name=DB::select('select name from countries where country_id='.$country_id);
     $country=$country_name[0]->name;
     }
     else{
      $country='';
     }    
     if(!empty($zone_id))
     {
     $zone_name=DB::select('select name from zone where zone_id='.$zone_id);
     $zone=$zone_name[0]->name;
     }
    else{
      $zone='';
    }
     
     $customer_name=DB::table('eseal_customer')->where('customer_id',$result[0]->customer_id)->pluck('brand_name');
     
     $order_status_id=$result[0]->order_status_id;
     
    $custArr = array();
    $finalCustArr = array();
    $pname = 'IoT';
    $customer_details = DB::table('eseal_order_products')
                          ->select('eseal_order_products.*','locations.*','eseal_customer.firstname as custfname','eseal_customer.lastname as custlname')
                          ->leftJoin('locations','locations.location_id','=','eseal_order_products.vendor_id')
                          ->leftJoin('eseal_customer','eseal_customer.customer_id','=','eseal_order_products.customer_id')
                          ->where('eseal_order_products.order_id','=',$id)
                          ->where('eseal_order_products.name','=',$pname)
                          ->get();
      // $total_prod_quantity = DB::Table('eseal_order_products')
      //                           ->select('sum(quantity) as total_quantity')
      //                           ->where('eseal_order_products.order_id','=',$id)
      //                           ->where('eseal_order_products.name','=',$pname)
      //                           ->get();
     $total_prod_quantity=DB::select('select sum(quantity) as total_quantity from eseal_order_products where order_id='.$id);
     
     $sum=DB::select('select sum(total) as sum_total,sum(tax) as tax_total from eseal_order_products where order_id='.$id);
     $total_master_sum=$sum[0]->sum_total+$sum[0]->tax_total;
     $order_history=DB::select('select master_lookup.*,order_history.*,date(order_history.date_added) as dateadded from order_history inner join master_lookup on order_history.order_status_id=master_lookup.value');

     $vendors=DB::table('locations as loc')->leftjoin('location_types as loc_type','loc_type.location_type_id','=','loc.location_type_id')
              ->where('loc_type.location_type_name','Vendor')
              ->where('loc.manufacturer_id',$customer_details[0]->customer_id)
              ->select('loc.location_name','loc.location_id')->get();
     
    if(!empty($vendors))
    {
      foreach ($vendors as $key=>$itm) 
      {
        $array_circle1[] = array('id' => $itm->location_id, 'name' => $itm->location_name);
      }
    
      $vendors_array = json_encode($array_circle1);
    }
    else
    {
       $vendors_array='';
    }

    $delivery_mode=DB::table('lookup_categories')->where('name','IoT Delivery Modes')->get();
    $delivery_id=$delivery_mode[0]->id;
    $delivery_master_lookup=DB::table('master_lookup')->where('category_id',$delivery_id)->get();
    foreach ($delivery_master_lookup as $key=>$itm) 
    {
        $array_circle[] = array('id' => $itm->id, 'name' => $itm->name);
    }

     parent::Breadcrumbs(array('Home'=>'/','Edit Order'=>'#')); 
     $pname=DB::select('select name from eseal_order_products where order_id='.$id);
     $pname=$pname[0]->name;
     
     return View::make('orders.editmyOrder',compact('result','id','admin','cust_id','country','zone','date_added','customer_name','order_history','customer_details','total_master_sum','ima_id','pname','delivery_details','total_prod_quantity','array_circle1','array_circle'));  
}

public function updatemyOrder($ima_id,$id){
    
    $payment_firstname=Input::get('payment_firstname');
    $payment_lastname=Input::get('payment_lastname');
    $payment_address_1=Input::get('payment_address_1');
    $payment_city=Input::get('payment_city');
    $shipping_firstname=Input::get('shipping_firstname');
    $shipping_lastname=Input::get('shipping_lastname');
    $shipping_address_1=Input::get('shipping_address_1');
    $shipping_city=Input::get('shipping_city');
    

     DB::table('eseal_orders')
            ->where('order_id', $id)
            ->update(array('payment_firstname' => $payment_firstname,'payment_lastname'=>$payment_lastname,
             'payment_city'=>$payment_city,'payment_address_1'=>$payment_address_1,'shipping_firstname'=>$shipping_firstname,'shipping_lastname'=>$shipping_lastname,
              'shipping_address_1'=>$shipping_address_1,'shipping_city'=>$shipping_city));
   
    return Redirect::to('orders/editmyOrder/'.$id.'/'.$ima_id);

   
}
public function printInvoice($id,$ima_id,$print_invoice){
     
     $columns='eseal_orders.*,eseal_customer.*,master_lookup.name,date(eseal_orders.date_added) as dateadded,date(eseal_orders.date_modified) as datemodified';
     
     $leftJoin='eseal_customer on eseal_orders.customer_id=eseal_customer.customer_id left join master_lookup
       on eseal_orders.order_status_id=master_lookup.value';
     
     $where='eseal_orders.order_id="'.$id.'"';

     $where_purchased='order_id="'.$id.'"';
     
     if(!empty($ima_id)){
     $where .= 'and eseal_orders.ima_id='.$ima_id; 
     $where_purchased .='and ima_id='.$ima_id;
     }

     $customer_details = DB::table('eseal_order_products')->where('order_id','=',$id)->get();

     if($customer_details[0]->name=="IoT"){

        $delivery_details = DB::table('eseal_order_products')
                          ->select('eseal_order_products.*','locations.*','eseal_customer.firstname as custfname','eseal_customer.lastname as custlname')
                          ->leftJoin('locations','locations.location_id','=','eseal_order_products.vendor_id')
                          ->leftJoin('eseal_customer','eseal_customer.customer_id','=','eseal_order_products.customer_id')
                          ->where('order_id','=',$id)
                          ->get();
     }
     
     $result=DB::select('SELECT'.' '.$columns.' '.'FROM eseal_orders left join'.' '.$leftJoin.' '.'where'.' '.$where);
    
     $purchased_date=DB::select('SELECT date(date_added) as date_added FROM eseal_orders WHERE' .' '.$where_purchased);

     $date=new DateTime($purchased_date[0]->date_added);
     $date_added=$date->format('Y-m-d');
     Session::put('order_sessionid', $id);
     Session::put('order_ima_id', $ima_id);

      $user_id = Session::get('userId');
      $user_details=$this->custRepo->getUserDetails($user_id);
      $cust_id=$user_details[0]->customer_id;
    
     $lookup_id=DB::select('select id from lookup_categories where name="Order Status"');
     $lid=$lookup_id[0]->id;
     $admin=DB::select('select name,value from master_lookup where category_id='.$lid);
     
     $country_id=$result[0]->payment_country_id;
     $zone_id=$result[0]->payment_zone_id;
     
     if(!empty($country_id))
     {
     $country_name=DB::select('select name from countries where country_id='.$country_id);
     $country=$country_name[0]->name;
     }
     else{
      $country='';
     }    
     if(!empty($zone_id))
     {
     $zone_name=DB::select('select name from zone where zone_id='.$zone_id);
     $zone=$zone_name[0]->name;
     }
    else{
      $zone='';
    }
     
     $customer_name=DB::table('eseal_customer')->where('customer_id',$result[0]->customer_id)->pluck('brand_name');
     
    $order_status_id=$result[0]->order_status_id;
    $custArr = array();
    $finalCustArr = array();
    $customer_details = DB::table('eseal_order_products')->where('order_id','=',$id)->get();
    
     $sum=DB::select('select sum(total) as sum_total,sum(tax) as tax_total from eseal_order_products where order_id='.$id);
     $total_master_sum=$sum[0]->sum_total+$sum[0]->tax_total;
     $order_history=DB::select('select master_lookup.*,order_history.*,date(order_history.date_added) as dateadded from order_history inner join master_lookup on order_history.order_status_id=master_lookup.value');
     parent::Breadcrumbs(array('Home'=>'/','Edit Order'=>'#')); 
     
     $payment_id= $result[0]->payment_method;
     $payment_method=DB::select('select name from master_lookup where value='.$payment_id);
     $payment_method=$payment_method[0]->name;
     
     return View::make('orders.print_invoice',compact('result','id','admin','cust_id','country','zone','date_added','customer_name','order_history','customer_details','total_master_sum','ima_id','print_invoice','payment_method','delivery_details'));
  
}
  public function Gds()
  {
      $user_id = Session::get('userId');
      $user_details=$this->custRepo->getUserDetails($user_id);
      $cust_id=$user_details[0]->customer_id;
      
      parent::Breadcrumbs(array('Home'=>'/','Orders'=>'#'));
      return View::make('orders.gds',compact('customer_ima'));
  }
  public function Aidc()
  {
      $user_id = Session::get('userId');
      $user_details=$this->custRepo->getUserDetails($user_id);
      $cust_id=$user_details[0]->customer_id;
      if(!empty($cust_id))
      {
          $customer_ima=DB::select('select date(start_date) as start_date,date(end_date) as end_date,ima_id,datediff(end_date,start_date)  as estimated_period from customer_ima where customer_id="'.$cust_id.'"order by ima_id desc');
      }
      else
      {
          $customer_ima=DB::select('select date(start_date) as start_date,date(end_date) as end_date,ima_id,datediff(end_date,start_date)  as estimated_period from customer_ima order by ima_id desc'); 
      }    
       $iot_type=DB::table('master_lookup')
              ->where('name',"SCO IOT-order")
              ->select('value')
              ->get();
    
     $iot_type=$iot_type[0]->value;
    
     $aidc_type=DB::table('master_lookup')
              ->where('name',"SCO AIDC-order")
              ->select('value')
              ->get();

     $aidc_type=$aidc_type[0]->value;
      
      $columns='order_status_id,count(*) as count,master_lookup.name';
      $leftjoin='master_lookup on eseal_orders.order_status_id=master_lookup.value';
      $where='eseal_orders.order_type="'.$aidc_type.'"';
      
      if(!empty($cust_id))
      {   
          $placed_aidc = DB::select('SELECT'.' '.$columns.' '.'FROM eseal_orders left join'.' '.$leftjoin.' '.'where'.' '.$where.' '.'and eseal_orders.customer_id="'.$cust_id.'" and master_lookup.name="Placed"');
    
          $Approved_aidc = DB::select('SELECT'.' '.$columns.' '.'FROM eseal_orders left join'.' '.$leftjoin.' '.'where'.' '.$where.' '.'and eseal_orders.customer_id="'.$cust_id.'"  and master_lookup.name="Approve"');
          
          $Delivered_aidc = DB::select('SELECT'.' '.$columns.' '.'FROM eseal_orders left join'.' '.$leftjoin.' '.'where'.' '.$where.' '.'and eseal_orders.customer_id="'.$cust_id.'"  and master_lookup.name="Delivered"');
  
          $allorders_aidc=$placed_aidc[0]->count+$Approved_aidc[0]->count+$Delivered_aidc[0]->count;
         
          $placed_aidc=$placed_aidc[0]->count;
          $approved_aidc=$Approved_aidc[0]->count;
          $delivered_aidc=$Delivered_aidc[0]->count;
          
          return View::make('orders.aidc',compact('allorders_aidc','placed_aidc','approved_aidc','delivered_aidc'));
      }
      else
      {
           $placed_aidc = DB::select('SELECT'.' '.$columns.' '.'FROM eseal_orders left join'.' '.$leftjoin.' '.'where'.' '.$where.' '.'and master_lookup.name="Placed"');
    
          $Approved_aidc = DB::select('SELECT'.' '.$columns.' '.'FROM eseal_orders left join'.' '.$leftjoin.' '.'where'.' '.$where.' '.'and master_lookup.name="Approve"');
          
          $Delivered_aidc = DB::select('SELECT'.' '.$columns.' '.'FROM eseal_orders left join'.' '.$leftjoin.' '.'where'.' '.$where.' '.'and master_lookup.name="Delivered"');

          $allorders_aidc=$placed_aidc[0]->count+$Approved_aidc[0]->count+$Delivered_aidc[0]->count;
        
          $placed_aidc=$placed_aidc[0]->count;
          $approved_aidc=$Approved_aidc[0]->count;
          $delivered_aidc=$Delivered_aidc[0]->count;

          parent::Breadcrumbs(array('Home'=>'/','Orders'=>'#'));
          return View::make('orders.aidc',compact('allorders_aidc','placed_aidc','approved_aidc','delivered_aidc'));
      }
  }
  public function getVendorAddress()
  {
      $vendor_id = Input::get('location_id');
      $vendors=DB::table('locations as loc')
            ->leftjoin('location_types as loc_type','loc_type.location_type_id','=','loc.location_type_id')
              ->where('loc_type.location_type_name','=','Vendor')
              ->where(array('loc.location_id'=>$vendor_id))
              ->select('loc.firstname','loc.lastname','loc.location_email','loc.state','loc.region','loc.city','loc.country','loc.phone_no')->get();
      $dbp = DB::getQueryLog();
      /*return end($dbp);*/
     $vendor_details = '<div>'.$vendors[0]->firstname.'</div><div>'.$vendors[0]->lastname.'</div><div>'.$vendors[0]->location_email.'</div><div>'.$vendors[0]->region.'</div>'.$vendors[0]->city.'</div><div>'.$vendors[0]->country.'</div>'.$vendors[0]->phone_no;
      return $vendor_details;
  }

  public function addEditOrderIotCodes()
     {
        $data = Input::get();
        $cid = Session::get('cust_temp_cust_id');
        //return  'jjj';
        if($data['vendorid']!=0)
        {
              $validate_vendor = DB::table('eseal_order_products')
                              ->where('order_id',$data['order_id'])
                              ->where('customer_id',$data['customer_id'])
                              ->where('vendor_id',$data['vendorid'])
                              ->get();
              if($validate_vendor)
                return '1';
              else
              {
                 $vendor_name = DB::table('locations')->select('locations.location_name')->where('location_id',$data['vendorid'])->get();

                 $pid = DB::table('customer_products_plans')
                        ->select('customer_products_plans.*')
                        ->where(array('customer_products_plans.customer_id'=>$data['customer_id'],'customer_products_plans.name'=>'IoT'))
                        ->get();

                $tax=DB::table('customer_products_plans as cc')
                      ->leftJoin('master_lookup as ml','ml.value','=','cc.tax_class_id')
                      ->select('ml.description as tax')
                      ->where(array('cc.customer_id'=>$data['customer_id'],'cc.name'=>'IoT'))
                      ->get();

                  if(empty($tax[0]->tax))
                  {  
                    $tax[0]->tax = 0; 
                  }
                $total_price = $data['total_codes'] * $pid[0]->price;
                $tax_paid = ($total_price * ($tax[0]->tax/100));
                $suborder_part = DB::table('eseal_orders')
                            ->select('eseal_orders.order_number')
                            ->where(array('order_id'=>$data['order_id'],'customer_id'=>$data['customer_id']))
                            ->get();
                
                $suborder = $suborder_part[0]->order_number.'_'.$data['customer_id']; 

                $data_added = DB::Table('eseal_order_products')
                              ->insert(array(
                                     'order_id' => $data['order_id'],
                                     'pid' => $pid[0]->customer_product_plan_id,
                                     'name' => $pid[0]->name,
                                     'quantity' => $data['total_codes'],
                                     'price' => $pid[0]->price,
                                     'total' => $total_price,
                                     'tax' => $tax_paid,
                                     'delivery_mode' => $data['deliveryname'],
                                     'delivery_mode_id' => $data['deliveryid'],
                                     'sub_order_id' => $suborder,
                                     'customer_id' => $data['customer_id'],
                                     'delivery_to' => $vendor_name[0]->location_name,
                                     'vendor_id' => $data['vendorid'],
                                     'vendor_flag' => 0  
                                     ));
                    if($data_added)
                    {                       
                         $orders=DB::Table('eseal_orders')->select('order_number','payment_firstname')->where('order_id',$data['order_id'])->get();
                         $customer_details = $this->custRepo->getAllCustomers($cid);
                         $custMail = $customer_details[0]->email;
                         $brand_name = $customer_details[0]->brand_name;
                         $eseal_order_products=EsealOrderProducts::where('order_id',$data['order_id'])->get();

                         \Mail::send(['html' => 'emails.orders'], array('order_number' => $orders[0]->order_number, 'username' => $orders[0]->payment_firstname,'eseal_order_products'=> $eseal_order_products,'vendorFlag'=>0), 
                          function($message) use ($custMail)
                         {        
                                    $message->to($custMail)->subject('Your eSeal(IoT) order has been updated.');
                         });
                        $mailids = array();
                        foreach ($eseal_order_products as $esp) 
                        {
                          $esp['vendor_id'] = isset($esp['vendor_id'])?$esp['vendor_id']:0;
                          //return $esp['vendor_id'];
                          if($esp['vendor_id']!=0)
                          {
                              $temp = DB::table('locations')->select('location_email')->where(array('manufacturer_id' => $esp['customer_id'],'location_id'=> $esp['vendor_id']))->get();
                              //return $cid;
                              $mailids[] = $temp[0]->location_email;
                          }    
                        }
                        \Mail::send(['html' => 'emails.orders'], array('order_number' => $orders[0]->order_number, 'username' => $orders[0]->payment_firstname,'eseal_order_products'=> $eseal_order_products,'vendorFlag'=>1,'custName'=>$customer_details[0]->brand_name), 
                          function($message) use ($mailids,$custMail,$brand_name) 
                         {        
                                    $message->to($custMail)->bcc($mailids)->subject('Order from '.$brand_name);
                         });
                         $totalQuantity = $this->getPresentQuantity($data['order_id']);
                         //return 2;//
                         return '2'.'-'.$totalQuantity;
                    }
                    else
                    {
                      return 0;
                    }                    
                }
        }
        else
        {
            $validate_vendor = DB::table('eseal_order_products')            
                    ->select('quantity')
                    ->where(array('customer_id'=>$data['customer_id'],'order_id'=>$data['order_id'],'vendor_id'=>$data['vendorid'],'delivery_mode'=>$data['deliveryname']))
                    ->get();

                     
            if($validate_vendor)
            {                    
                $pquantity = $data['total_codes'] + $validate_vendor[0]->quantity;
                $data_added = DB::Table('eseal_order_products')
                        ->where(array('customer_id'=>$data['customer_id'],'order_id'=>$data['order_id'],'vendor_id'=>$data['vendorid'],'delivery_mode'=>$data['deliveryname']))
                        ->update(array('quantity' => $pquantity));

            //$vq = DB::getQueryLOg();
                      //return $vq;
                  if($data_added)
                  {
                     $totalQuantity = $this->getPresentQuantity($data['order_id']);
                     //return 2;//
                     return '2'.'-'.$totalQuantity;
                  }else{
                    return 0;
                  }
             }               
            else
            {
                $pid = DB::table('customer_products_plans')
                        ->select('customer_products_plans.*')
                        ->where(array('customer_products_plans.customer_id'=>$data['customer_id'],'customer_products_plans.name'=>'IoT'))
                        ->get();

                
                $tax=DB::table('customer_products_plans as cc')
                      ->leftJoin('master_lookup as ml','ml.value','=','cc.tax_class_id')
                      ->select('ml.description as tax')
                      ->where(array('cc.customer_id'=>$data['customer_id'],'cc.name'=>'IoT'))
                      ->get();

                if(empty($tax[0]->tax))
                  {  
                    $tax[0]->tax = 0; 
                  }
                
                $total_price = $data['total_codes'] * $pid[0]->price;

                $tax_paid = ($total_price * ($tax[0]->tax/100));
                $suborder_part = DB::table('eseal_orders')
                            ->select('eseal_orders.order_number')
                            ->where(array('order_id'=>$data['order_id'],'customer_id'=>$data['customer_id']))
                            ->get();
               $suborder = $suborder_part[0]->order_number.'_'.$data['customer_id'];
                   $data_added = DB::Table('eseal_order_products')
                              ->insert(array(
                                     'order_id' => $data['order_id'],
                                     'pid' => $pid[0]->customer_product_plan_id,
                                     'name' => $pid[0]->name,
                                     'quantity' => $data['total_codes'],
                                     'price' => $pid[0]->price,
                                     'total' => $total_price,
                                     'tax' => $tax_paid,
                                     'delivery_mode' => $data['deliveryname'],
                                     'delivery_mode_id' => $data['deliveryid'],
                                     'sub_order_id' => $suborder,
                                     'customer_id' => $data['customer_id'],
                                     'vendor_id' => $data['vendorid'],
                                     'vendor_flag' => 0  
                                     ));
                  if($data_added)
                  {
                     //return 2;
                      $totalQuantity = $this->getPresentQuantity($data['order_id']);
                         //return 2;//
                      return '2'.'-'.$totalQuantity;
                  }
                  else
                  {
                    return 0;
                  }
                
            }
           \Mail::send(['html' => 'emails.orders'], array('order_number' => $orders[0]->order_number, 'username' => $orders[0]->payment_firstname,'eseal_order_products'=> $eseal_order_products,'vendorFlag'=>0), 
            function($message) use ($custMail)
           {        
                      $message->to($custMail)->subject('Your eSeal(IoT) order has been updated.');
           });
          }
      }

  public function showEditOrderIotCodes($orderid,$custid)
  {
        $iotArr = array();
        $finaliotArr = array();
        $iot_details = DB::table('eseal_order_products')
                            ->select('eseal_order_products.*')
                            ->where('eseal_order_products.order_id','=',$orderid)
                            ->where('eseal_order_products.customer_id','=',$custid)
                            ->get();
                                
        foreach($iot_details as $value)
        {         
          $iotArr['id'] = $value->order_product_id;
          $iotArr['order_id'] = $value->order_id;
          $iotArr['customer_id'] = $value->customer_id;
          $iotArr['delivery_mode'] = $value->delivery_mode;
          if($value->delivery_mode=='Print and Deliver' || $value->delivery_mode=='Downloadable')
             $iotArr['vendor'] = 'Not Applicable';
          else
            $iotArr['vendor'] = $value->delivery_to;
          $iotArr['quantity'] = $value->quantity;
          $iotArr['actions'] ='
          <span style="padding-left:20px;" ><a data-href="/orders/editorderiotcodes/' . $value->order_product_id . '" onclick = "getOrderProductId(' . $value->order_product_id . ','.$value->order_id.')" data-toggle="modal" data-target="#basicvalCodeModal"><span class="badge bg-light-blue"><i class="fa fa-pencil"></i></span></a></span><span style="padding-left:10px;" ></span>
            <span style="padding-left:10px;" ><a onclick = "deleteIot(' . $value->order_product_id . ','.$value->order_id.')"><span class="badge bg-red"><i class="fa fa-trash-o"></i></span></a></span>';
          $finaliotArr[] = $iotArr;
        }
        
       return json_encode($finaliotArr);
  }

  public function editOrderIotCodes($id)
    {
        $iot = DB::table('eseal_order_products')->where('order_product_id', $id)->first();
        // echo "<pre>";print_r($iot);die;
        return Response::json($iot);
    }

  public function updateEditOrderIotCodes($id)
  {
      $data = Input::get();
      if(isset($data))
      {
        
        $price=DB::table('eseal_order_products')->where('order_product_id','=',$data['order_product_id'])->pluck('price');
        
        $total=$price*$data['quantity'];
        
        DB::table('eseal_order_products')->where('order_product_id',$data['order_product_id'])->update(array('quantity'=>$data['quantity'],'total'=>$total));
        
        $total_order=DB::table('eseal_order_products')->where('order_id','=',$data['id'])->pluck(DB::raw('sum(quantity) as quantity'));     
        
        DB::table('eseal_orders')->where('order_id',$data['id'])->update(array('total'=>$total_order));
        
        return $total_order;
      }
      /*$data = Input::get();
      $cid = Session::get('cust_temp_cust_id');
      if($data['vendorid']!=0)
      {
          $validate_vendor = '';
          if($data['vendor_idValidate']!=$data['vendorid'])
          {
              $validate_vendor = DB::table('customer_iot_cart')
                            ->where(array('vendor_id'=>$data['vendorid'],'customer_id'=>$cid))
                           ->get();
          }
          if($validate_vendor)
              return '1';
          else
          {
               $availQuantity = $this->getQuantity();
               if($availQuantity < $data['total_codes'] )
               {

               } 
               $data_added = DB::Table('customer_iot_cart')
                            ->where('id', Input::get('id'))
                            ->update(array('delivery_mode_id' => Input::get('deliveryid'),
                             'vendor_id' => Input::get('vendorid'), 
                             'quantity' => Input::get('total_codes'),  
                             'delivery_mode' => Input::get('deliveryname'),                              
                             'vendor_name' => Input::get('vendor_name')                         
                          ));
              if($data_added)
              {
                 $availQuantity = $this->getQuantity();
                  return '2'.'-'.$availQuantity;
              }
              else
              {
                return '0';
              }                    
            }
      }else
      {
              
             $validate_vendor = DB::table('customer_iot_cart')            
                      ->select('quantity')
                      ->where(array('delivery_mode_id'=>$data['deliveryid'],'customer_id'=>$cid))
                      ->get();
            if($validate_vendor)
            {    
                    $editIdQuantity = DB::table('customer_iot_cart')            
                      ->select('quantity')
                      ->where(array('id'=>$data['id'],'customer_id'=>$cid))
                      ->get();
                  if($editIdQuantity)
                  {
                       $prev_quantity = $editIdQuantity[0]->quantity - $data['total_codes'];
                  }
                  else
                  {
                    $prev_quantity = $data['total_codes'];
                  }
                      $pres_quantity = $data['total_codes']+ $validate_vendor[0]->quantity;

                     $data_added = DB::Table('customer_iot_cart')
                            ->where(array('delivery_mode_id'=>Input::get('deliveryid'),'customer_id'=>$cid))
                            ->update(array('delivery_mode_id'=>$data['deliveryid'],'vendor_id' => Input::get('vendorid'),'delivery_mode'=>$data['deliveryname'], 
                             'quantity' => $pres_quantity,                              
                             'vendor_name' => ''                         
                          ));
                      if($prev_quantity==0)
                    {
                        DB::Table('customer_iot_cart')
                          ->where(array('customer_id'=>$cid,'id'=>$data['id']))
                          ->delete();
                    }
                    else
                    {
                      $data_added_prev = DB::Table('customer_iot_cart')
                            ->where(array('id'=>Input::get('id'),'customer_id'=>$cid))
                            ->update(array('quantity' => $prev_quantity,                              
                             'vendor_name' => ''                         
                          ));
                    }
                     // DB::table('customer_iot_cart')->where(array('id'=>Input::get('id'),'customer_id'=>$cid))->delete();

                    $vq = DB::getQueryLOg();
                   
                    //return end($vq);
                  if($data_added)
                  {
                      $availQuantity = $this->getQuantity();
                      return '2'.'-'.$availQuantity;
                  }
                  else
                  {
                    return '0';
                  }
             }               
              else
              {  
                  $editIdQuantity = DB::table('customer_iot_cart')            
                      ->select('quantity')
                      ->where(array('id'=>$data['id'],'customer_id'=>$cid))
                      ->get();
                  if($editIdQuantity)
                  {
                       $prev_quantity = $editIdQuantity[0]->quantity - $data['total_codes'];
                  }
                  else
                  {
                    $prev_quantity = $data['total_codes'];
                  }
                  $pres_quantity = $data['total_codes'];
                 
                  $data_added = DB::Table('customer_iot_cart')
                              ->insert(array('customer_id' => $cid,
                                     'delivery_mode_id' => $data['deliveryid'],
                                     'delivery_mode' => $data['deliveryname'], 
                                     'vendor_id' => $data['vendorid'], 
                                     'vendor_name' => '', 
                                     'quantity' => $data['total_codes'] 
                                     ));
                  
                    if($prev_quantity==0)
                    {
                        DB::Table('customer_iot_cart')
                          ->where(array('customer_id'=>$cid,'id'=>$data['id']))
                          ->delete();
                    }
                    else
                    {
                     $data_added_prev = DB::Table('customer_iot_cart')
                            ->where(array('id'=>Input::get('id'),'customer_id'=>$cid))
                            ->update(array('quantity' => $prev_quantity,                              
                             'vendor_name' => ''                         
                          ));
                    }
                    

                  if($data_added)
                  {
                     $availQuantity = $this->getQuantity();
                    return '2'.'-'.$availQuantity;
                  }
                  else
                  {
                    return '0';
                  }
                
            }                   

            
           }*/

  }  

  public function deleteEditOrderIotCodes($id,$orderid)
  {
        $deleted =  DB::Table('eseal_order_products')->where('order_product_id', '=', $id)->delete();
        if($deleted)
        {               
            $totalQuantity = $this->getPresentQuantity($orderid);
            DB::table('eseal_orders')->where('order_id',$orderid)->update(array('total'=>$totalQuantity));
             //return 2;//
            return '1'.'-'.$totalQuantity;
            /*return 1;*/
        }
        else
        {
          return 0; 
        }
  }

  public function getEditedQuantity($id)
   {
        $finaldata=$this->OrderRepo->checkOut(Session::get('cust_temp_cust_id'),Session::get('ima_id'),Session::get('iot_id')); 

        $iotquantity = DB::select('select order_id, sum(quantity) as cqty from eseal_order_products where customer_id='.Session::get('cust_temp_cust_id').' group by customer_id ');

        if(empty($iotquantity))
            $iotquantity = 0;
        else
          $iotquantity = $iotquantity[0]->cqty;
        $finaldata[0]->qty = ($finaldata[0]->qty - $iotquantity);
        return $finaldata[0]->qty;
   }

}