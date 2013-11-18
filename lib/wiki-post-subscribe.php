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
        $subscribeId = get_post_meta($id,'subcribers_list', true);
        $subscribeId[] = $userId;
        update_post_meta($id, 'subcribers_list', $subscribeId);
    }
}

add_action('init', 'update');

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
            wp_mail($subscriber, 'Diff', $diff_table);
        }
        remove_filter('wp_mail_content_type', 'set_html_content_type');
    }
}

//add_action('save_post', 'post_changes_send_mail');

function set_html_content_type() {

    return 'text/html';
}
