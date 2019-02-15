import $ from "jquery"

import parallax from "./../tools/parallax.coffee"
import UploadField from "./../tools/UploadField.coffee"
import SelectField from "./../tools/SelectField.coffee"
import PasswordBox from "./../tools/PasswordBox.coffee"
import ConfirmationModal from "./../tools/ConfirmationModal.coffee"
import Shadowbox from "./../tools/Shadowbox.coffee"

import "./../tools/jquery.showif.coffee"
import "./../tools/jquery.collapsable.coffee"
import "./../tools/jquery.scrollnav.coffee"
import "./../tools/jquery.incrementor.coffee"

export default in: ->

    # =============================================================================
    # > ANIMATIONS
    # =============================================================================

    # INCREMENTOR
    $(".incrementor").each (i, el) -> $(el).incrementor()

    # PARALLAX
    setTimeout ->
        if parallax then parallax.refresh()
    , 500

    # CONTAINERS DELAY
    $(".site-main .container").each (i, el) -> if i then $(el).addClass "delay-" + i

    # ANIMATION CLASSES
    $("p > img, .wp-caption > p").parent("p, .wp-caption").addClass("animation-image image-wrapper")


    # =============================================================================
    # > CONTENTS
    # =============================================================================
    # COLLAPSABLES
    $(".elevator-box, .collapsable, [data-collapsable]").each (i, el) -> $(el).collapsable()

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

    # CONFIRM POPUP
    $("[data-confirm]").each -> new ConfirmationModal $(@)


    # =============================================================================
    # > FORMS
    # =============================================================================

    # SELECT 2
    $("select").each (i, el) ->
        if $(@).closest(".nf-field").length then return false
        new SelectField $(@)

    # DROPZONE
    $("input[type='file']").not(".nf-field-upload, .dz-hidden-input").each (i, el) -> new UploadField $(@)

    # PASSWORDBOX
    $("input[type='password']").not(".ninja-forms-field, .passwordbox__field").each -> new PasswordBox $(@)

    # DOUBLE-SUBMIT PREVENTION
    $("form").submit ->
        $(@).find("button, input[type='submit']").addClass "is-disabled"
        $(@).submit (e) -> e.preventDefault()

    # PATTERN
    $("html").on "keyup", "input[data-pattern]", ->
        pattern = $(@).attr("data-pattern")
        val     = $(@).val()

        while val && !val.match pattern
            val = val.substr(0, val.length - 1)

        $(@).val val