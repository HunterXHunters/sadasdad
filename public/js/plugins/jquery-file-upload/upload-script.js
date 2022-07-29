$(function () {
    'use strict';
    // Change this to the location of your server-side upload handler:
    var url = window.location.hostname === 'blueimp.github.io' ?
                '//jquery-file-upload.appspot.com/' : '/product/uploadhandler',
        uploadButton = $('<button/>')
            .addClass('btn btn-primary')
            .attr('type', 'button') 
            .prop('disabled', true)
            .text('Processing...')
            .on('click', function (event) {                
                event.preventDefault;
                var $this = $(this),
                    data = $this.data();
                $this
                    .off('click')
                    .text('Abort')
                    .on('click', function () {
                        $this.preventDefault;
                        $this.remove();
                        data.abort();
                    });
                data.submit().always(function () {
                    $this.remove();
                });
            });
    $('#fileupload').fileupload({        
        url: url,
        dataType: 'json',
        autoUpload: true,
        acceptFileTypes: /(\.|\/)(gif|jpe?g|png)$/i,
        maxFileSize: 999000,
        // Enable image resizing, except for Android and Opera,
        // which actually support image resizing, but fail to
        // send Blob objects via XHR requests:
        disableImageResize: /Android(?!.*Chrome)|Opera/
            .test(window.navigator.userAgent),
        previewMaxWidth: 100,
        previewMaxHeight: 100,
        previewCrop: false
    }).on('fileuploadadd', function (e, data) {
        console.log('fileuploadadd');
        var temp = data;
        data.context = $('<tr class="template-upload"/>').appendTo('#files');
        $.each(data.files, function (index, file) {
            var node = '';
            var spanContent = $('<span/>').text(file.name);
            var spanInput = '';
            spanInput = $('<span id="spanInput"/>');
            var node = $('<td class="preview"/>').append(spanContent).append(spanInput);
            //var tempButton = uploadButton.clone(true).data(data);
            //node.append('<br/>');
            //node.append(tempButton);
//            //var node2 = $('<td/>').append($('<span/>').text(file.name));
//            if (!index) {
//                console.log('in if');
//                if(file.size)
//                {
//                    console.log('file sixe');
//                    var tempButton = uploadButton.clone(true).data(data);
//                    if(file.size < 1024)
//                        var node3 = $('<td/>').append($('<span/>').text(file.size+' B'));
//                    else
//                        var node3 = $('<td/>').append($('<span/>').text(Math.round((file.size/1024), 2)+' KB'));
//                    var tempTd = $('<td class="button"/>').append(tempButton);
//                    var node4 = tempTd;
//                }else{
//                    console.log('no file size');
//                    var tempButton = uploadButton.clone(true).data(data);
//                    var node3 = $('<td/>').append($('<span/>').text(file.size));
//                    var tempTd = $('<td class="button"/>').append(tempButton);
//                    var node4 = tempTd;
//                }
//            }
            node.appendTo(data.context);
            //node2.appendTo(data.context);
            //node3.appendTo(data.context);
            //node4.appendTo(data.context);
        });
        var node = '';
        data = temp;
    }).on('fileuploadprocessalways', function (e, data) {
        console.log('fileuploadprocessalways');
        var node = '';
        var index = data.index,
            file = data.files[index],
            node = $(data.context.children()[index]);
        if (file.preview) {
            node
                .prepend('<br>')
                .prepend(file.preview);
        }
        if (file.error) {
            node
                .append('<br>')
                .append($('<span class="text-danger"/>').text(file.error));
        }
        if (index + 1 === data.files.length) {
            data.context.find('button')
                .text('Upload')
                .prop('disabled', !!data.files.error);
        }
    }).on('fileuploadprogressall', function (e, data) {
        console.log('fileuploadprogressall');
        var progress = parseInt(data.loaded / data.total * 100, 10);
        $('#progress .progress-bar').css(
            'width',
            progress + '%'
        );
    }).on('fileuploaddone', function (e, data) {
        console.log('fileuploaddone');
        $.each(data.result.files, function (index, file) {
            if (file.url) {
                var link = $('<a>')
                    .attr('target', '_blank')
                    .prop('href', file.url);
                $(data.context.children('td.preview').children()[index])
                    .wrap(link);
            var inputData = $('<input/>').attr('type', 'hidden').attr('name', 'media[image][]').val(file.url);            
            var container = $(data.context.children('td.preview').children('span#spanInput')[index]);
            container.append('<br/>');
            $('<input />', { type: 'radio', id: 'is_default', name: 'media[is_default]', value: file.url }).appendTo(container);
            $('<label />', { 'for': 'is_default', text: 'Is default' }).appendTo(container);
            $('#fileupload').removeAttr('required');
            $(data.context.children()[index]).append(inputData);            
            } else if (file.error) {
                var error = $('<span class="text-danger"/>').text(file.error);
                $(data.context.children()[index])
                    .append('<br>')
                    .append(error);
            }
        });
    }).on('fileuploadfail', function (e, data) {
        console.log('fileuploadfail');
        $.each(data.files, function (index) {
            var error = $('<span class="text-danger"/>').text('File upload failed.');
            $(data.context.children()[index])
                .append('<br>')
                .append(error);
        });
    }).prop('disabled', !$.support.fileInput)
        .parent().addClass($.support.fileInput ? undefined : 'disabled');
});