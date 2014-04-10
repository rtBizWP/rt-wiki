<?php

/*
  Plugin Name: WordPress Wiki
  Plugin URI: http://rtcamp.com/
  Description: Manage wiki in multiple groups
  Version: 0.1.30
  Author: rtCamp
  Author URI: http://rtcamp.com
  License: GPL
  Text Domain: rt_wiki
 */

/**
 * Main file, contains the plugin metadata and activation processes
 *
 */
if ( ! defined( 'RT_WIKI_VERSION' ) ){
	define( 'RT_WIKI_VERSION', '1.1' );
}

/**
 * Text domain for wiki plugin
 *
 */
if ( !defined( 'RT_WIKI_TEXT_DOMAIN' ) ) {
    define( 'RT_WIKI_TEXT_DOMAIN', 'rt_wiki' );
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
 * The server file system path to the App directory
 *
 */
if ( ! defined( 'RT_WIKI_PATH_ADMIN' ) ){
    define( 'RT_WIKI_PATH_APP', plugin_dir_path( __FILE__ ) . 'app/' );
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
	define( 'RT_WIKI_PATH_LIB', plugin_dir_path( __FILE__ ) . 'app/lib/' );
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

include_once RT_WIKI_PATH_LIB . 'wp-helpers.php';

function rt_wiki_include() {

    include_once RT_WIKI_PATH_HELPER . 'rtwiki-functions.php';
    include_once RT_WIKI_PATH_ADMIN . 'rtwiki-widgets.php';

    global $rtwiki_app_autoload, $rtwiki_admin_autoload, $rtwiki_models_autoload, $rtwiki_helper_autoload;
    $rtwiki_app_autoload = new RT_WP_Autoload( RT_WIKI_PATH_APP );
    $rtwiki_admin_autoload = new RT_WP_Autoload( RT_WIKI_PATH_ADMIN );
    $rtwiki_models_autoload = new RT_WP_Autoload( RT_WIKI_PATH_MODELS );
    $rtwiki_helper_autoload = new RT_WP_Autoload( RT_WIKI_PATH_HELPER );

}

function rt_wiki_init() {

    rt_wiki_include();

    global $rt_wp_wiki;
    $rt_wp_wiki = new RT_WP_WIKI();

}
add_action( 'rt_biz_init', 'rt_wiki_init', 1 );

/**
 * Next File: /app/class-rt-wp-wiki.php
 */
