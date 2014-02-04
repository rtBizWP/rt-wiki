<?php
/*
  Plugin Name: rtWiki
  Description: Creates a Wiki CPT. Check for pages inside it. If not found will create it. Post filtering for different user groups
  Version: 1.1
  Author: rtCamp
  Author Uri: http://rtcamp.com
  Contributors: Prannoy Tank, Sohil
 */

require_once dirname(__FILE__) . '/lib/wiki-CPT.php';
require_once dirname(__FILE__) . '/lib/user-groups.php';
require_once dirname(__FILE__) . '/lib/wiki-single-custom-template.php';
require_once dirname(__FILE__) . '/lib/wiki-404-redirect.php';
require_once dirname(__FILE__) . '/lib/wiki-post-filtering.php';
require_once dirname(__FILE__) . '/lib/wiki-post-subscribe.php';
require_once dirname(__FILE__) . '/lib/wiki-singlepost-content.php';
require_once dirname(__FILE__) . '/lib/class-daily-changes.php';
require_once dirname(__FILE__) . '/lib/wiki-sidebar.php';
require_once dirname(__FILE__) . '/lib/wiki-widgets.php';


wp_register_style( 'rtwiki-admin-styles', plugins_url('/css/rtwiki-admin-styles.css', __FILE__) );
if( is_admin() )
    wp_enqueue_style( 'rtwiki-admin-styles' );

wp_register_script('rtwiki-custom-script', plugins_url('/js/rtwiki-custom-script.js', __FILE__), array('jquery'));
wp_enqueue_script('rtwiki-custom-script');

if (!defined('RC_TC_BASE_FILE'))
    define('RC_TC_BASE_FILE', __FILE__);
if (!defined('RC_TC_BASE_DIR'))
    define('RC_TC_BASE_DIR', dirname(RC_TC_BASE_FILE));
if (!defined('RC_TC_PLUGIN_URL'))
    define('RC_TC_PLUGIN_URL', plugin_dir_url(__FILE__));


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
