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
    *  @param string $server_key                The key identifying the server to store the value on.
    *  @param int $expiration                   The expiration time.  Can be number of seconds from now.  
    *                                           If this value exceeds 60*60*24*30 (number of seconds in 30 days), the value will be interpreted
    *                                           as a UNIX timestamp.
    *  @access public
    *  @return bool 
    */
    public function add_to_cache($key, $value, $server_key='', $expiration=0);
    
   /**
    *  Deletes an item.
    *
    *  @param string $key                       The key to be deleted.
    *  @param string $server_key                The key identifying the server to delete the value from.
    *  @param int $time                         The amount of time the server will wait to delete the item.
    *  @access public
    *  @return bool 
    */
    public function delete_from_cache($key, $server_key='', $time=0);
    
   /**
    *  Invalidates all items in the cache.
    *
    *  @param int $delay                        Number of seconds to wait before invalidating the items.
    *  @access public
    *  @return bool 
    */
    public function flush_cache($delay=0);
    
   /**
    *  Retrieves an item.  Returns false if the key is not found.
    *
    *  @param string $key                       The key of the item to retrieve.
    *  @param string $server_key                The key identifying the server to retrieve the value from.
    *  @param callback $cache_callback          Read-through caching callback. 
    *  @param float &$cas_token                 The variable to store the CAS token in. 
    *  @access public
    *  @return mixed 
    *
    */
    public function get_from_cache($key, $server_key='', $cache_callback=null, &$cas_token=null);
    
   /**
    *  Replaces the item under an existing key.  Functionally equivalent to set_in_cache(), though this operation will fail
    *  if $key does not exist.
    *
    *  @param string $key                       The key under which to store the value.
    *  @param mixed $value                      The value to be stored. 
    *  @param string $server_key                The key identifying the server to store the value on.
    *  @param int $expiration                   The expiration time.  Can be number of seconds from now.  
    *                                           If this value exceeds 60*60*24*30 (number of seconds in 30 days), the value will be interpreted
    *                                           as a UNIX timestamp.
    *  @access public
    *  @return bool 
    */
    public function replace_in_cache($key, $value, $server_key='', $expiration=0);
    
   /**
    *  Stores an item.
    *
    *  @param string $key                       The key under which to store the value.
    *  @param mixed $value                      The value to be stored. 
    *  @param string $server_key                The key identifying the server to store the value on.
    *  @param int $expiration                   The expiration time.  Can be number of seconds from now.  
    *                                           If this value exceeds 60*60*24*30 (number of seconds in 30 days), the value will be interpreted
    *                                           as a UNIX timestamp.
    *  @access public
    *  @return bool 
    */
    public function set_in_cache($key, $value, $server_key='', $expiration=0);
}

?>
