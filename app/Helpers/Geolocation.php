<?php

namespace Syltaen;

abstract class Geolocation
{

    /**
     * Radius of the earth in meters
     */
    const EARTH_RADIUS = 6371000;

    // ==================================================
    // > DISTANCES
    // ==================================================
    /**
     * Calculates the great-circle distance between two points, with
     * the Haversine formula.
     *
     * @param array $point_a
     * @param array $point_b
     * @return int Number of meters between the two points
     */
    public static function getHaversineDistance($point_a, $point_b)
    {
        // Convert from degrees to radians
        $point_a = array_map("deg2rad", $point_a);
        $point_b = array_map("deg2rad", $point_b);

        // Get deltas
        $lat_delta = $point_b["lat"] - $point_a["lat"];
        $lng_delta = $point_a["lng"] - $point_b["lng"];

        // Get the angle between the two points
        $angle = 2 * asin(sqrt(pow(sin($lat_delta / 2), 2) + cos($point_a["lat"]) * cos($point_b["lat"]) * pow(sin($lng_delta / 2), 2)));

        // Multiply with earth's radius (meters)
        return $angle * static::EARTH_RADIUS;
    }


    // ==================================================
    // > COORDONATES
    // ==================================================
    /**
     * Get both a name and coord by providing one information or the other
     *
     * @param string $location
     * @param string $coord
     * @return array
     */
    public static function get($name = false, $coord = false)
    {
        if (empty($name) && empty($coord)) return false;

        // Parse the coord if provided
        if (!empty($coord) && preg_match("/(\-?[0-9]+\.[0-9]+), ?\-?([0-9]+\.[0-9]+)/", $coord, $parts)) {
            $coord = [
                "lat" => (float) $parts[1],
                "lng" => (float) $parts[2]
            ];
        }

        // If name but no coord : geocoding
        if (empty($coord)) {
            $results = static::geocode($name);

            if (empty($results)) return [
                "name" => $name,
                "error" => "Ce lieu est inconnu.<br>Merci de vérifier votre recherche."
            ];

            $coord = [
                "lat" => $results[0]->location->y,
                "lng" => $results[0]->location->x
            ];
        }

        // If coord but no name : reverse geocoding
        if (empty($name)) {
            $reverse = Geolocation::reverseGeocoding($coord);
            $name = !empty($reverse->address->City) ? $reverse->address->City : "Ma position";
        }

        // Should always have both at this point
        return [
            "name"  => $name,
            "coord" => $coord
        ];
    }

    // ==================================================
    // > GEOCODING
    // ==================================================
    /**
     * Geocode search terms
     *
     * @param string $search
     * @return array of results
     */
    public static function geocode($search)
    {
        return json_decode((new Request("http://geocode.arcgis.com/arcgis/rest/services/World/GeocodeServer/findAddressCandidates?" . implode("&", [
            "f=json",
            "countryCode=BE",
            "langCode=FR",
            "SingleLine=$search"
        ])))->get()->responseBody)->candidates;
    }

    /**
     * Use the ESRI geocoder to get a list of propositions
     *
     * @param string $search
     * @param int $limit
     * @param boolean $extend
     * @return array
     */
    public static function getSuggestions($search, $limit = 10, $extent = false)
    {
        // Generate an endpoint
        $endpoint = "http://geocode.arcgis.com/arcgis/rest/services/World/GeocodeServer/suggest";
        $query    = [
            "f=json",
            "maxSuggestions=$limit",
            "countryCode=BE",
            "langCode=FR",
            "text=$search"
        ];
        if ($extent) $query[] = stripcslashes("searchExtent=$extent");
        $endpoint .= "?" . implode("&", $query);

        // Make the call
        return json_decode((new Request($endpoint))->get()->responseBody)->suggestions;
    }


    /**
     * Get an address details using its magic key
     *
     * @param string $magicKey
     * @return object
     */
    public static function getSuggestionInfo($magicKey)
    {
        $info = json_decode((new Request(
            "http://geocode.arcgis.com/arcgis/rest/services/World/GeocodeServer/findAddressCandidates?f=json&maxLocations=1&outFields=*&SingleLine=Li%C3%A8ge,%20BEL&outSR=%7b%22wkid%22:102100,%22latestWkid%22:3857%7d&magicKey=$magicKey"
        ))->get()->responseBody);

        if (empty($info->candidates[0])) return false;
        return $info->candidates[0];
    }


    /**
     * Reverse coordonates into an address name
     *
     * @param array $coord
     * @return array
     */
    public static function reverseGeocoding($coord)
    {
        return json_decode((new Request(
            "http://geocode.arcgis.com/arcgis/rest/services/World/GeocodeServer/reverseGeocode?location=$coord[lng],$coord[lat]&f=json&langCode=FR"
        ))->get()->responseBody);
    }
}