@extends('layouts.default')

@extends('layouts.header')

@extends('layouts.sideview')

@section('content')

<!-- breadcrumbs -->
     
      <!-- /breadcrumbs --> 
       <div> 
              <div class="row cards">
           <div class="card-container col-lg-3 col-md-6 col-sm-12">
            <div class="card card-orange hover">
              <div class="front">
                <h1>All Orders</h1>
                @if(!empty($allorders))
                <p>{{$allorders}}</p>
                @else
                <p>0</p>
                @endif

                <span class="fa-stack fa-2x pull-right"> <i class="fa fa-circle fa-stack-2x"></i> <i class="fa fa-eye fa-stack-1x"></i> <span class="easypiechart" data-percent="90" data-line-width="4" data-size="80" data-line-cap="butt" data-animate="2000" data-target="#visits-count" data-update="3000" data-bar-color="white" data-scale-color="false" data-track-color="rgba(0, 0, 0, 0.15)"></span> </span> </div>
              <div class="back">
                <ul class="inline divided">
                  <li>
                   
                  </li>
                  <li>
                   
                  </li>
                </ul>
                <!-- <div class="summary negative">2% <i class="fa fa-arrow-down"></i> this month</div> -->
                 <button class="btn btn-primary" data-toggle="modal" data-target="" onclick="makeGrid(0)">All Orders</button>
              </div>
            </div>
          </div>


          <div class="card-container col-lg-3 col-md-6 col-sm-12">
            <div class="card card-red hover">
              <div class="front">
                <h1>Placed Orders</h1>
                @if(!empty($placed))
                <p>{{$placed}}</p>
                @else
                <p>0</p>
                @endif
                <span class="fa-stack fa-2x pull-right"> <i class="fa fa-circle fa-stack-2x"></i> <i class="fa fa-user fa-stack-1x"></i> <span class="easypiechart" data-percent="100" data-line-width="4" data-size="80" data-line-cap="butt" data-animate="2000" data-target="#users-count" data-update="3000" data-bar-color="white" data-scale-color="false" data-track-color="rgba(0, 0, 0, 0.15)"></span> </span> </div>
              <div class="back">
                <ul class="inline divided">
                  <li>
                    
                  </li>
                  <li>
                    
                  </li>
                </ul>
                
                <button class="btn btn-primary" data-toggle="modal" data-target="" onclick="makeGrid(1)">Placed Orders</button>
              </div>
            </div>
          </div>
          
          <!-- Modal -->
          <div class="modal fade" id="codeModal01" tabindex="-1" role="dialog" aria-labelledby="cardCode01" aria-hidden="true">
            <div class="modal-dialog wide">
              <div class="modal-content">
                <div class="modal-header">
                  <button type="button" class="close" data-dismiss="modal" aria-hidden="true" >&times;</button>
                  <h4 class="modal-title" id="cardCode01">Users Card - Source Code</h4>
                </div>
                <div class="modal-body"> 
                  
                  <!-- Source Code -->
                  
                  <!-- /Source Code --> 
                  
                </div>
              </div>
              <!-- /.modal-content --> 
            </div>
            <!-- /.modal-dialog --> 
          </div>
             <div class="card-container col-lg-3 col-md-6 col-sm-12">
            <div class="card card-cyan hover">
              <div class="front">
                <h1>Approved Orders</h1>
                @if(!empty($approved))
                <p>{{$approved}}</p>
                @else
                <p>0</p>
                @endif
                <span class="fa-stack fa-2x pull-right"> <i class="fa fa-circle fa-stack-2x"></i> <i class="fa fa-shopping-cart fa-stack-1x"></i> <span class="easypiechart" data-percent="55" data-line-width="4" data-size="80" data-line-cap="butt" data-animate="2000" data-target="#orders-count" data-update="3000" data-bar-color="white" data-scale-color="false" data-track-color="rgba(0, 0, 0, 0.15)"></span> </span> </div>
              <div class="back">
                <ul class="inline divided">
                  <li>
                   
                  </li>
                  <li>
                   
                  </li>
                </ul>
                <button class="btn btn-primary" data-toggle="modal" data-target="" onclick="makeGrid(2)">Approved Orders</button>
              </div>
            </div>
          </div>
          
          <!-- Modal -->
        
              <div class="card-container col-lg-3 col-md-6 col-sm-12">
            <div class="card card-green hover">
              <div class="front">
                <h1>Delivered Orders</h1>
                @if(!empty($delivered))
                <p>{{$delivered}}</p>
                @else
                <p>0</p>
                @endif
                <span class="fa-stack fa-2x pull-right"> <i class="fa fa-circle fa-stack-2x"></i> <i class="fa fa-usd fa-stack-1x"></i> <span class="easypiechart" data-percent="30" data-line-width="4" data-size="80" data-line-cap="butt" data-animate="2000" data-target="#sales-count" data-update="3000" data-bar-color="white" data-scale-color="false" data-track-color="rgba(0, 0, 0, 0.15)"></span> </span> </div>
              <div class="back">
                <ul class="inline divided">
                  <li>
                    
                  </li>
                  <li>
                    
                  </li>
                </ul>
                <button class="btn btn-primary" data-toggle="modal" data-target="" onclick="makeGrid(3)">Delivered Orders</button>
              </div>
            </div>
          </div>

         
          
          <!-- Modal -->
       
          <!-- /.modal -->
               
              <div style="width:350px;">
              <font size="5px;" color="green"><a href="orders/createOrder/0">Create An Order</a></font>

              </div>    
              
              
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
    <script type="text/javascript">
    //var makeGrid = function(){};
    $(document).ready(function () 
        {
        
        makeGrid = function (id){
         //alert(id)
          var url = "orders/getCusotmers/" + id;
            // prepare the data
            var source =
            {
                datatype: "json",
                datafields: [
                    { name: 'order_no', type: 'string' },
                    { name: 'customer_name', type: 'string' },
                    { name: 'date_added', type: 'datetime' },
                    { name: 'bill_to_name', type: 'string' },
                    { name: 'ship_to_name', type: 'string' },
                    { name: 'total_cost', type: 'decimal' },
                    { name: 'order_status', type: 'string' },
                    { name: 'actions', type: 'string' },
                   // { name: 'delete', type: 'string' }
                ],
                id: 'order_no',
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
                width: '100%',
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
                  { text: 'Order no', filtercondition: 'starts_with', datafield: 'order_no', width: 200 },
                  { text: 'Customer Name', datafield: 'customer_name', width: 200},
                  { text: 'Purchased On', datafield: 'date_added', width: 200 },
                  { text: 'Bill To Name', datafield: 'bill_to_name', width:200},
                  { text: 'Ship To Name', datafield: 'ship_to_name', width:100},
                  { text: 'Total Cost', datafield: 'total_cost', width:100},
                  { text: 'Order Status', datafield: 'order_status', width:100},
                  //{ text: 'Edit', datafield: 'edit' },
                  { text: 'Actions', datafield: 'actions',width:200 }
                ]               
            }); 
        }

        makeGrid(0);
        
      }); 
    
    </script>    
@stop