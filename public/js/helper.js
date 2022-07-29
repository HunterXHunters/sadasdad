function makePopupAjax($el) {
    var $form = $el.find('form');
    $form.validate();
}

function makePopupEditAjax($el, primaryKey) {
    $el.on('shown.bs.modal', function (e) {
        var url = $(e.relatedTarget).data('href'),
                $this = $(this),
                $form = $this.find('form'),
                key = primaryKey || 'id';        
        $.get(url, function (data) {
            $.each(data, function (i, v) {
                if ( i == key ) {
                    $form.attr('action', function () {
                        return $(this).data('url') + v;
                    });
                }
                var el = $form.find('[name="' + i + '"]');
                if ( el.length && el[0].type.toLowerCase() == 'checkbox' ) {
                    el.prop('checked', false);
                    el.filter('[value=' + v + ']').prop('checked', true);
                    return;
                }
                el.val(v);
            });            
        });
        $form.validate();
    });
}
function makePopupEditAjaxNew($el, primaryKey,otherKey) {
    $el.on('shown.bs.modal', function (e) {
        var url = $(e.relatedTarget).data('href'),
                $this = $(this),
                $form = $this.find('form'),
                key = primaryKey || 'id';
                
        $.get(url, function (data) {
            $.each(data, function (i, v) {
                if ( i == key ) {
                    $form.attr('action', function () {
                        
                        return $(this).data('url') + v;
                    });
                }
                if ( i == otherKey ) {
                    var url1 = $form.attr('action');
                    $form.attr('action' , url1+'/'+v);
                }
                var el = $form.find('[name="' + i + '"]');
                if ( el.length && el[0].type.toLowerCase() == 'checkbox' ) {
                    el.prop('checked', false);
                    el.filter('[value=' + v + ']').prop('checked', true);
                    return;
                }
                el.val(v);
            });            
        });
        $form.validate();
    });
}
function ajaxCallPopup($form) {
    $.post($form.attr('action'), $form.serialize(), function (data) {        
        if ( data.status === true ) {
            $form.closest('.modal').modal('hide');
            $form[0].reset();
            if ( $('.jqxgrid').lenth && $.fn.jqxGrid )
                $('.jqxgrid').jqxGrid('refresh');
            alert('' + data.message);
            postData(data);
        } else {
            alert('' + data.message);
        }
    });
}

function postData(data)
{
    console.log('we are in helper.js');
    location.reload();
}

$(function () {
    $.validator.setDefaults({
        onfocusout: function (element) {
            $(element).valid();
        },
        submitHandler: function (form) {
            var $form = $(form);
            ajaxCallPopup($form);
        },
        errorPlacement: function (error, element) {
            element.closest('.form-group').append(error);
        },
        unhighlight: function (element, errorClass, validClass) {
            if ( $(element).hasClass('optional') && $(element).val() == '' ) {
                $(element).removeClass('error valid');
            } else {
                $(element).removeClass('error').addClass('valid');
            }
        }
    });
});