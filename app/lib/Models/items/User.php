<?php

namespace Syltaen;

/**
 * Wrap each result of a model in a class that is used to retrieve dynamic fields defined by the model
 */

class User extends ModelItem
{
    const FIELD_PREFIX = "user_";

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
     * @param  int    $id
     * @param  string $key
     * @param  mixed  $value
     * @return mixed  Meta ID if the key didn't exist, true on successful update, false on failure
     */
    public function setMeta($key, $value)
    {
        return update_user_meta($this->getID(), $key, $value);
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
        $this->setTaxonomies($roles, $mege);
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
        // Keep only the some data
        $item = (object) [
            "ID"         => $user->ID,
            "roles"      => $user->roles,
            "caps"       => $user->allcaps,
            "first_name" => $user->first_name,
            "last_name"  => $user->last_name,
        ];

        // Remove "user_" prefix for some data
        foreach ($user->data as $key => $value) {
            $item->{str_replace("user_", "", $key)} = $value;
        }

        return $item;
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