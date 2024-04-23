<?php

namespace Syltaen;

class ArchiveProcessor extends DataProcessor
{
    /**
     * Archive of news
     *
     * @return void
     */
    public function news()
    {
        $this->model = new News;

        // Add filters
        $this->filter()
            ->addTaxonomy(new NewsTaxonomy)
            ->addSelect("ordre", __("Trier par", "syltaen"), array_merge(
                ["date" => __("Chronologique", "syltaen"), "title" => __("Alphabétique", "syltaen")],
            ), "order", "date")
            ->addSearch();

        // Add pagination
        $this->paginate(6);
    }

    // =============================================================================
    // > TOOLS
    // =============================================================================
    /**
     * Create a pagination from a model
     *
     * @param  array          $c          Local context
     * @param  \Syltaen\Model $model
     * @param  int            $perpage
     * @return void
     */
    public function paginate($perpage = 6, $anchor = "")
    {
        $pagination           = new Pagination($this->model, $perpage);
        $this->data["walker"] = $pagination->walker($anchor ?: (!empty($this->content) ? $this->content->getAnchor() : ""), "pagination--simple")->data;
        $this->data["posts"]  = $pagination->posts();
    }

    /**
     * Create a filter form for this controller's model
     *
     * @return Filters
     */
    public function filter()
    {
        $this->data["filters"] = new Filters([], $this);
        return $this->data["filters"];
    }

    /**
     * Set a reference to the content layout processor
     *
     * @return self
     */
    public function setContent($content)
    {
        $this->content = $content;
        return $this;
    }
}
