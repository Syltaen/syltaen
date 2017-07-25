###
 * Make a container closable
 * @package Syltaen
 * @author Stanley Lambot
 * @requires jQuery
###

import $ from "jquery";


# ==================================================
# > JQUERY METHOD
# ==================================================
$.fn.collapsable = (trigger = ".trigger") ->
    $(this).each -> new Collapsable $(this), trigger
    return $(this)


# ==================================================
# > CLASS
# ==================================================
class Collapsable

    constructor: (@$el, @trigger) ->
        @$trigger     = @$el.find(@trigger)

        @openHeight   = @$el.innerHeight()
        @closedHeight = @$trigger.innerHeight()

        @close()

        @bindClick()

    close: ->
        @$el.removeClass "open"
        @$el.css "height", @closedHeight
        @opened = false

    open: ->
        @$el.addClass "open"
        @$el.css "height", @openHeight
        @opened = true

    toggle: ->
        if @opened then @close() else @open()

    bindClick: ->
        @$trigger.click => @toggle()



