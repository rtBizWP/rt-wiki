<?php

/**
 * Checks if subscriber ID is in the subscribers list or not 
 * 
 * @global type $post
 * @return boolean
 */
function checkSubscribe() {
    global $post, $rtWikiSubscribe;
    $userId = get_current_user_id();
    return $rtWikiSubscribe->is_subscribe($post->ID, $userId);
}

/**
 * Checks if parent post subscribe for subpage for perticular user 
 * 
 * @global type $post
 * @return boolean
 */
function checkParentSubSubscribe($userId) {
    global $post, $rtWikiSubscribe;
    return $rtWikiSubscribe->is_subpage_subscribe($post->post_parent, $userId);
}

/**
 * Checks if subscriber ID is in the sub page subscribers list or not 
 * 
 * @global type $post
 * @return boolean
 */
function checkSubPageSubscribe() {
    global $post, $rtWikiSubscribe;
    $userId = get_current_user_id();
    return $rtWikiSubscribe->is_subpage_subscribe($post->ID, $userId);
}

/**
 * UnSubscribe for the particular post ID. 
 * 
 * @global type $pagenow
 */
function unSubscribe() {
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

add_action('wp', 'unSubscribe');

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

            //Current user id 
            $userId = get_current_user_id();

            //Post type 
            $post_type = 'post';
            if (isset($_POST['post-type']))
                $post_type = $_POST['post-type'];

            //subscribe single post
            if (isset($_POST['single_subscribe'])) {
                if ($_POST['single_subscribe'] == 'current') {
                    if (isset($_POST['subPage_subscribe']) && $_POST['subPage_subscribe'] == 'subpage'):
                        pageSubscription($postID, $userId, TRUE);
                    else:
                        pageSubscription($postID, $userId, FALSE);
                    endif;
                }
            } else if ($_POST['single_subscribe'] == NULL) {
                unSubscription($postID, $userId);
            }

            //subscribe post with subpage 
            if (isset($_POST['subPage_subscribe'])) {
                if ($_POST['subPage_subscribe'] == 'subpage') {
                    pageSubscription($postID, $userId, TRUE);
                    subcribeSubPages($postID, 0, $userId, $post_type);
                }
            } else if ($_POST['subPage_subscribe'] == NULL) {
                unSubscription($postID, $userId);
                unSubcribeSubPages($postID, 0, $userId, $post_type);
            }
        }
    }
}

add_action('wp', 'update');

/**
 * Subscription funciton for subpages
 * 
 * @param
 *      int $parentId
 *      int $lvl : heirarchi level
 *      int $userId
 *      String $post_type : post type of parenrt 
 */
function subcribeSubPages($parentId, $lvl, $userId, $post_type = 'post') {

    $args = array('parent' => $parentId, 'post_type' => $post_type);
    $pages = get_pages($args);
    if ($pages) {
        $lvl++;
        foreach ($pages as $page) {

            $permission = getPermission($page->ID);

            if ($permission == true) {
                pageSubscription($page->ID, $userId, TRUE);
            }
            subcribeSubPages($page->ID, $lvl, $userId, $post_type);
        }
    }
}

/**
 * Unsubscription function for subpages
 * 
 * @param
 *      int $parentId
 *      int $lvl : heirarchi level
 *      int $userId
 *      String $post_type : post type of parenrt 
 */
function unSubcribeSubPages($parentId, $lvl, $userId, $post_type = 'post') {

    $args = array('parent' => $parentId, 'post_type' => $post_type);
    $pages = get_pages($args);
    if ($pages) {
        $lvl++;
        foreach ($pages as $page) {

            $permission = getPermission($page->ID);

            if ($permission == true) {
                unSubscription($page->ID, $userId);
            }
            unSubcribeSubPages($page->ID, $lvl, $userId, $post_type);
        }
    }
}

/**
 * Check if pages have any sub pages/child page
 * 
 * @param 
 *      int $parentId
 *      String $post_type : post type of parent 
 * 
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
 * Check permission of sub pages/child page
 * 
 * @param 
 *      int $parentId
 *      bool $subPage : flag for subpage 
 *      String $post_type : post type of parent 
 * 
 * @return boolean
 */
function rt_wiki_subpages_check($parentId, $subPage, $post_type = 'post') {
    $args = array('parent' => $parentId, 'post_type' => $post_type);
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
 * Send mail On post Update having body as diff of content for daily update 
 * 
 * @global type $post
 * @param type $post
 * @param type $email
 */
function post_changes_send_mail($postID, $email, $group, $url = '') {

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
}

/**
 * Send mail On post Update having body as diff of content for daily update 
 * 
 * @param 
 *      int  $postID
 *      String $email : Email id of user 
 *      String $tax_diff : body of mail 
 *      String $url : url of post 
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

/**
 * Function Called after a Wiki post is Upated 
 * Sends Email to Subscribers of Wiki Posts 
 * 
 * @param type  $post
 */
function sendMailonPostUpdateWiki($post) {
    global $rtWikiSubscribe;
    $postObject = get_post($post);
    $supported_posts = rtwiki_get_supported_attribute();

    if (in_array(get_post_type($post), $supported_posts, true)) {

        if (wp_is_post_revision($postObject->ID)) {
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
        }
        $subscribersList = $rtWikiSubscribe->get_all_subscribers($postObject->ID);

        if (!empty($subscribersList) || $subscribersList != NULL) {
            foreach ($subscribersList as $subscribers) {
                $user_info = get_userdata($subscribers->attribute_userid);
                wiki_page_changes_send_mail($postObject->ID, $user_info->user_email, $diff, get_permalink($postObject->ID));
            }
        }
    }
}

add_action('post_updated', 'sendMailonPostUpdateWiki', 99, 1);

