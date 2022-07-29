@extends('layouts.default')
<!-- @extends('layouts.header') -->
@extends('layouts.sideview')
@section('content')
<div class="box">
  <div class="box-header">
    <h3 class="box-title"><strong>Customers</strong> List</h3>
    <?php if(isset($allow_buttons['add']) && $allow_buttons['add'] == 1) { ?>        
        <a class="pull-right" data-toggle="modal" onclick="location.href = '/customer/onboard';"><i class="fa fa-plus-circle"></i>Add Company</a>
    <?php } ?>
  </div>
  <div class="col-sm-12">
     <div class="tile-body nopadding">                  
        <div id="customer_grid"></div>
     </div>
    </div>
</div>

<?php if (isset($formData['error_message']))
{ ?>
    <div>
        <span><?php echo $formData['error_message']; ?></span>
    </div>
<?php } ?>
<!-- /breadcrumbs -->

<!-- set up the modal to start hidden and fade in and out -->
<div id="customer_approval" class="modal fade">
  <div class="modal-dialog">
    <div class="modal-content">
      <!-- dialog body -->
      <div class="modal-body">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        Do you want to approve.
      </div>
      <!-- dialog buttons -->
      <div class="modal-footer"><button type="button" class="btn btn-primary">Yes</button><button type="button" class="btn btn-danger">No</button></div>      
    </div>
  </div>
</div>
<!-- Preloader -->
<div class="mask">
  <div id="loader"></div>
</div>
<!--/Preloader --> 
@stop

@section('style')
{{HTML::style('jqwidgets/styles/jqx.base.css')}}

@stop

@section('script')
{{HTML::script('jqwidgets/jqxcore.js')}}
{{HTML::script('jqwidgets/jqxbuttons.js')}}
{{HTML::script('jqwidgets/jqxscrollbar.js')}}
{{HTML::script('jqwidgets/jqxmenu.js')}}
{{HTML::script('jqwidgets/custom_jqxgrid.js')}}
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
    $(document).ready(function ()
    {
        ajaxCall();        
    });
    
    function ajaxCall()
    {
        var url = "/customer/getcustomers";
        // prepare the data
        var source =
                {
                    datatype: "json",
                    datafields: [
                        {name: 'logo', type: 'string'},
                        {name: 'brand_name', type: 'string'},
                        {name: 'customer_type_id', type: 'string'},
                        {name: 'status', type: 'string'},
                        {name: 'website', type: 'string'},
                        {name: 'phone', type: 'string'},
                        {name: 'approved', type: 'string'},
                        {name: 'actions', type: 'string'},
                        // { name: 'delete', type: 'string' }
                    ],
                    id: 'customer_id',
                    url: url,
                    pager: function (pagenum, pagesize, oldpagenum) {
                        // callback called when a page or page size is changed.
                    }
                };
        var dataAdapter = new $.jqx.dataAdapter(source);
        $("#customer_grid").jqxGrid(
                {
                    width: "100%",
                    source: source,
                    selectionmode: 'multiplerowsextended',
                    //sortable: true,
                    pageable: true,
                    autoheight: true,
                    autoloadstate: false,
                    autosavestate: false,
                    columnsresize: true,
                    columnsreorder: true,
                    showfilterrow: true,
                    filterable: true,
                    columns: [
                        {text: 'Logo', datafield: 'logo',  width: '15%', cellsalign: 'center'},
                        {text: 'Brand Name', filtercondition: 'starts_with', datafield: 'brand_name', width: '20%'},
                        {text: 'Customer Type', datafield: 'customer_type_id', width: '10%'},
                        {text: 'Status', datafield: 'status', width: '7.5%'},
                        {text: 'Website', datafield: 'website', width: '18%'},
                        {text: 'Phone', datafield: 'phone', width: '9.5%'},
                        {text: 'Approved', datafield: 'approved', width: '10%'},
                        {text: 'Actions', datafield: 'actions', width: '10%', sortable: false, filterable: false}
                    ]
                });
                makePopupAjax($('#basicvalCodeModal form'));
                makePopupEditAjax($('#basicvalCodeModal1'));               
    }
    
    $("div .modal-footer .btn-primary").on("click", function(e) {
        //console.log("button pressed");   // just as an example...
        $('.mask, .loader').show();
        updateCustomerApproval();
        $("#customer_approval").modal('hide');     // dismiss the dialog
        $('.mask, .loader').hide();
    });
    $("div .modal-footer .btn-danger").on("click", function(e) {
        //console.log("danger button pressed");   // just as an example...
        $("#customer_approval").modal('hide');     // dismiss the dialog
    });
    //$('img[data-target="#customer_approval"]').click(alert('we are here'));

    $("#customer_approval").on("hide", function() {    // remove the event listeners when the dialog is dismissed
        $("#customer_approval a.btn").off("click");
    });    
    
    function updateCustomerApproval()
    {
        url = $('.jqx-grid-cell.jqx-fill-state-pressed').find('[data-target="#customer_approval"]').attr('data-href');
        var customer_id = url.split("/").pop();
        // Send the data using post
        var posting = $.post( url, { customer_id: customer_id } );
        // Put the results in a div
        posting.done(function( data ) {
          var result = JSON.parse(data);
          if(result['result'] == 1)
          {
              alert('Customer Approved');          
              location.reload();
          }else{
              alert(result['message']);
              return 0;
          }
        });
    }
    
    function deleteEntityType(customer_id)
        {
            var deletecustomer = confirm("Are you sure you want to Delete ?"), self = $(this);
            if ( deletecustomer == true ) {
                $.ajax({
                    data: '',
                    type: 'GET',
                    datatype: "JSON",
                    url: '/customer/deletecustomer/' + customer_id,
                    success: function (resp) {
                        if ( resp.message )
                            alert(resp.message);
                        if ( resp.status == true )
                        {
                            self.parents('td').remove();
                            location.reload();
                        }

                    },
                    error: function (error) {
                        console.log(error.responseText);
                    },
                    complete: function () {

                    }
                });
            }
        }
        function restoreEntityType(customer_id)
        {
            var restorecustomer = confirm("Are you sure you want to restore it ?"), self = $(this);
            if ( restorecustomer == true ) {
                $.ajax({
                    data: '',
                    type: 'GET',
                    datatype: "JSON",
                    url: '/customer/restorecustomer/' + customer_id,
                    success: function (resp) {
                        if ( resp.message )
                            alert(resp.message);
                        if ( resp.status == true )
                        {
                            self.parents('td').remove();
                            location.reload();
                        }

                    },
                    error: function (error) {
                        console.log(error.responseText);
                    },
                    complete: function () {

                    }
                });
            }
        }        
</script>    
@stop