###
  * Show a field under conditions
  * @package Syltaen
  * @author Stanley Lambot
  * @require jQuery

  Usage :
  data-showif="{fieldname}=='fieldbalue'"
###

import $ from "jquery"
import AttributeWatcher from "./AttributeWatcher.coffee"

# ==================================================
# > JQUERY METHOD
# ==================================================
$.fn.showif = (stringCondition, action = "show") -> new Condition($(this), stringCondition, action)


# ==================================================
# > CLASS
# ==================================================
class Condition
    constructor: (@$el, @stringCondition, @action) ->
        @watch = new AttributeWatcher @stringCondition

        @watch.onChange (conditionResult) =>
            if conditionResult
                if @class then @$el.addClass(@class) else @doAction()
            else
                if @class then @$el.removeClass(@class) else @undoAction()


    doAction: ->
        switch @action
            when "show" then @$el.show()
            when "enable" then @$el.attr "disabled", false

    undoAction: ->
        switch @action
            when "show" then @$el.hide()
            when "enable" then @$el.attr "disabled", "disabled"