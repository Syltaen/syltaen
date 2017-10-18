###
  * Make a smooth scroll animation with anchor
  * Detect anchor menu and activate elements based on scroll position
  * @package Syltaen
  * @author Stanley Lambot
  * @requires jQuery, hammer.js
###

import $ from "jquery"

# ==================================================
# > CLASSES
# ==================================================
class Anchor
    constructor: (@$el, @speed, @offset) ->
        @hash = @getHash()
        unless @hash then return false

        @$target   = $(@hash).first()

        @localizeTarget()
        @bindClick()

    getHash: ->
        hash = @$el.attr("href").match(/(.*)(#.+)/)
        # if abs anchor or same page
        if hash[1] == "" || hash[1] == window.location.pathname || hash[1] == window.location.origin + window.location.pathname
            return if hash && hash[2] && $(hash[2]).length > 0 then hash[2] else false
        else
            return false

    localizeTarget: ->
        @targetTop = @$target.offset().top + @offset

    bindClick: ->
        @$el.click (e) =>
            e.preventDefault()
            $roots.stop().animate
                "scrollTop": @targetTop + 1
            , @speed, "swing", =>
                window.location.hash = @hash



class AnchorCollection
    constructor: ->
        @items     = []
        @scroll    = 0
        @current   = ""
        @mirrorURL = false

        @cleanURL = window.location.href.match(/(.+)(#.+)/)
        @cleanURL = if @cleanURL then @cleanURL[1] else window.location.href

    add: (item) ->
        if item && item.hash
            @items.push item

    activateMirror: (shouldActivate) ->
        if shouldActivate
            @mirrorURL = true

    checkCurrent: ->
        @scroll  = $(window).scrollTop()
        toSelect = false

        for item in @items

            item.localizeTarget()

            if !toSelect || toSelect.targetTop < item.targetTop

                if @scroll > item.targetTop
                    toSelect = item

        if toSelect isnt @current then @updateCurrent toSelect

    updateCurrent: (toSelect) ->
        @current     = toSelect
        @hash        = @current.hash || ""

        for item in @items
            if item.hash == @hash
                item.$el.addClass "current"
            else
                item.$el.removeClass "current"

        if @mirrorURL
            window.history.replaceState
                action: "mirrorURL"
                id: @hash
            , "", @cleanURL+@hash


# ==================================================
# > GLOBALS
# ==================================================
anchorCollection = new AnchorCollection()
$roots           = $("html, body")

# ==================================================
# > JQUERY METHOD
# ==================================================
$.fn.scrollnav = (speed = 500, mirrorURL = false, offset = -20) ->

    $(this).find("a[href*='#']").each (i, el) ->
        anchor = new Anchor $(el), speed, offset

        anchorCollection.add anchor
        anchorCollection.activateMirror mirrorURL

# ==================================================
# > EVENTS
# ==================================================
$(window).scroll => anchorCollection.checkCurrent()
$(window).resize => anchorCollection.checkCurrent()
$(window).on "load", => anchorCollection.checkCurrent()