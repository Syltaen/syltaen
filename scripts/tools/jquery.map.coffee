###
  * Create a Google Map and add filterable pins to it
  * @package Syltaen
  * @author Stanley Lambot
  * @requires jQuery
###

import $ from "jquery"
import L from "leaflet"
import { GestureHandling } from "leaflet-gesture-handling"
import "leaflet.markercluster"

# ==================================================
# > JQUERY METHOD
# ==================================================
$.fn.addMap = (config = {}) -> if $(this).length then return new Map $(this), config

# =============================================================================
# > CLASSES
# =============================================================================
class Map

    constructor: (@$map, customConfig = {}) ->
        @config   = @getConfig(customConfig)
        @map      = null

        @$markers = @$map.find(@config.markerSelector)
        @markers  = []
        @createMap()

        if @$map.data("center") && $(window).width() >= 960
            @map.setView @$map.data("center"), @config.startZoom

        if @config.geoloc && @config.geoloc.length
            @config.geoloc.click => @centerOnGeoloc()

    getConfig: (config) ->
        $.extend
            filters:            {}
            beforeCreate:       false
            afterCreate:        false
            onMarkerClick:      false
            onMarkerMouseIn:    false
            onMarkerMouseOut:   false
            onZoom:             false

            markerSelector:     ".map__marker"
            geoloc:             false

            # @see http://leaflet-extras.github.io/leaflet-providers/preview/index.html
            tilesServer: "https://server.arcgisonline.com/ArcGIS/rest/services/Canvas/World_Light_Gray_Base/MapServer/tile/{z}/{y}/{x}"
            maxZoom:            16 # Depend on the tile server
            startZoom:          10
            startCoord:         [50.6494449, 4.6522069] # belgium

            iconsFolder:        @$map.data("icons")
            iconSize:           [28, 44]
            iconAnchor:         [14, 44]
            popupAnchor:        [0, -30]

            fadeAnimation: false
            zoomAnimation: false
            # scrollWheelZoom: false

            # zoomControl: false
            gestureHandling: true
            gestureHandlingText:
                touch: "Utilisez deux\u00a0doigts pour d\u00e9placer la carte",
                scroll: "Vous pouvez zoomer sur la carte \u00e0 l'aide de CTRL+Molette de d\u00e9filement",
                scrollMac: "Vous pouvez zoomer sur la carte \u00e0 l'aide de \u2318+Molette de d\u00e9filement"
        , config

        # console.log LanguageContent


    # ==================================================
    # > MAP
    # ==================================================
    ###
    # Create the map and its markers
    ###
    createMap: ->
        if @config.beforeCreate then @config.beforeCreate @

        # Create the map
        @map = L.map(@$map[0], @config).setView(@config.startCoord, @config.startZoom)
        # L.control.zoom({ position: "topright" }).addTo(@map)

        # Add tiles layer
        L.tileLayer(@config.tilesServer, {
            maxZoom: @config.maxZoom,
            attributionControl: false
        }).addTo(@map)

        # Auto-add markers, if any
        @markersCluster = new L.MarkerClusterGroup()
        @$markers.each (i, el) => @addHTMLMarker $(el)
        @map.addLayer @markersCluster
        @center @markers

        # Custom events
        if @config.afterCreate then @config.afterCreate @
        if @config.onZoom then @map.on "zoomend", => @config.onZoom @
        @map.on "zoomend", => @removeTooltip()

    # ==================================================
    # > MARKERS
    # ==================================================
    ###
    # Add marker based on an HTML element
    ###
    addHTMLMarker: ($marker, attrs = {}) ->
        # Marker attributes
        attrs.id      = $marker.data("id") || false
        attrs.tooltip = $marker.data("tooltip") || false
        attrs.popup   = $marker.html()

        # Custom icon
        attrs.icon = @getCustomIcon $marker.data("icon") || "default", attrs

        # Filters
        attrs.filters = {}
        $.each $marker[0].attributes, (i, attr) -> if attr.name.match /^data-filter-/
            filter_value = $marker.data attr.name.replace("data-", "")
            if (typeof filter_value != "object") then filter_value = [filter_value]
            filter_value = filter_value.map (value) -> value.toString()
            attrs.filters[attr.name.replace("data-filter-", "")] = filter_value

        # Add the marker to the map
        @addMarker $marker.attr("data-lat"), $marker.attr("data-lng"), attrs


    ###
    # Add a marker to the map
      * attrs : icon (url), filters (keys-values), content (string)
    ###
    addMarker: (lat, lng, attrs = {}) ->
        marker = L.marker([lat, lng], attrs)

        @markersCluster.addLayer marker
        @markers.push marker

        # Set popup
        if attrs.popup
            marker.bindPopup attrs.popup
        # Set tooltip
        if attrs.tooltip
            marker.on "mouseover", (e) => @addTooltip marker, attrs.tooltip
            marker.on "mouseout", (e) => @removeTooltip()

        if @config.onMarkerClick then marker.on "click", (e) => @config.onMarkerClick marker, @
        if @config.onMarkerMouseIn then marker.on "mouseover", (e) => @config.onMarkerMouseIn marker, @
        if @config.onMarkerMouseOut then marker.on "mouseout", (e) => @config.onMarkerMouseOut marker, @

    ###
    # Get a marker by its ID
    ###
    getMarker: (id) ->
        for m in @markers
            if m.options.id == id then return m
        return null

    ###
    # Set the icons of all markers
    ###
    setAllMarkersIcon: (icon, url) ->
        @markers.forEach (m) =>
            m.setIcon @getCustomIcon icon, m.options
            $(m._icon).attr("data-state", icon)

    ###
    # Set the icon of a specific marker
    ###
    setMarkerIcon: (marker, icon) ->
        if marker
            marker.setIcon @getCustomIcon icon, marker.options
            $(marker._icon).attr("data-state", icon)

    ###
    # Add a marker to the map
      * attrs : icon (url), filters (keys-values), content (string)
    ###
    getCustomIcon: (icon = "default", attrs = {}) ->
        baseAttrs =
            iconUrl:      @config.iconsFolder + "marker-#{icon}.png"
            iconSize:     @config.iconSize
            iconAnchor:   @config.iconAnchor
            popupAnchor:  @config.popupAnchor
            # shadowUrl:    ""
            # shadowSize:   [20, 20]  # size of the shadow
            # shadowAnchor: [5, 20]   # the same for the shadow

        return L.icon $.extend baseAttrs, attrs

    # ==================================================
    # > CENTERING & GEOLOCATION
    # ==================================================
    ###
    # Center the map with a set of items' bounds
    ###
    center: (items = false, zoom = false, zoomIntensity = @config.startZoom) ->
        zoom = zoom || @config.startZoom

        if !items || items.length == 0
            @map.setView @config.startCoord, zoomIntensity
        else
            @map.fitBounds items.map (item) -> return item.getLatLng()

    ###
    # Center on all visible markers
    ###
    centerAll: ->
        @center @markers

    ###
    # Center the map on the user's position based on geolocation
    ###
    centerOnGeoloc: ->
        @config.geoloc.addClass "is-loading"
        navigator.geolocation.getCurrentPosition ((pos) => @centerOnCoord(pos.coords.latitude, pos.coords.longitude, 12)), (=> @showGeolocError()),
            enableHighAccuracy: false,
            timeout: 60000,
            maximumAge: 0

    ###
    # Show an error when the geolocation malfunctions
    ###
    showGeolocError: ->
        @config.geoloc.removeClass "is-loading"
        alert "Votre position n'a pas pu être déterminée. Veuillez vérifier que la géolocalisation est activée sur votre appareil."


    ###
    # Center the map on the specified coodonates
    ###
    centerOnCoord: (lat, lng, zoom = false) ->
        @map.setView { lat: lat, lng: lng }, zoom || @config.startZoom

    ###
    # Focus on a specific marker
    ###
    focusMarker: (marker, zoom = false) ->
        @centerOnCoord marker.getLatLng()["lat"], marker.getLatLng()["lng"], zoom || Math.max(@map.getZoom(), 13)

        @openCluster marker, =>
            @centerOnCoord marker.getLatLng()["lat"], marker.getLatLng()["lng"], zoom || Math.max(@map.getZoom(), 13)

    ###
    # Open a cluster recursively
    ###
    openCluster: (cluster, onOpen = false) ->
        # Has an icon : stop recursion and execute the callback
        if cluster._icon then return if onOpen then onOpen() else null

        # Open the cluster
        if cluster.__parent
            @openCluster cluster.__parent, ->
                $(cluster.__parent._icon).click()
                if onOpen then setTimeout ->
                    onOpen()
                , 100

    # ==================================================
    # > TOOLTIPS
    # ==================================================
    ###
    # Add a tooltip just after a specific marker
    ###
    addTooltip: (marker, text) ->
        @removeTooltip()

        xy = @$map.find(".leaflet-map-pane").css("transform")
        xy = xy.substring(0, xy.length - 1).split(",").slice(-2)

        @$tooltip = $("<div class='map__tooltip'>" + text + "</div>")
        @$tooltip.css
            left: $(marker._icon).offset().left - @$map.offset().left - xy[0]
            top: $(marker._icon).offset().top - @$map.offset().top - xy[1]

        $(marker._icon).after @$tooltip

    ###
    # Remove the tooltip
    ###
    removeTooltip: ->
        if @$tooltip
            @$tooltip.remove()
            @$tooltip = false


    # ==================================================
    # > FILTERS
    # ==================================================
  # ==================================================
    # > FILTERS
    # ==================================================
    ###
    # Dislpay only markers matching the filter
    ###
    applyFilters: ->
        if @config.beforeFilters then @config.beforeFilters @

        visibleMakers = []

        # for all markers
        $.each @markers, (i, marker) =>

            # flag used for the filter
            shouldHide = false

            # look one filter at a time
            for i, filter of @filters

                # Skip if the filter has no value
                unless filter.values.length then continue

                # Skip if the marker is not affected by this filter
                unless marker.options.filters.hasOwnProperty filter.key then continue

                # Hide if the marker value does not match the filter's value
                foundMatch = false

                for mof in marker.options.filters[filter.key]
                    for f in filter.values
                        if f.test mof
                            foundMatch = true
                            break

                unless foundMatch
                    shouldHide = true
                    break

            # If marker is visible, add its ID to the list
            unless shouldHide
                visibleMakers.push marker


        if !visibleMakers.length && @config.onFilterNoResult
            @config.onFilterNoResult @
        else
            @showMarkers(visibleMakers)


    ###
    # Show only certain markers
    ###
    showMarkers: (markers) ->
        newVisibleMarkersIDs = markers.map (marker) -> marker._leaflet_id

        # Compare new list of IDs to previous one, stop if didn't change
        if JSON.stringify(@visibleMarkersIDs) == JSON.stringify(newVisibleMarkersIDs) then return

        # Register new list of IDs
        @visibleMarkersIDs = newVisibleMarkersIDs.slice()

        # Show or hide marker one by one
        # $(marker._icon).addClass "is-hidden"
        #         $(marker._shadow).addClass "is-hidden"
        #     else
        #         $(marker._icon).removeClass "is-hidden"
        #         $(marker._shadow).removeClass "is-hidden"

        # Center on visible markers
        @center markers

        # Add class to map
        if markers.length
            @$map.removeClass "is-noresult"
        else
            @$map.addClass "is-noresult"

        # Trigger callback
        if @config.afterFilters then @config.afterFilters @, markers

    ###
    # Dislpay only markers matching the filter
    ###
    filterMarkers: (callback) ->
        visibleMakers = @markers.filter callback

        # Remove the previous markers cluster
        @markersCluster.remove()

        # Create a new markers cluster
        @markersCluster = new L.MarkerClusterGroup()
        for marker in visibleMakers then  @markersCluster.addLayer marker
        @map.addLayer @markersCluster

        # Center the map on the visible markers
        # @center visibleMakers

# =============================================================================
# > FILTER
# =============================================================================
class Filter
    constructor: (@key, @$el, @filterCallBack) ->
        @values = @previousValues = []
        @bindCallback()


    ###
    # Bind change callbacks for each field types
    ###
    bindCallback: ->
        # ========== SELECT FILTERS ========== #
        if @$el.is "select[multiple]"
            @$el.change =>
                @values = @$el.val().map (val) -> new RegExp("^" + val + "$")
                @checkChange()

        else if @$el.is "select"
            @$el.change =>
                @values = [@$el.val()]
                    .filter (i) -> i
                    .map (val) -> -> new RegExp("^" + val + "$")
                @checkChange()

        # ========== RADIO / CHECKBOX FILTERS ========== #
        else if @$el.is("[type='checkbox'], [type='radio']")
            @$el.change =>
                @values = $.map @$el.filter(":checked"), (el) -> new RegExp("^" + $(el).val() + "$")
                @checkChange()

        # ========== NUMBER / TEXT FILTERS ========== #
        else
            @$el.on "change keyup", =>
                @values = if @$el.val() then [new RegExp("^" + @$el.val())] else []
                @checkChange()

        @$el.change()

    ###
    # Compare each value to see if something changed
    ###
    checkChange: ->
        # Not the same length : change detected
        if @values.length != @previousValues.length then return @onChange()

        # Compare each item
        for value, i in @values
            # Item is different : change detected
            if value + "" != @previousValues[i] + "" then return @onChange()


    ###
    # When a change is detected
    ###
    onChange: ->
        @previousValues = @values.slice()
        @filterCallBack.call()