<?php
/**
 * Wiki Archive Template
 *
 * @package rtPanel
 *
 * @since rtPanelChild 2.0
 */
get_header();

?>

<div id="primary" class="content-area">
    <div id="content" class="site-content" role="main">

        <?php if (have_posts()) : ?>
            <header class="archive-header">
                <h1 class="archive-title"><?php
                    if (is_day()) :
                        printf(__('Daily Archives: %s', 'twentythirteen'), get_the_date());
                    elseif (is_month()) :
                        printf(__('Monthly Archives: %s', 'twentythirteen'), get_the_date(_x('F Y', 'monthly archives date format', 'twentythirteen')));
                    elseif (is_year()) :
                        printf(__('Yearly Archives: %s', 'twentythirteen'), get_the_date(_x('Y', 'yearly archives date format', 'twentythirteen')));
                    else :
                        _e('Archives', 'twentythirteen');
                    endif;
                    ?></h1>
            </header><!-- .archive-header -->

            <?php /* The loop */ ?>
            <?php while (have_posts()) : the_post(); ?>

                <?php
                if (getPermission(get_the_ID()) == true) {
                    get_template_part('content', get_post_format());
                }
                ?>
            <?php endwhile; ?>
            <div class="navigation">
                <div class="alignleft"><?php previous_posts_link('&laquo; Previous Entries') ?></div>
                <div class="alignright"><?php next_posts_link('Next Entries &raquo;', '') ?></div>
            </div>
            <?php //twentythirteen_paging_nav(); ?>

        <?php else : ?>
            <?php get_template_part('content', 'none'); ?>
        <?php endif; ?>

    </div><!-- #content -->
</div><!-- #primary -->

<?php get_sidebar(); ?>
<?php get_footer(); ?>