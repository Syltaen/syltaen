<?php

namespace Syltaen;

use AllowDynamicProperties;

#[AllowDynamicProperties]
abstract class Model implements \Iterator

{
    /**
     * The slug that define what this model is used for
     */
    const TYPE = "";

    /**
     * Query constants used by the model's methods
     */
    const QUERY_CLASS  = "WP_Query";
    const OBJECT_CLASS = "WP_Post";
    const ITEM_CLASS   = "\Syltaen\Post";
    const OBJECT_KEY   = "posts";
    const QUERY_IS     = "post__in";
    const QUERY_ISNT   = "post__not_in";
    const QUERY_LIMIT  = "posts_per_page";
    const QUERY_STATUS = "post_status";
    const QUERY_PAGE   = "paged";
    const QUERY_HOOK   = "posts_clauses_request";
    const META_TABLE   = "postmeta";
    const META_ID      = "meta_id";
    const META_OBJECT  = "post_id";
    const META_KEY     = "meta_key";
    const META_VALUE   = "meta_value";

    /**
     * Store the query and its arguments to be modified by the model
     *
     * @var array
     */
    public $filters = [];
    /**
     * @var mixed
     */
    public $cachedQuery = false;
    /**
     * @var mixed
     */
    public $cachedFilters = false;
    /**
     * @var mixed
     */
    public $cachedResults = false;
    /**
     * @var array
     */
    public $queryModifiers = [];
    /**
     * @var array
     */
    public $resultFilters = [];

    /**
     * Internal key used for any iteration on the model
     *
     * @var integer
     */
    private $iteration_key = 0;

    /**
     * List of fields definition with their default values and processing callbacks
     *
     * @var Set
     */
    public $fields = null;

    /**
     * @var Set
     */
    public $fieldsIndex = null;

    /**
     * @var bool|array
     */
    public $forceFetchFields = false;

    /**
     * List of global data that can be used everywhere in the model
     *
     * @var Set
     */
    public $globals = [];

    /**
     * @var Set
     */
    public $globalsIndex = [];

    /**
     * @var mixed
     */
    public $globalsOptionPage;

    // ==================================================
    // > MAGIC METHODS
    // ==================================================
    /**
     * Create the base query and pre-sort all needed fields
     * A new instance should define the fields of each items with addFields()
     */
    public function __construct()
    {
        $this->iteration_key = 0;
        $this->fields        = new Set();
        $this->fieldsIndex   = new Set();
        $this->globals       = new Set();
        $this->cachedResults = new Set();
        $this->clearFilters();
    }

    /**
     * Lazy load properties, running the query and the populaters
     * only when trying to access a property.
     * @param  string $property
     * @return mixed  The property value
     */
    public function __get($property)
    {
        // Check for globals in the model, fetch them and store them for following requests
        if (isset($this->globalsIndex[$property])) {
            $data = Data::getAdvanced(
                $this->globalsIndex[$property],
                $this->globals[$this->globalsIndex[$property]],
                $this->globalsOptionPage
            );
            $this->{$data["key"]} = $data["value"];
            return $this->{$data["key"]};
        }

        // There are no result for the query
        if ($this->count() <= 0) {
            $key_parts = Data::parseDataKey($this->fieldsIndex[$property] ?? false);
            return !empty($key_parts["filter"]) ? Data::filter(null, $key_parts["filter"]) : false;
        }

        // Get the results
        $items = $this->get();

        // Not a list of object (probaby IDs)
        if (!is_object($items[0])) {
            return null;
        }

        // The property is not present in the first result and not defined in the fields index
        if (!isset($items[0]->{$property}) && !isset($this->fieldsIndex[$property])) {
            trigger_error("\"$property\" was not found in \"" . static::class . "\"");
            return null;
        }

        // Only one post queried and found : return the value of the match
        // if ($items->count() == 1 && (empty($this->filters[static::QUERY_LIMIT]) || $this->filters[static::QUERY_LIMIT] != -1)) {
        //     return $items[0]->$property;
        // }

        // Several posts : return a set of all the property of all matches
        return $items->reduce(function ($set, $item) use ($property) {
            // Property is a model : merge into one model targeting all (models must be filtering by id)
            if ($item->$property instanceof Model) {
                // Start with first item
                if (!($set instanceof Model)) {
                    $set = $item->$property;
                }

                // Merge everything
                $set = $set->merge($item->$property);

                // Not a model, just add the value to the list
            } else {
                $set[$item->ID] = $item->$property;
            }

            return $set;
        });
    }

    /**
     * Defer unkown methods to all items, if they implement that method.
     *
     * @param  string $method
     * @param  array  $arguments
     * @return array  The result of each call in an associative array
     */
    public function __call($method, $args)
    {
        if (method_exists(static::ITEM_CLASS, $method)) {
            return call_user_func_array([$this->callEach(), $method], $args);
        }

        throw new \Exception("\"$method\" method is not implemented by \\" . static::class . " nor " . static::ITEM_CLASS . ".");
    }

    // public function __call($name, $arguments)
    // {
    //     wp_send_json(["call", $name]);
    // }
    // public static function __callStatic($name, $arguments)
    // {
    //     wp_send_json(["callStatic", $name]);
    // }

    // ==================================================
    // > ITERATOR INTERFACE
    // ==================================================
    /**
     * Start the iteration by getting results
     *
     * @return void
     */
    public function rewind(): void
    {
        $this->iteration_key = 0;
        $this->get();
    }

    /**
     * Get the current result of the iteration
     *
     * @return mixed
     */
    public function current(): mixed
    {
        return $this->cachedResults[$this->iteration_key];
    }

    /**
     * Get the current key
     *
     * @return int
     */
    public function key(): mixed
    {
        return $this->iteration_key;
    }

    /**
     * Increment the key
     *
     * @return void
     */
    public function next(): void
    {
        ++$this->iteration_key;
    }

    /**
     * Check if there are results for the current iteration
     *
     * @return bool
     */
    public function valid(): bool
    {
        return isset($this->cachedResults[$this->iteration_key]);
    }

    /**
     * Call a function on each items
     *
     * @return Set Result of each call
     */
    public function map($callable)
    {
        return $this->get()->map($callable);
    }

    /**
     * Reduce the total to a single value
     *
     * @return mixed The carried result
     */
    public function reduce($callable, $carry = [])
    {
        return $this->get()->reduce($callable, $carry);
    }

    /**
     * Retrieve a list of items based on a callback
     *
     * @return Set The filtered results
     */
    public function filter($callable)
    {
        return $this->get()->filter($callable);
    }

    /**
     * Return a CallableCollection of all the results
     *
     * @return CallableCollection
     */
    public function callEach()
    {
        return $this->get()->callEach();
    }

    // ==================================================
    // > QUERY MODIFIERS
    // ==================================================
    /**
     * Restrict to only specific posts
     *
     * @param  array|int $list
     * @param  string    $mode   Define what to do if there is already a list of ID to filter. Either replace, merge or intersect
     * @return self
     */
    public function is($list, $mode = "replace")
    {
        $ids = Data::filter((array) $list, "ids");
        $this->updateExistingFilter(static::QUERY_IS, $ids, $mode);

        if (empty($this->filters[static::QUERY_IS])) {
            $this->filters[static::QUERY_IS] = [0];
        }

        return $this;
    }

    /**
     * Exclude specific posts
     *
     * @param  array|int $list
     * @param  string    $mode   Define what to do if there is already a list of ID to filter. Either replace, merge or intersect
     * @return self
     */
    public function isnt($list, $mode = "replace")
    {
        $ids = Data::filter((array) $list, "ids");

        // Already have an "IS" filter : intersect with the new list
        if (!empty($this->filters[static::QUERY_IS])) {
            $this->filters[static::QUERY_IS] = array_diff($this->filters[static::QUERY_IS], $ids);
        }

        return $this->updateExistingFilter(static::QUERY_ISNT, $ids, $mode);
    }

    /**
     * Force no results
     *
     * @return self
     */
    public function none()
    {
        $this->is(-1);
        return $this;
    }

    /**
     * Execute the is method, only if ids are specified
     *
     * @param  array|int $list
     * @param  string    $mode   Define what to do if there is already a list of ID to filter. Either replace, merge or intersect
     * @return self
     */
    public function isMaybe($list, $mode = "replace")
    {
        $list = array_diff($list ?: [], $this->filters[static::QUERY_ISNT] ?? []);

        if (!$list || empty($list)) {
            return $this;
        }

        return $this->is($list);
    }

    /**
     * Merge this model with another
     * Only works with IDs filtering
     * @param  Syltaen\Model $model
     * @return self
     */
    public function merge($model)
    {
        $merge = (clone $this)->applyFilters();
        $merge->is((clone $model)->getIDs(), "merge");
        return $merge;
    }

    /**
     * Execute the isnt method, only if ids are specified
     *
     * @param  array|int $list
     * @return self
     */
    public function isntMaybe($list)
    {
        if (!$list || empty($list)) {
            return $this;
        }

        return static::isnt($list);
    }

    /**
     * Update the status filter.
     * See https://codex.wordpress.org/Class_Reference/WP_Query#Status_Parameters
     * @param  array|string $status : ["publish", "pending", "draft", "future", "private", "trash", "any"]
     * @return self
     */
    public function status($status = false)
    {
        if ($status) {
            $this->filters[static::QUERY_STATUS] = $status;
        }
        return $this;
    }

    /**
     * Filter by lang
     *
     * @param  string Lang slug
     * @return self
     */
    public function lang($lang)
    {
        $this->filters["lang"] = $lang;
        return $this;
    }

    /**
     * Limit the number of posts returned.
     * See https://codex.wordpress.org/Class_Reference/WP_Query#Pagination_Parameters
     * @param  int    $limit
     * @return self
     */
    public function limit($limit = false, $filter_key = "posts_per_page")
    {
        if ($limit) {
            unset($this->filters["nopaging"]);
            $this->filters[static::QUERY_LIMIT] = $limit;
        }
        return $this;
    }

    /**
     * Offset the results to a certain page.
     * See https://codex.wordpress.org/Class_Reference/WP_User_Query#Pagination_Parameters
     * @param  int    $page
     * @return self
     */
    public function page($page = false)
    {
        if ($page) {
            $this->filters[static::QUERY_PAGE] = $page;
        }
        return $this;
    }

    /**
     * Basic search, should be updated by the children classes
     *
     * @param  string $terms
     * @param  $a     To       keep the same number of arguments
     * @param  $b     To       keep the same number of arguments
     * @return self
     */
    public function search($terms, $a = false, $b = false)
    {
        $this->filters["search"] = $terms;
        return $this;
    }

    /**
     * Filter on the publication date of a post
     *
     * @param  mixed  $after  Date to retrieve posts before. Accepts strtotime()-compatible string, or array of 'year', 'month', 'day'
     * @param  mixed  $before Same as $after
     * @return self
     */
    public function date($after = false, $before = false)
    {
        $this->filters["date_query"] = [
            [
                "after"     => $after,
                "before"    => $before,
                "inclusive" => true,
            ],
        ];
        return $this;
    }

    /**
     * Change the post order.
     * See https://codex.wordpress.org/Class_Reference/WP_Query#Order_.26_Orderby_Parameters
     * Must include a meta query beforehand specifying criteras for that key.
     * @param  string $orderby  the field to order the posts by
     * @param  int    $order    ASC or DESC
     * @param  string $meta_key When $orderby is "meta_value", specify the meta_key.
     * @return self
     */
    public function order($orderby = false, $order = "ASC")
    {
        $orderby = is_array($orderby) ? $orderby : explode(":", $orderby);

        $this->filters["orderby"] = $orderby[0];
        $this->filters["order"]   = $order;

        if ($orderby[0] == "meta_value" || $orderby[0] == "meta_value_num") {
            $this->filters["meta_key"] = implode(":", array_slice($orderby, 1));
        }

        return $this;
    }

    /**
     * Update the meta filter
     * See https://codex.wordpress.org/Class_Reference/WP_Query#Custom_Field_Parameters
     *
     * @param  string       $key      Custom field key.
     * @param  string|array $value    Custom field value. You don't have to specify a value when using the 'EXISTS' or 'NOT EXISTS' It can be an array only when compare is 'IN', 'NOT IN', 'BETWEEN', or 'NOT BETWEEN'.
     * @param  string       $compare  Operator to test. Possible values are : '=', '!=', '>', '>=', '<', '<=', 'LIKE', 'NOT LIKE', 'IN', 'NOT IN', 'BETWEEN', 'NOT BETWEEN', 'EXISTS' and 'NOT EXISTS'.
     * @param  string       $type     Custom field type. Possible values are : 'NUMERIC', 'BINARY', 'CHAR', 'DATE', 'DATETIME', 'DECIMAL', 'SIGNED', 'TIME', 'UNSIGNED'. Default value is 'CHAR'.
     * @param  string       $relation Erase the current relation between each meta_query. Either "OR", "AND" (default) or false to keep the current one.
     * @return self
     */
    public function meta($key, $value = null, $compare = "=", $type = null, $relation = false)
    {
        // Update the relation if specified
        $this->setMetaRelation($relation);

        // Key is a filter array, add as-is
        if (is_array($key)) {
            $filter                        = $key;
            $this->filters["meta_query"][] = $filter;
            return $this;
        }

        // Add the filter
        $filter = [
            "key"     => $key,
            "value"   => $value,
            "compare" => $compare,
            "type"    => $type,
        ];

        if (is_null($value)) {unset($filter["value"]);}
        if (is_null($type)) {unset($filter["type"]);}
        if ($compare == "IN" && empty((array) $value)) {
            $filter["value"] = [-1];
        }

        $this->filters["meta_query"][] = $filter;

        return $this;
    }

    /**
     * Set the relation between each meta query
     *
     * @param  string $relation AND|OR
     * @return self
     */
    public function setMetaRelation($relation)
    {
        // Create the meta_query if it doesn't exist
        $this->filters["meta_query"] ??= ["relation" => "AND"];

        // Update the relation if specified
        if ($relation) {
            $this->filters["meta_query"]["relation"] = $relation;
        }

        return $this;
    }

    /**
     * Filter items that have a specific meta defined
     *
     * @param  array  $keys     List of keys that should exist
     * @param  string $relation AND|OR - Specify if all keys should exist or at least one
     * @return self
     */
    public function withMeta($keys, $relation = "AND")
    {
        $filter = array_map(function ($key) {
            return [
                "relation" => "AND",
                [
                    "key"     => $key,
                    "compare" => "EXISTS",
                ],
                [
                    "key"     => $key,
                    "compare" => "!=",
                    "value"   => "",
                ],
                [
                    "key"     => $key,
                    "compare" => "!=",
                    "value"   => null,
                ],
            ];
        }, (array) $keys);

        $filter["relation"] = $relation;

        return $this->meta($filter);
    }

    /**
     * Filter items that don't have specific meta defined/set
     *
     * @param  array  $keys     List of keys that should exist
     * @param  string $relation AND|OR - Specify if all keys should exist or at least one
     * @return self
     */
    public function withoutMeta($keys, $relation = "AND")
    {
        $filter = array_map(function ($key) {
            return [
                "relation" => "OR",
                [
                    "key"     => $key,
                    "compare" => "NOT EXISTS",
                ],
                [
                    "key"     => $key,
                    "compare" => "=",
                    "value"   => "",
                ],
            ];
        }, (array) $keys);

        $filter["relation"] = $relation;

        return $this->meta($filter);
    }

    /**
     * Query by relation
     *
     * @param  string  $metakey The meta key
     * @param  int     $post_id The post ID to relate to
     * @param  boolean $loose   Include posts that have no relation defined
     * @return self
     */
    public function relatedTo($metakey, $post_id)
    {
        return $this->meta([
            "key"     => $metakey,
            "compare" => "LIKE",
            "value"   => "\"{$post_id}\"",
        ]);
    }

    /**
     * Run all the current filters and combine them into one using the results' IDs
     * Allow to add new filters that would have confilcted the previous ones
     * @return self
     */
    public function applyFilters()
    {
        $ids = $this->getIDs();
        $this->clearFilters();
        $this->is($ids);
        return $this;
    }

    /**
     * Clear one, several or all filters
     *
     * @param  array|string $filter_keys
     * @return self
     */
    public function clearFilters($filter_keys = false, $default_filters = [])
    {
        if (!$filter_keys) {
            $this->filters = $default_filters;
            return $this;
        }

        $filter_keys = is_array($filter_keys) ? $filter_keys : [$filter_keys];

        foreach ($filter_keys as $filter_key) {
            unset($this->filters[$filter_key]);
        }
        return $this;
    }

    /**
     * Update filters in the hard way
     *
     * @param  array  $filters
     * @return self
     */
    public function addFilters($filters)
    {
        $this->filters = array_merge($this->filters, $filters);
        return $this;
    }

    /**
     * Update a filter that maybe already existed
     *
     * @param  string       $filter_key
     * @param  array|string $new_value
     * @param  string       $mode         merge, intersect or replace
     * @return array
     */
    public function updateExistingFilter($filter_key, $new_value, $mode)
    {
        $previous_value = $this->filters[$filter_key] ?? [];

        switch ($mode) {
            case "merge":
                $value = array_unique(array_merge((array) $previous_value, (array) $new_value));
                break;
            case "intersect":
                $value = array_intersect((array) $previous_value, (array) $new_value);
                break;
            case "restrict":
                $value = $previous_value ? array_intersect((array) $previous_value, (array) $new_value) : (array) $new_value;
                break;
            case "replace":
            default:
                $value = $new_value;
                break;
        }

        $this->filters[$filter_key] = $value;

        return $this;
    }

    /**
     * Return a clone of the current model to avoid modifying the original
     *
     * @return self
     */
    public function clone ()
    {
        return clone $this;
    }

    // ==================================================
    // > SQL QUERY MODIFIER
    // ==================================================
    /**
     * Register a query modifiers to be applied only when this model Query runs
     *
     * @param  callable $function
     * @param  string   $hook
     * @return self
     */
    public function updateQuery($function, $group = "default")
    {
        $this->queryModifiers[$group][] = [
            "hook"     => static::QUERY_HOOK,
            "function" => $function,
        ];
        return $this;
    }

    /**
     * Add a global filter to execute each filter when the query runs
     *
     * @return self
     */
    public function applyQueryModifiers()
    {
        foreach ($this->queryModifiers as $group) {
            foreach ($group as $modifier) {
                add_filter($modifier["hook"], $modifier["function"]);
            }
        }

        return $this;
    }

    /**
     * Remove all global filter updating the SQL query
     *
     * @return void
     */
    public function unapplyQueryModifiers()
    {
        foreach ($this->queryModifiers as $group) {
            foreach ($group as $modifier) {
                remove_filter($modifier["hook"], $modifier["function"]);
            }
        }
    }

    /**
     * Clear all modifiers saved in this model
     *
     * @param  string|boolean $group
     * @return self
     */
    public function clearQueryModifiers($group = false)
    {
        if ($group) {
            $this->queryModifiers[$group] = [];
        } else {
            $this->queryModifiers = [];
        }
        return $this;
    }

    // ==================================================
    // > GETTERS
    // ==================================================
    /**
     * Execute the query and retrive all the found posts
     *
     * @param  int $limit Number of posts to return
     * @param  int $page  Page offset to use
     * @return Set of WP_Post
     */
    public function get($limit = false, $page = false)
    {
        $this->limit($limit)->page($page);

        // Nothing changed since last query run, return result
        if ($this->filters == $this->cachedFilters && $this->cachedResults->count()) {
            return $this->cachedResults;
        }

        // Executre query and parse results
        $this->cachedResults = $this->getResultsFromQuery(
            $this->run()->cachedQuery
        );

        return apply_filters("syltaen_get_" . static::TYPE, $this->cachedResults, $this);
    }

    /**
     * Only return the matching items' IDs
     *
     * @return Set
     */
    public function getIDs()
    {
        $this->filters["fields"] = "ids";
        return $this->get();
    }

    /**
     * Extracts results from the query
     *
     * @param  $query
     * @return array    of Item
     */
    protected function getResultsFromQuery($query)
    {
        $results = set(array_map(function ($item) {
            // Anything else than ITEM_CLASS : return as is
            if (!is_object($item) || get_class($item) !== static::OBJECT_CLASS) {
                return $item;
            }

            // Edge case for models that support joins
            if ($join_item = $this->parseJoinItem($item)) {
                return $join_item;
            }

            // Wrap objects in Item, return IDs as is
            $class = static::ITEM_CLASS;
            return new $class($item, $this);
        }, $query->{static::OBJECT_KEY} ?: []));

        foreach ($this->resultFilters as $callback) {
            $results = $results->filter($callback);
        }

        return $results;
    }

    /**
     * Add a callback to further filter the results after the query
     *
     * @param  callback $callback
     * @return self
     */
    protected function addResultFilter($callback)
    {
        $this->resultFilters[] = $callback;
        return $this;
    }

    /**
     * Allow chilidren to support joined model
     *
     * @return ModelItem of a different model
     */
    public function parseJoinItem($item)
    {
        return false;
    }

    /**
     * Get an object instance
     *
     * @return WP_...
     */
    public static function getObject($id)
    {
        $class = static::OBJECT_CLASS;
        return $class::get_instance($id);
    }

    /**
     * Get a dummy object instance
     *
     * @return object
     */
    public static function getDummyObject()
    {
        return (object) [
            "ID"                    => 0,
            "post_author"           => "",
            "post_date"             => "",
            "post_date_gmt"         => "",
            "post_content"          => "",
            "post_title"            => "<i>-</i>",
            "post_excerpt"          => "",
            "post_status"           => "nonexistant",
            "comment_status"        => "",
            "ping_status"           => "",
            "post_password"         => "",
            "post_name"             => "",
            "to_ping"               => "",
            "pinged"                => "",
            "post_modified"         => "",
            "post_modified_gmt"     => "",
            "post_content_filtered" => "",
            "post_parent"           => 0,
            "guid"                  => "",
            "menu_order"            => 0,
            "post_type"             => "",
            "post_mime_type"        => "",
            "comment_count"         => "",
            "filter"                => "",
        ];
    }

    /**
     * Get an item instance
     *
     * @return ModelItem|mixed
     */
    public static function getItem($id_or_object)
    {
        $class = static::ITEM_CLASS;
        return new $class($id_or_object, new static );
    }

    /**
     * Shortcut for is + get
     *
     * @return self
     */
    public static function getItems($ids)
    {
        return (new static )->is($ids)->get();
    }

    /**
     * Get an item instance, but only with an ID (prevent useless DB queries)
     *
     * @return ModelItem|mixed
     */
    public static function getLightItem($id)
    {
        $class = static::ITEM_CLASS;
        $item  = new $class($id);
        $item->setModel(new static );
        return $item;
    }

    /**
     * Get a dummy object instance
     *
     * @return object
     */
    public static function getDummyItem()
    {
        return static::getItem(0);
    }

    /**
     * Get several light item instances
     *
     * @return ModelItem
     */
    public static function getLightItems($ids)
    {
        return set(array_map(function ($id) {
            return static::getLightItem($id);
        }, $ids ?: []));
    }

    /**
     * Return only one result
     *
     * @return Item|bool
     */
    public function getOne()
    {
        $results = $this->get(1);
        return $this->found() ? $results[0] : false;
    }

    /**
     * Execute the query with the filters and store the result
     *
     * @return self
     */
    public function run()
    {
        if ($this->cachedQuery && $this->filters == $this->cachedFilters) {
            return $this;
        }

        $this->clearCache();

        $this->applyQueryModifiers();
        $class               = static::QUERY_CLASS;
        $this->cachedQuery   = new $class($this->filters);
        $this->cachedFilters = $this->filters;
        $this->unapplyQueryModifiers();

        return $this;
    }

    /**
     * Return the stored query
     *
     * @return WP_Query
     */
    public function getQuery()
    {
        $this->run();
        return $this->cachedQuery;
    }

    /**
     * Return the stored filters
     *
     * @return array of filters
     */
    public function getFilters()
    {
        return $this->filters;
    }

    /**
     * Return the number of posts matching the query
     *
     * @param  bool  $paginated Return the number of result on that page, or not
     * @return int
     */
    public function count($paginated = true)
    {
        if ($paginated) {
            return $this->getQuery()->post_count;
        } else {
            return intval($this->getQuery()->found_posts);
        }
    }

    /**
     * Check if only one result was found, can be used in _n functions
     *
     * @return int
     */
    public function singular()
    {
        return $this->count() <= 1 ? 1 : 0;
    }

    /**
     * Return the number of pages the query would return
     *
     * @return int
     */
    public function getPagesCount()
    {
        return $this->getQuery()->max_num_pages;
    }

    /**
     * Check if there are results
     *
     * @return boolean
     */
    public function found()
    {
        return $this->count() > 0;
    }

    /**
     * Check that the model's results contain a specific item
     *
     * @param  ModelItem $item
     * @return boolean
     */
    public function contains($item)
    {
        return !empty(array_intersect(
            Data::extractIds($item) ?: [],
            (array) (clone $this)->getIDs()
        ));
    }

    /**
     * Put all matching result in a clean array
     * Used for exporting data
     *
     * @param  callable $columns An associative array. $header=>$value
     * @return array
     */
    public function getAsTable($getColumnsData = false)
    {
        return $this->map($getColumnsData ?: function ($item) {
            return [
                "ID"     => $item->getID(),
                "Langue" => $item->getLang(),
                "Titre"  => $item->getTitle(),
                "slug"   => $item->getSlug(),
                "URL"    => $item->url,
            ];
        });
    }

    /**
     * Process the results per group, managing memoy more efficently
     *
     * @param  int      $groupSize
     * @param  callable $process_function
     * @return void
     */
    public function processInGroups($groupSize, $process_function)
    {
        $this
            ->applyFilters()->status("all")
            ->limit($groupSize);

        // Process one group at a time
        for ($page = 1; $page <= $this->getPagesCount(); $page++) {
            $this->page($page);
            $process_function($this);
        }
    }

    // ==================================================
    // > TRANSLATIONS
    // ==================================================
    /**
     * Parse a list of translations, retrieve an associative array of lang=>id each time
     *
     * @param  array   $items
     * @return array
     */
    public static function parseTranslationsList($translations)
    {
        $translations = (array) $translations;
        if (empty($translations)) {
            return false;
        }

        // No language information provided, try to guess from each posts
        if (is_int(array_keys($translations)[0])) {
            $translations = (array) set($translations)->mapAssoc(function ($i, $id) {
                return [static::getLightItem($id)->getLang(), $id];
            });
        }

        // ModelItems passed instead of IDs, convert them
        if (array_values($translations)[0] instanceof ModelItem) {
            $translations = array_map(function ($item) {
                return $item->getID();
            }, $translations);
        }

        // Need several posts to be linked
        if (count($translations) < 2) {
            return false;
        }

        return $translations;
    }

    /**
     * Create a translation for each matching element
     *
     * @param  string $lang
     * @return array  List of translations IDs
     */
    public function createTranslations($lang)
    {
        return $this->callEach()->createTranslation($lang);
    }

    // ==================================================
    // > FIELDS MANIPULATION
    // ==================================================
    /**
     * Add/Erase one or several fields to the model
     *
     * @param  array  $fields
     * @return self
     */
    public function addFields($fields)
    {
        $this->fields      = $this->fields->merge(Data::normalizeFieldsKeys($fields));
        $this->fieldsIndex = Data::generateFieldsIndex($this->fields);
        $this->clearCache();
        return $this;
    }

    /**
     * Add one or several global data to the model
     *
     * @param  array  $fields
     * @return self
     */
    public function addGlobals($fields)
    {
        $this->globals      = $this->globals->merge(Data::normalizeFieldsKeys($fields));
        $this->globalsIndex = Data::generateFieldsIndex($this->globals);
        return $this;
    }

    /**
     * Force the model to fetch and store the result of each field
     *
     * @param  mxied  $fields true/false to enable/disable all fields fetching, or a list of fields to fetch
     * @return self
     */
    public function fetchFields($fields = true)
    {
        // Force all fields fetching
        if ($fields === true) {
            $this->forceFetchFields = true;
            return $this;
        }
        // Disable all fields fetching
        if ($fields === false) {
            $this->forceFetchFields = false;
            return $this;
        }
        // Limit fetching to specific fields, add to the list
        $this->forceFetchFields = is_array($this->forceFetchFields) ? $this->forceFetchFields : [];
        $this->forceFetchFields = array_merge($this->forceFetchFields, (array) $fields);

        return $this;
    }

    // ==================================================
    // > CACHE
    // ==================================================
    /**
     * Clear all cached data, forcing new data fetching
     *
     * @return self
     */
    public function clearCache()
    {
        $this->cachedQuery   = false;
        $this->cachedFilters = false;
        $this->cachedResults = new Set;
        return $this;
    }

    // ==================================================
    // > DEBUG
    // ==================================================
    /**
     * Dump the result of a model with all its fields loaded
     *
     * @return void
     */
    public function json()
    {
        Log::json($this->fetchFields()->get());
    }

    /**
     * Dump the result of a model with all its fields loaded
     *
     * @return void
     */
    public function log()
    {
        Log::console($this->fetchFields()->get());
    }

    // ==================================================
    // > ACTIONS
    // ==================================================
    /**
     * Update all items matching the query
     *
     *
     * @param  array  $attrs     Base attributes
     * @param  array  $fields    ACF data
     * @param  array  $tax_roles Taxonomies or roles to set
     * @param  bool   $merge     Wether to merge or set the values
     * @return self
     */
    public function update($attrs = [], $fields = [], $tax_roles = [], $merge = false)
    {
        $this->callEach()->update($attrs, $fields, $tax_roles, $merge);

        // Force get refresh
        $this->clearCache();

        return $this;
    }

    /**
     * Update only the attrs of all matching item
     *
     * @param  array  $attrs Base attributes
     * @param  bool   $merge Wether to merge or set the values
     * @return self
     */
    public function setProperties($attrs, $merge = false)
    {
        return $this->update($attrs, false, false, $merge);
    }

    /**
     * Update only the fields of all matching item
     *
     * @param  array  $fields ACF data
     * @param  bool   $merge  Wether to merge or set the values
     * @return self
     */
    public function setFields($fields, $merge = false)
    {
        return $this->update(false, $fields, false, $merge);
    }

    /**
     * Update the postmeta directrly (does not create a duplicate metakey for ACF)
     *
     * @return self
     */
    public function setMetas($meta)
    {
        $this->get()->callEach()->setMetas($meta);

        // Force get refresh
        $this->clearCache();

        return $this;
    }

    /**
     * Update only the tax of all matching item
     *
     * @param  array  $tax_roles Taxonomies or roles to set
     * @param  bool   $merge     Wether to merge or set the values
     * @return self
     */
    public function setTaxonomies($tax, $merge = false)
    {
        return $this->update(false, false, $tax, $merge);
    }

    /**
     * Update the language of each matching item
     *
     * @param  string $lang The language's code
     * @return self
     */
    public function setLang($lang)
    {
        $this->lang(false)->callEach()->setLang($lang);

        // Force get refresh
        $this->clearCache();

        return $this;
    }

    /**
     * Delete all items matching the query
     *
     * @param  boolean $force Whether to bypass Trash and force deletion.
     * @return self
     */
    public function delete($force = false)
    {
        $this->callEach()->delete($force);

        // Force get refresh
        $this->clearCache();

        return $this;
    }

    // ==================================================
    // > MASS DATA MANIPULATION
    // ==================================================
    /**
     * Get all the IDs of this model's objects
     *
     * @return void
     */
    public static function getAllIDs()
    {
        // Should be implemented by children
    }

    /**
     * Get the meta values of all the posts for a specific key
     *
     * @return Set
     */
    public static function getMassMeta($meta_keys, $object_ids = false, $groupby_value = false, $index_key = "object_id", $value_key = "meta_value")
    {
        // Get the data of ALL the objects of this model
        if (!$object_ids) {$object_ids = (array) static::getAllIDs();}

        // Get all the metas
        $metas = Database::get_results(
            "SELECT "
            . static::META_ID . " meta_id, "
            . static::META_OBJECT . " object_id, "
            . static::META_KEY . " meta_key, "
            . static::META_VALUE . " meta_value "
            . "FROM " . static::META_TABLE . " "
            . "WHERE meta_key IN " . Database::inArray($meta_keys)
            . " AND " . static::META_OBJECT . " IN " . Database::inArray($object_ids)
        );

        // Return by grouped values
        if ($groupby_value) {
            return $metas->groupBy("meta_value", "object_id");
        }

        return $metas->index($index_key, $value_key);
    }

    /**
     * Get all the matches for specific(s) meta value(s)
     *
     * @param $meta_key
     * @param $meta_values
     */
    public static function getMatchingMeta($meta_key, $meta_values, $group_by_values = false)
    {
        // Restrict to only objects of this model
        $object_ids = static::getAllIDs();

        $results = Database::get_results(
            "SELECT "
            . static::META_OBJECT . ", "
            . static::META_VALUE . " "
            . "FROM " . static::META_TABLE . " meta "
            . "WHERE " . implode(" AND ", array_filter([
                static::META_KEY . " = '{$meta_key}'",
                static::META_VALUE . " IN " . Database::inArray((array) $meta_values),
                $object_ids?static::META_OBJECT . " IN " . Database::inArray($object_ids) : "",
            ]))
        );

        // Regroup each id by value
        if ($group_by_values) {
            return $results->groupBy(static::META_VALUE, static::META_OBJECT);
        }

        // Return only the matching ids
        return $results->column(static::META_OBJECT)->map("intval");
    }

    /**
     * Allow children to restrict the targets of mass-meta queries
     *
     * @return array
     */
    public static function getMetaObjectRestrinctions()
    {
        return [
            "JOIN"  => false,
            "WHERE" => [],
        ];
    }

    /**
     * Apply a serie of meta updates in an optimal way.
     *
     * @param  array|string $meta_keys   The meta key to use for each update
     * @param  array|mixed  $meta_values The meta value to use for each update
     * @param  array|int    $object_ids  The object ID to apply the update to. Leave to false to apply to every object.
     * @param  bool         $only_update Only update the existing data, do not create new metadata
     * @return void
     */
    public static function setMassMeta($meta_keys, $meta_values, $object_ids = "all", $only_update = false)
    {
        // Nothing to update
        if (empty($meta_values) && empty($object_ids)) {return;}
        // Apply updates to ALL the objects of this model
        if ($object_ids === "all") {$object_ids = (array) static::getAllIDs();}

        // Get a list of all existing meta, indexing the first match as object_id:meta_key
        $index = static::getMassMeta($meta_keys, $object_ids, false, "meta_id", false)->reverse()->index(function ($row) {
            return "{$row->object_id}:{$row->meta_key}";
        });

        // Uniformize into an array of update
        $max_updates = max(count((array) $meta_keys), count((array) $meta_values), count((array) $object_ids));
        $updates     = [];
        for ($i = 0; $i < $max_updates; $i++) {
            $update = [
                "object_id"  => ($object_id = is_array($object_ids) ? $object_ids[$i] : $object_ids),
                "meta_key"   => ($meta_key = is_array($meta_keys) ? $meta_keys[$i] : $meta_keys),
                "meta_value" => ($meta_value = is_array($meta_values) ? $meta_values[$i] : $meta_values),
                "meta_id"    => $index["{$object_id}:{$meta_key}"]->meta_id ?? "",
            ];
            // Do not register updates that have the same value as currently
            if (($index["{$object_id}:{$meta_key}"]->meta_value ?? null) === $meta_value) {continue;}
            // Do not register update that do not have a meta_id, if only_update is requested
            if ($only_update && !$update["meta_id"]) {continue;}
            // Add update to the lsit
            $updates[] = $update;
        }

        // No update
        if (empty($updates)) {return;}
        // Generate the SQL query to perform
        $query = "INSERT INTO " . static::META_TABLE . " (" . static::META_ID . ", " . static::META_OBJECT . ", " . static::META_KEY . ", " . static::META_VALUE . ") VALUES ";
        $query .= implode(",\n", array_map(function ($update) {
            return "(\"{$update['meta_id']}\",\"{$update['object_id']}\",\"{$update['meta_key']}\",\"{$update['meta_value']}\")";
        }, $updates));
        $query .= " ON DUPLICATE KEY UPDATE " . static::META_VALUE . "=VALUES(" . static::META_VALUE . ")";

        return Database::query($query);
    }

    /**
     * Keep only one occurance of a specific meta_key for each object.
     * Used only before setMassMeta() to avoid duplicates.
     *
     * @param  string $meta_key
     * @param  array  $object_ids
     * @return void
     */
    public static function removeDuplicateMeta($meta_key, $object_ids)
    {
        // Nothing to update
        if (empty($object_ids)) {return;}

        // Get a list of all existing meta and keep only duplicates meta_ids
        $duplicates = static::getMassMeta($meta_key, $object_ids, false, "meta_id", false)->groupBy("object_id", "meta_id")->map(function ($meta_ids) {
            array_shift($meta_ids);
            return $meta_ids;
        })->merge();

        if ($duplicates->empty()) {
            return false;
        }

        // Remove all the duplicates
        Database::get_results("DELETE FROM postmeta WHERE meta_id IN " . Database::inArray($duplicates));
    }
}
