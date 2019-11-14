import $ from "jquery"
import "jquery.transit"

# =============================================================================
# > ACF PAGE BUILDER
# =============================================================================
class ACFSection
    constructor: (@$section) ->
        @$section.attr "data-processed", true

        @$bgPicker    = @$section.find(".acf-page-sections__bg input").change => @updateHandle()
        @$colorPicker = @$section.find(".acf-page-sections__color input").change => @updateHandle()
        @$imagePicker = jQuery(@$colorPicker.closest(".acf-page-sections__color").next().find("input[type='hidden']")[0]).change => @updateHandle()

        @$handle = @$section.children(".acf-row-handle.order")

        @updateHandle()

    updateHandle: ->
        @$handle.attr("class", "acf-page-sections__handle acf-row-handle order ui-sortable-handle bg-" + @$bgPicker.filter(":checked").val() + " color-" + @$colorPicker.filter(":checked").val())

        if (($img =  @$imagePicker.next(".image-wrap").find("img")) && (@$bgPicker.filter(":checked").val() == "image"))
            @$handle.css("background-image", "url(" + $img.attr("src") + ")")
        else
            @$handle.css("background-image", "")

$ ->
    # Section display
    setInterval ->
        $(".acf-page-sections__bg").map ->
            $section = $(@).closest(".acf-row")
            if $section.data("processed") || $section.is(".acf-clone") then return false
            new ACFSection $section
    , 1000

    # Columns width
    $("body").on "keyup change", ".acf-sections-row__columns__width input[type='number']", ->
        console.log "change"
        $(@).closest(".acf-row").css "flex", $(@).val()

    # Label tooltips
    $(".acf-choice .acf-input label").each ->
        $(@).append "<span class='acf-label-tooltip'>" + $(@).text() + "</span>"
        $(@).addClass "acf-label-tooltip__parent"

    $(".acf-light-repeater > .acf-input > .acf-repeater > .acf-table > tbody > .acf-row > .acf-fields > .acf-field > .acf-input > div > .acf-actions .button, .acf-horizontal-repeater > .acf-input > .acf-repeater > .acf-actions .button").each ->
        $(@).html "<span class='acf-label-tooltip acf-label-tooltip--right'>" + $(@).text() + "</span>"
        $(@).addClass "acf-label-tooltip__parent"


# =============================================================================
# > NINJA FORM BUILDER
# =============================================================================
class NinjaFormBuilder
    constructor: (@$wrap) ->
        $("body").mouseup => setTimeout =>
            @embedColumns()
        , 5

        $("body").keyup => setTimeout =>
            @embedColumns()
        , 5

        @embedColumns()

    embedColumns: ->
        offset = 0

        $(".nf-field-wrap").each ->

            if $(@).hasClass("fieldopentag") || $(@).hasClass("fieldrepeater")
                $(@).css "x", offset
                offset += 20

            else if $(@).hasClass "fieldclosetag"
                offset -= 20
                $(@).css "x", offset

            else
                $(@).css "x", offset
                # console.log "no", $(@)


$ ->
    setTimeout ->
        if $(".nf-app-area").length then new NinjaFormBuilder $(".nf-app-area")
    , 500