<?
/**
 * By default ploof expects your primary keys to be 'id'.
 * You can change it to something else if you want a different convention.
 */
define("PRIMARY_KEY", "id");

/**
 * Table separator for HABTM
 */
define("TABLE_SEPARATOR", "__");

/**
 * Initial controller to load
 */
define("DEFAULT_CONTROLLER", "Dashboard");

/**
 * Initial action to call on controllers
 */
define("DEFAULT_ACTION", "index");

define("DEFAULT_LAYOUT", "default");

/**
 * Uhh... Obvious hopefully.
 */
define("DATABASE_USER", "root");
define("DATABASE_PASS", "");
define("DATABASE_HOST", "localhost");
define("DATABASE_NAME", "ploof");

/**
 * Turn off / on automatic sanitizers
 */
define("SANITIZE_INPUT", true);

/**
 * Fixture database for unit tests.
 */
define("TEST_DATABASE_USER", "root");
define("TEST_DATABASE_PASS", "");
define("TEST_DATABASE_HOST", "localhost");
define("TEST_DATABASE_NAME", "ploof_fixtures");

/**
 * Confuse and amaze your friends by changing the view extension to your last name!
 * Actually this is here so you can change it to what you want so your editor will
 * mark it up properly. I prefer html. You can use whatever you please.
 */
define("VIEW_EXTENSION", ".html");

// TODO
define("BASE_INSTALL", "");

// TODO
set_include_path(get_include_path().PATH_SEPARATOR.BASE_INSTALL.PATH_SEPARATOR.BASE_INSTALL."/model".PATH_SEPARATOR.BASE_INSTALL."/controller".PATH_SEPARATOR.BASE_INSTALL."/view");

// namespace autoloader
function __autoload($class_name)
{
    require_once(str_replace("\\", "/", $class_name).".php");
}
?>