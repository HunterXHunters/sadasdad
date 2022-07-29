<?php
//$_SERVER['REMOTE_ADDR'] = $_SERVER['HTTP_X_REAL_IP']?$_SERVER['HTTP_X_REAL_IP']:$_SERVER['REMOTE_ADDR'];

	/*if( !( ( isset($_SERVER['PHP_AUTH_USER']) && ( $_SERVER['PHP_AUTH_USER'] == "cteredis" && $_SERVER['PHP_AUTH_PW'] == "cteredis16!@3" ) )) ){
  		header('WWW-Authenticate: Basic realm="My Realm"');
  		header('HTTP/1.0 401 Unauthorized');
  		echo 'You are not authenticated';
  		exit;
	}else{
	}*/


$current_redis_master = "";
$current_redis_port = "";
$redis_connections_arr = array();
if( $_SERVER['HTTP_HOST'] == "www.cartradeexchange.com" || $_SERVER['HTTP_HOST'] == "www2.cartradeexchange.com"){
	$redis_connections_arr = array(
			  array("host" => "10.0.2.5", "port" => "6379")			  
			  );
}
else
{
	$redis_connections_arr = array(
			  array("host" => "app2.eseal.prd.in2.eseal.int", "port" => "6379")			  
			  );
}
/*
if(file_exists($_SERVER['DOCUMENT_ROOT']."config_redis_dynamic.php"))
{
      include($_SERVER['DOCUMENT_ROOT']."config_redis_dynamic.php");
}
*/


$redis_dyn = new Redis();

if(isset($_POST['make_master']))
{
	foreach($redis_connections_arr as $redis_connection_arr)
	{
        if($redis_connection_arr['host'].':'.$redis_connection_arr['port'] != $_POST['frm_host'].':'.$_POST['frm_port'])
        {
			$redis_connection = $redis_dyn->connect($redis_connection_arr['host'],$redis_connection_arr['port'],1.5);
			if($redis_connection)
			{
			      $redis_dyn->slaveOf($_POST['frm_host'], $_POST['frm_port']);
			}
		}
	}
	
	$redis_connection = $redis_dyn->connect($_POST['frm_host'],$_POST['frm_port'],1.5);
	if($redis_connection)
	{
		$redis_dyn->slaveOf();
		
		$file = fopen($_SERVER['DOCUMENT_ROOT']."config_redis_dynamic.php","w");
		fwrite($file,'<?php $redis_host = "'.$_POST["frm_host"].'"; $redis_port = "'.$_POST["frm_port"].'"; ?>');
		fclose($file);
		echo '<script language="javascript">window.location.href="https://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'].'";</script>';
	}
	
}

if(isset($_POST['make_slave']))
{
	$redis_connection = $redis_dyn->connect($_POST['frm_host'],$_POST['frm_port'],1.5);
	if($redis_connection)
	{
	    $redis_dyn->slaveOf($redis_host, $redis_port);
	}
		
}

if(isset($_POST['set_config']))
{
	if($_POST["redis_host"]!='' && $_POST["redis_port"]!='')
	{
		$file = fopen($_SERVER['DOCUMENT_ROOT']."config_redis_dynamic.php","w");
		fwrite($file,'<?php $redis_host = "'.$_POST["redis_host"].'"; $redis_port = "'.$_POST["redis_port"].'"; ?>');
		fclose($file);
	}
	else
	{
		echo '<script>alert("Please select anyone as Master.");</script>';
	}
}


foreach($redis_connections_arr as $redis_connection_arr)
{        
	$redis_connection = $redis_dyn->connect($redis_connection_arr['host'],$redis_connection_arr['port'],1.5);
	if($redis_connection)
	{
		$redis_info  =  $redis_dyn->info();
		if($redis_info['role'] == 'master')
		{
		       $current_redis_master = $redis_connection_arr['host'];
		       $current_redis_port = $redis_connection_arr['port'];
		       break;
		}	      
	}	
}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<title> Redis Info</title>
</head>
<body>	       	
	    <table width="100%" border="0" align="center" cellpadding="0" cellspacing="2" style="border-bottom:1px solid #cdcdcd;"  >
	        <tr>
		        <td valign="middle" width="70%">
		      	 <img style="padding-top:1px; padding-bottom:1px; padding-left:2px;height:35px;" src="/images/logo_cte1.png">
		        </td>		        
	        </tr>
	      </table>
	      <br/><br/>
	      <span>Running from <?php echo $_SERVER['SERVER_ADDR']; ?> Server</span><br/>
	      <span>Current Master tcp://<?php echo $redis_host.':'.$redis_port; ?> Server</span>
	      <table width="100%" border="1" align="center" cellpadding="0" cellspacing="0" style="border-bottom:1px solid #cdcdcd;">
	             <thead>
	             	<?php
	             	foreach($redis_connections_arr as $redis_connection_arr)
			{
			?>
		     	<th>Redis Server <?php echo $redis_connection_arr['host']; ?> Port <?php echo $redis_connection_arr['port']; ?></th>
		     	<?php
		     	}
			?>		     	
		     </thead>
		     <tr>
		        <?php
		        $arr_cnt = (int)100/count($redis_connections_arr); 
			foreach($redis_connections_arr as $redis_connection_arr)
			{ 
				$redis_conn = $redis_dyn->connect($redis_connection_arr['host'],$redis_connection_arr['port'],1.5); 			    
			?>
			        <td width="<?php echo $arr_cnt;?>%">
			        <form name="fmaster<?php echo $i; ?>" id="fmaster<?php echo $i; ?>" action="" method="post">
			        <input type="hidden" name="frm_host" value="<?php echo $redis_connection_arr['host']; ?>">
			        <input type="hidden" name="frm_port" value="<?php echo $redis_connection_arr['port']; ?>">
				<table width="100%" border="1" cellpadding="0" cellspacing="0">		     
			        <?php
			        if(!$redis_conn)
			        {			
				?>		        
			     		<tr><td>Redis Server <?php echo $redis_connection_arr['host']; ?> Port <?php echo $redis_connection_arr['port']; ?> is Down</td></tr>		     	
			     	<?php
				}
				else
				{
					$redis_info  =  $redis_dyn->info();
				?>
					<tr><td> role  </td><td><b><?php echo $redis_info['role'];?></b></td></tr>
					<tr><td>redis version  </td> <td><b><?php echo $redis_info['redis_version'];?></b></td></tr>		
					<tr><td> port  </td><td><b><?php echo $redis_info['tcp_port'];?></b></td></tr>			
					<tr><td> Number of client connections  </td><td><b><?php echo $redis_info['connected_clients'];?></b></td></tr>
					<tr><td> memory consumed by Redis </td><td><b><?php echo $redis_info['used_memory_human'];?></b></td></tr>			
					<tr><td> Peak memory consumed by Redis  </td><td><b><?php echo $redis_info['used_memory_peak_human'];?></b></td></tr>			
					<tr><td> Num of changes since the last dump </td><td><b><?php echo $redis_info['rdb_changes_since_last_save'];?></b></td></tr>			
					<tr><td> last successful RDB save  </td><td><b><?php echo date("Y-m-d H:i:s",( $redis_info['rdb_last_save_time']));?></b></td></tr>
					<tr><td> Flag indicating a RDB save is on-going  </td><td><b><?php echo $redis_info['rdb_bgsave_in_progress'];?></b></td></tr>
					<tr><td> Status of the last RDB save operation  </td><td><b><?php echo $redis_info['rdb_last_bgsave_status'];?></b></td></tr>
					<tr><td> Duration of the last RDB save operation in seconds  </td><td><b><?php echo $redis_info['rdb_last_bgsave_time_sec'];?></b></td></tr>
					<tr><td> Duration of the on-going RDB save operation if any  </td><td><b><?php echo $redis_info['rdb_current_bgsave_time_sec'];?></b></td></tr>				
					<tr><td> Num of commands processed per second </td><td><b><?php echo $redis_info['instantaneous_ops_per_sec'];?></b></td></tr>			
					<tr><td> instantaneous input  </td><td><b><?php echo $redis_info['instantaneous_input_kbps'].' kbps';?></b></td></tr>				
					<tr><td> instantaneous output  </td><td><b><?php echo $redis_info['instantaneous_output_kbps'].' kbps';?></b></td></tr>
					<tr><td> connected slaves  </td><td><b><?php echo $redis_info['connected_slaves'];?></b></td></tr>
					<?php
					if($redis_info['role'] == 'slave')
					{
					?>
					<tr><td align="center" colspan="2" height="50"><input type="submit" name="make_master" value="Make This Master"/></td></tr>
					<?php
					}
					?> 
					<?php				
					if($redis_info['role'] == 'master'&& $redis_host.':'.$redis_port != $redis_connection_arr['host'].':'.$redis_connection_arr['port'])
					{ 
					?>
					<tr><td align="center" colspan="2" height="50"><input type="submit" name="make_slave" value="Make This Slave"/></td></tr>
					<?php
					}
					?>			
				<?php
				}
				?>
				</table>
				</form>
				</td>
			<?php				
			}
			?>			
		     </tr>
		     <tr>
		         <td align="center" colspan="2" height="50">
		         <form name="fconfig" id="fconfig" action="" method="post">
		         <input type="hidden" name="redis_host" value="<?php echo $current_redis_master;?>">
			 <input type="hidden" name="redis_port" value="<?php echo $current_redis_port;?>"> 
			 <input type="submit" name="set_config" value="Generate Config"/>
			 </form>
			 </td>
		     </tr>		     
	      </table>
	</form>
</body>
</html>