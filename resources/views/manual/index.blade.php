@extends('layouts.default')

@extends('layouts.header')

@extends('layouts.sideview')

@section('content')
  
  @section('style')
    {{HTML::style('jqwidgets/styles/jqx.base.css')}}
    <style type="text/css">
        #jqxResponsivePanel, #content {
            float: left;
        }
        .jqx-rc-all {
            border-radius: 0px;
        }
        body, html {
            width: 100%;
            height: 100%;
            padding: 0;
            margin: 0;
            overflow: hidden;
        }
    </style>         
  @stop

  @section('script')
      
      {{HTML::script('jqwidgets/jqxcore.js')}}
      {{HTML::script('jqwidgets/jqxmenu.js')}}
      {{HTML::script('jqwidgets/jqxtree.js')}}
      {{HTML::script('jqwidgets/jqxbuttons.js')}}
      {{HTML::script('jqwidgets/jqxscrollbar.js')}}
      {{HTML::script('jqwidgets/jqxpanel.js')}}
      {{HTML::script('jqwidgets/jqxresponsivepanel.js')}}
      {{HTML::script('scripts/demos.js')}}
    <script type="text/javascript">
        $(document).ready(function () {
          $('#jqxMenu').jqxTree();
 
          $('#jqxResponsivePanel').jqxResponsivePanel({
                collapseBreakpoint: 700,
                toggleButton: $('#toggleResponsivePanel'),
                animationType: 'none',
                autoClose: false
            });
    

                    
            $('#jqxResponsivePanel').on('open expand close collapse', function (event) {
                if (event.args.element)
                    return;

                var collapsed = $('#jqxResponsivePanel').jqxResponsivePanel('isCollapsed');
                var opened = $('#jqxResponsivePanel').jqxResponsivePanel('isOpened');

            });

            
        });
    </script>
  @stop


  <div class="box">
    <div class="box-header">
      <h3 class="box-title"><strong>User </strong>  Manual</h3>                 
      
    </div>
    <div class="col-md-4" id="jqxResponsivePanel">
      <div id="jqxMenu" >
        <ul>
          @foreach($results as $result)
            <li>
              <a href="javascript:void(0);" onclick="loadContent({{$result->manual_id}})">{{$result->screen_name}}</a>
              @if(isset($result->child) && !empty($result->child))
                <ul>
                  @foreach($result->child as $child)
                    <li>
                      <a href="javascript:void(0);" onclick="loadContent({{$child->manual_id}})">{{$child->screen_name}}</a>
                    </li>
                  @endforeach
                </ul>
              @endif
            </li>
          @endforeach
          </li>  
        </ul>        
      </div>   
    </div>
    <div class="col-md-8">
          <div class="box box-solid">
            <div class="box-header with-border">
              <div class="row">
                <div class="col-md-10" id="heading"><h3 class="box-title">{{$results[0]->screen_name}}</h3></div>
                
                <div class="col-md-1 pullright" id="previous">@if($results[0]->previous_screen_id > 0)<a href="javascript:void(0)" id="addLookup" class="pull-right" onclick="loadContent({{$results[0]->previous_screen_id}})"> <span>Previous</span></a>@endif</div>
                <div class="col-md-1 pullright" id="next">@if($results[0]->next_screen_id > 0)<a href="javascript:void(0)" id="addLookup" class="pull-right" onclick="loadContent({{$results[0]->next_screen_id}})"> <span>Next</span></a>@endif</div>
                
              </div>
              
            </div>
            <!-- /.box-header -->
            <div class="box-body" id="content">
              {{$results[0]->content}}          
            </div>
            <!-- /.box-body -->
          </div>
          <!-- /.box -->
        </div>
  </div>
  <script type="text/javascript">
    function loadContent(Id){ 
      $.get('manual/getManual/' + Id, function (data) { 
            var result = $.parseJSON(data);
            
            $("#heading").html('<h3 class="box-title">'+result[0].screen_name+'</h3>');
            
            if(result[0].previous_screen_id> 0) {
              $("#previous").html('<a href="javascript:void(0)" class="pull-right" onclick="loadContent('+result[0].previous_screen_id+')"> <span>Previous</span></a>');
            } else { $("#previous").html(''); } 
            
            if(result[0].next_screen_id> 0) {
              $("#next").html('<a href="javascript:void(0)" class="pull-right" onclick="loadContent('+result[0].next_screen_id+')"> <span>Next</span></a>');
            } else { $("#next").html(''); } 

            $("#content").html(result[0].content);
          
        });
    }


  </script>    
@stop      