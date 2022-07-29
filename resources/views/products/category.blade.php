@extends('layouts.default')
@extends('layouts.header')
@extends('layouts.sideview')
@section('content')
<div class="box">
              <div class="box-header">
                <h3 class="box-title"><strong>Category </strong> List</h3>  
             @if(isset($allowed_buttons['add_new_parent_category']) && $allowed_buttons['add_new_parent_category'] == 1)
        <a href="javascriot:void(0)"  data-toggle="modal" id="addUser" class="pull-right" data-target="#basicvalCodeModalAddParent"><i class="fa fa-user-plus"></i> <span style="font-size:11px;">Add New Parent Category</span></a>
    @endif
    @if(isset($allowed_buttons['add_category']) && $allowed_buttons['add_category'] == 1)
        <a href="javascriot:void(0)"  data-toggle="modal" id="addUser" class="pull-right" data-target="#add_categories"><i class="fa fa-user"></i> <span style="font-size:11px;">Assign Categories</span></a>
    @endif
    <br />

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


    <!-- Modal - Popup for ADD Parent Category -->
    <div class="modal fade" id="addCategory" tabindex="-1" role="dialog" aria-labelledby="basicvalCode" aria-hidden="true">
        <div class="modal-dialog wide">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                    <h4 class="modal-title" id="basicvalCode">Add Category</h4>
                </div>
                <div class="modal-body">
    
                    {{ Form::open(array('url' => 'product/savecategory', 'class' => 'form-horizontal form1','id'=>'add_category_validation' )) }}
                    {{ Form::hidden('_method','POST') }}

                    <div class="row">
                        <div class="form-group col-sm-6">
                            <label class="col-sm-2 control-label" for="Name">Name</label>
                            <div class="col-sm-10">
                            <div class="input-group ">
                                <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
                                <input type="text"  id="name" name="name" placeholder="name" class="form-control">
                            </div>
                            </div>
                        </div>
                        <div class="form-group col-sm-6">
                            <label class="col-sm-2 control-label" for="exampleInputEmail">Parent</label>
                            <div class="col-sm-10">
                                <div class="input-group ">
                                    <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
                                    <select class="chosen-select form-control parsley-validated" name="parent_id" id="addCategory_parent_id">
                                        <?php 
                                        foreach($categoryList as $category){
                                            foreach($category as $childCategory){                                            
                                                if(property_exists($childCategory, 'childs'))
                                                {
                                                    ?>
                                        <option value="<?php echo $childCategory->category_id; ?>" style="padding-left: 0px;"><?php echo $childCategory->name; ?></option>                                                        
                                                    <?php
                                                    foreach($childCategory->childs as $childChildCategory)
                                                    {
                                                        if(property_exists($childChildCategory, 'childs'))
                                                        {
                                                            ?>
                                        <option value="<?php echo $childChildCategory->category_id; ?>" style="padding-left: 25px;"><?php echo $childChildCategory->name; ?></option>
                                                            <?php
                                                            foreach($childChildCategory->childs as $childChildChildCategory)
                                                            { ?>
                                        <option value="<?php echo $childChildChildCategory->category_id; ?>" style="padding-left: 50px;"><?php echo $childChildChildCategory->name; ?></option>                                                                
                                                            <?php }
                                                        }else{ ?>
                                        <option value="<?php echo $childChildCategory->category_id; ?>" style="padding-left: 25px;"><?php echo $childChildCategory->name; ?></option>
                                                            <?php }
                                                    }
                                                }else{ ?>
                                        <option value="<?php echo $childCategory->category_id; ?>" style="padding-left: 10px;"><?php echo $childCategory->name; ?></option>                                                    
                                                <?php }
                                            }
                                        }                                    
                                        ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        </div>
                    <div class="row">
                        <div class="form-group col-sm-6">
                            <label class="col-sm-2 control-label" for="exampleInputEmail">Sort Order</label>
                            <div class="col-sm-10">
                            <div class="input-group ">
                                <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
                                <input type="text" id="sort_order" name="sort_order" placeholder="sort_order" class="form-control">
                            </div>
                            </div>
                        </div>
                        <div class="form-group col-sm-6">
                            <label class="col-sm-2 control-label" for="exampleInputEmail">Status</label>
                            <div class="col-sm-10">
                            <div class="input-group">
                                <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
                                <select name="status" required class="form-control">
                                    <option  value="1">Active</option>
                                    <option  value="0">In-Active</option>
                                </select>
                                </div>
                            </div>                        
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col-sm-6">
                            <label class="col-sm-2 control-label" for="exampleInputEmail">Top</label>
                            <div class="col-sm-10">
                            <div class="input-group ">
                                <span class="input-group-addon addon-red">
                                    <i class="fa fa-user"></i></span>
                                <input type="text" id="top" name="top" placeholder="top" class="form-control">
                            </div>
                            </div>
                        </div>
                        <div class="form-group col-sm-6">
                            <label class="col-sm-2 control-label" for="exampleInputEmail">Column</label>
                            <div class="col-sm-10">
                            <div class="input-group">
                                <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
                                <input type="text" id="column" name="column" placeholder="column" class="form-control">
                            </div>
                            </div>                        
                        </div>
                    </div>

                    {{ Form::submit('Add', array('class' => 'btn btn-primary')) }}
                    {{Form::close()}}

                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->
    
    
    <!-- Modal - Popup for Edit Parent Category -->
    <div class="modal fade" id="editCategory" tabindex="-1" role="dialog" aria-labelledby="basicvalCode" aria-hidden="true">
        <div class="modal-dialog wide">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                    <h4 class="modal-title" id="basicvalCode">Edit Category</h4>
                </div>
                <div class="modal-body">
    
                    {{ Form::open(array('url' => 'product/updatecategory','data-url' => 'product/updatecategory/', 'class' => 'form-horizontal form1','id'=>'update_category_validation' )) }}
                    {{ Form::hidden('_method','PUT') }}

                    <div class="row">
                        <div class="form-group col-sm-6">
                            <label class="col-sm-2 control-label" for="Name">Name</label>
                            <div class="col-sm-10">
                            <div class="input-group ">
                                <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
                                <input type="text"  id="name" name="name" placeholder="name" class="form-control">
                            </div>
                            </div>
                        </div>
                        <div class="form-group col-sm-6">
                            <label class="col-sm-2 control-label" for="exampleInputEmail">Parent</label>
                            <div class="col-sm-10">
                                <div class="input-group ">
                                    <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
                                    <select class="chosen-select form-control parsley-validated" name="parent_id" id="addCategory_parent_id">
                                        <?php 
                                        foreach($categoryList as $category){
                                            foreach($category as $childCategory){                                            
                                                if(property_exists($childCategory, 'childs'))
                                                {
                                                    ?>
                                        <option value="<?php echo $childCategory->category_id; ?>" style="padding-left: 0px;"><?php echo $childCategory->name; ?></option>                                                        
                                                    <?php
                                                    foreach($childCategory->childs as $childChildCategory)
                                                    {
                                                        if(property_exists($childChildCategory, 'childs'))
                                                        {
                                                            ?>
                                        <option value="<?php echo $childChildCategory->category_id; ?>" style="padding-left: 25px;"><?php echo $childChildCategory->name; ?></option>
                                                            <?php
                                                            foreach($childChildCategory->childs as $childChildChildCategory)
                                                            { ?>
                                        <option value="<?php echo $childChildChildCategory->category_id; ?>" style="padding-left: 50px;"><?php echo $childChildChildCategory->name; ?></option>                                                                
                                                            <?php }
                                                        }else{ ?>
                                        <option value="<?php echo $childChildCategory->category_id; ?>" style="padding-left: 25px;"><?php echo $childChildCategory->name; ?></option>
                                                            <?php }
                                                    }
                                                }else{ ?>
                                        <option value="<?php echo $childCategory->category_id; ?>" style="padding-left: 10px;"><?php echo $childCategory->name; ?></option>                                                    
                                                <?php }
                                            }
                                        }                                    
                                        ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        </div>
                    <div class="row">
                        <div class="form-group col-sm-6">
                            <label class="col-sm-2 control-label" for="exampleInputEmail">Sort Order</label>
                            <div class="col-sm-10">
                            <div class="input-group ">
                                <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
                                <input type="text" id="sort_order" name="sort_order" placeholder="sort_order" class="form-control">
                            </div>
                            </div>
                        </div>
                        <div class="form-group col-sm-6">
                            <label class="col-sm-2 control-label" for="exampleInputEmail">Status</label>
                            <div class="col-sm-10">
                            <div class="input-group">
                                <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
                                <select name="status" required class="form-control">
                                    <option  value="1">Active</option>
                                    <option  value="0">In-Active</option>
                                </select>
                                </div>
                            </div>                        
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col-sm-6">
                            <label class="col-sm-2 control-label" for="exampleInputEmail">Top</label>
                            <div class="col-sm-10">
                            <div class="input-group ">
                                <span class="input-group-addon addon-red">
                                    <i class="fa fa-user"></i></span>
                                <input type="text" id="top" name="top" placeholder="top" class="form-control">
                            </div>
                            </div>
                        </div>
                        <div class="form-group col-sm-6">
                            <label class="col-sm-2 control-label" for="exampleInputEmail">Column</label>
                            <div class="col-sm-10">
                            <div class="input-group">
                                <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
                                <input type="text" id="column" name="column" placeholder="column" class="form-control">
                            </div>
                            </div>                        
                        </div>
                    </div>

                    {{ Form::submit('Update', array('class' => 'btn btn-primary')) }}
                    {{Form::close()}}

                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->
    
    <!-- Modal - Popup for ADD Parent Category -->
    <div class="modal fade" id="basicvalCodeModalAddParent" tabindex="-1" role="dialog" aria-labelledby="basicvalCode" aria-hidden="true">
        <div class="modal-dialog wide">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                    <h4 class="modal-title" id="basicvalCode">Add Parent Category</h4>
                </div>
                <div class="modal-body">
    
                    {{ Form::open(array('url' => 'product/savecategory', 'class' => 'form-horizontal form1','id'=>'add_parent_validation' )) }}
                    {{ Form::hidden('_method','POST') }}

                    <div class="row">
                        <div class="form-group col-sm-6">
                            <label class="col-sm-2 control-label" for="exampleInputEmail">Name</label>
                            <div class="col-sm-10">
                            <div class="input-group ">
                                <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
                                <input type="text"  id="name" name="name" placeholder="name" class="form-control">
                            </div>
                            </div>
                        </div>
                        <div class="form-group col-sm-6">
                            <label class="col-sm-2 control-label" for="exampleInputEmail">Parent</label>
                            <div class="col-sm-10">
                            <div class="input-group ">
                                <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
                                <input type="text"  id="parent_id" name="parent_id" placeholder="parent_id" class="form-control" value="0" readonly >
                                </div>
                            </div>
                        </div>
                        </div>
                    <div class="row">
                        <div class="form-group col-sm-6">
                            <label class="col-sm-2 control-label" for="exampleInputEmail">Sort Order</label>
                            <div class="col-sm-10">
                            <div class="input-group ">
                                <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
                                <input type="text" id="sort_order" name="sort_order" placeholder="sort_order" class="form-control">
                            </div>
                            </div>
                        </div>
                        <div class="form-group col-sm-6">
                            <label class="col-sm-2 control-label" for="exampleInputEmail">Status</label>
                            <div class="col-sm-10">
                            <div class="input-group">
                                <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
                                <select name="status" required class="form-control">
                                    <option  value="1">Active</option>
                                    <option  value="0">In-Active</option>
                                </select>
                                </div>
                            </div>                        
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col-sm-6">
                            <label class="col-sm-2 control-label" for="exampleInputEmail">Top</label>
                            <div class="col-sm-10">
                            <div class="input-group ">
                                <span class="input-group-addon addon-red">
                                    <i class="fa fa-user"></i></span>
                                <input type="text" id="top" name="top" placeholder="top" class="form-control">
                            </div>
                            </div>
                        </div>
                        <div class="form-group col-sm-6">
                            <label class="col-sm-2 control-label" for="exampleInputEmail">Column</label>
                            <div class="col-sm-10">
                            <div class="input-group">
                                <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
                                <input type="text" id="column" name="column" placeholder="column" class="form-control">
                            </div>
                            </div>                        
                        </div>
                    </div>

                    {{ Form::submit('Add', array('class' => 'btn btn-primary')) }}
                    {{Form::close()}}

                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->
    
    <div class="modal fade" id="add_categories" tabindex="-1" role="dialog" aria-labelledby="basicvalCode" aria-hidden="true">
        <div class="modal-dialog wide">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                    <h4 class="modal-title" id="basicvalCode">Add Categories</h4>
                </div>
                <div class="modal-body">
    
                    {{ Form::open(array('url' => 'product/savemanufacturercategory', 'class' => 'form-horizontal form1', 'id' => 'add_category_form' )) }}
                    {{ Form::hidden('_method','POST') }}
                    <div class="form-horizontal form3">
                        <div class="form-group">
                            <div class="col-sm-12">
                                <label class="col-sm-2 control-label" for="Manufacturer">Manufacturer</label>
                                <div class="col-sm-10">
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-addon addon-red"><i class="fa fa-building-o"></i></span>
                                        <div id="selectbox">
                                            <select class="chosen-select form-control parsley-validated" name="manufacturer_id" id="manufacturer_id" parsley-trigger="change" parsley-required="true" parsley-error-container="#selectbox" required="true">
                                                @foreach ($manufacturerList as $key => $value)
                                                <option value="{{ $key }}" selected>{{ $value }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-horizontal form3">
                            <div class="form-group">
                                <div class="col-sm-12">
                                    <?php 
                                    foreach($categoryList as $category){
                                        foreach($category as $childCategory){                                            
                                            if(property_exists($childCategory, 'childs'))
                                            {
                                                ?>
                                                    <div class="checkbox" id="parent" style="padding-left: 0px;">
                                                        <input type="checkbox" value="<?php echo $childCategory->category_id; ?>" name="category_name[]" id="category_<?php echo $childCategory->category_id; ?>" parsley-trigger="change" parsley-required="true" class="parsley-validated <?php echo $childCategory->category_id; ?> <?php echo $childCategory->name; ?>">
                                                        <label for="category_<?php echo $childCategory->category_id; ?>"><?php echo $childCategory->name; ?></label>
                                                    </div>
                                                <?php
                                                foreach($childCategory->childs as $childChildCategory)
                                                {
                                                    if(property_exists($childChildCategory, 'childs'))
                                                    {
                                                        ?>
                                                            <div class="checkbox" id="first_child" style="padding-left: 25px;">
                                                                <input type="checkbox" value="<?php echo $childChildCategory->category_id; ?>" name="category_name[]" id="category_<?php echo $childChildCategory->category_id; ?>" parsley-trigger="change" parsley-required="true" class="parsley-validated category_<?php echo $childCategory->category_id; ?> category_<?php echo $childCategory->category_id; ?> <?php echo $childChildCategory->name; ?>">
                                                                <label for="category_<?php echo $childChildCategory->category_id; ?>"><?php echo $childChildCategory->name; ?></label>
                                                            </div>
                                                        <?php
                                                        foreach($childChildCategory->childs as $childChildChildCategory)
                                                        { ?>
                                                            <div class="checkbox" id="second_child" style="padding-left: 50px;">
                                                                <input type="checkbox" value="<?php echo $childChildChildCategory->category_id; ?>" name="category_name[]" id="category_<?php echo $childChildChildCategory->category_id; ?>" parsley-trigger="change" parsley-required="true" class="parsley-validated category_<?php echo $childChildCategory->category_id; ?> category_<?php echo $childCategory->category_id; ?> <?php echo $childChildChildCategory->name; ?>">
                                                                <label for="category_<?php echo $childChildChildCategory->category_id; ?>"><?php echo $childChildChildCategory->name; ?></label>
                                                            </div>
                                                        <?php }
                                                    }else{ ?>
                                                        <div class="checkbox" id="first_child" style="padding-left: 25px;">
                                                            <input type="checkbox" value="<?php echo $childChildCategory->category_id; ?>" name="category_name[]" id="category_<?php echo $childChildCategory->category_id; ?>" parsley-trigger="change" parsley-required="true" class="parsley-validated category_<?php echo $childChildCategory->category_id; ?> category_<?php echo $childCategory->category_id; ?> <?php echo $childChildCategory->name; ?>">
                                                            <label for="category_<?php echo $childChildCategory->category_id; ?>"><?php echo $childChildCategory->name; ?></label>
                                                        </div>
                                                    <?php }
                                                }
                                            }else{ ?>
                                                <div class="checkbox" id="parent" style="padding-left: 10px;">
                                                    <input type="checkbox" value="<?php echo $childCategory->category_id; ?>" name="category_name[]" id="category_<?php echo $childCategory->category_id; ?>" parsley-trigger="change" parsley-required="true" class="parsley-validated category_<?php echo $childCategory->category_id; ?> <?php echo $childCategory->name; ?>">
                                                    <label for="category_<?php echo $childCategory->category_id; ?>"><?php echo $childCategory->name; ?></label>
                                                </div>
                                            <?php }
                                        }
                                    }                                    
                                    ?>
                                </div>
                            </div>                        
                    </div>
                    <div class="navbar-fixed-bottom" role="navigation">
                        <div id="content" class="col-md-12">
                            <button class="btn btn-primary"><i class="fa fa-hdd-o"></i> Save</button>
                            <button class="btn btn-default"><i class="fa fa-times-circle"></i> Cancel</button>
                        </div>           
                    </div>                    
                    {{Form::close()}}
                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->
    <div id="treeGrid">
    </div>

</div> <!--/.main -->



@stop

@section('style')
{{HTML::style('jqwidgets/styles/jqx.base.css')}}

@stop

@section('script')
<!-- {{HTML::script('scripts/jquery-1.11.1.min.js')}} -->
{{HTML::script('jqwidgets/jqxcore.js')}}
{{HTML::script('jqwidgets/jqxdata.js')}}
{{HTML::script('jqwidgets/jqxbuttons.js')}}
{{HTML::script('jqwidgets/jqxscrollbar.js')}}
{{HTML::script('jqwidgets/jqxdatatable.js')}}
{{HTML::script('jqwidgets/jqxtreegrid.js')}}
{{HTML::script('scripts/demos.js')}}

<!-- Include all compiled plugins (below), or include individual files as needed -->
<!-- <script src="js/bootstrap.min.js"></script> -->
<script type="text/javascript">
    $(document).ready(function (){
        
        $('#add_parent_validation').bootstrapValidator({
//        live: 'disabled',
        message: 'This value is not valid',
        feedbackIcons: {
            valid: 'glyphicon glyphicon-ok',
            invalid: 'glyphicon glyphicon-remove',
            validating: 'glyphicon glyphicon-refresh'
        },
        fields: {
          name: {
                    validators: {
                        notEmpty: {
                            message: 'Please enter Category name.'
                        },
                    remote: {
                            url: '/customer/uniquevalidation',
                            type: 'POST',
                            data: {
                                table_name: 'categories', 
                                field_name: 'name', 
                                field_value: $('#add_parent_validation #name').val(),
                                pluck_id: 'category_id', 
                            },
                            delay: 2000,     // Send Ajax request every 2 seconds
                            message: 'Name already exists.'
                        },
                    }
                }
        }
    }).on('success.form.bv', function(event) {
            ajaxCallPopup($('#add_parent_validation'));
        return true;
        }).validate({
        submitHandler: function (form) {
            return false;
        }
    });

    $('#add_category_validation').bootstrapValidator({
//        live: 'disabled',
        message: 'This value is not valid',
        feedbackIcons: {
            valid: 'glyphicon glyphicon-ok',
            invalid: 'glyphicon glyphicon-remove',
            validating: 'glyphicon glyphicon-refresh'
        },
        fields: {
          
            name: {
                    validators: {
                        notEmpty: {
                            message: 'Please enter Category name.'
                        },
                        remote: {
                            url: '/customer/uniquevalidation',
                            type: 'POST',
                            data: {
                                table_name: 'categories', 
                                field_name: 'name', 
                                field_value: $('#add_category_validation #name').val(),
                                pluck_id: 'category_id', 
                            },
                            delay: 2000,     // Send Ajax request every 2 seconds
                            message: 'Name already exists.'
                        },
                    }
                }
        }
    }).on('success.form.bv', function(event) {
            ajaxCallPopup($('#add_category_validation'));
        return true;
        }).validate({
        submitHandler: function (form) {
            return false;
        }
    });

    $('#update_category_validation').bootstrapValidator({
//        live: 'disabled',
        message: 'This value is not valid',
        feedbackIcons: {
            valid: 'glyphicon glyphicon-ok',
            invalid: 'glyphicon glyphicon-remove',
            validating: 'glyphicon glyphicon-refresh'
        },
        fields: {
            name: {
                    validators: {
                        notEmpty: {
                            message: 'Please enter Category name.'
                        }
                    }
                }
        }
    }).on('success.form.bv', function(event) {
            ajaxCallPopup($('#update_category_validation'));
        return true;
        }).validate({
        submitHandler: function (form) {
            return false;
        }
    });  
});
</script>
<script type="text/javascript">
function getcategoriesName(el) {
    var parentCategory = $(el).closest('tr').find('td:eq(0) span:last').text();
    $('#addCategory_parent_id').find('option').prop('disabled', true);
    $('#addCategory_parent_id').find('option').filter(function () {
        return ($(this).text() == parentCategory);
    }).prop({'selected': true, 'disabled': false});

}

$(document).ready(function ()
{
    $('#manufacturer_id').trigger('change');
    $.ajax(
            {
                url: "/product/getcategoriestree",
                success: function (result)
                {


                    var employees = result;
                    // prepare the data
                    var source =
                            {
                                datatype: "json",
                                datafields: [
                                    {name: 'id', type: 'varchar'},
                                    {name: 'pname', type: 'varchar'},
                                    //name: 'product_class', type: 'varchar'},
                                    //{name: 'product_type', type: 'varchar'},
                                    {name: 'stat', type: 'varchar'},
                                    {name: 'actions', type: 'varchar'},
                                    {name: 'children', type: 'array'},
                                    {name: 'expanded', type: 'bool'}
                                ],
                                hierarchy:
                                        {
                                            root: 'children'
                                        },
                                id: 'id',
                                localData: employees
                            };
                    var dataAdapter = new $.jqx.dataAdapter(source);
                    $("#treeGrid").jqxTreeGrid(
                            {
                                width: "100%",
                                source: dataAdapter,
                                sortable: true,
                                columns: [
                                    //{text: 'Parent', datafield: 'name', width: 150},
                                    {text: 'Category Name', datafield: 'pname', width: "90%"},
                                    //{text: 'Product Class', datafield: 'product_class', width: 250},
                                    //{text: 'Product Type', datafield: 'product_type', width: 250},
                                    //{text: 'Status', datafield: 'stat', width: 150},
                                    {text: 'Actions', datafield: 'actions', width: "10%"}
                                ]
                            });

                    }
                
                });
    makePopupAjax($('#basicvalCodeModalAddParent'));
    makePopupAjax($('#addCategory'));
    makePopupEditAjax($('#editCategory'));
    getCategories();
});

function getCategories()
{
    url = '/product/getcategorieslist';
    // Send the data using post
    var posting = $.get(url);
    // Put the results in a div
    posting.done(function (data) {
        console.log(data);
        
    });
}

function deleteEntityType(category_id)
{
    var deletecategory = confirm("Are you sure you want to Delete ?"), self = $(this);
        if ( deletecategory == true ) {
            $.ajax({
                data: '',
                type: 'GET',
                datatype: "JSON",
                url: '/product/deletecategory/' + category_id,
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
$('[name="category_name[]"]').click(function(event){
    var $checkbox = $(this);
    if($checkbox.is(':checked'))
    {
        $('.'+$checkbox.attr('id')).prop('checked', true);
    }else{
        $('.'+$checkbox.attr('id')).prop('checked', false);
    }
});
$('#add_category_form #manufacturer_id').change(function(event){
    $('[name="category_name[]"]').each(function(event){
        $(this).prop('checked', false);
    });    
    var url = '/product/getcustomercategorylist';
    var manufacturerId = $(this).val();
    var posting = $.get(url, { manufacturer_id: manufacturerId });
    // Put the results in a div
    posting.done(function (data) {
        if(data.status == true)
        {
            if(data.categories.category_id != null)
            {
                var categories = data.categories.category_id.split(',');
                $.each(categories, function(id, category){
                    $('#category_'+category).prop('checked', true);
                });
            }
        }
    });  
});

$('#add_category_form').submit(function(event){
    event.preventDefault();    
});
$("div .navbar-fixed-bottom .btn-default").on("click", function(e) {
    $('#add_categories .close').trigger('click');
});
$("div .navbar-fixed-bottom .btn-primary").on("click", function(e) {
    $(this).prop('disabled', true);
    url = $('#add_category_form').attr('action');
    var manufacturerId = $('#add_category_form #manufacturer_id').val();
    var categoryList = new Array();
    $('input:checkbox[name="category_name[]"]').each(function () { 
        var cat = this.checked ? $(this).val() : "";
        if(cat != "")
        {
            categoryList.push(cat);
        }
    });
    // Send the data using post
    var posting = $.post(url, { manufacturer_id: manufacturerId, category_list: categoryList });
    // Put the results in a div
    posting.done(function (data) {
        if(data.status == true)
        {
            alert('Sucessfully added categories');
            $('#add_categories .close').trigger('click');
            $("div .navbar-fixed-bottom .btn-primary").prop('disabled', false);
        }else{
            alert('Unable to add categories, please try again');
            $("div .navbar-fixed-bottom .btn-primary").prop('disabled', false);
        }
    });    
});
</script>    
@stop   
