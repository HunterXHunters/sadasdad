@extends('layouts.default')
@extends('layouts.header')
<style type="text/css">
    .row{margin-left:-10px; }
    .jqx-grid-cell-left-align { padding-left: 5px;}
    .btn-primary[disabled], .btn-primary[disabled]:hover{
        background-color:#26B99A;
        border:1px solid #169F85;
      }
    .yellow {
        color: black\9;
        background-color: yellow\9;
        text-decoration: underline;
        cursor: pointer;
    }
    .yellow:not(.jqx-grid-cell-hover):not(.jqx-grid-cell-selected), .jqx-widget .yellow:not(.jqx-grid-cell-hover):not(.jqx-grid-cell-selected) {
            color: black;
            background-color: yellow;
    }
    .red {rgba(61, 158, 61, 0.99);
        color: black\9;
        background-color: #9B3939;
        text-decoration: underline;
        cursor: pointer;
        color: #fff;
    }
    .red:not(.jqx-grid-cell-hover):not(.jqx-grid-cell-selected), .jqx-widget .red:not(.jqx-grid-cell-hover):not(.jqx-grid-cell-selected) {
            color: black;
            background-color: #9B3939;
            color: #fff;
    }
    .green {
        color: black\9;
        background-color: #4B9A4B;
        text-decoration: underline;
        cursor: pointer;
        color: #fff;
    }
    .green:not(.jqx-grid-cell-hover):not(.jqx-grid-cell-selected), .jqx-widget .green:not(.jqx-grid-cell-hover):not(.jqx-grid-cell-selected) {
            color: black;
            background-color: #4B9A4B;
            color: #fff;
    }
    .orange {
        color: black\9;
        background-color: #FFD700;
        color: #fff;
    }
    .orange:not(.jqx-grid-cell-hover):not(.jqx-grid-cell-selected), .jqx-widget .orange:not(.jqx-grid-cell-hover):not(.jqx-grid-cell-selected) {
            color: black;
            background-color: #FFD700;
            color: #fff;
    }
    .jqx-popover{width:300px;}  
    canvas {
        -moz-user-select: none;
        -webkit-user-select: none;
        -ms-user-select: none;
    }  
    .csschanges{
        padding-top:20px;
    }
    #labelIdsWithQty {
    font-family: "Trebuchet MS", Arial, Helvetica, sans-serif;
    border-collapse: collapse;
}

#labelIdsWithQty td, #labelIdsWithQty th {
    border: 1px solid black;
    padding: 8px;
}

#labelIdsWithQty tr:nth-child(even){background-color: #f2f2f2;}

#labelIdsWithQty tr:hover {background-color: #ddd;}

#labelIdsWithQty th {
    padding-top: 12px;
    padding-bottom: 12px;
    text-align: left;
    background-color: #4CAF50;
    color: white;
}
</style>
@extends('layouts.sideview')
@section('content')

@section('style')
{{HTML::style('jqwidgets/styles/jqx.base.css')}}
{{HTML::style('css/dragdrop/jquery-ui.css')}}
{{HTML::style('css/dragdrop/style.css')}}
{{HTML::style('css/bootstrap-select.css')}}
@stop

@section('script')

{{HTML::script('jqwidgets/jqxcore.js')}}
{{HTML::script('jqwidgets/jqxbuttons.js')}}
{{HTML::script('js/plugins/dragdrop/fieldChooser.js')}}
{{HTML::script('jqwidgets/jqxscrollbar.js')}}
{{HTML::script('js/plugins/dragdrop/jquery-ui.js')}}
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
{{HTML::script('jqwidgets/jqxcheckbox.js')}}
{{HTML::script('jqwidgets/jqxdatatable.js')}}
{{HTML::script('jqwidgets/jqxtreegrid.js')}}
{{HTML::script('js/plugins/bootstrap-select/bootstrap-select.js')}}
{{HTML::script('js/plugins/bootstrap-select/bootstrap-datepicker.min.js')}}
{{HTML::script('js/plugins/bootstrap-select/bootstrap-multiselect.js')}}
{{HTML::script('js/plugins/jquery-file-upload/vendor/jquery.ui.widget.js')}}
{{HTML::script('js/plugins/jquery-file-upload/load-image.all.min.js')}}
{{HTML::script('js/plugins/jquery-file-upload/jquery.iframe-transport.js')}}
{{HTML::script('js/plugins/jquery-file-upload/jquery.fileupload.js')}}
{{HTML::script('js/plugins/jquery-file-upload/jquery.fileupload-process.js')}}
{{HTML::script('js/plugins/jquery-file-upload/jquery.fileupload-image.js')}}
{{HTML::script('js/plugins/jquery-file-upload/jquery.fileupload-audio.js')}}
{{HTML::script('js/plugins/jquery-file-upload/jquery.fileupload-video.js')}}
{{HTML::script('js/plugins/jquery-file-upload/jquery.fileupload-validate.js')}}
{{HTML::script('js/plugins/jquery-file-upload/customer-upload-script.js')}}
{{HTML::script('scripts/demos.js')}}
{{HTML::script('js/plugins/bootstrap-select/bootstrap-select.js')}}
{{HTML::script('js/plugins/bootstrap-select/bootstrap-multiselect.js')}}
{{HTML::script('js/plugins/validator/formValidation.min.js')}}
{{HTML::script('js/plugins/validator/validator.bootstrap.min.js')}}
{{HTML::script('js/plugins/validator/jquery.bootstrap.wizard.min.js')}}


<!-- Include all compiled plugins (below), or include individual files as needed -->
<script type="text/javascript">
    $(document).ready(function () {
        $('#changeStatus [name="name"]').keyup(function () {
            //console.log('Hi');
            $('#changeStatus [name="status_id"]').val($('#changeStatus [name="name"]').val().replace(/\s+/g, '_').toLowerCase());
            $('[name="status_id"]').change();
        });
        $('#editLabelDetails [name="name"]').keyup(function () {
            //console.log('Hi');
            $('#editLabelDetails [name="label_id"]').val($('#editLabelDetails [name="name"]').val().replace(/\s+/g, '_').toLowerCase());
            $('#editLabelDetails [name="label_id"]').change();
        });
 $('#editLabelwithPO [name="name"]').keyup(function () {
            //console.log('Hi');
            $('#editLabelwithPO [name="label_id"]').val($('#editLabelwithPO [name="name"]').val().replace(/\s+/g, '_').toLowerCase());
            $('#editLabelwithPO [name="label_id"]').change();
        });



 $('#export_po').bootstrapValidator({
        message: 'This value is not valid',
        icon: {
            validating: 'glyphicon glyphicon-refresh'
        },
        fields: {
            vendor_id: {
                validators: {
                    notEmpty: {
                        message: 'Select vendor type'
                    }
                }
            }

        }
}).on('success.form.bv', function(e){
});




//validator
   
        $('#editLabelDetails').bootstrapValidator({
//        live: 'disabled',
            message: 'This value is not valid',
            feedbackIcons: {
                valid: 'glyphicon glyphicon-ok',
                invalid: 'glyphicon glyphicon-remove',
                validating: 'glyphicon glyphicon-refresh'
            },
            fields: {
                label_id: {
                    validators: {
                        notEmpty: {
                            message: 'Please select Label name.'
                        }
                    }
                },
                qty: {
                    validators: {
                        notEmpty: {
                            message: 'Qty is Required'
                        },
                         numeric:{
                             message : 'The Qty should be numeric'
                        },
                    }
                }
            }
        }).on('success.form.bv', function (event) {
            event.preventDefault();
            var label_id = $('#label_id').val();
            var po_number = $('#po_number').val();
            $.ajax({
        type: "PUT",
        url: '/account/updateLabelDetails/'+ label_id +'/'+ po_number,
        data: $(this).serialize(),
        success: function(msg) {
        alert('Succesfully Updated');
        alaxcall();
        //location.reload();
        }
    });
        }).validate({
            submitHandler: function (form) {
                return false;
            }
        });
        $('#basicvalCodeModal1').on('hide.bs.modal', function () {
            console.log('resetForm');
            //var sheet_qty = $("sheet_qty").val();
            //alert(sheet_qty);
            //$("#sheet_qty").val(sheet_qty);
            $('#editLabelDetails').data('bootstrapValidator').resetForm();
            $('#editLabelDetails')[0].reset();
        });
        $('#changeStatus').bootstrapValidator({
//        live: 'disabled',
            message: 'This value is not valid',
            feedbackIcons: {
                valid: 'glyphicon glyphicon-ok',
                invalid: 'glyphicon glyphicon-remove',
                validating: 'glyphicon glyphicon-refresh'
            },
            fields: {
                status_id: {
                    validators: {
                        notEmpty: {
                            message: 'Please select Status name.'
                        }
                    }
                },
                url: {
                    validators: {
                        notEmpty: {
                            message: 'File is Required'
                        }
                    }
                },
                invoice_number: {
                    validators: {
                        notEmpty: {
                            message: 'Invoice Number is Required'
                        }
                    }
                },
                payment_reference_number: {
                    validators: {
                        notEmpty: {
                            message: 'reference number is Required'
                        }
                    }
                },

                // dispatch_date: {
                //     validators: {
                //         notEmpty: {
                //             message: 'Dispatch Date is Required'
                //         }
                //     }
                // }
                
            }
        }).on('success.form.bv', function (event) {
            event.preventDefault();

            var status_id = $('#status_id').val();
            var po_number = $('#po_number').val();
             var form = $('#changeStatus')[0];
             var data = new FormData(form);
            $.ajax({
           type: "POST",
           enctype: 'multipart/form-data',
           url: '/account/saveStatus/'+ status_id+'/'+ po_number,
           
           //data: $("#changeStatus").serialize(),
           data:data,
           processData: false,
        contentType: false,
            cache: false,
           success: function(msg) {
          // location.reload();
        $('#basicvalCodeModal').modal('toggle');
          $("#success_message_ajax").html('<div class="flash-message"><div class="alert alert-success">'+msg+'</div></div>' );
            $(".alert-success").fadeOut(20000);
           }
              });
            //ajaxCallPopup($('#changeStatus'));
           ajaxCall();
            //return false;
        }).validate({
            submitHandler: function (form) {
                return false;
            }
        });
        $('#basicvalCodeModal').on('hide.bs.modal', function () {
            console.log('resetForm');
            $('#changeStatus').data('bootstrapValidator').resetForm();
            $('#changeStatus')[0].reset();
        });    


$('#editLabelwithPO').bootstrapValidator({
//        live: 'disabled',
            message: 'This value is not valid',
            feedbackIcons: {
                valid: 'glyphicon glyphicon-ok',
                invalid: 'glyphicon glyphicon-remove',
                validating: 'glyphicon glyphicon-refresh'
            },
            fields: {
                label_id: {
                    validators: {
                        notEmpty: {
                            message: 'Please select Label name.'
                        }
                    }
                },
                qty: {
                    validators: {
                        notEmpty: {
                            message: 'Qty is Required'
                        },
                         numeric:{
                             message : 'The Qty should be numeric'
                        },
                    }
                }
            }
        }).on('success.form.bv', function (event) {
            event.preventDefault();
            //var label_id = $('#label_id').val();
            //var po_number = $('#po_number').val();
            //alert('hello1');
            $.ajax({
        type: "PUT",
        url: '/account/updateLabelwithPO',
        data: $(this).serialize(),
        success: function(data) {
            //alert('hello2');
        // alert('Succesfully Updated');
        // location.reload();
        console.log(data['message']);
            if ( data['status'] == true )
            {
                $('.close').trigger('click');
                alert(data['message']);
                location.reload();
                //ajaxCall();
            } else {
                alert(data['message']);
            }    
        }
    });
    //alert('hello3');
        }).validate({
            submitHandler: function (form) {
                return false;
            }
        });
        $('#basicvalCodeModal2').on('hide.bs.modal', function () {
            console.log('resetForm');
            //var sheet_qty = $("sheet_qty").val();
            //alert(sheet_qty);
            //$("#sheet_qty").val(sheet_qty);
            $('#editLabelwithPO').data('bootstrapValidator').resetForm();
            $('#editLabelwithPO')[0].reset();
        });





    });
   
   
    $(document).ready(function ()
    {
        $('#main_manufacturer_id').trigger('change');
        var s = $('#status_id').val();
        var p = $('#po_number').val();
        makePopupEditAjaxnewFor($('#basicvalCodeModal'),'status_id','po_number');
        makePopupEditAjaxNew($('#basicvalCodeModal1'),'label_id','po_number');
        //makePopupEditAjax($('#editqcAttributewithProductmodal'), 'product_id');
        makePopupEditAjaxNew($('#basicvalCodeModal2'),'label_id','po_number');


    });
    

    function ajaxCall(){
    var manufacturerId = $('#main_manufacturer_id').val();  
        $('#manuId').hide();               
    var url = "/account/getElementData";
    $("#loading").show();
    $.get(url,function(response){
        var res = response['ponumbers'];
        var res1 = response['labels'];
        var source =
        {
            id: 'po_number',
            datafields: [
                 { name: 'po_number', type: 'string' },
                { name: 'location_name', type: 'string' },
                { name: 'date', type: 'string' },
                { name: 'proposal_date', type: 'string' },
                { name: 'status', type: 'string' },
               { name: 'actions', type: 'string' }
               
            ], 
            datatype: "json",
            localdata: res,
            pagesize:20,
            pager: function (pagenum, pagesize, oldpagenum) {
                // callback called when a page or page size is changed.
            }
        };
       
        var jobAdapter = new $.jqx.dataAdapter(source);
        
        var jobDetailSource = {
            
            datatype: "json",
            localdata: res1,
            async: false
            
        }
                
        var jobDetailAdapter= new $.jqx.dataAdapter(jobDetailSource, { autoBind: true });
        jobsDetails = jobDetailAdapter.records;
        var nestedGrids = new Array();
        console.log(jobsDetails);
        var initrowdetails = function (index, parentElement, gridElement, record) {
            
            var id = record.uid.toString();
            var grid = $($(parentElement).children()[0]);
            nestedGrids[index] = grid;
            var filtergroup = new $.jqx.filter();
            var filter_or_operator = 1;
            var filtervalue = id;
            var filtercondition = 'equal';
            var filter = filtergroup.createfilter('stringfilter', filtervalue, filtercondition);
            // fill the orders depending on the id.
            var jobbyid = [];
            for (var m = 0; m < jobsDetails.length; m++) {
                var result = filter.evaluate(jobsDetails[m]["po_number"]);
                if (result)
                    jobbyid.push(jobsDetails[m]);
            } 

            var jobsource = {
                datafields: [
                    { name: 'label_type_name', type: 'string' },
                    { name: 'sheet_qty', type: 'number' },
                   // { name: 'Qty', type: 'number' },
                    { name: 'actions', type: 'string' }

                 ], 
                id: 'po_number',
                localdata: jobbyid
            }
            var nestedGridAdapter = new $.jqx.dataAdapter(jobsource);
            if (grid != null) {
                grid.jqxGrid({
                    source: nestedGridAdapter, 
                    width: '100%',
                    height:'100%',
                    rowsheight: 30,
                    filterable: true,
                    showfilterrow: true,
                    sortable: true,    
                    columns: [
                      { text: 'Label Type Name', datafield: 'label_type_name', filtertype:'input', width: 500 },
                      { text: 'Sheet Qty', datafield: 'sheet_qty', width: 600},                     
                      { text: 'Actions', datafield: 'actions',  width:750}


                   ]
                });
            }
        }
        $("#Attributegrid").jqxGrid(
        {
            width: '100%', //change
            height: '100%',
            rowsheight: 30,
            pageable: true,
            altrows: true,
            source: jobAdapter,
            rowdetails: true,   
            columnsresize: true,
            filterable: true,
            showfilterrow: true,
            sortable: true,
            pagesizeoptions: ['20','50','80','100'],
            rowdetailstemplate: { rowdetails: "<div id='grid' style='margin: 10px;'></div>", rowdetailsheight: 220, rowdetailshidden: true },           
            initrowdetails: initrowdetails,
                    columns: [
                { text: 'PO Number', datafield: 'po_number', filtertype: 'input', width:200},
                { text: 'Vendor', datafield: 'location_name', filtertype: 'input', width:200},
                { text: 'Date', datafield: 'date', filtertype: 'input', width:200},
                { text: 'Proposal Date', datafield: 'proposal_date', filtertype: 'input', width:200},
                { text: 'Status', datafield: 'status', filtertype: 'input', width:200},
                { text: 'Actions', datafield: 'actions', filtertype: 'input', width:205}

              ],ready:function(){$("#loading").hide();}
        });
        $("#loading").hide();
//         $("#window").jqxWindow({autoOpen: false, theme: 'energyblue'});
//       $('#Attributegrid').on('rowClick', function (event) {
//     var args = event.args;
//     var row = args.row;
//     var column = args.dataField;
//     if (column === "status")
//     {
//         var value = row.status;
//         $("#window").jqxWindow({content: value});
//         $("#window").jqxWindow('open');
    
//     }
// });
    });     
 }

    function makePopupAttributeAjax($el, primaryKey)
    {
        $el.on('shown.bs.modal', function (e) {
            var url = $(e.relatedTarget).data('href'),
                    $this = $(this),
                    $form = $this.find('form'),
                    key = primaryKey || 'attribute_group_id';

            $.get(url, function (data) {
                $.each(data, function (i, v) {
                    $form.find('[name="' + i + '"]').val(v);
                });
            });
        });
    }
    
    
    

    // function getAttributeGroupName(productId) {
    //     $('#product_id').val(productId);
    // }

    // function getAssignAttribute(attributeSetId)
    // {
    //     $('#assign_attribute_set_id').val(attributeSetId);
    //     $('#attribute_set_id_add_attribute').val(attributeSetId);
    //     $('#assign_attribute_set_name').val($('#attribute_set_id_add_attribute option:selected').text());
    // }

    // // function loadAssignData()
    // {
    // }

    $('#main_manufacturer_id').change(function () {
        $('[id="update_manufacturer_name"]').val($('#main_manufacturer_id option:selected').text());
        $('[id="update_manufacturer_id"]').val($(this).val());
        ajaxCall($(this).val());
        //loadAssignData();
    });
     function getPonum(po_number)
    {
     var po_number = $('#po_number').val(po_number);
     //$('#po_number').val($('#po_number option:selected').text());

     }
     function geteditLabelDetails(label_id,po_number,sheet_qty)
    {
     var po_number = $('#po_number').val(po_number);
     var label_id = $('#label_id').val(label_id);
     var sheet_qty = $('#sheet_qty').val(sheet_qty);
     //alert(label_id);
     //alert(sheet_qty);
                   

     }
      function vendorGroups()
    {
        //var get = getPonum(po_number);
        $('[name="vendor_id"]').empty();
        var po_number = $('#po_number').val();
        var url = '/account/getVendordata';
        // Send the data using post
        var posting = $.post(url, {data_type: 'vendorGroups', data_value: po_number});
        // Put the results in a div
        posting.done(function (data) {
            var result = JSON.parse(data);
            //$('[name="vendor_id"]').append('<option value="" selected="true">Please select... </option>');
            $.each(result, function (key, value) {
                $('[name="vendor_id"]').append('<option value="' + value['vendor_id'] + '">' + value['location_name'] + '</option>');
            });
        });
    }
    function getPonumwithIds(po_number)
    {
     var getpo_number = $('#po_number').val(po_number);
     // var vendor_id = $('#vendor_id').val(vendor_id);
     // var created_date = $('#po_number').val(created_date);
    
    }
    
//Edit
   
//Edit
   
    $('[name="input_type"').change(function () {
        var inputTypeValue = $(this).val();
        if ( inputTypeValue == 'select' || inputTypeValue == 'multiselect' )
        {
            $('#option-button').trigger('click');
        }
    });
    $('#add_new_option').on('click', function () {
        var $template = $('#option_data');
        $clone = $template.clone();
        $('#option_data').before($clone.removeAttr('id').removeAttr('style'));
    });
    
    function postData()
    {
        console.log('we are in view');
        return;
    }

    $('#add_type_name').click(function () {
        var frmData = $('#save_type_name_form').serialize();
        var label_type_name = $('#type_label_name').val();
        if(label_type_name == ""){
            alert("add label type");
        }else{
        $.ajax({
        type: "POST",
        url: '/account/savelabelname',
        data: frmData, 
        success: function (respData)
        {
            var d =JSON.stringify(respData);
            var dd =JSON.parse(d);
            for (var i = 0; i < dd.length; i++) {
//alert((dd[i].label_type_name + ' '+ (dd[i].codes_qty+ '-'+"Code For Sheet") + '-'+"size "+dd[i].codes_size ));
$('#label_type').append($("<option></option>").text(dd[i].label_type_name + ' '+ (dd[i].codes_qty+ '-'+"Code For Sheet") + '-'+"size "+dd[i].codes_size).val(dd[i].id));
            };
            $('#labeladdtype').modal('toggle');
        }
    });

        }
    
    });
</script>    
@stop
</head>
<body>
<span id="success_message_ajax"></span>
<div align="center" id="loading" style="text-align:center; z-index:9999;position:absolute;background:rgba(0,0,0,0.3);height:709px; width:1261px; display:none;" ><img src="/img/loading.gif">    </div>
    @if (Session::has('message'))
    <div class="flash alert">
        <p>{{ Session::get('message') }}</p>
    </div>
    @endif
    <!-- Page content -->
    <!--  <div id="content" class="col-md-12" style="padding-left:258px !important;">  -->


    <div class="box">      
        <div class="main" style="margin-top:15px;">           
            <div class="row">
                <div class="form-group col-sm-5">
              <div class="form-group">
                <div class="col-sm-10">
              <p style="font-size:20px">Label Order Details</p>
            </div>
            </div>
           </div> 
                <div class="form-group col-sm-2 hidden" id="manuId">
                    <label class="col-sm-2 control-label" for="BusinessType">Manufacturer</label>
                    <div class="col-sm-10">
                        <div class="input-group input-group-sm">
                            <span class="input-group-addon addon-red"><i class="fa fa-building-o"></i></span>
                            <div id="selectbox">
                                <select name="manufacturer_id" id="main_manufacturer_id" class="form-control">
                                   @if(!empty($custType) && isset($custType[0]) && $custType[0]->customer_type_id==1001)
                                    @foreach($manufacturerData as $key => $value)
                                    <option value="{{ $key }}" selected="true">{{ $value }}</option>
                                    @endforeach
                                    @else
                                    <option value="0">Please select..</option>                                    
                                    @foreach($manufacturerData as $key => $value)
                                    <option value="{{ $key }}">{{ $value }}</option>
                                    @endforeach
                                    @endif
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            <div class="form-group col-sm-7">
              <div class="form-group pull-right">
                 <div class="col-sm-4">
                     <a href="/account/add" class="button"> <button type="button" class="btn btn-primary"><i class="fa fa-plus-circle"></i><span style="font-size:11px;">Create New PO</span></button></a>
                </div>
                <div class="col-sm-4">
                    <button type="button"  class="btn btn-primary" data-toggle="modal" data-target="#labelorderpopup">List Of Label Orders</button>
              </div>
              <div class="col-sm-4">
                  <button type="button"  class="btn btn-primary" data-toggle="modal" data-target="#Exportpo" style="margin-left:34px">Export PO</button>
            </div>
            </div>
           </div> 
        </div> 
    </div> 

        <div class="col-sm-12">
            <div class="tile-body nopadding"> 
                      
                <div id="Attributegrid" style="width:100% !important;"></div>
                <button data-toggle="modal" id="edit" class="btn btn-default" data-target="#wizardCodeModal" style="display: none"></button>
               <!--  <div id='window'>
                   <div>Product Name</div>
            </div> -->
        </div>           
        <!-- Modal -->
        <div class="modal fade" id="basicvalCodeModal1" tabindex="-1" role="dialog" aria-labelledby="basicvalCode" aria-hidden="true">
            <div class="modal-dialog wide">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                        <h4 class="modal-title" id="basicvalCode">Edit Label Details</h4>
                    </div>
                    <div class="modal-body">
                        {{ Form::open(array('url' => '/account/updateLabelDetails', 'data-url' => '/account/updateLabelDetails/','id'=>'editLabelDetails')) }} 
                        {{ Form::hidden('_method', 'PUT') }}

                        <div class="row">
                            <div class="form-group col-sm-6">
                                <label for="exampleInputEmail"> Label*</label>
                                 <div class="input-group">
                                    <span class="input-group-addon addon-red"><i class="fa fa-cubes"></i></span>
                                    <select name="label_id" id="label_id" class="form-control">
                                       @foreach($labelswithQty as $label)
                                            <option value="{{$label->label_id}}"> {{$label->name}}</option>
                                          @endforeach

                                    </select>
                                    <input type="hidden" name="po_number" id="po_number" value="" />

                                </div>                        
                            </div>
                             <div class="form-group col-sm-4">
                                <label for="exampleInputEmail">Qty *</label>
                                <div class="input-group ">
                                    <span class="input-group-addon addon-red"><i class="fa fa-cube"></i></span>
                                    <input type="text"  id="sheet_qty" name="qty" value="" class="form-control" aria-describedby="basic-addon1">
                                </div>
                            </div>  
                                                         
                        </div>
                        {{ Form::submit('Update', array('class' => 'btn btn-primary')) }}
                        {{ Form::close() }}

                    </div><!-- /.modal-content -->
                </div><!-- /.modal-dialog -->
            </div><!-- /.modal -->
        </div>



         <div class="modal fade" id="labelorderpopup" tabindex="-1" role="dialog" aria-labelledby="basicvalCode" aria-hidden="true">
            <div class="modal-dialog wide">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                        <h4 class="modal-title" id="basicvalCode">List Of Label Orders</h4>
                    </div>
                    <div class="modal-body">
                       <!--  {{ Form::open(array('url' => '/account/updateLabelwithQTY', 'data-url' => '/account/updateLabelwithQTY/','id'=>'editlabeldetailswithqty')) }} 
                        {{ Form::hidden('_method', 'PUT') }}
 -->
                        <div class="row">
                            <div class="form-group">
                                <!-- <label for="exampleInputEmail"> Label Orders</label> -->
                                 <div class="input-group1">
                                    <table class="table table-responsive" id="labelIdsWithQty">
                                        <tr>
                                            <th>Vendor Po Number</th>
                                        <th>Labels</th>
                                      <th>Qty</th>

                                        </tr>
                                        <tr>
                                            <td>
                                               @foreach($labelsNqty as $po)
                                        {{$po->po_number}} <br>
                                           @endforeach 
                                            </td>
                                        <td>
                                       @foreach($labelsNqty as $label)
                                        <!-- <input type="checkbox" name="labels[]" id="label_id" value="{{$label->id}}">  -->
                                        {{$label->label_type_name}} <br>
                                           @endforeach
                                        </td>
                                        <td>
                                              @foreach($labelsNqty as $sheet)
                                    {{$sheet->sheet_qty}} <br>
                                           @endforeach
                                        </td>
                                    </tr>
                                    </table>
                                    <input type="hidden" name="po_number" id="po_number" value="" />
                                
                                </div>                        
                            </div>                       
                        </div>
                       <!--  {{ Form::submit('Update', array('class' => 'btn btn-primary')) }}
                        {{ Form::close() }} -->

                    </div><!-- /.modal-content -->
                </div><!-- /.modal-dialog -->
            </div><!-- /.modal -->
        </div>



        <div class="modal fade" id="basicvalCodeModal2" tabindex="-1" role="dialog" aria-labelledby="basicvalCode" aria-hidden="true">
            <div class="modal-dialog wide">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                        <h4 class="modal-title" id="basicvalCode">Add LabelTypes</h4>
                    </div>
                    <div class="modal-body">
                        {{ Form::open(array('url' => '/account/updateLabelwithPO',
                        'data-url' => '/account/updateLabelwithPO/','id'=>'editLabelwithPO')) }} 
                        {{ Form::hidden('_method', 'PUT') }}
            <div class="row">    
                <div class="form-group col-sm-4">
                  <label>Vendor Name</label>
                    <div id="Vendor">
                      <select class="form-control chosen" id="vendor_id" name="vendor_id" parsley-trigger="change" parsley-required="true" parsley-error-container="#selectbox">
                       <option value="">Please Select Vendor</option>
                        
                      </select>
                    </div>
              </div>
              <div class="form-group col-sm-4">
                  <label>PO Number</label>
                    <div id="location">
                      <input type="text" class="form-control" placeholder="PO Number" name="po_number" id="po_number_set">
                    </div>
              </div>
             <!--  <div class="form-group col-sm-4">
                  <label>Date</label>
                    <input type="text" class="form-control" id="datepicker" name="date">
                     
                    </div> -->
                 </div>
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
                      <input type="text" class="form-control"  id="quantity" name="quantity">         
              </div>
                <div class="col-sm-2">
                                <label for="exampleInputEmail"></label>
                                <div class="input-group csschanges">
                                    <div class="input-group-addon">
                                        <i id="add_assign" style="cursor: pointer;">Add</i>
                                    </div>
                                </div>
                </div>
                @if($addlabelaccess == 1)
            <div class="col-sm-5">
                    <button type="button"  class="btn btn-primary" data-toggle="modal" data-target="#labeladdtype">Add Label type</button>
            </div>
            @endif
                        </div> 

                        <div class="row">
                            <section class="tile">
                                <div class="panel panel-default">
                                    <!-- Default panel contents -->
                                    <div class="panel-heading">Label details</div>
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
                        {{ Form::submit('Update', array('class' => 'btn btn-primary')) }}
                        {{ Form::close() }}
                    </div>
                </div><!-- /.modal-content -->
            </div><!-- /.modal-dialog -->
        </div><!-- /.modal -->



        <!-- Modal -->
        <div class="modal fade" id="basicvalCodeModal" tabindex="-1" role="dialog" aria-labelledby="basicvalCode" aria-hidden="true">
            <div class="modal-dialog wide">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                        <h4 class="modal-title" id="basicvalCode">Change Status</h4>
                    </div>
                    <div class="modal-body">
                        {{ Form::open(array('url' => 'account/saveStatus','data-url' => '/account/saveStatus/','id'=>'changeStatus','enctype'=>'multipart/form-data')) }}
                        {{ Form::hidden('_method', 'POST') }}

                        <div class="row">
                            <div class="form-group col-sm-6">
                                <label for="exampleInputEmail">Status Name*</label>
                                <div class="input-group">
                                    <span class="input-group-addon addon-red"><i class="fa fa-cubes"></i></span>
                                    <select name="status_id" id="status_id" class="form-control">
                                    <option value="">Please Select</option>
                                       @foreach($getStatus as $status)
                                            <option value="{{$status->status_id}}"> {{$status->name}}</option>
                                          @endforeach
                                    </select>
                                <input type="hidden" name="po_number" id="po_number" value="" />
                                </div>                        
                            </div>
                            <div class="form-group col-sm-6" id="urldiv">
                               <label>FILE *</label>
                                    <input type="file" id="url" name="url">
                            </div>                             
                        </div>
                    <div class="row" class="invoicehide" id="invoicediv">
                        <div class="form-group col-sm-3" >
                               <label for="exampleInputEmail">Invoice Number*</label>
                                <div class="input-group ">
                                   <span class="input-group-addon addon-red"><i class="fa fa-cube"></i></span>
                                    <input type="text"  id="invoice_number" name="invoice_number" placeholder="Invoice Number" class="form-control">
                                </div>
                            </div> 
                            <div class="form-group col-sm-3" >
                               <label for="exampleInputEmail">Track Number</label>
                                <div class="input-group ">
                                   <span class="input-group-addon addon-red"><i class="fa fa-cube"></i></span>
                                    <input type="text"  id="track_number" name="track_number" placeholder="Track Number" class="form-control">
                                </div>
                            </div> 
                           <!--  <div class="form-group col-sm-3" >
                               <label>Dispatch Date*</label>
                                <div class="input-group ">
                                   <span class="input-group-addon addon-red"><i class="fa fa-cube"></i></span>
                                    <input type="text"  id="datepicker" name="dispatch_date" placeholder="Date" class="form-control">
                                </div>
                            </div> -->
                            <div class="form-group col-sm-3">
                               <label for="exampleInputEmail">Courier Name</label>
                                <div class="input-group ">
                                   <span class="input-group-addon addon-red"><i class="fa fa-cube"></i></span>
                                    <input type="text"  id="courier_name" name="courier_name" placeholder="Courier Name" class="form-control">
                                </div>
                            </div>
                            <div class="form-group col-sm-3">
                               <label>Invoice Image</label>
                                <div class="input-group ">
                                    <input type="file"  id="invoice_image" name="invoice_image">
                                </div>
                            </div>
                      </div>

                      <div class="row" class="poraisedhide" id="poraiseddiv">    
                            <div class="form-group col-sm-3" id="po_raised">
                               <label for="">proposal date</label>
                                <div class="input-group ">
                                   <span class="input-group-addon addon-red"><i class="fa fa-cube"></i></span>
                                    <input type="date"  id="po_raised_date" name="po_raised_date" placeholder="select date" class="form-control">
                                </div>
                            </div>
                      </div>

                      <div class="row" class="paymentdiv" id="paymentdiv">
                            <div class="form-group col-sm-3" >
                               <label for="exampleInputEmail">Payment type</label>
                                <div class="input-group ">
                                   <span class="input-group-addon addon-red"><i class="fa fa-cube"></i></span>
                                    <select  id="payment_type_po" name="payment_type_po" class="form-control">
                                        <option value="check">check</option>
                                        <option value="NEFT">NEFT</option>
                                    </select>
                                </div>
                            </div> 
                            <div class="form-group col-sm-3">
                               <label for="exampleInputEmail">Payment Reference Number</label>
                                <div class="input-group ">
                                   <span class="input-group-addon addon-red"><i class="fa fa-cube"></i></span>
                                    <input type="text"  id="payment_reference_number" name="payment_reference_number" placeholder="reference number" class="form-control">
                                </div>
                            </div>
                      </div>                       
                        {{ Form::submit('Update', array('class' => 'btn btn-primary')) }}
                        {{ Form::close() }}


                    </div><!-- /.modal-content -->
                </div><!-- /.modal-dialog -->
            </div><!-- /.modal -->
        </div>

    <div class="modal fade" id="labeladdtype" tabindex="-1" role="dialog" aria-labelledby="basicvalCode" aria-hidden="true">
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
                            <label>Label Rows</label>
                            <div id="location">
                                <input type="text" class="form-control" placeholder="ex:3" name="type_label_qty" id="type_label_qty">
                            </div>
                        </div>
                         <div class="form-group col-sm-4">
                            <label>Label columns</label>
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

    <div class="modal fade" id="Exportpo" tabindex="-1" role="dialog" aria-labelledby="basicvalCode" aria-hidden="true">
        <div class="modal-dialog wide">
            <div class="modal-content">
                <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                        <h4 class="modal-title" id="basicvalCode">Export po</h4>
                </div>
                <div class="modal-body">

                    {{ Form::open(array('url' => 'account/downloadpo','method'=>'post','id' => 'export_po'))}}

                    <div class="row">    
                        <div class="form-group col-sm-4">
                            <label>Vendor Name</label>
                            <div id="vendorid">
                                <select id="vendor_id" name="vendor_id[]" class = "form-control" multiple>
                                    <option value="">Please Select</option>
                                    @foreach($getvendorbymanf as $status)
                                            <option value="{{$status->location_id}}"> {{$status->location_name}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                       <!--  <div class="form-group col-sm-4">
                            <label>PO Number</label>
                            <div id="vendorid">
                                <select id="vendor_id_po" name="vendor_id_po[]" class = "form-control" disabled multiple>
                                    
                                </select>
                            </div>
                        </div> -->
                    </div>
                    <div class="row">
                        <div class="col-md-12 text-center">
                            <div class="form-group">
                            <button type="submit" class="btn green-meadow">Download</button>
                            </div>
                        </div>                   
                    </div>
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>


    <div class="modal fade" id="view-po-status" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                                <h4 class="modal-title" id="myModalLabel"> VIEW HISTORY PO</h4>
                            </div>

                            <table style="width:100%" class="table table-bordered">
                              <thead>
                                <tr>
                                  <th>PO number</th>
                                  <th>Date</th>
                                  <th>User Name</th>
                                  <th>Status</th>
                                </tr>
                              </thead>
                              <tbody id="pocontainer">
                              </tbody>
                          </table>

                        </div>
                    </div>
    </div>




        <button id="option-button" data-toggle="modal" data-target="#addoptions" style="display: none;"></button>
        <!-- Modal -->
       

    </div>
    <script type="text/javascript">
    
   function delLabelwithPo(label_id, po_number)
    {
        var dec = confirm("Are you sure you want to Delete ?");
        if(dec == true){
                    $.ajax({
                        url: '/account/delLabelwithPo'+'/'+label_id+'/'+po_number,
                        type: 'GET',
                        success: function (result)
                        {
                            if ( result == 1 ) {
                                alert('Succesfully Deleted !!');
                             ajaxCall();
                            } else {
                                alert(result);
                            }
                        }
                    });
                }
    }
    $(function() {
    $("#status_id").on("change",function() {
       var urlvalue = $("#status_id").val(); 
       var invoiceD = $("#status_id").val(); 
       //$(".urlhide").hide();
       if(urlvalue == "51002"){
        $("#poraiseddiv").show();
       }else{
        $("#poraiseddiv").hide();

       }

       if(urlvalue == "51003")
       {
            $("#urldiv").show();
       }
       else 
       {
            $("#urldiv").hide();
       }
       $(".invoicehide").hide();
       if(invoiceD == "51006")
       {
            $("#invoicediv").show();
       }
       else 
       {
            $("#invoicediv").hide();
       }
       if(urlvalue == "51008"){
        $("#paymentdiv").show();
       }else{
        $("#paymentdiv").hide();
       }


       

       
    }).change();
});
    


        function removeActions($option)
        {
            $option.parent().parent().remove();
            //$option.closest('div #option_data');
            //console.log($option);
        }
        $('#save-options').click(function () {
            var keyData = [];
            var valueData = [];
            var sortOrderData = [];
            $('[name="key[]"]').each(function (elem) {
                if ( $(this).val() != '' )
                {
                    keyData.push($(this).val());
                }
            });
            $('[name="value[]"]').each(function (elem) {
                if ( $(this).val() != '' )
                {
                    valueData.push($(this).val());
                }
            });
            $('[name="sort_order[]"]').each(function (elem) {
                if ( $(this).val() != '' )
                {
                    sortOrderData.push($(this).val());
                }
            });
            var responseData = {};
            for (var i = 0, len = keyData.length; i < len; i++) {
                var keyValue = '';
                var dataValue = '';
                var sortValue = 0;
                if ( keyData[i] != '' || keyData[i] != 'undefined' )
                {
                    keyValue = keyData[i];
                }
                if ( valueData[i] != '' || valueData[i] != 'undefined' )
                {
                    dataValue = valueData[i];
                }
                if ( sortOrderData[i] != '' || sortOrderData[i] != 'undefined' )
                {
                    sortValue = sortOrderData[i];
                }
                responseData[i] = keyValue + ';' + dataValue + ';' + sortValue;
            }
            var modelId = $('[class="modal fade in"').attr('id');
            $('#' + modelId).find('[name="option_values"]').val(JSON.stringify(responseData));
            $('#option-close').trigger('click');
        });
        
 $('#basicvalCodeModal2').on('show.bs.modal', function (e) {
            vendorGroups();

        var po_number = $('#po_number').val();
        //console.log(attribute_set_id);
        $('#po_number_set').val(po_number);

        $('#po_number_set').attr('readonly','true');

        var url = '/account/getLabeldetailsForPO/'+ po_number;
        
        $("#assign_data").empty();
        // $('#po_number').val($('#po_number option:selected').text());
        var posting = $.get(url); 
        posting.done(function (data) {
            //console.log(data);
            //$('#assigntable').empty();
            $.each(data, function (key, value) {
                var jsonArg = new Object();
                jsonArg.label_id = value['label_id'];
                jsonArg.qty_id = value['qty'];
                var hiddenJsonData = new Array();
                hiddenJsonData.push(jsonArg);    
                
                $("#assign_data").append('<tr><td scope="row" id="label_type_text">' + value['label_type'] + '</td><td id="qty_text">' + value['qty']
                            + '</td><td><a href="javascript:void(0);" class="check-toggler" id="remCF"><span class="badge bg-red"><i class="fa fa-trash-o"></i></span></a><input type="hidden" name="data[]" value=' + "'" + JSON.stringify(jsonArg) + "'" + ' /></td></tr>');
            });
        });               
    });    

 $('#add_assign').click(function(){
            var label_val = $('#label_type').val();
            var label_text = $('#label_type option:selected').text();
            var qty_id = $('#quantity').val();
            var qty_text = $('#quantity').text();
            
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
/*function loadponumber(){

    $("#vendor_id_po").removeAttr("disabled");

    var frmData = $('#export_po').serialize();

        $.ajax({
        type: "POST",
        url: '/account/checkponumberagainstvendor',
        data: frmData, 
        success: function (data)
        {
            var brand = $('#vendor_id_po');
                        brand.find('option').remove().end();
                        for(var i=0; i<data.length; i++){
                            brand.append(
                                $('<option></option>').val(data[i].po_number).html(data[i].po_number)
                            );
                        }
        }
    });
    }*/

    function viewdetailsdata(po_num){
        $('#view-po-status').modal('toggle');
        $.ajax({
            type: "GET",
            url: '/account/gethistorypodetails/' + po_num,
            success: function (data)
            {
                $("#pocontainer").empty();

                $('#pocontainer').append(data);
            }
    });
    }
    </script>
    @stop            