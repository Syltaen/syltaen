<?php

namespace Syltaen;

/**
 * Unit test using the WordPress testing library
 */
abstract class UnitTest extends \WP_UnitTestCase
{

    /**
     * Set to false in a child to prevent the use of fixtures.
     * No data will be saved in the database, preventing data sharing (and corruption) between tests
     * @var boolean
     */
    protected $useFixtures = false;

    /**
     * Erase the parent to prevent the deletion of all data
     *
     * @return void
     */
    public static function setUpBeforeClass()
    {
    }

    public static function tearDownAfterClass()
    {
    }


    /**
     * Do not use the parent setUp/tearDown if fixtures are disabled
     *
     * @return void
     */
    public function setUp() {
        if ($this->useFixtures) {
            parent::setUp();
        }
    }

    public function tearDown() {
        if ($this->useFixtures) {
            parent::tearDown();
        }
    }

    /**
     * Echo a string into the console during tests
     *
     * @param string $string
     * @return void
     */
    public function echo($string)
    {
        fwrite(STDOUT, "\n" . $string . "\n");
    }
}