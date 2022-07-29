@extends('layouts.default')
@extends('layouts.header')
@extends('layouts.sideview')
@section('content')

@section('style')
{{HTML::style('jqwidgets/styles/jqx.base.css')}}
{{HTML::style('css/dragdrop/jquery-ui.css')}}
{{HTML::style('css/dragdrop/style.css')}}
{{HTML::style('css/bootstrap-select.css')}}


@stop

@section('script')
{{HTML::script('jqwidgets/jqxcore.js')}}
{{HTML::script('jqwidgets/jqxdata.js')}}
{{HTML::script('jqwidgets/jqxbuttons.js')}}
{{HTML::script('jqwidgets/jqxscrollbar.js')}}
{{HTML::script('jqwidgets/jqxdatatable.js')}}
{{HTML::script('jqwidgets/jqxtreegrid.js')}}
{{HTML::script('jqwidgets/jqxlistbox.js')}}
{{HTML::script('jqwidgets/jqxdropdownlist.js')}}
{{HTML::script('scripts/demos.js')}}
{{HTML::script('js/plugins/dragdrop/jquery-ui.js')}}
{{HTML::script('js/plugins/dragdrop/fieldChooser.js')}}
{{HTML::script('js/plugins/bootstrap-select/bootstrap-select.js')}}

<!-- Include all compiled plugins (below), or include individual files as needed -->
<script type="text/javascript">

    $('#addAttribute [name="name"]').keyup(function () {
        //console.log('Hi');
        $('#addAttribute [name="attribute_code"]').val($('#addAttribute [name="name"]').val().replace(/\s+/g, '_').toLowerCase());
        $('[name="attribute_code"]').change();
    });
    $('#editAttribute [name="name"]').keyup(function () {
        //console.log('Hi');
        $('#editAttribute [name="attribute_code"]').val($('#editAttribute [name="name"]').val().replace(/\s+/g, '_').toLowerCase());
        $('#editAttribute [name="attribute_code"]').change();
    });


//validator
    $(document).ready(function () {
        $('#editAttribute').bootstrapValidator({
//        live: 'disabled',
            message: 'This value is not valid',
            feedbackIcons: {
                valid: 'glyphicon glyphicon-ok',
                invalid: 'glyphicon glyphicon-remove',
                validating: 'glyphicon glyphicon-refresh'
            },
            fields: {
                attribute_set_id: {
                    validators: {
                        notEmpty: {
                            message: 'Please select Attribute Set.'
                        }
                    }
                },
                attribute_group_id: {
                    validators: {
                        notEmpty: {
                            message: 'Please select Attribute Group.'
                        }
                    }
                },
                name: {
                    validators: {
                        notEmpty: {
                            message: 'Attribute Name is Required'
                        },
                        remote: {
                            message: 'Name already exists.Please enter a new name',
                            url: '/product/checkAttributeAvailability',
                            data: function (validator, $field, value) {
                                return {
                                    'manufacturer_id': validator.getFieldElements('manufacturer_id').val(),
                                    'attribute_id': validator.getFieldElements('attribute_id').val(),
                                    /*'attribute_code': validator.getFieldElements('attribute_code').val(),*/
                                };
                            },
                            delay: 7000     // Send Ajax request every 2 seconds
                        },
                    }
                },
                attribute_code: {
                    trigger: 'change keyup',
                    validators: {
                        notEmpty: {
                            message: 'Attribute Code is required'
                        },
                        regexp: {
                            regexp: '^[a-zA-Z0-9_]+$',
                            message: 'Please enter only alpha-numeric and underscore'
                        },
                        remote: {
                            message: 'Attribute Exists with this code.Please enter a new code',
                            url: '/product/checkAttrAvailability',
                            type: 'GET',
                            data: function (validator, $field, value) {
                                return {
                                    'attribute_code': validator.getFieldElements('attribute_code').val(),
                                    'attribute_id': validator.getFieldElements('attribute_id').val(),
                                };
                            },
                            delay: 7000     // Send Ajax request every 2 seconds
                        },
                    }
                },
                input_type: {
                    validators: {
                        notEmpty: {
                            message: 'Input Type is required'
                        }
                    }
                },
                attribute_type: {
                    validators: {
                        notEmpty: {
                            message: 'Attribute Type is required'
                        }
                    }
                }
            }
        }).on('success.form.bv', function (event) {
            event.preventDefault();
            ajaxCallPopup($('#editAttribute'));
            ajaxCall();
            return false;
        }).validate({
            submitHandler: function (form) {
                return false;
            }
        });
        $('#basicvalCodeModal1').on('hide.bs.modal', function () {
            console.log('resetForm');
            $('#editAttribute').data('bootstrapValidator').resetForm();
            $('#editAttribute')[0].reset();
        });
        $('#addAttribute').bootstrapValidator({
//        live: 'disabled',
            message: 'This value is not valid',
            feedbackIcons: {
                valid: 'glyphicon glyphicon-ok',
                invalid: 'glyphicon glyphicon-remove',
                validating: 'glyphicon glyphicon-refresh'
            },
            fields: {
                attribute_set_id: {
                    validators: {
                        notEmpty: {
                            message: 'Please select Attribute Set.'
                        }
                    }
                },
                attribute_group_id: {
                    validators: {
                        notEmpty: {
                            message: 'Please select Attribute Group.'
                        }
                    }
                },
                attribute_code: {
                    trigger: 'change keyup',
                    validators: {
                        notEmpty: {
                            message: 'Attribute code is Required.'
                        },
                        regexp: {
                            regexp: '^[a-zA-Z0-9_]+$',
                            message: 'Please enter only alpha-numeric and underscore'
                        },
                        remote: {
                            message: 'Attribute Exists with this code.Please enter a new code',
                            url: '/product/checkAttrAvailability',
                            type: 'GET',
                            data: function (validator, $field, value) {
                                return {
                                    'attribute_code': validator.getFieldElements('attribute_code').val(),
                                };
                            },
                            delay: 2000     // Send Ajax request every 2 seconds
                        }

                    }
                },
                name: {
                    validators: {
                        notEmpty: {
                            message: 'Attribute Name is Required'
                        },
                        remote: {
                            message: 'Name already exists.Please enter a new name',
                            url: '/product/checkAttributeAvailability',
                            type: 'GET',
                            data: function (validator, $field, value) {
                                return {
                                    'manufacturer_id': validator.getFieldElements('manufacturer_id').val(),
                                };
                            },
                            delay: 2000     // Send Ajax request every 2 seconds
                        },
                    }/*,onSuccess: function(e, data) {
                     $('#addAttribute').data('bootstrapValidator').validateField('attribute_code');
                     }, */
                },
                input_type: {
                    validators: {
                        callback: {
                            message: 'Please choose Input Type',
                            callback: function (value, validator, $field) {
                                var options = $('[id="input_type"]').val();
                                return (options != 0);
                            }
                        },
                        notEmpty: {
                            message: 'Input Type is required'
                        }
                    }
                },
                attribute_type: {
                    validators: {
                        callback: {
                            message: 'Please choose Attribute Type',
                            callback: function (value, validator, $field) {
                                var options = $('[id="attribute_type"]').val();
                                return (options != 0);
                            }
                        },
                        notEmpty: {
                            message: 'Attribute Type is required'
                        }
                    }
                }
            }
        }).on('success.form.bv', function (event) {
            event.preventDefault();
            ajaxCallPopup($('#addAttribute'));
            ajaxCall();
            return false;
        }).validate({
            submitHandler: function (form) {
                return false;
            }
        });
        $('#basicvalCodeModal').on('hide.bs.modal', function () {
            console.log('resetForm');
            $('#addAttribute').data('bootstrapValidator').resetForm();
            $('#addAttribute')[0].reset();
        });
        $('#addAttributeGroup').bootstrapValidator({
//        live: 'disabled',
            message: 'This value is not valid',
            feedbackIcons: {
                valid: 'glyphicon glyphicon-ok',
                invalid: 'glyphicon glyphicon-remove',
                validating: 'glyphicon glyphicon-refresh'
            },
            fields: {
                'attribute_group[name]': {
                    validators: {
                        remote: {
                            message: 'Name already exists.Please enter a new name',
                            url: '/product/checkGroupAvailability',
                            type: 'GET',
                            data: function (validator, $field, value) {
                                return {
                                    'manufacturer_id': validator.getFieldElements('attribute_group[manufacturer_id]').val()
                                };
                            },
                            delay: 2000     // Send Ajax request every 2 seconds
                        },
                        notEmpty: {
                            message: 'Attribute Group Name is Required'
                        }
                    }
                },
                update_manufacturer_name: {
                    validators: {
                        callback: {
                            message: 'Please choose Manufacturer Name',
                            callback: function (value, validator, $field) {
                                var options = $('[id="update_manufacturer_name"]').val();
                                return (options != 'Please select..');
                            }
                        },
                        notEmpty: {
                            message: 'Manufacturer Name is required'
                        }
                    }
                },
                'attribute_group[category_id]': {
                    validators: {
                        callback: {
                            message: 'Please choose Category Name',
                            callback: function (value, validator, $field) {
                                var options = $('[name="attribute_group[category_id]"]').val();
                                return (options != 0);
                            }
                        },
                        notEmpty: {
                            message: 'Please select Category.'
                        }
                    }
                }
            }
        }).on('success.form.bv', function (event) {
            event.preventDefault();
            //console.log('we r hwewe');
            ajaxCallPopup($('#addAttributeGroup'));
            setTimeout('updateGroups()', 2000);
            ajaxCall();
            return false;
        }).validate({
            submitHandler: function (form) {
                return false;
            }
        });
        $('#basicvalCodeModal3').on('hide.bs.modal', function () {
            console.log('resetForm');
            $('#addAttributeGroup').data('bootstrapValidator').resetForm();
            $('#addAttributeGroup')[0].reset();
            /*            $('[id="update_manufacturer_name"]').val($('#main_manufacturer_id option:selected').text());
             $('[id="update_manufacturer_id"]').val($('#main_manufacturer_id option:selected').val()); */
        });
        $('#basicvalCodeModal3').on('show.bs.modal', function (e) {
            var manufacturerName = $('#main_manufacturer_id option:selected').text();
            //console.log(manufacturerName);
            $('#addAttributeGroup [id="update_manufacturer_name"]').val(manufacturerName);
            $('#addAttributeGroup [id="update_manufacturer_id"]').val($('#main_manufacturer_id option:selected').val());
        });
        $('#save_attribute_set').bootstrapValidator({
//        live: 'disabled',
            message: 'This value is not valid',
            feedbackIcons: {
                valid: 'glyphicon glyphicon-ok',
                invalid: 'glyphicon glyphicon-remove',
                validating: 'glyphicon glyphicon-refresh'
            },
            fields: {
                'attribute_set[attribute_set_name]': {
                    validators: {
                        remote: {
                            message: 'Name already exists.Please enter a new name',
                            url: '/product/checkSetAvailability',
                            type: 'GET',
                            data: function (validator, $field, value) {
                                return {
                                    'manufacturer_id': validator.getFieldElements('attribute_set[manufacturer_id]').val()
                                };
                            },
                            delay: 2000     // Send Ajax request every 2 seconds
                        },
                        notEmpty: {
                            message: 'Attribute Set Name is Required'
                        }
                    }
                },
                update_manufacturer_name: {
                    validators: {
                        callback: {
                            message: 'Please choose Manufacturer Name',
                            callback: function (value, validator, $field) {
                                var options = $('[name="update_manufacturer_name"]').val();
                                return (options != 'Please select..');
                            }
                        },
                        notEmpty: {
                            message: 'Manufacturer Name is required'
                        }
                    }
                },
                'attribute_set[category_id]': {
                    validators: {
                        callback: {
                            message: 'Please choose Category Name',
                            callback: function (value, validator, $field) {
                                var options = $('[name="attribute_set[category_id]"]').val();
                                return (options != 0);
                            }
                        },
                        notEmpty: {
                            message: 'Please select Category.'
                        }
                    }
                }
            }
        }).on('success.form.bv', function (event) {
            event.preventDefault();
            $('#save_attribute_set_button').prop('disabled', true);
            var url = '/product/saveattributeset';
            var inherit = $('[name="attribute_set[inherit_from]"]').prop('checked');
            if ( inherit )
            {
                inherit = 1;
            } else {
                inherit = 0;
            }
            var selectedAttr = new Array();
            var selectedAttrArray = new Array();
            $('#attribute_id div').each(function (i, v) {
                selectedAttr.push($(v).attr('value'));
                selectedAttrArray.push($(v).attr('key'));
            });
            // selectedAttr = selectedAttr.substr(1,selectedAttr.length);
            var temp = {
                attribute_set_name: $('[name="attribute_set[attribute_set_name]"]').val(),
                category_id: $('[name="attribute_set[category_id]"]').val(),
                manufacturer_id: $('#main_manufacturer_id').val(),
                is_active: $('[name="attribute_set[is_active]"]').val(),
                //inherit_from: $('[name="attribute_set[inherit_from]"]').val(),
                attribute_id: selectedAttr,
                //sort_order: selectedAttrArray,
                inherit_from: inherit
            };
            var posting = $.post(url, {attribute_set: temp});
            // Put the results in a div
            posting.done(function (data) {
                console.log(data['message']);
                if ( data['status'] == true )
                {
                    $('.close').trigger('click');
                    alert(data['message']);
                    //location.reload();
                    ajaxCall();
                } else {
                    alert(data['message']);
                }
                //location.reload();
            });
            $('#save_attribute_set_button').prop('disabled', false);
            return false;
        }).validate({
            submitHandler: function (form) {
                return false;
            }
        });
        $('#addAttributeSet').on('hide.bs.modal', function () {
            console.log('resetForm');
            $('#save_attribute_set').data('bootstrapValidator').resetForm();
            $('#save_attribute_set')[0].reset();
            $('#attribute_id').empty();
            $('#Selectattribute').empty();
            $('[id="update_manufacturer_name"]').val($('#main_manufacturer_id option:selected').text());
            $('[id="update_manufacturer_id"]').val($(this).val());
        });
        $('#addAttributeSet').on('show.bs.modal', function () {
            $('#addAttributeSet [id="update_manufacturer_name"]').val($('#main_manufacturer_id option:selected').text());
            $('#addAttributeSet [id="update_manufacturer_id"]').val($('#main_manufacturer_id option:selected').val());
        });

        $('#editAttributeset').bootstrapValidator({
//        live: 'disabled',
            message: 'This value is not valid',
            feedbackIcons: {
                valid: 'glyphicon glyphicon-ok',
                invalid: 'glyphicon glyphicon-remove',
                validating: 'glyphicon glyphicon-refresh'
            },
            fields: {
                attribute_set_name: {
                    validators: {
                        remote: {
                            message: 'Name already exists.Please enter a new name',
                            url: '/product/checkSetAvailability',
                            type: 'GET',
                            data: function (validator, $field, value) {
                                return {
                                    'manufacturer_id': validator.getFieldElements('attribute_set[manufacturer_id]').val(),
                                    'attribute_set_id': validator.getFieldElements('attribute_set_id').val()
                                };
                            },
                            delay: 2000     // Send Ajax request every 2 seconds
                        },
                        notEmpty: {
                            message: 'Attribute Set Name is Required'
                        }
                    }
                },
                category_id: {
                    validators: {
                        callback: {
                            message: 'Please choose Category Name',
                            callback: function (value, validator, $field) {
                                // Get the selected options
                                var options = $('[id="category_id1"]').val();
                                return (options != 0);
                            }
                        },
                        notEmpty: {
                            message: 'Please select Category.'
                        }
                    }
                }
            }
        }).on('success.form.bv', function (event) {
            event.preventDefault();
            //ajaxCall();
            return false;
        })
    });
    $('#editAttributeSet').on('hide.bs.modal', function () {
        console.log('resetForm');
        $('#editAttributeset').data('bootstrapValidator').resetForm();
        $('#editAttributeset')[0].reset();
        $('[id="update_manufacturer_name"]').val($('#main_manufacturer_id option:selected').text());
        $('[id="update_manufacturer_id"]').val($('#main_manufacturer_id option:selected').val());
    });
    $('#editAttributeSet').on('show.bs.modal', function () {
        $('[id="update_manufacturer_name"]').val($('#main_manufacturer_id option:selected').text());
        $('[id="update_manufacturer_id"]').val($('#main_manufacturer_id option:selected').val());
    });
//validator
    $(document).ready(function () {
        var $sourceFields = $("#Selectattribute1");
        var $destinationFields = $("#attribute_id1");
        var $chooser = $("#fieldChooser").fieldChooser(Selectattribute1, attribute_id1);
    });
    $(document).ready(function () {
        var $sourceFields = $("#Selectattribute");
        var $destinationFields = $("#attribute_id");
        var $chooser = $("#fieldChooser").fieldChooser(Selectattribute, attribute_id);
    });
    $(document).ready(function ()
    {
        $('#main_manufacturer_id').trigger('change');
        makePopupAjax($('#basicvalCodeModal'));
        makePopupEditAjax($('#basicvalCodeModal1'), 'attribute_id');
        makePopupAttributeAjax($('#basicvalCodeModal2'), 'attribute_id');
        makePopupAjax($('#basicvalCodeModal3'));
        makePopupAjax($('#addAttributeSet'));
        makePopupEditAjax($('#basicvalCodeModal4'), 'attribute_group_id');
        makePopupEditAjax($('#editAttributeSet'), 'attribute_set_id');
    });

    function ajaxCall()
    {
        var manufacturerId = $('#main_manufacturer_id').val();
        $.ajax(
                {
                    url: "/product/getallattributes/" + manufacturerId,
                    success: function (result)
                    {
                        var employees = result;
                        // prepare the data
                        var source =
                                {
                                    datatype: "json",
                                    datafields: [
                                        {name: 'attribute_set_name', type: 'string'},
                                        {name: 'category_id', type: 'number'},
                                        {name: 'manufacturer_id', type: 'number'},
                                        {name: 'attribute_group_name', type: 'string'},
                                        {name: 'attribute_name', type: 'string'},
                                        {name: 'text', type: 'string'},
                                        {name: 'actions', type: 'varchar'},
                                        {name: 'children', type: 'array'},
                                        {name: 'expanded', type: 'bool'}
                                    ],
                                    hierarchy:
                                            {
                                                root: 'children'
                                            },
                                    id: 'attribute_set_name',
                                    localData: employees
                                };
                        var dataAdapter = new $.jqx.dataAdapter(source);
                        $("#treeGrid").jqxTreeGrid(
                                {
                                    width: '100%',
                                    source: dataAdapter,
                                    sortable: true,
                                    //sortable: true,
                                    //filterable: true,
                                    columns: [
                                        {text: 'Attribute Set Name', datafield: 'attribute_set_name', width: '20%'},
                                        {text: 'Category Name', datafield: 'category_id', width: '20%'},
                                        {text: 'Manufacturer Name', datafield: 'manufacturer_id', width: '20%'},
                                        {text: 'Group Name', datafield: 'attribute_group_name', width: '10%'},
                                        {text: 'Attribute Name', datafield: 'attribute_name', width: '10%'},
                                        {text: 'Attribute Text', datafield: 'text', width: '10%'},
                                        {text: 'Actions', datafield: 'actions', width: '10%'}
                                    ]
                                });
                    }
                });
    }

    $('#map_attributes').submit(function (event) {
        event.preventDefault();
        var url = $(this).attr('action');
        $.post(url, {attribute_group_id: $('#attribute_group_id').val(), aname: $('#aname').val()}, function (data) {
            $.each(data, function (i, v) {
                if ( true == v )
                {
                    $('#basicvalCodeModal2').addClass('modal fade');
                    location.reload();
                }
            });
        });
    });

    function makePopupAttributeAjax($el, primaryKey)
    {
        $el.on('shown.bs.modal', function (e) {
            var url = $(e.relatedTarget).data('href'),
                    $this = $(this),
                    $form = $this.find('form'),
                    key = primaryKey || 'attribute_group_id';

            $.get(url, function (data) {
                $.each(data, function (i, v) {
                    $form.find('[name="' + i + '"]').val(v);
                });
            });
        });
    }
    function deleteAttrSet(attribute_set_id)
    {
        var dec = confirm("Are you sure you want to Delete ?");
        if ( dec == true ) {
            $('#verifyUserPassword').modal('show');
            $('#verifyUserPassword button#cancel-btn').on('click', function (e) {
                e.preventDefault();
                //console.log('clicked cancel');
                $('#verifyUserPassword').modal('hide');
            });
            $('#verifyUserPassword button#save-btn').off('click');
            $('#verifyUserPassword button#save-btn').on('click', function (e) {
                e.preventDefault();
                //console.log('cliked submit');
                var userPassword = $.trim($('#verifyUserPassword input').val());
                if ( userPassword == '' ) {
                    alert('Field is required');
                    return false
                } else
                    $.ajax({
                        url: '/product/deleteattributeset',
                        data: {attribute_set_id: attribute_set_id, 'password': userPassword},
                        type: 'POST',
                        success: function (result)
                        {
                            if ( result == 1 ) {
                                alert('Succesfully Deleted !!');
                                //location.reload();
                                ajaxCall();
                                $('#verifyUserPassword').modal('hide');
                            } else {
                                alert(result);
                            }
                        },
                        error: function (err) {
                            console.log('Error: ' + err);
                        },
                        complete: function (data) {
                            console.log(data);
                        }
                    });
            });
        }
    }
    function delAttributeFromGroup(attribute_id, attribute_set_id)
    {
        var dec = confirm("Are you sure you want to Delete ?");
        if ( dec == true ) {
            $('#verifyUserPassword').modal('show');
            $('#verifyUserPassword button#cancel-btn').on('click', function (e) {
                e.preventDefault();
                //console.log('clicked cancel');
                $('#verifyUserPassword').modal('hide');
            });
            $('#verifyUserPassword button#save-btn').off('click');
            $('#verifyUserPassword button#save-btn').on('click', function (e) {
                e.preventDefault();
                //console.log('cliked submit');
                var userPassword = $.trim($('#verifyUserPassword input').val());
                if ( userPassword == '' ) {
                    alert('Field is required');
                    return false
                } else
                    $.ajax({
                        url: '/product/delAttributeFromGroup',
                        data: {attribute_id: attribute_id, attribute_set_id: attribute_set_id, password: userPassword},
                        type: 'POST',
                        success: function (result)
                        {
                            if ( result == 1 ) {
                                alert('Succesfully Deleted !!');
                                //location.reload();
                                ajaxCall();
                                //window.location.href = '/customer/editcustomer/'+manufacturerId;
                                $('#verifyUserPassword').modal('hide');
                            } else {
                                alert(result);
                            }
                        },
                        error: function (err) {
                            console.log('Error: ' + err);
                        },
                        complete: function (data) {
                            console.log(data);
                        }
                    });
            });
        }
    }
    function switchAttributeSearchable(attribute_id,attribute_set_id,flag)
    {
        if(flag){
            console.log(flag);
            var decission = confirm("Do you want to make it searchable?");
            if(decission==true)
            updateSearch(attribute_id,attribute_set_id,flag);
        }else{
            console.log(flag);
            var decission = confirm("Are you sure you want to make it unsearchable?");
            if(decission==true)
            updateSearch(attribute_id,attribute_set_id,flag);           
        }
    }
 function updateSearch(attribute_id,attribute_set_id,flag)
 {  
    $.ajax({
        url: '/product/searchAttributes',
        data: {attribute_id: attribute_id, attribute_set_id: attribute_set_id, flag: flag},
        type: 'POST',
        success: function (result)
        {
            if ( result == 1 ) {
                alert('Succesfully Updated !!');
                //location.reload();
                ajaxCall();
            } else {
                alert(result);
            }
        },
        error: function (err) {
            console.log('Error: ' + err);
        },
        complete: function (data) {
            console.log(data);
        }
    });
 }
    function getAttributeGroupName(attributeSetId) {
        $('#attribute_set_id_add_attribute').val(attributeSetId);
    }

    function getAssignAttribute(attributeSetId)
    {
        $('#assign_attribute_set_id').val(attributeSetId);
        $('#attribute_set_id_add_attribute').val(attributeSetId);
        $('#assign_attribute_set_name').val($('#attribute_set_id_add_attribute option:selected').text());
    }

    function loadAssignData()
    {
        var manufacturer_id = $('#main_manufacturer_id').val();
        var url = '/product/getelementdata';
        // Send the data using post
        var posting = $.post(url, {data_type: 'locations_groups', data_value: manufacturer_id});
        // Put the results in a div
        posting.done(function (data) {
            var result = JSON.parse(data);
            var temp;
            var fieldId;
            $.each(result, function (field, data) {
                if ( field == 'locations' )
                {
                    fieldId = 'locations';
                } else {
                    fieldId = 'product_groups';
                }
                if ( data != '' )
                {
                    $.each(data, function (key, value) {
                        $('#' + fieldId).append('<option value="' + value['id'] + '">' + value['name'] + '</option>');
                    });
                }
            });
        });
    }

    $('#main_manufacturer_id').change(function () {
        $('[id="update_manufacturer_name"]').val($('#main_manufacturer_id option:selected').text());
        $('[id="update_manufacturer_id"]').val($(this).val());
        ajaxCall($(this).val());
        updateGroups();
        loadAssignData();
    });

    function updateGroups()
    {
        $('[name="attribute_group_id"]').empty();
        var manufacturer_id = $('#main_manufacturer_id').val();
        var url = '/product/getelementdata';
        // Send the data using post
        var posting = $.post(url, {data_type: 'attributeGroups', data_value: manufacturer_id});
        // Put the results in a div
        posting.done(function (data) {
            var result = JSON.parse(data);
            $('[name="attribute_group_id"]').append('<option value="" selected="true">Please select... </option>');
            $.each(result, function (key, value) {
                $('[name="attribute_group_id"]').append('<option value="' + value['attribute_group_id'] + '">' + value['name'] + '</option>');
            });
        });
    }
    $('[data-target="#addAttributeSet"]').click(function () {
        var manufacturer_id = $('#main_manufacturer_id').val();
        var url = '/product/attributes';
        // Send the data using post
        var posting = $.get(url, {manufacturer_id: manufacturer_id});
        // Put the results in a div
        posting.done(function (data) {
            var result = JSON.parse(data);
            $.each(result, function (key, value) {
                /*$('#Selectattribute').append('<option value="' + value['attribute_id'] + '">' + value['name'] + '</option>');  */
                /*$('#Selectattribute').append('<li value="' + value['attribute_id'] + '">' + value['name'] + '</li>');*/
                $('#Selectattribute').append('<div class="fc-field" value="' + value['attribute_id'] + '">' + value['name'] + '</div>');
            });
        });
        //getAttributes();
    });
    
    $('#update_assign_attribute_set_button').click(function () {
        var url = $('#assignGroupsLocations').attr('action');
        var postData = $('#assignGroupsLocations').serializeArray();
        var posting = $.post(url, postData );
        posting.done(function (data) {
            /*var result = JSON.parse(data);
            console.log(data);*/
            console.log(data['message']);
            if ( data['status'] == true )
            {
                $('.close').trigger('click');
                alert(data['message']);
                //location.reload();
                ajaxCall();
            } else {
                alert(data['message']);
            }            
        });
        //getAttributes();
    });
//Edit
    $('#editAttributeSet').on('show.bs.modal', function (e) {

        var manufacturer_id = $('#main_manufacturer_id').val();
        // console.log(manufacturer_id);
        var attribute_set_id = $(e.relatedTarget).data('attributeid');
        var url = '/product/getAttributedata/' + manufacturer_id + '/' + attribute_set_id;
        //console.log('Removing already selected attributes: '+$('#Removeattribute1').val());
        $('#Removeattribute1').val('0');
        var posting = $.get(url);
        $('#Selectattribute1').html('');
        $('#attribute_id1').html('');
        posting.done(function (data) {
            var result = JSON.parse(data);
            //console.log(result);            
            $.each(result.unselected, function (key, value) {
                var key = key.substr(1, key.length);
                //$('#Selectattribute1').append('<option value="' + key + '">' + value + '</option>'); 
                $('#Selectattribute1').append('<div class="fc-field" value="' + key + '">' + value + '</div>');
            });
            $.each(result.selectedAttr, function (key, value) {
                var key = key.substr(1, key.length);
                //console.log(key);
                /*$('#attribute_id1').append('<option value="' + key + '">' + value + '</option>');*/
                $('#attribute_id1').append('<div class="fc-field" value="' + key + '">' + value + '</div>');
                //console.log($('#attribute_id').html());
            });
            $('#Removeattribute1').val('0');
            $('#attribute_id1 div').each(function (i, v) {
                $('#formattributes1').val($('#formattributes1').val() + ',' + $(v).attr('value'));
            });
        });
    });
//Edit
    $('#update_attribute_set_button').click(function (e) {
        e.preventDefault();
        e.stopPropagation();
        var formattributes1 = '';
        $('#attribute_id1 div').each(function (i, v) {
            formattributes1 += ',' + $(v).attr('value');
        });
        formattributes1 = formattributes1.substr(1, formattributes1.length);
        $('#formattributes1').val(formattributes1);
        $('#editAttributeSet form').submit();
        ajaxCall();
    });
    $('[name="input_type"').change(function () {
        var inputTypeValue = $(this).val();
        if ( inputTypeValue == 'select' || inputTypeValue == 'multiselect' )
        {
            $('#option-button').trigger('click');
        }
    });
    $('#add_new_option').on('click', function () {
        var $template = $('#option_data');
        $clone = $template.clone();
        $('#option_data').before($clone.removeAttr('id').removeAttr('style'));
    });
    $('#assignAttributeSet').on('show.bs.modal', function (e) {
        var attribute_set_id = $('#assign_attribute_set_id').val();
        //console.log(attribute_set_id);
        var url = '/product/getAssignGroupDetails/'+ attribute_set_id;
        var posting = $.get(url); 
        posting.done(function (data) {
            //console.log(data);
            $('#assigntable').empty();

            $.each(data, function (key, value) {
                var jsonArg = new Object();
                jsonArg.product_group = value['product_group_id'];
                jsonArg.location_val = value['location_id'];
                var hiddenJsonData = new Array();
                hiddenJsonData.push(jsonArg);                
                $("#assign_data").append('<tr><td scope="row" id="product_groups_text">' + value['productgroup'] + '</td><td id="location_text">' + value['location_name']
                            + '</td><td><a href="javascript:void(0);" class="check-toggler" id="remCF"><span class="badge bg-red"><i class="fa fa-trash-o"></i></span></a><input type="hidden" name="assign_locations[]" value=' + "'" + JSON.stringify(jsonArg) + "'" + ' /></td></tr>');
            });
        });               
    });    
    function postData()
    {
        console.log('we are in view');
        return;
    }
</script>    
@stop
</head>
<body>

    @if (Session::has('message'))
    <div class="flash alert">
        <p>{{ Session::get('message') }}</p>
    </div>
    @endif
    <!-- Page content -->
    <!--  <div id="content" class="col-md-12" style="padding-left:258px !important;">  -->


    <div class="box">

        <div class="box-header with-border">
            <h3 class="box-title"><strong>Attribute </strong>  List</h3> 
            @if($addAttributesets)                 
            <a href="javascriot:void(0)"  data-toggle="modal"  class="pull-right" data-target="#addAttributeSet" ><i class="fa fa-plus-circle"></i> <span style="font-size:11px;">Add Attribute Set</span></a>
            @endif
            @if($addAttributegroups)              
            <a href="javascriot:void(0)"  data-toggle="modal"  class="pull-right" data-target="#basicvalCodeModal3" ><i class="fa fa-plus-circle"></i> <span style="font-size:11px;">Add Attribute Group</span></a>
            @endif
        </div>         

        <div class="main" style="margin-top:15px;">           
            <div class="row">
                <div class="form-group col-sm-6">
                    <label class="col-sm-2 control-label" for="BusinessType">Manufacturer</label>
                    <div class="col-sm-10">
                        <div class="input-group input-group-sm">
                            <span class="input-group-addon addon-red"><i class="fa fa-building-o"></i></span>
                            <div id="selectbox">
                                <select name="manufacturer_id" id="main_manufacturer_id" class="form-control">
                                    @if(!empty($custType) && isset($custType[0]) && $custType[0]->customer_type_id==1001)
                                    @foreach($manufacturerData as $key => $value)
                                    <option value="{{ $key }}" selected="true">{{ $value }}</option>
                                    @endforeach
                                    @else
                                    <option value="0">Please select..</option>                                    
                                    @foreach($manufacturerData as $key => $value)
                                    <option value="{{ $key }}">{{ $value }}</option>
                                    @endforeach
                                    @endif
                                </select>
                            </div>
                        </div>                        
                    </div>                    
                </div>
                <div class="col-sm-2">
                            <button class="btn btn-primary" onclick="exportexcel()">Export to xls</button>
                        </div>
                <!--                 <div class="form-group col-sm-6">
                                    <button class="btn btn-primary " data-toggle="modal" data-target="#addAttributeSet">
                                        Add Attribute Set
                                    </button>
                                    <button class="btn btn-primary " data-toggle="modal" data-target="#basicvalCodeModal3">
                                        Add Attribute Group
                                    </button>
                                </div>
                -->                
            </div> 

        </div> 

        <div class="col-sm-12">
            <div class="tile-body nopadding">                  
                <div id="treeGrid" style="width:100% !important;"></div>
                <button data-toggle="modal" id="edit" class="btn btn-default" data-target="#wizardCodeModal" style="display: none"></button>
            </div>
        </div>           
        <!-- Modal -->
        <div class="modal fade" id="basicvalCodeModal1" tabindex="-1" role="dialog" aria-labelledby="basicvalCode" aria-hidden="true">
            <div class="modal-dialog wide">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                        <h4 class="modal-title" id="basicvalCode">Edit Attribute</h4>
                    </div>
                    <div class="modal-body">
                        {{ Form::open(array('url' => '/product/updateattribute', 'data-url' => '/product/updateattribute/','id'=>'editAttribute')) }} 
                        {{ Form::hidden('_method', 'PUT') }}

                        <div class="row">
                            <div class="form-group col-sm-6">
                                <label for="exampleInputEmail">Attribute Set *</label>
                                <div class="input-group">
                                    <span class="input-group-addon addon-red"><i class="fa fa-cubes"></i></span>

                                    <select name="attribute_set_id" id="attribute_set_id" class="form-control">
                                        @foreach($attributeSetData as  $attributeSet)
                                        <option value="{{ $attributeSet->attribute_set_id}}">{{ $attributeSet->attribute_set_name}}</option>
                                        @endforeach
                                    </select>
                                </div>                        
                            </div>
                            <input type="hidden" name="manufacturer_id" id="update_manufacturer_id" value="" />  
                            <div class="form-group col-sm-6">
                                <label for="exampleInputEmail">Attribute Group *</label>
                                <div class="input-group">
                                    <span class="input-group-addon addon-red"><i class="ion-ios-color-filter-outline"></i></span>

                                    <select name="attribute_group_id" id="attribute_group_id" class="form-control"><!--required-->
                                        @foreach($ag as  $ag1)
                                        <option value="{{ $ag1->attribute_group_id}}">{{ $ag1->name}}</option>
                                        @endforeach
                                    </select>
                                </div>                        
                            </div>                                  
                        </div>

                        <div class="row">
                            <div class="form-group col-sm-6">
                                <label for="exampleInputEmail">Attribute Name *</label>
                                <div class="input-group ">
                                    <span class="input-group-addon addon-red"><i class="fa fa-cube"></i></span>
                                    <input type="text"  id="name" name="name" value="" class="form-control" aria-describedby="basic-addon1">
                                    <input type="hidden" name="attribute_id" id="attributeid" value="" />
                                </div>
                            </div>  
                            <div class="form-group col-sm-6">
                                <label for="exampleInputEmail">Attribute Code *</label>
                                <div class="input-group ">
                                    <span class="input-group-addon addon-red"><i class="fa fa-keyboard-o"></i></span>
                                    <input type="text"  id="attribute_code" name="attribute_code" value="" class="form-control" aria-describedby="basic-addon1">
                                </div>
                            </div>                                      
                        </div>
                        <div class="row">
                            <div class="form-group col-sm-6">
                                <label for="exampleInputEmail">Attribute Text</label>
                                <div class="input-group ">
                                    <span class="input-group-addon addon-red"><i class="fa fa-keyboard-o"></i></span>
                                    <input type="text" id="text" name="text" value="" class="form-control" aria-describedby="basic-addon1">
                                </div>
                            </div>                              
                            <div class="form-group col-sm-6">
                                <label for="exampleInputEmail">Input Type *</label>
                                <div class="input-group ">
                                    <span class="input-group-addon addon-red"><i class="fa fa-shield"></i></span>

                                    <select name="input_type" required class="form-control">
                                        <option  value="checkbox">Check Box</option>
                                        <option  value="radio">Radio</option>
                                        <option  value="text">Text</option>
                                        <option  value="hidden">Hidden</option>
                                        <option  value="file">File</option>
                                        <option  value="inherit">Inherit</option>
                                        <option  value="label">Label</option>
                                        <option  value="toggle">Toggle</option>
                                        <option  value="textarea">Text Area</option>
                                        <option  value="date">Date</option>
                                        <option  value="datetime">Date Time</option>
                                        <option  value="select">Select Drop Down</option>
                                        <option  value="multiselect">Multi Select Drop Down</option>
                                        <option  value="sdropdown">Single Select Drop Down</option>
                                    </select>
                                </div>
                            </div>                            
                        </div>


                        <div class="row">
                            <div class="form-group col-sm-6">
                                <label for="exampleInputEmail">Default Value</label>
                                <div class="input-group ">
                                    <span class="input-group-addon addon-red"><i class="fa fa-credit-card"></i></span>
                                    <input type="text" id="default_value" name="default_value" value=""  class="form-control" aria-describedby="basic-addon1">
                                </div>
                            </div>                              
                            <div class="form-group col-sm-6">
                                <label for="exampleInputEmail">Is Required</label>
                                <div class="input-group">
                                    <span class="input-group-addon addon-red"><i class="ion-android-done"></i></span>

                                    <select name="is_required" class="form-control">
                                        <option  value="1">Yes</option>
                                        <option  value="0">No</option>
                                    </select>
                                </div>                        
                            </div>
                        </div>

                        <div class="row">
                            <div class="form-group col-sm-6">
                                <label for="exampleInputEmail">Validation</label>
                                <div class="input-group ">
                                    <span class="input-group-addon addon-red"><i class="fa fa-puzzle-piece"></i></span>
                                    <input type="text" id="validation" name="validation" value=""  class="form-control" aria-describedby="basic-addon1">
                                </div>
                            </div>                              
                            <div class="form-group col-sm-6">
                                <label for="exampleInputEmail">Regexp</label>
                                <div class="input-group">
                                    <span class="input-group-addon addon-red"><i class="fa fa-newspaper-o"></i></span>
                                    <input  type="text" id="regexp" name="regexp" value=""  class="form-control" aria-describedby="basic-addon1">
                                </div>                        
                            </div>                          
                        </div>

                        <div class="row">
                            <div class="form-group col-sm-6">
                                <label for="exampleInputEmail">Lookup ID</label>
                                <div class="input-group ">
                                    <span class="input-group-addon addon-red"><i class="fa fa-ticket"></i></span>
                                    <input  type="text" id="lookup_id" name="lookup_id" value=""  class="form-control" aria-describedby="basic-addon1">
                                </div>
                            </div>                              
                            <div class="form-group col-sm-6">
                                <label for="exampleInputEmail">Attribute Type *</label>
                                <div class="input-group">
                                    <span class="input-group-addon addon-red"><i class="fa fa-code-fork"></i></span>

                                    <select name="attribute_type" class="form-control">
                                        <option  value="1">Static</option>
                                        <option  value="2">Dynamic</option>
                                        <option  value="3">Binding</option>
                                        <option  value="4">TP</option>
                                        <option  value="5">QC</option>                                            
                                    </select>
                                </div>                        
                            </div>
                        </div>



                        {{ Form::submit('Update', array('class' => 'btn btn-primary')) }}
                        {{ Form::close() }}

                    </div><!-- /.modal-content -->
                </div><!-- /.modal-dialog -->
            </div><!-- /.modal -->
        </div>

        <!-- Modal -->
        <div class="modal fade" id="basicvalCodeModal" tabindex="-1" role="dialog" aria-labelledby="basicvalCode" aria-hidden="true">
            <div class="modal-dialog wide">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                        <h4 class="modal-title" id="basicvalCode">Add Attribute</h4>
                    </div>
                    <div class="modal-body">
                        {{ Form::open(array('url' => 'product/saveattribute','id'=>'addAttribute')) }}
                        {{ Form::hidden('_method', 'POST') }}

                        <div class="row">
                            <div class="form-group col-sm-6">
                                <label for="exampleInputEmail">Attribute Set *</label>
                                <div class="input-group">
                                    <span class="input-group-addon addon-red"><i class="fa fa-cubes"></i></span>

                                    <select name="attribute_set_id" id="attribute_set_id_add_attribute" class="form-control">
                                        @foreach($attributeSetData as  $attributeSet)
                                        <option value="{{ $attributeSet->attribute_set_id}}">{{ $attributeSet->attribute_set_name}}</option>
                                        @endforeach
                                    </select>
                                </div>                        
                            </div>
                            <input type="hidden" name="manufacturer_id" id="update_manufacturer_id" value="" /> 
                            <div class="form-group col-sm-6">
                                <label for="exampleInputEmail">Attribute Group *</label>
                                <div class="input-group">
                                    <span class="input-group-addon addon-red"><i class="ion-ios-color-filter-outline"></i></span>

                                    <select name="attribute_group_id" id="attribute_group_id" class="form-control">
                                        @foreach($ag as  $ag1)
                                        <option value="{{ $ag1->attribute_group_id}}">{{ $ag1->name}}</option>
                                        @endforeach
                                    </select>
                                </div>                        
                            </div>                                
                        </div>

                        <div class="row">
                            <div class="form-group col-sm-6">
                                <label for="exampleInputEmail">Attribute Name *</label>
                                <div class="input-group ">
                                    <span class="input-group-addon addon-red"><i class="fa fa-cube"></i></span>
                                    <input type="text"  id="cname" name="name" placeholder="name" class="form-control">
                                </div>
                            </div>
                            <div class="form-group col-sm-6">
                                <label for="exampleInputEmail">Attribute Code *</label>
                                <div class="input-group ">
                                    <span class="input-group-addon addon-red"><i class="fa fa-keyboard-o"></i></span>
                                    <input type="text"  id="attribute_code" name="attribute_code" placeholder="text" class="form-control">
                                </div>
                            </div>                                                       
                        </div>

                        <div class="row">
                            <div class="form-group col-sm-6">
                                <label for="exampleInputEmail">Attribute Text</label>
                                <div class="input-group ">
                                    <span class="input-group-addon addon-red"><i class="fa fa-keyboard-o"></i></span>
                                    <input type="text"  id="ctext" name="text" placeholder="text" class="form-control">
                                </div>
                            </div>                              
                            <div class="form-group col-sm-6">
                                <label for="exampleInputEmail">Input Type *</label>
                                <div class="input-group ">
                                    <span class="input-group-addon addon-red"><i class="fa fa-shield"></i></span>
                                    <select name="input_type" id="input_type"  class="form-control">
                                        <option  value="0">Please Select ..</option>
                                        <option  value="checkbox">Check Box</option>
                                        <option  value="radio">Radio</option>
                                        <option  value="text">Text</option>
                                        <option  value="hidden">Hidden</option>
                                        <option  value="file">File</option>
                                        <option  value="inherit">Inherit</option>
                                        <option  value="label">Label</option>
                                        <option  value="toggle">Toggle</option>
                                        <option  value="textarea">Text Area</option>
                                        <option  value="date">Date</option>
                                        <option  value="datetime">Date Time</option>
                                        <option  value="select">Select Drop Down</option>
                                        <option  value="multiselect">Multi Select Drop Down</option>
                                        <option  value="sdropdown">Single Select Drop Down</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="form-group col-sm-6">
                                <label for="exampleInputEmail">Default Value</label>
                                <div class="input-group ">
                                    <span class="input-group-addon addon-red"><i class="fa fa-credit-card"></i></span>
                                    <input type="text" id="default_value" name="default_value" placeholder="default_value" class="form-control">
                                </div>
                            </div>                              
                            <div class="form-group col-sm-6">
                                <label for="exampleInputEmail">Is_Required</label>
                                <div class="input-group">
                                    <span class="input-group-addon addon-red"><i class="ion-android-done"></i></span>

                                    <select name="is_required" class="form-control">
                                        <option  value="1">Yes</option>
                                        <option  value="0">No</option>
                                    </select>
                                </div>                         
                            </div>                           
                        </div>

                        <div class="row">
                            <div class="form-group col-sm-6">
                                <label for="exampleInputEmail">Validation</label>
                                <div class="input-group ">
                                    <span class="input-group-addon addon-red"><i class="fa fa-puzzle-piece"></i></span>
                                    <input type="text" id="validation" name="validation" placeholder="validation" class="form-control">
                                </div>
                            </div>                              
                            <div class="form-group col-sm-6">
                                <label for="exampleInputEmail">Regexp</label>
                                <div class="input-group">
                                    <span class="input-group-addon addon-red"><i class="fa fa-newspaper-o"></i></span>
                                    <input type="text" id="regexp" name="regexp" placeholder="regexp" class="form-control">
                                </div>                        
                            </div>                          
                        </div>


                        <div class="row">
                            <div class="form-group col-sm-6">
                                <label for="exampleInputEmail">Lookup ID</label>
                                <div class="input-group ">
                                    <span class="input-group-addon addon-red"><i class="fa fa-ticket"></i></span>
                                    <input type="text" id="lookup_id" name="lookup_id" placeholder="lookup_id" class="form-control">
                                </div>
                            </div>                              
                            <div class="form-group col-sm-6">
                                <label for="exampleInputEmail">Attribute Type *</label>
                                <div class="input-group">
                                    <span class="input-group-addon addon-red"><i class="fa fa-code-fork"></i></span>

                                    <select name="attribute_type" id="attribute_type" class="form-control">
                                        <option  value="0">Please Select ..</option>
                                        <option  value="1">Static</option>
                                        <option  value="2">Dynamic</option>
                                        <option  value="3">Binding</option>
                                        <option  value="4">TP</option>
                                        <option  value="5">QC</option>                                              
                                    </select>
                                </div>                        
                            </div>
                        </div>

                        {{ Form::submit('Submit', array('class' => 'btn btn-primary')) }}
                        {{ Form::close() }}


                    </div><!-- /.modal-content -->
                </div><!-- /.modal-dialog -->
            </div><!-- /.modal -->

        </div>
        <button id="option-button" data-toggle="modal" data-target="#addoptions" style="display: none;"></button>
        <!-- Modal -->
        <div class="modal fade" id="addoptions" tabindex="-1" role="dialog" aria-labelledby="basicvalCode" aria-hidden="true">
            <div class="modal-dialog wide">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" id="option-close" aria-hidden="true">×</button>
                        <h4 class="modal-title" id="basicvalCode">Add options</h4>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="form-group col-sm-6">
                                <label for="exampleInputEmail">Key</label>                                  
                            </div>
                            <div class="form-group col-sm-6">
                                <label for="exampleInputEmail" >Value</label>
                                <label class="pull-right"><i class="fa fa-plus-circle" data-toggle="modal" id="add_new_option"  style="cursor: pointer; font-size:15px"></i></label>
                                <!--<div class="input-group-addon" id="add_option"></div>-->
                            </div>
                        </div>
                        <div class="row">
                            <div class="form-group col-sm-6">
                                <label for="exampleInputEmail"></label>
                                <input type="text" name="key[]" class="form-control" value="" />
                            </div>
                            <div class="form-group col-sm-4">
                                <label for="exampleInputEmail"></label>
                                <input type="text" name="value[]" class="form-control" value="" />                                        
                            </div>
                            <div class="form-group col-sm-2">
                                <label for="exampleInputEmail"></label>
                                <input type="text" name="sort_order[]" class="form-control" value="" />                 
                            </div>
                        </div>
                        <div class="row" id="option_data" style="display: none;">
                            <div class="form-group col-sm-6">
                                <label for="exampleInputEmail"></label>
                                <input type="text" name="key[]" class="form-control" value="" />
                            </div>
                            <div class="form-group col-sm-4">
                                <label for="exampleInputEmail"></label>
                                <input type="text" name="value[]" class="form-control" value="" />                                    
                            </div>
                            <div class="form-group col-sm-2">
                                <label for="exampleInputEmail"></label>
                                <input type="text" name="sort_order[]" class="form-control" value="" />                 
                                <button type="button" class="btn btn-default removeButton" onclick="removeActions($(this))" style="position: absolute; top: 17px; right: 14px;">
                                    <i class="fa fa-minus option-delete" data-toggle="modal" style="cursor: pointer;"></i>
                                </button> 
                            </div>
                        </div>                            
                        <div class="row">
                            <div class="form-group col-sm-6">
                                <button type="button" id="save-options" class="btn btn-success">Submit</button>
                            </div>                                
                        </div>
                    </div><!-- /.modal-content -->
                </div><!-- /.modal-dialog -->
            </div><!-- /.modal -->
        </div>


        <div class="modal fade" id="basicvalCodeModal3" tabindex="-1" role="dialog" aria-labelledby="basicvalCode" aria-hidden="true">
            <div class="modal-dialog wide">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                        <h4 class="modal-title" id="basicvalCode">Add Attribute Group</h4>
                    </div>
                    <div class="modal-body">
                        {{ Form::open(array('url' => 'product/saveAttributeGroup','id'=>'addAttributeGroup')) }}
                        {{ Form::hidden('_method', 'POST') }}

                        <div class="row">
                            <div class="form-group col-sm-6">
                                <label for="exampleInputEmail">Attribute Group Name *</label>
                                <div class="input-group ">
                                    <span class="input-group-addon addon-red"><i class="ion-ios-color-filter-outline"></i></span>
                                    <input type="text"  id="name" name="attribute_group[name]" placeholder="name" class="form-control">
                                </div>
                            </div>
                            <div class="form-group col-sm-6">
                                <label for="exampleInputEmail">Category Name *</label>
                                <div class="input-group ">
                                    <span class="input-group-addon addon-red"><i class="fa fa-bars"></i></span>
                                    <select name="attribute_group[category_id]" id="category_id" class="form-control selectpicker" data-live-search="true">
                                        <option value="0">Please Select...</option>
                                        @foreach($cat as  $cat1)
                                        <option value="{{ $cat1->category_id}}">{{ $cat1->name}}</option>
                                        @endforeach
                                    </select>
                                </div>  
                            </div>
                        </div>

                        <div class="row">
                            <div class="form-group col-sm-6">
                                <label for="exampleInputEmail">Manufacture Name *</label>
                                <div class="input-group ">
                                    <span class="input-group-addon addon-red"><i class="fa fa-building-o"></i></span>
                                    <input type="text" id="update_manufacturer_name" name="update_manufacturer_name" value="" class="form-control" readonly />
                                    <input type="hidden" name="attribute_group[manufacturer_id]" id="update_manufacturer_id" value="" />                                        
                                </div>
                            </div>

                        </div>
                        {{ Form::submit('Submit', array('class' => 'btn btn-primary')) }}
                        {{ Form::close() }}

                    </div>
                </div><!-- /.modal-content -->
            </div><!-- /.modal-dialog -->
        </div><!-- /.modal -->

        <div class="modal fade" id="addAttributeSet" tabindex="-1" role="dialog" aria-labelledby="basicvalCode" aria-hidden="true">
            <div class="modal-dialog wide">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                        <h4 class="modal-title" id="basicvalCode">Add Attribute Set</h4>
                    </div>
                    <div class="modal-body">
                        {{ Form::open(array('url' => '/product/saveattributeset', 'id' => 'save_attribute_set')) }}
                        {{ Form::hidden('_method', 'POST') }}

                        <div class="row">
                            <div class="form-group col-sm-6">
                                <label for="exampleInputEmail">Attribute Set Name *</label>
                                <div class="input-group ">
                                    <span class="input-group-addon addon-red"><i class="fa fa-cubes"></i></span>
                                    <input type="text"  id="name" name="attribute_set[attribute_set_name]" placeholder="name" class="form-control">
                                </div>
                            </div>
                            <div class="form-group col-sm-6">
                                <label for="exampleInputEmail">Category Name *</label>
                                <div class="input-group ">
                                    <span class="input-group-addon addon-red"><i class="fa fa-bars"></i></span>
                                    <select name="attribute_set[category_id]" id="category_id" class="form-control" >
                                        <option value="0">Please Select...</option>                                        
                                        @foreach($cat as  $cat1)
                                        <option value="{{ $cat1->category_id}}">{{ $cat1->name}}</option>
                                        @endforeach
                                    </select>
                                </div>  
                            </div>
                        </div>

                        <div class="row">
                            <div class="form-group col-sm-6">
                                <label for="exampleInputEmail">Manufacture Name *</label>
                                <div class="input-group ">
                                    <span class="input-group-addon addon-red"><i class="fa fa-building-o"></i></span>
                                    <input type="text" id="update_manufacturer_name" value="" class="form-control" name="update_manufacturer_name" readonly />
                                    <input type="hidden" name="attribute_set[manufacturer_id]" id="update_manufacturer_id" value="" />                                        

                                </div>
                            </div>
                            <!--                                 <div class="form-group col-sm-6">
                                                                <label for="exampleInputEmail"></label>
                                                                <div class="checkbox">
                                                                    <input type="checkbox" value="1" id="opt01" name="attribute_set[inherit_from]" parsley-group="mygroup" parsley-trigger="change" parsley-required="true" parsley-mincheck="2" parsley-error-container="#myproperlabel .last" class="parsley-validated">
                                                                    <label for="opt01">Inherit all attributes from <b>Default</b> attribute set</label>
                                                                </div>
                                                            </div> -->
                            <input type="hidden" name="attribute_set[is_active]" value="1" />
                        </div>
                        <!--added for Pulling and adding-->
                        <div class="row">
                            <div id="fieldChooser" tabIndex="1">
                                <div class="form-group col-sm-6">
                                    <label for="exampleInputEmail">Select Attributes</label>
                                    <a href="#" data-toggle="modal" data-target="#wizardCodeModal" data-placement="right" title="Add New Attribute!"><!-- <i class="fa fa-user-plus"></i> --></a>

                                    <div id="selectbox" >
                                        <div id="Selectattribute"></div>
                                    </div>
                                </div>
                                <div class="form-group col-sm-6">
                                    <label for="exampleInputEmail" >Selected Attributes</label>  
                                    <div id="attribute_id" name="attribute_id[]"></div>
                                </div>
                            </div>
                        </div>
                        <!--added for Pulling and adding-->

                        <!--input type="button" class="btn btn-primary" name="Submit" id="saveAttributeSet" value="Submit" /-->
                        {{ Form::submit('Submit', array('class' => 'btn btn-primary', 'id' => 'save_attribute_set_button')) }}
                        {{ Form::close() }}

                    </div>
                </div><!-- /.modal-content -->
            </div><!-- /.modal-dialog -->
        </div><!-- /.modal -->


        <div class="modal fade" id="basicvalCodeModal4" tabindex="-1" role="dialog" aria-labelledby="basicvalCode" aria-hidden="true">
            <div class="modal-dialog wide">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                        <h4 class="modal-title" id="basicvalCode">Edit Attribute Group</h4>
                    </div>
                    <div class="modal-body">
                        {{ Form::open(array('url' => '/product/updateAttributeGroup', 'data-url' => '/product/updateAttributeGroup/','id'=>'editAttributeGroup')) }} 
                        {{ Form::hidden('_method', 'PUT') }}



                        <div class="row">
                            <div class="form-group col-sm-6">
                                <label for="exampleInputEmail">Attribute Group Name</label>
                                <div class="input-group ">
                                    <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
                                    <input type="text"  id="name" name="name" value="" class="form-control" aria-describedby="basic-addon1">
                                </div>
                            </div>
                            <div class="form-group col-sm-6">
                                <label for="exampleInputEmail">Category Name</label>
                                <div class="input-group ">
                                    <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
                                   <!--  <input type="number" id="attribute_id" name="attribute_id" value="" class="form-control" aria-describedby="basic-addon1"> -->
                                    <select name="category_id" id="category_id" class="form-control">
                                        <option value="0">Please Select...</option>                                        
                                        @foreach($cat as  $cat1)
                                        <option value="{{ $cat1->category_id}}">{{ $cat1->name}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>



                        <div class="row">
                            <div class="form-group col-sm-6">
                                <label for="exampleInputEmail">Manufacturer Name</label>
                                <div class="input-group ">
                                    <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
                                    <!-- <input type="text"  id="name" name="name" value="" class="form-control" aria-describedby="basic-addon1"> -->
                                    <input type="text" id="update_manufacturer_name" value="" class="form-control" readonly />
                                    <input type="hidden" name="customer_id" id="update_manufacturer_id" value="" />
                                </div>
                            </div>
                            <div class="form-group col-sm-6">
                                <button class="btn btn-primary" onclick="exportXls()">Export to xls</button>
                            </div>
                        </div>



                        {{ Form::submit('Update', array('class' => 'btn btn-primary')) }}
                        {{ Form::close() }}
                    </div>
                </div><!-- /.modal-content -->
            </div><!-- /.modal-dialog -->
        </div><!-- /.modal -->

        <div class="modal fade" id="editAttributeSet" tabindex="-1" role="dialog" aria-labelledby="basicvalCode" aria-hidden="true">
            <div class="modal-dialog wide">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                        <h4 class="modal-title" id="basicvalCode">Edit Attribute Set</h4>
                    </div>
                    <div class="modal-body">
                        {{ Form::open(array('url' => '/product/updateattributeset', 'data-url' => '/product/updateattributeset/','id'=>'editAttributeset')) }} 
                        {{ Form::hidden('_method', 'POST') }}
                        <div class="row">
                            <div class="form-group col-sm-6">
                                <label for="exampleInputEmail">Attribute Set Name *</label>
                                <div class="input-group ">
                                    <span class="input-group-addon addon-red"><i class="fa fa-cubes"></i></span>
                                    <input type="text"  id="name" name="attribute_set_name" placeholder="name" class="form-control">
                                    <input type="hidden" name="attribute_set_id" id="attribute_set_id" value="" /> 
                                </div>
                            </div>
                            <div class="form-group col-sm-6">
                                <label for="exampleInputEmail">Category Name *</label>
                                <div class="input-group ">
                                    <span class="input-group-addon addon-red"><i class="fa fa-bars"></i></span>
                                    <select name="category_id" id="category_id1" class="form-control">
                                        <option value="0">Please Select...</option>
                                        @foreach($cat as  $cat1)
                                        <option value="{{ $cat1->category_id}}">{{ $cat1->name}}</option>
                                        @endforeach
                                    </select>
                                </div>  
                            </div>
                        </div>

                        <div class="row">
                            <div class="form-group col-sm-6">
                                <label for="exampleInputEmail">Manufacture Name</label>
                                <div class="input-group ">
                                    <span class="input-group-addon addon-red"><i class="fa fa-building-o"></i></span>
                                    <input type="text" id="update_manufacturer_name" value="" class="form-control" readonly />
                                    <input type="hidden" name="attribute_set[manufacturer_id]" id="update_manufacturer_id" value="" />
                                </div>
                            </div>
                            <input type="hidden" name="attribute_set[is_active]" value="1" />
                        </div>
                        <!--added for Pulling and adding-->
                        <div class="row">
                            <div id="fieldChooser" tabIndex="1">
                                <div class="form-group col-sm-6">
                                    <label for="exampleInputEmail">Select Attributes</label>
                                    <a href="#" data-toggle="modal" data-target="#wizardCodeModal" data-placement="right" title="Add New Attribute!"><!-- <i class="fa fa-user-plus"></i> --></a>
                                    <div id="selectbox">
                                        <input type="hidden" name="formattributes" id="formattributes1" value="0" />
                                        <div id="Selectattribute1" name="attributes"></div>
                                    </div>
                                </div>
                                <div class="form-group col-sm-6">
                                    <label for="exampleInputEmail" >Selected Attributes</label>  
                                    <div id="attribute_id1" name="attribute_id[]"></div>
                                </div>
                            </div>
                        </div>
                        <!--added for Pulling and adding-->

                        {{ Form::submit('Update', array('class' => 'btn btn-primary', 'id' => 'update_attribute_set_button')) }}
                        {{ Form::close() }}
                    </div>
                </div><!-- /.modal-content -->
            </div><!-- /.modal-dialog -->
        </div><!-- /.modal -->

        <div class="modal fade" id="assignAttributeSet" tabindex="-1" role="dialog" aria-labelledby="basicvalCode" aria-hidden="true">
            <div class="modal-dialog wide">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                        <h4 class="modal-title" id="basicvalCode">Edit Attribute Set</h4>
                    </div>
                    <div class="modal-body">
                        {{ Form::open(array('url' => '/product/assigngroups', 'id'=>'assignGroupsLocations')) }} 
                        {{ Form::hidden('_method', 'POST') }}
                        <div class="row">
                            <div class="form-group col-sm-6">
                                <label for="exampleInputEmail">Attribute Set Name *</label>
                                <div class="input-group ">
                                    <span class="input-group-addon addon-red"><i class="fa fa-cubes"></i></span>
                                    <input type="text"  id="assign_attribute_set_name" name="attribute_set_name" placeholder="name" class="form-control" readonly>
                                    <input type="hidden" name="attribute_set_id" id="assign_attribute_set_id" value="" /> 
                                </div>
                            </div>
                            <div class="form-group col-sm-6">
                                <label for="exampleInputEmail">Manufacture Name</label>
                                <div class="input-group ">
                                    <span class="input-group-addon addon-red"><i class="fa fa-building-o"></i></span>
                                    <input type="text" id="update_manufacturer_name" value="" class="form-control" readonly />
                                    <input type="hidden" name="attribute_set[manufacturer_id]" id="update_manufacturer_id" value="" />
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="form-group col-sm-6">
                                <label for="exampleInputEmail">Product Groups</label>
                                <div class="input-group ">
                                    <span class="input-group-addon addon-red"><i class="fa fa-bars"></i></span>
                                    <select name="product_groups" id="product_groups" class="form-control">                                            
                                    </select>
                                </div>  
                            </div>
                            <div class="form-group col-sm-4">
                                <label for="exampleInputEmail">Locations</label>
                                <div class="input-group ">
                                    <span class="input-group-addon addon-red"><i class="fa fa-bars"></i></span>
                                    <select name="locations" id="locations" class="form-control selectpicker" data-live-search="true">                                            
                                    </select>
                                </div>
                            </div>
                            <div class="form-group col-sm-2">
                                <label for="exampleInputEmail"></label>
                                <div class="input-group ">
                                    <div class="input-group-addon">
                                        <i class="fa fa-plus" id="add_assign" style="cursor: pointer;"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <section class="tile">
                                <div class="panel panel-default">
                                    <!-- Default panel contents -->
                                    <div class="panel-heading">Location details</div>
                                    <!-- Table -->
                                    <table class="table" id="assign_data">
                                        <thead>
                                            <tr>
                                                <th>Product Group</th>
                                                <th>Location</th>
                                                <th style="width: 30px;">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody id="assigntable">
                                        </tbody>
                                    </table>
                                </div>
                            </section>
                        </div>
                        {{ Form::button('Save', array('class' => 'btn btn-primary', 'id' => 'update_assign_attribute_set_button')) }}
                        {{ Form::close() }}
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

    </div>
    <script type="text/javascript">
        function removeActions($option)
        {
            $option.parent().parent().remove();
            //$option.closest('div #option_data');
            //console.log($option);
        }
        $('#save-options').click(function () {
            var keyData = [];
            var valueData = [];
            var sortOrderData = [];
            $('[name="key[]"]').each(function (elem) {
                if ( $(this).val() != '' )
                {
                    keyData.push($(this).val());
                }
            });
            $('[name="value[]"]').each(function (elem) {
                if ( $(this).val() != '' )
                {
                    valueData.push($(this).val());
                }
            });
            $('[name="sort_order[]"]').each(function (elem) {
                if ( $(this).val() != '' )
                {
                    sortOrderData.push($(this).val());
                }
            });
            var responseData = {};
            for (var i = 0, len = keyData.length; i < len; i++) {
                var keyValue = '';
                var dataValue = '';
                var sortValue = 0;
                if ( keyData[i] != '' || keyData[i] != 'undefined' )
                {
                    keyValue = keyData[i];
                }
                if ( valueData[i] != '' || valueData[i] != 'undefined' )
                {
                    dataValue = valueData[i];
                }
                if ( sortOrderData[i] != '' || sortOrderData[i] != 'undefined' )
                {
                    sortValue = sortOrderData[i];
                }
                responseData[i] = keyValue + ';' + dataValue + ';' + sortValue;
            }
            var modelId = $('[class="modal fade in"').attr('id');
            $('#' + modelId).find('[name="option_values"]').val(JSON.stringify(responseData));
            $('#option-close').trigger('click');
        });
        $('#add_assign').click(function(){
            var product_groups_val = $('#product_groups').val();
            var product_groups_text = $('#product_groups option:selected').text();
            var location_val = $('#locations').val();
            var location_text = $('#locations option:selected').text();
            
            if(product_groups_val == 0 || product_groups_val == '')
            {
                alert('Please select attribtue set.');
            }else if(location_val == 0 || location_val == '')
            {
                alert('Please select locations.');
            }else{
                var attributeSetElements = new Array();
                $('[id="product_groups_text"]').each(function(){
                    attributeSetElements.push($(this).text()+'##'+$(this).next('td#location_text').text());
                });
                var temp;
                temp = product_groups_text+'##'+location_text;
                if(attributeSetElements.length > 0 && $.inArray(temp, attributeSetElements) >= 0)
                {
                    alert('This element already added.');
                }else{            
                    var jsonArg = new Object();
                    jsonArg.product_group = product_groups_val;
                    jsonArg.location_val = location_val;
                    var hiddenJsonData = new Array();
                    hiddenJsonData.push(jsonArg);

                    $("#assign_data").append('<tr><td scope="row" id="product_groups_text">' + product_groups_text + '</td><td id="location_text">' + location_text
                            + '</td><td><a href="javascript:void(0);" class="check-toggler" id="remCF"><span class="badge bg-red"><i class="fa fa-trash-o"></i></span></a><input type="hidden" name="assign_locations[]" value=' + "'" + JSON.stringify(jsonArg) + "'" + ' /></td></tr>');
                }
            }
        });
        $("#assign_data").on('click', '#remCF', function () {
            $(this).parent().parent().remove();
        });
        function exportexcel(){
            if($('#main_manufacturer_id').val()!=0 && $('#main_manufacturer_id').val()!='' ){
                location.href="/product/getallattributesexport/"+$('#main_manufacturer_id').val();

            }
        }
    </script>
    @stop            