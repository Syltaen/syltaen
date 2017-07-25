<?php

namespace Syltaen;

class FieldFileUpload extends \NF_Abstracts_Input
{

    protected $_name      = "fieldfileupload";

    protected $_section   = "userinfo";

    protected $_type      = "hidden";

    protected $_icon      = "paperclip";

    protected $_templates = "dropzone";


    public function __construct()
    {
        parent::__construct();

        $this->_nicename = __("Upload", "ninja-forms");

        $this->_settings["filetypes"] = [
            "name"  => "filetypes",
            "type"  => "textbox",
            "label" => __("Types de fichiers authorisés", "syltaen"),
            "width" => "full",
            "group" => "restrictions",
            "value" => ".jpg, .jpeg, .png, .gif",
            "help"  => __("Une liste d\'extensions de fichiers, séparées par des virgules.", "syltaen"),
        ];

        $this->_settings["maxupload"] = [
            "name" => "maxupload",
            "type" => "number",
            "label" => __("Taille maximum authorisée", "sytlaen"),
            "width" => "full",
            "group" => "restrictions",
            "value" => "2",
            "help" => __("Taille en Mo. Ne peut pas dépasser la limite imposée par le serveur : ".ini_get("upload_max_filesize"), "syltaen"),
        ];
    }
}