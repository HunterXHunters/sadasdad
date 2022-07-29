 @extends('layouts.default')

@extends('layouts.sideview')

@section('content')
<aside class="right-side">
    <!-- Content Header (Page header) -->
    <section class="content-header">
         <h1>
            Pallet Type
            <small>Edit</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href=""><i class="fa fa-dashboard"></i> Home</a></li>
            <!-- <li class="active">Dashboard</li> -->
        </ol>
    </section>

    <!-- Main content -->
    <section class="content">
    <!--box-start-->
    <div class="box">
    
      <div class="box-body table-responsive">
 <div class="container" style="width:auto !important;">

{{ HTML::ul($errors->all()) }}
{{ Form::model($pallettype, array('route' => array('pallettype.update', $pallettype->id), 'method' => 'PUT')) }}
    <fieldset>
    <center>
    <table width="70%" border="0" cellspacing="0" cellpadding="10">
    <tbody>
    <tr>
      <td> {{ Form::label('pallet_name)', 'Pallet Name') }}</td>
      <td>:</td>
      <td>{{ Form::text('pallet_name', $pallettype->pallet_name, array('class' => 'form-control','required' => 'required')) }}</td>
      <td width="10"></td>
      <!-- <td>Validation Message</td> -->
    </tr>
    <tr>
      <td>{{ Form::label('status', 'Status') }}</td>
      <td>:</td>
      <td>{{ Form::select('status', array('0' => 'InActive', '1' => 'Active'), $pallettype->status, array('class' => 'form-control')) }}</td>
      <td width="10"></td>
      <!-- <td>Validation Message</td> -->
    </tr>
    </tbody>
    </table>
    </center>
</fieldset>
<br>
    {{ Form::submit('Update the Pallet Type!', array('class' => 'btn btn-primary')) }}

{{ Form::close() }}

</div>
 </div>
    </div>
    <!-- /.box-end -->
    
  </section><!-- /.content -->
</aside><!-- /.right-side -->
@stop
@section('style')
    {{HTML::style('css/style.css')}}
@stop
@section('script')
  <script type="text/javascript">
    $(function(){
      $('form').validate({
        rules:{
          pallet_name:{
            required:true
          },status:{
            requiredDropdown:true
          }
        },
        messages:{
          pallet_name:{
            required:'pallet name is required'
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





