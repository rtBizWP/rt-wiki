<?php
/**
 * Don't load this file directly!
 */
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * rtWiki
 *
 * The RtWikiAttributeTaxonomyModel Class. Model class for AttributeTaxonomy
 *
 * @package    RtWikiAdmin
 * @subpackage Models
 *
 * @author     Udit
 */
if ( ! class_exists( 'RtWikiAttributeTaxonomyModel' ) ){
	/**
	 * Class RtWikiAttributeTaxonomyModel
	 */
	class RtWikiAttributeTaxonomyModel extends RTDBModels
	{
		/**
		 *
		 */
		public function __construct()
		{
			parent::__construct( 'rtwiki_attribute_taxonomy' );
		}

		/**
		 * @param        $attribute_name
		 * @param string $post_type
		 *
		 * @return bool
		 */
		function attribute_exists( $attribute_name, $post_type = '' )
		{
			$attributes = $this->get_all_attributes( $post_type );
			foreach ( $attributes as $attribute ) {
				if ( $attribute_name == $attribute->attribute_name ){
					return true;
				}
			}

			return false;
		}

		/**
		 *
		 * @param string $post_type
		 *
		 * @return type
		 */
		function get_all_attributes( $post_type = '' )
		{
			$args = array();
			if ( ! empty ( $post_type ) ){
				$args[ 'attribute_post_type' ] = array( 'compare' => '=', 'value' => explode( ',', $post_type ) );
			}

			return parent::get( $args );
		}

		/**
		 * @param $attribute_id
		 *
		 * @return bool
		 */
		function get_attribute( $attribute_id )
		{
			$args      = array( 'id' => $attribute_id );
			$attribute = parent::get( $args );
			if ( empty( $attribute ) ){
				return false;
			}

			return $attribute[ 0 ];
		}

		/**
		 * @param $attribute_id
		 *
		 * @return bool
		 */
		function get_attribute_name( $attribute_id )
		{
			$attribute = $this->get_attribute( $attribute_id );
			if ( empty( $attribute ) ){
				return false;
			}

			return $attribute->attribute_name;
		}

		/**
		 * @param $attribute_name
		 * @param $post_type
		 *
		 * @return array|type
		 */
		function get_attribute_by_name( $attribute_name, $post_type )
		{
			$args   = array();
			$return = array();
			if ( ! empty( $attribute_name ) && ! empty ( $post_type ) ){
				$args[ 'attribute_post_type' ] = array( 'compare' => '=', 'value' => explode( ',', $post_type ) );
				$args[ 'attribute_name' ]      = array( 'compare' => '=', 'value' => explode( ',', $attribute_name ) );
				$return = parent::get( $args );
			}

			return $return;
		}

		/**
		 * @param $data
		 *
		 * @return type
		 */
		function add_attribute( $data )
		{
			return parent::insert( $data );
		}

		/**
		 * @param $data
		 * @param $where
		 *
		 * @return mixed
		 */
		function update_attribute( $data, $where )
		{
			return parent::update( $data, $where );
		}

		/**
		 * @param $where
		 *
		 * @return type
		 */
		function delete_attribute( $where )
		{
			return parent::delete( $where );
		}
	}
}