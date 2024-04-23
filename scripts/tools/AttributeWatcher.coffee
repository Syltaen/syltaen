import $ from "jquery"

export default class AttributeWatcher

    constructor: (@stringCondition) ->
        @watchList  = @getWatchList(@stringCondition)
        @doOnChange = []

    ###
    # Register a new callback when the value changes
    ###
    onChange: (callback) ->
        @doOnChange.push callback
        @check()


    ###
    # Get a list of fields to keep an eye on
    ###
    getWatchList: (stringCondition) ->
        watchList = false
        stringCondition.match(/\{[^\}]+\}/g).map (item) ->
            name = item.replace /[\{\}]/g, ""
            field = $("[name='#{name}']")
            if field.length
                watchList = if watchList then watchList else {}
                watchList[name] = field

        # Add event for value changes
        for name, $field of watchList then $field.on "change keyup", => @check()
        return watchList


    ###
    # Get the value of the string attribute
    ###
    getParsedCondition: () ->
        string = @stringCondition

        for name, $field of @watchList
            name = name.replaceAll "[", "\\["
            name = name.replaceAll "]", "\\]"
            string = string.replace new RegExp("{" + name + "}", "g"), @getFieldStringValue($field)

        return string

    ###
    # Check the new value of the attribute and trigger callbacks
    ###
    check: ->
        @value = eval(@getParsedCondition())

        for callback in @doOnChange
            callback(@value, @)

    ###
    # Parse a field to get its value as a string
    ###
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