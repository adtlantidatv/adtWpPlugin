<?php
/**
 * Settings Sections
 *
 * @since 1.0
 * @return array
 */
function adtp_settings_sections() {
    $sections = array(
        array(
            'id' => 'adtp_frontend_posting',
            'title' => __( 'Frontend Posting', 'adtp' )
        ),
        array(
            'id' => 'adtp_others',
            'title' => __( 'Others', 'adtp' )
        ),
        array(
            'id' => 'adtp_server',
            'title' => __( 'Server', 'adtp' )
        ),
    );

    return apply_filters( 'adtp_settings_sections', $sections );
}

function adtp_settings_fields() {
    $users = adtp_list_users();
    $pages = adtp_get_pages();
    
    $settings_fields = array(
        'adtp_frontend_posting' => apply_filters( 'adtp_options_frontend', array(
            array(
                'name' => 'enable_featured_image',
                'label' => __( 'Featured Image upload', 'adtp' ),
                'desc' => __( 'Gives ability to upload an image as featured image', 'adtp' ),
                'type' => 'radio',
                'default' => 'no',
                'options' => array(
                    'yes' => __( 'Enable', 'adtp' ),
                    'no' => __( 'Disable', 'adtp' )
                )
            ),
            array(
                'name' => 'attachment_num',
                'label' => __( 'Number of attachments', 'adtp' ),
                'desc' => __( 'How many attachments can be attached on a post. Put <b>0</b> for unlimited attachment', 'adtp' ),
                'type' => 'text',
                'default' => '0'
            ),
            array(
                'name' => 'attachment_max_size',
                'label' => __( 'Attachment max size', 'adtp' ),
                'desc' => __( 'Enter the maximum file size in <b>KILOBYTE</b> that is allowed to attach', 'adtp' ),
                'type' => 'text',
                'default' => '2048'
            ),
            array(
                'name' => 'editor_type',
                'label' => __( 'Content editor type', 'adtp' ),
                'type' => 'select',
                'default' => 'plain',
                'options' => array(
                    'rich' => __( 'Rich Text (tiny)', 'adtp' ),
                    'full' => __( 'Rich Text (full)', 'adtp' ),
                    'plain' => __( 'Plain Text', 'adtp' )
                )
            ),
        ) ),
        'adtp_others' => apply_filters( 'adtp_options_others', array(
            array(
                'name' => 'post_notification',
                'label' => __( 'New post notification', 'adtp' ),
                'desc' => __( 'A mail will be sent to admin when a new post is created', 'adtp' ),
                'type' => 'select',
                'default' => 'yes',
                'options' => array(
                    'yes' => __( 'Yes', 'adtp' ),
                    'no' => __( 'No', 'adtp' )
                )
            ),
            array(
                'name' => 'edit_page_id',
                'label' => __( 'Edit Page', 'adtp' ),
                'desc' => __( 'Select the page where [wpuf_editpost] is located', 'adtp' ),
                'type' => 'select',
                'options' => $pages
            ),
            array(
                'name' => 'edit_stream_id',
                'label' => __( 'Edit Stream Page', 'adtp' ),
                'desc' => __( 'Select the page where [wpuf_edit_stream] is located', 'adtp' ),
                'type' => 'select',
                'options' => $pages
            ),
            array(
                'name' => 'admin_access',
                'label' => __( 'Admin area access', 'adtp' ),
                'desc' => __( 'Allow you to block specific user role to WordPress admin area.', 'adtp' ),
                'type' => 'select',
                'default' => 'read',
                'options' => array(
                    'install_themes' => __( 'Admin Only', 'adtp' ),
                    'edit_others_posts' => __( 'Admins, Editors', 'adtp' ),
                    'publish_posts' => __( 'Admins, Editors, Authors', 'adtp' ),
                    'edit_posts' => __( 'Admins, Editors, Authors, Contributors', 'adtp' ),
                    'read' => __( 'Default', 'adtp' )
                )
            ),
        ) ),
        'adtp_server' => apply_filters( 'adtp_options_server', array(
            array(
                'name' => 'avconv_path',
                'label' => __( 'avconv server path', 'adtp' ),
                'desc' => __( 'Enter the server path for avconv(http://libav.org/avconv.html). For example /usr/bin/avconv', 'adtp' ),
                'type' => 'text'
            ),
            array(
                'name' => 'ffmpeg_path',
                'label' => __( 'ffmpeg server path', 'adtp' ),
                'desc' => __( 'Enter the server path for ffmpeg(http://www.ffmpeg.org/). For example /usr/bin/ffmpeg', 'adtp' ),
                'type' => 'text'
            ),
            array(
                'name' => 'sox_path',
                'label' => __( 'sox server path', 'adtp' ),
                'desc' => __( 'Enter the server path for sox(http://sox.sourceforge.net/), which is needed for converting mp3 files to png waveforms. For example /usr/bin/sox', 'adtp' ),
                'type' => 'text'
            ),
            array(
                'name' => 'wav2png_path',
                'label' => __( 'wav2png server path', 'adtp' ),
                'desc' => __( 'Enter the server path for wav2png(https://github.com/beschulz/wav2png), which is needed for converting audio files to png waveforms. For example /usr/bin/wav2png', 'adtp' ),
                'type' => 'text'
            ),
        ) ),
    );

    return apply_filters( 'adtp_settings_fields', $settings_fields );
}