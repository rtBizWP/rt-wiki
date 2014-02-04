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
                        $rtwiki_settings = '';
                        if ( is_multisite() ) {
                            $rtwiki_settings = get_site_option( 'rtwiki_settings', array() );
                        }
                        else {
                            $rtwiki_settings = get_option( 'rtwiki_settings', array() );
                        }
                        if ( isset ( $rtwiki_settings['attribute'] ) ) { 
                            $tax_attributes = $rtwiki_settings['attribute'];
                            foreach ($tax_attributes as $value) {
                                $attributes = $rtWikiAttributesModel->get_all_attributes( $value );
                                foreach ($attributes as $attr) {
                                    $rtWikiAttributes->register_taxonomy( $value, $attr->id );
                                }
                            }
                        }
		}

		function register_pages() {
			global $rtWikiAttributes;
                        $rtwiki_settings = '';
                        if ( is_multisite() ) {
                            $rtwiki_settings = get_site_option( 'rtwiki_settings', true );
                        }
                        else {
                            $rtwiki_settings = get_option( 'rtwiki_settings', true );
                        }
                        $attributes = $rtwiki_settings['attribute'];
                        foreach( $attributes as $attribute )
                            add_submenu_page( 'edit.php?post_type='.$attribute, __( 'Attributes' ), __( 'Attributes' ), 'administrator', 'rtwiki-attributes', array( $rtWikiAttributes, 'attributes_page' ) );
		}

		function init_attributes() {
			global $rtWikiAttributes;
			$rtWikiAttributes = new RtWikiAttributes();
		}
	}
}
