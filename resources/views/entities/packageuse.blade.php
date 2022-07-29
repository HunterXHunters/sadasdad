 @extends('layouts.default')

@extends('layouts.sideview')

@section('content')
<div class="container">


{{ Form::open(array('url' => 'package/packageusestore')) }}
{{ Form::hidden('_method', 'POST') }}

<fieldset>
<legend>Entity Type</legend>
<table width="100%" border="0" cellspacing="0" cellpadding="6">
  <tbody>
   <tr>
      
       <td>Rack Types:</td>
                <td><select name="rack_type_id" id="rack_type_id" class="select1">
                  <option value="">Select Rack Type</option>
                      @foreach($package_details as $key => $value)
                        <option value="{{ $key}}">{{$value}}</option>
                      @endforeach
      </select></td>
    </tr>    
  </tbody>
</table>
</fieldset>
 {{ Form::submit('APPLY RACK TYPES', array('class' => 'btn btn-primary')) }}
{{ Form::close() }}

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
          rack_type_id:{
            requiredDropdown:true
          }
        },messages:{
          rack_type_id:{
            requiredDropdown:'Rack Type is required'
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