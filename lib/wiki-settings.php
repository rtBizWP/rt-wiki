<?php
/*
 * Settings options to enable rtWiki features like show subpages on "Pages" post_types
 */


add_action('admin_menu', 'fwds_plugin_settings');

function fwds_plugin_settings() {
    add_menu_page('rtWiki Settings', 'rtWiki Settings', 'administrator', 'rtWiki_settings', 'rtWiki_display_settings');
}

function rtWiki_display_settings() {

    $args = array(
        //'public' => true,
        //'_builtin' => true,
        'hierarchical' => true,
    );
    $post_types = get_post_types($args);
    $exclude=array('wiki');
    ?>  

    <div class="wrap">


        <?php
        $subPagesOptions = get_option('rtWiki_subpages_options');
        $subscribeOptions = get_option('rtWiki_subscribe_options');
        ?>

        <form action="<?php echo str_replace('%7E', '~', $_SERVER['REQUEST_URI']); ?>" method="post">

            <h2>Select Your Settings</h2> <?php wp_nonce_field('update-options'); ?>

            <h4>Extra Settings for different post types </h4>
            <table class="form-table">
                <input type="hidden" name="rtWiki_hidden" value="Y" />
                <tbody> 
                    
                    <tr>
                        <th>Post Types</th>
                        <th>SubPages List</th>
                        <th>Subscription</th>
                        
                    </tr>  

                    <?php foreach ($post_types as $types) {
                        
                       if(in_array($types,$exclude,true))
                        continue; ?>
                        <tr>
                            <td>  <label><?php echo $types; ?></label> </td>
                            <td> <input type="checkbox" name="subpages[<?php echo $types; ?>]" <?php if ($subPagesOptions['subpages'][$types] == 1) { ?>checked="checked"<?php } ?> /> </td>
                            <td> <input type="checkbox" name="subscribe[<?php echo $types; ?>]" <?php if ($subscribeOptions['subscribe'][$types] == 1) { ?>checked="checked"<?php } ?> /> </td>
                        </tr>
                    <?php } ?>
                     <tr>
                    <th>Example Shortcode</th>
                    <th style="font-style:italic;">[rtwikiSubPages post_type="page"  post_id="25"]</th>
                    <th></th>
                    </tr>   
                        
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

    //var_dump($_POST['subpages']);    

    $args = array(
        'public' => true,
        '_builtin' => true,
        'hierarchical' => true,
    );
    $post_types = get_post_types($args);


    if ($_POST['rtWiki_hidden'] == 'Y') {
        //Form data sent  

        foreach ($post_types as $types) {

            /* Subpages Option Update */
            if (isset($_POST['subpages'][$types])) {
                $subPagesOptions['subpages'][$types] = 1;
            } else {
                $subPagesOptions['subpages'][$types] = 0;
            }
            update_option('rtWiki_subpages_options', $subPagesOptions);

            /* Subscribe Option Update */
            if (isset($_POST['subscribe'][$types])) {
                $subscribeOptions['subscribe'][$types] = 1;
            } else {
                $subscribeOptions['subscribe'][$types] = 0;
            }
            update_option('rtWiki_subscribe_options', $subscribeOptions);
        }
        ?>  
        <div class="updated"><p><strong><?php _e('Options saved.'); ?></strong></p></div>  
        <?php
    }
}

add_action('admin_init', 'rtwiki_save_settings');