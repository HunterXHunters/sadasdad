  @extends('layouts.default')

  @extends('layouts.header')

  @extends('layouts.sideview')

  @section('content')

  
  @section('style')
      {{HTML::style('jqwidgets/styles/jqx.base.css')}}
       
  @stop

  @section('script')
      
      {{HTML::script('jqwidgets/jqxcore.js')}}
      {{HTML::script('jqwidgets/jqxdata.js')}}
      {{HTML::script('js/common-validator.js')}}
      {{HTML::script('js/jquery.validate.min.js')}}
      {{HTML::script('js/helper.js')}}
      {{HTML::script('jqwidgets/jqxbuttons.js')}}
      {{HTML::script('jqwidgets/jqxscrollbar.js')}}
      {{HTML::script('jqwidgets/jqxdatatable.js')}}
      {{HTML::script('jqwidgets/jqxtreegrid.js')}}
      {{HTML::script('scripts/demos.js')}}
@stop
<script type="text/javascript">

 

</script>
<style>

</style>


<div class="row">
<div class="col-md-12">
<div class="portlet light tasks-widget">
  <form  action ="{{url('iotreportimport')}}" method="POST" id= "iot_report_key" files="true" enctype="multipart/form-data">
  <input type="hidden" name="_token" id="csrf-token" value="{{ Session::token() }}">
    <div class="portlet-body">
        <div class="row">
            <div class="col-md-2">
                <div class="form-group">
                    <label for="select_report" class="control-label">Import IOT</label>
                   <input type="file" name="upload_iot_file" id="upload_iot_file" accept="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"/>
                </div>
            </div>
        
            

            <div class="col-md-2">
            <div class="form-group genra">
                <button type="button" id = "import_export_iot_report" class="btn green-meadow" style ="margin-top: 26px;
">Generate</button>
            </div>
          </div>
            </div>
    </div>
</div>
</form>    
</div>
</div>
</div>
<script>
 $("#import_export_iot_report").click(function() {

  var file=$('#upload_iot_file').val();

  if(file=="" || file==0){
    alert("please select file");
    return true;
  }else{
    var x = document.getElementsByTagName("form");
    x[0].submit();

  }
  


 }); 
</script>
        
@stop