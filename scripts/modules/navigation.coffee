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
# $(window).on "hashchange", ->
#     # No hash
#     unless window.location.hash then return false

#     # No element
#     $el = $(window.location.hash)
#     unless $el.length then return false

#     # Animation scroll
#     $roots.stop().animate
#         "scrollTop": $el.offset().top
#     , 400


# # On anchor click
# $("body").on "click.anchor", "a[href*='#']", -> setTimeout ->
#         $(window).trigger "hashchange"
#     , 100

# # On new page load
# setTimeout ->
#     $(window).trigger "hashchange"
# , 350


# ==================================================
# > MOBILE
# ==================================================
class MobileMenu
    constructor: (@$trigger, @$menu, @openClass) ->
        @$trigger.click => @toggle()

        @$root = $("html")

        hammermenu = new Hammer @$menu[0]
        hammermenu.on "swipeleft", => @close()

        @$menu.find(".site-mobilenav__close").click => @close()

    toggle: ->
        @$root.toggleClass @openClass

    open: ->
        @$root.addClass @openClass

    close: ->
        @$root.removeClass @openClass

# ========== INIT ========== #
$ ->
    new MobileMenu $(".site-mobilenav__trigger"), $(".site-mobilenav"), "is-mobilenav-open"