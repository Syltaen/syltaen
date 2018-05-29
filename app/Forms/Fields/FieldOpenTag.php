<?php

namespace Syltaen;

class FieldOpenTag extends \NF_Abstracts_Input
{
    protected $_name      = "fieldopentag";
    protected $_section   = "layout";
    protected $_icon      = "chevron-left";
    protected $_templates = "tag";

    protected $_settings_only = ["label"];

    public function __construct()
    {
        parent::__construct();
        $this->_nicename = "gr-6";
    }

}