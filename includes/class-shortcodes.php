<?php

class RCPGA_Group_Shortcodes {

	/**
	 * The current user ID. Set in can_manage().
	 *
	 * @access private
	 * @var int
	 */
	private $user_id;

	/**
	 * Holds any error messages related to group management permissions.
	 *
	 * @access private
	 * @var string
	 */
	private $manage_error;

	public function __construct() {

		add_shortcode( 'rcp_group_dashboard', array( $this, 'dashboard' ) );
		add_shortcode( 'rcp_group_member_add', array( $this, 'add_member' ) );
		add_shortcode( 'rcp_group_member_import', array( $this, 'import_members' ) );
		add_shortcode( 'rcp_group_members_list', array( $this, 'members_list' ) );
		add_shortcode( 'rcp_group_edit_group', array( $this, 'edit_group' ) );
		add_shortcode( 'rcp_group_is_owner', array( $this, 'is_group_owner' ) );
		add_shortcode( 'rcp_group_is_admin', array( $this, 'is_group_admin' ) );
		add_shortcode( 'rcp_group_is_member', array( $this, 'is_group_member' ) );

		add_filter( 'rcp_template_stack',    array( $this, 'template_stack' ) );
		add_filter( 'rcp_get_template_part', array( $this, 'group_subscription_template' ), 10, 3 );

	}

	/**
	 * Include this plugin in the template check for RCP templates
	 *
	 * @param $template_stack
	 *
	 * @return array
	 */
	public function template_stack( $template_stack ) {
		$template_stack[] = RCPGA_GROUPS_PLUGIN_DIR . 'templates';
		return $template_stack;
	}

	/**
	 * Content for the dashboard shortcode
	 *
	 * @param      $atts
	 * @param null $content
	 *
	 * @return string
	 */
	public function dashboard( $atts, $content = null ) {

		if ( ! $this->can_manage() ) {
			return $this->manage_error;
		}

		$this->load_form_styles();

		ob_start();

		rcp_get_template_part( 'group', 'dashboard' );

		return ob_get_clean();

	}

	/**
	 * Displays the [rcp_group_member_add] shortcode content.
	 *
	 * @since 1.0
	 *
	 * @param array  $atts    Shortcode attributes
	 * @param string $content Shortcode content
	 *
	 * @return string HTML content to display the shortcode output.
	 */
	public function add_member( $atts, $content = null ) {

		$atts = shortcode_atts( array(
			'show_csv_import'   => false,
			'show_seat_count' => true
		), $atts, 'rcp_group_member_add' );

		$this->load_form_styles();

		if ( ! $this->can_manage() ) {
			return $this->manage_error;
		}

		$group_id    = rcpga_group_accounts()->members->get_group_id( $this->user_id );
		$total_seats = rcpga_group_accounts()->groups->get_seats_count( $group_id );
		$used_seats  = rcpga_group_accounts()->groups->get_member_count( $group_id );

		ob_start();

		if ( $total_seats > $used_seats && ! in_array( rcp_get_status(), array( 'expired', 'pending' ) ) && ! rcp_is_expired() ) {

			rcp_get_template_part( 'group', 'member-add' );

			if ( $atts['show_csv_import'] ) {
				rcp_get_template_part( 'group', 'member-import' );
			}
		}

		if ( $atts['show_seat_count'] ) {
			echo apply_filters( 'rcpga-group-status-message', sprintf( '<p>' . __( 'You are currently using %s out of %s seats available on your account.', 'rcp-group-accounts' ) . '</p>', esc_html( $used_seats ), esc_html( $total_seats ) ), $group_id, $this->user_id );
		}

		return ob_get_clean();
	}

	/**
	 * Displays the [rcp_group_member_import] shortcode content.
	 *
	 * @since 1.0
	 *
	 * @param array  $atts    Shortcode attributes
	 * @param string $content Shortcode content
	 *
	 * @return string HTML content to display the shortcode output.
	 */
	public function import_members( $atts, $content = null ) {

		$atts = shortcode_atts( array(
			'show_seat_count' => true
		), $atts, 'rcp_group_member_import' );

		$this->load_form_styles();

		if ( ! $this->can_manage() ) {
			return $this->manage_error;
		}

		$group_id    = rcpga_group_accounts()->members->get_group_id( $this->user_id );
		$total_seats = rcpga_group_accounts()->groups->get_seats_count( $group_id );
		$used_seats  = rcpga_group_accounts()->groups->get_member_count( $group_id );

		ob_start();

		if ( $total_seats > $used_seats && ! in_array( rcp_get_status(), array( 'expired', 'pending' ) ) && ! rcp_is_expired() ) {
			rcp_get_template_part( 'group', 'member-import' );
		}

		if ( true === $atts['show_seat_count'] ) {
			echo apply_filters( 'rcpga-group-status-message', sprintf( '<p>' . __( 'You are currently using %s out of %s seats available on your account.', 'rcp-group-accounts' ) . '</p>', esc_html( $used_seats ), esc_html( $total_seats ) ), $group_id, $this->user_id );
		}

		return ob_get_clean();
	}

	/**
	 * Displays the [rcp_group_members_list] shortcode content.
	 *
	 * @since 1.0
	 *
	 * @param array  $atts    Shortcode attributes
	 * @param string $content Shortcode content
	 *
	 * @return string HTML content to display the shortcode output.
	 */
	public function members_list( $atts, $content = null ) {

		$this->load_form_styles();

		if ( ! $this->can_manage() ) {
			return $this->manage_error;
		}

		ob_start();

		rcp_get_template_part( 'group', 'members-list' );

		return ob_get_clean();
	}

	/**
	 * Displays the [rcp_group_edit_group] shortcode content.
	 *
	 * @since 1.0
	 *
	 * @param array  $atts    Shortcode attributes
	 * @param string $content Shortcode content
	 *
	 * @return string HTML content to display the shortcode output.
	 */
	public function edit_group( $atts, $content = null ) {

		$this->load_form_styles();

		if ( ! $this->can_manage() ) {
			return $this->manage_error;
		}

		ob_start();

		rcp_get_template_part( 'group', 'edit' );

		return ob_get_clean();
	}

	/**
	 * Shows content only to group owners.
	 *
	 * @since 1.0.3
	 *
	 * @param array  $atts    Shortcode attributes
	 * @param string $content Shortcode content
	 *
	 * @return string|void
	 */
	public function is_group_owner( $atts, $content = null ) {

		if ( rcpga_group_accounts()->members->is_group_owner() ) {
			return do_shortcode( $content );
		}

	}

	/**
	 * Shows content only to group admins.
	 *
	 * @since 1.0.4
	 *
	 * @param array  $atts    Shortcode attributes
	 * @param string $content Shortcode content
	 *
	 * @return string|void
	 */
	public function is_group_admin( $atts, $content = null ) {

		if ( rcpga_group_accounts()->members->is_group_admin() ) {
			return do_shortcode( $content );
		}

	}

	/**
	 * Shows content only to group members, and optionally group owners.
	 *
	 * @since 1.0.3
	 *
	 * @param array  $atts    Shortcode attributes
	 * @param string $content Shortcode content
	 *
	 * @return string|void
	 */
	public function is_group_member( $atts, $content = null ) {

		$atts = shortcode_atts( array(
			'include_owner' => false,
			'include_admin' => false
		), $atts, 'rcp_group_is_member' );

		$is_member = false;

		// Regular member.
		if ( rcpga_group_accounts()->members->is_group_member() ) {
			$is_member = true;
		}

		// Group owner.
		if ( $atts['include_owner'] && rcpga_group_accounts()->members->is_group_owner() ) {
			$is_member = true;
		}

		// Group admin.
		if ( $atts['include_admin'] && rcpga_group_accounts()->members->is_group_admin() ) {
			$is_member = true;
		}

		if ( $is_member ) {
			return do_shortcode( $content );
		}

	}

	/**
	 * Determines if the logged in user can manage a group.
	 *
	 * @since 1.0
	 *
	 * @return bool True if the user can manage a group, false if not.
	 */
	private function can_manage() {

		if ( ! is_user_logged_in() ) {
			$this->manage_error = __( 'Please sign in to manage your group.', 'rcp-group-accounts' );
			return false;
		}

		$this->user_id = get_current_user_id();

		if ( in_array( rcp_get_status( $this->user_id ), array( 'expired', 'pending' ) ) ) {
			$this->manage_error = __( 'You must have an active membership to manage your group.', 'rcp-group-accounts' );
			return false;
		}

		if ( ! $group_id = rcpga_group_accounts()->members->get_group_id( $this->user_id ) ) {
			// if the user does not have a group, check if one should be created
			$group_id = rcpga_maybe_create_member_group( $this->user_id );
		}

		// only show the dashboard if we have a group id
		if ( ! $group_id ) {
			$this->manage_error = __( 'You must have a membership that allows group accounts to manage a group.', 'rcp-group-accounts' );
			return false;
		}

		// make sure the current user can manage members
		if ( ! rcpga_group_accounts()->capabilities->can( 'manage_members' ) ) {
			$this->manage_error = __( 'You must be a group administrator to manage this group.', 'rcp-group-accounts' );
			return false;
		}

		return true;
	}

	/**
	 * Loads the RCP core form styles if they are not disabled.
	 */
	public function load_form_styles() {

		global $rcp_options;

		if ( empty( $rcp_options['disable_css'] ) ) {
			wp_print_styles( 'rcp-form-css' );
		}

	}

	/**
	 * Return the 'group-subscription' template instead of the 'subscription' template when
	 * a non-owner group member is viewing the [subscription_details] shortcode
	 *
	 * @param $templates
	 * @param $slug
	 * @param $name
	 *
	 * @return string
	 */
	public function group_subscription_template( $templates, $slug, $name ) {

		if ( 'subscription' !== $slug ) {
			return $templates;
		}

		// only applies to non-owners
		if ( rcpga_group_accounts()->members->is_group_owner() ) {
			return $templates;
		}

		// make sure this user belongs to a group
		if ( ! rcpga_group_accounts()->members->get_group_id() ) {
			return $templates;
		}

		// unhook this filter
		remove_filter( 'rcp_get_template_part', array( $this, 'group_subscription_template' ), 10, 3 );

		// get the group-subscription template
		$templates = rcp_get_template_part( 'group', 'subscription' );

		// re-hook this filter
		add_filter( 'rcp_get_template_part', array( $this, 'group_subscription_template' ), 10, 3 );

		return $templates;

	}

}

/**
 * Inits the shortcodes class.
 */
function rcpga_shortcodes_init() {
	new RCPGA_Group_Shortcodes;
}
add_action( 'template_redirect', 'rcpga_shortcodes_init' );