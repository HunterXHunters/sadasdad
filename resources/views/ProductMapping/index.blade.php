@extends('layouts.default')

@extends('layouts.header')

@extends('layouts.sideview')

@section('content')


<html>
<head>
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
            var url = "/productmap/getData";
            
            // prepare the data
            var source =
            {
                datatype: "json",
                datafields: [
                    { name: 'name', type: 'string' },
                    { name: 'channels', type: 'string' },
                    { name: 'actions', type: 'string' }

                    
                   // { name: 'delete', type: 'string' }
                ],
                id: 'product_id',
                url: url,
                pager: function (pagenum, pagesize, oldpagenum) {
                    // callback called when a page or page size is changed.
                }
            };
            var dataAdapter = new $.jqx.dataAdapter(source);
            $("#jqxgrid").jqxGrid(
            {
                width: 1000,
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
                  { text: 'ProductName', datafield: 'name', width: 300 },
                  { text: 'ChannelName', datafield: 'channels', width:250},

                    { text: 'Actions', datafield: 'actions',width:450 }
                ]               
            });
            
   makePopupAjax($('#basicvalCodeModal'));
        }); 



    
    </script>    
@stop


</head>
<body>
<div class="container">

<!-- breadcrumbs -->
     
      <!-- /breadcrumbs --> 
<div class="main">

 <div class="tile-header">
                    <h3>Channel Products Mapping</h3>
                  </div>
                  

<div id="jqxgrid">
  </div>


 </div>


 <!-- Modal -->
                    <div class="modal fade" id="basicvalCodeModal" tabindex="-1" role="dialog" aria-labelledby="basicvalCode" aria-hidden="true">
                      <div class="modal-dialog wide">
                        <div class="modal-content">
                          <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">X</button>
                            <h4 class="modal-title" id="basicvalCode">Select Channels</h4>
                          </div>
                          <div class="modal-body">   

                              {{ Form::open(array('url' => 'productmap/store')) }}
                            {{ Form::hidden('_method', 'POST') }}
                           
                   <div class="row">
                         <div class="checkbox" style="padding-left:0px !important">                                                        
                                                            <label class="col-sm-4 control-label" for="is_gds_enabled" style="width: 100%;"> 
                                                            <input type="checkbox"  name="chk[]" value="1">Ebay</label>
                                                        </div>
                      </div>
                   <div class="row">
                         <div class="checkbox" style="padding-left:0px !important">                                                        
                                                            <label class="col-sm-4 control-label" for="is_gds_enabled" style="width: 100%;"> 
                                                            <input type="checkbox"  name="chk[]" value="2">Flipkart</label>
                                                        </div>
                                                         </div>
                                                         <div class="row">
                         <div class="checkbox" style="padding-left:0px !important">                                                        
                                                            <label class="col-sm-4 control-label" for="is_gds_enabled" style="width: 100%;"> 
                                                            <input type="checkbox"  name="chk[]" value="3" >Amazon</label>
                                                        </div>
                                                         </div>
                                                    <div align="center">
      {{ Form::submit('Submit', array('class' => 'btn btn-primary')) }}
            {{Form::close()}}
    </div>
                          </div>
                        </div><!-- /.modal-content -->
                      </div><!-- /.modal-dialog -->
                    </div><!-- /.modal -->
                  </div>



@stop
@extends('layouts.footer')

