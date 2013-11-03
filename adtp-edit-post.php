<?php

class ADTP_Edit_Post {

    function __construct() {
        add_shortcode( 'wpuf_edit', array($this, 'shortcode') );
    }

    /**
     * Handles the edit post shortcode
     *
     * @return string generated form by the plugin
     */
    function shortcode() {

        ob_start();

        if ( is_user_logged_in() ) {
            $this->prepare_form();
        } else {
            printf( __( "This page is restricted. Please %s to view this page.", 'adtp' ), wp_loginout( '', false ) );
        }

        $content = ob_get_contents();
        ob_end_clean();

        return $content;
    }

    /**
     * Main edit post form
     *
     * @global type $wpdb
     * @global type $userdata
     */
    function prepare_form() {
        global $wpdb, $userdata;

        $post_id = isset( $_GET['pid'] ) ? intval( $_GET['pid'] ) : 0;

        //is editing enabled?

        $curpost = get_post( $post_id );

        if ( !$curpost ) {
            return __( 'Invalid post', 'adtp' );
        }

        //has permission?
        if ( !current_user_can( 'delete_others_posts' ) && ( $userdata->ID != $curpost->post_author ) ) {
            return __( 'You are not allowed to edit', 'adtp' );
        }

        //perform delete attachment action
        if ( isset( $_REQUEST['action'] ) && $_REQUEST['action'] == "del" ) {
            check_admin_referer( 'wpuf_attach_del' );
            $attach_id = intval( $_REQUEST['attach_id'] );

            if ( $attach_id ) {
                wp_delete_attachment( $attach_id );
            }
        }

        //process post
        if ( isset( $_POST['wpuf_edit_post_submit'] ) && wp_verify_nonce( $_REQUEST['_wpnonce'], 'adtp-edit-post' ) ) {
            $this->submit_post();

            $curpost = get_post( $post_id );
        }

        //show post form
        $this->edit_form( $curpost );
    }

    function edit_form( $curpost ) {
    	global $files;
        $post_tags = wp_get_post_tags( $curpost->ID );
        $tagsarray = array();
        foreach ($post_tags as $tag) {
            $tagsarray[] = $tag->name;
        }
        $tagslist = implode( ', ', $tagsarray );
        $categories = get_the_category( $curpost->ID );
        $featured_image = wpuf_get_option( 'enable_featured_image', 'adtp_frontend_posting', 'no' );

		$files = getFilesUrlByType($curpost->ID);
        ?>
        <div id="wpuf-post-area" class="span12 editar_video">
        	
        	<?php if($files['webm']!=null){ ?>
			<?php
				$thumb = wp_get_attachment_image_src( get_post_thumbnail_id($curpost->ID), 'video_poster' );
				$url = $thumb['0']; 
			?>
			<video class="video-js vjs-default-skin" poster="<?php echo $url; ?>" height="659" width="100%" controls="" data-setup='{"controls":true}' id="video_<?php the_ID(); ?>">
				<?php if($files['webm']){ ?>
			    <source src="<?php echo $files['webm'] ?>" type="video/webm">
				<?php } ?>
				<?php if($files['mp4']){ ?>
			    <source src="<?php echo $files['mp4']; ?>" type="video/mp4">
				<?php } ?>-
				<?php if($files['ogv']){ ?>
			    <source src="<?php echo $files['ogv']; ?>" type="video/ogg">
				<?php } ?>
			    <p class="warning">Your browser does not support HTML5 video.</p>
			</video>
			<?php } ?>

				<!-- audio player -->
				<?php if($files['mp3']!=null || $files['ogg']!=null){ ?>
					<?php get_template_part( 'player', 'audio' ); ?> 
				<?php } ?>
				
			<div class="row margin_top_30">
				<div class="span1">
					<ul class="main_menu reset">
						<li>
							<a href="#" id="adt_menu" title="<?php _e('Adtlantida.tv menu', 'adt'); ?>" class="btn_01"></a>
						</li>
					
						<li>
							<a href="<?php echo wp_nonce_url( "?action=del&pid=" . $curpost->ID, 'wpuf_del' ) ?>" onclick="return confirm('Are you sure to delete this post?');"><i class="icon-remove-sign"></i></a>
							
						</li>

				</div>
				
				<div class="span11">
					<h1>
						<span><?php _e('Editar:', 'adt'); ?></span> <?php echo $curpost->post_title; ?>
					</h1>
				</div>
			</div>
        
            <form class="form_01" name="wpuf_edit_post_form" id="wpuf_edit_post_form" action="" enctype="multipart/form-data" method="POST">
                <?php wp_nonce_field( 'adtp-edit-post' ) ?>
                
                <ul class="wpuf-post-form">
	                <?php do_action( 'adtp_add_clear_errors', $curpost->post_type, $curpost ); //plugin hook      ?>
                    <li class="row">
                    	
                    	<div class="span3">
	                        <label for="new-post-title">
	                            <?php _e('title', 'adt'); ?> <span class="required">*</span>
	                        </label>

                            <div class="description">
                                <?php _e('Los v&iacute;deos son m&aacute;s interesantes cuando tienen t&iacute;tulos creativos. Sabemos que puedes mejorar el nombre "Mi v&iacute;deo".', 'adt'); ?>
                            </div>
                    	</div>

                    	<div class="span8">                	
                    		<input type="text" name="wpuf_post_title" id="new-post-title" minlength="2" value="<?php echo esc_html( $curpost->post_title ); ?>">
                    	</div>
                    	
                    </li>

                    <?php do_action( 'wpuf_add_post_form_description', $curpost->post_type, $curpost ); ?>

                    <li class="row">
                    	<div class="span3">
	                        <label for="new-post-desc">
	                            <?php _e('descripcion', 'adt'); ?> <span class="required">*</span>
	                        </label>
                    	</div>
                    	
                    	<div class="span7">
	                        <?php
	                        $editor = wpuf_get_option( 'editor_type', 'adtp_frontend_posting', 'normal' );
	                        if ( $editor == 'full' ) {
	                            ?>
	                            <div style="float:left;">
	                                <?php wp_editor( $curpost->post_content, 'new-post-desc', array('textarea_name' => 'wpuf_post_content', 'editor_class' => 'requiredField', 'teeny' => false, 'textarea_rows' => 8) ); ?>
	                            </div>
	                        <?php } else if ( $editor == 'rich' ) { ?>
	                            <div style="float:left;">
	                                <?php wp_editor( $curpost->post_content, 'new-post-desc', array('textarea_name' => 'wpuf_post_content', 'editor_class' => 'requiredField', 'teeny' => true, 'textarea_rows' => 8) ); ?>
	                            </div>
	
	                        <?php } else { ?>
	                            <textarea name="wpuf_post_content" class="requiredField" id="new-post-desc" cols="60" rows="8"><?php echo esc_textarea( $curpost->post_content ); ?></textarea>
	                        <?php } ?>
                        </div>

                    </li>

                    <?php do_action( 'wpuf_add_post_form_after_description', $curpost->post_type, $curpost ); ?>

                    	<li class="row">
	                    	<div class="span3">
	                            <label for="new-post-tags">
	                                <?php _e('tags', 'adt'); ?>
	                            </label>
                            </div>
                            
	                    	<div class="span8">
                            	<input type="text" name="wpuf_post_tags" id="new-post-tags" value="<?php echo $tagslist; ?>">
	                    	</div>
                        </li>

                    <?php do_action( 'wpuf_add_post_form_tags', $curpost->post_type, $curpost ); ?>

                    <li>
                        <label>&nbsp;</label>
                        <input class="wpuf_submit" type="submit" name="wpuf_edit_post_submit" value="<?php _e('update', 'adt'); ?>">
                        <input type="hidden" name="wpuf_edit_post_submit" value="yes" />
                        <input type="hidden" name="post_id" value="<?php echo $curpost->ID; ?>">
                    </li>
                </ul>
            </form>
        </div>

        <?php
    }

    function submit_post() {
        global $userdata;

        $errors = array();

        $title = trim( $_POST['wpuf_post_title'] );
        $content = trim( $_POST['wpuf_post_content'] );

        $tags = '';
        $cat = '';
        if ( isset( $_POST['wpuf_post_tags'] ) ) {
            $tags = wpuf_clean_tags( $_POST['wpuf_post_tags'] );
        }

        //if there is some attachement, validate them
        if ( !empty( $_FILES['wpuf_post_attachments'] ) ) {
            $errors = wpuf_check_upload();
        }

        if ( empty( $title ) ) {
            $errors[] = __( 'Empty post title', 'adtp' );
        } else {
            $title = trim( strip_tags( $title ) );
        }

        if ( empty( $content ) ) {
            $errors[] = __( 'Empty post content', 'adtp' );
        } else {
            $content = trim( $content );
        }

        if ( !empty( $tags ) ) {
            $tags = explode( ',', $tags );
        }

        //post attachment
        $attach_id = isset( $_POST['wpuf_featured_img'] ) ? intval( $_POST['wpuf_featured_img'] ) : 0;

        $errors = apply_filters( 'wpuf_edit_post_validation', $errors );

        if ( !$errors ) {

            $post_update = array(
                'ID' => trim( $_POST['post_id'] ),
                'post_title' => $title,
                'post_content' => $content,
                'tags_input' => $tags
            );

            //plugin API to extend the functionality
            $post_update = apply_filters( 'wpuf_edit_post_args', $post_update );

            $post_id = wp_update_post( $post_update );

            if ( $post_id ) {
                echo '<div class="success">' . __( 'Post updated succesfully.', 'adtp' ) . '</div>';

                //upload attachment to the post
                wpuf_upload_attachment( $post_id );

                //set post thumbnail if has any
                if ( $attach_id ) {
                    set_post_thumbnail( $post_id, $attach_id );
                }


            }
        } else {
            echo wpuf_error_msg( $errors );
        }
    }

}

$wpuf_edit = new ADTP_Edit_Post();