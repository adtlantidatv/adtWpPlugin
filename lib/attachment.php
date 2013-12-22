<?php

/**
 * Attachment Uploader class
 *
 * @since 0.8
 * @package WP User Frontend
 */
class WPUF_Attachment {

	private $type_attached = '';
	
    function __construct() {

        add_action( 'adtp_add_clear_errors', array($this, 'adtp_clear_errors'), 10, 2 );
        add_action( 'wpuf_add_post_form_top', array($this, 'add_post_fields'), 10, 2 );
        add_action( 'wp_enqueue_scripts', array($this, 'scripts') );

        add_action( 'wp_ajax_wpuf_attach_upload', array($this, 'upload_file') );
        add_action( 'wp_ajax_wpuf_attach_del', array($this, 'delete_file') );

        add_action( 'wpuf_add_post_after_insert', array($this, 'attach_file_to_post') );
        add_action( 'wpuf_edit_post_after_update', array($this, 'attach_file_to_post') );
    }

    function scripts() {

        $max_file_size = intval( wpuf_get_option( 'attachment_max_size', 'adtp_frontend_posting' )  ) * 1024;
        $max_upload = intval( wpuf_get_option( 'attachment_num', 'adtp_frontend_posting' ) );

        wp_enqueue_script( 'jquery' );
        
        if ( wpuf_has_shortcode( 'wpuf_addpost' ) || wpuf_has_shortcode( 'wpuf_edit' ) ) {
            wp_enqueue_script( 'plupload-handlers' );
        }
        
        wp_enqueue_script( 'jquery-ui-sortable' );
        wp_enqueue_script( 'wpuf_attachment', plugins_url( 'js/attachment.js', dirname( __FILE__ ) ), array('jquery') );

        wp_localize_script( 'wpuf_attachment', 'wpuf_attachment', array(
            'nonce' => wp_create_nonce( 'wpuf_attachment' ),
            'number' => $max_upload,
            'attachment_enabled' => true,
            'plupload' => array(
                'runtimes' => 'html5,flash,html4',
                'browse_button' => 'wpuf-attachment-upload-pickfiles',
                'container' => 'wpuf-attachment-upload-container',
                'file_data_name' => 'wpuf_attachment_file',
                'max_file_size' => $max_file_size . 'b',
                'url' => admin_url( 'admin-ajax.php' ) . '?action=wpuf_attach_upload&nonce=' . wp_create_nonce( 'wpuf_audio_track' ),
                'flash_swf_url' => includes_url( 'js/plupload/plupload.flash.swf' ),
                'filters' => array(array('title' => __( 'Allowed Files' ), 'extensions' => '*')),
                'multipart' => true,
                'urlstream_upload' => true,
            )
        ) );
    }

    function add_post_fields( $post_type, $post_obj = null ) {
        //var_dump($post_type, $post_obj);
        $attachments = array();
        if ( $post_obj ) {
            $attachments = wpfu_get_attachments( $post_obj->ID );
        }
        ?>
        <li class="upload_row">
        	<div class="uploadui">
				<a href="#" id="adt_menu" title="<?php _e('Adtlantida.tv menu', 'adt'); ?>" class="btn_01"></a>
        	</div>
        	<div class="relative progress_circle uploadui">
        		<input class="knob" data-width="90" data-height="90" data-min="0" data-max="100" data-fgColor="#79e2fd" data-displayInput=false>
                <a id="wpuf-attachment-upload-pickfiles" class="btn_01" href="#">
                	<i class="icon-cloud-upload"></i>
                </a>
        	</div>

        	
        	<div class="span1" id="wpuf-attachment-upload-filelist">
                        <script>window.wpufFileCount = 0;</script>
                        <?php
                        if ( $attachments ) {
                            foreach ($attachments as $attach) {
                                echo $this->attach_html( $attach['id'] );
                                echo '<script>window.wpufFileCount += 1;</script>';
                            }
                        }
                        ?>        	
        	</div>
        </li>
        <li>
            <div id="wpuf-attachment-upload-container"></div>
        </li>
        <?php
    }

    function adtp_clear_errors( $post_type, $post_obj = null ) {
    ?>
    	<li>
    		<div id="wpuf-attachment-upload-container"></div>
        </li>

    <?php
	}

    function upload_file() {
        check_ajax_referer( 'wpuf_audio_track', 'nonce' );

        $upload = array(
            'name' => $_FILES['wpuf_attachment_file']['name'],
            'type' => $_FILES['wpuf_attachment_file']['type'],
            'tmp_name' => $_FILES['wpuf_attachment_file']['tmp_name'],
            'error' => $_FILES['wpuf_attachment_file']['error'],
            'size' => $_FILES['wpuf_attachment_file']['size']
        );
        
        $attach_id = wpuf_upload_file( $upload );
        $type_attached = $_FILES['wpuf_attachment_file']['type'];

        if ( $attach_id ) {
            $html = $this->attach_html( $attach_id );

            //upload post thumbnail
            //$attach_img_id = wpuf_upload_thumb_file( $upload );
            //$html .= sprintf( '<input type="hidden" name="wpuf_attach_id[]" value="%d" />', $attach_img_id );

            $response = array(
                'success' => true,
                'html' => $html,
            );

            echo json_encode( $response );

        
            exit;
        }


        $response = array('success' => false);
        echo json_encode( $response );
                
        exit;
    }

    function attach_html( $attach_id ) {

        $attachment = get_post( $attach_id );

        $html = '';
        //$html .= sprintf( '<a href="#" class="btn_01 track-delete blue" data-attach_id="%d">%s</a>', $attach_id, __( 'Delete', 'adt' ) );
        $html .= sprintf( '<input type="hidden" name="wpuf_attach_id[]" value="%d" />', $attach_id );

        return $html;
    }

    function delete_file() {
        check_ajax_referer( 'wpuf_attachment', 'nonce' );

        $attach_id = isset( $_POST['attach_id'] ) ? intval( $_POST['attach_id'] ) : 0;
        $attachment = get_post( $attach_id );

        //post author or editor role
        if ( get_current_user_id() == $attachment->post_author || current_user_can( 'delete_private_pages' ) ) {
            wp_delete_attachment( $attach_id, true );
            echo 'success';
        }

        exit;
    }

    function attach_file_to_post( $post_id ) {
        $posted = $_POST;
        $source_id = '';
        

        if ( isset( $posted['wpuf_attach_id'] ) ) {
            foreach ($posted['wpuf_attach_id'] as $index => $attach_id) {
            	$source_id = $attach_id;
                $postarr = array(
                    'ID' => $attach_id,
                    'post_title' => $file_type,
                    'post_parent' => $post_id,
                    'menu_order' => $index
                );

                //set_post_thumbnail( $post_id, $attach_id );

                wp_update_post( $postarr );

                $file_type = get_post_mime_type($attach_id);

                // Check if the file is an audio file
                if(($file_type == "audio/mpeg") || ($file_type == "audio/x-mpeg") || ($file_type == "audio/mp3") || ($file_type == "audio/x-mp3") || ($file_type == "audio/mpeg3") || ($file_type == "audio/x-mpeg3") || ($file_type == "audio/mpg") || ($file_type == "audio/x-mpg") || ($file_type == "audio/x-mpegaudio") || ($file_type == 'audio/ogg')){
	                
                }else{
					update_post_meta($post_id, 'adt_is_converting', '1');
	        
                	// If it is not an audio file, we generate the webm version of it.
	                $php_url = '/home/adtlantida/adtlantida.tv/wp-content/plugins/adtWpPlugin/lib/processVideo.php';
	                exec("php -f '".$php_url."' ".$post_id." ".$source_id." > /dev/null &");

	            }
	        }	        
        }        
    }

}

$wpuf_audio = new WPUF_Attachment();