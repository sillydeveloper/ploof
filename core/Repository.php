<?
namespace core;

class Repository implements PluginInterfaceDB, PluginInterfaceCache
{
    private $db;
    private $cache;
    
    function __construct(PluginInterfaceDB $db=null, PluginInterfaceCache $cache=null)
    {
        $this->db= $db;
        $this->cache= $cache;
    }
    
    function set_database(PluginInterfaceDB $db)
    {
        $this->db= $db;
    }
    
    function set_cache(PluginInterfaceCache $cache)
    {
        $this->cache= $cache;
    }
    
    function get_database()
    {
        return $this->db;
    }
    
    function get_cache()
    {
        return $this->cache;
    }
    
    //-------------------------------------------------
    // by contract to PluginInterfaceCache
    //-------------------------------------------------
    function add_to_cache($key, $value, $expiration=0)
    {
        
    }
    
    function delete_from_cache($key, $time=0)
    {
        
    }
    
    function flush_cache($delay=0)
    {
        
    }
    
    function get_cached($key, $cache_callback=null, $cas_token=null)
    {
        
    }
    
    function key_exists($key)
    {
        
    }
    
    function replace_cached_item($key, $value, $expiration=0)
    {
        
    }
    
    function set_cached_item($key, $value, $expiration=0)
    {
        
    }
    
    
    //-------------------------------------------------
    // by contract to PluginInterfaceDB:
    //-------------------------------------------------
    function load_row($table, $id)
    {
        return $this->db->load_row($table, $id);
    }
    
    function find_rows($table, $where_array)
    {
        return $this->db->find_rows($table, $where_array);
    }
    
    function show_tables()
    {
        return $this->db->show_tables();
    }
    
    function get_table_columns($table)
    {
        return $this->db->get_table_columns($table);
    }
    
    function is_numeric_datatype($field_type)
    {
        return $this->db->is_numeric_datatype($field_type);
    }
    
    function query($sql)
    {
        
    }
    
    function delete_row($table, $id)
    {
        $this->db->delete_row($table, $id);
    }
    
    function store_row($table, $data)
    {
        $this->db->store_row($table, $data);
    }
    
    function is_date_datatype($field_type)
    {
        return $this->db->is_date_datatype($field_type);
    }
    
    
}

?>