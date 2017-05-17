<?php

/**
 * Add a member to a group. If the user does not exist then create a new user.
 *
 * @param $args
 *
 * @return int|WP_Error
 */
function rcpga_add_member_to_group( $args ) {

	global $rcp_options;

	$default = array(
		'user_email'            => '',
		'group_id'              => 0,
		'send_invite'           => true,
	);

	$args = wp_parse_args( $args, $default );

	// if we are sending an invite, use the Invited role
	if ( empty( $args['role'] ) && ! empty( $args['send_invite'] ) ) {
		$args['role'] = 'rcp-invited';
	}

	$args = apply_filters( 'rcpga_invite_user_args', $args );

	// make sure we have required information
	if ( empty( $args['user_email'] ) ) {
		return new WP_Error( 'empty-email', __( 'Please enter a valid email address.', 'rcp-group-account' ) );
	}

	if ( empty( $args['group_id'] ) ) {
		return new WP_Error( 'no-group', __( 'Please specify a group ID.', 'rcp-group-account' ) );
	}

	// use the email as the new user's login if it is not already set
	if ( empty( $args['user_login'] ) ) {
		$args['user_login'] = $args['user_email'];
	}

	// create a new user if one does not already exist
	if ( $user = get_user_by( 'email', $args['user_email'] ) ) {
		$user_id = $user->ID;
	} else {
		$user_id = wp_insert_user( $args );
	}

	// make sure we don't have any errors
	if ( is_wp_error( $user_id ) ) {
		return $user_id;
	}

	// active members cannot be added to a group
	if ( rcp_is_active( $user_id ) && rcp_get_subscription_id( $user_id ) ) {
		return new WP_Error( 'active-user', __( 'Members with an active subscription cannot be added to a group.', 'rcp-group-account' ) );
	}

	// make sure this user does not already belong to a group
	if ( rcpga_group_accounts()->members->get_group_id( $user_id ) ) {
		return new WP_Error( 'has-group', __( 'This user is already has a group. Users may only be part of one group at a time.', 'rcp-group-account' ) );
	}

	$member_add_args = apply_filters( 'rcpga_add_member_to_group_args', array(
		'user_id'  => $user_id,
		'group_id' => $args['group_id'],
		'role'     => ( isset( $args['role'] ) && 'rcp-invited' == $args['role'] ) ? 'invited' : 'member',
	) );

	// add the member to the group
	rcpga_group_accounts()->members->add( $member_add_args );

	if ( empty( $args['send_invite'] ) || empty( $rcp_options['disable_new_user_notices'] ) ) {
		$notify = 'both';

		if ( ! empty( $rcp_options['disable_new_user_notices'] ) ) {
			// Send notification to user only.
			$notify = 'user';
		} elseif ( ! empty( $args['send_invite'] ) ) {
			// Send notification to admin only.
			$notify = 'admin';
		}

		rcpga_send_new_user_notifications( $user_id, $notify );
	}

	if ( ! empty( $args['send_invite'] ) ) {
		rcpga_send_group_invite( $user_id );
	} else {
		// if this user is not invited, then update their user_role to the group role
		rcpga_maybe_update_member_role( $user_id );
	}

	do_action( 'rcpga_add_member_to_group_after', $user_id, $args );

	return $user_id;

}

/**
 * Handle sending invites to group members
 *
 * @param $user_id
 *
 * @return bool
 */
function rcpga_send_group_invite( $user_id ) {
	global $rcp_options;

	if ( ! $user = get_user_by( 'id', $user_id ) ) {
		return false;
	}

	$email['to'] = $user->user_email;
	$email['subject'] = ( isset( $rcp_options['group_invite_subject'] ) ) ? $rcp_options['group_invite_subject'] : '';
	$email['message'] = ( isset( $rcp_options['group_invite_email'] ) ) ? $rcp_options['group_invite_email'] : '';

	// Template tags and headers are processed in the RCP_Emails class in RCP 2.7+. This is here for backwards compatibility only.
	if ( ! class_exists( 'RCP_Emails' ) ) {
		$email['subject'] = rcp_filter_email_tags( $email['subject'], $user->ID, $user->display_name );
		$email['message'] = rcp_filter_email_tags( $email['message'], $user->ID, $user->display_name );

		$site_name      = stripslashes_deep( html_entity_decode( get_bloginfo('name'), ENT_COMPAT, 'UTF-8' ) );

		$from_name      = isset( $rcp_options['from_name'] ) ? $rcp_options['from_name'] : $site_name;
		$from_name      = apply_filters( 'rcp_emails_from_name', $from_name, $user_id, 'group_invite' );

		$from_email     = isset( $rcp_options['from_email'] ) ? $rcp_options['from_email'] : get_option( 'admin_email' );
		$from_email     = apply_filters( 'rcp_emails_from_address', $from_email );

		$headers        = "From: " . stripslashes_deep( html_entity_decode( $from_name, ENT_COMPAT, 'UTF-8' ) ) . " <$from_email>\r\n";
		$headers       .= "Reply-To: ". $from_email . "\r\n";
		$headers        = apply_filters( 'rcp_email_headers', $headers, $user_id, 'group_invite' );

		$email['headers'] = $headers;
	}

	$email = apply_filters( 'rcpga_send_group_invite_args', $email, $user_id );

	if ( empty( $email['subject'] ) || empty( $email['message'] ) || empty( $email['to'] ) ) {
		return false;
	}

	do_action( 'rcpga_send_group_invite', $user_id, $email );

	if ( class_exists( 'RCP_Emails' ) ) {

		$emails = new RCP_Emails;
		$emails->member_id = $user_id;
		
		return $emails->send( $email['to'], $email['subject'], $email['message'] );

	} else {
		return wp_mail( $email['to'], $email['subject'], $email['message'], $email['headers'] );
	}

}

/**
 * Generate the link used for group member invites
 *
 * @param $user_id
 *
 * @return bool|mixed|void
 */
function rcpga_group_invite_link( $user_id ) {

	// user must exist, at least as a pending user
	if ( ! $user = get_user_by( 'id', $user_id ) ) {
		return false;
	}

	// the user should already be added to the group as an invited member
	if ( ! 'invited' === rcpga_group_accounts()->members->get_role( $user_id ) ) {
		return false;
	}

	$login_link = add_query_arg( array(
		'rcpga-invite-key' => urlencode( $user->user_pass ),
		'rcpga-user'       => urlencode( $user->user_email )
	), trailingslashit( home_url() ) );

	return apply_filters( 'rcpga_group_invite_link', $login_link, $user_id );

}

/**
 * Handle group email template tags
 *
 * @deprecated 1.1 Template tags are now registered via rcpga_register_email_template_tags()
 *
 * @param $message
 * @param $user_id
 *
 * @return mixed
 */
function rcpga_filter_email_tags( $message, $user_id ) {

	if ( class_exists( 'RCP_Emails' ) ) {
		return $message;
	}

	$group_id = rcpga_group_accounts()->members->get_group_id( $user_id );

	if ( ! $name = rcpga_group_accounts()->groups->get_name( $group_id ) ) {
		$name = '';
	}

	if ( ! $desc = rcpga_group_accounts()->groups->get_description( $group_id ) ) {
		$desc = '';
	}

	if ( ! $invite_link = rcpga_group_invite_link( $user_id ) ) {
		$invite_link = '';
	}

	$message = str_replace( '%groupname%', $name, $message );
	$message = str_replace( '%groupdesc%', $desc, $message );
	$message = str_replace( '%invitelink%', $invite_link, $message );

	return $message;

}
add_filter( 'rcp_email_tags', 'rcpga_filter_email_tags', 10, 2 );

/**
 * Register group email template tags
 *
 * @param array $email_tags
 *
 * @since 1.1
 * @return array
 */
function rcpga_register_email_template_tags( $email_tags ) {

	$email_tags[] = array(
		'tag'         => 'groupname',
		'description' => __( 'The name of the group to which person receiving the email belongs or is being invited', 'rcp-group-accounts' ),
		'function'    => 'rcpga_email_tag_group_name'
	);

	$email_tags[] = array(
		'tag'         => 'groupdesc',
		'description' => __( 'The description of the group to which person receiving the email belongs or is being invited', 'rcp-group-accounts' ),
		'function'    => 'rcpga_email_tag_group_desc'
	);

	$email_tags[] = array(
		'tag'         => 'invitelink',
		'description' => __( 'The invite link the user will click to join the group, only for the Group Invite Email', 'rcp-group-accounts' ),
		'function'    => 'rcpga_email_tag_invite_link'
	);

	return $email_tags;

}
add_filter( 'rcp_email_template_tags', 'rcpga_register_email_template_tags' );

/**
 * Email template tag: groupname
 * Name of the group the user has been invited to.
 *
 * @param int    $user_id    ID of the member receiving the email.
 * @param int    $payment_id The ID of the latest payment made by the user.
 * @param string $tag        Name of the tag being processed.
 *
 * @since 1.1
 * @return string
 */
function rcpga_email_tag_group_name( $user_id, $payment_id, $tag ) {
	$group_id = rcpga_group_accounts()->members->get_group_id( $user_id );

	if ( ! $name = rcpga_group_accounts()->groups->get_name( $group_id ) ) {
		$name = '';
	}

	return $name;
}

/**
 * Email template tag: groupdesc
 * Description fo the group the user has been invited to.
 *
 * @param int    $user_id    ID of the member receiving the email.
 * @param int    $payment_id The ID of the latest payment made by the user.
 * @param string $tag        Name of the tag being processed.
 *
 * @since 1.1
 * @return string
 */
function rcpga_email_tag_group_desc( $user_id, $payment_id, $tag ) {
	$group_id = rcpga_group_accounts()->members->get_group_id( $user_id );

	if ( ! $desc = rcpga_group_accounts()->groups->get_description( $group_id ) ) {
		$desc = '';
	}

	return $desc;
}

/**
 * Template tag: invitelink
 * Invitation link to join the group.
 *
 * @param int    $user_id    ID of the member receiving the email.
 * @param int    $payment_id The ID of the latest payment made by the user.
 * @param string $tag        Name of the tag being processed.
 *
 * @since 1.1
 * @return string
 */
function rcpga_email_tag_invite_link( $user_id, $payment_id, $tag ) {
	if ( ! $invite_link = rcpga_group_invite_link( $user_id ) ) {
		$invite_link = '';
	}

	return $invite_link;
}

/**
 * Update a user's role to match the group account role
 *
 * @param $user_id
 *
 * @return bool
 */
function rcpga_maybe_update_member_role( $user_id ) {
	$member = new RCP_Member( $user_id );

	// make sure this user has a group
	if ( ! $group_id = rcpga_group_accounts()->members->get_group_id( $user_id ) ) {
		return false;
	}

	// get the role for this group
	if ( ! $role = rcpga_group_accounts()->groups->get_group_role( $group_id ) ) {
		return false;
	}

	global $rcp_options;

	$role = apply_filters( 'rcpga_group_account_role', $role, $user_id );

	// Check for the invited role
	if ( false !== array_search( 'rcp-invited', (array) $member->roles ) ) {

		$member->remove_role( 'rcp-invited' );
		$member->add_role( $role );

		// This is a new user, send the welcome email unless disabled
		$notify = false;

		if ( ! empty( $rcp_options['disable_new_user_notices'] ) && empty( $rcp_options['disable_group_welcome_email'] ) ) {
			// Send notification to user only.
			$notify = 'user';
		} elseif ( empty( $rcp_options['disable_new_user_notices'] ) && ! empty( $args['disable_group_welcome_email'] ) ) {
			// Send notification to admin only.
			$notify = 'admin';
		} elseif( empty( $rcp_options['disable_new_user_notices'] ) && empty( $rcp_options['disable_group_welcome_email'] ) ) {
			// Send notification to both user and admin.
			$notify = 'both';
		}

		if ( apply_filters( 'rcpga_send_invite_welcome_email', $notify, $member ) ) {
			// Backwards compatibility check in case $notify is just `true`.
			$notify = in_array( $notify, array( 'both', 'user', 'admin' ) ) ? $notify : 'both';

			rcpga_send_new_user_notifications( $member->ID, $notify );
		}

	} else {

		// get the sites default role
		$old_role = get_option( 'default_role', 'subscriber' );

		// check for an existing subscription role
		if ( $level_id = rcp_get_subscription_id( $member->ID ) ) {
			$level    = rcp_get_subscription_details( $level_id );
			$old_role = ! empty( $level->role ) ? $level->role : $old_role;
		}

		$member->remove_role( $old_role );
		$member->add_role( $role );

	}

	return true;

}

/**
 * Determine if this user should have a group and create one if it doesn't exist
 *
 * @param $user_id
 *
 * @return bool|false|int
 */
function rcpga_maybe_create_member_group( $user_id ) {

	if ( rcpga_group_accounts()->members->get_group_id( $user_id ) ) {
		return false;
	}

	$create_group = true;

	// make sure member has an active account
	if ( 'free' != rcp_get_status( $user_id ) && ! rcp_is_active( $user_id ) ) {
		$create_group = false;
	}

	$level_id   = rcp_get_subscription_id( $user_id );
	$seat_count = rcpga_get_level_group_seats_allowed( $level_id );

	// make sure the user's subscription level supports groups
	if ( empty( $seat_count ) ) {
		$create_group = false;
	}

	if ( ! apply_filters( 'rcpga_maybe_create_member_group', $create_group, $user_id ) ) {
		return false;
	}

	$args = array(
		'owner_id' => $user_id,
		'seats'    => $seat_count,
	);

	return rcpga_group_accounts()->groups->add( $args );

}

/**
 * Removes the group seats metadata for the specified subscription level.
 *
 * @param int $subscription_id The subscription level ID.
 */
function rcpga_remove_level_seat_count( $level_id ) {

	global $rcp_levels_db;

	$rcp_levels_db->delete_meta( $level_id, 'group_seats_allowed' );

	do_action( 'rcpga_remove_level_seat_count', $level_id );

}
add_action( 'rcp_remove_level', 'rcpga_remove_level_seat_count' );

/**
 * Gets the number of seats allowed for the specified subscription level.
 *
 * @param int $level_id The subscription level ID.
 *
 * @return integer The number of seats allowed for the specified subscription level.
 */
function rcpga_get_level_group_seats_allowed( $level_id ) {

	global $rcp_levels_db;

	$count = $rcp_levels_db->get_meta( $level_id, 'group_seats_allowed', true );

	return apply_filters( 'rcpga_get_level_group_seats_allowed', absint( $count ), $level_id );

}

/**
 * Check if group accounts are disabled for this level.
 * Note, this does not check the number of seats for this level, which could be 0.
 *
 * @param $level_id
 *
 * @return bool True if the level is group enabled, false if not.
 */
function rcpga_is_level_group_accounts_enabled( $level_id ) {

	global $rcp_levels_db;

	$enabled = (bool) $rcp_levels_db->get_meta( $level_id, 'group_seats_enabled', true );

	return apply_filters( 'rcpga_is_level_group_accounts_enabled', $enabled, $level_id );
}

/**
 * Sets the number of group seats allowed for the specified subscription level.
 *
 * @param int $level_id The subscription level ID.
 * @param int $seats_allowed The number of seats allowed.
 */
function rcpga_set_level_group_seats_allowed( $level_id, $seats_allowed ) {

	global $rcp_levels_db;

	$rcp_levels_db->update_meta( $level_id, 'group_seats_allowed', absint( $seats_allowed ) );

	do_action( 'rcpga_set_level_group_seats_allowed', $level_id, absint( $seats_allowed ) );

}

/**
 * Disable group accounts for this level
 *
 * @param $level_id
 */
function rcpga_disable_level_group_accounts( $level_id ) {

	global $rcp_levels_db;

	$rcp_levels_db->delete_meta( $level_id, 'group_seats_enabled' );

	do_action( 'rcpga_disable_level_group_accounts', $level_id );

}

/**
 * Disable group accounts for this level
 *
 * @param $level_id
 */
function rcpga_enable_level_group_accounts( $level_id ) {

	global $rcp_levels_db;

	$rcp_levels_db->update_meta( $level_id, 'group_seats_enabled', true );

	do_action( 'rcpga_enable_level_group_accounts', $level_id );

}

/**
 * Return all levels that are enabled for group accounts
 *
 * @return mixed|void
 */
function rcpga_get_group_enabled_levels() {

	global $rcp_levels_db, $wpdb;

	$enabled_levels = $wpdb->get_results( $wpdb->prepare( "SELECT level_id FROM {$wpdb->levelmeta} WHERE meta_key = %s", 'group_seats_enabled' ), ARRAY_A );

	return apply_filters( 'rcpga_get_group_enabled_levels', wp_list_pluck( $enabled_levels, 'level_id' ) );
}

/**
 * Send new user notifications
 *
 * @param int    $user_id ID of the user new user.
 * @param string $notify  Who to notify: 'both', 'admin', or 'user'.
 *
 * @since 1.1.1
 * @return void
 */
function rcpga_send_new_user_notifications( $user_id, $notify = 'both' ) {

	/**
	 * After the password reset key is generated and before the email body is created,
	 * add our filter to replace the URLs in the email body.
	 */
	add_action( 'retrieve_password_key', function() {

		add_filter( 'wp_mail', function( $args ) {

			global $rcp_options;

			if ( ! empty( $rcp_options['hijack_login_url'] ) && ! empty( $rcp_options['login_redirect'] ) ) {

				// Rewrite the password reset link
				$args['message'] = str_replace( trailingslashit( network_site_url() ) . 'wp-login.php?action=rp', get_permalink( $rcp_options['login_redirect'] ) . '?rcp_action=lostpassword_reset', $args['message'] );

			}

			return $args;

		});

	});

	wp_send_new_user_notifications( $user_id, $notify );

}