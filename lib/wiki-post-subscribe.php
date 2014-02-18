<?php

/**
 * Check if current user subcribe for globle post
 * 
 * @global type $post
 * @global type $rtWikiSubscribe
 * @param type $userid
 * @return boolean
 */
function isPostSubscribeByCurUser($userid) {
    global $post, $rtWikiSubscribe;
    if ($rtWikiSubscribe->isPostSubscibeByUser($post->ID, $userid)) {
        return true;
    } else if (isset($post->post_parent)) {
        return isSubPostSubscribe(get_post($post->post_parent), $userid);
    }
}

/**
 * Check if any parent post of given post subcribe by user
 * 
 * @global type $rtWikiSubscribe
 * @param type $post
 * @param type $userid
 * @return boolean
 */
function isSubPostSubscribe($post, $userid) {
    global $rtWikiSubscribe;
    if ($rtWikiSubscribe->isSubPostSubscibeByUser($post->ID, $userid)) {
        return true;
    }
    if (isset($post->post_parent) && $post->post_parent != 0) {
        return isSubPostSubscribe(get_post($post->post_parent), $userid);
    } else {
        return false;
    }
}

/**
 * UnSubscribe for the particular post ID. 
 * 
 * @global type $pagenow
 */
/*function unSubscribe() {
    global $pagenow;

    if (isset($_REQUEST['unSubscribe']) == '1') {
        $params = array_keys($_REQUEST);  //get the keys from request parameter
        $actionParam = $params[0];
        $postID = $_REQUEST['unSubscribe-postId'];  //get post id from the request parameter
        $url = get_permalink($postID);      //get permalink from post id
        $redirectURl = $url . '?' . $actionParam . '=1'; //form the url

        if (!is_user_logged_in() && $pagenow != 'wp-login.php') {
            wp_redirect(wp_login_url($redirectURl), 302); //after login and if permission is set , user would be subscribed to the page
        } else {
            if (isset($_POST['unSubscribe-postId'])) {
                $id = $_POST['unSubscribe-postId'];
                $userId = get_current_user_id();
                $subscribeId = get_post_meta($id, 'subcribers_list', true);
                unSubscription($id, $userId, $subscribeId);
            }
        }
    }
}

add_action('wp', 'unSubscribe');*/

/**
 * Update subscriber list meta value for the particular post ID. 
 * 
 * @global type $pagenow
 */
function update() {
    global $pagenow;

    if (isset($_REQUEST['PageSubscribe']) == '1') {
        $params = array_keys($_REQUEST);  //get the keys from request parameter
        $actionParam = $params[0];
        $postID = $_REQUEST['update-postId'];  //get post id from the request parameter
        $url = get_permalink($postID);      //get permalink from post id
        $redirectURl = $url . '?' . $actionParam . '=1'; //form the url

        if (!is_user_logged_in() && $pagenow != 'wp-login.php') {
            wp_redirect(wp_login_url($redirectURl), 302); //after login and if permission is set , user would be subscribed to the page
        } else {

            //Single post subscribe
            $singleStatus = '';
            if (isset($_POST['single_subscribe']))
                $singleStatus = $_POST['single_subscribe'];

            //Post type 
            $post_type = 'post';
            if (isset($_POST['post-type']))
                $post_type = $_POST['post-type'];

            //subscribe or unsubscribe single post
            if (isset($_POST['single_subscribe'])) {
                if ($_POST['single_subscribe'] == 'current') {
                    if (isset($_POST['subPage_subscribe']) && $_POST['subPage_subscribe'] == 'subpage'):
                        subscribePostByCurUser($postID, TRUE);
                    else:
                        subscribePostByCurUser($postID, FALSE);
                    endif;
                }
            } else if ($_POST['single_subscribe'] == NULL) {
                if (isset($_POST['subPage_subscribe']) && $_POST['subPage_subscribe'] == 'subpage'):
                    unsubcribeSubPostByCurUser($postID, TRUE);
                else:
                    unsubscribePostByCurUser($postID);
                endif;
            }
        }
    }
}

add_action('wp', 'update');

/**
 * Function for sunscribe given post for current user
 * 
 * @global type $rtWikiSubscribe
 * @param type $postid
 * @param type $is_sub_subscribe : flag if you want to subscribe sub page
 */
function subscribePostByCurUser($postid, $is_sub_subscribe) {
    global $rtWikiSubscribe;
    $userid = get_current_user_id();
    if (!$rtWikiSubscribe->isPostSubscibeByUser($postid, $userid)) {
        $subscriber = array(
            'attribute_postid' => $postid,
            'attribute_userid' => $userid,
            'attribute_sub_subscribe' => $is_sub_subscribe
        );
        $rtWikiSubscribe->add_subscriber($subscriber);
    } else {
        unsubcribeSubPostByCurUser($postid, $is_sub_subscribe);
    }
}

/**
 * Function to unsubscribe/remove userid from subscriptionsub page list
 * 
 * @global type $rtWikiSubscribe
 * @param type $postid
 * @param type $is_sub_subscribe
 */
function unsubcribeSubPostByCurUser($postid, $is_sub_subscribe) {
    global $rtWikiSubscribe;
    $userid = get_current_user_id();
    if ($rtWikiSubscribe->isPostSubscibeByUser($postid, $userid)) {
        $subscriber = array(
            'attribute_sub_subscribe' => $is_sub_subscribe,
        );
        $subscriberWhere = array(
            'attribute_postid' => $postid,
            'attribute_userid' => $userid,
        );

        $rtWikiSubscribe->update_subscriber($subscriber, $subscriberWhere);
    }
}

/**
 * Function to unsubscribe/remove userid from subscription list
 * 
 * @global type $rtWikiSubscribe
 * @param type $postid
 * @param type $userid
 */
function unsubscribePostByCurUser($postid, $userid) {
    global $rtWikiSubscribe;

    if (!isset($userid))
        $userid = get_current_user_id();

    if ($rtWikiSubscribe->isPostSubscibeByUser($postid, $userid)) {

        $subscriber = array(
            'attribute_postid' => $postid,
            'attribute_userid' => $userid
        );
        $rtWikiSubscribe->delete_subscriber($subscriber);
    }
}

/**
 * Check if pages have any sub pages/child page [wiki-widgets]
 * 
 * @param type $parentId
 * @param type $post_type
 * @return boolean
 */
function ifSubPages($parentId, $post_type = 'post') {

    $args = array('parent' => $parentId, 'post_type' => $post_type);
    $pages = get_pages($args);

    if ($pages)
        return true;
    else
        return false;
}

/**
 * Check permission of sub pages/child page [wiki-widgets]
 * 
 * @param type $parentId
 * @param type $subPage : flag for subpage 
 * @param type $post_type
 * @return boolean
 */
function rt_wiki_subpages_check($parentId, $subPage, $post_type = 'post') {
    $args = array('parent' => $parentId, 'post_type' => $post_type);
    $subPageFlag = $subPage;
    $pages = get_pages($args);
    if ($pages) {
        foreach ($pages as $page) {
            $permission = getPermission($page->ID,get_current_user_id());
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
 * Send mail On post Update having body as diff of content for daily update 
 * 
 * @global type $post
 * @param type $post
 * @param type $email
 */
/*function post_changes_send_mail($postID, $email, $group, $url = '') {

    $revision = wp_get_post_revisions($postID);
    $content = array();
    $title = array();

    foreach ($revision as $revisions) {
        $content[] = $revisions->post_content;
        $title[] = $revisions->post_title;
    }

//    $args = array(
//        'title' => 'Differences',
//        'title_left' => $title[1],
//        'title_right' => $title[0],
//    );

    if (!empty($content)) {
        $url = 'Page Link:' . $url . '<br>';
        //$diff_table = wp_text_diff($content[1], $content[0], $args);
        $body = rtcrm_text_diff($title[count($title) - 1], $title[0], $content[count($title) - 1], $content[0]);
        //$body.=$diff;
        $finalBody = $url . '<br>' . $body;
        add_filter('wp_mail_content_type', 'set_html_content_type');

        $subject .= 'Updates for "' . strtoupper(get_the_title($postID)) . '"';
        //$subject .=':Time: ' . date("F j, Y, g:i a");
        $headers[] = 'From: rtcamp.com <no-reply@' . sanitize_title_with_dashes(get_bloginfo('name')) . '.com>';

        wp_mail($email, $subject, $finalBody, $headers);

        remove_filter('wp_mail_content_type', 'set_html_content_type');
    }
}*/

/**
 * Function Called after a Wiki post is Upated 
 * Sends Email to Subscribers of Wiki Posts 
 * 
 * @global type $rtWikiSubscribe
 * @param type $post
 */
function sendMailonPostUpdateWiki($post) {
    global $rtWikiSubscribe;
    $postObject = get_post($post);
    $supported_posts = rtwiki_get_supported_attribute();
    $diff = '';
    if (in_array(get_post_type($post), $supported_posts, true)) {

        /*if (wp_is_post_revision($postObject->ID)) {
            return;
        }

        global $rtWikiAttributesModel;
        $rtWikiAttributesModel = new RtWikiAttributeTaxonomyModel();
        $attributes = $rtWikiAttributesModel->get_all_attributes();
        $mainTermArray = array();
        $termArray = array();
        $attr_term = array();
        foreach ($attributes as $attr) {
            $attr_term[] = $attr->attribute_name;
        }
        $taxo = $_REQUEST['tax_input'];
        foreach ($attr_term as $attr) {
            $terms = get_the_terms($post, $attr);

            if (is_array($terms)) {
                foreach ($terms as $term) {
                    $termArray[] = $term->name;
                }

                $mainTermArray[$attr] = $termArray;
                unset($termArray);
            }
        }

        $newTermId = array();
        $oldTermId = array();
        $iterator = new MultipleIterator;
        if (is_array($taxo))
            $iterator->attachIterator(new ArrayIterator($taxo));
        $iterator->attachIterator(new ArrayIterator($mainTermArray));
        $diff = '';
        foreach ($iterator as $key => $values) {

            if ($key[0] == $key[1]) {

                foreach ($values[0] as $val) {
                    $newTermId[] = $val;
                }

                foreach ($values[1] as $val1) {

                    if ($key[1] == NULL) {

                        //unset($oldTermId);
                        $oldTermId[] = '-';
                    } else {
                        if (!empty($val1)) {
                            $oldTermId[] = $val1;
                        } else {
                            $oldTermId[] = ' ';
                        }
                    }
                }

                $diff.=contacts_diff_on_lead($post, $newTermId, $oldTermId, $key[0]);

                unset($oldTermId);
                unset($newTermId);
            }
        }*/
        $subscribersList = $rtWikiSubscribe->getAllSubscribersList($postObject->ID);
        $subscribersList = array_unique(array_merge($subscribersList, $rtWikiSubscribe->getAllParentSubSubscribers(getAllParentIDs(get_post($postObject->post_parent)))));
        if (!empty($subscribersList) || $subscribersList != NULL) {
            foreach ($subscribersList as $subscriber) {
                $user_info = get_userdata($subscriber);
                if (getPermission($postObject->ID, $user_info->ID)) {
                    wiki_page_changes_send_mail($postObject->ID, $user_info->user_email, $diff, get_permalink($postObject->ID));
                }
            }
        }
    }
    //exit();
    //send_daily_change_mail();
}

add_action('save_post', 'sendMailonPostUpdateWiki', 99, 1);

/**
 * get all parent id of perticular post
 * 
 * @param type $post
 * @param type $postids
 * @return string
 */
function getAllParentIDs($post, $postids) {
    if (!isset($postids)) {
        $postids = '';
    }
    $postids = $postids . $post->ID . ',';
    if (isset($post->post_parent) && $post->post_parent != 0) {
        return getAllParentIDs(get_post($post->post_parent), $postids);
    }
    return $postids;
}

/**
 * Send mail On post Update having body as diff of content 
 * 
 * @param type $postID
 * @param type $email : Email id of user 
 * @param type $tax_diff : body of mail 
 * @param string $url : url of post 
 */
function wiki_page_changes_send_mail($postID, $email, $tax_diff = '', $url = '') {

    $revision = wp_get_post_revisions($postID);
    $content = array();
    $title = array();

    foreach ($revision as $revisions) {
        $content[] = $revisions->post_content;
        $title[] = $revisions->post_title;
    }

//    $args = array(
//        'title' => 'Differences',
//        'title_left' => $title[1],
//        'title_right' => $title[0],
//    );

    if (!empty($content)) {
        $url = 'Page Link:' . $url . '<br>';
        $body = rtcrm_text_diff($title[count($title) - 1], $title[0], $content[count($title) - 1], $content[0]);

        $body.=$tax_diff;
        $finalBody = $url . '<br>' . $body;
        add_filter('wp_mail_content_type', 'set_html_content_type');

        $subject = 'Updates for "' . strtoupper(get_the_title($postID)) . '"';
        // $subject .=':Time: ' . date("F j, Y, g:i a");
        $headers[] = 'From: rtcamp.com <no-reply@' . sanitize_title_with_dashes(get_bloginfo('name')) . '.com>';
        //var_dump($email, $subject, $finalBody, $headers);
        //exit();
        wp_mail($email, $subject, $finalBody, $headers);

        remove_filter('wp_mail_content_type', 'set_html_content_type');
    }
}

/**
 * get html content type
 * 
 * @return String 
 */
function set_html_content_type() {
    return 'text/html';
}
