<?php

namespace Syltaen;

class Time
{
    /**
     * Set a default timezone for the application
     *
     * @return void
     */
    public static function setDefaultTimezone()
    {
        date_default_timezone_set(App::config("timezone"));
    }


    /**
     * Normalize a value into a timestamp
     *
     * @param mixed $date A timestamp, a string, an array
     * @return void
     */
    public static function normalize($date)
    {
        if (!$date) return false;

        // A timestamp
        if (is_int($date) || (string) intval($date) == $date) return (int) $date;

        // An array of 'year', 'month', 'day'
        if (is_array($date)) return static::fromArray($date);

        // Convert string
        return (int) strtotime($date);
    }


    /**
     * Get a timestamp from a array-date : year, month, day, hour, minute, second
     *
     * @param array $array
     * @return void
     */
    public static function fromArray($array)
    {
        // Add defaults
        $array = array_merge([
            "year"   => date("Y"),
            "month"  => "01",
            "day"    => "01",
            "hour"   => "00",
            "minute" => "00",
            "second" => "00",
        ], $array);

        // Return timestamp of formed date
        return (int) strtotime(
            "$array[year]-$array[month]-$array[day] $array[hour]:$array[minute]:$array[second]"
        );
    }


    /**
     * Get the offset of the theme's timezone at a certain date
     */
    public static function getTimezoneOffset($date = "now")
    {
        $timezone = new \DateTimeZone(App::config("timezone"));
        return $timezone->getOffset(new \DateTime($date, $timezone));
    }


    /**
     * Normalize a date, offset it with the theme's timezone and transform it into a date string
     *
     * @param mixed $date
     * @return string
     */
    public static function normalizedOffsetedString($date)
    {
        if (!$date) return false;
        return date(DATE_ATOM, static::normalize($date) + static::getTimezoneOffset());
    }
}