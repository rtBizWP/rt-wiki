<?php
/*
  Plugin Name: rtWiki
  Description: Declares and create a wiki CPT.Adds email metafield to user group taxonomy.
  Version: 1.0
  Author: Prannoy Tank a.k.a Wolverine
 */

/**
 * Admin Files Loaded
 */
if ( !defined( 'RT_WIKI_VERSION' ) ) {
	define( 'RT_WIKI_VERSION', '1.0' );
}
if ( !defined( 'RT_WIKI_PATH' ) ) {
	define( 'RT_WIKI_PATH', plugin_dir_path( __FILE__ ) );
}
if ( !defined( 'RT_WIKI_URL' ) ) {
	define( 'RT_WIKI_URL', plugin_dir_url( __FILE__ ) );
}
if ( !defined( 'RT_WIKI_PATH_ADMIN' ) ) {
	define( 'RT_WIKI_PATH_ADMIN', plugin_dir_path( __FILE__ ) . 'admin/' );
}
if ( !defined( 'RT_WIKI_PATH_LIB' ) ) {
	define( 'RT_WIKI_PATH_LIB', plugin_dir_path( __FILE__ ) . 'lib/' );
}
if ( !defined( 'RT_WIKI_PATH_MODELS' ) ) {
	define( 'RT_WIKI_PATH_MODELS', plugin_dir_path( __FILE__ ) . 'models/' );
}
if ( !defined( 'RT_WIKI_PATH_HELPER' ) ) {
	define( 'RT_WIKI_PATH_HELPER', plugin_dir_path( __FILE__ ) . 'helper/' );
}

function rtwiki_include_class_file( $dir ) {
	if ( $dh = opendir( $dir ) ) {
		while ( $file = readdir( $dh ) ) {
			//Loop
			if ( $file !== '.' && $file !== '..' && $file[0] !== '.' ) {
				if ( is_dir( $dir . $file ) ) {
					rtwiki_include_class_file( $dir . $file . '/' );
				} else {
					include_once $dir . $file;
				}
			}
		}
		closedir( $dh );
		return 0;
	}
}

function rtwiki_include() {
	$rtWooCLIncludePaths = array(
		RT_WIKI_PATH_LIB,
		RT_WIKI_PATH_MODELS,
		RT_WIKI_PATH_HELPER,
		RT_WIKI_PATH_ADMIN,
	);
	foreach ( $rtWooCLIncludePaths as $path ) {
		rtwiki_include_class_file( $path );
	}
}

function rtwiki_init() {
	rtwiki_include();

	// DB Upgrade
	$updateDB = new RTDBUpdate( false, RT_WIKI_PATH . 'index.php' , RT_WIKI_PATH . 'schema/' );
	$updateDB->do_upgrade();

	global $rtWikiAdmin;
	$rtWikiAdmin = new RtWikiAdmin();
}
add_action( 'init', 'rtwiki_init' );
