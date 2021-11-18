###
  * Repeat a specific HTML element by clicking a button
  * Auto-update fields names and values
  * @package Syltaen
  * @author Stanley Lambot
  * @require jQuery

  Usage :
  data-repeat="repeat_unique_id"
###

import $ from "jquery"
import SelectField from "./../tools/SelectField.coffee"
import "./../tools/jquery.showif.coffee"
import AttributeWatcher from "./AttributeWatcher.coffee"

# ==================================================
# > JQUERY METHOD
# ==================================================
$.fn.repeat = (config) -> new Repeater $(@), config


# ==================================================
# > CLASS
# ==================================================
class Repeater
    constructor: (@$wrap, customConfig = {}) ->
        @config = @getConfig(customConfig)
        @items  = @getItems()
        @setupHTML()
        @setupAutoValue @$wrap.data("repeat-auto")


    ###
    # Generate a config based on attributes and argumetns
    ###
    getConfig: (customConfig) ->
        return $.extend
            button: @$wrap.data("repeat") || false
            deleteConfirmation: @$wrap.data("repeat-delete-confirmation") || false
            updatedAttributes: ["name", "data-showif"]
        , customConfig


    ###
    # Add HTML classes and nodes
    ###
    setupHTML: ->
        @$wrap.addClass "repeater"
        @$wrap.attr "data-repeat-count", @items.length

        # Add "add" button
        if @config.button
            @$add = $("<span class='button button--primary repeater__add'><i class='fa fa-plus-circle'></i> #{@config.button}</span>")
            @$add.click => @addNewItem()
            @$wrap.append @$add


    ###
    # Create starting RepeatedItem
    ###
    getItems: ->
        return @$wrap.find("[data-repeat-item]")
            .map (i, el) => new RepeatedItem $(el), i, @
            .toArray()

    ###
    # Clone the last item without keeping fields
    ###
    addNewItem: ->
        @items[@items.length - 1].clone().resetFields()


    ###
    # Reset all indexes
    ###
    resetAllIndexes: ->
        for item, i in @items then item.setIndex i
        @$wrap.attr "data-repeat-count", @items.length


    ###
    # Make the number of fields depend on a predefined value
    ###
    setupAutoValue: (value = false) ->
        unless value then return

        watcher = new AttributeWatcher(value)
        watcher.onChange (count) =>
            count = Math.max(count, 1) # Minimum one item

            # Add new items until number is met
            while count > @items.length
                @addNewItem()

            # Remove items until number is met
            while @items.length > count
                @items[@items.length - 1].delete()



###
# A repeater item
###
class RepeatedItem
    constructor: (@$el, @index, @repeater) ->
        @$el.addClass("repeater__item")
        @$delete = @$el.find("[data-repeat-delete]").click => @delete()
        @$clone  = @$el.find("[data-repeat-clone]").click  => @clone()

    ###
    # Update the index, reset all fields names
    ###
    setIndex: (newIndex) ->
        # Update all attributes
        for attr in @repeater.config.updatedAttributes
            @$el.find("[#{attr}]").each (i, el) =>
                $(el).attr attr, $(el).attr(attr).replace "[#{@index}]", "[#{newIndex}]"

        # Update displayed index
        @$el.find("[data-repeat-index]").text newIndex + 1

        # Set the new index
        @index = newIndex


    ###
    # Clone this element
    ###
    clone: ->
        # Clone item and place it after this one
        $clone = @$el.clone()
        @$el.after $clone

        # Crete a new instance
        repeatedClone = new RepeatedItem $clone, @index, @repeater

        # Add it to the list at the right index
        @repeater.items.splice @index + 1, 0, repeatedClone

        # Reset all the indexes
        @repeater.resetAllIndexes()

        # Recreate all select2
        $clone.find(".select2").remove()
        $clone.find("select").each -> new SelectField $(@)
        @$el.find("select").each -> new SelectField $(@) # Don't know why it's necessary

        # Setup conditionnal display
        $clone.find("[data-showif]").each -> $(@).showif $(@).data("showif")

        return repeatedClone


    ###
    # Delete this item, reset all indexes
    ###
    delete: ->
        @$el.remove()
        @repeater.items.splice @index, 1
        @repeater.resetAllIndexes()


    ###
    # Reset all the fields in this element
    ###
    resetFields: ->
        @$el.find("[name]").val("").change()
        @$el.find(".form__error").remove()