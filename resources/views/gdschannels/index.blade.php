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
  
            var url = "getCustomers";
//alert("hi");
            var source =

            {

                datatype: "json",
                    datafields: [
                    { name: 'channel_logo', type: 'string' },
                  /* { name: 'channel_id', type: 'string' },*/
                    { name: 'channnel_name', type: 'string' },
                    { name: 'channel_url', type: 'string' },
                       { name: 'price_url', type: 'string' },
                          { name: 'tnc_url', type: 'string' },
                    { name: 'actions', type: 'string' },

                ],

                id: 'channel_id',

                url: url,

                pager: function (pagenum, pagesize, oldpagenum) {

                    // callback called when a page or page size is changed.

                }

            };
           

            var dataAdapter = new $.jqx.dataAdapter(source);
            var photorenderer = function (row, column, value) {
               var name = $('#jqxgrid').jqxGrid('getrowdata', row).channel_logo;
               

              
                    var imgurl = name;// + name.toLowerCase() + '.png';
                var img = '<div style="background: white;"><img style="margin:2px; margin-left: 4px;" width="100" height="32" src="' + imgurl + '"></div>';
                return img;
/*
                if(channel_id = '1'){
                var imgurl = '/uploads/channels/ebay_logo.png';// + name.toLowerCase() + '.png';
                var img = '<div style="background: white;"><img style="margin:2px; margin-left: 10px;" width="32" height="32" src="' + imgurl + '"></div>';
                return img;  
                }*/
                


                /*var imgurl = '/uploads/profile_picture/'+name;// + name.toLowerCase() + '.png';
                var img = '<div style="background: white;"><img style="margin:2px; margin-left: 10px;" width="32" height="32" src="' + imgurl + '"></div>';
                return img;*/
            }

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

{ text: 'Channel logo',  datafield: 'channel_logo', cellsrenderer: photorenderer },
             
                  
                  { text: 'Channel Name', datafield: 'channnel_name', width: "10%" },
                  
                  { text: 'Channel URL',  datafield: 'channel_url', width: "20%" },
                  { text: 'Price URL',  datafield: 'price_url', width: "20%" },
                  { text: 'TNC URL',  datafield: 'tnc_url', width: "15%" },

                
                 

                  //{ text: 'Edit', datafield: 'edit' },

                  { text: 'Actions', datafield: 'actions',width: "25%" }


                ]              

            });

            makePopupAjax($('#basicvalCodeModal'));
            makePopupEditAjax($('#basicvalCodeModal1'), 'channel_id');
             makePopupEditAjax($('#basicvalCodeModal2'), 'channel_id');
        });

 

function deleteEntityType(channel_id)

        {

            var decission = confirm("Are you sure you want to Delete.");

            if(decission==true)

                window.location.href='delete/'+channel_id;

        }

   

    </script>   

@stop

</head>

<body>

<div class="container">

   @if (Session::has('message'))

   <div class="flash alert">

       <p>{{ Session::get('message') }}</p>

   </div>

   @endif


<div class="main">

 

 

 

   <!-- show code btn -->

                  <button class="btn btn-primary" data-toggle="modal" data-target="#basicvalCodeModal" onclick="getAddPage();">

                    Create Channel

                  </button>

                  <br/><br/>

                  <!-- /show code btn -->

 

<div id="jqxgrid">

  </div>

                  <!-- Modal -->

                  <div class="modal fade" id="basicvalCodeModal" tabindex="-1" role="dialog" aria-labelledby="basicvalCode" aria-hidden="true">

                    <div class="modal-dialog wide">

                      <div class="modal-content">

                        <div class="modal-header">

                          <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>

                          <h4 class="modal-title" id="basicvalCode">Create Channel</h4>

                        </div>

                        <div class="modal-body" id="userDiv">

                          

                           

                        </div>

                      </div><!-- /.modal-content -->

                    </div><!-- /.modal-dialog -->

                  </div><!-- /.modal -->

 

 

                   <!-- Modal -->

                  <div class="modal fade" id="basicvalCodeModal1" tabindex="-1" role="dialog" aria-labelledby="basicvalCode" aria-hidden="true">

                    <div class="modal-dialog wide">

                      <div class="modal-content">

                        <div class="modal-header">

                          <button type="button" class="close" data-dismiss="modal" aria-hidden="true" onclick="getEditPage()">x</button>

                          <h4 class="modal-title" id="basicvalCode">Edit Channel</h4>

                        </div>

                        <div class="modal-body" id="editDiv">




                        </div>

                      </div><!-- /.modal-content -->

                    </div><!-- /.modal-dialog -->

                  </div><!-- /.modal -->

                  <div class="modal fade" id="basicvalCodeModal2" tabindex="-1" role="dialog" aria-labelledby="basicvalCode" aria-hidden="true">

                    <div class="modal-dialog wide">

                      <div class="modal-content">

                        <div class="modal-header">

                          <button type="button" class="close" data-dismiss="modal" aria-hidden="true" onclick="getEditCredPage()">x</button>

                          <h4 class="modal-title" id="basicvalCode">Edit Credentials</h4>

                        </div>

                        <div class="modal-body" id="editCredDiv">




                        </div>

                      </div><!-- /.modal-content -->

                    </div><!-- /.modal-dialog -->

                  </div><!-- /.modal -->
</div>
 
<script type="text/javascript">
  function getAddPage()
  {
    $.get('add',function(data){
        $("#userDiv").html(data);
    });
  }
</script>

 <script type="text/javascript">
  function getEditPage(id)
  {
     var serve = window.location.origin;

    $.get(serve+'/gdschannels/edit/'+id,function(data){ 
        $("#editDiv").html(data);
    });
  }
</script>
<script type="text/javascript">
  
    function getEditCredPage(id)
  {
     var serve = window.location.origin;

    $.get(serve+'/gdschannels/edit_credentials/'+id,function(data){ 
        $("#editCredDiv").html(data);
    });
  }

</script>


 

 

 




 

@stop