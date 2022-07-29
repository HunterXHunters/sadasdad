
@extends('layouts.default')

@extends('layouts.header')

@extends('layouts.sideview')

@section('content')
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/dt/dt-1.10.13/datatables.min.css"/>
 
<script type="text/javascript" src="https://cdn.datatables.net/v/dt/dt-1.10.13/datatables.min.js"></script>

<style>
.col-md-3 {
    width: 20% !important;
	padding-right:0px !important;
}
.col-sm-1 {
    width: 6.333333% !important;
    padding-left: 0px!important;
    padding-right: 0px!important;
}
</style>
  
        <!-- /.box -->
        
       <div class="box collapsed-box">
          <div class="box-header with-border">
          	<h3 class="box-title"><strong>Sale </strong>Report</h3>
            <div class="box-tools1 pull-right">
              <button class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-filter"></i></button>
            </div>


<div class="box-body">
            @if(!empty($customers))
  
  
    <div class="form-group col-sm-5">
      <label for="exampleInputEmail">Choose Customer</label>
      <div id="selectbox">
      
        <select class="form-control requiredDropdown" id="customer_id" name="customer_id"       parsley-trigger="change" parsley-required="true" parsley-error-container="#selectbox" onchange="" >
          <option  value="">Select Customer</option>
            @foreach($customers as $customer)  
              <option value="{{$customer->customer_id}}" >{{$customer->brand_name}}</option>
            @endforeach
        </select>
        
      </div>
    </div>
    
  

  @else
    <input type="hidden" id="customer_id" value="{{$customerId}}">
  
  
  @endif

  
    <div class="form-group col-sm-5">
                <label for="locationtype">Location Type</label>
                <div class="input-group ">
                    <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
                    <div id="">
                        <select class="list-unstyled selectpicker" data-live-search="true" id="locationtype" name="locationtype" parsley-trigger="change" parsley-required="true" parsley-error-container="#selectbox" >
                            <option  value="0">Select Location Type</option>
                              @foreach($locationTypes as $key=>$result)
                                  <option  value="{{$key}}">{{$result}}</option>
                              @endforeach
                        </select>
                    </div>
                </div>
    </div>

            <div class="form-group col-sm-5">
                <label for="exampleInputEmail">From</label>
                <div class="input-group ">
                    <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
                    <div id="">
                        <input type="text" name="from_date" id="from_date" class ="bootstrap-datepicker">
                     </div>
                 </div>
            </div>



            <div class="form-group col-sm-5">
                <label for="exampleInputEmail">To</label>
                <div class="input-group ">
                    <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
                    <div id="">
                        <input type="text" name="to_date" id="to_date">
                    </div>
                </div>
            </div>

            <div class="form-group col-sm-2">
                <label for="exampleInputEmail"></label>
                <div class="input-group ">
                    <div id="button">
                        <button class="btn btn-primary" data-toggle="modal"  onclick="makeGrid(0);">Filter</button>
                        <button type="button" class="btn btn-primary" aria-label="Left Align" id="demo">
                            <span class="glyphicon glyphicon-refresh" aria-hidden="true"></span>
                        </button>
                    </div>
                    <input type="hidden" id="module_id" name="module_id" value="">
                    <input type="hidden" id="access_token" name="access_token" value="">
                </div>
            </div>

  </div>
            
                <!-- /.box-tools -->
              </div><!-- /.box-header -->
              <!-- <div class="box-body">
                <div class="form-group col-sm-5">
                    <label for="exampleInputEmail">Location</label>
                    <div class="input-group ">
                      <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
                      <div id="selectbox">
                       <select class="list-unstyled selectpicker" data-live-search="true" id="location_id" name="location_id" 
                        parsley-trigger="change" parsley-required="true" parsley-error-container="#selectbox" >
                            <option  value="">Select Location</option>
                            
                          </select>
                        </div>
                    </div>
                    </div>
                    <div class="col-sm-1" style="margin-top:25px;">
                  <button class="btn btn-primary" data-toggle="modal" data-target="" onclick="makeGrid();" >Filter</button>
                   </div>
                  
    
                  <div class="form-group col-sm-5">
                    <label for="exampleInputEmail">Product</label>
                    <div class="input-group ">
                      <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
                      <div id="selectbox">
                       <select class="list-unstyled selectpicker" data-live-search="true" id="product_id" name="product_id" 
                        parsley-trigger="change" parsley-required="true" parsley-error-container="#selectbox" >
                           <option  value="">Select Product</option>
                           </select>
                        </div>
                    </div>
                  </div>
    
                  <div class="form-group ">
                  <label for="exampleInputEmail"></label>
                  <div class="input-group ">
                  <div id="button">
                  <button class="btn btn-primary" data-toggle="modal"  onclick="makeGrid();">Filter</button>
                  <button type="button" class="btn btn-primary" aria-label="Left Align" id="demo">
                  <span class="glyphicon glyphicon-refresh" aria-hidden="true"></span>
                  </button>
                  </div>
                  <input type="hidden" id="module_id" name="module_id" value="{{$module_id}}">
                  <input type="hidden" id="access_token" name="access_token" value="{{$access_token}}">
                  </div>
                  </div>
              </div> --><!-- /.box-body -->

           <div class="col-sm-12">
             <div class="tile-body nopadding">                  
                <div id="jqxgrid"></div>
             </div>
           </div>
        </div>
        <div class="jqxgrid" id="jqxgrid"></div>

        <!-- <div class="reporttable">

        <table class="table hover" cellspacing="0" width="100%" id="salereport"> 
          <thead>
          <tr>
            <td>Qty</td>
            <td>Material Code</td>
            <td>Material Name</td>
            <td>Sale Location</td>
            <td>Sale Time</td>
            <td>Tp</td>
            <td>Delivery</td>
            <td>Source Location</td>
          </tr>
          </thead>
          <tbody id ="report">
          @foreach($sales as $sale)
          <tr>
            <td>{{$sale->Qty}}</td>
            <td>{{$sale->MaterialCode}}</td>
            <td>{{$sale->MaterialName}}</td>
            <td>{{$sale->SaleLocation}}</td>
            <td>{{$sale->SaleTime}}</td>
            <td>{{$sale->Tp}}</td>
            <td>{{$sale->Delivery}}</td>
            <td>{{$sale->SourceLocation}}</td>
          </tr>

          @endforeach
          </tbody>

        </table>
        </div>
 -->
		 
            

             
              
          
                
            

@stop

@section('style')
    {{HTML::style('jqwidgets/styles/jqx.base.css')}}
     {{HTML::style('css/bootstrap-select.css')}}

@stop

@section('script')
{{HTML::script('js/plugins/bootstrap-select/bootstrap-select.js')}}
{{HTML::script('js/plugins/bootstrap-select/bootstrap-datepicker.min.js')}}
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
    $(document).ready(function() { 
 //     $('#salereport').DataTable();
         $('.box-body').show();
        $('#demo').click(function() { 
        //alert('wrking')
        var module_id=document.getElementById('module_id').value;
        var access_token=document.getElementById('access_token').value;
       //var ajaxTime= new Date().getTime();
         
        $.blockUI({ css: { 
            border: 'none', 
            padding: '15px', 
            backgroundColor: '#000', 
            '-webkit-border-radius': '10px', 
            '-moz-border-radius': '10px', 
            opacity: .5, 
            color: '#fff' 
        } }); 
 
        setTimeout($.unblockUI, 15000); 
    }); 
var cust_id = {{$customerId}};
if(cust_id != 0){
  //reportdata(cust_id);
 // makeGrid(0);
  //test();
}

        
}); 
    
     // function salereportdata(){
     //  var cust = $('#customer_id').val();
     //  if(cust !=0){
     //    reportdata(cust);
     //  }
     //  else{
     //    $('#salereport').dataTable().fnDestroy();
     //    $('#report').empty();
     //    $('#salereport').DataTable();
     //  }
     // }   
        

        // function reportdata(cust = 0){
        //   if(cust !=0){
        //     $.ajax({
        //       url:'saleReportData',
        //       method:'get',
        //       data:'customer_id='+cust,
        //       success:function(response){
        //         //alert('success');
        //         $('#salereport').dataTable().fnDestroy();
        //         $('#report').empty();
        //         $.each(response, function(i,data){
        //           $('#report').append('<tr><td>'+data['Qty']+'</td><td>'+data['MaterialCode']+'</td><td>'+data['MaterialName']+'</td><td>'+data['SaleLocation']+'</td><td>'+data['SaleTime']+'</td><td>'+data['Tp']+'</td><td>'+data['Delivery']+'</td><td>'+data['SourceLocation']+'</td></tr>')

        //         });
        //         $('#salereport').DataTable();

        //       },
        //       error:function(){
        //         $('#salereport').dataTable().fnDestroy();
        //         $('#report').empty();
        //         $('#salereport').DataTable();
        //         alert('error');
        //       }
        //   });

        //   }
        //   else{
        //     $('#salereport').dataTable().fnDestroy();
        //     $('#report').empty();
        //     $('#salereport').DataTable();
        //   }
        // }
        
        // function test(){
        //  // $('#salereport').dataTable().fnDestroy();
        //   //alert("jj");
        //   $('#salereport').DataTable({
        //        processing: true,
        //        serverSide: true,
        //        ajax: {
        //            url: 'saleReportData',
        //            data: {customer_id: 5},
        //            dataSrc:''
        //        },
 
        //        columns: [
        //            {data: 'Qty', name: 'Qty'},
        //            {data: 'MaterialCode', name: 'MaterialCode'},
        //            {data: 'MaterialName', name: 'MaterialName'},
        //            {data: 'SaleLocation', name: 'SaleLocation'},
        //            {data: 'SaleTime', name: 'SaleTime'},
        //            {data: 'Tp', name: 'Tp'},
        //            {data: 'Delivery', name: 'Delivery'},
        //            {data: 'SourceLocation', name: 'SourceLocation'}
        //        ]
        //    });
        // }

         makeGrid = function (){
                var cust = $('#customer_id').val();
                cust = typeof cust !=='undefined' ? cust : 0;
                var location_type =$('#locationtype').val();
                var from_date = $('#from_date').val();
                var to_date = $('#to_date').val();

                var url = '/dashboard/saleReportData?customer_id='+cust+'&location_type='+location_type+'&from_date='+from_date+'&to_date='+to_date;//+ location_id +'/' + product_id + '/' + from_date  + '/' + to_date;
                // alert(url);
                if(cust == 0 || cust =="" || location_type ==0 || location_type =="" || from_date == 0 || from_date =="" || to_date ==0 || to_date =="" ){
                  alert("Please select all the fileds");
                  return false;
                }
                var source =
                    {
                        datatype: "json",
                        datafields: [
                            { name: 'Qty', type: 'string' },
                            { name: 'MaterialCode', type: 'string' },
                            { name: 'MaterialName', type: 'string' },
                            { name: 'SaleLocation', type: 'string' },
                            { name: 'SaleTime', type: 'string' },
                            { name: 'Tp', type: 'string' },
                            { name: 'Delivery', type: 'string' },
                            { name: 'SourceLocation', type: 'string' }
                        ],
                        id: 'id',
                        url: url,
                        pager: function (pagenum, pagesize, oldpagenum) {
                            // callback called when a page or page size is changed.
                        }
                    };
                var dataAdapter = new $.jqx.dataAdapter(source);
                createGrid(dataAdapter);
            }

            function createGrid(source){
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
                                { text: 'Qty', datafield: 'Qty'},
                            { text: 'Material Code', datafield: 'MaterialCode' },
                            { text: 'Material Name', datafield: 'MaterialName'},
                            { text: 'Sale Location', datafield: 'SaleLocation'},
                            { text: 'Sale Time', datafield: 'SaleTime'},
                            { text: 'Tp', datafield: 'Tp'},
                            { text: 'Delivery', datafield: 'Delivery'},
                            { text: 'Source Location', datafield: 'SourceLocation'}
                        ]
                    });
            }

  //          makeGrid(0);
$(function() {
     
     $('#from_date').datepicker({ dateFormat: 'yy-mm-dd'}); 
     $('#to_date').datepicker({ dateFormat: 'yy-mm-dd'}); 
  
});

$('#customer_id').on('change',function(){
  var cust = $(this).val();
  if(cust == 0){
    $('#locationtype').empty();
    var opt = new Option('Select Location type',0);
    $('#locationtype').append(opt);
    $('#locationtype').selectpicker('refresh');
  }
  else{
    $.ajax({
      url:"getlocationtypesbycustomerid",
      data:'customer_id='+cust,
      method:'get',
      success:function(response){
        $('#locationtype').empty();

        //select.empty();
        var opt = new Option('Select Location type',0);
        $('#locationtype').append(opt);
        $.each(response,function(i,val){
          opt =new Option(val,i);
          $('#locationtype').append(opt);
        });
        $('#locationtype').selectpicker('refresh');
      }
    });
  }


});
    </script>    
@stop