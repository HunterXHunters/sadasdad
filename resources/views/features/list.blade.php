@extends('layouts.default')
@extends('layouts.header')
@extends('layouts.sideview')

@section('style')
    {{HTML::style('jqwidgets/styles/jqx.base.css')}}
     
@stop

@section('script')
    
{{HTML::script('jqwidgets/jqxcore.js')}}
{{HTML::script('jqwidgets/jqxdata.js')}}
{{HTML::script('jqwidgets/jqxbuttons.js')}}
{{HTML::script('jqwidgets/jqxscrollbar.js')}}
{{HTML::script('jqwidgets/jqxdatatable.js')}}
{{HTML::script('jqwidgets/jqxtreegrid.js')}}
{{HTML::script('scripts/demos.js')}}

<script type="text/javascript">
    $(document).ready(function () 
    {
        var source =
        {
            datatype: "json",
            datafields: [
            { name: 'name', type: 'string' },
            { name: 'featureName', type: 'string' },
            { name: 'child', type: 'array' },
            { name: 'expanded', type: 'bool' }
            ],
            hierarchy:
            {
                root: 'child'
            },
            id: 'feature_id',
            url: 'getdata',
            pager: function (pagenum, pagesize, oldpagenum) {
                // callback called when a page or page size is changed.
            }
            
        };
        var dataAdapter = new $.jqx.dataAdapter(source);
        // create Tree Grid
        $("#treeGrid").jqxTreeGrid(
        {
            width: '100%',
            source: dataAdapter,
            sortable: true,
            //autoheight: true,
            //autowidth: true,
            columns: [
              { text: 'Module Name', datafield: 'name', width:'20%'},                        
              { text: 'Feature Name', datafield: 'featurename', width:'20%'},
              { text: 'Featurecode',  datafield: 'feature_code', width: '20%' },
              { text: 'State', datafield: 'is_active', width: '20%' },
              { text: 'Actions', datafield: 'actions',width:'20%' }
            ]
        });
        
    }); 
</script>    
@stop
@section('content')

<div class="main margleft">
<div id="treeGrid"></div>
</div>

@stop