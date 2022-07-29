<?php


class ProductMappingController extends BaseController {
 /**
  * Display a listing of the resource.
  *
  * @return Response
  */
public function index()
 {
 // $languages = languages::all();
   //return $attributes;
   //return View::make('languages.index',compact("languages"));
 // $data=DB::table('products')->get();
   
   return View::make('ProductMapping.index');
   //->with('data',$data);
 }
 /**
  * Show the form for creating a new resource.
  *
  * @return Response
  */


public function getData()
{

/* $data = DB::select(DB::raw('select distinct prd.name as name ,prd.product_id as product_id from products prd,Channel_product ch where prd.product_type_id=8003 and prd.is_gds_enabled=1
and prd.product_id=ch.product_id'));*/


/*   if(!$user_id){
        $cust_id = Session::get('customerId');
        }*/
        
       /* $user_details=$this->custRepo->getUserDetails($user_id);
        $cust_id=$user_details[0]->customer_id;*/
       // $cust_id = '62';
 $cust_id = Session::get('customerId');

    $data = DB:: table('products as prod')
        ->where('prod.manufacturer_id', $cust_id)
        ->where('is_gds_enabled', '=','1')
        //->where('prod.product_type_id','=','8003')
        //->where('')
        ->get();

/*    
$data =   DB:: table('products')
  ->where('is_gds_enabled','=','1')
  ->get();*/

    $i = 0;
    foreach($data as $value)
  {         

    $data[$i]->name = $value->name;
/*    $data[$i]->channels='<a >Select Channel</a>';
    $data[$i]->actions = '<span style="padding-left:15px;" ><input type="checkbox" class="btn btn-default"> Enable</input></span><span style="padding-left:30px;" ></span>';*/

     $data[$i]->channels='<a
      data-toggle="modal" data-target="#basicvalCodeModal">Select Channels</a>';
     $data[$i]->actions = '<span style="padding-left:15px;" >
     <input type="checkbox" class="btn btn-default"> Enable</input></span>
     <span style="padding-left:30px;" ></span>';  
/*
     $data[$i]->channels='<a  onclick=getproduct()
      data-toggle="modal" data-target="#basicvalCodeModal">Select Channels</a>';
     $data[$i]->actions = '<span style="padding-left:15px;" ><button type="submit" class="btn btn-default">Add</button></span><span style="padding-left:30px;" ></span>'; */
   
   $i++;
  }   
    return json_encode($data);

 }

 public function store()
{
  $chkvalue = implode(",", $_POST['chk']);

  //$var=DB::Table('demo_product_channel')->insert([
  //$var=DB::Table('Channel_product as chprod')
       // ->leftJoin('Products as prod')
       // ->where('prod.name',)

  $var=DB::Table('Channel_product')->insert([
    'channel_id'=>$chkvalue

    ]);  
  return Response::json([
                    'status' => true,
                    'message' => 'Sucessfully Inserted.'
        ]); 
 
 }

}