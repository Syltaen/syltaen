import $ from "jquery"


# ==================================================
# > COLLAPSABLES
# ==================================================
import "./../tools/collapsable.coffee"

$ -> $(".collapsable").each (i, el) -> $(el).collapsable()


# ==================================================
# > SLIDERS
# ==================================================
import "slick-carousel" # @see https://github.com/kenwheeler/slick/

$ -> $(".slide-exemple").slick()


# ==================================================
# > GOOGLE MAP
# ==================================================
# import "./../tools/gmap.coffee"

# $ -> $(".gmap").gmap()


# ==================================================
# > SHADOWBOX
# ==================================================
import Shadowbox from "./../tools/shadowbox.coffee"

$ ->
    sb = new Shadowbox()

    $(".sb-trigger").click ->
        sb.html("<h1>Shadowbox</h1>").show()



# ==================================================
# > CONDITIONAL DISPLAY
# ==================================================
import "./../tools/showif.coffee"
$ -> $(".sb-trigger").showif "#condition-exemple", "show"



# ==================================================
# > TABLE FILTERS
# ==================================================
import FilterableTable from "./../tools/tableFilters.coffee"

$ -> $(".table-filters").tableFilters()


