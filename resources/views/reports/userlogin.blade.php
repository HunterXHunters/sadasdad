@extends('layouts.default')

@extends('layouts.header')

@extends('layouts.sideview')

@section('content')

    <style>
        .col-md-3 {
            width: 20% !important;
            padding-right:0px !important;
        }
        .col-sm-1 {
            width: 6.333333% !important;
            padding-left: 0px!important;
            padding-right: 0px!important;
        }
    </style>

    <!-- /.box -->

    <div class="box collapsed-box">
        <div class="box-header with-border">
            <h3 class="box-title"><strong>User Login</strong> Report</h3>
            <div class="box-tools1 pull-right">
                <button class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-filter"></i></button>
            </div>

            <!-- /.box-tools -->
        </div><!-- /.box-header -->
        <div class="box-body">
         @if(!empty($customers))
  
  
    <div class="form-group col-sm-5">
      <label for="exampleInputEmail">Choose Customer</label>
      <div id="selectbox">
      <!-- {{ Form::open(array('url' => '/dashboard/iotBankReport','method'=>'POST','id'=>'IOTBFrm','role'=>"form",'name'=>'IOTBFrm')) }}
       -->  <select class="form-control requiredDropdown" id="customer_id" name="customer_id"       parsley-trigger="change" parsley-required="true" parsley-error-container="#selectbox" onchange="getproductsLocationTypesProductTypesCategories()" >
          <option  value="">Select Customer</option>
            @foreach($customers as $customer)  
              <option value="{{$customer->customer_id}}" >{{$customer->brand_name}}</option>
            @endforeach
        </select>
        <!-- {{Form::close()}} -->
      </div>
    </div>
    <div class="clr"></div>
    @else
    <input type = "hidden" name ="customer_id" id ="customer_id" value= "{{$customerId}}">
    @endif

          <div class="row">
            <div class="form-group col-sm-4">
                <label for="exampleInputEmail">Users</label>
                <div class="input-group ">
                    <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
                    <div id="selectbox">
                        <select class="list-unstyled selectpicker" data-live-search="true" id="user" name="user"
                                parsley-trigger="change" parsley-required="true" parsley-error-container="#selectbox" onchange="disableLocations()">
                            <option  value="">Select User</option>
                            @foreach($users as $key=>$result)
                                <option  value="{{$key}}">{{$result}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
            <div class="form-group col-sm-4">
                <label for="exampleInputEmail">Location Type</label>
                <div class="input-group ">
                    <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
                    <div id="selectbox">
                        <select multiple class="list-unstyled selectpicker" data-live-search="true" id="location_type" name="location_type"
                                parsley-trigger="change" parsley-required="true" parsley-error-container="#selectbox" onchange="disableUsers()">
                            <option  value="">Please Select</option>
                            @foreach($location_types as $key=>$result)
                                <option  value="{{$result->location_type_id}}">{{$result->location_type_name}}</option>
                            @endforeach
                        </select>
                    </div>                    
                </div>

            </div>
             

            <div class="form-group col-sm-2" style="margin-top:14px">
            <label for="exampleInputEmail"> </label>
            <div class="input-group ">
<!--             <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
 -->            <div id="filter_box">
            <button class="btn btn-primary" data-toggle="modal" data-target="" id="filter" onclick="makeGrid()" >Filter</button>
            </div>
           
            </div>
            </div>
            <div class="form-group col-sm-2 pull-right" style="margin-top:33px" >
            <div class="input-group ">
             <div >
        <button class="btn btn-primary pull-right" id='export_excel' data-toggle="modal" onclick="Export()">Export</button>        
        </div>
         </div>
           </div>
            </div>

        </div><!-- /.box-body -->
        
       


        <div class="col-sm-12">
            <div class="tile-body nopadding">
                <div id="jqxgrid"></div>
            </div>
        </div>
    </div>


@stop

@section('style')
    {{HTML::style('jqwidgets/styles/jqx.base.css')}}
    {{HTML::style('css/bootstrap-select.css')}}
@stop

@section('script')
    {{HTML::script('js/plugins/bootstrap-select/bootstrap-select.js')}}
    {{HTML::script('js/plugins/bootstrap-select/bootstrap-datepicker.min.js')}}
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
    var location_type =0;
    var user =0;
    var cust_id = 0;
    
        $(document).ready(function ()
        {            
            var location_type = $('#location_type').val();                                                
            var user = $('#user').val();

            $("#export_excel").hide();
                      

           if(!location_type && !user) 
             $("#filter").prop('disabled',true); 
            
                    
          

         
        });// document ready function closing


        function makeGrid(){
                
                var location_type = $('#location_type').val();                                
                var user = $('#user').val();                
                if(user == null){
                    user =0;
                }
                if(location_type == null){
                    location_type =0;
                }
                
                var cust = $('#customer_id').val();
               
                if(cust == "" || typeof(cust) == 'undefined'){
                    cust = 0;
                }
                pre_cust_id = cust;
                if(user==''){
                     user=0;
                }
                if(location_type==''){
                     location_type=0;
                }
                
                $('#export_excel').show();                
                var url = "/reports/user/getData/"+ location_type +'/' + user + '/' + pre_cust_id;

                 var source =
                    {
                        datatype: "json",
                        datafields: [
                            { name: 'username', type: 'string' },
                            { name: 'firstname', type: 'string' },
                            { name:'lastname', type: 'string'},
                            { name: 'email', type: 'string' },
                            { name: 'phone_no', type: 'string' },
                            { name: 'status', type: 'string' },
                            { name: 'created_on', type: 'string' },                            
                            { name: 'role', type: 'string' },
                            { name: 'location_type_name', type: 'string' },
                            { name: 'last_login', type: 'string' }
                        ],
                        id: 'id',
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
                        width: "100%",
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
                        columns:
            [
                            { text: 'User Name', datafield: 'username', width: "10%"},
                            { text: 'First Name', datafield: 'firstname', width: "5%" },
                            { text: 'Last Name', datafield: 'lastname', width: "5%"},
                            { text: 'Email', datafield: 'email', width:"15%"},
                            { text: 'Phone No', datafield: 'phone_no', width: "10%"},
                            { text: 'Status', datafield: 'status', width: "5%"},
                            { text: 'Created On', datafield: 'created_on', width: "10%"},                            
                            { text: 'Role', datafield: 'role', width: "10%"},
                            { text: 'Location Type', datafield: 'location_type_name', width: "10%"},
                            { text: 'Last login', datafield: 'last_login', width: "20%"}
                        ],
                        
                    });

                
            
            }          



          function disableLocations(){
            var location_type = $("#location_type").val();
            var user = $("#user").val();

            if(user)
             $("#location_type").prop("disabled", true);
            else
             $("#location_type").prop("disabled", false);   
            
            if(!location_type && !user)            
             $("#filter").prop("disabled", true);
            else
             $("#filter").prop("disabled", false);   
          }  

          function disableUsers(){
            var location_type = $("#location_type").val();
            var user = $("#user").val();
            
            if(location_type)
             $("#user").prop("disabled", true);
            else
             $("#user").prop("disabled", false);   
            
            if(!location_type && !user)            
             $("#filter").prop("disabled", true);
            else
             $("#filter").prop("disabled", false);   
          }  


      function Export(){
     
        var type = 'xls';
        var location_type = $('#location_type').val();                                
        var user = $('#user').val();                                
        var customer_id = $('#customer_id').val();                                

        if(location_type=='')
            location_type = 0;        

        if(user=='')
            user = 0;
       
            var url = "/reports/user/getExportData/"+ location_type +'/' + user + '/' + customer_id + '/' + type;
             window.location = url;
    }      
          


    
    </script>
@stop 