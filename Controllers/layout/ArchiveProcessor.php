<?php

namespace Syltaen;

class ArchiveProcessor extends DataProcessor
{
    /**
     * Défis - Liste complète avec filtres
     *
     * @return void
     */
    public function news()
    {
        $model = new News;

        // Add filters
        $this->filter($model)
        ->addSelectTaxonomy(new NewsTaxonomy)
        ->addSelect("ordre", __("Trier par", "syltaen"), array_merge(
            ["date" => __("Chronologique", "syltaen"), "title" => __("Alphabétique", "syltaen")],
        ), "order", "date")
        ->addSearch();

        // Add pagination
        $this->paginate($model, 6, $this->content->getAnchor());
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
    public function paginate($model, $perpage = 6, $anchor = "")
    {
        $pagination           = new Pagination($model, $perpage);
        $this->data["walker"] = $pagination->walker($anchor, "pagination--simple")->data;
        $this->data["posts"]  = $pagination->posts();
    }

    /**
     * Create a pagination from a model
     *
     * @param  array          $c          Local context
     * @param  \Syltaen\Model $model
     * @param  int            $perpage
     * @return Filters
     */
    public function filter($model)
    {
        $filters               = new Filters($model, $this->content);
        $this->data["filters"] = &$filters->data;
        return $filters;
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