###
  * Create a Google Map and add filterable pins to it
  * @package Syltaen
  * @author Stanley Lambot
  * @requires jQuery
###

import $ from "jquery"

export default class Shadowbox

    constructor: (@removeOnHide = true, @autoShow = true) ->
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

        # Close with "esc" key
        $(document).on "keyup", (e) => if e.which == 27 then @hide()

        if @autoShow then @show()


    addNew: ->
        @$sb      = $("<div class='shadowbox'></div>")
        @$close   = $("<span class='shadowbox__close' data-action='close'>Fermer</span>")
        @$content = $("<div class='shadowbox__content' data-action='close'></div>")

        @$body.append @$sb.append(@$content).append(@$close)

        return @$sb

    remove: ->
        @$sb.remove()

    # ==================================================
    # > CONTENTS
    # ==================================================
    empty: () ->
        @$content.html ""
        return @

    video: (url, attrs = "autoplay controls") ->
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

    addSidebar: (data) ->
        @$sidebar = $("<aside class='shadowbox__sidebar'></aside>")
        @$sb.append @$sidebar
        if data.image
            @$sidebar.append "<div class='shadowbox__sidebar__image' style='background-image: url(#{data.image})'></div>"
        if data.title
            @$sidebar.append "<h2 class='shadowbox__sidebar__title'>#{data.title}</h2>"
        if data.content
            @$sidebar.append "<p class='shadowbox__sidebar__content'>#{data.content}</p>"

    # ==================================================
    # > ACTIONS
    # ==================================================
    show: (speed = 350) ->
        # @$sb.fadeIn speed
        @$sb.addClass "is-shown"
        @$html.addClass "is-scroll-locked"
        return @

    hide: (speed = 350) ->
        @$sb.removeClass "is-shown"
        if @removeOnHide then setTimeout =>
            @remove()
        , speed

        @$html.removeClass "is-scroll-locked"
        return @

    setScrollable: ->
        @$sb.addClass "shadowbox--scrollable"

    # ==================================================
    # > CHECKERS
    # ==================================================
    isShown: () ->
        return @$sb.is(":visible")