/*
 * Add a class to an element at a certain scrollHeight
 * @package Syltaen
 * @author Stanley Lambot
 * @require jQuery
 */

(function ($) {

	var items = [];

	var _update = function () {
		var s = $(window).scrollTop(),
			i;

		for(i in items) {
			if (s >= items[i].top && !items[i].hasClass ) {
				items[i].$el.addClass( items[i].class );
				items[i].hasClass = true;
			}
			else
			if (s < items[i].top && items[i].hasClass ) {
				items[i].$el.removeClass( items[i].class );
				items[i].hasClass = false;
			}

		}
	}

	$.fn.addClassAt = function (scrollTop, classToAdd) {
		var toAdd = $('#wpadminbar').length ? $('#wpadminbar').innerHeight() : 0;
		items.push({
			$el: $(this),
			top: parseInt(scrollTop, 10) + toAdd,
			hasClass: false,
			class: classToAdd
		});
		return $(this);
	};

	$(window).scroll( _update );

}) (jQuery);
