@extends('layouts.default')
@extends('layouts.header')
@extends('layouts.sideview')
@section('content')
@section('style')
    {{HTML::style('jqwidgets/styles/jqx.base.css')}}    

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.4.1/css/bootstrap-datepicker3.css"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.12.1/css/bootstrap-select.css" />
    
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
.edit{
  display: none;
}
button{
  background: transparent;
    border: none;
}
</style>

 <!-- Latest compiled and minified CSS -->
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">

<!-- jQuery library -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>

<!-- Latest compiled JavaScript -->
<!-- <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>  -->

@stop
<div align="center" id="loading" style="text-align:center; z-index:9999;position:absolute;background:rgba(0,0,0,0.3);height:709px; width:1261px; display:none;" ><img src="/img/loading.gif">    </div>
<div class="box">
    <div class="box-header">
      <h3 class="box-title"><i class="fa fa-th"></i><strong> Generate </strong> Labels </h3>
    </div>
    <div class="box-body">
        <?php if($success==1) { ?>
       <div class="alert alert-success">
        <a href="#" class="close" data-dismiss="alert" aria-label="close" title="close">×</a>
         <strong>Success!</strong> QrCodes Added Successfully,<a href="/qrcode/generate_quotes">Click Here to check the process</a>.
        </div>
        <?php } ?>      
        {{Form::open(array('method'=>'post','url' => '/qrcode/generate_quotes','id'=>'qr_form1','enctype'=>'multipart/form-data'))}}
      <div class="row">
        <div class="addForm">
          <div class="col-md-3">
            <div class="form-group">
                <label>Vendor</label>
                      <select class="form-control vendorSelect selectpicker" data-live-search="true" id="vendor" name="vendor" >
                       <option value="">Please Select Vendor</option>
                        @foreach($vendors as $vendor)
                          <option value="{{$vendor->user_id}}"> {{$vendor->location_name}}-{{$vendor->erp_code}}</option>
                        @endforeach
                      </select>
              </div>
          </div>


          <div class="col-md-2">
            <div class="form-group">
                <label>Date of Manufacture</label>
                <input type="text" name="manufactureDate" id="manufactureDate"  class="form-control datepicker ManufactureDate vendorDis">      
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

         <div class="col-md-2">
            <div class="form-group">
                <label>Quantity <span id="mulVal"></span></label>
                <input type="text" name="prod_quantity" id="prod_quantity" class="form-control number vendorDis">      
                <h6 id="mulValDown"></h6>
              </div>
          </div>
          <div class="col-md-2"> <button type="button" class="btn btn-primary addBtn" >Add</button>  </div>

          <div class="clearfix"></div>
          <div class="col-md-12">
              
              <table class="addedTable table table-stripped table-bordered table-hover" style="display: none">
                <thead>
                  <tr>
                    <th>Sno</th>
                    <th>Material</th>
                    <th>Material Description</th>
                    <th>Vendor</th>
                    <th>Mfg Date</th>
                    <th>Quatity</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody class="addedData">
                   <!-- <tr>
                      <td></td>
                      <td></td>
                      <td></td>
                      <td>
                        <input type="text" value="" name="grid_userId[]" id="userId_1">
                        <input type="text" value="" name="grid_manufactureDate[]" id="manufactureDate_1">
                        <input type="text" value="" name="grid_Product[]" id="Product_1">
                        <input type="text" value="" name="grid_Quantity[]" id="Quantity_1">
                      </td>
                   </tr> -->
                </tbody>
                <tfoot>
                  <tr>
                    <td colspan="7">
                      <button type="submit" class=" pull-right btn btn-primary">Submit</button>
                      <button type="button" onclick=" location.reload();" style="margin-right: 15px;" class="pull-right btn btn-warning">Cancel</button>
                    </td>
                  </tr>
                </tfoot>
                  
              </table>

          </div>

        </div>
        {{Form::close()}}
    </div>
    <div class="box-footer">
    </div>  
  </div>
  <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.4.1/js/bootstrap-datepicker.min.js"></script>
  
  <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.12.1/js/bootstrap-select.js"></script>

  <script type="text/javascript">

    

    var product_qty=[];
    var productInfo=[];
    var tr_index=0;
    var tr_rowsrno=0
    var final_checkQty=0;
    var selectedVendors=[];

    function removeRow(removeindex){
        //alert(removeindex); trow_`+tr_index+`
        $('#trow_'+removeindex).remove();       
        for(i=parseInt(removeindex)+1;i<=tr_index;i++){
          sno=$('#inx_cell_'+i).html();
          $('#inx_cell_'+i).html(sno-1);
        }
        tr_rowsrno=tr_rowsrno-1;
    }

     function edit(removeindex){
      $('#trow_'+removeindex+' .edit').show();     
      $('#trow_'+removeindex+' .nonedit').hide(); 
    }
     function updateRow(removeindex){
      var mulQuantity= $('#mulQuantity_'+removeindex).val();
      var Quantity= $('#Quantity_'+removeindex).val();
      var accptqty=parseInt(Quantity%mulQuantity)||0;
      if(!(Quantity!=0 && accptqty==0)){
         $('#Quantity_'+removeindex).val(0);
        alert('Please Enter correct Quantity\n');
      } 
      $('#trow_'+removeindex+' .Quantity_'+removeindex).html($('#Quantity_'+removeindex).val());
      $('#trow_'+removeindex+' .manufactureDate_'+removeindex).html($('#manufactureDate_'+removeindex).val());
      $('#trow_'+removeindex+' .edit').hide();     
      $('#trow_'+removeindex+' .nonedit').show(); 

      //$('#manufactureDate').val('').change();
      //$('#prod_quantity').val('').change();


        var tr_index=removeindex;
        var vendor=$('#userId_'+tr_index).val();
        var product=$('#Product_'+tr_index).val();
        var manufactureDate=$('#manufactureDate_'+tr_index).val();
        var prod_quantity=$('#Quantity_'+removeindex).val();
        var mfgDatechange=0;
        var temp=selectedVendors[vendor+'#'+product][tr_index];
        console.log("updating data set");
        console.log(temp);

        for (var i =0; i < selectedVendors[vendor+'#'+product].length; i++) {
          var temp=selectedVendors[vendor+'#'+product][i];
          if(temp.tr_index==tr_index){
            console.log("temp.manufactureDate");
            if(temp.manufactureDate==manufactureDate){
              temp={'vendor':vendor,'product':product,'tr_index':tr_index,'manufactureDate':manufactureDate,'prod_quantity':prod_quantity};
              selectedVendors[vendor+'#'+product][i]=temp;
            } else {
              for (var j =0; j < selectedVendors[vendor+'#'+product].length; j++) {
                var tempj=selectedVendors[vendor+'#'+product][j];
                if(tempj.manufactureDate==manufactureDate){
                  mfgDatechange=1;
                }
              }
              if(mfgDatechange==0){
                temp={'vendor':vendor,'product':product,'tr_index':tr_index,'manufactureDate':manufactureDate,'prod_quantity':prod_quantity};
                selectedVendors[vendor+'#'+product][i]=temp;
              } else {
                alert("Please select diff date");
                manufactureDate=temp.manufactureDate;
                $('#manufactureDate_'+tr_index).val(manufactureDate);
                $('#trow_'+removeindex+' .manufactureDate_'+removeindex).html(manufactureDate);
                temp={'vendor':vendor,'product':product,'tr_index':tr_index,'manufactureDate':manufactureDate,'prod_quantity':prod_quantity};
                selectedVendors[vendor+'#'+product][i]=temp;
              }
              
            }              
          }
        }



        /*if(mfgDatechange==1){
          for (var i =0; i < selectedVendors[vendor+'#'+product].length; i++) {
            var temp=selectedVendors[vendor+'#'+product][i];
            if(temp.manufactureDate==manufactureDate && temp.tr_index!=tr_index){
              alert("Please Chenge The Mfg Date. which is already given for other product.");
               temp={'vendor':vendor,'product':product,'tr_index':tr_index,'manufactureDate':temp.manufactureDate,'prod_quantity':prod_quantity};
                  selectedVendors[vendor+'#'+product][i]=temp;
                  $('#manufactureDate_'+tr_index).val(temp.manufactureDate);
                  $('#trow_'+removeindex+' .manufactureDate_'+removeindex).html(temp.manufactureDate);
               /* if(temp.manufactureDate!=manufactureDate){
                  mfgDatechange=1;
                }
                if(mfgDatechange==0){
                  temp={'vendor':vendor,'product':product,'tr_index':tr_index,'manufactureDate':manufactureDate,'prod_quantity':prod_quantity};
                  selectedVendors[vendor+'#'+product][i]=temp;
                }    *.../          
            }
          }
        }  */      
        console.log("update Quatity");
        console.log(selectedVendors[vendor+'#'+product]);
    }


    $( document ).ready(function() {
        //$(".datepicker").datepicker({format:'dd-mm-yyyy',todayHighlight:true,autoclose: true,minDate:new Date()})


        $('body').on('focus',".datepicker", function(){
           $(this).datepicker({format:'dd-mm-yyyy',todayHighlight:true,autoclose: true,startDate:new Date()});
         });


       $(".number").keydown(function (e) {
        // Allow: backspace, delete, tab, escape, enter and .
        if ($.inArray(e.keyCode, [46, 8, 9, 27, 13, 110]) !== -1 ||
             // Allow: Ctrl+A, Command+A
            (e.keyCode === 65 && (e.ctrlKey === true || e.metaKey === true)) || 
             // Allow: home, end, left, right, down, up
            (e.keyCode >= 35 && e.keyCode <= 40)) {
                 // let it happen, don't do anything
                 return;
        }
        // Ensure that it is a number and stop the keypress
        if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
            e.preventDefault();
        }
    });


      $('.vendorDis,.addBtn').attr('disabled',true);


      $('.productSelect').change(function (){
        var prd=$('#product').val();
        var checkingQty=product_qty[prd];
        var productInfoSingle=productInfo[prd];
        console.log(checkingQty);
        console.log(productInfoSingle);
        $('#mulVal').html('(Q X'+(checkingQty==0?1:checkingQty)+')');

        if(!productInfoSingle[3]){
          $('.addBtn').attr('disabled',true);
          $('#mulValDown').html('<span style="color:red">Not Configured</span>');
        }else {
          $('#mulValDown').html('Configured Layout:'+productInfoSingle[3]);
        }

      });
      
      $('.addBtn').click(function (){

        console.log("selectedVendors");
        console.log(selectedVendors);
       // alert(selectedVendors);

        var prd=$('#product').val();          
        var checkingQty=product_qty[prd];
        var productInfoSingle=productInfo[prd];
       

        var cuQty=$('#prod_quantity').val();
        var accptqty=parseInt(cuQty%checkingQty)||0;
        //alert(accptqty);

        var prarent_avl=false;
        var childs_avl=false;
        var lay_prarent_avl=false;
        var lay_childs_avl=false;
        var error=0;
        var errorMsg='';

        if(!productInfoSingle[3]){
           error=1;
          errorMsg+='Please Configured with this product\n';
        } 


        if(checkingQty>1){
          prarent_avl=true;
          childs_avl=true;
        } else {
          childs_avl=true;
        }
        layoutArray=productInfoSingle[3].split(',');
        console.log("layoutArray");
        console.log(layoutArray);
/*        alert(layoutArray);
*/
        if(layoutArray.indexOf("1")!=-1){
           console.log("Index:1");
          lay_childs_avl=true;
        }
        if(layoutArray.indexOf("2")!=-1){
           console.log("Index:2");
          lay_childs_avl=true;
        }


        if(layoutArray.indexOf("3")!=-1){
           console.log("Index:3");
          lay_childs_avl=true;
          lay_prarent_avl=true;
        }

        if(layoutArray.indexOf("5")!=-1){
           console.log("Index:5");
          lay_childs_avl=true;
          lay_prarent_avl=true;
        }


        if(layoutArray.indexOf("4")!=-1){  
           console.log("Index:4");
          lay_prarent_avl=true;
        }

/*        if(productInfoSingle[3]==3||productInfoSingle[3]==5){
          lay_childs_avl=true;
          lay_prarent_avl=true;
        }

         if(productInfoSingle[3]==4){
          lay_prarent_avl=true;
        }
*/

        if(lay_childs_avl!=childs_avl){
          error=1;
          errorMsg+='Layout configuration mismatch\n';
/*           errorMsg+='productInfoSingle:'+productInfoSingle[3]+'checkingQty:'+checkingQty+'\n';
           errorMsg+='lay_childs_avl:'+lay_childs_avl+'\n';
           errorMsg+='childs_avl:'+childs_avl+'\n';
*/        }

        if(lay_prarent_avl!=prarent_avl){
          error=1;
          errorMsg+='Layout configuration mismatch\n';
/*           errorMsg+='productInfoSingle:'+productInfoSingle[3]+'checkingQty:'+checkingQty+'\n';
           errorMsg+='lay_prarent_avl:'+lay_prarent_avl+'\n';
           errorMsg+='prarent_avl:'+prarent_avl+'\n';
*/        }
/*
    error=1;
    errorMsg+='prarent_avl:'+prarent_avl+'\n';
    errorMsg+='prarent_avl:'+prarent_avl+'\n';
    errorMsg+='childs_avl:'+childs_avl+'\n';
    errorMsg+='lay_prarent_avl:'+lay_prarent_avl+'\n';
    errorMsg+='lay_childs_avl:'+lay_childs_avl+'\n';
*/
if($('#prod_quantity').val()>30000){
    error=1;
    errorMsg+='Please Enter Quatity lessthen 30000.\n';
}

        if(!($('#prod_quantity').val()!=0 && accptqty==0) || $('#prod_quantity').val()<=0){
          error=1;
          errorMsg+='Please Enter correct Quantity\n';
        } 


        
        if($('#manufactureDate').val()==''){
          error=1;
          errorMsg+='Please Enter Manufacture Date\n';
        } 


        if($('#product').val()==''){
          error=1;
          errorMsg+='Please select product \n';
        } 

        if($('#vendor').val()==''){
          error=1;
          errorMsg+='Please select vendor \n';
        } 

        if(error==1){
          alert(errorMsg);
          return 0;
        } else {

          tr_index++;
          tr_rowsrno++;



          var vendor = $('#vendor').val();
          var vendorName = $('#vendor option:selected').text();//$("#id option:selected").text();
          var product = $('#product').val();
          var manufactureDate = $('#manufactureDate').val();
          var prod_quantity = $('#prod_quantity').val();
          var productInfoSingle=productInfo[prd];
          if(selectedVendors[vendor+'#'+product]){
            for (var i =0; i < selectedVendors[vendor+'#'+product].length; i++) {
              var temp=selectedVendors[vendor+'#'+product][i];
                if(temp.manufactureDate==manufactureDate){
                tr_index=temp.tr_index;
                prod_quantity=parseInt(parseInt(prod_quantity)+parseInt(temp.prod_quantity));
                temp={'vendor':vendor,'product':product,'tr_index':tr_index,'manufactureDate':manufactureDate,'prod_quantity':prod_quantity};
                $('#Quantity_'+tr_index).val(prod_quantity);
                $('#trow_'+tr_index+' .Quantity_'+tr_index).html(prod_quantity);
                selectedVendors[vendor+'#'+product][i]=temp;
                return true;
              }
            }
            console.log(selectedVendors[vendor+'#'+product]);            
          } else 
          selectedVendors[vendor+'#'+product]=[]; 
            selectedVendors[vendor+'#'+product].push({'vendor':vendor,'product':product,'tr_index':tr_index,'manufactureDate':manufactureDate,'prod_quantity':prod_quantity});
            //selectedVendors[vendor+'#'+product]={'vendor':vendor,'product':product,'tr_index':tr_index,'manufactureDate':manufactureDate,'prod_quantity':prod_quantity};
             console.log("fianl array");
              console.log(selectedVendors[vendor+'#'+product]);
          $('.addedData').append(`<tr id="trow_`+tr_index+`">
                        <td id="inx_cell_`+tr_index+`">`+tr_rowsrno+`</td>
                        <td>`+productInfoSingle[2]+`</td>
                        <td>`+productInfoSingle[0]+`</td>
                        <td> <div class="nedit">
                        <input type="hidden" value="`+vendor+`" name="grid_userId[]" id="userId_`+tr_index+`"></div><div class="knonedit">`+vendorName+`</div>
                        </td>
                        <td><div class="nonedit manufactureDate_`+tr_index+`">`+manufactureDate+`</div>
                          <div class="edit">
                            <input type="text"  class="datepicker form-control " value="`+manufactureDate+`" name="grid_manufactureDate[]" id="manufactureDate_`+tr_index+`">
                          </div>
                        </td>
                        <td>
                        <div class="nonedit Quantity_`+tr_index+`">
                        `+cuQty+`
                        </div>
                        <div class="edit">
                        <input type="text" value="`+prod_quantity+`"  class="number form-control " name="grid_Quantity[]" id="Quantity_`+tr_index+`">
                        <input type="hidden" value="`+checkingQty+`" name="mul_Quantity[]" id="mulQuantity_`+tr_index+`">
                        </div>
                        </td>
                        <td>
                         
                          <input type="hidden" value="`+product+`" name="grid_Product[]" id="Product_`+tr_index+`">
                          
                           <div class="edit">
                           <button type="button" class="" onclick="updateRow('`+tr_index+`')"><span class="badge bg-light-blue"><i class="fa fa-check"></i></span></button></div>
                           </div>
                           <div class="nonedit">
                          <button type="button" class="" onclick="removeRow('`+tr_index+`')" data-index="`+tr_index+`"><span class="badge bg-red"><i class="fa fa-trash-o"></i></span></button>
                          <button type="button" class="" onclick="edit('`+tr_index+`')" data-index="`+tr_index+`"><span class="badge bg-light-blue"><i class="fa fa-pencil"></i></span></button>
                          </div>
                        </td>
                     </tr>`);
            $('.addedTable').show();
           //  $(".datepicker").datepicker({format:'dd-mm-yyyy',todayHighlight:true,autoclose: true,minDate:new Date()})
          
          //selectedVendors.push({'vendor':vendor,'product':product,'tr_index':tr_index,'manufactureDate':manufactureDate,'prod_quantity':prod_quantity});
         

          $('#product').val('');
          $('#product').selectpicker('refresh');  
          //$('#manufactureDate').val('').change();
          $('#prod_quantity').val('').change();
        }       

          
      });

     
      
      

      $('#prod_quantity').keyup(function(){
          $($(this).parent()).removeClass('hasError');
          $('.addBtn').attr('disabled',true);
          var prd=$('#product').val();          
          var checkingQty=product_qty[prd];
          var cuQty=$(this).val();
          var accptqty=parseInt(cuQty%checkingQty)||0;
        /*  alert('cutqty'+cuQty+'checkingQty'+checkingQty+'prd'+prd);
          alert(checkingQty);*/
          console.log('checkingQty'+checkingQty);
          console.log('accptqty'+accptqty);
          if(checkingQty!=0){
            if($(this).val!=0 && accptqty==0){
              $('.addBtn').removeAttr('disabled');
            } else {
              $($(this).parent()).addClass('hasError');
            }
          } else {
            $('.addBtn').removeAttr('disabled');
          }
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


          $('#manufactureDate').val('');
          $('#prod_quantity').val('');

        $('#product').selectpicker('refresh');        
      });

      function smallprint(){
        $('#smallprint').val('1');
        $('#add').click();
      }
      
      function printBtnClick(){
           $("#loading").show();
                  setTimeout(function(){
                      $("#qr_form :input").each(function(elem,em){
                   //   console.log(em);
                      $(em).val('');
                      });
                      $("#qty").val('').change();
                     $("#loading").hide();
                     //document.getElementById("qr_form").reset();
                  //  location.reload();
                  }, 7000);
      }

    $('#qr_form').bootstrapValidator({
        message: 'This value is not valid',
            feedbackIcons: {
                valid: 'glyphicon glyphicon-ok',
                invalid: 'glyphicon glyphicon-remove',
                validating: 'glyphicon glyphicon-refresh'
            },
            fields: {
                 qty: {
                    validators: {
                        notEmpty: {
                            message: 'The Quantity is required and can\'t be empty'
                        },
                         numeric:{
                             message : 'The Qty should be numeric'
                        },
                         remote: {
                            url: '/qrcode/checkqty',
                            type: 'POST',
                            data: function(validator, $field, value) {
                              //alert(value);
                                return {
                                    table_name: 'product_packages', 
                                    field_name: $('#qty').val(), 
                                    code: value, 
                                    product_id: $('#product_id').val(),
                                    pluck_id:'quantity'
                                };
                            },
                            delay: 1000,     // Send Ajax request every 2 seconds
                            message: 'The Qty should be multiples of Package qty.'
                        }                
                    }
                }
            }
      }).on('success.form.bv', function(event) {
      $("#loading").show();
      $.ajax({
             type: "POST",
             url: '/qrcode/save2',
             data: $(this).serialize(),
             success: function(msg) {

              console.log(msg);
              if(msg.Data==''){
                alert(msg.Message);
                 location.reload();
              } else {
                var link = document.createElement("a");
                console.log(link.download);
                link.download = msg.Data.substring(msg.Data.lastIndexOf('/')+1);
                link.href = msg.Data;
                link.click();
                location.reload();
                console.log(msg.Data);
              }
             }
         });
         return false;
        });
    });
    


function ClearFields() {

     document.getElementById("qty").value = "";
}
//});
</script>
  @stop