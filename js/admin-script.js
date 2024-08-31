jQuery(document).ready(function($) {
    var mediaUploader;

    $('#upload_logo_button').click(function(e) {
        e.preventDefault();
        if (mediaUploader) {
            mediaUploader.open();
            return;
        }

        // Create the media frame.
        mediaUploader = wp.media.frames.file_frame = wp.media({
            title: 'Choose Logo',
            button: {
                text: 'Choose Logo'
            },
            multiple: false,
            library: {
                type: 'image' // Filter by images only
            }
        });

        // Set the filter to only show JPEG images
        mediaUploader.on('open', function() {
            var filter = mediaUploader.state().get('library');
            filter.props.set({ type: 'image/jpeg' });
        });

        mediaUploader.on('select', function() {
            var attachment = mediaUploader.state().get('selection').first().toJSON();

            // Check if the selected file is a JPEG
            if (attachment.mime !== 'image/jpeg') {
                alert('Please select a JPEG image.');
                return;
            }

            $('#exec_dev_office_suite_logo').val(attachment.url);
            $('#logo_preview').html('<img src="' + attachment.url + '" style="max-width: 150px;">');
        });

        mediaUploader.open();
    });
});
