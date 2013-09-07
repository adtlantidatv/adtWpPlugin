<?php

/**
 * Dashboard class
 *
 * @author Tareq Hasan
 * @package WP User Frontend
 */
class ADTP_Dashboard {

    function __construct() {
        add_shortcode( 'adtp_dashboard', array($this, 'shortcode') );
    }

    /**
     * Handle's user dashboard functionality
     *
     * Insert shortcode [wpuf_dashboard] in a page to
     * show the user dashboard
     *
     * @since 0.1
     */
    function shortcode( $atts ) {

        extract( shortcode_atts( array('post_type' => 'post'), $atts ) );

        ob_start();

        if ( is_user_logged_in() ) {
            $this->post_listing( $post_type );
        } else {
            printf( __( "This page is restricted. Please %s to view this page.", 'adtp' ), wp_loginout( '', false ) );
        }

        $content = ob_get_contents();
        ob_end_clean();

        return $content;
    }

    /**
     * List's all the posts by the user
     *
     * @global object $wpdb
     * @global object $userdata
     */
    function post_listing( $post_type ) {
        global $wpdb, $userdata, $post;

        $userdata = get_userdata( $userdata->ID );
        $pagenum = isset( $_GET['pagenum'] ) ? intval( $_GET['pagenum'] ) : 1;

        //delete post
        if ( isset( $_REQUEST['action'] ) && $_REQUEST['action'] == "del" ) {
            $this->delete_post();
        }

        //show delete success message
        if ( isset( $_GET['msg'] ) && $_GET['msg'] == 'deleted' ) {
            echo '<div class="success">' . __( 'Post Deleted', 'adtp' ) . '</div>';
        }

        $args = array(
            'author' => get_current_user_id(),
            'post_status' => array('draft', 'future', 'pending', 'publish'),
            'post_type' => $post_type,
            'posts_per_page' => 10,
            'paged' => $pagenum
        );

        $dashboard_query = new WP_Query( $args );
        $post_type_obj = get_post_type_object( $post_type );
        ?>

        <h2 class="page-head">
            <span class="colour"><?php printf( __( "%s's Dashboard", 'adtp' ), $userdata->user_login ); ?></span>
        </h2>

        <?php do_action( 'wpuf_dashboard_top', $userdata->ID, $post_type_obj ) ?>

        <?php if ( $dashboard_query->have_posts() ) { ?>

            <?php
            $charging_enabled = 'no';
            ?>
            <table class="wpuf-table" cellpadding="0" cellspacing="0">
                <thead>
                    <tr>
                        <?php
                        ?>
                        <th><?php _e( 'Title', 'adtp' ); ?></th>
                        <th><?php _e( 'Status', 'adtp' ); ?></th>
                        <th><?php _e( 'Options', 'adtp' ); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    while ($dashboard_query->have_posts()) {
                        $dashboard_query->the_post();
                        ?>
                        <tr>
                            <td>
                                <?php if ( in_array( $post->post_status, array('draft', 'future', 'pending') ) ) { ?>

                                    <?php the_title(); ?>

                                <?php } else { ?>

                                    <a href="<?php the_permalink(); ?>" title="<?php printf( esc_attr__( 'Permalink to %s', 'adtp' ), the_title_attribute( 'echo=0' ) ); ?>" rel="bookmark"><?php the_title(); ?></a>

                                <?php } ?>
                            </td>
                            <td>
                                <?php wpuf_show_post_status( $post->post_status ) ?>
                            </td>

                            <?php
                            if ( $charging_enabled == 'yes' ) {
                                $order_id = get_post_meta( $post->ID, 'wpuf_order_id', true );
                                ?>
                                <td>
                                    <?php if ( $post->post_status == 'pending' && $order_id ) { ?>
                                        <a href="">Pay Now</a>
                                    <?php } ?>
                                </td>
                            <?php } ?>

                            <td>
                                    <?php
                                    $edit_page = (int) wpuf_get_option( 'edit_page_id', 'adtp_others' );
                                    $url = get_permalink( $edit_page );
                                    ?>
                                    <a href="<?php echo wp_nonce_url( $url . '?pid=' . $post->ID, 'wpuf_edit' ); ?>"><?php _e( 'Edit', 'adtp' ); ?></a>

                                    <a href="<?php echo wp_nonce_url( "?action=del&pid=" . $post->ID, 'wpuf_del' ) ?>" onclick="return confirm('Are you sure to delete this post?');"><span style="color: red;"><?php _e( 'Delete', 'adtp' ); ?></span></a>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>

            <div class="wpuf-pagination">
                <?php
                $pagination = paginate_links( array(
                    'base' => add_query_arg( 'pagenum', '%#%' ),
                    'format' => '',
                    'prev_text' => __( '&laquo;', 'adtp' ),
                    'next_text' => __( '&raquo;', 'adtp' ),
                    'total' => $dashboard_query->max_num_pages,
                    'current' => $pagenum
                        ) );

                if ( $pagination ) {
                    echo $pagination;
                }
                ?>
            </div>

            <?php
        } else {
            printf( __( 'No %s found', 'adtp' ), $post_type_obj->label );
            do_action( 'wpuf_dashboard_nopost', $userdata->ID, $post_type_obj );
        }

        do_action( 'wpuf_dashboard_bottom', $userdata->ID, $post_type_obj );
        ?>

        <?php
        $this->user_info();
    }

    /**
     * Show user info on dashboard
     */
    function user_info() {
        global $userdata;
    }

    /**
     * Delete a post
     *
     * Only post author and editors has the capability to delete a post
     */
    function delete_post() {
        global $userdata;

        $nonce = $_REQUEST['_wpnonce'];
        if ( !wp_verify_nonce( $nonce, 'wpuf_del' ) ) {
            die( "Security check" );
        }

        //check, if the requested user is the post author
        $maybe_delete = get_post( $_REQUEST['pid'] );

        if ( ($maybe_delete->post_author == $userdata->ID) || current_user_can( 'delete_others_pages' ) ) {
            wp_delete_post( $_REQUEST['pid'] );

            //redirect
            $redirect = add_query_arg( array('msg' => 'deleted'), get_permalink() );
            wp_redirect( $redirect );
        } else {
            echo '<div class="error">' . __( 'You are not the post author. Cheeting huh!', 'adtp' ) . '</div>';
        }
    }

}

$adtp_dashboard = new ADTP_Dashboard();