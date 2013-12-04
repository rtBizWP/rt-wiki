<?php

/**
 * 
 * Custom Widgets for rtWiki Plugin.  
 * 
 */

/*
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
            echo $args['before_title'] . 'Contributers' . $args['after_title'];
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

/*
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
        $isParent = ifSubPages($post->ID);

        if ($isParent) {

            echo $args['before_widget'];
            echo $args['before_title'] . 'Sub Pages' . $args['after_title'];
            getSubPages($post->ID, 0);
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

/*
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
        echo $args['before_title'] . 'Taxonomies' . $args['after_title'];
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

/*
 * rtWiki Single Page Subscription Widget
 */

class rt_wiki_single_page_subscribe extends WP_Widget {

    function __construct() {
        $widget_ops = array('classname' => 'rtWiki-singlePageSubscription', 'description' => __('Single Page Subscription', 'rtCamp'));
        parent::__construct('rtWiki-singlePageSubscription-widgets', __('rtWiki:Single Page Subscription', 'rtCamp'), $widget_ops);
    }

    function widget($args, $instance) {
        extract($args, EXTR_SKIP);
        global $post;
        echo $args['before_widget'];
        echo $args['before_title'] . 'Subscribe For Updates' . $args['after_title'];
        if (checkSubscribe() == true) {

            echo '<p>You are Subscribed to this Page. </p>';
        } else {
            echo '<form id="user-subscribe" method="post" action="?subscribe=1">
                <input type="submit" name=post-update-subscribe" value="Subscribe" >
                <input type="hidden" name="update-postId"  value=' . $post->ID . '>
            </form>';
        }
        echo $args['after_widget'];
    }

    function update($new_instance, $old_instance) {
        $instance = $old_instance;
        return $instance;
    }

    function form($instance) {
        
    }

}

/*
 * rtWiki SubPage Subscription Widget
 */

class rt_wiki_subpage_subscribe extends WP_Widget {

    function __construct() {
        $widget_ops = array('classname' => 'rtWiki-subPageSubscription', 'description' => __('SubPage Subscription', 'rtCamp'));
        parent::__construct('rtWiki-subPageSubscription-widgets', __('rtWiki:Sub Page Subscription', 'rtCamp'), $widget_ops);
    }

    function widget($args, $instance) {
        extract($args, EXTR_SKIP);
        global $post;
        echo $args['before_widget'];
        $isParent = ifSubPages($post->ID);
        if ($isParent == true) {
            $parent_ID = $post->post_parent;
            if ($parent_ID != '0' || $parent_ID != 0) {
                $userId = get_current_user_id();
                $parentSubpageTracking = get_post_meta($parent_ID, 'subpages_tracking', true);
                if (!in_array($userId, $parentSubpageTracking, true)) {

                    echo $args['before_title'] . 'Subscribe For All Pages' . $args['after_title'];
                    ?>   
                    <form id="user-all-subscribe" method="post" action="?allSubscribe=1">
                        <input type="submit" name=post-update-subscribe" value="Subscribe To all subpages" >
                        <input type="hidden" name="update-all-postId"  value=<?php echo $post->ID ?>>
                    </form>
                    <?php
                }
                echo $args['after_widget'];
            }
        }
    }

    function update($new_instance, $old_instance) {
        $instance = $old_instance;
        return $instance;
    }

    function form($instance) {
        
    }

}

function rt_wiki_register_widgets() {
    register_widget('rt_wiki_contributers');
    register_widget('rt_wiki_subPages');
    register_widget('rt_wiki_single_page_subscribe');
    register_widget('rt_wiki_subpage_subscribe');
    register_widget('rt_wiki_taxonomies');
}

add_action('widgets_init', 'rt_wiki_register_widgets');