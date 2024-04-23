###
 * Site message
 * @package Syltaen
 * @author Stanley Lambot
 * @requires jQuery
###

import $ from "jquery"
import "slick-carousel"
import Shadowbox from "./../tools/Shadowbox.coffee"

# ==================================================
# > JQUERY METHOD
# ==================================================
$.fn.zoomGallery = () ->
    $(this).each -> new ZoomGallery $(this)
    return $(this)


# ==================================================
# > CLASS
# ==================================================
class ZoomGallery

   constructor: (@$el) ->
        @sb = new Shadowbox false, false
        @sb.$content.html @$el.parent().html()
        @$el.click =>
            if $(window).innerWidth() <= 1040
                # console.log @$el.find("img").attr("src")
                window.open @$el.find("img").attr("src"), "_blank"
            else
                @sb.show()

        @$zoom = @sb.$content.find(".zoom-gallery__list")

        @$el.slick
            autoplay: false
            dots: true
            appendDots: @$el.next(".zoom-gallery__controls")
            appendArrows: @$el.next(".zoom-gallery__controls")
            asNavFor: @$zoom

        @$zoom.slick
            autoplay: false
            dots: true
            appendDots: @$zoom.next(".zoom-gallery__controls")
            appendArrows: @$zoom.next(".zoom-gallery__controls")
            asNavFor: @$el