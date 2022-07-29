@extends('layouts.default')
@extends('layouts.header')
@extends('layouts.sideview')

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.4.1/css/bootstrap-datepicker3.css"/>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.12.1/css/bootstrap-select.css" />

<link href="http://cdn.rawgit.com/davidstutz/bootstrap-multiselect/master/dist/css/bootstrap-multiselect.css"
    rel="stylesheet" type="text/css" />
    <link href="https://gitcdn.github.io/bootstrap-toggle/2.2.2/css/bootstrap-toggle.min.css" rel="stylesheet">

<div align="center" id="loading" style="text-align:center; z-index:9999;position:absolute;background:rgba(0,0,0,0.3);height:709px; width:1261px; display:none;" ><img src="/img/loading.gif" > </div>
@section('content') 
<style>
    .btn {
        margin-left: 10px !important;
    } 
#dvLoading
{
   background:#000 url(public/img1/ajax-loader.gif) no-repeat center center;
   height: 100px;
   width: 100px;
   position: fixed;
   z-index: 1000;
   left: 50%;
   top: 50%;
   margin: -25px 0 0 -25px;
}
.addBtn,.configBtn{
      margin-top: 32px
}
.dropdown-toggle{
  margin-left: 0px !important;
}
.modelPopUp{
  min-height: 150px;
}
.multiselect-container {
  width: 100% !important;
}
.btn .caret {
    margin-left: 0;
    text-align: right;
    float: right;
}
.multiselect.dropdown-toggle {
    text-align: left;
}
.marginTop32{
  margin-top: 32px;
}
.toggle.btn {
    min-width: 40px; 
    min-height: 18px;
    width: 40px;
    height: 20px;
    border-radius: 8px;
}
.location td {
    background: #01537b;
    color: #fff;
    text-align: center;
    font-weight: 600;
    font-size: 16px;
}
</style>
<!-- Page content -->
<?php if (isset($error_message)){ ?>
<div><span><?php echo $formData['error_message']; ?></span></div>
<?php } ?>



 {{ Form::open(array('url' => '/product/fifoviolations_report', 'id' => 'getproduct_loactionmapping', 'files'=>'true' )) }}
                   
 @if(!empty($customers))
  
    <div class="form-group col-sm-5">
      <label for="exampleInputEmail">Choose Customer</label>
      <div id="selectbox">
      <!-- {{ Form::open(array('url' => '/dashboard/iotBankReport','method'=>'POST','id'=>'IOTBFrm','role'=>"form",'name'=>'IOTBFrm')) }}
       -->  
       <select class="form-control requiredDropdown multiSelect" id="customer_id" name="customer_id"       parsley-trigger="change" parsley-required="true" parsley-error-container="#selectbox" onchange="getproductsLocationTypesProductTypesCategories()" >
          <option  value="">Select Customer</option>
            @foreach($customers as $customer)  
              <option value="{{$customer->customer_id}}" >{{$customer->brand_name}}</option>
            @endforeach
        </select>
        <!-- {{Form::close()}} -->
      </div>
    </div>
    <div class="clr"></div>
    @else
    <input type = "hidden" name ="customer_id" id ="customer_id" value= "{{$cust_id}}">
    @endif

                <div class="form-group col-sm-12">
                    <style>
                        .modal-content{
                            width: 75% !important;
                            margin: auto !important;
                        }
                        .btn {
                            margin-left: 0px !important;
                        }
                        .btn-group{
                            width: 550px !important;
                        }
                        .btn-group .multiselect{
                            width: 550px !important;
                        } 
                    </style>
            <!--     <label for="exampleInputEmail"><b>Vendor</b></label> -->
                <div class="col-md-4">
                <div class="input-group ">
                    <div id="selectbox">
                         <select class=" form-control getLayouts" id="ser_location_id" name="location_id" >
                           <option  value="">select One Location</option>
                            @foreach($locations as $key=>$result)
                                <option  value="{{$result->location_id}}">{{$result->location_name}}-{{$result->erp_code}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
              </div>
                <div class="col-md-4"> 
                  <button type="submit" class="gridrefresh btn btn-primary" >Search</button>
                </div>
                </div>
                
               <!--  <div class="col-sm-3 pull-right">
                    <br>
                   
                    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#bulk_update">Add Pdf Layout</button>
                </div>
 -->
                <div class="clearfix"></div>

                    {{ Form::close() }}
 <div id="jqxgrid"></div>

       

@stop
@section('style') 
{{HTML::style('jqwidgets/styles/jqx.base.css')}}
{{HTML::style('css/bootstrap-select.css')}}
@stop 

@section('script') 
{{HTML::script('js/plugins/bootstrap-select/bootstrap-select.js')}}
{{HTML::script('jqwidgets/jqxcore.js')}} {{HTML::script('jqwidgets/jqxbuttons.js')}} {{HTML::script('jqwidgets/jqxscrollbar.js')}} {{HTML::script('jqwidgets/jqxmenu.js')}} {{HTML::script('jqwidgets/custom_jqxgrid.js')}} {{HTML::script('jqwidgets/jqxgrid.selection.js')}} {{HTML::script('jqwidgets/jqxgrid.columnsresize.js')}} {{HTML::script('jqwidgets/jqxdata.js')}} {{HTML::script('scripts/demos.js')}} {{HTML::script('jqwidgets/jqxlistbox.js')}} {{HTML::script('jqwidgets/jqxdropdownlist.js')}} {{HTML::script('jqwidgets/jqxgrid.pager.js')}} {{HTML::script('jqwidgets/jqxgrid.sort.js')}} {{HTML::script('jqwidgets/jqxgrid.filter.js')}} {{HTML::script('jqwidgets/jqxgrid.storage.js')}} {{HTML::script('jqwidgets/jqxgrid.columnsreorder.js')}} {{HTML::script('jqwidgets/jqxpanel.js')}} {{HTML::script('jqwidgets/jqxcheckbox.js')}}

<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.4.1/js/bootstrap-datepicker.min.js"></script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.12.1/js/bootstrap-select.js"></script>
<script src="http://cdn.rawgit.com/davidstutz/bootstrap-multiselect/master/dist/js/bootstrap-multiselect.js" type="text/javascript"></script>

<script src="https://gitcdn.github.io/bootstrap-toggle/2.2.2/js/bootstrap-toggle.min.js"></script>

<script type="text/javascript">
$(document).ready(function() {


$('#getproduct_loactionmapping').submit(function(event) {
 
    event.preventDefault();
  //    $form = $(this);
  
    var formData = new FormData($(this)[0]);
    $.ajax({
    url: '/product/fifoviolations_report',
    type: 'POST',
    data: formData,
    async: false,
    success: function(data) {
      console.log(data);
        source.localdata = data;
        $("#jqxgrid").jqxGrid('updatebounddata', 'cells');
    },
    cache: false,
    contentType: false,
    processData: false
    });
});
   

   $('#product_loc,#storage_loc').multiselect({
        //columns: 1,
        nonSelectedText :'Select One',
        includeSelectAllOption: true,
        enableFiltering:true,
        numberDisplayed: 1,
        enableCaseInsensitiveFiltering: true,
        maxHeight: 300,
        buttonWidth: '100%'
    }); 
      
 $('body').on('focus',".datepicker", function(){
   $(this).datepicker({format:'dd-mm-yyyy',todayHighlight:true,autoclose: true});
 });

var source = 
    {
        localdata: '<?=json_encode($fifoviolations)?>',
        datafields: [{
                        name: 'primary_id',
                        type: 'string'
                    }, {
                        name: 'material_code',
                        type: 'string'
                    },   {
                        name: 'prod_batch',
                        type: 'string'
                    }, {
                        name: 'old_batch',
                        type: 'string'
                    }, {
                        name: 'location_id',
                        type: 'string'
                    }, {
                        name: 'erp_code',
                        type: 'string'
                    },{
                        name: 'name',
                        type: 'string'
                    },{
                        name: 'cnt',
                        type: 'string'
                    }],
        datatype: "json"
    };
var dataAdapter = new $.jqx.dataAdapter(source);
var columns = [{
                    text:'eSealID',
                    datafield: 'primary_id',
                    width: "20%"
                },{
                text: 'Material Code',
                    datafield: 'material_code',
                    cellsalign: 'left',
                    width: "15%"
                }, {
                    text:'Product Name',
                    datafield: 'name',
                      cellsalign: 'left',
                    width: "20%"
                }, {
                    text:'Prod Batch',
                    datafield: 'prod_batch',
                      cellsalign: 'center',
                    width: "15%"
                }, {
                    text:'Old Batch',
                    datafield: 'old_batch',
                      cellsalign: 'center',
                    width: "15%"
                }, {
                    text:'Location Erp',
                    datafield: 'erp_code',
                      cellsalign: 'center',
                    width: "8%"
                },{
                    text:'errors',
                    datafield: 'cnt',
                      cellsalign: 'center',
                    width: "7%"
                }];

$("#jqxgrid").jqxGrid(
{
    width: "100%",
    source: dataAdapter,
     rowsheight: 30,
    columns: columns,
    selectionmode: 'multiplerowsextended',
    sortable: true,
    pageable: true,
    autoheight: true,
    //autowidth: true,
    autoloadstate: false,
    autosavestate: false,
    columnsresize: true,
    columnsreorder: true,
    showfilterrow: true,
    filterable: true,
});

/*$('.addBtn').click(function(){
var url = "/product/ageing";
    var formData = new FormData($('#igGenForm')[0]);
    formData.append("search",1);
    $.ajax({
    url: url,
    type: 'POST',
    data: formData,
    async: false,
    success: function(data) {
      source.localdata = data;
      $("#jqxgrid").jqxGrid('updatebounddata', 'cells');
    },
    cache: false,
    contentType: false,
    processData: false
    });
 
});
*/

$('#product_id').change(function (){
$("#loading").show();
var url = "/product/ageing";
    var formData = new FormData();
     formData.append("ser_product_id", $(this).val());
      formData.append("search",1);
    $.ajax({
    url: url,
    type: 'POST',
    data: formData,
    async: false,
    dataType: "json",
    success: function(data) {
      console.log("data");
      console.log(data);
      $('#block_period').val(data.block_period);
      $("#loading").hide();
    },
    cache: false,
    contentType: false,
    processData: false
    });
});

$('#updateConfig').submit(function(event) {
  event.preventDefault();
 
  $("#loading").show();
  $form = $(this);
  url = $form.attr('action');
  var formData = new FormData($(this)[0]);
   $.ajax({
    url: url,
    type: 'POST',
    data: formData,
    dataType: "json",
    success: function(data) {
      console.log(data);
      if (data.status) {
        $("#loading").hide();
        alert(data.message);
      } else {
       // $('#bulkupdateerrorlog').hide();
        alert(data.message);
        $("#loading").hide();
      }
      $('#configModel').modal('toggle'); 
     // $(this)[0].reset();
      $('.locationSelect1').val('').change();
      $('.addBtn').click();
    },
    cache: false,
    contentType: false,
    processData: false
    });  

});



});   //document close
</script>
@stop