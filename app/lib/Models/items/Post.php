<?php

namespace Syltaen;

/**
 * Wrap each result of a model in a class that is used to retrieve dynamic fields defined by the model
 */

class Post extends ModelItem
{
    /**
     * Get the title of the post
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->post_title;
    }

    /**
     * Get the slug of the post
     *
     * @return string
     */
    public function getSlug()
    {
        return $this->post_name;
    }

    /**
     * Get the type of the item
     *
     * @return string
     */
    public function getType()
    {
        return $this->post_type;
    }

    /**
     * Get a specific meta data
     *
     * @param  string
     * @return mixed
     */
    public function getMeta($meta_key = "", $multiple = false)
    {
        return get_post_meta($this->getID(), $meta_key, !$multiple);
    }

    /**
     * Update a meta value in the database
     *
     * @param  string $key
     * @param  mixed  $value
     * @return mixed  Meta ID if the key didn't exist, true on successful update, false on failure
     */
    public function setMeta($key, $value)
    {
        return update_post_meta($this->getID(), $key, $value);
    }

    /**
     * Add a new meta value to a multi-value meta
     *
     * @param string $key
     * @param mixed  $value
     */
    public function addMeta($key, $value)
    {
        return add_post_meta($this->getID(), $key, $value);
    }

    /**
     * Remove a post meta
     *
     * @param  string $key    The meta key to remove
     * @param  mixed  $value  Allow to filter by type
     * @return bool   Success or failure
     */
    public function removeMeta($key, $value = null)
    {
        return delete_post_meta($this->getID(), $key, $value);
    }

    /**
     * Set the post thumbnail
     *
     * @param  int  $attachment_id
     * @return bool Success or failure
     */
    public function setThumbnail($attachment_id)
    {
        if ($attachment_id) {
            return set_post_thumbnail($this->getID(), $attachment_id);
        } else {
            return delete_post_thumbnail($this->getID());
        }
    }

    /**
     * Set the attributes of an item
     *
     * @param  int          $id
     * @param  array        $attributes
     * @return int|WP_Error The post ID on success. The value or WP_Error on failure.
     */
    public function setProperties($keys, $merge = false)
    {
        if (empty($keys)) {
            return false;
        }

        $keys       = $this->parseProperties($keys, $merge);
        $keys["ID"] = $this->getID();
        return wp_update_post($keys);
    }

    /**
     * Set the taxonomies of a post
     *
     * @param  array  $tax
     * @param  bool   $merge
     * @return void
     */
    public function setTaxonomies($tax, $merge = false)
    {
        foreach ((array) $tax as $taxonomy => $terms) {
            wp_set_object_terms($this->getID(), $terms, $taxonomy, $merge);
        }
    }

    /**
     * Get the language of a term
     *
     * @return string
     */
    public function getLang($field = "slug")
    {
        return pll_get_post_language($this->getID(), $field);
    }

    /**
     * Set the language of a post
     *
     * @param  string $lang
     * @return bool
     */
    public function setLang($lang)
    {
        // Switch the lang of the post
        pll_set_post_language($this->getID(), $lang);

        // Switch all its translatable taxonomies
        $terms = $this->getTerms();

        $terms = set($terms)->mapAssoc(function ($tax, $terms) use ($lang) {
            if (!pll_is_translated_taxonomy($tax)) {
                return false;
            }

            $terms = array_map(function ($term_id) use ($tax, $lang) {
                $term = new Term($term_id, new TaxonomyModel($tax));

                // Has an existing translation : return it
                if ($translation_id = $term->getTranslationID($lang)) {
                    return $translation_id;
                }

                // Create a new translation by duplicating the term in the right language
                $translation = $term->createTranslation($lang);
                return $translation->getID();
            }, $terms);

            return [$tax, $terms];
        });

        $this->setTaxonomies((array) $terms);

        return $this;
    }

    /**
     * Get a specific post translation's ID
     *
     * @param  string $lang
     * @return int    ID of the translated post
     */
    public function getTranslationID($lang = false)
    {
        return pll_get_post($this->getID(), $lang);
    }

    /**
     * Get all the post translations'IDs
     *
     * @return array
     */
    public function getTranslationsIDs()
    {
        return pll_get_post_translations($this->getID());
    }

    /**
     * Get the post terms
     *
     * @return void
     */
    public function getTerms($taxonomy = false)
    {
        $taxonomies = $taxonomy ? [$taxonomy] : get_post_taxonomies($this->getID());
        $taxonomies = Database::get_results("SELECT t.term_id, tt.taxonomy FROM terms t
            JOIN term_taxonomy tt ON tt.term_id = t.term_id
            JOIN term_relationships tr ON tr.term_taxonomy_id = tt.term_taxonomy_id
            WHERE tr.object_id = {$this->getID()}
        ")->groupBy("taxonomy", "term_id")
            ->callEach()->map("intval")
            ->callEach()->getArrayCopy();

        return $taxonomy ? ($taxonomies[$taxonomy] ?? []) : (array) $taxonomies;
    }

    /**
     * Delete a post
     *
     * @param  bool   $force Whether to bypass Trash and force deletion.
     * @return void
     */
    public function delete($force = false)
    {
        if ($force) {
            wp_delete_post($this->ID, true);
        } else {
            wp_trash_post($this->ID, true);
        }
    }

    /**
     * Create a clone of the post
     *
     * @return Post
     */
    public function duplicate()
    {
        remove_all_filters("wp_insert_post");

        // Create the new post
        $duplicate = $this->model::add([
            "post_title"   => $this->post_title,
            "post_name"    => $this->post_name,
            "post_excerpt" => $this->post_excerpt,
            "post_content" => $this->post_content,
            "post_author"  => $this->post_author,
            "post_status"  => $this->post_status,
            "post_parent"  => $this->post_parent,
        ]);

        // Duplicate all metadata
        foreach ($this->getMeta() as $key => $values) {
            foreach ($values as $value) {
                $duplicate->setMeta($key, maybe_unserialize($value));
            }
        }

        // Same lang as source, to prevent some default switching
        pll_set_post_language($duplicate->getID(), $this->getLang());

        // Duplicate all terms
        $duplicate->setTaxonomies(array_diff_key(
            (array) $this->getTerms(),
            array_flip(["post_translations", "language"])
        ));

        // Force cache flush
        wp_cache_flush();

        return $duplicate;
    }

    /**
     * Add a comment to this post
     *
     * @param  string  $message
     * @param  string  $author_name
     * @param  string  $author_email
     * @param  string  $author_url
     * @param  integer $parent_comment
     * @return void
     */
    public function addComment($message, $author_name = "", $author_email = "", $author_url = "", $parent_comment = 0)
    {
        return CommentsModel::add([
            "comment_post_ID"      => $this->getID(),
            "comment_author"       => $author_name,
            "comment_author_email" => $author_email,
            "comment_author_url"   => $author_url,
            "comment_type"         => "",
            "comment_parent"       => $parent_comment,
            "comment_content"      => wpautop($message),
        ]);
    }

    /**
     * Update or filter the object keys before there are saved
     *
     * @param  object   $object
     * @return object
     */
    public static function filterObjectKeys($post)
    {
        if (function_exists("qtranxf_translate_post")) {
            qtranxf_translate_post($post, Lang::getCurrent());
        }

        if (!empty($post->post_title)) {
            $post->post_title = do_shortcode($post->post_title);
        }

        if (!empty($post->post_content)) {
            $post->post_content = do_shortcode($post->post_content);
        }

        return $post;
    }

    /**
     * Return the ID of the post when used as a string.
     * Usefull shortcut for SQL Queries for example.
     * @return string
     */
    public function __toString()
    {
        return (string) $this->getID();
    }
}