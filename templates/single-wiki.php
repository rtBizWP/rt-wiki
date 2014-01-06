<?php
/*
 * 
 * The Template for displaying all single posts.
 *
 */

get_header();
?>

<div id="primary" class="content-area">
    <div id="content" class="site-content" role="main">

        <?php
        /* The loop */
        global $post;
        $postType = $post->post_type;
        ?>
        <?php while (have_posts()) : the_post(); ?>
            <h2> <?php //echo the_title();  ?></h2>
            <?php
            if (getPermission($post->ID) == true) {
                get_template_part('content', get_post_format());
            } else {
                wp_die(__('Not Enough Access To View the Content'));
            }
//            if (function_exists('rtwiki_single_shortcode')) {
//                do_shortcode('[rtWikiSinglePost]');
//            } else {
//                echo single_post_filtering();
//            }

        endwhile;

        //twentythirteen_post_nav();
        comments_template();
        ?>
    </div><!-- #content -->
</div><!-- #primary -->


<?php if ($postType == 'wiki') { ?>
    <div id="secondary" class="sidebar-container" role="complementary">

        <div class="widget-area">

    <?php dynamic_sidebar('rt-wiki-sidebar'); ?>

        </div> 

    </div> 
    <?php
} else {
    get_sidebar();
}

get_footer();
?>