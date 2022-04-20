<?php

namespace Syltaen;

abstract class FormProcessor extends DataProcessor
{
    /**
     * List of errors encountered during processing
     *
     * @var Set
     */
    public $errors = [];

    /**
     * List of prefilled-fields
     *
     * @var Set
     */
    public $prefill = [];

    /**
     * List of locked-fields
     *
     * @var Set
     */
    public $locks = [];

    /**
     * List of fields to hide
     * @var Set
     */
    public $hidden = [];

    /**
     * List of choice options for select/radio/checkbox fields
     *
     * @var Set
     */
    public $options = [];

    /**
     * Submited data
     *
     * @var Set
     */
    public $payload = [];

    /**
     * The form validator
     *
     * @var FormValidator
     */
    private $validator = null;

    /**
     * The message to display globaly, if any
     *
     * @var string
     */
    public $global_error_message = "There were some errors with your submission.<br>Please review the form and submit it again.";

    /**
     * Processing the rendering context
     */
    public function process()
    {
        $this->payload   = set($_POST);
        $this->errors    = set();
        $this->prefill   = set();
        $this->locks     = set();
        $this->hidden    = set();
        $this->options   = set();
        $this->validator = new FormValidator($this);

        // Get data that needed at any time
        $this->init();

        // Check permission
        $this->checkPermissions();

        // Get fields options
        $this->setup();

        // Get data that are needed at any time
        $this->addPrefill((array) $this->payload);

        // Extra config from hook
        do_action("syltaen_form_init", $this);

        // If data is posted, process it
        if (!$this->payload->empty()) {
            // Check for errors
            $this->validatePayload();

            // If there is none, process the data
            if ($this->errors->empty()) {
                $this->post();
            }

            if (!$this->errors->empty() && $this->global_error_message) {
                $this->controller->error($this->global_error_message);
            }

            // If eveyting is good, continue
            if ($this->errors->empty()) {
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
        $this->options->insert((array) $options);
        return $this;
    }

    /**
     * @return mixed
     */
    public function addPrefill($prefill)
    {
        $this->prefill->insert($prefill);
        return $this;
    }

    /**
     * Prepare and prefill data for upload fields
     *
     * @param  string $field The field name
     * @param  array  $value List of File or Attachment
     * @return void
     */
    public function addUploadPrefill($field, $values = [])
    {
        // Add prefilled data : attachment or basic file
        $this->prefill->set($field, array_map(function ($file) {
            return $file->getData();
        }, $values));

        // Process payload data
        if ($this->payload->get($field)) {
            $this->payload->set($field, array_map(function ($file) {
                $file = !empty($file->ID) ? new Attachment((int) $file->ID) : new File($file->file ?? $file->path);
                return $file->getData();
            }, json_decode(stripslashes($this->payload->get($field)))));
        }
    }

    /**
     * Prefill ajax select option with selected value, allows for embeded fields
     *
     * @return void
     */
    public function addAjaxSelectPrefill($field, $optionsname = false, $value = false)
    {
        $value = $this->payload->get($field) ?: $value;
        if (!$value) {return false;}

        $options = set(Hooks::getSelectOptions($optionsname ?: $field))->index("id");
        $prefill = [];

        foreach ((array) $value as $val) {
            if (isset($options[$val]["selection"])) {
                $prefill[$val] = $options[$val]["selection"];
            }
        }

        $this->options->set($field, $prefill);
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
        $this->locks->insert($locks);

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
        $this->hidden->insert($hidden_keys);
        return $this;
    }

    /**
     * Validate that the payload can be processed
     *
     * @return void
     */

    public function validatePayload()
    {
        // Should be extended by children
    }

    /**
     * Validate one or several fields
     *
     * @param string|array $fields
     * @param string|array $rules
     */
    public function validate($fields, $rules = false, $custom_error_message = false)
    {
        // Validated several fields/rules in one go
        if (is_array($fields) && !$rules) {
            foreach ($fields as $subfields => $subrules) {
                $this->validate($subfields, $subrules, $custom_error_message);
            }
            return;
        }

        $fields = is_string($fields) ? array_map("trim", explode("|", $fields)) : (array) $fields;
        $rules  = is_string($rules) ? array_map("trim", explode("|", $rules)) : (array) $rules;

        // Send each field/rule throught the FormValidator
        foreach ($fields as $field) {
            foreach ($rules as $rule) {
                $rule  = explode(":", $rule);
                $error = $this->validator->{$rule[0]}($field, $rule[1] ?? null, $custom_error_message);
                // Skip other validation for this field in case of error
                if ($error) {break;}
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
    public function init()
    {
    }
}