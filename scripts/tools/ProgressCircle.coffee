import $ from "jquery"
import "jquery.transit"

export default class ProgressCircle

    constructor: (@progress = 0) ->
        @setupElements()
        @setProgress(@progress)


    ###
    # Create the different HTML nodes
    ###
    setupElements: ->
        @$el     = $("<div class='progress-circle'></div>")

        @$title  = $("<h2 class='h3 text-align-center'>Transfert en cours...</h2>")

        @$wrap  =  $("<div class='progress-circle__wrap'></div>")
        @$left   = $("<div class='progress-circle__half progress-circle__half--left'><div class='progress-circle__progress'></div></div>")
        @$leftp  = @$left.find(".progress-circle__progress")
        @$right  = $("<div class='progress-circle__half progress-circle__half--right'><div class='progress-circle__progress'></div></div>")
        @$rightp = @$right.find(".progress-circle__progress")
        @$digit  = $("<div class='progress-circle__digit'>#{@digit}%</div>")

        @$wrap.append @$left, @$right, @$digit
        @$el.append @$title, @$wrap, @$cancel


    ###
    # Add the element in a specific place
    ###
    addTo: ($parent) ->
        $parent.append @$el
        $parent.addClass "progress-circle__parent"

    ###
    # Set the progression value
    ###
    setProgress: (progress) ->
        @progress = Math.round(progress)
        @progress = if @progress < 0 then 0 else @progress
        @progress = if @progress > 100 then 100 else @progress

        # Display digit
        @$digit.text @progress + "%"

        # Set right part
        rotation_right = if @progress >= 50 then 0 else (@progress * 3.6) - 180
        @$rightp.css "rotate", rotation_right + "deg"

        # Set left part
        rotation_left = if @progress <= 50 then -180 else ((@progress - 50) * 3.6) - 180
        @$leftp.css "rotate", rotation_left + "deg"

        # Set left part