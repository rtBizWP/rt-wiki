<?php 

//add_action('admin_init','rc_tc_template_chooser');


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

    // Check if a custom template exists in the theme folder, if not, load the plugin template file
    if ($theme_file = locate_template(array('plugin_templates/' . $template))) {
        $file = $theme_file;
    } else {
        $file = dirname(__FILE__). '/templates/' . $template;
    }

    return apply_filters('rc_repl_template_' . $template, $file);
}

add_filter('template_include', 'rc_tc_template_chooser');





