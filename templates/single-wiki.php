<?php
/**
 * The Template for displaying all single posts.
 *
 * @package WordPress
 * @subpackage Twenty_Thirteen
 * @since Twenty Thirteen 1.0
 */

get_header();
global $post;

?>
<h2><?php echo $post->post_title ?></h2>
<?php
$content = single_post_filtering();
echo $content;


dynamic_sidebar('rtWiki');?>
<?php
get_footer();
