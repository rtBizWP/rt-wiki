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
        $currentPageMessage = '';
        $subpageStatus = '';
        $subpageStatusMessage = '';
        $singleCheck = '';
        $subPageCheck = '';

        $parentStatus = false;
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
           
            if (rt_wiki_subpages_check($post->ID, true) == true) {
                $parent_ID = $post->post_parent;
                $parentIdFlag = false;
                 $parentStatus = true;
                $userId = get_current_user_id(); //current user id
                $parentSubpageTracking = get_post_meta($parent_ID, 'subpages_tracking', array()); //Parent Post meta
                $pageSubscription = get_post_meta($post->ID, 'subcribers_list', array()); // Current post meta
                $subPageSubscription = get_post_meta($post->ID, 'subpages_tracking', array());
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
        if ($currentPageStatus == 1) {

            $singleCheck = "checked";
        } else {

            $singleCheck = '';
        }
        if ($subpageStatus == 1) {
            $subPageCheck = "checked";
        } else {
            $subPageCheck = '';
        }
        echo '<form id="user-subscribe" method="post" action="?wikiPageSubscribe=1">
                <input type="checkbox" name="single-subscribe" value="current"  '. $singleCheck .' >Subscribe to this page <br/>';
        if ($parentStatus == true) {
            echo '<input type="checkbox" name="subPage-subscribe" value="subpage"  '. $subPageCheck .' >Subscribe to this page and  Sub Pages <br />';
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
                    echo '<form id="user-subscribe-non-wiki" method="post" action="?subscribe=1">
                <input type="submit" name=post-update-subscribe" value="Subscribe For Updates" >
                <input type="hidden" name="nonWikiPost"  value='. $post->ID .'>
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
                $parentSubpageTracking = get_post_meta($parent_ID, 'subpages_tracking', array()); //Parent Post meta
                $pageSubscription = get_post_meta($post->ID, 'subcribers_list', array()); // Current post meta
                $subPageSubscription = get_post_meta($post->ID, 'subpages_tracking', array());
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

/*
 * Function to add wiki activity to the dashboard.
 */
function rt_list_wikis() {
    $args = array(
        'post_type'     => 'revision',
        'date_query'    => array(
            'before'  => date ( 'Y-m-d', strtotime( '+1 day' ) ),
            'after'  => date ( 'Y-m-d', strtotime( '-1 day' ) ),
            'inclusive'   => true,
            'column'    => 'post_date'
        ),
        'posts_per_page'    => 10,
        'post_status'       => 'inherit'
    );
    $query = new WP_Query($args);
    $post_parent = array();
    if( $query->have_posts() ) {
        ?>
        <div id="wiki-widget">
            <?php
            foreach( $query->posts as $posts){
                if( ! in_array( $posts->post_parent, $post_parent ) && is_wiki_post_type( $posts->post_parent ) ) {
                    $revision_args = array( 
                                        'post_type'         => 'revision',
                                        'post_status'       => 'inherit', 
                                        'date_query'        => array(
                                                'after'    => date ( 'Y-m-d', strtotime( '-1 day' ) ),
                                            ),
                                        'post_parent'       => $posts->post_parent,
                                    );
                    $revisions = new WP_Query($revision_args);
                    foreach ($revisions->posts as $revision) {
                        if( 'Auto Draft' == $revision->post_title )
                            continue;
                        $date = date( 'Y-m-d H:i:s', strtotime( $revision->post_date ) );
                        $hour_ago = date_diff( new DateTime(), new DateTime( $date ) );
                        if( $hour_ago->d == 0 ) {
                            if( $hour_ago->h > 0 ) {
                                if( $hour_ago->h > 1 ) 
                                    $hour_ago = $hour_ago->h." hours ago";
                                else
                                    $hour_ago = $hour_ago->h." hour ago";
                            }
                            else {
                                if ( $hour_ago->i > 1 )
                                    $hour_ago = $hour_ago->i." minutes ago";
                                else
                                    $hour_ago = $hour_ago->i." minute ago";
                            }
                        }
                        else 
                            $hour_ago = $date;
                        ?>
                            <div class='rtwiki-diff'>
                                <?php echo get_avatar( $revision->post_author, '50' ); ?>
                                <div class='rtwiki-diff-wrap'>
                                    <h4 class='rtwiki-diff-meta'>
                                        <cite class='rtwiki-diff-author'><a href='<?php get_author_link('true', $revision->post_author); ?>'><?php echo ucwords( get_author_name( $revision->post_author ) ); ?></a></cite>
                                        <?php echo __('has edited', 'rtCamp'); ?>
                                        <a href='post.php?post=<?php echo $posts->post_parent; ?>&action=edit'><?php echo esc_attr( $revision->post_title ); ?></a>
                                        <?php echo __( "(" . $hour_ago . ")", 'rtCamp' ); ?>
                                        <a href='revision.php?revision=<?php echo $revision->ID; ?>'><?php echo __('View Diff', 'rtCamp'); ?></a>
                                    </h4>
                                </div>
                            </div>
                        <?php
                    }
                    array_push( $post_parent, $posts->post_parent );
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
 *
 * This function is hooked into the 'wp_dashboard_setup' action below.
 */
function wiki_add_dashboard_widgets() {

	wp_add_dashboard_widget(
            'dashboard_wiki',         // Widget slug.
            'Wiki Posts',         // Title.
            'rt_list_wikis' // Display function.
        );	
}
add_action( 'wp_dashboard_setup', 'wiki_add_dashboard_widgets' );

/*
 * Function to check whether the post type is registered in rtWiki plugin setting.
 */

function is_wiki_post_type($post_id = 0) {
    global $post;
    if( is_multisite() ) {
        $rtwiki_settings = get_site_option( 'rtwiki_settings', array() );
        $rtwiki_custom = get_site_option( 'rtwiki_custom', array() );
    }
    else {
        $rtwiki_settings = get_option( 'rtwiki_settings', array() );
        $rtwiki_custom = get_option( 'rtwiki_custom', array() );
    }
    $wiki_posts = array( 'wiki' );
    if( isset( $rtwiki_custom[0]['slug'] ) && !empty( $rtwiki_custom[0]['slug'] ) )
        array_push ( $wiki_posts, $rtwiki_custom[0]['slug'] );
    if( $post_id == 0 && $post->post_parent != 0)
        $post_id = $post->post_parent;
    $post_type = get_post_type( $post_id );
    if( in_array( $post_type, $wiki_posts, true ) )
        return true;
    else
        return false;
}