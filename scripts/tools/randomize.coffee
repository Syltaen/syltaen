###
  * Ransomize a set of elements
  * @package Syltaen
  * @author Stanley Lambot
  * @require jQuery
###

import $ from "jquery"

$.fn.randomize = (selector) ->
    wrapper = if selector then $(@).find(selector) else $(this).parent()

    wrapper.each ->
        $(@).children(selector).sort ->
            return Math.random() - 0.5
        .detach().appendTo(@)

    return wrapper.children(selector)