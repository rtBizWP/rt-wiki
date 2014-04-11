<?php
/**
 * Don't load this file directly!
 */
if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Description of RtWikiRoles
 *
 * @author Dipesh
 */
if ( ! class_exists( 'Rt_Wiki_Roles' ) ){

	class Rt_Wiki_Roles
	{
        var $wiki_cap_name = 'wiki';

		public $global_caps = array(
            'manage_wp_wiki' => 'manage_wp_wiki',
            'manage_attributes' => 'manage_attributes',
            'manage_rtwiki_terms' => 'manage_rtwiki_terms',
            'edit_rtwiki_terms' => 'edit_rtwiki_terms',
            'delete_rtwiki_terms' => 'delete_rtwiki_terms',
            'assign_rtwiki_terms' => 'assign_rtwiki_terms',
        );

		var $rtwikiroles;

		public function __construct()
		{

			$this->rtwikiroles = array(
				array(
					'name' => 'Wiki Moderator',
					'label' => 'rtwikimoderator', ),
				array(
					'name' => 'Wiki Writer',
					'label' => 'rtwikiwriter', ), );

			$this->register_roles();

			add_action( 'edit_user_profile', array( $this, 'add_access_profile_fields' ), 1 );
			add_action( 'show_user_profile', array( $this, 'add_access_profile_fields' ), 1 );
			add_action( 'profile_update', array( $this, 'update_access_profile_fields' ), 10, 2 );
			add_filter( 'editable_roles', array( $this, 'remove_wp_wiki_roles' ) );
			add_action( 'restrict_manage_users', array( $this, 'wiki_user_role_bulk_dropdown' ) );
			add_action( 'load-users.php', array( $this, 'wiki_user_role_bulk_change'   ) );

		}

		function remove_wp_wiki_roles( $roles )
		{

			foreach ( $this->rtwikiroles as $rtwikirole ) {

				unset( $roles[ strtolower( str_replace( '-', '_', sanitize_title( $rtwikirole[ 'label' ] ) ) ) ] );

				// Add admin & user roles
			}

			return $roles;
		}

		function register_roles()
		{

			foreach ( $this->rtwikiroles as $rtwikirole ) {

				if ( $rtwikirole[ 'label' ] == 'rtwikimoderator' ){

					$caps = array(
                        $this->global_caps['manage_wp_wiki'] => true,
                        $this->global_caps['manage_attributes'] => true,
						"edit_{$this->wiki_cap_name}" => true,
						"read_{$this->wiki_cap_name}" => true,
						"delete_{$this->wiki_cap_name}" => true,
						"edit_{$this->wiki_cap_name}s" => true,
						"edit_others_{$this->wiki_cap_name}s" => true,
						"publish_{$this->wiki_cap_name}s" => true,
						"read_private_{$this->wiki_cap_name}s" => true,
						"delete_{$this->wiki_cap_name}s" => true,
						"delete_private_{$this->wiki_cap_name}s" => true,
						"delete_published_{$this->wiki_cap_name}s" => true,
						"delete_others_{$this->wiki_cap_name}s" => true,
						"edit_private_{$this->wiki_cap_name}s" => true,
						"edit_published_{$this->wiki_cap_name}s" => true,
                        $this->global_caps['manage_rtwiki_terms'] => true,
                        $this->global_caps['edit_rtwiki_terms'] => true,
                        $this->global_caps['delete_rtwiki_terms'] => true,
                        $this->global_caps['assign_rtwiki_terms'] => true,
					);

				}else if( $rtwikirole[ 'label' ] == 'rtwikiwriter' ){
					$caps = array(
						"edit_{$this->wiki_cap_name}" => true,
						"read_{$this->wiki_cap_name}" => true,
						"delete_{$this->wiki_cap_name}" => true,
						"edit_{$this->wiki_cap_name}s" => true,
						"edit_others_{$this->wiki_cap_name}s" => true,
						"publish_{$this->wiki_cap_name}s" => true,
						"read_private_{$this->wiki_cap_name}s" => true,
						"delete_{$this->wiki_cap_name}s" => false,
						"delete_private_{$this->wiki_cap_name}s" => false,
						"delete_published_{$this->wiki_cap_name}s" => false,
						"delete_others_{$this->wiki_cap_name}s" => false,
						"edit_private_{$this->wiki_cap_name}s" => true,
						"edit_published_{$this->wiki_cap_name}s" => true,
					);
				}
				$label = strtolower( str_replace( '-', '_', sanitize_title( $rtwikirole[ 'label' ] ) ) );
				$role = get_role( $label );
				if ( empty( $role ) ){
					add_role( $label, __( ucfirst( $rtwikirole[ 'name' ] ) ), $caps );
				}

			}


			if ( isset( $_REQUEST['rt_wp_wiki_reset_roles'] ) && ! empty( $_REQUEST['rt_wp_wiki_reset_roles'] ) ) {
				/* rtwiki old role */
				$users = get_users( array( 'role' => 'rtwikiadmin' ) );
				foreach ( $users as $user ) {
					$u_obj = new WP_User( $user );
					$u_obj->remove_role( 'rtwikiadmin' );
				}
				$users = get_users( array( 'role' => 'rtwikieditor' ) );
				foreach ( $users as $user ) {
					$u_obj = new WP_User( $user );
					$u_obj->remove_role( 'rtwikieditor' );
				}
				$users = get_users( array( 'role' => 'rtwikiauthor' ) );
				foreach ( $users as $user ) {
					$u_obj = new WP_User( $user );
					$u_obj->remove_role( 'rtwikiauthor' );
				}
				$users = get_users( array( 'role' => 'rtwikicontributor' ) );
				foreach ( $users as $user ) {
					$u_obj = new WP_User( $user );
					$u_obj->remove_role( 'rtwikicontributor' );
				}
				$users = get_users( array( 'role' => 'rtwikisubscriber' ) );
				foreach ( $users as $user ) {
					$u_obj = new WP_User( $user );
					$u_obj->remove_role( 'rtwikisubscriber' );
				}
				remove_role( 'rtwikiadmin' );
				remove_role( 'rtwikieditor' );
				remove_role( 'rtwikiauthor' );
				remove_role( 'rtwikicontributor' );
				remove_role( 'rtwikisubscriber' );

				/* rtwiki new role */
				$users = get_users( array( 'role' => 'rtwikimoderator' ) );
				foreach ( $users as $user ) {
					$u_obj = new WP_User( $user );
					$u_obj->remove_role( 'rtwikimoderator' );
					$u_obj->add_role( 'rtwikimoderator' );
				}

				$users = get_users( array( 'role' => 'rtwikiwriter' ) );
				foreach ( $users as $user ) {
					$u_obj = new WP_User( $user );
					$u_obj->remove_role( 'rtwikiwriter' );
					$u_obj->add_role( 'rtwikiwriter' );
				}
			}
		}

		function add_access_profile_fields( $user )
		{

			$current_user = new WP_User( get_current_user_id() );

			if ( $current_user->has_cap( 'create_users' ) ){

				?>
				<h3 id="wordpress-rtwiki">rtWiki</h3>
				<a href="<?php echo add_query_arg( 'rt_wp_wiki_reset_roles', true, $_SERVER['REQUEST_URI'] ); ?>"><?php _e('Reset Roles'); ?></a>
				<table class="form-table">

					<tbody>

					<tr>

						<th><label
								for="rt_wiki_role"><?php _e( 'RtWiki Role' ); ?></label>
						</th>

						<td>

							<select id="rtwiki_role" name="rt_wiki_role">

								<option value="no_role"><?php _e( 'No Role' ); ?></option>

				<?php
				foreach ( $this->rtwikiroles as $rtwikirole ) {
					if ( in_array( strtolower( str_replace( '-', '_', sanitize_title( $rtwikirole[ 'label' ] ) ) ), $user->roles ) ){
						$selected = 'selected="selected"';
					} else {
						$selected = '';
					}
					?>
									<option value="<?php echo strtolower( str_replace( '-', '_', sanitize_title( $rtwikirole[ 'label' ] ) ) ); ?>" <?php echo $selected; ?>><?php _e(  $rtwikirole[ 'name' ] ); ?></option>

				<?php } ?>
							</select>

						</td>

					</tr>

					</tbody>

				</table>

			<?php
			}
		}

		function update_access_profile_fields( $user_id, $old_data )
		{
			$role_cnt = 0;

			$role_flags[ ] = array();

			if ( current_user_can( 'create_users' ) ){

				$user = get_user_by( 'id', $user_id );

				foreach ( $this->rtwikiroles as $rtwikirole ) {

					if ( isset( $_REQUEST[ 'rt_wiki_role' ] ) ){

						switch ( $_REQUEST[ 'rt_wiki_role' ] ) {

							case strtolower( str_replace( '-', '_', sanitize_title( $rtwikirole[ 'label' ] ) ) ):

								if ( ! in_array( strtolower( str_replace( '-', '_', sanitize_title( $rtwikirole[ 'label' ] ) ) ), $user->roles ) ){

									$user->add_role( strtolower( str_replace( '-', '_', sanitize_title( $rtwikirole[ 'label' ] ) ) ) );
								}

								$role_flags[ $role_cnt ++ ] = true;

								break;

							default:

								if ( in_array( strtolower( str_replace( '-', '_', sanitize_title( $rtwikirole[ 'label' ] ) ) ), $user->roles ) ){

									$user->remove_role( strtolower( str_replace( '-', '_', sanitize_title( $rtwikirole[ 'label' ] ) ) ) );
								}

								$role_flags[ $role_cnt ++ ] = false;

								break;
						}
					}
				}


				$rtwiki_user = false;

				foreach ( $role_flags as $flag ) {

					if ( $flag == true ){

						$rtwiki_user = true;

						break;
					}
				}

				if ( $rtwiki_user == true ){

					update_user_meta( $user->id, 'rt_wiki_user', 'yes' );
				} else {

					update_user_meta( $user->id, 'rt_wiki_user', '' );
				}
			}
		}

		function wiki_user_role_bulk_dropdown(){
			// if current user cannot promote users
			if ( ! current_user_can( 'promote_users' ) )
				return;
			?>

			<label class="screen-reader-text" for="wiki-new-role"><?php esc_html_e( 'Change wiki role;', 'rtcamp' ) ?></label>
			<select name="rt_wiki_role" id="rt_wiki_role" style="display:inline-block; float:none;">
			<option value="no_role"><?php _e( 'Change wiki role' ); ?></option>
			<?php foreach ( $this->rtwikiroles as $rtwikirole ) : ?>
				<option value="<?php echo strtolower( str_replace( '-', '_', sanitize_title( $rtwikirole[ 'label' ] ) ) ); ?>" ><?php _e(  $rtwikirole[ 'name' ] ); ?></option>
			<?php endforeach; ?>

			</select><?php submit_button( __( 'Change', 'rtcamp' ), 'secondary', 'wiki-change-role', false );
		}

		public function wiki_user_role_bulk_change() {

			// if current user cannot promote users
			if ( ! current_user_can( 'promote_users' ) )
				return;
			// if no users specified
			if ( empty( $_REQUEST['users'] ) )
				return;
			// if this isn't a wiki action
			if ( empty( $_REQUEST['rt_wiki_role'] ) || empty( $_REQUEST['wiki-change-role'] ) )
				return;

			// Run through user ids
			foreach ( (array) $_REQUEST['users'] as $user_id ) {
				$user_id = (int) $user_id;

				$this->update_access_profile_fields( $user_id, null );

			}
		}
	}
}

