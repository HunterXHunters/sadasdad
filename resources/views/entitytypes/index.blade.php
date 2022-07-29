@extends('layouts.default')
@extends('layouts.header')
@extends('layouts.sideview')
@section('content')




<div class="box">

<div class="box-header">
<h3 class="box-title">Entity Types</h3>
<span style="float:right;"><a href="entitytypes/create"><i class="fa fa-plus-circle"></i> <span>Add</span></a></span>
</div>





<div class="col-sm-12">
<div class="tile-body nopadding">
<div id="jqxgrid"></div>
</div>
</div>

</div>

@stop

@section('style')
    {{HTML::style('jqwidgets/styles/jqx.base.css')}}
     
@stop

@section('script')
    {{HTML::script('jqwidgets/jqxcore.js')}}
    {{HTML::script('jqwidgets/jqxbuttons.js')}}
    {{HTML::script('jqwidgets/jqxscrollbar.js')}}
    {{HTML::script('jqwidgets/jqxmenu.js')}}
    {{HTML::script('jqwidgets/custom_jqxgrid.js')}}
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
    <script type="text/javascript">
    $(document).ready(function () 
        {           
            var url = "entitytypes/getdata";
            // prepare the data
            var source =
            {
                datatype: "json",
                datafields: [
                    { name: 'entity_type_name', type: 'string' },
                    { name: 'status', type: 'string' },
                    { name: 'created_date', type: 'string' },
                    { name: 'updated_date', type: 'string' },
                    { name: 'actions', type: 'string' },
                   // { name: 'delete', type: 'string' }
                ],
                id: 'id',
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
                  { text: 'Entity Type Name', filtercondition: 'starts_with', datafield: 'entity_type_name', width: "20%" },
                  { text: 'Status', datafield: 'status', width: "20%" },
                  { text: 'Created Date', datafield: 'created_date', width:"20%"},
                  { text: 'Updated Date', datafield: 'updated_date', width:"20%"},
                  //{ text: 'Edit', datafield: 'edit' },
                  { text: 'Actions', datafield: 'actions',width:"20%" }
                ]               
            });            
        });    
        function deleteEntityType(id)
        {
            var decission = confirm("Are you sure you want to Delete.");
            if(decission==true)
                window.location.href='entitytypes/delete/'+id;
        }
    </script>    
@stop