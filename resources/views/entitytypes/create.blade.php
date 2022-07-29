@extends('layouts.default')
@extends('layouts.header')
@extends('layouts.sideview')
@section('content')





<div class="box">

<div class="box-header">
<h3 class="box-title">Entity <strong>Type</strong></h3>
</div>


<div class="row">



<div class="col-md-3">

<div class="form-group">
<label for="Entity Type Name">Entity Type Name</label>
<input type="text" name="entity_type_name" id="entity_type_name" value="" class="form-control">
</div>

</div>

<div class="col-md-3">

<div class="form-group">
<label for="Status">Status</label>

<select name="status" id="status" class="form-control">
<option value="">Select Status</option>
<option value="1">Active</option>
<option value="2">In-Active</option>
</select>

</div>

</div>

<div class="col-md-3" style="margin-top:23px;">



<div class="margin">
          {{ Form::open(array('url' => 'entitytypes/store')) }}
          {{ Form::hidden('_method', 'POST') }}

<div class="btn-group">
           {{ Form::submit('SAVE', array('class' => 'btn btn-primary')) }}
</div>
<div class="btn-group">
          {{ Form::reset('CLEAR', array('class' => 'btn btn-primary')) }}
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
          },status:{
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