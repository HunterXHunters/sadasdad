 @extends('layouts.default')

@extends('layouts.sideview')

@section('content')
<aside class="right-side">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            UOM Groups
            <small>List</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href=""><i class="fa fa-dashboard"></i> Home</a></li>
       <!--      <li class="active">Dashboard</li> -->
        </ol>
    </section>

    <!-- Main content -->
    <section class="content">
    <!--box-start-->
    <div class="box">
    
      <div class="box-body table-responsive">
                    <div> 
                    <!-- <div>
                        <font size="5px;" color="green">List of Uom Groups</font>
                        </div> -->
                        <div style="padding-top:5px;float-right:200px;"> 
                            <font size="5px;" color="green"><a href="uomgroup/create">Add</a></font> 
                        </div>
                        <div id="jqxgrid">
                            </div>
                   
                        
                    </div>
                  </div>
    </div>
    <!-- /.box-end -->
    
  </section><!-- /.content -->
</aside><!-- /.right-side -->

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
    <script type="text/javascript">
        $(document).ready(function () {           
            var url = "uomgroup/getdata";

            // prepare the data
            var source =
            {
                datatype: "json",
                datafields: [
                    { name: 'description', type: 'string' },
                    { name: 'status', type: 'string' },
                   
                    { name: 'edit', type: 'string' },
                    { name: 'delete', type: 'string' }
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
                width: 400,
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
                  { text: 'Description', filtercondition: 'starts_with', datafield: 'description', width: 100 },
                  
                  { text: 'Status', datafield: 'status', width: 100 },
                  { text: 'Edit', datafield: 'edit',width: 100 },
                  { text: 'Delete', datafield: 'delete',width : 100 }
                ]               
            });

            if($("#saveState").length)$("#saveState").jqxButton({ theme: theme });
            if($("#loadState").length)$("#loadState").jqxButton({ theme: theme });
            var state = null;
            $("#saveState").click(function () {
                // save the current state of jqxGrid.
                state = $("#jqxgrid").jqxGrid('savestate');
            })
            ;
            $("#loadState").click(function () {
                // load the Grid's state.
                if (state) {
                    $("#jqxgrid").jqxGrid('loadstate', state);
                }
                else {
                    $("#jqxgrid").jqxGrid('loadstate');
                }
            });
        });   

         function deleteUomgroup(id)
        {
            
            var decission = confirm("Are you sure you want to Delete.");
            if(decission==true)
                window.location.href='uomgroup/delete/'+id;
        }     
       
    </script>    
@stop

