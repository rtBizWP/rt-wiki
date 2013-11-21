<?php

function rt_wiki_sidebar() {

    register_sidebar(array(
        'name' => __('rtWiki', 'rtCamp'),
        'id' => 'rt-wiki-sidebar',
        'description' => __('An optional sidebar for the rtWiki Widget', 'rtCamp'),
        'before_widget' => '<div id="%1$s" class="headwidget %2$s">',
        'after_widget' => "</div>",
        'before_title' => '<h3>',
        'after_title' => '</h3>',
    ));

    
}

add_action('widgets_init', 'rt_wiki_sidebar');