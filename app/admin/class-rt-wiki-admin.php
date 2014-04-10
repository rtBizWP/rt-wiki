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
            $rt_attributes = new RT_Attributes( RT_wiki_TEXT_DOMAIN );
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
                        $rt_attributes->add_attributes_page( $this->attributes_page_slug, 'edit.php?post_type='.$attribute, $attribute, $rt_wiki_roles->global_caps['manage_attributes'], $terms_caps, $render_type = false, $storage_type = false, $orderby = true );
                    } else {
                        $rt_attributes->add_attributes_page( $this->attributes_page_slug, 'edit.php', $attribute, $rt_wiki_roles->global_caps['manage_attributes'], $terms_caps, $render_type = false, $storage_type = false, $orderby = true );
                    }
                }
            }
            $rt_attributes_model = new RT_Attributes_Model();
            $rt_attributes_relationship_model = new RT_Attributes_Relationship_Model();
		}

    }
}
