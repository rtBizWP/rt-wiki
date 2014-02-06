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
                        $tax_attributes = rtwiki_get_supported_attribute();
                        if( is_array( $tax_attributes ) && !empty( $tax_attributes ) ) {
                            foreach ( $tax_attributes as $value ) {
                                $attributes = $rtWikiAttributesModel->get_all_attributes( $value );
                                if( is_array($attributes) ) {
                                    foreach ($attributes as $attr) {
                                        $rtWikiAttributes->register_taxonomy( $value, $attr->id );
                                    }
                                }
                            }
                        }
		}

		function register_pages() {
			global $rtWikiAttributes;
                        $attributes = rtwiki_get_supported_attribute();
                        if( is_array( $attributes ) && !empty( $attributes ) ) {
                            foreach( $attributes as $attribute ) {
                                if ( $attribute !== 'post' ) {
                                    add_submenu_page( 'edit.php?post_type='.$attribute, __( 'Attributes' ), __( 'Attributes' ), 'administrator', $attribute.'-attributes', array( $rtWikiAttributes, 'attributes_page' ) );
                                } else {
                                    add_submenu_page( 'edit.php', __( 'Attributes' ), __( 'Attributes' ), 'administrator', $attribute.'-attributes', array( $rtWikiAttributes, 'attributes_page' ) );
                                }
                            }
                        }
		}

		function init_attributes() {
			global $rtWikiAttributes;
			$rtWikiAttributes = new RtWikiAttributes();
		}
	}
}
