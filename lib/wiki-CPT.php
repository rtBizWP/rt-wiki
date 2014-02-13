<?php
/**
 * Creates A wiki CPT , along with permissions metabox .
 * Adds email address custom field to user-group taxonomy.
 */
require_once dirname(__FILE__) . '/user-groups.php';
require_once dirname(__FILE__) . '/wiki-post-filtering.php';

/**
 * Creates wiki named CPT.
 */
add_action('init', 'create_wiki');

function create_wiki() {

    $rtwiki_settings = '';
    $rtwiki_custom = '';
    if (is_multisite()) {
        $rtwiki_settings = get_site_option("rtwiki_settings", array());
        $rtwiki_custom = get_site_option("rtwiki_custom", array());
    } else {
        $rtwiki_settings = get_option("rtwiki_settings", array());
        $rtwiki_custom = get_option("rtwiki_custom", array());
    }

    $post_name = array();
    if (isset($rtwiki_settings['custom_wiki']) && ( 'y' == $rtwiki_settings['custom_wiki'] ) && ( count($rtwiki_custom) > 0 )) {
        $post_name = $rtwiki_custom;
    }

    if (is_array($post_name) && ( count($post_name) > 0 )) {
        foreach ($post_name as $name) {
            $slug = $name['slug'];
            $label = ucwords($name['label']);
            /* $capabilities = array(
              'read_post' => 'read_post',
              'publish_posts' => 'publish_posts',
              'edit_posts' => 'edit_posts',
              'edit_others_posts' => 'edit_others_posts',
              'delete_posts' => 'delete_posts',
              'delete_others_posts' => 'delete_others_posts',
              'read_private_posts' => 'read_private_posts',
              'edit_post' => 'edit_post',
              'delete_post' => 'delete_post',
              'edit_published_posts' => 'edit_published_posts',
              'delete_published_posts' => 'delete_published_posts'
              ); */
            $labels = array(
                'name' => __($slug, 'post type general name', 'rtCamp'),
                'singular_name' => __($label, 'post type singular name', 'rtCamp'),
                'add_new' => __('Add New', $label, 'rtCamp'),
                'add_new_item' => __('Add New ' . $label, 'rtCamp'),
                'edit' => __('Edit', $label, 'rtCamp'),
                'edit_item' => __('Edit ' . $label, 'rtCamp'),
                'new_item' => __('New ' . $label, 'rtCamp'),
                'view' => __('View', $label, 'rtCamp'),
                'view_item' => __('View ' . $label, 'rtCamp'),
                'search_items' => __('Search ' . $label, 'rtCamp'),
                'not_found' => __('No ' . $label . ' found', 'rtCamp'),
                'not_found_in_trash' => __('No ' . $label . ' found in Trash', 'rtCamp'),
                'all_items' => __('All ' . $label, 'rtCamp'),
                'parent' => __('Parent ' . $label, 'rtCamp')
            );
            $args = array(
                'labels' => $labels,
                'description' => __($label, 'rtCamp'),
                'public' => true,
                'publicly_queryable' => true,
                'show_ui' => true,
                'show_in_menu' => true,
                'query_var' => true,
                'rewrite' => array('slug' => $slug),
                'capability_type' => 'post',
                'has_archive' => true,
                'hierarchical' => true,
                'menu_position' => 10,
                'supports' => array('title', 'editor', 'thumbnail', 'revisions', 'page-attributes', 'excerpt'),
                '_builtin' => false,
                '_edit_link' => 'post.php?post=%d',
                'menu_icon' => true,
                'can_export' => true,
                'show_in_nav_menus' => true,
                'show_in_admin_bar' => true,
                    //'exclude_from_search' => true,
            );
            register_post_type($slug, $args);
        }
    }
}

function add_wiki_caps() {
    $roles = array(get_role('administrator'), get_role('author'), get_role('editor'), get_role('contributor'));
    foreach ($roles as $role) {
        $role->add_cap('edit_wiki');
        $role->add_cap('edit_others_wiki');
        $role->add_cap('publish_wiki');
        $role->add_cap('read_wiki');
        $role->add_cap('read_private_wiki');
        $role->add_cap('delete_wiki');
        $role->add_cap('edit_published_wiki');
        $role->add_cap('delete_published_wiki');
        $role->add_cap('delete_others_wiki');
    }
}

add_action('init', 'add_wiki_caps');

/*
 * Add User group and permission type metabox  
 */

add_action('admin_init', 'wiki_permission_metabox');

function wiki_permission_metabox() {
    $supported_posts = rtwiki_get_supported_attribute();
    if (is_array($supported_posts) && !empty($supported_posts)) {
        foreach ($supported_posts as $posts)
            add_meta_box($posts . '_post_access', 'Permissions', 'display_wiki_post_access_metabox', $posts, 'normal', 'high');
    }
}

/*
 *  Permission And Group MetaBox for wiki CPT
 */

function display_wiki_post_access_metabox($post) {
    wp_nonce_field(plugin_basename(__FILE__), $post->post_type . '_noncename');

    $access_rights = get_post_meta($post->ID, 'access_rights', true);
    $disabled = '';

    if (isset($access_rights['public']) && ( 1 == $access_rights['public'] ))
        $disabled = 'disabled="disabled"';
    ?>  
    <table>
        <tbody>
            <tr>
                <th>Groups</th>
                <th>No Access</th>
                <th>Read</th>
                <th>Write</th>
            </tr>

            <tr>
                <td>All</td>
                <td><input type="radio" onclick="if (this.checked) {
                            uncheckAllGroup('na');
                        }
                           " <?php echo $disabled; ?> class="rtwiki_all_na rtwiki_na" name="access_rights[all]" <?php if (isset($access_rights['all']['na']) && ( $access_rights['all']['na'] == 1 )) { ?>checked="checked"<?php } ?> value="na" /></td>
                <td><input type="radio" onclick="if (this.checked) {
                            uncheckAllGroup('r');
                        }
                           " class="rtwiki_all_r rtwiki_r" name="access_rights[all]" <?php if (isset($access_rights['all']['r']) == 1) { ?>checked="checked"<?php } ?> value="r" /></td>
                <td><input type="radio" onclick="if (this.checked) {
                            uncheckAllGroup('w');
                        }
                           " class="rtwiki_all_w rtwiki_w" name="access_rights[all]" <?php if (isset($access_rights['all']['w']) == 1) { ?>checked="checked"<?php } ?> value="w" /></td>
            </tr>

            <?php
            $args = array('orderby' => 'asc', 'hide_empty' => false);
            $terms = get_terms('user-group', $args);
            foreach ($terms as $term) {
                $groupName = $term->name;
                ?>
                <tr>
                    <td><?php echo $groupName ?></td>
                    <td><input type="radio" onclick="if (this.checked) {
                                uncheckAll('na');
                            }
                               " class="case_na rtwiki_na" <?php echo $disabled; ?> id="na" name="access_rights[<?php echo $groupName ?>]"  <?php if (isset($access_rights[$groupName]['na']) && ( $access_rights[$groupName]['na'] == 1 )) { ?>checked="checked"<?php } ?> value="na" /></td>
                    <td><input type="radio" onclick="if (this.checked) {
                                uncheckAll('r');
                            }
                               " class="case_r rtwiki_r" id="r" name="access_rights[<?php echo $groupName ?>]" <?php if (( '' != $disabled ) || ( isset($access_rights[$groupName]['r']) && ( $access_rights[$groupName]['r'] == 1 ) )) { ?>checked="checked"<?php } ?> value="r" /></td>
                    <td><input type="radio" onclick="if (this.checked) {
                                uncheckAll('w');
                            }
                               " class="case_w rtwiki_w" id="w" name="access_rights[<?php echo $groupName ?>]" <?php if (isset($access_rights[$groupName]['w']) && ( $access_rights[$groupName]['w'] == 1 )) { ?>checked="checked"<?php } ?> value="w" /></td>
                </tr>
            <?php } ?> 


        </tbody>    
    </table>

    <table>
        <tbody>
            <tr><th colspan='2'><h4>Permission for public level</h4></th></tr>
    <tr>
        <td>Public</td> 
        <td colspan='2'><input type="checkbox" onclick='if (this.checked) {
                    jQuery(".rtwiki_na").prop("checked", false);
                    jQuery(".rtwiki_na").prop("disabled", true);
                } else {
                    jQuery(".rtwiki_na").prop("disabled", false);
                }' id="rtwiki_public_na" name="access_rights[public]" <?php if (isset($access_rights['public']) && ( 1 == $access_rights['public'] )) { ?> checked="checked" <?php } ?> value='1' /> </td>    
    </tr>
    </tbody>

    </table>
    <?php
}

/*
 *
 * Save user and its permission as meta value
 *  
 */

function rtp_wiki_permission_save($post) {
    global $wpdb;
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
        return;

    if (!isset($_REQUEST[$_REQUEST['post_type'] . '_noncename']) || !wp_verify_nonce(@$_POST[$_POST['post_type'] . '_noncename'], plugin_basename(__FILE__)))
        return;

    $supported_posts = rtwiki_get_supported_attribute();
    if (in_array($_POST['post_type'], $supported_posts, true)) {
        if (!current_user_can('edit_page', $post)) {
            return;
        } else {
            $perm = array('na', 'r', 'w');
            $args = array('orderby' => 'asc', 'hide_empty' => false);
            $terms = get_terms('user-group', $args);
            $group = array('all');
            foreach ($terms as $term) {
                $group[] = $term->name;
            }

            if (isset($_POST['access_rights'])) {
                foreach ($_POST['access_rights'] as $key => $value) {
                    $access_rights[$key][$value] = 1;
                }
            }

            if (isset($_POST['access_rights']['public']))
                $access_rights['public'] = 1;
            else
                $access_rights['public'] = 0;

            update_post_meta($post, 'access_rights', $access_rights);

            $userId = get_current_user_id();
            $access_rights = get_post_meta($post, 'access_rights', true);

            if (isset($access_rights['all']['na']) && ( $access_rights['all']['na'] == 1 )) {
                unSubscriptionAll($post);
            } else {
                $terms = get_terms('user-group', array('hide_empty' => true));
                foreach ($terms as $term) {
                    if (isset($access_rights[$term->name]) && ( $access_rights[$term->name]['na'] == 1 )) {
                        $groupusers = $wpdb->get_col($wpdb->prepare("SELECT term_rel.object_id FROM $wpdb->term_relationships term_rel,$wpdb->term_taxonomy term_taxo,$wpdb->terms term WHERE term_rel.term_taxonomy_id = term_taxo.term_taxonomy_id and term_taxo.term_id=term.term_id and term.slug='%s'", $term->slug));
                        var_dump($groupusers);
                        if ($groupusers) {
                            foreach ($groupusers as $id) {
                                unSubscription($post, $id);
                            }
                        }
                    } else if (isset($access_rights[$term->name]) && (( $access_rights[$term->name]['r'] == 1 ) || ($access_rights[$term->name]['w'] == 1))) {
                        $groupusers = $wpdb->get_col($wpdb->prepare("SELECT term_rel.object_id FROM $wpdb->term_relationships term_rel,$wpdb->term_taxonomy term_taxo,$wpdb->terms term WHERE term_rel.term_taxonomy_id = term_taxo.term_taxonomy_id and term_taxo.term_id=term.term_id and term.slug='%s'", $term->slug));
                        var_dump($groupusers);
                        if ($groupusers) {
                            foreach ($groupusers as $id) {
                                if (checkParentSubSubscribe($id)) {
                                    pageSubscription($post, $id,TRUE);
                                }
                            }
                        }
                    }
                }
            }
        }
    }
}

add_action('save_post', 'rtp_wiki_permission_save');

/*
 * Adds Email Address field in User Group Taxonomy
 */

function user_group_taxonomy_add_new_meta_field() {
    ?>
    <div class="form-field">
        <label for="term_meta[email_address]"><?php _e('Email Address', 'rtcamp'); ?></label>
        <input type="text" name="user-group[email_address]" id="user-group[email_address]" value="">
        <p class="description"><?php _e('Enter a Email address for this field', 'rtcamp'); ?></p>
    </div>
    <?php
}

add_action('user-group_add_form_fields', 'user_group_taxonomy_add_new_meta_field', 10, 2);

/*
 *  Edit User-Group
 */

function user_group_taxonomy_edit_meta_field($term) {
    $t_id = $term->term_id;
    $term_meta = '';
    if (is_multisite())
        $term_meta = get_site_option('user-group-meta');
    else
        $term_meta = get_option('user-group-meta');
    ?>
    <tr class="form-field">
        <th scope="row" valign="top"><label for="term_meta[email_address]"><?php _e('Email Address', 'rtCamp'); ?></label></th>
        <td>
            <input type="text" name="user-group[email_address]" id="user-group[email_address]" value="<?php echo esc_attr($term_meta[$t_id]['email_address']) ? esc_attr($term_meta[$t_id]['email_address']) : ''; ?>" />
            <p class="description"><?php _e('Enter a email address for this field', 'rtcamp'); ?></p>
        </td>
    </tr>
    <?php
}

add_action('user-group_edit_form_fields', 'user_group_taxonomy_edit_meta_field', 10, 2);

/*
 *  Adds New User-Group Term
 */

function save_taxonomy_custom_meta($term_id) {

    if (isset($_POST['user-group'])) {
        $term_meta = '';
        if (is_multisite())
            $term_meta = (array) get_site_option('user-group-meta');
        else
            $term_meta = (array) get_option('user-group-meta');
        $term_meta[$term_id] = (array) $_POST['user-group'];
        if (is_multisite())
            update_site_option('user-group-meta', $term_meta);
        else
            update_option('user-group-meta', $term_meta);

        if (isset($_POST['_wp_original_http_referer'])) {
            wp_safe_redirect($_POST['_wp_original_http_referer']);
            exit();
        }
    }
}

add_action('edited_user-group', 'save_taxonomy_custom_meta', 20, 2);
add_action('create_user-group', 'save_taxonomy_custom_meta', 20, 2);
