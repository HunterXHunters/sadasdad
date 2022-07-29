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
                        <select class="list-unstyled selectpicker" data-live-search="true" id="location_id" name="location_id" parsley-trigger="change" parsley-required="true" parsley-error-container="#selectbox" onchange = "changeStorageLocations()">
                            <option  value="0">Select Location</option>
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
                        <select class="list-unstyled selectpicker" data-live-search="true" id="product_id" name="product_id"
                                parsley-trigger="change" parsley-required="true" parsley-error-container="#selectbox" >
                            <option  value="0">Select Product</option>
                                @foreach($products as $key=>$result)
                                    <option  value="{{$key}}">{{$result}}</option>
                                @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="form-group col-sm-5">
                <label for="exampleInputEmail">Storage Location</label>
                <div class="input-group ">
                    <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
                    <div id="selectbox">
                        <select class="list-unstyled selectpicker" data-live-search="true" id="storage_location_id" name="storage_location_id"
                                parsley-trigger="change" parsley-required="true" parsley-error-container="#selectbox" >
                            <option>Select Storage Location</option>
                            {{--@foreach($locations as $key=>$result)--}}
                                {{--<option  value="{{$result->location_id}}">{{$result->location_name}}</option>--}}
                            {{--@endforeach--}}
                        </select>
                    </div>
                </div>
            </div>

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
        <div class="export col-sm-2 col-sm-offset-10">
        <button class="btn btn-primary" id='export_excel' data-toggle="modal"  onclick="Export()">Export</button>
        </div>

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
    var pre_location_id =0;
    var pre_product_id =0;
    var pre_storage_location = '';
    var pre_product_group = '';
    var pre_location_type = '';
    var pre_category = '';
        $(document).ready(function() {
            $('#export_excel').hide();
            $('#location_id').selectpicker('refresh');
            $('#product_id').selectpicker('refresh');
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
                var location_id=document.getElementById('location_id').value;
                var product_id=document.getElementById('product_id').value;
                var storage_location = document.getElementById('storage_location_id').value;
                var product_group = document.getElementById('product_group').value;
                var location_type = document.getElementById('location_type').value;
                var category = document.getElementById('category').value;

                pre_product_group = (product_group == "" || product_group == "0")? "ALL": product_group;
                pre_location_type = (location_type == "" || location_type =="0")? "ALL" : location_type;
                pre_category = (category == "" || category == "0")? "ALL" : category;
                pre_product_id = product_id;
                pre_location_id = location_id;
                pre_storage_location = storage_location;
                var from_date="";//document.getElementById('from_date').value;
                var to_date="";//document.getElementById('to_date').value;

                //alert(location_id);
                //alert(product_id);
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
                var url = "/dashboard/getData/"+ location_id +'/' + product_id + '/' + storage_location + '/' +from_date  + '/' + to_date;
                // alert(url);
                var source =
                    {
                        datatype: "json",
                        datafields: [
                            { name: 'product_name', type: 'string' },
                            { name: 'location_name', type: 'string' },
                            { name:'category_name', type: 'string'},
                            { name: 'available_inventory', type: 'integer' },
                            { name: 'storage_location', type: 'string' }
                        ],
                        id: 'id',
                        url: url,
                        pager: function (pagenum, pagesize, oldpagenum) {
                            // callback called when a page or page size is changed.
                        }
                    };
                var dataAdapter = new $.jqx.dataAdapter(source);
                //alert(JSON.stringify(source));
                createGrid(dataAdapter);
                // var datainformations = $('#jqxgrid').jqxGrid('getdatainformation');
                // var rowscounts = datainformations.rowscount; 
                // alert(rowscounts);
                
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
                        columns: [
                                { text: 'Product Name', datafield: 'product_name', width: "30%"},
                            { text: 'Location Name', datafield: 'location_name', width: "30%" },
                            { text: 'Category Name', datafield: 'category_name', width: "20%"},
                            { text: 'Available Inventory', datafield: 'available_inventory', width:"10%"},
                            { text: 'Storage Location', datafield: 'storage_location', width:"10%"}
                        ]
                        
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
                data: 'product_group_id=' +product_group+'&category_id='+category_id, //The data your sending to some-page.php
                success: function(response){
                    $('#product_id').empty();
                    var opt='';
                    var select=$('#product_id');
                    opt=new Option('Select Product',0);
                    select.append(opt);
                    $.each(response,function(key,value){
                        var opt=new Option(value,key);
                        select.append(opt);
                    });
                    select.selectpicker('refresh');

                    
                },
                error:function(){
                    // console.log("AJAX request was a failure");
                }

            });
          //  }           
    }
    function changeLocations(){
        var location_type=$('#location_type').val();
        if(location_type== 0 || location_type == ""){
            $('#location_id').empty();
            var opt="";
            opt=new Option('Select Location',0);
            $('#location_id').append(opt);
             $('#location_id').selectpicker('refresh');
        }
        else{
            $.ajax({
                url: "/dashboard/getLocations", //This is the page where you will handle your SQL insert
                type: "GET",
                data: 'location_type_id=' +location_type, //The data your sending to some-page.php
                success: function(response){
                    $('#location_id').empty();
                    var opt='';
                    var select=$('#location_id');
                    opt=new Option('Select Location',0);
                    select.append(opt);
                    $.each(response,function(key,value){
                        var opt=new Option(value,key);
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


    function Export(){
        //var location_id=document.getElementById('location_id').value;
        //var product_id=document.getElementById('product_id').value;   
        //if(pre_location_id)
        //alert(pre_location_id);
       
            var url = "/dashboard/exportExcel/"+ pre_location_id +'/' + pre_product_id + '/' + pre_storage_location;
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
        if(location== 0 || location == ""){
            $('#storage_location_id').empty();
            var opt="";
            opt=new Option('Select Storage Location');
            $('#storage_location_id').append(opt);
             $('#storage_location_id').selectpicker('refresh');
        }
        else{
            $.ajax({
                url: "/dashboard/getStorageLocations", //This is the page where you will handle your SQL insert
                type: "GET",
                data: 'location_id=' +location, //The data your sending to some-page.php
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

    </script>
@stop