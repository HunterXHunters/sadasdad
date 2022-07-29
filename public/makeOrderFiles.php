<?php
/**
 * Laravel - A PHP Framework For Web Artisans
 *
 * @package  Laravel
 * @author   Taylor Otwell <taylorotwell@gmail.com>
 */

/*
|--------------------------------------------------------------------------
| Register The Auto Loader
|--------------------------------------------------------------------------
|
| Composer provides a convenient, automatically generated class loader
| for our application. We just need to utilize it! We'll require it
| into the script here so that we do not have to worry about the
| loading of any our classes "manually". Feels great to relax.
|
*/

require __DIR__.'/../bootstrap/autoload.php';

/*
|--------------------------------------------------------------------------
| Turn On The Lights
|--------------------------------------------------------------------------
|
| We need to illuminate PHP development, so let's turn on the lights.
| This bootstraps the framework and gets it ready for use, then it
| will load up this application so that we can run it and send
| the responses back to the browser and delight these users.
|
*/

$app = require_once __DIR__.'/../bootstrap/start.php';

/*
|--------------------------------------------------------------------------
| Run The Application
|--------------------------------------------------------------------------
|
| Once we have the application, we can simply call the run method,
| which will execute the request and send the response back to
| the client's browser allowing them to enjoy the creative
| and wonderful application we have whipped up for them.
|
*/

//$app->run();

/////////////////STATUS 0: file not yet created, 1: file created zipped and moved to orderFiles Folder, 2: mail send with link

set_time_limit(0);

$config = Config::get('database.connections.mysql');
		$host = $config['host']; 
		$dbName = $config['database']; 
		$uName = $config['username']; 
		$pwd = $config['password']; 

$orders = DB::table('order_files')
			->where('status',0)
			->select('id','order_id','order_file', 'download_linkname')
			->groupBy('order_id', 'order_file')
			->orderBy('order_id')
			->get();

$sub = 'Codes generated for the order no 10000001';
$body = "Use following link to downlaod the codes : </br>"; 
$body .= 'http://local.central/downloadorderfile/download/file/20150825154240_1005';

$status1 = Mail::send('emails.tracker', array('body' => $body), function($message) use ($sub)
				{
					$message->to('ashish.thakre@esealinc.com')->subject($sub);
				});
var_dump($status1);



$nextOrderID = '';
$body = "Use following link to downlaod the codes : </br>"; 
$body1 = '';
foreach($orders as $val){
	if(!empty($val->order_file)){
		$output=0;
		$ret=0;
		$fileName = str_replace(' ', '_', $val->order_file);
		$file = $fileName.'.txt';
		$command = "mysql -u$uName -h$host -p'$pwd' $dbName -e \"select id from eseal_pregenerated_ids where used_for = '$val->order_file'\" > $file";
		exec($command, $output, $ret);	
		Log::info(implode('##', $output));
		Log::info(var_dump($ret));
		
		if(file_exists($file) && filesize($file)){  /////// CREATE FILE , ZIP IT and MOVE to orderFiles Folder
			Log::info('File created');
			$zipCommand = 'zip '.$file.'.zip '.$file;
			exec($zipCommand, $output1, $ret1);	
			if($ret1===0){
				Log::info('File zipped succesfully');
				$orderFilesfolder = dirname(dirname(__FILE__)).'/app/orderFiles/';
				$moveFile = 'mv '.$file.'.zip '.$orderFilesfolder;
				exec($moveFile, $output2, $ret2);		
				if($ret2===0){
					Log::info('File moved succesfully');
					unlink($file);
				}
			}
			$linkId = date('YmdHis').'_'.mt_rand(1000,9999);
			DB::table('order_files')->where('id', $val->id)->update(Array('status'=>1, 'download_linkname'=> $linkId));
			$body1 .= "</br>".'http://'.$_SERVER['HTTP_HOST'].'/downloadorderfile/download/'.$linkId;
		}
		if(empty($nextOrderID)){
			$nextOrderID = $val->order_id;
		}else{
			if($nextOrderID != $val->order_id){
				$sub = 'Codes generated for the order no '.$nextOrderID;
				
				$body += $body1;

				$status1 = Mail::send('emails.tracker', array('body' => $body), function($message) use ($sub)
								{
									$message->to('ashish.thakre@esealinc.com')->subject($sub);
								});
				var_dump($status1);
				$nextOrderID = $val->order_id;	
				$body1 = '';
			}
		}
	}
}