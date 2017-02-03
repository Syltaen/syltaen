/*
 * Show a field under conditions
 * @package Syltaen
 * @author Stanley Lambot
 * @require jQuery
 */


(function ($) {

	function _check($field, $target, value) {

		if ($field.val() == value) {
			$target.show();
		} else {
			$target.hide();
		}
	}

	$.fn.showif = function (field_selector, value) {
		var $field = $(field_selector),
			$target = $(this);

		if ($field.length > 0) {

			_check($field, $target, value);

			$field.change(function () {
				_check($field, $target, value);
			});

		}


	};



}) (jQuery);
