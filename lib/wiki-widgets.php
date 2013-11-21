<?php

class rt_wiki_contributers extends WP_Widget {

    function __construct() {
        $widget_ops = array('classname' => 'rtWiki-contributers', 'description' => __('Post Contributers', 'rtCamp'));
        parent::__construct('rtWiki-contributers-widget', __('rtWiki: Post Contributers', 'rtCamp'), $widget_ops);
    }

    function widget($args, $instance) {
        extract($args, EXTR_SKIP);

        $contributers = getContributers();

        if (!empty($contributers)) {
            echo $args['before_widget'];
            echo '<div class="clearfix">';
            echo '<h4>Contributers</h4>';
            echo '<ul id="contributers">';
            foreach ($contributers as $contributer) {

                echo '<li>' . $contributer . '</li>';
            }
            echo '</ul>';
            echo '</div>';
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
            ?>    
            <h4>Sub Pages</h4>
            <?php
            getSubPages($post->ID, 0);
        }
    }

    function update($new_instance, $old_instance) {
        $instance = $old_instance;
        return $instance;
    }

    function form($instance) {
        
    }

}

class rt_wiki_single_page_subscribe extends WP_Widget {

    function __construct() {
        $widget_ops = array('classname' => 'rtWiki-singlePageSubscription', 'description' => __('Single Page Subscription', 'rtCamp'));
        parent::__construct('rtWiki-singlePageSubscription-widgets', __('rtWiki:Single Page Subscription', 'rtCamp'), $widget_ops);
    }

    function widget($args, $instance) {
        extract($args, EXTR_SKIP);
        global $post;

        echo '<h4>Subscribe For Updates </h4>';
        if (checkSubscribe() == true) {

            echo '<p>You are Subscribed to this Page. </p>';
        } else {
            echo '<form id="user-subscribe" method="post" action="?subscribe=1">
                <input type="submit" name=post-update-subscribe" value="Subscribe" >
                <input type="hidden" name="update-postId"  value=' . $post->ID . '>
            </form>';
        }
    }

    function update($new_instance, $old_instance) {
        $instance = $old_instance;
        return $instance;
    }

    function form($instance) {
        
    }

}

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
           

          echo '<h4>Subscribe For All Pages </h4>   
            <form id="user-all-subscribe" method="post" action="?allSubscribe=1">
                <input type="submit" name=post-update-subscribe" value="Subscribe To all subpages" >
                <input type="hidden" name="update-all-postId"  value='.$post->ID.'>
            </form>';
            
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
}

add_action('widgets_init', 'rt_wiki_register_widgets');