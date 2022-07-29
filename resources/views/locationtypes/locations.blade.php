@extends('layouts.default')
@extends('layouts.header')
@extends('layouts.sideview')
@section('content')

<!-- breadcrumbs -->
<div class="breadcrumbs">
    <ol class="breadcrumb">
        <li><a href="#">Home</a></li>
        <li><a href="#">Dashboard</a></li>
        <li class="active">Overview</li>
    </ol>
</div>
<!-- /breadcrumbs --> 

<div class="main">
    <!-- show code btn -->      
    <button class="btn btn-primary" data-toggle="modal" data-target="#basicvalCodeModal">ADD</button> 
    <br/> </br>

    <!-- Modal - Popup for ADD -->
    <div class="modal fade" id="basicvalCodeModal" tabindex="-1" role="dialog" aria-labelledby="basicvalCode" aria-hidden="true">
        <div class="modal-dialog wide">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                    <h4 class="modal-title" id="basicvalCode">Add Location</h4>
                </div>
                <div class="modal-body">

                    {{ Form::open(array('url' => 'customer/savelocation', 'class' => 'form-horizontal form1' )) }}
                    {{ Form::hidden('_method','POST') }}


                    <!-- tile body -->

                    <div class="row">
                        <div class="form-group col-sm-6">
                            <label for="exampleInputEmail">Location Name</label>
                            <div class="input-group ">
                                <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
                                <input type="text"  id="locname" name="locname" placeholder="location_name" class="form-control">
                            </div>
                        </div>
                        <div class="form-group col-sm-6">
                            <label for="exampleInputEmail">Manufacturer ID</label>
                            <div class="input-group ">
                                <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
                                <input type="text"  id="manid" name="manid" placeholder="manufacturer_id" class="form-control">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="form-group col-sm-6">
                            <label for="exampleInputEmail">Parent Location ID</label>
                            <div class="input-group ">
                                <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
                                <input type="text" id="parlocid" name="parlocid" placeholder="parent_location_id" class="form-control">
                            </div>
                        </div>
                        <div class="form-group col-sm-6">
                            <label for="exampleInputEmail">Location Type ID</label>
                            <div class="input-group ">
                                <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
                                <input type="text" id="loctypid" name="loctypid" placeholder="location_type_id" class="form-control">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="form-group col-sm-6">
                            <label for="exampleInputEmail">Location Email</label>
                            <div class="input-group">
                                <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
                                <input type="text" id="locmail" name="locmail" placeholder="location_email" class="form-control">
                            </div>                        
                        </div>
                        <div class="form-group col-sm-6">
                            <label for="exampleInputEmail">Location Address</label>
                            <div class="input-group ">
                                <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
                                <input type="text" id="locadd" name="locadd" placeholder="location_address" class="form-control">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="form-group col-sm-6">
                            <label for="exampleInputEmail">Location Details</label>
                            <div class="input-group">
                                <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
                                <input type="text" id="locdet" name="locdet" placeholder="location_details" class="form-control">
                            </div>                        
                        </div>
                        <div class="form-group col-sm-6">
                            <label for="exampleInputEmail">State</label>
                            <div class="input-group ">
                                <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
                                <input type="text" id="state" name="state" placeholder="state" class="form-control">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="form-group col-sm-6">
                            <label for="exampleInputEmail">Region</label>
                            <div class="input-group">
                                <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
                                <input type="text" id="region" name="region" placeholder="region" class="form-control">
                            </div>                        
                        </div>
                        <div class="form-group col-sm-6">
                            <label for="exampleInputEmail">Longitude</label>
                            <div class="input-group ">
                                <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
                                <input type="text" id="long" name="long" placeholder="longitude" class="form-control">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="form-group col-sm-6" >
                            <label for="exampleInputEmail">Latitude</label>
                            <div class="input-group" >
                                <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
                                <input type="text" id="lat" name="lat" placeholder="latitude" class="form-control">
                            </div>                        
                        </div>
                        <div class="form-group col-sm-6">
                            <label for="exampleInputEmail">ERP Code</label>
                            <div class="input-group ">
                                <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
                                <input type="text" id="sap" name="sap" placeholder="erp_code" class="form-control">
                            </div>
                        </div>
                    </div>

                    {{ Form::submit('Add', array('class' => 'btn btn-primary')) }}
                    {{Form::close()}}

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
                    <h4 class="modal-title" id="basicvalCode">Edit</h4>
                </div>
                <div class="modal-body">
                    {{ Form::open(array('url' => 'customer/updatelocation','data-url' => 'customer/updatelocation/')) }}
                    {{ Form::hidden('_method','PUT') }}
                    <!-- tile header -->


                    <div class="tile-header">
                        <h1>Edit Location</h1>
                    </div>
                    <!-- /tile header -->
                    <!-- tile body -->

                    <div class="row">
                        <div class="form-group col-sm-6">
                            <label for="exampleInputEmail">Location Name</label>
                            <div class="input-group ">
                                <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
                                <input type="text"  id="location_name" name="location_name" value="" class="form-control">
                            </div>
                        </div>
                        <div class="form-group col-sm-6">
                            <label for="exampleInputEmail">Manufacturer ID</label>
                            <div class="input-group ">
                                <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
                                <input type="text"  id="manufacturer_id" name="manufacturer_id" value="" class="form-control">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="form-group col-sm-6">
                            <label for="exampleInputEmail">Parent Location ID</label>
                            <div class="input-group ">
                                <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
                                <input type="text" id="parent_location_id" name="parent_location_id" value="" class="form-control">
                            </div>
                        </div>
                        <div class="form-group col-sm-6">
                            <label for="exampleInputEmail">Location Type ID</label>
                            <div class="input-group ">
                                <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
                                <input type="text" id="location_type_id" name="location_type_id" value="" class="form-control">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="form-group col-sm-6">
                            <label for="exampleInputEmail">Location Email</label>
                            <div class="input-group">
                                <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
                                <input type="text" id="location_email" name="location_email" value="" class="form-control">
                            </div>                        
                        </div>
                        <div class="form-group col-sm-6">
                            <label for="exampleInputEmail">Location Address</label>
                            <div class="input-group ">
                                <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
                                <input type="text" id="location_address" name="location_address" value="" class="form-control">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="form-group col-sm-6">
                            <label for="exampleInputEmail">Location Details</label>
                            <div class="input-group">
                                <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
                                <input type="text" id="location_details" name="location_details" value="" class="form-control">
                            </div>                        
                        </div>
                        <div class="form-group col-sm-6">
                            <label for="exampleInputEmail">State</label>
                            <div class="input-group ">
                                <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
                                <input type="text" id="state" name="state" value="" class="form-control">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="form-group col-sm-6">
                            <label for="exampleInputEmail">Region</label>
                            <div class="input-group">
                                <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
                                <input type="text" id="region" name="region" value="" class="form-control">
                            </div>                        
                        </div>
                        <div class="form-group col-sm-6">
                            <label for="exampleInputEmail">Longitude</label>
                            <div class="input-group ">
                                <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
                                <input type="text" id="longitude" name="longitude" value="" class="form-control">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="form-group col-sm-6" >
                            <label for="exampleInputEmail">Latitude</label>
                            <div class="input-group" >
                                <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
                                <input type="text" id="latitude" name="latitude" value="" class="form-control">
                            </div>                        
                        </div>
                        <div class="form-group col-sm-6">
                            <label for="exampleInputEmail">ERP Code</label>
                            <div class="input-group ">
                                <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
                                <input type="text" id="erp_code" name="erp_code" value="" class="form-control">
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

<!-- Include all compiled plugins (below), or include individual files as needed -->
<script src="js/bootstrap.min.js"></script>

<script type="text/javascript">
$(document).ready(function ()
{
    var url = "customer/getLocations";
    // prepare the data
    var source =
            {
                datatype: "json",
                datafields: [
                    {name: 'location_id', type: 'integer'},
                    {name: 'location_name', type: 'string'},
                    {name: 'manufacturer_id', type: 'integer'},
                    {name: 'parent_location_id', type: 'integer'},
                    {name: 'location_type_id', type: 'integer'},
                    {name: 'location_email', type: 'string'},
                    {name: 'location_address', type: 'string'},
                    {name: 'location_details', type: 'string'},
                    {name: 'state', type: 'string'},
                    {name: 'region', type: 'string'},
                    {name: 'longitude', type: 'integer'},
                    {name: 'latitude', type: 'integer'},
                    {name: 'erp_code', type: 'string'},
                    {name: 'actions', type: 'string'}
                    // { name: 'delete', type: 'string' }
                ],
                id: 'location_id',
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
                    {text: 'Location ID', datafield: 'location_id', width: 100},
                    {text: 'Location Name', filtercondition: 'starts_with', datafield: 'location_name', width: 250},
                    {text: 'Manufacturer ID', datafield: 'manufacturer_id', width: 100},
                    {text: 'Parent location ID', datafield: 'parent_location_id', width: 100},
                    {text: 'Location Type ID', datafield: 'location_type_id', width: 100},
                    {text: 'Location Email', datafield: 'location_email', width: 200},
                    {text: 'Location Address', datafield: 'location_address', width: 200},
                    {text: 'Location Details', datafield: 'location_details', width: 200},
                    {text: 'State', datafield: 'state', width: 200},
                    {text: 'Region', datafield: 'region', width: 200},
                    {text: 'Longitude', datafield: 'longitude', width: 200},
                    {text: 'Latitude', datafield: 'latitude', width: 200},
                    {text: 'SAP Code', datafield: 'erp_code', width: 200},
                    {text: 'Actions', datafield: 'actions', width: 200}
                ]
            });

    makePopupAjax($('#basicvalCodeModal'));
    makePopupEditAjax($('#basicvalCodeModal1'), 'location_id');
});

function deleteEntityType(location_id)
{
    var dec = confirm("Are you sure you want to Delete ?");
    if (dec == true)
        window.location.href = '/customer/deletelocation/' + location_id;
}

</script>    
@stop