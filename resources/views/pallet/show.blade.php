 @extends('layouts.default')

@extends('layouts.sideview')

@section('content')
<div class="container">

<nav class="navbar navbar-inverse">
   <ul class="nav navbar-nav">
        <li><a href="{{ URL::to('pallets') }}">View All Pallets</a></li>
        <li><a href="{{ URL::to('pallets/create') }}">Create a Pallet</a>
    </ul>
</nav>

<h1>Showing a Pallet</h1>

    <div class="jumbotron text-center">
        
        <p>
            <strong>ID:</strong> {{ $pallet->id }}<br>
            <strong>Pallet Type:</strong> {{ PalletType::find($pallet->pallet_type_id)->pallet_name}}<br>
            <strong>Weight Uom:</strong> {{ Uom::find($pallet->weightUOMId)->description}}<br>
            <strong>Weight:</strong> {{ $pallet->weight }}<br>
            <strong>Dimension Uom:</strong> {{ Uom::find($pallet->dimensionUOMId)->description}}<br>
            <strong>Height:</strong> {{ $pallet->height }}<br>
            <strong>Width:</strong> {{ $pallet->width }}<br>
            <strong>Length:</strong> {{ $pallet->length }}<br>        
        </p>
    </div>

</div>
@stop



