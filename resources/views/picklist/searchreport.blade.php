  @extends('layouts.default')

  @extends('layouts.header')

  @extends('layouts.sideview')

  @section('content')

  
  @section('style')
    {{HTML::style('jqwidgets/styles/jqx.base.css')}}
     
@stop

@section('script')
    {{HTML::script('jqwidgets/jqxcore.js')}}
    {{HTML::script('jqwidgets/jqxbuttons.js')}}
    {{HTML::script('jqwidgets/jqxscrollbar.js')}}
    {{HTML::script('jqwidgets/jqxmenu.js')}}
    {{HTML::script('jqwidgets/jqxgrid.js')}}
    {{HTML::script('jqwidgets/jqxgrid.selection.js')}}
    {{HTML::script('jqwidgets/jqxgrid.columnsresize.js')}}
    {{HTML::script('jqwidgets/jqxdata.js')}}
    {{HTML::script('scripts/demos.js')}}
    {{HTML::script('jqwidgets/jqxlistbox.js')}}
    {{HTML::script('jqwidgets/jqxdropdownlist.js')}}
    {{HTML::script('jqwidgets/jqxgrid.pager.js')}}
    {{HTML::script('jqwidgets/jqxgrid.sort.js')}}
    {{HTML::script('jqwidgets/jqxgrid.filter.js')}}
    {{HTML::script('jqwidgets/jqxgrid.storage.js')}}
    {{HTML::script('jqwidgets/jqxgrid.columnsreorder.js')}}
    {{HTML::script('jqwidgets/jqxpanel.js')}}
    {{HTML::script('jqwidgets/jqxcheckbox.js')}}


<script type="text/javascript">
function statusmessage()
{
  $('#failureid').delay(3000).fadeOut(1500);
}
function binReport()
{
    var manufacturer_id = $('#manufacturer_id').val();
    var warehouse_id = $('#warehouse_id').val();
    var location_id = $('#location_id').val();
    var product_id = $('#product_id').val();
    var attribute_id = $('#attribute_id').val();
    var searchVal = $('#searchVal').val();
    if (manufacturer_id == ""){
       document.getElementById('failureid').style.display='block';
       document.getElementById('failureid').innerHTML='Please select a manufacturer'; 
       $("#failureid").addClass("callout callout-danger");
       $("#failureid").removeClass("callout-success");
       $("#failureid").removeClass("callout-info");
       $("#failureid").removeClass("callout-warning");
       statusmessage();             
    }else{  
    if(manufacturer_id=='undefined')
      manufacturer_id=0;
    if(warehouse_id=='')
      warehouse_id=0;
    if(location_id=='')
      location_id=0;
    if(attribute_id=='')
      attribute_id=0;
    if(product_id=='')
      product_id=0; 
    if(searchVal=='')
      searchVal=0; 
    //var filterdata=$('#serialize').serializeArray();
    var filterdata = JSON.parse(JSON.stringify(jQuery('#serialize').serializeArray()));
    filterdata = $.param(filterdata);
    //console.log(filterdata);
    if(filterdata == '')
     filterdata = 0; 
    //console.log(data);
    //return false;
      getBinReport(manufacturer_id,location_id,product_id,attribute_id,filterdata);
    }
}

// $(document).ready(function () 
// {
//     getBinReport();
// });

function getBinReport(manufacturer_id,location_id,product_id,attribute_id,filterdata)
{    
  var manufacturer_id = manufacturer_id || 0,
      location_id = location_id || 0,
      product_id = product_id || 0,
      attribute_id = attribute_id || 0,
      filterdata = filterdata || 0;
    //alert(searchVal);
    $.ajax(
              {
                  url: "picklist/getReportData/"+manufacturer_id+"/"+location_id+"/"+product_id+"/"+attribute_id+"/"+filterdata,
                  success: function(result)
                  {
                      var employees = result;
                      // prepare the data
                      var source =
                      {
                          datatype: "json",
                          datafields: [
                              { name: 'product', type: 'string' },
                              { name: 'product_name', type: 'string'},
                              { name: 'attribute', type: 'string' },
                              { name: 'value', type: 'string' },
                              { name: 'parent', type: 'string' },
                              { name: 'bin', type: 'string' }/*,
                              { name: 'actions', type: 'string' }*/
                          ],
                          id: 'eseal_id',
                          //url: url,
                          localData: employees,
                          pager: function (pagenum, pagesize, oldpagenum) {
                              // callback called when a page or page size is changed.
                          }
                      };
                      var dataAdapter = new $.jqx.dataAdapter(source);
                      // create Tree Grid
                      $("#jqxgrid").jqxGrid(
                      {
                          width:"100%",
                          source: source,
                          selectionmode: 'multiplerowsextended',
                          sortable: true,
                          pageable: true,
                          autoheight: true,
                          autoloadstate: false,
                          autosavestate: false,
                          columnsresize: true,
                          columnsreorder: true,
                          filterable: true,
                          //showfilterrow: true,
                          columns: [
                           { text: 'Eseal Code', datafield: 'product', width:"15%"},
                           { text: 'Product', datafield: 'product_name', width:"20%"},
                           { text: 'Attribute', datafield: 'attribute', width: "20%"},
                           { text: 'Value', datafield: 'value', width: "15%"},
                           { text: 'Pallet', datafield: 'parent', width: "10%" },
                           /*{ text: 'Dimensions UOM', datafield: 'dimensionUOMId', width: "13%"},
                           { text: 'Height', datafield: 'height', width: "10%" },
                           { text: 'Width', datafield: 'width', width: "10%" },
                           { text: 'Length', datafield: 'length', width: "10%" },*/
                           { text: 'Bin', datafield: 'bin', width: "20%" }
                           //{ text: 'Actions', datafield: 'actions',width:"15%" }
                          ]
                      });


                  }
              });
}   
</script>
<script type="text/javascript">
$(document).ready(function(){ 
  document.getElementById('failureid').style.display='none'; 

  if($('#manufacturer_id').val()!='')
  {
      binReport();
  }

  $("#manufacturer_id").change(function(){
    var ware_id = $('#manufacturer_id').val();
    if(ware_id=='')
      ware_id=0;
      $.ajax({
        url: "/getFilterWarehouse/"+ware_id,
        success: function(result){
          var sel = '';
          $("#warehouse_id").html(result);
        }
      });
  });

  $("#warehouse_id").change(function(){
    var ware_id = $('#warehouse_id').val();
    var manufacturer_id = $('#manufacturer_id').val();
    if(ware_id=='')
      ware_id=0;
    if(manufacturer_id=='')
      manufacturer_id=0;
    $.ajax({
        url: "/picklist/getWareLocations/"+manufacturer_id+"/"+ware_id,
        success: function(result){
          var sel = '';
          $("#location_id").html(result);
        }
      });
  });

  $("#location_id").change(function(){
    var location_id = $('#location_id').val();
    var manufacturer_id = $('#manufacturer_id').val();
    if(location_id=='')
      location_id=0;
    if(manufacturer_id=='')
      manufacturer_id=0;
      $.ajax({
        url: "/picklist/getProducts/"+manufacturer_id+"/"+location_id,
        success: function(result){
          var sel = '';
          $("#product_id").html(result);
        }
      });
  });

  $("#product_id").change(function(){
    var location_id = $('#location_id').val();
    var manufacturer_id = $('#manufacturer_id').val();
    var product_id = $('#product_id').val();
    if(location_id=='')
      location_id=0;
    if(manufacturer_id=='')
      manufacturer_id=0;
    if(product_id=='')
      product_id=0;
      $.ajax({
        url: "/picklist/getAttributes/"+manufacturer_id+"/"+location_id+"/"+product_id,
        success: function(result){
          var sel = '';
          $("#attribute_id").html(result);
        }
      });
  });


});
</script>
<script type="text/javascript">
 $(document).ready(function(){
 var cnt = 2;
 $("#anc_add").click(function(){
var report_attribute_id = $('#attribute_id').val();
var report_attribute_text = $('#attribute_id option:selected').text()
var values = $('#searchVal').val();
var report_operator = $('#operator').val();
var report_operator_text = $('#operator option:selected').text();
if(report_attribute_id == 0 || report_attribute_id == '')
{
    alert('Please select attribtue.');
    return false;
}else if(report_operator == 0 || report_operator == '')
{
    alert('Please select Operator.');
    return false;
}else if(values == '')
{
    alert('Please enter a value.');
    return false;
}
var jsonArg = {};
jsonArg.attribute_id = report_attribute_id;
jsonArg.values = values;
jsonArg.report_operator_text = report_operator_text;
var hiddenJsonData = new Array();
hiddenJsonData.push(jsonArg);
 $('#assign_data').append('<tr><td scope="row" id="report_attribute_id">' + report_attribute_text + '</td><td id="report_operator">' + report_operator_text+ '</td><td id="values">' + values + '</td><td><a href="javascript:void(0);" class="check-toggler" id="remCF"><span class="badge bg-red"><i class="fa fa-trash-o"></i></span></a><input type="hidden" id="filter" name="filter[]" value=' + "'" + JSON.stringify(jsonArg) + "'" + ' /></td></tr>');
// $('#search').val(JSON.stringify(jsonArg));
/* cnt++;*/
 });
$("#assign_data").on('click', '#remCF', function () {
  //console.log($(this).parent().parent());
    $(this).parent().parent().remove();
}); 
$("#anc_rem").click(function(){
if($('#tbl1 tr').size()>1){
 $('#tbl1 tr:last-child').remove();
 }else{
 alert('One row should be present in table');
 }
 });
 
});
 </script>
@stop

<div class="box">
    <div class="box-header">
            <h3 class="box-title"><strong>Search-Criteria </strong>  Report</h3>
    </div>
    <div class="box-body">
   
      <div class="row"> 
      <div id="failureid" class="callout" align="center"></div>                   
          @if(empty($manufacturerId))
          <div class="form-group col-sm-5">
            <label for="exampleInputEmail">Manufacturer</label>
            <div class="input-group ">
              <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
               <select class="form-control" id="manufacturer_id" name="manufacturer_id">
                    <option  value="">Select Manufacturer</option>
                    @if(!empty($orgs))
                      @foreach($orgs as $key=>$value)
                        <option value="{{$key}}">{{$value}}</option>
                      @endforeach
                    @endif                            
                </select>
            </div>
            </div>
            @else
               <input type="hidden" id="manufacturer_id" name="manufacturer_id" value="{{$manufacturerId}}">
            @endif

            <div class="form-group col-sm-5">
            <label for="exampleInputEmail">Warehouse</label>
            <div class="input-group ">
              <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
               <select class="form-control" id="warehouse_id" name="warehouse_id">
                    @if(empty($manufacturerId))
                    <option  value="">Select Warehouse</option>
                    @else
                      <option  value="">Select Warehouse</option>
                      @if(!empty($orgs))
                        @foreach($orgs as $key=>$value)
                          <option value="{{$key}}">{{$value}}</option>
                        @endforeach
                      @endif
                    @endif                            
                </select>
            </div>
            </div>

            <div class="form-group col-sm-5">
                    <label for="exampleInputEmail">Locations</label>
                    <div class="input-group ">
                      <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
                      <div id="selectbox">
                       <select class="form-control requiredDropdown" id="location_id" name="location_id" 
                        parsley-trigger="change" parsley-required="true" parsley-error-container="#selectbox">
                            <option  value="">Select Location</option>
                            @if(!empty($locationsArr))
                              @foreach($locationsArr as $value)
                                <option value="{{$value->location_id}}">{{$value->location_name}}</option>
                              @endforeach
                            @endif
                          </select>
                        </div>
                    </div>
            </div>
            
            <div class="form-group col-sm-5">
                    <label for="exampleInputEmail">Product</label>
                    <div class="input-group ">
                      <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
                      <div id="selectbox">
                       <select class="form-control requiredDropdown" id="product_id" name="product_id" 
                        parsley-trigger="change" parsley-required="true" parsley-error-container="#selectbox" >
                            <option  value="">Select Product</option>                            
                          </select>
                        </div>
                    </div>
                    </div>
            </div>
            <div class="row">
<!--             <div class="form-group col-sm-5">
                    <label for="exampleInputEmail">Attribute</label>
                    <div class="input-group ">
                      <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
                      <div id="selectbox">
                       <select class="form-control requiredDropdown" id="attribute_id" name="attribute_id" 
                        parsley-trigger="change" parsley-required="true" parsley-error-container="#selectbox" >
                            <option  value="0">Select Attribute</option>
                          
                          </select>
                        </div>
                    </div>
                    </div>

                <div class="form-group col-sm-5">
                    <label for="exampleInputEmail">Value</label>
                    <div class="input-group ">
                      <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
                      <div id="selectbox">
                       <input type="text" class="" id="searchVal" name="serarchVal" value="" />
                       </div>
                    </div>
                    </div> -->
        
        </div>
        <div class="row">
<!--              <div class="row" style="margin-left:20px;">
             
             <table  id="tbl1" border="0" width="80%">
             <tr>
             <td> -->
                <div class="form-group col-sm-3">
                    <label for="exampleInputEmail">Attribute</label>
                    <div class="input-group ">
                      <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
                      <div id="selectbox">
                       <select class="form-control requiredDropdown" id="attribute_id" name="attribute_id" 
                        parsley-trigger="change" parsley-required="true" parsley-error-container="#selectbox" >
                            <option  value="0">Select Attribute</option>
                          
                          </select>
                        </div>
                    </div>
                    </div>
<!--              </td>
             <td> -->
             <div class="form-group col-sm-2">
             <label for="exampleInputEmail">Opeator</label>
             <div class="input-group ">
                      <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
                      <div id="selectbox">
                <select class="form-control" id="operator" name="operator" 
                        parsley-trigger="change" parsley-required="true" parsley-error-container="#selectbox" >
                            <option  value="0">Select Operator</option>
                            <option value="1" >=</option> 
                            <option value="2" ><></option> 
                            <option value="3" >></option> 
                            <option value="4" ><</option> 
<!--                             <option value="5" >>=</option> 
                            <option value="6" ><=</option>  -->
                          </select>
                </div>
                    </div>
                    </div>
<!--              </td>
             <td> --><div class="form-group col-sm-3">
                    <label for="exampleInputEmail">Value</label>
                    <div class="input-group ">
                      <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
                      <div id="selectbox">
                       <input type="text" class="form-control" id="searchVal" name="serarchVal" value="" />
                       <input type="hidden" class="form-control" id="search" name="search" value="" />
                       </div>
                    </div>
                    </div>
<!--               </td>
              <td> -->
                <!-- <a href="javascript:void(0);" id='anc_rem'><button class="btn btn-primary">Remove</button></a>   -->          
<!--               </td>
              <td> -->
                 <a href="javascript:void(0);" id='anc_add'><button class="btn btn-primary" style="margin-top:32px;">Add</button></a>
              </td>
<!--              </tr>
             
            </table> -->
        </div>
                    <form id="serialize">
                        <div class="row">
                            <section class="tile">
                                <div class="panel panel-default">
                                    <!-- Default panel contents -->
                                    <!-- <div class="panel-heading">Location details</div> -->
                                    <!-- Table -->
                                    <table class="table" id="assign_data">
                                        <thead>
                                            <tr>
<!--                                                 <th>Product Group</th>
                                                <th>Location</th>
                                                <th style="width: 30px;">Action</th> -->
                                            </tr>
                                        </thead>
                                        <tbody id="assigntable">
                                        </tbody>
                                    </table>
                                </div>
                            </section>
                        </div>
                  </form>
        <div class="row">
        <div class="form-group col-sm-5">
          <label for="exampleInputEmail"></label>
          <div class="input-group ">
            <div id="button">
              <button class="btn btn-primary" onclick="binReport();">Filter</button>
            </div>                  
          </div>
        </div>
        </div>
      </div><!-- /.box-body -->
    <div class="col-sm-12">
       <div class="tile-body nopadding">                  
          <div id="jqxgrid"  style="width:100% !important;"></div>
        </div>
    </div>
</div>
@stop
