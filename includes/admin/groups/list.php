<div class="wrap" id="rcp-members-page">

	<h2><?php _e( 'Church', 'rcp-group-accounts' ); ?> <a href="<?php echo esc_url( admin_url( 'admin.php?page=rcp-groups&rcpga-view=add-group' ) ); ?>" class="add-new-h2"><?php _e( 'Add Church', 'rcp-group-accounts' ); ?></a></h2>
	<table class="wp-list-table widefat fixed posts">
		<thead>
			<tr>
				<th><?php _e( 'Church', 'rcp-group-accounts' ); ?></th>
				<th><?php _e( 'Administrator', 'rcp-group-accounts' ); ?></th>
				<th><?php _e( 'Description', 'rcp-group-accounts' ); ?></th>
                <th><?php _e( 'Address', 'rcp-group-accounts' ); ?></th>
				<th><?php _e( 'Usage', 'rcp-group-accounts' ); ?></th>
				<th><?php _e( 'Date Created', 'rcp-group-accounts' ); ?></th>
				<?php do_action( 'rcpga_groups_page_table_header' ); ?>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<th><?php _e( 'Church', 'rcp-group-accounts' ); ?></th>
				<th><?php _e( 'Administrator', 'rcp-group-accounts' ); ?></th>
                <th><?php _e( 'Description', 'rcp-group-accounts' ); ?></th>
                <th><?php _e( 'Address', 'rcp-group-accounts' ); ?></th>
				<th><?php _e( 'Usage', 'rcp-group-accounts' ); ?></th>
				<th><?php _e( 'Date Created', 'rcp-group-accounts' ); ?></th>
				<?php do_action( 'rcpga_groups_page_table_footer' ); ?>
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
		$groups = rcpga_group_accounts()->groups->get_groups( $args );

		$total_groups = rcpga_group_accounts()->groups->get_group_count();
		$total_pages  = ceil( $total_groups / $per_page );

		if( ! empty( $groups ) ) :
			$i = 1;
			foreach( $groups as $key => $group ) : ?>
				<tr<?php echo $i & 1 ? ' class="alternate"' : ''; ?>>
                    <td>

                        <?php
                        if (!empty($group->name)) {
                        ?>
                            <a title="Edit <?php _e( stripslashes( $group->name ), 'rcp-group-accounts' ); ?>" href="<?php echo esc_url( admin_url( 'admin.php?page=rcp-groups&rcpga-view=edit&rcpga-group=' . $group->group_id ) ); ?>"><?php _e( stripslashes( $group->name ), 'rcp-group-accounts' ); ?></a><br />
                        <?php
                        } else {
                            ?>
                            <a title="Edit <?php _e( 'Name Missing', 'rcp-group-accounts' ); ?>" href="<?php echo esc_url( admin_url( 'admin.php?page=rcp-groups&rcpga-view=edit&rcpga-group=' . $group->group_id ) ); ?>"><?php _e( 'Name Missing', 'rcp-group-accounts' ); ?></a><br />
                            <?php
                        }
                        ?>




                        <a href="<?php echo esc_url( admin_url( 'admin.php?page=rcp-groups&rcpga-view=view-members&rcpga-group=' . $group->group_id ) ); ?>"><?php _e( 'Members', 'rcp-group-accounts' ); ?></a>&nbsp;|&nbsp;
                        <span class="trash">
							<a href="<?php echo esc_url( admin_url( 'admin.php?page=rcp-groups&rcpga-action=delete-group&rcpga-group=' . $group->group_id ) ); ?>" class="submitdelete rcpga-group-delete" style="color:#a00"><?php _e( 'Delete', 'rcp-group-accounts' ); ?></a>
						</span>
                    </td>
					<td><a href="<?php echo esc_url( add_query_arg( 'user_id', $group->owner_id, admin_url( 'user-edit.php' ) ) ); ?>"> <?php echo get_userdata( $group->owner_id )->display_name; ?></a></td>
					<td><?php echo stripslashes( $group->description ); ?></td>

                    <td>
                        <?php
                        if (!empty($group->address1)) {
                            ?>
                            <?php echo stripslashes( $group->address1 ); ?><br />
                            <?php
                        } ?>

                        <?php
                        if (!empty($group->address2)) {
                        ?>
                            <?php echo stripslashes( $group->address2 ); ?><br />
                        <?php
                        } ?>

                        <?php
                        if (!empty($group->city)) {
                            ?>
                            <?php echo stripslashes( $group->city ); ?>,&nbsp;
                            <?php
                        } ?>

                        <?php
                        if (!empty($group->state)) {
                            ?>
                            <?php echo stripslashes( $group->state ); ?>&nbsp;
                            <?php
                        } ?>

                        <?php
                        if (!empty($group->zip)) {
                            ?>
                            <?php echo stripslashes( $group->zip ); ?>
                            <?php
                        } ?>
                    </td>

					<td><?php echo absint( $group->member_count ); ?> of
					<?php echo absint( $group->seats ); ?></td>
					<td><?php echo $group->date_created; ?></td>
					<?php do_action( 'rcpga_groups_page_table_column', $group ); ?>
				</tr>
			<?php $i++;
			endforeach;
		else : ?>
			<tr><td colspan="8"><?php _e( 'No groups found', 'rcp-group-accounts' ); ?></td></tr>
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