import $ from "jquery"
import "./../tools/incrementor.coffee"
import parallax from "./../tools/parallax.coffee"
import "select2"

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
    # USE SELECT 2 FOR SELECT INPUT
    $("select").each (i, el) ->

        $el = $(el)

        if ($el.data("value") || $el.data("value") is 0) then $el.val $el.data "value"

        disabled       = $el.data "disabled"
        allowClear     = $el.data "clearable"
        appendDropdown = $el.data "append"
        noSearch       = $el.data "nosearch"

        $el.select2
            minimumResultsForSearch: if noSearch then Infinity else 5
            placeholder: $el.attr("placeholder") || "Cliquez pour choisir"
            disabled: disabled
            allowClear: allowClear
            dropdownParent: if appendDropdown then $el.parent() else null
            theme: false

    # PATTERN
    $("html").on "keyup", "input[data-pattern]", ->
        pattern = $(@).attr("data-pattern")
        val     = $(@).val()

        while val && !val.match pattern
            val = val.substr(0, val.length - 1)

        $(@).val val