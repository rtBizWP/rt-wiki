<?php

/**
 * rtwiki Functions
 *
 * Helper functions for rtwiki
 *
 * @author udit
 */

function rtwiki_sanitize_taxonomy_name( $taxonomy ) {
	$taxonomy = strtolower( stripslashes( strip_tags( $taxonomy ) ) );
	$taxonomy = preg_replace( '/&.+?;/', '', $taxonomy ); // Kill entities
	$taxonomy = str_replace( array( '.', '\'', '"' ), '', $taxonomy ); // Kill quotes and full stops.
	$taxonomy = str_replace( array( ' ', '_' ), '-', $taxonomy ); // Replace spaces and underscores.

	return $taxonomy;
}

function rtwiki_attribute_taxonomy_name( $name ) {
	return  rtwiki_sanitize_taxonomy_name( $name );
}
