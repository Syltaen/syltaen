<?php

namespace Syltaen;

class Users extends UsersModel
{

    public function __construct()
    {
        parent::__construct();

        $this->fields = [
            // "key"
        ];
    }

    // ==================================================
    // > FILTERS
    // ==================================================
    /**
     * Get a user by its user unique key
     *
     * @param string $key
     * @return self
     */
    public function key($key)
    {
        return $this->meta("key", $key);
    }



    // ==================================================
    // > USER ACTIONS
    // ==================================================
    /**
     * Validate all found users
     *
     * @param string $token User activation key
     * @param string $redirection URL to which redirect when validated successfully
     * @return string|boolean Error message if error, or false if no error
     */
    public function validate($key = false, $redirection = false)
    {
        $users = $key ? $this->key($key)->get() : $this->get();

        if ($this->count() == 0) return __("Cette clef de validation ne correspond à aucun utilisateur.", "syltaen");

        foreach ($users as $user) {
            if ($user->state == "refused") return __("Votre compte a été désactivé par un administrateur.", "syltaen");
            if ($user->state == "valid") return __("Votre compte a déjà été validé.", "syltaen");
        }

        $this->update(null, ["status" => "valid"]);
        $this->login($redirection);

        return false;
    }


    // ==================================================
    // > USER CREATION
    // ==================================================
    /**
     * Update the defaut method to add a unique key to every created user
     *
     * @return void
     */
    public static function add($login, $password, $email, $attrs = [], $fields = [], $roles = [])
    {
        return parent::add($login, $password, $email, $attrs, array_merge($fields, [
            "key" => sha1(microtime(true).mt_rand(10000, 90000))
        ]), $roles);
    }
}