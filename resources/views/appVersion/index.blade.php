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
    

    <script type="text/javascript">
    
    $(document).ready(function () 
        {            
            var url = "/appVersion/show";
            
            // prepare the data
            var source =
            {
                datatype: "json",
                datafields: [
                    { name: 'id', type: 'integer' },
                    { name: 'db_update_needed', type: 'integer' },
                    { name: 'config_reset', type: 'integer' },
                    { name: 'latest_version', type: 'string' },
                    { name: 'release_date', type: 'string' },
                    { name: 'download_link', type: 'string' },                    
                    { name: 'app_id', type: 'string' }, 
                    { name: 'actions', type: 'string' }
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
                  { text: 'Id', filtercondition: 'starts_with', datafield: 'id', width: "10%" },
                  { text: 'Database Update', datafield: 'db_update_needed', width: "10%" },
                  { text: 'Configuration Reset', datafield: 'config_reset', width: "10%" },
                  { text: 'Version', datafield: 'latest_version', width: "10%" },
                  { text: 'Release Date', datafield: 'release_date', width:"10%"},
                  { text: 'Download Link', datafield: 'download_link', width: "30%" },               
                  { text: 'App Id', datafield: 'app_id', width: "10%" },
                  { text: 'Actions', datafield: 'actions',width:"10%" }
                ]               
            });
            
          }); 

function deleteEntityType(id)
        {
            var decission = confirm("Are you sure you want to Delete.");
            if(decission==true)
                window.location.href='appVersion/delete/'+id;
        }
    
    </script> 

<script type="text/javascript">
$(document).ready(function(){
    $("#upload").click(function(){
        $.get($(this).attr('data-url'),function(response){
            $("#basicvalCode").html('Create New app Version');
            $("#appVersionsDiv").html(response);
        });
    });
    
});
</script>
<script type="text/javascript">
function editAppVersion(id)
{
  console.log(id);
     $.get('/appVersion/edit/'+id,function(response){ 
            $("#basicvalCode").html('Create New app Version');
            
            $("#appVersionsDiv").html(response);
            
            $("#editAppVersion").click();
        });
}
</script>   
@stop

  


     @if (Session::has('message'))
     <div class="flash alert">
         <p>{{ Session::get('message') }}</p>
     </div>
     @endif

 <div class="box">
              <div class="box-header">
                <h3 class="box-title"><strong>App Version </strong> </h3>
                <a href="javascript:void(0)" id="upload" data-toggle="modal" class="pull-right" data-target="#basicvalCodeModal" data-url="{{URL::asset('appVersion/create')}}"><i class="fa fa-plus-circle"></i> <span>Create</span></a>
               <button data-toggle="modal" id="editAppVersion" class="btn btn-default" data-target="#basicvalCodeModal" style="display: none" data-url="{{URL::asset('appVersion/edit')}}"></button>
              </div>
                     
  
            <div id="jqxgrid">
<!--             <button data-toggle="modal" id="editAppVersion" class="btn btn-default" data-target="#basicvalCodeModal" style="display: none" data-url="{{URL::asset('appVersion/edit')}}"></button> -->
              
            </div>
             
</div>

  <!-- Modal -->
                    <div class="modal fade" id="basicvalCodeModal" tabindex="-1" role="dialog" aria-labelledby="basicvalCode" aria-hidden="true">
                      <div class="modal-dialog wide">
                        <div class="modal-content">
                          <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">X</button>
                            <h4 class="modal-title" id="basicvalCode">Create New App Version</h4>
                          </div>
                          <div class="modal-body">                         
                              <div class="modal-body" id="appVersionsDiv">
                              </div>
                          </div>
                        </div><!-- /.modal-content -->
                      </div><!-- /.modal-dialog -->
                    </div><!-- /.modal -->


           <!-- Modal - Popup for Verify User Password while deleting -->
  @stop

