import Barba from "barba.js"
import $ from "jquery"
import "hammerjs"
import "jquery.transit"

import "./../tools/jquery.addClassAt.coffee"

$roots = $("html, body")

# ==================================================
# > SCROLL TOP
# ==================================================
$ ->
    $("#scroll-top").addClassAt(100, "is-shown").click ->
        $roots.animate
            scrollTop: 0
        , 500


# ==================================================
# > HASH SCROLL
# ==================================================
$(window).on "hashchange", ->
    # No hash
    unless window.location.hash then return false

    # No element
    $el = $(window.location.hash)
    unless $el.length then return false

    # Animation scroll
    $roots.stop().animate
        "scrollTop": $el.offset().top
    , 400


# On anchor click
$("body").on "click.anchor", "a[href*='#']", -> setTimeout ->
        $(window).trigger "hashchange"
    , 100

# On new page load
setTimeout ->
    $(window).trigger "hashchange"
, 350


# ==================================================
# > MOBILE
# ==================================================
class MobileMenu
    constructor: (@$trigger, @$menu, @openClass) ->
        @$trigger.click => @toggle()

        @$body = $("body")

        hammermenu = new Hammer @$menu[0]
        hammermenu.on "swipeleft", => @close()

        @$menu.find(".site-mobilenav__close").click => @close()

    toggle: ->
        @$body.toggleClass @openClass

    open: ->
        @$body.addClass @openClass

    close: ->
        @$body.removeClass @openClass

# ========== INIT ========== #
$ ->
    new MobileMenu $(".site-mobilenav__trigger"), $(".site-mobilenav"), "is-mobilenav-open"



# =============================================================================
# > MAIN MENU
# =============================================================================
class Menu
    constructor: ->
        @$menu  = $(".site-header__menu")
        @$indicator = $("<div class='site-header__menu__indicator'></div>")
        @$menu.append @$indicator
        @selector = ".site-header__menu .current-menu-item, .site-header__menu > .current-menu-ancestor"

        # Set on new page load
        @setCurrent $(@selector)

        # Set on pajax load
        Barba.Dispatcher.on "newPageReady", (o, s, ef, html) =>
            ids = $.map $(html).find(@selector), (item) -> "#" + $(item).attr("id")
            @setCurrent @$menu.find ids.join ", "

    setCurrent: ($item) ->
        @$menu.find(".is-current").removeClass "is-current"
        $item.addClass "is-current"
        @$current = $item.first()

        if @$current.length
            @$indicator.css
                "x": @$current.offset().left - @$menu.offset().left
                "width": @$current.innerWidth()
        else
            @$indicator.css
                "width": 0


# > EVENT
menu = new Menu