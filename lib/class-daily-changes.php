<?php

/**
 * Description of class-daily-changes
 *
 * @author prannoy
 */
if (defined('WP_CLI') && WP_CLI) {

    class daily_changes extends WP_CLI_COMMAND {

        function changes() {
           // global $post;
            query_posts('post_type=wiki');
            $terms = get_terms('user-group', array('hide_empty' => false));

            if (have_posts())
                while (have_posts()) : the_post();
                    $postID = get_the_ID;
                    $access_rights = get_post_meta(get_the_ID, 'access_rights', true);
                    $term_meta = get_option("user-group-meta");
                    foreach ($terms as $term) {
                        if ($access_rights[$term]['w'] == '1' || $access_rights[$term]['r'] == '1') {
                            $termId=$term->term_id;
                            $email=$term_meta[$termId]['email_address'];
                            post_changes_send_mail($postID,$email);
                        }
                    }

                endwhile;
            wp_reset_query();
        }

    }

    WP_CLI::add_command('rtwiki', 'daily_changes');
}
