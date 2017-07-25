import $ from "jquery"

# ==================================================
# > JQUERY METHOD
# ==================================================
$.fn.tableFilters = ->

    $(this).each (i, el) ->

        new FilterableTable($(el).closest("table"), $(el).find("select"))


# ==================================================
# > CLASS
# ==================================================
class FilterableTable
    constructor: (@$table, @$filters) ->
        @filters = {}

        @rows = @findRows()

        @bindUpdate()

    findRows: ->
        rows = []

        @$table.find("tr").each (i, el) =>
            row =
                $el: $(el)
                filters: {}
            $.each el.attributes, (i, attr) =>
                if attr.name.startsWith("data-filter-")
                    filter =  attr.name.replace("data-filter-", "")
                    row.filters[filter] = attr.value
            unless $.isEmptyObject(row.filters) then rows.push row

        rows

    bindUpdate: ->
        @$filters.change =>
            @filters = {}
            @$filters.each (i, el) =>
                if $(el).val() then @filters[$(el).attr("name")] = $(el).val()
            @filter()

    matchFilter: (row) ->
        match = true
        $.each @filters, (key, value) ->
            if row.filters[key] && row.filters[key] != value
                match = false
        return match

    filter: ->
        $.each @rows, (i, row) =>
            if @matchFilter row
                row.$el.show()
            else
                row.$el.hide()


