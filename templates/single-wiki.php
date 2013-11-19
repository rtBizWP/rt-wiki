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
?>
<h4>Subscribe For Updates </h4>
<?php if (checkSubscribe() == true) { ?>

    <p>You are Subscribed to this Page. </p>
<?php } else { ?>
    <form id="user-subscribe" method="post" action="?subscribe=1">
        <input type="submit" name=post-update-subscribe" value="Subscribe" >
        <input type="hidden" name="update-postId"  value="<?php echo $post->ID ?>">
    </form>

    <?php
}


$isParent = ifSubPages($post->ID);
if ($isParent == true) {
    ?>

    <h4>Subscribe For All Pages </h4>   
    <form id="user-all-subscribe" method="post" action="?allSubscribe=1">
        <input type="submit" name=post-update-subscribe" value="Subscribe To all subpages" >
        <input type="hidden" name="update-all-postId"  value="<?php echo $post->ID ?>">
    </form>
    <?php
}


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
?>
<h4>Sub Pages</h4>
<?php getSubPages($post->ID, 0); ?>  

<?php
get_footer();
