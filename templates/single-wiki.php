<?php
/*
 * 
 * The Template for displaying all single posts.
 *
 */

get_header();

$content_class = apply_filters( 'rtwiki_content_class', 'large-8 columns rtp-singular' );
?>
	<section id="content" class="rtp-content-section <?php echo $content_class ?> " role="main">
<?php
			/* The loop */
if ( have_posts() ){
	the_post();
	?>
				<article id="<?php get_post_type() . '-' . get_the_ID(); ?>" <?php post_class( 'clearfix rtp-post-box' ); ?> >
					<header class="post-header"><div class="rtp-secondary-header">
						<h1 class="post-title"><?php echo the_title(); ?></h1>
						<?php
	if ( function_exists( 'yoast_breadcrumb' ) ) {
		echo '<hr class="rtp-separator" />';
		yoast_breadcrumb( '<p id="breadcrumbs" class="breadcrumbs clearfix rtp-breadcrumb-secondary">' , '</p>' );
	}
	?>
						</div>
					</header>
					<div class="post-content">
						<?php the_content(); ?>
					</div>
				</article>
	<?php
}
			comments_template();
?>
	</section>
<?php
$supported_posts = rtwiki_get_supported_attribute();
$post_type = get_post_type();
if ( in_array( $post_type, $supported_posts ) ){
	?>
	<aside id="sidebar" class="rtp-sidebar-section large-4 columns" role="complementary">
		<div class="rtp-sidebar-inner-wrapper">
			<?php dynamic_sidebar( 'rt-wiki-sidebar' ); ?>
		</div>
	</aside>
 <?php
} else {
	get_sidebar();
}
get_footer();
