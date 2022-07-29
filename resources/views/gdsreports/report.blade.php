@extends('layouts.default')

@extends('layouts.header')

@extends('layouts.sideview')

@section('content')

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

    <script type="text/javascript">

     $(document).ready(function ()

        {    
          
           // var url = '<?php print_r($gridurl); ?>'+'/'+'<?php print_r($order_status); ?>'+'/'+'<?php print_r($StartDate); ?>'+'/'+'<?php print_r($EndDate); ?>';
           var url = "<?php print_r($gridurl); ?>?order_status=<?php print_r($order_status); ?>&StartDate=<?php print_r($StartDate); ?>&EndDate=<?php print_r($EndDate); ?>&channel_id=<?php print_r($channel_id); ?>&fname=<?php print_r($fname); ?>";


// alert(url);
//alert(orders);

            var source =

            {

                datatype: "json",
                    datafields: [
               
            { name: 'channel_order_id', type: 'string' },
             { name: 'erp_order_id', type: 'string' },
           { name: 'channel_order_status', type: 'string' },
                 { name: 'order_date', type: 'string' },
     { name: 'payment_method', type: 'string' },
    { name: 'shipping_cost', type: 'string' },
       { name: 'sub_total', type: 'string' },
        { name: 'tax', type: 'string' },
      { name: 'total_amount', type: 'string' },
      { name: 'actions', type: 'string' }

      

                ],

                id: 'channel_order_id',

                url: url,

       pager: function (pagenum, pagesize, oldpagenum) {

                    // callback called when a page or page size is changed.

                }

            };
                


                // alert(source.value);   

            var dataAdapter = new $.jqx.dataAdapter(source);

            $("#jqxgrid").jqxGrid(

            {

                width: 1000,

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


               
                  
               
                  
                 { text: 'Channel Order Id',  datafield: 'channel_order_id', width: "20%" },
                  { text: 'Erp Order Id',  datafield: 'erp_order_id', width: "10%" },
          
          { text: 'Channel Order Status',  datafield: 'channel_order_status', width: "10%" },

                { text: 'Order Date',  datafield: 'order_date', width: "10%" },
        
        { text: 'Payment Method',  datafield: 'payment_method', width: "10%" },

        { text: 'shipping Cost',  datafield: 'shipping_cost', width: "5%" },
        
        { text: 'Sub Total',  datafield: 'sub_total', width: "10%" },

        { text: 'Tax',  datafield: 'tax', width: "5%" },

                
        { text: ' Total Amount',  datafield: 'total_amount', width: "10%" },
{ text: 'Actions', datafield: 'actions',width: "10%" }


                ]              

            });
           
        });

    </script>   

@stop





<div class="container">

   @if (Session::has('message'))

   <div class="flash alert">

       <p>{{ Session::get('message') }}</p>

   </div>

   @endif
 <table> 
                      <button type="submit" class="btn btn-primary" onclick = "Home()">Back</button>
                    </table>
<h3><?php print_r(strtoupper($fname))?>  <?php print_r(strtoupper($order_status));?>  ORDERS</h3>
<div class="main">

                  

<div id="jqxgrid">

  </div>


                  
</div>
          </div>





<style>
  table, th, td {
    border: 1px solid black;
    border-collapse: collapse;
  }
  th, td {
    padding: 5px;
    text-align: left;
  }
  table#Report-Data {
    width: 100%;
    background-color: #FFFFFF;    
    
  }
</style>

                      
  <script type="text/javascript">                
function Home(){
   var serve = window.location.origin;
  var url = serve+'/reportapis/index'; 
  window.location= url;
}
</script>  

@stop