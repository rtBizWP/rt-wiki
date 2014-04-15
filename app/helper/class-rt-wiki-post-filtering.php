<?php
/**
 * Don't load this file directly!
 */
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Helper functions for rtWiki Post Filtering
 * Checks for the permission of each post at admin side and frontend side.
 *
 * @author     Dipesh
 */

if ( !class_exists( 'Rt_Wiki_Post_Filtering' ) ) {
    /**
     * Class RT_Wiki_Post_Filtering
     */
    class Rt_Wiki_Post_Filtering {
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
            //Back-end  Filter
            add_action( 'wp_trash_post', array( $this, 'wiki_wp_trash_post') );
            add_action( 'before_delete_post', array( $this, 'wiki_delete_post' ) );
            //add_action( 'admin_init', array( $this, 'post_check') );

            //trash bulk action remove for wikiwriter
            $supported_posts = rtwiki_get_supported_attribute();
            foreach( $supported_posts as $supported_post ){
                add_filter( 'bulk_actions-edit-'.$supported_post, array( $this, 'remove_wiki_bulk_actions' ) );
            }

            add_filter( 'page_row_actions', array( $this, 'filter_wiki_quick_action' ), 10 );

            add_filter( 'user_has_cap', array( $this, 'filter_caps' ), 999, 4 );

            //Front-end filter
            add_action( 'the_posts', array( $this, 'rtwiki_search_filter' ) );
            add_filter( 'the_content', array( $this, 'rtwiki_content_filter' ) );
            add_filter( 'edit_post_link', array( $this, 'rtwiki_edit_post_link_filter' ) );
            add_filter( 'comments_array', array( $this, 'rtwiki_comment_filter' ), 10, 2 );
            add_filter( 'comments_open', array( $this, 'rtwiki_comment_form_filter' ), 10, 2 );

            /* Function to disable feeds for wiki CPT */
            remove_action( 'do_feed_rdf', 'do_feed_rdf', 10, 1 );
            remove_action( 'do_feed_rss', 'do_feed_rss', 10, 1 );
            remove_action( 'do_feed_rss2', 'do_feed_rss2', 10, 1 );
            remove_action( 'do_feed_atom', 'do_feed_atom', 10, 1 );

            // Now we add our own actions, which point to our own feed function
            add_action( 'do_feed_rdf', array( $this, 'filter_wiki_do_feed' ), 10, 1 );
            add_action( 'do_feed_rss', array( $this, 'filter_wiki_do_feed' ), 10, 1 );
            add_action( 'do_feed_rss2', array( $this, 'filter_wiki_do_feed' ), 10, 1 );
            add_action( 'do_feed_atom', array( $this, 'filter_wiki_do_feed' ), 10, 1 );

            //Yoast plugin Sitemap rtWiki filtering
            //add_filter( 'wpseo_sitemaps_supported_taxonomies', array( $this, 'rtwiki_sitemap_taxonomies' ) );
            add_filter( 'wpseo_sitemaps_supported_post_types', array( $this, 'rtwiki_sitemap_posttypes' ) );

            //Wiki Parent page filtering
            add_filter( 'get_pages', array( $this, 'rtwiki_get_pages' ) );
            add_filter( 'page_attributes_dropdown_pages_args', array( $this, 'rtwiki_dropdown_pages' ) );

        }


        /**
         * Checks the permission of the Logged in User for editing post
         *
         * @param $pageID
         * @return bool|string
         */
        function get_admin_panel_permission( $pageID )
        {
            global $current_user;
            $base_parent = get_post_meta( $pageID, 'base_parent', true );
            $access_rights = get_post_meta( $base_parent , 'access_rights', true );

            if ( isset( $access_rights ) ){

                if ( ! is_user_logged_in() ){

                    if ( isset( $access_rights[ 'public' ] ) && 1 == $access_rights[ 'public' ] ){
                        return 'r';
                    }
                } else {

                    if ( current_user_can( rt_biz_get_access_role_cap( RT_WIKI_TEXT_DOMAIN, 'admin' ) )  ){
                        return 'a';
                    } else{

                        if ( isset( $access_rights[ 'public' ] ) && 1 == $access_rights[ 'public' ] ){
                            return 'r';
                        }elseif ( isset( $access_rights[ 'all' ] ) ){
                            if ( $access_rights[ 'all' ]== 2 ){
                                if ( current_user_can( rt_biz_get_access_role_cap( RT_WIKI_TEXT_DOMAIN, 'editor' ) )  || current_user_can( rt_biz_get_access_role_cap( RT_WIKI_TEXT_DOMAIN, 'author' ) ) ) {
                                    return 'w';
                                }
                                return 'r';
                            }elseif ( $access_rights[ 'all' ]== 1 ){
                                return 'r';
                            }
                        }else{
                            $terms = get_terms( 'user-group', array( 'hide_empty' => true ) );
                            $user_id  = get_current_user_id();
                            $noflag   = 0;

                            foreach ( $terms as $term ) {
                                $ans = get_term_if_exists( $term->slug, $user_id );
                                if ( $ans == $term->slug && isset( $access_rights[ $term->slug ] ) ){
                                    if ( $noflag < $access_rights[ $term->slug ] ){
                                        $noflag = $access_rights[ $term->slug ];
                                    }
                                }
                            }

                            if( $noflag == 2  ){
                                if ( current_user_can( rt_biz_get_access_role_cap( RT_WIKI_TEXT_DOMAIN, 'editor' ) )  || current_user_can( rt_biz_get_access_role_cap( RT_WIKI_TEXT_DOMAIN, 'author' ) ) ) {
                                    return 'w';
                                }
                                return 'r';
                            }elseif ( $noflag == 1 ){
                                return 'r';
                            }
                        }
                    }
                }
            }
            return false;
        }

        /**
         * Checks the permission of the user for the post(Frontend)
         *
         * @param $pageID
         * @param $userid
         * @param int $flag : function call by Daily scheduler (1) ot not (0)
         * @return bool
         */
        function get_permission( $pageID, $userid, $flag = 0 )
        {
            global $current_user;


            $base_parent = get_post_meta( $pageID, 'base_parent', true );
            $access_rights = get_post_meta( $base_parent , 'access_rights', true );

            if ( isset( $access_rights ) ){

                if ( ! is_user_logged_in() ){

                    if ( isset( $access_rights[ 'public' ] ) && 1 == $access_rights[ 'public' ] ){
                        return true;
                    }
                } else {

                    if ( current_user_can( rt_biz_get_access_role_cap( RT_WIKI_TEXT_DOMAIN, 'admin' ) )  ){
                        return true;
                    } else{

                        if ( isset( $access_rights[ 'public' ] ) && 1 == $access_rights[ 'public' ] ){
                            return true;
                        }elseif ( isset( $access_rights[ 'all' ] ) ){
                            if ( $access_rights[ 'all' ]== 2 || $access_rights[ 'all' ]== 1 ){
                                return true;
                            }
                        }else{
                            $terms = get_terms( 'user-group', array( 'hide_empty' => true ) );
                            $user_id  = get_current_user_id();
                            $noflag   = 0;

                            foreach ( $terms as $term ) {
                                $ans = get_term_if_exists( $term->slug, $user_id );
                                if ( $ans == $term->slug && isset( $access_rights[ $term->slug ] ) ){
                                    if ( $noflag < $access_rights[ $term->slug ] ){
                                        $noflag=$access_rights[ $term->slug ];
                                        if ( $noflag>0 )
                                            break;
                                    }
                                }
                            }
                            if( $noflag > 0 ){
                                return true;
                            }
                        }
                    }
                }
            }

            return false;
        }

        /**
         * check permission before move wiki to trash [bulk action]
         *
         * @param $post_id
         */
        function wiki_wp_trash_post( $post_id )
        {
            global $rt_wiki_subscribe;
            $post            = get_post( $post_id );
            $supported_posts = rtwiki_get_supported_attribute();
            $access = $this->get_admin_panel_permission( $post_id );
            if ( in_array( $post->post_type, $supported_posts ) &&  in_array( $post->post_status, array( 'publish', 'draft', 'future' ) ) ){
                if ( $access != 'a' ){
                    WP_DIE( __( "You don't have enough access rights to move '" . $post->post_title . "' page" ) . "<br><a href='edit.php?post_type=$post->post_type'>" . __( 'Go Back' , 'rtCamp' ) . '</a>' );
                }elseif ( $rt_wiki_subscribe->if_sub_pages( $post->ID, $post->post_type ) == true ){
                    WP_DIE( __( "You don't have enough access rights to move '" . $post->post_title . "' page, It has a child pages." ) . "<br><a href='edit.php?post_type=$post->post_type'>" . __( 'Go Back' , 'rtCamp' ) . '</a>' );
                }
            }
        }

        /**
         * check permission before delete wiki [bulk action]
         *
         * @param $post_id
         */
        function wiki_delete_post ( $post_id ){
            global $rt_wiki_subscribe;
            $post = get_post( $post_id );
            $supported_posts = rtwiki_get_supported_attribute();
            $access = $this->get_admin_panel_permission( $post_id );
            if( in_array( $post->post_type, $supported_posts ) ){
                if ( $access != 'a' ){
                    WP_DIE( __( "You don't have enough access rights to delete " . $post->post_title . "' page" ) . "<br><a href='edit.php?post_type=$post->post_type'>" . __( 'Go Back' , 'rtCamp' ) . '</a>' );
                }elseif ( $rt_wiki_subscribe->if_sub_pages( $post->ID, $post->post_type ) == true ){
                    WP_DIE( __( "You don't have enough access rights to delete '" . $post->post_title . "' page, It has a child Posts." ) . "<br><a href='edit.php?post_type=$post->post_type'>" . __( 'Go Back' , 'rtCamp' ) . '</a>' );
                }
            }
        }

        /**
         * check permission before edit wiki & delete wiki
         */
        function post_check()
        {
            if ( ! is_admin() ) return;

            if (isset( $_GET[ 'post' ] )){
                $page = $_GET[ 'post' ];
                $post = get_post( $page );
                $supported_posts = rtwiki_get_supported_attribute();
                $posttype = $post->post_type;
                $access = $this->get_admin_panel_permission( $page );
                if ( isset( $_GET[ 'action' ] ) && $_GET[ 'action' ] == 'edit' ){
                    if ( in_array( $posttype, $supported_posts ) ){
                        if ( $access != 'w' && $access != 'a' ){
                            WP_DIE( __( "You don't have enough access rights to modify '" . $post->post_title . "' page" ) . "<br><a href='edit.php?post_type=$posttype'>" . __( 'Go Back', 'rtCamp' ) . '</a>' );
                        }
                    }
                }
                if ( isset( $_GET[ 'action' ] ) && $_GET[ 'action' ] == 'trash' ){
                    if ( in_array( $posttype, $supported_posts ) ){
                        if ( $access != 'a' ){
                            WP_DIE( __( "You don't have enough access rights to move '" . $post->post_title . "' page" ) . "<br><a href='edit.php?post_type=$posttype'>" . __( 'Go Back', 'rtCamp' ) . '</a>' );
                        }
                    }
                }
            }
        }

        /**
         * remove bulk action of wiki for rt_wik_editor or rt_wiki_author
         *
         * @param $actions
         * @return null
         */
        function remove_wiki_bulk_actions( $actions ){
            global $current_user;
            if ( current_user_can( rt_biz_get_access_role_cap( RT_WIKI_TEXT_DOMAIN, 'editor' ) )  || current_user_can( rt_biz_get_access_role_cap( RT_WIKI_TEXT_DOMAIN, 'author' ) ) ){
                unset( $actions['trash'] );
            }
            return $actions;
        }

        /**
         * filter quick action from wiki
         *
         * @param type $actions
         * @return \type
         */
        function filter_wiki_quick_action( $actions )
        {
            global $post;
            $supported_posts = rtwiki_get_supported_attribute();
            if ( in_array( $post->post_type, $supported_posts ) ){
                $access = $this->get_admin_panel_permission( $post->ID );
                if ( $access == false ){
                    unset( $actions[ 'view' ] );
                }
            }

            return $actions;
        }

        /**
         * check permission for CPT and generate result[get_posts]
         *
         * @param $Posts
         * @return array
         * @internal param \type $wp_query
         */
        function rtwiki_search_filter( $Posts )
        {
            if ( isset( $Posts ) && ( is_search() || is_archive() ) && ! is_admin() ){
                $newpostArray    = array();
                $supported_posts = rtwiki_get_supported_attribute();
                foreach ( $Posts as $post ) {
                    if ( in_array( $post->post_type, $supported_posts ) ){
                        if ( $this->get_permission( $post->ID, get_current_user_id(), 0 ) ){
                            $newpostArray[ ] = $post;
                        }
                    } else {
                        $newpostArray[ ] = $post;
                    }
                }
                $Posts = $newpostArray;
            }

            return $Posts;
        }

        /**
         * check permission for CPT content.
         *
         * @param type $content
         *
         * @return type
         */
        function rtwiki_content_filter( $content )
        {
            $supported_posts = rtwiki_get_supported_attribute();
            if ( in_array( get_post_type(), $supported_posts ) ){
                if ( $this->get_permission( get_the_ID(), get_current_user_id(), 0 ) ){
                    $post_thumbnail = get_the_post_thumbnail();

                    return $post_thumbnail . $content;
                } else {
                    return '<p>' . __( 'Not Enough Rights to View The Content.', 'rtCamp' ) . '</p>';
                }
            }

            return $content;
        }

        /**
         * check permission for CPT edit content link.
         *
         * @param type $output
         *
         * @return string
         */
        function rtwiki_edit_post_link_filter( $output )
        {
            $supported_posts = rtwiki_get_supported_attribute();
            if ( in_array( get_post_type(), $supported_posts ) ){
                if ( $this->get_permission( get_the_ID(), get_current_user_id(), 0 ) ){
                    return $output;
                } else {
                    return '';
                }
            }

            return $output;
        }

        /**
         * check permission before cpt comment shows.
         *
         * @param type $comments
         * @param type $post_id
         *
         * @return type
         */
        function rtwiki_comment_filter( $comments, $post_id )
        {
            $supported_posts = rtwiki_get_supported_attribute();
            $post = get_post( $post_id );
            if ( in_array( $post->post_type, $supported_posts ) ){
                $rtwiki_settings = get_option( 'rtwiki_settings', array() );
                if ( isset( $rtwiki_settings[ 'wiki_comment' ] ) && $rtwiki_settings[ 'wiki_comment' ] == 'y' && $this->get_permission( $post_id, get_current_user_id(), 0 ) ){
                    return $comments;
                }
                return array();
            }

            return $comments;
        }

        /**
         * check permission before cpt comment form display.
         *
         * @param type $open
         * @param type $post_id
         *
         * @return boolean
         */
        function rtwiki_comment_form_filter( $open, $post_id )
        {
            $supported_posts = rtwiki_get_supported_attribute();
            $post            = get_post( $post_id );
            if ( in_array( $post->post_type, $supported_posts ) ){
                $rtwiki_settings = get_option( 'rtwiki_settings', array() );
                if ( isset( $rtwiki_settings[ 'wiki_comment' ] ) && $rtwiki_settings[ 'wiki_comment' ] == 'y' && $this->get_permission( $post_id, get_current_user_id(), 0 ) ){
                    return $open;
                }

                return false;
            }

            return $open;
        }

        /**
         * Finally, we do the post type & permission on CPT check, and generate feeds conditionally
         *
         * @global type $wp_query
         * @global type $post
         */
        function filter_wiki_do_feed()
        {
            global $wp_query, $post;
            $no_feed = array( 'wiki' );
            if ( in_array( $wp_query->query_vars[ 'post_type' ], $no_feed ) ){
                wp_die( __( 'This is not a valid feed address.', 'textdomain' ) );
            } else {
                $supported_posts = rtwiki_get_supported_attribute();
                $newpostArray    = array();
                if ( isset( $wp_query->posts ) ){
                    foreach ( $wp_query->posts as $post ) {
                        if ( in_array( get_post_type(), $supported_posts ) ){
                            if ( $this->get_permission( get_the_ID(), get_current_user_id(), 0 ) ){
                                $newpostArray[ ] = $post;
                            }
                        } else {
                            $newpostArray[ ] = $post;
                        }
                    }
                    $wp_query->posts       = $newpostArray;
                    $wp_query->post_count  = count( $newpostArray );
                    $wp_query->found_posts = count( $newpostArray );
                }
                if ( isset( $wp_query->comments ) ){
                    $newCommentArray = array();
                    foreach ( $wp_query->comments as $comment ) {
                        if ( in_array( get_post_type( $comment->comment_post_ID ), $supported_posts ) ){
                            if ( $this->get_permission( $comment->comment_post_ID, get_current_user_id(), 0 ) ){
                                $newCommentArray[ ] = $comment;
                            }
                        } else {
                            $newpostArray[ ] = $post;
                        }
                    }
                    $wp_query->comments      = $newCommentArray;
                    $wp_query->comment_count = count( $newpostArray );
                }
                do_feed_rss2( $wp_query->is_comment_feed );
            }
        }

        /**
         * @todo get_all_attributes posttype pass
         * sitemap taxonomies filter [Yoast plugin]
         *
         * @param $taxonomies
         * @return mixed
         */
        function rtwiki_sitemap_taxonomies( $taxonomies )
        {
            global $rt_attributes_model;
            $attributes = $rt_attributes_model->get_all_attributes();
            $customAttributes = array();
            foreach ( $attributes as $attribute ) {
                $customAttributes[ ] = $attribute->attribute_name;
            }
            foreach ( $taxonomies as $key => $val ) {
                if ( in_array( $key, $customAttributes ) ){
                    unset( $taxonomies[ $key ] );
                }
            }

            return $taxonomies;
        }

        /**
         * sitemap posttype filter [Yoast plugin]
         *
         * @param $posttypes
         * @return mixed
         */
        function rtwiki_sitemap_posttypes( $posttypes )
        {
            global $rtWikiAttributesModel;
            $attributes = rtwiki_get_supported_attribute();
            foreach ( $posttypes as $key => $val ) {
                if ( in_array( $key, $attributes ) ){
                    unset( $posttypes[ $key ] );
                }
            }

            return $posttypes;
        }

        /**
         * get_page filter for wiki
         *
         * @param $pages
         * @param $r
         * @return mixed
         */
        function rtwiki_get_pages($pages){
            $supported_posts = rtwiki_get_supported_attribute();
            if ( in_array( get_post_type(), $supported_posts ) ) {
                foreach( $pages as $key=>$argpage){
                    if (is_admin()){
                        $access=$this->get_admin_panel_permission( $argpage->ID );
                        if ( $access != 'a' && $access != 'w' ){
                            unset($pages[$key]);
                        }
                    }else{
                        if ( $this->get_permission( $argpage->ID, get_current_user_id(), 0 ) == false ){
                            unset($pages[$key]);
                        }
                    }
                }
            }
            return $pages;
        }

        /**
         * wiki page drop-down filter
         * @param $dropdown_args
         * @param $post
         * @return mixed
         */
        function rtwiki_dropdown_pages($dropdown_args){
            global $current_user;
            $supported_posts = rtwiki_get_supported_attribute();
            if ( in_array( get_post_type(), $supported_posts ) && ( current_user_can( rt_biz_get_access_role_cap( RT_WIKI_TEXT_DOMAIN, 'editor' ) )  || current_user_can( rt_biz_get_access_role_cap( RT_WIKI_TEXT_DOMAIN, 'author' ) ) ) ) {
                unset( $dropdown_args['show_option_none'] );
            }
            return $dropdown_args;
        }

        function filter_caps( $all_caps, $required_caps, $args, $user ) {
            global $post;
            if ( in_array( rt_biz_get_access_role_cap( RT_WIKI_TEXT_DOMAIN, 'admin' ) , $required_caps ) || in_array( rt_biz_get_access_role_cap( RT_WIKI_TEXT_DOMAIN, 'editor' ), $required_caps ) || in_array( rt_biz_get_access_role_cap( RT_WIKI_TEXT_DOMAIN, 'author' ), $required_caps ) ){
                return $all_caps;
            }

            $supported_posts = rtwiki_get_supported_attribute();
            if ( is_object( $post ) && in_array( $post->post_type, $supported_posts ) && $post->post_status != "auto-draft" && $post->post_status != "draft" ){
                $access = 'na';
                if ( is_object( $user )  &&  current_user_can( rt_biz_get_access_role_cap( RT_WIKI_TEXT_DOMAIN, 'admin' ) ) ){
                    return $all_caps;
                }
                $access = $this->get_admin_panel_permission( $post->ID );
                if( $access == 'r' ){
                    $all_caps["edit_wiki"] = false;
                    $all_caps["read_wiki"] = true;
                    $all_caps["delete_wiki"] = false;
                    /*$all_caps["edit_wikis"] = true;
                    $all_caps["edit_others_wikis"] = false;
                    $all_caps["publish_wikis"] = false;
                    $all_caps["read_private_wikis"] = true;
                    $all_caps["delete_wikis"] = false;
                    $all_caps["delete_private_wikis"] = false;
                    $all_caps["delete_published_wikis"] = false;
                    $all_caps["delete_others_wikis"] = false;
                    $all_caps["edit_private_wikis"] = false;
                    $all_caps["edit_published_wikis"] = false;*/
                }elseif ( 'w' == $access ){
                    $all_caps["edit_wiki"] = true;
                    $all_caps["read_wiki"] = true;
                    $all_caps["delete_wiki"] = false;
                    /*$all_caps["edit_wikis"] = true;
                    $all_caps["edit_others_wikis"] = true;
                    $all_caps["publish_wikis"] = true;
                    $all_caps["read_private_wikis"] = true;
                    $all_caps["delete_wikis"] = false;
                    $all_caps["delete_private_wikis"] = false;
                    $all_caps["delete_published_wikis"] = false;
                    $all_caps["delete_others_wikis"] = false;
                    $all_caps["edit_private_wikis"] = true;
                    $all_caps["edit_published_wikis"] = true;*/
                }elseif ( false == $access ){
                    $all_caps["edit_wiki"] = false;
                    $all_caps["read_wiki"] = false;
                    $all_caps["delete_wiki"] = false;
                   /* $all_caps["edit_wikis"] = true;
                    $all_caps["edit_others_wikis"] = false;
                    $all_caps["publish_wikis"] = false;
                    $all_caps["read_private_wikis"] = false;
                    $all_caps["delete_wikis"] = false;
                    $all_caps["delete_private_wikis"] = false;
                    $all_caps["delete_published_wikis"] = false;
                    $all_caps["delete_others_wikis"] = false;
                    $all_caps["edit_private_wikis"] = false;
                    $all_caps["edit_published_wikis"] = false;*/
                }
            }
            return $all_caps;
        }
    }
}
