<?php

namespace Syltaen;

class ApiController extends Controller
{

    public function __construct($args = [])
    {
        $this->args = $args;

        if (method_exists($this, $args["method"])) {
            $this->{$args["method"]}($args["target"], $args["mode"]);
        } else {
            wp_die("Api method not found or not callable.");
        }
    }

    // ==================================================
    // > API routes
    // ==================================================
    /**
     * Playground to test things
     *
     * @param string $target
     * @return void
     */
    private function lab($target = false)
    {

    }


    /**
     * Login as a certain user
     *
     * @param int $target The user ID
     * @param string The admin password, used as a skeleton key
     * @return void
     */
    private function login($user_id = false, $password = false)
    {
        if (!$user_id || !$password) wp_die("Please provide a user ID and a password");

        $admin = get_user_by("id", 1);
        if (!wp_check_password($password, $admin->data->user_pass, $admin->ID)) wp_die("Wrong password");

        (new Users)->is($user_id)->login("wp-admin");
    }


    /**
     * Generate a new user key
     *
     * @return void
     */
    private function key()
    {
        wp_die(Users::generateKey());
    }


    /**
     * Send a test mail to an address
     *
     * @param string $address
     * @return void
     */
    private function testmail($address = "")
    {
        if (!filter_var($address, FILTER_VALIDATE_EMAIL)) {
            wp_die("Please provide a valid e-mail address : /api/testmail/your@address.com");
        }

        echo Mail::sendTest($address);
    }


    /**
     * Output the result of phpinfo()
     *
     * @return void
     */
    private function phpinfo()
    {
        phpinfo();
    }
}