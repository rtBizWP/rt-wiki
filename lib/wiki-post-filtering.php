<?php

function single_post_filtering() {
    global $post;

    $noflag = 0;
    $noGroup = 0;
    $readOnly = 0;
    $user = get_current_user_id();

    $terms = get_terms('user-group', array('hide_empty' => false));
    
    $access_rights = get_post_meta($post->ID, 'access_rights', true);

    if ($access_rights['all']['w'] == '1') {
        return $post->post_content;
    } else if ($access_rights['all']['r'] == '1') {
        show_admin_bar(false);
        return $post->post_content;
    } else if ($access_rights['all']['na'] == '1') {
        wp_redirect(home_url());
        wp_die(__('You Do not have permission to access this Content'));
    }


    foreach ($terms as $term) {
        $ans = get_term_if_exists($term->slug, $user);
        
        if ($ans == $term->slug) {
            
            if ($access_rights[$ans]['w'] == '1') {
               
                return $post->post_content;
            } else if ($access_rights[$ans]['r'] == '1') {
                $readOnly = 1;
            } else if ($access_rights[$ans]['na'] == '1') {
                $noflag = 1;
            }
        } else if ($ans == '' || $ans == null) {
            $noGroup = 1;
        }
    }
     if ($readOnly == 1) {
        show_admin_bar(false);
        return $post->post_content;
    }
    else if ($noflag == 1) {

        wp_redirect(home_url());
        wp_die(__('You Do not have permission to access this Content'));
        //return false;
    }
    else if ($noGroup == 1) {

        wp_redirect(home_url());
        wp_die(__('No Permissions found to access this Content'));
    }
   
}

function get_term_if_exists($term, $userid) {

    global $wpdb;
    $query = "SELECT slug FROM $wpdb->terms WHERE term_id IN(SELECT term_id from $wpdb->term_taxonomy WHERE term_taxonomy_id IN(SELECT term_taxonomy_id from $wpdb->term_relationships WHERE object_id=$userid))and name='" . $term . "'";
    $page_id = $wpdb->get_var($query);
    return $page_id;
}

