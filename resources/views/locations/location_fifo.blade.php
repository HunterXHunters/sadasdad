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
</style>
<!-- Page content -->
<?php if (isset($error_message)){ ?>
<div><span><?php echo $formData['error_message']; ?></span></div>
<?php } ?>
    <form id="igGenForm">
        <div class="box">

        <div class="box-header">
          <h3 class="box-title"><i class="fa fa-th"></i><strong> Product-Sloc </strong> Config </h3>
        </div>

          <div class="addForm">
            <div class="col-md-3">
            <div class="form-group">
                <label>Location Type</label>
                      <select class="form-control getLocations  selectpicker" data-live-search="true" id="locationtypes" name="locationtypes">
                       <option value="">Please Select Location Type</option>
                        @foreach($locationTypes as $lt)
                          <option value="{{$lt->location_type_id}}">{{$lt->location_type_name}}</option>
                        @endforeach
                      </select>
              </div>
          </div>

          <div class="col-md-3">
            <div class="form-group">
                <label>Location</label>
                      <select class="form-control vendorSelect.  selectpicker" data-live-search="true" id="vendor" name="vendor" >
                       <option value="">Please Select Location</option>
                        @foreach($vendors as $vendor)
                          <option value="{{$vendor->location_id}}"> {{$vendor->username}}-{{$vendor->erp_code}}</option>
                        @endforeach
                      </select>
              </div>
          </div>
          

         <!--  <div class="col-md-3">
            <div class="form-group">
                <label>Product</label>
                      <select class="form-control productSelect  selectpicker" data-live-search="true" id="product" name="product" >
                       <option value="">Please Select Product</option>
                      </select>
              </div>
          </div> -->

         

           <div class="col-md-6"> <button type="button" class="btn btn-primary addBtn" >Search</button>  <button type="button" data-toggle="modal" data-target="#configModel" class="btn btn-primary pull-right configBtn" >Config</button> </div>
      </div>
    </div>
  <div id="jqxgrid"></div>   
</div>
</form>
<div id="configModel" class="modal fade" role="dialog">
  <div class="modal-dialog">
    <form id="updateConfig" action="updateFifoConfig" method="post">
    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
           <h4 class="modal-title">Fifo Config</h4>
      </div>
      <div class="modal-body">
        <div class="modelPopUp">
             <div class="col-md-6">
            <div class="form-group">
                <label>Location Type</label>
                      <select class="form-control getLocations1  selectpicker" data-live-search="true" id="locationtypes" name="locationtypes">
                       <option value="">Please Select Location Type</option>
                        @foreach($locationTypes as $lt)
                          <option value="{{$lt->location_type_id}}">{{$lt->location_type_name}}</option>
                        @endforeach
                      </select>
              </div>
          </div>

          <div class="col-md-6">
           
            <label>Select Locations</label>
            <div class=" form-group">
            <select class="form-control locationSelect1  selectpicker" data-live-search="true" id="locationSelect1" name="vendor" >
             <option value="">Please Select Locations</option>
              @foreach($vendors as $vendor)
                <option value="{{$vendor->location_id}}"> {{$vendor->username}}-{{$vendor->erp_code}}</option>
              @endforeach
            </select>
          </div>
          </div>

          <div class="col-md-6">
             
            <label>Product</label>
            <div class=" form-group">
            <select class="form-control   " multiple="multiple"  data-live-search="true" id="product_loc" name="product_loc[]" >
             <option value="">Please Select Product</option>
            </select>
          </div>
          </div>

          <div class="cleasrfix"></div>
          <div class="col-md-5 pull-left">
            <label>Storage Location</label>
            <div class=" form-group">
            <select class="form-control   " multiple="multiple"  data-live-search="true" id="storage_loc" name="storage_loc[]" >
             <option value="">Please Select sloc</option>
            </select>
          </div>
          </div>

           <div class="col-md-1 pull-right"><button type="submit" class="btn btn-primary pull-right marginTop32" >Submit</button></div>
        
        </div>
      </div>
      <!-- <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div> -->
    </div>
  </form>
  </div>
</div>

       

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
   $(function() {
    $('.changeMandatory').bootstrapToggle({ on: 'Error',
      off: 'Warning',width:'100px',height:'20px'});
  })

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
      
  $('body').on('change','.changeMandatory',function(){
    var data=$(this).data();
    var checked=0;
    if($(this).is(':checked')){
      checked=1;
    } else {
      checked=0;
    }


    var formData = new FormData();
    formData.append("plid", data.plid);
    formData.append("checked", checked);
    $.ajax({
    url: 'fifoMandotoryUpodate',
    type: 'POST',
    data: formData,
    async: false,
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
     $('.addBtn').click();     
    },
    cache: false,
    contentType: false,
    processData: false
    });

  });

 $('body').on('focus',".datepicker", function(){
   $(this).datepicker({format:'dd-mm-yyyy',todayHighlight:true,autoclose: true});
 });
var source =
    {
        localdata: [],
        datafields: [{
                        name: 'product_id',
                        type: 'string'
                    }, {
                        name: 'description',
                        type: 'string'
                    },  {
                        name: 'material_code',
                        type: 'string'
                    }, {
                        name: 'fifo',
                        type: 'string'
                    }, {
                        name: 'mandatory',
                        type: 'string'
                    }, {
                        name: 'sloc',
                        type: 'string'
                    }],
        datatype: "json"
    };
var dataAdapter = new $.jqx.dataAdapter(source);
var columns = [{
                    text:'Product',
                    datafield: 'description',
                    width: "30%"
                },{
                text: 'Material Code',
                    datafield: 'material_code',
                    cellsalign: 'left',
                    width: "20%"
                }, {
                    text:'Fifo Status',
                    datafield: 'fifo',
                      cellsalign: 'left',
                    width: "10%"
                }, {
                    text:'Fifo Mandatory',
                    datafield: 'mandatory',
                      cellsalign: 'center',
                    width: "15%"
                }, {
                    text:'Storage Locations',
                    datafield: 'sloc',
                      cellsalign: 'center',
                    width: "25%"
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

$('.addBtn').click(function(){
var url = "getFifoProducts";
    var formData = new FormData($('#igGenForm')[0]);
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
    $('.changeMandatory').bootstrapToggle({ on: 'Error',
      off: 'Warning',width:'100px',height:'20px'});
});

$('.getLocations').change(function (){
    if($(this).val()!=''){
      $('#vendor')
      .empty()
      .append('<option selected="selected" value="">Select One</option>')
      ;

    $("#loading").show();
     $.ajax({
        url: '/locations/getLocationsByLocationsType/'+$(this).val(),
        type: 'GET',
        async: false,
        dataType: "json",
        success: function(data) {
          $.each(data, function(key, value) { 
          $('#vendor')
          .append($("<option></option>")
          .attr("value",value.location_id)
          .text(value.username+"-"+value.erp_code)); 
          });         
          $("#loading").hide();
        },
        cache: false,
        contentType: false,
        processData: false
      });
    } else {
      $('#vendor')
      .empty()
      .append('<option selected="selected" value="">Select One</option>');
      $('#vendor').val('');
    }  
    $('#vendor').selectpicker('refresh');        
  });

$('.getLocations1').change(function (){
    if($(this).val()!=''){
      $('#locationSelect1')
      .empty()
      .append('<option selected="selected" value="">Select One</option>')
      ;

    $("#loading").show();
     $.ajax({
        url: '/locations/getLocationsByLocationsType/'+$(this).val(),
        type: 'GET',
        async: false,
        dataType: "json",
        success: function(data) {
          $.each(data, function(key, value) { 
          $('#locationSelect1')
          .append($("<option></option>")
          .attr("value",value.location_id)
          .text(value.username+"-"+value.erp_code)); 
          });         
          $("#loading").hide();
        },
        cache: false,
        contentType: false,
        processData: false
      });
    } else {
      $('#locationSelect1')
      .empty()
      .append('<option selected="selected" value="">Select One</option>');
      $('#locationSelect1').val('');
    }  
    $('#locationSelect1').selectpicker('refresh');        
  });

$('.locationSelect1').change(function (){
  const selectProducts = $("#product_loc");
  const selectSloc = $("#storage_loc");

     selectProducts.multiselect('destroy');     
     selectSloc.multiselect('destroy');

   if($(this).val()!=''){
      selectProducts.empty();
      selectSloc.empty();
      //selectProducts.empty().append('<option  value="">Select Product </option>');
      //selectSloc.empty().append('<option  value="">Select Storage Location</option>');
      product_qty=[];
      productInfo=[];
      var formData = new FormData();
      formData.append("vendor", $(this).val());
    $("#loading").show();
     $.ajax({
        url: '/locationtypes/getProductsNstorageLocByUser',
        type: 'POST',
        data: formData,
        async: false,
        success: function(data) {
           //alert("kkkk======");
          $.each(data.products, function(key, value) { 
         /* var appnd=$("<option></option>")
          .attr("value",value.product_id)
          .text(value.name+"-"+value.material_code);*/
          
          selectProducts.append('<option value="'+value.product_id+'" '+(value.fifo==1?' selected ':'')+' >'+value.name+"-"+value.material_code+'</option>'); 

          });         
         
         $.each(data.locations, function(key, value) { 
          /*selectSloc.append($("<option></option>")
          .attr("value",value.location_id)
          .text(value.location_name+"-"+value.erp_code)); */
          selectSloc.append('<option value="'+value.location_id+'" '+(value.location_id==value.storage_loaction_id?' selected ':'')+' >'+value.location_name+"-"+value.erp_code+'</option>'); 
          }); 

          $("#loading").hide();

        },
        cache: false,
        contentType: false,
        processData: false
      });
    } else {
      selectProducts.empty()
      .append('<option selected="selected" value="">Select Product</option>');
      selectSloc.empty()
      .append('<option selected="selected" value="">Select Storage Location</option>');
    } 

                   
  selectProducts.multiselect({
    nonSelectedText :'Select Products',
    includeSelectAllOption: true,
    enableFiltering:true,
    numberDisplayed: 2,
    enableCaseInsensitiveFiltering: true,
    maxHeight: 300,
    buttonWidth: '100%'
  });

  selectSloc.multiselect({
    nonSelectedText :'Select Storage Location',
    includeSelectAllOption: true,
    enableFiltering:true,
    numberDisplayed: 2,
    enableCaseInsensitiveFiltering: true,
    maxHeight: 300,
    buttonWidth: '100%'
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