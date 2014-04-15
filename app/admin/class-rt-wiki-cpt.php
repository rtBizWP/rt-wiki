<?php
/**
 * Don't load this file directly!
 */
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * The RtWikiCPT Class. Creates A wiki CPT, along with permissions metabox
 * Adds email address custom field to user-group taxonomy.
 *
 * @author     Dipesh
 */
if ( ! class_exists( 'Rt_Wiki_CPT' ) ){

    /**
     * Class Rt_Wiki_CPT
     */
    class Rt_Wiki_CPT
	{
        /**
         * Object initialization
         */
        public function __construct()
		{
			$this->hook();
		}

        /**
         * Apply Hook/Filter for Wiki's
         */
        function hook(){
            add_action( 'init', array( $this, 'create_wiki' ) );
            add_action( 'admin_init', array( $this, 'wiki_permission_metabox' ) );
            add_action( 'save_post', array( $this, 'rtp_wiki_permission_save' ) );
        }

		/**
		 * Creates wiki named CPT.
		 */
		function create_wiki()
		{
			$rtwiki_settings = '';
			$rtwiki_custom   = '';
			if ( is_multisite() ){
				$rtwiki_settings = get_site_option( 'rtwiki_settings', array() );
				$rtwiki_custom   = get_site_option( 'rtwiki_custom', array() );
			} else {
				$rtwiki_settings = get_option( 'rtwiki_settings', array() );
				$rtwiki_custom   = get_option( 'rtwiki_custom', array() );
			}

			$post_name = array();
			if ( isset( $rtwiki_settings[ 'custom_wiki' ] ) && ( 'y' == $rtwiki_settings[ 'custom_wiki' ] ) && ( count( $rtwiki_custom ) > 0 ) ){
				$post_name = $rtwiki_custom;
			}

			if ( is_array( $post_name ) && ( count( $post_name ) > 0 ) ){
				foreach ( $post_name as $name ) {
					$slug  = $name[ 'slug' ];
					$label = ucwords( $name[ 'label' ] );
					$labels = array(
						'name' => __( $label, 'post type general name', 'rtCamp' ),
						'singular_name' => __( $label, 'post type singular name', 'rtCamp' ),
						'add_new' => __( 'Add New', $label, 'rtCamp' ),
						'add_new_item' => __( 'Add New ' . $label, 'rtCamp' ),
						'edit' => __( 'Edit', $label, 'rtCamp' ),
						'edit_item' => __( 'Edit ' . $label, 'rtCamp' ),
						'new_item' => __( 'New ' . $label, 'rtCamp' ),
						'view' => __( 'View', $label, 'rtCamp' ),
						'view_item' => __( 'View ' . $label, 'rtCamp' ),
						'search_items' => __( 'Search ' . $label, 'rtCamp' ),
						'not_found' => __( 'No ' . $label . ' found', 'rtCamp' ),
						'not_found_in_trash' => __( 'No ' . $label . ' found in Trash', 'rtCamp' ),
						'all_items' => __( 'All ' . $label, 'rtCamp' ),
						'parent' => __( 'Parent ' . $label, 'rtCamp' ),
						'menu_name' => __( $label, 'rtCamp' ), );
					$args = array(
						'labels' => $labels,
						'description' => __( $label, 'rtCamp' ),
						'public' => true,
						'publicly_queryable' => true,
						'show_ui' => true,
						'show_in_menu' => true,
						'query_var' => true,
						'rewrite' => array( 'slug' => $slug ),
							'capability_type' => 'wiki',
						'has_archive' => true,
						'hierarchical' => true,
						'menu_position' => 33,
						'supports' => array(
							'title',
							'editor',
							'thumbnail',
							'revisions',
							'page-attributes',
							'excerpt',
							'comments', ),
						'_builtin' => false,
						'_edit_link' => 'post.php?post=%d',
						'menu_icon' => true,
						'can_export' => true,
						'show_in_nav_menus' => true,
						'show_in_admin_bar' => true, );
					register_post_type( $slug, $args );
				}
			}
		}

		/**
		 * Add User group and permission type metabox
		 */
		function wiki_permission_metabox()
		{
			global $current_user;
			$supported_posts = rtwiki_get_supported_attribute();
			if ( is_array( $supported_posts ) && ! empty( $supported_posts ) && current_user_can( rt_biz_get_access_role_cap( RT_WIKI_TEXT_DOMAIN, 'admin' ) ) ){
				foreach ( $supported_posts as $posts )
					add_meta_box( $posts . '_post_access', 'Permissions', array( $this, 'display_wiki_post_access_metabox' ), $posts, 'normal', 'high' );
			}
		}

		/**
		 * Permission And Group MetaBox for wiki CPT
		 *
		 * @param type $post
		 */
		function display_wiki_post_access_metabox( $post )
		{

			if( $post->post_status=="auto-draft" ){
				echo "<div>Please publish this page to view permissions.</div>";
				return;
			}

			if( $post->post_parent!= 0 ){
				$base_parent = get_post_meta( $post->ID, 'base_parent', true );
				$base_parent_meta = get_post( $base_parent );
				echo "<p> Child page permissions depend on its base parent page ( " . $base_parent_meta->post_title . " )</p><p>";
				echo "<a class='post-edit-link' target='_blank' href='" . get_edit_post_link( $base_parent ) . "'>Click here</a>";
				echo ' to change permissions of ' . $base_parent_meta->post_title . '</p>';
				return;
			}
			$access_rights = get_post_meta( $post->ID, 'access_rights', true );
			$disabled      = '';

			if ( ( isset( $access_rights[ 'public' ] ) && ( 1 == $access_rights[ 'public' ] ) ) || ! isset( $access_rights ) || empty( $access_rights ) ) $disabled = 'disabled="disabled"';
			?>
			<table>
				<tbody>
				<tr>
					<th>Groups</th>
					<th>No Access</th>
					<th>Read</th>
					<th>Write</th>
				</tr>

				<tr>
					<td>All</td>
					<td><input type="radio" onclick="uncheckAllGroup(this,'na')" <?php echo esc_html( $disabled ); ?> class="rtwiki_all_na rtwiki_na"
							   name="access_rights[all]"
							   <?php if ( isset( $access_rights[ 'all' ] ) && ( $access_rights[ 'all' ] == 0 ) ) { ?>checked="checked"<?php } ?>
							   value="0"/></td>
					<td><input type="radio" onclick="uncheckAllGroup(this,'r')" class="rtwiki_all_r rtwiki_r" name="access_rights[all]"
					           <?php if ( isset( $access_rights[ 'all' ] ) && ( $access_rights[ 'all' ] == 1 ) ) { ?>checked="checked"<?php } ?>
							   value="1"/></td>
					<td><input type="radio" onclick="uncheckAllGroup(this,'w')" class="rtwiki_all_w rtwiki_w" name="access_rights[all]"
					           <?php if ( isset( $access_rights[ 'all' ] ) && ( $access_rights[ 'all' ] == 2 ) ) { ?>checked="checked"<?php } ?>
							   value="2"/></td>
				</tr>

			<?php
			$args = array( 'orderby' => 'asc', 'hide_empty' => false );
			$terms = get_terms( 'user-group', $args );
			foreach ( $terms as $term ) {
				$groupSlug = $term->slug;
					?>
					<tr>
						<td><a href="users.php?user-group=<?php echo esc_html( $groupSlug ) ?>" title="<?php echo esc_html( $term->name ) ?>"><?php echo esc_html( $term->name ) ?></a> (<?php echo esc_html( $term->count ) ?>)</td>
						<td><input type="radio" onclick="uncheckAll(this,'na')" class="case_na rtwiki_na" <?php echo esc_html( $disabled ); ?> id="na_<?php echo esc_html( $groupSlug ) ?>"
								   name="access_rights[<?php echo esc_html( $groupSlug ) ?>]"
								   <?php if ( isset( $access_rights[ $groupSlug ] ) && ( $access_rights[ $groupSlug ] == 0 ) ) { ?>checked="checked"<?php } ?>
								   value="0"/></td>
						<td><input type="radio" onclick="uncheckAll(this,'r')" class="case_r rtwiki_r" id="r_<?php echo esc_html( $groupSlug ) ?>"
								   name="access_rights[<?php echo esc_html( $groupSlug ) ?>]"
								   <?php if ( ( '' != $disabled ) || ( isset( $access_rights[ $groupSlug ] ) && ( $access_rights[ $groupSlug ] == 1 ) ) ) { ?>checked="checked"<?php } ?>
								   value="1"/></td>
						<td><input type="radio" onclick="uncheckAll(this,'w')" class="case_w rtwiki_w" id="w_<?php echo esc_html( $groupSlug ) ?>"
								   name="access_rights[<?php echo esc_html( $groupSlug ) ?>]"
								   <?php if ( isset( $access_rights[ $groupSlug ] ) && ( $access_rights[ $groupSlug ] == 2 ) ) { ?>checked="checked"<?php } ?>
								   value="2"/></td>
					</tr>
				<?php } ?>
				</tbody>
			</table>

			<table>
				<tbody>
				<tr>
					<th colspan='2'><h4>Permission for public level</h4></th>
				</tr>
				<tr>
					<td>Public</td>
					<td colspan='2'><input type="checkbox" onclick='if (this.checked) {
                        jQuery(".rtwiki_na").prop("checked", false);
                        jQuery(".rtwiki_w").prop("checked", false);
                        jQuery(".rtwiki_r").prop("checked", true);
                        jQuery(".rtwiki_na").prop("disabled", true);
                    } else {
                        jQuery(".rtwiki_r").prop("checked", false);
                        jQuery(".rtwiki_na").prop("disabled", false);
                        jQuery(".rtwiki_na").prop("checked", true);
                    }' id="rtwiki_public_na" class="rtwiki_pub"
										   name="access_rights[public]" <?php if ( ( isset( $access_rights[ 'public' ] ) && ( 1 == $access_rights[ 'public' ] ) ) || ! isset( $access_rights ) || empty( $access_rights ) ){ ?> checked="checked" <?php } ?>
										   value='1'/></td>
				</tr>
				</tbody>

			</table>
		<?php
		}

		/**
		 * Save user and its permission as meta value
		 *
		 * @param type $post
		 */
		function rtp_wiki_permission_save( $post )
		{

			if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;

			if ( ! isset( $_REQUEST[ 'post_type' ] ) ){
				$_REQUEST[ 'post_type' ] = 'post';
			}

			$post_info = get_post( $post );

			if ( isset( $_REQUEST['parent_id'] ) && $_REQUEST['parent_id'] == 0 ){

				$supported_posts = rtwiki_get_supported_attribute();
				if ( in_array( $_POST[ 'post_type' ], $supported_posts, true ) ){
					if ( ! current_user_can( 'edit_wiki', $post ) ){
						return;
					} else {
						$args  = array( 'orderby' => 'asc', 'hide_empty' => false );
						$terms = get_terms( 'user-group', $args );

						if( ( isset( $_POST[ 'access_rights' ][ 'public' ] ) && $_POST[ 'access_rights' ][ 'public' ]== 1 ) || !isset( $_POST[ 'access_rights' ] ) ){
							foreach ( $terms as $term ) {
								$access_rights[$term->slug]=1;
							}
							$access_rights[ 'public' ]=1;
						}elseif ( isset( $_POST[ 'access_rights' ][ 'all' ] ) ){
							foreach ( $terms as $term ) {
								$access_rights[$term->slug]=$_POST[ 'access_rights' ][ 'all' ];
							}
							$access_rights[ 'all' ]=$_POST[ 'access_rights' ][ 'all' ];
						}else{
							foreach ( $terms as $term ) {
								if( isset( $_POST[ 'access_rights' ][ $term->slug ] ) ){
									$access_rights[$term->slug]=$_POST[ 'access_rights' ][ $term->slug ];
								}else{
									$access_rights[$term->slug]=1;
								}
							}
						}
						update_post_meta( $post, 'access_rights', $access_rights );
						update_post_meta( $post, 'base_parent', $post );
					}
				}
			}else{
				update_post_meta( $post, 'access_rights', null );
				$base_parent=get_post_meta( $post_info->post_parent, 'base_parent', true );
				update_post_meta( $post, 'base_parent', $base_parent );
			}
		}
	}
}















