<?php

namespace Syltaen;

class Request
{
    /**
     * The URL to send the request to
     *
     * @var string
     */
    public $url;

    /**
     * Define if a mail should be sent when the request fail
     *
     * @var boolean
     */
    private $alertError = false;

    /**
     * List of email addresses to send the alert mail to
     *
     * @var array of strings
     */
    private $alertMails = [
        "stanley.lambot@gmail.com",
        "stanley.lambot@hungryminds.be"
    ];

    /**
     * The request arguments
     *
     * @var array
     */
    private $args = [
        "timeout" => 120
    ];

    /**
     * The request response
     *
     * @var array
     */
    public $response      = [];

    public $responseCode   = false;
    public $responseBody   = false;

    /**
     * Instanciate a new Request
     *
     * @param boolean $url
     * @param boolean $alertError
     */
    public function __construct($url = false, $alertError = false)
    {
        $this->url = $url ? $url : site_url();

        $this->alertError = $alertError;
    }

    // ==================================================
    // > ARGUMENTS & SHORTCUTS
    // ==================================================
    /**
     * Update the request arguments
     *
     * @param array $args
     * @return self
     */
    public function args($args = [])
    {
        $this->args = array_merge($this->args, $args);
        return $this;
    }

    /**
     * Update the request headers
     *
     * @param array $headers
     * @return self
     */
    public function headers($headers = [])
    {
        $this->args["headers"] = $headers;
        return $this;
    }

    /**
     * Update the request body
     *
     * @param mixed $body
     * @return self
     */
    public function body($body)
    {
        $this->args["body"] = $body;
        return $this;
    }


    // ==================================================
    // > RESPONSE
    // ==================================================
    /**
     * Do things with the response
     *
     * @return mixed the response
     */
    public function processResponse()
    {
        if (empty($this->response)) return false;

        // Errors
        if ($this->hasFailed()) $this->alert();


        // Extract
        $this->responseCode = wp_remote_retrieve_response_code($this->response);
        $this->responseBody = wp_remote_retrieve_body($this->response);

        return $this->response;
    }

    /**
     * Check if the request has failed
     *
     * @return boolean
     */
    public function hasFailed()
    {
        return is_wp_error($this->response);
    }

    // ==================================================
    // > REMOTE REQUESTS
    // ==================================================
    /**
     * Send a remote POST request
     *
     * @see https://codex.wordpress.org/Function_Reference/wp_remote_post
     * @param mixed $body
     * @param array $args
     * @return array the Response
     */
    public function post($body = false, $headers = false)
    {
        if ($body) $this->body($body);
        if ($headers) $this->headers($headers);

        $this->args["method"] = "POST";

        $this->response = wp_remote_post($this->url, $this->args);
        $this->processResponse();

        return $this;
    }

    /**
     * Send a remote GET request
     *
     * @see https://codex.wordpress.org/Function_Reference/wp_remote_get
     * @param mixed $body
     * @param array $args
     * @return array the Response
     */
    public function get($body = false, $headers = false)
    {
        if ($body) $this->body($body);
        if ($headers) $this->headers($headers);

        $this->args["method"] = "GET";

        $this->response = wp_remote_get($this->url, $this->args);
        $this->processResponse();

        return $this;
    }


    // ==================================================
    // > INFOS
    // ==================================================
    /**
     * Get the first availabled transport name
     *
     * @return string
     */
    public function getTransport()
    {
        $http   = new WP_Http();
        $transp = $http->_get_first_available_transport([], $this->url);
    }

    // ==================================================
    // > ERRORS
    // ==================================================
    /**
     * Log and send mail when an error occurs
     *
     * @param array $response
     * @return void
     */
    private function alert()
    {
        if (!$this->alertError) return false;

        // ========== Log the request ========== //
        $log = "Request failed : {$this->args['method']} to {$this->url}";
        // (new Cache)->log($log, "requests");

        // ========== Send a mail ========== //
        $mail  = $log;
        $mail .= "<h1>body</h1>";
        $mail .= isset($this->args['body']) ? "<pre>" . $this->args["body"] . "</pre>" : "<p>No body</p>";
        $mail .= "<h1>response</h1><pre>" . json_encode($this->response) . "</pre>";


        Mail::send($this->alertMails, Mail::$fromName . " : Request failed", $mail);

    }
}