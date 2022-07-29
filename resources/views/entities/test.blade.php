 @extends('layouts.default')

@extends('layouts.sideview')

@section('content')



  
  <body>

      <table class="tree">

      	@foreach($entities as $key => $value)
        <tr class="treegrid-1">
          <td>{{ $value->entity_name }}</td><td>Additional info</td>
        </tr>
         
         @foreach($entities1 as $key => $value)
             <tr class="treegrid-2 treegrid-parent-1">
          <td>{{ $value->entity_name }}</td><td>Additional info</td>
        </tr>
        	@foreach($entities2 as $key => $value)
        <tr class="treegrid-3 treegrid-parent-2">
          <td>{{ $value->entity_name }}</td><td>Additional info</td>
        </tr>
        @endforeach
        @endforeach
        @endforeach
        <!--<tr class="treegrid-4 treegrid-parent-3">
          <td>Node 1-2-1</td><td>Additional info</td>
        </tr>-->
      </table>	  

  </body>
</html>
@stop


@section('style')
    {{HTML::style('css/jquery.treegrid.css')}}
@stop    

@section('script')
    {{HTML::script('js/jquery.treegrid.js')}}
@stop  

    <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>

    <script type="text/javascript">
      $(document).ready(function() {
        $('.tree').treegrid();
      });
    </script>

@extends('layouts.footer')