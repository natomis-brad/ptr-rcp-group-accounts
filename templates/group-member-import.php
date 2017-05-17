<?php
global $rcp_options;

if ( ! did_action( 'rcpga_dashboard_notifications' ) ) {
	do_action( 'rcpga_dashboard_notifications' );
}
?>

<h4 class="rcp-header"><?php _e( 'Import Church Members', 'rcp-group-accounts' ); ?></h4>

<form method="post" action="" id="rcpga-group-member-import-form" class="rcp_form" enctype="multipart/form-data">

	<fieldset>

		<p id="rcpga-group-member-import-wrap">
			<input type="file" accept=".csv, text/csv" name="rcpga-group-csv" id="rcpga-group-member-import" />
		</p>

		<p class="rcpga-group-csv-import-sample"><?php _e( 'Bulk import accounts from a CSV file.', 'rcp-group-accounts' ); ?> <?php echo apply_filters( 'rcpga_group_csv_import_sample_link', sprintf( '<a href="%s">%s</a>', RCPGA_GROUPS_PLUGIN_URL . 'member-import-sample.csv',  __( 'Click here to see a sample CSV', 'rcp-group-accounts' ) ) ); ?></p>

		<?php if( isset( $rcp_options['group_invite_email'] ) && empty( $rcp_options['disable_group_invite_email'] ) ) : ?>
			<p id="rcpga-group-member-import-disable-invite_wrap">
				<label for="rcpga-group-member-import-disable-invite">
					<input type="checkbox" name="rcpga-disable-invite-email" id="rcpga-group-member-import-disable-invite" />
					<?php _e( 'Disable the church invite email and automatically add this user to the group.', 'rcp-group-accounts' ); ?>
					<?php if ( empty( $rcp_options['disable_new_user_notices'] ) ) : ?>
						<?php _e( '(If a new user is created, then the new user notification will be sent out.)', 'rcp-group-accounts' ); ?>
					<?php endif; ?>
				</label>
			</p>
		<?php endif; ?>

		<p class="rcp_form_wrap">
			<input type="hidden" name="rcpga-group" value="<?php echo absint( rcpga_group_accounts()->members->get_group_id() ); ?>" />
			<input type="hidden" name="rcpga-action" value="import-members" />
			<input type="submit" value="<?php _e( 'Import CSV', 'rcp-group-accounts' ); ?>" />
		</p>

	</fieldset>

</form>