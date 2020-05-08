###
  * Create a Google Map and add filterable pins to it
  * @package Syltaen
  * @author Stanley Lambot
  * @requires jQuery
###

import $ from "jquery"

export default class Shadowbox

    constructor: (@removeOnHide = true) ->
        @$html = $("html")
        @$body = $("body")
        @addNew()

        # Close on click, with strict target checking
        @$closeTarget = false
        @$sb.on "mousedown", "[data-action='close']", (e) =>
            if $(e.target).data("action") == "close"
                @$closeTarget = $(e.target)
        @$sb.on "mouseup", "[data-action='close']", (e) =>
            if $(e.target).data("action") == "close"
                if @$closeTarget && @$closeTarget.is($(e.target)) then @hide()
            @$closeTarget = false


    addNew: ->
        @$sb      = $("<div class='shadowbox'></div>")
        @$close   = $("<span class='shadowbox__close' data-action='close'>Fermer</span>")
        @$content = $("<div class='shadowbox__content' data-action='close'></div>")

        @$body.append @$sb.append(@$close).append(@$content)

        return @$sb

    remove: ->
        @$sb.remove()

    # ==================================================
    # > CONTENTS
    # ==================================================
    empty: () ->
        @$content.html ""
        return @

    video: (url, attrs = "loop autoplay controls") ->
        @$content.append "<video #{attrs}><source src='#{url}'></source></video>"
        return @

    iframe: (url, attrs = "frameborder='0' webkitallowfullscreen mozallowfullscreen allowfullscreen") ->
        @$content.append "<iframe src='#{url}' #{attrs}></iframe>"
        return @

    image: (url) ->
        @$content.append "<img src='" + url + "'>"
        return @

    html: (html) ->
        @$content.html html
        return @

    modal: (html) ->
        if html
            @$content.html "<div class='shadowbox__modal'>#{html}</div>"
        else
            @$content.html "<div class='shadowbox__modal is-loading'></div>"
        return @

    # ==================================================
    # > ACTIONS
    # ==================================================
    show: (speed = 350) ->
        @$sb.fadeIn speed
        @$sb.addClass "is-shown"
        @$html.addClass "is-scroll-locked"
        return this

    hide: (speed = 350) ->
        @$sb.fadeOut speed, => if @removeOnHide then @remove()
        @$sb.removeClass "is-shown"
        @$html.removeClass "is-scroll-locked"
        return this

    setScrollable: ->
        @$sb.addClass "shadowbox--scrollable"

    # ==================================================
    # > CHECKERS
    # ==================================================
    isShown: () ->
        return @$sb.is(":visible")