<?php

namespace Syltaen;

abstract class FormProcessor extends DataProcessor
{
    /**
     * The form method to use
     */
    const METHOD = "POST";

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
    protected $global_error_message = "There were some errors with your submission.<br>Please review the form and submit it again.";

    /**
     * Initialization
     *
     * @param boolean $controller
     */
    public function __construct($data = [], &$controller = false)
    {
        parent::__construct($data, $controller);

        $this->payload   = set(static::METHOD == "POST" ? $_POST : $_GET);
        $this->errors    = set();
        $this->prefill   = set();
        $this->options   = set();
        $this->locks     = set();
        $this->hidden    = set();
        $this->validator = new FormValidator($this);

        $GLOBALS["form_count"] = ($GLOBALS["form_count"] ?? 0) + 1;
        $this->data["form_id"] = $GLOBALS["form_count"];
    }

    // =============================================================================
    // > LIFE CYCLE
    // =============================================================================

    /**
     * Processing the rendering context
     */
    public function process()
    {
        // Get data that needed at any time
        $this->init();

        // Check permission
        $this->checkPermissions();

        // Provide prefilled data for the form
        $this->prefill();
        $this->addPrefill((array) $this->payload);

        // Setup fields : options, locks, hidden values, ...
        $this->setup();

        // Extra config from hook
        do_action("syltaen_form_init", $this);

        // If data is posted, process it
        if (!$this->payload->empty() && ($this->payload["form_id"] ?? null) == $this->data["form_id"]) {
            // Process the submited payload before anything else
            $this->processPayload();

            // Merge the payload with prefilled and locked data
            $this->payload = set(array_merge(
                (array) $this->prefill,
                (array) $this->payload,
                (array) $this->locks,
            ));

            // Check for errors
            $this->validatePayload();

            // If there is none, process the data
            if ($this->errors->empty()) {
                do_action("syltaen_form_before_post", $this);
                $this->post();
                do_action("syltaen_form_after_post", $this);
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
        if (!empty($_GET["success"]) && $_GET["success"] == $this->data["form_id"]) {
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
     * Fetch data that is required for GET and POST
     *
     * @return void
     */
    public function init()
    {
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
     * Base prefill of the form : stored value, payload, processed data...
     *
     * @return void
     */
    public function prefill()
    {
        // No prefill by default (except the payload)
    }

    /**
     * Define the different available options for each fields
     *
     * @return void
     */
    public function setup()
    {
        // No custom options by default
    }

    /**
     * Allow to transform the submited payload before anything else is done with it
     */
    public function processPayload()
    {
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
            Route::getFullUrl(["success" => $this->data["form_id"]]) . "#success-" . $this->data["form_id"]
        );
    }

    /**
     * Populate context for the success page
     *
     * @return void
     */
    public function success()
    {
        $this->data["success"] = sprintf(
            "<h2>%s</h2><p>%s</p>",
            __("Merci!", "syltaen"),
            __("Le formulaire a bien été envoyé.", "syltaen")
        );
    }

    /**
     * Process get requests
     *
     * @return void
     */
    public function get()
    {
    }

    // =============================================================================
    // > SETUP
    // =============================================================================
    /**
     * @return mixed
     */
    public function addPrefill($prefill)
    {
        $this->prefill->insert($prefill);
        return $this;
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
        $this->prefill->insert($locks);

        if (!$this->payload->empty()) {
            $this->payload->insert((array) $locks);
        }

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
     * Add new options to the list
     *
     * @param  array  $options
     * @return self
     */
    public function addOptions($fields)
    {
        $this->options->insert((array) $fields);
        return $this;
    }

    /**
     * Make an option array with values as keys and values
     *
     * @param  array   $values
     * @return array
     */
    public static function makeOptions($values)
    {
        return set($values)->mapAssoc(function ($i, $value) {
            return [$value => $value];
        });
    }

    /**
     * Get list of options from the settings
     *
     * @param  string  $field_name
     * @param  boolean $add_other
     * @param  boolean $map_callback
     * @return array
     */
    public static function getOptionsFromSettings($field_name, $add_other = false, $map_callback = false)
    {
        $options = set(Data::option("fields_{$field_name}"));

        // Apply optional callback / filter
        if ($map_callback) {
            $options = $options->map($map_callback)->filter();
        }

        // Add other
        if ($add_other) {
            $options->insert([["name" => "Other"]]);
        }

        // Transform intro array of values
        return $options->mapAssoc(function ($i, $option) {
            if (!empty($option["subs"])) {
                return [
                    $option["name"] => [$option["name"], (array) set($option["subs"])->index("value", "label")],
                ];
            }

            return [$option["name"] => $option["name"]];
        });
    }

    /**
     * Add options for each subfields of a repeater
     *
     * @param  string $field
     * @param  string $subfield
     * @param  array  $options
     * @return void
     */
    public function addOptionsRepeater($field, $subfield, $options)
    {
        foreach (($this->prefill->get($field) ?? [0]) as $i => $row) {
            $this->options->set("{$field}.$i.{$subfield}", $options);
        }
    }

    /**
     * Prefill ajax select option with selected value, allows for embeded fields
     *
     * @return void
     */
    public function addOptionsAjax($field, $optionsname = false)
    {
        $value = $this->prefill->get($field);
        if (!$value) {return false;}

        $options = set(Hooks::getSelectOptions($optionsname ?: $field, $value))->index("id");
        $prefill = [];

        foreach ((array) $value as $val) {
            if (isset($options[$val]["text"])) {
                $prefill[$val] = $options[$val]["text"];
            }
        }

        $this->options->set($field, $prefill);
    }

    /**
     * Prepare and prefill data for uploaded fields
     *
     * @param  string $field The field name
     * @return void
     */
    public function setupFiles($fields)
    {
        foreach ((array) $fields as $field) {
            $files = $this->prefill->get($field);
            $files = is_object($files) ? [$files] : (array) Text::maybeJsonDecode($files);

            // Normalize into a list of Files/Attachments
            $files = array_filter(array_map(function ($file) {
                // Already a File or Attachement
                if (is_object($file) && method_exists($file, "getData")) {
                    return $file;
                }

                if (is_object($file)) {
                    return !empty($file->ID) ? new Attachment((int) $file->ID) : new File($file->file ?? ($file->path ?? ($file->url ?? false)));
                }

                // Attachement ID
                if (is_int($file)) {
                    return new Attachment($file);
                }

                // File path or url
                if (is_string($file)) {
                    return new File($file);
                }

                return false;
            }, $files));

            // Set prefill data
            $this->prefill->set($field, array_map(function ($file) {
                return $file->getData();
            }, $files));

            // Set payload data
            if ($this->payload->get($field)) {
                $this->payload->set($field, $files);
            }
        }
    }

    /**
     * Set the default upload directory for all files
     *
     * @param  string $directory
     * @return self
     */
    public function setUploadDirectory($directory)
    {
        $this->data["upload_directory"] = $directory;
        return $this;
    }

    // =============================================================================
    // > TOOLS / ACTIONS
    // =============================================================================
    /**
     * FIll the payload with data form the prefill to trigger submit actions
     *
     * @return void
     */
    public function submit()
    {
        $this->payload = $this->prefill;
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
}
