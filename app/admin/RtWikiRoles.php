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
if ( ! class_exists( 'RtWikiRoles' ) ){

	class RtWikiRoles
	{

		public $global_caps = array( 'edit_wiki' => 'edit_wiki', 'edit_others_wiki' => 'edit_others_wiki', 'publish_wiki' => 'publish_wiki', 'read_wiki' => 'read_wiki', 'read_private_wiki' => 'read_private_wiki', 'delete_wiki' => 'delete_wiki', 'edit_published_wiki' => 'edit_published_wiki', 'delete_published_wiki' => 'delete_published_wiki', 'delete_others_wiki' => 'delete_others_wiki', );

		var $rtwikiroles;

		public function __construct()
		{

			$this->rtwikiroles = array(
				array(
					'name' => 'Wiki Admin',
					'label' => 'rtwikiadmin', ),
				array(
					'name' => 'Wiki Editor',
					'label' => 'rtwikieditor', ),
				array(
					'name' => 'Wiki Author',
					'label' => 'rtwikiauthor', ),
				array(
					'name' => 'Wiki Contributor',
					'label' => 'rtwikicontributor', ),
				array(
					'name' => 'Wiki Subscriber',
					'label' => 'rtwikisubscriber', ), );

			$this->register_roles();

			add_action( 'edit_user_profile', array( $this, 'add_access_profile_fields' ), 1 );

			add_action( 'show_user_profile', array( $this, 'add_access_profile_fields' ), 1 );

			add_action( 'profile_update', array( $this, 'update_access_profile_fields' ), 10, 2 );

			add_filter( 'editable_roles', array( $this, 'remove_wp_crm_roles' ) );

			add_action( 'restrict_manage_users', array( $this, 'wiki_user_role_bulk_dropdown' ) );

			add_action( 'load-users.php', array( $this, 'wiki_user_role_bulk_change'   ) );

		}

		function remove_wp_crm_roles( $roles )
		{

			unset( $roles[ 'rt_wp_crm_manager' ] );


			foreach ( $this->rtwikiroles as $rtwikirole ) {

				unset( $roles[ strtolower( str_replace( '-', '_', sanitize_title( $rtwikirole[ 'label' ] ) ) ) ] );

				// Add admin & user roles
			}

			return $roles;
		}

		function register_roles()
		{

			foreach ( $this->rtwikiroles as $rtwikirole ) {

				if ( $rtwikirole[ 'label' ] == 'rtwikiadmin' || $rtwikirole[ 'label' ] == 'rtwikieditor' || $rtwikirole[ 'label' ] == 'rtwikiauthor' ){

					$caps = array(
						$this->global_caps[ 'edit_wiki' ] => true,
						$this->global_caps[ 'edit_others_wiki' ] => true,
						$this->global_caps[ 'publish_wiki' ] => true,
						$this->global_caps[ 'read_wiki' ] => true,
						$this->global_caps[ 'read_private_wiki' ] => true,
						$this->global_caps[ 'delete_wiki' ] => true,
						$this->global_caps[ 'edit_published_wiki' ] => true,
						$this->global_caps[ 'delete_published_wiki' ] => true,
						$this->global_caps[ 'delete_others_wiki' ] => true, );

				} else if ( $rtwikirole[ 'label' ] == 'rtwikicontributor' ){

					$caps = array(
						$this->global_caps[ 'edit_wiki' ] => true,
						$this->global_caps[ 'edit_others_wiki' ] => false,
						$this->global_caps[ 'publish_wiki' ] => false,
						$this->global_caps[ 'read_wiki' ] => true,
						$this->global_caps[ 'read_private_wiki' ] => false,
						$this->global_caps[ 'delete_wiki' ] => true,
						$this->global_caps[ 'edit_published_wiki' ] => false,
						$this->global_caps[ 'delete_published_wiki' ] => false,
						$this->global_caps[ 'delete_others_wiki' ] => false, );

				} else if ( $rtwikirole[ 'label' ] == 'rtwikisubscriber' ){

					$caps = array(
						$this->global_caps[ 'edit_wiki' ] => false,
						$this->global_caps[ 'edit_others_wiki' ] => false,
						$this->global_caps[ 'publish_wiki' ] => false,
						$this->global_caps[ 'read_wiki' ] => true,
						$this->global_caps[ 'read_private_wiki' ] => false,
						$this->global_caps[ 'delete_wiki' ] => false,
						$this->global_caps[ 'edit_published_wiki' ] => false,
						$this->global_caps[ 'delete_published_wiki' ] => false,
						$this->global_caps[ 'delete_others_wiki' ] => false, );

				}

				$label = strtolower( str_replace( '-', '_', sanitize_title( $rtwikirole[ 'label' ] ) ) );
				//remove_role( $label );
				$role = get_role( $label );
				if ( empty( $role ) ){
					add_role( $label, __( ucfirst( $rtwikirole[ 'name' ] ) ), $caps );
				}
			}
		}

		function add_access_profile_fields( $user )
		{

			$current_user = new WP_User( get_current_user_id() );

			if ( $current_user->has_cap( 'create_users' ) ){

				if ( in_array( 'rt_wp_crm_manager', $user->roles ) ){

					$selected = 'selected="selected"';
				} else {

					$selected = '';
				}
				?>

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

			// Get the current user ID
			$current_user_id = (int) get_current_user_id();

			// Run through user ids
			foreach ( (array) $_REQUEST['users'] as $user_id ) {
				$user_id = (int) $user_id;

				// Don't let a user change their own role
				if ( $user_id === $current_user_id )
					continue;

				$this->update_access_profile_fields( $user_id, null );

			}
		}



	}

}

