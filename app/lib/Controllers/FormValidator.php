<?php

namespace Syltaen;

use AllowDynamicProperties;

/**
 * Form validation rules
 * Loosly based on https://laravel.com/docs/9.x/validation
 */
#[AllowDynamicProperties]//;
class FormValidator
{
    /**
     * Required field
     */
    private function _required()
    {
        if (!$this->value && $this->value !== "0" && $this->value !== 0) {
            return "This field is required.";
        }
    }

    /**
     * Required field if another field has a specific value
     */
    private function _required_if($params)
    {
        $params = static::multipleParameters($params);

        $value  = (array) $this->form->payload->get($params[0]);
        $target = array_slice($params, 1);

        $other_field_match = empty($target) ? $value : array_intersect($value, $target);

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
    private function _new_email($exception)
    {
        // Allow to skip this check if the user is editing his own profile
        if ($exception == "self" && wp_get_current_user()->user_email == $this->value) {
            return;
        }

        // Allow an exception
        if (!empty($exception) && $exception == $this->value) {
            return;
        }

        if (email_exists($this->value)) {
            return "This e-mail address is already used.";
        }
    }

    /**
     * Is a valid login (username or email address)
     */
    private function _login_exists()
    {
        if (!get_user_by("email", trim(wp_unslash($this->value))) && !get_user_by("login", trim($this->value))) {
            return "There is no account with that username or email address.";
        }
    }

    /**
     * Same value than another field
     */
    private function _same_as($field)
    {
        if ($this->value != $this->form->payload->get($field)) {
            return [
                $this->field => "The values must be the same.",
                $field       => "The values must be the same.",
            ];
        }
    }

    /**
     * Greater than
     */
    private function _gt($num)
    {
        $num = $this->getNumericalValue($num);
        if ($this->value <= $num) {
            return "This value must be greater than {$num}.";
        }
    }

    /**
     * Greater than or equal to
     */
    private function _gte($num)
    {
        $num = $this->getNumericalValue($num);
        if ($this->value < $num) {
            return "This value must be greater than or equal to {$num}.";
        }
    }

    /**
     * Less than
     */
    private function _lt($num)
    {
        $num = $this->getNumericalValue($num);
        if ($this->value >= $num) {
            return "This value must be less than {$num}.";
        }
    }

    /**
     * Less than or equal to
     */
    private function _lte($num)
    {
        $num = $this->getNumericalValue($num);
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
    private function _in_options()
    {
        $options = set($this->form->options->get($this->field));

        foreach ((array) $this->value as $value) {
            if (!$options->hasKey($value, true)) {
                return "This value is not allowed.";
            }
        }
    }

    /**
     * Value must be all the available options
     */
    private function _all_options()
    {
        $values    = (array) $this->value;
        $options   = $this->form->options->get($this->field);
        $mandatory = !array_keys($options)[0] ? array_values($options) : array_keys($options);

        if (!empty(array_diff($mandatory, $values))) {
            return "You must select all options.";
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

    /**
     * Value is not already used as a post meta
     */
    private function _unique_post_meta($params)
    {
        $params    = static::multipleParameters($params);
        $post_type = $params[0] ?? "post";
        $meta_key  = $params[1] ?? $this->field;

        if ((new Posts)->addFilters(["post_type" => $post_type])->meta($meta_key, $this->value)->found()) {
            return "This value is already used.";
        }
    }

    /**
     * Validate a group of address fields
     */
    private function _address()
    {
        $subfields = ["street", "zip", "city", "country"];
        $errors    = [];

        foreach ($subfields as $subfield) {
            if (empty($this->value[$subfield])) {
                $errors[$this->field . "." . $subfield] = "This field is required.";
            }
        }

        return $errors;
    }

    /**
     * Validate a readable date
     */
    private function _date()
    {
        if (!strtotime($this->value)) {
            return "This date is not valid.";
        }
    }

    /**
     * Validate a recaptcha
     */
    private function _recaptcha()
    {
        $res = (new Request("https://www.google.com/recaptcha/api/siteverify"))->post([
            "secret"   => config("recaptcha.secret_key"),
            "response" => $this->value,
        ]);

        if (empty($res->body["success"])) {
            return __("Please confirm you are not a robot.", "syltaen");
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
            throw new \Exception ("Validation method does not exist : _$method");
        }
        $error = $this->{"_" . $method}($arguments[1] ?? null);
        $error = $error ? (!empty($arguments[2]) ? $arguments[2] : $error) : false;

        // If has error, add it to the form errors (allow custom error message as third parameter)
        if ($error) {
            if (is_array($error)) {
                foreach ($error as $field => $message) {
                    $this->form->errors->set($field, $message);
                }
            } else {
                $this->form->errors->set($this->field, $error);
            }
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

    /**
     * Return the numerical value of a string
     *
     * @param  [type] $num
     * @return void
     */
    public function getNumericalValue($num)
    {
        return is_numeric($num) ? $num : (
            $this->form->payload->get($num) ?: (float) $num
        );
    }
}
