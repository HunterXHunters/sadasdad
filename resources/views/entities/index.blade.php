 @extends('layouts.default')

@extends('layouts.sideview')

@section('content')

<div class="container">

<nav class="navbar navbar-inverse">
    <ul class="nav navbar-nav">
        <li><a href="{{ URL::to('entities') }}">View All Entities</a></li>
        <li><a href="{{ URL::to('entities/create/1/0') }}">Create a Entity</a>
    </ul>
</nav>

<h1>All the Entities</h1>

<!-- will be used to show any messages -->
@if (Session::has('message'))
    <div class="alert alert-info">{{ Session::get('message') }}</div>
@endif

<table class="table table-striped table-bordered">
    <thead>
        <tr>
            <td>ID</td>
            <td>Name</td>
            <td>Status</td>
            <td>parent Entity</td>
            <td>Actions</td>
        </tr>
    </thead>
    <tbody>
    @foreach($entities as $key => $value)

        <tr>
            <td>{{ $value->id }}</td>
            <td>{{ $value->entity_name }}</td>
            <td>{{ $value->status }}</td>
            <td>{{ $value->parent_entity_id }}</td>
            <!-- we will also add show, edit, and delete buttons -->
            <td>

                <!-- delete the nerd (uses the destroy method DESTROY /nerds/{id} -->
                 {{ Form::open(array('url' => 'entities/delete/' . $value->id, 'class' => 'pull-right')) }}
                    {{ Form::hidden('_method', 'DELETE') }}
                    {{ Form::submit('Delete', array('class' => 'btn btn-warning')) }}
                {{ Form::close() }}

                <!-- show the nerd (uses the show method found at GET /nerds/{id} 
                <a class="btn btn-small btn-success" href="{{ URL::to('entities/show/' . $value->id) }}">View</a>-->

                <!-- edit this nerd (uses the edit method found at GET /nerds/{id}/edit -->
                <a class="btn btn-small btn-info" href=" {{ URL::to('entities/edit/' . $value->id ) }} ">Edit</a>

            </td>
        </tr>
    @endforeach
    </tbody>
</table>

</div>

@stop

@section('script')
    {{HTML::script('css/style.css')}}
@stop

@extends('layouts.footer')