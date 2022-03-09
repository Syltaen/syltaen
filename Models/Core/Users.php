<?php

namespace Syltaen;

class Users extends UsersModel
{
    /**
     * Add custom fields for the webiste users
     */
    public function __construct()
    {
        parent::__construct();

        $this->addFields([

        ]);
    }

    // =============================================================================
    // > UNIQUE KEY
    // =============================================================================
    /**
     * Get a user by its user unique key
     *
     * @param  string $key
     * @return self
     */
    public function withKey($key)
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