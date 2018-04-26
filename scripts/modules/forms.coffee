import $ from "jquery"
import "../tools/ninja-forms.coffee"

# ==================================================
# > USE SELECT 2 FOR SELECT INPUT
# ==================================================
$ -> $("select").each (i, el) ->

    $el = $(el)

    if ($el.data("value") || $el.data("value") is 0) then $el.val $el.data "value"

    disabled       = $el.hasClass "disabled"
    allowClear     = $el.hasClass "clearable"
    appendDropdown = $el.data("append-dropdown")
    noSearch       = $el.data("no-search")

    $el.select2
        minimumResultsForSearch: if noSearch then Infinity else 5
        placeholder: $el.attr("placeholder") || "Cliquez pour choisir"
        disabled: disabled
        allowClear: allowClear
        dropdownParent: if appendDropdown then $el.parent() else null


# ==================================================
# > PATTERN
# ==================================================
$ ->

    $("html").on "keyup", "input[data-pattern]", ->
        pattern = $(@).attr("data-pattern")
        val     = $(@).val()

        while val && !val.match pattern
            val = val.substr(0, val.length - 1)

        $(@).val val


