 @extends('layouts.default')

@extends('layouts.sideview')

@section('content')
<aside class="right-side">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            UOM Groups
            <small>Create</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i>Home</a></li>
            <!-- <li class="active">Dashboard</li> -->
        </ol>
    </section>

    <!-- Main content -->
    <section class="content">
    <!--box-start-->
    <div class="box">
    
      <div class="box-body table-responsive">
<div class="container" style="width:auto !important;">





<!-- if there are creation errors, they will show here -->
{{ HTML::ul($errors->all()) }}

{{ Form::open(array('url' => 'uomgroup/store')) }}

<fieldset>
<legend>Create A UOM Group</legend>
<table width="70%" border="0" cellspacing="0" cellpadding="10">
  <tbody>
 
  <tr>
                <td>{{ Form::label('description', 'Description') }}</td>
                <td>:</td>
                <td>{{ Form::text('description', null, array('class' => 'form-control','required' => 'required')) }}</td>
                <td width="10"></td>
                <!-- <td>Validation Message</td> -->
  </tr>
  <tr>
                <td>{{ Form::label('status', 'Status') }}</td>
                <td>:</td>
                <td>{{ Form::select('status', array('' => 'Select Status','0' => 'InActive', '1' => 'Active'), null , array('class' => 'form-control','required' => 'required')) }}</td>
                <td width="10"></td>
                <!-- <td>Validation Message</td> -->
  </tr>    
</tbody>
</table>
</fieldset>
<br>
    {{ Form::submit('Create Uom Group!', array('class' => 'btn btn-primary')) }}

{{ Form::close() }}

</div>
@stop
@section('style')
    {{HTML::style('css/style.css')}}
@stop
@section('script')
  <script type="text/javascript">
    $(function(){
      $('form').validate({
        rules:{
          description:{
            required:true
          },status:{
            requiredDropdown:true
          }
        },
        messages:{
          description:{
            required:'description is required'
          },status:{
            requiredDropdown:'status is required'
          }
        },submitHandler:function(form){
          form.submit();
        },errorPlacement: function(error, element) {
          element.closest('td').append(error);
        },unhighlight: function (element, errorClass, validClass) {
          if ($(element).hasClass('optional') && $(element).val() == '') {
            $(element).removeClass('error valid');
          }else{
            $(element).removeClass('error').addClass('valid');
          }
        }
      });
    });
  </script>
@stop