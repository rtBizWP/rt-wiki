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
            <article id="<?php get_post_type() . "-" . get_the_ID(); ?>" <?php post_class('clearfix'); ?> >
                <header class="entry-header">
                    <h1 class="entry-title post-title"><?php echo the_title(); ?></h1>
                </header>
                <div class="entry-content">
                    <?php the_content(); ?>
                </div>
            </article>
        <?php } 
        comments_template();
        ?>

    </div><!-- #content -->
</div><!-- #primary -->
<?php
//if (comments_open() || get_comments_number()) {
    
//}
?>

<?php
$supported_posts = rtwiki_get_supported_attribute();
$post_type = get_post_type();
if (in_array($post_type, $supported_posts)) {
    ?>
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
