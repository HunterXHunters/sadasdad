@extends('layouts.default')
@extends('layouts.header')
@extends('layouts.sideview')
@section('content')
@if($errors->any())
<h4>{{$errors->first()}}</h4>
@endif

<div class="box">
<div class="box-body">

    @if(session('status')!==NULL)
    <div class="alert alert-success">
        <strong>hii</strong>
    </div>
    @endif
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css">
<style>
.btn{margin-left:10px !important;}
.panel-heading .accordion-toggle:after {
    /* symbol for "opening" panels */
    font-family: 'Glyphicons Halflings';  /* essential for enabling glyphicon */
    content: "\e114";    /* adjust as needed, taken from bootstrap.css */
    float: right;        /* adjust as needed */
    color: grey;         /* adjust as needed */
}
.panel-heading .accordion-toggle.collapsed:after {
    /* symbol for "collapsed" panels */
    content: "\e080";    /* adjust as needed, taken from bootstrap.css */
}

</style>
<style type="text/css">
    .error {
        color: red;
    }
    .modal-header h4{margin-bottom:0px !important}
    .fileinput-button i{position:absolute; z-index:-99999!important;}
    .form-control-feedback{top:0px !important;}
    .checkbox input[type=checkbox], .checkbox-inline input[type=checkbox], .radio input[type=radio], .radio-inline input[type=radio]{margin-left:0px !important;}
    .col-sm-1{padding-left:0px;}
    h4{margin-bottom:20px !important;}
</style>



<div><span class="error_message"></span></div>
    <div class="nav-tabs-custom">
        <ul class="nav nav-tabs pull-right">
            
            <!-- <li><a href="#transaction" data-toggle="tab">Transaction</a></li>
            <li><a href="#orders" data-toggle="tab">Orders</a></li>
            <li><a href="#products" data-toggle="tab">Products</a></li> -->
            <!-- <li ><a href="#location_types" >Location Types</a></li>         -->
            <!-- <li><a href="#erp_configuration" data-toggle="tab">ERP Configuration</a></li>
            <li class=""><a href="#tax_class" data-toggle="tab" aria-expanded="true">Tax Class</a></li>
            <li><a href="#price_management" data-toggle="tab" aria-expanded="true">Pricing Contract</a></li>
            <li><a href="#eseal_products" data-toggle="tab" aria-expanded="false">eSeal Products</a></li>                          
            <li class="active"><a href="#basic" data-toggle="tab" aria-expanded="false">Basic</a></li> -->
            <li class="pull-left header"><i class="fa fa-th"></i> Import Po List</li>
        </ul>                
    </div>
    <?php
    $customer_id = isset($customer_id) ? $customer_id : '';
    ?>
    <input type="hidden" id="customer_id" name="customer_id" value="{{ $customer_id }}" />
    <!-- tile body -->
    <div class="row">
                <div class="form-group col-sm-12">
                    <div id="locations_treegrid"></div>
                </div>
            </div>
<div id="customer_approval" class="modal fade">
  <div class="modal-dialog">
    <div class="modal-content">
      <!-- dialog body -->
      <div class="modal-body">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        Do you want to approve.
      </div>
      <!-- dialog buttons -->
      <div class="modal-footer"><button type="button" class="btn btn-primary">Yes</button><button type="button" class="btn btn-danger">No</button></div>      
    </div>
  </div>
</div>


<div class="state_name" id="state_name" style="display: none;">
    <label for="opt01">Option 1</label>
</div>
<div class="row" id="checkboxTemplate" style="display: none;">
    <div class="form-group col-sm-6">
        <div class="checkbox">
            <input type="checkbox" value="1" id="opt01" parsley-group="mygroup" parsley-trigger="change" parsley-required="true" parsley-mincheck="2" parsley-error-container="#myproperlabel .last" class="parsley-validated">
            <label for="opt01">Option 1</label>
        </div>                       
    </div>  
</div>

@stop

@section('style')
{{HTML::style('jqwidgets/styles/jqx.base.css')}}
{{HTML::style('css/bootstrap-select.css')}}
{{HTML::style('css/datepicker.min.css')}}
{{HTML::style('css/jquery.fileupload.css')}}
@stop

@section('script')
<!-- location data -->
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
{{HTML::script('js/plugins/bootstrap-select//bootstrap-datepicker.min.js')}}

{{HTML::script('js/plugins/validator/formValidation.min.js')}}
{{HTML::script('js/plugins/validator/validator.bootstrap.min.js')}}
{{HTML::script('js/plugins/validator/jquery.bootstrap.wizard.min.js')}}
<!-- location data end -->

<script type="text/javascript">
    $(document).ready(function () {

        ajaxLocationCall();
    });

    function ajaxLocationCall()
    {
        var manufacturer_id = $('#manufid').val();
        // $('#add_location_form').find('#manufacturer_name').val(manufacturer_id).find('option')
        //         .prop('disabled', false).not(':selected').prop('disabled', true);
        // $('#add_location_form').find('#manufacturer_id').val(manufacturer_id);
        var customerId = $('[name="customer_id"]').val();
        // prepare the data
        var source =
        {
            datatype: "json",
            datafields: [
                {name: 'po_number', type: 'varchar'},
                {name: 'idb_number', type: 'varchar'},
                {name: 'material', type: 'varchar'},
                {name: 'vender', type: 'varchar'},
                {name: 'batch', type: 'varchar'},
                {name: 'material_des', type: 'varchar'},
                {name: 'grn_number', type: 'varchar'},
                {name: 'actions', type: 'varchar'},
                {name: 'children', type: 'array'},
                {name: 'expanded', type: 'bool'}
            ],
            hierarchy:
                    {
                        root: 'children'
                    },
            id: 'location_id',
            // url: "/products/getTreeLocation/" + customerId,
            url: "getTreeImportPoList/" + customerId,
            //localData: employees,
            pagesize:30,
            pager: function (pagenum, pagesize, oldpagenum) {
                // callback called when a page or page size is changed.
            }
        };

        var dataAdapter = new $.jqx.dataAdapter(source);
        console.log(dataAdapter);
        $("#locations_treegrid").jqxTreeGrid(
                {
                    width: "100%",
                    source: dataAdapter,
                    sortable: true,
                    filterable: true,
                    pageable: true,
                    columns: [
                        {text: 'PO Number', datafield: 'po_number', width: "10%"},
                        {text: 'IDB Number', datafield: 'idb_number', width: "10%"},
                        {text: 'Material', datafield: 'material', width: "10%"},
                        {text: 'Material Description', datafield: 'material_des', width:"30%"},
                        {text: 'Vendor', datafield: 'vender', width: "10%"},
                        {text: 'Grn Number', datafield: 'grn_number', width: "10%"},
                        {text: 'Batch', datafield: 'batch', width:"10%"},
                        {text: 'Actions', datafield: 'actions', width: "10%"}
                    ]
                });
    }

    function deleteGrn(grnNumer)
    {
        var dec = confirm("Are you sure you want to cancel GRN ?");

        if ( dec == true )
            $.ajax({
                url: '/deleteGrn/' + grnNumer,
                type:'GET',
                success: function(result)
                {
                    if ( result == 1 ) {
                        alert('GRN Cancelled Succesfully !!');
                     location.reload();
                    } else {
                        alert(result);
                    }
                    //window.location.href = '/customer/editcustomer/'+manufacturerId;
                    ajaxLocationCall();
                },
                error: function(err){
                    console.log('Error: '+err);
                },
                complete: function(data){
                    console.log(data);
                    /*loadLocations();*/
                }                
            });
            //window.location.href = 'customer/deletelocation/' + location_id + '/'+manufacturer_id;
    }

</script>
<!-- transaction data end -->
@stop
@extends('layouts.footer')