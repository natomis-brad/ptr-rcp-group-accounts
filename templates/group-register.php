<?php

// make sure this user does not already own a group
if ( rcpga_group_accounts()->members->is_group_owner() ) {
	return;
}
?>

<div class="rcpga-group-fields">
	<?php do_action( 'rcpga_register_group_before' ); ?>
	<fieldset class="rcpga-group-fieldset">
<p id="rcpga-group-address-wrap">
<table>
<tr>
<td colspan="3">
Church Name<br/>
	<input type="text" size="20" maxlength="20" name="rcpga-group-name" id="rcpga-group-name" />
</td>
</tr>
<tr>
<td colspan="3">
	Church Description<br/>
<textarea name="rcpga-group-description" id="rcpga-group-description"></textarea>
</td>
</tr>
<tr>
<td colspan="3">
Address
</td>
</tr>
<tr>
<td colspan="3">
	<input type="text" size="100" maxlength="100" name="rcpga-group-address1" id="rcpga-group-address2" />
</td>
</tr>

<tr>
<td colspan="3">
<input type="text" size="100" maxlength="100" name="rcpga-group-address2" id="rcpga-group-address2" />
</td>
</tr>
<tr>
<td>
City
</td>
<td>
State
</td>
<td>
Zip
</td>
</tr>
<tr>
<td>
<input type="text" size="50" maxlength="50" name="rcpga-group-city" id="rcpga-group-city" />
</td>
<td>
<select  data-val="true" data-val-required="*" name="rcpga-group-state" id="rcpga-group-state" aria-required="true" aria-invalid="false"><option value=""> -- Select a state -- </option>
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
<option value="New Brunswick ">New Brunswick </option>
<option value="Nova Scotia">Nova Scotia</option>
<option value="Ontario">Ontario</option>
<option value="Prince Edward Is">Prince Edward Is</option>
<option value="Quebec">Quebec</option>
<option value="Saskatchewan">Saskatchewan</option>
<option value="Newfoundland &amp; Lab">Newfoundland &amp; Lab</option>
</select>
</td>
<td>
<input type="text" size="10" maxlength="10" name="rcpga-group-zip" id="rcpga-group-zip" />
</td>
</tr>
</table>
</p>
	</fieldset>
	<?php do_action( 'rcpga_register_group_after' ); ?>

</div>