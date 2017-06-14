<?php

namespace Syltaen\Models\Users;

use Syltaen\App\Services\Fields;

class Users
{
    /**
     * List of fields used by the Fields::store method
     *
     * @var array
     */
    protected $fields = [
        "user_team_type@team_type"
    ];

    /**
     * Store the query and its arguments to be modified by the model
     *
     * @var boolean
     */
    protected $query         = false;
    protected $filters       = [];
    protected $cachedFilters = [];
    protected $cachedUsers   = [];

    /**
     * Create the base query and pre-sort all needed fields
     */
    public function __construct()
    {
        $this->clearFilters();
    }

    // ==================================================
    // > QUERY MODIFIERS
    // ==================================================
    /**
     * Limit the number of users returned.
     * See https://codex.wordpress.org/Class_Reference/WP_User_Query#Pagination_Parameters
     * @param int $limit
     * @return self
     */
    public function limit($limit = false)
    {
        if ($limit) {
            $this->filters["number"] = $limit;
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
     * Restrict to only specific users
     *
     * @param array|int $list
     * @return void
     */
    public function is($list)
    {
        $this->filters["include"] = Fields::extractIds($list);
        return $this;
    }

    /**
     * Exclude specific users
     *
     * @param array|int $list
     * @return void
     */
    public function isnt($list)
    {
        $this->filters["exclude"] = Fields::extractIds($list);
        return $this;
    }

    // public function current()
    // {
    //     return wp_get_current_user();
    // }


    /**
     * Clear one, several or all filters
     *
     * @param array|string $filter_keys
     * @return self
     */
    public function clearFilters($filter_keys = false)
    {
        if (!$filter_keys) {
            $this->filters = [];
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
    public function get($limit = false, $page = false)
    {
        $this->limit($limit)->page($page);

        // Only re-fetch users if the query has been updated
        if ($this->filters !== $this->cachedFilters) {
            $this->cachedUsers = $this
                ->run()
                ->populateData()
                ->query
                ->results;
            $this->cachedFilters = $this->filters;
        }

        return $this->cachedUsers;
    }

    /**
     * Execute the query with the filters and store the result
     *
     * @return self
     */
    public function run()
    {
        $this->query = new \WP_User_Query($this->filters);
        return $this;
    }

    /**
     * Return the stored query
     *
     * @return WP_Query
     */
    public function getQuery()
    {
        return $this->query;
    }


    // ==================================================
    // > DATA HANDLING FOR EACH POST
    // ==================================================
    /**
     * Add data to each user
     *
     * @return self
     */
    public function populateData()
    {
        if (!isset($this->query) || empty($this->query)) die("The WP_Query need to be run before populating its posts.");

        foreach ($this->query->results as $user) {
            $this->populateUserData($user);
        }

        return $this;
    }

    /**
     * Launch each populate method on a user
     *
     * @param WP_User $user
     * @return void
     */
    public function populateUserData(&$user)
    {
        /* ADD FIELDS IF ANY */
        if (!empty($this->fields)) {
            $this->populateFields($user);
        }
    }

    /**
     * Add all Custom Fields's values specified in the model's constructor to a user
     *
     * @param WP_User $user
     * @return void
     */
    protected function populateFields(&$user)
    {
        Fields::store($user, $this->fields, "user_".$user->ID);
    }


    // ==================================================
    // > CHECKERS
    // ==================================================
    public function can()
    {

    }

    // ==================================================
    // > ACTIONS
    // ==================================================
    public static function add()
    {

    }


    public function update()
    {

    }

    public function delete()
    {

    }

    // ==================================================
    // > ROLES AND PERMISSIONS
    // ==================================================
    public static function unregisterRoles($roles)
    {
        foreach ($roles as $role) {
            if (get_role($role)) {
                remove_role($role);
            }
        }
    }

}