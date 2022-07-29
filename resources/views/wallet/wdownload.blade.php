<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>eSealCentral | Dashboard</title>
  <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>
  
  <!-- Bootstrap 3.3.4 -->
  <link href="/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
  <!-- Font Awesome Icons -->
  <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css" rel="stylesheet" type="text/css" />
  
  <!-- Theme style -->
  <link href="/css/AdminLTE.min.css" rel="stylesheet" type="text/css" />
  <link href="{{ URL::asset('css/_all-skins.min.css') }}" rel="stylesheet" type="text/css" />
  <link href="{{ URL::asset('css/bootstrapValidator.css') }}" rel="stylesheet" type="text/css" />
    <script src="{{URL::asset('scripts/jquery-1.10.2.min.js')}}"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.4.1/css/bootstrap-datepicker3.css"/>
      </head>
      <body class="skin-blue sidebar-collapse sidebar-mini">
        <!-- Site wrapper -->
        <div class="wrapper">
          

        

       

        
          <section class="content">
            <style type="text/css">
              .box {border-top :1px solid #d2d6de;}
              .info-tile {
                  margin: 0px 0px 15px;
                  box-shadow: 0 1px 3px 0px rgba(0, 0, 0, 0.2);
                  background: #fff;
                  padding: 16px;
                  position: relative;
                  overflow: hidden;
                  border-radius: 2px;
                  display: block;
              }
              .info-tile .tile-icon {
                  position: absolute;
                  height: 160px;
                  width: 160px;
                  border-radius: 50%;
                  left: -80px;
                  bottom: -80px;
                  color: #9f9f9f;
              }
              .info-tile .tile-body {
                  text-align: right;
                  color: #616161;
                  font-size: 30px;
                  font-weight: 400;
                  line-height: 72px;
                  position: relative;
                  z-index: 1;
               }
               .info-tile .tile-footer {
                  text-align: right;
                  font-size: 12px;
                  position: absolute;
                  right: 12px;
                  bottom: 8px;
              }   
              .info-tile .tile-icon i {
                  font-size: 52px;
                  position: absolute;
                  left: 96px;
                  top: 0;
              }
            </style>
            <div class="box">
              <div class="box-header with-border">
                  <div class="col-sm-6 col-xs-6" style="padding-top: 10px;"><h3 class="box-title">eSeal Wallet</h3></div>
                </div><!-- /.box-header -->
                <div class="box-body">
                  <div class="col-sm-3 col-xs-12">
                    <div class="box" style="border: 2px solid #d2d6de;">
                    <div class="box-header">
                          <h3 class="box-title">Current Balance</h3>

                        </div><!-- /.box-header --> 
                        <div class="box-body" >
                          <div class="row">
                        <div class="col-sm-12 col-xs-12">
                          <div class="info-tile" style="visibility: visible; opacity: 1; display: block; transform: translateY(0px);">
                            <div class="tile-icon"><i class="fa fa-barcode"></i></div>
                            <div class="tile-body"><span id="availableIds">{{$availableIds}}</span></div>
                                  <div class="tile-footer" align="right">Bank <i class="fa fa-question-circle" data-toggle="tooltip" title="IDs available"></i></div>
                          </div>
                        </div>
                      </div>                    
                      
                        </div>
                  </div>
                  </div>
                  <div class="col-sm-9 col-xs-12">
                    <div class="box" style="border: 2px solid #d2d6de;">
                    <div class="box-header">
                          <h3 class="box-title">Drawn Analysis</h3>
                        </div><!-- /.box-header --> 
                        <div class="box-body" >
                          <div class="col-md-4">
                        <div class="info-tile" style="visibility: visible; opacity: 1; display: block; transform: translateY(0px);">
                          <div class="tile-icon"><i class="fa fa-download"></i></div>
                          <div class="tile-body"><span id="issuedIds">{{$drawn_count}}</span></div>
                                <div class="tile-footer" align="right">
                                  <div>Total Drawn <i class="fa fa-question-circle" data-toggle="tooltip" title="IDs printed or download but not activated"></i></div>
                                </div>
                        </div>
                      </div>                
                      <div class="col-md-4" >
                        <div class="info-tile" style="visibility: visible; opacity: 1; display: block; transform: translateY(0px);">
                          <div class="tile-icon"><i class="fa fa-print"></i></div>
                          <div class="tile-body"><span id="printIds">{{$print_count}}</span></div>
                                <div class="tile-footer" align="right">
                                  <div>Print <i class="fa fa-question-circle" data-toggle="tooltip" title="IDs printed  but not activated"></i></div>
                                </div>
                        </div>
                      </div>
                      <div class="col-md-4" >
                        <div class="info-tile" style="visibility: visible; opacity: 1; display: block; transform: translateY(0px);">
                          <div class="tile-icon"><i class="fa fa-download"></i></div>
                          <div class="tile-body"><span id="downloadIds">{{$download_count}}</span></div>
                                <div class="tile-footer" align="right">
                                  <div>Downloaded <i class="fa fa-question-circle" data-toggle="tooltip" title="IDs download but not activated"></i></div>
                                </div>
                        </div>
                      </div>
                        </div>
                  </div>
                  </div>
                  <div class="col-sm-12 col-xs-12">
                    <div class="box">
                      <div class="box-header">
                          <h3 class="box-title">Usage History</h3>
                        </div><!-- /.box-header --> 
                      <div class="box-body">
                        <div class="row">
                        <div class="col-md-3" >
                          <div class="info-tile" style="visibility: visible; opacity: 1; display: block; transform: translateY(0px);" >
                            <div class="tile-icon"><i class="fa fa-bar-chart-o"></i></div>
                            <div class="tile-body"><span id="totalIds">{{$totalUsedCount}}</span></div>
                                  <div class="tile-footer" align="right">Total Used <i class="fa fa-question-circle" data-toggle="tooltip" title="IDs scanned at least once"></i></div>
                          </div>
                        </div>
                        <div class="col-md-3" >
                          <div class="info-tile" style="visibility: visible; opacity: 1; display: block; transform: translateY(0px);" >
                            <div class="tile-icon"><i class="fa fa-file"></i></div>
                            <div class="tile-body"><span id="productIds">{{$usagesHistory[0]->qty}}</span></div>
                                  <div class="tile-footer" align="right">Products <i class="fa fa-question-circle" data-toggle="tooltip" title="IDs used for products"></i></div>
                          </div>
                        </div>
                        <div class="col-md-3" >
                          <div class="info-tile" style="visibility: visible; opacity: 1; display: block; transform: translateY(0px);" >
                            <div class="tile-icon"><i class="fa fa-cube"></i></div>
                            <div class="tile-body"><span id="cartonIds">{{$usagesHistory[1]->qty}}</span></div>
                                  <div class="tile-footer" align="right">Cartons <i class="fa fa-question-circle" data-toggle="tooltip" title="IDs used for cartons"></i></div>
                          </div>
                        </div>
                        <div class="col-md-3" >
                          <div class="info-tile" style="visibility: visible; opacity: 1; display: block; transform: translateY(0px);" >
                            <div class="tile-icon"><i class="fa fa-truck"></i></div>
                            <div class="tile-body"><span id="tpIds">{{$usagesHistory[3]->qty}}</span></div>
                                  <div class="tile-footer" align="right">TP <i class="fa fa-question-circle" data-toggle="tooltip" title="IDs used for TP"></i></div>
                          </div>
                        </div>
                      </div>
                      </div>
                    
                    <div class="box-header">
                          <h3 class="box-title">Usage Analysis</h3>
                        </div><!-- /.box-header --> 
                        <div class="box-body">

                        
                      <div class="row">
                        <div class="col-md-12">
                          <h4>Used ID's : <span id="IdsResult">{{$currentMonthUsedId}}</span></h4>
                        </div>
                      </div> 

                      <div class="row">
                        <div class="col-md-12" id="stmntcanvasDiv">
                          <canvas id="stmntcanvas" style="width: 681px; height: 285px;"></canvas>
                        </div>
                      </div> 
                        </div>
                  </div>
                  </div>
                
                
                  <div class="col-sm-12 col-xs-12">
                    <div class="box ">
                        <div class="box-header with-border">
                          <h3 class="box-title">Invoice  History</h3>
                        </div><!-- /.box-header -->
                        <div class="box-body">
                          <div class="table-responsive">
                            <table class="table no-margin">
                                    <thead>
                                        <tr>
                                          <th>Invoice Number</th>
                                          <th>PO Number</th>
                                          <th>Date</th>
                                          <th>Quantity</th>
                                          <th>Type</th>
                                          
                                        </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($po_results as $result)
                                      <tr>
                                        <td><a href="{{URL::asset($result->invoice_file_path)}}" target="_blank">{{$result->invoice_no}}</a></td>
                                        <td><a href="{{URL::asset($result->invoice_file_path)}}" target="_blank">{{$result->po_number}}</a></td>
                                        <td>{{$result->date}}</td>  
                                        <td><?PHP echo number_format($result->quantity,0,'.',',');?></td>
                                        <td>{{$result->po_for}}</td>
                                        
                                      </tr>
                                    @endforeach 
                                    </tbody>
                                </table>    
                          </div>
                        </div>
                    </div>
                  </div>
                </div>
            </div>
         
          </section>

       

        
        
        
      </div><!-- ./wrapper -->
	  <script src="/js/classie.js"></script>
	  <script src="/js/cbpViewModeSwitch.js"></script>
      <!-- Bootstrap 3.3.2 JS -->
      <script src="/js/bootstrap.min.js" type="text/javascript"></script>
      <!-- SlimScroll -->
      <script src="/js/plugins/slimScroll/jquery.slimscroll.min.js" type="text/javascript"></script>
      <!-- FastClick -->
      <script src='/js/plugins/fastclick/fastclick.min.js'></script>
      <!-- AdminLTE App -->
      <script src="/js/app.min.js" type="text/javascript"></script>
      <script src="/js/bootstrapValidator.js"></script>
      <!-- Helper -->
      <script src="/js/helper.js"></script>
      <script src="/js/common-validator.js"></script> 
      <script src="/js/jquery.validate.min.js"></script> 
      <!-- jQuery UI-->
      <script src="/js/plugins/jQueryUI/jquery-ui.js"></script>
      <!-- Demo -->
      <script src="/js/demo.js" type="text/javascript"></script>
      <!-- @yield('footer') -->
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.4.1/js/bootstrap-datepicker.min.js"></script>
  <script type="text/javascript" src="/assets/plugins/chartJs/Chart.bundle.js"></script>

  <script type="text/javascript" src="/assets/plugins/chartJs/utils.js"></script>
    <script type="text/javascript">
    var Months = {{$months}};
    var quantity = {{$quantity}}
    getChart(Months,quantity,"stmntcanvas");
      

    function getChart(labels,quantity,canvasId)
    {
      var stmntChatData = {
        labels: labels,
        datasets:[{
          label: 'IDs',
              backgroundColor: "#0825f3",
              borderColor: "#0825f3",
              borderWidth: 1,
              data: quantity
        }]
      };
      var stmntCanvas = $("#"+canvasId).get(0).getContext("2d");
      stmntCanvas.canvas.width = "618px";
      stmntCanvas.canvas.height = "279px";
      window.myBar = new Chart(stmntCanvas, {
            type: 'bar',
            data: stmntChatData,
            options: {
                responsive: true,
                legend: {
                    display: false,
                },
                elements: {
                    rectangle: {
                        borderWidth: 2,
                    }
                },
                tooltips: {
                    mode: 'nearest',
                    intersect: true
                },
                scales: {
                    xAxes: [{
                        ticks: {
                            beginAtZero: true,
                            
                        },
                        barPercentage: 0.4,
                        stacked: false,

                    }],
                    yAxes:[{stacked: false}]
                },
                
                title: {
                    display: false,
                    text: ''
                }
            }
        });
    }

    </script>
    </body>
    </html>