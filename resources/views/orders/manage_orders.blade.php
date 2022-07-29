 @extends('layouts.default')

@extends('layouts.header')

@extends('layouts.sideview')

@section('content')


<div class="box box-default color-palette-box">
            <div class="box-header with-border">
              <h3 class="box-title"><i class="fa fa-tag"></i> Placed Order</h3>
            </div>
            <div class="box-body">
            <h3><b>Your Order has been Placed with order number {{$order_number}}</b></h3>
            <br />
            Should you need any help or have a question, please feel free to reply to this email or reach 
            
            out to our customer support at-
            <br />
            Toll Free: 1-800-300-23305 
            <br />
            Email: support@esealinc.com
            <br />
            <br />
            An email has been sent to you.Thanks for placing order with us online!
            <br/>

             <a href="/orders/customerIma" class="btn btn-primary"   id="continue">
                  Continue Shopping
                </a>
   
            </div><!-- /.box-body -->
          </div>
          

@stop

@section('style')
    {{HTML::style('jqwidgets/styles/jqx.base.css')}}
     
@stop

@section('script')
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

    <!-- Include all compiled plugins (below), or include individual files as needed -->
    <script src="js/bootstrap.min.js"></script>

    <script type="text/javascript">
    $(document).ready(function () 
        {           
            var url = "grid/getOrders";
            // prepare the data
            var source =
            {
                datatype: "json",
                datafields: [
                    { name: 'name', type: 'string' },
                    { name: 'email_id', type: 'string' },
                    { name: 'status', type: 'string' },                    
                    { name: 'phone', type: 'integer' },
                    { name: 'actions', type: 'string' },
                   // { name: 'delete', type: 'string' }
                ],
                id: 'id',
                url: url,
                pager: function (pagenum, pagesize, oldpagenum) {
                    // callback called when a page or page size is changed.
                }
            };
            var dataAdapter = new $.jqx.dataAdapter(source);
            $("#jqxgrid").jqxGrid(
            {
                width: 850,
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
                  { text: 'User Name', filtercondition: 'starts_with', datafield: 'name', width: 250 },
                  { text: 'Email Id', datafield: 'email_id', width:100},
                  { text: 'Status', datafield: 'status', width: 100 },               
                  { text: 'Phone', datafield: 'phone', width:100},
                  //{ text: 'Edit', datafield: 'edit' },
                  { text: 'Actions', datafield: 'actions',width:300 }
                ]               
            });
            
            makePopupAjax($('#basicvalCodeModal form'));
            makePopupEditAjax($('#basicvalCodeModal1'));
        });         
    </script>    
@stop