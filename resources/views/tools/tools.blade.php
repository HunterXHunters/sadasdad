<style>
	.trsel:hover{
		background: lightgray;
	}
	.trsel{
		border-bottom: 1px solid lightgray;
	}
	.chk{
		width:20px;
		height:20px;
		cursor: pointer;
	}
</style>
<script>
	function selcheck(id)
	{
		if(document.getElementById('che_'+id).checked)
		{
			document.getElementById('tr_'+id).style.backgroundColor = "";
			document.getElementById('che_'+id).checked = false;
		}else{
			document.getElementById('tr_'+id).style.backgroundColor = "gray";
			document.getElementById('che_'+id).checked = true;
		}
	
	}
	function check_all(ele) {
     var checkboxes = document.getElementsByTagName('input');
     if (ele.checked) {
         for (var i = 0; i < checkboxes.length; i++) {
             if (checkboxes[i].type == 'checkbox' && checkboxes[i].disabled != true) {
                 checkboxes[i].checked = true;
             }
         }
     } else {
         for (var i = 0; i < checkboxes.length; i++) {
             console.log(i)
             if (checkboxes[i].type == 'checkbox' && checkboxes[i].disabled != true) {
                 checkboxes[i].checked = false;
             }
         }
     }
 }
</script>

<?php 
$host = str_replace("www.", "", $_SERVER['HTTP_HOST']);
if(!empty(Input::get('host')))
{

	$hosts = array(
		"test"=>"betashim.local",
		"live"=>"52.6.74.110"
		);
	$host = $_POST?$hosts[Input::get('host')]:$hosts[Input::get('host')];
	if(!in_array(trim($host), $hosts))
		die('No Room for you bro..');
}
if($_POST)
{
	$searchitem = Input::get('searchitem');
	if(!trim($searchitem))
	{
		exit("Enter Search Term");
	}
	$flr = '';
	$fsch = "*.php ";
	if(!empty(Input::get('filesearch')))
	{
		$fsch = trim(Input::get('filesearch'))."*.php ";
	}
	if(Input::get('dir'))
	{
		foreach ((array) Input::get('dir') as $key => $folder) {
			//$flr .= "/var/www/html/betashim".$folder."/".$fsch;
            $flr .= "/var/www/html/betashim/app".$folder." ";
            
		}
	}else
	{
		echo "No directory Given...Searching Default Root Directory <br>";
		$flr = "/var/www/html/betashim/app".$fsch;
	}

$flr = trim($flr);
if(Input::get('s')=='y')
{ echo "grep -Fin '$searchitem' $flr -A0 -B0"; }
	// exec("grep -in '$searchitem' $flr -A0 -B0",$oo);
// echo "grep -in '$searchitem' $flr";

//quotemeta()
//	ack -Qw --php "\$_REQUEST['action']" admin/

$case = 'i';
if(Input::get('casesense') == 'on')
$case = '';
//exec("grep -F".$case."n '$searchitem' $flr",$oo);
exec("grep -rFI".$case."n --include='*.php' '$searchitem' $flr -A0 -B0",$oo);
	$final = array_map('htmlspecialchars',$oo);
	echo "<b>Total results count ".count(array_filter($oo))."</b>";

	foreach ((array) $final as $key => $value) {
		if($final[$key] == "--")continue;
	$fd = explode('.php',$value);
	$fd2 = explode(':',$fd[1]);     
	// $fd2 = array_values(array_filter($fd2));
	?>
	<table style="border-bottom: 1px solid gray;border-top: 1px solid gray;font-size: 14px;font-family: inherit;margin:15px 2px 7px 15px ">
		<tr><td bgcolor="lightgray"><?=str_replace("/var/www/html/betashim/app",'',$fd[0])?>.php</td></tr>
		<tr><td>Line Number : <?=$fd2[0]?></td></tr>
		<tr><td bgcolor=""><?=str_replace($searchitem, "<span style='background:yellow'>".$searchitem."</span>",$fd[1])?></td></tr>
	</table>
	<?php
	}
	
}
// var_dump($con);
exec("find /var/www/html/betashim/app -maxdepth 1 -mindepth 1 -type d | sort",$o);
$o= str_replace("/var/www/html/betashim/app","",$o);
// $o = array_flip($o);
if(($_SERVER['HTTP_HOST'] == '52.6.74.110')){
	$order = [
"mobile_api_json",
"bank_pdfs",
"classes",
"data",
"direct_reports",
"dms_apis",
"accounts",
"excellreader",
"leads_api",
"mobileapp",
"motorcheck",
"mysql_error_logs",
"user_reports"];

//echo "<pre>"; print_r($o); echo "</pre>";

foreach ((array) $o as $key => $value) {
	if(in_array($value,$order))
	{
		$akey = $key;
		$bkey = array_search($value,$order);
		$tmp = $o[$akey];
		$o[$akey] = $o[$bkey];
		$o[$bkey] = $tmp;
	}
}
}
if(!$_POST)
{
	$ign = array("vimages","___cteredmin","__mydb","__satish","_notes");
?>
<form action="" method="POST" target="ifr1">
<div style="height:100%;overflow:auto;position:fixed;padding-bottom	:15px;width:225px;">
<table style="float:left;border-collapse:collapse;cursor:pointer;font-family: Tahoma;font-size: 13px;width:200px;">
<tr  class="trsel">
	<th>Check All Folders</th>
	<th><input class="chk" type="checkbox" id="" onclick="check_all(this)"></th>
</tr>
<tr onclick="selcheck(1)" id="tr_1" class="trsel"><td>Root folder</td><th>
<input class="chk" onclick="selcheck(1)" type="checkbox" id="che_1" value="." name="dir[]" checked="true">

</th></tr>
<?php
$s = 1;
foreach ((array) $o as $key => $value) {
$sear = str_replace("/var/www/html/betashim/app",'',$value);	 
	if(in_array($sear, $ign))
		continue;
	$s++;
?>
<tr onclick="selcheck(<?=$s?>)" id="tr_<?=$s?>" class="trsel"><td><?=@in_array($value,$order)?'<b>'.$value.'</b>':$value?></td><th><input class="chk" type="checkbox" id="che_<?=$s?>" value="<?=$value?>" name="dir[]" onchange= "selcheck(<?=$s?>)" id=""></th></tr>
<?php
}
?>
<tr>
	<td></td>
</tr>
</table>
</div>
<?php
if(!$_POST)
{
?>
<table style="width: 40px;margin-left: 38%;position: fixed;"><tr><td>
<select name="host" id="" onchange="location.href='?host='+this.value" >
<option <?=Input::get('host')==""?'selected':''?> value="">Select</option>
<?php
if($_SERVER['HTTP_HOST'] == "betashimdemo.local")
{
?>
<option <?=Input::get('host')=="test"?'selected':''?> value="test">Betatracker</option>
<?php
}
else if($_SERVER['HTTP_HOST'] == "52.6.74.110")
{
?>
<option <?=Input::get('host')=="live"?'selected':''?> value="live">Betatracker Live</option>
<?php
}
?>
<?php  ?>
<?php  ?>
</select></td><td><?=$host?></td></tr></table>
<?php
}
?>
<div style="width: 240px;margin-left: 18%;position: fixed;">
	<input placeholder="String to Search" type="text" name="searchitem" style="width: 201px;height: 30px;font-size: 15px;">
	<input placeholder="particular file ?" type="text" name="filesearch" style="width: 201px;height: 30px;font-size: 15px;">
	<div><label>Case-sensitive?</label> <input type="checkbox" name="casesense"></div>

	<input type="submit" value="Search" style="padding:3px 5px 3x 5px;">
</div>
</form>
<div id="pass_div" style="padding:0px;margin-left: 780px;">
<form method="POST" target="pass_ifr" action="decrypt_enc_password.php">
	<table style="margin:0px;font-size:12px;" cellpadding="0" cellspacing="0">
		<tr>
			<th>Username/Id/Encrypted Password</th>
			<td><input type="" name="username"></td>
			<td><input type="submit" name=""><input type="hidden" name="action" value="getit">
			<input type="hidden" name="from" value="findfilesnew">
			</td>
			<td>Admin?<input type="checkbox" name="admin" id="admin" <?=Input::get('admin')=="on"?"checked":""?>></td>
		</tr>
	</table>	
</form>
<iframe name="pass_ifr" src="" style="width: 389px;height: 57px;"></iframe>
</div>
<iframe style="width:80%;height:85%;margin-left:18%;margin-top:1%;border:1px solid gray;" src="" name="ifr1" frameborder="0"></iframe>
<?php
}
exit;
?>
