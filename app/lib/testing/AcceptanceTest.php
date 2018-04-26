<?php

namespace Syltaen;

/**
 * Acceptance test using Selenium
 * Require :
 * - phpunit/phpunit-selenium
 * - selenium2 (web driver)
 * - chromedriver
 *
 * Run "selenium" (alias created for java -jar /usr/local/bin/selenium-server-standalone-3.10.0.jar) before the tests
 * And switch DB_NAME to the test database in wp-config.php
 *
 * java -jar /usr/local/bin/selenium-server-standalone-3.10.0.jar -browserSessionReuse
 *
 * @see http://apigen.juzna.cz/doc/sebastianbergmann/phpunit-selenium/class-PHPUnit_Extensions_Selenium2TestCase.html
 */
abstract class AcceptanceTest extends \PHPUnit_Extensions_Selenium2TestCase
{

    /**
     * Prevent the closing of the browser
     * Use it to debug failed tests
     * @var boolean
     */
    protected static $preventClose = false;

    /**
     * Use the same session for each tests
     *
     * @var boolean
     */
    protected static $shareSession = true;

    /**
     * Usename used during the registration and connexion tests
     *
     * @var string
     */
    protected $username = "my_test_user@test.test";

    /**
     * Password used during the registration and connexion tests
     *
     * @var string
     */
    protected $password = "test";


    /**
     * Go to the connexion page and login as the test user
     *
     * @param [type] $password
     * @return void
     */
    protected function login($password = false, $username = false)
    {
        // Go to the connexion page
        $this->url("/connexion");

        // Get the connexion form, fill it and submit it
        $form = $this->byId("loginform");

        // Fill the two input
        $form->byName("log")->value($username ? $username : $this->username);
        $form->byName("pwd")->value($password ? $password : $this->password);

        // Submit the form
        $form->submit();
    }

    /**
     * Create the test user directly in the database using the registration form action
     *
     * @return void
     */
    protected function createTestUser()
    {
        // Format the data for the form aciton
        $fields = [];
        foreach ($this->generateTestUserData() as $key=>$value) {
            $fields[] = [
                "key" => $key,
                "value"=> $value
            ];
        };

        // Launch the form action
        (new ActionRegisterUser)->process(
            ["success_message" => false],
            false,
            ["fields" => $fields]
        );

        // Validate the user
        (new Users)->order("ID", "desc")->limit(1)->validate();
    }

    public function generateTestUserData()
    {
        return [
            "email"    => $this->username,
            "password" => $this->password,

            "firstname" => "TEST",
            "lastname"  => "USER",
        ];
    }


    // ==================================================
    // > DRIVER SET UP
    // ==================================================
    public function setUp()
    {
        $this->setBrowser("chrome");
        $this->setBrowserUrl(site_url());

        // Cheat to keep the browser open at the end
        if (static::$preventClose) {
            $myClassReflection = new \ReflectionClass( get_class( $this->prepareSession() ) );
            $secret            = $myClassReflection->getProperty( 'stopped' );
            $secret->setAccessible( true );
            $secret->setValue( $this->prepareSession(), true );
        }
    }

    // ==================================================
    // > CUSTOM ASSERTIONS
    // ==================================================
    public function assertSee($message, $content = false)
    {
        $target = $target ? $target : $this->byClassName("site");
        $this->assertContains($message, $target->text());
    }

    public function assertNotSee($message, $target = false)
    {
        $target = $target ? $target : $this->byClassName("site");
        $this->assertNotContains($message, $target->text());
    }

    // ==================================================
    // > DATABASE SWITCHING
    // ==================================================
    public static function setUpBeforeClass()
    {
        global $_DB_LIVE, $_DB_TEST;

        // Replace the DB_NAME in wp-config.php to use our test database
        Data::switchDatabase($_DB_LIVE, $_DB_TEST, "wp-config.php");

        self::shareSession(self::$shareSession);

        parent::setUpBeforeClass();
    }

    public static function tearDownAfterClass()
    {
        global $_DB_LIVE, $_DB_TEST;

        // Replace the DB_NAME in wp-config.php to use our live database again
        Data::switchDatabase($_DB_TEST, $_DB_LIVE, "wp-config.php");

        parent::tearDownAfterClass();
    }

}