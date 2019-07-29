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
     * A list of posts for the current page
     *
     * @var array
     */
    private $posts;

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
     * Any query string found in the url
     *
     * @var string
     */
    private $queryString;


    /**
     * Generate the pagination and update the model to query for posts on the current page number
     *
     * @param Syltaen\Posts $model The model used to generate the pagination
     * @param int $per_page The number of posts to display on a page
     */
    public function __construct($model, $per_page, $force_page = false)
    {
        $this->perPage     = $per_page;
        $this->model       = $model;

        $this->page        = $force_page ? $force_page : $this->getPage();
        $this->posts       = $model->get($this->perPage, $this->page);
        $this->totalPages  = $model->getPagesCount();

        $this->querystring = $_SERVER["QUERY_STRING"] ? "?".$_SERVER["QUERY_STRING"] : "";
    }

    /**
     * Get the full link to a page
     *
     * @param int $page Page number
     * @return string Full link to the page
     */
    public function getLink($page, $anchor = "")
    {
        if ($this->isDisabled($page)) return "";

        // Clean stem
        $url = Route::getFullUrl([], false);
        $url = preg_replace("/\/$/", "", $url);
        $url = preg_replace("/\/[0-9]*$/", "", $url);

        // Add page
        $url .= $page == 1 ? "/" : "/$page/";

        // Add querystring and anchor
        $url .= $this->querystring . $anchor;

        return $url;
    }

    /**
     * Check if the page exists
     *
     * @param int $page Page number
     * @return bool
     */
    public function isDisabled($page)
    {
        if ($page < 1) return true;
        if ($page > $this->totalPages) return true;
        if ($page == $this->page) return true;
        return false;
    }

    /**
     * Format the displayed number
     *
     * @param int $page
     * @return void
     */
    public static function format($page)
    {
        return $page < 10 ? "0".$page : $page;
    }


    /**
     * Generate Walker
     *
     * @param string $anchor ID to append to each page link
     * @param boolean $class Class to add to the navigation
     * @param int $pages_span Number of pages to display in the navigation
     * @param bool $hide_alone Return an empty string if the walker only has one page
     * @param string $view The view template to use
     * @return HTML
     */
    public function walker($anchor = "", $class = false, $pages_span = 5, $hide_alone = true, $view = "includes/_pagination-walker")
    {
        if ($hide_alone && $this->totalPages <= 1) return "";

        $walker = [
            "classes"  => $class,
            "first"  => [
                "url"      => $this->getLink(1, $anchor),
                "disabled" => $this->isDisabled(1),
                "title"    => __("Première page", "syltaen")
            ],
            "previous" => [
                "url"      => $this->getLink($this->page - 1, $anchor),
                "disabled" => $this->isDisabled($this->page - 1),
                "title"    => __("Page précédente", "syltaen")
            ],
            "next"  => [
                "url"      => $this->getLink($this->page + 1, $anchor),
                "disabled" => $this->isDisabled($this->page + 1),
                "title"    => __("Page suivante", "syltaen")
            ],
            "last"   => [
                "url"      => $this->getLink($this->totalPages, $anchor),
                "disabled" => $this->isDisabled($this->totalPages),
                "title"    => __("Dernière page", "syltaen")
            ],
            "pages"   => [],
            "text"  => sprintf(__("Page %s sur %s", "syltaen"), $this->page, $this->totalPages),
        ];

        // prevent a span above the max number of pages
        $pages_span = $pages_span > $this->totalPages ? $this->totalPages : $pages_span;
        // define the page to start on to always have (int $pages_span) pages displayed
        $i = ceil(($pages_span - 1) / 2 * -1);
        while ($this->page + $i <= 0) $i++;
        while ($this->page + ($pages_span - 2 + $i) >= $this->totalPages) $i--;

        for (; $pages_span > 0; $i++, $pages_span--) {
            $walker["pages"][] = [
                "url"     => $this->getLink($this->page + $i, $anchor),
                "current" => $i == 0,
                "number"  => $this->page + $i
            ];
        }

        return (new Controller)->view($view, $walker);

    }

    /**
     * Retrive the list of posts for the current page
     *
     * @return array
     */
    public function posts()
    {
        return $this->posts;
    }

    /**
     * Get the current page number
     *
     * @return int
     */
    public static function getPage()
    {
        $page = get_query_var("page");
        $page = $page == 0 ? 1 : $page;
        return $page;
    }

    /**
     * Set the current page number
     *
     * @param int $page
     * @param int $per_page
     * @return self
     */
    public function setPage($page, $per_page = false)
    {
        if ($per_page) $this->perPage = $per_page;

        $this->page = $page;
        $this->posts = $this->model->get($this->perPage, $this->page);

        return $this;
    }
}
