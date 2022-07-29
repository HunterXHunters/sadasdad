@extends('layouts.default')

@extends('layouts.header')

@extends('layouts.sideview')

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
            var url = "/subscribeapis/getData/"+'{{$customerId}}';
            
            // prepare the data
            var source =
            {
                datatype: "json",
                datafields: [
                    { name: 'channel_logo', type: 'string', cellsalign: 'center' },
                    { name: 'channnel_name', type: 'string' },
                    { name: 'price_url', type: 'string' },
                    { name: 'tnc_url', type: 'string' },
                    { name: 'Subscription', type: 'string' }

                    
                   // { name: 'delete', type: 'string' }
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
                //alert(imgurl);
                var img = '<div style="background: white;"><img style="margin:2px; margin-left: 2px;" width="100" height="32" src="' + imgurl + '"></div>';
                return img;
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
                  { text: 'Channel logo',  datafield: 'channel_logo', cellsrenderer: photorenderer, width : "15%" },  
                  { text: 'Cannel name', datafield: 'channnel_name', width: '25%' },
                  { text: 'Channel Charges ', datafield: 'price_url', width:'15%'},
                  { text: 'Channel Terms & Condition', datafield: 'tnc_url', width:'15%'},
                  { text: 'Subscription', datafield: 'Subscription',width:'30%' }
                ]               
            });
            

        }); 
        function popup(name){
            var customer_id = $("#customer_id").val();
            if(customer_id=='') {
                if($("#chk"+name).attr('checked'))
                    $("#chk"+name).prop('checked',true);
                else
                    $("#chk"+name).prop('checked',false);
                alert("Please select Manufacturer");
            }else { 
                if($("#chk"+name).prop('checked')==false){
                    if(confirm("Are you sure you unsubscribe this channel")) {
                        var status = 0;
                        subscribeChannel($("#chk"+name).val(), status)
                    }else{
                        $("#chk"+name).prop('checked',true);
                    }

                }
                else{
                    if(confirm("Are you sure you subscribe this channel. Before enable please read terms and condition and charges of channel.")) {
                        var status = 1;
                        subscribeChannel($("#chk"+name).val(), status)
                    }else {
                        $("#chk"+name).prop('checked',false);
                    }
                    
                }
            }
        }
        
        function subscribeChannel(name, status){ 
            var customer_id = $("#customer_id").val();
            $.get('/subscribeapis/Store/',{channel_id:name,status:status,customerId:customer_id},function(response){
                if(status == 1 && response=='success')
                    alert("You are subscribe for channel")
                else
                    alert("You are unsubscribe for channel")
            });
          /*$.ajax(
              {
                url: "/subscribeapis/Store/",
                type: "POST", 
                data: "channel_id=" +name + "&status=" +status+"&customerId="+customer_id,
                success: function(response)
                {
                  alert(response);
                  //window.location = "/subscribeapis/index/";
                },

                error:function()
                {
                }   
              }
            );*/  
        }    
        
        /**/   
       
    </script>    
@stop
@section('content')
    <div class="box">
        <div class="box-header">
            <h3 class="box-title"><strong>Channel </strong>  Subscription</h3>
            @if(Session::get('customerId')=='') 
            <form name="subscfrm" action="/subscribeapis/index" method="post">
            <div class="pull-right"> 
                <select class="form-control select2" name="customer_id" id="customer_id" onchange="document.subscfrm.submit();">
                    <option value="">Select Manafucturer</option>
                    @foreach($customers as $customer)
                        <option value="{{$customer->customer_id}}" @if($customer->customer_id==$customerId) selected="selected" @endif>{{$customer->brand_name}}</option>
                    @endforeach
                </select>
            </div>
            @else
                <input type="hidden" name="customer_id", id="customer_id" value="{{$customerId}}">
            @endif
        
        <div class="col-sm-12">
            <div class="tile-body nopadding">                  
            <div id="jqxgrid"  style="width:100% !important;"></div>
             
            </div>
        </div>
    </div>

@stop

<?PHP /*@extends('layouts.default')
                    

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
            var url = "/subscribeapis/getData";
            
            // prepare the data
            var source =
            {
                datatype: "json",
                datafields: [
                    { name: 'channnel_name', type: 'string' },
                    { name: 'channel_url', type: 'string' },
                    { name: 'Subscription', type: 'string' }

                    
                   // { name: 'delete', type: 'string' }
                ],
                id: 'channel_id',
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
                  { text: 'channnel_name', datafield: 'channnel_name', width: 300 },
                  { text: 'channel_url', datafield: 'channel_url', width:250},
                  { text: 'Subscription', datafield: 'Subscription',width:450 }
                ]               
            });
            

        }); 

   
   function test(name){
    
    
    var status=document.getElementById('chk'+name).checked;
    //alert(status);
    
    $.ajax
    (
      {
        url: "/subscribeapis/Store",
        type: "GET", 
        data: "name=" +name + "&status=" +status,
        success: function()
        {
          
          //window.location = "/subscribeapis/index/";
        },

        error:function()
        {
        }   
      }
    );   
   }
    
    </script>    
@stop


</head>
<body>
<div class="container">

<div class="main">

 <div class="tile-header">
                    <h3>Channel Subscription</h3>
                  </div>
                  
<div id="jqxgrid">
    
  </div>
  <html>
<body>



</body>
</html>
 </div>
@stop
@extends('layouts.footer') 

   <!--   makePopupAjax($('#basicvalCodeModal'));      
          makePopupEditAjax($('#basicvalCodeModal1'), 'id'); -->

    <!-- if(!$user_id){
        $user_id = Session::get('userId');
        }
        
        $user_details=$this->custRepo->getUserDetails($user_id);
        $cust_id=$user_details[0]->customer_id; -->
*/ ?>        