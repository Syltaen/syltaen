<?php

namespace Syltaen;

class MergeTagsUser extends \NF_Abstracts_MergeTags
{

    protected $id = "website_user";

    protected $tags = [
        "id"               => "ID",
        "firstname"        => "Prénom",
        "lastname"         => "Nom",
        "roles"            => "Rôles",
    ];

    public function __construct()
    {
        parent::__construct();

        $this->title = __("Utilisateur site", "syltaen");

        $this->merge_tags = [];
        foreach ($this->tags as $tag=>$label) {
            $this->merge_tags[$tag] = [
                "id"       => $tag,
                "callback" => $tag,
                "tag"      => "{{$this->id}:$tag}",
                "label"    => $label
            ];
        }

        add_action("init", [$this, "init"]);
        add_action("admin_init", [$this, "admin_init"]);
    }

    public function init() {
        $this->user = Data::globals("user");

        if (!$this->user) return "";

        // $this->user->fields([
        // ], true);

        if ($this->user && $this->user->limit(1)->can("edit_users") && Route::query("user")) {
            $this->user->is(Route::query("user"));
        }

        $this->userData = $this->user ? $this->user->getOne() : "";

        return $this;
    }

    public function admin_init() { /* This section intentionally left blank. */ }

    // ==================================================
    // > CALLBACKS
    // ==================================================
    public function id()
    {
        if (!$this->userData) return "";
        return $this->userData->ID;
    }

    public function firstname()
    {
        if (!$this->userData) return "";
        return $this->userData->first_name;
    }

    public function lastname()
    {
        if (!$this->userData) return "";
        return $this->userData->last_name;
    }

    public function roles()
    {
        if (!$this->userData) return "";
        $caps_list = "";
        foreach ($this->userData->caps as $cap=>$has) {
            if ($has) $caps_list .= $cap."; ";
        }
        return $caps_list;
    }
}
