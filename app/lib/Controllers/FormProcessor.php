<?php

namespace Syltaen;

abstract class FormProcessor extends DataProcessor
{
    /**
     * Fields that will be auto-validated
     */
    public $required_fields = [];

    /**
     * Processing the rendering context
     */
    public function process($data = [])
    {
        $this->data    = $data;
        $this->errors  = [];
        $this->prefill = [];
        $this->payload = $_POST;

        // Check permission
        $this->checkPermissions();

        // Get data that needed at any time
        $this->fetchRequirements();

        // Get data that are needed at any time
        $this->prefill();

        // If data is posted, process it
        if (!empty($this->payload)) {

            // Check for errors
            $this->validatePayload();

            // If there is none, process the data
            if (empty($this->errors)) $this->post();

            if (!empty($this->errors)) {
                $this->controller->error(__("Veuillez corriger les erreurs dans le formulaire et le soumettre à nouveau.", "syltaen"));
            }
        }

        // Else, fallback to get (display the form)
        $this->data["prefill"] = $this->prefill;
        $this->data["errors"]  = $this->errors;
        $this->get();

        return $this->data;
    }

    // =============================================================================
    // > GET / DISPLAYING FORM
    // =============================================================================
    /**
     * Process get requests
     *
     * @return void
     */
    public function get()
    {

    }


    /**
     * Get the data used to prefill each field
     *
     * @return void
     */
    public function prefill()
    {
        $this->prefill = array_merge(
            $this->prefill,
            $this->payload
        );
    }


    /**
     * Fetch data that is required for GET and POST
     *
     * @return void
     */
    public function  fetchRequirements()
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


    // =============================================================================
    // > VALIDATION OF PAYLOAD
    // =============================================================================
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
    public function validateRequired($fields)
    {
        foreach ($fields as $req) {
            if (empty($this->payload[$req])) {
                $this->errors[$req] = __("Ce champs est requis", "syltaen");
            }
        }
    }


    // =============================================================================
    // > POST / SAVING DATA
    // =============================================================================
    /**
     * Process submitted data
     *
     * @return void
     */
    public function post()
    {

    }

}