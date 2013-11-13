<?php

// Add submenu page
//add_submenu_page( 'rtcrm-studio', __( 'Attributes' ), __( 'Attributes' ), $rtCRMRoles->global_caps['manage_attributes'], 'rtcrm-attributes', array( $rtCRMAttributes, 'attributes_page' ) );

// Callback Function
//function attributes_page() {
//	//rtcrm_attributes();
//}

// Render Function
//function rtcrm_attributes() {
//
//    // Perform Update old/Add new
//	$action_completed = perform_action();
//
//	// If an attribute was added, edited or deleted: clear cache and redirect
//	if ( ! empty( $action_completed ) ) {
//		wp_redirect( admin_url( 'admin.php?page=rtcrm-attributes' ) );
//	}
//
//	// Show admin interface
//	if ( ! empty( $_GET['edit'] ) )
//		rtcrm_edit_attribute();
//	else
//		rtcrm_add_attribute();
//}


function register_taxonomy( $post_type, $attr_id ) {
	global $rtCRMAttributesModel;
	$tax = $rtCRMAttributesModel->get_attribute( $attr_id );
	$name = rtcrm_attribute_taxonomy_name( $tax->attribute_name );
	$hierarchical = true;
	if ($name) {

		$label = ( isset( $tax->attribute_label ) && $tax->attribute_label ) ? $tax->attribute_label : $tax->attribute_name;

		$show_in_nav_menus = apply_filters( 'rtcrm_attribute_show_in_nav_menus', false, $name );

		register_taxonomy( $name,
			apply_filters( 'rtcrm_taxonomy_objects_' . $name, $post_type ),
			apply_filters( 'rtcrm_taxonomy_args_' . $name, array(
				'hierarchical' 				=> $hierarchical,
				'update_count_callback' 	=> '_update_post_term_count',
				'labels' => array(
						'name' 						=> $label,
						'singular_name' 			=> $label,
						'search_items' 				=> __( 'Search' ) . ' ' . $label,
						'all_items' 				=> __( 'All' ) . ' ' . $label,
						'parent_item' 				=> __( 'Parent' ) . ' ' . $label,
						'parent_item_colon' 		=> __( 'Parent' ) . ' ' . $label . ':',
						'edit_item' 				=> __( 'Edit' ) . ' ' . $label,
						'update_item' 				=> __( 'Update' ) . ' ' . $label,
						'add_new_item' 				=> __( 'Add New' ) . ' ' . $label,
						'new_item_name' 			=> __( 'New' ) . ' ' . $label
					),
				'show_ui' 					=> true,
				'query_var' 				=> true,
				'capabilities'			=> array(
					'manage_terms' 		=> 'manage_rtcrm_terms',
					'edit_terms' 		=> 'edit_rtcrm_terms',
					'delete_terms' 		=> 'delete_rtcrm_terms',
					'assign_terms' 		=> 'assign_rtcrm_terms',
				),
				'show_in_nav_menus' 		=> $show_in_nav_menus,
//						'rewrite' 					=> array( 'slug' => $product_attribute_base . sanitize_title( $tax->attribute_name ), 'with_front' => false, 'hierarchical' => $hierarchical ),
				'rewrite' => true,
			) )
		);
	}
}