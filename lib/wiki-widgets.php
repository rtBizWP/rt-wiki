<?php
/**
 * Custom Widgets for rtWiki Plugin.  
 */

/**
 * rtWiki Post Contributers Widget
 */
class rt_wiki_contributers extends WP_Widget {

    function __construct() {
        $widget_ops = array('classname' => 'rtWiki-contributers', 'description' => __('Post Contributers', 'rtCamp'));
        parent::__construct('rtWiki-contributers-widget', __('rtWiki: Post Contributers', 'rtCamp'), $widget_ops);
    }

    function widget($args, $instance) {
        extract($args, EXTR_SKIP);
        global $post;

        if (ifWikiContributers($post->ID)) {
            echo $args['before_widget'];
            echo $args['before_title'] . '<h2>Contributers</h2>' . $args['after_title'];
            getContributers($post->ID);
            echo $args['after_widget'];
        }
    }

    function update($new_instance, $old_instance) {
        $instance = $old_instance;
        return $instance;
    }

    function form($instance) {
        
    }

}

/**
 * rtWiki Post SubPages List Widget
 */
class rt_wiki_subPages extends WP_Widget {

    function __construct() {
        $widget_ops = array('classname' => 'rtWiki-subPages', 'description' => __('Subpages', 'rtCamp'));
        parent::__construct('rtWiki-subPages-widgets', __('rtWiki: Subpages', 'rtCamp'), $widget_ops);
    }

    function widget($args, $instance) {
        extract($args, EXTR_SKIP);
        global $post;
        $isParent = ifSubPages($post->ID, $post->post_type);

        if ($isParent) {
            echo $args['before_widget'];
            echo $args['before_title'] . '<h2>Sub Pages</h2>' . $args['after_title'];
            getSubPages($post->ID, 0, $post->post_type);
            echo $args['after_widget'];
        }
    }

    function update($new_instance, $old_instance) {
        $instance = $old_instance;
        return $instance;
    }

    function form($instance) {
        
    }

}

/**
 * rtWiki Post Taxonomies Widget
 */
class rt_wiki_taxonomies extends WP_Widget {

    function __construct() {
        $widget_ops = array('classname' => 'rtWiki-taxonomies', 'description' => __('Taxonomies', 'rtCamp'));
        parent::__construct('rtWiki-taxonomies-widgets', __('rtWiki: Taxonomies', 'rtCamp'), $widget_ops);
    }

    function widget($args, $instance) {
        extract($args, EXTR_SKIP);
        global $post;
        echo $args['before_widget'];
        echo $args['before_title'] . '<h2>Taxonomies</h2>' . $args['after_title'];
        //wiki_default_taxonomies($post->ID);
        wiki_custom_taxonomies($post->ID);
        echo $args['after_widget'];
    }

    function update($new_instance, $old_instance) {
        $instance = $old_instance;
        return $instance;
    }

    function form($instance) {
        
    }

}

/**
 * rtWiki Single Page Subscription Widget
 */
class rt_wiki_page_subscribe extends WP_Widget {

    function __construct() {
        $widget_ops = array('classname' => 'rtWiki-wikiPageSubscription', 'description' => __('Wiki Page Subscription', 'rtCamp'));
        parent::__construct('rtWiki-wikiPageSubscription-widgets', __('rtWiki:Wiki Page Subscription', 'rtCamp'), $widget_ops);
    }

    function widget($args, $instance) {
        extract($args, EXTR_SKIP);
        global $post;
        $subpageStatus = '';
        $singleCheck = '';
        $subPageCheck = '';

        $parentStatus = false;
        if (getPermission($post->ID, get_current_user_id())) {
            echo $args['before_widget'];
            echo $args['before_title'] . '<h2>Subscribe For Updates</h2>' . $args['after_title'];
            if (isPostSubscribeByCurUser(get_current_user_id()) == true) {
                $singleCheck = "checked";
            } else {
                $singleCheck = '';
            }
            $isParent = ifSubPages($post->ID, $post->post_type);

            if ($isParent == true) {
                if (rt_wiki_subpages_check($post->ID, true, $post->post_type) == true) {
                    $parentStatus = true;
                    // Check if user subscribe for sub page
                    if (isSubPostSubscribe($post, get_current_user_id()) == true) {
                        $subpageStatus = 1;
                    } else {
                        $subpageStatus = 0;
                    }
                }
            }

            if ($subpageStatus == 1) {
                $subPageCheck = "checked";
            } else {
                $subPageCheck = '';
            }
            echo '<form id="user-subscribe" method="post" action="?PageSubscribe=1">
                <input type="checkbox" name="single_subscribe" value="current"  ' . $singleCheck . ' >Subscribe to this page <br/>';
            if ($parentStatus == true) {
                echo '<input type="checkbox" name="subPage_subscribe" value="subpage"  ' . $subPageCheck . ' >Subscribe to this page and  Sub Pages <br />';
            }
            echo '<input type="hidden" name=post-type value=' . $post->post_type . ' /><input type="submit" class="button" name=post-update-subscribe" value="Submit" >
                <input type="hidden" name="update-postId"  value=' . $post->ID . '>
            </form>';

            echo $args['after_widget'];
        }
    }

    function update($new_instance, $old_instance) {
        $instance = $old_instance;
        return $instance;
    }

    function form($instance) {
        
    }

}

/**
 * register rtwiki widgets
 */
function rt_wiki_register_widgets() {
    register_widget('rt_wiki_contributers');
    register_widget('rt_wiki_subPages');
    register_widget('rt_wiki_page_subscribe');
    register_widget('rt_wiki_taxonomies');
}

add_action('widgets_init', 'rt_wiki_register_widgets');

/**
 * Function to add wiki activity to the dashboard.
 */
function rt_list_wikis() {
    $args = array(
        'post_type' => 'revision',
        'date_query' => array(
            'before' => date('Y-m-d', strtotime('+1 day')),
            'after' => date('Y-m-d', strtotime('-1 day')),
            'inclusive' => true,
            'column' => 'post_date'
        ),
        'posts_per_page' => 10,
        'post_status' => 'inherit'
    );
    $query = new WP_Query($args);
    $post_parent = array();
    if ($query->have_posts()) {
        ?>
        <div id="wiki-widget">
            <?php
            foreach ($query->posts as $posts) {
                if (!in_array($posts->post_parent, $post_parent) && is_wiki_post_type($posts->post_parent)) {
                    $revision_args = array(
                        'post_type' => 'revision',
                        'post_status' => 'inherit',
                        'date_query' => array(
                            'after' => date('Y-m-d', strtotime('-1 day')),
                        ),
                        'post_parent' => $posts->post_parent,
                    );
                    $revisions = new WP_Query($revision_args);
                    foreach ($revisions->posts as $revision) {
                        if ('Auto Draft' == $revision->post_title)
                            continue;
                        $date = date('Y-m-d H:i:s', strtotime($revision->post_date));
                        $hour_ago = date_diff(new DateTime(), new DateTime($date));
                        if ($hour_ago->d == 0) {
                            if ($hour_ago->h > 0) {
                                if ($hour_ago->h > 1)
                                    $hour_ago = $hour_ago->h . " hours ago";
                                else
                                    $hour_ago = $hour_ago->h . " hour ago";
                            }
                            else {
                                if ($hour_ago->i > 1)
                                    $hour_ago = $hour_ago->i . " minutes ago";
                                else
                                    $hour_ago = $hour_ago->i . " minute ago";
                            }
                        } else
                            $hour_ago = $date;
                        ?>
                        <div class='rtwiki-diff'>
                            <?php echo get_avatar($revision->post_author, '50'); ?>
                            <div class='rtwiki-diff-wrap'>
                                <h4 class='rtwiki-diff-meta'>
                                    <cite class='rtwiki-diff-author'><a href='<?php echo get_author_posts_url($revision->post_author); ?>'><?php echo ucwords(get_the_author_meta('display_name', $revision->post_author)); ?></a></cite>
                                        <?php echo __('has edited', 'rtCamp'); ?>
                                    <a href='post.php?post=<?php echo $posts->post_parent; ?>&action=edit'><?php echo esc_attr($revision->post_title); ?></a>
                                    <?php echo __("(" . $hour_ago . ")", 'rtCamp'); ?>
                                    <a href='revision.php?revision=<?php echo $revision->ID; ?>'><?php echo __('View Diff', 'rtCamp'); ?></a>
                                </h4>
                            </div>
                        </div>
                        <?php
                    }
                    array_push($post_parent, $posts->post_parent);
                    wp_reset_postdata();
                }
            }
            wp_reset_postdata();
            ?>
        </div>
        <?php
    }
}

/**
 * Add a widget to the dashboard.
 * This function is hooked into the 'wp_dashboard_setup' action below.
 */
function wiki_add_dashboard_widgets() {

    wp_add_dashboard_widget(
            'dashboard_wiki', // Widget slug.
            'Wiki Posts', // Title.
            'rt_list_wikis' // Display function.
    );
}

add_action('wp_dashboard_setup', 'wiki_add_dashboard_widgets');

/**
 * Function to check whether the post type is registered in rtWiki plugin setting.
 * 
 * @global type $post
 * @param type $post_id
 * @return boolean
 */
function is_wiki_post_type($post_id = 0) {
    global $post;
    if (is_multisite()) {
        $rtwiki_settings = get_site_option('rtwiki_settings', array());
        $rtwiki_custom = get_site_option('rtwiki_custom', array());
    } else {
        $rtwiki_settings = get_option('rtwiki_settings', array());
        $rtwiki_custom = get_option('rtwiki_custom', array());
    }
    $wiki_posts = array('wiki');
    if (isset($rtwiki_custom[0]['slug']) && !empty($rtwiki_custom[0]['slug']))
        array_push($wiki_posts, $rtwiki_custom[0]['slug']);
    if ($post_id == 0 && $post->post_parent != 0)
        $post_id = $post->post_parent;
    $post_type = get_post_type($post_id);
    if (in_array($post_type, $wiki_posts, true))
        return true;
    else
        return false;
}
