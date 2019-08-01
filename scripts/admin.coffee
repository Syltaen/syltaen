import $ from "jquery"
import "jquery.transit"

# =============================================================================
# > ACF PAGE BUILDER
# =============================================================================
class ACFSection
    constructor: (@$section) ->
        @$section.attr "data-processed", true

        @$bgPicker    = @$section.find(".acf-page-sections__bg select").change => @updateHandle()
        @$colorPicker = @$section.find(".acf-page-sections__color select").change => @updateHandle()
        @$imagePicker = jQuery(@$colorPicker.closest(".acf-page-sections__color").next().find("input[type='hidden']")[0]).change => @updateHandle()

        @$handle = @$section.children(".acf-row-handle.order")

        @updateHandle()

    updateHandle: ->
        @$handle.attr("class", "acf-page-sections__handle acf-row-handle order ui-sortable-handle bg-" + @$bgPicker.val() + " color-" + @$colorPicker.val())

        if (($img =  @$imagePicker.next(".image-wrap").find("img")) && (@$bgPicker.val() == "image"))
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
    $(".acf-sections-row__columns__width input[type='number']").on "keyup change", ->
        $(@).closest(".acf-row").css "flex", $(@).val()


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