<?php

/*
 * 
 * Different Functions for showing sidebar widgets content
 * in wiki CPT single page.
 * 
 */

/*
 * Check if contributers exists or not 
 */

function ifWikiContributers($postid) {
    $supported_posts = rtwiki_get_supported_attribute();
    if ( !empty( $supported_posts ) && in_array( get_query_var('post_type'), $supported_posts ) ) {
        $revision = wp_get_post_revisions($postid);
        if (!empty($revision))
            return true;
        else
            return false;
    }
}

/*
 * Get post Contributers list via revisions 
 */

function getContributers($postid) {
    $supported_posts = rtwiki_get_supported_attribute();
    if ( !empty( $supported_posts ) && in_array( get_query_var('post_type'), $supported_posts ) ) {

        $revision = wp_get_post_revisions($postid);
        $authorId = array();
        echo '<ul id="contributers">';
        foreach ($revision as $revisions) {
            if (!in_array($revisions->post_author, $authorId, true)) {
                $id = $revisions->post_author;
                echo '<li><a href="' . get_author_posts_url($id) . '">' . get_userdata($id)->display_name . '</a></li>';
                $authorId[] = $revisions->post_author;
            }
        }
        echo '</ul>';
    }
}

/*
 * Get Wiki post SubPages
 */

function getSubPages($parentId, $lvl, $post_type='post') {
    $args = array('parent' => $parentId, 'post_type' => $post_type);
    $pages = get_pages($args);
    $supported_posts = rtwiki_get_supported_attribute();
    
    if ($pages) {
        $lvl++;
        echo '<ul>';
        foreach ($pages as $page) {
            if ( !empty( $supported_posts ) && in_array( $post_type, $supported_posts ) ) {
                $permission = getPermission($page->ID);
            }else {
                $permission = true;
            }
                
            if ($permission == true) {
                echo '<li><a href=' . get_permalink( $page->ID ) . '>' . $page->post_title . "</a></li>";
            }

            getSubPages($page->ID, $lvl, $post_type);
        }
        echo '</ul>';
    }
}

/*
 * Get wiki post taxonomies and its terms list 
 */

function wiki_custom_taxonomies($postid, $display = true) {

    $post = get_post($postid);
    //$post_type = $post->post_type;
    //$taxonomies = get_object_taxonomies($post_type);
    global $rtWikiAttributesModel;
    $rtWikiAttributesModel = new RtWikiAttributeTaxonomyModel();
    $attributes = $rtWikiAttributesModel->get_all_attributes( get_post_type() );
    
    if( $display ) {
        $out = "";
        foreach ($attributes as $attr) {

            $taxonomy = $attr->attribute_name;
            $out .= "<div class='wikidropdown'><h3><a href='#' >".$attr->attribute_name."</a></h3>";
            //echo $taxonomy;
            $terms = get_terms( $taxonomy );
            if (!empty($terms)) {
                $out.= "<ul style='display: none;'>";
                foreach ($terms as $term)
                   // var_dump($term);
                    $out .= '<li><a href="' . get_term_link($term,$taxonomy) . '" title="' . $term->name . '" >' . $term->name . '</a></li>';
                $out .="</ul>";
            }
            $out .= "</div>";
        }
        //$out .= "</ul>";
        echo $out;
    } else {
        $out = array();
        foreach ($attributes as $attr) {
            $out[$attr->attribute_name] = array();
            $taxonomy = $attr->attribute_name;
            //echo $taxonomy;
            $terms = get_terms( $taxonomy );
            if (!empty($terms)) {
                foreach ($terms as $term) {
                    if( !array_key_exists( $attr->attribute_name, $out) )
                        array_push( $out, array( $attr->attribute_name => array() ) );
                    $term_link = get_term_link($term,$taxonomy);
                    $term = array( 'link' => $term_link, 'name' => $term->name );
                    $out[$attr->attribute_name][] = $term;
                }
            }
        }
        return $out;
    }
}

/*
 * gets Top level Parent 
 */

function getTopParent() {
    global $post;

    if ($post->post_parent) {
        $ancestors = get_post_ancestors($post->ID);
        $root = count($ancestors) - 1;
        $parent = $ancestors[$root];
    } else {
        $parent = $post->ID;
    }
    echo $parent;
}

/**
 * Custom Shortcode to show post content on single page according to the permission
 */
function rtwiki_single_shortcode() {
    global $post;
    $supported_posts = rtwiki_get_supported_attribute();
    if ( !empty( $supported_posts ) && in_array( get_query_var('post_type'), $supported_posts ) ) {
        echo single_post_filtering();
    }
}

add_shortcode("rtWikiSinglePost", "rtwiki_single_shortcode");


function subpages_non_wiki($post_type){
    
    
    $subpageList=get_option('rtWiki_subpages_options');
    $list=$subpageList['subpages'];
    
    if( is_array($list) && in_array($post_type,$list) )
    {
      if($list[$post_type] == 1 )
      return true;    
       
    }
 }
add_shortcode("rtwikiSubPages","subpages_non_wiki");