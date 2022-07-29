@section('header')
<!-- Fixed navbar -->
    <div class="navbar navbar-default navbar-fixed-top" role="navigation"> 
      
      <!-- Branding -->
      <div class="navbar-header col-md-2" style="padding-left:0px !important"> 
      	<a href="#" class="esel-logo"><img src="{{ URL::asset('img/eseal-logo.png') }}" width="40" height="40" alt=""/></a>
        @if(Session::has('customerLogoPath'))
      	<a class="navbar-brand" href="#">
          <img src="{{ URL::asset(Session::get('customerLogoPath')) }}" width="111" height="37" alt=""/>
        </a>
        @endif
      </div>
      <!-- Branding end --> 
      
      <!-- .nav-collapse -->
      <div class="navbar-collapse"> 
        
        <!-- Content collapsing at 768px to sidebar -->
        <div class="collapsing-content"> 
          <!-- Quick Actions -->
          @if(Session::has('userId')) 
          <div class="user-controls">
            <ul>
              <li class="dropdown messages"> 
                <a class="dropdown-toggle" data-toggle="dropdown" href="#"> 
                    <div class="profile-photo"> 
                        @if(Session::has('userLogoPath'))
                            <img src="{{ URL::asset(Session::get('userLogoPath')) }}" alt /> 
                        @else 
                            <img src="{{ URL::asset('/img/avatar5.png') }}" alt /> 
                        @endif
                    </div>
                </a>
                
                <ul class="dropdown-menu wide arrow red nopadding">
                  <li>
                    <h1>You have <strong>3</strong> new messages</h1>
                  </li>
                  <li> 
                  	<a class="cyan" href="#">
                        <div class="profile-photo"><img src="img/ici-avatar.jpg" alt /></div>
                        <div class="message-info"> 
                          <span class="sender">Ing. Imrich Kamarel</span> 
                          <span class="time">12 mins</span>
                          <div class="message-content">Duis aute irure dolor in reprehenderit in voluptate velit esse cillum</div>
                        </div>
                    </a> 
                  </li>
                  <li> 
                    <a class="green" href="#">
                    	<div class="profile-photo"> <img src="img/arnold-avatar.jpg" alt /> </div>
                        <div class="message-info"> 
                          <span class="sender">Arnold Karlsberg</span> 
                          <span class="time">1 hour</span>
                          <div class="message-content">Lorem ipsum dolor sit amet, consectetur adipisicing elit</div>
                        </div>
                    </a> 
                  </li>
                  <li> 
                    <a href="#">
                      <div class="profile-photo"> <img src="img/profile-photo.jpg" alt /> </div>
                      <div class="message-info"> <span class="sender">John Douey</span> <span class="time">3 hours</span>
                        <div class="message-content">Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia</div>
                      </div>
                    </a> 
                  </li>
                  <li> 
                  	<a class="red" href="#">
                        <div class="profile-photo"> <img src="img/peter-avatar.jpg" alt /> </div>
                        <div class="message-info"> <span class="sender">Peter Kay</span> <span class="time">5 hours</span>
                          <div class="message-content">Ut enim ad minim veniam, quis nostrud exercitation</div>
                        </div>
                    </a> 
                  </li>
                  <li>
                  	<a class="orange" href="#">
                        <div class="profile-photo"> <img src="img/george-avatar.jpg" alt /> </div>
                        <div class="message-info"> <span class="sender">George McCain</span> <span class="time">6 hours</span>
                          <div class="message-content">Lorem ipsum dolor sit amet, consectetur adipisicing elit</div>
                        </div>
                    </a> 
                  </li>
                  <li>
                  	<a href="#">
                    	Check all messages 
                        <i class="fa fa-angle-right"></i>
                    </a>
                  </li>
                </ul>
              </li>
              <li class="dropdown settings"> 
                  <a class="dropdown-toggle options" data-toggle="dropdown" href="#"> 
                    <i class="fa fa-cog"></i> 
                  </a>
                <ul class="dropdown-menu arrow">
                  <li>
                    <h3>Color schemes:</h3>
                    <ul id="color-schemes">
                      <li><a href="#" class="brownish-scheme" title="Brownish"></a></li>
                      <li><a href="#" class="darkgrey-scheme" title="Dark Grey"></a></li>
                      <li><a href="#" class="lightgrey-scheme" title="Light Grey"></a></li>
                      <li><a href="#" class="cyan-scheme" title="Cyan"></a></li>
                      <li><a href="#" class="red-scheme" title="Red"></a></li>
                      <li><a href="#" class="orange-scheme" title="Orange"></a></li>
                      <li><a href="#" class="green-scheme" title="Green"></a></li>
                      <li><a href="#" class="amethyst-scheme" title="Amethyst"></a></li>
                    </ul>
                  </li>
                  <li class="divider"></li>
                  <li> <a href="#"><i class="fa fa-user"></i> Profile</a> </li>
                  <li> <a href="#"><i class="fa fa-calendar"></i> Calendar</a> </li>
                  <li> <a href="#"><i class="fa fa-envelope"></i> Inbox <span class="badge badge-red" id="user-inbox">3</span></a> </li>
                  <li class="divider"></li>
                  <li> <a href="/logout"><i class="fa fa-power-off"></i> Logout</a> </li>
                </ul>
              </li>
            </ul>
          </div>
          <ul class="nav navbar-nav" style="float:right !important">            
            <li class="dropdown quick-action notifications"> 
              <a class="dropdown-toggle button" data-toggle="dropdown" href="#"> 
                <i class="fa fa-bell"></i> 
                <span class="overlay-label orange">12</span> 
              </a>
              <ul class="dropdown-menu wide arrow orange nopadding">
                <li>
                  <h1>You have <strong>12</strong> new notifications</h1>
                </li>
                <li> 
                  <a href="#"> 
                    <span class="label label-green">
                      <i class="fa fa-user"></i>
                    </span> 
                    New user registered. 
                    <span class="small">18 mins</span> 
                  </a> 
                </li>
                <li> 
                  <a href="#"> 
                    <span class="label label-red">
                      <i class="fa fa-power-off"></i>
                    </span> 
                    Server down. 
                    <span class="small">27 mins</span> 
                  </a> 
                </li>
                <li> 
                  <a href="#"> 
                    <span class="label label-orange">
                      <i class="fa fa-plus"></i>
                    </span> 
                    New order. 
                    <span class="small">36 mins</span> 
                  </a> 
                </li>
                <li> 
                  <a href="#"> 
                    <span class="label label-cyan">
                      <i class="fa fa-power-off"></i>
                    </span> 
                    Server restared. 
                    <span class="small">45 mins</span> 
                  </a> 
                </li>
                <li> 
                  <a href="#"> 
                    <span class="label label-amethyst">
                      <i class="fa fa-power-off"></i>
                    </span> 
                    Server started.
                    <span class="small">50 mins</span> 
                  </a>
                </li>
                <li>
                  <a href="#">
                    Check all notifications 
                    <i class="fa fa-angle-right"></i>
                  </a>
                </li>
              </ul>
            </li>
          </ul>
          <!-- Quick Actions end --> 
          
          @else
          <div class="user-controls">
            <ul>
                <li> <a href="/login"><i class="fa fa-power-off"></i> Login </a> </li>
            </ul>
          </div>    
            @endif         
          
        </div>
        <!-- /Content collapsing at 768px to sidebar --> 
        
        <!-- Sidebar -->
			@yield('sideview')
         
        <!-- Sidebar end --> 
      </div>
      <!--/.nav-collapse --> 
      
    </div>
@stop