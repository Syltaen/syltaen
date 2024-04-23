import $ from "jquery"
import "rangeslider.js"


export default class RangeField

    constructor: (@$input, @onUpdate = false) ->

        @$input.rangeslider
            polyfill: false

            rangeClass:      "rangefield"
            fillClass:       "rangefield__fill"
            handleClass:     "rangefield__handle"
            disabledClass:   "rangefield--disabled"
            horizontalClass: "rangefield--horizontal"
            verticalClass:   "rangefield--vertical"

            onSlide: (position, value) => @updateValue value

        @step = @$input.attr("step") || 1
        @min  = @$input.attr("min") || 1
        @max  = @$input.attr("max") || 100


        @$el = @$input.next(".rangefield")

        @labels = @getLabels()

        @addThemes()
        @addGraduations()
        @addValue()


    ###
    # Add custom themes
    ###
    addThemes: ->
        @themes = @$input.data("theme") && @$input.data("theme").split(" ")
        unless @themes && @themes.length then return false
        for theme in @themes then @$el.addClass "rangefield--#{theme}"


    ###
    # Add markings and values
    ###
    addGraduations: ->
        @$graduations = $("<ul class='rangefield__graduations'></ul>")

        @$graduations.append @labels.map (num) ->
            $("<li class='rangefield__graduations__item'><span>#{num}</span></li>")

        @$el.append @$graduations


    ###
    # Add the value to the handle
    ###
    addValue: ->
        @$value = $("<div class='rangefield__value'></div>")
        @$el.find(".rangefield__handle").append @$value

        @updateValue @$input.val()

    ###
    # Update the displayed value
    ###
    updateValue: (value) ->
        @$value.text @labels[value / @step]
        if @onUpdate then @onUpdate value



    getLabels: ->
        customLabels = if @$input.data("labels") then @$input.data("labels").split "," else false
        labels = []
        values = [(@min / @step) .. (@max / @step)]

        for value, i in values
            labels[value] = if customLabels then customLabels[i] else value

        return labels