@extends('layouts.default')

@extends('layouts.header')

@extends('layouts.sideview')

@section('content')

         <!-- Info boxes -->
          <div class="row">
            <div class="col-md-3 col-sm-6 col-xs-12">
              <div class="info-box">
                <span class="info-box-icon bg-aqua"><i class="ion ion-ios-gear-outline"></i></span>
                <div class="info-box-content">
                  <span class="info-box-text">Total Revenue</span>
                  <span class="info-box-number">{{$order_data[0]->total}}<small>INR</small></span>
                </div><!-- /.info-box-content -->
              </div><!-- /.info-box -->
            </div><!-- /.col -->
            <div class="col-md-3 col-sm-6 col-xs-12">
              <div class="info-box">
                <span class="info-box-icon bg-red"><i class="fa fa-google-plus"></i></span>
                <div class="info-box-content">
                  <span class="info-box-text">Total Orders</span>
                  <span class="info-box-number">{{$order_data[0]->total_orders}}</span>
                </div><!-- /.info-box-content -->
              </div><!-- /.info-box -->
            </div><!-- /.col -->

            <!-- fix for small devices only -->
            <div class="clearfix visible-sm-block"></div>

            <div class="col-md-3 col-sm-6 col-xs-12">
              <div class="info-box">
                <span class="info-box-icon bg-green"><i class="ion ion-ios-cart-outline"></i></span>
                <div class="info-box-content">
                  <span class="info-box-text">Sold Orders</span>
                  <span class="info-box-number">{{$sold_orders[0]->sold_orders}}</span>
                </div><!-- /.info-box-content -->
              </div><!-- /.info-box -->
            </div><!-- /.col -->
            <div class="col-md-3 col-sm-6 col-xs-12">
              <div class="info-box">
                <span class="info-box-icon bg-yellow"><i class="ion ion-ios-people-outline"></i></span>
                <div class="info-box-content">
                  <span class="info-box-text">Cancelled Orders</span>
                  <span class="info-box-number">{{$canceled_orders[0]->canceled_orders}}</span>
               </div><!-- /.info-box-content -->
              </div><!-- /.info-box -->
            </div><!-- /.col -->
          
          </div><!-- /.row -->

          <div class="row">
            
              <div class="box">
                <div class="box-header with-border">
                  <h3 class="box-title">GDS Dashboard</h3>
                  <div class="box-tools pull-right">
                    
                    <div class="btn-group">
                      
                      <ul class="dropdown-menu" role="menu">
                        <li><a href="#">Action</a></li>
                        <li><a href="#">Another action</a></li>
                        <li><a href="#">Something else here</a></li>
                        <li class="divider"></li>
                        <li><a href="#">Separated link</a></li>
                      </ul>
                    </div>
                    
                  </div>
                </div><!-- /.box-header -->
                <div class="box-body">
                  <div class="row">
                    <div class="col-md-12">
                      <p class="text-center">
                        @foreach($min_and_max_dates as $max)
                        <strong>{{"Orders from :".$max->minimum_date." "."to"." ".$max->maximum_date}}</strong>
                        @endforeach
                      </p>
                      
                        <!-- Sales Chart Canvas -->
                  <div class="col-md-10">
                  <canvas style="display:none !important;" id="salesChart" height="180"></canvas>
                  
                  <div class="box-body chart-responsive">
                  <div class="chart" id="bar-chart" style="width:1250px;height:300px;"></div>
                   
                   </div><!-- /.box-body -->
                  </div>
                 
                  </div>
                  
                </div><!-- ./box-body -->
              </div>
                <div class="box-footer">
                  
                </div><!-- /.box-footer -->
              </div><!-- /.box -->
            
          </div><!-- /.row -->

          <!-- Main row -->
          <div class="row">
            <!-- Left col -->

            <div class="col-md-7">
              <!-- MAP & BOX PANE -->
              <div class="box box-primary">
                <div class="box-header with-border">
                  <h3 class="box-title">Products Of Recent Orders</h3>
                  <div class="box-tools pull-right">
                    <button class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                    <button class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button>
                  </div>
                </div><!-- /.box-header -->
                <div class="box-body">
                  <ul class="products-list product-list-in-box">
                    @foreach($products_view as $prod)
                    <li class="item">
                      <div class="product-img">
                        <img src="/uploads/products/<?php echo $prod->image ?>" alt="Product Image" />
                      </div>
                      <div class="product-info">
                        <a href="javascript::;" class="product-title">{{$prod->name}}<span class="label label-warning pull-right">{{$prod->mrp}}/-</span></a>
                        <span class="product-description">
                          Channel Name : {{$prod->channnel_name}}
                        </span>
                        <span class="product-description">
                          Product Description : {{$prod->description}}
                        </span>
                      </div>
                    </li><!-- /.item -->
                  @endforeach                    
                  </ul>
                </div><!-- /.box-body -->
                </div><!-- /.box -->

                
             </div><!-- /.col -->
             <div class="col-md-5">
                  <!-- USERS LIST -->
                  <div class="box box-primary" style="min-height:385px;">
                <div class="box-header with-border">
                  <h3 class="box-title">Channels of GDS System</h3>
                  <div class="box-tools pull-right">
                    <button class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                    <button class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button>
                  </div>
                </div><!-- /.box-header -->
                <div class="box-body">
                  <ul class="products-list product-list-in-box">
                    @foreach($subscribed_channel as $channel_data)
                    <li class="item">
                       <div class="product-img">
                        <img src="<?php echo $channel_data->channel_logo ?>" alt="Product Image" />
                      </div>
                      <div class="product-info">
                        <a href="javascript::;" class="product-title">{{$channel_data->channnel_name}}</a>
                        <span class="product-description">
                        </span>
                      </div>
                      <div class="product-info">
                        <a href="javascript::;">{{$channel_data->channel_url}}</a>
                        <span class="product-description">
                        </span>
                      </div>
                    </li><!-- /.item -->
                  @endforeach
                   </ul>
                </div><!-- /.box-body -->
               
              </div><!-- /.box -->
                </div><!-- /.col -->
          </div><!-- /.row -->



@stop
@section('script')
  
    <script src="/js/plugins/jQuery/jQuery-2.1.4.min.js" type="text/javascript"></script>
    <script src="/js/plugins/chartjs/Chart.min.js" type="text/javascript"></script>
    <script src="/js/plugins/morris/morris.min.js" type="text/javascript"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/raphael/2.1.0/raphael-min.js"></script>
    <!-- jvectormap -->
    <script src="/js/plugins/jvectormap/jquery-jvectormap-1.2.2.min.js" type="text/javascript"></script>
    <script src="/js/plugins/jvectormap/jquery-jvectormap-world-mill-en.js" type="text/javascript"></script>
    <!-- Sparkline -->
    <script src="/js/plugins/sparkline/jquery.sparkline.min.js" type="text/javascript"></script>
<script type="text/javascript">
$(function () {
  

  var salesChartCanvas = $("#salesChart").get(0).getContext("2d");
  
  var salesChart = new Chart(salesChartCanvas);
  
  var finaleBayarray = '<?php echo $ebay_count ?>';
  
  var finaleBayarray = JSON.parse(finaleBayarray);
  
  var finalflipkartarray = '<?php echo $flipkart_count ?>';
  
  var finalflipkartarray = JSON.parse(finalflipkartarray);
  
  var finalamazonarray = '<?php echo $amazon_count ?>';
  
  var finalamazonarray = JSON.parse(finalamazonarray); 
  
  var count_channel_skus = '<?php echo $count_channel_skus ?>'
 
  var count_channel_skus = JSON.parse(count_channel_skus); 
   

    
  
  var salesChartData = {
    labels: ["September","October","November"],
    datasets: [
      {
        label: "Amazon",
        fillColor: "rgba(128,0,0,0.7)",
        strokeColor: "rgb(192, 192, 192)",
        pointColor: "rgb(192, 192, 192)",
        pointStrokeColor: "#c1c7d1",
        pointHighlightFill: "#fff",
        pointHighlightStroke: "rgb(220,220,220)",
        data: [finalamazonarray[9], finalamazonarray[10], finalamazonarray[11] ]
      },
      {
        label: "Flipkart",
        fillColor: "rgba(60,141,188,0.9)",
        strokeColor: "rgba(60,141,188,0.9)",
        pointColor: "#3b8bba",
        pointStrokeColor: "rgba(60,141,188,1)",
        pointHighlightFill: "#fff",
        pointHighlightStroke: "rgba(60,141,188,1)",
        data: [finalflipkartarray[9] ,finalflipkartarray[10], finalflipkartarray[11]]
      },
      {
        label: "eBay",
        fillColor: "rgba(0,255,0,0.4)",
        strokeColor: "rgba(0,255,0,0.4)",
        pointColor: "#3b8bba",
        pointStrokeColor: "rgba(0,255,0,0.4)",
        pointHighlightFill: "#fff",
        pointHighlightStroke: "rgba(60,141,188,1)",
        data: [finaleBayarray[9], finaleBayarray[10], finaleBayarray[11]]
      }
    ]
  };

  var salesChartOptions = {
    //Boolean - If we should show the scale at all
    showScale: true,
    //Boolean - Whether grid lines are shown across the chart
    scaleShowGridLines: false,
    //String - Colour of the grid lines
    scaleGridLineColor: "rgba(0,0,0,.05)",
    //Number - Width of the grid lines
    scaleGridLineWidth: 1,
    //Boolean - Whether to show horizontal lines (except X axis)
    scaleShowHorizontalLines: true,
    //Boolean - Whether to show vertical lines (except Y axis)
    scaleShowVerticalLines: true,
    //Boolean - Whether the line is curved between points
    bezierCurve: true,
    //Number - Tension of the bezier curve between points
    bezierCurveTension: 0.3,
    //Boolean - Whether to show a dot for each point
    pointDot: false,
    //Number - Radius of each point dot in pixels
    pointDotRadius: 4,
    //Number - Pixel width of point dot stroke
    pointDotStrokeWidth: 1,
    //Number - amount extra to add to the radius to cater for hit detection outside the drawn point
    pointHitDetectionRadius: 20,
    //Boolean - Whether to show a stroke for datasets
    datasetStroke: true,
    //Number - Pixel width of dataset stroke
    datasetStrokeWidth: 2,
    //Boolean - Whether to fill the dataset with a color
    datasetFill: true,
    //String - A legend template
    legendTemplate: "<ul class=\"<%=name.toLowerCase()%>-legend\"><% for (var i=0; i<datasets.length; i++){%><li><span style=\"background-color:<%=datasets[i].lineColor%>\"></span><%=datasets[i].label%></li><%}%></ul>",
    //Boolean - whether to maintain the starting aspect ratio or not when responsive, if set to false, will take up entire container
    maintainAspectRatio: false,
    //Boolean - whether to make the chart responsive to window resizing
    responsive: true
  };

  //Create the line chart
  salesChart.Line(salesChartData, salesChartOptions);

  //---------------------------
  //- END MONTHLY SALES CHART -
  //---------------------------

  //-------------
  //- PIE CHART -
  //-------------
  // Get context with jQuery - using jQuery's .get() method.

  //-----------------
  //- END PIE CHART -
  //-----------------

  /* jVector Maps
   * ------------
   * Create a world map with markers
   */
    
    
      
  $('#world-map-markers').vectorMap({
    map: 'world_mill_en',
    normalizeFunction: 'polynomial',
    hoverOpacity: 0.7,
    hoverColor: false,
    backgroundColor: 'transparent',
    regionStyle: {
      initial: {
        fill: 'rgba(210, 214, 222, 1)',
        "fill-opacity": 1,
        stroke: 'none',
        "stroke-width": 0,
        "stroke-opacity": 1
      },
      hover: {
        "fill-opacity": 0.7,
        cursor: 'pointer'
      },
      selected: {
        fill: 'yellow'
      },
      selectedHover: {
      }
    },
    markerStyle: {
      initial: {
        fill: '#00a65a',
        stroke: '#111'
      }
    },
    markers: [
      {latLng: [41.90, 12.45], name: 'Vatican City'},
      {latLng: [43.73, 7.41], name: 'Monaco'},
      {latLng: [-0.52, 166.93], name: 'Nauru'},
      {latLng: [-8.51, 179.21], name: 'Tuvalu'},
      {latLng: [43.93, 12.46], name: 'San Marino'},
      {latLng: [47.14, 9.52], name: 'Liechtenstein'},
      {latLng: [7.11, 171.06], name: 'Marshall Islands'},
      {latLng: [17.3, -62.73], name: 'Saint Kitts and Nevis'},
      {latLng: [3.2, 73.22], name: 'Maldives'},
      {latLng: [35.88, 14.5], name: 'Malta'},
      {latLng: [12.05, -61.75], name: 'Grenada'},
      {latLng: [13.16, -61.23], name: 'Saint Vincent and the Grenadines'},
      {latLng: [13.16, -59.55], name: 'Barbados'},
      {latLng: [17.11, -61.85], name: 'Antigua and Barbuda'},
      {latLng: [-4.61, 55.45], name: 'Seychelles'},
      {latLng: [7.35, 134.46], name: 'Palau'},
      {latLng: [42.5, 1.51], name: 'Andorra'},
      {latLng: [14.01, -60.98], name: 'Saint Lucia'},
      {latLng: [6.91, 158.18], name: 'Federated States of Micronesia'},
      {latLng: [1.3, 103.8], name: 'Singapore'},
      {latLng: [1.46, 173.03], name: 'Kiribati'},
      {latLng: [-21.13, -175.2], name: 'Tonga'},
      {latLng: [15.3, -61.38], name: 'Dominica'},
      {latLng: [-20.2, 57.5], name: 'Mauritius'},
      {latLng: [26.02, 50.55], name: 'Bahrain'},
      {latLng: [0.33, 6.73], name: 'São Tomé and Príncipe'}
    ]
  });

  /* SPARKLINE CHARTS
   * ----------------
   * Create a inline charts with spark line
   */

  //-----------------
  //- SPARKLINE BAR -
  //-----------------
  $('.sparkbar').each(function () {
    var $this = $(this);
    $this.sparkline('html', {
      type: 'bar',
      height: $this.data('height') ? $this.data('height') : '30',
      barColor: $this.data('color')
    });
  });

  //-----------------
  //- SPARKLINE PIE -
  //-----------------
  $('.sparkpie').each(function () {
    var $this = $(this);
    $this.sparkline('html', {
      type: 'pie',
      height: $this.data('height') ? $this.data('height') : '90',
      sliceColors: $this.data('color')
    });
  });

  //------------------
  //- SPARKLINE LINE -
  //------------------
  $('.sparkline').each(function () {
    var $this = $(this);
    $this.sparkline('html', {
      type: 'line',
      height: $this.data('height') ? $this.data('height') : '90',
      width: '100%',
      lineColor: $this.data('linecolor'),
      fillColor: $this.data('fillcolor'),
      spotColor: $this.data('spotcolor')
    });
  });


  var bar = new Morris.Bar({
          element: 'bar-chart',
          resize: true,
          data: [
            {y: 'Jan',  a: +finaleBayarray[1] , b: +finalflipkartarray[1],c:+finalamazonarray[1]},
            {y: 'Feb',  a: +finaleBayarray[2] , b: +finalflipkartarray[2],c:+finalamazonarray[2]},
            {y: 'Mar',  a: +finaleBayarray[3] , b: +finalflipkartarray[3],c:+finalamazonarray[3]},
            {y: 'Apr',  a: +finaleBayarray[4] , b: +finalflipkartarray[4],c:+finalamazonarray[4]},
            {y: 'May',  a: +finaleBayarray[5] , b: +finalflipkartarray[5],c:+finalamazonarray[5]},
            {y: 'Jun',  a: +finaleBayarray[6] , b: +finalflipkartarray[6],c:+finalamazonarray[6]},
            {y: 'Jul',  a: +finaleBayarray[7] , b: +finalflipkartarray[7],c:+finalamazonarray[7]},
            {y: 'Aug',  a: +finaleBayarray[8] , b: +finalflipkartarray[8],c:+finalamazonarray[8]},
            {y: 'Sep', a: +finaleBayarray[9], b: +finalflipkartarray[9],c:+finalamazonarray[9]},
            {y: 'Oct',  a: +finaleBayarray[10], b: +finalflipkartarray[10],c:+finalamazonarray[10]},
            {y: 'Nov',  a: +finaleBayarray[11], b: +finalflipkartarray[11],c:+finalamazonarray[11]},
            {y: 'Dec',  a: +finaleBayarray[12], b: +finalflipkartarray[12],c:+finalamazonarray[12]},
           
          ],
          barColors: ['#00a65a', '#6495ED','#f56954'],
          xkey: 'y',
          ykeys: ['a', 'b','c'],
          labels: ['eBay', 'Flipkart','Amazon'],
          //hideHover: 'auto'
        });
});

</script>
@stop
@extends('layouts.footer')