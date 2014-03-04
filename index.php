<?php

/*
  Plugin Name: rtWiki
  Description: Creates a Wiki CPT. Check for pages inside it. If not found will create it. Post filtering for different user groups
  Version: 0.1 BETA
  Author: rtCamp
  Author Uri: http://rtcamp.com
  Contributors: Prannoy Tank, Sohil
 */

/**
 * Main file, contains the plugin metadata and activation processes
 *
 */
if ( ! defined( 'RT_WIKI_VERSION' ) ){
	define( 'RT_WIKI_VERSION', '1.1' );
}

/**
 * The server file system path to the plugin directory
 *
 */
if ( ! defined( 'RT_WIKI_PATH' ) ){
	define( 'RT_WIKI_PATH', plugin_dir_path( __FILE__ ) );
}

/**
 * The url to the plugin directory
 *
 */
if ( ! defined( 'RT_WIKI_URL' ) ){
	define( 'RT_WIKI_URL', trailingslashit( plugin_dir_url( __FILE__ ) ) );
}

/**
 * The server file system path to the admin directory
 *
 */
if ( ! defined( 'RT_WIKI_PATH_ADMIN' ) ){
	define( 'RT_WIKI_PATH_ADMIN', plugin_dir_path( __FILE__ ) . 'app/admin/' );
}

/**
 * The server file system path to the lib directory
 *
 */
if ( ! defined( 'RT_WIKI_PATH_LIB' ) ){
	define( 'RT_WIKI_PATH_LIB', plugin_dir_path( __FILE__ ) . 'lib/' );
}

/**
 * The server file system path to the models directory
 *
 */
if ( ! defined( 'RT_WIKI_PATH_MODELS' ) ){
	define( 'RT_WIKI_PATH_MODELS', plugin_dir_path( __FILE__ ) . 'app/models/' );
}

/**
 * The server file system path to the helper directory
 *
 */
if ( ! defined( 'RT_WIKI_PATH_HELPER' ) ){
	define( 'RT_WIKI_PATH_HELPER', plugin_dir_path( __FILE__ ) . 'app/helper/' );
}

/**
 * The server file system path to the assets directory
 *
 */
if ( ! defined( 'RT_WIKI_PATH_ASSETS' ) ){
	define( 'RT_WIKI_PATH_ASSETS', plugin_dir_path( __FILE__ ) . 'app/assets/' );
}

/**
 * The server file system path to the schema directory
 *
 */
if ( ! defined( 'RT_WIKI_PATH_SCHEMA' ) ){
	define( 'RT_WIKI_PATH_SCHEMA', plugin_dir_path( __FILE__ ) . 'app/schema/' );
}

if ( ! defined( 'RC_TC_BASE_FILE' ) ) define( 'RC_TC_BASE_FILE', __FILE__ );
if ( ! defined( 'RC_TC_BASE_DIR' ) ) define( 'RC_TC_BASE_DIR', dirname( RC_TC_BASE_FILE ) );
if ( ! defined( 'RC_TC_PLUGIN_URL' ) ) define( 'RC_TC_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

/**
 * Auto Loader Function
 *
 * Autoloads classes on instantiation. Used by spl_autoload_register.
 *
 * @param string $class_name The name of the class to autoload
 */
function rtwiki_autoloader( $class_name )
{
	$rtLibPath = array(
		'app/helper/' . $class_name . '.php',
		'app/helper/rtdbmodel/' . $class_name . '.php',
		'app/admin/' . $class_name . '.php',
		'app/models/' . $class_name . '.php',
		'app/main/' . $class_name . '.php', );

	foreach ( $rtLibPath as $path ) {
		$path = RT_WIKI_PATH . $path;
		if ( file_exists( $path ) ){
			include $path;
			break;
		}
	}
}

/**
 * Register the autoloader function into spl_autoload
 */
spl_autoload_register( 'rtwiki_autoloader' );

global $rtWiki;
$rtWiki = new RTWiki();

/**
 * Next File: /app/main/RTWiki.php
 */
