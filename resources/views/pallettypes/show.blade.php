 @extends('layouts.default')

@extends('layouts.sideview')

@section('content')
<div class="container">

<nav class="navbar navbar-inverse">
   <ul class="nav navbar-nav">
        <li><a href="{{ URL::to('pallettype') }}">View All Pallet Types</a></li>
        <li><a href="{{ URL::to('pallettype/create') }}">Create a Pallet Type</a>
    </ul>
</nav>


    <div class="jumbotron text-center">
        <h2>Pallet Type</h2>
        <p>
            <strong>Pallet Name:</strong> {{ $pallettype->pallet_name }}<br>
            @if($pallettype->status)<strong>Status:</strong>Active
            @else<strong>Status:</strong>InActive
            @endif
        </p>
    </div>

</div>
@stop



