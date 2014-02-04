<?php
/*
 * 
 * The Template for displaying all single posts.
 *
 */

get_header();

$content_class = apply_filters('rtwiki_content_class', 'large-8 small-12 columns');
?>

<div id="primary" class="content-area <?php echo $content_class ?>">
    <div id="content" class="site-content" role="main">

        <?php
        /* The loop */
        if (have_posts()) {
            the_post();
            ?>
            <article id="post-<?php the_ID(); ?>" <?php post_class('clearfix'); ?> >
                <?php
                if (getPermission( get_the_ID() ) == true) {
                    ?>
                    <header class="entry-header">
                        <h1 class="entry-title post-title"><?php echo the_title(); ?></h1>
                    </header>
                    <div class="entry-content">
                    <?php echo the_content(); ?>
                    </div>
                    <?php } else { ?>
                    <div class="entry-content">
                    <?php _e('Not Enough Rights to View The Content on this page.', 'rtCamp'); ?>
                    </div>
            <?php } ?>
            </article>
<?php } ?>

    </div><!-- #content -->
</div><!-- #primary -->


<?php
get_sidebar();

get_footer();