/*
 * CSS animations based on scrollYop value
 * @package Syltaen
 * @author Stanley Lambot
 * @require jQuery
 * @param property (string) CSS property to animate
 * @param unit (string) CSS unit to place after the calculated property value
 * @param start (int) CSS property value at the start of the animation
 * @param end (int) CSS property value at the end of the animation
 * @param relstart (int||string) Pixel value or percent value (based on window height) setting the start of the animation regarding the element position
 * @param relend (int||string) Pixel value or percent value (based on window height) setting the end of the animation regarding the element position
 */




(function($) {

	$.fn.parallax = function (property, unit, from, to, relstart, relend) {
		$.fn._parallax.addItem({
			$el: this,
			property: property || 'y',
			unit: unit || '%',
			from: from || 0,
			to: to || 100,
			relstart: relstart || '',
			relend: relend || ''
		});
		return this;
	};

	$.fn._parallax = {

		items: [],
		y: $(window).scrollTop(),
		lastScroll: 0,
		resizetimeout: null,

		addItem: function (param) {
			if (param.$el.size() > 0) {
				if (param.$el.css('transform')) param.$el.css('transform', param.$el.css('transform'));
				return $.fn._parallax.items.push( $.fn._parallax.calculateProperties({
					$el: param.$el,
					property: param.property,
					unit: param.unit,
					from: param.from,
					to: param.to,
					relstart: param.relstart,
					relend: param.relend
				}));
			}
		},


		calculateProperties: function (item) {
			var rel_start = item.relstart,
				rel_end = item.relend;

			if (rel_start.match('(.*)%')) { rel_start = $(window).innerHeight() / (100 / parseInt(rel_start.match('(.*)%')[1], 10)); }
			if (rel_end.match('(.*)%')) { rel_end = $(window).innerHeight() / (100 / parseInt(rel_end.match('(.*)%')[1], 10)); }

			item.start = item.$el.offset().top - $(window).innerHeight() + rel_start; item.start = item.start < 0 ? 0 : item.start;
			item.end = (item.property == "y") ? item.$el.offset().top + item.$el.innerHeight() + (item.to - item.from) + rel_end: item.$el.offset().top + item.$el.innerHeight() + rel_end;
			item.ratio = (item.to - item.from) / (item.end - item.start);
			return item;
		},



		recalculateEverything: function () {
			for (var id in scroll.items) {
				scroll.items[id] = scroll.calculateProperties( scroll.items[id] );
			}
		},



		refresh: function () {

			if (Date.now() - $.fn._parallax.lastScroll > 25) {
				$.fn._parallax.lastScroll = Date.now();
				for (id in $.fn._parallax.items) {
					var actor = $.fn._parallax.items[id],
						css = {},
						value = $.fn._parallax.y <= actor.start ? actor.from :
								$.fn._parallax.y >= actor.end ? actor.to :
								($.fn._parallax.y * actor.ratio) + actor.from - (actor.start * actor.ratio);
					css[actor.property] = value + actor.unit;
					actor.$el.css(css);

				}
			}
		}
	};


	$(window).scroll(function () {
		$.fn._parallax.y = $(window).scrollTop();
		$.fn._parallax.refresh();
	});

	$(window).resize(function () {
		clearTimeout( $.fn._parallax.resizetimeout );
		$.fn._parallax.resizetimeout = setTimeout(function () {
			$.fn._parallax.recalculateEverything();
		}, 200);
	});

	setTimeout(function ()  {
		$.fn._parallax.recalculateEverything();
	}, 500);


})(jQuery);
