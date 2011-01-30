<?
namespace plugins\cache;

class Memcached
{

   /**
    *  The Memcached object. 
    *
    *  @var object
    *  @access protected
    */
    protected $_cache;

   /**
    *  Creates a new instance of Memcached. 
    *
    *  @param string $persistent_id             A unique ID used to allow persistence between requests.
    *                                           By default, instances are destroyed at the end of the request.
    *  @access public
    *  @return void
    */
    public function __construct($persistent_id='')
    {
        $this->_cache = new Memcached($persistent_id);
    }

   /**
    *  Adds an item under a new key.  Functionally equivalent to set_in_cache(), though this operation will fail
    *  if $key already exists on the server.
    *
    *  @param string $key                       The key under which to store the value.
    *  @param mixed $value                      The value to be stored. 
    *  @param int $expiration                   The expiration time.  Can be number of seconds from now.  
    *                                           If this value exceeds 60*60*24*30 (number of seconds in 30 days), the value will be interpreted
    *                                           as a UNIX timestamp.
    *  @param string $server_key                The key identifying the server to store the value on.
    *  @access public
    *  @return bool 
    */
    public function add_to_cache($key, $value, $expiration=0, $server_key='')
    {
        return $this->_cache->addByKey($server_key, $key, $value, $expiration);
    }

   /**
    *  Adds a server to the server pool.
    *
    *  @param string $host                      The hostname of the memcached server.
    *  @param int $weight                       The weight of the server relative to the total weight of all the servers in the pool. 
    *                                           This controls the probability of the server being selected for operations, and
    *                                           usually corresponds to the amount of memory available to memcached on that server.
    *  @param int $port                         The port on which memcache is running.  (Typically 11211.)
    *  @access public
    *  @return bool 
    */
    public function add_server($host, $weight=0, $port=11211)
    {
        return $this->_cache->addServer($host, $port, $weight);
    }

   /**
    *  Adds multiple servers to the server pool.
    *
    *  @param array $servers                    Array of the servers to add to the pool. (Expected format: array(array($host, $weight=0, $port=11211)))
    *  @access public
    *  @return bool 
    */
    public function add_servers($servers)
    {
        $reassembled = array();
        foreach ( array_values($servers) as $server )
        {
            $reassembled[] = array($server[0], 
                                   (isset($server[2])) ? $server[2] : 11211, 
                                   (isset($server[1])) ? $server[1] : 0);
        }

        return $this->_cache->addServers($reassembled);
    }

   /**
    *  Appends data to an existing item.
    *
    *  @param string $key                       The key under which to store the value.
    *  @param string $value                     The string to append.
    *  @param mixed $server_key                 The key identifying the server to store the value on.
    *  @access public
    *  @return bool 
    */
    public function append($key, $value, $server_key='')
    {
        return $this->_cache->appendByKey($server_key, $key, $value);
    }

   /**
    *  Compares and swaps an item.  This means that the item will be stored only if no other client has updated it since it was last fetched by this client.
    *
    *  @param float $token                      Unique value associated with the existing item. Generated by memcached.
    *  @param string $key                       The key under which to store the value.
    *  @param mixed $value                      The value to be stored. 
    *  @param int $expiration                   The expiration time.  Can be number of seconds from now.  
    *                                           If this value exceeds 60*60*24*30 (number of seconds in 30 days), the value will be interpreted
    *                                           as a UNIX timestamp.
    *  @param string $server_key                The key identifying the server to store the value on.
    *  @access public
    *  @return bool 
    *
    *  @see get_from_cache() for how to obtain the CAS token.
    */
    public function cas($token, $key, $value, $expiration=0, $server_key='')
    {
        return $this->_cache->casByKey($token, $server_key, $key, $value, $expiration);
    }

   /**
    *  Decrements a numeric item's value.
    *
    *  @param string $key                       The key of the item to decrement.
    *  @param int $offset                       The amount by which to decrement the item's value. 
    *  @access public
    *  @return int                              If the item's value is not numeric, it is treated as if the value were 0.
    *                                           If the operation would decrease the value below 0, the new value will be 0.
    */
    public function decrement($key, $offset=1)
    {
        return $this->_cache->decrement($key, $offset);
    }

   /**
    *  Deletes an item.
    *
    *  @param string $key                       The key to be deleted.
    *  @param int $time                         The amount of time the server will wait to delete the item.
    *  @param string $server_key                The key identifying the server to delete the value from.
    *  @access public
    *  @return bool 
    */
    public function delete_from_cache($key, $time=0, $server_key='')
    {
        return $this->_cache->deleteByKey($server_key, $key, $time);
    }

   /**
    *  Invalidates all items in the cache.
    *
    *  @param int $delay                        Number of seconds to wait before invalidating the items.
    *  @access public
    *  @return bool 
    */
    public function flush_cache($delay=0)
    {
        return $this->_cache->flush($delay);
    }

   /**
    *  Retrieves an item.  Returns false if the key is not found.
    *
    *  @param string $key                       The key of the item to retrieve.
    *  @param callback $cache_callback          Read-through caching callback. 
    *  @param float &$cas_token                 The variable to store the CAS token in. 
    *  @param string $server_key                The key identifying the server to retrieve the value from.
    *  @access public
    *  @return mixed 
    *
    *  @see cas() for how to use CAS tokens.
    */
    public function get_from_cache($key, $cache_callback=null, &$cas_token=null, $server_key='')
    {
        return $this->_cache->getByKey($server_key, $key, $cache_callback, $cas_token);
    }

   /**
    *  Gets the list of the servers in the pool.
    *
    *  @access public
    *  @return array
    */
    public function get_server_list()
    {
        return $this->_cache->getServerList();
    }

   /**
    *  Gets server pool statistics.
    *
    *  @access public
    *  @return array
    */
    public function get_stats()
    {
        return $this->_cache->getStats();
    }

   /**
    *  Increments a numeric item's value.
    *
    *  @param string $key                       The key of the item to increment.
    *  @param int $offset                       The amount by which to increment the item's value. 
    *  @access public
    *  @return int                              If the item's value is not numeric, it is treated as if the value were 0.
    */
    public function increment($key, $offset=1)
    {
        return $this->_cache->increment($key, $offset);
    }

   /**
    *  Prepends data to an existing item.
    *
    *  @param string $key                       The key of the item to prepend the data to.
    *  @param string $value                     The string to prepend.
    *  @param mixed $server_key                 The key identifying the server to store the value on.
    *  @access public
    *  @return bool 
    */
    public function prepend($key, $value, $server_key='')
    {
        return $this->_cache->prependByKey($server_key, $key, $value);
    }

   /**
    *  Replaces the item under an existing key.  Functionally equivalent to set_in_cache(), though this operation will fail
    *  if $key does not exist.
    *
    *  @param string $key                       The key under which to store the value.
    *  @param mixed $value                      The value to be stored. 
    *  @param int $expiration                   The expiration time.  Can be number of seconds from now.  
    *                                           If this value exceeds 60*60*24*30 (number of seconds in 30 days), the value will be interpreted
    *                                           as a UNIX timestamp.
    *  @param string $server_key                The key identifying the server to store the value on.
    *  @access public
    *  @return bool 
    */
    public function replace_in_cache($key, $value, $expiration=0, $server_key='')
    {
        return $this->_cache->replaceByKey($server_key, $key, $value, $expiration);
    }

   /**
    *  Stores an item.
    *
    *  @param string $key                       The key under which to store the value.
    *  @param mixed $value                      The value to be stored. 
    *  @param int $expiration                   The expiration time.  Can be number of seconds from now.  
    *                                           If this value exceeds 60*60*24*30 (number of seconds in 30 days), the value will be interpreted
    *                                           as a UNIX timestamp.
    *  @param string $server_key                The key identifying the server to store the value on.
    *  @access public
    *  @return bool 
    */
    public function set_in_cache($key, $value, $expiration=0, $server_key='')
    {
        return $this->_cache->setByKey($server_key, $key, $value, $expiration);
    }
    
}
?>
