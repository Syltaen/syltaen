import Barba from "barba.js"
import global from "./../modules/global.coffee"
import formLoader from "./../modules/formLoader.coffee"

export default Barba.BaseView.extend
    namespace: "page"

    onEnterCompleted: ->
        global.in()
        formLoader.in()


    # onLeave: ->
    # onEnter: ->
    # onLeaveCompleted: ->