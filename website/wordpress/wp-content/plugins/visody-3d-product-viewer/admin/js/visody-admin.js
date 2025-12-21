(function($) {

    var settings = {
        setting_visody_model: null,
        setting_visody_model_id: null,
    }

    if ( $('.visody_inline_viewer_field input:checked').length ) {
        $('.visody_inline_viewer_last_slide_field').show();
    } else {
        $('.visody_inline_viewer_last_slide_field').hide();
    }

    $('.visody_inline_viewer_field input').on('change', function() {
        if ( $(this).is(':checked') ) {
            $('.visody_inline_viewer_last_slide_field').show();
        } else {
            $('.visody_inline_viewer_last_slide_field').hide();
        }
    });

    if ( $('.visody_inline_shortcode_viewer_field input:checked').length ) {
        $('.visody_viewer_frame_ratio_field').show();
        $('.visody_viewer_frame_ratio_mobile_field').show();
    } else {
        $('.visody_viewer_frame_ratio_field').hide();
        $('.visody_viewer_frame_ratio_mobile_field').hide();
    }

    $('.visody_inline_shortcode_viewer_field input').on('change', function() {
        if ( $(this).is(':checked') ) {
            $('.visody_viewer_frame_ratio_field').show();
            $('.visody_viewer_frame_ratio_mobile_field').show();
        } else {
            $('.visody_viewer_frame_ratio_field').hide();
            $('.visody_viewer_frame_ratio_mobile_field').hide();
        }
    });

    function visody_handle_model_upload(event) {
        var $button = $( this ),
            post_id = $button.attr( 'rel' ),
            $parent = $button.closest( '.vsd-file-upload' );

        settings.setting_visody_model    = $parent;
        settings.setting_visody_model_id = post_id;

        event.preventDefault();
        
        if ( $parent.hasClass('remove')) {
            
            $( '.vsd-file-upload-field', settings.setting_visody_model ).val( '' ).trigger( 'change' );

            settings.setting_visody_model.val( '' ).trigger( 'change' );
			settings.setting_visody_model.find( 'img' ).eq( 0 ).attr( 'src', '' );
			settings.setting_visody_model.find( '.action-name' ).text('Set ' + settings.setting_visody_model.find('label').text());
			settings.setting_visody_model.find( '.details-title' ).text('');
			// settings.setting_visody_model.find( '.details-name' ).text('');
			settings.setting_visody_model.find( '.details-size' ).text('');

            settings.setting_visody_model.removeClass( 'remove' );

        } else {
            
            // If the media frame already exists, reopen it.
            if ( settings.setting_visody_model_frame ) {
                settings.setting_visody_model_frame.uploader.uploader.param( 'post_id', settings.setting_visody_model_id );
                settings.setting_visody_model_frame.open();
                return;
            } else {
                // wp.media.model.settings.post.id = settings.setting_visody_model_id;
            }

            // Create the media frame.
            settings.setting_visody_model_frame = wp.media.frames.variable_image = wp.media({
                // Set the title of the modal.
                title: 'Choose ' + settings.setting_visody_model.find('label').text(),
                button: {
                    text: 'Set ' + settings.setting_visody_model.find('label').text()
                }
            });

            // When an image is selected, run a callback.
            settings.setting_visody_model_frame.on( 'select', function () {
                var attachment = settings.setting_visody_model_frame.state().get( 'selection' ).first().toJSON(),
                    url = attachment.sizes && attachment.sizes.thumbnail.url ? attachment.sizes.thumbnail.url : attachment.icon;

                if ( ! attachment.filename.includes('.gltf') && ! attachment.filename.includes('.glb') ) {
                    if ( ! settings.setting_visody_model.find('.error').length ) {
                        settings.setting_visody_model.append( '<p class="error">Please select GLB or glTF file.</p>' );
                    }
                    return;
                }

                $( '.vsd-file-upload-field', settings.setting_visody_model ).val( attachment.id ).trigger( 'change' );

                settings.setting_visody_model.find( 'img' ).eq( 0 ).attr( 'src', url );

                settings.setting_visody_model.find( '.action-name' ).text('Remove ' + settings.setting_visody_model.find('label').text());
                settings.setting_visody_model.find( '.details-title' ).text(attachment.filename);
                // settings.setting_visody_model.find( '.details-name' ).text(attachment.filename);
                settings.setting_visody_model.find( '.details-size' ).text(attachment.filesizeHumanReadable);

                if ( settings.setting_visody_model.find('.error').length ) {
                    settings.setting_visody_model.find('.error').remove();
                }

                settings.setting_visody_model.addClass( 'remove' );

                // wp.media.model.settings.post.id = settings.wp_media_post_id;
            });

            // Finally, open the modal.
            settings.setting_visody_model_frame.open();
        }
    }

    $(document).on('click', '.vsd-file-upload-button', visody_handle_model_upload);

})(jQuery)