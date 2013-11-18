<?php
function single_post_filtering() {
    global $post;
    $rflag = 0;
    $wflag = 0;
    $noflag = 0;
    $user = get_current_user_id();
    $terms = get_terms('user-group', array('hide_empty' => false));
    $access_rights = get_post_meta($post->ID, 'access_rights', true);

    foreach ($terms as $term) {
        $ans = get_term_if_exists($term->slug, $user);
        if ($ans == $term->slug) {

            if ($access_rights[$ans]['w'] == '1') {

                $wflag = 1;
                return $post->post_content;
                break;
            } else if ($access_rights[$ans]['r'] == '1') {
               return $post->post_content;
                $rflag = 1;
                break;
            } else if ($access_rights[$ans]['na'] == '1') {
                  //wp_redirect( home_url() ); 
                  //wp_die(__('You Do not have permission to access this Content'));
                  //return 0;
                  //break;
                $noflag = 1;
                
            }
        } else if ($ans == '') {
             wp_redirect( home_url() ); 
             wp_die(__('You Do not have permission to access this Content or access rights for you are not set'));
             return 0;
             break;
             
        }
     if(noflag==1)
    {
       
        
    }     
        
    }
    
   
}


function get_term_if_exists($term, $userid) {

    global $wpdb;
    $query = "SELECT slug FROM $wpdb->terms WHERE term_id IN(SELECT term_id from $wpdb->term_taxonomy WHERE term_taxonomy_id IN(SELECT term_taxonomy_id from $wpdb->term_relationships WHERE object_id=$userid))and name='" . $term . "'";
    $page_id = $wpdb->get_var($query);
    return $page_id;
}
