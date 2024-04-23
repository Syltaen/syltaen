import Alpine from "alpinejs"
import { setkey, getkey } from "./nestedkeys.coffee"
import normalizeForSearch from "normalize-for-search"

window.Alpine = Alpine

# =============================================================================
# > COMMON METHODS
# =============================================================================
Alpine.magic "increment", (el) -> (num, incr = 1, min = null, max = null) ->
    num = +(num || 0) + +incr
    if max != null && num > max then num = max
    if min != null && num < max then num = min
    return num

Alpine.magic "radioGrid", (el) -> (data, field, others) ->
    setTimeout ->
        for other in others
            if data[other] == data[field]
                data[other] = null
    , 100

Alpine.magic "matchSearch", (el) -> (search, index) ->
    index = normalizeForSearch(index)
    for word in normalizeForSearch(search).split(" ")
        if index.indexOf(word) == -1 then return false
    return true

Alpine.magic "triggerResize", (el) -> (time = 0) ->
    setTimeout ->
        dispatchEvent(new Event("resize"))
    , time

# Select2 : add x-init="\$select2(\$data)"
Alpine.magic "select2", (el) -> (data) ->
    $(el).on "change", -> data[$(@).attr("x-model")] =  $(@).val()

# =============================================================================
# > COMPONENTS INIT
# =============================================================================
Alpine.$form = ($data, form) ->
    $(form).data("reactive", $data).find("[name]").each (i, el) ->
        name = $(el).attr("name").replaceAll("[]", "")
        $(el).attr("x-model", name.replaceAll("[", "['").replaceAll("]", "']"))

        switch $(el).attr("type")
            when "radio"
                if getkey($data, name) == undefined then setkey $data, name, ""
                if $(el).is(":checked") then setkey $data, name, $(el).val()

            when "checkbox"
                setkey $data, name, getkey($data, name) || []
                if $(el).is(":checked") then getkey($data, name).push $(el).val()

            else
                setkey $data, name, $(el).val()
                if ($(el).is("select"))
                    $(el).on "change", -> setkey $data, name, $(el).val()
                    $data.$watch name, (v) -> $(el).trigger("change.select2")

# ==================================================
# > INIT
# ==================================================
Alpine.start()