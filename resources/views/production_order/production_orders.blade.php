
    @extends('layouts.default')

    @extends('layouts.header')

    @extends('layouts.sideview')

    @section('content')

    @section('style')
    {{HTML::style('jqwidgets/styles/jqx.base.css')}}
    {{HTML::style('css/bootstrap-select.css')}}
    <!-- {{HTML::script ('jqwidgets/jqxpopover.js')}} -->
    @stop
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.12.1/css/bootstrap-select.css" />
    <link rel="stylesheet" href="../jqwidgets/styles/jqx.base.css" type="text/css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.4.1/css/bootstrap-datepicker3.css"/>
  
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
    <!-- {{HTML::script('jqwidgets/jqxpopover.js')}} -->
    {{HTML::script('jqwidgets/jqxcheckbox.js')}}
    {{HTML::script('jqwidgets/jqxdata.export.js')}}
    {{HTML::script('jqwidgets/jqxgrid.export.js')}}
    {{HTML::script('jqwidgets/jqxgrid.sort.js')}}
    {{HTML::script('jqwidgets/jqxbuttons.js')}}
    
    {{HTML::script ('jqwidgets/jqxwindow.js')}}

    
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.12.1/js/bootstrap-select.js"></script>

<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.4.1/js/bootstrap-datepicker.min.js"></script>
<script >

  $("#jqxButton").on('click', function ()
            {
                $("#events").find('span').remove();
                $("#events").append('<span>Button Clicked</span');
            });
</script>
    <style>
      .modal-footer {     
        text-align: right;
        border-top: none !important;
      }
      .addLabelHeight{
            margin-top: 32px;
      }
      a:hover {
            background-color:white;
      }
      .productionOrderNumber,.createdDt,.poQty,.palQty,.pkdQty,.conQty,.cnfZ01,.cnfEA{
        font-weight: bold;
      }
    </style>

    <div class="box">
        <div class="box-header">
          <h3 class="box-title"><strong>Process </strong> Orders</h3>
        </div>

      <div  class="box-body">
          @if($errors[0]=="")
          <div></div>
          @else
          <div class="alert <?=$errors[0]?'alert-success':'alert-danger'?>" id="alert">
          <a href="/production_orders" class="close" data-dismiss="alert" aria-label="close"  id= "close"title="close">×</a>
          <?php
           foreach ($errors as $key => $value){
             if($key==0) continue;
             echo '<p>'.$value.'</p>';
          }
          ?>
          </div>
          @endif

          <div class="alert alert-warning" id="reversAlert" id="alert">
            <a href="/production_orders" class="close" data-dismiss="alert" aria-label="close"  id= "close"title="close">×</a>
            <p></p>
          </div>
          
        <div class="tile-body nopadding">
            <!-- //<form  method="post"  action="{{url('productorder/getPOorders')}}"> -->
         <!-- {{ Form::open(array('url' => '/productorder/getPOorders', 'id' => 'getPOorders', 'files'=>'true' )) }} -->
          <!--  <div class="alert alert-success">
             <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
            <strong>Success!</strong> You should <a href="#" class="alert-link">read this message</a>.
          </div> -->        
          <div class="col-md-3">
          <div class="form-group">
          <label>Material</label>
              <select class="form-control productSelect  selectpicker" data-live-search="true" id="product" name="product" required="required" multiple="multiple" >
           <!--     <option value="">Please Select one Product</option> -->
              @foreach($products as $product)
              <option value="{{$product->product_id}}"> {{$product->material_code}}-{{$product->name}}</option>
              @endforeach
              </select>
          </div>
          </div>

          <div class="col-md-3">
            <div class="form-group">
                <label>Plant</label>
                      <select class="form-control vendorSelect selectpicker" data-live-search="true" id="vendor" name="vendor" required="required">
                       <!-- <option value="">Please Select one Location</option> -->
                       @foreach($vendors as $vendor)
                          <option value="{{$vendor->location_id}}" {{ $vendor->location_id ? 'selected="selected"' :'' }}> {{$vendor->location_name}}-{{$vendor->erp_code}}</option>
                        @endforeach
                      </select>
              </div>
          </div>
          
          <div class="col-md-6" style="  margin-top: 32px;">
            <div class="form-group">     
                    <!-- <a href="{{ url('productorder/getPOorders') }}" > --> 
              <!-- <button class="btn btn-success" onclick="getdetailsforpo();" >Search</button> -->
              <button  type="button" class="btn btn-success" data-toggle="modal" id="search" onclick="getdetailsforpo()">Search</button>
            <!-- </a> -->
              <button type="button" class="btn btn-success pull-right" data-toggle="modal" data-target="#createOrder">Create Order</button>
            </div>
            <div class="loader" id="loader"></div>
          <div>
    </div>
          </div>
        </div>
                  <!-- {{ Form::close() }} -->
                    <br>
                    <br>
                     <div id="tablegrid"></div>
  
            <!-- </form> -->
        </div>
        <!-- <div id="phpinv"> -->
      </div>



<div id="createOrder" class="modal fade" role="dialog">
  <div class="modal-dialog">

    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Create Order</h4>
      </div>
      <div class="modal-body">
<form method="post" action="/productorder/createOrder" id="createOrderForm"  >
<div class="form-group">
          <div class="col-xs-6">
          <div class="form-group">
          <label>Material</label>
              <select class="form-control productSelect  selectpicker selectProduct" data-live-search="true" id="product_po_id" name="product"  required="required">
               <option value="">Please Select one Product</option>
              @foreach($products as $product)
              <option value="{{$product->product_id}}"> {{$product->material_code}}-{{$product->name}}</option>
              @endforeach
              </select>
          </div>
          </div>

          <div class="col-xs-6">
            <div class="form-group">
                <label>Plant</label>
                      <select class="form-control vendorSelect selectpicker" data-live-search="true" id="vendor_po" name="vendor"   required="required">
                       <option value="">Please Select one Location</option>
                        @foreach($vendors as $vendor)
                          <option value="{{$vendor->location_id}}"> {{$vendor->location_name}}-{{$vendor->erp_code}}</option>
                        @endforeach
                      </select>
              </div>
          </div>

          <div class="col-xs-6">
            <div class="form-group">
                <label>Quantity</label>
                <input type="number" name="orderQty" id="orderQty" min="1" class="form-control decmelNum" placeholder="Quantity"   required="required">
              </div>
          </div>

          <div class="col-xs-6">
            <div class="form-group">
                <label>Order UOM</label>
                 <select class="form-control vendorSelect  UOMSelect"  id="uom" name="uom"   required="required">
                       <option value="0">Please Select One UOM</option>
              @foreach($uoms as $uom)
                          <option value="{{$uom['name']}}"> {{$uom['name']}} </option>
                        @endforeach 
                      </select>
              </div>
              <div id="uom_text"></div>
          </div>
            <div class="col-xs-6">
              <div class="form-group">
                <label>Remarks</label>
                <input type="text" name="remarks" maxlength="150" id="remarks" class="form-control " placeholder="Remarks"   required="required">
              </div>
           </div>
           


            <div class="col-xs-6">
              <div class="form-group"> 
                <button type="button" class="btn btn-success addLabelHeight pull-right"  id="createOrderSubmit">Create Order</button>

              </div>
            </div>
          
      </div>
    </form>
      </div>
 <div class="modal-footer"></div> 
    </div>

  </div>
</div>
      <!-- MODAL  FOR PO_CONFIRMED --> 
         
              <div class="modal" id="poModal">
                <div class="modal-dialog" >
                  <div class="modal-content">
                    <div>
<!--                     <a  class="close" data-dismiss="modal" aria-label="Close"  id= "close"title="close">×</a>
 -->                      
  
                    </div>
                    <div class="modal-header bg-primary">
                      
                        
                      <h4 class="modal-title">PO Confirm Details :<span class="productionOrderNumber"></span>
                      <span class="pull-right fa fa-times" data-dismiss="modal" aria-label="Close" style="color: red;">
                      </span> 
                      </h4>
                     
                    </div>
                    
                    <div class="modal-body" >
                      <div class="thumbnail">
                        <table class="table table-bordered table-striped table-dark table-hover" style="margin-bottom:0px;">
                          <tbody>
                            <tr>
                              <td> PO Number </td>
                              <td class="productionOrderNumber"></td>
                              <td> Created On</td>
                              <td class="createdDt"></td>
                            </tr>

                            <tr>
                              <td> PO Quantity</td>
                              <td class="poQty">PO Quantity</td>
                              <td> Quantity in Pal</td>
                              <td class="palQty">PO Quantity</td>
                            </tr>

                            <tr>
                              <td> Packed Quantity</td>
                              <td class="pkdQty">PO Quantity</td>
                              <td> confirmed Quantity</td>
                              <td class="conQty">PO Quantity</td>
                            </tr> 
                            <tr>
                              <td> confirmed cartons</td>
                              <td class="cnfZ01">Conf cartons</td>
                              <td> confirmed eaches</td>
                              <td class="cnfEA">Conf Eaches</td>
                            </tr>
                            <tr>
                              <td> Remark </td>
                              <td class="productionRemark"></td>
                              <td> </td>
                              <td class=""></td>
                            </tr>
                          </tbody>
                        </table>
                      </div>

                         <div id="po_confirm_grid"></div> 
                    </div>
                    
                  </div>
                </div>
              </div>



               <div class="modal" id="pocancel">
                <div class="modal-dialog" >
                  <div class="modal-content">
                    <div>
<!--                     <a  class="close" data-dismiss="modal" aria-label="Close"  id= "close"title="close">×</a>
 -->                      
  
                    </div>
                    <div class="modal-header bg-primary">
                      
                        
                      <h4 class="modal-title">Reverse Order 
                      <span class="pull-right glyphicon glyphicon-remove" data-dismiss="modal" aria-label="Close" style="color: red;">
                      </span> 
                      </h4>
                     
                    </div>
                    
                    <div class="modal-body" >
                      <div class="thumbnail">
                        <form method="post" id="pocancelForm" name="pocancelForm" action="" onsubmit="return cancelPo()">
                        <table class="table table-bordered table-striped table-dark table-hover" style="margin-bottom:0px;">
                          <tbody>
                                <input type="hidden" placeholder="id" name="can_id" id="can_id"> 
                            <tr>
                              <td> PO Number </td>
                              <td > <input type="hidden" placeholder="Reson for reverse" name="rpno" id="rpno" class="form-control rpno">  <h4 class="rpno">
                              </td>
                            </tr>


                            <tr>
                              <td> Refrence No</td>
                              <td > <input type="hidden" placeholder="Reson for reverse" name="refno" id="refno" class="form-control refno"> <h4 class="refno"></h4>  </td>
                            </tr>

                            <tr>
                              <td> Counter</td>
                              <td> <input type="hidden" placeholder="Reson for reverse" name="refCounter" id="refCounter" class="form-control refCounter"><h4  class="refCounter"></h4>   </td>
                            </tr>
                            <tr>
                              <td>Reversal reason</td>
                              <td class=""><input type="text" required placeholder="Reson for reverse" name="reson" id="reson" class="form-control"> </td>
                            <tr>
                              <td colspan="2"><button type="submit" class="btn btn-success pull-right"> Cancel Order</button></td>
                            </tr>
                          </tbody>
                        </table>
                      </div>

                         <div id="po_confirm_grid"></div> 
                    </div>
                    
                  </div>
                </div>
              </div>
         
    </div>

  </div>
  <style>
.loader {
  border: 5px solid #f3f3f3;
  border-top-color: rgb(243, 243, 243);
  border-top-style: solid;
  border-top-width: 5px;
  -webkit-animation: spin 1s linear infinite;
  animation: spin 1s linear infinite;
  border-top: 5px solid #555;
  border-radius: 50%;
  width: 50px;
  height: 50px;
  display:none;
  position: absolute;
  opacity: 0.7;
  z-index: 99;
  text-align: center;
  opacity: .9;
}

/* Safari */
@-webkit-keyframes spin {
  0% { -webkit-transform: rotate(0deg); }
  100% { -webkit-transform: rotate(360deg); }
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}
</style>

</div>

    <script >
      // Multi select
      // $(document).ready(function() {
      //    $('#product').multiselect();
      // });
      //var arr = new Array(100);
 //     var error='<?=implode('\n',$errors)?>';
      //console.log(arr[1]);
      // var a =arr[1];
      // alert(arr);
      // if(a){
      //    document.write(lert(error[1]));
      // }
      // else{
      //   document.write(alert(error[1]));
      // }
     //  if(error!=''){

     //      document.write(alert(classerror));
     //   //alert(error);
     // }
$("[type='number']").keypress(function (evt) {
    if(evt.which < 48 || evt.which > 57){
    evt.preventDefault();
  }
});
function reversePO(id,batch_no,is_confirmed,reference_value,erp_doc_no){
  console.log(id,batch_no,is_confirmed,reference_value,erp_doc_no);
   $('#poModal').modal('toggle');
   $('#pocancel').modal('toggle');
   $('.rpno').html(erp_doc_no);
   $('.refno').html(reference_value);
   $('.refCounter').html(is_confirmed);
      $('#can_id').val(id);
      $('#rpno').val(erp_doc_no);
   $('#refno').val(reference_value);
   $('#refCounter').val(is_confirmed);
}
function cancelPo(){
     $('#pocancel').modal('toggle');
     $('#reversAlert p').show();
  $.ajax({
    type: "POST",
    url: '/productorder/cancelOrder',
    data: $('#pocancelForm').serialize(),
     success: function( data){
        $('#reversAlert p').html(data);
        $('#reversAlert').show();
      console.log(data);
//      alert(data);
      },
      error: function(data){
        $('#reversAlert').show();
          console.log(data);
      }
  });
  return false;
}

       
$(document).ready(function (){ 
      $('#reversAlert ').hide();
      $('#orderQty').on('input',function(e){
        $('#uom_text').hide();    
        $('#uom').val('');
      });
      $('#alert').delay(10000).fadeOut();
      $("#createOrderSubmit").click(function (e) {
        var x = document.getElementById("loader");
        var x1 = document.getElementById("createOrder");
        
        x.style.display = "block";
        x1.style.display = "none";
          $.ajax({
            type:"POST",
            url:'/productorder/getECCstatus',
            success:function(data){
            // alert(data);
            // x.style.display = "none";
            if(data==1){
            var r =confirm("ECC is down.Do you want to confirm order creation offline ?");
              //e.preventDefault();
              if (r == true){
                $("#createOrderForm").submit();
//                return true;
              }
              else{
                e.preventDefault();
  //              return false;
              }
            } else {
                              $("#createOrderForm").submit();
                              $('#loading').show();

            }

            },

          });
          
      });

      $('#uom').change(function(){
         $('#uom_text').show();
        var qty =$('#orderQty').val();
        var UOM =$('#uom').val();
        // alert(UOM);
        var p_id=$('#product_po_id').val();
        $.ajax({
          type:"POST",
          url:'/productorder/getConversion/'+qty+'/'+UOM+'/'+p_id,
          success:function( data){
            $('#uom_text').html(data);
          },
        });
      });
      $('#')
       

      $('.selectProduct').change(function(){
          $.ajax({
            type: "POST",
            url: '/getUomProduct/'+$(this).val(),
             success: function( data){
                console.log(data);
                $('.UOMSelect').empty().append('<option value="0">Select UOM</option>');
                data=JSON.parse(data);
                $.each( data, function( key, value ) {
                  $('.UOMSelect').append('<option value="'+key+'">'+value+'</option>');
//                  alert( key + ": " + value );
                });
//                $('.UOMSelect').selectpicker('refresh');
              },
              error: function(data){
                  console.log(data);
              }
          });
      });

      $("#mfg_date").datepicker({format:'d-m-yyyy',todayHighlight:true,autoclose: true});
});

$("#search").click(function(){
  p_id = $("#product").val();
  
  if(!p_id){
    alert('Please select material');
  
  }
});
var getdetailsforpo;
var createOrderpopup;
$(document).ready(function(){

/* GRID TO DISPLAY CONFIRMED PO */
createOrderpopup=function(erp_doc_no,eseal_doc_no){
// alert("hai");
// if(eseal_doc_no)
  $('#poModal').modal('toggle');
  $('.productionOrderNumber').html(erp_doc_no);
  
   var  url="productorder/getPOconfirmdetails/"+erp_doc_no+"/"+eseal_doc_no;
   $.ajax({
  type: "POST",
  url: "productorder/getPoQuantity?po_number="+erp_doc_no,
  success: function(resultData) { 

  $('.createdDt').html(resultData.createdDt);
  $('.poQty').html(resultData.po_qty+' ('+resultData.po_uom+') ');
  $('.palQty').html(resultData.qty+' (PAL) ');
  $('.pkdQty').html(resultData.packedqty+' (PAL) ');
  $('.conQty').html(resultData.confirmQty+' (PAL) ');
  $('.cnfZ01').html(resultData.çonfirm_cartons);
  $('.cnfEA').html(resultData.confirm_EA);
  $('.productionRemark').html(resultData.remark);
   // alert("Save Complete"); 
   console.log(resultData);

},
  dataType: "json"
});

   var source =
          {
            //localdata:[],
              datatype: "json",
              datafields:
                [
                  {name: 'timestamp', type: 'string'},
                  {name: 'batch_no', type: 'string'},
                  {name: 'reference_value', type: 'string' },
                  {name:'qty',type:'string'},
                  {name: 'status',type:'string'},
                  {name: 'actions',type:'string'}
                ],
              url: url,
              pager: function (pagenum, pagesize, oldpagenum) {
                }

          };
             var dataAdapter = new $.jqx.dataAdapter(source);
                
                poconfrimGrid(dataAdapter);
}
 var  cols=[
                { text: 'Date',  datafield: 'timestamp',width:'15%'},
                { text: 'Batch No',  datafield: 'batch_no',width:'20%'},
                { text: 'Reference Value ', datafield:'reference_value',width:'20%'},
                {text: 'Quantity',datafield:'qty',width:'10%'} , 
                {text:'Status',datafield:'status',width:'20%'},              
                {text:'Action',datafield:'actions',width:'15%'}              
                ];
  
/* GRID TO DISPLAY  PO DETAILS  */  
getdetailsforpo=function ()
{

  p_id = $("#product").val();
  l_id  =$("#vendor").val();
  if(p_id ==''){
  alert("The paragraph was clicked.");
  }
  
  var  url="productorder/getPOorders/"+p_id+"/"+l_id;

   var source =
          {
            //localdata:[],
              datatype: "json",
              datafields:
                [
                  {name: 'material_code', type: 'string'},
                  {name: 'ean',type:'string'},
                  // {name: 'erp_code', type: 'string' },
                  //{ name: 'product_id', type: 'string'},
                  //{ name: 'location_id', type: 'string'},
                  {name:'description',type:'string'},
                  {name: 'order_no', type: 'string'},
                  {name: 'po_type',type:'string'},
                  {name: 'qty',type:'string'},
                  {name: 'packed_qty',type:'string'},
                  {name: 'po_status',type:'string'},
                  
                  // {name: 'conversion_id',type:'string'},
                  // {name: 'manufacturer_id',type:'string'},
                  {name: 'date',type:'string'}
                  // {name: 'actions',type:'string'}
                ],
                //  updaterow: function (rowid, rowdata, commit) {
                    
                //     commit(true);
                // },
             //id: 'product_id',
              url: url,
              pager: function (pagenum, pagesize, oldpagenum) {
                }

          };

    //       var NoteRenderer = function (row, datafield, value) {
    //             editrow = row;
    //             var dataRecord = $("#ffrGrid").jqxGrid('getrowdata', editrow);
    //             var idx = dataRecord.reportdueindex;
    //             var ffrTitle = dataRecord.grantid;
    //             var html = "<input type='button' class='grantId' id='55' value='Note' onclick='customFunction()'/>";
    //             return html;
    //         }
    //          var cellsrenderer = function (row, column, value) {
    //     return value;
    // };
            console.log(source);
             var dataAdapter = new $.jqx.dataAdapter(source);
                
                createGrid(dataAdapter);
               
}
 var  columns=[
                { text: 'Order No', datafield: 'order_no',width:'10%' },
                { text: 'Material Code',  datafield: 'material_code',width:'10%'},
                {text:'Description',datafield:'description',width:'20%'},
                // { text: 'location Erp', datafield:'erp_code',width:'15%'},
                { text: 'EAN', datafield: 'ean',width:'10%'},
                { text: 'Quantity', datafield: 'qty',width:'15%'},
                { text: 'Packed Qty', datafield: 'packed_qty',width:'10%'},
                { text: 'Type', datafield: 'po_type',width:'5%'},
                { text: 'Status', datafield: 'po_status',width:'5%'},
                // { text: 'UOM', datafield: 'order_uom',width:'10%'},
                { text: 'Created Date', datafield: 'date',width:'15%'}
                // {text:'action',datafield:'actions',width:'5%'}
           //      {text: 'Remarks', datafield: 'order_uom',width:'25%'}
                // {text: 'Edit', datafield: 'btn', columntype: 'button',cellsrenderer: function () {
                //       return "Edit";}, buttonclick: function () {
                //          $("#createOrder").toggle();
                //        }
                //        }
                ];

function createGrid(source)  
{
  $("#tablegrid").jqxGrid({

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
                        //enablebrowserselection: true,
                        selectionmode: 'multiplecellsextended',
                        filterable: true,
                        autoshowloadelement: false,
                        columns: columns              
    });

} 
function poconfrimGrid(source){
  $("#po_confirm_grid").jqxGrid({

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
                        autoshowloadelement: false,
                        filterable: true,
                        columns: cols              
    });
}
    getdetailsforpo();
});
    </script>
    @stop
