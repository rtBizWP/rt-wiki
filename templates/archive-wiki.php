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
	?>
			</h1>
		</header>
		<article id="docslist" class='clearfix rtp-post-box'>
			<div class="post-content">

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
					'post_type' => 'docs',
					'post_status' => 'publish',
					'show_date' => '',
					'sort_column' => 'menu_order, post_title',
					'title_li' => __( '<p>Below is list of Docs that can help you with different aspects:</p>' ),
					'walker' => '', );

$wikis = wp_list_pages( $args );

if ( isset( $wikis ) ){
	echo $wikis;
} else {
	get_template_part( 'content', 'none' );
}
	?>
			</div>
		</article>
	</section>
<?php get_sidebar();
get_footer();