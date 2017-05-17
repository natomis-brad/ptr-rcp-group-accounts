<div class="wrap">

	<h2><?php _e( 'New Group', 'rcp-group-accounts' ); ?></h2>

	<form method="post">

		<table class="form-table">

			<tr class="form-row form-required">

				<th scope="row">
					<label for="rcpga-group-name"><?php _e( 'Church', 'rcp-group-accounts' ); ?></label>
				</th>

				<td>
					<input type="text" name="rcpga-group-name" id="rcpga-group-name" class="regular-text" autocomplete="off" />
					<p class="description"><?php _e( 'The name of this church', 'rcp-group-accounts' ); ?></p>
				</td>

			</tr>

			<tr class="form-row form-required">

				<th scope="row">
					<label for="rcpga-group-description"><?php _e( 'Description', 'rcp-group-accounts' ); ?></label>
				</th>

				<td>
					<?php wp_editor( '', 'rcpga-group-description', array( 'textarea_name' => 'rcpga-group-description', 'media_buttons' => false ) ); ?>
					<p class="description"><?php _e( 'The description of this church', 'rcp-group-accounts' ); ?></p>
				</td>

			</tr>

			<tr class="form-row form-required">

				<th scope="row">
					<label for="rcpga-user-email"><?php _e( 'Church Administrator', 'rcp-group-accounts' ); ?></label>
				</th>

				<td>
					<input type="text" name="rcpga-user-email" id="rcpga-user-email" />
					<p class="description"><?php _e( 'Enter the email address of the user account to set as the church administrator.', 'rcp-group-accounts' ); ?></p>
				</td>

			</tr>

			<tr class="form-row form-required">

				<th scope="row">
					<label for="rcpga-group-seats"><?php _e( 'Seats', 'rcp-group-accounts' ); ?></label>
				</th>

				<td>
					<input type="number" min="1" step="1" name="rcpga-group-seats" id="rcpga-group-seats" class="regular-text" autocomplete="off" />
					<p class="description"><?php _e( 'The number of seats for this church', 'rcp-group-accounts' ); ?></p>
				</td>

			</tr>

            <tr class="form-row form-required">
                <th scope="row">
                    <label for="rcpga-group-address1"><?php _e( 'Address', 'rcp-group-accounts' ); ?></label>
                </th>
                <td>
                    <input type="text"  size="100" maxlength="100" name="rcpga-group-address1" id="rcpga-group-address1" placeholder="<?php _e( 'Address Line 1', 'rcp-group-accounts' ); ?>" class="regular-text" autocomplete="off" />
                </td>
            </tr>
            <tr class="form-row form-required">
                <th scope="row">
                    <label for="rcpga-group-address2">&nbsp;</label>
                </th>
                <td>
                    <input type="text"  size="100" maxlength="100" name="rcpga-group-address2" id="rcpga-group-address2" placeholder="<?php _e( 'Address Line 2', 'rcp-group-accounts' ); ?>"  class="regular-text" autocomplete="off" />
                </td>
            </tr>
            <tr class="form-row form-required">
                <th scope="row">
                    <label for="rcpga-group-city"><?php _e( 'City', 'rcp-group-accounts' ); ?></label>
                </th>
                <td>
                    <input type="text"  size="50" maxlength="50" name="rcpga-group-city" id="rcpga-group-city" placeholder="<?php _e( 'City', 'rcp-group-accounts' ); ?>"  class="regular-text" autocomplete="off" />
                </td>
            </tr>
            <tr class="form-row form-required">
                <th scope="row">
                    <label for="rcpga-group-state"><?php _e( 'State', 'rcp-group-accounts' ); ?></label>
                </th>
                <td>
                    <select  data-val="true" data-val-required="*" name="rcpga-group-state" id="rcpga-group-state" aria-required="true" aria-invalid="false">
                        <option value=""> -- Select a state -- </option>
                        <option value="AK">AK</option>
                        <option value="AL">AL</option>
                        <option value="AR">AR</option>
                        <option value="AZ">AZ</option>
                        <option value="CA">CA</option>
                        <option value="CO">CO</option>
                        <option value="CT">CT</option>
                        <option value="DC">DC</option>
                        <option value="DE">DE</option>
                        <option value="FL">FL</option>
                        <option value="GA">GA</option>
                        <option value="HI">HI</option>
                        <option value="IA">IA</option>
                        <option value="ID">ID</option>
                        <option value="IL">IL</option>
                        <option value="IN">IN</option>
                        <option value="KS">KS</option>
                        <option value="KY">KY</option>
                        <option value="LA">LA</option>
                        <option value="MA">MA</option>
                        <option value="MD">MD</option>
                        <option value="ME">ME</option>
                        <option value="MI">MI</option>
                        <option value="MN">MN</option>
                        <option value="MO">MO</option>
                        <option value="MS">MS</option>
                        <option value="MT">MT</option>
                        <option value="NC">NC</option>
                        <option value="ND">ND</option>
                        <option value="NE">NE</option>
                        <option value="NH">NH</option>
                        <option value="NJ">NJ</option>
                        <option value="NM">NM</option>
                        <option value="NV">NV</option>
                        <option value="NY">NY</option>
                        <option value="OH">OH</option>
                        <option value="OK">OK</option>
                        <option value="OR">OR</option>
                        <option value="PA">PA</option>
                        <option value="PR">PR</option>
                        <option value="RI">RI</option>
                        <option value="SC">SC</option>
                        <option value="SD">SD</option>
                        <option value="TN">TN</option>
                        <option value="TX">TX</option>
                        <option value="UT">UT</option>
                        <option value="VA">VA</option>
                        <option value="VT">VT</option>
                        <option value="WA">WA</option>
                        <option value="WI">WI</option>
                        <option value="WV">WV</option>
                        <option value="WY">WY</option>
                        <option value="Alberta">Alberta</option>
                        <option value="British Columbia">British Columbia</option>
                        <option value="Manitoba">Manitoba</option>
                        <option value="New Brunswick">New Brunswick </option>
                        <option value="Nova Scotia">Nova Scotia</option>
                        <option value="Ontario">Ontario</option>
                        <option value="Prince Edward Is">Prince Edward Is</option>
                        <option value="Quebec">Quebec</option>
                        <option value="Saskatchewan">Saskatchewan</option>
                        <option value="Newfoundland &amp; Lab">Newfoundland &amp; Lab</option>
                    </select>
                </td>
            </tr>
            <tr class="form-row form-required">
                <th scope="row">
                    <label for="rcpga-group-zip"><?php _e( 'Zip', 'rcp-group-accounts' ); ?></label>
                </th>
                <td>
                    <input type="text"  size="10" maxlength="10" name="rcpga-group-zip" id="rcpga-group-city" placeholder="<?php _e( 'Zip Code', 'rcp-group-accounts' ); ?>"  class="regular-text" autocomplete="off" />
                </td>
            </tr>

		</table>

		<input type="hidden" name="rcpga-user-id" id="rcpga-user-id" value="" />
		<input type="hidden" name="rcpga-action" value="add-group" />

		<?php submit_button( __( 'Add Church', 'rcp-group-accounts' ) ); ?>

	</form>

</div>
