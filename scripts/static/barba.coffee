import $ from "jquery"
import Barba from "barba.js"

###
    @see http://barbajs.org/docs/B
    @see http://barbajs.org/
###

# =============================================================================
# > CONFIG
# =============================================================================
# Barba.Pjax.cacheEnabled       = false
# Barba.Pjax.ignoreClassLink    = "no-barba"
Barba.Pjax.Dom.wrapperId      = "site-views"
Barba.Pjax.Dom.containerClass = "site-view"



# =============================================================================
# > VIEWS : Call a list of modules
# =============================================================================
import modules from "./modules.coffee"
modules.init()


# =============================================================================
# > TRANSITIONS
# =============================================================================
Barba.Pjax.getTransition = -> Barba.BaseTransition.extend
    start: ->
        @$html    = $("html")

        @$html.addClass "is-loading"
        @$html.removeClass "is-done-loading"

        unless window.location.hash then $("html, body").stop().animate
            "scrollTop": 0
        , 400

        @newContainerLoading.then =>

            @$html.removeClass "is-loading is-mobilenav-open"
            @$html.addClass "is-done-loading"

            $(@oldContainer).removeClass("in").addClass("out")

            setTimeout =>
                @done()
                $(@newContainer).addClass "in"

                # If anchor, scroll to it
                if window.location.hash
                    $(window).trigger "hashchange"

            , 180

# PAGE LOADING
$(".site-view").addClass "in"

# =============================================================================
# > PREVENTING
# =============================================================================
Barba.Pjax.defaultPreventCheck = Barba.Pjax.preventCheck
Barba.Pjax.preventCheck = (e, el) ->

    href = $(el).attr("href")

    # No href, ignore
    unless href then return false

    # Make it work with anchors on same page
    if href.indexOf("#") is 0 then return true

    # Not the same webiste
    if href.indexOf(window.location.site) < 0 then return false

    # Make it work with anchors to other pages
    if href.indexOf("#") > -1 then return true

    # DefaultPrevent
    unless Barba.Pjax.defaultPreventCheck(e, el) then return false
    if $(el).closest(".no-barba").length then return false

    # Prevent common extensions
    if href.match /(\.pdf|\.jpg|\.png|\.gif)$/ then return false

    # wp-admin stop
    if /wp-admin/.test el.href.toLowerCase() then return false

    # lang switcher
    # if $(el).closest(".lang-menu").length then return false

    return true


# =============================================================================
# > EVENTS
# =============================================================================
Barba.Dispatcher.on "newPageReady", (currentStatus, oldStatus, container, html) ->

    # Add body classes
    html        = html.replace /(<\/?)body( .+?)?>/gi, '$1notbody$2>'
    bodyClasses = $(html).filter("notbody").attr("class")
    $("body").attr "class", bodyClasses
    $("html").removeClass "is-mobilenav-open"

    # Replace the admin tool bar
    $("#wpadminbar").html $(html).find("#wpadminbar").html()

    # Replace the lang menus
    # $(".lang-menu").html $(html).find(".lang-menu").html()

    # Replace breadcrumb
    # $(".site-header__breadcrumbs").addClass("out").removeClass("in")
    # setTimeout ->
    #     $(".site-header__breadcrumbs").html($(html).find(".site-header__breadcrumbs").html()).removeClass("out").addClass("in")
    # , 150

    # trigger Google Analytics
    if typeof ga is "function"
        ga("send", "pageview", location.pathname)


# =============================================================================
# > INIT
# =============================================================================
Barba.Pjax.start()
Barba.Prefetch.init()