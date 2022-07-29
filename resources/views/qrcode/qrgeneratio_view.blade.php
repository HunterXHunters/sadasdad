@extends('layouts.default')
@extends('layouts.header')
@extends('layouts.sideview')

   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.4.1/css/bootstrap-datepicker3.css"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.12.1/css/bootstrap-select.css" />

<div align="center" id="loading" style="text-align:center; z-index:9999;position:absolute;background:rgba(0,0,0,0.3);height:709px; width:1261px; display:none;" ><img src="/img/loading.gif" >    </div>
@section('content')

<style>
    .btn {
        margin-left: 10px !important;
    }
    hr {
    margin-top: 4px !important;
    margin-bottom: 0px !important;
    border: 0;
    border-top: 1px solid #eee;
  }
</style>
    <style>

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
.addBtn{
      margin-top: 32px
}
.dropdown-toggle{
  margin-left: 0px !important;
}
</style>
<!-- Page content -->
<?php if (isset($error_message))
{ ?>
    <div>
        <span><?php echo $formData['error_message']; ?></span>
    </div>
    <?php } ?>
<!-- 
<link href="http://cdn.rawgit.com/davidstutz/bootstrap-multiselect/master/dist/css/bootstrap-multiselect.css"
    rel="stylesheet" type="text/css" />
<script src="http://cdn.rawgit.com/davidstutz/bootstrap-multiselect/master/dist/js/bootstrap-multiselect.js"
    type="text/javascript"></script> -->
    <form id="igGenForm">
        <div class="box">

    <div class="box-header">
      <h3 class="box-title"><i class="fa fa-th"></i><strong> Download </strong> Labels </h3>
    </div>

            <div class="addForm">
          <div class="col-md-3">
            <div class="form-group">
                <label>Vendor</label>
                      <select class="form-control vendorSelect  selectpicker" data-live-search="true" id="vendor" name="vendor" >
                       <option value="">Please Select Vendor</option>
                        @foreach($vendors as $vendor)
                          <option value="{{$vendor->user_id}}"> {{$vendor->username}}</option>
                        @endforeach
                      </select>
              </div>
          </div>

          <div class="col-md-3">
            <div class="form-group">
                <label>Product</label>
                      <select class="form-control productSelect  selectpicker" data-live-search="true" id="product" name="product" >
                       <option value="">Please Select Product</option>
                      </select>
              </div>
          </div>

          <div class="clearfix"></div>
          <hr>
           <div class="col-md-3">
            <div class="form-group">
                <label>From Date</label>
                <input type="text"  class="datepicker form-control " name="fromDate" id="fromDate" class="form-control ManufactureDate vendorDis">      
              </div>
          </div>

           <div class="col-md-3">
            <div class="form-group">
                <label>To Date</label>
                <input type="text"  class="datepicker form-control " name="toDate" id="toDate" class="form-control ManufactureDate vendorDis">      
              </div>
          </div>

           <div class="col-md-3"> <button type="button" class="btn btn-primary addBtn" >Search</button>  </div>
      </div>
    </div>
      <div id="jqxgrid"></div>
            
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



        <script type="text/javascript">
 

        function regen(qid){
           var formData = new FormData();
            formData.append("qid",qid);
    $.ajax({
    url: '/qrcode/resetPdf',
    type: 'POST',
    data: formData,
    async: false,
    dataType: "json",
    success: function(data) {
       alert(data.msg);
    },
    cache: false,
    contentType: false,
    processData: false
    });
        }
    


$(document).ready(function() {

 $('body').on('focus',".datepicker", function(){
           $(this).datepicker({format:'dd-mm-yyyy',todayHighlight:true,autoclose: true});
         });


var source =
    {
        localdata: [],
        datafields: [{
                        name: 'batch_no',
                        type: 'string',
                        cellsalign: 'center'
                    }, {
                        name: 'batch_no',
                        type: 'string'
                    },  {
                        name: 'mfg_date',
                        type: 'string'
                    }, {
                        name: 'download_pdf',
                        type: 'string'
                    }, {
                        name: 'download_excel',
                        type: 'string'
                    }, {
                        name: 'material',
                        type: 'string'
                    }, {
                        name: 'quatity',
                        type: 'string'
                    }, {
                        name: 'username',
                        type: 'string'
                    },{
                        name: 'genDate',
                        type: 'string'
                    },{
                        name: 'download',
                        type: 'string'
                    },{
                        name: 'userstamp',
                        type: 'string'
                    }],
        datatype: "json"
    };
var dataAdapter = new $.jqx.dataAdapter(source);
var columns = [ {
                    text: 'Location',
                    datafield: 'username',
                    width: "18%"
                }, {
                    text: 'Material Code - Material Name',
                    datafield: 'material',
                    width: "18%"
                },{
                text: 'Batch No',
                    datafield: 'batch_no',
                    cellsalign: 'left',
                    width: "7%"
                }, {
                    text: 'Mfg Date',
                    datafield: 'mfg_date',
                      cellsalign: 'left',
                    width: "8%"
                }, {
                    text: 'Quatity',
                    datafield: 'quatity',
                      cellsalign: 'left',
                    width: "6%"
                }, {
                    text: '<i class="fa fa-file-pdf-o" style="font-size:16px;color:red"></i> PDF',
                    datafield: 'download_pdf',
                      cellsalign: 'center',
                    width: "6%"
                }, {
                    text: '<i class="fa fa-file-excel-o" style="font-size:16px;color:green"></i> Excel',
                    datafield: 'download_excel',
                      cellsalign: 'center',
                    width: "6%"
                }, {
                    text: 'Remarks',
                    datafield: 'download',
                      cellsalign: 'left',
                    width: "15%"
                },{
                    text: 'Created On',
                    datafield: 'genDate',
                      cellsalign: 'left',
                    width: "8%"
                },{
                    text: 'Created By',
                    datafield: 'userstamp',
                      cellsalign: 'left',
                    width: "8%"
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
/*$('#getproduct_loactionmapping').submit(function(event) {
    event.preventDefault();
  //    $form = $(this);
   
});*/

var url = "getImportProductDetails";
$('.addBtn').click(function(){
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
});

 $('.vendorSelect').change(function (){
        if($(this).val()!=''){
          $('#product')
          .empty()
          .append('<option selected="selected" value="">Select Product</option>')
          ;
          product_qty=[];
          productInfo=[];
        var formData = new FormData();
          formData.append("vendor", $(this).val());
        $("#loading").show();
         $.ajax({
            url: 'getProductsByUser',
            type: 'POST',
            data: formData,
            async: false,
            success: function(data) {
                $.each(data, function(key, value) { 
                  product_qty[value.product_id]=parseInt(value.quantity) || 0;
                  productInfo[value.product_id]=[value.name,value.description,value.material_code,value.layout_id,value.location_id,value.product_id,value.quantity];
                  $('#product')
                  .append($("<option></option>")
                  .attr("value",value.product_id)
                  .text(value.name+"-"+value.material_code)); 
                });
                console.log(productInfo);
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
          .append('<option selected="selected" value="">Select Product</option>')
          ;
          $('#product').val('');
        }  
        $('#product').selectpicker('refresh');        
      });

});

   
        </script>
        @stop