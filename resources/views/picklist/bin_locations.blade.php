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

function binReport()
{
    var manufacturer_id = $('#manufacturer_id').val();
    var manuf_id = $('#manuf_id').val();
    var warehouse_id = $('#warehouse_id').val();
    var storage_bin_id = $('#storage_bin_id').val();
    var pallet_id = $('#pallet_id').val();
    var product_id = $('#product_id').val();
    if(manufacturer_id=='undefined')
      manufacturer_id=0;
    if(manuf_id=='')
      manuf_id=0;
    if(warehouse_id=='')
      warehouse_id=0;
    if(storage_bin_id=='')
      storage_bin_id=0;
    if(pallet_id=='')
      pallet_id=0;
    if(product_id=='')
      product_id=0;
    /*if(manuf_id)
    {  
      getBinReport(manuf_id,warehouse_id,storage_bin_id,pallet_id,product_id);
    }
    else{*/
      getBinReport(manufacturer_id,warehouse_id,storage_bin_id,pallet_id,product_id);
   // }
}


function getBinReport(manufacturer,warehouse,bin,pallet,product)
{
  var manufacturer = manufacturer || 0,
      warehouse = warehouse || 0,
      bin = bin || 0,
      pallet = pallet || 0,
      product = product || 0;
    
    $.ajax(
              {
                  url: "/getBinLocationsData/"+manufacturer+"/"+warehouse+"/"+bin+"/"+pallet+"/"+product,
                  success: function(result)
                  {
                      var employees = result;
                      // prepare the data
                      var source =
                      {
                          datatype: "json",
                          datafields: [
                          { name: 'storage_bin_id', type: 'string' },
                          { name: 'entity_id', type: 'integer' },
                          { name: 'parent_id', type: 'string' },
                          { name: 'primary_id', type: 'string' },
                          { name: 'pkg_qty', type: 'float' },
                          //{ name: 'actions', type: 'string' },
                          { name: 'children', type: 'array' },
                          { name: 'expanded', type: 'bool' }
                          ],
                          hierarchy:
                          {
                              root: 'children'
                          },
                          id: 'entity_id',
              class: 'configuration_grid',
                          localData: employees
                      };
                      var dataAdapter = new $.jqx.dataAdapter(source);
                      // create Tree Grid
                      $("#treeGrid").jqxTreeGrid(
                      {
                          width: "100%",
                          source: dataAdapter,
                          sortable: true,
                          //autoheight: true,
                          //autowidth: true,
                          columns: [
                     { text: 'Storage Location', datafield: 'storage_bin_id', width:"30%"},
                    { text: 'Pallet ID', datafield: 'parent_id', width:"30%"},
                    { text: 'Product ID', datafield: 'primary_id', width:"20%"},
                    { text: 'Pallet Capacity',  datafield: 'pkg_qty', width: "20%" }
                         ]
                      });


                  }
              });
}   

$(document).ready(function(){ 

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
    var bin_id = $('#warehouse_id').val();
    if(bin_id=='')
      bin_id=0;
    $.ajax({
        url: "/getFilterBins/"+bin_id,
        success: function(result){
          var sel = '';
          $("#storage_bin_id").html(result);
        }
      });
  });

  $("#storage_bin_id").change(function(){
    var str_bin_id = $('#storage_bin_id').val();
    var manuf_id = $('#manufacturer_id').val();
    if(str_bin_id=='')
      str_bin_id=0;
    if(manuf_id=='')
      manuf_id=0;
      $.ajax({
        url: "/getFilterPallets/"+str_bin_id+"/"+manuf_id,
        success: function(result){
          var sel = '';
          $("#pallet_id").html(result);
        }
      });
  });

  $("#pallet_id").change(function(){
    var pallet_id = $('#pallet_id').val();
    var manuf_id = $('#manufacturer_id').val();
    if(pallet_id=='')
      pallet_id=0;
    if(manuf_id=='')
      manuf_id=0;
      $.ajax({
        url: "/getFilterProducts/"+pallet_id+"/"+manuf_id,
        success: function(result){
          var sel = '';
          $("#product_id").html(result);
        }
      });
  });

});
</script>
<style>
.col-md-3 {
    width: 20% !important;
  padding-right:0px !important;
}
.col-sm-1 {
    width: 6.333333% !important;
    padding-left: 0px!important;
    padding-right: 0px!important;
}
</style>


        
       <div class="box collapsed-box">
          <div class="box-header with-border">
            <h3 class="box-title"><strong>Storage-Bin Locations</strong>Report</h3>
            <div class="box-tools1 pull-right">
              <button class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-filter"></i></button>
            </div>
            
                <!-- /.box-tools -->
              </div><!-- /.box-header -->
              <div class="box-body">
   
      <div class="row">                  
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
            <label for="exampleInputEmail">Storage Location</label>
            <div class="input-group ">
              <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
              <div id="selectbox">
               <select class="form-control" id="storage_bin_id" name="storage_bin_id" parsley-trigger="change" parsley-required="true" parsley-error-container="#selectbox">
                  <option value="">Select Storage-Bin</option>
                    <!-- @if(!empty($storage_bin))
                      @foreach($storage_bin as $key=>$value)
                        <option value="{{$value}}">{{$value}}</option>
                      @endforeach
                    @endif -->
                  </select>
                </div>
            </div>
            </div>
            <div class="form-group col-sm-5">
            <label for="exampleInputEmail">Pallet ID</label>
            <div class="input-group ">
              <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
               <select class="form-control" id="pallet_id" name="pallet_id">
                    <option  value="">Select Pallet</option>                            
                </select>
            </div>
        </div>
        <div class="form-group col-sm-5">
            <label for="exampleInputEmail">Product ID</label>
            <div class="input-group ">
              <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
              <select class="form-control" id="product_id" name="product_id">
                <option value="0">Select Product</option>                  
              </select>
            </div>
        </div>
        </div>
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
                <div id="treeGrid"></div>
             </div>
           </div>
        </div>
@stop