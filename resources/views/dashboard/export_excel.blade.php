<html>

<body>

<table>

<thead>

	<tr>
		<td colspan ="3"><center><b>V-GUARD INDUSTRIES PVT LTD.</b></center></td>
	</tr>
	<tr>
	<td colspan = "3"><center><b>INVENTORY REPORT</b></center></td>
	</tr>
	<tr>
	<td><b>REPORT DATE:</b></td>
	<td><b>{{date('d-M-y, h:i:s A')}}</b></td>
	</tr>

	@foreach($filters as $column=>$value)
	<tr>
	<td><b>{{$column}}:</b></td>
	<td><b>{{$value}}</b></td>
	</tr>
	@endforeach
	<tr><td></td></tr>
	

	<tr>
		<td><strong><center>Product Name</center></strong></td>
		<td><strong><center>Material Code</center></strong></td>
		<td><strong><center>Location</center></strong></td>
		<td><strong><center>ERP Code</center></strong></td>
		<td><strong><center>Location Type</center></strong></td>
		<td><strong><center>Category</center></strong></td>
		<td><strong><center>Avaliable Inventory</center></strong></td>
		<td><strong><center>Storage Location</center></strong></td>
	</tr>
</thead>
<tbody>
@foreach($data as $d)
<tr>
<td>{{$d->product_name}}</td>
<td>{{$d->material_code}}</td>
<td>{{$d->location_name}}</td>
<td>{{$d->ErpCode}}</td>
<td>{{$d->location_type_name}}</td>
<td>{{$d->category_name}}</td>
<td>{{$d->available_inventory}}</td>
@if($storage_location_exists)
<td>{{$d->storage_location}}</td>
@endif

</tr>
@endforeach
</tbody>
</table>
</body>
</html>
