<?php

/*
 * 
 * Different Functions for showing sidebar widgets content
 * in wiki CPT single page.
 * 
 */

/**
 * Check if contributers exists or not 
 * 
 * @param type $postid
 * @return boolean
 */
function ifWikiContributers($postid) {
    $supported_posts = rtwiki_get_supported_attribute();
    if (!empty($supported_posts) && in_array(get_post_type($postid), $supported_posts)) {
        $revision = wp_get_post_revisions($postid);
        if (!empty($revision))
            return true;
        else
            return false;
    }
}

/**
 * Get post Contributers list via revisions 
 * 
 * @param type $postid
 */
function getContributers($postid) {
    $supported_posts = rtwiki_get_supported_attribute();
    if (!empty($supported_posts) && in_array(get_post_type($postid), $supported_posts)) {
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

/**
 * Get Wiki post SubPages
 * 
 * @param type $parentId
 * @param type $lvl
 * @param type $post_type
 */
function getSubPages($parentId, $lvl, $post_type = 'post') {
    $args = array('parent' => $parentId, 'post_type' => $post_type);
    $pages = get_pages($args);
    $supported_posts = rtwiki_get_supported_attribute();
    if ($pages) {
        $lvl++;
        echo '<ul>';
        foreach ($pages as $page) {
            echo '<li><a href=' . get_permalink($page->ID) . '>' . $page->post_title . "</a></li>";
            getSubPages($page->ID, $lvl, $post_type);
        }
        echo '</ul>';
    }
}

/**
 * Get wiki post taxonomies and its terms list
 * 
 * @global RtWikiAttributeTaxonomyModel $rtWikiAttributesModel
 * @param type $postid
 * @param type $display
 */
function wiki_custom_taxonomies($postid, $display = true) {

    $post = get_post($postid);
    //$post_type = $post->post_type;
    //$taxonomies = get_object_taxonomies($post_type);
    global $rtWikiAttributesModel;
    $rtWikiAttributesModel = new RtWikiAttributeTaxonomyModel();
    $attributes = $rtWikiAttributesModel->get_all_attributes(get_post_type());
    if ($display) {
        $out = "";
        foreach ($attributes as $attr) {
            if ($out != "") {
                $ulstyle = "style='display: none;'";
            } else {
                $ulstyle = "";
            }
            $taxonomy = $attr->attribute_name;
            $out .= "<div class='wikidropdown'><h3><a href='#' >" . $attr->attribute_name . "</a></h3>";

            if (is_single()) {
                $terms = wp_get_post_terms($postid, $taxonomy);
            } else {
                $terms = get_terms($taxonomy);
            }
            if (!empty($terms)) {
                $out.= "<ul " . $ulstyle . " >";
                foreach ($terms as $term)
                // var_dump($term);
                    $out .= '<li><a href="' . get_term_link($term, $taxonomy) . '" title="' . $term->name . '" >' . $term->name . '</a></li>';
                $out .="</ul>";
            } else {
                $out .= 'No ' . $taxonomy . ' assign.';
            }
            $out .= "</div>";
        }
        //$out .= "</ul>";
        echo $out;
    }
}

/**
 * Get wiki post taxonomies and its terms list [category & tags]
 */
/*function wiki_default_taxonomies($postid, $display = true) {
    if (is_single()) {
        $categories = get_the_category($postid);
    } else {
        $categories = get_categories();
    }
    if ($display) {
        $out = "";
        $taxonomy = 'category';
        $out .= "<div class='wikidropdown'><h3><a href='#' >" . $taxonomy . "</a></h3>";
        //echo $taxonomy;
        if (!empty($categories)) {
            $out.= "<ul style='display: none;'>";
            foreach ($categories as $category)
            // var_dump($term);
                $out .= '<li><a href="' . get_term_link($category, $taxonomy) . '" title="' . $category->name . '" >' . $category->name . '</a></li>';
            $out .="</ul>";
        } else {
            $out .= 'No Category assign.';
        }
        $out .= "</div>";
        echo $out;
    }
    if (is_single()) {
        $tags = wp_get_post_tags($postid);
    } else {
        $tags = get_tags();
    }
    $terms = get_tags();
    if ($display) {
        $out = "";
        $taxonomy = 'tags';
        $out .= "<div class='wikidropdown'><h3><a href='#' >" . $taxonomy . "</a></h3>";
        //echo $taxonomy;
        if (!empty($tags)) {
            $out.= "<ul style='display: none;'>";
            foreach ($tags as $tag)
            // var_dump($term);
                $out .= '<li><a href="' . get_term_link($tag, $taxonomy) . '" title="' . $tag->name . '" >' . $tag->name . '</a></li>';
            $out .="</ul>";
        } else {
            $out .= 'No Tag assign.';
        }
        $out .= "</div>";
        echo $out;
    }
}*/

/**
 * gets Top level Parent 
 */
/*function getTopParent() {
    global $post;

    if ($post->post_parent) {
        $ancestors = get_post_ancestors($post->ID);
        $root = count($ancestors) - 1;
        $parent = $ancestors[$root];
    } else {
        $parent = $post->ID;
    }
    echo $parent;
}*/

/**
 * Custom Shortcode to show post content on single page according to the permission
 */
/*function rtwiki_single_shortcode() {
    global $post;
    $supported_posts = rtwiki_get_supported_attribute();
    if (!empty($supported_posts) && in_array(get_query_var('post_type'), $supported_posts)) {
        echo single_post_filtering();
    }
}

add_shortcode("rtWikiSinglePost", "rtwiki_single_shortcode");*/

/*function subpages_non_wiki($post_type) {


    $subpageList = get_option('rtWiki_subpages_options');
    $list = $subpageList['subpages'];

    if (is_array($list) && in_array($post_type, $list)) {
        if ($list[$post_type] == 1)
            return true;
    }
}

add_shortcode("rtwikiSubPages", "subpages_non_wiki");*/
