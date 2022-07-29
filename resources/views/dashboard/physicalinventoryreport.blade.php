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
        .text{
            mso-number-format:"\@";
        }
        
    </style>

    <!-- /.box -->

    <div class="box">
        <div class="box-header with-border">
            <h3 class="box-title"><strong>Physical Inventory</strong>Report</h3>
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
       -->  <select class="form-control requiredDropdown" id="customer_id" name="customer_id"       parsley-trigger="change" parsley-required="true" parsley-error-container="#selectbox" onchange="" >
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


    <div class="form-group col-sm-2">

                <label for="exampleInputEmail">Location Type</label>
                <div class="input-group ">
                    <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
                    <div id="selectbox">
                        <select class="list-unstyled selectpicker" data-live-search="true" id="location_type" name="location_type"
                                parsley-trigger="change" parsley-required="true" parsley-error-container="#selectbox" onchange="changeLocations()">
                            <option  value="0">Select Location Type</option>
                            @foreach($locationTypes as $key=>$result)
                                <option  value="{{$key}}">{{$result}}</option>
                            @endforeach
                        </select>
                    </div>
                    
                </div>
            </div>
    <div class="form-group col-sm-2">
                <label for="exampleInputEmail">Location</label>
                <div class="input-group ">
                    <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
                    <div class="loader"><span class="loader"><img src="{{ asset('jqwidgets/styles/images/loader.gif') }}" class="img-responsive" /></span></div>
                    <div  id="selectdiv">
                        <select id="location_id" name="location_id[]" onchange ="changeProducts(); enablefilter();"></select>
                    </div>
                </div>
    </div>


    <div class="form-group col-sm-2">
                <label for="exampleInputEmail">Product</label>
                <div class="input-group ">
                    <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
                    <div id="productselect">
                        <select id="product_id" name="product_id[]"  multiple="multiple" ></select>
                    </div>
                </div>
    </div>
    <div class="form-group col-sm-2">
                <label for="exampleInputEmail">From</label>
                <div class="input-group ">
                    <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
                    <div id="">
                        <input type="text" class="datepicker" name="from_date" id="from_date"  placeholder="from date">
                     </div>
                 </div>
             </div>



            <div class="form-group col-sm-2">
                <label for="exampleInputEmail">To</label>
                <div class="input-group ">
                    <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
                    <div id="">
                        <input type="text" class="datepicker" name="to_date" id="to_date" placeholder="to date">
                    </div>
                </div>
            </div>


    <!-- <div class="form-group col-sm-2">
                
                <input type="checkbox" name ="invalids"  id ="invalids">    
                <span>Show Invalids</span>
                
    </div> -->
    <div class="form-group col-sm-2">
                <label for="exampleInputEmail"></label>
                <div class="input-group ">
                    <div id="button">
                        <button class="btn btn-primary" data-toggle="modal"  id ="filter" onclick="makeGrid();">Filter</button>
                        
                    </div>
                    
                </div>
    </div>

    <div>
    <div calss="form-group col-sm-12">
    <div class='col-sm-12'>
                    <div  class='col-sm-2'>

                        <input type="checkbox" name ="invalids"  id ="invalids">    
                        <span>Show Invalids</span>
                    </div>
                    <div class='form-group col-sm-2'>
                        <input type="checkbox" name ="secondaries"  id ="secondaries">    
                        <span>Show Secondaries</span>
                    </div>
    </div>
     </div>
     </div>
     


    

    </div><!-- /.box-body -->
    <!-- <div class="export col-sm-2 col-sm-offset-10">
        <button class="btn btn-primary" id='export_excel' data-toggle="modal"  onclick="Export()">Export</button>
        </div> -->

        <div class="col-sm-12">
            <div class="tile-body nopadding">
            <input type="button" id="btnExport" value="Export" class="pull-right">
                <div id="report">
                    <!-- <table class="table table-bordered table-striped "cellspacing="0" id="reporttable">
                    <caption><center><b>Physical Inventory in ALL Locations</b></center></caption>
                        <thead>
                            <tr>
                            <td>Location</td>
                            <td>Inventory</td>
                                <td>Material code</td>
                                <td>Batch No</td>
                                <td>Level </td>
                                <td> ERP Code</td>
                                <td>Count</td>
                            </tr>
                        </thead>
                        <tbody>
                           
                        </tbody>
                    </table> -->
                </div>
            </div>
        </div>
</div>
<div class="hide" id="dvData">

</div>


<link href="http://cdn.rawgit.com/davidstutz/bootstrap-multiselect/master/dist/css/bootstrap-multiselect.css"
    rel="stylesheet" type="text/css" />
<script src="http://cdn.rawgit.com/davidstutz/bootstrap-multiselect/master/dist/js/bootstrap-multiselect.js"
    type="text/javascript"></script>
<link rel="styleheet" href="https://cdn.datatables.net/1.10.13/css/jquery.dataTables.min.css"/>

<script type="text/javascript" src="https://cdn.datatables.net/1.10.13/js/jquery.dataTables.min.js"></script>
@stop

@section('style')
    {{HTML::style('jqwidgets/styles/jqx.base.css')}}
    {{HTML::style('css/bootstrap-select.css')}}
    {{HTML::style('css/datepicker.min.css')}}
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
    {{HTML::script('jqwidgets/jqxdata.export.js')}}
    {{HTML::script('jqwidgets/jqxgrid.export.js')}}


    <script type="text/javascript">
    var pre_location ="";
    var pre_location_type = 0;
    var pre_product ="";
    var pre_invalid = "false";
    var pre_secondaries = "false";
    var pre_from_date = "";
    var pre_to_date = "";
    $(document).ready(function(){
        $('#btnExport').prop('disabled',true);
        $('#filter').prop('disabled',true);
    $('#from_date').datepicker({
        format:'yyyy-mm-dd'
    });
    $('#to_date').datepicker({
        format:'yyyy-mm-dd'
    });
    $('.loader').hide();

        // $('#reporttable').DataTable({
        //     "bFilter" : false,               
        //     "bLengthChange": false
        //     //"paging":   false,
        // //"ordering": false,
        // //"info":     false
        // //"dom": '<"top"i>rt<"bottom"flp><"clear">'
        // });

        //makeGrid();

        $("#report").jqxGrid(
                    {
                        width: "100%",
                        source: {
                        datatype: "json",
                        datafields: [
                            
                            { name: 'erp_code', type: 'string' },
                            { name:'username', type: 'string'},
                            { name: 'material_code', type: 'string' },
                            { name: 'name', type: 'string' },
                            { name: 'batch_no', type: 'string' },
                            { name: 'primary_id', type: 'integer' },
                            { name: 'level', type: 'integer' },
                            { name: 'parent_id', type: 'integer' },
                            { name: 'qty', type: 'integer' },
                            {name:'physical_location',type:'string'},
                            {name:'eseal_location',type:'string'},
                            { name: 'phydate', type: 'date' },
                            { name: 'Remarks', type: 'string' }
                            
                        ],
                        id: 'id',
                        //url: url,
                        // data:{
                        //     location_type:loc_type,
                        //     location:location_id,
                        //     product:product_id,
                        //     invalids: invalids,
                        //     from_date:from_date,
                        //     to_date:to_date
                        // },
                        pager: function (pagenum, pagesize, oldpagenum) {
                            // callback called when a page or page size is changed.
                        }
                    },
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
                                
                            { text: 'Location Code', datafield:'erp_code' },
                            { text: 'Login ID', datafield:'username'},
                            { text: 'Material Code', datafield:'material_code'},
                            {text:'Product Name',datafield:'name'},
                            {text:'Batch Number',datafield:'batch_no'},
                            {text:'Primary ID',datafield:'primary_id'},
                            {text:'Level',datafield:'level'},
                            {text:'Parent ID',datafield:'parent_id'},
                            {text:'Qty',datafield:'qty'},
                            {text:'Physical Location',datafield:'physical_location'},
                            {text:'Eseal Location',datafield:'eseal_location'},
                            {text:'Physical Inv Date',datafield:'phydate'},
                            {text:'Remarks',datafield:'Remarks'}

                        ]
                        
                    });


    });



    //-------------------

    makeGrid = function (){
                //var product_id=$('[name="location_id"]').val())
                //var location_id=document.getElementById('location_id').value;

                var location_id = $('#location_id').val();
                //var product_id=document.getElementById('product_id').value;
                var product_id = $('#product_id').val();
                if(product_id == null){
                    product_id =new Array();
                }
                if(location_id == null){
                    location_id = new Array();
                }
               // var storage_location = document.getElementById('storage_location_id').value;
                var cust = $('#customer_id').val();
                var from_date=$('#from_date').val();//document.getElementById('from_date').value;
                var to_date=$('#to_date').val();//document.getElementById('to_date').value;
                //var prod_grp = $('#product_group').val();
                var loc_type = $('#location_type').val();
                var invalids = $('#invalids').is(':checked');
                var secondaries = $('#secondaries').is(':checked');
                //var cat =  $('#category').val();
                pre_product = product_id;
                pre_location = location_id;
                //pre_storage_location = storage_location;
                //pre_product_group = prod_grp;
                pre_location_type = loc_type;
                pre_from_date = from_date;
                pre_to_date = to_date;
                pre_invalid = invalids;
                pre_secondaries = secondaries;
                //pre_category = cat;
                //alert(location_id);
                //alert(product_id);
                if(loc_type =="" || typeof(loc_type) == 'undefined'){
                    loc_type = 0; 
                }
                if(cust == "" || typeof(cust) == 'undefined'){
                    cust = 0;
                }
                pre_cust_id = cust;
                if(product_id==''){
                     product_id=new Array();
                }
                if(location_id==''){
                     location_id= new Array();
                }
                if(from_date == '' || from_date == null){
                    from_date =0;
                    pre_from_date = 0;
                }
                if(to_date == '' || to_date == null){
                    to_date =0;
                    pre_to_date = 0;
                }
                // if(storage_location == '' || storage_location == null){
                //     storage_location = '';
                // }
                //$('#export_excel').show();
                var url = "/dashboard/getphysicalinventorydatawithfilter";

                // alert(url);
                
                var source =
                    {
                        datatype: "json",
                        datafields: [
                            
                            { name: 'erp_code', type: 'string' },
                            { name:'username', type: 'string'},
                            { name: 'material_code', type: 'string' },
                            { name: 'name', type: 'string' },
                            { name: 'batch_no', type: 'string' },
                            { name: 'primary_id', type: 'integer' },
                            { name: 'level', type: 'integer' },
                            { name: 'parent_id', type: 'integer' },
                            { name: 'qty', type: 'integer' },
                            {name:'physical_location',type:'string'},
                            {name:'eseal_location',type:'string'},
                            { name: 'phydate', type: 'date' },
                            { name: 'Remarks', type: 'string' }
                            
                        ],
                        id: 'id',
                        url: url,
                        data:{
                            location_type:loc_type,
                            //location:location_id,
                            //product:product_id,
                            invalids: invalids,
                            secondaries: secondaries,
                            from_date:from_date,
                            to_date:to_date,
                            cust_id: pre_cust_id
                        },
                        pager: function (pagenum, pagesize, oldpagenum) {
                            // callback called when a page or page size is changed.
                        }
                    };
                
                var dataAdapter = new $.jqx.dataAdapter(source);
                createGrid(dataAdapter);
                $('#btnExport').prop('disabled',false);
                
            }

            var columns = 
            [
                                
                            { text: 'Location Code', datafield: 'erp_code', width: "30%" },
                            { text: 'Login ID', datafield: 'username', width: "20%"},
                            { text: 'Material Code', datafield: 'material_code', width:"10%"},
                            {text:'Product Name',datafield: 'name',width:"10%"},
                            {text:'Batch Number',datafield: 'batch_no',width:"10%"},
                            {text:'Primary ID',datafield: 'primary_id',width:"10%"},
                            {text:'Level',datafield: 'level',width:"10%"},
                            {text:'Parent ID',datafield: 'parent_id',width:"10%"},
                            {text:'Qty',datafield: 'qty',width:"10%"},
                            {text:'Physical Location',datafield:'physical_location'},
                            {text:'Eseal Location',datafield:'eseal_location'},
                            {text:'Physical Inv Date',datafield: 'phydate',width:"10%"},
                            {text:'Remarks',datafield: 'Remarks',width:"10%"}

                        ];


            function createGrid(source){
                $("#report").jqxGrid(
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
                                
                            { text: 'Location Code', datafield:'erp_code' },
                            { text: 'Login ID', datafield:'username'},
                            { text: 'Material Code', datafield:'material_code'},
                            {text:'Product Name',datafield:'name'},
                            {text:'Batch Number',datafield:'batch_no'},
                            {text:'Primary ID',datafield:'primary_id'},
                            {text:'Level',datafield:'level'},
                            {text:'Parent ID',datafield:'parent_id'},
                            {text:'Qty',datafield:'qty'},
                            {text:'Physical Location',datafield:'physical_location'},
                            {text:'Eseal Location',datafield:'eseal_location'},
                            {text:'Physical Inv Date',datafield:'phydate'},
                            {text:'Remarks',datafield:'Remarks'}

                        ]
                        
                    });

                
            
            }
    //---------------------
    // function makeGrid(){
    //     var location = $('#location_id').val();
    //     var invalid = $('#invalids').is(':checked');
    //     var product = $('#product_id').val();
    //     prelocation = location;
    //     preinvalid = invalid;
    //     preproduct = product;
    //     //alert(invalid);
    //     if(typeof(product)=='undefined' || product ==""){
    //         product =0;
    //     }
    //     if((typeof(location_id) !='undefined')){


    //         $.ajax({
    //             url:'getphysicalinventorydatawithfilter',
    //             method:'post',
    //             data:{
    //                 location:location,
    //                 invalids:invalid,
    //                 product: product
    //             },
    //             success:function(response){
    //                 //alert('success');

    //                 if(location ==0){
    //                     //$('#btnExport').hide();
    //                 }
    //                 else{
    //                     $('#btnExport').show();
    //                 }
    //                 $('#report').html(response['html']);
    //                 $('#dvData').html(response['iotshtml'])
    //                 $('#reporttable').DataTable({
    //                     "bFilter" : false,               
    //                     "bLengthChange": false
    //                     //"paging":   false,
    //                     //"ordering": false,
    //                     //"info":     false
    //                     //"dom": '<"top"i>rt<"bottom"flp><"clear">'
    //                     });
    //             }
    //         });
    //     }
    // }
$("#invalids").change(function() {
    if(this.checked) {
    //    alert("checked");
    //$('#productselect').fade();
    //$('#product_id').prop('disabled',true);
    }
    else{
        //alert("unchecked");
        //$('#productselect').fadeout();
      //  $('#product_id').prop('disabled',false);
    }
});


$("#btnExport").click(function () {


    var url = "/dashboard/physicalinventoryexport?location_type="+pre_location_type+"&location="+pre_location+"&invalids="+pre_invalid+"&product="+pre_product+"&from_date="+pre_from_date+"&to_date="+pre_to_date+"&secondaries="+secondaries;
             window.location = url;
    // $.ajax({
    //     url:"physicalinventoryexport",
    //     data:{
    //         location_type:pre_location_type,
    //         location:pre_location,
    //         invalids:pre_invalid,
    //         product:pre_product,
    //         from_date:pre_from_date,
    //         to_date:pre_to_date
    //     },
    //     method:'post',
    //     success:function(response){

    //     }
    // });
    
});


function changeLocations(){
    $('#filter').prop('disabled',true);
    $('.loader').show();
    $('#selectdiv').hide();
        var location_type=$('#location_type').val();
        var cust = $('#customer_id').val();
        $('#location_id').multiselect('destroy');
        $('#product_id').multiselect('destroy'); 
         $('#product_id').empty();
        $('#product_id').multiselect({
                nonSelectedText :'Select Products',
               includeSelectAllOption: true,
                       enableFiltering:true,
                        numberDisplayed: 0,
                 enableCaseInsensitiveFiltering: true,
                 maxHeight: 300
             });
             
        if(location_type== 0 || location_type == ""){
            $('#location_id').empty();
            var opt="";
            //opt=new Option('Select Location',0);
            //$('#location_id').append(opt);
             //$('#location_id').selectpicker('refresh');
             //$('#location_id').multiselect('refresh');
             $('#location_id').multiselect({
                nonSelectedText :'Select Locations',
               includeSelectAllOption: true,
                       enableFiltering:true,
                        numberDisplayed: 0,
                 enableCaseInsensitiveFiltering: true,
                 maxHeight: 300
             });
        }
        else{
            $.ajax({
                url: "/dashboard/getLocations", //This is the page where you will handle your SQL insert
                type: "POST",
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
                    //$('#location_id').multiselect('refresh');
                    $('#location_id').multiselect({
                nonSelectedText :'Select Locations',
               includeSelectAllOption: true,
                       enableFiltering:true,
                        numberDisplayed: 0,
                        enableCaseInsensitiveFiltering: true,
                        maxHeight: 300
             });

                    
                },
                error:function(){
                    // console.log("AJAX request was a failure");
                },
                complete:function(){
                    $('.loader').hide();
                    $('#selectdiv').show();
                }
            });
        }

    }
    
    function changeProducts(){
    $('#filter').prop('disabled',false);
    $('.loader').show();
    $('#selectdiv').hide();
        var location_id=$('#location_id').val();
        var cust = $('#customer_id').val();
        $('#product_id').multiselect('destroy');
        if(location_id== 0 || location_id == ""){
            $('#product_id').empty();
            var opt="";
            //opt=new Option('Select Location',0);
            //$('#location_id').append(opt);
             //$('#location_id').selectpicker('refresh');
             //$('#location_id').multiselect('refresh');
             $('#product_id').multiselect({
                nonSelectedText :'Select Products',
               includeSelectAllOption: true,
                       enableFiltering:true,
                        numberDisplayed: 0,
                 enableCaseInsensitiveFiltering: true,
                 maxHeight: 300
             });
        }
        else{
            $.ajax({
                url: "/dashboard/getProducts", //This is the page where you will handle your SQL insert
                type: "POST",
                data: 'location_id=' +location_id+'&customer_id='+cust, //The data your sending to some-page.php
                success: function(response){  
                    $('#product_id').multiselect('destroy');
                    $('#product_id').empty();
                    var opt='';
                    var select=$('#product_id');
                    //opt=new Option('Select Location',0);
                    //select.append(opt);
                    $.each(response,function(key,value){
                        var opt=new Option(value,key);  
                        select.append(opt);
                    });
                    //select.selectpicker('refresh');
                    //$('#location_id').multiselect('refresh');
                    $('#product_id').multiselect({
                nonSelectedText :'Select Products',
               includeSelectAllOption: true,
                       enableFiltering:true,
                        numberDisplayed: 0,
                        enableCaseInsensitiveFiltering: true,
                        maxHeight: 300
             });

                    
                },
                error:function(){
                    // console.log("AJAX request was a failure");
                },
                complete:function(){
                    $('.loader').hide();
                    $('#selectdiv').show();
                }
            });
        }

    }

    $('#product_id').multiselect({
    //columns: 1,
    nonSelectedText :'Select Products',
   includeSelectAllOption: true,
           enableFiltering:true,
            numberDisplayed: 0,
        enableCaseInsensitiveFiltering: true,
        maxHeight: 300
});
    $('#location_id').multiselect({
    //columns: 1,
    nonSelectedText :'Select Locations',
   includeSelectAllOption: true,
           enableFiltering:true,
            numberDisplayed: 0,
        enableCaseInsensitiveFiltering: true,
        maxHeight: 300
});

    function enablefilter(){

        var loc =$('#location_id').val();
        //alert(loc);
        if(loc == null){
            $('#filter').prop('disabled',true);
        }
        else{
            $('#filter').prop('disabled',false);   
        }
    }

    </script>
@stop