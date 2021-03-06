<?php
/**
 * Don't load this file directly!
 */
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Helper functions for RtWiki Page not Found
 * Check for the page in wiki CPT. If not found(404), it would Display redirect link to new post
 *
 * @author     Dipesh
 */


if ( !class_exists( 'Rt_Wiki_404_Redirect' ) ) {

    /**
     * Class RT_Wiki_Post_Filtering
     */
    class Rt_Wiki_404_Redirect {

        /**
         * Object initialization
         */
        public function __construct() {
            $this->hook();
        }

        /**
         * Apply Filter Wiki's Filter
         */
        function hook(){

        }

        /**
         * get page id from name and type
         *
         * @param $name
         * @param string $post_type
         * @return mixed
         */
        function rtwiki_get_page_id( $name, $post_type = 'post' )
        {
            global $wpdb;
            $page_id = $wpdb->get_var( "SELECT ID FROM $wpdb->posts WHERE ( post_name = '" . $name . "' or post_title = '" . $name . "' ) and post_status = 'publish' and post_type='" . $post_type . "'" );

            return $page_id;
        }

        /**
         * return url of new post if page not found
         *
         * @return URl
         */
        function redirect_404()
        {
            global $rt_wiki_404_redirect;
            $supported_posts = rtwiki_get_supported_attribute();
            $url             = null;
            if ( is_404() && is_array( $supported_posts ) && in_array( get_query_var( 'post_type' ), $supported_posts ) ){
                $userId = get_current_user_id();
                if ( $userId == 0 ){
                    $userId = 1;
                }
                $page      = $_SERVER[ 'REQUEST_URI' ];
                $segments  = explode( '/', trim( $page, '/' ) );
                $post_type = $segments[ 0 ];
                if ( in_array( $post_type, $supported_posts ) ){
                    $postid = '';
                    $url    = '';
                    for ( $i = 1; $i < count( $segments ); $i ++ ) {
                        $page = $rt_wiki_404_redirect->rtwiki_get_page_id( $segments[ $i ], $post_type );
                        if ( $page == null ){
                            if ( $i == 1 ){
                                $url = admin_url( 'post-new.php?post_type=' . $post_type . '&rtpost_title=' . $segments[ $i ] );
                            } else {
                                $pid      = $i - 1;
                                $parentId = $rt_wiki_404_redirect->rtwiki_get_page_id( $segments[ $pid ], $post_type );
                                $url      = admin_url( 'post-new.php?post_type=' . $post_type . '&rtpost_title=' . $segments[ $i ] . '&rtpost_parent=' . $parentId );
                            }
                            break;
                        }
                    }
                    $url = filter_var( $url, FILTER_SANITIZE_URL );
                    $url = "<p style='margin: inherit;'><a href='" . $url . "'>" . __( 'Click here. ', 'rtCamp' ) . '</a>' . __( 'If you want to add this post', 'rtCamp' ) . '</p>';
                }
            }

            return $url;
        }
    }
}




