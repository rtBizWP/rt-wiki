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
            $this->register_pages();
		}

		/**
		 * add attributes page link in menu bar
		 */
		function register_pages()
		{
            global $rt_attributes, $rt_wiki_roles, $rt_attributes_model, $rt_attributes_relationship_model;

            $attributes = rtwiki_get_supported_attribute();

            $admin_cap = ( function_exists( 'rtbiz_get_access_role_cap' ) ) ? rtbiz_get_access_role_cap( RT_WIKI_TEXT_DOMAIN, 'admin' ) : '';
            $editor_cap = ( function_exists( 'rtbiz_get_access_role_cap' ) ) ? rtbiz_get_access_role_cap( RT_WIKI_TEXT_DOMAIN, 'editor' ) : '';

            $terms_caps = array(
                'manage_terms' => $editor_cap,
                'edit_terms'   => $editor_cap,
                'delete_terms' => $editor_cap,
                'assign_terms' => $editor_cap,
            );

            if ( is_array( $attributes ) && ! empty( $attributes ) && current_user_can('edit_wiki') ){
                foreach ( $attributes as $attribute ) {
                    if ( $attribute !== 'post' ){
                        $rt_attributes->add_attributes_page( $this->attributes_page_slug, 'edit.php?post_type='.$attribute, $attribute, $admin_cap, $terms_caps, $render_type = false, $storage_type = false, $orderby = true );
                    } else {
                        $rt_attributes->add_attributes_page( $this->attributes_page_slug, 'edit.php', $attribute, $admin_cap, $terms_caps, $render_type = false, $storage_type = false, $orderby = true );
                    }
                }
            }
		}

    }
}
