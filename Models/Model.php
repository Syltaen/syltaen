<?php

namespace Syltaen;

abstract class Model implements \Iterator
{

    /**
     * Store the query and its arguments to be modified by the model
     *
     * @var boolean
     */
    protected $filters       = [];
    protected $cachedQuery   = false;
    protected $cachedFilters = false;
    protected $cachedResults = false;

    /**
     * Internal key used for any iteration on the model
     *
     * @var integer
     */
    private $iteration_key = 0;

    /**
     * List of fields used by the Data::store method
     *
     * @var array
     */
    protected $fields = [];

    /**
     * Specify that the populateCustomFields method should be run on each result
     *
     * @var boolean
     */
    protected $hasCustomFields = false;

    /**
     * A list of attributes to retrieve for each post.
     * If left empty, the whole post will be retuned.
     * Note : "ID" and "filter" are always kept for internal usage
     */
    protected $attrs = [];


    // ==================================================
    // > MAGIC METHODS
    // ==================================================
    /**
     * Create the base query and pre-sort all needed fields
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
        // There are no result for the query
        if ($this->count() <= 0) return null;

        // Get the results
        $posts = $this->get();

        // The  property does not exists
        if (!isset($posts[0]->$property)) {
            trigger_error(
                "<em>$property</em> was not found in <em>".static::class."</em>.<br>".
                "Be sure to add it as a field in the model.<br>"
            );
            return null;
        }

        // Return the property
        if ($this->count() > 1) {
            $list = [];

            foreach ($posts as $post) {

                if ($post->$property instanceof Model) {
                    if ($list instanceof Model) {
                        $list->merge($post->$property);
                    } else {
                        $list = $post->$property;
                    }
                } else {
                    if (!$list instanceof Model) {
                        $list[] = $post->$property;
                    }
                }
            }
            return $list;
        } else {
            return $posts[0]->$property;
        }


        return null;
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


    // ==================================================
    // > QUERY MODIFIERS
    // ==================================================
    /**
     * Restrict to only specific posts
     *
     * @param array|int $list
     * @param bool $add Define if the IDs should be added or replace the old ones
     * @return self
     */
    public function is($list, $add = false, $filter_key = "post__in")
    {
        $ids = Data::filter($list, "ids");

        if ($add) {
            if (!$ids) return $this;
            $this->filters[$filter_key] = array_merge($this->filters[$filter_key], $ids);
        } else {
            if (!$ids) {
                $this->filters[$filter_key] = [0];
            } else {
                $this->filters[$filter_key] = $ids;
            }
        }

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
        if (isset($model->filters["post__in"])) {
            $this->is($model->filters["post__in"], true);
        }
        return $this;
    }

    /**
     * Exclude specific posts
     *
     * @param array|int $list
     * @return void
     */
    public function isnt($list)
    {
        $this->filters["post__not_in"] = Data::filter($list, "ids");
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
     * Limit the number of posts returned.
     * See https://codex.wordpress.org/Class_Reference/WP_Query#Pagination_Parameters
     * @param int $limit
     * @return self
     */
    public function limit($limit = false, $filter_key = "posts_per_page")
    {
        if ($limit) {
            unset($this->filters["nopaging"]);
            $this->filters[$filter_key] = $limit;
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
            $this->filters["paged"] = $page;
        }
        return $this;
    }

    /**
     * Add search filter to the query.
     * See https://codex.wordpress.org/Class_Reference/WP_Query#Search_Parameter
     * @param string $terms
     * @param boolean $exclusive : Specify if the search is incluive (||) or exclusive (&&)
     * @return self
     */
    public function search($terms, $exclusive = false, $filter_key = "s")
    {
        // $local_query
        // "s" => "keyword",
        // "post_in"
        $this->filters[$filter_key] = $terms;

        return $this;
    }

    /**
     * Change the post order.
     * See https://codex.wordpress.org/Class_Reference/WP_Query#Order_.26_Orderby_Parameters
     * @param string $orderby the field to order the posts by
     * @param int $order ASC or DESC
     * @param string $meta_key When $orderby is "meta_value", specify the meta_key.
     * Must include a meta query beforehand specifying criteras for that key.
     * @return void
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
     *        Either "OR", "AND" (deflault) or false to keep the current one.
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
     * Update filters in the hard way
     *
     * @param array $filters
     * @return self
     */
    public function filters($filters)
    {
        $this->filters = array_merge($this->filters, $filters);
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

        // Only re-fetch posts if the query has been updated
        if ($this->filters !== $this->cachedFilters || !$this->cachedResults) {
            $this->cachedResults = static::getResultsFromQuery(
                $this
                    ->run()
                    ->populateData()
                    ->cachedQuery
            );
        }

        return $this->cachedResults;
    }

    /**
     * Only return the matching IDs without
     *
     * @return void
     */
    public function getIDs()
    {
        $this->filters["fields"] = "ids";
        $this->cachedResults = static::getResultsFromQuery(
            $this->run()
            ->cachedQuery
        );

        return $this->cachedResults;
    }

    /**
     * Extracts results from the query
     *
     * @param $query
     * @return array
     */
    protected static function getResultsFromQuery($query)
    {
        return $query->posts;
    }

    /**
     * Return only one result
     *
     * @return WP_User|bool
     */
    public function getOne()
    {
        $results = $this->get(1);
        return $this->found() ? $results[0] : false;
    }

    /**
     * Execute the query with the filters and store the result
     *
     * @param bool $force Force the query to run, overlooking the cached one
     * @return self
     */
    public function run($force = false)
    {
        if ($this->cachedQuery && $this->filters == $this->cachedFilters && !$force) return $this;

        $this->cachedQuery = new \WP_Query($this->filters);
        $this->cachedFilters = $this->filters;
        $this->cachedResults = false;

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

    public function getSingularQuery()
    {
        $query = $this->getQuery();

        $query->is_singular = true;
        $query->is_single = true;
        $query->is_home = false;
        $query->max_num_page = 0;

        return $query;
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
        if (!is_callable($getColumnsData)) wp_die("getColumnsData must be a callable function");

        // ========== ROWS ========== //
        $rows = [];
        foreach ($this->get() as $result) $rows[] = $getColumnsData($result);;

        // ========== HEADER ========== //
        $header = [];
        foreach ($rows[0] as $name=>$key) $header[] = $name;

        // ========== EXPORT ========== //
        return array_merge([$header], $rows);
    }

    // ==================================================
    // > DATA HANDLING FOR EACH RESULT
    // ==================================================
    /**
     * Add data to each result based on what the model supports
     *
     * @return self
     */
    public function populateData()
    {
        if (!isset($this->cachedQuery) || empty($this->cachedQuery)) die("The query need to be run before populating its results.");

        $results = static::getResultsFromQuery($this->cachedQuery);
        foreach ($results as $result) {
            $this->populateResultData($result);
        }

        return $this;
    }

    /**
     * Launch each populate method on a user
     *
     * @param object
     * @return void
     */
    public function populateResultData(&$result)
    {
        /* REMOVE ATTRIBUTES THAT SHOULD NOT BE RETRIEVED */
        if (!empty($this->attrs)) {
            $this->removeUnusedAttr($result);
        }

        /* ADD FIELDS IF ANY */
        if (!empty($this->fields)) {
            $this->populateFields($result);
        }

        if ($this->hasCustomFields) {
            $this->populateCustomFields($result);
        }
    }

    /**
     * Add all Custom Fields's values specified in the model's constructor to a user
     *
     * @param WP_Post $post
     * @return void
     */
    protected function populateFields(&$result, $fields_prefix = "")
    {
        Data::store($result, $this->fields, $fields_prefix.$result->ID);
    }

    /**
     * Hook method used to add custom fields to each result
     *
     * @param WP_Post $result
     * @return void
     */
    protected function populateCustomFields(&$result)
    {
        die("Custom fields set to true, but no custom fields are registered.");
    }

    /**
     * Check if a specific field should be retrieved
     *
     * @param string $field
     * @return void
     */
    protected function hasAttr($attr)
    {
        if (empty($this->attrs)) return true;
        if (in_array($attr, $this->attrs)) return true;
        return false;
    }

    /**
     * Remove all fields that should not be retrieved
     *
     * @param WP_Post $post
     * @return void
     */
    protected function removeUnusedAttr($result) {
        foreach ($result as $attr=>$value) {
            if (!in_array($attr, $this->attrs) && $attr !== "ID" && $attr !== "filter") {
                unset($result->$attr);
            }
        }
    }

    /**
     * Add or remove attributes that should be returned for each posts.
     *
     * @param array $keys
     * @param boolean $merge
     * @return void
     */
    public function attrs($attrs, $merge = false)
    {
        $attrs = (array) $attrs;
        if ($merge) {
            $this->attrs = array_merge($this->attrs, $attrs);
        } else {
            $this->attrs = $attrs;
        }
        $this->cachedFilters = false;
        return $this;
    }

    /**
     * Add or remove fields to be populated
     *
     * @param array $fields Fields to be populated
     * @param bool $merge Add fields to the current defined one or replace them
     * @return self
     */
    public function fields($fields, $merge = false)
    {
        $fields = (array) $fields;
        if ($merge) {
            $this->fields = array_merge($this->fields, $fields);
        } else {
            $this->fields = $fields;
        }
        $this->cachedFilters = false;
        return $this;
    }


    // ==================================================
    // > ACTIONS
    // ==================================================
    /**
     * Update all posts matching the query
     *
     * @param array $attrs
     * @param array $filds
     * @param bool $merge Only update data that is not already set
     * @return self
     */
    public function update($attrs = [], $fields = false, $merge = false)
    {
        foreach ($this->get() as $result) {
            // Default attributes
            if ($attrs && !empty($attrs)) {
                static::updateAttrs($result, $attrs, $merge);
            }

            // Custom fields
            if ($fields && !empty($fields)) {
                static::updateFields($result, $fields, $merge);
            }

            // Taxonomy
            // TODO
        }

        // Force get refresh
        $this->cachedFilters = false;

        return $this;
    }

    /**
     * Update a result base attributes
     *
     * @param array $attrs
     * @param bool $merge Only update empty attrs
     * @return void
     */
    public static function updateAttrs($result, $attrs, $merge = false)
    {
        if ($merge) {
            foreach ($attrs as $attr=>$value) {
                if (isset($result->$attr) && !empty($result->$attr)) {
                    unset($attrs[$attr]);
                }
            }
        }

        foreach ($attrs as &$attr) {
            if (is_callable($attr)) $attr = $attr($result);
        }

        $attrs["ID"] = $result->ID;
        wp_update_post($attrs);
    }

    /**
     * Update a result custom fields
     *
     * @param object $result
     * @param array $fields
     * @param string $fields_prefix
     * @param bool $merge Only update empty fields
     * @return void
     */
    public static function updateFields($result, $fields, $merge = false, $fields_prefix = "")
    {
        if ($fields && !empty($fields)) {
            $result_id = Data::filter($result, "id");
            foreach ($fields as $key=>$value) {
                if (is_callable($value)) $value = $value($result);
                Data::update($key, $value, $fields_prefix.$result_id, $merge);
            }
        }
    }

    /**
     * Delete all posts matching the query
     *
     * @param boolean $force : Completely remove the posts instead of placing them in the trash
     * @return void
     */
    public function delete($force = false)
    {
        foreach ($this->get() as $post) {
            wp_delete_post($post->ID, $force);
        }
    }
}