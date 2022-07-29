 @extends('layouts.default')

@extends('layouts.sideview')

@section('content')
<div class="container">

<nav class="navbar navbar-inverse">
   <ul class="nav navbar-nav">
        <li><a href="{{ URL::to('uoms') }}">View All Uoms</a></li>
        <li><a href="{{ URL::to('uoms/create') }}">Create a Uom</a>
    </ul>
</nav>

<h1>Showing a Uom</h1>

    <div class="jumbotron text-center">
        <h2>{{ $uom->description  }} </h2>
        <p>
            <strong>Code:</strong> {{ $uom->code }}<br>
            <strong>Uom Group:</strong> {{ UomGroup::find($uom->uom_group_id)->description}}<br>
            @if($uom->status)<strong>Status:</strong> Active
            @else <strong>Status:</strong>InActive
            @endif
        </p>
    </div>

</div>
@stop



