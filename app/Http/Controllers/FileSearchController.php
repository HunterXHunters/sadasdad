<?php

use Illuminate\Http\Request;

//use App\Controllers\BaseController;
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
class FileSearchController extends BaseController{
    private $request;
    public function __construct() {
      $this->request = new Request;       
    }
    public function index()
    {                                                                            
        echo "Cron File";                    
    }
    
    public function searchFile()
    { 
        return view::make('tools/tools');               
    }
    
    public function phpinfo()
    { 
        echo phpinfo();               
    }

    public function toolRedis(){
        $redis = Redis::connection('session');
    /*      for ($i = 0; $i < 5; $i++)
    {
        $redis->set("mykey:$i", $i);
    }*/
  /* $info=$redis->get('mykey:4');

        $info = $redis->info();
       echo "<pre>";
        print_r($info);
        exit;*/
        $keys = $redis->keys('*');
        $redisData=[];
        foreach ($keys as $key => $value) {
          $redisData[$value]=$redis->get($value);
        }
        return view::make('tools/redis_content')->with(array('redisData'=>$redisData));    

    }
    
}
?>