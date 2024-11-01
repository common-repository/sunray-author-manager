/* jQuery helper functions for Sunray Author Manager plugin */
jQuery(document).ready(function() {

    //console.log('sam admin scripts loaded')

    // add datepicker element to any datepicker class items
    jQuery('.sam_pub_date').datepicker({
        dateFormat : 'MM dd, yy'
    });

    var pub_date = '';
    jQuery(".sam_pub_date_forthcoming").click( function(){
        var date_input = jQuery('.sam_pub_date');
        if(jQuery(this).is(':checked')) {
            pub_date = date_input.val();
            date_input.val('Forthcoming')
            date_input.prop('disabled', true);
        } else {
            date_input.val(pub_date);
            date_input.prop('disabled', false)
        }
    });

    // Uploading files
    var file_frame;

    jQuery('.sam_cover_image_button').on('click', function( e ){

        e.preventDefault();

        // If the media frame already exists, reopen it.
        if ( file_frame ) {
            // Open frame
            file_frame.open();
            return;
        }

        // Create the media frame.
        file_frame = wp.media.frames.file_frame = wp.media({
            title: 'Select an image to upload',
            button: {
                text: 'Use this image',
            },
            multiple: false	// Set to true to allow multiple files to be selected
        });

        // When an image is selected, run a callback.
        file_frame.on( 'select', function() {
            // We set multiple to false so only get one image from the uploader
            attachment = file_frame.state().get('selection').first().toJSON();

            // Do something with attachment.id and/or attachment.url here
            jQuery('.sam_cover_image').val(attachment.url);
            jQuery(".sam_cover_image_preview").attr("src",attachment.url);

        });

        // Finally, open the modal
        file_frame.open();
    });


    // on title change
    if(jQuery('.sam_title').length > 0) {
        jQuery('#title').on('change keyup', function () {
            jQuery('.sam_title').val(jQuery(this).val());

        });
    }




    // on reprint change

    show_hide_reprint_select();

    jQuery(document).on('change','.sam_reprint',function(e) {
        e.preventDefault();
        show_hide_reprint_select();

    });

    function show_hide_reprint_select() {
        var reprint_id = jQuery('.sam_reprint').val();
        if(reprint_id == 1) {
            jQuery('.sam_reprint_id_meta').fadeIn();
            jQuery('.select2-container').css('width','100%');
        } else {
            jQuery('.sam_reprint_id_meta').fadeOut();
        }
    }

});