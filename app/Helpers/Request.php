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
     * List of email addresses to send the alert mail to
     *
     * @var array of strings
     */
    private $alertMails = [];

    /**
     * The request arguments
     *
     * @var array
     */
    public $args = [];

    /**
     * The request response
     *
     * @var array
     */
    public $response = [];
    /**
     * @var mixed
     */
    public $responseCode = false;
    /**
     * @var mixed
     */
    public $responseBody = false;

    /**
     * Instanciate a new Request
     *
     * @param string $url
     */
    public function __construct($url)
    {
        $this->url = $url;
    }

    /**
     * Send a remote POST request
     *
     * @see self::send
     */
    public function get($body = false, $headers = false)
    {
        return $this->send("GET", $body, $headers);
    }

    /**
     * Send a remote POST request
     *
     * @see self::send
     */
    public function post($body = false, $headers = false)
    {
        return $this->send("POST", $body, $headers);
    }

    /**
     * Send a remote PUT request
     *
     * @see self::send
     */
    public function put($body = false, $headers = false)
    {
        return $this->send("PUT", $body, $headers);
    }

    /**
     * Send a remote PATCH request
     *
     * @see self::send
     */
    public function patch($body = false, $headers = false)
    {
        return $this->send("PATCH", $body, $headers);
    }

    /**
     * Send a remote DELETE request
     *
     * @see self::send
     */
    public function delete($body = false, $headers = false)
    {
        return $this->send("DELETE", $body, $headers);
    }

    /**
     * Send a remote request
     *
     * @see https://codex.wordpress.org/Function_Reference/wp_remote_request
     *
     * @param  string method   The HTTP method to use
     * @param  mixed  $body    The body of the request
     * @param  array  $headers The headers of the request
     * @return object the Response
     */
    public function send($method, $body = false, $headers = false)
    {
        // Prevent external request blocking by Performances helper
        remove_all_filters("pre_http_request");

        // Set the method to use
        $this->args["method"] = $method;

        // Add body and headers, if provided
        if ($body) {
            $this->setBody($body);
        }

        if ($headers) {
            $this->addHeaders($headers);
        }

        // Send the request
        $this->response = wp_remote_request($this->url, $this->args);

        // If it failed, act on it
        if (empty($this->response) || $this->hasFailed()) {
            return $this->onFailure();
        }

        // Parse response parts
        $this->response["body"] = Text::maybeJsonDecode(wp_remote_retrieve_body($this->response), true);
        $this->response         = (object) $this->response;
        return $this->response;
    }

    // =============================================================================
    // > HEADERS, BODY AND OTHER OPTIONS
    // =============================================================================
    /**
     * Set the headers of the request
     *
     * @param  array|string $headers
     * @return self
     */
    public function setHeaders($headers)
    {
        return $this->setOption("headers", (array) $headers);
    }

    /**
     * Add one or several headers line
     *
     * @param  array|string $headers
     * @return self
     */
    public function addHeaders($headers)
    {
        return $this->setOption("headers", array_merge($this->args["headers"] ?? [], (array) $headers));
    }

    /**
     * Remove a specific header linke
     *
     * @param  string $header
     * @return self
     */
    public function removeHeader($header)
    {
        return $this->setOption("headers", array_filter($this->args["headers"], function ($key, $value) use ($header) {
            if ($key == $header || $value == $header) {
                return false;
            }

            return true;
        }, ARRAY_FILTER_USE_BOTH));
    }

    /**
     * Set the body of the request
     *
     * @param  [type] $body
     * @return void
     */
    public function setBody($body)
    {
        return $this->setOption("body", $body);
    }

    /**
     * Update the request arguments
     *
     * @param  string $option_name
     * @param  string $option_value
     * @return self
     */
    public function setOption($option_name, $option_value)
    {
        $this->args[$option_name] = $option_value;
        return $this;
    }

    // ==================================================
    // > FAILURE HANDLING
    // ==================================================
    /**
     * Send an email to the given addresses if the request fails
     *
     * @param  array|string $emails
     * @return void
     */
    public function onErrorAlert($emails)
    {
        $this->alertMails = (array) $emails;
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

    /**
     * Log and send mail when an error occurs
     *
     * @return void
     */
    private function onFailure($log = "")
    {
        // Log failure
        Log::failed_requests("Request failed : {$this->args['method']} to {$this->url}");

        // Send mail, if requested
        if (!empty($this->alertMails)) {
            Mail::send($this->alertMails, get_bloginfo("name") . " : Request failed", implode("", [
                $log,
                "<h1>body</h1>",
                !empty($this->args["body"]) ? "<pre>" . $this->args["body"] . "</pre>" : "<p>No body</p>",
                "<h1>response</h1>",
                "<pre>" . json_encode($this->response) . "</pre>",
            ]));
        }

        return false;
    }
}