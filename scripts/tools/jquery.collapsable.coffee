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
$.fn.collapsable = ($trigger = false, $content = false) ->

    $trigger = $(this).find("[data-collapsable-trigger], .collapsable__header")
    $content = $(this).find("[data-collapsable-content], .collapsable__content")

    $(this).each -> new Collapsable $(this), $trigger, $content
    return $(this)


# ==================================================
# > CLASS
# ==================================================
class Collapsable

    constructor: (@$el, @$trigger, @$content) ->

        unless @$el.hasClass "is-open" then @close 0
        @bindClick()

    close: (speed = 300) ->
        @$content.slideUp speed
        @$el.removeClass "is-open"
        @opened = false

    open: (speed = 300) ->
        @$content.slideDown speed
        @$el.addClass "is-open"
        @opened = true

    toggle: ->
        if @opened then @close() else @open()

    bindClick: ->
        @$trigger.click => @toggle()