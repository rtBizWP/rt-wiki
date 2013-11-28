<?php

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
 * Get SubPages
 */

function getSubPages($parentId, $lvl) {
    $args = array('parent' => $parentId, 'post_type' => 'wiki');
    $pages = get_pages($args);

    if ($pages) {
        $lvl++;
        echo '<ul>';
        foreach ($pages as $page) {

            $permission = getPermission($page->ID);
            if ($permission == true) {
                echo '<li><a href=' . $page->guid . '>' . $page->post_title . "</a></li>";
            }

            getSubPages($page->ID, $lvl);
        }
        echo '</ul>';
    }
}

function wiki_custom_taxonomies($postid) {

    $post = &get_post($postid);
    $post_type = $post->post_type;
    $taxonomies = get_object_taxonomies($post_type);
    $out = "<ul>";
    foreach ($taxonomies as $taxonomy) {
        $out .= "<ul>" . $taxonomy;

        $terms = get_the_terms($post->ID, $taxonomy);
        if (!empty($terms)) {
            foreach ($terms as $term)
                $out .= '<li><a href="' . get_term_link($term->slug, $taxonomy) . '" title="' . $term->name . '" >' . $term->name . '</a></li>';
        }
        $out .= "</ul>";
    }
    $out .= "</ul>";
    echo $out;
}
?>