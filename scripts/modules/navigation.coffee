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
# > COOKIES CONSENT
# ==================================================
$(".manage-cookies").click -> revisitCkyConsent()

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
$("body").on "click.anchor", "a[href*='#']", (e) ->
    # If on different page, do nothing
    page = $(this).attr("href").split("#")[0]
    unless (!page || page == window.location.href.split("#")[0]) then return

    # Else, change hash and trigger scroll
    e.preventDefault()
    history.pushState null, null, $(this).attr("href")
    $(window).trigger "hashchange"

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
$ -> if $(".site-mobilenav").length
    new MobileMenu $(".site-mobilenav__trigger"), $(".site-mobilenav"), "is-mobilenav-open"