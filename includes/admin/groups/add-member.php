<?php
global $rcp_options;

$group_id = absint( $_GET['rcpga-group'] );
?>

<div class="wrap">

	<h2><?php _e( 'New Church Member', 'rcp-group-accounts' ); ?></h2>

	<form method="post">

		<table class="form-table">

			<?php do_action( 'rcpga_add_member_before' ); ?>

			<tr class="form-field">

				<th scope="row" valign="top">
					<label for="rcpga-add-member-is-new"><?php _e( 'Is this a new member?', 'rcp-group-accounts' ) ?></label>
				</th>

				<td>
					<input id="rcpga-add-member-is-new" type="checkbox" name="rcpga-add-member-is-new">
					<p class="description"><?php _e( 'Check if this is a new member', 'rcp-group-accounts' ) ?></p>
				</td>

			</tr>

			<tr class="form-row form-required">

				<th scope="row">
					<label for="rcpga-user-email"><?php _e( 'User Email', 'rcp-group-accounts' ); ?></label>
				</th>

				<td>
					<input type="text" name="rcpga-user-email" id="rcpga-user-email" class="rcp-user-search" autocomplete="off" />
					<p class="description"><?php _e( 'Enter the email address of a user account to add to the group.', 'rcp-group-accounts' ); ?></p>
				</td>

			</tr>

			<tr class="form-row" style="display: none">

				<th scope="row">
					<label for="rcpga-first-name"><?php _e( 'User First Name', 'rcp-group-accounts' ); ?></label>
				</th>

				<td>
				<input type="text" name="rcpga-first-name" id="rcpga-first-name" class="rcpga-new-member-field" autocomplete="off" />
					<p class="description"><?php _e( 'Enter the first name of the user to add to the group.', 'rcp-group-accounts' ); ?></p>
				</td>

			</tr>

			<tr class="form-row" style="display: none">

				<th scope="row">
					<label for="rcpga-last-name"><?php _e( 'User Last Name', 'rcp-group-accounts' ); ?></label>
				</th>

				<td>
				<input type="text" name="rcpga-last-name" id="rcpga-last-name" class="rcpga-new-member-field" autocomplete="off" />
					<p class="description"><?php _e( 'Enter the last name of the user to add to the group.', 'rcp-group-accounts' ); ?></p>
				</td>

			</tr>

			<?php do_action( 'rcpga_add_member_after' ); ?>

			<tr>

				<?php if( isset( $rcp_options['group_invite_email'] ) && empty( $rcp_options['disable_group_invite_email'] ) ) : ?>
					<th class="row">
						<label for="rcpga-group-member-disable-invite"><label for="rcpga-group-member-disable-invite"><?php _e( 'Disable Invite', 'rcp-group-accounts' ); ?></label></label>
					</th>
					<td>
						<input type="checkbox" name="rcpga-disable-invite-email" id="rcpga-group-member-disable-invite" />
						<?php _e( 'Disable the church invite email and automatically add this user to the group.', 'rcp-group-accounts' ); ?>
						<?php if ( empty( $rcp_options['disable_new_user_notices'] ) ) : ?>
							<?php _e( '(If a new user is created, then the new user notification will be sent out.)', 'rcp-group-accounts' ); ?>
						<?php endif; ?>
					</td>
				<?php endif; ?>

			</tr>



		</table>

		<input type="hidden" name="rcpga-group" id="rcpga-group" value="<?php echo absint( $group_id ); ?>" />
		<input type="hidden" name="rcpga-action" value="add-member" />
		<input type="hidden" name="rcpga-view" value="view-members&amp;rcpga-group=<?php echo absint( $_GET['rcpga-group'] ); ?>" />

		<?php submit_button( __( 'Add Member', 'rcp-group-accounts' ) ); ?>

	</form>

</div>
