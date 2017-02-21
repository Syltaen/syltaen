/*
 * Make a smooth scroll animation with anchor
 * Detect anchor menu and activate elements based on scroll position
 * @package Syltaen
 * @author Stanley Lambot
 * @require jQuery, hammer.js
 */

(function ($) {

	var ids = {},
		selected = null;

	_getHash = function ($el) {
		var hash = $el.attr('href').match(/(.*)(#.+)/);
		if (hash[1] == "" || hash[1] == window.location.pathname || hash[1] == window.location.origin + window.location.pathname) { // if abs anchor or same page
			return hash && hash[2] && $(hash[2]).size() > 0 ? hash[2] : false;
		} else {
			return false;
		}
	}

	_bindClick = function ($elToBind) {
		$elToBind.$a.bind('click.scrollnav', function (e) {
			e.preventDefault();
			$('html, body').stop().animate( {
				'scrollTop': ids[$elToBind.hash].scrollTop - 20
			}, $elToBind.speed, 'swing', function () {
				window.location.hash = $elToBind.hash;
			} );
		});
	}

	_addID = function (hash, $sec, scrollTop) {
		ids[ hash ] = {
			$sec: $sec,
			anchors: [],
			scrollTop: scrollTop,
			hash: hash,
			mirrorURL: false
		}
	}

	_addAnchor = function (hash, $a, speed, mirrorURL) {
		var lastPos = ids[ hash ].anchors.push({
			$a: $a,
			speed: speed,
			mirrorURL: mirrorURL,
			hash: hash
		});
		ids[ hash ].mirrorURL = mirrorURL ? true : ids[ hash ].mirrorURL;
		_bindClick( ids[ hash ].anchors[lastPos - 1] );
	}

	_select = function (toSelect) {
		var i, id, a;

		for (i in ids) {
			id = ids[i];
			if (id == toSelect) {
				for (a in id.anchors) { id.anchors[a].$a.addClass('current'); }
			} else {
				for (a in id.anchors) { id.anchors[a].$a.removeClass('current'); }
			}
		}


		if ( (toSelect && toSelect.mirrorURL) || (selected && selected.mirrorURL) ) {
			var newHash = toSelect ? toSelect.hash : "",
				cleanURL = window.location.href.match(/(.+)(#.+)/);
				cleanURL = cleanURL ? cleanURL[1] : window.location.href;

			window.history.replaceState({
				'action': 'mirrorURL',
				'id': toSelect ? ids['hash'] : false
			}, '', cleanURL + newHash);
		}

		selected = toSelect;
	}

	_update = function () {
		var s = $(window).scrollTop(),
			toSelect, i, id;

		for (i in ids) {
			id = ids[i];
			if (id.scrollTop <= s && (!toSelect || id.scrollTop > toSelect.scrollTop) ) {
				toSelect = id;
			}
		}

		if (toSelect !== selected) {
			_select(toSelect)
		}
	}

	_relocate = function () {
		for (id in ids) {
			ids[id].scrollTop = ids[id].$sec.offset().top;
		}
	}


	$.fn.scrollnav = function (s, mirrorURL) {
		var speed = s || 500,
			mirrorURL = mirrorURL || false;
		$(this).find('a[href*="#"]').each(function () {
			var hash = _getHash( $(this) );
			if (hash) { // if there's an hash and the element exists
				// Create the id if it doesn't exist
				if (!ids.hasOwnProperty(hash)) {
					_addID(hash, $(hash), $(hash).offset().top);
				}
				// Add the anchor link to its section
				_addAnchor(hash, $(this), speed, mirrorURL);
			}
			// console.log(ids);
		});
	};

	$(window).scroll( _update );
	$(window).resize( _relocate );
	$(window).load( _relocate );

}) (jQuery);
