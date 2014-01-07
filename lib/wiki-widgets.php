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
            if (rt_wiki_subpages_check($post->ID, true) == true) {
                echo $args['before_title'] . 'Sub Pages' . $args['after_title'];
                getSubPages($post->ID, 0);
            }
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

class rt_non_wiki_subPages extends WP_Widget {

    function __construct() {
        $widget_ops = array('classname' => 'rtWiki-nonwiki-subPages', 'description' => __('Subpages List For Non Wiki Pages', 'rtCamp'));
        parent::__construct('rtWiki-nonwiki-subPages-widgets', __('rtWiki: Non wiki Subpages ', 'rtCamp'), $widget_ops);
    }

    function widget($args, $instance) {
        extract($args, EXTR_SKIP);
        global $post;

        if ($post->post_type != 'wiki' && !is_single()) {

            if (subpages_non_wiki($post->post_type) == true && is_page()) {

                $isParent = ifSubPages($post->ID, $post->post_type);

                echo $args['before_widget'];
                if ($isParent) {


                    echo $args['before_title'] . 'Sub Pages' . $args['after_title'];
                    getSubPages($post->ID, 0, $post->post_type);
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

class rt_wiki_page_subscribe extends WP_Widget {

    function __construct() {
        $widget_ops = array('classname' => 'rtWiki-wikiPageSubscription', 'description' => __('Wiki Page Subscription', 'rtCamp'));
        parent::__construct('rtWiki-wikiPageSubscription-widgets', __('rtWiki:Wiki Page Subscription', 'rtCamp'), $widget_ops);
    }

    function widget($args, $instance) {
        extract($args, EXTR_SKIP);
        global $post;
        $currentPageStatus = '';
        $currentPageMessage='';
        $subpageStatus = '';
        $subpageStatusMessage = '';
        $singleCheck='';
        $subPageCheck='';
        
        $parentStatus=false;
        echo $args['before_widget'];
        echo $args['before_title'] . 'Subscribe For Updates' . $args['after_title'];

        if (checkSubscribe() == true) {
            $currentPageStatus = 1;
            //$currentPageMessage='You Are Subscribed to this page';
            //echo '<p>You are Subscribed to this Page. </p>';
        } else {
            $currentPageStatus = 0;
            //$currentPageMessage= ''
        }

        $isParent = ifSubPages($post->ID);

        if ($isParent == true) {
             $parentStatus=true;
            if (rt_wiki_subpages_check($post->ID, true) == true) {
                $parent_ID = $post->post_parent;
                $parentIdFlag = false;

                $userId = get_current_user_id(); //current user id
                $parentSubpageTracking = get_post_meta($parent_ID, 'subpages_tracking', true); //Parent Post meta
                $pageSubscription = get_post_meta($post->ID, 'subcribers_list', true); // Current post meta
                $subPageSubscription = get_post_meta($post->ID, 'subpages_tracking', true);
                /* Check if post has any parent or not */
                if ($parent_ID == 0)
                    $parentIdFlag = false;
                else
                    $parentIdFlag = in_array($userId, $parentSubpageTracking, true);
                if (!in_array($userId, $subPageSubscription, true)) {

                    $subpageStatus = 0;
                } else {
                    $subpageStatus = 1;
                }
            }
        } 
        if ($currentPageStatus == 1)
        {
            
            $singleCheck= "checked"; 
        }
        else
        {
            
            $singleCheck='';
        }
        if($subpageStatus == 1)
        {
            $subPageCheck="checked";
        }else{
            $subPageCheck='';
        }
            echo '<form id="user-subscribe" method="post" action="?wikiPageSubscribe=1">
                <input type="checkbox" name="single-subscribe" value="current" '.$singleCheck.' >Subscribe to this page <br/>';
                if($parentStatus == true){
                echo '<input type="checkbox" name="subPage-subscribe" value="subpage" '.$subPageCheck.' >Subscribe to Sub Pages <br />';    
                }
                echo '<input type="submit" name=post-update-subscribe" value="Submit" >
                <input type="hidden" name="update-postId"  value=' . $post->ID . '>
            </form>';
        
        echo $args['after_widget'];
    }

    function update($new_instance, $old_instance) {
        $instance = $old_instance;
        return $instance;
    }

    function form($instance) {
        
    }

}

class rt_non_wiki_single_page_subscribe extends WP_Widget {

    function __construct() {
        $widget_ops = array('classname' => 'rtWiki-otherPostTypes-singlePageSubscription', 'description' => __('Single Page Subscription For Non Wiki Pages', 'rtCamp'));
        parent::__construct('rtWiki-otherPostTypes-singlePageSubscription-widgets', __('rtWiki:Subscribe(Non Wiki Pages)', 'rtCamp'), $widget_ops);
    }

    function widget($args, $instance) {
        extract($args, EXTR_SKIP);
        global $post;
        if ($post->post_type != 'wiki' && !is_single()) {
            echo $args['before_widget'];
            echo $args['before_title'] . 'Subscribe For Updates' . $args['after_title'];

            if (subscribe_non_wiki($post->post_type) == true && is_page()) {
                if (checkSubscribe() == true) {

                    echo '<form id="user-unsubscribe" method="post" action="?unSubscribe=1">
                <input type="submit" name=post-unsubscribe" value="Unsubscribe" >
                <input type="hidden" name="unSubscribe-postId"  value=' . $post->ID . '>
            </form>';
                } else {
                    echo '<form id="user-subscribe" method="post" action="?subscribe=1">
                <input type="submit" name=post-update-subscribe" value="Subscribe For Updates" >
                <input type="hidden" name="update-postId"  value=' . $post->ID . '>
            </form>';
                }
            } else {
                echo '<p>Please Enable the option to subscribe for this Page from Settings Page.</p>';
            }
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

        $isParent = ifSubPages($post->ID);

        if ($isParent == true) {

            if (rt_wiki_subpages_check($post->ID, true) == true) {
                $parent_ID = $post->post_parent;
                $parentIdFlag = false;

                $userId = get_current_user_id(); //current user id
                $parentSubpageTracking = get_post_meta($parent_ID, 'subpages_tracking', true); //Parent Post meta
                $pageSubscription = get_post_meta($post->ID, 'subcribers_list', true); // Current post meta
                $subPageSubscription = get_post_meta($post->ID, 'subpages_tracking', true);
                /* Check if post has any parent or not */
                if ($parent_ID == 0)
                    $parentIdFlag = false;
                else
                    $parentIdFlag = in_array($userId, $parentSubpageTracking, true);


                /* Check whether current post  has userid in page and parent page meta value */
                echo $args['before_widget'];
                echo $args['before_title'] . 'Subscribe For All Pages' . $args['after_title'];
                if (!in_array($userId, $subPageSubscription, true)) {
                    ?>

                    <form id="user-all-subscribe" method="post" action="?allSubscribe=1">
                        <input type="submit" name=post-update-subscribe" value="Subscribe To all subpages" >
                        <input type="hidden" name="update-all-postId"  value=<?php echo $post->ID ?>>
                    </form>
                    <?php
                } else {
                    echo 'You Are Subscribed to this page and its sub pages';
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
    register_widget('rt_non_wiki_subPages');
    register_widget('rt_wiki_page_subscribe');
   // register_widget('rt_wiki_subpage_subscribe');
    register_widget('rt_wiki_taxonomies');
    register_widget('rt_non_wiki_single_page_subscribe');
}

add_action('widgets_init', 'rt_wiki_register_widgets');