 @extends('layouts.default')

@extends('layouts.sideview')

@section('content')

<aside class="right-side">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>Edit  {{ $uom->description }}</h1>
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



<!-- if there are creation errors, they will show here -->
{{ HTML::ul($errors->all()) }}

{{ Form::model($uom, array('route' => array('uoms.update', $uom->id), 'method' => 'PUT')) }}
<fieldset>
<legend>Edit  {{ $uom->description }}</legend>
<table width="50%" border="0" cellspacing="0" cellpadding="4">
  <tbody>
<tr>
                <td>{{ Form::label('code', 'Code') }}</td>
                <td>:</td>
                <td>{{ Form::text('code', null, array('class' => 'form-control','required' => 'required')) }}</td>
                <td width="10"></td>
                <!-- <td>Validation Message</td> -->
              </tr>  
 
  
  
<tr>
                <td>{{ Form::label('uom_group_id', 'Uom Group') }}</td>
                <td>:</td>
                <td>{{ Form::select('uom_group_id', $uomgroup , null, array('class' => 'form-control')) }}</td>
                <td width="10"></td>
                <!-- <td>Validation Message</td> -->
              </tr>
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
                <td>{{ Form::select('status', array('0' => 'InActive', '1' => 'Active'), null, array('class' => 'form-control')) }}</td>
                <td width="10"></td>
                <!-- <td>Validation Message</td> -->
              </tr>
   <tr>
                <td>{{ Form::label('parent_uom_id', 'Parent UOM Id') }}</td>
                <td>:</td>
                <td>{{ Form::select('parent_uom_id',$uoms, null, array('class' => 'form-control','required' => 'required')) }}</td>
                <td width="10"></td>
                <!-- <td>Validation Message</td> -->
              </tr>            
  
    <tr>
 
    <td width = "30%"class="form-group">
         
         <!-- {{ Form::text('uom_group_id', null,array('class' => 'form-control')) }} -->
         
    </td>

    <td width = "30%"class="form-group">
         
         
    
</tr>
</tbody>
</table>
</fieldset>
<br>
    {{ Form::submit('UPDATE', array('class' => 'btn btn-primary')) }}

{{ Form::close() }}

</div>


</div>
                </div>
                <!-- /.box-end -->
                
              </section><!-- /.content -->
            </aside><!-- /.right-side -->
@stop

@section('script')
  <script type="text/javascript">
    $(function(){
      $('form').validate({
        rules:{
          code:{
            required:true
          },description:{
            required:true
          },uom_group_id:{
            requiredDropdown:true
          },status:{
            required:true
          }
        },messages:{
           code:{
            required:'code is required'
          },description:{
            required:'description is required'
          },uom_group_id:{
            requiredDropdown:'uom dimension is required'
          },status:{
            required:'status is required'
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