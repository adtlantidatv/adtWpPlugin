<?php

class ADTP_Edit_Profile {

    function __construct() {
        add_shortcode( 'wpuf_editprofile', array($this, 'shortcode') );

        add_action( 'personal_options_update', array($this, 'post_lock_update') );
        add_action( 'edit_user_profile_update', array($this, 'post_lock_update') );

        add_action( 'show_user_profile', array($this, 'post_lock_form') );
        add_action( 'edit_user_profile', array($this, 'post_lock_form') );
		add_action('user_edit_form_tag',array($this, 'make_uploadable_form'));
    }

	function make_uploadable_form() {
	    echo ' enctype="multipart/form-data"';
	}
    /**
     * Hanldes the editprofile shortcode
     *
     * @author Tareq Hasan
     */
    function shortcode() {

        ob_start();

        if ( is_user_logged_in() ) {
            $this->show_form();
        } else {
            printf( __( "This page is restricted. Please %s to view this page.", 'adtp' ), wp_loginout( '', false ) );
        }

        $content = ob_get_contents();
        ob_end_clean();

        return $content;
    }

    /**
     * Shows the user profile form
     *
     * @global type $userdata
     * @param type $user_id
     */
    function show_form( $user_id = null ) {
        global $userdata, $wp_http_referer;
        get_currentuserinfo();

        if ( !(function_exists( 'get_user_to_edit' )) ) {
            require_once(ABSPATH . '/wp-admin/includes/user.php');
        }

        if ( !(function_exists( '_wp_get_user_contactmethods' )) ) {
            require_once(ABSPATH . '/wp-includes/registration.php');
        }

        if ( !$user_id ) {
            $current_user = wp_get_current_user();
            $user_id = $user_ID = $current_user->ID;
        }

        if ( isset( $_POST['submit'] ) ) {
            check_admin_referer( 'update-profile_' . $user_id );
            $errors = edit_user( $user_id );
            if ( is_wp_error( $errors ) ) {
                $message = $errors->get_error_message();
                $style = 'error';
            } else {
                $message = __( '<strong>Success</strong>: Profile updated', 'adtp' );
                $style = 'alert alert-success alert-full';
                do_action( 'personal_options_update', $user_id );
            }
        }

        $profileuser = get_user_to_edit( $user_id );

        if ( isset( $message ) ) {
            echo '<div class="' . $style . '"><button type="button" class="close" data-dismiss="alert">&times;</button>' . $message . '</div>';
        }
        ?>
        <div class="wpuf-profile span12">
            <form class="form_01 usuario" name="profile" id="your-profile" action="" method="post" enctype="multipart/form-data">
                <?php wp_nonce_field( 'update-profile_' . $user_id ) ?>
                <?php if ( $wp_http_referer ) : ?>
                    <input type="hidden" name="wp_http_referer" value="<?php echo esc_url( $wp_http_referer ); ?>" />
                <?php endif; ?>
                <input type="hidden" name="from" value="profile" />
                <input type="hidden" name="checkuser_id" value="<?php echo $user_id; ?>" />
                <table class="wpuf-table">
                    <?php do_action( 'personal_options', $profileuser ); ?>
                </table>
                <?php do_action( 'profile_personal_options', $profileuser ); ?>

                <ul class="reset">
	            	<?php do_action( 'adtp_add_clear_errors', $curpost->post_type, $curpost ); //plugin hook      ?>
                	<li class="row">
                		<div class="span3 offset1">
                			<label for="user_login1"><?php _e( 'Username' ); ?></label>
                           
                            <div class="description">
                                <?php _e('Username can not be changed', 'adt'); ?>
                            </div>
                		</div>
                		<div class="span7 field">
                			<input type="text" name="user_login" id="user_login1" value="<?php echo esc_attr( $profileuser->user_login ); ?>" disabled="disabled" class="regular-text" />
                		</div>
                	</li>
                	
                	<li class="row">
                		<div class="span3 offset1">
	                		<label for="first_name"><?php _e( 'First Name' ) ?></label>

                		</div>

                		<div class="span7">
                			<input type="text" name="first_name" id="first_name" value="<?php echo esc_attr( $profileuser->first_name ) ?>" class="regular-text" />
                		</div>
                	</li>

                	<li class="row">
                		<div class="span3 offset1">
	                		<label for="last_name"><?php _e( 'Last Name' ) ?></label>
                		</div>

                		<div class="span7">
                			<input type="text" name="last_name" id="last_name" value="<?php echo esc_attr( $profileuser->last_name ) ?>" class="regular-text" />
                		</div>
                	</li>

                	<li class="row">
                		<div class="span3 offset1">
	                		<label for="nickname"><?php _e( 'Nickname' ); ?></label>
                		</div>

                		<div class="span7">
                			<input type="text" name="nickname" id="nickname" value="<?php echo esc_attr( $profileuser->nickname ) ?>" class="regular-text" />
                		</div>
                	</li>

                	<li class="row">
                		<div class="span3 offset1">
	                		<label for="email"><?php _e( 'E-mail' ); ?></label>
                		</div>

                		<div class="span7">
                			<input type="text" name="email" id="email" value="<?php echo esc_attr( $profileuser->user_email ) ?>" class="regular-text" />
                		</div>
                	</li>

                	<li class="row">
                		<div class="span3 offset1">
	                		<label for="url"><?php _e( 'Website' ) ?></label>
                		</div>

                		<div class="span7">
                			<input type="text" name="url" id="url" value="<?php echo esc_attr( $profileuser->user_url ) ?>" class="regular-text code" />
                		</div>
                	</li>

                    <! -- _____ post thumbnail __________________________ -->
                	<?php if ( current_theme_supports( 'post-thumbnails' ) ) { ?>
					<?php } ?>

                	<li class="row">
                		<div class="span3 offset1">
	                		<label for="description"><?php _e( 'Biographical Info', 'adtp' ); ?></label>
                            <div class="description">
                                <?php _e( 'Share a little biographical information to fill out your profile. This may be shown publicly.', 'adt' ); ?>
                            </div>
                		</div>

                		<div class="span7 field">
                			<textarea name="description" id="description" rows="5" cols="30"><?php echo esc_html( $profileuser->description ); ?></textarea>
                		</div>
                	</li>

                	<li class="row">
                		<div class="span3 offset1">
	                		<label for="pass1"><?php _e( 'New Password', 'adtp' ); ?></label>
                		</div>

                		<div class="span7">
                			<input type="password" name="pass1" id="pass1" size="16" value="" autocomplete="off" />
                		</div>
                	</li>

                	<li class="row">
                		<div class="span3 offset1">
	                		<label><?php _e( 'Confirm Password', 'adtp' ); ?></label>
                		</div>

                		<div class="span7">
                			<input type="password" name="pass2" id="pass2" size="16" value="" autocomplete="off" />
                		</div>
                	</li>
                	

                <?php do_action( 'show_user_profile', $profileuser ); ?>
                </ul>

                <div class="submit row">
                    <div class="span2 offset4">
	                    <input type="hidden" name="action" value="update" />
	                    <input type="hidden" name="user_id" id="user_id" value="<?php echo esc_attr( $user_id ); ?>" />
	                    <input type="submit" class="wpuf-submit" value="<?php _e( 'Update Profile', 'adtp' ); ?>" name="submit" />
                    </div>
                </div>
            </form>
        </div>
        <?php
    }

    /**
     * Adds the postlock form in users profile
     *
     * @param object $profileuser
     */
    function post_lock_form( $profileuser ) {
        if ( is_admin() && current_user_can( 'edit_users' ) ) {
            $select = ( $profileuser->wpuf_postlock == 'yes' ) ? 'yes' : 'no';
            ?>

            <h3><?php _e( 'ADTP Post Lock', 'adtp' ); ?></h3>
            <table class="form-table">
                <tr>
                    <th><label for="post-lock"><?php _e( 'Lock Post:', 'adtp' ); ?> </label></th>
                    <td>
                        <select name="wpuf_postlock" id="post-lock">
                            <option value="no"<?php selected( $select, 'no' ); ?>>No</option>
                            <option value="yes"<?php selected( $select, 'yes' ); ?>>Yes</option>
                        </select>
                        <span class="description"><?php _e( 'Lock user from creating new post.', 'adtp' ); ?></span></em>
                    </td>
                </tr>

                <tr>
                    <th><label for="post-lock"><?php _e( 'Lock Reason:', 'adtp' ); ?> </label></th>
                    <td>
                        <input type="text" name="wpuf_lock_cause" id="wpuf_lock_cause" class="regular-text" value="<?php echo esc_attr( $profileuser->wpuf_lock_cause ); ?>" />
                    </td>
                </tr>

            </table>
            <?php
        } ?>
					<li class="row hide">
                    	<div class="span3 offset1">
                    	
                        	<label for="post-thumbnail">
                        		<?php _e( 'Id:', 'adtp' ); ?>
                        	</label>
						</div>

						<div class="span7 field">
							<div class="file_warper">
                                <input type="text" name="avatar" id="avatar" class="regular-text" value="<?php echo esc_attr( $profileuser->avatar ); ?>" />
							</div>
						</div>
					</li>

					<li class="row">
                    	<div class="span3 offset1">
                    	
                        	<label for="post-thumbnail">
                        		<?php echo _e('Avatar', 'adt'); ?>
                        	</label>

                            <div class="description">
                                <?php _e('Upload an image not bigger than 1200px width. Allowed formats: jpg, png', 'adt'); ?>
                            </div>

                            <?php if(get_user_meta($profileuser->id, 'avatar', true) != ''){ ?>
                            <div class="margin_top_20">
                            	<?php echo wp_get_attachment_image(get_user_meta($profileuser->id, 'avatar', true), 'zpan3_false') ?>
                            </div>
                            <?php } ?>
                            
						</div>

						<div class="span7 field">
							<div class="file_warper">
                                <input type="file" id="input_thumb" name="input_thumb" class="filestyle" data-icon="false" />
							</div>

						</div>
					</li>
					
					<script type="text/javascript">
						jQuery(":file").filestyle({icon: false});
					</script>
					
					


    <?php }

    /**
     * Update user profile lock
     *
     * @param int $user_id
     */
    function post_lock_update( $user_id ) {
		global $_FILES,$_POST;
            update_user_meta( $user_id, 'wpuf_postlock', $_POST['wpuf_postlock'] );
            update_user_meta( $user_id, 'wpuf_lock_cause', $_POST['wpuf_lock_cause'] );
            update_user_meta( $user_id, 'wpuf_sub_validity', $_POST['wpuf_sub_validity'] );
            update_user_meta( $user_id, 'wpuf_sub_pcount', $_POST['wpuf_sub_pcount'] );

            //upload attachment to the post 
            if($_FILES['input_thumb']['error']==0) {          
				if ( ! function_exists( 'wp_handle_upload' ) ) require_once( ABSPATH . 'wp-admin/includes/file.php' );
	
	            $uploadedfile = $_FILES['input_thumb'];
				$upload_overrides = array( 'test_form' => false );
				$movefile = wp_handle_upload( $uploadedfile, $upload_overrides );
				
				$attachment = array(
					'post_title' => $_FILES["input_thumb"]["name"],
					'post_content' => '',
					'post_type' => 'attachment',
					'post_status' => 'publish',
					'post_mime_type' => $_FILES["input_thumb"]["type"],
					'guid' => $movefile['url']
				);
				$imaxe_id = wp_insert_attachment( $attachment,$movefile[ 'file' ] );
				require_once( ABSPATH . 'wp-admin/includes/image.php' );
				$attach_data = wp_generate_attachment_metadata( $imaxe_id, $movefile['file'] );
				wp_update_attachment_metadata( $imaxe_id, $attach_data );
				
				update_usermeta( $user_id, 'avatar', $imaxe_id );
			}
        
    }

}

$edit_profile = new ADTP_Edit_Profile();