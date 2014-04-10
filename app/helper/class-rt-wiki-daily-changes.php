<?php
/**
 * Don't load this file directly!
 */
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Helper functions for RtWiki Page not Found
 * send mail to subscribers of daily change in wiki
 *
 * @author     Dipesh
 */
if ( !class_exists( 'Rt_Wiki_Daily_Changes' ) ) {

    /**
     * Class Rt_Wiki_Daily_Changes
     */
    class Rt_Wiki_Daily_Changes
    {
        /**
         * Object initialization
         */
        public function __construct() {
            $this->hook();
        }

        /**
         * Apply Filter Wiki's Filter
         */
        function hook(){

            //change schedual time for daily update
            add_filter( 'cron_schedules', array( $this, 'wiki_add_weekly_schedule' ) );

            add_action( 'wiki_daily_event_hook', array( this, 'send_daily_change_mail' ) );
            register_activation_hook( __FILE__, array( $this, 'wiki_prefix_activation' ) );
            register_deactivation_hook( __FILE__, array( $this, 'wiki_prefix_deactivation' )  );
            add_action( 'init',array( $this, 'wiki_prefix_setup_schedule' ) );

            //disable wiki daily update schedula
            //wp_clear_scheduled_hook( 'wiki_daily_event_hook' );
        }

        /**
         * Send mail On post Update having body as diff of content for daily update
         *
         * @return type
         */
        function get_users_subscribeposts_list(){

            global $rt_wiki_post_filtering,$rt_wiki_subscribe;

            $subscriberslist = array();
            $blogusers       = get_users( );
            $supported_posts = rtwiki_get_supported_attribute();
            if ( is_array( $supported_posts ) && ! empty( $supported_posts ) ){
                foreach ( $supported_posts as $post_types ) {
                    $wp_query = new WP_Query( array( 'post_type' => $post_types, ) );
                    if ( $wp_query->have_posts() ){
                        while ( $wp_query->have_posts() ) {
                            $wp_query->the_post();
                            foreach ( $blogusers as $user ) {
                                if ( $rt_wiki_subscribe->is_post_subscribe_cur_user( $user->ID ) && $rt_wiki_post_filtering->get_permission( get_the_ID(), $user->ID, 1 ) ){
                                    $subscriberslist[ $user->ID ][ ] = get_the_ID();

                                }
                            }
                        }
                    }
                }
            }
            return $subscriberslist;
        }

        /**
         * Send mail to subscribers as daily changes
         */
        function send_daily_change_mail()
        {
            var_dump( 'Schedule called' );
            $subscriberslist = $this->get_users_subscribeposts_list();
            foreach ( $subscriberslist as $key => $value ) {
                $user_info = get_userdata( $key );
                $finalBody = '';
                foreach ( $value as $postid ) {
                    $finalBody .= $this->get_post_content_diff( $postid );
                    if ( isset( $finalBody ) && $finalBody != '' ){
                        $finalBody .= '<br/>';
                    }
                }
                if ( isset( $finalBody ) && $finalBody != '' ){
                    add_filter( 'wp_mail_content_type', 'set_html_content_type' );
                    $subject    = 'Daily Update : RtWiki';
                    $headers[ ] = 'From: rtcamp.com <no-reply@' . sanitize_title_with_dashes( get_bloginfo( 'name' ) ) . '.com>';
                    wp_mail( $user_info->user_email, $subject, $finalBody, $headers );
                    remove_filter( 'wp_mail_content_type', 'set_html_content_type' );
                }
            }
        }

        /**
         * get difference for wiki
         *
         * @param $postID
         * @return string
         */
        function get_post_content_diff( $postID )
        {
            global $rt_wiki_diff;
            $revision     = wp_get_post_revisions( $postID );
            $content   = array();
            $title     = array();
            $modifieddate = array();
            $finalBody    = '';

            foreach ( $revision as $revisions ) {
                $content[ ]     = $revisions->post_content;
                $title[ ]       = $revisions->post_title;
                $modifieddate[] = $revisions->post_modified;
            }
            if ( ! empty( $content ) && date( 'Y-m-d', strtotime( $modifieddate[ 0 ] ) ) == date( 'Y-m-d' ) ){
                $url       = 'Page Link:' . get_permalink( $postID ) . '<br>';
                $body      = $rt_wiki_diff->rtwiki_text_diff( $title[ count( $title ) - 1 ], $title[ 0 ], $content[ count( $title ) - 1 ], $content[ 0 ] );
                $finalBody = $url . '<br>' . $body;
            }

            return $finalBody;
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

        /**
         * Wiki mail schedule
         *
         * @param $schedules
         * @return mixed
         */
        function wiki_add_weekly_schedule( $schedules ) {
            $schedules['weekly'] = array(
                'interval' => 30 * 60,
                'display' => __( 'Every Other Week', 'my-plugin-domain' )
            );
            return $schedules;
        }

    }
}