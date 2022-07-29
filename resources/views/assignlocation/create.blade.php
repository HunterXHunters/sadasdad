@extends('layouts.default')

@extends('layouts.sideview')

@section('content')

<div class="box">

<div class="box-header with-border">
	<h3 class="box-title"><strong>Assign Location</strong>Create</h3>
</div>

<div class="col-sm-12">
  <div class="tile-body nopadding"> 



{{ Form::open(array('url' => 'assignlocation/store','id' => 'assignproduct')) }}
{{ Form::hidden('_method', 'POST') }}
<input type="hidden" name="entity_id" id="entity_id" value="{{$entity_id}}" />
<input type="hidden" name="org_id" id="org_id" value="{{$org_id}}" />
<input type="hidden" name="ware_id" id="ware_id" value="{{$ware_id}}" />
<h4><strong>Assign Product to Entity</strong></h4>
<div class="row">
	<div class="form-group col-sm-6">
        <label for="exampleInputEmail">Product Name</label>
        <div class="input-group ">
            <span class="input-group-addon addon-red"><i class="fa fa-keyboard-o"></i></span>
            <select name="product_id" id="product_id" class="form-control">
               @foreach($products as $key => $value)
                <option value="{{ $key}}">{{ $value}}</option>
               @endforeach
              </select>
        </div>
    </div>
    
    <div class="form-group col-sm-6">
        <label for="exampleInputEmail">Locator</label>
        <div class="input-group ">
            <span class="input-group-addon addon-red"><i class="fa fa-keyboard-o"></i></span>
            <input type="text" id="locator" name="locator" class='form-control' value="{{$entity_location}}" readonly/> 
        </div>
    </div>
</div>
   
<div>

{{ Form::submit('SAVE', array('class' => 'btn btn-primary')) }}
{{ Form::reset('CLEAR', array('class' => 'btn btn-primary', 'id' => 'resetform')) }}
{{ Form::close() }}
<!--{{ Form::button('CANCEL', array('class' => 'btn btn-primary')) }}
{{ Form::close() }}-->
</div>
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
  $(document).ready(function(){
      $('#assignproduct').bootstrapValidator({
          message: 'This value is not valid',
          feedbackIcons: {
              valid: 'glyphicon glyphicon-ok',
              invalid: 'glyphicon glyphicon-remove',
              validating: 'glyphicon glyphicon-refresh'
          },
          fields: {
              product_id: {
                  validators: {
                      callback: {
                          message: 'Please select Floor.',
                          callback: function(value, validator, $field){
                          var options = $('[id="product_id"]').val();
                          if (options != 0){
                            return (options != 0);
                          }else{
                            return false;
                          }
                        }
                      }
                  }
              }
          }
      }).on('success.form.bv', function(event) {
          //event.preventDefault();
          return true;
      });
  });

$('#assignproduct #resetform').click(function(){
  $('#assignproduct').data('bootstrapValidator').resetForm();
  $('#assignproduct')[0].reset();
});
</script>
  <script type="text/javascript">
  //   $(function(){
  //     $('form').validate({
  //       rules:{
  //         product_id:{
  //           required:true
  //         },package_id:{
  //           requiredDropdown:true
  //         }
  //       },
  //       messages:{
  //         product_id:{
  //           required:'Product is required.'
  //         },package_id:{
  //           requiredDropdown:'Package is required.'
  //         }
  //       },submitHandler:function(form){
  //         form.submit();
  //       },errorPlacement: function(error, element) {
  //         element.closest('td').append(error);
  //       },unhighlight: function (element, errorClass, validClass) {
  //         if ($(element).hasClass('optional') && $(element).val() == '') {
  //           $(element).removeClass('error valid');
  //         }else{
  //           $(element).removeClass('error').addClass('valid');
  //         }
  //       }
  //     });
  //   });
   </script>
@stop


@extends('layouts.footer')