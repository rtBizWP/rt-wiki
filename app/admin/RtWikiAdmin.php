<?php

/**
 * Don't load this file directly!
 */
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * rtWiki
 *
 * The RtWikiAdmin Class. loading admin screen
 *
 * @package    RtWikiAdmin
 * @subpackage Admin
 *
 * @author     Udit
 */
if ( ! class_exists( 'RtWikiAdmin' ) ){

	class RtWikiAdmin
	{

		/**
		 * construct
		 */
		public function __construct()
		{
			global $rtwiki_cpt;
			$this->init_attributes();
			add_action( 'admin_init', array( $rtwiki_cpt, 'wiki_permission_metabox' ) );

			//rtWiki Attributes and taxonomies
			add_action( 'admin_menu', array( $this, 'register_pages' ) );
			$this->register_taxonomies();
			//flush_rewrite_rules( true );

			//Wiki Setting : wiki-settings.php
			add_action( 'admin_menu', 'fwds_plugin_settings' );
			rtwiki_save_settings();

			//Post filtering
			add_action( 'wp_trash_post', 'my_wp_trash_post' );
			add_filter( 'page_row_actions', 'remove_quick_edit', 10 );
			add_action( 'admin_init', 'post_check' );
			//add_filter( 'user_has_cap', 'add_capabilities', 10, 4 );

			//Yoast plugin Sitemap rtWiki filtering
			add_filter( 'wpseo_sitemaps_supported_taxonomies', 'rtwiki_sitemap_taxonomies' );
			add_filter( 'wpseo_sitemaps_supported_post_types', 'rtwiki_sitemap_posttypes' );



		}

		/**
		 * init a globle variable
		 *
		 * @global RtWikiAttributeTaxonomyModel $rtWikiAttributesModel
		 * @global RtWikiAttributes             $rtWikiAttributes
		 * @global RtWikiSubscribeModel         $rtWikiSubscribe
		 */
		function init_attributes()
		{
			global $rtWikiAttributesModel, $rtWikiAttributes, $rtWikiSubscribe;
			$rtWikiAttributesModel = new RtWikiAttributeTaxonomyModel();
			$rtWikiSubscribe       = new RtWikiSubscribeModel();
			$rtWikiAttributes      = new RtWikiAttributes();
		}

		/**
		 * add attributes page link in menu bar
		 *
		 * @global RtWikiAttributes $rtWikiAttributes
		 */
		function register_pages()
		{
			global $rtWikiAttributes;
			$attributes = rtwiki_get_supported_attribute();
			if ( is_array( $attributes ) && ! empty( $attributes ) && current_user_can('edit_wiki') ){
				foreach ( $attributes as $attribute ) {
					if ( $attribute !== 'post' ){
						add_submenu_page( 'edit.php?post_type=' . $attribute, __( 'Attributes' ), __( 'Attributes' ), 'administrator', $attribute . '-attributes', array( $rtWikiAttributes, 'attributes_page' ) );
					} else {
						add_submenu_page( 'edit.php', __( 'Attributes' ), __( 'Attributes' ), 'administrator', $attribute . '-attributes', array( $rtWikiAttributes, 'attributes_page' ) );
					}
				}
			}
		}

		/**
		 * create a texonomies
		 *
		 * @global RtWikiAttributeTaxonomyModel $rtWikiAttributesModel
		 * @global RtWikiAttributes             $rtWikiAttributes
		 */
		function register_taxonomies()
		{
			global $rtWikiAttributesModel, $rtWikiAttributes;
			$tax_attributes = rtwiki_get_supported_attribute();
			if ( is_array( $tax_attributes ) && ! empty( $tax_attributes ) ){
				foreach ( $tax_attributes as $value ) {
					$attributes = $rtWikiAttributesModel->get_all_attributes( $value );
					if ( is_array( $attributes ) ){
						foreach ( $attributes as $attr ) {
							if ( is_object( $attr ) ){
								$rtWikiAttributes->register_taxonomy( $value, $attr->id );
							} else {
								$rtWikiAttributes->register_taxonomy( $value, 0 );
							}
						}
					}
				}
			}
		}
	}

}
