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
@stop

<link href="http://cdn.rawgit.com/davidstutz/bootstrap-multiselect/master/dist/css/bootstrap-multiselect.css"
    rel="stylesheet" type="text/css" />
<script src="http://cdn.rawgit.com/davidstutz/bootstrap-multiselect/master/dist/js/bootstrap-multiselect.js"
    type="text/javascript"></script>
<link rel="styleheet" href="https://cdn.datatables.net/1.10.13/css/jquery.dataTables.min.css"/>

<script type="text/javascript" src="https://cdn.datatables.net/1.10.13/js/jquery.dataTables.min.js"></script>
 @if (Session::has('message'))
  <div class="alert alert-info">{{ Session::get('message') }}</div>
 @endif
 
<div class="box">
    <div class="box-header">
      <h3 class="box-title"><i class="fa fa-th"></i><strong>Create   </strong>Invoice </h3>
    </div>
    <div class="box-body">
        {{Form::open(array('url'=>'vendorinvoice/save','method'=>'post','id'=>'invoice_form','enctype'=>'multipart/form-data'))}}

        <div class="row">
             <div class="form-group col-sm-3">
               <label>VGIL PO Number*</label>
                  <div class="input-group">
                    <span class="input-group-addon addon-red"><i class="fa fa-caret-square-o-right"></i></span>
                    <input type="text" class="form-control" maxlength="500" id="po_number" name="po_number" placeholder="PO number">          
                </div> 
            </div>
             <div class="form-group col-sm-3">
               <label>Invoice Number*</label>
                  <div class="input-group">
                    <span class="input-group-addon addon-red"><i class="fa fa-caret-square-o-right"></i></span>
                    <input type="text" class="form-control" maxlength="500" id="invoice_number" name="invoice_number" placeholder="Invoice Number">          
                </div> 
            </div>
             <div class="form-group col-sm-3">
               <label>Invoice Date</label>
                  <div class="input-group">
                    <span class="input-group-addon addon-red"><i class="fa fa-caret-square-o-right"></i></span>
                    <input type="text" class="form-control" maxlength="500" id="datepicker1" name="invoice_date" placeholder="Invoice Date">          
                </div> 
            </div>
             <div class="form-group col-sm-3">
               <label>BL(Shipping Reference) No</label>
                  <div class="input-group">
                    <span class="input-group-addon addon-red"><i class="fa fa-caret-square-o-right"></i></span>
                    <input type="text" class="form-control" maxlength="500" id="bill_number" name="bill_number" placeholder="Bill Number">          
                </div> 
            </div>
           
          </div>
          <div class="row">
            <div class="form-group col-sm-3">
               <label>BL(Shipping Reference) Date</label>
                  <div class="input-group">
                    <span class="input-group-addon addon-red"><i class="fa fa-caret-square-o-right"></i></span>
                    <input type="text" class="form-control" maxlength="500" id="datepicker" name="bill_date" placeholder="Bill Date">          
                </div> 
            </div>
          </div>
          <div class="row">
             <div class="form-group col-sm-4">
                 <div id="attr">

                </div>
            </div>
             
        
          </div>
           <div id="autoUpdate">
           <div class="row">
            <div class="form-group col-sm-4">
                  <label>Material</label>
                   <div class="input-group">
                     <span class="input-group-addon addon-red"><i class="fa fa-caret-square-o-right"></i></span>
                        <div  id="selectdiv">
                      <select class="selectpicker" data-live-search="true" id="item" name="item">
                       <option value="">Please Select Material</option>
                        @foreach($products as $product)
                        <option value="{{$product->product_id}}" > {{$product->name}}</option>
                      @endforeach
                          </select>
                    </div> 
                </div>       
            </div> 
             <div class="form-group col-sm-4">
               <label>Quantity*</label>
                <div class="input-group">
                <span class="input-group-addon addon-red"><i class="fa fa-caret-square-o-right"></i></span>
                <input type="text" class="form-control select2" maxlength="500" id="qty" name="qty" min="0" placeholder="Quantity" onkeypress="return isNumber(event)">          
                </div> 
            </div>
             <div class="form-group col-sm-1">
                                <label for="exampleInputEmail"></label>
                                <div class="input-group ">
                                    <div class="input-group-addon">
                                        <i class="fa fa-plus" id="add_assign" style="cursor: pointer;"></i>
                                    </div>
                                </div>
                            </div>   
         </div>
          <div class="row">
                            <section class="tile">
                                <div class="panel panel-default">
                                    <!-- Default panel contents -->
                                    <div class="panel-heading"></div>
                                    <!-- Table -->
                                    <table class="table" id="assign_data">
                                        <thead>
                                            <tr>
                                                <th>Item</th>
                                                <th>Qty</th>
                                                <th style="width: 30px;">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody id="assigntable">
                                        </tbody>
                                    </table>
                                </div>
                            </section>
                        </div>
       </div>
        <div class="row">
          <div class="form-group col-sm-6">
            </div>
          <div class="form-group col-sm-6">
            <div class="form-group col-sm-6 pull-right">
              <div class="col-sm-6">
                    <button type="submit" id="add" class="btn btn-primary pull-right"><i class="fa fa-hdd-o"></i> Save</button>
                  </div>
                <div class="form-group col-sm-6 ">
                    <button type="button" class="btn btn-default pull-left " onclick=" location.href='/vendorinvoice/index'"> <i class="fa fa-times-circle"></i> Cancel</button>
                  </div>
              </div>
            </div>
         </div>

        {{Form::close()}}
    </div>
    <div class="box-footer">
    </div>  
  </div>

    
  <script type="text/javascript">
     
   $(function() {
    $( "#datepicker" ).datepicker({ dateFormat: "dd-mm-yy" });
    $("#datepicker").on("change",function(){
        var selected = $(this).val();
        //alert(selected);
    });
});
    $(function() {
    $( "#datepicker1" ).datepicker({ dateFormat: "dd-mm-yy" });
    $("#datepicker1").on("change",function(){
        var selected = $(this).val();
        //alert(selected);
    });
});
$('#is_issued').change(function(){
if(this.checked)
$('#autoUpdate').fadeIn('slow');
else
$('#autoUpdate').fadeOut('slow');
});

 
    $("#add").click(function() {
      var location = $("#to_location_id");
            var type = $("#type");
             var invoice_no = $("#invoice_number").val();
             var po_number = $("#po_number").val();
      var item = $("#item");

            if (location.val() == "") {
                //If the "Please Select" option is selected display error.
                alert("Please select an to Location!");
                return false;
            }
       
           
              if (item.val() == "") {
                //If the "Please Select" option is selected display error.
                alert("Please select an to Material!");
                return false;
            }
    //          $.ajax({
    //     type: "POST",
    //     url: '/vendorinvoice/invoice_number_uniquevalidation/'+ po_number +'/'+invoice_no,
    //     data: $(this).serialize(),
    //     success: function(msg) {
    //       //alert(JSON.stringify(msg));
    //       if(msg['status'] == false){
    //         alert('Invoice Number Alredy Exists');
    //         return false;
    //       }
    //     }
    // });

    //else {
    $('#invoice_form').bootstrapValidator({
        message: 'This value is not valid',
            feedbackIcons: {
                valid: 'glyphicon glyphicon-ok',
                invalid: 'glyphicon glyphicon-remove',
                validating: 'glyphicon glyphicon-refresh'
            },
            fields: {
               po_number: {
                    validators: {
                        notEmpty: {
                            message: 'The PO is required and can\'t be empty'
                        }                        
                    }
                },
              invoice_number: {
                    validators: {
                        notEmpty: {
                            message: 'The Invoice is required and can\'t be empty'
                        }
                                         
                    }
                }
              
            }
      }).on('success.form.bv', function(event) {
            $('button').attr('disabled', 'disabled');
            
     $.ajax({
        type: "POST",
        url: '/vendorinvoice/save',
        data: $(this).serialize(),
        success: function(msg) {
          //alert(JSON.stringify(msg));
           if(msg['status'] == true){
          //   alert('Invoice Number Alredy Exists');
          // }
          // else{
        alert('Succesfully added');
        window.location.href='http://vguard.esealinc.com:555/vendorinvoice/index';
      //}
    }
        }
    });
        }).validate({
            submitHandler: function (form) {
                return false;
            }
        });
    });

$(document).ready(function(){
  
  $('#to_location_id').multiselect({
           enableFiltering:true,
        maxHeight: 300,
        buttonWidth:'300px',
        //overflow:'auto',

            //numberDisplayed: 0,
        enableCaseInsensitiveFiltering: true,
         onChange: function(option, checked) {
                // Get selected options.

                var selectedOptions = $('#to_location_id option:selected');
 
                
            }

        });
$('#item').multiselect({
    //columns: 1,
    nonSelectedText :'Select Material',
   includeSelectAllOption: true,
           enableFiltering:true,
        maxHeight: 300,
        buttonWidth:'300px',
        overflow:'auto',

            //numberDisplayed: 0,
        enableCaseInsensitiveFiltering: true,
         onChange: function(option, checked) {
                // Get selected options.
                var selectedOptions = $('#item option:selected');
 
               
            }
    //.multiselect('selectAll', false);
        });
 $('#from_location_id').multiselect({
    //columns: 1,
    nonSelectedText :'Select Pallet',
   includeSelectAllOption: true,
           enableFiltering:true,
            //numberDisplayed: 0,
        enableCaseInsensitiveFiltering: true,
        maxHeight: 300,
        buttonWidth:'300px',
        overflow:'auto'
        });
    });

    
         $('#add_assign').click(function(){
            var product_val = $('#item').val();
            var product_text = $('#item option:selected').text();
            var qty_id = $('#qty').val();
            var qty_text = $('#qty').text();
            
            if(product_val == 0 || product_val == '')
            {
                alert('Please select Item.');
            }else if(qty_id == 0 || qty_id == '')
            {
                alert('Please fill qty.');
            }else{
                var attributeSetElements = new Array();
                $('[id="product_text"]').each(function(){
                    attributeSetElements.push($(this).text());
                });
                var temp;
                //temp = product_text+'##'+qty_text;
                temp = product_text;
                if(attributeSetElements.length > 0 && $.inArray(temp, attributeSetElements) >= 0)
                {
                    alert('This Item already added.');
                }else{            
                    var jsonArg = new Object();
                    jsonArg.product_id = product_val;
                    jsonArg.qty_id = qty_id;
                    var hiddenJsonData = new Array();
                    hiddenJsonData.push(jsonArg);
                    $("#assign_data").append('<tr><td scope="row" id="product_text">' + product_text + '</td><td id="qty_text">' + qty_id
                            + '</td><td><a href="javascript:void(0);" class="check-toggler" id="remCF"><span class="badge bg-red"><i class="fa fa-trash-o"></i></span></a><input type="hidden" name="data[]" value=' + "'" + JSON.stringify(jsonArg) + "'" + ' /></td></tr>');
                    //alert(JSON.stringify(jsonArg));
                }
               $('#qty').val('');
            }
        });
$("#assign_data").on('click', '#remCF', function () {
            $(this).parent().parent().remove();
        });

function isNumber(evt) {
    evt = (evt) ? evt : window.event;
    var charCode = (evt.which) ? evt.which : evt.keyCode;
    if (charCode > 31 && (charCode < 48 || charCode > 57)) {
        return false;
    }
    return true;
}


function fetch_select(val)
{
 // alert(val);
 $.ajax({
 type: 'get',
 url: '/delivery/getLocationDetails/'+val,
 data: {get_option:val},
 success: function (response) {
//  alert(response);
 var result = JSON.stringify(response);
 var json = JSON.parse(result);
  var finalresult=[];  
 $.each(response, function(key, val) {
          var text='';
         
          //text += "<div class='row'>";
          if(val.locationDetails){
         text += "<div class='form-group col-sm-6'><label>Location Details :</label><div>"+ val.locationDetails +"</div></div>";
            }
    finalresult.push(text);
       
    });
      //finalresult.push(json);
 $("#attr").html(finalresult.join(""));
 
}
 });
}


</script>
  @stop