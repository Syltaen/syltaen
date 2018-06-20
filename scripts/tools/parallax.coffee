import skrollr from "skrollr"
import $ from "jquery"


parallax = skrollr.init
    forceHeight: false
    smoothScrolling: false
    smoothScrollingDuration: 0


if parallax.isMobile() then parallax.destroy()
$(window).resize -> parallax.refresh()

export default parallax