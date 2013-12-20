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
    if (get_query_var('post_type') == 'wiki') {
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
    if (get_query_var('post_type') == 'wiki') {

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

function getSubPages($parentId, $lvl,$post_type='wiki') {
    $args = array('parent' => $parentId, 'post_type' => $post_type);
    $pages = get_pages($args);

    if ($pages) {
        $lvl++;
        echo '<ul>';
        foreach ($pages as $page) {
            if($post_type == 'wiki'){ 
                $permission = getPermission($page->ID);
            }else {
                $permission = true;
            }
                
            if ($permission == true) {
                echo '<li><a href=' . $page->guid . '>' . $page->post_title . "</a></li>";
            }

            getSubPages($page->ID, $lvl);
        }
        echo '</ul>';
    }
}

/*
 * Get wiki post taxonomies and its terms list 
 */

function wiki_custom_taxonomies($postid) {

    $post = get_post($postid);
    //$post_type = $post->post_type;
    //$taxonomies = get_object_taxonomies($post_type);
    global $rtWikiAttributesModel;
    $rtWikiAttributesModel = new RtWikiAttributeTaxonomyModel();
    $attributes = $rtWikiAttributesModel->get_all_attributes();

    $attr_term = array();
    foreach ($attributes as $attr) {
        $attr_term[] = $attr->attribute_name;
    }
     $out = '';
    foreach ($attr_term as $attr) {

        $taxonomy = 'rt_' . $attr;
        $out.= "<ul>" . $attr;

        $terms = get_the_terms($post->ID, $taxonomy);
        if (!empty($terms)) {
            foreach ($terms as $term)
                $out .= '<li><a href="' . get_term_link($term->slug, $taxonomy) . '" title="' . $term->name . '" >' . $term->name . '</a></li>';
        }
        $out .="</ul>";
    }
    //$out .= "</ul>";
    echo $out;
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
    if ($post->post_type == 'wiki')
        echo single_post_filtering();
}

add_shortcode("rtWikiSinglePost", "rtwiki_single_shortcode");


function subpages_non_wiki($attr){
    
    $type=$attr['post_type'];
    $id=$attr['post_id'];
    $subpageList=get_option('rtWiki_subpages_options');
   
    if(in_array($type,$subpageList,true))
    {
      if($subpageList['subpages'][$type] == 1 )
      getSubPages ($id,1,$type);    
       
    }
 }
add_shortcode("rtwikiSubPages","subpages_non_wiki");


function subscribe_non_wiki($attr){
    $subscribe=get_option('rtWiki_subscribe_options');
    $id=$attr['postId'];
    $type=$attr['post_type'];
  if(in_array($type,$subscribe,true))
  {
     if($subscribe['subscribe'][$type] == 1)
     {}
  }
    
}
add_shortcode("rtwikiSubscribe","subscribe_non_wiki");