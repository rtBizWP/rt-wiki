<?php

/**
 * Don't load this file directly!
 */
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * rtWiki
 *
 * The RtWikiSubscribeModel Class. Model class for Subscribers
 *
 * @package    RtWikiAdmin
 * @subpackage Models
 *
 * @author     Udit
 */
if ( ! class_exists( 'RtWikiSubscribeModel' ) ){

	class RtWikiSubscribeModel extends RT_DB_Model
	{

		public function __construct()
		{
			parent::__construct( 'rtwiki_subscribe' );
		}

		function is_post_subscibe( $postid, $userid )
		{
			$subscribers = $this->get_subscriber( $postid, $userid );
			foreach ( $subscribers as $subscriber ) {
				if ( $userid == $subscriber->attribute_userid ){
					return true;
				}
			}

			return false;
		}

		function is_subpost_subscibe( $postid, $userid )
		{
			$subscribers = $this->get_subscriber( $postid, $userid );
			foreach ( $subscribers as $subscriber ) {
				if ( $subscriber->attribute_sub_subscribe == 1 ){
					return true;
				}
			}

			return false;
		}

		function get_subscriber( $postid, $userid )
		{
			$args   = array();
			$return = array();
			if ( ! empty( $postid ) ){
				$args[ 'attribute_postid' ] = array( 'compare' => '=', 'value' => explode( ',', $postid ) );
				$args[ 'attribute_userid' ] = array( 'compare' => '=', 'value' => explode( ',', $userid ) );
				$return = parent::get( $args );
			}

			return $return;
		}

		function get_subscribers( $postid )
		{
			$args   = array();
			$return = array();
			if ( ! empty( $postid ) ){
				$args[ 'attribute_postid' ] = array( 'compare' => 'in', 'value' => explode( ',', $postid ) );
				$subscribers = parent::get( $args );
				foreach ( $subscribers as $subscriber ) {
					$return[ ] = $subscriber->attribute_userid;
				}
			}

			return $return;
		}

		function get_parent_subpost_subscribers( $postid )
		{
			$args   = array();
			$return = array();
			if ( ! empty( $postid ) ){
				$args[ 'attribute_postid' ] = array( 'compare' => 'in', 'value' => explode( ',', $postid ) );
				$subscribers = parent::get( $args );
				foreach ( $subscribers as $subscriber ) {
					if ( $subscriber->attribute_sub_subscribe == 1 ) $return[ ] = $subscriber->attribute_userid;
				}
			}

			return $return;
		}

		function add_subscriber( $data )
		{
            global $wpdb;
			return $wpdb->insert( $this->table_name, $data );
		}

		function update_subscriber( $data, $where )
		{
			return parent::update( $data, $where );
		}

		function delete_subscriber( $where )
		{
			return parent::delete( $where );
		}

	}

}