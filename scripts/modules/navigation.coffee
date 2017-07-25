import $ from "jquery"
import "hammerjs"
import "./../tools/addClassAt.coffee"

$root  = $("html")
$roots = $("html, body")

# ==================================================
# > SCROLL NAV
# ==================================================
# import "./../tools/scrollnav.coffee"

# $(".scrollnav").scrollnav()


# ==================================================
# > SCROLL TOP
# ==================================================
$ -> $("#scroll-top").addClassAt(100, "shown").click ->
    $roots.animate
        scrollTop: 0
    , 500

# ==================================================
# > MOBILE
# ==================================================
class MobileMenu
    constructor: (@$trigger, @$menu, @openClass) ->
        @$trigger.click => @toggle()

        hammermenu = new Hammer @$menu[0]
        hammermenu.on "swipeleft", => @close()

        @$menu.find(".mobile-nav__close").click => @close()

    toggle: ->
        $root.toggleClass @openClass

    open: ->
        $root.addClass @openClass

    close: ->
        $root.removeClass @openClass

# ========== INIT ========== #
$ ->
    new MobileMenu $("#mobile-menu-trigger"), $("#mobile-menu"), "mobile-nav-open"