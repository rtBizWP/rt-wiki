<?php
/**
 * rtWiki
 *
 * Helper functions for RtWiki Post subscribe
 *
 * @package    WikiPostSubscribe
 * @subpackage Helper
 *
 * @author     Dipesh
 */

/**
 * Check if current user subcribe for globle post
 *
 * @global type $post
 * @global type $rtWikiSubscribe
 *
 * @param type  $userid
 *
 * @return boolean
 */
function is_post_subscribe_cur_user( $userid )
{
	global $post, $rtWikiSubscribe;
	if ( $rtWikiSubscribe->is_post_subscibe( $post->ID, $userid ) ){
		return true;
	} else if ( isset( $post->post_parent ) ){
		return is_subpost_subscribe( get_post( $post->post_parent ), $userid );
	}
}

/**
 * Check if any parent post of given post subcribe by user
 *
 * @global type $rtWikiSubscribe
 *
 * @param type  $post
 * @param type  $userid
 *
 * @return boolean
 */
function is_subpost_subscribe( $post, $userid )
{
	global $rtWikiSubscribe;
	if ( $rtWikiSubscribe->is_subpost_subscibe( $post->ID, $userid ) ){
		return true;
	}
	if ( isset( $post->post_parent ) && $post->post_parent != 0 ){
		return is_subpost_subscribe( get_post( $post->post_parent ), $userid );
	} else {
		return false;
	}
}

/**
 * Update subscriber list meta value for the particular post ID.
 *
 * @global type $pagenow
 */
function update_subscribe()
{
	global $pagenow;

	if ( isset( $_REQUEST[ 'PageSubscribe' ] ) == '1' ){
		$params      = array_keys( $_REQUEST ); //get the keys from request parameter
		$actionParam = $params[ 0 ];
		$postID      = $_REQUEST[ 'update-postId' ]; //get post id from the request parameter
		$url         = get_permalink( $postID ); //get permalink from post id
		$redirectURl = $url . '?' . $actionParam . '=1'; //form the url

		if ( ! is_user_logged_in() && $pagenow != 'wp-login.php' ){
			wp_redirect( wp_login_url( $redirectURl ), 302 ); //after login and if permission is set , user would be subscribed to the page
		} else {

			//Single post subscribe
			$singleStatus = '';
			if ( isset( $_POST[ 'single_subscribe' ] ) ) $singleStatus = $_POST[ 'single_subscribe' ];

			//Post type
			$post_type = 'post';
			if ( isset( $_POST[ 'post-type' ] ) ) $post_type = $_POST[ 'post-type' ];

			//subscribe or unsubscribe single post
			if ( isset( $_POST[ 'single_subscribe' ] ) ){
				if ( $_POST[ 'single_subscribe' ] == 'current' ){
					if ( isset( $_POST[ 'subPage_subscribe' ] ) && $_POST[ 'subPage_subscribe' ] == 'subpage' ) :
						subscribe_post_curuser( $postID, true );
					else :
						subscribe_post_curuser( $postID, false );
					endif;
				}
			} else if ( $_POST[ 'single_subscribe' ] == null ){
				if ( isset( $_POST[ 'subPage_subscribe' ] ) && $_POST[ 'subPage_subscribe' ] == 'subpage' ):
					unsubcribe_subpost_curuser( $postID, true );
				else :
					unsubscribe_post_curuser( $postID );
				endif;
			}
		}
	}
}

/**
 * Function for sunscribe given post for current user
 *
 * @global type $rtWikiSubscribe
 *
 * @param type  $postid
 * @param type  $is_sub_subscribe : flag if you want to subscribe sub page
 */
function subscribe_post_curuser( $postid, $is_sub_subscribe )
{
	global $rtWikiSubscribe;
	$userid = get_current_user_id();
	if ( ! $rtWikiSubscribe->is_post_subscibe( $postid, $userid ) ){
		$subscriber = array( 'attribute_postid' => $postid, 'attribute_userid' => $userid, 'attribute_sub_subscribe' => $is_sub_subscribe );
		$rtWikiSubscribe->add_subscriber( $subscriber );
	} else {
		unsubcribe_subpost_curuser( $postid, $is_sub_subscribe );
	}
}

/**
 * Function to unsubscribe/remove userid from subscriptionsub page list
 *
 * @global type $rtWikiSubscribe
 *
 * @param type  $postid
 * @param type  $is_sub_subscribe
 */
function unsubcribe_subpost_curuser( $postid, $is_sub_subscribe )
{
	global $rtWikiSubscribe;
	$userid = get_current_user_id();
	if ( $rtWikiSubscribe->is_post_subscibe( $postid, $userid ) ){
		$subscriber      = array( 'attribute_sub_subscribe' => $is_sub_subscribe, );
		$subscriberWhere = array( 'attribute_postid' => $postid, 'attribute_userid' => $userid, );

		$rtWikiSubscribe->update_subscriber( $subscriber, $subscriberWhere );
	}
}

/**
 * Function to unsubscribe/remove userid from subscription list
 *
 * @global type $rtWikiSubscribe
 *
 * @param type  $postid
 * @param type  $userid
 */
function unsubscribe_post_curuser( $postid )
{
	global $rtWikiSubscribe;

	$userid = get_current_user_id();

	if ( $rtWikiSubscribe->is_post_subscibe( $postid, $userid ) ){

		$subscriber = array( 'attribute_postid' => $postid, 'attribute_userid' => $userid );
		$rtWikiSubscribe->delete_subscriber( $subscriber );
	}
}

/**
 * Check if pages have any sub pages/child page [wiki-widgets]
 *
 * @param type $parentId
 * @param type $post_type
 *
 * @return boolean
 */
function if_sub_pages( $parentId, $post_type = 'post' )
{

	$args  = array( 'parent' => $parentId, 'post_type' => $post_type );
	$pages = get_pages( $args );

	if ( $pages ) return true; else
		return false;
}

/**
 * Check permission of sub pages/child page [wiki-widgets]
 *
 * @param type $parentId
 * @param type $subPage : flag for subpage
 * @param type $post_type
 *
 * @return boolean
 */
function rt_wiki_subpages_check( $parentId, $subPage, $post_type = 'post' )
{
	$args        = array( 'parent' => $parentId, 'post_type' => $post_type );
	$subPageFlag = $subPage;
	$pages       = get_pages( $args );
	if ( $pages ){
		foreach ( $pages as $page ) {
			$permission = get_permission( $page->ID, get_current_user_id(), 0 );
			if ( $permission == true ){
				return true;
			} else {
				$subPageFlag = false;
			}
			getSubPages( $page->ID, $subPageFlag );
		}
		if ( $subPageFlag == false ) return false;
	}
}

/**
 * Function Called after a Wiki post is Upated
 * Sends Email to Subscribers of Wiki Posts
 *
 * @global type $rtWikiSubscribe
 *
 * @param type  $post
 */
function send_mail_postupdate_wiki( $post )
{
	global $rtWikiSubscribe;
	$postObject      = get_post( $post );
	$supported_posts = rtwiki_get_supported_attribute();
	$diff            = '';
	if ( in_array( get_post_type( $post ), $supported_posts, true ) ){
		$subscribersList = $rtWikiSubscribe->get_Subscribers( $postObject->ID );
		$subscribersList = array_unique( array_merge( $subscribersList, $rtWikiSubscribe->get_parent_subpost_subscribers( get_all_parent_ids( get_post( $postObject->post_parent ), '' ) ) ) );
		if ( ! empty( $subscribersList ) || $subscribersList != null ){
			foreach ( $subscribersList as $subscriber ) {
				$user_info = get_userdata( $subscriber );
				if ( get_permission( $postObject->ID, $user_info->ID, 0 ) ){
					wiki_page_changes_send_mail( $postObject->ID, $user_info->user_email, $diff, get_permalink( $postObject->ID ) );
				}
			}
		}
	}
}



/**
 * get all parent id of perticular post
 *
 * @param type $post
 * @param type $postids
 *
 * @return string
 */
function get_all_parent_ids( $post, $postids )
{
	if ( ! isset( $postids ) ){
		$postids = '';
	}
	if ( is_object( $post ) ){
		$postids = $postids . $post->ID . ',';
		if ( isset( $post->post_parent ) && $post->post_parent != 0 ){
			return get_all_parent_ids( get_post( $post->post_parent ), $postids );
		}
	}

	return $postids;
}

/**
 * Send mail On post Update having body as diff of content
 *
 * @param type   $postID
 * @param type   $email    : Email id of user
 * @param type   $tax_diff : body of mail
 * @param string $url      : url of post
 */
function wiki_page_changes_send_mail( $postID, $email, $tax_diff = '', $url = '' )
{

	$revision = wp_get_post_revisions( $postID );
	$content  = array();
	$title    = array();

	foreach ( $revision as $revisions ) {
		$content[ ] = $revisions->post_content;
		$title[ ]   = $revisions->post_title;
	}

	if ( ! empty( $content ) ){
		$url  = 'Page Link:' . $url . '<br>';
		$body = rtwiki_text_diff( $title[ count( $title ) - 1 ], $title[ 0 ], $content[ count( $title ) - 1 ], $content[ 0 ] );

		$body     .= $tax_diff;
		$finalBody = $url . '<br>' . $body;
		add_filter( 'wp_mail_content_type', 'set_html_content_type' );

		$subject = 'Updates for "' . strtoupper( get_the_title( $postID ) ) . '"';
		// $subject .=':Time: ' . date("F j, Y, g:i a");
		$headers[ ] = 'From: rtcamp.com <no-reply@' . sanitize_title_with_dashes( get_bloginfo( 'name' ) ) . '.com>';
		//var_dump($email, $subject, $finalBody, $headers);
		//exit();
		wp_mail( $email, $subject, $finalBody, $headers );

		remove_filter( 'wp_mail_content_type', 'set_html_content_type' );
	}
}

/**
 * get html content type
 *
 * @return String
 */
function set_html_content_type()
{
	return 'text/html';
}
