
<?php

/**
 * 
 * Check for the page in wiki CPT. If not found(404), it would redirect to edit post page 
 * with title and parent properly set.
 * 
 * @global type $wpdb
 * @param type $name
 * @return type
 * 
 * 
 **/

function rtwiki_get_page_id($name) {
    global $wpdb;
    $page_id = $wpdb->get_var("SELECT ID FROM $wpdb->posts WHERE ( post_name = '" . $name . "' or post_title = '" . $name . "' ) and post_status = 'publish' and post_type='wiki' ");
    return $page_id;
}

add_action('template_redirect', 'redirect_404');

/*
 * Redirect to edit page when page not found in wiki with parent set 
 */

function redirect_404() {
      if (is_404() && get_query_var('post_type') == 'wiki') {
          
        $page = $_SERVER['REQUEST_URI'];
        $segments = explode('/', trim($page, '/'));
        if ($segments[0] == 'wiki') {
            $postid = '';
            for ($i = 1; $i < count($segments); $i++) {

                $page = rtwiki_get_page_id($segments[$i]);
                if ($i == 1) {
                    if ($page == null) {
                             $my_post1 = array(
                            'post_title' => $segments[$i],
                            'post_content' => '',
                            'post_status' => 'publish',
                            //'post_author' => 1,
                            'post_type' => 'wiki',
                            'slug' => $segments[$i],
                        );
                        $postid = wp_insert_post($my_post1);
                    }
                } else {

                    $pid = $i - 1; 
                    
                    $parentId = rtwiki_get_page_id($segments[$pid]);
                    if ($page == null)  {
                            $my_post = array(
                            'post_title' => $segments[$i],
                            'post_content' => '',
                            'post_status' => 'publish',
                            //'post_author' => 1,
                            'post_type' => 'wiki',
                            'slug' => $segments[$i],
                            'post_parent' => $parentId,
                        );
                        $postid = wp_insert_post($my_post);
                    }
                }
            }
            $url = admin_url('post.php?post=' . $postid . '&action=edit');
            wp_redirect($url);
        }
    }
}

