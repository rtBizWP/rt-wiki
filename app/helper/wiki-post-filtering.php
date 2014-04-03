<?php

/**
 * rtWiki
 *
 * Helper functions for rtWiki Post Filtering
 *Checks for the permission of each post at admin side and frontend side.
 *
 * @package    WikiPostFiltering
 * @subpackage Helper
 *
 * @author     Dipesh
 */

function remove_bulk_actions( $actions ){
	global $current_user;
	if ( in_array( 'rtwikiwriter', $current_user->roles ) ){
		return null;
	}
	return $actions;
}

/**
 * check permission before move post of CPT to trash
 *
 * @param type $post_id
 */
function my_wp_trash_post( $post_id )
{
	$post            = get_post( $post_id );
	$supported_posts = rtwiki_get_supported_attribute();
	$access = get_admin_panel_permission( $post_id );
	if ( in_array( $post->post_type, $supported_posts ) &&  in_array( $post->post_status, array( 'publish', 'draft', 'future' ) ) ){
		if ( $access != 'a' ){
			WP_DIE( __( "You don't have enough access rights to move " . $post->post_title . " post to the trash" ) . "<br><a href='edit.php?post_type=$post->post_type'>" . __( 'Go Back' , 'rtCamp' ) . '</a>' );
		}elseif ( if_sub_pages( $post->ID, $post->post_type ) == true ){
			WP_DIE( __( "You can't move " . $post->post_title . " post to trash. It has a child Posts." ) . "<br><a href='edit.php?post_type=$post->post_type'>" . __( 'Go Back' , 'rtCamp' ) . '</a>' );
		}
	}
}

function my_delete_post ( $post_id ){
	$post            = get_post( $post_id );
	$supported_posts = rtwiki_get_supported_attribute();
	$access = get_admin_panel_permission( $post_id );
	if( in_array( $post->post_type, $supported_posts ) ){
		if ( $access != 'a' ){
			WP_DIE( __( "You don't have enough access rights to delete " . $post->post_title . " post." ) . "<br><a href='edit.php?post_type=$post->post_type'>" . __( 'Go Back' , 'rtCamp' ) . '</a>' );
		}elseif ( if_sub_pages( $post->ID, $post->post_type ) == true ){
			WP_DIE( __( "You can't delete " . $post->post_title . " post. It has a child Posts." ) . "<br><a href='edit.php?post_type=$post->post_type'>" . __( 'Go Back' , 'rtCamp' ) . '</a>' );
		}
	}
}

/**
 * removes quick edit from wiki post type
 *
 * @global type $current_user
 * @global type $post
 *
 * @param type  $actions
 *
 * @return type
 */
function remove_quick_edit( $actions )
{
	global $post;
	$supported_posts = rtwiki_get_supported_attribute();
	if ( in_array( $post->post_type, $supported_posts ) ){
		$access = get_admin_panel_permission( $post->ID );
		if ( $access != 'w' && $access != 'a' ){
			unset( $actions[ 'edit' ] );
			unset( $actions[ 'inline hide-if-no-js' ] );
		}

		if ( $access != 'a' ){
			unset( $actions[ 'trash' ] );
		}

		if ( $access == false ){
			unset( $actions[ 'view' ] );
		}
	}

	return $actions;
}

/**
 * Check permissions at Admin side for edit post & delete post
 */
function post_check()
{
	global $pagenow, $current_user;
	if ( ! is_admin() ) return;

	$page            = isset( $_GET[ 'post' ] ) ? $_GET[ 'post' ] : 0;
	$post            = get_post( $page );
	$supported_posts = rtwiki_get_supported_attribute();
	$posttype        = $post->post_type;
	$access = get_admin_panel_permission( $page );
	if ( isset( $_GET[ 'action' ] ) && $_GET[ 'action' ] == 'edit' ){
		if ( in_array( $posttype, $supported_posts ) ){
			if ( $access != 'w' && $access != 'a' ){
				WP_DIE( __( "You don't have enough access rights to Edit " . $post->post_title . "  post" ) . "<br><a href='edit.php?post_type=$posttype'>" . __( 'Go Back', 'rtCamp' ) . '</a>' );
			}
		}
	}
	if ( isset( $_GET[ 'action' ] ) && $_GET[ 'action' ] == 'trash' ){
		if ( in_array( $posttype, $supported_posts ) ){
			if ( $access != 'a' ){
				WP_DIE( __( "You don't have enough access rights to move " . $post->post_title . "  post to the trash" ) . "<br><a href='edit.php?post_type=$posttype'>" . __( 'Go Back', 'rtCamp' ) . '</a>' );
			}
		}
	}
}

/**
 * add capabilities for perticular user
 *
 * @global type $post
 *
 * @param type  $capabilities
 * @param type  $cap
 * @param type  $args
 * @param type  $user
 *
 * @return type
 */
function add_capabilities( $capabilities, $cap, $args, $user )
{
	/*global $post, $current_user;
	$supported_posts = rtwiki_get_supported_attribute();
	if ( is_object( $post ) && in_array( $post->post_type, $supported_posts ) ){
		$access = 'na';

		if ( ( is_object( $user ) && ! empty( $user->roles ) && ( in_array( 'rtwikiadmin', $current_user->roles ) || in_array( 'rtwikieditor', $current_user->roles ) || $user->ID == $post->post_author ) ) ){
			return $user->allcaps;
		}
		$access = get_admin_panel_permission( $post->ID );
		if ( ( is_object( $post ) && $post->post_author == get_current_user_id() ) ){
			$capabilities["read_wiki"] = true;
			$capabilities["delete_wiki"] = true;
			$capabilities["edit_wikis"] = true;
			$capabilities["edit_others_wikis"] = true;
			$capabilities["publish_wikis"] = true;
			$capabilities["read_private_wikis"] = true;
			$capabilities["delete_wikis"] = true;
			$capabilities["delete_private_wikis"] = true;
			$capabilities["delete_published_wikis"] = true;
			$capabilities["delete_others_wikis"] = true;
			$capabilities["edit_private_wikis"] = true;
			$capabilities["edit_published_wikis"] = true;
		}elseif( $access == 'r' ){
			$capabilities["read_wiki"] = true;
			$capabilities["delete_wiki"] = false;
			$capabilities["edit_wikis"] = false;
			$capabilities["edit_others_wikis"] = false;
			$capabilities["publish_wikis"] = false;
			$capabilities["read_private_wikis"] = false;
			$capabilities["delete_wikis"] = false;
			$capabilities["delete_private_wikis"] = false;
			$capabilities["delete_published_wikis"] = false;
			$capabilities["delete_others_wikis"] = false;
			$capabilities["edit_private_wikis"] = false;
			$capabilities["edit_published_wikis"] = false;
		}elseif ( 'w' == $access ){
			$capabilities["read_wiki"] = true;
			$capabilities["delete_wiki"] = true;
			$capabilities["edit_wikis"] = true;
			$capabilities["edit_others_wikis"] = true;
			$capabilities["publish_wikis"] = true;
			$capabilities["read_private_wikis"] = true;
			$capabilities["delete_wikis"] = false;
			$capabilities["delete_private_wikis"] = false;
			$capabilities["delete_published_wikis"] = false;
			$capabilities["delete_others_wikis"] = false;
			$capabilities["edit_private_wikis"] = true;
			$capabilities["edit_published_wikis"] = true;
		}
	}

	return $capabilities;*/
}


/**
 * Checks the permission of the Logged in User for editing post
 *
 * @global type $current_user
 *
 * @param type  $pageID
 *
 * @return string|boolean
 */
function get_admin_panel_permission( $pageID )
{
	global $current_user;
	$base_parent = get_post_meta( $pageID, 'base_parent', true );
	$access_rights = get_post_meta( $base_parent , 'access_rights', true );

	if ( isset( $access_rights ) ){

		if ( ! is_user_logged_in() ){

			if ( isset( $access_rights[ 'public' ] ) && 1 == $access_rights[ 'public' ] ){
				return 'r';
			}
		} else {

			if ( in_array( 'rtwikimoderator', $current_user->roles )  ){
				return 'a';
			} else{

				if ( isset( $access_rights[ 'public' ] ) && 1 == $access_rights[ 'public' ] ){
					return 'r';
				}elseif ( isset( $access_rights[ 'all' ] ) ){
					if ( $access_rights[ 'all' ]== 2 ){
						return 'w';
					}elseif ( $access_rights[ 'all' ]== 1 ){
						return 'r';
					}
				}else{
					$terms = get_terms( 'user-group', array( 'hide_empty' => true ) );
					$user_id  = get_current_user_id();
					$noflag   = 0;

					foreach ( $terms as $term ) {
						$ans = get_term_if_exists( $term->slug, $user_id );
						if ( $ans == $term->slug && isset( $access_rights[ $term->slug ] ) ){
							if ( $noflag < $access_rights[ $term->slug ] ){
								$noflag = $access_rights[ $term->slug ];
							}
						}
					}

					if( $noflag == 2 ){
						return 'w';
					}elseif ( $noflag == 1 ){
						return 'r';
					}
				}
			}
		}
	}
	return false;
}

/**
 * Checks the permission of the user for the post(Frontend)
 *
 * @param type  $pageID
 * @param       $userid
 * @param int   $flag : function call by scheduler (1) ot not (0)
 *
 * @global type $current_user
 *
 * @internal param \type $user
 *
 * @return boolean
 */
function get_permission( $pageID, $userid, $flag = 0 )
{
	global $current_user;


	$base_parent = get_post_meta( $pageID, 'base_parent', true );
	$access_rights = get_post_meta( $base_parent , 'access_rights', true );

	if ( isset( $access_rights ) ){

		if ( ! is_user_logged_in() ){

			if ( isset( $access_rights[ 'public' ] ) && 1 == $access_rights[ 'public' ] ){
				return true;
			}
		} else {

			if ( in_array( 'rtwikimoderator', $current_user->roles )  ){
				return true;
			} else{

				if ( isset( $access_rights[ 'public' ] ) && 1 == $access_rights[ 'public' ] ){
					return true;
				}elseif ( isset( $access_rights[ 'all' ] ) ){
					if ( $access_rights[ 'all' ]== 2 || $access_rights[ 'all' ]== 1 ){
						return true;
					}
				}else{
					$terms = get_terms( 'user-group', array( 'hide_empty' => true ) );
					$user_id  = get_current_user_id();
					$noflag   = 0;

					foreach ( $terms as $term ) {
						$ans = get_term_if_exists( $term->slug, $user_id );
						if ( $ans == $term->slug && isset( $access_rights[ $term->slug ] ) ){
							if ( $noflag < $access_rights[ $term->slug ] ){
								$noflag=$access_rights[ $term->slug ];
								if ( $noflag>0 )
									break;
							}
						}
					}
					if( $noflag > 0 ){
						return true;
					}
				}
			}
		}
	}

	return false;
}

/**
 * Finally, we do the post type & permission on CPT check, and generate feeds conditionally
 *
 * @global type $wp_query
 * @global type $post
 */
function my_do_feed()
{
	global $wp_query, $post;
	$no_feed = array( 'wiki' );
	if ( in_array( $wp_query->query_vars[ 'post_type' ], $no_feed ) ){
		wp_die( __( 'This is not a valid feed address.', 'textdomain' ) );
	} else {
		$supported_posts = rtwiki_get_supported_attribute();
		$newpostArray    = array();
		if ( isset( $wp_query->posts ) ){
			foreach ( $wp_query->posts as $post ) {
				if ( in_array( get_post_type(), $supported_posts ) ){
					if ( get_permission( get_the_ID(), get_current_user_id(), 0 ) ){
						$newpostArray[ ] = $post;
					}
				} else {
					$newpostArray[ ] = $post;
				}
			}
			$wp_query->posts       = $newpostArray;
			$wp_query->post_count  = count( $newpostArray );
			$wp_query->found_posts = count( $newpostArray );
		}
		if ( isset( $wp_query->comments ) ){
			$newCommentArray = array();
			foreach ( $wp_query->comments as $comment ) {
				if ( in_array( get_post_type( $comment->comment_post_ID ), $supported_posts ) ){
					if ( get_permission( $comment->comment_post_ID, get_current_user_id(), 0 ) ){
						$newCommentArray[ ] = $comment;
					}
				} else {
					$newpostArray[ ] = $post;
				}
			}
			$wp_query->comments      = $newCommentArray;
			$wp_query->comment_count = count( $newpostArray );
		}
		do_feed_rss2( $wp_query->is_comment_feed );
	}
}

/**
 * check permission for CPT and generate result[get_posts]
 *
 * @param type $wp_query
 *
 * @return type
 */
function rtwiki_search_filter( $Posts )
{
	if ( isset( $Posts ) && ( is_search() || is_archive() ) && ! is_admin() ){
		$newpostArray    = array();
		$supported_posts = rtwiki_get_supported_attribute();
		foreach ( $Posts as $post ) {
			if ( in_array( $post->post_type, $supported_posts ) ){
				if ( get_permission( $post->ID, get_current_user_id(), 0 ) ){
					$newpostArray[ ] = $post;
				}
			} else {
				$newpostArray[ ] = $post;
			}
		}
		$Posts = $newpostArray;
	}

	return $Posts;
}

/**
 * check permission for CPT content.
 *
 * @param type $content
 *
 * @return type
 */
function rtwiki_content_filter( $content )
{
	$supported_posts = rtwiki_get_supported_attribute();
	if ( in_array( get_post_type(), $supported_posts ) ){
		if ( get_permission( get_the_ID(), get_current_user_id(), 0 ) ){
			$post_thumbnail = get_the_post_thumbnail();

			return $post_thumbnail . $content;
		} else {
			return '<p>' . __( 'Not Enough Rights to View The Content.', 'rtCamp' ) . '</p>';
		}
	}

	return $content;
}

/**
 * check permission for CPT edit content link.
 *
 * @param type $output
 *
 * @return string
 */
function rtwiki_edit_post_link_filter( $output )
{
	$supported_posts = rtwiki_get_supported_attribute();
	if ( in_array( get_post_type(), $supported_posts ) ){
		if ( get_permission( get_the_ID(), get_current_user_id(), 0 ) ){
			return $output;
		} else {
			return '';
		}
	}

	return $output;
}

/**
 * check permission before cpt comment shows.
 *
 * @param type $comments
 * @param type $post_id
 *
 * @return type
 */
function rtwiki_comment_filter( $comments, $post_id )
{
	//@TODO is wiki post type
	$supported_posts = rtwiki_get_supported_attribute();
	$post            = get_post( $post_id );
	if ( in_array( $post->post_type, $supported_posts ) ){
		$rtwiki_settings = get_option( 'rtwiki_settings', array() );
		if ( isset( $rtwiki_settings[ 'wiki_comment' ] ) && $rtwiki_settings[ 'wiki_comment' ] == 'y' && get_permission( $post_id, get_current_user_id(), 0 ) ){
			return $comments;
		}
		return array();
	}

	return $comments;
}

/**
 * check permission before cpt comment form display.
 *
 * @param type $open
 * @param type $post_id
 *
 * @return boolean
 */
function rtwiki_comment_form_filter( $open, $post_id )
{
	//@TODO is wiki post type
	$supported_posts = rtwiki_get_supported_attribute();
	$post            = get_post( $post_id );
	if ( in_array( $post->post_type, $supported_posts ) ){
		$rtwiki_settings = get_option( 'rtwiki_settings', array() );
		if ( isset( $rtwiki_settings[ 'wiki_comment' ] ) && $rtwiki_settings[ 'wiki_comment' ] == 'y' && get_permission( $post_id, get_current_user_id(), 0 ) ){
			return $open;
		}

		return false;
	}

	return $open;
}

/**
 * sitemap taxonomies filter [Yoast plugin]
 *
 * @global type $rtWikiAttributesModel
 *
 * @param type  $taxonomies
 *
 * @return type
 */
function rtwiki_sitemap_taxonomies( $taxonomies )
{
	global $rtWikiAttributesModel;
	$attributes       = $rtWikiAttributesModel->get_all_attributes();
	$customAttributes = array();
	foreach ( $attributes as $attribute ) {
		$customAttributes[ ] = $attribute->attribute_name;
	}
	foreach ( $taxonomies as $key => $val ) {
		if ( in_array( $key, $customAttributes ) ){
			unset( $taxonomies[ $key ] );
		}
	}

	return $taxonomies;
}


/**
 * sitemap posttype filter [Yoast plugin]
 *
 * @global type $rtWikiAttributesModel
 *
 * @param type  $posttypes
 *
 * @return type
 */
function rtwiki_sitemap_posttypes( $posttypes )
{
	global $rtWikiAttributesModel;
	$attributes = rtwiki_get_supported_attribute();
	foreach ( $posttypes as $key => $val ) {
		if ( in_array( $key, $attributes ) ){
			unset( $posttypes[ $key ] );
		}
	}

	return $posttypes;
}

function rtwiki_get_pages($pages, $r){
	$supported_posts = rtwiki_get_supported_attribute();
	if ( in_array( get_post_type(), $supported_posts ) ) {
		foreach( $pages as $key=>$argpage){
			if (is_admin()){
				if ( get_admin_panel_permission( $argpage->ID ) != 'a' && get_admin_panel_permission( $argpage->ID ) != 'w' ){
					unset($pages[$key]);
				}
			}else{
				if ( get_permission( $argpage->ID, get_current_user_id(), 0 ) == false ){
					unset($pages[$key]);
				}
			}
		}
	}
	return $pages;
}