<?php

/**
 * Checks if subscribe ID is in the list or not 
 * @global type $post
 * @return boolean
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

/**
 * Update subscriber list meta value for the particular post ID. 
 * @global type $pagenow
 */
function update() {
    global $pagenow;

    if (isset($_REQUEST['subscribe']) == '1') {


        $params = array_keys($_REQUEST);  //get the keys from request parameter
        $actionParam = $params[0];
        $postID = $_REQUEST['update-postId'];  //get post id from the request parameter
        $url = get_permalink($postID);      //get permalink from post id
        $redirectURl = $url . '?' . $actionParam . '=1'; //form the url


        if (!is_user_logged_in() && $pagenow != 'wp-login.php') {
            wp_redirect(wp_login_url($redirectURl), 302); //after login and if permission is set , user would be subscribed to the page
        } else {
            if (isset($_POST['update-postId'])) {
                $id = $_POST['update-postId'];
                $userId = get_current_user_id();
                $subscribeId = get_post_meta($id, 'subcribers_list', true);
                pageSubscription($id, $userId, $subscribeId);
              }
        }
    }
}

add_action('wp', 'update');

/**
 * Update subscriber list meta value for the Sub Pages. 
 * @global type $pagenow
 */
function updateForAllSubPages() {
    global $pagenow;

    if (isset($_REQUEST['allSubscribe']) == '1') {

        $params = array_keys($_REQUEST);  //get the keys from request parameter
        $actionParam = $params[0];
        $postID = $_REQUEST['update-postId'];  //get post id from the request parameter
        $url = get_permalink($postID);      //get permalink from post id
        $redirectURl = $url . '?' . $actionParam . '=1'; //form the url


        if (!is_user_logged_in() && $pagenow != 'wp-login.php') {
            wp_redirect(wp_login_url($redirectURl), 302); //after login and if permission is set , user would be subscribed to the page and its subpages
        } else {
            $id = $_POST['update-all-postId'];
            $userId = get_current_user_id();
            $subpagesTrackingList = get_post_meta($id, 'subpages_tracking', true);
            $pageSubsciptionList = get_post_meta($id, 'subcribers_list', true);
            
            pageSubscription($id, $userId, $pageSubsciptionList);
            subPageSubscription($id, $userId, $subpagesTrackingList);
            
            subcribeSubPages($id, 0, $userId);
        }
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
                $subpagesTrackingList = get_post_meta($page->ID, 'subpages_tracking', true);
                
                pageSubscription($page->ID, $userId, $subscribeId);
                subPageSubscription($page->ID, $userId, $subpagesTrackingList);
                
            }
            subcribeSubPages($page->ID, $lvl, $userId);
        }
    }
}

/**
 * Check if pages have any sub pages/child page
 * 
 * @param type $parentId
 * @return boolean
 */
function ifSubPages($parentId,$post_type ='wiki') {
    
    $args = array('parent' => $parentId, 'post_type' => $post_type);
    $pages = get_pages($args);

    if ($pages)
        return true;
    else
        return false;
}

function rt_wiki_subpages_check($parentId, $subPage) {
    $args = array('parent' => $parentId, 'post_type' => 'wiki');
    $subPageFlag = $subPage;
    $pages = get_pages($args);
    if ($pages) {
        foreach ($pages as $page) {
            $permission = getPermission($page->ID);

            if ($permission == true) {
                return true;
            } else {
                $subPageFlag = false;
            }
            getSubPages($page->ID, $subPageFlag);
        }
        if ($subPageFlag == false)
            return false;
    }
}

/**
 * Send mail On post Update having body as diff of content 
 * 
 * @global type $post
 * @param type $post
 * @param type $email
 */
function post_changes_send_mail($postID, $email ,$group) {

    $revision = wp_get_post_revisions($postID);
    $content = array();
    $title = array();

    //$currentDate = date('Y-m-d');

    foreach ($revision as $revisions) {
        $content[] = $revisions->post_content;
        $title[] = $revisions->post_title;
    }

    $args = array(
        'title' => 'Differences',
        'title_left' => $title[1],
        'title_right' => $title[0],
    );
    if (!empty($content)) {

        $diff_table = wp_text_diff($content[1], $content[0], $args);

        add_filter('wp_mail_content_type', 'set_html_content_type');

        $subject .= 'Message:Update for  >>> "' . strtoupper(get_the_title($postID)) .'" You are getting these updates as you belong to "'. $group .'" group';
        $subject .=':Time: ' . date("F j, Y, g:i a");
        $headers[] = 'From: rtcamp.com <no-reply@' . sanitize_title_with_dashes(get_bloginfo('name')) . '.com>';

        wp_mail($email, $subject, $diff_table, $headers);

        remove_filter('wp_mail_content_type', 'set_html_content_type');        
    }
}

//add_action('wp', 'post_changes_send_mail');

function set_html_content_type() {

    return 'text/html';
}
