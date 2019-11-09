/*
 * jQuery File Upload Demo
 * https://github.com/blueimp/jQuery-File-Upload
 *
 * Copyright 2010, Sebastian Tschan
 * https://blueimp.net
 *
 * Licensed under the MIT license:
 * https://opensource.org/licenses/MIT
 */

/* global $ */

$(function () {
    'use strict';

    // Initialize the jQuery File Upload widget:
    $('#fileupload').fileupload({
        // Uncomment the following to send cross-domain cookies:
        //xhrFields: {withCredentials: true},
        url: '/INOVACMS/files/create/'+ $('#fileupload').data("model") +'/'+$('#fileupload').data("id"),
        url_all:'/INOVACMS/files/'+ $('#fileupload').data("model") +'/'+$('#fileupload').data("id"),
    });

    // Enable iframe cross-domain access via redirect option:
   // $('#fileupload').fileupload(
//        'option',
  //      'redirect',
//        window.location.href.replace(/\/[^/]*$/, '/cors/result.html?%s')
  //  );
    // Load existing files:
    $('#fileupload').addClass('fileupload-processing');
    $.ajax({
            // Uncomment the following to send cross-domain cookies:
            //xhrFields: {withCredentials: true},
            url: $('#fileupload').fileupload('option', 'url_all'),
            dataType: 'json',
            method: 'post',
            data : {"model" : $('#fileupload').data("model"), "id":$('#fileupload').data("id")},
            context: $('#fileupload')[0]
        })
        .always(function () {
            $(this).removeClass('fileupload-processing');
        })
        .done(function (result) {
            $(this)
                .fileupload('option', 'done')
                // eslint-disable-next-line new-cap
                .call(this, $.Event('done'), {
                    result: result
                });
        });

});
