import $ from "jquery"

# =============================================================================
# > ROW
# =============================================================================
class Row
    constructor: (@$tr) ->
        @cells = @getCellsData()
        # console.log @cells

    ###
    # Get text data of each cell
    ###
    getCellsData: ->
        return @$tr.find("td")
            .map -> $(@).text().trim()
            .toArray()

    ###
    # Check that the row's cells match the filters
    ###
    matchFilters: (filters) ->
        for search, index in filters
            if search && !@cells[index].match search
                return false
        return true

    ###
    # Shortcuts
    ###
    hide: -> @$tr.show()
    show: -> @$tr.hide()


# =============================================================================
# > HEADING
# =============================================================================
class Heading
    constructor: (@$th, @table, @index) ->
        if @$th.is("[data-order]") then @setupOrdering()
        if @$th.is("[data-filter]") then @setupFilter()

    ###
    # Create events for ordering the table on this column
    ###
    setupOrdering: ->
        @$th.click =>
            console.log @table
            @table.setOrderColumn @index

    ###
    # Set the order direction
    ###
    setOrder: (order) ->
        @$th.attr("data-order", if order then order else "")


    ###
    # Setup filter for this column
    ###
    setupFilter: ->
        @table.prepareFilters()
        if @$th.data("filter") == "dropdown"
            @$filter = @createDropdownFilter()
        else
            @$filter = @createFreeFilter()

        @table.$filters.find("td:eq(" + @index + ")").append @$filter

    createFreeFilter: ->
        $filter = $("<input type='text' placeholder='Filter'>")
        $filter.on "keyup", => @table.setFilter @index, @$filter.val()
        return $filter

    createDropdownFilter: ->
        $filter = $("<select data-clearable='clearable' data-append='append'><option></option></select>")
        @table
            # Get all options
            .rows.map (row) => return row.cells[@index]
            # Keep unique
            .filter (v, i, self) -> self.indexOf(v) == i
            # Sort alphabeticaly
            .sort (a, b) -> if a > b then 1 else -1
            # Add as <option> in the select field
            .map (value) -> $filter.append("<option value='" + value + "'>" + value + "</option>") #
        $filter.on "change", => @table.setFilter @index, @$filter.val()
        return $filter


# =============================================================================
# > TABLE CONTROLLER
# =============================================================================
export default class FilteredTable
    constructor: (@$table) ->
        @$body    = @$table.find("tbody")
        @rows     = @getRows()
        @headings = @getHeadings()

        @order       = "ASC"
        @orderColumn = -1
        @filters     = []


    ###
    # Create headings instances
    ###
    getHeadings: ->
        return @$table.find("thead tr th, thead tr td")
            .map (i, el) => new Heading $(el), @, i
            .toArray()

    ###
    # Create rows instances
    ###
    getRows: ->
        return @$table.find("tbody tr")
            .map (i, el) => new Row $(el), @
            .toArray()

    ###
    # Refresh the display of the rows and the pagination when something changes
    ###
    render: ->
        @$body.html("")

        for row in @rows
            if row.matchFilters(@filters)
                @$body.append row.$tr


    # ==================================================
    # > ORDERING
    # ==================================================
    setOrderColumn: (orderColumn) ->
        # Switch order if on same column
        if orderColumn == @orderColumn
            @order = if @order == "ASC" then "DESC" else "ASC"

        # Set the order on the heading
        if @headings[@orderColumn] then @headings[@orderColumn].setOrder(false)
        @orderColumn = orderColumn
        if @headings[@orderColumn] then @headings[@orderColumn].setOrder(@order)

        @rows = @rows.sort (a, b) =>
            if @order == "ASC" && a.cells[@orderColumn] > b.cells[@orderColumn]
                return 1
            if @order == "DESC" && a.cells[@orderColumn] < b.cells[@orderColumn]
                return 1
            return -1

        # Refresh display
        @render()


    # ==================================================
    # > FILTERING
    # ==================================================
    setFilter: (index, value) ->
        @filters[index] = if value then new RegExp(value, "gi") else false
        @render()

    ###
    # Add new row for filters
    ###
    prepareFilters: ->
        if @$filters then return # Already prepared
        @$filters = $("<tr class='table__filters'></tr>")
        @$table.find("thead tr th, thead tr td").each => @$filters.append("<td></td>")
        @$table.find("thead").append @$filters