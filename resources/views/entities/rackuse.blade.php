@extends('layouts.default')
@extends('layouts.sideview')
@section('content')

<div class="box">

<div class="box-header">
	<h3 class="box-title"><strong>Rack Types </strong>Apply</h3>
</div>

<div class="col-sm-12">
  <div class="tile-body nopadding">  


<!-- {{ Form::open(array('url' => 'entities/rackusestore')) }}
{{ Form::hidden('_method', 'POST') }} -->
<input type="hidden" id="entity_type_id" name="entity_type_id" value="{{$entity_type_id}}" />
<input type="hidden" id="parent_entity_id" name="parent_entity_id" value="{{$parent_entity_id}}" />
<input type="hidden" name="org_id" id="org_id" value="{{ $org_id }}" class="input1">
<input type="hidden" name="location_id" id="location_id" value="{{ $location_id }}" class="input1">
<!-- <h4><strong>Rack Type</strong></h4> -->
<div class="row">	
    <div class="form-group col-sm-6">
        <label for="exampleInputEmail">Rack Types</label>
        <div class="input-group ">
            <span class="input-group-addon addon-red"><i class="fa fa-cube"></i></span>
            <select name="rack_id" id="rack_id" class="form-control">
                  <option value="">Select Rack Type</option>
                      @foreach($rackuse_details as $key => $value)
                        <option value="{{ $key}}">{{$value}}</option>
                      @endforeach
      		</select>
        </div>
    </div> 
<!--     {{ Form::submit('APPLY RACK TYPES', array('class' => 'btn btn-primary margin')) }}
{{ Form::close() }}  -->
<button data-toggle="modal" id="rackCopy" class="btn btn-primary" style="margin-top:25px;">APPLY RACK TYPES</button> 
</div>


</div>
</div>

<!---->
<div class="col-sm-12">
 <div class="tile-body nopadding">                  
     <button data-toggle="modal" id="rackdetails" class="btn btn-default" data-target="#basicvalCodeModal" style="display: none" data-url="{{URL::asset('/entities/rackusestore')}}">APPLY RACK TYPES</button>
    </div>
</div>
<div class="modal fade" id="basicvalCodeModal" tabindex="-1" role="dialog" aria-labelledby="basicvalCode" aria-hidden="true">
  <div class="modal-dialog wide">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" id="close_it_now" data-dismiss="modal" aria-hidden="true">X</button>
        <h4 class="modal-title" id="basicvalCode"><strong>Rack Types </strong>Apply</h4>
      </div>
        <div class="modal-body" id="entitiesDiv">
        </div>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
<!---->
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
          rack_id:{
            requiredDropdown:true
          }
        },messages:{
          rack_id:{
            requiredDropdown: 'Rack Type is required'
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
$('#rackCopy').click(function(){
  var rack_id=$('#rack_id').val();
  //console.log(zone_id);
  if(rack_id =='')
  alert('Please select');
  else
  $("#rackdetails").click();
});
$("#rackdetails").click(function(){
  copyRack();
  });
function copyRack()
{
  var entity_type_id=$('#entity_type_id').val();
  var parent_entity_id=$('#parent_entity_id').val();
  var org_id=$('#org_id').val();
  var location_id=$('#location_id').val(); 
  var rack_id=$('#rack_id').val(); 
  if(rack_id!='')
  $.get('/entities/rackusestore/'+entity_type_id+'/'+parent_entity_id+'/'+org_id+'/'+location_id+'/'+rack_id ,function(response){ 
        /*$("#basicvalCode").html('Edit Rack');*/
        $("#entitiesDiv").html(response);       
    });
}     
  </script>
@stop