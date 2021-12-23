<?php

namespace Syltaen;

class Lang
{
    /**
     * The default language to use when it can't be determined
     */
    const DEFAULT_LANG = "fr_be";

    /**
     * Hold a reference to the previous language when switching locale
     *
     * @var string
     */
    public static $previous_lang = false;

    /**
     * Get the list of langs
     *
     * @return array
     */
    public static function getList($args = [])
    {
        if (function_exists("pll_languages_list")) {
            return pll_languages_list($args);
        }

        return [static::getDefault()];
    }

    /**
     * Get the list of langs as key=>value
     *
     * @return array
     */
    public static function getListOptions()
    {
        $options = [];
        foreach (Lang::getList() as $lang) {
            $options[$lang] = strtoupper($lang);
        }

        return $options;
    }

    /**
     * Get the current lang
     *
     * @return string
     */
    public static function getCurrent()
    {
        if (function_exists("pll_current_language")) {
            return pll_current_language();
        }

        return static::getDefault();
    }

    /**
     * If the website is translated, add a suffix to a given slug.
     * Used for post names, term slugs, option pages...
     *
     * @param string $slug
     */
    public static function suffixed($slug, $lang = false)
    {
        $lang = $lang ?: (function_exists("pll_current_language") ? pll_current_language() : false);
        return $lang ? "$slug-$lang" : $slug;
    }

    /**
     * Get the current lang
     *
     * @return string
     */
    public static function getCurrentFlag()
    {
        return [
            "fr_be" => "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAALCAMAAABBPP0LAAAAbFBMVEUAAADx7wD76QD29QryAgLrAAB5eXn8/G78+WH5U1NjY2NYWFj29k39/Uv6+kH29T31MDD0Jyf2QUFQUFD5+TT08TP1GxvmAAD39idwcHFHR0f18h3zDw8/Pz/HAAA2NjbV3wDi5ADg0gCmAAD9Uqo6AAAAX0lEQVR4AS3HtWFDURQD0KP7IMwLZP/Jfu/GzBYrICGnQmIiiGDf6ZG4R1ZFPBNzyvBbPL6WgfL37KmZehvmUfH4mGiO781J13S2b0VTt7+wRLzG80bF3ImPgH8ke4wzt8AM9KqOK78AAAAASUVORK5CYII=",
            "nl_be" => "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAALCAMAAABBPP0LAAAAbFBMVEUAAADx7wD76QD29QryAgLrAAB5eXn8/G78+WH5U1NjY2NYWFj29k39/Uv6+kH29T31MDD0Jyf2QUFQUFD5+TT08TP1GxvmAAD39idwcHFHR0f18h3zDw8/Pz/HAAA2NjbV3wDi5ADg0gCmAAD9Uqo6AAAAX0lEQVR4AS3HtWFDURQD0KP7IMwLZP/Jfu/GzBYrICGnQmIiiGDf6ZG4R1ZFPBNzyvBbPL6WgfL37KmZehvmUfH4mGiO781J13S2b0VTt7+wRLzG80bF3ImPgH8ke4wzt8AM9KqOK78AAAAASUVORK5CYII=",
            "fr"    => "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAALCAMAAABBPP0LAAAAbFBMVEVzldTg4ODS0tLxDwDtAwDjAADD0uz39/fy8vL3k4nzgna4yOixwuXu7u7s6+zn5+fyd2rvcGPtZljYAABrjNCpvOHrWkxegsqfs93NAADpUUFRd8THAABBa7wnVbERRKa8vLyxsLCoqKigoKClCvcsAAAAXklEQVR4AS3JxUEAQQAEwZo13Mk/R9w5/7UERJCIGIgj5qfRJZEpPyNfCgJTjMR1eRRnJiExFJz5Mf1PokWr/UztIjRGQ3V486u0HO55m634U6dMcf0RNPfkVCTvKjO16xHA8miowAAAAABJRU5ErkJggg==",
            "nl"    => "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAALCAMAAABBPP0LAAAAY1BMVEX/AAD8AADzAADqAAD/fYz+dYT6aHr7XG36VGb6R1f4L0H/ECz+mKXw8fH9/f36+vr19vbW1tbS0tIAG493n89cjcZNgsA/eLkzcbUpaLAcYKwAAFEAA4ANU6UAADsAAHgAAGcSgiW+AAAAS0lEQVR4AQXBiQ3CQBAAMc/dConQf688SoIdBIIyQiR9R0tCuM2rNxHpMqsDIWiBEE4NgnBiHhVJcs48P0uSjXsPl4hLmH2EHzb4A7DPDGppZMSwAAAAAElFTkSuQmCC",
        ][static::getCurrent()];
    }

    /**
     * Get the default lang
     *
     * @return string
     */
    public static function getDefault()
    {
        if (function_exists("pll_default_language")) {
            return pll_default_language();
        }

        return static::DEFAULT_LANG;
    }

    /**
     * Switch to a specific locale temporally
     *
     * @param  string $lang
     * @return bool
     */
    public static function switchTo($lang)
    {
        static::$previous_lang = PLL()->curlang;

        // Set the language
        PLL()->curlang = PLL()->model->get_language($lang);
        PLL()->load_strings_translations();
        switch_to_locale(static::toLocale($lang));
        load_theme_textdomain("syltaen", Files::path("app/lang"));
    }

    /**
     * Restore the default locale
     *
     * @param  string $lang
     * @return bool
     */
    public static function switchBack()
    {
        if (empty(static::$previous_lang)) {return false;}

        PLL()->curlang = PLL()->model->get_language(static::$previous_lang);
        PLL()->load_strings_translations();
        restore_previous_locale();
        load_theme_textdomain("syltaen", Files::path("app/lang"));
    }

    /**
     * Convert a lang code into a locale code
     *
     * @param  string   $lang
     * @return string
     */
    public static function toLocale($lang_slug)
    {
        return static::getLangField($lang_slug, "locale");
    }

    /**
     * Get a specific field form a language based on its slug
     *
     * @param  string   $lang
     * @return string
     */
    public static function getLangField($lang_slug, $field)
    {
        foreach (static::getList(["fields" => false]) as $lang) {
            if ($lang->slug == $lang_slug) {
                return $lang->{$field};
            }
        }

        return false;
    }

    /**
     * Run some code with a specifc language
     *
     * @param  string   $lang
     * @param  callable $code
     * @return mixed
     */
    public static function switchFor($lang, $code)
    {
        // Set the language
        static::switchTo($lang);

        // Run the code and save result
        $result = $code();

        // Restore the language
        static::switchBack();

        // Return the result
        return $result;
    }

    /**
     * Check if the current lang is in the provided list
     * @param  $langs
     * @return bool
     */
    public static function is($langs)
    {
        return in_array(static::getCurrent(), (array) $langs);
    }

    // =============================================================================
    // > POSTS
    // =============================================================================
    /**
     * Get the language of a post
     *
     * @param  int      $post_id
     * @param  $field   (optional) either ‘name’ or ‘locale’ or ‘slug’, defaults to ‘slug’
     * @return string
     */
    public static function ofPost($post_id, $field = "slug")
    {
        if (function_exists("pll_get_post_language")) {
            return pll_get_post_language($post_id, $field);
        }

        return false;
    }

    // =============================================================================
    // > SQL
    // =============================================================================

    /**
     * Fetch the polylang join clause.
     *
     * @return string
     */
    public static function getSQLJoin()
    {
        return PLL()->model->post->join_clause();
    }

    /**
     * Fetch the polylang where clause.
     *
     * @return string
     */
    public static function getSQLWhere($lang)
    {
        return PLL()->model->post->where_clause($lang);
    }
}