<?php

/*
 * Get post Contributers list via revisions 
 */

function getContributers() {
    if (get_query_var('post_type') == 'wiki') {
        global $post;
        $revision = wp_get_post_revisions($post->ID);
        $authorId = array();
        $authorName = array();
        foreach ($revision as $revisions) {

            if (!in_array($revisions->post_author, $authorId, true)) {
                $id = $revisions->post_author;
                $authorId[] = $revisions->post_author;
                $authorName[] = get_userdata($id)->display_name;
            }
        }
        return $authorName;
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
        print '<ul>';
        foreach ($pages as $page) {

            $permission = getPermission($page->ID);
            if ($permission == true) {
                print '<li>' . $page->post_title . "</li>";
            }

            getSubPages($page->ID, $lvl);
        }
        print '</ul>';
    }
}
