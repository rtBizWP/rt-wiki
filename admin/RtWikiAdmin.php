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
            private $rewrite_rules;
		public function __construct() {

			global $rtWikiAttributesModel;
			$rtWikiAttributesModel = new RtWikiAttributeTaxonomyModel();

			$this->init_attributes();
                        $this->rewrite_rules = array();
                        add_filter( 'rewrite_rules_array', array( $this, 'rtwiki_rewrite_rules' ) );
			add_action( 'admin_menu', array( $this, 'register_pages' ) );
			add_action( 'wp_loaded', array( $this, 'rtwiki_flush_rules' ) );

			$this->register_taxonomies();
		}

                function rtwiki_rewrite_rules($rules) {
                    $rules = array_merge($rules, $this->rewrite_rules);
//                    var_dump($rules);
//                    die;
                    return $rules;
                }
                
                function rtwiki_flush_rules() {
                    $rules = get_option('rewrite_rules');
                    if (!empty($rules)) {
                        global $wp_rewrite;
                        $wp_rewrite->flush_rules();
                    }
                }
                
		function register_taxonomies() {
			global $rtWikiAttributesModel, $rtWikiAttributes;
                        $tax_attributes = rtwiki_get_supported_attribute();
                        if( is_array( $tax_attributes ) && !empty( $tax_attributes ) ) {
                            foreach ( $tax_attributes as $value ) {
                                $attributes = $rtWikiAttributesModel->get_all_attributes( $value );
                                if( is_array($attributes) ) {
                                    foreach ($attributes as $attr) {
                                        if( is_object( $attr ) ) {
                                            $rtWikiAttributes->register_taxonomy( $value, $attr->id );
                                            $this->rewrite_rules[$value . '/' . $attr->attribute_name . '/([^/]+)/?$'] = 'index.php?&' . $attr->attribute_name . '=$matches[1]';
                                            $this->rewrite_rules[$value . '/' . $attr->attribute_name . '/([^/]+)/page/([0-9]+)?$'] = 'index.php?&' . $attr->attribute_name . '=$matches[1]&page=$matches[2]';
                                        } else {
                                            $rtWikiAttributes->register_taxonomy( $value, 0 );
                                        }
                                    }
                                }
                            }
                        }
                        flush_rewrite_rules(true);
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
