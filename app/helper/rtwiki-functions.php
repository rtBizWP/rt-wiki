<?php

/**
 * rtWiki
 *
 * Helper functions for rtwiki
 *
 * @package    RtWikiFunction
 * @subpackage Helper
 *
 * @author     Udit
 */

/**
 * sanitize a taxonomy name
 *
 * @param type $taxonomy
 *
 * @return type
 */
function rtwiki_sanitize_taxonomy_name( $taxonomy )
{
	$taxonomy = strtolower( stripslashes( strip_tags( $taxonomy ) ) );
	$taxonomy = preg_replace( '/&.+?;/', '', $taxonomy ); // Kill entities
	$taxonomy = str_replace( array( '.', '\'', '"' ), '', $taxonomy ); // Kill quotes and full stops.
	$taxonomy = str_replace( array( ' ', '_' ), '-', $taxonomy ); // Replace spaces and underscores.

	return $taxonomy;
}

/**
 * sanitize a taxonomy name
 *
 * @param type $name
 *
 * @return type
 */
function rtwiki_attribute_taxonomy_name( $name )
{
	return rtwiki_sanitize_taxonomy_name( $name );
}

/**
 * get supported attributes of rtwiki
 *
 * @return type
 */
function rtwiki_get_supported_attribute()
{
	$attributes      = array();
	$rtwiki_settings = '';
	$rtwiki_custom   = '';
	if ( is_multisite() ){
		$rtwiki_settings = get_site_option( 'rtwiki_settings', array() );
		$rtwiki_custom   = get_site_option( 'rtwiki_custom', array() );
	} else {
		$rtwiki_settings = get_option( 'rtwiki_settings', array() );
		$rtwiki_custom   = get_option( 'rtwiki_custom', array() );
	}
	if ( isset( $rtwiki_custom[ 0 ][ 'slug' ] ) ) $attributes[ ] = $rtwiki_custom[ 0 ][ 'slug' ];
	if ( isset( $rtwiki_settings[ 'attribute' ] ) )
		$attributes = array_merge( $attributes, $rtwiki_settings[ 'attribute' ] );

	return $attributes;
}

/**
 * check if term[group] exist or not for perticular user
 *
 * @global type $wpdb
 *
 * @param type  $term : term name
 * @param type  $userid
 *
 * @return int
 */
function get_term_if_exists( $term, $userid )
{

	global $wpdb;
	$query   = "SELECT slug FROM $wpdb->terms WHERE term_id IN(SELECT term_id from $wpdb->term_taxonomy WHERE term_taxonomy_id IN(SELECT term_taxonomy_id from $wpdb->term_relationships WHERE object_id=$userid))and slug='" . $term . "'";
	$page_id = $wpdb->get_var( $query );

	return $page_id;
}


function get_rtwiki_archive( $atts ) {
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
}
