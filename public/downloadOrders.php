<?php

$v_user = $_SERVER['PHP_AUTH_USER'];
$v_pass = $_SERVER['PHP_AUTH_PW'];

$dbuser = '100000473';
$dbpassword = '473@374';
/*
$sql = 'select count(*) cnt from download_order where order_id = "'.$v_user.'" and pwd = "'.$v_pass.'"';
$cnt = $read->fetchOne($sql);*/



if ($v_user!=$dbuser && $v_pass!=$dbpassword) {
    header("WWW-Authenticate: Basic realm='eSeal Order'");
    header('HTTP/1.0 401 Unauthorized');
    die('Unauthorized Access');
}



$orderPath = 'download/';
$oid = $v_user;

if(isset($_GET['file']) && !empty($_GET['file'])){
	$docRoot = $orderPath.$oid.'/';
	$fname = $_GET['file'];
			$yourfile = $docRoot.$fname;
		    $file_name = basename($yourfile);

		    header("Content-Type: application/zip");
		    header("Content-Disposition: attachment; filename=$file_name");
		    header("Content-Length: " . filesize($yourfile));

		    readfile($yourfile);

exit;
}
if(!empty($oid) && is_numeric($oid)){

$finalOrderPath = $orderPath.$oid;
//echo $finalOrderPath;exit;
if(file_exists($finalOrderPath) && is_dir($finalOrderPath) ){   /// CHECK IF FILE EXISTS AND IS DIRECTORY
			//echo 'directory exists';
		$dirHandle = dir($finalOrderPath);  // get directory handle
		if(is_resource($dirHandle->handle)){  /// check if its a resource
			//echo 'got directory handle'.$dirHandle->handle;
			while(false !== ($entry = $dirHandle->read())){
				if(is_file($finalOrderPath.'/'.$entry))
					echo '<a href="http://esealcom.com/downloadOrders.php?file='.$entry.'" target="_blank">'.$entry.'</a>'."</br>";
			}
			
		}else{
 			echo 'unable to get directory handle';
		}
	}else{
		echo 'invalid directory';
	}
}


?>
