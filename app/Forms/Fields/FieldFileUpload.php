<?php

namespace Syltaen;

class FieldFileUpload extends \NF_Abstracts_Input
{
    /**
     * @var string
     */
    protected $_name = "fieldfileupload";

    /**
     * @var string
     */
    protected $_section = "userinfo";

    /**
     * @var string
     */
    protected $_type = "file";

    /**
     * @var string
     */
    protected $_icon = "paperclip";

    /**
     * @var string
     */
    protected $_templates = "upload";

    public function __construct()
    {
        parent::__construct();

        $this->_nicename = __("Upload", "ninja-forms");

        // ========== BASE ========== //

        $this->_settings["tooltip"] = [
            "name"           => "tooltip",
            "label"          => __("Texte d'information", "ninja-forms"),
            "type"           => "textbox",
            "width"          => "full",
            "group"          => "primary",
            "value"          => "Fichier(s)",
            "use_merge_tags" => true,
        ];

        $this->_settings["filetypes"] = [
            "name"  => "filetypes",
            "type"  => "textbox",
            "label" => "Types de fichiers autorisés",
            "width" => "full",
            "group" => "primary",
            "value" => ".jpg, .jpeg, .png, .gif",
            "help"  => "Une liste d\'extensions de fichiers, séparées par des virgules.",
        ];

        $this->_settings["maxupload"] = [
            "name"  => "maxupload",
            "type"  => "number",
            "label" => __("Taille maximum authorisée", "sytlaen"),
            "width" => "full",
            "group" => "primary",
            "value" => "2",
            "help"  => "Taille en Mo. Ne peut pas dépasser la limite imposée par le serveur : " . ini_get("upload_max_filesize"),
        ];

        $this->_settings["limit"] = [
            "name"  => "limit",
            "type"  => "number",
            "label" => __("Nombre de fichiers max. autorisés", "sytlaen"),
            "width" => "full",
            "group" => "primary",
            "value" => "1",
            "help"  => "Renseigner un nombre très grand pour ne pas mettre de limite",
        ];

        // ========== ADVANCED ========== //

        $this->_settings["return"] = [
            "name"    => "return",
            "label"   => "Données sauvées",
            "type"    => "select",
            "options" => [
                [
                    "label" => "URLs des fichiers",
                    "value" => "url",
                ],
                [
                    "label" => "Toutes les données (JSON)",
                    "value" => "all",
                ],
            ],
            "group"   => "advanced",
            "value"   => "url",
            "width"   => "one-half",
        ];

        $this->_settings["create_attachments"] = [
            "name"        => "create_attachments",
            "type"        => "toggle",
            "group"       => "advanced",
            "label"       => "Ajouter les éléments dans la bibliothèque de médias",
            "placeholder" => "",
            "value"       => "",
            "width"       => "full",
            "help"        => "Ne cocher que si c'est nécéssaire",
        ];

        $this->_settings["custom_folder"] = [
            "name"        => "custom_folder",
            "type"        => "textbox",
            "group"       => "advanced",
            "label"       => "Dossier personnalisé",
            "placeholder" => "Non",
            "value"       => "",
            "width"       => "full",
            "help"        => "Permet de séparer ces fichiers dans un dossier à part se trouvant dans " . site_url("wp-content/uploads/"),
        ];

    }
}