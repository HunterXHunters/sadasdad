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

  @section('script')
      
      {{HTML::script('jqwidgets/jqxcore.js')}}
      {{HTML::script('js/common-validator.js')}}
      {{HTML::script('js/jquery.validate.min.js')}}
      {{HTML::script('js/helper.js')}}
      {{HTML::script('jqwidgets/jqxbuttons.js')}}
      {{HTML::script('jqwidgets/jqxscrollbar.js')}}
      {{HTML::script('jqwidgets/jqxlistbox.js')}}
      {{HTML::script('jqwidgets/jqxdropdownlist.js')}}
      {{HTML::script('jqwidgets/jqxdropdownbutton.js')}}
      {{HTML::script('jqwidgets/jqxcolorpicker.js')}}
      {{HTML::script('jqwidgets/jqxwindow.js')}}
      {{HTML::script('jqwidgets/jqxeditor.js')}}
      {{HTML::script('jqwidgets/jqxtooltip.js')}}
      {{HTML::script('jqwidgets/jqxcheckbox.js')}}
      {{HTML::script('scripts/demos.js')}}
@stop
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/chosen/1.5.1/chosen.min.css">

    <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.js"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/chosen/1.5.1/chosen.jquery.min.js"></script>

<div class="box">
    <div class="box-header">
      <h3 class="box-title"><i class="fa fa-th"></i><strong>Create   </strong> Delivery </h3>
    </div>
    <div class="box-body">
        {{Form::open(array('url'=>'delivery/save','method'=>'post','id'=>'delivery_form','enctype'=>'multipart/form-data'))}}
      <div class="row">
                <div class="form-group col-sm-3">
                  <label>Document Type</label>
                    <div id="location">
                      <select class="form-control chosen" id="type" name="type" parsley-trigger="change" parsley-required="true" parsley-error-container="#selectbox">
                       <option value="">Please Select document type</option>
                        @foreach($types as $type)
                        <option value="{{$type->value}}" > {{$type->name}} - {{$type->label}}</option>
                      @endforeach
                      </select>
                    </div>
              </div>
                <div class="form-group col-sm-3">
                  <label>Supplying Plant</label>
                    <div id="location">
                      <select class="form-control chosen" id="from_location_id" name="from_location_id" parsley-trigger="change" parsley-required="true" parsley-error-container="#selectbox">
<!--                        <option value="">Please Select Location</option>
 -->                        @foreach($locations as $location)
                        <option value="{{$location->location_id}}" {{ ($locId == $location->location_id) ? 'selected="selected"' :'' }}> {{$location->location_name}}</option>
                      @endforeach
                      </select>
                    </div>
              </div>
                <div class="form-group col-sm-3">
                  <label>Issuing storage location</label>
                    <div id="location">
                      <select class="form-control chosen"  data-live-search="true" id="storage_loc" name="storage_loc" parsley-trigger="change" parsley-required="true" parsley-error-container="#selectbox" >
                       <option value="">Please Select storage location</option>
                       @foreach($issueStrLoc as $StrLoc)
                        <option value="{{$StrLoc->storage_location}}"> {{$StrLoc->storage_location}}</option>
                      @endforeach
                      </select>
                    </div>
              </div>
              <div class="form-group col-sm-3">
                  <label>Receiving Plant</label>
                    <div id="location">
                      <select class="form-control chosen selectPlant"  data-live-search="true" id="to_location_id" name="to_location_id" parsley-trigger="change" parsley-required="true" parsley-error-container="#selectbox" >
                       <option value="">Please Select receiving plant</option>
                        @foreach($tolocations as $location)
                        <option value="{{$location->location_id}}"> {{$location->location_name}}</option>
                      @endforeach
                      </select>
                    </div>
              </div>
               
              
            </div>
           <!--  <div class="row">   
              
          </div> -->
           <hr>
      <div id="autoUpdate">
           <div class="row">
            <!-- <div class="form-group col-sm-3">
                  <label>Receiving Storage Location</label>
                    <div id="location">
                      <select class="form-control chosen receivingSelect" id="to_sloc" name="to_sloc" parsley-trigger="change" parsley-required="true" parsley-error-container="#selectbox">
                       <option value="">Please Select receiving storage location</option>
                      </select>
                    </div>
                </div> -->

                <div class="form-group col-sm-3">
                  <label>Receiving Storage Location</label>
                    <div id="location">
                      <select class="form-control receivingSelect"  id="to_sloc" name="to_sloc"   required="required">
                       <option value="0">Please Select receiving storage location</option>
                      </select>
                    </div>
                </div>
            
              <div class="form-group col-sm-3">
                  <label>Item</label>
                    <div id="location">
                      <select class="form-control chosen" id="item" name="item" parsley-trigger="change" parsley-required="true" parsley-error-container="#selectbox">
                       <option value="">Please Select Item</option>
                        @foreach($products as $product)
                        <option value="{{$product->product_id}}" > {{$product->name}}</option>
                      @endforeach
                      </select>
                    </div>
                </div>
              <div class="form-group col-sm-2">
                  <label>Quantity in Cases</label>
                      <input type="number" class="form-control" min="1" id="qty" name="qty" onchange=UOMConversion()>
                      <div id="uom_text"></div>         
              </div>
              <div class="form-group col-sm-3">
                <label>Batch no</label>
                <input type="text" class="form-control"  id="bno" name="bno">
              </div>
              <div class="form-group col-sm-1">
                <button type="button" id="add_assign" class="btn btn-primary" style="margin-top:30px;">
                  <i class="fa fa-plus" style="cursor: pointer;"></i> Add
                </button>
              </div>
              <!-- <div class="form-group col-sm-1">
                                <label for="exampleInputEmail"></label>
                                <div class="input-group ">
                                    <div class="input-group-addon">
                                        <i class="fa fa-plus" id="add_assign" style="cursor: pointer;"></i>
                                    </div>
                                </div>
                            </div> -->
                
            </div>
            <div class="row">
              
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
                                                <th>Batch no</th>
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
                    <button type="button" class="btn btn-default pull-left " onclick=" location.href='/delivery/Sto'"> <i class="fa fa-times-circle"></i> Cancel</button>
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
      $(".chosen").chosen();
// $(function() {
//   $('.selectpicker').selectpicker();
// });

$(document).ready(function(){

  var number = document.getElementById('qty');
  number.onkeydown = function(e) {
    
    if(!((e.keyCode > 95 && e.keyCode < 106)
      || (e.keyCode > 47 && e.keyCode < 58) 
      || e.keyCode == 8)) {
        return false;
    }
}

 $('.selectPlant').change(function(){
  $('.receivingSelect').empty().append('<option value="0">Please wait</option>');
          $.ajax({
            type: "GET",
            url: '/getPlantStorageLoc/'+$(this).val(),
             success: function( data){
                console.log(data);
                $('.receivingSelect').empty().append('<option value="0">Please Select receiving storage location</option>');
                data=JSON.parse(data);
                // console.log(data);
                $.each( data, function( key, value ) {
                  $('.receivingSelect').append('<option value="'+value+'">'+key+'</option>');
                });
//                $('.UOMSelect').selectpicker('refresh');
              },
              error: function(data){
                alert('in error');
                  console.log(data);
              }
          });
      });

});

function UOMConversion(){
  $('#uom_text').show();
  var qty = $('#qty').val();
  var p_id = $('#item').val();
  var UOM = 'EA';
  // alert(qty + '--' + UOM + '--' + p_id);
  $('#uom_text').html('Please wait...');
  $.ajax({
    type:"GET",
    url:'/getConversion/'+qty+'/'+UOM+'/'+p_id,
    success:function( data){
      var msg = qty + ' Cases = ' + data + ' EA';
      $('#qty').val(data);
      $('#uom_text').html(msg);
    },
    error: function(data){
      $('#qty').val(0);
      $('#uom_text').html('Conversion not available');
      console.log(data);
    }
  });
  
}

//$(document).ready(function(){
$('#is_issued').change(function(){
if(this.checked)
$('#autoUpdate').fadeIn('slow');
else
$('#autoUpdate').fadeOut('slow');
});

// if(document.getElementById("first").value == document.getElementById("second").value){
//     alert('');
// }else
 
    $("#add").click(function() {
      var location = $("#to_location_id");
            var type = $("#type");
      var item = $("#item");

            if (location.val() == "") {
                //If the "Please Select" option is selected display error.
                alert("Please select receiving plant!");
                return false;
            }      
             if (type.val() == "") {
                //If the "Please Select" option is selected display error.
                alert("Please select an document type!");
                return false;
            }
            //   if (item.val() == "") {
            //     //If the "Please Select" option is selected display error.
            //     alert("Please select an to Item!");
            //     return false;
            // }
            if($('#data').length == 0)
            {
              alert("Please add item(s) first.");
              return false;
            }

    //else {
    $('#delivery_form').bootstrapValidator({
        message: 'This value is not valid',
            feedbackIcons: {
                valid: 'glyphicon glyphicon-ok',
                invalid: 'glyphicon glyphicon-remove',
                validating: 'glyphicon glyphicon-refresh'
            },
            fields: {
              to_location_id: {
                    validators: {
                        notEmpty: {
                            message: 'The Receiving Plant is required and can\'t be empty'
                        }                        
                    }
                },
                item: {
                    validators: {
                        notEmpty: {
                            message: 'The item is required and can\'t be empty'
                        }

                    }
                },
                type: {
                    validators: {
                        notEmpty: {
                            message: 'The document type is required and can\'t be empty'
                        }                
                    }
                },
                 // qty: {
                 //    validators: {
                 //        notEmpty: {
                 //            message: 'The Quantity is required and can\'t be empty'
                 //        },
                 //         numeric:{
                 //             message : 'The Qty should be numeric'
                 //        },                
                 //    }
                // }
            }
      }).on('success.form.bv', function(event) {
            
            $.post($(this).attr('actiofn'),$(this).serialize(),function(response) {        
            alert(response);
            alert("Successfully Added");
            });
            //return false;
        }) 
    //}
    //window.location.href = "purchase/index";
    });
    $(document).ready(function () {
      // $('#location_form').bootstrapValidator({
      //   message: 'This value is not valid',
      //       feedbackIcons: {
      //           valid: 'glyphicon glyphicon-ok',
      //           invalid: 'glyphicon glyphicon-remove',
      //           validating: 'glyphicon glyphicon-refresh'
      //       },
      //       fields: {
      //         location_id: {
      //               validators: {
      //                   notEmpty: {
      //                       message: 'The Location is required and can\'t be empty'
      //                   }                        
      //               }
      //           },
      //           parent_id: {
      //               validators: {
      //                   notEmpty: {
      //                       message: 'The PO is required and can\'t be empty'
      //                   }                        
      //               }
      //           },
      //           withdrawn: {
      //               validators: {
      //                   notEmpty: {
      //                       message: 'The IDs  Quantity is required and can\'t be empty'
      //                   }                        
      //               }
      //           },
      //           po_amount: {
      //               validators: {
      //                   notEmpty: {
      //                       message: 'The PO Amount is required and can\'t be empty'
      //                   }                        
      //               }
      //           }
      //       }
      // }).on('success.form.bv', function(event) {
            
      //       $.post($(this).attr('actiofn'),$(this).serialize(),function(response) {        
      //       alert(response);
      //       });
      //       //return false;
      //   })
  });

  $('#type').change(function(){
    if($("#type").val() == 30001)
         $("#bno").hide();
         else{
          $("#bno").show();
         }
});   
$('#add_assign').click(function(){
            var product_val = $('#item').val();
            var to_location = $('#to_location_id').val();
            var storage_loc = $('#to_sloc').val();
            var doc_type = $('#type').val();
            // alert(type);
            var type = $("#type");
            var issueStrLoc = $('#storage_loc').val();
            var product_text = $('#item option:selected').text();
            var qty_id = $('#qty').val();
            var qty_text = $('#qty').text();
            var b_noo=$('#bno').val();
            var b_no=$('#bno').text();

  $('#delivery_form #qty').val('');
  $('#delivery_form #bno').val('');

            if(type.val() =='') {
              //alert(type.val());
               alert("Please select an document type!");
            }
            else if(issueStrLoc == 0 || issueStrLoc == ''){
              alert('Please enter Issuing storage location');
            }
            else if(to_location == 0 || to_location == '')
            {
                alert('Please Select Receiving plant.');
            }
             else if(storage_loc == 0 || storage_loc == '')
            {
                alert('Please enter Receiving storage location.');
            }
            /*else if (type.val() ==" ") {
                //If the "Please Select" option is selected display error.
                alert("Please select an  Type!");
                //return false;
            }
            else if(type.val() >30001)
            {
               if(b_noo == 0 || b_noo== ''){
                 alert('Please enter batch number');
               }
            }*/
            
            else if(product_val == 0 || product_val == '')
            {
                alert('Please select Item.');
            }else if(qty_id == 0 || qty_id == '')
            {
                alert('Please fill qty.');
            }
            else if(type.val() >30001 && b_noo== '' ){
              alert('Please enter batch number');

            }
            
           
            
            else{
                var attributeSetElements = new Array();

                /*
                $('[id="product_text"]').each(function(){
                    attributeSetElements.push($(this).text());
                });
                */
                $('#assigntable > tr').each(function(){
                  //console.log("trrrrrr");
                  var prd = $(this).find('#product_text').html();
                  var batch = $(this).find('#b_no').html();
                  /*console.log($(this));
                  console.log("======");
                  console.log(prd+"===="+batch);*/
                   attributeSetElements.push(''+prd+"##"+batch+'');
                });
               /* $('[id="b_noo"]').each(function(){
                    attributeSetElements.push($(this).text());
                });*/
                var temp;
                //temp = product_text+'##'+qty_text;
                temp = product_text+'##'+b_noo;
                // alert(attributeSetElements);
                console.log("attributeSetElements");
                console.log(attributeSetElements);
                if(attributeSetElements.length > 0 && $.inArray(temp, attributeSetElements) >= 0)
                {
                    alert('This Item already added.');
                }else{            
                    var jsonArg = new Object();
                    jsonArg.product_id = product_val;
                    jsonArg.qty_id = qty_id;
                    jsonArg.b_noo = b_noo;
                    
                    var hiddenJsonData = new Array();
                    hiddenJsonData.push(jsonArg);
                    $("#assign_data").append('<tr><td scope="row" id="product_text">' + product_text + '</td><td id="qty_text">' + qty_id+'</td><td id="b_no">' + b_noo
                            + '</td><td><a href="javascript:void(0);" class="check-toggler" id="remCF"><span class="badge bg-red"><i class="fa fa-trash-o"></i></span></a><input id="data" type="hidden" name="data[]" value=' + "'" + JSON.stringify(jsonArg) + "'" + ' /></td></tr>');
                }
               //$('#qty').val('');
            }
        });
$("#assign_data").on('click', '#remCF', function () {
            $(this).parent().parent().remove();
        });

$(document).ready(function()
{
var obj = $("#dragandrophandler");
obj.on('dragenter', function (e) 
{
  e.stopPropagation();
  e.preventDefault();
  $(this).css('border', '2px solid #0B85A1');
});
obj.on('dragover', function (e) 
{
   e.stopPropagation();
   e.preventDefault();
});
obj.on('drop', function (e) 
{
  
   $(this).css('border', '2px dotted #0B85A1');
   e.preventDefault();
   var files = e.originalEvent.dataTransfer.files;

   //We need to send dropped files to Server
   handleFileUpload(files,obj);
});
$(document).on('dragenter', function (e) 
{
  e.stopPropagation();
  e.preventDefault();
});
$(document).on('dragover', function (e) 
{
  e.stopPropagation();
  e.preventDefault();
  obj.css('border', '2px dotted #0B85A1');
});
$(document).on('drop', function (e) 
{
  e.stopPropagation();
  e.preventDefault();
});

});

</script>
  @stop
