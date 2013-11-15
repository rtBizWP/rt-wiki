<?php
/*
  Plugin Name: rtWiki
  Description: Declares and create a wiki CPT.Adds email metafield to user group taxonomy.
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
            'thumbnail', 'revisions'),
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
            <td><input type="radio" class="all_na" name="access_rights[all][na]" <?php if ($access_rights['all']['na'] == '1') { ?>checked="checked"<?php } ?> value="<?php echo $access_rights['all']['na']; ?>"></td>
            <td><input type="radio" class="all_r" name="access_rights[all][r]" <?php if ($access_rights['all']['r'] == '1') { ?>checked="checked"<?php } ?> value="<?php echo $access_rights['all']['r']; ?>"></td>
            <td><input type="radio" class="all_w" name="access_rights[all][w]" <?php if ($access_rights['all']['w'] == '1') { ?>checked="checked"<?php } ?> value="<?php echo $access_rights['all']['w']; ?>"></td>
        </tr>

        <?php
        $args = array('orderby' => 'asc', 'hide_empty' => false);
        $terms = get_terms('user-group', $args);
        foreach ($terms as $term) {
            $groupName = $term->name;
            ?>
            <tr>
                <td><?php echo $groupName ?></td>
                <td><input type="radio" class="case" id="na" name="access_rights[<?php echo $groupName ?>][na]"  <?php if ($access_rights[$groupName]['na'] == '1') { ?>checked="checked"<?php } ?> value="<?php echo $access_rights[$groupName]['na']; ?>"></td>
                <td><input type="radio" class="case" id="r" name="access_rights[<?php echo $groupName ?>][r]" <?php if ($access_rights[$groupName]['r'] == '1') { ?>checked="checked"<?php } ?> value="<?php echo $access_rights[$groupName]['r']; ?>"></td>
                <td><input type="radio" class="case" id="w" name="access_rights[<?php echo $groupName ?>][w]" <?php if ($access_rights[$groupName]['w'] == '1') { ?>checked="checked"<?php } ?> value="<?php echo $access_rights[$groupName]['w']; ?>"></td>
            </tr>
        <?php } ?> 

        <input type="button" name="reset" id="reset" value="Reset">
    </table>

    <?php
}

function rtp_wiki_permission_save($post) {

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
        return;

    // verify this came from the our screen and with proper authorization,
    // because save_post can be triggered at other times

    if (!wp_verify_nonce(@$_POST[$_POST['post_type'] . '_noncename'], plugin_basename(__FILE__)))
        return;

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

function rtwiki_get_page_id($name) {
    global $wpdb;
    $page_id = $wpdb->get_var("SELECT ID FROM $wpdb->posts WHERE ( post_name = '" . $name . "' or post_title = '" . $name . "' ) and post_status = 'publish' and post_type='wiki' ");
    return $page_id;
}

add_action('template_redirect', 'redirect_404');
/* Redirect to edit page when page not found in wiki */

function redirect_404() {
    if (is_404() && get_query_var('post_type') == 'wiki') {


        $page = $_SERVER['REQUEST_URI'];
        $segments = explode('/', trim($page, '/'));
        if ($segments[0] == 'wiki') {
            $postid = '';
            for ($i = 1; $i < count($segments); $i++) {

                $page = rtwiki_get_page_id($segments[$i]);

                var_dump($page);
                if ($i == 1) {


                    if ($page != null) {
                        
                    } else {

                        $my_post1 = array(
                            'post_title' => $segments[$i],
                            'post_content' => '',
                            'post_status' => 'publish',
                            'post_author' => 1,
                            'post_type' => 'wiki',
                            'slug' => $segments[$i],
                        );
                        $postid = wp_insert_post($my_post1);
                    }
                } else {

                    $pid = $i - 1;
                    $parentId = rtwiki_get_page_id($segments[$pid]);
                    if ($page != null) {
                        
                    } else {

                        $my_post = array(
                            'post_title' => $segments[$i],
                            'post_content' => '',
                            'post_status' => 'publish',
                            'post_author' => 1,
                            'post_type' => 'wiki',
                            'slug' => $segments[$i],
                            'post_parent' => $parentId,
                        );
                        $postid = wp_insert_post($my_post);
                    }
                }
            }
            $url = admin_url('post.php?post=' . $postid . '&action=edit');
            wp_redirect($url);
        }
    }
}

/* Single post content  */

function single_post_filtering() {
    global $post;
    $rflag = 0;
    $wflag = 0;
    $noflag = 0;
    $user = get_current_user_id();
    $terms = get_terms('user-group', array('hide_empty' => false));
    $access_rights = get_post_meta($post->ID, 'access_rights', true);

    foreach ($terms as $term) {
        $ans = get_term_if_exists($term->slug, $user);
        if ($ans == $term->slug) {

            if ($access_rights[$ans]['w'] == '1') {

                echo $post->post_title;
                $wflag = 1;
                break;
            } else if ($access_rights[$ans]['r'] == '1') {
                echo 'got read access for' . $ans;
                $rflag = 1;
                break;
            } else if ($access_rights[$ans]['na'] == '1') {
                echo 'cannot look';
                $noflag = 1;
            }
        } else if ($ans == '') {
            echo 'cannot find your access rights';
        }
    }
}

//add_action('wp','random_picture');
//add_shortcode('shortcode_name', 'single_post_filtering');

function get_term_if_exists($term, $userid) {

    global $wpdb;
    $query = "SELECT slug FROM $wpdb->terms WHERE term_id IN(SELECT term_id from $wpdb->term_taxonomy WHERE term_taxonomy_id IN(SELECT term_taxonomy_id from $wpdb->term_relationships WHERE object_id=$userid))and name='" . $term . "'";
    $page_id = $wpdb->get_var($query);
    return $page_id;
}

function admin_side_post_check() {
    global $post;
    $rflag = 0;
    $wflag = 0;
    $noflag = 0;
    $user = get_current_user_id();
    $terms = get_terms('user-group', array('hide_empty' => false));
    $access_rights = get_post_meta($post->ID, 'access_rights', true);

    foreach ($terms as $term) {
        $ans = get_term_if_exists($term->slug, $user);
        if ($ans == $term->slug) {

            if ($access_rights[$ans]['w'] == '1') {

                echo $post->post_title;
                $wflag = 1;
                break;
            } else if ($access_rights[$ans]['r'] == '1') {
                echo 'got read access for' . $ans;
                $rflag = 1;
                break;
            } else if ($access_rights[$ans]['na'] == '1') {
                
            }
        }
    }
}

/* Send mail On post Update having body as diff of content */

function post_changes_send_mail() {
    if (get_query_var('post_type') == 'wiki') {
        $post_id = absint($_POST['post']);
        $post = get_post($post_id);
        $revision = wp_get_post_revisions($post->ID);
        $textContent = array();
        $textTitle = array();
        $authorId = array();
        $authorName = array();
        foreach ($revision as $revisions) {


            $textContent[] = $revisions->post_content;
            $textTitle[] = $revisions->post_title;
        }

        $args = array(
            'title' => 'Differences',
            'title_left' => $textTitle[1],
            'title_right' => $textTitle[0],
        );
        $diff_table = wp_text_diff($textContent[1], $textContent[0], $args);

        add_filter('wp_mail_content_type', 'set_html_content_type');

        wp_mail('prannoy.tank@rtcamp.com', 'Diff', $diff_table);

        remove_filter('wp_mail_content_type', 'set_html_content_type');
    }
}

/* Get post Contributers list via revisions */

function getContributers() {
    if (get_query_var('post_type') == 'wiki') {
        $post_id = absint($_POST['post']);
        $post = get_post($post_id);
        $revision = wp_get_post_revisions($post->ID);
        $textContent = array();
        $textTitle = array();
        $authorId = array();
        $authorName = array();
        foreach ($revision as $revisions) {

            if (in_array($revisions->post_author, $authorId, true)) {
                
            } else {

                $id = $revisions->post_author;
                $authorId[] = $revisions->post_author;
                $authorName[] = get_userdata($id)->display_name;
            }
            $textContent[] = $revisions->post_content;
            $textTitle[] = $revisions->post_title;
        }

        $args = array(
            'title' => 'Differences',
            'title_left' => $textTitle[1],
            'title_right' => $textTitle[0],
        );
        $diff_table = wp_text_diff($textContent[1], $textContent[0], $args);

        echo $diff_table;
    }
}

add_action('save_post', 'getContributers');

function set_html_content_type() {

    return 'text/html';
}