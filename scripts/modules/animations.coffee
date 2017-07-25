import $ from "jquery"


# ==================================================
# > INCREMENTOR
# ==================================================
import "./../tools/incrementor.coffee"

$ -> $(".incrementor").each (i, el) -> $(el).incrementor()


# ==================================================
# > PARALLAX
# ==================================================
import skrollr from "skrollr"

parallax = skrollr.init
    forceHeight: false
    smoothScrolling: false
    smoothScrollingDuration: 0

if parallax.isMobile() then parallax.destroy()
$(window).resize -> parallax.refresh()
