<?php
$group_id = absint( $_GET['rcpga-group'] );
$group    = rcpga_group_accounts()->groups->get( $group_id );
$owner    = get_userdata( $group->owner_id );
?>
<div class="wrap">

	<h2><?php _e( 'Edit Group', 'rcp-group-accounts' ); ?></h2>

	<form method="post">

		<table class="form-table">

			<tr class="form-row form-required">

				<th scope="row">
					<label for="rcpga-group-name"><?php _e( 'Church', 'rcp-group-accounts' ); ?></label>
				</th>

				<td>
					<input type="text" name="rcpga-group-name" id="rcpga-group-name" class="regular-text" autocomplete="off" value="<?php echo esc_attr( stripslashes( $group->name ) ); ?>"/>
					<p class="description"><?php _e( 'The name of this church', 'rcp-group-accounts' ); ?></p>
				</td>

			</tr>

			<tr class="form-row form-required">

				<th scope="row">
					<label for="rcpga-group-description"><?php _e( 'Description', 'rcp-group-accounts' ); ?></label>
				</th>

				<td>
					<?php wp_editor( wp_kses_post( wptexturize( $group->description )  ), 'rcpga-group-description', array( 'textarea_name' => 'rcpga-group-description', 'media_buttons' => false,  'editor_height' => 180 ) ); ?>
					<p class="description"><?php _e( 'The description of this church', 'rcp-group-accounts' ); ?></p>
				</td>

			</tr>

			<tr class="form-row form-required">

				<th scope="row">
					<label for="rcpga-owner-user-name"><?php _e( 'Church Administrator', 'rcp-group-accounts' ); ?></label>
				</th>

				<td>
					<input name="rcpga-owner-user-name" type="text" disabled="disabled" class="rcp-user-search" autocomplete="off" value="<?php echo esc_attr( $owner->user_login ); ?>" />
					<p class="description"><?php _e( 'Church administrators cannot be changed', 'rcp-group-accounts' ); ?></p>
				</td>

			</tr>

			<tr class="form-row form-required">

				<th scope="row">
					<label for="rcpga-group-seats"><?php _e( 'Seats', 'rcp-group-accounts' ); ?></label>
				</th>

				<td>
					<input type="number" min="1" step="1" name="rcpga-group-seats" id="rcpga-group-seats" class="regular-text" autocomplete="off" value="<?php echo absint( $group->seats ); ?>" />
					<p class="description"><?php _e( 'The number of seats for this group', 'rcp-group-accounts' ); ?></p>
				</td>

			</tr>

            <tr class="form-row form-required">
                <th scope="row">
                    <label for="rcpga-group-address1"><?php _e( 'Address', 'rcp-group-accounts' ); ?></label>
                </th>
                <td>
                    <input type="text"  size="100" maxlength="100" name="rcpga-group-address1" id="rcpga-group-address1" placeholder="<?php _e( 'Address Line 1', 'rcp-group-accounts' ); ?>" value="<?php echo esc_attr( rcpga_group_accounts()->groups-> get_colval( $group_id,'address1' ) ); ?>" autocomplete="off" />
                </td>
            </tr>
            <tr class="form-row form-required">
                <th scope="row">
                    <label for="rcpga-group-address2">&nbsp;</label>
                </th>
                <td>
                    <input type="text"  size="100" maxlength="100" name="rcpga-group-address2" id="rcpga-group-address2" placeholder="<?php _e( 'Address Line 2', 'rcp-group-accounts' ); ?>" value="<?php echo esc_attr( rcpga_group_accounts()->groups-> get_colval( $group_id,'address2' ) ); ?>" autocomplete="off" />
                </td>
            </tr>
            <tr class="form-row form-required">
                <th scope="row">
                    <label for="rcpga-group-city"><?php _e( 'City', 'rcp-group-accounts' ); ?></label>
                </th>
                <td>
                    <input type="text"  size="50" maxlength="50" name="rcpga-group-city" id="rcpga-group-city" placeholder="<?php _e( 'City', 'rcp-group-accounts' ); ?>" value="<?php echo esc_attr( rcpga_group_accounts()->groups-> get_colval( $group_id,'city' ) ); ?>" autocomplete="off" />
                </td>
            </tr>
            <tr class="form-row form-required">
                <th scope="row">
                    <label for="rcpga-group-state"><?php _e( 'State', 'rcp-group-accounts' ); ?></label>
                </th>
                <td>
                    <?php
                    $group_edit_state = rcpga_group_accounts()->groups-> get_colval( $group_id,'state' );
                    ?>
                    <select  data-val="true" data-val-required="*" name="rcpga-group-state" id="rcpga-group-state" aria-required="true" aria-invalid="false">    <option value=""> -- Select a state -- </option>
                        <option <?php selected( $group_edit_state, 'AK'); ?> value="AK">AK</option>
                        <option <?php selected( $group_edit_state, 'AL'); ?> value="AL">AL</option>
                        <option <?php selected( $group_edit_state, 'AR'); ?> value="AR">AR</option>
                        <option <?php selected( $group_edit_state, 'AZ'); ?> value="AZ">AZ</option>
                        <option <?php selected( $group_edit_state, 'CA'); ?> value="CA">CA</option>
                        <option <?php selected( $group_edit_state, 'CO'); ?> value="CO">CO</option>
                        <option <?php selected( $group_edit_state, 'CT'); ?> value="CT">CT</option>
                        <option <?php selected( $group_edit_state, 'DC'); ?> value="DC">DC</option>
                        <option <?php selected( $group_edit_state, 'DE'); ?> value="DE">DE</option>
                        <option <?php selected( $group_edit_state, 'FL'); ?> value="FL">FL</option>
                        <option <?php selected( $group_edit_state, 'GA'); ?> value="GA">GA</option>
                        <option <?php selected( $group_edit_state, 'HI'); ?> value="HI">HI</option>
                        <option <?php selected( $group_edit_state, 'IA'); ?> value="IA">IA</option>
                        <option <?php selected( $group_edit_state, 'ID'); ?> value="ID">ID</option>
                        <option <?php selected( $group_edit_state, 'IL'); ?> value="IL">IL</option>
                        <option <?php selected( $group_edit_state, 'IN'); ?> value="IN">IN</option>
                        <option <?php selected( $group_edit_state, 'KS'); ?> value="KS">KS</option>
                        <option <?php selected( $group_edit_state, 'KY'); ?> value="KY">KY</option>
                        <option <?php selected( $group_edit_state, 'LA'); ?> value="LA">LA</option>
                        <option <?php selected( $group_edit_state, 'MA'); ?> value="MA">MA</option>
                        <option <?php selected( $group_edit_state, 'MD'); ?> value="MD">MD</option>
                        <option <?php selected( $group_edit_state, 'ME'); ?> value="ME">ME</option>
                        <option <?php selected( $group_edit_state, 'MI'); ?> value="MI">MI</option>
                        <option <?php selected( $group_edit_state, 'MN'); ?> value="MN">MN</option>
                        <option <?php selected( $group_edit_state, 'MO'); ?> value="MO">MO</option>
                        <option <?php selected( $group_edit_state, 'MS'); ?>  value="MS">MS</option>
                        <option <?php selected( $group_edit_state, 'MT'); ?>  value="MT">MT</option>
                        <option <?php selected( $group_edit_state, 'NC'); ?>  value="NC">NC</option>
                        <option <?php selected( $group_edit_state, 'ND'); ?>  value="ND">ND</option>
                        <option <?php selected( $group_edit_state, 'NE'); ?>  value="NE">NE</option>
                        <option <?php selected( $group_edit_state, 'NH'); ?>  value="NH">NH</option>
                        <option <?php selected( $group_edit_state, 'NJ'); ?>  value="NJ">NJ</option>
                        <option <?php selected( $group_edit_state, 'NM'); ?>  value="NM">NM</option>
                        <option <?php selected( $group_edit_state, 'NV'); ?>  value="NV">NV</option>
                        <option <?php selected( $group_edit_state, 'NY'); ?>  value="NY">NY</option>
                        <option <?php selected( $group_edit_state, 'OH'); ?>  value="OH">OH</option>
                        <option <?php selected( $group_edit_state, 'OK'); ?>  value="OK">OK</option>
                        <option <?php selected( $group_edit_state, 'OR'); ?>  value="OR">OR</option>
                        <option <?php selected( $group_edit_state, 'PA'); ?>  value="PA">PA</option>
                        <option <?php selected( $group_edit_state, 'PR'); ?>  value="PR">PR</option>
                        <option <?php selected( $group_edit_state, 'RI'); ?>  value="RI">RI</option>
                        <option <?php selected( $group_edit_state, 'SC'); ?>  value="SC">SC</option>
                        <option <?php selected( $group_edit_state, 'SD'); ?>  value="SD">SD</option>
                        <option <?php selected( $group_edit_state, 'TN'); ?>  value="TN">TN</option>
                        <option <?php selected( $group_edit_state, 'TX'); ?>  value="TX">TX</option>
                        <option <?php selected( $group_edit_state, 'UT'); ?>  value="UT">UT</option>
                        <option <?php selected( $group_edit_state, 'VA'); ?>  value="VA">VA</option>
                        <option <?php selected( $group_edit_state, 'VT'); ?>  value="VT">VT</option>
                        <option <?php selected( $group_edit_state, 'WA'); ?>  value="WA">WA</option>
                        <option <?php selected( $group_edit_state, 'WI'); ?>  value="WI">WI</option>
                        <option <?php selected( $group_edit_state, 'WV'); ?>  value="WV">WV</option>
                        <option <?php selected( $group_edit_state, 'WY'); ?>  value="WY">WY</option>
                        <option <?php selected( $group_edit_state, 'Alberta'); ?>  value="Alberta">Alberta</option>
                        <option <?php selected( $group_edit_state, 'British Columbia'); ?>  value="British Columbia">British Columbia</option>
                        <option <?php selected( $group_edit_state, 'Manitoba'); ?>  value="Manitoba">Manitoba</option>
                        <option <?php selected( $group_edit_state, 'New Brunswick'); ?>  value="New Brunswick">New Brunswick </option>
                        <option <?php selected( $group_edit_state, 'Nova Scotia'); ?>  value="Nova Scotia">Nova Scotia</option>
                        <option <?php selected( $group_edit_state, 'Ontario'); ?>  value="Ontario">Ontario</option>
                        <option <?php selected( $group_edit_state, 'Prince Edward Is'); ?>  value="Prince Edward Is">Prince Edward Is</option>
                        <option <?php selected( $group_edit_state, 'Quebec'); ?>  value="Quebec">Quebec</option>
                        <option <?php selected( $group_edit_state, 'Saskatchewan'); ?>  value="Saskatchewan">Saskatchewan</option>
                        <option <?php selected( $group_edit_state, 'Newfoundland &amp; Lab'); ?>  value="Newfoundland &amp; Lab">Newfoundland &amp; Lab</option>
                    </select>
                </td>
            </tr>
            <tr class="form-row form-required">
                <th scope="row">
                    <label for="rcpga-group-zip"><?php _e( 'Zip', 'rcp-group-accounts' ); ?></label>
                </th>
                <td>
                    <input type="text"  size="10" maxlength="10" name="rcpga-group-zip" id="rcpga-group-city" placeholder="<?php _e( 'Zip Code', 'rcp-group-accounts' ); ?>" value="<?php echo esc_attr( rcpga_group_accounts()->groups-> get_colval( $group_id,'zip' ) ); ?>" autocomplete="off" />
                </td>
            </tr>

		</table>

		<input type="hidden" name="rcpga-group" id="rcpga-group" value="<?php echo esc_attr( $group->group_id ); ?>" />
		<input type="hidden" name="rcpga-action" value="edit-group" />

		<?php submit_button( __( 'Update Church', 'rcp-group-accounts' ) ); ?>

	</form>

</div>
