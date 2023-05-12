<?php

namespace Syltaen;

abstract class LayoutProcessor extends DataProcessor
{
    /**
     * The type of layout
     *
     * @var string
     */
    public $type = "layout";

    /**
     * The layout background color
     *
     * @var string|bool
     */
    private $background = false;

    /**
     * The layout custom background color
     *
     * @var string|bool
     */
    private $customBackground = false;

    /**
     * The layout text color
     *
     * @var string|bool
     */
    private $color = false;

    /**
     * The layout paddings and margins
     *
     * @var array
     */
    private $spacings = [];

    /**
     * Initialization
     *
     * @param array|Set     $data
     * @param DataProcessor $parent
     * @param int           $index
     */
    public function __construct($data, $parent, $index = null)
    {
        $this->controller    = is_a($parent, Controller::class) || is_subclass_of($parent, Controller::class) ? $parent : $parent->controller;
        $this->index         = $index;
        $this->data          = set($data);
        $this->data["attrs"] = [];

        if (!is_subclass_of($parent, Controller::class)) {
            $this->setParent($parent);
        }
    }

    /**
     * Process the layout
     *
     * @return self
     */
    public function process()
    {
        return $this;
    }

    /**
     * Process the data and return it
     *
     * @param  boolean $key
     * @return array
     */
    public function getData($key = false)
    {
        $this->process()->storeAttributes();
        return parent::getData($key);
    }

    /**
     * Compute attributes with their final values
     *
     * @return self
     */
    public function storeAttributes()
    {
        // Background
        if ($this->background) {
            $this->addClass("bg-{$this->background}");
        }

        if ($this->customBackground) {
            $this->addStyle("background-color", $this->customBackground);
        }

        // Text color
        if ($this->color) {
            $this->addClass("color-{$this->color}");
        }

        // Spacings
        foreach ($this->spacings as $breakpoint => $spacings) {
            foreach ($spacings as $spacing => $value) {
                if ($value) {
                    $this->addClass("{$value}-{$spacing}" . ($breakpoint ? "-{$breakpoint}" : ""));
                }
            }
        }

        return $this;
    }

    // =============================================================================
    // > SETTERS
    // =============================================================================
    /**
     * Set the background attributes for this layout
     *
     * @param  string $class
     * @return void
     */
    public function setBackground($color, $custom = false)
    {
        $this->background       = empty($color) || in_array($color, ["none", "unset"]) ? false : $color;
        $this->customBackground = $color == "custom" ? $custom : false;
    }

    /**
     * Set the text color class for this layout
     *
     * @param  string $class
     * @return void
     */
    public function setTextColor($color)
    {
        $this->color = empty($color) || in_array($color, ["none", "unset"]) ? false : $color;
    }

    /**
     * Set the text color class for this layout
     *
     * @param  string $spacing    margin-top, padding-bottom...
     * @param  string $value      spacing slug
     * @param  string $breakpoint The targeted breakpoint
     * @return void
     */
    public function setSpacing($spacing, $value, $breakpoint = "")
    {
        $this->spacings[$breakpoint][$spacing] = empty($value) || in_array($value, ["none", "unset"]) ? false : $value;
    }

    // =============================================================================
    // > GETTERS
    // =============================================================================
    /**
     * Get the closest background defined
     *
     * @return void
     */
    public function getBackground()
    {
        if (!empty($this->background)) {
            return $this->background;
        }

        if ($this->parent) {
            return $this->parent->getBackground();
        }

        return false;
    }

    /**
     * Get the anchor hash if an ID is defined
     *
     * @return void
     */
    public function getAnchor()
    {
        if (!empty($this->data["attrs"]["id"])) {
            return "#" . $this->data["attrs"]["id"];
        }

        if ($this->parent) {
            return $this->parent->getAnchor();
        }

        return "";
    }

    // =============================================================================
    // > ATTRIBUTES MANIPULATION
    // =============================================================================
    /**
     * Add a class to this content
     *
     * @param  string $class
     * @return void
     */
    public function addClass($class)
    {
        $this->addAttribute("class", $class);
    }

    /**
     * Add several classes to this content
     *
     * @param  array  $classes
     * @return void
     */
    public function addClasses($classes)
    {
        $this->addAttribute("class", implode(" ", $classes));
    }

    /**
     * Add a custom style property to this content
     *
     * @param  string $name
     * @param  string $value
     * @return void
     */
    public function addStyle($name, $value)
    {
        $this->addAttribute("style", "{$name}: {$value};");
    }

    /**
     * Set a specific attribute, erasing any existing value
     *
     * @param  string $name
     * @param  string $value
     * @return void
     */
    public function setAttribute($name, $value)
    {
        $this->data["attrs"][$name] = $value;
    }

    /**
     * Set all attributes
     *
     * @param  array  $attrs
     * @return void
     */
    public function setAttributes($attrs)
    {
        $this->data["attrs"] = $attrs;
    }

    /**
     * Set a specific attribute, erasing any existing value
     *
     * @param  string $name
     * @param  string $value
     * @return void
     */
    public function addAttribute($name, $value)
    {
        $this->data["attrs"][$name] = $this->data["attrs"][$name] ?? "";
        $this->data["attrs"][$name] .= " {$value}";
        $this->data["attrs"][$name] = trim($this->data["attrs"][$name]);
    }

    // =============================================================================
    // > PARENTS MANIPULATION
    // =============================================================================
    /**
     * @var mixed
     */
    public $parent = false;

    /**
     * @var mixed
     */
    private $section = false;

    /**
     * @var mixed
     */
    private $row = false;

    /**
     * @var mixed
     */
    private $column = false;

    /**
     * @return mixed
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * @return mixed
     */
    public function getSection()
    {
        return $this->section;
    }

    /**
     * @return mixed
     */
    public function getRow()
    {
        return $this->row;
    }

    /**
     * @return mixed
     */
    public function getColumn()
    {
        return $this->column;
    }

    /**
     * Set the direct parent + a reference to each layout processor above this one
     *
     * @param  LayoutProcessor $parent
     * @return void
     */
    public function setParent($parent)
    {
        $this->parent = $parent;

        if (!empty($parent->type)) {
            $this->{("set" . ucfirst($parent->type))}($parent);
        }

        return $this;
    }

    /**
     * Specify the current section for each sub-layout items
     *
     * @param  SectionProcessor $section
     * @return void
     */
    public function setSection($section)
    {
        $this->section = $section;

        return $this;
    }

    /**
     * Specify the current row for each sub-layout items
     *
     * @param  RowProcessor $row
     * @return void
     */
    public function setRow($row)
    {
        $this->row = $row;
        $this->setSection($row->parent);
        return $this;
    }

    /**
     * Specify the current column for each sub-layout items
     *
     * @param  ColumnProcessor $column
     * @return void
     */
    public function setColumn($column)
    {
        $this->column = $column;
        $this->setRow($column->row);
        return $this;
    }
}
