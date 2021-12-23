<?php

namespace Syltaen;

/**
 * Get a config item
 *
 * @return mixed
 */
function config($key)
{
    return Cache::value("global_config", function () {
        return set(include Files::path("app/config/_config.php"));
    })->get($key);
}

// =============================================================================
// > COMMON CLASSES
// =============================================================================

/**
 * Shortcut to create an new set instance from an array
 *
 * @param  array $array
 * @return Set
 */
function set($array = [])
{
    return new Set($array);
}

/**
 * Shortcut to create an new recursive set instance from an array
 *
 * @param  array          $array
 * @return RecursiveSet
 */
function recusive_set($array = [])
{
    return new RecursiveSet($array);
}

/**
 * Create a new cache instance
 *
 * @return Cache
 */
function cache($key, $ttl = 60, $keep = 10, $format = "serialized")
{
    return new Cache($key, $ttl, $keep, $format);
}
