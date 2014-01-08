<?php

/**
 * Description of class-daily-changes
 * Use  "wp rtwiki wikiChanges" and "wp rtwiki nonWikiChanges" to run the CLI command.
 * @author prannoy
 */
if (defined('WP_CLI') && WP_CLI) {

    class daily_changes extends WP_CLI_COMMAND {

 /* Function for sending email to User Group */
        
        function wikiGroup() {

            // query_posts('post_type=wiki');
            $args = array('hierarchical' => true);
            $post_types = get_post_types($args);
            $wp_query = new WP_Query(array('post_type' => 'wiki', 'posts_per_page' => -1));

            $terms = get_terms('user-group', array('hide_empty' => true));

            if ($wp_query->have_posts()) {
                while ($wp_query->have_posts()) {
                    $wp_query->the_post();

                    $postID = get_the_ID();
                    //$postObject=get_post($postID);
                    $access_rights = get_post_meta($postID, 'access_rights', true);

                    if ($access_rights != null) {

                        $term_meta = get_option("user-group-meta");

                        foreach ($terms as $term) {

                            if (isset($access_rights[$term->slug]) && ( $access_rights[$term->slug]['w'] == 1 || $access_rights[$term->slug]['r'] == 1)) {
                                $termId = $term->term_id;
                                $email = $term_meta[$termId]['email_address'];

                                post_changes_send_mail($postID, $email, strtoupper($term->slug));
                            }
                        }
                    }
                }
            }
            wp_reset_query();
        }

        
/* Function for sending email to Wiki Post Subscribe Users */  
        
        function wikiUser()
        {
           $wp_query = new WP_Query(array('post_type' => 'wiki', 'posts_per_page' => -1));
            if ($wp_query->have_posts()) {
                while ($wp_query->have_posts()) {
                    $wp_query->the_post();

                    $postID = get_the_ID();
                   
                    $subscribersList = get_post_meta($postID, 'subcribers_list', true);              
                   
                    foreach ($subscribersList as $subscribers) {
                       
                        $user_info = get_userdata($subscribers);
                        nonWiki_page_changes_send_mail($postID, $user_info->user_email);
                    }
                }
            }
            wp_reset_query();
            
            
            
        }

/* Function for sending email to Non Wiki Post Subscribe Users */        
        
        function nonWikiUsers() {
            $args = array('hierarchical' => true);
            $post_types = get_post_types($args);
            $wp_query = new WP_Query(array('post_type' => $post_types, 'posts_per_page' => -1));
            if ($wp_query->have_posts()) {
                while ($wp_query->have_posts()) {
                    $wp_query->the_post();

                    $postID = get_the_ID();
                    $subscribersList = get_post_meta($postID, 'subcribers_list', true);              
                  
                    foreach ($subscribersList as $subscribers) {
                       
                        $user_info = get_userdata($subscribers);
                        nonWiki_page_changes_send_mail($postID, $user_info->user_email);
                    }
                }
            }
            wp_reset_query();
        }

    }

    WP_CLI::add_command('rtwiki', 'daily_changes');
}
