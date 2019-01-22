import Barba from "barba.js"
import $ from "jquery"
import "hammerjs"
import "./../tools/addClassAt.coffee"
import "jquery.transit"

$roots = $("html, body")

# ==================================================
# > SCROLL TOP
# ==================================================
$ ->
    $("#scroll-top").addClassAt(100, "shown").click ->
        $roots.animate
            scrollTop: 0
        , 500


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

        @selector = ".site-header__menu > .current-menu-item, .site-header__menu > .current-menu-ancestor"
        Barba.Dispatcher.on "newPageReady", (o, s, ef, html) =>
            @setCurrent @$menu.find "#" + $(html).find(@selector).attr("id")
        @setCurrent $(@selector)


    setCurrent: ($item) ->
        @$current = $item

        if @$current.length
            @$indicator.css
                "x": @$current.offset().left - @$menu.offset().left
                "width": @$current.innerWidth()
        else
            @$indicator.css
                "width": 0


# > EVENT
menu = new Menu
