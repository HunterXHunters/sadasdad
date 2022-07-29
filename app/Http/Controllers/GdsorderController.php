<?php

use Central\Repositories\OrderRepo;
use Central\Repositories\CustomerRepo;


/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

Class GdsorderController extends BaseController{
   

    /*    public function index()
    {
      return View::make('gdsorders/gdsordersreport');
    }*/
      public function gdsordersreport()
    {
         $fromDate = Input::get('from_date');
         $toDate = Input::get('to_date');
         $ordstatus=Input::get('order_status');
        /*print_r($fromDate);
         print_r($toDate);
         print_r($ordstatus);*/
         if($ordstatus != 'All')
         {

          $gdsordinfo = db::table('gds_orders')
                   ->join('gds_order_products','gds_orders.gds_order_id','=','gds_order_products.gds_order_id')
                   ->join('manf_charges','manf_charges.reference_id','=','gds_orders.gds_order_id')
                   ->join('master_lookup','master_lookup.value','=','gds_orders.order_status_id')
      ->select(DB::raw('count(gds_orders.gds_order_id) order_count,sum(manf_charges.charges) charges,sum(gds_orders.total) order_total,sum(gds_orders.ship_total) ship_total,sum(gds_orders.tax_total) tax_total,sum(gds_orders.discount) discount_total,sum(gds_order_products.qty) qty,gds_order_products.price-gds_order_products.cost as profit','master_lookup.name'))
       ->whereBetween('gds_orders.order_date',[$fromDate,$toDate])
       ->where('master_lookup.name','=',$ordstatus)
       ->where('master_lookup.category_id','=',17)
      ->first();
       }
         else
         {
          $gdsordinfo = db::table('gds_orders')
                   ->join('gds_order_products','gds_orders.gds_order_id','=','gds_order_products.gds_order_id')
                   ->join('manf_charges','manf_charges.reference_id','=','gds_orders.gds_order_id')
                   ->join('master_lookup','master_lookup.value','=','gds_orders.order_status_id')
      ->select(DB::raw('count(gds_orders.gds_order_id) order_count,sum(manf_charges.charges) charges,sum(gds_orders.total) order_total,sum(gds_orders.ship_total) ship_total,sum(gds_orders.tax_total) tax_total,sum(gds_orders.discount) discount_total,sum(gds_order_products.qty) qty,gds_order_products.price-gds_order_products.cost as profit','master_lookup.name'))
       ->whereBetween('gds_orders.order_date',[$fromDate,$toDate])
       ->where('master_lookup.category_id','=',17)
      ->first();
         }
     

      $data = db::table('master_lookup')
              ->select('master_lookup.name','master_lookup.id')
              ->where('master_lookup.category_id','=',17)
              ->get();
            
   
              

      /*
      $last=DB::getQueryLog();
      print_r($last);die;*/
      return View::make('gdsorders/gdsordersreport')->with(array('gdsordinfo'=>$gdsordinfo,'data'=>$data,'fromDate'=>$fromDate,'toDate'=>$toDate,'ordstatus'=>$ordstatus));
      
    }
    public function show()
    {  
        $fromDate = (Input::get('from_date') == '') ? date("Y-m-d",strtotime("-1 week")) : Input::get('from_date');
        $toDate = (Input::get('to_date') == '') ? date('Y-m-d') : Input::get('to_date');
         $ordstatus=Input::get('order_status');
         // print_r($fromDate);die;
        $gdsorder = array();
        $finalgdsorder = array();
         if($ordstatus != 'All')
         {
        $orderdet = DB::table('gds_orders')
                  ->join('Channel','Channel.channel_id','=','gds_orders.channel_id')
                  ->join('master_lookup','master_lookup.value','=','gds_orders.order_status_id')
                  ->select('gds_orders.gds_order_id as OrderId','Channel.channnel_name','gds_orders.order_date','gds_orders.firstname','gds_orders.lastname','gds_orders.total','master_lookup.name as order_status',db::raw('concat(gds_orders.firstname,gds_orders.lastname) as full_name'))
                  ->whereBetween('gds_orders.order_date',[$fromDate,$toDate])
                  ->where('master_lookup.category_id','=',17)
                  ->where('master_lookup.name','=',$ordstatus)
                  ->get();
          }        
         else
         {
            $orderdet = DB::table('gds_orders')
                  ->join('Channel','Channel.channel_id','=','gds_orders.channel_id')
                  ->join('master_lookup','master_lookup.value','=','gds_orders.order_status_id')
                  ->select('gds_orders.gds_order_id as OrderId','Channel.channnel_name','gds_orders.order_date','gds_orders.firstname','gds_orders.lastname','gds_orders.total','master_lookup.name as order_status',db::raw('concat(gds_orders.firstname,gds_orders.lastname) as full_name'))
                  ->whereBetween('gds_orders.order_date',[$fromDate,$toDate])
                  ->where('master_lookup.category_id','=',17)
                  ->get();
         }         
      /* $last=db::getQuerylog();
      print_r($last);die();   */       
        
        //print_r($orderdet);die;
        $gdsorder_details=json_decode(json_encode($orderdet),true);

       foreach($gdsorder_details as $value)
       {  
       
         //return $customer_details;
         $gdsorder['OrderId'] = $value['OrderId'];
         $gdsorder['channnel_name'] = $value['channnel_name'];
         $gdsorder['total'] = $value['total'];
         $gdsorder['order_date'] = $value['order_date'];
         $gdsorder['order_status'] = $value['order_status'];
         $gdsorder['full_name'] = $value['full_name'];
        
         $finalgdsorder[] = $gdsorder;
        }
         return json_encode($finalgdsorder);
    }

 }
