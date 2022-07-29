@extends('layouts.default')
@extends('layouts.header')
@extends('layouts.sideview')
@section('content')




<div class="box">

<div class="box-header">
<h3 class="box-title">Edit <strong>Types</strong></h3>
</div>


<div class="row">



<div class="col-md-3">

<div class="form-group">
<label for="Entity Type Name">Entity Type Name</label>
<input type="text" name="entity_type_name" id="entity_type_name" value="{{$entity_type->entity_type_name}}" class="form-control">
</div>

</div>

<div class="col-md-3">

<div class="form-group">
<label for="Width">Status</label>
<select name="status" id="status" class="form-control">      
<option value="">Select Status</option>
@if($entity_type->status==1)
<option value="1" selected="selected">Active</option>
@else
<option value="1">Active</option>
@endif
@if($entity_type->status==2)
<option value="2" selected="selected">In-Active</option>
@else
<option value="2" >In-Active</option>
@endif     
</select>
</div>

</div>

<div class="col-md-3" style="margin-top:23px;">



<div class="margin">
{{ Form::open(array('url' => 'entitytypes/update/'.$entity_type->id)) }}
{{ Form::hidden('_method', 'PUT') }}

<div class="btn-group">
{{ Form::submit('UPDATE', array('class' => 'btn btn-primary')) }}
</div>
<div class="btn-group">
{{ Form::reset('CLEAR', array('class' => 'btn btn-block btn-danger')) }}
</div>
{{ Form::close() }}

</div>




</div>

</div>




</div>

    













@stop

@section('style')
    {{HTML::style('css/style.css')}}
@stop

@extends('layouts.footer')

@section('script')
  <script type="text/javascript">
    $(function(){
      $('form').validate({
        rules:{
          entity_type_name:{
            required:true
          }, status:{
            requiredDropdown:true
          }
        },messages:{
          entity_type_name:{
            required: 'Entity type Name is required'
          }, status:{
            requiredDropdown:'Status is required'
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