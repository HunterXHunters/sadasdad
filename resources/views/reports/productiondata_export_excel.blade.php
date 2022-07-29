<table>
<thead>
	<tr>
		<td>Location</td>
		<td>Po Number</td>
		<td>Material Code</td>
		<td>Product Description</td>
		<td>Batch No</td>
		<td>Primary Id</td>
		<td>Parent Id</td>
		<td>Sync Date</td>
		<td>Sync Time</td>
		<td>GR</td>
	</tr>
</thead>
<tbody>

@foreach($data as $row)
<tr>
	<td>{{$row->location_name}}</td>
	<td>{{$row->po_number}}</td>
	<td><?PHP if(!empty($row->material_code)){echo "'".$row->material_code;}?></td>
	<td>{{$row->description}}</td>
	<td>{{$row->batch_no}}</td>
	<td><?PHP if(!empty($row->primary_id)){echo "'".$row->primary_id;}?></td>
	<td><?PHP if(!empty($row->parent_id)){echo "'".$row->parent_id;}?></td>
	<td>{{$row->sync_date}}</td>
	<td>{{$row->sync_time}}</td>
	<td>{{$row->GR}}</td>			
</tr>

@endforeach
</tbody>
</table>