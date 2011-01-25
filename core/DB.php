<?
namespace core;

class DB extends AbstractDB
{
    private $db;
    private $cache;
    
    function __construct(AbstractDB $db=null, AbstractCache $cache=null)
    {
        $this->db= $db;
        $this->cache= $cache;
    }
    
    function set_database(AbstractDB $db)
    {
        $this->db= $db;
    }
    
    function set_cache(AbstractCache $cache)
    {
        $this->cache= $cache;
    }
    
    // by contract to AbstractDB:
    
    function load($table, $id)
    {
        
    }
    
    function find($table, $where_array)
    {
        $this->db->find($table, $where_array);
    }
    function show_tables()
    {
        $this->db->show_tables();
    }
    function get_columns($table)
    {
        $this->db->get_columns($table);
    }
    function is_numeric($field_type)
    {
        
    }
    
    function query($sql);
    
}

?>