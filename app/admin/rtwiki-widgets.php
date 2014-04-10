<?php
/**
 * Custom Widgets for rtWiki Plugin.
 */

/**
 * rtWiki Post Contributers Widget
 */
class RtWikiContributers extends WP_Widget
{

	function __construct()
	{
		$widget_ops = array( 'classname' => 'rtWiki-contributers', 'description' => __( 'Post Contributers', 'rtCamp' ) );
		parent::__construct( 'rtWiki-contributers-widget', __( 'rtWiki: Post Contributers', 'rtCamp' ), $widget_ops );
	}

	function widget( $args, $instance )
	{
        global $rt_wiki_widget_helper;
		extract( $args, EXTR_SKIP );
		global $post;

		if ( isset( $instance['title']  ) ){
			$title = apply_filters( 'widget_title', $instance['title'] );
		} else {
			$title = apply_filters( 'widget_title', 'Contributers' );
		}

		if ( $rt_wiki_widget_helper->has_wiki_contributers( $post->ID ) ){
			echo $args[ 'before_widget' ];
			if ( isset( $title ) ){
				echo $args[ 'before_title' ] .  $title . $args[ 'after_title' ];
			}
			echo '<div class="rtwikicontributers" >';
            $rt_wiki_widget_helper->get_contributers( $post->ID );
			echo '</div>';
			echo $args[ 'after_widget' ];
		}
	}

	function update( $new_instance, $old_instance )
	{
		$instance = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : 'Contributers';

		return $instance;
	}

	function form( $instance )
	{
		if ( isset( $instance[ 'title' ] ) ){
			$title = $instance[ 'title' ];
		} else {
			$title = __( 'Contributers', 'text_domain' );
		}
		$number = isset( $instance[ 'number' ] ) ? absint( $instance[ 'number' ] ) : 1;
		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>"
				   name="<?php echo $this->get_field_name( 'title' ); ?>" type="text"
				   value="<?php echo esc_attr( $title ); ?>">
		</p>

		<?php
	}

}

/**
 * rtWiki Post SubPages List Widget
 */
class RtWikiSubPage extends WP_Widget
{

	function __construct()
	{
		$widget_ops = array( 'classname' => 'rtWiki-subPages', 'description' => __( 'Subpages', 'rtCamp' ) );
		parent::__construct( 'rtWiki-subPages-widgets', __( 'rtWiki: Subpages', 'rtCamp' ), $widget_ops );
	}

	function widget( $args, $instance )
	{
        global $rt_wiki_widget_helper,$rt_wiki_post_filtering;
		extract( $args, EXTR_SKIP );
		global $post;
		$isParent = $rt_wiki_post_filtering->if_sub_pages( $post->ID, $post->post_type );

		if ( isset( $instance['title']  ) ){
			$title = apply_filters( 'widget_title', $instance['title'] );
		} else {
			$title = apply_filters( 'widget_title', 'SubPages' );
		}

		if ( $isParent ){
			echo $args[ 'before_widget' ];
			if ( isset( $title ) ){
				echo $args[ 'before_title' ] .  $title . $args[ 'after_title' ];
			}
			echo '<div class="rtwikisubpage" >';
            $rt_wiki_widget_helper->get_subpages( $post->ID, 0, $post->post_type );
			echo '</div>';
			echo $args[ 'after_widget' ];
		}
	}

	function update( $new_instance, $old_instance )
	{
		$instance = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : 'SubPages';

		return $instance;
	}

	function form( $instance )
	{
		if ( isset( $instance[ 'title' ] ) ){
			$title = $instance[ 'title' ];
		} else {
			$title = __( 'SubPages', 'text_domain' );
		}
		$number = isset( $instance[ 'number' ] ) ? absint( $instance[ 'number' ] ) : 1;
		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>"
				   name="<?php echo $this->get_field_name( 'title' ); ?>" type="text"
				   value="<?php echo esc_attr( $title ); ?>">
		</p>

		<?php
	}

}

/**
 * rtWiki Post Taxonomies Widget
 */
class RtWikiTaxonimies extends WP_Widget
{

	function __construct()
	{
		$widget_ops = array( 'classname' => 'rtWiki-taxonomies', 'description' => __( 'Taxonomies', 'rtCamp' ) );
		parent::__construct( 'rtWiki-taxonomies-widgets', __( 'rtWiki: Taxonomies', 'rtCamp' ), $widget_ops );
	}

	function widget( $args, $instance )
	{
		extract( $args, EXTR_SKIP );
		global $post,$rt_wiki_widget_helper;


		if ( isset( $instance['title']  ) ){
			$title = apply_filters( 'widget_title', $instance['title'] );
		} else {
			$title = apply_filters( 'widget_title', 'Taxonimies' );
		}

		$out=$rt_wiki_widget_helper->wiki_custom_taxonomies( $post->ID );

		if ( isset( $out ) && $out != '' ){
			echo $args[ 'before_widget' ];
			if ( isset( $title ) ){
				echo $args[ 'before_title' ] .  $title . $args[ 'after_title' ];
			}
			//wiki_default_taxonomies($post->ID);
			echo '<div class="rtwikitaxonimies" >';
			echo $out;
			echo '</div>';
			echo $args[ 'after_widget' ];
		}
	}

	function update( $new_instance, $old_instance )
	{
		$instance = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : 'Taxonimies';

		return $instance;
	}

	function form( $instance )
	{
		if ( isset( $instance[ 'title' ] ) ){
			$title = $instance[ 'title' ];
		} else {
			$title = __( 'Taxonimies', 'text_domain' );
		}
		$number = isset( $instance[ 'number' ] ) ? absint( $instance[ 'number' ] ) : 1;
		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>"
				   name="<?php echo $this->get_field_name( 'title' ); ?>" type="text"
				   value="<?php echo esc_attr( $title ); ?>">
		</p>

		<?php
	}

}

/**
 * rtWiki Single Page Subscription Widget
 */
class RtWikiPageSubscribe extends WP_Widget
{

	function __construct()
	{
		$widget_ops = array( 'classname' => 'rtWiki-wikiPageSubscription', 'description' => __( 'Wiki Page Subscription', 'rtCamp' ) );
		parent::__construct( 'rtWiki-wikiPageSubscription-widgets', __( 'rtWiki:Wiki Page Subscription', 'rtCamp' ), $widget_ops );
	}

	function widget( $args, $instance )
	{
		extract( $args, EXTR_SKIP );
		global $post, $rt_wiki_post_filtering,$rt_wiki_subscribe;
		$subpageStatus = '';
		$singleCheck   = '';
		$subPageCheck  = '';

		if ( isset( $instance['title']  ) ){
			$title = apply_filters( 'widget_title', $instance['title'] );
		} else {
			$title = apply_filters( 'widget_title', 'Subscribe' );
		}

		$parentStatus = false;
		if ( $rt_wiki_post_filtering->get_permission( $post->ID, get_current_user_id(), 0 ) ){
			echo $args[ 'before_widget' ];
			if ( isset( $title ) ){
				echo $args[ 'before_title' ] .  $title . $args[ 'after_title' ];
			}
			echo '<div class="rtwikipagesubscribe" >';
			if ( $rt_wiki_subscribe->is_post_subscribe_cur_user( get_current_user_id() ) == true ){
				$singleCheck = 'checked';
			} else {
				$singleCheck = '';
			}
			$isParent = $rt_wiki_post_filtering->if_sub_pages( $post->ID, $post->post_type );

			if ( $isParent == true ){
				if ( $rt_wiki_subscribe->rt_wiki_subpages_check( $post->ID, true, $post->post_type ) == true ){
					$parentStatus = true;
					// Check if user subscribe for sub page
					if ( $rt_wiki_subscribe->is_subpost_subscribe( $post, get_current_user_id() ) == true ){
						$subpageStatus = 1;
					} else {
						$subpageStatus = 0;
					}
				}
			}

			if ( $subpageStatus == 1 ){
				$subPageCheck = 'checked';
			} else {
				$subPageCheck = '';
			}
			echo '<form id="user-subscribe" method="post" action="?PageSubscribe=1">
                <label><input type="checkbox" name="single_subscribe" value="current"  ' . $singleCheck . ' >&nbspSubscribe to this page </label>';
			if ( $parentStatus == true ){
				echo '<label><input type="checkbox" name="subPage_subscribe" value="subpage"  ' . $subPageCheck . ' >&nbspSubscribe to this page and  Sub Pages</label>';
			}
			echo '<input type="hidden" name=post-type value=' . $post->post_type . ' /><input type="submit" class="button" name=post-update-subscribe" value="Submit" >
                <input type="hidden" name="update-postId"  value=' . $post->ID . '>
            </form>';
			echo '</div>';
			echo $args[ 'after_widget' ];
		}
	}

	function update( $new_instance, $old_instance )
	{
		$instance = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : 'Subscribe';

		return $instance;
	}

	function form( $instance )
	{
		if ( isset( $instance[ 'title' ] ) ){
			$title = $instance[ 'title' ];
		} else {
			$title = __( 'Subscribe', 'text_domain' );
		}
		$number = isset( $instance[ 'number' ] ) ? absint( $instance[ 'number' ] ) : 1;
		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>"
				   name="<?php echo $this->get_field_name( 'title' ); ?>" type="text"
				   value="<?php echo esc_attr( $title ); ?>">
		</p>

		<?php
	}

}
