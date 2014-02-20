<?php

/**
 * 
 * Checks for the permission of each post at admin side
 * and frontend side.Display content according to it
 * 
 */
//require (ABSPATH . WPINC . '/feed.php');

/**
 * Single Post Content Permission for Wiki CPT
 */
/* function single_post_filtering() {
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
  if ($post->post_author == $user) {

  return $post->post_content;
  } else {
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
  } */

/**
 * check if term[group] exist or not for perticular user
 * 
 * @global type $wpdb
 * @param type $term : term name 
 * @param type $userid
 * @return int
 */
function get_term_if_exists($term, $userid) {

    global $wpdb;
    $query = "SELECT slug FROM $wpdb->terms WHERE term_id IN(SELECT term_id from $wpdb->term_taxonomy WHERE term_taxonomy_id IN(SELECT term_taxonomy_id from $wpdb->term_relationships WHERE object_id=$userid))and name='" . $term . "'";
    $page_id = $wpdb->get_var($query);
    return $page_id;
}

/**
 * check permission before move post of CPT to trash
 * 
 * @param type $post_id
 */
function my_wp_trash_post($post_id) {
    $post = get_post($post_id);
    $supported_posts = rtwiki_get_supported_attribute();
    if (in_array($post->post_type, $supported_posts) && in_array($post->post_status, array('publish', 'draft', 'future'))) {
        if (!current_user_can($post_id, 'delete_wiki')) {
            WP_DIE(__('You dont have enough access rights to move this post to the trash') . "<br><a href='edit.php?post_type=$post->post_type'>" . __("Go Back") . "</a>");
        }
    }
}

add_action('wp_trash_post', 'my_wp_trash_post');

/**
 * removes quick edit from wiki post type
 * 
 * @global type $current_user
 * @global type $post
 * @param type $actions
 * @return type
 */
function remove_quick_edit($actions) {
    global $post;
    $supported_posts = rtwiki_get_supported_attribute();
    if (in_array($post->post_type, $supported_posts)) {
        if (!current_user_can('edit_wiki', $post->ID)) {
            unset($actions['edit']);
            unset($actions['inline hide-if-no-js']);
        }

        if (!current_user_can('delete_wiki', $post->ID)) {
            unset($actions['trash']);
        }

        if (!current_user_can('read_wiki', $post->ID)) {
            unset($actions['view']);
        }
    }
    return $actions;
}

add_filter('page_row_actions', 'remove_quick_edit', 10);

/**
 * Check permissions at Admin side for edit post & delete post
 */
function postCheck() {
    $page = isset($_GET['post']) ? $_GET['post'] : 0;
    $supported_posts = rtwiki_get_supported_attribute();
    $posttype = get_post_type($page);
    if (isset($_GET['action']) && $_GET['action'] == 'edit') {
        if (in_array($posttype, $supported_posts)) {
            if (!current_user_can($page, 'edit_wiki')) {
                WP_DIE(__('You dont have enough access rights to Edit this post') . "<br><a href='edit.php?post_type=$posttype'>" . __("Go Back") . "</a>");
            }
        }
    }
    if (isset($_GET['action']) && $_GET['action'] == 'trash') {
        if (in_array($posttype, $supported_posts)) {
            if (!current_user_can($page, 'delete_wiki')) {
                WP_DIE(__('You dont have enough access rights to move this post to the trash') . "<br><a href='edit.php?post_type=$posttype'>" . __("Go Back") . "</a>");
            }
        }
    }
}

add_action('admin_init', 'postCheck');

/**
 * add capabilities for perticular user
 * 
 * @global type $post
 * @param type $capabilities
 * @param type $cap
 * @param type $args
 * @param type $user
 * @return type
 */
function addCapabilities($capabilities, $cap, $args, $user) {
    global $post, $current_user;
    $supported_posts = rtwiki_get_supported_attribute();
    if (is_object($post) && in_array($post->post_type, $supported_posts)) {
        $access = 'na';
        if (is_object($user) && !empty($user->roles) && ( 'administrator' == $user->roles[0] )) {
            return $user->allcaps;
        }

        if (is_object($user) && !empty($user->roles) && ( 'rtwikicontributor' == $user->roles[0] )) {
            return $capabilities;
        }

        $access = getAdminPanelSidePermission($post->ID);
        if (( is_object($post) && $post->post_author != get_current_user_id())) {
            $capabilities["delete_wiki"] = false;
            $capabilities["delete_others_wiki"] = false;
            $capabilities["delete_published_wiki"] = false;
        }
        if ($access == false || $access == 'r') {
            $capabilities["edit_posts"] = false;
            $capabilities["edit_others_posts"] = false;
            $capabilities["edit_published_posts"] = false;
            $capabilities["edit_wiki"] = false;
            $capabilities["edit_others_wiki"] = false;
            $capabilities["edit_published_wiki"] = false;
            $capabilities["publish_wiki"] = false;
        }

        if ($access == false) {
            $capabilities["read_private_wiki"] = false;
            $capabilities["read_wiki"] = false;
        }
    }

    /* if (is_object($user) && !empty($user->roles) && ( 'administrator' == $user->roles[0] )) {
      return $user->allcaps;
      }

      if (is_object($post) && $post->post_author == get_current_user_id()) {
      $capabilities = array_merge($capabilities, array(
      'delete_posts' => 'delete_posts',
      'delete_others_posts' => 'delete_others_posts',
      'delete_post' => 'delete_post',
      'delete_published_posts' => 'delete_published_posts'
      )
      );
      }

      if ('w' == $access) {
      $capabilities = array_merge($capabilities, array('read_post' => 'read_post',
      'publish_posts' => 'publish_posts',
      'edit_posts' => 'edit_posts',
      'edit_others_posts' => 'edit_others_posts',
      'read_private_posts' => 'read_private_posts',
      'edit_post' => 'edit_post',
      'edit_published_posts' => 'edit_published_posts',
      )
      );
      } else if ('r' == $access) {
      $capabilities = array_merge($capabilities, array('read_post' => 'read_post',
      'read_private_posts' => 'read_private_posts'
      )
      );
      } */
    return $capabilities;
}

add_filter('user_has_cap', 'addCapabilities', 10, 4);

/**
 * Checks the permission of the Logged in User for editing post
 * 
 * @global type $current_user
 * @param type $pageID
 * @return string|boolean
 */
function getAdminPanelSidePermission($pageID) {

    $noflag = 0;
    $noPublic = 0;
    global $current_user;
    $user = get_current_user_id();
    $terms = get_terms('user-group', array('hide_empty' => true));
    $access_rights = get_post_meta($pageID, 'access_rights', true);

    if (!is_user_logged_in()) {
        if (isset($access_rights['public']) && ( 1 == $access_rights['public'] )) {
            return 'r';
        } else if (isset($access_rights['public']) && ( 0 == $access_rights['public'] )) {
            return false;
        }
    } else {

        $post_meta = get_post($pageID);

        if (( 'administrator' == $current_user->roles[0] ) || ( is_object($post_meta) && ( $post_meta->post_author == $user ) )) {
            return 'w';
        } else {
            if (empty($access_rights)) {
                return 'r';
            }
            if (isset($access_rights['public']) && 1 == $access_rights['public']) {
                return 'r';
            } else if (isset($access_rights['all'])) {

                if (isset($access_rights['all']['w']) && ( $access_rights['all']['w'] == 1 )) {
                    return 'w';
                } else if (isset($access_rights['all']['r']) && ( $access_rights['all']['r'] == 1 )) {
                    return 'r';
                } else if (isset($access_rights['all']['na']) && ( $access_rights['all']['na'] == 1 )) {
                    $noflag = 1;
                }
            } else {
                foreach ($terms as $term) {
                    $ans = get_term_if_exists($term->slug, $user);
                    if ($ans == $term->slug && isset($access_rights[$term->name])) {
                        if (isset($access_rights[$term->name]['w']) && ( $access_rights[$term->name]['w'] == 1 )) {
                            return 'w';
                        } else if (isset($access_rights[$term->name]['r']) && ( $access_rights[$term->name]['r'] == 1 )) {
                            return 'r';
                        } else if (isset($access_rights[$term->name]['na']) && ( $access_rights[$term->name]['na'] == 1 )) {
                            $noflag = 1;
                        }
                    } else if ($ans == '' || $ans == null) {
                        $noPublic = 1;
                    }
                }
            }
            if ($noflag == 1 || $noPublic == 1) {
                return false;
            }
        }
    }
}

/**
 * Checks the permission of the user for the post(Frontend)
 * 
 * @global type $current_user
 * @param type $pageID
 * @param type $user
 * @return boolean
 */
function getPermission($pageID, $user) {
    global $current_user;
    $noflag = 0;
    $noPublic = 0;
    $terms = get_terms('user-group', array('hide_empty' => true));
    $access_rights = get_post_meta($pageID, 'access_rights', true);
    if (isset($access_rights['public']) && ( 1 == $access_rights['public'] )) {
        return true;
    } else if (is_user_logged_in()) {
        $post_details = get_post($pageID);
        if (( 'rtwikicontributor' == $current_user->roles[0] ) || ( 'administrator' == $current_user->roles[0] ) || ( is_object($post_details) && ( $post_details->post_author == $user ) )) {
            return true;
        } elseif (isset($access_rights['all'])) {
            if (( isset($access_rights['all']['r']) && ( $access_rights['all']['r'] == 1 ) ) || ( isset($access_rights['all']['w']) && ( $access_rights['all']['w'] == 1 ) )) {
                return true;
            } else if (isset($access_rights['all']['na']) && ( $access_rights['all']['na'] == 1 )) {
                $noflag = 1;
            }
        } else {
            foreach ($terms as $term) {
                $ans = get_term_if_exists($term->slug, $user);
                if ($ans == $term->slug && isset($access_rights[$term->name])) {
                    if (( isset($access_rights[$term->name]['r']) && ( $access_rights[$term->name]['r'] == 1 ) ) || ( isset($access_rights[$term->name]['w']) && ( $access_rights[$term->name]['w'] == 1 ) )) {
                        return true;
                    } else if (isset($access_rights[$term->name]['na']) && ( $access_rights[$term->name]['na'] == 1 )) {
                        $noflag = 1;
                    }
                } else if ($ans == '' || $ans == null) {
                    $noPublic = 1;
                }
            }
        }
        if ($noflag == 1) {
            return false;
        }
        if ($noPublic == 1) {
            return false;
        }
    } else if (isset($access_rights['public']) && !is_user_logged_in() && ( 0 == $access_rights['public'] )) {
        return false;
    }
}

add_filter('bulk_actions-' . 'edit-wiki', '__return_empty_array');

/* Function to disable feeds for wiki CPT */
remove_action('do_feed_rdf', 'do_feed_rdf', 10, 1);
remove_action('do_feed_rss', 'do_feed_rss', 10, 1);
remove_action('do_feed_rss2', 'do_feed_rss2', 10, 1);
remove_action('do_feed_atom', 'do_feed_atom', 10, 1);

// Now we add our own actions, which point to our own feed function
add_action('do_feed_rdf', 'my_do_feed', 10, 1);
add_action('do_feed_rss', 'my_do_feed', 10, 1);
add_action('do_feed_rss2', 'my_do_feed', 10, 1);
add_action('do_feed_atom', 'my_do_feed', 10, 1);

/**
 * Finally, we do the post type & permission on CPT check, and generate feeds conditionally
 * 
 * @global type $wp_query
 * @global type $post
 */
function my_do_feed() {
    global $wp_query, $post;
    $no_feed = array('wiki');
    if (in_array($wp_query->query_vars['post_type'], $no_feed)) {
        wp_die(__('This is not a valid feed address.', 'textdomain'));
    } else {
        $supported_posts = rtwiki_get_supported_attribute();
        $newpostArray = array();
        if (isset($wp_query->posts)) {
            foreach ($wp_query->posts as $post) {
                if (in_array(get_post_type(), $supported_posts)) {
                    if (getPermission(get_the_ID(), get_current_user_id())) {
                        $newpostArray[] = $post;
                    }
                } else {
                    $newpostArray[] = $post;
                }
            }
            $wp_query->posts = $newpostArray;
            $wp_query->post_count = count($newpostArray);
            $wp_query->found_posts = count($newpostArray);
        }
        if (isset($wp_query->comments)) {
            $newCommentArray = array();
            foreach ($wp_query->comments as $comment) {
                if (in_array(get_post_type($comment->comment_post_ID), $supported_posts)) {
                    if (getPermission($comment->comment_post_ID, get_current_user_id())) {
                        $newCommentArray[] = $comment;
                    }
                } else {
                    $newpostArray[] = $post;
                }
            }
            $wp_query->comments = $newCommentArray;
            $wp_query->comment_count = count($newpostArray);
        }
        do_feed_rss2($wp_query->is_comment_feed);
    }
}

/**
 * check permission for CPT and generate result[get_posts] 
 * 
 * @param type $wp_query
 * @return type
 */
function rtwiki_search_filter($Posts) {
    if (isset($Posts) && (is_search() || is_archive() ) && !is_admin()) {
        $newpostArray = array();
        $supported_posts = rtwiki_get_supported_attribute();
        foreach ($Posts as $post) {
            if (in_array($post->post_type, $supported_posts)) {
                if (getPermission($post->ID, get_current_user_id())) {
                    $newpostArray[] = $post;
                }
            } else {
                $newpostArray[] = $post;
            }
        }
        $Posts = $newpostArray;
    }
    return $Posts;
}

add_action('the_posts', 'rtwiki_search_filter');

/**
 * check permission for CPT content.
 * 
 * @param type $content
 * @return type
 */
function rtwiki_content_filter($content) {
    $supported_posts = rtwiki_get_supported_attribute();
    if (in_array(get_post_type(), $supported_posts)) {
        if (getPermission(get_the_ID(), get_current_user_id())) {
            $post_thumbnail = get_the_post_thumbnail();
            return $post_thumbnail . $content;
        } else {
            return '<p>' . __('Not Enough Rights to View The Content.', 'rtCamp') . '</p>';
        }
    }
    return $content;
}

add_filter('the_content', 'rtwiki_content_filter');

/**
 * check permission for CPT edit content link.
 * 
 * @param type $output
 * @return string
 */
function rtwiki_edit_post_link_filter($output) {
    $supported_posts = rtwiki_get_supported_attribute();
    if (in_array(get_post_type(), $supported_posts)) {
        if (getPermission(get_the_ID(), get_current_user_id())) {
            return $output;
        } else {
            return '';
        }
    }
    return $output;
}

add_filter('edit_post_link', 'rtwiki_edit_post_link_filter');

/**
 * check permission before cpt comment shows.
 * 
 * @param type $comments
 * @param type $post_id
 * @return type
 */
function rtwiki_comment_filter($comments, $post_id) {
    //@TODO is wiki post type
    $supported_posts = rtwiki_get_supported_attribute();
    $post = get_post($post_id);
    if (in_array($post->post_type, $supported_posts)) {
        if (getPermission($post_id, get_current_user_id())) {
            return $comments;
        }
        return array();
    }
    return $comments;
}

add_filter('comments_array', 'rtwiki_comment_filter', 10, 2);

/**
 * check permission before cpt comment form display.
 * 
 * @param type $open
 * @param type $post_id
 * @return boolean
 */
function rtwiki_comment_form_filter($open, $post_id) {
    //@TODO is wiki post type
    $supported_posts = rtwiki_get_supported_attribute();
    $post = get_post($post_id);
    if (in_array($post->post_type, $supported_posts)) {
        if (getPermission($post_id, get_current_user_id())) {
            return $open;
        }
        return FALSE;
    }
    return $open;
}

add_filter('comments_open', 'rtwiki_comment_form_filter', 10, 2);

/**
 * sitemap taxonomies filter [Yoast plugin]
 * 
 * @global type $rtWikiAttributesModel
 * @param type $taxonomies
 * @return type
 */
function rtwiki_sitemap_taxonomies($taxonomies) {
    global $rtWikiAttributesModel;
    $attributes = $rtWikiAttributesModel->get_all_attributes();
    $customAttributes = array();
    foreach ($attributes as $attribute) {
        $customAttributes[] = $attribute->attribute_name;
    }
    foreach ($taxonomies as $key => $val) {
        if (in_array($key, $customAttributes)) {
            unset($taxonomies[$key]);
        }
    }
    return $taxonomies;
}

add_filter('wpseo_sitemaps_supported_taxonomies', 'rtwiki_sitemap_taxonomies');

/**
 * sitemap posttype filter [Yoast plugin]
 * 
 * @global type $rtWikiAttributesModel
 * @param type $posttypes
 * @return type
 */
function rtwiki_sitemap_posttypes($posttypes) {
    global $rtWikiAttributesModel;
    $attributes = rtwiki_get_supported_attribute();
    foreach ($posttypes as $key => $val) {
        if (in_array($key, $attributes)) {
            unset($posttypes[$key]);
        }
    }
    return $posttypes;
}

add_filter('wpseo_sitemaps_supported_post_types', 'rtwiki_sitemap_posttypes');
