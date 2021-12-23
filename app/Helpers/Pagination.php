<?php

namespace Syltaen;

class Pagination
{
    /**
     * The model to paginate
     *
     * @var \Syltaen\Model
     */
    public $model;

    /**
     * The current page number
     *
     * @var int
     */
    public $page;

    /**
     * Number of element per page
     *
     * @var int
     */
    public $perPage;

    /**
     * The maximum number of pages
     *
     * @var int
     */
    public $totalPages;

    /**
     * Data used for the rendering of the (walker)
     *
     * @var array
     */
    public $data = [];

    /**
     * Any query string found in the url
     *
     * @var string
     */
    private $queryString;

    /**
     * Generate the pagination and update the model to query for posts on the current page number
     *
     * @param Syltaen\Posts $model    The model used to generate the pagination
     * @param int           $per_page The number of posts to display on a page
     */
    public function __construct($model, $per_page, $force_page = false)
    {
        $this->model = $model;

        $this->setPage(
            $force_page ? $force_page : $this->getPage(),
            $per_page
        );

        $this->querystring = $_SERVER["QUERY_STRING"] ? "?" . $_SERVER["QUERY_STRING"] : "";
    }

    // ==================================================
    // > TOOLS
    // ==================================================
    /**
     * Check if the page exists
     *
     * @param  int    $page Page number
     * @return bool
     */
    public function isDisabled($page)
    {
        if ($page < 1) {
            return true;
        }

        if ($page > $this->totalPages) {
            return true;
        }

        if ($page == $this->page) {
            return true;
        }

        return false;
    }

    /**
     * Format the displayed number
     *
     * @param  int    $page
     * @return void
     */
    public static function format($page)
    {
        return $page < 10 ? "0" . $page : $page;
    }

    /**
     * Generate Walker
     *
     * @param  string  $anchor     ID to append to each page link
     * @param  boolean $class      Class to add to the navigation
     * @param  int     $pages_span Number of pages to display in the navigation
     * @param  string  $view       The view template to use
     * @return HTML
     */
    public function walker($anchor = "", $class = false, $pages_span = 7)
    {
        $this->setPositionLabel(
            sprintf(
                __("%s-%s de %s articles", "syltaen"),
                ($this->page - 1) * $this->perPage + 1,
                $this->page * $this->perPage > $this->totalPosts ? ($this->page - 1) * $this->perPage + ($this->totalPosts % $this->perPage) : $this->page * $this->perPage,
                $this->totalPosts
            )
        );

        $this->data["classes"] = $class;
        $this->data["walker"]  = $this->getWalkerData($anchor, $pages_span);

        return $this;
    }

    /**
     * Render the walker
     *
     * @return void
     */
    public function render()
    {
        return View::parsePug(
            "include " . "/views/includes/filters/_pagination.pug\n" .
            '+pagination($walker)'
            , [
                "walker" => $this->data,
            ]);
    }

    // ==================================================
    // > GETTERS
    // ==================================================
    /**
     * Generate Walker data
     *
     * @param  string  $anchor     ID to append to each page link
     * @param  int     $pages_span Number of pages to display in the navigation
     * @return array
     */
    public function getWalkerData($anchor = "", $pages_span = 3)
    {
        if ($this->totalPages <= 1) {
            return false;
        }

        $walker = [
            "previous" => [
                "url"      => $this->getLink($this->page - 1, $anchor),
                "number"   => $this->page - 1,
                "disabled" => $this->isDisabled($this->page - 1),
                "title"    => __("Page précédente", "syltaen"),
            ],
            "next"     => [
                "url"      => $this->getLink($this->page + 1, $anchor),
                "number"   => $this->page + 1,
                "disabled" => $this->isDisabled($this->page + 1),
                "title"    => __("Page suivante", "syltaen"),
            ],
            "pages"    => [],
        ];

        // Prevent a span above the max number of pages
        $pages_span = $pages_span > $this->totalPages ? $this->totalPages : $pages_span;

        // Define the page to start on to always have (int $pages_span) pages displayed
        $i = ceil(($pages_span - 1) / 2 * -1);
        while ($this->page + $i <= 0) {
            $i++;
        }

        while ($this->page + ($pages_span - 2 + $i) >= $this->totalPages) {
            $i--;
        }

        for (; $pages_span > 0; $i++, $pages_span--) {
            $walker["pages"][$this->page + $i] = [
                "url"     => $this->getLink($this->page + $i, $anchor),
                "current" => $i == 0,
                "number"  => $this->page + $i,
                "text"    => $this->page + $i,
            ];
        }

        // Add in-betweens
        if (empty($walker["pages"][$this->totalPages]) && empty($walker["pages"][$this->totalPages - 1])) {
            $last_av               = array_keys($walker["pages"])[count($walker["pages"]) - 1];
            $num                   = ceil($last_av + ($this->totalPages - $last_av) / 2);
            $walker["pages"][$num] = [
                "url"     => $this->getLink($num, $anchor),
                "current" => false,
                "number"  => $num,
                "text"    => "...",
            ];
        }

        if (empty($walker["pages"][1]) && empty($walker["pages"][2])) {
            $num                   = floor(1 + (array_keys($walker["pages"])[0] - 1) / 2);
            $walker["pages"][$num] = [
                "url"     => $this->getLink($num, $anchor),
                "current" => false,
                "number"  => $num,
                "text"    => "...",
            ];
        }

        // Always add first page
        if (empty($walker["pages"][1])) {
            $walker["pages"][1] = [
                "url"     => $this->getLink(1, $anchor),
                "current" => false,
                "number"  => 1,
                "text"    => "1",
            ];
        }

        // Always add last page
        if (empty($walker["pages"][$this->totalPages])) {
            $walker["pages"][$this->totalPages] = [
                "url"     => $this->getLink($this->totalPages, $anchor),
                "current" => false,
                "number"  => $this->totalPages,
                "text"    => "$this->totalPages",
            ];
        }

        ksort($walker["pages"]);

        return $walker;
    }

    /**
     * Retrive the list of posts for the current page
     *
     * @return array
     */
    public function posts()
    {
        return $this->model->get();
    }

    /**
     * Get the current page number
     *
     * @return int
     */
    public static function getPage()
    {
        $page = get_query_var("paged");
        $page = $page == 0 ? 1 : $page;
        return $page;
    }

    /**
     * Get the full link to a page
     *
     * @param  int    $page Page number
     * @return string Full link to the page
     */
    public function getLink($page, $anchor = "")
    {
        if ($this->isDisabled($page)) {
            return "";
        }

        // Clean stem
        $url = static::getBaseURL();

        // Add page
        $url .= $page == 1 ? "/" : "/$page/";

        // Add querystring and anchor
        $url .= $this->querystring . $anchor;

        return $url;
    }

    /**
     * Get the current URL without pagination or parameters
     *
     * @return string
     */
    public static function getBaseURL()
    {
        $url = Route::getFullUrl([], false);
        $url = preg_replace("/\/$/", "", $url);
        $url = preg_replace("/\/[0-9]*$/", "", $url);
        return $url;
    }

    // ==================================================
    // > SETTERS
    // ==================================================
    /**
     * Set the current page number
     *
     * @param  int    $page
     * @param  int    $per_page
     * @return self
     */
    public function setPage($page, $per_page = false)
    {
        if ($per_page) {
            $this->perPage = $per_page;
        }

        $this->page = $page;

        $this->model->limit($this->perPage)->page($this->page);

        $this->totalPages = $this->model->getPagesCount();
        $this->totalPosts = $this->model->count(false);

        return $this;
    }

    /**
     * Set options to change the order
     *
     * @return self
     */
    public function setOrderOptions($options, $current = false, $default = false)
    {
        $this->data["order_options"] = $options;
        $this->data["order_value"]   = $current;
        $this->data["order_default"] = $default;
        return $this;
    }

    /**
     * Set options to change the limit
     *
     * @return self
     */
    public function setLimitOptions($options, $current = false, $default = false)
    {
        $this->data["limit_options"] = $options;
        $this->data["limit_value"]   = $current;
        $this->data["limit_default"] = $default;
        return $this;
    }

    /**
     * Set the rendeing context
     *
     * @param  array  $data
     * @return self
     */
    public function setData($data)
    {
        $this->data = $data;
        return $this;
    }

    /**
     * Set the position label
     *
     * @return self
     */
    public function setPositionLabel($position_label)
    {
        $this->data["position_label"] = $position_label;
        return $this;
    }

    /**
     * Set the filters that should be submitted when an option change
     *
     * @return self
     */
    public function setFiltersToKeep($filters)
    {
        $this->data["filters_to_keep"] = [];

        foreach ($filters as $filter) {
            if (isset($_GET[$filter])) {
                $this->data["filters_to_keep"][$filter] = $_GET[$filter];
            }
        }

        return $this;
    }
}
