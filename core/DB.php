<?
namespace core;

class DB extends AbstractDatabase
{
    private $db;
    private $cache;
    
    function __construct(AbstractDatabase $db=null, AbstractCache $cache=null)
    {
        $this->db= $db;
        $this->cache= $cache;
    }
    
    function set_database(AbstractDatabase $db)
    {
        $this->db= $db;
    }
    
    function set_cache(AbstractCache $cache)
    {
        $this->cache= $cache;
    }
    
    // by contract to AbstractDatabase:
    function load($table, $id)
    {
        return $this->db->load($table, $id);
    }
    function find($table, $where_array)
    {
        return $this->db->find($table, $where_array);
    }
    function show_tables()
    {
        return $this->db->show_tables();
    }
    function get_columns($table)
    {
        return $this->db->get_columns($table);
    }
    function is_numeric($field_type)
    {
        return $this->db->is_numeric($field_type);
    }
    function query($sql)
    {
//        return $this->db->query($sql);
    }
    function delete($table, $id)
    {
        
    }
    function store($table, $data)
    {
        
    }
    
    
}

?>