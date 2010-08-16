<?
// TODO: Make it so you don't have to set this. It's annoying.
define("BASE_INSTALL", "");

// TODO: Same as above
set_include_path(get_include_path().PATH_SEPARATOR.BASE_INSTALL.PATH_SEPARATOR.BASE_INSTALL."/model".PATH_SEPARATOR.BASE_INSTALL."/controller".PATH_SEPARATOR.BASE_INSTALL."/view".PATH_SEPARATOR.BASE_INSTALL."/core".PATH_SEPARATOR.BASE_INSTALL."/test/temp");

/**
 * What object that a session attaches to
 */
define("SESSION_OBJECT", "User");
define("SESSION_KEY", "username");
define("SESSION_PASSWORD", "password");
define("SESSION_PASSWORD_SALT", false);

/**
 * Display errors.
 */
ini_set('display_errors', 1);

/**
 * How verbose you want to debug (1-5). 5 will show everything.
 */
define("DEBUG_LEVEL", 1);

/**
 * Enable caching: turn off for development, on for production.
 * 0: ALWAYS call refresh() in the core\Joiner object (more DB thrashing)
 * 1: Refresh joined objects only on refresh() command
 */
define('ENABLE_OBJECT_CACHE', 1);

/**
 * Initial controller to load
 */
define("DEFAULT_CONTROLLER", "Dashboard");

/**
 * Uhh... Obvious hopefully.
 */
define("DATABASE_USER", "root");
define("DATABASE_PASS", "");
define("DATABASE_HOST", "");
define("DATABASE_NAME", "");

/**
 * Fixture database for unit tests. This database
 * WILL BE WIPED each time you run unit tests.
 */
define("TEST_DATABASE_USER", "root");
define("TEST_DATABASE_PASS", "");
define("TEST_DATABASE_HOST", "");
define("TEST_DATABASE_NAME", "ploof_fixtures");

/**
 * Initial action to call on controllers
 */
define("DEFAULT_ACTION", "index");

define("DEFAULT_LAYOUT", "default");

/**
 * TODO: This is temporary.
 */
define("USE_MYSQLI", true);

/**
 * PHP 5.3 requires it.
 */
date_default_timezone_set('America/Los_Angeles');

/**
 * Turn off / on automatic sanitizers, if available
 */
define("SANITIZE_INPUT", true);

/**
 * By default ploof expects your primary keys to be 'id'.
 * You can change it to something else if you want a different convention.
 */
define("PRIMARY_KEY", "id");

/**
 * The token that comes before id -- for example, User_id
 */
define("PK_SEPERATOR", "_");

/**
 * Table separator for HABTM
 */
define("PLOOF_SEPARATOR", "__");

/**
 * Confuse and amaze your friends by changing the view extension to your last name!
 * Actually this is here so you can change it to what you want so your editor will
 * mark it up properly. I prefer html. You can use whatever you please.
 */
define("VIEW_EXTENSION", ".html");

/**
 * TODO: This is dumb.
 */
if (!defined("IN_UNIT_TESTING"))
    define('IN_UNIT_TESTING', 0); 

if (file_exists("config.application.php"))
    require_once "config.application.php";

require_once "application.php";
require_once "fun.php";
?>