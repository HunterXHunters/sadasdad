<h1>Adding a New User</h1>

{{ Form::open(array('url' => 'grid/store')) }}
{{ Form::hidden('_method', 'POST') }}

<table border="1">
	<tr>
		<td>Name</td>
		<td><input type="text" id="uname" name="uname" /></td>
	</tr>
	<tr>
		<td>Email Id</td>
		<td><input type="text" id="email_id" name="email_id" /></td>
	</tr>
	<tr>
		<td>Phone Number</td>
		<td><input type="text" id="phone" name="phone" /></td>
	</tr>

</table>

{{ Form::submit('Submit') }}