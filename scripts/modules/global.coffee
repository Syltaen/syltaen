import $ from "jquery"
import "./../tools/incrementor.coffee"
import parallax from "./../tools/parallax.coffee"
import UploadField from "./../tools/uploadField.coffee"
import SelectField from "./../tools/selectField.coffee"

export default in: ->

    # =============================================================================
    # > ANIMATIONS
    # =============================================================================

    # INCREMENTOR
    $(".incrementor").each (i, el) -> $(el).incrementor()

    # PARALLAX
    setTimeout ->
        parallax.refresh()
    , 500

    # CONTAINERS DELAY
    $(".site-main .container").each (i, el) -> if i then $(el).addClass "delay-" + i

    # ANIMATION CLASSES
    $("p > img, .wp-caption > p").parent("p, .wp-caption").addClass("animation-image image-wrapper")


    # =============================================================================
    # > CONTENTS
    # =============================================================================

    # SLICK GALLERY
    $(".gallery").each ->
        $(@).find("br").remove()
        columns = $(@)[0].className.match /gallery-columns-([0-9]+)/
        $(@).slick
            adaptiveHeight: true
            dots: true
            autoplay: true
            autoplaySpeed: 6000
            slidesToShow: columns[1] || 1




    # =============================================================================
    # > FORMS
    # =============================================================================
    # ========== SELECT 2 ========== #
    $("select").each (i, el) ->
        if $(@).closest(".nf-field").length then return false
        new SelectField $(@)

    # ========== DROPZONE ========== #
    $("input[type='file']").not(".nf-field-upload, .dz-hidden-input").each (i, el) -> new UploadField $(@)


    # PATTERN
    $("html").on "keyup", "input[data-pattern]", ->
        pattern = $(@).attr("data-pattern")
        val     = $(@).val()

        while val && !val.match pattern
            val = val.substr(0, val.length - 1)

        $(@).val val