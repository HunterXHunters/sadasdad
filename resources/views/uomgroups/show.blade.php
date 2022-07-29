 @extends('layouts.default')

@extends('layouts.sideview')

@section('content')
<aside class="right-side">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            Dashboard
            <small>Control panel</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
            <li class="active">Dashboard</li>
        </ol>
    </section>

    <!-- Main content -->
    <section class="content">
    <!--box-start-->
    <div class="box">
    
      <div class="box-body table-responsive">
<div class="container">

<nav class="navbar navbar-inverse">
   <ul class="nav navbar-nav">
        <li><a href="{{ URL::to('uomgroup') }}">View All Uom Groups</a></li>
        <li><a href="{{ URL::to('uomgroup/create') }}">Create a Uom Group</a>
    </ul>
</nav>

<h1>Showing a Uom Group</h1>

    <div class="jumbotron text-center">
        <h2>Uom Group</h2>
        <p>
            <strong>Description:</strong> {{ $uomgroup->description }}<br>
            @if($uomgroup->status)<strong>Status:</strong>Active
            @else<strong>Status:</strong>InActive
            @endif
        </p>
    </div>

</div>
@stop



