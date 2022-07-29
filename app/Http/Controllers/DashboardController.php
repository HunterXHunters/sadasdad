<?php
namespace App\Http\Controllers;
use App\Models\Products;
use Maatwebsite\Excel\Facades\Excel;
set_time_limit(0);
ini_set('memory_limit', '-1');

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
use App\Repositories\RoleRepo;
use App\Repositories\CustomerRepo;
use App\Repositories\OrderRepo;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

use Illuminate\Support\Facades\Input;

use Session;
use DB;
use View;

//use View;
use Validator;
use Redirect;
use Log;
use Exception;

Class DashboardController extends BaseController{
    
    var $OrderObj; 
    var $custRepoObj;
    public $_request;
    public function __construct(OrderRepo $OrderObj, CustomerRepo $custRepoObj,Request $request) {
        $this->OrderObj = $OrderObj;
        $this->custRepoObj = $custRepoObj;
        $this->_request=$request;
    }
    
    public function orders()
    
  
    {
    parent::Breadcrumbs(array('Home' => '/', 'Dashboard Orders' => '#'));
        $data = Input::get();
        if(!empty($data))
        {
            $orders =  $this->OrderObj->getOrders($data);
        }else{
            $orders = $this->OrderObj->getOrders();
        }
        //print_r( $orders); die;
        
        $OStatus = $this->OrderObj->orderStatus();
        if(Session::get('cusotmerId')==0)
        {
            $cusotmers = $this->custRepoObj->getAllCustomers();
        }
        
        //echo "<pre>";        print_r($orders); die;
        
        return View::make('dashboard.orders')->with(array('OStatus'=>$OStatus,'customers'=>$cusotmers,'orders'=>$orders,'row'=>$data));
        
    }

  public function test()
{
Log::info('We are in '.__METHOD__);
  $user_id = Session::get('userId');
Log::info('User Id : '.$user_id);
  
  $user_details=DB::table('users')->where('user_id','=',$user_id)->get();
  
  //return $user_id;
  $users_token=DB::select('select * from users_token where user_id='.$user_id);
  if(!empty($users_token)){
  $module_id=$users_token[0]->module_id;
  $access_token=$users_token[0]->access_token; 
  }
  else{
    $module_id='';
    $access_token=''; 
  }
  //$baseurl= $_SERVER['SERVER_NAME'];;
  //return $baseurl;
  $cust_id=$user_details[0]->customer_id;
  if(empty($cust_id)){
  $locations=DB::select('select locations.location_id, locations.location_name '
          . 'from locations '
          . 'join location_types on location_types.location_type_id = locations.location_type_id '
          . 'where location_types.location_type_name != "Customer"');
  $last = DB::getQueryLog();
//  echo "<pre>";print_r(end($last));die;
  Log::info(end($last));
  $products=DB::select('select product_id, name from products');
  }
  else{
      $locations=DB::select('select locations.location_id, locations.location_name '
          . 'from locations '
          . 'join location_types on location_types.location_type_id = locations.location_type_id '
          . 'where locations.manufacturer_id = '.$cust_id.' and location_types.location_type_name != "Customer" ');
//   $locations = DB::select('select location_id, location_name, location_type_id from locations where manufacturer_id='.$cust_id.' and location_type_id != 874');
   $last = DB::getQueryLog();
//  echo "<pre>";print_r(end($last));die;
   Log::info(end($last));
   $products = DB::select('select product_id, name from products where manufacturer_id='.$cust_id); 
  }
  

  \Log::info(DB::getQueryLog());
  return View::make('dashboard.inventoryreport',compact('locations', 'products', 'module_id', 'access_token'));
}

    public function getTableName($userId)
    {
        $tableName = 'eseal_0';
        $manufacturerId = 0;
        if($userId > 0)
        {
            $manufacturerId = DB::table('users')->where('user_id', $userId)->pluck('customer_id');
        }
        $tableName = 'eseal_'.$manufacturerId;
        return $tableName;
    }


    public function getTotal()
{
   $location_id=Input::get('location_id');
   $product_id=Input::get('product_id');
   $user_id = Session::get('userId');
   $user_details=DB::table('users')->where('user_id','=',$user_id)->get();
   
   $cust_id=$user_details[0]->customer_id;
   //return $cust_id;
   if(!empty($location_id)){
         if(!empty($cust_id)){
       $available_inventory = DB::select('select sum(product_inventory.available_inventory)  as available_inventory from product_inventory  inner join locations on locations.location_id=product_inventory.location_id 
          inner join products on products.product_id=product_inventory.product_id 
          where locations.manufacturer_id="'.$cust_id.'" and products.manufacturer_id="'.$cust_id.'" and product_inventory.location_id='.$location_id);
        $intransit_inventory=DB::select('select sum(product_inventory.intransit_inventory)  as intransit_inventory from product_inventory  inner join locations on locations.location_id=product_inventory.location_id 
          inner join products on products.product_id=product_inventory.product_id 
          where locations.manufacturer_id="'.$cust_id.'" and products.manufacturer_id="'.$cust_id.'" and product_inventory.location_id='.$location_id);
         $reserved_inventory=DB::select('select sum(product_inventory.reserved)  as reserved_inventory from product_inventory  inner join locations on locations.location_id=product_inventory.location_id 
          inner join products on products.product_id=product_inventory.product_id 
          where locations.manufacturer_id="'.$cust_id.'" and products.manufacturer_id="'.$cust_id.'" and product_inventory.location_id='.$location_id);
         $sold_inventory = DB::select('select sum(product_inventory.sold)  as sold_inventory from product_inventory  inner join locations on locations.location_id=product_inventory.location_id 
          inner join products on products.product_id=product_inventory.product_id 
          where locations.manufacturer_id="'.$cust_id.'" and products.manufacturer_id="'.$cust_id.'" and product_inventory.location_id='.$location_id);
        $total_inventory=$available_inventory[0]->available_inventory + $intransit_inventory[0]->intransit_inventory
                          +$reserved_inventory[0]->reserved_inventory+$sold_inventory[0]->sold_inventory;
       
        }
         else{
        $available_inventory = DB::select('select sum(available_inventory) as available_inventory from  product_inventory
          where location_id='.$location_id);
        $intransit_inventory=DB::select('select sum(intransit_inventory) as intransit_inventory from  product_inventory
          where location_id='.$location_id);
        $reserved_inventory=DB::select('select sum(reserved) as reserved_inventory from  product_inventory
          where location_id='.$location_id);
        $sold_inventory=DB::select('select sum(sold) as sold_inventory from  product_inventory
          where location_id='.$location_id);
        $total_inventory=$available_inventory[0]->available_inventory + $intransit_inventory[0]->intransit_inventory
                         +$reserved_inventory[0]->reserved_inventory+$sold_inventory[0]->sold_inventory;
        }
        //return $available_inventory;
        }
        //return $total_inventory;
        if(!empty($product_id)){
        //$customer_details = DB::table('product_inventory')->where('product_id','=',$product_id)->get(); 
        if(!empty($cust_id)){
         $available_inventory = DB::select('select sum(product_inventory.available_inventory)  as available_inventory from product_inventory  inner join locations on locations.location_id=product_inventory.location_id 
          inner join products on products.product_id=product_inventory.product_id 
          where locations.manufacturer_id="'.$cust_id.'" and products.manufacturer_id="'.$cust_id.'" and product_inventory.product_id='.$product_id);
        $intransit_inventory=DB::select('select sum(product_inventory.intransit_inventory)  as intransit_inventory from product_inventory  inner join locations on locations.location_id=product_inventory.location_id 
          inner join products on products.product_id=product_inventory.product_id 
          where locations.manufacturer_id="'.$cust_id.'" and products.manufacturer_id="'.$cust_id.'" and product_inventory.product_id='.$product_id);
        $reserved_inventory = DB::select('select sum(product_inventory.reserved)  as reserved_inventory from product_inventory  inner join locations on locations.location_id=product_inventory.location_id 
          inner join products on products.product_id=product_inventory.product_id 
          where locations.manufacturer_id="'.$cust_id.'" and products.manufacturer_id="'.$cust_id.'" and product_inventory.product_id='.$product_id);
        $sold_inventory = DB::select('select sum(product_inventory.sold)  as sold_inventory from product_inventory  inner join locations on locations.location_id=product_inventory.location_id 
          inner join products on products.product_id=product_inventory.product_id 
          where locations.manufacturer_id="'.$cust_id.'" and products.manufacturer_id="'.$cust_id.'" and product_inventory.product_id='.$product_id);
        $total_inventory=$available_inventory[0]->available_inventory + $intransit_inventory[0]->intransit_inventory
                         +$reserved_inventory[0]->reserved_inventory+$sold_inventory[0]->sold_inventory;
        }
        else{
        $available_inventory = DB::select('select sum(available_inventory) as available_inventory from  product_inventory
          where product_id='.$product_id);
        $intransit_inventory=DB::select('select sum(intransit_inventory) as intransit_inventory from  product_inventory
          where product_id='.$product_id);
        $reserved_inventory=DB::select('select sum(reserved) as reserved_inventory from  product_inventory
          where product_id='.$product_id);
        $sold_inventory=DB::select('select sum(sold) as sold_inventory from  product_inventory
          where product_id='.$product_id);
        $total_inventory=$available_inventory[0]->available_inventory + $intransit_inventory[0]->intransit_inventory
                         +$reserved_inventory[0]->reserved_inventory+$sold_inventory[0]->sold_inventory;
        }
        }
        if(!empty($location_id) && !empty($product_id)){
          //$customer_details = DB::table('product_inventory')->where('product_id','=',$product_id)->get(); 
         if(!empty($cust_id)){
       $available_inventory = DB::select('select sum(product_inventory.available_inventory) as available_inventory from  product_inventory
          inner join locations on locations.location_id=product_inventory.location_id 
          inner join products on products.product_id=product_inventory.product_id 
          where locations.manufacturer_id="' .$cust_id.'" and products.manufacturer_id="'.$cust_id.'" and product_inventory.product_id="'.$product_id.'"and product_inventory.location_id='.$location_id);
        $intransit_inventory=DB::select('select sum(product_inventory.intransit_inventory) as intransit_inventory from  product_inventory
          inner join locations on locations.location_id=product_inventory.location_id 
          inner join products on products.product_id=product_inventory.product_id 
          where locations.manufacturer_id="' .$cust_id.'"and products.manufacturer_id="'.$cust_id.'" and product_inventory.product_id="'.$product_id.'"and product_inventory.location_id='.$location_id);
         $reserved_inventory=DB::select('select sum(product_inventory.reserved) as reserved_inventory from  product_inventory
          inner join locations on locations.location_id=product_inventory.location_id 
          inner join products on products.product_id=product_inventory.product_id 
          where locations.manufacturer_id="' .$cust_id.'"and products.manufacturer_id="'.$cust_id.'" and product_inventory.product_id="'.$product_id.'"and product_inventory.location_id='.$location_id);
         $sold_inventory=DB::select('select sum(product_inventory.sold) as sold_inventory from  product_inventory
          inner join locations on locations.location_id=product_inventory.location_id 
          inner join products on products.product_id=product_inventory.product_id 
          where locations.manufacturer_id="' .$cust_id.'"and products.manufacturer_id="'.$cust_id.'" and product_inventory.product_id="'.$product_id.'"and product_inventory.location_id='.$location_id);
        $total_inventory=$available_inventory[0]->available_inventory + $intransit_inventory[0]->intransit_inventory
                         +$reserved_inventory[0]->reserved_inventory+$sold_inventory[0]->sold_inventory;
         }
        else{
        $available_inventory = DB::select('select sum(available_inventory) as available_inventory from  product_inventory
          where product_id="'.$product_id.'"and location_id='.$location_id);
        $intransit_inventory=DB::select('select sum(intransit_inventory) as intransit_inventory from  product_inventory
          where product_id="'.$product_id.'"and location_id='.$location_id);
        $reserved_inventory=DB::select('select sum(reserved) as reserved_inventory from  product_inventory
          where product_id="'.$product_id.'"and location_id='.$location_id);
        $sold_inventory=DB::select('select sum(sold) as sold_inventory from  product_inventory
          where product_id="'.$product_id.'"and location_id='.$location_id);
        $total_inventory=$available_inventory[0]->available_inventory + $intransit_inventory[0]->intransit_inventory
                         +$reserved_inventory[0]->reserved_inventory+$sold_inventory[0]->sold_inventory;
        }
        }
         
        if(empty($location_id) && empty($product_id))
        {
           if(!empty($cust_id)){
          
           $available_inventory = DB::select('select sum(product_inventory.available_inventory) as available_inventory from  product_inventory
          inner join locations on locations.location_id=product_inventory.location_id 
          inner join products on products.product_id=product_inventory.product_id 
          where locations.manufacturer_id="' .$cust_id.'" and products.manufacturer_id='.$cust_id );
         $intransit_inventory=DB::select('select sum(product_inventory.intransit_inventory) as intransit_inventory from  product_inventory
          inner join locations on locations.location_id=product_inventory.location_id 
          inner join products on products.product_id=product_inventory.product_id 
          where locations.manufacturer_id="' .$cust_id.'"and products.manufacturer_id='.$cust_id);
          $reserved_inventory=DB::select('select sum(product_inventory.reserved) as reserved_inventory from  product_inventory
          inner join locations on locations.location_id=product_inventory.location_id 
          inner join products on products.product_id=product_inventory.product_id 
          where locations.manufacturer_id="' .$cust_id.'"and products.manufacturer_id='.$cust_id);
           $sold_inventory=DB::select('select sum(product_inventory.sold) as sold_inventory from  product_inventory
          inner join locations on locations.location_id=product_inventory.location_id 
          inner join products on products.product_id=product_inventory.product_id 
          where locations.manufacturer_id="' .$cust_id.'"and products.manufacturer_id='.$cust_id);
          $total_inventory=$available_inventory[0]->available_inventory + $intransit_inventory[0]->intransit_inventory
                           +$reserved_inventory[0]->reserved_inventory+$sold_inventory[0]->sold_inventory;
          }
          else{
          $available_inventory=DB::select('select sum(available_inventory) as available_inventory from  product_inventory');
          $intransit_inventory=DB::select('select sum(intransit_inventory) as intransit_inventory from  product_inventory');
          $reserved_inventory=DB::select('select sum(reserved) as reserved_inventory from  product_inventory');
          $sold_inventory=DB::select('select sum(sold) as sold_inventory from  product_inventory');
          
          $total_inventory=$available_inventory[0]->available_inventory + $intransit_inventory[0]->intransit_inventory
                           +$reserved_inventory[0]->reserved_inventory+$sold_inventory[0]->sold_inventory;
          }
        }
          //return $reserved_inventory[0]->reserved_inventory;
          $custArr['total_inventory'] = $total_inventory;
          $custArr['available_inventory'] = $available_inventory[0]->available_inventory;
          $custArr['intransit_inventory'] = $intransit_inventory[0]->intransit_inventory;
          $custArr['reserved_inventory'] = $reserved_inventory[0]->reserved_inventory;
          $custArr['sold_inventory'] = $sold_inventory[0]->sold_inventory;
          
          $finalCustArr[] = $custArr;

          
     return $finalCustArr;
  
}
/*
public function updateInventory()
{
    
    $module_id = Input::get('module_id');
    $access_token= Input::get('access_token');
   
   // $loc_id = urldecode($this->getRequest()->getParam('loc_id')); 
    
    $baseurl= $_SERVER['SERVER_NAME'];;
  // return $baseurl;
    $url=$baseurl.'/job/updateInventory?module_id="'.$module_id.'"&access_token='.$access_token;
    $url = str_replace(' ', '%20', $url);
    $ch = curl_init();

            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE); //Set curl to return the data instead of printing it to the browser.
            curl_setopt($ch, CURLOPT_TIMEOUT,1200);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT,600); # timeout after 100 seconds, you can increase it
            //curl_setopt($ch, CURLOPT_USERAGENT);
            curl_setopt($ch, CURLOPT_URL, $url);
            $result = curl_exec($ch);
            curl_close($ch);
           
           print_r($result);exit; 
}*/

 public function iotBankReport()
    {
      //dd("fatata");
      $products= array();
      $locations = array();
      if(Session::get('customerId')==0){
        $inputData = Input::all();
        if(empty($inputData))
        {
          $customerId = 0; 

        }else{
          //echo "<pre>"; print_r($inputData); die;
          $customerId = $inputData['customer_id']; 
          $products = DB::table('products')->where('manufacturer_id',$customerId)->lists('name','product_id');
          $locations = DB::table('locations')->where('manufacturer_id',$customerId)->lists('location_name','location_id');
        }
        $customers = $this->custRepoObj->getAllCustomers();
        
      }else{
         $customerId = Session::get('customerId');
         $customers = array(); 
         $products = DB::table('products')->where('manufacturer_id',$customerId)->lists('name','product_id');
          $locations = DB::table('locations')->where('manufacturer_id',$customerId)->lists('location_name','location_id');
      }  
      
      //echo "<pre>"; print_r($customers); die;
     

      $data = new ArrayObject();

      // if($customerId > 0){
      //   $data->total_IOTs = DB::table('eseal_bank_'.$customerId)->pluck(DB::raw('count(id) as total'));
      //   $data->available_download_IOTs = DB::table('eseal_bank_'.$customerId)->where(array('used_status'=>'0','issue_status'=>'0','download_status'=>'0'))->pluck(DB::raw('count(id) as total'));
        
      //  // $data->available_issue_IOTs = DB::table('eseal_bank_'.$customerId)->where(array('used_status'=>'0','issue_status'=>'1','download_status'=>'0'))->pluck(DB::raw('count(id) as total'));
        
      //   //$data->used_issue_IOTs = DB::table('eseal_bank_'.$customerId)->where(array('used_status'=>'1','issue_status'=>1))->pluck(DB::raw('count(id) as total'));
      //   //$data->used_download_IOTs = DB::table('eseal_bank_'.$customerId)->where(array('used_status'=>'1','download_status'=>1))->pluck(DB::raw('count(id) as total'));
       
      //   $data->primary_IOTs = DB::table('eseal_bank_'.$customerId)->where(array('used_status'=>'1','level'=>'0',))->pluck(DB::raw('count(id) as total'));
      //   $data->secondary_IOTs = DB::table('eseal_bank_'.$customerId)->where(array('used_status'=>'1','level'=>'1','download_status'=>1))->pluck(DB::raw('count(id) as total'));
      //   $data->tp_IOTs = DB::table('eseal_bank_'.$customerId)->where(array('used_status'=>'1','level'=>'9','download_status'=>1))->pluck(DB::raw('count(id) as total'));
      // }else{
      //   $data->total_IOTs = 0;
      //   $data->available_download_IOTs = 0;
      //   $data->available_issue_IOTs = 0;
      //   $data->used_issue_IOTs = 0;
      //   $data->used_download_IOTs = 0;
        
      //   $data->primary_IOTs = 0;
      //   $data->secondary_IOTs = 0;
      //   $data->tp_IOTs = 0;
      // }
      //echo "<pre>"; print_r($data); die;
      return View::make('dashboard.iotbankreport')->with(array('customers'=>$customers,'customerId'=>$customerId,'products'=>$products,'locations'=>$locations));
    }

public function inventoryReport()
    {
      $user_id = Session::get('userId');
      
      $user_details=DB::table('users')->where('user_id','=',$user_id)->get();
      
      $users_token=DB::table('users_token')
                   ->where('user_id',$user_id)
                   ->get();

                
      
      if(!empty($users_token)){
      $module_id=$users_token[0]->module_id;
      $access_token=$users_token[0]->access_token; 
      }
      else{
        $module_id='';
        $access_token=''; 
      }
      
      $customers = array();
      $cust_id=$user_details[0]->customer_id;
      $locations = [];
      $products= [];
      $location_types = [];
      $product_groups = [];
      $categories =[];
      $products = [];
      if($cust_id == 0){
        $customers = $this->custRepoObj->getAllCustomers();
      }
      if(Schema::hasColumn('eseal_'.$cust_id, 'storage_location'))  //check whether users table has email column
        {
         $storage_location_exists = true;
        }
        else{
          $storage_location_exists = false;
        }

      if(empty($cust_id)){
          return View::make('dashboard.inventoryreport',compact('locations','products','module_id','access_token','location_types','product_groups','categories','storage_location_exists','customers','cust_id'));
      }
      else{
        $location_types=DB::table('location_types')->where('manufacturer_id',$cust_id)->where('is_deleted',0)->get();
        
        $product_groups= DB::table('product_groups')->where('manufacture_id',$cust_id)->lists('name','group_id');
        $categories = DB::table('categories')->lists('name','category_id');
        $products=  DB::table('products')->where('manufacturer_id',$cust_id)->lists('name','product_id');
      }
      
       
      
      return View::make('dashboard.inventoryreport',compact('locations','products','module_id','access_token','location_types','product_groups','categories','storage_location_exists','customers','cust_id'));
    }

public function getData($location_id,$product_id,$storage_location,$from_date,$to_date,$cust_id=0,$excelexport = 0)
    {
        $data = Input::all();

//dd($product_id);
      //check if the customer id is 0 while method is called from excelexport function..

      if($excelexport == 1 && $cust_id == 0){
        return [];
      }

      //  parent::Breadcrumbs(array('Home' => '/', 'Reports' => '#', 'inventoryreport' => 'dashboard/inventoryReport'));
        $user_id = Session::get('userId');
        $user_details=DB::table('users')->where('user_id','=',$user_id)->get();
        //$cust_id=$user_details[0]->customer_id;
        $location_filter = "";
        $product_filter = "";
        $fromfilter = "";
        $tofilter = "";
        $product_group_filter = "";
        $location_type_filter = "";
        $category_filter = "";
        $storageLocationfilter = "";
        $customer_details = [];
        $storage_location_exists = false;
        if(Schema::hasColumn('eseal_'.$cust_id, 'storage_location'))  //check whether users table has email column
        {
         $storage_location_exists = true;
        }
       // dd($storage_location_exists);
        if(empty($cust_id)){
           return json_encode($customer_details);
        }
        if(!empty($data['product_group'])){
            $product_group_filter = " and p.group_id = ".$data['product_group'];
        }
        if(!empty($data['location_type'])){
            $location_type_filter = " and l.location_type_id = ".$data['location_type'];
        }
        if(!empty($data['category'])){
            $category_filter = " and p.category_id = ".$data['category'];
        }
        if(!empty($location_id)){
            $location_filter=" and l.location_id in(".$location_id.")";
        }
        if(!empty($product_id)){
            $product_filter =" and p.product_id in( ".$product_id .")";
        }
        if(!empty($from_date)){
            $fromfilter=" and DATE(update_time) >= '".$from_date."'";
        }
        if(!empty($to_date)){
            $tofilter=" and DATE(update_time) <= '".$to_date."'";
        }
        if($storage_location_exists){
          if(!empty($storage_location) && (trim(strtolower($storage_location)) !="select storage location")){
            $storageLocationfilter=" and es.storage_location= '".$storage_location."'";
          }  
          $select_query = "select p.name AS product_name,p.material_code,l.`location_name` AS location_name,l.erp_code as 'ErpCode',lt.location_type_name,SUM(es.pkg_qty) AS available_inventory,c.name as category_name,es.storage_location";
          $group = "GROUP BY l.location_id,es.pid,es.storage_location";
        }
        else{
          $select_query = "select p.name AS product_name,p.material_code as 'MaterialCode',l.`location_name` AS location_name,l.erp_code as 'ErpCode',lt.location_type_name,SUM(es.pkg_qty) AS available_inventory,c.name as category_name";
          $group = "GROUP BY l.location_id,es.pid";
        }
        

        if(!empty($cust_id)){
            $cust_table = "eseal_".$cust_id;
        }
        else{
            $cust_table ="";
        }

        $query= $select_query."
                FROM ".$cust_table." es
                JOIN track_history th ON th.track_id=es.track_id
                JOIN products p ON p.product_id=es.pid
                JOIN locations l ON l.location_id=th.src_loc_id
                Join location_types lt on (lt.location_type_id = l.location_type_id)
                JOIN categories c ON p.category_id = c.category_id
                WHERE th.dest_loc_id =0 AND level_id=0 AND es.is_active = 1 ".$product_group_filter." ".$location_type_filter." ".$category_filter." ".$location_filter." ".$product_filter." ".$fromfilter." ".$tofilter." ".$storageLocationfilter." ".$group;
//                GROUP BY l.location_id,es.pid,es.storage_location";
                //dd($query);
        $customer_details = DB::select($query);
        if($excelexport == 1){
          return $customer_details;
        }

        return json_encode($customer_details);
    }


  public function getProducts(){
    $user_id = Session::get('userId');
    if(empty($user_id)){
      return [];
    }
    //dd($user_id);
    $cust_id = DB::table('users')->where('user_id',$user_id)->take(1)->value('customer_id');
    $product_group_id=Session::get('product_group_id');
    $category_id = Session::get('category_id');
    $and ="";
    $where ="";
    $categoryfilter = "";
    $productgroupfilter = "";
    $products = DB::table('products');
    if($category_id  >0 ){
      $products->where('category_id',$category_id);
//      $categoryfilter = " category_id =".$category_id; 
    }
    if($product_group_id > 0){
          $products->where('group_id',$product_group_id);
      //$productgroupfilter = " group_id = ".$product_group_id;
    }
    // if(!empty($product_group_id) || !empty($category_id)){
    //   $where ="where";
    // }
    // if(!empty($product_group_id) && !empty($category_id)){
    //   $and ="and";
    // }
    // $products = DB::select('select name,product_id from products '.$where.' '.$categoryfilter.' '.$and.' '.$productgroupfilter)->lists('name','product_id');
    // $products = DB::table('products')->where('group_id',$product_group_id)
    //   ->where('manufacturer_id',$cust_id)
    //   ->lists('name','product_id');
    $products =$products->value('name','product_id');
    //print_r($products);exit;
      return $products;
      //$cate = "" 
      
    
    

}

public function getLocationsOld(){
  $user_id = Session::get('userId');
  if(empty($user_id)){
    return [];
  }
  //dd($user_id);
  //$cust_id = DB::table('users')->where('user_id',$user_id)->take(1)->pluck('customer_id');
  $cust_id = Input::get('customer_id');
  $location_type_id=Input::get('location_type_id');
  if(!empty($location_type_id)){
    $locations = DB::table('locations')->where('location_type_id',$location_type_id)
    ->where('manufacturer_id',$cust_id)
    ->lists('location_name','location_id');
    return $locations;
  }
  else{
    return [];
  }
}
public function getLocations(Request $request){
  
  $user_id = Session::get('userId');
  /*if(empty($user_id)){
    return [];
  }*/
  //dd($user_id);
  //$cust_id = DB::table('users')->where('user_id',$user_id)->take(1)->pluck('customer_id');
  $cust_id = Session::get('customer_id');
  $cust_id = 6;
  $getLoc = DB::table('users')->where('user_id',$user_id)->value('location_id');
  $getCustType = DB::table('users')->where('user_id',$user_id)->value('customer_type');
  $location_type_id=$request->get('location_type_id');
  if(!empty($location_type_id) && $getCustType != 1000){
    $locations = DB::table('locations')->where('location_type_id',$location_type_id)
    ->where('manufacturer_id',$cust_id)
    ->get(['location_name','location_id'])->toArray();
    // print_r($locations);exit;
  //print_r($locations);exit;
    return $locations;
  }
  else{
     $locations = DB::table('locations')->where('location_id',$getLoc)
    ->where('manufacturer_id',$cust_id)
    ->get(['location_name','location_id'])->toArray();
    return $locations;
  }
}

public function getProductsByLocation(){
  $user_id = Session::get('userId');
  if(empty($user_id)){
    return [];
  }

  $cust_id = Input::get('customer_id');
  $location_id=Input::get('location_id');
  if(!empty($location_id)){
    $products = DB::table('products')
    ->join('product_locations', 'products.product_id', '=', 'product_locations.product_id')
    ->where('products.manufacturer_id',$cust_id)
    ->where('product_locations.location_id',$location_id)    
    ->select('products.name','products.product_id')
    ->get();
    $products_arr = array();
    foreach($products as $product)
    {
        $products_arr[$product->product_id] = $product->name;
    }

    return $products_arr;
  }
  else{
    return [];
  }
}

    
    public function iotBankReportData(){
    //  dd(Session::get('customerId'));
      $inputData = Input::all();
       if(Session::get('customerId')==0){
        

        if(empty($inputData))
        {
          $customerId = 0;  
        }else{
          //echo "<pre>"; print_r($inputData); die;
          $customerId = $inputData['customer_id']; 
        }
        $customers = $this->custRepoObj->getAllCustomers();
        
      }else{
         $customerId = Session::get('customerId');
         $customers = array();           
      }  
      $data = [];
      //dd($customerId);
      $productfilter = 1;
      $locationfilter =1;
      $from_datefilter =1;
      $to_datefilter =1;
 Log::info($customerId);
 Log::info($inputData);
        if($customerId > 0){
     //    if(isset($inputData['product_id']) && !empty($inputData['product_id'])){
     //      $productfilter = " e.pid = ".$inputData['product_id']; 
     //    }
     //    if(isset($inputData['location_id']) && !empty($inputData['location_id'])){
     //      $locationfilter = "eb.location_id = ".$inputData['location_id'];
     //    }
     //    if(isset($inputData['from_date']) && !empty($inputData['from_date'])){
     //      $from_datefilter = " DATE(eb.utilizedDate) >= '".$inputData['from_date']."' ";
     //    }
     //    if(isset($inputData['to_date']) && !empty($inputData['to_date'])){
     //      $to_datefilter =" DATE(eb.utilizedDate) <= '".$inputData['to_date']."' ";
     //    }
     //    if(empty($productfilter) && empty($locationfilter) && empty($from_datefilter) && empty($to_datefilter)){
     //      $where ="";
     //    }
     //    else{
     //      $where ="where";
     //    }
     //    if(!empty($inputData['product_id']) || !empty($inputData['from_date']) || !empty($inputData['to_date'])){
     //      $join = " join eseal_".$customerId." e on e.primary_id = eb.id";
     //    }
     //    else{
     //      $join = "";
     //    }
     //    $query ="select count(id) as iot,download_status,issue_status,used_status,level from eseal_bank_".$customerId." eb ".$join."  ".$where." ".$productfilter." and ".$locationfilter." and ".$from_datefilter." and ".$to_datefilter." group by download_status,issue_status,used_status,level ";
     //    Log::info($query);
     //    $result = DB::select($query);
     //    //dd($result);

     //    $used_for_products = 0;
     //    $used_for_cartons =0;
     //    $used_for_tp = 0;
     //    foreach($result as $res){
          
          
     //      // //------------------
     //      if(($res->used_status ==1) && ($res->level == 9 ) ){

     //        $used_for_tp += $res->iot;
     //      }
     //      if(($res->used_status ==1) && ($res->level == 1 ) ){

     //        $used_for_cartons +=$res->iot;
     //      }
     //      if(($res->used_status ==1) && ($res->level == 0) ){

     //        $used_for_products +=$res->iot;
     //      }

     //    }
     //    if(!empty($join) &&  (empty($inputData['product_id']) && empty($inputData['location_id']))){
     //      $tp_query = "select count(serial_id) as count from eseal_bank_".$customerId." eb where used_status = 1 and level = 9  and ".$from_datefilter." and ".$to_datefilter;
     //      $used_for_tp = DB::select($tp_query);
     //      $used_for_tp = $used_for_tp[0]->count;
     //    }

     //    //$data['total_IOTs'] = 
     //    //$data['available_download_IOTs']
     //    //$data['available_issue_IOTs']
     //    //$data['used_issue_IOTs']
     //    //$data['used_download_IOTs']
     //    $data['primary_IOTs']=$used_for_products;
     //    $data['secondary_IOTs']= $used_for_cartons;
     //    $data['tp_IOTs']= $used_for_tp;
     //    //$data['total_IOTs'] = DB::select('select count(id) as total from eseal_bank_'.$customerId)
     //    $data['total_IOTs'] = DB::table('eseal_bank_'.$customerId)->pluck(DB::raw('count(id) as total'));
     //    $data['available_download_IOTs'] = DB::table('eseal_bank_'.$customerId)->where(array('used_status'=>'0','issue_status'=>'0','download_status'=>'0'))->pluck(DB::raw('count(id) as total'));
        
     //    $data['available_issue_IOTs'] = DB::table('eseal_bank_'.$customerId)->where(array('used_status'=>'0','issue_status'=>'1','download_status'=>'0'))->pluck(DB::raw('count(id) as total'));
        
     //    $data['used_issue_IOTs'] = DB::table('eseal_bank_'.$customerId)->where(array('used_status'=>'1','issue_status'=>1))->pluck(DB::raw('count(id) as total'));
     //    $data['used_download_IOTs'] = DB::table('eseal_bank_'.$customerId)->where(array('used_status'=>'1','download_status'=>1))->pluck(DB::raw('count(id) as total'));
       
     //    // $data['primary_IOTs'] = DB::table('eseal_bank_'.$customerId)->where(array('used_status'=>'1','level'=>'0',))->pluck(DB::raw('count(id) as total'));
     //    // $data['secondary_IOTs'] = DB::table('eseal_bank_'.$customerId)->where(array('used_status'=>'1','level'=>'1','download_status'=>1))->pluck(DB::raw('count(id) as total'));
     //    // $data['tp_IOTs'] = DB::table('eseal_bank_'.$customerId)->where(array('used_status'=>'1','level'=>'9','download_status'=>1))->pluck(DB::raw('count(id) as total'));
     // // dd($data);


        $total_iots = DB::table('eseal_bank_'.$customerId);
        
        $total_iots = $total_iots->count();
 
        $total_available_iots = DB::table('eseal_bank_'.$customerId)->where(array('used_status'=>0,'download_status'=>0));
        
        $total_available_iots = $total_available_iots->count();
        //$data['available_issue_IOTs'] = DB::table('eseal_bank_'.$customerId)->where(array('used_status'=>0,'issue_status'=>0,'download_status'=>0))->count();
        $total_used_iots = DB::table('eseal_bank_'.$customerId)->where(array('used_status'=>1));
        $total_used_iots = $total_used_iots->count();
        // select count(serial_id) from eseal_bank_5 where used_status=0 and issue_status=0 and download_status=0;
        $primary = DB::table('eseal_bank_'.$customerId)->where(array('level'=>0,'used_status'=>1));
        if(isset($inputData['product_id']) && !empty($inputData['product_id'])){
          $primary->where('pid',$inputData['product_id']);
        }
        if(isset($inputData['location_id']) && !empty($inputData['location_id'])){
          $primary->where('location_id',$inputData['location_id']);
        }
        if(isset($inputData['from_date']) && !empty($inputData['from_date'])){
          $primary->where('utilizedDate','>=',$inputData['from_date']);
        }
        if(isset($inputData['to_date']) && !empty($inputData['to_date'])){
          $primary->where('utilizedDate','<=',$inputData['to_date']);
        }
        $primary= $primary->count();

        $secondary =DB::table('eseal_bank_'.$customerId)->where(array('level'=>1,'used_status'=>1));
          if(isset($inputData['product_id']) && !empty($inputData['product_id'])){
          $secondary->where('pid',$inputData['product_id']);
        }
        if(isset($inputData['location_id']) && !empty($inputData['location_id'])){
          $secondary->where('location_id',$inputData['location_id']);
        }
        if(isset($inputData['from_date']) && !empty($inputData['from_date'])){
          $secondary->where('utilizedDate','>=',$inputData['from_date']);
        }
        if(isset($inputData['to_date']) && !empty($inputData['to_date'])){
          $secondary->where('utilizedDate','<=',$inputData['to_date']);
        }
        $secondary = $secondary->count();
        $tp = DB::table('eseal_bank_'.$customerId)->where(array('level'=>9,'used_status'=>1));
          if(isset($inputData['product_id']) && !empty($inputData['product_id'])){
          $tp->where('pid',$inputData['product_id']);
        }
        if(isset($inputData['location_id']) && !empty($inputData['location_id'])){
          $tp->where('location_id',$inputData['location_id']);
        }
        if(isset($inputData['from_date']) && !empty($inputData['from_date'])){
          $tp->where('utilizedDate','>=',$inputData['from_date']);
        }
        if(isset($inputData['to_date']) && !empty($inputData['to_date'])){
          $tp->where('utilizedDate','<=',$inputData['to_date']);
        }
        $tp = $tp->count();

        $downloaded_and_notused = DB::table('eseal_bank_'.$customerId)->where(array('used_status'=>0,'download_status'=>1))->count();
        //$data['used_issue_IOTs'] = 0;
        //$data['used_download_IOTs'] = 0;
         
        //$data['primary_IOTs'] = 0;
         $data['total_IOTs'] = $total_iots;
        $data['total_available_IOTs'] = $total_available_iots;
        $data['total_used_IOTs'] = $total_used_iots;
        $data['primary_IOTs'] = $primary;
        $data['secondary_IOTs'] = $secondary;
         
        $data['tp_IOTs'] = $tp;
        $data['downloaded_but_notused']=$downloaded_and_notused;
        
        
      }
      else{
        $data['total_IOTs'] = 0;
        $data['total_available_IOTs'] = 0;
        $data['total_used_IOTs'] = 0;
        $data['primary_IOTs'] = 0;
        $data['secondary_IOTs'] = 0;
         
        $data['tp_IOTs'] = 0;
        $data['downloaded_but_notused']= 0;
        //$data['secondary_IOTs'] = 0;
        //$data['tp_IOTs'] = 0;
      }
      return $data;
    }


  public function exportExcel($location_id=0,$product_id=0,$storage_location='',$cust_id=0){

    
    $filter_data = Input::all();

    //Logging the report..
    $report_name = "";
    if($filter_data['type'] == 1){
      $report_name = "Inventory Summary Report";
    }

    if($filter_data['type'] == 2){
      $report_name = "Inventory Report Against IOT";
    }

    DB::table('report_log')->insert(['report_name'=>$report_name,'user_id'=>Session::get('userId')]);

    //Preparing the Filter data 
    if(!empty($filter_data['product_group'])){
        $product_group = DB::table('product_groups')->where(['manufacture_id'=>Session::get('customerId'),'group_id'=>$filter_data['product_group']])->take(1)->pluck('name');
    }else{
      $product_group = 'ALL';
    }
    if(!empty($filter_data['location_type'])){
        $location_type = DB::table('location_types')->where(['manufacturer_id'=>Session::get('customerId'),"location_type_id"=>$filter_data['location_type']])->take(1)->pluck('location_type_name');

    }else{
      $location_type= 'ALL';
    }
    if(!empty($filter_data['category'])){
        $category = DB::table('categories')->where(['category_id'=>$filter_data['category']])->pluck('name');
    }
    else{
      $category = "ALL";
    }
    if(!empty($location_id)){
        $locations = explode(',',$location_id);
        $locations = DB::table('locations')->whereIn('location_id',$locations)->lists('location_name');
        $locations = implode(',',$locations);
    }else{
      $locations = "ALL";
    }
    if(!empty($product_id)){
        $products = explode(',',$product_id);
        $products =DB::table('products')->whereIn('product_id',$products)->lists('name');
        $products = implode(',',$products);
    }else{
      $products = "ALL";
    }

    if(empty($storage_location) || strtolower($storage_location) == "select storage location"){
      $storage_location_filter = 'ALL';
    }
    
    
    $filters = ["Product Group"=>$product_group,'Location Type'=>$location_type,'Category'=>$category,'Locations'=>$locations,'Products'=>$products,"Storage Location"=>$storage_location_filter];


    if(Schema::hasColumn('eseal_'.$cust_id, 'storage_location'))  //check whether users table has email column
    {
     $storage_location_exists = true;
    }
    else{
      $storage_location_exists = false;
    }


    if($filter_data['type'] == 1){
      $data = $this->getData($location_id,$product_id,$storage_location,0,0,$cust_id,1);  
ob_end_clean();
ob_start();
      Excel::create('Inventory Report', function($excel) use($data,$storage_location_exists,$filters) {
          return $excel->sheet('New sheet', function($sheet) use($data,$storage_location_exists,$filters){
            $sheet->loadView('dashboard.export_excel',array('data'=>$data,'storage_location_exists'=>$storage_location_exists,"filters"=>$filters));
          });

      })->export('xlsx');

    }
    else if($filter_data['type'] == 2){

    $data = (array) json_decode(json_encode($this->getDataAgainstIot($location_id,$product_id,$storage_location,0,0,$cust_id,1)),true);  

//   $data = $this->getDataAgainstIot($location_id,$product_id,$storage_location,0,0,$cust_id,1);  

ob_end_clean();
ob_start();
$exportData=array();
foreach ($data as $key => $value) {
  $exportData[$key]['Primary IOT']=(string)$data[$key]['iot'].'';
  $exportData[$key]['Parent IOT']=(string)$data[$key]['parent'].'';
  $exportData[$key]['Material Code']=$data[$key]['material_code'];
  $exportData[$key]['Product Name']=$data[$key]['product_name'];
  $exportData[$key]['Location Type']=$data[$key]['location_type_name'];
  $exportData[$key]['Location']=$data[$key]['location_name'];
  $exportData[$key]['Location Erp Code']=$data[$key]['erp_code'];
  $exportData[$key]['Mfg Date']=$data[$key]['mfg_date'];
  $exportData[$key]['Category']=$data[$key]['category_name'];
  $exportData[$key]['Avaliable Inventory']=$data[$key]['availabel_inventory'];
  $exportData[$key]['Storage Location']=$data[$key]['storage_location'];
  $exportData[$key]['Expiry Date']=$data[$key]['expiry_date'];
  $exportData[$key]['Age']=$data[$key]['age'];
}
/*
echo "<pre>";
print_r($filters);
exit;
*/

/*$sheet1[]['V-GUARD INDUSTRIES PVT LTD.']='INVENTORY REPORT ';
$sheet1[]['V-GUARD INDUSTRIES PVT LTD.']='REPORT DATE: '.date('d-M-y, h:i:s A');
foreach ($filters as $key => $value) {
  //$sheet1[][$key]
}*/
         Excel::create('Inventory_Report', function($excel) use ($exportData,$storage_location_exists,$filters) {
            $excel->sheet('Details', function($sheet) use ($exportData,$filters)
            {

              
              $sheet->setColumnFormat(array(
              "A:M" => "@"
              ));
              $sheet->cell('A1', function($cell) {$cell->setValue('V-GUARD INDUSTRIES PVT LTD.');   });
              $sheet->cell('A2', function($cell) {$cell->setValue('INVENTORY REPORT');   });
              $sheet->cell('A3', function($cell) {$cell->setValue('REPORT DATE: '.date('d-M-y, h:i:s A'));   });
              $i=4;

              foreach ($filters as $key => $value) {
                  $sheet->cell('A'.$i, function($cell) use($key,$value) {$cell->setValue($key.': ');   });
                  $sheet->cell('B'.$i, function($cell) use($key,$value) {$cell->setValue($value);   });
                  $i++;
                }
                            
            $n=1;
            while($n<=$i){
              $sheet->row($n, function ($Row) {
                  $Row->setFontWeight('bold');
                });
              $n++;
            }


            });


            $excel->sheet('IotData', function($sheet) use ($exportData,$filters)
            {
              $sheet->setColumnFormat(array(
              "A:M" => "@"
              ));
              $sheet->fromArray($exportData);
              $sheet->row(1, function ($Row) {
                  $Row->setFontWeight('bold');
                });
                
            });

          })->export('xlsx');
      //   $pdf="/download/qrpdfs/".$name.'.xlsx';

/*foreach ($data as $key => $value) {
  $data[$key]->iot=(string)$data[$key]->iot.'';
  $data[$key]->parent=(string)$data[$key]->parent.'';
}
*/ 
/*
          Excel::create('Inventory_Report', function($excel) use($data,$storage_location_exists,$filters) {

            return $excel->sheet('New sheet', function($sheet) use($data,$storage_location_exists,$filters){
               $sheet->setColumnFormat(array(
       "A:M" => "@"
    ));
               //$sheet->setCellValueExplicit('A12', 'aaaaaa');
            $sheet->loadView('dashboard.export_excel_againstiot',array('data'=>$data,'storage_location_exists'=>$storage_location_exists,"filters"=>$filters));
            });
          })->export('xlsx');
*/


    }

  }
      
  public function exportExcel_backup($location_id=0,$product_id=0,$storage_location='',$cust_id=0){
    $filter_data = Input::all();
    $data = $this->getData($location_id,$product_id,$storage_location,0,0,$cust_id,1);

    if(!empty($filter_data['product_group'])){
        $product_group = DB::table('product_groups')->where(['manufacture_id'=>Session::get('customerId'),'group_id'=>$filter_data['product_group']])->take(1)->pluck('name');
    }else{
      $product_group = 'ALL';
    }
    if(!empty($filter_data['location_type'])){
        $location_type = DB::table('location_types')->where(['manufacturer_id'=>Session::get('customerId'),"location_type_id"=>$filter_data['location_type']])->take(1)->pluck('location_type_name');

    }else{
      $location_type= 'ALL';
    }
    if(!empty($filter_data['category'])){
        $category = DB::table('categories')->where(['category_id'=>$filter_data['category']])->pluck('name');
    }
    else{
      $category = "ALL";
    }
    if(!empty($location_id)){
        $locations = explode(',',$location_id);
        $locations = DB::table('locations')->whereIn('location_id',$locations)->lists('location_name');
        $locations = implode(',',$locations);
    }else{
      $locations = "ALL";
    }
    if(!empty($product_id)){
        $products = explode(',',$product_id);
        $products =DB::table('products')->whereIn('product_id',$products)->lists('name');
        $products = implode(',',$products);
    }else{
      $products = "ALL";
    }

    if(empty($storage_location) || strtolower($storage_location) == "select storage location"){
      $storage_location = 'ALL';
    }
    
    
    $filters = ["Product Group"=>$product_group,'Location Type'=>$location_type,'Category'=>$category,'Locations'=>$locations,'Products'=>$products,"Storage Location"=>$storage_location];


    if(Schema::hasColumn('eseal_'.$cust_id, 'storage_location'))  //check whether users table has email column
        {
         $storage_location_exists = true;
        }
        else{
          $storage_location_exists = false;
        }
    Excel::create('Inventory Report', function($excel) use($data,$storage_location_exists,$filters) {

    return $excel->sheet('New sheet', function($sheet) use($data,$storage_location_exists,$filters){

        $sheet->loadView('dashboard.export_excel',array('data'=>$data,'storage_location_exists'=>$storage_location_exists,"filters"=>$filters));

      });

    })->export('xls');
    
    //dd($data);

    // return Excel::create('Inventory Report', function($excel) use($data) {
    //     $excel->sheet('Sheet 1', function($sheet) use($data) {
    //         $sheet->fromArray($data);
    //     });
    // })->export('xls');
  }

  public function getStorageLocations(){
    $user_id = Session::get('userId');
    /*if(empty($user_id)){
      return [];
    }*/
    //dd($user_id);
    //$cust_id = DB::table('users')->where('user_id',$user_id)->take(1)->pluck('customer_id');
    $cust_id = Session::get('customer_id');
    $location =Session::get('location_id');

    $storageLocations=DB::table('locations')->where('parent_location_id',$location)
    ->where('manufacturer_id',$cust_id)
    ->value('erp_code');
  
    return $storageLocations;
  
  }


  public function saleReport(){

      Log::info('We are in '.__METHOD__);
  $user_id = Session::get('userId');
  Log::info('User Id : '.$user_id);
  
  $user_details=DB::table('users')->where('user_id','=',$user_id)->get();
  
  //return $user_id;
  $users_token=DB::select('select * from users_token where user_id='.$user_id);
  if(!empty($users_token)){
  $module_id=$users_token[0]->module_id;
  $access_token=$users_token[0]->access_token; 
  }
  else{
    $module_id='';
    $access_token=''; 
  }

$locationTypes =[];
  if(Session::get('customerId')==0){
        $customerId = 0;
        $customers = $this->custRepoObj->getAllCustomers();
      }else{
         $customerId = Session::get('customerId');
         $customers = array();           
         $locationTypes = DB::table('location_types')->where('manufacturer_id',$customerId)->lists('location_type_name','location_type_id');
      }  
      $cust_table= 'eseal_'.$customerId;
      $sales= [];
     
  

      
      return View::make('dashboard.salereport',compact('sales','module_id','access_token','customers','customerId','locationTypes'));
    }

    public function salesReportData(){
        $inputData = Input::all();
        //dd($inputData);
       if(Session::get('customerId')==0){
        
        if(empty($inputData))
        {
          $customerId = 0;  
        }else{
          //echo "<pre>"; print_r($inputData); die;
          $customerId = $inputData['customer_id']; 
        }
        //$customers = $this->custRepoObj->getAllCustomers();
        
      }else{
         $customerId = Session::get('customerId');
         $customers = array();           
      }  
      $location_type=isset($inputData['location_type'])?$inputData['location_type']:0;
      $from_date = isset($inputData['from_date'])?$inputData['from_date']:0;
      $to_date = isset($inputData['to_date'])?$inputData['to_date']:0;
      $data = [];
      $cust_table ='eseal_'.$customerId;


 
     if($customerId > 0 && !empty($location_type)){
        $packingtransactions = DB::table('transaction_master')->where(array('srcLoc_action' => 1 ,'dstLoc_action' => 0, 'intrn_action' =>0,'manufacturer_id'=>$customerId))->lists('id');
        
        //$packtrans= 


        $salestransactions = DB::table('transaction_master')->where(array('srcLoc_action'=> -1,'dstLoc_action' => 0, 'intrn_action' =>1,'manufacturer_id'=>$customerId))->lists('id');
        if(count($packingtransactions) >0){
          $packtrans = implode(',',$packingtransactions);
        }
        else{
          $packtrans = null;
        }
        if(count($salestransactions) >0){
          $saletrans = implode(',',$salestransactions);
        }
        else{
          $saletrans = null;
        }
        //dd($salestransactions);
        if(!empty($location_type)){
          $location_type_filter = " AND l.location_type_id =".$location_type;
        }
        else{
          $location_type_filter = "";
        }
        if(!empty($from_date)){
          $from_filter = " AND DATE(update_time)>='".$from_date."'";
        }
        else{
          $from_filter = "";
        }
        if(!empty($to_filter)){
          $to_filter =" AND DATE(update_time) <='".$to_date."'";
        }
        else{
          $to_filter ="";
        }

        // $data = DB::select("SELECT SUM(es.pkg_qty) AS Qty,p.material_code AS MaterialCode,p.name AS MaterialName,l.location_name AS SaleLocation,th.`update_time` AS SaleTime,th.tp_id AS Tp,
        //   (SELECT VALUE FROM tp_attributes tpa WHERE tpa.tp_id=th.tp_id AND tpa.attribute_id=205) AS Delivery,
        //   (SELECT l.location_name FROM track_history th1 JOIN track_details td1 ON td1.track_id=th1.track_id
        //   JOIN locations l ON l.location_id=th1.src_loc_id
        //   WHERE td1.code=td.code AND th1.dest_loc_id=0 AND th1.transition_id IN (".$packtrans.") LIMIT 1) AS SourceLocation
        //   FROM ".$cust_table." es JOIN track_details td ON td.code=es.primary_id
        //   JOIN track_history th ON th.track_id=td.track_id
        //   JOIN products p ON p.product_id=es.pid
        //   JOIN locations l ON l.location_id=th.src_loc_id
        //   WHERE th.dest_loc_id !=0 AND level_id=0 AND th.transition_id IN (".$saletrans.") AND l.location_type_id=872
        //   AND (DATE(update_time) >= '2016-12-23' AND DATE(update_time) <= '2016-12-29') 
        //   GROUP BY th.src_loc_id,es.pid,th.tp_id"); 
     // dd($data);
        $data=DB::select("SELECT SUM(es.pkg_qty) AS Qty,p.material_code AS MaterialCode,p.name AS MaterialName,l.location_name AS SaleLocation,th.`update_time` AS SaleTime,th.tp_id AS Tp,
           (SELECT VALUE FROM tp_attributes tpa WHERE tpa.tp_id=th.tp_id AND tpa.attribute_id=205) AS Delivery,
           (SELECT l.location_name FROM track_history th1 JOIN track_details td1 ON td1.track_id=th1.track_id
           JOIN locations l ON l.location_id=th1.src_loc_id
           WHERE td1.code=td.code AND th1.dest_loc_id=0 AND th1.transition_id IN (".$packtrans.") LIMIT 1) AS SourceLocation
           FROM ".$cust_table." es JOIN track_details td ON td.code=es.primary_id
           JOIN track_history th ON th.track_id=td.track_id
           JOIN products p ON p.product_id=es.pid
           JOIN locations l ON l.location_id=th.src_loc_id
           WHERE th.dest_loc_id !=0 AND level_id=0 AND th.transition_id IN (".$saletrans.") ".$location_type_filter." ".$from_filter." ".$to_filter." GROUP BY th.src_loc_id,es.pid,th.tp_id");

      }
      else{
        $data = [];
      }
      return json_encode($data);      
    }


public function getproductsandlocationsbycustomerId(){
  $data= Input::all();
  //dd(Input::all());
  $result=array();
  $result['products'] =array();
  $result['locations'] = array();
  $customer_id =Input::get('customer_id');
  if(!empty($customer_id)){
    $result['products'] = DB::table('products')->where('manufacturer_id',$customer_id)->lists('name','product_id');
    $result['locations'] = DB::table('locations')->where('manufacturer_id',$customer_id)->lists('location_name','location_id');
  }

  return $result;
}


public function getLocationTypes(){
  $data=Input::all();
  $location_types =array();
  if(isset($data['customer_id']) && $data['customer_id'] != 0){
    $location_types = DB::table('location_types')->where('manufacturer_id',$data['customer_id'])->lists('location_type_name','location_type_id');
  }

  return $location_types;
}


public function getAllFilterData(){
  $data =Input::all();
  $location_types = [];
  $product_groups = [];
  $products = [];
  $categories = [];
  $result=[];
  if(isset($data['customer_id']) && !empty($data['customer_id'])){
    $cust_id = $data['customer_id'];
    $location_types=DB::table('location_types')->where('manufacturer_id',$cust_id)->where('is_deleted',0)->lists('location_type_name','location_type_id');
        
        $product_groups= DB::table('product_groups')->where('manufacture_id',$cust_id)->lists('name','group_id');
        $categories = DB::table('categories')->where('customer_id',$cust_id)->lists('name','category_id');
        $products=  DB::table('products')->where('manufacturer_id',$cust_id)->lists('name','product_id');    
  }

  $result['location_types'] = $location_types;
  $result['product_groups'] = $product_groups;
  $result['categories'] = $categories;
  $result['products'] = $products;

  return $result;
}


public function getrecievepayment(){


    return view::make('dashboard.payment.recievepayment');
}


public function getinvoicedetails(){
  $data = Input::all();
  $result =[];
  if(isset($data['invoice_number']) && !empty($data['invoice_number'])){
    $payment_history = [];
    
    $invoice = DB::table('invoice')->join('eseal_customer','invoice.customer_id','=','eseal_customer.customer_id')->where('invoice_no',$data['invoice_number'])->first();
    if($invoice){
      $payment_history = DB::table('payment_history')->join('invoice','invoice.invoice_id','=','payment_history.invoice_id')->where('payment_history.invoice_id',$invoice->invoice_id)->get();  
    }
    if($invoice == null){
      $invoice = [];
    }
    $result['invoice_details'] = $invoice;
    $result['payment_history']  = $payment_history;
  }

  return $result;



}


public function savePaymentDetails(){
  $data= Input::all();
  //dd($data);
  if(isset($data['invoice_number']) && !empty($data['invoice_number'])){

    $invoice_id = DB::table('invoice')->where('invoice_no',$data['invoice_number'])->pluck('invoice_id');
    $rec['invoice_id'] = $invoice_id;
    $rec['recieve_date'] = $data['payment_date'];
    $rec['customer_id'] = $data['customer'];
    $rec['payment_method'] = $data['payment_method'];
    $rec['reference_no'] =  $data['reference_no'];
    $rec['amount_recieved'] = $data['amount_recieved'];

    $insert =DB::table('payment_history')->insert($rec); 
    if($insert){
      $invoice_amount = DB::table('invoice')->where('invoice_no',$data['invoice_number'])->take(1)->pluck('amount');
      $email = $data['email'];
      if(!isset($data['send_later'])){
        \Mail::send('emails.invoice_payment',['total'=>$invoice_amount,'data'=>$rec], function($message) use($email)
                            {
                                $message->to($email)->subject('Payment Invoice.');
                            });
      }
      return Redirect::to('/');
    }
    else{
      return "Not Inserted";  
    }
  }
  else{

  }
}




public  function getPhysicalInventoryReport(){

  $cust_id = Session::get('customerId');

  $customers = [];
  //$cust_id ="";
    if($cust_id==0)
        {
            $customers = $this->custRepoObj->getAllCustomers();
        }

        $products = DB::table('products')->where('product_type_id',8003)->lists('name','product_id');
        $locationTypes = DB::table('location_types')->where('manufacturer_id',$cust_id)->lists('location_type_name','location_type_id');
        $locations = DB::table('locations')->where('manufacturer_id',$cust_id)->lists('location_name','location_id');

        // $statistics = DB::table('physical_inventory_statistics')->join('physical_inventory_ref','physical_inventory_ref.ref_id','=','physical_inventory_statistics.ref_id')
        // //->join('physical_inventory_log','physical_inventory_log.ref_id','=','physical_inventory_ref.ref_id')
        // ->join('locations','locations.erp_code','=','physical_inventory_statistics.erp_code')->where(array('physical_inventory_ref.is_deleted'=>0,'physical_inventory_statistics.level'=>0))->groupby('physical_inventory_statistics.erp_code')->selectRaw('locations.location_name as Location,sum(count) as inventory')
        // ->WhereNotNull('physical_inventory_statistics.level')->get();
        // //dd($statistics);
        // $validIots = DB::table('physical_inventory_log')->join('physical_inventory_ref','physical_inventory_ref.ref_id','=','physical_inventory_log.ref_id')
        // ->join('physical_inventory_statistics','physical_inventory_statistics.ref_id','=','physical_inventory_ref.ref_id')
        // ->join('locations','locations.erp_code','=','physical_inventory_statistics.erp_code')
        // ->where(array('physical_inventory_ref.is_deleted'=>0,'physical_inventory_statistics.level'=>0))
        // ->select('iot','location_name')->groupby('iot')->get();//->toArray();
        // //dd($validIots);


        // $iotss =[];
        // foreach($validIots as $iot){
        //   //$iotss['']
        //   if(!isset($iotss[$iot->location_name])){
        //     $iotss[$iot->location_name]=[];
        //   }
        //   array_push($iotss[$iot->location_name],$iot->iot);
        // }
        //->lists('iot');
    //$statistics =DB::table('physical_inventory_ref')->join('physical_inventory_statistics','physical_inventory_ref.ref_id','=','physical_inventory_statistics.ref_id')->leftjoin('products','products.material_code','=','physical_inventory_statistics.material_code')->join('locations','locations.erp_code','=','physical_inventory_statistics.erp_code')->where(array('customer_id'=>2,'physical_inventory_ref.is_deleted'=>0))->get();
    return view::make('dashboard.physicalinventoryreport',compact('customers','cust_id','products','locations','locationTypes'));   
}



public function PhysicalInventoryReportDataWithFilters(){
  $data = Input::all();  
  //$customer_id = $data[]
  $location = isset($data['location'])?$data['location']:[];
  $product = isset($data['product'])?$data['product']:[];
  $location_type = isset($data['location_type'])?$data['location_type']:0;
  $from_date = isset($data['from_date'])?$data['from_date']:"";
  $to_date = isset($data['to_date'])?$data['to_date']:"";
  $invalids = isset($data['invalids'])?$data['invalids']:'false';
  $secondaries = isset($data['secondaries'])?$data['secondaries']:false;  
  $mfg_id = Session::get('customerId');
  
  if($secondaries== 'true'){    
    $level = 1;
  }
  else{    
    $level = 0;
  }

  if(!empty($location) && !is_array($location)){
    $location = explode(',',$location);
  }

  // if(!empty($product) && !(is_array($product))){
  //   $product = explode(',',$product);
  // }
//dd($product);
//filters
  {
      $location_filter = 1; 
    $location_type_filter =1;
    $product_filter = 1;
    $from_datefilter = 1;
    $to_datefilter = 1;
    //$product_type_join =1;
    
    if(count($location)){
     /* if(is_array($location)){
        $location_filter ="l.location_id in (".implode(',',$location).") ";  
      }   */  

       $erpcodes=DB::table('locations as l')->whereIn('location_id',$location)->lists('erp_code');
      $location_filter="pis.erp_code in (".implode(',',$erpcodes).") ";
    }
    if(count($product)){
      if(is_array($product)){
        $product_filter =" p.product_id in (".implode(',',$product).") ";  
      }
      
    }
    if(!empty($location_type)){
      //$product_type_join = " join product_types pt on pt.";
     // $location_type_filter = " l.location_type_id = ".$location_type;

      $erpcodes=DB::table('locations as l')->where('location_type_id',$location_type)->lists('erp_code');
      $location_type_filter="pis.erp_code in (".implode(',',$erpcodes).") ";
    }
    if(!empty($from_date)){
      $from_datefilter = " DATE(datetime) >= '".$from_date."'";
    }
    if(!empty($to_date)){
      $to_datefilter = " DATE(datetime) <= '".$to_date."'";
    }
  }

  if($invalids == "true"){

/*    $query = 'select NULL as eseal_location,pl.location_name as physical_location,l.erp_code,u.username,NULL as material_code,NULL as name,NULL as batch_no,pil.iot as primary_id,pil.`level`,NULL as parent_id,1 as qty,pir.datetime as phydate,pir.remarks as Remarks
 from physical_inventory_ref pir join physical_inventory_log pil on pil.ref_id = pir.ref_id 
join physical_inventory_statistics pis on pis.ref_id = pir.ref_id 
join locations l on pis.erp_code = l.erp_code
left join locations pl on pl.location_id = pir.location_id
join users u on u.user_id=pir.user_id where pil.is_valid =0
 and '.$location_type_filter.' and '.$location_filter.' and '.$from_datefilter.' and '.$to_datefilter.' group by pil.iot';*/

 $query = 'select NULL as eseal_location,(select location_name from locations pl where pl.location_id=pir.location_id)  as physical_location,pis.erp_code,u.username,NULL as material_code,NULL as name,NULL as batch_no,pil.iot as primary_id,pil.`level`,NULL as parent_id,1 as qty,pir.datetime as phydate,pir.remarks as Remarks
 from physical_inventory_ref pir join physical_inventory_log pil on pil.ref_id = pir.ref_id 
join physical_inventory_statistics pis on pis.ref_id = pir.ref_id 
join users u on u.user_id=pir.user_id where pil.is_valid =0
 and '.$location_type_filter.' and '.$location_filter.' and '.$from_datefilter.' and '.$to_datefilter.' group by pil.iot';


//dd($query);
    $result =  DB::select($query); 
  }
  else{

      if(empty($location)&& empty($product) && empty($product_type) && empty($from_date) && empty($to_date)){
/*    $result =  DB::select('select el.location_name as eseal_location,pl.location_name as physical_location,l.erp_code,u.username,p.material_code,p.name,es.batch_no,pil.iot as primary_id,pil.`level`,es.parent_id,1 as qty,pir.datetime as phydate,pir.remarks as Remarks
 from physical_inventory_ref pir join physical_inventory_log pil on pil.ref_id = pir.ref_id 
join physical_inventory_statistics pis on pis.ref_id = pir.ref_id 
join locations l on pis.erp_code = l.erp_code 
left join locations pl on pl.location_id = pir.location_id
left join locations el on el.location_id = pil.eseal_location
join products p on p.material_code = pis.material_code 
join users u on u.user_id=pir.user_id
join eseal_'.$mfg_id.' es on es.primary_id=pil.iot and pil.level='.$level.'
group by pil.iot');*/
$result =  DB::select('select (select location_name from locations el where el.location_id=pil.eseal_location)  as  eseal_location,(select location_name from locations pl where pl.location_id=pir.location_id)  as physical_location,pis.erp_code,u.username,p.material_code,p.name,es.batch_no,pil.iot as primary_id,pil.`level`,es.parent_id,1 as qty,pir.datetime as phydate,pir.remarks as Remarks
 from physical_inventory_ref pir join physical_inventory_log pil on pil.ref_id = pir.ref_id 
join physical_inventory_statistics pis on pis.ref_id = pir.ref_id 
join products p on p.material_code = pis.material_code 
join users u on u.user_id=pir.user_id
join eseal_'.$mfg_id.' es on es.primary_id=pil.iot and pil.level='.$level.'
group by pil.iot');

  }

  else{
    

   /* $query = "select el.location_name as eseal_location,pl.location_name as physical_location,l.erp_code,u.username,p.material_code,p.name,es.batch_no,pil.iot as primary_id,pil.`level`,es.parent_id,1 as qty,pir.datetime as phydate,pir.remarks as Remarks
 from physical_inventory_ref pir join physical_inventory_log pil on pil.ref_id = pir.ref_id 
join physical_inventory_statistics pis on pis.ref_id = pir.ref_id 
join locations l on pis.erp_code = l.erp_code
left join locations pl on pl.location_id = pir.location_id
left join locations el on el.location_id = pil.eseal_location
join products p on p.material_code = pis.material_code 
join users u on u.user_id=pir.user_id
join eseal_".$mfg_id." es on es.primary_id=pil.iot and pil.level= ".$level." 
and ".$location_filter." and ".$location_type_filter." and ".$product_filter." and ".$from_datefilter." and ".$to_datefilter."
group by pil.iot ";*/

$query = "select (select location_name from locations el where el.location_id=pil.eseal_location)  as  eseal_location,(select location_name from locations pl where pl.location_id=pir.location_id)  as physical_location,pis.erp_code,u.username,p.material_code,p.name,es.batch_no,pil.iot as primary_id,pil.`level`,es.parent_id,1 as qty,pir.datetime as phydate,pir.remarks as Remarks
 from physical_inventory_ref pir join physical_inventory_log pil on pil.ref_id = pir.ref_id 
join physical_inventory_statistics pis on pis.ref_id = pir.ref_id 
join products p on p.material_code = pis.material_code 
join users u on u.user_id=pir.user_id
join eseal_".$mfg_id." es on es.primary_id=pil.iot and pil.level= ".$level." 
and ".$location_filter." and ".$location_type_filter." and ".$product_filter." and ".$from_datefilter." and ".$to_datefilter."
group by pil.iot ";
//dd($query);

$result = DB::select($query);
//dd($result);
  }  
  }
  


return json_encode($result);
}



public function physicalinventoryexport(){
  $data = Input::all();
  //dd($data);
  $data = $this->PhysicalInventoryReportDataWithFilters();
  $data = json_decode($data);
//dd($data);

  ob_clean(); ob_end_clean();
  Excel::create('Physical Inventory Report', function($excel) use($data) {

    //$excel->setColumnFormat('F',PHPExcel_Style_NumberFormat::FORMAT_TEXT);

    return $excel->sheet('New sheet', function($sheet) use($data){

        //$sheet->setColumnFormat(array('F'=>'@','H'=>'@'));
         $sheet->setColumnFormat(array(
//     //'B' => '0',
//     //'D' => '0.00',
    
     'F' => PHPExcel_Style_NumberFormat::FORMAT_TEXT,
 ));

         //$sheet->rows($data,NULL);
        //$sheet->prependRow(array('id','kod','...'));
        $sheet->loadView('dashboard.physical_inventory_export_excel',array('data'=>$data));


      });

    })->export('xlsx');

}


public function productionreport(){


Log::info('We are in '.__METHOD__);
  $user_id = Session::get('userId');
  Log::info('User Id : '.$user_id);
  
  $user_details=DB::table('users')->where('user_id','=',$user_id)->get();
  
  //return $user_id;
  $users_token=DB::select('select * from users_token where user_id='.$user_id);
  if(!empty($users_token)){
  $module_id=$users_token[0]->module_id;
  $access_token=$users_token[0]->access_token; 
  }
  else{
    $module_id='';
    $access_token=''; 
  }

$locations =[];
$product_materials=[];
  if(Session::get('customerId')==0){
        $customerId = 0;
        $customers = $this->custRepoObj->getAllCustomers();
      }else{
         $customerId = Session::get('customerId');
         $customers = array();           
         $location_type_id = DB::table('location_types')->where(['manufacturer_id'=>$customerId,'location_type_name'=>'Plant'])->pluck('location_type_id');
         $locations= DB::table('locations')->where(array('manufacturer_id'=>$customerId,'location_type_id'=>$location_type_id,'parent_location_id' =>0))->lists('location_name','location_id');
         $product_materials = DB::table('products')->where(['product_type_id'=>8003,'manufacturer_id'=>$customerId])->lists('material_code','product_id');
      }  
      $cust_table= 'eseal_'.$customerId;
      $sales= [];
     
  

      
      return View::make('reports.productionreport',compact('sales','module_id','access_token','customers','customerId','locations','product_materials'));    
}


public function productionReportData(){
  $location_id = Input::get('location');
  $product_id = Input::get('product_id');
  $from_date = Input::get('from_date');
  $to_date = Input::get('to_date');
  $param = Input::get('param');
  $method = Input::get('method');

  if($param == 1){
    return [];
  }
  if(!empty($from_date)){
    $from_date = Date('Y-m-d H:i:s',strtotime($from_date));  
  }
  if(!empty($to_date)){
    $to_date = Date('Y-m-d H:i:s',strtotime($to_date));  
  }

  if(!empty($product_id) && $product_id !="null"){
    if($method !="export"){
      $product_id = implode(',',$product_id);
    }
    
  }
  else if($product_id =='null'){
    $product_id = null;
  }
  else{
    $product_id = 0;
  }


  if(empty($location_id) || empty($from_date)){
        return [];
  }
  else{

    $sql = "SELECT l.location_name,e.po_number,p.material_code,e.batch_no,e.primary_id,e.parent_id,DATE_FORMAT(th.sync_time,'%Y-%m-%d') AS sync_date,DATE_FORMAT(th.sync_time,'%h:%i:%s') AS sync_time,CASE WHEN e.is_confirmed=0 THEN ''  ELSE e.reference_value END AS GR
  FROM eseal_2 e
  JOIN track_details td ON td.code=e.primary_id
  JOIN track_history th ON th.track_id=td.track_id
  JOIN products p ON p.product_id=e.pid
  JOIN locations l ON l.location_id=th.src_loc_id
  WHERE level_id=0 AND dest_loc_id=0 AND product_type_id=8003 AND parent_id !=0 ";

  $sql .= " and src_loc_id = ".$location_id." and (sync_time >= '". $from_date."' and sync_time <='".$to_date."')";
  if(!empty($product_id)){
    $sql .=" and p.product_id in (".$product_id.")";
  }
  //return $sql;
  $sql .=" group by e.primary_id";
    return DB::select($sql);

  }

}


public function ProductReportExcelExport(){


$export_type =Input::get('export_type');
$data = $this->productionReportData();

  if($export_type == "excel"){

    Excel::create('Production Report', function($excel) use($data) {

        return $excel->sheet('New sheet', function($sheet) use($data){

            $sheet->loadView('reports.productiondata_export_excel',array('data'=>$data));

          });

        })->export('xlsx');

  }
  else if($export_type == "text"){
    $file = fopen(public_path()."/Production_Report.txt","w");

    foreach($data as $row){
      foreach($row as $c){
        fputs($file,$c);
        fputs($file,"\t");
      }
      fputs($file,"\r\n");
    }
    //fputs($file, implode('\t',$data));
    fclose($file);

            $headers = array(
                  'Content-Type: text/plain',
                );
    return Response::download(public_path()."/Production_Report.txt","Production_Report.txt" , $headers);  


  }



}

public function getDataAgainstIot($location_id,$product_id,$storage_location,$from_date,$to_date,$cust_id=0,$excelexport = 0)
    {
        $data = Input::all();


      //check if the customer id is 0 while method is called from excelexport function..

      if($excelexport == 1 && $cust_id == 0){
        return [];
      }

      
        $user_id = Session::get('userId');
        $user_details=DB::table('users')->where('user_id','=',$user_id)->get();
        //$cust_id=$user_details[0]->customer_id;
        $location_filter = "";
        $product_filter = "";
        $fromfilter = "";
        $tofilter = "";
        $product_group_filter = "";
        $location_type_filter = "";
        $category_filter = "";
        $storageLocationfilter = "";
        $customer_details = [];
        $storage_location_exists = false;
        if(Schema::hasColumn('eseal_'.$cust_id, 'storage_location'))  //check whether users table has email column
        {
         $storage_location_exists = true;
        }
       // dd($storage_location_exists);
        if(empty($cust_id)){
           return json_encode($customer_details);
        }
        if(!empty($data['product_group'])){
            $product_group_filter = " and p.group_id = ".$data['product_group'];
        }
        if(!empty($data['location_type'])){
            $location_type_filter = " and l.location_type_id = ".$data['location_type'];
        }
        if(!empty($data['category'])){
            $category_filter = " and p.category_id = ".$data['category'];
        }
        if(!empty($location_id)){
            $location_filter=" and l.location_id in(".$location_id.")";
        }
        if(!empty($product_id)){
            $product_filter =" and p.product_id in( ".$product_id .")";
        }
        if(!empty($from_date)){
            $fromfilter=" and DATE(update_time) >= '".$from_date."'";
        }
        if(!empty($to_date)){
            $tofilter=" and DATE(update_time) <= '".$to_date."'";
        }

        $select_query = "select e.primary_id as iot,e.parent_id as parent,
        p.material_code as 'material_code',p.name as 'product_name',
        (select location_name from locations l where l.location_id = th.src_loc_id ) as 'location_name',c.name as 'category_name',lt.location_type_name,l.erp_code,
        e.mfg_date as 'mfg_date',concat(e.pkg_qty,' ',(select uom_name from uom_classes where id = p.uom_class_id)) as 'availabel_inventory',
        am.value as expiry_date,
        (select case when 0 or p.expiry_period = '' or p.expiry_period IS NULL then '' when datediff(CURDATE(),e.mfg_date) > expiry_period then 'expired' else datediff(CURDATE(),e.mfg_date) end) as 'age'";
        

        if($storage_location_exists){
          if(!empty($storage_location) && (trim(strtolower($storage_location)) !="select storage location")){
            $storageLocationfilter=" and e.storage_location= '".$storage_location."'";
          }  
          $select_query .= ",e.storage_location";
          
        }
        
        if(!empty($cust_id)){
            $cust_table = "eseal_".$cust_id;
        }
        else{
            $cust_table ="";
        }

        $joinquery = " from ".$cust_table." e join products p on (p.product_id = e.pid ".$product_group_filter.$product_filter." ) join track_history th on (th.track_id = e.track_id and th.dest_loc_id = 0)       join locations l on (l.location_id = th.src_loc_id ".$location_type_filter.$location_filter." ) join categories c on (p.category_id = c.category_id ".$category_filter.") join location_types lt on l.location_type_id = lt.location_type_id  left join attribute_mapping am on (am.attribute_map_id = e.attribute_map_id and am.attribute_name = 'date_of_exp')   where level_id = 0 and e.is_active = 1 ";

        $filters = $storageLocationfilter.$fromfilter." ".$tofilter;

        $query = $select_query." ".$joinquery." ".$filters;

        $customer_details = DB::select($query);
        if($excelexport == 1){
          return $customer_details;
        }

        return json_encode($customer_details);
    }


    public function testingTransactionCall(){
      $time =Input::get('time');
      sleep($time);
      return "Successfully Completed"; 
    }

}

