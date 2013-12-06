<?php
/*
 * 
 * The Template for displaying all single posts.
 *
 */

get_header();
global $post;
?>

<h2><?php echo $post->post_title ?></h2>
<?php
$content = single_post_filtering();
echo $content;

/* Wiki Sidebar */
dynamic_sidebar('rtWiki Widget Area');


/* Footer */
get_footer();
