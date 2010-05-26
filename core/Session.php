<?
namespace core;

class Session extends Ploof
{
    const ERROR= "Error";
    const WARNING= "Warning";
    const SYSTEM= "System";
    const NOTICE= "Notice";

	const CLEAR= true;
	const NO_CLEAR= false;
    
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
            Session::set_message(Session::NOTICE, "Please enter a username and password");
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
            Session::set_message(Session::NOTICE, "Couldn't log in with that username and password");
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
    
    static function push_request()
    {
        $sys= Session::get("PLOOF_ROUTES");
        if (is_array($sys)) 
            array_push($sys, $_SERVER['REQUEST_URI']);
        else
            $sys= array($_SERVER['REQUEST_URI']);
        Session::set("PLOOF_ROUTES", $sys);
    }
    
    static function pop_request()
    {
        $sys= Session::get("PLOOF_ROUTES");
        $res= array_pop($sys);
        Session::set("PLOOF_ROUTES", $sys);
        return $res;
    }
    
    static function set_message($type, $msg)
    {
        $sys= Session::get("PLOOF_MESSAGES");
        if (is_array($sys[$type]))
            array_push($sys[$type], $msg);
        else
            $sys= array($type => array($msg));
        
        Session::set("PLOOF_MESSAGES", $sys);
    }
    
    static function has_messages($type)
    {
        return (count(Session::get_messages($type, false)) > 0);
    }
    
    static function get_messages($type=null, $clear=Session::CLEAR)
    {
        $msgs= Session::get("PLOOF_MESSAGES");
        
        $message= ($type) ? $msgs[$type] : $msgs;
        
        if ($clear)
            Session::clear_messages($type);
        
        return $message;
    }
    
    static function clear_messages($type=null)
    {
        if ($type)
        {
            $sys= Session::get("PLOOF_MESSAGES");
            unset($sys[$type]);
            Session::set("PLOOF_MESSAGES", $sys);
        }
        else
            Session::clear("PLOOF_MESSAGES");
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