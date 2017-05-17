/*global jQuery, document, console, rcp_site_creation_vars*/
/*jslint newcap: true*/
jQuery(document).ready(function ($) {
	'use strict';

	var rcpgaLevelMap = window.rcpgaLevelMap || {};
	var $groupFields  = $('.rcpga-group-fields');

	if (! $groupFields.length) {
		return;
	}

	var display = $groupFields.css('display');

	// Get selected level, if any
	var level = $('.rcp_level:checked').val();

	/**
	 * If there's no selected level, find the first named input.
	 * This makes it work with [register_form id=xx], or on pages
	 * where the level is not already pre-selected.
	 */
	if (undefined == level || ! level.length) {
		var level = $("input[name='rcp_level']").val();
	}

	// Adjust group field visibility based on level selected on page load
	if ( -1 !== rcpgaLevelMap.indexOf(level) ) {
		$groupFields.css('display', display);
	} else {
		$groupFields.css('display', 'none');
	}

	// Adjust group field visibility on change events
	$('input.rcp_level').change( function () {

		var level = $('.rcp_level:checked').val();

		if ( -1 !== rcpgaLevelMap.indexOf(level) ) {
			$groupFields.css('display', display);
		} else {
			$groupFields.css('display', 'none');
		}
	});

});
