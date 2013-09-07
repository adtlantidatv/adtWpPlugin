jQuery(document).ready(function($) {

	jQuery(".knob").knob({});

    var WPUF_Attachment = {
        init: function () {
            window.wpufFileCount = typeof window.wpufFileCount == 'undefined' ? 0 : window.wpufFileCount;
            this.maxFiles = parseInt(wpuf_attachment.number);

            $('#wpuf-attachment-upload-filelist').on('click', 'a.track-delete', this.removeTrack);
            $('#wpuf-attachment-upload-filelist ul.wpuf-attachment-list').sortable({
                cursor: 'crosshair',
                handle: '.handle'
            });

            this.attachUploader();
            this.hideUploadBtn();
        },
        hideUploadBtn: function () {

            if(WPUF_Attachment.maxFiles !== 0 && window.wpufFileCount >= WPUF_Attachment.maxFiles) {
                //$('#wpuf-attachment-upload-pickfiles').hide();
            }
        },
        attachUploader: function() {
            if(typeof plupload === 'undefined') {
                return;
            }

            if(wpuf_attachment.attachment_enabled !== '1') {
                return
            }

            var attachUploader = new plupload.Uploader(wpuf_attachment.plupload);

            $('#wpuf-attachment-upload-pickfiles').click(function(e) {
                attachUploader.start();
                e.preventDefault();
            });

            attachUploader.init();

            attachUploader.bind('FilesAdded', function(up, files) {
            	$('#wpuf-attachment-upload-pickfiles').append('<span id="up_percentage_txt"></span>');
            	$('#wpuf-attachment-upload-pickfiles i').fadeOut();
            	$('#adt_menu').addClass('animation_spin');

                up.refresh(); // Reposition Flash/Silverlight
                attachUploader.start();
            });

            attachUploader.bind('UploadProgress', function(up, file) {
            	$('#wpuf-attachment-upload-pickfiles #up_percentage_txt').html(file.percent + "%");
                $('#' + file.id + " b").html(file.percent + "%");
                $('.progress .bar').css('width', (file.percent)+'%');
                console.log(file.percent);
                jQuery('.knob').val(file.percent).trigger("change");
            });

            attachUploader.bind('Error', function(up, err) {
                $('#wpuf-attachment-upload-filelist').append("<div>Error: " + err.code +
                    ", Message: " + err.message +
                    (err.file ? ", File: " + err.file.name : "") +
                    "</div>"
                    );

                up.refresh(); // Reposition Flash/Silverlight
            });

            attachUploader.bind('FileUploaded', function(up, file, response) {
            	console.log('FileUploaded');
            	$('#wpuf-attachment-upload-pickfiles span').fadeOut(function(){
            		$(this).parent().html('<span>done!</span>');
	            	$(this).remove();
            	});
                var resp = $.parseJSON(response.response);
                $('#' + file.id).remove();
                //console.log(resp);
                if( resp.success ) {
                    window.wpufFileCount += 1;
                    $('#wpuf-attachment-upload-filelist').append(resp.html);
                    $('.form_01 input[type=submit]').fadeIn();
                    WPUF_Attachment.hideUploadBtn();
                }
            	$('#adt_menu').removeClass('animation_spin');
            });
        },
        removeTrack: function(e) {
            e.preventDefault();

            if(confirm(wpuf.confirmMsg)) {
                var el = $(this),
                data = {
                    'attach_id' : el.data('attach_id'),
                    'nonce' : wpuf_attachment.nonce,
                    'action' : 'wpuf_attach_del'
                };

                $.post(wpuf.ajaxurl, data, function(){
                   $('#wpuf-attachment-upload-filelist').html('');

                    window.wpufFileCount -= 1;
                    if(WPUF_Attachment.maxFiles !== 0 && window.wpufFileCount < WPUF_Attachment.maxFiles ) {
                        //$('#wpuf-attachment-upload-pickfiles').show();

			        	$('#wpuf-attachment-upload-pickfiles span').fadeOut(function(){
			        		$(this).parent().html('<i class="icon-plus"></i>');
			            	$(this).remove();
			        	});                        
                    }
                });
            }
        }
    };

    //run the bootstrap
    WPUF_Attachment.init();

});