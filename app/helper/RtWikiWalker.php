<?php

/**
 * Created by PhpStorm.
 * User: dips
 * Date: 26/2/14
 * Time: 11:41 AM
 */
class RtWikiWalker extends Walker_Page
{
	function start_el( &$output, $page, $depth, $args, $current_page )
	{
		if ( $depth ) $indent = str_repeat( "\t", $depth ); else
			$indent = '';

		extract( $args, EXTR_SKIP );
		$css_class = array( 'page_item', 'page-item-' . $page->ID );

		if ( isset( $args[ 'pages_with_children' ][ $page->ID ] ) ) $css_class[ ] = 'page_item_has_children';

		if ( ! empty( $current_page ) ){
			$_current_page = get_post( $current_page );
			if ( in_array( $page->ID, $_current_page->ancestors ) ) $css_class[ ] = 'current_page_ancestor';
			if ( $page->ID == $current_page ) $css_class[ ] = 'current_page_item'; elseif ( $_current_page && $page->ID == $_current_page->post_parent ) $css_class[ ] = 'current_page_parent';
		} elseif ( $page->ID == get_option( 'page_for_posts' ) ) {
			$css_class[ ] = 'current_page_parent';
		}

		$css_class = implode( ' ', apply_filters( 'page_css_class', $css_class, $page, $depth, $args, $current_page ) );

		if ( '' === $page->post_title ) $page->post_title = sprintf( __( '#%d (no title)' ), $page->ID );

		/** This filter is documented in wp-includes/post-template.php */
		if ( get_permission( $page->ID, get_current_user_id(), 0 ) ) {
			$output .= $indent . '<li class="' . $css_class . '"><a href="' . get_permalink( $page->ID ) . '">' . $link_before . apply_filters( 'the_title', $page->post_title, $page->ID ) . $link_after . '</a>';
		}
		if ( ! empty( $show_date ) ){
			if ( 'modified' == $show_date ) $time = $page->post_modified; else
				$time = $page->post_date;

			$output .= ' ' . mysql2date( $date_format, $time );
		}
	}
}