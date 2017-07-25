###
  * Make a smooth scroll animation with anchor
  * Detect anchor menu and activate elements based on scroll position
  * @package Syltaen
  * @author Stanley Lambot
  * @requires jQuery, hammer.js
###

import $ from "jquery"

# ==================================================
# > GLOBALS
# ==================================================
anchorCollection = new AnchorCollection()
$roots           = $("html, body")

# ==================================================
# > JQUERY METHOD
# ==================================================
$.fn.scrollnav = (speed = 500, mirrorURL = false) ->

    $(this).find("a[href=*='#']").each (i, el) ->
        anchor = new Anchor $(el), speed, mirrorURL
        anchorCollection.add anchor

# ==================================================
# > EVENTS
# ==================================================
$(window).scroll anchorCollection.checkCurrent
$(window).resize anchorCollection.checkCurrent
$(window).on "load", anchorCollection.checkCurrent


# ==================================================
# > CLASSES
# ==================================================
class Anchor
    constructor: (@$el, @speed, @mirrorURL) ->
        @hash = getHash()
        unless @hash then return false

        @$target = $(@hash).first()


    getHash: ->
        hash = @$el.attr("href").match(/(.*)(#.+)/)
        # if abs anchor or same page
        if hash[1] == "" || hash[1] == window.location.pathname || hash[1] == window.location.origin + window.location.pathname
            return if hash && hash[2] && $(hash[2]).length > 0 then hash[2] else false
        else
            return false

    bindClick: ->
        @$el.click =>
            e.preventDefault()
            $roots.stop().animate
                "scrollTop": @$target - 20
            , @speed, "swing", =>
                window.location.hash = @hash


class AnchorCollection
    constructor: ->
        @items   = []
        @scroll  = 0
        @current = ""

        @cleanURL = window.location.href.match(/(.+)(#.+)/)
        @cleanURL = if @cleanURL then cleanURL[1] else window.location.href

    add: (item) ->
        if item
            @items.push item

    checkCurrent: ->
        @scroll = $(window).scrollTop()
        hash    = false


        for i, item of @items
            if @scroll >= item.$target.offset().top
                #

###
            var _update = function () {
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
            } ###



        if hash isnt @current
            @setCurrent hash

    setCurrent: (hash) ->
        @current = hash
        shouldMirror = false

        for i, item of @items
            if item.hash == @current
                item.$el.addClass "current"
                shouldMirror = if item.mirrorURL then true else shouldMirror
            else
                item.$el.removeClass "current"

        if shouldMirror
            window.history.replaceState
                action: "mirrorURL"
                id: @current
            , "", @cleanURL+@current