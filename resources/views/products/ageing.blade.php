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
          <h3 class="box-title"><i class="fa fa-th"></i><strong> Product Blocking </strong>Config</h3>
        </div>

          <div class="addForm">
          <div class="col-md-3">
            <div class="form-group">
                <label> Product Group</label>
                      <select class="form-control selectpicker" data-live-search="true" id="ser_product_id" name="ser_product_id" >
                       <option value="">Please  Product Group</option>
                        @foreach($products as $product)
                          <option value="{{$product->group_id}}"> {{$product->name}}</option>
                        @endforeach
                      </select>
              </div>
          </div>
          

         

           <div class="col-md-6"> <button type="button" class="btn btn-primary addBtn" >Search</button>  <button type="button" data-toggle="modal" data-target="#configModel" class="btn btn-primary pull-right configBtn" >Config</button> </div>
      </div>
    </div>
  <div id="jqxgrid"></div>   
</div>
</form>
<div id="configModel" class="modal fade" role="dialog">
  <div class="modal-dialog">
    <form id="updateConfig" action="updateAgeingConfig" method="post">
    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Product Block Days Config</h4>
      </div>
      <div class="modal-body">
        <div class="modelPopUp">
          <div class="col-md-6">
           
            <label>Select Product Group</label>
            <div class=" form-group">
            <select class="form-control  selectpicker" data-live-search="true" id="product_id" name="product_group_id" >
             <option value="">Please  Product Group</option>
               @foreach($products as $product)
                <option value="{{$product->group_id}}"> {{$product->name}}</option>
              @endforeach
            </select>
          </div>
          </div>

          <div class="col-md-4">
            <label>Block Period in Days</label>
            <div class=" form-group">
            <input type="text" name="block_period" id="block_period" class="form-control ">
            </div>
          </div>
          <div class="col-md-2 pull-right">
            <button type="submit" class="btn btn-primary pull-right marginTop32" >Submit</button>
          </div>
        
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
      
/* $('body').on('change','.changeMandatory',function(){
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
*/
 $('body').on('focus',".datepicker", function(){
   $(this).datepicker({format:'dd-mm-yyyy',todayHighlight:true,autoclose: true});
 });
var source =
    {
        localdata: [],
        datafields: [{
                        name: 'name',
                        type: 'string'
                    }, {
                        name: 'block_period',
                        type: 'string'
                    },  {
                        name: 'material_code',
                        type: 'string'
                    }, {
                        name: 'expiry_period',
                        type: 'string'
                    }, {
                        name: 'product_id',
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
                    width: "25%"
                }, {
                    text:'Block Period',
                    datafield: 'block_period',
                      cellsalign: 'left',
                    width: "25%"
                }, {
                    text:'expiry period',
                    datafield: 'expiry_period',
                      cellsalign: 'center',
                    width: "15%"
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