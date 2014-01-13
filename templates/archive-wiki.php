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
                printf(__('Daily Archives: %s', 'rtCamp'), get_the_date());
            elseif (is_month()) :
                printf(__('Monthly Archives: %s', 'rtCamp'), get_the_date(_x('F Y', 'monthly archives date format', 'rtCamp')));
            elseif (is_year()) :
                printf(__('Yearly Archives: %s', 'rtCamp'), get_the_date(_x('Y', 'yearly archives date format', 'rtCamp')));
            else :
                _e('Archives', 'rtCamp');
            endif;
            ?></h1>
            </header><!-- .archive-header -->

            
            <?php while (have_posts()) : the_post(); ?>
            <h2><?php the_title(); ?></h2>
            <?php echo the_excerpt() ?>
            <?php endwhile; ?>
            <div class="navigation">
                        <div class="alignleft"><?php previous_posts_link('&laquo; Previous Page') ?></div>
                        <div class="alignright"><?php next_posts_link('Next Page &raquo;', '') ?></div>
            </div>
           

        <?php else : ?>
            <?php get_template_part('content', 'none'); ?>
        <?php endif; ?>

                 
      

      
    </div><!-- #content -->
</div><!-- #primary -->

<?php get_sidebar(); ?>
<?php get_footer(); ?>