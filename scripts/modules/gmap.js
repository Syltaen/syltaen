/**
 * Create a Google Map and add filterable pins to it
 * @package Syltaen
 * @author Stanley Lambot
 * @requires jQuery
 */

(function($) {

    $.fn.gmap = function (filtersSelector) {
        if ($(this).length) {
            return new GMap($(this), filtersSelector).init();
        } else {
            return false;
        }
    }

    function GMap($wrapper, filtersSelector) {

        this.$wrapper = $wrapper;
        this.$map     = $wrapper.find(".map");
        this.$markers = this.$map.find(".marker");
        this.map      = null;
        this.$filters = $wrapper.find(filtersSelector);
        this.filters  = null;

        this.markers = [];
        this.infobox = null;

        this.defaultArgs = {
            zoom:        3,
            center:      new google.maps.LatLng(0, 0),
            mapTypeId:   google.maps.MapTypeId.ROADMAP,
            scrollwheel: false
        };

        /**
         * Handel map creating and filters binding
         */
        this.init = function () {
            this.createMap();

            (function(self) {
                self.$filters.click(function (e) {
                    e.stopPropagation();
                    self.$filters.removeClass("selected");
                    $(this).addClass("selected");
                    self.filterMarkers();
                })
            }(this));

            return this;
        }


        /**
         * Create the map and its markers
         */
        this.createMap = function () {
            this.map = new google.maps.Map(this.$map[0], this.defaultArgs);

            (function (self) {
                self.$markers.each(function () {
                    self.addMarker($(this));
                });
            }(this));

            this.filterMarkers();
        }

        /**
         * Add a marker to the map
         * @param jQueryNode $marker The HTML element storing the marker data
         */
        this.addMarker = function ($marker) {
            var marker = new google.maps.Marker({
                position: new google.maps.LatLng($marker.data("lat"), $marker.data("lng")),
                map:      this.map,
                filter:   $marker.data("filter"),
                icon:     {url: $marker.data("icon")}
            });
            this.markers.push(marker);

            if ($marker.html()) {
                var infowindow = new google.maps.InfoWindow({
                    content: $marker.html()
                });

                (function(self){
                    google.maps.event.addListener(marker, "click", function() {
                        if (self.infowindow) self.infowindow.close();
                        self.infowindow = infowindow;
                        self.infowindow.open( self.map, marker );
                    });
                }(this));
            }
        }

        /**
         * Dislpay only markers matching the filter
         */
        this.filterMarkers = function () {
            this.filters = [];
            if (this.infowindow) this.infowindow.close();

            (function (self) {
                self.$filters.each(function () {
                    if ($(this).hasClass("selected")) self.filters.push($(this).data("filter"));
                });

                $.each(self.markers, function(i, marker) {
                    if (self.filters.indexOf(marker.filter) >= 0) {
                        marker.setVisible(true);
                    } else {
                        marker.setVisible(false);
                    }
                });
            }(this));

            this.center();
        }

        /**
         * Center the map to display only visible markers
         */
        this.center = function () {
            var bounds = new google.maps.LatLngBounds(),
                visible_markers = [];

            $.each(this.markers, function(i, marker) {
                if (marker.visible) {
                    bounds.extend(new google.maps.LatLng(marker.position.lat(), marker.position.lng()));
                    visible_markers.push(marker);
                }
            });

            if (visible_markers.length == 0) {
                this.map.setCenter(this.defaultArgs.center);
                this.map.setZoom(this.defaultArgs.zoom);
            }
            else if (visible_markers.length == 1) {
                this.map.setCenter(bounds.getCenter());
                this.map.setZoom(10);
            } else {
                this.map.fitBounds(bounds);
            }
        }
    }

})(jQuery);