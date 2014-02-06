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
    if (!empty($subscriberList)) {
        if (in_array($userId, $subscriberList, true)) {
            return true;
        } else {
            return false;
        }
    }
}

function nonWikiSubscribe() {
    global $pagenow;

    if (isset($_REQUEST['subscribe']) == '1') {


        $params = array_keys($_REQUEST);  //get the keys from request parameter
        $actionParam = $params[0];
        $postID = $_REQUEST['nonWikiPost'];  //get post id from the request parameter
        $url = get_permalink($postID);      //get permalink from post id
        $redirectURl = $url . '?' . $actionParam . '=1'; //form the url


        if (!is_user_logged_in() && $pagenow != 'wp-login.php') {
            wp_redirect(wp_login_url($redirectURl), 302); //after login and if permission is set , user would be subscribed to the page
        } else {
            //var_dump($_POST['nonWikiPost']);

            if (isset($_POST['nonWikiPost'])) {

                $id = $_POST['nonWikiPost'];
                $userId = get_current_user_id();
                $subscribeId = get_post_meta($id, 'subcribers_list', true);
                pageSubscription($id, $userId, $subscribeId);
            }
        }
    }
}

add_action('wp', 'nonWikiSubscribe');

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
 * @global type $pagenow
 */
function update() {
    global $pagenow;

    if (isset($_REQUEST['wikiPageSubscribe']) == '1') {


        $params = array_keys($_REQUEST);  //get the keys from request parameter
        $actionParam = $params[0];
        $postID = $_REQUEST['update-postId'];  //get post id from the request parameter
        $url = get_permalink($postID);      //get permalink from post id
        $redirectURl = $url . '?' . $actionParam . '=1'; //form the url


        if (!is_user_logged_in() && $pagenow != 'wp-login.php') {
            wp_redirect(wp_login_url($redirectURl), 302); //after login and if permission is set , user would be subscribed to the page
        } else {
            $singleStatus = $_POST['single-subscribe'];
            $userId = get_current_user_id();

            $subscribeId = get_post_meta($postID, 'subcribers_list', true);
            if (isset($_POST['single-subscribe'])) {

                if ($_POST['single-subscribe'] == 'current') {
                    pageSubscription($postID, $userId, $subscribeId);
                }
            } else if ($_POST['single-subscribe'] == NULL) {

                unSubscription($postID, $userId, $subscribeId);
            }

            if (isset($_POST['subPage-subscribe'])) {
                //$subPageStatus = $_POST['subPage-subscribe'];
                $userId = get_current_user_id();
                $subpagesTrackingList = get_post_meta($postID, 'subpages_tracking', true);
                $pageSubsciptionList = get_post_meta($postID, 'subcribers_list', true);
                if ($_POST['subPage-subscribe'] == 'subpage') {
                    pageSubscription($postID, $userId, $pageSubsciptionList);
                    subPageSubscription($postID, $userId, $subpagesTrackingList);
                    subcribeSubPages($postID, 0, $userId);
                }
            } else if ($_POST['subPage-subscribe'] == NULL) {
                //var_dump($pageSubsciptionList);

                unSubscription($postID, $userId, $pageSubsciptionList);
                subpageUnSubscription($postID, $userId, $subpagesTrackingList);

                unSubcribeSubPages($postID, 0, $userId);
            }
        }
    }
}

add_action('wp', 'update');

/**
 * Update subscriber list meta value for the Sub Pages. 
 * @global type $pagenow
 */
//function updateForAllSubPages() {
//    global $pagenow;
//
//    if (isset($_REQUEST['allSubscribe']) == '1') {
//
//        $params = array_keys($_REQUEST);  //get the keys from request parameter
//        $actionParam = $params[0];
//        $postID = $_REQUEST['update-postId'];  //get post id from the request parameter
//        $url = get_permalink($postID);      //get permalink from post id
//        $redirectURl = $url . '?' . $actionParam . '=1'; //form the url
//
//
//        if (!is_user_logged_in() && $pagenow != 'wp-login.php') {
//            wp_redirect(wp_login_url($redirectURl), 302); //after login and if permission is set , user would be subscribed to the page and its subpages
//        } else {
//            $id = $_POST['update-all-postId'];
//            $userId = get_current_user_id();
//            $subpagesTrackingList = get_post_meta($id, 'subpages_tracking', true);
//            $pageSubsciptionList = get_post_meta($id, 'subcribers_list', true);
//
//            pageSubscription($id, $userId, $pageSubsciptionList);
//            subPageSubscription($id, $userId, $subpagesTrackingList);
//
//            subcribeSubPages($id, 0, $userId);
//        }
//    }
//}
//add_action('wp', 'updateForAllSubPages');

/*
 * Subscription funciton for Pages and subpages
 * 
 */


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

/*
 * Unsubscription function for Pages and subpages
 * 
 */

function unSubcribeSubPages($parentId, $lvl, $userId) {
    $args = array('parent' => $parentId, 'post_type' => 'wiki');
    $pages = get_pages($args);

    if ($pages) {
        $lvl++;
        foreach ($pages as $page) {

            $permission = getPermission($page->ID);

            if ($permission == true) {
                $subscribeId = get_post_meta($page->ID, 'subcribers_list', true);
                $subpagesTrackingList = get_post_meta($page->ID, 'subpages_tracking', true);

                unSubscription($page->ID, $userId, $subscribeId);
                subpageUnSubscription($page->ID, $userId, $subpagesTrackingList);
            }
            unSubcribeSubPages($page->ID, $lvl, $userId);
        }
    }
}

/**
 * Check if pages have any sub pages/child page
 * 
 * @param type $parentId
 * @return boolean
 */
function ifSubPages($parentId, $post_type = 'wiki') {

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
        $body.=$diff;
        $finalBody = $url . '<br>' . $body;
        add_filter('wp_mail_content_type', 'set_html_content_type');

        $subject .= 'Updates for "' . strtoupper(get_the_title($postID)) . '"';
        //$subject .=':Time: ' . date("F j, Y, g:i a");
        $headers[] = 'From: rtcamp.com <no-reply@' . sanitize_title_with_dashes(get_bloginfo('name')) . '.com>';

        wp_mail($email, $subject, $finalBody, $headers);

        remove_filter('wp_mail_content_type', 'set_html_content_type');
    }
}

function nonWiki_page_changes_send_mail($postID, $email, $tax_diff = '', $url = '') {

    $revision = wp_get_post_revisions($postID);
    $content = array();
    $title = array();

//    $termid = array();
//    $taxo = $_REQUEST['tax_input'];
//
//    $diff = '';
//    if (isset($taxo)) {
//        foreach ($taxo as $key => $value) {
//
//            foreach ($value as $val) {
//                if ($val != 0) {
//                    $termid[] = $val;
//                }
//            }
//            if (!empty($termid)) {
//
//                $diff.=contacts_diff_on_lead($postID, $termid, $key);
//            }
//            unset($termid);
//        }
//    }


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

//add_action('wp', 'post_changes_send_mail');

function set_html_content_type() {

    return 'text/html';
}

//function sendMailonPostUpdateWiki($post) {
//    $postObject = get_post($post);
//    if ($postObject->post_type == 'wiki') {
//        // If this is just a revision, don't send the email.
//
//        if (wp_is_post_revision($postObject->ID)) {
//            return;
//        }
//
//        $subscribersList = get_post_meta($postObject->ID, 'subcribers_list', true);
//        if (!empty($subscribersList) || $subscribersList != NULL) {
//            foreach ($subscribersList as $subscribers) {
//
//                $user_info = get_userdata($subscribers);
//                nonWiki_page_changes_send_mail($postObject->ID, $user_info->user_email);
//            }
//        }
//    }
//}
//add_action('save_post', 'sendMailonPostUpdateWiki');


/*
 * Function Called when a Wiki post is Upated 
 * Sends Email to Subscribers of Wiki Posts
 */

function sendMailonPostUpdateWiki($post) {

    $postObject = get_post($post);
    if ($postObject->post_type == 'wiki') {

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

            if( is_array($terms) ) {
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
        if( is_array($taxo) )
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

        $subscribersList = get_post_meta($postObject->ID, 'subcribers_list', true);
        if (!empty($subscribersList) || $subscribersList != NULL) {
            foreach ($subscribersList as $subscribers) {

                $user_info = get_userdata($subscribers);
                nonWiki_page_changes_send_mail($postObject->ID, $user_info->user_email, $diff, get_permalink($postObject->ID));
            }
        }

        //echo $diff;
    }
}

add_action('pre_post_update', 'sendMailonPostUpdateWiki', 99, 1);

/*
 * Function Called when a Non Wiki post type is Updated 
 * Sends Email to Subscribers of Non Wiki Posts
 */

function sendMailNonWiki($post) {
    $postObject = get_post($post);
    $args = array('hierarchical' => true);
    $post_types = get_post_types($args);

    if ($postObject->post_type != 'wiki') {

        if (wp_is_post_revision($postObject->ID)) {
            return;
        }

//        global $rtWikiAttributesModel;
//        $rtWikiAttributesModel = new RtWikiAttributeTaxonomyModel();
//        $attributes = $rtWikiAttributesModel->get_all_attributes();
//        $mainTermArray = array();
//        $termArray = array();
//        $attr_term = array();
//        foreach ($attributes as $attr) {
//            $attr_term[] = $attr->attribute_name;
//        }
//        $taxo = $_REQUEST['tax_input'];
//        foreach ($attr_term as $attr) {
//            $terms = get_the_terms($post, $attr);
//
//            foreach ($terms as $term) {
//                $termArray[] = $term->name;
//            }
//
//            $mainTermArray[$attr] = $termArray;
//            unset($termArray);
//        }
//
//        $newTermId = array();
//        $oldTermId = array();
//        $iterator = new MultipleIterator;
//        $iterator->attachIterator(new ArrayIterator($taxo));
//        $iterator->attachIterator(new ArrayIterator($mainTermArray));
//        $diff = '';
//        foreach ($iterator as $key => $values) {
//
//            if ($key[0] == $key[1]) {
//
//                foreach ($values[0] as $val) {
//                    $newTermId[] = $val;
//                }
//
//                foreach ($values[1] as $val1) {
//
//                    if ($key[1] == NULL) {
//
//                        //unset($oldTermId);
//                        $oldTermId[] = '-';
//                    } else {
//                        if (!empty($val1)) {
//                            $oldTermId[] = $val1;
//                        } else {
//                            $oldTermId[] = ' ';
//                        }
//                    }
//                }
//
//                $diff.=contacts_diff_on_lead($post, $newTermId, $oldTermId, $key[0]);
//
//                unset($oldTermId);
//                unset($newTermId);
//            }
//        }


        if (in_array($postObject->post_type, $post_types)) {
            $subscribersList = get_post_meta($postObject->ID, 'subcribers_list', true);
            if (!empty($subscribersList) || $subscribersList != NULL) {
                foreach ($subscribersList as $subscribers) {
                    $user_info = get_userdata($subscribers);
                    nonWiki_page_changes_send_mail($postObject->ID, $user_info->user_email, '', get_permalink($postObject->ID));
                }
            }
        }
    }
}

add_action('save_post', 'sendMailNonWiki', 99, 1);

