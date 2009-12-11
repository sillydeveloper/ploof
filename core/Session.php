<?
namespace core;

class Session
{
    static function start()
    {
        session_start();
    }
    
    static function as_object()
    {
        
    }
    
    static function login($key, $password)
    {
        $k_field= SESSION_KEY;
        $p_field= SESSION_PASSWORD;
        $class = SESSION_OBJECT;
        
        $sql= $k_field."='".$key."' and ".$p_field."='".$password."'";

        $object= $class::find_object($sql);

        if ($object)
        {
            Session::set_logged_in($object);
        }
        else
        {
            Session::set_error_message("Couldn't log in with that username and password");
        }
    }
    
    // note this doesn't actually _do_ the logging in:
    static function set_logged_in($object)
    {
        $_SESSION["logged_in"]= true;
        $_SESSION["object"]= $object;
    }
    
    static function logout()
    {
        self::set_logged_out();
    }
    
    static function set_logged_out()
    {
        session_destroy();
    }
    
    static function set_error_message($msg)
    {
        $sys= Session::get("PLOOF_SYSTEM_ERROR_MESSAGES");
        $sys= ($sys) ? array_push($sys, $msg) : array($msg);
        Session::set("PLOOF_SYSTEM_ERROR_MESSAGES", $sys);
    }
    
    
    static function set($name, $value)
    {
        $_SESSION[$name]= $value;
    }
    
    static function get($name)
    {
        return $_SESSION[$name];
    }    
}
?>