<?php

namespace Syltaen;

class Time
{
    // =============================================================================
    // > FUTURE OR RECURRENT EVENTS
    // =============================================================================
    /**
     * Plan an event to occure once
     *
     * @return void
     */
    public static function planEvent($hook, $time = "+ 5 minutes", $args = [])
    {
        if (!wp_next_scheduled($hook, $args)) {
            wp_schedule_single_event(strtotime($time), $hook, $args);
        }
    }

    /**
     * Add a cron task if it does not already exists
     */
    public static function addCron($hook, $recurrence, $start = false)
    {
        $start = $start ?: Time::current();
        $start = is_int($start) ? $start : Time::fromString($start);

        if (!wp_next_scheduled($hook)) {
            wp_schedule_event($start, $recurrence, $hook);
        }
    }

    // =============================================================================
    // > DATETIME TOOLS
    // =============================================================================
    /**
     * Get the current time
     *
     * @return int|string
     */
    public static function current($format = "timestamp")
    {
        return current_time($format);
    }

    /**
     * Strtotime, but with the right timezone
     *
     * @param  string $string
     * @return int
     */
    public static function fromString($string, $format = "U")
    {
        return (new \DateTime($string, wp_timezone()))->format($format);
    }

    /**
     * Get a timestamp from a array-date : year, month, day, hour, minute, second
     *
     * @param  array  $array
     * @return void
     */
    public static function fromArray($array, $format = "U")
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
        return static::fromString(
            "$array[year]-$array[month]-$array[day] $array[hour]:$array[minute]:$array[second]",
            $format
        );
    }

    /**
     * Format a number of seconds to a readable text
     *
     * @param  int      $span
     * @return string
     */
    public static function spanToText($span)
    {
        $days  = "<strong>" . floor($span / DAY_IN_SECONDS) . "</strong>";
        $hours = "<strong>" . floor($span % DAY_IN_SECONDS / HOUR_IN_SECONDS) . "</strong>";
        $min   = "<strong>" . floor($span % HOUR_IN_SECONDS / MINUTE_IN_SECONDS) . "</strong>";

        return sprintf(__("%s jours %s h %s min", "syltaen"), $days, $hours, $min);
    }

    /**
     * Set a default timezone for the application
     *
     * @return void
     */
    public static function setDefaultTimezone()
    {
        date_default_timezone_set(config("timezone"));
    }

    /**
     * Normalize a value into a timestamp
     *
     * @param  mixed $date A timestamp, a string, an array
     * @return int
     */
    public static function normalize($date)
    {
        if (!$date) {
            return false;
        }

        // A timestamp
        if (is_int($date) || (string) intval($date) == $date) {
            return (int) $date;
        }

        // An array of 'year', 'month', 'day'
        if (is_array($date)) {
            return static::fromArray($date);
        }

        // Convert string
        return (int) strtotime($date);
    }

    /**
     * Return the human time diff from a date to another one
     *
     * @param  mixed    $to
     * @param  mixed    $from
     * @return string
     */
    public static function diff($from, $to = false)
    {
        return human_time_diff(Time::normalize($from), $to ?: time());
    }

    /**
     * Get the offset of the theme's timezone at a certain date
     */
    public static function getTimezoneOffset($date = "now")
    {
        $timezone = new \DateTimeZone(config("timezone"));
        return $timezone->getOffset(new \DateTime($date, $timezone));
    }

    /**
     * Normalize a date, offset it with the theme's timezone and transform it into a date string
     *
     * @param  mixed    $date
     * @return string
     */
    public static function normalizedOffsetedString($date)
    {
        if (!$date) {
            return false;
        }

        return date(DATE_ATOM, static::normalize($date)+static::getTimezoneOffset());
    }

    /**
     * Check that a date is today
     *
     * @return boolean
     */
    public static function isToday($date)
    {
        return date("Y-m-d", strtotime($date)) == Time::current("Y-m-d");
    }

    /**
     * Check that a date is yesterday
     *
     * @return boolean
     */
    public static function isYesterday($date)
    {
        return date("Y-m-d", strtotime($date)) == Time::fromString("yesterday", "Y-m-d");
    }

    /**
     * Change the format of a string
     *
     * @return void
     */
    public static function format($time, $format)
    {
        $time = is_int($time) ? $time : strtotime($time);
        return date($format, $time);
    }
}