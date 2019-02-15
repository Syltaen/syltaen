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
$.fn.showif = (stringCondition, classToAdd = false) -> new Condition($(this), stringCondition, classToAdd)


# ==================================================
# > CLASS
# ==================================================
class Condition
    constructor: (@$el, @stringCondition, @class) ->
        @watchList = @getWatchList(@stringCondition)

        for name, $field of @watchList then $field.change => @check()
        @check()


    getWatchList: (stringCondition) ->
        watchList = {}
        stringCondition.match(/\[[^\]]+\]/g).map (item) ->
            name = item.replace /[\[\]]/g, ""
            watchList[name] = $("[name='#{name}']")
        return watchList


    getParsedCondition: () ->
        string = @stringCondition

        for name, $field of @watchList
            string = string.replace new RegExp("\\["+name+"\\]", "g"), @getFieldStringValue($field)

        return string

    check: ->
        console.log @getParsedCondition()
        if eval(@getParsedCondition())
            if @class then @$el.addClass(@class) else @$el.show()
        else
            if @class then @$el.removeClass(@class) else @$el.hide()


    getFieldStringValue: ($field) ->
        switch $field.attr "type"
            when "radio"
                value = $field.filter(":checked").val()
            when "checkbox"
                value = []
                $field.filter(":checked").each -> value.push $(@).val()
            else
                value = $field.val()

        return JSON.stringify value