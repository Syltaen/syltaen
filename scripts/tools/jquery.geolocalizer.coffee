###
  * Geolocalize the client
  * @package Syltaen
  * @author Stanley Lambot
  * @requires jQuery
###

import $ from "jquery"
import ajax from "./../tools/ajax.coffee"

# ==================================================
# > JQUERY METHOD
# ==================================================
$.fn.geolocalizer = (config) -> if $(this).length then return new Geolocalizer $(this), config


# ==================================================
# > CLASS
# ==================================================
class Geolocalizer

    config:
        selectors:
            input:           ".geolocalizer__input"
            coord:           ".geolocalizer__coord"
            action:          ".geolocalizer__action"
            error:           ".geolocalizer__error"
            distance:        ".geolocalizer__distance"
            distanceTrigger: ".geolocalizer__distance__trigger"

    constructor: (@$el, customConfig) ->
        @config      = $.extend @config, customConfig

        # Nodes
        @$input           = @$el.find @config.selectors.input
        @$coord           = @$el.find @config.selectors.coord
        @$action          = @$el.find @config.selectors.action
        @$error           = @$el.find @config.selectors.error
        @$distance        = @$el.find @config.selectors.distance
        @$distanceTrigger = @$el.find @config.selectors.distanceTrigger

        # Events
        @$action.click => @getCurrentPosition()
        @$distanceTrigger.click =>
            @$distanceTrigger.closest("p").slideUp()
            @$distance.slideDown()

        @$input.change => @$coord.val "" # empty cached coord when input change

    ###
    # Get the current position from the browser
    ###
    getCurrentPosition: ->
        navigator.geolocation.getCurrentPosition ((pos) => @storePosition(pos)), (=> @showError()),
            enableHighAccuracy: false,
            timeout: 60000,
            maximumAge: 0

    ###
    # Store a location in the hidden fields
    ###
    storePosition: (position) ->
        unless position.coords.latitude || position.coords.longitude then return @showError()

        @$input.val "..."
        @$coord.val "#{position.coords.latitude},#{position.coords.longitude}"

        # Get the name from the coords
        ajax.get "reverse_geocoding",
            data:
                coord: { lat: position.coords.latitude, lng: position.coords.longitude }
            success: (name) =>
                @$input.val name

    ###
    # Display an error message
    ###
    showError: (err) ->
        @$error.show().html "La geolocalisation n'est pas disponible."