import $ from "jquery"
import "jquery.transit"

# =============================================================================
# > ACF PAGE BUILDER
# =============================================================================

class AutoLayoutWatcher
    constructor: (@wrap, @addAction) ->
        @check()

        # Direct adding by clicking a button
        if @addAction
            $(document).on "click", @addAction, => @check()

        # Ajax adding
        else
            $(document).ajaxSuccess () => @check()


    check: ->
        $(@wrap).filter(":visible").each (i, el) =>
            $emptyLayout = $(el).children(".acf-input").children(".acf-flexible-content.-empty")
            if $emptyLayout.length
                @autoAdd $emptyLayout

            # $(@wrap).closest(".acfe-modal-content").stop().animate({ "scrollTop": 999999 }, 100)

    autoAdd: ($el) ->
        $el.children(".acf-actions").find("[data-name='add-layout']").click()



$ ->
    # Skip if not a page builder
    unless $(".acf-page-sections, .acf-light-repeater").length then return

    # Section display
    setInterval ->
        # $(".acf-page-sections__bg").map ->
        #     $section = $(@).closest(".acf-row")
        #     if $section.data("processed") || $section.is(".acf-clone") then return false
        #     new ACFSection $section

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

    # Auto-add rows and columns when empty
    new AutoLayoutWatcher ".acf-sections-row", ".acf-page-sections > .acf-input > .acf-repeater > .acf-actions > [data-event='add-row']"
    new AutoLayoutWatcher ".acf-sections-row__columns"



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