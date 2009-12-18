<?
namespace core;

class Session
{
    static function start()
    {
        session_start();
    }
    
    static function object()
    {
        return Session::get("object");
    }
    
    static function login($key, $password)
    {
        $k_field= SESSION_KEY;
        $p_field= SESSION_PASSWORD;
        $class = SESSION_OBJECT;
        
        if ($key == "" or $password == "")
        {
            Session::set_error_message("Please enter a username and password");
            return false;
        }
            
        $sql= $k_field."='".$key."' and ".$p_field."='".$password."'";

        $object= $class::find_object($sql);

        if ($object)
        {
            Session::set_logged_in($object);
            return true;
        }
        else
        {
            Session::set_error_message("Couldn't log in with that username and password");
            return false;
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
    
    static function has_error_messages()
    {
        return (count(Session::get_error_messages(false)) > 0);
    }
    
    static function get_error_messages($clear=true)
    {
        $msgs= Session::get("PLOOF_SYSTEM_ERROR_MESSAGES");
        if ($clear)
            Session::clear_error_messages();
        return $msgs;
    }
    
    static function clear_error_messages()
    {
        Session::clear("PLOOF_SYSTEM_ERROR_MESSAGES");
    }
    
    static function set($name, $value)
    {
        $_SESSION[$name]= $value;
    }
    
    static function get($name)
    {
        return $_SESSION[$name];
    }    
    
    static function clear($name)
    {
        unset($_SESSION[$name]);
    }
}
?>