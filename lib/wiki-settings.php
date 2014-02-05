<?php
/*
 * Settings options to enable rtWiki features like show subpages on "Pages" post_types
 */


add_action('admin_menu', 'fwds_plugin_settings');

function fwds_plugin_settings() {
    add_submenu_page('options-general.php', 'rtWiki Settings', 'rtWiki Settings', 'administrator', 'rtWiki_settings', 'rtWiki_display_settings');
}

function rtWiki_display_settings() {

    $post_types = get_post_types();
    
    /* Provide the post type slug to exclude */
    
    $exclude = apply_filters( 'rtwiki_exclude_post_types', array( 'attachment', 'revision', 'nav_menu_item', 'wiki' ) );
    if( isset( $_GET['success'] ) && ( 1 == $_GET['success'] ) ) {
            ?>  
                <div class="updated"><p><strong><?php _e('Options saved.'); ?></strong></p></div>  
            <?php
        } else if( isset( $_GET['error'] ) && ( 1 == $_GET['error'] ) ) {
            ?>
                <div class="error"><p><strong><?php _e('Custom wiki label required'); ?></strong></p></div>  
            <?php
        }
    ?>  

    <div class="wrap">


        <?php
            $rtwiki_settings = array();
            $rtwiki_custom = array();
            $attributes = array();
            if( is_multisite() ) {
                $rtwiki_settings = get_site_option( 'rtwiki_settings', array() );
                $rtwiki_custom = get_site_option( 'rtwiki_custom', array() );
            }
            else {
                $rtwiki_settings = get_option( 'rtwiki_settings', array() );
                $rtwiki_custom = get_option( 'rtwiki_custom', array() );
            }
            if( isset( $rtwiki_settings['attribute'] ) )
                $attributes = $rtwiki_settings['attribute'];
            $custom_wiki = ( isset( $rtwiki_settings['custom_wiki'] )?$rtwiki_settings['custom_wiki']:'' );

            if( isset( $rtwiki_custom[0]['slug'] ) )
                $exclude[] = $rtwiki_custom[0]['slug'];
        ?>

        <form method="post">

            <h2>rtWiki Settings</h2> <?php wp_nonce_field('update-options'); ?>

            <h4>Extra Settings for different post types </h4>
            
             Note: <span style="margin-left:20px; font-style:italic; font-weight: bold;"> Check the checkbox if you want to enable  functionality as same as wiki CPT for different post types</span>
             <h4>To show on Frontend , add widgets to the sidebars.</h4>
             <table class="form-table">
                 <tr valign="top"><th scope="row"><label>Use custom type for wiki </label></th><td><label><input onclick="jQuery('.rtwiki_section_name').css( 'display', '' )" type="radio" value="y" <?php if( ( isset( $_POST['rtwiki_settings']['custom_wiki'] ) && ( 'y' == $_POST['rtwiki_settings']['custom_wiki'] ) ) || ( 'y' == $custom_wiki ) ) echo 'checked=checked'; ?> name="rtwiki_settings[custom_wiki]" />Yes</label> &nbsp; <label><input type="radio" value="n" name="rtwiki_settings[custom_wiki]" onclick="jQuery('.rtwiki_section_name').css( 'display', 'none' )" <?php if( ( isset( $_POST['rtwiki_settings']['custom_wiki'] ) && ( 'n' == $_POST['rtwiki_settings']['custom_wiki'] ) ) || ( ( !isset( $_POST['rtwiki_settings']['custom_wiki'] ) ) && ( !isset( $custom_wiki ) || ('' == $custom_wiki) || ( 'n' == $custom_wiki ) ) ) ) echo 'checked=checked'; ?> />No</label></td></tr>
                 <tr valign="top" class="rtwiki_section_name" <?php if( ( isset( $_POST['rtwiki_settings']['custom_wiki'] ) && ( 'n' == $_POST['rtwiki_settings']['custom_wiki'] ) ) || ( ( !isset( $_POST['rtwiki_settings']['custom_wiki'] ) ) && ( !isset( $custom_wiki ) || ( 'n' == $custom_wiki ) || ( '' == $custom_wiki ) ) ) ) echo "style='display: none;'" ; ?> ><th scope="row"><label>Wiki Section Name </label></th><td><input type="text" name="rtwiki_custom[]" value="<?php if( 'y' == $custom_wiki ) echo ucwords( $rtwiki_custom[0]['label'] ); ?>" /></td></tr>
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
                            <th scope="row"><?php echo ucwords($types); ?></th>
                            <td> <input type="checkbox" name="rtwiki_settings[attribute][]" value="<?php echo $types ?>" <?php if ( ( isset( $_POST['rtwiki_settings']['attribute'] ) && in_array( $types, $_POST['rtwiki_settings']['attribute'], true ) ) || in_array( $types, $attributes, true ) ) { ?>checked="checked"<?php } ?> /> </td>
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
        $rtwiki_custom = array();
        $error = true;
        if( is_multisite() )
            $rtwiki_custom = get_site_option ( 'rtwiki_custom', array() );
        else
            $rtwiki_custom = get_option ( 'rtwiki_custom', array() );
        if( isset( $_POST['rtwiki_settings'] ) ){
            if( 'n' == $_POST['rtwiki_settings']['custom_wiki'] )
                $error = false;
            else {
                $rtwiki_custom_args = array( 'slug' => '', 'label' => '' );
                if( isset( $_POST['rtwiki_custom'][0] ) && !empty( $_POST['rtwiki_custom'][0] ) ) {
                    if( !isset( $rtwiki_custom[0]['slug'] ) ) {
                        $taxonomy_name = rtwiki_sanitize_taxonomy_name ( $_POST['rtwiki_custom'][0] );
                        $rtwiki_custom_args['slug'] = $taxonomy_name;
                    }
                    else 
                        $rtwiki_custom_args['slug'] = $rtwiki_custom[0]['slug'];
                    $rtwiki_custom_args['label'] = $_POST['rtwiki_custom'][0];
                    $rtwiki_custom = array( $rtwiki_custom_args );
                    $error = false;
                }
            }
            $url = "options-general.php?page=rtWiki_settings";
            if( !$error ) {
                if( is_multisite() ) {
                    update_site_option('rtwiki_settings', $_POST['rtwiki_settings']);
                    update_site_option('rtwiki_custom', $rtwiki_custom);
                }
                else {
                    update_option('rtwiki_settings', $_POST['rtwiki_settings']);
                    update_option('rtwiki_custom', $rtwiki_custom);
                }
                $url .= "&success=1";
            }
            else {
                $url .= "&error=1";
            }
            wp_redirect($url);
        }
    }
}

add_action('init', 'rtwiki_save_settings', 2);