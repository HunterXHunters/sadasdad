<table>
<thead>
	<tr>
		<td>Location Code</td>
		<td>Login ID</td>
		<td>Material Code</td>
		<td>Product Name</td>
		<td>Batch Number</td>
		<td>Primary ID</td>
		<td>Level</td>
		<td>Parent ID</td>
		<td>Qty</td>
		<td>Physical Location</td>
		<td>Eseal Location</td>
		<td>Phy Inv Date</td>
		<td>Remarks</td>
	</tr>
</thead>
<tbody>

@foreach($data as $row)
<tr>
	<td>{{$row->erp_code}}</td>
	<td>{{$row->username}}</td>
	<td><?PHP if(!empty($row->material_code)){echo '"'.$row->material_code;}?></td>
	<td>{{$row->name}}</td>
	<td>{{$row->batch_no}}</td>
	<td><?PHP if(!empty($row->primary_id)){echo '"'.$row->primary_id;}?></td>
	<td>{{$row->level}}</td>
	<td><?PHP if(!empty($row->parent_id)){echo '"'.$row->parent_id;}?></td>
	<td>{{$row->qty}}</td>
	<td>{{$row->physical_location}}</td>
	<td>{{$row->eseal_location}}</td>
	<td>{{$row->phydate}}</td>
	<td>{{$row->Remarks}}</td>
	
	
</tr>

@endforeach
</tbody>
</table>