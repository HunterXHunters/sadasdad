@extends('layouts.default')

@extends('layouts.sideview')

@section('content')


<div class="box">

<div class="box-header with-border">
	<h3 class="box-title"><strong>Entities </strong>List</h3>
</div>

<div class="col-sm-12">
  <div class="tile-body nopadding"> 

  {{ Form::open(array('url' => '','id'=>'addZoneType')) }}

	<h4><strong>Zone Details</strong></h4>
    <input type="hidden" id="org_parent_entity_id" name="org_parent_entity_id" value="{{$parent_entity_id}}" />
    <div class="row">    
        <div class="form-group col-sm-6">
            <label class="control-label" for="exampleInputEmail">Select Floor</label>
             <div class="input-group ">
                <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
                <select name="warehouse_id" id="warehouse_id" class="form-control" >
                <option value=''>Select Floor</option>
                  @foreach($warehouse_details as $key => $value)
                    <option value="{{ $key}}">{{ $value}}</option>
                  @endforeach
                </select>
            </div>
        </div>
        <div class="form-group col-sm-6">
            <label class="control-label" for="exampleInputEmail">Select Zone</label>
             <div class="input-group ">
                <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
                <select name="zone_id" id="zone_id" class="form-control">
                  <option value=''>Select Zone</option>
                </select>
            </div>
        </div>
        <button data-toggle="modal" id="zoneCopy" class="btn btn-primary margin">Copy</button> 
    </div>
    {{ Form::close() }}


<div class="col-sm-12">
 <div class="tile-body nopadding">                  
     <button data-toggle="modal" id="zonedetails" class="btn btn-default" data-target="#basicvalCodeModal" style="display: none" data-url="{{URL::asset('/entities/zonedetails')}}">Copy</button>
  </div>
</div>
 <!--<a class="btn btn-primary" href=" {{ URL::to('entities/zonedetails/') }}" >CREATE A COPY OF ZONE</a>-->

<div class="modal fade" id="basicvalCodeModal" tabindex="-1" role="dialog" aria-labelledby="basicvalCode" aria-hidden="true">
  <div class="modal-dialog wide">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" id="close_it_now" data-dismiss="modal" aria-hidden="true">X</button>
        <h4 class="modal-title" id="basicvalCode">Edit Zone</h4>
      </div>
        <div class="modal-body" id="entitiesDiv">
        </div>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

</div>
</div>
    </div>

@stop
@section('script')
<script type="text/javascript">
  $(document).ready(function(){
      $('#addZoneType').bootstrapValidator({
          message: 'This value is not valid',
          feedbackIcons: {
              valid: 'glyphicon glyphicon-ok',
              invalid: 'glyphicon glyphicon-remove',
              validating: 'glyphicon glyphicon-refresh'
          },
          fields: {
              warehouse_id: {
                      validators: {
                          callback: {
                              message: 'Please select Floor.',
                              callback: function(value, validator, $field){
                              var options = $('[id="warehouse_id"]').val();
                              return (options != 0);
                            }
                          }
                      }
                  },
              zone_id: {
                      validators: {
                          callback: {
                              message: 'Please select Zone.',
                              callback: function(value, validator, $field){
                              var options = $('[id="zone_id"]').val();
                              return (options != 0);
                            }
                          }
                      }
                  }
          }
      }).on('success.form.bv', function(event) {
          //event.preventDefault();
          $("#zonedetails").click();
          return true;
      });
  });
</script>

<script type="text/javascript">
    
$("#zonedetails").click(function(){
  copyZone();
});

function copyZone()
{
  var org_parent_entity_id=$('#org_parent_entity_id').val();
  var zone_id=$('#zone_id').val();
  if(org_parent_entity_id !=''&& zone_id!='')
  $.get('/entities/zonedetails/'+org_parent_entity_id+'/'+zone_id ,function(response){ 
        $("#basicvalCode").html('Edit Zone');
        $("#entitiesDiv").html(response);       
    });
}

$(document).ready(function(){ 
  $("#warehouse_id").change(function(){
    var wid = $('#warehouse_id').val();
      $.ajax({
        url: "/entities/getzones/"+wid,
        success: function(result){
          var sel = '';
          $("#zone_id").html(result);
        }
      });
  }); 
}); 
</script>  
@stop

@section('style')
    {{HTML::style('css/style.css')}}
@stop

@section('script')
    {{HTML::script('js/jquery.js')}}

    
    
@stop

