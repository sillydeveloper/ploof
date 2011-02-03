<?
namespace core;

class Repository implements PluginInterfaceDB, PluginInterfaceCache
{
    private $db;
    private $cache;
    
    public function __construct(PluginInterfaceDB $db=null, PluginInterfaceCache $cache=null)
    {
        $this->db= $db;
        $this->cache= $cache;
    }
    
    public function set_database(PluginInterfaceDB $db)
    {
        $this->db= $db;
    }
    
    public function set_cache(PluginInterfaceCache $cache)
    {
        $this->cache= $cache;
    }
    
    public function get_database()
    {
        return $this->db;
    }
    
    public function get_cache()
    {
        return $this->cache;
    }
    
    //-------------------------------------------------
    // by contract to PluginInterfaceCache
    //-------------------------------------------------
    public function add_to_cache($key, $value, $expiration=0, $server_key='')
    {
        return $this->cache->add_to_cache($key, $value, $expiration, $server_key);    
    }
    
    public function delete_from_cache($key, $time=0, $server_key='')
    {
        return $this->cache->delete_from_cache($key, $time, $server_key);
    }
    
    public function flush_cache($delay=0)
    {
        return $this->cache->flush_cache($delay);
    }
    
    public function get_from_cache($key, $cache_callback=null, &$cas_token=null, $server_key='')
    {
        return $this->cache->get_from_cache($key, $cache_callback, $cas_token, $server_key);
    }
    
    public function replace_in_cache($key, $value, $expiration=0, $server_key='')
    {
        return $this->cache->replace_in_cache($key, $value, $expiration, $server_key);
    }
    
    public function set_in_cache($key, $value, $expiration=0, $server_key='')
    {
        return $this->cache->set_in_cache($key, $value, $expiration, $server_key);
    }
    
    
    //-------------------------------------------------
    // by contract to PluginInterfaceDB:
    //-------------------------------------------------
    public function load_row($table, $id)
    {
        return $this->db->load_row($table, $id);
    }

    public function find_rows($table, $where_array)
    {
        return $this->db->find_rows($table, $where_array);
    }
    public function show_tables()
    {
        return $this->db->show_tables();
    }
    public function get_table_columns($table)
    {
        return $this->db->get_table_columns($table);
    }
    public function is_numeric_datatype($field_type)
    {
        return $this->db->is_numeric_datatype($field_type);
    }
    public function query($sql)
    {
        
    }
    public function delete_row($table, $id)
    {
        $this->db->delete_row($table, $id);
    }
    public function store_row($table, $data)
    {
        $this->db->store_row($table, $data);
    }
    public function is_date_datatype($field_type)
    {
        return $this->db->is_date_datatype($field_type);
    }
    
    
}

?>
