<?php
/*
 * 
 * The Template for displaying all single posts.
 *
 */

get_header();
?>

<div id="primary" class="content-area">
    <div id="content" class="site-content" role="main">

        <?php
        /* The loop */
        
        global $post;
        $postType = $post->post_type;
        ?>
            <h2> <?php //echo the_title();  ?></h2>
            <div id="content-wrapper">
                <?php
            
            if (getPermission($post->ID) == true) { ?>
                <h2><?php echo the_title(); ?></h2>
                <p><?php echo the_content(); ?></p>
                             
               <?php  } else { ?>
              <p> <?php echo 'Not Enough Rights to View The Content'; ?>  </p>
             
           <?php } ?>
            </div>  
    </div><!-- #content -->
</div><!-- #primary -->


<?php if ($postType == 'wiki') { ?>
    <div id="secondary" class="sidebar-container" role="complementary">

        <div class="widget-area">

    <?php dynamic_sidebar('rt-wiki-sidebar'); ?>

        </div> 

    </div> 
    <?php
} else {
    get_sidebar();
}

get_footer();
?>