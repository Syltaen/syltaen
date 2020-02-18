###
 * Site message
 * @package Syltaen
 * @author Stanley Lambot
 * @requires jQuery
###

import $ from "jquery"

# ==================================================
# > JQUERY METHOD
# ==================================================
$.fn.siteMessage = () ->
    $(this).each -> new SiteMessage $(this)
    return $(this)


# ==================================================
# > CLASS
# ==================================================
class SiteMessage

   constructor: (@$el) ->
        @show()
        @$el.find(".site-message__close").click => @hide()


        @$el.addClass "is-doomed"
        setTimeout =>
            @hide()
        , 10000

    ###
    # Show the message
    ###
    show: -> @$el.addClass "is-shown"

    ###
    # Hide the message
    ###
    hide: -> @$el.removeClass "is-shown"