<?php $group_id = absint( $_GET['rcpga-group'] ); ?>
<div class="wrap" id="rcp-members-page">

	<h2>
		<?php printf( __( '%s Church Members', 'rcp-group-accounts' ), rcpga_group_accounts()->groups->get_name( $group_id ) ); ?>
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=rcp-groups&rcpga-view=add-member&rcpga-group=' . $group_id ) ); ?>" class="add-new-h2"><?php _e( 'Add Member', 'rcp-group-accounts' ); ?></a>
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=rcp-groups&rcpga-view=import-members&rcpga-group=' . $group_id ) ); ?>" class="add-new-h2"><?php _e( 'Import Members', 'rcp-group-accounts' ); ?></a>
	</h2>

	<table class="wp-list-table widefat fixed posts">
		<thead>
			<tr>
				<th><?php _e( 'Name', 'rcp-group-accounts' ); ?></th>
				<th><?php _e( 'ID', 'rcp-group-accounts' ); ?></th>
				<th><?php _e( 'Role', 'rcp-group-accounts' ); ?></th>
				<th><?php _e( 'Actions', 'rcp-group-accounts' ); ?></th>
				<?php do_action( 'rcpga_group_members_page_table_header' ); ?>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<th><?php _e( 'Name', 'rcp-group-accounts' ); ?></th>
				<th><?php _e( 'ID', 'rcp-group-accounts' ); ?></th>
				<th><?php _e( 'Role', 'rcp-group-accounts' ); ?></th>
				<th><?php _e( 'Actions', 'rcp-group-accounts' ); ?></th>
				<?php do_action( 'rcpga_group_members_page_table_footer' ); ?>
			</tr>
		</tfoot>
		<tbody>
		<?php

		$page     = isset( $_GET['paged'] ) ? $_GET['paged'] : 1;
		$per_page = 20;
		$offset   = $per_page * ( $page - 1 );

		$args   = array(
			'number' => $per_page,
			'offset' => $offset
		);
		$members = rcpga_group_accounts()->members->get_members( $group_id, $args );

		$total_members = rcpga_group_accounts()->members->count( $group_id );
		$total_pages   = ceil( $total_members / $per_page );

		if( ! empty( $members ) ) :
			$i = 1;
			foreach( $members as $key => $member ) : ?>

				<?php
				$user_data = get_userdata( $member->user_id );
				if( ! $user_data ) {
					continue;
				}
				?>

				<tr<?php echo $i & 1 ? ' class="alternate"' : ''; ?>>
					<td><a href="<?php echo esc_url( add_query_arg( 'user_id', $member->user_id, admin_url( 'user-edit.php' ) ) ); ?>"><?php echo $user_data->display_name; ?></a></td>
					<td><?php echo $member->user_id; ?></td>
					<td><?php echo $member->role; ?></td>
					<td>
						<?php if( 'owner' != $member->role ) : ?>
							<a href="<?php echo esc_url( admin_url( 'admin.php?page=rcp-groups&rcpga-action=remove-member&rcpga-group=' . $member->group_id . '&rcpga-member=' . $member->user_id ) ); ?>" class="rcp-member-delete"><?php _e( 'Remove from Group', 'rcp-group-accounts' ); ?></a>&nbsp;|&nbsp;
							<?php if( 'admin' == $member->role ) : ?>
								<a href="<?php echo esc_url( admin_url( 'admin.php?page=rcp-groups&rcpga-action=make-member&rcpga-group=' . $member->group_id . '&rcpga-member=' . $member->user_id ) ); ?>"><?php _e( 'Set as Member', 'rcp-group-accounts' ); ?></a>
							<?php elseif ( 'member' == $member->role ) : ?>
								<a href="<?php echo esc_url( admin_url( 'admin.php?page=rcp-groups&rcpga-action=make-admin&rcpga-group=' . $member->group_id . '&rcpga-member=' . $member->user_id ) ); ?>"><?php _e( 'Set as Admin', 'rcp-group-accounts' ); ?></a>
							<?php else : ?>
								<a href="<?php echo esc_url( admin_url( 'admin.php?page=rcp-groups&rcpga-action=make-member&rcpga-update-role=true&rcpga-group=' . $member->group_id . '&rcpga-member=' . $member->user_id ) ); ?>"><?php _e( 'Set as Member', 'rcp-group-accounts' ); ?></a>&nbsp;|&nbsp;
								<a href="<?php echo esc_url( admin_url( 'admin.php?page=rcp-groups&rcpga-action=resend-invite&rcpga-group=' . $member->group_id . '&rcpga-member=' . $member->user_id ) ); ?>"><?php _e( 'Resend Invite', 'rcp-group-accounts' ); ?></a>
							<?php endif; ?>
						<?php endif; ?>
					</td>
					<?php do_action( 'rcpga_group_members_page_table_column', $member ); ?>
				</tr>
			<?php $i++;
			endforeach;
		else : ?>
			<tr><td colspan="5"><?php _e( 'No members in this group', 'rcp-group-accounts' ); ?></td></tr>
		<?php endif; ?>
		</tbody>
	</table>
	<?php if ( $total_pages > 1 ) : ?>
		<div class="tablenav">
			<div class="tablenav-pages alignright">
				<?php
				$base = 'admin.php?' . remove_query_arg( 'paged', $_SERVER['QUERY_STRING'] ) . '%_%';
				echo paginate_links( array(
					'base' 		=> $base,
					'format' 	=> '&paged=%#%',
					'prev_text' => __( '&laquo; Previous', 'rcp' ),
					'next_text' => __( 'Next &raquo;', 'rcp' ),
					'total' 	=> $total_pages,
					'current' 	=> $page,
					'end_size' 	=> 1,
					'mid_size' 	=> 5,
				));
				?>
			</div>
		</div><!--end .tablenav-->
	<?php endif; ?>
</div><!--end wrap-->