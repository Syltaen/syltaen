<?php

namespace Syltaen;

/**
 * Test the theme and its dependencies
 */
//
class UnitTest_Theme extends UnitTest
{

    protected $useFixtures = false;

    // ==================================================
    // > DEPENDENCIES
    // ==================================================
    /**
     * Check npm dependencies
     *
     * @return void
     */
    public function testNPM()
    {
        $this->assertTrue(is_dir(Files::path("node_modules")));
    }

    /**
     * Check composer dependencies
     *
     * @return void
     */
    public function testComposer()
    {
        $this->assertTrue(is_dir(Files::path("app/vendors/vendor")));
    }

    /**
     * Test the required plugins
     *
     * @return void
     */
    public function testPlugins()
    {
        $this->assertTrue(is_plugin_active("advanced-custom-fields-pro/acf.php"));
        $this->assertTrue(is_plugin_active("tinymce-advanced/tinymce-advanced.php"));
        $this->assertTrue(is_plugin_active("all-in-one-wp-migration/all-in-one-wp-migration.php"));
    }

    // ==================================================
    // > MODELS
    // ==================================================
    /**
     * Test the post creation
     *
     * @return void
     */
    public function testModelAdd()
    {
        $model = (new Pages)->limit(1)->addFields("custom_field");

        $string = rand_str();

        // Post creation
        Pages::add([
            "post_title" => "My new post"
        ], [
            "custom_field" => "My custom field {$string}"
        ]);

        $this->assertSame("My custom field {$string}", $model->getOne()->custom_field);
    }

    /**
     * Test the post update
     *
     * @return void
     */
    public function testModelUpdate()
    {
        $model = (new Pages)->addFields("custom_field")->limit(1)->order("ID", "desc");

        $string = rand_str();

        $model->update([
            "post_title" => "My updated post"
        ], [
            "custom_field" => "My updated custom field {$string}"
        ]);

        $this->assertSame("My updated post", $model->getOne()->post_title);
        $this->assertSame("My updated custom field {$string}", $model->getOne()->custom_field);
    }

    /**
     * Test the post deletion
     *
     * @return void
     */
    public function testModelDelete()
    {
        $totalPagesThen = (new Pages)->count();
        (new Pages)->limit(1)->delete();
        $totalPagesNow = (new Pages)->count();

        $this->assertTrue($totalPagesThen - $totalPagesNow == 1);
    }

    /**
     * Test the model's cache
     *
     * @return void
     */
    public function testModelCache()
    {
        $id = Pages::add(["post_title" => "AAA"]);

        $model = (new Pages)->limit(1);

        // Without filters, getOne should return the last post by date
        $this->assertSame($id, $model->getOne()->ID);

        // If I update one result
        $model->update(["post_status" => "pending"]);

        // I should still have the same return
        $this->assertSame($id, $model->status("pending")->getOne()->ID);
    }

    // ==================================================
    // > CONTROLLERS
    // ==================================================


    // ==================================================
    // > ROUTERS
    // ==================================================
    // public function testRoute()
    // {
    // }
}
