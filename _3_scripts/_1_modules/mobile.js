/*
 * Add mobile menu and interactions support
 * @package Syltaen
 * @author Stanley Lambot
 * @require jQuery, hammer.js
 */

(function($) {

	var $root = $('html'),
		$menutriggers = $('#primary-menu .mobile-menu, #mobile-menu header'),
		$menu = $('#mobile-menu');

	$menutriggers.click(function () {
		$root.toggleClass('mobile-menu-open');
	});

	$menu.hammer().bind('swipeleft', function () {
		$root.toggleClass('mobile-menu-open');
	});


	$('#top-menu .mobile-menu').click(function () {
		$('#top-menu').toggleClass('open');
	});

})(jQuery);
