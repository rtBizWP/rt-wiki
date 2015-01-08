<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


/**
 * Description of class-rt-wiki-acl
 *
 * @author dipesh
 */
if ( ! class_exists( 'Rt_Wiki_ACL' ) ) {
	class Rt_Wiki_ACL {

        var $wiki_cap_name = 'wiki';

		public function __construct() {
			add_filter( 'rt_biz_modules', array( $this, 'register_rt_wiki_module' ) );
		}

		function register_rt_wiki_module( $modules ) {
			global $rt_wiki_roles;
			$module_key = ( function_exists( 'rt_biz_sanitize_module_key' ) ) ? rt_biz_sanitize_module_key( RT_WIKI_TEXT_DOMAIN ) : '';
			$modules[ $module_key ] = array(
				'label' => __( 'rtWiki' ),
				'post_types' => array( $this->wiki_cap_name ),
			);
			return $modules;
		}
	}
}