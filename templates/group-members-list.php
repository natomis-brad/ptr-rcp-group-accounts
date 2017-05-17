<?php
global $rcp_options;
$group_id = rcpga_group_accounts()->members->get_group_id();
$page     = get_query_var( 'paged' ) ? get_query_var( 'paged' ) : 1;
$per_page = 20;
$offset   = $per_page * ( absint( $page ) - 1 );
$args     = array(
	'number' => $per_page,
	'offset' => $offset
);

$members = $member_list = rcpga_group_accounts()->members->get_members( $group_id, $args );

// this should never happen
if ( empty( $members ) ) {
	return;
}

$sort_order  = apply_filters( 'rcpga_member_role_sort_order', array( 'owner', 'admin', 'invited', 'member' ) );

// sort members by role
if ( ! empty( $sort_order ) ) {
	$member_list = array();

	foreach( $sort_order as $role ) {
		foreach( $members as $member ) {
			if ( $role == $member->role ) {
				$member_list[] = $member;
			}
		}
	}

}

if ( ! did_action( 'rcpga_dashboard_notifications' ) ) {
	do_action( 'rcpga_dashboard_notifications' );
}
?>

<h4 class="rcp-header"><?php _e( 'Church Members', 'rcp-group-accounts' ); ?></h4>

<table class="rcp-table" id="rcpga-group-members-list">

	<thead>
	<tr>
		<th colspan="2"><?php _e( 'Name', 'rcp-group-accounts' ); ?></th>
		<th><?php _e( 'Role', 'rcp-group-accounts' ); ?></th>
		<th><?php _e( 'Actions', 'rcp-group-accounts' ); ?></th>
	</tr>
	</thead>

	<tbody>
	<?php foreach ( $member_list as $member ) : ?>

		<?php
		if ( ! $user_data = get_userdata( $member->user_id ) ) {
			continue;
		}
		?>

		<tr>
			<?php do_action( 'rcpga_before_member_data', $user_data ); ?>
			<td colspan="2" class="member-name" data-th="<?php esc_attr_e( 'Name', 'rcp-group-accounts' ); ?>"><?php echo $user_data->display_name; ?></td>
			<td class="member-roll" data-th="<?php esc_attr_e( 'Role', 'rcp-group-accounts' ); ?>"><?php echo esc_html( $member->role ); ?></td>
			<td class="member-actions" data-th="<?php esc_attr_e( 'Actions', 'rcp-group-accounts' ); ?>">
				<?php if ( 'owner' !== $member->role ) : ?>
					<a href="<?php echo esc_url( home_url( 'index.php?rcpga-action=remove-member&rcpga-group=' . $member->group_id . '&rcpga-member=' . $member->user_id ) ); ?>"><?php _e( 'Remove from Group', 'rcp-group-accounts' ); ?></a><br />
					<?php if( 'admin' == $member->role ) : ?>
						<a href="<?php echo esc_url( admin_url( 'index.php?rcpga-action=make-member&rcpga-group=' . $member->group_id . '&rcpga-member=' . $member->user_id ) ); ?>"><?php _e( 'Set as Member', 'rcp-group-accounts' ); ?></a>
					<?php elseif ( 'member' == $member->role ) : ?>
						<a href="<?php echo esc_url( admin_url( 'index.php?rcpga-action=make-admin&rcpga-group=' . $member->group_id . '&rcpga-member=' . $member->user_id ) ); ?>"><?php _e( 'Set as Admin', 'rcp-group-accounts' ); ?></a>
					<?php elseif ( 'invited' == $member->role ) : ?>
						<a href="<?php echo esc_url( admin_url( 'index.php?rcpga-action=resend-invite&rcpga-group=' . $member->group_id . '&rcpga-member=' . $member->user_id ) ); ?>"><?php _e( 'Resend Invite', 'rcp-group-accounts' ); ?></a>
					<?php endif; ?>
				<?php endif; ?>
			</td>
			<?php do_action( 'rcpga_after_member_data', $user_data ); ?>
		</tr>
	<?php endforeach; ?>
	</tbody>
</table>

<?php
// Pagination
$total_members = rcpga_group_accounts()->groups->get_member_count( $group_id );
$total_pages   = ceil( $total_members / $per_page );

if ( $total_pages > 1 ) {
	?>
	<div id="rcpga-group-members-pagination">
		<?php
		$big = 999999;
		echo paginate_links( array(
			'base'     => str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),
			'format'   => '?paged=%#%',
			'total'    => $total_pages,
			'current'  => $page,
			'end_size' => 1,
			'mid_size' => 5,
		) );
		?>
	</div>
	<?php
}