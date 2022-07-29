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
				echo "<tr>
				<td>{$value['primary_iot']}-</td>
				<td>{$value['mfg_date']}</td>
				<td>{$value['material_code']}</td>
				<td>{$value['product_name']}</td>
				<td>{$value['primary_iot']}-</td>
				<td>{$value['parent_iot']}-</td>
				<td>{$value['category_name']}</td>
				<td>{$value['manufacturing_name']}</td>
				<td>{$value['batch_no']}</td>
				<td>{$value['po_number']}</td>
				<td>{$value['name']}</td>
				<td>{$value['Dest_Loc']}</td>
				<td>{$value['update_time']}</td>
				<td>{$value['2manufacturing_name']}</td>
				<td>{$value['2Dest_Loc']}</td>
				<td>{$value['2update_time']}</td>
				</tr>";
			}
		}

		?>
	</table>

</html>