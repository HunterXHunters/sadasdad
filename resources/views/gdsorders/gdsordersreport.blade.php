@extends('layouts.default')
@extends('layouts.header')
@extends('layouts.sideview')
@section('content')
         {{ Form::open(array('url' => 'gdsorders/gdsordersreport','class'=>'form-date','id'=>'form-date')) }}
         {{ Form::hidden('_method', 'POST') }}
          <div class="row">

   

 <div class="col-sm-3">
  <div class="form-group">
   <label for="from_date">From Date</label>
    <div class="input-group input-append date" id="dateRangePickerFrom">
    <span class="input-group-addon "><span class="glyphicon glyphicon-calendar"></span></span>
     <input type="text" class="form-control" name="from_date" id="from_date" value="{{$fromDate}}"/></div>
     </div>
      </div>


<div class="col-sm-3">
<div class="form-group">
<label for="to_date">To Date</label>
<div class="input-group   input-append date" id="dateRangePickerTo">
<span class="input-group-addon "><span class="glyphicon glyphicon-calendar"></span></span>
<input type="text" class="form-control" name="to_date" id="to_date" value="{{$toDate}}" />
</div>
</div>
</div>

                   
                    
<div class="col-sm-3">
  <div class="form-group">
<label for="exampleInputEmail">Order Status</label>
<div class="input-group ">
<span class="input-group-addon addon-red"><i class="ion-arrow-shrink"></i></span>
<select name="order_status" id="order_status" class="form-control" >
<option value="">Please Select</option>
@if($ordstatus == 'All')
<option value="All" selected ="selected">All</option>
@else
<option value="All">All</option>
@endif
@foreach($data as $data) 
   @if($data->name == $ordstatus ) 
    <option value="{{$data->name}}" selected="selected" >{{$data->name}}</option>
    @else
    <option value="{{$data->name}}">{{$data->name}}</option>
  @endif
@endforeach
</select>
</div>
</div>
</div>



 <div class="col-sm-3">
 <div class="form-group">
  <div class="submitmarg">
  {{ Form::submit('Submit',array('class' => 'btn btn-primary')) }}
    {{ Form::close() }}
    </div>
    </div> 
  </div>                       

        
    </div>
    
                 
                  <div class="row">
                            <div class="col-sm-6">
                              <div class="form-group">
                            <label for="exampleInputEmail"><strong>Order Count :</strong> </label>
                             {{$gdsordinfo->order_count}} 
                          </div>
</div>
                            <div class="col-sm-6">
                              <div class="form-group">
                            <label for="exampleInputEmail"><strong>Charges :</strong> </label>
                              &#8377 {{$gdsordinfo->charges}} 
                          </div>
                          </div>
</div>


                          <div class="row">
                          <div class="col-sm-6">
<div class="form-group">
                            <label for="exampleInputEmail"><strong>Orders Total Amount :</strong> </label>
                              &#8377 {{$gdsordinfo->order_total}} 
                          </div>
                         </div>
                           <div class="col-sm-6">
                            <div class="form-group">
                            <label for="exampleInputEmail"><strong>Total Shipping Amount :</strong></label>&#8377 {{$gdsordinfo->ship_total}} 
                          </div>
                        </div>
                        </div>


                           <div class="row">
                          <div class="col-sm-6">
                            <div class="form-group">
                            <label for="exampleInputEmail"><strong>Tax Total :</strong></label>
                           &#8377 {{$gdsordinfo->tax_total}} 
                          </div>
                          </div>
                          
                           <div class="col-sm-6">
                            <div class="form-group">
                            <label for="exampleInputEmail"><strong>Discount Total :</strong></label>
                            &#8377 {{$gdsordinfo->discount_total}}
                          </div>
                          </div>
                        </div>

                          <div class="row">
                          <div class="col-sm-6">
                            <div class="form-group">
                            <label for="exampleInputEmail"><strong>Profit :</strong></label>
                            &#8377 {{$gdsordinfo->profit}} 
                          </div>
                          </div>
                           
                           <div class="col-sm-6">
                            <div class="form-group">
                            <label for="exampleInputEmail"><strong>Quantity :</strong></label>
                            {{$gdsordinfo->qty}}
                          </div>
                          </div>
                        </div>
                           

                       
                        <div class="box">
              <div class="box-header">
                <h3 class="box-title"><strong>GDS Orders </strong>  Report</h3>
           
              </div>
               
               <div class="col-sm-12">
                 <div class="tile-body nopadding">                  
                    <div id="jqxgrid"  style="width:100% !important;"></div>
                    </div>
                </div>

              </div>


     
@stop

@section('style')
{{HTML::style('jqwidgets/styles/jqx.base.css')}}
{{HTML::style('css/bootstrap-select.css')}}
{{HTML::style('css/datepicker.min.css')}}
{{HTML::style('css/jquery.fileupload.css')}}
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
    {{HTML::script('js/plugins/bootstrap-select//bootstrap-datepicker.min.js')}}
    {{HTML::script('jqwidgets/jqxcheckbox.js')}}
    
    <script type="text/javascript">

    // $(document).ready(function(){
    // window.setTimeout(function(){
    //     $(".alert").hide();
    // },3000);
//});
    $(document).ready(function () 
        {        
        //alert('we are in ready');
            var url = "/gdsorders/show";
            var fromdate= $('#from_date').val();
            var todate=$('#to_date').val();
            var orderstatus =$('#order_status').val();
            // prepare the data
            var source =
            {
                datatype: "json",
                datafields: [
                    { name: 'OrderId', type: 'integer' },
                     { name: 'order_status', type: 'string' },
                    { name: 'channnel_name', type: 'string' },
                    { name: 'total', type: 'decimal' },
                    { name: 'order_date', type: 'datetime' },
                    {name:'full_name',type:'string'}
                    //{ name: 'delete', type: 'string' }
                ],
                id: 'gds_order_id',
                url: url+'?from_date='+fromdate+'&to_date='+todate +'&order_status='+orderstatus,
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
                  { text: 'Order ID',datafield: 'OrderId', width: "20%" },
                  { text: 'Name',datafield: 'full_name', width: "10%" },
                  { text: 'Channel Name', datafield: 'channnel_name', width: "10%" },
                  { text: 'Order Date', datafield: 'order_date', width:"20%"},
                  { text: 'Order Status',datafield: 'order_status', width: "20%" },
                 { text: 'Total (₹)', datafield: 'total', width:"20%"}
                  
                  ]
            });            /*

             makePopupAjax($('#basicvalCodeModal'));      
             makePopupEditAjax($('#basicvalCodeModal1'), 'gds_order_id');*/
        });

    $('#form-date').submit(function(){
        $('#form-date').submit();
    });
    datePicket();

    function datePicket()
    {
        var today = new Date();
        var dd = today.getDate();
        var ddd = today.getDate()+1;
        var mm = today.getMonth()+1; //January is 0!

        var yyyy = today.getFullYear();
        var yyyyy = today.getFullYear()+20;
        if(dd<10){
            dd='0'+dd
        } 
        if(mm<10){
            mm='0'+mm
        } 
        var today = yyyy+'-'+mm+'-'+dd;
        var tomorrow = yyyy+'-'+mm+'-'+ddd;
        
        $('#dateRangePickerFrom')
            .datepicker({
                format: 'yyyy-mm-dd',
                endDate: yyyyy+'-12-30'
            })
            .on('changeDate', function(e) {
                // Revalidate the date field
                //$('#dateRangeForm').formValidation('revalidateField', 'date');
                $('#to_date').val('');
                $('#dateRangePickerTo').datepicker('remove');
                changeData($("#from_date").val(), yyyyy);
                $('.datepicker.datepicker-dropdown').hide();
            });

             $('#dateRangePickerTo')
            .datepicker({
                format: 'yyyy-mm-dd',
                startDate: today,
                endDate: yyyyy+'-12-30'
            })
            .on('changeDate', function(e) {
                // Revalidate the date field
                //$('#dateRangeForm').formValidation('revalidateField', 'date');
                /*$('#from_date').val('');
                $('#dateRangePickerFrom').datepicker('remove');*/
                changeData($("#to_date").val(), yyyyy);
                $('.datepicker.datepicker-dropdown').hide();
            });
    }
    function changeData(tomorrow, yyyyy)
    {
        $('#dateRangePickerTo')
            .datepicker({
                format: 'yyyy-mm-dd',
                startDate: tomorrow,
                endDate: yyyyy+'-12-30',
                Default: false
            })
            .on('changeDate', function(e) {
                // Revalidate the date field
                //$('#dateRangePickerTo').formValidation('revalidateField', 'date');
                $('.datepicker.datepicker-dropdown').hide();
            });
    } 
     
    </script>
@stop
