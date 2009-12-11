<?
/**
 * What object that a session attaches to
 */
define("SESSION_OBJECT", "user");
define("SESSION_KEY", "email");
define("SESSION_PASSWORD", "password");
define("SESSION_PASSWORD_SALT", false);
 
/**
 * By default ploof expects your primary keys to be 'id'.
 * You can change it to something else if you want a different convention.
 */
define("PRIMARY_KEY", "id");

/**
 * Table separator for HABTM
 */
define("PLOOF_SEPARATOR", "__");

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

// Secondary database
//  
//  
//  
//define("SECONDARY_DATABASE_USER", "root");
//define("SECONDARY_DATABASE_PASS", "");
//define("SECONDARY_DATABASE_HOST", "localhost");
//define("SECONDARY_DATABASE_NAME", "ploof");

date_default_timezone_set('America/Los_Angeles');

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

// TODO: Make it so you don't have to set this. It's annoying.
define("BASE_INSTALL", "");

// TODO: Same as above
set_include_path(get_include_path().PATH_SEPARATOR.BASE_INSTALL.PATH_SEPARATOR.BASE_INSTALL."/model".PATH_SEPARATOR.BASE_INSTALL."/controller".PATH_SEPARATOR.BASE_INSTALL."/view");

//--------------------------------------------------
//          SHARED HELPER METHODS
//--------------------------------------------------

// namespace autoloader
function __autoload($class_name)
{
    require_once(str_replace("\\", "/", $class_name).".php");
}

function classname_only($classname)
{
    return preg_replace("/([A-Za-z0-9]*\\\\)/", "", $classname);
}

function url_matches($url)
{
    $uri= $_SERVER['REQUEST_URI'];
    
    list($url_con, $url_act, $url_id) = get_url_parts($url);
    list($uri_con, $uri_act, $uri_id) = get_url_parts($uri);    
    
    if ($url_con and $url_act and $url_id)
        return  get_url_parts($url) == get_url_parts($uri);
    if ($url_con and $url_act)
        return ($url_con == $uri_con and $url_act == $uri_act);
    if ($url_con)
        return ($url_con == $uri_con);
}       

function get_url_parts($url)
{
    $split= explode("/",substr($url, 1)); // trim the front slash and split
    return array($split[0], $split[1], $split[2]);
        
}

?>