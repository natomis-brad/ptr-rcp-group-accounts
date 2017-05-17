<?php

class RCPGA_Groups_Admin_Menu {

	public function __construct() {
		add_action( 'admin_menu', array( $this, 'register_menus' ), 100 );
	}

	public function register_menus() {
		add_submenu_page( 'rcp-members','Churches', 'Churches', 'rcp_view_members', 'rcp-groups', array( $this, 'groups_admin' ) );
	}

	public function groups_admin() {

		$view = isset( $_GET['rcpga-view'] ) ? $_GET['rcpga-view'] : '';

		switch( $view ) {

			case 'edit' :
				include RCPGA_GROUPS_PLUGIN_DIR . 'includes/admin/groups/edit-group.php';
				break;

			case 'add-group' :
				include RCPGA_GROUPS_PLUGIN_DIR . 'includes/admin/groups/add-group.php';
				break;

			case 'view-members' :
				include RCPGA_GROUPS_PLUGIN_DIR . 'includes/admin/groups/members.php';
				break;

			case 'add-member' :
				include RCPGA_GROUPS_PLUGIN_DIR . 'includes/admin/groups/add-member.php';
				break;

			case 'import-members' :
				include RCPGA_GROUPS_PLUGIN_DIR . 'includes/admin/groups/import-members.php';
				break;

			default:
				include RCPGA_GROUPS_PLUGIN_DIR . 'includes/admin/groups/list.php';
				break;

		}

	}

}
$rcp_groups_admin_menu = new RCPGA_Groups_Admin_Menu;