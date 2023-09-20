<?php

namespace Syltaen;

/**
 * Wrap each result of a model in a class that is used to retrieve dynamic fields defined by the model
 */

abstract class AbstractUser extends ModelItem
{
    const FIELD_PREFIX = "user_";

    /**
     * Get the type of the item
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->display_name;
    }

    /**
     * Get the type of the item
     *
     * @return string
     */
    public function getType()
    {
        return "user";
    }

    /**
     * Get the date of the post
     *
     * @param  string   $format
     * @return string
     */
    public function getDate($format = "d/m/Y")
    {
        return date_i18n($format, strtotime($this->registered));
    }

    /**
     * Get the slug of the post
     *
     * @return string
     */
    public function getSlug()
    {
        return $this->nicename;
    }

    /**
     * Get a specific meta data
     *
     * @param  string
     * @return mixed
     */
    public function getMeta($meta_key = "", $multiple = false)
    {
        return get_user_meta($this->getID(), $meta_key, !$multiple);
    }

    /**
     * Update a meta value in the database
     *
     * @param string $key
     * @param mixed  $value
     */
    public function setMeta($key, $value)
    {
        return update_user_meta($this->getID(), $key, $value);
    }

    /**
     * Add a new meta value to a multi-value meta
     *
     * @param string $key
     * @param mixed  $value
     */
    public function addMeta($key, $value)
    {
        return add_user_meta($this->getID(), $key, $value);
    }

    /**
     * Set the attributes of an item
     *
     * @param  array        $attributes
     * @return int|WP_Error The updated user's ID or a WP_Error object if the user could not be updated.
     */
    public function setProperties($keys, $merge = false)
    {
        if (empty($keys)) {
            return false;
        }

        $keys       = $this->parseProperties($keys, $merge);
        $keys["ID"] = $this->getID();
        return wp_update_user($keys);
    }

    /**
     * Alias for updateRoles()
     *
     * @param  [type]  $tax
     * @param  boolean $merge
     * @return void
     */
    public function setTaxonomies($roles, $merge = false)
    {
        $user = get_user_by("id", $this->ID);

        if (empty($user)) {
            return false;
        }

        // No merge : remove all current roles
        if (!$merge) {
            $user->set_role("");
        }

        // Add all new roles
        foreach ((array) $roles as $role) {
            $user->add_role($role);
        }
    }

    /**
     * Alias for updateTaxonomies()
     *
     * @return void
     */
    public function setRoles($roles, $merge = false)
    {
        $this->setTaxonomies($roles, $merge);
    }

    /**
     * Delete a single user
     *
     * @param  bool|int $reassign Reassign posts and links to new User ID.
     * @return void
     */
    public function delete($reassign = null)
    {
        require_once ABSPATH . "wp-admin/includes/user.php";
        return wp_delete_user($this->ID, $reassign);
    }

    /**
     * Update or filter the object keys before there are saved
     *
     * @param  object   $object
     * @return object
     */
    public static function filterObjectKeys($user)
    {
        global $wp_roles;

        // Keep only the some data
        $item = (object) [
            "ID"         => $user->ID,
            "roles"      => set($user->roles)->mapAssoc(function ($i, $role) use ($wp_roles) {
                return [$role => $wp_roles->roles[$role]];
            }),
            "caps"       => $user->allcaps,
            "first_name" => $user->first_name,
            "last_name"  => $user->last_name,
        ];

        // Remove "user_" prefix for some data
        foreach ($user->data as $key => $value) {
            $item->{str_replace("user_", "", $key)} = $value;
        }

        // Unset the default URL
        unset($item->url);
        unset($item->status);

        return $item;
    }

    // =============================================================================
    // > CHECKERS
    // =============================================================================
    /**
     * Check is the user has a capability or a role
     *
     * @param  string|array $capability Capability or Role to check, or an array of them
     * @param  string       $relation   If $capability is an array, specify if the users should have any or all capacility (any|all)
     * @return bool
     */
    public function can($capability, $relation = "all")
    {
        $matches = set($this->caps)->keys()->keep($capability)->values();

        if ($relation == "all") {
            return $matches->count() == count((array) $capability);
        } else {
            return !$matches->empty();
        }
    }

    /**
     * Check that the user is the one currently logged
     *
     * @return boolean
     */
    public function isLogged()
    {
        return $this->getID() && $this->getID() == get_current_user_id();
    }

    // =============================================================================
    // > ACTIONS
    // =============================================================================
    /**
     * Send a mail to each matching user
     *
     * @param  string $subject
     * @param  string $body
     * @param  array  $custom_headers
     * @return void
     */
    public function sendMail($subject, $body, $attachments = [], $mail_hook = false)
    {
        Mail::send($this->email, $subject, $body, $attachments, $mail_hook);
    }

    /**
     * Login with that user account
     *
     * @param  string  $redirecton URL to which redirect when logged in successfully
     * @return boolean Success of the login
     */
    public function login($redirection = false)
    {
        wp_set_current_user($this->ID, $this->login);
        wp_set_auth_cookie($this->ID);
        do_action("wp_login", $this->login, $this);

        if ($redirection) {
            Route::redirect($redirection);
        }
    }
}
