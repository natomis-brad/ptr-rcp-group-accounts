<?php
global $rcp_options;

?>

<div class="wrap">

	<h2><?php _e( 'Import Church Members', 'rcp-group-accounts' ); ?></h2>

	<form method="post" enctype="multipart/form-data">

		<table class="form-table">

			<tr class="form-row form-required">

				<th scope="row">
					<label for="rcpga-group-csv"><?php _e( 'CSV File Upload', 'rcp-group-accounts' ); ?></label>
				</th>

				<td>
					<input type="file" accept=".csv, text/csv" name="rcpga-group-csv" id="rcpga-group-member-import"/>
					<p class="description"><?php _e( 'Upload a CSV file. ', 'rcp-group-accounts' ); ?><?php echo apply_filters( 'rcpga_group_csv_import_sample_link', sprintf( '<a href="%s">%s</a>', RCPGA_GROUPS_PLUGIN_URL . 'member-import-sample.csv',  __( 'Click here to see a sample CSV', 'rcp-group-accounts' ) ) ); ?></p>
				</td>

			</tr>

			<tr>

			<?php if( isset( $rcp_options['group_invite_email'] ) && empty( $rcp_options['disable_group_invite_email'] ) ) : ?>
				<th class="row">
					<label for="rcpga-group-member-disable-invite"><label for="rcpga-group-member-disable-invite"><?php _e( 'Disable Invite', 'rcp-group-accounts' ); ?></label></label>
				</th>
				<td>
					<input type="checkbox" name="rcpga-disable-invite-email" id="rcpga-group-member-disable-invite" />
					<?php _e( 'Disable the church invite email and automatically add these users to the group.', 'rcp-group-accounts' ); ?>
					<?php if ( empty( $rcp_options['disable_new_user_notices'] ) ) : ?>
						<?php _e( '(If a new users are created, then the new user notification will be sent out.)', 'rcp-group-accounts' ); ?>
					<?php endif; ?>
				</td>
			<?php endif; ?>

			</tr>

		</table>

		<input type="hidden" name="rcpga-group" value="<?php echo absint( $_GET['rcpga-group'] ) ?>" />
		<input type="hidden" name="rcpga-action" value="import-members" />
		<input type="hidden" name="rcpga-view" value="view-members&amp;rcpga-group=<?php echo absint( $_GET['rcpga-group'] ); ?>" />

		<?php submit_button( __( 'Import Members From CSV File', 'rcp-group-accounts' ) ); ?>

	</form>

</div>
