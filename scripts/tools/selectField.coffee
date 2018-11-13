import $ from "jquery"
import "select2"

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
        if (@$el.data("value") || @$el.data("value") is 0) then @$el.val @$el.data "value"

        disabled       = @$el.data "disabled"
        allowClear     = @$el.data "clearable"
        appendDropdown = @$el.data "append"
        noSearch       = @$el.data "nosearch"

        @$el.select2
            minimumResultsForSearch: if noSearch then Infinity else 8
            placeholder: @$el.attr("placeholder") || "Cliquez pour choisir"
            disabled: disabled
            allowClear: allowClear
            dropdownParent: if appendDropdown then @$el.parent() else null
            theme: false


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

