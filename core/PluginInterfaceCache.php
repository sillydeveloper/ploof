<?
namespace core;

interface PluginInterfaceCache
{
    function add_to_cache($key, $value, $expiration=0);
    
    function delete_from_cache($key, $time=0);
    
    function flush_cache($delay=0);
    
    function get_cached($key, $cache_callback=null, $cas_token=null);
    
    function key_exists($key);
    
    function replace_cached_item($key, $value, $expiration=0);
    
    function set_cached_item($key, $value, $expiration=0);
    
}

?>
