###
  * Show a field under conditions
  * @package Syltaen
  * @author Stanley Lambot
  * @require jQuery
###

import $ from "jquery"

# ==================================================
# > JQUERY METHOD
# ==================================================
$.fn.showif = (fieldSelector, value = true, classToAdd = false) ->

    $field = $(fieldSelector)

    if $field.length
        new Condition($(this), $field, value, classToAdd)
    else
        false

# ==================================================
# > CLASS
# ==================================================
class Condition

    constructor: (@$el, @$field, @goalValue, @class) ->

        @goalValue = unless Array.isArray(@goalValue) then [@goalValue] else @goalValue

        @$field.change => @check()
        @check()

    isGoal: ->
        common = @value.filter (n) => @goalValue.indexOf(n) isnt -1
        return common.length > 0

    getValue: ->
        @value = @$field.val()

        switch @$field.attr "type"
            when "radio"
                @value = @$field.filter(":checked").val()

            when "checkbox"
                @value = []
                @$field.filter(":checked").each (i, el) =>
                    @value.push $(el).val()

        @value = unless Array.isArray(@value) then [@value] else @value

    check: ->
        @getValue()

        if @isGoal()
            if @class
                @$el.addClass @class
            else
                @$el.show()
        else
            if @class
                @$el.removeClass @class
            else
                @$el.hide()

