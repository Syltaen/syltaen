<?php

namespace Syltaen;

class AcceptanceTest_Navigation extends AcceptanceTest
{

    /**
     * Create a page and try to access it by its url
     *
     * @return void
     */
    public function testPageAccess()
    {
        global $_DB_LIVE, $_DB_TEST;

        // Create a base from the theme
        Pages::add([
            "post_title" => "My test page",
            "post_name"  => "my-test-page"
        ]);

        // Try to navigate to that page with the browser
        $this->url("/my-test-page");

        // Check if the page exist
        try {
            $this->assertStringStartsWith("My test page", $this->title());
        }
        catch (\PHPUnit\Framework\ExpectationFailedException $e) {
            throw new \Exception("The website does not use the test database. Please change DB_NAME  \"".$_DB_LIVE."\" by \"".$_DB_TEST."\" in wp-config.php");
            exit;
        }
    }
}