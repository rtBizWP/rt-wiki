<?php
/**
 * Wiki Archive Template
 *
 * @package rtPanel
 *
 * @since   rtPanelChild 2.0
 */
get_header();

$content_class = apply_filters( 'rtwiki_content_class', 'large-8 columns rtp-singular' );
?>
	<section id="content" class="rtp-content-section <?php echo $content_class ?> " role="main">

	<?php
	global $rtWikiAttributesModel;
	$attributes = $rtWikiAttributesModel->attribute_exists( get_query_var( 'taxonomy' ), get_post_type() );
if ( ! $attributes ) { ?>

		<article id="<?php echo get_post_type() . '-list'; ?>" <?php post_class( 'clearfix wikilist rtp-post-box' ); ?>>
			<header class="post-header"><div class="rtp-secondary-header">
					<h1 class="post-title"><?php _e( sprintf( '%s' , strtoupper( get_post_type() ) ) , 'rtCamp' ); ?></h1>
				</div>
			</header>
			<div class="post-content">
				<ul class="ulwikilist">

				<?php
				$args = array(
					'authors' => '',
					'child_of' => 0,
					'depth' => 0,
					'echo' => 0,
					'exclude' => '',
					'include' => '',
					'link_after' => '',
					'link_before' => '',
					'post_type' => get_post_type(),
					'post_status' => 'publish',
					'show_date' => '',
					'sort_column' => 'menu_order, post_title',
					'title_li' => '',
					'walker' => new RtWikiWalker(), );

	$wikis = wp_list_pages( $args );

	if ( isset( $wikis ) ){
		echo $wikis;
	} else {
		get_template_part( 'content', 'none' );
	}
	?>
				</ul>
			</div>
		</article>
	<?php } else { ?>

	<header class="page-header">
		<h1 class="page-title"><?php
	if ( is_day() ) :
		printf( __( 'Daily Archives: %s', 'rtCamp' ), get_the_date() );
	elseif ( is_month() ) :
		printf( __( 'Monthly Archives: %s', 'rtCamp' ), get_the_date( _x( 'F Y', 'monthly archives date format', 'rtCamp' ) ) );
	elseif ( is_year() ) :
		printf( __( 'Yearly Archives: %s', 'rtCamp' ), get_the_date( _x( 'Y', 'yearly archives date format', 'rtCamp' ) ) );
	else :
		_e( 'Archives', 'rtCamp' );
	endif;
	?></h1>
	</header>

	<?php if ( have_posts() ) : ?>
		<?php while ( have_posts() ) : the_post(); ?>
			<article id="<?php get_post_type() . '-' . get_the_ID(); ?>" <?php post_class( 'clearfix rtp-post-box' ); ?> >
				<header class="post-header"><div class="rtp-secondary-header">
						<h1 class="post-title"><a href="<?php the_permalink(); ?>" rel="bookmark" title="<?php printf( esc_attr__( 'Permanent Link to %s', 'rtCamp' ), the_title_attribute( 'echo=0' ) ); ?>"><?php the_title(); ?></a></h1>
					</div>
				</header>
				<div class="post-content">
					<?php the_excerpt(); ?>
				</div>
			</article>
		<?php
		endwhile;
		?>
		<div class="navigation">
			<div class="alignleft"><?php previous_posts_link( '&laquo; Previous Page' ) ?></div>
			<div class="alignright"><?php next_posts_link( 'Next Page &raquo;', '' ) ?></div>
		</div>

	<?php else : ?>
		<?php get_template_part( 'content', 'none' ); ?>
	<?php endif; ?>

	<?php } ?>
	</section>
<?php
$supported_posts = rtwiki_get_supported_attribute();
$post_type = get_post_type();
if ( in_array( $post_type, $supported_posts ) ){
	?>
	<aside id="sidebar" class="rtp-sidebar-section large-4 columns" role="complementary">
		<div class="rtp-sidebar-inner-wrapper">
			<?php dynamic_sidebar( 'rt-wiki-archive-sidebar' ); ?>
		</div>
	</aside>
<?php
} else {
	get_sidebar();
}
get_footer();