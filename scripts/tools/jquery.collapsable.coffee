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
    $trigger = $trigger || $(this).find("[data-collapsable-trigger], .collapsable__header")
    $content = $content || $(this).find("[data-collapsable-content], .collapsable__content")
    isLinked = $(this).data("link") != false

    $(this).each -> new Collapsable $(this), $trigger, $content, isLinked
    return $(this)


# ==================================================
# > CLASS
# ==================================================
class Collapsable

    constructor: (@$el, @$trigger, @$content, @isLinked = false) ->
        unless @$el.hasClass "is-open" then @close 0

        # Open/close events
        @$el.on "close", => @close()
        @$trigger.click => @toggle()

    ###
    # Close the collapsable
    ###
    close: (speed = 300) ->
        @$content.slideUp speed
        @$el.removeClass "is-open"
        @opened = false

    ###
    # Open the collapsable
    ###
    open: (speed = 300) ->
        @$content.slideDown speed
        @$el.addClass "is-open"
        @opened = true

        if @isLinked then $("[data-link]").not(@$el).trigger("close")


    ###
    # Toggle open/close
    ###
    toggle: ->
        if @opened then @close() else @open()
