###
  * Show a field under conditions
  * @package Syltaen
  * @author Stanley Lambot
  * @require jQuery

  Usage :
  data-showif="{fieldname}=='fieldbalue'"
###

import $ from "jquery"

# ==================================================
# > JQUERY METHOD
# ==================================================
$.fn.showif = (stringCondition, action = "show") -> new Condition($(this), stringCondition, action)


# ==================================================
# > CLASS
# ==================================================
class Condition
    constructor: (@$el, @stringCondition, @action) ->
        @watchList = @getWatchList(@stringCondition)

        if @watchList
            for name, $field of @watchList then $field.change => @check()
            @check()


    getWatchList: (stringCondition) ->
        watchList = false
        stringCondition.match(/\{[^\}]+\}/g).map (item) ->
            name = item.replace /[\{\}]/g, ""
            field = $("[name='#{name}']")
            if field.length
                watchList = if watchList then watchList else {}
                watchList[name] = field
        return watchList

    getParsedCondition: () ->
        string = @stringCondition

        for name, $field of @watchList
            name = name.replace "[", "\\["
            name = name.replace "]", "\\]"
            string = string.replace new RegExp("{" + name + "}", "g"), @getFieldStringValue($field)

        return string

    check: ->
        if eval(@getParsedCondition())
            if @class then @$el.addClass(@class) else @doAction()
        else
            if @class then @$el.removeClass(@class) else @undoAction()


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

    doAction: ->
        switch @action
            when "show" then @$el.show()
            when "enable" then @$el.attr "disabled", false

    undoAction: ->
        switch @action
            when "show" then @$el.hide()
            when "enable" then @$el.attr "disabled", "disabled"