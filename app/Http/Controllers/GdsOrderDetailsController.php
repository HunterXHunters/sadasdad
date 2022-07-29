<?php

class GdsOrderDetailsController extends BaseController{


	public function index()
	{
		return View::make('gdsOrderDetails.index');
	}

	public function show()
	{
		$data = DB::table('gds_orders')
						->leftJoin('Channel','gds_orders.channel_id','=','Channel.channel_id')
						->leftJoin('master_lookup','gds_orders.order_status_id','=','master_lookup.value')
						->leftJoin('currency','currency.currency_id','=','gds_orders.currency_id')
						->select('gds_orders.*','currency.symbol_left as code','Channel.channnel_name','master_lookup.name as stat')
						->get();
		
		$i=0;    
        foreach($data as $value)
        {  
        	$value->name = $value->firstname.' '.$value->lastname;
        	$value->price = $value->code.' '.$value->total;
        	
          $data[$i]->actions = '<span style="padding-left:20px;" ><a href="gdsOrders/edit/'.$value->gds_order_id.'"><span class="badge bg-light-blue"><i class="fa fa-pencil"></i></span></a>
           <span style="padding-left:20px;" ></span>';
            $i++;     
        }
        /*dd($data);*/
     return json_encode($data);
	}

	public function edit($order_id)
	{
		$order_status_value = 17;
		$payment_method_value = 28;
		$payment_status_value = 22;
		$data = DB::table('gds_orders')
						->leftJoin('Channel','gds_orders.channel_id','=','Channel.channel_id')
						->leftJoin('gds_customer','gds_orders.gds_cust_id','=','gds_customer.gds_cust_id')
						->leftJoin('gds_order_products','gds_orders.gds_order_id','=','gds_order_products.gds_order_id')
						->where('gds_orders.gds_order_id',$order_id)
						->select('gds_orders.*','gds_customer.*','Channel.channnel_name','gds_order_products.pid','gds_order_products.pname','gds_order_products.qty','gds_order_products.price','gds_order_products.tax','gds_order_products.total as subtotal','gds_order_products.discount')
						->get();
				/*		dd($data);*/
		$order_stat = DB::table('master_lookup')
						->Join('gds_orders','gds_orders.order_status_id','=','master_lookup.value')
						->join('lookup_categories','lookup_categories.id','=','master_lookup.category_id')
						->where('lookup_categories.name','Order Status')
						->select('master_lookup.name')
						->first();
		$order_stat = isset($order_stat->name) ? $order_stat->name :'';
		$orderStatus = DB::table('master_lookup')
						->leftjoin('lookup_categories','lookup_categories.id','=','master_lookup.category_id')
						->where('lookup_categories.name','Order Status')
						->get(array('master_lookup.value','master_lookup.name'));
					/*echo "<pre>"; print_r($orderStatus); die();*/
						/*dd($orderStatus);*/
		$payment_method = DB::table('gds_orders_payment')
						->Join('master_lookup','gds_orders_payment.payment_method_id','=','master_lookup.value')
						->Join('currency','gds_orders_payment.currency_id','=','currency.currency_id')
						->where('gds_orders_payment.gds_order_id',$order_id)
						->where('master_lookup.category_id',$payment_method_value)
						->select('master_lookup.name','gds_orders_payment.*','currency.code','currency.symbol_left')
						->get();
		$payment_status = DB::table('gds_orders_payment')
						->Join('master_lookup','gds_orders_payment.payment_status_id','=','master_lookup.value')
						->where('master_lookup.category_id',$payment_status_value)
						->where('gds_orders_payment.gds_order_id',$order_id)
						->select('master_lookup.name')
						->get();
		$charges = DB::table('manf_charges')
						->Join('gds_orders','reference_id','=','gds_orders.gds_order_id')
						->Join('channel_service_type','manf_charges.service_type_id','=','channel_service_type.service_type_id')
						->select('charges','eseal_fee','channel_service_type.service_name')
						->get();
		/*dd($charges,$data);*/
		/*dd($payment_method);*/
		$shipper = DB::table('gds_order_shipping_details')
						->leftJoin('shipping_services','shipping_services.service_id','=','gds_order_shipping_details.service_id')
						->where('gds_order_shipping_details.gds_order_id',$order_id
							)
						->select('gds_order_shipping_details.*','shipping_services.service_name')
						->get();
		/*dd($shipper);*/
		/*dd($order_stat);*/
		/*$value = DB::getQuerylog();*/
		/*dd($value);*/
		/*dd($data);*/
		$billing_address = DB::table('gds_orders_addresses')
						->leftJoin('gds_orders','gds_orders.gds_order_id','=','gds_orders_addresses.gds_order_id')
						->leftJoin('countries','gds_orders_addresses.country_id','=','countries.country_id')
						->leftJoin('zone','gds_orders_addresses.state_id','=','zone.zone_id')
						
						->where('gds_orders_addresses.address_type','=','billing')
						->select('gds_orders_addresses.*','countries.name as country','zone.name as state')
						->get();
				/*dd($billing_address);*/
		$shipping_address = DB::table('gds_orders')
						->leftJoin('gds_orders_addresses','gds_orders.gds_order_id','=','gds_orders_addresses.gds_order_id')
						->leftJoin('countries','gds_orders_addresses.country_id','=','countries.country_id')
						->leftJoin('zone','gds_orders_addresses.state_id','=','zone.zone_id')
						->where('gds_orders_addresses.address_type','=','shipping')
						->select('gds_orders_addresses.*','countries.name as country','zone.name as state')
						->get();
		$order =2;
		$comment = DB::table('gds_orders_comments')
						->leftJoin('gds_orders','gds_orders.gds_order_id','=','gds_orders_comments.entity_id')
						->where('gds_orders_comments.comment_type',$order )
						->select('gds_orders_comments.*','gds_orders.order_status_id','gds_orders.order_date')
						->get();
		/*dd($comment);*/
		/*dd($data,$billing_address,$shipping_address);*/
		

		/*$mytime = Carbon\Carbon::now();
		echo $mytime->toDateTimeString();
		echo date('Y-m-d H:i:s'); die();*/
		return View::make('gdsOrderDetails.editGdsOrderDetails')
								->with('data',$data)
								->with('billing_address',$billing_address)
								->with('shipping_address',$shipping_address)
								->with('comment',$comment)
								->with('order_stat',$order_stat)
								->with('shipper',$shipper)
								->with('payment_method',$payment_method)
								->with('payment_status',$payment_status)
								->with('charges',$charges)
								->with('orderStatus',$orderStatus);
	}

	public function saveComment($gds_order_id){
		$order = 2;
		/*dd($gds_order_id);*/
		/*return Input::get('gds_order_id');*/
		$data = Input::all();
		/*dd($data);*/
		DB::table('gds_orders')
					->where('gds_order_id',$gds_order_id)
					->update(['order_status_id' => $data['orderStatus']]);
		DB::Table('gds_orders_comments')->insert(['entity_id' => $gds_order_id,
                     'comment' => $data['order_comment'],
                     'comment_type' => $order,
                     'comment_date' => date('Y-m-d H:i:s')
                      ]);
       	return 1;
	}

	public function confirmOrder($gds_order_id){

		$orderStatus = DB::table('master_lookup')
						->join('lookup_categories','lookup_categories.id','=','master_lookup.category_id')
						->where('lookup_categories.name','Order Status')
						->where('master_lookup.name','Confirmed')
						->select('master_lookup.value')
						->first();

		DB::table('gds_orders')
					->where('gds_order_id',$gds_order_id)
					->update(['order_status_id' => $orderStatus->value]);
		return 1;
	}

	public function editInvoice($id){

		return View::make('gdsOrderDetails.editGdsOrderInvoiceDetails');
	}
	public function shipmentsIndex($id)
	{
		return View::make('gdsOrderDetails.shipmentsIndex')->with('id',$id);
	}
	public function showShipmentsIndex($id)
	{

		// its clearly shown in the route kadha Lasya.. ah URL ki e data return avuddhi anthe !
		$data = DB::table('gds_ship_grid')
						->where('gds_order_id',$id)
						->get();
		$i =0;
		foreach($data as $value)
        {
            
          $data[$i]->actions = '<span style="padding-left:20px;" ><a href="/gdsOrders/editShipments/'.$id.'/'.$value->gds_ship_grid_id.'"><span class="badge bg-light-blue"><i class="fa fa-pencil"></i></span></a>';
            $i++;     
        }
     return Response::json($data);
		
	}
	public function editShipments($id,$grid_id)
	{
		$order_status_value = 17;
		$data = DB::table('gds_orders')
						->leftJoin('Channel','gds_orders.channel_id','=','Channel.channel_id')
						->leftJoin('gds_customer','gds_orders.gds_cust_id','=','gds_customer.gds_cust_id')
						->where('gds_orders.gds_order_id',$id)
						->select('gds_orders.*','gds_customer.*','Channel.channnel_name')
						->get();
		$order_stat = DB::table('master_lookup')
						->Join('gds_orders','gds_orders.order_status_id','=','master_lookup.value')
						->join('lookup_categories','lookup_categories.id','=','master_lookup.category_id')
						->where('lookup_categories.name','Order Status')
						->select('master_lookup.name')
						->first();
		$order_stat = isset($order_stat->name) ? $order_stat->name :'';
		$shipping_address = DB::table('gds_orders_ship_details')
						->Join('gds_orders','gds_orders.gds_order_id','=','gds_orders_ship_details.gds_order_id')
						->leftJoin('countries','gds_orders_ship_details.country_id','=','countries.country_id')
						->leftJoin('zone','gds_orders_ship_details.state_id','=','zone.zone_id')
						->select('gds_orders_ship_details.*','countries.name as country','zone.name as state')
						->first();
			/*dd($shipping_address);*/
		$comment = DB::table('gds_orders_comments')
						->leftJoin('gds_orders_ship_details','gds_orders_ship_details.gds_order_id','=','gds_orders_comments.entity_id')
						->select('gds_orders_comments.*')
						->get();
		$addTrack = DB::table('carriers')
							->leftJoin('shipping_services','carriers.carrier_id','=','shipping_services.carrier_id')
							->select('shipping_services.service_name','carriers.carrier_id','carriers.name as carrier')
							->get();
		$shipper = DB::table('gds_order_shipping_details')
						->leftJoin('shipping_services','shipping_services.service_id','=','gds_order_shipping_details.service_id')
						->where('gds_order_shipping_details.gds_order_id',$id)
						->select('gds_order_shipping_details.service_cost','shipping_services.service_name')
					->first();
					/*dd($shipper);*/
	
		$tracking_data = DB::table('gds_orders_shipment_track')
								->Join('carriers','carriers.carrier_id','=','ship_service_id')
								->Join('gds_ship_items','gds_ship_items.order_ship_id','=','gds_orders_shipment_track.order_ship_id')
								->Join('products','products.product_id','=','gds_orders_shipment_track.pid')
								->where('gds_ship_items.gds_order_id',$id)
								->select('gds_orders_shipment_track.ship_method','gds_orders_shipment_track.order_ship_id','track_number','ship_service_id','gds_orders_shipment_track.qty as ship_qty','carriers.name','gds_ship_items.pid','products.name as prod_name','products.sku','gds_ship_items.qty as quantity')
								->get();

		$trackData = DB::table('gds_orders_shipment_track')
								->Join('carriers','carriers.carrier_id','=','ship_service_id')
								->Join('gds_ship_items','gds_ship_items.order_ship_id','=','gds_orders_shipment_track.order_ship_id')
								->Join('products','products.product_id','=','gds_orders_shipment_track.pid')
								->where('gds_ship_items.gds_order_id',$id)
								->select('gds_orders_shipment_track.ship_method','gds_orders_shipment_track.order_ship_id','track_number','ship_service_id','gds_orders_shipment_track.qty as ship_qty',DB::raw('gds_ship_items.qty - sum(gds_orders_shipment_track.qty) as avail_qty '),'carriers.name','gds_ship_items.pid','products.name as prod_name','products.sku','gds_ship_items.qty as quantity')
								->groupBy('gds_orders_shipment_track.pid')
								->get();
			 	
		$prodInfo = DB::table('gds_ship_items')
							->leftJoin('products','products.product_id','=','pid')
							->select('products.name as prod','products.sku','gds_ship_items.*')
							->where('gds_order_id',$id)
							->get();
		$ship =1;
		$comment = DB::table('gds_orders_comments')
						->leftJoin('gds_orders_ship_details','gds_orders_ship_details.order_ship_id','=','gds_orders_comments.entity_id')
						->where('gds_orders_comments.comment_type',$ship)
						->select('gds_orders_comments.*')
						->get();
		return View::make('gdsOrderDetails.editGdsOrderShipmentDetails')
											->with('data',$data)
											->with('order_stat',$order_stat)
											->with('shipping_address',$shipping_address)
											->with('comment',$comment)
											->with('addTrack',$addTrack)
											->with('tracking_data',$tracking_data)
											->with('shipper',$shipper)
											->with('prodInfo',$prodInfo)
											->with('trackData',$trackData);
	}

	public function getShipTitle($carrier_id)
	{
		$service_name = DB::table('shipping_services')
							->where('carrier_id',$carrier_id)
							->select('service_name')
							->first();
		/*dd($carrier_id);*/
		return ($service_name) ? $service_name->service_name : '';
	}

	public function addTrack($id)
	{	
		
		/*dd($data);*/
		return Response::json($id);
	}

	public function getData($id)
	{
		$data = Input::all();
		return $data;
	}

	public function saveTrack($id){
		$check = Input::get('myTextEditBox');
		$data[] = Input::all();
		$id = Input::get('gds_order_id');

		$i=0;	
		$array=array();

		// print_r($data);exit;
		foreach ($data as  $value) {
			
			/*print_r($value['myTextEditBox']['check'][0]);exit();*/
			$length=sizeof($value['myTextEditBox']['check']);/*print_r($length);exit();*/
			$checkedElements = $value['myTextEditBox']['check'];
			$checkedIndex = array();
			foreach($checkedElements as $check => $checkValue)
			{
				$checkedIndex[] = $check;
			}
			
			if(!empty($checkedIndex))
			{
			
				foreach($checkedIndex as $index)
				{
					$array['qty'] = $value['myTextEditBox']['ship_quantity'][$index];
					$array['product_id'] = $value['myTextEditBox']['product_id'][$index];
					$array['chk'] =$value['myTextEditBox']['check'][$index];
					$final_array[]=$array;

					
				}		
			}
		
		/*print_r($final_array);  exit();*/
		}
		foreach ($final_array as $key => $value) {
			 $order_ship_id = DB::table('gds_ship_items')
		 					->where('pid',$value['product_id'])
		 					->where('gds_order_id',$id)
		 					->select('order_ship_id')
		 					->first();
		 	/*echo "<pre>"; print_r($order_ship_id);*/
			if($value['chk'] == "on")
			{
				$track = DB::table('gds_orders_shipment_track')
		            ->where('gds_order_id', $id)
		            ->insert(array('ship_method'=>Input::get('title'),
		            'order_ship_id' => $order_ship_id->order_ship_id,
		            'ship_service_id'=>Input::get('carrier'),
		            'track_number'=>Input::get('track_id'),
		            'qty' =>$value['qty'],
		            'pid' =>$value['product_id'],
		            'gds_order_id' =>$id
		             ));
            }
        }

        return Response::json([
        'status' => true,
        'message'=>'Sucessfully updated.'
      ]);

	}

	public function saveShipComment($order_ship_id){
		$ship =1;
		/*dd($gds_order_id);*/
		/*return Input::get('gds_order_id');*/
		$data = Input::all();
		/*dd($data);*/
		DB::Table('gds_orders_comments')->insert(['entity_id' => $order_ship_id,
                     'comment' => $data['order_comment'],
                     'comment_type' => $ship,
                     'comment_date' => date('Y-m-d H:i:s')
                      ]);
       	return 1;
	}
}
?>