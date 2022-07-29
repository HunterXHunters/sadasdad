@extends('layouts.default')
@extends('layouts.header')
@extends('layouts.sideview')
@section('content')

<!-- breadcrumbs -->
<div class="breadcrumbs">
    <ol class="breadcrumb">
        <li><a href="#">Home</a></li>
        <li><a href="#">Dashboard</a></li>
        <li class="active">Location Types</li>
    </ol>
</div>
<!-- /breadcrumbs -->
<div class="main">

    <!-- show code btn -->
    <button type="button" class="btn btn-primary"  data-toggle="modal" data-target="#basicvalCodeModal">
        Add LocationType 
    </button>
    <br/>
    <br/>
    <!-- /show code btn -->            

    <!-- Modal -->
    <div class="modal fade" id="basicvalCodeModal" tabindex="-1" role="dialog" aria-labelledby="basicvalCode" aria-hidden="true">
        <div class="modal-dialog wide">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                    <h4 class="modal-title" id="basicvalCode">Add Location Type</h4>
                </div>
                <div class="modal-body">

                    <br/>

                    {{ Form::open(array('url' => 'customer/savelocationtype')) }}
                    {{ Form::hidden('_method', 'POST') }}

                    <div class="row">

                        <div class="form-group col-sm-6">
                            <label for="exampleInputEmail">Location Type Name</label>
                            <div class="input-group ">
                                <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
                                <input type="text"  id="location_type_name" name="location_type_name" required placeholder="location_type_name" class="form-control">
                            </div>
                        </div>
                        <div class="form-group col-sm-6">
                            <label for="exampleInputEmail">Manufacturer Id</label>
                            <div class="input-group ">
                                <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
                                <input type="text" id="manufacturer_id" name="manufacturer_id" 
                                       required placeholder="manufacturer_id" class="form-control">

                            </div>
                        </div>
                    </div>
                    {{ Form::submit('Submit', array('class' => 'btn btn-primary')) }}
                    {{ Form::close() }}
                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->

    <!-- Modal - Popup for EDIT -->
    <div class="modal fade" id="basicvalCodeModal1" tabindex="-1" role="dialog" aria-labelledby="basicvalCode" aria-hidden="true">
        <div class="modal-dialog wide">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                    <h4 class="modal-title" id="basicvalCode">Edit LocationTypes</h4>
                </div>
                <div class="modal-body">
                    {{ Form::open(array('url' => 'customer/updatelocationtype','data-url' => 'customer/updatelocationtype/')) }}
                    {{ Form::hidden('_method','PUT') }}
                    <!-- tile header -->



                    <!-- /tile header -->
                    <!-- tile body -->

                    <div class="row">

                        <div class="form-group col-sm-6">
                            <label for="exampleInputEmail">Location Type Name</label>
                            <div class="input-group ">
                                <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
                                <input type="text"  id="location_type_name" required name="location_type_name" value="" class="form-control" aria-describedby="basic-addon1">
                            </div>
                        </div>
                        <div class="form-group col-sm-6">
                            <label for="exampleInputEmail">Manufacturer Id</label>
                            <div class="input-group ">
                                <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
                                <input type="text" id="manufacturer_id" required name="manufacturer_id" 
                                       value="" class="form-control" aria-describedby="basic-addon1">

                            </div>
                        </div>
                    </div>

                    {{ Form::submit('Update') }}

                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->


    <div id="jqxgrid">
    </div>

</div> <!--/.main -->

@stop

@section('style')
{{HTML::style('jqwidgets/styles/jqx.base.css')}}

@stop

@section('script')
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
<script src="js/bootstrap.min.js"></script>
<script type="text/javascript">
$(document).ready(function ()
{
    var url = "/customer/getlocationtype";
    // prepare the data
    var source =
            {
                datatype: "json",
                datafields: [
                    {name: 'location_type_id', type: 'integer'},
                    {name: 'location_type_name', type: 'string'},
                    {name: 'manufacturer_id', type: 'integer'},
                    // { name: 'actions', index: 'actions', width: 70,  formatter:'actions',
                    //formatoptions: {keys: true, editbutton:true,delbutton:true } }

                    {name: 'actions', type: 'string'}
                    //{ name: 'delete', type: 'string' }
                ],
                id: 'location_type_id',
                url: url,
                pager: function (pagenum, pagesize, oldpagenum) {
                    // callback called when a page or page size is changed.
                }
            };
    var dataAdapter = new $.jqx.dataAdapter(source);
    $("#jqxgrid").jqxGrid(
            {
                width: '100%',
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
                filterable: true,
                columns: [
                    {text: 'Location Type Id', datafield: 'location_type_id', width: 150},
                    {text: 'Location Type Name', datafield: 'location_type_name', width: 150},
                    {text: 'Manufacturer Id', datafield: 'manufacturer_id', width: 150},
                    {text: 'actions', datafield: 'actions', width: 250}
                    //{ text: 'Actions', datafield: 'actions',width:200 }
                ]
            });
    makePopupAjax($('#basicvalCodeModal'));
    makePopupEditAjax($('#basicvalCodeModal1'), 'location_type_id');
});
function deleteEntityType(location_type_id)
{
    var decission = confirm("Are you sure you want to Delete.");
    if (decission == true)
        window.location.href = '/customer/deletelocationtype/' + location_type_id;
}


</script>    
@stop


