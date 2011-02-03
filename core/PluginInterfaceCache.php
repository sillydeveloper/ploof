<?
namespace core;

interface PluginInterfaceCache
{

   /**
    *  Adds an item under a new key.  Functionally equivalent to set_in_cache(), though this operation will fail
    *  if $key already exists on the server.
    *
    *  @param string $key                       The key under which to store the value.
    *  @param mixed $value                      The value to be stored. 
    *  @access public
    *  @return bool 
    */
    public function add_to_cache($key, $value);
    
   /**
    *  Deletes an item.
    *
    *  @param string $key                       The key to be deleted.
    *  @access public
    *  @return bool 
    */
    public function delete_from_cache($key);
    
   /**
    *  Invalidates all items in the cache.
    *
    *  @access public
    *  @return bool 
    */
    public function flush_cache();
    
   /**
    *  Retrieves an item.  Returns false if the key is not found.
    *
    *  @param string $key                       The key of the item to retrieve.
    *  @access public
    *  @return mixed 
    *
    */
    public function get_from_cache($key);
    
   /**
    *  Replaces the item under an existing key.  Functionally equivalent to set_in_cache(), though this operation will fail
    *  if $key does not exist.
    *
    *  @param string $key                       The key under which to store the value.
    *  @param mixed $value                      The value to be stored. 
    *  @access public
    *  @return bool 
    */
    public function replace_in_cache($key, $value);
    
   /**
    *  Stores an item.
    *
    *  @param string $key                       The key under which to store the value.
    *  @param mixed $value                      The value to be stored. 
    *  @access public
    *  @return bool 
    */
    public function set_in_cache($key, $value);
}

?>
