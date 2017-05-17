<?php
/*
Plugin Name: Restrict Content Pro - Group Accounts
Plugin URL: https://github.com/restrictcontentpro/rcp-group-accounts
Description: Provides the database and API for group accounts
Version: 1.1.2
Author: Restrict Content Pro team
Author URI: https://restrictcontentpro.com/
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'RCPGA_Group_Accounts' ) ) :

/**
 * Main RCPGA_Group_Accounts Class
 *
 * @since 1.0
 */
final class RCPGA_Group_Accounts {

	/** Singleton ************************************************************/

	/**
	 * @var RCPGA_Group_Accounts The one true RCPGA_Group_Accounts
	 * @since 1.0
	 */
	private static $instance;

	/**
	 * The version number of RCP Groups
	 *
	 * @since 1.0
	 */
	private $version = '1.1.2';

	/**
	 * The groups DB instance variable.
	 *
	 * @var RCPGA_Groups
	 * @since 1.0
	 */
	public $groups;

	/**
	 * The members instance variable.
	 *
	 * @var RCPGA_Group_Members
	 * @since 1.0
	 */
	public $members;

	/**
	 * The capabilities instance variable.
	 *
	 * @var RCPGA_Group_Capabilities
	 * @since 1.0
	 */
	public $capabilities;

	/**
	 * The group notices instance.
	 *
	 * @var RCPGA_Group_Notices
	 * @since 1.0.1
	 */
	public $notices;

	/**
	 * The group actions instance.
	 *
	 * @var RCPGA_Group_Actions
	 * @since 1.0.1
	 */
	public $actions;

	/**
	 * Main RCPGA_Group_Accounts Instance
	 *
	 * Insures that only one instance of RCPGA_Group_Accounts exists in memory at any one
	 * time. Also prevents needing to define globals all over the place.
	 *
	 * @since 1.0
	 * @static var array $instance
	 * @uses RCPGA_Group_Accounts::constants() Setup the plugin constants
	 * @uses RCPGA_Group_Accounts::includes() Include the required files
	 * @return RCPGA_Group_Accounts
	 */
	public static function instance() {
		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof RCPGA_Group_Accounts ) ) {
			self::$instance = new RCPGA_Group_Accounts;
			self::$instance->constants();
			self::$instance->includes();

			// Setup objects
			self::$instance->groups       = RCPGA_Groups::get_instance();
			self::$instance->members      = RCPGA_Group_Members::get_instance();
			self::$instance->capabilities = RCPGA_Group_Capabilities::get_instance();
			self::$instance->notices      = new RCPGA_Groups_Notices;
			self::$instance->actions      = new RCPGA_Groups_Actions;

		}
		return self::$instance;
	}

	/**
	 * Setup plugin constants
	 *
	 * @access private
	 * @since 1.0
	 * @return void
	 */
	private function constants() {
		// Plugin version
		if ( ! defined( 'RCPGA_GROUPS_VERSION' ) ) {
			define( 'RCPGA_GROUPS_VERSION', $this->version );
		}

		// Plugin Folder Path
		if ( ! defined( 'RCPGA_GROUPS_PLUGIN_DIR' ) ) {
			define( 'RCPGA_GROUPS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
		}

		// Plugin Folder URL
		if ( ! defined( 'RCPGA_GROUPS_PLUGIN_URL' ) ) {
			define( 'RCPGA_GROUPS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
		}

		// Plugin Root File
		if ( ! defined( 'RCPGA_GROUPS_PLUGIN_FILE' ) ) {
			define( 'RCPGA_GROUPS_PLUGIN_FILE', __FILE__ );
		}
	}

	/**
	 * Include required files
	 *
	 * @access private
	 * @since 1.0
	 * @return void
	 */
	private function includes() {

		require_once RCPGA_GROUPS_PLUGIN_DIR . 'includes/class-capabilities.php';
		require_once RCPGA_GROUPS_PLUGIN_DIR . 'includes/class-db-base.php';
		require_once RCPGA_GROUPS_PLUGIN_DIR . 'includes/class-db-groups.php';
		require_once RCPGA_GROUPS_PLUGIN_DIR . 'includes/class-db-group-members.php';
		require_once RCPGA_GROUPS_PLUGIN_DIR . 'includes/class-actions.php';
		require_once RCPGA_GROUPS_PLUGIN_DIR . 'includes/group-member-functions.php';
		require_once RCPGA_GROUPS_PLUGIN_DIR . 'includes/class-notices.php';

		if( is_admin() ) {

			require_once RCPGA_GROUPS_PLUGIN_DIR . 'includes/admin/class-menu.php';
			require_once RCPGA_GROUPS_PLUGIN_DIR . 'includes/admin/class-settings.php';

		} else {

			require_once RCPGA_GROUPS_PLUGIN_DIR . 'includes/class-shortcodes.php';

		}

	}

}

endif; // End if class_exists check


/**
 * The main function responsible for returning the one true RCPGA_Group_Accounts
 * Instance to functions everywhere.
 *
 * Use this function like you would a global variable, except without needing
 * to declare the global.
 *
 * Example: <?php $RCPGA_Group_Accounts = RCPGA_Group_Accounts(); ?>
 *
 * @since 1.0
 * @return RCPGA_Group_Accounts The one true Instance
 */
function rcpga_group_accounts() {
	return RCPGA_Group_Accounts::instance();
}
rcpga_group_accounts();


/**
 * Create database tables during install
 *
 * @since 1.0
 */
function rcpga_group_accounts_install() {

	rcpga_group_accounts()->groups->create_table();
	rcpga_group_accounts()->members->create_table();

	do_action( 'rcp-group-accounts-activated' );

}
register_activation_hook( __FILE__, 'rcpga_group_accounts_install' );

/**
 * Create custom user role for members created during a group invite
 */
function rcpga_custom_user_role() {
	add_role( 'rcp-invited', __( 'Invited', 'rcp-group-accounts' ), array( 'read' => true ) );
}
add_action( 'rcp-group-accounts-activated', 'rcpga_custom_user_role' );

/**
 * Loads the plugin updater.
 */
function rcpga_plugin_updater() {
	if ( is_admin() && class_exists( 'RCP_Add_On_Updater' ) ) {
		new RCP_Add_On_Updater( 388, __FILE__, RCPGA_GROUPS_VERSION );
	}
}
add_action( 'plugins_loaded', 'rcpga_plugin_updater' );

/**
 * Loads the plugin translation files.
 */
function rcpga_textdomain() {
	load_plugin_textdomain( 'rcp-group-accounts', false, dirname( plugin_basename( RCPGA_GROUPS_PLUGIN_FILE ) ) . '/languages' );
}
add_action( 'plugins_loaded', 'rcpga_textdomain' );
