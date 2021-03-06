<?php
/**
 * Don't load this file directly!
 */
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Helper functions for RtWiki sidebar widgets content
 *
 * @author     Dipesh
 */

if ( !class_exists( 'Rt_Wiki_Widget_Helper' ) ) {

    /**
     * Class Rt_Wiki_Widget_Helper
     */
    class Rt_Wiki_Widget_Helper {

        /**
         * Object initialization
         */
        public function __construct() {
            $this->hook();
        }

        /**
         * Apply Filter Wiki's Filter
         */
        function hook(){

        }

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
                        echo '<li><a href="' . get_author_posts_url( $id ) . '">' . get_avatar( get_the_author_meta( $id ), apply_filters( 'rtwiki_contributers_avatar_size', 32 ) ) . '<span>&nbsp' . get_userdata( $id )->display_name . '</span></a></li>';
                        $authorId[ ] = $revisions->post_author;
                    }
                }
                echo '</ul>';
            }
        }

        /**
         * Get Wiki post SubPages
         *
         * @param        $parentId
         * @param        $lvl
         * @param string $post_type
         */
        function get_subpages( $parentId, $lvl, $post_type = 'post' )
        {
            global $rt_wiki_post_filtering;
            $args            = array( 'parent' => $parentId, 'post_type' => $post_type );
            $pages           = get_pages( $args );
            $supported_posts = rtwiki_get_supported_attribute();
            if ( $pages ){
                $lvl ++;
                echo '<ul>';
                foreach ( $pages as $page ) {
                    if ( ! empty( $supported_posts ) && in_array( $post_type, $supported_posts ) ) {
                        $permission = $rt_wiki_post_filtering->get_permission( $page->ID, get_current_user_id(), 0 );
                    }else {
                        $permission = true;
                    }
                    if ( $permission == true ) {
                        echo '<li><a href=' . get_permalink( $page->ID ) . '>' . $page->post_title . '</a></li>';
                    }else {
                        echo '<li>' . $page->post_title . '</li>';
                    }
                    $this->get_subpages( $page->ID, $lvl, $post_type );
                }
                echo '</ul>';
            }
        }

        /**
         *Get wiki post taxonomies and its terms list
         *
         * @param type                          $postid
         * @param bool|\type                    $display
         * @return string
         *
         * @global RtWikiAttributeTaxonomyModel $rtWikiAttributesModel
         *
         */
        function wiki_custom_taxonomies( $postid, $display = true )
        {
            $post = get_post( $postid );
            //$post_type = $post->post_type;
            //$taxonomies = get_object_taxonomies($post_type);
            global $rt_attributes_relationship_model,$rt_attributes_model,$rt_attributes;

            $attributes = $rt_attributes_relationship_model->get_relations_by_post_type( get_post_type() );
            $out = '';
            foreach ( $attributes as $attributes ) {
                $attr = $rt_attributes_model->get_attribute( $attributes->attr_id );
                if ( $out != '' ){
                    $ulstyle = "style='display: none;'";
                } else {
                    $ulstyle = '';
                }
                $taxonomy = $rt_attributes->get_taxonomy_name( $attr->attribute_name );
                $terms    = wp_get_post_terms( $postid, $taxonomy );
                /*if ( is_single() ){
                    $terms = wp_get_post_terms( $postid, $taxonomy );
                } else {
                    $terms = get_terms( $taxonomy );
                }*/
                if ( ! empty( $terms ) ){
                    $out .= "<div class='wikidropdown'><h3><a href='#' >" . $attr->attribute_name . '</a></h3>';
                    $out .= '<ul ' . $ulstyle . ' >';
                    foreach ( $terms as $term ) {
                        $out .= '<li><a href="' . get_term_link( $term, $taxonomy ) . '" title="' . $term->name . '" >' . $term->name . '</a></li>';
                    }
                    $out .= '</ul>';
                    $out .= '</div>';
                }
            }
            return $out;
        }

    }
}




