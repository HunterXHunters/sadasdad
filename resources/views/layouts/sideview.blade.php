@section('sideview')
<section class="sidebar">
  <ul class="sidebar-menu">
    <!-- Sidebar -->
    @foreach($roleFeatures as $menu)
    <li class="treeview">
      <a href="<?PHP echo isset($menu->url) ? URL::asset($menu->url) : '';?>"><i class="<?PHP echo isset($menu->icon) ? $menu->icon : '';?>"></i> <span><?PHP echo isset($menu->name) ? $menu->name : '';?></span> <i class="fa fa-angle-left pull-right"></i></a>
      <ul class="treeview-menu">
         <?PHP if(isset($menu->submenus)) { ?>    
         @foreach($menu->submenus as $submenu)
           <?PHP $submenusArr = explode('-', $submenu); ?>
           <li><a href="<?PHP echo (!empty($submenusArr[1])) ? URL::asset($submenusArr[1]) : '#';?>"><i class="fa fa-circle-o"></i> {{$submenusArr[0]}}</a></li>
         @endforeach
         <?PHP } ?>
     </ul>
   </li>
   @endforeach
   <!-- Sidebar end --> 
 </ul>
</section>

@stop