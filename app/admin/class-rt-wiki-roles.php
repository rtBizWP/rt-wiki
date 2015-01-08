<?php
/**
 * Don't load this file directly!
 */
if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Description of RtWikiRoles
 *
 * @author Dipesh
 */
if ( ! class_exists( 'Rt_Wiki_Roles' ) ){

    /**
     * Class Rt_Wiki_Roles
     */
    class Rt_Wiki_Roles
	{

        /**
         *
         */
        public function __construct()
		{
            $this->remove_wiki_role();
		}

        /**
         * Romove all wiki role
         */
        function remove_wiki_role(){

            /* rtwiki old role */
            $users = get_users( array( 'role' => 'rtwikiadmin' ) );
            foreach ( $users as $user ) {
                $u_obj = new WP_User( $user );
                $u_obj->remove_role( 'rtwikiadmin' );
            }
            $users = get_users( array( 'role' => 'rtwikieditor' ) );
            foreach ( $users as $user ) {
                $u_obj = new WP_User( $user );
                $u_obj->remove_role( 'rtwikieditor' );
            }
            $users = get_users( array( 'role' => 'rtwikiauthor' ) );
            foreach ( $users as $user ) {
                $u_obj = new WP_User( $user );
                $u_obj->remove_role( 'rtwikiauthor' );
            }
            $users = get_users( array( 'role' => 'rtwikicontributor' ) );
            foreach ( $users as $user ) {
                $u_obj = new WP_User( $user );
                $u_obj->remove_role( 'rtwikicontributor' );
            }
            $users = get_users( array( 'role' => 'rtwikisubscriber' ) );
            foreach ( $users as $user ) {
                $u_obj = new WP_User( $user );
                $u_obj->remove_role( 'rtwikisubscriber' );
            }
            $users = get_users( array( 'role' => 'rtwikisubscriber' ) );
            foreach ( $users as $user ) {
                $u_obj = new WP_User( $user );
                $u_obj->remove_role( 'rtwikisubscriber' );
            }

            /* rtwiki new role */
            $users = get_users( array( 'role' => 'rtwikimoderator' ) );
            foreach ( $users as $user ) {
                $u_obj = new WP_User( $user );
                $u_obj->remove_role( 'rtwikimoderator' );
            }

            $users = get_users( array( 'role' => 'rtwikiwriter' ) );
            foreach ( $users as $user ) {
                $u_obj = new WP_User( $user );
                $u_obj->remove_role( 'rtwikiwriter' );
            }

            remove_role( 'rtwikiadmin' );
            remove_role( 'rtwikieditor' );
            remove_role( 'rtwikiauthor' );
            remove_role( 'rtwikicontributor' );
            remove_role( 'rtwikisubscriber' );
            remove_role( 'rtwikimoderator' );
            remove_role( 'rtwikiwriter' );
        }
	}
}

