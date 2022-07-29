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
                  <button class="btn " data-toggle="modal" data-target="#basicvalCodeModal">
                    Add User
                  </button>
                  <br/><br/>
                  <!-- /show code btn -->


                  <!-- Modal -->
                  <div class="modal fade" id="basicvalCodeModal" tabindex="-1" role="dialog" aria-labelledby="basicvalCode" aria-hidden="true">
                    <div class="modal-dialog wide">
                      <div class="modal-content">
                        <div class="modal-header">
                          <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                          <h4 class="modal-title" id="basicvalCode">Store</h4>
                        </div>
                        <div class="modal-body">
                          <h1>Adding a New User</h1>
                            {{ Form::open(array('url' => 'grid/store')) }}
                            {{ Form::hidden('_method', 'POST') }}
                            <table border="1">
                              <tr>
                                <td>Name</td>
                                <td><input type="text" id="name" name="name" /></td>
                              </tr>
                              <tr>
                                <td>Email Id</td>
                                <td><input type="text" id="email_id" name="email_id" /></td>
                              </tr>
                              <tr>
                                <td>Phone Number</td>
                                <td><input type="text" id="phone" name="phone" /></td>
                              </tr>

                            </table>

                            {{ Form::submit('Submit') }}
                            {{ Form::close() }}
                        </div>
                      </div><!-- /.modal-content -->
                    </div><!-- /.modal-dialog -->
                  </div><!-- /.modal -->


                   <!-- Modal -->
                  <div class="modal fade" id="basicvalCodeModal1" tabindex="-1" role="dialog" aria-labelledby="basicvalCode" aria-hidden="true">
                    <div class="modal-dialog wide">
                      <div class="modal-content">
                        <div class="modal-header">
                          <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                          <h4 class="modal-title" id="basicvalCode">Edit</h4>
                        </div>
                        <div class="modal-body">
                        {{ Form::open(array('url' => 'grid/update/', 'data-url' => 'grid/update/')) }}
                            {{ Form::hidden('_method', 'PUT') }}
                            <table border="1">
                              <tr>
                                <td>Name</td>
                                <td><input type="text" id="name" name="name" value=""/></td>
                              </tr>
                              <tr>
                                <td>Email Id</td>
                                <td><input type="text" id="email_id" name="email_id" value="" /></td>
                              </tr>
                              <tr>
                                <td>Phone Number</td>
                                <td><input type="text" id="phone" name="phone" value="" /></td>
                              </tr>

                            </table>
                        {{ Form::submit('Update') }}
                        </div>
                      </div><!-- /.modal-content -->
                    </div><!-- /.modal-dialog -->
                  </div><!-- /.modal -->



  <div id="jqxgrid">
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
            var url = "grid/getUsers";
            // prepare the data
            var source =
            {
                datatype: "json",
                datafields: [
                    { name: 'name', type: 'string' },
                    { name: 'email_id', type: 'string' },
                    { name: 'status', type: 'string' },                    
                    { name: 'phone', type: 'integer' },
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
                width: "100%",
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
                  { text: 'User Name', filtercondition: 'starts_with', datafield: 'name', width: "20%" },
                  { text: 'Email Id', datafield: 'email_id', width:"20%"},
                  { text: 'Status', datafield: 'status', width: "20%" },               
                  { text: 'Phone', datafield: 'phone', width:"20%"},
                  //{ text: 'Edit', datafield: 'edit' },
                  { text: 'Actions', datafield: 'actions',width:"20%" }
                ]               
            });
            
            makePopupAjax($('#basicvalCodeModal'));
            makePopupEditAjax($('#basicvalCodeModal1'));
        });         
    </script>    
@stop