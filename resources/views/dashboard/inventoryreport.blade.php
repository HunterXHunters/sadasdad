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
            <h3 class="box-title"><strong>Inventory</strong>Report</h3>
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
    <input type = "hidden" name ="customer_id" id ="customer_id" value= "{{$cust_id}}">
    @endif


            <div class="form-group col-sm-5">
                <label for="exampleInputEmail">Product Group</label>
                <div class="input-group ">
                    <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
                    <div id="selectbox">
                        <select class="list-unstyled selectpicker" data-live-search="true" id="product_group" name="product_group"
                                parsley-trigger="change" parsley-required="true" parsley-error-container="#selectbox" onchange="changeProducts()">
                            <option  value="">Select Product Group</option>
                            @foreach($product_groups as $key=>$result)
                                <option  value="{{$key}}">{{$result}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
            <div class="form-group col-sm-5">
                <label for="exampleInputEmail">Location Type</label>
                <div class="input-group ">
                    <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
                    <div id="selectbox">
                        <select class="list-unstyled selectpicker" data-live-search="true" id="location_type" name="location_type"
                                parsley-trigger="change" parsley-required="true" parsley-error-container="#selectbox" onchange="changeLocations()">
                            <option  value="">Select Location Type</option>
                            @foreach($location_types as $key=>$result)
                                <option  value="{{$result->location_type_id}}">{{$result->location_type_name}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
            {{--<div class="col-sm-1" style="margin-top:25px;">--}}
            {{--<button class="btn btn-primary" data-toggle="modal" data-target="" onclick="makeGrid();" >Filter</button>--}}
            {{--</div>--}}


            
            <div class="form-group col-sm-5">
                <label for="exampleInputEmail">Category</label>
                <div class="input-group ">
                    <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
                    <div id="selectbox">
                        <select class="list-unstyled selectpicker" data-live-search="true" id="category" name="category" parsley-trigger="change" parsley-required="true" parsley-error-container="#selectbox" onchange="changeProducts()">
                            <option  value="0">Select Category</option>
                            @foreach($categories as $key=>$category)
                                <option  value="{{$key}}">{{$category}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="form-group col-sm-5">
                <label for="exampleInputEmail">Location</label>
                <div class="input-group ">
                    <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
                    <div id="selectbox">
                        <select class="" multiple="multiple" id="location_id" name="location_id[]"  onchange = "changeStorageLocations()">
                            <!-- <option  value="0">Select Location</option> -->
                            {{--@foreach($locations as $key=>$result)--}}
                                {{--<option  value="{{$result->location_id}}">{{$result->location_name}}</option>--}}
                            {{--@endforeach--}}
                        </select>
                    </div>
                </div>
            </div>
            {{--<div class="col-sm-1" style="margin-top:25px;">--}}
                {{--<button class="btn btn-primary" data-toggle="modal" data-target="" onclick="makeGrid();" >Filter</button>--}}
            {{--</div>--}}


            <div class="form-group col-sm-5">
                <label for="exampleInputEmail">Product</label>
                <div class="input-group ">
                    <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
                    <div id="selectbox">
                        <select class="" id="product_id" name="product_id[]"  multiple="multiple">
                            
                                @foreach($products as $key=>$result)
                                    <option  value="{{$key}}">{{$result}}</option>
                                @endforeach
                        </select>
                    </div>
                </div>
            </div>
            @if($storage_location_exists)
            <div class="form-group col-sm-5">
                <label for="exampleInputEmail">Storage Location</label>
                <div class="input-group ">
                    <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
                    <div id="selectbox">
                        <select class="list-unstyled selectpicker" data-live-search="true" id="storage_location_id" name="storage_location_id"
                                parsley-trigger="change" parsley-required="true" parsley-error-container="#selectbox" >
                            <option>Select Storage Location</option>
                            @foreach($locations as $key=>$result)
                                <option  value="{{$result->location_id}}">{{$result->location_name}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
            @endif
            <input type="hidden" value="0" id="storage_location_id">

            {{--<div class="form-group ">--}}
                {{--<label for="exampleInputEmail"></label>--}}
                {{--<div class="input-group ">--}}
                    {{--<div id="button">--}}
                        {{--<button class="btn btn-primary" data-toggle="modal"  onclick="makeGrid();">Filter</button>--}}
                        {{--<button type="button" class="btn btn-primary" aria-label="Left Align" id="demo">--}}
                            {{--<span class="glyphicon glyphicon-refresh" aria-hidden="true"></span>--}}
                        {{--</button>--}}
                    {{--</div>--}}
                    {{--<input type="hidden" id="module_id" name="module_id" value="{{$module_id}}">--}}
                    {{--<input type="hidden" id="access_token" name="access_token" value="{{$access_token}}">--}}
                {{--</div>--}}
            {{--</div>--}}
            
            <!-- ------------------------- -->
             <!-- <div class="form-group col-sm-5">
                <label for="exampleInputEmail">From</label>
                <div class="input-group ">
                    <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
                    <div id="">
                        <input type="date" name="from_date" id="from_date">
                     </div>
                 </div>
             </div>



            <div class="form-group col-sm-5">
                <label for="exampleInputEmail">To</label>
                <div class="input-group ">
                    <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
                    <div id="">
                        <input type="date" name="to_date" id="to_date">
                    </div>
                </div>
            </div> -->

            <div class="form-group col-sm-2">
                <label for="exampleInputEmail"></label>
                <div class="input-group ">
                    <div id="button">
                        <button class="btn btn-primary" data-toggle="modal"  onclick="makeGrid();">Filter</button>
                        <button type="button" class="btn btn-primary" aria-label="Left Align" id="demo">
                            <span class="glyphicon glyphicon-refresh" aria-hidden="true"></span>
                        </button>
                    </div>
                    <input type="hidden" id="module_id" name="module_id" value="{{$module_id}}">
                    <input type="hidden" id="access_token" name="access_token" value="{{$access_token}}">
                </div>
            </div>






        </div><!-- /.box-body -->
        <div class="export col-sm-4 col-sm-offset-8">
        <button class="btn btn-primary pull-right" id='export_excel' data-toggle="modal"  onclick="Export(1)">Export</button>
        <button class="btn btn-primary pull-right" id='export_excel_againstiots' data-toggle="modal"  onclick="Export(2)">Export IOT WISE</button>
        </div>


        <div class="col-sm-12">
            <div class="tile-body nopadding">
                <div id="jqxgrid"></div>
            </div>
        </div>
    </div>








<link href="http://cdn.rawgit.com/davidstutz/bootstrap-multiselect/master/dist/css/bootstrap-multiselect.css"
    rel="stylesheet" type="text/css" />
<script src="http://cdn.rawgit.com/davidstutz/bootstrap-multiselect/master/dist/js/bootstrap-multiselect.js"
    type="text/javascript"></script>

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
    var pre_location_id =0;
    var pre_product_id =0;
    var pre_cust_id = 0;
    var pre_storage_location = '';
    var pre_product_group = 0;
    var pre_location_type = 0;
    var pre_category = 0;
        $(document).ready(function() {
            $('#export_excel').hide();
            $('#export_excel_againstiots').hide();
            //$('#location_id').selectpicker('refresh');
           // $('#product_id').selectpicker('refresh');
            $('.box-body').show();
            $('#demo').click(function() {
                //alert('wrking')
                var module_id=document.getElementById('module_id').value;
                var access_token=document.getElementById('access_token').value;
                //var ajaxTime= new Date().getTime();
                $.ajax
                (
                    {
                        url: "/job/updateInventory",
                        type: "GET",
                        data: "module_id=" + module_id + "&access_token=" + access_token,
                        success: function(response)
                        {
                            //var request_time = new Date().getTime() - start_time;
                            //alert(request_time);
                            window.location = "/dashboard/inventoryReport";
                        },
                        error:function()
                        {
                        }
                    }
                );
                $.blockUI({ css: {
                    border: 'none',
                    padding: '15px',
                    backgroundColor: '#000',
                    '-webkit-border-radius': '10px',
                    '-moz-border-radius': '10px',
                    opacity: .5,
                    color: '#fff'
                } });

                setTimeout($.unblockUI, 15000);
            });
        });

        $(document).ready(function ()
        {
//            var pre_location_id=document.getElementById('location_id').value;
  //              var pre_product_id=document.getElementById('product_id').value;

            makeGrid = function (){
                //var product_id=$('[name="location_id"]').val())
                //var location_id=document.getElementById('location_id').value;
                var location_id = $('#location_id').val();
                //var product_id=document.getElementById('product_id').value;
                var product_id = $('#product_id').val();
                if(product_id == null){
                    product_id =0;
                }
                if(location_id == null){
                    location_id =0;
                }
                var storage_location = document.getElementById('storage_location_id').value;
                var cust = $('#customer_id').val();
                var from_date="";//document.getElementById('from_date').value;
                var to_date="";//document.getElementById('to_date').value;
                var prod_grp = $('#product_group').val();
                var loc_type = $('#location_type').val();
                var cat =  $('#category').val();
                pre_product_id = product_id;
                pre_location_id = location_id;
                pre_storage_location = storage_location;
                pre_product_group = prod_grp;
                pre_location_type = loc_type;
                pre_category = cat;
                //alert(location_id);
                //alert(product_id);
                if(cust == "" || typeof(cust) == 'undefined'){
                    cust = 0;
                }
                pre_cust_id = cust;
                if(product_id==''){
                     product_id=0;
                }
                if(location_id==''){
                     location_id=0;
                }
                if(from_date == '' || from_date == null){
                    from_date =0;
                }
                if(to_date == '' || to_date == null){
                    to_date =0;
                }
                if(storage_location == '' || storage_location == null){
                    storage_location = '';
                }
                $('#export_excel').show();
                $('#export_excel_againstiots').show();
                var url = "/dashboard/getData/"+ location_id +'/' + product_id + '/' + storage_location + '/' +from_date  + '/' + to_date + '/'+ cust+'?product_group='+prod_grp+'&location_type='+loc_type+'&category='+cat;
                // alert(url);
                @if($storage_location_exists)
                var source =
                    {
                        datatype: "json",
                        datafields: [
                            { name: 'product_name', type: 'string' },
                            { name: 'location_name', type: 'string' },
                            { name:'category_name', type: 'string'},
                            { name: 'available_inventory', type: 'integer' },
                            { name: 'storage_location', type: 'integer' }
                        ],
                        id: 'id',
                        url: url,
                        pager: function (pagenum, pagesize, oldpagenum) {
                            // callback called when a page or page size is changed.
                        }
                    };
                    @else
                    var source =
                    {
                        datatype: "json",
                        datafields: [
                            { name: 'product_name', type: 'string' },
                            { name: 'location_name', type: 'string' },
                            { name:'category_name', type: 'string'},
                            { name: 'available_inventory', type: 'integer' },
                        ],
                        id: 'id',
                        url: url,
                        pager: function (pagenum, pagesize, oldpagenum) {
                            // callback called when a page or page size is changed.
                        }
                    };
                @endif
                // var source =
                //     {
                //         datatype: "json",
                //         datafields: [
                //             { name: 'product_name', type: 'string' },
                //             { name: 'location_name', type: 'string' },
                //             { name:'category_name', type: 'string'},
                //             { name: 'available_inventory', type: 'integer' }
                //         ],
                //         id: 'id',
                //         url: url,
                //         pager: function (pagenum, pagesize, oldpagenum) {
                //             // callback called when a page or page size is changed.
                //         }
                //     };
                var dataAdapter = new $.jqx.dataAdapter(source);
                //alert(JSON.stringify(source));
                createGrid(dataAdapter);
                // var datainformations = $('#jqxgrid').jqxGrid('getdatainformation');
                // var rowscounts = datainformations.rowscount; 
                // alert(rowscounts);
                
            }
            @if($storage_location_exists)
            var columns = 
            [
                                { text: 'Product Name', datafield: 'product_name', width: "30%"},
                            { text: 'Location Name', datafield: 'location_name', width: "30%" },
                            { text: 'Category Name', datafield: 'category_name', width: "20%"},
                            { text: 'Available Inventory', datafield: 'available_inventory', width:"10%"},
                            {text:'Storage Location',datafield: 'storage_location',width:"10%"}
                        ];
            @else
            var columns = 
            [
                                { text: 'Product Name', datafield: 'product_name', width: "40%"},
                            { text: 'Location Name', datafield: 'location_name', width: "30%" },
                            { text: 'Category Name', datafield: 'category_name', width: "20%"},
                            { text: 'Available Inventory', datafield: 'available_inventory', width:"10%"}
                        ];
            @endif

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
                        columns: columns
                        
                    });

                
            
            }


            //makeGrid(0);

        });

        function test(){
            //alert('test');
            var location_id=document.getElementById('location_id').value;
            var product_id=document.getElementById('product_id').value;
            //alert(product_id);
            $.ajax({
                url: "/dashboard/getTotal", //This is the page where you will handle your SQL insert
                type: "GET",
                data: "location_id=" + location_id + '&product_id=' + product_id , //The data your sending to some-page.php
                success: function(response){

                    document.getElementById('total_id').innerHTML= response[0].total_inventory;

                    document.getElementById('available_id').innerHTML= response[0].available_inventory;

                    document.getElementById('intransit_id').innerHTML= response[0].intransit_inventory;

                    document.getElementById('reserved_id').innerHTML= response[0].reserved_inventory;

                    document.getElementById('sold_id').innerHTML= response[0].sold_inventory;
                },
                error:function(){
                    // console.log("AJAX request was a failure");
                }
            });
        }
    function changeProducts(){
            var product_group=$('#product_group').val();
            var category_id = $('#category').val();
            var cust = $('#customer_id').val();

            // if((product_group == "" || product_group == 0) && (category_id == "" || category_id == 0)){
            //     $('#product_id').empty();
            //     var b=new Option('Select Product',0);
            //     $('#product_id').append(b);
            //      $('#product_id').selectpicker('refresh');
            // }
           // else{
                 $.ajax({
                 url: "/dashboard/getProducts", //This is the page where you will handle your SQL insert
                type: "GET",
                data: 'product_group_id=' +product_group+'&category_id='+category_id+'&customer_id='+cust, //The data your sending to some-page.php
                success: function(response){
                    $('#product_id').multiselect('destroy');
                    $('#product_id').empty();
                    var opt='';
                    var select=$('#product_id');
                   // opt=new Option('Select Product',0);
                    //select.append(opt);
                    $.each(response,function(key,value){
                        var opt=new Option(value,key);
                        select.append(opt);
                    });
                    select.multiselect({
                            nonSelectedText :'Select Products',
                               includeSelectAllOption: true,
                                       enableFiltering:true,
                                        numberDisplayed: 2,
                        enableCaseInsensitiveFiltering: true,
                        maxHeight: 300
                    });

                    
                },
                error:function(){
                    // console.log("AJAX request was a failure");
                }

            });
          //  }           
    }
    function changeLocations(){
        var location_type=$('#location_type').val();
        var cust = $('#customer_id').val();
        $('#location_id').multiselect('destroy');
        if(location_type== 0 || location_type == ""){
            $('#location_id').empty();
            var opt="";
            //opt=new Option('Select Location',0);
            //$('#location_id').append(opt);
             //$('#location_id').selectpicker('refresh');
             $('#location_id').multiselect({
                nonSelectedText :'Select Locations',
               includeSelectAllOption: true,
                       enableFiltering:true,
                        numberDisplayed: 2,
                 enableCaseInsensitiveFiltering: true,
                 maxHeight: 300
             });
        }
        else{
            $.ajax({
                url: "/dashboard/getLocations", //This is the page where you will handle your SQL insert
                type: "GET",
                data: 'location_type_id=' +location_type+'&customer_id='+cust, //The data your sending to some-page.php
                success: function(response){
                    $('#location_id').multiselect('destroy');
                    $('#location_id').empty();
                    var opt='';
                    var select=$('#location_id');
                    //opt=new Option('Select Location',0);
                    //select.append(opt);
                    $.each(response,function(key,value){
                        var opt=new Option(value,key);
                        select.append(opt);
                    });
                    //select.selectpicker('refresh');
                    $('#location_id').multiselect({
                nonSelectedText :'Select Locations',
               includeSelectAllOption: true,
                       enableFiltering:true,
                        numberDisplayed: 2,
                        enableCaseInsensitiveFiltering: true,
                        maxHeight: 300
             });

                    
                },
                error:function(){
                    // console.log("AJAX request was a failure");
                }
            });
        }

    }


    function Export(type){
        //type {1-- For getting the normal report
            // 2 -- for exporting the report with IOt wise}
        //var location_id=document.getElementById('location_id').value;
        //var product_id=document.getElementById('product_id').value;   
        //if(pre_location_id)
        //alert(pre_location_id);

        if(pre_cust_id == "" || typeof(pre_cust_id) == 'undefined'){
                    pre_cust_id = 0;
            }
       
            var url = "/dashboard/exportExcel/"+ pre_location_id +'/' + pre_product_id + '/' + pre_storage_location + '/' + pre_cust_id+'?product_group='+pre_product_group+'&location_type='+pre_location_type+'&category='+pre_category+'&type='+type;
             window.location = url;
            // $.ajax({
            //     url:url,
            //     method:"GET",
            //     success:function(response){
            //         alert("sucesss");
            //     },
            //     error:function(){
            //         alert('error');
            //     }

            // });            
        

    }


    function changeStorageLocations(){

        var location=$('#location_id').val();
        var cust = $('#customer_id').val();
        if(location== 0 || location == ""){
            $('#storage_location_id').empty();
            var opt="";
            opt= new Option('Select Storage Location');
            $('#storage_location_id').append(opt);
             $('#storage_location_id').selectpicker('refresh');
        }
        else{
            $.ajax({
                url: "/dashboard/getStorageLocations", //This is the page where you will handle your SQL insert
                type: "GET",
                data: 'location_id=' +location+'&customer_id='+cust, //The data your sending to some-page.php
                success: function(response){
                    $('#storage_location_id').empty();
                    var opt='';
                    var select=$('#storage_location_id');
                    opt=new Option('Select Storage Location');
                    select.append(opt);
                    $.each(response,function(key,value){
                        var opt=new Option(value);
                        select.append(opt);
                    });
                    select.selectpicker('refresh');

                    
                },
                error:function(){
                    // console.log("AJAX request was a failure");
                }
            });
        }


    }

    function getproductsLocationTypesProductTypesCategories(){
          var cust=$('#customer_id').val();
          $.ajax({
            url:"getproductsLocationTypesProductTypesCategoriesbycustomerId",
            data:'customer_id='+cust,
            method:'get',
            success:function(response){

                $('#product_group').empty();
                $('#location_type').empty();
                $('#category').empty();
                $('#product_id').multiselect('destroy');
                $('#product_id').empty();
                opt = new Option('Select Product Group',0);
                $('#product_group').append(opt);
                opt = new Option('Select Location Type',0);
                $('#location_type').append(opt);
                opt = new Option('Select Category',0);
                $('#category').append(opt);
                //opt = new Option('Select Product',0);
                //$('#product_id').append(opt);
                $.each(response['product_groups'],function(i,val){
                    opt = new Option(val,i);
                    $('#product_group').append(opt);
                });
                $.each(response['location_types'],function(i,val){
                    opt = new Option(val,i);
                    $('#location_type').append(opt);
                });
                $.each(response['categories'],function(i,val){
                    opt = new Option(val,i);
                    $('#category').append(opt);
                });
                $.each(response['products'],function(i,val){
                    opt = new Option(val,i);
                    $('#product_id').append(opt);
                })

                $('#product_group').selectpicker('refresh');
                $('#location_type').selectpicker('refresh');
                $('#category').selectpicker('refresh');
                $('#product_id').multiselect({
                    nonSelectedText :'Select Products',
                   includeSelectAllOption: true,
                           enableFiltering:true,
                            numberDisplayed: 2,
                    enableCaseInsensitiveFiltering: true,
                    maxHeight: 300
                });
            }
          });
    }


    $('#product_id').multiselect({
    //columns: 1,
    nonSelectedText :'Select Products',
   includeSelectAllOption: true,
           enableFiltering:true,
            numberDisplayed: 2,
        enableCaseInsensitiveFiltering: true,
        maxHeight: 300
});
    $('#location_id').multiselect({
    //columns: 1,
    nonSelectedText :'Select Locations',
   includeSelectAllOption: true,
           enableFiltering:true,
            numberDisplayed: 2,
        enableCaseInsensitiveFiltering: true,
        maxHeight: 300
});

    </script>
@stop