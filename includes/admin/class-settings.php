<?php

RCPGA_Settings::get_instance();
class RCPGA_Settings {

	/**
	 * @var
	 */
	protected static $_instance;

	/**
	 * Only make one instance of the RCPGA_Settings
	 *
	 * @return RCPGA_Settings
	 */
	public static function get_instance() {
		if ( ! self::$_instance instanceof RCPGA_Settings ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * Add Hooks and Actions
	 */
	protected function __construct() {
		$this->hooks();
	}

	/**
	 * Actions and Filters
	 */
	protected function hooks() {

		// Admin scripts
		add_action( 'admin_enqueue_scripts', array( $this, 'scripts' ) );

		// Add seat count to subscription level table list
		add_action( 'rcp_levels_page_table_header', array( $this, 'seat_count_label' ) );
		add_action( 'rcp_levels_page_table_footer', array( $this, 'seat_count_label' ) );
		add_action( 'rcp_levels_page_table_column', array( $this, 'seat_count'       ) );

		// Add form field to subscription level add and edit forms
		add_action( 'rcp_add_subscription_form',  array( $this, 'subscription_seat_count' ) );
		add_action( 'rcp_edit_subscription_form', array( $this, 'subscription_seat_count' ) );

		// Actions for saving subscription seat count
		add_action( 'rcp_edit_subscription_level', array( $this, 'subscription_level_save_settings' ), 10, 2 );
		add_action( 'rcp_add_subscription',        array( $this, 'subscription_level_save_settings' ), 10, 2 );

		// Add link back to member group if member is a group owner
		add_action( 'rcp_member_row_actions', array( $this, 'member_group_link' ) );

		// Add group invite email
		add_action( 'rcp_email_settings', array( $this, 'group_invite_email' ) );
		add_action( 'rcp_available_template_tags', array( $this, 'group_template_tags' ) );
	}

	/**
	 * Admin scripts
	 */
	public function scripts() {
		wp_enqueue_script( 'rcp-group-accounts-admin', RCPGA_GROUPS_PLUGIN_URL . 'assets/js/admin.js', array( 'jquery' ), RCPGA_GROUPS_VERSION );
		wp_localize_script( 'rcp-group-accounts-admin', 'rcpga_group_vars', array(
			'delete_group' => __( 'Are you sure you want to delete this group?', 'rcp-group-accounts' ),
			'delete_member' => __( 'Are you sure you want to remove this member from the group?', 'rcp-group-accounts' )
		) );
	}

	/**
	 * Table header/footer
	 */
	public function seat_count_label() {
		printf( '<th class="rcp-sub-children-col">%s</th>', __( 'Seats', 'rcp-group-accounts' ) );
	}

	/**
	 * Table child count
	 *
	 * @param $level_id
	 */
	public function seat_count( $level_id ) {
		printf( '<td>%s</td>', rcpga_get_level_group_seats_allowed( $level_id ) );
	}

	public function subscription_seat_count( $level = null ) {

		$enabled    = apply_filters( 'rcpga_default_level_group_accounts_status', false, $level );
		$enabled    = ( empty( $level->id ) ) ? $enabled : rcpga_is_level_group_accounts_enabled( $level->id );
		$seat_count = ( empty( $level->id ) ) ? 0 : rcpga_get_level_group_seats_allowed( $level->id ); ?>

		<tr class="form-field">
			<th scope="row" valign="top">
				<label for="rcpga-group-seats-allow"><?php _e( 'Allow Church Account', 'rcp-group-accounts' ); ?></label>
			</th>
			<td>
				<input id="rcpga-group-seats-allow" type="checkbox" name="rcpga-group-seats-allow" <?php checked( $enabled ); ?> />
				<p class="description"><?php _e( 'Check to enable church member accounts for this subscription level.', 'rcp-group-accounts' ); ?></p>
			</td>
		</tr>

		<tr class="form-field" <?php echo ( $enabled ) ? '' : 'style="display:none;"' ?>>
			<th scope="row" valign="top">
				<label for="rcpga-group-seats"><?php _e( 'Church Member Seats', 'rcp-group-accounts' ); ?></label>
			</th>
			<td>
				<input id="rcpga-group-seats" type="number" name="rcpga-group-seats" value="<?php echo absint( $seat_count ); ?>" min="0" style="width: 40px;"/>
				<p class="description"><?php _e( 'The number of church member seats available to this level including the church owner.', 'rcp-group-accounts' ); ?></p>
			</td>
		</tr>

	<?php
	}

	/**
	 * Save the member type for this subscription
	 *
	 * @param $subscription_id
	 * @param $args
	 */
	public function subscription_level_save_settings( $subscription_id, $args ) {

		if ( isset( $_POST['rcpga-group-seats-allow'] ) ) {
			rcpga_enable_level_group_accounts( $subscription_id );
		} else {
			rcpga_disable_level_group_accounts( $subscription_id );
		}

		if ( ! empty( $_POST['rcpga-group-seats'] ) ) {
			rcpga_set_level_group_seats_allowed( $subscription_id, absint( $_POST['rcpga-group-seats'] ) );
		} else {
			rcpga_remove_level_seat_count( $subscription_id );
		}

	}

	/**
	 * Add group link to member edit links
	 *
	 * @param $user_id
	 */
	public function member_group_link( $user_id ) {
		if ( ! $group = rcpga_group_accounts()->groups->get_group_by_owner( $user_id ) ) {
			return;
		}
		?>
		<span class="rcp-separator"> | </span>
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=rcp-groups&rcpga-view=view-members&rcpga-group=' . $group->group_id ) ); ?>"><?php _e( 'Church', 'rcp-group-accounts' ); ?></a>
		<?php
	}

	/**
	 * Settings for group invite email
	 *
	 * @param $rcp_options
	 */
	public function group_invite_email( $rcp_options )  {
		?>
		<table class="form-table">
			<tr valign="top">
				<th colspan=2>
					<h3><?php _e( 'Church Invite Email', 'rcp-group-accounts' ); ?></h3>
				</th>
			</tr>
			<tr>
				<th>
					<label for="rcp_settings[disable_group_invite_email]"><?php _e( 'Disabled', 'rcp' ); ?></label>
				</th>
				<td>
					<p>
						<input type="checkbox" value="1" name="rcp_settings[disable_group_invite_email]" id="rcp_settings[disable_group_invite_email]" <?php checked( true, isset( $rcp_options['disable_group_invite_email'] ) ); ?>/>
						<span><?php _e( 'Check this to disable the email sent out when a member is invited to a group.', 'rcp-group-accounts' ); ?></span>
					</p>
					<p>
						<input type="checkbox" value="1" name="rcp_settings[disable_group_welcome_email]" id="rcp_settings[disable_group_welcome_email]" <?php checked( true, isset( $rcp_options['disable_group_welcome_email'] ) ); ?>/>
						<span><?php _e( 'Check this to disable the new user welcome email sent out when a new member accepts an invite link.', 'rcp-group-accounts' ); ?></span>
					</p>
				</td>
			</tr>
			<tr valign="top">
				<th>
					<label for="rcp_settings[group_invite_subject]"><?php _e( 'Subject', 'rcp' ); ?></label>
				</th>
				<td>
					<input class="regular-text" id="rcp_settings[group_invite_subject]" style="width: 300px;" name="rcp_settings[group_invite_subject]" value="<?php if( isset( $rcp_options['group_invite_subject'] ) ) { echo $rcp_options['group_invite_subject']; } ?>"/>
					<p class="description"><?php _e( 'The subject line for the email sent out when a member is invited to a group.', 'rcp-group-accounts' ); ?></p>
				</td>
			</tr>
			<tr valign="top">
				<th>
					<label for="rcp_settings[group_invite_email]"><?php _e( 'Email Body', 'rcp' ); ?></label>
				</th>
				<td>
					<?php
					$invite_email = isset( $rcp_options['group_invite_email'] ) ? $rcp_options['group_invite_email'] : '';

					if ( class_exists( 'RCP_Emails' ) ) {
						wp_editor( $invite_email, 'rcp_settings_group_invite_email', array( 'textarea_name' => 'rcp_settings[group_invite_email]', 'teeny' => true ) );
					} else {
						?>
						<textarea id="rcp_settings[group_invite_email]" style="width: 300px; height: 100px;" name="rcp_settings[group_invite_email]"><?php echo esc_textarea( $invite_email ); ?></textarea>
						<?php
					}
					?>
					<p class="description"><?php _e( 'This is the email message that is sent out when a member is invited to a group.', 'rcp-group-accounts' ); ?></p>
				</td>
			</tr>
		</table>
		<?php
	}

	/**
	 * Add template tags
	 *
	 * @deprecated 1.1 Template tags are added to the list automatically in RCP 2.7+
	 */
	public function group_template_tags() {
		if ( class_exists( 'RCP_Emails' ) ) {
			return;
		}
		?>
		<li><em>%groupname%</em> - <?php _e( 'will be replaced with the name of the church to which person receiving the email belongs or is being invited', 'rcp-group-accounts' ); ?></li>
		<li><em>%groupdesc%</em> - <?php _e( 'will be replaced with the description of the church to which person receiving the email belongs or is being invited', 'rcp-group-accounts' ); ?></li>
		<li><em>%invitelink%</em> - <?php _e( 'will be replaced with the invite link the user will click to join the church, only for the Church Invite Email', 'rcp-group-accounts' ); ?></li>
		<?php
	}

}
