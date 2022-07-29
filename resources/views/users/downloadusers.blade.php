<?php

?>
<html>
	<table>

		<tr>
			<?php
			for($i=0;$i<sizeof($headers);$i++)
			{
			?>
			<td align="center" style="background:#ffff00; font-weight: bold;border: 1px solid #999999;"><?php echo $headers[$i]; ?></td>
			<?php  
				}
			?>

		</tr>
		<?php
		if(!empty($data))
		{
			foreach ($data as $value) 
			{

				//echo "<pre/>";print_r($value);
				echo "<tr>
				<td>{$value->username}</td>
				<td>{$value->firstname}</td>
				<td>{$value->lastname}</td>
				<td>{$value->name}</td>
				<td>{$value->email}</td>
				<td>{$value->phone_no}</td>
				
				
				</tr>";
			}

		}



		?>
	</table>

</html>