@extends('layouts.default')
@extends('layouts.header')
@extends('layouts.sideview')
@section('content')


{{ Form::open(array('url' => 'lookup')) }}
<div class="row">
                     
    <div class="form-group col-sm-6">
        {{ Form::label('name', 'Name') }}
        {{ Form::text('name', Input::old('name'), array('class' => 'form-control')) }}
    </div>
</div>
<div class="row">
                     
    <div class="form-group col-sm-6">
        {{ Form::label('name', 'Name') }}
        {{ Form::text('name', Input::old('name'), array('class' => 'form-control')) }}
    </div>
    <div class="form-group col-sm-6">
        {{ Form::label('Categories', 'Categories') }}
    	{{Form::select('user', array_pluck($LookupCategories, 'name', 'id'));}}
    </div>
    

    
</div>
<div class="row">

	<div class="form-group col-sm-6">
        {{ Form::label('description', 'Description') }}
        {{ Form::textarea('Description', Input::old('Description'), array('class' => 'form-control')) }}
    </div>
<div>
<div class="row">
	<div class="form-group col-sm-6">
    {{ Form::submit('Create the Nerd!', array('class' => 'btn btn-primary')) }}
    <div>
<div>
{{ Form::close() }}

@stop