<?php

namespace Syltaen\App\Services;
use Syltaen\Controllers\Controller;

class Pagination
{

    private $posts;
    private $page;
    private $totalPages;
    private $queryString;


    /**
     * Generate the pagination and update the model to query for posts on the current page number
     *
     * @param Syltaen\Models\Posts $model The model used to generate the pagination
     * @param int $per_page The number of posts to display on a page
     */
    function __construct(&$model, $per_page)
    {
        $this->page        = $this->getPage();
        $this->posts       = $model->get($per_page, $this->page);
        $this->totalPages  = $model->getQuery()->max_num_pages;

        $this->querystring = $_SERVER['QUERY_STRING'] ? "?".$_SERVER['QUERY_STRING'] : "";
    }

    /**
     * Get the full link to a page
     *
     * @param int $page Page number
     * @return string Full link to the page
     */
    public function getLink($page)
    {
        if ($this->isDisabled($page)) return "";
        $page = $page == 1 ? "" : $page."/";
        return get_the_permalink() . $page . $this->querystring;
    }

    /**
     * Check if the page exists
     *
     * @param int $page Page number
     * @return ss
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
     * @param boolean $id Id to add to the navigation
     * @param boolean $class Class to add to the navigation
     * @param int $pages_span Number of pages to display in the navigation
     * @param bool $hide_alone Return an empty string if the walker only has one page
     * @return HTML
     */
    public function walker($id = false, $class = false, $pages_span = 3, $hide_alone = true)
    {

        if ($hide_alone && $this->totalPages <= 1) return "";

        $walker = [
            "id"       => $id,
            "classes"  => $class,
            "first"  => [
                "url"      => $this->getLink(1),
                "disabled" => $this->isDisabled(1),
                "title"    => __("Go to first page", "syltaen")
            ],
            "previous" => [
                "url"      => $this->getLink($this->page - 1),
                "disabled" => $this->isDisabled($this->page - 1),
                "title"    => __("Go to previous page", "syltaen")
            ],
            "next"  => [
                "url"      => $this->getLink($this->page + 1),
                "disabled" => $this->isDisabled($this->page + 1),
                "title"    => __("Go to next page", "syltaen")
            ],
            "last"   => [
                "url"      => $this->getLink($this->totalPages),
                "disabled" => $this->isDisabled($this->totalPages),
                "title"    => __("Go to last page", "syltaen")
            ],
            "pages"  => []
        ];

        // prevent a span above the max number of pages
        $pages_span = $pages_span > $this->totalPages ? $this->totalPages : $pages_span;
        // define the page to start on to always have (int $pages_span) pages displayed
        $i = ceil(($pages_span - 1) / 2 * -1);
        while ($this->page + $i <= 0) $i++;
        while ($this->page + ($pages_span - 2 + $i) >= $this->totalPages) $i--;

        for (; $pages_span > 0; $i++, $pages_span--) {
            $walker["pages"][] = [
                "url"     => $this->getLink($this->page + $i),
                "current" => $i == 0,
                "number"  => $this->format($this->page + $i)
            ];
        }

        return (new Controller)->view("parts/_pagination-walker", $walker);

    }

    /**
     * Retrive
     *
     * @return void
     */
    public function posts()
    {
        return $this->posts;
    }

    public static function getPage()
    {
        $page = get_query_var("page");
        $page = $page == 0 ? 1 : $page;
        return $page;
    }
}

/*<nav class="pagination-walker <?= $class;?>" <?= $id?"id='$id'":"";?>>

    <a href="<?= $this->getLink(0); ?>" class="pagination-walker__direction pagination-walker__direction--first <?= $this->page<=1?'disabled':''; ?>" title="Page précédente">Page précédente</a>
    <a href="<?= $this->getLink($this->page-1); ?>" class="pagination-walker__direction pagination-walker__direction--previous <?= $this->page<=1?'disabled':''; ?>" title="Page précédente">Page précédente</a>

    <div class="pagination-walker__pagelistwrapper gr-8 gr-12-xs">
        <ul class="pagination-walker__pagelist">

            <?php for($p = 1; $p <= $this->totalPages; $p++): ?>
                <li <?= $p==$this->page?"class='page'":""; ?>>
                    <a href="<?= $this->getLink($p); ?>"><?= $this->format($p); ?></a>
                </li>
            <?php endfor; ?>

        </ul>
    </div>

    <a href="<?= $this->getLink($this->page+1); ?>" class="pagination-walker__direction pagination-walker__direction--next <?= $this->page>=$this->totalPages?'disabled':''; ?>" title="Page suivante">Page suivante</a>
    <a href="<?= $this->getLink(0); ?>" class="pagination-walker__direction pagination-walker__direction--last <?= $this->page<=1?'disabled':''; ?>" title="Page précédente">Page précédente</a>
</nav>*/
