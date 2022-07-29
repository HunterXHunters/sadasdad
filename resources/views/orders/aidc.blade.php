@extends('layouts.default')
@extends('layouts.header')
@extends('layouts.sideview')
@section('content')
        


 



  <div class="row">
            <div class="col-md-3 col-sm-6 col-xs-12">
              <div class="info-box">
                <span class="info-box-icon bg-aqua"><i class="ion ion-ios-gear-outline"></i></span>
                <div class="info-box-content">
                   <span class="info-box-text">All Orders</span>
                  <span class="info-box-number" id="total_id">
                          @if(!empty($allorders_aidc))
                            {{$allorders_aidc}}
                          @else
                            0
                          @endif
                  </span>

                </div><!-- /.info-box-content -->
              </div><!-- /.info-box -->
            </div><!-- /.col -->
            <div class="col-md-3 col-sm-6 col-xs-12">
              <div class="info-box">
                <span class="info-box-icon bg-red"><i class="fa fa-google-plus"></i></span>
                <div class="info-box-content">
                  <span class="info-box-text">Placed Orders</span>
                  <span class="info-box-number"id="available_id"> 
                        @if(!empty($placed_aidc))
                          {{$placed_aidc}}
                        @else
                          0
                        @endif
                  </span>
                </div><!-- /.info-box-content -->
              </div><!-- /.info-box -->
            </div><!-- /.col -->

            <!-- fix for small devices only -->
            <div class="clearfix visible-sm-block"></div>

            <div class="col-md-3 col-sm-6 col-xs-12">
              <div class="info-box">
                <span class="info-box-icon bg-green"><i class="ion ion-ios-cart-outline"></i></span>
                <div class="info-box-content">
                  <span class="info-box-text">Approved Orders</span>
                  <span class="info-box-number" id="intransit_id">
                      @if(!empty($approved_aidc))
                          {{approved_aidc}}
                        @else
                          0
                        @endif
                  </span>
                </div><!-- /.info-box-content -->
              </div><!-- /.info-box -->
            </div><!-- /.col -->
            <div class="col-md-3 col-sm-6 col-xs-12">
              <div class="info-box">
                <span class="info-box-icon bg-yellow"><i class="ion ion-ios-people-outline"></i></span>
                <div class="info-box-content">
                  <span class="info-box-text">Delivered Orders</span>
                  <span class="info-box-number" id="reserved_id">
                      @if(!empty($delivered_aidc))
                          {{delivered_aidc}}
                        @else
                          0
                        @endif
                  </span>
                </div><!-- /.info-box-content -->
              </div><!-- /.info-box -->
            </div><!-- /.col -->
          </div>
        <!-- /.box -->
        
        
       <div class="box collapsed-box">
          <div class="box-header with-border">
            <h3 class="box-title"><strong>AIDC</strong> Orders</h3>
         </div><!-- /.box-header -->
              

           <div class="col-sm-12">
             <div class="tile-body nopadding">                  
                <div id="treeGrid2"></div>
             </div>
           </div>
        </div>
@stop
@section('style')
   {{HTML::style('jqwidgets/styles/jqx.base.css')}}   
     
@stop

@section('script')
    {{HTML::script('scripts/jquery-1.10.1.min.js')}}
    {{HTML::script('jqwidgets/jqxcore.js')}}
    {{HTML::script('jqwidgets/jqxdata.js')}}
    {{HTML::script('jqwidgets/jqxbuttons.js')}}
    {{HTML::script('jqwidgets/jqxscrollbar.js')}}
    {{HTML::script('jqwidgets/jqxdatatable.js')}}
    {{HTML::script('jqwidgets/jqxtreegrid.js')}}
    {{HTML::script('scripts/demos.js')}} 

    {{HTML::script('jqwidgets/jqxmenu.js')}}
    {{HTML::script('jqwidgets/jqxgrid.js')}}
    {{HTML::script('jqwidgets/jqxgrid.selection.js')}}
    {{HTML::script('jqwidgets/jqxgrid.columnsresize.js')}}
    
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
        $(document).ready(function () 
        {
            $.ajax(
            {
               url: "/orders/getCusotmers/" +2,
                success: function(result)
                {
                    var employees = result;
                    
                    var source =
                    {
                        dataType: "json",
                        dataFields: [
                            { name: 'subscription_id', type: 'string' },
                            { name: 'order_no', type: 'string' },
                            { name: 'customer_name', type: 'string' },
                            { name: 'date_added', type: 'datetime' },
                            { name: 'total_cost', type: 'decimal' },
                            { name: 'actions', type: 'varchar' },
                            { name: 'start_date', type: 'datetime' },
                            { name: 'end_date', type: 'datetime' },
                            { name: 'customer_id', type: 'number' },
                            //{ name: 'edit', type: 'varchar' },
                            //{ name: 'delete', type: 'varchar' },
                            { name: 'children', type: 'array' },
                            { name: 'expanded', type: 'bool' }
                        ],
                        hierarchy:
                        {
                            root: 'children'
                        },
                        id: 'id',
                        localData: employees
                    };
                    var dataAdapter = new $.jqx.dataAdapter(source);
                    // create Tree Grid
                    $("#treeGrid2").jqxTreeGrid(
                    {
                        width: '100%',
                        source: dataAdapter,
                        sortable: true,
                        columns: [
                          { text: 'Subscription Id', dataField: 'subscription_id', width: "15%" },
                          { text: 'Order Number', dataField: 'order_no', width: "15%" },
                          { text: 'Customer Name', dataField: 'customer_name', width: "15%" },
                          { text: 'Purchased On', dataField: 'date_added', width: "10%" },
                          { text: 'Total Cost', dataField: 'total_cost', width: "5%" },
                          { text: 'Actions', dataField: 'actions', width: "10%" },
                          { text: 'Start Date', dataField: 'start_date', width: "10%" },
                          { text: 'End Date', dataField: 'end_date', width: "10%" },
                          { text: 'Customer Id', dataField: 'customer_id', width: "10%" },
                          //{ text: 'Edit', dataField: 'edit', width: 90 },
                          //{ text: 'Delete', dataField: 'delete', width: 90 },
                          
                        ]
                    });


                }
            });

        
       
 });

</script>   
@stop