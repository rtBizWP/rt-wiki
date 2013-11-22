<?php
function rc_tc_template_chooser($template) {

    // Post ID
    $post_id = get_the_ID();

    // For all other CPT
    if (get_post_type($post_id) != 'wiki') {
        return $template;
    }

    // Else use custom template
    if (is_single()) {
        return rc_tc_get_template_hierarchy('single-wiki');
    }
}

/**
 * Get the custom template if is set
 *
 * @since 1.0
 */
function rc_tc_get_template_hierarchy($template) {

    // Get the template slug
    $template_slug = rtrim($template, '.php');
    $template = $template_slug . '.php';
    
    $file = RC_TC_BASE_DIR . '/templates/' . $template;
    return apply_filters('rc_repl_template_' . $template, $file);
}

add_filter('template_include', 'rc_tc_template_chooser');





