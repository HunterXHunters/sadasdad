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

$(document).ready(function(){
    window.setTimeout(function(){
        $(".alert").hide();
    },3000);
});
          $(document).ready(function () 
          {
              
              ajaxCall();
        makePopupAjax($('#basicvalCodeModal'));
              makePopupEditAjax($('#basicvalCodeModal1'));
          });

  function ajaxCall()
  {
    $.ajax(
              {
                  url: "lookups/getTreeData",
                  success: function(result)
                  {
                      var employees = result;
                      // prepare the data
                      var source =
                      {
                          datatype: "json",
                          datafields: [
                          { name: 'name', type: 'string' },
                          { name: 'description', type: 'string' },
                          { name: 'mname', type: 'string' },
                          { name: 'mdescription', type: 'string' },
                          { name: 'mvalue', type: 'integer' },
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
                     { text: ' Name', datafield: 'name', width:"30%"},
                    { text: ' Description', datafield: 'description', width:"30%"},
                    { text: 'Master_name',  datafield: 'mname', width: "10%" },
                    { text: 'master_desc',  datafield: 'mdescription', width: "10%" },
                    { text: ' Master_value',  datafield: 'mvalue', width: "10%" },
                    //{ text: 'State', datafield: 'state', width: 150 },
                    { text: 'Actions', datafield: 'actions',width:"10%" }
                          ]
                      });


                  }
              });
  }


  // function deleteEntityType(id)
  //         {
  //             var decission = confirm("Are you sure you want to Delete.");
  //             if(decission==true)
  //                 window.location.href='lookups/deleteLookup/'+id;
  //         }


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
                url: 'lookups/deleteLookup/'+id,
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


   function getlookupCategoryName(el){
      var loc = $(el).closest('tr').find('td:eq(0) span:last').html();
      //console.log(loc);
      $.get('lookups/create',function(response){
          $("#basicvalCode").html('Add Lookup');
          $("#lookupsDiv").html(response);
          var name = $('#basicvalCodeModal').find('#name');
          name.find('option').each(function(i,v){
            if($(v).html()==loc){
              $(v).prop('selected',true);
            }
          });
          /*name.prop('disabled',true);*/
      });
      
    }
      
</script>

<script type="text/javascript">
$(document).ready(function(){
    $("#addLookup").click(function(){
        $.get($(this).attr('data-url'),function(response){
            $("#basicvalCode").html('Add Lookup');
            $("#lookupsDiv").html(response);
        });
    });
    
});
</script>
<script type="text/javascript">
function editLookup(id)
{
  console.log(id);
     $.get('lookups/edit/'+id,function(response){ 
            $("#basicvalCode").html('Edit Lookup');
            
            $("#lookupsDiv").html(response);
            
            $("#editLookup").click();
        });
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
                <h3 class="box-title"><strong>Master </strong>  Lookup</h3>
                @if(isset($allowed_buttons['add_lookup']) && $allowed_buttons['add_lookup'] == 1)
                <a href="javascript:void(0)" id="addLookup" data-toggle="modal" class="pull-right" data-target="#basicvalCodeModal" data-url="{{URL::asset('lookups/create')}}"><i class="fa fa-plus-circle"></i> <span>Add Lookup</span></a>
                @endif
                
                
                @if (Session::has('flash_message'))            
            <div class="alert alert-info">{{ Session::get('flash_message') }}</div>
            @endif
                 
              </div>
                     
  
   
                    <div class="col-sm-12">
                 <div class="tile-body nopadding">                  
                    <div id="treeGrid"  style="width:100% !important;"></div>
                     <button data-toggle="modal" id="editLookup" class="btn btn-default" data-target="#basicvalCodeModal" style="display: none" data-url="{{URL::asset('lookups/edit')}}"></button>
                    </div>
                </div>
   
  </div>





                    <!-- Modal -->
                    <div class="modal fade" id="basicvalCodeModal" tabindex="-1" role="dialog" aria-labelledby="basicvalCode" aria-hidden="true">
                      <div class="modal-dialog wide">
                        <div class="modal-content">
                          <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">X</button>
                            <h4 class="modal-title" id="basicvalCode">Add Lookup</h4>
                          </div>
                          <div class="modal-body">                         
                              <div class="modal-body" id="lookupsDiv">
                              </div>
                          </div>
                        </div><!-- /.modal-content -->
                      </div><!-- /.modal-dialog -->
                    </div><!-- /.modal -->


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

