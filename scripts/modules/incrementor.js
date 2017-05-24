/**
  * Make a number increment from 0 to its value
  * @package Syltaen
  * @author Stanley Lambot
  * @requires jQuery
  */


(function ($) {

    var digits = [];

    /**
     * Format a number
     * @param {string|int} nStr Number to format
     * @param {int} decimals Number of decimals to keep
     */
    var _formatNumber = function(nStr, decimals) {
        nStr += '';
        x = nStr.split('.');
        x1 = x[0];
        x2 = x.length > 1 ? '.' + x[1] : '';
        var rgx = /(\d+)(\d{3})/;
        while (rgx.test(x1)) {
            x1 = x1.replace(rgx, '$1' + ',' + '$2');
            // x1 = x1.replace(rgx, '$1' + '$2');
        }

        x2 = decimals ? x2.substr(0,decimals+1) : "";
        return x1 + x2;
    }

    /**
     * Write the digit value inside the element
     * @param object digit An instance of digit
     * @param int value The value to write
     */
    var _changeValue = function(digit, value) {
        value = value || digit.value;
        digit.$el.html(
            (digit.prefix ? "<span class='incrementor--prefix'>"+digit.prefix+"</span>" : "")
            +
            (
                digit.timeFormat ?
                    dateFormat(value, digit.timeFormat, true)
                    :
                    _formatNumber(value, digit.decimals)
            )
            +
            (digit.suffix ? "<span class='incrementor--suffix'>"+digit.suffix+"</span>" : "")
        );
    }

    /**
     * Start the incrementation for a number
     * @param {int} digit
     */
    var _increment = function (digit) {
        digit.value = 0;
        digit.started = true;
        digit.incrementation = digit.goal / (digit.speed / 100);
        digit.decimals = digit.goal % 1 ? 1 : 0;

        clearInterval(digit.interval);
        digit.interval = setInterval(function () {
            digit.value += digit.incrementation;
            _changeValue(digit);
            if (digit.value >= digit.goal) {
                clearInterval(digit.interval);
                _changeValue(digit, digit.goal);
            }
        }, 100);
    }

    /**
     * Look if a digit should be incremented base on scroll value
     */
    var _update = function () {
        var s = $(window).scrollTop();
        $.each(digits, function() {
            if (s >= this.top && !this.started && !this.manual) {
                _increment(this);
            }
        });
    }

    /**
     * Add a new number to animate
     */
    $.fn.incrementor = function (speed, manual) {
        if ($(this).length) {
            var toAdd     = $('#wpadminbar').length ? $('#wpadminbar').innerHeight(): 0,
                scrollTop = $(this).offset().top,
                speed     = speed || 1000,
                manual    = manual || false;

            digits.push({
                $el: $(this),
                top: parseFloat(scrollTop, 10) + toAdd - ($(window).innerHeight() ),
                goal: parseFloat($(this).text(), 10),
                value: 0,
                speed: speed,
                started: false,
                prefix: $(this).data("prefix") || "",
                suffix: $(this).data("suffix") || "",
                timeFormat: $(this).data("time") || false,
                manual: manual
            });

            $(this).text(0);
            $(this).attr("data-incrementor", digits.length);

            _update();
        }
    };

    /**
     * Trigger the incrementation for a number
     */
    $.fn.increment = function () {
        var id = $(this).data("incrementor");
        _increment( digits[id-1] );
    }

    // ==================================================
    // > UPDATING ON SCROLL
    // ==================================================
    $(window).scroll(_update);


}) (jQuery);