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
class Incrementor

    constructor: (@$el, @speed, @manual) ->
        @format   = ""
        @top      = 0
        @goal     = 0
        @value    = 0
        @step     = 0
        @started  = false
        @interval = null

        @txt      = @$el.html()
        @goal     = @getValue @txt
        @format   = @getFormat @txt
        @top      = parseFloat(@$el.offset().top, 10) - wH
        @step     = @goal/(@speed/STEP_SPEED)

        @updateText()
        @check 0
        @getFormatedValue()



    ###
      * Get the format of a value
    ###
    getFormat: (txt) ->
        return txt.replace(/[\d]/g,"0").toString()


    ###
      * Get the value of a number, overlooking its formating
    ###
    getValue: (txt) ->
        return parseFloat(txt.replace(/[^\d]/g, ""), 10)


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
                    formatedValue = formatedValue.slice(0, b)+char+formatedValue.slice(b+1, formatedValue.length)
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
            @value += @step;
            @updateText()

            if @value >= @goal
                clearInterval @interval
                @value = @goal
                @$el.html @txt
        , STEP_SPEED

    ###
      * Check if the incrementation should start based on the scroll value
    ###
    check: (scroll) ->
        if (scroll >= @top && !@started && !@manual)
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

