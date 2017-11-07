###
  * Create a Google Map and add filterable pins to it
  * @package Syltaen
  * @author Stanley Lambot
  * @requires jQuery
###

import $ from "jquery"

# ==================================================
# > JQUERY METHOD
# ==================================================
$.fn.gmap = (config) ->
    if $(this).length then return new GMap $(this), config


# ==================================================
# > CLASS
# ==================================================
class Filter
    constructor: (@key, @$el, @filterCallBack) ->

        @value = []
        @bindCallback()


    bindCallback: ->
        # ========== SELECT FILTERS ========== #
        if @$el.is "select"

            @value = [@$el.val()]

            @$el.change =>
                @value = [@$el.val()]
                @filterCallBack.call()

        # ========== LI FILTERS ========== #
        if @$el.is("li")
            @$el.each (i, el) => $(el).click (e) =>
                e.stopPropagation()
                @$el.removeClass "selected"
                $(el).addClass "selected"
                @value = [$(el).data("value")]
                @filterCallBack.call()


class GMap

    @config:
        filters: []
        beforeCreate:       false
        afterCreate:        false
        beforeFilters:      false
        afterFilters:       false
        onMarkerClick:      false

    constructor: (@$wrapper, customConfig) ->

        # update the config
        @config      = $.extend @config, customConfig

        @$map        = @$wrapper.find ".map"
        @map         = null

        @$markers    = @$map.find(".marker")
        @markers     = []

        @infobox     = null
        @defaultArgs =
            zoom: 3
            center: new (google.maps.LatLng)(0, 0)
            mapTypeId: google.maps.MapTypeId.ROADMAP
            scrollwheel: false

        @createMap()

        @filters = []
        for filter, selector of @config.filters
            @filters[filter] = new Filter filter, @$wrapper.find(selector), => @applyFilters()

        @applyFilters()


    ###
      * Create the map and its markers
    ###
    createMap: ->
        if @config.beforeCreate then @config.beforeCreate @

        @map = new (google.maps.Map)(@$map[0], @defaultArgs)
        @$markers.each (i, el) => @addMarker $(el)

        if @config.afterCreate then @config.afterCreate @


    ###
      * Add a marker to the map
      * @param jQueryNode $marker The HTML element storing the marker data
    ###
    addMarker: ($marker) ->
        marker = new google.maps.Marker
            position: new google.maps.LatLng $marker.data("lat"), $marker.data("lng")
            map: @map
            filters: {}
            icon:
                url: $marker.data "icon"

        # Add filters
        $.each $marker[0].attributes, (i, attr) ->
            if attr.name.startsWith("data-filter-")
                key =  attr.name.replace("data-filter-", "")
                marker.filters[key] = attr.value

        # Add infowindow
        if $marker.html()
            marker.content = $marker.html()
            marker.infowindow = new google.maps.InfoWindow
                content: marker.content

            google.maps.event.addListener marker, "click", =>
                if @config.onMarkerClick
                    @config.onMarkerClick marker, @
                else
                    @openInfobox marker

        # add the marker to the collection
        @markers.push marker


    ###
      * Default callback when clicking a marker : Open its infobox
      * @param maker
    ###
    openInfobox: (marker) ->
        if @infowindow then @infowindow.close()
        @infowindow = marker.infowindow
        @infowindow.open @map, marker


    ###
      * Dislpay only markers matching the filter
    ###
    applyFilters: ->

        if @config.beforeFilters then @config.beforeFilters @

        if @infowindow then @infowindow.close()

        visibleMakers = []

        # for all markers
        $.each @markers, (i, marker) =>

            # flag used for the filter
            shouldHide = false

            # look each filters and their values
            for i, filter of @filters then if filter.value.length then for value in filter.value then if value

                # if the marker should be filtered
                if marker.filters.hasOwnProperty filter.key

                    # if the marker does not match the filter value, hide it
                    unless marker.filters[filter.key] == value then shouldHide = true

            # hide or show the marker
            marker.setVisible !shouldHide

            unless shouldHide then visibleMakers.push marker

        @center()

        if @config.afterFilters then @config.afterFilters @, visibleMakers


    ###
      * Center the map to display only visible markers
    ###
    center: (focusedMarkers = false, zoom = 10) ->

        bounds = new (google.maps.LatLngBounds)

        # a set of defined marker or all of them
        focusedMarkers = focusedMarkers || @markers

        visible_markers = []
        $.each focusedMarkers, (i, marker) ->
            if marker.visible
                bounds.extend new google.maps.LatLng marker.position.lat(), marker.position.lng()
                visible_markers.push marker

        if visible_markers.length == 0
            @map.setCenter @defaultArgs.center
            @map.setZoom @defaultArgs.zoom

        else if visible_markers.length == 1
            @map.setCenter bounds.getCenter()
            if zoom then @map.setZoom zoom

        else
            @map.fitBounds bounds
