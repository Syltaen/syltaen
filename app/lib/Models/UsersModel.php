<?php

namespace Syltaen;

abstract class UsersModel extends Model
{

    // =============================================================================
    // > QUERY MODIFIERS
    // =============================================================================

    /* Update parent method */
    public function is($list, $add = false, $filter_key = "include")
    {
        return parent::is($list, $add, $filter_key);
    }

    public function merge($model)
    {
        if (isset($model->filters["include"])) {
            $this->is($model->filters["include"], true);
        }
        return $this;
    }

    /* Update parent method */
    public function isnt($list, $filter_key = "exclude")
    {
        return parent::isnt($list, $filter_key);
    }

    /* Update parent method */
    public function limit($limit = false, $filter_key = "number")
    {
        return parent::limit($limit, $filter_key);
    }

    /* Update parent method */
    public function search($terms, $columns = [], $strict = false)
    {
        $this->filters["search"] = $strict ? $terms : "*$terms*";

        if (!empty($columns)) $this->filters["search_columns"] = $columns;

        return $this;
    }


    /**
     * Filter to the current logged user
     *
     * @return self
     */
    public function logged()
    {
        return $this->is(wp_get_current_user()->ID);
    }


    /**
     * Check if there is a logged user
     *
     * @return boolean
     */
    public static function isLogged()
    {
        return is_user_logged_in();
    }


    /**
     * Filter users by roles
     *
     * @param array|string $roles An array or a comma-separated list of role names that users must match to be included in results.
     * @param $relation Specify if the matches should have : any, all or none of the roles
     * @return self
     */
    public function role($roles, $relation = "all")
    {
        switch ($relation) {
            case "any":
                $this->filters["role__in"] = $roles;
                break;
            case "none":
                $this->filters["role__not_in"] = $roles;
                break;
            case "all":
            default:
                $this->filters["role"] = $roles;
        }
        return $this;
    }


    /**
     * Set a default filter because runing WP_User_Query without any argument return no result
     *
     * @param boolean $filter_keys
     * @param array $default_filters
     * @return self
     */
    public function clearFilters($filter_keys = false, $default_filters = null)
    {
        return parent::clearFilters($filter_keys, [
            "prevent_empty"   => true
        ]);
    }




    // =============================================================================
    // > GETTERS
    // =============================================================================
    /**
     * Update parent method to have a cleaner data structure for each result
     *
     * @param WP_User_Query $query
     * @return void
     */
    protected static function getResultsFromQuery($query)
    {
        // empty
        if (empty($query->results)) return [];
        // string|int -> IDs
        if (is_string($query->results[0]) || is_int($query->results[0])) return $query->results;
        // already transformed
        if (get_class($query->results[0]) !== "WP_User") return $query->results;

        $wp_users = $query->results;
        $results  = [];

        $query->results = [];
        foreach ($wp_users as $wp_user) {
            $result = (object) [
                "ID"    => $wp_user->ID,
                "caps"  => $wp_user->allcaps
            ];
            foreach ($wp_user->data as $key=>$value) {
                $key          = str_replace("user_", "", $key);
                $result->$key = $value;
            }

            $result->first_name = $wp_user->first_name;
            $result->last_name  = $wp_user->last_name;

            $results[] = $result;
        }

        $query->results = $results;

        return $query->results;
    }


    /* Update parent method */
    public function run($force = false)
    {
        if ($this->cachedQuery && $this->filters == $this->cachedFilters && !$force) return $this;
        $this->clearCache();
        $this->cachedQuery = new \WP_User_Query($this->filters);
        $this->cachedFilters = $this->filters;
        return $this;
    }


    /* Update parent method */
    public function count($paginated = true)
    {
        $total = $this->getQuery()->total_users;

        if (!$paginated || !isset($this->filters["number"])) return $total;

        // Not on last page : return the limit
        $page = isset($this->filters["paged"]) ? $this->filters["paged"] : 1;

        if ($page < $this->getPagesCount()) return $this->filters["number"];

        // On last page : return the rest
        return $total - ($page - 1 ) * $this->filters["number"];
    }

    /* Update parent method */
    public function getPagesCount()
    {
        $total = $this->getQuery()->total_users;

        if (!isset($this->filters["number"])) return $total;

        return ceil($total / $this->filters["number"]);
    }


    // =============================================================================
    // > DATA HANDLING FOR EACH POST
    // =============================================================================
    /* Update parent method */
    protected function populateFields(&$user, $fields_prefix = "user_")
    {
        parent::populateFields($user, $fields_prefix);
    }


    // =============================================================================
    // > ROLES AND PERMISSIONS
    // =============================================================================
    /**
     * Check is the matched users have a capability or a role
     *
     * @param string|array $capability Capability or Role to check, or an array of them
     * @param string $relation If $capability is an array, specify if the users should have any or all capacility (any|all)
     * @return void
     */
    public function can($capability, $relation = "all")
    {
        if (!$this->found()) return false;

        foreach ($this->get() as $user) {
            if (is_array($capability)) {
                switch ($relation) {
                    case "any":
                        $user_can = false;
                        foreach ($capability as $cap) {
                            if (isset($user->caps[$cap]) && $user->caps[$cap]) {
                                $user_can = true;
                                break;
                            }
                        }
                        if (!$user_can) return false;
                        break;
                    case "all":
                    default:
                        foreach ($capability as $cap) {
                            if (!isset($user->caps[$cap]) || !$user->caps[$cap]) {
                                return false;
                            }
                        }
                        break;
                }
            } else {
                if (!isset($user->caps[$capability]) || !$user->caps[$capability]) {
                    return false;
                }
            }
        }
        return true;
    }


    /**
     * Remove unused roles
     *
     * @param array $roles
     * @return void
     */
    public static function unregisterRoles($roles)
    {
        foreach ($roles as $role) {
            if (get_role($role)) {
                remove_role($role);
            }
        }
    }


    /**
     * Register custom capablilities
     *
     * @param array $capablilities
     * @return void
     */
    public static function registerCapabilities($capablilities)
    {
        $role = get_role("administrator");

        foreach ($capablilities as $cap) {
            $role->add_cap($cap);
        }
    }




    // =============================================================================
    // > ACTIONS
    // =============================================================================
    /**
     * Create a new user
     *
     * @param string $login
     * @param string $password
     * @param string $email
     * @param array $roles
     * @return int $user_id
     */
    public static function add($login, $password, $email, $attrs = [], $fields = [], $roles = [])
    {
        $user_id = wp_insert_user(array_merge([
            "user_login"           => $login,
            "user_pass"            => $password,
            "user_email"           => $email,
            "show_admin_bar_front" => "false"
        ], $attrs));

        if ($user_id instanceof \WP_Error) return $user_id;

        if ($fields && !empty($fields)) {
            static::updateFields($user_id, $fields);
        }

        if ($roles && !empty($roles)) {
            static::updateRoles($user_id, $roles);
        }

        return $user_id;
    }


    /**
     * Update all posts matching the query
     *
     * @param array $attrs
     * @param array $filds
     * @param array $merge Only update data that is not already set
     * @return self
     */
    public function update($attrs = [], $fields = [], $roles = [], $merge = false)
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

            // Roles
            if ($roles && !empty($roles)) {
                static::updateRoles($result, $roles, $merge);
            }
        }

        // Force get refresh
        $this->clearCache();

        return $this;
    }


    /**
     * Update parent method
     *
     * @see https://codex.wordpress.org/Function_Reference/wp_update_user
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
            if (is_callable($attr) && !is_string($attr)) $attr = $attr($result);
        }

        $attrs["ID"] = $result->ID;
        wp_update_user($attrs);
    }


    /* Update parent method */
    public static function updateFields($user, $fields, $merge = false, $fields_prefix = "user_")
    {
        parent::updateFields($user, $fields, $merge, $fields_prefix);
    }


    /**
     * Update a user's roles
     *
     * @param mixed $user The user
     * @param array $roles A list of roles slugs
     * @return void
     */
    public static function updateRoles($user, $roles, $merge = false)
    {
        $user = get_user_by("id", Data::filter($user, "id"));
        if ($user) {
            if (!$merge) {
                $user->set_role("");
            }
            foreach ((array) $roles as $role) {
                $user->add_role($role);
            }
        }
    }


    /* Update parent method */
    public function delete($reassign = null)
    {
        foreach ($this->get() as $user) {
            wp_delete_user($user->ID, $reassign);
        }
        $this->clearCache();
    }


    /**
     * Login as the first found user
     *
     * @param string $redirecton URL to which redirect when logged in successfully
     * @return boolean Success of the login
     */
    public function login($redirection = false)
    {
        $user = $this->getOne();

        if ($this->count()) {
            wp_set_current_user($user->ID, $user->login);
            wp_set_auth_cookie($user->ID);
            do_action("wp_login", $user->login, $user);

            if ($redirection) {
                Route::redirect($redirection);
            }

            return true;
        } else {
            return false;
        }
    }


    /**
     * Logout any logged user
     *
     * @param string $redirection URL to which redirect when logged out successfully
     * @return void
     */
    public static function logout($redirection = false)
    {
        wp_logout();
        if ($redirection) {
            Route::redirect($redirection);
        }
    }


    /**
     * Send a mail to each matching user
     *
     * @param string $subject
     * @param string $body
     * @param array $custom_headers
     * @return void
     */
    public function sendMail($subject, $body, $custom_headers = [])
    {
        foreach ($this->get() as $user) {
            Mail::send($user->email, $subject, $body, $custom_headers);
        }
    }
}