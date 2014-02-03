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
            $rtwiki_settings = '';
            $rtwiki_custom = '';
            if( is_multisite() ) {
                $rtwiki_settings = get_site_option('rtwiki_settings', true);
                $rtwiki_custom = get_site_option('rtwiki_custom', true);
            }
            else {
                $rtwiki_settings = get_option('rtwiki_settings', true);
                $rtwiki_custom = get_option('rtwiki_custom', true);
            }
            $attributes = $rtwiki_settings['attribute'];
            $custom_post = $rtwiki_settings['custom_post'];
        ?>

        <form method="post">

            <h2>rtWiki Settings</h2> <?php wp_nonce_field('update-options'); ?>

            <h4>Extra Settings for different post types </h4>
            
             Note: <span style="margin-left:20px; font-style:italic; font-weight: bold;"> Check the checkbox if you want to enable  functionality as same as wiki CPT for different post types</span>
             <h4>To show on Frontend , add widgets to the sidebars.</h4>
             <table class="form-table">
                 <tr valign="top"><th scope="row"><label>Use custom type for wiki </label></th><td><input onclick="jQuery('.rtwiki_section_name').css( 'display', '' )" type="radio" value="y" <?php if( 'y' == $custom_post ) echo 'checked=checked'; ?> name="rtwiki_settings[custom_post]" />Yes &nbsp; <input type="radio" value="n" name="rtwiki_settings[custom_post]" onclick="jQuery('.rtwiki_section_name').css( 'display', 'none' )" <?php if( !isset( $custom_post ) || '' == $custom_post || 'n' == $custom_post ) echo 'checked=checked'; ?> />No</td></tr>
                 <tr valign="top" class="rtwiki_section_name" <?php if( !isset( $custom_post ) || ( 'n' == $custom_post ) || ( '' == $custom_post ) ) echo "style='display: none;'" ; ?> ><th scope="row"><label>Wiki Section Name </label></th><td><input type="text" name="rtwiki_custom[]" value="<?php if( 'y' == $custom_post ) echo ucwords( $rtwiki_custom[0]['label'] ); ?>" /></td></tr>
             </table>
            <table class="form-table">
                <input type="hidden" name="rtWiki_hidden" value="Y" />
                <tbody> 
                    
                    <tr valign="top">
                        <th scope="row">Post Types</th>
                        <td><b>Enable Attributes</b></td>
                    </tr>  

                    <?php foreach ($post_types as $types) {
                        
                       if(in_array($types,$exclude,true))
                        continue; ?>
                        <tr valign="top">
                            <th scope="row"><?php echo ( $types == $rtwiki_custom[0]['slug'] )? $rtwiki_custom[0]['label']: ucwords($types); ?></th>
                            <td> <input type="checkbox" name="rtwiki_settings[attribute][]" value="<?php echo $types ?>" <?php if ( in_array( $types, $attributes ) ) { ?>checked="checked"<?php } ?> /> </td>
                        </tr>
                    <?php } ?>
                      
                        
                </tbody>
            </table>
            
           
            <div class="submit">  
                <input type="submit" value="Update Options" class='button-primary' />  
            </div>
        </form>
    </div>
    <?php
}

function rtwiki_save_settings() {
    
    $rtWikiHidden=isset($_POST['rtWiki_hidden']) ? $_POST['rtWiki_hidden'] : '' ; 
    if ($rtWikiHidden == 'Y') {
        //Form data sent  
        $rtwiki_custom = '';
        if( is_multisite() )
            $rtwiki_custom = get_site_option ( 'rtwiki_custom', true );
        else
            $rtwiki_custom = get_option ( 'rtwiki_custom', true );
        if( isset( $_POST['rtwiki_settings'] ) ){
            $_POST['rtwiki_settings']['default']['slug'] = 'wiki';
            $_POST['rtwiki_settings']['default']['label'] = 'Wiki';
            if( ( 'n' == $_POST['rtwiki_settings']['custom_post'] ) && isset($_POST['rtwiki_custom']) && ( '' == $_POST['rtwiki_custom'] ) )
                $rtwiki_custom = $rtwiki_custom;
            else {
                if( '' == $rtwiki_custom[0]['slug'] )
                    $rtwiki_custom[0]['slug'] = rtwiki_sanitize_taxonomy_name ( $_POST['rtwiki_custom'][0] );
                
                $rtwiki_custom[0]['label'] = $_POST['rtwiki_custom'][0];
            }
            if( is_multisite() ) {
                update_site_option('rtwiki_settings', $_POST['rtwiki_settings']);
                update_site_option('rtwiki_custom', $rtwiki_custom);
            }
            else {
                update_option('rtwiki_settings', $_POST['rtwiki_settings']);
                update_option('rtwiki_custom', $rtwiki_custom);
            }
            ?>  
                <div class="updated"><p><strong><?php _e('Options saved.'); ?></strong></p></div>  
            <?php
        }
    }
}

add_action('admin_init', 'rtwiki_save_settings');