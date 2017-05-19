/*
 * Make a container closable
 * @package Syltaen
 * @author Stanley Lambot
 * @require jQuery
 */

(function ($) {

	$.fn.elevatorbox = function () {
		$(this).each(function () {
			var closedH = $(this).hasClass('elevator-box') ? 65 : $(this).find('.elevator-section-intro').innerHeight();
			$(this)
				.attr('data-open-height', 	$(this).innerHeight())
				.attr('data-closed-height', closedH)
				.removeClass('open')
				.css('height', closedH)
				.addClass('toggleable')
				.on('click', '.trigger', function () {
					var $elev = $(this).parent();
					$elev.toggleClass('open');
					if ($elev.hasClass('open')) {
						$elev.css('height', $elev.attr('data-open-height'));
					} else {
						$elev.css('height', $elev.attr('data-closed-height'));
					}
				});
		});
	};
}) (jQuery);