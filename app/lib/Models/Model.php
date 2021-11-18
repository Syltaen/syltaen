<?php

namespace Syltaen;

abstract class Model implements \Iterator
{
    /**
     * The slug that define what this model is used for
     */
    const TYPE = "";

    /**
     * Query arguments used by the model's methods
     */
    const QUERY_CLASS  = "WP_Query";
    const OBJECT_CLASS = "WP_Post";
    const OBJECT_KEY   = "posts";
    const ITEM_CLASS   = "ModelItemPost";
    const QUERY_IS     = "post__in";
    const QUERY_ISNT   = "post__not_in";
    const QUERY_LIMIT  = "posts_per_page";
    const QUERY_STATUS = "post_status";
    const QUERY_PAGE   = "paged";

    /**
     * Store the query and its arguments to be modified by the model
     *
     * @var array
     */
    public $filters        = [];
    public $cachedQuery    = false;
    public $cachedFilters  = false;
    public $cachedResults  = false;
    public $queryModifiers = [];

    /**
     * Internal key used for any iteration on the model
     *
     * @var integer
     */
    private $iteration_key = 0;

    /**
     * List of fields definition with their default values and processing callbacks
     *
     * @var array
     */
    public $fields = [];
    public $fieldsIndex = [];
    public $forceFetchFields = false;


    /**
     * List of global data that can be used everywhere in the model
     *
     * @var array
     */
    public $globals = [];
    public $globalsIndex = [];
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
        $this->clearFilters();
    }

    /**
     * Lazy load properties, running the query and the populaters
     * only when trying to access a property.
     * @param string $property
     * @return mixed The property value
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
        if ($this->count() <= 0) return null;

        // Get the results
        $items = $this->get();

        // The property is not present in the first result and not defined in the fields index
        if (!isset($items[0]->{$property}) && !isset($this->fieldsIndex[$property])) {
            trigger_error("\"$property\" was not found in \"".static::class."\"");
            return null;
        }

        // Only one post queried and found : return the value of the match
        if ($this->count() == 1 && (empty($this->filters[static::QUERY_LIMIT]) || $this->filters[static::QUERY_LIMIT] != -1)) return $items[0]->$property;

        // Several posts : return the value of all matches
        return array_reduce($items, function ($list, $item) use ($property) {

            // Property is a model : merge into one model targeting all (models must be filtering by id)
            if ($item->$property instanceof Model) {
                // Start with first item
                if (!($list instanceof Model)) $list = $item->$property;
                // Merge everything
                $list->merge($item->$property);

            // Not a model, just add the value to the list
            } else {
                $list[$item->ID] = $item->$property;
            }

            return $list;
        }, []);
    }


    // ==================================================
    // > ITERATOR INTERFACE
    // ==================================================
    /**
     * Start the iteration by getting results
     *
     * @return void
     */
    public function rewind()
    {
        $this->iteration_key = 0;
        $this->get();
    }

    /**
     * Get the current result of the iteration
     *
     * @return mixed
     */
    public function current()
    {
        return $this->cachedResults[$this->iteration_key];
    }

    /**
     * Get the current key
     *
     * @return int
     */
    public function key()
    {
        return $this->iteration_key;
    }

    /**
     * Increment the key
     *
     * @return void
     */
    public function next()
    {
        ++$this->iteration_key;
    }

    /**
     * Check if there are results for the current iteration
     *
     * @return bool
     */
    public function valid()
    {
        return isset($this->cachedResults[$this->iteration_key]);
    }


    /**
     * Call a function on each items
     *
     * @return array Result of each call
     */
    public function map($callable)
    {
        return array_map($callable, $this->get());
    }


    /**
     * Reduce the total to a single value
     *
     * @return mixed The carried result
     */
    public function reduce($callable, $carry)
    {
        return array_reduce($this->get(), $callable, $carry);
    }


    /**
     * Retrieve a list of items based on a callback
     *
     * @return array The filtered results
     */
    public function filter($callable)
    {
        return array_filter($this->get(), $callable);
    }



    // ==================================================
    // > QUERY MODIFIERS
    // ==================================================
    /**
     * Restrict to only specific posts
     *
     * @param array|int $list
     * @param string $mode Define what to do if there is already a list of ID to filter. Either replace, merge or intersect
     * @return self
     */
    public function is($list, $mode = "replace")
    {
        $ids = Data::filter($list, "ids");

        if (empty($ids)) {
            $this->filters[static::QUERY_IS] = [0];
            return $this;
        }

        return $this->updateExistingFilter(static::QUERY_IS, $ids, $mode);
    }


    /**
     * Exclude specific posts
     *
     * @param array|int $list
     * @param string $mode Define what to do if there is already a list of ID to filter. Either replace, merge or intersect
     * @return void
     */
    public function isnt($list, $mode = "replace")
    {
        $ids = Data::filter($list, "ids");
        return $this->updateExistingFilter(static::QUERY_ISNT, $ids, $mode);
    }


    /**
     * Force no results
     *
     * @return void
     */
    public function none()
    {
        $this->is(-1);
        return $this;
    }

    /**
     * Execute the is method, only if ids are specified
     *
     * @param array|int $list
     * @return self
     */
    public function isMaybe($list)
    {
        if (!$list || empty($list)) return $this;
        return static::is($list);
    }


    /**
     * Merge this model with another
     * Only works with IDs filtering
     * @param Syltaen\Model $model
     * @return self
     */
    public function merge($model)
    {
        if (isset($model->filters[static::QUERY_IS])) {
            $this->is($model->filters[static::QUERY_IS], "merge");
        }
        return $this;
    }


    /**
     * Execute the isnt method, only if ids are specified
     *
     * @param array|int $list
     * @return self
     */
    public function isntMaybe($list)
    {
        if (!$list || empty($list)) return $this;
        return static::isnt($list);
    }



    /**
     * Update the status filter.
     * See https://codex.wordpress.org/Class_Reference/WP_Query#Status_Parameters
     * @param array|string $status : ["publish", "pending", "draft", "future", "private", "trash", "any"]
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
     * @param string Lang slug
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
     * @param int $limit
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
     * @param int $page
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
     * @param string $terms
     * @param $a To keep the same number of arguments
     * @param $b To keep the same number of arguments
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
     * @param mixed $after Date to retrieve posts before. Accepts strtotime()-compatible string, or array of 'year', 'month', 'day'
     * @param mixed $before Same as $after
     * @return self
     */
    public function date($after = false, $before = false)
    {
        $this->filters["date_query"] = [
            [
                "after"     => $after,
                "before"    => $before,
                "inclusive" => true
            ]
        ];
        return $this;
    }

    /**
     * Change the post order.
     * See https://codex.wordpress.org/Class_Reference/WP_Query#Order_.26_Orderby_Parameters
     * @param string $orderby the field to order the posts by
     * @param int $order ASC or DESC
     * @param string $meta_key When $orderby is "meta_value", specify the meta_key.
     * Must include a meta query beforehand specifying criteras for that key.
     * @return self
     */
    public function order($orderby = false, $order = "ASC", $meta_key = false)
    {
        $this->filters["orderby"] = $orderby;
        $this->filters["order"]   = $order;
        if ($orderby == "meta_value" || $orderby == "meta_value_num") {
            $this->filters["meta_key"] = $meta_key;
        }
        return $this;
    }


    /**
     * Update the meta filter
     * See https://codex.wordpress.org/Class_Reference/WP_Query#Custom_Field_Parameters
     * @param string $key Custom field key.
     * @param string|array $value Custom field value.
     *        It can be an array only when compare is 'IN', 'NOT IN', 'BETWEEN', or 'NOT BETWEEN'.
     *        You don't have to specify a value when using the 'EXISTS' or 'NOT EXISTS'
     * @param string $compare  Operator to test. Possible values are :
     *        '=', '!=', '>', '>=', '<', '<=', 'LIKE', 'NOT LIKE', 'IN',
     *        'NOT IN', 'BETWEEN', 'NOT BETWEEN', 'EXISTS' and 'NOT EXISTS'.
     * @param string $type Custom field type. Possible values are :
     *        'NUMERIC', 'BINARY', 'CHAR', 'DATE', 'DATETIME', 'DECIMAL', 'SIGNED',
     *        'TIME', 'UNSIGNED'. Default value is 'CHAR'.
     *        You can also specify precision and scale for the 'DECIMAL' and 'NUMERIC' types
     *        (for example, 'DECIMAL(10,5)' or 'NUMERIC(10)' are valid).
     * @param string $relation Erase the current relation between each meta_query.
     *        Either "OR", "AND" (default) or false to keep the current one.
     * @param boolean $replace Specify if the filter should replace any existing one on the same meta_key
     * @return void
     */
    public function meta($key, $value = null, $compare = "=", $type = null, $relation = false, $replace = false)
    {
        // Create the meta_query if it doesn't exist
        $this->filters["meta_query"] = isset($this->filters["meta_query"]) ? $this->filters["meta_query"] : [
            "relation" => "AND"
        ];

        // Update the relation if specified
        $this->filters["meta_query"]["relation"] = $relation ?: $this->filters["meta_query"]["relation"];

        // If $replace, remove all filters made on that specific meta_key
        if ($replace) {
            foreach ($this->filters["meta_query"] as $filter_key=>$filter) {
                if (isset($filter["key"]) && $filter["key"] == $key) {
                    unset($this->filters["meta_query"][$filter_key]);
                }
            }
        }

        // Add the filter
        $filter = [
            "key"     => $key,
            "value"   => $value,
            "compare" => $compare,
            "type"    => $type
        ];

        if (is_null($value)) {
            unset($filter["value"]);
        }

        if (is_null($type)) {
            unset($filter["type"]);
        }

        $this->filters["meta_query"][] = $filter;

        return $this;
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
     * @param array|string $filter_keys
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
     * @param array $filters
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
     * @param string $filter_key
     * @param array|string $new_value
     * @param string $mode merge, intersect or replace
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
            case "replace":
            default:
                $value = $new_value;
                break;
        }

        $this->filters[$filter_key] = $value;

        return $this;
    }


    // ==================================================
    // > SQL QUERY MODIFIER
    // ==================================================
    /**
     * Register a query modifiers to be applied only when this model WP_Query runs
     *
     * @param callable $function
     * @param string $hook
     * @return self
     */
    public function updateQuery($function, $group = "default", $hook = "posts_clauses_request")
    {
        $this->queryModifiers[$group][] = [
            "hook"     => $hook,
            "function" => $function
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
     * @param string|boolean $group
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
     * @param int $limit Number of posts to return
     * @param int $page Page offset to use
     * @return array of WP_Post
     */
    public function get($limit = false, $page = false)
    {
        $this->limit($limit)->page($page);

        // Nothing changed since last query run, return result
        if ($this->filters == $this->cachedFilters && !empty($this->cachedResults)) {
            return $this->cachedResults;
        }

        // Executre query and parse results
        $this->cachedResults = $this->getResultsFromQuery(
            $this->run()->cachedQuery
        );

        return apply_filters("syltaen_get_" . static::TYPE, $this->cachedResults);
    }

    /**
     * Only return the matching items' IDs
     *
     * @return array
     */
    public function getIDs()
    {
        $this->filters["fields"] = "ids";
        return $this->get();
    }

    /**
     * Extracts results from the query
     *
     * @param $query
     * @return array of ModelItem
     */
    protected function getResultsFromQuery($query)
    {
        return array_map(function ($item) {

            // Anything else than ITEM_CLASS : return as is
            if (!is_object($item) || get_class($item) !== static::OBJECT_CLASS) return $item;

            // Wrap objects in ModelItem, return IDs as is
            $class = "\\Syltaen\\" . static::ITEM_CLASS;
            return new $class($item, $this);
        }, $query->{static::OBJECT_KEY});
    }

    /**
     * Return only one result
     *
     * @return ModelItem|bool
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
        if ($this->cachedQuery && $this->filters == $this->cachedFilters) return $this;
        $this->clearCache();

        $this->applyQueryModifiers();

        $class = static::QUERY_CLASS;
        $this->cachedQuery = new $class($this->filters);
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
     * @param bool $paginated Return the number of result on that page, or not
     * @return int
     */
    public function count($paginated = true)
    {
        if ($paginated)
            return $this->getQuery()->post_count;
        else
            return intval($this->getQuery()->found_posts);
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
     * Put all matching result in a clean array
     * Used for exporting data
     *
     * @param callable $columns An associative array. $header=>$value
     * @return array
     */
    public function getAsTable($getColumnsData = false)
    {
        if (!is_callable($getColumnsData)) throw_error("\$getColumnsData must be a callable function");

        // Map rows
        $rows = $this->map(function ($result) use ($getColumnsData) {
            return $getColumnsData($result);
        });

        // Return header and rows
        return [
            "header" => array_keys($rows[0]),
            "rows"   => array_map("array_values", $rows)
        ];
    }


    /**
     * Process the results per group, managing memoy more efficently
     *
     * @param int $groupSize
     * @param callable $process_function
     * @return void
     */
    public function processInGroups($groupSize = 100, $process_function)
    {
        $this
            // Make sure we always target the inital posts
            ->applyFilters()->status("all")
            // Fetch X posts at a time
            ->limit($groupSize);

        // Process one group at a time
        for ($page = 1; $page <= $cluster->getPagesCount(); $page++) {
            $cluster->page($page);
            $process_function($cluster);
        }
    }



    // ==================================================
    // > FIELDS MANIPULATION
    // ==================================================
    /**
     * Add/Erase one or several fields to the model
     *
     * @param array $fields
     * @return self
     */
    public function addFields($fields)
    {
        $this->fields = array_merge($this->fields, Data::normalizeFieldsKeys((array) $fields));
        $this->fieldsIndex = Data::generateFieldsIndex($this->fields);
        $this->clearCache();
        return $this;
    }


    /**
     * Add one or several global data to the model
     *
     * @param array $fields
     * @return self
     */
    public function addGlobals($fields)
    {
        $this->globals = array_merge($this->globals, Data::normalizeFieldsKeys((array) $fields));
        $this->globalsIndex = Data::generateFieldsIndex($this->globals);
        return $this;
    }


    /**
     * Force the model to fetch and store the result of each field
     *
     * @return void
     */
    public function fetchFields()
    {
        $this->forceFetchFields = true;
        return $this;
    }

    // ==================================================
    // > CACHE
    // ==================================================
    /**
     * Clear all cached data, forcing new data fetching
     *
     * @return void
     */
    public function clearCache()
    {
        $this->cachedQuery   = false;
        $this->cachedFilters = false;
        $this->cachedResults = false;
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
        wp_send_json($this->fetchFields()->get());
    }


    /**
     * Dump the result of a model with all its fields loaded
     *
     * @return void
     */
    public function log()
    {
        Controller::log($this->fetchFields()->get());
    }

    // ==================================================
    // > ACTIONS
    // ==================================================
    /**
     * Update all items matching the query
     *
     *
     * @param array $attrs Base attributes
     * @param array $fields ACF data
     * @param array $tax_roles Taxonomies or roles to set
     * @param bool $merge Wether to merge or set the values
     * @return self
     */
    public function update($attrs = [], $fields = [], $tax_roles = [], $merge = false)
    {
        foreach ($this->get() as $item) {
            $item->update($attrs, $fields, $tax_roles, $merge);
        }

        // Force get refresh
        $this->clearCache();

        return $this;
    }

    /**
     * Update only the attrs of all matching item
     *
     * @param array $attrs Base attributes
     * @param bool $merge Wether to merge or set the values
     * @return self
     */
    public function updateAttrs($attrs, $merge = false)
    {
        return $this->update($attrs, false, false, $merge);
    }

    /**
     * Update only the fields of all matching item
     *
     * @param array $fields ACF data
     * @param bool $merge Wether to merge or set the values
     * @return self
     */
    public function updateFields($fields, $merge = false)
    {
        return $this->update(false, $fields, false, $merge);
    }

    /**
     * Update the postmeta directrly (does not create a duplicate metakey for ACF)
     *
     * @return void
     */
    public function updateMeta($meta)
    {
        foreach ($this->get() as $item) {
            $item->updateMeta($meta);
        }

        // Force get refresh
        $this->clearCache();

        return $this;
    }

    /**
     * Update only the tax of all matching item
     *
     * @param array $tax_roles Taxonomies or roles to set
     * @param bool $merge Wether to merge or set the values
     * @return self
     */
    public function updateTaxonomies($tax, $merge = false)
    {
        return $this->update(false, false, $tax, $merge);
    }

    /**
     * Update the language of each matching item
     *
     * @param string $lang The language's code
     * @return self
     */
    public function updateLang($lang)
    {
        foreach ($this->lang(false)->get() as $item) {
            $item->updateLang($lang);
        }

        // Force get refresh
        $this->clearCache();

        return $this;
    }


    /**
     * Delete all items matching the query
     *
     * @param boolean $force Whether to bypass Trash and force deletion.
     * @return void
     */
    public function delete($force = false)
    {
        foreach ($this->get() as $item) {
            $item->delete($force);

        }
        $this->clearCache();
    }



    /**
     * Update one meta field for several posts in one SQL query
     *
     * @param string $meta_key
     * @param array $posts_values ID=>value
     * @return void
     */
    public static function updateMetaMulti($meta_key, $posts_values)
    {
        if (empty($posts_values)) return false;

        // Generate an index of post_id=>meta_id to use for INSERT
        $meta_index = [];
        $meta = Database::get_results("SELECT meta_id, post_id FROM postmeta WHERE meta_key = '$meta_key' AND post_id IN (".implode(",", array_keys($posts_values)).")");
        foreach ((array) $meta as $m) $meta_index[$m->post_id] = $m->meta_id;

        // Generate the SQL query to perform
        $query  = "INSERT INTO postmeta (meta_id, post_id, meta_key, meta_value) VALUES ";
        $query .= implode(",", array_map(function ($id, $value) use ($meta_key, $meta_index) {
            return "(".($meta_index[$id] ?? "''").",$id,'$meta_key','$value')";
        }, array_keys($posts_values), array_values($posts_values)));
        $query .= " ON DUPLICATE KEY UPDATE meta_value=VALUES(meta_value)";
        return Database::query($query);
    }


    /**
     * Set one meta field & value for several posts in one SQL query
     *
     * @param string $meta_key
     * @param array $posts_values ID=>value
     * @return void
     */
    public static function setMetaMulti($meta_key, $meta_value, $post_ids = false)
    {
        return Database::query(
           "UPDATE postmeta
            SET meta_value = '$meta_value'
            WHERE meta_key = '$meta_key'
           " . (empty($post_ids) ? "" : " AND post_id IN (".implode(",", $post_ids).")")
        );
    }





}