<?php

/**
 * Don't load this file directly!
 */
if ( ! defined( 'ABSPATH' ) ){

	exit;
}

/**
 * rtWiki
 *
 * The main rtWiki Class. This is where everything starts.
 *
 * @package    rtWiki
 * @subpackage Main
 *
 * @author     Dipesh
 */
if ( ! class_exists( 'RTWiki' ) ){

	/**
	 * Class RTWiki
	 */
	class RTWiki
	{

		/**
		 * Constructs the class
		 * Defines constants and excerpt lengths, initiates admin notices,
		 * loads and initiates the plugin, loads translations.
		 *
		 * @global int $bp_media_counter Media counter
		 */
		public function __construct()
		{
			$this->rtwiki_require_once();
			$this->update_db();


			//Rtwiki enqueue scripts
			add_action( 'admin_enqueue_scripts', array( $this, 'rtwiki_admin_enqueue_styles_and_scripts' ) );
			add_action( 'wp_enqueue_scripts', array( $this, 'rtwiki_enqueue_styles_and_scripts' ) );

			//Rtwiki widget area register
			add_action( 'widgets_init', array( $this, 'rt_wiki_widget_area' ) );
			add_action( 'widgets_init', 'rt_wiki_register_widgets' );
			add_action( 'wp_dashboard_setup', 'wiki_add_dashboard_widgets' );

			add_action( 'init', array( $this, 'admin_init' ) );

			//Post filtering
			add_action( 'the_posts', 'rtwiki_search_filter' );
			//add_filter( 'the_content', 'rtwiki_content_filter' );
			add_filter( 'edit_post_link', 'rtwiki_edit_post_link_filter' );
			add_filter( 'comments_array', 'rtwiki_comment_filter', 10, 2 );
			add_filter( 'comments_open', 'rtwiki_comment_form_filter', 10, 2 );

			/* Function to disable feeds for wiki CPT */
			remove_action( 'do_feed_rdf', 'do_feed_rdf', 10, 1 );
			remove_action( 'do_feed_rss', 'do_feed_rss', 10, 1 );
			remove_action( 'do_feed_rss2', 'do_feed_rss2', 10, 1 );
			remove_action( 'do_feed_atom', 'do_feed_atom', 10, 1 );

			// Now we add our own actions, which point to our own feed function
			add_action( 'do_feed_rdf', 'my_do_feed', 10, 1 );
			add_action( 'do_feed_rss', 'my_do_feed', 10, 1 );
			add_action( 'do_feed_rss2', 'my_do_feed', 10, 1 );
			add_action( 'do_feed_atom', 'my_do_feed', 10, 1 );

			//Template include for rtwiki
			add_filter( 'template_include', 'rc_tc_template_chooser', 1 );

			//update subscribe entry
			add_action( 'wp', 'update_subscribe' );

			//Send Content diff through email on wikipostupdate
			add_action( 'save_post', 'send_mail_postupdate_wiki', 99, 1 );

			//shortcode for wiki archive link
			add_shortcode( 'rtwikiarchive', 'get_rtwiki_archive' );

			//change schedual time for daily update
			add_filter( 'cron_schedules', array( $this, 'wiki_add_weekly_schedule' ) );

			//Wiki Daily update
			/*$rtwikidailychange = new RtWikiDailyChanges();
			add_action( 'wiki_daily_event_hook', array( $rtwikidailychange, 'send_daily_change_mail' ) );
			register_activation_hook( __FILE__, array( $this, 'wiki_prefix_activation' ) );
			register_deactivation_hook( __FILE__, array( $this, 'wiki_prefix_deactivation' )  );
			add_action( 'init',array( $this, 'wiki_prefix_setup_schedule' ) );*/

			//disable wiki daily update schedula
			//wp_clear_scheduled_hook( 'wiki_daily_event_hook' );

			if (wp_next_scheduled( 'wiki_daily_event_hook' ) ) {
				var_dump( 'hi' );
			}

		}

		/**
		 * Include a file
		 */
		function rtwiki_require_once()
		{
			require_once RT_WIKI_PATH_ADMIN . 'user-groups.php';
			require_once RT_WIKI_PATH_HELPER . 'rtwiki-functions.php';
			require_once RT_WIKI_PATH_HELPER . 'wiki-settings.php';
			require_once RT_WIKI_PATH_HELPER . 'wiki-post-filtering.php';
			require_once RT_WIKI_PATH_HELPER . 'wiki-single-custom-template.php';
			require_once RT_WIKI_PATH_HELPER . 'wiki-404-redirect.php';
			require_once RT_WIKI_PATH_HELPER . 'wiki-post-subscribe.php';
			require_once RT_WIKI_PATH_HELPER . 'wiki-singlepost-content.php';
			require_once RT_WIKI_PATH_HELPER . 'RtWikiDailyChanges.php';
			require_once RT_WIKI_PATH_ADMIN . 'wiki-widgets.php';
			require_once RT_WIKI_PATH_HELPER . 'RtWikiEmailDiff.php';
		}

		/**
		 * Update db version
		 */
		function update_db()
		{	
			$update = new RT_DB_Update( RT_WIKI_PATH . 'index.php', RT_WIKI_PATH . 'app/schema/', false );
			$update->do_upgrade();
		}

		/**
		 * Load admin screens
		 *
		 * @global RtWikiAdmin $rtwiki_admin Class for loading admin screen
		 */
		function admin_init()
		{
			global $rtwiki_admin,$rtwiki_cpt,$rtwiki_roles;
			$rtwiki_cpt   = new RtWikiCPT();
			$rtwiki_admin = new RtWikiAdmin();
			$rtwiki_roles = new RtWikiRoles();
		}

		/**
		 * Register rtWiki custom sidebar in the widget area.
		 */
		function rt_wiki_widget_area()
		{
			$arg = array(
				'name' => __( 'rtWiki Widget Area', 'rtCamp' ),
				'id' => 'rt-wiki-sidebar',
				'description' => __( 'An optional sidebar for the rtWiki Widget', 'rtCamp' ),
				'before_widget' => '<div id="%1$s" class="widget %2$s sidebar-widget rtp-subscribe-widget-container">',
				'after_widget' => '</div>',
				'before_title' => '<h3 class="widgettitle">',
				'after_title' => '</h3>', ) ;

			register_sidebar( $arg );
		}


		/**
		 * Load stylesheet and j script for admin
		 */
		function rtwiki_admin_enqueue_styles_and_scripts() {
			global $hook_suffix;
			wp_register_script( 'rtwiki-admin-script', RT_WIKI_URL . 'app/assets/js/rtwiki-admin-script.js', array( 'jquery' ) );
			wp_enqueue_script( 'rtwiki-admin-script' );

			wp_register_script( 'rtwiki-new-post-script', RT_WIKI_URL . 'app/assets/js/rtwiki-new-post-script.js', array( 'jquery' ) );


			if ( is_admin() && $hook_suffix == 'post-new.php' ) {
				wp_enqueue_script( 'rtwiki-new-post-script' );
			}

			wp_register_style( 'rtwiki-admin-styles', RT_WIKI_URL . 'app/assets/css/rtwiki-admin-styles.css' );

			if (is_admin())
				wp_enqueue_style( 'rtwiki-admin-styles' );
		}

		/**
		 * Load stylesheet and j script for client
		 */
		function rtwiki_enqueue_styles_and_scripts() {
			wp_register_script( 'rtwiki-custom-script', RT_WIKI_URL . 'app/assets/js/rtwiki-custom-script.js', array( 'jquery' ) );
			wp_enqueue_script( 'rtwiki-custom-script' );
			if ( is_404() ) {
				wp_register_script( 'rtwiki-404-script', RT_WIKI_URL . 'app/assets/js/rtwiki-404-script.js', array( 'jquery' ) );
				wp_localize_script( 'rtwiki-404-script', 'redirectURL', "<a href='" . redirect_404() . "'>" . __( 'Click here. ', 'rtCamp' ) . '</a>' . __( 'If you want to add this post', 'rtCamp' ) );
				wp_enqueue_script( 'rtwiki-404-script' );
			}

			wp_register_style( 'rtwiki-client-styles', RT_WIKI_URL . 'app/assets/css/rtwiki-client-styles.css' );
			wp_enqueue_style( 'rtwiki-client-styles' );
		}

		/**
		 * Wiki daily update schedule add while plugin activated
		 */
		function wiki_prefix_activation() {
			wp_schedule_event( time(), 'weekly', 'wiki_daily_event_hook' );
		}

		/**
		 *  Wiki daily update schedule remove while plugin deactivated.
		 */
		function wiki_prefix_deactivation() {
			wp_clear_scheduled_hook( 'wiki_daily_event_hook' );
		}

		/**
		 *  Wiki daily update schedule start if plugin already active
		 */
		function wiki_prefix_setup_schedule() {
			if ( ! wp_next_scheduled( 'wiki_daily_event_hook' ) ) {
				wp_schedule_event( time(), 'weekly', 'wiki_daily_event_hook' );
			}
		}

		function wiki_add_weekly_schedule( $schedules ) {
			$schedules['weekly'] = array(
				'interval' => 30 * 60,
				'display' => __( 'Every Other Week', 'my-plugin-domain' )
			);
			return $schedules;
		}

	}

}
