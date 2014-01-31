<?php
/*
 * Settings options to enable rtWiki features like show subpages on "Pages" post_types
 */


add_action('admin_menu', 'fwds_plugin_settings');

function fwds_plugin_settings() {
    add_submenu_page('options-general.php', 'rtWiki Settings', 'rtWiki Settings', 'administrator', 'rtWiki_settings', 'rtWiki_display_settings');
}

function rtWiki_display_settings() {

    $args = array(
        //'public' => true,
        //'_builtin' => true,
        'hierarchical' => true,
    );
    $post_types = get_post_types($args);
    
    /* Provide the post type slug to exclude */
    $exclude = apply_filters( 'rtwiki_exclude_post_types', array() );
    ?>  

    <div class="wrap">


        <?php
            $rtwiki_settings = get_site_option('rtwiki_settings');
            $attributes = $rtwiki_settings['attribute'];
            $custom_post = $rtwiki_settings['custom_post'];
        ?>

        <form method="post">

            <h2>rtWiki Settings</h2> <?php wp_nonce_field('update-options'); ?>

            <h4>Extra Settings for different post types </h4>
            
             Note: <span style="margin-left:20px; font-style:italic; font-weight: bold;"> Check the checkbox if you want to enable  functionality as same as wiki CPT for different post types</span>
             <h4>To show on Frontend , add widgets to the sidebars.</h4>
             <label>Use custom type for wiki </label> &nbsp; <input onclick="jQuery('.rtwiki_section_name').css( 'display', '' )" type="radio" value="y" <?php if( 'y' == $custom_post['option'] ) echo 'checked=checked'; ?> name="rtwiki_settings[custom_post][option]" />Yes &nbsp; <input type="radio" value="n" name="rtwiki_settings[custom_post][option]" onclick="jQuery('.rtwiki_section_name').css( 'display', 'none' )" <?php if( '' == $custom_post['option'] || 'n' == $custom_post['option'] ) echo 'checked=checked'; ?> />No
             <br />
             <div class="rtwiki_section_name" style="display: none;"><label>Wiki Section Name </label> &nbsp; <input type="text" name="rtwiki_settings[custom_post][slug]" value="<?php if( 'y' == $custom_post['option'] ) echo ucwords( $custom_post['slug'] ); ?>" /></div>
            <table class="form-table">
                <input type="hidden" name="rtWiki_hidden" value="Y" />
                <tbody> 
                    
                    <tr valign="top">
                        <th scope="row">Post Types</th>
                        <th>Enable Attributes</th>
                    </tr>  

                    <?php foreach ($post_types as $types) {
                        
                       if(in_array($types,$exclude,true))
                        continue; ?>
                        <tr valign="top">
                            <td scope="row"><?php echo ucwords( $types ); ?></td>
                            <td> <input type="checkbox" name="rtwiki_settings[attribute][]" value="<?php echo $types; ?>" <?php if ( in_array( $types, $attributes ) ) { ?>checked="checked"<?php } ?> /> </td>
                        </tr>
                    <?php } ?>
                      
                        
                </tbody>
            </table>
            
           
            <div class="submit">  
                <input type="submit" value="Update Options" />  
            </div>
        </form>
    </div>
    <?php
}

function rtwiki_save_settings() {
    
    $rtWikiHidden=isset($_POST['rtWiki_hidden']) ? $_POST['rtWiki_hidden'] : '' ; 
    if ($rtWikiHidden == 'Y') {
        //Form data sent  
        update_site_option('rtwiki_settings', $_POST['rtwiki_settings'])
        ?>  
        <div class="updated"><p><strong><?php _e('Options saved.'); ?></strong></p></div>  
        <?php
    }
}

add_action('admin_init', 'rtwiki_save_settings');