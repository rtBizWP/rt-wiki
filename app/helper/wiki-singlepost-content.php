<?php
/**
 * rtWiki
 *
 * Helper functions for RtWiki sidebar widgets content
 *
 * @package    WikiSinglepostContent
 * @subpackage Helper
 *
 * @author     Dipesh
 */

/**
 * Check if contributers exists or not
 *
 * @param type $postid
 *
 * @return boolean
 */
function has_wiki_contributers( $postid )
{
	$supported_posts = rtwiki_get_supported_attribute();
	if ( ! empty( $supported_posts ) && in_array( get_post_type( $postid ), $supported_posts ) ){
		$revision = wp_get_post_revisions( $postid );
		if ( ! empty( $revision ) ) return true; else
			return false;
	}
}

/**
 * Get post Contributers list via revisions
 *
 * @param type $postid
 */
function get_contributers( $postid )
{
	$supported_posts = rtwiki_get_supported_attribute();
	if ( ! empty( $supported_posts ) && in_array( get_post_type( $postid ), $supported_posts ) ){
		$revision = wp_get_post_revisions( $postid );
		$authorId = array();
		echo '<ul id="contributers">';
		foreach ( $revision as $revisions ) {
			if ( ! in_array( $revisions->post_author, $authorId, true ) ){
				$id = $revisions->post_author;
				echo '<li><a href="' . get_author_posts_url( $id ) . '">' . get_userdata( $id )->display_name . '</a></li>';
				$authorId[ ] = $revisions->post_author;
			}
		}
		echo '</ul>';
	}
}

/**
 *
 *
 * @param type $parentId
 * @param type $lvl
 * @param type $post_type
 */


/**
 * Get Wiki post SubPages
 *
 * @param        $parentId
 * @param        $lvl
 * @param string $post_type
 */
function get_subpages( $parentId, $lvl, $post_type = 'post' )
{
	$args            = array( 'parent' => $parentId, 'post_type' => $post_type );
	$pages           = get_pages( $args );
	$supported_posts = rtwiki_get_supported_attribute();
	if ( $pages ){
		$lvl ++;
		echo '<ul>';
		foreach ( $pages as $page ) {
			if ( ! empty( $supported_posts ) && in_array( $post_type, $supported_posts ) ) {
				$permission = get_permission( $page->ID, get_current_user_id() );
			}else {
				$permission = true;
			}
			if ( $permission == true ) {
				echo '<li><a href=' . get_permalink( $page->ID ) . '>' . $page->post_title . '</a></li>';
			}else {
				echo '<li>' . $page->post_title . '</li>';
			}
			get_subpages( $page->ID, $lvl, $post_type );
		}
		echo '</ul>';
	}
}

/**
 *Get wiki post taxonomies and its terms list
 *
 * @param type                          $postid
 * @param bool|\type                    $display
 *
 * @global RtWikiAttributeTaxonomyModel $rtWikiAttributesModel
 *
 */
function wiki_custom_taxonomies( $postid, $display = true )
{

	$post = get_post( $postid );
	//$post_type = $post->post_type;
	//$taxonomies = get_object_taxonomies($post_type);
	global $rtWikiAttributesModel;
	$rtWikiAttributesModel = new RtWikiAttributeTaxonomyModel();
	$attributes            = $rtWikiAttributesModel->get_all_attributes( get_post_type() );
	if ( $display ){
		$out = '';
		foreach ( $attributes as $attr ) {
			if ( $out != '' ){
				$ulstyle = "style='display: none;'";
			} else {
				$ulstyle = '';
			}
			$taxonomy = $attr->attribute_name;
			$out     .= "<div class='wikidropdown'><h3><a href='#' >" . $attr->attribute_name . '</a></h3>';

			if ( is_single() ){
				$terms = wp_get_post_terms( $postid, $taxonomy );
			} else {
				$terms = get_terms( $taxonomy );
			}
			if ( ! empty( $terms ) ){
				$out .= '<ul ' . $ulstyle . ' >';
				foreach ( $terms as $term ) {
					$out .= '<li><a href="' . get_term_link( $term, $taxonomy ) . '" title="' . $term->name . '" >' . $term->name . '</a></li>';
				}
				$out .= '</ul>';
			} else {
				$out .= 'No ' . $taxonomy . ' assign.';
			}
			$out .= '</div>';
		}
		//$out .= "</ul>";
		echo $out;
	}
}