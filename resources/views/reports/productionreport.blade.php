
@extends('layouts.default')

@extends('layouts.header')

@extends('layouts.sideview')

@section('content')
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/dt/dt-1.10.13/datatables.min.css"/>
 
<script type="text/javascript" src="https://cdn.datatables.net/v/dt/dt-1.10.13/datatables.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-datetimepicker/2.5.4/build/jquery.datetimepicker.full.min.js" integrity="sha256-W0Tnsbs/T3dgnwYioTOlfjqnj6PvOHO9/NitMcWAD1w=" crossorigin="anonymous"></script>

<link href="http://cdn.rawgit.com/davidstutz/bootstrap-multiselect/master/dist/css/bootstrap-multiselect.css"
    rel="stylesheet" type="text/css" />
<script src="http://cdn.rawgit.com/davidstutz/bootstrap-multiselect/master/dist/js/bootstrap-multiselect.js"
    type="text/javascript"></script>

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
  
        <!-- /.box -->
        
       <div class="box collapsed-box">
          <div class="box-header with-border">
          	<h3 class="box-title"><strong>Production </strong>Report</h3>
            <div class="box-tools1 pull-right">
              <button class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-filter"></i></button>
            </div>


<div class="box-body">
            @if(!empty($customers))
  
  
    <div class="form-group col-sm-5">
      <label for="exampleInputEmail">Choose Customer</label>
      <div id="selectbox">
      
        <select class="form-control requiredDropdown" id="customer_id" name="customer_id"       parsley-trigger="change" parsley-required="true" parsley-error-container="#selectbox" onchange="" >
          <option  value="">Select Customer</option>
            @foreach($customers as $customer)  
              <option value="{{$customer->customer_id}}" >{{$customer->brand_name}}</option>
            @endforeach
        </select>
        
      </div>
    </div>
    
  

  @else
    <input type="hidden" id="customer_id" value="{{$customerId}}">
  
  
  @endif

  
    <div class="form-group col-sm-5">
                <label for="locationtype">Location</label>
                <div class="input-group ">
                    <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
                    <div id="">
                        <select class="list-unstyled selectpicker" data-live-search="true" id="location" name="locationtype" parsley-trigger="change" parsley-required="true" parsley-error-container="#selectbox" >
                            <option  value="0">Select Location</option>
                              @foreach($locations as $key=>$result)
                                  <option  value="{{$key}}">{{$result}}</option>
                              @endforeach
                        </select>
                    </div>
                </div>
    </div>

    <div class="form-group col-sm-5">
                <label for="locationtype">Material Code</label>
                <div class="input-group ">
                    <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
                    <div id="">
                        <select   id="product" name="product[]" multiple ="multiple"  >
                            <!-- <option  value="0">Select Material</option> -->
                              @foreach($product_materials as $key=>$result)
                                  <option  value="{{$key}}">{{$result}}</option>
                              @endforeach
                        </select>
                    </div>
                </div>
    </div>

            <div class="form-group col-sm-5">
                <label for="exampleInputEmail">From</label>
                <div class="input-group ">
                    <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
                    <div id="">
                        <input type="text" name="from_date" id="from_date" class ="bootstrap-datepicker">
                     </div>
                     
                 </div>
            </div>



            <div class="form-group col-sm-5">
                <label for="exampleInputEmail">To</label>
                <div class="input-group ">
                    <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
                    <div id="">
                        <input type="text" name="to_date" id="to_date">
                    </div>
                </div>
            </div>

            <div class="form-group col-sm-2">
                <label for="exampleInputEmail"></label>
                <div class="input-group ">
                    <div id="button">
                        <button class="btn btn-primary" data-toggle="modal"  onclick="makeGrid(0);">Filter</button>
                        <button type="button" class="btn btn-primary" aria-label="Left Align" id="demo">
                            <span class="glyphicon glyphicon-refresh" aria-hidden="true"></span>
                        </button>
                    </div>
                    <input type="hidden" id="module_id" name="module_id" value="">
                    <input type="hidden" id="access_token" name="access_token" value="">
                </div>
            </div>

  </div>
            
                <!-- /.box-tools -->
              </div><!-- /.box-header -->
              <!-- <div class="box-body">
                <div class="form-group col-sm-5">
                    <label for="exampleInputEmail">Location</label>
                    <div class="input-group ">
                      <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
                      <div id="selectbox">
                       <select class="list-unstyled selectpicker" data-live-search="true" id="location_id" name="location_id" 
                        parsley-trigger="change" parsley-required="true" parsley-error-container="#selectbox" >
                            <option  value="">Select Location</option>
                            
                          </select>
                        </div>
                    </div>
                    </div>
                    <div class="col-sm-1" style="margin-top:25px;">
                  <button class="btn btn-primary" data-toggle="modal" data-target="" onclick="makeGrid();" >Filter</button>
                   </div>
                  
    
                  <div class="form-group col-sm-5">
                    <label for="exampleInputEmail">Product</label>
                    <div class="input-group ">
                      <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
                      <div id="selectbox">
                       <select class="list-unstyled selectpicker" data-live-search="true" id="product_id" name="product_id" 
                        parsley-trigger="change" parsley-required="true" parsley-error-container="#selectbox" >
                           <option  value="">Select Product</option>
                           </select>
                        </div>
                    </div>
                  </div>
    
                  <div class="form-group ">
                  <label for="exampleInputEmail"></label>
                  <div class="input-group ">
                  <div id="button">
                  <button class="btn btn-primary" data-toggle="modal"  onclick="makeGrid();">Filter</button>
                  <button type="button" class="btn btn-primary" aria-label="Left Align" id="demo">
                  <span class="glyphicon glyphicon-refresh" aria-hidden="true"></span>
                  </button>
                  </div>
                  <input type="hidden" id="module_id" name="module_id" value="{{$module_id}}">
                  <input type="hidden" id="access_token" name="access_token" value="{{$access_token}}">
                  </div>
                  </div>
              </div> --><!-- /.box-body -->

           <div class="col-sm-12">
             <div class="tile-body nopadding">
             <div class="col-md-offset-6  col-md-6 pull-right form-group" id="export_div">
             <label for="select_export" class="col-md-4 control-label">Export Type:</label>
             <div class="col-md-3" id="select_export">
             <select id="select_export_type">
               <option value="text">Text</option>
               <option value="excel">Excel</option>
             </select>
             </div>
             <div class="col-md-3">
               <input type="button" id="btnExport" value="Export" class="pull-right">                  
             </div>
              
              </div>
                <div id="jqxgrid"></div>
             </div>
           </div>
        </div>


        <div>
        <form name="exportform" id="exportform" method="post" action = "/dashboard/productionreportexcelexport">
          <input type="hidden" id="product_id1" name="product_id" value=''>
          <input type="hidden" name="location" id="location1" value='' >
          <input type="hidden" name="from_date" id="from_date1" value=''>
          <input type="hidden" name="to_date" id="to_date1" value=''>
          <input type="hidden" name="method" value="export" />
          <input type="hidden" name="export_type" id="export_type" value="text">
  </form>

        </div>
        <!-- <div class="jqxgrid" id="jqxgrid"></div> -->

        <!-- <div class="reporttable">

        <table class="table hover" cellspacing="0" width="100%" id="salereport"> 
          <thead>
          <tr>
            <td>Qty</td>
            <td>Material Code</td>
            <td>Material Name</td>
            <td>Sale Location</td>
            <td>Sale Time</td>
            <td>Tp</td>
            <td>Delivery</td>
            <td>Source Location</td>
          </tr>
          </thead>
          <tbody id ="report">
          @foreach($sales as $sale)
          <tr>
            <td>{{$sale->Qty}}</td>
            <td>{{$sale->MaterialCode}}</td>
            <td>{{$sale->MaterialName}}</td>
            <td>{{$sale->SaleLocation}}</td>
            <td>{{$sale->SaleTime}}</td>
            <td>{{$sale->Tp}}</td>
            <td>{{$sale->Delivery}}</td>
            <td>{{$sale->SourceLocation}}</td>
          </tr>

          @endforeach
          </tbody>

        </table>
        </div>
 -->
		 
            

             
              
          
                
            

@stop

@section('style')
    {{HTML::style('jqwidgets/styles/jqx.base.css')}}
     {{HTML::style('css/bootstrap-select.css')}}
     {{HTML::style('js/plugins/timepicker/bootstrap-timepicker.css')}}
     {{HTML::style('css/datepicker.min.css')}}
     {{HTML::style('js/plugins/bootstrap-select/jquery.datetimepicker.css')}}

@stop

@section('script')
{{HTML::script('js/plugins/bootstrap-select/bootstrap-select.js')}}
{{HTML::script('js/plugins/bootstrap-select/bootstrap-datepicker.min.js')}}
<!-- {{HTML::script('js/plugins/bootstrap-select/jquery.datetimepicker.min.js')}} -->
{{HTML::script('js/plugins/timepicker/bootstrap-timepicker.min.js')}}
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
    {{HTML::script('jqwidgets/jqxgrid.export.js')}}
    {{HTML::script('jqwidgets/jqxdata.export.js')}}
    <script type="text/javascript">
    var pre_location = 0;
    var pre_product_id = 0;
    var pre_from_date = 0;
    var pre_to_date = 0;
    $(document).ready(function() { 

      $('#export_div').hide();
 //     $('#salereport').DataTable();
         $('.box-body').show();
        $('#demo').click(function() { 
        //alert('wrking')
        var module_id=document.getElementById('module_id').value;
        var access_token=document.getElementById('access_token').value;
       //var ajaxTime= new Date().getTime();
         
        $.blockUI({ css: { 
            border: 'none', 
            padding: '15px', 
            backgroundColor: '#000', 
            '-webkit-border-radius': '10px', 
            '-moz-border-radius': '10px', 
            opacity: .5, 
            color: '#fff' 
        } }); 
 
        setTimeout($.unblockUI, 15000); 
    }); 

        $('#product').multiselect({
                nonSelectedText :'Select Material Code',
               includeSelectAllOption: true,
                       enableFiltering:true,
                        numberDisplayed: 0,
                 enableCaseInsensitiveFiltering: true,
                 maxHeight: 300
             });
var cust_id = {{$customerId}};
if(cust_id != 0){
  //reportdata(cust_id);
 makeGrid(1);
  //test();
}

        
}); 
    
     
         makeGrid = function (param = 0){

                var cust = $('#customer_id').val();
                cust = typeof cust !=='undefined' ? cust : 0;
                var location =$('#location').val();
                var from_date = $('#from_date').val();
                var to_date = $('#to_date').val();
                var product_id = $('#product').val();
                pre_location = location;
                pre_from_date = from_date;
                pre_to_date = to_date;
                pre_product_id = product_id;

                var url = '/dashboard/productionreportData';
                // alert(url);
                if(param !=1){
                  if(cust == 0 || cust =="" || location ==0 || location =="" || from_date == 0 || from_date =="" || to_date ==0 || to_date =="" ){
                    alert("Location, From Date and To Date fields are mandatory");
                    return false;
                  }  
                }
                
                var source =
                    {
                        datatype: "json",
                        datafields: [
                            { name: 'location_name', type: 'string' },
                            { name: 'po_number', type: 'string' },
                            { name: 'material_code', type: 'string' },
                            { name: 'description', type: 'string' },
                            { name: 'batch_no', type: 'string' },
                            { name: 'primary_id', type: 'string' },
                            { name: 'parent_id', type: 'string' },
                            { name: 'sync_date', type: 'string' },
                            { name: 'sync_time', type: 'string' },
                            { name: 'GR', type: 'string' }
                        ],
                        id: 'id',
                        url: url,
                        type:'POST',
                        data:{
                          customer_id:cust,
                          location:location,
                          from_date:from_date,
                          to_date:to_date,
                          product_id:product_id,
                          param:param,
                          method:'grid'

                        },
                        pager: function (pagenum, pagesize, oldpagenum) {
                            // callback called when a page or page size is changed.
                        }
                    };
                var dataAdapter = new $.jqx.dataAdapter(source);
                createGrid(dataAdapter);
                if(param ==0){
                  $('#export_div').show();  
                }
                
            }

            function createGrid(source){
                $("#jqxgrid").jqxGrid(
                    {
                        width: "100%",
                        source: source,
                        selectionmode: 'multiplerowsextended',
                        sortable: true,
                        pageable: true,
                        autoheight: true,
                        autoloadstate: false,
                        autosavestate: false,
                        columnsresize: true,
                        columnsreorder: true,
                        showfilterrow: true,
                        filterable: true,
                        columns: [
                                { text: 'Location', datafield: 'location_name'},
                            { text: 'Po Number', datafield: 'po_number' },
                            { text: 'Material Code', datafield: 'material_code'},
                            { text: 'Product Description', datafield: 'description'},
                            { text: 'Batch No', datafield: 'batch_no'},
                            { text: 'Primary Id', datafield: 'primary_id'},
                            { text: 'Parent Id', datafield: 'parent_id'},
                            { text: 'Sync Date', datafield: 'sync_date'},
                            { text: 'Sync Time', datafield: 'sync_time'},
                            { text: 'GR', datafield: 'GR'}
                        ]
                    });
            }

  //          makeGrid(0);
$(function() {
     
     //$('#from_date').datepicker({ dateFormat: 'yy-mm-dd'}); 
     //$('#to_date').datepicker({ dateFormat: 'yy-mm-dd'}); 
     $(function () {
            $('#from_date').datetimepicker({
              timeFormat: 'HH:mm:ss',
              dateFormat:'yyyy-mm-dd'
            });
        });

     
     
     $(function () {
            $('#to_date').datetimepicker({
              timeFormat: 'HH:mm:ss',
              dateFormat:'yyyy-mm-dd'
            });
        });
  
});

$('#customer_id').on('change',function(){
  var cust = $(this).val();
  if(cust == 0){
    $('#locationtype').empty();
    var opt = new Option('Select Location type',0);
    $('#locationtype').append(opt);
    $('#locationtype').selectpicker('refresh');
  }
  else{
    $.ajax({
      url:"getlocationtypesbycustomerid",
      data:'customer_id='+cust,
      method:'get',
      success:function(response){
        $('#locationtype').empty();

        //select.empty();
        var opt = new Option('Select Location type',0);
        $('#locationtype').append(opt);
        $.each(response,function(i,val){
          opt =new Option(val,i);
          $('#locationtype').append(opt);
        });
        $('#locationtype').selectpicker('refresh');
      }
    });
  }


});


$("#btnExport").click(function () {


   // var url = "/dashboard/productionreportexcelexport?location="+pre_location+"&product_id="+pre_product_id+"&from_date="+pre_from_date+"&to_date="+pre_to_date+"&param=0";
             //window.location = url;

             //var url = "/dashboard/productionreportexcelexport";
             //alert(pre_location);
             //alert(pre_product_id);
             $('#location1').val(pre_location);
             $('#from_date1').val(pre_from_date);
             $('#to_date1').val(pre_to_date);
             $('#product_id1').val(pre_product_id);
             $('#export_type').val($('#select_export_type').val());
             //alert($('#location').val());
var form = $('#exportform');
//$('body').append(form);
form.submit();
    // $.ajax({
    //     url:"physicalinventoryexport",
    //     data:{
    //         location_type:pre_location_type,
    //         location:pre_location,
    //         invalids:pre_invalid,
    //         product:pre_product,
    //         from_date:pre_from_date,
    //         to_date:pre_to_date
    //     },
    //     method:'post',
    //     success:function(response){

    //     }
    // });
    
});

// $("#btnExport").click(function () {


// $("#jqxgrid").jqxGrid('exportdata', 'xls', 'jqxGrid');
    
// });


    </script>    
@stop