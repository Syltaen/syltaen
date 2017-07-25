###
  * Create a Google Map and add filterable pins to it
  * @package Syltaen
  * @author Stanley Lambot
  * @requires jQuery
###

import $ from "jquery";

export default class Shadowbox

    constructor: ->
        @$html = $("html")
        @addNew()

        @$close.click => @hide()
        @$content.click => @hide()

    addNew: ->
        @$sb      = $("<div class='shadowbox'></div>")
        @$close   = $("<span class='fermer'>Fermer</span>")
        @$content = $("<div class='content'></div>")

        @$html.append @$sb.append(@$close).append(@$content)

        return @$sb

    empty: () ->
        @$content.html ""
        return this

    video: (url) ->
        # todo
        return this

    image: (url) ->
        @$content.append "<img src='"+url+"'>"
        return this

    html: (html) ->
        @$content.html html
        return this

    show: (speed = 350) ->
        @$sb.fadeIn speed
        @$html.addClass "shadowbox-open"
        return this

    hide: (speed = 350) ->
        @$sb.fadeOut speed
        @$html.removeClass "shadowbox-open"
        return this



