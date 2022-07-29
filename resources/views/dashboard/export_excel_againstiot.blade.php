<html>

<body>

<table>

<thead>
	<style>
	.num {
		mso-number-format:General;
	}
	.text{
		mso-number-format:"\@";/*force text*/
	}
	</style>
	<tr>
		<td colspan ="3"><center><b>V-GUARD INDUSTRIES PVT LTD.</b></center></td>
	</tr>
	<tr>
	<td colspan = "3"><center><b>INVENTORY REPORT</b></center></td>
	</tr>
	<tr>
	<td><b>REPORT DATE: {{date('d-M-y, h:i:s A')}}</b></td>
	</tr>

	@foreach($filters as $column=>$value)
	<tr>
	<td><b>{{$column}}:</b></td>
	<td><b>{{$value}}</b></td>
	</tr>
	@endforeach
	<tr><td></td></tr>
		
		
	<tr>
		<td><strong><center>Primary IOT</center></strong></td>
		<td><strong><center>Parent IOT</center></strong></td>
		<td><strong><center>Material Code</center></strong></td>
		<td><strong><center>Product Name</center></strong></td>
		<td><strong><center>Location Type</center></strong></td>
		<td><strong><center>Location</center></strong></td>
		<td><strong><center>Location Erp Code</center></strong></td>
		<td><strong><center>Category</center></strong></td>
		<td><strong><center>Mfg Date</center></strong></td>
		<td><strong><center>Avaliable Inventory</center></strong></td>
		<td><strong><center>Storage Location</center></strong></td>
		<td><strong><center>Expiry Date</center></strong></td>
		<td><strong><center>Age</center></strong></td>
	</tr>
</thead>
<tbody>
@foreach($data as $d)
<tr>
<td class="text" style="mso-number-format:\@;"><?php echo (string)strval($d->iot); ?></td>
<td class="text" style="mso-number-format:\@;"><?php echo (string)strval($d->parent); ?></td>
<td>{{$d->material_code}}</td>
<td>{{$d->product_name}}</td>
<td>{{$d->location_type_name}}</td>
<td>{{$d->location_name}}</td> 
<td>{{$d->erp_code}}</td>
<td>{{$d->category_name}}</td>
<td>{{$d->mfg_date}}</td>
<td>{{$d->availabel_inventory}}</td>

@if($storage_location_exists)
<td>{{$d->storage_location}}</td>
@else
<td></td>
@endif

<td>{{$d->expiry_date}}</td>
<td>{{$d->age}}</td>


</tr>
@endforeach
</tbody>
</table>
</body>
</html>