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
.productSelectResponce p,
.productSelectResponce_price p,
.productSelectResponce_applicable p,
.productSelectResponce_remarks p,
.productSelectResponce_log p{
  color :red;
}
</style>
<!-- Page content -->
<?php if (isset($error_message)){ ?>
<div><span><?php echo $formData['error_message']; ?></span></div>
<?php } ?>
    <form id="igGenForm">
        <div class="box">

        <div class="box-header">
          <h3 class="box-title"><i class="fa fa-th"></i><strong> Product </strong> Price Config </h3>
        </div>

          <div class="addForm">
          <div class="col-md-3">
            <div class="form-group">
                <label>Select Product</label>
                      <select class="form-control  selectpicker" data-live-search="true" id="ser_product" name="ser_product" >
                       <option value="">Please Select Product</option>
                          @foreach($productsList as $product)
                          <option value="{{$product->product_id}}"> {{$product->name}}-{{$product->material_code}}</option>
                          @endforeach
                      </select>
              </div>
          </div>
          


         

           <div class="col-md-6"> <button type="button" class="btn btn-primary addBtn" >Search</button>  <button type="button" data-toggle="modal" data-target="#configModel" class="btn btn-primary pull-right configBtn" >Config</button>
              <button type="button" data-toggle="modal" data-target="#importModel" class="btn btn-primary pull-right configBtn" >Import Sheet</button>
            </div>
      </div>
    </div>
  <div id="jqxgrid"></div>   
</div>
</form>


<div id="priceHistory" class="modal fade" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
           <h4 class="modal-title">Material:<span class="pop_matName"></span>-<span class="pop_matCode"></span></h4>
      </div>
      <div class="modal-body">
        <div class="modelPopUp">
       <div id="logTable"></div>  
       <h3 class="align-center text-center" style="color:skyblue">Current Price</h3>
          <table class="table table-hover table-stripped table-bordered curr" >
            <tr>
              <td class="cur_price"></td>
              <td class="cur_apl_from"></td>
              <td class="cur_updated_by"></td>
              <td class="cur_updated_on"></td>
              <td class="cur_remarks"></td>
            </tr>
          </table>
        </div>
    </div>
  </div>
</div>
</div>


<div id="configModel" class="modal fade" role="dialog" >
  <div class="modal-dialog">
    <form id="updateConfig" action="/product/mrp_add" method="post">
    <!-- Modal content-->
    <div class="modal-content"  style="height: 300px;">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
           <h4 class="modal-title">MRP Config</h4>
      </div>
      <div class="modal-body">
        <div class="modelPopUp">
          <div class="col-md-6">
           
            <label>Select Product</label>
            <div class=" form-group">
            <select required="required" class="form-control locationSelect selectpicker productSelect" data-live-search="true" id="product" name="product" >
             <option value="">Please Select Product</option>
              @foreach($productsList as $product)
                <option value="{{$product->product_id}}"> {{$product->name}}-{{$product->material_code}}</option>
              @endforeach
            </select>
            <div class="productSelectResponce"><p></p></div>
          </div>
        
        </div>

         <div class="col-md-3">
           
            <label>Price</label>
            <div class=" form-group">
            <input required="required" type="text" autocomplete="off" name="price" id="price" class="form-control cost"  placeholder="0.00">
            <div class="productSelectResponce_price"><p></p></div>
          </div>
        
        </div>

        <div class="col-md-3">
           
            <label>Applicable From</label>
            <div class=" form-group">
            <input required="required" type="text" autocomplete="off" name="applicable_from" id="applicable_from" class="form-control datepicker">
            <div class="productSelectResponce_applicable"><p></p></div>
          </div>
        
        </div>


        <div class="col-md-10">
           
            <label>Remarks</label>
            <div class=" form-group"><textarea autocomplete="off" required="required" name="remarks" id="remarks" class="form-control"></textarea>
            <div class="productSelectResponce_remarks"><p></p></div>
          </div>
        
        </div>

        

           <div class="col-md-2 pull-right"><button type="submit" class="btn btn-primary pull-right marginTop32" >Submit</button>
            <div class="productSelectResponce_log"><p></p></div>
           </div>


      </div>
      <!-- <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div> -->
    </div>
  </form>
  </div>
</div>
</form>
</div> 
</div>


<div id="importModel" class="modal fade" role="dialog">
  <div class="modal-dialog">
    <form id="updateConfigFile" action="/product/mrp_add" method="post" enctype='multipart/form-data'>

    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
           <h4 class="modal-title">MRP Config</h4>
      </div>
      <div class="modal-body">
        <div class="modelPopUp">
          
   <div class="col-md-4 pull-left">
             <a href="/download/mrpconfig.xlsx"> Sample file Download</a>
           </div>
          

         
           <div class="col-md-6">
           
            <label>Select File</label>
            <div class=" form-group">
            <input type="file" name="import" id="import" accept="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet">
          </div>
        
        </div>
        

           <div class="col-md-2 pull-right"><button type="submit" class="btn btn-primary pull-right marginTop32" >Submit</button></div>
       


      </div>
      <!-- <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div> -->
    </div>
  </form>
  </div>
</div>

       </form></div>
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


var logsource =
    {
        localdata: [],
        datafields: [{
                        name: 'applicable_from',
                        type: 'string'
                    }, {
                        name: 'id',
                        type: 'string'
                    }, {
                        name: 'price',
                        type: 'string'
                    }, {
                        name: 'product_id',
                        type: 'string'
                    }, {
                        name: 'remarks',
                        type: 'string'
                    }, {
                        name: 'timestamp',
                        type: 'string'
                    }, {
                        name: 'user_id',
                        type: 'string'
                    }, {
                        name: 'username',
                        type: 'string'
                    }],
        datatype: "json"
    };
var logdataAdapter = new $.jqx.dataAdapter(logsource);
var logColumns = [ {
                    text:'Price',
                    datafield: 'price',
                      cellsalign: 'left',
                    width: "15%"
                }, {
                    text:'Applicable From',
                    datafield: 'applicable_from',
                      cellsalign: 'center',
                    width: "20%"
                }, {
                    text:'Updated By',
                    datafield: 'username',
                      cellsalign: 'center',
                    width: "20%"
                }, {
                    text:'Updated On',
                    datafield: 'timestamp',
                      cellsalign: 'center',
                    width: "20%"
                }, {
                    text:'Remarks',
                    datafield: 'remarks',
                      cellsalign: 'center',
                    width: "25%"
                }];
$("#logTable").jqxGrid(
{
    width: "100%",
    source: logdataAdapter,
     rowsheight: 30,
    columns: logColumns,
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


  function openHestory(pid){
    var formData = new FormData();
    formData.append("product_id",pid);
    formData.append("product_log",pid);
    $.ajax({
    url: '/product/getPrice',
    type: 'POST',
    data: formData,
    async: false,
    dataType: "json",
    success: function(data) {
     // alert("responce CAme");
      console.log(data);
      $('#priceHistory').modal('show'); 
       logsource.localdata = data.mrp_list;
       console.log(data.mrp_list);
      $("#logTable").jqxGrid('updatebounddata', 'cells');
       //  var mrp=data.mrp; 
      console.log("mrp.length",Object.keys(data.mrp).length);
      if(Object.keys(data.mrp).length){
      $('.curr').show();
      $('.pop_matCode').html(data.mrp.material_code);
      $('.pop_matName').html(data.mrp.name);
      $('.cur_price').html(data.mrp.price);
      $('.cur_apl_from').html(data.mrp.applicable_from);
      $('.cur_updated_by').html(data.mrp.username);
      $('.cur_updated_on').html(data.mrp.timestamp);
      $('.cur_remarks').html(data.mrp.remarks);
    } else{
      $('.curr').hide();
    }
    },
    cache: false,
    contentType: false,
    processData: false
    });
  }
$(document).ready(function() {
   $(function() {
    $('.changeMandatory').bootstrapToggle({ on: 'Error',
      off: 'Warning',width:'100px',height:'20px'});
  });

$(".cost").on("keypress keyup blur",function (event) {
            //this.value = this.value.replace(/[^0-9\.]/g,'');
$(this).val($(this).val().replace(/[^0-9\.]/g,''));
  if ((event.which != 46 || $(this).val().indexOf('.') != -1) && (event.which < 48 || event.which > 57)) {
  event.preventDefault();
  }
    //this.value = parseFloat(this.value).toFixed(2);
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
    
  $('.productSelect').change(function(){
    var formData = new FormData();
    formData.append("product_id",$(this).val());
    $.ajax({
    url: '/product/getPrice',
    type: 'POST',
    data: formData,
    async: false,
    dataType: "json",
    success: function(data) {
    //  alert("responce CAme");
      console.log(data);
      var mrp=data.mrp; 
      console.log("mrp.length",Object.keys(mrp).length);
      if(Object.keys(mrp).length){
      $('.productSelectResponce_price p').html(mrp.price);
      $('.productSelectResponce_applicable p').html(mrp.applicable_from);
      $('.productSelectResponce_remarks p').html(mrp.remarks);
      $('.productSelectResponce p').html('Updated by: '+mrp.username+' on '+mrp.timestamp);
      } else {
      $('.productSelectResponce p').html('');
      }
    },
    cache: false,
    contentType: false,
    processData: false
    });
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
        localdata: <?=json_encode($products)?>,
        datafields: [{
                        name: 'name',
                        type: 'string'
                    }, {
                        name: 'material_code',
                        type: 'string'
                    },  {
                        name: 'price',
                        type: 'string'
                    }, {
                        name: 'applicable_from',
                        type: 'string'
                    }, {
                        name: 'actions',
                        type: 'string'
                    }],
        datatype: "json"
    };
var dataAdapter = new $.jqx.dataAdapter(source);
var columns = [{
                    text:'Product',
                    datafield: 'name',
                    width: "30%"
                },{
                text: 'Material Code',
                    datafield: 'material_code',
                    cellsalign: 'left',
                    width: "20%"
                }, {
                    text:'Price',
                    datafield: 'price',
                      cellsalign: 'left',
                    width: "20%"
                }, {
                    text:'Applicable From',
                    datafield: 'applicable_from',
                      cellsalign: 'center',
                    width: "20%"
                }, {
                    text:'Actions',
                    datafield: 'actions',
                      cellsalign: 'center',
                    width: "10%"
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
var url = "/product/mrp_config";
    var formData = new FormData($('#igGenForm')[0]);
    $.ajax({
    url: url,
    type: 'POST',
    data: formData,
    async: false,
    success: function(data) {
      source.localdata = data;
      $("#jqxgrid").jqxGrid('updatebounddata', 'cells');
     /* if(data.status==1){
         location.reload();
      }*/
    },
    cache: false,
    contentType: false,
    processData: false
    });
    $('.changeMandatory').bootstrapToggle({ on: 'Error',
      off: 'Warning',width:'100px',height:'20px'});
});

$('.vendorSelect').change(function (){
    if($(this).val()!=''){
      $('#product')
      .empty()
      .append('<option selected="selected" value="">Select One</option>')
      ;
      product_qty=[];
      productInfo=[];
      var formData = new FormData();
      formData.append("vendor", $(this).val());
    $("#loading").show();
     $.ajax({
        url: '/qrcode/getProductsByUser',
        type: 'POST',
        data: formData,
        async: false,
        success: function(data) {
          $.each(data, function(key, value) { 
          $('#product')
          .append($("<option></option>")
          .attr("value",value.product_id)
          .text(value.name+"-"+value.material_code)); 
          });         
          $('.vendorDis').removeAttr('disabled');
          $("#loading").hide();
        },
        cache: false,
        contentType: false,
        processData: false
      });
    } else {
      $('#product')
      .empty()
      .append('<option selected="selected" value="">Select One</option>');
      $('#product').val('');
    }  
    $('#product').selectpicker('refresh');        
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

$('#updateConfig,#updateConfigFile').submit(function(event) {
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
 location.reload();
        $("#loading").hide();
        alert(data.message);
      } else {

        alert(data.message);
        $("#loading").hide();
      }
    },
    cache: false,
    contentType: false,
    processData: false
    });  

});



});   //document close
</script>
@stop