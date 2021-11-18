<?php

namespace Syltaen;

/**
 * Wrap each result of a model in a class that is used to retrieve dynamic fields defined by the model
 */

class ModelItemUser extends ModelItem
{
    const FIELD_PREFIX = "user_";

    /**
     * Expose each default value of the wp_object
     *
     * @param object $user
     * @param Model $model
     */
    public function __construct($user, $model)
    {
        // Keep only the some data
        $item = (object) [
            "ID"         => $user->ID,
            "roles"      => $user->roles,
            "caps"       => $user->allcaps,
            "first_name" => $user->first_name,
            "last_name"  => $user->last_name
        ];

        // Remove "user_" prefix for some data
        foreach ($user->data as $key=>$value) {
            $item->{str_replace("user_", "", $key)} = $value;
        }

        parent::__construct($item, $model);
    }

    /**
     * Delete a single user
     *
     * @param bool|int $reassign Reassign posts and links to new User ID.
     * @return void
     */
    public function delete($reassign = null)
    {
        require_once(ABSPATH . "wp-admin/includes/user.php");
        return wp_delete_user($this->ID, $reassign);
    }


    /**
     * Set the attributes of an item
     *
     * @param int $id
     * @param array $attributes
     * @return int|WP_Error The updated user's ID or a WP_Error object if the user could not be updated.
     */
    public static function setAttrs($id, $attrs)
    {
        $attrs["ID"] = $id;
        return wp_update_user($attrs);
    }

    /**
     * Update a meta value in the database
     *
     * @param int $id
     * @param string $key
     * @param mixed $value
     * @return mixed Meta ID if the key didn't exist, true on successful update, false on failure
     */
    public function setMeta($id, $key, $value)
    {
        return update_user_meta($id, $key, $value);
    }


    /**
     * Alias for updateRoles()
     *
     * @param [type] $tax
     * @param boolean $merge
     * @return void
     */
    public function updateTaxonomies($roles, $merge = false)
    {
        $user = get_user_by("id", $this->ID);

        if (empty($user)) return false;

        // No merge : remove all current roles
        if (!$merge) $user->set_role("");

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
    public function updateRoles($roles, $merge = false)
    {
        $this->updateTaxonomies($roles, $mege);
    }
}