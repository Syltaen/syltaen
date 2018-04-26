<?php

namespace Syltaen;
global $wpdb, $current_site, $current_blog, $wp_rewrite, $shortcode_tags, $wp, $phpmailer, $wp_theme_directories;

// ==================================================
// > DATABASE
// ==================================================
// Get the config for the test database
// If it does not exist, you must clone wp-config.php and make sure to :
//    - change DB_NAME
//    - remove the last line (call to wp-settings.php)
require_once __DIR__ . "/../../../../../wp-config-tests.php";

// Import the Data helper
require_once __DIR__ . "/../Helpers/Data.php";

// Get the name of the live and test database for cloning and switching
// Use to reset the test database everytime and for the database switching
$_DB_LIVE = Data::getDatabaseName("wp-config.php");
$_DB_TEST = Data::getDatabaseName("wp-config-tests.php");

// Clone the live database into the test one
Data::cloneDatabase($_DB_LIVE, $_DB_TEST);

// Sets up WordPress vars and included files.
require_once ABSPATH . "wp-settings.php";


// ==================================================
// > PHPUNIT
// ==================================================
// Install phpunit and phpunit/phpunit-selenium either :
// 1/ Locally with "composer require --dev phpunit/phpunit-selenium" and  comment the line bellow
// 2/ globally and update the line bellow
//    For exemple, add this to your bash_profile to install global composer vendors in /Library/Composer :
//    export COMPOSER_HOME="/Library/Composer"
//    export PATH=$PATH:/Library/Composer/vendor/bin
require_once "/Library/Composer/vendor/autoload.php";

// ==================================================
// > WORDPRESS TEST LIBRARY
// ==================================================
define("WP_TESTS_FORCE_KNOWN_BUGS", false);

require_once __DIR__ . "/../lib/testing/wp-tests/functions.php";
if (class_exists("PHPUnit\Runner\Version")) require_once __DIR__ . "/../lib/testing/wp-tests/phpunit6-compat.php";

require_once __DIR__ . "/../lib/testing/wp-tests/testcase.php";
require_once __DIR__ . "/../lib/testing/wp-tests/testcase-rest-api.php";
require_once __DIR__ . "/../lib/testing/wp-tests/testcase-rest-controller.php";
require_once __DIR__ . "/../lib/testing/wp-tests/testcase-rest-post-type-controller.php";
require_once __DIR__ . "/../lib/testing/wp-tests/testcase-xmlrpc.php";
require_once __DIR__ . "/../lib/testing/wp-tests/testcase-ajax.php";
require_once __DIR__ . "/../lib/testing/wp-tests/testcase-canonical.php";
require_once __DIR__ . "/../lib/testing/wp-tests/exceptions.php";
require_once __DIR__ . "/../lib/testing/wp-tests/utils.php";
require_once __DIR__ . "/../lib/testing/wp-tests/spy-rest-server.php";


// ==================================================
// > THEME CALSSES
// ==================================================
require __DIR__ . "/../lib/testing/UnitTest.php";
require __DIR__ . "/../lib/testing/AcceptanceTest.php";