###
 * Add a class to an element at a certain scrollHeight
 * @package Syltaen
 * @author Stanley Lambot
 * @requires jQuery
###

import $ from "jquery"

# ==================================================
# > CLASS
# ==================================================
class Elements

    constructor: ->
        @items  = []
        @scroll = 0

    add: (item) ->
        @items.push item

    check: ->
        @scroll = $(window).scrollTop()
        for item in @items

            if @scroll >= item.top && !item.hasClass
                item.$el.addClass item.class
                item.hasClass = true

            else if @scroll < item.top && item.hasClass
                item.$el.removeClass item.class
                item.hasClass = false



# ==================================================
# > GLOBALS
# ==================================================
elements = new Elements()
$(window).scroll -> elements.check()

# ==================================================
# > JQUERY METHOD
# ==================================================
$.fn.addClassAt = (scrollTop, classToAdd) ->
    toAdd = if $('#wpadminbar').length then $('#wpadminbar').innerHeight() else 0

    elements.add({
        $el: $(this),
        top: parseInt(scrollTop, 10) + toAdd,
        hasClass: false,
        class: classToAdd
    });

    return $(this)