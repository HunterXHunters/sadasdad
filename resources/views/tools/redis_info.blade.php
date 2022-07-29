<table border='1'>
	<thead>
		<tr>
			<th>Key</th>
			<th>Values</th>
		</tr>
	</thead>
	<tbody>
			<?php foreach ($redisData as $key => $value) { ?>
			<tr>
				<td><?=$key?></td>
				<td>
					<?php if(is_array($value)){ ?>
						<table>
							<?php foreach ($redisData as $key => $value){ ?>
								<tr><td><?=$key?></td>
								<td><?=$value?></td></tr>
							<?php }	?>
						</table>
					<?php } else echo $value; ?>
				</td>					
			</tr>
			<?php }	?>
	</tbody>
</table>