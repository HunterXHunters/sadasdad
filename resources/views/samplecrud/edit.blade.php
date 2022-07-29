Adding a New User

{{ Form::open(array('url' => 'grid/update/'.$cuser->id)) }}
{{ Form::hidden('_method', 'PUT') }}
<table border="1">
	<tr>
		<td>Name</td>
		<td><input type="text" id="uname" name="uname" value="{{$cuser->name}}"/></td>
	</tr>
	<tr>
		<td>Email Id</td>
		<td><input type="text" id="email_id" name="email_id" value="{{$cuser->email_id}}" /></td>
	</tr>
	<tr>
		<td>Phone Number</td>
		<td><input type="text" id="phone" name="phone" value="{{$cuser->phone}}" /></td>
	</tr>

</table>

{{ Form::submit('Update') }}