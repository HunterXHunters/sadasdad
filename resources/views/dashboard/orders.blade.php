@extends('layouts.default')

@extends('layouts.header')

@extends('layouts.sideview')

@section('content')

<div class="row margleft">
    <div class="col-md-12">
        <section class="tile cornered">
            <!-- tile header -->
            <div class="tile-header">
                <h1><strong>Orders </strong>  Dashboards</h1>
            </div>
                 
            <div class="tile-body nopadding">
                <div class="tile-widget nopadding">
                    <ul class="nav nav-tabs tabdrop"><li class="dropdown pull-right tabdrop hide"><a class="dropdown-toggle" data-toggle="dropdown" href="#"><i class="fa fa-th-list"></i> <span class="badge"></span></a><ul class="dropdown-menu"></ul></li>
                      <li class="active"><a href="form-wizard.html#tab1" data-toggle="tab">GDS Orders</a></li>
                      <li><a href="form-wizard.html#tab2" data-toggle="tab">SCO Orders</a></li>
                    </ul>
                </div>
                
                <div class="tab-content">
                    <div class="tab-pane active" id="tab1">
                        
                        <div class="tile-header">
                          <h1><strong>GDS</strong> Orders</h1>
                        </div>
                        <div class="box box-primary">
                            {{ Form::open(array('url' => 'dashboard/orders','method'=>'POST','id'=>'ordRepFrm')) }}
                            <div class="row">
                                <div class="form-group col-sm-6">
                                  <label for="exampleInputEmail">Order Status</label>
                                  <div class="input-group ">
                                    <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
                                    <select class="chosen-select form-control parsley-validated" id="order_status_id" name="order_status_id" parsley-trigger="change" parsley-required="true" parsley-error-container="#selectbox">
                                        <option value="">Please choose</option>
                                        @foreach($OStatus as $status)  
                                            <option value="{{$status->value}}" @if(isset($row['order_status_id']) && $row['order_status_id']==$status->value) selected="selected" @endif>{{$status->name}}</option>
                                        @endforeach
                                    </select>
                                  </div>
                                </div>
                                @if(Session::get('cusotmerId')==0)    
                                <div class="form-group col-sm-6">
                                  <label for="exampleInputEmail">Customer Name</label>
                                  <div class="input-group ">
                                    <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
                                    <div id="selectbox">
                                        <select class="chosen-select form-control parsley-validated" id="customer_id" name="customer_id" parsley-trigger="change" parsley-required="true" parsley-error-container="#selectbox">
                                          <option value="">Please choose</option>
                                            @foreach($customers as $customer)
                                                <option value="{{$customer->customer_id}}"  @if(isset($row['customer_id']) && $row['customer_id']==$status->value) selected="selected" @endif>{{$customer->brand_name}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                  </div>
                                </div>
                                @endif
                            </div>
                            
                            <div class="row">
                                <div class="form-group col-sm-6">
                                  <label for="exampleInputEmail">From Date</label>
                                  <div class="input-group ">
                                    <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
                                    <input type="text" id="from_date" name="from_date" placeholder="From Date" class="form-control" value="<?PHP echo (isset($row['from_date'])) ? $row['from_date'] : '';?>">
                                  </div>
                                </div>

                                <div class="form-group col-sm-6">
                                  <label for="exampleInputEmail">To Date</label>
                                  <div class="input-group ">
                                    <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
                                    <input type="text" id="to_date" name="to_date" placeholder="To Date" class="form-control" value="<?PHP echo (isset($row['to_date'])) ? $row['to_date'] : '';?>" >
                                  </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="form-group col-sm-6">
                                  <label for="exampleInputEmail">Filter By</label>
                                  <div class="input-group ">
                                    <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
                                    <div id="selectbox">
                                        <select class="chosen-select form-control parsley-validated" id="filter_type" name="filter_type" parsley-trigger="change" parsley-required="true" parsley-error-container="#selectbox">
                                          <option value="">Please choose</option>
                                          <option value="DAY" @if(isset($row['filter_type']) && $row['filter_type']=='DAY') selected="selected" @endif >Daily</option>
                                          <option value="MONTH" @if(isset($row['filter_type']) && $row['filter_type']=='MONTH') selected="selected" @endif >Month</option>
                                          <option value="YEAR" @if(isset($row['filter_type']) && $row['filter_type']=='YEAR') selected="selected" @endif>Year</option>
                                        </select>
                                    </div>
                                  </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="form-group form-footer">
                                    <div class="col-sm-12">
                                        <button type="submit" class="btn btn-primary">Filter</button>
                                        <button type="reset" class="btn btn-default">Reset</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        {{Form::close()}}
                        <section class="tile color greensea">

                            <!-- tile header -->
                            <div class="tile-header">
                              <h1><strong>GDS</strong> Orders Chart</h1>
                             
                            </div>
                            <!-- /tile header -->

                            <!-- tile body -->
                            <div class="tile-body">
                              <table id="bar-chart" class="flot-chart" data-type="bars" data-bar-width="0.1" data-tool-tip="show" data-width="80%" data-height="250px" data-font-color="White" data-legend="hidden" data-tick-color="rgba(255,255,255,.3)">
                                <thead>
                                  <tr>
                                    <th></th>
                                    <th style="color : #5fdcc3;">Sales</th>
                                  </tr>
                                </thead>
                                <tbody>
                                  @foreach($orders as $order)
                                   <tr>
                                    <th>
                                        @if(isset($row['filter_type']) && $row['filter_type']=='DAY')
                                            {{date('d',strtotime($order->date))}}
                                        @elseif(isset($row['filter_type']) && $row['filter_type']=='MONTH')
                                             {{date('M',strtotime($order->date))}}
                                        @elseif(isset($row['filter_type']) && $row['filter_type']=='YEAR')
                                             {{date('Y',strtotime($order->date))}}
                                        @else
                                            {{date('M d, Y',strtotime($order->date))}}
                                        @endif
                                    </th>
                                    <td>{{$order->total}}</td>
                                   </tr>
                                  @endforeach


                                </tbody>
                              </table>
                            </div>
                        </section>
                    </div>
                    
                    
                    <div class="tab-pane" id="tab2">
                        <div class="tile-header">
                          <h1><strong>SCO</strong> Orders</h1>
                        </div>
                         <div class="box box-primary">
                            {{ Form::open(array('url' => 'dashboard/orders','method'=>'POST','id'=>'ordRepFrm')) }}
                            <div class="row">
                                <div class="form-group col-sm-6">
                                  <label for="exampleInputEmail">Order Status</label>
                                  <div class="input-group ">
                                    <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
                                    <select class="chosen-select form-control parsley-validated" id="order_status_id" name="order_status_id" parsley-trigger="change" parsley-required="true" parsley-error-container="#selectbox">
                                        <option value="">Please choose</option>
                                        @foreach($OStatus as $status)  
                                            <option value="{{$status->value}}" @if(isset($row['order_status_id']) && $row['order_status_id']==$status->value) selected="selected" @endif>{{$status->name}}</option>
                                        @endforeach
                                    </select>
                                  </div>
                                </div>
                                @if(Session::get('cusotmerId')==0)    
                                <div class="form-group col-sm-6">
                                  <label for="exampleInputEmail">Customer Name</label>
                                  <div class="input-group ">
                                    <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
                                    <div id="selectbox">
                                        <select class="chosen-select form-control parsley-validated" id="customer_id" name="customer_id" parsley-trigger="change" parsley-required="true" parsley-error-container="#selectbox">
                                          <option value="">Please choose</option>
                                            @foreach($customers as $customer)
                                                <option value="{{$customer->customer_id}}"  @if(isset($row['customer_id']) && $row['customer_id']==$status->value) selected="selected" @endif>{{$customer->brand_name}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                  </div>
                                </div>
                                @endif
                            </div>
                            
                            <div class="row">
                                <div class="form-group col-sm-6">
                                  <label for="exampleInputEmail">From Date</label>
                                  <div class="input-group ">
                                    <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
                                    <input type="text" id="from_date1" name="from_date" placeholder="From Date" class="form-control" value="<?PHP echo (isset($row['from_date'])) ? $row['from_date'] : '';?>">
                                  </div>
                                </div>

                                <div class="form-group col-sm-6">
                                  <label for="exampleInputEmail">To Date</label>
                                  <div class="input-group ">
                                    <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
                                    <input type="text" id="to_date1" name="to_date" placeholder="To Date" class="form-control" value="<?PHP echo (isset($row['to_date'])) ? $row['to_date'] : '';?>" >
                                  </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="form-group col-sm-6">
                                  <label for="exampleInputEmail">Filter By</label>
                                  <div class="input-group ">
                                    <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
                                    <div id="selectbox">
                                        <select class="chosen-select form-control parsley-validated" id="filter_type" name="filter_type" parsley-trigger="change" parsley-required="true" parsley-error-container="#selectbox">
                                          <option value="">Please choose</option>
                                          <option value="DAY" @if(isset($row['filter_type']) && $row['filter_type']=='DAY') selected="selected" @endif >Daily</option>
                                          <option value="MONTH" @if(isset($row['filter_type']) && $row['filter_type']=='MONTH') selected="selected" @endif >Month</option>
                                          <option value="YEAR" @if(isset($row['filter_type']) && $row['filter_type']=='YEAR') selected="selected" @endif>Year</option>
                                        </select>
                                    </div>
                                  </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="form-group form-footer">
                                    <div class="col-sm-12">
                                        <button type="submit" class="btn btn-primary">Filter</button>
                                        <button type="reset" class="btn btn-default">Reset</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        {{Form::close()}}
                        <section class="tile color greensea">

                            <!-- tile header -->
                            <div class="tile-header">
                              <h1><strong>SCO</strong> Orders Chart</h1>
                             
                            </div>
                            <!-- /tile header -->

                            <!-- tile body -->
                            <div class="tile-body">
                              <table id="bar-chart" class="flot" data-type="bars" data-bar-width="0.1" data-tool-tip="show" data-width="80%" data-height="250px" data-font-color="White" data-legend="hidden" data-tick-color="rgba(255,255,255,.3)">
                                <thead>
                                  <tr>
                                    <th></th>
                                    <th style="color : #5fdcc3;">Sales</th>
                                  </tr>
                                </thead>
                                <tbody>
                                  @foreach($orders as $order)
                                   <tr>
                                    <th>
                                        @if(isset($row['filter_type']) && $row['filter_type']=='DAY')
                                            {{date('d',strtotime($order->date))}}
                                        @elseif(isset($row['filter_type']) && $row['filter_type']=='MONTH')
                                             {{date('M',strtotime($order->date))}}
                                        @elseif(isset($row['filter_type']) && $row['filter_type']=='YEAR')
                                             {{date('Y',strtotime($order->date))}}
                                        @else
                                            {{date('M d, Y',strtotime($order->date))}}
                                        @endif
                                    </th>
                                    <td>{{$order->total}}</td>
                                   </tr>
                                  @endforeach


                                </tbody>
                              </table>
                            </div>
                        </section>
                    </div>
                </div>    
            </div>
        </section>
    </div>
</div>    
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

<script>
    $(function(){
      var bars = false;
      var lines = true;
      var pie = false;
      
    
        
        var el = $('table.flot-chart');

        el.each(function(){
          var data = $(this).data();
          var colors = [];
          var gridColor= data.tickColor || 'rgba(0,0,0,.1)';

          $(this).find('thead th:not(:first)').each(function() {
            colors.push($(this).css('color'));
          });

          if(data.type){
            bars = data.type.indexOf('bars') != -1;
            lines = data.type.indexOf('lines') != -1;
            pie = data.type.indexOf('pie') != -1;
          }

          $(this).graphTable({
            series: 'columns',
            position: 'replace',
            colors: colors,
            width: data.width,
            height: data.height
          },
          {
            series: { 
              stack: data.stack,
              pie: {
                show: pie,
                innerRadius: data.innerRadius || 0,
                label:{ 
                  show: data.pieLabel=='show' ? true:false
                }
              },
              bars: {
                show: bars,
                barWidth: data.barWidth || 0.5,
                fill: data.fill || 1,
                align: 'center'
              },
              lines: { 
                show: lines,
                fill: 0.1,
                lineWidth: 3
              },
              shadowSize: 0,
              points: {
                radius: 4
              }
            },
            xaxis: {
              mode: 'categories',
              tickLength: 0,
              font :{
                lineHeight: 24,
                weight: '300',
                color: data.fontColor,
                size: 14
              } 
            },
            yaxis: { 
              tickColor: gridColor,
              tickFormatter: function number(x) {  var num; if (x >= 1000) { num=(x/1000)+'k'; }else{ num=x; } return num; },
              max: data.yMax,
              font :{
                lineHeight: 13,
                weight: '300',
                color: data.fontColor
              }
            },  
            grid: { 
              borderWidth: {
                top: 0,
                right: 0,
                bottom: 1,
                left: 1
              },
              borderColor:gridColor,
              margin: 13,
              minBorderMargin:0,              
              labelMargin:20,
              hoverable: true,
              clickable: true,
              mouseActiveRadius:6
            },
            legend: { show: data.legend=='show' ? true:false },
            tooltip: data.toolTip=='show' ? true:false,
            tooltipOpts: { content: (pie ? '%p.0%, %s':'<b>%s</b> :  %y') }
          });
        });
    
        var pl = $('table.flot');

        pl.each(function(){
          var data = $(this).data();
          var colors = [];
          var gridColor= data.tickColor || 'rgba(0,0,0,.1)';

          $(this).find('thead th:not(:first)').each(function() {
            colors.push($(this).css('color'));
          });

          if(data.type){
            bars = data.type.indexOf('bars') != -1;
            lines = data.type.indexOf('lines') != -1;
            pie = data.type.indexOf('pie') != -1;
          }

          $(this).graphTable({
            series: 'columns',
            position: 'replace',
            colors: colors,
            width: data.width,
            height: data.height
          },
          {
            series: { 
              stack: data.stack,
              pie: {
                show: pie,
                innerRadius: data.innerRadius || 0,
                label:{ 
                  show: data.pieLabel=='show' ? true:false
                }
              },
              bars: {
                show: bars,
                barWidth: data.barWidth || 0.5,
                fill: data.fill || 1,
                align: 'center'
              },
              lines: { 
                show: lines,
                fill: 0.1,
                lineWidth: 3
              },
              shadowSize: 0,
              points: {
                radius: 4
              }
            },
            xaxis: {
              mode: 'categories',
              tickLength: 0,
              font :{
                lineHeight: 24,
                weight: '300',
                color: data.fontColor,
                size: 14
              } 
            },
            yaxis: { 
              tickColor: gridColor,
              tickFormatter: function number(x) {  var num; if (x >= 1000) { num=(x/1000)+'k'; }else{ num=x; } return num; },
              max: data.yMax,
              font :{
                lineHeight: 13,
                weight: '300',
                color: data.fontColor
              }
            },  
            grid: { 
              borderWidth: {
                top: 0,
                right: 0,
                bottom: 1,
                left: 1
              },
              borderColor:gridColor,
              margin: 13,
              minBorderMargin:0,              
              labelMargin:20,
              hoverable: true,
              clickable: true,
              mouseActiveRadius:6
            },
            legend: { show: data.legend=='show' ? true:false },
            tooltip: data.toolTip=='show' ? true:false,
            tooltipOpts: { content: (pie ? '%p.0%, %s':'<b>%s</b> :  %y') }
          });
        });


    })
      
    </script>


@stop