<?php

namespace Syltaen;

/**
 * Wrap each result of a model in a class that is used to retrieve dynamic fields defined by the model
 */

class User extends AbstractUser
{
    /**
     * Get the user's info as an HTML list
     *
     * @return string
     */
    public function getInfoHTML()
    {
        $info = set([
            "Prénom"         => $this->first_name,
            "Nom"            => $this->last_name,
            "Adresse e-mail" => $this->email,
            "Rôle(s)"        => $this->roles->column("name")->join(", "),
            "Statut"         => $this->status_label,
        ]);

        return "<ul>" . $info->mapWithKey(function ($value, $label) {
            return "<li><strong>{$label} :</strong> $value</li>";
        })->join("") . "</ul>";
    }

    /**
     * Send a mail using a template defined in the options
     *
     * @param  string  $option_key The option key
     * @param  array   $tags       A set of dynamic tags to use. Provide "to" if it's not define in the options.
     * @return boolean True if no error during the sending
     */
    public function sendMailTemplate($option_key, $tags = [])
    {
        return Mail::sendTemplate($option_key, array_merge([
            "to"           => $this->email,
            "info"         => $this->getInfoHTML(),
            "first_name"   => $this->first_name,
            "last_name"    => $this->last_name,
            "display_name" => $this->display_name,
        ], $tags));
    }
}