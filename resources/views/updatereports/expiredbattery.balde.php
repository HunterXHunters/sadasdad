@extends('layouts.default')

@extends('layouts.header')

@extends('layouts.sideview')

@section('content')
  
 <!-- @section('style')
    {{HTML::style('jqwidgets/styles/jqx.base.css')}}
  @stop -->
  <div class="box">
    <div class="box-header">
      <h3 class="box-title"><strong>Battery Aging
  </strong>Report </h3>                   
    </div>
  </div>
  
<iframe width="1250" height="900" src="https://app.powerbi.com/view?r=eyJrIjoiODdhOTNkYjYtMjQxZi00MTg4LThiYTMtZmZmY2EwMTU5Y2E1IiwidCI6ImY1YzNjMWViLTNmZjQtNDI3OS05NjI3LWY2OGYxMDFjYzdkMiJ9" frameborder="0" allowFullScreen="true" style="margin-top:-80px;width:1270px;background:#fff;"></iframe>

@stop      