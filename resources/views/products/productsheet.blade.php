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
if(!empty($data2))
{

foreach ($data2 as $value) 
{
echo "<tr>
<td>{$value['image']}</td>
<td>{$value['name']}</td>
<td>{$value['sku']}</td>
<td>{$value['material_code']}</td>
<td>{$value['status']}</td>
<td>{$value['is_deleted']}</td>
<td>{$value['actions']}</td>
</tr>";
}
}

?>
</table>

</html>