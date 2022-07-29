<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>eSealCentral | Dashboard</title>
  <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>
  <meta name="csrf-token" content="{{ csrf_token() }}">
  
  <!-- Bootstrap 3.3.4 -->
  <link href="{{ URL::asset('/css/bootstrap.min.css') }}" rel="stylesheet" type="text/css" />
  <!-- Font Awesome Icons -->
  <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css" rel="stylesheet" type="text/css" />
  <!-- Ionicons -->
  <link href="{{ URL::asset('/css/ionicons.css') }}" rel="stylesheet" type="text/css" />
  <!-- Theme style -->
  <link href="{{ URL::asset('/css/AdminLTE.min.css') }}" rel="stylesheet" type="text/css" />
    <!-- AdminLTE Skins. Choose a skin from the css/skins 
    folder instead of downloading all of them to reduce the load. -->
  <link href="{{ URL::asset('/css/_all-skins.min.css') }}" rel="stylesheet" type="text/css" />
  <link href="{{ URL::asset('/css/bootstrapValidator.css') }}" rel="stylesheet" type="text/css" />
  <!-- jQuery UI -->
  <link rel="stylesheet" href="{{ URL::asset('/css/jquery-ui.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ URL::asset('/css/component.css') }}" rel="stylesheet" type="text/css" />

@yield('style')

 <script src="https://code.jquery.com/jquery-1.9.1.min.js"></script>
  <script src="https://code.jquery.com/ui/1.10.3/jquery-ui.min.js"></script>

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
        <script src="https://oss.maxcdn.c/om/respond/1.4.2/respond.min.js"></script>
        <![endif]/-->
      </head>
      <body class="skin-blue sidebar-collapse sidebar-mini">
        <!-- Site wrapper -->
        <div class="wrapper">
          
          <header class="main-header">
            <a href="#" class="logo">
              <span class="logo-mini"><img src="/img/eseal-logo.png"></span>
              <span class="logo-lg"><img src="/img/eseal-logo.png"> Eseal</span>
            </a>
            <nav class="navbar navbar-static-top" role="navigation">
              <a href="#" class="sidebar-toggle" data-toggle="offcanvas" role="button">
                <span class="sr-only">Toggle navigation</span>
              </a>
              @if(Session::has('customerLogoPath'))
              <span class="logo-mini"><a class="navbar-brand" href="#" style="padding-top: 6px;"><img src="{{ URL::asset(Session::get('customerLogoPath')) }}" alt=""/></a></span>
              @endif  
              <div class="navbar-custom-menu">
                <ul class="nav navbar-nav">
                @if(Session::has('cust_temp_cust_id'))
                <?php 
                  $mystring = isset($_SERVER['HTTP_REFERER'])?$_SERVER['HTTP_REFERER']:'';
                  $findme   = 'orders';
                  $pos = strpos($mystring, $findme);
                  if ($pos !== false) {
                  ?>
                    <li><a class="" href="/orders/checkOut/<?php echo Session::get('ima_id'); ?>/<?php echo Session::get('iot_id'); ?>/<?php echo Session::get('cust_temp_cust_id'); ?>" onclick="cartsubmit()" data-toggle="tooltip" data-original-title="Cart"><i class="ion-ios-cart-outline"></i><span id="cart"><?php echo Session::get('cartValue'); ?></span></a>
                    </li>
                    <?php } ?> 
                @endif                   
               <!--  <li class="dropdown messages-menu">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                      <i class="fa fa-envelope-o"></i>
                      <span class="label label-success">4</span>
                    </a>
                    <ul class="dropdown-menu">
                      <li class="header">You have 4 messages</li>
                      <li>
                        <ul class="menu">
                          <li>
                            <a href="#">
                              <div class="pull-left">
                                <img src="/img/user2-160x160.jpg" class="img-circle" alt="User Image"/>
                              </div>
                              <h4>
                                Support Team
                                <small><i class="fa fa-clock-o"></i> 5 mins</small>
                              </h4>
                              <p>Why not buy a new awesome theme?</p>
                            </a>
                          </li>
                          <li>
                            <a href="#">
                              <div class="pull-left">
                                <img src="/img/user3-128x128.jpg" class="img-circle" alt="user image"/>
                              </div>
                              <h4>
                                AdminLTE Design Team
                                <small><i class="fa fa-clock-o"></i> 2 hours</small>
                              </h4>
                              <p>Why not buy a new awesome theme?</p>
                            </a>
                          </li>
                          <li>
                            <a href="#">
                              <div class="pull-left">
                                <img src="img/user4-128x128.jpg" class="img-circle" alt="user image"/>
                              </div>
                              <h4>
                                Developers
                                <small><i class="fa fa-clock-o"></i> Today</small>
                              </h4>
                              <p>Why not buy a new awesome theme?</p>
                            </a>
                          </li>
                          <li>
                            <a href="#">
                              <div class="pull-left">
                                <img src="/img/user3-128x128.jpg" class="img-circle" alt="user image"/>
                              </div>
                              <h4>
                                Sales Department
                                <small><i class="fa fa-clock-o"></i> Yesterday</small>
                              </h4>
                              <p>Why not buy a new awesome theme?</p>
                            </a>
                          </li>
                          <li>
                            <a href="#">
                              <div class="pull-left">
                                <img src="/img/user4-128x128.jpg" class="img-circle" alt="user image"/>
                              </div>
                              <h4>
                                Reviewers
                                <small><i class="fa fa-clock-o"></i> 2 days</small>
                              </h4>
                              <p>Why not buy a new awesome theme?</p>
                            </a>
                          </li>
                        </ul>
                      </li>
                      <li class="footer"><a href="#">See All Messages</a></li>
                    </ul>
                  </li> -->
                 <!--  <li class="dropdown notifications-menu">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                      <i class="fa fa-bell-o"></i>
                      <span class="label label-warning">10</span>
                    </a>
                    <ul class="dropdown-menu">
                      <li class="header">You have 10 notifications</li>
                      <li>
                        <ul class="menu">
                          <li>
                            <a href="#">
                              <i class="fa fa-users text-aqua"></i> 5 new members joined today
                            </a>
                          </li>
                          <li>
                            <a href="#">
                              <i class="fa fa-warning text-yellow"></i> Very long description here that may not fit into the page and may cause design problems
                            </a>
                          </li>
                          <li>
                            <a href="#">
                              <i class="fa fa-users text-red"></i> 5 new members joined
                            </a>
                          </li>

                          <li>
                            <a href="#">
                              <i class="fa fa-shopping-cart text-green"></i> 25 sales made
                            </a>
                          </li>
                          <li>
                            <a href="#">
                              <i class="fa fa-user text-red"></i> You changed your username
                            </a>
                          </li>
                        </ul>
                      </li>
                      <li class="footer"><a href="#">View all</a></li>
                    </ul>
                  </li> -->

                  <li class="dropdown user user-menu">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                        @if(Session::has('userLogoPath'))
                          <img src="{{ URL::asset(Session::get('userLogoPath')) }}" class="user-image" alt="User Image" />
                        @else
                          <img src="/img/avatar5.png" class="user-image" alt="User Image" />
                        @endif
                        
                      <span class="hidden-xs">{{Session::get('userName')}}</span>
                    </a>
                    <ul class="dropdown-menu">
                      <li class="user-header">
                          @if(Session::has('userLogoPath'))
                            <img src="{{URL::asset(Session::get('userLogoPath')) }}" class="img-circle" alt="User Image" />
                          @else
                            <img src="/img/avatar5.png" class="img-circle" alt="User Image" />
                          @endif
                          
                        
                        <p>
                      <span class="hidden-xs">{{Session::get('userName')}}</span>
<!--                           <small>Member since Nov. 2014</small>
 -->                        </p>
                      </li>
                      <li class="user-footer">
                        <div class="pull-left">
                          <a href="#" class="btn btn-default btn-flat">Profile</a>
                        </div>
                        <div class="pull-right">
                          <a href="/logout" class="btn btn-default btn-flat">Sign out</a>
                        </div>
                      </li>
                    </ul>
                  </li>
                </ul>
              </div>
            </nav>
          </header>

          <!-- =============================================== -->

          <!-- Left side column. contains the sidebar -->
          <aside class="main-sidebar">
           <!-- Sidebar -->
           @yield('sideview')
           
           <!-- Sidebar end --> 
           
         </aside>

         <div class="content-wrapper">

          <section class="content-header">
            @if(isset($breadcrumd))
            <ol class="breadcrumb">
              {!! $breadcrumd !!}
              
            </ol>
            @endif

          </section>

          <section class="content">

            @yield('content')
          </section>

        </div>
   

        <footer class="main-footer">
          <strong>Copyright &copy; <?php echo date('Y');?> <a href="#">eSealCentral</a>.</strong> All rights reserved.
        </footer>
  
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
<!--       <script src="/js/plugins/jQueryUI/jquery-ui.js"></script>
 -->      <!-- Demo -->
      <script src="/js/demo.js" type="text/javascript"></script>
      <!-- @yield('footer') -->
       @yield('script') 
           @if(Session::has('errorMsg'))
    
           <button class="btn" id="errorMsgBtn" data-toggle="modal" data-target="#wizardCodeModal" style="display: none">
      Add New Role
    </button>  
    <!-- /tile header -->

    <div class="modal fade" id="wizardCodeModal" tabindex="-1" role="dialog" aria-labelledby="wizardCode" aria-hidden="true" style="display: none;">
      <div class="modal-dialog wide">
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">x</button>
            <h4 class="modal-title" id="wizardCode">Error Message</h4>
          </div>
          <div class="modal-body">

             <?PHP   echo Session::get('errorMsg'); ?>
              <?PHP Session::forget('errorMsg');?>  
          </div>
        </div><!-- /.modal-content -->
      </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->  
         
<script> 
    $(document).ready(function(){
        $("#errorMsgBtn").click();
    });   
</script>
     
    @endif   
    </body>
    </html>
