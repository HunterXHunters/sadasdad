@extends('layouts.default')

@extends('layouts.header')

@extends('layouts.sideview')

@section('content')
  
 <!-- @section('style')
    {{HTML::style('jqwidgets/styles/jqx.base.css')}}
  @stop -->
  <div class="box">
    <div class="box-header">
      <h3 class="box-title"><strong>Stock Transfer 
  </strong> Report </h3>                   
    </div>
  </div>
    <iframe width="1250" height="900" src="https://app.powerbi.com/view?r=eyJrIjoiZDk0ODk1NjctYzY1ZC00ODJlLThhNTAtOTdmNzZlYjI2NmQ2IiwidCI6ImY1YzNjMWViLTNmZjQtNDI3OS05NjI3LWY2OGYxMDFjYzdkMiJ9" frameborder="0" allowFullScreen="true" style="margin-top:-80px;width:1270px;background:#fff;"></iframe>
@stop      