<?php
/**
 * This is the file used for the [subscription_details] shortcode when being viewed by a
 * group member who is not the owner
 */

global $rcp_load_css;

$rcp_load_css = true;

do_action( 'rcp_subscription_details_top' );  ?>
<table class="rcp-table" id="rcp-account-overview">
	<thead>
		<tr>
			<th><?php _e( 'Church Status', 'rcp-group-accounts' ); ?></th>
			<th><?php _e( 'Church Subscription', 'rcp-group-accounts' ); ?></th>
			<?php if( rcp_is_recurring() && ! rcp_is_expired() ) : ?>
			<th><?php _e( 'Church Renewal Date', 'rcp-group-accounts' ); ?></th>
			<?php else : ?>
			<th><?php _e( 'Church Expiration', 'rcp-group-accounts' ); ?></th>
			<?php endif; ?>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td><?php rcp_print_status(); ?></td>
			<td><?php echo rcp_get_subscription(); ?></td>
			<td><?php echo rcp_get_expiration_date(); ?></td>
		</tr>
	</tbody>
</table>
<?php do_action( 'rcp_subscription_details_bottom' );