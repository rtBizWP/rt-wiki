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

/**
 * check permission before move post of CPT to trash
 *
 * @param type $post_id
 */
function my_wp_trash_post( $post_id )
{
	$post            = get_post( $post_id );
	$supported_posts = rtwiki_get_supported_attribute();
	if ( in_array( $post->post_type, $supported_posts ) && in_array( $post->post_status, array( 'publish', 'draft', 'future' ) ) ){
		if ( ! current_user_can( 'delete_wiki', $post_id ) ){
			WP_DIE( __( 'You dont have enough access rights to move this post to the trash' ) . "<br><a href='edit.php?post_type=$post->post_type'>" . __( 'Go Back' , 'rtCamp' ) . '</a>' );
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
		if ( ! current_user_can( 'edit_wiki', $post->ID ) ){
			unset( $actions[ 'edit' ] );
			unset( $actions[ 'inline hide-if-no-js' ] );
		}

		if ( ! current_user_can( 'delete_wiki', $post->ID ) ){
			unset( $actions[ 'trash' ] );
		}

		if ( ! current_user_can( 'read_wiki', $post->ID ) ){
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
	$supported_posts = rtwiki_get_supported_attribute();
	$posttype        = get_post_type( $page );
	if ( isset( $_GET[ 'action' ] ) && $_GET[ 'action' ] == 'edit' ){
		if ( in_array( $posttype, $supported_posts ) ){
			if ( ! current_user_can( 'edit_wiki', $page ) ){
				WP_DIE( __( 'You dont have enough access rights to Edit this post' ) . "<br><a href='edit.php?post_type=$posttype'>" . __( 'Go Back', 'rtCamp' ) . '</a>' );
			}
		}
	}
	if ( isset( $_GET[ 'action' ] ) && $_GET[ 'action' ] == 'trash' ){
		if ( in_array( $posttype, $supported_posts ) ){
			if ( ! current_user_can( 'delete_wiki', $page ) ){
				WP_DIE( __( 'You dont have enough access rights to move this post to the trash' ) . "<br><a href='edit.php?post_type=$posttype'>" . __( 'Go Back', 'rtCamp' ) . '</a>' );
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
	global $post, $current_user;
	$supported_posts = rtwiki_get_supported_attribute();
	if ( is_object( $post ) && in_array( $post->post_type, $supported_posts ) ){
		$access = 'na';
		if ( ( is_object( $user ) && ! empty( $user->roles ) && ( in_array( 'rtwikiadmin', $current_user->roles ) || in_array( 'rtwikieditor', $current_user->roles ) || $user->ID == $post->post_author ) ) ){
			return $user->allcaps;
		}
		$access = get_admin_panel_permission( $post->ID );
		if ( ( is_object( $post ) && $post->post_author != get_current_user_id() ) ){
			$capabilities[ 'edit_wiki' ] = false;
			$capabilities[ 'edit_others_wiki' ] = false;
			$capabilities[ 'publish_wiki' ] = false;
			$capabilities[ 'read_wiki' ] = false;
			$capabilities[ 'read_private_wiki' ]     = false;
			$capabilities[ 'delete_wiki' ] = false;
			$capabilities[ 'edit_published_wiki' ]   = false;
			$capabilities[ 'delete_published_wiki' ] = false;
			$capabilities[ 'delete_others_wiki' ]    = false;
			$capabilities[ 'edit_others_posts' ]     = false;
			$capabilities[ 'edit_published_posts' ]  = false;
		}
		if ( $access == 'r' ){
			$capabilities[ 'read_wiki' ] = true;
			$capabilities[ 'read_private_wiki' ] = true;
		}
		if ( 'w' == $access ){
			$capabilities[ 'edit_wiki' ] = true;
			$capabilities[ 'edit_others_wiki' ] = true;
			$capabilities[ 'publish_wiki' ] = true;
			$capabilities[ 'read_wiki' ] = true;
			$capabilities[ 'read_private_wiki' ]     = true;
			$capabilities[ 'delete_wiki' ] = true;
			$capabilities[ 'edit_published_wiki' ]   = true;
			$capabilities[ 'delete_published_wiki' ] = true;
			$capabilities[ 'delete_others_wiki' ]    = true;
			$capabilities[ 'edit_others_posts' ]     = true;
			$capabilities[ 'edit_published_posts' ]  = true;
		}
	}

	return $capabilities;
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

	$noflag   = 0;
	$noPublic = 0;
	global $current_user;
	$user          = get_current_user_id();
	$terms         = get_terms( 'user-group', array( 'hide_empty' => true ) );
	$access_rights = get_post_meta( $pageID, 'access_rights', true );

	if ( ! is_user_logged_in() ){
		if ( isset( $access_rights[ 'public' ] ) && ( 1 == $access_rights[ 'public' ] ) ){
			return 'r';
		} else if ( isset( $access_rights[ 'public' ] ) && ( 0 == $access_rights[ 'public' ] ) ){
			return false;
		}
	} else {

		$post_meta = get_post( $pageID );

		if ( in_array( 'rtwikiadmin', $current_user->roles ) || in_array( 'rtwikieditor', $current_user->roles ) || $user == $post_meta->post_author ){
			return 'w';
		} else {
			if ( isset( $access_rights[ 'public' ] ) && 1 == $access_rights[ 'public' ] ){
				return 'r';
			} else if ( isset( $access_rights[ 'all' ] ) ){
				if ( isset( $access_rights[ 'all' ][ 'w' ] ) && ( $access_rights[ 'all' ][ 'w' ] == 1 ) ){
					return 'w';
				} else if ( isset( $access_rights[ 'all' ][ 'r' ] ) && ( $access_rights[ 'all' ][ 'r' ] == 1 ) ){
					return 'r';
				} else if ( isset( $access_rights[ 'all' ][ 'na' ] ) && ( $access_rights[ 'all' ][ 'na' ] == 1 ) ){
					$noflag = 1;
				}
			} else {
				foreach ( $terms as $term ) {
					$ans = get_term_if_exists( $term->slug, $user );
					if ( $ans == $term->slug && isset( $access_rights[ $term->name ] ) ){
						if ( isset( $access_rights[ $term->name ][ 'w' ] ) && ( $access_rights[ $term->name ][ 'w' ] == 1 ) ){
							return 'w';
						} else if ( isset( $access_rights[ $term->name ][ 'r' ] ) && ( $access_rights[ $term->name ][ 'r' ] == 1 ) ){
							return 'r';
						} else if ( isset( $access_rights[ $term->name ][ 'na' ] ) && ( $access_rights[ $term->name ][ 'na' ] == 1 ) ){
							$noflag = 1;
						}
					} else if ( $ans == '' || $ans == null ){
						$noPublic = 1;
					}
				}
			}
			if ( $noflag == 1 || $noPublic == 1 ){
				return false;
			}
		}
	}
}

/**
 * Checks the permission of the user for the post(Frontend)
 *
 * @global type $current_user
 *
 * @param type  $pageID
 * @param type  $user
 *
 * @return boolean
 */
function get_permission( $pageID, $user )
{
	global $current_user;
	$noflag        = 0;
	$noPublic      = 0;
	$terms         = get_terms( 'user-group', array( 'hide_empty' => true ) );
	$access_rights = get_post_meta( $pageID, 'access_rights', true );
	if ( isset( $access_rights[ 'public' ] ) && ( 1 == $access_rights[ 'public' ] ) ){
		return true;
	} else if ( is_user_logged_in() ){
		$post_details = get_post( $pageID );

		// if rtwikiAdmin or rtwikieditor or postauthor
		if ( in_array( 'rtwikiadmin', $current_user->roles ) || in_array( 'rtwikieditor', $current_user->roles ) || $user == $post_details->post_author ){
			return true;
		}else if ( isset( $access_rights[ 'all' ] ) ) {
			if ( ( isset( $access_rights[ 'all' ][ 'r' ] ) && ( $access_rights[ 'all' ][ 'r' ] == 1 ) ) || ( isset( $access_rights[ 'all' ][ 'w' ] ) && ( $access_rights[ 'all' ][ 'w' ] == 1 ) ) ){
				return true;
			} else if ( isset( $access_rights[ 'all' ][ 'na' ] ) && ( $access_rights[ 'all' ][ 'na' ] == 1 ) ){
				$noflag = 1;
			}
		}else {
			foreach ( $terms as $term ) {
				$ans = get_term_if_exists( $term->slug, $user );
				if ( $ans == $term->slug && isset( $access_rights[ $term->name ] ) ){
					if ( ( isset( $access_rights[ $term->name ][ 'r' ] ) && ( $access_rights[ $term->name ][ 'r' ] == 1 ) ) || ( isset( $access_rights[ $term->name ][ 'w' ] ) && ( $access_rights[ $term->name ][ 'w' ] == 1 ) ) ){
						return true;
					} else if ( isset( $access_rights[ $term->name ][ 'na' ] ) && ( $access_rights[ $term->name ][ 'na' ] == 1 ) ){
						$noflag = 1;
					}
				} else if ( $ans == '' || $ans == null ){
					$noPublic = 1;
				}
			}
			if ( $noflag == 1 ){
				return false;
			}
			if ( $noPublic == 1 ){
				return false;
			}
		}
	} else if ( isset( $access_rights[ 'public' ] ) && ! is_user_logged_in() && ( 0 == $access_rights[ 'public' ] ) ){
		return false;
	}
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
					if ( get_permission( get_the_ID(), get_current_user_id() ) ){
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
					if ( get_permission( $comment->comment_post_ID, get_current_user_id() ) ){
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
				if ( get_permission( $post->ID, get_current_user_id() ) ){
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
		if ( get_permission( get_the_ID(), get_current_user_id() ) ){
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
		if ( get_permission( get_the_ID(), get_current_user_id() ) ){
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
		if ( $rtwiki_settings[ 'wiki_comment' ] == 'y' && get_permission( $post_id, get_current_user_id() ) ){
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
		if ( $rtwiki_settings[ 'wiki_comment' ] == 'y' && get_permission( $post_id, get_current_user_id() ) ){
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

