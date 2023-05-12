import $ from "jquery"
import "select2"
import en from "select2/src/js/select2/i18n/en"

export default class SelectField

    constructor: (@$el, @done = false) ->
        @group()

        @$el.click()
        @select2()

        if @done then @done @$el


    ###
    # Transform the select with select2
    ###
    select2: () ->
        if (@$el.attr("value") || @$el.attr("value") is 0)
            value = @$el.attr("value")
            value = if value[0] == "[" then JSON.parse(value) else value
            @$el.val value

        disabled       = @$el.attr "disabled"
        allowClear     = @$el.attr "clearable"
        appendDropdown = @$el.attr "append"
        noSearch       = @$el.attr "nosearch"
        autoSubmit     = @$el.attr "autosubmit"
        tags           = @$el.attr "tags"
        source         = if @$el.attr("source") then ajaxurl + "?action=options_" + @$el.attr("source") else null

        # Create the field
        @select2 = @$el.select2
            language: en
            data: @getHTMLOptions()
            minimumResultsForSearch: if noSearch then Infinity else 8
            placeholder: @$el.attr("placeholder") || "Click here to make a choice"
            disabled: disabled
            allowClear: allowClear
            tags: tags
            dropdownParent: if appendDropdown then @$el.parent() else null
            theme: @$el.attr("theme") || false

            # Ajax
            minimumInputLength: @$el.attr("min-input-length") || 0
            ajax: unless source then null else
                url: source
                dataType: "json"
                data: (params) =>
                    params.form = @$el.closest("form").serializeArray()
                    return params

            ###
            Allow the use of HTML in options
            ###
            escapeMarkup: (markup) ->
                return markup

        # appendDropdown
        if appendDropdown
            @$el.parent().css("position", "relative")


    ###
    # Create optgroup automagically
    ###
    group: () ->
        # Should group?
        if @$el.find("option").eq(1).text().indexOf("|") <= 0 then return false

        defaultValue = @$el.val()

        # Create groups
        groups = {}
        @$el.find("option").each -> if $(@).text().indexOf("|") > 0
            parts = $(@).text().split("|")
            group = parts[0].trim()
            label = parts[1].trim()
            value = $(@).val()

            # Group does not exists
            if !groups[group] then groups[group] = {}

            # Add to the group
            groups[group][value] = label

        # Render groups
        groupRender = if @$el.find("option").first().text() then "" else "<option></option>"
        for group, options of groups
            groupRender += "<optgroup label='#{group}'>"
            for value, label of options then groupRender += "<option value='#{value}'>#{label}</option>"
            groupRender += "</optgroup>"

        @$el.html groupRender

        # reset the value
        @$el.val(defaultValue)

    ###
    # Get the field's options
    ###
    getHTMLOptions: ->
        data = []

        for id, text of @$el.data("options") || {}
            data.push
                id: id
                text: text

        return data