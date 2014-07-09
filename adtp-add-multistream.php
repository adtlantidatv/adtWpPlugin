<?php

/**
 * Add Post form class
 *
 * @author Tareq Hasan
 * @package WP User Frontend
 */
class ADTP_Add_Multistream {

    function __construct() {
        add_shortcode( 'wpuf_addmultistream', array($this, 'shortcode') );
    }

    /**
     * Handles the add stream shortcode
     *
     * @param $atts
     */
    function shortcode( $atts ) {

        extract( shortcode_atts( array('post_type' => 'multistreamings'), $atts ) );

        ob_start();

        if ( is_user_logged_in() ) {
            $this->post_form( $post_type );
        } else {
            printf( __( "This page is restricted. Please %s to view this page.", 'adtp' ), wp_loginout( get_permalink(), false ) );
        }

        $content = ob_get_contents();
        ob_end_clean();

        return $content;
    }

    /**
     * Add multistreaming main form
     *
     * @param $post_type
     */
    function post_form( $post_type ) {
        global $userdata;

        $userdata = get_user_by( 'id', $userdata->ID );

        if ( isset( $_POST['wpuf_post_new_submit'] ) ) {
            $nonce = $_REQUEST['_wpnonce'];
            if ( !wp_verify_nonce( $nonce, 'wpuf-add-multistream' ) ) {
                wp_die( __( 'Cheating?' ) );
            }

            $this->submit_multistream();
        }

        $info = __( "Post It!", 'adtp' );
        $can_post = 'yes';

        $info = apply_filters( 'wpuf_addpost_notice', $info );
        $can_post = apply_filters( 'wpuf_can_post', $can_post );
        $featured_image = wpuf_get_option( 'enable_featured_image', 'adtp_frontend_posting', 'no' );

        $title = isset( $_POST['wpuf_post_title'] ) ? esc_attr( $_POST['wpuf_post_title'] ) : '';
        $description = isset( $_POST['wpuf_post_content'] ) ? $_POST['wpuf_post_content'] : '';
        $hashtag = isset( $_POST['wpuf_post_hashtags'] ) ? $_POST['wpuf_post_hashtags'] : '';

        if ( $can_post == 'yes' ) {
            ?>
            <div id="wpuf-post-area" class="span12">
                <form class="form_01 multistream_form" id="wpuf_new_post_form" name="wpuf_new_post_form" action="" enctype="multipart/form-data" method="POST">
                    <?php wp_nonce_field( 'wpuf-add-multistream' ) ?>

                    <ul class="wpuf-post-form">

                        <! -- _____ titulo _________________________________ -->
                        <li class="row">
                        	<div class="span3 offset1">
	                            
	                            <label for="new-post-title">
	                                <?php _e('Title', 'adt'); ?>
	                            </label>
	                            
	                            <div class="description">
	                                <?php _e('You know, be creative. Maybe you are also interested on being <a href="http://moz.com/learn/seo/title-tag">SEO friendly</a>. Avoid using titles like "my video"', 'adt'); ?>
	                            </div>
	                            
                        	</div>
                        	
                        	<div class="span7 field">
                        		<div class="input_warper">
	                            	<input class="requiredField" type="text" value="<?php echo $title; ?>" name="wpuf_post_title" id="new-post-title" minlength="2">
                        		</div>
                        	</div>
                        </li>

                        <?php do_action( 'wpuf_add_post_form_description', $post_type ); ?>

                        <! -- _____ description ___________________________ -->
                        <li class="row">
                        	<div class="span3 offset1">
                        	
	                            <label for="new-post-desc">
	                                <?php _e('Description', 'adt'); ?>
	                            </label>
                        	
	                            <div class="description">
	                                <?php _e('Tell the story that is behind the video, or just tell whatever you want', 'adt'); ?>
	                            </div>
                        	</div>
                        	
                        	<div class="span7 field">
                            <?php
                            $editor = wpuf_get_option( 'editor_type', 'adtp_frontend_posting' );
                            if ( $editor == 'full' ) {
                                ?>
                                <div style="float:left;">
                                    <?php wp_editor( $description, 'new-post-desc', array('textarea_name' => 'wpuf_post_content', 'editor_class' => 'requiredField', 'teeny' => false, 'textarea_rows' => 8) ); ?>
                                </div>
                            <?php } else if ( $editor == 'rich' ) { ?>
                                <div style="float:left;">
                                    <?php wp_editor( $description, 'new-post-desc', array('textarea_name' => 'wpuf_post_content', 'editor_class' => 'requiredField', 'teeny' => true, 'textarea_rows' => 8) ); ?>
                                </div>

                            <?php } else { ?>
                                <textarea name="wpuf_post_content" class="requiredField" id="new-post-desc" cols="60" rows="8"><?php echo esc_textarea( $description ); ?></textarea>
                            <?php } ?>
                            </div>
                        </li>

                        <?php do_action( 'wpuf_add_post_form_after_description', $post_type ); ?>
                        
                        <! -- _____ post thumbnail __________________________ -->
                    	<?php if ( current_theme_supports( 'post-thumbnails' ) ) { ?>
						<li class="row">
                        	<div class="span3 offset1">
                        	
                            	<label for="post-thumbnail">
                            		<?php echo wpuf_get_option( 'ft_image_label', 'wpuf_labels', __( 'Featured Image', 'wpuf' ) ); ?>
                            	</label>

	                            <div class="description">
	                                <?php _e('Upload an image not bigger than 1200px width. Allowed formats: jpg, png', 'adt'); ?>
	                            </div>
							</div>

							<div class="span7 field">
								<div class="file_warper">
	                                <input type="file" id="input_thumb" name="file" class="filestyle" data-icon="false" />
								</div>
							</div>
						</li>
						
						<script type="text/javascript">
							jQuery(":file").filestyle({icon: false});
						</script>
						<?php } ?>


                            <! -- _____ tags ___________________________ -->
                            <li class="row">
	                        	<div class="span3 offset1">
	                                <label for="new-post-tags">
	                                    <?php _e('Tags', 'adt'); ?>
	                                </label>

		                            <div class="description">
		                                <?php _e('Add tags that would make easier to find your video. Do not add more than 5', 'adt'); ?>
		                            </div>
                                </div>
                                
                                <div class="span7 field">
                                	<input type="text" name="wpuf_post_tags" id="new-post-tags" class="new-post-tags">
                                </div>
                            </li>
                            <?php

                        do_action( 'wpuf_add_post_form_tags', $post_type );
                        ?>

                        <! -- _____ hashtag ___________________________ -->
                    	<li class="row">
	                    	<div class="span3 offset1">
	                            <label for="new-post-hashtags">
	                                <?php _e('Twitter hashtag', 'adt'); ?>
	                            </label>

	                            <div class="description">
	                                <?php _e('Write the twitter hashtag without the "#"', 'adt'); ?>
	                            </div>
                            </div>
                            
	                    	<div class="span7 field">
                            	<input type="text" name="wpuf_post_hashtags" id="new-post-hashtags" value="<?php echo $hashtag; ?>">
	                    	</div>
                        </li>

                        <li class="row">
                        	<div class="span2 offset4">
	                            <input class="wpuf_submit" type="submit" name="wpuf_new_post_submit" value="<?php _e('send', 'adt'); ?>">
	                            <input type="hidden" name="wpuf_post_type" value="<?php echo $post_type; ?>" />
	                            <input type="hidden" name="wpuf_post_new_submit" value="yes" />
                        	</div>
                        </li>

                        <?php do_action( 'wpuf_add_post_form_bottom', $post_type ); ?>

                    </ul>
                </form>
            </div>
            <?php
        } else {
            echo '<div class="info">' . $info . '</div>';
        }
    }


    /**
     * Validate the post submit data
     *
     * @global type $userdata
     * @param type $post_type
     */
    function submit_multistream() {
        global $userdata;

        $errors = array();

        var_dump( $_POST );        

        $title = trim( $_POST['wpuf_post_title'] );
        $content = trim( $_POST['wpuf_post_content'] );
        $hashtag = trim( $_POST['wpuf_post_hashtags'] );

        $tags = '';
        if ( isset( $_POST['wpuf_post_tags'] ) ) {
            $tags = wpuf_clean_tags( $_POST['wpuf_post_tags'] );
        }

        //validate title
        if ( empty( $title ) ) {
            $errors[] = __( 'Empty post title', 'adtp' );
        } else {
            $title = trim( strip_tags( $title ) );
        }

        //validate post content
        if ( empty( $content ) ) {
            $errors[] = __( 'Empty post content', 'adtp' );
        } else {
            $content = trim( $content );
        }

        //process tags
        if ( !empty( $tags ) ) {
            $tags = explode( ',', $tags );
        }


        //post type
        $post_type = trim( strip_tags( $_POST['wpuf_post_type'] ) );

        $errors = apply_filters( 'wpuf_add_post_validation', $errors );


        //if not any errors, proceed
        if ( $errors ) {
            echo wpuf_error_msg( $errors );
            return;
        }

        $my_post = array(
            'post_title' => $title,
            'post_content' => $content,
            'post_status' => 'publish',
            'post_author' => $userdata->ID,
            'post_type' => $post_type,
            'tags_input' => $tags
        );


        //plugin API to extend the functionality
        $my_post = apply_filters( 'wpuf_add_post_args', $my_post );

        //var_dump( $_POST, $my_post );die();
        //insert the post
        $post_id = wp_insert_post( $my_post );

        if ( $post_id ) {

            //upload attachment to the post            
			if ( ! function_exists( 'wp_handle_upload' ) ) require_once( ABSPATH . 'wp-admin/includes/file.php' );
            $uploadedfile = $_FILES['file'];
			$upload_overrides = array( 'test_form' => false );
			$movefile = wp_handle_upload( $uploadedfile, $upload_overrides );
			
			$attachment = array(
				'post_title' => $_FILES["file"]["name"],
				'post_content' => '',
				'post_type' => 'attachment',
				'post_parent' => $post_id,
				'post_mime_type' => $_FILES["file"]["type"],
				'guid' => $movefile['url']
			);
			$imaxe_id = wp_insert_attachment( $attachment,$movefile[ 'file' ], $post_id );
			require_once( ABSPATH . 'wp-admin/includes/image.php' );
			$attach_data = wp_generate_attachment_metadata( $imaxe_id, $movefile['file'] );
			wp_update_attachment_metadata( $imaxe_id, $attach_data );
			set_post_thumbnail( $post_id, $imaxe_id );

            //send mail notification
            if ( wpuf_get_option( 'post_notification', 'adtp_others', 'yes' ) == 'yes' ) {
                wpuf_notify_post_mail( $userdata, $post_id );
            }
            
			if ( isset( $_POST['wpuf_post_hashtags'] ) ) {
				update_post_meta($post_id, 'adt_twitter_hashtag', $hashtag);
			}


            //Set Post expiration date if has any

            //plugin API to extend the functionality
            do_action( 'wpuf_add_post_after_insert', $post_id );

            //echo '<div class="success">' . __('Post published successfully', 'adtp') . '</div>';
            if ( $post_id ) {
                $redirect = get_permalink( $post_id );

                wp_redirect( $redirect );
                exit;
            }
        }
        
    }

}


$wpuf_postform = new ADTP_Add_Multistream();