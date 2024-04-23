import $ from "jquery"
import "slick-carousel"

import parallax from "./../tools/parallax.coffee"
import { UploadField, AutoUploadField } from "./../tools/UploadField.coffee"
import SelectField from "./../tools/SelectField.coffee"
import PasswordBox from "./../tools/PasswordBox.coffee"
import ConfirmationModal from "./../tools/ConfirmationModal.coffee"
import Shadowbox from "./../tools/Shadowbox.coffee"

import "./../tools/jquery.if.coffee"
import "./../tools/jquery.collapsable.coffee"
import "./../tools/jquery.scrollnav.coffee"
import "./../tools/jquery.incrementor.coffee"
import "./../tools/jquery.map.coffee"
import "./../tools/jquery.siteMessage.coffee"

$ ->

    # =============================================================================
    # > ANIMATIONS
    # =============================================================================
    # INCREMENTOR
    $(".incrementor").each (i, el) -> $(el).incrementor()

    # PARALLAX
    setTimeout ->
        if parallax then parallax.refresh()
    , 500


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

    # SITE MESSAGE
    $(".site-message").siteMessage()

    # IMAGE SHADOWBOX
    $("a[href$='.jpg'], a[href$='.png'], a[href$='.gif']").click (e) ->
        unless $(@).attr("download")
            e.preventDefault()
            sb = new Shadowbox().image($(@).attr("href"))

    # MAP
    $(".map__wrap").each -> $(@).addMap()

    # VIDEO
    $(".video__play").click ->
        $(@).closest(".video").addClass("is-started")
        .find("video").attr("controls", "controls")[0].play()


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

    # AUTO-SUBMIT
    $("[autosubmit]").change -> $(@).closest("form").submit()

    # DOUBLE-SUBMIT PREVENTION
    $(".site-main form").submit (e) ->
        if ($(@).hasClass("is-sending")) then e.preventDefault()
        $(@).addClass "is-sending"

    $("form").change ->
        $(@).removeClass "is-sending"

    # PATTERN
    $("html").on "keyup", "input[data-pattern]", ->
        pattern = $(@).attr("data-pattern")
        val     = $(@).val()

        while val && !val.match pattern
            val = val.substr(0, val.length - 1)

        $(@).val val

    # CONDITIONAL DISPLAY
    $("[data-if]").each -> $(@).if $(@).data("if"), $(@).data("if-action")