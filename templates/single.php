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
        //var_dump($post);
        $postType = $post->post_type;
        ?>
        <?php while (have_posts()) : the_post(); ?>
         <h2> <?php echo the_title(); ?></h2>
            <?php
            if ($postType == 'wiki') {
                ?>
                
                <?php
                if (function_exists('rtwiki_single_shortcode')) {
                    do_shortcode('[rtwikiSubPages post_type="'.$postType.'"  post_id="'.$post->ID.'"]');
                    do_shortcode('[rtWikiSinglePost]');
                } else {
                    echo single_post_filtering();
                }
            } else {
                
                do_shortcode('[rtwikiSubPages postType="'.$postType.'"]');
                get_template_part('content', get_post_format());
                
            }
            ?>


<?php endwhile; ?>
        <?php if ($postType == 'wiki') { ?>

            <?php dynamic_sidebar('rtWiki Widget Area'); ?>

        <?php
        }
        twentythirteen_post_nav();
        comments_template();
        
        ?>

    </div><!-- #content -->
</div><!-- #primary -->


<?php get_sidebar(); ?>
<?php get_footer(); ?>