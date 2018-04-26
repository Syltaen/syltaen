import $ from "jquery"


export default class LinkedSelects

    constructor: (selects) ->
        @$selects = $(selects)

        @$selects.change => @checkAll()

        # selects.forEach (select) =>
        #     console.log $(select)
        #     $(select).change => @checkAll()
        #     @$selects.push $(select)

    checkAll: ->

        taken = []

        @$selects.each -> if $(this).val() then taken.push $(this).val()

        console.log taken

        @$selects.each (i, select) ->
            $(select).find("option").each (j, option) ->

                console.log i, j, $(select).val(), $(option).val(), taken.indexOf($(option).val())

                if taken.indexOf($(option).val()) >= 0 && $(select).val() != $(option).val()
                    option.disabled = true
                    console.log "true"
                else
                    option.disabled = false
                    console.log "false"


        @$selects.select2
            minimumResultsForSearch: Infinity
            placeholder: "Cliquez pour choisir"
            allowClear: true

