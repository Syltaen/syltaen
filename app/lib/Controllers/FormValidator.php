<?php

namespace Syltaen;

/**
 * Form validation rules
 * Loosly based on https://laravel.com/docs/9.x/validation
 */

class FormValidator
{
    /**
     * Required field
     */
    private function _required()
    {
        if (!$this->value) {
            return "This field is required.";
        }
    }

    /**
     * Required field if another field has a specific value
     */
    private function _required_if($params)
    {
        $params = static::multipleParameters($params);
        $field  = $params[0];
        $value  = $params[1] ?? null;

        $other_field_match = !empty($value)
            ? $this->form->payload->get($field) == $value
            : $this->form->payload->get($field);

        if ($other_field_match && !$this->value) {
            return "This field is required.";
        }
    }

    /**
     * Minimum field length
     */
    private function _min($min)
    {
        if (strlen($this->value) < $min) {
            return "This field must be at least {$min} characters long.";
        }
    }

    /**
     * Maximum field length
     */
    private function _max($max)
    {
        if (strlen($this->value) > $max) {
            return "This field must be at most {$max} characters long.";
        }
    }

    /**
     * Is a valid email address
     */
    private function _email()
    {
        if (!filter_var($this->value, FILTER_VALIDATE_EMAIL)) {
            return "This e-mail address is not valid.";
        }
    }

    /**
     * Unused email address (except by the current user)
     */
    private function _new_email()
    {
        if (wp_get_current_user()->email != $this->value && email_exists($this->value)) {
            return "This e-mail address is already used.";
        }
    }

    /**
     * Same value than another field
     */
    private function _confirmed($field)
    {
        if ($this->value != $this->form->payload->get($field)) {
            return "The values must be the same.";
        }
    }

    /**
     * Greater than
     */
    private function _gt($num)
    {
        if ($this->value <= $num) {
            return "This value must be greater than {$num}.";
        }
    }

    /**
     * Greater than or equal to
     */
    private function _gte($num)
    {
        if ($this->value < $num) {
            return "This value must be greater than or equal to {$num}.";
        }
    }

    /**
     * Less than
     */
    private function _lt($num)
    {
        if ($this->value >= $num) {
            return "This value must be less than {$num}.";
        }
    }

    /**
     * Less than or equal to
     */
    private function _lte($num)
    {
        if ($this->value > $num) {
            return "This value must be less than or equal to {$num}.";
        }
    }

    /**
     * Equals
     */
    private function _equals($value)
    {
        if ($this->value != $value) {
            return "This value must be {$value}.";
        }
    }

    /**
     * Is an array of files with an url and a path
     */
    private function _files()
    {
        if (empty($this->value)) {
            return false;
        }

        if (!is_array($this->value)) {
            return "There is a problem with the file(s) you uploaded.";
        }

        foreach ($this->value as $file) {
            if (!isset($file->url) || !isset($file->path)) {
                return "There is a problem with the file(s) you uploaded.";
            }
        }
    }

    /**
     * Is an array
     */
    private function _array()
    {
        if (!is_array($this->value)) {
            return "This value is not valid.";
        }
    }

    /**
     * Value must be in a list of values
     */
    private function _in($values)
    {
        if (!in_array($this->value, static::multipleParameters($values))) {
            return "This value is not allowed.";
        }
    }

    /**
     * Value is in the field provided options
     */
    private function _options()
    {
        $options = $this->form->options->get($this->field);
        $allowed = !array_keys($options)[0] ? array_values($options) : array_keys($options);

        if (!in_array($this->value, $allowed)) {
            return "This value is not allowed.";
        }
    }

    /**
     * Value is numeric
     */
    private function _numeric()
    {
        if (!is_numeric($this->value)) {
            return "This value is not valid.";
        }
    }

    // =============================================================================
    // > INTERNAL RULE ROUTING AND TOOLS
    // =============================================================================
    /**
     * Create an new form validator
     *
     * @param FormProcessor $form
     */
    public function __construct($form)
    {
        $this->form = $form;
    }

    /**
     * @param string $method    Name of the method to use
     * @param array  $arguments field, options, error message
     */
    public function __call($method, $arguments)
    {
        // Local temporary data to be used by the validation method
        $this->field = $arguments[0];
        $this->value = $this->form->payload->get($this->field);

        // Call the validation method with custom parameters
        if (!method_exists($this, "_" . $method)) {
            throw new \Exception("Validation method does not exist : _$method");
        }
        $error = $this->{"_" . $method}($arguments[1] ?? null);
        $error = $error ? (!empty($arguments[2]) ? $arguments[2] : $error) : false;

        // If has error, add it to the form errors (allow custom error message as third parameter)
        if ($error) {
            $this->form->errors->set($this->field, $error);
        }

        return $error;
    }

    /**
     * Separate several parameters from a string
     *
     * @param  string  $parameters
     * @return array
     */
    public static function multipleParameters($parameters)
    {
        return array_filter(array_map("trim", explode(",", $parameters ?? "")));
    }
}