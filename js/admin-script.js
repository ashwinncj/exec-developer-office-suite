jQuery(document).ready(function($) {
    var mediaUploader;

    $('#upload_logo_button').click(function(e) {
        e.preventDefault();
        if (mediaUploader) {
            mediaUploader.open();
            return;
        }
        mediaUploader = wp.media.frames.file_frame = wp.media({
            title: 'Choose Logo',
            button: {
                text: 'Choose Logo'
            },
            multiple: false
        });

        mediaUploader.on('select', function() {
            var attachment = mediaUploader.state().get('selection').first().toJSON();
            $('#exec_dev_office_suite_logo').val(attachment.url);
            $('#logo_preview').html('<img src="' + attachment.url + '" style="max-width: 150px;">');
        });

        mediaUploader.open();
    });
});
