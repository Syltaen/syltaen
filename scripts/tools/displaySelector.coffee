###
 * Bind a menu to a list of elements to display
 * @package Syltaen
 * @author Stanley Lambot
 * @requires jQuery
###

import $ from "jquery"

# ==================================================
# > ITEM
# ==================================================
class DisplayItem
    constructor: (@$menu, @$content = false, @bind, @index, @selector) ->
        @$menu.click => @set()

        if @$content then @resetDelays()

    set: ->
        @selector.unsetAll()
        @$menu.addClass "is-active"
        if @$content
            @$content.removeClass "is-hidden"

            # Reset slick to debug the display
            if @$content.find(".slick-slider").length
                @$content.find(".slick-slider").slick("slickGoTo", 0, true)

            # Trigger all refresh
            $(window).resize()

        @selector.current = @index

        if @selector.config.mirrorUrl
            window.location.hash = @bind

    unset: ->
        @$menu.removeClass "is-active"

        if @$content
            @$content.addClass "is-hidden"


    resetDelays: ->
        @$content.find(".container").each (i, el) ->
            $(el).removeClass -> (i, classname) -> classname.match /delay\-.+/
            $(el).addClass "delay-" + i




# ==================================================
# > SELECTOR
# ==================================================
export default class DisplaySelector

    defaults =
        start:        0
        menuItems:    false
        contentItems: false
        bind:         "data-name"
        prev:         false
        next:         false
        mirrorUrl:    false

    constructor: (config) ->

        @config = $.extend defaults, config
        @items  = @getItems()

        @setCurrent()

        # Register nav events
        if @config.prev && @config.next
            @config.prev.click => @nav -1
            @config.next.click => @nav  1

    ###
    # Create a list of DisplayItem
    ###
    getItems: ->
        items = []

        @config.menuItems.each (i, el) =>

            if @config.contentItems
                bind = $(el).attr(@config.bind)
                items.push new DisplayItem $(el), @config.contentItems.filter("[#{@config.bind}='#{bind}']"), bind, i, @
            else
                items.push new DisplayItem $(el), false, bind, i, @

        return items


    ###
    # Unset all elements
    ###
    unsetAll: -> for i in @items then i.unset()


    ###
    # Set the current item
    ###
    setCurrent: (index = false) ->
        # Set requested
        unless index is false then return @items[index].set()

        # Detect from URL if mirrorUrl is enabled
        if @config.mirrorUrl && window.location.hash
            for i in @items then if i.bind == window.location.hash.replace "#", ""
                return i.set()

        # Set from default start
        @setCurrent @config.start


    ###
    ###
    getCurrentFromUrl: ->

    ###
    # Navigate to previous or next item
    ###
    nav: (dir) ->
        c = @current + dir
        c = if c < 0 then @items.length - 1 else c
        c = if c > @items.length - 1 then 0 else c
        @setCurrent c