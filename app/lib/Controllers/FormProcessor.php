<?php

namespace Syltaen;

abstract class FormProcessor extends DataProcessor
{
    /**
     * Fields that will be auto-validated
     */
    public $required_fields = [];

    /**
     * List of errors encountered during processing
     *
     * @var array
     */
    public $errors = [];

    /**
     * List of prefilled-fields
     *
     * @var array
     */
    public $prefill = [];

    /**
     * List of locked-fields
     *
     * @var array
     */
    public $locks = [];

    /**
     * List of fields to hide
     * @var array
     */
    public $hidden = [];

    /**
     * List of choice options for select/radio/checkbox fields
     *
     * @var array
     */
    public $options = [];

    /**
     * Submited data
     *
     * @var array
     */
    public $payload = [];

    /**
     * The message to display globaly, if any
     *
     * @var string
     */
    public $global_error_message = "Certaines champs ne sont pas corrects, merci de les corriger.";

    /**
     * Processing the rendering context
     */
    public function process()
    {
        $this->payload = $_POST;

        // Get data that needed at any time
        $this->fetchRequirements();

        // Check permission
        $this->checkPermissions();

        // Get fields options
        $this->setup();

        // Get data that are needed at any time
        $this->addPrefill($this->payload);

        // Extra config from hook
        do_action("syltaen_form_init", $this);

        // If data is posted, process it
        if (!empty($this->payload)) {
            // Check for errors
            $this->validatePayload();

            // If there is none, process the data
            if (empty($this->errors)) {
                $this->post();
            }

            if (!empty($this->errors) && $this->global_error_message) {
                $this->controller->error($this->global_error_message);
            }

            // If eveyting is good, continue
            if (empty($this->errors)) {
                do_action("syltaen_form_success", $this);
                $this->continueToNextPage();
            } else {
                do_action("syltaen_form_errors", $this);
            }
        }

        // Success page
        if (!empty($_GET["success"])) {
            $this->success();
            return $this->data;
        }

        // Else, fallback to get (display the form)
        $this->data["options"] = $this->options;
        $this->data["prefill"] = $this->prefill;
        $this->data["locks"]   = $this->locks;
        $this->data["hidden"]  = $this->hidden;
        $this->data["errors"]  = $this->errors;
        $this->get();

        return $this->data;
    }

    /**
     * Process get requests
     *
     * @return void
     */
    public function get()
    {
    }

    /**
     * Process submitted data
     *
     * @return void
     */
    public function post()
    {
    }

    /**
     * Action to perform once the payload has been processed without error
     *
     * @return void
     */
    public function continueToNextPage()
    {
        Route::redirect(
            Route::getFullUrl(["success" => 1])
        );
    }

    /**
     * Populate context for the success page
     *
     * @return void
     */
    public function success()
    {
        $this->data["success"] = "<h2>Merci !</h2><p>Le formulaire a bien été envoyé</p>";
    }

    /**
     * Setup the form : prefill data, add options, add locks
     *
     * @return void
     */
    public function setup()
    {
        // No custom prefill/options/locks by default
    }

    /**
     * Add new options to the list
     *
     * @param  array  $options
     * @return self
     */
    public function addOptions($options)
    {
        $this->options = array_merge(
            $this->options,
            $options,
        );
        return $this;
    }

    /**
     * @return mixed
     */
    public function addPrefill($prefill)
    {
        $this->prefill = array_merge(
            $this->prefill,
            $prefill
        );
        return $this;
    }

    /**
     * @return mixed
     */
    public function addUploadPrefill($field, $attachment)
    {
        // Add prefilled data
        if ($attachment && empty($this->payload[$field])) {
            $this->addPrefill([
                $field => [$attachment->getData()],
            ]);
        }

        // Process payload data
        if (!empty($this->payload[$field])) {
            $this->payload[$field] = json_decode(stripslashes($this->payload[$field]));
        }
    }

    /**
     * Add new locks to the list
     *
     * @param  array  $locks
     * @param  bool   $hidden_locks Hide the locked fields
     * @return self
     */
    public function addLocks($locks, $hidden_locks = false)
    {
        $this->locks = array_merge(
            $this->locks,
            $locks
        );

        if ($hidden_locks) {
            $this->addHidden(array_keys($locks));
        }

        return $this;
    }

    /**
     * Add new hidden fields to the list
     *
     * @param  $hidden_keys
     * @return self
     */
    public function addHidden($hidden_keys)
    {
        $this->hidden = array_merge(
            $this->hidden,
            $hidden_keys);
        return $this;
    }

    /**
     * Validate that the payload can be processed
     *
     * @return void
     */

    public function validatePayload()
    {
        $this->validateRequired($this->required_fields);
    }

    /**
     * Check that these fields are filled in.
     *
     * @return void
     */
    public function validateRequired($fields, $error_message = "Ce champs est requis.")
    {
        $fields = apply_filters(static::class . "/required_fields", $fields, $this);
        /* #LOG# */\Syltaen\Log::debug(static::class . "/required_fields");

        foreach ((array) $fields as $req) {
            if (empty($this->payload[$req]) && !isset($this->locks[$req])) {
                $this->errors[$req] = $error_message;
            }
        }
    }

    /**
     * Check the user access to this form
     *
     * @return void
     */
    public function checkPermissions()
    {
    }

    /**
     * Fetch data that is required for GET and POST
     *
     * @return void
     */
    public function fetchRequirements()
    {
    }
}