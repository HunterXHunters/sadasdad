<?php
namespace App\Repositories; 
use Token;
use User;
use DB;  //Include laravel db class
use Session;
use Response;
use Exception;

Class MarketPlaceRepo {

    public function getDate(){
        return date("Y-m-d H:i:s");
    }

    public function getGdsProducts($data) {
        try {
            \Log::info(__FUNCTION__ . ' : ' . print_r($data, true));
            $channel_name = isset($data['channel_name']) ? $data['channel_name'] : '';                   
            $status =1;
            $message = 'Data found';
            $result = array();
            if(empty($channel_name))
                throw new Exception ('Channel Name not passed');

            $prods = DB::table('channel_product_add_update')
                    ->leftJoin('products as prod', 'channel_product_add_update.product_id', '=', 'prod.product_id')
                    ->leftJoin('product_inventory', 'product_inventory.product_id', '=', 'channel_product_add_update.product_id')
                    ->leftJoin('locations', 'product_inventory.location_id', '=', 'locations.location_id')
                    ->leftJoin('product_attributes', 'product_attributes.product_id', '=', 'prod.product_id')
                    ->leftJoin('attributes', 'product_attributes.attribute_id', '=', 'attributes.attribute_id')
                    ->leftJoin('channel_categories', 'channel_categories.ebutor_category_id', '=', 'channel_product_add_update.category_id')
                    ->where('is_added', 1);
            
                $channel_id = DB::table('channel')->where('channnel_name', $channel_name)->pluck('channel_id');
                if (empty($channel_id))
                    throw new Exception('Channel doesnt exist');

                $prods->where('channel_product_add_update.channel_id', $channel_id);
            
            $result = $prods->select(DB::raw('sum(available_inventory) as qty'), DB::raw('CONCAT(group_concat(product_attributes.value,"$",attributes.name)) as product_attributes'), 'prod.name as Title', 'prod.description as Description', 'prod.product_id', 'prod.mrp as StartPrice', 'prod.image as PictureURL', 'channel_categories.channel_category_id', 'prod.upc', 'locations.location_address', 'locations.city')
                    ->where('available_inventory', '>', 0)
                    ->groupBy('prod.product_id')
                    ->take(5)
                    ->get();

            if (empty($result))
                throw new Exception('Data not found');

        } catch (Exception $e) {
            $status = 0;
            $message = $e->getMessage();
            \Log::info('Error:' . $message);
        }
        return json_encode(['Status'=>$status,'Message'=>$message,'Data'=>$result]);
    }

    public function getUpdateItem($data) {
        try {
            \Log::info(__FUNCTION__ . ' : ' . print_r($data, true));
            $status =1;
            $message = 'Data found';
            $result = array();
            $channel_name = isset($data['channel_name']) ? $data['channel_name'] : '';                   
               if(empty($channel_name))
                throw new Exception ('Channel Name not passed');

            $prods = DB::table('products as prod')
                    ->leftJoin('channel_product_add_update as cpau', 'prod.product_id', '=', 'cpau.product_id')
                    ->leftJoin('product_attributes', 'product_attributes.product_id', '=', 'prod.product_id')
                    ->leftJoin('attributes', 'product_attributes.attribute_id', '=', 'attributes.attribute_id')
                    ->select(DB::raw('CONCAT(group_concat(product_attributes.value,"$",attributes.name)) as product_attributes'), 'prod.name as Title', 'prod.description as Description', 'prod.product_id', 'prod.mrp as StartPrice', 'cpau.channel_product_key as ItemID', 'prod.image as PictureURL')
                    ->where('is_update', '=', '1');
            
                $channel_id = DB::table('channel')->where('channnel_name', $channel_name)->pluck('channel_id');
                if (empty($channel_id))
                    throw new Exception('Channel doesnt exist');

                $prods->where('cpau.channel_id', $channel_id);
            
            $result = $prods->groupBy('prod.product_id')
                    ->take(5)
                    ->get();
            if (empty($result))
                throw new Exception('Data not found');
        } catch (Exception $e) {
            $status = 0;
            $message = $e->getMessage();
            \Log::info('Error:' . $message);
        }
        return json_encode(['Status'=>$status,'Message'=>$message,'Data'=>$result]);
    }

    public function getChannelUrl($data) {
        try {
            \Log::info(__FUNCTION__ . ' : ' . print_r($data, true));
            $status =1;
            $message = 'Data found';
            $result = array();
            $channel_name = isset($data['channel_name']) ? $data['channel_name'] : '';                   
            $token = isset($data['token_name']) ? $data['token_name'] : '';                   
            if (empty($channel_name) || empty($token))
                throw new Exception('Parameter Missing');

            $channel_id = DB::table('channel')->where('channnel_name', $channel_name)->pluck('channel_id');
            if (empty($channel_id))
                throw new Exception('Channel doesnt exist');

            $query = DB::table('channel_configuration as conf')
                    ->leftJoin('channel as ch', 'conf.channel_id', '=', 'ch.channel_id');
            if (!empty($token))
                $query->where('conf.Key_name', $token);

            $result = $query->where('conf.channel_id', $channel_id)
                    ->get(['conf.Key_value', 'ch.channel_url', 'Key_name']);

            if (empty($result))
                throw new Exception('Data not found');
        } catch (Exception $e) {
            $status =0;
            $message = $e->getMessage();
            \Log::info('Error:' . $message);
        }
        return json_encode(['Status'=>$status,'Message'=>$message,'Data'=>$result]);
    }

    public function updateChannelProduct($data) {
        try {
            \Log::info(__FUNCTION__ . ' : ' . print_r($data, true));
            $status =1;
            $channel_name = isset($data['channel_name']) ? $data['channel_name'] : '';                   
            $product_id = isset($data['product_id']) ? $data['product_id'] : '';                   
            $fieldJson = isset($data['fields']) ? $data['fields'] : '{}';
            $fieldArray = json_decode($fieldJson,true);                   
            
            if (empty($channel_name) || empty($product_id) || !is_array($fieldArray) || empty($fieldArray))
                throw new Exception('Parameter Missing');

            $channel_id = DB::table('channel')->where('channnel_name', $channel_name)->pluck('channel_id');
            if (empty($channel_id))
                throw new Exception('Channel doesnt exist');

            $productExists = DB::table('channel_product_add_update')->where('product_id', $product_id)->count();
            if (!$productExists)
                throw new Exception('Product doesnt exist');

            DB::table('channel_product_add_update')
                    ->where('product_id', $product_id)
                    ->where('channel_id', $channel_id)
                    ->update($fieldArray);
            $message = 'Updated channel product Successfully';
        } catch (Exception $e) {
            $status =0;
            $message = $e->getMessage();
            \Log::info('Error:' . $message);
        }
        return json_encode(['Status'=>$status,'Message'=>$message]);
    }

    public function addChannelProduct($data) {
        try {
            \Log::info(__FUNCTION__ . ' : ' . print_r($data, true));
            $status =1;
            $channel_name = isset($data['channel_name']) ? $data['channel_name'] : '';                   
            $fieldJson = isset($data['fields']) ? $data['fields'] : '{}';                   
            $fieldArray = json_decode($fieldJson,true);

            if (empty($channel_name) || empty($fieldArray) || !is_array($fieldArray))
                throw new Exception('Parameter Missing');

            $channel_id = DB::table('channel')->where('channnel_name', $channel_name)->pluck('channel_id');
            if (empty($channel_id))
                throw new Exception('Channel doesnt exist');

            $fieldArray['channel_id'] = $channel_id;
            DB::table('channel_product_add_update')
                    ->insert($fieldArray);
            $message = 'Product added Successfully';
        } catch (Exception $e) {
            $status = 0;
            $message = $e->getMessage();
            \Log::info('Error:' . $message);
        }
        return json_encode(['Status'=>$status,'Message'=>$message]);
    }

    

    public function getUpdatedImage($data) {
        try {
            \Log::info(__FUNCTION__ . ' : ' . print_r($data, true));
            $status =1;
            $message = 'Data found';
            $result = array();
            $channel_name = isset($data['channel_name']) ? $data['channel_name'] : '';                   

            if (empty($channel_name))
                throw new Exception('Parameter Missing');

            
                $channel_id = DB::table('channel')->where('channnel_name', $channel_name)->pluck('channel_id');
                if (empty($channel_id))
                    throw new Exception('Channel doesnt exist');
            
            $result = DB::table('products as prodprice')
                    ->leftJoin('channel_product_add_update as cod', 'prodprice.product_id', '=', 'cod.product_id')
                    ->select('cod.channel_product_key as ItemID', 'prodprice.product_id as product_id', 'prodprice.image as image', 'prodprice.sku as sku')
                    ->where('cod.is_added', 0)
                    ->where('cod.channel_id', $channel_id)
                    ->where('cod..channel_product_key', '<>', empty('cod.channel_product_key'))
                    ->get();
            if(empty($result))
                throw new Exception('Data not found');        
        } catch (Exception $e) {
            $status =0;
            $message = $e->getMessage();
            \Log::info('Error:' . $message);
        }
        return json_encode(['Status'=>$status,'Message'=>$message,'Data'=>$result]);
    }


    public function cancelOrder($data){
        try{
            \Log::info(__FUNCTION__ . ' : ' . print_r($data, true));
            $status =1;
            $channel_order_id = isset($data['channel_order_id']) ? $data['channel_order_id'] : '';                   
            $status_id = isset($data['status_id']) ? $data['status_id'] : '';                   
            $dispute_id = isset($data['dispute_id']) ? $data['dispute_id'] : '';                   

            if(empty($channel_order_id))
                throw new Exception('Channel Order Id is not passed');
            
            DB::beginTransaction();
            
            $isOrderExists = DB::table('channel_orders')->where('channel_order_id',$channel_order_id)->first(['channel_id','gds_order_id','order_id']);
            if(!$isOrderExists)
                throw new Exception('Order Id doesnt Exist');

            $disputeExists = DB::table('channel_order_disputes')->where('order_id',$channel_order_id)->count();
            
            if(!$disputeExists){
            Log::info('Inserting Dispute');   
            $updateStatus = DB::table('master_lookup')->where('name','Canceled')->pluck('value');

            DB::table('channel_orders')->where('channel_order_id',$channel_order_id)->update(['order_status'=>'CANCELLED']);
            DB::table('gds_orders')->where('gds_order_id',$isOrderExists->gds_order_id)->update(['order_status_id'=>$updateStatus]);

            $itemDetails = DB::table('channel_order_item_details')->where('order_id',$isOrderExists->order_id)->first(['channel_item_id','transaction_id']);
            
            DB::table('channel_order_disputes')
                      ->insert([
                        'ack'=>'Success',
                        'order_id'=>$channel_order_id,
                        'item_id'=>$itemDetails->channel_item_id,
                        'dispute_status'=>'WaitingForBuyerResponse',
                        'dispute_modified_time'=>$this->getDate(),
                        'dispute_reason'=>'TransactionMutuallyCanceled',
                        'raise_dispute'=>1,
                        'transaction_id'=>$itemDetails->transaction_id
                        ]);
            $message = 'Dispute Inserted Successfully';                 
            }
            else{
             Log::info('Updating Dispute'); 
             if(empty($status_id) || empty($dispute_id))
                throw new Exception('Parameters not passed');

             $isStatusExists = DB::table('channel_order_status')->where(['channel_id'=>$isOrderExists->channel_id,'status_id'=>$status_id])->first(['status_value']);
             if(!$isStatusExists)
                throw new Exception('Status Id doesnt Exist');
            
            DB::table('channel_order_disputes')
                    ->where('order_id',$channel_order_id)
                    ->update([
                        'dispute_id'=>$dispute_id,
                        'raise_dispute'=>0,
                        'dispute_explanation'=>$isStatusExists->status_value,
                        'dispute_modified_time'=>$this->getDate()
                        ]);
            $message = 'Dispute Updated Successfully';        
            }

            DB::commit();                                
        }
        catch(Exception $e){
          DB::rollback();   
          $status = 0;
          $message = $e->getMessage();
          \Log::info('Error:' . $message); 
        }
        return json_encode(['Status'=>$status,'Message'=>$message]);
    }


    public function getUpdatedQty($data)
    {
        try{
        \Log::info(__FUNCTION__ . ' : ' . print_r($data, true));
            $status =1;
            $message= 'Data found';
            $result = array();

            $channel_name = isset($data['channel_name']) ? $data['channel_name'] : '';                   
            if(empty($channel_name))
              throw new Exception('Channel name not passed');       

            $channel_id = DB::table('channel')->where('channnel_name', $channel_name)->pluck('channel_id');
            if(empty($channel_id))
                throw new Exception('Channel doesnt exist');
           
                $result = DB::table('product_inventory as prodinv')
                         ->leftJoin('channel_product_add_update as cod','prodinv.product_id','=','cod.product_id')
                         ->select('cod.channel_product_key as ItemID','prodinv.available_inventory as Quantity','prodinv.product_id')
                         ->where('prodinv.is_updated',1)
                         ->where('cod.channel_id',$channel_id)
                         ->get();
          
            if(empty($result))
                throw new Exception('Data not found');
          }
          catch(Exception $e){
            $status = 0;
            $message = $e->getMessage();
            \Log::info('Error:' . $message);
          }
          return json_encode(['Status'=>$status,'Message'=>$message,'Data'=>$result]);
    }

    public function getUpdatedOrder($data)
    {
      try{
        \Log::info(__FUNCTION__ . ' : ' . print_r($data, true));
            $status =1;
            $message ='Data found';
            $result = array();
            $channel_name = isset($data['channel_name']) ? $data['channel_name'] : '';                   
            if(empty($channel_name))
              throw new Exception('Channel name not passed');       

            $channel_id = DB::table('channel')->where('channnel_name', $channel_name)->pluck('channel_id');
            if(empty($channel_id))
                throw new Exception('Channel doesnt exist');
        
            $result = DB::table('channel_orders as Co')
                       ->leftJoin('channel_order_item_details as Cod','Cod.order_id','=','Co.order_id')
                       ->leftJoin('channel_order_payment as Cop','Cop.order_id','=','Cod.order_id')
                       ->leftJoin('channel_order_shipping_details as Cosd','Cosd.order_id','=','Cop.order_id')
                       ->leftJoin('channel_orders_address as Cosa','Cosa.order_id','=','Cosd.order_id')
                       ->leftJoin('channel as ch','ch.channel_id','=','Co.channel_id')
                       ->where('Cod.order_status',1)
                       ->where('ch.channel_id',$channel_id)
                       ->select('Cosa.*','Cod.channel_item_id as ItemID','Cop.payment_status as OrderStatus','Co.payment_method as PaymentMethodUsed','Cop.amount','Cosd.service_name','Cod.transaction_id','Co.channel_order_id')
                       ->get();
            if(empty($result))
                throw new Exception('Data not found');
        } 
        catch(Exception $e){
            $status = 0;
            $message = $e->getMessage();
            \Log::info('Error:' . $message);
        }
        return json_encode(['Status'=>$status,'Message'=>$message,'Data'=>$result]);
    }

     public function pullReturnOrders($data){
        try{
          \Log::info(__FUNCTION__ . ' : ' . print_r($data, true));
          $limit = 100;
          $date = $this->getDate();   
          $status =1;
          $i = false;
          $flag =0;   
          $return_data = isset($data['data']) ? $data['data'] : '{}';
          $channel_id = isset($data['channel_id']) ? $data['channel_id'] : '';
          $return_data = json_decode($return_data,true);
          $returnCount = count($return_data);
            if($returnCount > $limit)
                throw new Exception('Please send maximum 100 return orders');

            if(empty($return_data) || !is_array($return_data))
                throw new Exception('Returns data is empty or array not passed');
            
            DB::beginTransaction();
            foreach($return_data as $data){
                $order_id = $data['order_id'];
                $channel_return_id = isset($data['channel_return_id']) && !empty($data['channel_return_id']) ? $data['channel_return_id'] :'';

                $initiated_date = isset($data['initiated_date']) && !empty($data['initiated_date']) ? $data['initiated_date'] :'';
                if(empty($initiated_date))
                    throw new Exception('Return Order Datetime not provided');
                
                $isReturnExists = DB::table('gds_return_grid')->where('order_id',$order_id)->get();
                
                if(!empty($channel_return_id)){
                  $exists =  DB::table('gds_return_grid')->where(['channel_return_id'=>$channel_return_id,'channel_id'=>$channel_id])->count();
                  if($exists)
                    throw new Exception('This channel return id is already inserted for the channel:'.$channel_return_id);
                }
                DB::table('gds_return_grid')->insert(['order_id'=>$order_id,'channel_return_id'=>$channel_return_id,'date_added'=>$initiated_date,'channel_id'=>$channel_id]);
                $return_grid_id = DB::getPdo()->lastInsertId();
                

                $query = DB::table('gds_orders')->where(['channel_order_id'=>$order_id,'channel_id'=>$channel_id]);
                $gds_order_id = $query->lists('gds_order_id');

                if(empty($gds_order_id))
                    throw new Exception('This particular channel order id doesnt exist :'.$order_id);
                
                
                
                foreach($data['products'] as $prod){
                   $prod_key = isset($prod['product_key']) && !empty($prod['product_key']) ? $prod['product_key'] :'';
                   
                   if(empty($prod_key)){
                    $prod_key = isset($prod['sku_id']) && !empty($prod['sku_id']) ? $prod['sku_id'] :'';
                    $flag =1;
                    if(empty($prod_key))
                        throw new Exception('Channel Product is not passed');

                   }

                   $qty = $prod['qty'];
                   $return_reason_id = isset($prod['return_reason_id']) ? $prod['return_reason_id'] : ''; 
                   $return_reason_text = isset($prod['reason_text']) ? $prod['reason_text'] : '';                   
                   $approved_qty = isset($prod['approved_qty']) ? $prod['approved_qty'] : 0;
                   

                   /*$pid = DB::table('channel_product_add_update')->where(['channel_id'=>$channel_id,'channel_product_key'=>$prod_key])->pluck('product_id');
                   if(!$pid)
                    throw new Exception('This particular product doesnt exist:'.$prod_key);*/

                    $table = 'gds_returns';
                    $result = $this->validateProduct($order_id,$gds_order_id,$channel_id,$prod_key,$approved_qty,$table,$flag);
                    $result =json_decode($result,true);
                    if($result['Status'] == 0)
                      throw new Exception($result['Message']);               
                     
                    $pid = $result['pid'];
                   
                   

                   $isExists = DB::table('gds_returns')->where(['return_grid_id'=>$return_grid_id,'product_id'=>$pid])->get();
                   
                    $i=true;
                     DB::table('gds_returns')->insert([
                        'product_id'=>$pid,
                        'return_reason_id'=>$return_reason_id,
                        'qty'=>$qty,
                        'return_grid_id'=>$return_grid_id,
                        'channel_return_id'=>$channel_return_id,
                        'approved_quantity'=>$approved_qty                                       
                        ]); 
                     $return_id = DB::getPdo()->lastInsertId();
                     
                     if(!empty($return_reason_text)){
                     DB::table('gds_orders_comments')->insert([
                        'comment_type'=>'gds_returns',
                        'entity_id'=>$return_id,
                        'comment'=>$return_reason_text,
                        'created_date'=>$date
                        ]);
                     }

                     DB::table('gds_return_track')->insert([
                        'product_id'=>$pid,
                        'initiated_date'=>$initiated_date,
                        'received_date'=>$date,
                        'return_reason_id'=>$return_reason_id,
                        'return_id'=>$return_id,
                        'qty'=>$qty,
                        'approved'=>$approved_qty
                        ]);
                   
                }
            }

            if($i)
                $message = 'Returns Inserted Successfully';
            DB::commit();
        }
        catch(Exception $e){
        DB::rollback();
           $return_grid_id ='';
           $status =0;
           $message = $e->getMessage();

        }
        \Log::info(['Status'=>$status,'Message'=>$message,'return_id'=>$return_grid_id]);
        return json_encode(['Status'=>$status,'Message'=>$message,'return_id'=>$return_grid_id]);
    }

    public function UpdateReturnOrder($data){
        try{
              \Log::info(__FUNCTION__ . ' : ' . print_r($data, true));   
              $status =1;
              $flag = 0;
              $date = $this->getDate();
              $array = array();
              $array1 = array();  
              $channel_return_id = isset($data['channel_return_id']) ? $data['channel_return_id'] : '';
              $order_id = isset($data['channel_order_id']) ? $data['channel_order_id'] : '';
              $channel_id = isset($data['channel_id']) ? $data['channel_id'] : '';
              $return_reason_id = isset($data['return_reason_id']) ? $data['return_reason_id'] : '';
              $return_reason_text = isset($data['reason_text']) ? $data['reason_text'] : '';
              $approved_qty = isset($data['approved_qty']) ? $data['approved_qty'] : '';
              $return_status = isset($data['return_status_id']) ? $data['return_status_id'] : '';
              $initiated_date = $data['initiated_date'];
              $qty = isset($data['qty']) ? $data['qty'] : '';

               $prod_key = isset($data['product_key']) && !empty($data['product_key']) ? $data['product_key'] :'';
                   
                   if(empty($prod_key)){
                    $prod_key = isset($data['sku_id']) && !empty($data['sku_id']) ? $data['sku_id'] :'';
                    $flag =1;
                    if(empty($prod_key))
                        throw new Exception('Channel Product is not passed');

                   }
              
               if(empty($channel_return_id) || empty($order_id) || empty($channel_id) || empty($prod_key))
                    throw new Exception('Parameters are missing');

                $query = DB::table('gds_orders')->where(['channel_order_id'=>$order_id,'channel_id'=>$channel_id]);
                if(!$query->pluck('gds_order_id'))
                    throw new Exception('This particular channel order id doesnt exist :'.$order_id);
                
                $gds_order_id = $query->lists('gds_order_id');


                $query = DB::table('gds_return_grid')->where(['order_id'=>$order_id,'channel_return_id'=>$channel_return_id,'channel_id'=>$channel_id]);
               
                if(!$query->count()){
                   $count =  DB::table('gds_return_grid')->where(['channel_return_id'=>$channel_return_id,'channel_id'=>$channel_id])->count();
                
                if($count){
                    throw new Exception('The channel order id is already existing for some other order');
                }
                else{
                  $query = DB::table('gds_return_grid')->where(['order_id'=>$order_id,'channel_id'=>$channel_id,'date_added'=>$initiated_date]);
                  $query->update(['channel_return_id'=>$channel_return_id]);
                  $return_grid_id =  $query->where('channel_return_id',$channel_return_id)->pluck('return_grid_id');                   
                }
                
                }

                    //throw new Exception('Return doesnt exist to update');
   else{

                $return_grid_id = $query->pluck('return_grid_id');
                
 }
                /*$pid = DB::table('channel_product_add_update')->where(['channel_id'=>$channel_id,'channel_product_key'=>$prod_key])->pluck('product_id');
                   if(!$pid)
                    throw new Exception('This particular product doesnt exist:'.$prod_key);*/

                    $table = 'gds_returns';
                    $result = $this->validateProduct($order_id,$gds_order_id,$channel_id,$prod_key,$approved_qty,$table,$flag);
                    $result =json_decode($result,true);
                    if($result['Status'] == 0)
                      throw new Exception($result['Message']);               
                     
                    $pid = $result['pid'];
                
                DB::beginTransaction();        

                $query = DB::table('gds_returns')->where(['product_id'=>$pid,'return_grid_id'=>$return_grid_id]);
                 if($qty)
                    $query->where('qty',$qty);
                
                $return_id = $query->pluck('return_id');
                if(!$return_id)
                    throw new Exception('Returns not created');

                $array['channel_return_id'] = $channel_return_id;

                if(!empty($approved_qty)){
                    $array['approved_quantity'] = $approved_qty;
                    $array1['approved'] = $approved_qty;
                }
                if(!empty($return_reason_id)){
                    $array['return_reason_id'] = $return_reason_id;
                    $array1['return_reason_id'] = $return_reason_id;
                }
                if(!empty($return_status))
                    $array['return_status_id'] = $return_status;

                $query->update($array);
                
            
                if(!empty($array1)){
                DB::table('gds_return_track')
                      ->where('return_id',$return_id)
                      ->update($array1);
                }     

                if(!empty($return_reason_text)){
                     DB::table('gds_orders_comments')->insert([
                        'comment_type'=>'gds_returns',
                        'entity_id'=>$return_id,
                        'comment'=>$return_reason_text,
                        'created_date'=>$date
                        ]);
                     }
                DB::commit();
                $message = 'Return updated Successfully';
        }
        catch(Exception $e){
            DB::rollback();
             $status =0;
             $message = $e->getMessage();
        }
        \Log::info(['Status'=>$status,'Message'=>$message]);    
        return json_encode(['Status'=>$status,'Message'=>$message]);

    }
    
    public function UpdateReturnStatus($data){
        try{
              \Log::info(__FUNCTION__ . ' : ' . print_r($data, true));   
              $status =1;
              $flag = 0;
              $array = array();
              $date = $this->getDate();  
              $channel_return_id = isset($data['channel_return_id']) ? $data['channel_return_id'] : '';
              $track_id = isset($data['channel_track_id']) ? $data['channel_track_id'] : '';
              $track_status = isset($data['track_status_id']) ? $data['track_status_id'] : '';
              $channel_id = isset($data['channel_id']) ? $data['channel_id'] : '';
              $return_reason_text = isset($data['reason_text']) ? $data['reason_text'] : '';
              $return_reason_id = isset($data['return_reason_id']) ? $data['return_reason_id'] : '';
              $approved_qty = isset($data['approved_qty']) ? $data['approved_qty'] : '';

               $prod_key = isset($data['product_key']) && !empty($data['product_key']) ? $data['product_key'] :'';
                   
                   if(empty($prod_key)){
                    $prod_key = isset($data['sku_id']) && !empty($data['sku_id']) ? $data['sku_id'] :'';
                    $flag =1;
                    if(empty($prod_key))
                        throw new Exception('Channel Product is not passed');

                   }

                if(empty($channel_return_id) || empty($track_id) || empty($track_status) || empty($prod_key) || empty($channel_id))
                    throw new Exception('Parameters are missing');
              
                    $query = DB::table('gds_return_grid')->where(['channel_id'=>$channel_id,'channel_return_id'=>$channel_return_id]);
                    $order_id = $query->pluck('order_id');

                    if(!$order_id)
                        throw new Exception('The particular channel order id doesnt exist');

                    $gds_order_id = DB::table('gds_orders')->where(['channel_id'=>$channel_id,'channel_order_id'=>$order_id])->lists('gds_order_id');

                    $table = 'gds_returns';
                    $result = $this->validateProduct($order_id,$gds_order_id,$channel_id,$prod_key,$approved_qty,$table,$flag);
                    $result =json_decode($result,true);
                    if($result['Status'] == 0)
                      throw new Exception($result['Message']);               
                     
                    $pid = $result['pid'];


                       $query = DB::table('gds_returns')
                                    ->where(['channel_return_id'=>$channel_return_id,'product_id'=>$pid]);
                   if($return_reason_id)
                       $query->where('return_reason_id',$return_reason_id);
                   if($approved_qty)
                       $query->where('approved_quantity',$approved_qty);

              $return_id = $query->pluck('return_id');  
              
              if(!$return_id)
                throw new Exception('Return record doesnt exist');
              
              $query = DB::table('gds_return_track')
                            ->where(['return_id'=>$return_id])
                            ->update([
                                'return_track_status_id'=>$track_status,
                                'track_id'=>$track_id
                             ]);
               
               if(!empty($return_reason_text)){
                     DB::table('gds_orders_comments')->insert([
                        'comment_type'=>'gds_returns',
                        'entity_id'=>$return_id,
                        'comment'=>$return_reason_text,
                        'created_date'=>$date
                        ]);
                     }
                                   
              $message = 'Return Statuses updated Successfully';      
        }
        catch(Exception $e){
              $status =0;
              $message = $e->getMessage();
        }
        \Log::info(['Status'=>$status,'Message'=>$message]);    
        return json_encode(['Status'=>$status,'Message'=>$message]);
    }

    

    public function pushReturnStatus($data){
        try{
            $status =1;
            $seller_id = isset($data['seller_id']) ? $data['seller_id'] : '';
            $channel_id = isset($data['channel_id']) ? $data['channel_id'] : '';
            $auth_keys = isset($data['auth_keys']) ? $data['auth_keys'] : '{}';
            $channel_return_id = isset($data['channel_return_id']) ? $data['channel_return_id'] : '';
            $prod_key = isset($data['product_key']) ? $data['product_key'] : '';
            $auth_keys = json_decode($auth_keys,true);
            $qty = isset($data['qty']) ? $data['qty'] : '';
            $approved_qty = isset($data['approved_qty']) ? $data['approved_qty'] : '';
            $return_status_id = isset($data['return_status_id']) ? $data['return_status_id'] : '';
            $return_reason_id = isset($data['return_reason_id']) ? $data['return_reason_id'] : '';

            if(empty($channel_return_id) || empty($channel_id) || empty($seller_id) || !is_array($auth_keys) || empty($prod_key))
                throw new Exception('Parameters Missing');
            
            $pid = DB::table('channel_product_add_update')->where(['channel_id'=>$channel_id,'channel_product_key'=>$prod_key])->pluck('product_id');
                   if(!$pid)
                    throw new Exception('This particular product doesnt exist:'.$prod_key);
            
            $query = DB::table('gds_returns')->where(['product_id'=>$pid,'channel_return_id'=>$channel_return_id]);
            if($qty)
                $query->where('qty',$qty);
            if($approved_qty)
                $query->where('approved_qty',$approved_qty);
            if($return_reason_id)
                $query->where('return_reason_id',$return_reason_id);
            if($return_status_id)
                $query->where('return_status_id',$return_status_id);

            if(!$query->pluck('return_id'))
                throw new Exception('Returns not created for this particular channel return id :'.$channel_return_id);
            
            $return_id = $query->pluck('return_id');

            $returnStatus = DB::table('gds_return_track')
                                  ->where('return_id',$return_id)
                                  ->get(['return_track_status_id','track_id','return_reason_id','approved',DB::raw($seller_id.' as seller_id'),DB::raw($channel_id.' as channel_id'),DB::raw($channel_return_id.' as channel_return_id'),DB::raw('"'.$prod_key.'" as product_key')]);
            $returnStatus[0]->auth_keys = $auth_keys;

            $result = $this->XYZ($returnStatus);       
            $result = json_decode($result,true);
            if($result['Status'] == 1)
                $message = 'Return statuses pushed Successfully';
            else
                throw new Exception($result['Message']);
        }
        catch(Exception $e){
            $status =0;
            $message = $e->getMessage();
        }
        \Log::info(['Status'=>$status,'Message'=>$message]);
        return json_encode(['Status'=>$status,'Message'=>$message]);
    }



    public function pullRefundOrders($data){
        try{
          \Log::info(__FUNCTION__ . ' : ' . print_r($data, true));   
          $status =1;
          $flag =0;
          $limit = 100;
          $date = $this->getDate();  
          $i = false;   
          $price = 0;
          $refund_data = isset($data['data']) ? $data['data'] : '{}';
          $channel_id =  isset($data['channel_id']) ? $data['channel_id'] : '';
          $refund_data =  json_decode($refund_data,true);
          $refundCount = count($refund_data);
            if($refundCount > $limit)
                throw new Exception('Please send maximum 100 refund orders');

            if(empty($refund_data) || !is_array($refund_data))
                throw new Exception('Returns data is empty or array not passed');
            if(empty($channel_id))
                throw new Exception('Channel Id not passed');

            DB::beginTransaction();
            foreach($refund_data as $data){
                $order_id = $data['order_id'];
                $total_order_amount = DB::table('gds_orders')->where('channel_order_id',$order_id)->pluck('total');
                $refund_amount =  isset($data['refund_amount']) ? $data['refund_amount'] : '';
                $transaction_id = isset($data['transaction_id']) ? $data['transaction_id'] : '';
                $channel_return_id = $data['channel_return_id'];
                $isRefundExists = DB::table('gds_refund_grid')->where(['order_id'=>$order_id,'channel_return_id'=>$channel_return_id])->get();
                $isReturnExists = DB::table('gds_return_grid')->where(['order_id'=>$order_id,'channel_return_id'=>$channel_return_id])->get();                
                $initiated_date = isset($data['initiated_date']) ? $data['initiated_date'] : '';
                $type = isset($data['refund_type']) ? $data['refund_type'] : '';

                if(!$isReturnExists)
                    throw new Exception('The return order doesnt exist');
                if(empty($initiated_date))
                    throw new Exception('Initiated date not passed');

                if (!preg_match("/^(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})$/", $initiated_date))
                    throw new Exception('The initiated date is not in yyyy-mm-dd hh:mm:ss format');
                   
              
                $gds_order_id = DB::table('gds_orders')->where(['channel_id'=>$channel_id,'channel_order_id'=>$order_id])->pluck('gds_order_id');
                if(!$gds_order_id)
                    throw new Exception('The channel order id doesnt exist');
                
                if(!$isRefundExists){
                    DB::table('gds_refund_grid')->insert([
                               'order_id'=>$order_id,
                               'date_added'=>$initiated_date,
                               'refund_amount'=>$refund_amount,
                               'total_amount'=>$total_order_amount,
                               'transactionid'=>$transaction_id,
                               'channel_return_id'=>$channel_return_id,
                               'type'=>$type
                               ]);
                    $refund_grid_id = DB::getPdo()->lastInsertId();
                }
                else{
                   throw new Exception('A refund is already created against this channel_return_id');
                    //$refund_grid_id = $isRefundExists[0]->refund_grid_id;
                }
                
                foreach($data['products'] as $prod){
                   $prod_key = isset($prod['product_key']) && !empty($prod['product_key']) ? $prod['product_key'] :'';
                   
                   if(empty($prod_key)){
                    $prod_key = isset($prod['sku_id']) && !empty($prod['sku_id']) ? $prod['sku_id'] :'';
                    $flag =1;
                    if(empty($prod_key))
                        throw new Exception('Channel Product is not passed');

                   }

                   
                   $return_reason_text = isset($prod['reason_text']) ? $prod['reason_text'] : '';                   

                   

                   $table = 'gds_returns';
                    $result = $this->validateProduct($order_id,$gds_order_id,$channel_id,$prod_key,0,$table,$flag);
                    $result =json_decode($result,true);
                    if($result['Status'] == 0)
                      throw new Exception($result['Message']);               
                     
                    $pid = $result['pid'];

                  
                   $qty = DB::table('gds_returns')->where(['channel_return_id'=>$channel_return_id,'product_id'=>$pid])->pluck('approved_quantity');
                   $product = DB::table('products')->where('product_id',$pid)->get(['name','sku']);
                   $name = $product[0]->name;
                   $sku = $product[0]->sku;
                   
                   $unit_price = DB::table('gds_order_products')->where(['gds_order_id'=>$gds_order_id,'pid'=>$pid])->pluck('unit_price');

                   $price = $price + ($unit_price * $qty); 

                   $isExists = DB::table('gds_refund_products')->where(['refund_grid_id'=>$refund_grid_id,'sku'=>$sku])->get();
                  // if(!$isExists){
                    $i=true;
                     DB::table('gds_refund_products')->insert([
                        'sku'=>$sku,
                        'product_name'=>$name,
                        'total_amount'=>$total_order_amount,
                        'quantity'=>$qty,
                        'refund_grid_id'=>$refund_grid_id,
                        'date_added'=>$initiated_date                      
                        ]); 
                     $refund_products_id = DB::getPdo()->lastInsertId();
                     
                     if(!empty($return_reason_text)){
                     DB::table('gds_orders_comments')->insert([
                        'comment_type'=>'gds_refund_products',
                        'entity_id'=>$refund_products_id,
                        'comment'=>$return_reason_text,
                        'created_date'=>$date
                        ]);
                    }

                   // }
                }
                if($refund_amount > $price)
                    throw new Exception('The refund amount is greater than the price:'. $price);
             }

            if($i)
                $message = 'Refunds Inserted Successfully';
            else
                $message = 'Refund already created';
            DB::commit();
        }
        catch(Exception $e){
           DB::rollback();
           $status =0;
           $message = $e->getMessage();
        }
        \Log::info(['Status'=>$status,'Message'=>$message]);
        return json_encode(['Status'=>$status,'Message'=>$message]);
    }

    public function UpdateRefundOrder($data){
       try{
             \Log::info(__FUNCTION__ . ' : ' . print_r($data, true)); 
              $status =1;            
              $channel_return_id = isset($data['channel_return_id']) ? $data['channel_return_id'] : '';
              $adjust_fee = isset($data['adjustment_fee']) ? $data['adjustment_fee'] : 0;
              $payment_type = isset($data['payment_type']) ? $data['payment_type'] : '';
              $paid_name = isset($data['paid_name']) ? $data['paid_name'] : '';

               if(empty($channel_return_id))
                    throw new Exception('Parameters are missing');

                DB::table('gds_refund_grid')
                          ->where('channel_return_id',$channel_return_id)
                          ->update([
                            'adjustment_fee'=>$adjust_fee,
                            'paymenttype'=>$payment_type,
                            'paidname'=>$paid_name
                            ]);
                $message =  'Refund updated Successfully';          
       }
       catch(Exception $e){
             $status =0;
             $message =  $e->getMessage();
       }
        \Log::info(['Status'=>$status,'Message'=>$message]);
        return json_encode(['Status'=>$status,'Message'=>$message]);                   
    }
    
    public function UpdateRefundStatus($data){
        try{
            \Log::info(__FUNCTION__ . ' : ' . print_r($data, true)); 
              $status =1;
              $date = $this->getDate();
              $channel_return_id = isset($data['channel_return_id']) ? $data['channel_return_id'] : '';
              $order_id = isset($data['channel_order_id']) ? $data['channel_order_id'] : '';
              $paid_status = isset($data['paid_status']) ? $data['paid_status'] : '';
              $reason_text = isset($data['reason_text']) ? $data['reason_text'] : '';
              $paid_date = isset($data['paid_date']) && !empty($data['paid_date']) ? $data['paid_date'] : '';
             
             

               if(empty($channel_return_id) || empty($order_id) || empty($paid_status) || empty($paid_date))
                    throw new Exception('Parameters are missing');

                if (!preg_match("/^(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})$/", $paid_date))
                    throw new Exception('The initiated date is not in yyyy-mm-dd hh:mm:ss format');
                
                if($paid_status != 0 && $paid_status !=1)
                    throw new Exception('In-valid paid status');

                $query = DB::table('gds_refund_grid')->where(['order_id'=>$order_id,'channel_return_id'=>$channel_return_id]);
                if(!$query->count())
                    throw new Exception('Refund record doesnt exist to update');
                
                $refund_grid_id = $query->pluck('refund_grid_id');

                $query->update(['paid_status'=>$paid_status,'paid_date'=>$paid_date]);

                if(!empty($reason_text)){
                     DB::table('gds_orders_comments')->insert([
                        'comment_type'=>'gds_refund_grid',
                        'entity_id'=>$refund_grid_id,
                        'comment'=>$reason_text,
                        'created_date'=>$date
                        ]);
                    }

                $message = 'Refund updated Successfully';
        }
        catch(Exception $e){
            $status =0;
            $message = $e->getMessage();
        }
        \Log::info(['Status'=>$status,'Message'=>$message]);
        return json_encode(['Status'=>$status,'Message'=>$message]);
    }

    
    public function pullCancellations($data){
        try{
          \Log::info(__FUNCTION__ . ' : ' . print_r($data, true));
          $limit = 100;
          $date = $this->getDate();   
          $status =1;
          $i = false;
          $flag =0;   
          $cancel_data = isset($data['data']) ? $data['data'] : '{}';
          $channel_id = isset($data['channel_id']) ? $data['channel_id'] : '';
          $cancel_data = json_decode($cancel_data,true);
          $cancelCount = count($cancel_data);
            if($cancelCount > $limit)
                throw new Exception('Please send maximum 100 return orders');

            if(empty($cancel_data) || !is_array($cancel_data))
                throw new Exception('Cancellation data is empty or array not passed');
            
            DB::beginTransaction();
            foreach($cancel_data as $data){
                $order_id = $data['order_id'];
                $channel_cancel_id = isset($data['channel_cancel_id']) && !empty($data['channel_cancel_id']) ? $data['channel_cancel_id'] :'';

                $initiated_date = isset($data['initiated_date']) && !empty($data['initiated_date']) ? $data['initiated_date'] :'';
                if(empty($initiated_date))
                    throw new Exception('Cancel Order Datetime not provided');
                
                $isCancelExists = DB::table('gds_cancel_grid')->where('order_id',$order_id)->get();
                
                if(!empty($channel_cancel_id)){
                  $exists =  DB::table('gds_cancel_grid')->where(['channel_cancel_id'=>$channel_cancel_id,'channel_id'=>$channel_id])->count();
                  if($exists)
                    throw new Exception('This channel cancel id is already inserted for the channel:'.$channel_cancel_id);
                }
                DB::table('gds_cancel_grid')->insert(['order_id'=>$order_id,'channel_cancel_id'=>$channel_cancel_id,'date_added'=>$initiated_date,'channel_id'=>$channel_id]);
                $cancel_grid_id = DB::getPdo()->lastInsertId();
                

                $query = DB::table('gds_orders')->where(['channel_order_id'=>$order_id,'channel_id'=>$channel_id]);
                $gds_order_id = $query->lists('gds_order_id');

                if(empty($gds_order_id))
                    throw new Exception('This particular channel order id doesnt exist :'.$order_id);
                
                
                
                foreach($data['products'] as $prod){
                   $prod_key = isset($prod['product_key']) && !empty($prod['product_key']) ? $prod['product_key'] :'';
                   
                   if(empty($prod_key)){
                    $prod_key = isset($prod['sku_id']) && !empty($prod['sku_id']) ? $prod['sku_id'] :'';
                    $flag =1;
                    if(empty($prod_key))
                        throw new Exception('Channel Product is not passed');

                   }

                   $qty = $prod['qty'];
                   $cancel_reason_id = isset($prod['cancel_reason_id']) ? $prod['cancel_reason_id'] : ''; 
                   $cancel_reason_text = isset($prod['reason_text']) ? $prod['reason_text'] : '';                   
                   $approved_qty = isset($prod['approved_qty']) ? $prod['approved_qty'] : 0;
                   

                   /*$pid = DB::table('channel_product_add_update')->where(['channel_id'=>$channel_id,'channel_product_key'=>$prod_key])->pluck('product_id');
                   if(!$pid)
                    throw new Exception('This particular product doesnt exist:'.$prod_key);*/

                    $table = 'gds_order_cancel';
                    $result = $this->validateProduct($order_id,$gds_order_id,$channel_id,$prod_key,$approved_qty,$table,$flag);
                    $result =json_decode($result,true);
                    if($result['Status'] == 0)
                      throw new Exception($result['Message']);               
                     
                    $pid = $result['pid'];
                   
                   

                   $isExists = DB::table('gds_order_cancel')->where(['cancel_grid_id'=>$cancel_grid_id,'product_id'=>$pid])->get();
                   
                    $i=true;
                     DB::table('gds_order_cancel')->insert([
                        'product_id'=>$pid,
                        'cancel_reason_id'=>$cancel_reason_id,
                        'qty'=>$qty,
                        'cancel_grid_id'=>$cancel_grid_id,
                        'channel_cancel_id'=>$channel_cancel_id,
                        'approved_qty'=>$approved_qty                                       
                        ]); 
                     $cancel_id = DB::getPdo()->lastInsertId();
                     
                     if(!empty($cancel_reason_text)){
                     DB::table('gds_orders_comments')->insert([
                        'comment_type'=>'gds_order_cancel',
                        'entity_id'=>$cancel_id,
                        'comment'=>$cancel_reason_text,
                        'created_date'=>$date
                        ]);
                     }

                     
                   
                }
            }

            if($i)
                $message = 'Cancellations Inserted Successfully';
            DB::commit();
        }
        catch(Exception $e){
        DB::rollback();
           $cancel_grid_id ='';
           $status =0;
           $message = $e->getMessage();

        }
        \Log::info(['Status'=>$status,'Message'=>$message,'cancel_id'=>$cancel_grid_id]);
        return json_encode(['Status'=>$status,'Message'=>$message,'cancel_id'=>$cancel_grid_id]);
    }

    

    public function UpdateCancelOrder($data){
        try{
              \Log::info(__FUNCTION__ . ' : ' . print_r($data, true));   
              $status =1;
              $flag = 0;
              $date = $this->getDate();
              $array = array();
              $array1 = array();  
              $channel_cancel_id = isset($data['channel_cancel_id']) ? $data['channel_cancel_id'] : '';
              $order_id = isset($data['order_id']) ? $data['order_id'] : '';
              $channel_id = isset($data['channel_id']) ? $data['channel_id'] : '';
              $cancel_reason_id = isset($data['cancel_reason_id']) ? $data['cancel_reason_id'] : '';
              $cancel_reason_text = isset($data['reason_text']) ? $data['reason_text'] : '';
              $approved_qty = isset($data['approved_qty']) ? $data['approved_qty'] :'';
              $cancel_status = isset($data['cancel_status_id']) ? $data['cancel_status_id'] : '';
              $initiated_date = $data['initiated_date'];
              $qty = isset($data['qty']) ? $data['qty'] : '';

               $prod_key = isset($data['product_key']) && !empty($data['product_key']) ? $data['product_key'] :'';
                   
                   if(empty($prod_key)){
                    $prod_key = isset($data['sku_id']) && !empty($data['sku_id']) ? $data['sku_id'] :'';
                    $flag =1;
                    if(empty($prod_key))
                        throw new Exception('Channel Product is not passed');

                   }
              
               if(empty($channel_cancel_id) || empty($order_id) || empty($channel_id) || empty($prod_key))
                    throw new Exception('Parameters are missing');

                $query = DB::table('gds_orders')->where(['channel_order_id'=>$order_id,'channel_id'=>$channel_id]);
                if(!$query->pluck('gds_order_id'))
                    throw new Exception('This particular channel order id doesnt exist :'.$order_id);
                
                $gds_order_id = $query->lists('gds_order_id');


                $query = DB::table('gds_cancel_grid')->where(['order_id'=>$order_id,'channel_cancel_id'=>$channel_cancel_id,'channel_id'=>$channel_id]);
               
                if(!$query->count()){
                   $count =  DB::table('gds_cancel_grid')->where(['channel_cancel_id'=>$channel_cancel_id,'channel_id'=>$channel_id])->count();
                
                if($count){
                    throw new Exception('The channel order id is already existing for some other order');
                }
                else{
                  $query = DB::table('gds_cancel_grid')->where(['order_id'=>$order_id,'channel_id'=>$channel_id,'date_added'=>$initiated_date]);
                  $query->update(['channel_cancel_id'=>$channel_cancel_id]);
                  $cancel_grid_id =  $query->where('channel_cancel_id',$channel_cancel_id)->pluck('cancel_grid_id');                   
                }
                
                }

                    //throw new Exception('Return doesnt exist to update');
   else{

                $cancel_grid_id = $query->pluck('cancel_grid_id');
                
 }
                /*$pid = DB::table('channel_product_add_update')->where(['channel_id'=>$channel_id,'channel_product_key'=>$prod_key])->pluck('product_id');
                   if(!$pid)
                    throw new Exception('This particular product doesnt exist:'.$prod_key);*/

                    $table = 'gds_order_cancel';
                    $result = $this->validateProduct($order_id,$gds_order_id,$channel_id,$prod_key,$approved_qty,$table,$flag);
                    $result =json_decode($result,true);
                    if($result['Status'] == 0)
                      throw new Exception($result['Message']);               
                     
                    $pid = $result['pid'];
                
                DB::beginTransaction();        

                $query = DB::table('gds_order_cancel')->where(['product_id'=>$pid,'cancel_grid_id'=>$cancel_grid_id]);
                 if($qty)
                    $query->where('qty',$qty);
                
                $cancel_id = $query->pluck('cancel_id');
                if(!$cancel_id)
                    throw new Exception('Cancellation not created');

                $array['channel_cancel_id'] = $channel_cancel_id;

                if(!empty($approved_qty)){
                    $array['approved_qty'] = $approved_qty;
                    
                }
                if(!empty($cancel_reason_id)){
                    $array['cancel_reason_id'] = $cancel_reason_id;
                    
                }
                if(!empty($cancel_status))
                    $array['cancel_status_id'] = $cancel_status;

                $query->update($array);
                
            
                if(!empty($cancel_reason_text)){
                     DB::table('gds_orders_comments')->insert([
                        'comment_type'=>'gds_order_cancel',
                        'entity_id'=>$cancel_id,
                        'comment'=>$cancel_reason_text,
                        'created_date'=>$date
                        ]);
                     }
                DB::commit();
                $message = 'Cancellation updated Successfully';
        }
        catch(Exception $e){
            DB::rollback();
             $status =0;
             $message = $e->getMessage();
        }
        \Log::info(['Status'=>$status,'Message'=>$message]);    
        return json_encode(['Status'=>$status,'Message'=>$message]);

    }

    


     public function getReturnData($data){
        try{
            \Log::info(__FUNCTION__ . ' : ' . print_r($data, true)); 
             $status =1;
             $channel_id = isset($data['channel_id']) ? $data['channel_id'] : '';          
             $data = array();
             
             if(empty($channel_id))
                throw new Exception('Channel Id not passed');

             $return_ids = DB::table('gds_return_grid')
                                 ->whereNotNull('channel_return_id')
                                 ->get(['order_id','channel_return_id','return_grid_id']);
             
             \Log::info('Return Orders:');
             \Log::info($return_ids);

             if(empty($return_ids))
                  throw new Exception('There are no channel updated return orders.');

             foreach($return_ids as $return){
             
             $return_data  = DB::table('gds_returns as gr')
                                ->join('channel_product_add_update as cpau','cpau.product_id','=','gr.product_id')
                                ->join('gds_return_track as grt','grt.return_id','=','gr.return_id')
                                ->where(['gr.return_grid_id'=>$return->return_grid_id,'gr.channel_return_id'=>$return->channel_return_id,'cpau.channel_id'=>$channel_id])                                
                                ->distinct()
                                ->get(['cpau.channel_product_key','gr.return_reason_id','gr.return_status_id','gr.qty','gr.approved_quantity','gr.channel_return_id','grt.initiated_date','grt.return_track_status_id','grt.track_id']); 
             
             array_push($data,['channel_return_id'=>$return->channel_return_id,'products'=>$return_data]); 

             }
             $message = 'Data Successfully Retrieved';  
        }
        catch(Exception $e){
            $status =0;
            $message = $e->getMessage();
        }

        \Log::info(['Status'=>$status,'Message'=>$message,'Data'=>$data]); 
        return json_encode(['Status'=>$status,'Message'=>$message,'Data'=>$data]);
     }

     

     public function saveShippingData($data){
        try{
            \Log::info(__FUNCTION__ . ' : ' . print_r($data, true)); 
             $status =1;
             $flag = 0;
             $date = $this->getDate();
             $ship_data = isset($data['data']) ? $data['data'] : '{}';                     
             $channel_id = isset($data['channel_id']) ? $data['channel_id'] : '';
             $ship_data = json_decode($ship_data,true);
             $ship_track_id ='';
             
             if(!is_array($ship_data) || empty($ship_data) || empty($channel_id))
                throw new Exception('Parameters Missing');
            
            DB::beginTransaction();
            foreach($ship_data as $ship){
                $channel_order_id = $ship['channel_order_id'];
                $reason_text = isset($ship['reason_text']) ? $ship['reason_text'] : '';
                
                $query = DB::table('gds_orders')->where(['channel_order_id'=>$channel_order_id]);
                if(!$query->pluck('gds_order_id'))
                    throw new Exception('This particular channel order id doesnt exist :'.$channel_order_id);
                
                $gds_order_id = $query->lists('gds_order_id');

                
                DB::table('gds_ship_grid')->insert([
                    'channel_order_id'=>$channel_order_id,
                    'created_date'=>$date
                    ]);

                $ship_grid_id = DB::getPdo()->lastInsertId();

                if(!empty($reason_text)){

                 DB::table('gds_orders_comments')
                    ->insert([
                        'comment_type'=>'gds_ship_grid',
                        'entity_id'=>$ship_grid_id,
                        'comment'=>$reason_text,
                        'created_date'=>$date
                        ]);
                }  

                foreach($ship['products'] as $product){
                    $prod_key = isset($product['product_key']) && !empty($product['product_key']) ? $product['product_key'] : '';
                   
                    if(empty($prod_key)){
                    $prod_key = isset($product['sku_id']) && !empty($product['sku_id']) ? $product['sku_id'] :'';
                    $flag =1;
                    if(empty($prod_key))
                        throw new Exception('Channel Product is not passed');

                   }

                    $qty = isset($product['qty']) && !empty($product['qty']) ? $product['qty'] : '';
                    
                    if(empty($prod_key) || empty($qty))
                        throw new Exception('Parameters Missing');
                    
                    $table = 'gds_ship_items';
                    $result = $this->validateProduct($channel_order_id,$gds_order_id,$channel_id,$prod_key,$qty,$table,$flag);
                    $result =json_decode($result,true);
                    if($result['Status'] == 0)
                      throw new Exception($result['Message']);               
                     
                    $pid = $result['pid'];

                    DB::table('gds_ship_items')->insert([
                        'gds_ship_grid_id'=>$ship_grid_id,
                        'channel_order_id'=>$channel_order_id,
                        'pid'=>$pid,
                        'qty'=>$qty,
                        'created_date'=>$date
                        ]);

                    DB::table('gds_orders_shipment_track')->insert([
                        'gds_ship_grid_id'=>$ship_grid_id,
                        'qty'=>$qty,
                        'channel_order_id'=>$channel_order_id,
                        'pid'=>$pid,
                        'created_at'=>$date
                        ]);

                    $ship_track_id = DB::getPdo()->lastInsertId();
                    
                }
            }
             
             DB::commit();
             $message = 'Shipments Saved Successfully'; 
              
             
        }
        catch(Exception $e){
            DB::rollback();
            $status =0;
            $message = $e->getMessage();
        }

        \Log::info(['Status'=>$status,'Message'=>$message,'TrackID'=>$ship_track_id]); 
        return json_encode(['Status'=>$status,'Message'=>$message,'TrackID'=>$ship_track_id]);
     }


    public function validateProduct($channel_order_id,$gds_order_id,$channel_id,$prod_key,$qty,$table,$flag){
       try{
        $status =1;
        $pid = '';
        if($flag == 0){
        $pid = DB::table('channel_product_add_update')->where(['channel_id'=>$channel_id,'channel_product_key'=>$prod_key])->pluck('product_id');
        }
        else{
        $pid = DB::table('products')->where(['sku'=>$prod_key])->pluck('product_id');
        }             
        if(!$pid)
                    throw new Exception('This particular product doesnt exist:'.$prod_key);

        $exists = DB::table('channel_product_add_update')->where(['channel_id'=>$channel_id,'product_id'=>$pid])->count();
        if(!$exists)
                    throw new Exception('This particular product is related to the channel:' .$pid);        
                
         
                if($table == 'gds_ship_items'){
                  $till_count = DB::table('gds_ship_items')->whereIn('gds_order_id',$gds_order_id)->where('pid',$pid)->get([DB::raw('sum(qty) as qty')])[0]->qty;
                }
                if($table == 'gds_returns'){
                  $till_count = DB::table('gds_return_grid as grd')
                                   ->join('gds_returns as gr','gr.return_grid_id','=','grd.return_grid_id')
                                   ->where(['product_id'=>$pid,'order_id'=>$channel_order_id])
                                   ->get([DB::raw('sum(approved_quantity) as qty')])[0]->qty;

                }
                if($table == 'gds_order_cancel'){
                  $till_count = DB::table('gds_cancel_grid as gcd')
                                   ->join('gds_order_cancel as gc','gc.cancel_grid_id','=','gcd.cancel_grid_id')
                                   ->where(['product_id'=>$pid,'order_id'=>$channel_order_id])
                                   ->get([DB::raw('sum(approved_qty) as qty')])[0]->qty;
                }

                if(!empty($qty) || $qty != 0){
                if(!$till_count)
                    $till_count = 0;

                    $query= DB::table('gds_order_products')->whereIn('gds_order_id',$gds_order_id)->where('pid',$pid);

                    if(!$query->count())
                        throw new Exception('This particular product doesnt exists in the order: product_key:'.$prod_key.' order_id:'.$channel_order_id);

                    $count = $query->pluck('qty');
                    
                    $left_qty = $count-$till_count;
                    
                    if($qty > $left_qty || $left_qty == 0)
                        throw new Exception('The quantity provided is greater than the left over quantity in order: product_key:'.$prod_key. 'order_id:' .$channel_order_id.' qty:' .$left_qty);
               }

               $message ='Product Validated';            
            }
            catch(Exception $e){
                $status =0;
                $message = $e->getMessage();
            }
            return json_encode(['Status'=>$status,'Message'=>$message,'pid'=>$pid]);
            }


       public function getNewGdsOrders($data){
            try{
                \Log::info(__FUNCTION__ . ' : ' . print_r($data, true)); 
                $status =1;
                $message ='Data retrieved Successfully';
                $result = array();
                $date = isset($data['date']) && !empty($data['date']) ? $data['date'] :'0000-00-00 00:00:00';

                $orders = DB::table('gds_orders')->where('order_date','>=',$date)->get();

                foreach ($orders as $order) {
                      $gds_order_id = $order->gds_order_id;

                      $products = DB::table('gds_order_products')
                                    ->join('products','products.product_id','=','gds_order_products.pid')
                                    ->where('gds_order_id',$gds_order_id)
                                    ->select(DB::raw('gds_order_products.*'))
                                    ->addSelect('products.name')
                                    ->get();
                      
                      $result[] = ['order'=>$order,'products'=>$products];

                }
                if(empty($result))
                    throw new Exception('Data not found');

            }
            catch(Exception $e){
               $status =0;
               $message = $e->getMessage();
            }
            \Log::info(['Status'=>$status,'Message'=>$message,'Data'=>$result]); 
            return json_encode(['Status'=>$status,'Message'=>$message,'Data'=>$result]);
       }


       public function storeGdsOrders($data){
          try{
              \Log::info(__FUNCTION__ . ' : ' . print_r($data, true)); 
              $i=false;
              $status =1;
              $message = 'Data inserted Successfully';
              $arr =  json_decode($data['data'],true);
              $data = $arr['Data'];

              DB::beginTransaction();
              foreach($data as $order){
              \Log::info($order);
                $gds_order = $order['order'];

                DB::table('gds_orders')->insert($gds_order);

                if(empty($order['products'])){
                    throw new Exception('Products are empty for the gds_order:' .$gds_order['gds_order_id']);
                }

                foreach($order['products'] as $prod){
                    $name = $prod['name'];

                    if(empty($name) || is_null($name))
                        throw new Exception('Product name is empty or null');

                    $product_id = DB::table('products')->where(['name'=>$name,'product_type_id'=>8003])->pluck('product_id');

                    if(empty($product_id))
                        throw new Exception('The following product does not exist in the database:'.$name);
                    
                    unset($prod['name']);
                    $prod['pid'] = $product_id;                    

                    DB::table('gds_order_products')->insert($prod);

                    $i = true;
                }

                if($i){
                    $message = 'Data Successfully Inserted';
                    DB::commit();
                }

              }
          } 
          catch(Exception $e){
            DB::rollback();
            $status =0;
            $message =$e->getMessage();
          }
          return json_encode(['Status'=>$status,'Message'=>$message]);       
       }

}