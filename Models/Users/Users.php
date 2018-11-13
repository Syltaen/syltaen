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




    // =============================================================================
    // > UNIQUE KEY
    // =============================================================================
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


    /**
     * Generate a unique user key
     *
     * @return string
     */
    public static function generateKey($prefix = "", $suffix = "")
    {
        return $prefix . sha1(microtime(true) . mt_rand(10000, 90000)) . $suffix;
    }

}