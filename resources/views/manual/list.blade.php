@extends('layouts.default')

@extends('layouts.header')

@extends('layouts.sideview')

@section('content')

@section('style')
    {{HTML::style('jqwidgets/styles/jqx.base.css')}}    
@stop

  @section('script')
      
      {{HTML::script('jqwidgets/jqxcore.js')}}
      {{HTML::script('jqwidgets/jqxdata.js')}}
      {{HTML::script('js/common-validator.js')}}
      {{HTML::script('js/jquery.validate.min.js')}}
      {{HTML::script('js/helper.js')}}
      {{HTML::script('jqwidgets/jqxbuttons.js')}}
      {{HTML::script('jqwidgets/jqxscrollbar.js')}}
      {{HTML::script('jqwidgets/jqxdatatable.js')}}
      {{HTML::script('jqwidgets/jqxtreegrid.js')}}
      {{HTML::script('scripts/demos.js')}}

<script type="text/javascript">

  $(document).ready(function () 
  {              
      ajaxCall();
  });

  function ajaxCall()
  {
    $.ajax(
              {
                  url: "getManualList",
                  success: function(result)
                  {
                      var employees = result;
                      // prepare the data
                      var source =
                      {
                          datatype: "json",
                          datafields: [
                          { name: 'screen_name', type: 'string' },
                          { name: 'child_screen_name', type: 'string' },
                          { name: 'previous_screen_name', type: 'string' },
                          { name: 'next_screen_name', type: 'string' },
                          { name: 'actions', type: 'string' },
                          { name: 'children', type: 'array' },
                          { name: 'expanded', type: 'bool' }
                          ],
                          hierarchy:
                          {
                              root: 'children'
                          },
                          id: 'mid',
                      class: 'configuration_grid',
                          localData: employees
                      };
                      var dataAdapter = new $.jqx.dataAdapter(source);
                      // create Tree Grid
                      $("#treeGrid").jqxTreeGrid(
                      {
                          width: "100%",
                          source: dataAdapter,
                          sortable: true,
                          //autoheight: true,
                          //autowidth: true,
                          columns: [
                     { text: 'Screen Name', datafield: 'screen_name', width:"40%"},
                    { text: 'Child Screen Name', datafield: 'child_screen_name', width:"30%"},
                    { text: 'Previous Screen',  datafield: 'previous_screen_name', width: "10%" },
                    { text: 'Next Screen',  datafield: 'next_screen_name', width: "10%" },
                    
                    //{ text: 'State', datafield: 'state', width: 150 },
                    { text: 'Actions', datafield: 'actions',width:"10%" }
                          ]
                      });


                  }
              });
  }

  function deleteManual(Id)
    {
      var cnf = confirm("Are you sure you want to Delete ?");
      if(cnf){
        window.location.href='delete/'+Id;
      }
    }

  </script>
  @stop

     @if (Session::has('message'))
     <div class="flash alert">
         <p>{{ Session::get('message') }}</p>
     </div>
     @endif

  <div class="box">
    <div class="box-header">
      <h3 class="box-title"><strong>User </strong>  Manual</h3>
      @if(isset($addPermission) && $addPermission == 1)
      <a href="addnew" class="pull-right" >
      <i class="fa fa-plus-circle"></i> <span>Add New</span></a>
      @endif
      
      
      @if (Session::has('flash_message'))            
        <div class="alert alert-info">{{ Session::get('flash_message') }}</div>
      @endif
       
    </div>
    <div class="box-body">
      <div id="treeGrid" ></div>
    </div>
    <div class="box-footer">
    </div>  
  </div>
  @stop

