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

            add_action( 'widgets_init', array( $this, 'rt_wiki_widget_area' ) );
            add_action( 'widgets_init', array( $this, 'rt_wiki_register_widgets' ) );
            add_action( 'wp_dashboard_setup', array( $this, 'wiki_add_dashboard_widgets' ) );

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
            global $rt_wiki_settings,$rt_wiki_post_filtering, $rt_wiki_404_redirect,
                   $rt_wiki_subscribe, $rt_wiki_daily_change, $rt_wiki_acl, $rt_wiki_diff,
                   $rt_wiki_widget_helper,$rt_wiki_subscribe_model,$rt_wiki_cpt,$rt_wiki_roles,
                   $rt_attributes,$rt_attributes_model, $rt_attributes_relationship_model;

            $rt_wiki_subscribe_model = new RT_Subscribe_Model();
            $rt_wiki_settings= new Rt_Wiki_Settings();
            $rt_attributes = new RT_Attributes( RT_WIKI_TEXT_DOMAIN );
            $rt_attributes_model = new RT_Attributes_Model();
            $rt_attributes_relationship_model = new RT_Attributes_Relationship_Model();
            $rt_wiki_post_filtering= new Rt_Wiki_Post_Filtering();
            $rt_wiki_404_redirect = new Rt_Wiki_404_Redirect();
            //$rt_wiki_daily_change = new Rt_Wiki_Daily_Changes();
            $rt_wiki_diff = new Rt_Wiki_Diff();
            $rt_wiki_subscribe = new Rt_Wiki_Subscribe();
            $rt_wiki_widget_helper = new Rt_Wiki_Widget_Helper();
            $rt_wiki_roles = new Rt_Wiki_Roles();
            $rt_wiki_cpt = new Rt_Wiki_CPT();
            $rt_wiki_acl = new Rt_Wiki_ACL();

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
            $rt_wiki_admin = new Rt_Wiki_Admin();
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
            $supported_posts = rtwiki_get_supported_attribute();
            if ( is_404() && is_array( $supported_posts ) && in_array( get_query_var( 'post_type' ), $supported_posts ) ){
				wp_register_script( 'rtwiki-404-script', RT_WIKI_URL . 'app/assets/js/rtwiki-404-script.js', array( 'jquery' ) );
				wp_localize_script( 'rtwiki-404-script', 'redirectURL', $rt_wiki_404_redirect->redirect_404() );
				wp_enqueue_script( 'rtwiki-404-script' );
			}

			wp_register_style( 'rtwiki-client-styles', RT_WIKI_URL . 'app/assets/css/rtwiki-client-styles.css' );
			wp_enqueue_style( 'rtwiki-client-styles' );
		}

        /**
         * Register rtWiki custom sidebar in the widget area.
         */
        function rt_wiki_widget_area()
        {
            $arg = array(
                'name' => __( 'Wiki Single page Widget', 'rtCamp' ),
                'id' => 'rt-wiki-single-sidebar',
                'description' => __( 'An optional sidebar for the Wiki single page Widget', 'rtCamp' ),
                'before_widget' => '<div id="%1$s" class="widget %2$s sidebar-widget rtp-subscribe-widget-container">',
                'after_widget' => '</div>',
                'before_title' => '<h3 class="widgettitle">',
                'after_title' => '</h3>', ) ;

            register_sidebar( $arg );

            $archive_arg = array(
                'name' => __( 'Wiki Archive page Widget', 'rtCamp' ),
                'id' => 'rt-wiki-archive-sidebar',
                'description' => __( 'An optional sidebar for the Wiki Archive page Widget', 'rtCamp' ),
                'before_widget' => '<div id="%1$s" class="widget %2$s sidebar-widget rtp-subscribe-widget-container">',
                'after_widget' => '</div>',
                'before_title' => '<h3 class="widgettitle">',
                'after_title' => '</h3>', ) ;

            register_sidebar( $archive_arg );
        }

        /**
         * register rtwiki widgets
         */
        function rt_wiki_register_widgets()
        {
            register_widget( 'RtWikiContributers' );
            register_widget( 'RtWikiSubPage' );
            register_widget( 'RtWikiPageSubscribe' );
            register_widget( 'RtWikiTaxonimies' );
        }

        /**
         * Add a widget to the dashboard.
         * This function is hooked into the 'wp_dashboard_setup' action below.
         * wp_add_dashboard_widget(slug,Title,Display function)
         */
        function wiki_add_dashboard_widgets()
        {

            wp_add_dashboard_widget( 'dashboard_wiki', 'Wiki Posts', array( $this, 'rt_list_wikis' ) );
        }

        /**
         * Function to add wiki activity to the dashboard.
         */
        function rt_list_wikis()
        {
            $args        = array( 'post_type' => 'revision', 'date_query' => array( 'before' => date( 'Y-m-d', strtotime( '+1 day' ) ), 'after' => date( 'Y-m-d', strtotime( '-1 day' ) ), 'inclusive' => true, 'column' => 'post_date' ), 'posts_per_page' => 10, 'post_status' => 'inherit' );
            $query       = new WP_Query( $args );
            $post_parent = array();
            if ( $query->have_posts() ){
                ?>
                <div id="wiki-widget">
                    <?php
                    foreach ( $query->posts as $posts ) {
                        if ( ! in_array( $posts->post_parent, $post_parent ) && $this->is_wiki_post_type( $posts->post_parent ) ){
                            $revision_args = array( 'post_type' => 'revision', 'post_status' => 'inherit', 'date_query' => array( 'after' => date( 'Y-m-d', strtotime( '-1 day' ) ), ), 'post_parent' => $posts->post_parent, );
                            $revisions     = new WP_Query( $revision_args );
                            foreach ( $revisions->posts as $revision ) {
                                if ( 'Auto Draft' == $revision->post_title ) continue;
                                $date     = date( 'Y-m-d H:i:s', strtotime( $revision->post_date ) );
                                $hour_ago = date_diff( new DateTime(), new DateTime( $date ) );
                                if ( $hour_ago->d == 0 ){
                                    if ( $hour_ago->h > 0 ){
                                        if ( $hour_ago->h > 1 ) $hour_ago = $hour_ago->h . ' hours ago'; else
                                            $hour_ago = $hour_ago->h . ' hour ago';
                                    } else {
                                        if ( $hour_ago->i > 1 ) $hour_ago = $hour_ago->i . ' minutes ago'; else
                                            $hour_ago = $hour_ago->i . ' minute ago';
                                    }
                                } else
                                    $hour_ago = $date;
                                ?>
                                <div class='rtwiki-diff'>
                                    <?php echo get_avatar( $revision->post_author, '50' ); ?>
                                    <div class='rtwiki-diff-wrap'>
                                        <h4 class='rtwiki-diff-meta'>
                                            <cite class='rtwiki-diff-author'><a
                                                    href='<?php echo get_author_posts_url( $revision->post_author ); ?>'><?php echo esc_html( ucwords( get_the_author_meta( 'display_name', $revision->post_author ) ) ); ?></a></cite>
                                            <?php echo esc_html( __( 'has edited', 'rtCamp' ) ); ?>
                                            <a href='post.php?post=<?php echo esc_attr( $posts->post_parent ); ?>&action=edit'><?php echo esc_attr( $revision->post_title ); ?></a>
                                            <?php echo esc_html( __( '(' . $hour_ago . ')', 'rtCamp' ) ); ?>
                                            <a href='revision.php?revision=<?php echo esc_attr( $revision->ID ); ?>'><?php echo esc_html( __( 'View Diff', 'rtCamp' ) ); ?></a>
                                        </h4>
                                    </div>
                                </div>
                            <?php
                            }
                            array_push( $post_parent, $posts->post_parent );
                            wp_reset_postdata();
                        }
                    }
                    wp_reset_postdata();
                    ?>
                </div>
            <?php
            }
        }

        /**
         * Function to check whether the post type is registered in rtWiki plugin setting.
         *
         * @param int|\type $post_id
         *
         * @global type $post
         *
         * @return boolean
         */
        function is_wiki_post_type( $post_id = 0 )
        {
            global $post;
            if ( is_multisite() ){
                $rtwiki_settings = get_site_option( 'rtwiki_settings', array() );
                $rtwiki_custom   = get_site_option( 'rtwiki_custom', array() );
            } else {
                $rtwiki_settings = get_option( 'rtwiki_settings', array() );
                $rtwiki_custom   = get_option( 'rtwiki_custom', array() );
            }
            $wiki_posts = array( 'wiki' );
            if ( isset( $rtwiki_custom[ 0 ][ 'slug' ] ) && ! empty( $rtwiki_custom[ 0 ][ 'slug' ] ) ) array_push( $wiki_posts, $rtwiki_custom[ 0 ][ 'slug' ] );
            if ( $post_id == 0 && $post->post_parent != 0 ) $post_id = $post->post_parent;
            $post_type = get_post_type( $post_id );
            if ( in_array( $post_type, $wiki_posts, true ) ) return true; else
                return false;
        }

	}
}
