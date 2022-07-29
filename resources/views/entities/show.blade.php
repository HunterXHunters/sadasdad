 @extends('layouts.default')

@extends('layouts.sideview')

@section('content')
<div class="container">

<nav class="navbar navbar-inverse">
   <ul class="nav navbar-nav">
        <li><a href="{{ URL::to('entitytypes') }}">View All Entity Types</a></li>
        <li><a href="{{ URL::to('entitytypes/create') }}">Create a Entity Type</a>
    </ul>
</nav>

<h1>Showing {{ $entity_type->entity_type_name }}</h1>

    <div class="jumbotron text-center">
        <h2>{{ $entity_type->entity_type_name }}</h2>
        <p>
            <strong>Status:</strong> {{ $entity_type->status }}<br>
            <strong>Created At:</strong> {{ $entity_type->created_at }}<br>
            <strong>Updated At:</strong> {{ $entity_type->updated_at }}
        </p>
    </div>

</div>
@stop

@extends('layouts.footer')
