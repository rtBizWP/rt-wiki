<?php
/**
 * Don't load this file directly!
 */
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * The RtWikiAdmin Class. loading admin screen
 *
 * @author     Dipesh
 */
if ( ! class_exists( 'Rt_Wiki_Admin' ) ){

	class Rt_Wiki_Admin
	{
        var $attributes_page_slug = 'rtwiki-attributes';

		/**
		 * Object initialization
		 */
		public function __construct()
		{
			$this->hook();
		}

		/**
		 * Apply Hook/Filter for Wiki's
		 */
		function hook()
		{
            add_action( 'admin_menu', array( $this, 'register_pages' ) );

            add_action( 'widgets_init', array( $this, 'rt_wiki_widget_area' ) );
            add_action( 'widgets_init', array( $this, 'rt_wiki_register_widgets' ) );
            add_action( 'wp_dashboard_setup', array( $this, 'wiki_add_dashboard_widgets' ) );
		}

		/**
		 * add attributes page link in menu bar
		 */
		function register_pages()
		{
            global $rt_attributes, $rt_wiki_roles, $rt_attributes_model, $rt_attributes_relationship_model;
            $rt_attributes = new RT_Attributes( 'rt_wiki' );
            $attributes = rtwiki_get_supported_attribute();
            $terms_caps = array(
                'manage_terms' => $rt_wiki_roles->global_caps['manage_rtwiki_terms'],
                'edit_terms' => $rt_wiki_roles->global_caps['edit_rtwiki_terms'],
                'delete_terms' => $rt_wiki_roles->global_caps['delete_rtwiki_terms'],
                'assign_terms' => $rt_wiki_roles->global_caps['assign_rtwiki_terms'],
            );

            if ( is_array( $attributes ) && ! empty( $attributes ) && current_user_can('edit_wiki') ){
                foreach ( $attributes as $attribute ) {
                    if ( $attribute !== 'post' ){
                        var_dump($rt_attributes->add_attributes_page( $this->attributes_page_slug, 'edit.php?post_type='.$attribute, '', $rt_wiki_roles->global_caps['manage_attributes'], $terms_caps, $render_type = false, $storage_type = false, $orderby = true ));
                    } else {
                        $rt_attributes->add_attributes_page( $this->attributes_page_slug, 'edit.php', '', $rt_wiki_roles->global_caps['manage_attributes'], $terms_caps, $render_type = false, $storage_type = false, $orderby = true );
                    }
                }
            }
            $rt_attributes_model = new RT_Attributes_Model();
            $rt_attributes_relationship_model = new RT_Attributes_Relationship_Model();
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
