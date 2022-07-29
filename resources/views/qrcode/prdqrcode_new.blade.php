@extends('layouts.default')
@extends('layouts.header')
@extends('layouts.sideview')
@section('content')
@section('style')
    {{HTML::style('jqwidgets/styles/jqx.base.css')}}    
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
      <h3 class="box-title"><i class="fa fa-th"></i><strong>Print   </strong> Label </h3>
    </div>
    <div class="box-body">
        {{Form::open(array('method'=>'post','id'=>'qr_form','enctype'=>'multipart/form-data'))}}
      <div class="row">
                <div class="form-group col-sm-6">
                    <div id="product">
                      <div class="form-group col-sm-6">
                            <label>Product</label>
                      <select class="form-control" id="product_id" name="product_id" parsley-trigger="change" parsley-required="true" parsley-error-container="#selectbox" onchange="fetch_select(this.value);">
                       <option value="">Please Select Product</option>
                        @foreach($prdArray as $prd)
                        <option value="{{$prd->product_id}}"> {{$prd->name}}</option>
                      @endforeach
                      </select>
                    </div>
                     <div class="form-group col-sm-6">
                       <label>Primaries Quantity</label>
                      <input type="text" class="form-control"  id="qty" name="qty" required >         
                  </div>
                    </div>
                <div id="attr">
 
                  </div>
              <div class="form-group col-sm-6" id="attr3">

              </div>
              <div class="form-group">
                <label for="smallpage">Small Page</label>
                <input type="checkbox" id="smallpage" name="smallpage">

                <label for="excelExport">Excel </label>
                <input type="checkbox" id="excelExport" name="excelExport">
              </div>
             
<!--               <div class="form-group col-sm-6">
 -->             <div class="form-group col-sm-2 ">
                  <button type="submit" id="add" class="btn btn-primary" ><i class="fa fa-hdd-o"></i> Print</button>

                 
                   
                </div>
                 <div class="form-group col-sm-2">
                    <button type="button" class="btn btn-default" onclick=" location.href='/qrcode/prdqrcode'"> <i class="fa fa-times-circle"></i> Cancel</button>
                  </div>
                  <div class="form-group col-sm-2">
                    <button type="button"  class="btn btn-default" onclick="ClearFields();"> <i class="fa fa-times-circle"></i> Clear</button>
                  </div>
<!--                   </div>
 -->              </div>
               <div class="form-group col-sm-6" id="attr2">
             </div>
        </div>
        {{Form::close()}}
    </div>
    <div class="box-footer">
    </div>  
  </div>

  <script type="text/javascript">
    $( document ).ready(function() {
    //$("#add").click(function() {
//       $("#product_id").validate({
//     keypress : true
// });
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

      /*    window.open("/test");
         // window.open(msg.Data,'_blank');
        //  window.open(msg.Data);
          console.log(msg);
         // window.open('http://vguard.esealinc.com:555'+msg.Data, '_blank');
         /* var data=JSON.parse(msg.trim());
          alert("ajax done");*/
          //location.reload();*/
        //  $("#loading").hide();
        //  console.log(data);
         }
     });
//event.preventDefault();
            return false;


           // $("#loading").show().delay(5000).fadeOut();



            //$("#loading").show();
           //  $.post($(this).attr('actiofn'),$(this).serialize(),function(response) {        
           //  alert(response);
           // if(response['Status'] == 1){
           //  $("#loading").hide();
           //  alert(response['Message']);
           //  window.location.href="qrcode/prdqrcode";
           // }
           // else{
           //  $("#loading").hide();
           // alert(response['Message']);
           // }
           //  //alert("Successfully Added");
           //  });
    //         $.ajax({
    //     type: "POST",
    //     url: '/qrcode/save',
    //     data: $(this).serialize(),
    //     success: function(msg) {
    //       alert(msg['Data']);
    //        if(msg['Status'] == 1){
    //        $("#loading").hide();
    //     alert(msg['Message']);
    //     window.location.href='qrcode/prdqrcode';
    //              }
    //  else{
    //         $("#loading").hide();
    //        alert(response['Message']);
    //        }
    //     }
    // });
            //return false;
        });
    //}
    });
    
function fetch_select(val)
{
   $("#loading").show();
 // alert(val);
//var qty = $('#qty').val();
//alert(qty);
if(val){
  var qty=$('#qty').val('');
}
 $.ajax({
 type: 'get',
 url: '/qrcode/getAttributes/'+val,
 data: {get_option:val},
 success: function (response) {
 // alert(response);
 var result = JSON.stringify(response);
 var json = JSON.parse(result);
  //alert(result);
  //if(result){
    //$("#attr").html(result);
  //}
   var d = new Date(); // 1-Feb-2011
          var today_date =
              ("0" + d.getDate()).slice(-2) + "-" +
              ("0" + (d.getMonth() + 1)).slice(-2) + "-" + 
              d.getFullYear();
              //alert(today_date);
  var finalresult=[];
  var finalresult2=[];
  var prdArry=[];
  var matcodeArry=[];
 $.each(response, function(key, val) {
          var text='';
          var text2='';
          var prd='';
          var matcode='';
          //text += "<div class='row'>";
          if(val.input_type == 'text'){
         text += "<div class='form-group col-sm-6'><label>" + val.name +":"+ "</label><input style='width:275px;height:35px; type='text' name='" + val.name + "' value='" + val.default_value + "'/></div>";
            }
            
          if(val.input_type == 'hidden'){
            // text += "<div class='form-group col-sm-6'>" + val.name +":"+"</div>";
          }
           if(val.input_type == 'date'){
         text += "<div class='form-group col-sm-6'><label>" + val.name +":"+ "</label><input style='width:275px;height:35px; type='text' name='" + val.name + "' value='" + today_date + "'/></div>";
            }
          if(val.input_type == 'select'){
            $.each(response.options, function(key, value) {
           //text = val.name +":"+ "<input type='text' name='" + val.name + "' value='" + val.default_value + "'/>";
           $('#attr3').append('<option value="' + value.default_value + '">' + value.option_value + '</option>');
            });
          }
          if(val.input_type == 'inherit'){
          text += "<div class='form-group col-sm-6'><label>" + val.name +":"+ "</label><input style='width:275px;height:35px; type='text' name='" + val.name + "' value='" + val.default_value + "'/></div>";
            }          
             if(val.input_type == 'quantity'){
          text += "<div class='form-group col-sm-6'>Package Quantity: <b>"+  val.quantity + "</b></div>";
            }  
             if(val.input_type == 'image'){
          text2 += val.image;
            }  
             if(val.input_type == 'prdName'){
          prd +=  "<div class='form-group col-sm-6'><label>Product Name:</label> <b>" + val.prdName + "</b></div>";
            } 
             if(val.input_type == 'matcode'){
          matcode += "<div class='form-group col-sm-6'><label>Material Code: </label><b>" + val.matcode + "</b></div>";
            } 
           //text += "</div>";
        //alert('hello');
        finalresult.push(text);
        finalresult2.push(text2);
        prdArry.push(prd);
        matcodeArry.push(matcode);
    });
 $("#attr").html(finalresult.join(""));
  $("#attr2").html(finalresult2.join(""));
    $("#prdname").html(prdArry.join(""));
  $("#matcode").html(matcodeArry.join(""));
   $("#loading").hide();
   $("#add").attr("disabled", "disabled");
  

}
 });
}

function ClearFields() {

     document.getElementById("qty").value = "";
}
//});
</script>
  @stop