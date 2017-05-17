/*global jQuery, document*/
/*jslint newcap: true*/
jQuery(document).ready(function ($) {
	'use strict';

	$('#rcpga-group-seats-allow').change(function () {
		if ($(this).is(':checked')) {
			$('#rcpga-group-seats').parents('tr').css('display', 'table-row');
		} else {
			$('#rcpga-group-seats').parents('tr').css('display', 'none');
		}
	}).change();

	$('#rcpga-add-member-is-new').change(function () {
		if ($(this).is(':checked')) {
			$('.rcpga-new-member-field').parents('tr').css('display', 'table-row');
		} else {
			$('.rcpga-new-member-field').parents('tr').css('display', 'none');
		}
	}).change();

	$('.rcpga-group-delete').click(function(e) {

		if( ! confirm( rcpga_group_vars.delete_group ) ) {
			return false;
		}
	});

	$('.rcp-member-delete').click(function(e) {

		if( ! confirm( rcpga_group_vars.delete_member ) ) {
			return false;
		}
	});
});
