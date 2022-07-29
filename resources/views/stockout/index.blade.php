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

<!-- {{HTML::script('jqwidgets/jqxcore.js')}}
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
{{HTML::script('jqwidgets/jqxtreegrid.js')}} -->
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
<!-- {{HTML::script('js/plugins/jquery-file-upload/jquery.fileupload-validate.js')}}
 --><!-- {{HTML::script('js/plugins/jquery-file-upload/customer-upload-script.js')}} -->
{{HTML::script('scripts/demos.js')}}
{{HTML::script('js/plugins/bootstrap-select/bootstrap-select.js')}}
{{HTML::script('js/plugins/bootstrap-select/bootstrap-multiselect.js')}}
{{HTML::script('js/plugins/validator/formValidation.min.js')}}
{{HTML::script('js/plugins/validator/validator.bootstrap.min.js')}}
{{HTML::script('js/plugins/validator/jquery.bootstrap.wizard.min.js')}}

<script>
$('#frm_stockout_tmpl').bootstrapValidator({
            message: 'This value is not valid',
            feedbackIcons: {
                valid: 'glyphicon glyphicon-ok',
                invalid: 'glyphicon glyphicon-remove',
                validating: 'glyphicon glyphicon-refresh'
            },
            fields: {
                srcLocationId: {
                    validators: {
                        notEmpty: {
                            message: 'Please select src location.'
                        }
                    }
                },
                destLocationId: {
                    validators: {
                        notEmpty: {
                            message: 'Please select dest location'
                        }
                    }
                },
                delivery_no: {
                    validators: {
                        notEmpty: {
                            message: 'Deliver Number is Required'
                        }
                    }
                },
                
            }
        }).on('success.form.bv', function (event) {
            event.preventDefault();

            var form=$("#frm_stockout_tmpl");
            
            $.ajax({
           type: "POST",
           url: '/stockout/savestockout',
           data: form.serialize(),
           success: function(msg) {

            $("#success_message_ajax").html('<div class="flash-message"><div class="alert alert-success">'+msg.Message+'</div></div>' );
                $(".alert-success").fadeOut(20000);
           
           }
              });
        }).validate({
            submitHandler: function (form) {
                return false;
            }
        });
    
</script>    
@stop
</head>
<body>
<?php View::share('title', 'Stockout'); ?>
<span id="success_message_ajax"></span>
<div class="row">
<div class="col-md-12">
<div class="portlet light tasks-widget">

    <div class="portlet-title">
        <div class="caption">Stockout</div>
        <div class="tools">
        <span data-original-title="Tooltip in top" data-placement="top" class="badge bg-blue tooltips"><i class="fa fa-question"></i></span>
        </div>
    </div>
    
<form  action ="" method="POST" id = "frm_stockout_tmpl" name = "frm_stockout_tmpl">

    <div class="portlet-body">
        <div class="row">
            <div class="col-md-3">
                <div class="form-group">
                    <label>Source Location</label>
                    <select id = "srcLocationId"  name =  "srcLocationId" class="selectpicker" data-live-search="true" parsley-required="true">
                        <option value = "0">--Please Select--</option>
                        @foreach($sourcelocations as $location)
                        <option value = "{{$location->location_id}}">{{$location->location_name}}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group">
                    <label>Destinate Location</label>
                    <select id = "destLocationId"  name="destLocationId" class="selectpicker" data-live-search="true">
                        <option value = "0">--Please Select--</option>
                        @foreach($destilocations as $location)
                        <option value = "{{$location->location_id}}">{{$location->location_name}}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="col-md-3">
                <div class="row">
                        <div class="form-group">
                        <label>Delivery Number</label>
                            <input type="text" class="form-control" name="delivery_no" id="delivery_no"/>
                        </div>
                </div>
            </div>
            <div class="col-md-2">
                        <div class="form-group">
                            <label>&nbsp;</label>
                            <div class="mt-checkbox-list margt">
                                <label class="mt-checkbox">
                                <input type="checkbox"  value="1" id = "isSapEnabled" name = "isSapEnabled"> Is SAP Enabled
                                <span></span>
                                </label>
                            </div>
                        </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
            <div class="form-group">
                <label>Module Name</label>
                <select id = "module_id"  name =  "module_id" class="form-control">
                        <option value = "">--Please Select--</option>
                        @foreach($modulname as $name)
                        <option value = "{{$name->module_id}}">{{$name->module_id}}</option>
                        @endforeach
                    </select>
            </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label>Parent Code</label>
                    <input type="text" class="form-control"  id = "codes" name = "codes"/>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label>Transition Type</label>
                    <select class="form-control"  id = "transitionId" name = "transitionId">
                        <option value = "">--Please Select--</option>
                        @foreach($transType as $transTypes)
                        <option value = "{{$transTypes->id}}">{{$transTypes->name}}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <div class="row">
            @foreach($attributetypes as $types)
            <div class="col-md-3">
            <div class="form-group">
                @if($types->input_type !="hidden")
                <label>{{str_replace('_', ' ', $types->attribute_code) }}</label>
                <input type ="{{$types->input_type}}" id = "{{$types->attribute_code}}"  name ="{{$types->attribute_code}}" class="form-control">
                @endif
            </div>
            </div>
            @endforeach
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    <label for="multiple" class="control-label">Eseal Ids</label>
                        <textarea id="ids" name =  "ids" class="form-control">
                        </textarea>
                </div>
            </div>
        </div>

                <div class="row">
            <div class="col-md-12 text-center">
                <button type="submit"class="btn green-meadow">Save Details</button>
            </div>
        </div>
    </div>
</div>
</form>    
</div>
</div>
</div>
    <script type="text/javascript">
         
    </script>
    @stop            