<?php
//ini_set('upload_max_filesize','30M');
//ini_set('post_max_size','30M');
//ini_set('max_input_time', 300);
namespace App\Http\Controllers;
use App\Models\Products;
ini_set('max_execution_time', 300); //300 seconds = 5 minutes
////set_time_limit(0);
//ini_set('memory_limit', '-1');
use App\Repositories\RoleRepo;
use App\Repositories\CustomerRepo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;

use Session;
use DB;
use View;
use Input;
use Validator;
use Redirect;
use Log;
use Exception;
class AppVersionController extends BaseController {
 /**
  * Display a listing of the resource.
  *
  * @return Response
  */

 public function index()
 {

   return View::make('appVersion.index');
     
 }
 public function show()
 {
  $data = DB::table('app_versions')->get();
 
        $i=0;
//        echo "<pre>"; print_r($data);die;
        foreach($data as $value)
        {
            $download_link = $value->download_link;
//            $data[$i]->download_link = URL::to('/').$download_link;
            $data[$i]->download_link = '<a href="'.$download_link.'"  style="margin-top:0px;">'.$download_link.'</a>';
          $dpupdate = ($value->db_update_needed==1) ? 'Yes' : 'No';
          $confreset = ($value->config_reset==1) ? 'Yes' : 'No';
          $data[$i]->actions = '<span style="padding-left:30px;" ><a href="javascript:void(0);" onclick="editAppVersion('.$value->id.')" data-target="#basicvalCodeModal1"><span class="badge bg-light-blue"><i class="fa fa-pencil"></i></span>
           <span style="padding-left:20px;" ><a href="javascript:void(0)" onclick="deleteEntityType('.$value->id.')"><span class="badge bg-red"><i class="fa fa-trash-o"></i></span></a></span>';
            $i++;     
        }
     return json_encode($data);

 }

 public function create()
 {
  $data = DB::table('app_versions')
                ->get();
  return View::make('appVersion.upload')->with('data',$data);
 }

 public function store()
 {
    $data = Input::all();
//    echo "<pre>";print_R($data);die;
  $destinationPath = '';
  $filename        = '';
  /*$path            = '';*/

    $link = '/uploads/appVersions/';
    if (Input::hasFile('files')) {
        $file            = Input::file('files');
        /*echo "<pre>"; print_r($file); die;*/
        $destinationPath = public_path().'/uploads/appVersions/';
       
       $filename   = $file->getClientOriginalName();
        $extension = $file->getClientOriginalExtension();
        
        $filename = basename($filename,'.'.$extension);

        $filename =  $filename.Input::get('version').'.'.$extension;
        //$link = '/public/uploads/appVersions/';
        /*echo "<pre>"; print_r($filename); die();*/
        $path   = $file->move($destinationPath, $filename);
        /*echo "<pre>"; print_r($path); die();*/
      }

   DB::table('app_versions')->insert([
              'db_update_needed' => Input::get('dbupdate'),
              'config_reset' => Input::get('configreset'),
              'latest_version' => Input::get('version'),
              'release_date'=>Input::get('release_date'),
              'download_link'=> $link . $filename,
              'app_id'=>Input::get('app_id')
                  ]);

   return Redirect::to('appVersion');

 }

 public function edit($id){

  $data = DB::Table('app_versions')->where('id',$id)->first();
  return View::make('appVersion.edit')->with('data',$data);
 }

 public function update($id)
 {
  /*$destinationPath = '';
  $filename        = '';*/


    /*if (Input::hasFile('files')) {
        $file            = Input::file('files');
        echo "<pre>"; print_r($file); die;
        $destinationPath = public_path().'/uploads/appVersions/';
        $filename        = $file->getClientOriginalName();
        echo "<pre>"; print_r($filename); die();
        $path   = $file->move($destinationPath, $filename);
        echo "<pre>"; print_r($path); die();
      }*/

  DB::Table('app_versions')
                ->where('id', $id)
                ->update(array('db_update_needed' => Input::get('dbupdate'),
                     'config_reset' => Input::get('configreset'),
                      'latest_version' => Input::get('version'),
                      'release_date'=>Input::get('release_date'),
                      
                      'app_id'=>Input::get('app_id')));

    return Redirect::to('appVersion');
 }

 public function delete($id)
 {
  
        DB::Table('app_versions')->where('id', '=', $id)->delete();
        
        return Redirect::to('appVersion')
            ->withInput()
            ->with('message', 'Successfully deleted.');
 }

}
