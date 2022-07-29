@extends('layouts.default')

@extends('layouts.header')

@extends('layouts.sideview')

@section('content')

<div class="row">
  
  
  
  <div class="col-md-3 col-sm-6 col-xs-12">
    <div class="info-box">
      <span class="info-box-icon bg-aqua"><i class="ion ion-ios-gear-outline"></i></span>
      <div class="info-box-content">
        <span class="info-box-text">Today's Revenue</span>
        <span class="info-box-number">{{ $TodayOrderDetails[0]->TodayRevenue}}<small>INR</small></span>
      </div><!-- /.info-box-content -->
    </div><!-- /.info-box -->
  </div><!-- /.col -->
  <div class="col-md-3 col-sm-6 col-xs-12">
    <div class="info-box">
      <span class="info-box-icon bg-red"><i class="fa fa-google-plus"></i></span>
      <div class="info-box-content">
        <span class="info-box-text">Today's Orders</span>
        <!-- <span class="info-box-number">{{ $TodayOrderDetails[1]->TodayOrders}}</span> -->
        <a class="info-box-number" href='todayorders?os=all'>{{$TodayOrderDetails[1]->TodayOrders}}</a>
      </div><!-- /.info-box-content -->
    </div><!-- /.info-box -->
  </div><!-- /.col -->
  
  <div class="col-md-3 col-sm-6 col-xs-12">
    <div class="info-box">
      <span class="info-box-icon bg-green"><i class="ion ion-ios-cart-outline"></i></span>
      <div class="info-box-content">
        <span class="info-box-text">Total Unshipped Orders</span>
        <!-- <span class="info-box-number">{{$TodayOrderDetails[2]->Unshipped}}</span> -->
        <a class="info-box-number" href='todayorders?os=Unshipped'>{{$TodayOrderDetails[2]->Unshipped}}</a>
      </div><!-- /.info-box-content -->
    </div><!-- /.info-box -->
  </div><!-- /.col -->
  <div class="col-md-3 col-sm-6 col-xs-12">
    <div class="info-box">
      <span class="info-box-icon bg-yellow"><i class="ion ion-ios-people-outline"></i></span>
      <div class="info-box-content"  >
        <span class="info-box-text">Today's Completed Orders</span>
        <!-- <span class="info-box-number">{{$TodayOrderDetails[3]->Completed}}</span> -->
        <a class="info-box-number" href="todayorders?os=Completed">{{$TodayOrderDetails[3]->Completed}}</a>
      </div><!-- /.info-box-content -->
    </div><!-- /.info-box -->
  </div><!-- /.col -->
  
</div>


<div class="row">
    <div class="col-md-12">
    <section class="tile cornered">
      <div class="tile-header">
                <h3><strong>Orders </strong></h3>
      </div>
            <div class="tile-body nopadding">
                <div class="tile-widget nopadding">
          
        </div>
        <div class="tab-content">
          <div class="tab-pane active" id="tab1">
                        
                        <div class="tile-header">
              
            </div>
            <div class="box box-primary">
                            {{ Form::open(array('url' => '/reportapis/index','method'=>'POST','id'=>'reportfrm','name'=>'reportfrm')) }}
              
              
              <div class="row btn_holder">
                
                                <div class="form-group col-sm-3">
                  <label for="exampleInputEmail">Channel Name</label>
                  <div class="input-group ">
                    <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
                    <div id="selectbox">
                      <select class="chosen-select form-control parsley-validated" id="customer_id" name="customer_id" parsley-trigger="change" parsley-required="true" parsley-error-container="#selectbox">
                        <option value="">Please choose</option>
                        <option value="all">All</option>
                        @foreach($channels as $channels)
                                                <option value="{{$channels->channnel_name}}"@if(isset($row['customer_id']) && $row['customer_id']==$channels->channnel_name) selected="selected" @endif><?php echo strtoupper($channels->channnel_name)?></option>
                        @endforeach
                        
                        
                      </select>
                    </div>
                  </div>
                </div>
                <div class="form-group col-sm-3">
                  <label for="exampleInputEmail">Order Status</label>
                  <div class="input-group ">
                    <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
                    <select class="chosen-select form-control parsley-validated" id="order_status_id" name="order_status_id" parsley-trigger="change" parsley-required="true" parsley-error-container="#selectbox">
                      <option value="">Please choose</option>
                      @foreach($order_status as $status)  
                                            <option id = "order_status_id" name= "order_status_id" value="{{$status->order_status}}">{{$status->order_status}}</option>
                      @endforeach
                    </select>
                  </div>
                </div>
                
                
                                <div class="form-group col-sm-3">
                  <label for="exampleInputEmail">From Date</label>
                  <div class="input-group ">
                    <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
                    <input type="text" id="from_date" name="from_date" placeholder="From Date" class="form-control" value="<?PHP echo (isset($row['from_date'])) ? $row['from_date'] : '';?>" >
                  </div>
                </div>
                <div class="form-group col-sm-3">
                  <label for="exampleInputEmail">To Date</label>
                  <div class="input-group ">
                    <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
                    <input type="text" id="to_date" name="to_date" placeholder="To Date" class="form-control" value="<?PHP echo (isset($row['to_date'])) ? $row['to_date'] : '';?>" >
                  </div>
                </div>
                
                
                
                
                <div class="row">
                  <div class="form-group form-footer">
                    
                    <button type="button"   class="btn btn-primary filter_btn" onclick = "myFunction()" >Filter</button>
                    
                    <button type="reset" class="btn btn-warning filter_btn">Reset</button>
                    
                  </div>
                </div>
              </div>
              
              
            </div>
            <?PHP $urlgrid = (isset($urlgrid)) ? $urlgrid :  "orders/".$os;?>
            <input type="hidden" name="urlgrid" id="urlgrid" value="<?PHP echo $urlgrid;?>">
          </div>
          {{Form::close()}}
        </div></div></section>
        
        
  </div>
  
  
  <style>
    
    .ui-datepicker{
    position: absolute !important;
    z-index: 999999 !important;
    
    }
    .chart-legend li{
    width:33% !important;
    float:left !important;
    text-align:center;
    padding-top:50px !important;
    }
    table, th, td {
    border: 1px solid black;
    border-collapse: collapse;
    background-color: #FFFFFF
    }
    th, td {
    padding: 5px;
    text-align: left;
    }
    table#Report-Data {
    width: 100%;    
    background-color: #f1f1c1;
    }
    .filter_btn{ padding:5px 15px; margin: 10px 20px; font-size:16px; }
    .btn_holder{ margin: 10px 10px 0px 10px !important;  }
    /*.calender_style table,th, td{ 
    border:1px solid #cccccc!important; padding:10px !important; color:#333333; }
    
    .calender_style td:hover{ 
    background-color:#0275d8!important;  color:#ffffff; }*/
    
    .table_style{ padding:15px!important;border:1px solid #cccccc!important ;  }
  </style>
  
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
    


    $(document).ready(function (){
      var url = $("#urlgrid").val();
      setGrid(url);
    });
    
    function setGrid(gridUrl)
    {
      var url = gridUrl;
      var source = {
        datatype: "json",
        datafields: [
        { name: 'channel_logo',  type: 'string', cellsalign: 'center' },
        /*{ name: 'channnel_name', type: 'integer' },*/
        { name: 'channel_order_id', type: 'string' },
        { name: 'gds_order_id', type: 'string' },
        { name: 'erp_order_id', type: 'string' },
        { name: 'channel_order_status', type: 'string' },
        { name: 'order_date', type: 'string' },
        //{ name: 'payment_method', type: 'string' },
        { name: 'shipping_cost', type: 'string' },
        // { name: 'sub_total', type: 'string' },
        { name: 'tax', type: 'string' },
        { name: 'total_amount', type: 'string' },
        { name: 'actions', type: 'string' }
        ],
        id: 'channel_order_id',
        url: url,
        pager: function (pagenum, pagesize, oldpagenum) {}
      };
      var dataAdapter = new $.jqx.dataAdapter(source);
      var photorenderer = function (row, column, value) {
        var name = $('#jqxgrid').jqxGrid('getrowdata', row).channel_logo;  
        var imgurl = name;// + name.toLowerCase() + '.png';
        var img = '<div style="background: white;"><img style="margin:2px; margin-left: 2px;" width="50" height="32" src="' + imgurl + '"></div>';
        return img;
      }
      
      $("#jqxgrid").jqxGrid({
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
        showfilterrow: false,
        filterable: false,
        columns: [
        { text: 'Channel',  datafield: 'channel_logo', cellsrenderer: photorenderer},
        { text: 'Channel Order Id',  datafield: 'channel_order_id', width: "15%" },
        { text: 'ERP Order Id',  datafield: 'erp_order_id', width: "15%" },
        { text: 'GDS Order Id',  datafield: 'gds_order_id', width: "10%" },
        { text: 'Channel Order Status',  datafield: 'channel_order_status', width: "10%" },
        { text: 'Order Date',  datafield: 'order_date', width: "10%" },
        //{ text: 'Payment Method',  datafield: 'payment_method', width: "15%" },
        { text: 'Shipping Cost (INR)',  datafield: 'shipping_cost', width: "10%" },
        //        { text: 'Sub Total',  datafield: 'sub_total', width: "10%" },
        { text: 'Tax (INR)',  datafield: 'tax', width: "5%" },
        { text: ' Total Amount (INR)',  datafield: 'total_amount', width: "10%" },
        { text: 'Order Details', datafield: 'actions',width: "10%" }
        ]
      });
    }
    
  </script>   
  
  @stop
  
  
  
  
  
  <div>
    
    @if (Session::has('message'))
    
    <div class="flash alert">
      
      <p>{{ Session::get('message') }}</p>
      
    </div>
    
    @endif
    
    
    <div class="main">
      
      
      
      <div id="jqxgrid">
        
      </div>
      
      
      
    </div>
  </div>
  
  
  
  
    <script src="/js/plugins/jQuery/jQuery-2.1.4.min.js" type="text/javascript"></script>
    <script src="/js/plugins/chartjs/Chart.min.js" type="text/javascript"></script>
    <!-- jvectormap -->
    <script src="/js/plugins/jvectormap/jquery-jvectormap-1.2.2.min.js" type="text/javascript"></script>
    <script src="/js/plugins/jvectormap/jquery-jvectormap-world-mill-en.js" type="text/javascript"></script>
    <!-- Sparkline -->
    <script src="/js/plugins/sparkline/jquery.sparkline.min.js" type="text/javascript"></script>
  <script type="text/javascript">
    
    
  </script>
  
  
  <script src="{{URL::asset('js/plugins/datepicker/bootstrap-datepicker.js')}}"></script>            
  <script>
    $(function(){
      $( "#from_date" ).datepicker();
      $( "#to_date" ).datepicker();
      $( "#from_date1" ).datepicker();
      $( "#to_date1" ).datepicker();
    })
  </script>
  <script src="{{URL::asset('js/plugins/flot/jquery.flot.min.js')}}"></script>
  <script src="{{URL::asset('js/plugins/flot/jquery.flot.categories.min.js')}}"></script>
  <script src="{{URL::asset('js/plugins/graphtable/jquery.graphTable-0.3.js')}}"></script>
  <!--  <link rel="stylesheet" type="text/css" href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.7/themes/redmond/jquery-ui.css" />
    <link rel="stylesheet" type="text/css" href="http://www.ok-soft-gmbh.com/jqGrid/jquery.jqGrid-3.8.2/css/ui.jqgrid.css" />
    <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.4.4/jquery.min.js"></script>
    <script type="text/javascript" src="http://www.ok-soft-gmbh.com/jqGrid/jquery.jqGrid-3.8.2/js/i18n/grid.locale-en.js"></script>
    <script type="text/javascript" src="http://www.ok-soft-gmbh.com/jqGrid/jquery.jqGrid-3.8.2/js/jquery.jqGrid.min.js"></script>
  -->
  <script>
    
    
    
    
    
    function myFunction() {
      
      var order_status_id= document.getElementById("order_status_id").value;
      var customer_id = document.getElementById("customer_id").value;
      var from_date = new Date();
      var to_date = new Date();
      
      var date = new Date( $("#from_date").val() );
      var tdate = new Date( $("#to_date").val() );
      //alert(date);
      if(from_date < date || to_date < tdate || tdate < date)
      alert('Invalid Date');
      else { 
        if((customer_id=="") || (order_status_id =="")){
          
         alert("Please select Channel Name and Order Status");
         var serve = window.location.origin;
          
          // document.getElementById('reportfrm').action  = serve+'/reportapis/AllChannels';
          document.getElementById('reportfrm').submit();
          } else{ 
          /*
            if( customer_id=="all" && (order_status_id !=""))
            {
            
            var url =  document.getElementById("order_status_id").value;
            
            var serve = window.location.origin;
            
            document.getElementById('reportfrm').action = serve+'/reportapis/AllChannels/'+url;
            document.getElementById('reportfrm').submit();
            
            } 
          if(customer_id !="all"  && (order_status_id !="" || order_status_id=="" ))*/
          
          
          var url =  document.getElementById("customer_id").value;
          /*var s = document.getElementById("order_status_id").value;
          alert(s);*/
          
          var serve = window.location.origin;
          
          // document.getElementById('reportfrm').action = serve+'/reportapis/'+url;
          // document.getElementById('reportfrm').submit();
          
          $.ajax({
            method: "POST",
            url: serve+'/reportapis/'+url,
            data: {  }
            }).done(function( msg ) {
            setGrid(url+'?customer_id='+$("#customer_id").val()+'&order_status_id='+$("#order_status_id").val()+'&from_date='+$("#from_date").val()+'&to_date='+$("#to_date").val());
            setTimeout(function(){
              $("#jqxgrid").jqxGrid('refresh');
              $("#jqxgrid").jqxGrid('hideloadelement');
            }, 2000);
            
          });
          
          
          
        }
        
        
      }}
      
      //$( ".expand" ).on('click', function() {
      // alert( "Handler for .click() called." );
      //$(this).parent().parent().find(".some").css("display", "block");
      function expand(order_id) {
        // var order_id = $(this).parent().parent().find(".some").html();
        //alert(order_id);
        orderdetails(order_id);
      }
      // console.log($(this).parent().parent().find(".some").html());
      //});
      function orderdetails(order_id){
        
        //alert(order_id);
        var serve = window.location.origin;
        //alert(order_id);
        var order_id = order_id.replace(/\s/g,"");
        var url = serve+'/reportapis/ChannelOrderDetails/'+order_id;
        //alert(url);
        $.get(url, function (data) {
          var obj = jQuery.parseJSON(data);
          //alert(obj.length);
          var length =obj.length;
          //alert(obj[0].channel_order_item_id);
          // $.each(obj, function (){ 
          var str ='';
          str += '<table width="100%" >';
          str +='<tbody >';
          str +='<tr class= "row">';
          str += '<td class ="channe_item_id" >Channel Item Id </td>';
          str += '<td class ="channel_order_status" >Channel Order Status</td>';
          str += '<td class ="quantity" >Quantity</td>';
          str += '<td class ="price" >Price</td>';
          str += '</tr>';
          
          for (var i=0,l=obj.length; i<l; i++){
            //channel_order_item_id = obj[i].channel_order_item_id;
            //var arr = [];
            //var valuesss =arr.push({ channel_order_item_id: i});   
            //                           alert(valuesss);
            
            
            
            
            str +='<tr class= "row" >';
            str +='<td class="channel_item_id" id ="channel_item_id" class="channel_item_id" >'+obj[i].channel_item_id+'</td>';
            str += '<td class ="channel_order_status" >'+obj[i].channel_order_status+'</td>';
            str += '<td class ="quantity" >'+obj[i].quantity+'</td>';
            str += '<td class ="price" >'+obj[i].price+'</td>';
            /*str +='<div class="channel_order_status" display: table-cell>';
              str +='<span id ="channel_order_status" class="channel_order_status">'+obj[i].channel_order_status+'</span></div>';
              str +='<div class="quantity" display: table-cell>';
              str +='<span id ="quantity" class="quantity">'+obj[i].quantity+'</span></div>';
              str +='<div class="price" display: table-cell>';
            str +='<span id ="price" class="price">'+obj[i].price+'</span></div>';*/
            str +='</tr>';
            
            
          }
          //}
          str +='</tbody>';
          
          str +='</table>'
          //alert(str);  
          $("#RowId"+order_id).html(str);  
          $("#row_id"+order_id).show();    
          /*alert($("#RowId"+order_id).html());*/
          
          
          
          
        });
        
        
      }
      
      
      
      
      
      $(document).ready(function () {
        
        $('#customer_id').trigger("change");
        
        
      });
      
      $("#customer_id").on('change', function () {
        
        ajaxCall();
      });
      function ajaxCall()
      {
        var cname = $('#customer_id').val();
        var serve = window.location.origin;
        //alert(serve);
        if(cname != ''){
        $.get(serve+'/reportapis/getstatus/' + cname, function (data) {
          
          var result = $.parseJSON(data);
          
          
          $('#order_status_id').find('option').remove().end();
          // $('#order_status_id').append($("<option>").attr('value', '').text('please choose'.toUpperCase()));
          $.each(result, function (k, v) {
            //display the key and value pair
            //$('#order_status_id').selectpicker('refresh');
            $('#order_status_id').append($("<option>").attr('value', v).text(v.toUpperCase()));
          });
          
        });
      }
        
      }
      
  </script>
  
  
  
  
  @stop
@extends('layouts.footer')