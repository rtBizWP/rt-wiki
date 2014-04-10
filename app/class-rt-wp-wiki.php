<?php

/**
 * Don't load this file directly!
 */
if ( ! defined( 'ABSPATH' ) ){

	exit;
}

/**
 * The main rtWiki Class. This is where everything starts.
 *
 * @author     Dipesh
 */
if ( ! class_exists( 'RT_WP_WIKI' ) ){

	/**
	 * Class RT_WP_WIKI
	 */
	class RT_WP_WIKI
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
            $this->check_rt_biz_dependecy();

            $this->init_globals();

            add_action( 'init', array( $this, 'admin_init' ) );

            //Template include for rtwiki
            add_filter( 'template_include', 'rc_tc_template_chooser', 1 );

            //Rtwiki enqueue scripts
			add_action( 'admin_enqueue_scripts', array( $this, 'rtwiki_admin_enqueue_styles_and_scripts' ) );
			add_action( 'wp_enqueue_scripts', array( $this, 'rtwiki_enqueue_styles_and_scripts' ) );

			//shortcode for wiki archive link
			add_shortcode( 'rt-wiki-child-pages', 'get_rtwiki_archive' );

		}

        function check_rt_biz_dependecy() {
            if ( ! class_exists( 'Rt_Biz' ) ) {
                add_action( 'admin_notices', array( $this, 'rt_biz_admin_notice' ) );
            }
        }

        function rt_biz_admin_notice() { ?>
            <div class="updated">
                <p><?php _e( sprintf( 'WordPress WIKI : It seems that WordPress Contacts plugin is not installed or activated. Please %s / %s it.', '<a href="'.admin_url( 'plugin-install.php?tab=search&s=rt-contacts' ).'">'.__( 'install' ).'</a>', '<a href="'.admin_url( 'plugins.php' ).'">'.__( 'activate' ).'</a>' ) ); ?></p>
            </div>
        <?php }


        /**
		 * Include a file
		 */
		function init_globals()
		{
            global $rt_wiki_settings,$rt_wiki_post_filtering, $rt_wiki_404_redirect,$rt_wiki_admin,
                   $rt_wiki_subscribe, $rt_wiki_daily_change, $rt_wiki_widget, $rt_wiki_diff,
                   $rt_wiki_widget_helper,$rt_wiki_subscribe_model,$rt_wiki_cpt,$rt_wiki_roles;

            $rt_wiki_subscribe_model = new RT_Subscribe_Model();
            $rt_wiki_settings= new Rt_Wiki_Settings();
            $rt_wiki_post_filtering= new Rt_Wiki_Post_Filtering();
            $rt_wiki_404_redirect = new Rt_Wiki_404_Redirect();
            //$rt_wiki_daily_change = new Rt_Wiki_Daily_Changes();
            $rt_wiki_diff = new Rt_Wiki_Diff();
            $rt_wiki_subscribe = new Rt_Wiki_Subscribe();
            $rt_wiki_widget_helper = new Rt_Wiki_Widget_Helper();
            $rt_wiki_roles = new Rt_Wiki_Roles();
            $rt_wiki_cpt = new Rt_Wiki_CPT();
            $rt_wiki_admin = new Rt_Wiki_Admin();

        }

		/**
		 * Update db version
		 */
		function update_database()
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
            $this->update_database();

			global $rt_wiki_admin;

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
            global $rt_wiki_404_redirect;
			wp_register_script( 'rtwiki-custom-script', RT_WIKI_URL . 'app/assets/js/rtwiki-custom-script.js', array( 'jquery' ) );
			wp_enqueue_script( 'rtwiki-custom-script' );
			if ( is_404() ) {
				wp_register_script( 'rtwiki-404-script', RT_WIKI_URL . 'app/assets/js/rtwiki-404-script.js', array( 'jquery' ) );
				wp_localize_script( 'rtwiki-404-script', 'redirectURL', array( $rt_wiki_404_redirect, 'redirect_404' ) );
				wp_enqueue_script( 'rtwiki-404-script' );
			}

			wp_register_style( 'rtwiki-client-styles', RT_WIKI_URL . 'app/assets/css/rtwiki-client-styles.css' );
			wp_enqueue_style( 'rtwiki-client-styles' );
		}

	}

}
