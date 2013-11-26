<?php
/**
 * Don't load this file directly!
 */
if ( ! defined( 'ABSPATH' ) )
	exit;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of RtWikiAdmin
 *
 * @author udit
 */
if ( !class_exists( 'RtWikiAdmin' ) ) {
	class RtWikiAdmin {
		public function __construct() {

			global $rtWikiAttributesModel;
			$rtWikiAttributesModel = new RtWikiAttributeTaxonomyModel();

			$this->init_attributes();
			add_action('admin_menu', array($this, 'register_pages'));

			$this->register_taxonomies();
		}

		function register_taxonomies() {
			global $rtWikiAttributesModel, $rtWikiAttributes;
			$attributes = $rtWikiAttributesModel->get_all_attributes();
			foreach ($attributes as $attr) {
				$rtWikiAttributes->register_taxonomy( 'wiki', $attr->id );
			}
		}

		function register_pages() {
			global $rtWikiAttributes;
			add_submenu_page( 'edit.php?post_type=wiki', __( 'Attributes' ), __( 'Attributes' ), 'administrator', 'rtwiki-attributes', array( $rtWikiAttributes, 'attributes_page' ) );
		}

		function init_attributes() {
			global $rtWikiAttributes;
			$rtWikiAttributes = new RtWikiAttributes();
		}
	}
}
