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
     * @param [type] $target
     * @return void
     */
    private function login($target)
    {
        wp_die("no");
        (new Users)->is($target)->login("wp-admin");
    }

    /**
     * Generate a new user key
     *
     * @param [type] $target
     * @return void
     */
    private function key()
    {
        wp_die(sha1(microtime(true).mt_rand(10000,90000)));
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
}