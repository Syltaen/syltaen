###
  * Show a field under conditions, or do any other action
  * @package Syltaen
  * @author Stanley Lambot
  * @require jQuery

  Usage :
  data-if="{fieldname}=='fieldbalue'"
  data-if-action="disable"
###

import $ from "jquery"
import AttributeWatcher from "./AttributeWatcher.coffee"

# ==================================================
# > JQUERY METHOD
# ==================================================
$.fn.if = (stringCondition, action = "show") -> new Condition($(this), stringCondition, action.split(":")[0] || "show", action.split(":")[1] || null)

# ==================================================
# > CLASS
# ==================================================
class Condition
    constructor: (@$el, @stringCondition, @action = "show", @params = null) ->
        @watch = new AttributeWatcher @stringCondition
        @watch.onChange (conditionResult) => if conditionResult then @doAction() else @undoAction()

    doAction: ->
        switch @action
            when "show" then @$el.show()
            when "slide" then @$el.slideDown()
            when "enable" then @$el.attr "disabled", false
            when "class" then @$el.addClass @params
            when "attr" then @$el.attr @params, @params
            when "uncheck" then @$el.prop "checked", false

    undoAction: ->
        switch @action
            when "show" then @$el.hide()
            when "slide" then @$el.slideUp()
            when "enable" then @$el.attr "disabled", "disabled"
            when "class" then @$el.removeClass @params
            when "attr" then @$el.removeAttr @params