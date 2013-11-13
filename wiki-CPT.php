<?php
/*
  Plugin Name: rtWiki CPT
  Description: Declares and create a wiki CPT
  Version: 1.0
  Author: Prannoy Tank a.k.a Wolverine
 */


require dirname(__FILE__) . '/lib/user-groups.php';

wp_register_script('rtwiki-custom-script', plugins_url('/js/rtwiki-custom-script.js', __FILE__), array('jquery'));
wp_enqueue_script('rtwiki-custom-script');

add_action('init', 'create_wiki');

function create_wiki() {
    register_post_type('wiki', array(
        'labels' => array(
            'name' => 'Wiki',
            'singular_name' => 'wiki',
            'add_new' => 'Add New',
            'add_new_item' => 'Add New wiki',
            'edit' => 'Edit',
            'edit_item' => 'Edit wiki',
            'new_item' => 'New wiki',
            'view' => 'View',
            'view_item' => 'View wiki',
            'search_items' => 'wiki',
            'not_found' => 'No wiki found',
            'not_found_in_trash' =>
            'No wiki found in Trash',
            'parent' => 'Parent wiki'
        ),
        'hierarchical' => true,
        'public' => true,
        'menu_position' => 10,
        'supports' =>
        array('title', 'editor', 'comments',
            'thumbnail',),
        'has_archive' => true
            )
    );
}

add_action('admin_init', 'my_admin');

function my_admin() {
    add_meta_box('wiki_post_access', 'Permissions', 'display_wiki_post_access_metabox', 'wiki', 'normal', 'high');
}

function display_wiki_post_access_metabox($post) {
    wp_nonce_field(plugin_basename(__FILE__), $post->post_type . '_noncename');

    $access_rights = get_post_meta($post->ID, 'access_rights', true);
    ?>  
    <table>
        <tr>
            <td><h4>Public Permission:</h4></td>    
            <td><input type="checkbox" id="public" name="public" value=""> </td>    
        </tr>

        <tr>
            <th>Groups</th>
            <th>No Access</th>
            <th>Read</th>
            <th>Write</th>
        </tr>

        <tr>
            <td>All</td>
            <td><input type="checkbox" class="all_na" name="access_rights[all][na]" <?php if ($access_rights['all']['na'] == '1') { ?>checked="checked"<?php } ?> value="<?php echo $access_rights['all']['na']; ?>"></td>
            <td><input type="checkbox" class="all_r" name="access_rights[all][r]" <?php if ($access_rights['all']['r'] == '1') { ?>checked="checked"<?php } ?> value="<?php echo $access_rights['all']['r']; ?>"></td>
            <td><input type="checkbox" class="all_w" name="access_rights[all][w]" <?php if ($access_rights['all']['w'] == '1') { ?>checked="checked"<?php } ?> value="<?php echo $access_rights['all']['w']; ?>"></td>
        </tr>

        <?php
        $args = array('orderby' => 'asc', 'hide_empty' => false);
        $terms = get_terms('user-group', $args);
        foreach ($terms as $term) {
            $groupName = $term->name;
            ?>
            <tr>
                <td><?php echo $groupName ?></td>
                <td><input type="checkbox" class="case" id="na" name="access_rights[<?php echo $groupName ?>][na]"  <?php if ($access_rights[$groupName]['na'] == '1') { ?>checked="checked"<?php } ?> value="<?php echo $access_rights[$groupName]['na']; ?>"></td>
                <td><input type="checkbox" class="case" id="r" name="access_rights[<?php echo $groupName ?>][r]" <?php if ($access_rights[$groupName]['r'] == '1') { ?>checked="checked"<?php } ?> value="<?php echo $access_rights[$groupName]['r']; ?>"></td>
                <td><input type="checkbox" class="case" id="w" name="access_rights[<?php echo $groupName ?>][w]" <?php if ($access_rights[$groupName]['w'] == '1') { ?>checked="checked"<?php } ?> value="<?php echo $access_rights[$groupName]['w']; ?>"></td>
            </tr>
    <?php } ?> 
    </table>
    <?php
}

function rtp_wiki_permission_save($post) {

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
        return;

    // verify this came from the our screen and with proper authorization,
    // because save_post can be triggered at other times

   // if (!wp_verify_nonce(@$_POST[$_POST['post_type'] . '_noncename'], plugin_basename(__FILE__)))
       // return;

    if ('wiki' == $_POST['post_type']) {
        if (!current_user_can('edit_page', $post)) {
            return;
        } else {
            $perm = array('na', 'r', 'w');
            $args = array('orderby' => 'asc', 'hide_empty' => false);
            $terms = get_terms('user-group', $args);
            $group = array();
            foreach ($terms as $term) {
                $group[] = $term->name;
            }
            array_unshift($group, 'all');
            foreach ($group as $g) {
                foreach ($perm as $p) {
                    $value = isset($_POST['access_rights'][$g][$p]) ? '1' : '0';
                    $access_rights[$g][$p] = $value;
                }
            }
            update_post_meta($post, 'access_rights', $access_rights);
        }
    }
}

add_action('save_post', 'rtp_wiki_permission_save');


/* Email Address field in User group taxonomy */

// Add term page
function user_group_taxonomy_add_new_meta_field() {
    // this will add the custom meta field to the add new term page
    ?>
    <div class="form-field">
        <label for="term_meta[email_address]"><?php _e('Email Address', 'rtcamp'); ?></label>
        <input type="text" name="user-group[email_address]" id="user-group[email_address]" value="">
        <p class="description"><?php _e('Enter a Email address for this field', 'rtcamp'); ?></p>
    </div>
    <?php
}

add_action('user-group_add_form_fields', 'user_group_taxonomy_add_new_meta_field', 10, 2);

// Edit term page
function user_group_taxonomy_edit_meta_field($term) {

    // put the term ID into a variable
    $t_id = $term->term_id;

    // retrieve the existing value(s) for this meta field. This returns an array
    $term_meta = get_option("user-group-meta");
    ?>

    <tr class="form-field">
        <th scope="row" valign="top"><label for="term_meta[email_address]"><?php _e('Email Address', 'rtCamp'); ?></label></th>
        <td>
            <input type="text" name="user-group[email_address]" id="user-group[email_address]" value="<?php echo esc_attr($term_meta[$t_id]['email_address']) ? esc_attr($term_meta[$t_id]['email_address']) : ''; ?>">
            <p class="description"><?php _e('Enter a email address for this field', 'rtcamp'); ?></p>
        </td>
    </tr>
    <?php
}

add_action('user-group_edit_form_fields', 'user_group_taxonomy_edit_meta_field', 10, 2);

function save_taxonomy_custom_meta($term_id) {

    if (isset($_POST['user-group'])) {

        $term_meta = (array) get_option('user-group-meta');

        $term_meta[$term_id] = (array) $_POST['user-group'];
        update_option('user-group-meta', $term_meta);

        if (isset($_POST['_wp_original_http_referer'])) {
            wp_safe_redirect($_POST['_wp_original_http_referer']);
            exit();
        }
    }
}

add_action('edited_user-group', 'save_taxonomy_custom_meta', 20, 2);
add_action('create_user-group', 'save_taxonomy_custom_meta', 20, 2);



add_action('template_redirect', 'redirect_404');
/* Redirect to edit post page when page not found in wiki */

function redirect_404() {
    if (is_404() && get_query_var('post_type') == 'wiki') {
        $my_post = array(
            'post_title' => get_query_var('name'),
            'post_content' => '',
            'post_status' => 'publish',
            'post_author' => 1,
            'post_type' => 'page',
            'slug' => get_query_var('name')
        );

// Insert the post into the database
        $postid = wp_insert_post($my_post);
        $url = admin_url('post.php?post=' . $postid . '&action=edit');
        wp_redirect($url);
        exit();
    }
}

function wiki_post_redirect() {
    global $post;

    $id = $post->ID;
    $access_rights = get_post_meta($post->ID, 'access_rights', true);


    if ('wiki' === $post->post_type && (!is_user_logged_in() || !($user = wp_get_current_user()) || $post->ID !== get_user_meta($user->ID, 'student_post', true))
    ) {
        wp_redirect(home_url());
        exit();
    }
}

add_action('template_redirect', 'wiki_post_redirect');

function add_custom_taxonomies() {
    // Add new "servicess" taxonomy to Posts
    register_taxonomy('services', 'wiki', array(
       
        'hierarchical' => true,
        // This array of options controls the labels displayed in the WordPress Admin UI
        'labels' => array(
            'name' => _x('services', 'taxonomy general name'),
            'singular_name' => _x('services', 'taxonomy singular name'),
            'search_items' => __('Search services'),
            'all_items' => __('All services'),
            'parent_item' => __('Parent services'),
            'parent_item_colon' => __('Parent services:'),
            'edit_item' => __('Edit services'),
            'update_item' => __('Update services'),
            'add_new_item' => __('Add New services'),
            'new_item_name' => __('New services Name'),
            'menu_name' => __('Services'),
        ),
        // Control the slugs used for this taxonomy
        'rewrite' => array(
            'slug' => 'services', // This controls the base slug that will display before each term
            'with_front' => false, // Don't display the category base before "/servicess/"
            'hierarchical' => true // This will allow URL's like "/servicess/boston/cambridge/"
        ),
    ));
}

add_action('init', 'add_custom_taxonomies', 0);


function services_taxonomy_add_new_meta_field() {
    // this will add the custom meta field to the add new term page
    ?>
    <div class="form-field">
        <label for="term_meta[services]"><?php _e('Source', 'rtcamp'); ?></label>
        <input type="text" name="services[source]" id="services[source]" value="">
        <p class="description"><?php _e('Enter a Source for this field', 'rtcamp'); ?></p>
    </div>
    <?php
}

add_action('services_add_form_fields', 'services_taxonomy_add_new_meta_field', 10, 2);


function services_taxonomy_edit_meta_field($term) {

    // put the term ID into a variable
    $t_id = $term->term_id;
   
    // retrieve the existing value(s) for this meta field. This returns an array
    $term_meta = get_option("services");
    
    ?>

    <tr class="form-field">
        <th scope="row" valign="top"><label for="term_meta[email_address]"><?php _e('Source', 'rtCamp'); ?></label></th>
        <td>
            <input type="text" name="services[source]" id="services[source]" value="<?php echo esc_attr($term_meta[$t_id]['source']) ? esc_attr($term_meta[$t_id]['source']) : ''; ?>">
            <p class="description"><?php _e('Enter a Source for this field', 'rtcamp'); ?></p>
        </td>
    </tr>
    <?php
}

add_action('services_edit_form_fields', 'services_taxonomy_edit_meta_field', 10, 2);

function save_service_taxonomy_custom_meta($term_id) {

    if (isset($_POST['services'])) {

        $term_meta = (array) get_option('services');
       
        $term_meta[$term_id] = (array) $_POST['services'];
        update_option('services', $term_meta);

        if (isset($_POST['_wp_original_http_referer'])) {
            wp_safe_redirect($_POST['_wp_original_http_referer']);
            exit();
        }
    }
}
add_action('edited_services', 'save_service_taxonomy_custom_meta', 20, 2);
add_action('create_services', 'save_service_taxonomy_custom_meta', 20, 2);

add_filter( 'manage_edit_services_columns', array(&$this,'manage_services_column'));
function manage_services_column( $columns ) {

		unset($columns['slug'] );		
                $columns['source']=__('Source','services');        
		return $columns;
}


function manage_services_source_column( $display, $column, $term_id ) {

		switch($column) {
			
                        case 'source';
                                $term_meta = get_option("services");                               
                                $source= $term_meta[$term_id]['source'];                                 
                                echo $source;
				break;
                                }
		return;
}

add_action( 'manage_services_custom_column', array(&$this,'manage_services_source_column'), 10, 3 );
