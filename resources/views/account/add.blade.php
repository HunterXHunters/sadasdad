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


<div class="box">
    <div class="box-header">
      <h3 class="box-title"><i class="fa fa-th"></i><strong>Create   </strong> Label Orders </h3>
    </div>
    <div class="box-body">
        {{Form::open(array('url'=>'account/save','method'=>'post','id'=>'account_form','enctype'=>'multipart/form-data'))}}
      <div class="row">
                <div class="form-group col-sm-4">
                  <label>Vendor Name</label>
                    <div id="Vendor">
                      <select class="form-control chosen" id="vendor_id" name="vendor_id" parsley-trigger="change" parsley-required="true" parsley-error-container="#selectbox">
                       <option value="">Please Select Vendor</option>
                        @foreach($vendor as $vendorlocation)
                        <option value="{{$vendorlocation->location_id}}"> {{$vendorlocation->location_name}}</option>
                      @endforeach
                      </select>
                    </div>
              </div>
              <div class="form-group col-sm-4">
                  <label>PO Number</label>
                    <div id="location">
                      <input type="text" class="form-control" placeholder="PO Number" name="po_number" id="po_number">
                    </div>
              </div>
              <div class="form-group col-sm-4">
                  <label>Date</label>
                    <input type="text" class="form-control" id="datepicker" name="date" autocomplete="off">
                      <!-- <div class="input-group-addon"> -->
                        <!-- <span class="glyphicon glyphicon-th"></span> -->
                       <!-- </div> -->
                    </div>
                 </div>
           <!--  <div class="row">   
              
          </div> -->
           <hr>
      <div id="autoUpdate">
           <div class="row">
              <div class="form-group col-sm-4">
                  <label>Label Type</label>
                    <div id="location">
                      <select class="form-control chosen" id="label_type" name="lebel_type" parsley-trigger="change" parsley-required="true" parsley-error-container="#selectbox">
                       <option value="">Please Select Label</option>
                         @foreach($labels as $label)
                        <option value="{{$label->id}}"> {{$label->label_type_name}}</option>
                      @endforeach
                      </select>
                    </div>
                </div>

              <div class="form-group col-sm-4">
                  <label>Quantity</label>
                      <input type="text" class="form-control"  id="qty" name="qty">         
              </div>
               <div class="form-group col-sm-4">
                  <label>PO Documnet</label>
                      <input type="file" id="file_documnet" name="file_documnet">         
              </div>
                <div class="form-group col-sm-1">
                                <label for="exampleInputEmail"></label>
                                <div class="input-group ">
                                    <div class="input-group-addon">
                                        <i class="fa fa-plus" id="add_assign" style="cursor: pointer;"></i>
                                    </div>
                                </div>
                            </div>
                  @if($addlabelaccess == 1)
                <div class="col-sm-5">
                  <button type="button"  class="btn btn-primary" data-toggle="modal" data-target="#labeltypes">Add Label type</button>
                </div>
              @endif
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
                                                <th>Label Type</th>
                                                <th>Quantity</th>
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
                    <button type="button" class="btn btn-default pull-left " onclick=" location.href='/account/index'"> <i class="fa fa-times-circle"></i> Cancel</button>
                  </div>
              </div>
            </div>
         </div>
        {{Form::close()}}
    </div>




    <div class="modal fade" id="labeltypes" tabindex="-1" role="dialog" aria-labelledby="basicvalCode" aria-hidden="true">
        <div class="modal-dialog wide">
            <div class="modal-content">
                <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                        <h4 class="modal-title" id="basicvalCode">Create New Label Type</h4>
                </div>
                <div class="modal-body">

                    {{ Form::open(array('url' => '', 'id' => 'save_type_name_form'))}}

                    <div class="row">    
                        <div class="form-group col-sm-4">
                            <label>Add Label type</label>
                            <div id="location">
                                <input type="text" class="form-control" placeholder="ex:3 X10 Sheet (30-Code For Sheet)-size 1 X 1" name="type_label_name" id="type_label_name">
                            </div>
                        </div>
                         <div class="form-group col-sm-4">
                            <label>Number of labels per sheet</label>
                            <div id="location">
                                <input type="text" class="form-control" placeholder="ex:30" name="type_label_qty" id="type_label_qty">
                            </div>
                        </div>
                         <div class="form-group col-sm-4">
                            <label>Number of codes</label>
                            <div id="location">
                                <input type="text" class="form-control" placeholder="ex:10" name="type_label_size" id="type_label_size">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12 text-center">
                            <div class="form-group">
                            <button type="button" class="btn green-meadow" id="add_type_name">Add</button>
                            </div>
                        </div>                   
                    </div>
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>





    <div class="box-footer">
    </div>  
  </div>

  <script type="text/javascript">
   $(function() {
    $( "#datepicker" ).datepicker({ dateFormat: "yy-mm-dd" });
    $("#datepicker").on("change",function(){
        var selected = $(this).val();
        //alert(selected);
    });
});

       $('#add_type_name').click(function () {
        var frmData = $('#save_type_name_form').serialize();
        var label_type_name = $('#type_label_name').val();
        var label_sheet_qty = $('#type_label_qty').val();
        var number_codes = $('#type_label_size').val();
        if(label_type_name == ""){
            alert("add label type");
        }else if(label_sheet_qty == ""){
          alert("add number of labels");
        }else if(number_codes == ""){
          alert("add number of codes");
        } else{
        $.ajax({
        type: "POST",
        url: '/account/savelabelname',
        data: frmData, 
        success: function (respData)
        {
            var d =JSON.stringify(respData);
            var dd =JSON.parse(d);
            for (var i = 0; i < dd.length; i++) {

$('#label_type').append($("<option></option>").text(dd[i].label_type_name + ' '+ (dd[i].codes_qty+ '-'+"Code For Sheet") + '-'+"size "+dd[i].codes_size).val(dd[i].id));
            };
            $('#labeladdtype').modal('toggle');
        }
    });

        }
    
    });





// if(document.getElementById("first").value == document.getElementById("second").value){
//     alert('');
// }else
 
    $("#add").click(function() {
      var vendor = $("#vendor_id");
      //       var label = $("#label_type");
      // //var qty = $("#qty");
        //var ponumber = $("#po_number"); 
            if (vendor.val() == "") {
                //If the "Please Select" option is selected display error.
                alert("Please select an vendor Location!");
                return false;
            }
       
      //        if (label.val() == "") {
      //           //If the "Please Select" option is selected display error.
      //           alert("Please select an Label Type!");
      //           return false;
      //       }
            
    
    $('#account_form').bootstrapValidator({
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
                        },
                         numeric:{
                             message : 'The PO should be numeric'
                        },                         
                    }
                },
                // vendor_id: {
                //     validators: {
                //         notEmpty: {
                //             message: 'The item is required and can\'t be empty'
                //         }

                //     }
                // },
                // type: {
                //     validators: {
                //         notEmpty: {
                //             message: 'The type is required and can\'t be empty'
                //         }                
                //     }
                // },
                 qty: {
                    validators: {
                        notEmpty: {
                            message: 'The Quantity is required and can\'t be empty'
                        },
                         numeric:{
                             message : 'The Qty should be numeric'
                        },                
                    }
                }
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
   
$('#add_assign').click(function(){
            var label_val = $('#label_type').val();
            var label_text = $('#label_type option:selected').text();
            var qty_id = $('#qty').val();
            var qty_text = $('#qty').text();
            
            if(label_val == 0 || label_val == '')
            {
                alert('Please select labelType.');
            }else if(qty_id == 0 || qty_id == '')
            {
                alert('Please fill qty.');
            }else{
                var attributeSetElements = new Array();
                $('[id="label_text"]').each(function(){
                    attributeSetElements.push($(this).text());
                });
                var temp;
                //temp = product_text+'##'+qty_text;
                temp = label_text;
                if(attributeSetElements.length > 0 && $.inArray(temp, attributeSetElements) >= 0)
                {
                    alert('This labelType already added.');
                }else{            
                    var jsonArg = new Object();
                    jsonArg.label_id = label_val;
                    jsonArg.qty_id = qty_id;
                    var hiddenJsonData = new Array();
                    hiddenJsonData.push(jsonArg);
                    $("#assign_data").append('<tr><td scope="row" id="label_text">' + label_text + '</td><td id="qty_text">' + qty_id + '</td><td><a href="javascript:void(0);" class="check-toggler" id="remCF"><span class="badge bg-red"><i class="fa fa-trash-o"></i></span></a><input type="hidden" name="data[]" value=' + "'" + JSON.stringify(jsonArg) + "'" + ' /></td></tr>');
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