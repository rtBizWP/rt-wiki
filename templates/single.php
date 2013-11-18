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
//var_dump($content);
if ($content == null)
    wp_redirect(home_url());
//echo $content;
?>
<?php
$contributers = getContributers();

if (!empty($contributers)) {
    ?>
    <h4>Contributers</h4>
    <ul id="contributers">
        <?php foreach ($contributers as $contributer) {
            ?>
            <li><?php echo $contributer ?> </li>
        <?php } ?>
    </ul>     
<?php
}

$childpages = getSubPages();
if (!empty($childpages)) {
    ?>
    <h4>Sub Pages</h4>
    <ul id="child-pages">
        <?php foreach ($childpages as $childpage) {
            ?>
            <li><?php echo $childpage ?> </li>
    <?php } ?>
    </ul>     
<?php
} ?>

    
       
    
get_footer();


