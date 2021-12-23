import $ from "jquery"
import "jquery.transit"

# =============================================================================
# > ACF PAGE BUILDER
# =============================================================================

###
# Sections display preview
###
class ACFSection
    constructor: (@$section) ->
        @$section.attr "data-processed", true

        @bg       = "white"
        @customBg = "#fff"
        @text     = "text"
        @image    = false

        @$bgPicker       = @$section.find(".acf-page-sections__bg input").change                 => @updateHandle()
        @$customBgPicker = @$section.find(".acf-page-sections__custombg input").change           => @updateHandle()
        @$textPicker     = @$section.find(".acf-page-sections__color input").change              => @updateHandle()
        @$imagePicker    = jQuery(@$section.find(".acf-field[data-name='bg_img']")).find("input").change => @updateHandle()

        @$handle = @$section.children(".acf-row-handle.order")

        @updateHandle()

    ###
    # Update the section hanlde to preview colors
    ###
    updateHandle: ->
        @bg       = @$bgPicker.filter(":checked").val()
        @customBg = @$customBgPicker.val()
        @text     = @$textPicker.filter(":checked").val()
        @image    = @$imagePicker.next(".image-wrap").find("img").attr("src")

        # Update bg and text color of the handle
        @$handle.attr("class", "acf-page-sections__handle acf-row-handle order ui-sortable-handle bg-#{@bg} color-#{@text}")
        if @bg == "custom"
            @$handle.css("background-color", @customBg)
        else
            @$handle.css("background-color", false)

        # Add bg image if any
        if @image
            @$handle.css("background-image", "url(" + @image + ")")
        else
            @$handle.css("background-image", "")

###
# Columns width preview
###
class ColumnWidthWatcher
    constructor: (@input) ->

        # On trigger : update row widths
        $("body").on "update-column-width", ".acf-sections-row__columns", (e) => @updateRow $(e.target)

        # Triger on input change
        $("body").on "keyup", ".acf-sections-row__columns " + @input, ->
            $(@).closest(".acf-sections-row__columns").trigger("update-column-width")

        # Trigger on mouseup in row (add/remove columns...)
        $("body").on "mouseup", ".acf-sections-row__columns", (e) ->
            setTimeout =>
                $(@).trigger("update-column-width")
            , 100

        # Trigger on page load
        $(@input).trigger("keyup")

    ###
    # Update a row's columns' widths
    ###
    updateRow: ($row) ->
        total = 0
        $row.find(@input).each -> total += parseInt $(@).val()
        $row.find(@input).each ->
            $(@).closest(".layout").css "flex", $(@).val()
            .children(".acf-fc-layout-handle").find(".no-thumbnail").text "Colonne [" + $(@).val() + "/" + total + "]"


$ ->
    # Skip if not a page builder
    unless $(".acf-page-sections, .acf-light-repeater").length then return

    # Section display
    setInterval ->
        $(".acf-page-sections__bg").map ->
            $section = $(@).closest(".acf-row")
            if $section.data("processed") || $section.is(".acf-clone") then return false
            new ACFSection $section

        # Choices tooltips
        $(".acf-choice .acf-input label:not(.acf-label-tooltip__parent)").each ->
            $(@).append "<span class='acf-label-tooltip'>" + $(@).text() + "</span>"
            $(@).addClass "acf-label-tooltip__parent"

        # Repeater tooltip
        $(".acf-light-repeater > .acf-input > .acf-flexible-content > .acf-actions > .button:not(.acf-label-tooltip__parent)").each ->
            text = $(@).text().trim() || "Copier/Coller" + (if $(@).closest(".acf-sections-content").length then " du contenu" else " une rangée")
            $(@).html "<span class='acf-label-tooltip acf-label-tooltip--right'>" + text + "</span>"
            $(@).addClass "acf-label-tooltip__parent"
    , 1000

    # Columns width
    new ColumnWidthWatcher ".acf-input > .acf-flexible-content > .values > .layout > .acfe-modal.-settings input[type='number']"



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