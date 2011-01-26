<?
namespace core;

abstract class AbstractCache
{

    abstract public function __construct($persistent_id='');

    abstract public function add($key, $value, $expiration=0);

    abstract public function delete($key, $time=0);

    abstract public function flush($delay=0);

    abstract public function get($key, $cache_callback=null, $cas_token=null);

    abstract public function key_exists($key);

    abstract public function replace($key, $value, $expiration=0);

    abstract public function set($key, $value, $expiration=0);
    
}

?>
