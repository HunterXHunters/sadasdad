@extends('layouts.default')
@extends('layouts.header')
@extends('layouts.sideview')
@section('content')
<style>
.form-horizontal .form-group {
   margin-right: 0px !important; 
   margin-left: 0px !important; 
}

.form-horizontal .form-group {
    margin-left: -0px !important;
    margin-right: -0px !important;
}
.checkbox input[type="checkbox"], .checkbox-inline input[type="checkbox"], .radio input[type="radio"], .radio-inline input[type="radio"]
{margin-left: 0px !important;}
</style>

 <div class="box">
              <div class="box-header">
                <h3 class="box-title"><strong>Features </strong> List</h3>

                 
                 <!-- <button class="btn btn-primary pull-right" data-toggle="modal" data-target="#basicvalCodeModal"><i class="fa fa-plus-circle"></i> Add Feature</button> -->
              @if($addFeature)
              <a href="javascript:void(0)"  data-toggle="modal" class="pull-right" data-target="#basicvalCodeModal" ><i class=" fa fa-user-plus"></i> <span style="font-size:11px;">Add Feature</span></a>
              @endif 
              <a href="/rbac/featuresexport" class="btn btn-primary pull-right">Export to xls</a>
              </div>
               
               <div class="col-sm-12">
                 <div class="tile-body nopadding">                  
                    <div id="treeGrid"></div>
                </div>

              </div>


<div class="main pricemaster">  
<br />

<br/>
<br/>
<div id="treeGrid"></div>
</div>


        <!-- Modal - Popup for ADD -->
  <div class="modal fade" id="basicvalCodeModal" tabindex="-1" role="dialog" aria-labelledby="basicvalCode" aria-hidden="true">
    <div class="modal-dialog wide">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
          <h4 class="modal-title" id="basicvalCode">Add Feature</h4>
        </div>
        <div class="modal-body">        

         {{ Form::open(array('url' => 'rbac/store', 'class' => 'form-horizontal form1','id' => 'addfeature' )) }}
            {{ Form::hidden('_method','POST') }}    

      
            <!-- tile body -->   
      
    <div class="row">
      <div class="form-group col-sm-6">
         <label for="exampleInputEmail">Module Name*</label>
          <div class="input-group">
             <span class="input-group-addon addon-red"><i class="ion-ios-compose"></i></span>  
             <select name="master_lookup_id" id="master_lookup_id" class="form-control">
                      <option value="">Please choose</option>
                    @foreach($modules as $module)  
                      <option value="{{$module->module_id}}">{{$module->name}}</option>
                     @endforeach
             </select>
          </div>                        
      </div>
        <div class="form-group col-sm-6">
          <label for="exampleInputEmail">Feature Name*</label>
          <div class="input-group ">
            <span class="input-group-addon addon-red"><i class="ion-ios-list-outline"></i></span>
            <input type="text"  id="name" name="name" placeholder="Feature Name" class="form-control">
        </div>
      </div>
    </div>

    <div class="row">
      <div class="form-group col-sm-6">
        <label for="exampleInputEmail">Feature Code*</label>
        <div class="input-group ">
          <span class="input-group-addon addon-red"><i class="ion-ios-barcode-outline"></i></span>
          <input type="text" id="feature_code" name="feature_code" placeholder="feature Code" class="form-control">
        </div>
      </div>
      <div class="form-group col-sm-6">
       <label for="exampleInputEmail">Parent</label>
        <div class="input-group">
         <span class="input-group-addon addon-red"><i class="ion-ios-color-filter-outline"></i></span>  
         <select name="parent_id" id="parent_id" class="form-control">
         <option value="">Please choose</option>
          @foreach($parents as $parent) 
          @if($parent->parent_id==null) 
            <option value="{{$parent->feature_id}}">{{$parent->featurename}}</option>
          @else
          <option value="{{$parent->feature_id}}">--{{$parent->featurename}}</option>
          @endif
           @endforeach
         </select>
        </div>                        
      </div>
  </div>
    
<div class="row">
 <div class="form-group col-sm-6">
      <label for="exampleInputEmail">Description</label>
      <div class="input-group">
        <span class="input-group-addon addon-red"><i class="ion-ios-compose-outline"></i></span>
        <textarea class="form-control" id="description" value="" name="description" rows="3" ></textarea>
      </div>                        
  </div>
    <div class="form-group col-sm-6">
        <label for="exampleInputEmail">Status</label>
        <div class="input-group ">
          <div id="myproperlabel">
              <div class="checkbox">
                  <input type="checkbox" value="1"  id="opt01" id="is_active" name="is_active" parsley-group="mygroup" parsley-trigger="change" parsley-required="true" parsley-mincheck="2" parsley-error-container="#myproperlabel .last" class="parsley-validated" checked>
                <label for="opt01">Is Active</label>
              </div>
          </div>

          <div id="myproperlabel">
              <div class="checkbox">
                  <input type="checkbox" value="1"  id="opt01" id="is_menu" name="is_menu" parsley-group="mygroup" parsley-trigger="change" parsley-required="true" parsley-mincheck="2" parsley-error-container="#myproperlabel .last" class="parsley-validated" checked>
                <label for="opt01">Is Menu</label>
              </div>
          </div>
        </div>
      </div>
</div>
<div class="row">
  <div class="form-group col-sm-6">
    <label for="exampleInputEmail">Icon</label>
    <div class="input-group ">
      <span class="input-group-addon addon-red"><i class="ion-information-circled"></i></span>
      <input type="text"  id="icon" name="icon" value="" class="form-control" >
  </div>
  </div>
  <div class="form-group col-sm-6">
    <label for="exampleInputEmail">URL</label>
    <div class="input-group ">
      <span class="input-group-addon addon-red"><i class="ion-earth"></i></span>
      <input type="text"  id="url" name="url" value="" class="form-control" >
  </div>
  </div>
</div>
 <div class="row">
      <div class="form-group col-sm-6">
       <label for="exampleInputEmail">Sort Order</label>
        <div class="input-group">
         <span class="input-group-addon addon-red"><i class="ion-code"></i></span>  
         <select name="sort_order" id="sort_order" class="form-control">
         <option value="0">Please choose</option>
                <?php
                  for ($i=1; $i<=100; $i++)
                  {
                ?> 
          <option value="<?php echo $i;?>"><?php echo $i;?></option>
                  <?php
                   }
                  ?>
         </select>
        </div>                        
      </div>
  </div>  

  
           {{ Form::submit('Add', array('class' => 'btn btn-primary','id'=>'addfeaturebutton')) }}
            {{Form::close()}}
        
    </div>
      </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
  </div><!-- /.modal -->

  <div class="modal fade" id="basicvalCodeModal1" tabindex="-1" role="dialog" aria-labelledby="basicvalCode" aria-hidden="true">
    <div class="modal-dialog wide">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
          <h4 class="modal-title" id="basicvalCode">Edit Feature</h4>
        </div>
        <div class="modal-body">
        {{ Form::open(array('url' => 'update','data-url' => 'update/','id' => 'editfeature')) }}
            {{ Form::hidden('_method','PUT') }}                                             
      <div class="row">
      <div class="form-group col-sm-6">
       <label for="exampleInputEmail">Module Name*</label>
        <div class="input-group">
         <span class="input-group-addon addon-red"><i class="ion-ios-compose"></i></span>  
         <select name="master_lookup_id"s id="master_lookup_id" class="form-control" >
                @foreach($modules as $module)  
                  <option value="{{$module->module_id}}">{{$module->name}}</option>
                 @endforeach
         </select>
        </div>                        
      </div>

      <div class="form-group col-sm-6">
        <label for="exampleInputEmail">Feature Name*</label>
        <div class="input-group ">
          <span class="input-group-addon addon-red"><i class="ion-ios-list-outline"></i></span>
          <input type="text"  id="name" name="name" placeholder="Feature Name" class="form-control">
      </div>
      </div>
    </div>

    <div class="row">
      <div class="form-group col-sm-6">
        <label for="exampleInputEmail">Feature Code*</label>
        <div class="input-group ">
          <span class="input-group-addon addon-red"><i class="ion-ios-barcode-outline"></i></span>
          <input type="text" id="feature_code" name="feature_code" placeholder="feature Code" class="form-control">
        </div>
      </div>
     <div class="form-group col-sm-6">
       <label for="exampleInputEmail">Parent</label>
        <div class="input-group">
         <span class="input-group-addon addon-red"><i class="ion-ios-color-filter-outline"></i></span>  
         <select name="parent_id" id="parent_id" class="form-control">
           <option value="0">Please choose</option>
            @foreach($parents as $parent) 
            @if($parent->parent_id==null) 
            <option value="{{$parent->feature_id}}">{{$parent->featurename}}</option>
            @else
            <option value="{{$parent->feature_id}}">--{{$parent->featurename}}</option>
            @endif
            @endforeach
         </select>
        </div>                        
      </div>
    </div>
    
    <div class="row">
     <div class="form-group col-sm-6">
          <label for="exampleInputEmail">Description</label>
          <div class="input-group">
            <span class="input-group-addon addon-red"><i class="ion-ios-compose-outline"></i></span>
            <textarea class="form-control" id="description" value="" name="description" rows="3"></textarea>
          </div>                        
      </div>
        <div class="form-group col-sm-6">
            <label for="exampleInputEmail">Status</label>
            <div class="input-group ">
              <div id="myproperlabel">
                  <div class="checkbox">
                      <input type="checkbox" value="1" id="opt02" name="is_active" parsley-group="mygroup" parsley-trigger="change" parsley-required="true" parsley-mincheck="2" parsley-error-container="#myproperlabel .last" class="parsley-validated">
                    <label for="opt02">Is Active</label>
                  </div>
              </div>
              <div id="">
                  <div class="checkbox">
                      <input type="checkbox" value="1" id="opt02" name="is_menu" parsley-group="mygroup" parsley-trigger="change" parsley-required="true" parsley-mincheck="2" parsley-error-container="#myproperlabel .last" class="parsley-validated">
                    <label for="opt02">Is Menu</label>
                  </div>
              </div>
            </div>

          </div>
    </div>
    <div class="row">
        <div class="form-group col-sm-6">
        <label for="exampleInputEmail">Icon</label>
        <div class="input-group ">
          <span class="input-group-addon addon-red"><i class="ion-information-circled"></i></span>
          <input type="text"  id="icon" name="icon" value="" class="form-control">
      </div>
      </div>
      <div class="form-group col-sm-6">
        <label for="exampleInputEmail">URL</label>
        <div class="input-group ">
          <span class="input-group-addon addon-red"><i class="ion-earth"></i></span>
          <input type="text"  id="url" name="url" value="" class="form-control" >
      </div>
      </div>
    </div>
     <div class="row">
          <div class="form-group col-sm-6">
           <label for="exampleInputEmail">Sort Order</label>
            <div class="input-group">
             <span class="input-group-addon addon-red"><i class="ion-code"></i></span>  
             <select name="sort_order" id="sort_order" class="form-control">
             <option value="0">Please choose</option>
              <?php
                for ($i=1; $i<=100; $i++)
                {
              ?> 
              <option value="<?php echo $i;?>"><?php echo $i;?></option>
              <?php
               }
              ?>
             </select>
            </div>                        
          </div>
      </div>
                                          
            {{ Form::submit('Update', array('class' => 'btn btn-primary')) }}
            {{Form::close()}}
                        
            </div>
                      </div><!-- /.modal-content -->
                    </div><!-- /.modal-dialog -->
                  </div><!-- /.modal -->
<!-- /.Editmodal -->

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
  
    <script type="text/javascript">
        $(document).ready(function () 
        {
             
            ajaxCall();
            //makePopupAjax($('#basicvalCodeModal'));
            makePopupEditAjax($('#basicvalCodeModal1'), 'feature_id');
        });

function ajaxCall()
{
  $.ajax(
            {
                url: "getdata",
                success: function(result)
                {
                    var employees = result;
                    // prepare the data
                    var source =
                    {
                        datatype: "json",
                        datafields: [
                        { name: 'modulename', type: 'string' },
                        { name: 'featurename', type: 'string' },
                        { name: 'featurecode', type: 'string' },
                        { name: 'state', type: 'string' },
                        { name: 'actions', type: 'string' },
                        { name: 'children', type: 'array' },
                        { name: 'expanded', type: 'bool' }
                        ],
                        hierarchy:
                        {
                            root: 'children'
                        },
                        id: 'feature_id',
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
                          { text: 'Module Name', datafield: 'modulename', width:"20%"},                        
                          { text: 'Feature Name', datafield: 'featurename', width:"25%"},
                          { text: 'Feature Code',  datafield: 'feature_code', width: "20%" },
                          { text: 'State', datafield: 'is_active', width: "25%" },
                          { text: 'Actions', datafield: 'actions',width:"10%" }
                        ]
                    });


                }
            });
}

function deleteEntityType(feature_id)
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
                url: 'deletefeature/'+ feature_id,
                data: 'password='+userPassword,
                type:'POST',
                success: function(result)
                {
                    if(result == 1){
                        alert('Succesfully Deleted !!');
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
    function deleteParent(feature_id)
    { 
        var dec = confirm("Are you sure you want to delete parent feature along with children?");
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
                url: 'deleteParentfeature/'+ feature_id,
                data: 'password='+userPassword,
                type:'POST',
                success: function(result)
                {
                    if(result == 1){
                        alert('Succesfully Deleted !!');
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


    $('#verifyUserPassword').on('hide.bs.modal',function(){
            console.log('hide bs modal');
            $(this).find('button#cancel-btn').off('click');
            $(this).find('button#save-btn').off('click');
            $(this).find('input').val('');
        });
    function getModuleId(moduleid,parentid) {
    $('#master_lookup_id').val(moduleid);
    $('#parent_id').val(parentid);
    }
$(document).ready(function() {
    $('#addfeature').bootstrapValidator({
        //live: 'disabled',
        message: 'This value is not valid',
        feedbackIcons: {
            valid: 'glyphicon glyphicon-ok',
            invalid: 'glyphicon glyphicon-remove',
            validating: 'glyphicon glyphicon-refresh'
        },
fields: {
          
            master_lookup_id: {
                    validators: {
                        callback: {
                            message: 'Please choose Module',
                            callback: function(value, validator, $field) {
                                var options = $('[name="master_lookup_id"]').val();
                                return (options != 'Please choose');
                            }
                        },                      
                        notEmpty: {
                            message: 'Module is required.'
                        }
                    }
                },
                 name: {
                    validators: {
                        notEmpty: {
                            message: 'Feature Name is required.'
                        }
                    }
                },

               
                 feature_code: {
                    validators: {
                        notEmpty: {
                            message: 'Feature Code is required.'
                        }
                    }
                }           
        }
    }).on('success.form.bv', function(event) {
        ajaxCallPopup($('#addfeature'));
        return true;
    }).validate({
        submitHandler: function (form) {
            return false;
        }
    });

});

    $('#basicvalCodeModal').on('hide.bs.modal',function(){
    console.log('resetForm');
    $('#addfeature').data('bootstrapValidator').resetForm();
    $('#addfeature')[0].reset();   
});
    $('#basicvalCodeModal1').on('hide.bs.modal',function(){
    console.log('resetForm');
    $('#editfeature').data('bootstrapValidator').resetForm();
    $('#editfeature')[0].reset();   
});

$(document).ready(function() {
    $('#editfeature').bootstrapValidator({
        //live: 'disabled',
        message: 'This value is not valid',
        feedbackIcons: {
            valid: 'glyphicon glyphicon-ok',
            invalid: 'glyphicon glyphicon-remove',
            validating: 'glyphicon glyphicon-refresh'
        },
fields: {
          
            master_lookup_id: {
                    validators: {
                        callback: {
                            message: 'Please choose Module',
                            callback: function(value, validator, $field) {
                                var options = $('[name="master_lookup_id"]').val();
                                return (options != 'Please choose');
                            }
                        },                      
                        notEmpty: {
                            message: 'Module is required.'
                        }
                    }
                },
                 name: {
                    validators: {
                        notEmpty: {
                            message: 'Feature Name is required.'
                        }
                    }
                },

               
                 feature_code: {
                    validators: {
                        notEmpty: {
                            message: 'Feature Code is required.'
                        }
                    }
                }           
        }
    }).on('success.form.bv', function(event) {
        return false;
    });

});
</script>
@stop