<?php
function rc_tc_template_chooser($template) {

    // For all other CPT
    $return = '';
    $supported_posts = rtwiki_get_supported_attribute();
    if ( !in_array( get_post_type(), $supported_posts ) ) {
        $return = $template;
    }

    // Else use custom template
    else if (is_singular() )
        $return = rc_tc_get_template_hierarchy('single-wiki');
    else if( is_archive() )
        $return = rc_tc_get_template_hierarchy('archive-wiki');
    else
        $return = $template;
    
    return $return;
}

/**
 * Get the custom template if is set
 *
 * @since 1.0
 */


function rc_tc_get_template_hierarchy( $template ) {
 
    // Get the template slug
    $template_slug = rtrim( $template, '.php' );
    $template = $template_slug . '.php';
 
     //Check if a custom template exists in the theme folder, if not, load the plugin template file
    if ( $theme_file = locate_template( array($template ) ) ) {
        $file = $theme_file;
    }
    else {
        $file = RC_TC_BASE_DIR . '/templates/' . $template;
    }
 
    $file = RC_TC_BASE_DIR . '/templates/' . $template;
    return apply_filters( 'rc_repl_template_' . $template, $file );
}
 
/*
|--------------------------------------------------------------------------
| FILTERS
|--------------------------------------------------------------------------
*/
 
add_filter( 'template_include', 'rc_tc_template_chooser', 1 );


