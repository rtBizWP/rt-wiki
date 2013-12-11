<?php

/**
 * 
 * Checks for the permission of each post at admin side
 * and frontend side.Display content according to it
 * 
 */
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

        if ($post->post_author == $user)
            return $post->post_content;
        else {
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
    if ($post->post_type == 'wiki') {
        if (getAdminPanelSidePermission($post->ID) == false) {
            unset($actions['inline hide-if-no-js']);
            unset($actions['trash']);
            //unset($actions['view']);
            unset($actions['edit']);
        }
    }
    return $actions;
}

add_filter('page_row_actions', 'remove_quick_edit', 99);


/*
 * Check permissions at Admin side for edit post 
 */

function postCheck() {
    if (isset($_GET['action']) && $_GET['action'] == 'edit') {
        $page = $_GET['post'];

       // $status = get_post_meta($page, '_edit_last');

        //if ($status[0] == '1') {
            if (get_post_type($page) == 'wiki' && isset($_GET['message']) != 1) {
                if (getAdminPanelSidePermission($page) == false) {
                    WP_DIE(__('You Dont have enough access rights to Edit this post'));
                }
           // }
        }
    }
}

add_action('admin_init', 'postCheck');

/*
 * Checks the permission of the Logged in User for editing post
 */

function getAdminPanelSidePermission($pageID) {

    $noflag = 0;
    $readOnly = 0;
    $noPublic = 0;
    $user = get_current_user_id();
    $terms = get_terms('user-group', array('hide_empty' => true));
    $access_rights = get_post_meta($pageID, 'access_rights', true);

    if (!is_user_logged_in()) {
        if ($access_rights['public']['r'] == 1) {
            return true;
        } else if ($access_rights['public']['na'] == 1) {
            return false;
        }
    } else {

        $post_meta = get_post($pageID);
        if ($post_meta->post_author == $user)
            return true;
        else {


            if (empty($access_rights)) {
                return true;
            }
            foreach ($terms as $term) {

                $ans = get_term_if_exists($term->slug, $user);

                if ($ans == $term->slug) {

                    if ($access_rights[$ans]['w'] == 1) {
                        return true;
                    } else if ($access_rights[$ans]['r'] == 1) {
                        $readOnly = 1;
                    } else if ($access_rights[$ans]['na'] == 1) {
                        $noflag = 1;
                    }
                } else if ($ans == '' || $ans == null) {
                    $noPublic = 1;
                }
            }


            if ($noflag == 1 || $readOnly == 1 || $noPublic == 1) {
                return false;
            }


//        if (isset($access_rights['all']['w']) == 1) {
//            return true;
//        } else if (isset($access_rights['all']['r']) == 1) {
//            return false;
//        } else if (isset($access_rights['all']['na']) == 1) {
//            return false;
//        }
        }
    }
}

/*
 * Checks the permission of the user for the post(Frontend)
 */

function getPermission($pageID) {

    $noflag = 0;
    $noGroup = 0;
    $noPublic = 0;
    $user = get_current_user_id();
    $terms = get_terms('user-group', array('hide_empty' => true));
    $access_rights = get_post_meta($pageID, 'access_rights', true);

    if (!is_user_logged_in()) {
        if ($access_rights['public']['r'] == 1) {
            return true;
        } else if ($access_rights['public']['na'] == 1) {
            return false;
        }
    } else {

        $post_meta = get_post($pageID);
        if ($post_meta->post_author == $user)
            return true;
        else {
            foreach ($terms as $term) {
                $ans = get_term_if_exists($term->slug, $user);

                if ($ans == $term->slug) {
                    if ($access_rights[$ans]['w'] == 1 || $access_rights[$ans]['r'] == 1) {
                        return true;
                    } else if ($access_rights[$ans]['na'] == 1) {
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