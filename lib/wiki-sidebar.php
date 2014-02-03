<?php

/**
 * 
 * Register rtWiki custom sidebar in the widget area.
 * 
 */


function rt_wiki_sidebar() {

    register_sidebar(array(
        'name' => __('rtWiki Widget Area', 'rtCamp'),
        'id' => 'rt-wiki-sidebar',
        'description' => __('An optional sidebar for the rtWiki Widget', 'rtCamp'),
        'before_widget' => '<aside id="%1$s" class="widget %2$s">',
        'after_widget' => '</aside>',
        'before_title' => '<h3 class="widget-title">',
        'after_title' => '</h3>',
    ));    
}

//add_action('widgets_init', 'rt_wiki_sidebar');