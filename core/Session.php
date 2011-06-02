<?
// Copyright (c) 2010, ploof development team
// All rights reserved.
// 
// Redistribution and use in source and binary forms, with or without modification, are permitted provided 
// that the following conditions are met:
// 
// Redistributions of source code must retain the above copyright notice, this list of conditions and the 
// following disclaimer. 
// Redistributions in binary form must reproduce the above copyright notice, this list of 
// conditions and the following disclaimer in the documentation and/or other materials provided with the distribution.
// The names of its contributors may not be used to endorse or promote products derived from this software without 
// specific prior written permission.
// THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, 
// INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE 
// ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, 
// INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE 
// GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF 
// LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY 
// OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.

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

        $object= $class::find_object(array($k_field=>$key, $p_field=>$password));

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
    
    static function push_request()  /* Does this work when user can click 'back' button */
    {
        $sys= Session::get("PLOOF_ROUTES");
        if (is_array($sys)) 
            array_push($sys, $_SERVER['REQUEST_URI']);
        else
            $sys= array($_SERVER['REQUEST_URI']);
        
        // keep the queue at 5 entries:
        if (count($sys) > 5)
            $sys= array_slice($sys, -5, 5);
            
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