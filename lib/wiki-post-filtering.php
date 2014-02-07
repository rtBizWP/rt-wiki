<?php

/**
 * 
 * Checks for the permission of each post at admin side
 * and frontend side.Display content according to it
 * 
 */
//require (ABSPATH . WPINC . '/feed.php');

/*
 * Single Post Content Permission for Wiki CPT
 */

function single_post_filtering() {
    global $post;

    $noflag = 0;
    $noGroup = 0;
    $readOnly = 0;

    $user = get_current_user_id();
    $terms = get_terms('user-group', array('hide_empty' => true));
    $access_rights = get_post_meta($post->ID, 'access_rights', true);
    
    if (!is_user_logged_in()) {

        if ($access_rights['public']['r'] == 1) {
            return $post->post_content;
        }if ($access_rights['public']['na'] == 1) {
            wp_die(__('Please <a href=' . wp_login_url($post->guid) . '>Login</a> To View the post Content'));
        }
    } else {
        if ($post->post_author == $user) {

            return $post->post_content;
        } else {
            foreach ($terms as $term) {

                $ans = get_term_if_exists($term->slug, $user);

                if ($ans == $term->slug) {

                    if ($access_rights[$ans]['w'] == 1) {
                        return $post->post_content;
                    } else if ($access_rights[$ans]['r'] == 1) {
                        $readOnly = 1;
                    } else if ($access_rights[$ans]['na'] == 1) {
                        $noflag = 1;
                    }
                } else if ($ans == '' || $ans == null) {
                    $noGroup = 1;
                }
            }
            if ($readOnly == 1) {
                show_admin_bar(false);
                return $post->post_content;
            } else if ($noflag == 1) {

                wp_redirect(home_url());
                wp_die(__('You Do not have permission to access this Content....<a href=' . admin_url() . 'edit.php?post_type=wiki >Back to admin panel</a>'));
            } else if ($noGroup == 1) {
                wp_redirect(home_url());
                wp_die(__('No Permissions found to access this Content...<a href=' . admin_url() . 'edit.php?post_type=wiki >Back to admin panel</a>'));
            }

//        if ($access_rights['all']['w'] == 1) {
//            return $post->post_content;
//        } else if ($access_rights['all']['r'] == 1) {
//            show_admin_bar(false);
//            return $post->post_content;
//        } else if ($access_rights['all']['na'] == 1) {
//            
//            wp_redirect(home_url());
//            wp_die(__('You Do not have permission to access this Content'));
//        }
        }
    }
}

function get_term_if_exists($term, $userid) {

    global $wpdb;
    $query = "SELECT slug FROM $wpdb->terms WHERE term_id IN(SELECT term_id from $wpdb->term_taxonomy WHERE term_taxonomy_id IN(SELECT term_taxonomy_id from $wpdb->term_relationships WHERE object_id=$userid))and name='" . $term . "'";
    $page_id = $wpdb->get_var($query);
    return $page_id;
}

/*
 * removes quick edit from wiki post type
 */

function remove_quick_edit($actions) {
    global $post;
    $supported_posts = rtwiki_get_supported_attribute();
    if ( in_array( $post->post_type, $supported_posts ) ) {
        if (getAdminPanelSidePermission($post->ID) == false) {
            unset($actions['inline hide-if-no-js']);
            unset($actions['trash']);
            //unset($actions['view']);
            unset($actions['edit']);
        }
    }
    return $actions;
}

add_filter('page_row_actions', 'remove_quick_edit', 10);


/*
 * Check permissions at Admin side for edit post 
 */

function postCheck() {
    if (isset($_GET['action']) && $_GET['action'] == 'edit') {
        $page = isset( $_GET['post'] )?$_GET['post']:0;
        $supported_posts = rtwiki_get_supported_attribute();
        // $status = get_post_meta($page, '_edit_last');
        //if ($status[0] == '1') {
        if ( in_array( get_post_type($page), $supported_posts ) ) {
            if (getAdminPanelSidePermission($page) == false) {
                WP_DIE(__('You Dont have enough access rights to Edit this post'));
            }
            // }
        }
    }
}

add_action('admin_init', 'postCheck' );

function addCapabilities($capabilities, $cap, $args, $user) {
    global $post;
    $access = 'na';
    $supported_posts = rtwiki_get_supported_attribute();
    if ( is_object($post) && in_array( $post->post_type, $supported_posts ) ) {
        $access = getAdminPanelSidePermission($post->ID);
        if ( $access == false) {
            return array();
        }
    }
    
    if( is_object($user) && !empty($user->roles) && ( 'administrator' == $user->roles[0] ) ){ 
        return $user->allcaps;
    }
    
    if( is_object($post) && $post->post_author == get_current_user_id() ) {
        $capabilities = array_merge( $capabilities, array(
                'delete_posts' => 'delete_posts',
                'delete_others_posts' => 'delete_others_posts',
                'delete_post' => 'delete_post',
                'delete_published_posts' => 'delete_published_posts'
            )
        );
    }
    
    if( 'w' == $access ) {
        $capabilities = array_merge( $capabilities, array('read_post' => 'read_post',
                    'publish_posts' => 'publish_posts',
                    'edit_posts' => 'edit_posts',
                    'edit_others_posts' => 'edit_others_posts',
                    'read_private_posts' => 'read_private_posts',
                    'edit_post' => 'edit_post',
                    'edit_published_posts' => 'edit_published_posts',
            ) 
        );
    }
    else if( 'r' == $access ) {
        $capabilities = array_merge( $capabilities, array('read_post' => 'read_post',
                    'read_private_posts' => 'read_private_posts'
            ) 
        );
    }
    return $capabilities;
}

add_filter( 'user_has_cap', 'addCapabilities', 10, 4 );

/*
 * Checks the permission of the Logged in User for editing post
 */

function getAdminPanelSidePermission($pageID) {

    $noflag = 0;
    $noPublic = 0;
    global $current_user;
    $user = get_current_user_id();
    $terms = get_terms('user-group', array('hide_empty' => true));
    $access_rights = get_post_meta($pageID, 'access_rights', true);
    
    if( !is_user_logged_in() ) {
        if ( isset( $access_rights['public'] ) && ( 1 == $access_rights['public'] ) ) {
            return true;
        } else if ( isset( $access_rights['public'] ) && ( 0 == $access_rights['public'] ) ) {
            return false;
        }
    } else {
        
        $post_meta = get_post($pageID);

        if ( ( 'administrator' == $current_user->roles[0] ) || ( is_object($post_meta) && ( $post_meta->post_author == $user ) ) ) {
            return true;
        } else {
            if (empty($access_rights)) {
                return true;
            }
            foreach ($terms as $term) {

                $ans = get_term_if_exists($term->slug, $user);

                if ($ans == $term->slug && isset( $access_rights[$ans] ) ) {

                    if ( isset( $access_rights[$ans]['w'] ) && ( $access_rights[$ans]['w'] == 1 ) ) {
                        return 'w';
                    } else if ( isset( $access_rights[$ans]['r'] ) && ( $access_rights[$ans]['r'] == 1 ) ) {
                        return 'r';
                    } else if ( isset( $access_rights[$ans]['na'] ) && ( $access_rights[$ans]['na'] == 1 ) ) {
                        $noflag = 1;
                    }
                } else if ($ans == '' || $ans == null) {
                    $noPublic = 1;
                }
            }


            if( $noflag == 1 || $noPublic == 1 ) {
                return false;
            }
        }
    }
}

/*
 * Checks the permission of the user for the post(Frontend)
 */

function getPermission($pageID) {

    $noflag = 0;
    //$noGroup = 0;
    $noPublic = 0;
    $user = get_current_user_id();
    $terms = get_terms('user-group', array('hide_empty' => true));
    $access_rights = get_post_meta($pageID, 'access_rights', true);
    
    if ( isset( $access_rights['public'] ) && ( 1 == $access_rights['public'] ) ) {
        return true;
    } else if ( isset( $access_rights['public'] ) && !is_user_logged_in() && ( 0 == $access_rights['public'] ) ) {
        return false;
    }else if( is_user_logged_in() ) {
        $post_details = get_post($pageID);

        if ($post_details->post_author == $user) {
            return true;
        } else {
            foreach ($terms as $term) {
                $ans = get_term_if_exists($term->slug, $user);

                if ($ans == $term->slug && isset( $access_rights[$ans] )) {
                    if ( ( isset( $access_rights[$ans]['r'] ) && ( $access_rights[$ans]['r'] == 1 ) ) || ( isset( $access_rights[$ans]['w'] ) && ( $access_rights[$ans]['w'] == 1 ) ) ) {
                        return true;
                    } else if ( isset( $access_rights[$ans]['na'] ) && ( $access_rights[$ans]['na'] == 1 ) ) {
                        $noflag = 1;
                    }
                } else if ($ans == '' || $ans == null) {
                    $noPublic = 1;
                }
            }
            if ($noflag == 1) {
                return false;
            }
            if ($noPublic == 1) {
                return false;
            }

//        if ($access_rights['all']['w'] == 1 || $access_rights['all']['r'] == 1) {
//            return true;
//        } else if ($access_rights['all']['na'] == 1) {
//            return false;
//        }
        }
    }
}



add_filter('bulk_actions-' . 'edit-wiki', '__return_empty_array');


/*
 * Function to unsubscribe/remove userid from post meta
 */

function unSubscription($postid, $userid, $list) {
    if (in_array($userid, $list, true)) {

        if (($key = array_search($userid, $list)) !== false) {

            unset($list[$key]);
            $newSubpageTrackingList = $list;
            update_post_meta($postid, 'subcribers_list', $newSubpageTrackingList);
        }
    }
}

function subpageUnSubscription($postid, $userid, $list) {
    if (in_array($userid, $list, true)) {

        if (($key = array_search($userid, $list)) !== false) {
            unset($list[$key]);
            $newSubpageTrackingList = $list;
            update_post_meta($postid, 'subpages_tracking', $newSubpageTrackingList);
        }
    }
}



/*
 * Function to subscribe page and subpages 
 */

function pageSubscription($postid, $userid, $list) {
    if( empty( $list ) )
        $list = array();
    if (!in_array($userid, $list, true)) {
        $list[] = $userid;
        update_post_meta($postid, 'subcribers_list', $list);
    }
}

function subPageSubscription($postid, $userid, $list) {
    if( empty( $list ) )
        $list = array();
    if (!in_array($userid, $list, true)) {
        $list[] = $userid;
        update_post_meta($postid, 'subpages_tracking', $list);
    }
}





/* Function to disable feeds for wiki CPT */
remove_action('do_feed_rdf', 'do_feed_rdf', 10, 1);
remove_action('do_feed_rss', 'do_feed_rss', 10, 1);
remove_action('do_feed_rss2', 'do_feed_rss2', 10, 1);
remove_action('do_feed_atom', 'do_feed_atom', 10, 1);

// Now we add our own actions, which point to our own feed function
add_action('do_feed_rdf', 'my_do_feed', 10, 1);
add_action('do_feed_rss', 'my_do_feed', 10, 1);
add_action('do_feed_rss2', 'my_do_feed', 10, 1);
add_action('do_feed_atom', 'my_do_feed', 10, 1);

// Finally, we do the post type check, and generate feeds conditionally
function my_do_feed() {
    global $wp_query;
    $no_feed = array('wiki');
    if (in_array($wp_query->query_vars['post_type'], $no_feed)) {
        wp_die(__('This is not a valid feed address.', 'textdomain'));
    } else {
        do_feed_rss2($wp_query->is_comment_feed);
    }
}



/* Changing taxnonmy query for wiki CPT */
add_action('pre_get_posts', 'add_wiki_taxonomy');

function add_wiki_taxonomy($query) {
    //global $post;

    if (is_admin()) {
        return false;
    }

    if (is_archive() && is_tax()) {

        $tax = get_queried_object();
        $objectArray = get_taxonomy($tax->taxonomy);

        if (array_key_exists('object_type', $objectArray)) {
            $post_types = $objectArray->object_type;

            if (in_array('wiki', $post_types)) {
                if ($query->is_tax()) {
                    $query->set('post_type', 'wiki');
                }
            }
        }
    }
}

function rtwiki_content_filter ( $content ) {
    if( getPermission ( get_the_ID() ) ) {
        $post_thumbnail = get_the_post_thumbnail();
        return $post_thumbnail.$content;
    }
    else { 
        return '<p>'.__( 'Not Enough Rights to View The Content.', 'rtCamp' ).'</p>'; 
    }
}

add_filter( 'the_content', 'rtwiki_content_filter' );

function rtwiki_edit_post_link_filter( $output ) {
    if( getPermission ( get_the_ID() ) ) {
        return $output;
    }
    else { 
        return '';
    }
}
add_filter('edit_post_link', 'rtwiki_edit_post_link_filter');