import skrollr from "skrollr"
import $ from "jquery"


parallax = skrollr.init
    forceHeight: false
    smoothScrolling: false
    smoothScrollingDuration: 0

if parallax.isMobile()
    parallax.destroy()
    parallax = false
else
    $(window).resize -> parallax.refresh()

export default parallax