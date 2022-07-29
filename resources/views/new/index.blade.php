@extends('layouts.default')

@extends('layouts.header')

@extends('layouts.sideview')

@section('content')
  
  @section('style')
    {{HTML::style('jqwidgets/styles/jqx.base.css')}}
  @stop
  <div class="box">
    <div class="box-header">
<!--       <h3 class="box-title"><strong>Production  </strong>  Dispatch</h3>                   
 -->    </div>
    <iframe  width="1250" height="900" src="https://app.powerbi.com/view?r=eyJrIjoiNDgyNjQxNDUtMmJmOS00MmZjLWFhZGUtOGZkOWVkZTBkYTBiIiwidCI6ImY1YzNjMWViLTNmZjQtNDI3OS05NjI3LWY2OGYxMDFjYzdkMiJ9" frameborder="0" allowFullScreen="true"></iframe> 
  </div>
@stop      