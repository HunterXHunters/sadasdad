<? php
header('Content-disposition: inline');
header('Content-type: application/msword'); // not sure if this is the correct MIME type
readfile('MyWordDocument.doc');
exit;
?>
@extends('layouts.default')

@extends('layouts.header')

@extends('layouts.sideview')

@section('content')
  
 <!-- @section('style')
    {{HTML::style('jqwidgets/styles/jqx.base.css')}}
  @stop -->
  <div class="box">
    <div class="box-header">
      <h3 class="box-title"><strong>User Manual
  </strong> </h3>                   
    </div>
    <iframe width="1250" height="900"  src="http://vguard.esealinc.com:555/download/qrimages/import.pdf" allowFullScreen="true" frameborder=0></iframe>
   <!-- <object data="http://vguard.esealinc.com:555/download/qrimages/import.pdf" type="application/pdf">
        <embed src="http://vguard.esealinc.com:555/download/qrimages/import.pdf" type="application/pdf" />
    </object> -->
 
  </div>
 
@stop      