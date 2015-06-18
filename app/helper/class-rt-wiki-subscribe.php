<?php
/**
 * Don't load this file directly!
 */
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Helper functions for RtWiki Post subscribe
 *
 * @author     Dipesh
 */

if ( !class_exists( 'Rt_Wiki_Subscribe' ) ) {
    /**
     * Class Rt_Wiki_Subscribe
     */
    class Rt_Wiki_Subscribe {
        /**
         * Object initialization
         */
        public function __construct() {
            $this->hook();
        }

        /**
         * Apply Filter for Wiki
         */
        function hook(){
            //update subscribe entry
            add_action( 'wp', array( $this, 'update_subscribe' ) );
            add_action( 'save_post', array( $this, 'send_mail_postupdate_wiki' ), 99, 1 );
            add_action( 'pre_post_update', array( $this, 'wiki_pre_post_update' ) );
        }

        /**
         * Update subscriber list meta value for the particular post ID.
         *
         * @global type $pagenow
         */
        function update_subscribe()
        {
            global $pagenow;

            if ( isset( $_REQUEST[ 'PageSubscribe' ] ) == '1' ){
                $params      = array_keys( $_REQUEST ); //get the keys from request parameter
                $actionParam = $params[ 0 ];
                $postID      = $_REQUEST[ 'update-postId' ]; //get post id from the request parameter
                $url         = get_permalink( $postID ); //get permalink from post id
                $redirectURl = $url . '?' . $actionParam . '=1'; //form the url
                $flagsuccess=0;

                if ( ! is_user_logged_in() && $pagenow != 'wp-login.php' ){
                    wp_redirect( wp_login_url( $redirectURl ), 302 ); //after login and if permission is set , user would be subscribed to the page
                } else {

                    //Single post subscribe
                    $singleStatus = '';
                    if ( isset( $_REQUEST[ 'single_subscribe' ] ) ) $singleStatus = $_REQUEST[ 'single_subscribe' ];

                    //Post type
                    $post_type = 'post';
                    if ( isset( $_REQUEST[ 'post-type' ] ) ) $post_type = $_REQUEST[ 'post-type' ];

                    //subscribe or unsubscribe single post
                    if ( isset( $_REQUEST[ 'single_subscribe' ] ) && !empty( $_REQUEST[ 'single_subscribe' ] ) ){
                        if ( $_REQUEST[ 'single_subscribe' ] == 'current' ){
                            if ( isset( $_REQUEST[ 'subPage_subscribe' ] ) && $_REQUEST[ 'subPage_subscribe' ] == 'subpage' ) :
                                $this->subscribe_post_curuser( $postID, 1 );
                                $flagsuccess=1;
                            else :
                                $this->subscribe_post_curuser( $postID, 0 );
                                $flagsuccess=2;
                            endif;
                        }
                    } else if ( !isset( $_REQUEST[ 'single_subscribe' ] ) || empty( $_REQUEST[ 'single_subscribe' ] ) ){
                        if ( isset( $_REQUEST[ 'subPage_subscribe' ] ) && $_REQUEST[ 'subPage_subscribe' ] == 'subpage' ):
                            $this->subscribe_post_curuser( $postID, 1 );
                            $flagsuccess=1;
                        else :
                            $this->unsubscribe_post_curuser( $postID );
                            $flagsuccess=3;
                        endif;
                    }
                }
                wp_safe_redirect( add_query_arg( array('success_massage' => $flagsuccess), wp_get_referer() ))  ;
            }
        }

        /**
         * Function Called after a Wiki post is Upated
         * Sends Email to Subscribers of Wiki Posts
         *
         * @param type  $post
         */
        function send_mail_postupdate_wiki( $post )
        {
            global $rt_wiki_subscribe_model, $wiki_post_old_value;

            $newPostObject = get_post( $post );
            $supported_posts = rtwiki_get_supported_attribute();

            if ( in_array( $newPostObject->post_type, $supported_posts, true ) ){
                $subscribersList = $rt_wiki_subscribe_model->get_Subscribers( $newPostObject->ID );
                $subscribersList = array_unique( array_merge( $subscribersList, $rt_wiki_subscribe_model->get_parent_subpost_subscribers( $this->get_all_parent_ids( get_post( $newPostObject->post_parent ), '' ) ) ) );
                if ( ! empty( $subscribersList ) || $subscribersList != null ){
                    $this->wiki_page_changes_send_mail($post, $subscribersList );
                }
            }
        }

        /**
         * Send mail On post Update having body as diff of content
         *
         * @param type   $postID
         * @param type   $email    : Email id of user
         * @param type   $tax_diff : body of mail
         * @param string $url      : url of post
         */
        function wiki_page_changes_send_mail( $postID, $subscribersList )
        {
            global $wiki_post_old_value,$rt_wiki_diff,$rt_wiki_post_filtering;

            if ( isset($postID) ){

                $newPostObject = get_post( $postID );

                $headers[ ] = 'From: rtcamp.com <no-reply@' . sanitize_title_with_dashes( get_bloginfo( 'name' ) ) . '.com>';

                $subject = 'Updates for "' . strtoupper( $newPostObject->post_title )."'";

                if ( $wiki_post_old_value->post_content != $newPostObject->post_content ){
                    $body = $rt_wiki_diff->rtwiki_text_diff( $wiki_post_old_value->post_title, $newPostObject->post_title, $wiki_post_old_value->post_content, $newPostObject->post_content );
                }
                if ( $wiki_post_old_value->post_parent != $newPostObject->post_parent ){
                    $body .= '<p>Parent page chnaged</p>';
                    if( isset( $wiki_post_old_value->post_parent ) && !empty( $wiki_post_old_value->post_parent ) && $wiki_post_old_value->post_parent != 0 ){
                        $body .= '<p>Old Parent: ' . get_permalink(  $wiki_post_old_value->post_parent ) . '</p>';
                    }else{
                        $body .= "<p>Old Parent: No parent, Old post was 'Base Parent'.</p>";
                    }

                    if( isset( $newPostObject->post_parent ) && !empty( $newPostObject->post_parent ) && $newPostObject->post_parent != 0 ){
                        $body .= '<p>New Parent: ' . get_permalink( $newPostObject->post_parent ).'</p>';
                    }else{
                        $body .= "<p>New Parent: No parent, New Post is become 'Base Parent'.</p>";
                    }
                }

                $url  = 'Page Link: ' . get_permalink( $newPostObject->ID );
                $finalBody = $url . '<br/><br/>' . $body;

                add_filter( 'wp_mail_content_type', array( $this, 'set_html_content_type' ) );

                if ( isset($body) && !empty( $body ) && isset( $url ) && !empty( $url ) ){

                    foreach ( $subscribersList as $subscriber ) {
                        $user_info = get_userdata( $subscriber );
                        if ( $rt_wiki_post_filtering->get_permission( $newPostObject->ID, $user_info->ID ) ){
                            wp_mail( $user_info->user_email, $subject, $finalBody, $headers );
                        }
                    }

                }

                remove_filter( 'wp_mail_content_type', array( $this, 'set_html_content_type' ) );
            }
        }

        /**
         * get html content type
         *
         * @return String
         */
        function set_html_content_type()
        {
            return 'text/html';
        }

        /**
         * Check if current user subcribe for globle post
         *
         * @global type $post
         * @global type $rt_wiki_subscribe_model
         *
         * @param type  $userid
         *
         * @return boolean
         */
        function is_post_subscribe_cur_user( $userid )
        {
            global $post, $rt_wiki_subscribe_model;
            if ( $rt_wiki_subscribe_model->is_post_subscibe( $post->ID, $userid ) ){
                return true;
            } else if ( isset( $post->post_parent ) ){
                return $this->is_subpost_subscribe( get_post( $post->post_parent ), $userid );
            }
        }

        /**
         * Check if any parent post of given post subcribe by user
         *
         * @global type $rt_wiki_subscribe_model
         *
         * @param type  $post
         * @param type  $userid
         *
         * @return boolean
         */
        function is_subpost_subscribe( $post, $userid )
        {
            global $rt_wiki_subscribe_model;
            if ( $rt_wiki_subscribe_model->is_subpost_subscibe( $post->ID, $userid ) ){
                return true;
            }
            if ( isset( $post->post_parent ) && $post->post_parent != 0 ){
                return $this->is_subpost_subscribe( get_post( $post->post_parent ), $userid );
            } else {
                return false;
            }
        }


        /**
         * Function for sunscribe given post for current user
         *
         * @global type $rt_wiki_subscribe_model
         *
         * @param type  $postid
         * @param type  $is_sub_subscribe : flag if you want to subscribe sub page
         */
        function subscribe_post_curuser( $postid, $is_sub_subscribe )
        {
            global $rt_wiki_subscribe_model;
            $userid = get_current_user_id();
            if ( ! $rt_wiki_subscribe_model->is_post_subscibe( $postid, $userid ) ){
                $subscriber = array(
                    'attribute_postid' => $postid,
                    'attribute_userid' => $userid,
                    'attribute_sub_subscribe' => $is_sub_subscribe,
                );
                $rt_wiki_subscribe_model->add_subscriber( $subscriber );
            } else {
                $this->unsubcribe_subpost_curuser( $postid, $is_sub_subscribe );
            }
        }

        /**
         * Function to unsubscribe/remove userid from subscriptionsub page list
         *
         * @global type $rt_wiki_subscribe_model
         *
         * @param type  $postid
         * @param type  $is_sub_subscribe
         */
        function unsubcribe_subpost_curuser( $postid, $is_sub_subscribe )
        {
            global $rt_wiki_subscribe_model;
            $userid = get_current_user_id();
            if ( $rt_wiki_subscribe_model->is_post_subscibe( $postid, $userid ) ){
                $subscriber      = array( 'attribute_sub_subscribe' => $is_sub_subscribe, );
                $subscriberWhere = array( 'attribute_postid' => $postid, 'attribute_userid' => $userid, );

                $rt_wiki_subscribe_model->update_subscriber( $subscriber, $subscriberWhere );
            }
        }

        /**
         * Function to unsubscribe/remove userid from subscription list
         *
         * @param type $postid
         * @global type $rt_wiki_subscribe_model
         *
         * @internal param \type $userid
         */
        function unsubscribe_post_curuser( $postid )
        {
            global $rt_wiki_subscribe_model;

            $userid = get_current_user_id();

            if ( $rt_wiki_subscribe_model->is_post_subscibe( $postid, $userid ) ){

                $subscriber = array( 'attribute_postid' => $postid, 'attribute_userid' => $userid );
                $rt_wiki_subscribe_model->delete_subscriber( $subscriber );
            }
        }

        /**
         * Check if pages have any sub pages/child page [wiki-widgets]
         *
         * @param type $parentId
         * @param string|\type $post_type
         *
         * @return boolean
         */
        function if_sub_pages( $parentId, $post_type = 'post' )
        {

            $args  = array( 'parent' => $parentId, 'post_type' => $post_type );
            $pages = get_pages( $args );

            if ( $pages ) return true; else
                return false;
        }

        /**
         * Check permission of sub pages/child page [wiki-widgets]
         *
         * @param type $parentId
         * @param type $subPage : flag for subpage
         * @param string|\type $post_type
         *
         * @return boolean
         */
        function rt_wiki_subpages_check( $parentId, $subPage, $post_type = 'post' )
        {
            global $rt_wiki_post_filtering;
            $args        = array( 'parent' => $parentId, 'post_type' => $post_type );
            $subPageFlag = $subPage;
            $pages       = get_pages( $args );
            if ( $pages ){
                foreach ( $pages as $page ) {
                    $permission = $rt_wiki_post_filtering->get_permission( $page->ID, get_current_user_id() );
                    if ( $permission == true ){
                        return true;
                    } else {
                        $subPageFlag = false;
                    }
                    getSubPages( $page->ID, $subPageFlag );
                }
                if ( $subPageFlag == false ) return false;
            }
        }


        /**
         * get all parent id of perticular post
         *
         * @param type $post
         * @param type $postids
         *
         * @return string
         */
        function get_all_parent_ids( $post, $postids )
        {
            if ( ! isset( $postids ) ){
                $postids = '';
            }
            if ( is_object( $post ) ){
                $postids = $postids . $post->ID . ',';
                if ( isset( $post->post_parent ) && $post->post_parent != 0 ){
                    return $this->get_all_parent_ids( get_post( $post->post_parent ), $postids );
                }
            }

            return $postids;
        }

        /**
         * Store old value of wiki docs.
         *
         * @param $post_ID
         * @internal param $post
         */
        function wiki_pre_post_update( $post_ID ){

            global $wiki_post_old_value,$current_user;
            $supported_posts = rtwiki_get_supported_attribute();
            if ( in_array( get_post_type(), $supported_posts ) ) {
                $posttype = get_post_type();
                if( ! rtbiz_get_access_role_cap( RT_WIKI_TEXT_DOMAIN, 'admin' )  && (!isset( $_REQUEST['parent_id'] ) || empty( $_REQUEST['parent_id'] ) ) ){
                    WP_DIE( __( "You don't have enough access rights to create root post" ) . "<br><a href='edit.php?post_type=$posttype'>" . __( 'Go Back', 'rtCamp' ) . '</a>' );
                }
                $post_meta=get_post( $post_ID );
                $supported_posts = rtwiki_get_supported_attribute();
                if ( in_array( $post_meta->post_type, $supported_posts, true ) ){
                    $wiki_post_old_value = $post_meta;
                }
            }
        }

    }
}


