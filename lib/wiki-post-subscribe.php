<?php

/*
 * 
 * Checks if subscribe ID is in the list or not 
 * 
 */

function checkSubscribe() {
    global $post;
    $subscriberList = get_post_meta($post->ID, 'subcribers_list', true);
    $userId = get_current_user_id();
    if (in_array($userId, $subscriberList, true)) {
        return true;
    } else {
        return false;
    }
}

/*
 * 
 * Update subscriber list meta value for the particular post ID. 
 * 
 */

function update() {
    if (isset($_REQUEST['subscribe']) == '1') {
        $id = $_POST['update-postId'];
        $userId = get_current_user_id();
        $subscribeId = get_post_meta($id, 'subcribers_list', true);
        $subscribeId[] = $userId;
        update_post_meta($id, 'subcribers_list', $subscribeId);
    }
}

add_action('wp', 'update');


/*
 * 
 * Update subscriber list meta value for the Sub Pages. 
 * 
 */

function updateForAllSubPages() {
    if (isset($_REQUEST['allSubscribe']) == '1') {
        $id = $_POST['update-all-postId'];

        $userId = get_current_user_id();
        subcribeSubPages($id, 0, $userId);
    }
}

add_action('wp', 'updateForAllSubPages');

function subcribeSubPages($parentId, $lvl, $userId) {
    $args = array('parent' => $parentId, 'post_type' => 'wiki');
    $pages = get_pages($args);

    if ($pages) {
        $lvl++;
        foreach ($pages as $page) {

            $permission = getPermission($page->ID);

            if ($permission == true) {
                $subscribeId = get_post_meta($page->ID, 'subcribers_list', true);
                if (!in_array($userId, $subscribeId, true)) {
                    $subscribeId[] = $userId;
                }

                update_post_meta($page->ID, 'subcribers_list', $subscribeId);
            }
            subcribeSubPages($page->ID, $lvl, $userId);
        }
    }
}

function ifSubPages($parentId) {
    $args = array('parent' => $parentId, 'post_type' => 'wiki');
    $pages = get_pages($args);

    if ($pages)
        return true;
    else
        return false;
}

/*
 * 
 * Send mail On post Update having body as diff of content  
 * 
 */

function post_changes_send_mail() {
    if (get_query_var('post_type') == 'wiki') {
        $post_id = absint($_POST['post']);
        $post = get_post($post_id);
        $revision = wp_get_post_revisions($post->ID);
        $textContent = array();
        $textTitle = array();

        foreach ($revision as $revisions) {
            $textContent[] = $revisions->post_content;
            $textTitle[] = $revisions->post_title;
        }

        $args = array(
            'title' => 'Differences',
            'title_left' => $textTitle[1],
            'title_right' => $textTitle[0],
        );
        $diff_table = wp_text_diff($textContent[1], $textContent[0], $args);

        add_filter('wp_mail_content_type', 'set_html_content_type');

        $subscriberList = get_post_meta($post->ID, 'subcribers_list', true);
        foreach ($subscriberList as $subscriber) {
            $user = get_user_by('id', $subscriber);

            wp_mail($user->user_email, 'Diff', $diff_table);
        }
        remove_filter('wp_mail_content_type', 'set_html_content_type');
    }
}

//add_action('edit_post', 'post_changes_send_mail');

function set_html_content_type() {

    return 'text/html';
}

function getPermission($pageID) {

    $noflag = 0;
    $noGroup = 0;
    $user = get_current_user_id();
    $terms = get_terms('user-group', array('hide_empty' => false));

    $access_rights = get_post_meta($pageID, 'access_rights', true);

    if ($access_rights['all']['w'] == '1') {
        return true;
    } else if ($access_rights['all']['r'] == '1') {
        return true;
    } else if ($access_rights['all']['na'] == '1') {
        return false;
    }


    foreach ($terms as $term) {

        $ans = get_term_if_exists($term->slug, $user);

        if ($ans == $term->slug) {


            if ($access_rights[$ans]['w'] == '1') {
                return true;
            } else if ($access_rights[$ans]['r'] == '1') {
                return true;
            } else if ($access_rights[$ans]['na'] == '1') {
                $noflag = 1;
            }
        } else if ($ans == '' || $ans == null) {
            $noGroup = 1;
        }
    }
    if ($noflag == 1) {
        return false;
    }
    if ($noGroup == 1) {
        return false;
    }
}