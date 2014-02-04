<?php
/**
 * Wiki Archive Template
 *
 * @package rtPanel
 *
 * @since rtPanelChild 2.0
 */
get_header();

$content_class = apply_filters('rtwiki_content_class', 'large-8 small-12 columns');
?>

<div id="primary" class="content-area <?php echo $content_class; ?>">
    <div id="content" class="site-content" role="main">

        <header class="page-header">
            <h1 class="page-title"><?php
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
        </header>
        <?php if (have_posts()) : 
            $post_count = 0; ?>
            <?php while (have_posts()) : the_post(); ?>
                <?php if ( getPermission( get_the_ID() ) == true ) { ?>
                    <article id="post-<?php the_ID(); ?>" <?php post_class( 'clearfix' ); ?> >
                        <header class="entry-header">
                            <h1 class="entry-title"><a href="<?php the_permalink(); ?>" rel="bookmark" title="<?php printf( esc_attr__( 'Permanent Link to %s', 'rtCamp' ), the_title_attribute( 'echo=0' ) ); ?>"><?php the_title(); ?></a></h1>
                        </header>
                        <div class="entry-content">
                            <?php echo the_excerpt(); ?>
                        </div>
                    </article>
                <?php  
                        $post_count++;
                    }
                ?>
            <?php 
                endwhile; 
                if( $post_count == 0 ) {
                    ?>
                        <article id="post-0" class="post not-found" >
                            <header class="entry-header">
                                <h1 class="entry-title"><?php _e( 'No Posts', 'rtCamp' ); ?></h1>
                            </header>
                            <div class="entry-content">
                                <p><?php _e( 'Not Enough Rights to View The Content.', 'rtCamp' ); ?></p>
                            </div>
                        </article>
                    <?php
                }
            ?>
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