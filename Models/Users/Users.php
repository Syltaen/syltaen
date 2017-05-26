<?php

namespace Syltaen\Models\Users;

class Users {

    public function __construct() {

    }

    // ==================================================
    // > GET USER
    // ==================================================
    public function get()
    {

    }

    // ==================================================
    // > CREATE NEW USER
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
    public function can()
    {

    }

    public static function unregisterRoles($roles)
    {
        foreach ($roles as $role) {
            if (get_role($role)) {
                remove_role($role);
            }
        }
    }

}