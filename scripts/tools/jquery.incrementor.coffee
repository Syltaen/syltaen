###
  * Make a number increment from 0 to its value
  * @package Syltaen
  * @author Stanley Lambot
  * @requires jQuery
###

import $ from "jquery"

# ==================================================
# > CLASSES
# ==================================================
###
  * @param {*}
  * @param int speed The speed to use
###
export default class Incrementor

    constructor: (@$el, @speed, @manual, incFrom = 0, incTo = false, @callBack = false) ->
        @txt      = @$el.html()
        @value    = @getValue(incFrom)
        @goal     = @getValue(incTo) || @getValue(@txt)
        @format   = @getFormat incFrom, (incTo || @txt)
        @step     = (@goal - @value) / (@speed / STEP_SPEED)
        @started  = false
        @interval = null

        # console.log "value", @value
        # console.log "goal", @goal
        # console.log "speed", @speed
        # console.log "step", @step
        # console.log "step_speed", STEP_SPEED
        # console.log "format", @format

        @createClone()
        @updateText()
        @check 0
        @getFormatedValue()


    ###
      * Create a non-animated copy of the element to display when printing and to reserve the space needed
    ###
    createClone: ->
        @$wrap      = $("<span class='incrementor-wrap'></span>")
        @$el.after @$wrap
        @$clone     = @$el.clone()

        @$wrapEl    = @$el.wrap("<span></span>").parent("span")
        @$wrapClone = @$clone.wrap("<span></span>").parent("span")

        @$wrap.append @$wrapEl
        @$wrap.append @$wrapClone

        @$wrap.css
            "position": "relative"
            "display": "inline-block"


        @$wrapClone.css
            "opacity": 0
            "visibility": "hidden"

        @$wrapEl.css
            "position": "absolute"
            "top": 0
            "left": 0
            "right": 0
            "bottom": 0

    ###
      * Get the format of a value
    ###
    getFormat: (fromTxt, toTxt) ->
        fromTxt = fromTxt + ""
        toTxt   = toTxt + ""
        refText = if fromTxt.length > toTxt.length then fromTxt else toTxt
        return refText.replace(/[\d]/g, "0").toString()


    ###
      * Get the value of a number, overlooking its formating
    ###
    getValue: (txt) ->
        return parseFloat(("" + txt).replace(/[^\d]/g, ""), 10)


    ###
      * Format a number
    ###
    getFormatedValue: ->
        formatedValue = @format
        stringValue   = Math.round(@value).toString()
        c             = stringValue.length - 1
        b             = formatedValue.length - 1

        # Fill the placehoders with the value
        while c >= 0
            char = stringValue[c]
            while b >= 0
                #  console.log(char + " -> " + formatedValue + "["+b+"] = " + formatedValue[b])
                if formatedValue[b] == "0"
                    formatedValue = formatedValue.slice(0, b) + char + formatedValue.slice(b + 1, formatedValue.length)
                    b--
                    break
                b--
            c--


        # Remove extra placeholder
        formatedValue = formatedValue.replace /\d+/g, (match) ->
            while (match[0] == "0") then match = match.slice(1, match.length)

            # match = if match == "" then "0" else match

            return match || "0"

        return formatedValue


    ###
      * Update the text in the element based on the guessed format
    ###
    updateText: ->
        @$el.html @getFormatedValue()


    ###
      * Start the incrementation for a number
    ###
    increment: ->
        @started = true
        @interval = setInterval =>
            @value += @step

            if (@step > 0 && @value < @goal) || (@step < 0 && @value > @goal)
                @updateText()
            else
                clearInterval @interval
                @value = @goal
                @$el.html @txt
                if @callBack then @callBack()

        , STEP_SPEED

    ###
      * Check if the incrementation should start based on the scroll value
    ###
    check: (scroll) ->

        top = parseFloat(@$el.offset().top, 10) - wH
        if (scroll >= top && !@started && !@manual)
            @increment()



###
  * Collection of incrementor objects
###
class IncrementorCollection

    constructor: () ->
        @items  = []
        @scroll = 0

    checkAll: ->
        @scroll = $(window).scrollTop()

        for item in @items then item.check @scroll


    startCheck: -> $(window).scroll => @checkAll()

    add: (incr) ->
        @items.push incr

    get: ($el) ->
        for i, item in @items
            if item.$el == $el then return item
        return false


# ==================================================
# > GLOBALS
# ==================================================
collection = new IncrementorCollection()
collection.startCheck()

wH = $(window).innerHeight()
$(window).resize -> wH = $(window).innerHeight()


STEP_SPEED = 100

# ==================================================
# > JQUERY METHODS
# ==================================================
###
  * Create an incrementor for each matching items
  * @param speed The animation speed
###
$.fn.incrementor = (speed = 1000, manual = false) ->

    $(this).each ->
        incr = new Incrementor $(this), speed, manual
        collection.add incr

    return $(this)


###
  * Trigger the incrementation for a number manualy
###
$.fn.increment = -> collection.get($(this)).increment()