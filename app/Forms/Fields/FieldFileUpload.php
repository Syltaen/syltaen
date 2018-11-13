<?php

namespace Syltaen;

class FieldFileUpload extends \NF_Abstracts_Input
{

    protected $_name      = "fieldfileupload";

    protected $_section   = "userinfo";

    protected $_type      = "file";

    protected $_icon      = "paperclip";

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
            "use_merge_tags" => true
        ];

        $this->_settings["filetypes"] = [
            "name"  => "filetypes",
            "type"  => "textbox",
            "label" => __("Types de fichiers authorisés", "syltaen"),
            "width" => "full",
            "group" => "primary",
            "value" => ".jpg, .jpeg, .png, .gif",
            "help"  => __("Une liste d\'extensions de fichiers, séparées par des virgules.", "syltaen"),
        ];


        $this->_settings["maxupload"] = [
            "name" => "maxupload",
            "type" => "number",
            "label" => __("Taille maximum authorisée", "sytlaen"),
            "width" => "full",
            "group" => "primary",
            "value" => "2",
            "help" => __("Taille en Mo. Ne peut pas dépasser la limite imposée par le serveur : ".ini_get("upload_max_filesize"), "syltaen"),
        ];


        $this->_settings["limit"] = [
            "name" => "limit",
            "type" => "number",
            "label" => __("Nombre de fichiers max. autorisés", "sytlaen"),
            "width" => "full",
            "group" => "primary",
            "value" => "1",
            "help" => __("Renseigner un nombre très grand pour ne pas mettre de limite", "syltaen"),
        ];



        // ========== ADVANCED ========== //

        $this->_settings["return"] = [
            "name"  => "return",
            "label" => __("Données sauvées", "syltaen"),
            "type"  => "select",
            "options" => [
                [
                    "label" => "URLs des fichiers",
                    "value" => "url"
                ],
                [
                    "label" => "Toutes les données (JSON)",
                    "value" => "all"
                ]
            ],
            "group" => "advanced",
            "value" => "url",
            "width" => "one-half",
        ];

        $this->_settings["create_attachments"] = [
            "name"           => "create_attachments",
            "type"           => "toggle",
            "group"          => "advanced",
            "label"          => "Ajouter les éléments dans la bibliothèque de médias",
            "placeholder"    => "",
            "value"          => "",
            "width"          => "full",
            "help"           => __("Ne cocher que si c'est nécéssaire", "syltaen"),
        ];

    }
}