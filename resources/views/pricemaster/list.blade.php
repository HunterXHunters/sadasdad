@extends('layouts.default')
@extends('layouts.header')
@extends('layouts.sideview')
@section('content')

<div class="box">
    <div class="box-header">
      <h3 class="box-title">Manage <strong>Eseal PriceMaster</strong></h3>
      @if (Session::has('flash_message'))            
            <div class="alert alert-info">{{ Session::get('flash_message') }}</div>
            @endif
       @if(isset($allowed_buttons['add_price']) && $allowed_buttons['add_price'] == 1)
      <a href="pricemaster/add" class="pull-right"><i class="fa fa-plus-circle"></i>Add Price</a>
       @endif
    </div>
     
    <div class="col-sm-12">
       <div class="tile-body nopadding">                  
          <div id="treeGrid"></div> 
       </div>
    </div>
</div> 

<!-- Modal - Popup for Verify User Password while deleting -->
    <div class="modal fade" id="verifyUserPassword" tabindex="-1" role="dialog" aria-labelledby="basicvalCode" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                    <h4 class="modal-title" id="basicvalCode">Enter Password</h4>
                </div>
                <div class="modal-body">
                    <div class="">
                        <div class="form-group col-sm-12">
                            <label class="col-sm-2 control-label" for="BusinessType">Password*</label>
                            <div class="col-sm-10">
                                <div class="input-group input-group-sm">
                                    <span class="input-group-addon addon-red"><i class="fa fa-flag-checkered"></i></span>
                                    <input type="password" id="verifypassword" name="passwordverify" class="form-control">      
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal" id="cancel-btn">Cancel</button>
                    <button type="button" id="save-btn" class="btn btn-success">Submit</button>
                </div>                
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->
      
    


@stop

@section('style')
    {{HTML::style('jqwidgets/styles/jqx.base.css')}}
     
@stop

@section('script')
    
    {{HTML::script('jqwidgets/jqxcore.js')}}
    {{HTML::script('jqwidgets/jqxdata.js')}}
    {{HTML::script('jqwidgets/jqxbuttons.js')}}
    {{HTML::script('jqwidgets/jqxscrollbar.js')}}
    {{HTML::script('jqwidgets/jqxdatatable.js')}}
    {{HTML::script('jqwidgets/jqxtreegrid.js')}}
    {{HTML::script('scripts/demos.js')}}
  <script src="js/bootstrap.min.js"></script>
    <script type="text/javascript">
    $(document).ready(function(){
    window.setTimeout(function(){
        $(".alert").hide();
    },3000);
});
        $(document).ready(function () 
        {
                               // prepare the data
                    var url= "pricemaster/show";
                    var source =
                    {
                        datatype: "json",
                        datafields: [
                        { name: 'component', type: 'string' },
                        { name: 'price', type: 'string' },
                        { name: 'actions', type: 'string' },
                        { name: 'children', type: 'array' },
                        { name: 'expanded', type: 'bool' }
                        ],
                        hierarchy:
                        {
                            root: 'children'
                        },
                        id: 'id',
                        url: url,
                        pager: function (pagenum, pagesize, oldpagenum) {
                    // callback called when a page or page size is changed.
                }
                    };
                    var dataAdapter = new $.jqx.dataAdapter(source);
                    // create Tree Grid
                    $("#treeGrid").jqxTreeGrid(
                    {
                        width: "100%",
                        source: dataAdapter,
                        sortable: true,
                        //filterable: true,
                        //autoheight: true,
                        //autowidth: true,
                        columns: [
                          { text: 'Component', datafield: 'component', width: "60%" },
                          { text: 'Price', datafield: 'price', width: "30%" },
                          { text: 'Actions', datafield: 'actions',width:"10%" }
                        ]
                    });

            });

// function deleteEntityType(id)
//     {
//       var dec = confirm("Are you sure you want to Delete ?");
//       if(dec == true)
//           window.location.href = 'pricemaster/delete/'+id;
//     }


function deleteEntityType(id)
    { 
        var dec = confirm("Are you sure you want to Delete ?");
        if ( dec == true )
        $('#verifyUserPassword').modal('show');
        $('#verifyUserPassword button#cancel-btn').on('click',function(e){
            e.preventDefault();
            //console.log('clicked cancel');
            $('#verifyUserPassword').modal('hide');
        });
        $('#verifyUserPassword button#save-btn').on('click',function(e){
            e.preventDefault();
            //console.log('cliked submit');
            var userPassword = $.trim($('#verifyUserPassword input').val());
            if(userPassword == ''){
                alert('Field is required');
                return false
            } else
            $.ajax({
                url: 'pricemaster/delete/'+id,
                data: 'password='+userPassword,
                type:'POST',
                success: function(result)
                {
                    if(result == 1){
                        alert('Successfully Deleted !!');
                        location.reload();
                        //window.location.href = '/customer/editcustomer/'+manufacturerId;
                        $('#verifyUserPassword').modal('hide');
                    }else{
                        alert(result);
                    }
                },
                error: function(err){
                    console.log('Error: '+err);
                },
                complete: function(data){
                    console.log(data);
                }
            });
        });
    }




</script>

              
@stop