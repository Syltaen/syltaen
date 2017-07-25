###
  * Create a Google Map and add filterable pins to it
  * @package Syltaen
  * @author Stanley Lambot
  * @requires jQuery
###


# ==================================================
# > JQUERY METHOD
# ==================================================
 $.fn.gmap = (filtersSelector) ->
    if $(this).length
        new GMap($(this), filtersSelector).init()
    else
        false

# ==================================================
# > CLASS
# ==================================================
class GMap = ->

    constructor: (@$wrapper, @filtersSelector) ->

        @$map = $wrapper.find ".map"
        @map  = null

        @$markers = @$map.find(".marker")
        @markers  = []

        @$filters = $wrapper.find filtersSelector
        @filters  = null

        @infobox = null
        @defaultArgs =
            zoom: 3
            center: new (google.maps.LatLng)(0, 0)
            mapTypeId: google.maps.MapTypeId.ROADMAP
            scrollwheel: false

    ###
      * Handel map creating and filters binding
    ###
    init: ->
        @createMap()
        self.$filters.click (e) =>
          e.stopPropagation()
          @$filters.removeClass "selected"
          $(e.target).addClass "selected"
          @filterMarkers()


    ###
      * Create the map and its markers
    ###
    createMap: ->
        @map = new (google.maps.Map)(@$map[0], @defaultArgs)
        @$markers.each (i, el) => @addMarker $(el)
        @filterMarkers()

    ###
      * Add a marker to the map
      * @param jQueryNode $marker The HTML element storing the marker data
    ###

    addMarker: ($marker) ->
      marker = new (google.maps.Marker)(
        position: new (google.maps.LatLng)($marker.data('lat'), $marker.data('lng'))
        map: @map
        filter: $marker.data('filter')
        icon: url: $marker.data('icon'))
      @markers.push marker
      if $marker.html()
        infowindow = new (google.maps.InfoWindow)(content: $marker.html())
        ((self) ->
          google.maps.event.addListener marker, 'click', ->
            if self.infowindow
              self.infowindow.close()
            self.infowindow = infowindow
            self.infowindow.open self.map, marker
            return
          return
        ) this
      return

    ###
      * Dislpay only markers matching the filter
    ###
    filterMarkers: ->
      @filters = []
      if @infowindow
        @infowindow.close()
      ((self) ->
        self.$filters.each ->
          if $(this).hasClass('selected')
            self.filters.push $(this).data('filter')
          return
        $.each self.markers, (i, marker) ->
          if self.filters.indexOf(marker.filter) >= 0
            marker.setVisible true
          else
            marker.setVisible false
          return
        return
      ) this
      @center()
      return

    ###
      * Center the map to display only visible markers
    ###
    center: ->
      bounds = new (google.maps.LatLngBounds)
      visible_markers = []

      $.each @markers, (i, marker) ->
        if marker.visible
          bounds.extend new (google.maps.LatLng)(marker.position.lat(), marker.position.lng())
          visible_markers.push marker

      if visible_markers.length == 0
        @map.setCenter @defaultArgs.center
        @map.setZoom @defaultArgs.zoom
      else if visible_markers.length == 1
        @map.setCenter bounds.getCenter()
        @map.setZoom 10
      else
        @map.fitBounds bounds
      return

    return

