<?
namespace plugins\cache;

class SessionCache extends core\PluginInterfaceCache
{

   /**
    *  Begins the session. 
    *
    *  @access public
    *  @return void
    */
    public public function __construct()
    {
        session_start();
    }

   /**
    *  Adds an item under a new key.  Functionally equivalent to set_in_cache(), though this operation will fail
    *  if $key already exists on the server.
    *
    *  @param string $key                       The key under which to store the value.
    *  @param mixed $value                      The value to be stored. 
    *  @access public
    *  @return bool 
    */
    public function add_to_cache($key, $value)
    {
        if ( isset($_SESSION[$key]) )
        {
            return false;
        }
        return $this->set($key, $value);
    }
    
   /**
    *  Deletes an item.
    *
    *  @param string $key                       The key to be deleted.
    *  @access public
    *  @return bool 
    */
    public function delete_from_cache($key)
    {
        unset($_SESSION[$key]);
        return true;
    }
    
   /**
    *  Invalidates all items in the cache.
    *
    *  @access public
    *  @return bool 
    */
    public function flush_cache()
    {
        session_unset();
        session_destroy();
        session_start();
        session_regenerate_id();
        $_SESSION = array();
        return true;
    }
    
   /**
    *  Retrieves an item.  Returns false if the key is not found.
    *
    *  @param string $key                       The key of the item to retrieve.
    *  @access public
    *  @return mixed 
    */
    public function get_from_cache($key)
    {
        if ( !isset($_SESSION[$key]) )
        {
            return false;
        }
        return $_SESSION[$key];
    }
    
   /**
    *  Replaces the item under an existing key.  Functionally equivalent to set_in_cache(), though this operation will fail
    *  if $key does not exist.
    *
    *  @param string $key                       The key under which to store the value.
    *  @param mixed $value                      The value to be stored. 
    *  @access public
    *  @return bool 
    */
    public function replace_in_cache($key, $value)
    {
        if ( !isset($_SESSION[$key]) )
        {
            return false;
        }
        return $this->set($key, $value);
    }
    
   /**
    *  Stores an item.
    *
    *  @param string $key                       The key under which to store the value.
    *  @param mixed $value                      The value to be stored. 
    *  @access public
    *  @return bool 
    */
    public function set_in_cache($key, $value)
    {
        $_SESSION[$key] = $value;
        return true;
    }
}

?>
