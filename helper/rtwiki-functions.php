<?php

/**
 * rtwiki Functions
 *
 * Helper functions for rtwiki
 *
 * @author udit
 */

/**
 * sanitize a taxonomy name
 * 
 * @param type $taxonomy
 * @return type
 */
function rtwiki_sanitize_taxonomy_name( $taxonomy ) {
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
 * @return type
 */
function rtwiki_attribute_taxonomy_name( $name ) {
	return  rtwiki_sanitize_taxonomy_name( $name );
}

/**
 * get supported attributes of rtwiki
 * 
 * @return type
 */
function rtwiki_get_supported_attribute() {
    $attributes = array();
    $rtwiki_settings = '';
    $rtwiki_custom = '';
    if( is_multisite() ){
        $rtwiki_settings = get_site_option( 'rtwiki_settings', array() );
        $rtwiki_custom = get_site_option( 'rtwiki_custom', array() );
    }
    else {
        $rtwiki_settings = get_option( 'rtwiki_settings', array() );
        $rtwiki_custom = get_option( 'rtwiki_custom', array() );
    }
    if( isset( $rtwiki_custom[0]['slug'] ) )
        $attributes[] = $rtwiki_custom[0]['slug'];
    if( isset( $rtwiki_settings['attribute'] ) )
        $attributes = array_merge ( $attributes, $rtwiki_settings['attribute'] );
    return $attributes;
}