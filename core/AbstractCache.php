<?
namespace core;

abstract class AbstractCache
{

    abstract public function __construct($persistent_id='');

    abstract public function add($key, $value, $expiration=0);

    abstract public function add_server($host, $port, $weight=0);

    abstract public function append($key, $value);

    abstract public function cas($token, $key, $value, $expiration=0);

    abstract public function decrement($key, $offset=1);

    abstract public function delete($key, $time=0);

    abstract public function flush($delay=0);

    abstract public function get($key, $cache_callback=null, $cas_token=null);

    abstract public function get_server_list();

    abstract public function get_stats();

    abstract public function increment($key, $offset=1);

    abstract public function prepend($key, $value);

    abstract public function replace($key, $value, $expiration=0);

    abstract public function set($key, $value, $expiration=0);
    
}
?>
