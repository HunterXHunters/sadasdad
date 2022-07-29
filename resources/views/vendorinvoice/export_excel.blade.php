<html>
<body>
<table>
	
	<tr>
		<td><strong><center>PO Number</center></strong></td>
		<td><strong><center>Invoice Number</center></strong></td>
		<td><strong><center>Invoice Date</center></strong></td>
		<td><strong><center>BL (Shipping Reference)</center></strong></td>
		<td><strong><center>BL Date</center></strong></td>
	<td><strong><center>Product Code</center></strong></td>
		<td><strong><center>Product Name</center></strong></td>
		<td><strong><center>Qty</center></strong></td>
		<td><strong><center>Location Name</center></strong></td>

			</tr>
<tbody>
@foreach($data as $d)
<tr>
<td>{{$d->po_number}}</td>
<td>{{$d->invoice_no}}</td>
<td>{{$d->invoice_date}}</td>
<td>{{$d->bill_no}}</td>
<td>{{$d->bill_date}}</td>
<td>{{$d->material_code}}</td>
<td>{{$d->prdname}}</td>
<td>{{$d->qty}}</td>
<td>{{$d->location_name}}</td>
</tr>
@endforeach
</tbody>
</table>
</body>
</html>
