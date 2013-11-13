<?php

function custom_post_status(){
	register_post_status( 'unread', array(
		'label'                     => _x( 'Unread', 'wiki' ),
		'public'                    => true,
		'exclude_from_search'       => false,
		'show_in_admin_all_list'    => true,
		'show_in_admin_status_list' => true,
		'label_count'               => _n_noop( 'Unread <span class="count">(%s)</span>', 'Unread <span class="count">(%s)</span>' ),
	) );
}
add_action( 'init', 'custom_post_status' );