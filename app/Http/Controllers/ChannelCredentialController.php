<?php

  
  class ChannelCredentialController extends BaseController
  {

 function index()


    { 
/* $id = DB::select(DB::raw('select channel_id from Channel_configuration where Channel_configuration_id = (select max(`channel_configuration_id`) from Channel_configuration)'));

print_r($id[0]->channel_id);

exit*/
       /*$id = DB::table('Channel_configuration')
     ->select('channel_configuration_id')
     ->get();
     print_r($id);exit;*/
      return View::make('gdschannels.index');
        
    }


 public function add()
 {
  return View::make('gdschannels.add');
 }

   public function store()
 {
//$image = Input::all();
  $image = Input::file('Channel_Logo');      
 //print_r($image);exit;
$filename = $image->getClientOriginalName();     
//print_r($filename);exit;   
$destination = 'uploads/channels/';      
$image->move($destination, $filename);
//$image->image = strtolower($filename);
//$image->save();

//print_r("hi");exit;




//return  Input::get();

  // dd(Input::get('key_name'));
 //print_r($filename);exit;
    DB::table('Channel')->insert([
'channel_logo' => '/'.$destination.$filename,
      //'channel_id' => Input::get('channel_id'),
              'channnel_name' => Input::get('channnel_name'),
              'channel_url' => Input::get('channel_url'),
              'price_url'=> Input::get('price_url'),
              'tnc_url'=>  Input::get('tnc_url'),
             
       ]);
$keyname = Input::get('key_name');
$key_value =  Input::get('key_value');
//print_r($keyname);
/*  return Response::json([
        "status" => true,
        "message"=> $keyname
      ]);*/

    $this->storemaincred($keyname,$key_value);
/* $id = DB::table('Channel')
 ->select('channel_id')
 ->where('channel_configuration_id','=','max(channel_configuration_id) as channel_configuration_id')
 ->get();

    foreach (Input::get('key_name') as $key => $value) {
      DB::table('Channel_configuration')->insert([
       'channel_id' => $id,
        'Key_name' => $value,
        'Key_value' => Input::get('key_value')[$key]
      ]);
    }*/
/* return Response::json([
        "status" => true,
        "message"=> "success"
      ]);*/
return Redirect::to('gdschannels/index'); 

 }
 public function storemaincred($keyname,$key_value){

$id = DB::select(DB::raw('select channel_id from Channel where channel_id = (select max(`channel_id`) from Channel)'));

  foreach (Input::get('key_name') as $key => $value) {
      DB::table('Channel_configuration')->insert([
       'channel_id' => $id[0]->channel_id,
        'Key_name' => $value,
        'Key_value' => Input::get('key_value')[$key]
      ]);
    }
return;

 }
public function storecred($channel_id){

 foreach (Input::get('key_name') as $key => $value) {
      DB::table('Channel_configuration')->insert([
        'channel_id' => $channel_id,
        'Key_name' => $value,
        'Key_value' => Input::get('key_value')[$key]
      ]);
    }

  return Response::json([
        "status" => true,
        "message"=> "Successfully Updated"
      ]);
 
}
 public function edit($id)
    {

        $channel_data =  DB::Table('Channel')

                  ->where('channel_id', $id)
                  ->first();




return View::make('gdschannels.edit')
   ->with('channel_data',$channel_data);
}
public function edit_credentials($id){

$credential_data = DB::Table('Channel_configuration')
  
                  ->where('channel_id', $id)
                  ->get();
                 // echo $credential_data;exit;
//print_r($credential_data);exit;
return View::make('gdschannels.editcredentials')
   ->with('credential_data', $credential_data);
}

 /**
  * Update the spcified resource in storage.
  *
  * @param  int  $id
  * @return Response
  */
 public function update($id)
 {
    //echo "hi";exit;
    //dd(Input::all());
     DB::table('Channel')
            ->where('channel_id', $id)
            ->update(array(
                'channnel_name' => Input::get('channnel_name'),
                'channel_url' => Input::get('channel_url'),
                'price_url' => Input::get('price_url'),
                'tnc_url' => Input::get('tnc_url'),
              ));

    return Response::json([
        "status" => true,
        "message"=> "Successfully Updated"
      ]);
  }


 public function updatecredentials($id)
 {
    //echo "hi";exit;
 $s=Input::get('Key_name');
/*return Response::json([
    "status" => true,
    "message"=> $id
  ]);*/
  $size = DB::Table('Channel_configuration')
  ->select(DB::raw('count(channel_configuration_id) as count'))
  ->where('channel_id','=',$id)
  ->get();
 // $e = $size[0]->count;

  if(count($s)<=$size[0]->count){
  foreach (Input::get('Key_name') as $key => $value) {
       DB::table('Channel_configuration')
          ->where('channel_configuration_id', Input::get('channel_configuration_id')[$key])
          ->update(array(
              'Key_name' => $value,
              'Key_value' => Input::get('Key_value')[$key]
            ));
      //print_r(DB::getQueryLog());
    }}
    else{

for($i = $size[0]->count;$i<count($s);$i++)
{
/* return Response::json([
    "status" => true,
    "message"=> "inside"
  ]);*/
      DB::table('Channel_configuration')->insert([
       'channel_id' => $id,
        'Key_name' => Input::get('Key_name')[$i],
        'Key_value' => Input::get('Key_value')[$i]
      ]);
    }
  }


  
  return Response::json([
    "status" => true,
    "message"=> "Successfully Updated"
  ]);

    // return Redirect::to('gdschannels/index');
  }


 public function credentials()
 {

return View::make('gdschannels.create_credentials');

 }
 public function delete($id)
 {
  DB::table('Channel')->where('channel_id', '=', $id)->delete();
    return Redirect::to('gdschannels/index');
 }


     public function getCustomers()
  {

    
    $custArr = array();
        $finalCustArr = array();
         $channel_data =DB::Table('Channel')
       //  ->where('channel_id','<','4')
         ->get();
        $customer_details = json_decode(json_encode($channel_data), true);
//print_r($customer_details);exit;
$credential_data = DB::Table('Channel_configuration')->get();
$credArr = json_decode(json_encode($credential_data), true);

       foreach($customer_details as $value)
        {         
          
          $custArr['channel_id'] = $value['channel_id'];
          $custArr['channnel_name'] = $value['channnel_name'];
          $custArr['channel_url'] = $value['channel_url'];
          $custArr['channel_logo'] = $value['channel_logo'];
           $custArr['price_url'] = $value['price_url'];
            $custArr['tnc_url'] = $value['tnc_url'];
         
          
          
           $custArr['actions'] =  '<span style="padding-left:20px;" ><a data-href="javascript:void(0);" data-toggle="modal" onclick="getEditPage('.$value['channel_id'].')" data-target="#basicvalCodeModal1"><span class="badge bg-light-blue"><i class="fa fa-pencil"></i></span></span>
          <span style="padding-left:20px;" ><a data-href="javascript:void(0);" data-toggle="modal" onclick="getEditCredPage('.$value['channel_id'].')" data-target="#basicvalCodeModal2">Edit Credentials</a></span><span style="padding-left:50px;" ></span>
                                  <span style="padding-left:02px;" ><a onclick="deleteEntityType('.$value['channel_id'].')"><span class="badge bg-red"><i class="fa fa-trash-o"></i></span></a></span>';

      $finalCustArr[] = $custArr;
        }
        //print_r($finalCustArr);exit;
return json_encode($finalCustArr);
     //  return array(json_encode($finalCustArr), json_encode($FinalCred));

  }

  }
