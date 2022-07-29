<?php

use Central\Repositories\CustomerRepo;

class SubscriptionController extends BaseController
{
 
 private $custRepo;

 function __construct(CustomerRepo $custRepo) {
    $this->custRepo = $custRepo;
    //$this->OrderRepo = $OrderRepo;
  }

public function index()
{ 
  if(empty(Input::get('customer_id'))) {
	  $customerId =  (Session::has('customerId') && !empty(Session::get('customerId'))) ? Session::get('customerId') : 0;
  }
  else{
    $customerId =  Input::get('customer_id');
  }
    

    $customers = $this->custRepo->getAllCustomers();

  //echo $customerId; die;

  return view::make('gdsreports.subscription')->with(array('customers'=>$customers,'customerId'=>$customerId));
}
	
public function getData($customerId=0)
{

  /* $data = DB::select(DB::raw('select distinct prd.name as name ,prd.product_id as product_id from products prd,Channel_product ch where prd.product_type_id=8003 and prd.is_gds_enabled=1
  and prd.product_id=ch.product_id'));*/
	//$customerId = Session::get('customerId');

  $channel_details = DB::table('Channel')
                     //->leftJoin('manf_channels','Channel.channel_id','=','manf_channels.channel_id')
                     ->select('Channel.*',DB::raw('(select manf_channels.status from manf_channels where channel_id=Channel.channel_id and manf_id='.$customerId.') as status'))
                     ->get();

	//prev(array)int_r($customerId);exit;
  $i = 0;
  foreach( $channel_details as $value)
  {         
    //print_r($customerId);exit;
    $channel_details[$i]->price_url= '<a href="'.$value->price_url.'" target="_blank">Charges</a>';
    $channel_details[$i]->tnc_url= '<a href="'.$value->tnc_url.'" target="_blank">Terms and Condition</a>';
    $name = "'".$value->channnel_name."'";
    if(isset($value->status) && $value->status==1) {
      $channel_details[$i]->Subscription='<span style="padding-left:15px;" > <input type="checkbox" name="chk"  id="chk'.$value->channnel_name.'" onclick="popup('.$name.');"  class="btn btn-default" checked="checked" value="'.$value->channel_id.'">   Subscribe</input></span><span style="padding-left:30px;" ></span>';
   
    }else {  
    $channel_details[$i]->Subscription='<span style="padding-left:15px;" > <input type="checkbox" name="chk"  id="chk'.$value->channnel_name.'" onclick="popup('.$name.');"  class="btn btn-default" value="'.$value->channel_id.'">   Subscribe</input></span><span style="padding-left:30px;" ></span>';
   
   
    }
    $i++;
  }

   //print_r($data); die;
  return json_encode($channel_details);

 

 }
 public function Store()
 {

  $customerId = Input::get('customerId');
  $status = Input::get('status');
  $channel_id = Input::get('channel_id');

  if($status==0)
  {
    DB::table('manf_channels')
  ->where(array('channel_id'=>$channel_id,'manf_id'=>$customerId))
  ->update(array('status'=>$status));  
  }else {
    DB::table('manf_channels')
  ->insert(array('status'=>$status,'channel_id'=>$channel_id,'manf_id'=>$customerId));  
  }
  
  return 'success';
 	//$customerId = Session::get('customerId');
  /*foreach ($variable as $key => $value) {
    # code...
  } 	$channel_name=Input::get('name');
 	
 	$status=Input::get('status');

$channel_name='Flipkart';
  //return $status;
 	if($status=='true'){
 		//return 'true here';
 	$chn_id= DB::table('Channel')
 	             //->select('channel_id')
 	             ->where('channnel_name',$channel_name)
 	             ->pluck('channel_id');
 	           //print_r($chn_id);exit;
               //return $customerId;
 	 DB::table('manf_channels')
                    ->insert([
                        'manf_id'=> $customerId,
                        'channel_id'=>$chn_id,
                        'status'=>'1'
                        ]);


 }

else{
	$chn_id= DB::table('Channel')
 	             //->select('channel_id')
 	             ->where('channnel_name',$channel_name)
 	             ->pluck('channel_id');
	
	DB::table('manf_channels')
	//->leftjoin('Channel')
	->where('channel_id',$chn_id)
	->where('manf_id',$customerId)
	->update(array('status'=>'0'));
}*/
}

}
